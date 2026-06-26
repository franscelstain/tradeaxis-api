<?php

namespace App\Application\Watchlist\Services;

/**
 * C83 artifact-only contract for controlled limited activation authorization review.
 * This contract is intentionally not consumed by PLAN/CONFIRM runtime services.
 * It authorizes a later execution review only; it never executes activation, production deployment, live rollout, runtime bridge activation, or PLAN/CONFIRM mutation.
 */
class WatchlistProductionCatalogControlledLimitedActivationAuthorizationReviewContract
{
    public const CONTRACT_CODE = 'C83_CONTROLLED_LIMITED_ACTIVATION_AUTHORIZATION_REVIEW_CONTRACT';
    public const DEFAULT_ENABLED = false;
    public const DEFAULT_CONTROLLED_RUNTIME_OPT_IN_PILOT_ENABLED = false;
    public const DEFAULT_CONTROLLED_SHADOW_ROLLOUT_ENABLED = false;
    public const DEFAULT_CONTROLLED_PARALLEL_RUN_ENABLED = false;
    public const DEFAULT_CONTROLLED_ROLLOUT_ENABLED = false;
    public const DEFAULT_KILL_SWITCH = false;
    public const REQUIRED_OPERATOR_OPTION = '--operator-approved';
    public const REQUIRED_APPROVAL_REFERENCE_OPTION = '--approval-reference';
    public const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    public const ACTIVATION_AUTHORIZATION_REVIEW_ONLY = true;
    public const ACTIVATION_AUTHORIZED_ARTIFACT_ONLY = true;
    public const ARTIFACT_ONLY = true;
    public const ACTIVATION_EXECUTED = false;
    public const PLAN_CONFIRM_MUTATION_ALLOWED = false;
    public const PRODUCTION_DEPLOYMENT_ALLOWED = false;
    public const RUNTIME_BRIDGE_ACTIVATION_ALLOWED = false;
    public const CONTROLLED_ROLLOUT_ACTIVATION_ALLOWED = false;
    public const ACTIVATION_EXECUTION_REVIEW_NEXT_ONLY = true;
    public const NEXT_REVIEW = 'C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW';

    public static function summary(): array
    {
        return [
            'contract_code' => self::CONTRACT_CODE,
            'default_enabled' => self::DEFAULT_ENABLED,
            'controlled_runtime_opt_in_pilot_default_enabled' => self::DEFAULT_CONTROLLED_RUNTIME_OPT_IN_PILOT_ENABLED,
            'controlled_shadow_rollout_default_enabled' => self::DEFAULT_CONTROLLED_SHADOW_ROLLOUT_ENABLED,
            'controlled_parallel_run_default_enabled' => self::DEFAULT_CONTROLLED_PARALLEL_RUN_ENABLED,
            'controlled_rollout_default_enabled' => self::DEFAULT_CONTROLLED_ROLLOUT_ENABLED,
            'kill_switch_default_on' => self::DEFAULT_KILL_SWITCH,
            'required_operator_option' => self::REQUIRED_OPERATOR_OPTION,
            'required_approval_reference_option' => self::REQUIRED_APPROVAL_REFERENCE_OPTION,
            'primary_candidate' => self::PRIMARY_CANDIDATE,
            'backup_candidate' => self::BACKUP_CANDIDATE,
            'comparator_only_candidate' => self::COMPARATOR_ONLY_CANDIDATE,
            'activation_authorization_review_only' => self::ACTIVATION_AUTHORIZATION_REVIEW_ONLY,
            'activation_authorized_artifact_only' => self::ACTIVATION_AUTHORIZED_ARTIFACT_ONLY,
            'artifact_only' => self::ARTIFACT_ONLY,
            'activation_executed' => self::ACTIVATION_EXECUTED,
            'plan_confirm_mutation_allowed' => self::PLAN_CONFIRM_MUTATION_ALLOWED,
            'production_deployment_allowed' => self::PRODUCTION_DEPLOYMENT_ALLOWED,
            'runtime_bridge_activation_allowed' => self::RUNTIME_BRIDGE_ACTIVATION_ALLOWED,
            'controlled_rollout_activation_allowed' => self::CONTROLLED_ROLLOUT_ACTIVATION_ALLOWED,
            'activation_execution_review_next_only' => self::ACTIVATION_EXECUTION_REVIEW_NEXT_ONLY,
            'next_review' => self::NEXT_REVIEW,
        ];
    }
}
