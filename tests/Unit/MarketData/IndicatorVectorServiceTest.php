<?php

use App\Application\MarketData\Services\IndicatorVectorService;
use PHPUnit\Framework\TestCase;

class IndicatorVectorServiceTest extends TestCase
{
    private function config()
    {
        return [
            'set_version' => 'ind_v1',
            'lot_size' => 100,
            'price_basis_default' => 'close',
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
        ];
    }

    private function bars($days = 21)
    {
        $rows = [];
        for ($i = 1; $i <= $days; $i++) {
            $day = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $close = 100 + $i;
            $rows[] = [
                'trade_date' => '2026-04-'.$day,
                'open' => $close - 1,
                'high' => $close + 1,
                'low' => $close - 2,
                'close' => $close,
                'adj_close' => $close,
                'volume' => 1000 + ($i * 10),
            ];
        }
        return $rows;
    }

    public function test_build_row_returns_valid_indicator_vector_for_sufficient_history()
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(), '2026-04-21', 55, 9001, '2026-04-21 18:00:00', $this->config());

        $this->assertSame(101, $row['ticker_id']);
        $this->assertSame(1, $row['is_valid']);
        $this->assertNull($row['invalid_reason_code']);
        $this->assertIsFloat($row['dv20_idr']);
        $this->assertIsFloat($row['atr14_pct']);
        $this->assertIsFloat($row['vol_ratio']);
        $this->assertIsFloat($row['roc20']);
        $this->assertIsFloat($row['hh20']);
    }

    public function test_build_row_marks_insufficient_history_when_requested_date_has_short_window()
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, array_slice($this->bars(), 0, 10), '2026-04-10', 55, 9001, '2026-04-10 18:00:00', $this->config());

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_INSUFFICIENT_HISTORY', $row['invalid_reason_code']);
        $this->assertNull($row['dv20_idr']);
    }

    public function test_build_row_marks_missing_dependency_when_bar_field_is_null_inside_required_window()
    {
        $service = new IndicatorVectorService();
        $bars = $this->bars();
        $bars[5]['close'] = null;
        $row = $service->buildRow(101, $bars, '2026-04-21', 55, 9001, '2026-04-21 18:00:00', $this->config());

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_MISSING_DEPENDENCY_BAR', $row['invalid_reason_code']);
    }
}
