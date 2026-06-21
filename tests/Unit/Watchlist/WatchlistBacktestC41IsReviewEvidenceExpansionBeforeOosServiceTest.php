<?php

use App\Application\Watchlist\Services\WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService;

class WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosServiceTest extends TestCase
{
    public function test_it_blocks_when_C40_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c41-missing-c40-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            sys_get_temp_dir().'/missing-c40-artifact.json',
            WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService::DEFAULT_EXPECTED_C40_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C41_BLOCKED_MISSING_C40_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C41_C40_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C40_hash_mismatches(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('hash-mismatch');
        $c40 = $this->c40Artifact();
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            'wrong-c40-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_C40_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c40_hash_match']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_status_is_unexpected(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('status');
        $c40 = $this->c40Artifact();
        $c40['status'] = 'C40_OPERATOR_VALIDATION_REQUIRED';
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_UNEXPECTED_C40_STATUS', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_conclusion_is_not_warning_review_path(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('conclusion');
        $c40 = $this->c40Artifact();
        $c40['diagnostic_conclusion'] = 'C40_CANDIDATE_VALIDATED_FOR_OOS_PROOF_NEXT';
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_UNEXPECTED_C40_CONCLUSION', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_next_step_is_unexpected(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('next-step');
        $c40 = $this->c40Artifact();
        $c40['next_step_recommendation'] = 'C41_OOS_PROOF_WITH_LOCKED_C40_VALIDATED_CANDIDATE';
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_UNEXPECTED_C40_NEXT_STEP', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_production_ready_is_not_false(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('production-ready');
        $c40 = $this->c40Artifact();
        $c40['production_ready'] = true;
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_C40_PRODUCTION_READY_NOT_FALSE', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_oos_tuning_flag_is_not_false(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('oos-tuning');
        $c40 = $this->c40Artifact();
        $c40['is_period']['oos_data_used_for_tuning'] = true;
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_C40_OOS_TUNING_FLAG_NOT_FALSE', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_IS_period_touches_reserved_OOS_window(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('oos-window');
        $c40 = $this->c40Artifact();
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_overall_result_is_not_warning(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('not-warning');
        $c40 = $this->c40Artifact();
        $c40['validation_summary']['overall_anti_overfit_result'] = 'PASS';
        $c40['validation_summary']['warning_layers'] = 0;
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_C40_NOT_WARNING_PATH', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_has_failed_layers(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('failed-layer');
        $c40 = $this->c40Artifact();
        $c40['validation_summary']['failed_layers'] = 1;
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_C40_LAYER_FAILURE_OR_NOT_EVALUABLE', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_blocks_when_C40_safety_boundary_is_invalid(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('safety');
        $c40 = $this->c40Artifact();
        $c40['anti_overfit_summary']['no_oos_proof'] = false;
        $c40['artifact_hash'] = $this->stableHash($c40);
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_BLOCKED_C40_SAFETY_BOUNDARY_INVALID', $result['status']);
        $this->cleanup($c40Path, $outputPath);
    }

    public function test_it_completes_C41_review_and_requires_evidence_expansion_before_OOS(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('completed');
        $c40 = $this->c40Artifact();
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c40_hash_match']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertSame('C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS', $artifact['diagnostic_conclusion']);
        $this->assertSame('C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_OR_GUARD_REFINEMENT', $artifact['next_step_recommendation']);
        $this->assertSame(2, $artifact['warning_layer_review']['warning_layer_count']);
        $this->assertSame(3, $artifact['warning_layer_review']['rolling_warning_review']['warning_or_fail_window_count']);
        $this->assertTrue($artifact['warning_layer_review']['non_bad_month_warning_review']['needs_review']);
        $this->assertTrue($artifact['guard_blocker_recheck']['prior_c37_coverage_branch_blocker_resolved']);
        $this->assertSame(2, $artifact['not_evaluable_evidence_gap_review']['carry_forward_gap_count']);
        $this->assertFalse($artifact['review_decision_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['review_decision_summary']['oos_proof_unlocked']);
        $this->assertFalse($artifact['review_decision_summary']['new_candidate_selected']);
        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);

        foreach ($artifact['candidate_safety_audit'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
            $this->assertTrue($row['no_oos_proof']);
            $this->assertTrue($row['no_best_of_oos']);
            $this->assertTrue($row['no_production_catalog']);
        }

        $this->cleanup($c40Path, $outputPath);
    }

    private function c40Artifact(): array
    {
        $artifact = [
            'run_code' => 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE',
            'status' => 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED',
            'artifact_type' => 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE',
            'production_ready' => false,
            'input_c39_artifact' => 'storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json',
            'expected_c39_hash' => '504aaa061054ed2771ed08294d8a0570f08e18db',
            'actual_c39_hash' => '504aaa061054ed2771ed08294d8a0570f08e18db',
            'c39_hash_match' => true,
            'c39_status' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED',
            'c39_diagnostic_conclusion' => 'C39_GUARDED_IS_CANDIDATE_FORMED',
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c39_summary' => [
                'best_is_candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                'source_evidence' => 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json',
            ],
            'validation_target' => [
                'baseline_candidate_code' => 'C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR',
                'target_candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                'target_candidate_is_not_production' => true,
            ],
            'validation_summary' => [
                'total_validation_layers' => 9,
                'passed_layers' => 7,
                'warning_layers' => 2,
                'failed_layers' => 0,
                'not_evaluable_layers' => 0,
                'overall_anti_overfit_result' => 'WARNING',
                'candidate_c40_decision' => 'C40_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS',
            ],
            'full_is_validation' => [
                'result' => 'PASS',
                'comparison_vs_baseline' => [
                    'delta_avg_ret_net_vs_baseline' => 0.006182075965237786,
                    'delta_month_avg_ret_net_min_vs_baseline' => 0.0034795072171515656,
                    'delta_bad_month_like_count_vs_baseline' => -3,
                ],
            ],
            'rolling_window_validation' => $this->rollingWarnings(),
            'non_bad_month_validation' => [
                'validation_layer' => 'NON_BAD_MONTH',
                'validation_slice' => 'NON_BAD_MONTH_IS_MONTHS',
                'baseline_candidate' => ['selected_rows' => 2001, 'month_avg_ret_net_min' => 0.0001003666052420909],
                'target_candidate' => ['selected_rows' => 1208, 'month_avg_ret_net_min' => -0.007926413671186232],
                'comparison_vs_baseline' => [
                    'delta_avg_ret_net_vs_baseline' => 0.0038820395035978495,
                    'delta_p25_ret_net_vs_baseline' => 0.004967101128253132,
                    'delta_win_rate_vs_baseline' => 0.1364206141962131,
                    'delta_month_avg_ret_net_min_vs_baseline' => -0.008026780276428322,
                    'delta_bad_month_like_count_vs_baseline' => 1,
                ],
                'result' => 'WARNING',
                'reason_code' => 'C40_VALIDATION_SLICE_WARNING',
            ],
            'branch_concentration_validation' => [
                'validation_layer' => 'BRANCH_CONCENTRATION',
                'validation_slice' => 'FULL_IS',
                'candidate' => ['top_branch_share' => 0.79374624173181, 'g16_share' => 0.79374624173181, 'g21_share' => 0.20625375826819],
                'top_branch_share' => 0.79374624173181,
                'g16_share' => 0.79374624173181,
                'g21_share' => 0.20625375826819,
                'removed_or_suppressed_g21_rows' => 1427,
                'result' => 'PASS',
            ],
            'month_coverage_validation' => [
                'validation_layer' => 'MONTH_COVERAGE',
                'validation_slice' => 'FULL_IS',
                'candidate' => [
                    'months_covered' => 27,
                    'months_with_selected_rows' => 27,
                    'min_selected_rows_per_month' => 13,
                    'zero_pick_months' => 0,
                ],
                'result' => 'PASS',
            ],
            'anti_overfit_summary' => [
                'full_is_result' => 'PASS',
                'yearly_validation_result' => 'PASS',
                'rolling_validation_result' => 'WARNING',
                'bad_month_stress_result' => 'PASS',
                'normal_month_result' => 'WARNING',
                'ticker_concentration_result' => 'PASS',
                'branch_concentration_result' => 'PASS',
                'month_coverage_result' => 'PASS',
                'downside_stability_result' => 'PASS',
                'overall_anti_overfit_result' => 'WARNING',
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'candidate_is_not_production' => true,
                'no_oos_proof' => true,
                'no_oos_tuning' => true,
                'no_best_of_oos' => true,
                'no_production_catalog' => true,
                'no_candidate_promoted' => true,
            ],
            'candidate_safety_audit' => [
                [
                    'candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
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
                ],
            ],
            'not_evaluable_reasons' => [
                [
                    'validation_layer' => 'C39_CANDIDATE_FORMATION',
                    'validation_slice' => 'C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED',
                    'reason_code' => 'C39_BLOCKED_G21_PRE_TRADE_QUALITY_FIELD_UNAVAILABLE',
                    'message' => 'Candidate is not evaluable from the available C28 IS diagnostic fields without unsafe return/future-path selection input.',
                ],
                [
                    'validation_layer' => 'C39_CANDIDATE_FORMATION',
                    'validation_slice' => 'C39_ROLLING_STABILITY_PRE_TRADE_SPLIT_EXPANSION_REQUIRED',
                    'reason_code' => 'C39_BLOCKED_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELD_UNAVAILABLE',
                    'message' => 'Candidate is not evaluable from the available C28 IS diagnostic fields without unsafe return/future-path selection input.',
                ],
            ],
            'diagnostic_conclusion' => 'C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS',
            'next_step_recommendation' => 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function rollingWarnings(): array
    {
        return [
            $this->rollingWarning('2023-10_to_2024-03', '6_month_window', 829, 492, 0.004891344784638333, -0.008026780276428322, 1),
            $this->rollingWarning('2023-07_to_2024-03', '9_month_window', 1137, 724, 0.0034089788146250523, -0.004826148755050039, 1),
            $this->rollingWarning('2023-04_to_2024-03', '12_month_window', 1409, 903, 0.002921899951438923, -0.004826148755050039, 1),
        ];
    }

    private function rollingWarning(string $slice, string $window, int $baselineRows, int $targetRows, float $deltaAvg, float $deltaMin, int $deltaBadMonths): array
    {
        return [
            'validation_layer' => 'ROLLING_IS',
            'validation_slice' => $slice,
            'window_code' => $window,
            'baseline_candidate' => ['selected_rows' => $baselineRows, 'month_avg_ret_net_min' => -0.003100264916136193],
            'target_candidate' => ['selected_rows' => $targetRows, 'month_avg_ret_net_min' => -0.007926413671186232],
            'comparison_vs_baseline' => [
                'delta_avg_ret_net_vs_baseline' => $deltaAvg,
                'delta_month_avg_ret_net_min_vs_baseline' => $deltaMin,
                'delta_bad_month_like_count_vs_baseline' => $deltaBadMonths,
            ],
            'result' => 'WARNING',
            'reason_code' => 'C40_VALIDATION_SLICE_WARNING',
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c41-review-'.$suffix.'-'.uniqid();
        return [$base.'-c40.json', $base.'-out.json'];
    }

    private function writeJson(string $path, array $payload): void
    {
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }
}
