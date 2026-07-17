<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffClosureSealReviewService
{
    public const RUN_CODE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW';
    public const PHASE_LABEL = 'PR-68 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW';
    public const ARTIFACT_TYPE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW';

    public const DEFAULT_C162_HANDOFF_COMPLETION_BOUNDARY_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-completion-boundary-review.json';
    public const DEFAULT_EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_HASH = 'a99616c2d7e136afa3e55ba6760a405229a9eb94';
    public const DEFAULT_EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_FILE_SHA1 = '83DE7DBACB14DA28A48DBB14626DEB6A4773A4B0';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-closure-seal-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_CLOSURE_SEAL_REVIEW';
    private const EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_PHASE_LABEL = 'PR-67 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    private const EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C162_HANDOFF_AUDIT_ARCHIVE_RECOMMENDATION = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW';

    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_CLOSURE_SEAL_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_HANDOFF_CLOSURE_SEAL_CONFIRMATION_MISSING';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_COMPLETE_CONFIRMATION_MISSING';
    private const HANDOFF_COMPLETION_BOUNDARY_CLEARED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_HANDOFF_COMPLETION_BOUNDARY_CLEARED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_ARTIFACT_LOCK_MISMATCH';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_FILE_SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_FILE_SHA1_LOCK_MISMATCH';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_CONVERT_FROM_JSON_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_STATUS_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_STATUS_MISMATCH';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_PHASE_LABEL_MISMATCH';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH';
    private const C162_HANDOFF_COMPLETION_BOUNDARY_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_C162_HANDOFF_COMPLETION_BOUNDARY_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C162_HANDOFF_COMPLETION_BOUNDARY_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_pass',
        'production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_pass',
        'handoff_ready',
        'handoff_finalized',
        'handoff_completion_boundary_cleared',
        'handoff_completion_boundary_confirmed',
        'c162_handoff_finalization_complete_confirmed',
        'handoff_finalized_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c162_handoff_finalization_lock_valid',
        'c162_plan_confirm_completion_handoff_finalization_valid',
        'c162_handoff_finalization_convert_from_json_pass',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_review',
        'production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest_created',
        'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
        'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'primary_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review',
        'backup_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review',
        'a01_remains_comparator_only',
        'c162_plan_confirm_completion_handoff_completion_boundary_review_only',
        'c162_controlled_completion_only',
        'c162_not_publication',
        'c162_not_unrestricted_publication',
        'c162_not_plan_confirm_mutation',
        'c162_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C162_HANDOFF_COMPLETION_BOUNDARY_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c162-*handoff-closure-seal*-test.json',
        'storage/app/watchlist/backtest/c162-*negative-*-test.json',
        'storage/app/watchlist/backtest/c162-*missing-*-test.json',
        'storage/app/watchlist/backtest/c162-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c162-*invalid-*-test.json',
        'storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-closure-seal-negative-*.json',
    ];

    public function execute(
        string $c162HandoffCompletionBoundaryArtifact = self::DEFAULT_C162_HANDOFF_COMPLETION_BOUNDARY_ARTIFACT,
        string $expectedC162HandoffCompletionBoundaryHash = self::DEFAULT_EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_HASH,
        string $expectedC162HandoffCompletionBoundaryFileSha1 = self::DEFAULT_EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c162HandoffCompletionBoundaryArtifact, $expectedC162HandoffCompletionBoundaryHash, $expectedC162HandoffCompletionBoundaryFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C162_HANDOFF_COMPLETION_BOUNDARY_LOCK_MISMATCH_STATUS, 'C162 handoff completion boundary artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c162_handoff_completion_boundary_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C162_HANDOFF_COMPLETION_BOUNDARY_CONVERT_FROM_JSON_STATUS, 'C162 handoff completion boundary artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C162_HANDOFF_COMPLETION_BOUNDARY_LOCK_MISMATCH_STATUS, 'C162 handoff completion boundary artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C162_HANDOFF_COMPLETION_BOUNDARY_FILE_SHA1_MISMATCH_STATUS, 'C162 handoff completion boundary file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $boundary = $load['payload'];
        if (($boundary['status'] ?? null) !== self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_STATUS || ($boundary['reason_code'] ?? null) !== self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_COMPLETION_BOUNDARY_STATUS_MISMATCH_STATUS, 'C162 handoff completion boundary status/reason is not closure-seal-ready.', $outputPath, $overwrite);
        }
        if (($boundary['phase_label'] ?? null) !== self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_COMPLETION_BOUNDARY_PHASE_LABEL_MISMATCH_STATUS, 'C162 handoff completion boundary phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c162NextRecommendationMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C162 handoff completion boundary next recommendation is not C162 handoff closure seal.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C162 boundary evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c162HandoffCompletionBoundaryStateValid($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_COMPLETION_BOUNDARY_STATE_INVALID_STATUS, 'C162 handoff completion boundary evidence is incomplete for C162 handoff closure seal.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($boundary)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C162 handoff completion boundary candidate scope does not match locked handoff closure seal scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C162 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_closure_seal_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_CLOSURE_SEAL_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-closure-seal-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c162_handoff_completion_boundary_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_COMPLETION_BOUNDARY_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C162 requires --c162-handoff-completion-boundary-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_completion_boundary_cleared_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_COMPLETION_BOUNDARY_CLEARED_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-completion-boundary-cleared-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C162 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS, 'C162 requires --no-live-plan-confirm-rollout-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::FREE_PUBLICATION_LOCK_MISSING_STATUS, 'C162 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C162 seals the PLAN/CONFIRM completion handoff closure for E02 primary and B01 backup. This remains controlled, artifact-backed, and does not mutate PLAN/CONFIRM, execute live rollout, or unlock free publication.';
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEALED_READY_FOR_C162_HANDOFF_AUDIT_ARCHIVE_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C162_HANDOFF_AUDIT_ARCHIVE_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-68',
            'internal_checkpoint' => 'C162',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW',
            'status' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_NOT_RUN',
            'reason_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_pass' => false,
            'handoff_ready' => false,
            'handoff_finalized' => false,
            'handoff_completion_boundary_cleared' => false,
            'handoff_closure_sealed' => false,
            'handoff_closure_seal_confirmed' => false,
            'handoff_closure_seal_go_decision' => 'NO_GO',
            'c162_handoff_completion_boundary_complete_confirmed' => false,
            'handoff_completion_boundary_cleared_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review' => false,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_manifest_created' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c162_plan_confirm_completion_handoff_closure_seal_review_only' => true,
            'c162_controlled_completion_only' => true,
            'c162_not_publication' => true,
            'c162_not_unrestricted_publication' => true,
            'c162_not_plan_confirm_mutation' => true,
            'c162_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_NOT_YET_EVALUATED',
            'next_step_recommendation' => null,
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_pass' => true,
            'handoff_ready' => true,
            'handoff_finalized' => true,
            'handoff_completion_boundary_cleared' => true,
            'handoff_closure_sealed' => true,
            'handoff_closure_seal_confirmed' => true,
            'handoff_closure_seal_go_decision' => 'HANDOFF_CLOSURE_SEALED_GO',
            'c162_handoff_completion_boundary_complete_confirmed' => true,
            'handoff_completion_boundary_cleared_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review' => true,
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_manifest_created' => true,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $boundary = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($boundary, $load, $pass, $options));
        $artifact['c162_handoff_completion_boundary_lock_validation_summary'] = $this->c162HandoffCompletionBoundaryLockValidationSummary($load, $boundary);
        $artifact['c162_plan_confirm_completion_handoff_completion_boundary_carry_forward_summary'] = $this->c162HandoffCompletionBoundaryCarryForwardSummary($boundary);
        $artifact['plan_confirm_completion_handoff_closure_seal_guard_summary'] = $this->handoffClosureSealGuardSummary($boundary, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($boundary, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c162_handoff_closure_seal_decision'] = $this->handoffClosureSealDecision($pass, $options);
        $artifact['next_plan_confirm_completion_handoff_audit_archive_decision'] = $this->nextHandoffAuditArchiveDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_manifest'] = $this->handoffClosureSealManifest($boundary, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_checklist'] = $this->handoffClosureSealChecklist($pass, $options);
        $artifact['c162_candidate_plan_confirm_completion_handoff_closure_seal_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($boundary);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $boundary, array $load, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($boundary['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'controlled_completion_path' => $boundary['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $boundary['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $boundary['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($boundary['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($boundary['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($boundary['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($boundary['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($boundary['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($boundary['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($boundary['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c162_handoff_completion_boundary_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c162_plan_confirm_completion_handoff_completion_boundary_valid' => $this->c162HandoffCompletionBoundaryStateValid($boundary),
            'c162_handoff_completion_boundary_convert_from_json_pass' => $load['convert_from_json_pass'],
            'handoff_ready' => (bool) ($boundary['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($boundary['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($boundary['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => $pass,
            'handoff_closure_seal_confirmed' => (bool) ($options['handoff_closure_seal_confirmed'] ?? false),
            'handoff_closure_seal_go_decision' => $pass ? 'HANDOFF_CLOSURE_SEALED_GO' : 'NO_GO',
            'c162_handoff_completion_boundary_complete_confirmed' => (bool) ($options['c162_handoff_completion_boundary_complete_confirmed'] ?? false),
            'handoff_completion_boundary_cleared_confirmed' => (bool) ($options['handoff_completion_boundary_cleared_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function c162HandoffCompletionBoundaryStateValid(array $boundary): bool
    {
        foreach (self::REQUIRED_C162_HANDOFF_COMPLETION_BOUNDARY_TRUE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C162_HANDOFF_COMPLETION_BOUNDARY_FALSE_FIELDS as $field) {
            if (($boundary[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($boundary['handoff_completion_boundary_go_decision'] ?? null) === 'HANDOFF_COMPLETION_BOUNDARY_CLEARED_GO'
            && ($boundary['controlled_completion_record_count'] ?? 0) === 2
            && trim((string) ($boundary['controlled_completion_path'] ?? '')) !== ''
            && trim((string) ($boundary['controlled_completion_hash'] ?? '')) !== ''
            && trim((string) ($boundary['controlled_completion_file_sha1'] ?? '')) !== ''
            && $this->valueAt($boundary, ['next_plan_confirm_completion_handoff_closure_seal_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($boundary, ['next_plan_confirm_completion_handoff_closure_seal_decision', 'topic_stage_advances_within_c162_handoff_after_completion_boundary']) === true
            && $this->valueAt($boundary, ['next_plan_confirm_completion_handoff_closure_seal_decision', 'c162_handoff_completion_boundary_complete']) === true
            && $this->valueAt($boundary, ['c162_handoff_completion_boundary_decision', 'review_valid']) === true
            && $this->valueAt($boundary, ['c162_handoff_completion_boundary_decision', 'handoff_completion_boundary_cleared']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest', 'ready_for_plan_confirm_completion_handoff_closure_seal_review']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest', 'handoff_completion_boundary_artifact_only']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest', 'handoff_completion_boundary_used_for_free_publication']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest', 'handoff_completion_boundary_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest', 'handoff_completion_boundary_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_checklist', 'artifact_only']) === true
            && $this->valueAt($boundary, ['weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_checklist', 'weekly_swing_stock_recommendation_free_published_in_c162_handoff_completion_boundary']) === false;
    }

    private function c162NextRecommendationMatches(array $boundary): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_handoff_closure_seal_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($boundary, $path) !== self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function publicationAndPlanGuardClean(array $boundary): bool
    {
        foreach ([
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
        ] as $field) {
            if (($boundary[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $boundary): bool
    {
        return ($boundary['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($boundary['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($boundary['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($boundary['primary_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review'] ?? null) === true
            && ($boundary['backup_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review'] ?? null) === true
            && ($boundary['comparator_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review'] ?? null) === false
            && ($boundary['a01_remains_comparator_only'] ?? null) === true
            && ($boundary['a01_promoted'] ?? false) === false
            && ($boundary['candidate_promotion_executed'] ?? false) === false
            && ($boundary['candidate_rerank_executed'] ?? false) === false;
    }

    private function c162HandoffCompletionBoundaryLockValidationSummary(array $load, array $boundary): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_STATUS,
            'actual_status' => $boundary['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_PHASE_LABEL,
            'actual_phase_label' => $boundary['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c162NextRecommendationMatches($boundary),
            'c162_handoff_completion_boundary_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function c162HandoffCompletionBoundaryCarryForwardSummary(array $boundary): array
    {
        return [
            'validation_completed' => true,
            'c162_plan_confirm_completion_handoff_completion_boundary_valid' => $this->c162HandoffCompletionBoundaryStateValid($boundary),
            'topic_code' => $boundary['topic_code'] ?? null,
            'topic_stage' => $boundary['topic_stage'] ?? null,
            'handoff_completion_boundary_go_decision' => $boundary['handoff_completion_boundary_go_decision'] ?? null,
            'handoff_completion_boundary_cleared' => (bool) ($boundary['handoff_completion_boundary_cleared'] ?? false),
            'controlled_completion_path' => $boundary['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $boundary['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $boundary['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($boundary['controlled_completion_record_count'] ?? 0),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function handoffClosureSealGuardSummary(array $boundary, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'handoff_closure_seal_valid' => $pass,
            'c162_handoff_completion_boundary_complete' => $this->valueAt($boundary, ['next_plan_confirm_completion_handoff_closure_seal_decision', 'c162_handoff_completion_boundary_complete']) === true,
            'handoff_completion_boundary_cleared' => (bool) ($boundary['handoff_completion_boundary_cleared'] ?? false),
            'ready_for_plan_confirm_completion_handoff_closure_seal_review' => (bool) ($boundary['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_review'] ?? false),
            'handoff_closure_sealed' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $boundary, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($boundary),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
            'a01_remains_comparator_only' => true,
            'candidate_promotion_executed' => false,
            'candidate_rerank_executed' => false,
            'strategy_retune_executed' => false,
            'scoring_mutation_executed' => false,
            'catalog_selection_changed' => false,
            'runtime_selection_changed' => false,
        ];
    }

    private function operatorApprovalValidationSummary(array $options): array
    {
        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => (string) ($options['approval_reference'] ?? ''),
            'approval_reference_present' => trim((string) ($options['approval_reference'] ?? '')) !== '',
            'handoff_closure_seal_confirmation_required' => true,
            'handoff_closure_seal_confirmed' => (bool) ($options['handoff_closure_seal_confirmed'] ?? false),
            'c162_handoff_completion_boundary_complete_confirmation_required' => true,
            'c162_handoff_completion_boundary_complete_confirmed' => (bool) ($options['c162_handoff_completion_boundary_complete_confirmed'] ?? false),
            'handoff_completion_boundary_cleared_confirmation_required' => true,
            'handoff_completion_boundary_cleared_confirmed' => (bool) ($options['handoff_completion_boundary_cleared_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
    }

    private function temporaryNegativeArtifactGuardSummary(array $paths): array
    {
        return [
            'validation_completed' => true,
            'temporary_negative_artifacts_remaining' => $paths !== [],
            'temporary_negative_artifact_cleanup_confirmed' => $paths === [],
            'temporary_negative_artifact_paths' => $paths,
            'patterns' => self::TEMPORARY_NEGATIVE_PATTERNS,
        ];
    }

    private function handoffClosureSealDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'handoff_closure_sealed' => $pass,
            'handoff_closure_seal_go_decision' => $pass ? 'HANDOFF_CLOSURE_SEALED_GO' : 'NO_GO',
            'handoff_closure_seal_confirmed' => (bool) ($options['handoff_closure_seal_confirmed'] ?? false),
            'c162_handoff_completion_boundary_complete_confirmed' => (bool) ($options['c162_handoff_completion_boundary_complete_confirmed'] ?? false),
            'handoff_completion_boundary_cleared_confirmed' => (bool) ($options['handoff_completion_boundary_cleared_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass
                ? 'C162 PLAN/CONFIRM completion handoff closure sealed; handoff audit archive may start next'
                : 'C162 PLAN/CONFIRM completion handoff closure seal did not pass',
        ];
    }

    private function nextHandoffAuditArchiveDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_RECOMMENDATION : null,
            'next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff audit archive review only; no PLAN/CONFIRM mutation or live rollout is authorized by C162 closure seal' : null,
            'next_is_concrete' => $pass,
            'next_requires_locked_c162_handoff_closure_seal_artifact' => $pass,
            'topic_stage_advances_within_c162_handoff_after_closure_seal' => $pass,
            'c162_handoff_closure_seal_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function handoffClosureSealManifest(array $boundary, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_handoff_closure_seal_review',
            'source_artifact' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY_REVIEW',
            'source_artifact_path' => self::DEFAULT_C162_HANDOFF_COMPLETION_BOUNDARY_ARTIFACT,
            'source_artifact_hash' => $boundary['artifact_hash'] ?? null,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C162_HANDOFF_COMPLETION_BOUNDARY_FILE_SHA1,
            'handoff_ready' => (bool) ($boundary['handoff_ready'] ?? false),
            'handoff_finalized' => (bool) ($boundary['handoff_finalized'] ?? false),
            'handoff_completion_boundary_cleared' => (bool) ($boundary['handoff_completion_boundary_cleared'] ?? false),
            'handoff_closure_sealed' => $pass,
            'handoff_closure_seal_go_decision' => $pass ? 'HANDOFF_CLOSURE_SEALED_GO' : 'NO_GO',
            'handoff_closure_seal_confirmed' => (bool) ($options['handoff_closure_seal_confirmed'] ?? false),
            'c162_handoff_completion_boundary_complete_confirmed' => (bool) ($options['c162_handoff_completion_boundary_complete_confirmed'] ?? false),
            'handoff_completion_boundary_cleared_confirmed' => (bool) ($options['handoff_completion_boundary_cleared_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_hash' => $boundary['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $boundary['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($boundary['controlled_completion_record_count'] ?? 0),
            'ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            'handoff_closure_seal_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'handoff_closure_seal_used_for_free_publication' => false,
            'handoff_closure_seal_used_for_plan_confirm_mutation' => false,
            'handoff_closure_seal_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function handoffClosureSealChecklist(bool $pass, array $options): array
    {
        return [
            'handoff_closure_seal_reviewed' => $pass,
            'c162_handoff_completion_boundary_source_lock_reviewed' => $pass,
            'c162_handoff_completion_boundary_complete_reviewed' => $pass,
            'handoff_closure_seal_required' => true,
            'handoff_closure_seal_confirmed' => (bool) ($options['handoff_closure_seal_confirmed'] ?? false),
            'c162_handoff_completion_boundary_complete_confirmed' => (bool) ($options['c162_handoff_completion_boundary_complete_confirmed'] ?? false),
            'handoff_completion_boundary_cleared_confirmed' => (bool) ($options['handoff_completion_boundary_cleared_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_handoff_closure_seal_gate_required' => true,
            'negative_c162_handoff_completion_boundary_complete_gate_required' => true,
            'negative_handoff_completion_boundary_cleared_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'handoff_closure_seal_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c162_handoff_closure_seal' => false,
            'ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'role' => 'PRIMARY',
                'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
                'ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'role' => 'BACKUP',
                'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
                'ready_for_plan_confirm_completion_handoff_audit_archive_review' => $pass,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'role' => 'COMPARATOR_ONLY',
                'a01_remains_comparator_only' => true,
                'ready_for_plan_confirm_completion_handoff_audit_archive_review' => false,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $boundary): array
    {
        $summary = [
            'validation_completed' => true,
            'source_publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($boundary),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'free_publication_allowed' => false,
        ];

        return $summary;
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_artifact_path' => $load['path'],
            'source_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
            'documentation_update_required' => true,
            'operator_validation_commands_required' => true,
            'audit_tracker_update_required' => true,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'current' => self::RUN_CODE,
            'current_topic_number' => 'C162',
            'current_topic_complete' => $pass,
            'completed_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REVIEW',
            'next_topic_number' => $pass ? 'C162' : 'C162',
            'next_topic' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_RECOMMENDATION : self::RUN_CODE,
            'topic_numbering_rule' => 'Keep topic number C162 until the complete HANDOFF sequence is closed.',
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C162_HANDOFF_AUDIT_ARCHIVE_RECOMMENDATION : self::RUN_CODE,
            'planned_next_action' => $pass ? 'Lock C162 closure seal artifact and archive the handoff audit evidence before any later publication expansion.' : 'Resolve C162 rejection and rerun closure seal.',
            'free_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'live_plan_confirm_rollout_allowed' => false,
        ];
    }

    private function diagnostics(): array
    {
        return [
            'runtime_family' => 'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion',
            'review_layer' => 'handoff_closure_seal',
            'source_layer' => 'c162_handoff_completion_boundary',
            'next_layer' => 'c162_handoff_audit_archive',
            'candidate_policy' => 'E02 primary, B01 backup, A01 comparator only',
            'publication_policy' => 'controlled output remains generated but unpublished',
            'plan_confirm_policy' => 'PLAN/CONFIRM remains unchanged and no live rollout is authorized',
        ];
    }

    private function failureAttributionSummary(array $failures): array
    {
        return [
            'failure_count' => count($failures),
            'failures' => $failures,
            'attribution' => $failures === [] ? 'NONE' : 'SOURCE_OR_OPERATOR_CONFIRMATION',
        ];
    }

    private function loadArtifactLock(string $path, string $expectedHash, string $expectedFileSha1): array
    {
        $exists = is_file($path);
        $raw = $exists ? (string) file_get_contents($path) : '';
        $payload = $exists ? json_decode($raw, true) : null;
        $decoded = is_array($payload) && json_last_error() === JSON_ERROR_NONE;
        $duplicateKeys = $decoded ? $this->caseInsensitiveDuplicateKeys($payload) : [];
        $actualHash = is_array($payload) ? (string) ($payload['artifact_hash'] ?? '') : '';
        $actualFileSha1 = $exists ? strtoupper(sha1($raw)) : '';

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== '' && hash_equals($expectedHash, $actualHash),
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== '' && strtoupper($expectedFileSha1) === $actualFileSha1,
            'convert_from_json_pass' => $decoded && $duplicateKeys === [],
            'case_insensitive_duplicate_keys' => $duplicateKeys,
        ];
    }

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c162_plan_confirm_completion_handoff_completion_boundary' => [
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
            'expected_c162_handoff_completion_boundary_hash' => $load['expected_hash'],
            'actual_c162_handoff_completion_boundary_hash' => $load['actual_hash'],
            'c162_handoff_completion_boundary_hash_match' => $load['hash_match'],
            'expected_c162_handoff_completion_boundary_file_sha1' => $load['expected_file_sha1'],
            'actual_c162_handoff_completion_boundary_file_sha1' => $load['actual_file_sha1'],
            'c162_handoff_completion_boundary_file_sha1_match' => $load['file_sha1_match'],
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

        return array_values(array_unique($paths));
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_BLOCKED_SOURCE_LOCK';

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_CLOSURE_SEAL_REJECTED';
        $artifact['next_step_recommendation'] = self::RUN_CODE;

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $artifact['artifact_path'] = $outputPath;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeArtifact($outputPath, $artifact, $overwrite);

        return $artifact;
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): void
    {
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException('Output artifact already exists: '.$path);
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash'], $artifact['artifact_path']);
        $this->sortRecursive($artifact);

        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function sortRecursive(array &$value): void
    {
        foreach ($value as &$item) {
            if (is_array($item)) {
                $this->sortRecursive($item);
            }
        }
        unset($item);

        if (! array_is_list($value)) {
            ksort($value);
        }
    }

    private function caseInsensitiveDuplicateKeys(array $payload, string $prefix = ''): array
    {
        $duplicates = [];
        if (! array_is_list($payload)) {
            $seen = [];
            foreach (array_keys($payload) as $key) {
                $lower = strtolower((string) $key);
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                if (isset($seen[$lower])) {
                    $duplicates[] = $seen[$lower].' / '.$path;
                } else {
                    $seen[$lower] = $path;
                }
            }
        }

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $childPrefix = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                $duplicates = array_merge($duplicates, $this->caseInsensitiveDuplicateKeys($value, $childPrefix));
            }
        }

        return $duplicates;
    }

    private function valueAt(array $source, array $path)
    {
        $current = $source;
        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
