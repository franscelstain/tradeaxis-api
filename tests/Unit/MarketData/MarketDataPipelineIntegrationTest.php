<?php

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

    private function makePipeline(): MarketDataPipelineService
    {
        return $this->makePipelineWithPublications(new EodPublicationRepository());
    }

    private function makePipelineWithPublications(EodPublicationRepository $publications): MarketDataPipelineService
    {
        $runs = new EodRunRepository();
        $artifacts = new EodArtifactRepository();
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
            new PublicationFinalizeOutcomeService()
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