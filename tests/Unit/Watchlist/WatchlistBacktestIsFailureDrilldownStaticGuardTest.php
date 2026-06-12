<?php

class WatchlistBacktestIsFailureDrilldownStaticGuardTest extends TestCase
{
    public function test_is_failure_drilldown_command_is_explicit_is_only_file_artifact_surface(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php'));
        $batchCommand = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestIsDiagnoseBatchCommand.php'));
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $combined = $command."\n".$batchCommand."\n".$service;

        $this->assertStringContainsString('watchlist:backtest-is-diagnose', $command);
        $this->assertStringContainsString('watchlist:backtest-is-diagnose-batch', $batchCommand);
        $this->assertStringContainsString('RunBacktestIsDiagnoseCommand::class', $kernel);
        $this->assertStringContainsString('RunBacktestIsDiagnoseBatchCommand::class', $kernel);
        foreach (['{--catalog-code=', '{--from=', '{--to=', '{--param-id=', '{--row-code=', '{--output=', '{--overwrite'] as $option) {
            $this->assertStringContainsString($option, $command);
        }
        foreach (['{--catalog-code=', '{--from=', '{--to=', '{--param-ids=', '{--output-dir=', '{--summary=', '{--overwrite'] as $option) {
            $this->assertStringContainsString($option, $batchCommand);
        }
        $this->assertStringContainsString('row_filter', $service);
        $this->assertStringContainsString('WS_BT_IS_DRILLDOWN_ROW_FILTER_NO_MATCH', $service);
        $this->assertStringContainsString('hard_market_data_to_date', $service);
        $this->assertStringContainsString('no_latest_catalog_fallback', $service);
        $this->assertStringContainsString('no_active_catalog_fallback', $service);
        $this->assertStringContainsString('no_current_date_default', $service);
        $this->assertStringContainsString('no_max_trade_date_default', $service);
        $this->assertStringContainsString('canonical_artifact_hash', $service);
        $this->assertStringContainsString('is_trading_date_hash', $service);
        $this->assertStringContainsString('runtime_consumed_parameter_summary', $service);
        $this->assertStringContainsString('score_component_effectiveness_summary', $service);
        $this->assertStringContainsString('sector_bucket_summary', $service);
        $this->assertStringContainsString('event_risk_flag_summary', $service);
        $this->assertStringContainsString('corporate_action_types', $service);
        $this->assertStringContainsString('trading_status_code', $service);
        $this->assertStringContainsString('event_risk_reasons', $service);
        $this->assertStringContainsString('FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE', $service);
        $this->assertStringContainsString('NOT_DERIVED', $service);
        $this->assertStringContainsString('NOT_USED_FOR_NEXT_CATALOG_DECISION', $service);
        $this->assertStringContainsString('IS_ONLY_BATCHED_FAILURE_DRILLDOWN', $batchCommand);
        $this->assertStringContainsString('oos_service_invoked=0', $batchCommand);
        $this->assertStringContainsString('oos_repository_invoked=0', $batchCommand);
        $this->assertStringContainsString('production_ready=0', $batchCommand);
        $this->assertStringContainsString("unset(\n            \$artifact['artifact_hash']", $service);
        $this->assertStringContainsString('oos_service_invoked', $service);
        $this->assertStringContainsString('oos_repository_invoked', $service);
        $this->assertStringContainsString('production_ready', $service);
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $combined);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $combined);
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $combined);
        $this->assertStringNotContainsString('status\' => \'ACTIVE', $combined);
        $this->assertStringNotContainsString('promoteparamset', strtolower($combined));
        $this->assertStringNotContainsString('paramset_active', strtolower($combined));
        $this->assertStringNotContainsString('latest(', strtolower($combined));
        $this->assertStringNotContainsString('max(trade_date)', strtolower($combined));
        $this->assertStringNotContainsString('Route::', $combined);
    }
}
