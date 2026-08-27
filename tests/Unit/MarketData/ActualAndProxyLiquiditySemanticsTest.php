<?php

use App\Application\MarketData\Services\IndicatorVectorService;
use App\Domain\MarketData\LiquidityMetricLabelRegistry;
use App\Domain\MarketData\MarketDataSemanticBindings;

/**
 * B13: what the actual and proxy liquidity metrics mean, and what they must never become.
 */
class ActualAndProxyLiquiditySemanticsTest extends TestCase
{
    private function bars(int $count, float $close = 100.0, int $volume = 1000): array
    {
        $bars = [];
        $day = new DateTimeImmutable('2026-01-05');
        for ($i = 0; $i < $count; $i++) {
            $bars[] = [
                'trade_date' => $day->format('Y-m-d'),
                'open' => $close,
                'high' => $close + 1,
                'low' => $close - 1,
                'close' => $close,
                'adj_close' => $close,
                'volume' => $volume,
            ];
            $day = $day->modify('+1 day');
        }

        return $bars;
    }

    private function config(array $overrides = []): array
    {
        return $overrides + [
            'set_version' => 'v1',
            'formula_version' => 'v1',
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
            'sector_code' => null,
            'selected_price_product_code' => 'RAW',
            'price_adjustment_factors' => [],
        ];
    }

    private function vectorFor(array $bars, array $config = null): array
    {
        $config = $config ?: $this->config();

        return (new IndicatorVectorService())->calculateIndicators($bars, count($bars) - 1, $config, $bars);
    }

    public function test_the_proxy_is_raw_close_times_raw_volume_averaged_over_the_declared_window(): void
    {
        $values = $this->vectorFor($this->bars(25, 100.0, 1000));

        $this->assertSame(100000.0, (float) $values['adv20_close_volume_proxy_idr']);
    }

    public function test_the_actual_traded_value_stays_null_when_no_source_reports_it(): void
    {
        $values = $this->vectorFor($this->bars(25));

        $this->assertNull(
            $values['adv20_traded_value_idr_actual'],
            'An unavailable actual traded value is NULL and is never derived from the proxy.'
        );
    }

    public function test_the_actual_field_never_receives_the_proxy_value(): void
    {
        $values = $this->vectorFor($this->bars(25, 100.0, 1000));

        $this->assertNotNull($values['adv20_close_volume_proxy_idr']);
        $this->assertNotSame(
            $values['adv20_close_volume_proxy_idr'],
            $values['adv20_traded_value_idr_actual'],
            'Actual and proxy are never coalesced into one output.'
        );
    }

    public function test_the_legacy_alias_carries_the_proxy_value_and_never_the_actual(): void
    {
        $values = $this->vectorFor($this->bars(25, 100.0, 1000));

        $this->assertSame($values['adv20_close_volume_proxy_idr'], $values['dv20_idr']);
        $this->assertNull($values['adv20_traded_value_idr_actual']);
    }

    public function test_the_alias_does_not_stand_in_for_the_field_it_aliases(): void
    {
        $values = $this->vectorFor($this->bars(25, 100.0, 1000));

        // The contract is explicit that where the explicitly named proxy field does not yet exist,
        // the alias is a gap to close rather than a substitute. Both keys must be present.
        $this->assertArrayHasKey('adv20_close_volume_proxy_idr', $values);
        $this->assertArrayHasKey('dv20_idr', $values);
        $this->assertNotNull($values['adv20_close_volume_proxy_idr']);
    }

    public function test_a_missing_volume_anywhere_in_the_window_yields_null_rather_than_a_partial_average(): void
    {
        $bars = $this->bars(25, 100.0, 1000);
        $bars[10]['volume'] = null;

        $values = $this->vectorFor($bars);

        $this->assertNull($values['adv20_close_volume_proxy_idr']);
        $this->assertNull($values['dv20_idr']);
    }

    public function test_zero_volume_bars_lower_the_proxy_instead_of_being_skipped(): void
    {
        $traded = $this->vectorFor($this->bars(25, 100.0, 1000));

        $bars = $this->bars(25, 100.0, 1000);
        for ($i = 15; $i < 25; $i++) {
            $bars[$i]['volume'] = 0;
        }
        $withZeros = $this->vectorFor($bars);

        // Zero volume is a real source-backed value. Treating it as missing would either null the
        // metric or silently exclude the session, and both misstate what the market did.
        $this->assertNotNull($withZeros['adv20_close_volume_proxy_idr']);
        $this->assertLessThan(
            (float) $traded['adv20_close_volume_proxy_idr'],
            (float) $withZeros['adv20_close_volume_proxy_idr']
        );
    }

    public function test_the_proxy_uses_the_raw_series_even_when_indicators_run_on_the_adjusted_one(): void
    {
        $rawBars = $this->bars(25, 100.0, 1000);

        $adjustedBars = [];
        foreach ($rawBars as $bar) {
            $bar['open'] /= 2;
            $bar['high'] /= 2;
            $bar['low'] /= 2;
            $bar['close'] /= 2;
            $adjustedBars[] = $bar;
        }

        $config = $this->config(['selected_price_product_code' => 'STRUCTURAL_ADJUSTED']);
        $values = (new IndicatorVectorService())
            ->calculateIndicators($adjustedBars, count($adjustedBars) - 1, $config, $rawBars);

        // Adjusted price times raw volume is dimensionally inconsistent and forbidden. The proxy is
        // computed from the raw series, so halving the adjusted price must not halve it.
        $this->assertSame(100000.0, (float) $values['adv20_close_volume_proxy_idr']);
    }

