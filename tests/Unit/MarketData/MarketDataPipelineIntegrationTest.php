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

        config()->set('market_data.source.local_directory', str_replace(base_path().'/', '', $this->fixtureDir.'/'));
        config()->set('market_data.source.file_template_json', '{date}.json');
        config()->set('market_data.source.file_template_csv', '{date}.csv');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        foreach (glob($this->fixtureDir.'/*.json') ?: [] as $file) {
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

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file');

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame('COMPLETED', $run->lifecycle_state);
        $this->assertSame('2026-03-20', $run->trade_date_effective);
        $this->assertEquals('1.0000', (string) $run->coverage_ratio);
        $this->assertNotNull($run->bars_batch_hash);
        $this->assertNotNull($run->indicators_batch_hash);
        $this->assertNotNull($run->eligibility_batch_hash);

        $publication = DB::table('eod_publications')->where('run_id', $run->run_id)->first();
        $this->assertSame(1, (int) $publication->is_current);
        $this->assertSame('SEALED', $publication->seal_state);

        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->first();
        $this->assertSame((int) $publication->publication_id, (int) $pointer->publication_id);

        $this->assertSame(1, DB::table('eod_bars')->where('trade_date', '2026-03-20')->count());
        $this->assertSame(1, DB::table('eod_indicators')->where('trade_date', '2026-03-20')->count());
        $this->assertSame(1, DB::table('eod_eligibility')->where('trade_date', '2026-03-20')->where('eligible', 1)->count());
        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', $publication->publication_id)->count());
        $this->assertSame(1, DB::table('eod_indicators_history')->where('publication_id', $publication->publication_id)->count());
        $this->assertSame(1, DB::table('eod_eligibility_history')->where('publication_id', $publication->publication_id)->count());
        $this->assertTrue(DB::table('eod_run_events')->where('run_id', $run->run_id)->where('event_type', 'RUN_FINALIZED')->exists());
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

        $corrections = new EodCorrectionRepository();
        $request = $corrections->createRequest('2026-03-20', 'READABILITY_FIX', 'recompute', 'system');
        $approved = $corrections->approve($request->correction_id, 'reviewer');

        $run = $this->makePipeline()->runDaily('2026-03-20', 'manual_file', $approved->correction_id);

        $this->assertSame('SUCCESS', $run->terminal_status);
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame(1, DB::table('eod_publications')->where('trade_date', '2026-03-20')->where('is_current', 1)->count());

        $currentPublication = DB::table('eod_publications')->where('trade_date', '2026-03-20')->where('is_current', 1)->first();
        $this->assertGreaterThan(1, (int) $currentPublication->publication_version);
        $this->assertSame(1, (int) $currentPublication->supersedes_publication_id);

        $currentBar = DB::table('eod_bars')->where('trade_date', '2026-03-20')->where('ticker_id', 1)->first();
        $this->assertEquals('134', (string) $currentBar->close);

        $persistedCorrection = DB::table('eod_dataset_corrections')->where('correction_id', $approved->correction_id)->first();
        $this->assertSame('PUBLISHED', $persistedCorrection->status);
        $this->assertSame('Historical correction published safely via new sealed current publication.', $persistedCorrection->final_outcome_note);
        $this->assertSame(90, (int) $persistedCorrection->prior_run_id);
        $this->assertSame((int) $run->run_id, (int) $persistedCorrection->new_run_id);
        $this->assertNotNull($persistedCorrection->published_at);

        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', 1)->count());
        $this->assertSame(1, DB::table('eod_bars_history')->where('publication_id', $currentPublication->publication_id)->count());
        $this->assertTrue(DB::table('eod_run_events')->where('run_id', $run->run_id)->where('event_type', 'CORRECTION_PUBLISHED')->exists());
    }

    private function makePipeline(): MarketDataPipelineService
    {
        $runs = new EodRunRepository();
        $publications = new EodPublicationRepository();
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
        $indicators = new EodIndicatorsComputeService($artifacts, $publications, new IndicatorVectorService());
        $eligibility = new EodEligibilityBuildService($tickers, $artifacts, $publications, new EligibilityDecisionService());

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

    private function writeBarsFixture(string $tradeDate, array $rows): void
    {
        file_put_contents($this->fixtureDir.'/'.$tradeDate.'.json', json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
