<?php

use App\Application\Watchlist\Services\WatchlistBacktestC37IsValidationAntiOverfitCheckService;

class WatchlistBacktestC37StaticGuardTest extends TestCase
{
    public function test_C37_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC37IsValidationAntiOverfitCheckCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC37IsValidationAntiOverfitCheckService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c37-is-validation-anti-overfit-check', $command);
        $this->assertStringContainsString('RunBacktestC37IsValidationAntiOverfitCheckCommand::class', $kernel);
        $this->assertStringContainsString('C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK', $service);
        $this->assertStringContainsString('c36-artifact', $command);
        $this->assertStringContainsString('expected-c36-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c37-is-validation-anti-overfit-check", $kernel);
    }

    public function test_C37_does_not_mutate_C01_to_C36_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC37IsValidationAntiOverfitCheckService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC37IsValidationAntiOverfitCheckCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C36_MUTATION', $service);
        $this->assertStringContainsString('NO_C01_TO_C36_ARTIFACT_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('C37_CANDIDATE_FROM_C36_CANDIDATE', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c36-is-controlled-redesign-candidate-formation', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC37ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC37ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC37ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC37ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC37ParamGridSeeder.php'));
    }

    public function test_C37_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC37IsValidationAntiOverfitCheckService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC37IsValidationAntiOverfitCheckCommand.php'));

        $this->assertStringContainsString('NO_OOS_TUNING', $service);
        $this->assertStringContainsString('NO_OOS_PROOF', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('NO_OOS_WINNER', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION_FROM_OOS', $service);
        $this->assertStringContainsString('CANDIDATE_IS_NOT_PRODUCTION', $service);
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $service);
        $this->assertStringContainsString("'return_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_price_used_for_selection' => false", $service);
        $this->assertStringContainsString("'profile_ret_net_used_for_selection' => false", $service);
        $this->assertStringContainsString("'derived_mfe_mae_used_for_execution' => false", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringNotContainsString('watchlist:backtest-c34-bad-month-robustness-diagnostic', $command);
        $this->assertStringNotContainsString('watchlist:backtest-c36-is-controlled-redesign-candidate-formation', $command);
    }

    public function test_C37_expected_C36_hash_and_candidate_target_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC37IsValidationAntiOverfitCheckService.php'));

        $this->assertStringContainsString('8bc5198cf3b79fc9b58c39fc19f319826406b4b1', $service);
        $this->assertStringContainsString('A5D7E25594238C2743E5DB2E68657AE95BA8B927', $service);
        $this->assertStringContainsString('C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED', $service);
        $this->assertStringContainsString('C36_COMBINED_CANDIDATE_FORMED', $service);
        $this->assertStringContainsString('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', $service);
        $this->assertStringContainsString('C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR', $service);
        $this->assertStringContainsString('2023-01-02', $service);
        $this->assertStringContainsString('2025-05-21', $service);
        $this->assertStringContainsString('2025-05-22', $service);
    }

