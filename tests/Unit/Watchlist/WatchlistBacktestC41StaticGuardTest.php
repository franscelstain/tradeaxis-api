<?php

use App\Application\Watchlist\Services\WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService;

class WatchlistBacktestC41StaticGuardTest extends TestCase
{
    public function test_C41_command_is_registered_not_scheduled_and_has_required_options(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-c41-is-review-or-evidence-expansion-before-oos', $command);
        $this->assertStringContainsString('RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand::class', $kernel);
        $this->assertStringContainsString('C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS', $service);
        $this->assertStringContainsString('c40-artifact', $command);
        $this->assertStringContainsString('expected-c40-hash', $command);
        $this->assertStringContainsString('from', $command);
        $this->assertStringContainsString('to', $command);
        $this->assertStringContainsString('output', $command);
        $this->assertStringContainsString('progress', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c41-is-review-or-evidence-expansion-before-oos", $kernel);
    }

    public function test_C41_locks_C40_warning_artifact_and_expected_warning_path(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService.php'));

        $this->assertStringContainsString('0b40ee2464ed820d47ad0b83acbacd78b440d5bd', $service);
        $this->assertStringContainsString('306E01AD1274944991F1AFE6CFEBBDB3C0E06BFC', $service);
        $this->assertStringContainsString('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED', $service);
        $this->assertStringContainsString('C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS', $service);
        $this->assertStringContainsString('C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS', $service);
        $this->assertStringContainsString('C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA', $service);
        $this->assertStringContainsString('storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json', $service);
    }

    public function test_C41_does_not_mutate_C01_to_C40_or_create_production_catalog(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('NO_C01_TO_C40_ARTIFACT_MUTATION', $service);
        $this->assertStringContainsString('NO_PRODUCTION_CATALOG', $service);
        $this->assertStringContainsString('NO_PROMOTION', $service);
        $this->assertStringContainsString('NO_PLAN_CONFIRM_MUTATION', $service);
        $this->assertStringContainsString('NO_C41_CANDIDATE_RESELECTION', $service);
        $this->assertStringNotContainsString('watchlist:backtest-c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate', $command);
        $this->assertStringNotContainsString('WatchlistBacktestC41ParamGridCatalog', $repository);
        $this->assertStringNotContainsString('WatchlistBacktestC41ParamGridCatalog', $factory);
        $this->assertFileDoesNotExist(base_path('app/Application/Watchlist/Services/WatchlistBacktestC41ParamGridCatalog.php'));
        $this->assertFileDoesNotExist(base_path('app/Console/Commands/Watchlist/SeedBacktestC41ParamGridCommand.php'));
        $this->assertFileDoesNotExist(base_path('database/seeders/Watchlist/WatchlistBacktestC41ParamGridSeeder.php'));
    }

    public function test_C41_does_not_run_OOS_proof_or_use_best_of_OOS_or_profile_reselection(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService.php'));
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC41IsReviewEvidenceExpansionBeforeOosCommand.php'));

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
        $this->assertStringNotContainsString('watchlist:backtest-c41-oos-proof', $command);
    }

    public function test_C41_preserves_execution_model_and_required_review_outputs(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService.php'));

        $this->assertStringContainsString("'entry' => 'NEXT_OPEN'", $service);
        $this->assertStringContainsString("'exit' => 'STOP_TP_OR_TIME'", $service);
        $this->assertStringContainsString("'hold' => 5", $service);
        $this->assertStringContainsString("'fee' => 'IDR_FIXED'", $service);
        $this->assertStringContainsString("'slip' => 0", $service);
        $this->assertStringContainsString("'gap' => 'OPEN'", $service);
        $this->assertStringContainsString("'px' => 'IDX_BANDS'", $service);
        $this->assertStringContainsString('warning_layer_review', $service);
        $this->assertStringContainsString('rolling_warning_review', $service);
        $this->assertStringContainsString('non_bad_month_warning_review', $service);
        $this->assertStringContainsString('guard_blocker_recheck', $service);
        $this->assertStringContainsString('not_evaluable_evidence_gap_review', $service);
        $this->assertStringContainsString('evidence_expansion_requirements', $service);
        $this->assertStringContainsString('review_decision_summary', $service);
    }

    public function test_C41_artifact_safety_boundaries_use_structure_not_forbidden_keys(): void
    {
        [$c40Path, $outputPath] = $this->tempPaths('static-artifact');
        $c40 = $this->c40Artifact();
        $this->writeJson($c40Path, $c40);

        $result = (new WatchlistBacktestC41IsReviewEvidenceExpansionBeforeOosService())->execute(
            $c40Path,
            $c40['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);

        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);
        $this->assertArrayNotHasKey('production_catalog', $artifact);

        $safetyBoundaries = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        $this->assertTrue($safetyBoundaries['c40_artifact_hash_lock']);
        $this->assertTrue($safetyBoundaries['is_only_review']);
        $this->assertTrue($safetyBoundaries['no_best_of_oos']);
        $this->assertTrue($safetyBoundaries['no_oos_proof']);
        $this->assertTrue($safetyBoundaries['no_oos_winner']);
        $this->assertTrue($safetyBoundaries['no_production_catalog']);
        $this->assertTrue($safetyBoundaries['no_promotion']);
        $this->assertTrue($safetyBoundaries['no_plan_confirm_mutation']);
        $this->assertTrue($safetyBoundaries['no_c01_to_c40_artifact_mutation']);
        $this->assertTrue($safetyBoundaries['no_c41_candidate_reselection']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_price_used_for_selection']);
        $this->assertFalse($safetyBoundaries['profile_ret_net_used_for_selection']);
        $this->assertFalse($safetyBoundaries['derived_mfe_mae_used_for_execution']);

        $this->cleanup($c40Path, $outputPath);
    }

    private function c40Artifact(): array
    {
        $artifact = [
            'status' => 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED',
            'production_ready' => false,
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_c39_summary' => [
                'best_is_candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                'source_evidence' => 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json',
            ],
            'validation_target' => ['target_candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA'],
            'validation_summary' => [
                'passed_layers' => 7,
                'warning_layers' => 2,
                'failed_layers' => 0,
                'not_evaluable_layers' => 0,
                'overall_anti_overfit_result' => 'WARNING',
                'candidate_c40_decision' => 'C40_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS',
            ],
            'rolling_window_validation' => [
                [
                    'validation_slice' => '2023-10_to_2024-03',
                    'window_code' => '6_month_window',
                    'baseline_candidate' => ['selected_rows' => 829, 'month_avg_ret_net_min' => 0.0001003666052420909],
                    'target_candidate' => ['selected_rows' => 492, 'month_avg_ret_net_min' => -0.007926413671186232],
                    'comparison_vs_baseline' => [
                        'delta_avg_ret_net_vs_baseline' => 0.004891344784638333,
                        'delta_month_avg_ret_net_min_vs_baseline' => -0.008026780276428322,
                        'delta_bad_month_like_count_vs_baseline' => 1,
                    ],
                    'result' => 'WARNING',
                    'reason_code' => 'C40_VALIDATION_SLICE_WARNING',
                ],
            ],
            'non_bad_month_validation' => [
                'baseline_candidate' => ['selected_rows' => 2001, 'month_avg_ret_net_min' => 0.0001003666052420909],
                'target_candidate' => ['selected_rows' => 1208, 'month_avg_ret_net_min' => -0.007926413671186232],
                'comparison_vs_baseline' => [
                    'delta_avg_ret_net_vs_baseline' => 0.0038820395035978495,
                    'delta_month_avg_ret_net_min_vs_baseline' => -0.008026780276428322,
                    'delta_bad_month_like_count_vs_baseline' => 1,
                ],
                'result' => 'WARNING',
                'reason_code' => 'C40_VALIDATION_SLICE_WARNING',
            ],
            'branch_concentration_validation' => [
                'candidate' => ['top_branch_share' => 0.79374624173181, 'g16_share' => 0.79374624173181, 'g21_share' => 0.20625375826819],
                'top_branch_share' => 0.79374624173181,
                'g16_share' => 0.79374624173181,
                'g21_share' => 0.20625375826819,
                'removed_or_suppressed_g21_rows' => 1427,
                'result' => 'PASS',
            ],
            'month_coverage_validation' => [
                'candidate' => ['months_covered' => 27, 'min_selected_rows_per_month' => 13, 'zero_pick_months' => 0],
                'result' => 'PASS',
            ],
            'anti_overfit_summary' => [
                'overall_anti_overfit_result' => 'WARNING',
                'no_oos_proof' => true,
                'no_oos_tuning' => true,
                'no_best_of_oos' => true,
                'no_production_catalog' => true,
                'no_candidate_promoted' => true,
            ],
            'candidate_safety_audit' => [
                [
                    'return_used_for_selection' => false,
                    'future_path_used_for_selection' => false,
                    'profile_ret_net_used_for_selection' => false,
                    'future_path_price_used_for_selection' => false,
                    'derived_mfe_mae_used_for_execution' => false,
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                    'no_oos_proof' => true,
                    'no_best_of_oos' => true,
                    'no_oos_winner' => true,
                    'no_production_catalog' => true,
                    'no_candidate_promoted' => true,
                    'no_plan_confirm_mutation' => true,
                ],
            ],
            'not_evaluable_reasons' => [
                ['validation_slice' => 'C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED', 'reason_code' => 'C39_BLOCKED_G21_PRE_TRADE_QUALITY_FIELD_UNAVAILABLE'],
            ],
            'diagnostic_conclusion' => 'C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS',
            'next_step_recommendation' => 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c41-static-'.$suffix.'-'.uniqid();
        return [$base.'-c40.json', $base.'-out.json'];
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
