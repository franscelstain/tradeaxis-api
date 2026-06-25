<?php

namespace App\Application\Watchlist\Services;

final class WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContext
{
    public const REQUIRED_OPERATOR_OPTION = '--operator-approved';
    public const REQUIRED_APPROVAL_REFERENCE_OPTION = '--approval-reference';

    private bool $operatorApproved;
    private string $approvalReference;

    public function __construct(bool $operatorApproved, string $approvalReference)
    {
        $this->operatorApproved = $operatorApproved;
        $this->approvalReference = trim($approvalReference);
    }

    public function isValid(): bool
    {
        return $this->operatorApproved && $this->approvalReference !== '';
    }

    public function toArtifactSummary(): array
    {
        return [
            'controlled_wiring_context_created' => true,
            'controlled_wiring_context_is_explicit_only' => true,
            'controlled_wiring_context_requires_operator_approval' => true,
            'controlled_wiring_context_requires_approval_reference' => true,
            'controlled_wiring_context_requires_feature_flag_on' => true,
            'controlled_wiring_context_requires_kill_switch_off' => true,
            'controlled_wiring_context_is_artifact_only' => true,
            'controlled_wiring_context_is_not_persisted_to_config' => true,
            'controlled_wiring_context_is_not_persisted_to_db' => true,
            'controlled_wiring_context_is_not_persisted_to_live_runtime' => true,
            'controlled_wiring_context_does_not_mutate_plan_confirm' => true,
            'controlled_wiring_context_does_not_change_default_runtime' => true,
            'controlled_wiring_context_carries_primary_candidate' => WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract::PRIMARY_CANDIDATE,
            'controlled_wiring_context_carries_backup_candidate' => WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract::BACKUP_CANDIDATE,
            'controlled_wiring_context_rejects_a01_as_runtime_candidate' => true,
            'controlled_wiring_context_fallback_preserves_default_plan_confirm' => true,
            'context_valid' => $this->isValid(),
            'operator_approval_reference' => $this->approvalReference,
        ];
    }
}
