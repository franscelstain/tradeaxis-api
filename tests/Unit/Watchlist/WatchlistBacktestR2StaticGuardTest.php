<?php

class WatchlistBacktestR2StaticGuardTest extends TestCase
{
    public function test_r2_command_is_explicit_is_only_and_not_scheduled(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-is-calibrate', $command);
        foreach (['{--catalog-code=', '{--from=', '{--to=', '{--output=', '{--overwrite'] as $option) {
            $this->assertStringContainsString($option, $command);
        }
        $this->assertStringContainsString('RunBacktestIsCalibrationCommand::class', $kernel);
        $this->assertStringContainsString('SeedBacktestR2ParamGridCommand::class', $kernel);
        $this->assertStringNotContainsString('run-oos', strtolower($command));
        $this->assertStringNotContainsString('latest(', strtolower($command));
        $this->assertStringNotContainsString('max(trade_date)', strtolower($command));
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-is-calibrate", $kernel);
        $this->assertStringNotContainsString('Route::', $command);
    }

    public function test_r2_execution_has_no_oos_service_repository_or_business_mutation_dependency(): void
    {
        $files = [
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'),
            base_path('app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php'),
            base_path('app/Console/Commands/Watchlist/RunBacktestIsCalibrationCommand.php'),
        ];
        $content = implode("\n", array_map('file_get_contents', $files));
        $lower = strtolower($content);

        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $content);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $content);
        $this->assertStringNotContainsString('insertGetId($payload)', file_get_contents($files[0]));
        $this->assertStringNotContainsString("DB::table('eod_bars')", $content);
        $this->assertStringNotContainsString("DB::table('eod_indicators')", $content);
        $this->assertStringNotContainsString('promoteparamset', $lower);
        $this->assertStringNotContainsString('status\' => \'ACTIVE', $content);
        $this->assertStringNotContainsString('orderservice', $lower);
        $this->assertStringNotContainsString('brokerservice', $lower);
        $this->assertStringNotContainsString('portfolioservice', $lower);
    }

    public function test_strict_boundary_and_fixed_execution_semantics_are_explicit(): void
    {
        $runtime = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php'
        ));
        $catalog = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestR2ParamGridCatalog.php'
        ));
        $execution = file_get_contents(base_path(
            'app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php'
        ));

        $this->assertStringContainsString('hard_market_data_to_date', $runtime);
        $this->assertStringContainsString('EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PRICE_READS_WITHIN_IS', $runtime);
        $this->assertStringContainsString("public const R2_MIN_IS_DATE = '2023-01-02'", $execution);
        $this->assertStringContainsString("public const R2_MAX_IS_DATE = '2025-05-21'", $execution);
        $this->assertStringContainsString('FIXED_STOP_ATR_MULT = 1.50', $catalog);
        $this->assertStringContainsString('FIXED_MIN_RR = 1.50', $catalog);
        $this->assertStringContainsString('FIXED_TOP_PICKS_TARGET = 5', $catalog);
        $this->assertStringContainsString('FIXED_SECONDARY_TARGET = 10', $catalog);
        $this->assertStringContainsString('ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS', $execution);
    }

    public function test_every_r2_axis_has_an_explicit_runtime_consumer(): void
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
        foreach (['top_min_score_q', 'secondary_min_score_q'] as $field) {
            $this->assertStringContainsString($field, $grouping);
        }
    }

    public function test_locked_gate_and_oos_contract_files_were_not_modified(): void
    {
        $file16 = base_path('docs/watchlist/system/policies/weekly_swing/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md');
        $file17 = base_path('docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md');

        $this->assertSame('31299d858b68ee351ae898f4c9380d8753a65d8a', sha1_file($file16));
        $this->assertSame('39519a391158a7b2dcf7b6e989079788d61669be', sha1_file($file17));
    }
}
