<?php

use PHPUnit\Framework\TestCase;

class OperationalReadinessStaticGuardTest extends TestCase
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

    public function test_operational_runbook_exists_and_covers_required_flows(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'OPERATIONAL_READINESS_CONTRACT',
            'Daily operational flow',
            'Manual file import-only flow',
            'Manual file promote flow',
            'Provider/API flow',
            'market-data:backfill:lifecycle',
            'market-data:backfill:missing-tickers',
            'Coverage, hash, seal, finalize, pointer sequence',
            'Evidence export flow',
            'Replay verification flow',
            'Correction lifecycle flow',
            'Backfill flow',
            'Session snapshot flow',
            'Manual DB action policy',
            'Forbidden shortcuts',
            'Operator checklist before publish',
            'Operator checklist after publish',
            'Troubleshooting quick map',
            'Manual validation commands',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    /**
     * Every registered command must appear in the runbook.
     *
     * This walked a hand-written roster of twenty-eight while the kernel registered thirty-three,
     * so four commands were absent from the operator's primary document and the test passed
     * throughout — a command missing from a roster is one the roster cannot report. Among them
     * was market-data:repair-price-scale-stretches, which rewrites sealed price history.
     *
     * The same shape of gap existed in CommandSurfaceSafetyStaticGuardTest and was closed there
     * the same way. Two hand-written rosters of the same thing is how they drift apart.
     */
    public function test_runbook_documents_all_registered_market_data_commands(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        $undocumented = [];

        foreach ($this->registeredMarketDataCommandNames() as $name) {
            if (strpos($runbook, '`'.$name.'`') === false) {
                $undocumented[] = $name;
            }
        }

        $this->assertSame([], $undocumented, 'Registered commands missing from the operational runbook.');
    }

    /**
     * @return string[]
     */
    private function registeredMarketDataCommandNames(): array
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

        $this->assertGreaterThan(25, count($names), 'Kernel command reflection returned suspiciously few commands.');

        return $names;
    }

    public function test_runbook_documents_terminal_states_and_next_actions(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'SUCCESS / READABLE',
            'SUCCESS / NOT_READABLE',
            'HELD / NOT_READABLE',
            'FAILED / NOT_READABLE',
            'status=BLOCKED',
            'terminal_status',
            'publishability_state',
            'reason_code',
            'final_reason_code',
            'next action',
            'Stop',
            'preserve pointer',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    public function test_runbook_documents_evidence_export_and_replay_verification(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'market-data:evidence:export --run_id',
            'market-data:evidence:export --correction_id',
            'market-data:evidence:export --replay_id',
            'market-data:evidence:export --trade_date',
            'market-data:evidence-replay:full-range-current',
            'market-data:sector-indexes:ingest-api',
            'market-data:sector-indexes:import-bars',
            'market-data:sectors:import-memberships',
            'market-data:events:import-corporate-actions',
            'market-data:events:import-trading-status',
            'run id and requested/effective trade date present',
            'publication id/version/current flag present',
            'pointer/current-publication context present',
            'coverage state/counts/ratio/threshold present',
            'source mode/name/input/file hash/attempt telemetry present',
            'Replay is the proof mechanism',
            'market-data:replay:verify',
            'market-data:replay:smoke',
            'market-data:replay:backfill',
            'market-data:replay:fixture:generate',
            'market-data:evidence-replay:full-range-current',
            'market-data:sector-indexes:ingest-api',
            'market-data:sector-indexes:import-bars',
            'market-data:sectors:import-memberships',
            'market-data:events:import-corporate-actions',
            'market-data:events:import-trading-status',
            'reason code mismatch case',
            'broken manifest case',
            'missing file case',
            'coverage mismatch',
            'source context mismatch',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    public function test_runbook_documents_manual_file_import_vs_promote(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'manual_file',
            'request_mode=import_only',
            'promote_status=NOT_PROMOTED',
            'promoted=false',
            'pointer_switched=false',
            'Manual file import must not become current/readable automatically',
            'market-data:promote --requested_date',
            'request_mode=promote',
            'coverage_gate_state=PASS',
            'seal_state=SEALED',
            'pointer_switched=true',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    public function test_runbook_documents_correction_lifecycle(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'market-data:correction:request',
            'market-data:correction:approve',
            'market-data:correction:run',
            'REQUESTED',
            'APPROVED',
            'PUBLISHED',
            'CANCELLED',
            'RESEALED',
            'baseline is not current/readable/SEALED/SUCCESS',
            'previous current must stay preserved',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    public function test_runbook_forbids_raw_staging_latest_and_max_date_shortcuts(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'raw/staging/latest/MAX(date)',
            'MAX(trade_date)',
            "latest('trade_date')",
            'direct pointer switch',
            'direct `READABLE` update',
            'coverage bypass',
            'seal bypass',
            'finalize bypass',
            'replay bypass',
            'empty output treated as success',
            'silent provider failure',
            'unbounded provider retry',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    public function test_runbook_documents_manual_db_action_policy(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'Manual DB action is exceptional',
            'backup/rollback plan exists',
            'SQL file is reviewed',
            'reason code and operator name are recorded',
            'evidence exported after action',
            'market-data:current-publication:repair',
            'direct pointer switch to make data readable',
            'direct publication current flag edits as publish flow',
            'manual correction status edit',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }


}
