<?php

use App\Application\MarketData\Services\MarketDataInvariantGuard;
use PHPUnit\Framework\TestCase;

class MarketDataInvariantGuardTest extends TestCase
{
    public function test_readable_requires_success_and_coverage_pass()
    {
        $guard = new MarketDataInvariantGuard();

        $guard->assertNoBypassState([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_status' => 'PASS',
            'promotion_allowed' => true,
        ], 'unit');

        $this->assertTrue(true);
    }

    public function test_readable_without_coverage_pass_fails_fast()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires coverage PASS');

        (new MarketDataInvariantGuard())->assertNoBypassState([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_status' => 'FAIL',
        ], 'unit');
    }

    public function test_promotion_allowed_cannot_bypass_readable_matrix()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('promotion_allowed requires SUCCESS + READABLE + coverage PASS');

        (new MarketDataInvariantGuard())->assertNoBypassState([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'NOT_READABLE',
            'coverage_gate_status' => 'PASS',
            'promotion_allowed' => true,
        ], 'unit');
    }

    public function test_pointer_target_requires_success_readable_pass()
    {
        $publication = (object) [
            'trade_date' => '2026-04-21',
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-21 17:20:00',
        ];

        $run = (object) [
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
        ];

        (new MarketDataInvariantGuard())->assertValidPointerTarget($publication, $run, '2026-04-21', 'unit');
        $this->assertTrue(true);
    }

    public function test_fallback_target_requires_prior_success_readable_pass()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('fallback target requires run coverage PASS');

        $publication = (object) [
            'trade_date' => '2026-04-20',
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-20 17:20:00',
        ];

        $run = (object) [
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'FAIL',
        ];

        (new MarketDataInvariantGuard())->assertValidFallbackTarget($publication, $run, '2026-04-20', 'unit');
    }
}
