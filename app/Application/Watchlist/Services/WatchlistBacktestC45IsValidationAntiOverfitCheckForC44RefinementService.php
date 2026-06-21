<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService
{
    public const RUN_CODE = 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT';
    public const ARTIFACT_TYPE = 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT';
    public const DEFAULT_C44_ARTIFACT = 'storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json';
    public const DEFAULT_EXPECTED_C44_HASH = '606cd3109371b0d99419082daee18ff65f1cd99b';
    public const DEFAULT_C44_FILE_SHA1 = '4A9A7A915DD37278D9F44634C5D08006B310ED71';
    public const DEFAULT_SOURCE_EVIDENCE = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C44_STATUS = 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED';
    public const EXPECTED_C44_CONCLUSION = 'C44_GUARD_REFINEMENT_CANDIDATE_FORMED';
    public const EXPECTED_C44_NEXT_STEP = 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT';
    public const BASELINE_CANDIDATE_CODE = 'C44_BASELINE_C39_METADATA_G21_MONTHLY_QUOTA';
    public const TARGET_CANDIDATE_CODE = 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA';
    public const BRANCH_TOP_SHARE_LIMIT = 0.80;

    public function execute(
        string $c44Artifact = self::DEFAULT_C44_ARTIFACT,
        string $expectedC44Hash = self::DEFAULT_EXPECTED_C44_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($c44Artifact, $expectedC44Hash, null, null, null, null, $from, $to, $createdAt);
        if (! is_file($c44Artifact)) {
            return $this->blocked($artifact, 'C45_BLOCKED_MISSING_C44_ARTIFACT', 'WS_BT_C45_C44_ARTIFACT_MISSING', 'C45 requires the locked C44 candidate artifact.', $outputPath);
        }
        $c44 = json_decode((string) file_get_contents($c44Artifact), true);
        if (! is_array($c44)) {
            return $this->blocked($artifact, 'C45_BLOCKED_MISSING_C44_ARTIFACT', 'WS_BT_C45_C44_ARTIFACT_UNREADABLE', 'C44 artifact is not readable JSON.', $outputPath);
        }

        $actualHash = $this->stableHash($c44);
        $artifact = $this->baseArtifact($c44Artifact, $expectedC44Hash, $actualHash, $c44['status'] ?? null, $c44['diagnostic_conclusion'] ?? null, $c44['next_step_recommendation'] ?? null, $from, $to, $createdAt);
        $artifact['source_c44_summary'] = $this->sourceC44Summary($c44);

        if ($actualHash !== $expectedC44Hash) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_HASH_MISMATCH', 'WS_BT_C45_C44_ARTIFACT_HASH_MISMATCH', 'C44 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c44['status'] ?? null) !== self::EXPECTED_C44_STATUS) {
            return $this->blocked($artifact, 'C45_BLOCKED_UNEXPECTED_C44_STATUS', 'WS_BT_C45_UNEXPECTED_C44_STATUS', 'C45 requires a completed C44 artifact.', $outputPath);
        }
        if (($c44['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C44_CONCLUSION) {
            return $this->blocked($artifact, 'C45_BLOCKED_UNEXPECTED_C44_CONCLUSION', 'WS_BT_C45_UNEXPECTED_C44_CONCLUSION', 'C45 requires a formed C44 refinement candidate.', $outputPath);
        }
        if (($c44['next_step_recommendation'] ?? null) !== self::EXPECTED_C44_NEXT_STEP) {
            return $this->blocked($artifact, 'C45_BLOCKED_UNEXPECTED_C44_NEXT_STEP', 'WS_BT_C45_UNEXPECTED_C44_NEXT_STEP', 'C44 does not authorize C45 validation.', $outputPath);
        }
        if (! $this->strictFalse($c44['production_ready'] ?? false) || ! $this->strictFalse($c44['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_SAFETY_FLAGS_INVALID', 'WS_BT_C45_C44_SAFETY_FLAGS_INVALID', 'C45 requires C44 production_ready=false and oos_data_used_for_tuning=false.', $outputPath);
        }
        if (($c44['c44_decision_summary']['direct_oos_proof_recommended'] ?? true) !== false || ($c44['c44_decision_summary']['oos_proof_unlocked'] ?? true) !== false) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_OOS_FLAGS_INVALID', 'WS_BT_C45_C44_OOS_FLAGS_INVALID', 'C45 requires the C44 OOS recommendation and unlock flags to remain false.', $outputPath);
        }
        if (($c44['c44_decision_summary']['requires_c45_is_validation_and_anti_overfit_check'] ?? false) !== true || ($c44['candidate_summary']['best_candidate_requires_c45_validation'] ?? false) !== true) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_VALIDATION_NOT_REQUIRED', 'WS_BT_C45_C44_VALIDATION_NOT_REQUIRED', 'C44 does not require C45 validation.', $outputPath);
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C45_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C45_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C45 only accepts an IS period before the reserved OOS boundary.', $outputPath);
        }

        $bestCode = (string) ($c44['candidate_summary']['best_is_candidate_code'] ?? '');
        $best = $this->candidateByCode($c44, $bestCode);
        if ($bestCode !== self::TARGET_CANDIDATE_CODE || ! is_array($best)) {
            return $this->blocked($artifact, 'C45_BLOCKED_MISSING_C44_BEST_CANDIDATE', 'WS_BT_C45_C44_BEST_CANDIDATE_MISSING', 'C45 requires the locked C44 market-extension refinement candidate.', $outputPath);
        }
        if (($best['all_required_guards_passed'] ?? false) !== true || ($best['advancement_gate']['passed'] ?? false) !== true || ($best['candidate_is_not_production'] ?? false) !== true || ! $this->strictFalse($best['production_ready'] ?? false)) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_BEST_CANDIDATE_INVALID', 'WS_BT_C45_C44_BEST_CANDIDATE_INVALID', 'C44 best candidate did not preserve its guards, advancement gate, or non-production boundary.', $outputPath);
        }

        $sourcePath = trim((string) ($options['source_evidence_artifact'] ?? ($c44['source_evidence_summary']['source_evidence_artifact'] ?? self::DEFAULT_SOURCE_EVIDENCE)));
        if ($sourcePath === '' || ! is_file($sourcePath)) {
            return $this->blocked($artifact, 'C45_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C45_IS_EVIDENCE_MISSING', 'C45 requires the C44-linked IS evidence artifact.', $outputPath);
        }
        $source = json_decode((string) file_get_contents($sourcePath), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked($artifact, 'C45_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C45_IS_EVIDENCE_ROWS_MISSING', 'C45 requires pick_diagnostic_rows from the IS evidence artifact.', $outputPath);
        }

        $rows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21 = $this->targetRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16 = $this->targetRows($rows, 'G16', 'next_open_delay_after_close_signal');
        if (count($g21) === 0 || count($g16) === 0) {
            return $this->blocked($artifact, 'C45_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C45_BASELINE_ROWS_MISSING', 'C45 found no usable G16/G21 IS branch rows.', $outputPath);
        }
        $months = $this->uniqueMonths(array_merge($g16, $g21));
        $quota = (int) ($c44['source_evidence_summary']['monthly_g21_quota'] ?? 0);
        if ($quota <= 0) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_QUOTA_INVALID', 'WS_BT_C45_C44_QUOTA_INVALID', 'C45 requires the fixed monthly G21 quota recorded by C44.', $outputPath);
        }
        $sourceLoad = $this->loadMarketSources($g21, $options);
        if (count($sourceLoad['rows']) === 0) {
            return $this->blocked($artifact, 'C45_BLOCKED_PRE_TRADE_SOURCE_UNAVAILABLE', 'WS_BT_C45_PRE_TRADE_SOURCE_UNAVAILABLE', 'C45 could not reconstruct the signal-date market field used by C44.', $outputPath);
        }
        $g21 = $this->enrichRows($g21, $sourceLoad['rows']);
        $baselineG21 = $this->selectMonthlyQuota($g21, $months, $quota, 'METADATA');
        $targetG21 = $this->selectMonthlyQuota($g21, $months, $quota, 'MARKET_EXTENSION');
        $baselineRows = array_merge($g16, $baselineG21);
        $targetRows = array_merge($g16, $targetG21);

        $artifact['source_evidence_summary'] = [
            'source_evidence_artifact' => $sourcePath,
            'is_rows' => count($rows),
            'g21_rows' => count($g21),
            'g16_rows' => count($g16),
            'months' => count($months),
            'monthly_g21_quota' => $quota,
            'pre_trade_source_mode' => $sourceLoad['mode'],
            'pre_trade_source_row_count' => count($sourceLoad['rows']),
            'pre_trade_source_error' => $sourceLoad['error'],
            'baseline_selected_rows' => count($baselineRows),
            'target_selected_rows' => count($targetRows),
            'target_selected_g21_rows' => count($targetG21),
            'oos_data_used_for_tuning' => false,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
        ];
        $artifact['validation_target'] = [
            'baseline_candidate_code' => self::BASELINE_CANDIDATE_CODE,
            'target_candidate_code' => $bestCode,
            'target_selection_rule' => $best['selection_rule'] ?? null,
            'target_candidate_is_not_production' => true,
            'c44_selected_rows_match' => count($targetRows) === (int) ($best['selected_rows'] ?? -1),
            'c44_selected_g21_rows_match' => count($targetG21) === (int) ($best['selected_g21_rows'] ?? -1),
        ];
        if (! $artifact['validation_target']['c44_selected_rows_match'] || ! $artifact['validation_target']['c44_selected_g21_rows_match']) {
            return $this->blocked($artifact, 'C45_BLOCKED_C44_RECONSTRUCTION_MISMATCH', 'WS_BT_C45_C44_RECONSTRUCTION_MISMATCH', 'C45 could not exactly reconstruct the locked C44 target selection.', $outputPath);
        }

        $validation = $this->buildValidation($baselineRows, $targetRows, $months, $best);
        foreach ($validation as $key => $value) {
            $artifact[$key] = $value;
        }
        $overall = (string) ($artifact['validation_summary']['overall_anti_overfit_result'] ?? 'NOT_EVALUABLE');
        $artifact['status'] = 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT_COMPLETED';
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($overall);
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($overall);
        $artifact['diagnostics'][] = [
            'reason_code' => $artifact['diagnostic_conclusion'],
            'message' => 'C45 independently validated the locked C44 refinement across full IS, yearly, rolling, stress, concentration, coverage, and downside layers without OOS use.',
            'fatal' => false,
        ];

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function buildValidation(array $baselineRows, array $targetRows, array $months, array $best): array
    {
        $full = $this->sliceValidation('FULL_IS', $baselineRows, $targetRows, $months);

        $yearly = [];
        $byYear = [];
        foreach ($months as $month) {
            $byYear[substr($month, 0, 4)][] = $month;
        }
        foreach ($byYear as $year => $yearMonths) {
            $yearly[] = $this->sliceValidation($year, $this->filterMonths($baselineRows, $yearMonths), $this->filterMonths($targetRows, $yearMonths), $yearMonths);
        }
        $yearlyResult = $this->aggregateResults(array_column($yearly, 'result'));

        $rolling = [];
        foreach ([6, 9, 12] as $window) {
            for ($start = 0; $start + $window <= count($months); $start++) {
                $windowMonths = array_slice($months, $start, $window);
                $rolling[] = $this->sliceValidation(
                    'ROLLING_'.$window.'M_'.$windowMonths[0].'_TO_'.$windowMonths[count($windowMonths) - 1],
                    $this->filterMonths($baselineRows, $windowMonths),
                    $this->filterMonths($targetRows, $windowMonths),
                    $windowMonths
                );
            }
        }
        $rollingResult = $this->aggregateResults(array_column($rolling, 'result'));

        $badMonths = $this->badMonths($baselineRows, $months);
        $normalMonths = array_values(array_diff($months, $badMonths));
        $badStress = $this->sliceValidation('BASELINE_BAD_MONTH_LIKE_STRESS', $this->filterMonths($baselineRows, $badMonths), $this->filterMonths($targetRows, $badMonths), $badMonths);
        $normal = $this->sliceValidation('NON_BAD_MONTHS', $this->filterMonths($baselineRows, $normalMonths), $this->filterMonths($targetRows, $normalMonths), $normalMonths);
        $ticker = $this->tickerValidation($baselineRows, $targetRows);
        $branch = $this->branchValidation($baselineRows, $targetRows);
        $coverage = $this->coverageValidation($baselineRows, $targetRows, $months, $best);
        $downside = $this->downsideValidation($full);

        $layerResults = [
            'full_is' => $full['result'],
            'yearly' => $yearlyResult,
            'rolling' => $rollingResult,
            'bad_month_like_stress' => $badStress['result'],
            'non_bad_month' => $normal['result'],
            'ticker_concentration' => $ticker['result'],
            'branch_concentration' => $branch['result'],
            'month_coverage' => $coverage['result'],
            'downside_stability' => $downside['result'],
        ];
        $overall = $this->aggregateResults(array_values($layerResults));
        $counts = array_count_values(array_values($layerResults));

        return [
            'full_is_validation' => $full,
            'yearly_validation' => ['result' => $yearlyResult, 'slice_count' => count($yearly), 'slices' => $yearly],
            'rolling_window_validation' => [
                'result' => $rollingResult,
                'window_lengths_months' => [6, 9, 12],
                'slice_count' => count($rolling),
                'pass_count' => count(array_filter($rolling, function (array $row): bool { return $row['result'] === 'PASS'; })),
                'warning_count' => count(array_filter($rolling, function (array $row): bool { return $row['result'] === 'WARNING'; })),
                'fail_count' => count(array_filter($rolling, function (array $row): bool { return $row['result'] === 'FAIL'; })),
                'slices' => $rolling,
            ],
            'bad_month_like_stress_validation' => array_merge(['baseline_bad_months' => $badMonths], $badStress),
            'non_bad_month_validation' => array_merge(['normal_months' => $normalMonths], $normal),
            'ticker_concentration_validation' => $ticker,
            'branch_concentration_validation' => $branch,
            'month_coverage_validation' => $coverage,
            'downside_stability_validation' => $downside,
            'candidate_comparison_table' => $this->comparisonTable($full, $badStress, $normal),
            'validation_summary' => [
                'total_validation_layers' => count($layerResults),
                'passed_layers' => $counts['PASS'] ?? 0,
                'warning_layers' => $counts['WARNING'] ?? 0,
                'failed_layers' => $counts['FAIL'] ?? 0,
                'not_evaluable_layers' => $counts['NOT_EVALUABLE'] ?? 0,
                'layer_results' => $layerResults,
                'overall_anti_overfit_result' => $overall,
                'candidate_c45_decision' => $this->candidateDecision($overall),
                'candidate_c45_decision_reason' => $this->decisionReason($overall),
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'requires_human_review_before_any_oos_step' => true,
                'production_ready' => false,
            ],
            'anti_overfit_summary' => [
                'overall_anti_overfit_result' => $overall,
                'full_is_result' => $full['result'],
                'yearly_validation_result' => $yearlyResult,
                'rolling_validation_result' => $rollingResult,
                'worst_rolling_delta_avg_ret_net' => $this->minNested($rolling, 'comparison_vs_baseline', 'delta_avg_ret_net'),
                'worst_rolling_delta_month_avg_ret_net_min' => $this->minNested($rolling, 'comparison_vs_baseline', 'delta_month_avg_ret_net_min'),
                'baseline_bad_month_count' => count($badMonths),
                'target_full_is_bad_month_count' => $full['target_candidate']['bad_month_like_count'],
                'candidate_is_not_production' => true,
                'oos_data_used_for_tuning' => false,
            ],
            'candidate_safety_audit' => [
                'selection_reconstructed_from_locked_c44_rule' => true,
                'selection_uses_signal_date_market_index_roc20' => true,
                'fixed_monthly_quota_preserved' => true,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'oos_proof_executed' => false,
                'candidate_is_not_production' => true,
                'production_ready' => false,
                'passed' => true,
            ],
            'not_evaluable_reasons' => [],
        ];
    }

    private function sliceValidation(string $slice, array $baselineRows, array $targetRows, array $scopeMonths): array
    {
        $baseline = $this->metrics($baselineRows, $scopeMonths);
        $target = $this->metrics($targetRows, $scopeMonths);
        $comparison = $this->comparison($target, $baseline);
        $result = $this->sliceResult($target, $baseline, $comparison);
        return [
            'validation_slice' => $slice,
            'scope_months' => $scopeMonths,
            'baseline_candidate' => $baseline,
            'target_candidate' => $target,
            'comparison_vs_baseline' => $comparison,
            'result' => $result,
            'reason_code' => 'C45_SLICE_'.$result,
        ];
    }

    private function sliceResult(array $target, array $baseline, array $comparison): string
    {
        if (($target['selected_rows'] ?? 0) === 0 || ($baseline['selected_rows'] ?? 0) === 0) {
            return 'NOT_EVALUABLE';
        }
        $avgDelta = $this->num($comparison['delta_avg_ret_net'] ?? null);
        $p25Delta = $this->num($comparison['delta_p25_ret_net'] ?? null);
        $monthMinDelta = $this->num($comparison['delta_month_avg_ret_net_min'] ?? null);
        $badDelta = (int) ($comparison['delta_bad_month_like_count'] ?? 0);
        if ($avgDelta === null || $p25Delta === null || $monthMinDelta === null) {
            return 'NOT_EVALUABLE';
        }
        if ($avgDelta < -0.005 || $p25Delta < -0.005 || $monthMinDelta < -0.010 || $badDelta > 1) {
            return 'FAIL';
        }
        if ($avgDelta < 0.0 || $p25Delta < -0.002 || $monthMinDelta < 0.0 || $badDelta > 0) {
            return 'WARNING';
        }
        return 'PASS';
    }

    private function tickerValidation(array $baselineRows, array $targetRows): array
    {
        $baseline = $this->concentration($baselineRows, 'ticker');
        $target = $this->concentration($targetRows, 'ticker');
        $delta = $this->delta($target, $baseline);
        $result = $target === null || $baseline === null ? 'NOT_EVALUABLE' : ($target > $baseline + 0.05 ? 'FAIL' : ($target > $baseline + 0.02 ? 'WARNING' : 'PASS'));
        return ['validation_layer' => 'TICKER_CONCENTRATION', 'baseline_top_ticker_share' => $baseline, 'target_top_ticker_share' => $target, 'delta_top_ticker_share' => $delta, 'target_unique_tickers' => $this->uniqueCount($targetRows, 'ticker'), 'result' => $result, 'reason_code' => 'C45_TICKER_CONCENTRATION_'.$result];
    }

    private function branchValidation(array $baselineRows, array $targetRows): array
    {
        $baseline = $this->concentration($baselineRows, 'selected_source_code');
        $target = $this->concentration($targetRows, 'selected_source_code');
        $branches = $this->uniqueCount($targetRows, 'selected_source_code');
        $result = $target === null ? 'NOT_EVALUABLE' : ($target > self::BRANCH_TOP_SHARE_LIMIT || $branches < 2 ? 'FAIL' : ($target > 0.795 ? 'WARNING' : 'PASS'));
        return ['validation_layer' => 'BRANCH_CONCENTRATION', 'max_top_branch_share' => self::BRANCH_TOP_SHARE_LIMIT, 'baseline_top_branch_share' => $baseline, 'target_top_branch_share' => $target, 'target_branch_count' => $branches, 'target_distribution' => $this->distribution($targetRows, 'selected_source_code'), 'result' => $result, 'reason_code' => 'C45_BRANCH_CONCENTRATION_'.$result];
    }

    private function coverageValidation(array $baselineRows, array $targetRows, array $months, array $best): array
    {
        $baselineCounts = $this->monthCounts($baselineRows);
        $targetCounts = $this->monthCounts($targetRows);
        $zero = array_values(array_diff($months, array_keys($targetCounts)));
        $min = count($targetCounts) > 0 ? min($targetCounts) : 0;
        $requiredMin = (int) ($best['minimum_monthly_rows_guard']['required_minimum'] ?? 0);
        $pass = count($zero) === 0 && count($targetCounts) === count($months) && $min >= $requiredMin;
        return [
            'validation_layer' => 'MONTH_COVERAGE',
            'required_months' => count($months),
            'baseline_months_covered' => count($baselineCounts),
            'target_months_covered' => count($targetCounts),
            'target_zero_pick_months' => $zero,
            'target_zero_pick_month_count' => count($zero),
            'required_min_selected_rows_per_month' => $requiredMin,
            'target_min_selected_rows_per_month' => $min,
            'target_median_selected_rows_per_month' => $this->median(array_values($targetCounts)),
            'result' => $pass ? 'PASS' : 'FAIL',
            'reason_code' => $pass ? 'C45_MONTH_COVERAGE_PASS' : 'C45_MONTH_COVERAGE_FAIL',
        ];
    }

    private function downsideValidation(array $full): array
    {
        $comparison = $full['comparison_vs_baseline'] ?? [];
        $p10 = $this->num($comparison['delta_p10_ret_net'] ?? null);
        $monthMin = $this->num($comparison['delta_month_avg_ret_net_min'] ?? null);
        if ($p10 === null || $monthMin === null) {
            $result = 'NOT_EVALUABLE';
        } elseif ($p10 < -0.005 || $monthMin < -0.010) {
            $result = 'FAIL';
        } elseif ($p10 < -0.002 || $monthMin < -0.005) {
            $result = 'WARNING';
        } else {
            $result = 'PASS';
        }
        return ['validation_layer' => 'DOWNSIDE_STABILITY', 'delta_p10_ret_net' => $p10, 'delta_p25_ret_net' => $comparison['delta_p25_ret_net'] ?? null, 'delta_month_avg_ret_net_min' => $monthMin, 'delta_loss_concentration' => $comparison['delta_loss_concentration'] ?? null, 'result' => $result, 'reason_code' => 'C45_DOWNSIDE_STABILITY_'.$result];
    }

    private function metrics(array $rows, array $scopeMonths): array
    {
        $values = [];
        $byMonth = [];
        $losses = 0;
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) {
                continue;
            }
            $values[] = $value;
            if ($value < 0.0) {
                $losses++;
            }
            $month = $this->rowMonth($row);
            $byMonth[$month][] = $value;
        }
        sort($values);
        $count = count($values);
        $monthAvgs = [];
        $monthWins = [];
        $bad = 0;
        foreach ($scopeMonths as $month) {
            $monthValues = $byMonth[$month] ?? [];
            if (count($monthValues) === 0) {
                continue;
            }
            $avg = array_sum($monthValues) / count($monthValues);
            $wins = $this->winCount($monthValues) / count($monthValues);
            $monthAvgs[] = $avg;
            $monthWins[] = $wins;
            if ($avg < 0.0 || $wins <= 0.0) {
                $bad++;
            }
        }
        return [
            'selected_rows' => $count,
            'avg_ret_net' => $count > 0 ? array_sum($values) / $count : null,
            'median_ret_net' => $this->percentile($values, 0.50),
            'p25_ret_net' => $this->percentile($values, 0.25),
            'p10_ret_net' => $this->percentile($values, 0.10),
            'win_rate' => $count > 0 ? $this->winCount($values) / $count : null,
            'month_win_rate_min' => count($monthWins) > 0 ? min($monthWins) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => $bad,
            'loss_concentration' => $count > 0 ? $losses / $count : null,
            'months_required' => count($scopeMonths),
            'months_covered' => count(array_intersect($scopeMonths, array_keys($byMonth))),
            'zero_pick_months' => array_values(array_diff($scopeMonths, array_keys($byMonth))),
            'min_selected_rows_per_month' => count($byMonth) > 0 ? min(array_map('count', $byMonth)) : 0,
        ];
    }

    private function comparison(array $target, array $baseline): array
    {
        $out = [];
        foreach (['avg_ret_net', 'median_ret_net', 'p25_ret_net', 'p10_ret_net', 'win_rate', 'month_win_rate_min', 'month_avg_ret_net_min', 'loss_concentration'] as $field) {
            $out['delta_'.$field] = $this->delta($target[$field] ?? null, $baseline[$field] ?? null);
        }
        $out['delta_bad_month_like_count'] = (int) ($target['bad_month_like_count'] ?? 0) - (int) ($baseline['bad_month_like_count'] ?? 0);
        $out['delta_selected_rows'] = (int) ($target['selected_rows'] ?? 0) - (int) ($baseline['selected_rows'] ?? 0);
        return $out;
    }

    private function comparisonTable(array $full, array $bad, array $normal): array
    {
        return [
            ['scope' => 'FULL_IS', 'baseline' => $full['baseline_candidate'], 'target' => $full['target_candidate'], 'comparison' => $full['comparison_vs_baseline'], 'result' => $full['result']],
            ['scope' => 'BASELINE_BAD_MONTH_LIKE_STRESS', 'baseline' => $bad['baseline_candidate'], 'target' => $bad['target_candidate'], 'comparison' => $bad['comparison_vs_baseline'], 'result' => $bad['result']],
            ['scope' => 'NON_BAD_MONTHS', 'baseline' => $normal['baseline_candidate'], 'target' => $normal['target_candidate'], 'comparison' => $normal['comparison_vs_baseline'], 'result' => $normal['result']],
        ];
    }

    private function baseArtifact(string $path, string $expected, ?string $actual, $status, $conclusion, $next, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C45_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c44_artifact' => $path,
            'expected_c44_hash' => $expected,
            'actual_c44_hash' => $actual,
            'c44_hash_match' => $actual !== null && $actual === $expected,
            'expected_c44_file_sha1' => self::DEFAULT_C44_FILE_SHA1,
            'c44_status' => $status,
            'c44_diagnostic_conclusion' => $conclusion,
            'c44_next_step_recommendation' => $next,
            'is_period' => ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM, 'oos_reserved_to' => self::OOS_RESERVED_TO, 'oos_data_used_for_tuning' => false],
            'source_c44_summary' => [],
            'source_evidence_summary' => [],
            'validation_target' => [],
            'validation_summary' => [],
            'full_is_validation' => [],
            'yearly_validation' => [],
            'rolling_window_validation' => [],
            'bad_month_like_stress_validation' => [],
            'non_bad_month_validation' => [],
            'ticker_concentration_validation' => [],
            'branch_concentration_validation' => [],
            'month_coverage_validation' => [],
            'downside_stability_validation' => [],
            'candidate_comparison_table' => [],
            'anti_overfit_summary' => [],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_PENDING',
            'next_step_recommendation' => 'C45_PENDING',
            'diagnostics' => [['reason_code' => 'WS_BT_C45_IS_ONLY_NOTE', 'message' => 'C45 validates the locked C44 refinement on IS only and cannot unlock OOS or production.', 'fatal' => false]],
            'safety_boundaries' => [
                'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT' => true,
                'C44_ARTIFACT_HASH_LOCK' => true,
                'IS_ONLY_VALIDATION' => true,
                'ANTI_OVERFIT_VALIDATION' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C44_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'HUMAN_REVIEW_REQUIRED_BEFORE_OOS' => true,
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

    private function sourceC44Summary(array $c44): array
    {
        return [
            'candidate_formed' => (bool) ($c44['candidate_summary']['candidate_formed'] ?? false),
            'best_is_candidate_code' => $c44['candidate_summary']['best_is_candidate_code'] ?? null,
            'best_is_candidate_is_not_production' => (bool) ($c44['candidate_summary']['best_is_candidate_is_not_production'] ?? false),
            'best_candidate_requires_c45_validation' => (bool) ($c44['candidate_summary']['best_candidate_requires_c45_validation'] ?? false),
            'advancement_gate_pass_count' => $c44['candidate_summary']['advancement_gate_pass_count'] ?? null,
            'c39_guard_coverage_preserved' => (bool) ($c44['c44_decision_summary']['c39_guard_coverage_preserved'] ?? false),
            'c39_branch_diversification_preserved' => (bool) ($c44['c44_decision_summary']['c39_branch_diversification_preserved'] ?? false),
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function loadMarketSources(array $rows, array $options): array
    {
        if (array_key_exists('pre_trade_source_rows', $options)) {
            $map = [];
            foreach ((array) $options['pre_trade_source_rows'] as $row) {
                if (is_array($row)) {
                    $map[$this->joinKey($row)] = $row;
                    if (isset($row['trade_date'])) {
                        $map['DATE:'.(string) $row['trade_date']] = $row;
                    }
                }
            }
            return ['mode' => 'INJECTED_TEST_SOURCE', 'rows' => $map, 'error' => null];
        }
        try {
            if (! Schema::hasTable('market_benchmark_indicators')) {
                return ['mode' => 'SOURCE_NOT_MIGRATED', 'rows' => [], 'error' => 'market_benchmark_indicators unavailable'];
            }
            $dates = [];
            foreach ($rows as $row) {
                $dates[(string) ($row['trade_date'] ?? '')] = true;
            }
            unset($dates['']);
            $map = [];
            foreach (array_chunk(array_keys($dates), 200) as $chunk) {
                $dbRows = DB::table('market_benchmark_indicators')->where('benchmark_code', 'IHSG')->whereIn('trade_date', $chunk)->select(['trade_date', 'roc_20'])->get();
                foreach ($dbRows as $dbRow) {
                    $row = (array) $dbRow;
                    $row['market_index_roc20'] = $row['roc_20'] ?? null;
                    $map['DATE:'.(string) $row['trade_date']] = $row;
                }
            }
            return ['mode' => 'DATABASE_AS_OF_SIGNAL_DATE_JOIN', 'rows' => $map, 'error' => null];
        } catch (Throwable $e) {
            return ['mode' => 'DATABASE_JOIN_FAILED', 'rows' => [], 'error' => get_class($e).': '.$e->getMessage()];
        }
    }

    private function enrichRows(array $rows, array $sources): array
    {
        return array_map(function (array $row) use ($sources): array {
            $source = $sources[$this->joinKey($row)] ?? $sources['DATE:'.(string) ($row['trade_date'] ?? '')] ?? [];
            return array_merge($row, $source);
        }, $rows);
    }

    private function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array
    {
        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[$this->rowMonth($row)][] = $row;
        }
        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[$month] ?? [];
            usort($monthRows, function (array $a, array $b) use ($ranking): int {
                $quality = strcmp($this->qualityKey($a, $ranking), $this->qualityKey($b, $ranking));
                return $quality !== 0 ? $quality : strcmp($this->metadataKey($a), $this->metadataKey($b));
            });
            $selected = array_merge($selected, array_slice($monthRows, 0, $quota));
        }
        return $selected;
    }

    private function qualityKey(array $row, string $ranking): string
    {
        if ($ranking === 'METADATA') {
            return '';
        }
        $roc = $this->num($row['market_index_roc20'] ?? null);
        $rank = $roc === null ? 9 : ($roc < 0.0 ? 1 : ($roc < 0.10 ? 0 : 2));
        return sprintf('%02d|%020.10f', $rank, abs((float) ($roc ?? 999)));
    }

    private function metadataKey(array $row): string
    {
        return implode('|', [$this->rowMonth($row), (string) ($row['trade_date'] ?? ''), (string) ($row['ticker'] ?? ''), sprintf('%010d', (int) ($row['param_id'] ?? 0)), (string) ($row['row_code'] ?? '')]);
    }

    private function candidateByCode(array $c44, string $code): ?array
    {
        foreach (($c44['candidate_results'] ?? []) as $candidate) {
            if (is_array($candidate) && (string) ($candidate['candidate_code'] ?? '') === $code) {
                return $candidate;
            }
        }
        return null;
    }

    private function isRows(array $rows, string $from, string $to): array
    {
        return array_values(array_filter($rows, function ($row) use ($from, $to): bool {
            if (! is_array($row)) {
                return false;
            }
            $date = (string) ($row['trade_date'] ?? '');
            return $date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0
                && ! (($row['oos_executed'] ?? false) === true || (int) ($row['oos_executed'] ?? 0) === 1)
                && ! (($row['production_ready'] ?? false) === true || (int) ($row['production_ready'] ?? 0) === 1);
        }));
    }

    private function targetRows(array $rows, string $source, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($source, $bucket): bool {
            return (string) ($row['selected_source_code'] ?? '') === $source
                && (string) ($row['bucket_code'] ?? '') === $bucket
                && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
    }

    private function badMonths(array $rows, array $months): array
    {
        $bad = [];
        foreach ($months as $month) {
            $metrics = $this->metrics($this->filterMonths($rows, [$month]), [$month]);
            if (($metrics['avg_ret_net'] ?? 0.0) < 0.0 || ($metrics['win_rate'] ?? 1.0) <= 0.0) {
                $bad[] = $month;
            }
        }
        return $bad;
    }

    private function filterMonths(array $rows, array $months): array
    {
        $set = array_fill_keys($months, true);
        return array_values(array_filter($rows, function (array $row) use ($set): bool { return isset($set[$this->rowMonth($row)]); }));
    }

    private function uniqueMonths(array $rows): array
    {
        $months = [];
        foreach ($rows as $row) {
            $months[$this->rowMonth($row)] = true;
        }
        unset($months['']);
        $months = array_keys($months);
        sort($months);
        return $months;
    }

    private function monthCounts(array $rows): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $month = $this->rowMonth($row);
            $counts[$month] = ($counts[$month] ?? 0) + 1;
        }
        ksort($counts);
        return $counts;
    }

    private function rowMonth(array $row): string
    {
        return (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
    }

    private function joinKey(array $row): string
    {
        return (string) ($row['trade_date'] ?? '').'|'.(isset($row['ticker_id']) ? 'ID:'.$row['ticker_id'] : 'TICKER:'.strtoupper((string) ($row['ticker'] ?? '')));
    }

    private function distribution(array $rows, string $field): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? 'UNKNOWN');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        arsort($counts);
        $out = [];
        foreach ($counts as $value => $count) {
            $out[] = ['value' => $value, 'count' => $count, 'share' => count($rows) > 0 ? $count / count($rows) : null];
        }
        return $out;
    }

    private function concentration(array $rows, string $field): ?float
    {
        $distribution = $this->distribution($rows, $field);
        return $distribution[0]['share'] ?? null;
    }

    private function uniqueCount(array $rows, string $field): int
    {
        $values = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? '');
            if ($value !== '') {
                $values[$value] = true;
            }
        }
        return count($values);
    }

    private function aggregateResults(array $results): string
    {
        if (count($results) === 0) {
            return 'NOT_EVALUABLE';
        }
        if (in_array('FAIL', $results, true)) {
            return 'FAIL';
        }
        if (in_array('WARNING', $results, true)) {
            return 'WARNING';
        }
        if (in_array('NOT_EVALUABLE', $results, true)) {
            return in_array('PASS', $results, true) ? 'WARNING' : 'NOT_EVALUABLE';
        }
        return 'PASS';
    }

    private function candidateDecision(string $overall): string
    {
        if ($overall === 'PASS') {
            return 'C45_CANDIDATE_PASSED_IS_ANTI_OVERFIT_REQUIRES_REVIEW_BEFORE_OOS';
        }
        if ($overall === 'WARNING') {
            return 'C45_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS';
        }
        if ($overall === 'FAIL') {
            return 'C45_CANDIDATE_FAILED_IS_ANTI_OVERFIT';
        }
        return 'C45_CANDIDATE_NOT_EVALUABLE';
    }

    private function decisionReason(string $overall): string
    {
        if ($overall === 'PASS') {
            return 'All independent IS validation layers passed; OOS remains locked pending explicit review.';
        }
        if ($overall === 'WARNING') {
            return 'No material IS failure was found, but at least one stability layer requires review before any OOS step.';
        }
        if ($overall === 'FAIL') {
            return 'At least one independent IS stability layer failed; the C44 refinement must not advance to OOS.';
        }
        return 'Available IS evidence was insufficient for a complete anti-overfit decision.';
    }

    private function diagnosticConclusion(string $overall): string
    {
        if ($overall === 'PASS') {
            return 'C45_C44_REFINEMENT_PASSED_IS_VALIDATION_REVIEW_REQUIRED';
        }
        if ($overall === 'WARNING') {
            return 'C45_C44_REFINEMENT_WARNING_REQUIRES_REVIEW_BEFORE_OOS';
        }
        if ($overall === 'FAIL') {
            return 'C45_C44_REFINEMENT_FAILED_IS_ANTI_OVERFIT_CHECK';
        }
        return 'C45_C44_REFINEMENT_NOT_EVALUABLE';
    }

    private function nextStepRecommendation(string $overall): string
    {
        if ($overall === 'PASS' || $overall === 'WARNING') {
            return 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
        }
        if ($overall === 'FAIL') {
            return 'C46_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC';
        }
        return 'C46_IS_EVIDENCE_EXPANSION_DIAGNOSTIC';
    }

    private function minNested(array $rows, string $section, string $field): ?float
    {
        $values = [];
        foreach ($rows as $row) {
            $value = $this->num($row[$section][$field] ?? null);
            if ($value !== null) {
                $values[] = $value;
            }
        }
        return count($values) > 0 ? min($values) : null;
    }

    private function winCount(array $values): int
    {
        return count(array_filter($values, function ($value): bool { return (float) $value > 0.0; }));
    }

    private function percentile(array $sorted, float $percentile): ?float
    {
        $count = count($sorted);
        if ($count === 0) {
            return null;
        }
        sort($sorted);
        $position = ($count - 1) * $percentile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        return $lower === $upper ? (float) $sorted[$lower] : (float) $sorted[$lower] + (((float) $sorted[$upper] - (float) $sorted[$lower]) * ($position - $lower));
    }

    private function median(array $values): ?float
    {
        return $this->percentile($values, 0.50);
    }

    private function delta($target, $baseline): ?float
    {
        $target = $this->num($target);
        $baseline = $this->num($baseline);
        return $target === null || $baseline === null ? null : $target - $baseline;
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
        $artifact['diagnostic_conclusion'] = 'C45_INPUT_LOCK_OR_BOUNDARY_BLOCKED';
        $artifact['next_step_recommendation'] = 'C45_BLOCKED_UNTIL_INPUT_VALIDATED';
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
            $artifact['status'] = 'C45_OPERATOR_VALIDATION_REQUIRED';
            return $this->result($artifact, $output, (string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), (string) ($write['message'] ?? 'Unable to write C45 artifact.'));
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
            'expected_c44_hash' => $artifact['expected_c44_hash'] ?? null,
            'actual_c44_hash' => $artifact['actual_c44_hash'] ?? null,
            'c44_hash_match' => $artifact['c44_hash_match'] ?? false,
            'c44_status' => $artifact['c44_status'] ?? null,
            'c44_diagnostic_conclusion' => $artifact['c44_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'validation_summary' => $artifact['validation_summary'] ?? [],
            'anti_overfit_summary' => $artifact['anti_overfit_summary'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C45 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
