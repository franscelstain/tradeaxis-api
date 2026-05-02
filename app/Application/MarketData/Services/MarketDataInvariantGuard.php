<?php

namespace App\Application\MarketData\Services;

class MarketDataInvariantGuard
{
    public function assertReadableRequiresCoveragePass($state, $context = 'state'): void
    {
        $terminalStatus = $this->value($state, 'terminal_status');
        $publishabilityState = $this->value($state, 'publishability_state');
        $coverageGateState = $this->coverageState($state);

        if ($publishabilityState === 'READABLE' && $terminalStatus !== 'SUCCESS') {
            throw new \LogicException($context.': READABLE requires terminal_status SUCCESS.');
        }

        if ($publishabilityState === 'READABLE' && $coverageGateState !== 'PASS') {
            throw new \LogicException($context.': READABLE requires coverage PASS.');
        }

        if ($publishabilityState === 'READABLE') {
            $this->assertCoverageTelemetryCompleteForReadable($state, $context);
        }
    }

    public function assertValidFallbackTarget($publication, $run = null, $tradeDate = null, $context = 'fallback'): void
    {
        if (! $publication) {
            throw new \LogicException($context.': fallback target publication is missing.');
        }

        if ($tradeDate !== null && (string) $this->value($publication, 'trade_date') !== (string) $tradeDate) {
            throw new \LogicException($context.': fallback target trade_date mismatch.');
        }

        if ($this->value($publication, 'seal_state') !== 'SEALED' || ! $this->truthy($this->value($publication, 'sealed_at'))) {
            throw new \LogicException($context.': fallback target must be SEALED with sealed_at.');
        }

        $effectiveRun = $run ?: $publication;
        $this->assertRunAllowsReadablePublication($effectiveRun, $context.': fallback target');
    }

    public function assertValidPointerTarget($publication, $run = null, $tradeDate = null, $context = 'pointer'): void
    {
        if (! $publication) {
            throw new \LogicException($context.': pointer target publication is missing.');
        }

        if ($tradeDate !== null && (string) $this->value($publication, 'trade_date') !== (string) $tradeDate) {
            throw new \LogicException($context.': pointer target trade_date mismatch.');
        }

        if ($this->value($publication, 'seal_state') !== 'SEALED' || ! $this->truthy($this->value($publication, 'sealed_at'))) {
            throw new \LogicException($context.': pointer target must be SEALED with sealed_at.');
        }

        $effectiveRun = $run ?: $publication;
        $this->assertRunAllowsReadablePublication($effectiveRun, $context.': pointer target');
    }

    public function assertNoBypassState($state, $context = 'state'): void
    {
        $terminalStatus = $this->value($state, 'terminal_status');
        $publishabilityState = $this->value($state, 'publishability_state');
        $coverageGateState = $this->coverageState($state);
        $promotionAllowed = $this->value($state, 'promotion_allowed');

        $this->assertReadableRequiresCoveragePass($state, $context);

        if (in_array($terminalStatus, ['FAILED', 'HELD'], true) && $publishabilityState !== 'NOT_READABLE') {
            throw new \LogicException($context.': FAILED/HELD requires NOT_READABLE.');
        }

        if (in_array($terminalStatus, ['FAILED', 'HELD'], true) && ! $this->truthy($this->value($state, 'reason_code', $this->coverageField($state, 'coverage_reason_code')))) {
            throw new \LogicException($context.': FAILED/HELD requires explicit reason_code.');
        }

        if ($promotionAllowed === true && ($terminalStatus !== 'SUCCESS' || $publishabilityState !== 'READABLE' || $coverageGateState !== 'PASS')) {
            throw new \LogicException($context.': promotion_allowed requires SUCCESS + READABLE + coverage PASS.');
        }

        if ($promotionAllowed === true) {
            $this->assertCoverageTelemetryCompleteForReadable($state, $context.': promotion_allowed');
        }
    }

    private function assertRunAllowsReadablePublication($run, $context): void
    {
        if ($this->value($run, 'terminal_status') !== 'SUCCESS') {
            throw new \LogicException($context.' requires run terminal_status SUCCESS.');
        }

        if ($this->value($run, 'publishability_state') !== 'READABLE') {
            throw new \LogicException($context.' requires run publishability_state READABLE.');
        }

        if ($this->coverageState($run) !== 'PASS') {
            throw new \LogicException($context.' requires run coverage PASS.');
        }

        $this->assertCoverageTelemetryCompleteForReadable($run, $context);
    }

