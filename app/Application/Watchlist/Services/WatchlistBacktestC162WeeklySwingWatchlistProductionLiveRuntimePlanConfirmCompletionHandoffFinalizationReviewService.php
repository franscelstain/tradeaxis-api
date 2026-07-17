<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffFinalizationReviewService
{
    public const RUN_CODE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW';
    public const PHASE_LABEL = 'PR-66 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW';
    public const ARTIFACT_TYPE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW';

    public const DEFAULT_C162_HANDOFF_READINESS_ARTIFACT = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review.json';
    public const DEFAULT_EXPECTED_C162_HANDOFF_READINESS_HASH = '69a0d4384511782cd6e65eb25543275694a2b02a';
    public const DEFAULT_EXPECTED_C162_HANDOFF_READINESS_FILE_SHA1 = 'D48FF62967B413BA244AA502EE2F57F526AD2C10';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C162_HANDOFF_READINESS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_FINALIZATION_REVIEW';
    private const EXPECTED_C162_HANDOFF_READINESS_PHASE_LABEL = 'PR-65 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW';
    private const EXPECTED_C162_HANDOFF_READINESS_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C162_HANDOFF_COMPLETION_BOUNDARY_RECOMMENDATION = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY_REVIEW';

    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_COMPLETION_BOUNDARY_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_FINALIZATION_NOT_CONFIRMED_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_CONFIRMATION_MISSING';
    private const C162_HANDOFF_READINESS_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_COMPLETE_CONFIRMATION_MISSING';
    private const HANDOFF_READY_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_READY_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C162_HANDOFF_READINESS_LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_ARTIFACT_LOCK_MISMATCH';
    private const C162_HANDOFF_READINESS_FILE_SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_FILE_SHA1_LOCK_MISMATCH';
    private const C162_HANDOFF_READINESS_CONVERT_FROM_JSON_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C162_HANDOFF_READINESS_STATUS_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_STATUS_MISMATCH';
    private const C162_HANDOFF_READINESS_PHASE_LABEL_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_PHASE_LABEL_MISMATCH';
    private const C162_HANDOFF_READINESS_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_NEXT_RECOMMENDATION_MISMATCH';
    private const C162_HANDOFF_READINESS_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C162_HANDOFF_READINESS_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass',
        'production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass',
        'handoff_ready',
        'handoff_readiness_confirmed',
        'c161_topic_complete_confirmed',
        'plan_confirm_completion_closed_confirmed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'c161_finalization_lock_valid',
        'c161_plan_confirm_completion_finalization_valid',
        'c161_finalization_convert_from_json_pass',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review',
        'production_live_runtime_plan_confirm_completion_handoff_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest_created',
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
        'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
        'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
        'a01_remains_comparator_only',
        'c162_plan_confirm_completion_handoff_readiness_review_only',
        'c162_controlled_completion_only',
        'c162_not_publication',
        'c162_not_unrestricted_publication',
        'c162_not_plan_confirm_mutation',
        'c162_not_live_plan_confirm_rollout',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C162_HANDOFF_READINESS_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c162-*handoff-finalization*-test.json',
        'storage/app/watchlist/backtest/c162-*negative-*-test.json',
        'storage/app/watchlist/backtest/c162-*missing-*-test.json',
        'storage/app/watchlist/backtest/c162-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c162-*invalid-*-test.json',
    ];

    public function execute(
        string $c162HandoffReadinessArtifact = self::DEFAULT_C162_HANDOFF_READINESS_ARTIFACT,
        string $expectedC162HandoffReadinessHash = self::DEFAULT_EXPECTED_C162_HANDOFF_READINESS_HASH,
        string $expectedC162HandoffReadinessFileSha1 = self::DEFAULT_EXPECTED_C162_HANDOFF_READINESS_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c162HandoffReadinessArtifact, $expectedC162HandoffReadinessHash, $expectedC162HandoffReadinessFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C162_HANDOFF_READINESS_LOCK_MISMATCH_STATUS, 'C162 handoff readiness artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c162_handoff_readiness_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C162_HANDOFF_READINESS_CONVERT_FROM_JSON_STATUS, 'C162 handoff readiness artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C162_HANDOFF_READINESS_LOCK_MISMATCH_STATUS, 'C162 handoff readiness artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C162_HANDOFF_READINESS_FILE_SHA1_MISMATCH_STATUS, 'C162 handoff readiness file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $readiness = $load['payload'];
        if (($readiness['status'] ?? null) !== self::EXPECTED_C162_HANDOFF_READINESS_STATUS || ($readiness['reason_code'] ?? null) !== self::EXPECTED_C162_HANDOFF_READINESS_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_READINESS_STATUS_MISMATCH_STATUS, 'C162 handoff readiness status/reason is not finalization-ready.', $outputPath, $overwrite);
        }
        if (($readiness['phase_label'] ?? null) !== self::EXPECTED_C162_HANDOFF_READINESS_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_READINESS_PHASE_LABEL_MISMATCH_STATUS, 'C162 handoff readiness phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c162NextRecommendationMatches($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_READINESS_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C162 handoff readiness next recommendation is not C162 handoff finalization.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C162 handoff readiness evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c162HandoffReadinessStateValid($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_READINESS_STATE_INVALID_STATUS, 'C162 handoff readiness evidence is incomplete for C162 handoff finalization.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($readiness)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C162 handoff readiness candidate scope does not match locked handoff finalization scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C162 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_finalization_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_FINALIZATION_NOT_CONFIRMED_STATUS, 'C162 requires --handoff-finalization-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c162_handoff_readiness_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C162_HANDOFF_READINESS_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C162 requires --c162-handoff-readiness-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_ready_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_READY_CONFIRMATION_MISSING_STATUS, 'C162 requires --handoff-ready-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C162 finalizes the PLAN/CONFIRM completion handoff package for E02 primary and B01 backup. This remains controlled, artifact-backed, and does not mutate PLAN/CONFIRM, execute live rollout, or unlock free publication.';
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZED_READY_FOR_C162_HANDOFF_COMPLETION_BOUNDARY_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C162_HANDOFF_COMPLETION_BOUNDARY_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-66',
            'internal_checkpoint' => 'C162',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW',
            'status' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_NOT_RUN',
            'reason_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_finalization_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_finalization_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_finalization_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_handoff_finalization_review_pass' => false,
            'handoff_ready' => false,
            'handoff_finalized' => false,
            'handoff_finalization_confirmed' => false,
            'handoff_finalization_go_decision' => 'NO_GO',
            'c162_handoff_readiness_complete_confirmed' => false,
            'handoff_ready_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'c162_handoff_readiness_lock_valid' => false,
            'c162_plan_confirm_completion_handoff_readiness_valid' => false,
            'c162_handoff_readiness_convert_from_json_pass' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_review' => false,
            'production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_manifest_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => false,
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => true,
            'controlled_completion_path' => null,
            'controlled_completion_hash' => null,
            'controlled_completion_file_sha1' => null,
            'controlled_completion_record_count' => 0,
            'weekly_swing_watchlist_official_output_generated' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => false,
            'weekly_swing_watchlist_runtime_active' => false,
            'weekly_swing_watchlist_live_output_enabled' => false,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => false,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c162_plan_confirm_completion_handoff_finalization_review_only' => true,
            'c162_controlled_completion_only' => true,
            'c162_not_publication' => true,
            'c162_not_unrestricted_publication' => true,
            'c162_not_plan_confirm_mutation' => true,
            'c162_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_finalization_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_finalization_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_finalization_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_handoff_finalization_review_pass' => true,
            'handoff_ready' => true,
            'handoff_finalized' => true,
            'handoff_finalization_confirmed' => true,
            'handoff_finalization_go_decision' => 'HANDOFF_FINALIZED_GO',
            'c162_handoff_readiness_complete_confirmed' => true,
            'handoff_ready_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'c162_handoff_readiness_lock_valid' => true,
            'c162_plan_confirm_completion_handoff_readiness_valid' => true,
            'c162_handoff_readiness_convert_from_json_pass' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_review' => true,
            'production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_manifest_created' => true,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $readiness = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($readiness, $load, $pass, $options));
        $artifact['c162_handoff_readiness_lock_validation_summary'] = $this->c162HandoffReadinessLockValidationSummary($load, $readiness);
        $artifact['c162_plan_confirm_completion_handoff_readiness_carry_forward_summary'] = $this->c162HandoffReadinessCarryForwardSummary($readiness);
        $artifact['plan_confirm_completion_handoff_finalization_guard_summary'] = $this->handoffFinalizationGuardSummary($readiness, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($readiness, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c162_handoff_finalization_decision'] = $this->handoffFinalizationDecision($pass, $options);
        $artifact['next_plan_confirm_completion_handoff_completion_boundary_decision'] = $this->nextHandoffCompletionBoundaryDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_manifest'] = $this->handoffFinalizationManifest($readiness, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_checklist'] = $this->handoffFinalizationChecklist($pass, $options);
        $artifact['c162_candidate_plan_confirm_completion_handoff_finalization_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($readiness);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $readiness, array $load, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($readiness['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'controlled_completion_path' => $readiness['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $readiness['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $readiness['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($readiness['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($readiness['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($readiness['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($readiness['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($readiness['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($readiness['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($readiness['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c162_handoff_readiness_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c162_plan_confirm_completion_handoff_readiness_valid' => $this->c162HandoffReadinessStateValid($readiness),
            'c162_handoff_readiness_convert_from_json_pass' => $load['convert_from_json_pass'],
            'handoff_ready' => (bool) ($readiness['handoff_ready'] ?? false),
            'handoff_finalized' => $pass,
            'handoff_finalization_confirmed' => (bool) ($options['handoff_finalization_confirmed'] ?? false),
            'handoff_finalization_go_decision' => $pass ? 'HANDOFF_FINALIZED_GO' : 'NO_GO',
            'c162_handoff_readiness_complete_confirmed' => (bool) ($options['c162_handoff_readiness_complete_confirmed'] ?? false),
            'handoff_ready_confirmed' => (bool) ($options['handoff_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function c162HandoffReadinessStateValid(array $readiness): bool
    {
        foreach (self::REQUIRED_C162_HANDOFF_READINESS_TRUE_FIELDS as $field) {
            if (($readiness[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C162_HANDOFF_READINESS_FALSE_FIELDS as $field) {
            if (($readiness[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($readiness['handoff_readiness_go_decision'] ?? null) === 'HANDOFF_READY_GO'
            && ($readiness['controlled_completion_record_count'] ?? 0) === 2
            && trim((string) ($readiness['controlled_completion_path'] ?? '')) !== ''
            && trim((string) ($readiness['controlled_completion_hash'] ?? '')) !== ''
            && trim((string) ($readiness['controlled_completion_file_sha1'] ?? '')) !== ''
            && $this->valueAt($readiness, ['next_plan_confirm_completion_handoff_finalization_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($readiness, ['next_plan_confirm_completion_handoff_finalization_decision', 'topic_stage_advances_within_c162_handoff_after_readiness']) === true
            && $this->valueAt($readiness, ['next_plan_confirm_completion_handoff_finalization_decision', 'c162_handoff_readiness_complete']) === true
            && $this->valueAt($readiness, ['c162_handoff_readiness_decision', 'review_valid']) === true
            && $this->valueAt($readiness, ['c162_handoff_readiness_decision', 'handoff_ready']) === true
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest', 'ready_for_plan_confirm_completion_handoff_finalization_review']) === true
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest', 'handoff_readiness_artifact_only']) === true
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest', 'handoff_readiness_used_for_free_publication']) === false
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest', 'handoff_readiness_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest', 'handoff_readiness_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_checklist', 'artifact_only']) === true
            && $this->valueAt($readiness, ['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_checklist', 'weekly_swing_stock_recommendation_free_published_in_c162_handoff_readiness']) === false;
    }

    private function c162NextRecommendationMatches(array $readiness): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_handoff_finalization_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($readiness, $path) !== self::EXPECTED_C162_HANDOFF_READINESS_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function publicationAndPlanGuardClean(array $readiness): bool
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
            if (($readiness[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $readiness): bool
    {
        return ($readiness['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($readiness['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($readiness['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($readiness['primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review'] ?? null) === true
            && ($readiness['backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review'] ?? null) === true
            && ($readiness['comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review'] ?? null) === false
            && ($readiness['a01_remains_comparator_only'] ?? null) === true
            && ($readiness['a01_promoted'] ?? false) === false
            && ($readiness['candidate_promotion_executed'] ?? false) === false
            && ($readiness['candidate_rerank_executed'] ?? false) === false;
    }

    private function c162HandoffReadinessLockValidationSummary(array $load, array $readiness): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C162_HANDOFF_READINESS_STATUS,
            'actual_status' => $readiness['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C162_HANDOFF_READINESS_PHASE_LABEL,
            'actual_phase_label' => $readiness['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C162_HANDOFF_READINESS_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c162NextRecommendationMatches($readiness),
            'c162_handoff_readiness_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function c162HandoffReadinessCarryForwardSummary(array $readiness): array
    {
        return [
            'validation_completed' => true,
            'c162_plan_confirm_completion_handoff_readiness_valid' => $this->c162HandoffReadinessStateValid($readiness),
            'topic_code' => $readiness['topic_code'] ?? null,
            'topic_stage' => $readiness['topic_stage'] ?? null,
            'handoff_readiness_go_decision' => $readiness['handoff_readiness_go_decision'] ?? null,
            'handoff_ready' => (bool) ($readiness['handoff_ready'] ?? false),
            'controlled_completion_path' => $readiness['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $readiness['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $readiness['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($readiness['controlled_completion_record_count'] ?? 0),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function handoffFinalizationGuardSummary(array $readiness, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'handoff_finalization_valid' => $pass,
            'c162_handoff_readiness_complete' => $this->valueAt($readiness, ['next_plan_confirm_completion_handoff_finalization_decision', 'c162_handoff_readiness_complete']) === true,
            'handoff_ready' => (bool) ($readiness['handoff_ready'] ?? false),
            'ready_for_plan_confirm_completion_handoff_finalization_review' => (bool) ($readiness['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review'] ?? false),
            'handoff_finalized' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $readiness, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($readiness),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
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
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'handoff_finalization_confirmation_required' => true,
            'handoff_finalization_confirmed' => (bool) ($options['handoff_finalization_confirmed'] ?? false),
            'c162_handoff_readiness_complete_confirmation_required' => true,
            'c162_handoff_readiness_complete_confirmed' => (bool) ($options['c162_handoff_readiness_complete_confirmed'] ?? false),
            'handoff_ready_confirmation_required' => true,
            'handoff_ready_confirmed' => (bool) ($options['handoff_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
    }

    private function handoffFinalizationDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'handoff_finalized' => $pass,
            'handoff_finalization_go_decision' => $pass ? 'HANDOFF_FINALIZED_GO' : 'NO_GO',
            'handoff_finalization_confirmed' => (bool) ($options['handoff_finalization_confirmed'] ?? false),
            'c162_handoff_readiness_complete_confirmed' => (bool) ($options['c162_handoff_readiness_complete_confirmed'] ?? false),
            'handoff_ready_confirmed' => (bool) ($options['handoff_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff finalized; handoff completion boundary may start next' : 'targeted repair required before C162 handoff finalization can be recorded',
        ];
    }

    private function nextHandoffCompletionBoundaryDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C162_HANDOFF_COMPLETION_BOUNDARY_RECOMMENDATION : 'C162_TARGETED_HANDOFF_FINALIZATION_REPAIR',
            'next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff completion boundary review only; no PLAN/CONFIRM mutation or live rollout is authorized by C162 handoff finalization' : 'targeted repair before C162 handoff finalization can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c162_handoff_finalization_artifact' => $pass,
            'topic_stage_advances_within_c162_handoff_after_finalization' => $pass,
            'c162_handoff_finalization_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function handoffFinalizationManifest(array $readiness, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_handoff_finalization_review',
            'source_artifact' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW',
            'source_artifact_path' => self::DEFAULT_C162_HANDOFF_READINESS_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C162_HANDOFF_READINESS_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C162_HANDOFF_READINESS_FILE_SHA1,
            'handoff_ready' => (bool) ($readiness['handoff_ready'] ?? false),
            'handoff_finalized' => $pass,
            'handoff_finalization_go_decision' => $pass ? 'HANDOFF_FINALIZED_GO' : 'NO_GO',
            'handoff_finalization_confirmed' => (bool) ($options['handoff_finalization_confirmed'] ?? false),
            'c162_handoff_readiness_complete_confirmed' => (bool) ($options['c162_handoff_readiness_complete_confirmed'] ?? false),
            'handoff_ready_confirmed' => (bool) ($options['handoff_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_hash' => $readiness['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $readiness['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($readiness['controlled_completion_record_count'] ?? 0),
            'ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            'handoff_finalization_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'handoff_finalization_used_for_free_publication' => false,
            'handoff_finalization_used_for_plan_confirm_mutation' => false,
            'handoff_finalization_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function handoffFinalizationChecklist(bool $pass, array $options): array
    {
        return [
            'handoff_finalization_reviewed' => true,
            'c162_handoff_readiness_source_lock_reviewed' => true,
            'c162_handoff_readiness_complete_reviewed' => true,
            'handoff_finalization_required' => true,
            'handoff_finalization_confirmed' => (bool) ($options['handoff_finalization_confirmed'] ?? false),
            'c162_handoff_readiness_complete_confirmed' => (bool) ($options['c162_handoff_readiness_complete_confirmed'] ?? false),
            'handoff_ready_confirmed' => (bool) ($options['handoff_ready_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_handoff_finalization_gate_required' => true,
            'negative_c162_handoff_readiness_complete_gate_required' => true,
            'negative_handoff_ready_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'handoff_finalization_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c162_handoff_finalization' => false,
            'ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'plan_confirm_completion_handoff_finalization_review_valid' => $pass,
            'handoff_finalized' => $pass,
            'ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            'plan_confirm_mutated' => false,
            'live_rollout_executed' => false,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c162_role' => 'primary_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review',
                'primary_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c162_role' => 'backup_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review',
                'backup_candidate_ready_for_plan_confirm_completion_handoff_completion_boundary_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c162_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_completion_handoff_completion_boundary_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $readiness): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($readiness),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function documentationHygieneGuardSummary(array $load): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c162_handoff_readiness_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c162_handoff_readiness_artifact_not_modified' => true,
            'c162_handoff_finalization_review_is_artifact_only_not_free_publication_or_live_rollout' => true,
            'c162_handoff_finalization_review_closes_handoff_finalization_step' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-66_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW',
            'c162_handoff_readiness_carried_forward' => true,
            'handoff_finalized' => $pass,
            'handoff_finalization_complete' => $pass,
            'topic_stage_advances_within_c162_handoff_after_finalization' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C162_HANDOFF_COMPLETION_BOUNDARY_RECOMMENDATION : 'C162_TARGETED_HANDOFF_FINALIZATION_REPAIR',
            'planned_next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff completion boundary review only; C162 handoff finalization does not mutate PLAN/CONFIRM, execute live rollout, or authorize free publication' : 'targeted repair before C162 handoff finalization can be recorded',
            'topic_stage_advances_within_c162_handoff_after_finalization' => $pass,
            'c162_handoff_finalization_complete' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C162 handoff finalization artifact hash',
                'locked C162 handoff finalization file SHA1',
                'handoff-finalized decision',
                'C162 handoff readiness complete',
                'PLAN/CONFIRM unchanged',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C162 handoff finalization validates C162 handoff readiness artifact_hash and file SHA1 locks before handoff finalization is recorded.',
            'C162 handoff finalization validates C162 handoff readiness completion, controlled completion lock evidence, candidate scope, and next recommendation to C162.',
            'C162 handoff finalization requires operator approval plus handoff finalization, C162 handoff readiness complete, handoff-ready, PLAN/CONFIRM unchanged, no-live-rollout, and free-publication lock confirmations.',
            'C162 handoff finalization keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C162 handoff finalization recommends C162 PLAN/CONFIRM completion handoff completion boundary review.',
            'C162 handoff finalization does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
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

    private function sourceArtifactLocks(array $load): array
    {
        return [
            'c162_plan_confirm_completion_handoff_readiness' => [
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
            'expected_c162_handoff_readiness_hash' => $load['expected_hash'],
            'actual_c162_handoff_readiness_hash' => $load['actual_hash'],
            'c162_handoff_readiness_hash_match' => $load['hash_match'],
            'expected_c162_handoff_readiness_file_sha1' => $load['expected_file_sha1'],
            'actual_c162_handoff_readiness_file_sha1' => $load['actual_file_sha1'],
            'c162_handoff_readiness_file_sha1_match' => $load['file_sha1_match'],
            'c162_handoff_readiness_convert_from_json_pass' => $load['convert_from_json_pass'],
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

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'expected_hash' => $expectedHash,
            'actual_hash' => $actualHash,
            'hash_match' => $actualHash !== null && hash_equals($expectedHash, (string) $actualHash),
            'expected_file_sha1' => strtoupper($expectedFileSha1),
            'actual_file_sha1' => $actualFileSha1,
            'file_sha1_match' => $actualFileSha1 !== null && strtoupper($expectedFileSha1) === $actualFileSha1,
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

    private function failureAttributionSummary(array $status): array
    {
        return [
            'failure_codes' => array_values(array_filter($status)),
            'failure_count' => count(array_values(array_filter($status))),
        ];
    }

    private function blocked(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([$status]);

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function rejected(array $artifact, string $status, string $message, string $outputPath, bool $overwrite): array
    {
        $artifact['status'] = $status;
        $artifact['reason_code'] = $status;
        $artifact['message'] = $message;
        $artifact['planned_next_summary'] = $this->plannedNextSummary(false);
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
