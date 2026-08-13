<?php

use App\Application\MarketData\Services\IndicatorVectorService;

/**
 * W14 — deterministic indicators and dependency graph, stage 15.
 *
 * Exit gate: "independent short/long/gap/action/correction oracles lulus; ATR state stabil sejak
 * dataset/listing boundary dan correction impact tidak dipotong secara salah."
 *
 * Owner contracts:
 *   docs/market_data/book/EOD_Indicators_Contract.md
 *   docs/market_data/book/Indicator_Nullability_And_OHLCV_Gap_Contract.md
 *   docs/market_data/indicators/EOD_Indicators_Formula_Spec.md
 *   docs/market_data/registry/Indicator_Registry_Baseline_LOCKED.md
 *
 * The expected values here are computed from the formula definitions, not read back from the
 * implementation. An oracle that takes its answer from the code under test proves only that the
 * code is self-consistent, which is precisely the failure mode the audit spine warns about:
 * a bounded mechanism agreeing with itself is not evidence about the world.
 */
class IndicatorIndependentOracleTest extends TestCase
{
    private function config(array $override = []): array
    {
        return array_merge([
            'set_version' => 'ind_v1',
            'price_adjustment_factors' => [],
            'price_basis_default' => 'close',
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
            'sector_code' => null,
            'sector_index_code' => null,
            'event_risk_context' => [],
            'corporate_action_contamination' => [],
            'price_scale_break_contamination' => [],
            'atr_contamination_horizon_days' => 0,
            'benchmark_roc20_pct' => null,
            'sector_roc20_pct' => null,
        ], $override);
    }

