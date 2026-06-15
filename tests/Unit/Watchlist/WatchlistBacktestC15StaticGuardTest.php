<?php

use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;

class WatchlistBacktestC15StaticGuardTest extends TestCase
{
    public function test_c15_seed_command_is_registered_explicit_and_not_scheduled(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/SeedBacktestC15ParamGridCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $seeder = file_get_contents(base_path('database/seeders/Watchlist/WatchlistBacktestC15ParamGridSeeder.php'));

        $this->assertStringContainsString('watchlist:backtest-c15-param-grid-seed', $command);
        $this->assertStringContainsString('SeedBacktestC15ParamGridCommand::class', $kernel);
        $this->assertStringContainsString('WatchlistBacktestC15ParamGridCatalog::rows()', $command);
        $this->assertStringContainsString('WatchlistBacktestC15ParamGridCatalog::rows()', $seeder);
        foreach (['r1_immutable=1', 'r2_immutable=1', 'c01_immutable=1', 'c02_immutable=1', 'c03_immutable=1', 'c04_immutable=1', 'c05_immutable=1', 'c06_immutable=1', 'c07_immutable=1', 'c14_immutable=1'] as $marker) {
            $this->assertStringContainsString($marker, $command);
        }
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c15-param-grid-seed", $kernel);
    }

    public function test_c15_quality_floor_rejects_overextended_score_and_accepts_controlled_pullback(): void
    {
        $row = WatchlistBacktestC15ParamGridCatalog::rows()[1];
        $row['param_id'] = 15001;
        $paramset = (new WatchlistBacktestParamGridParamsetFactory())->make($row);
        $service = new WatchlistPlanGroupingService();

        $result = $service->groupScoredOutput($this->scoredOutput([
            $this->c15Item(1, 'OVER', 0.95, ['roc5' => -0.010]),
            $this->c15Item(2, 'MOMO', 0.82, ['roc5' => 0.030]),
            $this->c15Item(3, 'LIQD', 0.82, ['dv20_idr' => 12000000000.0]),
            $this->c15Item(4, 'GOOD', 0.82, ['roc5' => -0.010]),
        ]), $paramset, '2026-05-19');

        $this->assertTrue($result['ready']);
        $active = array_merge(
            $result['groups']['TOP_PICKS'],
            $result['groups']['SECONDARY'],
            $result['groups']['WATCH_ONLY']
        );
        $this->assertSame(['GOOD'], array_column($active, 'ticker_code'));
        $this->assertSame(['OVER', 'MOMO', 'LIQD'], array_column($result['excluded'], 'ticker_code'));
        $this->assertContains('WATCHLIST_C15_SCORE_OVEREXTENSION_FAIL', $result['excluded'][0]['reason_codes']);
        $this->assertContains('WATCHLIST_C15_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL', $result['excluded'][1]['reason_codes']);
        $this->assertContains('WATCHLIST_C15_DV20_MID_LIQUIDITY_RANGE_FAIL', $result['excluded'][2]['reason_codes']);
        foreach ($result['excluded'] as $excluded) {
            $this->assertContains('WATCHLIST_C15_ENTRY_QUALITY_FLOOR_FAIL', $excluded['reason_codes']);
        }
    }


    public function test_c15_quality_floor_accepts_percent_point_momentum_payloads(): void
    {
        $row = WatchlistBacktestC15ParamGridCatalog::rows()[1];
        $row['param_id'] = 15001;
        $paramset = (new WatchlistBacktestParamGridParamsetFactory())->make($row);
        $service = new WatchlistPlanGroupingService();

        $result = $service->groupScoredOutput($this->scoredOutput([
            $this->c15Item(5, 'PCT', 0.82, ['roc5' => -1.20, 'roc20' => 1.20]),
        ]), $paramset, '2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame(['PCT'], array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertSame([], $result['excluded']);
    }

    public function test_c15_calibration_path_is_explicit_is_only_and_has_no_oos_dependency(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC15ParamGridCatalog.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'),
            base_path('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06', $content);
        $this->assertStringContainsString('C15_GRID_FAILED_IS_QUALITY', $content);
        $this->assertStringContainsString('WATCHLIST_C15_IS_CALIBRATION_V1', $content);
        $this->assertStringContainsString('WEEKLY_SWING_DOWNSIDE_STABILITY_C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_IS_ONLY', $content);
        $this->assertStringContainsString('requires_c14_catalog', $content);
        $this->assertStringContainsString('WS_BT_C15_NO_VALID_IS_CANDIDATE', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
    }


    public function test_c15_runtime_payload_enrichment_is_enabled_before_grouping_guard(): void
    {
        $candidateUniverse = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php'));
        $scoring = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistScoringService.php'));
        $grouping = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'));

        foreach ([$candidateUniverse, $scoring, $grouping] as $source) {
            $this->assertStringContainsString('C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION', $source);
        }
        $this->assertMatchesRegularExpression('/\$extendedCatalogVersions\s*=\s*\[[^\]]*\'C15\'[^\]]*\]/', $candidateUniverse);
        $this->assertMatchesRegularExpression('/\$extendedCatalogVersions\s*=\s*\[[^\]]*\'C15\'[^\]]*\]/', $scoring);
        $this->assertStringContainsString("'roc5'", $candidateUniverse);
        $this->assertStringContainsString("'roc5'", $scoring);
        $this->assertStringContainsString('$this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null)', $grouping);
    }

    private function scoredOutput(array $items): array
    {
        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_SCORING_READY',
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            'items' => $items,
            'excluded' => [],
            'summary' => ['input_count' => count($items), 'scored_count' => count($items), 'excluded_count' => 0],
        ];
    }

    private function c15Item(int $tickerId, string $tickerCode, float $scoreTotal, array $overrides = []): array
    {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'recommendation_score' => $overrides['recommendation_score'] ?? $scoreTotal,
            'score_components' => [
                'score_momentum' => $overrides['score_momentum'] ?? 0.28,
                'score_breakout' => $overrides['score_breakout'] ?? 0.60,
                'score_volume' => $overrides['score_volume'] ?? 0.30,
                'score_risk' => $overrides['score_risk'] ?? 0.70,
            ],
            'factor_breakdown' => [
                'momentum' => [
                    'ma20_slope_pct' => $overrides['ma20_slope_pct'] ?? -0.005,
                    'rs_20_vs_ihsg' => $overrides['rs_20_vs_ihsg'] ?? 0.000,
                    'close_vs_ma20_pct' => $overrides['close_vs_ma20_pct'] ?? -0.010,
                    'close_vs_ma50_pct' => $overrides['close_vs_ma50_pct'] ?? -0.020,
                    'roc5' => $overrides['roc5'] ?? -0.010,
                    'roc20' => $overrides['roc20'] ?? 0.010,
                ],
            ],
            'reason_codes' => ['WS_MOM_COOLING'],
            'score_metrics' => [
                'dv20_idr' => $overrides['dv20_idr'] ?? 3500000000.0,
                'atr14_pct' => $overrides['atr14_pct'] ?? 0.0250,
                'vol_ratio' => $overrides['vol_ratio'] ?? 1.30,
                'roc5' => $overrides['roc5'] ?? -0.010,
                'roc20' => $overrides['roc20'] ?? 0.010,
            ],
        ];
    }
}
