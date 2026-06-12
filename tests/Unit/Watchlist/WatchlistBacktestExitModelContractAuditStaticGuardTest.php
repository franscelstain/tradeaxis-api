<?php

class WatchlistBacktestExitModelContractAuditStaticGuardTest extends TestCase
{
    public function test_c11_exit_model_contract_audit_command_is_registered_and_is_only(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestExitModelContractAuditCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestExitModelContractAuditService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-exit-model-contract-audit', $command);
        $this->assertStringContainsString('RunBacktestExitModelContractAuditCommand::class', $kernel);
        $this->assertStringContainsString('EXIT_MODEL_CATALOG_NOT_AUTHORIZED', $service);
        $this->assertStringContainsString('C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT', $service);
        $this->assertStringContainsString('PUBLISHED_RUNTIME_FORCES_HOLD_5', $service);
        $this->assertStringContainsString('PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS', $service);
        $this->assertStringContainsString('strategy_catalog_created', $service);
        $this->assertStringContainsString('oos_executed', $service);
        $this->assertStringContainsString('production_ready', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $command.$service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $command.$service);
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $command.$service);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-exit-model-contract-audit", $kernel);
    }
}
