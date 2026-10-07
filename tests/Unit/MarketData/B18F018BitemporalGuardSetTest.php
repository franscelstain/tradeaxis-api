<?php

use App\Application\MarketData\Services\AsKnownReplaySnapshotService;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- `F-MD-B18-A002-018` (as-known fixtures, bitemporal resolution and comparison-class review gaps).
 *
 * `Replay_Verification_Contract_LOCKED.md`: "As-known replay performs bitemporal resolution with `effective_at <= target
 * context` and `recorded_at <= knowledge_cutoff`; ties and corrections use versioned deterministic rules. Unresolved
 * ambiguity fails closed." and the anti-future fixtures "a calendar/status fact corrected after T" and "a corporate action
 * learned or verified later".
 *
 * `F-MD-B18-A002-018` found the recorded guards of these predicates pass against a WALL (an implementation that hides
 * everything as soon as a cutoff is supplied), vary one time axis only, or never hold a revision known before the cutoff
 * beside the one learned after it. Every method here therefore carries, for the root it proves:
 *
 *   - a revision KNOWN BEFORE the cutoff that must be visible at the cutoff (so a wall fails),
 *   - the same shape recorded AFTER the cutoff that must be invisible to it (so a missing knowledge bound fails),
 *   - an effective-time control: a revision recorded early but effective after the target date must not apply to it, yet
 *     must apply to a later target (so a missing effective bound fails and a wall still fails),
 *   - for a correction: the original stays the answer at the early cutoff, the correction becomes the answer once it is
 *     recorded, and the uncut read agrees with the later cutoff (so a current-state substitution fails).
 *
 * The methods are one predicate each; none stands in for another.
 */
class B18F018BitemporalGuardSetTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TARGET = '2026-03-24';

    /** The knowledge cutoff: everything "known" is recorded on or before it. */
    private const CUTOFF = '2026-04-15 00:00:00';

    /** After the corrections below were recorded. */
    private const AFTER = '2026-06-30 00:00:00';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    // ================================================================================================
    // MD-S003-R0011 -- calendar/session/status revisions respect effective and knowledge time
    // ================================================================================================

    public function test_r0011_calendar_and_status_revisions_respect_effective_time_and_knowledge_time_each_on_its_own(): void
    {
        // ---- calendar: knowledge time
        $this->calendar('2026-03-24', '2026-03-01 00:00:00');
        $this->calendar('2026-03-25', '2026-06-01 00:00:00');
        $calendar = new MarketCalendarRepository();
        $this->assertSame('2026-03-24', $this->known(function () use ($calendar) { return $calendar->sessionContext('2026-03-24', self::CUTOFF)['trade_date']; }, 'a calendar revision recorded before the cutoff must be visible at it (a wall hides it)'),
            'a calendar revision recorded before the cutoff must be visible at it (a wall hides it)');
        $this->assertSame('2026-03-25', $calendar->sessionContext('2026-03-25')['trade_date'], 'the late revision exists, so the fixture is real');
        try {
            $calendar->sessionContext('2026-03-25', self::CUTOFF);
            $this->fail('a calendar revision recorded after the cutoff must be invisible to it');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('MARKET_CALENDAR_EVIDENCE_MISSING', $e->getMessage());
        }

        // ---- calendar: effective time. A date after the target, recorded early, is not part of a window ending at the target.
        $this->calendar('2026-03-26', '2026-03-01 00:00:00');
        $this->assertSame(['2026-03-24'], $calendar->tradingDatesBetween('2026-03-23', '2026-03-25', self::CUTOFF),
            'a revision effective after the target date must not apply to it, and the late-recorded one is unknown');
        $this->assertSame(['2026-03-24', '2026-03-26'], $calendar->tradingDatesBetween('2026-03-23', '2026-03-26', self::CUTOFF),
            'it applies to a later target, so effective time is a filter and not a wall');

        // ---- status: knowledge time and effective time, one listing per case
        $visible = $this->status(5301, 'status-visible', '2026-03-01 00:00:00', '2026-03-02 00:00:00');
        $effectiveLater = $this->status(5302, 'status-effective-later', '2026-04-01 00:00:00', '2026-03-02 00:00:00');
        $recordedLater = $this->status(5303, 'status-recorded-later', '2026-03-01 00:00:00', '2026-05-02 00:00:00');
        $repository = new TemporalTradingStatusRepository();

        $this->assertSame('SUSPENSION', $repository->resolveForListing($visible, self::TARGET, self::CUTOFF)['status_code'],
            'a status revision effective and recorded before the cutoff must be visible at it (a wall hides it)');

        $this->assertNotSame('SUSPENSION', $repository->resolveForListing($effectiveLater, self::TARGET, self::CUTOFF)['status_code'],
            'a status revision known at the cutoff but effective only after the target date must not apply to the target');
        $this->assertSame('SUSPENSION', $repository->resolveForListing($effectiveLater, '2026-04-02', self::CUTOFF)['status_code'],
            'it applies to a target after its effective time, so effective time is a filter and not a wall');

        $this->assertNotSame('SUSPENSION', $repository->resolveForListing($recordedLater, self::TARGET, self::CUTOFF)['status_code'],
            'a status revision recorded after the cutoff must be invisible to it, although it was effective at the target');
        $this->assertSame('SUSPENSION', $repository->resolveForListing($recordedLater, self::TARGET, self::AFTER)['status_code'],
            'and visible once recorded');
        $this->assertSame('SUSPENSION', $repository->resolveForListing($recordedLater, self::TARGET)['status_code'], 'and without a cutoff');
    }

    // ================================================================================================
    // MD-S050-R0022 -- a calendar/status fact corrected after T
    // ================================================================================================

    public function test_r0022_a_calendar_and_a_status_fact_corrected_after_T_resolve_as_known_at_each_cutoff(): void
    {
        // Calendar: the original says a full session; the correction (recorded after the cutoff) says a half day.
        $original = $this->calendar('2026-03-24', '2026-03-01 00:00:00', ['is_half_day' => 0, 'session_close_at' => '2026-03-24 16:00:00']);
        $this->calendar('2026-03-24', '2026-05-20 00:00:00', ['is_half_day' => 1, 'session_close_at' => '2026-03-24 12:00:00', 'supersedes_revision_id' => $original]);
        $calendar = new MarketCalendarRepository();

        $atCutoff = $this->known(function () use ($calendar) { return $calendar->sessionContext(self::TARGET, self::CUTOFF); }, 'at the early cutoff the original, known revision stands');
        $this->assertFalse($atCutoff['is_half_day'], 'at the early cutoff the original, known revision stands');
        $this->assertSame('2026-03-24 16:00:00', $atCutoff['session_close_at']);
        $afterCorrection = $this->known(function () use ($calendar) { return $calendar->sessionContext(self::TARGET, self::AFTER); }, 'once the correction is recorded it is the answer (so the cutoff is not a wall)');
        $this->assertTrue($afterCorrection['is_half_day'], 'once the correction is recorded it is the answer (so the cutoff is not a wall)');
        $this->assertSame('2026-03-24 12:00:00', $afterCorrection['session_close_at']);
        $this->assertSame($afterCorrection['calendar_revision_id'], $calendar->sessionContext(self::TARGET)['calendar_revision_id'],
            'and the uncut read agrees with the later cutoff');
        $this->assertNotSame($atCutoff['calendar_revision_id'], $afterCorrection['calendar_revision_id']);

        // Status: a suspension known at the cutoff, lifted by a later-recorded correction of the same revision.
        $listing = $this->status(5311, 'status-corrected', '2026-03-10 00:00:00', '2026-03-12 00:00:00');
        $this->statusCorrection($listing, 'status-corrected', '2026-03-10 00:00:00', '2026-03-18 00:00:00', '2026-05-01 00:00:00');
        $status = new TemporalTradingStatusRepository();

        $this->assertSame('SUSPENSION', $status->resolveForListing($listing, self::TARGET, self::CUTOFF)['status_code'],
            'the lift took effect but was learned later: at the early cutoff the suspension still stands');
        $this->assertNotSame('SUSPENSION', $status->resolveForListing($listing, self::TARGET, self::AFTER)['status_code'],
            'once the correction is recorded the same target resolves differently');
        $this->assertSame($status->resolveForListing($listing, self::TARGET, self::AFTER)['status_code'], $status->resolveForListing($listing, self::TARGET)['status_code'],
            'and the uncut read agrees with the later cutoff');
    }

    public function test_r0022_the_current_state_is_not_substituted_for_the_as_known_correction_chain(): void
    {
        $original = $this->calendar('2026-03-24', '2026-03-01 00:00:00', ['is_half_day' => 0]);
        $this->calendar('2026-03-24', '2026-05-20 00:00:00', ['is_half_day' => 1, 'supersedes_revision_id' => $original]);
        $listing = $this->status(5312, 'status-substitution', '2026-03-10 00:00:00', '2026-03-12 00:00:00');
        $this->statusCorrection($listing, 'status-substitution', '2026-03-10 00:00:00', '2026-03-18 00:00:00', '2026-05-01 00:00:00');

        $calendar = new MarketCalendarRepository();
        $status = new TemporalTradingStatusRepository();
        $this->assertNotSame(
            $calendar->sessionContext(self::TARGET)['is_half_day'],
            $calendar->sessionContext(self::TARGET, self::CUTOFF)['is_half_day'],
            'the fixture must be one where the current calendar and the as-known calendar disagree, or a substitution is undetectable'
        );
        $this->assertNotSame(
            $status->resolveForListing($listing, self::TARGET)['status_code'],
            $status->resolveForListing($listing, self::TARGET, self::CUTOFF)['status_code'],
            'the fixture must be one where the current status and the as-known status disagree, or a substitution is undetectable'
        );
    }

    // ================================================================================================
    // MD-S050-R0023 -- a corporate action learned or verified later
    // ================================================================================================

    public function test_r0023_a_corporate_action_learned_or_verified_later_is_known_only_as_it_was_at_the_cutoff(): void
    {
        $this->listing(31, 931);
        $this->listing(32, 932);
        $this->listing(33, 933);

        // 31: learned before the cutoff, not revised: visible at the cutoff (a wall hides it).
        $this->action('evt-known', 1, 31, 'UNVERIFIED', '2026-03-01 00:00:00');
        // 32: learned after the cutoff: invisible to it.
        $this->action('evt-learned-later', 1, 32, 'AUTHORITATIVE_VERIFIED', '2026-06-01 00:00:00');
        // 33: known early as unverified and VERIFIED later (a superseding revision recorded after the cutoff).
        $first = $this->action('evt-verified-later', 1, 33, 'UNVERIFIED', '2026-03-01 00:00:00');
        $this->action('evt-verified-later', 2, 33, 'AUTHORITATIVE_VERIFIED', '2026-06-01 00:00:00', $first);

        $repository = new EventRiskSourceRepository();
        $asKnown = $repository->resolveEventRiskContextForTickerIds([931, 932, 933], self::TARGET, self::CUTOFF);
        $later = $repository->resolveEventRiskContextForTickerIds([931, 932, 933], self::TARGET, self::AFTER);
        $uncut = $repository->resolveEventRiskContextForTickerIds([931, 932, 933], self::TARGET);

        $this->assertSame(1, (int) ($asKnown[931]['corporate_action_flag'] ?? 0), 'an action known before the cutoff must be visible at it');
        $this->assertSame(0, (int) ($asKnown[932]['corporate_action_flag'] ?? 0), 'an action learned after the cutoff must be invisible to it');
        $this->assertSame(1, (int) ($later[932]['corporate_action_flag'] ?? 0), 'and visible once learned');
        $this->assertArrayHasKey(933, $asKnown, 'verified later: the revision known at the cutoff must still be the terminal one, not erased by a verification recorded after it');
        $this->assertSame('UNVERIFIED', $asKnown[933]['corporate_action_verification_states'], 'verified later: at the cutoff it is still the unverified revision');
        $this->assertSame('AUTHORITATIVE_VERIFIED', $later[933]['corporate_action_verification_states'], 'once the verification is recorded it is the verified revision');
        $this->assertSame($later[933]['corporate_action_verification_states'], $uncut[933]['corporate_action_verification_states'], 'and the uncut read agrees');
        $this->assertNotSame($asKnown[933]['corporate_action_revision_ids'], $later[933]['corporate_action_revision_ids']);
    }

    public function test_r0023_a_cancellation_recorded_after_the_cutoff_does_not_remove_an_action_known_at_it(): void
    {
        $this->listing(34, 934);
        $first = $this->action('evt-cancelled-later', 1, 34, 'AUTHORITATIVE_VERIFIED', '2026-03-01 00:00:00');
        $this->action('evt-cancelled-later', 2, 34, 'AUTHORITATIVE_VERIFIED', '2026-06-01 00:00:00', $first, 'CANCELLED');

        $repository = new EventRiskSourceRepository();
        $this->assertSame(1, (int) ($repository->resolveEventRiskContextForTickerIds([934], self::TARGET, self::CUTOFF)[934]['corporate_action_flag'] ?? 0),
            'the June cancellation is not known in April, so the earlier revision is still the terminal one');
        $this->assertSame(0, (int) ($repository->resolveEventRiskContextForTickerIds([934], self::TARGET)[934]['corporate_action_flag'] ?? 0),
            'and once known the cancellation removes it, so the fixture is real');
    }

    // ================================================================================================
    // MD-S050-R0028 -- as-known bitemporal resolution: effective_at <= target and recorded_at <= cutoff for EVERY root;
    //                  ties and corrections are deterministic; unresolved ambiguity fails closed
    // ================================================================================================

    /**
     * The per-root guard set `F-MD-B18-A002-018` asked for. The rule is universal over as-known resolution, so each root the
     * exit gate names -- identity, status, calendar, event, config, factor -- is held to BOTH predicates on its own, with the
     * control that keeps a wall from passing: a revision known before the cutoff is visible at it.
     */
    public function test_r0028_every_root_resolves_by_effective_time_and_by_knowledge_time(): void
    {
        // ---- identity: five separately recorded roots (listing, instrument, issuer, symbol, board). Exactly one root is late per
        // listing, so each knowledge bound has to hide its own row; a listing effective only after the target is a separate control.
        $this->identityListing(2600, 'IDKNOWN', null);
        foreach (['listing', 'instrument', 'issuer', 'symbol', 'board'] as $i => $root) {
            $this->identityListing(2601 + $i, 'ID'.strtoupper($root), $root);
        }
        $this->identityListing(2610, 'IDFUTURE', null, '2026-05-01');
        $identity = new TemporalIdentityRepository();
        $atTarget = array_column($identity->universeAsOf(self::TARGET, self::CUTOFF), 'ticker_code');
        $uncut = array_column($identity->universeAsOf(self::TARGET), 'ticker_code');
        $this->assertContains('IDKNOWN', $atTarget, 'identity: a listing known before the cutoff must be visible at it');
        foreach (['listing', 'instrument', 'issuer', 'symbol', 'board'] as $root) {
            $this->assertNotContains('ID'.strtoupper($root), $atTarget, 'identity: a listing whose '.$root.' root was recorded after the cutoff must be invisible to it');
            $this->assertContains('ID'.strtoupper($root), $uncut, 'identity: the '.$root.' listing exists, so the fixture is real');
        }
        $this->assertNotContains('IDFUTURE', $atTarget, 'identity: a listing effective only after the target date must not be in its universe');
        $this->assertContains('IDFUTURE', array_column($identity->universeAsOf('2026-05-02', self::CUTOFF), 'ticker_code'),
            'identity: it applies to a later target, so effective time is a filter and not a wall');
        // ---- status and calendar: the same three-way shape (full coverage is the R0011 method; here the root-level controls)
        $this->calendar('2026-03-24', '2026-03-01 00:00:00');
        $this->calendar('2026-03-27', '2026-06-01 00:00:00');
        $this->assertSame('2026-03-24', $this->known(function () { return (new MarketCalendarRepository())->sessionContext(self::TARGET, self::CUTOFF)['trade_date']; }, 'calendar: known before the cutoff, visible at it'), 'calendar: known before the cutoff, visible at it');
        $this->assertThrowsWith('MARKET_CALENDAR_EVIDENCE_MISSING', function () { (new MarketCalendarRepository())->sessionContext('2026-03-27', self::CUTOFF); }, 'calendar: recorded after the cutoff, invisible to it');
        $known = $this->status(5321, 'r0028-known', '2026-03-01 00:00:00', '2026-03-02 00:00:00');
        $recordedLate = $this->status(5322, 'r0028-late', '2026-03-01 00:00:00', '2026-05-02 00:00:00');
        $effectiveLate = $this->status(5323, 'r0028-future', '2026-04-01 00:00:00', '2026-03-02 00:00:00');
        $status = new TemporalTradingStatusRepository();
        $this->assertSame('SUSPENSION', $status->resolveForListing($known, self::TARGET, self::CUTOFF)['status_code'], 'status: known before the cutoff, visible at it');
        $this->assertNotSame('SUSPENSION', $status->resolveForListing($recordedLate, self::TARGET, self::CUTOFF)['status_code'], 'status: recorded after the cutoff, invisible to it');
        $this->assertNotSame('SUSPENSION', $status->resolveForListing($effectiveLate, self::TARGET, self::CUTOFF)['status_code'], 'status: effective after the target, not applied to it');

        // ---- event (corporate action revisions)
        $this->listing(41, 941);
        $this->listing(42, 942);
        $this->listing(43, 943);
        $this->action('r0028-known', 1, 41, 'AUTHORITATIVE_VERIFIED', '2026-03-01 00:00:00');
        $this->action('r0028-late', 1, 42, 'AUTHORITATIVE_VERIFIED', '2026-06-01 00:00:00');
        DB::table('md_corporate_action_revisions')->insert([
            'event_uid' => 'r0028-future', 'revision_number' => 1, 'listing_id' => 43, 'action_type_code' => 'STOCK_SPLIT', 'lifecycle_state' => 'EFFECTIVE',
            'verification_state' => 'AUTHORITATIVE_VERIFIED', 'ex_date' => '2026-05-04', 'recorded_at' => '2026-03-01 00:00:00',
        ]);
        $events = new EventRiskSourceRepository();
        $context = $events->resolveEventRiskContextForTickerIds([941, 942, 943], self::TARGET, self::CUTOFF);
        $this->assertSame(1, (int) ($context[941]['corporate_action_flag'] ?? 0), 'event: known before the cutoff, visible at it');
        $this->assertSame(0, (int) ($context[942]['corporate_action_flag'] ?? 0), 'event: recorded after the cutoff, invisible to it');
        $this->assertSame(0, (int) ($context[943]['corporate_action_flag'] ?? 0), 'event: ex-date after the target, not applied to it');
        $this->assertSame(1, (int) ($events->resolveEventRiskContextForTickerIds([943], '2026-05-04', self::CUTOFF)[943]['corporate_action_flag'] ?? 0),
            'event: it applies to its own ex-date, so effective time is a filter and not a wall');

        // ---- config
        $early = $this->snapshot('2026-01-01 00:00:00', '2026-01-10 00:00:00', 'r0028-early');
        $recordedLaterSnapshot = $this->snapshot('2026-03-01 00:00:00', '2026-06-01 00:00:00', 'r0028-late');
        $effectiveLaterSnapshot = $this->snapshot('2026-05-01 00:00:00', '2026-02-01 00:00:00', 'r0028-future');
        $config = new MarketDataConfigSnapshotRepository();
        $this->assertSame($early, (int) $this->known(function () use ($config) { return $config->resolveForRun(self::TARGET, self::CUTOFF)['config_snapshot_id']; }, 'config: the snapshot known before the cutoff governs the target at it'), 'config: the snapshot known before the cutoff governs the target at it');
        $this->assertSame($recordedLaterSnapshot, (int) $this->known(function () use ($config) { return $config->resolveForRun(self::TARGET, self::AFTER)['config_snapshot_id']; }, 'config: once recorded, the later snapshot governs (so the cutoff is not a wall)'), 'config: once recorded, the later snapshot governs (so the cutoff is not a wall)');
        $this->assertSame($effectiveLaterSnapshot, (int) $this->known(function () use ($config) { return $config->resolveForRun('2026-05-02', self::AFTER)['config_snapshot_id']; }, 'config: a snapshot effective after the target governs only later targets'), 'config: a snapshot effective after the target governs only later targets');
        $this->assertNotSame($effectiveLaterSnapshot, (int) $this->known(function () use ($config) { return $config->resolveForRun(self::TARGET, self::AFTER)['config_snapshot_id']; }, 'config: and not the target before its effective time'), 'config: and not the target before its effective time');

        // ---- factor (the as-known event/factor context of the snapshot service)
        $setKnown = $this->factorSet('r0028-set-known', '2026-03-01 00:00:00');
        $setLate = $this->factorSet('r0028-set-late', '2026-06-01 00:00:00');
        $this->factor($setKnown, 41, '2026-03-20');
        $this->factor($setKnown, 41, '2026-05-01');
        $this->factor($setLate, 41, '2026-03-20');
        $atCutoff = $this->eventFactorContext(self::TARGET, self::CUTOFF);
        $this->assertSame([$setKnown], array_map('intval', array_column($atCutoff['factor_sets'], 'factor_set_id')), 'factor: only the set known at the cutoff, and it is visible (a wall hides it)');
        $this->assertSame(['2026-03-20'], array_column($atCutoff['factors'], 'effective_from'), 'factor: a factor effective after the target is not applied to it');
        $this->assertSame([$setKnown, $setLate], array_map('intval', array_column($this->eventFactorContext(self::TARGET, self::AFTER)['factor_sets'], 'factor_set_id')), 'factor: once recorded the later set is visible');
    }

    /**
     * Ties and corrections are deterministic and ambiguity fails closed -- as KNOWN: a conflicting revision recorded after the
     * cutoff is not an ambiguity at the cutoff, and becomes one the moment it is known.
     */
    public function test_r0028_ties_and_corrections_are_deterministic_and_ambiguity_fails_closed_as_known(): void
    {
        // calendar: two terminal revisions for one date, neither superseding the other
        $this->calendar('2026-03-24', '2026-03-01 00:00:00', ['revision_uid' => hash('sha256', 'r0028-cal-a')]);
        $this->calendar('2026-03-24', '2026-06-01 00:00:00', ['revision_uid' => hash('sha256', 'r0028-cal-b'), 'is_half_day' => 1]);
        $calendar = new MarketCalendarRepository();
        $this->assertSame('2026-03-24', $this->known(function () use ($calendar) { return $calendar->sessionContext(self::TARGET, self::CUTOFF)['trade_date']; }, 'the competing revision is not known at the cutoff, so there is no ambiguity at it'),
            'the competing revision is not known at the cutoff, so there is no ambiguity at it');
        $this->assertThrowsWith('MARKET_CALENDAR_REVISION_CONFLICT', function () use ($calendar) { $calendar->sessionContext(self::TARGET, self::AFTER); },
            'once both are known two terminal revisions are an ambiguity and must fail closed, not pick the latest');
        $this->assertThrowsWith('MARKET_CALENDAR_REVISION_CONFLICT', function () use ($calendar) { $calendar->sessionContext(self::TARGET); },
            'and the uncut read agrees');

        // status: two same-authority revisions of one type that disagree
        $listing = $this->status(5331, 'r0028-tie-a', '2026-03-01 00:00:00', '2026-03-02 00:00:00');
        $this->statusRevision($listing, 'r0028-tie-b', 'SUSPENDED', 'BAR_EXPECTED', '2026-03-01 00:00:00', '2026-06-01 00:00:00');
        $status = new TemporalTradingStatusRepository();
        $atCutoff = $status->resolveForListing($listing, self::TARGET, self::CUTOFF);
        $this->assertSame('SUSPENSION', $atCutoff['status_code'], 'the competing revision is not known at the cutoff, so there is no conflict at it');
        $afterBoth = $status->resolveForListing($listing, self::TARGET, self::AFTER);
        $this->assertSame('TRADING_STATUS_CONFLICT', $afterBoth['reason_code'], 'once both are known the same-authority disagreement fails closed');
        $this->assertSame('CONFLICTING', $afterBoth['status_code'], 'and resolves to the CONFLICTING state, not to the latest or the first');

        // config: same effective time, corrections resolved by knowledge time, and a full tie broken deterministically
        $first = $this->snapshot('2026-02-01 00:00:00', '2026-02-02 00:00:00', 'r0028-tie-first');
        $correction = $this->snapshot('2026-02-01 00:00:00', '2026-06-01 00:00:00', 'r0028-tie-correction');
        $config = new MarketDataConfigSnapshotRepository();
        $this->assertSame($first, (int) $this->known(function () use ($config) { return $config->resolveForRun(self::TARGET, self::CUTOFF)['config_snapshot_id']; }, 'at the cutoff the correction is unknown and the first stands'), 'at the cutoff the correction is unknown and the first stands');
        $this->assertSame($correction, (int) $this->known(function () use ($config) { return $config->resolveForRun(self::TARGET, self::AFTER)['config_snapshot_id']; }, 'once recorded the correction replaces it'), 'once recorded the correction replaces it');
        $twinA = $this->snapshot('2026-02-15 00:00:00', '2026-03-01 00:00:00', 'r0028-twin-a');
        $twinB = $this->snapshot('2026-02-15 00:00:00', '2026-03-01 00:00:00', 'r0028-twin-b');
        $resolved = [];
        for ($i = 0; $i < 3; $i++) {
            $resolved[] = (int) $config->resolveForRun(self::TARGET, '2026-03-02 00:00:00')['config_snapshot_id'];
        }
        $this->assertSame([$twinB, $twinB, $twinB], $resolved, 'an exact tie is broken by the same rule every time (the higher id), never by read order');
        $this->assertGreaterThan($twinA, $twinB);
    }
    // ================================================================================================
    // MD-S041-R0032 -- historical processing uses the calendar revision governed for the replay mode; as-known must not use a
    //                  future calendar correction that was unknown at its cutoff
    // ================================================================================================

    /**
     * As-known mode. The three calendar reads historical processing is built from -- the session of the date, the trading
     * dates of a window and the start of an indicator window -- all take the cutoff, and a correction recorded after it
     * (here: the date is later declared a holiday) changes none of them at the cutoff and all of them once it is known. The
     * original session is complete and verified at the cutoff, so a wall (nothing resolves under a cutoff) fails too.
     * Publication mode is the other half of the sentence: it does not re-resolve a calendar at all, it replays the calendar
     * identity frozen with the publication (B18ReplayPersistedEvidenceBindingTest, "calendar and status revisions").
     */
    public function test_r0032_as_known_historical_processing_uses_the_calendar_known_at_the_cutoff_and_not_a_future_correction(): void
    {
        $this->calendar('2026-03-23', '2026-03-23 18:00:00');
        $original = $this->calendar('2026-03-24', '2026-03-24 18:00:00');
        $this->calendar('2026-03-25', '2026-03-25 18:00:00');
        $this->calendar('2026-03-24', '2026-06-01 00:00:00', [
            'is_trading_day' => 0, 'session_state' => 'CLOSED', 'session_open_at' => null, 'session_close_at' => null, 'completed_at' => null,
            'supersedes_revision_id' => $original,
        ]);
        $calendar = new MarketCalendarRepository();

        // the session of the date
        $context = $this->known(function () use ($calendar) { return $calendar->assertCompletedRegularSession(self::TARGET, self::CUTOFF); }, 'at the cutoff the date is the completed trading session that was on record');
        $this->assertTrue($context['is_trading_day'], 'at the cutoff the date is the completed trading session that was on record');
        $this->assertSame('2026-03-23', $context['prev_trading_day'], 'its neighbours are the ones known at the cutoff');
        $this->assertSame('2026-03-25', $context['next_trading_day']);
        $this->assertThrowsWith('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE', function () use ($calendar) { $calendar->assertCompletedRegularSession(self::TARGET, self::AFTER); },
            'once the correction is known the same date is no longer a trading day, so the cutoff decides');
        $this->assertFalse($calendar->sessionContext(self::TARGET)['is_trading_day'], 'and the uncut read agrees with the later cutoff');

        // the trading dates of a window
        $this->assertSame(['2026-03-23', '2026-03-24', '2026-03-25'], $calendar->tradingDatesBetween('2026-03-23', '2026-03-25', self::CUTOFF),
            'the window at the cutoff contains the date the correction later removes');
        $this->assertSame(['2026-03-23', '2026-03-25'], $calendar->tradingDatesBetween('2026-03-23', '2026-03-25', self::AFTER),
            'and the window once the correction is known does not');

        // the start of an indicator window
        $this->assertSame('2026-03-24', $calendar->tradingDateWindowStart('2026-03-25', 2, true, self::CUTOFF),
            'a two-session window ending 2026-03-25 starts on the date known at the cutoff');
        $this->assertSame('2026-03-23', $calendar->tradingDateWindowStart('2026-03-25', 2, true, self::AFTER),
            'and one session earlier once the correction is known');
    }
    // ================================================================================================
    // fixtures
    // ================================================================================================

    private function statusRevision(int $listingId, string $uid, string $statusType, string $barState, string $effectiveFrom, string $recordedAt): void
    {
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => $listingId, 'instrument_id' => $listingId + 1000, 'status_event_uid' => hash('sha256', $uid),
            'status_type_code' => $statusType, 'status_code' => 'SUSPENSION', 'bar_expectation_state' => $barState,
            'board_code' => 'RG', 'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => str_repeat('b', 64), 'verification_state' => 'VERIFIED', 'full_session_verified' => 1,
            'effective_from' => $effectiveFrom, 'effective_to' => null, 'recorded_at' => $recordedAt,
            'source_observation_id' => (int) DB::table('md_source_observations')->value('source_observation_id'),
            'source_ref' => 'https://www.idx.co.id/notice', 'observed_at' => $recordedAt, 'announced_at' => $recordedAt,
        ]);
    }

    /** One listing with the five identity roots; `$lateRoot` is recorded after the cutoff, the rest long before it. */
    private function identityListing(int $n, string $code, ?string $lateRoot, string $listedDate = '2020-01-02'): void
    {
        $early = '2020-01-01 00:00:00';
        $at = static function (string $root) use ($lateRoot, $early) {
            return $lateRoot === $root ? '2026-06-01 00:00:00' : $early;
        };
        $issuerId = DB::table('md_issuers')->insertGetId(['issuer_uid' => 'F018-ID-ISSUER-'.$n, 'legal_name' => 'Issuer '.$n, 'source_ref' => 'fixture', 'recorded_at' => $at('issuer'), 'created_at' => $early]);
        $instrumentId = DB::table('md_instruments')->insertGetId(['instrument_uid' => 'F018-ID-INSTRUMENT-'.$n, 'issuer_id' => $issuerId, 'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'source_ref' => 'fixture', 'recorded_at' => $at('instrument'), 'created_at' => $early]);
        $listingId = DB::table('md_listings')->insertGetId([
            'listing_uid' => 'F018-ID-LISTING-'.$n, 'legacy_ticker_id' => 9000 + $n, 'instrument_id' => $instrumentId, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
            'board_code' => 'MAIN', 'listed_date' => $listedDate, 'delisted_date' => null, 'listing_state' => 'LISTED', 'source_ref' => 'fixture', 'recorded_at' => $at('listing'), 'created_at' => $early,
        ]);
        DB::table('md_listing_symbols')->insert(['listing_id' => $listingId, 'symbol' => $code, 'symbol_type' => 'EXCHANGE', 'symbol_namespace' => 'IDX', 'effective_from' => '2020-01-02 00:00:00', 'effective_to' => null, 'recorded_at' => $at('symbol'), 'source_ref' => 'fixture', 'change_reason' => 'LISTING']);
        DB::table('md_listing_boards')->insert(['listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'MAIN', 'effective_from' => '2020-01-02 00:00:00', 'effective_to' => null, 'recorded_at' => $at('board'), 'retracted_at' => null, 'source_ref' => 'fixture', 'change_reason' => 'LISTING']);
    }

    private function ticker(int $id, string $code, string $listed, string $recordedAt): void
    {
        DB::table('tickers')->insert(['ticker_id' => $id, 'ticker_code' => $code, 'company_name' => $code.' Tbk', 'is_active' => 1, 'listed_date' => $listed, 'created_at' => $recordedAt]);
    }

    private function snapshot(string $effectiveAt, string $recordedAt, string $seed): int
    {
        $json = json_encode(['seed' => $seed]);

        return (int) DB::table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => hash('sha256', $seed), 'snapshot_schema_version' => (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1'),
            'serialization_version' => 'canonical_json_v1', 'resolved_config_json' => $json, 'config_hash' => hash('sha256', $json),
            'registry_revision' => 'platform_config_registry_v2', 'effective_at' => $effectiveAt, 'recorded_at' => $recordedAt,
            'build_id' => 'f018', 'environment_profile' => (string) config('market_data.governance.environment_profile', 'local'),
            'resolver_version' => 'market_data_config_resolver_v1', 'created_at' => $recordedAt,
        ]);
    }

    private function factorSet(string $uid, string $recordedAt): int
    {
        return (int) DB::table('md_adjustment_factor_sets')->insertGetId([
            'factor_set_uid' => hash('sha256', $uid), 'price_product_code' => 'STRUCTURAL_ADJUSTED', 'factor_formula_version' => 'factor_formula_v1',
            'config_snapshot_id' => 1, 'state' => 'ACTIVE', 'content_hash' => hash('sha256', 'content-'.$uid), 'recorded_at' => $recordedAt, 'created_at' => $recordedAt,
        ]);
    }

    private function factor(int $setId, int $listingId, string $effectiveFrom): void
    {
        DB::table('md_adjustment_factors')->insert([
            'factor_set_id' => $setId, 'listing_id' => $listingId, 'effective_from' => $effectiveFrom, 'price_factor' => '1.000000000000',
            'volume_factor' => '1.000000000000', 'corporate_action_revision_id' => 1, 'created_at' => '2026-03-01 00:00:00',
        ]);
    }

    /** The as-known event/factor context of the snapshot service (private: read through reflection, the same code the replay runs). */
    private function eventFactorContext(string $date, string $cutoff): array
    {
        $service = new AsKnownReplaySnapshotService();
        $method = new ReflectionMethod($service, 'eventFactorContext');
        $method->setAccessible(true);

        return $method->invoke($service, $date, $cutoff);
    }

    /** Runs a read that MUST resolve; if it throws, the test fails with the stated reason instead of an unrelated exception. */
    private function known(callable $read, string $message)
    {
        try {
            return $read();
        } catch (\RuntimeException $e) {
            $this->fail($message.' -- but the read raised: '.$e->getMessage());
        }
    }

    private function assertThrowsWith(string $needle, callable $call, string $message): void
    {
        try {
            $call();
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString($needle, $e->getMessage(), $message);

            return;
        }
        $this->fail($message.' (nothing was thrown)');
    }
    private function calendar(string $date, string $recordedAt, array $over = []): int
    {
        return (int) DB::table('md_market_calendar_revisions')->insertGetId(array_merge([
            'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date,
            'revision_uid' => hash('sha256', 'f018-calendar-'.$date.'-'.$recordedAt.'-'.json_encode($over)), 'timezone' => 'Asia/Jakarta',
            'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
            'session_open_at' => $date.' 09:00:00', 'session_close_at' => $date.' 16:00:00',
            'completed_at' => $date.' 16:00:00', 'recorded_at' => $recordedAt,
            'source_ref' => 'https://www.idx.co.id/calendar', 'source_version' => 'idx-calendar-2026',
            'provenance_tier' => 'VERIFIED', 'reconciled_at' => $date,
            'reconciliation_source_ref' => 'https://www.idx.co.id/calendar',
        ], $over));
    }

    /** A listing with one SUSPENSION revision; returns the listing id. */
    private function status(int $listingId, string $uid, string $effectiveFrom, string $recordedAt): int
    {
        $observationId = $this->statusFoundation($listingId);
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => $listingId, 'instrument_id' => $listingId + 1000, 'status_event_uid' => hash('sha256', $uid),
            'status_type_code' => 'SUSPENDED', 'status_code' => 'SUSPENSION', 'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG', 'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => str_repeat('b', 64), 'verification_state' => 'VERIFIED', 'full_session_verified' => 1,
            'effective_from' => $effectiveFrom, 'effective_to' => null, 'recorded_at' => $recordedAt,
            'source_observation_id' => $observationId, 'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => $recordedAt, 'announced_at' => $recordedAt,
        ]);

        return $listingId;
    }

    /** The later-recorded revision that closes the suspension interval (the schema's way to end a status). */
    private function statusCorrection(int $listingId, string $uid, string $effectiveFrom, string $effectiveTo, string $recordedAt): void
    {
        $original = (int) DB::table('md_trading_status_revisions')->where('listing_id', $listingId)->value('status_revision_id');
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => $listingId, 'instrument_id' => $listingId + 1000, 'status_event_uid' => hash('sha256', $uid.'-correction'),
            'status_type_code' => 'SUSPENDED', 'status_code' => 'SUSPENSION', 'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'supersedes_revision_id' => $original, 'board_code' => 'RG', 'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL', 'source_payload_hash' => str_repeat('b', 64), 'verification_state' => 'VERIFIED',
            'full_session_verified' => 1, 'effective_from' => $effectiveFrom, 'effective_to' => $effectiveTo, 'recorded_at' => $recordedAt,
            'source_observation_id' => (int) DB::table('md_source_observations')->value('source_observation_id'),
            'source_ref' => 'https://www.idx.co.id/notice', 'observed_at' => $recordedAt, 'announced_at' => $recordedAt,
        ]);
    }

    private function statusFoundation(int $listingId): int
    {
        $instrumentId = $listingId + 1000;
        DB::table('md_issuers')->insert([
            'issuer_id' => $instrumentId, 'issuer_uid' => 'issuer-'.$instrumentId, 'legal_name' => 'Issuer '.$instrumentId,
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_instruments')->insert([
            'instrument_id' => $instrumentId, 'instrument_uid' => 'instrument-'.$instrumentId, 'issuer_id' => $instrumentId,
            'instrument_type' => 'EQUITY', 'currency_code' => 'IDR', 'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_listings')->insert([
            'listing_id' => $listingId, 'listing_uid' => 'listing-'.$listingId, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'RG', 'listed_date' => '2023-01-02',
            'listing_state' => 'LISTED', 'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'RG', 'effective_from' => '2023-01-02 00:00:00',
            'effective_to' => null, 'recorded_at' => '2023-01-02 00:00:00', 'retracted_at' => null, 'source_ref' => 'idx', 'change_reason' => 'TEST_FIXTURE',
        ]);

        return (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'f018-status-observation-'.$listingId), 'attempt_uid' => 'f018-as-known',
            'requested_trade_date' => self::TARGET, 'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice', 'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => '2026-03-01 00:00:00', 'adapter_version' => 'test-v1', 'payload_hash' => str_repeat('b', 64),
            'outcome_state' => 'ACCEPTED', 'created_at' => '2026-03-01 00:00:00',
        ]);
    }

    private function listing(int $listingId, int $tickerId): void
    {
        $issuerId = DB::table('md_issuers')->insertGetId([
            'issuer_uid' => 'F018-ISSUER-'.$listingId, 'legal_name' => 'Issuer '.$listingId, 'source_ref' => 'fixture',
            'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);
        $instrumentId = DB::table('md_instruments')->insertGetId([
            'instrument_uid' => 'F018-INSTRUMENT-'.$listingId, 'issuer_id' => $issuerId, 'instrument_type' => 'EQUITY',
            'currency_code' => 'IDR', 'source_ref' => 'fixture', 'recorded_at' => '2020-01-01 00:00:00', 'created_at' => '2020-01-01 00:00:00',
        ]);
        DB::table('md_listings')->insert([
            'listing_id' => $listingId, 'listing_uid' => 'F018-LISTING-'.$listingId, 'legacy_ticker_id' => $tickerId, 'instrument_id' => $instrumentId,
            'exchange_code' => 'IDX', 'market_segment' => 'REGULAR', 'board_code' => 'MAIN', 'listed_date' => '2020-01-02',
            'listing_state' => 'LISTED', 'source_ref' => 'fixture', 'recorded_at' => '2020-01-02 00:00:00', 'created_at' => '2020-01-02 00:00:00',
        ]);
    }

    private function action(string $eventUid, int $revision, int $listingId, string $verification, string $recordedAt, ?int $supersedes = null, string $lifecycle = 'EFFECTIVE'): int
    {
        return (int) DB::table('md_corporate_action_revisions')->insertGetId([
            'event_uid' => $eventUid, 'revision_number' => $revision, 'listing_id' => $listingId, 'action_type_code' => 'STOCK_SPLIT',
            'lifecycle_state' => $lifecycle, 'verification_state' => $verification, 'ex_date' => self::TARGET,
            'recorded_at' => $recordedAt, 'supersedes_revision_id' => $supersedes,
        ]);
    }
}
