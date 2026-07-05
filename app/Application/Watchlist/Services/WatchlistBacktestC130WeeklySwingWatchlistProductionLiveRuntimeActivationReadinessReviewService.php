<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService
{
    public const RUN_CODE = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW';
    public const PHASE_LABEL = 'PR-18 / C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW';
    public const ARTIFACT_TYPE = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW';

    public const DEFAULT_C129_ARTIFACT = 'storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json';
    public const DEFAULT_EXPECTED_C129_HASH = '39b7a16acf266f9b8853d275ff8dff3ef582f716';
    public const DEFAULT_EXPECTED_C129_FILE_SHA1 = 'BA9AE12F4111AED9DC973BF1EA1BAE9181844E9E';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C129_STATUS = 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP';
    private const EXPECTED_C129_PHASE_LABEL = 'PR-17 / C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW';
    private const EXPECTED_C129_TERMINAL_RECOMMENDATION = 'NO_NEXT_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED';
    private const C131_RECOMMENDATION = 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW';

    private const PASS_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const ACTIVATION_READINESS_NOT_CONFIRMED_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_ACTIVATION_READINESS_NOT_CONFIRMED';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C129_LOCK_MISMATCH_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_ARTIFACT_LOCK_MISMATCH';
    private const C129_FILE_SHA1_MISMATCH_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_FILE_SHA1_LOCK_MISMATCH';
    private const C129_STATUS_MISMATCH_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_STATUS_MISMATCH';
    private const C129_PHASE_LABEL_MISMATCH_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_PHASE_LABEL_MISMATCH';
    private const C129_TERMINAL_RECOMMENDATION_MISMATCH_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_TERMINAL_RECOMMENDATION_MISMATCH';
    private const C129_FINAL_CLOSURE_INCOMPLETE_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_FINAL_CLOSURE_INCOMPLETE';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const LIVE_OR_PRODUCTION_MUTATION_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_LIVE_OR_PRODUCTION_MUTATION_ATTEMPT';
    private const C129_CONVERT_FROM_JSON_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_C129_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS = 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_FEATURE_FLAGS_NOT_DEFAULT_OFF';

    private const REQUIRED_C129_TRUE_FIELDS = [
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed',
        'controlled_runtime_wiring_handoff_audit_archive_final_closed',
        'handoff_audit_archive_final_closed',
        'audit_archive_final_closed',
        'final_closure_manifest_created',
        'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed',
        'controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed',
        'handoff_audit_archive_final_closure_confirmed',
        'primary_candidate_handoff_audit_archive_final_closed',
        'backup_candidate_handoff_audit_archive_final_closed',
        'a01_remains_comparator_only',
    ];

    private const REQUIRED_C129_FALSE_FIELDS = [
        'comparator_candidate_handoff_audit_archive_final_closed',
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'runtime_bridge_active',
        'controlled_rollout_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
    ];

    private const REQUIRED_FALSE_SAFETY_FLAGS = [
        'production_ready',
        'production_catalog_runtime_wired',
        'production_runtime_wiring_allowed',
        'production_runtime_wiring_executed',
        'production_deployment_allowed',
        'production_deployment_executed',
        'controlled_opt_in_runtime_bridge_active',
        'controlled_parallel_run_active',
        'controlled_rollout_active',
        'pilot_runtime_active',
        'shadow_runtime_active',
        'runtime_bridge_active',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_plan_confirm_mutation_allowed',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_live_recommendation_generated',
        'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
        'production_live_runtime_activation_readiness_context_persisted_to_live_runtime',
    ];

    private const FEATURE_FLAG_KEYS = [
        'production_catalog_runtime_bridge_enabled',
        'production_catalog_controlled_opt_in_runtime_bridge_enabled',
        'production_catalog_controlled_runtime_opt_in_pilot_enabled',
        'production_catalog_controlled_shadow_rollout_enabled',
        'production_catalog_controlled_parallel_run_enabled',
        'production_catalog_controlled_rollout_enabled',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/*no-*-test.json',
        'storage/app/watchlist/backtest/*missing-*-test.json',
        'storage/app/watchlist/backtest/*mismatch-*-test.json',
        'storage/app/watchlist/backtest/*negative-*-test.json',
    ];

    public function execute(
        string $c129Artifact = self::DEFAULT_C129_ARTIFACT,
        string $expectedC129Hash = self::DEFAULT_EXPECTED_C129_HASH,
        string $expectedC129FileSha1 = self::DEFAULT_EXPECTED_C129_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c129Artifact, $expectedC129Hash, $expectedC129FileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C129_LOCK_MISMATCH_STATUS, 'C129 artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c129_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C129_CONVERT_FROM_JSON_STATUS, 'C129 artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C129_LOCK_MISMATCH_STATUS, 'C129 artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C129_FILE_SHA1_MISMATCH_STATUS, 'C129 file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c129 = $load['payload'];
        if (($c129['status'] ?? null) !== self::EXPECTED_C129_STATUS || ($c129['reason_code'] ?? null) !== self::EXPECTED_C129_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C129_STATUS_MISMATCH_STATUS, 'C129 status/reason is not final closed.', $outputPath, $overwrite);
        }
        if (($c129['phase_label'] ?? null) !== self::EXPECTED_C129_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C129_PHASE_LABEL_MISMATCH_STATUS, 'C129 phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c129TerminalRecommendationMatches($c129)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C129_TERMINAL_RECOMMENDATION_MISMATCH_STATUS, 'C129 terminal recommendation is not no-next handoff audit archive review.', $outputPath, $overwrite);
        }
        if (! $this->c129FinalClosureComplete($c129)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C129_FINAL_CLOSURE_INCOMPLETE_STATUS, 'C129 final closure evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c129)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C129 candidate scope does not match locked activation readiness scope.', $outputPath, $overwrite);
        }

        $safetyFailure = $this->firstLiveOrMutatingSafetyFlag($c129);
        if ($safetyFailure !== null) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c129_live_or_mutating_safety_flag_failure'] = $safetyFailure;

            return $this->rejected($artifact, self::LIVE_OR_PRODUCTION_MUTATION_STATUS, 'C129 contains live, mutating, production, or weekly-live flag set to true.', $outputPath, $overwrite);
        }
        if ($this->enabledRuntimeFeatureFlags() !== []) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['enabled_runtime_feature_flags'] = $this->enabledRuntimeFeatureFlags();

            return $this->rejected($artifact, self::FEATURE_FLAG_NOT_DEFAULT_OFF_STATUS, 'C130 requires runtime feature flags to remain default-off.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C130 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['production_live_runtime_activation_readiness_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::ACTIVATION_READINESS_NOT_CONFIRMED_STATUS, 'C130 requires --production-live-runtime-activation-readiness-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $load, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $load, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C130 opens a new production/live runtime activation readiness review after C129 final closure. It confirms readiness for an activation approval review only; it does not activate runtime bridge, run live output, publish recommendations, or mutate PLAN/CONFIRM.';
        $artifact['diagnostic_conclusion'] = 'C130_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_PASSED_REVIEW_ONLY_NON_LIVE_NON_MUTATING';
        $artifact['next_step_recommendation'] = self::C131_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-18',
            'internal_checkpoint' => 'C130',
            'status' => 'C130_NOT_RUN',
            'reason_code' => 'C130_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass' => false,
            'production_live_runtime_activation_readiness_review_pass' => false,
            'ready_for_production_live_runtime_activation_approval_review' => false,
            'production_live_runtime_activation_readiness_manifest_created' => false,
            'c129_final_closure_valid' => false,
            'c129_handoff_audit_archive_final_closed' => false,
            'c129_audit_archive_terminal' => false,
            'c130_is_new_production_live_activation_phase' => false,
            'c130_not_handoff_audit_archive_continuation' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_approval_review' => false,
            'backup_candidate_ready_for_production_live_runtime_activation_approval_review' => false,
            'comparator_candidate_ready_for_production_live_runtime_activation_approval_review' => false,
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
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass' => true,
            'production_live_runtime_activation_readiness_review_pass' => true,
            'ready_for_production_live_runtime_activation_approval_review' => true,
            'production_live_runtime_activation_readiness_manifest_created' => true,
            'c129_final_closure_valid' => true,
            'c129_handoff_audit_archive_final_closed' => true,
            'c129_audit_archive_terminal' => true,
            'c130_is_new_production_live_activation_phase' => true,
            'c130_not_handoff_audit_archive_continuation' => true,
            'primary_candidate_ready_for_production_live_runtime_activation_approval_review' => true,
            'backup_candidate_ready_for_production_live_runtime_activation_approval_review' => true,
            'comparator_candidate_ready_for_production_live_runtime_activation_approval_review' => false,
            'a01_remains_comparator_only' => true,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $c129 = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? []);

        $artifact['c129_lock_validation_summary'] = $this->c129LockValidationSummary($load, $c129);
        $artifact['c129_final_closure_carry_forward_summary'] = $this->c129FinalClosureCarryForwardSummary($c129, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($c129, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options, $pass);
        $artifact['activation_readiness_confirmation_summary'] = $this->activationReadinessConfirmationSummary($options, $pass);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['feature_flag_default_off_summary'] = $this->featureFlagDefaultOffSummary();
        $artifact['c130_readiness_decision'] = $this->activationReadinessDecision($pass);
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_readiness_manifest'] = $this->activationReadinessManifest($pass);
        $artifact['weekly_swing_watchlist_production_live_runtime_activation_readiness_checklist'] = $this->activationReadinessChecklist();
        $artifact['c130_candidate_activation_readiness_scorecard'] = $this->candidateScorecard($pass);
        $artifact['production_live_runtime_activation_readiness_context_summary'] = $this->activationReadinessContextSummary($pass);
        $artifact['production_mutation_safety_summary'] = $this->productionMutationSafetySummary();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary($pass ? [] : [(string) ($artifact['status'] ?? 'C130_PENDING')]);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['temporary_negative_artifacts_remaining'] = $temporaryNegativePaths !== [];
        $artifact['temporary_negative_artifact_cleanup_confirmed'] = $temporaryNegativePaths === [];
        $artifact['temporary_negative_artifact_paths'] = array_values($temporaryNegativePaths);

        if ($pass) {
            $artifact = array_merge($artifact, $this->passingTopLevelState());
        }

        return $artifact;
    }

    private function c129FinalClosureComplete(array $c129): bool
    {
        foreach (self::REQUIRED_C129_TRUE_FIELDS as $field) {
            if (! (bool) ($c129[$field] ?? false)) {
                return false;
            }
        }
        foreach (self::REQUIRED_C129_FALSE_FIELDS as $field) {
            if ((bool) ($c129[$field] ?? false)) {
                return false;
            }
        }

        return ($c129['handoff_audit_archive_final_closure_go_decision'] ?? null) === 'HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED_GO';
    }

    private function c129TerminalRecommendationMatches(array $c129): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_readiness_decision', 'next_recommendation'],
            ['c129_readiness_decision', 'next_recommendation'],
            ['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            $value = $this->valueAt($c129, $path);
            if ($value !== null && $value !== self::EXPECTED_C129_TERMINAL_RECOMMENDATION) {
                return false;
            }
        }

        return ($c129['next_step_recommendation'] ?? null) === self::EXPECTED_C129_TERMINAL_RECOMMENDATION;
    }

    private function candidateScopeMatches(array $source): bool
    {
        if (($source['primary_candidate_code'] ?? null) !== self::PRIMARY_CANDIDATE) {
            return false;
        }
        if (($source['backup_candidate_code'] ?? null) !== self::BACKUP_CANDIDATE) {
            return false;
        }
        if (($source['comparator_candidate_code'] ?? null) !== self::COMPARATOR_CANDIDATE) {
            return false;
        }
        foreach ([
            'a01_promoted',
            'candidate_promotion_executed',
            'candidate_rerank_executed',
            'strategy_retune_executed',
            'scoring_mutation_executed',
            'catalog_selection_changed',
            'runtime_selection_changed',
            'weekly_swing_live_recommendation_selection_executed',
        ] as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return false;
            }
        }

        return (bool) ($source['a01_remains_comparator_only'] ?? false);
    }

    private function firstLiveOrMutatingSafetyFlag(array $source): ?string
    {
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $field) {
            if ((bool) ($source[$field] ?? false)) {
                return $field;
            }
        }

        return null;
    }

    private function enabledRuntimeFeatureFlags(): array
    {
        $enabled = [];
        foreach (self::FEATURE_FLAG_KEYS as $key) {
            if ($this->configFlagIsOn($key)) {
                $enabled[] = $key;
            }
        }

        return $enabled;
    }

    private function c129LockValidationSummary(array $load, array $c129): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C129',
            'artifact_path' => $load['path'],
            'artifact_exists' => $load['exists'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C129_STATUS,
            'actual_status' => $c129['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C129_PHASE_LABEL,
            'actual_phase_label' => $c129['phase_label'] ?? null,
            'expected_terminal_recommendation' => self::EXPECTED_C129_TERMINAL_RECOMMENDATION,
            'terminal_recommendation_match' => $this->c129TerminalRecommendationMatches($c129),
            'c129_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
        ];
    }

    private function c129FinalClosureCarryForwardSummary(array $c129, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c129_final_closure_review_pass' => (bool) ($c129['weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass'] ?? false),
            'c129_handoff_audit_archive_final_closed' => (bool) ($c129['handoff_audit_archive_final_closed'] ?? false),
            'c129_audit_archive_final_closed' => (bool) ($c129['audit_archive_final_closed'] ?? false),
            'c129_final_closure_manifest_created' => (bool) ($c129['final_closure_manifest_created'] ?? false),
            'c129_terminal_recommendation_no_next_handoff_audit_archive_review' => $this->c129TerminalRecommendationMatches($c129),
            'c130_production_live_activation_readiness_can_start' => $pass,
        ];
    }

    private function candidateScopeFreezeSummary(array $source, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($source),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_readiness_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_readiness_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'primary_candidate_ready_for_production_live_runtime_activation_approval_review' => $pass,
            'backup_candidate_ready_for_production_live_runtime_activation_approval_review' => $pass,
            'comparator_candidate_ready_for_production_live_runtime_activation_approval_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
            'weekly_swing_live_recommendation_selection_executed' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options, bool $pass): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'operator_approval_validation_pass' => $pass,
        ];
    }

    private function activationReadinessConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'production_live_runtime_activation_readiness_confirmation_required' => true,
            'production_live_runtime_activation_readiness_confirmed' => (bool) ($options['production_live_runtime_activation_readiness_confirmed'] ?? false),
            'activation_readiness_confirmation_pass' => $pass,
            'activation_execution_requested' => false,
            'live_runtime_mutation_requested' => false,
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

    private function featureFlagDefaultOffSummary(): array
    {
        $flags = [];
        foreach (self::FEATURE_FLAG_KEYS as $key) {
            $flags[$key] = $this->configFlagIsOn($key);
        }

        return [
            'validation_completed' => true,
            'feature_flags_checked' => $flags,
            'enabled_runtime_feature_flags' => $this->enabledRuntimeFeatureFlags(),
            'all_runtime_feature_flags_remain_default_off' => $this->enabledRuntimeFeatureFlags() === [],
            'kill_switch_identified' => array_key_exists('production_catalog_runtime_bridge_kill_switch', $this->watchlistConfig()),
            'production_catalog_runtime_bridge_kill_switch' => $this->configFlagIsOn('production_catalog_runtime_bridge_kill_switch'),
        ];
    }

    private function activationReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'validation_completed' => true,
            'c129_final_closure_valid' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_allowed' => $pass,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_approval_review' => $pass,
            'production_ready' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'production_deployment_allowed' => false,
            'production_deployment_executed' => false,
            'runtime_bridge_active' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
            'next_recommendation' => $pass ? self::C131_RECOMMENDATION : 'C130_TARGETED_C129_FINAL_CLOSURE_OR_APPROVAL_REPAIR',
            'decision_reason' => $pass ? 'C130 confirms readiness to request production/live activation approval only.' : 'C130 cannot proceed until C129 lock, final closure, approval, confirmation, feature flags, and safety gates pass.',
        ];
    }

    private function nextReadinessDecision(bool $pass): array
    {
        return [
            'review_pass' => $pass,
            'next_recommendation' => $pass ? self::C131_RECOMMENDATION : 'C130_TARGETED_C129_FINAL_CLOSURE_OR_APPROVAL_REPAIR',
            'next_scope' => $pass ? 'production/live runtime activation approval review only; still no runtime bridge activation, live output generation, official publication, or PLAN/CONFIRM mutation' : 'targeted C129 final closure, approval, feature flag, or cleanup repair only',
        ];
    }

    private function activationReadinessManifest(bool $pass): array
    {
        return [
            'manifest_created' => $pass,
            'manifest_context' => 'artifact_only_production_live_runtime_activation_readiness_review',
            'source_artifact' => self::EXPECTED_C129_STATUS,
            'source_artifact_path' => self::DEFAULT_C129_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C129_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C129_FILE_SHA1,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_role' => 'primary_production_live_runtime_activation_readiness_candidate',
            'backup_candidate_role' => 'backup_production_live_runtime_activation_readiness_candidate',
            'comparator_candidate_role' => 'comparator_only_not_promoted',
            'c129_final_closure_carried_forward' => $pass,
            'ready_for_production_live_runtime_activation_approval_review' => $pass,
            'production_live_runtime_activation_approval_review_required_next' => $pass,
            'production_live_runtime_activation_executed' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_live_output_enabled' => false,
            'weekly_swing_official_output_generated' => false,
            'weekly_swing_official_output_published' => false,
            'weekly_swing_live_recommendation_generated' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'activation_readiness_used_for_plan_confirm_mutation' => false,
            'activation_readiness_used_for_live_rollout' => false,
            'activation_readiness_artifact_only' => true,
        ];
    }

    private function activationReadinessChecklist(): array
    {
        return [
            'c129_final_closure_artifact_locked' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'operator_approval_required' => true,
            'activation_readiness_confirmation_required' => true,
            'feature_flags_default_off_required' => true,
            'kill_switch_required' => true,
            'rollback_plan_required_for_future_activation' => true,
            'manual_validation_required_before_future_activation' => true,
            'live_runtime_activation_approval_required_next' => true,
            'readiness_review_only' => true,
            'non_live' => true,
            'non_mutating' => true,
            'artifact_only' => true,
            'runtime_bridge_activated' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutated' => false,
            'weekly_swing_stock_recommendation_generated' => false,
            'weekly_swing_stock_recommendation_published' => false,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'production_live_runtime_activation_readiness_review_pass' => $pass,
            'ready_for_production_live_runtime_activation_approval_review' => $pass,
            'production_ready' => false,
            'production_deployment_allowed' => false,
            'production_runtime_wiring_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c130_role' => 'primary_production_live_runtime_activation_readiness_candidate',
                'primary_candidate_ready_for_production_live_runtime_activation_approval_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c130_role' => 'backup_production_live_runtime_activation_readiness_candidate',
                'backup_candidate_ready_for_production_live_runtime_activation_approval_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c130_role' => 'comparator_only_candidate',
                'production_live_runtime_activation_readiness_review_pass' => false,
                'ready_for_production_live_runtime_activation_approval_review' => false,
                'comparator_candidate_ready_for_production_live_runtime_activation_approval_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function activationReadinessContextSummary(bool $pass): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_created' => true,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_pass' => $pass,
            'context_is_artifact_only' => true,
            'context_persisted_to_live_runtime' => false,
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_context_persisted_to_live_runtime' => false,
            'production_live_runtime_activation_readiness_context_persisted_to_live_runtime' => false,
            'production_catalog_runtime_wired' => false,
            'production_runtime_wiring_allowed' => false,
            'production_runtime_wiring_executed' => false,
            'plan_confirm_mutated' => false,
            'runtime_bridge_active' => false,
            'controlled_rollout_active' => false,
            'pilot_runtime_active' => false,
            'shadow_runtime_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_live_recommendation_generated' => false,
        ];
    }

    private function productionMutationSafetySummary(): array
    {
        $summary = [
            'validation_completed' => true,
            'all_required_safety_flags_false' => true,
        ];
        foreach (self::REQUIRED_FALSE_SAFETY_FLAGS as $flag) {
            $summary[$flag] = false;
        }

        return $summary;
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-18_C130_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW',
            'c129_final_closure_carried_forward' => true,
            'c130_production_live_runtime_activation_readiness_review_executed' => true,
            'c130_ready_for_activation_approval_review' => $pass,
            'still_no_live_runtime' => true,
            'still_no_runtime_bridge_activation' => true,
            'still_no_plan_confirm_mutation' => true,
            'artifact_only' => true,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C131_RECOMMENDATION : 'C130_TARGETED_C129_FINAL_CLOSURE_OR_APPROVAL_REPAIR',
            'planned_next_scope' => $pass ? 'production/live runtime activation approval review only; not live runtime execution, not official weekly swing output, not PLAN/CONFIRM mutation' : 'targeted repair before production/live activation readiness can be recorded',
            'planned_next_required_inputs' => $pass ? [
                'locked C129 artifact hash',
                'locked C129 file SHA1',
                'operator approval reference',
                'activation readiness confirmation',
                'runtime feature flags still default-off',
                'future activation approval contract',
            ] : [],
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c129_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c129_artifact_not_modified' => true,
            'c60_c129_artifacts_not_modified' => true,
            'c130_is_separate_from_handoff_audit_archive_closure' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C130 validates C129 artifact_hash and file SHA1 locks before production/live runtime activation readiness is recorded.',
            'C130 validates C129 final closure fields and terminal no-next handoff audit archive recommendation.',
            'C130 starts a new production/live activation readiness phase; it is not another handoff audit archive review.',
            'C130 requires --operator-approved, a non-empty --approval-reference, and --production-live-runtime-activation-readiness-confirmed.',
            'C130 keeps runtime feature flags default-off and checks the kill-switch surface.',
            'C130 keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C130 does not activate runtime bridge, execute production runtime wiring, generate official weekly swing output, publish recommendations, or mutate PLAN/CONFIRM.',
            'C130 may only recommend C131 production/live runtime activation approval review as the next controlled step.',
        ];
    }

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
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

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c129' => [
                'artifact_path' => $load['path'],
                'expected_artifact_hash' => $load['expected_hash'],
                'actual_artifact_hash' => $load['actual_hash'],
                'artifact_hash_match' => $load['hash_match'],
                'expected_file_sha1' => $load['expected_file_sha1'],
                'actual_file_sha1' => $load['actual_file_sha1'],
                'file_sha1_match' => $load['file_sha1_match'],
                'convert_from_json_pass' => $load['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $load): array
    {
        return [
            'expected_c129_hash' => $load['expected_hash'],
            'actual_c129_hash' => $load['actual_hash'],
            'c129_hash_match' => $load['hash_match'],
            'expected_c129_file_sha1' => $load['expected_file_sha1'],
            'actual_c129_file_sha1' => $load['actual_file_sha1'],
            'c129_file_sha1_match' => $load['file_sha1_match'],
            'c129_convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
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

    private function watchlistConfig(): array
    {
        $path = 'config/watchlist.php';
        if (! is_file($path)) {
            return [];
        }
        $config = require $path;

        return is_array($config) ? $config : [];
    }

    private function configFlagIsOn(string $key): bool
    {
        return (bool) ($this->watchlistConfig()[$key] ?? false);
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['next_readiness_decision'] = $this->nextReadinessDecision(false);
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