    public function test_C37_preserves_execution_model_and_required_validation_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC37IsValidationAntiOverfitCheckService.php'));

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
        $this->assertStringContainsString('anti_overfit_summary', $service);
        $this->assertStringContainsString('candidate_safety_audit', $service);
    }

    public function test_C37_artifact_safety_boundaries_use_structure_not_forbidden_keys(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('static-artifact');
        $c36 = $this->c36Artifact($isPath);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED', $result['status']);
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
        $this->assertTrue($safetyBoundaries['no_c01_to_c36_mutation']);
        $this->assertTrue($safetyBoundaries['c37_candidate_from_c36_candidate']);
        $this->assertTrue($safetyBoundaries['c36_artifact_hash_lock']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_price_used_for_selection']);
        $this->assertFalse($safetyBoundaries['profile_ret_net_used_for_selection']);
        $this->assertFalse($safetyBoundaries['derived_mfe_mae_used_for_execution']);

        foreach ($artifact['candidate_safety_audit'] as $row) {
            $this->assertArrayNotHasKey('best_of_oos', $row);
            $this->assertArrayNotHasKey('oos_winner', $row);
            $this->assertArrayNotHasKey('production_candidate', $row);
            $this->assertArrayNotHasKey('candidate_promoted', $row);
            $this->assertArrayNotHasKey('profile_reselection_from_oos', $row);
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
            $this->assertTrue($row['candidate_is_not_production']);
            $this->assertTrue($row['no_oos_proof']);
            $this->assertTrue($row['no_production_catalog']);
        }
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    private function c36Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION',
            'status' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED',
            'artifact_type' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c35_summary' => [
                'g21_rows' => 6,
                'g16_rows' => 6,
                'source_evidence' => $isPath,
            ],
            'candidate_summary' => [
                'total_candidates' => 7,
                'evaluated_candidates' => 4,
                'not_evaluable_candidates' => 3,
                'candidate_formed' => true,
                'best_is_candidate_code' => 'C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR',
                'best_is_candidate_is_not_production' => true,
            ],
            'candidate_results' => [
                $this->candidate('C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR', 'BASELINE_COMPARATOR', 'G21_G16'),
                $this->candidate('C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE', 'G21_PRIMARY_REDESIGN', 'G21'),
                $this->candidate('C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE', 'G16_SECONDARY_REDESIGN', 'G16'),
                $this->candidate('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', 'COMBINED_CONTROLLED_COMPARATOR', 'G21_G16'),
            ],
            'not_evaluable_reasons' => [
                ['candidate_code' => 'C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD', 'reason_code' => 'C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE'],
            ],
            'diagnostic_conclusion' => 'C36_COMBINED_CANDIDATE_FORMED',
            'next_step_recommendation' => 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function candidate(string $code, string $group, string $branch): array
    {
        return [
            'candidate_code' => $code,
            'candidate_group' => $group,
            'source_hypothesis' => 'C35_TEST_HYPOTHESIS',
            'source_branch' => $branch,
            'candidate_status' => 'EVALUATED',
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
            'candidate_is_not_production' => true,
        ];
    }

    private function isArtifact(): array
    {
        return [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_COMPLETED',
            'pick_diagnostic_rows' => [
                $this->row('2023-03-15', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAG21', -0.0300),
                $this->row('2023-03-20', '2023-03', 'G16', 'next_open_delay_after_close_signal', 'AAG16', 0.0200),
                $this->row('2023-07-15', '2023-07', 'G21', 'no_rule_profit_signal_before_fallback', 'BBG21', -0.0250),
                $this->row('2023-07-20', '2023-07', 'G16', 'next_open_delay_after_close_signal', 'BBG16', 0.0180),
                $this->row('2024-04-15', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', 'CCG21', -0.0280),
                $this->row('2024-04-20', '2024-04', 'G16', 'next_open_delay_after_close_signal', 'CCG16', 0.0190),
                $this->row('2024-07-15', '2024-07', 'G21', 'no_rule_profit_signal_before_fallback', 'DDG21', -0.0260),
                $this->row('2024-07-20', '2024-07', 'G16', 'next_open_delay_after_close_signal', 'DDG16', 0.0185),
                $this->row('2025-02-10', '2025-02', 'G21', 'no_rule_profit_signal_before_fallback', 'EEG21', -0.0310),
                $this->row('2025-02-12', '2025-02', 'G16', 'next_open_delay_after_close_signal', 'EEG16', 0.0210),
                $this->row('2025-04-10', '2025-04', 'G21', 'no_rule_profit_signal_before_fallback', 'FFG21', -0.0240),
                $this->row('2025-04-12', '2025-04', 'G16', 'next_open_delay_after_close_signal', 'FFG16', 0.0175),
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
            'profile_exit_reason' => $source === 'G21' ? 'raw_damage_control_no_profit_d2_exit_d3_open' : 'raw_preplanned_intraday_target_hit',
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
        $base = sys_get_temp_dir().'/c37-guard-'.$suffix.'-'.uniqid();
        return [$base.'-c36.json', $base.'-is.json', $base.'-out.json'];
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
