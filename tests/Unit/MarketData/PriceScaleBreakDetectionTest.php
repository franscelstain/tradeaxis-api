<?php

use App\Application\MarketData\Services\PriceScaleBreakDetectionService;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 */
class PriceScaleBreakDetectionTest extends TestCase
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

    private function seedTicker($tickerId, $code): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $code,
            'company_name' => $code.' Tbk',
            'is_active' => 1,
        ]);
    }

    /**
     * @param array<int, array{0:string,1:float,2:float}> $bars [trade_date, open, close]
     */
    private function seedBars($tickerId, array $bars): void
    {
        foreach ($bars as $bar) {
            DB::table('eod_bars')->insert([
                'trade_date' => $bar[0],
                'ticker_id' => $tickerId,
                'open' => $bar[1],
                'high' => max($bar[1], $bar[2]),
                'low' => min($bar[1], $bar[2]),
                'close' => $bar[2],
                'volume' => 1000000,
                'adj_close' => $bar[2],
                'source' => 'YAHOO_FINANCE',
                'run_id' => 1,
                'publication_id' => 1,
                'created_at' => '2026-01-01 00:00:00',
            ]);
        }
    }

    private function detect($apply = true): array
    {
        return (new PriceScaleBreakDetectionService())->detect(null, null, null, $apply);
    }

    /**
     * RMKE 2025-10-06: prior close 1855, open 377. Using open resolves to 1:5 cleanly, while
     * using close (462) would give 4.02 because the stock rose during the ex-split session.
     */
    public function test_detects_a_persistent_split_and_infers_the_ratio_from_open(): void
    {
        $this->seedTicker(1, 'RMKE');
        $this->seedBars(1, [
            ['2025-10-02', 1805, 1810],
            ['2025-10-03', 1810, 1855],
            ['2025-10-06', 377, 462],
            ['2025-10-08', 490, 502],
        ]);

        $result = $this->detect();

        $this->assertCount(1, $result['detected']);
        $break = $result['detected'][0];

        $this->assertSame('2025-10-06', $break['trade_date']);
        $this->assertSame('SCALE_SHIFT', $break['break_type']);
        $this->assertSame('UNEXPLAINED', $break['match_status']);
        $this->assertSame(5.0, (float) $break['inferred_ratio']);
        $this->assertEqualsWithDelta(4.92, $break['implied_ratio'], 0.01);
        $this->assertSame('PRICE_DECREASED', $break['ratio_direction']);
    }

    /**
     * A real split never reverts. A bar that snaps back carries a different adjustment
     * epoch than its neighbours.
     */
    public function test_classifies_a_reverting_bar_as_an_isolated_anomaly(): void
    {
        $this->seedTicker(2, 'MLPT');
        $this->seedBars(2, [
            ['2025-08-12', 60000, 61900],
            ['2025-08-13', 2670, 2670],
            ['2025-08-15', 70000, 70500],
            ['2025-08-19', 68000, 68000],
        ]);

        $result = $this->detect();

        $types = array_column($result['detected'], 'break_type');
        $this->assertContains('ISOLATED_ANOMALY', $types);
    }

    /**
     * At 1-2 IDR one tick is a 100 percent move, so ratio alone cannot separate ordinary
     * trading from a split. Guarding on ratio instead of price would flag these endlessly.
     */
    public function test_ignores_penny_price_oscillation_below_the_minimum_price(): void
    {
        $this->seedTicker(3, 'MKNT');
        $this->seedBars(3, [
            ['2024-04-16', 2, 2],
            ['2024-04-17', 1, 1],
            ['2024-04-18', 2, 2],
            ['2024-04-19', 1, 1],
        ]);

        $result = $this->detect();

        $this->assertSame([], $result['detected']);
        $this->assertGreaterThan(0, $result['skipped_below_min_price']);
    }

    public function test_break_explained_by_a_recorded_corporate_action_is_marked_explained(): void
    {
        $this->seedTicker(4, 'RAJA');
        $this->seedBars(4, [
            ['2026-07-14', 4500, 4590],
            ['2026-07-15', 920, 920],
            ['2026-07-16', 900, 875],
        ]);

        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 4,
            'ticker_code' => 'RAJA',
            'action_date' => '2026-07-16',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_manual',
        ]);

        $this->seedVerifiedMarketCalendarDate('2026-07-14');
        $this->seedVerifiedMarketCalendarDate('2026-07-15');
        $this->seedVerifiedMarketCalendarDate('2026-07-16');

        $result = $this->detect();

        $this->assertCount(1, $result['detected']);
        $this->assertSame('EXPLAINED', $result['detected'][0]['match_status']);
        $this->assertSame('STOCK_SPLIT', $result['detected'][0]['matched_action_type']);
    }

    public function test_only_unexplained_breaks_feed_the_indicator_quarantine(): void
    {
        $this->seedTicker(5, 'SCCO');
        $this->seedBars(5, [
            ['2024-01-31', 9900, 9975],
            ['2024-02-01', 2506, 2500],
            ['2024-02-02', 2510, 2520],
        ]);

        $this->detect();

        $tradingDates = ['2024-02-01', '2024-02-02', '2024-02-05'];
        $contamination = (new PriceScaleBreakRepository())
            ->resolveContaminationForTickerIds([5], $tradingDates);

        $this->assertArrayHasKey(5, $contamination);
        $this->assertSame('2024-02-01', $contamination[5][0]['trade_date']);
        $this->assertSame(2, $contamination[5][0]['depth']);
    }

    /**
     * Dismissal is a deliberate operator statement. Absence of review is not.
     */
    public function test_dismissed_breaks_stop_quarantining_but_detected_ones_do_not(): void
    {
        $this->seedTicker(6, 'PYFA');
        $this->seedBars(6, [
            ['2024-04-15', 1030, 1040],
            ['2024-04-16', 223, 230],
            ['2024-04-17', 228, 232],
        ]);

        $this->detect();

        $repository = new PriceScaleBreakRepository();
        $tradingDates = ['2024-04-16', '2024-04-17', '2024-04-18'];

        $this->assertArrayHasKey(6, $repository->resolveContaminationForTickerIds([6], $tradingDates));

        DB::table('market_data_price_scale_breaks')
            ->where('ticker_id', 6)
            ->update(['review_status' => 'DISMISSED', 'review_note' => 'verified as a genuine price move']);

        $this->assertSame([], $repository->resolveContaminationForTickerIds([6], $tradingDates));
    }

    public function test_detection_is_idempotent(): void
    {
        $this->seedTicker(7, 'RMKE');
        $this->seedBars(7, [
            ['2025-10-03', 1810, 1855],
            ['2025-10-06', 377, 462],
            ['2025-10-08', 490, 502],
        ]);

        $this->detect();
        $this->detect();

        $this->assertSame(1, DB::table('market_data_price_scale_breaks')->where('ticker_id', 7)->count());
    }

    public function test_dry_run_detects_without_persisting(): void
    {
        $this->seedTicker(8, 'RMKE');
        $this->seedBars(8, [
            ['2025-10-03', 1810, 1855],
            ['2025-10-06', 377, 462],
            ['2025-10-08', 490, 502],
        ]);

        $result = (new PriceScaleBreakDetectionService())->detect(null, null, null, false);

        $this->assertCount(1, $result['detected']);
        $this->assertSame(0, DB::table('market_data_price_scale_breaks')->count());
    }
}
