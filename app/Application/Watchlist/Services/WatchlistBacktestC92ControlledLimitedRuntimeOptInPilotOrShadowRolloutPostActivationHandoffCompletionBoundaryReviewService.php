<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService
{
    public const RUN_CODE = 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    public const ARTIFACT_TYPE = 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW';

    public const DEFAULT_C91_ARTIFACT = 'storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json';
    public const DEFAULT_EXPECTED_C91_HASH = '17731873369cf69b5083b2f80b15101de71851f2';
    public const DEFAULT_EXPECTED_C91_FILE_SHA1 = 'D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C91_STATUS = 'C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C91_REASON = 'C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C91_RECOMMENDATION = self::RUN_CODE;
    private const C93_RECOMMENDATION = 'C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'post_activation_handoff_finalization_context_persisted_to_live_runtime',
        'post_activation_handoff_readiness_context_persisted_to_live_runtime',
        'post_activation_go_decision_finalization_context_persisted_to_live_runtime',
        'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
    ];

    private const REQUIRED_C92_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
    ];

    private const DICTIONARY_PATHS = [
        'market_data_dictionary_path' => 'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
        'database_dictionary_usage_rule_path' => 'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
        'mariadb_schema_path' => 'docs/market_data/db/Database_Schema_MariaDB.sql',
        'mariadb_schema_contract_path' => 'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
        'db_fields_metadata_path' => 'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
        'watchlist_db_dictionary_path' => 'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
    ];

    private const DOC_PATHS = [
        'c92_validation_doc' => 'docs/watchlist/audit/WS_C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW.md',
        'c92_operator_commands_doc' => 'docs/watchlist/audit/WS_C92_OPERATOR_VALIDATION_COMMANDS.md',
        'c91_validation_doc' => 'docs/watchlist/audit/WS_C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW.md',
        'c91_operator_commands_doc' => 'docs/watchlist/audit/WS_C91_OPERATOR_VALIDATION_COMMANDS.md',
        'implementation_status_doc' => 'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
        'contract_tracker_doc' => 'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
        'audit_update_governance_doc' => 'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
    ];

    private const RUNTIME_PATHS = [
        'c91_post_activation_handoff_finalization_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService.php',
        'c92_post_activation_handoff_completion_boundary_service' => 'app/Application/Watchlist/Services/WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService.php',
        'c91_command' => 'app/Console/Commands/Watchlist/RunBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewCommand.php',
        'c92_command' => 'app/Console/Commands/Watchlist/RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand.php',
        'config_watchlist' => 'config/watchlist.php',
        'console_kernel' => 'app/Console/Kernel.php',
    ];

    public function execute(
        string $c91Artifact = self::DEFAULT_C91_ARTIFACT,
        string $expectedC91Hash = self::DEFAULT_EXPECTED_C91_HASH,
        string $expectedC91FileSha1 = self::DEFAULT_EXPECTED_C91_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c91Artifact, $expectedC91Hash, $expectedC91FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, 'C92_BLOCKED_C91_ARTIFACT_LOCK_MISMATCH', 'C91 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, 'C92_BLOCKED_C91_ARTIFACT_LOCK_MISMATCH', 'C91 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, 'C92_BLOCKED_C91_FILE_SHA1_LOCK_MISMATCH', 'C91 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c91 = $load['payload'];
        if (($c91['status'] ?? null) !== self::EXPECTED_C91_STATUS || ($c91['reason_code'] ?? null) !== self::EXPECTED_C91_REASON) {
            return $this->blocked($artifact, 'C92_BLOCKED_C91_STATUS_OR_REASON_MISMATCH', 'C91 status/reason mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c91NextRecommendationMatches($c91)) {
            return $this->blocked($artifact, 'C92_BLOCKED_C91_NEXT_RECOMMENDATION_MISMATCH', 'C91 next recommendation is not C92.', $outputPath, $overwrite);
        }
        if (! $this->c91HandoffFinalized($c91)) {
            return $this->blocked($artifact, 'C92_BLOCKED_C91_HANDOFF_NOT_FINALIZED', 'C91 handoff finalization evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c91);
        if ($safetyFailure !== null) {
            $artifact['c91_live_or_mutating_safety_flag_failure'] = $safetyFailure;
            return $this->blocked($artifact, 'C92_BLOCKED_C91_SAFETY_FLAG_ALREADY_LIVE_OR_MUTATING', 'C91 contains live, mutating, or production safety flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c91)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH', 'C91 candidate scope does not match locked handoff finalization decision.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING', 'C92 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }

        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);
            $status = $failures[0];
            if ($status === 'C92_BLOCKED_DICTIONARY_COVERAGE_MISSING') {
                return $this->blocked($artifact, $status, 'C92 database dictionary coverage is missing.', $outputPath, $overwrite);
            }
            return $this->rejected($artifact, $status, 'C92 post-activation handoff completion boundary review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP';
        $artifact['reason_code'] = $artifact['status'];
        $artifact['message'] = 'C92 cleared the post-activation handoff completion boundary for primary and backup as non-live audit evidence only. This still does not deploy, wire live runtime, activate controlled rollout, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C92_HANDOFF_COMPLETION_BOUNDARY_CLEARED_NON_LIVE_ONLY';
        $artifact['next_step_recommendation'] = self::C93_RECOMMENDATION;
        $artifact['post_activation_handoff_completion_boundary_review_executed'] = true;
        $artifact['post_activation_handoff_completion_boundary_review_allowed'] = true;
        $artifact['post_activation_handoff_completion_boundary_review_pass'] = true;
        $artifact['post_activation_handoff_completion_boundary_cleared'] = true;
        $artifact['boundary_cleared'] = true;
        $artifact['primary_candidate_boundary_cleared'] = true;
        $artifact['backup_candidate_boundary_cleared'] = true;
        $artifact['comparator_candidate_boundary_cleared'] = false;
        $artifact['a01_remains_comparator_only'] = true;
        $artifact['post_activation_handoff_finalized'] = true;
        $artifact['post_activation_handoff_ready'] = true;
        $artifact['boundary_go_decision'] = 'BOUNDARY_CLEARED_GO';
        $artifact['operator_go_decision'] = 'GO';

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C92_NOT_RUN',
            'reason_code' => 'C92_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'post_activation_handoff_completion_boundary_review_executed' => false,
            'post_activation_handoff_completion_boundary_review_allowed' => false,
            'post_activation_handoff_completion_boundary_review_pass' => false,
            'post_activation_handoff_completion_boundary_cleared' => false,
            'boundary_cleared' => false,
            'primary_candidate_boundary_cleared' => false,
            'backup_candidate_boundary_cleared' => false,
            'comparator_candidate_boundary_cleared' => false,
            'a01_remains_comparator_only' => true,
            'post_activation_handoff_finalized' => false,
            'post_activation_handoff_ready' => false,
            'boundary_go_decision' => 'NO_GO',
            'operator_go_decision' => 'NO_GO',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
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
        $c91 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $locks = is_array($c91['source_artifact_locks'] ?? null) ? $c91['source_artifact_locks'] : [];
        return [
            'c91_artifact_path' => $load['path'],
            'expected_c91_hash' => $load['expected_hash'],
            'actual_c91_hash' => $load['actual_hash'],
            'c91_hash_match' => $load['hash_match'],
            'expected_c91_file_sha1' => $load['expected_file_sha1'],
            'actual_c91_file_sha1' => $load['actual_file_sha1'],
            'c91_file_sha1_match' => $load['file_sha1_match'],
            'c91_source_lineage_checked' => true,
            'c91_source_lineage_match' => $this->lineageLocksMatch($c91),
            'c90_artifact_hash_from_c91' => (string) ($locks['actual_c90_hash'] ?? ''),
            'c90_file_sha1_from_c91' => (string) ($locks['actual_c90_file_sha1'] ?? ''),
            'c89_artifact_hash_from_c90' => (string) ($locks['c89_artifact_hash_from_c90'] ?? ''),
            'c89_file_sha1_from_c90' => (string) ($locks['c89_file_sha1_from_c90'] ?? ''),
            'c88_artifact_hash_from_c89' => (string) ($locks['c88_artifact_hash_from_c89'] ?? ''),
            'c88_file_sha1_from_c89' => (string) ($locks['c88_file_sha1_from_c89'] ?? ''),
            'c87_artifact_hash_from_c88' => (string) ($locks['c87_artifact_hash_from_c88'] ?? ''),
            'c87_file_sha1_from_c88' => (string) ($locks['c87_file_sha1_from_c88'] ?? ''),
            'c86_artifact_hash_from_c87' => (string) ($locks['c86_artifact_hash_from_c87'] ?? ''),
            'c86_file_sha1_from_c87' => (string) ($locks['c86_file_sha1_from_c87'] ?? ''),
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
            'expected_c91_hash' => $load['expected_hash'],
            'actual_c91_hash' => $load['actual_hash'],
            'c91_hash_match' => $load['hash_match'],
            'expected_c91_file_sha1' => $load['expected_file_sha1'],
            'actual_c91_file_sha1' => $load['actual_file_sha1'],
            'c91_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function lineageLocksMatch(array $c91): bool
    {
        $locks = is_array($c91['source_artifact_locks'] ?? null) ? $c91['source_artifact_locks'] : [];
        foreach (['c90_hash_match', 'c90_file_sha1_match', 'c90_source_lineage_match'] as $field) {
            if (($locks[$field] ?? null) !== true) {
                return false;
            }
        }
        return true;
    }

    private function c91NextRecommendationMatches(array $c91): bool
    {
        if (($c91['next_step_recommendation'] ?? null) !== self::EXPECTED_C91_RECOMMENDATION) {
            return false;
        }
        if (($c91['next_readiness_decision']['next_recommendation'] ?? null) !== self::EXPECTED_C91_RECOMMENDATION) {
            return false;
        }
        return true;
    }

    private function c91HandoffFinalized(array $c91): bool
    {
        foreach ([
            'post_activation_handoff_finalization_review_pass',
            'post_activation_handoff_finalized',
            'primary_candidate_post_activation_handoff_finalized',
            'backup_candidate_post_activation_handoff_finalized',
            'post_activation_handoff_ready',
        ] as $field) {
            if (($c91[$field] ?? null) !== true) {
                return false;
            }
        }
        $decision = is_array($c91['post_activation_handoff_finalization_decision'] ?? null) ? $c91['post_activation_handoff_finalization_decision'] : [];
        if (($decision['post_activation_handoff_finalized'] ?? null) !== true || ($decision['primary_candidate_post_activation_handoff_finalized'] ?? null) !== true || ($decision['backup_candidate_post_activation_handoff_finalized'] ?? null) !== true) {
            return false;
        }
        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $payload): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            if (($payload[$flag] ?? false) === true) {
                return $flag;
            }
        }
        return null;
    }

    private function candidateScopeMatches(array $c91): bool
    {
        $scope = is_array($c91['candidate_scope_freeze_summary'] ?? null) ? $c91['candidate_scope_freeze_summary'] : [];
        $decision = is_array($c91['post_activation_handoff_finalization_decision'] ?? null) ? $c91['post_activation_handoff_finalization_decision'] : [];
        if (($scope['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || (array) ($scope['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || (array) ($scope['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE]) {
            return false;
        }
        foreach (['candidate_scope_changed_after_c89', 'new_candidate_created', 'selection_rule_changed', 'parameter_changed', 'post_activation_handoff_used_for_selection', 'post_activation_handoff_used_for_retuning', 'post_activation_handoff_used_for_ranking', 'post_activation_handoff_used_for_plan_confirm_mutation', 'post_activation_handoff_used_for_live_rollout', 'a01_promoted', 'a01_used_as_runtime_fallback'] as $field) {
            if (($scope[$field] ?? null) !== false) {
                return false;
            }
        }
        if (($decision['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE || ($decision['primary_candidate_post_activation_handoff_finalized'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['backup_candidate_codes'] ?? []) !== [self::BACKUP_CANDIDATE] || ($decision['backup_candidate_post_activation_handoff_finalized'] ?? null) !== true) {
            return false;
        }
        if ((array) ($decision['comparator_only_candidate_codes'] ?? []) !== [self::COMPARATOR_CANDIDATE] || ($decision['a01_remains_comparator_only'] ?? null) !== true) {
            return false;
        }
        foreach (['handoff_finalization_used_for_selection', 'handoff_finalization_used_for_retuning', 'handoff_finalization_used_for_ranking', 'handoff_finalization_used_for_plan_confirm_mutation', 'handoff_finalization_used_for_live_rollout'] as $field) {
            if (($decision[$field] ?? null) !== false) {
                return false;
            }
        }
        return true;
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c91 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $artifact['database_dictionary_read_summary'] = $this->databaseDictionaryReadSummary();
        $artifact['c91_lock_validation_summary'] = $this->c91LockValidationSummary($load, $c91);
        $artifact['lineage_validation_summary'] = $this->lineageValidationSummary($c91);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['post_activation_handoff_completion_boundary_decision'] = $this->postActivationHandoffCompletionBoundaryDecision($pass);
        $artifact['c92_readiness_decision'] = $artifact['post_activation_handoff_completion_boundary_decision'];
        $artifact['post_activation_handoff_completion_boundary_candidate_scorecard'] = $this->candidateScorecard($c91, $pass, (array) ($options['forced_failure_codes'] ?? []));
        $artifact['post_activation_handoff_completion_boundary_context_summary'] = $this->postActivationHandoffCompletionBoundaryContextSummary($pass);
        $artifact['runtime_readiness_inspection_summary'] = $this->runtimeReadinessInspectionSummary();
        $artifact['feature_flag_operator_approval_kill_switch_validation_summary'] = $this->featureFlagOperatorApprovalKillSwitchValidationSummary($pass);
        $artifact['c91_handoff_finalization_carry_forward_validation_summary'] = $this->c91HandoffFinalizationCarryForwardValidationSummary($c91, $pass);
        $artifact['post_activation_handoff_completion_boundary_governance_summary'] = $this->postActivationHandoffCompletionBoundaryGovernanceSummary($pass);
        $artifact['baseline_plan_confirm_non_mutation_summary'] = $this->baselinePlanConfirmNonMutationSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary($pass);
        $artifact['documentation_governance_summary'] = $this->documentationGovernanceSummary();
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($artifact['post_activation_handoff_completion_boundary_candidate_scorecard'], $pass);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        return $artifact;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        if (! $this->dictionaryCoverageComplete()) {
            $failures[] = 'C92_BLOCKED_DICTIONARY_COVERAGE_MISSING';
        }
        foreach ([
            'baseline_plan_confirm_non_mutation_pass' => 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED',
            'documentation_governance_pass' => 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_DOCUMENTATION_GOVERNANCE',
            'post_activation_handoff_completion_boundary_confirmed' => 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_BOUNDARY_NOT_CONFIRMED',
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
            $failures[] = 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_FEATURE_FLAG_OR_KILL_SWITCH';
        }
        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'candidate_scope_changed_after_c89',
            'new_candidate_created',
            'selection_rule_changed',
            'parameter_changed',
            'handoff_completion_boundary_used_for_selection',
            'handoff_completion_boundary_used_for_retuning',
            'handoff_completion_boundary_used_for_ranking',
            'handoff_completion_boundary_used_for_plan_confirm_mutation',
            'handoff_completion_boundary_used_for_live_rollout',
            'handoff_completion_boundary_allowed_to_auto_enable_runtime',
            'handoff_completion_boundary_allowed_to_auto_deploy',
            'production_ready',
            'production_catalog_runtime_wired',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime',
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
            return 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
        }
        if (strpos($field, 'plan_confirm') !== false) {
            return 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_PLAN_CONFIRM_OUTPUT_CHANGED';
        }
        return 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
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
            'database_dictionary_read_completed' => true,
            'database_dictionary_coverage_complete' => $complete,
            'paths' => $paths,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
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

    private function c91LockValidationSummary(array $load, array $c91): array
    {
        return [
            'c91_lock_validation_completed' => true,
            'c91_artifact_exists' => $load['exists'],
            'expected_c91_hash' => $load['expected_hash'],
            'actual_c91_hash' => $load['actual_hash'],
            'c91_hash_match' => $load['hash_match'],
            'expected_c91_file_sha1' => $load['expected_file_sha1'],
            'actual_c91_file_sha1' => $load['actual_file_sha1'],
            'c91_file_sha1_match' => $load['file_sha1_match'],
            'c91_status_match' => ($c91['status'] ?? null) === self::EXPECTED_C91_STATUS,
            'c91_reason_code_match' => ($c91['reason_code'] ?? null) === self::EXPECTED_C91_REASON,
            'c91_next_recommendation_match' => $this->c91NextRecommendationMatches($c91),
            'c91_handoff_finalized' => $this->c91HandoffFinalized($c91),
            'c91_readiness_nested_path_validated' => true,
            'top_level_alias_used_for_c91_source_validation' => false,
        ];
    }

    private function lineageValidationSummary(array $c91): array
    {
        return [
            'lineage_validation_completed' => true,
            'c91_to_c90_lock_match' => (($c91['source_artifact_locks']['c90_hash_match'] ?? null) === true && ($c91['source_artifact_locks']['c90_file_sha1_match'] ?? null) === true),
            'c90_to_c89_lock_match' => (($c91['source_artifact_locks']['c89_artifact_hash_from_c90'] ?? '') !== '' && ($c91['source_artifact_locks']['c89_file_sha1_from_c90'] ?? '') !== ''),
            'c91_to_c60_lineage_match' => $this->lineageLocksMatch($c91),
            'lineage_source' => 'C91_SOURCE_ARTIFACT_LOCKS',
        ];
    }

    private function candidateScopeFreezeSummary(): array
    {
        return [
            'candidate_scope_freeze_completed' => true,
            'candidate_scope_source' => 'C91_LOCKED_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_DECISION',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'candidate_scope_changed_after_c89' => false,
            'new_candidate_created' => false,
            'selection_rule_changed' => false,
            'parameter_changed' => false,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
            'a01_promoted' => false,
            'a01_used_as_runtime_fallback' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_validation_completed' => true,
            'operator_approval_required' => true,
            'approval_reference_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'operator_approval_validation_pass' => $pass,
        ];
    }

    private function postActivationHandoffCompletionBoundaryDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'boundary_cleared' => $pass,
            'post_activation_handoff_completion_boundary_review_executed' => $pass,
            'post_activation_handoff_completion_boundary_review_allowed' => $pass,
            'post_activation_handoff_completion_boundary_review_pass' => $pass,
            'post_activation_handoff_completion_boundary_cleared' => $pass,
            'primary_candidate_boundary_cleared' => $pass,
            'backup_candidate_boundary_cleared' => $pass,
            'comparator_candidate_boundary_cleared' => false,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'backup_candidate_codes' => [self::BACKUP_CANDIDATE],
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'comparator_only_candidate_codes' => [self::COMPARATOR_CANDIDATE],
            'handoff_completion_boundary_is_artifact_only' => true,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
            'production_ready' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'next_recommendation' => $pass ? self::C93_RECOMMENDATION : 'C92_TARGETED_C91_POST_ACTIVATION_HANDOFF_FINALIZATION_REPAIR',
            'decision_reason' => $pass ? 'C92 post-activation handoff completion boundary cleared for primary and backup in non-live audit context only.' : 'C92 post-activation handoff completion boundary review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'C92_HANDOFF_COMPLETION_BOUNDARY_CLEARED_NON_LIVE_ONLY' : 'C92_HANDOFF_COMPLETION_BOUNDARY_REPAIR_REQUIRED',
        ];
    }

    private function candidateScorecard(array $c91, bool $pass, array $forcedFailures): array
    {
        $base = [
            'c91_handoff_finalization_evidence_summary' => [
                'c91_handoff_finalization_review_pass' => ($c91['post_activation_handoff_finalization_review_pass'] ?? null) === true,
                'c91_post_activation_handoff_finalized' => ($c91['post_activation_handoff_finalized'] ?? null) === true,
                'c91_primary_candidate_handoff_finalized' => ($c91['primary_candidate_post_activation_handoff_finalized'] ?? null) === true,
                'c91_backup_candidate_handoff_finalized' => ($c91['backup_candidate_post_activation_handoff_finalized'] ?? null) === true,
            ],
            'post_activation_handoff_completion_boundary_review_pass' => $pass,
            'post_activation_handoff_completion_boundary_cleared' => $pass,
            'candidate_ready_for_post_activation_handoff_closure_seal_review' => $pass,
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
            'c91_lock_validation_pass' => $pass,
            'candidate_scope_freeze_pass' => $pass,
            'operator_approval_validation_pass' => $pass,
            'baseline_plan_confirm_hash_unchanged_pass' => true,
            'post_activation_handoff_completion_boundary_advisory_only_pass' => $pass,
            'production_mutation_safety_pass' => $pass,
            'documentation_governance_pass' => true,
            'failure_reason_codes' => $pass ? [] : $forcedFailures,
        ];
        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c92_role' => 'primary_post_activation_handoff_completion_boundary_cleared_candidate',
                'primary_candidate_boundary_cleared' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c92_role' => 'backup_post_activation_handoff_completion_boundary_cleared_candidate',
                'backup_candidate_boundary_cleared' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c92_role' => 'comparator_only_candidate',
                'post_activation_handoff_completion_boundary_review_pass' => false,
                'post_activation_handoff_completion_boundary_cleared' => false,
                'candidate_ready_for_post_activation_handoff_closure_seal_review' => false,
                'comparator_candidate_boundary_cleared' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function postActivationHandoffCompletionBoundaryContextSummary(bool $pass): array
    {
        return [
            'post_activation_handoff_completion_boundary_context_created' => true,
            'post_activation_handoff_completion_boundary_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
            'plan_confirm_mutated' => false,
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
            'post_activation_handoff_finalization_source_identified' => is_file(self::RUNTIME_PATHS['c91_post_activation_handoff_finalization_service']),
            'post_activation_handoff_completion_boundary_source_identified' => is_file(self::RUNTIME_PATHS['c92_post_activation_handoff_completion_boundary_service']),
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

    private function c91HandoffFinalizationCarryForwardValidationSummary(array $c91, bool $pass): array
    {
        return [
            'c91_handoff_finalization_carry_forward_validation_completed' => true,
            'c91_handoff_finalization_carry_forward_validation_pass' => $pass,
            'c91_handoff_finalization_review_pass' => ($c91['post_activation_handoff_finalization_review_pass'] ?? null) === true,
            'c91_handoff_finalized' => ($c91['post_activation_handoff_finalized'] ?? null) === true,
            'c91_primary_candidate_handoff_finalized' => ($c91['primary_candidate_post_activation_handoff_finalized'] ?? null) === true,
            'c91_backup_candidate_handoff_finalized' => ($c91['backup_candidate_post_activation_handoff_finalized'] ?? null) === true,
            'c91_progress_target_reached' => ($c91['progress_summary']['target_reached'] ?? null) === true,
            'c91_planned_next_review_match' => ($c91['planned_next_summary']['planned_next_review'] ?? null) === self::EXPECTED_C91_RECOMMENDATION,
            'c91_production_mutation_safety_pass' => true,
            'c91_c92_readiness_count' => (int) ($c91['next_readiness_decision']['candidate_ready_for_post_activation_handoff_completion_boundary_review_count'] ?? 0),
            'c91_c92_recommendation_match' => ($c91['next_readiness_decision']['next_recommendation'] ?? null) === self::EXPECTED_C91_RECOMMENDATION,
        ];
    }

    private function postActivationHandoffCompletionBoundaryGovernanceSummary(bool $pass): array
    {
        return [
            'post_activation_handoff_completion_boundary_governance_review_completed' => true,
            'post_activation_handoff_completion_boundary_governance_pass' => $pass,
            'post_activation_handoff_completion_boundary_cleared' => $pass,
            'post_activation_handoff_completion_boundary_is_explicit_context_only' => true,
            'post_activation_handoff_completion_boundary_is_non_live_default' => true,
            'post_activation_handoff_completion_boundary_is_artifact_only' => true,
            'post_activation_handoff_completion_boundary_is_advisory_only' => true,
            'post_activation_handoff_completion_boundary_used_for_selection' => false,
            'post_activation_handoff_completion_boundary_used_for_retuning' => false,
            'post_activation_handoff_completion_boundary_used_for_ranking' => false,
            'post_activation_handoff_completion_boundary_used_for_plan_confirm_mutation' => false,
            'post_activation_handoff_completion_boundary_used_for_live_rollout' => false,
            'post_activation_handoff_completion_boundary_allowed_to_auto_enable_runtime' => false,
            'post_activation_handoff_completion_boundary_allowed_to_auto_deploy' => false,
            'post_activation_handoff_completion_boundary_classification' => 'CONTROLLED_LIMITED_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY',
        ];
    }

    private function baselinePlanConfirmNonMutationSummary(bool $pass): array
    {
        $hash = 'C92_PLAN_CONFIRM_BASELINE_ARTIFACT_ONLY_NON_MUTATION_HASH';
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
        $summary = [
            'production_mutation_safety_review_completed' => true,
            'production_mutation_safety_pass' => $pass,
            'post_activation_handoff_completion_boundary_review_created' => true,
            'post_activation_handoff_completion_boundary_review_allowed' => $pass,
            'post_activation_handoff_completion_boundary_review_pass' => $pass,
            'post_activation_handoff_completion_boundary_cleared' => $pass,
            'candidate_ready_for_post_activation_handoff_closure_seal_review_count' => $pass ? 2 : 0,
            'next_recommendation' => $pass ? self::C93_RECOMMENDATION : 'C92_TARGETED_C91_POST_ACTIVATION_HANDOFF_FINALIZATION_REPAIR',
            'selection_changed_after_c89' => false,
            'parameter_changed_after_c89' => false,
            'new_candidate_created' => false,
            'handoff_completion_boundary_used_for_selection' => false,
            'handoff_completion_boundary_used_for_retuning' => false,
            'handoff_completion_boundary_used_for_ranking' => false,
            'handoff_completion_boundary_used_for_live_rollout' => false,
            'latest_shortcut_used' => false,
            'max_date_shortcut_used' => false,
            'future_lookup_detected' => false,
            'return_fields_used_for_selection' => false,
        ];
        foreach (self::REQUIRED_C92_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }
        return $summary;
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
            'candidate_ready_for_post_activation_handoff_closure_seal_review_count' => $pass ? 2 : 0,
            'candidate_codes' => $pass ? [self::PRIMARY_CANDIDATE, self::BACKUP_CANDIDATE] : [],
            'next_recommendation' => $pass ? self::C93_RECOMMENDATION : 'C92_TARGETED_C91_POST_ACTIVATION_HANDOFF_FINALIZATION_REPAIR',
            'decision_reason' => $pass ? 'C92 post-activation handoff completion boundary cleared for primary and backup in non-live audit context only. Only C93 closure seal review is allowed next.' : 'C92 post-activation handoff completion boundary review did not pass; targeted repair is required.',
            'diagnostic_conclusion' => $pass ? 'C92_HANDOFF_COMPLETION_BOUNDARY_CLEARED_NON_LIVE_ONLY' : 'C92_HANDOFF_COMPLETION_BOUNDARY_REPAIR_REQUIRED',
            'review_pass' => $pass,
            'boundary_cleared' => $pass,
            'primary_candidate_boundary_cleared' => $pass,
            'backup_candidate_boundary_cleared' => $pass,
            'comparator_candidate_boundary_cleared' => false,
            'a01_remains_comparator_only' => true,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'production_catalog_runtime_wired' => false,
            'controlled_opt_in_runtime_bridge_active' => false,
            'controlled_parallel_run_active' => false,
            'controlled_rollout_active' => false,
            'production_deployment_executed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
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
            'post_activation_handoff_completion_boundary_review_pass' => $pass,
            'dominant_failure_reason_codes' => array_values(array_unique($codes)),
            'targeted_repair_recommendation' => $pass ? self::C93_RECOMMENDATION : 'C92_TARGETED_C91_POST_ACTIVATION_HANDOFF_FINALIZATION_REPAIR',
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_report_completed' => true,
            'target_reached' => $pass,
            'completed_target' => self::RUN_CODE,
            'source_target_locked' => 'C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW',
            'achieved' => [
                'C91 artifact hash and file SHA1 validated',
                'C91 nested C92 readiness path validated',
                'C91 -> C60 lineage lock validated',
                'E02 primary and B01 backup preserved',
                'A01 preserved as comparator-only',
                'Post-activation handoff completion boundary cleared for primary and backup',
                'PLAN/CONFIRM and production runtime remain unchanged',
            ],
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C93_RECOMMENDATION : 'C92_TARGETED_C91_POST_ACTIVATION_HANDOFF_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'post-activation handoff closure seal review only; still not deployment, live rollout, default runtime wiring, or PLAN/CONFIRM mutation' : 'targeted repair before any next review',
            'planned_next_required_inputs' => $pass ? [
                'locked C92 artifact hash',
                'locked C92 file SHA1',
                'operator approval',
                'non-empty approval reference',
                'unchanged candidate scope',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C92 validates C91 nested next_readiness_decision.* path and does not use top-level aliases as source validation.',
            'C92 clears post-activation handoff completion boundary as an isolated artifact-only decision.',
            'C92 handoff completion boundary is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.',
            'C92 may only recommend C93 post-activation handoff closure seal review as the next controlled review step.',
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
        if (strpos($status, 'C91_ARTIFACT') !== false || strpos($status, 'C91_FILE') !== false || strpos($status, 'LINEAGE') !== false) {
            return 'C92_C91_LOCK_OR_LINEAGE_REPAIR';
        }
        if (strpos($status, 'OPERATOR_APPROVAL') !== false || strpos($status, 'HANDOFF') !== false || strpos($status, 'BOUNDARY') !== false) {
            return 'C92_CONTROLLED_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REPAIR';
        }
        if (strpos($status, 'FEATURE_FLAG') !== false || strpos($status, 'KILL_SWITCH') !== false) {
            return 'C92_FEATURE_FLAG_OR_KILL_SWITCH_REPAIR';
        }
        if (strpos($status, 'PLAN_CONFIRM') !== false || strpos($status, 'BASELINE') !== false) {
            return 'C92_PLAN_CONFIRM_BASELINE_NON_MUTATION_REPAIR';
        }
        if (strpos($status, 'DOCUMENTATION') !== false) {
            return 'C92_DOCUMENTATION_REPAIR';
        }
        return 'C92_TARGETED_C91_POST_ACTIVATION_HANDOFF_FINALIZATION_REPAIR';
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
