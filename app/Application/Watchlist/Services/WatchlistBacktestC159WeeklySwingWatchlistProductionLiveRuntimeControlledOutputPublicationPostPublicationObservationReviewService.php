<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService
{
    public const RUN_CODE = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW';
    public const PHASE_LABEL = 'PR-51 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW';
    public const ARTIFACT_TYPE = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW';

    public const DEFAULT_C158_FINALIZATION_ARTIFACT = 'storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json';
    public const DEFAULT_EXPECTED_C158_FINALIZATION_HASH = 'd8e4bfc3f906f3bc613f9aae1e03a27a67f9241b';
    public const DEFAULT_EXPECTED_C158_FINALIZATION_FILE_SHA1 = 'D732BDF92A76DC25434C2DECC539CD26181C8F21';
    public const DEFAULT_CONTROLLED_PUBLICATION_ARTIFACT = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C158_FINALIZATION_STATUS = 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C158_FINALIZATION_PHASE_LABEL = 'PR-50 / C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW';
    private const EXPECTED_C158_FINALIZATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const NEXT_RESULT_REVIEW_RECOMMENDATION = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW';

    private const PASS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PASSED_CONTROLLED_PUBLICATION_OBSERVED_READY_FOR_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const OBSERVATION_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_POST_PUBLICATION_OBSERVATION_CONFIRMATION_MISSING';
    private const CONTROLLED_PUBLICATION_OBSERVATION_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_OBSERVATION_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C158_FINALIZATION_LOCK_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_ARTIFACT_LOCK_MISMATCH';
    private const C158_FINALIZATION_FILE_SHA1_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_FILE_SHA1_LOCK_MISMATCH';
    private const C158_FINALIZATION_CONVERT_FROM_JSON_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C158_FINALIZATION_STATUS_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_STATUS_MISMATCH';
    private const C158_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_PHASE_LABEL_MISMATCH';
    private const C158_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C158_FINALIZATION_INCOMPLETE_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_C158_FINALIZATION_INCOMPLETE';
    private const CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const FREE_PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_C158_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass',
        'production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass',
        'operator_go_decision_confirmed',
        'go_decision_finalized',
        'go_decision_finalization_confirmed',
        'controlled_publication_finalization_confirmed',
        'free_publication_locked_confirmed',
        'plan_confirm_unchanged_confirmed',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_review',
        'production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed_next',
        'controlled_output_publication_go_decision_finalization_manifest_created',
        'weekly_swing_watchlist_controlled_output_publication_result_reviewed',
        'weekly_swing_watchlist_controlled_output_publication_executed',
        'weekly_swing_watchlist_controlled_output_published',
        'weekly_swing_watchlist_controlled_publication_artifact_created',
        'weekly_swing_watchlist_official_output_generated',
        'weekly_swing_watchlist_controlled_publication_allowed',
        'runtime_bridge_active',
        'weekly_swing_watchlist_runtime_active',
        'weekly_swing_watchlist_live_output_enabled',
        'weekly_swing_watchlist_live_recommendation_generation_allowed',
        'c158_operator_go_no_go_lock_valid',
        'c158_operator_go_no_go_review_valid',
        'c158_operator_go_no_go_convert_from_json_pass',
        'c158_result_review_lock_valid',
        'c158_controlled_output_publication_result_review_valid',
        'controlled_publication_lock_valid',
        'controlled_publication_integrity_valid',
        'primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review',
        'backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review',
        'a01_remains_comparator_only',
        'c158_controlled_output_publication_go_decision_finalization_review_only',
        'c158_controlled_publication_only',
        'c158_not_free_publication',
        'c158_not_unrestricted_publication',
        'c158_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_C158_FALSE_FIELDS = [
        'comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c159-*no-*-test.json',
        'storage/app/watchlist/backtest/c159-*missing-*-test.json',
        'storage/app/watchlist/backtest/c159-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c159-*negative-*-test.json',
        'storage/app/watchlist/backtest/c159-*invalid-*-test.json',
    ];

    public function execute(
        string $c158FinalizationArtifact = self::DEFAULT_C158_FINALIZATION_ARTIFACT,
        string $expectedC158FinalizationHash = self::DEFAULT_EXPECTED_C158_FINALIZATION_HASH,
        string $expectedC158FinalizationFileSha1 = self::DEFAULT_EXPECTED_C158_FINALIZATION_FILE_SHA1,
        string $controlledPublicationArtifact = self::DEFAULT_CONTROLLED_PUBLICATION_ARTIFACT,
        string $expectedControlledPublicationHash = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH,
        string $expectedControlledPublicationFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $c158Load = $this->loadJsonLock($c158FinalizationArtifact, $expectedC158FinalizationHash, $expectedC158FinalizationFileSha1, 'artifact_hash');
        $publicationLoad = $this->loadJsonLock($controlledPublicationArtifact, $expectedControlledPublicationHash, $expectedControlledPublicationFileSha1, 'controlled_publication_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($c158Load, $publicationLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($c158Load, $publicationLoad));

        if (! $c158Load['exists'] || ! is_array($c158Load['payload'])) {
            return $this->blocked($artifact, self::C158_FINALIZATION_LOCK_MISMATCH_STATUS, 'C158 finalization artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $c158Load['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $c158Load, $publicationLoad, $options, false);
            $artifact['c158_finalization_convert_from_json_duplicate_keys'] = $c158Load['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C158_FINALIZATION_CONVERT_FROM_JSON_STATUS, 'C158 finalization artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $c158Load['hash_match']) {
            return $this->blocked($artifact, self::C158_FINALIZATION_LOCK_MISMATCH_STATUS, 'C158 finalization artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $c158Load['file_sha1_match']) {
            return $this->blocked($artifact, self::C158_FINALIZATION_FILE_SHA1_MISMATCH_STATUS, 'C158 finalization file SHA1 mismatch.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['exists'] || ! is_array($publicationLoad['payload'])) {
            return $this->blocked($artifact, self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $c158Load, $publicationLoad, $options, false);
            $artifact['controlled_publication_convert_from_json_duplicate_keys'] = $publicationLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS, 'Controlled publication artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['hash_match']) {
            return $this->blocked($artifact, self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication hash mismatch.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS, 'Controlled publication file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $c158 = $c158Load['payload'];
        $publication = $publicationLoad['payload'];
        if (($c158['status'] ?? null) !== self::EXPECTED_C158_FINALIZATION_STATUS || ($c158['reason_code'] ?? null) !== self::EXPECTED_C158_FINALIZATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::C158_FINALIZATION_STATUS_MISMATCH_STATUS, 'C158 finalization status/reason is not post-publication observation ready.', $outputPath, $overwrite);
        }
        if (($c158['phase_label'] ?? null) !== self::EXPECTED_C158_FINALIZATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::C158_FINALIZATION_PHASE_LABEL_MISMATCH_STATUS, 'C158 finalization phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->c158NextRecommendationMatches($c158)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::C158_FINALIZATION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C158 finalization next recommendation is not C159.', $outputPath, $overwrite);
        }
        if (! $this->c158FinalizationComplete($c158)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::C158_FINALIZATION_INCOMPLETE_STATUS, 'C158 finalization evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->controlledPublicationIntegrityValid($publication)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH_STATUS, 'Controlled publication artifact does not match expected controlled-only observation shape.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($c158) || ! $this->publicationCandidateScopeMatches($publication)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'Candidate scope does not match locked C159 post-publication observation scope.', $outputPath, $overwrite);
        }
        if (! $this->freePublicationAndPlanGuardClean($c158) || ! $this->publicationGuardClean($publication)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::FREE_PUBLICATION_OR_PLAN_MUTATION_STATUS, 'Free publication, unrestricted publication, or PLAN/CONFIRM mutation already occurred.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C159 requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['post_publication_observation_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::OBSERVATION_CONFIRMATION_MISSING_STATUS, 'C159 requires --post-publication-observation-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_publication_observation_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_OBSERVATION_CONFIRMATION_MISSING_STATUS, 'C159 requires --controlled-publication-observation-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING_STATUS, 'C159 requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $c158Load, $publicationLoad, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C159 requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $c158Load, $publicationLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $c158Load, $publicationLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C159 observes the controlled output publication after C158 GO finalization. Controlled publication evidence is stable enough for observation result review; free publication, unrestricted publication, and PLAN/CONFIRM mutation remain locked.';
        $artifact['diagnostic_conclusion'] = 'C159_POST_PUBLICATION_OBSERVATION_PASSED_CONTROLLED_PUBLICATION_STABLE_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::NEXT_RESULT_REVIEW_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState());

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-51',
            'internal_checkpoint' => 'C159',
            'topic_code' => 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION',
            'topic_stage' => 'POST_PUBLICATION_OBSERVATION_REVIEW',
            'status' => 'C159_POST_PUBLICATION_OBSERVATION_REVIEW_NOT_RUN',
            'reason_code' => 'C159_POST_PUBLICATION_OBSERVATION_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_pass' => false,
            'production_live_runtime_controlled_output_publication_post_publication_observation_review_pass' => false,
            'post_publication_observation_confirmed' => false,
            'controlled_publication_observation_confirmed' => false,
            'free_publication_locked_confirmed' => false,
            'plan_confirm_unchanged_confirmed' => false,
            'weekly_swing_watchlist_controlled_output_publication_observed' => false,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_review' => false,
            'production_live_runtime_controlled_output_publication_post_publication_observation_result_review_allowed_next' => false,
            'controlled_output_publication_post_publication_observation_manifest_created' => false,
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => false,
            'weekly_swing_watchlist_controlled_output_publication_executed' => false,
            'weekly_swing_watchlist_controlled_output_published' => false,
            'weekly_swing_watchlist_controlled_publication_artifact_created' => false,
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
            'c158_finalization_lock_valid' => false,
            'c158_go_decision_finalization_valid' => false,
            'c158_finalization_convert_from_json_pass' => false,
            'controlled_publication_lock_valid' => false,
            'controlled_publication_convert_from_json_pass' => false,
            'controlled_publication_integrity_valid' => false,
            'primary_candidate_observed_in_controlled_publication' => false,
            'backup_candidate_observed_in_controlled_publication' => false,
            'comparator_candidate_observed_in_controlled_publication' => false,
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'a01_remains_comparator_only' => true,
            'c159_post_publication_observation_review_only' => true,
            'c159_controlled_publication_observation_only' => true,
            'c159_not_free_publication' => true,
            'c159_not_unrestricted_publication' => true,
            'c159_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
            'diagnostic_conclusion' => 'C159_POST_PUBLICATION_OBSERVATION_REVIEW_NOT_RUN',
            'next_step_recommendation' => 'C159_POST_PUBLICATION_OBSERVATION_REVIEW_NOT_RUN',
            'message' => '',
        ];
    }

    private function completeSections(array $artifact, array $c158Load, array $publicationLoad, array $options, bool $pass): array
    {
        $c158 = is_array($c158Load['payload']) ? $c158Load['payload'] : [];
        $publication = is_array($publicationLoad['payload']) ? $publicationLoad['payload'] : [];
        $temporaryNegativePaths = (array) ($options['temporary_negative_artifact_paths'] ?? $this->temporaryNegativeArtifactPaths());

        $artifact = array_merge($artifact, $this->topLevelState($c158, $publication, $pass, $options));
        $artifact['c158_finalization_lock_validation_summary'] = $this->c158FinalizationLockValidationSummary($c158Load, $c158);
        $artifact['controlled_publication_lock_validation_summary'] = $this->controlledPublicationLockValidationSummary($publicationLoad, $publication);
        $artifact['post_publication_observation_summary'] = $this->postPublicationObservationSummary($c158, $publication, $pass);
        $artifact['controlled_publication_observation_summary'] = $this->controlledPublicationObservationSummary($publication, $pass);
        $artifact['candidate_publication_observation_scorecard'] = $this->candidatePublicationObservationScorecard($publication, $pass);
        $artifact['publication_plan_confirm_safety_summary'] = $this->publicationPlanConfirmSafetySummary($c158, $publication);
        $artifact['operator_approval_validation_summary'] = $this->operatorApprovalValidationSummary($options);
        $artifact['temporary_negative_artifact_guard_summary'] = $this->temporaryNegativeArtifactGuardSummary($temporaryNegativePaths);
        $artifact['documentation_hygiene_guard_summary'] = $this->documentationHygieneGuardSummary($c158Load, $publicationLoad);
        $artifact['progress_summary'] = $this->progressSummary($pass);
        $artifact['planned_next_summary'] = $this->plannedNextSummary($pass);
        $artifact['diagnostics'] = $this->diagnostics();
        $artifact['failure_attribution_summary'] = $this->failureAttributionSummary([]);
        $artifact['operator_approved'] = (bool) ($options['operator_approved'] ?? false);
        $artifact['approval_reference'] = (string) ($options['approval_reference'] ?? '');

        return $artifact;
    }

    private function topLevelState(array $c158, array $publication, bool $pass, array $options): array
    {
        return [
            'post_publication_observation_confirmed' => (bool) ($options['post_publication_observation_confirmed'] ?? false),
            'controlled_publication_observation_confirmed' => (bool) ($options['controlled_publication_observation_confirmed'] ?? false),
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_observed' => $pass,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => $pass,
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => (bool) ($c158['weekly_swing_watchlist_controlled_output_publication_result_reviewed'] ?? false),
            'weekly_swing_watchlist_controlled_output_publication_executed' => (bool) ($publication['weekly_swing_watchlist_controlled_output_publication_executed'] ?? false),
            'weekly_swing_watchlist_controlled_output_published' => (bool) ($publication['weekly_swing_watchlist_controlled_output_published'] ?? false),
            'weekly_swing_watchlist_controlled_publication_artifact_created' => (bool) ($c158['weekly_swing_watchlist_controlled_publication_artifact_created'] ?? false),
            'weekly_swing_watchlist_official_output_generated' => (bool) ($c158['weekly_swing_watchlist_official_output_generated'] ?? false),
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => (bool) ($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? false),
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => (bool) ($c158['runtime_bridge_active'] ?? false),
            'weekly_swing_watchlist_runtime_active' => (bool) ($c158['weekly_swing_watchlist_runtime_active'] ?? false),
            'weekly_swing_watchlist_live_output_enabled' => (bool) ($c158['weekly_swing_watchlist_live_output_enabled'] ?? false),
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => (bool) ($c158['weekly_swing_watchlist_live_recommendation_generation_allowed'] ?? false),
            'c158_finalization_lock_valid' => (bool) (($c158['artifact_hash'] ?? null) === self::DEFAULT_EXPECTED_C158_FINALIZATION_HASH),
            'c158_go_decision_finalization_valid' => $this->c158FinalizationComplete($c158),
            'controlled_publication_lock_valid' => (bool) (($publication['controlled_publication_hash'] ?? null) === self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH),
            'controlled_publication_integrity_valid' => $this->controlledPublicationIntegrityValid($publication),
            'primary_candidate_observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE),
            'backup_candidate_observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE),
            'comparator_candidate_observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE),
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function passingTopLevelState(): array
    {
        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_pass' => true,
            'production_live_runtime_controlled_output_publication_post_publication_observation_review_pass' => true,
            'post_publication_observation_confirmed' => true,
            'controlled_publication_observation_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'weekly_swing_watchlist_controlled_output_publication_observed' => true,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_review' => true,
            'production_live_runtime_controlled_output_publication_post_publication_observation_result_review_allowed_next' => true,
            'controlled_output_publication_post_publication_observation_manifest_created' => true,
            'c158_finalization_lock_valid' => true,
            'c158_go_decision_finalization_valid' => true,
            'c158_finalization_convert_from_json_pass' => true,
            'controlled_publication_lock_valid' => true,
            'controlled_publication_convert_from_json_pass' => true,
            'controlled_publication_integrity_valid' => true,
            'primary_candidate_observed_in_controlled_publication' => true,
            'backup_candidate_observed_in_controlled_publication' => true,
            'comparator_candidate_observed_in_controlled_publication' => false,
        ];
    }

    private function c158NextRecommendationMatches(array $c158): bool
    {
        foreach ([
            ['next_step_recommendation'],
            ['next_controlled_output_publication_post_publication_observation_decision', 'next_recommendation'],
            ['planned_next_summary', 'planned_next_review'],
        ] as $path) {
            if ($this->valueAt($c158, $path) !== self::EXPECTED_C158_FINALIZATION_NEXT_RECOMMENDATION) {
                return false;
            }
        }

        return true;
    }

    private function c158FinalizationComplete(array $c158): bool
    {
        foreach (self::REQUIRED_C158_TRUE_FIELDS as $field) {
            if (($c158[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_C158_FALSE_FIELDS as $field) {
            if (($c158[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($c158['operator_decision'] ?? null) === 'GO'
            && ($c158['operator_go_decision'] ?? null) === 'GO'
            && $this->valueAt($c158, ['c158_go_decision_finalization_decision', 'go_decision_finalized']) === true
            && $this->valueAt($c158, ['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_manifest', 'go_decision_finalization_used_for_free_publication']) === false
            && $this->valueAt($c158, ['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_manifest', 'go_decision_finalization_used_for_plan_confirm_mutation']) === false
            && $this->valueAt($c158, ['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_checklist', 'artifact_only']) === true
            && $this->valueAt($c158, ['weekly_swing_watchlist_controlled_output_publication_go_decision_finalization_checklist', 'weekly_swing_stock_recommendation_free_published_in_c158_finalization']) === false;
    }

    private function controlledPublicationIntegrityValid(array $publication): bool
    {
        return ($publication['controlled_publication_hash'] ?? null) === self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH
            && ($publication['controlled_publication_type'] ?? null) === 'weekly_swing_watchlist_controlled_output_publication'
            && ($publication['publication_mode'] ?? null) === 'controlled'
            && ($publication['publication_state'] ?? null) === 'controlled_published'
            && ($publication['public_release_state'] ?? null) === 'not_unrestricted'
            && ($publication['weekly_swing_watchlist_controlled_output_publication_executed'] ?? null) === true
            && ($publication['weekly_swing_watchlist_controlled_output_published'] ?? null) === true
            && ($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? null) === true
            && count((array) ($publication['output_rows'] ?? [])) === 2
            && $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE)
            && $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE)
            && ! $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE)
            && $this->valueAt($publication, ['comparator_candidate', 'candidate_code']) === self::COMPARATOR_CANDIDATE
            && $this->valueAt($publication, ['comparator_candidate', 'a01_remains_comparator_only']) === true;
    }

    private function publicationHasCandidate(array $publication, string $candidate): bool
    {
        foreach ((array) ($publication['output_rows'] ?? []) as $row) {
            if (is_array($row) && ($row['candidate_code'] ?? null) === $candidate) {
                return true;
            }
        }

        return false;
    }

    private function candidateScopeMatches(array $c158): bool
    {
        return ($c158['primary_candidate_code'] ?? null) === self::PRIMARY_CANDIDATE
            && ($c158['backup_candidate_code'] ?? null) === self::BACKUP_CANDIDATE
            && ($c158['comparator_candidate_code'] ?? null) === self::COMPARATOR_CANDIDATE
            && ($c158['primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review'] ?? null) === true
            && ($c158['backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review'] ?? null) === true
            && ($c158['comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review'] ?? null) === false
            && ($c158['a01_remains_comparator_only'] ?? null) === true
            && ($c158['a01_promoted'] ?? false) === false
            && ($c158['candidate_promotion_executed'] ?? false) === false
            && ($c158['candidate_rerank_executed'] ?? false) === false;
    }

    private function publicationCandidateScopeMatches(array $publication): bool
    {
        return $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE)
            && $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE)
            && ! $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE)
            && $this->valueAt($publication, ['comparator_candidate', 'candidate_code']) === self::COMPARATOR_CANDIDATE
            && $this->valueAt($publication, ['comparator_candidate', 'a01_remains_comparator_only']) === true;
    }

    private function freePublicationAndPlanGuardClean(array $c158): bool
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
            if (($c158[$field] ?? false) !== false) {
                return false;
            }
        }

        return true;
    }

    private function publicationGuardClean(array $publication): bool
    {
        return ($publication['weekly_swing_watchlist_official_output_published'] ?? false) === false
            && ($publication['weekly_swing_watchlist_publication_allowed'] ?? false) === false
            && ($publication['weekly_swing_watchlist_unrestricted_publication_allowed'] ?? false) === false
            && ($publication['plan_confirm_mutated'] ?? false) === false;
    }

    private function c158FinalizationLockValidationSummary(array $load, array $c158): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C158_GO_DECISION_FINALIZATION',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'expected_status' => self::EXPECTED_C158_FINALIZATION_STATUS,
            'actual_status' => $c158['status'] ?? null,
            'expected_phase_label' => self::EXPECTED_C158_FINALIZATION_PHASE_LABEL,
            'actual_phase_label' => $c158['phase_label'] ?? null,
            'expected_next_recommendation' => self::EXPECTED_C158_FINALIZATION_NEXT_RECOMMENDATION,
            'next_recommendation_match' => $this->c158NextRecommendationMatches($c158),
            'c158_finalization_lock_valid' => $load['hash_match'] && $load['file_sha1_match'],
        ];
    }

    private function controlledPublicationLockValidationSummary(array $load, array $publication): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'CONTROLLED_PUBLICATION',
            'artifact_path' => $load['path'],
            'expected_controlled_publication_hash' => $load['expected_hash'],
            'actual_controlled_publication_hash' => $load['actual_hash'],
            'controlled_publication_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
            'controlled_publication_integrity_valid' => $this->controlledPublicationIntegrityValid($publication),
        ];
    }

    private function postPublicationObservationSummary(array $c158, array $publication, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'observation_review_valid' => $pass,
            'c158_go_decision_finalized' => (bool) ($c158['go_decision_finalized'] ?? false),
            'controlled_publication_observed' => $pass,
            'controlled_publication_hash' => $publication['controlled_publication_hash'] ?? null,
            'controlled_publication_file_sha1' => self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1,
            'controlled_publication_record_count' => count((array) ($publication['output_rows'] ?? [])),
            'official_output_published' => false,
            'free_publication_allowed' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'ready_for_observation_result_review' => $pass,
        ];
    }

    private function controlledPublicationObservationSummary(array $publication, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'controlled_publication_integrity_valid' => $this->controlledPublicationIntegrityValid($publication),
            'observation_stable' => $pass,
            'publication_mode' => $publication['publication_mode'] ?? null,
            'publication_state' => $publication['publication_state'] ?? null,
            'public_release_state' => $publication['public_release_state'] ?? null,
            'primary_candidate_observed' => $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE),
            'backup_candidate_observed' => $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE),
            'comparator_candidate_observed' => $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE),
            'a01_remains_comparator_only' => $this->valueAt($publication, ['comparator_candidate', 'a01_remains_comparator_only']) === true,
        ];
    }

    private function candidatePublicationObservationScorecard(array $publication, bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c159_role' => 'primary_candidate_observed_in_controlled_publication',
                'observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE),
                'ready_for_observation_result_review' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c159_role' => 'backup_candidate_observed_in_controlled_publication',
                'observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE),
                'ready_for_observation_result_review' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c159_role' => 'comparator_only_candidate',
                'observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE),
                'ready_for_observation_result_review' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => true,
            ],
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $c158, array $publication): array
    {
        return [
            'validation_completed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($c158) && $this->publicationGuardClean($publication),
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

    private function operatorApprovalValidationSummary(array $options): array
    {
        $reference = trim((string) ($options['approval_reference'] ?? ''));

        return [
            'operator_approval_required' => true,
            'operator_approved' => (bool) ($options['operator_approved'] ?? false),
            'approval_reference_required' => true,
            'approval_reference' => $reference,
            'approval_reference_present' => $reference !== '',
            'post_publication_observation_confirmation_required' => true,
            'post_publication_observation_confirmed' => (bool) ($options['post_publication_observation_confirmed'] ?? false),
            'controlled_publication_observation_confirmation_required' => true,
            'controlled_publication_observation_confirmed' => (bool) ($options['controlled_publication_observation_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
        ];
    }

    private function documentationHygieneGuardSummary(array $c158Load, array $publicationLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c158_finalization_convert_from_json_pass' => $c158Load['convert_from_json_pass'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
            'c158_top_level_case_insensitive_duplicate_keys' => $c158Load['case_insensitive_duplicate_keys'],
            'controlled_publication_top_level_case_insensitive_duplicate_keys' => $publicationLoad['case_insensitive_duplicate_keys'],
            'c158_finalization_artifact_not_modified' => true,
            'controlled_publication_artifact_not_modified' => true,
            'c159_observation_review_is_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-51_C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW',
            'topic_code' => 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION',
            'topic_stage' => 'POST_PUBLICATION_OBSERVATION_REVIEW',
            'c158_finalization_carried_forward' => true,
            'controlled_publication_observed' => $pass,
            'observation_stable' => $pass,
            'ready_for_observation_result_review' => $pass,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_RESULT_REVIEW_RECOMMENDATION : 'C159_TARGETED_C158_FINALIZATION_OR_CONTROLLED_PUBLICATION_REPAIR',
            'planned_next_scope' => $pass ? 'C159 same-topic controlled output publication post-publication observation result review only; still no free publication or PLAN/CONFIRM mutation' : 'targeted source lock, publication integrity, approval, or confirmation repair before C159 result review',
            'planned_next_required_inputs' => $pass ? [
                'locked C159 observation artifact hash',
                'locked C159 observation file SHA1',
                'C158 finalization lock remains valid',
                'controlled publication artifact lock remains valid',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C159 validates C158 finalization artifact_hash and file SHA1 locks before observing controlled publication.',
            'C159 validates the controlled publication artifact hash, file SHA1, controlled mode, primary/backup records, and A01 comparator-only state.',
            'C159 requires operator approval plus explicit post-publication, controlled-publication, free-publication-lock, and PLAN/CONFIRM unchanged confirmations.',
            'C159 does not free-publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.',
            'C159 may only recommend same-topic C159 post-publication observation result review next.',
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

    private function sourceArtifactLocks(array $c158Load, array $publicationLoad): array
    {
        return [
            'c158_go_decision_finalization' => [
                'artifact_path' => $c158Load['path'],
                'expected_artifact_hash' => $c158Load['expected_hash'],
                'actual_artifact_hash' => $c158Load['actual_hash'],
                'artifact_hash_match' => $c158Load['hash_match'],
                'expected_file_sha1' => $c158Load['expected_file_sha1'],
                'actual_file_sha1' => $c158Load['actual_file_sha1'],
                'file_sha1_match' => $c158Load['file_sha1_match'],
                'convert_from_json_pass' => $c158Load['convert_from_json_pass'],
            ],
            'controlled_publication' => [
                'artifact_path' => $publicationLoad['path'],
                'expected_controlled_publication_hash' => $publicationLoad['expected_hash'],
                'actual_controlled_publication_hash' => $publicationLoad['actual_hash'],
                'controlled_publication_hash_match' => $publicationLoad['hash_match'],
                'expected_file_sha1' => $publicationLoad['expected_file_sha1'],
                'actual_file_sha1' => $publicationLoad['actual_file_sha1'],
                'file_sha1_match' => $publicationLoad['file_sha1_match'],
                'convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
            ],
        ];
    }

    private function topLevelLockAliases(array $c158Load, array $publicationLoad): array
    {
        return [
            'expected_c158_finalization_hash' => $c158Load['expected_hash'],
            'actual_c158_finalization_hash' => $c158Load['actual_hash'],
            'c158_finalization_hash_match' => $c158Load['hash_match'],
            'expected_c158_finalization_file_sha1' => $c158Load['expected_file_sha1'],
            'actual_c158_finalization_file_sha1' => $c158Load['actual_file_sha1'],
            'c158_finalization_file_sha1_match' => $c158Load['file_sha1_match'],
            'c158_finalization_convert_from_json_pass' => $c158Load['convert_from_json_pass'],
            'expected_controlled_publication_hash' => $publicationLoad['expected_hash'],
            'actual_controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_hash_match' => $publicationLoad['hash_match'],
            'expected_controlled_publication_file_sha1' => $publicationLoad['expected_file_sha1'],
            'actual_controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_file_sha1_match' => $publicationLoad['file_sha1_match'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
        ];
    }

    private function loadJsonLock(string $path, string $expectedHash, string $expectedFileSha1, string $hashKey): array
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
                $actualHash = $decoded[$hashKey] ?? null;
            }
            $actualFileSha1 = strtoupper(sha1($raw));
        }

        return [
            'path' => $path,
            'exists' => $exists,
            'payload' => $payload,
            'hash_key' => $hashKey,
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
