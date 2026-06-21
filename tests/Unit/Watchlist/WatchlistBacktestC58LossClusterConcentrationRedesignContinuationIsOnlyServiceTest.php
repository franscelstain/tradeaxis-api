<?php

use App\Application\Watchlist\Services\WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService;

class WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyServiceTest extends TestCase
{
    public function test_C58_rejects_missing_C57_artifact(): void
    {
        $out = storage_path('framework/testing/c58-missing-out.json');
        $result = (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute(
            storage_path('framework/testing/c58-missing-c57.json'),
            WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C57_HASH,
            WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C57_FILE_SHA1,
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true]
        );
        $this->assertSame('C58_BLOCKED_MISSING_C57_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C58_C57_ARTIFACT_MISSING', $result['reason_code']);
        @unlink($out);
    }

    public function test_C58_validates_C57_hash_and_file_sha1_locks(): void
    {
        [$c57, $hash, $sha1, $out] = $this->fixture('lock');
        $badHash = (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, 'bad-hash', $sha1, '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C58_BLOCKED_C57_HASH_MISMATCH', $badHash['status']);
        $badSha = (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, $hash, 'BADSHA1', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C58_BLOCKED_C57_FILE_SHA1_MISMATCH', $badSha['status']);
        $this->cleanup($c57, $out);
    }

    public function test_C58_generates_artifact_with_dictionary_summary_and_C57_regime_retained(): void
    {
        [$c57, $hash, $sha1, $out] = $this->fixture('main');
        $result = (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, $hash, $sha1, '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertSame('C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertTrue($artifact['c57_hash_match']);
        $this->assertTrue($artifact['c57_file_sha1_match']);
        $this->assertTrue($artifact['database_dictionary_read_summary']['dictionary_read_required']);
        $this->assertFalse($artifact['database_dictionary_read_summary']['dictionary_missing_coverage_detected']);
        $this->assertTrue($artifact['database_dictionary_read_summary']['asof_safe']);
        $this->assertFalse($artifact['database_dictionary_read_summary']['future_lookup_detected']);
        $this->assertSame(0, $artifact['database_dictionary_read_summary']['oos_rows_requested']);
        $this->assertTrue($artifact['c57_carry_forward_summary']['regime_field_reconstruction_summary']['regime_fully_evaluable']);
        $this->assertTrue($artifact['c57_carry_forward_summary']['regime_field_reconstruction_summary']['market_index_roc20_reconstructed']);
        $this->assertTrue($artifact['c57_carry_forward_summary']['regime_field_reconstruction_summary']['market_index_ma20_slope_pct_reconstructed']);
        $this->cleanup($c57, $out);
    }

    public function test_C58_creates_candidates_from_track_A_track_B_and_hybrid_lineage(): void
    {
        [$c57, $hash, $sha1, $out] = $this->fixture('candidates');
        (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, $hash, $sha1, '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertGreaterThanOrEqual(2, $artifact['candidate_generation_summary']['track_a_candidate_count']);
        $this->assertGreaterThanOrEqual(4, $artifact['candidate_generation_summary']['track_b_candidate_count']);
        $this->assertGreaterThanOrEqual(2, $artifact['candidate_generation_summary']['hybrid_candidate_count']);
        $codes = array_column($artifact['candidate_scorecard'], 'candidate_code');
        foreach (['C58_R02_R21_ADAPTIVE_BRANCH_BUCKET_48_LOSS_10', 'C58_R04_R09_BRANCH_BUCKET_CAP_48_SAMPLE_RECOVERY', 'C58_R09_HYBRID_R23_R14_BALANCED_REGIME'] as $code) {
            $this->assertContains($code, $codes);
        }
        $this->cleanup($c57, $out);
    }

    public function test_C58_computes_required_validation_metrics_for_every_candidate(): void
    {
        [$c57, $hash, $sha1, $out] = $this->fixture('metrics');
        (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, $hash, $sha1, '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($out), true);
        $count = count($artifact['candidate_scorecard']);
        $this->assertGreaterThanOrEqual(8, $count);
        $this->assertCount($count, $artifact['concentration_dependency_validation_results']);
        $this->assertCount($count, $artifact['loss_cluster_validation_results']);
        $this->assertCount($count, $artifact['rolling_validation_results']);
        $this->assertCount($count, $artifact['leave_one_month_out_results']);
        $this->assertCount($count, $artifact['regime_robustness_validation_results']);
        foreach ($artifact['candidate_scorecard'] as $row) {
            foreach (['candidate_code', 'parent_candidate_code', 'candidate_role', 'selection_rule_summary', 'pre_trade_fields_used', 'evaluated_picks_count', 'avg_ret_net', 'median_ret_net', 'win_rate', 'max_branch_share', 'max_bucket_share', 'max_sector_share', 'max_ticker_share', 'max_month_share', 'loss_cluster_share', 'rolling_validation_pass', 'loo_validation_pass', 'regime_robustness_validation_pass', 'concentration_validation_pass', 'material_selection_difference_pass', 'anti_shared_core_pass', 'overall_is_redesign_pass', 'candidate_ready_for_c59', 'failure_reason_codes'] as $field) {
                $this->assertArrayHasKey($field, $row, $field.' missing for '.$row['candidate_code']);
            }
            $this->assertFalse($row['return_fields_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_return_used_for_selection']);
        }
        $this->cleanup($c57, $out);
    }

    public function test_C58_blocks_reserved_OOS_date_access(): void
    {
        [$c57, $hash, $sha1, $out] = $this->fixture('oos');
        $result = (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, $hash, $sha1, '2025-05-22', '2025-05-30', $out, ['overwrite' => true]);
        $this->assertSame('C58_BLOCKED_OOS_DATE_RANGE_REQUESTED', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['c59_readiness_decision']['direct_oos_proof_recommended']);
        $this->cleanup($c57, $out);
    }

    public function test_C58_keeps_next_step_IS_only_when_no_candidate_passes_all_gates(): void
    {
        [$c57, $hash, $sha1, $out] = $this->fixture('decision');
        (new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService())->execute($c57, $hash, $sha1, '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertSame(0, $artifact['c59_readiness_decision']['candidate_ready_for_c59_count']);
        $this->assertSame([], $artifact['c59_readiness_decision']['candidate_codes']);
        $this->assertStringContainsString('IS_ONLY', $artifact['c59_readiness_decision']['c59_recommendation']);
        $this->assertFalse($artifact['c59_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c59_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c59_readiness_decision']['production_ready']);
        $this->assertFalse($artifact['safety_boundaries']['production_ready']);
        $this->cleanup($c57, $out);
    }

    private function fixture(string $name): array
    {
        $c57 = storage_path('framework/testing/c58-'.$name.'-c57.json');
        $out = storage_path('framework/testing/c58-'.$name.'-out.json');
        $payload = $this->c57Payload();
        $payload['artifact_hash'] = $this->stableHash($payload);
        $this->write($c57, $payload);
        return [$c57, $payload['artifact_hash'], strtoupper((string) sha1_file($c57)), $out];
    }

    private function c57Payload(): array
    {
        $metrics = [
            'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION' => [86, 0.0009447008583989918, 0.005874905039250443, 0.5697674418604651, 0.4883720930232558, 0.5116279069767442, 0.13953488372093023, 0.06976744186046512, 0.06976744186046512, 0.10810810810810811, false, false, false, false],
            'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE' => [81, 0.0013721955918260308, 0.00595, 0.5679012345679012, 0.49382716049382713, 0.5061728395061729, 0.14814814814814814, 0.07407407407407407, 0.07407407407407407, 0.11428571428571428, false, false, false, false],
            'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08' => [112, 0.0025154266227082156, 0.006741459978870051, 0.5892857142857143, 0.5357142857142857, 0.5357142857142857, 0.15178571428571427, 0.08035714285714286, 0.08035714285714286, 0.13043478260869565, true, false, false, false],
            'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08' => [114, 0.0025427853312492868, 0.006741459978870051, 0.5877192982456141, 0.5350877192982456, 0.5350877192982456, 0.14912280701754385, 0.07894736842105263, 0.07894736842105263, 0.1276595744680851, true, false, false, false],
            'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER' => [116, 0.0023424973367700106, 0.006012024048096192, 0.5689655172413793, 0.5517241379310345, 0.5517241379310345, 0.15517241379310345, 0.07758620689655173, 0.07758620689655173, 0.12, true, false, false, false],
            'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER' => [118, 0.0024057469687099988, 0.006012024048096192, 0.5677966101694916, 0.5508474576271186, 0.5508474576271186, 0.15254237288135594, 0.07627118644067797, 0.07627118644067797, 0.11764705882352941, true, false, false, false],
        ];
        $score = [];
        foreach ($metrics as $code => $m) {
            $score[] = [
                'candidate_code' => $code,
                'candidate_role' => 'primary_anchor',
                'evaluated_picks_count' => $m[0],
                'avg_ret_net' => $m[1],
                'median_ret_net' => $m[2],
                'p25_ret_net' => 0.001,
                'p10_ret_net' => -0.005,
                'win_rate' => $m[3],
                'month_win_rate_min' => 0.25,
                'month_avg_ret_net_min' => -0.002,
                'bad_month_like_count' => 1,
                'coverage_months' => 27,
                'max_branch_share' => $m[4],
                'max_bucket_share' => $m[5],
                'max_sector_share' => $m[6],
                'max_ticker_share' => $m[7],
                'max_month_share' => $m[8],
                'loss_cluster_share' => $m[9],
                'quality_pass' => true,
                'coverage_pass' => true,
                'rolling_validation_pass' => $m[10],
                'loo_validation_pass' => $m[11],
                'regime_robustness_validation_pass' => $m[12],
                'concentration_validation_pass' => $m[13],
                'material_selection_difference_pass' => true,
                'anti_shared_core_pass' => true,
                'overall_is_redesign_pass' => false,
                'candidate_ready_for_c58' => false,
                'failure_reason_codes' => ['C57_LOSS_CLUSTER_GAP_REMAINS'],
            ];
        }
        return [
            'run_code' => 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY',
            'status' => 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED',
            'artifact_type' => 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY',
            'production_ready' => false,
            'diagnostic_conclusion' => 'C57_LOSS_CLUSTER_GAP_REMAINS',
            'next_step_recommendation' => 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY',
            'regime_field_reconstruction_summary' => [
                'regime_field_reconstruction_attempted' => true,
                'required_field_count' => 9,
                'evaluable_field_count' => 9,
                'missing_field_count' => 0,
                'regime_field_coverage_min' => 1,
                'regime_fully_evaluable' => true,
                'market_index_regime_fields_reconstructed' => true,
                'market_index_roc20_reconstructed' => true,
                'market_index_ma20_slope_pct_reconstructed' => true,
                'asof_safe' => true,
                'future_lookup_detected' => false,
                'oos_rows_requested' => 0,
                'reconstruction_pass' => true,
                'failure_reason_codes' => [],
            ],
            'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true, 'read_only' => true, 'asof_safe' => true],
            'candidate_scorecard' => $score,
            'selected_c57_candidates_for_c58' => ['candidate_count' => 0, 'candidate_codes' => [], 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'c58_readiness_decision' => ['validation_completed' => true, 'candidate_ready_for_c58_count' => 0, 'candidate_codes' => [], 'c58_recommendation' => 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY', 'decision_reason' => 'loss_cluster_gap_remains', 'diagnostic_conclusion' => 'C57_LOSS_CLUSTER_GAP_REMAINS', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
        ];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function write(string $path, array $payload): void
    {
        if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); }
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) { @unlink($path); }
    }
}
