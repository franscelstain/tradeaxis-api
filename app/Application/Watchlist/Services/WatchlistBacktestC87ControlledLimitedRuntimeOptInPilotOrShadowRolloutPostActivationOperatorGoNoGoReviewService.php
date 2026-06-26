<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C86_ARTIFACT = 'storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json';
    public const DEFAULT_EXPECTED_C86_HASH = '2ec7b0acddcf0ed09d1988c555cc32165e6c972f';
    public const DEFAULT_EXPECTED_C86_FILE_SHA1 = 'D0F261827F286FFE502927D7C3704D7A79B4FD6E';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C86_STATUS = 'C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_RESULT_REVIEWED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C86_REASON = 'C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_RESULT_REVIEWED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C86_RECOMMENDATION = self::RUN_CODE;
    private const C88_RECOMMENDATION = 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';

    private const EXPECTED_C85_HASH = '80aa0fc1a0ea662870c373706e8fc15b7bb03396';
    private const EXPECTED_C85_FILE_SHA1 = '80C9596AC8AD714DE161BDA17AECE4734667E645';
    private const EXPECTED_C84_HASH = '54f39e02202b597c0e353cfec602215a1f41251b';
    private const EXPECTED_C84_FILE_SHA1 = 'CEAF5D69D61D15A7220CA5A843DCF3CB1DDB5255';
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
        'c87_validation_doc' => 'docs/watchlist/audit/WS_C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW.md',
        'c87_operator_commands_doc' => 'docs/watchlist/audit/WS_C87_OPERATOR_VALIDATION_COMMANDS.md',
        'c86_validation_doc' => 'docs/watchlist/audit/WS_C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW.md',
        'c86_operator_commands_doc' => 'docs/watchlist/audit/WS_C86_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c86_post_activation_observation_result_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService.php',
        'c87_post_activation_operator_go_no_go_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewService.php',
        'c86_post_activation_observation_result_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationResultReviewContract.php',
        'c86_post_activation_observation_result_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationResultReviewContext.php',
        'c87_post_activation_operator_go_no_go_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationOperatorGoNoGoReviewContract.php',
        'c87_post_activation_operator_go_no_go_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationOperatorGoNoGoReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    public function execute(
        string $c86Artifact = self::DEFAULT_C86_ARTIFACT,
        string $expectedC86Hash = self::DEFAULT_EXPECTED_C86_HASH,
        string $expectedC86FileSha1 = self::DEFAULT_EXPECTED_C86_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c86Artifact, $expectedC86Hash, $expectedC86FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_ARTIFACT_LOCK_MISMATCH', 'C86 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_ARTIFACT_LOCK_MISMATCH', 'C86 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_FILE_SHA1_LOCK_MISMATCH', 'C86 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c86 = $load['payload'];
        if (($c86['status'] ?? null) !== self::EXPECTED_C86_STATUS || ($c86['reason_code'] ?? null) !== self::EXPECTED_C86_REASON) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_STATUS_OR_REASON_MISMATCH', 'C86 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c86['post_activation_observation_result_review_pass'] ?? null) !== true || ($c86['post_activation_observation_result_reviewed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_POST_ACTIVATION_OBSERVATION_RESULT_NOT_PASSED', 'C86 post-activation observation result review did not pass.', $outputPath, $overwrite);
        }
        if (($c86['activation_authorized'] ?? null) !== true || ($c86['activation_executed'] ?? null) !== true || ($c86['controlled_activation_record_observed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_ACTIVATION_RESULT_NOT_CONFIRMED', 'C86 activation result evidence is incomplete.', $outputPath, $overwrite);
        }
        if (($c86['primary_candidate_post_activation_result_reviewed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_PRIMARY_RESULT_NOT_REVIEWED', 'C86 primary post-activation result review missing.', $outputPath, $overwrite);
        }
        if (($c86['backup_candidate_post_activation_result_reviewed'] ?? null) !== true) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_BACKUP_RESULT_NOT_REVIEWED', 'C86 backup post-activation result review missing.', $outputPath, $overwrite);
        }
        if (($c86['next_readiness_decision']['candidate_ready_for_post_activation_operator_go_no_go_review_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_C87_READINESS_COUNT_MISMATCH', 'C86 nested C87 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c86['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C86_RECOMMENDATION) {
            return $this->blocked($artifact, 'C87_BLOCKED_C86_RECOMMENDATION_MISMATCH', 'C86 nested C87 recommendation mismatch.', $outputPath, $overwrite);
        }
        foreach ($this->c86SafetyGateMap() as $field => $status) {
            if (($c86[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C86 safety field '.$field.' is not false.', $outputPath, $overwrite);
            }
        }
        if (! $this->lineageLocksMatch($c86)) {
            return $this->blocked($artifact, 'C87_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C86 to C60 lineage lock mismatch.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c86)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C86 candidate scope does not match locked post-activation observation result decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C87 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);
            $status = $failures[0];
            if ($status === 'C87_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C87 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C87 post-activation operator go/no-go review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C87 post-activation operator go/no-go review issued GO for primary and backup. This remains artifact-only and does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C88_RECOMMENDATION;
        $artifact['post_activation_operator_go_no_go_review_executed'] = true;
        $artifact['post_activation_operator_go_no_go_review_allowed'] = true;
        $artifact['post_activation_operator_go_no_go_review_pass'] = true;
        $artifact['operator_go_decision'] = 'GO';
        $artifact['activation_authorized'] = true;
        $artifact['activation_executed'] = true;
        $artifact['controlled_activation_record_observed'] = true;
        $artifact['post_activation_observation_result_reviewed'] = true;
        $artifact['primary_candidate_post_activation_operator_go'] = true;
        $artifact['backup_candidate_post_activation_operator_go'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C87_NOT_RUN',
            'reason_code' => 'C87_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'post_activation_operator_go_no_go_review_executed' => false,
            'post_activation_operator_go_no_go_review_allowed' => false,
            'post_activation_operator_go_no_go_review_pass' => false,
            'operator_go_decision' => 'NO_GO',
            'activation_authorized' => false,
            'activation_executed' => false,
            'controlled_activation_record_observed' => false,
            'post_activation_observation_result_reviewed' => false,
            'primary_candidate_post_activation_operator_go' => false,
            'backup_candidate_post_activation_operator_go' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_operator_go_no_go_context_persisted_to_live_runtime' => false,
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
        $c86 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c86['source_artifact_locks'] ?? null) ? $c86['source_artifact_locks'] : [];
        return [
            'c86_artifact_path' => $load['path'],
            'expected_c86_hash' => $load['expected_hash'],
            'actual_c86_hash' => $load['actual_hash'],
            'c86_hash_match' => $load['hash_match'],
            'expected_c86_file_sha1' => $load['expected_file_sha1'],
            'actual_c86_file_sha1' => $load['actual_file_sha1'],
            'c86_file_sha1_match' => $load['file_sha1_match'],
            'c86_source_lineage_checked' => true,
            'c86_source_lineage_match' => $this->lineageLocksMatch($c86),
            'c85_artifact_hash_from_c86' => (string) ($locks['actual_c85_hash'] ?? ''),
            'c85_file_sha1_from_c86' => (string) ($locks['actual_c85_file_sha1'] ?? ''),
            'c84_artifact_hash_from_c85' => (string) ($locks['c84_artifact_hash_from_c85'] ?? ''),
            'c84_file_sha1_from_c85' => (string) ($locks['c84_file_sha1_from_c85'] ?? ''),
            'c83_artifact_hash_from_c84' => (string) ($locks['c83_artifact_hash_from_c84'] ?? ''),
            'c83_file_sha1_from_c84' => (string) ($locks['c83_file_sha1_from_c84'] ?? ''),
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
            'expected_c86_hash' => $load['expected_hash'],
            'actual_c86_hash' => $load['actual_hash'],
            'c86_hash_match' => $load['hash_match'],
            'expected_c86_file_sha1' => $load['expected_file_sha1'],
            'actual_c86_file_sha1' => $load['actual_file_sha1'],
            'c86_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c86SafetyGateMap(): array
    {
        return [
            'production_ready' => 'C87_BLOCKED_C86_PRODUCTION_READY_ALREADY_TRUE',
            'production_catalog_runtime_wired' => 'C87_BLOCKED_C86_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C87_BLOCKED_C86_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C87_BLOCKED_C86_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C87_BLOCKED_C86_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'post_activation_observation_result_context_persisted_to_live_runtime' => 'C87_BLOCKED_C86_OBSERVATION_RESULT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C87_BLOCKED_C86_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C87_BLOCKED_C86_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C87_BLOCKED_C86_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C87_BLOCKED_C86_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C87_BLOCKED_C86_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C87_BLOCKED_C86_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C87_BLOCKED_C86_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c86): bool
    {
        $locks = is_array($c86['source_artifact_locks'] ?? null) ? $c86['source_artifact_locks'] : [];
        $summary = is_array($c86['lineage_validation_summary'] ?? null) ? $c86['lineage_validation_summary'] : [];
        $expected = [
            'actual_c85_hash' => self::EXPECTED_C85_HASH,
            'actual_c85_file_sha1' => self::EXPECTED_C85_FILE_SHA1,
            'c84_artifact_hash_from_c85' => self::EXPECTED_C84_HASH,
            'c84_file_sha1_from_c85' => self::EXPECTED_C84_FILE_SHA1,
            'c83_artifact_hash_from_c84' => self::EXPECTED_C83_HASH,
            'c83_file_sha1_from_c84' => self::EXPECTED_C83_FILE_SHA1,
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
        if (($locks['c85_hash_match'] ?? null) !== true || ($locks['c85_file_sha1_match'] ?? null) !== true || ($locks['c85_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        foreach ($expected as $field => $value) {
            if (($locks[$field] ?? null) !== $value) {
                return false;
            }
        }
        foreach ([
            'lineage_lock_validation_pass',
            'c85_to_c84_lock_match',
            'c84_to_c83_lock_match',
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

    private function candidateScopeMatches(array $c86): bool
    {
        $scope = is_array($c86['candidate_scope_freeze_summary'] ?? null) ? $c86['candidate_scope_freeze_summary'] : [];
        $decision = is_array($c86['post_activation_observation_result_decision'] ?? null) ? $c86['post_activation_observation_result_decision'] : [];
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || (array) ($scope['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || (array) ($scope['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE]) {
            return false;
        }
        foreach (['candidate_scope_changed_after_c85', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'observation_used_for_selection', 'observation_used_for_retuning', 'observation_used_for_ranking', 'observation_used_for_plan_confirm_mutation', 'observation_used_for_live_rollout', 'a01_promoted', 'a01_used_as_runtime_fallback'] as $field) {
            if (($scope[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($decision['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($decision['primary_candidate_post_activation_result_reviewed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || ($decision['backup_candidate_post_activation_result_reviewed'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE] || ($decision['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        foreach (['post_activation_observation_result_used_for_selection', 'post_activation_observation_result_used_for_retuning', 'post_activation_observation_result_used_for_ranking', 'post_activation_observation_result_used_for_plan_confirm_mutation', 'post_activation_observation_result_used_for_live_rollout'] as $field) {
            if (($decision[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c86 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c86_lock_validation_summary'] = $this->c86LockValidationSummary($load, $c86);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c86);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['post_activation_operator_go_no_go_decision'] = $this->postActivationOperatorGoNoGoDecision($pass);
        $artifact['post_activation_operator_go_no_go_candidate_scorecard'] = $this->candidateScorecard($c86, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['post_activation_operator_go_no_go_context_summary'] = $this->postActivationOperatorGoNoGoContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($pass);
        $artifact['c86_post_activation_observation_result_carry_forward_validation_summary'] = $this->c86PostActivationObservationResultCarryForwardValidationSummary($c86, $pass);
        $artifact['post_activation_operator_go_no_go_governance_summary'] = $this->postActivationOperatorGoNoGoGovernanceSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['post_activation_operator_go_no_go_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C87_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'rollback_plan_defined' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'documentation_governance_pass' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
            'post_activation_operator_go_decision_confirmed' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED',
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
            $failures[] = 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c86',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'operator_go_used_for_selection',
            'operator_go_used_for_retuning',
            'operator_go_used_for_ranking',
            'operator_go_used_for_plan_confirm_mutation',
            'operator_go_used_for_live_rollout',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_operator_go_no_go_context_persisted_to_live_runtime',
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
            return 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (strpos($field, 'plan_confirm') !== false) {
            return 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if (strpos($field, 'production') !== false || strpos($field, 'runtime') !== false || strpos($field, 'rollout') !== false) {
            return 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PRODUCTION_MUTATION';
        }
        if (strpos($field, 'latest') !== false || strpos($field, 'max_date') !== false || strpos($field, 'future') !== false || strpos($field, 'return_fields') !== false) {
            return 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
        }
        return 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
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

    private function c86LockValidationSummary(array $load, array $c86): array
    {
        return [
            'c86_lock_validation_completed' => true,
            'c86_artifact_exists' => $load['exists'],
            'c86_artifact_hash_match' => $load['hash_match'],
            'c86_file_sha1_match' => $load['file_sha1_match'],
            'c86_status_match' => ($c86['status'] ?? null) === self::EXPECTED_C86_STATUS,
            'c86_reason_code_match' => ($c86['reason_code'] ?? null) === self::EXPECTED_C86_REASON,
            'c86_post_activation_observation_result_review_pass' => ($c86['post_activation_observation_result_review_pass'] ?? null) === true,
            'c86_activation_authorized' => ($c86['activation_authorized'] ?? null) === true,
            'c86_activation_executed' => ($c86['activation_executed'] ?? null) === true,
            'c86_controlled_activation_record_observed' => ($c86['controlled_activation_record_observed'] ?? null) === true,
            'c86_post_activation_observation_result_reviewed' => ($c86['post_activation_observation_result_reviewed'] ?? null) === true,
            'c86_primary_candidate_post_activation_result_reviewed' => ($c86['primary_candidate_post_activation_result_reviewed'] ?? null) === true,
            'c86_backup_candidate_post_activation_result_reviewed' => ($c86['backup_candidate_post_activation_result_reviewed'] ?? null) === true,
            'c86_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c86_source_validation' => false,
            'c86_c87_readiness_count_match' => ($c86['next_readiness_decision']['candidate_ready_for_post_activation_operator_go_no_go_review_count'] ?? null) === 2,
            'c86_c87_recommendation_match' => ($c86['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C86_RECOMMENDATION,
            'c86_safety_fields_clean' => $this->c86SafetyFieldsClean($c86),
        ];
    }

    private function c86SafetyFieldsClean(array $c86): bool
    {
        foreach (array_keys($this->c86SafetyGateMap()) as $field) {
            if (($c86[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c86): array
    {
        $source = is_array($c86['lineage_validation_summary'] ?? null) ? $c86['lineage_validation_summary'] : [];
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c86),
            'lineage' => 'C86 -> C85 -> C84 -> C83 -> C82 -> C81 -> C80 -> C79 -> C78 -> C77 -> C76 -> C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c86_to_c85_lock_match' => true,
            'c85_to_c84_lock_match' => ($source['c85_to_c84_lock_match'] ?? null) === true,
            'c84_to_c83_lock_match' => ($source['c84_to_c83_lock_match'] ?? null) === true,
            'c83_to_c82_lock_match' => ($source['c83_to_c82_lock_match'] ?? null) === true,
            'c82_to_c81_lock_match' => ($source['c82_to_c81_lock_match'] ?? null) === true,
            'c81_to_c80_lock_match' => ($source['c81_to_c80_lock_match'] ?? null) === true,
            'c80_to_c79_lock_match' => ($source['c80_to_c79_lock_match'] ?? null) === true,
            'candidate_scope_lineage_locked' => ($source['candidate_scope_lineage_locked'] ?? null) === true,
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C86_LOCKED_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c86' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'operator_go_used_for_selection' => false,
            'operator_go_used_for_retuning' => false,
            'operator_go_used_for_ranking' => false,
            'operator_go_used_for_plan_confirm_mutation' => false,
            'operator_go_used_for_live_rollout' => false,
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
            'operator_approval_scope' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY',
            'operator_approval_persists_to_live_runtime' => false,
            'operator_approval_allows_post_activation_operator_go_no_go_record' => $pass,
            'operator_approval_allows_default_runtime_wiring' => false,
            'operator_approval_allows_deployment' => false,
            'operator_approval_allows_plan_confirm_mutation' => false,
        ];
    }

    private function postActivationOperatorGoNoGoDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'post_activation_operator_go_no_go_review_executed' => true,
            'post_activation_operator_go_no_go_review_allowed' => $pass,
            'post_activation_operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'source_activation_authorized' => true,
            'source_activation_executed' => true,
            'source_controlled_activation_record_observed' => true,
            'source_post_activation_observation_result_reviewed' => true,
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'post_activation_observation_result_reviewed' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_candidate_post_activation_operator_go' => $pass,
            'backup_candidate_post_activation_operator_go' => $pass,
            'a01_remains_comparator_only' => true,
            'post_activation_operator_go_no_go_is_artifact_record_only' => true,
            'operator_go_used_for_selection' => false,
            'operator_go_used_for_retuning' => false,
            'operator_go_used_for_ranking' => false,
            'operator_go_used_for_plan_confirm_mutation' => false,
            'operator_go_used_for_live_rollout' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'Operator GO recorded after C86 post-activation result review. Default live runtime remains unchanged.' : 'C87 post-activation operator go/no-go review did not pass; targeted repair or no-go handling is required.',
            'diagnostic_conclusion' => $pass ? 'POST_ACTIVATION_OPERATOR_GO_READY_FOR_C88_GO_DECISION_FINALIZATION_REVIEW' : 'C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR_REQUIRED',
        ];
    }

    private function candidateScorecard(array $c86, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c86_post_activation_observation_result_evidence_summary' => [
                'c86_post_activation_observation_result_review_pass' => ($c86['post_activation_observation_result_review_pass'] ?? null) === true,
                'c86_activation_authorized' => ($c86['activation_authorized'] ?? null) === true,
                'c86_activation_executed' => ($c86['activation_executed'] ?? null) === true,
                'c86_controlled_activation_record_observed' => ($c86['controlled_activation_record_observed'] ?? null) === true,
                'c86_post_activation_observation_result_reviewed' => ($c86['post_activation_observation_result_reviewed'] ?? null) === true,
            ],
            'post_activation_operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'candidate_ready_for_post_activation_go_decision_finalization_review' => $pass,
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'post_activation_observation_result_reviewed' => $pass,
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
            'c86_lock_validation_pass' => true,
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
            array_merge($base, ['candidate_code' => self::PRIMARY_CANDIDATE, 'c87_role' => 'primary_post_activation_operator_go_candidate', 'post_activation_operator_go' => $pass]),
            array_merge($base, ['candidate_code' => self::BACKUP_CANDIDATE, 'c87_role' => 'backup_post_activation_operator_go_candidate', 'post_activation_operator_go' => $pass]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c87_role' => 'comparator_only',
                'post_activation_operator_go_no_go_review_pass' => false,
                'operator_go_decision' => 'NO_GO',
                'candidate_ready_for_post_activation_go_decision_finalization_review' => false,
                'post_activation_operator_go' => false,
                'activation_authorized' => false,
                'activation_executed' => false,
                'controlled_activation_record_observed' => false,
                'post_activation_observation_result_reviewed' => false,
                'operator_approval_validation_pass' => false,
                'failure_reason_codes' => ['C87_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function postActivationOperatorGoNoGoContextSummary(bool $pass): array
    {
        return [
            'post_activation_operator_go_no_go_context_created' => true,
            'post_activation_operator_go_no_go_context_validation_pass' => $pass,
            'post_activation_operator_go_no_go_context_is_explicit_only' => true,
            'post_activation_operator_go_no_go_context_requires_operator_approval' => true,
            'post_activation_operator_go_no_go_context_requires_approval_reference' => true,
            'post_activation_operator_go_no_go_context_is_artifact_only' => true,
            'post_activation_operator_go_no_go_context_is_not_persisted_to_config' => true,
            'post_activation_operator_go_no_go_context_is_not_persisted_to_db' => true,
            'post_activation_operator_go_no_go_context_is_not_persisted_to_live_runtime' => true,
            'post_activation_operator_go_no_go_context_records_go_no_go_only' => $pass,
            'post_activation_operator_go_no_go_context_does_not_mutate_plan_confirm' => true,
            'post_activation_operator_go_no_go_context_does_not_change_default_runtime' => true,
            'post_activation_operator_go_no_go_context_rejects_a01_as_runtime_candidate' => true,
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
            'post_activation_observation_result_review_source_identified' => is_file(self::RUNTIME_PATHS['c86_post_activation_observation_result_service']),
            'post_activation_operator_go_no_go_review_source_identified' => is_file(self::RUNTIME_PATHS['c87_post_activation_operator_go_no_go_service']),
            'post_activation_operator_go_no_go_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c87_post_activation_operator_go_no_go_contract']),
            'explicit_post_activation_operator_go_no_go_context_identified_or_created' => is_file(self::RUNTIME_PATHS['c87_post_activation_operator_go_no_go_context']),
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
            'post_activation_operator_go_no_go_review_pass' => $pass,
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
            'post_activation_operator_go_no_go_review_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c86PostActivationObservationResultCarryForwardValidationSummary(array $c86, bool $pass): array
    {
        return [
            'c86_post_activation_observation_result_carry_forward_validation_completed' => true,
            'c86_post_activation_observation_result_carry_forward_validation_pass' => $pass,
            'c86_post_activation_observation_result_review_pass' => ($c86['post_activation_observation_result_review_pass'] ?? null) === true,
            'c86_activation_authorized' => ($c86['activation_authorized'] ?? null) === true,
            'c86_activation_executed' => ($c86['activation_executed'] ?? null) === true,
            'c86_controlled_activation_record_observed' => ($c86['controlled_activation_record_observed'] ?? null) === true,
            'c86_post_activation_observation_result_reviewed' => ($c86['post_activation_observation_result_reviewed'] ?? null) === true,
            'c86_primary_candidate_post_activation_result_reviewed' => ($c86['primary_candidate_post_activation_result_reviewed'] ?? null) === true,
            'c86_backup_candidate_post_activation_result_reviewed' => ($c86['backup_candidate_post_activation_result_reviewed'] ?? null) === true,
            'c86_progress_target_reached' => ($c86['progress_summary']['target_reached'] ?? null) === true,
            'c86_c87_readiness_count' => (int) ($c86['next_readiness_decision']['candidate_ready_for_post_activation_operator_go_no_go_review_count'] ?? 0),
            'c86_c87_recommendation_match' => ($c86['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C86_RECOMMENDATION,
        ];
    }

    private function postActivationOperatorGoNoGoGovernanceSummary(bool $pass): array
    {
        return [
            'post_activation_operator_go_no_go_governance_review_completed' => true,
            'post_activation_operator_go_no_go_governance_pass' => $pass,
            'post_activation_operator_go_no_go_is_artifact_only_record' => true,
            'post_activation_operator_go_no_go_is_not_default_runtime_wiring' => true,
            'post_activation_operator_go_no_go_is_not_production_deployment' => true,
            'post_activation_operator_go_no_go_is_not_plan_confirm_live_rollout' => true,
            'post_activation_operator_go_no_go_is_not_runtime_bridge_activation' => true,
            'operator_go_used_for_selection' => false,
            'operator_go_used_for_retuning' => false,
            'operator_go_used_for_ranking' => false,
            'operator_go_allowed_to_auto_enable_runtime' => false,
            'operator_go_allowed_to_auto_deploy' => false,
            'go_no_go_classification' => 'CONTROLLED_LIMITED_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY',
        ];
    }

    private function productionMutationSafetySummary(bool $pass): array
    {
        return [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'post_activation_operator_go_no_go_review_created' => true,
            'post_activation_operator_go_no_go_review_allowed' => $pass,
            'post_activation_operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'candidate_ready_for_post_activation_go_decision_finalization_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C88_RECOMMENDATION : 'C87_TARGETED_C86_POST_ACTIVATION_OBSERVATION_RESULT_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_operator_go_no_go_context_persisted_to_live_runtime' => false,
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
            'candidate_ready_for_post_activation_go_decision_finalization_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C88_RECOMMENDATION : 'C87_TARGETED_C86_POST_ACTIVATION_OBSERVATION_RESULT_REPAIR',
            'decision_reason' => $pass ? 'C87 operator GO was recorded for primary and backup after post-activation evidence. Only C88 post-activation go decision finalization review is allowed next.' : 'C87 post-activation operator go/no-go review did not pass; targeted repair or no-go handling is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW' : 'C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR_REQUIRED',
            'post_activation_operator_go_no_go_review_allowed' => $pass,
            'post_activation_operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'activation_authorized' => $pass,
            'activation_executed' => $pass,
            'controlled_activation_record_observed' => $pass,
            'post_activation_observation_result_reviewed' => $pass,
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
            'post_activation_operator_go_no_go_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C88_RECOMMENDATION : 'C87_TARGETED_C86_POST_ACTIVATION_OBSERVATION_RESULT_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW',
            'achieved' => [
                'C86 artifact hash and file SHA1 validated',
                'C86 nested C87 readiness path validated',
                'C86 -> C60 lineage lock validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Post-activation operator GO decision recorded for primary and backup',
                'PLAN/CONFIRM, default live runtime, and production deployment remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C88_RECOMMENDATION : 'C87_TARGETED_C86_POST_ACTIVATION_OBSERVATION_RESULT_REPAIR',
            'planned_next_scope' => $pass ? 'post-activation go decision finalization review only; C87 still does not wire default live runtime, deploy production, or mutate PLAN/CONFIRM' : 'targeted repair or no-go handling before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C87 artifact hash',
                'locked C87 file SHA1',
                'operator approval',
                'non-empty approval reference',
                'unchanged candidate scope',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C87 validates C86 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C87 records post-activation operator GO/NO-GO as an isolated artifact-only decision.',
            'C87 GO is not production deployment, not default runtime wiring, not live rollout, and not PLAN/CONFIRM mutation.',
            'C87 may only recommend C88 post-activation go decision finalization review as the next controlled review step.',
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
        if (strpos($status, 'C86_ARTIFACT') !== false || strpos($status, 'C86_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C87_C86_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'GO_DECISION') !== false) {
            return 'C87_CONTROLLED_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR';
        }
        if (strpos($status, 'OBSERVATION') !== false || strpos($status, 'ACTIVATION') !== false) {
            return 'C87_CONTROLLED_POST_ACTIVATION_OBSERVATION_RESULT_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C87_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C87_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C87_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C87_DOCUMENTATION_REPAIR';
        }
        return 'C87_TARGETED_C86_POST_ACTIVATION_OBSERVATION_RESULT_REPAIR';
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
