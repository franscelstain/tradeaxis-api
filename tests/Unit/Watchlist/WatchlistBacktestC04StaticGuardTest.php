<?php

use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;

class WatchlistBacktestC04StaticGuardTest extends TestCase
{
    public function test_c04_seed_command_is_registered_explicit_and_not_scheduled(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/SeedBacktestC04ParamGridCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $seeder = file_get_contents(base_path('database/seeders/Watchlist/WatchlistBacktestC04ParamGridSeeder.php'));

        $this->assertStringContainsString('watchlist:backtest-c04-param-grid-seed', $command);
        $this->assertStringContainsString('SeedBacktestC04ParamGridCommand::class', $kernel);
        $this->assertStringContainsString('WatchlistBacktestC04ParamGridCatalog::rows()', $command);
        $this->assertStringContainsString('WatchlistBacktestC04ParamGridCatalog::rows()', $seeder);
        $this->assertStringContainsString('r1_immutable=1', $command);
        $this->assertStringContainsString('r2_immutable=1', $command);
        $this->assertStringContainsString('c01_immutable=1', $command);
        $this->assertStringContainsString('c02_immutable=1', $command);
        $this->assertStringContainsString('c03_immutable=1', $command);
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString('latest(', strtolower($command));
        $this->assertStringNotContainsString('max(trade_date)', strtolower($command));
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c04-param-grid-seed", $kernel);
    }

    public function test_c04_calibration_path_is_explicit_is_only_and_has_no_oos_dependency(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC04ParamGridCatalog.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'),
            base_path('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06', $content);
        $this->assertStringContainsString('C04_GRID_FAILED_IS_QUALITY', $content);
        $this->assertStringContainsString('WATCHLIST_C04_IS_CALIBRATION_V1', $content);
        $this->assertStringContainsString('WEEKLY_SWING_DOWNSIDE_STABILITY_C04_IS_ONLY', $content);
        $this->assertStringContainsString('requires_c01_catalog', $content);
        $this->assertStringContainsString('requires_c02_catalog', $content);
        $this->assertStringContainsString('requires_c03_catalog', $content);
        $this->assertStringContainsString('c01_immutability_proof', $content);
        $this->assertStringContainsString('c02_immutability_proof', $content);
        $this->assertStringContainsString('c03_immutability_proof', $content);
        $this->assertStringContainsString('WS_BT_C04_NO_VALID_IS_CANDIDATE', $content);
        $this->assertStringContainsString("public const R2_MIN_IS_DATE = '2023-01-02'", $content);
        $this->assertStringContainsString("public const R2_MAX_IS_DATE = '2025-05-21'", $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $content);
        $this->assertStringNotContainsString("DB::table('eod_bars')", $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
        $this->assertStringNotContainsString('orderservice', $lower);
        $this->assertStringNotContainsString('brokerservice', $lower);
        $this->assertStringNotContainsString('portfolioservice', $lower);
    }

    public function test_c04_does_not_continue_r_series_catalog_naming_or_add_unsupported_sector_filter(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC04ParamGridCatalog.php'),
            base_path('app/Console/Commands/Watchlist/SeedBacktestC04ParamGridCommand.php'),
            base_path('database/seeders/Watchlist/WatchlistBacktestC04ParamGridSeeder.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06', $content);
        $this->assertDoesNotMatchRegularExpression('/WS_BT_GRID_[A-Z0-9_]*_R[3-9]_/', $content);
        $this->assertStringNotContainsString('WS_BT_GRID_ENTRY_QUALITY_R3', $content);
        $this->assertStringNotContainsString('WS_BT_GRID_ENTRY_QUALITY_R4', $content);
        $this->assertStringNotContainsString('WS_BT_GRID_ENTRY_QUALITY_R5', $content);
        $this->assertStringContainsString('sector_filter_used', $content);
        $this->assertStringContainsString('DIAGNOSTIC_REVIEW_ONLY_NO_SECTOR_FILTER', $content);
        $this->assertStringNotContainsString("'sector_code' =>", $content);
        $this->assertStringNotContainsString('sector_filter =>', $content);
    }

    public function test_every_c04_axis_has_an_explicit_runtime_consumer(): void
    {
        $universe = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php'));
        $scoring = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistScoringService.php'));
        $grouping = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'));

        foreach (['min_dv20_idr', 'min_atr14_pct', 'max_atr14_pct'] as $field) {
            $this->assertStringContainsString($field, $universe);
        }
        foreach ([
            'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            "'momentum'", "'breakout'", "'volume'", "'risk'",
        ] as $field) {
            $this->assertStringContainsString($field, $scoring);
        }
        foreach ([
            'top_min_score_q', 'secondary_min_score_q', 'candidate_selection_extension',
            'score_component_min', 'trend_metric_floor', 'raw_setup_guards',
            'score_components', 'factor_breakdown', 'momentum', 'breakout',
            'componentValue', 'roc_lo', 'roc_hi', 'bo_near_below_pct', 'bo_max_ext_pct',
        ] as $field) {
            $this->assertStringContainsString($field, $grouping);
        }
    }

    public function test_c04_quality_floor_excludes_weak_candidates_before_grouping(): void
    {
        $row = WatchlistBacktestC04ParamGridCatalog::rows()[1];
        $row['param_id'] = 9001;
        $paramset = (new WatchlistBacktestParamGridParamsetFactory())->make($row);
        $service = new WatchlistPlanGroupingService();

        $result = $service->groupScoredOutput($this->scoredOutput([
            $this->c04Item(1, 'WEAK', 1.00, ['score_breakout' => 0.30]),
            $this->c04Item(2, 'STRG', 0.95),
        ]), $paramset, '2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame(['STRG'], array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertSame(['WEAK'], array_column($result['excluded'], 'ticker_code'));
        $this->assertContains('WATCHLIST_C04_SCORE_COMPONENT_FLOOR_FAIL', $result['excluded'][0]['reason_codes']);
        $this->assertContains('WATCHLIST_C04_ENTRY_QUALITY_FLOOR_FAIL', $result['excluded'][0]['reason_codes']);
        $this->assertSame([], $result['groups']['AVOID']);
    }

    public function test_locked_gate_and_oos_contract_files_were_not_modified(): void
    {
        $file16 = base_path('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md');
        $file17 = base_path('docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md');

        $this->assertSame('31299d858b68ee351ae898f4c9380d8753a65d8a', sha1_file($file16));
        $this->assertSame('39519a391158a7b2dcf7b6e989079788d61669be', sha1_file($file17));
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
            'source_contract' => [
                'consumer' => 'WatchlistScoringService',
                'upstream' => 'WatchlistCandidateUniverseService',
            ],
            'score_contract' => [
                'sort_keys' => [
                    'score_total_desc',
                    'score_breakout_desc',
                    'score_momentum_desc',
                    'dv20_idr_desc',
                    'atr14_pct_asc',
                    'ticker_id_asc',
                ],
            ],
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            ],
            'items' => $items,
            'excluded' => [],
            'summary' => [
                'input_count' => count($items),
                'scored_count' => count($items),
                'excluded_count' => 0,
            ],
        ];
    }

