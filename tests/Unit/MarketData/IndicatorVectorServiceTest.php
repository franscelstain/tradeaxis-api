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
            'benchmark_roc20_pct' => 5.0,
            'sector_roc20_pct' => 2.5,
            'sector_code' => 'G',
        ];
    }

    private function bars($days = 21)
    {
        $rows = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = date('Y-m-d', strtotime('2026-04-01 +'.($i - 1).' days'));
            $close = 100 + $i;
            $rows[] = [
                'trade_date' => $date,
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
        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertSame(101, $row['ticker_id']);
        $this->assertSame(1, $row['is_valid']);
        $this->assertNull($row['invalid_reason_code']);
        $this->assertSame('G', $row['sector_code']);
        $this->assertIsFloat($row['dv20_idr']);
        $this->assertIsFloat($row['atr14_pct']);
        $this->assertIsFloat($row['vol_ratio']);
        $this->assertIsFloat($row['roc5']);
        $this->assertIsFloat($row['roc10']);
        $this->assertIsFloat($row['roc20']);
        $this->assertIsFloat($row['hh20']);
        $this->assertIsFloat($row['ll20']);
        $this->assertIsFloat($row['ma20']);
        $this->assertIsFloat($row['ma50']);
        $this->assertIsFloat($row['close_to_hh20_pct']);
        $this->assertIsFloat($row['close_to_ll20_pct']);
        $this->assertIsFloat($row['range_20_pct']);
        $this->assertIsFloat($row['range_position_20_pct']);
        $this->assertIsFloat($row['close_vs_ma20_pct']);
        $this->assertIsFloat($row['close_vs_ma50_pct']);
        $this->assertIsFloat($row['ma20_slope_pct']);
        $this->assertIsFloat($row['rs_20_vs_ihsg']);
        $this->assertIsFloat($row['sector_roc20']);
        $this->assertIsFloat($row['rs_20_vs_sector']);
        $this->assertIsFloat($row['sector_rs_20_vs_ihsg']);
        $this->assertNull($row['event_risk_flag']);
    }

    public function test_equity_indicator_extension_formulas_are_deterministic()
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertEqualsWithDelta(0.0333333333, $row['roc5'], 0.000000001);
        $this->assertEqualsWithDelta(0.0689655172, $row['roc10'], 0.000000001);
        $this->assertSame(145.5, $row['ma20']);
        $this->assertSame(130.5, $row['ma50']);
        $this->assertSame(134.0, $row['ll20']);
        $this->assertEqualsWithDelta(-0.641025641, $row['close_to_hh20_pct'], 0.000000001);
        $this->assertEqualsWithDelta(15.671641791, $row['close_to_ll20_pct'], 0.000000001);
        $this->assertEqualsWithDelta(16.4179104478, $row['range_20_pct'], 0.000000001);
        $this->assertEqualsWithDelta(95.4545454545, $row['range_position_20_pct'], 0.000000001);
        $this->assertEqualsWithDelta(6.529209622, $row['close_vs_ma20_pct'], 0.000000001);
        $this->assertEqualsWithDelta(18.77394636, $row['close_vs_ma50_pct'], 0.000000001);
        $this->assertEqualsWithDelta(3.5587188612, $row['ma20_slope_pct'], 0.000000001);
        $this->assertEqualsWithDelta(9.81481481, $row['rs_20_vs_ihsg'], 0.000000001);
        $this->assertEqualsWithDelta(2.5, $row['sector_roc20'], 0.000000001);
        $this->assertEqualsWithDelta(12.31481481, $row['rs_20_vs_sector'], 0.000000001);
        $this->assertEqualsWithDelta(-2.5, $row['sector_rs_20_vs_ihsg'], 0.000000001);
    }

    public function test_extension_indicators_remain_null_when_optional_lookback_or_benchmark_dependency_is_missing()
    {
        $service = new IndicatorVectorService();
        $config = $this->config();
        $config['benchmark_roc20_pct'] = null;
        $config['sector_roc20_pct'] = null;

        $row = $service->buildRow(101, $this->bars(), '2026-04-21', 55, 9001, '2026-04-21 18:00:00', $config);

        $this->assertSame(1, $row['is_valid']);
        $this->assertSame(111.5, $row['ma20']);
        $this->assertNull($row['ma50']);
        $this->assertNull($row['close_vs_ma50_pct']);
        $this->assertNull($row['ma20_slope_pct']);
        $this->assertNull($row['rs_20_vs_ihsg']);
        $this->assertNull($row['sector_roc20']);
        $this->assertNull($row['rs_20_vs_sector']);
        $this->assertNull($row['sector_rs_20_vs_ihsg']);
    }

    public function test_zero_denominator_extension_calculations_return_null_without_error()
    {
        $service = new IndicatorVectorService();
        $bars = $this->bars(55);
        for ($i = 35; $i < 55; $i++) {
            $bars[$i]['close'] = 0;
            $bars[$i]['adj_close'] = 0;
        }

        $row = $service->buildRow(101, $bars, '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertNull($row['ma20']);
        $this->assertNull($row['close_vs_ma20_pct']);
        $this->assertNull($row['ma20_slope_pct']);
    }

    public function test_build_row_marks_insufficient_history_when_requested_date_has_short_window()
    {
        $service = new IndicatorVectorService();
        $row = $service->buildRow(101, array_slice($this->bars(), 0, 10), '2026-04-10', 55, 9001, '2026-04-10 18:00:00', $this->config());

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_INSUFFICIENT_HISTORY', $row['invalid_reason_code']);
        $this->assertNull($row['dv20_idr']);
        $this->assertNull($row['roc5']);
        $this->assertNull($row['roc10']);
        $this->assertNull($row['ll20']);
        $this->assertNull($row['range_position_20_pct']);
    }

    public function test_event_risk_context_is_source_backed_and_stamped_into_valid_rows()
    {
        $service = new IndicatorVectorService();
        $config = $this->config();
        $config['event_risk_context'] = [
            'corporate_action_flag' => 1,
            'corporate_action_types' => 'DIVIDEND,STOCK_SPLIT',
            'trading_status_code' => 'UMA',
            'is_suspended' => 0,
            'is_uma' => 1,
            'event_risk_flag' => 1,
            'event_risk_reasons' => 'CORPORATE_ACTION:DIVIDEND,UMA',
        ];

        $row = $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $config);

        $this->assertSame(1, $row['corporate_action_flag']);
        $this->assertSame('DIVIDEND,STOCK_SPLIT', $row['corporate_action_types']);
        $this->assertSame('UMA', $row['trading_status_code']);
        $this->assertSame(0, $row['is_suspended']);
        $this->assertSame(1, $row['is_uma']);
        $this->assertSame(1, $row['event_risk_flag']);
        $this->assertSame('CORPORATE_ACTION:DIVIDEND,UMA', $row['event_risk_reasons']);
    }

    public function test_event_risk_context_survives_invalid_indicator_rows_without_faking_absence()
    {
        $service = new IndicatorVectorService();
        $config = $this->config();
        $config['event_risk_context'] = [
            'trading_status_code' => 'ACTIVE',
            'is_suspended' => 0,
            'is_uma' => 0,
            'event_risk_flag' => 0,
        ];

        $row = $service->buildRow(101, array_slice($this->bars(), 0, 10), '2026-04-10', 55, 9001, '2026-04-10 18:00:00', $config);

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_INSUFFICIENT_HISTORY', $row['invalid_reason_code']);
        $this->assertSame('ACTIVE', $row['trading_status_code']);
        $this->assertSame(0, $row['is_suspended']);
        $this->assertSame(0, $row['is_uma']);
        $this->assertSame(0, $row['event_risk_flag']);
        $this->assertNull($row['corporate_action_flag']);
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

    public function test_flat_twenty_day_range_keeps_range_position_null_without_error()
    {
        $service = new IndicatorVectorService();
        $bars = $this->bars(55);

        for ($i = 35; $i < 55; $i++) {
            $bars[$i]['open'] = 100;
            $bars[$i]['high'] = 100;
            $bars[$i]['low'] = 100;
            $bars[$i]['close'] = 100;
            $bars[$i]['adj_close'] = 100;
        }

        $row = $service->buildRow(101, $bars, '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertSame(100.0, $row['ll20']);
        $this->assertSame(0.0, $row['range_20_pct']);
        $this->assertNull($row['range_position_20_pct']);
    }
}
