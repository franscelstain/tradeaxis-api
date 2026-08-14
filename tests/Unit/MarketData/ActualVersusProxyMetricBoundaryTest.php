<?php

use App\Application\MarketData\Services\IndicatorVectorService;

/**
 * W13 — actual and proxy daily market metrics, stage 14.
 *
 * Exit gate: "actual dan proxy tidak dapat berbagi misleading name/meaning; adjusted price × raw
 * volume tidak dipakai sebagai proxy."
 *
 * Owner contracts:
 *   docs/market_data/book/Market_Daily_Metrics_Contract.md
 *   docs/market_data/registry/Volume_and_Turnover_Normalization_LOCKED.md
 *
 * The proxy is `RAW close × RAW volume` (registry `:27`). Computing it from adjusted bars is safe
 * only while price and volume factors are reciprocal, which is true for a split and false for a
 * rights issue — there price is `SCALED` and volume is `NONE`, so the product moves by the price
 * factor alone and the turnover figure silently misstates liquidity.
 */
class ActualVersusProxyMetricBoundaryTest extends TestCase
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

    /**
     * 60 flat bars at close 100, volume 1000, so the expected proxy is exactly 100,000.
     */
    private function bars(): array
    {
        $bars = [];
        for ($i = 1; $i <= 60; $i++) {
            $bars[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.$i.' days')),
                'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100,
                'adj_close' => 100, 'volume' => 1000,
            ];
        }

        return $bars;
    }

    private function buildRow(array $factors = []): array
    {
        $bars = $this->bars();
        $requestedDate = $bars[count($bars) - 1]['trade_date'];

        return (new IndicatorVectorService())
            ->buildRow(1, $bars, $requestedDate, 55, 9001, '2026-05-25 18:00:00', $this->config($factors));
    }

    /**
     * A rights issue scales price but not volume. Computing the proxy on adjusted bars would
     * multiply the raw volume by an adjusted price and understate turnover by the price factor —
     * here by 20%, across the whole window, on exactly the liquidity number a screen filters on.
     */
    public function test_a_price_only_adjustment_does_not_distort_the_turnover_proxy(): void
    {
        $rightsIssue = [[
            'ex_date' => date('Y-m-d', strtotime('2026-01-01 +75 days')),
            'price_factor' => 0.8,
            'volume_factor' => 1.0,
        ]];

        $row = $this->buildRow($rightsIssue);

        $this->assertEqualsWithDelta(100000.0, $row['adv20_close_volume_proxy_idr'], 0.01);
        $this->assertEqualsWithDelta(100000.0, $row['dv20_idr'], 0.01, 'the legacy alias carries the same proxy');
    }

    /**
     * A split scales price and volume reciprocally, so the proxy is unchanged. Asserting this
     * alongside the rights-issue case shows the fix is about using the as-traded series, not about
     * one event type happening to cancel out.
     */
    public function test_a_reciprocal_split_leaves_the_turnover_proxy_unchanged(): void
    {
        $split = [[
            'ex_date' => date('Y-m-d', strtotime('2026-01-01 +75 days')),
            'price_factor' => 0.5,
            'volume_factor' => 2.0,
        ]];

        $row = $this->buildRow($split);

        $this->assertEqualsWithDelta(100000.0, $row['adv20_close_volume_proxy_idr'], 0.01);
    }

    /**
     * With no corporate action at all the proxy is simply the raw product.
     */
    public function test_the_proxy_is_raw_close_times_raw_volume(): void
    {
        $row = $this->buildRow();

        $this->assertEqualsWithDelta(100000.0, $row['adv20_close_volume_proxy_idr'], 0.01);
    }

    /**
     * The actual stays NULL because the provider supplies no traded value at all. Writing the
     * proxy into the actual field would be a misstatement rather than an approximation, and the
     * contract requires NULL when the actual is unavailable.
     */
    public function test_the_actual_traded_value_is_null_rather_than_filled_with_the_proxy(): void
    {
        $row = $this->buildRow();

        $this->assertArrayHasKey('adv20_traded_value_idr_actual', $row);
        $this->assertNull($row['adv20_traded_value_idr_actual']);
        $this->assertNotNull($row['adv20_close_volume_proxy_idr'], 'the proxy is available even though the actual is not');
    }

    /**
     * Actual and proxy occupy separate fields, so a consumer never has to infer which meaning a
     * number carries. Before this the only populated field was the ambiguously named alias.
     */
    public function test_actual_and_proxy_are_separate_fields_with_distinct_names(): void
    {
        $row = $this->buildRow();

        $this->assertArrayHasKey('adv20_traded_value_idr_actual', $row);
        $this->assertArrayHasKey('adv20_close_volume_proxy_idr', $row);
        $this->assertNotSame(
            $row['adv20_traded_value_idr_actual'],
            $row['adv20_close_volume_proxy_idr'],
            'an unavailable actual must not be indistinguishable from an available proxy'
        );
    }
}
