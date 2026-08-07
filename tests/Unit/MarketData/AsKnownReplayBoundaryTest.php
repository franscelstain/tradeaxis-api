<?php

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TemporalTradingStatusRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W18 — exact and as-known replay, stage 18.
 *
 * Exit gate: "exact replay matches values/nulls/reasons/lineage/hashes; as-known replay cannot see
 * later identity/status/event/config/factor revisions; no strategy P&L metric menjadi market-data
 * acceptance."
 *
 * Owner contracts:
 *   docs/market_data/book/Replay_Verification_Contract_LOCKED.md
 *   docs/market_data/backtest/Point_In_Time_Backtest_Input_Contract_LOCKED.md
 *
 * As-known replay answers a different question from exact replay. Exact replay asks whether the
 * platform still produces what it produced. As-known replay asks what the platform could have
 * known at a moment — and the only way that can be wrong is by letting a fact recorded later leak
 * backwards, which is indistinguishable from foresight in a backtest.
 *
 * Each root of temporal truth is tested at its own boundary: identity, trading status, and
 * calendar. A cutoff honoured by two of three still leaks.
 */
class AsKnownReplayBoundaryTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const CUTOFF = '2026-04-15 00:00:00';

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

    /**
     * A listing recorded after the cutoff is invisible to a replay run as of that cutoff, even
     * though it is visible today. Without this a backtest would trade an instrument the platform
     * had not yet heard of.
     */
    public function test_identity_recorded_after_the_cutoff_is_invisible(): void
    {
        $this->seedTicker(1, 'EARLY', '2023-01-02', '2023-01-02 00:00:00');
        $this->seedTicker(2, 'LATE', '2023-01-02', '2026-06-01 00:00:00');

        $repository = new TemporalIdentityRepository();

        $asKnown = $repository->universeAsOf('2026-03-24', self::CUTOFF);
        $today = $repository->universeAsOf('2026-03-24');

        $asKnownCodes = array_column($asKnown, 'ticker_code');
        $todayCodes = array_column($today, 'ticker_code');

        $this->assertContains('EARLY', $asKnownCodes);
        $this->assertNotContains('LATE', $asKnownCodes, 'a listing recorded in June cannot be known in April');
        $this->assertContains('LATE', $todayCodes, 'and it is visible without a cutoff, so the fixture is real');
    }

    /**
     * A suspension recorded after the cutoff cannot describe an earlier replay. Knowing about it
     * would let a backtest avoid a loss it had no way to foresee.
     */
    public function test_a_status_revision_recorded_after_the_cutoff_is_invisible(): void
    {
        DB::table('md_trading_status_revisions')->insert([
            'listing_id' => 5150,
            'status_code' => 'SUSPENSION',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'verification_state' => 'VERIFIED',
            'full_session_verified' => 1,
            'effective_from' => '2026-03-01 00:00:00',
            'effective_to' => null,
            // Recorded two months after it took effect, which is ordinary: the exchange announces,
            // the platform records later. The cutoff must respect when it was recorded.
            'recorded_at' => '2026-05-02 00:00:00',
            'source_ref' => 'test_fixture',
        ]);

        $repository = new TemporalTradingStatusRepository();

        $asKnown = $repository->resolveForListing(5150, '2026-03-24', self::CUTOFF);
        $today = $repository->resolveForListing(5150, '2026-03-24');

        $this->assertSame('UNKNOWN', $asKnown['status_code'], 'not yet recorded is not yet knowable');
        $this->assertSame('TRADING_STATUS_NO_EVIDENCE', $asKnown['reason_code']);
        $this->assertSame('SUSPENSION', $today['status_code'], 'and it resolves without a cutoff');
    }

    /**
     * A calendar revision recorded after the cutoff is invisible too. The calendar is the root of
     * bar expectation, so a later correction leaking backwards would silently change which bars a
     * replay believed were owed.
     */
    public function test_a_calendar_revision_recorded_after_the_cutoff_is_invisible(): void
    {
        DB::table('market_calendar')->updateOrInsert(['cal_date' => '2026-03-24'], [
            'is_trading_day' => 1,
            'session_close_time' => '16:00',
            'provenance_tier' => 'VERIFIED',
            'source' => 'test_fixture',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00',
        ]);

        $repository = new MarketCalendarRepository();
        $repository->sessionContext('2026-03-24');

        DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-24')->update([
            'recorded_at' => '2026-06-01 00:00:00',
        ]);

        $this->expectExceptionMessageMatches('/MARKET_CALENDAR_EVIDENCE_MISSING/');
        $repository->sessionContext('2026-03-24', self::CUTOFF);
    }

    /**
     * The three cutoffs are independent. A replay honouring identity but not status would still
     * leak, so the guard asserts every root accepts the parameter rather than assuming symmetry.
     */
    public function test_every_temporal_root_accepts_a_knowledge_cutoff(): void
    {
        foreach ([
            [TemporalIdentityRepository::class, 'universeAsOf', 1],
            [TemporalTradingStatusRepository::class, 'resolveForListing', 2],
            [MarketCalendarRepository::class, 'sessionContext', 1],
        ] as [$class, $method, $cutoffPosition]) {
            $parameters = (new ReflectionMethod($class, $method))->getParameters();

            $this->assertArrayHasKey($cutoffPosition, $parameters, $class.'::'.$method.' must accept a cutoff');
            $this->assertSame('knownAt', $parameters[$cutoffPosition]->getName(), $class.'::'.$method);
        }
    }

    private function seedTicker(int $tickerId, string $code, string $listedDate, string $recordedAt): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $code,
            'company_name' => $code.' Tbk',
            'is_active' => 1,
            'listed_date' => $listedDate,
            'created_at' => $recordedAt,
        ]);
    }
}
