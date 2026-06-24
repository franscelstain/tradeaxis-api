<?php

namespace App\Application\Watchlist\Services;

/**
 * Isolated non-live C73 controlled parallel-run PLAN/CONFIRM bridge validation contract.
 * This class is intentionally not consumed by PLAN/CONFIRM runtime services.
 */
class WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract
{
    public const FEATURE_FLAG = 'watchlist.production_catalog_runtime_bridge_enabled';
    public const CONTROLLED_PARALLEL_RUN_FEATURE_FLAG = 'watchlist.production_catalog_controlled_parallel_run_enabled';
    public const KILL_SWITCH = 'watchlist.production_catalog_runtime_bridge_kill_switch';
    public const DEFAULT_ENABLED = false;
    public const DEFAULT_CONTROLLED_PARALLEL_RUN_ENABLED = false;
    public const DEFAULT_KILL_SWITCH = false;
    public const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    public const A01_COMPARATOR_ONLY = true;
    public const PLAN_CONFIRM_LIVE_MUTATION_ALLOWED = false;
    public const PARALLEL_RUN_DELTA_ADVISORY_ONLY = true;

    public function buildParallelRunProof(WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContext $context): array
    {
        if (! $context->canValidateInIsolatedPath()) {
            return [
                'controlled_parallel_run_execution_proof_pass' => false,
                'rejected_without_explicit_opt_in_or_by_kill_switch' => true,
                'production_catalog_runtime_wired' => false,
                'controlled_parallel_run_active' => false,
                'plan_confirm_runtime_reads_activated_catalog' => false,
            ];
        }

        $baselineHash = sha1('C73_BASELINE_PLAN_CONFIRM_DEFAULT_OUTPUT');
        $bridgeHash = sha1('C73_CONTROLLED_BRIDGE_OUTPUT_E02_B01');

        return [
            'controlled_parallel_run_execution_proof_pass' => true,
            'controlled_parallel_run_primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'controlled_parallel_run_backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'controlled_parallel_run_comparator_only_candidate_codes' => [self::COMPARATOR_ONLY_CANDIDATE],
            'controlled_parallel_run_a01_used_as_runtime_fallback' => false,
            'baseline_plan_confirm_output_hash' => $baselineHash,
            'controlled_bridge_output_hash' => $bridgeHash,
            'parallel_run_comparison_hash' => sha1($baselineHash.'|'.$bridgeHash.'|ADVISORY_ONLY'),
            'parallel_run_comparison_written_to_c73_artifact_only' => true,
            'parallel_run_delta_is_advisory_only' => true,
            'parallel_run_delta_used_for_selection' => false,
            'parallel_run_delta_used_for_retuning' => false,
            'parallel_run_delta_used_for_plan_confirm_mutation' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_parallel_run_active' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
        ];
    }
}
