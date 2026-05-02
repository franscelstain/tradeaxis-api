<?php

namespace App\Application\MarketData\Services;

class PublicationFinalizeOutcomeService
{
    public function resolve(array $preDecision, array $context)
    {
        $fallbackTradeDate = $context['fallback_trade_date'] ?? $preDecision['trade_date_effective'] ?? null;
        $requestedDate = $context['requested_date'] ?? null;

        $candidatePublicationId = $context['candidate_publication_id'] ?? null;
        $candidatePublicationVersion = $context['candidate_publication_version'] ?? null;

        $resolvedCurrentPublicationId = $context['resolved_current_publication_id'] ?? null;
        $resolvedCurrentPublicationVersion = $context['resolved_current_publication_version'] ?? null;

        $correctionId = $context['correction_id'] ?? null;
        $priorPublicationId = $context['prior_publication_id'] ?? null;
        $priorPublicationVersion = $context['prior_publication_version'] ?? null;

        $unchangedCorrection = (bool) ($context['unchanged_correction'] ?? false);
        $promotionError = $context['promotion_error'] ?? null;

        $state = [
            'coverage_gate_status' => $preDecision['coverage_gate_status'] ?? null,
            'coverage_gate_state' => $preDecision['coverage_gate_state'] ?? ($preDecision['coverage_gate_status'] ?? null),
            'coverage_summary' => $preDecision['coverage_summary'] ?? [],
            'quality_gate_state' => $preDecision['quality_gate_state'],
            'terminal_status' => $preDecision['terminal_status'],
            'publishability_state' => $preDecision['publishability_state'],
            'trade_date_effective' => $preDecision['trade_date_effective'],
            'reason_code' => $preDecision['reason_code'],
            'message' => $preDecision['message'],
            'current_publication_id' => null,
            'current_publication_version' => null,
            'correction_outcome' => null,
            'correction_outcome_note' => null,
        ];

        if (! ($preDecision['promotion_allowed'] ?? false)) {
            $state['trade_date_effective'] = $fallbackTradeDate;
            if ($correctionId && ($preDecision['reason_code'] ?? null) === 'RUN_REPAIR_CANDIDATE_PARTIAL') {
                $state['correction_outcome'] = 'REPAIR_CANDIDATE';
                $state['correction_outcome_note'] = 'Correction request finalized as non-current repair candidate without current publication replacement.';
            }
            return $this->enforceStateMatrix($state);
        }

        if ($promotionError) {
            $state['terminal_status'] = 'HELD';
            $state['publishability_state'] = 'NOT_READABLE';
            $state['trade_date_effective'] = $fallbackTradeDate;
            $state['reason_code'] = 'RUN_LOCK_CONFLICT';
            $state['message'] = $promotionError;
            return $this->enforceStateMatrix($state);
        }

        if ($unchangedCorrection && $correctionId) {
            if ($this->hasPublicationIdentity($resolvedCurrentPublicationId, $resolvedCurrentPublicationVersion)
                && (! $this->hasPublicationIdentity($priorPublicationId, $priorPublicationVersion)
                    || $this->samePublicationIdentity(
                        $resolvedCurrentPublicationId,
                        $resolvedCurrentPublicationVersion,
                        $priorPublicationId,
                        $priorPublicationVersion
                    ))
            ) {
                $state['terminal_status'] = 'SUCCESS';
                $state['publishability_state'] = 'READABLE';
                $state['trade_date_effective'] = $requestedDate;
                $state['reason_code'] = null;
                $state['message'] = 'Correction rerun produced unchanged content; current publication preserved without version switch.';
                $state['current_publication_id'] = $resolvedCurrentPublicationId;
                $state['current_publication_version'] = $resolvedCurrentPublicationVersion;
                $state['correction_outcome'] = 'CANCELLED';
                $state['correction_outcome_note'] = 'Correction rerun produced unchanged content; current publication preserved without version switch.';
                return $this->enforceStateMatrix($state);
            }

            $state['terminal_status'] = 'HELD';
            $state['publishability_state'] = 'NOT_READABLE';
            $state['trade_date_effective'] = $fallbackTradeDate;
            $state['reason_code'] = 'RUN_LOCK_CONFLICT';
            $state['message'] = 'Correction unchanged outcome rejected because current readable pointer identity was not proven.';
            $state['correction_outcome'] = 'FAILED';
            $state['correction_outcome_note'] = 'Correction unchanged outcome rejected because current readable pointer identity was not proven.';
            return $this->enforceStateMatrix($state);
        }

        $resolvedMatchesCandidate =
            $this->hasPublicationIdentity($candidatePublicationId, $candidatePublicationVersion)
            && $this->hasPublicationIdentity($resolvedCurrentPublicationId, $resolvedCurrentPublicationVersion)
            && $this->samePublicationIdentity(
                $resolvedCurrentPublicationId,
                $resolvedCurrentPublicationVersion,
                $candidatePublicationId,
                $candidatePublicationVersion
            );

        if ($resolvedMatchesCandidate) {
            $state['terminal_status'] = 'SUCCESS';
            $state['publishability_state'] = 'READABLE';
            $state['trade_date_effective'] = $requestedDate;
            $state['reason_code'] = null;
            $state['message'] = $correctionId
                ? 'Historical correction published safely via new sealed current publication.'
                : 'Run finalized with sealed current publication for requested date.';
            $state['current_publication_id'] = $resolvedCurrentPublicationId;
            $state['current_publication_version'] = $resolvedCurrentPublicationVersion;
            $state['correction_outcome'] = $correctionId ? 'PUBLISHED' : null;
            $state['correction_outcome_note'] = $correctionId
                ? 'Historical correction published safely via new sealed current publication.'
                : null;
            return $this->enforceStateMatrix($state);
        }

        $state['terminal_status'] = 'HELD';
        $state['publishability_state'] = 'NOT_READABLE';
        $state['trade_date_effective'] = $fallbackTradeDate;
        $state['reason_code'] = 'RUN_LOCK_CONFLICT';
        $state['message'] = 'Current publication pointer resolution mismatch after finalize.';

        return $this->enforceStateMatrix($state);
    }

