<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use PHPUnit\Framework\TestCase;

class ApiBackfillLifecycleStaticGuardTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    private $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = dirname(__DIR__, 3);
        $this->bindMarketDataConfig();
    }

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
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

    public function test_diagnostic_reason_code_uses_failed_checkpoint_reason_when_summary_reason_is_missing()
    {
        $diagnostic = $this->invokeOrchestratorPrivate('buildSourceDiagnosticFromAcquired', [[
            'source_mode' => 'api',
            'source_acquisition_mode' => 'range_window',
            'source_acquisition_state' => 'FAILED_RETRY_BLOCKED',
        ], [
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_checkpoints' => [
                '2026-01-01|2026-03-31|WBSA' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-01',
                    'window_end' => '2026-03-31',
                    'ticker_code' => 'WBSA',
                    'reason_code' => 'RUN_SOURCE_BAD_REQUEST',
                    'http_status' => 400,
                    'failure_scope' => 'ticker',
                ],
            ],
        ]]);

        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $diagnostic['reason_code']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $diagnostic['failures_sample'][0]['reason_code']);
    }

    public function test_diagnostic_reason_code_uses_explicit_reason_before_checkpoint_reason()
    {
        $diagnostic = $this->invokeOrchestratorPrivate('buildSourceDiagnosticFromAcquired', [[
            'source_mode' => 'api',
            'source_acquisition_mode' => 'range_window',
            'source_acquisition_state' => 'FAILED_RETRY_BLOCKED',
            'reason_code' => 'RUN_SOURCE_BAD_REQUEST',
        ], [
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_checkpoints' => [
                '2026-01-01|2026-03-31|AAAA' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-01',
                    'window_end' => '2026-03-31',
                    'ticker_code' => 'AAAA',
                    'reason_code' => 'RUN_SOURCE_TIMEOUT',
                ],
            ],
        ]]);

        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $diagnostic['reason_code']);
    }

    public function test_diagnostic_reason_code_selects_dominant_reason_deterministically()
    {
        $diagnostic = $this->invokeOrchestratorPrivate('buildSourceDiagnosticFromAcquired', [[
            'source_mode' => 'api',
            'source_acquisition_mode' => 'range_window',
            'source_acquisition_state' => 'FAILED_RETRY_BLOCKED',
        ], [
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_checkpoints' => [
                '2026-01-02|2026-01-02|ZZZZ' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-02',
                    'window_end' => '2026-01-02',
                    'ticker_code' => 'ZZZZ',
                    'reason_code' => 'RUN_SOURCE_BAD_REQUEST',
                ],
                '2026-01-01|2026-01-01|BBBB' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-01',
                    'window_end' => '2026-01-01',
                    'ticker_code' => 'BBBB',
                    'reason_code' => 'RUN_SOURCE_TIMEOUT',
                ],
                '2026-01-03|2026-01-03|CCCC' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-03',
                    'window_end' => '2026-01-03',
                    'ticker_code' => 'CCCC',
                    'reason_code' => 'RUN_SOURCE_TIMEOUT',
                ],
            ],
        ]]);

        $this->assertSame('RUN_SOURCE_TIMEOUT', $diagnostic['reason_code']);
    }

    public function test_no_failed_checkpoint_diagnostic_keeps_existing_noop_reason()
    {
        $diagnostic = $this->invokeOrchestratorPrivate('buildSourceDiagnosticFromSummary', [[
            'source_mode' => 'api',
            'source_acquisition_mode' => 'range_window',
            'source_acquisition_state' => 'NO_FAILED_CHECKPOINT',
            'source_final_status' => 'NO_FAILED_CHECKPOINT',
            'reason_code' => 'NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT',
        ], [
            'source_acquisition_state' => 'NO_FAILED_CHECKPOINT',
            'reason_code' => 'NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT',
        ]]);

        $this->assertSame('NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT', $diagnostic['reason_code']);
    }

    public function test_source_acquisition_cache_is_slim_valid_json_and_sanitized()
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'market-data-cache-'.str_replace('.', '', uniqid('', true));
        mkdir($dir, 0777, true);
        $longSample = str_repeat('X', 620).' token=SECRET';

        $this->invokeOrchestratorPrivate('writeAcquisitionCache', [$dir, [
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_mode' => 'range_window',
            'source_acquisition_state' => 'FAILED_RETRY_BLOCKED',
            'windows' => [['start' => '2026-01-01', 'end' => '2026-03-31']],
            'window_count' => 1,
            'ticker_count' => 2,
            'trading_dates' => ['2026-01-02'],
            'estimated_http_requests' => 2,
            'rows_by_trade_date' => [
                '2026-01-02' => [
                    ['ticker_code' => 'AAAA', 'trade_date' => '2026-01-02', 'close' => 1],
                ],
            ],
            'window_telemetry' => [[
                'source_window_start' => '2026-01-01',
                'source_window_end' => '2026-03-31',
                'source_acquisition_state' => 'FAILED_RETRY_BLOCKED',
                'failed_ticker_contexts' => [
                    'WBSA' => [
                        'ticker_code' => 'WBSA',
                        'source_window_start' => '2026-01-01',
                        'source_window_end' => '2026-03-31',
                        'final_reason_code' => 'RUN_SOURCE_BAD_REQUEST',
                        'provider_error_sample' => $longSample,
                        'sanitized_url' => 'https://example.test/chart/WBSA?token=SECRET&safe=1',
                    ],
                ],
            ]],
            'source_acquisition_checkpoints' => [
                '2026-01-01|2026-03-31|AAAA' => [
                    'state' => 'SUCCESS',
                    'window_start' => '2026-01-01',
                    'window_end' => '2026-03-31',
                    'ticker_code' => 'AAAA',
                ],
                '2026-01-01|2026-03-31|WBSA' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-01',
                    'window_end' => '2026-03-31',
                    'ticker_code' => 'WBSA',
                    'reason_code' => 'RUN_SOURCE_BAD_REQUEST',
                    'http_status' => 400,
                    'failure_scope' => 'ticker',
                    'attempt_count' => 1,
                    'rows_count' => 0,
                    'error_sample' => $longSample,
                    'provider_error_sample' => $longSample,
                    'sanitized_url' => 'https://example.test/chart/WBSA?token=SECRET&safe=1',
                    'created_at' => '2026-05-25 00:00:00',
                    'updated_at' => '2026-05-25 00:00:00',
                ],
            ],
            'failed_checkpoint_total' => 1,
            'failed_checkpoint_eligible' => 1,
            'failed_checkpoint_retried' => 1,
            'failed_checkpoint_retry_failed' => 1,
            'retry_failed_count' => 1,
        ]]);

        $path = $dir.DIRECTORY_SEPARATOR.'source_acquisition_cache.json';
        $raw = file_get_contents($path);
        $cache = json_decode($raw, true);
        $failed = $cache['failed_source_acquisition_checkpoints']['2026-01-01|2026-03-31|WBSA'];

        $this->assertIsArray($cache);
        $this->assertSame('source_acquisition_resume_v2_slim', $cache['cache_format']);
        $this->assertFalse($cache['cache_supports_row_resume']);
        $this->assertArrayNotHasKey('rows_by_trade_date', $cache);
        $this->assertArrayNotHasKey('source_acquisition_checkpoints', $cache);
        $this->assertStringNotContainsString('failed_ticker_contexts', $raw);
        $this->assertStringNotContainsString('SECRET', $raw);
        $this->assertLessThanOrEqual(500, strlen($failed['error_sample']));
        $this->assertLessThanOrEqual(500, strlen($failed['provider_error_sample']));
        $this->assertStringEndsWith('...[truncated]', $failed['error_sample']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $failed['reason_code']);
        $this->assertSame(1, $cache['failed_checkpoint_total']);

        unlink($path);
        rmdir($dir);
    }

    private function invokeOrchestratorPrivate($method, array $arguments)
    {
        $reflection = new ReflectionClass(App\Application\MarketData\Services\BackfillLifecycleOrchestrator::class);
        $orchestrator = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($orchestrator, $arguments);
    }

}
