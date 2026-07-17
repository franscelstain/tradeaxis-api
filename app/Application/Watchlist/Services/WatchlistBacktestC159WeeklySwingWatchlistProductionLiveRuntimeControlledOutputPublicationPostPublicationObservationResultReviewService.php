<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationResultReviewService
{
    public const RUN_CODE = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW';
    public const PHASE_LABEL = 'PR-52 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW';
    public const ARTIFACT_TYPE = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW';

    public const DEFAULT_C159_OBSERVATION_ARTIFACT = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-review.json';
    public const DEFAULT_EXPECTED_C159_OBSERVATION_HASH = '4f4897570d35a4b572c7158c7e48e860b146aa86';
    public const DEFAULT_EXPECTED_C159_OBSERVATION_FILE_SHA1 = 'BD6A087B386CC4C170A30E8606533453CC20FA43';
    public const DEFAULT_CONTROLLED_PUBLICATION_ARTIFACT = 'storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH = 'df064c7290ff4c3bfd0c7a8412d39299049c01d5';
    public const DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1 = 'D87AB8CD1564BE8B266B8A68011470272D49EE60';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-result-review.json';

    private const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    private const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    private const COMPARATOR_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';

    private const EXPECTED_C159_OBSERVATION_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PASSED_CONTROLLED_PUBLICATION_OBSERVED_READY_FOR_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP';
    private const EXPECTED_C159_OBSERVATION_PHASE_LABEL = 'PR-51 / C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW';
    private const EXPECTED_C159_OBSERVATION_NEXT_RECOMMENDATION = self::RUN_CODE;
    private const NEXT_OPERATOR_RECOMMENDATION = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW';

    private const PASS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP';
    private const APPROVAL_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING';
    private const RESULT_REVIEW_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING';
    private const CONTROLLED_PUBLICATION_RESULT_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_OBSERVATION_RESULT_CONFIRMATION_MISSING';
    private const FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING';
    private const PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING';
    private const TEMPORARY_NEGATIVE_REMAINS_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS';
    private const C159_OBSERVATION_LOCK_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_ARTIFACT_LOCK_MISMATCH';
    private const C159_OBSERVATION_FILE_SHA1_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_FILE_SHA1_LOCK_MISMATCH';
    private const C159_OBSERVATION_CONVERT_FROM_JSON_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const C159_OBSERVATION_STATUS_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_STATUS_MISMATCH';
    private const C159_OBSERVATION_PHASE_LABEL_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_PHASE_LABEL_MISMATCH';
    private const C159_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH';
    private const C159_OBSERVATION_INCOMPLETE_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_C159_OBSERVATION_INCOMPLETE';
    private const CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FILE_SHA1_LOCK_MISMATCH';
    private const CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_COMPATIBILITY_VIOLATION';
    private const CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH';
    private const CANDIDATE_SCOPE_MISMATCH_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_CANDIDATE_SCOPE_MISMATCH';
    private const FREE_PUBLICATION_OR_PLAN_MUTATION_STATUS = 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REJECTED_FREE_PUBLICATION_OR_PLAN_CONFIRM_ALREADY_OCCURRED';

    private const REQUIRED_OBSERVATION_TRUE_FIELDS = [
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_executed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed',
        'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_pass',
        'production_live_runtime_controlled_output_publication_post_publication_observation_review_pass',
        'post_publication_observation_confirmed',
        'controlled_publication_observation_confirmed',
        'free_publication_locked_confirmed',
        'plan_confirm_unchanged_confirmed',
        'weekly_swing_watchlist_controlled_output_publication_observed',
        'weekly_swing_watchlist_controlled_output_publication_observation_stable',
        'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_review',
        'production_live_runtime_controlled_output_publication_post_publication_observation_result_review_allowed_next',
        'controlled_output_publication_post_publication_observation_manifest_created',
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
        'c158_finalization_lock_valid',
        'c158_go_decision_finalization_valid',
        'c158_finalization_convert_from_json_pass',
        'controlled_publication_lock_valid',
        'controlled_publication_integrity_valid',
        'primary_candidate_observed_in_controlled_publication',
        'backup_candidate_observed_in_controlled_publication',
        'a01_remains_comparator_only',
        'c159_post_publication_observation_review_only',
        'c159_controlled_publication_observation_only',
        'c159_not_free_publication',
        'c159_not_unrestricted_publication',
        'c159_not_plan_confirm_mutation',
        'temporary_negative_artifact_cleanup_confirmed',
    ];

    private const REQUIRED_OBSERVATION_FALSE_FIELDS = [
        'comparator_candidate_observed_in_controlled_publication',
        'temporary_negative_artifacts_remaining',
    ];

    private const TEMPORARY_NEGATIVE_PATTERNS = [
        'storage/app/watchlist/backtest/c159-*post-publication-observation-result-review*-test.json',
        'storage/app/watchlist/backtest/c159-*result-review*-test.json',
        'storage/app/watchlist/backtest/c159-*negative-*-test.json',
        'storage/app/watchlist/backtest/c159-*missing-*-test.json',
        'storage/app/watchlist/backtest/c159-*mismatch-*-test.json',
        'storage/app/watchlist/backtest/c159-*invalid-*-test.json',
    ];

    public function execute(
        string $c159ObservationArtifact = self::DEFAULT_C159_OBSERVATION_ARTIFACT,
        string $expectedC159ObservationHash = self::DEFAULT_EXPECTED_C159_OBSERVATION_HASH,
        string $expectedC159ObservationFileSha1 = self::DEFAULT_EXPECTED_C159_OBSERVATION_FILE_SHA1,
        string $controlledPublicationArtifact = self::DEFAULT_CONTROLLED_PUBLICATION_ARTIFACT,
        string $expectedControlledPublicationHash = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_HASH,
        string $expectedControlledPublicationFileSha1 = self::DEFAULT_EXPECTED_CONTROLLED_PUBLICATION_FILE_SHA1,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $artifact = $this->baseArtifact((string) ($options['created_at'] ?? gmdate('c')));
        $observationLoad = $this->loadJsonLock($c159ObservationArtifact, $expectedC159ObservationHash, $expectedC159ObservationFileSha1, 'artifact_hash');
        $publicationLoad = $this->loadJsonLock($controlledPublicationArtifact, $expectedControlledPublicationHash, $expectedControlledPublicationFileSha1, 'controlled_publication_hash');
        $artifact['source_artifact_locks'] = $this->sourceArtifactLocks($observationLoad, $publicationLoad);
        $artifact = array_merge($artifact, $this->topLevelLockAliases($observationLoad, $publicationLoad));

        if (! $observationLoad['exists'] || ! is_array($observationLoad['payload'])) {
            return $this->blocked($artifact, self::C159_OBSERVATION_LOCK_MISMATCH_STATUS, 'C159 observation artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $observationLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false);
            $artifact['c159_observation_convert_from_json_duplicate_keys'] = $observationLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::C159_OBSERVATION_CONVERT_FROM_JSON_STATUS, 'C159 observation artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $observationLoad['hash_match']) {
            return $this->blocked($artifact, self::C159_OBSERVATION_LOCK_MISMATCH_STATUS, 'C159 observation artifact_hash mismatch.', $outputPath, $overwrite);
        }
        if (! $observationLoad['file_sha1_match']) {
            return $this->blocked($artifact, self::C159_OBSERVATION_FILE_SHA1_MISMATCH_STATUS, 'C159 observation file SHA1 mismatch.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['exists'] || ! is_array($publicationLoad['payload'])) {
            return $this->blocked($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication artifact missing or unreadable.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['convert_from_json_pass']) {
            $artifact = $this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false);
            $artifact['controlled_publication_convert_from_json_duplicate_keys'] = $publicationLoad['case_insensitive_duplicate_keys'];

            return $this->rejected($artifact, self::CONTROLLED_PUBLICATION_CONVERT_FROM_JSON_STATUS, 'Controlled publication artifact is not PowerShell ConvertFrom-Json compatible.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['hash_match']) {
            return $this->blocked($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_LOCK_MISMATCH_STATUS, 'Controlled publication hash mismatch.', $outputPath, $overwrite);
        }
        if (! $publicationLoad['file_sha1_match']) {
            return $this->blocked($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_FILE_SHA1_MISMATCH_STATUS, 'Controlled publication file SHA1 mismatch.', $outputPath, $overwrite);
        }

        $observation = $observationLoad['payload'];
        $publication = $publicationLoad['payload'];
        if (($observation['status'] ?? null) !== self::EXPECTED_C159_OBSERVATION_STATUS || ($observation['reason_code'] ?? null) !== self::EXPECTED_C159_OBSERVATION_STATUS) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::C159_OBSERVATION_STATUS_MISMATCH_STATUS, 'C159 observation status/reason is not result-review ready.', $outputPath, $overwrite);
        }
        if (($observation['phase_label'] ?? null) !== self::EXPECTED_C159_OBSERVATION_PHASE_LABEL) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::C159_OBSERVATION_PHASE_LABEL_MISMATCH_STATUS, 'C159 observation phase label mismatch.', $outputPath, $overwrite);
        }
        if (! $this->observationNextRecommendationMatches($observation)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::C159_OBSERVATION_NEXT_RECOMMENDATION_MISMATCH_STATUS, 'C159 observation next recommendation is not C159 observation result review.', $outputPath, $overwrite);
        }
        if (! $this->observationComplete($observation)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::C159_OBSERVATION_INCOMPLETE_STATUS, 'C159 observation evidence is incomplete.', $outputPath, $overwrite);
        }
        if (! $this->controlledPublicationIntegrityValid($publication)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_INTEGRITY_MISMATCH_STATUS, 'Controlled publication artifact does not match expected controlled-only result review shape.', $outputPath, $overwrite);
        }
        if (! $this->candidateScopeMatches($observation, $publication)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::CANDIDATE_SCOPE_MISMATCH_STATUS, 'Candidate scope does not match locked C159 post-publication observation result review scope.', $outputPath, $overwrite);
        }
        if (! $this->freePublicationAndPlanGuardClean($observation) || ! $this->freePublicationAndPlanGuardClean($publication)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::FREE_PUBLICATION_OR_PLAN_MUTATION_STATUS, 'Free publication, unrestricted publication, or PLAN/CONFIRM mutation already occurred.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['operator_approved'] ?? false) || trim((string) ($options['approval_reference'] ?? '')) === '') {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::APPROVAL_MISSING_STATUS, 'C159 result review requires --operator-approved and non-empty --approval-reference.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['result_review_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::RESULT_REVIEW_CONFIRMATION_MISSING_STATUS, 'C159 result review requires --result-review-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['controlled_publication_observation_result_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::CONTROLLED_PUBLICATION_RESULT_CONFIRMATION_MISSING_STATUS, 'C159 result review requires --controlled-publication-observation-result-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['free_publication_locked_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING_STATUS, 'C159 result review requires --free-publication-locked-confirmed.', $outputPath, $overwrite);
        }
        if (! (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false)) {
            return $this->rejected($this->completeSections($artifact, $observationLoad, $publicationLoad, $options, false), self::PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING_STATUS, 'C159 result review requires --plan-confirm-unchanged-confirmed.', $outputPath, $overwrite);
        }

        $temporaryNegativePaths = $this->temporaryNegativeArtifactPaths();
        if ($temporaryNegativePaths !== []) {
            $artifact = $this->completeSections($artifact, $observationLoad, $publicationLoad, array_merge($options, ['temporary_negative_artifact_paths' => $temporaryNegativePaths]), false);
            $artifact['temporary_negative_artifacts_remaining'] = true;
            $artifact['temporary_negative_artifact_cleanup_confirmed'] = false;
            $artifact['temporary_negative_artifact_paths'] = $temporaryNegativePaths;

            return $this->rejected($artifact, self::TEMPORARY_NEGATIVE_REMAINS_STATUS, 'Temporary negative test artifact remains in storage/app/watchlist/backtest.', $outputPath, $overwrite);
        }

        $artifact = $this->completeSections($artifact, $observationLoad, $publicationLoad, $options, true);
        $artifact['status'] = self::PASS_STATUS;
        $artifact['reason_code'] = self::PASS_STATUS;
        $artifact['message'] = 'C159 reviews the post-publication observation result. Controlled publication remains stable for primary and backup; free publication remains locked, unrestricted publication remains disabled, and PLAN/CONFIRM is unchanged.';
        $artifact['diagnostic_conclusion'] = 'C159_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_PASSED_CONTROLLED_PUBLICATION_STABLE_READY_FOR_OPERATOR_GO_NO_GO_NOT_FREE_PUBLISHED_PLAN_CONFIRM_UNCHANGED';
        $artifact['next_step_recommendation'] = self::NEXT_OPERATOR_RECOMMENDATION;
        $artifact = array_merge($artifact, $this->passingTopLevelState($publicationLoad));

        return $this->writeAndReturn($artifact, $outputPath, $overwrite);
    }

    private function baseArtifact(string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'phase_checkpoint' => 'PR-52',
            'internal_checkpoint' => 'C159',
            'topic_code' => 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION',
            'topic_stage' => 'POST_PUBLICATION_OBSERVATION_RESULT_REVIEW',
            'status' => 'C159_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_NOT_RUN',
            'reason_code' => 'C159_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_NOT_RUN',
            'artifact_type' => self::ARTIFACT_TYPE,
            'artifact_hash' => null,
            'artifact_hash_algorithm' => 'stable_sha1_json_payload',
            'created_at' => $createdAt,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_executed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_allowed' => false,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass' => false,
            'production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass' => false,
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed' => false,
            'controlled_output_publication_post_publication_observation_result_review_manifest_created' => false,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_review' => false,
            'production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_allowed_next' => false,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
        ];
    }

    private function completeSections(array $artifact, array $observationLoad, array $publicationLoad, array $options, bool $pass): array
    {
        $observation = is_array($observationLoad['payload']) ? $observationLoad['payload'] : [];
        $publication = is_array($publicationLoad['payload']) ? $publicationLoad['payload'] : [];

        return array_merge($artifact, [
            'c159_observation_lock_validation_summary' => $this->observationLockValidationSummary($observationLoad),
            'controlled_publication_lock_validation_summary' => $this->controlledPublicationLockValidationSummary($publicationLoad),
            'c159_observation_carry_forward_summary' => $this->observationCarryForwardSummary($observation, $pass),
            'post_publication_observation_result_review_summary' => $this->observationResultReviewSummary($observation, $publication, $pass),
            'controlled_publication_observation_result_summary' => $this->controlledPublicationResultSummary($publication, $publicationLoad, $pass),
            'publication_plan_confirm_safety_summary' => $this->publicationPlanConfirmSafetySummary($observation, $publication),
            'candidate_observation_result_scorecard' => $this->candidateObservationResultScorecard($publication, $pass),
            'operator_approval_validation_summary' => $this->operatorApprovalValidationSummary($options),
            'result_review_confirmation_summary' => $this->resultReviewConfirmationSummary($options, $pass),
            'temporary_negative_artifact_guard_summary' => $this->temporaryNegativeArtifactGuardSummary((array) ($options['temporary_negative_artifact_paths'] ?? [])),
            'documentation_hygiene_guard_summary' => $this->documentationHygieneGuardSummary($observationLoad, $publicationLoad),
            'progress_summary' => $this->progressSummary($pass),
            'planned_next_summary' => $this->plannedNextSummary($pass),
            'diagnostics' => $this->diagnostics(),
        ]);
    }

    private function passingTopLevelState(array $publicationLoad): array
    {
        $publication = is_array($publicationLoad['payload']) ? $publicationLoad['payload'] : [];

        return [
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_executed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_allowed' => true,
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass' => true,
            'production_live_runtime_controlled_output_publication_post_publication_observation_result_review_pass' => true,
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed' => true,
            'controlled_output_publication_post_publication_observation_result_review_manifest_created' => true,
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_operator_go_no_go_review' => true,
            'production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_allowed_next' => true,
            'weekly_swing_watchlist_controlled_output_publication_observed' => true,
            'weekly_swing_watchlist_controlled_output_publication_observation_stable' => true,
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed' => true,
            'weekly_swing_watchlist_controlled_output_publication_executed' => true,
            'weekly_swing_watchlist_controlled_output_published' => true,
            'weekly_swing_watchlist_controlled_publication_artifact_created' => true,
            'weekly_swing_watchlist_official_output_generated' => true,
            'weekly_swing_watchlist_official_output_published' => false,
            'weekly_swing_watchlist_publication_allowed' => false,
            'weekly_swing_watchlist_controlled_publication_allowed' => true,
            'weekly_swing_watchlist_unrestricted_publication_allowed' => false,
            'plan_confirm_mutation_allowed' => false,
            'plan_confirm_mutated' => false,
            'plan_confirm_runtime_reads_activated_catalog' => false,
            'live_plan_confirm_rollout_allowed' => false,
            'live_plan_confirm_rollout_executed' => false,
            'runtime_bridge_active' => true,
            'weekly_swing_watchlist_runtime_active' => true,
            'weekly_swing_watchlist_live_output_enabled' => true,
            'weekly_swing_watchlist_live_recommendation_generation_allowed' => true,
            'c159_observation_lock_valid' => true,
            'c159_post_publication_observation_review_valid' => true,
            'c159_observation_convert_from_json_pass' => true,
            'controlled_publication_lock_valid' => true,
            'controlled_publication_convert_from_json_pass' => true,
            'controlled_publication_integrity_valid' => true,
            'controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_record_count' => count((array) ($publication['output_rows'] ?? [])),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'backup_candidate_code' => self::BACKUP_CANDIDATE,
            'comparator_candidate_code' => self::COMPARATOR_CANDIDATE,
            'primary_candidate_observation_result_reviewed' => true,
            'backup_candidate_observation_result_reviewed' => true,
            'comparator_candidate_observation_result_reviewed' => false,
            'primary_candidate_observed_in_controlled_publication' => true,
            'backup_candidate_observed_in_controlled_publication' => true,
            'comparator_candidate_observed_in_controlled_publication' => false,
            'a01_remains_comparator_only' => true,
            'post_publication_observation_result_review_confirmed' => true,
            'controlled_publication_observation_result_confirmed' => true,
            'free_publication_locked_confirmed' => true,
            'plan_confirm_unchanged_confirmed' => true,
            'c159_post_publication_observation_result_review_only' => true,
            'c159_controlled_publication_observation_result_only' => true,
            'c159_not_free_publication' => true,
            'c159_not_unrestricted_publication' => true,
            'c159_not_plan_confirm_mutation' => true,
            'temporary_negative_artifacts_remaining' => false,
            'temporary_negative_artifact_cleanup_confirmed' => true,
            'temporary_negative_artifact_paths' => [],
        ];
    }

    private function observationComplete(array $observation): bool
    {
        foreach (self::REQUIRED_OBSERVATION_TRUE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== true) {
                return false;
            }
        }
        foreach (self::REQUIRED_OBSERVATION_FALSE_FIELDS as $field) {
            if (($observation[$field] ?? null) !== false) {
                return false;
            }
        }

        return ($observation['topic_code'] ?? null) === 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION'
            && ($observation['topic_stage'] ?? null) === 'POST_PUBLICATION_OBSERVATION_REVIEW';
    }

    private function controlledPublicationIntegrityValid(array $publication): bool
    {
        return ($publication['publication_mode'] ?? null) === 'controlled'
            && ($publication['publication_state'] ?? null) === 'controlled_published'
            && ($publication['public_release_state'] ?? null) === 'not_unrestricted'
            && ($publication['weekly_swing_watchlist_controlled_output_publication_executed'] ?? null) === true
            && ($publication['weekly_swing_watchlist_controlled_output_published'] ?? null) === true
            && ($publication['weekly_swing_watchlist_controlled_publication_allowed'] ?? null) === true
            && count((array) ($publication['output_rows'] ?? [])) === 2
            && $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE)
            && $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE)
            && ! $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE)
            && $this->valueAt($publication, ['comparator_candidate', 'a01_remains_comparator_only']) === true;
    }

    private function candidateScopeMatches(array $observation, array $publication): bool
    {
        return ($observation['primary_candidate_observed_in_controlled_publication'] ?? null) === true
            && ($observation['backup_candidate_observed_in_controlled_publication'] ?? null) === true
            && ($observation['comparator_candidate_observed_in_controlled_publication'] ?? null) === false
            && ($observation['a01_remains_comparator_only'] ?? null) === true
            && $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE)
            && $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE)
            && ! $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE);
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

    private function freePublicationAndPlanGuardClean(array $source): bool
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
            'official_output_published',
            'free_publication_allowed',
            'unrestricted_publication_allowed',
            'publication_allowed',
            'unrestricted_publication',
        ] as $field) {
            if (($source[$field] ?? null) === true) {
                return false;
            }
        }

        return true;
    }

    private function observationNextRecommendationMatches(array $observation): bool
    {
        return ($observation['next_step_recommendation'] ?? null) === self::EXPECTED_C159_OBSERVATION_NEXT_RECOMMENDATION
            && $this->valueAt($observation, ['planned_next_summary', 'planned_next_review']) === self::EXPECTED_C159_OBSERVATION_NEXT_RECOMMENDATION;
    }

    private function observationLockValidationSummary(array $load): array
    {
        return [
            'validation_completed' => true,
            'source_lock' => 'C159_POST_PUBLICATION_OBSERVATION_REVIEW',
            'artifact_path' => $load['path'],
            'expected_artifact_hash' => $load['expected_hash'],
            'actual_artifact_hash' => $load['actual_hash'],
            'artifact_hash_match' => $load['hash_match'],
            'expected_file_sha1' => $load['expected_file_sha1'],
            'actual_file_sha1' => $load['actual_file_sha1'],
            'file_sha1_match' => $load['file_sha1_match'],
            'convert_from_json_pass' => $load['convert_from_json_pass'],
        ];
    }

    private function controlledPublicationLockValidationSummary(array $load): array
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
            'controlled_publication_integrity_valid' => is_array($load['payload']) && $this->controlledPublicationIntegrityValid($load['payload']),
        ];
    }

    private function observationCarryForwardSummary(array $observation, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'c159_observation_valid' => $this->observationComplete($observation),
            'topic_code' => (string) ($observation['topic_code'] ?? ''),
            'topic_stage' => (string) ($observation['topic_stage'] ?? ''),
            'controlled_publication_observed' => (bool) ($observation['weekly_swing_watchlist_controlled_output_publication_observed'] ?? false),
            'controlled_publication_observation_stable' => (bool) ($observation['weekly_swing_watchlist_controlled_output_publication_observation_stable'] ?? false),
            'official_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
            'c159_result_review_pass' => $pass,
        ];
    }

    private function observationResultReviewSummary(array $observation, array $publication, bool $pass): array
    {
        return [
            'result_review_executed' => $pass,
            'observation_result_review_valid' => $pass,
            'c159_observation_artifact_hash' => $observation['artifact_hash'] ?? null,
            'controlled_publication_hash' => $publication['controlled_publication_hash'] ?? null,
            'controlled_publication_record_count' => count((array) ($publication['output_rows'] ?? [])),
            'controlled_publication_stable_for_operator_go_no_go_review' => $pass,
            'free_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function controlledPublicationResultSummary(array $publication, array $publicationLoad, bool $pass): array
    {
        return [
            'validation_completed' => true,
            'controlled_publication_integrity_valid' => $this->controlledPublicationIntegrityValid($publication),
            'controlled_publication_path' => $publicationLoad['path'],
            'controlled_publication_hash' => $publicationLoad['actual_hash'],
            'controlled_publication_file_sha1' => $publicationLoad['actual_file_sha1'],
            'controlled_publication_record_count' => count((array) ($publication['output_rows'] ?? [])),
            'publication_mode' => $publication['publication_mode'] ?? null,
            'publication_state' => $publication['publication_state'] ?? null,
            'public_release_state' => $publication['public_release_state'] ?? null,
            'primary_candidate_observed' => $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE),
            'backup_candidate_observed' => $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE),
            'comparator_candidate_observed' => $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE),
            'result_review_stable' => $pass,
        ];
    }

    private function publicationPlanConfirmSafetySummary(array $observation, array $publication): array
    {
        return [
            'validation_completed' => true,
            'free_publication_and_plan_guard_clean' => $this->freePublicationAndPlanGuardClean($observation) && $this->freePublicationAndPlanGuardClean($publication),
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

    private function candidateObservationResultScorecard(array $publication, bool $pass): array
    {
        return [
            [
                'candidate_code' => self::PRIMARY_CANDIDATE,
                'c159_role' => 'primary_candidate_observation_result_reviewed',
                'observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::PRIMARY_CANDIDATE),
                'observation_result_reviewed' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::BACKUP_CANDIDATE,
                'c159_role' => 'backup_candidate_observation_result_reviewed',
                'observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::BACKUP_CANDIDATE),
                'observation_result_reviewed' => $pass,
                'free_published' => false,
            ],
            [
                'candidate_code' => self::COMPARATOR_CANDIDATE,
                'c159_role' => 'comparator_only_candidate',
                'observed_in_controlled_publication' => $this->publicationHasCandidate($publication, self::COMPARATOR_CANDIDATE),
                'observation_result_reviewed' => false,
                'free_published' => false,
                'a01_remains_comparator_only' => true,
            ],
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
        ];
    }

    private function resultReviewConfirmationSummary(array $options, bool $pass): array
    {
        return [
            'result_review_confirmation_required' => true,
            'result_review_confirmed' => (bool) ($options['result_review_confirmed'] ?? false),
            'controlled_publication_observation_result_confirmation_required' => true,
            'controlled_publication_observation_result_confirmed' => (bool) ($options['controlled_publication_observation_result_confirmed'] ?? false),
            'free_publication_lock_confirmation_required' => true,
            'free_publication_locked_confirmed' => (bool) ($options['free_publication_locked_confirmed'] ?? false),
            'plan_confirm_unchanged_confirmation_required' => true,
            'plan_confirm_unchanged_confirmed' => (bool) ($options['plan_confirm_unchanged_confirmed'] ?? false),
            'result_review_confirmation_valid' => $pass,
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

    private function documentationHygieneGuardSummary(array $observationLoad, array $publicationLoad): array
    {
        return [
            'documentation_hygiene_guard_reviewed' => true,
            'c159_observation_convert_from_json_pass' => $observationLoad['convert_from_json_pass'],
            'controlled_publication_convert_from_json_pass' => $publicationLoad['convert_from_json_pass'],
            'c159_observation_top_level_case_insensitive_duplicate_keys' => $observationLoad['case_insensitive_duplicate_keys'],
            'controlled_publication_top_level_case_insensitive_duplicate_keys' => $publicationLoad['case_insensitive_duplicate_keys'],
            'c159_observation_artifact_not_modified' => true,
            'controlled_publication_artifact_not_modified' => true,
            'c159_observation_result_review_is_not_free_publication' => true,
            'phase_label' => self::PHASE_LABEL,
        ];
    }

    private function progressSummary(bool $pass): array
    {
        return [
            'progress_marker' => 'PR-52_C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW',
            'topic_code' => 'C159_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION',
            'topic_stage' => 'POST_PUBLICATION_OBSERVATION_RESULT_REVIEW',
            'c159_observation_carried_forward' => true,
            'observation_result_review_pass' => $pass,
            'controlled_publication_stable_for_operator_go_no_go_review' => $pass,
            'same_topic_number_for_next_stage' => true,
            'official_weekly_swing_output_published' => false,
            'unrestricted_publication_allowed' => false,
            'plan_confirm_mutated' => false,
        ];
    }

    private function plannedNextSummary(bool $pass): array
    {
        return [
            'planned_next_review' => $pass ? self::NEXT_OPERATOR_RECOMMENDATION : 'C159_TARGETED_POST_PUBLICATION_OBSERVATION_RESULT_REVIEW_REPAIR',
            'planned_next_scope' => $pass ? 'same-topic C159 controlled output publication post-publication observation operator go/no-go review only; still no free publication or PLAN/CONFIRM mutation' : 'targeted observation lock, controlled publication lock, confirmation, guard, or cleanup repair',
            'same_topic_number_for_next_stage' => true,
            'planned_next_required_inputs' => $pass ? [
                'locked C159 observation result review artifact hash',
                'locked C159 observation result review file SHA1',
                'controlled publication artifact remains locked',
                'free publication still disabled',
                'PLAN/CONFIRM unchanged',
            ] : [],
        ];
    }

    private function diagnostics(): array
    {
        return [
            'C159 result review validates the C159 observation artifact hash and file SHA1 before reviewing results.',
            'C159 result review validates the controlled publication artifact hash and file SHA1 before recommending operator go/no-go review.',
            'C159 result review reviews controlled post-publication observation evidence only.',
            'C159 result review does not free-publish output, allow unrestricted publication, or mutate PLAN/CONFIRM.',
            'C159 result review keeps E02 primary, B01 backup, and A01 comparator-only.',
            'C159 result review may only recommend same-topic post-publication observation operator go/no-go review next.',
        ];
    }

    private function sourceArtifactLocks(array $observationLoad, array $publicationLoad): array
    {
        return [
            'c159_post_publication_observation_review' => [
                'artifact_path' => $observationLoad['path'],
                'expected_artifact_hash' => $observationLoad['expected_hash'],
                'actual_artifact_hash' => $observationLoad['actual_hash'],
                'artifact_hash_match' => $observationLoad['hash_match'],
                'expected_file_sha1' => $observationLoad['expected_file_sha1'],
                'actual_file_sha1' => $observationLoad['actual_file_sha1'],
                'file_sha1_match' => $observationLoad['file_sha1_match'],
                'convert_from_json_pass' => $observationLoad['convert_from_json_pass'],
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

    private function topLevelLockAliases(array $observationLoad, array $publicationLoad): array
    {
        return [
            'expected_c159_observation_hash' => $observationLoad['expected_hash'],
            'actual_c159_observation_hash' => $observationLoad['actual_hash'],
            'c159_observation_hash_match' => $observationLoad['hash_match'],
            'expected_c159_observation_file_sha1' => $observationLoad['expected_file_sha1'],
            'actual_c159_observation_file_sha1' => $observationLoad['actual_file_sha1'],
            'c159_observation_file_sha1_match' => $observationLoad['file_sha1_match'],
            'c159_observation_convert_from_json_pass' => $observationLoad['convert_from_json_pass'],
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
