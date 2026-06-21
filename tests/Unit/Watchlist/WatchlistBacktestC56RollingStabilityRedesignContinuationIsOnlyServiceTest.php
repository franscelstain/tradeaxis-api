<?php

use App\Application\Watchlist\Services\WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService;
use App\Application\Watchlist\Services\WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService;

class WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyServiceTest extends TestCase
{
    public function test_it_blocks_missing_or_mismatched_C55_locks(): void
    {
        [$c55, $c54, $c53, $c52, $out] = $this->fixture('c55-lock');
        $service = new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52());

        $result = $service->execute(storage_path('framework/testing/c56-missing-c55.json'), 'missing', 'missing', $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C56_BLOCKED_MISSING_C55_ARTIFACT', $result['status']);

        $result = $service->execute($c55, 'wrong', sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C56_BLOCKED_C55_HASH_MISMATCH', $result['status']);

        $result = $service->execute($c55, $this->hashFile($c55), 'WRONGSHA1', $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C56_BLOCKED_C55_FILE_SHA1_MISMATCH', $result['status']);
        $this->cleanup($c55, $c54, $c53, $c52, $out);
    }

    public function test_it_blocks_invalid_C55_contract_and_missing_rolling_gap(): void
    {
        $cases = [
            ['status', 'C55_PENDING', 'C56_BLOCKED_UNEXPECTED_C55_STATUS'],
            ['diagnostic_conclusion', 'C55_OTHER', 'C56_BLOCKED_UNEXPECTED_C55_CONCLUSION'],
            ['next_step_recommendation', 'C57_OTHER', 'C56_BLOCKED_C55_NEXT_STEP_UNEXPECTED'],
            ['production_ready', true, 'C56_BLOCKED_C55_PRODUCTION_READY_NOT_FALSE'],
            ['c56_readiness_decision.oos_proof_unlocked', true, 'C56_BLOCKED_C55_OOS_PROOF_FLAG_INVALID'],
            ['c56_readiness_decision.candidate_ready_for_c56_count', 1, 'C56_BLOCKED_MISSING_C55_ROLLING_STABILITY_GAP', 'diagnostic_conclusion', 'C55_CONCENTRATION_GAP_REMAINS', 'c56_readiness_decision.rolling_validation_pass_candidate_count', 1, 'rolling_validation_summary.candidate_full_rolling_pass_count', 1],
        ];
        foreach ($cases as $i => $case) {
            [$c55, $c54, $c53, $c52, $out] = $this->fixture('c55-contract-'.$i);
            $payload = json_decode((string) file_get_contents($c55), true);
            $this->setNested($payload, $case[0], $case[1]);
            if (isset($case[3])) { $this->setNested($payload, $case[3], $case[4]); }
            if (isset($case[5])) { $this->setNested($payload, $case[5], $case[6]); }
            if (isset($case[7])) { $this->setNested($payload, $case[7], $case[8]); }
            $payload['artifact_hash'] = $this->stableHash($payload);
            $this->write($c55, $payload);
            $result = (new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52()))->execute($c55, $payload['artifact_hash'], sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c55, $c54, $c53, $c52, $out);
        }
    }

    public function test_it_blocks_downstream_locks_and_reserved_OOS_period(): void
    {
        [$c55, $c54, $c53, $c52, $out] = $this->fixture('downstream-locks');
        $service = new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52());

        $this->assertSame('C56_BLOCKED_MISSING_C54_ARTIFACT', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), storage_path('framework/testing/c56-missing-c54.json'), 'h', 's', $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_C54_HASH_MISMATCH', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, 'wrong', sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_C54_FILE_SHA1_MISMATCH', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), 'WRONGSHA1', $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_MISSING_C53_ARTIFACT', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), storage_path('framework/testing/c56-missing-c53.json'), 'h', 's', $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_C53_HASH_MISMATCH', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, 'wrong', sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_C53_FILE_SHA1_MISMATCH', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), 'WRONGSHA1', $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_MISSING_C52_ARTIFACT', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), storage_path('framework/testing/c56-missing-c52.json'), 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_C52_HASH_MISMATCH', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, 'wrong', sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_C52_FILE_SHA1_MISMATCH', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), 'WRONGSHA1', '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C56_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2025-05-22', '2025-06-30', $out, ['overwrite' => true])['status']);
        $this->cleanup($c55, $c54, $c53, $c52, $out);
    }

    public function test_completed_C56_builds_required_layers_and_preserves_safety_boundaries(): void
    {
        [$c55, $c54, $c53, $c52, $out] = $this->fixture('complete');
        $service = new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52());
        $result = $service->execute($c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'reconstruction' => $this->reconstruction()]);

        $this->assertSame('C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($out), true);
        foreach (['c55_carry_forward_summary', 'c55_root_cause_summary', 'c54_carry_forward_summary', 'c53_evidence_carry_forward', 'c52_sector_reconstruction_carry_forward', 'near_pass_rolling_attribution_results', 'near_pass_rolling_attribution_summary', 'regime_field_reconstruction_summary', 'regime_field_coverage_results', 'missing_regime_field_results', 'asof_safety_validation_results', 'source_reconstruction_summary', 'redesign_candidate_definitions', 'candidate_replay_results', 'concentration_dependency_validation_results', 'branch_dependency_validation_results', 'bucket_dependency_validation_results', 'sector_dependency_validation_results', 'ticker_dependency_validation_results', 'month_dependency_validation_results', 'rolling_validation_results', 'rolling_validation_summary', 'leave_one_month_out_results', 'leave_one_month_out_summary', 'regime_robustness_validation_results', 'regime_robustness_validation_summary', 'material_difference_validation_results', 'source_reconstruction_bias_check', 'candidate_scorecard', 'selected_c56_candidates_for_c57', 'c57_readiness_decision', 'candidate_safety_audit', 'not_evaluable_reasons', 'diagnostics'] as $key) {
            $this->assertArrayHasKey($key, $artifact, $key);
        }
        $this->assertCount(26, $artifact['redesign_candidate_definitions']);
        $this->assertCount(26, $artifact['candidate_scorecard']);
        $this->assertFalse($artifact['is_validation_period']['oos_data_used_for_tuning']);
        $this->assertFalse($artifact['is_validation_period']['oos_return_used_for_selection']);
        $this->assertFalse($artifact['is_validation_period']['oos_proof_executed']);
        $this->assertFalse($artifact['oos_reserved_period']['used_for_selection']);
        $this->assertFalse($artifact['oos_reserved_period']['used_for_tuning']);
        $this->assertFalse($artifact['oos_reserved_period']['used_for_proof']);
        $this->assertFalse($artifact['near_pass_rolling_attribution_summary']['failed_window_exclusion_used']);
        $this->assertTrue($artifact['regime_field_reconstruction_summary']['regime_field_reconstruction_attempted']);
        $this->assertFalse($artifact['regime_field_reconstruction_summary']['future_lookup_detected']);
        $this->assertSame(0, $artifact['regime_field_reconstruction_summary']['oos_rows_requested']);
        foreach ($artifact['redesign_candidate_definitions'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['adverse_month_exclusion_used']);
            $this->assertFalse($row['failed_window_exclusion_used']);
            $this->assertFalse($row['oos_return_used_for_selection']);
        }
        $this->assertFalse($artifact['c57_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c57_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c57_readiness_decision']['production_ready']);
        $this->assertNotSame('C56_OOS_PROOF', $artifact['next_step_recommendation']);
        $this->cleanup($c55, $c54, $c53, $c52, $out);
    }

    public function test_safety_boundaries_are_powershell_compatible_and_no_forbidden_top_level_keys(): void
    {
        [$c55, $c54, $c53, $c52, $out] = $this->fixture('safety');
        (new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService($this->fakeC52()))->execute(storage_path('framework/testing/c56-missing.json'), 'h', 's', $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($out), true);

        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $key) {
            $this->assertArrayNotHasKey($key, $artifact);
        }
        $this->assertFalse($artifact['production_ready']);
        $safety = $artifact['safety_boundaries'];
        foreach (['no_best_of_oos', 'no_oos_winner', 'no_oos_proof', 'no_oos_proof_rerun', 'no_production_catalog', 'no_plan_confirm_mutation', 'no_c01_to_c55_artifact_mutation', 'candidate_is_not_production', 'c56_must_not_recommend_oos_proof', 'no_gate_relaxation', 'no_adverse_month_exclusion_rule', 'no_failed_window_exclusion_rule', 'no_ticker_exclusion_rule', 'no_sector_exclusion_rule', 'c53_adverse_months_diagnostic_only', 'c54_failed_windows_diagnostic_only', 'c55_failed_windows_diagnostic_only', 'source_reconstruction_no_max_trade_date', 'regime_reconstruction_asof_safe'] as $key) {
            $this->assertTrue($safety[$key], $key);
        }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'adverse_month_exclusion_used', 'failed_window_exclusion_used', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) {
            $this->assertFalse($safety[$key], $key);
        }
        $keys = array_keys($safety);
        $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys))), 'safety_boundaries must not contain case-insensitive duplicate keys because PowerShell ConvertFrom-Json fails on them.');
        $this->cleanup($c55, $c54, $c53, $c52, $out);
    }

    private function fakeC52(): WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService
    {
        return new class extends WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService {
            public function reconstructLockedRowsForC54(string $from = self::DEFAULT_FROM, string $to = self::DEFAULT_TO, array $options = []): array { return $options['reconstruction'] ?? ['source_rows' => [], 'lineage_rows' => [], 'source_summary' => [], 'not_evaluable_reasons' => []]; }
            public function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array { return array_slice($rows, 0, max(0, $quota)); }
            public function selectWithExposureCap(array $rows, int $tickerCapPerMonth, int $sectorCapPerMonth): array { return array_values($rows); }
            public function evaluateCandidateRowsForC54(array $candidateRows, array $sourceRows, array $lineage, array $c51Candidates, bool $sectorEvaluable, array &$notEvaluable): array
            {
                $replay = []; $conc = []; $rollingRows = []; $rollingSummaries = []; $looRows = []; $looSummaries = []; $regimeRows = []; $regimeSummaries = []; $material = [];
                foreach ($candidateRows as $code => $rows) {
                    $isComparator = strpos((string) $code, 'COMPARATOR') !== false;
                    $n = count($rows);
                    $readyLike = ! $isComparator && $n >= 3;
                    $replay[] = ['candidate_code' => $code, 'row_count' => $n, 'evaluated_picks_count' => $n, 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'p25_ret_net' => 0.001, 'p10_ret_net' => -0.002, 'win_rate' => 0.65, 'month_win_rate_min' => 0.5, 'month_avg_ret_net_min' => 0.001, 'bad_month_like_count' => 0, 'coverage_days' => 3, 'coverage_months' => 1, 'quality_pass' => true, 'stability_pass' => true, 'coverage_pass' => true, 'failure_reason_codes' => []];
                    $conc[] = ['candidate_code' => $code, 'max_ticker_share' => 0.05, 'max_sector_share' => 0.20, 'max_bucket_share' => 0.50, 'max_branch_share' => 0.50, 'max_month_share' => 0.08, 'unique_ticker_count' => max(1, min($n, 10)), 'unique_sector_count' => 8, 'unique_bucket_count' => 2, 'unique_branch_count' => 2, 'sector_metadata_coverage_rate' => 1, 'sector_concentration_evaluable' => true, 'loss_cluster_share' => 0.08, 'concentration_validation_pass' => $readyLike, 'concentration_validation_level' => $readyLike ? 'PASS' : 'FAIL', 'failure_reason_codes' => $readyLike ? [] : ['C56_COMPARATOR_ONLY_NOT_SELECTABLE']];
                    $rollingRows[] = ['validation_window_code' => 'ROLLING_6M_STEP_1M_1', 'window_from' => '2023-01-02', 'window_to' => '2023-06-30', 'candidate_code' => $code, 'evaluated_picks_count' => $n, 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'p25_ret_net' => 0.001, 'win_rate' => 0.65, 'month_win_rate_min' => 0.5, 'month_avg_ret_net_min' => 0.001, 'bad_month_like_count' => 0, 'coverage_days' => 3, 'coverage_months' => 1, 'quality_pass' => true, 'stability_pass' => true, 'coverage_pass' => true, 'failure_reason_codes' => []];
                    $rollingSummaries[] = ['candidate_code' => $code, 'rolling_window_count' => 1, 'rolling_pass_count' => 1, 'rolling_pass_rate' => 1.0, 'rolling_avg_ret_net_min' => 0.01, 'rolling_median_ret_net_min' => 0.01, 'rolling_month_win_rate_min' => 0.5, 'rolling_bad_month_like_max' => 0, 'rolling_coverage_months_min' => 1, 'rolling_validation_pass' => $readyLike];
                    $looRows[] = ['exclude_month' => '2023-01', 'candidate_code' => $code, 'row_count_after_exclusion' => 0, 'evaluated_picks_count_after_exclusion' => 0, 'avg_ret_net_after_exclusion' => null, 'median_ret_net_after_exclusion' => null, 'win_rate_after_exclusion' => null, 'month_win_rate_min_after_exclusion' => null, 'quality_delta' => 0, 'stability_delta' => 0, 'concentration_delta' => 0, 'loss_cluster_delta' => 0, 'candidate_rank_after_exclusion' => 1, 'rank_stable' => true, 'dependency_on_excluded_month' => false];
                    $looSummaries[] = ['candidate_code' => $code, 'loo_month_count' => 1, 'loo_rank_stable_count' => 1, 'loo_rank_stability_rate' => 1, 'loo_worst_quality_delta' => 0, 'loo_worst_stability_delta' => 0, 'loo_worst_concentration_delta' => 0, 'loo_worst_loss_cluster_delta' => 0, 'loo_single_month_dependency_detected' => false, 'loo_validation_pass' => $readyLike];
                    $regimeRows[] = ['candidate_code' => $code, 'regime_field' => 'roc20', 'regime_bucket' => 'roc20 >= 0', 'row_count' => $n, 'evaluated_picks_count' => $n, 'avg_ret_net' => 0.01, 'median_ret_net' => 0.01, 'win_rate' => 0.65, 'loss_count' => 0, 'loss_share' => 0, 'bad_month_like_contribution' => 0, 'coverage_share' => 1, 'regime_bucket_pass' => true, 'regime_failure_reason_codes' => []];
                    $regimeSummaries[] = ['candidate_code' => $code, 'regime_bucket_count' => 1, 'regime_bucket_pass_count' => 1, 'regime_pass_rate' => 1, 'regime_worst_bucket_avg_ret_net' => 0.01, 'regime_worst_bucket_win_rate' => 0.65, 'regime_loss_concentration_max' => 0, 'regime_robustness_validation_pass' => $readyLike];
                    $material[] = ['candidate_code' => $code, 'overlap_with_c44' => 0.3, 'overlap_with_f00' => 0.3, 'overlap_with_f03' => 0.3, 'overlap_with_f08' => 0.3, 'shared_core_row_count' => 1, 'candidate_only_row_count' => max(0, $n - 1), 'shared_core_avg_ret_net' => 0.01, 'candidate_only_avg_ret_net' => 0.01, 'candidate_only_win_rate' => 0.65, 'material_difference_score' => 0.7, 'material_selection_difference_pass' => $readyLike, 'anti_shared_core_pass' => $readyLike, 'failure_reason_codes' => $readyLike ? [] : ['C56_COMPARATOR_ONLY_NOT_SELECTABLE']];
                }
                return ['candidate_replay_results' => $replay, 'concentration_dependency_validation_results' => $conc, 'rolling_validation_results' => $rollingRows, 'rolling_validation_summary' => ['candidate_summaries' => $rollingSummaries], 'leave_one_month_out_results' => $looRows, 'leave_one_month_out_summary' => ['candidate_summaries' => $looSummaries], 'regime_robustness_validation_results' => $regimeRows, 'regime_robustness_validation_summary' => ['candidate_summaries' => $regimeSummaries], 'material_difference_validation_results' => $material];
            }
        };
    }

    private function fixture(string $name): array
    {
        $base = storage_path('framework/testing');
        $c55 = $base.'/c56-'.$name.'-c55.json'; $c54 = $base.'/c56-'.$name.'-c54.json'; $c53 = $base.'/c56-'.$name.'-c53.json'; $c52 = $base.'/c56-'.$name.'-c52.json'; $out = $base.'/c56-'.$name.'-out.json';
        $p55 = ['status' => 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C55_ROLLING_STABILITY_GAP_REMAINS', 'next_step_recommendation' => 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY', 'c56_readiness_decision' => ['candidate_ready_for_c56_count' => 0, 'rolling_validation_pass_candidate_count' => 0, 'concentration_validation_pass_candidate_count' => 0, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false], 'rolling_validation_summary' => ['candidate_full_rolling_pass_count' => 0, 'candidate_summaries' => [['candidate_code' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'rolling_window_count' => 60, 'rolling_pass_count' => 59, 'rolling_pass_rate' => 0.9833333333333333, 'rolling_validation_pass' => false]]], 'rolling_validation_results' => [['validation_window_code' => 'ROLLING_9M_STEP_1M_6', 'window_from' => '2023-08-01', 'window_to' => '2024-04-30', 'candidate_code' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'evaluated_picks_count' => 55, 'avg_ret_net' => -0.001, 'median_ret_net' => -0.0005, 'win_rate' => 0.42, 'month_win_rate_min' => 0, 'bad_month_like_count' => 5, 'coverage_months' => 9, 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'failure_reason_codes' => ['C55_ROLLING_STABILITY_FAIL']]], 'concentration_dependency_validation_results' => [['candidate_code' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'max_branch_share' => 0.60, 'max_bucket_share' => 0.60, 'max_sector_share' => 0.20, 'max_ticker_share' => 0.07, 'max_month_share' => 0.10, 'loss_cluster_share' => 0.11, 'concentration_validation_pass' => false]], 'near_pass_rolling_attribution_summary' => ['shared_failed_window_detected' => true, 'shared_failed_window_codes' => ['ROLLING_9M_STEP_1M_6']], 'leave_one_month_out_summary' => ['candidate_loo_pass_count' => 1], 'regime_robustness_validation_summary' => ['candidate_regime_pass_count' => 8], 'source_reconstruction_summary' => ['source_evidence_artifact' => 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json'], 'safety_boundaries' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false]]; $p55['artifact_hash'] = $this->stableHash($p55);
        $p54 = ['status' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C54_ROLLING_STABILITY_GAP_REMAINS', 'next_step_recommendation' => 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY', 'rolling_stability_redesign_summary' => ['candidate_count' => 12, 'candidate_ready_for_c55_count' => 0, 'candidate_full_rolling_pass_count' => 0, 'candidate_full_is_stability_pass_count' => 0, 'best_observed_rolling_pass_rate' => 0.9833333333333333]]; $p54['artifact_hash'] = $this->stableHash($p54);
        $p53 = ['status' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED', 'next_step_recommendation' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY', 'rolling_evidence_expansion_summary' => ['cohort_candidate_count' => 14, 'rolling_window_count' => 840, 'rolling_quality_failure_count' => 0, 'rolling_stability_failure_count' => 217, 'rolling_coverage_failure_count' => 0, 'candidate_full_rolling_pass_count' => 0]]; $p53['artifact_hash'] = $this->stableHash($p53);
        $p52 = ['status' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C52_EVIDENCE_EXPANSION_REQUIRED', 'next_step_recommendation' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN', 'sector_metadata_reconstruction_summary' => ['sector_metadata_reconstruction_pass' => true, 'sector_metadata_join_coverage_rate' => 1, 'sector_metadata_sector_code_coverage_rate' => 1, 'sector_metadata_sector_name_coverage_rate' => 1, 'sector_metadata_unique_sector_count' => 11, 'sector_metadata_max_sector_share_after_join' => 0.22, 'sector_concentration_evaluable' => true, 'dummy_sector_used' => false], 'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true]]; $p52['artifact_hash'] = $this->stableHash($p52);
        $this->write($c55, $p55); $this->write($c54, $p54); $this->write($c53, $p53); $this->write($c52, $p52);
        return [$c55, $c54, $c53, $c52, $out];
    }

    private function reconstruction(): array
    {
        $rows = [];
        foreach (range(1, 12) as $i) { $rows[] = ['trade_date' => '2023-01-'.str_pad((string) min($i, 28), 2, '0', STR_PAD_LEFT), 'signal_date' => '2023-01-'.str_pad((string) min($i, 28), 2, '0', STR_PAD_LEFT), 'trade_month' => '2023-01', 'ticker' => 'T'.$i, 'ticker_id' => $i, 'sector_code' => 'S'.(($i % 8) + 1), 'sector_name' => 'Sector', 'selected_source_code' => $i % 2 === 0 ? 'G16' : 'G21', 'bucket_code' => $i % 2 === 0 ? 'B1' : 'B2', 'param_id' => $i, 'row_code' => 'R'.$i, 'profile_ret_net' => 0.01, 'sector_roc20' => 0.01, 'rs_20_vs_ihsg' => 0.01, 'rs_20_vs_sector' => 0.01, 'roc20' => 0.01, 'ma20_slope_pct' => 0.01, 'atr14_pct' => 0.02, 'vol_ratio' => 1.2]; }
        return ['source_rows' => $rows, 'source_summary' => ['source_rows_available' => true, 'fields_present' => array_keys($rows[0])], 'lineage_rows' => ['months' => ['2023-01'], 'g16' => $rows, 'safe_g21' => $rows, 'g13' => $rows], 'c51_candidate_rows' => [], 'c52_candidate_rows' => ['C52_R07_G16_CAP_55_G21_BACKFILL_SECTOR_AWARE' => $rows], 'not_evaluable_reasons' => [['validation_layer' => 'regime_robustness', 'validation_slice' => 'fixture', 'reason_code' => 'C55_REGIME_FIELD_NOT_EVALUABLE', 'message' => 'Fixture reason']]];
    }

    private function write(string $path, array $payload): void { if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); } file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function hashFile(string $path): string { return $this->stableHash(json_decode((string) file_get_contents($path), true)); }
    private function setNested(array &$payload, string $path, $value): void { $ref =& $payload; $parts = explode('.', $path); foreach ($parts as $i => $part) { if ($i === count($parts) - 1) { $ref[$part] = $value; return; } if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; } $ref =& $ref[$part]; } }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
