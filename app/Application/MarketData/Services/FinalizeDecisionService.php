<?php

namespace App\Application\MarketData\Services;

class FinalizeDecisionService
{
    public function evaluate($cutoffSatisfied, $runSealed, $candidateSealState, array $coverageSummary, $fallbackTradeDate, array $promoteContext = [])
    {
        $coverageGateStatus = strtoupper((string) ($coverageSummary['coverage_gate_status'] ?? 'NOT_EVALUABLE'));
        $coverageThresholdValue = $coverageSummary['coverage_threshold_value'] ?? null;
        $coverageThresholdMode = $coverageSummary['coverage_threshold_mode'] ?? null;
        $coverageRatio = $coverageSummary['coverage_ratio'] ?? null;
        $expectedUniverseCount = isset($coverageSummary['expected_universe_count']) ? (int) $coverageSummary['expected_universe_count'] : null;
        $availableEodCount = isset($coverageSummary['available_eod_count']) ? (int) $coverageSummary['available_eod_count'] : null;
        $edgeCaseReasonCode = isset($coverageSummary['edge_case_reason_code']) ? (string) $coverageSummary['edge_case_reason_code'] : null;

        $promoteMode = (string) ($promoteContext['promote_mode'] ?? 'full_publish');
        $publishTarget = (string) ($promoteContext['publish_target'] ?? 'current_replace');
        $sourceMode = (string) ($promoteContext['source_mode'] ?? '');
        $sourceFinalReasonCode = isset($promoteContext['source_final_reason_code'])
            ? (string) $promoteContext['source_final_reason_code']
            : null;

        $isManualFileSource = in_array($sourceMode, ['manual_file', 'manual_entry'], true);
        $manualFilePolicy = $isManualFileSource ? 'COVERAGE_GATE_STRICT_HYBRID' : null;

        $isCorrection = ! empty($promoteContext['correction_id'])
            || ! empty($promoteContext['is_correction'])
            || $promoteMode === 'correction';

        $isUnchangedCorrection = $isCorrection && (
            ! empty($promoteContext['unchanged_artifacts'])
            || ! empty($promoteContext['unchanged_dataset'])
            || ! empty($promoteContext['is_unchanged'])
            || ! empty($promoteContext['correction_unchanged'])
            || (($promoteContext['correction_outcome'] ?? null) === 'UNCHANGED')
        );

        $baseState = [
            'coverage_gate_status' => $coverageGateStatus,
            'quality_gate_state' => $this->mapCoverageGateStatusToQualityGateState($coverageGateStatus),
            'terminal_status' => $fallbackTradeDate ? 'HELD' : 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => $fallbackTradeDate,
            'reason_code' => null,
            'message' => 'Run finalized without readable current publication for requested date.',
            'promotion_allowed' => false,
            'source_mode' => $sourceMode !== '' ? $sourceMode : null,
            'manual_file_policy' => $manualFilePolicy,
            'coverage_override_allowed' => false,
            'coverage_summary' => [
                'coverage_gate_status' => $coverageGateStatus,
                'coverage_ratio' => $coverageRatio !== null ? (float) $coverageRatio : null,
                'coverage_threshold_value' => $coverageThresholdValue !== null ? (float) $coverageThresholdValue : null,
                'coverage_threshold_mode' => $coverageThresholdMode,
                'expected_universe_count' => $expectedUniverseCount,
                'available_eod_count' => $availableEodCount,
                'edge_case_reason_code' => $edgeCaseReasonCode,
            ],
        ];

        /*
        * Correction no-op is a completed safe lifecycle outcome.
        * It must not request promotion, reseal, or create a new publication.
        * The existing current readable publication remains authoritative.
        */
        if ($isUnchangedCorrection && $coverageGateStatus === 'PASS') {
            $state = $baseState;
            $state['quality_gate_state'] = 'PASS';
            $state['terminal_status'] = 'SUCCESS';
            $state['publishability_state'] = 'READABLE';
            $state['trade_date_effective'] = null;
            $state['reason_code'] = null;
            $state['message'] = 'Correction cancelled because submitted artifacts are unchanged; current readable publication remains authoritative.';
            $state['promotion_allowed'] = false;
            $state['correction_outcome'] = 'CORRECTION_CANCELLED';

            return $this->enforceStateMatrix($state);
        }

        if (! $cutoffSatisfied) {
            $state = $baseState;
            $state['quality_gate_state'] = 'BLOCKED';
            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['reason_code'] = $this->resolveOperationalBlockReasonCode(
                $sourceFinalReasonCode,
                'RUN_FINALIZE_BEFORE_CUTOFF'
            );
            $state['message'] = 'Finalize blocked because cutoff policy is not yet satisfied.';

            return $this->enforceStateMatrix($state);
        }

        if ($publishTarget !== 'current_replace') {
            $state = $baseState;

            if ($promoteMode === 'repair_candidate') {
                $state['quality_gate_state'] = $coverageGateStatus === 'PASS' ? 'PASS' : ($coverageGateStatus === 'FAIL' ? 'FAIL' : 'BLOCKED');
                $state['terminal_status'] = 'SUCCESS';
                $state['publishability_state'] = 'NOT_READABLE';
                $state['trade_date_effective'] = $fallbackTradeDate;
                $state['reason_code'] = 'RUN_REPAIR_CANDIDATE_PARTIAL';
                $state['message'] = 'Repair candidate finalized as non-current partial dataset; current readable publication remains authoritative.';

                return $this->enforceStateMatrix($state);
            }

            if (! $runSealed || $candidateSealState !== 'SEALED') {
                $state['quality_gate_state'] = 'BLOCKED';
                $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
                $state['reason_code'] = 'RUN_SEAL_PRECONDITION_FAILED';
                $state['message'] = 'Finalize blocked because sealed publication state is missing.';

                return $this->enforceStateMatrix($state);
            }

            $state['quality_gate_state'] = $coverageGateStatus === 'PASS' ? 'PASS' : ($coverageGateStatus === 'FAIL' ? 'FAIL' : 'BLOCKED');
            $state['terminal_status'] = 'HELD';
            $state['publishability_state'] = 'NOT_READABLE';
            $state['trade_date_effective'] = $fallbackTradeDate;
            $state['reason_code'] = 'RUN_NON_CURRENT_PROMOTION';
            $state['message'] = sprintf(
                'Promote mode %s sealed a non-current publication candidate; current readable publication remains authoritative.',
                $promoteMode
            );

            return $this->enforceStateMatrix($state);
        }

        if ($coverageGateStatus === 'FAIL') {
            $state = $baseState;
            $state['quality_gate_state'] = 'FAIL';
            $state['reason_code'] = $this->resolveCoverageFailReasonCode(
                $availableEodCount,
                $expectedUniverseCount,
                $edgeCaseReasonCode
            );

            if ($edgeCaseReasonCode === 'RUN_DATA_DELAYED') {
                $state['terminal_status'] = 'HELD';
                $state['message'] = 'Finalize held because coverage gate failed while delayed data is still inside the controlled delay window.';

                return $this->enforceStateMatrix($state);
            }

            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['message'] = $fallbackTradeDate
                ? 'Finalize held because coverage gate failed and fallback readable publication remains available.'
                : 'Finalize failed because coverage gate failed and no readable fallback publication exists.';

            return $this->enforceStateMatrix($state);
        }

        if ($coverageGateStatus === 'NOT_EVALUABLE' || $coverageGateStatus === 'BLOCKED') {
            $state = $baseState;
            $state['quality_gate_state'] = 'BLOCKED';
            $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
            $state['reason_code'] = $this->resolveNotEvaluableReasonCode($sourceFinalReasonCode);
            $state['message'] = $fallbackTradeDate
                ? 'Finalize held because coverage gate could not be evaluated safely and fallback readable publication remains available.'
                : 'Finalize failed because coverage gate could not be evaluated safely and no readable fallback publication exists.';

            return $this->enforceStateMatrix($state);
        }

        if ($coverageGateStatus === 'PASS') {
            if (! $runSealed || $candidateSealState !== 'SEALED') {
                $state = $baseState;
                $state['quality_gate_state'] = 'BLOCKED';
                $state['terminal_status'] = $fallbackTradeDate ? 'HELD' : 'FAILED';
                $state['reason_code'] = 'RUN_SEAL_PRECONDITION_FAILED';
                $state['message'] = 'Finalize blocked because sealed publication state is missing.';

                return $this->enforceStateMatrix($state);
            }

            $state = $baseState;
            $state['quality_gate_state'] = 'PASS';
            $state['terminal_status'] = 'SUCCESS';
            $state['publishability_state'] = 'READABLE';
            $state['trade_date_effective'] = null;
            $state['reason_code'] = null;
            $state['message'] = 'Finalize completed with readable sealed publication; candidate may be promoted after pointer invariant validation.';
            $state['promotion_allowed'] = true;

            if ($isCorrection) {
                $state['correction_outcome'] = 'CORRECTION_PUBLISHED';
            }

            return $this->enforceStateMatrix($state);
        }

        return $this->enforceStateMatrix($baseState);
    }

