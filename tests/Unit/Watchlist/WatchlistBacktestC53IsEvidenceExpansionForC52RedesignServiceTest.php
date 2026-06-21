<?php

use App\Application\Watchlist\Services\WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService;

class WatchlistBacktestC53IsEvidenceExpansionForC52RedesignServiceTest extends TestCase
{
    public function test_it_blocks_missing_and_hash_mismatched_C52_artifact(): void
    {
        $service = new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService(); $output = $this->path('missing-output.json');
        $result = $service->execute($this->path('missing-c52.json'), 'hash', 'sha1', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C53_BLOCKED_MISSING_C52_ARTIFACT', $result['status']);
        [$c52, $validOutput] = $this->fixture('hash');
        $result = $service->execute($c52, 'wrong', sha1_file($c52), '2023-01-02', '2025-05-21', $validOutput, ['overwrite' => true]);
        $this->assertSame('C53_BLOCKED_C52_HASH_MISMATCH', $result['status']);
        $this->cleanup($output, $c52, $validOutput);
    }

    public function test_it_blocks_C52_file_sha1_mismatch(): void
    {
        [$c52, $output] = $this->fixture('file-sha');
        $result = (new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService())->execute($c52, $this->hashFromFile($c52), str_repeat('0', 40), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C53_BLOCKED_C52_FILE_SHA1_MISMATCH', $result['status']);
        $this->cleanup($c52, $output);
    }

    public function test_it_blocks_invalid_C52_contract_and_safety_inputs(): void
    {
        $cases = [
            ['status', 'C52_NOT_COMPLETED', 'C53_BLOCKED_UNEXPECTED_C52_STATUS'],
            ['diagnostic_conclusion', 'C52_RANDOM', 'C53_BLOCKED_UNEXPECTED_C52_CONCLUSION'],
            ['next_step_recommendation', 'C53_RANDOM', 'C53_BLOCKED_C52_NEXT_STEP_UNEXPECTED'],
            ['production_ready', true, 'C53_BLOCKED_C52_PRODUCTION_READY_NOT_FALSE'],
            ['c53_readiness_decision.direct_oos_proof_recommended', true, 'C53_BLOCKED_C52_OOS_PROOF_FLAG_INVALID'],
            ['c53_readiness_decision.oos_proof_unlocked', true, 'C53_BLOCKED_C52_OOS_PROOF_FLAG_INVALID'],
            ['sector_metadata_reconstruction_summary.sector_metadata_reconstruction_pass', false, 'C53_BLOCKED_C52_SECTOR_METADATA_NOT_VALID'],
            ['source_reconstruction_bias_check.source_bias_validation_pass', false, 'C53_BLOCKED_C52_SOURCE_BIAS_NOT_VALID'],
        ];
        foreach ($cases as $index => $case) {
            [$c52, $output] = $this->fixture('boundary-'.$index); $artifact = json_decode((string) file_get_contents($c52), true); $this->setNested($artifact, $case[0], $case[1]); $artifact['artifact_hash'] = $this->stableHash($artifact); $this->writeJson($c52, $artifact);
            $result = (new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService())->execute($c52, $artifact['artifact_hash'], sha1_file($c52), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]); $this->cleanup($c52, $output);
        }
    }

    public function test_it_blocks_period_touching_reserved_OOS(): void
    {
        [$c52, $output] = $this->fixture('oos');
        $result = (new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService())->execute($c52, $this->hashFromFile($c52), sha1_file($c52), '2025-05-22', '2025-06-30', $output, ['overwrite' => true]);
        $this->assertSame('C53_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']); $this->cleanup($c52, $output);
    }

