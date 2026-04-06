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

        foreach (glob($this->fixtureDir.'/*.json') ?: [] as $file) {
            @unlink($file);
        }

        foreach (glob($this->fixtureDir.'/*.csv') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->fixtureDir);

        parent::tearDown();
    }

    public function test_run_daily_persists_full_db_backed_pipeline_and_current_publication(): void
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

        $this->assertFileExists($this->fixtureDir.'/2026-03-20.json');

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertSame('2026-03-20', $run->trade_date_effective);
        $this->assertEquals(1.0, (float) $run->coverage_ratio);
        $this->assertNotNull($run->bars_batch_hash);
        $this->assertNotNull($run->indicators_batch_hash);
        $this->assertNotNull($run->eligibility_batch_hash);

        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertNotNull($publication);
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertSame('SEALED', $publication->seal_state);

        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $this->assertNotNull($pointer);
        $this->assertSame((int) $publication->publication_id, (int) $pointer->publication_id);

        $this->assertSame(1, DB::table('eod_bars')->where('trade_date', '2026-03-20')->count());
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


    public function test_run_daily_api_source_timeout_failure_persists_attempt_context_in_run_event(): void
    {
        $this->seedTicker(1, 'BBCA');
        config()->set('market_data.source.api.endpoint_template', 'https://example.test/eod/{date}?symbols={symbols}');
        config()->set('market_data.source.api.response_rows_path', 'rows');
        config()->set('market_data.source.api.provider', 'generic');
        config()->set('market_data.provider.api_retry_max', 2);
        config()->set('market_data.provider.api_backoff_ms', 5);
        config()->set('market_data.provider.api_throttle_qps', 1000);

        $calls = 0;
        try {
            $this->makePipelineWithApiFetcher(function () use (&$calls) {
                $calls++;

                return [
                    'status' => 500,
                    'body' => '{"error":"upstream unavailable"}',
                ];
            })->runDaily('2026-03-20', 'api');
            $this->fail('Expected api source timeout failure.');
        } catch (\App\Infrastructure\MarketData\Source\SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_TIMEOUT', $e->reasonCode());
        }

        $run = DB::table('eod_runs')
            ->where('trade_date_requested', '2026-03-20')
            ->orderByDesc('run_id')
            ->first();

        $this->assertNotNull($run);
        $this->assertSame('FAILED', $run->terminal_status);
        $this->assertSame('NOT_READABLE', $run->publishability_state);
        $this->assertSame(3, $calls);

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
        $this->assertSame('CANCELLED', $persistedCorrection->status);
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
        $this->assertSame(0, DB::table('eod_run_events')->count());
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
        $this->assertSame('RUN_COVERAGE_LOW', $event->reason_code);
        $this->assertSame('RUN_COVERAGE_LOW', $payload['coverage_reason_code']);
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
        $this->assertSame('RUN_COVERAGE_LOW', $event->reason_code);
        $this->assertSame('RUN_COVERAGE_LOW', $payload['coverage_reason_code']);
        $this->assertSame(1, $payload['coverage_available_count']);
        $this->assertSame(2, $payload['coverage_universe_count']);
        $this->assertSame(1, $payload['coverage_missing_count']);
        $this->assertNotNull($publication);
        $this->assertSame(0, (int) $publication->is_current);
        $this->assertNull(DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first());
    }

    public function test_finalize_not_evaluable_without_universe_stays_not_readable_and_emits_blocked_coverage_reason_code(): void
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

    private function seedTicker(int $tickerId, string $tickerCode): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'is_active' => 'Yes',
            'listed_date' => '2020-01-01',
            'delisted_date' => null,
        ]);
    }

    private function seedHistoricalBars(string $startDate, string $endDate, int $tickerId, float $startClose, int $startVolume): void
    {
        $date = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $close = $startClose;
        $volume = $startVolume;

        while ($date->lessThanOrEqualTo($end)) {
            DB::table('eod_bars')->insert([
                'trade_date' => $date->toDateString(),
                'ticker_id' => $tickerId,
                'open' => $close - 1,
                'high' => $close + 2,
                'low' => $close - 2,
                'close' => $close,
                'volume' => $volume,
                'adj_close' => $close,
                'source' => 'MANUAL_FILE',
                'run_id' => 1,
                'publication_id' => 0,
                'created_at' => Carbon::now()->toDateTimeString(),
            ]);

            $date->addDay();
            $close += 1;
            $volume += 10;
        }
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

        DB::table('eod_bars')->insert([
            'trade_date' => $publicationTradeDate,
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
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_bars')->insert([
            'trade_date' => $tradeDate,
            'ticker_id' => $tickerId,
            'open' => $close - 1,
            'high' => $close + 2,
            'low' => $close - 2,
            'close' => $close,
            'volume' => 1500,
            'adj_close' => $close,
            'source' => 'MANUAL_FILE',
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
            'eligible' => 1,
            'reason_code' => null,
            'run_id' => 90,
            'publication_id' => 1,
            'created_at' => '2026-03-20 17:00:00',
        ]);
    }


    private function seedReadableFallbackPublication(string $tradeDate, int $runId, int $publicationId): void
    {
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
        $this->conflictingPublicationId = $conflictingPublicationId;
        $this->conflictingRunId = $conflictingRunId;
        $this->conflictingPublicationVersion = $conflictingPublicationVersion;
    }

    public function promoteCandidateToCurrent(App\Models\EodRun $run, $priorPublicationId = null)
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
        $this->message = $message;
    }

    public function promoteCandidateToCurrent(App\Models\EodRun $run, $priorPublicationId = null)
    {
        throw new RuntimeException($this->message);
    }
}
class PostSwitchResolutionMismatchPublicationRepository extends EodPublicationRepository
{
    public function promoteCandidateToCurrent(App\Models\EodRun $run, $priorPublicationId = null)
    {
        $candidate = parent::promoteCandidateToCurrent($run, $priorPublicationId);

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
        $this->message = $message;
    }

    public function promotePublicationHistoryToCurrent($tradeDate, $publicationId, $runId)
    {
        throw new RuntimeException($this->message);
    }
}
