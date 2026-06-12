<?php

use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;

class WatchlistBacktestC07StaticGuardTest extends TestCase
{
    public function test_c07_seed_command_is_registered_explicit_and_not_scheduled(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/SeedBacktestC07ParamGridCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $seeder = file_get_contents(base_path('database/seeders/Watchlist/WatchlistBacktestC07ParamGridSeeder.php'));

        $this->assertStringContainsString('watchlist:backtest-c07-param-grid-seed', $command);
        $this->assertStringContainsString('SeedBacktestC07ParamGridCommand::class', $kernel);
        $this->assertStringContainsString('WatchlistBacktestC07ParamGridCatalog::rows()', $command);
        $this->assertStringContainsString('WatchlistBacktestC07ParamGridCatalog::rows()', $seeder);
        foreach (['r1_immutable=1', 'r2_immutable=1', 'c01_immutable=1', 'c02_immutable=1', 'c03_immutable=1', 'c04_immutable=1', 'c05_immutable=1', 'c06_immutable=1'] as $marker) {
            $this->assertStringContainsString($marker, $command);
        }
        $this->assertStringNotContainsString('watchlist:backtest-oos-proof', $command);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-c07-param-grid-seed", $kernel);
    }

    public function test_c07_calibration_path_is_explicit_is_only_and_has_no_oos_dependency(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestC07ParamGridCatalog.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'),
            base_path('app/Application/Watchlist/Services/WatchlistPlanGroupingService.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringContainsString('WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06', $content);
        $this->assertStringContainsString('C07_GRID_FAILED_IS_QUALITY', $content);
        $this->assertStringContainsString('WATCHLIST_C07_IS_CALIBRATION_V1', $content);
        $this->assertStringContainsString('WEEKLY_SWING_DOWNSIDE_STABILITY_C07_IS_ONLY', $content);
        $this->assertStringContainsString('requires_c06_catalog', $content);
        $this->assertStringContainsString('c06_immutability_proof', $content);
        $this->assertStringContainsString('WS_BT_C07_NO_VALID_IS_CANDIDATE', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
    }

    public function test_c07_quality_floor_uses_extended_runtime_metrics_without_sector_whitelist(): void
    {
        $row = WatchlistBacktestC07ParamGridCatalog::rows()[1];
        $row['param_id'] = 12001;
        $paramset = (new WatchlistBacktestParamGridParamsetFactory())->make($row);
        $service = new WatchlistPlanGroupingService();

        $result = $service->groupScoredOutput($this->scoredOutput([
            $this->c07Item(1, 'LOWR', 1.00, ['range_position_20_pct' => 20.0]),
            $this->c07Item(2, 'SECL', 0.98, ['sector_rs_20_vs_ihsg' => -5.0, 'sector_roc20' => -5.0, 'rs_20_vs_sector' => -5.0]),
            $this->c07Item(3, 'EVNT', 0.96, ['event_risk_flag' => 1]),
            $this->c07Item(4, 'GOOD', 0.94),
        ]), $paramset, '2026-05-19');

        $this->assertTrue($result['ready']);
        $active = array_merge(
            $result['groups']['TOP_PICKS'],
            $result['groups']['SECONDARY'],
            $result['groups']['WATCH_ONLY']
        );
        $this->assertSame(['GOOD'], array_column($active, 'ticker_code'));
        $this->assertSame(['LOWR', 'SECL', 'EVNT'], array_column($result['excluded'], 'ticker_code'));
        $this->assertContains('WATCHLIST_C07_RANGE_POSITION_FAIL', $result['excluded'][0]['reason_codes']);
        $this->assertContains('WATCHLIST_C07_CONFIRMATION_COUNT_FAIL', $result['excluded'][1]['reason_codes']);
        $this->assertContains('WATCHLIST_C07_EVENT_RISK_FLAG_FAIL', $result['excluded'][2]['reason_codes']);
        foreach ($result['excluded'] as $excluded) {
            $this->assertContains('WATCHLIST_C07_ENTRY_QUALITY_FLOOR_FAIL', $excluded['reason_codes']);
        }
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
            'items' => $items,
            'excluded' => [],
            'summary' => ['input_count' => count($items), 'scored_count' => count($items), 'excluded_count' => 0],
        ];
    }

    private function c07Item(int $tickerId, string $tickerCode, float $scoreTotal, array $overrides = []): array
    {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'score_components' => [
                'score_momentum' => $overrides['score_momentum'] ?? 0.42,
                'score_breakout' => $overrides['score_breakout'] ?? 0.76,
                'score_volume' => $overrides['score_volume'] ?? 0.36,
                'score_risk' => $overrides['score_risk'] ?? 0.78,
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
                    'close_to_hh20_pct' => $overrides['close_to_hh20_pct'] ?? 0.002,
                ],
            ],
            'reason_codes' => ['WS_MOM_STRONG'],
            'score_metrics' => [
                'dv20_idr' => $overrides['dv20_idr'] ?? 4500000000.0,
                'atr14_pct' => $overrides['atr14_pct'] ?? 0.0250,
                'vol_ratio' => $overrides['vol_ratio'] ?? 1.30,
                'roc5' => $overrides['roc5'] ?? 1.0,
                'roc10' => $overrides['roc10'] ?? 1.2,
                'close_to_ll20_pct' => $overrides['close_to_ll20_pct'] ?? 8.0,
                'range_position_20_pct' => $overrides['range_position_20_pct'] ?? 72.0,
                'sector_roc20' => $overrides['sector_roc20'] ?? 1.0,
                'rs_20_vs_sector' => $overrides['rs_20_vs_sector'] ?? 0.5,
                'sector_rs_20_vs_ihsg' => $overrides['sector_rs_20_vs_ihsg'] ?? 0.4,
                'corporate_action_flag' => $overrides['corporate_action_flag'] ?? 0,
                'is_suspended' => $overrides['is_suspended'] ?? 0,
                'is_uma' => $overrides['is_uma'] ?? 0,
                'event_risk_flag' => $overrides['event_risk_flag'] ?? 0,
            ],
        ];
    }
}
