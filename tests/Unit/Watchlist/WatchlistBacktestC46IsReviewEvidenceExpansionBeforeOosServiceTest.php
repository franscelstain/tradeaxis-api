<?php

use App\Application\Watchlist\Services\WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService;

class WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosServiceTest extends TestCase
{
    public function test_it_blocks_missing_C45_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService())->execute($this->path('missing-c45.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C46_BLOCKED_MISSING_C45_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_C45_hash_mismatch(): void
    {
        [$c45, $output] = $this->fixturePaths('hash');
        $this->writeJson($c45, $this->c45Artifact());
        $result = $this->execute($c45, 'wrong-hash', $output);
        $this->assertSame('C46_BLOCKED_C45_HASH_MISMATCH', $result['status']);
        $this->assertFalse($result['c45_hash_match']);
        $this->cleanup($c45, $output);
    }

    public function test_it_blocks_invalid_C45_contract_and_safety_flags(): void
    {
        $cases = [
            ['status', 'C45_OPERATOR_VALIDATION_REQUIRED', 'C46_BLOCKED_UNEXPECTED_C45_STATUS'],
            ['diagnostic_conclusion', 'C45_C44_REFINEMENT_FAILED_IS_ANTI_OVERFIT_CHECK', 'C46_BLOCKED_UNEXPECTED_C45_CONCLUSION'],
            ['next_step_recommendation', 'C46_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC', 'C46_BLOCKED_UNEXPECTED_C45_NEXT_STEP'],
            ['production_ready', true, 'C46_BLOCKED_C45_SAFETY_FLAGS_INVALID'],
            ['is_period.oos_data_used_for_tuning', true, 'C46_BLOCKED_C45_SAFETY_FLAGS_INVALID'],
            ['validation_summary.overall_anti_overfit_result', 'PASS', 'C46_BLOCKED_C45_RESULT_NOT_WARNING'],
            ['validation_summary.failed_layers', 1, 'C46_BLOCKED_C45_FAILURE_OR_GAP_PRESENT'],
            ['validation_summary.not_evaluable_layers', 1, 'C46_BLOCKED_C45_FAILURE_OR_GAP_PRESENT'],
            ['validation_summary.direct_oos_proof_recommended', true, 'C46_BLOCKED_C45_OOS_FLAGS_INVALID'],
            ['validation_summary.oos_proof_unlocked', true, 'C46_BLOCKED_C45_OOS_FLAGS_INVALID'],
            ['validation_summary.requires_human_review_before_any_oos_step', false, 'C46_BLOCKED_C45_REVIEW_FLAG_INVALID'],
            ['validation_target.target_candidate_code', 'OTHER', 'C46_BLOCKED_C45_TARGET_INVALID'],
            ['candidate_safety_audit.passed', false, 'C46_BLOCKED_C45_SAFETY_AUDIT_INVALID'],
            ['candidate_safety_audit.oos_proof_executed', true, 'C46_BLOCKED_C45_SAFETY_AUDIT_INVALID'],
        ];
        foreach ($cases as $index => $case) {
            [$path, $output] = $this->fixturePaths('boundary-'.$index);
            $artifact = $this->c45Artifact();
            $this->setNested($artifact, $case[0], $case[1]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($path, $artifact);
            $result = $this->execute($path, $artifact['artifact_hash'], $output);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($path, $output);
        }
    }

    public function test_it_blocks_period_touching_reserved_OOS(): void
    {
        [$c45, $output] = $this->fixturePaths('oos');
        $artifact = $this->c45Artifact();
        $this->writeJson($c45, $artifact);
        $result = (new WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService())->execute($c45, $artifact['artifact_hash'], '2023-01-02', '2025-05-22', $output, ['overwrite' => true]);
        $this->assertSame('C46_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c45, $output);
    }

    public function test_bounded_C45_warnings_are_accepted_for_separate_locked_C47_OOS_proof(): void
    {
        $artifact = $this->completedArtifact('accepted');
        $this->assertSame('C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED', $artifact['status']);
        $this->assertSame('C46_C45_WARNING_ACCEPTED_FOR_LOCKED_OOS_PROOF', $artifact['diagnostic_conclusion']);
        $this->assertSame('C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT', $artifact['next_step_recommendation']);
        $this->assertTrue($artifact['review_decision_summary']['all_review_checks_passed']);
        $this->assertTrue($artifact['review_decision_summary']['warning_acceptable_for_locked_oos_proof']);
        $this->assertFalse($artifact['review_decision_summary']['evidence_expansion_required']);
        $this->assertTrue($artifact['review_decision_summary']['oos_proof_unlocked']);
        $this->assertFalse($artifact['review_decision_summary']['oos_proof_executed']);
        $this->assertFalse($artifact['production_ready']);
    }

    public function test_review_explains_yearly_rolling_and_normal_month_warning_headroom(): void
    {
        $artifact = $this->completedArtifact('review');
        $this->assertSame(['yearly', 'rolling', 'non_bad_month'], $artifact['warning_layer_inventory']['warning_layer_names']);
        $this->assertSame('PASS', $artifact['yearly_warning_review']['result']);
        $this->assertSame('PASS', $artifact['rolling_warning_review']['result']);
        $this->assertSame('PASS', $artifact['non_bad_month_warning_review']['result']);
        $this->assertLessThanOrEqual(0.25, $artifact['rolling_warning_review']['warning_share']);
        $this->assertLessThanOrEqual(0.25, $artifact['rolling_warning_review']['avg_hard_fail_budget_share_used']);
        $this->assertSame(0, $artifact['rolling_warning_review']['warning_slices_with_bad_month_increase']);
        $this->assertTrue($artifact['non_bad_month_warning_review']['tail_and_bad_month_stability_preserved']);
        $this->assertSame([], $artifact['evidence_expansion_requirements']);
    }

    public function test_unbounded_warning_distribution_requires_IS_evidence_expansion_and_keeps_OOS_locked(): void
    {
        [$c45, $output] = $this->fixturePaths('expansion');
        $source = $this->c45Artifact();
        $source['rolling_window_validation']['slices'][1]['result'] = 'WARNING';
        $source['rolling_window_validation']['slices'][1]['comparison_vs_baseline']['delta_avg_ret_net'] = -0.001;
        $source['rolling_window_validation']['warning_count'] = 2;
        $source['rolling_window_validation']['pass_count'] = 2;
        $source['artifact_hash'] = $this->stableHash($source);
        $this->writeJson($c45, $source);
        $this->execute($c45, $source['artifact_hash'], $output);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->assertSame('C46_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS', $artifact['diagnostic_conclusion']);
        $this->assertSame('C47_IS_WARNING_SLICE_EVIDENCE_EXPANSION_FOR_C44_REFINEMENT', $artifact['next_step_recommendation']);
        $this->assertFalse($artifact['review_decision_summary']['warning_acceptable_for_locked_oos_proof']);
        $this->assertTrue($artifact['review_decision_summary']['evidence_expansion_required']);
        $this->assertFalse($artifact['review_decision_summary']['oos_proof_unlocked']);
        $this->assertNotEmpty($artifact['evidence_expansion_requirements']);
        $this->assertFalse($artifact['production_ready']);
        $this->cleanup($c45, $output);
    }

    private function completedArtifact(string $suffix): array
    {
        [$c45, $output] = $this->fixturePaths($suffix);
        $source = $this->c45Artifact();
        $this->writeJson($c45, $source);
        $this->execute($c45, $source['artifact_hash'], $output);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->cleanup($c45, $output);
        return $artifact;
    }

    private function execute(string $c45, string $hash, string $output): array
    {
        return (new WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService())->execute($c45, $hash, '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00']);
    }

    private function c45Artifact(): array
    {
        $passMetrics = ['selected_rows' => 100, 'avg_ret_net' => 0.01, 'p25_ret_net' => 0.0, 'p10_ret_net' => -0.001, 'month_avg_ret_net_min' => 0.001, 'bad_month_like_count' => 0];
        $artifact = [
            'status' => WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService::EXPECTED_C45_STATUS,
            'diagnostic_conclusion' => WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService::EXPECTED_C45_CONCLUSION,
            'next_step_recommendation' => WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService::EXPECTED_C45_NEXT_STEP,
            'production_ready' => false,
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'validation_target' => ['target_candidate_code' => WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService::TARGET_CANDIDATE_CODE, 'target_candidate_is_not_production' => true],
            'validation_summary' => [
                'total_validation_layers' => 9, 'passed_layers' => 6, 'warning_layers' => 3, 'failed_layers' => 0, 'not_evaluable_layers' => 0,
                'layer_results' => ['full_is' => 'PASS', 'yearly' => 'WARNING', 'rolling' => 'WARNING', 'bad_month_like_stress' => 'PASS', 'non_bad_month' => 'WARNING', 'ticker_concentration' => 'PASS', 'branch_concentration' => 'PASS', 'month_coverage' => 'PASS', 'downside_stability' => 'PASS'],
                'overall_anti_overfit_result' => 'WARNING', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'requires_human_review_before_any_oos_step' => true,
            ],
            'yearly_validation' => ['result' => 'WARNING', 'slices' => [
                $this->slice('2023', 'WARNING', -0.0004, 0.003, -1),
                $this->slice('2024', 'PASS', 0.001, 0.004, -2),
                $this->slice('2025', 'WARNING', 0.0005, -0.0003, 0),
            ]],
            'rolling_window_validation' => ['result' => 'WARNING', 'slice_count' => 4, 'pass_count' => 3, 'warning_count' => 1, 'fail_count' => 0, 'slices' => [
                $this->slice('ROLLING_6M_A', 'WARNING', -0.001, 0.0, 0),
                $this->slice('ROLLING_6M_B', 'PASS', 0.001, 0.0, 0),
                $this->slice('ROLLING_9M_A', 'PASS', 0.001, 0.002, -1),
                $this->slice('ROLLING_12M_A', 'PASS', 0.002, 0.003, -1),
            ]],
            'non_bad_month_validation' => ['result' => 'WARNING', 'normal_months' => ['2023-04', '2024-01'], 'comparison_vs_baseline' => [
                'delta_avg_ret_net' => -0.0002, 'delta_median_ret_net' => 0.0, 'delta_p25_ret_net' => 0.0, 'delta_p10_ret_net' => 0.0,
                'delta_win_rate' => -0.005, 'delta_month_avg_ret_net_min' => 0.0, 'delta_bad_month_like_count' => 0, 'delta_loss_concentration' => 0.005,
            ]],
            'full_is_validation' => ['result' => 'PASS', 'baseline_candidate' => $passMetrics, 'target_candidate' => $passMetrics, 'comparison_vs_baseline' => [
                'delta_avg_ret_net' => 0.0005, 'delta_p10_ret_net' => 0.001, 'delta_month_avg_ret_net_min' => 0.005, 'delta_bad_month_like_count' => -3,
            ]],
            'bad_month_like_stress_validation' => ['result' => 'PASS', 'comparison_vs_baseline' => ['delta_avg_ret_net' => 0.004, 'delta_bad_month_like_count' => -3]],
            'ticker_concentration_validation' => ['result' => 'PASS', 'target_top_ticker_share' => 0.06],
            'branch_concentration_validation' => ['result' => 'PASS', 'target_top_branch_share' => 0.79, 'target_branch_count' => 2],
            'month_coverage_validation' => ['result' => 'PASS', 'target_months_covered' => 27, 'target_zero_pick_month_count' => 0, 'required_min_selected_rows_per_month' => 13, 'target_min_selected_rows_per_month' => 13],
            'downside_stability_validation' => ['result' => 'PASS'],
            'candidate_safety_audit' => [
                'selection_reconstructed_from_locked_c44_rule' => true, 'selection_uses_signal_date_market_index_roc20' => true, 'fixed_monthly_quota_preserved' => true,
                'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_data_used_for_tuning' => false, 'oos_proof_executed' => false,
                'candidate_is_not_production' => true, 'production_ready' => false, 'passed' => true,
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function slice(string $name, string $result, float $avg, float $monthMin, int $bad): array
    {
        return ['validation_slice' => $name, 'result' => $result, 'comparison_vs_baseline' => [
            'delta_avg_ret_net' => $avg, 'delta_p25_ret_net' => 0.0, 'delta_p10_ret_net' => 0.0,
            'delta_month_avg_ret_net_min' => $monthMin, 'delta_bad_month_like_count' => $bad,
        ]];
    }

    private function fixturePaths(string $suffix): array { $base = sys_get_temp_dir().'/c46-'.$suffix.'-'.uniqid('', true); return [$base.'-c45.json', $base.'-output.json']; }
    private function path(string $name): string { return sys_get_temp_dir().'/c46-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $index => $part) { if ($index === count($parts) - 1) { $cursor[$part] = $value; return; } $cursor =& $cursor[$part]; } }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
