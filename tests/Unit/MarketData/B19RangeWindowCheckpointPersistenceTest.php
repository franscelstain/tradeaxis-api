<?php

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — the lifecycle backfill persists the window/ticker checkpoints the acquisition service
 * produces, resumes against them, and reports diagnostics that agree with the persisted file
 * (`MD-S053-R0202`, `R0212`, `R0214`, `R0217`).
 *
 * `B19RangeWindowCheckpointIdentityTest` proves what the acquisition service returns. The contract
 * says the rows must be *persisted* and that the resume diagnostics' failure sample must be
 * "consistent with `source_acquisition_checkpoint.json`". Both are orchestrator behaviour: the
 * service never touches a file. This guard runs the real orchestrator, the real acquisition
 * service and the real provider adapter (with a stubbed HTTP fetcher) over the real calendar, stops
 * after acquisition with `diagnose_source` (so no publication step is reachable — the pipeline is a
 * constructor-less tripwire instance), and reads the files the run left behind. Nothing internal is mocked
 * (`LifecycleProofIsNotMockedTest`): only the HTTP fetcher stands in for the outside world.
 */
class B19RangeWindowCheckpointPersistenceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const HOLIDAYS = [
        '2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20',
        '2026-03-23', '2026-03-24', '2026-03-25',
    ];

    private const START = '2026-03-30';

    private const END = '2026-04-01';

    /** @var string[] */
    private $dirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $date = strtotime('2026-03-02');
        $end = strtotime(self::END);
        while ($date <= $end) {
            $iso = date('Y-m-d', $date);
            $this->seedVerifiedMarketCalendarDate($iso, (int) date('N', $date) <= 5 && ! in_array($iso, self::HOLIDAYS, true));
            $date = strtotime('+1 day', $date);
        }
        config([
            'market_data.source.api_backfill.warmup_trading_days' => 10,
            'market_data.source.api_backfill.window_days' => 7,
            'market_data.source.api_backfill.max_dates_per_run' => 20,
            'market_data.provider.api_retry_max' => 0,
            'market_data.provider.api_backoff_ms' => 0,
            'market_data.provider.api_throttle_qps' => 1000,
            'market_data.source.default_source_name' => 'YAHOO_FINANCE',
            'market_data.source.api' => [
                'provider' => 'yahoo_finance',
                'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
                'response_format' => 'json', 'response_rows_path' => '', 'timeout_seconds' => 3,
                'auth_header_name' => '', 'auth_token' => '', 'source_name' => 'YAHOO_FINANCE',
                'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->dirs as $dir) {
            foreach ((array) glob($dir.'/*') as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function dir(): string
    {
        $dir = sys_get_temp_dir().'/md_b19_checkpoint_'.uniqid('', true);
        mkdir($dir, 0775, true);
        $this->dirs[] = $dir;

        return $dir;
    }

    /** @return string[] every trading date the calendar holds up to the end of the request */
    private function tradingDates(): array
    {
        return (new MarketCalendarRepository())->tradingDatesBetween('2026-03-02', self::END);
    }

    /** A provider answer holding one bar per trading date inside the requested period. */
    private function barsFor(string $url): array
    {
        preg_match('/period1=(\d+)&period2=(\d+)/', $url, $m);
        $from = (int) $m[1];
        $to = (int) $m[2];
        $stamps = [];
        foreach ($this->tradingDates() as $date) {
            $ts = Carbon::parse($date, 'Asia/Jakarta')->timestamp;
            if ($ts >= $from && $ts <= $to) {
                $stamps[] = $ts;
            }
        }
        $n = count($stamps);

        return [
            'status' => 200, 'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['chart' => ['result' => [[
                'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'], 'timestamp' => $stamps,
                'indicators' => ['quote' => [[
                    'open' => array_fill(0, $n, 100), 'high' => array_fill(0, $n, 110), 'low' => array_fill(0, $n, 99),
                    'close' => array_fill(0, $n, 108), 'volume' => array_fill(0, $n, 100000),
                ]], 'adjclose' => [['adjclose' => array_fill(0, $n, 108)]]],
            ]]]]),
        ];
    }

    /**
     * The real orchestrator over real collaborators: real calendar, real ticker master and temporal identity, real acquisition
     * service and provider adapter (only the HTTP fetcher is a stand-in, as the source boundary must be).
     *
     * The publication pipeline is a TRIPWIRE, not a double: an instance built without its constructor, so any publication step
     * that touches a collaborator fails with an Error. Nothing it could return is ever asserted.
     *
     * @return array{0:BackfillLifecycleOrchestrator,1:object}
     */
    private function orchestrator(callable $fetcher): array
    {
        foreach (['AAAA', 'BBBB'] as $code) {
            if (! DB::table('tickers')->where('ticker_code', $code)->exists()) {
                DB::table('tickers')->insert(['ticker_code' => $code, 'company_name' => $code.' Tbk', 'is_active' => 1, 'listed_date' => '2020-01-02']);
            }
        }
        $service = new ApiBackfillRangeAcquisitionService(new PublicApiEodBarsAdapter($fetcher, new EquityProviderSymbolResolver(new TemporalIdentityRepository())));
        $pipeline = (new ReflectionClass(MarketDataPipelineService::class))->newInstanceWithoutConstructor();

        return [new BackfillLifecycleOrchestrator(
            new MarketCalendarRepository(), new TickerMasterRepository(), $service, $pipeline,
            new MarketDataEvidenceExportService(new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()),
            new ReplayVerificationService(new EodEvidenceRepository(), new EodPublicationRepository(), new ReplayResultRepository()),
            new EodRunRepository()
        ), $pipeline];
    }
    private function checkpointFile(string $dir): array
    {
        $file = $dir.'/source_acquisition_checkpoint.json';
        $this->assertFileExists($file, 'the backfill did not persist source_acquisition_checkpoint.json');
        $rows = json_decode((string) file_get_contents($file), true);
        $this->assertIsArray($rows);

        return $rows;
    }

    // ------------------------------------------------------------------ persistence, diagnostics

    /**
     * `MD-S053-R0202`, `R0217`: the file holds one row per (window, ticker) of the run, a failure
     * stays on its own row, and the diagnostics' failure sample agrees with that row.
     */
    public function test_the_backfill_persists_one_checkpoint_per_window_and_ticker_and_its_diagnostics_agree_with_the_file(): void
    {
        $dir = $this->dir();
        $lastWindowStart = null;
        [$orchestrator] = $this->orchestrator(function ($url) use (&$lastWindowStart) {
            if (strpos($url, 'AAAA.JK') !== false && $lastWindowStart !== null && strpos($url, 'period1='.$lastWindowStart) !== false) {
                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"AAAA_LAST_WINDOW"}}}'];
            }

            return $this->barsFor($url);
        });

        $plan = $orchestrator->execute(self::START, self::END, 'api', ['plan' => true, 'output_dir' => $this->dir()]);
        $windows = $plan['plan']['windows'];
        $this->assertGreaterThan(1, count($windows), 'the fixture must span more than one window');
        $lastWindowStart = Carbon::parse(end($windows)['start'], 'Asia/Jakarta')->timestamp;

        $summary = $orchestrator->execute(self::START, self::END, 'api', ['diagnose_source' => true, 'output_dir' => $dir]);
        $rows = $this->checkpointFile($dir);

        $expected = [];
        foreach ($windows as $w) {
            $hasDates = array_filter($this->tradingDates(), function ($d) use ($w) { return $d >= $w['start'] && $d <= $w['end']; });
            if ($hasDates === []) {
                continue;
            }
            foreach (['AAAA', 'BBBB'] as $ticker) {
                $expected[] = $w['start'].'|'.$w['end'].'|'.$ticker;
            }
        }
        $keys = array_keys($rows);
        sort($keys);
        sort($expected);
        $this->assertSame($expected, $keys, 'the persisted file does not hold one row per (window, ticker)');

        $lastWindow = end($windows);
        $failedKey = $lastWindow['start'].'|'.$lastWindow['end'].'|AAAA';
        foreach ($rows as $key => $row) {
            $this->assertSame($key === $failedKey ? 'FAILED' : 'SUCCESS', $row['state'], $key);
        }

        // The diagnostics' failure sample is built from, and agrees with, the persisted row.
        $diagnostic = json_decode((string) file_get_contents($dir.'/source_acquisition_diagnostics.json'), true);
        $this->assertCount(1, $diagnostic['failures_sample'], 'the diagnostics name a different number of failures than the file holds');
        $sample = $diagnostic['failures_sample'][0];
        $row = $rows[$failedKey];
        $this->assertSame('AAAA', $sample['ticker_code']);
        foreach (['window_start', 'window_end', 'reason_code', 'http_status', 'failure_scope', 'sanitized_url', 'provider_error_sample', 'error_sample'] as $field) {
            $this->assertSame($row[$field], $sample[$field], 'the diagnostics\' failure sample disagrees with the checkpoint file on '.$field);
        }
        $this->assertSame($summary['source_acquisition_state'], $diagnostic['source_acquisition_state']);
    }

    /**
     * `MD-S053-R0202`, `R0212`: the persisted rows are what a later resume runs against. A resume
     * asks the provider for the failed window/ticker only, merges the recovered row into the file
     * without dropping the others, and reports `RETRY_SUCCESS`.
     */
    public function test_a_resume_retries_only_the_persisted_failure_and_merges_it_back_into_the_file(): void
    {
        $dir = $this->dir();
        $failLast = true;
        $lastWindowStart = null;
        $asked = [];
        [$orchestrator] = $this->orchestrator(function ($url) use (&$failLast, &$lastWindowStart, &$asked) {
            $asked[] = $url;
            if ($failLast && strpos($url, 'AAAA.JK') !== false && strpos($url, 'period1='.$lastWindowStart) !== false) {
                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"AAAA_LAST_WINDOW"}}}'];
            }

            return $this->barsFor($url);
        });
        $windows = $orchestrator->execute(self::START, self::END, 'api', ['plan' => true, 'output_dir' => $this->dir()])['plan']['windows'];
        $lastWindow = end($windows);
        $lastWindowStart = Carbon::parse($lastWindow['start'], 'Asia/Jakarta')->timestamp;

        $orchestrator->execute(self::START, self::END, 'api', ['diagnose_source' => true, 'output_dir' => $dir]);
        $before = $this->checkpointFile($dir);
        $failedKey = $lastWindow['start'].'|'.$lastWindow['end'].'|AAAA';
        $this->assertSame('FAILED', $before[$failedKey]['state']);

        $failLast = false;
        $asked = [];
        $summary = $orchestrator->execute(self::START, self::END, 'api', ['diagnose_source' => true, 'resume' => true, 'only_failed' => true, 'output_dir' => $dir]);

        $this->assertCount(1, $asked, 'the resume asked the provider for more than the one persisted failure');
        $this->assertStringContainsString('AAAA.JK', $asked[0]);
        $this->assertSame('RETRY_SUCCESS', $summary['source_acquisition_state']);

        $after = $this->checkpointFile($dir);
        $this->assertSame(array_keys($before), array_keys($after), 'the resume dropped or invented checkpoint rows instead of merging');
        $this->assertSame('SUCCESS', $after[$failedKey]['state'], 'the recovered row was not written back');
        foreach ($after as $key => $row) {
            if ($key !== $failedKey) {
                $this->assertSame($before[$key]['state'], $row['state'], $key.' changed during a resume that did not target it');
            }
        }
    }

    /**
     * `MD-S053-R0217`, in resume-only-failed mode: a resume whose retry fails again reports the
     * counts the contract names in the diagnostics file, and its failure sample is the row the
     * checkpoint file now holds for that identity.
     */
    public function test_a_resume_that_still_fails_reports_its_counts_and_a_failure_sample_that_agrees_with_the_file(): void
    {
        $dir = $this->dir();
        $lastWindowStart = null;
        $asked = 0;
        [$orchestrator] = $this->orchestrator(function ($url) use (&$lastWindowStart, &$asked) {
            if (strpos($url, 'AAAA.JK') !== false && strpos($url, 'period1='.$lastWindowStart) !== false) {
                $asked++;

                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"AAAA_STILL_FAILING"}}}'];
            }

            return $this->barsFor($url);
        });
        $windows = $orchestrator->execute(self::START, self::END, 'api', ['plan' => true, 'output_dir' => $this->dir()])['plan']['windows'];
        $lastWindow = end($windows);
        $lastWindowStart = Carbon::parse($lastWindow['start'], 'Asia/Jakarta')->timestamp;
        $failedKey = $lastWindow['start'].'|'.$lastWindow['end'].'|AAAA';

        $orchestrator->execute(self::START, self::END, 'api', ['diagnose_source' => true, 'output_dir' => $dir]);
        $asked = 0;
        $summary = $orchestrator->execute(self::START, self::END, 'api', ['diagnose_source' => true, 'resume' => true, 'only_failed' => true, 'output_dir' => $dir]);

        $this->assertSame(1, $asked, 'the resume must retry exactly the one persisted failure');
        $diagnostic = json_decode((string) file_get_contents($dir.'/source_acquisition_diagnostics.json'), true);
        $this->assertSame('FAILED_RETRY_BLOCKED', $summary['source_acquisition_state']);
        $this->assertSame('FAILED_RETRY_BLOCKED', $diagnostic['source_acquisition_state']);
        foreach (['failed_checkpoint_total' => 1, 'failed_checkpoint_eligible' => 1, 'failed_checkpoint_retried' => 1, 'retry_success_count' => 0, 'retry_failed_count' => 1, 'failed_checkpoint_skipped' => 0, 'skipped_failed_checkpoint_count' => 0] as $field => $expected) {
            $this->assertSame($expected, $diagnostic[$field], 'resume diagnostics: '.$field);
        }
        $this->assertSame([], (array) $diagnostic['skipped_failed_checkpoint_reasons']);

        $row = $this->checkpointFile($dir)[$failedKey];
        $this->assertSame('FAILED', $row['state']);
        $this->assertCount(1, $diagnostic['failures_sample']);
        $sample = $diagnostic['failures_sample'][0];
        $this->assertSame('AAAA', $sample['ticker_code']);
        foreach (['window_start', 'window_end', 'reason_code', 'http_status', 'failure_scope', 'sanitized_url', 'provider_error_sample', 'error_sample'] as $field) {
            $this->assertSame($row[$field], $sample[$field], 'the resume diagnostics\' failure sample disagrees with the checkpoint file on '.$field);
        }
        $this->assertStringContainsString('AAAA_STILL_FAILING', (string) $sample['provider_error_sample']);
    }

    /**
     * `MD-S053-R0214`, `R0215`: a resume over a file with no failed checkpoint is a no-op that says
     * so, and it makes no provider request.
     */
    public function test_a_resume_over_a_file_with_no_failed_checkpoint_is_a_reported_no_op(): void
    {
        $dir = $this->dir();
        file_put_contents($dir.'/source_acquisition_checkpoint.json', json_encode([
            '2026-03-30|2026-04-01|AAAA' => ['state' => 'SUCCESS', 'window_start' => '2026-03-30', 'window_end' => '2026-04-01', 'ticker_code' => 'AAAA'],
        ]));
        [$orchestrator] = $this->orchestrator(function () {
            $this->fail('a resume with nothing to retry made a provider request');
        });

        $summary = $orchestrator->execute(self::START, self::END, 'api', ['resume' => true, 'only_failed' => true, 'output_dir' => $dir]);

        $this->assertSame('NOOP', $summary['status']);
        $this->assertSame('NO_FAILED_CHECKPOINT', $summary['source_acquisition_state']);
        $this->assertSame('NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT', $summary['reason_code']);
        $this->assertSame(0, $summary['failed_checkpoint_total']);
        $this->assertTrue($summary['all_passed']);
    }
}
