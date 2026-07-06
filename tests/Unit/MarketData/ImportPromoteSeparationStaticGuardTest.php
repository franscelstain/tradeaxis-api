<?php

use PHPUnit\Framework\TestCase;

class ImportPromoteSeparationStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_request_mode_is_first_class_runtime_contract(): void
    {
        $dto = file_get_contents($this->projectPath('app/Application/MarketData/DTOs/MarketDataStageInput.php'));
        $repo = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodRunRepository.php'));
        $migration = file_get_contents($this->projectPath('database/migrations/2026_05_08_000001_add_request_mode_to_eod_runs.php'));
        $sqlite = file_get_contents($this->projectPath('tests/Support/UsesMarketDataSqlite.php'));
        $schema = file_get_contents($this->projectPath('docs/market_data/db/Database_Schema_MariaDB.sql'));

        foreach (['public $requestMode', 'normalizeRequestMode', 'import_only', 'promote', 'full_publish', 'correction'] as $needle) {
            $this->assertStringContainsString($needle, $dto);
        }

        foreach (['request_mode', 'idx_runs_request_mode'] as $needle) {
            $this->assertStringContainsString($needle, $migration);
            $this->assertStringContainsString($needle, $sqlite);
            $this->assertStringContainsString($needle, $schema);
        }

        $this->assertStringContainsString("'request_mode' => $".'requestMode', $repo);
        $this->assertStringContainsString('createPromoteRunFromSeed', $repo);
    }

    public function test_import_only_path_cannot_enter_publish_side_effects(): void
    {
        $pipeline = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataPipelineService.php'));
        $runs = file_get_contents($this->projectPath('app/Infrastructure/Persistence/MarketData/EodRunRepository.php'));

        foreach ([
            'assertAllowedRequestMode',
            'REQUEST_MODE_INVALID',
            'REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE',
            'assertImportOnlyDidNotPromote',
            'IMPORT_ONLY_ACCEPTED',
            'IMPORT_ONLY_COMPLETED',
            'IMPORT_ONLY_NOT_PROMOTED',
            'IMPORT_ONLY_COMPLETED_NOT_PROMOTED',
            'IMPORT_READABLE_STATE_BLOCKED',
            'IMPORT_PUBLICATION_CURRENT_BLOCKED',
            'IMPORT_POINTER_WRITE_BLOCKED',
            'findReadableCurrentPublicationForRun',
            'promote_status',
            'NOT_PROMOTED',
            'pointer_switched',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pipeline);
        }

        foreach ([
            'STALE_ACTIVE_RUN_CANCELLED',
            'active_run_stale_minutes',
            'lifecycle_state',
            'CANCELLED',
            'publishability_state',
            'NOT_READABLE',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runs);
        }

        $this->assertStringContainsString("return $".'this->executeStageSequence($requestedDate, $sourceMode, $correctionId, [', $pipeline);
        $this->assertStringContainsString("'INGEST_BARS' => 'completeIngest'", $pipeline);
        $this->assertStringContainsString("], 'import_only');", $pipeline);
    }

    public function test_promote_path_still_runs_full_gate_sequence(): void
    {
        $pipeline = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataPipelineService.php'));

        foreach ([
            'promoteSingleDay',
            'preparePromoteRunId',
            'ensurePromoteRunContext',
            'request_mode=promote',
            'PROMOTE_STARTED',
            'completeCoverageEvaluation',
            'requires_full_coverage',
            'completeIndicators',
            'completeEligibility',
            'completeHash',
            'completeSeal',
            'completeFinalize',
            'promoteCandidateToCurrent',
            'resolveCurrentReadablePublicationForTradeDate',
            'syncCurrentPublicationMirror',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pipeline);
        }
    }

    public function test_command_evidence_and_replay_expose_import_promote_boundary(): void
    {
        $command = file_get_contents($this->projectPath('app/Console/Commands/MarketData/AbstractMarketDataCommand.php'));
        $daily = file_get_contents($this->projectPath('app/Console/Commands/MarketData/DailyPipelineCommand.php'));
        $promote = file_get_contents($this->projectPath('app/Console/Commands/MarketData/PromoteMarketDataCommand.php'));
        $evidence = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $replay = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        foreach (['request_mode', 'import_status', 'promote_status', 'promoted', 'pointer_switched', 'current_publication_id'] as $field) {
            $this->assertStringContainsString($field, $command);
            $this->assertStringContainsString($field, $evidence);
            $this->assertStringContainsString($field, $replay);
        }

        $this->assertStringContainsString('request_mode=import_only', $daily);
        $this->assertStringContainsString('importDaily', $daily);
        $this->assertStringContainsString('request_mode=promote', $promote);
        $this->assertStringContainsString('promoteDaily', $promote);
        $this->assertStringContainsString('import_promote_boundary', $evidence);
        $this->assertStringContainsString('actual_import_promote_context', $replay);
        $this->assertStringContainsString('REPLAY_IMPORT_STATUS_MISMATCH', $replay);
        $this->assertStringContainsString('REPLAY_PROMOTE_STATUS_MISMATCH', $replay);
        $this->assertStringContainsString('REPLAY_UNEXPECTED_PUBLICATION_PROMOTION', $replay);
    }

    public function test_registry_seed_and_inventory_cover_import_promote_reason_codes(): void
    {
        $registry = file_get_contents($this->projectPath('docs/market_data/registry/Reason_Codes_Registry.md'));
        $seed = file_get_contents($this->projectPath('docs/market_data/registry/Reason_Codes_Seed.sql'));
        $inventory = file_get_contents($this->projectPath('docs/market_data/audit/IMPORT_PROMOTE_SEPARATION_INVENTORY.md'));
        $contract = file_get_contents($this->projectPath('docs/market_data/book/Import_Promote_Separation_Contract.md'));

        foreach ([
            'IMPORT_ONLY_ACCEPTED',
            'IMPORT_ONLY_COMPLETED',
            'IMPORT_ONLY_NOT_PROMOTED',
            'IMPORT_ONLY_COMPLETED_NOT_PROMOTED',
            'STALE_ACTIVE_RUN_CANCELLED',
            'REQUEST_MODE_INVALID',
            'REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE',
            'IMPORT_POINTER_WRITE_BLOCKED',
            'IMPORT_READABLE_STATE_BLOCKED',
            'IMPORT_PUBLICATION_CURRENT_BLOCKED',
            'PROMOTE_STARTED',
            'PROMOTE_COVERAGE_REQUIRED',
            'EVIDENCE_IMPORT_PROMOTE_BOUNDARY_INCLUDED',
            'REPLAY_IMPORT_STATUS_MISMATCH',
            'REPLAY_PROMOTE_STATUS_MISMATCH',
            'REPLAY_UNEXPECTED_PUBLICATION_PROMOTION',
        ] as $code) {
            $this->assertStringContainsString($code, $registry);
            $this->assertStringContainsString($code, $seed);
        }

        foreach (['manual file ingest', 'API ingest', 'pointer switch', 'evidence', 'replay', 'static guard'] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        foreach (['Status: ENFORCED', 'request_mode=import_only', 'request_mode=promote', 'Import-only side-effect boundary'] as $needle) {
            $this->assertStringContainsString($needle, $contract);
        }
    }

    public function test_import_promote_runtime_paths_do_not_use_forbidden_latest_trade_date_shortcuts(): void
    {
        $paths = [
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php',
            'app/Console/Commands/MarketData/AbstractMarketDataCommand.php',
        ];

        foreach ($paths as $path) {
            $source = file_get_contents($this->projectPath($path));
            foreach (["MAX(trade_date)", "max('trade_date')", "latest('trade_date')", "orderByDesc('trade_date')", 'ORDER BY trade_date DESC'] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $source, $path.' contains forbidden latest-date shortcut '.$forbidden);
            }
        }
    }
}
