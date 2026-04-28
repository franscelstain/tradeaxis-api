<?php

namespace App\Application\MarketData\Services;

class MarketDataInvariantGuard
{
    public function assertReadableRequiresCoveragePass($state, $context = 'state'): void
    {
        $terminalStatus = $this->value($state, 'terminal_status');
        $publishabilityState = $this->value($state, 'publishability_state');
        $coverageGateState = $this->value($state, 'coverage_gate_state', $this->value($state, 'coverage_gate_status'));

        if ($publishabilityState === 'READABLE' && $terminalStatus !== 'SUCCESS') {
            throw new \LogicException($context.': READABLE requires terminal_status SUCCESS.');
        }

        if ($publishabilityState === 'READABLE' && $coverageGateState !== 'PASS') {
            throw new \LogicException($context.': READABLE requires coverage PASS.');
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
        $coverageGateState = $this->value($state, 'coverage_gate_state', $this->value($state, 'coverage_gate_status'));
        $promotionAllowed = $this->value($state, 'promotion_allowed');

        $this->assertReadableRequiresCoveragePass($state, $context);

        if (in_array($terminalStatus, ['FAILED', 'HELD'], true) && $publishabilityState !== 'NOT_READABLE') {
            throw new \LogicException($context.': FAILED/HELD requires NOT_READABLE.');
        }

        if ($promotionAllowed === true && ($terminalStatus !== 'SUCCESS' || $publishabilityState !== 'READABLE' || $coverageGateState !== 'PASS')) {
            throw new \LogicException($context.': promotion_allowed requires SUCCESS + READABLE + coverage PASS.');
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

        if ($this->value($run, 'coverage_gate_state', $this->value($run, 'coverage_gate_status')) !== 'PASS') {
            throw new \LogicException($context.' requires run coverage PASS.');
        }
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
