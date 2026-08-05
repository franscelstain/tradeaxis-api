<?php

use App\Application\MarketData\Services\PriceScaleBreakDetectionService;
use App\Application\MarketData\Services\PriceScaleStretchRepairService;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class PriceScaleStretchRepairTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        DB::table('tickers')->insert(['ticker_id' => 1, 'ticker_code' => 'RMKE', 'company_name' => 'RMKE', 'is_active' => 1]);
        foreach ([
            ['2025-10-03', 1810, 1855, 9189600],
            ['2025-10-06', 377, 462, 140453500],
            ['2025-10-08', 2450, 2510, 8000000],
        ] as $bar) {
            $row = [
                'trade_date' => $bar[0], 'ticker_id' => 1, 'open' => $bar[1],
                'high' => max($bar[1], $bar[2]), 'low' => min($bar[1], $bar[2]),
                'close' => $bar[2], 'volume' => $bar[3], 'adj_close' => $bar[2],
                'source' => 'YAHOO_FINANCE', 'run_id' => 1, 'publication_id' => 1,
                'created_at' => '2026-01-01 00:00:00',
            ];
            DB::table('eod_bars')->insert($row);
            DB::table('eod_bars_history')->insert($row);
        }
        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_dry_run_reports_capability_boundary_without_repair_proposal(): void
    {
        $result = (new PriceScaleStretchRepairService())->repair(null, false);

        $this->assertSame([], $result['stretches']);
        $this->assertSame('DETECTION_ONLY', $result['capability_state']);
        $this->assertNotEmpty($result['skipped']);
        $this->assertSame('IMMUTABLE_HISTORY_CORRECTION_REQUIRED', $result['skipped'][0]['reason_code']);
    }

    public function test_apply_flag_never_mutates_current_or_history_bars(): void
    {
        $beforeCurrent = DB::table('eod_bars')->orderBy('trade_date')->get()->map(function ($row) { return (array) $row; })->all();
        $beforeHistory = DB::table('eod_bars_history')->orderBy('trade_date')->get()->map(function ($row) { return (array) $row; })->all();

        $result = (new PriceScaleStretchRepairService())->repair(null, true);

        $this->assertFalse($result['mutation_performed']);
        $this->assertSame($beforeCurrent, DB::table('eod_bars')->orderBy('trade_date')->get()->map(function ($row) { return (array) $row; })->all());
        $this->assertSame($beforeHistory, DB::table('eod_bars_history')->orderBy('trade_date')->get()->map(function ($row) { return (array) $row; })->all());
    }

    public function test_anomaly_remains_quarantined_until_authoritative_correction_publication(): void
    {
        $repository = new PriceScaleBreakRepository();
        $before = $repository->resolveContaminationForTickerIds([1], ['2025-10-06', '2025-10-08']);
        (new PriceScaleStretchRepairService())->repair(null, true);
        $after = $repository->resolveContaminationForTickerIds([1], ['2025-10-06', '2025-10-08']);

        $this->assertArrayHasKey(1, $before);
        $this->assertSame($before, $after);
        $this->assertSame('DETECTED', DB::table('market_data_price_scale_breaks')->orderBy('trade_date')->value('review_status'));
    }
}
