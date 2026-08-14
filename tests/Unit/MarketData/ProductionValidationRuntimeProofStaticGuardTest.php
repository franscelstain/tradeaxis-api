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

        // Historical PHPUnit tallies are no longer asserted. They recorded one past run
        // ("378 tests, 5072 assertions") against a suite that now holds well over 700, so
        // they could only ever be archaeology or churn, never a guard.
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

    /**
     * Every artisan command the inventory names must actually be registered.
     *
     * The previous version listed thirty command strings and asserted each appeared in the
     * markdown. That proves the document mentions a name; it cannot notice when a command is
     * renamed or removed, which is the failure that matters. Reading the names out of the
     * document and resolving them against the kernel catches exactly that, and needs no edit
     * here when a command is added.
     */
    public function test_every_artisan_command_named_in_the_inventory_is_registered(): void
    {
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        preg_match_all('/php artisan (market-data:[a-z0-9:\-]+)/', $inventory, $matches);

        $documented = array_values(array_unique($matches[1]));
        sort($documented);

        $this->assertNotEmpty($documented, 'Inventory must name at least one artisan command.');

        $registered = $this->registeredMarketDataCommands();

        $this->assertNotEmpty($registered, 'Kernel must register market-data commands.');

        $missing = array_values(array_diff($documented, $registered));

        $this->assertSame([], $missing, 'Commands documented in the validation inventory but not registered in the kernel.');
    }

    /**
     * Command names resolved from the kernel's registered command classes.
     *
     * @return array<int, string>
     */
    private function registeredMarketDataCommands(): array
    {
        $kernel = new ReflectionClass(\App\Console\Kernel::class);
        $property = $kernel->getProperty('commands');
        $property->setAccessible(true);

        $names = [];

        foreach ($property->getValue($kernel->newInstanceWithoutConstructor()) as $commandClass) {
            if (! class_exists($commandClass)) {
                continue;
            }

            $signatureProperty = (new ReflectionClass($commandClass))->getProperty('signature');
            $signatureProperty->setAccessible(true);

            $signature = (string) $signatureProperty->getValue(
                (new ReflectionClass($commandClass))->newInstanceWithoutConstructor()
            );

            $name = trim(strtok($signature, " \n\t{"));

            if (strpos($name, 'market-data:') === 0) {
                $names[] = $name;
            }
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
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

        // The command-count assertion is gone. It pinned "30-command command list/full help"
        // while the kernel had grown to thirty-three, so the test was holding a stale figure in
        // place rather than catching the drift. Command surface completeness is now derived from
        // the kernel by CommandSurfaceSafetyStaticGuardTest.
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

        // The distinction being protected: a DONE/LOCKED claim must rest on validation that
        // actually ran somewhere, and must say where. The command count that used to be asserted
        // here is not part of that — it was a figure from one past run, and it had gone stale.
        foreach ([$implementationBlock, $contractBlock] as $block) {
            $this->assertStringContainsString('vendor/` is absent', $block);
            $this->assertStringContainsString('not run in container', $block);
            $this->assertStringContainsString('Operator-local', $block);
            $this->assertStringContainsString('full MarketData', $block);
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
            'market-data:replay:fixture:generate',
            'market-data:provider:smoke',
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



    public function test_runtime_parity_provider_statuses_are_reason_coded_and_no_false_pass_is_allowed(): void
    {
        $artifact = $this->readProjectFile('storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt');
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $proofPack = $this->readProjectFile('docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md');
        $inventory = $this->readProjectFile('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        $combined = $status.$tracker.$proofPack.$inventory;

        foreach ([
            'OPS_RUNTIME_PARITY_PASSED',
            'OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_REQUEST_CONTEXT_BLOCKED',
            'OPS_RUNTIME_PARITY_PASSED',
        ] as $statusCode) {
            $this->assertStringContainsString($statusCode, $combined, $statusCode.' must be a documented runtime parity status.');
        }

        $claimsPassed = preg_match('/(\[SESSION_STATUS\] OPS_RUNTIME_PARITY_PASSED|Ops rollout\/runtime parity: `OPS_RUNTIME_PARITY_PASSED`|Current rollout status is `OPS_RUNTIME_PARITY_PASSED`)/', $combined) === 1;
        if ($claimsPassed) {
            $this->assertStringContainsString('provider_smoke_status=PASS', $artifact);
            $this->assertStringContainsString('reason_code=PROVIDER_SMOKE_OK', $artifact);
            $this->assertStringContainsString('http_status=200', $artifact);
            return;
        }

        $this->assertStringNotContainsString('[FINAL_DECISION] OPS_RUNTIME_PARITY_PASSED', $combined);
    }
    
    
    public function test_runtime_parity_command_output_artifacts_are_utf8_without_null_bytes(): void
    {
        $reportPath = 'storage/app/market-data/production-rollout-validation-runtime-parity/command-output/encoding-normalization-report.txt';
        $report = $this->readProjectFile($reportPath);
        $this->assertStringContainsString('ENCODING: UTF-8', $report);
        $this->assertStringContainsString('NORMALIZED_FILE_COUNT: 20', $report);

        $globalReport = $this->readProjectFile('storage/app/market-data/evidence-encoding-normalization-report.txt');
        $this->assertStringContainsString('ENCODING: UTF-8', $globalReport);
        $this->assertStringContainsString('SCOPE: storage/app/market-data/**/*.txt', $globalReport);

        $directory = $this->projectPath('storage/app/market-data/production-rollout-validation-runtime-parity/command-output');
        $this->assertDirectoryExists($directory);

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'txt') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            $this->assertStringNotContainsString("\0", $contents, $file->getPathname().' must be normalized to UTF-8/plain text without null-byte evidence noise.');
        }

        $globalDirectory = $this->projectPath('storage/app/market-data');
        $globalIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($globalDirectory));
        foreach ($globalIterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'txt') {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            $this->assertStringNotContainsString("\0", $contents, $file->getPathname().' must be normalized to UTF-8/plain text without null-byte evidence noise.');
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
