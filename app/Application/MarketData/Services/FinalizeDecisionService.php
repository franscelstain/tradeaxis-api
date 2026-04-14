<?php

namespace App\Application\MarketData\Services;

class FinalizeDecisionService
{
    public function evaluate($cutoffSatisfied, $runSealed, $candidateSealState, array $coverageSummary, $fallbackTradeDate)
    {
        $coverageGateStatus = strtoupper((string) ($coverageSummary['coverage_gate_status'] ?? 'BLOCKED'));
        $coverageThresholdValue = $coverageSummary['coverage_threshold_value'] ?? null;
        $coverageThresholdMode = $coverageSummary['coverage_threshold_mode'] ?? null;
        $coverageRatio = $coverageSummary['coverage_ratio'] ?? null;

        $qualityGateState = $this->mapCoverageGateStatusToQualityGateState($coverageGateStatus);
        $state = [
            'coverage_gate_status' => $coverageGateStatus,
            'quality_gate_state' => $qualityGateState,
            'terminal_status' => $fallbackTradeDate ? 'HELD' : 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => $fallbackTradeDate,
            'reason_code' => null,
            'message' => 'Run finalized without readable current publication for requested date.',
            'promotion_allowed' => false,
            'coverage_summary' => [
                'coverage_gate_status' => $coverageGateStatus,
                'coverage_ratio' => $coverageRatio !== null ? (float) $coverageRatio : null,
                'coverage_threshold_value' => $coverageThresholdValue !== null ? (float) $coverageThresholdValue : null,
                'coverage_threshold_mode' => $coverageThresholdMode,
            ],
        ];

        if (! $cutoffSatisfied) {
            $state['quality_gate_state'] = 'BLOCKED';
            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['reason_code'] = 'RUN_FINALIZE_BEFORE_CUTOFF';
            $state['message'] = 'Finalize blocked because cutoff policy is not yet satisfied.';
            return $state;
        }

        if ($coverageGateStatus === 'FAIL') {
            $state['quality_gate_state'] = 'FAIL';
            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['reason_code'] = 'RUN_COVERAGE_LOW';
            $state['message'] = $fallbackTradeDate
                ? 'Finalize held because coverage gate failed and fallback readable publication remains available.'
                : 'Finalize failed because coverage gate failed and no readable fallback publication exists.';
            return $state;
        }

        if ($coverageGateStatus === 'BLOCKED') {
            $state['quality_gate_state'] = 'BLOCKED';
            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['reason_code'] = 'RUN_COVERAGE_NOT_EVALUABLE';
            $state['message'] = $fallbackTradeDate
                ? 'Finalize held because coverage gate could not be evaluated safely and fallback readable publication remains available.'
                : 'Finalize failed because coverage gate could not be evaluated safely and no readable fallback publication exists.';

            return $state;
        }

        if (! $runSealed || $candidateSealState !== 'SEALED') {
            $state['quality_gate_state'] = 'BLOCKED';
            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['reason_code'] = 'RUN_SEAL_PRECONDITION_FAILED';
            $state['message'] = 'Finalize blocked because sealed publication state is missing.';
            return $state;
        }

        if ($coverageGateStatus === 'PASS') {
            $state['quality_gate_state'] = 'PASS';
            $state['terminal_status'] = 'SUCCESS';
            $state['publishability_state'] = 'READABLE';
            $state['trade_date_effective'] = null;
            $state['reason_code'] = null;
            $state['message'] = 'Finalize may promote candidate publication to current once pointer sync succeeds.';
            $state['promotion_allowed'] = true;
            return $state;
        }

        return $state;
    }

    private function mapCoverageGateStatusToQualityGateState($coverageGateStatus)
    {
        if ($coverageGateStatus === 'PASS') {
            return 'PASS';
        }

        if ($coverageGateStatus === 'FAIL') {
            return 'FAIL';
        }

        return 'BLOCKED';
    }
}
