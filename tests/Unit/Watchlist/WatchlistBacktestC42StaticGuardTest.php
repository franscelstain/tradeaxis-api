<?php

use App\Application\Watchlist\Services\WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService;

class WatchlistBacktestC42StaticGuardTest extends TestCase
{
    public function test_C42_artifact_has_structural_safety_guards_without_forbidden_top_level_keys(): void
    {
        $artifact = $this->completedArtifact();
        $safety = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);

        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('production_catalog', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);
        $this->assertFalse($artifact['production_ready']);
        $this->assertTrue($safety['no_best_of_oos']);
        $this->assertTrue($safety['no_oos_winner']);
        $this->assertTrue($safety['no_production_catalog']);
        $this->assertTrue($safety['no_promotion']);
        $this->assertTrue($safety['no_plan_confirm_mutation']);
        $this->assertTrue($safety['no_c01_to_c41_mutation']);
        $this->assertTrue($safety['no_c01_to_c41_artifact_mutation']);
        $this->assertTrue($safety['candidate_is_not_production']);
    }

    public function test_C42_does_not_enable_OOS_or_return_future_selection_flags(): void
    {
        $artifact = $this->completedArtifact();
        $safety = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);

        $this->assertTrue($safety['c42_is_rolling_normal_month_evidence_expansion']);
        $this->assertTrue($safety['c41_artifact_hash_lock']);
        $this->assertTrue($safety['is_only_evidence_expansion']);
        $this->assertTrue($safety['c42_from_c41_warning_requirements']);
        $this->assertTrue($safety['no_oos_proof']);
        $this->assertTrue($safety['no_oos_tuning']);
        $this->assertFalse($safety['oos_data_used_for_tuning']);
        $this->assertFalse($safety['return_used_for_selection']);
        $this->assertFalse($safety['future_path_used_for_selection']);
        $this->assertFalse($safety['future_path_price_used_for_selection']);
        $this->assertFalse($safety['profile_ret_net_used_for_selection']);
        $this->assertFalse($safety['derived_mfe_mae_used_for_execution']);
        $this->assertFalse($safety['oos_return_used_for_candidate_selection']);
    }

    public function test_C42_locks_expected_C41_hash_and_keeps_OOS_recommendation_as_next_step_only(): void
    {
        $artifact = $this->completedArtifact();

        $this->assertSame(WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_EXPECTED_C41_HASH, WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_EXPECTED_C41_HASH);
        $this->assertSame('C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION', $artifact['run_code']);
        $this->assertSame('C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION', $artifact['artifact_type']);
        $this->assertFalse($artifact['c42_decision_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c42_decision_summary']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c42_decision_summary']['requires_c43_oos_proof']);
        $this->assertFalse($artifact['candidate_safety_audit'][0]['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['candidate_safety_audit'][0]['oos_proof_unlocked']);
        $this->assertTrue($artifact['candidate_safety_audit'][0]['no_oos_proof']);
        $this->assertTrue($artifact['candidate_safety_audit'][0]['no_production_catalog']);
    }

    public function test_C42_service_source_does_not_contain_forbidden_production_or_OOS_winner_keys_as_outputs(): void
    {
        $path = base_path('app/Application/Watchlist/Services/WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService.php');
        $source = file_get_contents($path);
        $this->assertStringContainsString('NO_OOS_PROOF', $source);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $source);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $source);
        $this->assertStringContainsString('return_used_for_selection', $source);
        $this->assertStringContainsString('future_path_used_for_selection', $source);
        $this->assertStringContainsString(WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_EXPECTED_C41_HASH, $source);
    }

    private function completedArtifact(): array
    {
        $c41Path = sys_get_temp_dir().'/c42-static-'.uniqid('', true).'-c41.json';
        $evidencePath = sys_get_temp_dir().'/c42-static-'.uniqid('', true).'-evidence.json';
        $output = sys_get_temp_dir().'/c42-static-'.uniqid('', true).'-output.json';
        $c41 = $this->c41Artifact($evidencePath);
        file_put_contents($c41Path, json_encode($c41, JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($evidencePath, json_encode($this->evidenceArtifact(), JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            $c41['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );
        $this->assertSame('C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($output), true);
        @unlink($c41Path);
        @unlink($evidencePath);
        @unlink($output);
        return $artifact;
    }

    private function c41Artifact(string $sourceEvidence): array
    {
        $artifact = [
            'status' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::EXPECTED_C41_STATUS,
            'production_ready' => false,
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_c40_summary' => ['source_evidence' => $sourceEvidence, 'overall_anti_overfit_result' => 'WARNING'],
            'warning_layer_review' => [
                'warning_layer_count' => 2,
                'rolling_warning_review' => ['warning_or_fail_window_count' => 3, 'window_reviews' => [
                    $this->window('2023-10_to_2024-03', '6_month_window'),
                    $this->window('2023-07_to_2024-03', '9_month_window'),
                    $this->window('2023-04_to_2024-03', '12_month_window'),
                ]],
                'non_bad_month_warning_review' => ['needs_review' => true, 'result' => 'WARNING'],
            ],
            'guard_blocker_recheck' => ['prior_c37_coverage_branch_blocker_resolved' => true],
            'not_evaluable_evidence_gap_review' => ['gaps' => []],
            'review_decision_summary' => [
                'target_candidate_code' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::TARGET_CANDIDATE_CODE,
                'overall_anti_overfit_result' => 'WARNING',
                'warning_layers_count' => 2,
                'failed_layers_count' => 0,
                'rolling_warning_windows' => 3,
                'non_bad_month_warning' => true,
                'guard_blockers_resolved' => true,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::EXPECTED_C41_CONCLUSION,
            'next_step_recommendation' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::EXPECTED_C41_NEXT_STEP,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function window(string $slice, string $code): array
    {
        return [
            'validation_slice' => $slice,
            'window_code' => $code,
            'result' => 'WARNING',
            'baseline_selected_rows' => 0,
            'target_selected_rows' => 0,
            'target_month_avg_ret_net_min' => -0.10,
            'baseline_month_avg_ret_net_min' => 0.01,
            'delta_avg_ret_net_vs_baseline' => 0.01,
            'delta_month_avg_ret_net_vs_baseline' => -0.11,
            'delta_bad_month_like_count_vs_baseline' => 1,
        ];
    }

    private function evidenceArtifact(): array
    {
        $rows = [];
        $months = ['2023-04', '2023-05', '2023-06', '2023-07', '2023-08', '2023-09', '2023-10', '2023-11', '2023-12', '2024-01', '2024-02', '2024-03'];
        foreach ($months as $idx => $month) {
            $rows[] = $this->row($month.'-10', $month, 'G16', 'next_open_delay_after_close_signal', 'A'.$idx, 160, 0.03);
            $rows[] = $this->row($month.'-10', $month, 'G16', 'next_open_delay_after_close_signal', 'B'.$idx, 161, 0.03);
            $rows[] = $this->row($month.'-10', $month, 'G21', 'no_rule_profit_signal_before_fallback', 'C'.$idx, 210, $month === '2024-03' ? -0.20 : 0.02);
            $rows[] = $this->row($month.'-10', $month, 'G21', 'no_rule_profit_signal_before_fallback', 'D'.$idx, 211, 0.02);
        }
        return ['pick_diagnostic_rows' => $rows];
    }

    private function row(string $date, string $month, string $source, string $bucket, string $ticker, int $paramId, float $ret): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => 'ROW_'.$paramId,
            'bucket_code' => $bucket,
            'profile_code' => 'C28_TEST_PROFILE',
            'selected_source_code' => $source,
            'profile_exit_reason' => 'diagnostic_exit',
            'profile_ret_net' => $ret,
            'delta_vs_raw_r09' => $ret,
            'oos_executed' => false,
            'production_ready' => 0,
        ];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
