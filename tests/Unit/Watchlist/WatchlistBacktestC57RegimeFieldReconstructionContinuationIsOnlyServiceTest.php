<?php

use App\Application\Watchlist\Services\WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService;

class WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyServiceTest extends TestCase
{
    public function test_it_blocks_missing_or_mismatched_C56_artifact(): void
    {
        [$c56, $c55, $c54, $c53, $c52, $out] = $this->fixture('c56-lock');
        $service = new WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService();

        $result = $service->execute($this->path('missing-c56.json'), 'missing', $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C57_BLOCKED_MISSING_C56_ARTIFACT', $result['status']);

        $result = $service->execute($c56, 'wrong', $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $this->assertSame('C57_BLOCKED_C56_HASH_MISMATCH', $result['status']);
        $this->cleanup($c56, $c55, $c54, $c53, $c52, $out);
    }

    public function test_it_blocks_invalid_C56_contract_and_missing_regime_gap(): void
    {
        $cases = [
            ['status', 'C56_PENDING', 'C57_BLOCKED_UNEXPECTED_C56_STATUS'],
            ['diagnostic_conclusion', 'C56_RANDOM', 'C57_BLOCKED_UNEXPECTED_C56_CONCLUSION'],
            ['next_step_recommendation', 'C58_OTHER', 'C57_BLOCKED_C56_NEXT_STEP_UNEXPECTED'],
            ['production_ready', true, 'C57_BLOCKED_C56_PRODUCTION_READY_NOT_FALSE'],
            ['c57_readiness_decision.oos_proof_unlocked', true, 'C57_BLOCKED_C56_OOS_PROOF_FLAG_INVALID'],
            ['regime_field_reconstruction_summary.regime_fully_evaluable', true, 'C57_BLOCKED_MISSING_C56_REGIME_FIELD_GAP', 'regime_field_reconstruction_summary.missing_field_count', 0, 'regime_field_coverage_results.0.coverage_rate', 1.0, 'regime_field_coverage_results.1.coverage_rate', 1.0, 'diagnostic_conclusion', 'C56_CONCENTRATION_GAP_REMAINS'],
        ];
        foreach ($cases as $i => $case) {
            [$c56, $c55, $c54, $c53, $c52, $out] = $this->fixture('c56-contract-'.$i);
            $payload = json_decode((string) file_get_contents($c56), true);
            $this->setNested($payload, $case[0], $case[1]);
            if (isset($case[3])) { $this->setNested($payload, $case[3], $case[4]); }
            if (isset($case[5])) { $this->setNested($payload, $case[5], $case[6]); }
            if (isset($case[7])) { $this->setNested($payload, $case[7], $case[8]); }
            if (isset($case[9])) { $this->setNested($payload, $case[9], $case[10]); }
            $payload['artifact_hash'] = $this->stableHash($payload);
            $this->write($c56, $payload);
            $result = (new WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService())->execute($c56, $payload['artifact_hash'], $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c56, $c55, $c54, $c53, $c52, $out);
        }
    }

    public function test_it_blocks_downstream_locks_and_reserved_OOS_period(): void
    {
        [$c56, $c55, $c54, $c53, $c52, $out] = $this->fixture('downstream-locks');
        $service = new WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService();
        $h56 = $this->hashFile($c56);

        $this->assertSame('C57_BLOCKED_MISSING_C55_ARTIFACT', $service->execute($c56, $h56, $this->path('missing-c55.json'), 'h', 's', $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C55_HASH_MISMATCH', $service->execute($c56, $h56, $c55, 'wrong', sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C55_FILE_SHA1_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), 'WRONGSHA1', $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_MISSING_C54_ARTIFACT', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $this->path('missing-c54.json'), 'h', 's', $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C54_HASH_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, 'wrong', sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C54_FILE_SHA1_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), 'WRONGSHA1', $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_MISSING_C53_ARTIFACT', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $this->path('missing-c53.json'), 'h', 's', $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C53_HASH_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, 'wrong', sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C53_FILE_SHA1_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), 'WRONGSHA1', $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_MISSING_C52_ARTIFACT', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $this->path('missing-c52.json'), 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C52_HASH_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, 'wrong', sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_C52_FILE_SHA1_MISMATCH', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), 'WRONGSHA1', '2023-01-02', '2025-05-21', $out, ['overwrite' => true])['status']);
        $this->assertSame('C57_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $service->execute($c56, $h56, $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2025-05-22', '2025-06-30', $out, ['overwrite' => true])['status']);
        $this->cleanup($c56, $c55, $c54, $c53, $c52, $out);
    }

    public function test_completed_C57_builds_required_layers_with_complete_market_index_reconstruction(): void
    {
        [$c56, $c55, $c54, $c53, $c52, $out] = $this->fixture('complete');
        $sourceRows = $this->sourceRows();
        $marketRows = $this->marketRows();
        $result = (new WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService())->execute($c56, $this->hashFile($c56), $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'source_rows' => $sourceRows, 'market_index_source_rows' => $marketRows]);

        $this->assertSame('C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($out), true);
        foreach (['c56_carry_forward_summary', 'c56_root_cause_summary', 'c55_carry_forward_summary', 'c54_carry_forward_summary', 'c53_evidence_carry_forward', 'c52_sector_reconstruction_carry_forward', 'market_index_source_discovery_summary', 'market_index_source_discovery_results', 'market_index_reconstruction_results', 'market_index_date_coverage_results', 'market_index_asof_safety_results', 'regime_field_reconstruction_summary', 'regime_field_coverage_results', 'missing_regime_field_results', 'asof_safety_validation_results', 'source_reconstruction_summary', 'anchor_candidate_definitions', 'candidate_replay_results', 'concentration_dependency_validation_results', 'rolling_validation_results', 'rolling_validation_summary', 'leave_one_month_out_results', 'leave_one_month_out_summary', 'regime_robustness_validation_results', 'regime_robustness_validation_summary', 'material_difference_validation_results', 'source_reconstruction_bias_check', 'candidate_scorecard', 'selected_c57_candidates_for_c58', 'c58_readiness_decision', 'candidate_safety_audit', 'not_evaluable_reasons', 'diagnostics'] as $key) {
            $this->assertArrayHasKey($key, $artifact, $key);
        }
        $this->assertTrue($artifact['market_index_source_discovery_summary']['source_found']);
        $this->assertTrue($artifact['regime_field_reconstruction_summary']['regime_fully_evaluable']);
        $this->assertTrue($artifact['regime_field_reconstruction_summary']['market_index_regime_fields_reconstructed']);
        $this->assertCount(0, $artifact['missing_regime_field_results']);
        $this->assertSame('C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY', $artifact['next_step_recommendation']);
        $this->assertFalse($artifact['c58_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c58_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c58_readiness_decision']['production_ready']);
        $this->assertFalse($artifact['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['oos_return_used_for_selection']);
        $this->assertTrue($artifact['safety_boundaries']['market_index_reconstruction_no_max_trade_date']);
        $this->assertSame(count($artifact['safety_boundaries']), count(array_unique(array_map('strtolower', array_keys($artifact['safety_boundaries'])))));
        $this->cleanup($c56, $c55, $c54, $c53, $c52, $out);
    }


    public function test_completed_C57_loads_required_dates_from_locked_C28_source_evidence_when_source_rows_are_not_injected(): void
    {
        [$c56, $c55, $c54, $c53, $c52, $out] = $this->fixture('locked-c28-source');
        $c28 = $this->path('locked-c28-source-c28.json');
        $this->write($c28, ['status' => 'C28_COMPLETED', 'pick_diagnostic_rows' => $this->sourceRows()]);

        $payload = json_decode((string) file_get_contents($c56), true);
        $payload['source_reconstruction_summary']['source_evidence_artifact'] = $c28;
        $payload['artifact_hash'] = $this->stableHash($payload);
        $this->write($c56, $payload);

        $result = (new WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService())->execute(
            $c56,
            $payload['artifact_hash'],
            $c55,
            $this->hashFile($c55),
            sha1_file($c55),
            $c54,
            $this->hashFile($c54),
            sha1_file($c54),
            $c53,
            $this->hashFile($c53),
            sha1_file($c53),
            $c52,
            $this->hashFile($c52),
            sha1_file($c52),
            '2023-01-02',
            '2025-05-21',
            $out,
            ['overwrite' => true, 'market_index_source_rows' => $this->marketRows()]
        );

        $this->assertSame('C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertSame(5, $artifact['market_index_source_discovery_summary']['required_date_count']);
        $this->assertSame('signal_date', $artifact['market_index_source_discovery_summary']['source_row_date_field_detected']);
        $this->assertSame('2023-01-02', $artifact['market_index_source_discovery_summary']['required_date_min']);
        $this->assertSame('2023-01-06', $artifact['market_index_source_discovery_summary']['required_date_max']);
        $this->assertTrue($artifact['regime_field_reconstruction_summary']['regime_fully_evaluable']);
        $this->cleanup($c56, $c55, $c54, $c53, $c52, $out, $c28);
    }

    public function test_market_index_missing_or_invalid_safety_keeps_regime_not_fully_evaluable(): void
    {
        [$c56, $c55, $c54, $c53, $c52, $out] = $this->fixture('missing-market-index');
        $service = new WatchlistBacktestC57RegimeFieldReconstructionContinuationIsOnlyService();
        $result = $service->execute($c56, $this->hashFile($c56), $c55, $this->hashFile($c55), sha1_file($c55), $c54, $this->hashFile($c54), sha1_file($c54), $c53, $this->hashFile($c53), sha1_file($c53), $c52, $this->hashFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $out, ['overwrite' => true, 'source_rows' => $this->sourceRows()]);
        $this->assertSame('C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($out), true);
        $this->assertFalse($artifact['market_index_source_discovery_summary']['source_found']);
        $this->assertFalse($artifact['regime_field_reconstruction_summary']['regime_fully_evaluable']);
        $this->assertFalse($artifact['regime_field_reconstruction_summary']['reconstruction_pass']);
        $this->assertSame('C58_MARKET_INDEX_EVIDENCE_EXPANSION_OR_SOURCE_RECONSTRUCTION_IS_ONLY', $artifact['next_step_recommendation']);
        $this->assertNotEmpty($artifact['not_evaluable_reasons']);

        [$c56b, $c55b, $c54b, $c53b, $c52b, $outb] = $this->fixture('future-oos-market-index');
        $result = $service->execute($c56b, $this->hashFile($c56b), $c55b, $this->hashFile($c55b), sha1_file($c55b), $c54b, $this->hashFile($c54b), sha1_file($c54b), $c53b, $this->hashFile($c53b), sha1_file($c53b), $c52b, $this->hashFile($c52b), sha1_file($c52b), '2023-01-02', '2025-05-21', $outb, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'market_index_source_rows' => $this->marketRows(), 'force_future_lookup_detected' => true, 'force_oos_rows_requested' => 1]);
        $artifact = json_decode((string) file_get_contents($outb), true);
        $this->assertFalse($artifact['market_index_asof_safety_results'][0]['validation_pass']);
        $this->assertTrue($artifact['regime_field_reconstruction_summary']['future_lookup_detected']);
        $this->assertSame(1, $artifact['regime_field_reconstruction_summary']['oos_rows_requested']);
        $this->cleanup($c56, $c55, $c54, $c53, $c52, $out, $c56b, $c55b, $c54b, $c53b, $c52b, $outb);
    }

    private function fixture(string $name): array
    {
        $base = storage_path('framework/testing/c57-'.$name.'-');
        $c56 = $base.'c56.json'; $c55 = $base.'c55.json'; $c54 = $base.'c54.json'; $c53 = $base.'c53.json'; $c52 = $base.'c52.json'; $out = $base.'out.json';
        $p56 = $this->c56Payload(); $p56['artifact_hash'] = $this->stableHash($p56);
        $p55 = ['status' => 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C55_ROLLING_STABILITY_GAP_REMAINS', 'next_step_recommendation' => 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY']; $p55['artifact_hash'] = $this->stableHash($p55);
        $p54 = ['status' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C54_ROLLING_STABILITY_GAP_REMAINS', 'next_step_recommendation' => 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY']; $p54['artifact_hash'] = $this->stableHash($p54);
        $p53 = ['status' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED', 'next_step_recommendation' => 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY']; $p53['artifact_hash'] = $this->stableHash($p53);
        $p52 = ['status' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C52_EVIDENCE_EXPANSION_REQUIRED', 'next_step_recommendation' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN', 'sector_metadata_reconstruction_summary' => ['sector_metadata_reconstruction_pass' => true, 'sector_metadata_join_coverage_rate' => 1, 'sector_concentration_evaluable' => true, 'dummy_sector_used' => false], 'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true]]; $p52['artifact_hash'] = $this->stableHash($p52);
        $this->write($c56, $p56); $this->write($c55, $p55); $this->write($c54, $p54); $this->write($c53, $p53); $this->write($c52, $p52);
        return [$c56, $c55, $c54, $c53, $c52, $out];
    }

    private function c56Payload(): array
    {
        $anchors = ['C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION', 'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE', 'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER', 'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER', 'C56_R00_C55_R00_NEAR_PASS_REPLAY_COMPARATOR', 'C56_R01_C55_R01_NEAR_PASS_REPLAY_COMPARATOR', 'C56_R03_C55_R19_LOSS_CLUSTER_REPLAY_COMPARATOR', 'C56_R04_C55_R20_C52_ANCHOR_COMPARATOR_ONLY'];
        $score = [];
        $replay = [];
        $conc = [];
        foreach ($anchors as $i => $code) {
            $rolling = in_array($code, ['C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08', 'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER', 'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER'], true);
            $score[] = ['candidate_code' => $code, 'candidate_role' => strpos($code, 'COMPARATOR') !== false ? 'comparator_only' : 'redesigned_candidate', 'evaluated_picks_count' => 100, 'avg_ret_net' => 0.01, 'median_ret_net' => 0.005, 'win_rate' => 0.55, 'month_win_rate_min' => 0.4, 'max_branch_share' => 0.53, 'max_bucket_share' => 0.53, 'max_sector_share' => 0.15, 'max_ticker_share' => 0.08, 'max_month_share' => 0.08, 'loss_cluster_share' => 0.12, 'quality_pass' => true, 'full_is_stability_pass' => true, 'coverage_pass' => true, 'concentration_validation_pass' => false, 'rolling_validation_pass' => $rolling, 'loo_validation_pass' => $i % 2 === 0, 'regime_robustness_validation_pass' => false, 'regime_fully_evaluable' => false, 'material_selection_difference_pass' => true, 'anti_shared_core_pass' => true, 'overall_is_redesign_pass' => false, 'anti_overfit_pass' => false, 'candidate_ready_for_c57' => false, 'failure_reason_codes' => ['C56_REGIME_FIELD_NOT_EVALUABLE']];
            $replay[] = ['candidate_code' => $code, 'row_count' => 100, 'evaluated_picks_count' => 100, 'quality_pass' => true, 'full_is_stability_pass' => true, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_return_used_for_selection' => false, 'production_ready' => false];
            $conc[] = ['candidate_code' => $code, 'max_ticker_share' => 0.08, 'max_sector_share' => 0.15, 'max_bucket_share' => 0.53, 'max_branch_share' => 0.53, 'max_month_share' => 0.08, 'unique_ticker_count' => 40, 'unique_sector_count' => 10, 'unique_bucket_count' => 2, 'unique_branch_count' => 3, 'loss_cluster_share' => 0.12, 'concentration_validation_pass' => false, 'failure_reason_codes' => ['C56_CONCENTRATION_GAP_REMAINS']];
        }
        $coverage = [];
        foreach (['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio'] as $field) {
            $isMarket = in_array($field, ['market_index_roc20', 'market_index_ma20_slope_pct'], true);
            $coverage[] = ['field_name' => $field, 'required' => true, 'rows_required' => 6, 'rows_available' => $isMarket ? 0 : 6, 'coverage_rate' => $isMarket ? 0 : 1, 'asof_safe' => true, 'future_lookup_detected' => false, 'oos_rows_requested' => 0, 'reconstruction_pass' => ! $isMarket, 'failure_reason_codes' => $isMarket ? ['C56_REGIME_FIELD_NOT_EVALUABLE'] : []];
        }
        return [
            'run_code' => 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY',
            'status' => 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED',
            'artifact_type' => 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY',
            'production_ready' => false,
            'diagnostic_conclusion' => 'C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS',
            'next_step_recommendation' => 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY',
            'regime_field_reconstruction_summary' => ['regime_field_reconstruction_attempted' => true, 'required_field_count' => 9, 'evaluable_field_count' => 7, 'missing_field_count' => 2, 'regime_field_coverage_min' => 0, 'regime_fully_evaluable' => false, 'market_index_regime_fields_reconstructed' => false, 'asof_safe' => true, 'future_lookup_detected' => false, 'oos_rows_requested' => 0, 'reconstruction_pass' => false, 'failure_reason_codes' => ['C56_REGIME_FIELD_NOT_EVALUABLE']],
            'regime_field_coverage_results' => $coverage,
            'missing_regime_field_results' => [['field_name' => 'market_index_roc20'], ['field_name' => 'market_index_ma20_slope_pct']],
            'source_reconstruction_summary' => ['source_rows_available' => true, 'source_mode' => 'C28_PICK_DIAGNOSTIC_ROWS', 'reconstructed_source_row_count' => 6, 'fields_present' => ['sector_roc20','rs_20_vs_ihsg','rs_20_vs_sector','roc20','ma20_slope_pct','atr14_pct','vol_ratio'], 'read_only' => true, 'asof_safe' => true, 'source_bias_validation_pass' => true, 'oos_rows_requested' => 0],
            'redesign_candidate_definitions' => array_map(fn ($c) => ['candidate_code' => $c, 'selection_rule_description' => 'fixture', 'safe_pre_trade_fields_used' => ['market_index_roc20']], $anchors),
            'candidate_replay_results' => $replay,
            'concentration_dependency_validation_results' => $conc,
            'rolling_validation_results' => [],
            'rolling_validation_summary' => ['rolling_candidate_count' => 26, 'rolling_full_pass_required' => true, 'candidate_full_rolling_pass_count' => 4],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => ['loo_candidate_count' => 26, 'loo_validation_required' => true, 'candidate_loo_pass_count' => 2],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => ['regime_candidate_count' => 26, 'regime_validation_required' => true, 'candidate_regime_pass_count' => 0, 'regime_fully_evaluable' => false],
            'material_difference_validation_results' => [],
            'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true, 'read_only' => true, 'asof_safe' => true],
            'candidate_scorecard' => $score,
            'selected_c56_candidates_for_c57' => ['candidate_count' => 0, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'c57_readiness_decision' => ['validation_completed' => true, 'candidate_ready_for_c57_count' => 0, 'candidate_codes' => [], 'rolling_validation_pass_candidate_count' => 4, 'concentration_validation_pass_candidate_count' => 0, 'loss_cluster_pass_candidate_count' => 0, 'c57_recommendation' => 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY', 'decision_reason' => 'regime_field_reconstruction_not_fully_evaluable', 'diagnostic_conclusion' => 'C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
        ];
    }

    private function sourceRows(): array
    {
        $rows = [];
        foreach (range(1, 6) as $i) {
            $date = '2023-01-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $rows[] = ['trade_date' => $date, 'signal_date' => $date, 'trade_month' => '2023-01', 'ticker' => 'T'.$i, 'ticker_id' => $i, 'sector_code' => 'S'.(($i % 3) + 1), 'sector_name' => 'Sector', 'selected_source_code' => $i % 2 ? 'G21' : 'G16', 'bucket_code' => $i % 2 ? 'B1' : 'B2', 'row_code' => 'R'.$i, 'sector_roc20' => 0.01, 'rs_20_vs_ihsg' => 0.02, 'rs_20_vs_sector' => 0.03, 'roc20' => 0.04, 'ma20_slope_pct' => 0.005, 'atr14_pct' => 0.02, 'vol_ratio' => 1.1];
        }
        return $rows;
    }

    private function marketRows(): array
    {
        $rows = [];
        foreach (range(1, 6) as $i) {
            $rows[] = ['trade_date' => '2023-01-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'market_index_roc20' => 0.01, 'market_index_ma20_slope_pct' => 0.002];
        }
        return $rows;
    }

    private function path(string $name): string { return storage_path('framework/testing/c57-'.$name); }
    private function write(string $path, array $payload): void { if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); } file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function hashFile(string $path): string { return $this->stableHash(json_decode((string) file_get_contents($path), true)); }
    private function setNested(array &$payload, string $path, $value): void { $ref =& $payload; $parts = explode('.', $path); foreach ($parts as $i => $part) { if ($i === count($parts) - 1) { $ref[$part] = $value; return; } if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; } $ref =& $ref[$part]; } }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
