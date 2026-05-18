<?php

use App\Application\MarketData\Services\CoverageGateStateNormalizer;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use PHPUnit\Framework\TestCase;

class CoveragePolicyLegacyBlockedNormalizationTest extends TestCase
{
    public function test_legacy_blocked_coverage_gate_state_normalizes_to_not_evaluable(): void
    {
        $this->assertSame('PASS', CoverageGateStateNormalizer::normalize('PASS'));
        $this->assertSame('FAIL', CoverageGateStateNormalizer::normalize('FAIL'));
        $this->assertSame('NOT_EVALUABLE', CoverageGateStateNormalizer::normalize('NOT_EVALUABLE'));
        $this->assertSame('NOT_EVALUABLE', CoverageGateStateNormalizer::normalize('BLOCKED'));
        $this->assertSame('BLOCKED', CoverageGateStateNormalizer::legacyRaw('BLOCKED'));
        $this->assertNull(CoverageGateStateNormalizer::legacyRaw('NOT_EVALUABLE'));
    }

    public function test_run_repository_normalizes_legacy_coverage_input_without_changing_quality_blocked(): void
    {
        $repository = new EodRunRepository();
        $method = new ReflectionMethod(EodRunRepository::class, 'normalizeTelemetry');
        $method->setAccessible(true);

        $normalized = $method->invoke($repository, [
            'coverage_gate_status' => 'BLOCKED',
            'quality_gate_state' => 'BLOCKED',
        ]);

        $this->assertSame('NOT_EVALUABLE', $normalized['coverage_gate_state']);
        $this->assertArrayNotHasKey('coverage_gate_status', $normalized);
        $this->assertSame('BLOCKED', $normalized['quality_gate_state']);
    }
}
