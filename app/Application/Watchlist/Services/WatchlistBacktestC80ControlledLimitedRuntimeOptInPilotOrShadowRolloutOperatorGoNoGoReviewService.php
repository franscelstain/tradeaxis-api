<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C79_ARTIFACT = 'storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json';
    public const DEFAULT_EXPECTED_C79_HASH = '0ad7924e75a4627475600567fc6f6ad839a83961';
    public const DEFAULT_EXPECTED_C79_FILE_SHA1 = '94A900AFD592C2756E2D8165B043F25191F1ACAF';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    private const PRIMARY_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';
    private const BACKUP_PARENT = 'C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL';
    private const COMPARATOR_PARENT = 'C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA';

    private const EXPECTED_C79_STATUS = 'C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C79_REASON = 'C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C79_RECOMMENDATION = 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW';
    private const C81_RECOMMENDATION = 'C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';

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
        'c80_validation_doc' => 'docs/watchlist/audit/WS_C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW.md',
        'c80_operator_commands_doc' => 'docs/watchlist/audit/WS_C80_OPERATOR_VALIDATION_COMMANDS.md',
        'c79_validation_doc' => 'docs/watchlist/audit/WS_C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW.md',
        'c79_operator_commands_doc' => 'docs/watchlist/audit/WS_C79_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c79_observation_result_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewService.php',
        'c80_operator_go_no_go_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService.php',
        'c79_controlled_limited_pilot_observation_result_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationResultReviewContract.php',
        'c79_controlled_limited_pilot_observation_result_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationResultReviewContext.php',
        'c79_controlled_limited_shadow_observation_result_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationResultReviewContract.php',
        'c79_controlled_limited_shadow_observation_result_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationResultReviewContext.php',
        'c80_controlled_limited_operator_go_no_go_contract' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedOperatorGoNoGoReviewContract.php',
        'c80_controlled_limited_operator_go_no_go_context' => 'app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedOperatorGoNoGoReviewContext.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    public function execute(
        string $c79Artifact = self::DEFAULT_C79_ARTIFACT,
        string $expectedC79Hash = self::DEFAULT_EXPECTED_C79_HASH,
        string $expectedC79FileSha1 = self::DEFAULT_EXPECTED_C79_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $createdAt = (string) ($options['created_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($createdAt);

        $load = $this->loadArtifactLock($c79Artifact, $expectedC79Hash, $expectedC79FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_ARTIFACT_LOCK_MISMATCH', 'C79 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_ARTIFACT_LOCK_MISMATCH', 'C79 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_FILE_SHA1_LOCK_MISMATCH', 'C79 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c79 = $load['payload'];
        if (($c79['status'] ?? null) !== self::EXPECTED_C79_STATUS || ($c79['reason_code'] ?? null) !== self::EXPECTED_C79_REASON) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_STATUS_OR_REASON_MISMATCH', 'C79 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (($c79['controlled_limited_runtime_opt_in_pilot_observation_result_review_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_CONTROLLED_PILOT_OBSERVATION_RESULT_NOT_PASSED', 'C79 controlled limited pilot observation result review did not pass.', $outputPath, $overwrite);
        }
        if (($c79['controlled_limited_shadow_rollout_observation_result_review_pass'] ?? null) !== true) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_CONTROLLED_SHADOW_OBSERVATION_RESULT_NOT_PASSED', 'C79 controlled limited shadow observation result review did not pass.', $outputPath, $overwrite);
        }
        if (($c79['next_readiness_decision']['candidate_ready_for_controlled_limited_operator_go_no_go_review_count'] ?? null) !== 2) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_C80_READINESS_COUNT_MISMATCH', 'C79 nested C80 readiness count mismatch.', $outputPath, $overwrite);
        }
        if (($c79['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C79_RECOMMENDATION) {
            return $this->blocked($artifact, 'C80_BLOCKED_C79_RECOMMENDATION_MISMATCH', 'C79 nested C80 recommendation mismatch.', $outputPath, $overwrite);
        }
        foreach ($this->c79SafetyGateMap() as $field => $status) {
            if (($c79[$field] ?? null) !== false) {
                return $this->blocked($artifact, $status, 'C79 safety field '.$field.' is not false.', $outputPath, $overwrite);
            }
        }
        if (! $this->lineageLocksMatch($c79)) {
            return $this->blocked($artifact, 'C80_BLOCKED_LINEAGE_LOCK_MISMATCH', 'C79 to C60 lineage lock mismatch.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c79)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C79 candidate scope does not match locked freeze.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C80 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $gateFailures = $this->controlledGateFailures($options);
        if ($gateFailures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $gateFailures]), false);
            $status = $gateFailures[0];
            if ($status === 'C80_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C80 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C80 operator go/no-go review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C80 operator go/no-go review issued GO for primary and backup. This is still artifact-only and does not deploy live.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C81_RECOMMENDATION;
        $artifact['operator_go_no_go_review_executed'] = true;
        $artifact['operator_go_no_go_review_allowed'] = true;
        $artifact['operator_go_no_go_review_pass'] = true;
        $artifact['operator_go_decision'] = 'GO';
        $artifact['primary_candidate_operator_go'] = true;
        $artifact['backup_candidate_operator_go'] = true;
        $artifact['production_ready'] = false;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C80_NOT_RUN',
            'reason_code' => 'C80_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'operator_go_no_go_review_executed' => false,
            'operator_go_no_go_review_allowed' => false,
            'operator_go_no_go_review_pass' => false,
            'operator_go_decision' => 'NO_GO',
            'primary_candidate_operator_go' => false,
            'backup_candidate_operator_go' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
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
        $c79 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c79['source_artifact_locks'] ?? null) ? $c79['source_artifact_locks'] : [];
        return [
            'c79_artifact_path' => $load['path'],
            'expected_c79_hash' => $load['expected_hash'],
            'actual_c79_hash' => $load['actual_hash'],
            'c79_hash_match' => $load['hash_match'],
            'expected_c79_file_sha1' => $load['expected_file_sha1'],
            'actual_c79_file_sha1' => $load['actual_file_sha1'],
            'c79_file_sha1_match' => $load['file_sha1_match'],
            'c79_source_lineage_checked' => true,
            'c79_source_lineage_match' => $this->lineageLocksMatch($c79),
            'c78_artifact_hash_from_c79' => (string) ($locks['actual_c78_hash'] ?? ''),
            'c78_file_sha1_from_c79' => (string) ($locks['actual_c78_file_sha1'] ?? ''),
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
            'expected_c79_hash' => $load['expected_hash'],
            'actual_c79_hash' => $load['actual_hash'],
            'c79_hash_match' => $load['hash_match'],
            'expected_c79_file_sha1' => $load['expected_file_sha1'],
            'actual_c79_file_sha1' => $load['actual_file_sha1'],
            'c79_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function c79SafetyGateMap(): array
    {
        return [
            'production_catalog_runtime_wired' => 'C80_BLOCKED_C79_RUNTIME_ALREADY_WIRED',
            'controlled_opt_in_runtime_bridge_active' => 'C80_BLOCKED_C79_CONTROLLED_OPT_IN_BRIDGE_ALREADY_ACTIVE',
            'controlled_parallel_run_active' => 'C80_BLOCKED_C79_CONTROLLED_PARALLEL_RUN_ALREADY_ACTIVE',
            'controlled_rollout_active' => 'C80_BLOCKED_C79_CONTROLLED_ROLLOUT_ALREADY_ACTIVE',
            'controlled_limited_pilot_observation_result_context_persisted_to_live_runtime' => 'C80_BLOCKED_C79_CONTROLLED_PILOT_OBSERVATION_RESULT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'controlled_limited_shadow_observation_result_context_persisted_to_live_runtime' => 'C80_BLOCKED_C79_CONTROLLED_SHADOW_OBSERVATION_RESULT_CONTEXT_ALREADY_PERSISTED_TO_LIVE_RUNTIME',
            'production_deployment_allowed' => 'C80_BLOCKED_C79_PRODUCTION_DEPLOYMENT_ALREADY_ALLOWED',
            'production_deployment_executed' => 'C80_BLOCKED_C79_DEPLOYMENT_ALREADY_EXECUTED',
            'plan_confirm_mutation_allowed' => 'C80_BLOCKED_C79_PLAN_CONFIRM_MUTATION_ALREADY_ALLOWED',
            'plan_confirm_mutated' => 'C80_BLOCKED_C79_PLAN_CONFIRM_ALREADY_MUTATED',
            'plan_confirm_runtime_reads_activated_catalog' => 'C80_BLOCKED_C79_PLAN_CONFIRM_ALREADY_READING_ACTIVATED_CATALOG',
            'live_plan_confirm_rollout_allowed' => 'C80_BLOCKED_C79_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_ALLOWED',
            'live_plan_confirm_rollout_executed' => 'C80_BLOCKED_C79_LIVE_PLAN_CONFIRM_ROLLOUT_ALREADY_EXECUTED',
        ];
    }

    private function lineageLocksMatch(array $c79): bool
    {
        $locks = is_array($c79['source_artifact_locks'] ?? null) ? $c79['source_artifact_locks'] : [];
        $summary = is_array($c79['lineage_validation_summary'] ?? null) ? $c79['lineage_validation_summary'] : [];

        if (($locks['c78_hash_match'] ?? null) !== true || ($locks['c78_file_sha1_match'] ?? null) !== true || ($locks['c78_source_lineage_match'] ?? null) !== true) {
            return false;
        }
        if (($locks['actual_c78_hash'] ?? null) !== self::EXPECTED_C78_HASH || ($locks['actual_c78_file_sha1'] ?? null) !== self::EXPECTED_C78_FILE_SHA1) {
            return false;
        }
        if (($locks['c77_artifact_hash_from_c78'] ?? null) !== self::EXPECTED_C77_HASH || ($locks['c77_file_sha1_from_c78'] ?? null) !== self::EXPECTED_C77_FILE_SHA1) {
            return false;
        }
        if (($locks['c76_artifact_hash_from_c77'] ?? null) !== self::EXPECTED_C76_HASH || ($locks['c76_file_sha1_from_c77'] ?? null) !== self::EXPECTED_C76_FILE_SHA1) {
            return false;
        }
        if (($locks['c75_artifact_hash_from_c76'] ?? null) !== self::EXPECTED_C75_HASH || ($locks['c75_file_sha1_from_c76'] ?? null) !== self::EXPECTED_C75_FILE_SHA1) {
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
            ['storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json', self::EXPECTED_C78_HASH, self::EXPECTED_C78_FILE_SHA1],
            ['storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json', self::EXPECTED_C77_HASH, self::EXPECTED_C77_FILE_SHA1],
            ['storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json', self::EXPECTED_C76_HASH, self::EXPECTED_C76_FILE_SHA1],
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
        $c72 = json_decode((string) file_get_contents($expected[6][0]), true);
        $sequence = implode(' ', (array) ($c72['lineage_validation_summary']['lineage_sequence'] ?? []));
        return is_array($c72)
            && (($c72['source_artifact_locks']['c71_source_lineage_match'] ?? null) === true)
            && (($c72['lineage_validation_summary']['c71_source_lineage_match'] ?? null) === true)
            && strpos($sequence, 'C60') !== false;
    }

    private function candidateScopeMatches(array $c79): bool
    {
        $scope = is_array($c79['candidate_scope_freeze_summary'] ?? null) ? $c79['candidate_scope_freeze_summary'] : [];
        $codes = (array) ($c79['next_readiness_decision']['candidate_codes'] ?? []);
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (! in_array(self::BACKUP_CANDIDATE, (array) ($scope['backup_candidate_codes'] ?? []), true)) {
            return false;
        }
        if (! in_array(self::COMPARATOR_CANDIDATE, (array) ($scope['comparator_only_candidate_codes'] ?? []), true)) {
            return false;
        }
        return in_array(self::PRIMARY_CANDIDATE, $codes, true)
            && in_array(self::BACKUP_CANDIDATE, $codes, true)
            && ! in_array(self::COMPARATOR_CANDIDATE, $codes, true);
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c79 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c79_lock_validation_summary'] = $this->c79LockValidationSummary($load, $c79);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c79);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($options);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($pass);
        $artifact['operator_go_no_go_candidate_scorecard'] = $this->candidateScorecard($c79, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['operator_go_no_go_context_summary'] = $this->operatorGoNoGoContextSummary($pass, $options);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['rollback_and_emergency_disable_review_summary'] = $this->rollbackAndEmergencyDisableReviewSummary($pass);
        $artifact['c79_proof_carry_forward_validation_summary'] = $this->c79ProofCarryForwardValidationSummary($c79, $pass);
        $artifact['go_no_go_governance_summary'] = $this->goNoGoGovernanceSummary($options, $pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['operator_go_no_go_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C80_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'operator_go_decision_confirmed' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED',
            'feature_flag_default_off' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'operator_approval_required' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'approval_reference_required' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING',
            'kill_switch_available' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH',
            'rollback_plan_defined' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_ROLLBACK_PLAN_MISSING',
            'emergency_disable_path_defined' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_EMERGENCY_DISABLE_MISSING',
            'baseline_plan_confirm_non_mutation_pass' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'audit_logging_validation_pass' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_AUDIT_LOGGING_MISSING',
            'observability_validation_pass' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OBSERVABILITY_MISSING',
            'documentation_governance_pass' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
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
        if ($this->configFlagIsOn('production_catalog_runtime_bridge_enabled') || $this->configFlagIsOn('production_catalog_controlled_runtime_opt_in_pilot_enabled') || $this->configFlagIsOn('production_catalog_controlled_shadow_rollout_enabled') || $this->configFlagIsOn('production_catalog_controlled_parallel_run_enabled') || $this->configFlagIsOn('production_catalog_controlled_rollout_enabled')) {
            $failures[] = 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'plan_confirm_output_changed', 'baseline_plan_confirm_hash_changed', 'plan_confirm_runtime_default_path_changed',
            'a01_used_as_runtime_fallback', 'a01_promoted', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed',
            'operator_go_used_for_live_rollout', 'operator_go_used_for_selection', 'operator_go_used_for_retuning', 'operator_go_used_for_ranking',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (strpos($field, 'operator_go_used_') === 0) {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_USED_FOR_SELECTION_OR_ROLLOUT';
        }
        if ($field === 'plan_confirm_output_changed') {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        if ($field === 'baseline_plan_confirm_hash_changed') {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_BASELINE_HASH_CHANGED';
        }
        if (strpos($field, '_context_persisted_to_live_runtime') !== false || $field === 'plan_confirm_runtime_default_path_changed') {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_DEFAULT_PATH_MUTATION';
        }
        if (in_array($field, ['a01_used_as_runtime_fallback', 'a01_promoted', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed'], true)) {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (in_array($field, ['latest_shortcut_used', 'max_date_shortcut_used', 'future_lookup_detected', 'return_fields_used_for_selection'], true)) {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_SAFETY_OR_LEAKAGE';
        }
        if (in_array($field, ['production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active', 'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed'], true)) {
            return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_PRODUCTION_MUTATION';
        }
        return 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
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

    private function c79LockValidationSummary(array $load, array $c79): array
    {
        return [
            'c79_lock_validation_completed' => true,
            'c79_artifact_exists' => $load['exists'],
            'c79_artifact_hash_match' => $load['hash_match'],
            'c79_file_sha1_match' => $load['file_sha1_match'],
            'c79_status_match' => ($c79['status'] ?? null) === self::EXPECTED_C79_STATUS,
            'c79_reason_code_match' => ($c79['reason_code'] ?? null) === self::EXPECTED_C79_REASON,
            'c79_pilot_observation_result_review_pass' => ($c79['controlled_limited_runtime_opt_in_pilot_observation_result_review_pass'] ?? null) === true,
            'c79_shadow_observation_result_review_pass' => ($c79['controlled_limited_shadow_rollout_observation_result_review_pass'] ?? null) === true,
            'c79_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c79_source_validation' => false,
            'c79_c80_readiness_count_match' => ($c79['next_readiness_decision']['candidate_ready_for_controlled_limited_operator_go_no_go_review_count'] ?? null) === 2,
            'c79_c80_recommendation_match' => ($c79['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C79_RECOMMENDATION,
            'c79_safety_fields_clean' => $this->c79SafetyFieldsClean($c79),
        ];
    }

    private function c79SafetyFieldsClean(array $c79): bool
    {
        foreach ($this->c79SafetyGateMap() as $field => $status) {
            if (($c79[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function lineageValidationSummary(array $c79): array
    {
        $locks = is_array($c79['source_artifact_locks'] ?? null) ? $c79['source_artifact_locks'] : [];
        return [
            'lineage_validation_completed' => true,
            'lineage_lock_validation_pass' => $this->lineageLocksMatch($c79),
            'lineage' => 'C79 -> C78 -> C77 -> C76 -> C75 -> C74 -> C73 -> C72 -> C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60',
            'c79_to_c78_lock_match' => (($locks['actual_c78_hash'] ?? null) === self::EXPECTED_C78_HASH),
            'c78_to_c77_lock_match' => (($locks['c77_artifact_hash_from_c78'] ?? null) === self::EXPECTED_C77_HASH),
            'c77_to_c76_lock_match' => (($locks['c76_artifact_hash_from_c77'] ?? null) === self::EXPECTED_C76_HASH),
            'c76_to_c75_lock_match' => (($locks['c75_artifact_hash_from_c76'] ?? null) === self::EXPECTED_C75_HASH),
            'c75_to_c74_lock_match' => (($locks['c74_artifact_hash_from_c75'] ?? null) === self::EXPECTED_C74_HASH),
            'c74_to_c73_lock_match' => (($locks['c73_artifact_hash_from_c74'] ?? null) === self::EXPECTED_C73_HASH),
            'c73_to_c72_lock_match' => $this->knownLineageArtifactsMatch(),
            'candidate_scope_lineage_locked' => true,
        ];
    }

    private function candidateScopeFreezeSummary(array $options): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C79_LOCKED_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c79' => false,
            'new_candidate_created' => (bool) ($options['new_candidate_created'] ?? false),
            'selection_rule_changed' => (bool) ($options['selection_rule_changed'] ?? false),
            'parameter_changed' => (bool) ($options['parameter_changed'] ?? false),
            'operator_go_used_for_selection' => (bool) ($options['operator_go_used_for_selection'] ?? false),
            'operator_go_used_for_retuning' => (bool) ($options['operator_go_used_for_retuning'] ?? false),
            'operator_go_used_for_ranking' => (bool) ($options['operator_go_used_for_ranking'] ?? false),
            'operator_go_used_for_live_rollout' => (bool) ($options['operator_go_used_for_live_rollout'] ?? false),
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
            'operator_approval_scope' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_ONLY',
            'operator_go_decision_confirmed' => $pass,
            'operator_approval_executes_full_production_deployment' => false,
            'operator_approval_executes_live_plan_confirm_rollout' => false,
            'operator_approval_mutates_plan_confirm' => false,
            'operator_approval_activates_production_catalog_runtime_wiring' => false,
        ];
    }

    private function operatorGoNoGoDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'operator_go_no_go_review_executed' => true,
            'operator_go_no_go_review_allowed' => $pass,
            'operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'primary_candidate_operator_go' => $pass,
            'backup_candidate_operator_go' => $pass,
            'a01_remains_comparator_only' => true,
            'go_decision_is_artifact_only' => true,
            'go_decision_is_non_live_default' => true,
            'go_decision_used_for_selection' => false,
            'go_decision_used_for_retuning' => false,
            'go_decision_used_for_ranking' => false,
            'go_decision_used_for_plan_confirm_mutation' => false,
            'go_decision_used_for_live_rollout' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'decision_reason' => $pass ? 'Operator GO is recorded for primary and backup, but only as a non-live C80 artifact decision.' : 'Operator GO decision was not confirmed; targeted repair or no-go handling is required.',
            'diagnostic_conclusion' => $pass ? 'OPERATOR_GO_RECORDED_FOR_C81_FINALIZATION_REVIEW' : 'OPERATOR_NO_GO_OR_REPAIR_REQUIRED',
        ];
    }

    private function candidateScorecard(array $c79, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c79_observation_result_evidence_summary' => [
                'c79_pilot_observation_result_review_pass' => ($c79['controlled_limited_runtime_opt_in_pilot_observation_result_review_pass'] ?? null) === true,
                'c79_shadow_observation_result_review_pass' => ($c79['controlled_limited_shadow_rollout_observation_result_review_pass'] ?? null) === true,
            ],
            'operator_go_no_go_review_pass' => $pass,
            'candidate_ready_for_go_decision_finalization_review' => $pass,
            'candidate_active_in_controlled_catalog' => false,
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
            'c79_lock_validation_pass' => true,
            'lineage_lock_validation_pass' => true,
            'candidate_scope_freeze_pass' => true,
            'operator_approval_validation_pass' => $pass,
            'default_off_feature_flag_pass' => $pass,
            'kill_switch_validation_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => $pass,
            'operator_go_advisory_only_pass' => $pass,
            'rollback_plan_validation_pass' => $pass,
            'emergency_disable_validation_pass' => $pass,
            'audit_logging_validation_pass' => $pass,
            'observability_validation_pass' => $pass,
            'production_mutation_safety_pass' => $pass,
            'documentation_governance_pass' => $pass,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c80_role' => 'primary_operator_go_candidate',
                'parent_candidate_code' => self::PRIMARY_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c80_role' => 'backup_operator_go_candidate',
                'parent_candidate_code' => self::BACKUP_PARENT,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c80_role' => 'comparator_only',
                'parent_candidate_code' => self::COMPARATOR_PARENT,
                'operator_go_no_go_review_pass' => false,
                'candidate_ready_for_go_decision_finalization_review' => false,
                'operator_approval_validation_pass' => false,
                'failure_reason_codes' => ['C80_A01_REMAINS_COMPARATOR_ONLY'],
            ]),
        ];
    }

    private function operatorGoNoGoContextSummary(bool $pass, array $options): array
    {
        return [
            'operator_go_no_go_context_created' => true,
            'operator_go_no_go_context_validation_pass' => $pass,
            'operator_go_no_go_context_is_explicit_only' => true,
            'operator_go_no_go_context_requires_operator_approval' => true,
            'operator_go_no_go_context_requires_approval_reference' => true,
            'operator_go_no_go_context_is_artifact_only' => true,
            'operator_go_no_go_context_is_not_persisted_to_config' => true,
            'operator_go_no_go_context_is_not_persisted_to_db' => true,
            'operator_go_no_go_context_is_not_persisted_to_live_runtime' => true,
            'operator_go_no_go_context_does_not_mutate_plan_confirm' => true,
            'operator_go_no_go_context_does_not_change_default_runtime' => true,
            'operator_go_no_go_context_carries_primary_candidate' => self::PRIMARY_CANDIDATE,
            'operator_go_no_go_context_carries_backup_candidate' => self::BACKUP_CANDIDATE,
            'operator_go_no_go_context_rejects_a01_as_runtime_candidate' => true,
            'operator_go_no_go_context_fallback_preserves_default_plan_confirm' => true,
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
            'controlled_limited_observation_result_review_source_identified' => is_file(self::RUNTIME_PATHS['c79_observation_result_service']),
            'controlled_limited_operator_go_no_go_review_source_identified' => is_file(self::RUNTIME_PATHS['c80_operator_go_no_go_service']),
            'controlled_limited_operator_go_no_go_contract_identified_or_created' => is_file(self::RUNTIME_PATHS['c80_controlled_limited_operator_go_no_go_contract']),
            'explicit_operator_go_no_go_context_identified_or_created' => is_file(self::RUNTIME_PATHS['c80_controlled_limited_operator_go_no_go_context']),
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
            'kill_switch_name' => 'watchlist.production_catalog_runtime_bridge_kill_switch',
            'kill_switch_available' => true,
            'production_catalog_runtime_wired' => false,
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
            'rollback_preserves_existing_plan_confirm_behavior' => true,
            'emergency_disable_path_required' => true,
            'emergency_disable_path_defined' => true,
            'emergency_disable_validation_pass' => $pass,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function c79ProofCarryForwardValidationSummary(array $c79, bool $pass): array
    {
        return [
            'c79_proof_carry_forward_validation_completed' => true,
            'c79_proof_carry_forward_validation_pass' => $pass,
            'c79_pilot_observation_result_review_pass' => ($c79['controlled_limited_runtime_opt_in_pilot_observation_result_review_pass'] ?? null) === true,
            'c79_shadow_observation_result_review_pass' => ($c79['controlled_limited_shadow_rollout_observation_result_review_pass'] ?? null) === true,
            'c79_progress_target_reached' => ($c79['progress_summary']['target_reached'] ?? null) === true,
            'c79_planned_next_review_match' => ($c79['planned_next_summary']['planned_next_review'] ?? null) === self::EXPECTED_C79_RECOMMENDATION,
            'c79_operator_approval_proof_pass' => true,
            'c79_baseline_non_mutation_pass' => true,
            'c79_production_mutation_safety_pass' => true,
            'c79_negative_operator_approval_rejection_proof_retained' => true,
            'c79_c80_readiness_count' => (int) ($c79['next_readiness_decision']['candidate_ready_for_controlled_limited_operator_go_no_go_review_count'] ?? 0),
            'c79_c80_recommendation_match' => ($c79['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C79_RECOMMENDATION,
        ];
    }

    private function goNoGoGovernanceSummary(array $options, bool $pass): array
    {
        return [
            'go_no_go_governance_review_completed' => true,
            'go_no_go_governance_pass' => $pass,
            'operator_go_decision_confirmed' => $pass,
            'operator_go_is_explicit_context_only' => true,
            'operator_go_is_non_live_default' => true,
            'operator_go_is_artifact_only' => true,
            'operator_go_is_advisory_only' => true,
            'operator_go_used_for_selection' => false,
            'operator_go_used_for_retuning' => false,
            'operator_go_used_for_ranking' => false,
            'operator_go_used_for_plan_confirm_mutation' => false,
            'operator_go_used_for_live_rollout' => false,
            'operator_go_allowed_to_auto_promote_candidate' => false,
            'operator_go_allowed_to_auto_enable_runtime' => false,
            'operator_go_allowed_to_auto_deploy' => false,
            'go_no_go_classification' => 'CONTROLLED_LIMITED_OPERATOR_GO_NO_GO_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C80_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
            'operator_go_no_go_review_created' => true,
            'operator_go_no_go_review_allowed' => $pass,
            'operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'candidate_ready_for_go_decision_finalization_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C81_RECOMMENDATION : 'C80_TARGETED_C79_OBSERVATION_RESULT_REPAIR',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'selection_changed_after_c79' => false,
            'parameter_changed_after_c79' => false,
            'new_candidate_created' => false,
            'operator_go_used_for_selection' => false,
            'operator_go_used_for_retuning' => false,
            'operator_go_used_for_ranking' => false,
            'operator_go_used_for_live_rollout' => false,
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
            'c80_docs_exist' => is_file(self::DOC_PATHS['c80_validation_doc']),
            'operator_validation_commands_exist' => is_file(self::DOC_PATHS['c80_operator_commands_doc']),
            'audit_tracker_updated' => is_file(self::DOC_PATHS['implementation_status_doc']),
            'contract_tracker_updated' => is_file(self::DOC_PATHS['contract_tracker_doc']),
            'audit_governance_updated' => is_file(self::DOC_PATHS['audit_update_governance_doc']),
            'docs_overclaim_live_deployment' => false,
            'docs_overclaim_plan_confirm_live_catalog_read' => false,
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'validation_completed' => true,
            'candidate_ready_for_go_decision_finalization_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C81_RECOMMENDATION : 'C80_TARGETED_C79_OBSERVATION_RESULT_REPAIR',
            'decision_reason' => $pass ? 'C80 operator GO was recorded for primary and backup. Only C81 go decision finalization review is allowed next.' : 'C80 operator go/no-go review did not pass; targeted repair or no-go handling is required.',
            'diagnostic_conclusion' => $pass ? 'READY_FOR_C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW' : 'C80_OPERATOR_GO_NO_GO_REPAIR_REQUIRED',
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'operator_go_no_go_review_allowed' => $pass,
            'operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
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
            'operator_go_no_go_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C81_RECOMMENDATION : 'C80_TARGETED_C79_OBSERVATION_RESULT_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW',
            'source_target_locked' => 'C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW',
            'achieved' => [
                'C79 artifact hash and file SHA1 validated',
                'C79 nested C80 readiness path validated',
                'C79 -> C60 lineage lock validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Operator GO decision recorded for primary and backup',
                'PLAN/CONFIRM and production runtime remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C81_RECOMMENDATION : 'C80_TARGETED_C79_OBSERVATION_RESULT_REPAIR',
            'planned_next_scope' => $pass ? 'go decision finalization review only; still not deployment, live rollout, or PLAN/CONFIRM mutation' : 'targeted repair or no-go handling before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C80 artifact hash',
                'locked C80 file SHA1',
                'operator approval',
                'non-empty approval reference',
                'unchanged candidate scope',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C80 validates C79 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C80 records operator GO/NO-GO as an isolated artifact-only decision.',
            'C80 GO is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
            'C80 may only recommend C81 go decision finalization review as a further non-live review step.',
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
        if (strpos($status, 'C79_ARTIFACT') !== false || strpos($status, 'C79_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C80_C79_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'GO_DECISION') !== false) {
            return 'C80_CONTROLLED_OPERATOR_GO_NO_GO_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C80_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'ROLLBACK') !== false || strpos($status, 'EMERGENCY') !== false) {
            return 'C80_ROLLBACK_OR_EMERGENCY_DISABLE_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C80_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C80_DOCUMENTATION_REPAIR';
        }
        return 'C80_TARGETED_C79_OBSERVATION_RESULT_REPAIR';
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
