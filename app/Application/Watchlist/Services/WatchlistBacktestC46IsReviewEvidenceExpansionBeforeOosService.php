<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService
{
    public const RUN_CODE = 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
    public const ARTIFACT_TYPE = 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
    public const DEFAULT_C45_ARTIFACT = 'storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json';
    public const DEFAULT_EXPECTED_C45_HASH = '47970ba6e772bcf7fec68f306883f9f3d6cdd976';
    public const DEFAULT_C45_FILE_SHA1 = 'CF7D7D78103B543814C1B84F29B33AEA3E4FAF78';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C45_STATUS = 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT_COMPLETED';
    public const EXPECTED_C45_CONCLUSION = 'C45_C44_REFINEMENT_WARNING_REQUIRES_REVIEW_BEFORE_OOS';
    public const EXPECTED_C45_NEXT_STEP = 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
    public const TARGET_CANDIDATE_CODE = 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA';

    private const AVG_HARD_FAIL_BUDGET = 0.005;
    private const MONTH_MIN_HARD_FAIL_BUDGET = 0.010;
    private const REVIEW_BUDGET_SHARE_LIMIT = 0.25;
    private const NORMAL_MONTH_AVG_BUDGET_SHARE_LIMIT = 0.10;
    private const ROLLING_WARNING_SHARE_LIMIT = 0.25;

    public function execute(
        string $c45Artifact = self::DEFAULT_C45_ARTIFACT,
        string $expectedC45Hash = self::DEFAULT_EXPECTED_C45_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($c45Artifact, $expectedC45Hash, null, null, null, null, $from, $to, $createdAt);
        if (! is_file($c45Artifact)) {
            return $this->blocked($artifact, 'C46_BLOCKED_MISSING_C45_ARTIFACT', 'WS_BT_C46_C45_ARTIFACT_MISSING', 'C46 requires the locked C45 validation artifact.', $outputPath);
        }
        $c45 = json_decode((string) file_get_contents($c45Artifact), true);
        if (! is_array($c45)) {
            return $this->blocked($artifact, 'C46_BLOCKED_MISSING_C45_ARTIFACT', 'WS_BT_C46_C45_ARTIFACT_UNREADABLE', 'C45 artifact is not readable JSON.', $outputPath);
        }

        $actualHash = $this->stableHash($c45);
        $artifact = $this->baseArtifact($c45Artifact, $expectedC45Hash, $actualHash, $c45['status'] ?? null, $c45['diagnostic_conclusion'] ?? null, $c45['next_step_recommendation'] ?? null, $from, $to, $createdAt);
        $artifact['source_c45_summary'] = $this->sourceC45Summary($c45);

        if ($actualHash !== $expectedC45Hash) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_HASH_MISMATCH', 'WS_BT_C46_C45_ARTIFACT_HASH_MISMATCH', 'C45 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c45['status'] ?? null) !== self::EXPECTED_C45_STATUS) {
            return $this->blocked($artifact, 'C46_BLOCKED_UNEXPECTED_C45_STATUS', 'WS_BT_C46_UNEXPECTED_C45_STATUS', 'C46 requires a completed C45 validation artifact.', $outputPath);
        }
        if (($c45['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C45_CONCLUSION) {
            return $this->blocked($artifact, 'C46_BLOCKED_UNEXPECTED_C45_CONCLUSION', 'WS_BT_C46_UNEXPECTED_C45_CONCLUSION', 'C46 requires the C45 warning review conclusion.', $outputPath);
        }
        if (($c45['next_step_recommendation'] ?? null) !== self::EXPECTED_C45_NEXT_STEP) {
            return $this->blocked($artifact, 'C46_BLOCKED_UNEXPECTED_C45_NEXT_STEP', 'WS_BT_C46_UNEXPECTED_C45_NEXT_STEP', 'C45 does not authorize C46 review.', $outputPath);
        }
        if (! $this->strictFalse($c45['production_ready'] ?? false) || ! $this->strictFalse($c45['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_SAFETY_FLAGS_INVALID', 'WS_BT_C46_C45_SAFETY_FLAGS_INVALID', 'C46 requires C45 production_ready=false and oos_data_used_for_tuning=false.', $outputPath);
        }
        if (($c45['validation_summary']['overall_anti_overfit_result'] ?? null) !== 'WARNING') {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_RESULT_NOT_WARNING', 'WS_BT_C46_C45_RESULT_NOT_WARNING', 'C46 review is defined for the locked C45 WARNING result.', $outputPath);
        }
        if (($c45['validation_summary']['failed_layers'] ?? null) !== 0 || ($c45['validation_summary']['not_evaluable_layers'] ?? null) !== 0) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_FAILURE_OR_GAP_PRESENT', 'WS_BT_C46_C45_FAILURE_OR_GAP_PRESENT', 'C46 cannot accept a C45 artifact with failed or not-evaluable layers.', $outputPath);
        }
        if (($c45['validation_summary']['direct_oos_proof_recommended'] ?? true) !== false || ($c45['validation_summary']['oos_proof_unlocked'] ?? true) !== false) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_OOS_FLAGS_INVALID', 'WS_BT_C46_C45_OOS_FLAGS_INVALID', 'C46 requires C45 direct OOS and unlock flags to remain false.', $outputPath);
        }
        if (($c45['validation_summary']['requires_human_review_before_any_oos_step'] ?? false) !== true) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_REVIEW_FLAG_INVALID', 'WS_BT_C46_C45_REVIEW_FLAG_INVALID', 'C45 must explicitly require review before OOS.', $outputPath);
        }
        if (($c45['validation_target']['target_candidate_code'] ?? null) !== self::TARGET_CANDIDATE_CODE || ($c45['validation_target']['target_candidate_is_not_production'] ?? false) !== true) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_TARGET_INVALID', 'WS_BT_C46_C45_TARGET_INVALID', 'C46 requires the locked non-production C44 market-extension target.', $outputPath);
        }
        if (($c45['candidate_safety_audit']['passed'] ?? false) !== true || ! $this->strictFalse($c45['candidate_safety_audit']['oos_proof_executed'] ?? true)) {
            return $this->blocked($artifact, 'C46_BLOCKED_C45_SAFETY_AUDIT_INVALID', 'WS_BT_C46_C45_SAFETY_AUDIT_INVALID', 'C46 requires the completed C45 safety audit and no prior OOS execution.', $outputPath);
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C46_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C46_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C46 review is IS-only and rejects periods touching reserved OOS.', $outputPath);
        }

        $artifact['review_thresholds'] = $this->reviewThresholds();
        $artifact['warning_layer_inventory'] = $this->warningLayerInventory($c45);
        $artifact['yearly_warning_review'] = $this->yearlyWarningReview($c45);
        $artifact['rolling_warning_review'] = $this->rollingWarningReview($c45);
        $artifact['non_bad_month_warning_review'] = $this->nonBadMonthWarningReview($c45);
        $artifact['corroborating_pass_review'] = $this->corroboratingPassReview($c45);
        $artifact['guard_and_safety_recheck'] = $this->guardAndSafetyRecheck($c45);
        $artifact['prior_warning_gap_resolution'] = $this->priorWarningGapResolution($c45);
        $artifact['evidence_expansion_requirements'] = $this->evidenceExpansionRequirements($artifact);
        $artifact['review_decision_summary'] = $this->reviewDecisionSummary($artifact);
        $accepted = (bool) ($artifact['review_decision_summary']['warning_acceptable_for_locked_oos_proof'] ?? false);

        $artifact['status'] = 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED';
        $artifact['diagnostic_conclusion'] = $accepted
            ? 'C46_C45_WARNING_ACCEPTED_FOR_LOCKED_OOS_PROOF'
            : 'C46_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS';
        $artifact['next_step_recommendation'] = $accepted
            ? 'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT'
            : 'C47_IS_WARNING_SLICE_EVIDENCE_EXPANSION_FOR_C44_REFINEMENT';
        $artifact['diagnostics'][] = [
            'reason_code' => $artifact['diagnostic_conclusion'],
            'message' => $accepted
                ? 'C46 found the remaining C45 warnings bounded, explained, and well inside hard-fail budgets; it authorizes only a separately executed locked C47 OOS proof.'
                : 'C46 found at least one warning review condition unresolved; OOS remains locked pending IS evidence expansion.',
            'fatal' => false,
        ];

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function reviewThresholds(): array
    {
        return [
            'source' => 'C45_HARD_FAIL_BUDGET_HEADROOM_REVIEW',
            'avg_hard_fail_budget' => self::AVG_HARD_FAIL_BUDGET,
            'month_min_hard_fail_budget' => self::MONTH_MIN_HARD_FAIL_BUDGET,
            'yearly_and_rolling_max_budget_share' => self::REVIEW_BUDGET_SHARE_LIMIT,
            'normal_month_avg_max_budget_share' => self::NORMAL_MONTH_AVG_BUDGET_SHARE_LIMIT,
            'rolling_warning_share_limit' => self::ROLLING_WARNING_SHARE_LIMIT,
            'warning_slice_bad_month_increase_allowed' => 0,
            'failed_slice_allowed' => 0,
            'not_evaluable_slice_allowed' => 0,
            'thresholds_do_not_select_or_reselect_candidate' => true,
        ];
    }

    private function warningLayerInventory(array $c45): array
    {
        $layers = (array) ($c45['validation_summary']['layer_results'] ?? []);
        $warnings = [];
        foreach ($layers as $layer => $result) {
            if ($result === 'WARNING') {
                $warnings[] = $layer;
            }
        }
        return [
            'total_layers' => (int) ($c45['validation_summary']['total_validation_layers'] ?? count($layers)),
            'passed_layers' => (int) ($c45['validation_summary']['passed_layers'] ?? 0),
            'warning_layers' => (int) ($c45['validation_summary']['warning_layers'] ?? count($warnings)),
            'failed_layers' => (int) ($c45['validation_summary']['failed_layers'] ?? 0),
            'not_evaluable_layers' => (int) ($c45['validation_summary']['not_evaluable_layers'] ?? 0),
            'warning_layer_names' => $warnings,
            'warning_inventory_complete' => count($warnings) === (int) ($c45['validation_summary']['warning_layers'] ?? -1),
        ];
    }

    private function yearlyWarningReview(array $c45): array
    {
        $reviews = [];
        $allBounded = true;
        foreach ((array) ($c45['yearly_validation']['slices'] ?? []) as $slice) {
            if (($slice['result'] ?? null) !== 'WARNING') {
                continue;
            }
            $comparison = (array) ($slice['comparison_vs_baseline'] ?? []);
            $avgDelta = $this->num($comparison['delta_avg_ret_net'] ?? null);
            $monthMinDelta = $this->num($comparison['delta_month_avg_ret_net_min'] ?? null);
            $badDelta = (int) ($comparison['delta_bad_month_like_count'] ?? 0);
            $avgErosion = max(0.0, -(float) ($avgDelta ?? 0.0));
            $monthMinErosion = max(0.0, -(float) ($monthMinDelta ?? 0.0));
            $avgBudgetShare = $avgErosion / self::AVG_HARD_FAIL_BUDGET;
            $monthMinBudgetShare = $monthMinErosion / self::MONTH_MIN_HARD_FAIL_BUDGET;
            $bounded = $avgDelta !== null && $monthMinDelta !== null
                && $avgBudgetShare <= self::REVIEW_BUDGET_SHARE_LIMIT
                && $monthMinBudgetShare <= self::REVIEW_BUDGET_SHARE_LIMIT
                && $badDelta <= 0;
            $allBounded = $allBounded && $bounded;
            $reviews[] = [
                'validation_slice' => $slice['validation_slice'] ?? null,
                'delta_avg_ret_net' => $avgDelta,
                'delta_p25_ret_net' => $comparison['delta_p25_ret_net'] ?? null,
                'delta_p10_ret_net' => $comparison['delta_p10_ret_net'] ?? null,
                'delta_month_avg_ret_net_min' => $monthMinDelta,
                'delta_bad_month_like_count' => $badDelta,
                'avg_hard_fail_budget_share_used' => $avgBudgetShare,
                'month_min_hard_fail_budget_share_used' => $monthMinBudgetShare,
                'classification' => $this->yearlyClassification($avgDelta, $monthMinDelta),
                'bounded_and_explained' => $bounded,
            ];
        }
        return [
            'source_result' => $c45['yearly_validation']['result'] ?? null,
            'warning_slice_count' => count($reviews),
            'failed_slice_count' => $this->countSlicesByResult((array) ($c45['yearly_validation']['slices'] ?? []), 'FAIL'),
            'warning_slices' => $reviews,
            'all_yearly_warnings_bounded' => count($reviews) > 0 && $allBounded,
            'result' => count($reviews) > 0 && $allBounded ? 'PASS' : 'REQUIRES_EVIDENCE_EXPANSION',
        ];
    }

    private function rollingWarningReview(array $c45): array
    {
        $rolling = (array) ($c45['rolling_window_validation'] ?? []);
        $slices = (array) ($rolling['slices'] ?? []);
        $warnings = [];
        $worstAvg = null;
        $worstMonthMin = null;
        $badIncreaseCount = 0;
        foreach ($slices as $slice) {
            if (($slice['result'] ?? null) !== 'WARNING') {
                continue;
            }
            $comparison = (array) ($slice['comparison_vs_baseline'] ?? []);
            $avg = $this->num($comparison['delta_avg_ret_net'] ?? null);
            $monthMin = $this->num($comparison['delta_month_avg_ret_net_min'] ?? null);
            $bad = (int) ($comparison['delta_bad_month_like_count'] ?? 0);
            $worstAvg = $worstAvg === null || ($avg !== null && $avg < $worstAvg) ? $avg : $worstAvg;
            $worstMonthMin = $worstMonthMin === null || ($monthMin !== null && $monthMin < $worstMonthMin) ? $monthMin : $worstMonthMin;
            if ($bad > 0) {
                $badIncreaseCount++;
            }
            $warnings[] = [
                'validation_slice' => $slice['validation_slice'] ?? null,
                'delta_avg_ret_net' => $avg,
                'delta_p25_ret_net' => $comparison['delta_p25_ret_net'] ?? null,
                'delta_p10_ret_net' => $comparison['delta_p10_ret_net'] ?? null,
                'delta_month_avg_ret_net_min' => $monthMin,
                'delta_bad_month_like_count' => $bad,
                'classification' => $avg !== null && $avg < 0.0 ? 'BOUNDED_AVERAGE_TRADEOFF' : 'BOUNDED_WORST_MONTH_TRADEOFF',
            ];
        }
        $sliceCount = (int) ($rolling['slice_count'] ?? count($slices));
        $warningCount = (int) ($rolling['warning_count'] ?? count($warnings));
        $failCount = (int) ($rolling['fail_count'] ?? $this->countSlicesByResult($slices, 'FAIL'));
        $warningShare = $sliceCount > 0 ? $warningCount / $sliceCount : null;
        $avgBudgetShare = max(0.0, -(float) ($worstAvg ?? 0.0)) / self::AVG_HARD_FAIL_BUDGET;
        $monthMinBudgetShare = max(0.0, -(float) ($worstMonthMin ?? 0.0)) / self::MONTH_MIN_HARD_FAIL_BUDGET;
        $bounded = $sliceCount > 0 && $warningCount > 0 && $failCount === 0
            && $warningShare !== null && $warningShare <= self::ROLLING_WARNING_SHARE_LIMIT
            && $avgBudgetShare <= self::REVIEW_BUDGET_SHARE_LIMIT
            && $monthMinBudgetShare <= self::REVIEW_BUDGET_SHARE_LIMIT
            && $badIncreaseCount === 0;
        return [
            'source_result' => $rolling['result'] ?? null,
            'slice_count' => $sliceCount,
            'pass_count' => (int) ($rolling['pass_count'] ?? 0),
            'warning_count' => $warningCount,
            'fail_count' => $failCount,
            'warning_share' => $warningShare,
            'warning_share_limit' => self::ROLLING_WARNING_SHARE_LIMIT,
            'worst_delta_avg_ret_net' => $worstAvg,
            'worst_delta_month_avg_ret_net_min' => $worstMonthMin,
            'avg_hard_fail_budget_share_used' => $avgBudgetShare,
            'month_min_hard_fail_budget_share_used' => $monthMinBudgetShare,
            'warning_slices_with_bad_month_increase' => $badIncreaseCount,
            'warning_slices' => $warnings,
            'all_rolling_warnings_bounded' => $bounded,
            'result' => $bounded ? 'PASS' : 'REQUIRES_EVIDENCE_EXPANSION',
        ];
    }

    private function nonBadMonthWarningReview(array $c45): array
    {
        $slice = (array) ($c45['non_bad_month_validation'] ?? []);
        $comparison = (array) ($slice['comparison_vs_baseline'] ?? []);
        $avg = $this->num($comparison['delta_avg_ret_net'] ?? null);
        $avgBudgetShare = max(0.0, -(float) ($avg ?? 0.0)) / self::AVG_HARD_FAIL_BUDGET;
        $tailStable = $this->nonNegative($comparison['delta_median_ret_net'] ?? null)
            && $this->nonNegative($comparison['delta_p25_ret_net'] ?? null)
            && $this->nonNegative($comparison['delta_p10_ret_net'] ?? null)
            && $this->nonNegative($comparison['delta_month_avg_ret_net_min'] ?? null)
            && (int) ($comparison['delta_bad_month_like_count'] ?? 0) <= 0;
        $bounded = ($slice['result'] ?? null) === 'WARNING' && $avg !== null
            && $avgBudgetShare <= self::NORMAL_MONTH_AVG_BUDGET_SHARE_LIMIT
            && $tailStable;
        return [
            'source_result' => $slice['result'] ?? null,
            'normal_month_count' => count((array) ($slice['normal_months'] ?? [])),
            'delta_avg_ret_net' => $avg,
            'delta_median_ret_net' => $comparison['delta_median_ret_net'] ?? null,
            'delta_p25_ret_net' => $comparison['delta_p25_ret_net'] ?? null,
            'delta_p10_ret_net' => $comparison['delta_p10_ret_net'] ?? null,
            'delta_win_rate' => $comparison['delta_win_rate'] ?? null,
            'delta_month_avg_ret_net_min' => $comparison['delta_month_avg_ret_net_min'] ?? null,
            'delta_bad_month_like_count' => $comparison['delta_bad_month_like_count'] ?? null,
            'delta_loss_concentration' => $comparison['delta_loss_concentration'] ?? null,
            'avg_hard_fail_budget_share_used' => $avgBudgetShare,
            'tail_and_bad_month_stability_preserved' => $tailStable,
            'classification' => 'SMALL_NORMAL_MONTH_AVERAGE_TRADEOFF_FOR_BAD_MONTH_ROBUSTNESS',
            'warning_bounded_and_explained' => $bounded,
            'result' => $bounded ? 'PASS' : 'REQUIRES_EVIDENCE_EXPANSION',
        ];
    }

    private function corroboratingPassReview(array $c45): array
    {
        $layers = (array) ($c45['validation_summary']['layer_results'] ?? []);
        $required = ['full_is', 'bad_month_like_stress', 'ticker_concentration', 'branch_concentration', 'month_coverage', 'downside_stability'];
        $checks = [];
        foreach ($required as $layer) {
            $checks[$layer.'_passed'] = ($layers[$layer] ?? null) === 'PASS';
        }
        $full = (array) ($c45['full_is_validation']['comparison_vs_baseline'] ?? []);
        $bad = (array) ($c45['bad_month_like_stress_validation']['comparison_vs_baseline'] ?? []);
        $checks['full_is_avg_improved'] = $this->num($full['delta_avg_ret_net'] ?? null) !== null && (float) $full['delta_avg_ret_net'] > 0.0;
        $checks['full_is_downside_improved'] = $this->nonNegative($full['delta_p10_ret_net'] ?? null) && $this->nonNegative($full['delta_month_avg_ret_net_min'] ?? null);
        $checks['full_is_bad_month_count_reduced'] = (int) ($full['delta_bad_month_like_count'] ?? 0) < 0;
        $checks['bad_month_stress_avg_improved'] = $this->num($bad['delta_avg_ret_net'] ?? null) !== null && (float) $bad['delta_avg_ret_net'] > 0.0;
        return [
            'checks' => $checks,
            'full_is_delta_avg_ret_net' => $full['delta_avg_ret_net'] ?? null,
            'full_is_delta_p10_ret_net' => $full['delta_p10_ret_net'] ?? null,
            'full_is_delta_month_avg_ret_net_min' => $full['delta_month_avg_ret_net_min'] ?? null,
            'full_is_delta_bad_month_like_count' => $full['delta_bad_month_like_count'] ?? null,
            'bad_month_stress_delta_avg_ret_net' => $bad['delta_avg_ret_net'] ?? null,
            'all_corroborating_checks_passed' => ! in_array(false, $checks, true),
            'result' => ! in_array(false, $checks, true) ? 'PASS' : 'REQUIRES_EVIDENCE_EXPANSION',
        ];
    }

    private function guardAndSafetyRecheck(array $c45): array
    {
        $coverage = (array) ($c45['month_coverage_validation'] ?? []);
        $branch = (array) ($c45['branch_concentration_validation'] ?? []);
        $ticker = (array) ($c45['ticker_concentration_validation'] ?? []);
        $safety = (array) ($c45['candidate_safety_audit'] ?? []);
        $checks = [
            'month_coverage_passed' => ($coverage['result'] ?? null) === 'PASS',
            'zero_pick_months_is_zero' => (int) ($coverage['target_zero_pick_month_count'] ?? -1) === 0,
            'minimum_monthly_rows_preserved' => (int) ($coverage['target_min_selected_rows_per_month'] ?? 0) >= (int) ($coverage['required_min_selected_rows_per_month'] ?? PHP_INT_MAX),
            'branch_concentration_passed' => ($branch['result'] ?? null) === 'PASS',
            'branch_count_preserved' => (int) ($branch['target_branch_count'] ?? 0) >= 2,
            'ticker_concentration_passed' => ($ticker['result'] ?? null) === 'PASS',
            'selection_reconstruction_passed' => ($safety['selection_reconstructed_from_locked_c44_rule'] ?? false) === true,
            'fixed_monthly_quota_preserved' => ($safety['fixed_monthly_quota_preserved'] ?? false) === true,
            'return_not_used_for_selection' => $this->strictFalse($safety['return_used_for_selection'] ?? true),
            'future_path_not_used_for_selection' => $this->strictFalse($safety['future_path_used_for_selection'] ?? true),
            'oos_not_used_for_tuning' => $this->strictFalse($safety['oos_data_used_for_tuning'] ?? true),
            'oos_proof_not_executed' => $this->strictFalse($safety['oos_proof_executed'] ?? true),
            'candidate_non_production' => ($safety['candidate_is_not_production'] ?? false) === true && $this->strictFalse($safety['production_ready'] ?? true),
        ];
        return [
            'checks' => $checks,
            'months_covered' => $coverage['target_months_covered'] ?? null,
            'zero_pick_months' => $coverage['target_zero_pick_month_count'] ?? null,
            'min_selected_rows_per_month' => $coverage['target_min_selected_rows_per_month'] ?? null,
            'top_branch_share' => $branch['target_top_branch_share'] ?? null,
            'top_ticker_share' => $ticker['target_top_ticker_share'] ?? null,
            'all_guards_and_safety_checks_passed' => ! in_array(false, $checks, true),
            'result' => ! in_array(false, $checks, true) ? 'PASS' : 'REQUIRES_EVIDENCE_EXPANSION',
        ];
    }

    private function priorWarningGapResolution(array $c45): array
    {
        $full = (array) ($c45['full_is_validation']['comparison_vs_baseline'] ?? []);
        $bad = (array) ($c45['bad_month_like_stress_validation']['comparison_vs_baseline'] ?? []);
        $normal = (array) ($c45['non_bad_month_validation']['comparison_vs_baseline'] ?? []);
        $checks = [
            'c41_pre_trade_quality_field_gap_resolved_by_c43_c44' => ($c45['candidate_safety_audit']['selection_uses_signal_date_market_index_roc20'] ?? false) === true,
            'c41_fixed_quota_guard_preserved' => ($c45['candidate_safety_audit']['fixed_monthly_quota_preserved'] ?? false) === true,
            'prior_bad_month_cluster_improved' => $this->num($bad['delta_avg_ret_net'] ?? null) !== null && (float) $bad['delta_avg_ret_net'] > 0.0,
            'full_is_bad_month_count_reduced' => (int) ($full['delta_bad_month_like_count'] ?? 0) < 0,
            'normal_month_warning_does_not_create_new_bad_month' => (int) ($normal['delta_bad_month_like_count'] ?? 1) <= 0,
        ];
        return [
            'checks' => $checks,
            'prior_c41_c42_warning_gap_resolved' => ! in_array(false, $checks, true),
            'resolution_interpretation' => 'C43 supplied a safe signal-date market field, C44 used it inside the fixed quota, and C45 shows improved bad-month robustness without creating a new normal-month bad-like count.',
            'result' => ! in_array(false, $checks, true) ? 'PASS' : 'REQUIRES_EVIDENCE_EXPANSION',
        ];
    }

    private function evidenceExpansionRequirements(array $artifact): array
    {
        $requirements = [];
        $map = [
            'yearly_warning_review' => 'C46_REQ_YEARLY_WARNING_EVIDENCE_EXPANSION',
            'rolling_warning_review' => 'C46_REQ_ROLLING_WARNING_EVIDENCE_EXPANSION',
            'non_bad_month_warning_review' => 'C46_REQ_NORMAL_MONTH_WARNING_EVIDENCE_EXPANSION',
            'corroborating_pass_review' => 'C46_REQ_CORROBORATING_LAYER_REPAIR',
            'guard_and_safety_recheck' => 'C46_REQ_GUARD_OR_SAFETY_REPAIR',
            'prior_warning_gap_resolution' => 'C46_REQ_PRIOR_WARNING_GAP_RESOLUTION',
        ];
        foreach ($map as $section => $code) {
            if (($artifact[$section]['result'] ?? null) !== 'PASS') {
                $requirements[] = ['requirement_code' => $code, 'status' => 'REQUIRED_BEFORE_OOS', 'source_review' => $section];
            }
        }
        return $requirements;
    }

    private function reviewDecisionSummary(array $artifact): array
    {
        $checks = [
            'warning_inventory_complete' => ($artifact['warning_layer_inventory']['warning_inventory_complete'] ?? false) === true,
            'expected_warning_layers_only' => ($artifact['warning_layer_inventory']['warning_layer_names'] ?? []) === ['yearly', 'rolling', 'non_bad_month'],
            'yearly_warnings_bounded' => ($artifact['yearly_warning_review']['result'] ?? null) === 'PASS',
            'rolling_warnings_bounded' => ($artifact['rolling_warning_review']['result'] ?? null) === 'PASS',
            'normal_month_warning_bounded' => ($artifact['non_bad_month_warning_review']['result'] ?? null) === 'PASS',
            'corroborating_layers_passed' => ($artifact['corroborating_pass_review']['result'] ?? null) === 'PASS',
            'guards_and_safety_passed' => ($artifact['guard_and_safety_recheck']['result'] ?? null) === 'PASS',
            'prior_warning_gap_resolved' => ($artifact['prior_warning_gap_resolution']['result'] ?? null) === 'PASS',
            'no_evidence_expansion_requirement' => count($artifact['evidence_expansion_requirements'] ?? []) === 0,
        ];
        $accepted = ! in_array(false, $checks, true);
        return [
            'review_checks' => $checks,
            'all_review_checks_passed' => $accepted,
            'warning_review_result' => $accepted ? 'C46_WARNING_BOUNDED_AND_EXPLAINED' : 'C46_WARNING_REQUIRES_EVIDENCE_EXPANSION',
            'candidate_decision' => $accepted
                ? 'C46_LOCKED_C44_REFINEMENT_APPROVED_FOR_ONE_SHOT_OOS_PROOF'
                : 'C46_LOCKED_C44_REFINEMENT_REQUIRES_MORE_IS_EVIDENCE',
            'warning_acceptable_for_locked_oos_proof' => $accepted,
            'evidence_expansion_required' => ! $accepted,
            'direct_oos_proof_recommended' => $accepted,
            'oos_proof_unlocked' => $accepted,
            'oos_proof_executed' => false,
            'requires_c47_oos_proof' => $accepted,
            'requires_c47_is_evidence_expansion' => ! $accepted,
            'candidate_reselected' => false,
            'new_candidate_selected' => false,
            'candidate_is_not_production' => true,
            'production_ready' => false,
        ];
    }

    private function baseArtifact(string $path, string $expected, ?string $actual, $status, $conclusion, $next, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C46_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c45_artifact' => $path,
            'expected_c45_hash' => $expected,
            'actual_c45_hash' => $actual,
            'c45_hash_match' => $actual !== null && $actual === $expected,
            'expected_c45_file_sha1' => self::DEFAULT_C45_FILE_SHA1,
            'c45_status' => $status,
            'c45_diagnostic_conclusion' => $conclusion,
            'c45_next_step_recommendation' => $next,
            'is_period' => ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM, 'oos_reserved_to' => self::OOS_RESERVED_TO, 'oos_data_used_for_tuning' => false],
            'source_c45_summary' => [],
            'review_thresholds' => [],
            'warning_layer_inventory' => [],
            'yearly_warning_review' => [],
            'rolling_warning_review' => [],
            'non_bad_month_warning_review' => [],
            'corroborating_pass_review' => [],
            'guard_and_safety_recheck' => [],
            'prior_warning_gap_resolution' => [],
            'evidence_expansion_requirements' => [],
            'review_decision_summary' => [],
            'candidate_safety_audit' => [
                'review_only_no_candidate_reselection' => true,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'oos_proof_executed' => false,
                'candidate_is_not_production' => true,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_PENDING',
            'next_step_recommendation' => 'C46_PENDING',
            'diagnostics' => [['reason_code' => 'WS_BT_C46_IS_REVIEW_ONLY_NOTE', 'message' => 'C46 reviews C45 warning evidence and may authorize a future locked OOS proof, but never executes OOS itself.', 'fatal' => false]],
            'safety_boundaries' => [
                'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS' => true,
                'C45_ARTIFACT_HASH_LOCK' => true,
                'IS_ONLY_WARNING_REVIEW' => true,
                'EVIDENCE_EXPANSION_DECISION_ONLY' => true,
                'NO_OOS_TUNING' => true,
                'OOS_PROOF_NOT_EXECUTED' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C45_ARTIFACT_MUTATION' => true,
                'NO_C46_CANDIDATE_RESELECTION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
            ],
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function sourceC45Summary(array $c45): array
    {
        return [
            'target_candidate_code' => $c45['validation_target']['target_candidate_code'] ?? null,
            'overall_anti_overfit_result' => $c45['validation_summary']['overall_anti_overfit_result'] ?? null,
            'passed_layers' => $c45['validation_summary']['passed_layers'] ?? null,
            'warning_layers' => $c45['validation_summary']['warning_layers'] ?? null,
            'failed_layers' => $c45['validation_summary']['failed_layers'] ?? null,
            'not_evaluable_layers' => $c45['validation_summary']['not_evaluable_layers'] ?? null,
            'rolling_slice_count' => $c45['rolling_window_validation']['slice_count'] ?? null,
            'rolling_warning_count' => $c45['rolling_window_validation']['warning_count'] ?? null,
            'rolling_fail_count' => $c45['rolling_window_validation']['fail_count'] ?? null,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'requires_human_review_before_any_oos_step' => (bool) ($c45['validation_summary']['requires_human_review_before_any_oos_step'] ?? false),
            'candidate_is_not_production' => true,
            'production_ready' => false,
        ];
    }

    private function yearlyClassification(?float $avg, ?float $monthMin): string
    {
        if ($avg !== null && $avg < 0.0 && ($monthMin === null || $monthMin >= 0.0)) {
            return 'BOUNDED_AVERAGE_TRADEOFF_WITH_DOWNSIDE_PRESERVED';
        }
        if ($monthMin !== null && $monthMin < 0.0 && ($avg === null || $avg >= 0.0)) {
            return 'BOUNDED_WORST_MONTH_TRADEOFF_WITH_AVERAGE_IMPROVED';
        }
        return 'MIXED_BOUNDED_YEARLY_TRADEOFF';
    }

    private function countSlicesByResult(array $slices, string $result): int
    {
        return count(array_filter($slices, function ($slice) use ($result): bool { return is_array($slice) && ($slice['result'] ?? null) === $result; }));
    }

    private function nonNegative($value): bool
    {
        $value = $this->num($value);
        return $value !== null && $value >= 0.0;
    }

    private function num($value): ?float
    {
        return $value === null || $value === '' || ! is_numeric($value) ? null : (float) $value;
    }

    private function strictFalse($value): bool
    {
        return $value === false || $value === 0 || $value === '0';
    }

    private function validPeriod(string $from, string $to): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0;
    }

    private function touchesOos(string $from, string $to): bool
    {
        return strcmp($to, self::OOS_RESERVED_FROM) >= 0 || strcmp($from, self::OOS_RESERVED_FROM) >= 0;
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = 'C46_INPUT_LOCK_OR_BOUNDARY_BLOCKED';
        $artifact['next_step_recommendation'] = 'C46_BLOCKED_UNTIL_INPUT_VALIDATED';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') {
            $this->writeArtifact($output, $artifact, true);
        }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! ($write['ok'] ?? false)) {
            $artifact['status'] = 'C46_OPERATOR_VALIDATION_REQUIRED';
            return $this->result($artifact, $output, (string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), (string) ($write['message'] ?? 'Unable to write C46 artifact.'));
        }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return [
            'status' => $artifact['status'],
            'reason_code' => $reason,
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'] ?? null,
            'production_ready' => 0,
            'expected_c45_hash' => $artifact['expected_c45_hash'] ?? null,
            'actual_c45_hash' => $artifact['actual_c45_hash'] ?? null,
            'c45_hash_match' => $artifact['c45_hash_match'] ?? false,
            'c45_status' => $artifact['c45_status'] ?? null,
            'c45_diagnostic_conclusion' => $artifact['c45_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'warning_layer_inventory' => $artifact['warning_layer_inventory'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C46 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
