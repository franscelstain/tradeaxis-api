<?php

use PHPUnit\Framework\TestCase;

class ApiBackfillLifecycleStaticGuardTest extends TestCase
{
    private $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = dirname(__DIR__, 3);
    }

    public function test_lifecycle_command_is_registered_without_changing_import_only_backfill_command()
    {
        $kernel = file_get_contents($this->root.'/app/Console/Kernel.php');
        $backfillCommand = file_get_contents($this->root.'/app/Console/Commands/MarketData/BackfillMarketDataCommand.php');
        $lifecycleCommand = file_get_contents($this->root.'/app/Console/Commands/MarketData/BackfillLifecycleCommand.php');

        $this->assertStringContainsString('BackfillLifecycleCommand::class', $kernel);
        $this->assertStringContainsString('market-data:backfill:lifecycle', $lifecycleCommand);
        $this->assertStringContainsString('MarketDataBackfillService', $backfillCommand);
        $this->assertStringContainsString('Historical import-only backfill', $backfillCommand);
    }

    public function test_api_backfill_uses_range_window_service_and_period_bound_yahoo_urls()
    {
        $adapter = file_get_contents($this->root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $config = file_get_contents($this->root.'/config/market_data.php');
        $env = file_get_contents($this->root.'/.env.example');
        $orchestrator = file_get_contents($this->root.'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');

        $this->assertStringContainsString('fetchOrLoadEodBarsRange', $adapter);
        $this->assertStringContainsString('source_acquisition_mode', $adapter);
        $this->assertStringContainsString('range_window', $adapter);
        $this->assertStringContainsString('period1={period1}', $config);
        $this->assertStringContainsString('period2={period2}', $config);
        $this->assertStringContainsString('MARKET_DATA_API_BACKFILL_WINDOW_DAYS', $env);
        $this->assertStringContainsString('ApiBackfillRangeAcquisitionService', $orchestrator);
        $this->assertStringContainsString('importDailyFromAcquiredRows', $orchestrator);
    }

    public function test_lifecycle_replay_is_gated_by_readability_and_evidence_success()
    {
        $orchestrator = file_get_contents($this->root.'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');

        $this->assertStringContainsString('isReplayEligible', $orchestrator);
        $this->assertStringContainsString("(\$case['evidence_status'] ?? null) === 'EXPORTED'", $orchestrator);
        $this->assertStringContainsString('publishability_state', $orchestrator);
        $this->assertStringContainsString('sealed_at', $orchestrator);
    }

    public function test_range_lifecycle_does_not_reintroduce_forbidden_latest_or_max_date_fallback()
    {
        $paths = [
            $this->root.'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php',
            $this->root.'/app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php',
            $this->root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
        ];

        foreach ($paths as $path) {
            $source = file_get_contents($path);
            $this->assertDoesNotMatchRegularExpression('/MAX\s*\(\s*trade_date\s*\)/i', $source, $path);
            $this->assertStringNotContainsString('raw/latest', strtolower($source), $path);
        }
    }

    public function test_source_acquisition_failures_keep_domain_reason_and_diagnostics()
    {
        $command = file_get_contents($this->root.'/app/Console/Commands/MarketData/BackfillLifecycleCommand.php');
        $orchestrator = file_get_contents($this->root.'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');
        $adapter = file_get_contents($this->root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertStringContainsString('method_exists($e, \'reasonCode\')', $command);
        $this->assertStringContainsString('diagnostic_path', $command);
        $this->assertStringContainsString('provider_error_sample', $command);
        $this->assertStringContainsString('SourceAcquisitionException $e', $orchestrator);
        $this->assertStringContainsString('blockedSourceAcquisitionSummary', $orchestrator);
        $this->assertStringContainsString('source_acquisition_diagnostics.json', $orchestrator);
        $this->assertStringContainsString('source_acquisition_checkpoint.json', $orchestrator);
        $this->assertStringContainsString('RUN_SOURCE_BAD_REQUEST', $adapter);
        $this->assertStringContainsString('RUN_SOURCE_INVALID_SYMBOL', $adapter);
        $this->assertStringContainsString('RUN_SOURCE_PROVIDER_REJECTED_RANGE', $adapter);
        $this->assertStringContainsString('sanitizeUrl', $adapter);
    }

    public function test_resume_only_failed_is_window_ticker_checkpoint_aware()
    {
        $orchestrator = file_get_contents($this->root.'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');
        $service = file_get_contents($this->root.'/app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php');

        $this->assertStringContainsString('hasFailedAcquisitionCheckpoint', $orchestrator);
        $this->assertStringContainsString('NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT', $orchestrator);
        $this->assertStringContainsString('source_acquisition_checkpoint', $orchestrator);
        $this->assertStringContainsString('tickersForWindow', $service);
        $this->assertStringContainsString('checkpointKey', $service);
        $this->assertStringContainsString('source_acquisition_checkpoints', $service);
        $this->assertStringContainsString('in_array(($row[\'state\'] ?? null), [\'FAILED\', \'RETRYING\'], true)', $service);
    }

    public function test_resume_only_failed_outputs_retry_accounting_and_non_systemic_ticker_retry_state()
    {
        $command = file_get_contents($this->root.'/app/Console/Commands/MarketData/BackfillLifecycleCommand.php');
        $orchestrator = file_get_contents($this->root.'/app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');
        $service = file_get_contents($this->root.'/app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php');

        foreach (['failed_checkpoint_total', 'failed_checkpoint_eligible', 'failed_checkpoint_retried', 'retry_success_count', 'retry_failed_count', 'skipped_failed_checkpoint_reasons'] as $field) {
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $orchestrator);
            $this->assertStringContainsString($field, $service);
        }

        $this->assertStringContainsString('FAILED_RETRY_BLOCKED', $service);
        $this->assertStringContainsString('PARTIAL_RETRY_SUCCESS', $service);
        $this->assertStringContainsString('RETRY_SUCCESS', $service);
        $this->assertStringContainsString('NO_FAILED_CHECKPOINT', $orchestrator);
    }

    public function test_source_acquisition_checkpoint_telemetry_is_keyed_per_window_and_ticker()
    {
        $service = file_get_contents($this->root.'/app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php');
        $adapter = file_get_contents($this->root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertStringContainsString('failedTickerContextsByCheckpointKey', $service);
        $this->assertStringContainsString('provider_error_sample', $service);
        $this->assertStringContainsString('sanitized_url', $service);
        $this->assertStringContainsString('failure_scope', $service);
        $this->assertStringNotContainsString("\$telemetry['provider_error_sample'] ?? (\$telemetry['response_body_sample'] ?? null)", $service);
        $this->assertStringContainsString('sanitizeErrorSample', $adapter);
        $this->assertStringContainsString('catch (\\Throwable $e)', $adapter);
    }

}