    public function test_valid_C52_evidence_completes_C53_and_identifies_rolling_stability_gap(): void
    {
        [$c52, $output] = $this->fixture('completed');
        $result = (new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService())->execute($c52, $this->hashFromFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00']);
        $this->assertSame('C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED', $result['status']); $this->assertSame(0, $result['production_ready']);
        $out = json_decode((string) file_get_contents($output), true);
        foreach (['c52_carry_forward_summary', 'locked_lineage_summary', 'evidence_expansion_thresholds', 'review_cohort_definition', 'review_cohort_results', 'candidate_failure_inventory', 'rolling_evidence_expansion_results', 'rolling_evidence_expansion_summary', 'leave_one_month_out_evidence_results', 'leave_one_month_out_evidence_summary', 'adverse_month_attribution_results', 'regime_field_availability_matrix', 'regime_evidence_expansion_summary', 'structural_guard_preservation_audit', 'cross_layer_corroboration_results', 'c54_readiness_decision', 'candidate_safety_audit', 'diagnostics'] as $key) { $this->assertNotEmpty($out[$key], $key); }
        $this->assertSame('C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED', $out['diagnostic_conclusion']);
        $this->assertSame('C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY', $out['next_step_recommendation']);
        $this->assertSame(1, $out['c54_readiness_decision']['review_cohort_candidate_count']);
        $this->assertSame(0, $out['c54_readiness_decision']['candidate_ready_for_c54_count']);
        $this->assertSame('ROLLING_STABILITY', $out['c54_readiness_decision']['primary_evidence_gap']);
        $this->assertFalse($out['c54_readiness_decision']['direct_oos_proof_recommended']); $this->assertFalse($out['c54_readiness_decision']['oos_proof_unlocked']); $this->assertFalse($out['c54_readiness_decision']['production_ready']);
        $this->cleanup($c52, $output);
    }

    public function test_cohort_membership_and_safety_do_not_use_returns_or_form_a_winner(): void
    {
        [$c52, $output] = $this->fixture('safety');
        (new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService())->execute($c52, $this->hashFromFile($c52), sha1_file($c52), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $out = json_decode((string) file_get_contents($output), true); $definition = $out['review_cohort_definition'];
        $this->assertFalse($definition['return_used_for_cohort_membership']); $this->assertFalse($definition['candidate_winner_selected']); $this->assertFalse($definition['new_candidate_formed']);
        $this->assertNotContains('avg_ret_net', $definition['membership_fields']); $this->assertContains('avg_ret_net', $definition['excluded_membership_fields']);
        foreach ($out['review_cohort_results'] as $row) { $this->assertFalse($row['return_used_for_cohort_membership']); $this->assertFalse($row['future_path_used_for_cohort_membership']); $this->assertFalse($row['oos_return_used_for_cohort_membership']); }
        foreach ($out['candidate_safety_audit'] as $row) { $this->assertTrue($row['passed']); $this->assertFalse($row['return_used_for_selection']); $this->assertFalse($row['future_path_used_for_selection']); $this->assertFalse($row['oos_data_used_for_tuning']); }
        $this->cleanup($c52, $output);
    }

    private function fixture(string $suffix): array
    {
        $c52 = $this->path($suffix.'-c52.json'); $output = $this->path($suffix.'-output.json'); $artifact = $this->c52Artifact(); $artifact['artifact_hash'] = $this->stableHash($artifact); $this->writeJson($c52, $artifact); return [$c52, $output];
    }

    private function c52Artifact(): array
    {
        $candidate = 'C52_R02_C51_R08_REPLAY_SECTOR_FIXED';
        $rolling = [];
        foreach ([['ROLLING_6M_STEP_1M_1', '2023-01-01', '2023-06-31'], ['EARLY_IS', '2023-01-01', '2023-09-31']] as $window) { $rolling[] = ['validation_window_code' => $window[0], 'window_from' => $window[1], 'window_to' => $window[2], 'candidate_code' => $candidate, 'avg_ret_net' => 0.003, 'median_ret_net' => 0.004, 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'failure_reason_codes' => ['C52_ROLLING_STABILITY_FAIL']]; }
        $loo = [
            ['exclude_month' => '2023-01', 'candidate_code' => $candidate, 'quality_delta' => 0.001, 'stability_delta' => 0.002, 'rank_stable' => false, 'dependency_on_excluded_month' => false],
            ['exclude_month' => '2023-02', 'candidate_code' => $candidate, 'quality_delta' => -0.001, 'stability_delta' => 0.0, 'rank_stable' => true, 'dependency_on_excluded_month' => false],
        ];
        $regime = [];
        foreach (['sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'] as $field) { foreach (['lt_0', 'gte_0'] as $bucket) { $regime[] = ['candidate_code' => $candidate, 'regime_field' => $field, 'regime_bucket' => $bucket, 'avg_ret_net' => 0.002, 'win_rate' => 0.55, 'regime_bucket_pass' => true]; } }
        return [
            'run_code' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION', 'status' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C52_EVIDENCE_EXPANSION_REQUIRED', 'next_step_recommendation' => 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN',
            'expected_c51_hash' => 'h51', 'actual_c51_hash' => 'h51', 'c51_hash_match' => true, 'expected_c50_hash' => 'h50', 'actual_c50_hash' => 'h50', 'c50_hash_match' => true, 'expected_c49_hash' => 'h49', 'actual_c49_hash' => 'h49', 'c49_hash_match' => true,
            'sector_metadata_reconstruction_summary' => ['sector_metadata_reconstruction_pass' => true, 'sector_metadata_join_coverage_rate' => 1.0], 'source_reconstruction_bias_check' => ['source_bias_validation_pass' => true],
            'selected_c52_candidates_for_c53' => ['selected_candidate_count' => 0, 'best_redesigned_candidate_code' => null], 'c53_readiness_decision' => ['primary_dependency_reduced' => true, 'anti_overfit_pass' => false, 'decision_reason' => 'candidate_promising_but_needs_evidence_expansion', 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_scorecard' => [
                ['candidate_code' => $candidate, 'candidate_role' => 'c51_replay', 'quality_pass' => true, 'stability_pass' => false, 'coverage_pass' => true, 'concentration_validation_pass' => true, 'rolling_validation_pass' => false, 'loo_validation_pass' => false, 'regime_robustness_validation_pass' => true, 'material_selection_difference_pass' => true, 'source_bias_validation_pass' => true],
                ['candidate_code' => 'C52_R19_COMPARATOR', 'candidate_role' => 'comparator_only', 'quality_pass' => true, 'stability_pass' => true, 'coverage_pass' => true, 'concentration_validation_pass' => false, 'material_selection_difference_pass' => false, 'source_bias_validation_pass' => true],
            ],
            'concentration_dependency_validation_results' => [['candidate_code' => $candidate, 'max_ticker_share' => 0.05, 'max_sector_share' => 0.18, 'max_bucket_share' => 0.51, 'max_branch_share' => 0.49, 'max_month_share' => 0.04, 'loss_cluster_share' => 0.08, 'sector_metadata_coverage_rate' => 1.0, 'sector_concentration_evaluable' => true, 'concentration_validation_pass' => true]],
            'rolling_validation_results' => $rolling, 'leave_one_month_out_results' => $loo, 'regime_robustness_validation_results' => $regime,
            'not_evaluable_reasons' => [
                ['validation_layer' => 'regime_robustness', 'validation_slice' => $candidate.'|market_index_roc20', 'reason_code' => 'C52_REGIME_FIELD_NOT_EVALUABLE', 'message' => 'C52 regime field unavailable.'],
                ['validation_layer' => 'regime_robustness', 'validation_slice' => $candidate.'|market_index_ma20_slope_pct', 'reason_code' => 'C52_REGIME_FIELD_NOT_EVALUABLE', 'message' => 'C52 regime field unavailable.'],
            ],
        ];
    }

    private function path(string $name): string { return storage_path('framework/testing/c53-'.$name); }
    private function writeJson(string $path, array $payload): void { if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); } file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function hashFromFile(string $path): string { return $this->stableHash(json_decode((string) file_get_contents($path), true)); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function setNested(array &$artifact, string $path, $value): void { $ref =& $artifact; foreach (explode('.', $path) as $part) { if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; } $ref =& $ref[$part]; } $ref = $value; }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