    private function enforceStateMatrix(array $state): array
    {
        $coverageGateStatus = strtoupper((string) ($state['coverage_gate_status'] ?? 'NOT_EVALUABLE'));
        $terminalStatus = strtoupper((string) ($state['terminal_status'] ?? ''));
        $publishabilityState = strtoupper((string) ($state['publishability_state'] ?? ''));

        if ($publishabilityState === 'READABLE' && $terminalStatus !== 'SUCCESS') {
            throw new \LogicException('Invalid publishability state matrix: READABLE requires terminal_status SUCCESS.');
        }

        if ($publishabilityState === 'READABLE' && $coverageGateStatus !== 'PASS') {
            throw new \LogicException('Invalid publishability state matrix: READABLE requires coverage_gate_status PASS.');
        }

        if (in_array($terminalStatus, ['FAILED', 'HELD'], true) && $publishabilityState !== 'NOT_READABLE') {
            throw new \LogicException('Invalid publishability state matrix: FAILED/HELD requires NOT_READABLE.');
        }

        if ($terminalStatus === 'SUCCESS' && ! in_array($publishabilityState, ['READABLE', 'NOT_READABLE'], true)) {
            throw new \LogicException('Invalid publishability state matrix: SUCCESS requires explicit publishability state.');
        }

        if ($terminalStatus === 'FAILED' || $terminalStatus === 'HELD') {
            $state['publishability_state'] = 'NOT_READABLE';
        }

        (new MarketDataInvariantGuard())->assertNoBypassState($state, 'FinalizeDecisionService');

        return $state;
    }

