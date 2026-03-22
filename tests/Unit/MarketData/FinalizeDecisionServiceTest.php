<?php

use App\Application\MarketData\Services\FinalizeDecisionService;
use PHPUnit\Framework\TestCase;

class FinalizeDecisionServiceTest extends TestCase
{
    public function test_finalize_blocks_before_cutoff_and_keeps_fallback_effective_date()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(false, true, 'SEALED', 1.0, 0.95, '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('HELD', $decision['terminal_status']);
        $this->assertSame('RUN_FINALIZE_BEFORE_CUTOFF', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_finalize_blocks_when_candidate_publication_not_sealed()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'UNSEALED', 1.0, 0.95, null);

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('RUN_SEAL_PRECONDITION_FAILED', $decision['reason_code']);
    }

    public function test_finalize_blocks_when_coverage_below_threshold()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', 0.80, 0.95, '2026-04-20');

        $this->assertFalse($decision['promotion_allowed']);
        $this->assertSame('FAIL', $decision['quality_gate_state']);
        $this->assertSame('RUN_COVERAGE_LOW', $decision['reason_code']);
        $this->assertSame('2026-04-20', $decision['trade_date_effective']);
    }

    public function test_finalize_allows_promotion_when_cutoff_seal_and_coverage_are_valid()
    {
        $service = new FinalizeDecisionService();
        $decision = $service->evaluate(true, true, 'SEALED', 0.98, 0.95, '2026-04-20');

        $this->assertTrue($decision['promotion_allowed']);
        $this->assertSame('PASS', $decision['quality_gate_state']);
        $this->assertNull($decision['reason_code']);
    }
}
