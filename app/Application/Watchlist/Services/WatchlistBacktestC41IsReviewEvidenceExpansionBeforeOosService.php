<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService
{
    public const RUN_CODE = 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
    public const ARTIFACT_TYPE = 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
    public const DEFAULT_C40_ARTIFACT = 'storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json';
    public const DEFAULT_EXPECTED_C40_HASH = '0b40ee2464ed820d47ad0b83acbacd78b440d5bd';
    public const DEFAULT_C40_FILE_SHA1 = '306E01AD1274944991F1AFE6CFEBBDB3C0E06BFC';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C40_STATUS = 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED';
    public const EXPECTED_C40_CONCLUSION = 'C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS';
    public const EXPECTED_C40_NEXT_STEP = 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
    public const TARGET_CANDIDATE_CODE = 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA';

    public function execute(
        string $c40Artifact = self::DEFAULT_C40_ARTIFACT,
        string $expectedC40Hash = self::DEFAULT_EXPECTED_C40_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c40Artifact = trim($c40Artifact) !== '' ? trim($c40Artifact) : self::DEFAULT_C40_ARTIFACT;
        $expectedC40Hash = trim($expectedC40Hash) !== '' ? trim($expectedC40Hash) : self::DEFAULT_EXPECTED_C40_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact(
            $c40Artifact,
            $expectedC40Hash,
            null,
            null,
            null,
            null,
            $from,
            $to,
            $createdAt
        );

        if (! is_file($c40Artifact)) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_MISSING_C40_ARTIFACT',
                'WS_BT_C41_C40_ARTIFACT_MISSING',
                'C41 requires the locked C40 warning artifact, but the file is missing.',
                $outputPath,
                ['input_c40_artifact' => $c40Artifact]
            );
        }

        $c40 = json_decode((string) file_get_contents($c40Artifact), true);
        if (! is_array($c40)) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_MISSING_C40_ARTIFACT',
                'WS_BT_C41_C40_ARTIFACT_UNREADABLE',
                'C40 artifact is not readable JSON.',
                $outputPath,
                ['input_c40_artifact' => $c40Artifact]
            );
        }

        $actualC40Hash = $this->stableHash($c40);
        $artifact = $this->baseArtifact(
            $c40Artifact,
            $expectedC40Hash,
            $actualC40Hash,
            $c40['status'] ?? null,
            $c40['diagnostic_conclusion'] ?? null,
            $c40['next_step_recommendation'] ?? null,
            $from,
            $to,
            $createdAt
        );
        $artifact['source_c40_summary'] = $this->sourceC40Summary($c40);

        if ($actualC40Hash !== $expectedC40Hash) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_HASH_MISMATCH',
                'WS_BT_C41_C40_ARTIFACT_HASH_MISMATCH',
                'C40 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c40_artifact_hash_field' => $c40['artifact_hash'] ?? null]
            );
        }

        if (($c40['status'] ?? null) !== self::EXPECTED_C40_STATUS) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_UNEXPECTED_C40_STATUS',
                'WS_BT_C41_UNEXPECTED_C40_STATUS',
                'C41 requires a completed C40 IS validation and anti-overfit artifact.',
                $outputPath,
                ['expected_c40_status' => self::EXPECTED_C40_STATUS]
            );
        }

        if (($c40['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C40_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_UNEXPECTED_C40_CONCLUSION',
                'WS_BT_C41_UNEXPECTED_C40_CONCLUSION',
                'C41 review is only valid for the C40 warning path before OOS.',
                $outputPath,
                ['expected_c40_diagnostic_conclusion' => self::EXPECTED_C40_CONCLUSION]
            );
        }

        if (($c40['next_step_recommendation'] ?? null) !== self::EXPECTED_C40_NEXT_STEP) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_UNEXPECTED_C40_NEXT_STEP',
                'WS_BT_C41_UNEXPECTED_C40_NEXT_STEP',
                'C41 requires C40 next step to be IS review or evidence expansion before OOS.',
                $outputPath,
                ['expected_c40_next_step' => self::EXPECTED_C40_NEXT_STEP]
            );
        }

        if (! $this->strictFalse($c40['production_ready'] ?? false)) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_PRODUCTION_READY_NOT_FALSE',
                'WS_BT_C41_C40_PRODUCTION_READY_NOT_FALSE',
                'C41 requires C40 production_ready=false.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (! $this->strictFalse($c40['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_OOS_TUNING_FLAG_NOT_FALSE',
                'WS_BT_C41_C40_OOS_TUNING_FLAG_NOT_FALSE',
                'C41 requires C40 oos_data_used_for_tuning=false.',
                $outputPath,
                ['expected_oos_data_used_for_tuning' => false]
            );
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_INVALID_IS_PERIOD',
                'WS_BT_C41_INVALID_IS_PERIOD',
                'C41 requires a valid IS period where from <= to.',
                $outputPath,
                ['from' => $from, 'to' => $to]
            );
        }

        if ($this->touchesOos($from, $to) || $this->touchesOos((string) ($c40['is_period']['from'] ?? $from), (string) ($c40['is_period']['to'] ?? $to))) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'WS_BT_C41_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'C41 is IS-only review and rejects periods that touch the reserved OOS window.',
                $outputPath,
                [
                    'from' => $from,
                    'to' => $to,
                    'c40_from' => $c40['is_period']['from'] ?? null,
                    'c40_to' => $c40['is_period']['to'] ?? null,
                    'oos_reserved_from' => self::OOS_RESERVED_FROM,
                ]
            );
        }

        $validationSummary = is_array($c40['validation_summary'] ?? null) ? $c40['validation_summary'] : [];
        if (($validationSummary['overall_anti_overfit_result'] ?? null) !== 'WARNING') {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_NOT_WARNING_PATH',
                'WS_BT_C41_C40_NOT_WARNING_PATH',
                'C41 review/evidence expansion requires the C40 overall anti-overfit result to be WARNING.',
                $outputPath,
                ['overall_anti_overfit_result' => $validationSummary['overall_anti_overfit_result'] ?? null]
            );
        }

        if ((int) ($validationSummary['failed_layers'] ?? 0) !== 0 || (int) ($validationSummary['not_evaluable_layers'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_LAYER_FAILURE_OR_NOT_EVALUABLE',
                'WS_BT_C41_C40_LAYER_FAILURE_OR_NOT_EVALUABLE',
                'C41 warning review requires C40 to have zero failed and zero not-evaluable validation layers.',
                $outputPath,
                [
                    'failed_layers' => $validationSummary['failed_layers'] ?? null,
                    'not_evaluable_layers' => $validationSummary['not_evaluable_layers'] ?? null,
                ]
            );
        }

        if ((int) ($validationSummary['warning_layers'] ?? 0) < 1) {
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_WARNING_LAYER_MISSING',
                'WS_BT_C41_C40_WARNING_LAYER_MISSING',
                'C41 warning review requires at least one C40 warning layer.',
                $outputPath,
                ['warning_layers' => $validationSummary['warning_layers'] ?? null]
            );
        }

        if (! $this->c40SafetyIsPreserved($c40)) {
            $candidateSafetyAudit = $c40['candidate_safety_audit'] ?? [];
            return $this->blocked(
                $artifact,
                'C41_BLOCKED_C40_SAFETY_BOUNDARY_INVALID',
                'WS_BT_C41_C40_SAFETY_BOUNDARY_INVALID',
                'C41 requires C40 safety boundaries to show no OOS proof, no OOS tuning, and no production promotion.',
                $outputPath,
                ['candidate_safety_audit_count' => is_array($candidateSafetyAudit) ? count($candidateSafetyAudit) : 0]
            );
        }

        $artifact['warning_layer_review'] = $this->warningLayerReview($c40);
        $artifact['guard_blocker_recheck'] = $this->guardBlockerRecheck($c40);
        $artifact['not_evaluable_evidence_gap_review'] = $this->notEvaluableEvidenceGapReview($c40);
        $artifact['evidence_expansion_requirements'] = $this->evidenceExpansionRequirements(
            $artifact['warning_layer_review'],
            $artifact['not_evaluable_evidence_gap_review']
        );
        $artifact['review_decision_summary'] = $this->reviewDecisionSummary($artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($c40);
        $artifact['status'] = 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED';
        $artifact['diagnostic_conclusion'] = 'C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS';
        $artifact['next_step_recommendation'] = 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_OR_GUARD_REFINEMENT';
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(
        string $inputC40Path,
        string $expectedC40Hash,
        ?string $actualC40Hash,
        $c40Status,
        $c40Conclusion,
        $c40NextStep,
        string $from,
        string $to,
        string $createdAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C41_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c40_artifact' => $inputC40Path,
            'expected_c40_hash' => $expectedC40Hash,
            'actual_c40_hash' => $actualC40Hash,
            'c40_hash_match' => $actualC40Hash !== null && $actualC40Hash === $expectedC40Hash,
            'c40_status' => $c40Status,
            'c40_diagnostic_conclusion' => $c40Conclusion,
            'c40_next_step_recommendation' => $c40NextStep,
            'expected_c40_file_sha1' => self::DEFAULT_C40_FILE_SHA1,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c40_summary' => [
                'target_candidate_code' => self::TARGET_CANDIDATE_CODE,
                'overall_anti_overfit_result' => null,
                'warning_layers' => 0,
                'failed_layers' => 0,
                'not_evaluable_layers' => 0,
                'candidate_c40_decision' => null,
                'source_c39_best_candidate' => null,
                'source_evidence' => null,
            ],
            'warning_layer_review' => [
                'rolling_warning_review' => ['warning_or_fail_window_count' => 0, 'window_reviews' => []],
                'non_bad_month_warning_review' => ['needs_review' => false],
                'warning_layer_count' => 0,
            ],
            'guard_blocker_recheck' => [
                'month_coverage_result' => null,
                'branch_concentration_result' => null,
                'prior_c37_coverage_branch_blocker_resolved' => false,
            ],
            'not_evaluable_evidence_gap_review' => [
                'carry_forward_gap_count' => 0,
                'gaps' => [],
            ],
            'evidence_expansion_requirements' => [],
            'review_decision_summary' => [
                'candidate_decision' => 'C41_REVIEW_PENDING',
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'new_candidate_selected' => false,
                'production_ready' => false,
            ],
            'candidate_safety_audit' => [],
            'diagnostic_conclusion' => 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_PENDING',
            'next_step_recommendation' => 'C41_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C41_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C41 locks C40 warning evidence, reviews IS warning layers only, and does not run OOS proof or production promotion.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS' => true,
                'C40_ARTIFACT_HASH_LOCK' => true,
                'C41_SOURCE_IS_C40_WARNING_ARTIFACT' => true,
                'IS_ONLY_REVIEW' => true,
                'EVIDENCE_EXPANSION_REVIEW_ONLY' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C40_MUTATION' => true,
                'NO_C01_TO_C40_ARTIFACT_MUTATION' => true,
                'NO_C41_CANDIDATE_RESELECTION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_candidate_selection' => false,
            ],
            'execution_model' => [
                'entry' => 'NEXT_OPEN',
                'exit' => 'STOP_TP_OR_TIME',
                'hold' => 5,
                'fee' => 'IDR_FIXED',
                'slip' => 0,
                'gap' => 'OPEN',
                'px' => 'IDX_BANDS',
            ],
            'created_at' => $createdAt,
        ];
    }

    private function sourceC40Summary(array $c40): array
    {
        $validationSummary = is_array($c40['validation_summary'] ?? null) ? $c40['validation_summary'] : [];
        $antiOverfit = is_array($c40['anti_overfit_summary'] ?? null) ? $c40['anti_overfit_summary'] : [];

        return [
            'c40_artifact_hash_field' => $c40['artifact_hash'] ?? null,
            'source_c39_artifact' => $c40['input_c39_artifact'] ?? null,
            'expected_c39_hash' => $c40['expected_c39_hash'] ?? null,
            'actual_c39_hash' => $c40['actual_c39_hash'] ?? null,
            'c39_hash_match' => $c40['c39_hash_match'] ?? null,
            'c39_status' => $c40['c39_status'] ?? null,
            'c39_diagnostic_conclusion' => $c40['c39_diagnostic_conclusion'] ?? null,
            'target_candidate_code' => $c40['validation_target']['target_candidate_code'] ?? self::TARGET_CANDIDATE_CODE,
            'source_c39_best_candidate' => $c40['source_c39_summary']['best_is_candidate_code'] ?? null,
            'source_evidence' => $c40['source_c39_summary']['source_evidence'] ?? null,
            'overall_anti_overfit_result' => $validationSummary['overall_anti_overfit_result'] ?? null,
            'passed_layers' => $validationSummary['passed_layers'] ?? null,
            'warning_layers' => $validationSummary['warning_layers'] ?? null,
            'failed_layers' => $validationSummary['failed_layers'] ?? null,
            'not_evaluable_layers' => $validationSummary['not_evaluable_layers'] ?? null,
            'candidate_c40_decision' => $validationSummary['candidate_c40_decision'] ?? null,
            'layer_results' => [
                'full_is_result' => $antiOverfit['full_is_result'] ?? ($c40['full_is_validation']['result'] ?? null),
                'yearly_validation_result' => $antiOverfit['yearly_validation_result'] ?? null,
                'rolling_validation_result' => $antiOverfit['rolling_validation_result'] ?? null,
                'bad_month_stress_result' => $antiOverfit['bad_month_stress_result'] ?? null,
                'normal_month_result' => $antiOverfit['normal_month_result'] ?? ($c40['non_bad_month_validation']['result'] ?? null),
                'ticker_concentration_result' => $antiOverfit['ticker_concentration_result'] ?? null,
                'branch_concentration_result' => $antiOverfit['branch_concentration_result'] ?? ($c40['branch_concentration_validation']['result'] ?? null),
                'month_coverage_result' => $antiOverfit['month_coverage_result'] ?? ($c40['month_coverage_validation']['result'] ?? null),
                'downside_stability_result' => $antiOverfit['downside_stability_result'] ?? null,
            ],
            'full_is_deltas_vs_baseline' => $c40['full_is_validation']['comparison_vs_baseline'] ?? [],
        ];
    }

    private function warningLayerReview(array $c40): array
    {
        $rolling = $this->rollingWarningReview($c40['rolling_window_validation'] ?? []);
        $nonBadMonth = $this->nonBadMonthWarningReview($c40['non_bad_month_validation'] ?? []);
        $warningLayerCount = (int) (($c40['validation_summary']['warning_layers'] ?? 0));

        return [
            'warning_layer_count' => $warningLayerCount,
            'rolling_warning_review' => $rolling,
            'non_bad_month_warning_review' => $nonBadMonth,
            'review_result' => $rolling['warning_or_fail_window_count'] > 0 || $nonBadMonth['needs_review']
                ? 'C41_WARNING_LAYERS_REQUIRE_EVIDENCE_EXPANSION'
                : 'C41_NO_WARNING_LAYER_DETAIL_FOUND',
        ];
    }

    private function rollingWarningReview($rows): array
    {
        $rows = is_array($rows) ? $rows : [];
        $reviews = [];
        $warningCount = 0;
        $failCount = 0;
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $result = (string) ($row['result'] ?? '');
            if (! in_array($result, ['WARNING', 'FAIL'], true)) {
                continue;
            }
            if ($result === 'WARNING') {
                $warningCount++;
            }
            if ($result === 'FAIL') {
                $failCount++;
            }
            $comparison = is_array($row['comparison_vs_baseline'] ?? null) ? $row['comparison_vs_baseline'] : [];
            $target = is_array($row['target_candidate'] ?? null) ? $row['target_candidate'] : [];
            $baseline = is_array($row['baseline_candidate'] ?? null) ? $row['baseline_candidate'] : [];
            $reviews[] = [
                'validation_slice' => $row['validation_slice'] ?? null,
                'window_code' => $row['window_code'] ?? null,
                'result' => $result,
                'reason_code' => $row['reason_code'] ?? null,
                'baseline_selected_rows' => $baseline['selected_rows'] ?? null,
                'target_selected_rows' => $target['selected_rows'] ?? null,
                'target_month_avg_ret_net_min' => $target['month_avg_ret_net_min'] ?? null,
                'baseline_month_avg_ret_net_min' => $baseline['month_avg_ret_net_min'] ?? null,
                'delta_avg_ret_net_vs_baseline' => $comparison['delta_avg_ret_net_vs_baseline'] ?? null,
                'delta_month_avg_ret_net_min_vs_baseline' => $comparison['delta_month_avg_ret_net_min_vs_baseline'] ?? null,
                'delta_bad_month_like_count_vs_baseline' => $comparison['delta_bad_month_like_count_vs_baseline'] ?? null,
                'c41_review_action' => 'C41_EXPAND_ROLLING_WARNING_WINDOW_PRE_TRADE_SPLIT_EVIDENCE',
            ];
        }

        return [
            'warning_or_fail_window_count' => count($reviews),
            'warning_window_count' => $warningCount,
            'fail_window_count' => $failCount,
            'window_reviews' => $reviews,
        ];
    }

    private function nonBadMonthWarningReview($row): array
    {
        $row = is_array($row) ? $row : [];
        $result = (string) ($row['result'] ?? '');
        $comparison = is_array($row['comparison_vs_baseline'] ?? null) ? $row['comparison_vs_baseline'] : [];
        $target = is_array($row['target_candidate'] ?? null) ? $row['target_candidate'] : [];
        $baseline = is_array($row['baseline_candidate'] ?? null) ? $row['baseline_candidate'] : [];

        return [
            'needs_review' => in_array($result, ['WARNING', 'FAIL'], true),
            'validation_slice' => $row['validation_slice'] ?? null,
            'result' => $result !== '' ? $result : null,
            'reason_code' => $row['reason_code'] ?? null,
            'baseline_selected_rows' => $baseline['selected_rows'] ?? null,
            'target_selected_rows' => $target['selected_rows'] ?? null,
            'target_month_avg_ret_net_min' => $target['month_avg_ret_net_min'] ?? null,
            'baseline_month_avg_ret_net_min' => $baseline['month_avg_ret_net_min'] ?? null,
            'delta_avg_ret_net_vs_baseline' => $comparison['delta_avg_ret_net_vs_baseline'] ?? null,
            'delta_p25_ret_net_vs_baseline' => $comparison['delta_p25_ret_net_vs_baseline'] ?? null,
            'delta_win_rate_vs_baseline' => $comparison['delta_win_rate_vs_baseline'] ?? null,
            'delta_month_avg_ret_net_min_vs_baseline' => $comparison['delta_month_avg_ret_net_min_vs_baseline'] ?? null,
            'delta_bad_month_like_count_vs_baseline' => $comparison['delta_bad_month_like_count_vs_baseline'] ?? null,
            'c41_review_action' => in_array($result, ['WARNING', 'FAIL'], true)
                ? 'C41_EXPAND_NON_BAD_MONTH_STABILITY_EVIDENCE'
                : 'C41_NON_BAD_MONTH_LAYER_DOES_NOT_REQUIRE_EXPANSION',
        ];
    }

    private function guardBlockerRecheck(array $c40): array
    {
        $coverage = is_array($c40['month_coverage_validation'] ?? null) ? $c40['month_coverage_validation'] : [];
        $branch = is_array($c40['branch_concentration_validation'] ?? null) ? $c40['branch_concentration_validation'] : [];
        $candidateCoverage = is_array($coverage['candidate'] ?? null) ? $coverage['candidate'] : [];
        $candidateBranch = is_array($branch['candidate'] ?? null) ? $branch['candidate'] : [];
        $coveragePass = ($coverage['result'] ?? null) === 'PASS' && (int) ($candidateCoverage['zero_pick_months'] ?? 1) === 0;
        $branchPass = ($branch['result'] ?? null) === 'PASS';

        return [
            'month_coverage_result' => $coverage['result'] ?? null,
            'candidate_months_covered' => $candidateCoverage['months_covered'] ?? null,
            'candidate_zero_pick_months' => $candidateCoverage['zero_pick_months'] ?? null,
            'candidate_min_selected_rows_per_month' => $candidateCoverage['min_selected_rows_per_month'] ?? null,
            'branch_concentration_result' => $branch['result'] ?? null,
            'candidate_top_branch_share' => $branch['top_branch_share'] ?? ($candidateBranch['top_branch_share'] ?? null),
            'candidate_g16_share' => $branch['g16_share'] ?? ($candidateBranch['g16_share'] ?? null),
            'candidate_g21_share' => $branch['g21_share'] ?? ($candidateBranch['g21_share'] ?? null),
            'removed_or_suppressed_g21_rows' => $branch['removed_or_suppressed_g21_rows'] ?? null,
            'prior_c37_coverage_branch_blocker_resolved' => $coveragePass && $branchPass,
            'c41_guard_action' => $coveragePass && $branchPass
                ? 'C41_PRESERVE_C39_COVERAGE_AND_BRANCH_GUARDS'
                : 'C41_REVIEW_COVERAGE_OR_BRANCH_GUARD_BEFORE_OOS',
        ];
    }

    private function notEvaluableEvidenceGapReview(array $c40): array
    {
        $gaps = [];
        foreach (($c40['not_evaluable_reasons'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $slice = (string) ($row['validation_slice'] ?? '');
            $gaps[] = [
                'validation_layer' => $row['validation_layer'] ?? null,
                'validation_slice' => $slice,
                'reason_code' => $row['reason_code'] ?? null,
                'message' => $row['message'] ?? null,
                'c41_review_action' => $this->gapAction($slice, (string) ($row['reason_code'] ?? '')),
            ];
        }

        return [
            'carry_forward_gap_count' => count($gaps),
            'gaps' => $gaps,
        ];
    }

    private function evidenceExpansionRequirements(array $warningReview, array $gapReview): array
    {
        $requirements = [];

        if ((int) ($warningReview['rolling_warning_review']['warning_or_fail_window_count'] ?? 0) > 0) {
            $requirements[] = [
                'requirement_code' => 'C41_REQ_ROLLING_WARNING_WINDOW_PRE_TRADE_SPLIT_REVIEW',
                'priority' => 'HIGH',
                'status' => 'REQUIRED_BEFORE_OOS',
                'reason' => 'Rolling IS warning windows remain in C40 and need pre-trade split evidence before OOS proof.',
            ];
        }

        if (($warningReview['non_bad_month_warning_review']['needs_review'] ?? false) === true) {
            $requirements[] = [
                'requirement_code' => 'C41_REQ_NON_BAD_MONTH_STABILITY_REVIEW',
                'priority' => 'HIGH',
                'status' => 'REQUIRED_BEFORE_OOS',
                'reason' => 'Non-bad-month stability warning remains in C40 and needs evidence expansion before OOS proof.',
            ];
        }

        $seenGapRequirements = [];
        foreach (($gapReview['gaps'] ?? []) as $gap) {
            $action = (string) ($gap['c41_review_action'] ?? '');
            $code = $action === 'C41_EXPAND_G21_PRE_TRADE_QUALITY_FIELDS'
                ? 'C41_REQ_G21_PRE_TRADE_QUALITY_FIELD_EXPANSION'
                : ($action === 'C41_EXPAND_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELDS'
                    ? 'C41_REQ_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELD_EXPANSION'
                    : 'C41_REQ_CARRY_FORWARD_EVIDENCE_GAP_REVIEW');
            if (isset($seenGapRequirements[$code])) {
                continue;
            }
            $seenGapRequirements[$code] = true;
            $requirements[] = [
                'requirement_code' => $code,
                'priority' => 'MEDIUM',
                'status' => 'REQUIRED_BEFORE_OOS',
                'reason' => 'C40 carries forward C39 evidence gaps that are not safely evaluable from the current IS diagnostic fields.',
            ];
        }

        $requirements[] = [
            'requirement_code' => 'C41_REQ_PRESERVE_C39_COVERAGE_BRANCH_GUARDS',
            'priority' => 'LOW',
            'status' => 'PRESERVE',
            'reason' => 'C40 confirms the C39 month coverage and branch concentration guards resolved the prior blocker.',
        ];

        return $requirements;
    }

    private function reviewDecisionSummary(array $artifact): array
    {
        $sourceSummary = $artifact['source_c40_summary'] ?? [];
        $warningReview = $artifact['warning_layer_review'] ?? [];
        $guardReview = $artifact['guard_blocker_recheck'] ?? [];
        $gapReview = $artifact['not_evaluable_evidence_gap_review'] ?? [];

        return [
            'candidate_decision' => 'C41_REQUIRES_EVIDENCE_EXPANSION_BEFORE_OOS',
            'candidate_decision_reason' => 'C40 has warning layers but no failed layers; C41 therefore requires IS evidence expansion/review before any OOS proof.',
            'target_candidate_code' => $sourceSummary['target_candidate_code'] ?? self::TARGET_CANDIDATE_CODE,
            'overall_anti_overfit_result' => $sourceSummary['overall_anti_overfit_result'] ?? null,
            'warning_layers_count' => $sourceSummary['warning_layers'] ?? null,
            'failed_layers_count' => $sourceSummary['failed_layers'] ?? null,
            'not_evaluable_layers_count' => $sourceSummary['not_evaluable_layers'] ?? null,
            'rolling_warning_windows' => $warningReview['rolling_warning_review']['warning_or_fail_window_count'] ?? 0,
            'non_bad_month_warning' => $warningReview['non_bad_month_warning_review']['needs_review'] ?? false,
            'carry_forward_gap_count' => $gapReview['carry_forward_gap_count'] ?? 0,
            'guard_blockers_resolved' => $guardReview['prior_c37_coverage_branch_blocker_resolved'] ?? false,
            'evidence_requirements_count' => count($artifact['evidence_expansion_requirements'] ?? []),
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'new_candidate_selected' => false,
            'candidate_reselected' => false,
            'production_ready' => false,
        ];
    }

    private function candidateSafetyAudit(array $c40): array
    {
        return [
            [
                'candidate_code' => $c40['validation_target']['target_candidate_code'] ?? self::TARGET_CANDIDATE_CODE,
                'review_layer' => self::RUN_CODE,
                'passed' => true,
                'reason_code' => 'WS_BT_C41_CANDIDATE_SELECTION_INPUT_SAFE',
                'message' => 'C41 only reviews locked C40 warning evidence and does not use return, future path, OOS data, OOS proof, or production promotion.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_data_used_for_tuning' => false,
                'production_ready' => false,
                'candidate_is_not_production' => true,
                'no_oos_proof' => true,
                'no_best_of_oos' => true,
                'no_oos_winner' => true,
                'no_production_catalog' => true,
                'no_candidate_promoted' => true,
                'no_plan_confirm_mutation' => true,
                'new_candidate_selected' => false,
            ],
        ];
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C41_C40_WARNING_REVIEW_COMPLETED',
                'message' => 'C41 completed IS-only review of the locked C40 warning artifact.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C41_NO_OOS_OR_PRODUCTION_ACTION',
                'message' => 'C41 did not run OOS proof, did not select a new candidate, and did not promote production readiness.',
                'fatal' => false,
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C41_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C41 diagnostic conclusion requires evidence expansion/review before any OOS proof.',
                'fatal' => false,
            ],
        ];
    }

    private function gapAction(string $slice, string $reasonCode): string
    {
        $combined = strtoupper($slice.' '.$reasonCode);
        if (strpos($combined, 'G21_PRE_TRADE_QUALITY') !== false || strpos($combined, 'G21') !== false) {
            return 'C41_EXPAND_G21_PRE_TRADE_QUALITY_FIELDS';
        }
        if (strpos($combined, 'ROLLING_STABILITY') !== false || strpos($combined, 'ROLLING') !== false) {
            return 'C41_EXPAND_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELDS';
        }
        return 'C41_REVIEW_CARRY_FORWARD_EVIDENCE_GAP';
    }

    private function c40SafetyIsPreserved(array $c40): bool
    {
        if (! $this->strictFalse($c40['production_ready'] ?? false)) {
            return false;
        }
        if (! $this->strictFalse($c40['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return false;
        }

        $anti = is_array($c40['anti_overfit_summary'] ?? null) ? $c40['anti_overfit_summary'] : [];
        if (count($anti) === 0) {
            return false;
        }
        foreach (['no_oos_proof', 'no_oos_tuning', 'no_best_of_oos', 'no_production_catalog', 'no_candidate_promoted'] as $flag) {
            if (($anti[$flag] ?? null) !== true) {
                return false;
            }
        }

        $auditRows = $c40['candidate_safety_audit'] ?? [];
        if (! is_array($auditRows) || count($auditRows) === 0) {
            return false;
        }

        foreach ($auditRows as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach (['return_used_for_selection', 'future_path_used_for_selection', 'profile_ret_net_used_for_selection', 'future_path_price_used_for_selection', 'derived_mfe_mae_used_for_execution', 'oos_data_used_for_tuning', 'production_ready'] as $flag) {
                if (! $this->strictFalse($row[$flag] ?? false)) {
                    return false;
                }
            }
            foreach (['no_oos_proof', 'no_best_of_oos', 'no_oos_winner', 'no_production_catalog', 'no_candidate_promoted', 'no_plan_confirm_mutation'] as $flag) {
                if (($row[$flag] ?? null) !== true) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validPeriod(string $from, string $to): bool
    {
        return $from !== '' && $to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0;
    }

    private function touchesOos(string $from, string $to): bool
    {
        return strcmp($to, self::OOS_RESERVED_FROM) >= 0 || strcmp($from, self::OOS_RESERVED_FROM) >= 0;
    }

    private function strictFalse($value): bool
    {
        return $value === false || $value === 0 || $value === '0';
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value === null) {
            return '';
        }
        return (string) $value;
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, array $extra = []): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['diagnostic_conclusion'] = 'C41_INPUT_LOCK_OR_BOUNDARY_BLOCKED';
        $artifact['next_step_recommendation'] = 'C41_BLOCKED_UNTIL_INPUT_VALIDATED';
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact, true);
        }

        return [
            'status' => $status,
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c40_hash' => $artifact['expected_c40_hash'] ?? null,
            'actual_c40_hash' => $artifact['actual_c40_hash'] ?? null,
            'c40_hash_match' => $artifact['c40_hash_match'] ?? false,
            'c40_status' => $artifact['c40_status'] ?? null,
            'c40_diagnostic_conclusion' => $artifact['c40_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
        ];
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($outputPath, $artifact, $overwrite);
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C41_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C41 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c40_hash' => $artifact['expected_c40_hash'] ?? null,
                'actual_c40_hash' => $artifact['actual_c40_hash'] ?? null,
                'c40_hash_match' => $artifact['c40_hash_match'] ?? false,
                'c40_status' => $artifact['c40_status'] ?? null,
                'c40_diagnostic_conclusion' => $artifact['c40_diagnostic_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }

        return [
            'status' => $artifact['status'] ?? 'C41_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C41_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c40_hash' => $artifact['expected_c40_hash'] ?? null,
            'actual_c40_hash' => $artifact['actual_c40_hash'] ?? null,
            'c40_hash_match' => $artifact['c40_hash_match'] ?? false,
            'c40_status' => $artifact['c40_status'] ?? null,
            'c40_diagnostic_conclusion' => $artifact['c40_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c40_summary' => $artifact['source_c40_summary'] ?? [],
            'warning_layer_review' => $artifact['warning_layer_review'] ?? [],
            'review_decision_summary' => $artifact['review_decision_summary'] ?? [],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) {
                return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.'];
            }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C41 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
