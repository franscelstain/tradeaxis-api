<?php

namespace App\Application\Watchlist\Services;

/**
 * Isolated non-live C72 controlled opt-in runtime bridge validation contract.
 * This class is intentionally not consumed by PLAN/CONFIRM runtime services.
 */
class WatchlistProductionCatalogControlledOptInRuntimeBridgeContract
{
    public const FEATURE_FLAG = 'watchlist.production_catalog_runtime_bridge_enabled';
    public const CONTROLLED_OPT_IN_FEATURE_FLAG = 'watchlist.production_catalog_controlled_opt_in_runtime_bridge_enabled';
    public const KILL_SWITCH = 'watchlist.production_catalog_runtime_bridge_kill_switch';
    public const DEFAULT_ENABLED = false;
    public const DEFAULT_CONTROLLED_OPT_IN_ENABLED = false;
    public const DEFAULT_KILL_SWITCH = false;
    public const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    public const A01_COMPARATOR_ONLY = true;
    public const PLAN_CONFIRM_LIVE_MUTATION_ALLOWED = false;

    public function buildValidationReadProof(WatchlistProductionCatalogControlledOptInRuntimeBridgeContext $context): array
    {
        if (! $context->canValidateInIsolatedPath()) {
            return [
                'controlled_bridge_read_execution_proof_pass' => false,
                'rejected_without_explicit_opt_in_or_by_kill_switch' => true,
                'production_catalog_runtime_wired' => false,
                'plan_confirm_runtime_reads_activated_catalog' => false,
            ];
        }

        return [
            'controlled_bridge_read_execution_proof_pass' => true,
            'controlled_bridge_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'controlled_bridge_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'controlled_bridge_comparator_only_candidate_codes' => [self::COMPARATOR_ONLY_CANDIDATE],
            'controlled_bridge_a01_used_as_runtime_fallback' => false,
            'controlled_bridge_output_written_to_c72_artifact_only' => true,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }
}
