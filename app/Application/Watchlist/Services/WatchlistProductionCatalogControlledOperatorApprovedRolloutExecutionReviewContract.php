<?php

namespace App\Application\Watchlist\Services;

final class WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract
{
    public const DEFAULT_ENABLED = false;
    public const DEFAULT_CONTROLLED_ROLLOUT_ENABLED = false;
    public const DEFAULT_KILL_SWITCH = false;
    public const REQUIRED_OPERATOR_OPTION = '--operator-approved';
    public const REQUIRED_APPROVAL_REFERENCE_OPTION = '--approval-reference';
    public const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    public const CLASSIFICATION = 'CONTROLLED_WIRING_EXECUTION_REVIEW_ONLY';

    /**
     * This contract is intentionally not consumed by PLAN/CONFIRM runtime services.
     * It is artifact-only, explicit-context-only, default-off, kill-switch protected,
     * and never persists controlled wiring state to live runtime/config/database.
     */
    public static function defaults(): array
    {
        return [
            'enabled_by_default' => self::DEFAULT_ENABLED,
            'controlled_rollout_enabled_by_default' => self::DEFAULT_CONTROLLED_ROLLOUT_ENABLED,
            'kill_switch_on_by_default' => self::DEFAULT_KILL_SWITCH,
            'requires_operator_approval' => true,
            'requires_approval_reference' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_only_candidate_code' => self::COMPARATOR_ONLY_CANDIDATE,
            'a01_can_be_promoted' => false,
            'a01_can_be_runtime_fallback' => false,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }
}
