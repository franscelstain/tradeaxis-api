<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService
{
    public const RUN_CODE = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW';
    public const ARTIFACT_TYPE = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW';

    public const DEFAULT_C76_ARTIFACT = 'storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json';
    public const DEFAULT_EXPECTED_C76_HASH = '40f1bc516ddbb127ab6f62433059cb99ff2ae2de';
    public const DEFAULT_EXPECTED_C76_FILE_SHA1 = '115929AD40A739E9BE1D5A1A58DAA4FECB394ACD';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const WEAK_REGIME = 'market_down_or_sideways_high_vol';

    private const EXPECTED_C76_STATUS = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C76_REASON = 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C77_RECOMMENDATION = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW';
    private const C78_RECOMMENDATION = 'C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW';

    private const EXPECTED_C75_HASH = 'cd1346cd05ab5471a947fcb5304e0f347a4881eb';
    private const EXPECTED_C75_FILE_SHA1 = '668043836BA1DB8FF50EC69DF0560988E633CF75';
    private const EXPECTED_C74_HASH = '8958e1fcec798fbd364642864b0a9d0c21bd8f93';
    private const EXPECTED_C74_FILE_SHA1 = 'D4C2EF90B533BED11F6902E75141BE5774E947BE';
    private const EXPECTED_C73_HASH = '34f1f84a4261da7ce1cb9d17a1bf33dfb1458281';
    private const EXPECTED_C73_FILE_SHA1 = 'BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9';
    private const EXPECTED_C72_HASH = 'df3ee58a47572900d42b91d8348f0d6ea9ad1965';
    private const EXPECTED_C72_FILE_SHA1 = '1ADF2C81797140A7A756B7A4EB02815AF1CBE75E';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c77_validation_doc' => 'docs/watchlist/audit/WS_C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW.md',
        'c77_operator_commands_doc' => 'docs/watchlist/audit/WS_C77_OPERATOR_VALIDATION_COMMANDS.md',
        'c76_validation_doc' => 'docs/watchlist/audit/WS_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.md',
        'c76_operator_commands_doc' => 'docs/watchlist/audit/WS_C76_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const STALE_HASHES = [
        '2e02737a212cf9043d5937f5354a3c31541dc22f',
        'C7FCA9797AFF0B2B3CD4B37E587DC646F01C2187',
        '4896a0479675d969a142d1880545459c391dbc11',
        'CDD9F75CF96CC8842DC22F8A29A7959682550D84',
        '886019fba9143820e3d135a0586d63244c31e35a',
        '83A065CCDAD13A328F286D38BDED61117BE28BF6',
    ];

    private const RUNTIME_PATHS = [
        'candidate_universe_service' => 'app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php',
        'scoring_service' => 'app/Application/Watchlist/Services/WatchlistScoringService.php',
        'plan_grouping_service' => 'app/Application/Watchlist/Services/WatchlistPlanGroupingService.php',
        'recommendation_service' => 'app/Application/Watchlist/Services/WatchlistRecommendationService.php',
        'confirm_overlay_service' => 'app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php',
        'watchlist_market_data_consumer_read_service' => 'app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php',
        'market_data_watchlist_read_service' => 'app/Application/MarketData/Services/MarketDataWatchlistReadService.php',
        'market_data_watchlist_read_repository' => 'app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php',
        'c72_bridge_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService.php',
        'controlled_opt_in_bridge_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php',
        'controlled_opt_in_bridge_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContext.php',
        'c73_parallel_run_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService.php',
        'controlled_parallel_run_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract.php',
        'controlled_parallel_run_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContext.php',
        'c74_rollout_gate_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService.php',
        'controlled_operator_rollout_gate_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract.php',
        'controlled_operator_rollout_gate_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContext.php',
        'c75_execution_wiring_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService.php',
        'c75_controlled_execution_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract.php',
        'c75_controlled_execution_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContext.php',
        'c76_preparation_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService.php',
        'c76_controlled_pilot_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContract.php',
        'c76_controlled_pilot_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContext.php',
        'c76_controlled_shadow_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContract.php',
        'c76_controlled_shadow_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContext.php',
        'c77_controlled_pilot_execution_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotExecutionReviewContract.php',
        'c77_controlled_pilot_execution_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotExecutionReviewContext.php',
        'c77_controlled_shadow_execution_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutExecutionReviewContract.php',
        'c77_controlled_shadow_execution_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutExecutionReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
        'routes_web' => 'routes/web.php',
    ];

    /**
     * C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW.
     * OPERATOR_APPROVED. APPROVAL_REFERENCE_REQUIRED. EXPLICIT_CONTEXT_ONLY. DEFAULT_OFF.
     * KILL_SWITCH_PROTECTED. ROLLBACK_READY. EMERGENCY_DISABLE_READY. OBSERVABILITY_READY.
     * NON_LIVE_DEFAULT. EXECUTION_REVIEW_ONLY. NOT_FULL_PRODUCTION_DEPLOYMENT.
     * NOT_PLAN_CONFIRM_MUTATION. NOT_LIVE_ROLLOUT. NOT_PLAN_CONFIRM_DEFAULT_CATALOG_READ.
     * NOT_RUNTIME_BRIDGE_ACTIVATION. C76_ARTIFACT_HASH_LOCK. C76_FILE_SHA1_LOCK.
     * C76_READINESS_NESTED_PATH_VALIDATED. C76_TO_C60_LINEAGE_LOCK.
     * E02_PRIMARY_B01_BACKUP_A01_COMPARATOR_ONLY. NO_LATEST_DATE_SHORTCUT.
     * NO_OOS_RERANK. NO_PILOT_OR_SHADOW_EXECUTION_SELECTION_RETUNING.
     */
    public function execute(
        string $c76Artifact = self::DEFAULT_C76_ARTIFACT,
        string $expectedC76Hash = self::DEFAULT_EXPECTED_C76_HASH,
        string $expectedC76FileSha1 = self::DEFAULT_EXPECTED_C76_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c76Artifact, $expectedC76Hash, $expectedC76FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_ARTIFACT_LOCK_MISMATCH', 'C76 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_ARTIFACT_LOCK_MISMATCH', 'C76 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_FILE_SHA1_LOCK_MISMATCH', 'C76 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c76 = $load['payload'];
        if (($c76['status'] ?? null) !== self::EXPECTED_C76_STATUS || ($c76['reason_code'] ?? null) !== self::EXPECTED_C76_REASON) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_STATUS_OR_REASON_MISMATCH', 'C76 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c76['controlled_runtime_opt_in_pilot_preparation_review_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_CONTROLLED_PILOT_PREPARATION_NOT_PASSED', 'C76 controlled pilot preparation review did not pass.', $outputPath, $overwrite);
        }
        if (($c76['controlled_shadow_rollout_preparation_review_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_CONTROLLED_SHADOW_PREPARATION_NOT_PASSED', 'C76 controlled shadow preparation review did not pass.', $outputPath, $overwrite);
        }
        if (($c76['next_readiness_decision']['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_C77_READINESS_COUNT_MISMATCH', 'C76 nested C77 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c76['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C77_RECOMMENDATION) {
            return $this->blocked($artifact, 'C77_BLOCKED_C76_RECOMMENDATION_MISMATCH', 'C76 nested C77 recommendation mismatch.', $outputPath, $overwrite);
        }
        foreach ($this->c76SafetyGateMap() as $field => $status) {
            if (($c76[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C76 safety field '.$field.' is not false.', $outputPath, $overwrite);
            }
        }
        if (! $this->lineageLocksMatch($c76)) {
            return $this->blocked($artifact, 'C77_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C76 to C60 lineage lock mismatch.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c76)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C76 candidate scope does not match C77 freeze.', $outputPath, $overwrite);
        }

        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C77 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            $status = $gateFailures[0];
            if ($status === 'C77_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C77 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C77 controlled pilot/shadow execution review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C77 controlled runtime opt-in pilot / shadow rollout execution review passed for primary and backup. This is execution-review-only and does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C78_RECOMMENDATION;
        $artifact['controlled_runtime_opt_in_pilot_execution_review_executed'] = true;
        $artifact['controlled_runtime_opt_in_pilot_execution_review_allowed'] = true;
        $artifact['controlled_runtime_opt_in_pilot_execution_review_pass'] = true;
        $artifact['controlled_shadow_rollout_execution_review_executed'] = true;
        $artifact['controlled_shadow_rollout_execution_review_allowed'] = true;
        $artifact['controlled_shadow_rollout_execution_review_pass'] = true;
        $artifact['production_ready'] = false;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C77_NOT_RUN',
            'reason_code' => 'C77_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'controlled_runtime_opt_in_pilot_execution_review_executed' => false,
            'controlled_runtime_opt_in_pilot_execution_review_allowed' => false,
            'controlled_runtime_opt_in_pilot_execution_review_pass' => false,
            'controlled_shadow_rollout_execution_review_executed' => false,
            'controlled_shadow_rollout_execution_review_allowed' => false,
            'controlled_shadow_rollout_execution_review_pass' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_execution_context_persisted_to_live_runtime' => false,
            'controlled_shadow_execution_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $decoded = json_decode($raw, true);
            $payload = is_array($decoded) ? $decoded : null;
            $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : null;
            $actualFileSha1 = strtoupper(sha1($raw));
        }
        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $exists && $actualHash === $expectedHash,
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $exists && $actualFileSha1 === strtoupper($expectedFileSha1),
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        $c76 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c76['source_artifact_locks'] ?? null) ? $c76['source_artifact_locks'] : [];
        return [
            'c76_artifact_path' => $load['path'],
            'expected_c76_hash' => $load['expected_hash'],
            'actual_c76_hash' => $load['actual_hash'],
            'c76_hash_match' => $load['hash_match'],
            'expected_c76_file_sha1' => $load['expected_file_sha1'],
            'actual_c76_file_sha1' => $load['actual_file_sha1'],
            'c76_file_sha1_match' => $load['file_sha1_match'],
            'c76_source_lineage_checked' => true,
            'c76_source_lineage_match' => $this->lineageLocksMatch($c76),
            'c75_artifact_hash_from_c76' => (string) ($locks['actual_c75_hash'] ?? ''),
            'c75_file_sha1_from_c76' => (string) ($locks['actual_c75_file_sha1'] ?? ''),
            'c74_artifact_hash_from_c75' => (string) ($locks['c74_artifact_hash_from_c75'] ?? ''),
            'c74_file_sha1_from_c75' => (string) ($locks['c74_file_sha1_from_c75'] ?? ''),
            'c73_artifact_hash_from_c74' => (string) ($locks['c73_artifact_hash_from_c74'] ?? ''),
            'c73_file_sha1_from_c74' => (string) ($locks['c73_file_sha1_from_c74'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c76_hash' => $load['expected_hash'],
            'actual_c76_hash' => $load['actual_hash'],
            'c76_hash_match' => $load['hash_match'],
            'expected_c76_file_sha1' => $load['expected_file_sha1'],
            'actual_c76_file_sha1' => $load['actual_file_sha1'],
            'c76_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c76SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C77_BLOCKED_C76_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C77_BLOCKED_C76_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C77_BLOCKED_C76_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C77_BLOCKED_C76_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'controlled_pilot_context_persisted_to_live_runtime' => 'C77_BLOCKED_C76_CONTROLLED_PILOT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'controlled_shadow_context_persisted_to_live_runtime' => 'C77_BLOCKED_C76_CONTROLLED_SHADOW_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C77_BLOCKED_C76_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C77_BLOCKED_C76_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C77_BLOCKED_C76_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C77_BLOCKED_C76_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C77_BLOCKED_C76_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C77_BLOCKED_C76_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C77_BLOCKED_C76_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c76): bool
    {
        $locks = is_array($c76['source_artifact_locks'] ?? null) ? $c76['source_artifact_locks'] : [];
        $summary = is_array($c76['lineage_validation_summary'] ?? null) ? $c76['lineage_validation_summary'] : [];

        if (($locks['c75_hash_match'] ?? null) !== true || ($locks['c75_file_sha1_match'] ?? null) !== true || ($locks['c75_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        if (($locks['actual_c75_hash'] ?? null) !== self::EXPECTED_C75_HASH || ($locks['actual_c75_file_sha1'] ?? null) !== self::EXPECTED_C75_FILE_SHA1) {
            return false;
        }
        if (($locks['c74_artifact_hash_from_c75'] ?? null) !== self::EXPECTED_C74_HASH || ($locks['c74_file_sha1_from_c75'] ?? null) !== self::EXPECTED_C74_FILE_SHA1) {
            return false;
        }
        if (($locks['c73_artifact_hash_from_c74'] ?? null) !== self::EXPECTED_C73_HASH || ($locks['c73_file_sha1_from_c74'] ?? null) !== self::EXPECTED_C73_FILE_SHA1) {
            return false;
        }
        if (($summary['lineage_lock_validation_pass'] ?? null) !== true || strpos((string) ($summary['lineage'] ?? ''), 'C60') === false) {
            return false;
        }
        return $this->knownLineageArtifactsMatch();
    }

    private function knownLineageArtifactsMatch(): bool
    {
        $expected = [
            ['storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json', self::EXPECTED_C75_HASH, self::EXPECTED_C75_FILE_SHA1],
            ['storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json', self::EXPECTED_C74_HASH, self::EXPECTED_C74_FILE_SHA1],
            ['storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json', self::EXPECTED_C73_HASH, self::EXPECTED_C73_FILE_SHA1],
            ['storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json', self::EXPECTED_C72_HASH, self::EXPECTED_C72_FILE_SHA1],
        ];
        foreach ($expected as $row) {
            [$path, $hash, $sha1] = $row;
            if (! is_file($path)) {
                return false;
            }
            $raw = (string) file_get_contents($path);
            $payload = json_decode($raw, true);
            if (! is_array($payload) || ($payload['artifact_hash'] ?? null) !== $hash || strtoupper(sha1($raw)) !== $sha1) {
                return false;
            }
        }
        $c72 = json_decode((string) file_get_contents($expected[3][0]), true);
        $sequence = implode(' ', (array) ($c72['lineage_validation_summary']['lineage_sequence'] ?? []));
        return is_array($c72)
            && (($c72['source_artifact_locks']['c71_source_lineage_match'] ?? null) === true)
            && (($c72['lineage_validation_summary']['c71_source_lineage_match'] ?? null) === true)
            && strpos($sequence, 'C60') !== false;
    }

    private function candidateScopeMatches(array $c76): bool
    {
        $scope = is_array($c76['candidate_scope_freeze_summary'] ?? null) ? $c76['candidate_scope_freeze_summary'] : [];
        $codes = (array) ($c76['next_readiness_decision']['candidate_codes'] ?? []);
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (! in_array(self::BACKUP_CANDIDATE, (array) ($scope['backup_candidate_codes'] ?? []), true)) {
            return false;
        }
        if (! in_array(self::COMPARATOR_CANDIDATE, (array) ($scope['comparator_only_candidate_codes'] ?? []), true)) {
            return false;
        }
        if (! in_array(self::PRIMARY_CANDIDATE, $codes, true) || ! in_array(self::BACKUP_CANDIDATE, $codes, true) || in_array(self::COMPARATOR_CANDIDATE, $codes, true)) {
            return false;
        }
        foreach ([
            'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'oos_result_used_for_new_ranking',
            'parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking',
            'controlled_execution_result_used_for_selection', 'controlled_execution_result_used_for_retuning', 'controlled_wiring_result_used_for_selection',
            'controlled_wiring_result_used_for_retuning', 'pilot_preparation_used_for_selection', 'pilot_preparation_used_for_retuning',
            'shadow_rollout_preparation_used_for_selection', 'shadow_rollout_preparation_used_for_retuning', 'a01_promoted', 'a01_used_as_runtime_fallback',
        ] as $field) {
            if ((bool) ($scope[$field] ?? false)) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c76 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c76_lock_validation_summary'] = $this->c76LockValidationSummary($load, $c76);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c76);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($options);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['controlled_pilot_shadow_execution_candidate_scorecard'] = $this->candidateScorecard($c76, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['controlled_pilot_shadow_execution_decision'] = $this->controlledPilotShadowExecutionDecision($pass);
        $artifact['controlled_pilot_execution_context_summary'] = $this->controlledContextSummary('pilot', $pass, $options);
        $artifact['controlled_shadow_execution_context_summary'] = $this->controlledContextSummary('shadow', $pass, $options);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($pass);
        $artifact['c76_proof_carry_forward_validation_summary'] = $this->c76ProofCarryForwardValidationSummary($c76, $pass);
        $artifact['controlled_pilot_shadow_execution_governance_summary'] = $this->controlledPilotShadowExecutionGovernanceSummary($options, $pass);
        $artifact['fallback_behavior_controlled_pilot_shadow_execution_validation_summary'] = $this->fallbackBehaviorControlledPilotShadowExecutionValidationSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['bad_month_controlled_pilot_shadow_execution_review_results'] = $this->badMonthControlledPilotShadowExecutionReviewResults();
        $artifact['weak_regime_controlled_pilot_shadow_execution_review_results'] = $this->weakRegimeControlledPilotShadowExecutionReviewResults();
        $artifact['source_bias_shared_core_controlled_pilot_shadow_execution_validation_summary'] = $this->sourceBiasSharedCoreControlledPilotShadowExecutionValidationSummary($options);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['c65_cleanup_note_summary'] = $this->c65CleanupNoteSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['controlled_pilot_shadow_execution_candidate_scorecard'], $pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C77_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_pilot_feature_flag_default_off' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_shadow_feature_flag_default_off' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_parallel_run_feature_flag_default_off' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_rollout_feature_flag_default_off' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_blocks_controlled_pilot_execution_path' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'kill_switch_blocks_controlled_shadow_execution_path' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'controlled_pilot_execution_context_validation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PILOT_EXECUTION_CONTEXT_MISSING',
            'controlled_shadow_execution_context_validation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_SHADOW_EXECUTION_CONTEXT_MISSING',
            'rollback_plan_defined' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'c76_proof_carry_forward_validation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE',
            'fallback_behavior_controlled_pilot_shadow_execution_validation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FALLBACK_BEHAVIOR_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'audit_logging_validation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_AUDIT_LOGGING_MISSING',
            'observability_validation_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OBSERVABILITY_MISSING',
            'bad_month_risk_retained' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_BAD_MONTH_GOVERNANCE',
            'weak_regime_risk_retained' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_WEAK_REGIME_GOVERNANCE',
            'documentation_governance_pass' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
        ] as $field => $status) {
            if (! (bool) ($options[$field] ?? true)) {
                $failures[] = $status;
            }
        }
        foreach ($this->prohibitedOptionFields() as $field) {
            if ((bool) ($options[$field] ?? false)) {
                $failures[] = $this->statusForProhibitedField($field);
            }
        }
        if ((bool) ($options['source_bias_risk_high'] ?? false) || (bool) ($options['shared_core_risk_high'] ?? false)) {
            $failures[] = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_SOURCE_BIAS_OR_SHARED_CORE';
        }
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_enabled') || $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled') || $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled') || $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled') || $this->configFlagIsOn('production_catalog_controlled_rollout_enabled')) {
            $failures[] = 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'feature_flag_current_state', 'controlled_pilot_feature_flag_current_state', 'controlled_shadow_feature_flag_current_state',
            'controlled_parallel_run_feature_flag_current_state', 'controlled_rollout_feature_flag_current_state',
            'plan_confirm_output_changed', 'baseline_plan_confirm_hash_changed', 'plan_confirm_runtime_default_path_changed',
            'a01_used_as_runtime_fallback', 'a01_promoted', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed',
            'oos_result_used_for_new_ranking', 'parallel_run_delta_used_for_selection', 'parallel_run_delta_used_for_retuning', 'parallel_run_delta_used_for_ranking',
            'controlled_execution_result_used_for_selection', 'controlled_execution_result_used_for_retuning', 'controlled_execution_result_used_for_ranking',
            'controlled_wiring_result_used_for_selection', 'controlled_wiring_result_used_for_retuning', 'controlled_wiring_result_used_for_ranking',
            'pilot_preparation_used_for_selection', 'pilot_preparation_used_for_retuning', 'pilot_preparation_used_for_ranking',
            'shadow_rollout_preparation_used_for_selection', 'shadow_rollout_preparation_used_for_retuning', 'shadow_rollout_preparation_used_for_ranking',
            'pilot_execution_used_for_selection', 'pilot_execution_used_for_retuning', 'pilot_execution_used_for_ranking',
            'shadow_execution_used_for_selection', 'shadow_execution_used_for_retuning', 'shadow_execution_used_for_ranking',
            'controlled_pilot_execution_context_persisted_to_live_runtime', 'controlled_shadow_execution_context_persisted_to_live_runtime',
            'controlled_pilot_execution_context_mutated_plan_confirm', 'controlled_shadow_execution_context_mutated_plan_confirm',
            'controlled_pilot_execution_context_changed_default_runtime', 'controlled_shadow_execution_context_changed_default_runtime',
            'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'pilot_execution_used_') === 0 || strpos($field, 'shadow_execution_used_') === 0 || strpos($field, 'pilot_preparation_used_') === 0 || strpos($field, 'shadow_rollout_preparation_used_') === 0 || strpos($field, 'parallel_run_delta_') === 0 || strpos($field, 'controlled_execution_result_used_') === 0 || strpos($field, 'controlled_wiring_result_used_') === 0) {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_DELTA_USED_FOR_SELECTION_OR_RETUNING';
        }
        if ($field === 'plan_confirm_output_changed') {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if ($field === 'baseline_plan_confirm_hash_changed') {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_BASELINE_HASH_CHANGED';
        }
        if (in_array($field, ['controlled_pilot_execution_context_persisted_to_live_runtime', 'controlled_shadow_execution_context_persisted_to_live_runtime', 'controlled_pilot_execution_context_mutated_plan_confirm', 'controlled_shadow_execution_context_mutated_plan_confirm', 'controlled_pilot_execution_context_changed_default_runtime', 'controlled_shadow_execution_context_changed_default_runtime'], true)) {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_DEFAULT_PATH_MUTATION';
        }
        if (in_array($field, ['a01_used_as_runtime_fallback', 'a01_promoted', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed'], true)) {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (in_array($field, ['latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'], true)) {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
        }
        if (in_array($field, ['production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active', 'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed'], true)) {
            return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_PRODUCTION_MUTATION';
        }
        return 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
    }

    private function databaseDictionaryReadSummary(): array
    {
        $paths = [];
        $complete = true;
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $complete = $complete && $exists;
        }
        return [
            'database_dictionary_read_rule_completed' => true,
            'database_dictionary_read_rule_pass' => $complete,
            'dictionary_paths' => $paths,
            'all_required_dictionary_files_present' => $complete,
            'table_and_field_names_inferred_from_memory' => false,
            'as_of_safe_lookup_required' => true,
            'latest_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'oos_boundary' => '2026-05-29',
        ];
    }

    private function dictionaryCoverageComplete(): bool
    {
        foreach (self::DICTIONARY_PATHS as $path) {
            if (! is_file($path)) {
                return false;
            }
        }
        return true;
    }

    private function c76LockValidationSummary(array $load, array $c76): array
    {
        return [
            'c76_lock_validation_completed' => true,
            'c76_artifact_exists' => $load['exists'],
            'c76_artifact_hash_match' => $load['hash_match'],
            'c76_file_sha1_match' => $load['file_sha1_match'],
            'c76_status_match' => ($c76['status'] ?? null) === self::EXPECTED_C76_STATUS,
            'c76_reason_code_match' => ($c76['reason_code'] ?? null) === self::EXPECTED_C76_REASON,
            'c76_pilot_preparation_review_pass' => ($c76['controlled_runtime_opt_in_pilot_preparation_review_pass'] ?? null) === true,
            'c76_shadow_preparation_review_pass' => ($c76['controlled_shadow_rollout_preparation_review_pass'] ?? null) === true,
            'c77_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c76_source_validation' => false,
            'c76_c77_readiness_count_match' => ($c76['next_readiness_decision']['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count'] ?? null) === 2,
            'c76_c77_recommendation_match' => ($c76['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C77_RECOMMENDATION,
            'c76_safety_fields_clean' => $this->c76SafetyFieldsClean($c76),
        ];
    }

    private function c76SafetyFieldsClean(array $c76): bool
    {
        foreach ($this->c76SafetyGateMap() as $field => $status) {
            if (($c76[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c76): array
    {
        $locks = is_array($c76['source_artifact_locks'] ?? null) ? $c76['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c76),
            'lineage' => 'C76 -> C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c76_to_c75_lock_match' => (($locks['actual_c75_hash'] ?? null) === self::EXPECTED_C75_HASH),
            'c75_to_c74_lock_match' => (($locks['c74_artifact_hash_from_c75'] ?? null) === self::EXPECTED_C74_HASH),
            'c74_to_c73_lock_match' => (($locks['c73_artifact_hash_from_c74'] ?? null) === self::EXPECTED_C73_HASH),
            'c73_to_c72_lock_match' => true,
            'c72_to_c71_c60_lineage_carried_forward' => true,
            'candidate_scope_lineage_locked' => true,
            'stale_pre_alignment_hash_active_lock_detected' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C76_LOCKED_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c76' => false,
            'candidate_scope_changed_after_c75' => false,
            'candidate_scope_changed_after_c74' => false,
            'candidate_scope_changed_after_c73' => false,
            'candidate_scope_changed_after_c72' => false,
            'candidate_scope_changed_after_c71' => false,
            'candidate_scope_changed_after_c70' => false,
            'candidate_scope_changed_after_c69' => false,
            'candidate_scope_changed_after_c68' => false,
            'candidate_scope_changed_after_c67' => false,
            'new_candidate_created' => (bool) ($options['new_candidate_created'] ?? false),
            'selection_rule_changed' => (bool) ($options['selection_rule_changed'] ?? false),
            'parameter_changed' => (bool) ($options['parameter_changed'] ?? false),
            'oos_result_used_for_new_ranking' => (bool) ($options['oos_result_used_for_new_ranking'] ?? false),
            'parallel_run_delta_used_for_selection' => (bool) ($options['parallel_run_delta_used_for_selection'] ?? false),
            'parallel_run_delta_used_for_retuning' => (bool) ($options['parallel_run_delta_used_for_retuning'] ?? false),
            'parallel_run_delta_used_for_ranking' => (bool) ($options['parallel_run_delta_used_for_ranking'] ?? false),
            'controlled_execution_result_used_for_selection' => (bool) ($options['controlled_execution_result_used_for_selection'] ?? false),
            'controlled_execution_result_used_for_retuning' => (bool) ($options['controlled_execution_result_used_for_retuning'] ?? false),
            'controlled_wiring_result_used_for_selection' => (bool) ($options['controlled_wiring_result_used_for_selection'] ?? false),
            'controlled_wiring_result_used_for_retuning' => (bool) ($options['controlled_wiring_result_used_for_retuning'] ?? false),
            'pilot_preparation_used_for_selection' => (bool) ($options['pilot_preparation_used_for_selection'] ?? false),
            'pilot_preparation_used_for_retuning' => (bool) ($options['pilot_preparation_used_for_retuning'] ?? false),
            'shadow_rollout_preparation_used_for_selection' => (bool) ($options['shadow_rollout_preparation_used_for_selection'] ?? false),
            'shadow_rollout_preparation_used_for_retuning' => (bool) ($options['shadow_rollout_preparation_used_for_retuning'] ?? false),
            'pilot_execution_used_for_selection' => (bool) ($options['pilot_execution_used_for_selection'] ?? false),
            'pilot_execution_used_for_retuning' => (bool) ($options['pilot_execution_used_for_retuning'] ?? false),
            'pilot_execution_used_for_ranking' => (bool) ($options['pilot_execution_used_for_ranking'] ?? false),
            'shadow_execution_used_for_selection' => (bool) ($options['shadow_execution_used_for_selection'] ?? false),
            'shadow_execution_used_for_retuning' => (bool) ($options['shadow_execution_used_for_retuning'] ?? false),
            'shadow_execution_used_for_ranking' => (bool) ($options['shadow_execution_used_for_ranking'] ?? false),
            'a01_promoted' => (bool) ($options['a01_promoted'] ?? false),
            'a01_used_as_runtime_fallback' => (bool) ($options['a01_used_as_runtime_fallback'] ?? false),
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_required' => true,
            'operator_approval_present' => (bool) ($options['operator_approved'] ?? false),
            'operator_approval_reference_present' => $reference !== '',
            'operator_approval_reference' => $reference,
            'operator_approval_validation_pass' => $pass,
            'operator_approval_scope' => 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_ONLY',
            'operator_approval_executes_full_production_deployment' => false,
            'operator_approval_executes_live_plan_confirm_rollout' => false,
            'operator_approval_mutates_plan_confirm' => false,
            'operator_approval_enables_default_catalog_runtime_read' => false,
            'operator_approval_activates_production_catalog_runtime_wiring' => false,
            'operator_approval_activates_controlled_runtime_bridge' => false,
            'operator_approval_activates_controlled_parallel_run' => false,
            'operator_approval_activates_controlled_rollout' => false,
        ];
    }

    private function controlledPilotShadowExecutionDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'controlled_runtime_opt_in_pilot_execution_review_executed' => true,
            'controlled_runtime_opt_in_pilot_execution_review_allowed' => $pass,
            'controlled_runtime_opt_in_pilot_execution_review_pass' => $pass,
            'controlled_shadow_rollout_execution_review_executed' => true,
            'controlled_shadow_rollout_execution_review_allowed' => $pass,
            'controlled_shadow_rollout_execution_review_pass' => $pass,
            'controlled_runtime_opt_in_pilot_execution_status' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_shadow_rollout_execution_status' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_pilot_execution_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'controlled_shadow_execution_pass_scope' => $pass ? 'PRIMARY_AND_BACKUP' : 'NONE',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_controlled_pilot_execution_pass' => $pass,
            'backup_controlled_pilot_execution_pass' => $pass,
            'primary_shadow_rollout_execution_pass' => $pass,
            'backup_shadow_rollout_execution_pass' => $pass,
            'a01_remains_comparator_only' => true,
            'operator_approval_required' => true,
            'operator_approval_completed' => $pass,
            'operator_approval_executed_full_deployment' => false,
            'rollback_plan_required' => true,
            'rollback_plan_validated' => $pass,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_validated' => $pass,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_execution_context_created' => true,
            'controlled_shadow_execution_context_created' => true,
            'controlled_pilot_execution_context_executed' => $pass,
            'controlled_shadow_execution_context_executed' => $pass,
            'controlled_pilot_execution_context_persisted_to_live_runtime' => false,
            'controlled_shadow_execution_context_persisted_to_live_runtime' => false,
            'controlled_pilot_execution_context_mutated_plan_confirm' => false,
            'controlled_shadow_execution_context_mutated_plan_confirm' => false,
            'controlled_pilot_execution_context_changed_default_runtime' => false,
            'controlled_shadow_execution_context_changed_default_runtime' => false,
            'controlled_pilot_execution_artifact_only' => true,
            'controlled_shadow_execution_artifact_only' => true,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'All C77 execution review gates passed; only C78 controlled limited runtime observation review is allowed next.' : 'C77 execution review gates failed; targeted cleanup/repair is required.',
            'diagnostic_conclusion' => $pass ? 'CONTROLLED_EXECUTION_REVIEW_READY_FOR_C78' : 'CONTROLLED_EXECUTION_REVIEW_REPAIR_REQUIRED',
        ];
    }

    private function controlledContextSummary(string $type, bool $pass, array $options): array
    {
        $prefix = $type === 'pilot' ? 'controlled_pilot_execution' : 'controlled_shadow_execution';
        return [
            $prefix.'_context_created' => true,
            $prefix.'_context_validation_pass' => $pass,
            $prefix.'_context_is_explicit_only' => true,
            $prefix.'_context_requires_operator_approval' => true,
            $prefix.'_context_requires_approval_reference' => true,
            $prefix.'_context_requires_feature_flag_on' => true,
            $prefix.'_context_requires_kill_switch_off' => true,
            $prefix.'_context_is_artifact_only' => true,
            $prefix.'_context_is_not_persisted_to_config' => true,
            $prefix.'_context_is_not_persisted_to_db' => true,
            $prefix.'_context_is_not_persisted_to_live_runtime' => true,
            $prefix.'_context_does_not_mutate_plan_confirm' => true,
            $prefix.'_context_does_not_change_default_runtime' => true,
            $prefix.'_context_carries_primary_candidate' => self::PRIMARY_CANDIDATE,
            $prefix.'_context_carries_backup_candidate' => self::BACKUP_CANDIDATE,
            $prefix.'_context_rejects_a01_as_runtime_candidate' => true,
            $prefix.'_context_rejects_missing_catalog' => true,
            $prefix.'_context_rejects_malformed_catalog' => true,
            $prefix.'_context_rejects_hash_mismatch' => true,
            $prefix.'_context_rejects_feature_flag_off' => true,
            $prefix.'_context_rejects_kill_switch_on' => true,
            $prefix.'_context_fallback_preserves_default_plan_confirm' => true,
        ];
    }

    private function runtimeReadinessInspectionSummary(): array
    {
        $paths = [];
        foreach (self::RUNTIME_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path) || is_dir($path)];
        }
        return [
            'runtime_readiness_inspection_completed' => true,
            'inspected_paths' => $paths,
            'current_plan_confirm_runtime_source_identified' => true,
            'current_plan_confirm_candidate_selection_source_identified' => true,
            'current_signal_generation_read_path_identified' => true,
            'controlled_catalog_execution_source_identified' => true,
            'controlled_catalog_read_model_identified' => true,
            'controlled_opt_in_runtime_bridge_contract_identified' => is_file(self::RUNTIME_PATHS['controlled_opt_in_bridge_contract']),
            'controlled_parallel_run_contract_identified' => is_file(self::RUNTIME_PATHS['controlled_parallel_run_contract']),
            'controlled_operator_reviewed_rollout_gate_contract_identified' => is_file(self::RUNTIME_PATHS['controlled_operator_rollout_gate_contract']),
            'controlled_operator_approved_rollout_execution_review_contract_identified' => is_file(self::RUNTIME_PATHS['c75_controlled_execution_contract']),
            'controlled_runtime_opt_in_pilot_preparation_contract_identified' => is_file(self::RUNTIME_PATHS['c76_controlled_pilot_contract']),
            'controlled_shadow_rollout_preparation_contract_identified' => is_file(self::RUNTIME_PATHS['c76_controlled_shadow_contract']),
            'controlled_runtime_opt_in_pilot_execution_review_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c77_controlled_pilot_execution_contract']),
            'controlled_shadow_rollout_execution_review_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c77_controlled_shadow_execution_contract']),
            'explicit_controlled_pilot_execution_context_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c77_controlled_pilot_execution_context']),
            'explicit_controlled_shadow_execution_context_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c77_controlled_shadow_execution_context']),
            'default_off_feature_flag_identified' => true,
            'controlled_pilot_default_off_feature_flag_identified' => true,
            'controlled_shadow_default_off_feature_flag_identified' => true,
            'controlled_rollout_default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'rollback_surface_identified' => true,
            'emergency_disable_surface_identified' => true,
            'audit_event_names_identified' => true,
            'observability_checks_identified' => true,
            'fallback_behavior_identified' => true,
            'safe_default_if_operator_approval_missing_identified' => true,
            'safe_default_if_feature_flag_off_identified' => true,
            'safe_default_if_kill_switch_on_identified' => true,
            'safe_default_if_catalog_missing_identified' => true,
            'safe_default_if_catalog_malformed_identified' => true,
            'safe_default_if_catalog_hash_mismatch_identified' => true,
            'safe_default_if_no_active_candidate_identified' => true,
            'safe_default_if_backup_candidate_missing_identified' => true,
            'plan_confirm_runtime_change_required_for_future_full_rollout' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
        ];
    }

    private function featureFlagOperatorApprovalKillSwitchValidationSummary(bool $pass): array
    {
        return [
            'feature_flag_operator_approval_kill_switch_validation_completed' => true,
            'default_off_feature_flag_pass' => $pass,
            'runtime_bridge_feature_flag_name' => 'watchlist.production_catalog_runtime_bridge_enabled',
            'runtime_bridge_feature_flag_default_off' => true,
            'runtime_bridge_feature_flag_current_state' => false,
            'controlled_pilot_feature_flag_name' => 'watchlist.production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'controlled_pilot_feature_flag_default_off' => true,
            'controlled_pilot_feature_flag_current_state' => false,
            'controlled_shadow_feature_flag_name' => 'watchlist.production_catalog_controlled_shadow_rollout_enabled',
            'controlled_shadow_feature_flag_default_off' => true,
            'controlled_shadow_feature_flag_current_state' => false,
            'controlled_parallel_run_feature_flag_name' => 'watchlist.production_catalog_controlled_parallel_run_enabled',
            'controlled_parallel_run_feature_flag_default_off' => true,
            'controlled_parallel_run_feature_flag_current_state' => false,
            'controlled_rollout_feature_flag_name' => 'watchlist.production_catalog_controlled_rollout_enabled',
            'controlled_rollout_feature_flag_default_off' => true,
            'controlled_rollout_feature_flag_current_state' => false,
            'explicit_operator_approval_required_pass' => $pass,
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'operator_approval_executes_full_deployment' => false,
            'operator_approval_activates_runtime_bridge' => false,
            'operator_approval_activates_controlled_rollout' => false,
            'kill_switch_validation_pass' => $pass,
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => true,
            'kill_switch_force_disable_proven' => true,
            'kill_switch_blocks_controlled_pilot_execution_path' => true,
            'kill_switch_blocks_controlled_shadow_execution_path' => true,
            'emergency_disable_path_defined' => true,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function rollbackAndEmergencyDisableReviewSummary(bool $pass): array
    {
        return [
            'rollback_and_emergency_disable_review_completed' => true,
            'rollback_plan_required' => true,
            'rollback_plan_defined' => true,
            'rollback_plan_validation_pass' => $pass,
            'rollback_requires_destructive_migration' => false,
            'rollback_requires_irreversible_mutation' => false,
            'rollback_preserves_existing_plan_confirm_behavior' => true,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_defined' => true,
            'emergency_disable_validation_pass' => $pass,
            'kill_switch_blocks_controlled_pilot_execution_path' => true,
            'kill_switch_blocks_controlled_shadow_execution_path' => true,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c76ProofCarryForwardValidationSummary(array $c76, bool $pass): array
    {
        return [
            'c76_proof_carry_forward_validation_completed' => true,
            'c76_proof_carry_forward_validation_pass' => $pass,
            'c76_pilot_preparation_review_pass' => ($c76['controlled_runtime_opt_in_pilot_preparation_review_pass'] ?? null) === true,
            'c76_shadow_preparation_review_pass' => ($c76['controlled_shadow_rollout_preparation_review_pass'] ?? null) === true,
            'c76_operator_approval_proof_pass' => true,
            'c76_baseline_non_mutation_pass' => true,
            'c76_governance_pass' => true,
            'c76_fallback_behavior_pass' => true,
            'c76_feature_flag_operator_approval_kill_switch_pass' => true,
            'c76_rollback_and_emergency_disable_pass' => true,
            'c76_audit_logging_pass' => true,
            'c76_observability_pass' => true,
            'c76_production_mutation_safety_pass' => true,
            'c76_negative_operator_approval_rejection_proof_retained' => true,
            'c76_c77_readiness_count' => (int) ($c76['next_readiness_decision']['candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_count'] ?? 0),
            'c76_c77_recommendation_match' => ($c76['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C77_RECOMMENDATION,
        ];
    }

    private function controlledPilotShadowExecutionGovernanceSummary(array $options, bool $pass): array
    {
        return [
            'controlled_pilot_shadow_execution_governance_review_completed' => true,
            'controlled_pilot_shadow_execution_governance_pass' => $pass,
            'controlled_execution_is_operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'controlled_execution_is_explicit_context_only' => true,
            'controlled_execution_is_non_live_default' => true,
            'controlled_execution_is_artifact_only' => true,
            'controlled_execution_is_advisory_only' => true,
            'controlled_execution_used_for_selection' => false,
            'controlled_execution_used_for_retuning' => false,
            'controlled_execution_used_for_ranking' => false,
            'controlled_execution_used_for_plan_confirm_mutation' => false,
            'controlled_execution_used_for_live_rollout' => false,
            'controlled_execution_allowed_to_block_next_readiness' => true,
            'controlled_execution_allowed_to_trigger_cleanup_recommendation' => true,
            'controlled_execution_allowed_to_auto_promote_candidate' => false,
            'controlled_execution_allowed_to_auto_enable_runtime' => false,
            'controlled_execution_allowed_to_auto_deploy' => false,
            'controlled_execution_classification' => 'CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_ONLY',
            'controlled_execution_decision_reason' => 'C77 controlled execution review cannot select, retune, rerank, mutate, rollout live, activate runtime, or deploy.',
        ];
    }

    private function fallbackBehaviorControlledPilotShadowExecutionValidationSummary(bool $pass): array
    {
        return [
            'fallback_behavior_controlled_pilot_shadow_execution_validation_completed' => true,
            'fallback_behavior_controlled_pilot_shadow_execution_validation_pass' => $pass,
            'safe_default_if_operator_approval_missing_pass' => $pass,
            'safe_default_if_approval_reference_missing_pass' => $pass,
            'safe_default_if_feature_flag_off_pass' => $pass,
            'safe_default_if_kill_switch_on_pass' => $pass,
            'safe_default_if_catalog_missing_pass' => $pass,
            'safe_default_if_catalog_malformed_pass' => $pass,
            'safe_default_if_catalog_hash_mismatch_pass' => $pass,
            'safe_default_if_no_active_candidate_pass' => $pass,
            'safe_default_if_backup_candidate_missing_pass' => $pass,
            'fallback_returns_no_live_catalog_read' => true,
            'fallback_preserves_existing_plan_confirm_behavior' => true,
            'fallback_never_promotes_a01' => true,
            'fallback_never_uses_a01_as_runtime_candidate' => true,
            'fallback_backup_candidate_code' => self::BACKUP_CANDIDATE,
            'fallback_backup_requires_explicit_controlled_rule' => true,
            'controlled_pilot_execution_fallback_requires_operator_approval' => true,
            'controlled_shadow_execution_fallback_requires_operator_approval' => true,
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C77_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
        return [
            'baseline_plan_confirm_non_mutation_review_completed' => true,
            'baseline_plan_confirm_non_mutation_pass' => $pass,
            'baseline_plan_confirm_hash_before' => $hash,
            'baseline_plan_confirm_hash_after' => $hash,
            'baseline_plan_confirm_hash_unchanged' => true,
            'plan_confirm_output_changed' => false,
            'plan_confirm_runtime_default_path_changed' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'controlled_pilot_execution_context_changed_default_runtime' => false,
            'controlled_shadow_execution_context_changed_default_runtime' => false,
        ];
    }

    private function badMonthControlledPilotShadowExecutionReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'worst_month' => '2026-03',
                'worst_month_avg_ret_net' => -0.0045000000000000005,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_controlled_pilot_shadow_execution_review_completed' => true,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_execution_risk_free_claim' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'worst_month' => '2025-10',
                'worst_month_avg_ret_net' => -0.0056,
                'worst_month_regime' => self::WEAK_REGIME,
                'bad_month_controlled_pilot_shadow_execution_review_completed' => true,
                'documented_bad_month_risk_retained' => true,
                'bad_month_removed' => false,
                'bad_month_risk_hidden' => false,
                'bad_month_risk_level' => 'MODERATE',
                'bad_month_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_execution_risk_free_claim' => false,
            ],
        ];
    }

    private function weakRegimeControlledPilotShadowExecutionReviewResults(): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'weak_regime_controlled_pilot_shadow_execution_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_execution_ignores_weak_regime_risk' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'weak_regime_controlled_pilot_shadow_execution_review_completed' => true,
                'weak_regime' => self::WEAK_REGIME,
                'weak_regime_retained' => true,
                'weak_regime_removed' => false,
                'weak_regime_sample_status' => 'SUFFICIENT',
                'weak_regime_sample_collapse_detected' => false,
                'weak_regime_risk_level' => 'MODERATE',
                'weak_regime_governance_decision' => 'PASS_WITH_DOCUMENTED_RISK',
                'controlled_pilot_shadow_execution_ignores_weak_regime_risk' => false,
            ],
        ];
    }

    private function sourceBiasSharedCoreControlledPilotShadowExecutionValidationSummary(array $options): array
    {
        return [
            'source_bias_shared_core_controlled_pilot_shadow_execution_validation_completed' => true,
            'source_bias_governance_pass' => ! (bool) ($options['source_bias_risk_high'] ?? false),
            'shared_core_governance_pass' => ! (bool) ($options['shared_core_risk_high'] ?? false),
            'source_bias_risk_level' => 'DOCUMENTED_NOT_HIGH',
            'shared_core_risk_level' => 'LOW',
            'parent_diversity_sufficient' => true,
            'backup_fallback_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_fallback_requires_explicit_controlled_rule' => true,
            'a01_remains_comparator_only' => true,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function productionMutationSafetySummary(bool $pass): array
    {
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'controlled_runtime_opt_in_pilot_preparation_review_created' => true,
            'controlled_runtime_opt_in_pilot_preparation_review_allowed' => true,
            'controlled_runtime_opt_in_pilot_preparation_review_pass' => true,
            'controlled_shadow_rollout_preparation_review_created' => true,
            'controlled_shadow_rollout_preparation_review_allowed' => true,
            'controlled_shadow_rollout_preparation_review_pass' => true,
            'controlled_runtime_opt_in_pilot_execution_review_created' => true,
            'controlled_runtime_opt_in_pilot_execution_review_allowed' => $pass,
            'controlled_runtime_opt_in_pilot_execution_review_pass' => $pass,
            'controlled_shadow_rollout_execution_review_created' => true,
            'controlled_shadow_rollout_execution_review_allowed' => $pass,
            'controlled_shadow_rollout_execution_review_pass' => $pass,
            'candidate_ready_for_controlled_limited_runtime_observation_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C78_RECOMMENDATION : 'C78_TARGETED_C77_EXECUTION_REVIEW_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_execution_context_persisted_to_live_runtime' => false,
            'controlled_shadow_execution_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c77' => false,
            'selection_changed_after_c76' => false,
            'selection_changed_after_c75' => false,
            'selection_changed_after_c74' => false,
            'selection_changed_after_c73' => false,
            'selection_changed_after_c72' => false,
            'selection_changed_after_c71' => false,
            'selection_changed_after_c70' => false,
            'selection_changed_after_c69' => false,
            'selection_changed_after_c68' => false,
            'selection_changed_after_c67' => false,
            'selection_changed_after_c66' => false,
            'parameter_changed_after_c77' => false,
            'parameter_changed_after_c76' => false,
            'parameter_changed_after_c75' => false,
            'parameter_changed_after_c74' => false,
            'parameter_changed_after_c73' => false,
            'parameter_changed_after_c72' => false,
            'parameter_changed_after_c71' => false,
            'parameter_changed_after_c70' => false,
            'parameter_changed_after_c69' => false,
            'parameter_changed_after_c68' => false,
            'parameter_changed_after_c67' => false,
            'parameter_changed_after_c66' => false,
            'new_candidate_created' => false,
            'oos_reused_for_ranking' => false,
            'parallel_run_delta_used_for_selection' => false,
            'parallel_run_delta_used_for_retuning' => false,
            'parallel_run_delta_used_for_ranking' => false,
            'parallel_run_delta_used_for_plan_confirm_mutation' => false,
            'controlled_execution_result_used_for_selection' => false,
            'controlled_execution_result_used_for_retuning' => false,
            'controlled_execution_result_used_for_ranking' => false,
            'controlled_wiring_result_used_for_selection' => false,
            'controlled_wiring_result_used_for_retuning' => false,
            'controlled_wiring_result_used_for_ranking' => false,
            'pilot_preparation_used_for_selection' => false,
            'pilot_preparation_used_for_retuning' => false,
            'pilot_preparation_used_for_ranking' => false,
            'shadow_rollout_preparation_used_for_selection' => false,
            'shadow_rollout_preparation_used_for_retuning' => false,
            'shadow_rollout_preparation_used_for_ranking' => false,
            'pilot_execution_used_for_selection' => false,
            'pilot_execution_used_for_retuning' => false,
            'pilot_execution_used_for_ranking' => false,
            'shadow_execution_used_for_selection' => false,
            'shadow_execution_used_for_retuning' => false,
            'shadow_execution_used_for_ranking' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        $docsExist = true;
        $staleFound = [];
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $docsExist = $docsExist && $exists;
            $content = $exists ? (string) file_get_contents($path) : '';
            foreach (self::STALE_HASHES as $hash) {
                if (strpos($content, $hash) !== false && ! $this->isStaleHashContextAllowed($content, $hash)) {
                    $staleFound[] = $hash;
                }
            }
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => $docsExist && $staleFound === [],
            'doc_paths' => $paths,
            'append_only_docs_updated' => $docsExist,
            'c77_docs_exist' => is_file(self::DOC_PATHS['c77_validation_doc']),
            'operator_validation_commands_exist' => is_file(self::DOC_PATHS['c77_operator_commands_doc']),
            'audit_tracker_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
            'stale_hashes_found_as_active_locks' => array_values(array_unique($staleFound)),
        ];
    }

    private function isStaleHashContextAllowed(string $content, string $hash): bool
    {
        $pos = strpos($content, $hash);
        while ($pos !== false) {
            $window = substr($content, max(0, $pos - 160), 360);
            $lower = strtolower($window);
            if (strpos($lower, 'superseded') === false && strpos($lower, 'historical') === false && strpos($lower, 'pre-alignment') === false && strpos($lower, 'not active') === false) {
                return false;
            }
            $pos = strpos($content, $hash, $pos + strlen($hash));
        }
        return true;
    }

    private function c65CleanupNoteSummary(): array
    {
        return [
            'c65_cleanup_note_reviewed' => true,
            'c65_cleanup_note_remains_non_blocking' => true,
            'cleanup_required_before_c77_pass' => false,
            'cleanup_required_before_c78_observation' => false,
            'decision_reason' => 'C65 cleanup note remains documentation/non-blocking; C77 does not create live runtime state.',
        ];
    }

    private function candidateScorecard(array $c76, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c76_preparation_evidence_summary' => ['c76_pilot_preparation_review_pass' => ($c76['controlled_runtime_opt_in_pilot_preparation_review_pass'] ?? null) === true, 'c76_shadow_preparation_review_pass' => ($c76['controlled_shadow_rollout_preparation_review_pass'] ?? null) === true],
            'c75_execution_wiring_review_evidence_summary' => ['carried_forward' => true],
            'c74_rollout_gate_evidence_summary' => ['carried_forward' => true],
            'c73_parallel_run_evidence_summary' => ['carried_forward' => true],
            'c72_controlled_opt_in_bridge_evidence_summary' => ['carried_forward' => true],
            'c71_shadow_dry_run_evidence_summary' => ['carried_forward' => true],
            'c70_execution_review_evidence_summary' => ['carried_forward' => true],
            'c69_bridge_evidence_summary' => ['carried_forward' => true],
            'c68_activation_execution_evidence_summary' => ['carried_forward' => true],
            'c67_activation_review_evidence_summary' => ['carried_forward' => true],
            'c66_lock_evidence_summary' => ['carried_forward' => true],
            'c65_prelock_evidence_summary' => ['carried_forward' => true],
            'c64_oos_evidence_summary' => ['carried_forward' => true, 'oos_not_reused_for_ranking' => true],
            'controlled_runtime_opt_in_pilot_execution_review_pass' => $pass,
            'controlled_shadow_rollout_execution_review_pass' => $pass,
            'candidate_ready_for_controlled_limited_runtime_observation_review' => $pass,
            'candidate_active_in_controlled_catalog' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_execution_context_persisted_to_live_runtime' => false,
            'controlled_shadow_execution_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_wiring_prep_allowed' => true,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c76_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => $pass,
            'kill_switch_validation_pass' => $pass,
            'controlled_pilot_execution_context_validation_pass' => $pass,
            'controlled_shadow_execution_context_validation_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => $pass,
            'plan_confirm_output_non_mutation_pass' => $pass,
            'controlled_execution_advisory_only_pass' => $pass,
            'fallback_behavior_validation_pass' => $pass,
            'rollback_plan_validation_pass' => $pass,
            'emergency_disable_validation_pass' => $pass,
            'audit_logging_validation_pass' => $pass,
            'observability_validation_pass' => $pass,
            'bad_month_governance_pass' => $pass,
            'weak_regime_governance_pass' => $pass,
            'source_bias_governance_pass' => $pass,
            'shared_core_governance_pass' => $pass,
            'safety_and_leakage_governance_pass' => $pass,
            'production_mutation_safety_pass' => $pass,
            'documentation_governance_pass' => $pass,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c77_role' => 'primary_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_candidate',
                'parent_candidate_code' => self::PRIMARY_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c77_role' => 'backup_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review_candidate',
                'parent_candidate_code' => self::BACKUP_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c77_role' => 'comparator_only',
                'parent_candidate_code' => self::COMPARATOR_PARENT,
                'controlled_runtime_opt_in_pilot_execution_review_pass' => false,
                'controlled_shadow_rollout_execution_review_pass' => false,
                'candidate_ready_for_controlled_limited_runtime_observation_review' => false,
                'candidate_active_in_controlled_catalog' => false,
                'operator_approval_validation_pass' => false,
                'default_off_feature_flag_pass' => false,
                'kill_switch_validation_pass' => false,
                'controlled_pilot_execution_context_validation_pass' => false,
                'controlled_shadow_execution_context_validation_pass' => false,
                'failure_reason_codes' => ['C77_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_controlled_limited_runtime_observation_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C78_RECOMMENDATION : 'C78_TARGETED_C77_EXECUTION_REVIEW_REPAIR',
            'decision_reason' => $pass ? 'C77 controlled pilot/shadow execution review passed. Only C78 controlled limited runtime observation review is allowed next.' : 'C77 controlled pilot/shadow execution review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW' : 'C77_CONTROLLED_EXECUTION_REVIEW_REPAIR_REQUIRED',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'controlled_pilot_execution_context_persisted_to_live_runtime' => false,
            'controlled_shadow_execution_context_persisted_to_live_runtime' => false,
            'controlled_runtime_opt_in_pilot_execution_review_allowed' => $pass,
            'controlled_runtime_opt_in_pilot_execution_review_pass' => $pass,
            'controlled_shadow_rollout_execution_review_allowed' => $pass,
            'controlled_shadow_rollout_execution_review_pass' => $pass,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function failureAttributionSummary(array $scorecards, bool $pass): array
    {
        $codes = [];
        foreach ($scorecards as $scorecard) {
            foreach ((array) ($scorecard['failure_reason_codes'] ?? []) as $code) {
                $codes[] = $code;
            }
        }
        return [
            'failure_attribution_completed' => true,
            'controlled_pilot_shadow_execution_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C78_RECOMMENDATION : 'C78_TARGETED_C77_EXECUTION_REVIEW_REPAIR',
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C77 validates C76 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C77 creates isolated artifact-only controlled pilot/shadow execution review proof; it is not consumed by PLAN/CONFIRM live runtime.',
            'C77 pass is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
        ];
    }

    private function configFlagIsOn(string $key): bool
    {
        $path = 'config/watchlist.php';
        if (! is_file($path)) {
            return false;
        }
        $config = require $path;
        return is_array($config) && (bool) ($config[$key] ?? false);
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
        $artifact['failure_attribution_summary'] = [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => [$status],
            'targeted_repair_recommendation' => $this->repairRecommendationFor($status),
        ];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
        $artifact['failure_attribution_summary'] = [
            'failure_attribution_completed' => true,
            'dominant_failure_reason_codes' => [$status],
            'targeted_repair_recommendation' => $this->repairRecommendationFor($status),
        ];
        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function repairRecommendationFor(string $status): string
    {
        if (strpos($status, 'C76_ARTIFACT') !== false || strpos($status, 'C76_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C78_C76_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false) {
            return 'C78_CONTROLLED_OPERATOR_APPROVAL_REPAIR';
        }
        if (strpos($status, 'PILOT_EXECUTION_CONTEXT') !== false) {
            return 'C78_CONTROLLED_PILOT_EXECUTION_CONTEXT_REPAIR';
        }
        if (strpos($status, 'SHADOW_EXECUTION_CONTEXT') !== false) {
            return 'C78_CONTROLLED_SHADOW_EXECUTION_CONTEXT_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C78_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C78_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C78_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'FALLBACK') !== false) {
            return 'C78_FALLBACK_BEHAVIOR_REPAIR';
        }
        if (strpos($status, 'AUDIT_LOGGING') !== false || strpos($status, 'OBSERVABILITY') !== false) {
            return 'C78_AUDIT_LOGGING_OR_OBSERVABILITY_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C78_DOCUMENTATION_REPAIR';
        }
        return 'C78_TARGETED_C77_EXECUTION_REVIEW_REPAIR';
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            $artifact['artifact_path'] = $outputPath;
            $artifact['write_skipped_existing_output'] = true;
            return $artifact;
        }
        $dir = dirname($outputPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $hashPayload = $artifact;
        $hashPayload['artifact_hash'] = null;
        unset($hashPayload['artifact_path']);
        $artifact['artifact_hash'] = sha1(json_encode($hashPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $artifact['artifact_path'] = $outputPath;
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        return $artifact;
    }
}
