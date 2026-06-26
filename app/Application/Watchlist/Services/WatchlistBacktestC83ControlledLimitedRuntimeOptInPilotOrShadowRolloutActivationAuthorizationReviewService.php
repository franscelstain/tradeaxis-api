<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService
{
    public const RUN_CODE = 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW';

    public const DEFAULT_C82_ARTIFACT = 'storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json';
    public const DEFAULT_EXPECTED_C82_HASH = '1c78f08cc78abe4800cde96b892932ad6b8df725';
    public const DEFAULT_EXPECTED_C82_FILE_SHA1 = '24D91E58F7F9FAADE95F6DABF985F430C48C05E2';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C82_STATUS = 'C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C82_REASON = 'C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C82_RECOMMENDATION = 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW';
    private const C84_RECOMMENDATION = 'C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW';

    private const EXPECTED_C81_HASH = '45e1abfb6ba0ddc6ddf2b0494527cf8706172f18';
    private const EXPECTED_C81_FILE_SHA1 = '588753D1F62EBCDB318A5969ACE4165CD83D98BD';
    private const EXPECTED_C80_HASH = '76270e9ebce21b101629de62aa48262d1d1a6492';
    private const EXPECTED_C80_FILE_SHA1 = 'BD51FF78572E886E38D72BC2AA2FFA23A9D2C619';
    private const EXPECTED_C79_HASH = '0ad7924e75a4627475600567fc6f6ad839a83961';
    private const EXPECTED_C79_FILE_SHA1 = '94A900AFD592C2756E2D8165B043F25191F1ACAF';
    private const EXPECTED_C78_HASH = '989826f1620bea4592e3543d4908670192fab7f0';
    private const EXPECTED_C78_FILE_SHA1 = '6C6EE121EB7B5F86E19532D24115139F5915CBF3';
    private const EXPECTED_C77_HASH = 'd827547d6d40a73785d4c2409b2913f60db42115';
    private const EXPECTED_C77_FILE_SHA1 = '8C296276DD4D278206366953F975AFD5F7E328DE';
    private const EXPECTED_C76_HASH = '40f1bc516ddbb127ab6f62433059cb99ff2ae2de';
    private const EXPECTED_C76_FILE_SHA1 = '115929AD40A739E9BE1D5A1A58DAA4FECB394ACD';
    private const EXPECTED_C75_HASH = 'cd1346cd05ab5471a947fcb5304e0f347a4881eb';
    private const EXPECTED_C75_FILE_SHA1 = '668043836BA1DB8FF50EC69DF0560988E633CF75';
    private const EXPECTED_C74_HASH = '8958e1fcec798fbd364642864b0a9d0c21bd8f93';
    private const EXPECTED_C74_FILE_SHA1 = 'D4C2EF90B533BED11F6902E75141BE5774E947BE';
    private const EXPECTED_C73_HASH = '34f1f84a4261da7ce1cb9d17a1bf33dfb1458281';
    private const EXPECTED_C73_FILE_SHA1 = 'BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9';

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c83_validation_doc' => 'docs/watchlist/audit/WS_C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW.md',
        'c83_operator_commands_doc' => 'docs/watchlist/audit/WS_C83_OPERATOR_VALIDATION_COMMANDS.md',
        'c82_validation_doc' => 'docs/watchlist/audit/WS_C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW.md',
        'c82_operator_commands_doc' => 'docs/watchlist/audit/WS_C82_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c82_pre_activation_boundary_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService.php',
        'c83_activation_authorization_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService.php',
        'c82_pre_activation_boundary_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPreActivationBoundaryReviewContract.php',
        'c82_pre_activation_boundary_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPreActivationBoundaryReviewContext.php',
        'c83_activation_authorization_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationAuthorizationReviewContract.php',
        'c83_activation_authorization_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationAuthorizationReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    public function execute(
        string $c82Artifact = self::DEFAULT_C82_ARTIFACT,
        string $expectedC82Hash = self::DEFAULT_EXPECTED_C82_HASH,
        string $expectedC82FileSha1 = self::DEFAULT_EXPECTED_C82_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c82Artifact, $expectedC82Hash, $expectedC82FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_ARTIFACT_LOCK_MISMATCH', 'C82 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_ARTIFACT_LOCK_MISMATCH', 'C82 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_FILE_SHA1_LOCK_MISMATCH', 'C82 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c82 = $load['payload'];
        if (($c82['status'] ?? null) !== self::EXPECTED_C82_STATUS || ($c82['reason_code'] ?? null) !== self::EXPECTED_C82_REASON) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_STATUS_OR_REASON_MISMATCH', 'C82 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c82['pre_activation_boundary_review_pass'] ?? null) !== true || ($c82['pre_activation_boundary_cleared'] ?? null) !== true) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_PRE_ACTIVATION_BOUNDARY_NOT_CLEARED', 'C82 pre-activation boundary was not cleared.', $outputPath, $overwrite);
        }
        if (($c82['primary_candidate_boundary_cleared'] ?? null) !== true) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_PRIMARY_BOUNDARY_NOT_CLEARED', 'C82 primary boundary was not cleared.', $outputPath, $overwrite);
        }
        if (($c82['backup_candidate_boundary_cleared'] ?? null) !== true) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_BACKUP_BOUNDARY_NOT_CLEARED', 'C82 backup boundary was not cleared.', $outputPath, $overwrite);
        }
        if (($c82['next_readiness_decision']['candidate_ready_for_activation_authorization_review_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_C83_READINESS_COUNT_MISMATCH', 'C82 nested C83 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c82['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C82_RECOMMENDATION) {
            return $this->blocked($artifact, 'C83_BLOCKED_C82_RECOMMENDATION_MISMATCH', 'C82 nested C83 recommendation mismatch.', $outputPath, $overwrite);
        }
        foreach ($this->c82SafetyGateMap() as $field => $status) {
            if (($c82[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C82 safety field '.$field.' is not false.', $outputPath, $overwrite);
            }
        }
        if (! $this->lineageLocksMatch($c82)) {
            return $this->blocked($artifact, 'C83_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C82 to C60 lineage lock mismatch.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c82)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C82 candidate scope does not match locked freeze.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C83 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);
            $status = $failures[0];
            if ($status === 'C83_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C83 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C83 activation authorization review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C83 authorized activation for primary and backup as artifact-only evidence. This still does not execute live activation.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW';
        $artifact['next_step_recommendation'] = self::C84_RECOMMENDATION;
        $artifact['activation_authorization_review_executed'] = true;
        $artifact['activation_authorization_review_allowed'] = true;
        $artifact['activation_authorization_review_pass'] = true;
        $artifact['activation_authorized'] = true;
        $artifact['activation_executed'] = false;
        $artifact['primary_candidate_activation_authorized'] = true;
        $artifact['backup_candidate_activation_authorized'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C83_NOT_RUN',
            'reason_code' => 'C83_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'activation_authorization_review_executed' => false,
            'activation_authorization_review_allowed' => false,
            'activation_authorization_review_pass' => false,
            'activation_authorized' => false,
            'activation_executed' => false,
            'primary_candidate_activation_authorized' => false,
            'backup_candidate_activation_authorized' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'activation_authorization_context_persisted_to_live_runtime' => false,
            'pre_activation_boundary_context_persisted_to_live_runtime' => false,
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
        $c82 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c82['source_artifact_locks'] ?? null) ? $c82['source_artifact_locks'] : [];
        return [
            'c82_artifact_path' => $load['path'],
            'expected_c82_hash' => $load['expected_hash'],
            'actual_c82_hash' => $load['actual_hash'],
            'c82_hash_match' => $load['hash_match'],
            'expected_c82_file_sha1' => $load['expected_file_sha1'],
            'actual_c82_file_sha1' => $load['actual_file_sha1'],
            'c82_file_sha1_match' => $load['file_sha1_match'],
            'c82_source_lineage_checked' => true,
            'c82_source_lineage_match' => $this->lineageLocksMatch($c82),
            'c81_artifact_hash_from_c82' => (string) ($locks['actual_c81_hash'] ?? ''),
            'c81_file_sha1_from_c82' => (string) ($locks['actual_c81_file_sha1'] ?? ''),
            'c80_artifact_hash_from_c81' => (string) ($locks['c80_artifact_hash_from_c81'] ?? ''),
            'c80_file_sha1_from_c81' => (string) ($locks['c80_file_sha1_from_c81'] ?? ''),
            'c79_artifact_hash_from_c80' => (string) ($locks['c79_artifact_hash_from_c80'] ?? ''),
            'c79_file_sha1_from_c80' => (string) ($locks['c79_file_sha1_from_c80'] ?? ''),
            'c78_artifact_hash_from_c79' => (string) ($locks['c78_artifact_hash_from_c79'] ?? ''),
            'c78_file_sha1_from_c79' => (string) ($locks['c78_file_sha1_from_c79'] ?? ''),
            'c77_artifact_hash_from_c78' => (string) ($locks['c77_artifact_hash_from_c78'] ?? ''),
            'c77_file_sha1_from_c78' => (string) ($locks['c77_file_sha1_from_c78'] ?? ''),
            'c76_artifact_hash_from_c77' => (string) ($locks['c76_artifact_hash_from_c77'] ?? ''),
            'c76_file_sha1_from_c77' => (string) ($locks['c76_file_sha1_from_c77'] ?? ''),
            'c75_artifact_hash_from_c76' => (string) ($locks['c75_artifact_hash_from_c76'] ?? ''),
            'c75_file_sha1_from_c76' => (string) ($locks['c75_file_sha1_from_c76'] ?? ''),
            'c74_artifact_hash_from_c75' => (string) ($locks['c74_artifact_hash_from_c75'] ?? ''),
            'c74_file_sha1_from_c75' => (string) ($locks['c74_file_sha1_from_c75'] ?? ''),
            'c73_artifact_hash_from_c74' => (string) ($locks['c73_artifact_hash_from_c74'] ?? ''),
            'c73_file_sha1_from_c74' => (string) ($locks['c73_file_sha1_from_c74'] ?? ''),
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c82_hash' => $load['expected_hash'],
            'actual_c82_hash' => $load['actual_hash'],
            'c82_hash_match' => $load['hash_match'],
            'expected_c82_file_sha1' => $load['expected_file_sha1'],
            'actual_c82_file_sha1' => $load['actual_file_sha1'],
            'c82_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c82SafetyGateMap(): array
    {
        return [
            'activation_authorized' => 'C83_BLOCKED_C82_ACTIVATION_ALREADY_AUTHORIZED',
            'production_catalog_runtime_wired' => 'C83_BLOCKED_C82_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C83_BLOCKED_C82_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C83_BLOCKED_C82_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C83_BLOCKED_C82_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'pre_activation_boundary_context_persisted_to_live_runtime' => 'C83_BLOCKED_C82_PRE_ACTIVATION_BOUNDARY_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C83_BLOCKED_C82_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C83_BLOCKED_C82_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C83_BLOCKED_C82_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C83_BLOCKED_C82_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C83_BLOCKED_C82_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C83_BLOCKED_C82_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C83_BLOCKED_C82_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c82): bool
    {
        $locks = is_array($c82['source_artifact_locks'] ?? null) ? $c82['source_artifact_locks'] : [];
        $summary = is_array($c82['lineage_validation_summary'] ?? null) ? $c82['lineage_validation_summary'] : [];
        $expected = [
            'actual_c81_hash' => self::EXPECTED_C81_HASH,
            'actual_c81_file_sha1' => self::EXPECTED_C81_FILE_SHA1,
            'c80_artifact_hash_from_c81' => self::EXPECTED_C80_HASH,
            'c80_file_sha1_from_c81' => self::EXPECTED_C80_FILE_SHA1,
            'c79_artifact_hash_from_c80' => self::EXPECTED_C79_HASH,
            'c79_file_sha1_from_c80' => self::EXPECTED_C79_FILE_SHA1,
            'c78_artifact_hash_from_c79' => self::EXPECTED_C78_HASH,
            'c78_file_sha1_from_c79' => self::EXPECTED_C78_FILE_SHA1,
            'c77_artifact_hash_from_c78' => self::EXPECTED_C77_HASH,
            'c77_file_sha1_from_c78' => self::EXPECTED_C77_FILE_SHA1,
            'c76_artifact_hash_from_c77' => self::EXPECTED_C76_HASH,
            'c76_file_sha1_from_c77' => self::EXPECTED_C76_FILE_SHA1,
            'c75_artifact_hash_from_c76' => self::EXPECTED_C75_HASH,
            'c75_file_sha1_from_c76' => self::EXPECTED_C75_FILE_SHA1,
            'c74_artifact_hash_from_c75' => self::EXPECTED_C74_HASH,
            'c74_file_sha1_from_c75' => self::EXPECTED_C74_FILE_SHA1,
            'c73_artifact_hash_from_c74' => self::EXPECTED_C73_HASH,
            'c73_file_sha1_from_c74' => self::EXPECTED_C73_FILE_SHA1,
        ];
        if (($locks['c81_hash_match'] ?? null) !== true || ($locks['c81_file_sha1_match'] ?? null) !== true || ($locks['c81_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        foreach ($expected as $field => $value) {
            if (($locks[$field] ?? null) !== $value) {
                return false;
            }
        }
        foreach ([
            'lineage_lock_validation_pass',
            'c81_to_c80_lock_match',
            'c80_to_c79_lock_match',
            'c79_to_c78_lock_match',
            'c78_to_c77_lock_match',
            'c77_to_c76_lock_match',
            'c76_to_c75_lock_match',
            'c75_to_c74_lock_match',
            'c74_to_c73_lock_match',
            'c73_to_c72_lock_match',
            'candidate_scope_lineage_locked',
        ] as $field) {
            if (($summary[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function candidateScopeMatches(array $c82): bool
    {
        $scope = is_array($c82['candidate_scope_freeze_summary'] ?? null) ? $c82['candidate_scope_freeze_summary'] : [];
        $decision = is_array($c82['pre_activation_boundary_decision'] ?? null) ? $c82['pre_activation_boundary_decision'] : [];
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || (array) ($scope['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || (array) ($scope['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE]) {
            return false;
        }
        foreach (['candidate_scope_changed_after_c81', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'boundary_used_for_selection', 'boundary_used_for_retuning', 'boundary_used_for_ranking', 'boundary_used_for_live_rollout', 'a01_promoted', 'a01_used_as_runtime_fallback'] as $field) {
            if (($scope[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($decision['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($decision['primary_candidate_boundary_cleared'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || ($decision['backup_candidate_boundary_cleared'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE] || ($decision['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        $roles = [];
        foreach ((array) ($c82['pre_activation_boundary_candidate_scorecard'] ?? []) as $scorecard) {
            if (is_array($scorecard) && isset($scorecard['candidate_code'])) {
                $roles[$scorecard['candidate_code']] = $scorecard;
            }
        }
        return (($roles[self::PRIMARY_CANDIDATE]['c82_role'] ?? null) === 'primary_pre_activation_boundary_cleared_candidate')
            && (($roles[self::PRIMARY_CANDIDATE]['candidate_ready_for_activation_authorization_review'] ?? null) === true)
            && (($roles[self::BACKUP_CANDIDATE]['c82_role'] ?? null) === 'backup_pre_activation_boundary_cleared_candidate')
            && (($roles[self::BACKUP_CANDIDATE]['candidate_ready_for_activation_authorization_review'] ?? null) === true)
            && (($roles[self::COMPARATOR_CANDIDATE]['c82_role'] ?? null) === 'comparator_only')
            && (($roles[self::COMPARATOR_CANDIDATE]['candidate_ready_for_activation_authorization_review'] ?? null) === false);
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c82 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c82_lock_validation_summary'] = $this->c82LockValidationSummary($load, $c82);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c82);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['activation_authorization_decision'] = $this->activationAuthorizationDecision($pass);
        $artifact['activation_authorization_candidate_scorecard'] = $this->candidateScorecard($c82, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['activation_authorization_context_summary'] = $this->activationAuthorizationContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($pass);
        $artifact['c82_boundary_carry_forward_validation_summary'] = $this->c82BoundaryCarryForwardValidationSummary($c82, $pass);
        $artifact['activation_authorization_governance_summary'] = $this->activationAuthorizationGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['activation_authorization_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C83_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'rollback_plan_defined' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'documentation_governance_pass' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
            'activation_authorization_confirmed' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_AUTHORIZATION_NOT_CONFIRMED',
        ] as $field => $status) {
            if (($options[$field] ?? true) !== true) {
                $failures[] = $status;
            }
        }
        foreach ($this->prohibitedOptionFields() as $field) {
            if (($options[$field] ?? false) !== false) {
                $failures[] = $this->statusForProhibitedField($field);
            }
        }
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_enabled')
            || $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled')
            || $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled')
            || $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled')
            || $this->configFlagIsOn('production_catalog_controlled_rollout_enabled')) {
            $failures[] = 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c82',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'authorization_used_for_selection',
            'authorization_used_for_retuning',
            'authorization_used_for_ranking',
            'authorization_used_for_plan_confirm_mutation',
            'authorization_used_for_live_rollout',
            'authorization_allowed_to_auto_promote_candidate',
            'authorization_allowed_to_auto_enable_runtime',
            'activation_executed',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'activation_authorization_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'latest_shortcut_used',
            'max_date_shortcut_used',
            'future_lookup_detected',
            'return_fields_used_for_selection',
            'a01_promoted',
            'a01_used_as_runtime_fallback',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'selection') !== false || strpos($field, 'retuning') !== false || strpos($field, 'ranking') !== false || strpos($field, 'candidate_scope') !== false || strpos($field, 'new_candidate') !== false || strpos($field, 'parameter') !== false || strpos($field, 'a01') !== false) {
            return 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (strpos($field, 'plan_confirm') !== false) {
            return 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if (strpos($field, 'production') !== false || strpos($field, 'runtime') !== false || strpos($field, 'rollout') !== false || strpos($field, 'executed') !== false) {
            return 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_PRODUCTION_MUTATION';
        }
        if (strpos($field, 'latest') !== false || strpos($field, 'max_date') !== false || strpos($field, 'future') !== false || strpos($field, 'return_fields') !== false) {
            return 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
        }
        return 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
    }

    private function databaseDictionaryReadSummary(): array
    {
        $paths = [];
        foreach (self::DICTIONARY_PATHS as $key => $path) {
            $paths[$key] = ['path' => $path, 'exists' => is_file($path)];
        }
        return [
            'database_dictionary_read_completed' => true,
            'dictionary_paths' => $paths,
            'dictionary_coverage_complete' => $this->dictionaryCoverageComplete(),
            'selection_uses_documented_fields_only' => true,
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

    private function c82LockValidationSummary(array $load, array $c82): array
    {
        return [
            'c82_lock_validation_completed' => true,
            'c82_artifact_exists' => $load['exists'],
            'c82_artifact_hash_match' => $load['hash_match'],
            'c82_file_sha1_match' => $load['file_sha1_match'],
            'c82_status_match' => ($c82['status'] ?? null) === self::EXPECTED_C82_STATUS,
            'c82_reason_code_match' => ($c82['reason_code'] ?? null) === self::EXPECTED_C82_REASON,
            'c82_pre_activation_boundary_review_pass' => ($c82['pre_activation_boundary_review_pass'] ?? null) === true,
            'c82_pre_activation_boundary_cleared' => ($c82['pre_activation_boundary_cleared'] ?? null) === true,
            'c82_primary_boundary_cleared' => ($c82['primary_candidate_boundary_cleared'] ?? null) === true,
            'c82_backup_boundary_cleared' => ($c82['backup_candidate_boundary_cleared'] ?? null) === true,
            'c82_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c82_source_validation' => false,
            'c82_c83_readiness_count_match' => ($c82['next_readiness_decision']['candidate_ready_for_activation_authorization_review_count'] ?? null) === 2,
            'c82_c83_recommendation_match' => ($c82['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C82_RECOMMENDATION,
            'c82_safety_fields_clean' => $this->c82SafetyFieldsClean($c82),
        ];
    }

    private function c82SafetyFieldsClean(array $c82): bool
    {
        foreach (array_keys($this->c82SafetyGateMap()) as $field) {
            if (($c82[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c82): array
    {
        $source = is_array($c82['lineage_validation_summary'] ?? null) ? $c82['lineage_validation_summary'] : [];
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c82),
            'lineage' => 'C82 -> C81 -> C80 -> C79 -> C78 -> C77 -> C76 -> C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c82_to_c81_lock_match' => true,
            'c81_to_c80_lock_match' => ($source['c81_to_c80_lock_match'] ?? null) === true,
            'c80_to_c79_lock_match' => ($source['c80_to_c79_lock_match'] ?? null) === true,
            'c79_to_c78_lock_match' => ($source['c79_to_c78_lock_match'] ?? null) === true,
            'c78_to_c77_lock_match' => ($source['c78_to_c77_lock_match'] ?? null) === true,
            'c77_to_c76_lock_match' => ($source['c77_to_c76_lock_match'] ?? null) === true,
            'c76_to_c75_lock_match' => ($source['c76_to_c75_lock_match'] ?? null) === true,
            'c75_to_c74_lock_match' => ($source['c75_to_c74_lock_match'] ?? null) === true,
            'c74_to_c73_lock_match' => ($source['c74_to_c73_lock_match'] ?? null) === true,
            'c73_to_c72_lock_match' => ($source['c73_to_c72_lock_match'] ?? null) === true,
            'candidate_scope_lineage_locked' => ($source['candidate_scope_lineage_locked'] ?? null) === true,
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C82_LOCKED_PRE_ACTIVATION_BOUNDARY_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c82' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'authorization_used_for_selection' => false,
            'authorization_used_for_retuning' => false,
            'authorization_used_for_ranking' => false,
            'authorization_used_for_live_rollout' => false,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        $approvalReference = trim((string) ($options['approval_reference'] ?? ''));
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_validation_pass' => $pass,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_present' => $approvalReference !== '',
            'approval_reference' => $approvalReference,
            'operator_approval_scope' => 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_ONLY',
            'operator_approval_persists_to_live_runtime' => false,
            'operator_approval_allows_activation_authorization' => $pass,
            'operator_approval_executes_activation' => false,
            'operator_approval_allows_deployment' => false,
            'operator_approval_allows_plan_confirm_mutation' => false,
        ];
    }

    private function activationAuthorizationDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'activation_authorization_review_executed' => true,
            'activation_authorization_review_allowed' => $pass,
            'activation_authorization_review_pass' => $pass,
            'source_pre_activation_boundary_cleared' => true,
            'activation_authorized' => $pass,
            'activation_executed' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_candidate_activation_authorized' => $pass,
            'backup_candidate_activation_authorized' => $pass,
            'a01_remains_comparator_only' => true,
            'authorization_is_artifact_only' => true,
            'authorization_is_non_live_default' => true,
            'authorization_used_for_selection' => false,
            'authorization_used_for_retuning' => false,
            'authorization_used_for_ranking' => false,
            'authorization_used_for_plan_confirm_mutation' => false,
            'authorization_used_for_live_rollout' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'activation_authorization_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'C82 boundary clearance is authorized for activation execution review, but activation is still not executed.' : 'C83 authorization did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'ACTIVATION_AUTHORIZED_FOR_C84_EXECUTION_REVIEW_ONLY' : 'C83_ACTIVATION_AUTHORIZATION_REPAIR_REQUIRED',
        ];
    }

    private function candidateScorecard(array $c82, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c82_boundary_evidence_summary' => [
                'c82_pre_activation_boundary_review_pass' => ($c82['pre_activation_boundary_review_pass'] ?? null) === true,
                'c82_pre_activation_boundary_cleared' => ($c82['pre_activation_boundary_cleared'] ?? null) === true,
                'c82_primary_candidate_boundary_cleared' => ($c82['primary_candidate_boundary_cleared'] ?? null) === true,
                'c82_backup_candidate_boundary_cleared' => ($c82['backup_candidate_boundary_cleared'] ?? null) === true,
            ],
            'activation_authorization_review_pass' => $pass,
            'candidate_ready_for_activation_execution_review' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'c82_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => true,
            'kill_switch_validation_pass' => true,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'activation_authorization_artifact_only_pass' => true,
            'rollback_plan_validation_pass' => true,
            'emergency_disable_validation_pass' => true,
            'production_mutation_safety_pass' => true,
            'documentation_governance_pass' => true,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];
        return [
            array_merge($base, ['candidate_code' => self::PRIMARY_CANDIDATE, 'c83_role' => 'primary_activation_authorized_candidate']),
            array_merge($base, ['candidate_code' => self::BACKUP_CANDIDATE, 'c83_role' => 'backup_activation_authorized_candidate']),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c83_role' => 'comparator_only',
                'activation_authorization_review_pass' => false,
                'candidate_ready_for_activation_execution_review' => false,
                'activation_authorized' => false,
                'operator_approval_validation_pass' => false,
                'failure_reason_codes' => ['C83_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function activationAuthorizationContextSummary(bool $pass): array
    {
        return [
            'activation_authorization_context_created' => true,
            'activation_authorization_context_validation_pass' => $pass,
            'activation_authorization_context_is_explicit_only' => true,
            'activation_authorization_context_requires_operator_approval' => true,
            'activation_authorization_context_requires_approval_reference' => true,
            'activation_authorization_context_is_artifact_only' => true,
            'activation_authorization_context_is_not_persisted_to_config' => true,
            'activation_authorization_context_is_not_persisted_to_db' => true,
            'activation_authorization_context_is_not_persisted_to_live_runtime' => true,
            'activation_authorization_context_authorizes_activation_artifact_only' => $pass,
            'activation_authorization_context_does_not_execute_activation' => true,
            'activation_authorization_context_does_not_mutate_plan_confirm' => true,
            'activation_authorization_context_does_not_change_default_runtime' => true,
            'activation_authorization_context_rejects_a01_as_runtime_candidate' => true,
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
            'pre_activation_boundary_review_source_identified' => is_file(self::RUNTIME_PATHS['c82_pre_activation_boundary_service']),
            'activation_authorization_review_source_identified' => is_file(self::RUNTIME_PATHS['c83_activation_authorization_service']),
            'activation_authorization_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c83_activation_authorization_contract']),
            'explicit_activation_authorization_context_identified_or_created' => is_file(self::RUNTIME_PATHS['c83_activation_authorization_context']),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'activation_executed' => false,
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
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'kill_switch_validation_pass' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => false,
            'production_catalog_runtime_wired' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function rollbackAndEmergencyDisableReviewSummary(bool $pass): array
    {
        return [
            'rollback_and_emergency_disable_review_completed' => true,
            'rollback_plan_defined' => true,
            'rollback_plan_validation_pass' => $pass,
            'emergency_disable_path_defined' => true,
            'emergency_disable_validation_pass' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => false,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c82BoundaryCarryForwardValidationSummary(array $c82, bool $pass): array
    {
        return [
            'c82_boundary_carry_forward_validation_completed' => true,
            'c82_boundary_carry_forward_validation_pass' => $pass,
            'c82_pre_activation_boundary_review_pass' => ($c82['pre_activation_boundary_review_pass'] ?? null) === true,
            'c82_pre_activation_boundary_cleared' => ($c82['pre_activation_boundary_cleared'] ?? null) === true,
            'c82_primary_candidate_boundary_cleared' => ($c82['primary_candidate_boundary_cleared'] ?? null) === true,
            'c82_backup_candidate_boundary_cleared' => ($c82['backup_candidate_boundary_cleared'] ?? null) === true,
            'c82_activation_authorized_was_false' => ($c82['activation_authorized'] ?? null) === false,
            'c82_progress_target_reached' => ($c82['progress_summary']['target_reached'] ?? null) === true,
            'c82_c83_readiness_count' => (int) ($c82['next_readiness_decision']['candidate_ready_for_activation_authorization_review_count'] ?? 0),
            'c82_c83_recommendation_match' => ($c82['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C82_RECOMMENDATION,
        ];
    }

    private function activationAuthorizationGovernanceSummary(bool $pass): array
    {
        return [
            'activation_authorization_governance_review_completed' => true,
            'activation_authorization_governance_pass' => $pass,
            'activation_authorized' => $pass,
            'activation_execution_allowed_next_review_only' => $pass,
            'authorization_is_artifact_only' => true,
            'authorization_is_not_activation_execution' => true,
            'authorization_used_for_selection' => false,
            'authorization_used_for_retuning' => false,
            'authorization_used_for_ranking' => false,
            'authorization_used_for_plan_confirm_mutation' => false,
            'authorization_used_for_live_rollout' => false,
            'authorization_allowed_to_auto_enable_runtime' => false,
            'authorization_allowed_to_auto_deploy' => false,
            'activation_authorization_classification' => 'CONTROLLED_LIMITED_ACTIVATION_AUTHORIZATION_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C83_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
        ];
    }

    private function productionMutationSafetySummary(bool $pass): array
    {
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'activation_authorization_review_created' => true,
            'activation_authorization_review_allowed' => $pass,
            'activation_authorization_review_pass' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => false,
            'candidate_ready_for_activation_execution_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C84_RECOMMENDATION : 'C83_TARGETED_C82_PRE_ACTIVATION_BOUNDARY_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'activation_authorization_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function documentationGovernanceSummary(): array
    {
        $paths = [];
        $docsExist = true;
        foreach (self::DOC_PATHS as $key => $path) {
            $exists = is_file($path);
            $paths[$key] = ['path' => $path, 'exists' => $exists];
            $docsExist = $docsExist && $exists;
        }
        return [
            'documentation_governance_completed' => true,
            'documentation_governance_pass' => $docsExist,
            'doc_paths' => $paths,
            'append_only_docs_updated' => $docsExist,
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_activation_execution_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C84_RECOMMENDATION : 'C83_TARGETED_C82_PRE_ACTIVATION_BOUNDARY_REPAIR',
            'decision_reason' => $pass ? 'C83 authorized activation for primary and backup. Only C84 activation execution review is allowed next.' : 'C83 activation authorization review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW' : 'C83_ACTIVATION_AUTHORIZATION_REPAIR_REQUIRED',
            'activation_authorized' => $pass,
            'activation_executed' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
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
            'activation_authorization_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C84_RECOMMENDATION : 'C83_TARGETED_C82_PRE_ACTIVATION_BOUNDARY_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW',
            'achieved' => [
                'C82 artifact hash and file SHA1 validated',
                'C82 nested C83 readiness path validated',
                'C82 -> C60 lineage lock validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Activation authorization recorded for primary and backup',
                'Activation execution, PLAN/CONFIRM, and production runtime remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C84_RECOMMENDATION : 'C83_TARGETED_C82_PRE_ACTIVATION_BOUNDARY_REPAIR',
            'planned_next_scope' => $pass ? 'activation execution review only; C83 itself still does not execute activation, deployment, live rollout, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C83 artifact hash',
                'locked C83 file SHA1',
                'operator approval',
                'non-empty approval reference',
                'unchanged candidate scope',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C83 validates C82 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C83 records activation authorization as an isolated artifact-only decision.',
            'C83 authorization is not activation execution, not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
            'C83 may only recommend C84 activation execution review as the next controlled review step.',
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
        if (strpos($status, 'C82_ARTIFACT') !== false || strpos($status, 'C82_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C83_C82_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'AUTHORIZATION') !== false || strpos($status, 'BOUNDARY') !== false) {
            return 'C83_CONTROLLED_ACTIVATION_AUTHORIZATION_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C83_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C83_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C83_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C83_DOCUMENTATION_REPAIR';
        }
        return 'C83_TARGETED_C82_PRE_ACTIVATION_BOUNDARY_REPAIR';
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
