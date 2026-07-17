<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService
{
    public const RUN_CODE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW';
    public const PHASE_LABEL = 'PR-65 / C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW';
    public const ARTIFACT_TYPE = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW';

    public const DEFAULT_C161_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C161_FINALIZATION_HASH = '9409df354fc360554d502b4787878c770e806d45';
    public const DEFAULT_EXPECTED_C161_FINALIZATION_FILE_SHA1 = '06441C61A6A4B1F4BFE4C8398CD0BB4ED1C552EF';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C161_FINALIZATION_STATUS = 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_PLAN_CONFIRM_COMPLETION_CLOSED_READY_FOR_HANDOFF_READINESS_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C161_FINALIZATION_PHASE_LABEL = 'PR-64 / C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C161_FINALIZATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const C162_HANDOFF_FINALIZATION_RECOMMENDATION = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW';

    private const PASS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_FINALIZATION_REVIEW';
    private const APPROVAL_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const HANDOFF_READINESS_NOT_CONFIRMED_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_CONFIRMATION_MISSING';
    private const C161_TOPIC_COMPLETE_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_TOPIC_COMPLETE_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_COMPLETION_CLOSED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_COMPLETION_CLOSED_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const NO_LIVE_ROLLOUT_CONFIRMATION_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_MISSING_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C161_FINALIZATION_LOCK_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const C161_FINALIZATION_FILE_SHA1_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const C161_FINALIZATION_CONVERT_FROM_JSON_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C161_FINALIZATION_STATUS_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_STATUS_MISMATCH';
    private const C161_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const C161_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C161_FINALIZATION_STATE_INVALID_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_STATE_INVALID';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C161_FINALIZATION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_go_decision_finalization_review_pass',
        'production_live_runtime_plan_confirm_completion_go_decision_finalization_review_pass',
        'operator_go_decision',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'plan_confirm_completion_finalization_confirmed',
        'plan_confirm_completion_closed',
        'plan_confirm_unchanged_confirmed',
        'no_live_plan_confirm_rollout_confirmed',
        'free_publication_locked_confirmed',
        'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_review',
        'production_live_runtime_plan_confirm_completion_handoff_readiness_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_review_allowed_next',
        'weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest_created',
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
        'c161_operator_go_no_go_lock_valid',
        'c161_operator_go_no_go_review_valid',
        'c161_operator_go_no_go_convert_from_json_pass',
        'c161_result_review_lock_valid',
        'c161_plan_confirm_completion_result_review_valid',
        'c161_result_review_convert_from_json_pass',
        'c161_execution_lock_valid',
        'c161_completion_execution_valid',
        'c161_execution_convert_from_json_pass',
        'controlled_completion_lock_valid',
        'controlled_completion_integrity_valid',
        'controlled_completion_convert_from_json_pass',
        'result_review_confirmed',
        'controlled_completion_result_confirmed',
        'controlled_completion_only_confirmed',
        'primary_candidate_ready_for_plan_confirm_completion_handoff_readiness_review',
        'backup_candidate_ready_for_plan_confirm_completion_handoff_readiness_review',
        'a01_remains_comparator_only',
        'c161_plan_confirm_completion_go_decision_finalization_review_only',
        'c161_controlled_completion_only',
        'c161_not_publication',
        'c161_not_unrestricted_publication',
        'c161_not_plan_confirm_mutation',
        'c161_not_live_plan_confirm_rollout',
        'c161_topic_complete_after_finalization',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C161_FINALIZATION_FALSE_FIELDS = [
        'weekly_swing_watchlist_official_output_published',
        'weekly_swing_watchlist_publication_allowed',
        'weekly_swing_watchlist_unrestricted_publication_allowed',
        'plan_confirm_mutation_allowed',
        'plan_confirm_mutated',
        'plan_confirm_runtime_reads_activated_catalog',
        'live_plan_confirm_rollout_allowed',
        'live_plan_confirm_rollout_executed',
        'comparator_candidate_ready_for_plan_confirm_completion_handoff_readiness_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c162-*handoff-readiness*-test.json',
        'storage/app/watchlist/backtest/c162-*negative-*-test.json',
        'storage/app/watchlist/backtest/c162-*missing-*-test.json',
        'storage/app/watchlist/backtest/c162-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c162-*invalid-*-test.json',
    ];

    public function execute(
        string $c161FinalizationArtifact = self::DEFAULT_C161_FINALIZATION_ARTIFACT,
        string $expectedC161FinalizationHash = self::DEFAULT_EXPECTED_C161_FINALIZATION_HASH,
        string $expectedC161FinalizationFileSha1 = self::DEFAULT_EXPECTED_C161_FINALIZATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $load = $this->loadArtifactLock($c161FinalizationArtifact, $expectedC161FinalizationHash, $expectedC161FinalizationFileSha1);
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($load);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($load));

        if (! $load['exists'] || ! is_array($load['payload'])) {
            return $this->blocked($artifact, self::C161_FINALIZATION_LOCK_MISMATCH_STATUS, 'C161 finalization artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $load, $options, false);
            $artifact['c161_finalization_convert_from_json_duplicate_keys'] = $load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C161_FINALIZATION_CONVERT_FROM_JSON_STATUS, 'C161 finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $load['hash_match']) {
            return $this->blocked($artifact, self::C161_FINALIZATION_LOCK_MISMATCH_STATUS, 'C161 finalization artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $load['file_sha1_match']) {
            return $this->blocked($artifact, self::C161_FINALIZATION_FILE_SHA1_MISMATCH_STATUS, 'C161 finalization file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $finalization = $load['payload'];
        if (($finalization['status'] ?? null) !== self::EXPECTED_C161_FINALIZATION_STATUS || ($finalization['reason_code'] ?? null) !== self::EXPECTED_C161_FINALIZATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C161_FINALIZATION_STATUS_MISMATCH_STATUS, 'C161 finalization status/reason is not handoff-readiness ready.', $outputPath, $overwrite);
        }
        if (($finalization['phase_label'] ?? null) !== self::EXPECTED_C161_FINALIZATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C161_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS, 'C161 finalization phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c161NextRecommendationMatches($finalization)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C161_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C161 finalization next recommendation is not C162 handoff readiness.', $outputPath, $overwrite);
        }
        if (! $this->publicationAndPlanGuardClean($finalization)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PUBLICATION_OR_PLAN_MUTATION_STATUS, 'C161 finalization evidence has publication, PLAN/CONFIRM mutation, activated-catalog read, or live rollout already enabled.', $outputPath, $overwrite);
        }
        if (! $this->c161FinalizationStateValid($finalization)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C161_FINALIZATION_STATE_INVALID_STATUS, 'C161 finalization evidence is incomplete for C162 handoff readiness.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($finalization)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'C161 finalization candidate scope does not match locked handoff readiness scope.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::APPROVAL_MISSING_STATUS, 'C162 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['handoff_readiness_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::HANDOFF_READINESS_NOT_CONFIRMED_STATUS, 'C162 requires --handoff-readiness-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['c161_topic_complete_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::C161_TOPIC_COMPLETE_CONFIRMATION_MISSING_STATUS, 'C162 requires --c161-topic-complete-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_completion_closed_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $load, $options, false), self::PLAN_CONFIRM_COMPLETION_CLOSED_CONFIRMATION_MISSING_STATUS, 'C162 requires --plan-confirm-completion-closed-confirmed.', $outputPath, $overwrite);
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
        $artifact['message'] = 'C162 marks the PLAN/CONFIRM completion package handoff-ready for E02 primary and B01 backup. This is still controlled, artifact-backed, and does not mutate PLAN/CONFIRM, execute live rollout, or unlock free publication.';
        $artifact['diagnostic_conclusion'] = 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READY_READY_FOR_C162_HANDOFF_FINALIZATION_PLAN_UNCHANGED_NO_LIVE_ROLLOUT_NO_FREE_PUBLICATION';
        $artifact['next_step_recommendation'] = self::C162_HANDOFF_FINALIZATION_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-65',
            'internal_checkpoint' => 'C162',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW',
            'status' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_NOT_RUN',
            'reason_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass' => false,
            'production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass' => false,
            'handoff_ready' => false,
            'handoff_readiness_confirmed' => false,
            'handoff_readiness_go_decision' => 'NO_GO',
            'c161_topic_complete_confirmed' => false,
            'plan_confirm_completion_closed_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'no_live_plan_confirm_rollout_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'c161_finalization_lock_valid' => false,
            'c161_plan_confirm_completion_finalization_valid' => false,
            'c161_finalization_convert_from_json_pass' => false,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review' => false,
            'production_live_runtime_plan_confirm_completion_handoff_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review_allowed_next' => false,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest_created' => false,
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
            'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => false,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => false,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c162_plan_confirm_completion_handoff_readiness_review_only' => true,
            'c162_controlled_completion_only' => true,
            'c162_not_publication' => true,
            'c162_not_unrestricted_publication' => true,
            'c162_not_plan_confirm_mutation' => true,
            'c162_not_live_plan_confirm_rollout' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass' => true,
            'production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass' => true,
            'handoff_ready' => true,
            'handoff_readiness_confirmed' => true,
            'handoff_readiness_go_decision' => 'HANDOFF_READY_GO',
            'c161_topic_complete_confirmed' => true,
            'plan_confirm_completion_closed_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'no_live_plan_confirm_rollout_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'c161_finalization_lock_valid' => true,
            'c161_plan_confirm_completion_finalization_valid' => true,
            'c161_finalization_convert_from_json_pass' => true,
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review' => true,
            'production_live_runtime_plan_confirm_completion_handoff_finalization_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review_allowed_next' => true,
            'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest_created' => true,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => true,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => true,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => false,
        ];
    }

    private function completeSections(array $artifact, array $load, array $options, bool $pass): array
    {
        $finalization = is_array($load['payload'] ?? null) ? $load['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($finalization, $load, $pass, $options));
        $artifact['c161_finalization_lock_validation_summary'] = $this->c161FinalizationLockValidationSummary($load, $finalization);
        $artifact['c161_plan_confirm_completion_finalization_carry_forward_summary'] = $this->c161FinalizationCarryForwardSummary($finalization);
        $artifact['plan_confirm_completion_handoff_readiness_guard_summary'] = $this->handoffReadinessGuardSummary($finalization, $pass);
        $artifact['candidate_scope_freeze_summary'] = $this->candidateScopeFreezeSummary($finalization, $pass);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['c162_handoff_readiness_decision'] = $this->handoffReadinessDecision($pass, $options);
        $artifact['next_plan_confirm_completion_handoff_finalization_decision'] = $this->nextHandoffFinalizationDecision($pass);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest'] = $this->handoffReadinessManifest($finalization, $pass, $options);
        $artifact['weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_checklist'] = $this->handoffReadinessChecklist($pass, $options);
        $artifact['c162_candidate_plan_confirm_completion_handoff_readiness_scorecard'] = $this->candidateScorecard($pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($finalization);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($load);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $finalization, array $load, bool $pass, array $options): array
    {
        return [
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed' => (bool) ($finalization['weekly_swing_watchlist_plan_confirm_completion_result_reviewed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed' => (bool) ($finalization['weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created' => (bool) ($finalization['weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created'] ?? false),
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only' => (bool) ($finalization['weekly_swing_watchlist_plan_confirm_completion_controlled_only'] ?? false),
            'controlled_completion_path' => $finalization['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $finalization['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $finalization['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($finalization['controlled_completion_record_count'] ?? 0),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($finalization['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($finalization['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($finalization['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($finalization['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($finalization['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($finalization['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c161_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'] && $load['convert_from_json_pass'],
            'c161_plan_confirm_completion_finalization_valid' => $this->c161FinalizationStateValid($finalization),
            'c161_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
            'handoff_ready' => $pass,
            'handoff_readiness_confirmed' => (bool) ($options['handoff_readiness_confirmed'] ?? false),
            'handoff_readiness_go_decision' => $pass ? 'HANDOFF_READY_GO' : 'NO_GO',
            'c161_topic_complete_confirmed' => (bool) ($options['c161_topic_complete_confirmed'] ?? false),
            'plan_confirm_completion_closed_confirmed' => (bool) ($options['plan_confirm_completion_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => false,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function c161FinalizationStateValid(array $finalization): bool
    {
        foreach (self::REQUIRED_C161_FINALIZATION_TRUE_FIELDS as $field) {
            if (($finalization[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C161_FINALIZATION_FALSE_FIELDS as $field) {
            if (($finalization[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($finalization['operator_decision'] ?? null) === 'GO'
            && ($finalization['controlled_completion_record_count'] ?? 0) === 2
            && trim((string) ($finalization['controlled_completion_path'] ?? '')) !== ''
            && trim((string) ($finalization['controlled_completion_hash'] ?? '')) !== ''
            && trim((string) ($finalization['controlled_completion_file_sha1'] ?? '')) !== ''
            && $this->valueAt($finalization, ['next_plan_confirm_completion_handoff_readiness_decision', 'next_recommendation']) === self::RUN_CODE
            && $this->valueAt($finalization, ['next_plan_confirm_completion_handoff_readiness_decision', 'same_topic_c161_complete']) === true
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest', 'plan_confirm_completion_go_decision_finalization_review_pass']) === true
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest', 'ready_for_plan_confirm_completion_handoff_readiness_review']) === true
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest', 'go_decision_finalization_artifact_only']) === true
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest', 'go_decision_finalization_used_for_free_publication']) === false
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_manifest', 'go_decision_finalization_used_for_live_plan_confirm_rollout']) === false
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_checklist', 'artifact_only']) === true
            && $this->valueAt($finalization, ['weekly_swing_watchlist_plan_confirm_completion_go_decision_finalization_checklist', 'weekly_swing_stock_recommendation_free_published_in_c161_finalization']) === false;
    }

    private function c161NextRecommendationMatches(array $finalization): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_plan_confirm_completion_handoff_readiness_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($finalization, $path) !== self::EXPECTED_C161_FINALIZATION_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function publicationAndPlanGuardClean(array $finalization): bool
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
            if (($finalization[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function candidateScopeMatches(array $finalization): bool
    {
        return ($finalization['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($finalization['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($finalization['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($finalization['primary_candidate_ready_for_plan_confirm_completion_handoff_readiness_review'] ?? null) === true
            && ($finalization['backup_candidate_ready_for_plan_confirm_completion_handoff_readiness_review'] ?? null) === true
            && ($finalization['comparator_candidate_ready_for_plan_confirm_completion_handoff_readiness_review'] ?? null) === false
            && ($finalization['a01_remains_comparator_only'] ?? null) === true
            && ($finalization['a01_promoted'] ?? false) === false
            && ($finalization['candidate_promotion_executed'] ?? false) === false
            && ($finalization['candidate_rerank_executed'] ?? false) === false;
    }

    private function c161FinalizationLockValidationSummary(array $load, array $finalization): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C161_PLAN_CONFIRM_COMPLETION_GO_DECISION_FINALIZATION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C161_FINALIZATION_STATUS,
            'actual_status' => $finalization['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C161_FINALIZATION_PHASE_LABEL,
            'actual_phase_label' => $finalization['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C161_FINALIZATION_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c161NextRecommendationMatches($finalization),
            'c161_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function c161FinalizationCarryForwardSummary(array $finalization): array
    {
        return [
            'validation_completed' => true,
            'c161_plan_confirm_completion_finalization_valid' => $this->c161FinalizationStateValid($finalization),
            'topic_code' => $finalization['topic_code'] ?? null,
            'topic_stage' => $finalization['topic_stage'] ?? null,
            'operator_decision' => $finalization['operator_decision'] ?? null,
            'go_decision_finalized' => (bool) ($finalization['go_decision_finalized'] ?? false),
            'c161_topic_complete_after_finalization' => (bool) ($finalization['c161_topic_complete_after_finalization'] ?? false),
            'controlled_completion_path' => $finalization['controlled_completion_path'] ?? null,
            'controlled_completion_hash' => $finalization['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $finalization['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($finalization['controlled_completion_record_count'] ?? 0),
            'official_output_published' => false,
            'publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function handoffReadinessGuardSummary(array $finalization, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'handoff_readiness_valid' => $pass,
            'c161_topic_complete_after_finalization' => (bool) ($finalization['c161_topic_complete_after_finalization'] ?? false),
            'plan_confirm_completion_closed' => (bool) ($finalization['plan_confirm_completion_closed'] ?? false),
            'ready_for_plan_confirm_completion_handoff_readiness_review' => (bool) ($finalization['ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_review'] ?? false),
            'handoff_ready' => $pass,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function candidateScopeFreezeSummary(array $finalization, bool $pass): array
    {
        return [
            'candidate_scope_locked' => true,
            'candidate_scope_valid' => $this->candidateScopeMatches($finalization),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => false,
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
            'handoff_readiness_confirmation_required' => true,
            'handoff_readiness_confirmed' => (bool) ($options['handoff_readiness_confirmed'] ?? false),
            'c161_topic_complete_confirmation_required' => true,
            'c161_topic_complete_confirmed' => (bool) ($options['c161_topic_complete_confirmed'] ?? false),
            'plan_confirm_completion_closed_confirmation_required' => true,
            'plan_confirm_completion_closed_confirmed' => (bool) ($options['plan_confirm_completion_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmation_required' => true,
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
        ];
    }

    private function handoffReadinessDecision(bool $pass, array $options): array
    {
        return [
            'review_valid' => $pass,
            'handoff_ready' => $pass,
            'handoff_readiness_go_decision' => $pass ? 'HANDOFF_READY_GO' : 'NO_GO',
            'handoff_readiness_confirmed' => (bool) ($options['handoff_readiness_confirmed'] ?? false),
            'c161_topic_complete_confirmed' => (bool) ($options['c161_topic_complete_confirmed'] ?? false),
            'plan_confirm_completion_closed_confirmed' => (bool) ($options['plan_confirm_completion_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'decision_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff ready; handoff finalization may start next' : 'targeted repair required before C162 handoff readiness can be recorded',
        ];
    }

    private function nextHandoffFinalizationDecision(bool $pass): array
    {
        return [
            'review_valid' => $pass,
            'next_recommendation' => $pass ? self::C162_HANDOFF_FINALIZATION_RECOMMENDATION : 'C162_TARGETED_HANDOFF_READINESS_REPAIR',
            'next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff finalization review only; no PLAN/CONFIRM mutation or live rollout is authorized by C162 handoff readiness' : 'targeted repair before C162 handoff readiness can be recorded',
            'next_is_concrete' => $pass,
            'next_requires_locked_c162_handoff_readiness_artifact' => $pass,
            'topic_stage_advances_within_c162_handoff_after_readiness' => $pass,
            'c162_handoff_readiness_complete' => $pass,
            'free_publication_allowed_next' => false,
            'plan_confirm_mutation_allowed_next' => false,
            'live_plan_confirm_rollout_allowed_next' => false,
        ];
    }

    private function handoffReadinessManifest(array $finalization, bool $pass, array $options): array
    {
        return [
            'manifest_created' => true,
            'manifest_context' => 'plan_confirm_completion_handoff_readiness_review',
            'source_artifact' => 'C161_PLAN_CONFIRM_COMPLETION_GO_DECISION_FINALIZATION_REVIEW',
            'source_artifact_path' => self::DEFAULT_C161_FINALIZATION_ARTIFACT,
            'source_artifact_hash' => self::DEFAULT_EXPECTED_C161_FINALIZATION_HASH,
            'source_file_sha1' => self::DEFAULT_EXPECTED_C161_FINALIZATION_FILE_SHA1,
            'handoff_ready' => $pass,
            'handoff_readiness_go_decision' => $pass ? 'HANDOFF_READY_GO' : 'NO_GO',
            'handoff_readiness_confirmed' => (bool) ($options['handoff_readiness_confirmed'] ?? false),
            'c161_topic_complete_confirmed' => (bool) ($options['c161_topic_complete_confirmed'] ?? false),
            'plan_confirm_completion_closed_confirmed' => (bool) ($options['plan_confirm_completion_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'controlled_completion_hash' => $finalization['controlled_completion_hash'] ?? null,
            'controlled_completion_file_sha1' => $finalization['controlled_completion_file_sha1'] ?? null,
            'controlled_completion_record_count' => (int) ($finalization['controlled_completion_record_count'] ?? 0),
            'ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            'handoff_readiness_artifact_only' => true,
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
            'handoff_readiness_used_for_free_publication' => false,
            'handoff_readiness_used_for_plan_confirm_mutation' => false,
            'handoff_readiness_used_for_live_plan_confirm_rollout' => false,
        ];
    }

    private function handoffReadinessChecklist(bool $pass, array $options): array
    {
        return [
            'handoff_readiness_reviewed' => true,
            'c161_finalization_source_lock_reviewed' => true,
            'c161_topic_complete_reviewed' => true,
            'handoff_readiness_required' => true,
            'handoff_readiness_confirmed' => (bool) ($options['handoff_readiness_confirmed'] ?? false),
            'c161_topic_complete_confirmed' => (bool) ($options['c161_topic_complete_confirmed'] ?? false),
            'plan_confirm_completion_closed_confirmed' => (bool) ($options['plan_confirm_completion_closed_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'no_live_plan_confirm_rollout_confirmed' => (bool) ($options['no_live_plan_confirm_rollout_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'operator_approval_required' => true,
            'negative_approval_gate_required' => true,
            'negative_handoff_readiness_gate_required' => true,
            'negative_c161_topic_complete_gate_required' => true,
            'negative_plan_confirm_completion_closed_gate_required' => true,
            'negative_plan_confirm_unchanged_gate_required' => true,
            'negative_no_live_rollout_gate_required' => true,
            'negative_free_publication_lock_gate_required' => true,
            'artifact_hash_lock_required' => true,
            'file_sha1_lock_required' => true,
            'convert_from_json_validation_required' => true,
            'handoff_readiness_review_only' => true,
            'artifact_only' => true,
            'weekly_swing_stock_recommendation_free_published_in_c162_handoff_readiness' => false,
            'ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
        ];
    }

    private function candidateScorecard(bool $pass): array
    {
        $base = [
            'plan_confirm_completion_handoff_readiness_review_valid' => $pass,
            'handoff_ready' => $pass,
            'ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            'plan_confirm_mutated' => false,
            'live_rollout_executed' => false,
            'free_published' => false,
        ];

        return [
            array_merge($base, [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c162_role' => 'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
                'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c162_role' => 'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
                'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review' => $pass,
            ]),
            array_merge($base, [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c162_role' => 'comparator_only_candidate',
                'ready_for_plan_confirm_completion_handoff_finalization_review' => false,
                'a01_remains_comparator_only' => true,
            ]),
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $finalization): array
    {
        return [
            'validation_completed' => true,
            'publication_and_plan_guard_clean' => $this->publicationAndPlanGuardClean($finalization),
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
            'c161_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
            'top_level_case_insensitive_duplicate_keys' => $load['case_insensitive_duplicate_keys'],
            'c161_finalization_artifact_not_modified' => true,
            'c162_handoff_readiness_review_is_artifact_only_not_free_publication_or_live_rollout' => true,
            'c162_handoff_readiness_review_closes_handoff_readiness_step' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-65_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW',
            'topic_code' => 'C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS',
            'topic_stage' => 'PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW',
            'c161_finalization_carried_forward' => true,
            'handoff_ready' => $pass,
            'handoff_readiness_complete' => $pass,
            'topic_stage_advances_within_c162_handoff_after_readiness' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::C162_HANDOFF_FINALIZATION_RECOMMENDATION : 'C162_TARGETED_HANDOFF_READINESS_REPAIR',
            'planned_next_scope' => $pass ? 'C162 PLAN/CONFIRM completion handoff finalization review only; C162 handoff readiness does not mutate PLAN/CONFIRM, execute live rollout, or authorize free publication' : 'targeted repair before C162 handoff readiness can be recorded',
            'topic_stage_advances_within_c162_handoff_after_readiness' => $pass,
            'c162_handoff_readiness_complete' => $pass,
            'planned_next_required_inputs' => $pass ? [
                'locked C162 handoff readiness artifact hash',
                'locked C162 handoff readiness file SHA1',
                'handoff-ready decision',
                'C161 topic complete',
                'PLAN/CONFIRM unchanged',
                'live rollout still disabled',
                'free publication still disabled',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C162 handoff readiness validates C161 finalization artifact_hash and file SHA1 locks before handoff readiness is recorded.',
            'C162 handoff readiness validates C161 topic completion, PLAN/CONFIRM completion closure, controlled completion lock evidence, candidate scope, and next recommendation to C162.',
            'C162 handoff readiness requires operator approval plus handoff readiness, C161 topic complete, PLAN/CONFIRM completion closed, PLAN/CONFIRM unchanged, no-live-rollout, and free-publication lock confirmations.',
            'C162 handoff readiness keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C162 handoff readiness recommends C162 PLAN/CONFIRM completion handoff finalization review.',
            'C162 handoff readiness does not mutate PLAN/CONFIRM, read activated catalog, execute live rollout, free-publish recommendations, or allow unrestricted publication.',
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
            'c161_plan_confirm_completion_go_decision_finalization' => [
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
            'expected_c161_finalization_hash' => $load['expected_hash'],
            'actual_c161_finalization_hash' => $load['actual_hash'],
            'c161_finalization_hash_match' => $load['hash_match'],
            'expected_c161_finalization_file_sha1' => $load['expected_file_sha1'],
            'actual_c161_finalization_file_sha1' => $load['actual_file_sha1'],
            'c161_finalization_file_sha1_match' => $load['file_sha1_match'],
            'c161_finalization_convert_from_json_pass' => $load['convert_from_json_pass'],
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
