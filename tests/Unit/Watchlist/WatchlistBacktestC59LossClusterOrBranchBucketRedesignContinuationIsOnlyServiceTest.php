<?php

use App\Application\Watchlist\Services\WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService;

class WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyServiceTest extends TestCase
{
    public function test_C59_rejects_missing_C58_artifact(): void
    {
        $out = storage_path('framework/testing/c59-missing-out.json');
        $result = (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            storage_path('framework/testing/c59-missing-c58.json'),
            WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH,
            WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1,
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true]
        );
        $this->assertSame('C59_BLOCKED_MISSING_C58_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C59_C58_ARTIFACT_MISSING', $result['reason_code']);
        @unlink($out);
    }

    public function test_C59_validates_C58_hash_lock(): void
    {
        $out = storage_path('framework/testing/c59-bad-hash-out.json');
        $result = (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            $this->c58Path(), 'bad-hash', WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1,
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true]
        );
        $this->assertSame('C59_BLOCKED_C58_HASH_MISMATCH', $result['status']);
        @unlink($out);
    }

    public function test_C59_validates_C58_file_sha1_lock(): void
    {
        $out = storage_path('framework/testing/c59-bad-sha-out.json');
        $result = (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            $this->c58Path(), WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH, 'BADSHA1',
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true]
        );
        $this->assertSame('C59_BLOCKED_C58_FILE_SHA1_MISMATCH', $result['status']);
        @unlink($out);
    }

    public function test_C59_generates_artifact_with_dictionary_summary(): void
    {
        [$artifact, $out] = $this->runArtifact('dictionary');
        $this->assertSame('C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', $artifact['status']);
        $this->assertSame('C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertTrue($artifact['database_dictionary_read_summary']['dictionary_read_required']);
        $this->assertFalse($artifact['database_dictionary_read_summary']['dictionary_missing_coverage_detected']);
        $this->assertTrue($artifact['database_dictionary_read_summary']['asof_safe']);
        $this->assertFalse($artifact['database_dictionary_read_summary']['future_lookup_detected']);
        $this->assertSame(0, $artifact['database_dictionary_read_summary']['oos_rows_requested']);
        @unlink($out);
    }

    public function test_C59_records_C58_artifact_locks(): void
    {
        [$artifact, $out] = $this->runArtifact('locks');
        $this->assertTrue($artifact['c58_hash_match']);
        $this->assertTrue($artifact['c58_file_sha1_match']);
        $this->assertSame(WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH, $artifact['source_artifact_locks']['expected_c58_hash']);
        $this->assertSame(WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1, $artifact['source_artifact_locks']['expected_c58_file_sha1']);
        @unlink($out);
    }

    public function test_C59_includes_C58_blocker_summary(): void
    {
        [$artifact, $out] = $this->runArtifact('blockers');
        $summary = $artifact['c58_blocker_summary'];
        $this->assertSame('C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', $summary['source_c58_status']);
        $this->assertSame('C58_LOSS_CLUSTER_GAP_REMAINS', $summary['source_c58_reason_code']);
        $this->assertSame(10, $summary['source_c58_candidate_count']);
        $this->assertSame(0, $summary['source_c58_candidate_ready_for_c59_count']);
        $this->assertContains('loss_cluster_share', $summary['dominant_blockers']);
        $this->assertSame('market_down_or_sideways_high_vol', $summary['source_c58_weakest_regime']);
        @unlink($out);
    }

    public function test_C59_retains_C57_regime_reconstruction_through_C58_lock(): void
    {
        [$artifact, $out] = $this->runArtifact('regime-lock');
        $summary = $artifact['c57_c58_regime_lock_summary'];
        $this->assertTrue($summary['c57_regime_reconstruction_retained_through_c58_lock']);
        $this->assertTrue($summary['regime_fully_evaluable']);
        $this->assertSame(9, $summary['required_field_count']);
        $this->assertSame(9, $summary['evaluable_field_count']);
        $this->assertSame(0, $summary['missing_field_count']);
        $this->assertTrue($summary['market_index_roc20_reconstructed']);
        $this->assertTrue($summary['market_index_ma20_slope_pct_reconstructed']);
        $this->assertFalse($summary['market_index_reconstruction_repeated_in_c59']);
        @unlink($out);
    }

    public function test_C59_creates_loss_cluster_first_track_A_candidates(): void
    {
        [$artifact, $out] = $this->runArtifact('track-a');
        $this->assertGreaterThanOrEqual(3, $artifact['candidate_generation_summary']['track_a_loss_cluster_first_candidate_count']);
        $this->assertContains('C59_A01_R05_LOSS_CLUSTER_CAP_08_BRANCH_BUCKET_45', array_column($artifact['candidate_scorecard'], 'candidate_code'));
        @unlink($out);
    }

    public function test_C59_creates_branch_bucket_first_track_B_candidates(): void
    {
        [$artifact, $out] = $this->runArtifact('track-b');
        $this->assertGreaterThanOrEqual(3, $artifact['candidate_generation_summary']['track_b_branch_bucket_first_candidate_count']);
        $this->assertContains('C59_B01_R05_BRANCH_BUCKET_CAP_42_LOSS_085', array_column($artifact['candidate_scorecard'], 'candidate_code'));
        @unlink($out);
    }

    public function test_C59_creates_regime_stress_track_C_candidates(): void
    {
        [$artifact, $out] = $this->runArtifact('track-c');
        $this->assertGreaterThanOrEqual(2, $artifact['candidate_generation_summary']['track_c_regime_stress_candidate_count']);
        $this->assertContains('C59_C01_R09_WEAK_REGIME_EXPOSURE_BALANCE', array_column($artifact['candidate_scorecard'], 'candidate_code'));
        @unlink($out);
    }

    public function test_C59_creates_LOO_dependency_breaker_track_D_candidates(): void
    {
        [$artifact, $out] = $this->runArtifact('track-d');
        $this->assertGreaterThanOrEqual(2, $artifact['candidate_generation_summary']['track_d_loo_dependency_breaker_candidate_count']);
        $this->assertContains('C59_D02_R05_MONTH_CAP_06_LOO_BREAKER', array_column($artifact['candidate_scorecard'], 'candidate_code'));
        @unlink($out);
    }

    public function test_C59_creates_hybrid_candidates(): void
    {
        [$artifact, $out] = $this->runArtifact('hybrid');
        $this->assertGreaterThanOrEqual(2, $artifact['candidate_generation_summary']['hybrid_candidate_count']);
        $this->assertContains('C59_H01_R05_R09_HYBRID_LOSS08_BRANCH44', array_column($artifact['candidate_scorecard'], 'candidate_code'));
        @unlink($out);
    }

    public function test_C59_computes_loss_cluster_metrics_for_every_candidate(): void
    {
        [$artifact, $out] = $this->runArtifact('loss-metrics');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['loss_cluster_validation_results']);
        foreach ($artifact['loss_cluster_validation_results'] as $row) {
            foreach (['loss_cluster_share', 'loss_cluster_count', 'loss_cluster_trade_count', 'loss_cluster_month_count', 'loss_cluster_branch_count', 'loss_cluster_bucket_count', 'loss_cluster_ticker_count', 'loss_cluster_pre_trade_guard_pass', 'loss_cluster_validation_pass', 'failure_reason_codes'] as $field) {
                $this->assertArrayHasKey($field, $row);
            }
            $this->assertTrue($row['loss_cluster_pre_trade_guard_pass']);
        }
        @unlink($out);
    }

    public function test_C59_computes_concentration_metrics_for_every_candidate(): void
    {
        [$artifact, $out] = $this->runArtifact('concentration-metrics');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['concentration_dependency_validation_results']);
        foreach ($artifact['concentration_dependency_validation_results'] as $row) {
            foreach (['max_ticker_share', 'max_sector_share', 'max_bucket_share', 'max_branch_share', 'max_month_share', 'unique_ticker_count', 'unique_sector_count', 'unique_bucket_count', 'unique_branch_count', 'loss_cluster_share', 'concentration_validation_pass', 'failure_reason_codes'] as $field) {
                $this->assertArrayHasKey($field, $row);
            }
        }
        @unlink($out);
    }

    public function test_C59_computes_rolling_validation(): void
    {
        [$artifact, $out] = $this->runArtifact('rolling');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['rolling_validation_results']);
        $this->assertTrue($artifact['rolling_validation_summary']['validation_required']);
        $this->assertArrayHasKey('candidate_pass_count', $artifact['rolling_validation_summary']);
        @unlink($out);
    }

    public function test_C59_computes_leave_one_month_out_validation(): void
    {
        [$artifact, $out] = $this->runArtifact('loo');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['leave_one_month_out_results']);
        $this->assertTrue($artifact['leave_one_month_out_summary']['validation_required']);
        $this->assertArrayHasKey('single_month_dependency_detected_count', $artifact['leave_one_month_out_summary']);
        @unlink($out);
    }

    public function test_C59_marks_single_month_dependency_as_candidate_failure_when_detected(): void
    {
        [$artifact, $out] = $this->runArtifact('single-month');
        $detected = array_values(array_filter($artifact['candidate_scorecard'], fn (array $row): bool => (bool) $row['single_month_dependency_detected']));
        $this->assertNotEmpty($detected);
        foreach ($detected as $row) {
            $this->assertFalse($row['loo_validation_pass']);
            $this->assertContains('C59_SINGLE_MONTH_DEPENDENCY_DETECTED', $row['failure_reason_codes']);
        }
        @unlink($out);
    }

    public function test_C59_computes_regime_robustness_and_weakest_regime(): void
    {
        [$artifact, $out] = $this->runArtifact('regime');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['regime_robustness_validation_results']);
        $this->assertSame('market_down_or_sideways_high_vol', $artifact['regime_robustness_validation_summary']['weakest_regime_mode']);
        $this->assertTrue($artifact['regime_robustness_validation_summary']['c57_regime_reconstruction_retained_through_c58_lock']);
        @unlink($out);
    }

    public function test_C59_computes_sample_recovery_validation(): void
    {
        [$artifact, $out] = $this->runArtifact('sample-recovery');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['sample_recovery_results']);
        $this->assertTrue($artifact['sample_recovery_summary']['validation_required']);
        foreach ($artifact['sample_recovery_results'] as $row) {
            foreach (['parent_evaluated_picks_count', 'candidate_evaluated_picks_count', 'sample_retention_rate', 'sample_recovery_applied', 'sample_recovery_rule', 'sample_recovery_pass', 'minimum_evaluated_pick_threshold'] as $field) {
                $this->assertArrayHasKey($field, $row);
            }
        }
        @unlink($out);
    }

    public function test_C59_computes_material_difference_and_anti_shared_core(): void
    {
        [$artifact, $out] = $this->runArtifact('anti-shared');
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['material_selection_difference_results']);
        $this->assertCount(count($artifact['candidate_scorecard']), $artifact['anti_shared_core_results']);
        $this->assertTrue($artifact['material_selection_difference_summary']['validation_required']);
        $this->assertTrue($artifact['anti_shared_core_summary']['validation_required']);
        @unlink($out);
    }

    public function test_C59_replay_comparators_cannot_be_promoted(): void
    {
        [$artifact, $out] = $this->runArtifact('replay');
        $replays = array_values(array_filter($artifact['candidate_scorecard'], fn (array $row): bool => $row['candidate_role'] === 'replay_comparator'));
        $this->assertNotEmpty($replays);
        foreach ($replays as $row) {
            $this->assertFalse($row['candidate_ready_for_c60']);
            $this->assertFalse($row['overall_is_redesign_pass']);
            $this->assertContains('C59_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE', $row['failure_reason_codes']);
        }
        @unlink($out);
    }

    public function test_C59_blocks_reserved_OOS_date_access(): void
    {
        $out = storage_path('framework/testing/c59-oos-out.json');
        $result = (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            $this->c58Path(), WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH, WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1,
            '2025-05-22', '2025-05-30', $out, ['overwrite' => true]
        );
        $this->assertSame('C59_BLOCKED_OOS_DATE_RANGE_REQUESTED', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertSame(0, $artifact['c60_readiness_decision']['candidate_ready_for_c60_count']);
        @unlink($out);
    }

    public function test_C59_blocks_future_lookup_detection(): void
    {
        $out = storage_path('framework/testing/c59-future-out.json');
        $result = (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            $this->c58Path(), WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH, WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1,
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'force_future_lookup_detected' => true]
        );
        $this->assertSame('C59_BLOCKED_ASOF_OR_OOS_SAFETY', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertTrue($artifact['database_dictionary_read_summary']['future_lookup_detected']);
        @unlink($out);
    }

    public function test_C59_blocks_forced_OOS_rows_requested(): void
    {
        $out = storage_path('framework/testing/c59-oos-rows-out.json');
        $result = (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            $this->c58Path(), WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH, WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1,
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'force_oos_rows_requested' => 1]
        );
        $this->assertSame('C59_BLOCKED_ASOF_OR_OOS_SAFETY', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertSame(1, $artifact['database_dictionary_read_summary']['oos_rows_requested']);
        @unlink($out);
    }

    public function test_C59_never_uses_return_future_or_OOS_return_for_selection(): void
    {
        [$artifact, $out] = $this->runArtifact('selection-safety');
        foreach ($artifact['candidate_scorecard'] as $row) {
            $this->assertFalse($row['return_fields_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_return_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
        }
        $this->assertFalse($artifact['source_bias_validation_summary']['return_fields_used_for_selection']);
        $this->assertFalse($artifact['source_bias_validation_summary']['future_path_used_for_selection']);
        $this->assertFalse($artifact['source_bias_validation_summary']['oos_return_used_for_selection']);
        @unlink($out);
    }

    public function test_C59_keeps_production_and_direct_OOS_flags_false(): void
    {
        [$artifact, $out] = $this->runArtifact('not-production');
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['c60_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c60_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c60_readiness_decision']['production_ready']);
        $this->assertFalse($artifact['safety_boundaries']['production_ready']);
        @unlink($out);
    }

    public function test_C59_next_step_remains_IS_only_when_no_candidate_passes_all_gates(): void
    {
        [$artifact, $out] = $this->runArtifact('decision');
        $this->assertSame(0, $artifact['c60_readiness_decision']['candidate_ready_for_c60_count']);
        $this->assertSame([], $artifact['c60_readiness_decision']['candidate_codes']);
        $this->assertStringContainsString('IS_ONLY', $artifact['c60_readiness_decision']['c60_recommendation']);
        $this->assertStringContainsString('IS_ONLY', $artifact['next_step_recommendation']);
        @unlink($out);
    }

    public function test_C59_candidate_scorecard_contains_required_fields(): void
    {
        [$artifact, $out] = $this->runArtifact('scorecard-fields');
        $this->assertGreaterThanOrEqual(12, count($artifact['candidate_scorecard']));
        foreach ($artifact['candidate_scorecard'] as $row) {
            foreach (['candidate_code', 'parent_candidate_code', 'candidate_role', 'lineage_track', 'selection_rule_summary', 'pre_trade_fields_used', 'evaluated_picks_count', 'avg_ret_net', 'median_ret_net', 'win_rate', 'max_branch_share', 'max_bucket_share', 'max_sector_share', 'max_ticker_share', 'max_month_share', 'loss_cluster_share', 'rolling_validation_pass', 'loo_validation_pass', 'regime_robustness_validation_pass', 'concentration_validation_pass', 'loss_cluster_validation_pass', 'sample_recovery_pass', 'material_selection_difference_pass', 'anti_shared_core_pass', 'overall_is_redesign_pass', 'candidate_ready_for_c60', 'failure_reason_codes'] as $field) {
                $this->assertArrayHasKey($field, $row, $field.' missing for '.$row['candidate_code']);
            }
        }
        @unlink($out);
    }

    private function runArtifact(string $name): array
    {
        $out = storage_path('framework/testing/c59-'.$name.'-out.json');
        (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(
            $this->c58Path(),
            WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH,
            WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1,
            '2023-01-02', '2025-05-21', $out, ['overwrite' => true]
        );
        return [json_decode((string) file_get_contents($out), true), $out];
    }

    private function c58Path(): string
    {
        return base_path('storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json');
    }
}
