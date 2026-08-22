<?php

class CommandSurfaceSafetyStaticGuardTest extends TestCase
{
    private $commandDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandDir = base_path('app/Console/Commands/MarketData');
    }


    /**
     * Guards the guard: a reflection failure returning nothing would make the check above pass
     * against an empty command set.
     */
    public function test_the_kernel_registers_a_meaningful_command_surface(): void
    {
        $this->assertGreaterThan(25, count($this->registeredMarketDataCommandNames()));
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

        return $names;
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
        $registry = file_get_contents(base_path('docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md'));

        // The seed file is no longer checked here. ReasonCodeSeedExecutionTest runs the seed
        // and proves every registry code lands in eod_reason_codes, so repeating a textual
        // presence check per code adds nothing.
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
            'COMMAND_CORRECTION_STATUS_NOT_APPROVABLE',
            'COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE',
        ] as $reasonCode) {
            $this->assertStringContainsString($reasonCode, $registry, $reasonCode.' must exist in registry.');
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



    public function test_recompute_current_indicators_is_current_bars_only_and_does_not_import_source(): void
    {
        $command = file_get_contents($this->commandDir.'/RecomputeCurrentIndicatorsCommand.php');
        $pipeline = file_get_contents(base_path('app/Application/MarketData/Services/MarketDataPipelineService.php'));

        $this->assertStringContainsString('market-data:eod-indicators:recompute-current', $command);
        $this->assertStringContainsString('indicator_recompute_from_existing_current_bars', $command);
        $this->assertStringContainsString('source_acquisition_executed', $command);
        $this->assertStringContainsString('bar_ingest_executed', $command);
        $this->assertStringContainsString('source_master_write_executed', $command);
        $this->assertStringContainsString('eod_bars_write_executed', $command);
        $this->assertStringContainsString('promoteDaily(', $command);
        $this->assertStringContainsString("'analytical_remediation_current'", $command);
        $this->assertStringNotContainsString("'correction_current'", $command);
        $this->assertStringContainsString('exportCorrectionEvidence', $command);
        $this->assertStringContainsString('recomputePreservedCurrentPublication', $command);
        $this->assertStringNotContainsString('completeIngest(', $command);
        $this->assertStringNotContainsString('acquireSourceRows', $command);
        $this->assertStringNotContainsString('ingestAcquiredRows', $command);
        $this->assertStringContainsString('bars_rows_written_source=current_publication_snapshot', $pipeline);
        $this->assertStringContainsString('bar_ingest_executed=false', $pipeline);
    }
}
