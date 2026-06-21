<?php

use App\Application\Watchlist\Services\WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService;

class WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticServiceTest extends TestCase
{
    public function test_it_blocks_when_C37_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c38-missing-c37-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c37-artifact.json',
            WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService::DEFAULT_EXPECTED_C37_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C38_BLOCKED_MISSING_C37_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C38_C37_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C37_hash_mismatches(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('hash-mismatch');
        $c37 = $this->c37Artifact($isPath);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            'wrong-c37-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_C37_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c37_hash_match']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C37_status_is_unexpected(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('status');
        $c37 = $this->c37Artifact($isPath);
        $c37['status'] = 'C37_OPERATOR_VALIDATION_REQUIRED';
        $c37['artifact_hash'] = $this->stableHash($c37);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_UNEXPECTED_C37_STATUS', $result['status']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C37_is_not_failed_anti_overfit_input(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('conclusion');
        $c37 = $this->c37Artifact($isPath);
        $c37['diagnostic_conclusion'] = 'C37_CANDIDATE_VALIDATED_FOR_OOS_PROOF_NEXT';
        $c37['artifact_hash'] = $this->stableHash($c37);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_C37_NOT_FAILED_ANTI_OVERFIT_INPUT', $result['status']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C37_next_step_is_unexpected(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('next-step');
        $c37 = $this->c37Artifact($isPath);
        $c37['next_step_recommendation'] = 'C38_OOS_PROOF_WITH_LOCKED_C37_CANDIDATE';
        $c37['artifact_hash'] = $this->stableHash($c37);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_UNEXPECTED_C37_NEXT_STEP', $result['status']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C37_production_ready_is_true(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('prod-ready');
        $c37 = $this->c37Artifact($isPath);
        $c37['production_ready'] = true;
        $c37['artifact_hash'] = $this->stableHash($c37);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_C37_PRODUCTION_READY_NOT_FALSE', $result['status']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C37_oos_data_used_for_tuning_is_true(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('oos-tuning');
        $c37 = $this->c37Artifact($isPath);
        $c37['is_period']['oos_data_used_for_tuning'] = true;
        $c37['artifact_hash'] = $this->stableHash($c37);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_C37_OOS_TUNING_FLAG_NOT_FALSE', $result['status']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_evidence_is_missing(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('missing-is');
        $c37 = $this->c37Artifact($isPath);
        $this->writeJson($c37Path, $c37);

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C38_BLOCKED_MISSING_IS_EVIDENCE', $result['status']);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    public function test_it_completes_C38_diagnostic_when_C37_valid_and_IS_evidence_available(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('completed');
        $c37 = $this->c37Artifact($isPath);
        $this->writeJson($c37Path, $c37);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService())->execute(
            $c37Path,
            $c37['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c37_hash_match']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertSame('FAIL', $artifact['source_c37_summary']['overall_anti_overfit_result']);
        $this->assertSame('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', $artifact['validation_target']['target_candidate_code']);
        $this->assertTrue($artifact['month_coverage_failure_diagnostic']['coverage_failure_confirmed']);
        $this->assertSame(['2024-05'], $artifact['month_coverage_failure_diagnostic']['zero_pick_months']);
        $this->assertTrue($artifact['branch_concentration_diagnostic']['branch_concentration_confirmed']);
        $this->assertTrue($artifact['rolling_warning_diagnostic']['rolling_warning_confirmed']);
        $this->assertGreaterThanOrEqual(3, count($artifact['evidence_expansion_requirements']));
        $this->assertNotEmpty($artifact['redesign_hypotheses']);
        $this->assertSame('C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_NEW_CANDIDATE', $artifact['c38_decision_summary']['candidate_c38_decision']);
        $this->assertFalse($artifact['c38_decision_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c38_decision_summary']['new_candidate_selected']);
        $this->assertSame('C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS', $artifact['diagnostic_conclusion']);
        $this->assertSame('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS', $artifact['next_step_recommendation']);

        foreach ($artifact['candidate_safety_audit'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
            $this->assertTrue($row['no_new_candidate_selected']);
            $this->assertTrue($row['no_oos_proof']);
            $this->assertTrue($row['no_best_of_oos']);
            $this->assertTrue($row['no_production_catalog']);
        }

        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);
        $this->cleanup($c37Path, $isPath, $outputPath);
    }

    private function c37Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK',
            'status' => 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED',
            'artifact_type' => 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c37_summary' => [
                'overall_anti_overfit_result' => 'FAIL',
                'candidate_c37_decision' => 'C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION',
                'source_evidence' => $isPath,
                'g21_rows' => 3,
                'g16_rows' => 2,
            ],
            'validation_target' => [
                'baseline_candidate_code' => 'C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR',
                'target_candidate_code' => 'C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR',
                'target_candidate_is_not_production' => true,
            ],
            'anti_overfit_summary' => [
                'overall_anti_overfit_result' => 'FAIL',
                'candidate_c37_decision' => 'C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION',
                'month_coverage_result' => 'FAIL',
                'branch_concentration_result' => 'WARNING',
                'rolling_validation_result' => 'WARNING',
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'candidate_is_not_production' => true,
            ],
            'month_coverage_validation' => [
                'result' => 'FAIL',
                'reason_code' => 'C37_MONTH_COVERAGE_FAIL',
            ],
            'branch_concentration_validation' => [
                'result' => 'WARNING',
                'reason_code' => 'C37_BRANCH_CONCENTRATION_WARNING',
            ],
            'rolling_window_validation' => [
                [
                    'validation_slice' => '2024-04_to_2024-06',
                    'window_code' => '3_month_window',
                    'result' => 'WARNING',
                    'reason_code' => 'C37_VALIDATION_SLICE_WARNING',
                    'target_candidate' => ['selected_rows' => 2],
                    'comparison_vs_baseline' => [
                        'delta_avg_ret_net_vs_baseline' => 0.004,
                        'delta_month_win_rate_min_vs_baseline' => -0.15,
                    ],
                ],
            ],
            'not_evaluable_reasons' => [
                [
                    'validation_layer' => 'C36_CANDIDATE_FORMATION',
                    'validation_slice' => 'C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD',
                    'reason_code' => 'C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE',
                    'message' => 'Candidate is not evaluable from available fields.',
                ],
            ],
            'diagnostic_conclusion' => 'C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK',
            'next_step_recommendation' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function isArtifact(): array
    {
        return [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_COMPLETED',
            'pick_diagnostic_rows' => [
                $this->row('2024-04-15', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', 'AAG21', -0.0200),
                $this->row('2024-04-20', '2024-04', 'G16', 'next_open_delay_after_close_signal', 'AAG16', 0.0200),
                $this->row('2024-05-15', '2024-05', 'G21', 'no_rule_profit_signal_before_fallback', 'BBG21', -0.0100),
                $this->row('2024-06-15', '2024-06', 'G21', 'no_rule_profit_signal_before_fallback', 'CCG21', -0.0180),
                $this->row('2024-06-20', '2024-06', 'G16', 'next_open_delay_after_close_signal', 'CCG16', 0.0190),
            ],
        ];
    }

    private function row(string $date, string $month, string $source, string $bucket, string $ticker, float $ret): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $ticker,
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'profile_ret_net' => $ret,
            'oos_executed' => false,
            'production_ready' => 0,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c38-'.$suffix.'-'.uniqid();
        return [$base.'-c37.json', $base.'-is.json', $base.'-out.json'];
    }

    private function writeJson(string $path, array $payload): void
    {
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }
}