    private function hasPublicationIdentity($publicationId, $publicationVersion): bool
    {
        return $publicationId !== null
            && $publicationId !== ''
            && $publicationVersion !== null
            && $publicationVersion !== '';
    }

    private function samePublicationIdentity($leftId, $leftVersion, $rightId, $rightVersion): bool
    {
        return (string) $leftId === (string) $rightId
            && (string) $leftVersion === (string) $rightVersion;
    }

    private function enforceStateMatrix(array $state): array
    {
        $coverageGateStatus = strtoupper((string) ($state['coverage_gate_status'] ?? ($state['coverage_gate_state'] ?? 'NOT_EVALUABLE')));
        $terminalStatus = strtoupper((string) ($state['terminal_status'] ?? ''));
        $publishabilityState = strtoupper((string) ($state['publishability_state'] ?? ''));

        if ($publishabilityState === 'READABLE' && $terminalStatus !== 'SUCCESS') {
            throw new \LogicException('Invalid publication finalize outcome matrix: READABLE requires terminal_status SUCCESS.');
        }

        if ($publishabilityState === 'READABLE' && $coverageGateStatus !== 'PASS') {
            throw new \LogicException('Invalid publication finalize outcome matrix: READABLE requires coverage_gate_status PASS.');
        }

        if ($publishabilityState === 'READABLE'
            && ! $this->hasPublicationIdentity($state['current_publication_id'] ?? null, $state['current_publication_version'] ?? null)
        ) {
            throw new \LogicException('Invalid publication finalize outcome matrix: READABLE requires resolved current publication identity.');
        }

        if (in_array($terminalStatus, ['FAILED', 'HELD'], true) && $publishabilityState !== 'NOT_READABLE') {
            throw new \LogicException('Invalid publication finalize outcome matrix: FAILED/HELD requires NOT_READABLE.');
        }

        if ($terminalStatus === 'SUCCESS' && ! in_array($publishabilityState, ['READABLE', 'NOT_READABLE'], true)) {
            throw new \LogicException('Invalid publication finalize outcome matrix: SUCCESS requires explicit publishability state.');
        }

        if ($terminalStatus === 'FAILED' || $terminalStatus === 'HELD') {
            $state['publishability_state'] = 'NOT_READABLE';
        }

        (new MarketDataInvariantGuard())->assertNoBypassState($state, 'PublicationFinalizeOutcomeService');

        return $state;
    }
}
