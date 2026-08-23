<?php

use PHPUnit\Framework\TestCase;

class SourceProviderResilienceStaticGuardTest extends TestCase
{
    public function test_api_adapter_tracks_timeout_rate_limit_retry_attempts_and_partial_yahoo_response(): void
    {
        $source = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        foreach (['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT', 'attempt_count', 'retry_exhausted', 'final_reason_code', 'source_final_status', 'RUN_SOURCE_PARTIAL_RESPONSE'] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }

        $this->assertStringContainsString('buildYahooAggregateTelemetry', $source);
        $this->assertStringContainsString('isYahooPartialTolerantFailure', $source);
        $this->assertStringContainsString('failed_ticker_count', $source);
        $this->assertStringContainsString('missing_ticker_count', $source);
        $this->assertStringContainsString('failure_reason_summary', $source);
    }


    public function test_all_provider_fanout_paths_have_source_protection_and_retry_is_transient_only(): void
    {
        $source = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        foreach (['fetchYahooFinanceBars', 'fetchYahooFinanceBarsRange', 'fetchOrLoadBenchmarkBars'] as $method) {
            $body = $this->extractMethod($source, $method, $method === 'fetchOrLoadBenchmarkBars' ? 'public' : 'private');
            $this->assertStringContainsString('circuitBreakerTelemetry', $body, $method.' must stop fan-out when source protection opens.');
            $this->assertStringContainsString('requestWithRetry', $body, $method.' must use the shared throttled/retry transport.');
        }

        $transport = $this->extractMethod($source, 'requestWithRetry');
        $this->assertStringContainsString('applyThrottleAndJitter', $transport);
        $this->assertStringContainsString('shouldRetry', $transport);
        $this->assertStringContainsString('backoff', $transport);

        $retryClassifier = $this->extractMethod($source, 'shouldRetry');
        $this->assertStringContainsString("['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT']", $retryClassifier);
        foreach (['RUN_SOURCE_AUTH_ERROR', 'RUN_SOURCE_RESPONSE_CHANGED', 'PROVIDER_SYMBOL_MAPPING_AMBIGUOUS'] as $nonTransient) {
            $this->assertStringNotContainsString($nonTransient, $retryClassifier);
        }
    }

    public function test_circuit_breaker_uses_only_the_registered_configured_threshold(): void
    {
        $source = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $method = $this->extractMethod($source, 'openCircuitBreaker');
        $config = $this->readProjectFile('config/market_data.php');
        $registry = $this->readProjectFile('docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md');

        $this->assertStringContainsString("config('market_data.provider.circuit_breaker_error_rate'", $method);
        $this->assertStringContainsString('($failureCount / $universeCount) > $threshold', $method);
        $this->assertStringNotContainsString('minimumAttempts', $method);
        $this->assertStringNotContainsString('minimum_sample', $method);
        $this->assertStringContainsString("'circuit_breaker_error_rate'", $config);
        $this->assertStringContainsString('market_data.provider.circuit_breaker_error_rate', $registry);
    }

    public function test_retry_budget_uses_the_registered_config_without_hidden_clamp(): void
    {
        $adapter = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $registry = $this->readProjectFile('docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md');

        $retryMax = $this->extractMethod($adapter, 'retryMax');
        $this->assertStringContainsString("config('market_data.provider.api_retry_max')", $retryMax);
        $this->assertStringNotContainsString('min(3', $retryMax);
        $this->assertStringNotContainsString('min(3', $pipeline);
        $this->assertStringContainsString('market_data.provider.api_retry_max', $registry);
    }

    public function test_required_resilience_audit_fields_survive_into_evidence_projection(): void
    {
        $adapter = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $manual = $this->readProjectFile('app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php');
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $repository = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $evidence = $this->readProjectFile('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');

        foreach (['source_priority', 'active_source_decision', 'retry_attempt_count', 'failure_class_summary'] as $field) {
            $this->assertStringContainsString($field, $adapter);
            $this->assertStringContainsString($field, $manual);
            $this->assertStringContainsString($field, $pipeline);
            $this->assertStringContainsString($field, $repository);
            $this->assertStringContainsString($field, $evidence);
        }

        foreach (['payload_hash', 'schema_fingerprint', 'validation_state', 'md_source_observation_rejected_rows', 'source_observation_rejection_reason_summary'] as $field) {
            $this->assertStringContainsString($field, $repository);
        }
        $this->assertStringContainsString('exportRunSourceObservationAudit', $repository);
        $this->assertStringContainsString('source_observation_audit', $evidence);
    }

    public function test_source_capability_availability_trigger_is_bound_to_five_expected_sessions(): void
    {
        $slo = $this->readProjectFile('docs/market_data/authority/strategy/ops/Performance_SLO_and_Limits_LOCKED.md');

        $this->assertStringContainsString('Acquisition failure for **5 consecutive expected sessions**', $slo);
        $this->assertStringContainsString('Both are expressed in **expected sessions** resolved from the governed calendar', $slo);
        $this->assertStringNotContainsString('Acquisition failure for **5 consecutive calendar days**', $slo);
    }

    public function test_circuit_breaker_is_telemetry_not_an_unregistered_terminal_reason(): void
    {
        $source = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertStringNotContainsString('RUN_SOURCE_CIRCUIT_BREAKER_OPEN', $source);
        $this->assertStringContainsString("'circuit_breaker_open' => true", $source);
        $this->assertStringContainsString("'source_protection_state' => 'CIRCUIT_OPEN'", $source);
        $this->assertStringContainsString("'circuit_breaker_trigger_reason_code'", $source);
        $this->assertStringContainsString('dominantYahooFailureReasonCode', $this->extractMethod($source, 'circuitBreakerTelemetry'));
    }

