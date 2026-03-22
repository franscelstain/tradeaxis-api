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
            'quality_gate_state' => $preDecision['quality_gate_state'],
            'terminal_status' => $preDecision['terminal_status'],
            'publishability_state' => $preDecision['publishability_state'],
            'trade_date_effective' => $preDecision['trade_date_effective'],
            'reason_code' => $preDecision['reason_code'],
            'message' => $preDecision['message'],
            'current_publication_id' => null,
            'current_publication_version' => null,
            'correction_outcome' => null,
        ];

        if (! ($preDecision['promotion_allowed'] ?? false)) {
            $state['trade_date_effective'] = $fallbackTradeDate;
            return $state;
        }

        if ($promotionError) {
            $state['terminal_status'] = 'HELD';
            $state['publishability_state'] = 'NOT_READABLE';
            $state['trade_date_effective'] = $fallbackTradeDate;
            $state['reason_code'] = 'RUN_LOCK_CONFLICT';
            $state['message'] = $promotionError;
            return $state;
        }

        if ($unchangedCorrection && $correctionId) {
            $state['terminal_status'] = 'SUCCESS';
            $state['publishability_state'] = 'READABLE';
            $state['trade_date_effective'] = $requestedDate;
            $state['reason_code'] = null;
            $state['message'] = 'Correction rerun produced unchanged content; current publication preserved without version switch.';
            $state['current_publication_id'] = $resolvedCurrentPublicationId ?: $priorPublicationId;
            $state['current_publication_version'] = $resolvedCurrentPublicationVersion ?: $priorPublicationVersion;
            $state['correction_outcome'] = 'CANCELLED';
            return $state;
        }

        if ((string) $resolvedCurrentPublicationId === (string) $candidatePublicationId) {
            $state['terminal_status'] = 'SUCCESS';
            $state['publishability_state'] = 'READABLE';
            $state['trade_date_effective'] = $requestedDate;
            $state['reason_code'] = null;
            $state['message'] = $correctionId
                ? 'Historical correction published safely via new sealed current publication.'
                : 'Run finalized with sealed current publication for requested date.';
            $state['current_publication_id'] = $resolvedCurrentPublicationId;
            $state['current_publication_version'] = $resolvedCurrentPublicationVersion ?: $candidatePublicationVersion;
            $state['correction_outcome'] = $correctionId ? 'PUBLISHED' : null;
            return $state;
        }

        $state['terminal_status'] = 'HELD';
        $state['publishability_state'] = 'NOT_READABLE';
        $state['trade_date_effective'] = $fallbackTradeDate;
        $state['reason_code'] = 'RUN_LOCK_CONFLICT';
        $state['message'] = 'Current publication pointer resolution mismatch after finalize.';

        return $state;
    }
}
