<?php

class WatchlistBacktestExitModelRedesignContractStaticGuardTest extends TestCase
{
    public function test_c12_redesign_contract_command_is_registered_and_contract_only(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestExitModelRedesignContractCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestExitModelRedesignContractService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));

        $this->assertStringContainsString('watchlist:backtest-exit-model-redesign-contract', $command);
        $this->assertStringContainsString('RunBacktestExitModelRedesignContractCommand::class', $kernel);
        $this->assertStringContainsString('EXIT_MODEL_REDESIGN_CONTRACT_READY', $service);
        $this->assertStringContainsString('IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG', $service);
        $this->assertStringContainsString('CONDITIONALLY_ALLOWED_FOR_FUTURE_IMPLEMENTATION', $service);
        $this->assertStringContainsString('BLOCKED_FOR_FIRST_PHASE_CATALOG', $service);
        $this->assertStringContainsString('catalog_creation_authorized', $service);
        $this->assertStringContainsString('do_not_mutate_r1_r2_c01_c02_c03_c04_c05_c06_c07', $service);
        $this->assertStringContainsString('do_not_run_oos', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $command.$service);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $command.$service);
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $command.$service);
        $this->assertStringNotContainsString("schedule->command('watchlist:backtest-exit-model-redesign-contract", $kernel);
    }
}
