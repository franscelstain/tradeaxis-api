<?php

class StageEightReconstructionStaticGuardTest extends TestCase
{
    public function test_replacement_hash_columns_preserve_the_pipeline_contract_order(): void
    {
        $reflection = new ReflectionClass(
            App\Application\MarketData\Services\StageEightCorpusReconstructionService::class
        );
        $service = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('artifactSnapshotColumns');
        $method->setAccessible(true);
        $contract = App\Application\MarketData\Services\MarketDataPipelineService::BARS_HASH_COLUMNS;
        $available = array_values(array_unique(array_merge(
            array_reverse($contract),
            ['run_id', 'publication_id', 'created_at']
        )));

        $replacement = $method->invoke($service, 'bars', $available, false);
        $baseline = $method->invoke($service, 'bars', $available, true);
        $expectedBaseline = array_values(array_unique(array_merge(
            $contract,
            ['run_id', 'publication_id', 'created_at']
        )));
        sort($expectedBaseline, SORT_STRING);

        $this->assertSame($contract, $replacement);
        $this->assertSame($expectedBaseline, $baseline);
        $this->assertNotSame($replacement, $baseline);
    }

    public function test_command_is_dry_run_by_default_and_never_invokes_replay(): void
    {
        $command = $this->source('app/Console/Commands/MarketData/ReconstructCurrentCorpusCommand.php');
        $service = $this->source('app/Application/MarketData/Services/StageEightCorpusReconstructionService.php');

        $this->assertStringContainsString('{--dry-run}', $command);
        $this->assertStringContainsString('{--apply}', $command);
        $this->assertStringContainsString("? \$service->execute", $command);
        $this->assertStringContainsString(': $service->plan()', $command);
        $this->assertStringNotContainsString('ReplayVerificationService', $command.$service);
        $this->assertStringNotContainsString('Fixture', $command.$service);
        $this->assertStringContainsString("'stage_9_replay' => 'NOT_EXECUTED'", $service);
    }

    public function test_acquisition_is_bounded_batched_resumable_and_date_scoped(): void
    {
        $service = $this->source('app/Application/MarketData/Services/StageEightCorpusReconstructionService.php');

        $this->assertStringContainsString('const ACQUISITION_BATCH_SIZE = 20', $service);
        $this->assertStringContainsString('array_chunk($tickerCodes, self::ACQUISITION_BATCH_SIZE)', $service);
        $this->assertStringContainsString(".'/'.$".'date' . ".'.jsonl'", $service);
        $this->assertStringContainsString("unset(\$acquired)", $service);
        $this->assertStringContainsString("'completed_batches'", $service);
        $this->assertStringContainsString('last complete line wins deterministically', $service);
        $this->assertStringContainsString('cleanupAcquisitionRows($outputDir)', $service);
    }

    public function test_completion_oracle_covers_new_artifacts_and_immutable_baseline_content(): void
    {
        $service = $this->source('app/Application/MarketData/Services/StageEightCorpusReconstructionService.php');

        foreach ([
            'baseline_history_snapshot_violation_count',
            'replacement_artifact_hash_violation_count',
            'factor_decision_violation_count',
            'factor_application_violation_count',
            'missing_applied_factor_violation_count',
            'market_structure_cardinality_violation_count',
            'publication_lineage_violation_count',
        ] as $field) {
            $this->assertStringContainsString($field, $service);
        }
        $this->assertStringContainsString("if ((\$oracle['violation_count'] ?? 1) !== 0)", $service);
        $this->assertStringContainsString("'state' => 'COMPLETE'", $service);
        $this->assertStringContainsString('artifactSnapshotColumns', $service);
    }

    public function test_failed_target_is_terminalized_and_corpus_mode_does_not_fan_out_impact_reprocess(): void
    {
        $service = $this->source('app/Application/MarketData/Services/StageEightCorpusReconstructionService.php');
        $pipeline = $this->source('app/Application/MarketData/Services/MarketDataPipelineService.php');

        $this->assertStringContainsString('failTargetCorrection($target, $e)', $service);
        $this->assertStringContainsString("['FAILED', 'REJECTED', 'CANCELLED']", $service);
        $this->assertStringContainsString('Stage 8 retry after an immutable failed lifecycle attempt', $service);
        $this->assertStringContainsString("'status' => 'BLOCKED'", $service);
        $this->assertStringContainsString("'complete_target_count'", $service);
        $this->assertStringContainsString("if (\$input->requestMode !== 'corpus_reconstruction')", $pipeline);
    }


    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 3).'/'.$path);
    }
}
