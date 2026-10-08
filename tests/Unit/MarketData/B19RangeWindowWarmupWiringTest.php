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
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — the warmup boundary reaches the acquisition path, and an unprovable calendar blocks it.
 *
 * `B19RangeWindowWarmupCalendarTest` drives the orchestrator's `warmupStart()` resolver on its own.
 * That establishes what the resolver returns. It does not establish that the lifecycle backfill
 * *uses* it: an `execute()` that computed the boundary correctly and then handed `requested_start`
 * (or a calendar-day approximation) to the acquisition service would satisfy every assertion in that
 * file. The contract's sentence is about the acquisition path — "range-window source acquisition
 * must resolve warmup through the market calendar" (`MD-S053-R0218`) — so this guard runs the real
 * orchestrator against the real calendar repository and observes what the provider is actually asked
 * for and what the run records.
 *
 * Nothing internal is replaced (`LifecycleProofIsNotMockedTest`): the calendar, the ticker master and
 * temporal identity, the acquisition service and the provider adapter are real; only the HTTP fetcher
 * stands in for the outside world, and it records the requests the real adapter makes. The publication
 * pipeline is a TRIPWIRE — an instance built without its constructor, so any publication step that
 * touches a collaborator fails with an Error — and the publication tables are asserted empty. That is
 * how "publishing requested dates when the warmup calendar dependency cannot be proven"
 * (`MD-S053-R0221`) is asserted: by the absence of any request and any publication, not by an
 * exception message alone.
 */
class B19RangeWindowWarmupWiringTest extends TestCase
{
    use UsesMarketDataSqlite;

    /** A long exchange closure, so trading-day and calendar-day arithmetic cannot agree. */
    private const HOLIDAYS = [
        '2026-03-16', '2026-03-17', '2026-03-18', '2026-03-19', '2026-03-20',
        '2026-03-23', '2026-03-24', '2026-03-25',
    ];

    private const CALENDAR_START = '2026-03-02';

    private const REQUESTED_START = '2026-03-30';

    private const REQUESTED_END = '2026-04-01';

    /** @var string[] */
    private $outputDirs = [];

    /** @var string[] the provider requests the real adapter made */
    private $requests = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();

