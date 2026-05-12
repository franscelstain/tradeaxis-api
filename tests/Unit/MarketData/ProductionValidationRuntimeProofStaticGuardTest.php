<?php

use PHPUnit\Framework\TestCase;

class ProductionValidationRuntimeProofStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function readProjectFile(string $path): string
    {
        $fullPath = $this->projectPath($path);
        $this->assertFileExists($fullPath, $path.' must exist.');

        return file_get_contents($fullPath);
    }

    public function test_production_validation_inventory_exists(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'Production Validation Inventory',
            'PRODUCTION_VALIDATION_CONTRACT',
            'Production Validation / Manual + Runtime Proof',
            'runtime proof',
            'manual validation',
            'STATIC_PROOF_ONLY',
            'PENDING_RUNTIME_EVIDENCE',
            'PARTIAL_RUNTIME_PROOF',
            'READY_FOR_LOCAL_RUNTIME_VALIDATION',
            'DONE requires runtime evidence',
            'LOCKED requires targeted and full suite PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must be documented in production validation inventory.');
        }
    }

    public function test_production_validation_contract_is_tracked(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([$status, $tracker, $inventory] as $document) {
            $this->assertStringContainsString('Production Validation', $document);
            $this->assertStringContainsString('PRODUCTION_VALIDATION_CONTRACT', $document);
            $this->assertStringContainsString('DONE', $document);
        }

        $this->assertStringContainsString('LOCKED', $tracker.$inventory);

        $implementationBlock = $this->implementationBlock($status, 'Production Validation / Manual + Runtime Proof');
        $contractBlock = $this->contractBlock($tracker, 'PRODUCTION_VALIDATION_CONTRACT');

        $this->assertStringContainsString('- Production Validation / Manual + Runtime Proof -> DONE', $implementationBlock);
        $this->assertStringContainsString('- PRODUCTION_VALIDATION_CONTRACT -> LOCKED', $contractBlock);
        $this->assertStringContainsString('ProductionValidation filter OK (10 tests, 131 assertions)', $status.$tracker.$inventory);
        $this->assertStringContainsString('full MarketData suite OK (378 tests, 5072 assertions)', $status.$tracker.$inventory);
    }

    public function test_validation_inventory_lists_required_phpunit_commands(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "ProductionValidation"',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"',
            'vendor/bin/phpunit tests/Unit/MarketData',
            'OK (... tests, ... assertions)',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must be listed as a required PHPUnit validation command.');
        }
    }

    public function test_validation_inventory_lists_required_artisan_commands(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'php artisan list | findstr market-data',
            'php artisan market-data:daily --help',
            'php artisan market-data:promote --help',
            'php artisan market-data:evidence:export --help',
            'php artisan market-data:replay:verify --help',
            'php artisan market-data:replay:fixture:generate --help',
            'php artisan market-data:correction:request --help',
            'php artisan market-data:correction:approve --help',
            'php artisan market-data:correction:run --help',
            'php artisan market-data:eod-bars:ingest --help',
            'php artisan market-data:eod-indicators:compute --help',
            'php artisan market-data:eod-eligibility:build --help',
            'php artisan market-data:audit:hash --help',
            'php artisan market-data:dataset:seal --help',
            'php artisan market-data:run:finalize --help',
            'php artisan market-data:replay:smoke --help',
            'php artisan market-data:replay:backfill --help',
            'php artisan market-data:backfill --help',
            'php artisan market-data:session-snapshot --help',
            'php artisan market-data:session-snapshot:purge --help',
            'php artisan market-data:current-publication:repair --help',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must be listed as a required artisan validation command.');
        }
    }

    public function test_validation_inventory_requires_runtime_evidence_before_done(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([
            'Static proof cannot replace runtime proof',
            'Do not mark this implementation DONE unless',
            'Do not mark this contract LOCKED unless',
            'DONE requires runtime evidence',
            'LOCKED requires targeted and full suite PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker, $needle.' must remain enforced.');
        }

        $this->assertStringContainsString('- Production Validation / Manual + Runtime Proof -> DONE', $status);
        $this->assertStringContainsString('- PRODUCTION_VALIDATION_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('20-command command list/full help', $status.$tracker.$inventory);
        $this->assertStringContainsString('all_passed=1', $status.$tracker.$inventory);
        $this->assertStringContainsString('mismatch_count=0', $status.$tracker.$inventory);
    }

    public function test_validation_inventory_requires_evidence_export_runtime_proof(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'PENDING_EVIDENCE_RUNTIME_PROOF',
            'php artisan market-data:evidence:export --run_id=<RUN_ID> --output_dir=storage/app/market-data/evidence',
            'php artisan market-data:evidence:export --correction_id=<CORRECTION_ID> --output_dir=storage/app/market-data/evidence',
            'php artisan market-data:evidence:export --replay_id=<REPLAY_ID> --output_dir=storage/app/market-data/evidence',
            'php artisan market-data:evidence:export --trade_date=<YYYY-MM-DD> --output_dir=storage/app/market-data/evidence',
            'evidence output path printed or inferable',
            'manifest exists',
            'run context exists',
            'publication context exists',
            'pointer/current-publication context exists',
            'coverage context exists',
            'source context exists',
            'lineage context exists',
            'reason code exists',
            'no proof requires manual DB query as primary validation',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must remain required for evidence runtime proof.');
        }
    }

    public function test_validation_inventory_requires_replay_runtime_proof(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'PENDING_REPLAY_RUNTIME_PROOF',
            'php artisan market-data:replay:verify <RUN_ID> <FIXTURE_PATH> --output_dir=storage/app/market-data/replay',
            'php artisan market-data:replay:smoke <RUN_ID> --output_dir=storage/app/market-data/replay',
            'php artisan market-data:replay:backfill --from=<YYYY-MM-DD> --to=<YYYY-MM-DD> --output_dir=storage/app/market-data/replay',
            'php artisan market-data:replay:fixture:generate <RUN_ID> --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-<RUN_ID>',
            'replay result PASS/FAIL is explicit',
            'valid fixture case passes deterministically',
            'generated runtime fixture returns MATCH',
            'reason code mismatch case fails with reason code',
            'broken manifest case fails with reason code',
            'missing file case fails with reason code',
            'output artifact is created',
            'replay does not use raw/staging/latest/MAX(date)',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must remain required for replay runtime proof.');
        }
    }

    public function test_validation_inventory_tracks_missing_runtime_proof_as_pending(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'PENDING_RUNTIME_EVIDENCE',
            'PENDING_FLOW_RUNTIME_PROOF',
            'PENDING_EVIDENCE_RUNTIME_PROOF',
            'PENDING_REPLAY_RUNTIME_PROOF',
            'PARTIAL_RUNTIME_PROOF',
            'STATIC_PROOF_ONLY',
            'READY_FOR_LOCAL_RUNTIME_VALIDATION',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must stay visible until closed by actual runtime output.');
        }
    }

    public function test_audit_docs_do_not_claim_locked_without_runtime_evidence(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $implementationBlock = $this->implementationBlock($status, 'Production Validation / Manual + Runtime Proof');
        $contractBlock = $this->contractBlock($tracker, 'PRODUCTION_VALIDATION_CONTRACT');

        foreach ([$implementationBlock, $contractBlock] as $block) {
            $this->assertStringContainsString('vendor/` is absent', $block);
            $this->assertStringContainsString('not run in container', $block);
            $this->assertStringContainsString('Operator-local', $block);
            $this->assertStringContainsString('full MarketData', $block);
            $this->assertStringContainsString('20 registered market-data commands', $block);
            $this->assertStringContainsString('all_passed=1', $block);
            $this->assertStringContainsString('correction_evidence.json', $block);
        }

        $this->assertStringContainsString('-> DONE', $implementationBlock);
        $this->assertStringContainsString('-> LOCKED', $contractBlock);
    }

    public function test_manual_validation_commands_have_expected_output_and_pass_fail_criteria(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([
            'Manual validation command block',
            'Expected output:',
            'Pass/fail criteria:',
            'PHPUnit commands return `OK (... tests, ... assertions)`',
            'Artisan list shows registered market-data commands',
            'Help commands show usage/options and no fatal error',
            'Evidence/replay/flow commands create artifacts when run with valid local fixture IDs/paths',
            'FAIL when any DONE/LOCKED claim exists without runtime proof',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must remain documented for manual validation.');
        }
    }

    public function test_replay_runtime_persistence_fix_is_documented_and_guarded(): void
    {
        $schema = $this->readProjectFile('docs/market_data/db/Database_Schema_MariaDB.sql');
        $backtestSchema = $this->readProjectFile('docs/market_data/backtest/Replay_Results_Schema_MariaDB.sql');
        $migration = $this->readProjectFile('database/migrations/2026_05_08_000001_expand_replay_mismatch_summary_to_longtext.php');
        $service = $this->readProjectFile('app/Application/MarketData/Services/ReplayVerificationService.php');
        $command = $this->readProjectFile('app/Console/Commands/MarketData/VerifyReplayCommand.php');
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([$schema, $backtestSchema] as $document) {
            $this->assertStringContainsString('mismatch_summary LONGTEXT NULL', $document);
        }

        $this->assertStringContainsString('ALTER TABLE md_replay_daily_metrics MODIFY mismatch_summary LONGTEXT NULL', $migration);
        $this->assertStringContainsString('buildOperatorMismatchSummary', $service);
        $this->assertStringContainsString('details=mismatches_json', $service);
        $this->assertStringContainsString('reasonCodeFromException', $command);
        $this->assertStringContainsString('REPLAY_FIXTURE_SCHEMA_MISMATCH', $inventory.$status.$tracker);
        $this->assertStringContainsString('REPLAY_EXPECTED_PROOF_INCOMPLETE', $inventory.$status.$tracker);
        $this->assertStringContainsString('SQLSTATE[22001]', $inventory.$status.$tracker);
        $this->assertStringContainsString('mismatch_summary LONGTEXT', $inventory.$status.$tracker);
    }

    public function test_replay_fixture_generation_is_documented_and_guarded(): void
    {
        $kernel = $this->readProjectFile('app/Console/Kernel.php');
        $command = $this->readProjectFile('app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php');
        $service = $this->readProjectFile('app/Application/MarketData/Services/ReplayVerificationService.php');
        $smokeCommand = $this->readProjectFile('app/Console/Commands/MarketData/ReplaySmokeSuiteCommand.php');
        $smokeService = $this->readProjectFile('app/Application/MarketData/Services/ReplaySmokeSuiteService.php');
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        $this->assertStringContainsString('GenerateReplayFixtureCommand', $kernel);
        $this->assertStringContainsString('market-data:replay:fixture:generate', $command.$inventory.$status.$tracker);
        $this->assertStringContainsString('generateFixtureFromRun', $service.$command.$smokeService);
        $this->assertStringContainsString('buildExpectedReplayResultFromActual', $service);
        $this->assertStringContainsString('expected/expected_replay_result.json', $service.$inventory);
        $this->assertStringContainsString('expected/expected_reason_code_counts.json', $service.$inventory);
        $this->assertStringContainsString('generate_runtime_valid_case', $smokeCommand.$inventory);
        $this->assertStringContainsString('executeWithGeneratedValidCase', $smokeService.$smokeCommand);
        foreach ([
            'generated runtime fixture returns MATCH',
            'fixture_generated=1',
            'comparison_result=MATCH',
            'mismatch_count=0',
            'all_passed=1',
            'runtime_valid_fixture_generated=1',
            'replay_id=5',
            'selector=replay',
            'selector_id=5',
            'status=SUCCESS',
            'file_count=5',
            'replay_evidence_pack.json',
            'replay_reason_code_counts.json',
            '20 registered market-data commands',
            'market-data:replay:fixture:generate',
            '--generate_runtime_valid_case',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker, $needle.' must be recorded as generated replay runtime proof.');
        }
    }


    public function test_failure_and_correction_runtime_proof_is_recorded(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([
            'run_id=2',
            'terminal_status=HELD',
            'publishability_state=NOT_READABLE',
            'coverage_gate_state=FAIL',
            'coverage_reason_code=COVERAGE_BELOW_THRESHOLD',
            'final_reason_code=RUN_PARTIAL_DATA',
            'pointer_switched=false',
            'evidence_completeness_state=INCOMPLETE',
            'evidence_warning=EVIDENCE_INCOMPLETE',
            'correction_id=1',
            'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE',
            'status=APPROVED',
            'run_id=3',
            'request_mode=correction',
            'correction_status=PUBLISHED',
            'correction_outcome=PUBLISHED',
            'correction_reseal_status=RESEALED',
            'baseline_publication_id=1',
            'candidate_publication_id=3',
            'candidate_publication_switch=true',
            'changed_decision=CHANGED',
            'publication_switch=1',
            'correction_evidence.json',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker, $needle.' must be recorded as failure/correction runtime proof.');
        }
    }

    private function implementationBlock(string $status, string $name): string
    {
        $pattern = '/^- '.preg_quote($name, '/').' -> [A-Z_]+\R.*?(?=^- .+ -> [A-Z_]+\R|\z)/ms';
        $this->assertMatchesRegularExpression($pattern, $status);
        preg_match($pattern, $status, $match);

        return $match[0];
    }

    private function contractBlock(string $tracker, string $name): string
    {
        $pattern = '/^- '.preg_quote($name, '/').' (?:->|→) [A-Z_]+\R.*?(?=^- [A-Z0-9_]+_CONTRACT (?:->|→) [A-Z_]+\R|\z)/msu';
        $this->assertMatchesRegularExpression($pattern, $tracker);
        preg_match($pattern, $tracker, $match);

        return $match[0];
    }
}
