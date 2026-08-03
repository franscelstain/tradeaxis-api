<?php

use App\Application\MarketData\Services\MarketDataInvariantGuard;
use PHPUnit\Framework\TestCase;

/**
 * Behavioural cover for the coverage no-bypass rule.
 *
 * `CoverageGateNoBypassStaticGuardTest` asserted that eight coverage field names appear in three
 * service files. That is satisfied by a file that mentions the fields and ignores them.
 *
 * The rule those fields exist to enforce is narrow and worth stating plainly: a run may only
 * become READABLE if it can prove the coverage it claims. Not assert it — prove it, by carrying
 * numbers that are internally consistent and that clear the threshold the run itself declares.
 * Every check below closes one way of publishing a dataset that is not actually complete.
 *
 * The second half of this file covers something the static guard could not reach at all. The
 * guard accepts the same quantity under several names, because the pipeline, the run table and
 * the pointer scan each hand it a differently shaped row. A quantity readable under one shape
 * and invisible under another would apply the whole rule inconsistently depending on the caller.
 */
class CoverageTelemetryBypassTest extends TestCase
{
    private function guard(): MarketDataInvariantGuard
    {
        return new MarketDataInvariantGuard();
    }

    /**
     * A run reporting 99 of 100 tickers against a 98% threshold: complete, consistent, and above
     * the bar it set for itself.
     */
    private function honestReadableState(array $override = []): array
    {
        return array_merge([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_status' => 'PASS',
            'expected_universe_count' => 100,
            'available_eod_count' => 99,
            'missing_eod_count' => 1,
            'coverage_ratio' => 0.99,
            'coverage_threshold_value' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
        ], $override);
    }

    public function test_an_honest_readable_state_is_accepted(): void
    {
        $this->guard()->assertReadableRequiresCoveragePass($this->honestReadableState(), 'unit');

        $this->assertTrue(true);
    }

