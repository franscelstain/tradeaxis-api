<?php

namespace App\Application\Watchlist\Services;

/**
 * Isolated C74 rollout-gate contract.
 * This contract is intentionally not consumed by PLAN/CONFIRM runtime services.
 * It is readiness-only, default-off, kill-switch protected, and non-mutating.
 */
class WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract
{
    public const DEFAULT_ENABLED = false;
    public const DEFAULT_CONTROLLED_ROLLOUT_ENABLED = false;
    public const DEFAULT_KILL_SWITCH = false;
    public const REQUIRED_OPERATOR_OPTION = '--operator-reviewed';

    public const PRIMARY_CANDIDATE_CODE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE_CODE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE_CODE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    public const C75_RECOMMENDATION = 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW';
}
