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
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'OPERATIONAL_READINESS_CONTRACT',
            'Daily operational flow',
            'Manual file import-only flow',
            'Manual file promote flow',
            'Provider/API flow',
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

    public function test_runbook_documents_all_registered_market_data_commands(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');
        $kernel = $this->readProjectFile('app/Console/Kernel.php');

        foreach ($this->expectedCommands() as $command) {
            $this->assertStringContainsString($command['class'], $kernel, $command['class'].' must stay registered.');
            $this->assertStringContainsString('`'.$command['signature'].'`', $runbook, $command['signature'].' must be documented in operational runbook.');
        }
    }

    public function test_runbook_documents_terminal_states_and_next_actions(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

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
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'market-data:evidence:export --run_id',
            'market-data:evidence:export --correction_id',
            'market-data:evidence:export --replay_id',
            'market-data:evidence:export --trade_date',
            'run id and requested/effective trade date present',
            'publication id/version/current flag present',
            'pointer/current-publication context present',
            'coverage state/counts/ratio/threshold present',
            'source mode/name/input/file hash/attempt telemetry present',
            'Replay is the proof mechanism',
            'market-data:replay:verify',
            'market-data:replay:smoke',
            'market-data:replay:backfill',
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
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

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
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

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
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

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
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

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

    public function test_audit_docs_reference_operational_readiness_contract(): void
    {
        $status = $this->readProjectFile('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->readProjectFile('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->readProjectFile('docs/market_data/audit/OPERATIONAL_READINESS_INVENTORY.md');

        foreach ([$status, $tracker, $inventory] as $document) {
            $this->assertStringContainsString('Operational Readiness', $document);
            $this->assertStringContainsString('OPERATIONAL_READINESS_CONTRACT', $document);
            $this->assertStringContainsString('OperationalReadinessStaticGuardTest.php', $document);
            $this->assertStringContainsString('LOCKED_LOCAL_PHPUNIT_PASS', $document);
        }

        $this->assertStringContainsString('- Operational Readiness -> DONE', $status);
        $this->assertStringContainsString('- OPERATIONAL_READINESS_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('OK (10 tests, 196 assertions)', $status.$tracker.$inventory);
        $this->assertStringContainsString('OK (368 tests, 4927 assertions)', $status.$tracker.$inventory);
        $this->assertStringContainsString('php artisan list | findstr market-data', $status.$tracker.$inventory);
    }

    public function test_command_surface_and_runbook_stay_synchronized(): void
    {
        $runbook = $this->readProjectFile('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');
        $inventory = $this->readProjectFile('docs/market_data/ops/COMMAND_SURFACE_SAFETY_INVENTORY.md');
        $commandIndex = $this->readProjectFile('docs/market_data/ops/commands/README.md');

        foreach ($this->expectedCommands() as $command) {
            $signature = '`'.$command['signature'].'`';
            $this->assertStringContainsString($signature, $runbook);
            $this->assertStringContainsString($signature, $inventory);
        }

        $this->assertStringContainsString('OPERATIONAL_RUNBOOK.md', $commandIndex);
        $this->assertStringContainsString('operator source of truth', $commandIndex);
    }

    private function expectedCommands(): array
    {
        return [
            ['class' => 'IngestEodBarsCommand', 'signature' => 'market-data:eod-bars:ingest'],
            ['class' => 'ComputeIndicatorsCommand', 'signature' => 'market-data:eod-indicators:compute'],
            ['class' => 'BuildEligibilityCommand', 'signature' => 'market-data:eod-eligibility:build'],
            ['class' => 'AuditHashCommand', 'signature' => 'market-data:audit:hash'],
            ['class' => 'SealDatasetCommand', 'signature' => 'market-data:dataset:seal'],
            ['class' => 'FinalizeRunCommand', 'signature' => 'market-data:run:finalize'],
            ['class' => 'DailyPipelineCommand', 'signature' => 'market-data:daily'],
            ['class' => 'BackfillMarketDataCommand', 'signature' => 'market-data:backfill'],
            ['class' => 'PromoteMarketDataCommand', 'signature' => 'market-data:promote'],
            ['class' => 'ExportEvidenceCommand', 'signature' => 'market-data:evidence:export'],
            ['class' => 'VerifyReplayCommand', 'signature' => 'market-data:replay:verify'],
            ['class' => 'ReplaySmokeSuiteCommand', 'signature' => 'market-data:replay:smoke'],
            ['class' => 'ReplayBackfillCommand', 'signature' => 'market-data:replay:backfill'],
            ['class' => 'CaptureSessionSnapshotCommand', 'signature' => 'market-data:session-snapshot'],
            ['class' => 'PurgeSessionSnapshotCommand', 'signature' => 'market-data:session-snapshot:purge'],
            ['class' => 'RequestCorrectionCommand', 'signature' => 'market-data:correction:request'],
            ['class' => 'ApproveCorrectionCommand', 'signature' => 'market-data:correction:approve'],
            ['class' => 'RunCorrectionCommand', 'signature' => 'market-data:correction:run'],
            ['class' => 'RepairCurrentPublicationIntegrityCommand', 'signature' => 'market-data:current-publication:repair'],
        ];
    }
}
