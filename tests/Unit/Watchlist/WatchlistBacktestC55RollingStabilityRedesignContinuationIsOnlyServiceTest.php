<?php

use App\Application\Watchlist\Services\WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService;
use App\Application\Watchlist\Services\WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyService;

class WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyServiceTest extends TestCase
{
    public function test_it_blocks_missing_or_mismatched_C54_locks(): void
    {
        [$c54, $c53, $c52, $out] = $this->fixture('c54-lock');
        $service = new WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52());

        $result = $service->execute(storage_path('framework/testing/c55-missing-c54.json'), 'missing', 'missing', $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_MISSING_C54_ARTIFACT', $result['status']);

        $result = $service->execute($c54, 'wrong', sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_C54_HASH_MISMATCH', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), 'WRONGSHA1', $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_C54_FILE_SHA1_MISMATCH', $result['status']);
        $this->cleanup($c54, $c53, $c52, $out);
    }

    public function test_it_blocks_invalid_C54_contract_and_rolling_gap(): void
    {
        $cases = [
            ['status', 'C54_PENDING', 'C55_BLOCKED_UNEXPECTED_C54_STATUS'],
            ['diagnostic_conclusion', 'C54_OTHER', 'C55_BLOCKED_UNEXPECTED_C54_CONCLUSION'],
            ['next_step_recommendation', 'C56_OTHER', 'C55_BLOCKED_C54_NEXT_STEP_UNEXPECTED'],
            ['production_ready', true, 'C55_BLOCKED_C54_PRODUCTION_READY_NOT_FALSE'],
            ['c55_readiness_decision.oos_proof_unlocked', true, 'C55_BLOCKED_C54_OOS_PROOF_FLAG_INVALID'],
            ['rolling_stability_redesign_summary.candidate_full_rolling_pass_count', 1, 'C55_BLOCKED_MISSING_C54_ROLLING_STABILITY_GAP', 'diagnostic_conclusion', 'C54_ROLLING_STABILITY_IMPROVED_BUT_OTHER_IS_GAPS_REMAIN', 'rolling_stability_redesign_summary.candidate_ready_for_c55_count', 1],
        ];
        foreach ($cases as $i => $case) {
            [$c54, $c53, $c52, $out] = $this->fixture('c54-contract-'.$i);
            $payload = json_decode((string) file_get_contents($c54), true);
            $this->setNested($payload, $case[0], $case[1]);
            if (isset($case[3])) { $this->setNested($payload, $case[3], $case[4]); }
            if (isset($case[5])) { $this->setNested($payload, $case[5], $case[6]); }
            $payload['artifact_hash'] = $this->stableHash($payload);
            $this->write($c54, $payload);
            $result = (new WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52()))->execute($c54, $payload['artifact_hash'], sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c54, $c53, $c52, $out);
        }
    }

    public function test_it_blocks_C53_C52_locks_and_reserved_OOS_period(): void
    {
        [$c54, $c53, $c52, $out] = $this->fixture('downstream-locks');
        $service = new WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52());

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), storage_path('framework/testing/c55-missing-c53.json'), 'h', 's', $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_MISSING_C53_ARTIFACT', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, 'wrong', sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_C53_HASH_MISMATCH', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), 'WRONGSHA1', $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_C53_FILE_SHA1_MISMATCH', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), storage_path('framework/testing/c55-missing-c52.json'), 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_MISSING_C52_ARTIFACT', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, 'wrong', sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_C52_HASH_MISMATCH', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), 'WRONGSHA1', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_C52_FILE_SHA1_MISMATCH', $result['status']);

        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2025-05-22', '2025-06-30', $out, ['overwrite' => true]);
        $this->assertSame('C55_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c54, $c53, $c52, $out);
    }

    public function test_completed_C55_builds_required_layers_and_preserves_safety_boundaries(): void
    {
        [$c54, $c53, $c52, $out] = $this->fixture('complete');
        $service = new WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52());
        $result = $service->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'reconstruction' => $this->reconstruction()]);

        $this->assertSame('C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($out), true);
        foreach (['c54_carry_forward_summary', 'c54_root_cause_summary', 'c53_evidence_carry_forward', 'c52_sector_reconstruction_carry_forward', 'near_pass_rolling_attribution_results', 'near_pass_rolling_attribution_summary', 'source_reconstruction_summary', 'redesign_candidate_definitions', 'candidate_replay_results', 'concentration_dependency_validation_results', 'branch_dependency_validation_results', 'bucket_dependency_validation_results', 'sector_dependency_validation_results', 'month_dependency_validation_results', 'rolling_validation_results', 'rolling_validation_summary', 'leave_one_month_out_results', 'leave_one_month_out_summary', 'regime_robustness_validation_results', 'regime_robustness_validation_summary', 'material_difference_validation_results', 'source_reconstruction_bias_check', 'candidate_scorecard', 'selected_c55_candidates_for_c56', 'c56_readiness_decision', 'candidate_safety_audit', 'not_evaluable_reasons', 'diagnostics'] as $key) {
            $this->assertArrayHasKey($key, $artifact, $key);
        }
        $this->assertCount(21, $artifact['redesign_candidate_definitions']);
        $this->assertCount(21, $artifact['candidate_scorecard']);
        $this->assertFalse($artifact['is_validation_period']['oos_data_used_for_tuning']);
        $this->assertFalse($artifact['is_validation_period']['oos_return_used_for_selection']);
        $this->assertFalse($artifact['is_validation_period']['oos_proof_executed']);
        $this->assertFalse($artifact['oos_reserved_period']['used_for_selection']);
        $this->assertFalse($artifact['oos_reserved_period']['used_for_tuning']);
        $this->assertFalse($artifact['oos_reserved_period']['used_for_proof']);
        $this->assertFalse($artifact['near_pass_rolling_attribution_summary']['failed_window_exclusion_used']);
        foreach ($artifact['redesign_candidate_definitions'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['adverse_month_exclusion_used']);
            $this->assertFalse($row['failed_window_exclusion_used']);
            $this->assertFalse($row['oos_return_used_for_selection']);
        }
        $this->assertFalse($artifact['c56_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c56_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c56_readiness_decision']['production_ready']);
        $this->assertSame('C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY', $artifact['next_step_recommendation']);
        $this->cleanup($c54, $c53, $c52, $out);
    }

    public function test_safety_boundaries_are_powershell_compatible_and_no_forbidden_top_level_keys(): void
    {
        [$c54, $c53, $c52, $out] = $this->fixture('safety');
        (new WatchlistBacktestC55RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52()))->execute($c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'reconstruction' => $this->reconstruction()]);
        $artifact = json_decode((string) file_get_contents($out), true);
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $key) { $this->assertArrayNotHasKey($key, $artifact); }
        $safety = $artifact['safety_boundaries'];
        foreach (['c55_rolling_stability_redesign_continuation_is_only', 'c54_artifact_hash_lock', 'c54_file_sha1_lock', 'c53_artifact_hash_lock', 'c53_file_sha1_lock', 'c52_artifact_hash_lock', 'c52_file_sha1_lock', 'is_only_validation', 'c53_adverse_months_diagnostic_only', 'c54_failed_windows_diagnostic_only', 'no_adverse_month_exclusion_rule', 'no_failed_window_exclusion_rule', 'no_ticker_exclusion_rule', 'no_sector_exclusion_rule', 'predeclared_safe_pre_trade_selection_only', 'no_gate_relaxation', 'no_oos_proof', 'no_oos_proof_rerun', 'no_production_catalog', 'no_plan_confirm_mutation', 'no_c01_to_c54_artifact_mutation', 'candidate_is_not_production', 'c55_must_not_recommend_oos_proof'] as $key) { $this->assertTrue($safety[$key], $key); }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'adverse_month_exclusion_used', 'failed_window_exclusion_used', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) { $this->assertFalse($safety[$key], $key); }
        $keys = array_keys($safety); $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys))));
        $this->cleanup($c54, $c53, $c52, $out);
    }

    private function fakeC52(): WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService
    {
        return new class extends WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService {
            public function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array { return $quota > 0 ? array_slice($rows, 0, $quota) : []; }
            public function selectWithExposureCap(array $rows, int $tickerCap, int $sectorCap): array { return $rows; }
            public function evaluateCandidateRowsForC54(array $candidateRows, array $sourceRows, array $lineage, array $c51, bool $sector, array &$not): array
            {
                $replay = []; $concentration = []; $branch = []; $bucket = []; $sectorRows = []; $month = []; $rollingRows = []; $rolling = []; $looRows = []; $loo = []; $regimeRows = []; $regime = []; $material = [];
                foreach ($candidateRows as $code => $rows) {
                    $replay[] = ['candidate_code' => $code, 'evaluated_picks_count' => count($rows), 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'p25_ret_net' => 0.001, 'p10_ret_net' => -0.01, 'win_rate' => 0.6, 'month_win_rate_min' => 0.5, 'month_avg_ret_net_min' => 0.001, 'bad_month_like_count' => 1, 'coverage_days' => 10, 'coverage_months' => 12, 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'failure_reason_codes' => ['C54_FULL_IS_STABILITY_FAIL']];
                    $concentration[] = ['candidate_code' => $code, 'max_ticker_share' => 0.05, 'max_sector_share' => 0.18, 'max_bucket_share' => 0.55, 'max_branch_share' => 0.55, 'max_month_share' => 0.05, 'unique_ticker_count' => 20, 'unique_sector_count' => 8, 'unique_bucket_count' => 2, 'unique_branch_count' => 2, 'sector_metadata_coverage_rate' => 1.0, 'sector_concentration_evaluable' => true, 'loss_cluster_share' => 0.08, 'top_loss_ticker_share' => 0.05, 'top_loss_sector_share' => 0.1, 'top_loss_branch_share' => 0.2, 'concentration_validation_pass' => true, 'concentration_validation_level' => 'pass', 'failure_reason_codes' => []];
                    $branch[] = ['candidate_code' => $code, 'branch_code' => 'G16', 'branch_row_count' => 1, 'branch_share' => 0.5, 'branch_avg_ret_net' => 0.01, 'branch_median_ret_net' => 0.01, 'branch_win_rate' => 0.6, 'branch_loss_share' => 0.4, 'branch_dependency_detected' => false];
                    $bucket[] = ['candidate_code' => $code, 'bucket_code' => 'A', 'bucket_row_count' => 1, 'bucket_share' => 0.5, 'bucket_avg_ret_net' => 0.01, 'bucket_median_ret_net' => 0.01, 'bucket_win_rate' => 0.6, 'bucket_loss_share' => 0.4, 'bucket_dependency_detected' => false];
                    $sectorRows[] = ['candidate_code' => $code, 'sector_code' => 'S1', 'sector_name' => 'Sector 1', 'sector_row_count' => 1, 'sector_share' => 0.1, 'sector_avg_ret_net' => 0.01, 'sector_median_ret_net' => 0.01, 'sector_win_rate' => 0.6, 'sector_loss_share' => 0.4, 'sector_dependency_detected' => false, 'sector_metadata_source' => 'fixture'];
                    $month[] = ['candidate_code' => $code, 'trade_month' => '2023-01', 'month_row_count' => 1, 'month_share' => 0.05, 'month_avg_ret_net' => 0.01, 'month_median_ret_net' => 0.01, 'month_win_rate' => 0.6, 'month_loss_share' => 0.4, 'month_dependency_detected' => false];
                    $rollingRows[] = ['validation_window_code' => 'ROLLING_6M_STEP_1M_1', 'window_from' => '2023-01-01', 'window_to' => '2023-06-30', 'candidate_code' => $code, 'evaluated_picks_count' => count($rows), 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'p25_ret_net' => 0.001, 'win_rate' => 0.6, 'month_win_rate_min' => 0.5, 'month_avg_ret_net_min' => 0.001, 'bad_month_like_count' => 1, 'coverage_days' => 10, 'coverage_months' => 6, 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'failure_reason_codes' => ['C54_ROLLING_STABILITY_FAIL']];
                    $rolling[] = ['candidate_code' => $code, 'rolling_window_count' => 1, 'rolling_pass_count' => 0, 'rolling_pass_rate' => 0.0, 'rolling_avg_ret_net_min' => 0.01, 'rolling_median_ret_net_min' => 0.01, 'rolling_month_win_rate_min' => 0.5, 'rolling_bad_month_like_max' => 1, 'rolling_coverage_months_min' => 6, 'rolling_validation_pass' => false];
                    $looRows[] = ['exclude_month' => '2023-01', 'candidate_code' => $code, 'row_count_after_exclusion' => 1, 'evaluated_picks_count_after_exclusion' => 1, 'avg_ret_net_after_exclusion' => 0.01, 'median_ret_net_after_exclusion' => 0.01, 'win_rate_after_exclusion' => 0.6, 'month_win_rate_min_after_exclusion' => 0.5, 'quality_delta' => 0, 'stability_delta' => 0, 'candidate_rank_after_exclusion' => 1, 'rank_stable' => true, 'dependency_on_excluded_month' => false];
                    $loo[] = ['candidate_code' => $code, 'loo_month_count' => 1, 'loo_rank_stable_count' => 1, 'loo_rank_stability_rate' => 1.0, 'loo_worst_quality_delta' => 0, 'loo_worst_stability_delta' => 0, 'loo_single_month_dependency_detected' => false, 'loo_validation_pass' => true];
                    $regimeRows[] = ['candidate_code' => $code, 'regime_field' => 'roc20', 'regime_bucket' => 'roc20 >= 0', 'row_count' => 1, 'evaluated_picks_count' => 1, 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'win_rate' => 0.6, 'loss_count' => 0, 'loss_share' => 0, 'bad_month_like_contribution' => 0, 'coverage_share' => 1, 'regime_bucket_pass' => true, 'regime_failure_reason_codes' => []];
                    $regime[] = ['candidate_code' => $code, 'regime_bucket_count' => 1, 'regime_bucket_pass_count' => 1, 'regime_pass_rate' => 1.0, 'regime_worst_bucket_avg_ret_net' => 0.01, 'regime_worst_bucket_win_rate' => 0.6, 'regime_loss_concentration_max' => 0, 'regime_robustness_validation_pass' => true];
                    $material[] = ['candidate_code' => $code, 'overlap_with_c44' => 0.1, 'overlap_with_f00' => 0.1, 'overlap_with_f03' => 0.1, 'overlap_with_f08' => 0.1, 'overlap_with_c52_r07' => 0.1, 'overlap_with_c54_r05' => 0.1, 'overlap_with_c54_r07' => 0.1, 'overlap_with_c54_r08' => 0.1, 'overlap_with_c54_r11' => 0.1, 'shared_core_row_count' => 1, 'candidate_only_row_count' => 9, 'shared_core_avg_ret_net' => 0.01, 'candidate_only_avg_ret_net' => 0.01, 'candidate_only_win_rate' => 0.6, 'material_difference_score' => 0.9, 'material_selection_difference_pass' => true, 'anti_shared_core_pass' => true, 'failure_reason_codes' => []];
                }
                return ['candidate_replay_results' => $replay, 'concentration_dependency_validation_results' => $concentration, 'branch_dependency_validation_results' => $branch, 'bucket_dependency_validation_results' => $bucket, 'sector_dependency_validation_results' => $sectorRows, 'month_dependency_validation_results' => $month, 'rolling_validation_results' => $rollingRows, 'rolling_validation_summary' => ['candidate_summaries' => $rolling, 'rolling_candidate_count' => count($rolling)], 'leave_one_month_out_results' => $looRows, 'leave_one_month_out_summary' => ['candidate_summaries' => $loo, 'loo_candidate_count' => count($loo)], 'regime_robustness_validation_results' => $regimeRows, 'regime_robustness_validation_summary' => ['candidate_summaries' => $regime, 'regime_candidate_count' => count($regime)], 'material_difference_validation_results' => $material];
            }
        };
    }

    private function fixture(string $suffix): array
    {
        $c54 = $this->path($suffix.'-c54.json'); $c53 = $this->path($suffix.'-c53.json'); $c52 = $this->path($suffix.'-c52.json'); $out = $this->path($suffix.'-out.json');
        $p54 = ['status' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C54_ROLLING_STABILITY_GAP_REMAINS', 'next_step_recommendation' => 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY', 'c55_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false], 'rolling_stability_redesign_summary' => ['candidate_count' => 12, 'candidate_ready_for_c55_count' => 0, 'candidate_full_rolling_pass_count' => 0, 'candidate_full_is_stability_pass_count' => 0, 'best_observed_rolling_pass_rate' => 0.9833333333333333], 'candidate_scorecard' => [['candidate_code' => 'C54_R05_G16_08_G21_07_G13_01_MINIMAL', 'candidate_role' => 'redesigned_candidate', 'quality_pass' => true, 'coverage_pass' => true, 'stability_pass' => false, 'concentration_validation_pass' => false, 'rolling_validation_pass' => false, 'loo_validation_pass' => false, 'regime_robustness_validation_pass' => true, 'material_selection_difference_pass' => true]], 'rolling_validation_summary' => ['candidate_summaries' => [['candidate_code' => 'C54_R05_G16_08_G21_07_G13_01_MINIMAL', 'rolling_window_count' => 60, 'rolling_pass_count' => 59, 'rolling_pass_rate' => 0.9833333333333333, 'rolling_validation_pass' => false]]], 'rolling_validation_results' => [['validation_window_code' => 'ROLLING_9M_STEP_1M_6', 'window_from' => '2023-08-01', 'window_to' => '2024-04-30', 'candidate_code' => 'C54_R05_G16_08_G21_07_G13_01_MINIMAL', 'evaluated_picks_count' => 55, 'avg_ret_net' => -0.001, 'median_ret_net' => -0.0005, 'win_rate' => 0.42, 'month_win_rate_min' => 0, 'bad_month_like_count' => 5, 'coverage_months' => 9, 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'failure_reason_codes' => ['C54_ROLLING_STABILITY_FAIL']]], 'safety_boundaries' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false]]; $p54['artifact_hash'] = $this->stableHash($p54);
        $p53 = ['status' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED', 'next_step_recommendation' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY', 'rolling_evidence_expansion_summary' => ['cohort_candidate_count' => 14, 'rolling_window_count' => 840, 'rolling_quality_failure_count' => 0, 'rolling_stability_failure_count' => 217, 'rolling_coverage_failure_count' => 0, 'candidate_full_rolling_pass_count' => 0]]; $p53['artifact_hash'] = $this->stableHash($p53);
        $p52 = ['status' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C52_EVIDENCE_EXPANSION_REQUIRED', 'next_step_recommendation' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN', 'sector_metadata_reconstruction_summary' => ['sector_metadata_reconstruction_pass' => true, 'sector_metadata_join_coverage_rate' => 1, 'sector_metadata_sector_code_coverage_rate' => 1, 'sector_metadata_sector_name_coverage_rate' => 1, 'sector_metadata_unique_sector_count' => 11, 'sector_metadata_max_sector_share_after_join' => 0.22, 'sector_concentration_evaluable' => true, 'dummy_sector_used' => false], 'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true]]; $p52['artifact_hash'] = $this->stableHash($p52);
        $this->write($c54, $p54); $this->write($c53, $p53); $this->write($c52, $p52);
        return [$c54, $c53, $c52, $out];
    }

    private function reconstruction(): array
    {
        $rows = [];
        foreach (range(1, 12) as $i) { $rows[] = ['trade_date' => '2023-01-'.str_pad((string) min($i, 28), 2, '0', STR_PAD_LEFT), 'trade_month' => '2023-01', 'ticker' => 'T'.$i, 'ticker_id' => $i, 'sector_code' => 'S'.(($i % 8) + 1), 'sector_name' => 'Sector', 'selected_source_code' => $i % 2 === 0 ? 'G16' : 'G21', 'bucket_code' => $i % 2 === 0 ? 'B1' : 'B2', 'param_id' => $i, 'row_code' => 'R'.$i, 'profile_ret_net' => 0.01]; }
        return ['source_rows' => $rows, 'source_summary' => ['source_rows_available' => true], 'lineage_rows' => ['months' => ['2023-01'], 'g16' => $rows, 'safe_g21' => $rows, 'g13' => $rows], 'c51_candidate_rows' => [], 'c52_candidate_rows' => ['C52_R07_G16_CAP_55_G21_BACKFILL_SECTOR_AWARE' => $rows], 'not_evaluable_reasons' => [['validation_layer' => 'regime_robustness', 'validation_slice' => 'fixture', 'reason_code' => 'C54_REGIME_FIELD_NOT_EVALUABLE', 'message' => 'Fixture reason']]];
    }

    private function path(string $name): string { return storage_path('framework/testing/c55-'.$name); }
    private function write(string $path, array $payload): void { if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); } file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function hashFile(string $path): string { return $this->stableHash(json_decode((string) file_get_contents($path), true)); }
    private function setNested(array &$payload, string $path, $value): void { $ref =& $payload; foreach (explode('.', $path) as $part) { if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; } $ref =& $ref[$part]; } $ref = $value; }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
