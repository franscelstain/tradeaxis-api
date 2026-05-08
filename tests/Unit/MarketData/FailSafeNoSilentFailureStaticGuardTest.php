<?php

use PHPUnit\Framework\TestCase;

class FailSafeNoSilentFailureStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function readProjectFile(string $path): string
    {
        $fullPath = $this->projectPath($path);
        $this->assertFileExists($fullPath);

        return file_get_contents($fullPath);
    }

    public function test_source_paths_block_empty_success_and_zero_valid_rows(): void
    {
        $local = $this->readProjectFile('app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php');
        $api = $this->readProjectFile('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $ingest = $this->readProjectFile('app/Application/MarketData/Services/EodBarsIngestService.php');

        foreach ([
            'RUN_SOURCE_MANUAL_FILE_EMPTY',
            'manual_file_empty_blocked',
            'blockEmptyManualFile',
            "'accepted_row_count' => 0",
            "'source_final_status' => 'FAILED'",
        ] as $needle) {
            $this->assertStringContainsString($needle, $local);
        }

        foreach ([
            'RUN_SOURCE_NO_VALID_DATA',
            'empty_response_blocked',
            'buildGenericEmptyResponseTelemetry',
            "'source_final_status' => 'FAILED'",
            "'accepted_row_count' => 0",
        ] as $needle) {
            $this->assertStringContainsString($needle, $api);
            $this->assertStringContainsString($needle, $ingest.$api);
        }

        $this->assertStringContainsString('count($validRows) === 0', $ingest);
        $this->assertStringContainsString('empty bars artifact is blocked from publication', $ingest);
    }

    public function test_finalize_and_pipeline_keep_no_valid_data_non_readable_and_pointer_preserved(): void
    {
        $finalize = $this->readProjectFile('app/Application/MarketData/Services/FinalizeDecisionService.php');
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');

        foreach ([
            'extractExplicitValidDataCount',
            'RUN_SOURCE_NO_VALID_DATA',
            "'publishability_state' => 'NOT_READABLE'",
            "'promotion_allowed' => false",
            "['empty_artifact_blocked'] = true",
        ] as $needle) {
            $this->assertStringContainsString($needle, $finalize);
        }

        foreach ([
            'handleRecoverableSourceFailure',
            'RUN_SOURCE_NO_VALID_DATA',
            'findLatestReadablePublicationBefore',
            'Source acquisition failed, but prior readable publication remains available for fallback.',
            'Source acquisition failed and no prior readable publication is available; run is held as non-readable without fallback.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pipeline);
        }
    }

    public function test_reason_code_registry_and_seed_cover_fail_safe_codes(): void
    {
        $registry = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Seed.sql');

        foreach ([
            'RUN_SOURCE_NO_VALID_DATA',
            'RUN_SOURCE_MANUAL_FILE_EMPTY',
            'RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS',
            'SOURCE_NO_VALID_DATA',
            'SOURCE_PROVIDER_EMPTY_RESPONSE',
            'ARTIFACT_EMPTY',
            'EMPTY_ARTIFACT_NOT_READABLE',
            'POINTER_PRESERVED_FAIL_SAFE',
            'EVIDENCE_FAIL_SAFE_CONTEXT_INCLUDED',
            'REPLAY_FAIL_SAFE_REASON_MISMATCH',
        ] as $code) {
            $this->assertStringContainsString($code, $registry);
            $this->assertStringContainsString($code, $seed);
        }
    }

    public function test_no_silent_failure_static_inventory_and_audit_entries_exist(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md');
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([
            'FAIL_SAFE_NO_SILENT_FAILURE_CONTRACT',
            'API timeout',
            'manual file empty',
            'empty artifact',
            'pointer preserved',
            'evidence fail-safe context',
            'replay fail-safe verification',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        $this->assertStringContainsString('Fail-Safe Behavior / No Silent Failure', $status);
        $this->assertStringContainsString('FAIL_SAFE_NO_SILENT_FAILURE_CONTRACT', $tracker);
    }

    public function test_fail_safe_paths_do_not_use_forbidden_latest_shortcuts(): void
    {
        foreach ([
            'app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php',
            'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
            'app/Application/MarketData/Services/EodBarsIngestService.php',
            'app/Application/MarketData/Services/FinalizeDecisionService.php',
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
        ] as $path) {
            $source = $this->readProjectFile($path);
            foreach (["MAX(trade_date)", "max('trade_date')", "latest('trade_date')", "orderByDesc('trade_date')", 'ORDER BY trade_date DESC'] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $source, $path.' contains forbidden shortcut '.$forbidden);
            }
        }
    }
}
