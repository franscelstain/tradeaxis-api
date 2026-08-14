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

    /**
     * The comparison must happen before the delete. Ordering inside a single method is not
     * something execution can observe from outside it, so this stays a source check.
     *
     * The counts it used to assert as field names are driven by
     * BarMutationSummaryClassificationTest, which is also where the consequence of getting this
     * ordering wrong is shown: with the previous rows already deleted, everything incoming looks
     * inserted and a ticker dropped from the feed is never reported as removed at all.
     */
    public function test_eod_bar_replacement_compares_before_it_deletes(): void
    {
        $replaceBars = $this->extractMethod(
            $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php'),
            'replaceBars'
        );

        $this->assertLessThan(
            strpos($replaceBars, "->delete();"),
            strpos($replaceBars, '$mutationSummary = $this->buildBarsMutationSummary'),
            'Mutation summary must be built before replacement deletes the previous canonical rows.'
        );
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

    /**
     * Only the correction reason code stays asserted from source, because it names a policy
     * rather than a computation.
     *
     * The horizon itself is proven by BarMutationImpactResolverTest, which changes a bar
     * mid-series and checks the affected range. The old assertion looked for the literal "50"
     * inside maxDependencyTradingDays(), which would pass on a method that returned the right
     * number and then applied it to the wrong dates, or on one that mentioned 50 in a comment.
     */
    public function test_impact_resolver_escalates_affected_publications_to_correction(): void
    {
        $resolver = $this->read('app/Application/MarketData/Services/EodBarsMutationImpactResolver.php');

        $this->assertStringContainsString('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $resolver);
    }

    /**
     * Recovery must upsert, never replace the whole trade date.
     *
     * Only the prohibitions stay. A full-date replace during recovery would delete the bars of
     * every ticker that had already succeeded, so retrying a handful of failed tickers would
     * destroy the rest of the day.
     *
     * That the partial path actually preserves the other tickers is proven by
     * EodArtifactRepositoryPartialUpsertTest, which seeds three tickers, upserts a fourth, and
     * checks all four survive.
     */
    public function test_recovery_never_replaces_a_whole_trade_date(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $orchestrator = $this->read('app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');

        $partialUpsert = $this->extractMethod($repository, 'upsertBarsPartial');

        $this->assertStringContainsString('updateOrInsert', $partialUpsert);
        $this->assertStringNotContainsString('DB::table($table)->where', str_replace(["\r", "\n", ' '], '', $partialUpsert));

        // The partial summary must not compute removals: a ticker absent from a recovery batch
        // was never being retried, and reporting it removed would trigger a reprocess for data
        // that did not move.
        $this->assertStringContainsString('buildBarsMutationSummary($tradeDate, $publicationId, $validRows, $useHistory, false)', $partialUpsert);

        $this->assertStringNotContainsString('importDailyFromAcquiredRows($tradeDate, $sourceMode, $rows', $orchestrator);
    }

    public function test_impact_execution_service_recomputes_indicators_and_eligibility_before_reporting_executed(): void
    {
        $executor = $this->read('app/Application/MarketData/Services/MarketDataImpactReprocessExecutor.php');

        $this->assertStringContainsString('$this->indicators->compute', $executor);
        $this->assertStringContainsString('$this->eligibility->build', $executor);
        $this->assertStringContainsString('IMPACT_REPROCESS_EXECUTED', $executor);
        $this->assertStringContainsString('PENDING_PROMOTE', $executor);
        $this->assertStringContainsString('PENDING_READABLE_CORRECTION', $executor);
        $this->assertStringContainsString('readable_correction_candidate_trade_dates', $executor);
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
        $this->assertStringContainsString('executeReadablePublicationAutoCorrection', $orchestrator);
        $this->assertStringContainsString('correction_current', $publicationReprocess);
        $this->assertStringContainsString('AUTOMATED_READABLE_CORRECTION', $publicationReprocess);
        $this->assertStringContainsString('publication_reprocess_correction_ids', $orchestrator);
        $this->assertStringContainsString('publication_reprocess_republication_mode', $orchestrator);
        $this->assertStringContainsString('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $publicationReprocess);
        $this->assertStringContainsString('completeHash', $promoteSingleDay);
        $this->assertStringContainsString('completeSeal', $promoteSingleDay);
        $this->assertStringContainsString('completeFinalize', $promoteSingleDay);
    }


    public function test_import_only_backfill_outputs_reprocess_execution_surface(): void
    {
        $service = $this->read('app/Application/MarketData/Services/MarketDataBackfillService.php');
        $command = $this->read('app/Console/Commands/MarketData/BackfillMarketDataCommand.php');

        foreach ([
            'indicator_reprocess_execution_state',
            'indicator_reprocessed_trade_date_count',
            'eligibility_reprocess_execution_state',
            'eligibility_reprocessed_trade_date_count',
            'publication_reprocess_state',
            'publication_reprocess_republished_trade_date_count',
            'publication_reprocess_candidate_trade_dates',
            'publication_reprocess_blocked_reason_code',
            'publication_reprocess_republication_mode',
            'publication_reprocess_correction_ids',
            'publication_reprocess_correction_id',
            'recovered_row_apply_state',
            'recovered_row_count',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service);
            $this->assertStringContainsString($needle, $command);
        }
    }

    private function extractMethod(string $source, string $methodName): string
    {
        $pattern = '/function '.preg_quote($methodName, '/').'\([^)]*\)\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
