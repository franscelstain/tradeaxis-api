<?php

use App\Application\Watchlist\Services\WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService;

class WatchlistBacktestC40StaticGuardTest extends TestCase
{
    public function test_C40_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate', $command);
        $this->assertStringContainsString('RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand::class', $kernel);
        $this->assertStringContainsString('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE', $service);
        $this->assertStringContainsString('c39-artifact', $command);
        $this->assertStringContainsString('expected-c39-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate", $kernel);
    }

    public function test_C40_does_not_mutate_C01_to_C39_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C39_ARTIFACT_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('C40_CANDIDATE_FROM_C39_GUARDED_CANDIDATE', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC40ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC40ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC40ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC40ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC40ParamGridSeeder.php'));
    }

    public function test_C40_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateCommand.php'));

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
        $this->assertStringNotContainsString('watchlist:backtest-c40-oos-proof', $command);
    }

    public function test_C40_expected_C39_hash_and_guarded_input_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService.php'));

        $this->assertStringContainsString('504aaa061054ed2771ed08294d8a0570f08e18db', $service);
        $this->assertStringContainsString('B08233211E335C982E327D6A0C638428B906BFC9', $service);
        $this->assertStringContainsString('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED', $service);
        $this->assertStringContainsString('C39_GUARDED_IS_CANDIDATE_FORMED', $service);
        $this->assertStringContainsString('C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA', $service);
        $this->assertStringContainsString('best_candidate_requires_C40_validation', $service);
        $this->assertStringContainsString('all_required_guards_passed', $service);
        $this->assertStringContainsString('2023-01-02', $service);
        $this->assertStringContainsString('2025-05-21', $service);
        $this->assertStringContainsString('2025-05-22', $service);
    }

    public function test_C40_preserves_execution_model_and_required_validation_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('full_is_validation', $service);
        $this->assertStringContainsString('yearly_validation', $service);
        $this->assertStringContainsString('rolling_window_validation', $service);
        $this->assertStringContainsString('bad_month_like_stress_validation', $service);
        $this->assertStringContainsString('non_bad_month_validation', $service);
        $this->assertStringContainsString('ticker_concentration_validation', $service);
        $this->assertStringContainsString('branch_concentration_validation', $service);
        $this->assertStringContainsString('month_coverage_validation', $service);
        $this->assertStringContainsString('downside_stability_validation', $service);
    }

    public function test_C40_artifact_safety_boundaries_use_structure_not_forbidden_keys(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('static-artifact');
        $c39 = $this->c39Artifact($isPath);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED', $result['status']);
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
        $this->assertTrue($safetyBoundaries['no_c01_to_c39_artifact_mutation']);
        $this->assertTrue($safetyBoundaries['c39_artifact_hash_lock']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_price_used_for_selection']);
        $this->assertFalse($safetyBoundaries['profile_ret_net_used_for_selection']);
        $this->assertFalse($safetyBoundaries['derived_mfe_mae_used_for_execution']);

        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    private function c39Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
            'status' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED',
            'artifact_type' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
            'production_ready' => false,
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_c38_summary' => ['source_evidence' => $isPath, 'g21_rows' => 5, 'g16_rows' => 8],
            'candidate_summary' => [
                'candidate_formed' => true,
                'best_is_candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                'best_is_candidate_is_not_production' => true,
                'best_candidate_requires_C40_validation' => true,
            ],
            'candidate_results' => [[
                'candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                'candidate_status' => 'EVALUATED',
                'source_branch' => 'G21_G16',
                'selected_rows' => 13,
                'selection_rule' => 'keep_G16_and_reintroduce_metadata_sorted_G21_monthly_quota_until_top_branch_share_limit_is_met',
                'all_required_guards_passed' => true,
                'candidate_is_not_production' => true,
                'production_ready' => false,
            ]],
            'diagnostic_conclusion' => 'C39_GUARDED_IS_CANDIDATE_FORMED',
            'next_step_recommendation' => 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE',
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
        $base = sys_get_temp_dir().'/c40-static-'.$suffix.'-'.uniqid();
        return [$base.'-c39.json', $base.'-is.json', $base.'-out.json'];
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
