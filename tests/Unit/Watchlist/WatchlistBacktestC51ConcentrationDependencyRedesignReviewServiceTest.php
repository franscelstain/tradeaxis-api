<?php

use App\Application\Watchlist\Services\WatchlistBacktestC51ConcentrationDependencyRedesignReviewService;

class WatchlistBacktestC51ConcentrationDependencyRedesignReviewServiceTest extends TestCase
{
    public function test_it_blocks_missing_C50_artifact(): void
    {
        $output = $this->path('missing-c50-output.json');
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($this->path('missing-c50.json'), 'hash', $this->path('missing-c49.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C51_BLOCKED_MISSING_C50_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_expected_C50_hash_mismatch(): void
    {
        [$c50, $c49, $output] = $this->writeFixtures('c50-hash-mismatch');
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, 'wrong-hash', $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C51_BLOCKED_C50_HASH_MISMATCH', $result['status']);
        $this->cleanup($c50, $c49, $output);
    }

    public function test_it_blocks_invalid_C50_boundary_inputs(): void
    {
        $cases = [
            ['status', 'C50_NOT_COMPLETED', 'C51_BLOCKED_UNEXPECTED_C50_STATUS'],
            ['diagnostic_conclusion', 'C50_RANDOM_CONCLUSION', 'C51_BLOCKED_UNEXPECTED_C50_CONCLUSION'],
            ['production_ready', true, 'C51_BLOCKED_C50_PRODUCTION_READY_NOT_FALSE'],
            ['next_step_recommendation', 'C52_DIRECT_OOS_PROOF', 'C51_BLOCKED_C50_NEXT_STEP_UNEXPECTED'],
            ['c51_readiness_decision.direct_oos_proof_recommended', true, 'C51_BLOCKED_C50_OOS_PROOF_FLAG_INVALID'],
            ['c51_readiness_decision.oos_proof_unlocked', true, 'C51_BLOCKED_C50_OOS_PROOF_FLAG_INVALID'],
        ];

        foreach ($cases as $index => $case) {
            [$c50, $c49, $output] = $this->writeFixtures('c50-boundary-'.$index);
            $artifact = json_decode((string) file_get_contents($c50), true);
            $this->setNested($artifact, $case[0], $case[1]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($c50, $artifact);
            $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $artifact['artifact_hash'], $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c50, $c49, $output);
        }
    }

    public function test_it_blocks_when_C50_concentration_failure_is_missing(): void
    {
        [$c50, $c49, $output] = $this->writeFixtures('missing-concentration-failure');
        $artifact = json_decode((string) file_get_contents($c50), true);
        $artifact['candidate_validation_scorecard'][0]['failure_reason_codes'] = [];
        $artifact['concentration_dependency_validation_results'][0]['concentration_validation_pass'] = true;
        $artifact['concentration_dependency_validation_results'][0]['failure_reason_codes'] = [];
        $artifact['concentration_dependency_validation_results'][0]['max_branch_share'] = 0.60;
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($c50, $artifact);
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $artifact['artifact_hash'], $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C51_BLOCKED_MISSING_C50_CONCENTRATION_FAILURE', $result['status']);
        $this->cleanup($c50, $c49, $output);
    }

    public function test_it_blocks_missing_and_hash_mismatched_C49_artifact_after_valid_C50(): void
    {
        [$c50, $c49, $output] = $this->writeFixtures('missing-c49');
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $this->hashFromFile($c50), $this->path('missing-c49.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C51_BLOCKED_MISSING_C49_ARTIFACT', $result['status']);

        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $this->hashFromFile($c50), $c49, 'wrong-hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C51_BLOCKED_C49_HASH_MISMATCH', $result['status']);
        $this->cleanup($c50, $c49, $output);
    }

    public function test_it_blocks_when_validation_period_touches_OOS_reserved_window(): void
    {
        [$c50, $c49, $output] = $this->writeFixtures('oos-period');
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2025-05-22', '2025-06-30', $output, ['overwrite' => true, 'source_rows' => $this->sourceRows(), 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C51_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c50, $c49, $output);
    }

    public function test_valid_C50_and_C49_artifacts_complete_C51_redesign_review_without_OOS_tuning(): void
    {
        [$c50, $c49, $output] = $this->writeFixtures('completed');
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, [
            'overwrite' => true,
            'executed_at' => '2026-06-21T00:00:00+00:00',
            'source_rows' => $this->sourceRows(),
            'pre_trade_source_rows' => $this->preTradeRows(),
        ]);

        $this->assertSame('C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($output);
        $out = json_decode((string) file_get_contents($output), true);

        $this->assertSame('C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW', $out['run_code']);
        $this->assertSame('C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW', $out['artifact_type']);
        $this->assertFalse($out['production_ready']);
        $this->assertTrue($out['c50_hash_match']);
        $this->assertTrue($out['c49_hash_match']);
        $this->assertSame('C50_IS_VALIDATION_COMPLETED', $out['c50_status']);
        $this->assertSame('is_only_concentration_dependency_redesign_review', $out['is_validation_period']['purpose']);
        $this->assertFalse($out['is_validation_period']['oos_data_used_for_tuning']);
        $this->assertFalse($out['is_validation_period']['oos_return_used_for_selection']);
        $this->assertFalse($out['is_validation_period']['oos_proof_executed']);
        $this->assertFalse($out['oos_reserved_period']['used_for_selection']);
        $this->assertFalse($out['oos_reserved_period']['used_for_tuning']);
        $this->assertFalse($out['oos_reserved_period']['used_for_proof']);

        foreach (['c50_carry_forward_summary', 'c50_root_cause_summary', 'source_reconstruction_summary', 'redesign_candidate_definitions', 'candidate_replay_results', 'concentration_dependency_validation_results', 'branch_dependency_validation_results', 'bucket_dependency_validation_results', 'rolling_validation_results', 'rolling_validation_summary', 'leave_one_month_out_results', 'leave_one_month_out_summary', 'regime_robustness_validation_results', 'regime_robustness_validation_summary', 'material_difference_validation_results', 'source_reconstruction_bias_check', 'candidate_scorecard', 'selected_c51_candidates_for_c52', 'c52_readiness_decision', 'candidate_safety_audit', 'diagnostic_conclusion'] as $key) {
            $this->assertNotEmpty($out[$key], $key);
        }
        $this->assertIsArray($out['not_evaluable_reasons']);
        $this->assertSame('F03_G16_BRANCH_BUCKET_CONCENTRATION', $out['c50_root_cause_summary']['c50_root_cause']);
        $this->assertTrue($out['c50_root_cause_summary']['c50_concentration_failure_confirmed']);
        $this->assertCount(14, $out['redesign_candidate_definitions']);

        foreach ($out['redesign_candidate_definitions'] as $definition) {
            $this->assertFalse($definition['return_used_for_selection']);
            $this->assertFalse($definition['future_path_used_for_selection']);
            $this->assertFalse($definition['oos_return_used_for_selection']);
            $this->assertNotContains('profile_ret_net', $definition['safe_pre_trade_fields_used']);
        }
        foreach ($out['candidate_safety_audit'] as $audit) {
            $this->assertTrue($audit['passed']);
            $this->assertFalse($audit['return_used_for_selection']);
            $this->assertFalse($audit['future_path_used_for_selection']);
            $this->assertFalse($audit['oos_data_used_for_tuning']);
        }
        $this->assertFalse($out['c52_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($out['c52_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($out['c52_readiness_decision']['production_ready']);
        $this->assertFalse($out['safety_boundaries']['oos_data_used_for_tuning']);
        $this->assertFalse($out['safety_boundaries']['oos_return_used_for_candidate_selection']);
        $this->assertFalse($out['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($out['safety_boundaries']['future_path_used_for_selection']);
        $this->assertTrue($out['safety_boundaries']['no_oos_proof']);
        $this->assertTrue($out['safety_boundaries']['no_production_catalog']);
        $this->cleanup($c50, $c49, $output);
    }

    public function test_source_universe_or_not_evaluable_reason_is_recorded_when_source_rows_are_missing(): void
    {
        [$c50, $c49, $output] = $this->writeFixtures('missing-source');
        $result = (new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService())->execute($c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => [], 'pre_trade_source_rows' => []]);
        $this->assertSame('C51_SOURCE_ROWS_NOT_EVALUABLE', $result['status']);
        $out = json_decode((string) file_get_contents($output), true);
        $this->assertContains('C51_SOURCE_ROWS_NOT_EVALUABLE', array_column($out['not_evaluable_reasons'], 'reason_code'));
        $this->assertNotEmpty($out['source_reconstruction_bias_check']);
        $this->cleanup($c50, $c49, $output);
    }

    private function writeFixtures(string $suffix): array
    {
        $c50 = $this->path($suffix.'-c50.json');
        $c49 = $this->path($suffix.'-c49.json');
        $output = $this->path($suffix.'-output.json');
        $c50Artifact = $this->c50Artifact();
        $c49Artifact = $this->c49Artifact();
        $c50Artifact['artifact_hash'] = $this->stableHash($c50Artifact);
        $c49Artifact['artifact_hash'] = $this->stableHash($c49Artifact);
        $this->writeJson($c50, $c50Artifact);
        $this->writeJson($c49, $c49Artifact);
        return [$c50, $c49, $output];
    }

    private function c50Artifact(): array
    {
        return [
            'run_code' => 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK',
            'status' => 'C50_IS_VALIDATION_COMPLETED',
            'artifact_type' => 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK',
            'production_ready' => false,
            'diagnostic_conclusion' => 'C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED',
            'next_step_recommendation' => 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW',
            'selected_c50_candidates_for_c51' => [
                'primary_candidate_code' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL',
                'defensive_comparator_code' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN',
            ],
            'c51_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_validation_scorecard' => [
                ['candidate_code' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL', 'avg_ret_net' => 0.01, 'median_ret_net' => 0.015, 'win_rate' => 0.70, 'overall_is_validation_pass' => false, 'anti_overfit_pass' => false, 'failure_reason_codes' => ['C50_CONCENTRATION_DEPENDENCY_FAIL']],
                ['candidate_code' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN', 'avg_ret_net' => 0.004, 'failure_reason_codes' => ['C50_STABILITY_FAIL']],
                ['candidate_code' => 'C49_CANDIDATE_F00_C44_SHARED_CORE_COMPARATOR', 'anti_overfit_pass' => true, 'material_selection_difference_pass' => false],
            ],
            'concentration_dependency_validation_results' => [
                ['candidate_code' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL', 'max_branch_share' => 0.92, 'max_bucket_share' => 0.92, 'loss_cluster_share' => 0.13, 'concentration_validation_pass' => false, 'failure_reason_codes' => ['C50_CONCENTRATION_DEPENDENCY_WARNING']],
                ['candidate_code' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN', 'max_branch_share' => 0.54, 'max_bucket_share' => 0.54, 'loss_cluster_share' => 0.08, 'concentration_validation_pass' => true, 'failure_reason_codes' => []],
            ],
            'branch_dependency_validation_results' => [
                ['candidate_code' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL', 'branch_code' => 'G16', 'branch_row_count' => 1320, 'branch_share' => 0.92],
                ['candidate_code' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL', 'branch_code' => 'G21', 'branch_row_count' => 112, 'branch_share' => 0.08],
                ['candidate_code' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN', 'branch_code' => 'G16', 'branch_row_count' => 375, 'branch_share' => 0.54],
            ],
        ];
    }

    private function c49Artifact(): array
    {
        return [
            'run_code' => 'C49_BROADER_STRATEGY_REDESIGN',
            'status' => 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED',
            'artifact_type' => 'C49_BROADER_STRATEGY_REDESIGN',
            'production_ready' => false,
            'diagnostic_conclusion' => 'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION',
            'next_step_recommendation' => 'C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN',
            'source_universe_summary' => ['source_evidence_artifact' => 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json'],
            'selected_c49_candidates_for_c50' => ['primary_candidate' => 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL', 'defensive_comparator' => 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN'],
        ];
    }

    private function sourceRows(): array
    {
        $rows = [];
        $months = ['2023-01', '2023-02', '2023-03', '2023-04', '2023-05', '2023-06', '2023-07', '2023-08', '2023-09'];
        $sectors = ['IDXTECHNO', 'IDXFINANCE', 'IDXENERGY', 'IDXHEALTH'];
        foreach ($months as $monthIndex => $month) {
            for ($i = 1; $i <= 22; $i++) {
                $rows[] = $this->row($month, $i, 'G16', 'next_open_delay_after_close_signal', $this->ret($i, $monthIndex), $sectors[$i % 4]);
                $rows[] = $this->row($month, $i + 100, 'G21', 'no_rule_profit_signal_before_fallback', $this->ret($i + 100, $monthIndex), $sectors[$i % 4]);
            }
            for ($i = 1; $i <= 12; $i++) {
                $rows[] = $this->row($month, $i + 200, 'G13', 'no_rule_profit_signal_before_fallback', $this->ret($i + 200, $monthIndex), $sectors[$i % 4]);
            }
        }
        return $rows;
    }

    private function preTradeRows(): array
    {
        return array_map(function (array $row): array {
            return [
                'trade_date' => $row['trade_date'], 'ticker' => $row['ticker'], 'ticker_id' => $row['ticker_id'], 'dv20_idr' => $row['dv20_idr'],
                'atr14_pct' => $row['atr14_pct'], 'vol_ratio' => $row['vol_ratio'], 'roc20' => $row['roc20'], 'ma20_slope_pct' => $row['ma20_slope_pct'],
                'rs_20_vs_ihsg' => $row['rs_20_vs_ihsg'], 'rs_20_vs_sector' => $row['rs_20_vs_sector'],
            ];
        }, $this->sourceRows());
    }

    private function row(string $month, int $i, string $branch, string $bucket, float $ret, string $sector): array
    {
        return [
            'trade_date' => $month.'-'.str_pad((string) (($i % 20) + 1), 2, '0', STR_PAD_LEFT),
            'trade_month' => $month,
            'ticker' => 'T'.str_pad((string) ($i % 65), 3, '0', STR_PAD_LEFT),
            'ticker_id' => $i,
            'sector_code' => $sector,
            'selected_source_code' => $branch,
            'bucket_code' => $bucket,
            'param_id' => $i % 5,
            'row_code' => 'R'.$i,
            'profile_ret_net' => $ret,
            'market_index_roc20' => 0.02 + (($i % 3) * 0.01),
            'market_index_ma20_slope_pct' => 0.01,
            'sector_roc20' => 0.02,
            'rs_20_vs_ihsg' => 0.01,
            'rs_20_vs_sector' => 0.01,
            'roc20' => 0.03,
            'ma20_slope_pct' => 0.01,
            'atr14_pct' => 0.03,
            'vol_ratio' => 1.2,
            'dv20_idr' => 1000000000 + $i,
        ];
    }

    private function ret(int $i, int $monthIndex): float
    {
        if ($monthIndex === 2 && $i % 9 === 0) { return -0.012; }
        return ($i % 5 === 0) ? -0.003 : 0.012 + (($i % 4) * 0.001);
    }

    private function path(string $name): string
    {
        return storage_path('framework/testing/'.$name);
    }

    private function writeJson(string $path, array $payload): void
    {
        if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); }
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function hashFromFile(string $path): string
    {
        return $this->stableHash(json_decode((string) file_get_contents($path), true));
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function setNested(array &$artifact, string $path, $value): void
    {
        $ref =& $artifact;
        $parts = explode('.', $path);
        foreach ($parts as $part) {
            if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; }
            $ref =& $ref[$part];
        }
        $ref = $value;
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) { @unlink($path); }
    }
}
