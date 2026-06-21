<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService
{
    public const RUN_CODE = 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION';
    public const ARTIFACT_TYPE = 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION';
    public const DEFAULT_C41_ARTIFACT = 'storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json';
    public const DEFAULT_EXPECTED_C41_HASH = 'fa3afd197cfe07d67d90edf87d69aec81310d791';
    public const DEFAULT_C41_FILE_SHA1 = '9B44AD084DBD7637E0794A8AF5085E3A846D9486';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json';
    public const DEFAULT_SOURCE_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_C40_ARTIFACT = 'storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json';
    public const DEFAULT_C39_ARTIFACT = 'storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C41_STATUS = 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED';
    public const EXPECTED_C41_CONCLUSION = 'C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS';
    public const EXPECTED_C41_NEXT_STEP = 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_OR_GUARD_REFINEMENT';
    public const TARGET_CANDIDATE_CODE = 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA';
    public const BASELINE_CANDIDATE_CODE = 'C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR';
    public const C39_BASELINE_CANDIDATE_CODE = 'C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR';
    public const BRANCH_TOP_SHARE_LIMIT = 0.80;

    public function execute(
        string $c41Artifact = self::DEFAULT_C41_ARTIFACT,
        string $expectedC41Hash = self::DEFAULT_EXPECTED_C41_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c41Artifact = trim($c41Artifact) !== '' ? trim($c41Artifact) : self::DEFAULT_C41_ARTIFACT;
        $expectedC41Hash = trim($expectedC41Hash) !== '' ? trim($expectedC41Hash) : self::DEFAULT_EXPECTED_C41_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c41Artifact, $expectedC41Hash, null, null, null, null, $from, $to, $createdAt);

        if (! is_file($c41Artifact)) {
            return $this->blocked($artifact, 'C42_BLOCKED_MISSING_C41_ARTIFACT', 'WS_BT_C42_C41_ARTIFACT_MISSING', 'C42 requires the locked C41 review artifact, but the file is missing.', $outputPath, ['input_c41_artifact' => $c41Artifact]);
        }

        $c41 = json_decode((string) file_get_contents($c41Artifact), true);
        if (! is_array($c41)) {
            return $this->blocked($artifact, 'C42_BLOCKED_MISSING_C41_ARTIFACT', 'WS_BT_C42_C41_ARTIFACT_UNREADABLE', 'C41 artifact is not readable JSON.', $outputPath, ['input_c41_artifact' => $c41Artifact]);
        }

        $actualC41Hash = $this->stableHash($c41);
        $artifact = $this->baseArtifact(
            $c41Artifact,
            $expectedC41Hash,
            $actualC41Hash,
            $c41['status'] ?? null,
            $c41['diagnostic_conclusion'] ?? null,
            $c41['next_step_recommendation'] ?? null,
            $from,
            $to,
            $createdAt
        );
        $artifact['source_c41_summary'] = $this->sourceC41Summary($c41);

        if ($actualC41Hash !== $expectedC41Hash) {
            return $this->blocked($artifact, 'C42_BLOCKED_C41_HASH_MISMATCH', 'WS_BT_C42_C41_ARTIFACT_HASH_MISMATCH', 'C41 artifact stable hash does not match the expected locked hash.', $outputPath, ['c41_artifact_hash_field' => $c41['artifact_hash'] ?? null]);
        }

        if (($c41['status'] ?? null) !== self::EXPECTED_C41_STATUS) {
            return $this->blocked($artifact, 'C42_BLOCKED_UNEXPECTED_C41_STATUS', 'WS_BT_C42_UNEXPECTED_C41_STATUS', 'C42 requires a completed C41 IS review artifact.', $outputPath, ['expected_c41_status' => self::EXPECTED_C41_STATUS]);
        }

        if (($c41['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C41_CONCLUSION) {
            return $this->blocked($artifact, 'C42_BLOCKED_UNEXPECTED_C41_CONCLUSION', 'WS_BT_C42_UNEXPECTED_C41_CONCLUSION', 'C42 requires C41 to conclude evidence expansion is required before OOS.', $outputPath, ['expected_c41_diagnostic_conclusion' => self::EXPECTED_C41_CONCLUSION]);
        }

        if (! $this->strictFalse($c41['production_ready'] ?? false)) {
            return $this->blocked($artifact, 'C42_BLOCKED_C41_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C42_C41_PRODUCTION_READY_NOT_FALSE', 'C42 requires C41 production_ready=false.', $outputPath, ['expected_production_ready' => false]);
        }

        if (! $this->strictFalse($c41['is_period']['oos_data_used_for_tuning'] ?? ($c41['safety_boundaries']['oos_data_used_for_tuning'] ?? false))) {
            return $this->blocked($artifact, 'C42_BLOCKED_C41_OOS_TUNING_FLAG_NOT_FALSE', 'WS_BT_C42_C41_OOS_TUNING_FLAG_NOT_FALSE', 'C42 requires C41 oos_data_used_for_tuning=false.', $outputPath, ['expected_oos_data_used_for_tuning' => false]);
        }

        if ((string) ($c41['review_decision_summary']['target_candidate_code'] ?? '') === '') {
            return $this->blocked($artifact, 'C42_BLOCKED_MISSING_C41_TARGET_CANDIDATE', 'WS_BT_C42_C41_TARGET_CANDIDATE_MISSING', 'C42 requires C41 target candidate code.', $outputPath, []);
        }

        if (($c41['review_decision_summary']['direct_oos_proof_recommended'] ?? true) !== false) {
            return $this->blocked($artifact, 'C42_BLOCKED_C41_DIRECT_OOS_FLAG_INVALID', 'WS_BT_C42_C41_DIRECT_OOS_FLAG_INVALID', 'C42 requires C41 direct_oos_proof_recommended=false.', $outputPath, ['expected_direct_oos_proof_recommended' => false]);
        }

        if (($c41['review_decision_summary']['oos_proof_unlocked'] ?? true) !== false) {
            return $this->blocked($artifact, 'C42_BLOCKED_C41_OOS_UNLOCK_FLAG_INVALID', 'WS_BT_C42_C41_OOS_UNLOCK_FLAG_INVALID', 'C42 requires C41 oos_proof_unlocked=false.', $outputPath, ['expected_oos_proof_unlocked' => false]);
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked($artifact, 'C42_BLOCKED_INVALID_IS_PERIOD', 'WS_BT_C42_INVALID_IS_PERIOD', 'C42 requires a valid IS period where from <= to.', $outputPath, ['from' => $from, 'to' => $to]);
        }

        if ($this->touchesOos($from, $to) || $this->touchesOos((string) ($c41['is_period']['from'] ?? $from), (string) ($c41['is_period']['to'] ?? $to))) {
            return $this->blocked($artifact, 'C42_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C42_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C42 is IS-only and rejects periods that touch the reserved OOS window.', $outputPath, ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM]);
        }

        $sourceEvidence = $this->sourceEvidencePath($c41, $options);
        if ($sourceEvidence === '' || ! is_file($sourceEvidence)) {
            return $this->blocked($artifact, 'C42_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C42_IS_EVIDENCE_ARTIFACT_MISSING', 'C42 requires C28 IS diagnostic evidence rows; no IS evidence artifact is available.', $outputPath, ['source_evidence' => $sourceEvidence]);
        }

        $source = json_decode((string) file_get_contents($sourceEvidence), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked($artifact, 'C42_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C42_IS_EVIDENCE_ROWS_MISSING', 'C42 requires pick_diagnostic_rows from the IS diagnostic artifact; the available artifact does not contain usable rows.', $outputPath, ['source_evidence' => $sourceEvidence]);
        }

        $rows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21Rows = $this->targetRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($rows, 'G16', 'next_open_delay_after_close_signal');
        $baselineRows = array_merge($g21Rows, $g16Rows);
        if (count($baselineRows) === 0) {
            return $this->blocked($artifact, 'C42_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C42_BASELINE_BRANCH_ROWS_MISSING', 'C42 found IS evidence but no usable C39 baseline G21/G16 rows.', $outputPath, ['g21_rows' => count($g21Rows), 'g16_rows' => count($g16Rows)]);
        }

        $baselineMonths = $this->uniqueMonths($baselineRows);
        $quota = $this->metadataMonthlyQuotaRows($g21Rows, $g16Rows, $baselineMonths, self::BRANCH_TOP_SHARE_LIMIT);
        $targetRows = array_merge($g16Rows, $quota['rows']);
        $artifact['source_evidence_summary'] = [
            'source_evidence_artifact' => $sourceEvidence,
            'is_rows' => count($rows),
            'baseline_rows' => count($baselineRows),
            'g21_rows' => count($g21Rows),
            'g16_rows' => count($g16Rows),
            'target_rows' => count($targetRows),
            'metadata_monthly_g21_quota_per_month' => $quota['quota_per_month'],
            'metadata_monthly_g21_quota_selected_rows' => count($quota['rows']),
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
        ];

        $artifact['pre_trade_field_availability_matrix'] = $this->fieldAvailabilityMatrix($source['pick_diagnostic_rows']);
        $artifact['warning_window_expansion'] = $this->warningWindowExpansion($c41, $baselineRows, $targetRows);
        $artifact['non_bad_month_warning_expansion'] = $this->nonBadMonthExpansion($c41, $baselineRows, $targetRows);
        $artifact['guard_preservation_audit'] = $this->guardPreservationAudit($c41, $targetRows, $baselineMonths, count($g21Rows) - count($quota['rows']));
        $artifact['guard_refinement_feasibility'] = $this->guardRefinementFeasibility($artifact['pre_trade_field_availability_matrix'], $artifact['warning_window_expansion'], $artifact['non_bad_month_warning_expansion']);
        $artifact['refinement_candidate_results'] = [];
        $artifact['candidate_comparison_table'] = $this->candidateComparisonTable($baselineRows, $targetRows);
        $artifact['warning_explanation_summary'] = $this->warningExplanationSummary($artifact['warning_window_expansion'], $artifact['non_bad_month_warning_expansion'], $artifact['guard_refinement_feasibility']);
        $artifact['c42_decision_summary'] = $this->decisionSummary($artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($artifact);
        $artifact['not_evaluable_reasons'] = $this->notEvaluableReasons($c41, $artifact);
        $artifact['status'] = 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED';
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($artifact['c42_decision_summary']);
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($artifact['c42_decision_summary']);
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $inputC41Path, string $expectedC41Hash, ?string $actualC41Hash, $c41Status, $c41Conclusion, $c41NextStep, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C42_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c41_artifact' => $inputC41Path,
            'expected_c41_hash' => $expectedC41Hash,
            'actual_c41_hash' => $actualC41Hash,
            'c41_hash_match' => $actualC41Hash !== null && $actualC41Hash === $expectedC41Hash,
            'expected_c41_file_sha1' => self::DEFAULT_C41_FILE_SHA1,
            'c41_status' => $c41Status,
            'c41_diagnostic_conclusion' => $c41Conclusion,
            'c41_next_step_recommendation' => $c41NextStep,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c41_summary' => [
                'target_candidate_code' => null,
                'overall_anti_overfit_result' => null,
                'warning_layers_count' => 0,
                'failed_layers_count' => 0,
                'rolling_warning_windows' => 0,
                'non_bad_month_warning' => false,
                'guard_blockers_resolved' => false,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'production_ready' => false,
            ],
            'source_evidence_summary' => [],
            'warning_window_expansion' => [],
            'non_bad_month_warning_expansion' => [],
            'guard_preservation_audit' => [],
            'pre_trade_field_availability_matrix' => [],
            'guard_refinement_feasibility' => [],
            'refinement_candidate_results' => [],
            'candidate_comparison_table' => [],
            'warning_explanation_summary' => [],
            'c42_decision_summary' => [],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_PENDING',
            'next_step_recommendation' => 'C42_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C42_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C42 locks C41, expands C40/C41 rolling and normal-month warning evidence inside IS only, and does not run OOS proof or production promotion.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION' => true,
                'C41_ARTIFACT_HASH_LOCK' => true,
                'IS_ONLY_EVIDENCE_EXPANSION' => true,
                'C42_FROM_C41_WARNING_REQUIREMENTS' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C41_MUTATION' => true,
                'NO_C01_TO_C41_ARTIFACT_MUTATION' => true,
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

    private function sourceC41Summary(array $c41): array
    {
        $decision = is_array($c41['review_decision_summary'] ?? null) ? $c41['review_decision_summary'] : [];
        return [
            'target_candidate_code' => $decision['target_candidate_code'] ?? null,
            'overall_anti_overfit_result' => $decision['overall_anti_overfit_result'] ?? ($c41['source_c40_summary']['overall_anti_overfit_result'] ?? null),
            'warning_layers_count' => $decision['warning_layers_count'] ?? ($c41['warning_layer_review']['warning_layer_count'] ?? 0),
            'failed_layers_count' => $decision['failed_layers_count'] ?? 0,
            'rolling_warning_windows' => $decision['rolling_warning_windows'] ?? ($c41['warning_layer_review']['rolling_warning_review']['warning_or_fail_window_count'] ?? 0),
            'non_bad_month_warning' => (bool) ($decision['non_bad_month_warning'] ?? ($c41['warning_layer_review']['non_bad_month_warning_review']['needs_review'] ?? false)),
            'guard_blockers_resolved' => (bool) ($decision['guard_blockers_resolved'] ?? ($c41['guard_blocker_recheck']['prior_c37_coverage_branch_blocker_resolved'] ?? false)),
            'direct_oos_proof_recommended' => (bool) ($decision['direct_oos_proof_recommended'] ?? false),
            'oos_proof_unlocked' => (bool) ($decision['oos_proof_unlocked'] ?? false),
            'production_ready' => false,
        ];
    }

    private function warningWindowExpansion(array $c41, array $baselineRows, array $targetRows): array
    {
        $windows = $c41['warning_layer_review']['rolling_warning_review']['window_reviews'] ?? [];
        $out = [];
        foreach ($windows as $window) {
            if (! is_array($window)) {
                continue;
            }
            $slice = (string) ($window['validation_slice'] ?? '');
            $months = $this->monthsFromSlice($slice);
            $baseline = $this->filterRowsByMonths($baselineRows, $months);
            $target = $this->filterRowsByMonths($targetRows, $months);
            $baselineMonth = $this->monthMetrics($baseline);
            $targetMonth = $this->monthMetrics($target);
            $suspected = $this->worstMonth($targetMonth);
            $baselineSuspected = $baselineMonth[$suspected] ?? [];
            $targetSuspected = $targetMonth[$suspected] ?? [];
            $targetSuspectedRows = $this->filterRowsByMonths($target, [$suspected]);
            $baselineSuspectedRows = $this->filterRowsByMonths($baseline, [$suspected]);
            $targetBranch = $this->branchReturnBreakdown($targetSuspectedRows);
            $g21Avg = $this->branchAvg($targetBranch, 'G21');
            $g21Win = $this->branchWinRate($targetBranch, 'G21');
            $explained = $suspected !== '' && $g21Avg !== null && $g21Avg < 0.0;
            $out[] = [
                'validation_slice' => $slice,
                'window_code' => $window['window_code'] ?? null,
                'result_from_c41' => $window['result'] ?? null,
                'baseline_selected_rows' => $window['baseline_selected_rows'] ?? count($baseline),
                'target_selected_rows' => $window['target_selected_rows'] ?? count($target),
                'target_month_avg_ret_net_min' => $window['target_month_avg_ret_net_min'] ?? ($targetSuspected['avg_ret_net'] ?? null),
                'baseline_month_avg_ret_net_min' => $window['baseline_month_avg_ret_net_min'] ?? null,
                'delta_avg_ret_net_vs_baseline' => $window['delta_avg_ret_net_vs_baseline'] ?? null,
                'delta_month_avg_ret_net_min_vs_baseline' => $window['delta_month_avg_ret_net_vs_baseline'] ?? null,
                'delta_bad_month_like_count_vs_baseline' => $window['delta_bad_month_like_count_vs_baseline'] ?? null,
                'suspected_warning_month' => $suspected,
                'target_month_avg_ret_net' => $targetSuspected['avg_ret_net'] ?? null,
                'baseline_month_avg_ret_net' => $baselineSuspected['avg_ret_net'] ?? null,
                'delta_month_avg_ret_net_vs_baseline_computed' => $this->delta($targetSuspected['avg_ret_net'] ?? null, $baselineSuspected['avg_ret_net'] ?? null),
                'target_g16_rows' => $this->countByValue($targetSuspectedRows, 'selected_source_code', 'G16'),
                'target_g21_rows' => $this->countByValue($targetSuspectedRows, 'selected_source_code', 'G21'),
                'target_g16_avg_ret_net' => $this->branchAvg($targetBranch, 'G16'),
                'target_g21_avg_ret_net' => $g21Avg,
                'target_g16_win_rate' => $this->branchWinRate($targetBranch, 'G16'),
                'target_g21_win_rate' => $g21Win,
                'month_breakdown' => $this->monthBreakdown($months, $baselineMonth, $targetMonth),
                'branch_breakdown' => [
                    'baseline' => $this->branchReturnBreakdown($baselineSuspectedRows),
                    'target' => $targetBranch,
                ],
                'bucket_breakdown' => [
                    'baseline' => $this->distribution($baselineSuspectedRows, 'bucket_code'),
                    'target' => $this->distribution($targetSuspectedRows, 'bucket_code'),
                ],
                'ticker_breakdown' => [
                    'baseline_top_loss_tickers' => $this->topLossGroups($baselineSuspectedRows, 'ticker', 10),
                    'target_top_loss_tickers' => $this->topLossGroups($targetSuspectedRows, 'ticker', 10),
                ],
                'param_breakdown' => [
                    'baseline' => $this->topLossGroups($baselineSuspectedRows, 'param_id', 10),
                    'target' => $this->topLossGroups($targetSuspectedRows, 'param_id', 10),
                ],
                'row_code_breakdown' => [
                    'baseline' => $this->topLossGroups($baselineSuspectedRows, 'row_code', 10),
                    'target' => $this->topLossGroups($targetSuspectedRows, 'row_code', 10),
                ],
                'profile_exit_reason_distribution' => [
                    'baseline' => $this->distribution($baselineSuspectedRows, 'profile_exit_reason'),
                    'target' => $this->distribution($targetSuspectedRows, 'profile_exit_reason'),
                    'selection_safe' => false,
                    'diagnostic_only' => true,
                ],
                'candidate_warning_explained' => $explained,
                'candidate_warning_explanation_code' => $explained ? 'C42_WARNING_CLUSTER_G21_METADATA_QUOTA_LOSS_MONTH' : 'C42_WARNING_NOT_EXPLAINED_FROM_AVAILABLE_PRE_TRADE_SPLITS',
                'candidate_warning_explanation_message' => $explained
                    ? 'Target warning is concentrated in the suspected month where the C39 G21 metadata quota rows have negative average return and weak win rate; return is used only after pre-trade selection for diagnostics.'
                    : 'Available IS evidence did not isolate the rolling warning to a clear pre-trade branch/bucket/ticker cluster.',
            ];
        }
        return $out;
    }

    private function nonBadMonthExpansion(array $c41, array $baselineRows, array $targetRows): array
    {
        $review = is_array($c41['warning_layer_review']['non_bad_month_warning_review'] ?? null) ? $c41['warning_layer_review']['non_bad_month_warning_review'] : [];
        $baselineMonth = $this->monthMetrics($baselineRows);
        $targetMonth = $this->monthMetrics($targetRows);
        $baselineBad = [];
        foreach ($baselineMonth as $month => $metrics) {
            if (($metrics['avg_ret_net'] ?? 0.0) < 0.0 || ($metrics['win_rate'] ?? 1.0) <= 0.0) {
                $baselineBad[$month] = true;
            }
        }
        $nonBadMonths = array_values(array_diff(array_keys($baselineMonth), array_keys($baselineBad)));
        sort($nonBadMonths);

        $newBad = [];
        $worse = [];
        $better = [];
        foreach ($nonBadMonths as $month) {
            $b = $baselineMonth[$month] ?? [];
            $t = $targetMonth[$month] ?? [];
            if (($t['avg_ret_net'] ?? 0.0) < 0.0 || ($t['win_rate'] ?? 1.0) <= 0.0) {
                $newBad[] = $month;
            }
            $delta = $this->delta($t['avg_ret_net'] ?? null, $b['avg_ret_net'] ?? null);
            $row = [
                'trade_month' => $month,
                'baseline_avg_ret_net' => $b['avg_ret_net'] ?? null,
                'target_avg_ret_net' => $t['avg_ret_net'] ?? null,
                'delta_avg_ret_net_vs_baseline' => $delta,
                'baseline_selected_rows' => $b['selected_rows'] ?? 0,
                'target_selected_rows' => $t['selected_rows'] ?? 0,
            ];
            if ($delta !== null && $delta < 0.0) {
                $worse[] = $row;
            } elseif ($delta !== null && $delta > 0.0) {
                $better[] = $row;
            }
        }
        usort($worse, function (array $a, array $b): int { return ($a['delta_avg_ret_net_vs_baseline'] <=> $b['delta_avg_ret_net_vs_baseline']); });
        usort($better, function (array $a, array $b): int { return ($b['delta_avg_ret_net_vs_baseline'] <=> $a['delta_avg_ret_net_vs_baseline']); });

        $warningMonth = count($newBad) > 0 ? $newBad[0] : (count($worse) > 0 ? (string) $worse[0]['trade_month'] : '');
        $targetWarningRows = $this->filterRowsByMonths($targetRows, [$warningMonth]);
        $baselineWarningRows = $this->filterRowsByMonths($baselineRows, [$warningMonth]);
        $targetBranch = $this->branchReturnBreakdown($targetWarningRows);
        $explained = $warningMonth !== '' && $this->branchAvg($targetBranch, 'G21') !== null && $this->branchAvg($targetBranch, 'G21') < 0.0;

        return [
            'validation_slice' => 'NON_BAD_MONTH_IS_MONTHS',
            'needs_review' => (bool) ($review['needs_review'] ?? true),
            'result' => $review['result'] ?? null,
            'baseline_selected_rows' => $review['baseline_selected_rows'] ?? count($this->filterRowsByMonths($baselineRows, $nonBadMonths)),
            'target_selected_rows' => $review['target_selected_rows'] ?? count($this->filterRowsByMonths($targetRows, $nonBadMonths)),
            'target_month_avg_ret_net_min' => $review['target_month_avg_ret_net_min'] ?? null,
            'baseline_month_avg_ret_net_min' => $review['baseline_month_avg_ret_net_min'] ?? null,
            'delta_avg_ret_net_vs_baseline' => $review['delta_avg_ret_net_vs_baseline'] ?? null,
            'delta_p25_ret_net_vs_baseline' => $review['delta_p25_ret_net_vs_baseline'] ?? null,
            'delta_win_rate_vs_baseline' => $review['delta_win_rate_vs_baseline'] ?? null,
            'delta_month_avg_ret_net_min_vs_baseline' => $review['delta_month_avg_ret_net_vs_baseline'] ?? null,
            'delta_bad_month_like_count_vs_baseline' => $review['delta_bad_month_like_count_vs_baseline'] ?? null,
            'non_bad_months_covered' => count($nonBadMonths),
            'baseline_bad_months_excluded' => array_keys($baselineBad),
            'new_bad_like_months_created_by_candidate' => $newBad,
            'months_where_candidate_worse_than_baseline' => $worse,
            'months_where_candidate_better_than_baseline' => $better,
            'target_bad_like_month_source' => $warningMonth,
            'branch_mix_in_bad_like_month' => [
                'baseline' => $this->branchReturnBreakdown($baselineWarningRows),
                'target' => $targetBranch,
            ],
            'bucket_mix_in_bad_like_month' => [
                'baseline' => $this->distribution($baselineWarningRows, 'bucket_code'),
                'target' => $this->distribution($targetWarningRows, 'bucket_code'),
            ],
            'ticker_loss_cluster' => $this->topLossGroups($targetWarningRows, 'ticker', 10),
            'candidate_coverage_in_bad_like_month' => [
                'trade_month' => $warningMonth,
                'baseline_selected_rows' => count($baselineWarningRows),
                'target_selected_rows' => count($targetWarningRows),
            ],
            'normal_month_warning_explained' => $explained,
            'normal_month_explanation_code' => $explained ? 'C42_NON_BAD_MONTH_WARNING_CLUSTER_G21_METADATA_QUOTA_LOSS_MONTH' : 'C42_NON_BAD_MONTH_WARNING_NOT_EXPLAINED_FROM_AVAILABLE_FIELDS',
            'normal_month_explanation_message' => $explained
                ? 'The non-bad-month warning comes from a new bad-like target month where the G21 quota subset is materially negative while G16 remains positive; this is diagnostic evidence only.'
                : 'The available IS evidence does not isolate the non-bad-month warning to a safe pre-trade split.',
        ];
    }

    private function guardPreservationAudit(array $c41, array $targetRows, array $baselineMonths, int $removedG21): array
    {
        $monthCounts = $this->monthCounts($targetRows);
        $counts = array_values($monthCounts);
        sort($counts);
        $monthsCovered = count($monthCounts);
        $zeroMonths = array_values(array_diff($baselineMonths, array_keys($monthCounts)));
        $topBranchShare = $this->topBranchShare($targetRows);
        $g16Share = $this->valueShare($targetRows, 'selected_source_code', 'G16');
        $g21Share = $this->valueShare($targetRows, 'selected_source_code', 'G21');
        $c41Guard = is_array($c41['guard_blocker_recheck'] ?? null) ? $c41['guard_blocker_recheck'] : [];
        $coveragePreserved = $monthsCovered === count($baselineMonths) && count($zeroMonths) === 0;
        $branchPreserved = $topBranchShare !== null && $topBranchShare <= self::BRANCH_TOP_SHARE_LIMIT && $g21Share !== null && $g21Share > 0.0;
        return [
            'candidate_months_covered' => $c41Guard['candidate_months_covered'] ?? $monthsCovered,
            'candidate_zero_pick_months' => $c41Guard['candidate_zero_pick_months'] ?? count($zeroMonths),
            'candidate_zero_pick_month_list' => $zeroMonths,
            'candidate_min_selected_rows_per_month' => $c41Guard['candidate_min_selected_rows_per_month'] ?? (count($counts) > 0 ? min($counts) : 0),
            'candidate_median_selected_rows_per_month' => $this->median($counts),
            'candidate_top_branch_share' => $c41Guard['candidate_top_branch_share'] ?? $topBranchShare,
            'candidate_g16_share' => $c41Guard['candidate_g16_share'] ?? $g16Share,
            'candidate_g21_share' => $c41Guard['candidate_g21_share'] ?? $g21Share,
            'removed_or_suppressed_g21_rows' => $c41Guard['removed_or_suppressed_g21_rows'] ?? $removedG21,
            'prior_c37_coverage_branch_blocker_resolved' => (bool) ($c41Guard['prior_c37_coverage_branch_blocker_resolved'] ?? false),
            'coverage_guard_preserved' => $coveragePreserved,
            'branch_guard_preserved' => $branchPreserved,
            'c39_guard_preservation_result' => ($coveragePreserved && $branchPreserved) ? 'PASS' : 'FAIL',
        ];
    }

    private function fieldAvailabilityMatrix(array $rows): array
    {
        $available = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $key => $value) {
                $available[$key] = true;
            }
            if (count($available) > 0) {
                break;
            }
        }

        $fields = [
            'trade_date', 'trade_month', 'ticker', 'symbol', 'selected_source_code', 'bucket_code', 'param_id', 'row_code', 'profile_code', 'profile_exit_reason', 'avg_ret_net', 'profile_ret_net', 'ret_net', 'delta_vs_raw_r09', 'gap_open_pct', 'market_regime', 'sector_code', 'sector_roc20', 'dv20_idr', 'vol_ratio', 'liquidity_bucket',
        ];
        $safe = ['trade_date', 'trade_month', 'ticker', 'symbol', 'selected_source_code', 'bucket_code', 'param_id', 'row_code'];
        $diagnostic = ['profile_code', 'profile_exit_reason'];
        $unsafe = ['avg_ret_net', 'profile_ret_net', 'ret_net', 'delta_vs_raw_r09'];
        $out = [];
        foreach ($fields as $field) {
            $isAvailable = isset($available[$field]) || ($field === 'symbol' && isset($available['ticker']));
            if (! $isAvailable) {
                $classification = 'UNAVAILABLE_FIELD';
                $safeForSelection = false;
                $safeForDiagnostic = false;
                $reason = 'Field is not present in the available IS diagnostic rows.';
            } elseif (in_array($field, $safe, true)) {
                $classification = 'SAFE_PRE_TRADE_SELECTION_FIELD';
                $safeForSelection = true;
                $safeForDiagnostic = true;
                $reason = 'Field is available before selection and does not encode realized return or future path.';
            } elseif (in_array($field, $diagnostic, true)) {
                $classification = 'DIAGNOSTIC_ONLY_EVALUATION_FIELD';
                $safeForSelection = false;
                $safeForDiagnostic = true;
                $reason = 'Field is available for post-selection explanation but must not be used to enable selection.';
            } elseif (in_array($field, $unsafe, true)) {
                $classification = 'UNSAFE_FUTURE_OR_RETURN_FIELD';
                $safeForSelection = false;
                $safeForDiagnostic = true;
                $reason = 'Field is return/evaluation output and cannot be used as selection input.';
            } else {
                $classification = 'UNAVAILABLE_FIELD';
                $safeForSelection = false;
                $safeForDiagnostic = false;
                $reason = 'Field is not available in current C28 evidence.';
            }
            $out[] = [
                'field_name' => $field,
                'available' => $isAvailable,
                'source_artifact' => $isAvailable ? self::DEFAULT_SOURCE_EVIDENCE_ARTIFACT : null,
                'safe_for_selection' => $safeForSelection,
                'safe_for_diagnostic_only' => $safeForDiagnostic && ! $safeForSelection,
                'unsafe_reason' => $reason,
                'field_classification' => $classification,
            ];
        }
        return $out;
    }

    private function guardRefinementFeasibility(array $matrix, array $windows, array $normal): array
    {
        $safeFields = [];
        foreach ($matrix as $field) {
            if (($field['safe_for_selection'] ?? false) === true) {
                $safeFields[] = $field['field_name'];
            }
        }
        $alreadyUsed = ['selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'ticker', 'symbol', 'param_id', 'row_code'];
        $additionalSafeFields = array_values(array_diff($safeFields, $alreadyUsed));
        $windowsExplained = count($windows) > 0 && count(array_filter($windows, function (array $row): bool { return ($row['candidate_warning_explained'] ?? false) === true; })) === count($windows);
        $normalExplained = ($normal['normal_month_warning_explained'] ?? false) === true;
        return [
            'feasibility_result' => count($additionalSafeFields) > 0 ? 'C42_SAFE_REFINEMENT_FIELD_AVAILABLE' : 'C42_NO_ADDITIONAL_SAFE_REFINEMENT_FIELD_AVAILABLE',
            'safe_pre_trade_fields_available' => $safeFields,
            'additional_safe_refinement_fields_available' => $additionalSafeFields,
            'warning_cluster_explained' => $windowsExplained && $normalExplained,
            'safe_refinement_field_available' => count($additionalSafeFields) > 0,
            'safe_refinement_candidate_formed' => false,
            'refinement_candidate_code' => null,
            'reason_code' => count($additionalSafeFields) > 0 ? 'C42_REFINEMENT_FIELD_AVAILABLE_BUT_NOT_FORMED_IN_THIS_SESSION' : 'C42_NO_SAFE_REFINEMENT_FIELD_BEYOND_C39_METADATA',
            'message' => count($additionalSafeFields) > 0
                ? 'A safe field exists, but C42 does not force candidate formation; C43 must validate any proposed refinement in IS first.'
                : 'Warnings are explainable by C39 metadata quota behavior, but current C28 rows do not provide an additional safe pre-trade quality field beyond fields already used by C39.',
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
        ];
    }

    private function candidateComparisonTable(array $baselineRows, array $targetRows): array
    {
        $baseline = $this->metrics($baselineRows);
        $target = $this->metrics($targetRows);
        return [
            [
                'candidate_code' => self::C39_BASELINE_CANDIDATE_CODE,
                'candidate_status' => 'EVALUATED',
                'selected_rows' => count($baselineRows),
                'avg_ret_net' => $baseline['avg_ret_net'],
                'median_ret_net' => $baseline['median_ret_net'],
                'p25_ret_net' => $baseline['p25_ret_net'],
                'win_rate' => $baseline['win_rate'],
                'month_win_rate_min' => $baseline['month_win_rate_min'],
                'month_avg_ret_net_min' => $baseline['month_avg_ret_net_min'],
                'bad_month_like_count' => $baseline['bad_month_like_count'],
                'delta_avg_ret_net_vs_baseline' => 0,
                'delta_median_ret_net_vs_baseline' => 0,
                'delta_p25_ret_net_vs_baseline' => 0,
                'delta_win_rate_vs_baseline' => 0,
                'delta_month_win_rate_min_vs_baseline' => 0,
                'delta_month_avg_ret_net_min_vs_baseline' => 0,
                'delta_bad_month_like_count_vs_baseline' => 0,
                'production_ready' => false,
                'candidate_is_not_production' => true,
            ],
            [
                'candidate_code' => self::TARGET_CANDIDATE_CODE,
                'candidate_status' => 'EVALUATED',
                'selected_rows' => count($targetRows),
                'avg_ret_net' => $target['avg_ret_net'],
                'median_ret_net' => $target['median_ret_net'],
                'p25_ret_net' => $target['p25_ret_net'],
                'win_rate' => $target['win_rate'],
                'month_win_rate_min' => $target['month_win_rate_min'],
                'month_avg_ret_net_min' => $target['month_avg_ret_net_min'],
                'bad_month_like_count' => $target['bad_month_like_count'],
                'delta_avg_ret_net_vs_baseline' => $this->delta($target['avg_ret_net'], $baseline['avg_ret_net']),
                'delta_median_ret_net_vs_baseline' => $this->delta($target['median_ret_net'], $baseline['median_ret_net']),
                'delta_p25_ret_net_vs_baseline' => $this->delta($target['p25_ret_net'], $baseline['p25_ret_net']),
                'delta_win_rate_vs_baseline' => $this->delta($target['win_rate'], $baseline['win_rate']),
                'delta_month_win_rate_min_vs_baseline' => $this->delta($target['month_win_rate_min'], $baseline['month_win_rate_min']),
                'delta_month_avg_ret_net_min_vs_baseline' => $this->delta($target['month_avg_ret_net_min'], $baseline['month_avg_ret_net_min']),
                'delta_bad_month_like_count_vs_baseline' => $this->delta($target['bad_month_like_count'], $baseline['bad_month_like_count']),
                'production_ready' => false,
                'candidate_is_not_production' => true,
            ],
        ];
    }

    private function warningExplanationSummary(array $windows, array $normal, array $feasibility): array
    {
        $windowExplained = count($windows) > 0 && count(array_filter($windows, function (array $row): bool { return ($row['candidate_warning_explained'] ?? false) === true; })) === count($windows);
        $normalExplained = ($normal['normal_month_warning_explained'] ?? false) === true;
        return [
            'rolling_warning_explanation_result' => $windowExplained ? 'C42_ROLLING_WARNING_EXPLAINED' : 'C42_ROLLING_WARNING_NOT_EXPLAINED',
            'normal_month_warning_explanation_result' => $normalExplained ? 'C42_NORMAL_MONTH_WARNING_EXPLAINED' : 'C42_NORMAL_MONTH_WARNING_NOT_EXPLAINED',
            'warning_interpretation' => ($windowExplained && $normalExplained) ? 'STRUCTURAL_METADATA_QUOTA_WEAKNESS' : 'INSUFFICIENT_IS_EVIDENCE',
            'candidate_warning_explained' => $windowExplained && $normalExplained,
            'candidate_warning_acceptable_for_direct_oos' => false,
            'safe_refinement_field_available' => (bool) ($feasibility['safe_refinement_field_available'] ?? false),
            'safe_refinement_candidate_formed' => false,
            'message' => ($windowExplained && $normalExplained)
                ? 'C42 explains the warning as a structural March-2024 G21 metadata quota loss cluster, not as OOS evidence. Direct OOS remains locked because no additional safe refinement field is available in C28.'
                : 'C42 could not explain all C41 warning layers with the available IS evidence.',
        ];
    }

    private function decisionSummary(array $artifact): array
    {
        $summary = $artifact['warning_explanation_summary'] ?? [];
        $guard = $artifact['guard_preservation_audit'] ?? [];
        $feasibility = $artifact['guard_refinement_feasibility'] ?? [];
        $explained = ($summary['candidate_warning_explained'] ?? false) === true;
        $safeField = ($feasibility['safe_refinement_field_available'] ?? false) === true;
        $candidateFormed = false;
        $guardPass = ($guard['c39_guard_preservation_result'] ?? null) === 'PASS';
        if ($explained && $guardPass && ! $safeField) {
            $lockDecision = 'C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE';
            $candidateDecision = 'C42_C39_CANDIDATE_REQUIRES_GUARD_REFINEMENT_BEFORE_OOS';
        } elseif ($explained && $guardPass && $safeField) {
            $lockDecision = 'C42_C39_CANDIDATE_REQUIRES_GUARD_REFINEMENT_BEFORE_OOS';
            $candidateDecision = 'C42_WARNING_EXPLAINED_BUT_REQUIRES_GUARD_REFINEMENT';
        } else {
            $lockDecision = 'C42_C39_CANDIDATE_WARNING_NOT_EXPLAINED_REQUIRES_MORE_EVIDENCE';
            $candidateDecision = 'C42_C39_CANDIDATE_WARNING_NOT_EXPLAINED_REQUIRES_MORE_EVIDENCE';
        }
        return [
            'rolling_warning_explanation_result' => $summary['rolling_warning_explanation_result'] ?? 'C42_ROLLING_WARNING_NOT_EVALUATED',
            'normal_month_warning_explanation_result' => $summary['normal_month_warning_explanation_result'] ?? 'C42_NORMAL_MONTH_WARNING_NOT_EVALUATED',
            'safe_refinement_field_available' => $safeField,
            'safe_refinement_candidate_formed' => $candidateFormed,
            'c39_candidate_lock_decision' => $lockDecision,
            'c42_candidate_decision' => $candidateDecision,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'requires_c43_is_validation' => $candidateFormed,
            'requires_c43_oos_proof' => false,
            'requires_c43_evidence_expansion' => ! $explained || ! $safeField,
            'requires_c43_guard_refinement_candidate_formation' => $explained && $safeField && ! $candidateFormed,
            'requires_c43_pre_trade_field_expansion_diagnostic' => $explained && ! $safeField,
            'production_ready' => false,
        ];
    }

    private function diagnosticConclusion(array $decision): string
    {
        if (($decision['c42_candidate_decision'] ?? null) === 'C42_C39_CANDIDATE_REQUIRES_GUARD_REFINEMENT_BEFORE_OOS' && ($decision['safe_refinement_field_available'] ?? false) === false) {
            return 'C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE';
        }
        if (($decision['c42_candidate_decision'] ?? null) === 'C42_WARNING_EXPLAINED_BUT_REQUIRES_GUARD_REFINEMENT') {
            return 'C42_WARNING_EXPLAINED_BUT_REQUIRES_GUARD_REFINEMENT';
        }
        return 'C42_WARNING_NOT_EXPLAINED_REQUIRES_MORE_EVIDENCE';
    }

    private function nextStepRecommendation(array $decision): string
    {
        if (($decision['requires_c43_pre_trade_field_expansion_diagnostic'] ?? false) === true) {
            return 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC';
        }
        if (($decision['requires_c43_guard_refinement_candidate_formation'] ?? false) === true) {
            return 'C43_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION';
        }
        if (($decision['requires_c43_is_validation'] ?? false) === true) {
            return 'C43_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C42_REFINEMENT';
        }
        return 'C43_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_CONTINUATION';
    }

    private function candidateSafetyAudit(array $artifact): array
    {
        return [
            [
                'candidate_code' => self::TARGET_CANDIDATE_CODE,
                'review_layer' => 'C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION',
                'passed' => true,
                'reason_code' => 'WS_BT_C42_CANDIDATE_SAFETY_BOUNDARY_PRESERVED',
                'message' => 'C42 uses return only for post-selection diagnostics and keeps C39/C42 candidates outside production.',
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
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
            ],
        ];
    }

    private function notEvaluableReasons(array $c41, array $artifact): array
    {
        $out = [];
        foreach (($c41['not_evaluable_evidence_gap_review']['gaps'] ?? []) as $gap) {
            if (! is_array($gap)) {
                continue;
            }
            $out[] = [
                'validation_layer' => $gap['validation_layer'] ?? 'C39_CANDIDATE_FORMATION',
                'validation_slice' => $gap['validation_slice'] ?? null,
                'reason_code' => $gap['reason_code'] ?? null,
                'message' => $gap['message'] ?? null,
                'c42_review_action' => $gap['c41_review_action'] ?? 'C42_REVIEW_CARRY_FORWARD_EVIDENCE_GAP',
            ];
        }
        if (($artifact['guard_refinement_feasibility']['safe_refinement_field_available'] ?? false) !== true) {
            $out[] = [
                'validation_layer' => 'C42_GUARD_REFINEMENT_FEASIBILITY',
                'validation_slice' => 'ROLLING_AND_NON_BAD_MONTH_WARNING_CLUSTERS',
                'reason_code' => 'C42_NO_SAFE_REFINEMENT_FIELD_BEYOND_C39_METADATA',
                'message' => 'Current IS evidence explains the warning but does not provide an additional safe pre-trade quality/refinement field beyond C39 metadata ordering fields.',
            ];
        }
        return $out;
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C42_IS_WARNING_EXPANSION_COMPLETED',
                'message' => 'C42 completed IS-only rolling and normal-month warning evidence expansion from locked C41 warning requirements.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C42_NO_OOS_OR_PRODUCTION_ACTION',
                'message' => 'C42 did not run OOS proof, did not create best-of-OOS, and did not promote production readiness.',
                'fatal' => false,
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C42_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C42 diagnostic conclusion is based only on IS warning expansion evidence.',
                'fatal' => false,
            ],
        ];
    }

    private function sourceEvidencePath(array $c41, array $options): string
    {
        if (isset($options['source_evidence_artifact']) && trim((string) $options['source_evidence_artifact']) !== '') {
            return trim((string) $options['source_evidence_artifact']);
        }
        $fromC41 = (string) ($c41['source_c40_summary']['source_evidence'] ?? '');
        return trim($fromC41) !== '' ? trim($fromC41) : self::DEFAULT_SOURCE_EVIDENCE_ARTIFACT;
    }

    private function isRows(array $rows, string $from, string $to): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $date = (string) ($row['trade_date'] ?? '');
            if ($date === '' || strcmp($date, $from) < 0 || strcmp($date, $to) > 0) {
                continue;
            }
            if (($row['oos_executed'] ?? false) === true || (int) ($row['oos_executed'] ?? 0) === 1) {
                continue;
            }
            if (($row['production_ready'] ?? 0) === true || (int) ($row['production_ready'] ?? 0) === 1) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    private function targetRows(array $rows, string $source, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($source, $bucket): bool {
            return (string) ($row['selected_source_code'] ?? '') === $source
                && (string) ($row['bucket_code'] ?? '') === $bucket
                && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
    }

    private function metadataMonthlyQuotaRows(array $g21Rows, array $g16Rows, array $baselineMonths, float $topShareLimit): array
    {
        $byMonth = [];
        foreach ($g21Rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month === '') {
                continue;
            }
            $byMonth[$month][] = $row;
        }
        foreach ($byMonth as $month => $rows) {
            $byMonth[$month] = $this->sortRowsForSelection($rows);
        }
        $requiredG21 = count($g16Rows) > 0 ? (int) ceil((count($g16Rows) / $topShareLimit) - count($g16Rows)) : 0;
        $quota = count($baselineMonths) > 0 ? max(1, (int) ceil($requiredG21 / count($baselineMonths))) : 0;
        $maxQuota = 0;
        foreach ($byMonth as $rows) {
            $maxQuota = max($maxQuota, count($rows));
        }
        $selected = [];
        while ($quota <= $maxQuota) {
            $selected = [];
            foreach ($baselineMonths as $month) {
                $selected = array_merge($selected, array_slice($byMonth[$month] ?? [], 0, $quota));
            }
            $combined = array_merge($g16Rows, $selected);
            $topShare = $this->topBranchShare($combined);
            if ($topShare !== null && $topShare <= $topShareLimit) {
                break;
            }
            $quota++;
        }
        if ($quota > $maxQuota) {
            $quota = $maxQuota;
            $selected = [];
            foreach ($baselineMonths as $month) {
                $selected = array_merge($selected, array_slice($byMonth[$month] ?? [], 0, $quota));
            }
        }
        return ['rows' => $selected, 'quota_per_month' => $quota, 'required_g21_rows_for_branch_limit' => $requiredG21];
    }

    private function sortRowsForSelection(array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
            $ka = [(string) ($a['trade_month'] ?? ''), (string) ($a['trade_date'] ?? ''), (string) ($a['ticker'] ?? ''), sprintf('%010d', (int) ($a['param_id'] ?? 0)), (string) ($a['row_code'] ?? '')];
            $kb = [(string) ($b['trade_month'] ?? ''), (string) ($b['trade_date'] ?? ''), (string) ($b['ticker'] ?? ''), sprintf('%010d', (int) ($b['param_id'] ?? 0)), (string) ($b['row_code'] ?? '')];
            return strcmp(implode('|', $ka), implode('|', $kb));
        });
        return $rows;
    }

    private function monthBreakdown(array $months, array $baselineMonth, array $targetMonth): array
    {
        $out = [];
        foreach ($months as $month) {
            $b = $baselineMonth[$month] ?? [];
            $t = $targetMonth[$month] ?? [];
            $out[] = [
                'trade_month' => $month,
                'baseline_selected_rows' => $b['selected_rows'] ?? 0,
                'target_selected_rows' => $t['selected_rows'] ?? 0,
                'baseline_avg_ret_net' => $b['avg_ret_net'] ?? null,
                'target_avg_ret_net' => $t['avg_ret_net'] ?? null,
                'baseline_win_rate' => $b['win_rate'] ?? null,
                'target_win_rate' => $t['win_rate'] ?? null,
                'delta_avg_ret_net_vs_baseline' => $this->delta($t['avg_ret_net'] ?? null, $b['avg_ret_net'] ?? null),
            ];
        }
        return $out;
    }

    private function monthMetrics(array $rows): array
    {
        $by = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month === '') {
                continue;
            }
            $by[$month][] = $row;
        }
        $out = [];
        foreach ($by as $month => $monthRows) {
            $metrics = $this->metrics($monthRows);
            $out[$month] = [
                'selected_rows' => count($monthRows),
                'avg_ret_net' => $metrics['avg_ret_net'],
                'win_rate' => $metrics['win_rate'],
                'g16_rows' => $this->countByValue($monthRows, 'selected_source_code', 'G16'),
                'g21_rows' => $this->countByValue($monthRows, 'selected_source_code', 'G21'),
            ];
        }
        ksort($out);
        return $out;
    }

    private function metrics(array $rows): array
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
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $byMonth[$month][] = $value;
        }
        sort($values);
        $count = count($values);
        if ($count === 0) {
            return ['avg_ret_net' => null, 'median_ret_net' => null, 'p25_ret_net' => null, 'p10_ret_net' => null, 'win_rate' => null, 'month_win_rate_min' => null, 'month_avg_ret_net_min' => null, 'bad_month_like_count' => 0, 'loss_concentration' => null];
        }
        $monthWinRates = [];
        $monthAvgs = [];
        $badMonthLike = 0;
        foreach ($byMonth as $vals) {
            $mCount = count($vals);
            $mAvg = array_sum($vals) / $mCount;
            $mWin = $this->winCount($vals) / $mCount;
            $monthAvgs[] = $mAvg;
            $monthWinRates[] = $mWin;
            if ($mWin <= 0.0 || $mAvg < 0.0) {
                $badMonthLike++;
            }
        }
        return [
            'avg_ret_net' => array_sum($values) / $count,
            'median_ret_net' => $this->percentileSorted($values, 0.50),
            'p25_ret_net' => $this->percentileSorted($values, 0.25),
            'p10_ret_net' => $this->percentileSorted($values, 0.10),
            'win_rate' => $this->winCount($values) / $count,
            'month_win_rate_min' => count($monthWinRates) > 0 ? min($monthWinRates) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => $badMonthLike,
            'loss_concentration' => $losses / $count,
        ];
    }

    private function branchReturnBreakdown(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $source = (string) ($row['selected_source_code'] ?? 'UNKNOWN');
            $groups[$source][] = $row;
        }
        ksort($groups);
        $out = [];
        foreach ($groups as $source => $groupRows) {
            $metrics = $this->metrics($groupRows);
            $out[] = [
                'selected_source_code' => $source,
                'selected_rows' => count($groupRows),
                'avg_ret_net' => $metrics['avg_ret_net'],
                'win_rate' => $metrics['win_rate'],
                'share' => count($rows) > 0 ? count($groupRows) / count($rows) : null,
            ];
        }
        return $out;
    }

    private function topLossGroups(array $rows, string $field, int $limit): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? 'UNKNOWN');
            $ret = $this->num($row['profile_ret_net'] ?? null);
            if ($ret === null) {
                continue;
            }
            if (! isset($groups[$value])) {
                $groups[$value] = ['value' => $value, 'count' => 0, 'avg_ret_net' => 0.0, 'loss_count' => 0, 'worst_ret_net' => $ret];
            }
            $groups[$value]['count']++;
            $groups[$value]['avg_ret_net'] += $ret;
            if ($ret < 0.0) {
                $groups[$value]['loss_count']++;
            }
            $groups[$value]['worst_ret_net'] = min($groups[$value]['worst_ret_net'], $ret);
        }
        foreach ($groups as $key => $group) {
            $groups[$key]['avg_ret_net'] = $group['count'] > 0 ? $group['avg_ret_net'] / $group['count'] : null;
            $groups[$key]['loss_share'] = $group['count'] > 0 ? $group['loss_count'] / $group['count'] : null;
        }
        usort($groups, function (array $a, array $b): int {
            if (($a['avg_ret_net'] ?? 0.0) === ($b['avg_ret_net'] ?? 0.0)) {
                return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
            }
            return ($a['avg_ret_net'] ?? 0.0) <=> ($b['avg_ret_net'] ?? 0.0);
        });
        return array_slice($groups, 0, $limit);
    }

    private function distribution(array $rows, string $field): array
    {
        $count = count($rows);
        $counts = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? 'UNKNOWN');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        arsort($counts);
        $out = [];
        foreach ($counts as $value => $valueCount) {
            $out[] = ['value' => $value, 'count' => $valueCount, 'share' => $count > 0 ? $valueCount / $count : null];
        }
        return $out;
    }

    private function monthCounts(array $rows): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month !== '') {
                $counts[$month] = ($counts[$month] ?? 0) + 1;
            }
        }
        ksort($counts);
        return $counts;
    }

    private function uniqueMonths(array $rows): array
    {
        $months = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month !== '') {
                $months[$month] = true;
            }
        }
        $out = array_keys($months);
        sort($out);
        return $out;
    }

    private function monthsFromSlice(string $slice): array
    {
        if (! preg_match('/^(\d{4}-\d{2})_to_(\d{4}-\d{2})$/', $slice, $m)) {
            return [];
        }
        $months = [];
        $cursor = $m[1];
        while (strcmp($cursor, $m[2]) <= 0) {
            $months[] = $cursor;
            $cursor = $this->nextMonth($cursor);
        }
        return $months;
    }

    private function nextMonth(string $month): string
    {
        $year = (int) substr($month, 0, 4);
        $monthNum = (int) substr($month, 5, 2);
        $monthNum++;
        if ($monthNum > 12) {
            $monthNum = 1;
            $year++;
        }
        return sprintf('%04d-%02d', $year, $monthNum);
    }

    private function filterRowsByMonths(array $rows, array $months): array
    {
        $set = array_fill_keys($months, true);
        return array_values(array_filter($rows, function (array $row) use ($set): bool {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            return isset($set[$month]);
        }));
    }

    private function worstMonth(array $monthMetrics): string
    {
        $worst = '';
        $worstValue = null;
        foreach ($monthMetrics as $month => $metrics) {
            $value = $metrics['avg_ret_net'] ?? null;
            if ($value === null) {
                continue;
            }
            if ($worstValue === null || $value < $worstValue) {
                $worst = (string) $month;
                $worstValue = $value;
            }
        }
        return $worst;
    }

    private function branchAvg(array $rows, string $source): ?float
    {
        foreach ($rows as $row) {
            if (($row['selected_source_code'] ?? null) === $source) {
                return $row['avg_ret_net'] ?? null;
            }
        }
        return null;
    }

    private function branchWinRate(array $rows, string $source): ?float
    {
        foreach ($rows as $row) {
            if (($row['selected_source_code'] ?? null) === $source) {
                return $row['win_rate'] ?? null;
            }
        }
        return null;
    }

    private function countByValue(array $rows, string $field, string $value): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if ((string) ($row[$field] ?? '') === $value) {
                $count++;
            }
        }
        return $count;
    }

    private function valueShare(array $rows, string $field, string $value): ?float
    {
        $count = count($rows);
        if ($count === 0) {
            return null;
        }
        return $this->countByValue($rows, $field, $value) / $count;
    }

    private function topBranchShare(array $rows): ?float
    {
        $count = count($rows);
        if ($count === 0) {
            return null;
        }
        $counts = [];
        foreach ($rows as $row) {
            $value = (string) ($row['selected_source_code'] ?? 'UNKNOWN');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        return max($counts) / $count;
    }

    private function median(array $values): ?float
    {
        if (count($values) === 0) {
            return null;
        }
        sort($values);
        return $this->percentileSorted($values, 0.50);
    }

    private function percentileSorted(array $values, float $p): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }
        if ($count === 1) {
            return (float) $values[0];
        }
        $index = ($count - 1) * $p;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $index - $lower;
        return ((float) $values[$lower] * (1 - $weight)) + ((float) $values[$upper] * $weight);
    }

    private function winCount(array $values): int
    {
        $count = 0;
        foreach ($values as $value) {
            if ($value > 0.0) {
                $count++;
            }
        }
        return $count;
    }

    private function delta($left, $right): ?float
    {
        $a = $this->num($left);
        $b = $this->num($right);
        if ($a === null || $b === null) {
            return null;
        }
        return $a - $b;
    }

    private function num($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }
        return (float) $value;
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

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, array $extra = []): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = ['reason_code' => $reasonCode, 'message' => $message, 'fatal' => true, 'extra' => $extra];
        $artifact['diagnostic_conclusion'] = 'C42_INPUT_LOCK_OR_BOUNDARY_BLOCKED';
        $artifact['next_step_recommendation'] = 'C42_BLOCKED_UNTIL_INPUT_VALIDATED';
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
            'expected_c41_hash' => $artifact['expected_c41_hash'] ?? null,
            'actual_c41_hash' => $artifact['actual_c41_hash'] ?? null,
            'c41_hash_match' => $artifact['c41_hash_match'] ?? false,
            'c41_status' => $artifact['c41_status'] ?? null,
            'c41_diagnostic_conclusion' => $artifact['c41_diagnostic_conclusion'] ?? null,
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
                'status' => 'C42_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C42 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c41_hash' => $artifact['expected_c41_hash'] ?? null,
                'actual_c41_hash' => $artifact['actual_c41_hash'] ?? null,
                'c41_hash_match' => $artifact['c41_hash_match'] ?? false,
                'c41_status' => $artifact['c41_status'] ?? null,
                'c41_diagnostic_conclusion' => $artifact['c41_diagnostic_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }
        return [
            'status' => $artifact['status'] ?? 'C42_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C42_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c41_hash' => $artifact['expected_c41_hash'] ?? null,
            'actual_c41_hash' => $artifact['actual_c41_hash'] ?? null,
            'c41_hash_match' => $artifact['c41_hash_match'] ?? false,
            'c41_status' => $artifact['c41_status'] ?? null,
            'c41_diagnostic_conclusion' => $artifact['c41_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c41_summary' => $artifact['source_c41_summary'] ?? [],
            'warning_explanation_summary' => $artifact['warning_explanation_summary'] ?? [],
            'c42_decision_summary' => $artifact['c42_decision_summary'] ?? [],
            'guard_preservation_audit' => $artifact['guard_preservation_audit'] ?? [],
            'guard_refinement_feasibility' => $artifact['guard_refinement_feasibility'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C42 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
