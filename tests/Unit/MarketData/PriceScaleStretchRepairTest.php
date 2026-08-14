<?php

use App\Application\MarketData\Services\PriceScaleBreakDetectionService;
use App\Application\MarketData\Services\PriceScaleStretchRepairService;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 */
class PriceScaleStretchRepairTest extends TestCase
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

    private function seedBars($tickerId, array $bars): void
    {
        foreach ($bars as $bar) {
            $row = [
                'trade_date' => $bar[0],
                'ticker_id' => $tickerId,
                'open' => $bar[1],
                'high' => max($bar[1], $bar[2]),
                'low' => min($bar[1], $bar[2]),
                'close' => $bar[2],
                'volume' => $bar[3],
                'adj_close' => $bar[2],
                'source' => 'YAHOO_FINANCE',
                'run_id' => 1,
                'created_at' => '2026-01-01 00:00:00',
            ];

            DB::table('eod_bars')->insert($row + ['publication_id' => 1]);
            DB::table('eod_bars_history')->insert($row + ['publication_id' => 1]);
        }
    }

    /**
     * One bar sits on the divided scale between a decrease and a matching increase.
     * Restoring it means multiplying price by the split factor and dividing volume.
     */
    private function seedDefectiveStretch(): void
    {
        $this->seedTicker(1, 'RMKE');
        $this->seedBars(1, [
            ['2025-10-03', 1810, 1855, 9189600],
            ['2025-10-06', 377, 462, 140453500],
            ['2025-10-08', 2450, 2510, 8000000],
        ]);
    }

    public function test_dry_run_previews_without_touching_bars(): void
    {
        $this->seedDefectiveStretch();
        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);

        $result = (new PriceScaleStretchRepairService())->repair(null, false);

        $this->assertCount(1, $result['stretches']);
        $stretch = $result['stretches'][0];

        $this->assertSame(5.0, $stretch['factor']);
        $this->assertSame(1, $stretch['bar_count']);
        $this->assertSame('2025-10-06', $stretch['sample'][0]['trade_date']);
        $this->assertSame(462.0, $stretch['sample'][0]['close_before']);
        $this->assertSame(2310.0, $stretch['sample'][0]['close_after']);

        $this->assertEqualsWithDelta(
            462.0,
            (float) DB::table('eod_bars')->where('trade_date', '2025-10-06')->value('close'),
            0.01
        );
    }

    public function test_apply_restores_price_and_volume_scale(): void
    {
        $this->seedDefectiveStretch();
        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);

        (new PriceScaleStretchRepairService())->repair(null, true);

        $bar = DB::table('eod_bars')->where('trade_date', '2025-10-06')->first();

        $this->assertEqualsWithDelta(1885.0, (float) $bar->open, 0.01);
        $this->assertEqualsWithDelta(2310.0, (float) $bar->close, 0.01);
        // Share count moves inversely to price under a split.
        $this->assertSame(28090700, (int) $bar->volume);

        $history = DB::table('eod_bars_history')->where('trade_date', '2025-10-06')->first();
        $this->assertEqualsWithDelta(2310.0, (float) $history->close, 0.01);
    }

    public function test_repaired_break_stops_quarantining_and_records_its_trail(): void
    {
        $this->seedDefectiveStretch();
        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);

        $repository = new PriceScaleBreakRepository();
        $tradingDates = ['2025-10-06', '2025-10-08', '2025-10-09'];

        $this->assertArrayHasKey(1, $repository->resolveContaminationForTickerIds([1], $tradingDates));

        (new PriceScaleStretchRepairService())->repair(null, true);

        $this->assertSame([], $repository->resolveContaminationForTickerIds([1], $tradingDates));

        $break = DB::table('market_data_price_scale_breaks')->where('trade_date', '2025-10-06')->first();
        $this->assertSame('REPAIRED', $break->review_status);
        $this->assertSame(5.0, (float) $break->repair_factor);
        $this->assertSame(1, (int) $break->repaired_bar_count);
        $this->assertNotNull($break->repaired_at);
    }

    /**
     * A single unmatched break is a genuine split. The series either side of it is already
     * the true as-traded history, so rewriting it would destroy correct data.
     */
    public function test_genuine_split_without_a_matching_reversal_is_never_repaired(): void
    {
        $this->seedTicker(2, 'SCCO');
        $this->seedBars(2, [
            ['2024-01-31', 9900, 9975, 1000000],
            ['2024-02-01', 2506, 2500, 4000000],
            ['2024-02-02', 2510, 2520, 3500000],
        ]);

        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);

        $result = (new PriceScaleStretchRepairService())->repair(null, true);

        $this->assertSame([], $result['stretches']);
        $this->assertEqualsWithDelta(
            2500.0,
            (float) DB::table('eod_bars')->where('trade_date', '2024-02-01')->value('close'),
            0.01
        );
    }

    public function test_repair_is_not_repeated_once_applied(): void
    {
        $this->seedDefectiveStretch();
        (new PriceScaleBreakDetectionService())->detect(null, null, null, true);

        (new PriceScaleStretchRepairService())->repair(null, true);
        $closeAfterFirst = (float) DB::table('eod_bars')->where('trade_date', '2025-10-06')->value('close');

        $second = (new PriceScaleStretchRepairService())->repair(null, true);

        $this->assertSame([], $second['stretches'], 'an already repaired stretch must not be multiplied again');
        $this->assertEqualsWithDelta(
            $closeAfterFirst,
            (float) DB::table('eod_bars')->where('trade_date', '2025-10-06')->value('close'),
            0.01
        );
    }

    public function test_repaired_series_no_longer_reports_a_break(): void
    {
        $this->seedDefectiveStretch();
        $detector = new PriceScaleBreakDetectionService();
        $detector->detect(null, null, null, true);

        (new PriceScaleStretchRepairService())->repair(null, true);

        DB::table('market_data_price_scale_breaks')->delete();
        $rescan = $detector->detect(null, null, null, false);

        $this->assertSame([], $rescan['detected'], 'the repaired series must be continuous');
    }
}
