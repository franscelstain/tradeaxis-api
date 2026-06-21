<?php

use App\Application\Watchlist\Services\WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService;

class WatchlistBacktestC52ConcentrationDependencyRedesignContinuationServiceTest extends TestCase
{
    public function test_it_blocks_missing_and_hash_mismatched_C51_artifact(): void
    {
        $service = new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService();
        $missingOutput = $this->path('c52-missing-c51-output.json');
        $result = $service->execute($this->path('missing-c51.json'), 'hash', $this->path('missing-c50.json'), 'hash', $this->path('missing-c49.json'), 'hash', '2023-01-02', '2025-05-21', $missingOutput, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_MISSING_C51_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);

        [$c51, $c50, $c49, $output] = $this->writeFixtures('c51-hash');
        $result = $service->execute($c51, 'wrong-hash', $c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_C51_HASH_MISMATCH', $result['status']);
        $this->cleanup($missingOutput, $c51, $c50, $c49, $output);
    }

    public function test_it_blocks_invalid_C51_boundary_and_continuation_inputs(): void
    {
        $cases = [
            ['status', 'C51_NOT_COMPLETED', 'C52_BLOCKED_UNEXPECTED_C51_STATUS'],
            ['diagnostic_conclusion', 'C51_RANDOM', 'C52_BLOCKED_UNEXPECTED_C51_CONCLUSION'],
            ['production_ready', true, 'C52_BLOCKED_C51_PRODUCTION_READY_NOT_FALSE'],
            ['next_step_recommendation', 'C52_DIRECT_OOS_PROOF', 'C52_BLOCKED_C51_NEXT_STEP_UNEXPECTED'],
            ['c52_readiness_decision.direct_oos_proof_recommended', true, 'C52_BLOCKED_C51_OOS_PROOF_FLAG_INVALID'],
            ['c52_readiness_decision.oos_proof_unlocked', true, 'C52_BLOCKED_C51_OOS_PROOF_FLAG_INVALID'],
        ];
        foreach ($cases as $index => $case) {
            [$c51, $c50, $c49, $output] = $this->writeFixtures('boundary-'.$index);
            $artifact = json_decode((string) file_get_contents($c51), true);
            $this->setNested($artifact, $case[0], $case[1]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($c51, $artifact);
            $result = (new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService())->execute($c51, $artifact['artifact_hash'], $c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c51, $c50, $c49, $output);
        }

        [$c51, $c50, $c49, $output] = $this->writeFixtures('missing-continuation');
        $artifact = json_decode((string) file_get_contents($c51), true);
        $artifact['c52_readiness_decision']['decision_reason'] = 'unrelated';
        $artifact['c52_readiness_decision']['concentration_validation_pass'] = true;
        $artifact['concentration_dependency_validation_results'] = [];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $this->writeJson($c51, $artifact);
        $result = (new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService())->execute($c51, $artifact['artifact_hash'], $c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_MISSING_C51_CONCENTRATION_CONTINUATION_REASON', $result['status']);
        $this->cleanup($c51, $c50, $c49, $output);
    }

    public function test_it_blocks_missing_or_hash_mismatched_C50_and_C49_artifacts(): void
    {
        [$c51, $c50, $c49, $output] = $this->writeFixtures('lineage-locks');
        $service = new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService();
        $result = $service->execute($c51, $this->hashFromFile($c51), $this->path('missing-c50.json'), 'hash', $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_MISSING_C50_ARTIFACT', $result['status']);
        $result = $service->execute($c51, $this->hashFromFile($c51), $c50, 'wrong', $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_C50_HASH_MISMATCH', $result['status']);
        $result = $service->execute($c51, $this->hashFromFile($c51), $c50, $this->hashFromFile($c50), $this->path('missing-c49.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_MISSING_C49_ARTIFACT', $result['status']);
        $result = $service->execute($c51, $this->hashFromFile($c51), $c50, $this->hashFromFile($c50), $c49, 'wrong', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_C49_HASH_MISMATCH', $result['status']);
        $this->cleanup($c51, $c50, $c49, $output);
    }

    public function test_it_blocks_validation_period_that_touches_reserved_OOS(): void
    {
        [$c51, $c50, $c49, $output] = $this->writeFixtures('oos-period');
        $result = (new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService())->execute($c51, $this->hashFromFile($c51), $c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2025-05-22', '2025-06-30', $output, ['overwrite' => true]);
        $this->assertSame('C52_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c51, $c50, $c49, $output);
    }

    public function test_valid_lineage_completes_sector_fixed_second_pass_with_full_diagnostic_structure(): void
    {
        [$c51, $c50, $c49, $output] = $this->writeFixtures('completed');
        $sourceRows = $this->sourceRows();
        $result = (new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService())->execute($c51, $this->hashFromFile($c51), $c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'source_rows' => $sourceRows, 'pre_trade_source_rows' => $sourceRows]);
        $this->assertSame('C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $out = json_decode((string) file_get_contents($output), true);

        foreach (['c51_carry_forward_summary', 'c51_root_cause_summary', 'sector_metadata_reconstruction_summary', 'sector_metadata_source_candidates', 'sector_metadata_selected_source', 'sector_metadata_join_results', 'sector_metadata_validation_results', 'source_reconstruction_summary', 'redesign_candidate_definitions', 'candidate_replay_results', 'concentration_dependency_validation_results', 'branch_dependency_validation_results', 'bucket_dependency_validation_results', 'sector_dependency_validation_results', 'rolling_validation_results', 'rolling_validation_summary', 'leave_one_month_out_results', 'leave_one_month_out_summary', 'regime_robustness_validation_results', 'regime_robustness_validation_summary', 'material_difference_validation_results', 'source_reconstruction_bias_check', 'candidate_scorecard', 'selected_c52_candidates_for_c53', 'c53_readiness_decision', 'candidate_safety_audit', 'diagnostics'] as $key) {
            $this->assertNotEmpty($out[$key], $key);
        }
        $this->assertIsArray($out['sector_metadata_conflict_results']);
        $this->assertIsArray($out['not_evaluable_reasons']);
        $this->assertTrue($out['c51_root_cause_summary']['c51_sector_concentration_evaluation_defect_confirmed']);
        $this->assertTrue($out['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass']);
        $this->assertTrue($out['sector_metadata_reconstruction_summary']['sector_concentration_evaluable']);
        $this->assertFalse($out['sector_metadata_reconstruction_summary']['sector_concentration_not_evaluable']);
        $this->assertEquals(1.0, $out['sector_metadata_reconstruction_summary']['sector_metadata_join_coverage_rate']);
        $this->assertGreaterThanOrEqual(6, $out['sector_metadata_reconstruction_summary']['sector_metadata_unique_sector_count']);
        $this->assertCount(20, $out['redesign_candidate_definitions']);
        $this->assertCount(20, $out['candidate_replay_results']);

        foreach ($out['redesign_candidate_definitions'] as $definition) {
            $this->assertFalse($definition['return_used_for_selection']);
            $this->assertFalse($definition['future_path_used_for_selection']);
            $this->assertFalse($definition['oos_return_used_for_selection']);
            $this->assertNotContains('profile_ret_net', $definition['safe_pre_trade_fields_used']);
        }
        foreach ($out['concentration_dependency_validation_results'] as $row) {
            $this->assertTrue($row['sector_concentration_evaluable']);
            $this->assertFalse($row['sector_concentration_not_evaluable']);
            $this->assertNotSame(0, $row['unique_sector_count']);
        }
        foreach ($out['candidate_safety_audit'] as $audit) { $this->assertTrue($audit['passed']); $this->assertFalse($audit['return_used_for_selection']); $this->assertFalse($audit['future_path_used_for_selection']); $this->assertFalse($audit['oos_data_used_for_tuning']); }
        $this->assertFalse($out['is_validation_period']['oos_data_used_for_tuning']);
        $this->assertFalse($out['is_validation_period']['oos_return_used_for_selection']);
        $this->assertFalse($out['is_validation_period']['oos_proof_executed']);
        $this->assertFalse($out['oos_reserved_period']['used_for_selection']);
        $this->assertFalse($out['oos_reserved_period']['used_for_tuning']);
        $this->assertFalse($out['oos_reserved_period']['used_for_proof']);
        $this->assertFalse($out['production_ready']);
        $this->assertFalse($out['c53_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($out['c53_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($out['c53_readiness_decision']['production_ready']);
        $keys = array_keys($out['safety_boundaries']);
        $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys))));
        $this->cleanup($c51, $c50, $c49, $output);
    }

    public function test_missing_sector_metadata_is_not_evaluable_instead_of_fake_single_sector_failure(): void
    {
        [$c51, $c50, $c49, $output] = $this->writeFixtures('sector-not-evaluable');
        $rows = array_map(function (array $row): array { unset($row['sector_code'], $row['sector_name']); return $row; }, $this->sourceRows());
        $result = (new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService())->execute($c51, $this->hashFromFile($c51), $c50, $this->hashFromFile($c50), $c49, $this->hashFromFile($c49), '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'source_rows' => $rows, 'pre_trade_source_rows' => $rows]);
        $this->assertSame('C52_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED_WITH_SECTOR_NOT_EVALUABLE', $result['status']);
        $out = json_decode((string) file_get_contents($output), true);
        $this->assertFalse($out['sector_metadata_reconstruction_summary']['sector_concentration_evaluable']);
        $this->assertTrue($out['sector_metadata_reconstruction_summary']['sector_concentration_not_evaluable']);
        $this->assertContains('C52_SECTOR_METADATA_NOT_EVALUABLE', array_column($out['not_evaluable_reasons'], 'reason_code'));
        foreach ($out['concentration_dependency_validation_results'] as $row) { $this->assertNull($row['max_sector_share']); $this->assertFalse($row['concentration_validation_pass']); $this->assertSame('not_evaluable', $row['concentration_validation_level']); }
        $this->cleanup($c51, $c50, $c49, $output);
    }

    private function writeFixtures(string $suffix): array
    {
        $paths = [$this->path($suffix.'-c51.json'), $this->path($suffix.'-c50.json'), $this->path($suffix.'-c49.json'), $this->path($suffix.'-output.json')];
        $payloads = [$this->c51Artifact(), $this->c50Artifact(), $this->c49Artifact()];
        foreach ($payloads as $index => $payload) { $payload['artifact_hash'] = $this->stableHash($payload); $this->writeJson($paths[$index], $payload); }
        return $paths;
    }

    private function c51Artifact(): array
    {
        return ['run_code' => 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW', 'status' => 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED', 'artifact_type' => 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW', 'production_ready' => false, 'diagnostic_conclusion' => 'C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS', 'next_step_recommendation' => 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION', 'selected_c51_candidates_for_c52' => ['best_redesigned_candidate_code' => null, 'selected_candidate_count' => 0], 'c52_readiness_decision' => ['decision_reason' => 'concentration_dependency_issue_remains', 'concentration_validation_pass' => false, 'material_difference_validation_pass' => false, 'anti_overfit_pass' => false, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false], 'concentration_dependency_validation_results' => [['candidate_code' => 'C51_R05_F03_G16_DOWNSAMPLED_G21_BACKFILL', 'unique_sector_count' => 0, 'max_sector_share' => 1, 'concentration_validation_pass' => false]]];
    }

    private function c50Artifact(): array { return ['run_code' => 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK', 'status' => 'C50_IS_VALIDATION_COMPLETED', 'production_ready' => false, 'diagnostic_conclusion' => 'C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED', 'next_step_recommendation' => 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW']; }
    private function c49Artifact(): array { return ['run_code' => 'C49_BROADER_STRATEGY_REDESIGN', 'status' => 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED', 'production_ready' => false, 'source_universe_summary' => ['source_evidence_artifact' => 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json']]; }

    private function sourceRows(): array
    {
        $rows = []; $months = ['2023-01', '2023-02', '2023-03', '2023-04', '2023-05', '2023-06', '2023-07', '2023-08', '2023-09']; $sectors = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($months as $monthIndex => $month) {
            foreach ([['G16', 'next_open_delay_after_close_signal', 24, 0], ['G21', 'no_rule_profit_signal_before_fallback', 24, 100], ['G13', 'no_rule_profit_signal_before_fallback', 12, 200]] as $branch) {
                for ($i = 1; $i <= $branch[2]; $i++) { $n = $i + $branch[3]; $rows[] = ['trade_date' => $month.'-'.str_pad((string) (($n % 20) + 1), 2, '0', STR_PAD_LEFT), 'trade_month' => $month, 'ticker' => 'T'.str_pad((string) ($n % 70), 3, '0', STR_PAD_LEFT), 'ticker_id' => $n, 'sector_code' => $sectors[$n % count($sectors)], 'sector_name' => 'Sector '.$sectors[$n % count($sectors)], 'selected_source_code' => $branch[0], 'bucket_code' => $branch[1], 'param_id' => $n % 5, 'row_code' => 'R'.$n, 'profile_ret_net' => ($n % 7 === 0 ? -0.004 : 0.012 + (($n % 3) * 0.001)), 'market_index_roc20' => 0.02, 'market_index_ma20_slope_pct' => 0.01, 'sector_roc20' => 0.02, 'rs_20_vs_ihsg' => 0.01, 'rs_20_vs_sector' => 0.01, 'roc20' => 0.03, 'ma20_slope_pct' => 0.01, 'atr14_pct' => 0.03, 'vol_ratio' => 1.2, 'dv20_idr' => 1000000000 + $n]; }
            }
        }
        return $rows;
    }

    private function path(string $name): string { return storage_path('framework/testing/'.$name); }
    private function writeJson(string $path, array $payload): void { if (! is_dir(dirname($path))) { mkdir(dirname($path), 0775, true); } file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function hashFromFile(string $path): string { return $this->stableHash(json_decode((string) file_get_contents($path), true)); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function setNested(array &$artifact, string $path, $value): void { $ref =& $artifact; foreach (explode('.', $path) as $part) { if (! isset($ref[$part]) || ! is_array($ref[$part])) { $ref[$part] = []; } $ref =& $ref[$part]; } $ref = $value; }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
