<?php

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use App\Application\MarketData\Services\ExpectedBarDecisionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W06 — calendar, session, and temporal trading status, stage 7.
 *
 * Exit gate: "unknown tidak menjadi holiday/normal; current status tidak bocor ke historical
 * date; long suspension tidak diubah menjadi dormancy exclusion."
 *
 * Owner contracts:
 *   docs/market_data/book/Market_Calendar_Requirements_Contract.md
 *   docs/market_data/book/Trading_Status_Source_Contract_LOCKED.md
 */
class CalendarProvenanceAndStatusTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedCalendar(string $date, int $isTradingDay, ?string $tier, string $close = '16:00', int $isHalfDay = 0, ?string $state = null): void
    {
        DB::table('md_market_calendar_revisions')->insert([
            'market_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'cal_date' => $date,
            'revision_uid' => hash('sha256', 'calendar|'.$date.'|'.($tier ?? 'UNKNOWN').'|'.$isTradingDay),
            'timezone' => 'Asia/Jakarta',
            'is_trading_day' => $isTradingDay,
            'is_half_day' => $isHalfDay,
            'session_state' => $state ?: ($isTradingDay ? 'COMPLETED' : 'CLOSED'),
            'session_open_at' => $isTradingDay ? $date.' 09:00:00' : null,
            'session_close_at' => $isTradingDay ? $date.' '.$close.':00' : null,
            'completed_at' => ($state === null || $state === 'COMPLETED') && $isTradingDay ? $date.' '.$close.':00' : null,
            'recorded_at' => $date.' 17:00:00',
            'source_observation_id' => null,
            'supersedes_revision_id' => null,
            'provenance_tier' => $tier,
            'source_ref' => 'https://www.idx.co.id/calendar/'.$date,
            'source_version' => 'idx-calendar-'.$date,
            'reconciled_at' => $tier === 'VERIFIED' ? $date : null,
            'reconciliation_source_ref' => $tier === 'VERIFIED' ? 'https://www.idx.co.id/calendar/'.$date : null,
        ]);
    }

    private function seedStatusFoundation(int $listingId, int $instrumentId, string $hash): int
    {
        DB::table('md_issuers')->insert([
            'issuer_id' => $instrumentId, 'issuer_uid' => 'issuer-'.$instrumentId,
            'legal_name' => 'Issuer '.$instrumentId, 'source_ref' => 'idx',
            'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_instruments')->insert([
            'instrument_id' => $instrumentId, 'instrument_uid' => 'instrument-'.$instrumentId,
            'issuer_id' => $instrumentId, 'instrument_type' => 'EQUITY', 'currency_code' => 'IDR',
            'source_ref' => 'idx', 'recorded_at' => '2023-01-01 00:00:00', 'created_at' => '2023-01-01 00:00:00',
        ]);
        DB::table('md_listings')->insert([
            'listing_id' => $listingId, 'listing_uid' => 'listing-'.$listingId,
            'instrument_id' => $instrumentId, 'exchange_code' => 'IDX', 'market_segment' => 'REGULAR',
            'board_code' => 'RG', 'listed_date' => '2023-01-02', 'delisted_date' => null,
            'source_ref' => 'idx', 'listing_state' => 'LISTED',
            'recorded_at' => '2023-01-02 00:00:00', 'created_at' => '2023-01-02 00:00:00',
        ]);
        DB::table('md_listing_boards')->insert([
            'listing_id' => $listingId, 'market_segment' => 'REGULAR', 'board_code' => 'RG',
            'effective_from' => '2023-01-02 00:00:00', 'effective_to' => null,
            'recorded_at' => '2023-01-02 00:00:00', 'retracted_at' => null,
            'source_ref' => 'idx', 'change_reason' => 'TEST_FIXTURE',
        ]);

        return (int) DB::table('md_source_observations')->insertGetId([
            'observation_uid' => hash('sha256', 'observation|'.$listingId.'|'.$hash),
            'attempt_uid' => 'status-test', 'requested_trade_date' => '2026-06-01',
            'source_mode' => 'authority_document', 'source_name' => 'IDX', 'provider' => 'IDX',
            'sanitized_request_identity' => 'https://www.idx.co.id/notice',
            'response_status' => 200, 'content_type' => 'application/json',
            'acquired_at' => '2026-06-01 17:00:00', 'adapter_version' => 'test-v1',
            'payload_hash' => $hash, 'outcome_state' => 'ACCEPTED',
            'created_at' => '2026-06-01 17:00:00',
        ]);
    }

    private function seedStatusRevision(int $listingId, int $instrumentId, int $observationId, string $hash, array $override = []): int
    {
        $row = array_merge([
            'listing_id' => $listingId, 'instrument_id' => $instrumentId,
            'status_event_uid' => hash('sha256', 'status|'.$listingId.'|'.json_encode($override)),
            'status_type_code' => 'SUSPENDED', 'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED', 'board_code' => 'RG',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => $hash, 'full_session_verified' => 1,
            'effective_from' => '2026-06-01 00:00:00', 'effective_to' => null,
            'recorded_at' => '2026-06-01 17:00:00', 'retracted_at' => null,
            'source_observation_id' => $observationId, 'supersedes_revision_id' => null,
            'source_ref' => 'https://www.idx.co.id/notice', 'verification_state' => 'VERIFIED',
            'observed_at' => '2026-06-01 00:00:00', 'announced_at' => '2026-06-01 00:00:00',
        ], $override);

        return (int) DB::table('md_trading_status_revisions')->insertGetId($row);
    }

    /**
     * A projected row is an assumption about a date. Treating it as expected produces a
     * missing-bar finding on every public holiday beyond the exchange's published horizon.
     * Production evidence: 2028 through 2030 carry zero recorded national holidays while
     * 2023 through 2027 carry 21 to 28 each.
     */
    public function test_a_projected_calendar_date_is_never_expected(): void
    {
        $this->seedCalendar('2028-06-15', 1, 'PROJECTED');

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED/');
        (new MarketCalendarRepository())->assertCompletedRegularSession('2028-06-15');
    }

    /**
     * An absent tier is not an optimistic default. A row nobody classified is unknown, and
     * unknown must not become normal.
     */
    public function test_an_unclassified_calendar_date_is_never_expected(): void
    {
        $this->seedCalendar('2026-06-15', 1, null);

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED/');
        (new MarketCalendarRepository())->assertCompletedRegularSession('2026-06-15');
    }

    /**
     * A verified trading day whose session has completed resolves normally, and carries its tier
     * so a consumer can tell which evidence class produced it.
     */
    public function test_a_verified_completed_session_resolves_and_carries_its_tier(): void
    {
        $this->seedCalendar('2026-06-15', 1, 'VERIFIED');

        $context = (new MarketCalendarRepository())->assertCompletedRegularSession('2026-06-15');

        $this->assertSame('VERIFIED', $context['provenance_tier']);
        $this->assertSame('COMPLETED', $context['session_state']);
        $this->assertTrue($context['is_trading_day']);
    }

    /**
     * A verified non-trading day is refused for its own reason, distinct from the provenance
     * refusal. Conflating the two would hide which evidence was missing.
     */
    public function test_a_verified_holiday_is_refused_as_a_non_trading_day(): void
    {
        $this->seedCalendar('2026-06-16', 0, 'VERIFIED');

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE/');
        (new MarketCalendarRepository())->assertCompletedRegularSession('2026-06-16');
    }

    /**
     * Calendar evidence that does not exist fails closed rather than being guessed from
     * weekday arithmetic.
     */
    public function test_a_date_with_no_calendar_evidence_fails_closed(): void
    {
        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_EVIDENCE_MISSING/');
        (new MarketCalendarRepository())->sessionContext('2026-06-17');
    }

    /**
     * Current status must not leak backwards. A suspension recorded to start later cannot
     * describe a date before it took effect.
     */
    public function test_current_status_does_not_leak_into_an_earlier_date(): void
    {
        $hash = str_repeat('a', 64);
        $observationId = $this->seedStatusFoundation(4242, 5242, $hash);
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => 4242,
            'instrument_id' => 5242,
            'status_event_uid' => hash('sha256', 'status-4242'),
            'status_type_code' => 'SUSPENDED',
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => $hash,
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2026-05-01 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2026-05-01 00:00:00',
            'source_observation_id' => $observationId,
            'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => '2026-05-01 00:00:00',
            'announced_at' => '2026-05-01 00:00:00',
        ]);

        $repository = new TemporalTradingStatusRepository();

        $before = $repository->resolveForListing(4242, '2026-04-01');
        $after = $repository->resolveForListing(4242, '2026-06-01');

        $this->assertSame('UNKNOWN', $before['status_code'], 'a later suspension must not describe an earlier date');
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $before['reason_code']);
        $this->assertSame('SUSPENSION', $after['status_code']);
    }

    /**
     * A listing with no status evidence resolves to unknown, never to normal trading. Absence
     * of a suspension record is not proof that none existed.
     */
    public function test_absent_status_evidence_resolves_to_unknown_not_normal(): void
    {
        $this->seedStatusFoundation(9999, 5999, str_repeat('c', 64));
        $status = (new TemporalTradingStatusRepository())->resolveForListing(9999, '2026-06-01');

        $this->assertSame('UNKNOWN', $status['status_code']);
        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $status['reason_code']);
    }

    /**
     * A long suspension stays a suspension. Reclassifying it as dormancy would move it out of
     * the coverage denominator and hide a real market state as a liquidity characteristic.
     */
    public function test_a_long_suspension_is_not_reclassified_as_dormancy(): void
    {
        $hash = str_repeat('b', 64);
        $observationId = $this->seedStatusFoundation(4343, 5343, $hash);
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => 4343,
            'instrument_id' => 5343,
            'status_event_uid' => hash('sha256', 'status-4343'),
            'status_type_code' => 'SUSPENDED',
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => 'RG',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_OFFICIAL',
            'source_payload_hash' => $hash,
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2024-01-01 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2024-01-01 00:00:00',
            'source_observation_id' => $observationId,
            'source_ref' => 'https://www.idx.co.id/notice',
            'observed_at' => '2024-01-01 00:00:00',
            'announced_at' => '2024-01-01 00:00:00',
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(4343, '2026-06-01');

        $this->assertSame('SUSPENSION', $status['status_code'], 'a suspension carried for years is still a suspension');
        $this->assertNotSame('DORMANT', $status['status_code']);
        $this->assertSame('BAR_NOT_EXPECTED', $status['bar_expectation_state']);
    }

    public function test_wall_clock_cannot_manufacture_session_completion(): void
    {
        $this->seedCalendar('2026-06-18', 1, 'VERIFIED', '16:00', 0, 'SCHEDULED');
        Carbon::setTestNow('2026-06-18 20:00:00');

        $repository = new MarketCalendarRepository();
        $this->assertSame('SCHEDULED', $repository->sessionContext('2026-06-18')['session_state']);
        $this->assertSame(1, DB::table('md_market_calendar_revisions')->where('cal_date', '2026-06-18')->count());

        $this->expectExceptionMessageMatches('/MARKET_SESSION_NOT_COMPLETED/');
        $repository->assertCompletedRegularSession('2026-06-18');
    }

    public function test_derived_or_partial_status_cannot_remove_a_bar_from_expectation(): void
    {
        $hash = str_repeat('d', 64);
        $observationId = $this->seedStatusFoundation(4545, 5545, $hash);
        DB::table('md_trading_status_source_registry')->insert([
            'source_name' => 'THIRD_PARTY', 'status_type_code' => 'SUSPENDED',
            'authority_class' => 'DERIVED_REFERENCE', 'priority' => 10, 'active' => 1,
            'source_ref_pattern' => null, 'created_at' => '2026-06-01 00:00:00', 'updated_at' => '2026-06-01 00:00:00',
        ]);
        $this->seedStatusRevision(4545, 5545, $observationId, $hash, [
            'source_name' => 'THIRD_PARTY', 'authority_class' => 'DERIVED_REFERENCE',
            'source_ref' => 'https://reference.example/status',
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(4545, '2026-06-01');
        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_DERIVED_REFERENCE_NON_AUTHORITATIVE', $status['reason_code']);

        DB::table('md_trading_status_revisions')->delete();
        $this->seedStatusRevision(4545, 5545, $observationId, $hash, ['full_session_verified' => 0]);
        $status = (new TemporalTradingStatusRepository())->resolveForListing(4545, '2026-06-01');
        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_FULL_SESSION_NOT_VERIFIED', $status['reason_code']);
    }

    public function test_same_priority_authoritative_conflict_holds_instead_of_using_recency(): void
    {
        $hash = str_repeat('e', 64);
        $observationId = $this->seedStatusFoundation(4646, 5646, $hash);
        $this->seedStatusRevision(4646, 5646, $observationId, $hash);
        $this->seedStatusRevision(4646, 5646, $observationId, $hash, [
            'status_event_uid' => hash('sha256', 'conflict-4646'),
            'status_code' => 'ACTIVE', 'bar_expectation_state' => 'BAR_EXPECTED',
            'recorded_at' => '2026-06-01 18:00:00',
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(4646, '2026-06-01');
        $this->assertSame('CONFLICTING', $status['status_code']);
        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_CONFLICT', $status['reason_code']);
    }

    public function test_expected_bar_decision_is_explainable_and_half_day_is_normal(): void
    {
        $this->seedCalendar('2026-06-19', 1, 'VERIFIED', '12:00', 1);
        $hash = str_repeat('f', 64);
        $observationId = $this->seedStatusFoundation(4747, 5747, $hash);
        $this->seedStatusRevision(4747, 5747, $observationId, $hash, [
            'status_type_code' => 'UNSUSPENDED', 'status_code' => 'ACTIVE',
            'bar_expectation_state' => 'BAR_EXPECTED', 'full_session_verified' => 0,
            'effective_from' => '2026-06-19 00:00:00', 'effective_to' => '2026-06-20 00:00:00',
            'observed_at' => '2026-06-19 00:00:00', 'announced_at' => '2026-06-19 00:00:00',
        ]);

        $decision = (new ExpectedBarDecisionService())->decideForListing(4747, '2026-06-19');

        $this->assertSame('EXPECTED', $decision['expectation_state']);
        $this->assertSame('BAR_EXPECTED', $decision['bar_expectation_state']);
        $this->assertTrue($decision['is_half_day']);
        $this->assertNotEmpty($decision['calendar_revision_uid']);
        $this->assertNotEmpty($decision['trading_status_revision_ids']);
    }

    public function test_status_board_mismatch_fails_closed(): void
    {
        $hash = str_repeat('1', 64);
        $observationId = $this->seedStatusFoundation(4848, 5848, $hash);
        $this->seedStatusRevision(4848, 5848, $observationId, $hash, ['board_code' => 'TN']);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(4848, '2026-06-01');

        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_BOARD_SCOPE_MISMATCH', $status['reason_code']);
    }

    public function test_exact_date_status_cannot_carry_forward_without_an_end(): void
    {
        $hash = str_repeat('2', 64);
        $observationId = $this->seedStatusFoundation(4949, 5949, $hash);
        $this->seedStatusRevision(4949, 5949, $observationId, $hash, [
            'status_type_code' => 'UNSUSPENDED', 'status_code' => 'ACTIVE',
            'bar_expectation_state' => 'BAR_EXPECTED', 'full_session_verified' => 0,
            'effective_to' => null,
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(4949, '2026-06-01');

        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_TYPE_UNGOVERNED', $status['reason_code']);
    }

    public function test_governed_operator_entry_requires_and_carries_explicit_authority_context(): void
    {
        $hash = str_repeat('3', 64);
        $observationId = $this->seedStatusFoundation(5050, 6050, $hash);
        $this->seedStatusRevision(5050, 6050, $observationId, $hash, [
            'source_name' => 'GOVERNED_OPERATOR_ENTRY', 'authority_class' => 'OPERATOR_ENTERED',
            'operator_name' => 'Market Operations', 'governed_reason_code' => 'IDX_NOTICE_TRANSCRIPTION',
            'authoritative_source_ref' => 'https://www.idx.co.id/notice/5050',
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(5050, '2026-06-01');

        $this->assertSame('BAR_NOT_EXPECTED', $status['bar_expectation_state']);
        $this->assertSame('OPERATOR_ENTERED', $status['authority_class']);
    }

    public function test_successor_status_revision_replaces_its_predecessor_without_conflict(): void
    {
        $hash = str_repeat('4', 64);
        $observationId = $this->seedStatusFoundation(5151, 6151, $hash);
        $oldId = $this->seedStatusRevision(5151, 6151, $observationId, $hash);
        $newId = $this->seedStatusRevision(5151, 6151, $observationId, $hash, [
            'status_event_uid' => hash('sha256', 'successor-5151'),
            'status_code' => 'ACTIVE', 'bar_expectation_state' => 'BAR_EXPECTED',
            'full_session_verified' => 0, 'supersedes_revision_id' => $oldId,
            'recorded_at' => '2026-06-01 18:00:00',
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(5151, '2026-06-01');

        $this->assertSame('ACTIVE', $status['status_code']);
        $this->assertSame([$newId], $status['status_revision_ids']);
    }

    public function test_calendar_revision_conflict_and_incomplete_verification_both_fail_closed(): void
    {
        $this->seedCalendar('2026-06-22', 1, 'VERIFIED');
        $this->seedCalendar('2026-06-22', 0, 'VERIFIED');
        try {
            (new MarketCalendarRepository())->sessionContext('2026-06-22');
            $this->fail('Conflicting terminal revisions must not be selected by recency.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('MARKET_CALENDAR_REVISION_CONFLICT', $e->getMessage());
        }

        DB::table('md_market_calendar_revisions')->delete();
        $this->seedCalendar('2026-06-23', 1, 'VERIFIED');
        DB::table('md_market_calendar_revisions')->update(['source_version' => null]);

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_VERIFICATION_EVIDENCE_INCOMPLETE/');
        (new MarketCalendarRepository())->assertCompletedRegularSession('2026-06-23');
    }

    public function test_projected_calendar_revision_becomes_verified_only_after_known_successor(): void
    {
        $this->seedCalendar('2026-06-24', 1, 'PROJECTED');
        $old = (int) DB::table('md_market_calendar_revisions')->value('calendar_revision_id');
        DB::table('md_market_calendar_revisions')->insert([
            'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => '2026-06-24',
            'revision_uid' => hash('sha256', 'calendar-successor-2026-06-24'), 'timezone' => 'Asia/Jakarta',
            'is_trading_day' => 1, 'is_half_day' => 0, 'session_state' => 'COMPLETED',
            'session_open_at' => '2026-06-24 09:00:00', 'session_close_at' => '2026-06-24 16:00:00',
            'completed_at' => '2026-06-24 16:00:00', 'recorded_at' => '2026-06-25 09:00:00',
            'supersedes_revision_id' => $old, 'source_ref' => 'https://www.idx.co.id/calendar/2026-06-24',
            'source_version' => 'idx-calendar-2026-06-24-v2', 'provenance_tier' => 'VERIFIED',
            'reconciled_at' => '2026-06-25 09:00:00',
            'reconciliation_source_ref' => 'https://www.idx.co.id/calendar/2026-06-24',
        ]);

        $repository = new MarketCalendarRepository();
        $this->assertSame('PROJECTED', $repository->sessionContext('2026-06-24', '2026-06-24 18:00:00')['provenance_tier']);
        $this->assertSame('VERIFIED', $repository->assertCompletedRegularSession('2026-06-24', '2026-06-25 09:00:00')['provenance_tier']);
    }

    public function test_trading_window_excludes_a_verified_label_without_reconciliation_evidence(): void
    {
        $this->seedCalendar('2026-06-25', 1, 'VERIFIED');
        DB::table('md_market_calendar_revisions')->update(['reconciliation_source_ref' => null]);

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE/');
        (new MarketCalendarRepository())->tradingDateWindowStart('2026-06-25', 1);
    }

    public function test_exchange_authority_requires_an_accepted_exchange_observation(): void
    {
        $hash = str_repeat('5', 64);
        $observationId = $this->seedStatusFoundation(5252, 6252, $hash);
        DB::table('md_source_observations')->where('source_observation_id', $observationId)
            ->update(['source_name' => 'THIRD_PARTY', 'provider' => 'THIRD_PARTY']);
        $this->seedStatusRevision(5252, 6252, $observationId, $hash);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(5252, '2026-06-01');

        $this->assertSame('BAR_EXPECTATION_UNKNOWN', $status['bar_expectation_state']);
        $this->assertSame('TRADING_STATUS_SOURCE_OBSERVATION_INVALID', $status['reason_code']);
    }
}
