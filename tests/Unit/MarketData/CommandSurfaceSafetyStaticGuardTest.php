<?php

class CommandSurfaceSafetyStaticGuardTest extends TestCase
{
    private $commandDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandDir = base_path('app/Console/Commands/MarketData');
    }

    public function test_all_registered_market_data_commands_are_in_ops_safety_inventory(): void
    {
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $inventory = file_get_contents(base_path('docs/market_data/ops/COMMAND_SURFACE_SAFETY_INVENTORY.md'));

        foreach ($this->expectedCommands() as $command) {
            $this->assertStringContainsString($command['class'], $kernel, $command['class'].' must be registered in Console Kernel.');
            $this->assertStringContainsString('`'.$command['signature'].'`', $inventory, $command['signature'].' must be listed in command safety inventory.');
        }
    }

    public function test_DryRun_destructive_session_snapshot_purge_is_default_and_Apply_required_for_delete(): void
    {
        $command = file_get_contents($this->commandDir.'/PurgeSessionSnapshotCommand.php');
        $service = file_get_contents(base_path('app/Application/MarketData/Services/SessionSnapshotService.php'));
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/SessionSnapshotRepository.php'));

        $this->assertStringContainsString('{--dry-run', $command);
        $this->assertStringContainsString('{--apply', $command);
        $this->assertStringContainsString('$apply = (bool) $this->option(\'apply\')', $command);
        $this->assertStringContainsString('reason_code=', $command);
        $this->assertStringContainsString('function purge($beforeDate = null, $outputDir = null, $apply = false)', $service);
        $this->assertStringContainsString('COMMAND_DRY_RUN_ONLY', $service);
        $this->assertStringContainsString('COMMAND_APPLY_CONFIRMED', $service);
        $this->assertStringContainsString('countBefore', $service);
        $this->assertStringContainsString('$apply ? (int) $this->snapshots->purgeBefore', $service);
        $this->assertStringContainsString('function countBefore', $repository);
    }

    public function test_operator_validation_failures_render_registered_reason_codes(): void
    {
        $abstract = file_get_contents($this->commandDir.'/AbstractMarketDataCommand.php');
        $registry = file_get_contents(base_path('docs/market_data/registry/Reason_Codes_Registry.md'));
        $seed = file_get_contents(base_path('docs/market_data/registry/Reason_Codes_Seed.sql'));

        foreach ([
            'COMMAND_MISSING_REQUIRED_INPUT',
            'COMMAND_INVALID_DATE_FORMAT',
            'COMMAND_INVALID_SOURCE_MODE',
            'COMMAND_INVALID_PROMOTE_MODE',
            'COMMAND_CONFLICTING_OPTIONS',
            'COMMAND_DESTRUCTIVE_GUARD_REQUIRED',
            'COMMAND_DRY_RUN_ONLY',
            'COMMAND_APPLY_CONFIRMED',
            'COMMAND_EXECUTION_FAILED',
            'COMMAND_CORRECTION_NOT_FOUND',
            'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE',
        ] as $reasonCode) {
            $this->assertStringContainsString($reasonCode, $registry, $reasonCode.' must exist in registry.');
            $this->assertStringContainsString($reasonCode, $seed, $reasonCode.' must exist in seed SQL.');
        }

        $this->assertStringContainsString('renderCommandBlocked', $abstract);
        $this->assertStringContainsString('COMMAND_INVALID_DATE_FORMAT', $abstract);
        $this->assertStringContainsString('COMMAND_INVALID_SOURCE_MODE', $abstract);
    }

    public function test_promote_force_replace_requires_auditable_reason_and_does_not_become_default_apply_shortcut(): void
    {
        $command = file_get_contents($this->commandDir.'/PromoteMarketDataCommand.php');

        $this->assertStringContainsString('{--force_replace=false}', $command);
        $this->assertStringContainsString('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', $command);
        $this->assertStringContainsString('force_replace_reason', $command);
        $this->assertStringNotContainsString('{--force_replace=true}', $command);
    }

    public function test_repair_current_publication_uses_apply_guard_and_dry_run_reason_code(): void
    {
        $command = file_get_contents($this->commandDir.'/RepairCurrentPublicationIntegrityCommand.php');

        $this->assertStringContainsString('{--apply}', $command);
        $this->assertStringContainsString('{--reason=}', $command);
        $this->assertStringContainsString('{--force_reason=}', $command);
        $this->assertStringContainsString('COMMAND_DESTRUCTIVE_GUARD_REQUIRED', $command);
        $this->assertStringContainsString('operation_mode=', $command);
        $this->assertStringContainsString('COMMAND_DRY_RUN_ONLY', $command);
        $this->assertStringContainsString('repair_reason=', $command);
        $this->assertStringContainsString('pointer_before_publication_id=', $command);
        $this->assertStringContainsString('pointer_after_state=CLEARED', $command);
        $this->assertStringContainsString('next_action=Re-run with --apply --reason=', $command);
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
            ['class' => 'GenerateReplayFixtureCommand', 'signature' => 'market-data:replay:fixture:generate'],
            ['class' => 'CaptureSessionSnapshotCommand', 'signature' => 'market-data:session-snapshot'],
            ['class' => 'PurgeSessionSnapshotCommand', 'signature' => 'market-data:session-snapshot:purge'],
            ['class' => 'RequestCorrectionCommand', 'signature' => 'market-data:correction:request'],
            ['class' => 'ApproveCorrectionCommand', 'signature' => 'market-data:correction:approve'],
            ['class' => 'RunCorrectionCommand', 'signature' => 'market-data:correction:run'],
            ['class' => 'RepairCurrentPublicationIntegrityCommand', 'signature' => 'market-data:current-publication:repair'],
            ['class' => 'ProviderSmokeCommand', 'signature' => 'market-data:provider:smoke'],
        ];
    }
}
