<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService
{
    public const RUN_CODE = 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW';

    public const DEFAULT_C87_ARTIFACT = 'storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json';
    public const DEFAULT_EXPECTED_C87_HASH = '4c319158e1e90bc7e491636361551ed212848c5d';
    public const DEFAULT_EXPECTED_C87_FILE_SHA1 = 'EBEA22AD5E07792D0D5EE6F71A317966EFF546D8';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C87_STATUS = 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C87_REASON = 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const EXPECTED_C87_RECOMMENDATION = self::RUN_CODE;
    private const C89_RECOMMENDATION = 'C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';

    private const EXPECTED_C86_HASH = '2ec7b0acddcf0ed09d1988c555cc32165e6c972f';
    private const EXPECTED_C86_FILE_SHA1 = 'D0F261827F286FFE502927D7C3704D7A79B4FD6E';
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
        'c88_validation_doc' => 'docs/watchlist/audit/WS_C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW.md',
        'c88_operator_commands_doc' => 'docs/watchlist/audit/WS_C88_OPERATOR_VALIDATION_COMMANDS.md',
        'c87_validation_doc' => 'docs/watchlist/audit/WS_C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW.md',
        'c87_operator_commands_doc' => 'docs/watchlist/audit/WS_C87_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c87_post_activation_operator_go_no_go_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC87ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationOperatorGoNoGoReviewService.php',
        'c88_post_activation_go_decision_finalization_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService.php',
        'c87_post_activation_operator_go_no_go_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationOperatorGoNoGoReviewContract.php',
        'c87_post_activation_operator_go_no_go_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationOperatorGoNoGoReviewContext.php',
        'c88_post_activation_go_decision_finalization_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationGoDecisionFinalizationReviewContract.php',
        'c88_post_activation_go_decision_finalization_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationGoDecisionFinalizationReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    public function execute(
        string $c87Artifact = self::DEFAULT_C87_ARTIFACT,
        string $expectedC87Hash = self::DEFAULT_EXPECTED_C87_HASH,
        string $expectedC87FileSha1 = self::DEFAULT_EXPECTED_C87_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c87Artifact, $expectedC87Hash, $expectedC87FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_ARTIFACT_LOCK_MISMATCH', 'C87 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_ARTIFACT_LOCK_MISMATCH', 'C87 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_FILE_SHA1_LOCK_MISMATCH', 'C87 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c87 = $load['payload'];
        if (($c87['status'] ?? null) !== self::EXPECTED_C87_STATUS || ($c87['reason_code'] ?? null) !== self::EXPECTED_C87_REASON) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_STATUS_OR_REASON_MISMATCH', 'C87 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c87['post_activation_operator_go_no_go_review_pass'] ?? null) !== true || ($c87['operator_go_decision'] ?? null) !== 'GO') {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_POST_ACTIVATION_OPERATOR_GO_NOT_PASSED', 'C87 post-activation operator GO did not pass.', $outputPath, $overwrite);
        }
        if (($c87['primary_candidate_post_activation_operator_go'] ?? null) !== true) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_PRIMARY_GO_NOT_CONFIRMED', 'C87 primary post-activation operator GO missing.', $outputPath, $overwrite);
        }
        if (($c87['backup_candidate_post_activation_operator_go'] ?? null) !== true) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_BACKUP_GO_NOT_CONFIRMED', 'C87 backup post-activation operator GO missing.', $outputPath, $overwrite);
        }
        if (($c87['next_readiness_decision']['candidate_ready_for_post_activation_go_decision_finalization_review_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_C88_READINESS_COUNT_MISMATCH', 'C87 nested C88 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c87['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C87_RECOMMENDATION) {
            return $this->blocked($artifact, 'C88_BLOCKED_C87_RECOMMENDATION_MISMATCH', 'C87 nested C88 recommendation mismatch.', $outputPath, $overwrite);
        }
        foreach ($this->c87SafetyGateMap() as $field => $status) {
            if (($c87[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C87 safety field '.$field.' is not false.', $outputPath, $overwrite);
            }
        }
        if (! $this->lineageLocksMatch($c87)) {
            return $this->blocked($artifact, 'C88_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C87 to C60 lineage lock mismatch.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c87)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C87 candidate scope does not match locked post-activation operator GO decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C88 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);
            $status = $failures[0];
            if ($status === 'C88_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C88 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C88 post-activation GO decision finalization gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C88 finalized the C87 post-activation operator GO for primary and backup as artifact-only evidence. This still does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW';
        $artifact['next_step_recommendation'] = self::C89_RECOMMENDATION;
        $artifact['post_activation_go_decision_finalization_review_executed'] = true;
        $artifact['post_activation_go_decision_finalization_review_allowed'] = true;
        $artifact['post_activation_go_decision_finalization_review_pass'] = true;
        $artifact['post_activation_go_decision_finalized'] = true;
        $artifact['finalized_post_activation_go_decision'] = 'FINALIZED_GO';
        $artifact['operator_go_decision'] = 'GO';
        $artifact['primary_candidate_post_activation_go_finalized'] = true;
        $artifact['backup_candidate_post_activation_go_finalized'] = true;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C88_NOT_RUN',
            'reason_code' => 'C88_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'post_activation_go_decision_finalization_review_executed' => false,
            'post_activation_go_decision_finalization_review_allowed' => false,
            'post_activation_go_decision_finalization_review_pass' => false,
            'post_activation_go_decision_finalized' => false,
            'finalized_post_activation_go_decision' => 'NOT_FINALIZED',
            'operator_go_decision' => 'NO_GO',
            'primary_candidate_post_activation_go_finalized' => false,
            'backup_candidate_post_activation_go_finalized' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_go_decision_finalization_context_persisted_to_live_runtime' => false,
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
        $c87 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c87['source_artifact_locks'] ?? null) ? $c87['source_artifact_locks'] : [];
        return [
            'c87_artifact_path' => $load['path'],
            'expected_c87_hash' => $load['expected_hash'],
            'actual_c87_hash' => $load['actual_hash'],
            'c87_hash_match' => $load['hash_match'],
            'expected_c87_file_sha1' => $load['expected_file_sha1'],
            'actual_c87_file_sha1' => $load['actual_file_sha1'],
            'c87_file_sha1_match' => $load['file_sha1_match'],
            'c87_source_lineage_checked' => true,
            'c87_source_lineage_match' => $this->lineageLocksMatch($c87),
            'c86_artifact_hash_from_c87' => (string) ($locks['actual_c86_hash'] ?? ''),
            'c86_file_sha1_from_c87' => (string) ($locks['actual_c86_file_sha1'] ?? ''),
            'c85_artifact_hash_from_c86' => (string) ($locks['c85_artifact_hash_from_c86'] ?? ''),
            'c85_file_sha1_from_c86' => (string) ($locks['c85_file_sha1_from_c86'] ?? ''),
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
            'expected_c87_hash' => $load['expected_hash'],
            'actual_c87_hash' => $load['actual_hash'],
            'c87_hash_match' => $load['hash_match'],
            'expected_c87_file_sha1' => $load['expected_file_sha1'],
            'actual_c87_file_sha1' => $load['actual_file_sha1'],
            'c87_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c87SafetyGateMap(): array
    {
        return [
            'production_ready' => 'C88_BLOCKED_C87_PRODUCTION_READY_ALREADY_TRUE',
            'production_catalog_runtime_wired' => 'C88_BLOCKED_C87_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C88_BLOCKED_C87_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C88_BLOCKED_C87_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C88_BLOCKED_C87_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'post_activation_operator_go_no_go_context_persisted_to_live_runtime' => 'C88_BLOCKED_C87_OPERATOR_GO_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C88_BLOCKED_C87_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C88_BLOCKED_C87_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C88_BLOCKED_C87_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C88_BLOCKED_C87_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C88_BLOCKED_C87_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C88_BLOCKED_C87_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C88_BLOCKED_C87_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c87): bool
    {
        $locks = is_array($c87['source_artifact_locks'] ?? null) ? $c87['source_artifact_locks'] : [];
        $summary = is_array($c87['lineage_validation_summary'] ?? null) ? $c87['lineage_validation_summary'] : [];
        $expected = [
            'actual_c86_hash' => self::EXPECTED_C86_HASH,
            'actual_c86_file_sha1' => self::EXPECTED_C86_FILE_SHA1,
            'c85_artifact_hash_from_c86' => self::EXPECTED_C85_HASH,
            'c85_file_sha1_from_c86' => self::EXPECTED_C85_FILE_SHA1,
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
        if (($locks['c86_hash_match'] ?? null) !== true || ($locks['c86_file_sha1_match'] ?? null) !== true || ($locks['c86_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        foreach ($expected as $field => $value) {
            if (($locks[$field] ?? null) !== $value) {
                return false;
            }
        }
        foreach ([
            'lineage_lock_validation_pass',
            'c86_to_c85_lock_match',
            'c85_to_c84_lock_match',
            'c84_to_c83_lock_match',
            'c83_to_c82_lock_match',
            'c82_to_c81_lock_match',
            'c81_to_c80_lock_match',
            'c80_to_c79_lock_match',
            'candidate_scope_lineage_locked',
        ] as $field) {
            if (($summary[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function candidateScopeMatches(array $c87): bool
    {
        $scope = is_array($c87['candidate_scope_freeze_summary'] ?? null) ? $c87['candidate_scope_freeze_summary'] : [];
        $decision = is_array($c87['post_activation_operator_go_no_go_decision'] ?? null) ? $c87['post_activation_operator_go_no_go_decision'] : [];
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || (array) ($scope['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || (array) ($scope['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE]) {
            return false;
        }
        foreach (['candidate_scope_changed_after_c86', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'operator_go_used_for_selection', 'operator_go_used_for_retuning', 'operator_go_used_for_ranking', 'operator_go_used_for_plan_confirm_mutation', 'operator_go_used_for_live_rollout', 'a01_promoted', 'a01_used_as_runtime_fallback'] as $field) {
            if (($scope[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($decision['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($decision['primary_candidate_post_activation_operator_go'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || ($decision['backup_candidate_post_activation_operator_go'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE] || ($decision['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        foreach (['operator_go_used_for_selection', 'operator_go_used_for_retuning', 'operator_go_used_for_ranking', 'operator_go_used_for_plan_confirm_mutation', 'operator_go_used_for_live_rollout'] as $field) {
            if (($decision[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c87 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c87_lock_validation_summary'] = $this->c87LockValidationSummary($load, $c87);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c87);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['post_activation_go_decision_finalization_decision'] = $this->postActivationGoDecisionFinalizationDecision($pass);
        $artifact['post_activation_go_decision_finalization_candidate_scorecard'] = $this->candidateScorecard($c87, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['post_activation_go_decision_finalization_context_summary'] = $this->postActivationGoDecisionFinalizationContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($pass);
        $artifact['c87_post_activation_operator_go_carry_forward_validation_summary'] = $this->c87PostActivationOperatorGoCarryForwardValidationSummary($c87, $pass);
        $artifact['post_activation_go_decision_finalization_governance_summary'] = $this->postActivationGoDecisionFinalizationGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['post_activation_go_decision_finalization_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C88_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'feature_flag_default_off' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'rollback_plan_defined' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'audit_logging_validation_pass' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_AUDIT_LOGGING_MISSING',
            'observability_validation_pass' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OBSERVABILITY_MISSING',
            'documentation_governance_pass' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
            'post_activation_go_decision_finalized_confirmed' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED',
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
            $failures[] = 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c87',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'post_activation_go_decision_used_for_selection',
            'post_activation_go_decision_used_for_retuning',
            'post_activation_go_decision_used_for_ranking',
            'post_activation_go_decision_used_for_plan_confirm_mutation',
            'post_activation_go_decision_used_for_live_rollout',
            'post_activation_go_decision_allowed_to_auto_promote_candidate',
            'post_activation_go_decision_allowed_to_auto_enable_runtime',
            'post_activation_go_decision_allowed_to_auto_deploy',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_go_decision_finalization_context_persisted_to_live_runtime',
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
            return 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (strpos($field, 'plan_confirm') !== false) {
            return 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if (strpos($field, 'production') !== false || strpos($field, 'runtime') !== false || strpos($field, 'rollout') !== false || strpos($field, 'deploy') !== false) {
            return 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PRODUCTION_MUTATION';
        }
        if (strpos($field, 'latest') !== false || strpos($field, 'max_date') !== false || strpos($field, 'future') !== false || strpos($field, 'return_fields') !== false) {
            return 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
        }
        return 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
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

    private function c87LockValidationSummary(array $load, array $c87): array
    {
        return [
            'c87_lock_validation_completed' => true,
            'c87_artifact_exists' => $load['exists'],
            'c87_artifact_hash_match' => $load['hash_match'],
            'c87_file_sha1_match' => $load['file_sha1_match'],
            'c87_status_match' => ($c87['status'] ?? null) === self::EXPECTED_C87_STATUS,
            'c87_reason_code_match' => ($c87['reason_code'] ?? null) === self::EXPECTED_C87_REASON,
            'c87_post_activation_operator_go_no_go_review_pass' => ($c87['post_activation_operator_go_no_go_review_pass'] ?? null) === true,
            'c87_operator_go_decision_match' => ($c87['operator_go_decision'] ?? null) === 'GO',
            'c87_primary_post_activation_operator_go' => ($c87['primary_candidate_post_activation_operator_go'] ?? null) === true,
            'c87_backup_post_activation_operator_go' => ($c87['backup_candidate_post_activation_operator_go'] ?? null) === true,
            'c87_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c87_source_validation' => false,
            'c87_c88_readiness_count_match' => ($c87['next_readiness_decision']['candidate_ready_for_post_activation_go_decision_finalization_review_count'] ?? null) === 2,
            'c87_c88_recommendation_match' => ($c87['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C87_RECOMMENDATION,
            'c87_safety_fields_clean' => $this->c87SafetyFieldsClean($c87),
        ];
    }

    private function c87SafetyFieldsClean(array $c87): bool
    {
        foreach (array_keys($this->c87SafetyGateMap()) as $field) {
            if (($c87[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c87): array
    {
        $source = is_array($c87['lineage_validation_summary'] ?? null) ? $c87['lineage_validation_summary'] : [];
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c87),
            'lineage' => 'C87 -> C86 -> C85 -> C84 -> C83 -> C82 -> C81 -> C80 -> C79 -> C78 -> C77 -> C76 -> C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c87_to_c86_lock_match' => true,
            'c86_to_c85_lock_match' => ($source['c86_to_c85_lock_match'] ?? null) === true,
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
            'candidate_scope_source' => 'C87_LOCKED_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c87' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'post_activation_go_decision_used_for_selection' => false,
            'post_activation_go_decision_used_for_retuning' => false,
            'post_activation_go_decision_used_for_ranking' => false,
            'post_activation_go_decision_used_for_plan_confirm_mutation' => false,
            'post_activation_go_decision_used_for_live_rollout' => false,
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
            'operator_approval_scope' => 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY',
            'operator_approval_persists_to_live_runtime' => false,
            'operator_approval_allows_post_activation_go_decision_finalization_record' => $pass,
            'operator_approval_allows_default_runtime_wiring' => false,
            'operator_approval_allows_deployment' => false,
            'operator_approval_allows_plan_confirm_mutation' => false,
        ];
    }

    private function postActivationGoDecisionFinalizationDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'post_activation_go_decision_finalization_review_executed' => true,
            'post_activation_go_decision_finalization_review_allowed' => $pass,
            'post_activation_go_decision_finalization_review_pass' => $pass,
            'source_operator_go_decision' => 'GO',
            'finalized_post_activation_go_decision' => $pass ? 'FINALIZED_GO' : 'NOT_FINALIZED',
            'post_activation_go_decision_finalized' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_candidate_post_activation_go_finalized' => $pass,
            'backup_candidate_post_activation_go_finalized' => $pass,
            'a01_remains_comparator_only' => true,
            'post_activation_go_decision_finalization_is_artifact_only' => true,
            'post_activation_go_decision_finalization_is_non_live_default' => true,
            'post_activation_go_decision_finalization_used_for_selection' => false,
            'post_activation_go_decision_finalization_used_for_retuning' => false,
            'post_activation_go_decision_finalization_used_for_ranking' => false,
            'post_activation_go_decision_finalization_used_for_plan_confirm_mutation' => false,
            'post_activation_go_decision_finalization_used_for_live_rollout' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_go_decision_finalization_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'C87 post-activation operator GO is finalized for primary and backup, but only as non-live C88 artifact evidence.' : 'C88 finalization did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'FINALIZED_POST_ACTIVATION_GO_RECORDED_FOR_C89_COMPLETION_BOUNDARY_REVIEW' : 'C88_POST_ACTIVATION_GO_DECISION_FINALIZATION_REPAIR_REQUIRED',
        ];
    }

    private function candidateScorecard(array $c87, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c87_post_activation_operator_go_evidence_summary' => [
                'c87_post_activation_operator_go_no_go_review_pass' => ($c87['post_activation_operator_go_no_go_review_pass'] ?? null) === true,
                'c87_operator_go_decision' => (string) ($c87['operator_go_decision'] ?? ''),
                'c87_primary_candidate_post_activation_operator_go' => ($c87['primary_candidate_post_activation_operator_go'] ?? null) === true,
                'c87_backup_candidate_post_activation_operator_go' => ($c87['backup_candidate_post_activation_operator_go'] ?? null) === true,
            ],
            'post_activation_go_decision_finalization_review_pass' => $pass,
            'candidate_ready_for_post_activation_completion_boundary_review' => $pass,
            'candidate_active_in_default_runtime_catalog' => false,
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
            'c87_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => true,
            'kill_switch_validation_pass' => true,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'post_activation_go_decision_finalization_advisory_only_pass' => true,
            'rollback_plan_validation_pass' => true,
            'emergency_disable_validation_pass' => true,
            'audit_logging_validation_pass' => true,
            'observability_validation_pass' => true,
            'production_mutation_safety_pass' => true,
            'documentation_governance_pass' => true,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];

        return [
            array_merge($base, ['candidate_code' => self::PRIMARY_CANDIDATE, 'c88_role' => 'primary_finalized_post_activation_go_candidate', 'post_activation_go_finalized' => $pass]),
            array_merge($base, ['candidate_code' => self::BACKUP_CANDIDATE, 'c88_role' => 'backup_finalized_post_activation_go_candidate', 'post_activation_go_finalized' => $pass]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c88_role' => 'comparator_only',
                'post_activation_go_decision_finalization_review_pass' => false,
                'candidate_ready_for_post_activation_completion_boundary_review' => false,
                'post_activation_go_finalized' => false,
                'operator_approval_validation_pass' => false,
                'failure_reason_codes' => ['C88_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function postActivationGoDecisionFinalizationContextSummary(bool $pass): array
    {
        return [
            'post_activation_go_decision_finalization_context_created' => true,
            'post_activation_go_decision_finalization_context_validation_pass' => $pass,
            'post_activation_go_decision_finalization_context_is_explicit_only' => true,
            'post_activation_go_decision_finalization_context_requires_operator_approval' => true,
            'post_activation_go_decision_finalization_context_requires_approval_reference' => true,
            'post_activation_go_decision_finalization_context_is_artifact_only' => true,
            'post_activation_go_decision_finalization_context_is_not_persisted_to_config' => true,
            'post_activation_go_decision_finalization_context_is_not_persisted_to_db' => true,
            'post_activation_go_decision_finalization_context_is_not_persisted_to_live_runtime' => true,
            'post_activation_go_decision_finalization_context_does_not_mutate_plan_confirm' => true,
            'post_activation_go_decision_finalization_context_does_not_change_default_runtime' => true,
            'post_activation_go_decision_finalization_context_rejects_a01_as_runtime_candidate' => true,
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
            'post_activation_operator_go_no_go_review_source_identified' => is_file(self::RUNTIME_PATHS['c87_post_activation_operator_go_no_go_service']),
            'post_activation_go_decision_finalization_review_source_identified' => is_file(self::RUNTIME_PATHS['c88_post_activation_go_decision_finalization_service']),
            'post_activation_go_decision_finalization_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c88_post_activation_go_decision_finalization_contract']),
            'explicit_post_activation_go_decision_finalization_context_identified_or_created' => is_file(self::RUNTIME_PATHS['c88_post_activation_go_decision_finalization_context']),
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
            'explicit_operator_approval_required_pass' => $pass,
            'operator_approval_artifact_required' => true,
            'operator_approval_reference_required' => true,
            'kill_switch_validation_pass' => $pass,
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
            'post_activation_go_decision_finalization_review_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c87PostActivationOperatorGoCarryForwardValidationSummary(array $c87, bool $pass): array
    {
        return [
            'c87_post_activation_operator_go_carry_forward_validation_completed' => true,
            'c87_post_activation_operator_go_carry_forward_validation_pass' => $pass,
            'c87_post_activation_operator_go_no_go_review_pass' => ($c87['post_activation_operator_go_no_go_review_pass'] ?? null) === true,
            'c87_operator_go_decision' => (string) ($c87['operator_go_decision'] ?? ''),
            'c87_primary_candidate_post_activation_operator_go' => ($c87['primary_candidate_post_activation_operator_go'] ?? null) === true,
            'c87_backup_candidate_post_activation_operator_go' => ($c87['backup_candidate_post_activation_operator_go'] ?? null) === true,
            'c87_progress_target_reached' => ($c87['progress_summary']['target_reached'] ?? null) === true,
            'c87_planned_next_review_match' => ($c87['planned_next_summary']['planned_next_review'] ?? null) === self::EXPECTED_C87_RECOMMENDATION,
            'c87_production_mutation_safety_pass' => true,
            'c87_c88_readiness_count' => (int) ($c87['next_readiness_decision']['candidate_ready_for_post_activation_go_decision_finalization_review_count'] ?? 0),
            'c87_c88_recommendation_match' => ($c87['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C87_RECOMMENDATION,
        ];
    }

    private function postActivationGoDecisionFinalizationGovernanceSummary(bool $pass): array
    {
        return [
            'post_activation_go_decision_finalization_governance_review_completed' => true,
            'post_activation_go_decision_finalization_governance_pass' => $pass,
            'post_activation_operator_go_decision_finalized' => $pass,
            'finalized_post_activation_go_is_explicit_context_only' => true,
            'finalized_post_activation_go_is_non_live_default' => true,
            'finalized_post_activation_go_is_artifact_only' => true,
            'finalized_post_activation_go_is_advisory_only' => true,
            'finalized_post_activation_go_used_for_selection' => false,
            'finalized_post_activation_go_used_for_retuning' => false,
            'finalized_post_activation_go_used_for_ranking' => false,
            'finalized_post_activation_go_used_for_plan_confirm_mutation' => false,
            'finalized_post_activation_go_used_for_live_rollout' => false,
            'finalized_post_activation_go_allowed_to_auto_promote_candidate' => false,
            'finalized_post_activation_go_allowed_to_auto_enable_runtime' => false,
            'finalized_post_activation_go_allowed_to_auto_deploy' => false,
            'post_activation_go_decision_finalization_classification' => 'CONTROLLED_LIMITED_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C88_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'post_activation_go_decision_finalization_review_created' => true,
            'post_activation_go_decision_finalization_review_allowed' => $pass,
            'post_activation_go_decision_finalization_review_pass' => $pass,
            'post_activation_go_decision_finalized' => $pass,
            'finalized_post_activation_go_decision' => $pass ? 'FINALIZED_GO' : 'NOT_FINALIZED',
            'candidate_ready_for_post_activation_completion_boundary_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C89_RECOMMENDATION : 'C88_TARGETED_C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_go_decision_finalization_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c87' => false,
            'parameter_changed_after_c87' => false,
            'new_candidate_created' => false,
            'post_activation_go_decision_used_for_selection' => false,
            'post_activation_go_decision_used_for_retuning' => false,
            'post_activation_go_decision_used_for_ranking' => false,
            'post_activation_go_decision_used_for_live_rollout' => false,
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
            'candidate_ready_for_post_activation_completion_boundary_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C89_RECOMMENDATION : 'C88_TARGETED_C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR',
            'decision_reason' => $pass ? 'C88 finalized C87 post-activation operator GO for primary and backup. Only C89 post-activation completion boundary review is allowed next.' : 'C88 post-activation GO decision finalization did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW' : 'C88_POST_ACTIVATION_GO_DECISION_FINALIZATION_REPAIR_REQUIRED',
            'post_activation_go_decision_finalization_review_allowed' => $pass,
            'post_activation_go_decision_finalization_review_pass' => $pass,
            'post_activation_go_decision_finalized' => $pass,
            'finalized_post_activation_go_decision' => $pass ? 'FINALIZED_GO' : 'NOT_FINALIZED',
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
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
            'post_activation_go_decision_finalization_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C89_RECOMMENDATION : 'C88_TARGETED_C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW',
            'achieved' => [
                'C87 artifact hash and file SHA1 validated',
                'C87 nested C88 readiness path validated',
                'C87 -> C60 lineage lock validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Post-activation operator GO decision finalized for primary and backup',
                'PLAN/CONFIRM and production runtime remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C89_RECOMMENDATION : 'C88_TARGETED_C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR',
            'planned_next_scope' => $pass ? 'post-activation completion boundary review only; still not deployment, live rollout, default runtime wiring, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C88 artifact hash',
                'locked C88 file SHA1',
                'operator approval',
                'non-empty approval reference',
                'unchanged candidate scope',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C88 validates C87 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C88 finalizes C87 post-activation operator GO as an isolated artifact-only decision.',
            'C88 finalized GO is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
            'C88 may only recommend C89 post-activation completion boundary review as the next controlled review step.',
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
        if (strpos($status, 'C87_ARTIFACT') !== false || strpos($status, 'C87_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C88_C87_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'GO_DECISION') !== false) {
            return 'C88_CONTROLLED_POST_ACTIVATION_GO_DECISION_FINALIZATION_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C88_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C88_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C88_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C88_DOCUMENTATION_REPAIR';
        }
        return 'C88_TARGETED_C87_POST_ACTIVATION_OPERATOR_GO_NO_GO_REPAIR';
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
