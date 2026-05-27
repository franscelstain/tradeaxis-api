<?php

use PHPUnit\Framework\TestCase;

class OutOfOrderImportImpactStaticGuardTest extends TestCase
{
    private function read(string $relativePath): string
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function test_eod_bar_replacement_returns_mutation_summary_before_artifact_delete(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $replaceBars = $this->extractMethod($repository, 'replaceBars');

        $this->assertStringContainsString('$mutationSummary = $this->buildBarsMutationSummary', $replaceBars);
        $this->assertLessThan(
            strpos($replaceBars, "->delete();"),
            strpos($replaceBars, '$mutationSummary = $this->buildBarsMutationSummary'),
            'Mutation summary must be built before replacement deletes the previous canonical rows.'
        );
        $this->assertStringContainsString('return $mutationSummary;', $replaceBars);
        $this->assertStringContainsString('inserted_bar_count', $repository);
        $this->assertStringContainsString('updated_bar_count', $repository);
        $this->assertStringContainsString('unchanged_bar_count', $repository);
        $this->assertStringContainsString('removed_bar_count', $repository);
    }

    public function test_ingest_and_command_surfaces_expose_mutation_and_impact_summaries(): void
    {
        $ingest = $this->read('app/Application/MarketData/Services/EodBarsIngestService.php');
        $pipeline = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $dailyCommandBase = $this->read('app/Console/Commands/MarketData/AbstractMarketDataCommand.php');
        $lifecycleCommand = $this->read('app/Console/Commands/MarketData/BackfillLifecycleCommand.php');

        foreach ([
            'bar_mutation_summary',
            'indicator_impact_summary',
            'publication_impact_summary',
        ] as $needle) {
            $this->assertStringContainsString($needle, $ingest);
            $this->assertStringContainsString($needle, $dailyCommandBase);
        }

        $this->assertStringContainsString('EodBarsMutationImpactResolver $impactResolver = null', $ingest);
        $this->assertStringContainsString('mutationImpactNoteSegments', $pipeline);
        $this->assertStringContainsString('MarketDataImpactReprocessExecutor $impactReprocess = null', $pipeline);
        $this->assertStringContainsString('withImpactReprocessExecution', $pipeline);
        $this->assertStringContainsString('bar_mutation_changed_count', $lifecycleCommand);
        $this->assertStringContainsString('indicator_reprocess_state', $lifecycleCommand);
        $this->assertStringContainsString('indicator_reprocess_execution_state', $lifecycleCommand);
        $this->assertStringContainsString('eligibility_reprocess_execution_state', $lifecycleCommand);
        $this->assertStringContainsString('publication_impact_state', $lifecycleCommand);
        $this->assertStringContainsString('publication_reprocess_state', $lifecycleCommand);
    }

    public function test_impact_resolver_uses_trading_calendar_and_ma50_horizon(): void
    {
        $resolver = $this->read('app/Application/MarketData/Services/EodBarsMutationImpactResolver.php');

        $this->assertStringContainsString('tradingDatesBetween', $resolver);
        $this->assertStringContainsString('loadAvailableBarTradeDatesOnOrAfter', $resolver);
        $this->assertStringContainsString('maxDependencyTradingDays', $resolver);
        $this->assertStringContainsString('50', $this->extractMethod($resolver, 'maxDependencyTradingDays'));
        $this->assertStringContainsString('REQUIRES_REPUBLICATION', $resolver);
        $this->assertStringContainsString('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $resolver);
    }

    public function test_recovered_checkpoint_apply_uses_partial_upsert_not_full_date_replace(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $ingest = $this->read('app/Application/MarketData/Services/EodBarsIngestService.php');
        $pipeline = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $orchestrator = $this->read('app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');

        $partialUpsert = $this->extractMethod($repository, 'upsertBarsPartial');

        $this->assertStringContainsString('updateOrInsert', $partialUpsert);
        $this->assertStringNotContainsString('DB::table($table)->where', str_replace(["\r", "\n", ' '], '', $partialUpsert));
        $this->assertStringContainsString('buildBarsMutationSummary($tradeDate, $publicationId, $validRows, $useHistory, false)', $partialUpsert);
        $this->assertStringContainsString('ingestRecoveredRowsPartial', $ingest);
        $this->assertStringContainsString('upsertBarsPartial', $ingest);
        $this->assertStringContainsString('applyRecoveredRowsPartial', $pipeline);
        $this->assertStringContainsString('applyOnlyFailedRecoveredRows', $orchestrator);
        $this->assertStringNotContainsString('importDailyFromAcquiredRows($tradeDate, $sourceMode, $rows', $orchestrator);
    }

    public function test_impact_execution_service_recomputes_indicators_and_eligibility_before_reporting_executed(): void
    {
        $executor = $this->read('app/Application/MarketData/Services/MarketDataImpactReprocessExecutor.php');

        $this->assertStringContainsString('$this->indicators->compute', $executor);
        $this->assertStringContainsString('$this->eligibility->build', $executor);
        $this->assertStringContainsString('IMPACT_REPROCESS_EXECUTED', $executor);
        $this->assertStringContainsString('PENDING_PROMOTE', $executor);
        $this->assertStringContainsString('BLOCKED_REQUIRES_CORRECTION', $executor);
        $this->assertStringContainsString('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $executor);
        $this->assertStringContainsString("\$reprocessedDates === [] ? 'NONE' : 'FULL_DATE'", $executor);
    }

    public function test_lifecycle_executes_hash_seal_finalize_for_non_readable_affected_dates_via_promote(): void
    {
        $orchestrator = $this->read('app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');
        $pipeline = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');

        $publicationReprocess = $this->extractMethod($orchestrator, 'executePublicationReprocessForCase');
        $promoteSingleDay = $this->extractMethod($pipeline, 'promoteSingleDay');

        $this->assertStringContainsString('publicationReprocessCandidateDates', $orchestrator);
        $this->assertStringContainsString('$this->pipeline->promoteDaily', $publicationReprocess);
        $this->assertStringContainsString('executeImpactPublicationReprocessIfNeeded', $pipeline);
        $this->assertStringContainsString('$this->promoteDaily', $this->extractMethod($pipeline, 'executeImpactPublicationReprocessIfNeeded'));
        $this->assertStringContainsString('PENDING_PROMOTE', $publicationReprocess);
        $this->assertStringContainsString('BLOCKED_REQUIRES_CORRECTION', $publicationReprocess);
        $this->assertStringContainsString('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $publicationReprocess);
        $this->assertStringContainsString('completeHash', $promoteSingleDay);
        $this->assertStringContainsString('completeSeal', $promoteSingleDay);
        $this->assertStringContainsString('completeFinalize', $promoteSingleDay);
    }

    private function extractMethod(string $source, string $methodName): string
    {
        $pattern = '/function '.preg_quote($methodName, '/').'\([^)]*\)\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
