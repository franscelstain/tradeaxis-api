<?php

use App\Application\Watchlist\Services\WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService;

class WatchlistBacktestC39StaticGuardTest extends TestCase
{
    public function test_C39_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards', $command);
        $this->assertStringContainsString('RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand::class', $kernel);
        $this->assertStringContainsString('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS', $service);
        $this->assertStringContainsString('c38-artifact', $command);
        $this->assertStringContainsString('expected-c38-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards", $kernel);
    }

    public function test_C39_does_not_mutate_C01_to_C38_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C38_ARTIFACT_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('CANDIDATE_REQUIRES_C40_VALIDATION', $service);
        $this->assertStringContainsString('C39_FROM_C38_EVIDENCE_EXPANSION_REQUIRED', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c38-is-redesign-evidence-expansion-diagnostic', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC39ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC39ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC39ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC39ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC39ParamGridSeeder.php'));
    }

    public function test_C39_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC39IsControlledRedesignWithCoverageBranchGuardsCommand.php'));

        $this->assertStringContainsString('NO_OOS_TUNING', $service);
        $this->assertStringContainsString('NO_OOS_PROOF', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('NO_OOS_WINNER', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION_FROM_OOS', $service);
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $service);
        $this->assertStringContainsString("'return_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringNotContainsString('watchlist:backtest-c39-oos-proof', $command);
    }

    public function test_C39_expected_C38_hash_and_required_input_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService.php'));

        $this->assertStringContainsString('7fe69c9ee9797615df676b0fe0c7378b452da429', $service);
        $this->assertStringContainsString('74AF66E0170D4C6FF8AE3B7E45F8EC72D9774A7B', $service);
        $this->assertStringContainsString('C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED', $service);
        $this->assertStringContainsString('C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS', $service);
        $this->assertStringContainsString('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS', $service);
        $this->assertStringContainsString('C38_REQ_MONTH_COVERAGE_GUARD', $service);
        $this->assertStringContainsString('C38_REQ_BRANCH_DIVERSIFICATION_GUARD', $service);
        $this->assertStringContainsString('2023-01-02', $service);
        $this->assertStringContainsString('2025-05-21', $service);
        $this->assertStringContainsString('2025-05-22', $service);
    }

    public function test_C39_preserves_execution_model_and_required_guard_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('guard_configuration', $service);
        $this->assertStringContainsString('month_coverage_guard', $service);
        $this->assertStringContainsString('branch_diversification_guard', $service);
        $this->assertStringContainsString('guard_validation_summary', $service);
        $this->assertStringContainsString('candidate_comparison_table', $service);
        $this->assertStringContainsString('candidate_safety_audit', $service);
        $this->assertStringContainsString('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE', $service);
    }

    public function test_C39_artifact_safety_boundaries_use_structure_not_forbidden_keys(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('static-artifact');
        $c38 = $this->c38Artifact($isPath);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED', $result['status']);
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
        $this->assertTrue($safetyBoundaries['no_c01_to_c38_artifact_mutation']);
        $this->assertTrue($safetyBoundaries['candidate_requires_c40_validation']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_price_used_for_selection']);
        $this->assertFalse($safetyBoundaries['profile_ret_net_used_for_selection']);
        $this->assertFalse($safetyBoundaries['derived_mfe_mae_used_for_execution']);

        foreach ($artifact['candidate_results'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
        }
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    private function c38Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC',
            'status' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC',
            'production_ready' => false,
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_c37_summary' => ['overall_anti_overfit_result' => 'FAIL', 'source_evidence' => $isPath],
            'month_coverage_failure_diagnostic' => ['zero_pick_months' => ['2024-05'], 'coverage_failure_confirmed' => true],
            'branch_concentration_diagnostic' => ['branch_concentration_confirmed' => true],
            'rolling_warning_diagnostic' => ['rolling_warning_confirmed' => true, 'warning_or_fail_windows' => [['validation_slice' => '2024-04_to_2024-06']]],
            'evidence_expansion_requirements' => [
                ['requirement_code' => 'C38_REQ_MONTH_COVERAGE_GUARD', 'priority' => 'HIGH'],
                ['requirement_code' => 'C38_REQ_BRANCH_DIVERSIFICATION_GUARD', 'priority' => 'HIGH'],
            ],
            'c38_decision_summary' => ['direct_oos_proof_recommended' => false, 'new_candidate_selected' => false, 'production_ready' => false],
            'diagnostic_conclusion' => 'C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS',
            'next_step_recommendation' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function isArtifact(): array
    {
        $rows = [];
        for ($i = 1; $i <= 4; $i++) {
            $rows[] = $this->row('2024-04-0'.$i, '2024-04', 'G16', 'next_open_delay_after_close_signal', 0.0200, 100 + $i);
            $rows[] = $this->row('2024-06-0'.$i, '2024-06', 'G16', 'next_open_delay_after_close_signal', 0.0200, 200 + $i);
        }
        $rows[] = $this->row('2024-04-15', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 301);
        $rows[] = $this->row('2024-04-16', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 302);
        $rows[] = $this->row('2024-05-15', '2024-05', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 401);
        $rows[] = $this->row('2024-06-15', '2024-06', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 501);
        $rows[] = $this->row('2024-06-16', '2024-06', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 502);

        return ['pick_diagnostic_rows' => $rows];
    }

    private function row(string $date, string $month, string $source, string $bucket, float $ret, int $paramId): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $source.$month.$paramId,
            'param_id' => $paramId,
            'row_code' => 'TEST_'.$paramId,
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'profile_code' => 'TEST_'.$source.'_'.$paramId,
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
        $base = sys_get_temp_dir().'/c39-static-'.$suffix.'-'.uniqid();
        return [$base.'-c38.json', $base.'-is.json', $base.'-out.json'];
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
