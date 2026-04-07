<?php

use App\Application\MarketData\Services\FinalizeDecisionService;
use PHPUnit\Framework\TestCase;

class FinalizeDecisionServiceTest extends TestCase
{
    public function test_finalize_blocks_before_cutoff_and_keeps_fallback_effective_date()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(false, true, 'SEALED', [
            'coverage_gate_status' => 'PASS',
            'coverage_ratio' => 1.0,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('BLOCKED', $decision['quality_gate_state']);
        $this->assertSame('RUN_FINALIZE_BEFORE_CUTOFF', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_finalize_blocks_when_candidate_publication_not_sealed()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'UNSEALED', [
            'coverage_gate_status' => 'PASS',
            'coverage_ratio' => 1.0,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('BLOCKED', $decision['quality_gate_state']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('RUN_SEAL_PRECONDITION_FAILED', $decision['reason_code']);
    }

    public function test_finalize_uses_coverage_pass_to_allow_readable_promotion()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', [
            'coverage_gate_status' => 'PASS',
            'coverage_ratio' => 0.98,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], '2026-04-20');

        $this->assertTrue($decision['promotion_allowed']);
        $this->assertSame('PASS', $decision['coverage_gate_status']);
        $this->assertSame('PASS', $decision['quality_gate_state']);
        $this->assertSame('SUCCESS', $decision['terminal_status']);
        $this->assertSame('READABLE', $decision['publishability_state']);
        $this->assertNull($decision['reason_code']);
    }

    public function test_finalize_uses_coverage_fail_with_fallback_as_held_not_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', [
            'coverage_gate_status' => 'FAIL',
            'coverage_ratio' => 0.80,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAIL', $decision['coverage_gate_status']);
        $this->assertSame('FAIL', $decision['quality_gate_state']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_COVERAGE_LOW', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_finalize_uses_coverage_fail_without_fallback_as_failed_not_readable()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', [
            'coverage_gate_status' => 'FAIL',
            'coverage_ratio' => 0.80,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAILED', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_COVERAGE_LOW', $decision['reason_code']);
    }

    public function test_finalize_uses_blocked_as_non_readable_and_never_promotes()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', [
            'coverage_gate_status' => 'BLOCKED',
            'coverage_ratio' => null,
            'coverage_threshold_value' => 0.95,
            'coverage_threshold_mode' => 'MIN_RATIO',
        ], '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('BLOCKED', $decision['coverage_gate_status']);
        $this->assertSame('BLOCKED', $decision['quality_gate_state']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('NOT_READABLE', $decision['publishability_state']);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $decision['reason_code']);
    }
}