    public function test_source_protection_state_is_audit_visible_without_a_second_schema_truth(): void
    {
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $evidenceRepository = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $evidence = $this->readProjectFile('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $command = $this->readProjectFile('app/Console/Commands/MarketData/AbstractMarketDataCommand.php');
        $sectorService = $this->readProjectFile('app/Application/MarketData/Services/SectorIndexApiIngestService.php');
        $sectorCommand = $this->readProjectFile('app/Console/Commands/MarketData/IngestSectorIndexBarsApiCommand.php');

        foreach ([
            'circuit_breaker_open',
            'source_protection_state',
            'unattempted_acquisition_unit_count',
            'circuit_breaker_trigger_reason_code',
        ] as $field) {
            $this->assertStringContainsString($field, $pipeline);
            $this->assertStringContainsString($field, $evidenceRepository);
            $this->assertStringContainsString($field, $evidence);
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $sectorService);
        }

        foreach (['circuit_breaker_open', 'source_protection_state', 'unattempted_acquisition_unit_count', 'circuit_breaker_trigger_reason_code'] as $field) {
            $this->assertStringContainsString($field, $sectorCommand);
        }

        $this->assertStringContainsString("'source_acquisition' => \$sourceAcquisition", $pipeline);
        $this->assertStringContainsString("DB::table('eod_run_events')", $evidenceRepository);
        $this->assertStringNotContainsString('source_circuit_breaker_open', $this->readProjectFile('database/migrations/2026_08_22_000002_harden_source_observation_acquisition.php'));
    }


    public function test_resilience_audit_fields_propagate_through_operational_recovery_projections(): void
    {
        $backfill = $this->readProjectFile('app/Application/MarketData/Services/MarketDataBackfillService.php');
        $command = $this->readProjectFile('app/Console/Commands/MarketData/AbstractMarketDataCommand.php');
        $sectorService = $this->readProjectFile('app/Application/MarketData/Services/SectorIndexApiIngestService.php');
        $sectorCommand = $this->readProjectFile('app/Console/Commands/MarketData/IngestSectorIndexBarsApiCommand.php');

        foreach (['source_priority', 'active_source_decision', 'retry_attempt_count', 'failure_class_summary'] as $field) {
            $this->assertStringContainsString($field, $backfill);
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $sectorService);
            $this->assertStringContainsString($field, $sectorCommand);
        }

        foreach (['source_protection_state', 'attempted_acquisition_unit_count', 'unattempted_acquisition_unit_count', 'circuit_breaker_trigger_reason_code'] as $field) {
            $this->assertStringContainsString($field, $backfill);
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $sectorService);
            $this->assertStringContainsString($field, $sectorCommand);
        }

        $this->assertStringContainsString('source_failure_class_summary_json', $backfill);
        $this->assertStringContainsString('source_failure_class_summary_json', $command);
    }

    public function test_manual_file_adapter_uses_explicit_source_reason_codes_and_never_api_identity(): void
    {
        $source = $this->readProjectFile('app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php');

        foreach (['RUN_SOURCE_MANUAL_FILE_NOT_FOUND', 'RUN_SOURCE_MANUAL_FILE_NOT_READABLE', 'RUN_SOURCE_MANUAL_FILE_MALFORMED', 'manualFileException', "'source_name' => 'LOCAL_FILE'", "'provider' => null"] as $needle) {
            $this->assertStringContainsString($needle, $source);
        }

        $this->assertStringNotContainsString('YAHOO_FINANCE', $source);
        $this->assertStringNotContainsString('API_FREE', $source);
        $this->assertStringNotContainsString('PublicApiEodBarsAdapter', $source);
    }

    public function test_pipeline_source_failure_is_controlled_and_preserves_pointer_via_internal_readable_fallback(): void
    {
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');

        foreach (['handleRecoverableSourceFailure', "'terminal_status' => 'HELD'", "'publishability_state' => 'NOT_READABLE'", 'sourceTelemetryColumns', 'sourceFailureNoteSegments', 'source_final_reason_code'] as $needle) {
            $this->assertStringContainsString($needle, $pipeline);
        }

        $this->assertStringContainsString('findLatestReadablePublicationBefore', $pipeline);
        $this->assertStringContainsString('source_mode is immutable', $pipeline);
        $this->assertStringNotContainsString("terminal_status' => 'SUCCESS',\n                'publishability_state' => 'READABLE'", $this->extractMethod($pipeline, 'handleRecoverableSourceFailure'));
    }

    public function test_evidence_and_replay_carry_source_provider_context(): void
    {
        $evidence = $this->readProjectFile('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $replay = $this->readProjectFile('app/Application/MarketData/Services/ReplayVerificationService.php');
        $repo = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php');

        foreach (['source_mode', 'source_name', 'source_provider', 'source_timeout_seconds', 'source_retry_max', 'source_attempt_count', 'source_retry_exhausted', 'source_final_reason_code', 'source_input_file'] as $field) {
            $this->assertStringContainsString($field, $evidence);
            $this->assertStringContainsString($field, $replay);
            $this->assertStringContainsString($field, $repo);
        }

        $this->assertStringContainsString('expectedSourceContext', $replay);
        $this->assertStringContainsString('buildReplayActualSourceContext', $evidence);
        $this->assertStringContainsString('buildReplayExpectedSourceContext', $evidence);
    }

    // The latest-trade-date prohibition previously checked seven named paths here.
    // ReadPathShortcutProhibitionTest applies it to the whole runtime.

    private function readProjectFile($relativePath): string
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    private function extractMethod($source, $methodName, $visibility = 'private'): string
    {
        $pattern = '/'.$visibility.' function '.preg_quote($methodName, '/').'\([^)]*\)\s*(?::\s*[^\s{]+)?\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