    /**
     * @dataProvider fabricatedCoverageStates
     */
    public function test_coverage_that_cannot_be_proven_blocks_readable(array $override, string $expectedMessage, string $lie): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->guard()->assertReadableRequiresCoveragePass($this->honestReadableState($override), 'unit: '.$lie);
    }

    public function fabricatedCoverageStates(): array
    {
        return [
            // The one that matters most for a trading day: if universe resolution silently
            // returns nothing, 0 of 0 is arithmetically 100% covered. A day with no universe is
            // not a fully covered day, it is a day that was never evaluated.
            'empty universe' => [
                ['expected_universe_count' => 0, 'available_eod_count' => 0, 'missing_eod_count' => 0, 'coverage_ratio' => 1.0],
                'READABLE requires expected_universe_count > 0',
                'an empty universe claims total coverage',
            ],
            'universe count absent' => [
                ['expected_universe_count' => null],
                'READABLE requires expected_universe_count > 0',
                'the run does not say how many tickers it expected',
            ],
            'available count absent' => [
                ['available_eod_count' => null],
                'READABLE requires available_eod_count >= 0',
                'the run does not say how many tickers it actually got',
            ],
            'missing count absent' => [
                ['missing_eod_count' => null],
                'READABLE requires missing_eod_count >= 0',
                'the run does not say how many tickers it lost',
            ],
            'more bars than tickers' => [
                ['available_eod_count' => 101, 'missing_eod_count' => 0],
                'READABLE requires available_eod_count <= expected_universe_count',
                'the run reports more covered tickers than exist in its universe',
            ],
            'counts do not add up' => [
                ['missing_eod_count' => 0],
                'READABLE requires missing_eod_count = expected_universe_count - available_eod_count',
                'the three counts contradict each other',
            ],
            'ratio absent' => [
                ['coverage_ratio' => null],
                'READABLE requires coverage_ratio',
                'the run states counts but no ratio',
            ],
            'ratio asserted rather than derived' => [
                ['coverage_ratio' => 1.0],
                'READABLE requires coverage_ratio = available_eod_count / expected_universe_count',
                'the ratio disagrees with the counts it is supposed to summarise',
            ],
            'threshold absent' => [
                ['coverage_threshold_value' => null],
                'READABLE requires valid coverage_threshold_value',
                'the run declares no bar to clear',
            ],
            'threshold outside the unit interval' => [
                ['coverage_threshold_value' => 1.5],
                'READABLE requires valid coverage_threshold_value',
                'the threshold is not a ratio',
            ],
            'ratio below its own threshold' => [
                ['available_eod_count' => 50, 'missing_eod_count' => 50, 'coverage_ratio' => 0.5],
                'READABLE requires coverage_ratio >= coverage_threshold_value',
                'the run publishes below the bar it set for itself',
            ],
            'threshold mode absent' => [
                ['coverage_threshold_mode' => null],
                'READABLE requires coverage_threshold_mode',
                'the threshold cannot be interpreted',
            ],
            'universe basis absent' => [
                ['coverage_universe_basis' => null],
                'READABLE requires coverage_universe_basis',
                'the universe cannot be reconstructed later',
            ],
            'contract version absent' => [
                ['coverage_contract_version' => null],
                'READABLE requires coverage_contract_version',
                'the rules the run was judged by are unknown',
            ],
        ];
    }

    /**
     * A run that says PASS in one column and FAIL in another has not passed. Reading whichever
     * field comes first would let the outcome depend on field order.
     */
    public function test_contradictory_coverage_verdicts_are_not_treated_as_pass(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires coverage PASS');

        $this->guard()->assertReadableRequiresCoveragePass($this->honestReadableState([
            'coverage_gate_status' => 'PASS',
            'coverage_gate_state' => 'FAIL',
        ]), 'unit');
    }

    /**
     * The same coverage facts, written the way each caller writes them.
     *
     * @return array<string, array{0: array<string, mixed>}>
     */
    public function equivalentRowShapes(): array
    {
        return [
            // What the pipeline and finalize DTOs pass in.
            'pipeline shape' => [[
                'coverage_gate_status' => 'PASS',
                'expected_universe_count' => 100,
                'available_eod_count' => 99,
                'missing_eod_count' => 1,
                'coverage_ratio' => 0.99,
                'coverage_threshold_value' => 0.98,
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                'coverage_contract_version' => 'coverage_gate_v1',
            ]],
            // What a row selected straight out of eod_runs looks like.
            'run row shape' => [[
                'coverage_gate_state' => 'PASS',
                'coverage_universe_count' => 100,
                'coverage_available_count' => 99,
                'coverage_missing_count' => 1,
                'coverage_ratio' => '0.990000',
                'coverage_min_threshold' => '0.980000',
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                'coverage_contract_version' => 'coverage_gate_v1',
            ]],
            // What the current-pointer integrity scan produces, where run columns are aliased
            // apart from the publication columns they are joined to.
            'pointer scan shape' => [[
                'run_coverage_gate_state' => 'PASS',
                'run_coverage_universe_count' => 100,
                'run_coverage_available_count' => 99,
                'run_coverage_missing_count' => 1,
                'run_coverage_ratio' => '0.990000',
                'run_coverage_min_threshold' => '0.980000',
                'run_coverage_threshold_mode' => 'MIN_RATIO',
                'run_coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                'run_coverage_contract_version' => 'coverage_gate_v1',
            ]],
            // What the finalize outcome service nests under a coverage_summary key.
            'nested coverage summary shape' => [[
                'coverage_summary' => [
                    'coverage_gate_status' => 'PASS',
                    'expected_universe_count' => 100,
                    'available_eod_count' => 99,
                    'missing_eod_count' => 1,
                    'coverage_ratio' => 0.99,
                    'coverage_threshold_value' => 0.98,
                    'coverage_threshold_mode' => 'MIN_RATIO',
                    'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
                    'coverage_contract_version' => 'coverage_gate_v1',
                ],
            ]],
        ];
    }

    /**
     * @dataProvider equivalentRowShapes
     */
    public function test_every_caller_shape_expresses_the_same_honest_state(array $coverage): void
    {
        $this->guard()->assertReadableRequiresCoveragePass(array_merge([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
        ], $coverage), 'unit');

        $this->assertTrue(true);
    }

    /**
     * Acceptance agreeing across shapes is not enough: a shape could accept everything. Each
     * shape must also reject, or a caller using it would bypass the rule entirely.
     *
     * @dataProvider equivalentRowShapes
     */
    public function test_every_caller_shape_still_catches_a_ratio_that_was_asserted_not_derived(array $coverage): void
    {
        foreach (['coverage_ratio', 'run_coverage_ratio'] as $ratioKey) {
            if (array_key_exists($ratioKey, $coverage)) {
                $coverage[$ratioKey] = 1.0;
            }
        }

        if (isset($coverage['coverage_summary'])) {
            $coverage['coverage_summary']['coverage_ratio'] = 1.0;
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires coverage_ratio = available_eod_count / expected_universe_count');

        $this->guard()->assertReadableRequiresCoveragePass(array_merge([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
        ], $coverage), 'unit');
    }

    /**
     * Ratios are stored as DECIMAL and read back as strings. Comparing them as strings, or
     * demanding exact float equality, would reject an honest run.
     */
    public function test_a_ratio_stored_as_a_decimal_string_is_accepted(): void
    {
        $this->guard()->assertReadableRequiresCoveragePass($this->honestReadableState([
            'expected_universe_count' => '803',
            'available_eod_count' => '803',
            'missing_eod_count' => '0',
            'coverage_ratio' => '1.000000',
            'coverage_threshold_value' => '0.980000',
        ]), 'unit');

        $this->assertTrue(true);
    }
}
