<?php

namespace App\Application\Watchlist\Services;

/**
 * Non-live C74 context holder. Future runtime rollout must pass an explicit
 * operator-approved context and must never use this artifact as a default
 * PLAN/CONFIRM read path.
 */
class WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContext
{
    public const REQUIRED_OPERATOR_OPTION = '--operator-reviewed';
    public const RUNTIME_CONSUMER_ALLOWED = false;
    public const PLAN_CONFIRM_DEFAULT_READ_ALLOWED = false;
    public const PRODUCTION_DEPLOYMENT_EXECUTION_ALLOWED = false;

    public static function safeDefaults(): array
    {
        return [
            'operator_review_required' => true,
            'operator_approval_executed_in_c74' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_rollout_active' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }
}
