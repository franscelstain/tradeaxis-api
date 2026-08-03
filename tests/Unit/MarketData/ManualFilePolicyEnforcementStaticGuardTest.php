<?php

use PHPUnit\Framework\TestCase;

class ManualFilePolicyEnforcementStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_manual_file_adapter_keeps_local_identity_and_never_calls_api_provider(): void
    {
        $adapter = file_get_contents($this->projectPath('app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php'));
        $apiAdapter = file_get_contents($this->projectPath('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php'));

        foreach ([
            "'source_mode' => 'manual_file'",
            "'source_name' => 'LOCAL_FILE'",
            "'provider' => null",
            'RUN_SOURCE_MANUAL_FILE_NOT_FOUND',
            'RUN_SOURCE_MANUAL_FILE_NOT_READABLE',
            'RUN_SOURCE_MANUAL_FILE_MALFORMED',
            'consumeLastAcquisitionTelemetry',
            'source_file_hash',
            'source_file_row_count',
            'accepted_row_count',
            'rejected_row_count',
            'invalid_row_count',
        ] as $needle) {
            $this->assertStringContainsString($needle, $adapter);
        }

        foreach (['PublicApiEodBarsAdapter', 'YAHOO_FINANCE', 'retry_max', 'timeout_seconds'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $adapter, 'Manual adapter must not leak API/provider behavior: '.$forbidden);
        }

        $this->assertStringNotContainsString('local_input_file', $apiAdapter, 'API adapter must not read manual local input files.');
    }

    public function test_import_only_promote_and_coverage_paths_remain_separated(): void
    {
        $pipeline = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataPipelineService.php'));
        $daily = file_get_contents($this->projectPath('app/Console/Commands/MarketData/DailyPipelineCommand.php'));
        $promote = file_get_contents($this->projectPath('app/Console/Commands/MarketData/PromoteMarketDataCommand.php'));

        $this->assertStringContainsString("return $".'this->importSingleDay($requestedDate, $sourceMode, $correctionId);', $pipeline);
        $this->assertStringContainsString("'INGEST_BARS' => 'completeIngest'", $pipeline);
        $this->assertStringContainsString('request_mode', $daily);
        $this->assertStringContainsString('import_only', $daily);

        $this->assertStringContainsString('completeCoverageEvaluation', $pipeline);
        $this->assertStringContainsString('requires_full_coverage', $pipeline);
        $this->assertStringContainsString('completeFinalize', $pipeline);
        $this->assertStringContainsString('coverage_gate_state', $promote);
        $this->assertStringContainsString('publish_target', $promote);
    }

    public function test_manual_file_context_is_visible_in_command_evidence_and_replay(): void
    {
        $command = file_get_contents($this->projectPath('app/Console/Commands/MarketData/AbstractMarketDataCommand.php'));
        $backfillCommand = file_get_contents($this->projectPath('app/Console/Commands/MarketData/BackfillMarketDataCommand.php'));
        $backfillService = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataBackfillService.php'));
        $evidence = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $replay = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        foreach ([
            'source_mode',
            'source_name',
            'source_input_file',
            'source_file_hash',
            'source_file_hash_algorithm',
            'source_file_size_bytes',
            'source_file_row_count',
            'accepted_row_count',
            'rejected_row_count',
            'invalid_row_count',
            'coverage_gate_state',
            'coverage_reason_code',
            'terminal_status',
            'publishability_state',
            'final_reason_code',
        ] as $field) {
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $backfillCommand.$backfillService);
            $this->assertStringContainsString($field, $evidence);
            $this->assertStringContainsString($field, $replay);
        }

        $this->assertStringContainsString('appendManualFilePolicyMismatches', $replay);
        $this->assertStringContainsString('manual_file_readable_coverage_policy', $replay);
    }

    // The latest-trade-date prohibition previously checked six named paths here.
    // ReadPathShortcutProhibitionTest applies it to the whole runtime.
}
