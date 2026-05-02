<?php

use App\Application\MarketData\Services\FinalizeDecisionService;
use PHPUnit\Framework\TestCase;

class FinalizeDecisionServiceTest extends TestCase
{
    public function test_finalize_blocks_before_cutoff_and_keeps_fallback_effective_date()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(false, true, 'SEALED', $this->coverage('PASS', 100, 100, 0.95), '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('BLOCKED', $decision['quality_gate_state']);
        $this->assertSame('RUN_FINALIZE_BEFORE_CUTOFF', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_finalize_blocks_when_candidate_publication_not_sealed()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'UNSEALED', $this->coverage('PASS', 100, 100, 0.95), null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('BLOCKED', $decision['quality_gate_state']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('RUN_SEAL_PRECONDITION_FAILED', $decision['reason_code']);
    }

    public function test_finalize_uses_coverage_pass_to_allow_readable_promotion()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('PASS', 100, 98, 0.95), '2026-04-20');

        $this->assertTrue($decision['promotion_allowed']);
        $this->assertSame('PASS', $decision['coverage_gate_status']);
        $this->assertSame('PASS', $decision['quality_gate_state']);
        $this->assertSame('SUCCESS', $decision['terminal_status']);
        $this->assertSame('READABLE', $decision['publishability_state']);
        $this->assertNull($decision['reason_code']);
    }

    public function test_finalize_downgrades_incomplete_pass_to_not_evaluable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', [
            'coverage_gate_status' => 'PASS',
            'coverage_ratio' => 1.0,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('NOT_EVALUABLE', $decision['coverage_gate_status']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $decision['reason_code']);
    }

    public function test_finalize_uses_coverage_fail_with_fallback_as_held_not_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 100, 80, 0.95), '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAIL', $decision['coverage_gate_status']);
        $this->assertSame('FAIL', $decision['quality_gate_state']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_PARTIAL_DATA', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_finalize_uses_coverage_fail_without_fallback_as_failed_not_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 100, 80, 0.95), null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_PARTIAL_DATA', $decision['reason_code']);
    }

    public function test_finalize_uses_blocked_as_non_readable_and_never_promotes()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('NOT_EVALUABLE', 0, 0, 0.95), '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('NOT_EVALUABLE', $decision['coverage_gate_status']);
        $this->assertSame('BLOCKED', $decision['quality_gate_state']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $decision['reason_code']);
    }

    public function test_finalize_allows_repair_candidate_non_current_without_current_promotion()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 901, 5, 0.98), '2026-04-20', [
            'promote_mode' => 'repair_candidate',
            'publish_target' => 'repair_candidate',
        ]);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('SUCCESS', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_REPAIR_CANDIDATE_PARTIAL', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_manual_file_partial_strict_without_fallback_stays_not_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 901, 5, 0.98), null, [
            'source_mode' => 'manual_file',
            'promote_mode' => 'full_publish',
            'publish_target' => 'current_replace',
        ]);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_PARTIAL_DATA', $decision['reason_code']);
        $this->assertSame('COVERAGE_GATE_STRICT_HYBRID', $decision['manual_file_policy']);
        $this->assertFalse($decision['coverage_override_allowed']);
    }

    public function test_manual_file_partial_hybrid_with_fallback_is_held_and_keeps_fallback_effective_date()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 901, 5, 0.98), '2026-03-19', [
            'source_mode' => 'manual_file',
            'promote_mode' => 'full_publish',
            'publish_target' => 'current_replace',
        ]);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('2026-03-19', $decision['trade_date_effective']);
        $this->assertSame('RUN_PARTIAL_DATA', $decision['reason_code']);
        $this->assertSame('COVERAGE_GATE_STRICT_HYBRID', $decision['manual_file_policy']);
        $this->assertFalse($decision['coverage_override_allowed']);
    }

    public function test_manual_file_partial_does_not_create_readable_with_override_state()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 901, 5, 0.98), '2026-03-19', [
            'source_mode' => 'manual_file',
            'promote_mode' => 'full_publish',
            'publish_target' => 'current_replace',
            'allow_partial' => true,
        ]);

        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertNotSame('READABLE_WITH_OVERRIDE', $decision['publishability_state']);
        $this->assertFalse($decision['promotion_allowed']);
        $this->assertFalse($decision['coverage_override_allowed']);
    }

    public function test_partial_dataset_uses_specific_reason_code_and_never_becomes_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 900, 400, 0.98), null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_PARTIAL_DATA', $decision['reason_code']);
    }

    public function test_delayed_dataset_inside_window_is_held_not_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', $this->coverage('FAIL', 900, 630, 0.98, [
            'edge_case_reason_code' => 'RUN_DATA_DELAYED',
        ]), null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_DATA_DELAYED', $decision['reason_code']);
    }

    private function coverage(string $state, int $expected, int $available, float $threshold, array $override = []): array
    {
        $ratio = $expected > 0 ? $available / $expected : null;

        return array_merge([
            'coverage_gate_status' => $state,
            'coverage_gate_state' => $state,
            'coverage_ratio' => $ratio,
            'coverage_threshold_value' => $threshold,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'expected_universe_count' => $expected,
            'available_eod_count' => $available,
            'missing_eod_count' => max(0, $expected - $available),
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
        ], $override);
    }
}
