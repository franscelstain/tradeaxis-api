<?php

use App\Application\MarketData\Services\BenchmarkIndicatorVectorService;
use PHPUnit\Framework\TestCase;

class BenchmarkIndicatorVectorServiceTest extends TestCase
{
    private function bars($days = 50)
    {
        $rows = [];
        for ($i = 1; $i <= $days; $i++) {
            $close = 7000 + $i;
            $rows[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.($i - 1).' days')),
                'close_price' => $close,
                'adjusted_close' => $close,
            ];
        }

        return $rows;
    }

    public function test_benchmark_indicator_formulas_use_trading_day_rows()
    {
        $service = new BenchmarkIndicatorVectorService();
        $row = $service->buildRow('IHSG', $this->bars(50), '2026-02-19', '2026-02-19 18:00:00', ['set_version' => 'v1']);

        $this->assertSame('IHSG', $row['benchmark_code']);
        $this->assertSame(1, $row['is_valid']);
        $this->assertNull($row['invalid_reason_code']);
        $this->assertEqualsWithDelta(0.2844950213, $row['roc_20'], 0.000000001);
        $this->assertSame(7040.5, $row['ma20']);
        $this->assertSame(7025.5, $row['ma50']);
        $this->assertEqualsWithDelta(0.0710681544, $row['ma20_slope_pct'], 0.000000001);
        $this->assertEqualsWithDelta(0.1349335985, $row['close_to_ma20_pct'], 0.000000001);
        $this->assertEqualsWithDelta(0.3487296285, $row['close_to_ma50_pct'], 0.000000001);
    }

    public function test_benchmark_indicator_insufficient_lookback_keeps_values_null()
    {
        $service = new BenchmarkIndicatorVectorService();
        $row = $service->buildRow('IHSG', $this->bars(10), '2026-01-10', '2026-01-10 18:00:00', ['set_version' => 'v1']);

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_INSUFFICIENT_HISTORY', $row['invalid_reason_code']);
        $this->assertNull($row['roc_20']);
        $this->assertNull($row['ma20']);
        $this->assertNull($row['ma50']);
        $this->assertNull($row['ma20_slope_pct']);
        $this->assertNull($row['close_to_ma20_pct']);
        $this->assertNull($row['close_to_ma50_pct']);
    }

    public function test_benchmark_indicator_zero_denominator_returns_null_without_error()
    {
        $bars = $this->bars(50);
        $bars[29]['close_price'] = 0;
        $bars[29]['adjusted_close'] = 0;

        $service = new BenchmarkIndicatorVectorService();
        $row = $service->buildRow('IHSG', $bars, '2026-02-19', '2026-02-19 18:00:00', ['set_version' => 'v1']);

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_INVALID_BAR_INPUT', $row['invalid_reason_code']);
        $this->assertNull($row['roc_20']);
        $this->assertNull($row['close_to_ma50_pct']);
    }
}
