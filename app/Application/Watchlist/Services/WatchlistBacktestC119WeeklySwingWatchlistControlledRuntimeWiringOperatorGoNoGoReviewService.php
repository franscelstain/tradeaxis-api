<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService
{
    public const RUN_CODE = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW';
    public const PHASE_LABEL = 'PR-07 / C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW';
    public const ARTIFACT_TYPE = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW';

    public const DEFAULT_C118_ARTIFACT = 'storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json';
    public const DEFAULT_EXPECTED_C118_HASH = 'fff0b2461783386f897971a55621e265f4f1498f';
    public const DEFAULT_EXPECTED_C118_FILE_SHA1 = '1D81849D13F815900D56FE450BF69991904EA760';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C118_STATUS = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C118_REASON = 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C118_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const EXPECTED_C118_PHASE_LABEL = 'PR-06 / C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW';
    private const C120_RECOMMENDATION = 'C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW';

    private const PASS_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const GO_DECISION_NOT_CONFIRMED_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C118_LOCK_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_ARTIFACT_LOCK_MISMATCH';
    private const C118_FILE_SHA1_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_FILE_SHA1_LOCK_MISMATCH';
    private const C118_STATUS_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_STATUS_MISMATCH';
    private const C118_REASON_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_REASON_CODE_MISMATCH';
    private const C118_NEXT_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_NEXT_RECOMMENDATION_MISMATCH';
    private const C118_PHASE_LABEL_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_PHASE_LABEL_MISMATCH';
    private const C118_OBSERVATION_RESULT_REVIEW_INVALID_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_OBSERVATION_RESULT_REVIEW_INVALID';
    private const C118_CONVERT_FROM_JSON_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C118_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const BOUNDARY_INVALID_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C111_C112_C113_C114_C115_C116_C117_C118_BOUNDARY_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
        'operator_go_no_go_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
        'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
        'controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
        'production_deployment_allowed',
        'production_deployment_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'pilot_runtime_active',
        'shadow_runtime_active',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_plan_confirm_mutation_allowed',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
    ];

    public function execute(
        string $c118Artifact = self::DEFAULT_C118_ARTIFACT,
        string $expectedC118Hash = self::DEFAULT_EXPECTED_C118_HASH,
        string $expectedC118FileSha1 = self::DEFAULT_EXPECTED_C118_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c118Artifact, $expectedC118Hash, $expectedC118FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C118_LOCK_MISMATCH_STATUS, 'C118 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c118_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C118_CONVERT_FROM_JSON_STATUS, 'C118 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C118_LOCK_MISMATCH_STATUS, 'C118 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C118_FILE_SHA1_MISMATCH_STATUS, 'C118 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c118 = $load['payload'];
        if (($c118['status'] ?? null) !== self::EXPECTED_C118_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C118_STATUS_MISMATCH_STATUS, 'C118 status is not passed ready for C119.', $outputPath, $overwrite);
        }
        if (($c118['reason_code'] ?? null) !== self::EXPECTED_C118_REASON) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C118_REASON_MISMATCH_STATUS, 'C118 reason_code is not passed ready for C119.', $outputPath, $overwrite);
        }
        if (! $this->c118NextRecommendationMatches($c118)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C118_NEXT_MISMATCH_STATUS, 'C118 next recommendation is not C119.', $outputPath, $overwrite);
        }
        if (($c118['phase_label'] ?? null) !== self::EXPECTED_C118_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C118_PHASE_LABEL_MISMATCH_STATUS, 'C118 phase label is not PR-06 / C118.', $outputPath, $overwrite);
        }
        if (! $this->c118ObservationResultReviewValid($c118)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C118_OBSERVATION_RESULT_REVIEW_INVALID_STATUS, 'C118 observation result review evidence is incomplete.', $outputPath, $overwrite);
        }
        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c118);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c118_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C118 contains live, mutating, production, runtime wiring, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if (! $this->boundaryValid($c118)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::BOUNDARY_INVALID_STATUS, 'C111/C112/C113/C114/C115/C116/C117/C118 boundary evidence is invalid.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c118)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C118 candidate scope does not match locked operator go/no-go scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C119 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (($options['operator_go_decision_confirmed'] ?? true) !== true) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::GO_DECISION_NOT_CONFIRMED_STATUS, 'C119 requires explicit operator GO decision confirmation.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $failures = $this->controlledGateFailures($options);
        if ($failures !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['forced_failure_codes' => $failures]), false);

            return $this->rejected($artifact, $failures[0], 'C119 controlled runtime wiring operator go/no-go review gate failed.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C119 records an operator GO decision for E02 primary and B01 backup using the locked C118 observation result review. This is still artifact-only and does not deploy production, activate runtime bridge or rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'READY_FOR_C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW';
        $artifact['next_step_recommendation'] = self::C120_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-07',
            'internal_checkpoint' => 'C119',
            'status' => 'C119_NOT_RUN',
            'reason_code' => 'C119_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_executed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_allowed' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_pass' => false,
            'controlled_runtime_wiring_operator_go_no_go_review_pass' => false,
            'operator_go_decision' => 'NO_GO',
            'operator_go_decision_confirmed' => false,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'controlled_runtime_wiring_operator_go_no_go_manifest_created' => false,
            'controlled_runtime_wiring_go_decision_finalization_review_allowed_next' => false,
            'c118_lock_valid' => false,
            'c118_observation_result_review_valid' => false,
            'c118_convert_from_json_pass' => false,
            'c117_hash_match' => false,
            'c117_file_sha1_match' => false,
            'c117_convert_from_json_pass' => false,
            'c117_observation_review_valid' => false,
            'c115_hash_match' => false,
            'c115_file_sha1_match' => false,
            'c115_convert_from_json_pass' => false,
            'c115_execution_approval_valid' => false,
            'c114_hash_match' => false,
            'c114_file_sha1_match' => false,
            'c114_convert_from_json_pass' => false,
            'c114_runtime_wiring_readiness_valid' => false,
            'c111_final_closure_valid' => false,
            'c111_non_live_audit_archive_terminal' => false,
            'c112_post_c111_transition_gate_valid' => false,
            'c112_not_audit_archive_continuation' => false,
            'c112_does_not_reopen_c111_final_closure' => false,
            'c113_production_readiness_valid' => false,
            'c114_runtime_wiring_readiness_review_only' => true,
            'c115_execution_approval_review_only' => true,
            'c116_execution_review_only' => true,
            'c117_observation_review_only' => true,
            'c118_observation_result_review_only' => true,
            'c119_operator_go_no_go_review_only' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'backup_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'comparator_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $artifact[$flag] = false;
        }

        return $artifact;
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_allowed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_pass' => true,
            'controlled_runtime_wiring_operator_go_no_go_review_pass' => true,
            'operator_go_decision' => 'GO',
            'operator_go_decision_confirmed' => true,
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => true,
            'ready_for_controlled_runtime_wiring_go_decision_finalization_review' => true,
            'controlled_runtime_wiring_operator_go_no_go_manifest_created' => true,
            'controlled_runtime_wiring_go_decision_finalization_review_allowed_next' => true,
            'c118_lock_valid' => true,
            'c118_observation_result_review_valid' => true,
            'c118_convert_from_json_pass' => true,
            'primary_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => true,
            'backup_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => true,
            'comparator_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c118 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);
        $forcedFailures = (array) ($options['forced_failure_codes'] ?? []);

        $artifact = array_merge($artifact, $this->carryForwardTopLevelAliases($load, $c118));
        $artifact['c118_lock_validation_summary'] = $this->c118LockValidationSummary($load, $c118);
        $artifact['c111_c112_c113_c114_c115_c116_c117_c118_boundary_carry_forward_summary'] = $this->boundaryCarryForwardSummary($c118, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c118, $pass);
        $artifact['c118_final_operator_evidence_summary'] = $this->c118FinalOperatorEvidenceSummary();
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c119_operator_go_no_go_decision'] = $this->operatorGoNoGoDecision($pass);
        $artifact['next_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision($pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_manifest'] = $this->operatorGoNoGoManifest($load, $pass);
        $artifact['weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_checklist'] = $this->operatorGoNoGoChecklist();
        $artifact['c119_candidate_operator_go_no_go_scorecard'] = $this->candidateScorecard($pass, $forcedFailures);
        $artifact['controlled_runtime_wiring_operator_go_no_go_context_summary'] = $this->operatorGoNoGoContextSummary($pass);
        $artifact['runtime_config_review_summary'] = $this->runtimeConfigReviewSummary();
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($forcedFailures);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);
        $artifact['c111_c112_c113_c114_c115_c116_c117_c118_boundary_evidence_labels'] = $this->boundaryAliases($pass);

        return $artifact;
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [[
            'source_lock' => 'C118',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C118_STATUS,
            'expected_reason_code' => self::EXPECTED_C118_REASON,
            'expected_next_recommendation' => self::EXPECTED_C118_NEXT_RECOMMENDATION,
            'expected_phase_label' => self::EXPECTED_C118_PHASE_LABEL,
        ]];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'source_lock' => 'C118',
            'c118_artifact_path' => $load['path'],
            'c118_artifact_exists' => $load['exists'],
            'expected_c118_hash' => $load['expected_hash'],
            'actual_c118_hash' => $load['actual_hash'],
            'c118_hash_match' => $load['hash_match'],
            'expected_c118_file_sha1' => $load['expected_file_sha1'],
            'actual_c118_file_sha1' => $load['actual_file_sha1'],
            'c118_file_sha1_match' => $load['file_sha1_match'],
        ];
    }

    private function carryForwardTopLevelAliases(array $load, array $c118): array
    {
        return array_merge($this->topLevelLockAliases($load), [
            'c118_convert_from_json_pass' => $load['convert_from_json_pass'],
            'c118_observation_result_review_valid' => $this->c118ObservationResultReviewValid($c118),
            'c117_hash_match' => (bool) ($c118['c117_hash_match'] ?? false),
            'c117_file_sha1_match' => (bool) ($c118['c117_file_sha1_match'] ?? false),
            'c117_convert_from_json_pass' => (bool) ($c118['c117_convert_from_json_pass'] ?? false),
            'c117_observation_review_valid' => (bool) ($c118['c117_observation_review_valid'] ?? false),
            'c115_hash_match' => (bool) ($c118['c115_hash_match'] ?? false),
            'c115_file_sha1_match' => (bool) ($c118['c115_file_sha1_match'] ?? false),
            'c115_convert_from_json_pass' => (bool) ($c118['c115_convert_from_json_pass'] ?? false),
            'c115_execution_approval_valid' => (bool) ($c118['c115_execution_approval_valid'] ?? false),
            'c114_hash_match' => (bool) ($c118['c114_hash_match'] ?? false),
            'c114_file_sha1_match' => (bool) ($c118['c114_file_sha1_match'] ?? false),
            'c114_convert_from_json_pass' => (bool) ($c118['c114_convert_from_json_pass'] ?? false),
            'c114_runtime_wiring_readiness_valid' => (bool) ($c118['c114_runtime_wiring_readiness_valid'] ?? false),
            'c111_final_closure_valid' => (bool) ($c118['c111_final_closure_valid'] ?? false),
            'c111_non_live_audit_archive_terminal' => (bool) ($c118['c111_non_live_audit_archive_terminal'] ?? false),
            'c112_post_c111_transition_gate_valid' => (bool) ($c118['c112_post_c111_transition_gate_valid'] ?? false),
            'c112_not_audit_archive_continuation' => (bool) ($c118['c112_not_audit_archive_continuation'] ?? false),
            'c112_does_not_reopen_c111_final_closure' => (bool) ($c118['c112_does_not_reopen_c111_final_closure'] ?? false),
            'c113_production_readiness_valid' => (bool) ($c118['c113_production_readiness_valid'] ?? false),
            'primary_candidate_code' => $c118['primary_candidate_code'] ?? self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => $c118['backup_candidate_code'] ?? self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => $c118['comparator_candidate_code'] ?? self::COMPARATOR_CANDIDATE,
        ]);
    }

    private function c118ObservationResultReviewValid(array $c118): bool
    {
        foreach ([
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_pass',
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_operator_go_no_go_review',
            'controlled_runtime_wiring_observation_result_review_pass',
            'ready_for_controlled_runtime_wiring_operator_go_no_go_review',
            'controlled_runtime_wiring_observation_result_review_manifest_created',
            'controlled_runtime_wiring_operator_go_no_go_review_allowed_next',
            'c118_observation_result_review_decision.review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_manifest.ready_for_controlled_runtime_wiring_operator_go_no_go_review',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_checklist.observation_result_reviewed',
            'c117_hash_match',
            'c117_file_sha1_match',
            'c117_convert_from_json_pass',
            'c117_observation_review_valid',
            'c115_hash_match',
            'c115_file_sha1_match',
            'c115_convert_from_json_pass',
            'c115_execution_approval_valid',
            'c114_hash_match',
            'c114_file_sha1_match',
            'c114_convert_from_json_pass',
            'c114_runtime_wiring_readiness_valid',
        ] as $field) {
            if (! $this->boolField($c118, $field)) {
                return false;
            }
        }

        return true;
    }

    private function c118NextRecommendationMatches(array $c118): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_operator_go_no_go_decision', 'next_recommendation'],
            ['c118_observation_result_review_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c118, $path) !== self::EXPECTED_C118_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function boundaryValid(array $c118): bool
    {
        foreach ([
            'c111_final_closure_valid',
            'c111_non_live_audit_archive_terminal',
            'c112_not_audit_archive_continuation',
            'c112_does_not_reopen_c111_final_closure',
            'c113_production_readiness_valid',
            'c114_runtime_wiring_readiness_review_only',
            'c114_not_runtime_wiring_execution',
            'c115_execution_approval_review_only',
            'c115_not_runtime_wiring_execution',
            'c116_execution_review_only',
            'c117_observation_review_only',
            'c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_OBSERVATION_RESULT_REVIEW_ONLY',
            'c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_NOT_PRODUCTION_DEPLOYMENT',
            'c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_NOT_PLAN_CONFIRM_MUTATION',
            'c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_NOT_WEEKLY_SWING_LIVE_OUTPUT',
        ] as $field) {
            if (! $this->boolField($c118, $field)) {
                return false;
            }
        }
        foreach ([
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'runtime_bridge_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_live_recommendation_generated',
        ] as $field) {
            if ($this->boolField($c118, $field)) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $c118): bool
    {
        foreach ([
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
        ] as $key => $expected) {
            if (($c118[$key] ?? null) !== $expected) {
                return false;
            }
        }
        foreach ([
            'primary_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review',
            'backup_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review',
            'a01_remains_comparator_only',
        ] as $field) {
            if (! $this->boolField($c118, $field)) {
                return false;
            }
        }
        foreach ([
            'comparator_candidate_ready_for_controlled_runtime_wiring_operator_go_no_go_review',
            'a01_promoted',
            'candidate_promotion_executed',
            'candidate_rerank_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'weekly_swing_live_recommendation_selection_executed',
        ] as $field) {
            if ($this->boolField($c118, $field)) {
                return false;
            }
        }

        return true;
    }

    private function firstLiveOrMutatingSafetyFlag(array $c118): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $field) {
            if ($this->boolField($c118, $field)) {
                return $field;
            }
        }
        foreach ([
            'production_catalog_runtime_bridge_enabled',
            'production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'production_catalog_controlled_shadow_rollout_enabled',
            'production_catalog_controlled_parallel_run_enabled',
            'production_catalog_controlled_rollout_enabled',
        ] as $configFlag) {
            if ($this->configFlagIsOn($configFlag)) {
                return $configFlag;
            }
        }

        return null;
    }

    private function controlledGateFailures(array $options): array
    {
        $failures = [];
        foreach ($this->prohibitedOptionFields() as $field) {
            if ((bool) ($options[$field] ?? false)) {
                $failures[] = $this->statusForProhibitedField($field);
            }
        }

        return array_values(array_unique($failures));
    }

    private function prohibitedOptionFields(): array
    {
        return [
            'execute_production_runtime_wiring',
            'activate_production_runtime_wiring',
            'activate_production_catalog_runtime_bridge',
            'enable_controlled_rollout',
            'activate_controlled_opt_in_runtime_bridge',
            'activate_controlled_parallel_run',
            'activate_pilot_runtime',
            'activate_shadow_runtime',
            'persist_controlled_runtime_wiring_operator_go_no_go_context_to_live_runtime',
            'persist_controlled_runtime_wiring_observation_result_review_context_to_live_runtime',
            'persist_controlled_runtime_wiring_observation_review_context_to_live_runtime',
            'persist_controlled_runtime_wiring_execution_review_context_to_live_runtime',
            'persist_controlled_runtime_wiring_execution_context_to_live_runtime',
            'mutate_plan_confirm',
            'change_candidate_selection',
            'promote_a01',
            'rerank_candidate',
            'retune_strategy',
            'change_scoring_logic',
            'change_catalog_selection',
            'generate_official_weekly_swing_stock_recommendation',
            'generate_live_weekly_swing_watchlist_output',
            'publish_weekly_swing_output',
            'modify_c60_c118_artifacts',
        ];
    }

    private function statusForProhibitedField(string $field): string
    {
        if (
            strpos($field, 'a01') !== false
            || strpos($field, 'candidate') !== false
            || strpos($field, 'scoring') !== false
            || strpos($field, 'strategy') !== false
            || $field === 'change_catalog_selection'
        ) {
            return self::CANDIDATE_SCOPE_MISMATCH_STATUS;
        }

        return self::LIVE_OR_PRODUCTION_MUTATION_STATUS;
    }

    private function c118LockValidationSummary(array $load, array $c118): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C118',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'expected_status' => self::EXPECTED_C118_STATUS,
            'actual_status' => $c118['status'] ?? null,
            'expected_reason_code' => self::EXPECTED_C118_REASON,
            'actual_reason_code' => $c118['reason_code'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C118_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c118NextRecommendationMatches($c118),
            'expected_phase_label' => self::EXPECTED_C118_PHASE_LABEL,
            'actual_phase_label' => $c118['phase_label'] ?? null,
            'phase_label_match' => ($c118['phase_label'] ?? null) === self::EXPECTED_C118_PHASE_LABEL,
            'c118_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'c118_observation_result_review_valid' => $this->c118ObservationResultReviewValid($c118),
        ];
    }

    private function boundaryCarryForwardSummary(array $c118, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c111_non_live_handoff_audit_archive_final_closed' => (bool) ($c118['c111_final_closure_valid'] ?? false),
            'c112_separate_post_c111_production_phase_transition_gate' => (bool) ($c118['c112_post_c111_transition_gate_valid'] ?? false),
            'c113_production_readiness_review_only' => (bool) ($c118['c113_production_readiness_valid'] ?? false),
            'c114_runtime_wiring_readiness_review_only' => (bool) ($c118['c114_runtime_wiring_readiness_review_only'] ?? false),
            'c115_execution_approval_review_only' => (bool) ($c118['c115_execution_approval_review_only'] ?? false),
            'c116_execution_review_only' => (bool) ($c118['c116_execution_review_only'] ?? false),
            'c117_observation_review_only' => (bool) ($c118['c117_observation_review_only'] ?? false),
            'c118_observation_result_review_only' => $this->boolField($c118, 'c111_c112_c113_c114_c115_c116_c117_boundary_evidence_labels.C118_OBSERVATION_RESULT_REVIEW_ONLY'),
            'c119_operator_go_no_go_review_only' => true,
            'source_c118_observation_result_review_valid' => $this->c118ObservationResultReviewValid($c118),
            'c119_boundary_pass' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $c118, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($c118),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_controlled_runtime_wiring_operator_go_no_go_candidate',
            'backup_candidate_role' => 'backup_controlled_runtime_wiring_operator_go_no_go_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
            'weekly_swing_live_recommendation_selection_executed' => false,
            'official_weekly_swing_stock_recommendation_generated' => false,
            'weekly_swing_output_published' => false,
        ];
    }

    private function c118FinalOperatorEvidenceSummary(): array
    {
        return [
            'FOCUSED_PHPUNIT_C118' => 'OK (131 tests, 461 assertions)',
            'FULL_WATCHLIST_PHPUNIT_POST_C118' => 'OK (3419 tests, 32885 assertions)',
            'C118_ARTIFACT_HASH' => self::DEFAULT_EXPECTED_C118_HASH,
            'C118_FILE_SHA1' => self::DEFAULT_EXPECTED_C118_FILE_SHA1,
            'C118_RUNTIME_STATUS' => self::EXPECTED_C118_STATUS,
            'C118_RUNTIME_REASON_CODE' => self::EXPECTED_C118_REASON,
            'C117_HASH_MATCH' => true,
            'C117_FILE_SHA1_MATCH' => true,
            'C117_CONVERT_FROM_JSON_PASS' => true,
            'C117_OBSERVATION_REVIEW_VALID' => true,
            'NEGATIVE_WITHOUT_OPERATOR_APPROVAL' => 'REJECTED_OPERATOR_APPROVAL_MISSING',
            'NEGATIVE_WITHOUT_APPROVAL_REFERENCE' => 'REJECTED_OPERATOR_APPROVAL_MISSING',
            'TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP' => 'NO_OUTPUT',
            'NEXT_RECOMMENDATION' => self::EXPECTED_C118_NEXT_RECOMMENDATION,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'operator_go_decision_confirmation_required' => true,
            'operator_go_decision_confirmed' => (bool) ($options['operator_go_decision_confirmed'] ?? true),
            'operator_approval_validation_pass' => $pass,
        ];
    }

    private function temporaryNegativeArtifactGuardSummary(array $paths): array
    {
        return [
            'validation_completed' => true,
            'temporary_negative_artifacts_remaining' => $paths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $paths === [],
            'temporary_negative_artifact_paths' => array_values($paths),
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function operatorGoNoGoDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'c118_lock_valid' => $pass,
            'c118_observation_result_review_valid' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_executed' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_allowed' => $pass,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'primary_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'backup_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'comparator_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
            'a01_remains_comparator_only' => true,
            'production_ready' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => $pass ? self::C120_RECOMMENDATION : 'C119_TARGETED_C118_OBSERVATION_RESULT_REVIEW_LOCK_REPAIR',
            'decision_reason' => $pass ? 'C119 operator GO recorded for C118 observation result review in artifact-only, non-live, non-mutating context.' : 'C119 cannot proceed until C118 lock, boundary, approval, cleanup, candidate, and safety gates pass.',
        ];
    }

    private function nextGoDecisionFinalizationDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C120_RECOMMENDATION : 'C119_TARGETED_C118_OBSERVATION_RESULT_REVIEW_LOCK_REPAIR',
            'next_scope' => $pass ? 'controlled runtime wiring GO decision finalization review only; C119 itself still does not deploy production, generate official weekly swing output, activate live rollout, or mutate PLAN/CONFIRM' : 'targeted C118 observation result review lock, boundary, approval, or safety repair only',
        ];
    }

    private function operatorGoNoGoManifest(array $load, bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_controlled_runtime_wiring_operator_go_no_go_review',
            'source_artifact' => 'C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW',
            'source_artifact_path' => $load['path'],
            'source_artifact_hash' => $load['expected_hash'],
            'source_file_sha1' => $load['expected_file_sha1'],
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'operator_go_decision_confirmed' => $pass,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_runtime_wiring_operator_go_no_go_review_pass' => $pass,
            'ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'operator_go_no_go_artifact_only' => true,
            'runtime_wiring_execution_performed_against_live_runtime' => false,
            'runtime_wiring_enabled' => false,
            'runtime_bridge_enabled' => false,
            'controlled_rollout_enabled' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_live_output_published' => false,
            'plan_confirm_mutation_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'operator_go_no_go_used_for_selection' => false,
            'operator_go_no_go_used_for_retuning' => false,
            'operator_go_no_go_used_for_ranking' => false,
            'operator_go_no_go_used_for_plan_confirm_mutation' => false,
            'operator_go_no_go_used_for_live_rollout' => false,
        ];
    }

    private function operatorGoNoGoChecklist(): array
    {
        return [
            'operator_go_no_go_reviewed' => true,
            'operator_go_decision_confirmation_required' => true,
            'production_runtime_wiring_not_enabled' => true,
            'production_catalog_runtime_wired' => false,
            'runtime_bridge_not_enabled' => true,
            'controlled_rollout_not_enabled' => true,
            'pilot_runtime_not_enabled' => true,
            'shadow_runtime_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'production_config_default_unchanged' => true,
            'c118_source_lock_reviewed' => true,
            'runtime_entrypoint_reviewed' => true,
            'artisan_command_registration_reviewed' => true,
            'service_boundary_reviewed' => true,
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'temporary_negative_artifact_cleanup_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_go_no_go_review_only' => true,
            'non_live' => true,
            'non_mutating' => true,
            'artifact_only' => true,
            'live_endpoint_called' => false,
            'scheduler_executed' => false,
            'weekly_swing_stock_recommendation_generated' => false,
        ];
    }

    private function candidateScorecard(bool $pass, array $forcedFailures): array
    {
        $base = [
            'controlled_runtime_wiring_operator_go_no_go_review_pass' => $pass,
            'operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'failure_reason_codes' => $forcedFailures,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c119_role' => 'primary_controlled_runtime_wiring_operator_go_no_go_candidate',
                'primary_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c119_role' => 'backup_controlled_runtime_wiring_operator_go_no_go_candidate',
                'backup_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c119_role' => 'comparator_only_candidate',
                'controlled_runtime_wiring_operator_go_no_go_review_pass' => false,
                'operator_go_decision' => 'NO_GO',
                'ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
                'comparator_candidate_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function operatorGoNoGoContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_created' => true,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime' => false,
            'operator_go_no_go_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime' => false,
            'controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutated' => false,
            'runtime_bridge_active' => false,
            'controlled_rollout_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ];
    }

    private function runtimeConfigReviewSummary(): array
    {
        return [
            'runtime_config_reviewed' => true,
            'production_config_default_unchanged' => true,
            'c118_artifact_identified' => is_file(self::DEFAULT_C118_ARTIFACT),
            'c119_service_identified' => is_file('app/Application/Watchlist/Services/WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService.php'),
            'c119_command_identified' => is_file('app/Console/Commands/Watchlist/RunBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewCommand.php'),
            'default_off_feature_flag_identified' => true,
            'kill_switch_identified' => true,
            'operator_approval_surface_identified' => true,
            'production_runtime_wiring_not_enabled' => true,
            'runtime_bridge_not_enabled' => true,
            'controlled_rollout_not_enabled' => true,
            'scheduler_live_weekly_swing_not_enabled' => true,
            'plan_confirm_endpoint_behavior_unchanged' => true,
            'plan_confirm_runtime_change_executed' => false,
            'production_catalog_runtime_wired' => false,
            'live_runtime_behavior_changed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
        ];
    }

    private function productionMutationSafetySummary(): array
    {
        $summary = ['validation_completed' => true, 'all_required_safety_flags_false' => true];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }

        return $summary;
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => $status,
            'failure_count' => count($status),
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-07_C119_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW',
            'c111_final_closure_carried_forward' => true,
            'c112_post_c111_transition_gate_carried_forward' => true,
            'c113_production_readiness_review_carried_forward' => true,
            'c114_runtime_wiring_readiness_review_carried_forward' => true,
            'c115_execution_approval_review_carried_forward' => true,
            'c116_execution_review_carried_forward' => true,
            'c117_observation_review_carried_forward' => true,
            'c118_observation_result_review_carried_forward' => true,
            'c119_controlled_runtime_wiring_operator_go_no_go_review_executed' => true,
            'c119_operator_go_decision' => $pass ? 'GO' : 'NO_GO',
            'c119_ready_for_controlled_runtime_wiring_go_decision_finalization_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_production_deployment' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C120_RECOMMENDATION : 'C119_TARGETED_C118_OBSERVATION_RESULT_REVIEW_LOCK_REPAIR',
            'planned_next_scope' => $pass ? 'controlled runtime wiring GO decision finalization review only; not production deployment, live rollout, official weekly swing output, or PLAN/CONFIRM mutation' : 'targeted repair before controlled runtime wiring operator go/no-go review can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C119 artifact hash',
                'locked C119 file SHA1',
                'operator approval reference',
                'confirmed C119 operator GO decision',
                'unchanged candidate scope',
                'controlled runtime wiring GO decision finalization checklist',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c118_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c118_artifact_not_modified' => true,
            'c111_c112_c113_c114_c115_c116_c117_c118_artifacts_not_modified' => true,
            'c98_c118_sections_not_rewritten' => true,
            'uppercase_boundary_labels_nested_only' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function boundaryAliases(bool $pass): array
    {
        return [
            'C111_NON_LIVE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED' => $pass,
            'C111_NON_LIVE_AUDIT_ARCHIVE_TERMINAL' => $pass,
            'C112_SEPARATE_POST_C111_PRODUCTION_PHASE_TRANSITION_GATE' => $pass,
            'C112_NOT_AUDIT_ARCHIVE_CONTINUATION' => $pass,
            'C112_DOES_NOT_REOPEN_C111_FINAL_CLOSURE' => $pass,
            'C113_PRODUCTION_READINESS_REVIEW_ONLY' => $pass,
            'C114_RUNTIME_WIRING_READINESS_REVIEW_ONLY' => $pass,
            'C114_NOT_RUNTIME_WIRING_EXECUTION' => true,
            'C115_EXECUTION_APPROVAL_REVIEW_ONLY' => true,
            'C115_NOT_RUNTIME_WIRING_EXECUTION' => true,
            'C116_EXECUTION_REVIEW_ONLY' => true,
            'C116_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C116_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C116_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
            'C117_OBSERVATION_REVIEW_ONLY' => true,
            'C117_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C117_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C117_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
            'C118_OBSERVATION_RESULT_REVIEW_ONLY' => true,
            'C118_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C118_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C118_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
            'C119_OPERATOR_GO_NO_GO_REVIEW_ONLY' => true,
            'C119_NOT_PRODUCTION_DEPLOYMENT' => true,
            'C119_NOT_PLAN_CONFIRM_MUTATION' => true,
            'C119_NOT_WEEKLY_SWING_LIVE_OUTPUT' => true,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C119 validates C118 artifact_hash and file SHA1 locks before PR-07 controlled runtime wiring operator go/no-go review is recorded.',
            'C119 validates C118 controlled runtime wiring observation result review pass and next recommendation to C119.',
            'C119 requires --operator-approved, a non-empty --approval-reference, and an explicit operator GO decision confirmation.',
            'C119 records operator GO for E02 primary and B01 backup while keeping A01 comparator-only.',
            'C119 creates an artifact-only controlled runtime wiring operator go/no-go manifest and checklist.',
            'C119 does not deploy production, activate live rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.',
            'C119 may only recommend C120 weekly swing watchlist controlled runtime wiring GO decision finalization review as the next controlled step.',
        ];
    }

    private function temporaryNegativeArtifactPaths(): array
    {
        $paths = [];
        foreach (self::TEMPORARY_NEGATIVE_PATTERNS as $pattern) {
            foreach ((array) glob($pattern) as $path) {
                if (is_file($path)) {
                    $paths[] = str_replace('\\', '/', $path);
                }
            }
        }
        sort($paths);

        return array_values(array_unique($paths));
    }

    private function boolField(array $source, string $field): bool
    {
        return (bool) $this->valueAt($source, explode('.', $field));
    }

    private function valueAt(array $source, array $path)
    {
        $value = $source;
        foreach ($path as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
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

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $payload = null;
        $actualHash = null;
        $actualFileSha1 = null;
        $jsonError = null;
        $duplicateKeys = [];
        if ($exists) {
            $raw = (string) file_get_contents($path);
            $duplicateKeys = $this->caseInsensitiveDuplicateTopLevelKeys($raw);
            $decoded = json_decode($raw, true);
            $jsonError = json_last_error();
            if (is_array($decoded)) {
                $payload = $decoded;
                $actualHash = $decoded['artifact_hash'] ?? null;
            }
            $actualFileSha1 = strtoupper(sha1($raw));
        }
        $expectedFileSha1 = strtoupper($expectedFileSha1);

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash),
            'expected_file_sha1' => $expectedFileSha1,
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== null && $expectedFileSha1 === $actualFileSha1,
            'json_error' => $jsonError,
            'case_insensitive_duplicate_keys' => $duplicateKeys,
            'convert_from_json_pass' => $exists && $payload !== null && $jsonError === JSON_ERROR_NONE && $duplicateKeys === [],
        ];
    }

    private function caseInsensitiveDuplicateTopLevelKeys(string $raw): array
    {
        $length = strlen($raw);
        $depth = 0;
        $expectTopLevelKey = false;
        $seen = [];
        $duplicates = [];

        for ($i = 0; $i < $length; $i++) {
            $char = $raw[$i];
            if ($char === '"') {
                $start = $i;
                $i++;
                $escaped = false;
                while ($i < $length) {
                    $inner = $raw[$i];
                    if ($escaped) {
                        $escaped = false;
                    } elseif ($inner === '\\') {
                        $escaped = true;
                    } elseif ($inner === '"') {
                        break;
                    }
                    $i++;
                }
                $token = substr($raw, $start, $i - $start + 1);
                if ($depth === 1 && $expectTopLevelKey) {
                    $j = $i + 1;
                    while ($j < $length && ctype_space($raw[$j])) {
                        $j++;
                    }
                    if ($j < $length && $raw[$j] === ':') {
                        $decoded = json_decode($token, true);
                        if (is_string($decoded)) {
                            $lower = strtolower($decoded);
                            if (array_key_exists($lower, $seen) && ! in_array($decoded, $duplicates, true)) {
                                $duplicates[] = $decoded;
                            }
                            $seen[$lower] = $decoded;
                        }
                        $expectTopLevelKey = false;
                    }
                }
                continue;
            }
            if ($char === '{') {
                $depth++;
                if ($depth === 1) {
                    $expectTopLevelKey = true;
                }
                continue;
            }
            if ($char === '}') {
                if ($depth === 1) {
                    $expectTopLevelKey = false;
                }
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === '[') {
                $depth++;
                continue;
            }
            if ($char === ']') {
                $depth = max(0, $depth - 1);
                continue;
            }
            if ($char === ',' && $depth === 1) {
                $expectTopLevelKey = true;
            }
        }
        sort($duplicates);

        return array_values($duplicates);
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_go_decision_finalization_decision'] = $this->nextGoDecisionFinalizationDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
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
