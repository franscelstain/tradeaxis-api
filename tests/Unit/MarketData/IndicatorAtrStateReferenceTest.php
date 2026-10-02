<?php

use App\Application\MarketData\Services\IndicatorVectorService;

/**
 * F-MD-B10-A002-005 G2. Audit_Hash_and_Reproducibility_Contract_LOCKED.md:75 requires the indicator
 * row to carry the Wilder ATR value and percentage "and stable recursive-state reference";
 * EOD_Indicators_Formula_Spec.md:71 lets the runtime either persist versioned recursive state or
 * recompute from the stable chain. This runtime recomputes, so the reference must identify the
 * chain the published value was recomputed from. E-MD-B10-A002-016 found no producer assigned
 * `atr_state_ref` at all: it existed only in test fixtures.
 *
 * These tests drive the production row builder, not a hand-set value.
 */
class IndicatorAtrStateReferenceTest extends TestCase
{
    private const REF = '/^atr-state\/v1:[a-f0-9]{64}$/';

    private function config(array $overrides = []): array
    {
        return $overrides + [
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
        ];
    }

    /** Bars with a true range that varies, so the chain is a real recursion. */
    private function bars(int $count, float $start = 100.0): array
    {
        $bars = [];
        $close = $start;
        for ($i = 1; $i <= $count; $i++) {
            $close += (($i * 7) % 5) - 2;
            $bars[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-01-01 +'.$i.' days')),
                'open' => $close - 1, 'high' => $close + 2 + ($i % 3), 'low' => $close - 2 - ($i % 4),
                'close' => $close, 'adj_close' => 1, 'volume' => 1000 + $i,
            ];
        }

        return $bars;
    }

    private function row(array $bars, int $tickerId = 1, int $publicationId = 55, int $runId = 9001, array $series = null, array $config = null): array
    {
        $date = $bars[count($bars) - 1]['trade_date'];

        return (new IndicatorVectorService())->buildRow(
            $tickerId, $bars, $date, $publicationId, $runId, '2026-05-25 18:00:00', $config ?? $this->config(), $series
        );
    }

    public function test_the_producer_assigns_a_content_addressed_reference_beside_the_atr_value(): void
    {
        $row = $this->row($this->bars(60));

        $this->assertNotNull($row['atr14']);
        $this->assertNotNull($row['atr14_pct']);
        $this->assertMatchesRegularExpression(self::REF, (string) $row['atr_state_ref']);
        $this->assertLessThanOrEqual(128, strlen($row['atr_state_ref']), 'it must fit the persisted column');
    }

    public function test_equal_chains_have_equal_references_whatever_the_local_allocation(): void
    {
        $bars = $this->bars(60);
        $a = $this->row($bars, 1, 55, 9001);
        $b = $this->row($bars, 4242, 9999, 777);

        $this->assertSame($a['atr_state_ref'], $b['atr_state_ref']);
        $this->assertSame($a['atr14'], $b['atr14']);

        // Extra, non-semantic bar members do not enter it either.
        $noisy = $bars;
        foreach ($noisy as $index => $bar) {
            $noisy[$index]['adj_close'] = 99999;
            $noisy[$index]['source_observation_id'] = 5000 + $index;
            $noisy[$index]['volume'] += 5;
        }
        $this->assertSame($a['atr_state_ref'], $this->row($noisy)['atr_state_ref']);
    }

    public function test_a_changed_historical_true_range_changes_the_reference(): void
    {
        $bars = $this->bars(60);
        $baseline = $this->row($bars);

        $earlyHigh = $bars;
        $earlyHigh[20]['high'] += 0.5;
        $changed = $this->row($earlyHigh);
        $this->assertNotSame($baseline['atr_state_ref'], $changed['atr_state_ref']);
        $this->assertNotSame($baseline['atr14'], $changed['atr14'], 'the recursion carries the change forward');

        $earlyClose = $bars;
        $earlyClose[5]['close'] += 0.25;
        $this->assertNotSame($baseline['atr_state_ref'], $this->row($earlyClose)['atr_state_ref']);
    }

