<?php

use App\Application\Watchlist\Services\WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService;

class WatchlistBacktestC38StaticGuardTest extends TestCase
{
    public function test_C38_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c38-is-redesign-evidence-expansion-diagnostic', $command);
        $this->assertStringContainsString('RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC', $service);
        $this->assertStringContainsString('c37-artifact', $command);
        $this->assertStringContainsString('expected-c37-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c38-is-redesign-evidence-expansion-diagnostic", $kernel);
    }

    public function test_C38_does_not_mutate_C01_to_C37_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C37_ARTIFACT_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('NO_NEW_CANDIDATE_SELECTED', $service);
        $this->assertStringContainsString('C38_FROM_C37_FAILED_ANTI_OVERFIT', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c37-is-validation-anti-overfit-check', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC38ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC38ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC38ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC38ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC38ParamGridSeeder.php'));
    }

    public function test_C38_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC38IsRedesignEvidenceExpansionDiagnosticCommand.php'));

        $this->assertStringContainsString('NO_OOS_TUNING', $service);
        $this->assertStringContainsString('NO_OOS_PROOF', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('NO_OOS_WINNER', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION_FROM_OOS', $service);
        $this->assertStringContainsString('NO_NEW_CANDIDATE_SELECTED', $service);
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $service);
        $this->assertStringContainsString("'return_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringNotContainsString('watchlist:backtest-c38-oos-proof', $command);
    }

    public function test_C38_expected_C37_hash_and_diagnostic_input_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService.php'));

        $this->assertStringContainsString('5938e353296cb2188b6668093522d0b40d6cb9d2', $service);
        $this->assertStringContainsString('C17254C01D2405DE8F77999DD7131AEE0663A287', $service);
        $this->assertStringContainsString('C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED', $service);
        $this->assertStringContainsString('C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK', $service);
        $this->assertStringContainsString('C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC', $service);
        $this->assertStringContainsString('2023-01-02', $service);
        $this->assertStringContainsString('2025-05-21', $service);
        $this->assertStringContainsString('2025-05-22', $service);
    }

    public function test_C38_preserves_execution_model_and_required_diagnostic_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('month_coverage_failure_diagnostic', $service);
        $this->assertStringContainsString('branch_concentration_diagnostic', $service);
        $this->assertStringContainsString('rolling_warning_diagnostic', $service);
        $this->assertStringContainsString('not_evaluable_candidate_diagnostic', $service);
        $this->assertStringContainsString('evidence_expansion_requirements', $service);
        $this->assertStringContainsString('redesign_hypotheses', $service);
        $this->assertStringContainsString('c38_decision_summary', $service);
        $this->assertStringContainsString('candidate_safety_audit', $service);
    }

    public function test_C38_artifact_safety_boundaries_use_structure_not_forbidden_keys(): void
    {
        [$c37Path, $isPath, $outputPath] = $this->tempPaths('static-artifact');
        $c37 = $this->c37Artifact($isPath);
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

        $this->assertSame('C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);

        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);
        $this->assertArrayNotHasKey('production_catalog', $artifact);

        $safetyBoundaries = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        $this->assertTrue($safetyBoundaries['no_best_of_oos']);
        $this->assertTrue($safetyBoundaries['no_oos_proof']);
        $this->assertTrue($safetyBoundaries['no_oos_winner']);
        $this->assertTrue($safetyBoundaries['no_production_catalog']);
        $this->assertTrue($safetyBoundaries['no_promotion']);
        $this->assertTrue($safetyBoundaries['no_plan_confirm_mutation']);
        $this->assertTrue($safetyBoundaries['no_c01_to_c37_artifact_mutation']);
        $this->assertTrue($safetyBoundaries['no_new_candidate_selected']);
        $this->assertTrue($safetyBoundaries['c37_artifact_hash_lock']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_price_used_for_selection']);
        $this->assertFalse($safetyBoundaries['profile_ret_net_used_for_selection']);
        $this->assertFalse($safetyBoundaries['derived_mfe_mae_used_for_execution']);

        foreach ($artifact['candidate_safety_audit'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
            $this->assertTrue($row['no_new_candidate_selected']);
            $this->assertTrue($row['no_oos_proof']);
            $this->assertTrue($row['no_production_catalog']);
        }
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
            'month_coverage_validation' => ['result' => 'FAIL', 'reason_code' => 'C37_MONTH_COVERAGE_FAIL'],
            'branch_concentration_validation' => ['result' => 'WARNING', 'reason_code' => 'C37_BRANCH_CONCENTRATION_WARNING'],
            'rolling_window_validation' => [
                [
                    'validation_slice' => '2024-04_to_2024-06',
                    'window_code' => '3_month_window',
                    'result' => 'WARNING',
                    'reason_code' => 'C37_VALIDATION_SLICE_WARNING',
                    'target_candidate' => ['selected_rows' => 2],
                    'comparison_vs_baseline' => ['delta_avg_ret_net_vs_baseline' => 0.004, 'delta_month_win_rate_min_vs_baseline' => -0.15],
                ],
            ],
            'not_evaluable_reasons' => [],
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
                $this->row('2024-04-15', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', -0.0200),
                $this->row('2024-04-20', '2024-04', 'G16', 'next_open_delay_after_close_signal', 0.0200),
                $this->row('2024-05-15', '2024-05', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100),
                $this->row('2024-06-15', '2024-06', 'G21', 'no_rule_profit_signal_before_fallback', -0.0180),
                $this->row('2024-06-20', '2024-06', 'G16', 'next_open_delay_after_close_signal', 0.0190),
            ],
        ];
    }

    private function row(string $date, string $month, string $source, string $bucket, float $ret): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $source.$month,
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
        $base = sys_get_temp_dir().'/c38-guard-'.$suffix.'-'.uniqid();
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