    /**
     * A deterministic ramp: close rises by 1 each session from 100, range is always 2 wide.
     */
    private function rampBars(int $count, float $startClose = 100.0): array
    {
        $bars = [];
        for ($i = 0; $i < $count; $i++) {
            $close = $startClose + $i;
            $bars[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.($i + 1).' days')),
                'open' => $close,
                'high' => $close + 1,
                'low' => $close - 1,
                'close' => $close,
                'adj_close' => $close,
                'volume' => 1000,
            ];
        }

        return $bars;
    }

    private function values(array $bars, array $config = null, array $atrSeries = null): array
    {
        return (new IndicatorVectorService())
            ->calculateIndicators($bars, count($bars) - 1, $config ?: $this->config(), null, $atrSeries);
    }

    /**
     * Short-window oracle. On the ramp, close(D) = 159 and close(D-20) = 139, so
     * roc20 = 159/139 - 1 = 0.143884892086..., and ma20 is the mean of 140..159 = 149.5.
     */
    public function test_short_window_oracle_matches_hand_computed_roc_and_moving_average(): void
    {
        $values = $this->values($this->rampBars(60));

        $this->assertEqualsWithDelta(159 / 139 - 1, $values['roc20'], 1e-9);
        $this->assertEqualsWithDelta(149.5, $values['ma20'], 1e-9);
        $this->assertEqualsWithDelta(134.5, $values['ma50'], 1e-9, 'mean of 110..159');
    }

    /**
     * Long-window oracle. hh20 is the highest high across the last 20 sessions, which on the ramp
     * is close(D)+1 = 160; ll20 is the lowest low, close(D-19)-1 = 139.
     */
    public function test_long_window_oracle_matches_hand_computed_extremes(): void
    {
        $values = $this->values($this->rampBars(60));

        $this->assertEqualsWithDelta(160.0, $values['hh20'], 1e-9);
        $this->assertEqualsWithDelta(139.0, $values['ll20'], 1e-9);
        $this->assertEqualsWithDelta(149.5 * 1000, $values['dv20_idr'], 0.01, 'mean close 149.5 x volume 1000');
    }

    /**
     * ATR oracle. Every true range on the ramp is exactly 2: high-low = 2, |high - prevClose| = 2,
     * |low - prevClose| = 0. A Wilder average of a constant series is that constant, so ATR = 2
     * regardless of where the recursion is seeded — which makes this the one case where seed
     * placement cannot hide an error in the recursion itself.
     */
    public function test_atr_oracle_on_a_constant_true_range_series(): void
    {
        $values = $this->values($this->rampBars(60));

        $this->assertEqualsWithDelta(2.0 / 159.0, $values['atr14_pct'], 1e-9, 'ATR 2 over close 159');
    }

    /**
     * ATR seed stability. The same requested date computed from a short load window and from a
     * boundary-anchored series must agree, because Wilder ATR is defined by its recursion from the
     * boundary and not by how much history a run happened to load.
     */
    public function test_atr_is_seeded_from_the_boundary_not_the_load_window(): void
    {
        $full = $this->rampBars(200);
        $window = array_slice($full, -60);

        $atrSeries = array_map(function ($bar) {
            return [
                'trade_date' => $bar['trade_date'],
                'high' => $bar['high'],
                'low' => $bar['low'],
                'close' => $bar['close'],
            ];
        }, $full);

        $withBoundary = $this->values($window, null, $atrSeries);
        $fromFullWindow = $this->values($full);

        $this->assertEqualsWithDelta(
            $fromFullWindow['atr14_pct'],
            $withBoundary['atr14_pct'],
            1e-9,
            'a 60-bar load seeded at the boundary must equal the full-history computation'
        );
    }

    public function test_boundary_atr_uses_the_same_structural_adjustment_product_as_other_prices(): void
    {
        $adjusted = $this->rampBars(200);
        $raw = $adjusted;
        $splitIndex = 100;

        // Before a 2-for-1 split, the as-traded price scale is twice the current scale. A 0.5
        // structural factor must therefore be applied to the complete recursive ATR history.
        for ($i = 0; $i < $splitIndex; $i++) {
            foreach (['open', 'high', 'low', 'close', 'adj_close'] as $field) {
                $raw[$i][$field] *= 2;
            }
        }

        $atrSeries = array_map(function ($bar) {
            return [
                'trade_date' => $bar['trade_date'],
                'high' => $bar['high'],
                'low' => $bar['low'],
                'close' => $bar['close'],
            ];
        }, $raw);

        $window = array_slice($raw, -60);
        $withBoundary = $this->values($window, $this->config([
            'selected_price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_adjustment_factors' => [[
                'ex_date' => $raw[$splitIndex]['trade_date'],
                'price_factor' => 0.5,
                'volume_factor' => 2.0,
            ]],
        ]), $atrSeries);
        $expected = $this->values($adjusted);

        $this->assertEqualsWithDelta(
            $expected['atr14_pct'],
            $withBoundary['atr14_pct'],
            1e-9,
            'ATR boundary state may not mix the RAW and STRUCTURAL_ADJUSTED products'
        );
    }

    /**
     * Gap oracle. A NULL inside the required window is a genuine hole, so the dependent indicator
     * is NULL rather than computed over a shortened series. Skipping the gap would silently change
     * the window length and report a number for a period the platform cannot see.
     */
    public function test_gap_oracle_nulls_the_dependent_indicator_rather_than_shortening_the_window(): void
    {
        $bars = $this->rampBars(60);
        $bars[55]['close'] = null;

        $values = $this->values($bars);

        $this->assertNull($values['ma20'], 'a hole inside the 20-day window cannot be averaged around');
    }

    /**
     * Action oracle. A window spanning a contaminating corporate action is quarantined, and the
     * quarantine covers the explicitly named turnover proxy as well as its legacy alias.
     */
    public function test_action_oracle_quarantines_the_window_spanning_a_contaminating_event(): void
    {
        $bars = $this->rampBars(60);
        $actionDate = $bars[55]['trade_date'];

        // Quarantine is applied by buildRow, not by calculateIndicators, so the oracle has to go
        // through the path a real run takes.
        $values = (new IndicatorVectorService())->buildRow(
            1,
            $bars,
            $bars[count($bars) - 1]['trade_date'],
            55,
            9001,
            '2026-05-25 18:00:00',
            $this->config([
            'corporate_action_contamination' => [
                [
                    'action_type_code' => 'STOCK_SPLIT',
                    'action_date' => $actionDate,
                    // Four sessions back from the requested date, well inside the 20-day window.
                    'depth' => 4,
                    'breaks_price_continuity' => true,
                    'breaks_volume_continuity' => true,
                ],
            ],
            ])
        );

        $this->assertNull($values['dv20_idr'], 'turnover over a window containing a scale break is not reportable');
        $this->assertNull($values['adv20_close_volume_proxy_idr'], 'the clearer name must not expose a quarantined window');
    }

    /**
     * Correction oracle. A corrected bar changes every indicator whose window contains it, and the
     * change must be visible rather than absorbed. Here the corrected close moves ma20 by exactly
     * the correction divided by the window length.
     */
    public function test_correction_oracle_propagates_by_exactly_the_expected_amount(): void
    {
        $before = $this->values($this->rampBars(60));

        $corrected = $this->rampBars(60);
        $corrected[50]['close'] = $corrected[50]['close'] + 20;

        $after = $this->values($corrected);

        $this->assertEqualsWithDelta(
            $before['ma20'] + 20 / 20,
            $after['ma20'],
            1e-9,
            'a 20 correction inside a 20-day mean moves it by exactly 1'
        );
    }
}
