<?php

use PHPUnit\Framework\TestCase;

class OpsCommandSurfaceRuntimeMatrixStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path, $relativePath.' must exist.');

        return file_get_contents($path);
    }

    public function test_runtime_matrix_inventory_records_locked_decision(): void
    {
        $inventory = $this->read('docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md');
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $productionInventory = $this->read('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT',
            'Status: LOCKED',
            'Current implementation status: DONE',
            'Current contract status: LOCKED',
            'DONE/LOCKED is used for the ops command surface scope only',
            '- Ops Command Surface Runtime Matrix -> DONE',
            '[RELATED_CONTRACT] OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT',
            '- OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT -> LOCKED',
            '[RELATED_IMPLEMENTATION] Ops Command Surface Runtime Matrix',
            'CLOSED_RUNTIME_PROOF_PASS',
            'This append-only update closes the fixture-limited ops-command cases',
            'Production-ready fixture matrix',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker.$productionInventory);
        }
    }

    public function test_inventory_lists_all_public_market_data_commands(): void
    {
        $inventory = $this->read('docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md');

        foreach ([
            'market-data:daily',
            'market-data:backfill',
            'market-data:backfill:lifecycle',
            'market-data:backfill:missing-tickers',
            'market-data:promote',
            'market-data:run:finalize',
            'market-data:eod-bars:ingest',
            'market-data:eod-eligibility:build',
            'market-data:eod-indicators:compute',
            'market-data:audit:hash',
            'market-data:dataset:seal',
            'market-data:evidence:export',
            'market-data:evidence-replay:full-range-current',
            'market-data:sector-indexes:ingest-api',
            'market-data:sector-indexes:import-bars',
            'market-data:sectors:import-memberships',
            'market-data:events:import-corporate-actions',
            'market-data:events:import-trading-status',
            'market-data:replay:verify',
            'market-data:replay:smoke',
            'market-data:replay:backfill',
            'market-data:replay:fixture:generate',
            'market-data:correction:request',
            'market-data:correction:approve',
            'market-data:correction:run',
            'market-data:current-publication:repair',
            'market-data:session-snapshot',
            'market-data:session-snapshot:purge',
            'market-data:provider:smoke',
        ] as $command) {
            $this->assertStringContainsString('`'.$command.'`', $inventory, $command.' must be listed in the ops runtime matrix inventory.');
        }

        $this->assertStringContainsString('php artisan --env=testing list market-data', $inventory);
        $this->assertStringContainsString('29 public market-data commands registered', $inventory);
    }

    public function test_inventory_records_help_invalid_and_seeded_runtime_proof(): void
    {
        $inventory = $this->read('docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md');

        foreach ([
            'Help Proof Matrix',
            'Invalid Input Runtime Matrix',
            'Seeded Runtime Matrix',
            'Production-Ready Runtime Matrix',
            'status=BLOCKED',
            'COMMAND_INVALID_DATE_FORMAT',
            'COMMAND_MISSING_REQUIRED_INPUT',
            'COMMAND_CORRECTION_NOT_FOUND',
            'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE',
            'COMMAND_DESTRUCTIVE_GUARD_REQUIRED',
            'NO_READABLE_PUBLICATION',
            'COMMAND_DRY_RUN_ONLY',
            'COMMAND_APPLY_CONFIRMED',
            'run_id=6',
            'run_id=33',
            'publication_id=5',
            'publication_id=27',
            'current_publication_id=5',
            'current_publication_id=27',
            'replay_id=11',
            'replay_id=14',
            'replay_id=15',
            'replay_id=18',
            'comparison_result=MATCH',
            'replay_status=PASS',
            'all_passed=1',
            'mismatch_count=0',
            'storage/app/market-data/ops-command-surface-runtime-matrix/**',
            'storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/**',
            'Provider-Smoke Safe-Mode Overlay',
            'provider_smoke_status=PASS',
            'reason_code=PROVIDER_SMOKE_OK',
            'publication_created=false',
            'pointer_switched=false',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }
    }

    public function test_inventory_closes_fixture_limited_state_changing_cases(): void
    {
        $inventory = $this->read('docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md');

        foreach ([
            'Previously Blocked Cases Closed',
            'New `market-data:daily` success path',
            'New `market-data:backfill` success path',
            'New `market-data:promote` success path',
            'Stage commands success path',
            'Real lock conflict',
            '`current-publication:repair --apply` against invalid pointer',
            '`market-data:session-snapshot` success path',
            'CLOSED_RUNTIME_PROOF_PASS',
            'RUN_LOCK_CONFLICT',
            'RUN_SOURCE_MANUAL_FILE_EMPTY',
            'COMMAND_INVALID_REQUEST_MODE',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }
    }

    public function test_command_owned_missing_input_guards_are_present_in_code(): void
    {
        $files = [
            'app/Console/Commands/MarketData/BackfillMarketDataCommand.php' => ['{start_date?} {end_date?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/VerifyReplayCommand.php' => ['{run_id?} {fixture_path?}', 'COMMAND_MISSING_REQUIRED_INPUT', 'replay_status'],
            'app/Console/Commands/MarketData/ReplaySmokeSuiteCommand.php' => ['{run_id?}', 'COMMAND_MISSING_REQUIRED_INPUT', 'COMMAND_EXECUTION_FAILED', 'replay_status'],
            'app/Console/Commands/MarketData/ReplayBackfillCommand.php' => ['{start_date?} {end_date?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php' => ['{run_id?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/ApproveCorrectionCommand.php' => ['{correction_id?}', 'COMMAND_CORRECTION_NOT_FOUND', 'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE'],
            'app/Console/Commands/MarketData/RunCorrectionCommand.php' => ['{correction_id?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/CaptureSessionSnapshotCommand.php' => ['{trade_date?} {snapshot_slot?}', 'COMMAND_MISSING_REQUIRED_INPUT'],
            'app/Console/Commands/MarketData/IngestEodBarsCommand.php' => ['--request_mode=', 'validateStageRequestModeString'],
        ];

        foreach ($files as $path => $needles) {
            $contents = $this->read($path);
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $contents, $needle.' must be present in '.$path);
            }
        }
    }

    public function test_stage_hash_service_method_is_public_for_command_runtime(): void
    {
        $method = new ReflectionMethod(
            App\Application\MarketData\Services\MarketDataPipelineService::class,
            'completeHash'
        );

        $this->assertTrue($method->isPublic(), 'market-data:audit:hash must be able to invoke completeHash at runtime.');
    }
}
