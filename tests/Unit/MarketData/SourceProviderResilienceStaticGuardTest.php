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

    public function test_runtime_source_and_fallback_paths_do_not_use_forbidden_latest_trade_date_shortcuts(): void
    {
        foreach ([
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
            'app/Application/MarketData/Services/FinalizeDecisionService.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
            'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
            'app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php',
        ] as $path) {
            $source = $this->readProjectFile($path);
            foreach (["MAX(trade_date)", "max('trade_date')", "latest('trade_date')", "orderByDesc('trade_date')", 'ORDER BY trade_date DESC'] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $source, $path.' contains forbidden latest trade_date shortcut '.$forbidden);
            }
        }
    }

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