    private function assertCoverageTelemetryCompleteForReadable($state, $context): void
    {
        $expected = $this->numericCoverageField($state, 'expected_universe_count', ['coverage_universe_count', 'run_coverage_universe_count']);
        $available = $this->numericCoverageField($state, 'available_eod_count', ['coverage_available_count', 'run_coverage_available_count']);
        $missing = $this->numericCoverageField($state, 'missing_eod_count', ['coverage_missing_count', 'run_coverage_missing_count']);
        $ratio = $this->numericCoverageField($state, 'coverage_ratio', ['run_coverage_ratio']);
        $threshold = $this->numericCoverageField($state, 'coverage_threshold_value', ['coverage_min_threshold', 'run_coverage_min_threshold']);
        $thresholdMode = $this->coverageField($state, 'coverage_threshold_mode', ['run_coverage_threshold_mode']);
        $universeBasis = $this->coverageField($state, 'coverage_universe_basis', ['run_coverage_universe_basis']);
        $contractVersion = $this->coverageField($state, 'coverage_contract_version', ['coverage_calibration_version', 'run_coverage_contract_version']);

        if ($expected === null || $expected <= 0) {
            throw new \LogicException($context.': READABLE requires expected_universe_count > 0.');
        }

        if ($available === null || $available < 0) {
            throw new \LogicException($context.': READABLE requires available_eod_count >= 0.');
        }

        if ($missing === null || $missing < 0) {
            throw new \LogicException($context.': READABLE requires missing_eod_count >= 0.');
        }

        if ($available > $expected) {
            throw new \LogicException($context.': READABLE requires available_eod_count <= expected_universe_count.');
        }

        if ((int) $missing !== ((int) $expected - (int) $available)) {
            throw new \LogicException($context.': READABLE requires missing_eod_count = expected_universe_count - available_eod_count.');
        }

        if ($ratio === null) {
            throw new \LogicException($context.': READABLE requires coverage_ratio.');
        }

        $expectedRatio = $available / $expected;
        if (abs((float) $ratio - (float) $expectedRatio) > 0.0000001) {
            throw new \LogicException($context.': READABLE requires coverage_ratio = available_eod_count / expected_universe_count.');
        }

        if ($threshold === null || $threshold < 0 || $threshold > 1) {
            throw new \LogicException($context.': READABLE requires valid coverage_threshold_value.');
        }

        if ((float) $ratio + 0.0000001 < (float) $threshold) {
            throw new \LogicException($context.': READABLE requires coverage_ratio >= coverage_threshold_value.');
        }

        if (! $this->truthy($thresholdMode)) {
            throw new \LogicException($context.': READABLE requires coverage_threshold_mode.');
        }

        if (! $this->truthy($universeBasis)) {
            throw new \LogicException($context.': READABLE requires coverage_universe_basis.');
        }

        if (! $this->truthy($contractVersion)) {
            throw new \LogicException($context.': READABLE requires coverage_contract_version.');
        }
    }

    private function coverageState($state)
    {
        $values = [];
        foreach (['coverage_gate_state', 'coverage_gate_status', 'run_coverage_gate_state'] as $key) {
            $value = $this->coverageField($state, $key);
            if ($value !== null && $value !== '') {
                $values[] = $value;
            }
        }

        $values = array_values(array_unique($values));
        if (count($values) > 1) {
            return 'NOT_EVALUABLE';
        }

        return $values[0] ?? null;
    }

    private function coverageField($state, $key, array $aliases = [])
    {
        $keys = array_merge([$key], $aliases);
        foreach ($keys as $candidate) {
            $value = $this->value($state, $candidate, null);
            if ($value !== null) {
                return $value;
            }
        }

        if (is_array($state) && isset($state['coverage_summary']) && is_array($state['coverage_summary'])) {
            foreach ($keys as $candidate) {
                if (array_key_exists($candidate, $state['coverage_summary'])) {
                    return $this->normalize($state['coverage_summary'][$candidate]);
                }
            }
        }

        if (is_object($state) && isset($state->coverage_summary) && is_array($state->coverage_summary)) {
            foreach ($keys as $candidate) {
                if (array_key_exists($candidate, $state->coverage_summary)) {
                    return $this->normalize($state->coverage_summary[$candidate]);
                }
            }
        }

        return null;
    }

    private function numericCoverageField($state, $key, array $aliases = [])
    {
        $value = $this->coverageField($state, $key, $aliases);

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return $value + 0;
    }

    private function value($state, $key, $default = null)
    {
        if (is_array($state)) {
            return array_key_exists($key, $state) ? $this->normalize($state[$key]) : $default;
        }

        if (is_object($state) && isset($state->{$key})) {
            return $this->normalize($state->{$key});
        }

        return $default;
    }

    private function normalize($value)
    {
        return is_string($value) ? strtoupper($value) : $value;
    }

    private function truthy($value): bool
    {
        return $value !== null && $value !== '' && $value !== false;
    }
}