    private function resolveNotEvaluableReasonCode($sourceFinalReasonCode)
    {
        if (in_array($sourceFinalReasonCode, ['RUN_SOURCE_RATE_LIMIT', 'RUN_SOURCE_TIMEOUT'], true)) {
            return $sourceFinalReasonCode;
        }

        return 'RUN_COVERAGE_NOT_EVALUABLE';
    }

    private function resolveOperationalBlockReasonCode($sourceFinalReasonCode, string $fallbackReasonCode): string
    {
        if (in_array($sourceFinalReasonCode, ['RUN_SOURCE_RATE_LIMIT', 'RUN_SOURCE_TIMEOUT'], true)) {
            return $sourceFinalReasonCode;
        }

        return $fallbackReasonCode;
    }

    private function resolveCoverageFailReasonCode($availableEodCount, $expectedUniverseCount, $edgeCaseReasonCode)
    {
        if (in_array($edgeCaseReasonCode, ['RUN_DATA_DELAYED', 'RUN_PARTIAL_DATA', 'RUN_STALE_DATA'], true)) {
            return $edgeCaseReasonCode;
        }

        if ($expectedUniverseCount !== null && $expectedUniverseCount > 0 && $availableEodCount !== null && $availableEodCount > 0 && $availableEodCount < $expectedUniverseCount) {
            return 'RUN_PARTIAL_DATA';
        }

        return 'RUN_COVERAGE_LOW';
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