    private function c04Item(int $tickerId, string $tickerCode, float $scoreTotal, array $overrides = []): array
    {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'score_components' => [
                'score_momentum' => $overrides['score_momentum'] ?? 0.45,
                'score_breakout' => $overrides['score_breakout'] ?? 0.85,
                'score_volume' => $overrides['score_volume'] ?? 0.60,
                'score_risk' => $overrides['score_risk'] ?? 0.80,
            ],
            'score_weights' => [
                'momentum' => 0.10,
                'breakout' => 0.30,
                'volume' => 0.20,
                'risk' => 0.40,
            ],
            'factor_breakdown' => [
                'momentum' => [
                    'ma20_slope_pct' => $overrides['ma20_slope_pct'] ?? 0.002,
                    'rs_20_vs_ihsg' => $overrides['rs_20_vs_ihsg'] ?? 0.010,
                    'close_vs_ma20_pct' => $overrides['close_vs_ma20_pct'] ?? 0.005,
                    'close_vs_ma50_pct' => $overrides['close_vs_ma50_pct'] ?? 0.010,
                    'roc20' => $overrides['roc20'] ?? 0.030,
                ],
                'breakout' => [
                    'close_to_hh20_pct' => $overrides['close_to_hh20_pct'] ?? 0.008,
                ],
            ],
            'reason_codes' => ['WS_MOM_STRONG'],
            'ranking_keys' => [
                'score_total_desc' => $scoreTotal,
                'score_breakout_desc' => $overrides['score_breakout'] ?? 0.85,
                'score_momentum_desc' => $overrides['score_momentum'] ?? 0.45,
                'dv20_idr_desc' => 7000000000.0,
                'atr14_pct_asc' => 0.0250,
                'ticker_id_asc' => $tickerId,
            ],
            'score_metrics' => [
                'dv20_idr' => 7000000000.0,
                'atr14_pct' => 0.0250,
                'vol_ratio' => 1.30,
            ],
        ];
    }
}
