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
