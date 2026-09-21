<?php

use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\EligibilityDecisionService;
use App\Application\MarketData\Services\EodBarsIngestService;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Application\MarketData\Services\EodIndicatorsComputeService;
use App\Application\MarketData\Services\FinalizeDecisionService;
use App\Application\MarketData\Services\IndicatorVectorService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\PublicationDiffService;
use App\Application\MarketData\Services\PublicationFinalizeOutcomeService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataPipelineIntegrationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootMarketDataSqlite();
        Carbon::setTestNow('2026-03-25 10:30:00');
        $this->seedMarketCalendarRange('2025-01-01', '2026-12-31');

        $this->fixtureDir = storage_path('framework/testing/market_data_pipeline');
        if (! is_dir($this->fixtureDir)) {
            mkdir($this->fixtureDir, 0777, true);
        }

        // Penting:
        // LocalFileEodBarsAdapter memakai base_path(config('market_data.source.local_directory')).
        // Jadi nilai config harus RELATIVE terhadap base_path(), bukan absolute path hasil storage_path().
        config()->set('market_data.source.local_directory', 'storage/framework/testing/market_data_pipeline');
        config()->set('market_data.source.file_template_json', '{date}.json');
        config()->set('market_data.source.file_template_csv', '{date}.csv');
        config()->set('market_data.source.default_source_name', 'LOCAL_FILE');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        $this->deleteDirectory($this->fixtureDir);

        parent::tearDown();
    }

    public function test_recovered_ingest_captures_its_exact_consumed_rows_before_partial_write(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20',
            'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipeline();
        $original = $pipeline->importDaily('2026-03-20', 'manual_file');
        $repository = new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();
        $inputs = $this->capturedOperation($repository->forRun($original->run_id), 'ingest-source-rows/v1')['rows'];
        $recovered = $pipeline->applyRecoveredRowsPartial('2026-03-20', 'manual_file', $inputs);
        $this->assertNotSame($original->run_id, $recovered->run_id, 'Recovery uses the existing completed-run lifecycle.');
        $captures = array_column($repository->forRun($recovered->run_id), null, 'component_key');
        foreach (['run_config', 'source_observations', 'provider_mapping'] as $component) {
            $this->assertArrayHasKey($component, $captures, 'Recovered producer omitted required capture '.$component);
        }
        $payload = $this->capturedOperation($repository->forRun($recovered->run_id), 'ingest-source-rows/v1');
        $this->assertSame($inputs, $payload['rows']);
        $this->assertSame('ingestRecoveredRowsPartial', $payload['selection_context']['input_route']);
        $this->assertSame([], (new \App\Infrastructure\Persistence\MarketData\ProducerSourceObservationCompleteness())->missing($repository->forRun($recovered->run_id), $repository));
        $mapping = $this->capturedOperation($repository->forRun($recovered->run_id), 'ingest-resolved-mappings/v1');
        $this->assertGreaterThan(0, $mapping['rows'][0]['listing_id']);
        $population = $this->capturedOperation($repository->forRun($recovered->run_id), 'provider-mapping-revisions/v1');
        $this->assertSame('BBCA', $population['rows'][0]['selected_rows'][0]['ticker_code']);
        $this->assertFalse(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->exists());
    }

    public function test_run_daily_persists_full_db_backed_pipeline_and_current_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $expectedPublicationId = (int) DB::table('eod_publications')->max('publication_id') + 1;

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $this->assertFileExists($this->fixtureDir.'/2026-03-20.json');

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertSame('2026-03-20', $run->trade_date_effective);
        $this->assertSame('LOCAL_FILE', $run->source_name);
        $captures = (new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository())->forRun($run->run_id);
        $byComponent = array_column($captures, null, 'component_key');
        $this->assertArrayHasKey('calendar_session', $byComponent);
        $this->assertArrayHasKey('registry_versions', $byComponent);
        $this->assertArrayHasKey('completion', $byComponent);
        $calendarDates = [];
        foreach ($captures as $capture) {
            if ($capture['component_key'] !== 'calendar_session') continue;
            $payload = json_decode($capture['semantic_payload_json'], true);
            $this->assertSame((string) $run->knowledge_cutoff_at, $payload['selection_context']['known_at']);
            foreach ($payload['rows'] as $revision) {
                $this->assertArrayHasKey('recorded_at', $revision);
                $this->assertArrayHasKey('supersedes_revision_id', $revision);
                $calendarDates[$revision['cal_date']] = true;
            }
        }
        $this->assertArrayHasKey('2026-03-20', $calendarDates);
        $this->assertArrayHasKey('2026-03-19', $calendarDates);
        $sourceCapture = $this->capturedOperation($captures, 'ingest-source-rows/v1');
        $this->assertSame('BBCA', $sourceCapture['rows'][0]['ticker_code']);
        $this->assertEquals(124, $sourceCapture['rows'][0]['close']);
        $this->assertSame('ingestAcquiredRows', $sourceCapture['selection_context']['input_route']);
        $mappingCapture = $this->capturedOperation($captures, 'ingest-resolved-mappings/v1');
        $this->assertSame('BBCA', $mappingCapture['rows'][0]['ticker_code']);
        $this->assertGreaterThan(0, $mappingCapture['rows'][0]['listing_id']);
        foreach (['temporal-identity-revisions/v1', 'provider-mapping-revisions/v1'] as $operation) {
            $population = $this->capturedOperation($captures, $operation);
            $source = $this->capturedPopulation($captures, $population['rows'][0]['population_ref']);
            $this->assertCount(6, $source['tables']);
            $this->assertSame('BBCA', $population['rows'][0]['selected_rows'][0]['ticker_code']);
        }
        $sourceLink = $this->capturedOperation($captures, 'provider-source-row-link/v1');
        $this->assertSame($sourceLink['rows'][0]['observation_row']['source_observation_row_id'],
            (string) $sourceLink['rows'][0]['identity_binding']['source_observation_row_id']);
        $statusCapture = $this->capturedOperation($captures, 'status-authority-revisions/v1');
        $statusPopulation = $this->capturedPopulation($captures, $statusCapture['rows'][0]['population_ref']);
        $this->assertArrayHasKey('md_trading_status_revisions', $statusPopulation['tables']);
        $this->assertArrayHasKey('md_trading_status_source_registry', $statusPopulation['tables']);
        $this->assertArrayHasKey('bar_expectation_state', $statusCapture['rows'][0]['selection_result']);
        $this->assertArrayHasKey('omitted_revisions', $statusCapture['rows'][0]);
        $this->assertCount(64, $captures, 'All actual producer slots, calendar reads and per-scope completion must be retained.');
        $rawReads = [];
        foreach ($captures as $capture) {
            $payload = json_decode($capture['semantic_payload_json'], true);
            if (($payload['selection_context']['operation'] ?? null) !== 'raw-input-lineage/v1') continue;
            \App\Infrastructure\Persistence\MarketData\ProducerRawInputLineage::assertValid($payload['rows'][0], $payload['selection_context']);
            $rawReads[$payload['selection_context']['read_kind']] = $payload;
        }
        $this->assertSame([], (new \App\Infrastructure\Persistence\MarketData\ProducerRawInputCompleteness())->missing($captures, new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository()), 'Independent full RAW/read/projection completeness, not slot count.');
        $rawKinds = array_keys($rawReads); sort($rawKinds, SORT_STRING);
        $this->assertSame(['atr', 'date', 'window'], $rawKinds);
        $this->assertCount(21, $rawReads['atr']['rows'][0]['raw_rows']);
        $this->assertCount(21, $rawReads['window']['rows'][0]['raw_rows']);
        $this->assertCount(1, $rawReads['date']['rows'][0]['raw_rows']);
        $observationJournal = $this->capturedOperation($captures, 'source-observation-journal/v1');
        $this->assertArrayHasKey('payload_hash', $observationJournal['rows'][0]['observation']);
        $this->assertSame('PERSISTED_OBSERVATION_ONLY_NOT_CONSUMPTION_COMPLETENESS', $observationJournal['rows'][0]['scope']);
        $this->assertSame(['ACQUISITION', 'COVERAGE', 'ELIGIBILITY', 'HASH', 'INDICATORS', 'INGEST_BARS', 'RUN_CONTEXT'], array_values(array_unique(array_column($captures, 'stage_code'))));
        $capturedInputs = [];
        foreach ($captures as $capture) {
            $payload = json_decode($capture['semantic_payload_json'], true);
            $capturedInputs[$payload['selection_context']['operation']] = $payload;
        }
        // The SQLite mirror's eod_reason_codes is now seeded with the same canonical content as
        // deployed MariaDB (the whole-C1 fixture gap E031-E033 found is closed), so the manifest
        // this run captured is genuinely complete rather than BLOCKED on that one remaining gap.
        $this->assertSame('COMPLETE', $capturedInputs['input-completion-manifest/v1']['rows'][0]['status']);
        $this->assertSame([], (new \App\Infrastructure\Persistence\MarketData\ProducerSourceObservationCompleteness())->missing($captures, new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository()), 'Whole C06 requires content and cross-capture membership, not slot count.');
        $this->assertSame([], array_values(array_filter($capturedInputs['input-completion-manifest/v1']['rows'][0]['missing_paths'], static function ($path) {
            return strpos($path, 'universe_identity.') === 0 || strpos($path, 'provider_mapping.') === 0 || strpos($path, 'status_expectation.') === 0 || strpos($path, 'raw_history.') === 0 || strpos($path, 'event_factor.') === 0 || strpos($path, 'ancillary.') === 0 || strpos($path, 'completion.producer_scope.') === 0;
        })), 'C02/C03/C05/C08/C09 content and scoped membership are verified independently of slot count.');
        $this->assertArrayHasKey('rule_revisions', $capturedInputs['market-structure-consumed-inputs/v1']['rows'][0]);
        $this->assertCount(1, $capturedInputs['eligibility-universe/v1']['rows']);
        $this->assertSame('BBCA', $capturedInputs['eligibility-universe/v1']['rows'][0]['ticker_code']);
        $this->assertGreaterThan(1, count($capturedInputs['indicator-consumed-bars/v1']['rows']));
        $this->assertGreaterThan(1, count($capturedInputs['indicator-consumed-atr-series/v1']['rows']));
        $this->assertArrayHasKey('factor_set_hash', $capturedInputs['indicator-factor-context/v1']['rows'][0]);
        $this->assertArrayHasKey('bar_expectation_state', $capturedInputs['eligibility-expectation/v1']['rows'][0]);
        $this->assertSame($expectedPublicationId, (int) $run->publication_id);
        $this->assertNull($run->correction_id);
        $this->assertNull($run->final_reason_code);
        $this->assertEquals(1.0, (float) $run->coverage_ratio);
        $this->assertNotNull($run->bars_batch_hash);
        $this->assertNotNull($run->indicators_batch_hash);
        $this->assertNotNull($run->eligibility_batch_hash);
        $this->assertNotNull($run->config_snapshot_id);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $run->observation_manifest_hash);

        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($publication);
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame((int) $run->config_snapshot_id, (int) $publication->config_snapshot_id);
        $this->assertSame((string) $run->observation_manifest_hash, (string) $publication->observation_manifest_hash);
        $this->assertSame('STRUCTURAL_ADJUSTED', $publication->price_product_code);
        $this->assertSame('structural_adjusted_v2', $publication->price_product_version);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $publication->factor_set_hash);

        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $this->assertNotNull($pointer);
        $this->assertSame((int) $publication->publication_id, (int) $pointer->publication_id);

        $repo = new EodPublicationRepository();
        $resolvedCurrent = $repo->findCurrentPublicationForTradeDate('2026-03-20');
        $resolvedReadableForRun = $repo->findReadableCurrentPublicationForRun($run->run_id, '2026-03-20');

        $this->assertNotNull($resolvedCurrent);
        $this->assertNotNull($resolvedReadableForRun);
        $this->assertSame((int) $publication->publication_id, (int) $resolvedCurrent->publication_id);
        $this->assertSame((int) $publication->publication_id, (int) $resolvedReadableForRun->publication_id);

        $this->assertSame(1, DB::table('eod_bars')->where('trade_date', '2026-03-20')->count());
        $canonicalBar = DB::table('eod_bars')->where('trade_date', '2026-03-20')->first();
        $this->assertNotNull($canonicalBar->listing_id);
        $this->assertNotNull($canonicalBar->source_observation_id);
        $this->assertSame((int) $run->config_snapshot_id, (int) $canonicalBar->config_snapshot_id);
        $this->assertSame('RAW', $canonicalBar->price_product_code);
        $this->assertSame('VALIDATED', $canonicalBar->quality_state);
        $this->assertNull($canonicalBar->adj_close, 'provider adjusted close remains only in raw observation evidence');
        $this->assertTrue(DB::table('md_source_observations')->where('source_observation_id', $canonicalBar->source_observation_id)->where('outcome_state', 'ACCEPTED')->exists());
        $this->assertTrue(DB::table('md_source_observations')->where('run_id', $run->run_id)->where('outcome_state', 'CAPTURED')->exists());
        $this->assertSame(1, DB::table('eod_indicators')->where('trade_date', '2026-03-20')->count());
        $this->assertSame(1, DB::table('eod_eligibility')->where('trade_date', '2026-03-20')->where('eligible', 1)->count());

        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', $publication->publication_id)->count());
        $this->assertSame(1, DB::table('eod_indicators_history')->where('publication_id', $publication->publication_id)->count());
        $this->assertSame(1, DB::table('eod_eligibility_history')->where('publication_id', $publication->publication_id)->count());

        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'RUN_FINALIZED')
                ->exists()
        );
    }





    public function test_promote_daily_without_force_replace_holds_when_valid_current_exists(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 126,
            'low' => 120,
            'close' => 125,
            'volume' => 2500,
            'adj_close' => 125,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $run = $this->makePipeline()->promoteDaily('2026-03-20', 'manual_file');

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('RUN_LOCK_CONFLICT', $run->final_reason_code);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'RUN_FORCE_REPLACE_EXECUTED')
                ->exists()
        );
    }

    public function test_completed_lock_conflict_finalize_rerun_with_force_replace_is_idempotent(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 126,
            'low' => 120,
            'close' => 125,
            'volume' => 2500,
            'adj_close' => 125,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $pipeline = $this->makePipeline();
        $run = $pipeline->promoteDaily('2026-03-20', 'manual_file');

        $pointerBefore = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();
        $eventCountBefore = DB::table('eod_run_events')->where('run_id', $run->run_id)->count();
        $candidateCountBefore = DB::table('eod_publications')->where('run_id', $run->run_id)->count();

        $rerun = $pipeline->completeFinalize(
            new App\Application\MarketData\DTOs\MarketDataStageInput(
                '2026-03-20',
                'manual_file',
                $run->run_id,
                'FINALIZE',
                null,
                true,
                'late operator force replace must not mutate terminal lock-conflict run'
            )
        );

        $pointerAfter = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();
        $eventCountAfter = DB::table('eod_run_events')->where('run_id', $run->run_id)->count();
        $candidateCountAfter = DB::table('eod_publications')->where('run_id', $run->run_id)->count();

        $this->assertSame((int) $run->run_id, (int) $rerun->run_id);
        $this->assertSame('HELD', $rerun->terminal_status);
        $this->assertSame('NOT_READABLE', $rerun->publishability_state);
        $this->assertSame('RUN_LOCK_CONFLICT', $rerun->final_reason_code);
        $this->assertSame((int) $pointerBefore->publication_id, (int) $pointerAfter->publication_id);
        $this->assertSame((int) $pointerBefore->run_id, (int) $pointerAfter->run_id);
        $this->assertSame((int) $eventCountBefore, (int) $eventCountAfter);
        $this->assertSame((int) $candidateCountBefore, (int) $candidateCountAfter);
        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'RUN_FORCE_REPLACE_EXECUTED')
                ->exists()
        );
    }

    public function test_promote_daily_with_force_replace_switches_current_and_records_audit_event(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 126,
            'low' => 120,
            'close' => 125,
            'volume' => 2500,
            'adj_close' => 125,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $run = $this->makePipeline()->promoteDaily(
            '2026-03-20',
            'manual_file',
            null,
            null,
            null,
            true,
            'operator approved replacement after manual import validation'
        );

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertNull($run->final_reason_code);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame((int) $run->run_id, (int) $pointer->run_id);
        $this->assertNotSame(1, (int) $pointer->publication_id);

        $oldPublication = DB::table('eod_publications')->where('publication_id', 1)->first();
        $this->assertSame(0, (int) $oldPublication->is_current);

        $newPublication = DB::table('eod_publications')->where('publication_id', $pointer->publication_id)->first();
        $this->assertSame(1, (int) $newPublication->is_current);
        $this->assertSame(1, (int) $newPublication->supersedes_publication_id);
        $this->assertSame(1, (int) $newPublication->previous_publication_id);
        $this->assertSame(1, (int) $newPublication->replaced_publication_id);

        $event = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FORCE_REPLACE_EXECUTED')
            ->first();

        $this->assertNotNull($event);
        $this->assertSame('WARN', $event->severity);

        $payload = json_decode($event->event_payload_json, true);
        $this->assertTrue((bool) $payload['force_replace']);
        $this->assertSame(1, (int) $payload['previous_publication_id']);
        $this->assertSame((int) $pointer->publication_id, (int) $payload['new_publication_id']);
        $this->assertSame('operator approved replacement after manual import validation', $payload['force_replace_reason']);
    }

    public function test_complete_finalize_is_idempotent_for_already_completed_success_run(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $pipeline = $this->makePipeline();
        $run = $pipeline->runDaily('2026-03-20', 'manual_file');
        $pointerBefore = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $eventCountBefore = DB::table('eod_run_events')->where('run_id', $run->run_id)->count();

        $rerun = $pipeline->completeFinalize(
            new App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-20', 'manual_file', $run->run_id, 'FINALIZE', null)
        );

        $pointerAfter = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $eventCountAfter = DB::table('eod_run_events')->where('run_id', $run->run_id)->count();

        $this->assertSame((int) $run->run_id, (int) $rerun->run_id);
        $this->assertSame('SUCCESS', $rerun->terminal_status);
        $this->assertSame('READABLE', $rerun->publishability_state);
        $this->assertSame((int) $pointerBefore->publication_id, (int) $pointerAfter->publication_id);
        $this->assertSame((int) $pointerBefore->run_id, (int) $pointerAfter->run_id);
        $this->assertSame((int) $eventCountBefore, (int) $eventCountAfter);
    }

    public function test_completed_success_finalize_rerun_with_invalid_pointer_fails_safe_without_duplicate_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $pipeline = $this->makePipeline();
        $run = $pipeline->runDaily('2026-03-20', 'manual_file');

        $publicationCountBefore = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->count();

        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->update([
                'publication_version' => 999,
            ]);

        $rerun = $pipeline->completeFinalize(
            new App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-20', 'manual_file', $run->run_id, 'FINALIZE', null)
        );

        $publicationCountAfter = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->count();

        $this->assertSame((int) $run->run_id, (int) $rerun->run_id);
        $this->assertSame('HELD', $rerun->terminal_status);
        $this->assertSame('NOT_READABLE', $rerun->publishability_state);
        $this->assertSame('RUN_LOCK_CONFLICT', $rerun->final_reason_code);
        $this->assertNull($rerun->trade_date_effective);
        $this->assertSame((int) $publicationCountBefore, (int) $publicationCountAfter);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());
        $this->assertSame(0, DB::table('eod_publications')->where('trade_date', '2026-03-20')->where('is_current', 1)->count());

        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID')
                ->where('reason_code', 'RUN_LOCK_CONFLICT')
                ->exists()
        );
    }

    public function test_run_daily_success_path_with_post_switch_resolution_mismatch_holds_and_clears_invalid_current_pointer(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $run = $this->makePipelineWithPublications(new PostSwitchResolutionMismatchPublicationRepository())->runDaily('2026-03-20', 'manual_file');

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('RUN_LOCK_CONFLICT', $run->final_reason_code);
        $this->assertNull($run->trade_date_effective);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());

        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($publication);
        $this->assertSame(0, (int) $publication->is_current);

        $currentRun = DB::table('eod_runs')->where('run_id', $run->run_id)->first();
        $this->assertSame(0, (int) $currentRun->is_current_publication);

        $repo = new EodPublicationRepository();
        $this->assertNull($repo->findCurrentPublicationForTradeDate('2026-03-20'));
        $this->assertNull($repo->findReadableCurrentPublicationForRun($run->run_id, '2026-03-20'));
    }


    public function test_run_daily_api_source_timeout_degraded_hold_persists_attempt_context_in_run_event(): void
    {
        $this->seedTicker(1, 'BBCA');
        config()->set('market_data.source.api.endpoint_template', 'https://example.test/eod/{date}?symbols={symbols}');
        config()->set('market_data.source.api.response_rows_path', 'rows');
        config()->set('market_data.source.api.provider', 'generic');
        config()->set('market_data.provider.api_retry_max', 2);
        config()->set('market_data.provider.api_backoff_ms', 5);
        config()->set('market_data.provider.api_throttle_qps', 1000);

        $calls = 0;
        $this->makePipelineWithApiFetcher(function () use (&$calls) {
            $calls++;

            return [
                'status' => 500,
                'body' => '{"error":"upstream unavailable"}',
            ];
        })->runDaily('2026-03-20', 'api');

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->orderByDesc('run_id')
            ->first();

        $this->assertNotNull($run);
        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame(3, $calls);
        $this->assertStringContainsString('source_name=API_FREE', (string) $run->notes);
        $this->assertStringContainsString('source_provider=generic', (string) $run->notes);
        $this->assertStringContainsString('source_timeout_seconds=20', (string) $run->notes);
        $this->assertStringContainsString('source_retry_max=2', (string) $run->notes);
        $this->assertStringContainsString('source_attempt_count=3', (string) $run->notes);
        $this->assertStringContainsString('source_final_reason_code=RUN_SOURCE_TIMEOUT', (string) $run->notes);
        $this->assertStringContainsString('degraded_mode=NO_BASELINE_HELD', (string) $run->notes);
        $this->assertStringContainsString('final_outcome_note=SOURCE_UNAVAILABLE_NO_BASELINE', (string) $run->notes);

        $stageFailedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'STAGE_FAILED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($stageFailedEvent);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $stageFailedEvent->reason_code);

        $payload = json_decode($stageFailedEvent->event_payload_json, true);
        $this->assertIsArray($payload);
        $this->assertSame('App\Infrastructure\MarketData\Source\SourceAcquisitionException', $payload['exception_class']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $payload['exception_context']['final_reason_code']);
        $this->assertSame('generic', $payload['exception_context']['provider']);
        $this->assertSame(2, $payload['exception_context']['retry_max']);
        $this->assertSame(3, $payload['exception_context']['attempt_count']);
        $this->assertCount(3, $payload['exception_context']['attempts']);
        $this->assertTrue($payload['exception_context']['attempts'][0]['will_retry']);
        $this->assertFalse($payload['exception_context']['attempts'][2]['will_retry']);
        $this->assertGreaterThan(0, $payload['exception_context']['attempts'][0]['backoff_delay_ms']);
        $this->assertSame(0, $payload['exception_context']['attempts'][2]['backoff_delay_ms']);
    }
    public function test_run_daily_manual_file_with_explicit_input_file_exports_source_context_in_run_evidence(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        $explicitFile = $this->fixtureDir.'/manual-explicit-2026-03-20.csv';
        file_put_contents($explicitFile, implode("\n", [
            'ticker_code,trade_date,open,high,low,close,volume,adj_close,captured_at',
            'BBCA,2026-03-20,121,125,120,124,2000,124,2026-03-20T17:20:00+07:00',
        ]));

        app(\App\Application\MarketData\Services\ManualSourceInputContext::class)->set('storage/framework/testing/market_data_pipeline/manual-explicit-2026-03-20.csv');

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');

        $exportDir = storage_path('framework/testing/market_data_pipeline/evidence-manual-file');
        $result = $this->makeEvidenceExporter()->exportRunEvidence($run->run_id, $exportDir);
        $summary = json_decode(file_get_contents($exportDir.'/run_summary.json'), true);
        $evidencePack = json_decode(file_get_contents($exportDir.'/evidence_pack.json'), true);

        $this->assertSame('LOCAL_FILE', $result['summary']['source_name']);
        $this->assertSame('manual-explicit-2026-03-20.csv', $result['summary']['source_input_file']);
        $this->assertSame('source_priority=SECONDARY_CONTROLLED_RECOVERY | active_source_decision=manual_file | retry_attempt_count=0 | attempt_count=0', $result['summary']['source_summary']);
        $this->assertSame('LOCAL_FILE', $summary['source_context']['source_name']);
        $this->assertSame('SECONDARY_CONTROLLED_RECOVERY', $summary['source_context']['source_priority']);
        $this->assertSame('manual_file', $summary['source_context']['active_source_decision']);
        $this->assertSame(0, $summary['source_context']['retry_attempt_count']);
        $this->assertSame('manual-explicit-2026-03-20.csv', $summary['source_context']['source_input_file']);
        $this->assertSame('manual-explicit-2026-03-20.csv', $evidencePack['run_summary']['source_context']['source_input_file']);

        $runRow = DB::table('eod_runs')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($runRow);
        $this->assertStringContainsString('source_input_file=manual-explicit-2026-03-20.csv', (string) $runRow->notes);
    }

    public function test_run_daily_api_success_after_retry_exports_source_context_in_run_evidence(): void
    {
        $this->seedTicker(1, 'BBCA');
        config()->set('market_data.source.api.endpoint_template', 'https://example.test/eod/{date}?symbols={symbols}');
        config()->set('market_data.source.api.response_rows_path', 'rows');
        config()->set('market_data.source.api.provider', 'generic');
        config()->set('market_data.source.api.source_name', 'API_FREE');
        config()->set('market_data.provider.api_retry_max', 2);
        config()->set('market_data.provider.api_backoff_ms', 5);
        config()->set('market_data.provider.api_throttle_qps', 1000);

        $calls = 0;
        $run = $this->makePipelineWithApiFetcher(function () use (&$calls) {
            $calls++;

            if ($calls === 1) {
                return [
                    'status' => 429,
                    'body' => '{"error":"rate limit"}',
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['rows' => [[
                    'ticker_code' => 'BBCA',
                    'trade_date' => '2026-03-20',
                    'open' => 121,
                    'high' => 125,
                    'low' => 120,
                    'close' => 124,
                    'volume' => 2000,
                    'adj_close' => 124,
                    'captured_at' => '2026-03-20T17:20:00+07:00',
                ]]]),
            ];
        })->runDaily('2026-03-20', 'api');

        $this->assertSame(2, $calls);

        $exportDir = storage_path('framework/testing/market_data_pipeline/evidence-api-retry');
        $result = $this->makeEvidenceExporter()->exportRunEvidence($run->run_id, $exportDir);
        $summary = json_decode(file_get_contents($exportDir.'/run_summary.json'), true);
        $evidencePack = json_decode(file_get_contents($exportDir.'/evidence_pack.json'), true);

        $this->assertSame('API_FREE', $result['summary']['source_name']);
        $this->assertSame('provider=generic | source_priority=PRIMARY | active_source_decision=api_free | retry_attempt_count=1 | timeout_seconds=20 | retry_max=2 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | failure_class_summary={"TRANSIENT":1}', $result['summary']['source_summary']);
        $this->assertSame('API_FREE', $summary['source_context']['source_name']);
        $this->assertSame('PRIMARY', $summary['source_context']['source_priority']);
        $this->assertSame('api_free', $summary['source_context']['active_source_decision']);
        $this->assertSame(1, $summary['source_context']['retry_attempt_count']);
        $this->assertSame(['TRANSIENT' => 1], $summary['source_context']['failure_class_summary']);
        $this->assertSame(2, $summary['source_context']['attempt_count']);
        $this->assertSame('yes', $summary['source_context']['success_after_retry']);
        $this->assertSame(200, $summary['source_context']['final_http_status']);
        $this->assertSame('API_FREE', $evidencePack['run_summary']['source_context']['source_name']);
        $this->assertSame('PRIMARY', $evidencePack['run_summary']['source_context']['source_priority']);
        $this->assertSame('api_free', $evidencePack['run_summary']['source_context']['active_source_decision']);
        $this->assertSame(1, $evidencePack['run_summary']['source_context']['retry_attempt_count']);
        $this->assertSame(['TRANSIENT' => 1], $evidencePack['run_summary']['source_context']['failure_class_summary']);
        $this->assertSame(2, $evidencePack['run_summary']['source_context']['attempt_count']);

        $runRow = DB::table('eod_runs')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($runRow);
        $this->assertStringContainsString('source_provider=generic', (string) $runRow->notes);
        $this->assertStringContainsString('source_timeout_seconds=20', (string) $runRow->notes);
        $this->assertStringContainsString('source_retry_max=2', (string) $runRow->notes);
        $this->assertStringContainsString('source_priority=PRIMARY', (string) $runRow->notes);
        $this->assertStringContainsString('active_source_decision=api_free', (string) $runRow->notes);
        $this->assertStringContainsString('source_retry_attempt_count=1', (string) $runRow->notes);
        $this->assertStringContainsString('source_failure_class_summary_json={"TRANSIENT":1}', (string) $runRow->notes);
        $this->assertStringContainsString('source_attempt_count=2', (string) $runRow->notes);
        $this->assertStringContainsString('source_success_after_retry=yes', (string) $runRow->notes);
        $this->assertStringContainsString('source_final_http_status=200', (string) $runRow->notes);
    }


    public function test_backfill_api_success_after_retry_writes_source_context_per_date_in_summary_artifact(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedMarketCalendarRange('2026-03-20', '2026-03-23');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        config()->set('market_data.source.api.endpoint_template', 'https://example.test/eod/{date}?symbols={symbols}');
        config()->set('market_data.source.api.response_rows_path', 'rows');
        config()->set('market_data.source.api.provider', 'generic');
        config()->set('market_data.source.api.source_name', 'API_FREE');
        config()->set('market_data.provider.api_retry_max', 2);
        config()->set('market_data.provider.api_backoff_ms', 5);
        config()->set('market_data.provider.api_throttle_qps', 1000);

        $attemptsByDate = [];
        $service = $this->makeBackfillServiceWithApiFetcher(function (string $url) use (&$attemptsByDate) {
            $path = (string) parse_url($url, PHP_URL_PATH);
            $date = trim((string) basename($path));

            if ($date === '') {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $date = (string) ($query['date'] ?? '');
            }

            $attemptsByDate[$date] = ($attemptsByDate[$date] ?? 0) + 1;

            if ($attemptsByDate[$date] === 1) {
                return [
                    'status' => 429,
                    'body' => '{"error":"rate limit"}',
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['rows' => [[
                    'ticker_code' => 'BBCA',
                    'trade_date' => $date,
                    'open' => 121,
                    'high' => 125,
                    'low' => 120,
                    'close' => 124,
                    'volume' => 2000,
                    'adj_close' => 124,
                    'captured_at' => $date.'T17:20:00+07:00',
                ]]]),
            ];
        });

        $outputDir = storage_path('framework/testing/market_data_pipeline/backfill-api-retry');
        $summary = $service->execute('2026-03-20', '2026-03-23', 'api', $outputDir, false);
        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);

        $this->assertTrue($summary['all_passed']);
        $this->assertCount(2, $summary['cases']);
        $this->assertSame('API_FREE', $summary['cases'][0]['source_name']);
        $this->assertSame('provider=generic | source_priority=PRIMARY | active_source_decision=api_free | retry_attempt_count=1 | timeout_seconds=20 | retry_max=2 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | failure_class_summary={"TRANSIENT":1}', $summary['cases'][0]['source_summary']);
        $this->assertSame('API_FREE', $summaryFile['cases'][1]['source_name']);
        $this->assertSame('provider=generic | source_priority=PRIMARY | active_source_decision=api_free | retry_attempt_count=1 | timeout_seconds=20 | retry_max=2 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | failure_class_summary={"TRANSIENT":1}', $summaryFile['cases'][1]['source_summary']);
        $this->assertSame(['2026-03-20' => 2, '2026-03-23' => 2], $attemptsByDate);
    }

    public function test_backfill_manual_file_writes_real_run_source_name_in_summary_artifact(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedMarketCalendarRange('2026-03-20', '2026-03-20');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $outputDir = storage_path('framework/testing/market_data_pipeline/backfill-manual-file');
        $summary = $this->makeBackfillService()->execute('2026-03-20', '2026-03-20', 'manual_file', $outputDir, false);
        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);

        $this->assertTrue($summary['all_passed']);
        $this->assertSame('LOCAL_FILE', $summary['cases'][0]['source_name']);
        $this->assertArrayHasKey('source_summary', $summary['cases'][0]);
        $this->assertStringContainsString('source_priority=SECONDARY_CONTROLLED_RECOVERY', (string) $summary['cases'][0]['source_summary']);
        $this->assertSame('LOCAL_FILE', $summaryFile['cases'][0]['source_name']);
        $this->assertArrayNotHasKey('source_input_file', $summaryFile['cases'][0]);
    }


    public function test_run_daily_correction_replaces_current_publication_and_marks_correction_published(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 130,
            'high' => 135,
            'low' => 129,
            'close' => 134,
            'volume' => 2500,
            'adj_close' => 134,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $this->assertFileExists($this->fixtureDir.'/2026-03-20.json');

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);

        $this->assertSame(
            1,
            DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->where('is_current', 1)
                ->count()
        );

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertGreaterThan(1, (int) $currentPublication->publication_version);
        $this->assertSame(1, (int) $currentPublication->supersedes_publication_id);

        $currentBar = DB::table('eod_bars')
            ->where('trade_date', '2026-03-20')
            ->where('ticker_id', 1)
            ->first();

        $this->assertNotNull($currentBar);
        $this->assertEquals('134', (string) $currentBar->close);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('PUBLISHED', $persistedCorrection->status);
        $this->assertSame(
            'Historical correction published safely via new sealed current publication.',
            $persistedCorrection->final_outcome_note
        );
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNotNull($persistedCorrection->published_at);

        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', 1)->count());
        $this->assertSame(
            1,
            DB::table('eod_bars_history')
                ->where('publication_id', $currentPublication->publication_id)
                ->count()
        );

        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }

    public function test_run_daily_correction_with_unchanged_artifacts_cancels_request_and_preserves_current_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);

        $baselineRows = [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]];

        $this->writeBarsFixture('2026-03-20', $baselineRows);
        $baselineRun = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');

        $baselinePublication = DB::table('eod_publications')
            ->where('run_id', $baselineRun->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($baselinePublication);
        $this->assertSame(1, (int) $baselinePublication->is_current);
        $this->assertNotNull($baselinePublication->bars_batch_hash);
        $this->assertNotNull($baselinePublication->indicators_batch_hash);
        $this->assertNotNull($baselinePublication->eligibility_batch_hash);

        $baselineBarsHash = (string) $baselinePublication->bars_batch_hash;
        $baselineIndicatorsHash = (string) $baselinePublication->indicators_batch_hash;
        $baselineEligibilityHash = (string) $baselinePublication->eligibility_batch_hash;

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-same-content', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $this->writeBarsFixture('2026-03-20', $baselineRows);
        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertNotNull($currentPublication->bars_batch_hash);
        $this->assertNotNull($currentPublication->indicators_batch_hash);
        $this->assertNotNull($currentPublication->eligibility_batch_hash);

        $this->assertSame(
            $baselineBarsHash,
            (string) $currentPublication->bars_batch_hash,
            'bars_batch_hash must remain identical for unchanged correction rerun'
        );

        $this->assertSame(
            $baselineIndicatorsHash,
            (string) $currentPublication->indicators_batch_hash,
            'indicators_batch_hash must remain identical for unchanged correction rerun'
        );

        $this->assertSame(
            $baselineEligibilityHash,
            (string) $currentPublication->eligibility_batch_hash,
            'eligibility_batch_hash must remain identical for unchanged correction rerun'
        );

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('CONSUMED_CURRENT', $persistedCorrection->status);
        $this->assertSame(
            'Correction rerun produced unchanged content; current publication preserved without version switch.',
            $persistedCorrection->final_outcome_note
        );
        $this->assertSame((int) $baselineRun->run_id, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);

        $this->assertSame((int) $baselinePublication->publication_id, (int) $currentPublication->publication_id);
        $this->assertSame((int) $baselinePublication->publication_version, (int) $currentPublication->publication_version);

        $this->assertSame(
            1,
            DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_CANCELLED')
                ->exists()
        );
    }



    public function test_run_daily_correction_without_approval_rejects_before_run_creation_and_preserves_current_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'request-without-approval', 'system');

        $eventsBefore = DB::table('eod_run_events')->count();
        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $request->correction_id);
            $this->fail('Expected correction without approval to be rejected before run creation.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Correction request must be APPROVED before execution.', $e->getMessage());
        }

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $request->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('REQUESTED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);
        $this->assertSame(90, (int) $currentPublication->run_id);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(1, DB::table('eod_publications')->where('trade_date', '2026-03-20')->count());
        $this->assertSame(0, DB::table('eod_runs')->where('notes', 'like', 'correction_id='.$request->correction_id.'%')->count());
        $this->assertSame($eventsBefore, DB::table('eod_run_events')->count());
    }
    public function test_run_daily_correction_with_reseal_failure_keeps_prior_current_and_leaves_candidate_non_current(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 133,
            'high' => 138,
            'low' => 132,
            'close' => 137,
            'volume' => 2800,
            'adj_close' => 137,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-reseal-failure', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipelineWithPublications(
                new ThrowingSealPublicationRepository('Seal persistence failed while recording correction candidate publication.')
            )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected reseal failure to abort correction pipeline.');
        } catch (RuntimeException $e) {
            $this->assertSame('Seal persistence failed while recording correction candidate publication.', $e->getMessage());
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->orderByDesc('run_id')
            ->first();

        $this->assertNotNull($run);
        $this->assertSame('FAILED', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('FAILED', $run->lifecycle_state);
        $this->assertNull($run->sealed_at);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('EXECUTING', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);
        $this->assertSame('SEALED', $currentPublication->seal_state);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('UNSEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);
        $this->assertNull($candidatePublication->sealed_at);

        $stageFailedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'STAGE_FAILED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($stageFailedEvent);
        $this->assertSame('ERROR', $stageFailedEvent->severity);
        $this->assertSame('RUN_SEAL_WRITE_FAILED', $stageFailedEvent->reason_code);
        $this->assertStringContainsString('Seal persistence failed while recording correction candidate publication.', (string) $stageFailedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'RUN_FINALIZED')
                ->exists()
        );

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }


    public function test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-promote-conflict', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new ThrowingPromotionPublicationRepository('Promotion lost run ownership while switching current publication.')
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertSame('2026-03-19', $run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertStringContainsString('Promotion lost run ownership while switching current publication.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }


    public function test_run_daily_correction_with_changed_artifacts_and_malformed_fallback_pointer_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->update([
                'run_id' => 81,
                'updated_at' => '2026-03-19 17:25:00',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-promote-conflict-and-malformed-fallback', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new ThrowingPromotionPublicationRepository('Promotion lost run ownership while switching current publication.')
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $this->assertSame(
            1,
            DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->where('is_current', 1)
                ->count()
        );

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $currentPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($currentPointer);
        $this->assertSame(1, (int) $currentPointer->publication_id);
        $this->assertSame(90, (int) $currentPointer->run_id);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(81, (int) $fallbackPointer->run_id);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertStringContainsString('Promotion lost run ownership while switching current publication.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_changed_artifacts_and_baseline_pointer_mismatch_holds_and_preserves_prior_current_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 132,
            'high' => 137,
            'low' => 131,
            'close' => 136,
            'volume' => 2700,
            'adj_close' => 136,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-baseline-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new BaselineMismatchPromotionPublicationRepository(2, 91, 9)
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertSame('2026-03-19', $run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $this->assertFalse(
            DB::table('eod_publications')
                ->where('publication_id', 2)
                ->exists()
        );

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertStringContainsString('Correction baseline no longer matches current publication pointer.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }

    public function test_run_daily_correction_with_changed_artifacts_and_post_switch_resolution_mismatch_restores_prior_current_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertSame('2026-03-19', $run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $priorRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($priorRun);
        $this->assertSame(1, (int) $priorRun->is_current_publication);

        $candidateRun = DB::table('eod_runs')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($candidateRun);
        $this->assertSame(0, (int) $candidateRun->is_current_publication);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }


    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_malformed_fallback_pointer_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->update([
                'run_id' => 999,
                'updated_at' => '2026-03-19 17:21:00',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-malformed-fallback', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $malformedFallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($malformedFallbackPointer);
        $this->assertSame(999, (int) $malformedFallbackPointer->run_id);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }


    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_missing_pointer_row_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->delete();
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-missing-pointer-row', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $this->assertFalse(
            DB::table('eod_current_publication_pointer')
                ->where('trade_date', '2026-03-19')
                ->exists()
        );

        $fallbackPublication = DB::table('eod_publications')->where('publication_id', 11)->first();
        $this->assertNotNull($fallbackPublication);
        $this->assertSame('SEALED', $fallbackPublication->seal_state);
        $this->assertSame(1, (int) $fallbackPublication->is_current);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_missing_publication_row_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_publications')
            ->where('publication_id', 11)
            ->delete();
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-missing-publication-row', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $this->assertNull(
            DB::table('eod_publications')
                ->where('publication_id', 11)
                ->first()
        );

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_publication_version_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->update([
                'publication_version' => 99,
                'updated_at' => '2026-03-19 17:22:00',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-publication-version-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $malformedFallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($malformedFallbackPointer);
        $this->assertSame(99, (int) $malformedFallbackPointer->publication_version);
        $this->assertSame(11, (int) $malformedFallbackPointer->publication_id);
        $this->assertSame(80, (int) $malformedFallbackPointer->run_id);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }


    public function test_run_daily_correction_with_history_promotion_failure_keeps_prior_current_and_candidate_sealed_non_current(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-history-promotion-failure', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $this->makePipelineWithArtifacts(
            new ThrowingHistoryPromotionArtifactRepository('History promotion to current tables failed during correction finalize.')
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->orderByDesc('run_id')
            ->first();

        $this->assertNotNull($run);
        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNotNull($run->sealed_at);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);
        $this->assertSame('SEALED', $currentPublication->seal_state);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);
        $this->assertNotNull($candidatePublication->sealed_at);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertStringContainsString(
            'History promotion to current tables failed during correction finalize.',
            (string) $finalizedEvent->message
        );

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }

    public function test_run_daily_approved_correction_without_current_baseline_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-without-current-baseline', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected missing current baseline to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $this->assertSame(
            0,
            DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertNull(
            DB::table('eod_current_publication_pointer')
                ->where('trade_date', '2026-03-20')
                ->first()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }





    public function test_run_daily_approved_correction_with_pointer_to_different_trade_date_publication_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToDifferentTradeDatePublication('2026-03-20', '2026-03-19', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-different-trade-date-publication', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected pointer to different-trade-date publication to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('2026-03-19', $publication->trade_date);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }

    public function test_run_daily_approved_correction_with_pointer_to_publication_whose_run_requested_trade_date_mismatches_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToPublicationWithRunRequestedTradeDateMismatch('2026-03-20', '2026-03-19', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-baseline-run-requested-trade-date-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected baseline run requested-trade-date mismatch to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('2026-03-19', (string) $baselineRun->trade_date_requested);
        $this->assertSame('SUCCESS', $baselineRun->terminal_status);
        $this->assertSame('READABLE', $baselineRun->publishability_state);
        $this->assertSame(1, (int) $baselineRun->is_current_publication);

        $baselinePointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($baselinePointer);
        $this->assertSame(1, (int) $baselinePointer->publication_id);
        $this->assertSame(90, (int) $baselinePointer->run_id);
        $this->assertSame(1, (int) $baselinePointer->publication_version);

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }
    public function test_run_daily_approved_correction_with_pointer_to_publication_whose_run_row_is_missing_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToPublicationWithMissingRunForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-publication-whose-run-row-is-missing', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected missing run row behind current pointer publication to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->orderBy('run_id')
            ->get();

        $this->assertCount(0, $run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertSame(90, (int) $publication->run_id);

        $this->assertNull(DB::table('eod_runs')->where('run_id', 90)->first());

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }


    public function test_run_daily_approved_correction_with_pointer_to_non_readable_run_publication_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToNonReadableRunPublicationForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-non-readable-run-publication', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected non-readable baseline run to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('HELD', $baselineRun->terminal_status);
        $this->assertSame('NOT_READABLE', $baselineRun->publishability_state);
        $this->assertSame(0, (int) $baselineRun->is_current_publication);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }


    public function test_run_daily_approved_correction_with_pointer_to_success_readable_run_marked_non_current_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToReadableRunMarkedNonCurrentForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-readable-run-marked-non-current', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected baseline run current-mirror mismatch to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('SUCCESS', $baselineRun->terminal_status);
        $this->assertSame('READABLE', $baselineRun->publishability_state);
        $this->assertSame(0, (int) $baselineRun->is_current_publication);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }



    public function test_run_daily_approved_correction_with_pointer_to_sealed_publication_marked_non_current_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToSealedPublicationMarkedNonCurrentForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-sealed-publication-marked-non-current', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected publication current-mirror mismatch to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $unexpectedRun = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($unexpectedRun);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(0, (int) $publication->is_current);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('SUCCESS', $baselineRun->terminal_status);
        $this->assertSame('READABLE', $baselineRun->publishability_state);
        $this->assertSame(1, (int) $baselineRun->is_current_publication);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }


    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_trade_date_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_publications')
            ->where('publication_id', 11)
            ->update([
                'trade_date' => '2026-03-18',
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-trade-date-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $corruptedFallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($corruptedFallbackPublication);
        $this->assertSame('2026-03-18', (string) $corruptedFallbackPublication->trade_date);
        $this->assertSame(1, (int) $corruptedFallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_publication_missing_run_row_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_runs')->where('run_id', 80)->delete();
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-missing-run-row', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $corruptedFallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($corruptedFallbackPublication);
        $this->assertSame('2026-03-19', (string) $corruptedFallbackPublication->trade_date);
        $this->assertSame(80, (int) $corruptedFallbackPublication->run_id);
        $this->assertSame(1, (int) $corruptedFallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);
        $this->assertNull(DB::table('eod_runs')->where('run_id', 80)->first());

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_run_id_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->update([
                'run_id' => 999,
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-run-id-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $fallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($fallbackPublication);
        $this->assertSame('2026-03-19', (string) $fallbackPublication->trade_date);
        $this->assertSame(80, (int) $fallbackPublication->run_id);
        $this->assertSame(1, (int) $fallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(999, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $this->assertNull(DB::table('eod_runs')->where('run_id', 999)->first());

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_run_current_mirror_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_runs')
            ->where('run_id', 80)
            ->update([
                'is_current_publication' => 0,
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-run-current-mirror-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $corruptedFallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($corruptedFallbackPublication);
        $this->assertSame('2026-03-19', (string) $corruptedFallbackPublication->trade_date);
        $this->assertSame(80, (int) $corruptedFallbackPublication->run_id);
        $this->assertSame(1, (int) $corruptedFallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(0, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_run_terminal_status_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_runs')
            ->where('run_id', 80)
            ->update([
                'terminal_status' => 'HELD',
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-run-terminal-status-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $fallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($fallbackPublication);
        $this->assertSame('2026-03-19', (string) $fallbackPublication->trade_date);
        $this->assertSame(80, (int) $fallbackPublication->run_id);
        $this->assertSame(1, (int) $fallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('HELD', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_run_publishability_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_runs')
            ->where('run_id', 80)
            ->update([
                'publishability_state' => 'NOT_READABLE',
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-run-publishability-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $fallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($fallbackPublication);
        $this->assertSame('2026-03-19', (string) $fallbackPublication->trade_date);
        $this->assertSame(80, (int) $fallbackPublication->run_id);
        $this->assertSame(1, (int) $fallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('NOT_READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_run_requested_trade_date_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_runs')
            ->where('run_id', 80)
            ->update([
                'trade_date_requested' => '2026-03-18',
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-run-requested-trade-date-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('2026-03-18', (string) $fallbackRun->trade_date_requested);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }
    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_publication_missing_sealed_at_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_publications')
            ->where('publication_id', 11)
            ->update([
                'sealed_at' => null,
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-publication-missing-sealed-at', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $corruptedFallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($corruptedFallbackPublication);
        $this->assertSame('2026-03-19', (string) $corruptedFallbackPublication->trade_date);
        $this->assertSame(80, (int) $corruptedFallbackPublication->run_id);
        $this->assertSame('SEALED', $corruptedFallbackPublication->seal_state);
        $this->assertSame(1, (int) $corruptedFallbackPublication->is_current);
        $this->assertNull($corruptedFallbackPublication->sealed_at);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }

    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_unsealed_publication_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_publications')
            ->where('publication_id', 11)
            ->update([
                'seal_state' => 'UNSEALED',
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-unsealed-publication', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $corruptedFallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($corruptedFallbackPublication);
        $this->assertSame('2026-03-19', (string) $corruptedFallbackPublication->trade_date);
        $this->assertSame(80, (int) $corruptedFallbackPublication->run_id);
        $this->assertSame('UNSEALED', $corruptedFallbackPublication->seal_state);
        $this->assertSame(1, (int) $corruptedFallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_correction_with_post_switch_resolution_mismatch_and_fallback_publication_current_mirror_mismatch_does_not_invent_effective_trade_date(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);
        DB::table('eod_publications')
            ->where('publication_id', 11)
            ->update([
                'is_current' => 0,
                'updated_at' => '2026-03-19 17:22:30',
            ]);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 136,
            'low' => 130,
            'close' => 135,
            'volume' => 2600,
            'adj_close' => 135,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-post-switch-resolution-mismatch-and-fallback-publication-current-mirror-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipelineWithPublications(
            new PostSwitchResolutionMismatchPublicationRepository()
        )->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->trade_date_effective);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('RESEALED', $persistedCorrection->status);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $currentPublication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('is_current', 1)
            ->first();

        $this->assertNotNull($currentPublication);
        $this->assertSame(1, (int) $currentPublication->publication_id);
        $this->assertSame(1, (int) $currentPublication->publication_version);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(1, (int) $pointer->publication_version);

        $candidatePublication = DB::table('eod_publications')
            ->where('run_id', $run->run_id)
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($candidatePublication);
        $this->assertSame('SEALED', $candidatePublication->seal_state);
        $this->assertSame(0, (int) $candidatePublication->is_current);
        $this->assertSame(1, (int) $candidatePublication->supersedes_publication_id);

        $corruptedFallbackPublication = DB::table('eod_publications')
            ->where('publication_id', 11)
            ->first();

        $this->assertNotNull($corruptedFallbackPublication);
        $this->assertSame('2026-03-19', (string) $corruptedFallbackPublication->trade_date);
        $this->assertSame(80, (int) $corruptedFallbackPublication->run_id);
        $this->assertSame(0, (int) $corruptedFallbackPublication->is_current);

        $fallbackPointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-19')
            ->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertSame(80, (int) $fallbackPointer->run_id);
        $this->assertSame(1, (int) $fallbackPointer->publication_version);

        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);

        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->orderByDesc('event_id')
            ->first();

        $this->assertNotNull($finalizedEvent);
        $this->assertSame('WARN', $finalizedEvent->severity);
        $this->assertSame('RUN_LOCK_CONFLICT', $finalizedEvent->reason_code);
        $this->assertSame('Current publication pointer resolution mismatch after finalize.', (string) $finalizedEvent->message);

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'CORRECTION_PUBLISHED')
                ->exists()
        );
    }



    public function test_run_daily_approved_correction_with_pointer_publication_version_mismatch_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToPublicationWithDifferentVersionForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-publication-version-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected pointer/publication publication_version mismatch to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $unexpectedRun = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($unexpectedRun);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertSame(1, (int) $publication->publication_version);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('SUCCESS', $baselineRun->terminal_status);
        $this->assertSame('READABLE', $baselineRun->publishability_state);
        $this->assertSame(1, (int) $baselineRun->is_current_publication);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);
        $this->assertSame(2, (int) $pointer->publication_version);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }

    public function test_run_daily_approved_correction_with_pointer_run_id_mismatch_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerToPublicationWithDifferentRunIdForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-run-id-mismatch', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected pointer/publication run_id mismatch to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $unexpectedRun = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->whereNotIn('run_id', [90, 91])
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($unexpectedRun);

                $this->assertSame(
            2,
            (int) DB::table('eod_runs')
                ->where('trade_date_requested', '2026-03-20')
                ->count()
        );

        $incidentRun = DB::table('eod_runs')->where('run_id', 91)->first();

        $this->assertNotNull($incidentRun);
        $this->assertSame('SUCCESS', $incidentRun->terminal_status);
        $this->assertSame('READABLE', $incidentRun->publishability_state);
        $this->assertSame(1, (int) $incidentRun->is_current_publication);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertSame(90, (int) $publication->run_id);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('SUCCESS', $baselineRun->terminal_status);
        $this->assertSame('READABLE', $baselineRun->publishability_state);
        $this->assertSame(1, (int) $baselineRun->is_current_publication);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(91, (int) $pointer->run_id);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }



    public function test_run_daily_approved_correction_with_pointer_to_missing_publication_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedBaselinePointerWithoutPublicationForTradeDate('2026-03-20');

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-missing-publication', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected pointer to missing publication to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $this->assertNull(
            DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->first()
        );

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(999, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }

    public function test_run_daily_approved_correction_with_pointer_to_publication_missing_sealed_at_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        DB::table('eod_publications')
            ->where('publication_id', 1)
            ->update([
                'sealed_at' => null,
                'updated_at' => '2026-03-20 17:22:30',
            ]);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-publication-missing-sealed-at', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected baseline publication missing sealed_at to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertNull($publication->sealed_at);

        $baselineRun = DB::table('eod_runs')->where('run_id', 90)->first();
        $this->assertNotNull($baselineRun);
        $this->assertSame('SUCCESS', $baselineRun->terminal_status);
        $this->assertSame('READABLE', $baselineRun->publishability_state);
        $this->assertSame(1, (int) $baselineRun->is_current_publication);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }

    public function test_run_daily_approved_correction_with_pointer_to_unsealed_non_current_publication_rejects_before_run_creation_and_preserves_approval_state(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-27', '2026-03-19', 1, 100.0, 1000);
        $this->seedMalformedBaselinePointerForTradeDate('2026-03-20', 1, 120.0, 'UNSEALED', 0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 134,
            'high' => 139,
            'low' => 133,
            'close' => 138,
            'volume' => 2900,
            'adj_close' => 138,
            'captured_at' => '2026-03-20T17:25:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute-with-pointer-to-unsealed-baseline', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        try {
            $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);
            $this->fail('Expected malformed baseline pointer/current publication state to reject approved correction before run creation.');
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.',
                $e->getMessage()
            );
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->where('run_id', '!=', 90)
            ->orderByDesc('run_id')
            ->first();

        $this->assertNull($run);

        $persistedCorrection = DB::table('eod_dataset_corrections')
            ->where('correction_id', $approved->correction_id)
            ->first();

        $this->assertNotNull($persistedCorrection);
        $this->assertSame('APPROVED', $persistedCorrection->status);
        $this->assertNull($persistedCorrection->prior_run_id);
        $this->assertNull($persistedCorrection->new_run_id);
        $this->assertNull($persistedCorrection->published_at);
        $this->assertNull($persistedCorrection->final_outcome_note);

        $publication = DB::table('eod_publications')
            ->where('trade_date', '2026-03-20')
            ->where('publication_id', 1)
            ->first();

        $this->assertNotNull($publication);
        $this->assertSame('UNSEALED', $publication->seal_state);
        $this->assertSame(0, (int) $publication->is_current);

        $pointer = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '2026-03-20')
            ->first();

        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);
        $this->assertSame(90, (int) $pointer->run_id);

        $this->assertSame(
            1,
            (int) DB::table('eod_publications')
                ->where('trade_date', '2026-03-20')
                ->count()
        );

        $this->assertSame(
            0,
            DB::table('eod_run_events')
                ->where('payload_json', 'like', '%"correction_id":'.$approved->correction_id.'%')
                ->count()
        );
    }



    public function test_manual_file_import_only_writes_candidate_bars_without_finalize_or_pointer_switch(): void
    {
        $this->seedTicker(1, 'BBCA');

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $run = $this->makePipeline()->importDaily('2026-03-20', 'manual_file');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();

        $this->assertSame('INGEST_BARS', $run->stage);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertNull($run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertNull($run->coverage_gate_state);
        $this->assertNull($run->bars_batch_hash);
        $this->assertNull($run->sealed_at);
        $this->assertSame('IMPORT_ONLY_COMPLETED_NOT_PROMOTED', $run->final_reason_code);
        $this->assertNotNull($run->finished_at);
        $this->assertSame(1, (int) $run->bars_rows_written);

        $this->assertNotNull($publication);
        $this->assertSame('UNSEALED', $publication->seal_state);
        $this->assertSame(0, (int) $publication->is_current);
        $this->assertNull($publication->sealed_at);

        $this->assertSame(1, DB::table('eod_bars')->where('run_id', $run->run_id)->count());
        $this->assertSame(0, DB::table('eod_indicators')->where('run_id', $run->run_id)->count());
        $this->assertSame(0, DB::table('eod_eligibility')->where('run_id', $run->run_id)->count());
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());

        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('stage', 'INGEST_BARS')
                ->where('event_type', 'STAGE_COMPLETED')
                ->exists()
        );

        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('stage', 'INGEST_BARS')
                ->where('event_type', 'IMPORT_ONLY_COMPLETED_NOT_PROMOTED')
                ->where('reason_code', 'IMPORT_ONLY_COMPLETED_NOT_PROMOTED')
                ->exists()
        );

        $this->assertFalse(
            DB::table('eod_run_events')
                ->where('run_id', $run->run_id)
                ->where('event_type', 'RUN_FINALIZED')
                ->exists()
        );
    }

    public function test_manual_file_promote_from_imported_partial_dataset_enforces_coverage_gate_and_does_not_switch_pointer(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedTicker(2, 'BBRI');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 2, 80.0, 900);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $pipeline = $this->makePipeline();
        $importRun = $pipeline->importDaily('2026-03-20', 'manual_file');
        $promoteRun = $pipeline->promoteDaily('2026-03-20', 'manual_file', $importRun->run_id);
        $event = DB::table('eod_run_events')
            ->where('run_id', $promoteRun->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->first();
        $payload = json_decode((string) $event->event_payload_json, true);
        $candidate = DB::table('eod_publications')->where('run_id', $promoteRun->run_id)->first();

        $this->assertSame('FAILED', $promoteRun->terminal_status);
        $this->assertSame('NOT_READABLE', $promoteRun->publishability_state);
        $this->assertSame('FAIL', $promoteRun->coverage_gate_state);
        $this->assertSame('full_publish', $promoteRun->promote_mode);
        $this->assertSame('current_replace', $promoteRun->publish_target);
        $this->assertSame(2, (int) $promoteRun->coverage_universe_count);
        $this->assertSame(1, (int) $promoteRun->coverage_available_count);
        $this->assertSame(1, (int) $promoteRun->coverage_missing_count);
        $this->assertSame('RUN_PARTIAL_DATA', $event->reason_code);
        $this->assertSame('RUN_PARTIAL_DATA', $payload['coverage_reason_code']);
        $this->assertSame(2, $payload['coverage_universe_count']);
        $this->assertSame(1, $payload['coverage_available_count']);
        $this->assertSame(1, $payload['coverage_missing_count']);

        $this->assertNotNull($candidate);
        $this->assertSame(0, (int) $candidate->is_current);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());
        $this->assertSame(0, DB::table('eod_publications')->where('trade_date', '2026-03-20')->where('is_current', 1)->count());
    }

    public function test_run_daily_full_coverage_persists_finalize_coverage_payload_and_readable_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedTicker(2, 'BBRI');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 2, 80.0, 900);

        $this->writeBarsFixture('2026-03-20', [
            [
                'ticker_code' => 'BBCA',
                'trade_date' => '2026-03-20',
                'open' => 121,
                'high' => 125,
                'low' => 120,
                'close' => 124,
                'volume' => 2000,
                'adj_close' => 124,
                'captured_at' => '2026-03-20T17:20:00+07:00',
            ],
            [
                'ticker_code' => 'BBRI',
                'trade_date' => '2026-03-20',
                'open' => 91,
                'high' => 94,
                'low' => 90,
                'close' => 93,
                'volume' => 1800,
                'adj_close' => 93,
                'captured_at' => '2026-03-20T17:21:00+07:00',
            ],
        ]);

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');
        $event = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->first();
        $payload = json_decode((string) $event->event_payload_json, true);

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame('PASS', $run->coverage_gate_state);
        $this->assertSame(2, (int) $run->coverage_available_count);
        $this->assertSame(2, (int) $run->coverage_universe_count);
        $this->assertSame(0, (int) $run->coverage_missing_count);
        $this->assertSame('coverage_gate_v1', (string) $run->coverage_contract_version);
        $this->assertNotNull($event);
        $this->assertNull($event->reason_code);
        $this->assertSame('PASS', $payload['coverage_gate_state']);
        $this->assertSame('COVERAGE_THRESHOLD_MET', $payload['coverage_reason_code']);
        $this->assertSame(2, $payload['coverage_available_count']);
        $this->assertSame(2, $payload['coverage_universe_count']);
        $this->assertSame(0, $payload['coverage_missing_count']);
        $this->assertSame('coverage_gate_v1', $payload['coverage_contract_version']);
        $this->assertSame('2026-03-20', $payload['trade_date_effective']);
    }

    public function test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedTicker(2, 'BBRI');
        $this->seedHistoricalBars('2026-02-27', '2026-03-18', 1, 100.0, 1000);
        $this->seedHistoricalBars('2026-02-27', '2026-03-18', 2, 80.0, 900);
        $this->seedReadableFallbackPublication('2026-03-19', 80, 11);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');
        $event = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->first();
        $payload = json_decode((string) $event->event_payload_json, true);

        $this->assertSame('HELD', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('FAIL', $run->quality_gate_state);
        $this->assertSame('FAIL', $run->coverage_gate_state);
        $this->assertSame('2026-03-19', $run->trade_date_effective);
        $this->assertSame('RUN_PARTIAL_DATA', $event->reason_code);
        $this->assertSame('RUN_PARTIAL_DATA', $payload['coverage_reason_code']);
        $this->assertSame(1, $payload['coverage_available_count']);
        $this->assertSame(2, $payload['coverage_universe_count']);
        $this->assertSame(1, $payload['coverage_missing_count']);
        $this->assertSame(11, (int) $payload['fallback_publication_id']);
        $this->assertSame('2026-03-19', $payload['fallback_trade_date']);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());

        $fallbackPointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-19')->first();
        $fallbackPublication = DB::table('eod_publications')->where('publication_id', 11)->first();
        $fallbackRun = DB::table('eod_runs')->where('run_id', 80)->first();

        $this->assertNotNull($fallbackPointer);
        $this->assertSame(11, (int) $fallbackPointer->publication_id);
        $this->assertNotNull($fallbackPublication);
        $this->assertSame(1, (int) $fallbackPublication->is_current);
        $this->assertNotNull($fallbackRun);
        $this->assertSame('SUCCESS', $fallbackRun->terminal_status);
        $this->assertSame('READABLE', $fallbackRun->publishability_state);
        $this->assertSame(1, (int) $fallbackRun->is_current_publication);
    }

    public function test_run_daily_low_coverage_without_fallback_finishes_not_readable_and_emits_coverage_reason_code(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedTicker(2, 'BBRI');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 2, 80.0, 900);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 121,
            'high' => 125,
            'low' => 120,
            'close' => 124,
            'volume' => 2000,
            'adj_close' => 124,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');
        $event = DB::table('eod_run_events')
            ->where('run_id', $run->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->first();
        $payload = json_decode((string) $event->event_payload_json, true);
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();

        $this->assertSame('FAILED', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame('FAIL', $run->quality_gate_state);
        $this->assertSame('FAIL', $run->coverage_gate_state);
        $this->assertNull($run->trade_date_effective);
        $this->assertSame('RUN_PARTIAL_DATA', $event->reason_code);
        $this->assertSame('RUN_PARTIAL_DATA', $payload['coverage_reason_code']);
        $this->assertSame(1, $payload['coverage_available_count']);
        $this->assertSame(2, $payload['coverage_universe_count']);
        $this->assertSame(1, $payload['coverage_missing_count']);
        $this->assertNotNull($publication);
        $this->assertSame(0, (int) $publication->is_current);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());
    }

    public function test_finalize_blocked_without_universe_stays_not_readable_and_emits_blocked_coverage_reason_code(): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 55,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => null,
            'lifecycle_state' => 'RUNNING',
            'terminal_status' => null,
            'quality_gate_state' => 'PENDING',
            'publishability_state' => 'NOT_READABLE',
            'stage' => 'SEAL',
            'source' => 'manual_file',
            'coverage_universe_count' => 0,
            'coverage_available_count' => 0,
            'coverage_missing_count' => 0,
            'coverage_ratio' => null,
            'coverage_min_threshold' => '0.9800',
            'coverage_gate_state' => 'NOT_EVALUABLE',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ticker_master_active_on_trade_date',
            'coverage_contract_version' => 'coverage_gate_v1',
            'knowledge_cutoff_at' => '2026-03-24 23:00:00',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'not-evaluable-finalize',
            'bars_batch_hash' => 'bars-ne',
            'indicators_batch_hash' => 'ind-ne',
            'eligibility_batch_hash' => 'elig-ne',
            'config_version' => 'v1',
            'publication_version' => 1,
            'is_current_publication' => 0,
            'sealed_at' => '2026-03-24 23:06:08',
            'sealed_by' => 'system',
            'seal_note' => 'not-evaluable-finalize',
            'started_at' => '2026-03-24 23:00:00',
            'finished_at' => null,
            'created_at' => '2026-03-24 23:00:00',
            'updated_at' => '2026-03-24 23:06:08',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 21,
            'trade_date' => '2026-03-20',
            'run_id' => 55,
            'publication_version' => 1,
            'is_current' => 0,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-ne',
            'indicators_batch_hash' => 'ind-ne',
            'eligibility_batch_hash' => 'elig-ne',
            'sealed_at' => '2026-03-24 23:06:08',
            'created_at' => '2026-03-24 23:06:08',
            'updated_at' => '2026-03-24 23:06:08',
        ]);

        $finalizedRun = $this->makePipeline()->completeFinalize(
            new App\Application\MarketData\DTOs\MarketDataStageInput('2026-03-20', 'manual_file', 55, 'FINALIZE', null)
        );

        $event = DB::table('eod_run_events')
            ->where('run_id', $finalizedRun->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->first();
        $payload = json_decode((string) $event->event_payload_json, true);
        $publication = DB::table('eod_publications')->where('publication_id', 21)->first();

        $this->assertSame('FAILED', $finalizedRun->terminal_status);
        $this->assertSame('NOT_READABLE', $finalizedRun->publishability_state);
        $this->assertSame('BLOCKED', $finalizedRun->quality_gate_state);
        $this->assertSame('NOT_EVALUABLE', $finalizedRun->coverage_gate_state);
        $this->assertNull($finalizedRun->trade_date_effective);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $event->reason_code);
        $this->assertSame('RUN_COVERAGE_NOT_EVALUABLE', $payload['coverage_reason_code']);
        $this->assertSame(0, $payload['coverage_universe_count']);
        $this->assertSame(0, $payload['coverage_available_count']);
        $this->assertSame(0, $payload['coverage_missing_count']);

        $this->assertSame('coverage_gate_v1', $payload['coverage_contract_version']);
        $this->assertSame(0, (int) $publication->is_current);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());
    }

    /**
     * F-MD-B18-A002-020 resolved: a repair_candidate promote derives its run via
     * createPromoteRunFromSeed, which never re-executes INGEST_BARS/ACQUISITION, so it can never
     * capture its own provider_mapping/source_observations -- there is no new acquisition event.
     * ProducerInputCompletionManifest now proves those two domains satisfied by an immutable
     * reference to the seed run's own already-captured, already-verified rows (never a copy, never
     * a recompute, never current/latest state), so the promote can reach the SUCCESS/SEALED
     * lifecycle LOCKED authority (Finalize_Lock_And_Pointer_Behavior_LOCKED.md Sec3) requires.
     */
    public function test_promote_single_day_repair_candidate_completes_via_referenced_seed_run_capture(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 131,
            'high' => 135,
            'low' => 130,
            'close' => 134,
            'volume' => 2400,
            'adj_close' => 134,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'repair-first-pass', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $pipeline = $this->makePipeline();
        $importRun = $pipeline->importSingleDay('2026-03-20', 'manual_file');
        $repairRun = $pipeline->promoteSingleDay('2026-03-20', 'manual_file', $importRun->run_id, $approved->correction_id, 'repair_candidate');

        $correctionRow = DB::table('eod_dataset_corrections')->where('correction_id', $approved->correction_id)->first();
        $candidatePublication = DB::table('eod_publications')->where('run_id', $repairRun->run_id)->first();
        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $finalizedEvent = DB::table('eod_run_events')
            ->where('run_id', $repairRun->run_id)
            ->where('event_type', 'RUN_FINALIZED')
            ->first();
        $payload = json_decode((string) $finalizedEvent->event_payload_json, true);

        $this->assertNotSame((int) $importRun->run_id, (int) $repairRun->run_id, 'The derived promote run must be a distinct run from its seed.');
        $this->assertSame('SUCCESS', $repairRun->terminal_status, 'LOCKED authority requires this scenario reach SUCCESS or HELD, never be unconditionally blocked.');
        $this->assertSame('NOT_READABLE', $repairRun->publishability_state);
        $this->assertSame('repair_candidate', $repairRun->promote_mode);
        $this->assertSame('repair_candidate', $repairRun->publish_target);
        $this->assertSame('REPAIR_EXECUTED', $correctionRow->status);
        $this->assertSame(1, (int) $correctionRow->execution_count);
        $this->assertSame((int) $repairRun->run_id, (int) $correctionRow->new_run_id);
        $this->assertNotNull($correctionRow->last_executed_at);
        $this->assertNull($correctionRow->current_consumed_at);
        $this->assertNotNull($candidatePublication);
        $this->assertSame(0, (int) $candidatePublication->is_current, 'Current pointer preserved -- no in-place content repair implied.');
        $this->assertSame('SEALED', (string) $candidatePublication->seal_state);
        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id, 'The pre-existing current publication must be untouched.');
        $this->assertSame('RUN_REPAIR_CANDIDATE_PARTIAL', $finalizedEvent->reason_code);
        $this->assertSame('REPAIR_CANDIDATE', $payload['correction_outcome']);

        // Binding V2 completed too (not merely V1) -- the Seal precondition wired in E038 is
        // satisfied, and satisfied honestly, not by weakening it.
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $candidatePublication->publication_id)->first();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $lineage->bound_input_context_hash);

        // Provenance: the bound bundle's provider_mapping/source_observations components must name
        // the seed run as their true source, never the derived run pretending to have captured them
        // itself, and every other component must still be tagged as the derived run's own.
        $context = json_decode((string) $lineage->bound_input_context_json, true);
        $referencedKeys = ['provider_mapping', 'source_observations'];
        $sawReferenced = ['provider_mapping' => false, 'source_observations' => false];
        foreach ($context['components'] as $component) {
            if (in_array($component['component_key'], $referencedKeys, true)) {
                $this->assertSame((int) $importRun->run_id, (int) $component['source_run_id'],
                    $component['component_key'].' must be provenance-tagged to the exact seed run, not the derived run or any other.');
                $sawReferenced[$component['component_key']] = true;
            } else {
                $this->assertSame((int) $repairRun->run_id, (int) $component['source_run_id'],
                    $component['component_key'].' was actually captured by the derived run itself and must be tagged as such.');
            }
        }
        $this->assertTrue($sawReferenced['provider_mapping'], 'Expected at least one referenced provider_mapping component.');
        $this->assertTrue($sawReferenced['source_observations'], 'Expected at least one referenced source_observations component.');

        // Binding V2 remains deterministic/idempotent with referenced components in the bundle:
        // re-binding the same, already-sealed publication is a safe no-op with the identical hash.
        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        $again = $binder->bind($this->hydrateRunForRebind($repairRun->run_id), (int) $candidatePublication->publication_id, '2026-03-20');
        $this->assertTrue($again['idempotent']);
        $this->assertSame($lineage->bound_input_context_hash, $again['bound_input_context_hash']);
    }

    /**
     * Current/latest database state must never be read to fill in what a derived run inherits: the
     * seed run's own immutable capture content is what gets referenced, unaffected by whatever the
     * live reference tables look like by the time the repair runs.
     */
    public function test_repair_candidate_referenced_capture_is_unaffected_by_later_live_data_changes(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 131, 'high' => 135, 'low' => 130, 'close' => 134,
            'volume' => 2400, 'adj_close' => 134, 'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'repair-live-data-immunity', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $pipeline = $this->makePipeline();
        $importRun = $pipeline->importSingleDay('2026-03-20', 'manual_file');

        // Capture exactly what the seed run's own provider_mapping/source_observations rows contain
        // before anything downstream runs.
        $seedCapturesBefore = (new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository())->forRun((int) $importRun->run_id);
        $seedProviderMappingHashesBefore = array_values(array_unique(array_map(function ($row) {
            return $row['payload_hash'];
        }, array_filter($seedCapturesBefore, function ($row) { return $row['component_key'] === 'provider_mapping'; }))));
        $this->assertNotEmpty($seedProviderMappingHashesBefore);

        // Mutate the live reference tables the original capture was drawn from, after capture.
        DB::table('md_provider_symbol_mappings')->update(['provider_symbol' => 'MUTATED_AFTER_CAPTURE']);

        $repairRun = $pipeline->promoteSingleDay('2026-03-20', 'manual_file', $importRun->run_id, $approved->correction_id, 'repair_candidate');
        $this->assertSame('SUCCESS', $repairRun->terminal_status);

        $candidatePublication = DB::table('eod_publications')->where('run_id', $repairRun->run_id)->first();
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $candidatePublication->publication_id)->first();
        $context = json_decode((string) $lineage->bound_input_context_json, true);
        $referencedProviderMappingHashes = array_values(array_unique(array_map(function ($c) {
            return $c['payload_hash'];
        }, array_filter($context['components'], function ($c) { return $c['component_key'] === 'provider_mapping'; }))));

        sort($seedProviderMappingHashesBefore); sort($referencedProviderMappingHashes);
        $this->assertSame($seedProviderMappingHashesBefore, $referencedProviderMappingHashes,
            'The referenced provider_mapping content must be byte-identical to what the seed run actually captured, unaffected by the later live-table mutation.');
    }

    /**
     * A tampered or vanished seed-run capture must still fail closed -- the reference is proven
     * fresh each time by re-verifying the seed run's own recorded content, not merely assumed
     * present because it once was.
     */
    /**
     * `md_run_input_captures` is unconditionally append-only (the C1 foundation migration's own
     * immutability trigger rejects any UPDATE/DELETE outright, proven directly here rather than
     * assumed), so an already-written seed capture can never be corrupted in place -- confirming
     * the reference this mechanism relies on is tamper-proof by construction, not merely by
     * convention. What a broken/incomplete reference can still look like is a `seed_run_id` that
     * resolves to no run at all, or to a real run whose own captures do not actually satisfy
     * C03/C06 -- both are proven to fail closed below, without needing to defeat that immutability.
     */
    public function test_repair_candidate_fails_closed_when_the_referenced_seed_run_cannot_prove_the_domain(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 131, 'high' => 135, 'low' => 130, 'close' => 134,
            'volume' => 2400, 'adj_close' => 134, 'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'repair-tampered-reference', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $pipeline = $this->makePipeline();
        $importRun = $pipeline->importSingleDay('2026-03-20', 'manual_file');

        // Confirm the immutability guarantee this whole mechanism leans on is real, not assumed:
        // direct SQL tampering with an already-written seed capture is itself rejected by the
        // database.
        try {
            DB::table('md_run_input_captures')
                ->where('run_id', $importRun->run_id)
                ->where('component_key', 'provider_mapping')
                ->update(['payload_hash' => str_repeat('f', 64)]);
            $this->fail('Direct SQL tampering of an immutable producer capture was accepted by the database.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_IMMUTABLE', $e->getMessage());
        }

        $repairRun = $pipeline->promoteSingleDay('2026-03-20', 'manual_file', $importRun->run_id, $approved->correction_id, 'repair_candidate');
        $this->assertSame('SUCCESS', $repairRun->terminal_status, 'The untampered seed capture must still let repair_candidate complete normally.');

        $repository = new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();
        $validator = new \App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest();

        // A recorded seed_run_id that resolves to no run at all must fail closed.
        $bareRunA = $this->createBareRunWithSeedLink('2026-03-21', 999999999);
        $resultA = $validator->inspect(\App\Models\EodRun::query()->findOrFail($bareRunA), $repository);
        $missingA = array_values(array_filter($resultA['missing_paths'], function ($p) {
            return strpos($p, 'provider_mapping.') === 0 || strpos($p, 'source_observations.') === 0;
        }));
        $this->assertNotEmpty($missingA, 'An unresolvable seed_run_id must leave provider_mapping/source_observations genuinely missing.');

        // A recorded seed_run_id that resolves to a real run whose own captures do not satisfy
        // C03/C06 (e.g. it never ingested anything itself) must also fail closed, not be treated as
        // satisfied just because *some* run exists at that id.
        $emptyRunId = $this->createBareRunWithSeedLink('2026-03-22', null);
        $bareRunB = $this->createBareRunWithSeedLink('2026-03-23', $emptyRunId);
        $resultB = $validator->inspect(\App\Models\EodRun::query()->findOrFail($bareRunB), $repository);
        $missingB = array_values(array_filter($resultB['missing_paths'], function ($p) {
            return strpos($p, 'provider_mapping.') === 0 || strpos($p, 'source_observations.') === 0;
        }));
        $this->assertNotEmpty($missingB, 'A seed run that itself never captured provider_mapping/source_observations cannot satisfy the reference.');

        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $this->assertSame(1, (int) $pointer->publication_id);
    }

    /** A minimal, otherwise-real run row with a RUN_CREATED event carrying an explicit seed_run_id. */
    private function createBareRunWithSeedLink(string $tradeDate, ?int $seedRunId): int
    {
        $now = '2026-01-01 00:00:00';
        $runId = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => $tradeDate,
            'source' => 'manual_file',
            'request_mode' => 'promote',
            'knowledge_cutoff_at' => $now,
            'lifecycle_state' => 'PENDING',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('eod_run_events')->insert([
            'run_id' => $runId,
            'trade_date_requested' => $tradeDate,
            'event_time' => $now,
            'stage' => 'PUBLISH_BARS',
            'event_type' => 'RUN_CREATED',
            'severity' => 'INFO',
            'event_payload_json' => json_encode(['run_id' => $runId, 'seed_run_id' => $seedRunId]),
            'created_at' => $now,
        ]);
        return (int) $runId;
    }

    private function hydrateRunForRebind(int $runId)
    {
        return \App\Models\EodRun::query()->findOrFail($runId);
    }

    /**
     * F-MD-B18-A002-020 resolved: `incremental` is a literal internal alias for `repair_candidate`
     * (`MarketDataPipelineService::resolvePromoteContext()` maps `'incremental' => 'repair_candidate'`
     * before any other logic runs), so once repair_candidate can complete via referenced seed-run
     * capture, incremental must complete identically -- same lifecycle, same reference mechanism,
     * proven by actually rerunning the repair-and-correction lifecycle a second time.
     */
    public function test_promote_single_day_incremental_completes_identically_to_repair_candidate(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->seedCurrentPublicationBaselineForTradeDate('2026-03-20', 1, 120.0);

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 141,
            'high' => 146,
            'low' => 140,
            'close' => 145,
            'volume' => 2600,
            'adj_close' => 145,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'repair-rerun', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $pipeline = $this->makePipeline();
        $firstImportRun = $pipeline->importSingleDay('2026-03-20', 'manual_file');
        $firstRepairRun = $pipeline->promoteSingleDay('2026-03-20', 'manual_file', $firstImportRun->run_id, $approved->correction_id, 'repair_candidate');

        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-03-20',
            'open' => 143,
            'high' => 148,
            'low' => 142,
            'close' => 147,
            'volume' => 2800,
            'adj_close' => 147,
            'captured_at' => '2026-03-20T17:30:00+07:00',
        ]]);

        $secondImportRun = $pipeline->importSingleDay('2026-03-20', 'manual_file');
        // 'incremental' resolves to 'repair_candidate' before any other logic runs
        // (resolvePromoteContext()); this is the alias spelling, not a different lifecycle.
        $secondRepairRun = $pipeline->promoteSingleDay('2026-03-20', 'manual_file', $secondImportRun->run_id, $approved->correction_id, 'incremental');

        $correctionRow = DB::table('eod_dataset_corrections')->where('correction_id', $approved->correction_id)->first();
        $firstCandidate = DB::table('eod_publications')->where('run_id', $firstRepairRun->run_id)->first();
        $secondCandidate = DB::table('eod_publications')->where('run_id', $secondRepairRun->run_id)->first();
        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();

        $this->assertSame('SUCCESS', $firstRepairRun->terminal_status);
        $this->assertSame('SUCCESS', $secondRepairRun->terminal_status);
        $this->assertSame('NOT_READABLE', $secondRepairRun->publishability_state);
        // Recorded promote_mode/publish_target are normalized through the same alias resolution
        // regardless of which name was requested -- both runs read back as repair_candidate.
        $this->assertSame('repair_candidate', $secondRepairRun->promote_mode);
        $this->assertSame('repair_candidate', $secondRepairRun->publish_target);
        $this->assertSame('REPAIR_EXECUTED', $correctionRow->status);
        $this->assertSame(2, (int) $correctionRow->execution_count);
        $this->assertSame((int) $secondRepairRun->run_id, (int) $correctionRow->new_run_id);
        $this->assertNotNull($correctionRow->last_executed_at);
        $this->assertNull($correctionRow->current_consumed_at);
        $this->assertNotNull($firstCandidate);
        $this->assertNotNull($secondCandidate);
        $this->assertSame(0, (int) $firstCandidate->is_current);
        $this->assertSame(0, (int) $secondCandidate->is_current);
        $this->assertSame('SEALED', (string) $firstCandidate->seal_state);
        $this->assertSame('SEALED', (string) $secondCandidate->seal_state);
        $this->assertNotSame((int) $firstRepairRun->run_id, (int) $secondRepairRun->run_id);
        $this->assertNotNull($pointer);
        $this->assertSame(1, (int) $pointer->publication_id);

        // Both candidates bound V2 successfully, each referencing its own distinct seed run.
        $firstLineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $firstCandidate->publication_id)->first();
        $secondLineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $secondCandidate->publication_id)->first();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $firstLineage->bound_input_context_hash);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $secondLineage->bound_input_context_hash);
        $secondContext = json_decode((string) $secondLineage->bound_input_context_json, true);
        foreach ($secondContext['components'] as $component) {
            if ($component['component_key'] === 'provider_mapping' || $component['component_key'] === 'source_observations') {
                $this->assertSame((int) $secondImportRun->run_id, (int) $component['source_run_id'],
                    'The second (incremental) candidate must reference its own second import as its seed, not the first.');
            }
        }
    }

    private function capturedOperation(array $captures, string $operation): array
    {
        foreach ($captures as $capture) {
            $payload = json_decode($capture['semantic_payload_json'], true);
            if ($payload['selection_context']['operation'] === $operation) return $payload;
        }
        $this->fail('Producer omitted required capture operation '.$operation);
    }

    private function capturedPopulation(array $captures, array $reference): array
    {
        foreach ($captures as $capture) if ($capture['slot_hash'] === $reference['slot_hash'] && $capture['stage_code'] === $reference['stage_code']) {
            $this->assertSame($capture['payload_hash'], $reference['payload_hash']);
            return (new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository())->verify($capture)['rows'][0];
        }
        $this->fail('The consumed temporal population must resolve by immutable capture identity.');
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$item;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
                continue;
            }

            @unlink($fullPath);
        }

        @rmdir($path);
    }

    private function makeBackfillService(): MarketDataBackfillService
    {
        return new MarketDataBackfillService(new App\Infrastructure\Persistence\MarketData\MarketCalendarRepository(), $this->makePipeline());
    }

    private function makeBackfillServiceWithApiFetcher(callable $fetcher): MarketDataBackfillService
    {
        return new MarketDataBackfillService(new App\Infrastructure\Persistence\MarketData\MarketCalendarRepository(), $this->makePipelineWithApiFetcher($fetcher));
    }

    public function test_stale_running_import_only_run_is_cancelled_before_new_owning_run_is_created(): void
    {
        config()->set('market_data.pipeline.active_run_stale_minutes', 60);
        $old = Carbon::now(config('market_data.platform.timezone'))->subHours(2);

        DB::table('eod_runs')->insert([
            'run_id' => 901,
            'trade_date_requested' => '2026-03-21',
            'trade_date_effective' => null,
            'lifecycle_state' => 'RUNNING',
            'terminal_status' => null,
            'quality_gate_state' => 'PENDING',
            'publishability_state' => 'NOT_READABLE',
            'stage' => 'INGEST_BARS',
            'source' => 'manual_file',
            'request_mode' => 'import_only',
            'source_name' => null,
            'source_provider' => null,
            'source_input_file' => null,
            'source_timeout_seconds' => null,
            'source_retry_max' => null,
            'source_attempt_count' => null,
            'source_success_after_retry' => null,
            'source_retry_exhausted' => null,
            'source_final_http_status' => null,
            'source_final_reason_code' => null,
            'source_file_hash' => null,
            'source_file_hash_algorithm' => null,
            'source_file_size_bytes' => null,
            'source_file_row_count' => null,
            'coverage_universe_count' => null,
            'coverage_available_count' => null,
            'coverage_missing_count' => null,
            'coverage_ratio' => null,
            'coverage_min_threshold' => null,
            'coverage_gate_state' => null,
            'coverage_threshold_mode' => null,
            'coverage_universe_basis' => null,
            'coverage_contract_version' => null,
            'coverage_missing_sample_json' => null,
            'bars_rows_written' => null,
            'indicators_rows_written' => null,
            'eligibility_rows_written' => null,
            'invalid_bar_count' => null,
            'invalid_indicator_count' => null,
            'hard_reject_count' => null,
            'warning_count' => null,
            'notes' => 'request_mode=import_only',
            'bars_batch_hash' => null,
            'indicators_batch_hash' => null,
            'eligibility_batch_hash' => null,
            'config_version' => 'v1',
            'config_hash' => null,
            'config_snapshot_ref' => null,
            'supersedes_run_id' => null,
            'publication_id' => null,
            'publication_version' => null,
            'is_current_publication' => 0,
            'correction_id' => null,
            'promote_mode' => null,
            'publish_target' => null,
            'final_reason_code' => null,
            'sealed_at' => null,
            'sealed_by' => null,
            'seal_note' => null,
            'started_at' => $old,
            'finished_at' => null,
            'created_at' => $old,
            'updated_at' => $old,
        ]);

        $repository = new EodRunRepository();
        $newRun = $repository->getOrCreateOwningRun('2026-03-21', 'manual_file', 'INGEST_BARS', null, 'import_only');
        $staleRun = DB::table('eod_runs')->where('run_id', 901)->first();

        $this->assertNotSame(901, (int) $newRun->run_id);
        $this->assertSame('PENDING', $newRun->lifecycle_state);
        $this->assertSame('CANCELLED', $staleRun->lifecycle_state);
        $this->assertNull($staleRun->terminal_status);
        $this->assertSame('NOT_READABLE', $staleRun->publishability_state);
        $this->assertSame('STALE_ACTIVE_RUN_CANCELLED', $staleRun->final_reason_code);
        $this->assertNotNull($staleRun->finished_at);
        $this->assertTrue(
            DB::table('eod_run_events')
                ->where('run_id', 901)
                ->where('event_type', 'STALE_ACTIVE_RUN_CANCELLED')
                ->where('reason_code', 'STALE_ACTIVE_RUN_CANCELLED')
                ->exists()
        );
    }

    public function test_ancillary_capture_proves_real_sector_and_event_risk_selection_and_is_named_by_the_manifest_on_corruption(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20',
            'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $pipeline = $this->makePipelineWithAncillaryEngaged();
        $run = $pipeline->runDaily('2026-03-20', 'manual_file');

        $repository = new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();
        $captures = $repository->forRun((int) $run->run_id);
        $indicatorAncillary = null;
        foreach ($captures as $capture) {
            $payload = $repository->verify($capture);
            if ($payload['selection_context']['operation'] !== 'ancillary-source-revisions/v1') continue;
            if ($payload['selection_context']['domain'] !== 'indicator_dependencies') continue;
            $indicatorAncillary = ['capture' => $capture, 'payload' => $payload];
        }
        $this->assertNotNull($indicatorAncillary, 'Expected an indicator_dependencies ancillary capture.');
        $consumed = $indicatorAncillary['payload']['rows'][0]['consumed'];
        $this->assertSame('G', $consumed['sector_contexts'][1]['sector_code']);
        $this->assertSame('RESOLVED_AUTHORITATIVE', $consumed['sector_contexts'][1]['resolution_state']);

        $validator = new \App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest();
        $result = $validator->inspect($run, $repository);
        $this->assertSame([], array_values(array_filter($result['missing_paths'], static function ($p) {
            return strpos($p, 'ancillary.') === 0;
        })), 'A valid real C09 capture must have no ancillary gap.');
        // Whole-C1, all optional producers engaged: the SQLite mirror's eod_reason_codes is now
        // seeded with the same canonical content as deployed MariaDB (MD-B18-A002 Binding
        // orchestration wiring closed the fixture gap E031-E033 found), so the manifest is
        // genuinely complete rather than BLOCKED on that one remaining pre-existing gap.
        $this->assertSame([], $result['missing_paths'],
            'Whole-C1 with every optional ancillary producer engaged and canonical reason codes seeded has zero remaining gaps.');

        // Mutation: damaging one consumed field must be independently caught, in memory only (the real
        // run is already sealed, so no post-seal capture write is attempted).
        $selection = $indicatorAncillary['payload']['selection_context'];
        $damaged = $indicatorAncillary['payload']['rows'][0];
        $damaged['consumed']['sector_contexts'][1]['sector_code'] = 'ZZ';
        try {
            \App\Infrastructure\Persistence\MarketData\ProducerAncillaryCapture::assertValid($damaged, $selection, (string) $selection['trade_date']);
            $this->fail('Damaged sector context accepted');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_ANCILLARY_SECTOR_CONTEXTS_MISMATCH', $e->getMessage());
        }
    }

    /** Runs the pipeline through HASH (V1 governance binding + every C1 capture) without sealing. */
    private function runPipelineThroughHashUnsealed(MarketDataPipelineService $pipeline, string $tradeDate, string $sourceMode = 'manual_file')
    {
        $run = null;
        foreach ([
            'INGEST_BARS' => ['completeIngest', 'full_publish'],
            'COMPUTE_INDICATORS' => ['completeIndicators', 'full_publish'],
            'BUILD_ELIGIBILITY' => ['completeEligibility', 'full_publish'],
            'HASH' => ['completeHash', 'full_publish'],
        ] as $stage => [$method, $requestMode]) {
            $input = new \App\Application\MarketData\DTOs\MarketDataStageInput(
                $tradeDate, $sourceMode, $run ? $run->run_id : null, $stage, null, false, null, $requestMode
            );
            $run = $pipeline->$method($input);
        }

        return $run;
    }

    /**
     * Runs the pipeline all the way through the real SEAL stage (`completeSeal`), which is what
     * actually prepares the deterministic publication manifest (`prepareCandidateManifestForSeal`)
     * before calling `sealCandidatePublication` -- unlike `runPipelineThroughHashUnsealed`, which
     * deliberately stops at HASH so Binding-only tests can inspect the unsealed state.
     */
    private function runPipelineThroughSealed(MarketDataPipelineService $pipeline, string $tradeDate, string $sourceMode = 'manual_file')
    {
        $run = $this->runPipelineThroughHashUnsealed($pipeline, $tradeDate, $sourceMode);
        $input = new \App\Application\MarketData\DTOs\MarketDataStageInput(
            $tradeDate, $sourceMode, $run->run_id, 'SEAL', null, false, null, 'full_publish'
        );
        return $pipeline->completeSeal($input);
    }

    public function test_binding_persists_v2_context_and_matches_v1_compatibility_hashes_before_seal(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20',
            'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);

        $pipeline = $this->makePipelineWithAncillaryEngaged();
        // Binding V2 is now wired directly into completeHash, on the same lifecycle as V1's
        // governance binding, so a genuinely complete manifest must already be bound by the time
        // the pipeline returns from HASH -- no separate manual bind() call is needed to produce it.
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertSame('UNSEALED', $publication->seal_state, 'Binding must be exercised before seal.');

        $repository = new \App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository();
        $validator = new \App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest();
        $manifest = $validator->inspect($run, $repository);
        $this->assertSame([], $manifest['missing_paths'], 'Precondition: the manifest must be genuinely complete before Binding is exercised.');

        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();
        $this->assertSame('md_publication_inputs_v2', $lineage->bound_input_schema_version, 'HASH must have bound V2 context automatically.');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $lineage->bound_input_context_hash);
        $context = json_decode($lineage->bound_input_context_json, true);
        $this->assertSame('md_publication_inputs_v2', $context['schema_version']);
        $this->assertNotEmpty($context['components']);
        $this->assertSame((int) $run->config_snapshot_id, $context['scope']['config_snapshot_id']);
        $manifestJson = json_decode($lineage->bound_input_capture_manifest_json, true);
        $this->assertNotEmpty($manifestJson['actual_components']);

        // A manual re-bind against the same candidate must independently re-derive the same
        // compatibility hashes V1's existing binder already computed via live queries -- proving
        // V1's hashes were actually consistent with the captured population, not merely
        // self-consistent with themselves -- and must be a safe, idempotent no-op.
        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        $result = $binder->bind($run, (int) $publication->publication_id, '2026-03-20');
        $this->assertTrue($result['idempotent'], 'HASH already bound this publication; a manual re-bind of identical content must be idempotent.');
        $this->assertSame($lineage->bound_input_context_hash, $result['bound_input_context_hash']);
        $this->assertNotEmpty($result['derived_compatibility_hashes']);
        foreach ($result['derived_compatibility_hashes'] as $field => $hash) {
            $this->assertSame($lineage->$field, $hash, $field.' must match the existing V1 compatibility hash.');
        }
        $this->assertArrayHasKey('market_structure_revision_set_hash', $result['derived_compatibility_hashes']);
        $this->assertArrayHasKey('identity_revision_set_hash', $result['derived_compatibility_hashes']);
        $this->assertArrayHasKey('calendar_revision_set_hash', $result['derived_compatibility_hashes']);
        $this->assertArrayHasKey('status_revision_set_hash', $result['derived_compatibility_hashes']);
        $this->assertArrayHasKey('event_revision_set_hash', $result['derived_compatibility_hashes']);
        $this->assertArrayHasKey('source_scale_assessment_set_hash', $result['derived_compatibility_hashes']);
        $this->assertArrayHasKey('factor_decision_set_hash', $result['derived_compatibility_hashes']);
    }

    public function test_binding_rejects_ownership_mismatch(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();

        try {
            $binder->bind($run, 999999, '2026-03-20');
            $this->fail('Binding a non-existent publication was accepted.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_PUBLICATION_NOT_FOUND', $e->getMessage());
        }

        $otherRun = (new \App\Infrastructure\Persistence\MarketData\EodRunRepository())->getOrCreateOwningRun('2026-03-21', 'manual_file', 'INGEST_BARS', null, 'binding-ownership-probe');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        try {
            $binder->bind($otherRun, (int) $publication->publication_id, '2026-03-20');
            $this->fail('Binding with a mismatched owning run was accepted.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_OWNERSHIP_MISMATCH', $e->getMessage());
        }
    }

    public function test_binding_rejects_historical_replay_verify_run(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        // Binding V2 is wired into completeHash, so this run already binds a real, legitimate
        // context while its request_mode is genuinely 'full_publish'.
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $legitimateHash = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->value('bound_input_context_hash');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $legitimateHash, 'Precondition: HASH must have already bound a real context.');
        $run->request_mode = 'replay_verify';

        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        try {
            $binder->bind($run, (int) $publication->publication_id, '2026-03-20');
            $this->fail('Historical binding fabricated a new authoritative context.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_HISTORICAL_NOT_PERMITTED', $e->getMessage());
        }
        // The already-legitimate context from the real run must be left exactly as it was --
        // a historical-mode attempt must neither fabricate a new one nor disturb the real one.
        $this->assertSame($legitimateHash, DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->value('bound_input_context_hash'));
    }

    public function test_binding_rejects_an_incomplete_capture_manifest(): void
    {
        $this->seedTicker(1, 'BBCA');
        // Deliberately emptying eod_reason_codes (normally seeded canonically by the shared SQLite
        // trait, matching real MariaDB) reproduces the one real, pre-existing whole-C1 gap
        // E032/E033 found (registry_versions.reason_registry.entries) as an incomplete-manifest
        // fixture for Binding, rather than inventing an artificial one.
        DB::table('eod_reason_codes')->delete();
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();

        // Binding V2 is now wired directly into completeHash (MarketDataPipelineService::completeHash),
        // on the same lifecycle as V1's governance binding, so an incomplete manifest must fail the
        // pipeline itself closed at the HASH stage, not merely a manual post-hoc bind() call.
        try {
            $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
            $this->fail('Pipeline HASH stage completed despite an incomplete producer-input capture manifest.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_MANIFEST_INCOMPLETE', $e->getMessage());
        }
        $runRow = DB::table('eod_runs')->where('trade_date_requested', '2026-03-20')->orderByDesc('run_id')->first();
        $publication = DB::table('eod_publications')->where('run_id', $runRow->run_id)->first();
        $this->assertNull(DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->value('bound_input_context_hash'));
    }

    public function test_binding_detects_a_compatibility_hash_inconsistent_with_the_captured_population(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        // Binding V2 is wired into completeHash, so this run already bound a legitimate context
        // against V1's (still-uncorrupted) compatibility hashes.
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $legitimateHash = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->value('bound_input_context_hash');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $legitimateHash, 'Precondition: HASH must have already bound a real context.');

        // Corrupt V1's already-stored compatibility hash, as if it had drifted from the true
        // captured population (the exact class of defect C1 exists to catch).
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['calendar_revision_set_hash' => str_repeat('a', 64)]);

        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        try {
            $binder->bind($run, (int) $publication->publication_id, '2026-03-20');
            $this->fail('A compatibility hash inconsistent with the captured population was accepted.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_COMPATIBILITY_HASH_MISMATCH', $e->getMessage());
            $this->assertStringContainsString('calendar_revision_set_hash', $e->getMessage());
        }
        // The rejected re-bind attempt must not disturb the already-legitimate context recorded
        // before the corruption.
        $this->assertSame($legitimateHash, DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->value('bound_input_context_hash'));
    }

    public function test_binding_rejects_a_conflicting_context_and_a_sealed_mismatch_but_allows_a_sealed_match(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        $result = $binder->bind($run, (int) $publication->publication_id, '2026-03-20');

        // A stale/foreign context recorded out of band must not be silently overwritten.
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['bound_input_context_hash' => str_repeat('b', 64)]);
        try {
            $binder->bind($run, (int) $publication->publication_id, '2026-03-20');
            $this->fail('A conflicting recorded bound input context was silently overwritten.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_CONFLICT', $e->getMessage());
        }

        // Restore the true hash, then seal: matching re-bind is a safe no-op.
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['bound_input_context_hash' => $result['bound_input_context_hash']]);
        DB::table('eod_publications')->where('publication_id', $publication->publication_id)->update(['seal_state' => 'SEALED']);
        $sealedMatch = $binder->bind($run, (int) $publication->publication_id, '2026-03-20');
        $this->assertTrue($sealedMatch['idempotent']);

        // Now sealed with the correct hash: the database itself rejects any further tampering,
        // even before the application layer gets a chance to compare anything.
        try {
            DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
                ->update(['bound_input_context_hash' => str_repeat('c', 64)]);
            $this->fail('Direct SQL tampering of a sealed bound input context was accepted by the database.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', $e->getMessage());
        }
        $this->assertSame($result['bound_input_context_hash'], DB::table('md_publication_lineage_bindings')
            ->where('publication_id', $publication->publication_id)->value('bound_input_context_hash'));
    }

    public function test_binding_detects_a_pre_seal_corrupted_context_once_sealed_and_the_database_then_protects_it(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        $binder->bind($run, (int) $publication->publication_id, '2026-03-20');

        // A wrong value baked in while still lawfully unsealed, then sealed -- the only way such a
        // row can exist now that the database rejects post-seal tampering directly.
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['bound_input_context_hash' => str_repeat('c', 64)]);
        DB::table('eod_publications')->where('publication_id', $publication->publication_id)->update(['seal_state' => 'SEALED']);

        try {
            $binder->bind($run, (int) $publication->publication_id, '2026-03-20');
            $this->fail('A sealed publication with a mismatched bound input context was treated as writable.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_PUBLICATION_IMMUTABLE', $e->getMessage());
        }

        // Defense in depth: even attempting to "fix" the now-sealed, already-wrong row via direct
        // SQL is itself rejected by the database, not just detected by the application.
        try {
            DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
                ->update(['bound_input_context_hash' => str_repeat('d', 64)]);
            $this->fail('Direct SQL tampering of an already-sealed, already-wrong row was accepted by the database.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE', $e->getMessage());
        }
    }

    /**
     * Orchestration proof: completeHash binds V2 automatically (already proven above by every
     * other Binding test's precondition assertion), and separately, sealCandidatePublication's own
     * bound_input_context_hash precondition rejects a candidate lacking it without itself
     * computing or creating one -- a real state a publication predating this wiring could carry.
     */
    public function test_seal_rejects_a_candidate_missing_its_v2_binding_without_computing_one(): void
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        // Binding V2 is wired into completeHash, so this run already bound a real V2 context.
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', DB::table('md_publication_lineage_bindings')
            ->where('publication_id', $publication->publication_id)->value('bound_input_context_hash'));

        // Simulate a publication that reached HASH before this wiring existed: its V1 lineage row
        // is complete, but it never carries a V2 bound input context.
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update([
                'bound_input_schema_version' => null, 'bound_input_context_json' => null,
                'bound_input_context_hash' => null, 'bound_input_capture_manifest_json' => null,
            ]);

        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())
                ->sealCandidatePublication($run, 'operator');
            $this->fail('A candidate publication missing its V2 bound input context was sealed.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('DATASET_HASH_MISSING', $e->getMessage());
            $this->assertStringContainsString('producer-bound input context', $e->getMessage());
        }

        // The precondition must only have checked, never computed or created one: it is still null,
        // and the publication never reached SEALED.
        $lineageAfter = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();
        $this->assertNull($lineageAfter->bound_input_context_hash);
        $this->assertNull($lineageAfter->bound_input_schema_version);
        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')->where('publication_id', $publication->publication_id)->value('seal_state'));
    }

    /** A run/publication pair with a genuine, real V2 binding, HASH-completed but not yet sealed. */
    private function makeSealReadyPublication(): array
    {
        $this->seedTicker(1, 'BBCA');
        $this->seedProducerBoundHistoricalBars('2026-02-28', '2026-03-19', 1, 100.0, 1000);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        DB::table('ticker_sector_memberships')->insert([
            'ticker_id' => 1, 'listing_id' => $listingId, 'sector_code' => 'G', 'classification_system' => 'IDX-IC',
            'effective_from' => '2020-01-01', 'effective_to' => null, 'source_name' => 'idx', 'source_ref' => 'idx-membership-ref',
            'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $this->writeBarsFixture('2026-03-20', [[
            'ticker_code' => 'BBCA', 'trade_date' => '2026-03-20', 'open' => 121, 'high' => 125, 'low' => 120, 'close' => 124, 'volume' => 2000,
            'captured_at' => '2026-03-20T17:20:00+07:00',
        ]]);
        $pipeline = $this->makePipelineWithAncillaryEngaged();
        $run = $this->runPipelineThroughHashUnsealed($pipeline, '2026-03-20');
        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        return [$run, $publication, $pipeline];
    }

    /**
     * Reaches seal through the real pipeline stage (`completeSeal`), which prepares the
     * deterministic publication manifest (`prepareCandidateManifestForSeal`) before internally
     * calling `sealCandidatePublication` -- unlike calling `sealCandidatePublication` directly on
     * an under-prepared candidate, this is what a genuine successful seal actually requires.
     */
    private function sealThroughPipeline(MarketDataPipelineService $pipeline, $run)
    {
        $input = new \App\Application\MarketData\DTOs\MarketDataStageInput(
            '2026-03-20', 'manual_file', $run->run_id, 'SEAL', null, false, null, 'full_publish'
        );
        return $pipeline->completeSeal($input);
    }

    public function test_seal_completes_a_genuinely_valid_bound_context(): void
    {
        [$run, $publication, $pipeline] = $this->makeSealReadyPublication();

        $sealedRun = $this->sealThroughPipeline($pipeline, $run);
        $sealed = DB::table('eod_publications')->where('publication_id', $publication->publication_id)->first();

        $this->assertNotNull($sealedRun);
        $this->assertSame('SEALED', (string) $sealed->seal_state);
        $this->assertSame((int) $publication->publication_id, (int) $sealed->publication_id);
    }

    public function test_seal_rejects_a_bound_context_referencing_a_missing_component(): void
    {
        [$run, $publication] = $this->makeSealReadyPublication();
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();
        $bundle = json_decode((string) $lineage->bound_input_context_json, true);
        $this->assertNotEmpty($bundle['components']);
        // Rewrite the first component's slot_hash so it no longer resolves to any real capture,
        // then re-canonicalize so the digest itself stays self-consistent -- isolating this as
        // purely a missing-component defect, not a digest tamper.
        $bundle['components'][0]['slot_hash'] = str_repeat('0', 64);
        $newJson = json_encode($bundle);
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->update([
            'bound_input_context_json' => $newJson, 'bound_input_context_hash' => hash('sha256', $newJson),
        ]);

        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->sealCandidatePublication($run, 'operator');
            $this->fail('Sealed a publication whose bound context named a component with no matching immutable capture.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_COMPONENT_UNVERIFIABLE', $e->getMessage());
        }
        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')->where('publication_id', $publication->publication_id)->value('seal_state'));
    }

    public function test_seal_rejects_a_bound_context_with_an_altered_component_hash(): void
    {
        [$run, $publication] = $this->makeSealReadyPublication();
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();
        $bundle = json_decode((string) $lineage->bound_input_context_json, true);
        $bundle['components'][0]['payload_hash'] = str_repeat('a', 64);
        $newJson = json_encode($bundle);
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->update([
            'bound_input_context_json' => $newJson, 'bound_input_context_hash' => hash('sha256', $newJson),
        ]);

        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->sealCandidatePublication($run, 'operator');
            $this->fail('Sealed a publication whose bound context claimed a payload_hash the immutable capture does not actually have.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_COMPONENT_UNVERIFIABLE', $e->getMessage());
        }
        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')->where('publication_id', $publication->publication_id)->value('seal_state'));
    }

    public function test_seal_rejects_a_bound_context_with_wrong_source_run_id_provenance(): void
    {
        [$run, $publication] = $this->makeSealReadyPublication();
        $lineage = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();
        $bundle = json_decode((string) $lineage->bound_input_context_json, true);
        // Point one genuinely-owned component at an arbitrary other run id: neither this run's own
        // id nor its (nonexistent, for this mainline run) seed run.
        $bundle['components'][0]['source_run_id'] = (int) $run->run_id + 999;
        $newJson = json_encode($bundle);
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->update([
            'bound_input_context_json' => $newJson, 'bound_input_context_hash' => hash('sha256', $newJson),
        ]);

        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->sealCandidatePublication($run, 'operator');
            $this->fail('Sealed a publication whose bound context named a component sourced from an arbitrary, unrecorded run.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_PROVENANCE_INVALID', $e->getMessage());
        }
        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')->where('publication_id', $publication->publication_id)->value('seal_state'));
    }

    public function test_seal_rejects_a_bound_context_digest_mismatch(): void
    {
        [$run, $publication] = $this->makeSealReadyPublication();
        // Corrupt only the stored digest, leaving the JSON bytes exactly as bound -- proves the
        // digest re-check is independent of, and runs before, any per-component verification.
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['bound_input_context_hash' => str_repeat('9', 64)]);

        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->sealCandidatePublication($run, 'operator');
            $this->fail('Sealed a publication whose bound_input_context_hash did not match its own bound_input_context_json.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_DIGEST_MISMATCH', $e->getMessage());
        }
        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')->where('publication_id', $publication->publication_id)->value('seal_state'));
    }

    public function test_seal_verification_rejects_ownership_mismatch_directly(): void
    {
        // sealCandidatePublication always resolves its own candidate by $run->run_id, so a live
        // ownership mismatch can never actually reach this check through that one caller; proven
        // directly against PublicationInputBindingService::verifyBeforeSeal itself, the same way
        // Binding's own ownership guard is proven, as defense in depth rather than dead code.
        [$run, $publication] = $this->makeSealReadyPublication();
        $otherRun = (new \App\Infrastructure\Persistence\MarketData\EodRunRepository())
            ->getOrCreateOwningRun('2026-03-21', 'manual_file', 'INGEST_BARS', null, 'seal-ownership-probe');

        $binder = new \App\Application\MarketData\Services\PublicationInputBindingService();
        try {
            $binder->verifyBeforeSeal($otherRun, (int) $publication->publication_id, '2026-03-20');
            $this->fail('verifyBeforeSeal accepted a publication owned by a different run.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_OWNERSHIP_MISMATCH', $e->getMessage());
        }
    }

    public function test_seal_uses_frozen_bound_evidence_unaffected_by_later_live_data_mutation(): void
    {
        [$run, $publication, $pipeline] = $this->makeSealReadyPublication();
        $lineageBefore = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();

        // Mutate a live reference table the original ancillary capture was drawn from, after
        // Binding already ran -- Seal must never consult it, only the already-bound, immutable
        // evidence. (`eod_bars` itself is deliberately left alone here: completeSeal's own,
        // separate V1 snapshot/hash-equality check would legitimately reject a changed canonical
        // bar before ever reaching Seal's V2 verification, which is not what this test isolates.)
        DB::table('ticker_sector_memberships')->update(['sector_code' => 'Z']);

        $this->sealThroughPipeline($pipeline, $run);
        $sealed = DB::table('eod_publications')->where('publication_id', $publication->publication_id)->first();

        $this->assertSame('SEALED', (string) $sealed->seal_state);
        $lineageAfter = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();
        $this->assertSame($lineageBefore->bound_input_context_hash, $lineageAfter->bound_input_context_hash,
            'Seal must not recompute or alter the bound context even after live/current data changed.');
    }

    public function test_seal_failure_leaves_binding_untouched_and_a_later_retry_succeeds_once_corrected(): void
    {
        [$run, $publication, $pipeline] = $this->makeSealReadyPublication();
        $lineageBefore = DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)->first();

        // Induce a transient failure (a digest corruption), confirm Seal refuses and creates or
        // repairs nothing, then correct it and confirm a retry now succeeds -- Seal's refusal is
        // not a permanent, un-retriable state once the actual underlying condition is fixed. The
        // induced-failure call goes directly through the repository (Seal's own verification is
        // reached and refuses before the older, unrelated deterministic-manifest precondition ever
        // matters); the retry goes through the real pipeline stage, which is what a genuine
        // successful seal actually requires.
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['bound_input_context_hash' => str_repeat('1', 64)]);
        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->sealCandidatePublication($run, 'operator');
            $this->fail('Sealed despite an induced digest mismatch.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_SEAL_VERIFICATION_DIGEST_MISMATCH', $e->getMessage());
        }
        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')->where('publication_id', $publication->publication_id)->value('seal_state'));

        DB::table('md_publication_lineage_bindings')->where('publication_id', $publication->publication_id)
            ->update(['bound_input_context_hash' => $lineageBefore->bound_input_context_hash]);
        $this->sealThroughPipeline($pipeline, $run);
        $sealed = DB::table('eod_publications')->where('publication_id', $publication->publication_id)->first();
        $this->assertSame('SEALED', (string) $sealed->seal_state);
    }

    public function test_seal_candidate_publication_partial_still_unconditionally_refuses(): void
    {
        [$run] = $this->makeSealReadyPublication();
        try {
            (new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository())->sealCandidatePublicationPartial($run, 'operator');
            $this->fail('sealCandidatePublicationPartial sealed a partial candidate.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('RUN_SEAL_PRECONDITION_FAILED', $e->getMessage());
        }
    }

    private function makePipelineWithAncillaryEngaged(): MarketDataPipelineService
    {
        $publications = new EodPublicationRepository();
        $artifacts = new EodArtifactRepository();
        $runs = new EodRunRepository();
        $tickers = new TickerMasterRepository();
        $bars = new EodBarsIngestService(
            new LocalFileEodBarsAdapter(),
            new PublicApiEodBarsAdapter(function () {
                throw new RuntimeException('API source not expected in sqlite integration test.');
            }),
            $tickers,
            $artifacts,
            $publications
        );
        $eventRisks = new \App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository();
        $indicators = new EodIndicatorsComputeService(
            $artifacts,
            $publications,
            new IndicatorVectorService(),
            new \App\Application\MarketData\Services\BenchmarkIndicatorComputeService(
                new \App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository(),
                new \App\Application\MarketData\Services\BenchmarkIndicatorVectorService()
            ),
            new \App\Infrastructure\Persistence\MarketData\SectorClassificationRepository(),
            $eventRisks
        );
        $eligibility = new EodEligibilityBuildService(
            $tickers,
            $artifacts,
            $publications,
            new EligibilityDecisionService(),
            $eventRisks
        );
        return new MarketDataPipelineService(
            $runs,
            $bars,
            $indicators,
            $eligibility,
            $publications,
            new EodCorrectionRepository(),
            $artifacts,
            new DeterministicHashService(),
            new FinalizeDecisionService(),
            new PublicationDiffService(),
            new PublicationFinalizeOutcomeService(),
            new CoverageGateEvaluator(new TickerMasterRepository(), $artifacts)
        );
    }

    private function makePipeline(): MarketDataPipelineService
    {
        return $this->makePipelineWithOverrides(new EodPublicationRepository(), new EodArtifactRepository());
    }

    private function makePipelineWithApiFetcher(callable $fetcher): MarketDataPipelineService
    {
        return $this->makePipelineWithOverrides(new EodPublicationRepository(), new EodArtifactRepository(), $fetcher);
    }

    private function makePipelineWithPublications(EodPublicationRepository $publications): MarketDataPipelineService
    {
        return $this->makePipelineWithOverrides($publications, new EodArtifactRepository());
    }

    private function makePipelineWithArtifacts(EodArtifactRepository $artifacts): MarketDataPipelineService
    {
        return $this->makePipelineWithOverrides(new EodPublicationRepository(), $artifacts);
    }

    private function makePipelineWithOverrides(EodPublicationRepository $publications, EodArtifactRepository $artifacts, callable $apiFetcher = null): MarketDataPipelineService
    {
        $runs = new EodRunRepository();
        $tickers = new TickerMasterRepository();

        $bars = new EodBarsIngestService(
            new LocalFileEodBarsAdapter(),
            new PublicApiEodBarsAdapter($apiFetcher ?: function () {
                throw new RuntimeException('API source not expected in sqlite integration test.');
            }),
            $tickers,
            $artifacts,
            $publications
        );

        $indicators = new EodIndicatorsComputeService(
            $artifacts,
            $publications,
            new IndicatorVectorService()
        );

        $eligibility = new EodEligibilityBuildService(
            $tickers,
            $artifacts,
            $publications,
            new EligibilityDecisionService()
        );

        return new MarketDataPipelineService(
            $runs,
            $bars,
            $indicators,
            $eligibility,
            $publications,
            new EodCorrectionRepository(),
            $artifacts,
            new DeterministicHashService(),
            new FinalizeDecisionService(),
            new PublicationDiffService(),
            new PublicationFinalizeOutcomeService(),
            new CoverageGateEvaluator(new TickerMasterRepository(), $artifacts)
        );
    }

    private function makeEvidenceExporter(): MarketDataEvidenceExportService
    {
        return new MarketDataEvidenceExportService(
            new App\Infrastructure\Persistence\MarketData\EodEvidenceRepository(),
            new EodPublicationRepository(),
            new EodCorrectionRepository()
        );
    }

    private function seedTicker(int $tickerId, string $tickerCode): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'is_active' => 1,
            'listed_date' => '2020-01-01',
            'delisted_date' => null,
        ]);
    }

    private function seedMarketCalendarRange(string $startDate, string $endDate): void
    {
        $this->seedVerifiedMarketCalendarRange($startDate, $endDate);
    }

    /** Fixture-authored immutable input history; never a repair path for real historical data. */
    private function seedProducerBoundHistoricalBars(string $startDate, string $endDate, int $tickerId, float $startClose, int $startVolume): void
    {
        $identity = new \App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository();
        $identity->ensureLegacyProjection();
        $source = new \App\Infrastructure\Persistence\MarketData\SourceObservationRepository();
        $date = Carbon::parse($startDate); $end = Carbon::parse($endDate); $close = $startClose; $volume = $startVolume;
        while ($date->lessThanOrEqualTo($end)) {
            $day = $date->toDateString();
            $run = (new EodRunRepository())->getOrCreateOwningRun($day, 'manual_file', 'INGEST_BARS', null, 'c07-fixture-'.$day);
            $publicationId = (int) $date->format('Ymd');
            if (! DB::table('eod_publications')->where('publication_id', $publicationId)->exists()) DB::table('eod_publications')->insert([
                'publication_id' => $publicationId, 'trade_date' => $day, 'run_id' => $run->run_id,
                'publication_version' => 2, 'seal_state' => 'UNSEALED', 'created_at' => $day.' 18:00:00']);
            $pub = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
            $listing = $identity->resolveByTickerCodes([DB::table('tickers')->where('ticker_id', $tickerId)->value('ticker_code')], $day);
            $listing = (array) reset($listing);
            $row = ['ticker_code' => DB::table('tickers')->where('ticker_id', $tickerId)->value('ticker_code'),
                'listing_id' => $listing['listing_id'], 'provider_mapping_id' => $listing['provider_mapping_id'],
                'mapping_revision' => $listing['mapping_revision'], 'source_row_ref' => 'fixture:'.$tickerId,
                'trade_date' => $day, 'open' => $close - 1, 'high' => $close + 2, 'low' => $close - 2, 'close' => $close, 'volume' => $volume];
            $out = $source->recordAcceptedRows($source->capture(['run_id' => $run->run_id, 'attempt_uid' => 'c07-fixture-'.$day,
                'requested_trade_date' => $day, 'source_mode' => 'manual_file', 'source_name' => 'C07_FIXTURE',
                'provider' => 'fixture', 'provider_symbol' => $row['ticker_code'], 'sanitized_request_identity' => 'fixture:'.$day,
                'adapter_version' => 'c07-fixture-v1', 'payload' => json_encode($row), 'acquired_at' => $day.' 17:00:00']), [$row]);
            $source->bindResolvedIdentity($out['source_observation_id'], $row['source_row_ref'], $row);
            $raw = array_intersect_key($row, array_flip(['listing_id','trade_date','open','high','low','close','volume']));
            $raw += ['ticker_id' => $tickerId, 'source' => 'MANUAL_FILE', 'run_id' => $run->run_id,
                'publication_id' => $pub->publication_id, 'source_observation_id' => $out['source_observation_id'],
                'canonicalization_version' => 'c07-fixture-v1', 'price_product_code' => 'RAW', 'quality_state' => 'VALIDATED',
                'config_snapshot_id' => $run->config_snapshot_id, 'created_at' => $day.' 18:00:00'];
            DB::table('eod_bars')->insert($raw); DB::table('eod_bars_history')->insert($raw);
            DB::table('eod_publications')->where('publication_id', $pub->publication_id)->update([
                'seal_state' => 'SEALED', 'bars_batch_hash' => (new DeterministicHashService())->hashRows(DB::table('eod_bars_history')->where('publication_id', $pub->publication_id)->orderBy('ticker_id')->get(), MarketDataPipelineService::BARS_HASH_COLUMNS), 'sealed_at' => $day.' 18:00:00']);
            $date->addDay(); $close += 1; $volume += 10;
        }
    }

    private function seedHistoricalBars(string $startDate, string $endDate, int $tickerId, float $startClose, int $startVolume): void
    {
        $this->seedProducerBoundHistoricalBars($startDate, $endDate, $tickerId, $startClose, $startVolume);
    }

    private function seedBaselinePointerToDifferentTradeDatePublication(string $pointerTradeDate, string $publicationTradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $pointerTradeDate,
            'trade_date_effective' => $pointerTradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_expected_count' => 1,
            'coverage_bar_not_expected_count' => 0,
            'coverage_expectation_unknown_count' => 0,
            'coverage_delivered_count' => 1,
            'coverage_delivered_valid_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'trade-date-mismatch-baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'trade-date-mismatch-baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $publicationTradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $pointerTradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->updateOrInsert(
            [
                'trade_date' => $publicationTradeDate,
                'ticker_id' => $tickerId,
            ],
            [
                'open' => $close,
                'high' => $close,
                'low' => $close,
                'close' => $close,
                'volume' => 1000,
                'adj_close' => $close,
                'source' => 'MANUAL_FILE',
                'run_id' => 90,
                'publication_id' => 1,
                'created_at' => Carbon::now()->toDateTimeString(),
            ]
        );

        DB::table('eod_indicators')->insert([
            'trade_date' => $publicationTradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $publicationTradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => null,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }


    private function seedBaselinePointerToPublicationWithRunRequestedTradeDateMismatch(string $tradeDate, string $runTradeDateRequested, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $runTradeDateRequested,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'run-requested-trade-date-mismatch-baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'run-requested-trade-date-mismatch-baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'listing_id' => 1000 + $tickerId,
            'source_observation_id' => 5000 + $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'canonicalization_version' => 'eod_canonical_v1',
            'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'listing_id' => 1000 + $tickerId,
            'eligible' => 1,
            'reason_code' => null,
            'universe_membership_state' => 'MEMBER',
            'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
            'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED',
            'liquidity_state' => 'ACTIVE',
            'temporal_status_state' => 'UNKNOWN',
            'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'SOURCE_TRACEABLE',
            'price_basis_state' => 'RAW',
            'contamination_state' => 'NO_CONTAMINATION_DETECTED',
            'indicator_state' => 'VALID',
            'eligibility_reasons_json' => '[]',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }
    private function seedBaselinePointerToPublicationWithMissingRunForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => null,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }


    private function seedBaselinePointerToNonReadableRunPublicationForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'HELD',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'NOT_READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 1,
            'notes' => 'non-readable-baseline-run',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_version' => 1,
            'is_current_publication' => 0,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'non-readable-baseline-run',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => null,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }



    private function seedBaselinePointerToSealedPublicationMarkedNonCurrentForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'sealed-publication-marked-non-current',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'sealed-publication-marked-non-current',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 0,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => 'baseline',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }



    private function seedBaselinePointerToReadableRunMarkedNonCurrentForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'readable-run-marked-non-current',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_version' => 1,
            'is_current_publication' => 0,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'readable-run-marked-non-current',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => 'baseline',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }


    private function seedBaselinePointerToPublicationWithDifferentVersionForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'pointer-publication-version-mismatch-baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'pointer-publication-version-mismatch-baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 2,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => 'baseline',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    private function seedBaselinePointerToPublicationWithDifferentRunIdForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'pointer-run-id-mismatch-baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'pointer-run-id-mismatch-baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_runs')->insert([
            'run_id' => 91,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'pointer-run-id-mismatch-incident',
            'bars_batch_hash' => 'bars-incident',
            'indicators_batch_hash' => 'ind-incident',
            'eligibility_batch_hash' => 'elig-incident',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:21:00',
            'sealed_by' => 'system',
            'seal_note' => 'pointer-run-id-mismatch-incident',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:21:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 91,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => 'baseline',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    private function seedBaselinePointerWithoutPublicationForTradeDate(string $tradeDate): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'missing-publication-baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'missing-publication-baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 999,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);
    }

    private function seedMalformedBaselinePointerForTradeDate(string $tradeDate, int $tickerId, float $close, string $sealState = 'UNSEALED', int $isCurrent = 0): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'malformed-baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'publication_version' => 1,
            'is_current_publication' => $isCurrent,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'malformed-baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => $isCurrent,
            'supersedes_publication_id' => null,
            'seal_state' => $sealState,
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'sealed_at' => $sealState === 'SEALED' ? '2026-03-20 17:20:00' : null,
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close,
            'high' => $close,
            'low' => $close,
            'close' => $close,
            'volume' => 1000,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => config('market_data.indicators.set_version'),
            'dv20_idr' => 100000000,
            'atr14_pct' => 0.02,
            'vol_ratio' => 1.1,
            'roc20' => 0.03,
            'hh20' => $close,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'eligible' => 1,
            'reason_code' => 'baseline',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    private function seedCurrentPublicationBaselineForTradeDate(string $tradeDate, int $tickerId, float $close): void
    {
        $factorSetHash = hash('sha256', 'pipeline-baseline|'.$tradeDate.'|1');

        DB::table('eod_runs')->insert([
            'run_id' => 90,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_expected_count' => 1,
            'coverage_bar_not_expected_count' => 0,
            'coverage_expectation_unknown_count' => 0,
            'coverage_delivered_count' => 1,
            'coverage_delivered_valid_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'baseline',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'config_version' => 'v1',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'publication_id' => 1,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'baseline',
            'started_at' => '2026-03-20 17:00:00',
            'finished_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 1,
            'trade_date' => $tradeDate,
            'run_id' => 90,
            'publication_version' => 1,
            'is_current' => 0,
            'supersedes_publication_id' => null,
            'seal_state' => 'UNSEALED',
            'bars_batch_hash' => 'bars-old',
            'indicators_batch_hash' => 'ind-old',
            'eligibility_batch_hash' => 'elig-old',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'sealed_at' => null,
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'listing_id' => 1000 + $tickerId,
            'source_observation_id' => 5000 + $tickerId,
            'open' => $close - 1,
            'high' => $close + 2,
            'low' => $close - 2,
            'close' => $close,
            'volume' => 1500,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
            'canonicalization_version' => 'eod_canonical_v1',
            'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => '2026-03-20 17:00:00',
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'indicator_set_version' => 'v1',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'dv20_idr' => 1000000,
            'atr14_pct' => 0.01,
            'vol_ratio' => 1.0,
            'roc20' => 0.02,
            'hh20' => $close + 2,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => '2026-03-20 17:00:00',
        ]);

        DB::table('eod_eligibility')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'listing_id' => 1000 + $tickerId,
            'eligible' => 1,
            'reason_code' => null,
            'universe_membership_state' => 'MEMBER',
            'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
            'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED',
            'liquidity_state' => 'ACTIVE',
            'temporal_status_state' => 'UNKNOWN',
            'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'SOURCE_TRACEABLE',
            'price_basis_state' => 'RAW',
            'contamination_state' => 'NO_CONTAMINATION_DETECTED',
            'indicator_state' => 'VALID',
            'eligibility_reasons_json' => '[]',
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => '2026-03-20 17:00:00',
        ]);

        // Build the immutable baseline through the same legal pre-seal snapshot ordering that B10
        // now enforces. A test fixture must not fabricate a SEALED publication with missing history.
        (new EodArtifactRepository())->snapshotPublicationFromCurrentTables($tradeDate, 1, 90);

        DB::table('eod_publications')->where('publication_id', 1)->update([
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => 1,
            'run_id' => 90,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
    }


    private function seedReadableFallbackPublication(string $tradeDate, int $runId, int $publicationId): void
    {
        $factorSetHash = hash('sha256', 'pipeline-fallback|'.$tradeDate.'|'.$runId.'|'.$publicationId);

        DB::table('eod_runs')->insert([
            'run_id' => $runId,
            'trade_date_requested' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'SUCCESS',
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'coverage_ratio' => '1.0000',
            'coverage_gate_state' => 'PASS',
            'coverage_min_threshold' => '0.9800',
            'coverage_universe_count' => 1,
            'coverage_available_count' => 1,
            'coverage_missing_count' => 0,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 1,
            'indicators_rows_written' => 1,
            'eligibility_rows_written' => 1,
            'invalid_bar_count' => 0,
            'invalid_indicator_count' => 0,
            'hard_reject_count' => 0,
            'warning_count' => 0,
            'notes' => 'fallback',
            'bars_batch_hash' => 'bars-fallback',
            'indicators_batch_hash' => 'ind-fallback',
            'eligibility_batch_hash' => 'elig-fallback',
            'config_version' => 'v1',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'publication_id' => $publicationId,
            'publication_version' => 1,
            'is_current_publication' => 1,
            'sealed_at' => $tradeDate.' 17:20:00',
            'sealed_by' => 'system',
            'seal_note' => 'fallback',
            'started_at' => $tradeDate.' 17:00:00',
            'finished_at' => $tradeDate.' 17:20:00',
            'created_at' => $tradeDate.' 17:00:00',
            'updated_at' => $tradeDate.' 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => $publicationId,
            'trade_date' => $tradeDate,
            'run_id' => $runId,
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => 'bars-fallback',
            'indicators_batch_hash' => 'ind-fallback',
            'eligibility_batch_hash' => 'elig-fallback',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'sealed_at' => $tradeDate.' 17:20:00',
            'created_at' => $tradeDate.' 17:00:00',
            'updated_at' => $tradeDate.' 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => $tradeDate,
            'publication_id' => $publicationId,
            'run_id' => $runId,
            'publication_version' => 1,
            'sealed_at' => $tradeDate.' 17:20:00',
            'updated_at' => $tradeDate.' 17:20:00',
        ]);
    }

    private function writeBarsFixture(string $tradeDate, array $rows): void
    {
        file_put_contents(
            $this->fixtureDir.DIRECTORY_SEPARATOR.$tradeDate.'.json',
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}

class ThrowingSealPublicationRepository extends EodPublicationRepository
{
    private string $message;

    public function __construct(string $message)
    {
        parent::__construct();

        $this->message = $message;
    }

    public function sealCandidatePublication(App\Models\EodRun $run, $sealedBy, $sealNote = null)
    {
        throw new RuntimeException($this->message);
    }
}

class BaselineMismatchPromotionPublicationRepository extends EodPublicationRepository
{
    private int $conflictingPublicationId;

    private int $conflictingRunId;

    private int $conflictingPublicationVersion;

    public function __construct(int $conflictingPublicationId, int $conflictingRunId, int $conflictingPublicationVersion)
    {
        parent::__construct();

        $this->conflictingPublicationId = $conflictingPublicationId;
        $this->conflictingRunId = $conflictingRunId;
        $this->conflictingPublicationVersion = $conflictingPublicationVersion;
    }

    public function promoteCandidateToCurrent(App\Models\EodRun $run, $priorPublicationId = null, $forceReplace = false)
    {
        throw new RuntimeException(
            sprintf(
                'Correction baseline no longer matches current publication pointer. expected_prior_publication_id=%d conflicting_publication_id=%d conflicting_run_id=%d conflicting_publication_version=%d',
                (int) $priorPublicationId,
                $this->conflictingPublicationId,
                $this->conflictingRunId,
                $this->conflictingPublicationVersion
            )
        );
    }
}

class ThrowingPromotionPublicationRepository extends EodPublicationRepository
{
    private string $message;

    public function __construct(string $message)
    {
        parent::__construct();

        $this->message = $message;
    }

    public function promoteCandidateToCurrent(App\Models\EodRun $run, $priorPublicationId = null, $forceReplace = false)
    {
        throw new RuntimeException($this->message);
    }
}
class PostSwitchResolutionMismatchPublicationRepository extends EodPublicationRepository
{
    public function promoteCandidateToCurrent(App\Models\EodRun $run, $priorPublicationId = null, $forceReplace = false)
    {
        $candidate = parent::promoteCandidateToCurrent($run, $priorPublicationId, $forceReplace);

        DB::table('eod_publications')
            ->where('publication_id', $candidate->publication_id)
            ->update([
                'is_current' => 0,
            ]);

        return $candidate;
    }
}

class ThrowingHistoryPromotionArtifactRepository extends EodArtifactRepository
{
    private string $message;

    public function __construct(string $message)
    {
        parent::__construct();

        $this->message = $message;
    }

    public function promotePublicationHistoryToCurrent($tradeDate, $publicationId, $runId)
    {
        throw new RuntimeException($this->message);
    }
}
