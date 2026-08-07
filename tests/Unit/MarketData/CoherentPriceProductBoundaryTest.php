<?php

use App\Application\MarketData\Services\IndicatorVectorService;

/**
 * W12 — coherent analytical price products, stage 11.
 *
 * Exit gate: "one run/field vector cannot mix `close`, provider `adj_close`, factor sets, or
 * RAW/adjusted scales; unresolved factor contaminates/nulls rather than falls back."
 *
 * Owner contracts:
 *   docs/market_data/book/Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md
 *   docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
 *
 * Two series can look alike and mean different things. A window spanning a 1:5 split differs from
 * its unadjusted twin by a factor of five, not by a few percent, so a vector that cannot state
 * which product it was computed on is not merely undocumented — it is unusable for comparison.
 */
class CoherentPriceProductBoundaryTest extends TestCase
{
    private function config(array $factors = []): array
    {
        return [
            'set_version' => 'ind_v1',
            'price_adjustment_factors' => $factors,
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
        ];
    }

    private function adjust(array $bars, array $factors): array
    {
        $service = new IndicatorVectorService();
        $method = new ReflectionMethod($service, 'applyPriceAdjustment');
        $method->setAccessible(true);

        return $method->invoke($service, $bars, $this->config($factors));
    }

    private function bar(string $date, $close = 100, $adjClose = 77): array
    {
        return [
            'trade_date' => $date,
            'open' => $close, 'high' => $close, 'low' => $close, 'close' => $close,
            'adj_close' => $adjClose, 'volume' => 100,
        ];
    }

    /**
     * Provider adjusted close is an observation, not a platform product. Multiplying it by the
     * platform's structural factor produces a value that is neither the provider's figure nor a
     * product the platform defines, and it would sit in the same vector as `close`.
     */
    public function test_provider_adjusted_close_is_not_scaled_by_a_platform_factor(): void
    {
        $result = $this->adjust(
            [$this->bar('2026-04-05', 100, 77)],
            [['ex_date' => '2026-04-20', 'price_factor' => 0.5, 'volume_factor' => 2.0]]
        );

        $this->assertEqualsWithDelta(50.0, $result['bars'][0]['close'], 1e-9, 'the raw close is adjusted');
        $this->assertEqualsWithDelta(77.0, $result['bars'][0]['adj_close'], 1e-9, 'the provider observation is left as observed');
    }

    /**
     * An adjusted vector says so. Without the label a consumer cannot tell a split-adjusted window
     * from an unadjusted one, and the difference is the split ratio.
     */
    public function test_an_adjusted_vector_declares_the_structural_adjusted_product(): void
    {
        $result = $this->adjust(
            [$this->bar('2026-04-05')],
            [['ex_date' => '2026-04-20', 'price_factor' => 0.5, 'volume_factor' => 2.0]]
        );

        $this->assertSame('STRUCTURAL_ADJUSTED', $result['price_product_code']);
    }

    /**
     * An unadjusted vector says RAW rather than claiming an adjustment it never performed.
     */
    public function test_a_vector_with_no_factors_declares_raw(): void
    {
        $result = $this->adjust([$this->bar('2026-04-05')], []);

        $this->assertSame('RAW', $result['price_product_code']);
        $this->assertEqualsWithDelta(100.0, $result['bars'][0]['close'], 1e-9);
    }

    /**
     * A factor that lands outside the window adjusts nothing, so the vector stays RAW. Reporting
     * STRUCTURAL_ADJUSTED merely because a factor existed somewhere would make the label describe
     * the input rather than the output.
     */
    public function test_a_factor_that_changes_nothing_leaves_the_vector_raw(): void
    {
        $result = $this->adjust(
            [$this->bar('2026-05-10')],
            [['ex_date' => '2026-04-20', 'price_factor' => 0.5, 'volume_factor' => 2.0]]
        );

        $this->assertSame('RAW', $result['price_product_code'], 'a bar after the ex-date is already on the current scale');
        $this->assertEqualsWithDelta(100.0, $result['bars'][0]['close'], 1e-9);
    }

    /**
     * The product label reaches the persisted vector, not just the internal computation.
     */
    public function test_the_persisted_vector_carries_its_price_product_code(): void
    {
        $bars = [];
        for ($i = 1; $i <= 60; $i++) {
            $bars[] = $this->bar(date('Y-m-d', strtotime('2026-01-01 +'.$i.' days')));
        }
        $requestedDate = $bars[count($bars) - 1]['trade_date'];

        $row = (new IndicatorVectorService())
            ->buildRow(1, $bars, $requestedDate, 55, 9001, '2026-05-25 18:00:00', $this->config());

        $this->assertArrayHasKey('price_product_code', $row);
        $this->assertSame('RAW', $row['price_product_code']);
    }

    /**
     * Every price field moves on one scale. Adjusting close while leaving high and low behind
     * would corrupt true range, which mixes high, low, and the previous close in one arithmetic.
     */
    public function test_every_ohlc_field_moves_on_the_same_scale(): void
    {
        $bars = [[
            'trade_date' => '2026-04-05',
            'open' => 100, 'high' => 120, 'low' => 90, 'close' => 110,
            'adj_close' => 77, 'volume' => 100,
        ]];

        $result = $this->adjust($bars, [['ex_date' => '2026-04-20', 'price_factor' => 0.5, 'volume_factor' => 2.0]]);
        $adjusted = $result['bars'][0];

        $this->assertEqualsWithDelta(50.0, $adjusted['open'], 1e-9);
        $this->assertEqualsWithDelta(60.0, $adjusted['high'], 1e-9);
        $this->assertEqualsWithDelta(45.0, $adjusted['low'], 1e-9);
        $this->assertEqualsWithDelta(55.0, $adjusted['close'], 1e-9);
        $this->assertEqualsWithDelta(200.0, $adjusted['volume'], 1e-9, 'volume moves inversely');
    }
}
