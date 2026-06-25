<?php

namespace App\Application\Watchlist\Services;

/**
 * Explicit-only C76 shadow rollout preparation context.
 * Not persisted to config, DB, or live runtime; artifact-only proof.
 */
class WatchlistProductionCatalogControlledShadowRolloutPreparationContext
{
    public const CONTEXT_CODE = 'C76_CONTROLLED_SHADOW_ROLLOUT_PREPARATION_CONTEXT';
    public const REQUIRED_OPERATOR_OPTION = '--operator-approved';
    public const REQUIRED_APPROVAL_REFERENCE_OPTION = '--approval-reference';
    public const EXPLICIT_CONTEXT_ONLY = true;
    public const ARTIFACT_ONLY = true;
    public const PERSISTED_TO_CONFIG = false;
    public const PERSISTED_TO_DB = false;
    public const PERSISTED_TO_LIVE_RUNTIME = false;
    public const MUTATES_PLAN_CONFIRM = false;
    public const CHANGES_DEFAULT_RUNTIME = false;
    public const REJECTS_A01_AS_RUNTIME_CANDIDATE = true;
    public const FALLBACK_PRESERVES_DEFAULT_PLAN_CONFIRM = true;

    public static function build(string $approvalReference): array
    {
        return [
            'context_code' => self::CONTEXT_CODE,
            'approval_reference' => $approvalReference,
            'explicit_context_only' => self::EXPLICIT_CONTEXT_ONLY,
            'artifact_only' => self::ARTIFACT_ONLY,
            'persisted_to_config' => self::PERSISTED_TO_CONFIG,
            'persisted_to_db' => self::PERSISTED_TO_DB,
            'persisted_to_live_runtime' => self::PERSISTED_TO_LIVE_RUNTIME,
            'mutates_plan_confirm' => self::MUTATES_PLAN_CONFIRM,
            'changes_default_runtime' => self::CHANGES_DEFAULT_RUNTIME,
            'rejects_a01_as_runtime_candidate' => self::REJECTS_A01_AS_RUNTIME_CANDIDATE,
            'fallback_preserves_default_plan_confirm' => self::FALLBACK_PRESERVES_DEFAULT_PLAN_CONFIRM,
        ];
    }
}
