<?php

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
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
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedCalendar(string $date, int $isTradingDay, ?string $tier, string $close = '16:00'): void
    {
        DB::table('market_calendar')->updateOrInsert(['cal_date' => $date], [
            'is_trading_day' => $isTradingDay,
            'session_close_time' => $close,
            'provenance_tier' => $tier,
            'source' => 'test_fixture',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00',
        ]);
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
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => 4242,
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2026-05-01 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2026-05-01 00:00:00',
            'source_ref' => 'test_fixture',
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
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => 4343,
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2024-01-01 00:00:00',
            'effective_to' => null,
            'recorded_at' => '2024-01-01 00:00:00',
            'source_ref' => 'test_fixture',
        ]);

        $status = (new TemporalTradingStatusRepository())->resolveForListing(4343, '2026-06-01');

        $this->assertSame('SUSPENSION', $status['status_code'], 'a suspension carried for years is still a suspension');
        $this->assertNotSame('DORMANT', $status['status_code']);
        $this->assertSame('BAR_NOT_EXPECTED', $status['bar_expectation_state']);
    }
}
