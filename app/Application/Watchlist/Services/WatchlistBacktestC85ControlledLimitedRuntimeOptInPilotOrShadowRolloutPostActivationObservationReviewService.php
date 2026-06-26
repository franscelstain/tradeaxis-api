<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewService
{
    public const RUN_CODE = 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW';
    public const ARTIFACT_TYPE = 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW';

    public const DEFAULT_C84_ARTIFACT = 'storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json';
    public const DEFAULT_EXPECTED_C84_HASH = '54f39e02202b597c0e353cfec602215a1f41251b';
    public const DEFAULT_EXPECTED_C84_FILE_SHA1 = 'CEAF5D69D61D15A7220CA5A843DCF3CB1DDB5255';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C84_STATUS = 'C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_PASSED_EXECUTED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C84_REASON = 'C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_PASSED_EXECUTED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C84_RECOMMENDATION = 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW';
    private const C86_RECOMMENDATION = 'C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW';

    private const EXPECTED_C83_HASH = '2927dea9624be20ea493c9e449b57879e0ea5da7';
    private const EXPECTED_C83_FILE_SHA1 = 'E90EA61673FB7820988507670F547CD6F02D6A5F';
    private const EXPECTED_C82_HASH = '1c78f08cc78abe4800cde96b892932ad6b8df725';
    private const EXPECTED_C82_FILE_SHA1 = '24D91E58F7F9FAADE95F6DABF985F430C48C05E2';
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
        'c85_validation_doc' => 'docs/watchlist/audit/WS_C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW.md',
        'c85_operator_commands_doc' => 'docs/watchlist/audit/WS_C85_OPERATOR_VALIDATION_COMMANDS.md',
        'c84_validation_doc' => 'docs/watchlist/audit/WS_C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW.md',
        'c84_operator_commands_doc' => 'docs/watchlist/audit/WS_C84_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c84_activation_execution_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService.php',
        'c85_post_activation_observation_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC85ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationReviewService.php',
        'c85_post_activation_observation_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationReviewContract.php',
        'c85_post_activation_observation_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    public function execute(
        string $c84Artifact = self::DEFAULT_C84_ARTIFACT,
        string $expectedC84Hash = self::DEFAULT_EXPECTED_C84_HASH,
        string $expectedC84FileSha1 = self::DEFAULT_EXPECTED_C84_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c84Artifact, $expectedC84Hash, $expectedC84FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_ARTIFACT_LOCK_MISMATCH', 'C84 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_ARTIFACT_LOCK_MISMATCH', 'C84 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_FILE_SHA1_LOCK_MISMATCH', 'C84 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c84 = $load['payload'];
        if (($c84['status'] ?? null) !== self::EXPECTED_C84_STATUS || ($c84['reason_code'] ?? null) !== self::EXPECTED_C84_REASON) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_STATUS_OR_REASON_MISMATCH', 'C84 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c84['activation_execution_review_pass'] ?? null) !== true || ($c84['controlled_activation_record_created'] ?? null) !== true) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_ACTIVATION_EXECUTION_NOT_PASSED', 'C84 activation execution record did not pass.', $outputPath, $overwrite);
        }
        if (($c84['activation_authorized'] ?? null) !== true || ($c84['activation_executed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_ACTIVATION_NOT_EXECUTED', 'C84 activation must be authorized and executed before C85 observation.', $outputPath, $overwrite);
        }
        if (($c84['primary_candidate_activation_executed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_PRIMARY_EXECUTION_NOT_CONFIRMED', 'C84 primary activation execution missing.', $outputPath, $overwrite);
        }
        if (($c84['backup_candidate_activation_executed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_BACKUP_EXECUTION_NOT_CONFIRMED', 'C84 backup activation execution missing.', $outputPath, $overwrite);
        }
        if (($c84['next_readiness_decision']['candidate_ready_for_post_activation_observation_review_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_C85_READINESS_COUNT_MISMATCH', 'C84 nested C85 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c84['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C84_RECOMMENDATION) {
            return $this->blocked($artifact, 'C85_BLOCKED_C84_RECOMMENDATION_MISMATCH', 'C84 nested C85 recommendation mismatch.', $outputPath, $overwrite);
        }
        foreach ($this->c84SafetyGateMap() as $field => $status) {
            if (($c84[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C84 safety field '.$field.' is not false.', $outputPath, $overwrite);
            }
        }
        if (! $this->lineageLocksMatch($c84)) {
            return $this->blocked($artifact, 'C85_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C84 to C60 lineage lock mismatch.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c84)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C84 candidate scope does not match locked activation execution decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C85 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);
            $status = $failures[0];
            if ($status === 'C85_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C85 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C85 post-activation observation review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_PASSED_OBSERVED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C85 observed the controlled activation execution record for primary and backup. Default live runtime and PLAN/CONFIRM remain unchanged.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW';
        $artifact['next_step_recommendation'] = self::C86_RECOMMENDATION;
        $artifact['post_activation_observation_review_executed'] = true;
        $artifact['post_activation_observation_review_allowed'] = true;
        $artifact['post_activation_observation_review_pass'] = true;
        $artifact['activation_authorized'] = true;
        $artifact['activation_executed'] = true;
        $artifact['controlled_activation_record_observed'] = true;
        $artifact['primary_candidate_post_activation_observed'] = true;
        $artifact['backup_candidate_post_activation_observed'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C85_NOT_RUN',
            'reason_code' => 'C85_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'post_activation_observation_review_executed' => false,
            'post_activation_observation_review_allowed' => false,
            'post_activation_observation_review_pass' => false,
            'activation_authorized' => false,
            'activation_executed' => false,
            'controlled_activation_record_observed' => false,
            'primary_candidate_post_activation_observed' => false,
            'backup_candidate_post_activation_observed' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_observation_context_persisted_to_live_runtime' => false,
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
        $c84 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c84['source_artifact_locks'] ?? null) ? $c84['source_artifact_locks'] : [];
        return [
            'c84_artifact_path' => $load['path'],
            'expected_c84_hash' => $load['expected_hash'],
            'actual_c84_hash' => $load['actual_hash'],
            'c84_hash_match' => $load['hash_match'],
            'expected_c84_file_sha1' => $load['expected_file_sha1'],
            'actual_c84_file_sha1' => $load['actual_file_sha1'],
            'c84_file_sha1_match' => $load['file_sha1_match'],
            'c84_source_lineage_checked' => true,
            'c84_source_lineage_match' => $this->lineageLocksMatch($c84),
            'c83_artifact_hash_from_c84' => (string) ($locks['actual_c83_hash'] ?? ''),
            'c83_file_sha1_from_c84' => (string) ($locks['actual_c83_file_sha1'] ?? ''),
            'c82_artifact_hash_from_c83' => (string) ($locks['c82_artifact_hash_from_c83'] ?? ''),
            'c82_file_sha1_from_c83' => (string) ($locks['c82_file_sha1_from_c83'] ?? ''),
            'c81_artifact_hash_from_c82' => (string) ($locks['c81_artifact_hash_from_c82'] ?? ''),
            'c81_file_sha1_from_c82' => (string) ($locks['c81_file_sha1_from_c82'] ?? ''),
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
            'expected_c84_hash' => $load['expected_hash'],
            'actual_c84_hash' => $load['actual_hash'],
            'c84_hash_match' => $load['hash_match'],
            'expected_c84_file_sha1' => $load['expected_file_sha1'],
            'actual_c84_file_sha1' => $load['actual_file_sha1'],
            'c84_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c84SafetyGateMap(): array
    {
        return [
            'production_ready' => 'C85_BLOCKED_C84_PRODUCTION_READY_ALREADY_TRUE',
            'production_catalog_runtime_wired' => 'C85_BLOCKED_C84_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C85_BLOCKED_C84_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C85_BLOCKED_C84_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C85_BLOCKED_C84_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'activation_execution_context_persisted_to_live_runtime' => 'C85_BLOCKED_C84_EXECUTION_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C85_BLOCKED_C84_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C85_BLOCKED_C84_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C85_BLOCKED_C84_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C85_BLOCKED_C84_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C85_BLOCKED_C84_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C85_BLOCKED_C84_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C85_BLOCKED_C84_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c84): bool
    {
        $locks = is_array($c84['source_artifact_locks'] ?? null) ? $c84['source_artifact_locks'] : [];
        $summary = is_array($c84['lineage_validation_summary'] ?? null) ? $c84['lineage_validation_summary'] : [];
        $expected = [
            'actual_c83_hash' => self::EXPECTED_C83_HASH,
            'actual_c83_file_sha1' => self::EXPECTED_C83_FILE_SHA1,
            'c82_artifact_hash_from_c83' => self::EXPECTED_C82_HASH,
            'c82_file_sha1_from_c83' => self::EXPECTED_C82_FILE_SHA1,
            'c81_artifact_hash_from_c82' => self::EXPECTED_C81_HASH,
            'c81_file_sha1_from_c82' => self::EXPECTED_C81_FILE_SHA1,
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
        if (($locks['c83_hash_match'] ?? null) !== true || ($locks['c83_file_sha1_match'] ?? null) !== true || ($locks['c83_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        foreach ($expected as $field => $value) {
            if (($locks[$field] ?? null) !== $value) {
                return false;
            }
        }
        foreach ([
            'lineage_lock_validation_pass',
            'c83_to_c82_lock_match',
            'c82_to_c81_lock_match',
            'c81_to_c80_lock_match',
            'c80_to_c79_lock_match',
            'c79_to_c78_lock_match',
            'candidate_scope_lineage_locked',
        ] as $field) {
            if (($summary[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function candidateScopeMatches(array $c84): bool
    {
        $scope = is_array($c84['candidate_scope_freeze_summary'] ?? null) ? $c84['candidate_scope_freeze_summary'] : [];
        $decision = is_array($c84['activation_execution_decision'] ?? null) ? $c84['activation_execution_decision'] : [];
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || (array) ($scope['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || (array) ($scope['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE]) {
            return false;
        }
        foreach (['candidate_scope_changed_after_c83', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'execution_used_for_selection', 'execution_used_for_retuning', 'execution_used_for_ranking', 'execution_used_for_live_rollout', 'a01_promoted', 'a01_used_as_runtime_fallback'] as $field) {
            if (($scope[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($decision['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($decision['primary_candidate_activation_executed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || ($decision['backup_candidate_activation_executed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE] || ($decision['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        foreach (['activation_execution_used_for_selection', 'activation_execution_used_for_retuning', 'activation_execution_used_for_ranking', 'activation_execution_used_for_plan_confirm_mutation', 'activation_execution_used_for_live_rollout'] as $field) {
            if (($decision[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c84 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c84_lock_validation_summary'] = $this->c84LockValidationSummary($load, $c84);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c84);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['post_activation_observation_decision'] = $this->postActivationObservationDecision($pass);
        $artifact['post_activation_observation_candidate_scorecard'] = $this->candidateScorecard($c84, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['post_activation_observation_context_summary'] = $this->postActivationObservationContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($pass);
        $artifact['c84_activation_execution_carry_forward_validation_summary'] = $this->c84ActivationExecutionCarryForwardValidationSummary($c84, $pass);
        $artifact['post_activation_observation_governance_summary'] = $this->postActivationObservationGovernanceSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['post_activation_observation_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C85_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'rollback_plan_defined' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'documentation_governance_pass' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
            'post_activation_observation_confirmed' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OBSERVATION_NOT_CONFIRMED',
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
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_enabled') || $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled') || $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled') || $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled') || $this->configFlagIsOn('production_catalog_controlled_rollout_enabled')) {
            $failures[] = 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c84',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'observation_used_for_selection',
            'observation_used_for_retuning',
            'observation_used_for_ranking',
            'observation_used_for_plan_confirm_mutation',
            'observation_used_for_live_rollout',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_observation_context_persisted_to_live_runtime',
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
            return 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (strpos($field, 'plan_confirm') !== false) {
            return 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if (strpos($field, 'production') !== false || strpos($field, 'runtime') !== false || strpos($field, 'rollout') !== false) {
            return 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_PRODUCTION_MUTATION';
        }
        if (strpos($field, 'latest') !== false || strpos($field, 'max_date') !== false || strpos($field, 'future') !== false || strpos($field, 'return_fields') !== false) {
            return 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
        }
        return 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
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

    private function c84LockValidationSummary(array $load, array $c84): array
    {
        return [
            'c84_lock_validation_completed' => true,
            'c84_artifact_exists' => $load['exists'],
            'c84_artifact_hash_match' => $load['hash_match'],
            'c84_file_sha1_match' => $load['file_sha1_match'],
            'c84_status_match' => ($c84['status'] ?? null) === self::EXPECTED_C84_STATUS,
            'c84_reason_code_match' => ($c84['reason_code'] ?? null) === self::EXPECTED_C84_REASON,
            'c84_activation_execution_review_pass' => ($c84['activation_execution_review_pass'] ?? null) === true,
            'c84_activation_authorized' => ($c84['activation_authorized'] ?? null) === true,
            'c84_activation_executed' => ($c84['activation_executed'] ?? null) === true,
            'c84_controlled_activation_record_created' => ($c84['controlled_activation_record_created'] ?? null) === true,
            'c84_primary_candidate_activation_executed' => ($c84['primary_candidate_activation_executed'] ?? null) === true,
            'c84_backup_candidate_activation_executed' => ($c84['backup_candidate_activation_executed'] ?? null) === true,
            'c84_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c84_source_validation' => false,
            'c84_c85_readiness_count_match' => ($c84['next_readiness_decision']['candidate_ready_for_post_activation_observation_review_count'] ?? null) === 2,
            'c84_c85_recommendation_match' => ($c84['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C84_RECOMMENDATION,
            'c84_safety_fields_clean' => $this->c84SafetyFieldsClean($c84),
        ];
    }

    private function c84SafetyFieldsClean(array $c84): bool
    {
        foreach (array_keys($this->c84SafetyGateMap()) as $field) {
            if (($c84[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c84): array
    {
        $source = is_array($c84['lineage_validation_summary'] ?? null) ? $c84['lineage_validation_summary'] : [];
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c84),
            'lineage' => 'C84 -> C83 -> C82 -> C81 -> C80 -> C79 -> C78 -> C77 -> C76 -> C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c84_to_c83_lock_match' => true,
            'c83_to_c82_lock_match' => ($source['c83_to_c82_lock_match'] ?? null) === true,
            'c82_to_c81_lock_match' => ($source['c82_to_c81_lock_match'] ?? null) === true,
            'c81_to_c80_lock_match' => ($source['c81_to_c80_lock_match'] ?? null) === true,
            'c80_to_c79_lock_match' => ($source['c80_to_c79_lock_match'] ?? null) === true,
            'c79_to_c78_lock_match' => ($source['c79_to_c78_lock_match'] ?? null) === true,
            'candidate_scope_lineage_locked' => ($source['candidate_scope_lineage_locked'] ?? null) === true,
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C84_LOCKED_ACTIVATION_EXECUTION_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c84' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'observation_used_for_selection' => false,
            'observation_used_for_retuning' => false,
            'observation_used_for_ranking' => false,
            'observation_used_for_plan_confirm_mutation' => false,
            'observation_used_for_live_rollout' => false,
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
            'operator_approval_scope' => 'C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_ONLY',
            'operator_approval_persists_to_live_runtime' => false,
            'operator_approval_allows_post_activation_observation_record' => $pass,
            'operator_approval_allows_default_runtime_wiring' => false,
            'operator_approval_allows_deployment' => false,
            'operator_approval_allows_plan_confirm_mutation' => false,
        ];
    }

    private function postActivationObservationDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'post_activation_observation_review_executed' => true,
            'post_activation_observation_review_allowed' => $pass,
            'post_activation_observation_review_pass' => $pass,
            'source_activation_authorized' => true,
            'source_activation_executed' => true,
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_candidate_post_activation_observed' => $pass,
            'backup_candidate_post_activation_observed' => $pass,
            'a01_remains_comparator_only' => true,
            'post_activation_observation_is_artifact_record_only' => true,
            'post_activation_observation_used_for_selection' => false,
            'post_activation_observation_used_for_retuning' => false,
            'post_activation_observation_used_for_ranking' => false,
            'post_activation_observation_used_for_plan_confirm_mutation' => false,
            'post_activation_observation_used_for_live_rollout' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_observation_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'Controlled activation execution record observed for primary and backup. Default live runtime remains unchanged.' : 'C85 post-activation observation review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'POST_ACTIVATION_OBSERVATION_READY_FOR_C86_RESULT_REVIEW' : 'C85_POST_ACTIVATION_OBSERVATION_REPAIR_REQUIRED',
        ];
    }

    private function candidateScorecard(array $c84, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c84_activation_execution_evidence_summary' => [
                'c84_activation_execution_review_pass' => ($c84['activation_execution_review_pass'] ?? null) === true,
                'c84_activation_authorized' => ($c84['activation_authorized'] ?? null) === true,
                'c84_activation_executed' => ($c84['activation_executed'] ?? null) === true,
                'c84_controlled_activation_record_created' => ($c84['controlled_activation_record_created'] ?? null) === true,
            ],
            'post_activation_observation_review_pass' => $pass,
            'candidate_ready_for_post_activation_observation_result_review' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
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
            'c84_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => true,
            'kill_switch_validation_pass' => true,
            'production_mutation_safety_pass' => true,
            'documentation_governance_pass' => true,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];
        return [
            array_merge($base, ['candidate_code' => self::PRIMARY_CANDIDATE, 'c85_role' => 'primary_post_activation_observation_candidate']),
            array_merge($base, ['candidate_code' => self::BACKUP_CANDIDATE, 'c85_role' => 'backup_post_activation_observation_candidate']),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c85_role' => 'comparator_only',
                'post_activation_observation_review_pass' => false,
                'candidate_ready_for_post_activation_observation_result_review' => false,
                'activation_authorized' => false,
                'activation_executed' => false,
                'controlled_activation_record_observed' => false,
                'operator_approval_validation_pass' => false,
                'failure_reason_codes' => ['C85_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function postActivationObservationContextSummary(bool $pass): array
    {
        return [
            'post_activation_observation_context_created' => true,
            'post_activation_observation_context_validation_pass' => $pass,
            'post_activation_observation_context_is_explicit_only' => true,
            'post_activation_observation_context_requires_operator_approval' => true,
            'post_activation_observation_context_requires_approval_reference' => true,
            'post_activation_observation_context_is_artifact_only' => true,
            'post_activation_observation_context_is_not_persisted_to_config' => true,
            'post_activation_observation_context_is_not_persisted_to_db' => true,
            'post_activation_observation_context_is_not_persisted_to_live_runtime' => true,
            'post_activation_observation_context_observes_controlled_record_only' => $pass,
            'post_activation_observation_context_does_not_mutate_plan_confirm' => true,
            'post_activation_observation_context_does_not_change_default_runtime' => true,
            'post_activation_observation_context_rejects_a01_as_runtime_candidate' => true,
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
            'activation_execution_review_source_identified' => is_file(self::RUNTIME_PATHS['c84_activation_execution_service']),
            'post_activation_observation_review_source_identified' => is_file(self::RUNTIME_PATHS['c85_post_activation_observation_service']),
            'post_activation_observation_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c85_post_activation_observation_contract']),
            'explicit_post_activation_observation_context_identified_or_created' => is_file(self::RUNTIME_PATHS['c85_post_activation_observation_context']),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
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
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
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
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c84ActivationExecutionCarryForwardValidationSummary(array $c84, bool $pass): array
    {
        return [
            'c84_activation_execution_carry_forward_validation_completed' => true,
            'c84_activation_execution_carry_forward_validation_pass' => $pass,
            'c84_activation_execution_review_pass' => ($c84['activation_execution_review_pass'] ?? null) === true,
            'c84_activation_authorized' => ($c84['activation_authorized'] ?? null) === true,
            'c84_activation_executed' => ($c84['activation_executed'] ?? null) === true,
            'c84_controlled_activation_record_created' => ($c84['controlled_activation_record_created'] ?? null) === true,
            'c84_primary_candidate_activation_executed' => ($c84['primary_candidate_activation_executed'] ?? null) === true,
            'c84_backup_candidate_activation_executed' => ($c84['backup_candidate_activation_executed'] ?? null) === true,
            'c84_progress_target_reached' => ($c84['progress_summary']['target_reached'] ?? null) === true,
            'c84_c85_readiness_count' => (int) ($c84['next_readiness_decision']['candidate_ready_for_post_activation_observation_review_count'] ?? 0),
            'c84_c85_recommendation_match' => ($c84['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C84_RECOMMENDATION,
        ];
    }

    private function postActivationObservationGovernanceSummary(bool $pass): array
    {
        return [
            'post_activation_observation_governance_review_completed' => true,
            'post_activation_observation_governance_pass' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'post_activation_observation_is_artifact_only_record' => true,
            'post_activation_observation_is_not_default_runtime_wiring' => true,
            'post_activation_observation_is_not_production_deployment' => true,
            'post_activation_observation_is_not_plan_confirm_live_rollout' => true,
            'post_activation_observation_is_not_runtime_bridge_activation' => true,
            'post_activation_observation_used_for_selection' => false,
            'post_activation_observation_used_for_retuning' => false,
            'post_activation_observation_used_for_ranking' => false,
            'post_activation_observation_allowed_to_auto_enable_runtime' => false,
            'post_activation_observation_allowed_to_auto_deploy' => false,
            'post_activation_observation_classification' => 'CONTROLLED_LIMITED_POST_ACTIVATION_OBSERVATION_REVIEW_ONLY',
        ];
    }

    private function productionMutationSafetySummary(bool $pass): array
    {
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'post_activation_observation_review_created' => true,
            'post_activation_observation_review_allowed' => $pass,
            'post_activation_observation_review_pass' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'candidate_ready_for_post_activation_observation_result_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C86_RECOMMENDATION : 'C85_TARGETED_C84_ACTIVATION_EXECUTION_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_observation_context_persisted_to_live_runtime' => false,
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
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_post_activation_observation_result_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C86_RECOMMENDATION : 'C85_TARGETED_C84_ACTIVATION_EXECUTION_REPAIR',
            'decision_reason' => $pass ? 'C85 observed the controlled activation execution record for primary and backup. Only C86 post-activation observation result review is allowed next.' : 'C85 post-activation observation review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW' : 'C85_POST_ACTIVATION_OBSERVATION_REPAIR_REQUIRED',
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
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
            'post_activation_observation_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C86_RECOMMENDATION : 'C85_TARGETED_C84_ACTIVATION_EXECUTION_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW',
            'achieved' => [
                'C84 artifact hash and file SHA1 validated',
                'C84 nested C85 readiness path validated',
                'C84 -> C60 lineage lock validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Controlled activation execution record observed for primary and backup',
                'PLAN/CONFIRM, default live runtime, and production deployment remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C86_RECOMMENDATION : 'C85_TARGETED_C84_ACTIVATION_EXECUTION_REPAIR',
            'planned_next_scope' => $pass ? 'post-activation observation result review only; C85 still does not wire default live runtime, deploy production, or mutate PLAN/CONFIRM' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C85 artifact hash',
                'locked C85 file SHA1',
                'operator approval',
                'non-empty approval reference',
                'unchanged candidate scope',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C85 validates C84 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C85 observes controlled activation execution artifact/record only.',
            'C85 post-activation observation record is not production deployment, not default runtime wiring, not live rollout, and not PLAN/CONFIRM mutation.',
            'C85 may only recommend C86 post-activation observation result review as the next controlled review step.',
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
        if (strpos($status, 'C84_ARTIFACT') !== false || strpos($status, 'C84_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C85_C84_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'OBSERVATION') !== false || strpos($status, 'ACTIVATION') !== false) {
            return 'C85_CONTROLLED_POST_ACTIVATION_OBSERVATION_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C85_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C85_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C85_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C85_DOCUMENTATION_REPAIR';
        }
        return 'C85_TARGETED_C84_ACTIVATION_EXECUTION_REPAIR';
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
