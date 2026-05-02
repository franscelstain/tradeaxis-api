<?php

use App\Application\MarketData\Services\MarketDataInvariantGuard;
use PHPUnit\Framework\TestCase;

class MarketDataInvariantGuardTest extends TestCase
{
    public function test_readable_requires_success_and_complete_coverage_pass_context()
    {
        $guard = new MarketDataInvariantGuard();

        $guard->assertNoBypassState($this->readablePassState([
            'promotion_allowed' => true,
        ]), 'unit');

        $this->assertTrue(true);
    }

    public function test_readable_without_coverage_pass_fails_fast()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires coverage PASS');

        (new MarketDataInvariantGuard())->assertNoBypassState($this->readablePassState([
            'coverage_gate_status' => 'FAIL',
        ]), 'unit');
    }

    public function test_readable_with_incomplete_coverage_context_fails_fast()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('READABLE requires expected_universe_count > 0');

        (new MarketDataInvariantGuard())->assertNoBypassState([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_status' => 'PASS',
        ], 'unit');
    }

    public function test_readable_with_mismatched_coverage_counts_fails_fast()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('missing_eod_count = expected_universe_count - available_eod_count');

        (new MarketDataInvariantGuard())->assertNoBypassState($this->readablePassState([
            'missing_eod_count' => 0,
        ]), 'unit');
    }

    public function test_promotion_allowed_cannot_bypass_readable_matrix()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('promotion_allowed requires SUCCESS + READABLE + coverage PASS');

        (new MarketDataInvariantGuard())->assertNoBypassState($this->readablePassState([
            'publishability_state' => 'NOT_READABLE',
            'promotion_allowed' => true,
        ]), 'unit');
    }

    public function test_pointer_target_requires_success_readable_pass_with_complete_coverage_context()
    {
        $publication = (object) [
            'trade_date' => '2026-04-21',
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-21 17:20:00',
        ];

        $run = (object) $this->readablePassState([
            'coverage_gate_state' => 'PASS',
        ]);

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

        $run = (object) $this->readablePassState([
            'coverage_gate_state' => 'FAIL',
        ]);

        (new MarketDataInvariantGuard())->assertValidFallbackTarget($publication, $run, '2026-04-20', 'unit');
    }

    private function readablePassState(array $override = []): array
    {
        return array_merge([
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_status' => 'PASS',
            'coverage_gate_state' => 'PASS',
            'expected_universe_count' => 100,
            'available_eod_count' => 99,
            'missing_eod_count' => 1,
            'coverage_ratio' => 0.99,
            'coverage_threshold_value' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'promotion_allowed' => false,
        ], $override);
    }
}
