<?php

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- `MD-S041-R0032` (the calendar revision governed for the replay mode) and
 * `MD-S058-R0069` (a historical suspension/unsuspension sequence resolved only from records valid
 * and known under the requested mode).
 *
 * `AsKnownReplayBoundaryTest` proves the negative half of both: a calendar revision recorded after
 * the cutoff is invisible, a status revision recorded after the cutoff is invisible. What neither
 * establishes is that the cutoff is a filter rather than a wall. Every one of those assertions
 * would hold against a repository that returned nothing whenever a cutoff was supplied, and a
 * replay that can see nothing is not a replay that respects knowledge time.
 *
 * So this class supplies the other half for each root, and the sequence case `MD-S058` actually
 * asks for:
 *
 *  - a calendar revision recorded *before* the cutoff resolves *at* that cutoff, beside the same
 *    repository refusing one recorded after it;
 *  - a suspension followed by the lift that closes it, read at two cutoffs and with no cutoff, so
 *    the answer for one trade date changes as knowledge accrues: SUSPENSION while only the
 *    suspension is on record, and no suspension in force once the lift is. A cutoff treated as a
 *    wall gives the same answer at both; a current-state lookup gives the other same answer at
 *    both. Only knowledge-time resolution produces the sequence.
 *
 * The last point is the one `MD-S058-R0069` names explicitly: a current status lookup must never
 * substitute for the proof, and the way to detect a substitution is a fixture whose current answer
 * differs from its as-known answer.
 */
class B18AsKnownTemporalSequenceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const LISTING = 5201;

    private const INSTRUMENT = 6201;

    private const TRADE_DATE = '2026-03-24';

    /** Before the unsuspension was recorded: only the suspension is knowable. */
    private const CUTOFF_BEFORE_LIFT_RECORDED = '2026-03-20 00:00:00';

    /** After the unsuspension was recorded: both are knowable. */
    private const CUTOFF_AFTER_LIFT_RECORDED = '2026-05-10 00:00:00';

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

    // ---- MD-S041-R0032: the calendar revision governed for the mode --------------------------

    /**
     * The anti-vacuity half. A calendar revision recorded before the cutoff resolves at that
     * cutoff, so the cutoff selects by knowledge time rather than refusing every as-known read.
     */
    public function test_a_calendar_revision_recorded_before_the_cutoff_resolves_at_that_cutoff(): void
    {
        $this->seedCalendarRevision('2026-03-01 00:00:00');

        $context = (new MarketCalendarRepository())->sessionContext(self::TRADE_DATE, '2026-03-15 00:00:00');

        $this->assertNotEmpty($context, 'a calendar fact already on record at the cutoff must be visible to it');
        $this->assertSame(self::TRADE_DATE, (string) ($context['cal_date'] ?? $context['trade_date'] ?? null));
    }

    /**
     * The prohibition half, on the same fixture shape: the identical revision recorded after the
     * cutoff is refused rather than used. Read together with the test above, the only difference
     * between resolving and refusing is when the revision was recorded.
     */
    public function test_the_same_calendar_revision_recorded_after_the_cutoff_is_refused(): void
    {
        $this->seedCalendarRevision('2026-06-01 00:00:00');

        // Without a cutoff it resolves, so the fixture itself is sound.
        $this->assertNotEmpty((new MarketCalendarRepository())->sessionContext(self::TRADE_DATE));

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_EVIDENCE_MISSING/');
        (new MarketCalendarRepository())->sessionContext(self::TRADE_DATE, '2026-03-15 00:00:00');
    }

    // ---- MD-S058-R0069: the suspension/unsuspension sequence ---------------------------------

    /**
     * `MD-S058-R0069`. One trade date, one listing, two revisions, three reads.
     *
     * The suspension took effect on 2026-03-10 and was recorded on 2026-03-12. The lift took effect
     * on 2026-03-18 and was recorded on 2026-05-01 -- a real pattern: the exchange acts, the
     * platform learns later. For trade date 2026-03-24:
     *
     *   - as known on 2026-03-20 the listing is still SUSPENDED, because the lift is not yet on
     *     record even though it had already taken effect;
     *   - as known on 2026-05-10, and with no cutoff at all, the superseding revision has closed
     *     the suspension interval before this trade date, so no suspension is in force.
     *
     * A repository that answered from current state would say not-suspended at every cutoff. One
     * that treated the cutoff as a wall would say UNKNOWN at every cutoff. The sequence is what
     * separates knowledge-time resolution from both.
     */
    public function test_a_suspension_lifted_later_is_still_suspended_as_known_before_the_lift_was_recorded(): void
    {
        $this->seedSuspensionSequence();

        $repository = new TemporalTradingStatusRepository();

        $beforeLiftKnown = $repository->resolveForListing(self::LISTING, self::TRADE_DATE, self::CUTOFF_BEFORE_LIFT_RECORDED);
        $afterLiftKnown = $repository->resolveForListing(self::LISTING, self::TRADE_DATE, self::CUTOFF_AFTER_LIFT_RECORDED);
        $today = $repository->resolveForListing(self::LISTING, self::TRADE_DATE);

        $this->assertSame('SUSPENSION', $beforeLiftKnown['status_code'],
            'the lift had taken effect but was not yet recorded, so it may not be visible to a '
                .'replay reading as of a moment before it was known');
        $this->assertSame('BAR_NOT_EXPECTED', $beforeLiftKnown['bar_expectation_state'],
            'and the bar expectation follows the status the replay could actually see');

        $this->assertNotSame('SUSPENSION', $afterLiftKnown['status_code'],
            'once the lift is on record the same trade date resolves differently; if it did not, '
                .'the cutoff would be a wall rather than a knowledge-time filter');
        $this->assertSame($afterLiftKnown['status_code'], $today['status_code'],
            'and the later cutoff agrees with the uncut read, since everything is known by then');
    }

    /**
     * The substitution `MD-S058-R0069` forbids by name: a current status lookup standing in for
     * proof. Here the current answer and the as-known answer differ, so any implementation that
     * reached for current state would be caught rather than coincidentally right.
     */
    public function test_the_current_status_is_not_substituted_for_the_as_known_one(): void
    {
        $this->seedSuspensionSequence();

        $repository = new TemporalTradingStatusRepository();
        $today = $repository->resolveForListing(self::LISTING, self::TRADE_DATE);
        $asKnown = $repository->resolveForListing(self::LISTING, self::TRADE_DATE, self::CUTOFF_BEFORE_LIFT_RECORDED);

        $this->assertNotSame($today['status_code'], $asKnown['status_code'],
            'the fixture must be one where current and as-known disagree, or substituting one for '
                .'the other would be undetectable');
        $this->assertSame('SUSPENSION', $asKnown['status_code']);
    }

    /**
     * And a missing provider bar is not evidence of anything. A listing with no status record at
     * all resolves UNKNOWN with its own reason code rather than being assumed normal, so an absent
     * bar cannot be read as a suspension nor its absence as trading.
     */
    public function test_a_listing_with_no_status_evidence_is_unknown_rather_than_assumed_normal(): void
    {
        $this->seedListingFoundation();

        $resolved = (new TemporalTradingStatusRepository())
            ->resolveForListing(self::LISTING, self::TRADE_DATE, self::CUTOFF_AFTER_LIFT_RECORDED);

        $this->assertSame('UNKNOWN', $resolved['status_code']);
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $resolved['reason_code'],
            'silence is not a status; a missing record must say so rather than resolve to NORMAL');
    }

    // ---- fixtures ----------------------------------------------------------------------------

    private function seedCalendarRevision(string $recordedAt): void
    {
        DB::table('md_market_calendar_revisions')->insert([
            'market_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'cal_date' => self::TRADE_DATE,
            'revision_uid' => hash('sha256', 'b18-calendar-'.self::TRADE_DATE.'-'.$recordedAt),
            'timezone' => 'Asia/Jakarta',
            'is_trading_day' => 1,
            'is_half_day' => 0,
            'session_state' => 'COMPLETED',
            'session_open_at' => self::TRADE_DATE.' 09:00:00',
            'session_close_at' => self::TRADE_DATE.' 16:00:00',
            'completed_at' => self::TRADE_DATE.' 16:00:00',
            'recorded_at' => $recordedAt,
            'source_ref' => 'https://www.idx.co.id/calendar',
            'source_version' => 'idx-calendar-2026',
            'provenance_tier' => 'VERIFIED',
            'reconciled_at' => self::TRADE_DATE,
            'reconciliation_source_ref' => 'https://www.idx.co.id/calendar',
        ]);
    }

    private function seedListingFoundation(): int
    {
        DB::table('md_issuers')->insert([
            'issuer_id' => self::INSTRUMENT, 'issuer_uid' => 'issuer-'.self::INSTRUMENT,
            'legal_name' => 'Issuer '.self::INSTRUMENT, 'recorded_at' => '2023-01-01 00:00:00',
            'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_instruments')->insert([
            'instrument_id' => self::INSTRUMENT, 'instrument_uid' => 'instrument-'.self::INSTRUMENT,
            'issuer_id' => self::INSTRUMENT, 'instrument_type' => 'EQUITY', 'currency_code' => 'IDR',
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_listings')->insert([
            'listing_id' => self::LISTING, 'listing_uid' => 'listing-'.self::LISTING,
            'instrument_id' => self::INSTRUMENT, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
            'board_code' => 'RG', 'listed_date' => '2023-01-02', 'listing_state' => 'LISTED',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => self::LISTING, 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'retracted_at' => null,
            'source_ref' => 'idx', 'change_reason' => 'TEST_FIXTURE',
        ]);

        return (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'b18-status-observation-'.self::LISTING),
            'attempt_uid' => 'b18-as-known-sequence', 'requested_trade_date' => self::TRADE_DATE,
            'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice',
            'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => '2026-03-05 00:00:00', 'adapter_version' => 'test-v1',
            'payload_hash' => str_repeat('b', 64), 'outcome_state' => 'ACCEPTED',
            'created_at' => '2026-03-05 00:00:00',
        ]);
    }

    /**
     * Suspension effective 2026-03-10, recorded 2026-03-12.
     * Lift effective 2026-03-18, recorded 2026-05-01 -- after the suspension took hold and long
     * after it was lifted in fact.
     */
    private function seedSuspensionSequence(): void
    {
        $observationId = $this->seedListingFoundation();
        $hash = str_repeat('b', 64);

        $suspensionRevisionId = (int) DB::table('md_trading_status_revisions')->insertGetId([
            'listing_id' => self::LISTING,
            'instrument_id' => self::INSTRUMENT,
            'status_event_uid' => hash('sha256', 'b18-suspension-'.self::LISTING),
            'status_type_code' => 'SUSPENDED',
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => $hash,
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2026-03-10 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2026-03-12 00:00:00',
            'source_observation_id' => $observationId,
            'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => '2026-03-12 00:00:00',
            'announced_at' => '2026-03-12 00:00:00',
        ]);

        // The lift is recorded as a later revision of the same suspension that closes its
        // effective interval, which is how this schema expresses a status ending: a bare
        // NORMAL row is not a governed status type and would be discarded as ungoverned
        // rather than read as an unsuspension.
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => self::LISTING,
            'instrument_id' => self::INSTRUMENT,
            'status_event_uid' => hash('sha256', 'b18-unsuspension-'.self::LISTING),
            'status_type_code' => 'SUSPENDED',
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'supersedes_revision_id' => $suspensionRevisionId,
            'board_code' => 'RG',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => $hash,
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2026-03-10 00:00:00',
            'effective_to' => '2026-03-18 00:00:00',
            'recorded_at' => '2026-05-01 00:00:00',
            'source_observation_id' => $observationId,
            'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => '2026-05-01 00:00:00',
            'announced_at' => '2026-05-01 00:00:00',
        ]);
    }
}
