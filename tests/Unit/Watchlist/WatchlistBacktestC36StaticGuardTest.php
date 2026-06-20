<?php

use App\Application\Watchlist\Services\WatchlistBacktestC36IsControlledRedesignCandidateFormationService;

class WatchlistBacktestC36StaticGuardTest extends TestCase
{
    public function test_C36_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC36IsControlledRedesignCandidateFormationCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC36IsControlledRedesignCandidateFormationService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c36-is-controlled-redesign-candidate-formation', $command);
        $this->assertStringContainsString('RunBacktestC36IsControlledRedesignCandidateFormationCommand::class', $kernel);
        $this->assertStringContainsString('C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION', $service);
        $this->assertStringContainsString('c35-artifact', $command);
        $this->assertStringContainsString('expected-c35-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c36-is-controlled-redesign-candidate-formation", $kernel);
    }

    public function test_C36_does_not_mutate_C01_to_C35_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC36IsControlledRedesignCandidateFormationService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC36IsControlledRedesignCandidateFormationCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C35_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c35-is-robustness-redesign-diagnostic', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC36ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC36ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC36ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC36ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC36ParamGridSeeder.php'));
    }

    public function test_C36_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC36IsControlledRedesignCandidateFormationService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC36IsControlledRedesignCandidateFormationCommand.php'));

        $this->assertStringContainsString('NO_OOS_TUNING', $service);
        $this->assertStringContainsString('NO_OOS_PROOF', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('NO_PROFILE_RESELECTION_FROM_OOS', $service);
        $this->assertStringContainsString('CANDIDATE_IS_NOT_PRODUCTION', $service);
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $service);
        $this->assertStringContainsString("'return_used_for_selection' => false", $service);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $command);
        $this->assertStringNotContainsString('watchlist:backtest-c34-bad-month-robustness-diagnostic', $command);
    }

    public function test_C36_expected_C35_hash_and_candidate_hypotheses_are_explicit(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC36IsControlledRedesignCandidateFormationService.php'));

        $this->assertStringContainsString('1ab43b0dcee6d41d11b2ab0ed904721836dee3b1', $service);
        $this->assertStringContainsString('733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B', $service);
        $this->assertStringContainsString('C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED', $service);
        $this->assertStringContainsString('C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED', $service);
        $this->assertStringContainsString('2023-01-02', $service);
        $this->assertStringContainsString('2025-05-21', $service);
        $this->assertStringContainsString('2025-05-22', $service);
        $this->assertStringContainsString('C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK', $service);
        $this->assertStringContainsString('C35_HYP_G21_FALLBACK_EXIT_TOO_LATE', $service);
        $this->assertStringContainsString('C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE', $service);
        $this->assertStringContainsString('C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER', $service);
    }

    public function test_C36_preserves_execution_model_and_required_candidate_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC36IsControlledRedesignCandidateFormationService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('baseline_summary', $service);
        $this->assertStringContainsString('candidate_results', $service);
        $this->assertStringContainsString('candidate_comparison_table', $service);
        $this->assertStringContainsString('candidate_safety_audit', $service);
        $this->assertStringContainsString('not_evaluable_reasons', $service);
        $this->assertStringContainsString('C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE', $service);
        $this->assertStringContainsString('C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE', $service);
    }

    public function test_C36_artifact_safety_boundaries_use_structure_not_forbidden_keys(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('static-artifact');
        $c35 = $this->c35Artifact($isPath);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED', $result['status']);
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
        $this->assertTrue($safetyBoundaries['no_production_catalog']);
        $this->assertTrue($safetyBoundaries['no_promotion']);
        $this->assertTrue($safetyBoundaries['no_plan_confirm_mutation']);
        $this->assertTrue($safetyBoundaries['no_c01_to_c35_mutation']);
        $this->assertTrue($safetyBoundaries['c36_candidate_from_c35_hypotheses']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_price_used_for_selection']);
        $this->assertFalse($safetyBoundaries['profile_ret_net_used_for_selection']);
        $this->assertFalse($safetyBoundaries['derived_mfe_mae_used_for_execution']);

        foreach ($artifact['candidate_results'] as $candidate) {
            $this->assertFalse($candidate['return_used_for_selection']);
            $this->assertFalse($candidate['future_path_used_for_selection']);
            $this->assertFalse($candidate['oos_data_used_for_tuning']);
            $this->assertFalse($candidate['production_ready']);
            $this->assertTrue($candidate['candidate_is_not_production']);
        }
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    private function c35Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC',
            'status' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'is_evidence_summary' => [
                'source' => $isPath,
                'total_rows' => 6,
                'g21_rows' => 3,
                'g16_rows' => 3,
                'months_covered' => 3,
                'evidence_available' => true,
            ],
            'g21_is_summary' => ['is_weakness_confirmed' => true],
            'g16_is_summary' => ['is_weakness_confirmed' => true],
            'diagnostic_conclusion' => 'C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED',
            'next_step_recommendation' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION',
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
                $this->row('2023-03-15', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAA', -0.0200),
                $this->row('2023-03-16', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAA', -0.0100),
                $this->row('2023-04-10', '2023-04', 'G21', 'no_rule_profit_signal_before_fallback', 'BBB', -0.0040),
                $this->row('2023-03-20', '2023-03', 'G16', 'next_open_delay_after_close_signal', 'CCC', 0.0160),
                $this->row('2023-04-11', '2023-04', 'G16', 'next_open_delay_after_close_signal', 'DDD', 0.0150),
                $this->row('2023-05-15', '2023-05', 'G16', 'next_open_delay_after_close_signal', 'EEE', 0.0140),
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
            'delta_vs_raw_r09' => -0.0010,
            'oos_executed' => false,
            'production_ready' => 0,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c36-guard-'.$suffix.'-'.uniqid();
        return [$base.'-c35.json', $base.'-is.json', $base.'-out.json'];
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
