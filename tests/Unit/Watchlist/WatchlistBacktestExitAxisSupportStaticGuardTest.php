<?php

class WatchlistBacktestExitAxisSupportStaticGuardTest extends TestCase
{
    public function test_command_is_registered_and_keeps_no_oos_markers(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestExitAxisSupportAuditCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestExitAxisSupportAuditService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-exit-axis-support-audit', $command);
        $this->assertStringContainsString('RunBacktestExitAxisSupportAuditCommand::class', $kernel);
        $this->assertStringContainsString('strategy_catalog_created=0', $command);
        $this->assertStringContainsString('oos_service_invoked=0', $command);
        $this->assertStringContainsString('oos_repository_invoked=0', $command);
        $this->assertStringContainsString('oos_executed=0', $command);
        $this->assertStringContainsString('production_ready=0', $command);
        $this->assertStringContainsString("'strategy_catalog_created' => false", $service);
        $this->assertStringContainsString("'oos_executed' => false", $service);
        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-exit-axis-support-audit", $kernel);
    }

    public function test_support_boundary_only_allows_c12_first_phase_risk_axes(): void
    {
        $support = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestExitAxisSupport.php'));
        $factory = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php'));

        $this->assertStringContainsString('VARIABLE_RISK_EXIT_AXIS_V1', $support);
        $this->assertStringContainsString('risk.stop_atr_mult', $support);
        $this->assertStringContainsString('risk.min_rr', $support);
        $this->assertStringContainsString('backtest.holding_days', $support);
        $this->assertStringContainsString('backtest.target_pct', $support);
        $this->assertStringContainsString('backtest.stop_pct', $support);
        $this->assertStringContainsString('first-phase exit-axis support blocks holding_days and fixed percent exits', $support);
        $this->assertStringContainsString('WS_BT_R2_CATALOG_INVALID: fixed execution/grouping snapshot drifted.', $support);
        $this->assertStringContainsString('WatchlistBacktestExitAxisSupport::resolve', $factory);
    }
}