        $date = strtotime(self::CALENDAR_START);
        $end = strtotime(self::REQUESTED_END);
        while ($date <= $end) {
            $iso = date('Y-m-d', $date);
            $weekday = (int) date('N', $date) <= 5;
            $this->seedVerifiedMarketCalendarDate($iso, $weekday && ! in_array($iso, self::HOLIDAYS, true));
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
        foreach ($this->outputDirs as $dir) {
            foreach ((array) glob($dir.'/*') as $file) {
                @unlink($file);
            }
            @rmdir($dir);
        }
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function outputDir(): string
    {
        $dir = sys_get_temp_dir().'/md_b19_warmup_wiring_'.uniqid('', true);
        $this->outputDirs[] = $dir;

        return $dir;
    }

    /**
     * The real orchestrator over real collaborators. The fetcher records every request and then
     * fails it, so the run stops at the source — after the real adapter has built the URLs, and
     * before anything could be imported or published.
     */
    private function orchestrator(): BackfillLifecycleOrchestrator
    {
        if (! DB::table('tickers')->where('ticker_code', 'AAAA')->exists()) {
            DB::table('tickers')->insert(['ticker_code' => 'AAAA', 'company_name' => 'AAAA Tbk', 'is_active' => 1, 'listed_date' => '2020-01-02']);
        }
        $fetcher = function ($url) {
            $this->requests[] = (string) $url;
            throw new RuntimeException('probe: the provider is not reachable in this guard');
        };
        $service = new ApiBackfillRangeAcquisitionService(new PublicApiEodBarsAdapter($fetcher, new EquityProviderSymbolResolver(new TemporalIdentityRepository())));
        $pipeline = (new ReflectionClass(MarketDataPipelineService::class))->newInstanceWithoutConstructor();

        return new BackfillLifecycleOrchestrator(
            new MarketCalendarRepository(), new TickerMasterRepository(), $service, $pipeline,
            new MarketDataEvidenceExportService(new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()),
            new ReplayVerificationService(new EodEvidenceRepository(), new EodPublicationRepository(), new ReplayResultRepository()),
            new EodRunRepository()
        );
    }

    private function governedBoundary(string $firstRequestedTradingDate, int $tradingDays): string
    {
        return (new MarketCalendarRepository())->tradingDateWindowStart($firstRequestedTradingDate, $tradingDays);
    }

    private function period1(string $date): int
    {
        return Carbon::parse($date, 'Asia/Jakarta')->timestamp;
    }

    /**
     * `MD-S053-R0218`, `R0225`: the plan the lifecycle backfill records for an API range-window run
     * carries the calendar boundary and the four telemetry fields the contract names, and planning
     * asks the provider for nothing.
     */
    public function test_the_recorded_plan_carries_the_calendar_boundary_and_the_four_telemetry_fields(): void
    {
        $orchestrator = $this->orchestrator();
        $dir = $this->outputDir();

        $summary = $orchestrator->execute(self::REQUESTED_START, self::REQUESTED_END, 'api', ['plan' => true, 'output_dir' => $dir]);

        $this->assertSame([], $this->requests, 'planning made a provider request');
        $governed = $this->governedBoundary(self::REQUESTED_START, 10);
        $calendarDayArithmetic = date('Y-m-d', strtotime(self::REQUESTED_START.' -10 days'));
        $this->assertNotSame($calendarDayArithmetic, $governed, 'the fixture must make trading-day and calendar-day boundaries differ');

        $this->assertSame($governed, $summary['warmup_start'], 'the run summary does not record the calendar boundary');
        $this->assertSame($governed, $summary['plan']['warmup_start'], 'the plan does not record the calendar boundary');
        $this->assertNotSame(self::REQUESTED_START, $summary['warmup_start'], 'warmup_start collapsed to requested_start');
        $this->assertSame(self::REQUESTED_START, $summary['requested_start']);
        $this->assertSame(self::REQUESTED_END, $summary['requested_end']);
        $this->assertSame('range_window', $summary['source_acquisition_mode']);
        $this->assertSame('range_window', $summary['plan']['source_acquisition_mode']);

        // The telemetry is the artifact the operator reads, not just the return value.
        $file = $dir.'/market_data_backfill_lifecycle_summary.json';
        $this->assertFileExists($file);
        $written = json_decode((string) file_get_contents($file), true);
        foreach (['warmup_start' => $governed, 'requested_start' => self::REQUESTED_START, 'requested_end' => self::REQUESTED_END, 'source_acquisition_mode' => 'range_window'] as $field => $expected) {
            $this->assertArrayHasKey($field, $written, $field.' is absent from the written run summary');
            $this->assertSame($expected, $written[$field], $field.' is wrong in the written run summary');
        }
    }

    /**
     * `MD-S053-R0218`, `R0219`, `R0220`: the provider is asked for the history that starts at the
     * calendar boundary — the first request's period begins there — and the trading dates the plan
     * hands to acquisition are the calendar-resolved dates from that boundary. The boundary moves by
     * whole trading days with the configured count, so it is a function of the calendar and not of a
     * fixed offset.
     */
    public function test_the_acquisition_service_is_handed_the_calendar_boundary(): void
    {
        $boundaries = [];
        foreach ([10, 11] as $tradingDays) {
            config(['market_data.source.api_backfill.warmup_trading_days' => $tradingDays]);
            $this->requests = [];
            $orchestrator = $this->orchestrator();

            $summary = $orchestrator->execute(self::REQUESTED_START, self::REQUESTED_END, 'api', ['output_dir' => $this->outputDir()]);

            $this->assertSame('BLOCKED', $summary['status'], 'the run did not reach the provider');
            $this->assertNotSame([], $this->requests, 'the run made no provider request');
            $boundary = $this->governedBoundary(self::REQUESTED_START, $tradingDays);
            $boundaries[$tradingDays] = $boundary;

            $this->assertSame($boundary, $summary['plan']['windows'][0]['start'], 'the first acquisition window does not start at the calendar boundary for '.$tradingDays.' trading days');
            $this->assertStringContainsString('period1='.$this->period1($boundary), $this->requests[0],
                'the first provider request does not start at the calendar boundary for '.$tradingDays.' trading days');
            $this->assertSame(self::REQUESTED_START, $summary['requested_start']);
            $this->assertSame(self::REQUESTED_END, $summary['requested_end']);
            $this->assertSame(
                count((new MarketCalendarRepository())->tradingDatesBetween($boundary, self::REQUESTED_END)),
                $summary['plan']['trading_date_count'],
                'the trading dates given to acquisition are not the calendar-resolved dates from the boundary'
            );
            $this->assertNotSame(date('Y-m-d', strtotime(self::REQUESTED_START.' -'.$tradingDays.' days')), $summary['plan']['windows'][0]['start']);
        }

        $this->assertNotSame($boundaries[10], $boundaries[11], 'the fixture must move the boundary by one trading day');
    }

    /**
     * The blocked-run diagnostic is telemetry too (`MD-S053-R0225`): a run that stopped at the
     * source still states the boundary it was working from.
     */
    public function test_a_blocked_acquisition_still_records_the_boundary_in_its_diagnostic(): void
    {
        $orchestrator = $this->orchestrator();
        $dir = $this->outputDir();

        $orchestrator->execute(self::REQUESTED_START, self::REQUESTED_END, 'api', ['output_dir' => $dir]);

        $diagnostic = json_decode((string) file_get_contents($dir.'/source_acquisition_diagnostics.json'), true);
        $this->assertIsArray($diagnostic);
        $this->assertSame($this->governedBoundary(self::REQUESTED_START, 10), $diagnostic['warmup_start']);
        $this->assertSame(self::REQUESTED_START, $diagnostic['requested_start']);
        $this->assertSame(self::REQUESTED_END, $diagnostic['requested_end']);
        $this->assertSame('range_window', $diagnostic['source_acquisition_mode']);
    }

    /**
     * Scenario => message the backfill must stop with. The arrangement lives in
     * `arrangeUnprovableCalendar()` because a data provider runs before the test is booted.
     *
     * @return array<string,array{0:string,1:string}>
     */
    public function unprovableCalendars(): array
    {
        return [
            // The only requested dates are exchange closures: nothing the calendar can anchor.
            'requested range holds no trading date' => ['closures_only', 'Lifecycle backfill requires at least one requested trading date'],
            // A trading day recorded without verified provenance is not a trading day the
            // platform can stand on.
            'requested dates have unverified provenance' => ['unverified', 'Lifecycle backfill requires at least one requested trading date'],
            // The governed calendar itself is absent.
            'calendar revision foundation is missing' => ['foundation_missing', 'MARKET_CALENDAR_REVISION_FOUNDATION_MISSING'],
            // Two live revisions for one date: the calendar contradicts itself.
            'calendar holds conflicting live revisions' => ['conflict', 'MARKET_CALENDAR_REVISION_CONFLICT'],
        ];
    }

    /** @return array{0:string,1:string} the requested range for the scenario */
    private function arrangeUnprovableCalendar(string $scenario): array
    {
        switch ($scenario) {
            case 'closures_only':
                return ['2026-03-17', '2026-03-18'];
            case 'unverified':
                foreach (['2026-03-30', '2026-03-31', '2026-04-01'] as $date) {
                    $this->seedVerifiedMarketCalendarDate($date, true, ['provenance_tier' => 'PROVISIONAL']);
                }

                return [self::REQUESTED_START, self::REQUESTED_END];
            case 'foundation_missing':
                Schema::drop('md_market_calendar_revisions');

                return [self::REQUESTED_START, self::REQUESTED_END];
            case 'conflict':
                $row = (array) DB::table('md_market_calendar_revisions')->where('cal_date', '2026-03-31')->first();
                unset($row['calendar_revision_id']);
                $row['revision_uid'] = hash('sha256', 'conflicting-live-revision');
                DB::table('md_market_calendar_revisions')->insert($row);

                return [self::REQUESTED_START, self::REQUESTED_END];
        }

        throw new \InvalidArgumentException('unknown scenario '.$scenario);
    }

    /**
     * `MD-S053-R0221`, `R0223`: when the calendar cannot establish the boundary, the lifecycle
     * backfill stops before it asks the source for anything and before any publication step.
     *
     * @dataProvider unprovableCalendars
     */
    public function test_an_unprovable_calendar_blocks_before_acquisition_and_publication(string $scenario, string $expectedMessage): void
    {
        $orchestrator = $this->orchestrator();
        $dir = $this->outputDir();

        [$start, $end] = $this->arrangeUnprovableCalendar($scenario);

        try {
            $orchestrator->execute($start, $end, 'api', ['output_dir' => $dir]);
            $this->fail('the backfill proceeded although the calendar could not establish the warmup boundary');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
        }

        $this->assertSame([], $this->requests, 'the provider was asked for data although the calendar could not establish the boundary');
        $this->assertFileDoesNotExist($dir.'/market_data_backfill_lifecycle_summary.json', 'a run summary was written for a backfill that never started');
        $this->assertFileDoesNotExist($dir.'/source_acquisition_cache.json');
        foreach (['eod_runs', 'eod_publications', 'eod_current_publication_pointer', 'eod_bars'] as $table) {
            if (Schema::hasTable($table)) {
                $this->assertSame(0, DB::table($table)->count(), $table.' was written to by a backfill whose calendar was unprovable');
            }
        }
    }
}