    public function test_a_consumed_input_change_that_leaves_the_value_unchanged_still_moves_the_reference(): void
    {
        // A gap-down bar whose true range is set by |low - previous close|: moving its high inside
        // that range leaves every true range, and so the ATR, exactly as it was. The reference
        // names the chain that was consumed, so it still moves; it is not a hash of the output.
        $bars = $this->bars(60);
        $previousClose = $bars[29]['close'];
        $bars[30]['low'] = $previousClose - 10;
        $bars[30]['high'] = $previousClose - 8;
        $bars[30]['close'] = $previousClose - 9;
        $bars[30]['open'] = $previousClose - 9;
        $baseline = $this->row($bars);

        $moved = $bars;
        $moved[30]['high'] = $previousClose - 7;
        $changed = $this->row($moved);

        $this->assertSame($baseline['atr14'], $changed['atr14'], 'precondition: the published value is identical');
        $this->assertNotSame($baseline['atr_state_ref'], $changed['atr_state_ref']);
    }
    public function test_the_seed_boundary_the_window_and_the_formula_are_part_of_the_identity(): void
    {
        $bars = $this->bars(60);
        $baseline = $this->row($bars);

        // The same loaded bars, but with a longer stable series seeding the recursion, are a
        // different chain: the reference says where the recursion was seeded.
        $series = $this->bars(80);
        $seeded = $this->row(array_slice($series, 20), 1, 55, 9001, $series);
        $this->assertNotSame($baseline['atr_state_ref'], $seeded['atr_state_ref']);
        $this->assertNotNull($seeded['atr14']);

        $window = $this->row($bars, 1, 55, 9001, null, $this->config(['atr_window_days' => 10]));
        $this->assertNotSame($baseline['atr_state_ref'], $window['atr_state_ref']);

        $formula = $this->row($bars, 1, 55, 9001, null, $this->config(['set_version' => 'ind_v2']));
        $this->assertNotSame($baseline['atr_state_ref'], $formula['atr_state_ref']);

        $moved = $this->bars(60);
        foreach ($moved as $index => $bar) {
            $moved[$index]['trade_date'] = date('Y-m-d', strtotime($bar['trade_date'].' +1 day'));
        }
        $this->assertNotSame($baseline['atr_state_ref'], $this->row($moved)['atr_state_ref'], 'a shifted seed date is another chain');
    }

    public function test_the_price_basis_the_recursion_consumed_is_part_of_the_identity(): void
    {
        $bars = $this->bars(60);
        $baseline = $this->row($bars);

        $split = $this->config(['price_adjustment_factors' => [[
            'ex_date' => $bars[30]['trade_date'],
            'price_factor' => 0.5,
            'volume_factor' => 2.0,
            'action_type_code' => 'STOCK_SPLIT',
        ]]]);
        $adjusted = $this->row($bars, 1, 55, 9001, null, $split);

        $this->assertNotSame($baseline['atr_state_ref'], $adjusted['atr_state_ref']);
    }

    public function test_no_atr_means_no_reference(): void
    {
        $short = $this->row($this->bars(10));
        $this->assertNull($short['atr14']);
        $this->assertNull($short['atr_state_ref'], 'a reference without a value would name a chain that produced nothing');
    }

    public function test_a_quarantined_atr_is_withdrawn_together_with_its_reference(): void
    {
        $bars = $this->bars(60);
        $config = $this->config([
            'atr_contamination_horizon_days' => 60,
            'corporate_action_contamination' => [[
                'action_type_code' => 'STOCK_SPLIT',
                'action_date' => $bars[50]['trade_date'],
                'depth' => 9,
                'breaks_price_continuity' => true,
                'breaks_volume_continuity' => true,
            ]],
        ]);
        $row = $this->row($bars, 1, 55, 9001, null, $config);

        $this->assertNull($row['atr14'], 'precondition: the quarantine withdrew the value');
        $this->assertNull($row['atr_state_ref']);
    }

    public function test_the_loaded_window_fallback_is_distinguishable_from_the_stable_series(): void
    {
        // The registry calls the loaded-window fallback an approximation. Identical numbers seeded
        // from the window and from the stable series must not claim the same chain.
        $bars = $this->bars(60);
        $fromWindow = $this->row($bars);
        $fromSeries = $this->row($bars, 1, 55, 9001, array_merge($this->bars(1, 90.0), $bars));

        $this->assertNotNull($fromSeries['atr14']);
        $this->assertNotSame($fromWindow['atr_state_ref'], $fromSeries['atr_state_ref']);
    }
}
