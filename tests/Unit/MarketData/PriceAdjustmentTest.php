<?php

use App\Application\MarketData\Services\CorporateActionDerivationService;
use App\Application\MarketData\Services\IndicatorVectorService;
use App\Application\MarketData\Services\PriceScaleBreakDetectionService;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
 */
class PriceAdjustmentTest extends TestCase
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

    private function config(array $factors = [])
    {
        return [
            'set_version' => 'ind_v1',
            'price_basis_default' => 'close',
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
            'sector_code' => 'G',
            'price_adjustment_factors' => $factors,
            'atr_contamination_horizon_days' => 60,
        ];
    }

    /** A 1:5 split on day 30: prices before it are five times higher, volume five times lower. */
    private function splitBars($days = 55, $splitIndex = 30)
    {
        $rows = [];
        for ($i = 1; $i <= $days; $i++) {
            $base = 100 + $i;
            $preSplit = $i < $splitIndex;
            $price = $preSplit ? $base * 5 : $base;
            $volume = $preSplit ? 200000 : 1000000;

            $rows[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-04-01 +'.($i - 1).' days')),
                'open' => $price - 1,
                'high' => $price + 1,
                'low' => $price - 2,
                'close' => $price,
                'adj_close' => $price,
                'volume' => $volume,
            ];
        }

        return $rows;
    }

    private function splitDate($splitIndex = 30)
    {
        return date('Y-m-d', strtotime('2026-04-01 +'.($splitIndex - 1).' days'));
    }

    public function test_without_a_factor_the_split_distorts_the_indicators(): void
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(1, $this->splitBars(), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        // ma50 spans the split, so it averages two different price scales and lands far
        // above the close of 155.
        $this->assertGreaterThan(200, $row['ma50']);
        // close_vs_ma50_pct therefore reports a deep discount that never happened.
        $this->assertLessThan(-20, $row['close_vs_ma50_pct']);
    }

    public function test_applying_the_factor_makes_the_window_continuous(): void
    {
        $factors = [[
            'ex_date' => $this->splitDate(),
            'price_factor' => 0.2,
            'volume_factor' => 5.0,
        ]];

        $service = new IndicatorVectorService();
        $row = $service->buildRow(1, $this->splitBars(), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config($factors));

        // With pre-split prices divided by five, ma50 sits in the same range as the close.
        $this->assertLessThan(160, $row['ma50']);
        $this->assertGreaterThan(120, $row['ma50']);
    }

    public function test_bars_on_or_after_the_ex_date_are_left_alone(): void
    {
        $factors = [[
            'ex_date' => $this->splitDate(),
            'price_factor' => 0.2,
            'volume_factor' => 5.0,
        ]];

        $service = new IndicatorVectorService();
        $unadjusted = $service->buildRow(1, $this->splitBars(), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());
        $adjusted = $service->buildRow(1, $this->splitBars(), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config($factors));

        // hh20 covers only post-split bars, so adjustment must not change it.
        $this->assertSame($unadjusted['hh20'], $adjusted['hh20']);
    }

    public function test_two_splits_in_one_window_compound(): void
    {
        $exOne = date('Y-m-d', strtotime('2026-04-01 +10 days'));
        $exTwo = date('Y-m-d', strtotime('2026-04-01 +30 days'));

        $service = new IndicatorVectorService();

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('applyPriceAdjustment');
        $method->setAccessible(true);

        $bars = [
            ['trade_date' => '2026-04-05', 'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'adj_close' => 100, 'volume' => 100],
            ['trade_date' => '2026-04-20', 'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'adj_close' => 100, 'volume' => 100],
            ['trade_date' => '2026-05-10', 'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'adj_close' => 100, 'volume' => 100],
        ];

        $factors = [
            ['ex_date' => $exOne, 'price_factor' => 0.5, 'volume_factor' => 2.0],
            ['ex_date' => $exTwo, 'price_factor' => 0.2, 'volume_factor' => 5.0],
        ];

        $adjustment = $method->invoke($service, $bars, $this->config($factors));
        $result = $adjustment['bars'];

        // The vector now reports which product the adjusted series is, so a consumer can tell an
        // adjusted window from an unadjusted one.
        $this->assertSame('STRUCTURAL_ADJUSTED', $adjustment['price_product_code']);

        // Before both events: 0.5 * 0.2 = 0.1
        $this->assertEqualsWithDelta(10.0, $result[0]['close'], 0.0001);
        // Between them: only the second applies
        $this->assertEqualsWithDelta(20.0, $result[1]['close'], 0.0001);
        // After both: untouched
        $this->assertEqualsWithDelta(100.0, $result[2]['close'], 0.0001);

        // Volume moves the opposite way.
        $this->assertEqualsWithDelta(1000.0, $result[0]['volume'], 0.0001);
    }

    public function test_an_action_carrying_a_factor_stops_contaminating(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 1, 'ticker_code' => 'RAJA', 'company_name' => 'RAJA Tbk', 'is_active' => 1]);

        $repository = new EventRiskSourceRepository();
        $tradingDates = ['2026-07-13', '2026-07-14', '2026-07-15', '2026-07-16'];

        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'RAJA',
            'action_date' => '2026-07-15',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_manual',
        ]);

        $this->assertArrayHasKey(
            1,
            $repository->resolveCorporateActionContaminationForTickerIds([1], $tradingDates),
            'without a factor the action must still quarantine'
        );

        DB::table('market_data_corporate_actions')->where('ticker_id', 1)->update([
            'ex_date' => '2026-07-15',
            'price_adjustment_factor' => 0.2,
            'volume_adjustment_factor' => 5,
            // A factor alone no longer suppresses quarantine; it must also say where it came from.
            'adjustment_source' => 'EXCHANGE_ANNOUNCEMENT',
        ]);

        $this->assertSame(
            [],
            $repository->resolveCorporateActionContaminationForTickerIds([1], $tradingDates),
            'with a factor the window is adjusted, so nothing is left to quarantine'
        );
    }

    /** A factor of exactly 1 adjusts nothing and must not suppress the quarantine. */
    public function test_a_neutral_factor_does_not_suppress_the_quarantine(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 2, 'ticker_code' => 'TEST', 'company_name' => 'TEST Tbk', 'is_active' => 1]);

        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 2,
            'ticker_code' => 'TEST',
            'action_date' => '2026-07-15',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_manual',
            'ex_date' => '2026-07-15',
            'price_adjustment_factor' => 1,
        ]);

        $this->assertArrayHasKey(
            2,
            (new EventRiskSourceRepository())->resolveCorporateActionContaminationForTickerIds(
                [2],
                ['2026-07-13', '2026-07-14', '2026-07-15']
            )
        );
    }

    public function test_price_break_detection_never_authors_an_event_or_factor(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 3, 'ticker_code' => 'SCCO', 'company_name' => 'SCCO Tbk', 'is_active' => 1]);

        foreach ([['2024-01-31', 9900, 9975], ['2024-02-01', 2506, 2500], ['2024-02-02', 2510, 2520]] as $bar) {
            DB::table('eod_bars')->insert([
                'trade_date' => $bar[0], 'ticker_id' => 3,
                'open' => $bar[1], 'high' => max($bar[1], $bar[2]), 'low' => min($bar[1], $bar[2]),
                'close' => $bar[2], 'volume' => 1000000, 'adj_close' => $bar[2],
                'source' => 'YAHOO_FINANCE', 'run_id' => 1, 'publication_id' => 1,
                'created_at' => '2026-01-01 00:00:00',
            ]);
        }

        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);
        $result = (new CorporateActionDerivationService())->derive(true);

        $this->assertSame([], $result['derived']);
        $this->assertSame('DETECTION_ONLY', $result['capability_state']);
        $this->assertSame('CORPORATE_ACTION_AUTHORITATIVE_EVIDENCE_REQUIRED', $result['skipped'][0]['reason_code']);

        $action = DB::table('market_data_corporate_actions')->where('ticker_id', 3)->first();
        $this->assertNull($action);
    }

    private function seedBar($tickerId, $date, $open, $close): void
    {
        DB::table('eod_bars')->insert([
            'trade_date' => $date, 'ticker_id' => $tickerId,
            'open' => $open, 'high' => max($open, $close), 'low' => min($open, $close),
            'close' => $close, 'volume' => 1000000, 'adj_close' => $close,
            'source' => 'YAHOO_FINANCE', 'run_id' => 1, 'publication_id' => 1,
            'created_at' => '2026-01-01 00:00:00',
        ]);
    }

    /**
     * A recorded action whose series shows no material gap is evidence that the window is
     * continuous, so quarantining it protects nothing.
     */
    public function test_price_continuity_alone_does_not_verify_a_recorded_action(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 10, 'ticker_code' => 'CALM', 'company_name' => 'CALM Tbk', 'is_active' => 1]);

        $this->seedBar(10, '2025-03-10', 1000, 1005);
        $this->seedBar(10, '2025-03-11', 1006, 1010);
        $this->seedBar(10, '2025-03-12', 1011, 1015);

        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 10, 'ticker_code' => 'CALM',
            'action_date' => '2025-03-11', 'action_type' => 'RIGHTS_ISSUE',
            'source_name' => 'idx_manual',
        ]);

        $repository = new EventRiskSourceRepository();
        $dates = ['2025-03-10', '2025-03-11', '2025-03-12'];

        $this->assertArrayHasKey(10, $repository->resolveCorporateActionContaminationForTickerIds([10], $dates));

        (new CorporateActionDerivationService())->checkRecordedActions(true);

        $action = DB::table('market_data_corporate_actions')->where('ticker_id', 10)->first();
        $this->assertNull($action->continuity_check_status);
        $this->assertNull($action->price_adjustment_factor, 'a continuous series needs no factor');

        $this->assertArrayHasKey(10, $repository->resolveCorporateActionContaminationForTickerIds([10], $dates));
    }

    /**
     * A rights issue commonly moves price 10 to 30 percent, exactly the range ordinary
     * trading also produces. Absorbing that into an adjustment would corrupt real returns.
     */
    public function test_an_ambiguous_gap_keeps_the_quarantine_and_gets_no_factor(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 11, 'ticker_code' => 'AMBG', 'company_name' => 'AMBG Tbk', 'is_active' => 1]);

        $this->seedBar(11, '2025-03-10', 1000, 1000);
        $this->seedBar(11, '2025-03-11', 850, 860);
        $this->seedBar(11, '2025-03-12', 861, 870);

        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 11, 'ticker_code' => 'AMBG',
            'action_date' => '2025-03-11', 'action_type' => 'RIGHTS_ISSUE',
            'source_name' => 'idx_manual',
        ]);

        (new CorporateActionDerivationService())->checkRecordedActions(true);

        $action = DB::table('market_data_corporate_actions')->where('ticker_id', 11)->first();
        $this->assertNull($action->continuity_check_status);
        $this->assertNull($action->price_adjustment_factor);

        $this->assertArrayHasKey(
            11,
            (new EventRiskSourceRepository())->resolveCorporateActionContaminationForTickerIds(
                [11],
                ['2025-03-10', '2025-03-11', '2025-03-12']
            )
        );
    }

    public function test_a_recorded_action_beyond_the_exchange_band_still_requires_authoritative_terms(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 12, 'ticker_code' => 'BIGG', 'company_name' => 'BIGG Tbk', 'is_active' => 1]);

        $this->seedBar(12, '2025-03-10', 5000, 5000);
        $this->seedBar(12, '2025-03-11', 1000, 1010);
        $this->seedBar(12, '2025-03-12', 1011, 1020);

        DB::table('market_data_corporate_actions')->insert([
            'ticker_id' => 12, 'ticker_code' => 'BIGG',
            'action_date' => '2025-03-11', 'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_manual',
        ]);

        (new CorporateActionDerivationService())->checkRecordedActions(true);

        $action = DB::table('market_data_corporate_actions')->where('ticker_id', 12)->first();
        $this->assertNull($action->continuity_check_status);
        $this->assertNull($action->price_adjustment_factor);
        $this->assertNull($action->ex_date);

        $this->assertArrayHasKey(
            12,
            (new EventRiskSourceRepository())->resolveCorporateActionContaminationForTickerIds(
                [12],
                ['2025-03-10', '2025-03-11', '2025-03-12']
            )
        );
    }

    /** A move inside the exchange band could be genuine, so it must not be derived. */
    public function test_derivation_refuses_moves_within_the_exchange_session_limit(): void
    {
        DB::table('tickers')->insert(['ticker_id' => 4, 'ticker_code' => 'MILD', 'company_name' => 'MILD Tbk', 'is_active' => 1]);

        DB::table('market_data_price_scale_breaks')->insert([
            'ticker_id' => 4,
            'ticker_code' => 'MILD',
            'trade_date' => '2025-05-05',
            'previous_close' => 1000,
            'open_price' => 800,
            'implied_ratio' => 1.25,
            'ratio_direction' => 'PRICE_DECREASED',
            'break_type' => 'SCALE_SHIFT',
            'match_status' => 'UNEXPLAINED',
            'review_status' => 'DETECTED',
            'detection_contract_version' => 'price_scale_break_v1',
            'detected_at' => '2026-01-01 00:00:00',
        ]);

        $result = (new CorporateActionDerivationService())->derive(true);

        $this->assertSame([], $result['derived']);
        $this->assertSame(0, DB::table('market_data_corporate_actions')->where('ticker_id', 4)->count());
    }
}
