<?php

use PHPUnit\Framework\TestCase;

class LoggingTraceabilityReasonCodesStaticGuardTest extends TestCase
{
    public function test_reason_code_registry_and_seed_are_synchronized(): void
    {
        $registry = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Seed.sql');

        preg_match_all('/^\| `([A-Z][A-Z0-9_]+)` \|/m', $registry, $registryMatches);
        preg_match_all('/^\(\'([A-Z][A-Z0-9_]+)\'/m', $seed, $seedMatches);

        $registryCodes = array_values(array_unique($registryMatches[1] ?? []));
        $seedCodes = array_values(array_unique($seedMatches[1] ?? []));
        sort($registryCodes);
        sort($seedCodes);

        $this->assertSame($registryCodes, $seedCodes, 'Reason_Codes_Registry.md and Reason_Codes_Seed.sql must contain the same canonical reason-code set.');
    }

    public function test_run_lifecycle_events_are_persisted_with_trace_context(): void
    {
        $repository = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodRunRepository.php');
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');

        foreach (['RUN_CREATED', 'STAGE_STARTED', 'STAGE_COMPLETED', 'STAGE_FAILED', 'RUN_FINALIZED'] as $eventType) {
            $this->assertStringContainsString($eventType, $repository.$pipeline, $eventType.' must remain part of the persisted run lifecycle trace.');
        }

        foreach (['run_id', 'trade_date_requested', 'trade_date_effective', 'source_mode', 'publishability_state'] as $contextField) {
            $this->assertStringContainsString($contextField, $repository.$pipeline, $contextField.' must remain present in run lifecycle trace payloads.');
        }
    }

    public function test_failure_held_not_readable_and_blocked_paths_have_registered_reason_codes(): void
    {
        $registry = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Seed.sql');

        foreach ([
            'RUN_COMPUTE_FAILED',
            'RUN_ELIGIBILITY_FAILED',
            'RUN_COVERAGE_EVALUATION_FAILED',
            'RUN_FINALIZE_FAILED',
            'RUN_FINALIZE_BEFORE_CUTOFF',
            'RUN_LOCK_CONFLICT',
            'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
            'RUN_SOURCE_TIMEOUT',
            'RUN_SOURCE_RATE_LIMIT',
            'RUN_SOURCE_MALFORMED_PAYLOAD',
            'COMMAND_EXECUTION_FAILED',
            'COMMAND_DRY_RUN_ONLY',
            'COMMAND_APPLY_CONFIRMED',
        ] as $reasonCode) {
            $this->assertStringContainsString('`'.$reasonCode.'`', $registry, $reasonCode.' must be registered.');
            $this->assertStringContainsString("('".$reasonCode."'", $seed, $reasonCode.' must be seeded.');
        }
    }

    public function test_coverage_finalize_pointer_correction_replay_and_evidence_reason_codes_are_registered(): void
    {
        $registry = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->readProjectFile('docs/market_data/registry/Reason_Codes_Seed.sql');

        foreach ([
            'COVERAGE_THRESHOLD_MET',
            'COVERAGE_BELOW_THRESHOLD',
            'COVERAGE_UNIVERSE_EMPTY',
            'RUN_PARTIAL_DATA',
            'RUN_DATA_DELAYED',
            'PUBLICATION_ROW_MISSING',
            'PUBLICATION_NOT_SEALED',
            'POINTER_PUBLICATION_ID_MISMATCH',
            'POINTER_RUN_ID_MISMATCH',
            'CORRECTION_ARTIFACT_UNCHANGED',
            'CORRECTION_ARTIFACT_CHANGED',
            'CORRECTION_PUBLISHED',
            'CORRECTION_FAILED',
            'EVIDENCE_COMPLETE',
            'EVIDENCE_INCOMPLETE',
            'REPLAY_MATCH',
            'REPLAY_NON_DETERMINISTIC_OUTPUT',
        ] as $reasonCode) {
            $this->assertStringContainsString('`'.$reasonCode.'`', $registry, $reasonCode.' must be registered.');
            $this->assertStringContainsString("('".$reasonCode."'", $seed, $reasonCode.' must be seeded.');
        }
    }

    public function test_traceability_inventory_exists_and_covers_critical_areas(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/ops/LOGGING_TRACEABILITY_REASON_CODES_INVENTORY.md');

        foreach ([
            'daily pipeline',
            'ingest/import',
            'source provider/API',
            'manual file',
            'coverage',
            'finalize',
            'publication',
            'pointer',
            'correction',
            'replay',
            'evidence',
            'session snapshot',
            'current publication repair',
            'command failure/blocked/skipped',
        ] as $area) {
            $this->assertStringContainsString($area, $inventory, 'Traceability inventory must cover '.$area.'.');
        }
    }

    public function test_pointer_and_correction_recovery_catches_are_not_silent(): void
    {
        $pipeline = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');

        foreach ([
            'POINTER_RESTORE_FAILED',
            'POINTER_RESOLUTION_FAILED',
            'CURRENT_PUBLICATION_MIRROR_REPAIR_FAILED',
            'POINTER_CLEANUP_FAILED',
            'CORRECTION_ARTIFACT_UNCHANGED',
            'CORRECTION_PUBLISHED',
        ] as $eventOrReason) {
            $this->assertStringContainsString($eventOrReason, $pipeline, $eventOrReason.' must remain persisted in trace/logging code.');
        }

        $this->assertStringNotContainsString('A failed restore attempt must not clear the existing baseline pointer.', $pipeline);
        $this->assertStringNotContainsString('Mirror repair is best-effort; the current pointer resolver remains authoritative.', $pipeline);
        $this->assertStringNotContainsString('Final run state below is still forced to non-readable if pointer cleanup fails.', $pipeline);
    }

    public function test_no_forbidden_latest_trade_date_shortcuts_are_introduced_in_logging_scope(): void
    {
        foreach ([
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
            'app/Infrastructure/Persistence/MarketData/EodRunRepository.php',
        ] as $path) {
            $source = $this->readProjectFile($path);
            foreach (["MAX(trade_date)", "max('trade_date')", "latest('trade_date')", "orderByDesc('trade_date')", 'ORDER BY trade_date DESC'] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $source, $path.' contains forbidden latest-date shortcut '.$forbidden);
            }
        }
    }

    private function readProjectFile($relativePath): string
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }
}