    public function test_raw_volume_is_never_overwritten_by_the_adjusted_analytical_volume(): void
    {
        $rawBars = $this->bars(25, 100.0, 1000);
        $before = array_column($rawBars, 'volume');

        $config = $this->config(['selected_price_product_code' => 'STRUCTURAL_ADJUSTED']);
        (new IndicatorVectorService())->calculateIndicators($rawBars, count($rawBars) - 1, $config, $rawBars);

        // Structural-adjusted volume is a separate analytical field. The canonical series the
        // indicator ran over must come back unchanged, not rescaled in place.
        $this->assertSame($before, array_column($rawBars, 'volume'));
    }

    public function test_a_proxy_repair_never_rewrites_the_raw_volume_it_was_computed_from(): void
    {
        $bars = $this->bars(25, 100.0, 1000);
        $firstPass = $this->vectorFor($bars);

        // Recomputing produces the same proxy from the same raw inputs. A recompute that "repaired"
        // a proxy by adjusting historical volume would show up here as a changed input, and the
        // contract forbids exactly that repair.
        $secondPass = $this->vectorFor($bars);

        $this->assertSame($firstPass['adv20_close_volume_proxy_idr'], $secondPass['adv20_close_volume_proxy_idr']);
        $this->assertSame(1000, $bars[24]['volume']);
        $this->assertSame(1000, $bars[0]['volume']);
    }

    public function test_a_metric_field_implying_traded_value_without_its_basis_is_not_declarable(): void
    {
        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            $field = $label['metric_field'];
            if ($field === 'dv20_idr') {
                // The one alias that predates the prohibition. It is preserved, not propagated,
                // and it carries the retirement condition that makes preservation temporary.
                $this->assertTrue($label['is_compatibility_alias']);
                $this->assertNotSame('', trim((string) $label['retirement_condition']));

                continue;
            }

            $this->assertStringStartsNotWith('dv', $field, 'no new field may be named dv*');

            if (strpos($field, 'proxy') !== false) {
                $this->assertSame(
                    LiquidityMetricLabelRegistry::KIND_PROXY,
                    $label['metric_kind'],
                    $field.' names itself a proxy and must be declared as one'
                );
            }
            if (strpos($field, '_actual') !== false) {
                $this->assertSame(
                    LiquidityMetricLabelRegistry::KIND_ACTUAL,
                    $label['metric_kind'],
                    $field.' names itself actual and must be declared as one'
                );
            }
        }
    }

    public function test_the_row_states_which_liquidity_formula_produced_its_metrics(): void
    {
        $row = (new IndicatorVectorService())->buildRow(
            1,
            $this->bars(25, 100.0, 1000),
            '2026-01-29',
            5,
            9,
            '2026-01-29 18:00:00',
            $this->config()
        );

        $this->assertNotNull($row);
        $this->assertSame(MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION, $row['liquidity_formula_version']);
        $this->assertNotSame(
            $row['formula_version'],
            $row['liquidity_formula_version'],
            'The liquidity formula identity is not the operator-configured indicator set version.'
        );
    }

    public function test_the_proxy_is_rounded_only_at_the_storage_boundary(): void
    {
        // Two decimals is the stored precision of the liquidity columns. A value that needs more is
        // rounded once, on the way out, not accumulated in a rounded form across the window.
        $values = $this->vectorFor($this->bars(25, 100.005, 333));

        $stored = (float) $values['adv20_close_volume_proxy_idr'];
        $this->assertSame(round(100.005 * 333, 2), $stored);
    }

    public function test_no_liquidity_metric_carries_a_ranking_or_recommendation_semantic(): void
    {
        $values = $this->vectorFor($this->bars(25));

        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            $forbidden = ['rank', 'score', 'signal', 'buy', 'sell', 'recommend', 'target', 'timing'];
            foreach ($forbidden as $token) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $token,
                    $label['metric_field'],
                    'A market-data metric is factual context, never a market-timing or ranking output.'
                );
            }
        }

        $this->assertArrayNotHasKey('rank', $values);
        $this->assertArrayNotHasKey('score', $values);
    }

    public function test_every_declared_liquidity_metric_states_its_kind_units_and_market_scope(): void
    {
        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            $this->assertContains($label['metric_kind'], ['ACTUAL', 'PROXY']);
            $this->assertNotSame('', trim((string) $label['unit_code']));
            $this->assertSame('IDX_REGULAR', $label['market_scope']);
            $this->assertContains($label['price_basis'], ['RAW', 'NOT_APPLICABLE']);
            $this->assertNotSame(
                'STRUCTURAL_ADJUSTED',
                $label['price_basis'],
                'A liquidity metric declared on an adjusted basis would assert a forbidden construction.'
            );
        }
    }

    public function test_every_declared_proxy_states_its_window_and_every_per_bar_actual_does_not(): void
    {
        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            if ($label['metric_scope'] === LiquidityMetricLabelRegistry::SCOPE_INDICATOR_ROW) {
                $this->assertSame(20, $label['window_sessions'], $label['metric_field'].' must declare its window');

                continue;
            }
            $this->assertNull($label['window_sessions'], $label['metric_field'].' is a per-session fact, not a window');
        }
    }
}
