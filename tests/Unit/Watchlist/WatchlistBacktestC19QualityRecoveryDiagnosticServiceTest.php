<?php

require_once __DIR__.'/WatchlistBacktestC19ProposedSelectionPriceDiagnosticServiceTest.php';

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC19ProposedSelectionPriceDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestC19QualityRecoveryDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestC19SelectionModelRedesignAnalysisService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistBacktestRuntimeArtifactService;

class WatchlistBacktestC19QualityRecoveryDiagnosticServiceTest extends TestCase
{
    public function test_it_runs_quality_recovery_profiles_without_catalog_or_oos(): void
    {
        $outputPath = sys_get_temp_dir().'/c19-quality-recovery-diagnostic-test.json';
        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }

        $calendar = new WatchlistBacktestC19Phase4FakeCalendar();
        $paramGrid = new WatchlistBacktestC19Phase4FakeParamGridRepository();
        $paramsetFactory = new WatchlistBacktestParamGridParamsetFactory();
        $plan = new WatchlistBacktestC19Phase4FakePlanGroupingService();
        $selection = new WatchlistBacktestC19SelectionModelRedesignAnalysisService(
            $calendar,
            $paramGrid,
            $paramsetFactory,
            new WatchlistBacktestC19Phase4FakeCandidateUniverseService(),
            new WatchlistBacktestC19Phase4FakeScoringService(),
            $plan,
            new WatchlistBacktestC19Phase4FakeRecommendationService($plan)
        );
        $priceDiagnostic = new WatchlistBacktestC19ProposedSelectionPriceDiagnosticService(
            $calendar,
            $paramGrid,
            $paramsetFactory,
            $selection,
            new WatchlistBacktestC19Phase4FakePriceSeriesReadService(),
            new WatchlistBacktestRuntimeArtifactService()
        );
        $service = new WatchlistBacktestC19QualityRecoveryDiagnosticService($priceDiagnostic);

        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            [
                'overwrite' => true,
                'profiles' => 'Q00_TAHAP_4_BASELINE,Q05_DOWNSIDE_AWARE_SCORE_120,Q06_MONTHLY_QUALITY_CAP_120',
                'executed_at' => '2025-05-21T23:59:59+07:00',
            ]
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C19_QUALITY_RECOVERY_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC', $result['scope']);
        $this->assertSame(3, $result['profile_count']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($outputPath);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C19_QUALITY_RECOVERY_TUNING_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC', $artifact['scope']);
        $this->assertSame('Q00_TAHAP_4_BASELINE', $artifact['baseline_profile_code']);
        $this->assertFalse($artifact['safety_boundaries']['quality_profiles_use_price_outcome_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['catalog_allowed']);
        $this->assertTrue($artifact['safety_boundaries']['C19_CATALOG_IMPLEMENTATION_DEFERRED']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C19_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['OOS_NOT_RUN']);
        $this->assertSame(0, $artifact['safety_boundaries']['production_ready']);
        $this->assertNotEmpty($artifact['profile_summaries']);
        $this->assertArrayHasKey('recommended_next_step', $artifact);
        $this->assertContains($artifact['recommended_next_step']['decision'], [
            'CONTINUE_TO_REPEAT_IS_PROOF_WITH_BEST_PROFILE',
            'DO_NOT_CREATE_CATALOG_CONTINUE_QUALITY_REDESIGN',
        ]);

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_it_separates_small_sample_best_from_sample_qualified_decision_evidence(): void
    {
        $outputPath = sys_get_temp_dir().'/c19-quality-recovery-ranking-test.json';
        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }

        $service = new WatchlistBacktestC19QualityRecoveryDiagnosticService(new WatchlistBacktestC19Tahap5BFakePriceDiagnosticService());
        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            [
                'overwrite' => true,
                'profiles' => 'Q00_TAHAP_4_BASELINE,Q02_NO_SCORE_OVEREXTENSION_RECOVERY',
                'executed_at' => '2025-05-21T23:59:59+07:00',
            ]
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('Q00_TAHAP_4_BASELINE', $result['best_profile_code']);
        $this->assertSame('Q00_TAHAP_4_BASELINE', $result['best_sample_qualified_profile_code']);
        $this->assertSame('Q02_NO_SCORE_OVEREXTENSION_RECOVERY', $result['best_any_sample_profile_code']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('Q02_NO_SCORE_OVEREXTENSION_RECOVERY', $artifact['best_any_sample_profile_summary']['profile_code']);
        $this->assertSame(1, $artifact['best_any_sample_profile_summary']['best_any_sample_param']['evaluated_picks_count']);
        $this->assertSame('Q00_TAHAP_4_BASELINE', $artifact['best_sample_qualified_profile_summary']['profile_code']);
        $this->assertSame(124, $artifact['best_sample_qualified_profile_summary']['best_sample_qualified_param']['evaluated_picks_count']);
        $this->assertSame('DO_NOT_CREATE_CATALOG_CONTINUE_QUALITY_REDESIGN', $artifact['recommended_next_step']['decision']);

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_it_builds_sample_quality_frontier_table_without_promoting_catalog(): void
    {
        $outputPath = sys_get_temp_dir().'/c19-quality-frontier-test.json';
        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }

        $service = new WatchlistBacktestC19QualityRecoveryDiagnosticService(new WatchlistBacktestC19Tahap5BFakePriceDiagnosticService());
        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            $outputPath,
            [
                'overwrite' => true,
                'profiles' => 'Q11_FRONTIER_L0_STRICT_NO_OVEREXTENSION_CORE,Q12_FRONTIER_L1_LOW_ATR_NO_OVEREXTENSION_90,Q13_FRONTIER_L2_DOWNSIDE_BACKFILL_110,Q14_FRONTIER_L3_CONTROLLED_OVEREXTENSION_125,Q15_FRONTIER_L4_BASELINE_BOUNDARY_135',
                'executed_at' => '2025-05-21T23:59:59+07:00',
            ]
        );

        $this->assertSame('PASS', $result['status']);
        $this->assertSame(5, $result['profile_count']);
        $this->assertSame(5, $result['frontier_level_count']);
        $this->assertNotSame('', $result['best_frontier_profile_code']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC', $artifact['scope']);
        $this->assertCount(5, $artifact['sample_quality_frontier_table']);
        $this->assertSame(0, $artifact['sample_quality_frontier_table'][0]['frontier_level']);
        $this->assertSame(4, $artifact['sample_quality_frontier_table'][4]['frontier_level']);
        $this->assertSame('FRONTIER_HAS_NO_CATALOG_CANDIDATE_KEEP_REDESIGN_OR_STOP_C19', $artifact['sample_quality_frontier_interpretation']['decision']);
        $this->assertFalse($artifact['safety_boundaries']['catalog_allowed']);
        $this->assertTrue($artifact['safety_boundaries']['sample_quality_frontier_diagnostic_supported']);

        foreach (glob($outputPath.'*') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function test_it_blocks_unknown_quality_profile(): void
    {
        $service = new WatchlistBacktestC19QualityRecoveryDiagnosticService();
        $result = $service->execute(
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE,
            WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE,
            sys_get_temp_dir().'/c19-quality-profile-invalid-test.json',
            ['profiles' => 'NOT_A_PROFILE', 'overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C19_QUALITY_PROFILE_INVALID', $result['reason_code']);
        $this->assertSame(1, $result['c19_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }
}

class WatchlistBacktestC19Tahap5BFakePriceDiagnosticService extends WatchlistBacktestC19ProposedSelectionPriceDiagnosticService
{
    public function __construct()
    {
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $profile = strtoupper(trim((string) ($options['quality_profile'] ?? self::DEFAULT_QUALITY_PROFILE)));
        $diagnostics = [];
        if (strpos($profile, 'Q11_FRONTIER_') === 0) {
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q11_FRONTIER_L0', 52, 0.0010, 0.0040, -0.0180, 0.52, 8, 52, 0, 0);
        } elseif (strpos($profile, 'Q12_FRONTIER_') === 0) {
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q12_FRONTIER_L1', 82, -0.0008, 0.0000, -0.0185, 0.48, 10, 52, 30, 1);
        } elseif (strpos($profile, 'Q13_FRONTIER_') === 0) {
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q13_FRONTIER_L2', 108, -0.0012, -0.0005, -0.0190, 0.46, 12, 52, 56, 2);
        } elseif (strpos($profile, 'Q14_FRONTIER_') === 0) {
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q14_FRONTIER_L3', 118, -0.0016, -0.0005, -0.0200, 0.44, 13, 52, 66, 3);
        } elseif (strpos($profile, 'Q15_FRONTIER_') === 0) {
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q15_FRONTIER_L4', 124, -0.0018, -0.0005, -0.0182, 0.4355, 13, 52, 72, 4);
        } elseif ($profile === 'Q02_NO_SCORE_OVEREXTENSION_RECOVERY') {
            $diagnostics[] = $this->fakeDiagnostic(149, 'Q02_TINY_SAMPLE', 1, 0.0157, 0.0157, 0.0157, 1.0, 0, 1, 0);
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q02_USEFUL_DIAGNOSTIC', 53, 0.0, 0.0055, -0.0192, 0.5283, 8, 40, 13);
        } else {
            $diagnostics[] = $this->fakeDiagnostic(148, 'Q00_SAMPLE_QUALIFIED', 124, -0.0018, -0.0005, -0.0182, 0.4355, 13, 0, 0);
        }

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_PRICE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_PRICE_DIAGNOSTIC',
            'diagnostics' => $diagnostics,
            'artifact_hash' => sha1($profile),
        ];
        $dir = dirname($outputPath);
        if ($dir !== '' && ! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($outputPath, json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_PRICE_DIAGNOSTIC_READY',
            'scope' => 'IS_ONLY_PRICE_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'max_proposed_recommended_count' => 135,
            'max_evaluated_picks_count' => max(array_map(function (array $diag): int {
                return (int) $diag['price_evaluation_counts']['evaluated_picks_count'];
            }, $diagnostics)),
            'max_price_missing_count' => 0,
            'params_with_evaluated_sample_target_reached' => $profile === 'Q02_NO_SCORE_OVEREXTENSION_RECOVERY' ? 0 : 1,
        ];
    }

    private function fakeDiagnostic(int $paramId, string $rowCode, int $evaluated, float $avg, float $median, float $p25, float $win, int $periodFail, int $core, int $backfill, int $frontierLevel = -1): array
    {
        return [
            'param_id' => $paramId,
            'row_code' => $rowCode,
            'selection_counts' => [
                'baseline_proposed_recommended_count' => 135,
                'proposed_recommended_count' => $evaluated,
                'quality_profile_removed_count' => max(0, 135 - $evaluated),
                'quality_profile_core_selected_count' => $core,
                'quality_profile_backfill_selected_count' => $backfill,
            ],
            'price_evaluation_counts' => [
                'requested_pairs_count' => $evaluated * 5,
                'evaluated_picks_count' => $evaluated,
                'price_missing_count' => 0,
            ],
            'return_metrics' => [
                'avg_ret_net_top' => $avg,
                'median_ret_net_top' => $median,
                'p25_ret_net_top' => $p25,
                'win_rate_top' => $win,
                'month_win_rate_min' => 0.0,
                'month_avg_ret_net_min' => $p25,
                'period_fail_count' => $periodFail,
            ],
            'reason_code_distribution' => [
                'WATCHLIST_BACKTEST_EXIT_STOP' => 10,
                'WATCHLIST_BACKTEST_EXIT_TARGET' => 8,
            ],
            'quality_profile_diagnostic' => [
                'stage_counts' => [
                    'core_selected_count' => $core,
                    'backfill_selected_count' => $backfill,
                    'frontier_level' => $frontierLevel,
                    'frontier_target_selected_count' => $evaluated,
                    'frontier_l0_count' => $frontierLevel >= 0 ? $core : 0,
                    'frontier_l1_count' => $frontierLevel >= 1 ? max(0, (int) floor($backfill / 4)) : 0,
                    'frontier_l2_count' => $frontierLevel >= 2 ? max(0, (int) floor($backfill / 4)) : 0,
                    'frontier_l3_count' => $frontierLevel >= 3 ? max(0, (int) floor($backfill / 4)) : 0,
                    'frontier_l4_count' => $frontierLevel >= 4 ? max(0, (int) floor($backfill / 4)) : 0,
                    'frontier_rejected_count' => 0,
                ],
            ],
        ];
    }
}
