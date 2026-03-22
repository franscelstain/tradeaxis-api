<?php

namespace App\Application\MarketData\Services;

class FinalizeDecisionService
{
    public function evaluate($cutoffSatisfied, $runSealed, $candidateSealState, $coverageRatio, $coverageMin, $fallbackTradeDate)
    {
        $qualityGateState = ($coverageRatio !== null && $coverageRatio >= $coverageMin) ? 'PASS' : 'FAIL';
        $state = [
            'quality_gate_state' => $qualityGateState,
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => $fallbackTradeDate,
            'reason_code' => null,
            'message' => 'Run finalized without readable current publication for requested date.',
            'promotion_allowed' => false,
        ];

        if (! $cutoffSatisfied) {
            $state['reason_code'] = 'RUN_FINALIZE_BEFORE_CUTOFF';
            $state['message'] = 'Finalize blocked because cutoff policy is not yet satisfied.';
            return $state;
        }

        if (! $runSealed || $candidateSealState !== 'SEALED') {
            $state['reason_code'] = 'RUN_SEAL_PRECONDITION_FAILED';
            $state['message'] = 'Finalize blocked because sealed publication state is missing.';
            return $state;
        }

        if ($qualityGateState !== 'PASS') {
            $state['reason_code'] = 'RUN_COVERAGE_LOW';
            $state['message'] = 'Finalize held because coverage ratio is below minimum threshold.';
            return $state;
        }

        $state['promotion_allowed'] = true;
        $state['reason_code'] = null;
        $state['message'] = 'Finalize may promote candidate publication to current once pointer sync succeeds.';

        return $state;
    }
}
