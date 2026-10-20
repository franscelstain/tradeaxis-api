<?php

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Console\Commands\MarketData\BackfillLifecycleCommand;
use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

class B19RangeBackfillMariaDbProofTest extends TestCase
{
    use UsesMarketDataMariaDb;

    private array $directories = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            foreach ((array) glob($directory.'/*') as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($directory);
        }
        Carbon::setTestNow();
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    private function outputDirectory(): string
    {
        $directory = sys_get_temp_dir().'/md_b19_mariadb_backfill_'.uniqid('', true);
        mkdir($directory, 0775, true);
        $this->directories[] = $directory;
        return $directory;
    }

    private function protectedCanonicalSnapshot(): array
    {
        $snapshot = [];
        foreach ([
            'eod_bars', 'eod_indicators', 'eod_eligibility', 'eod_runs', 'eod_publications',
            'eod_dataset_corrections', 'eod_current_publication_pointer', 'eod_bars_history',
            'eod_indicators_history', 'eod_eligibility_history',
        ] as $table) {
            $rows = DB::table($table)->get()->map(static function ($row) { return (array) $row; })->all();
            usort($rows, static function ($left, $right) { return strcmp(json_encode($left), json_encode($right)); });
            $snapshot[$table] = $rows;
        }

        return $snapshot;
    }

    private function seedVerifiedTradingDate(string $date): void
    {
        $ref = 'https://www.idx.co.id/test-calendar/'.$date;
        DB::table('md_market_calendar_revisions')->updateOrInsert(
            ['market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date],
            [
                'revision_uid' => hash('sha256', 'e030-calendar|'.$date),
                'timezone' => 'Asia/Jakarta',
                'is_trading_day' => 1,
                'is_half_day' => 0,
                'session_state' => 'COMPLETED',
                'session_open_at' => $date.' 09:00:00',
                'session_close_at' => $date.' 16:00:00',
                'completed_at' => $date.' 16:00:00',
                'recorded_at' => $date.' 17:00:00',
                'source_observation_id' => null,
                'supersedes_revision_id' => null,
                'source_ref' => $ref,
                'source_version' => 'idx-test-calendar-e030',
                'provenance_tier' => 'VERIFIED',
                'reconciled_at' => $date,
                'reconciliation_source_ref' => $ref,
            ]
        );
    }

    private function seedFollowingTradingDate(): void
    {
        $this->seedVerifiedTradingDate('2026-03-24');
    }

    private function bindApiResponse(callable $fetcher): void
    {
        $adapter = new PublicApiEodBarsAdapter($fetcher, new EquityProviderSymbolResolver(new TemporalIdentityRepository()));
        app()->instance(PublicApiEodBarsAdapter::class, $adapter);
        app()->instance(ApiBackfillRangeAcquisitionService::class, new ApiBackfillRangeAcquisitionService($adapter));
    }

    private function executeCommand(string $outputDirectory, array $options = []): array
    {
        $command = app(BackfillLifecycleCommand::class);
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);
        $code = $tester->execute(array_merge([
            'start_date' => '2026-03-24',
            'end_date' => '2026-03-24',
            '--source_mode' => 'api',
            '--output_dir' => $outputDirectory,
        ], $options));
        return [$code, $tester->getDisplay()];
    }

    private function responseFor(string $date, int $close = 105): array
    {
        $world = R0025SyntheticV2World::world();
        $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
        $body['chart']['result'][0]['timestamp'] = [Carbon::parse($date.' 12:00:00', 'Asia/Jakarta')->timestamp];
        $body['chart']['result'][0]['indicators']['quote'][0]['close'] = [$close];
        return ['status' => 200, 'headers' => ['Content-Type: application/json'], 'body' => json_encode($body)];
    }

    private function responseForDates(array $dates): array
    {
        $body = json_decode(file_get_contents(R0025SyntheticV2World::packagePath().'/inputs/provider_response.json'), true);
        $result =& $body['chart']['result'][0];
        $result['timestamp'] = array_map(static function ($date) {
            return Carbon::parse($date.' 12:00:00', 'Asia/Jakarta')->timestamp;
        }, $dates);
        foreach (['open' => 100, 'high' => 110, 'low' => 95, 'close' => 105, 'volume' => 1000] as $field => $value) {
            $result['indicators']['quote'][0][$field] = array_fill(0, count($dates), $value);
        }
        return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($body)];
    }

    public function test_range_window_runs_each_requested_date_in_order_without_publishing_warmup(): void
    {
        Carbon::setTestNow('2026-03-26 10:30:00');
        R0025SyntheticV2World::build();
        Carbon::setTestNow('2026-03-26 10:30:00');
        $this->seedVerifiedTradingDate('2026-03-24');
        $this->seedVerifiedTradingDate('2026-03-25');
        $this->seedVerifiedTradingDate('2026-03-20');
        $this->assertSame('SYNV2.JK', (new TemporalIdentityRepository())->resolveProviderContext('SYNV2', 'yahoo_finance', '2026-03-25')['provider_symbol']);
        $this->bindApiResponse(function () { return $this->responseForDates(['2026-03-20', '2026-03-24', '2026-03-25']); });
        $failureProxy = new E031FailOncePipelineProxy(app(MarketDataPipelineService::class), '2026-03-25');
        app()->instance(MarketDataPipelineService::class, $failureProxy);
        $priorWarmupPointers = DB::table('eod_current_publication_pointer')
            ->where('trade_date', '<', '2026-03-24')->orderBy('trade_date')->pluck('publication_id', 'trade_date')->all();
        $dir = $this->outputDirectory();
        [$firstCode, $firstOutput] = $this->executeCommand($dir, ['end_date' => '2026-03-25']);
        $first = json_decode(file_get_contents($dir.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertNotSame(0, $firstCode, $firstOutput.' cases='.json_encode($first['cases']));
        $this->assertSame('SUCCESS', $first['cases'][0]['status']);
        $this->assertSame('FAILED', $first['cases'][1]['import_status']);
        $this->assertSame('SKIPPED', $first['cases'][1]['promote_status']);
        $this->assertTrue($failureProxy->didInjectInterruption());
        $completedPointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->value('publication_id');
        $completedRunCount = (int) DB::table('eod_runs')->whereDate('trade_date_requested', '2026-03-24')->count();
        $completedPublicationRows = DB::table('eod_publications')->where('trade_date', '2026-03-24')->orderBy('publication_id')->get()->toJson();
        $completedHistoryRows = DB::table('eod_bars_history')->where('trade_date', '2026-03-24')->orderBy('publication_id')->orderBy('ticker_id')->get()->toJson();
        $completedBarRows = DB::table('eod_bars')->where('trade_date', '2026-03-24')->orderBy('ticker_id')->get()->toJson();
        $this->assertNotNull($completedPointer);

        [$resumeCode, $resumeOutput] = $this->executeCommand($dir, ['end_date' => '2026-03-25', '--resume' => true]);
        $summary = json_decode(file_get_contents($dir.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertSame(0, $resumeCode, $resumeOutput.' cases='.json_encode($summary['cases']));
        $this->assertSame(['2026-03-24', '2026-03-25'], array_column($summary['cases'], 'requested_date'));
        $this->assertSame('SKIPPED_VERIFIED', $summary['cases'][0]['status']);
        $this->assertTrue((bool) ($summary['cases'][0]['resume_skip'] ?? false));
        $this->assertSame('SUCCESS', $summary['cases'][1]['status']);
        $this->assertSame($completedPointer, DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->value('publication_id'));
        $this->assertSame($completedRunCount, (int) DB::table('eod_runs')->whereDate('trade_date_requested', '2026-03-24')->count());
        $this->assertSame($completedPublicationRows, DB::table('eod_publications')->where('trade_date', '2026-03-24')->orderBy('publication_id')->get()->toJson());
        $this->assertSame($completedHistoryRows, DB::table('eod_bars_history')->where('trade_date', '2026-03-24')->orderBy('publication_id')->orderBy('ticker_id')->get()->toJson());
        $this->assertSame($completedBarRows, DB::table('eod_bars')->where('trade_date', '2026-03-24')->orderBy('ticker_id')->get()->toJson());
        $runIds = array_column($summary['cases'], 'run_id');
        $this->assertCount(2, array_unique($runIds));
        foreach (['2026-03-24', '2026-03-25'] as $index => $date) {
            $run = DB::table('eod_runs')->where('run_id', $runIds[$index])->first();
            $this->assertSame($date, $run->trade_date_requested);
            $this->assertSame('READABLE', $run->publishability_state);
            $this->assertSame(1, (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->count());
            foreach (['source_acquisition_mode' => 'range_window', 'source_acquisition_batch_id' => 'API_20260324_20260325_001',
                'source_window_start' => '2026-02-20', 'source_window_end' => '2026-03-25',
                'warmup_start' => '2026-02-20', 'requested_start' => '2026-03-24', 'requested_end' => '2026-03-25',
                'source_acquisition_state' => 'SUCCESS'] as $field => $expected) {
                $this->assertStringContainsString($field.'='.$expected, (string) $run->notes,
                    $field.' must persist on the independently identified '.$date.' run.');
            }
            $this->assertStringContainsString('expected_ticker_count=1', (string) $run->notes);
            $this->assertStringContainsString('success_ticker_count=1', (string) $run->notes);
            $this->assertStringContainsString('failed_ticker_count=0', (string) $run->notes);
        }
        $this->assertSame($priorWarmupPointers, DB::table('eod_current_publication_pointer')
            ->where('trade_date', '<', '2026-03-24')->orderBy('trade_date')->pluck('publication_id', 'trade_date')->all(),
            'Acquired warmup dates outside the requested range must not become publication targets.');
        $this->assertSame('range_window', $summary['source_acquisition_mode']);
        $this->assertSame('2026-03-24', $summary['requested_start']);
        $this->assertSame('2026-03-25', $summary['requested_end']);
        $warmupCase = collect($summary['warmup_cases'])->firstWhere('requested_date', '2026-03-20');
        $this->assertNotNull($warmupCase, 'The returned pre-request source row must be retained as an explicit warmup case.');
        $this->assertSame('SUCCESS', $warmupCase['import_status']);
        $this->assertSame('SKIPPED_WARMUP_ONLY', $warmupCase['promote_status']);
        $warmupRun = DB::table('eod_runs')->where('run_id', $warmupCase['run_id'])->first();
        $this->assertSame('2026-03-20', $warmupRun->trade_date_requested);
        $this->assertNotSame('READABLE', $warmupRun->publishability_state);
        $this->assertStringContainsString('source_acquisition_mode=range_window', (string) $warmupRun->notes);
        $this->assertStringContainsString('requested_start=2026-03-24', (string) $warmupRun->notes);
        $this->assertStringContainsString('requested_end=2026-03-25', (string) $warmupRun->notes);
        $warmupObservationId = DB::table('md_source_observation_rows')->where('trade_date', '2026-03-20')
            ->orderByDesc('source_observation_id')->value('source_observation_id');
        $warmupObservation = $warmupObservationId === null ? null : DB::table('md_source_observations')
            ->where('source_observation_id', $warmupObservationId)->first();
        $this->assertNotNull($warmupObservation, 'Warmup support rows must retain a source observation identity.');
        $this->assertSame(1, (int) DB::table('md_source_observation_rows')
            ->where('source_observation_id', $warmupObservation->source_observation_id)->where('trade_date', '2026-03-20')->count());
        $warmupBar = DB::table('eod_bars')->where('trade_date', '2026-03-20')->first();
        $this->assertNotNull($warmupBar, 'The pre-request support bar must be retained for downstream rolling calculations.');
        $this->assertSame((int) $warmupObservation->source_observation_id, (int) $warmupBar->source_observation_id);
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->count());
        $implicitSeed = app(\App\Infrastructure\Persistence\MarketData\EodRunRepository::class)
            ->findLatestForRequestedDate('2026-03-20', (string) $warmupRun->source);
        $this->assertNotNull($implicitSeed, 'The implicit source/date lookup must resolve the persisted warmup run.');
        $this->assertSame((int) $warmupRun->run_id, (int) $implicitSeed->run_id);
        try {
            $failureProxy->delegate()->promoteSingleDay('2026-03-20', 'api', (int) $warmupRun->run_id);
            $this->fail('Direct promotion of a warmup-only range date unexpectedly returned.');
        } catch (\RuntimeException $error) {
            $this->assertSame('RANGE_WINDOW_WARMUP_DATE_PROMOTION_REFUSED', $error->getMessage());
        }
        $runsBeforeImplicitAttempt = (int) DB::table('eod_runs')->count();
        $implicitRunFailure = null;
        try {
            // Use the persisted source identity so the normal implicit seed lookup resolves this
            // exact warmup run rather than exercising the separate missing-run refusal.
            $failureProxy->delegate()->promoteSingleDay('2026-03-20', (string) $warmupRun->source);
        } catch (\RuntimeException $error) {
            $implicitRunFailure = $error->getMessage();
        }
        $this->assertSame($runsBeforeImplicitAttempt, (int) DB::table('eod_runs')->count(),
            'A refused implicit warmup promotion must not create a derived publication-authority run.');
        $this->assertSame('RANGE_WINDOW_WARMUP_DATE_PROMOTION_REFUSED', $implicitRunFailure,
            'Implicit-run promotion must resolve and refuse the warmup-only range date.');
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-20')->count(),
            'A direct promotion attempt cannot turn a support-only warmup run into a readable target.');
    }

    private function oracleCanonicalHash(array $document): string
    {
        $canonicalize = function ($value) use (&$canonicalize) {
            if ($value === null) return '';
            if (! is_array($value)) return $value;
            $list = $value === [] || array_keys($value) === range(0, count($value) - 1);
            foreach ($value as $key => $item) $value[$key] = $canonicalize($item);
            if ($list) usort($value, static function ($a, $b) { return strcmp(json_encode($a), json_encode($b)); });
            else ksort($value, SORT_STRING);
            return $value;
        };
        return hash('sha256', json_encode($canonicalize($document), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function oracleSemanticEntry(int $id): array
    {
        $row = DB::table('md_source_observations')->where('source_observation_id', $id)->first();
        $this->assertNotNull($row, 'The expected semantic manifest cannot cite an unavailable observation.');
        $text = static function ($value) { $value = trim((string) ($value ?? '')); return $value === '' ? null : $value; };
        $lower = static function ($value) use ($text) { $value = $text($value); return $value === null ? null : strtolower($value); };
        $date = static function ($value) use ($text) { $value = $text($value); return $value === null ? null : substr($value, 0, 10); };
        $time = static function ($value) use ($text) { $value = $text($value); return $value === null ? null : substr($value, 0, 19); };
        $parent = $row->parent_observation_id === null ? null : $this->oracleCanonicalHash([
            'schema_version' => 'market-data-observation-entry/v2',
            'entry' => $this->oracleSemanticEntry((int) $row->parent_observation_id),
        ]);
        $supersedes = $row->supersedes_observation_id === null ? null : $this->oracleCanonicalHash([
            'schema_version' => 'market-data-observation-entry/v2',
            'entry' => $this->oracleSemanticEntry((int) $row->supersedes_observation_id),
        ]);
        return [
            'payload_hash' => $lower($row->payload_hash), 'payload_ref' => $text($row->payload_ref),
            'provider' => $text($row->provider), 'provider_symbol' => $text($row->provider_symbol),
            'mapping_revision' => $text($row->mapping_revision),
            'requested_trade_date' => $date($row->requested_trade_date),
            'requested_start_date' => $date($row->requested_start_date),
            'requested_end_date' => $date($row->requested_end_date),
            'source_timestamp' => $time($row->source_timestamp), 'acquired_at' => $time($row->acquired_at),
            'adapter_version' => $text($row->adapter_version),
            'schema_fingerprint' => $lower($row->schema_fingerprint),
            'provider_schema_version' => $text($row->provider_schema_version),
            'outcome_state' => $text($row->outcome_state),
            'validation_state' => $text($row->validation_state), 'reason_code' => $text($row->reason_code),
            'parent_entry_hash' => $parent, 'supersedes_entry_hash' => $supersedes,
        ];
    }

    private function oracleCompatibilityManifest(array $ids): string
    {
        $observations = DB::table('md_source_observations')->whereIn('source_observation_id', $ids)
            ->orderBy('observation_uid')->get();
        $this->assertCount(count($ids), $observations, 'The expected acquisition set is incomplete.');
        $lines = [];
        foreach ($observations as $row) {
            $lines[] = implode('|', [
                $row->observation_uid, $row->parent_observation_id ?? '', $row->source_mode ?? '',
                $row->provider ?? '', $row->provider_symbol ?? '', $row->provider_mapping_id ?? '',
                $row->requested_trade_date, $row->payload_hash ?? '', $row->schema_fingerprint ?? '',
                $row->adapter_version, $row->outcome_state, $row->reason_code ?? '',
            ]);
        }
        return hash('sha256', implode("\n", $lines));
    }

    public function test_real_mariadb_backfill_command_acquires_and_publishes_one_requested_date(): void
    {
        Carbon::setTestNow('2026-03-25 10:30:00');
        R0025SyntheticV2World::build();
        $this->seedFollowingTradingDate();
        $calls = 0;
        $this->bindApiResponse(function () use (&$calls) {
            $calls++;
            return $this->responseFor('2026-03-24');
        });

        $outputDirectory = $this->outputDirectory();
        [$code, $output] = $this->executeCommand($outputDirectory);
        $this->assertSame(0, $code, $output);
        $summary = json_decode(file_get_contents($outputDirectory.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertSame('2026-03-24', $summary['requested_start']);
        $this->assertSame('2026-03-24', $summary['requested_end']);
        $this->assertSame('range_window', $summary['source_acquisition_mode']);
        $this->assertCount(1, $summary['cases']);
        $case = $summary['cases'][0];
        $this->assertSame('2026-03-24', $case['requested_date']);
        $this->assertNotEmpty($case['run_id']);
        $run = DB::table('eod_runs')->where('run_id', $case['run_id'])->first();
        $publication = DB::table('eod_publications')->where('run_id', $case['run_id'])->first();
        $this->assertNotNull($publication, json_encode($case));
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame(1, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->where('publication_id', $publication->publication_id)->count());
        $observations = DB::table('md_source_observations')->where('requested_trade_date', '2026-03-24')->whereIn('outcome_state', ['ACCEPTED', 'NORMALIZED'])->get();
        $this->assertNotEmpty($observations, 'Successful API acquisition must persist its source observation in MariaDB. '.$output.' observations='.json_encode(DB::table('md_source_observations')->get(['requested_trade_date', 'outcome_state', 'provider_symbol'])));
        $this->assertSame('2026-03-24', $observations[0]->requested_trade_date);
        $this->assertGreaterThanOrEqual(2, $calls, 'Range acquisition and requested-date run must both execute through the API adapter.');
    }

    public function test_failed_provider_checkpoint_resumes_without_faking_readability_then_publishes_recovered_rows(): void
    {
        Carbon::setTestNow('2026-03-25 10:30:00');
        R0025SyntheticV2World::build();
        $this->seedFollowingTradingDate();
        $available = false;
        $this->bindApiResponse(function () use (&$available) {
            if (! $available) {
                return ['status' => 503, 'headers' => ['Content-Type: application/json'], 'body' => '{"error":"bounded provider outage"}'];
            }
            return $this->responseFor('2026-03-24');
        });
        config(['market_data.provider.api_retry_max' => 0, 'market_data.provider.api_backoff_ms' => 0]);

        $outputDirectory = $this->outputDirectory();
        $priorPointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id');
        $priorPublicationBytes = json_encode(DB::table('eod_publications')->where('publication_id', $priorPointer)->first());
        $priorHistoryBytes = DB::table('eod_bars_history')->where('publication_id', $priorPointer)->orderBy('ticker_id')->get()->toJson();
        [$firstCode, $firstOutput] = $this->executeCommand($outputDirectory);
        $this->assertFileExists($outputDirectory.'/source_acquisition_checkpoint.json');
        $checkpoint = json_decode(file_get_contents($outputDirectory.'/source_acquisition_checkpoint.json'), true);
        $this->assertNotEmpty($checkpoint);
        $firstObservationIds = DB::table('md_source_observations')->where('requested_trade_date', '2026-03-24')
            ->pluck('source_observation_id')->map(static function ($id) { return (int) $id; })->all();
        $firstObservationBytes = DB::table('md_source_observations')->whereIn('source_observation_id', $firstObservationIds)
            ->orderBy('source_observation_id')->get()->toJson();
        $failedSummary = json_decode(file_get_contents($outputDirectory.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertSame('SYSTEMIC_FAILED', $failedSummary['source_acquisition_state']);
        $this->assertSame('2026-03-24', $failedSummary['requested_start']);
        $this->assertSame('2026-03-24', $failedSummary['requested_end']);
        $this->assertSame('2026-02-20', $failedSummary['failed_window_start']);
        $this->assertSame('2026-03-24', $failedSummary['failed_window_end']);
        $this->assertSame(1, (int) $failedSummary['failed_ticker_count']);
        $this->assertNotEmpty($failedSummary['source_acquisition_batch_id']);
        $diagnostic = json_decode(file_get_contents($outputDirectory.'/source_acquisition_diagnostics.json'), true);
        $this->assertSame('SYSTEMIC_FAILED', $diagnostic['source_acquisition_state']);
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->count(), $firstOutput);
        $this->assertSame($priorPointer, DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id'));
        $this->assertSame($priorPublicationBytes, json_encode(DB::table('eod_publications')->where('publication_id', $priorPointer)->first()));
        $this->assertSame($priorHistoryBytes, DB::table('eod_bars_history')->where('publication_id', $priorPointer)->orderBy('ticker_id')->get()->toJson());

        [$stillFailedCode, $stillFailedOutput] = $this->executeCommand($outputDirectory, ['--resume' => true, '--only-failed' => true]);
        $stillFailedSummary = json_decode(file_get_contents($outputDirectory.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertSame([], $stillFailedSummary['cases'], 'A failed retry must not enter derived or publication execution.');
        $this->assertSame('NOOP', $stillFailedSummary['recovered_row_apply_state']);
        $this->assertSame('NOOP', $stillFailedSummary['indicator_reprocess_execution_state']);
        $this->assertSame('NOOP', $stillFailedSummary['eligibility_reprocess_execution_state']);
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->count(), $stillFailedOutput);
        $this->assertNotSame(0, $stillFailedCode, 'A retry with a still unavailable provider cannot be reported as a successful date.');
        $this->assertSame($priorPointer, DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id'));
        $this->assertSame($priorPublicationBytes, json_encode(DB::table('eod_publications')->where('publication_id', $priorPointer)->first()));
        $this->assertSame($priorHistoryBytes, DB::table('eod_bars_history')->where('publication_id', $priorPointer)->orderBy('ticker_id')->get()->toJson());
        $this->assertSame(0, (int) DB::table('eod_indicators')->where('trade_date', '2026-03-24')->count());
        $this->assertSame(0, (int) DB::table('eod_eligibility')->where('trade_date', '2026-03-24')->count());
        $this->assertGreaterThan(count($firstObservationIds), (int) DB::table('md_source_observations')
            ->where('requested_trade_date', '2026-03-24')->count(), 'The failed retry must append its own source attempt.');
        $this->assertSame($firstObservationBytes, DB::table('md_source_observations')->whereIn('source_observation_id', $firstObservationIds)
            ->orderBy('source_observation_id')->get()->toJson(), 'A retry cannot rewrite earlier source observations.');

        $available = true;
        [$resumeCode, $resumeOutput] = $this->executeCommand($outputDirectory, ['--resume' => true, '--only-failed' => true]);
        $resumeSummary = json_decode(file_get_contents($outputDirectory.'/market_data_backfill_lifecycle_summary.json'), true);
        $recoveredRunId = (int) ($resumeSummary['cases'][0]['run_id'] ?? 0);
        $debug = [
            'run' => DB::table('eod_runs')->where('run_id', $recoveredRunId)->first(['run_id', 'request_mode', 'publication_id', 'observation_manifest_hash', 'semantic_observation_manifest_hash', 'publishability_state']),
            'publication' => DB::table('eod_publications')->where('run_id', $recoveredRunId)->first(['publication_id', 'run_id', 'observation_manifest_hash', 'seal_state']),
            'observations' => DB::table('md_source_observations')->where('requested_trade_date', '2026-03-24')->orderByDesc('source_observation_id')->limit(4)->get(['source_observation_id', 'run_id', 'outcome_state', 'reason_code']),
            'failure' => $resumeSummary['cases'][0]['publication_reprocess_summary']['failure_reason_code'] ?? null,
        ];
        $this->assertSame(0, $resumeCode, $resumeOutput.' first_exit='.$firstCode.' persisted='.json_encode($debug));
        $this->assertSame('EXECUTED', $resumeSummary['cases'][0]['indicator_reprocess_execution_state']);
        $this->assertSame('EXECUTED', $resumeSummary['cases'][0]['eligibility_reprocess_execution_state']);
        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->first();
        $this->assertNotNull($pointer, 'A recovered successful source response should resume the requested date through normal publication.');
        $publication = DB::table('eod_publications')->where('publication_id', $pointer->publication_id)->first();
        $this->assertSame('SEALED', $publication->seal_state);
        $this->assertSame('2026-03-24', $publication->trade_date);
        $this->assertGreaterThan(0, (int) DB::table('md_source_observations')->where('requested_trade_date', '2026-03-24')->count());

        $accepted = DB::table('md_source_observations')->where('requested_trade_date', '2026-03-24')
            ->where('acquisition_batch_id', $resumeSummary['source_acquisition_batch_id'])
            ->whereIn('outcome_state', ['ACCEPTED', 'NORMALIZED'])->get();
        $this->assertCount(1, $accepted, 'The retry world must contain exactly one accepted acquisition outcome.');
        $acceptedId = (int) $accepted[0]->source_observation_id;
        $this->assertNotNull(DB::table('md_source_observations')->where('source_observation_id', $accepted[0]->parent_observation_id)
            ->where('outcome_state', 'CAPTURED')->first());
        $this->assertSame(1, (int) DB::table('md_source_observation_rows')->where('source_observation_id', $acceptedId)
            ->where('trade_date', '2026-03-24')->count());
        $recoveredBar = DB::table('eod_bars')->where('trade_date', '2026-03-24')->first();
        $this->assertSame($acceptedId, (int) $recoveredBar->source_observation_id);
        $this->assertSame((int) DB::table('md_source_observation_rows')->where('source_observation_id', $acceptedId)
            ->value('listing_id'), (int) $recoveredBar->listing_id);
        $this->assertSame($priorPointer, DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-23')->value('publication_id'));
        $this->assertSame($priorPublicationBytes, json_encode(DB::table('eod_publications')->where('publication_id', $priorPointer)->first()));
        $this->assertSame($priorHistoryBytes, DB::table('eod_bars_history')->where('publication_id', $priorPointer)->orderBy('ticker_id')->get()->toJson());
        $captures = DB::table('md_run_input_captures')->where('run_id', $recoveredRunId)
            ->where('component_key', 'source_observations')->get();
        $ingress = array_values(array_filter($captures->all(), static function ($capture) {
            $selection = json_decode($capture->selection_context_json, true);
            return ($selection['input_route'] ?? null) === 'ingestRecoveredRowsPartial';
        }));
        $this->assertCount(1, $ingress, 'The recovered run must retain its exact selected ingress population.');
        $inputRows = json_decode($ingress[0]->semantic_payload_json, true)['rows'];
        $this->assertSame([$acceptedId], array_values(array_unique(array_map('intval', array_column($inputRows, 'source_observation_id')))));

        $expectedCompatibility = $this->oracleCompatibilityManifest([$acceptedId]);
        $expectedSemantic = $this->oracleCanonicalHash([
            'schema_version' => 'market-data-observation-manifest/v2',
            'entries' => [$this->oracleSemanticEntry($acceptedId)],
        ]);
        $recoveredRun = DB::table('eod_runs')->where('run_id', $recoveredRunId)->first();
        $candidate = DB::table('eod_publications')->where('run_id', $recoveredRunId)->first();
        $this->assertSame($expectedCompatibility, $recoveredRun->observation_manifest_hash);
        $this->assertSame($expectedCompatibility, $candidate->observation_manifest_hash);
        $this->assertSame($expectedSemantic, $recoveredRun->semantic_observation_manifest_hash);
        $this->assertSame($expectedSemantic, $candidate->semantic_observation_manifest_hash);
        $this->assertNotSame($expectedCompatibility, $expectedSemantic);
        $this->assertSame($expectedCompatibility, $publication->observation_manifest_hash);
        $this->assertSame($expectedSemantic, $publication->semantic_observation_manifest_hash);
        $this->assertTrue((new EodPublicationRepository())->assertPublicationManifestHashValid((int) $publication->publication_id));
        $runCount = (int) DB::table('eod_runs')->whereDate('trade_date_requested', '2026-03-24')->count();
        [$noopCode] = $this->executeCommand($outputDirectory, ['--resume' => true, '--only-failed' => true]);
        $noopSummary = json_decode(file_get_contents($outputDirectory.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertSame(0, $noopCode);
        $this->assertSame('NO_FAILED_CHECKPOINT', $noopSummary['source_acquisition_state']);
        $this->assertSame($runCount, (int) DB::table('eod_runs')->whereDate('trade_date_requested', '2026-03-24')->count());
        $this->assertSame((int) $publication->publication_id, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->value('publication_id'));
    }

    public function test_invalid_source_mode_refuses_before_creating_a_run(): void
    {
        Carbon::setTestNow('2026-03-25 10:30:00');
        R0025SyntheticV2World::build();
        $this->seedFollowingTradingDate();
        $outputDirectory = $this->outputDirectory();

        [$code, $output] = $this->executeCommand($outputDirectory, ['--source_mode' => 'manual_api_mix']);

        $this->assertNotSame(0, $code, $output);
        $this->assertSame(0, (int) DB::table('eod_runs')->whereDate('trade_date_requested', '2026-03-24')->count());
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->count());
    }

    public function test_manual_file_historical_fill_executes_the_registered_lifecycle_and_rejects_missing_input(): void
    {
        app()->instance(\App\Application\MarketData\Services\ManualSourceInputContext::class,
            new \App\Application\MarketData\Services\ManualSourceInputContext());
        R0025SyntheticV2World::build();
        config()->set('market_data_runtime.artifact_hash_profile', \App\Application\MarketData\Services\ArtifactSemanticHashService::LEGACY_PROFILE_V1);
        $this->seedFollowingTradingDate();
        $directory = $this->outputDirectory();
        [$missingCode] = $this->executeCommand($directory, ['--source_mode' => 'manual_file', '--input_file' => $directory.'/missing.csv']);
        $this->assertNotSame(0, $missingCode);
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->count());

        $file = $directory.'/historical.csv';
        file_put_contents($file, "ticker_code,trade_date,open,high,low,close,volume,canonical_source,source_row_ref\nSYNV2,2026-03-24,100,110,95,105,1000,YAHOO_FINANCE,manual:SYNV2:2026-03-24\n");
        [$code, $output] = $this->executeCommand($directory, ['--source_mode' => 'manual_file', '--input_file' => $file]);
        $this->assertSame(0, $code, $output.' case='.json_encode(json_decode(file_get_contents($directory.'/market_data_backfill_lifecycle_summary.json'), true)['cases'][0] ?? []).' file='.(file_exists($file) ? 'exists' : 'missing'));
        $summary = json_decode(file_get_contents($directory.'/market_data_backfill_lifecycle_summary.json'), true);
        $this->assertSame('manual_file', $summary['source_mode']);
        $this->assertSame('2026-03-24', $summary['cases'][0]['requested_date']);
        $run = DB::table('eod_runs')->where('run_id', $summary['cases'][0]['run_id'])->first();
        $this->assertSame('READABLE', $run->publishability_state);
        $this->assertSame(1, (int) DB::table('eod_current_publication_pointer')->where('trade_date', '2026-03-24')->count());
    }

    public function observationIdentityMutations(): array
    {
        return [
            'substituted accepted observation' => ['substitute'],
            'missing observation' => ['missing'],
            'mismatched capture identity' => ['capture'],
            'mismatched payload identity' => ['payload'],
            'mismatched normalized value' => ['close'],
            'mismatched source row reference' => ['row_ref'],
        ];
    }

    /** @dataProvider observationIdentityMutations */
    public function test_recovered_rows_cannot_substitute_an_unrelated_accepted_observation(string $mutation): void
    {
        Carbon::setTestNow('2026-03-25 10:30:00');
        $world = R0025SyntheticV2World::build();
        $this->seedFollowingTradingDate();
        $ticker = (string) DB::table('tickers')->where('ticker_id', $world['ticker_id'])->value('ticker_code');
        $date = '2026-03-24';

        $this->bindApiResponse(function () use ($date) { return $this->responseFor($date, 105); });
        $first = app(ApiBackfillRangeAcquisitionService::class)->acquire($date, $date, $date, [$date], [$ticker],
            ['source_acquisition_batch_id' => 'E030_ORIGINAL']);
        $this->bindApiResponse(function () use ($date) { return $this->responseFor($date, 106); });
        $second = app(ApiBackfillRangeAcquisitionService::class)->acquire($date, $date, $date, [$date], [$ticker],
            ['source_acquisition_batch_id' => 'E030_UNRELATED']);
        $original = $first['rows_by_trade_date'][$date][0];
        $unrelated = $second['rows_by_trade_date'][$date][0];
        $this->assertNotSame($original['source_observation_id'], $unrelated['source_observation_id']);
        $this->assertNotSame((string) $original['close'], (string) $unrelated['close']);
        switch ($mutation) {
            case 'substitute': $original['source_observation_id'] = $unrelated['source_observation_id']; break;
            case 'missing': $original['source_observation_id'] = 999999999; break;
            case 'capture': $original['source_capture_observation_id'] = $unrelated['source_capture_observation_id']; break;
            case 'payload': $original['source_payload_hash'] = str_repeat('0', 64); break;
            case 'close': $original['close'] = 106; break;
            case 'row_ref': $original['source_row_ref'] = 'api:unrelated'; break;
        }

        try {
            $run = $world['pipeline']->applyRecoveredRowsPartial($date, 'api', [$original]);
            $this->assertNotSame('READABLE', $run->publishability_state);
            $this->assertSame(0, (int) DB::table('eod_bars')->where('trade_date', $date)->count(),
                'An accepted observation for different values must not authorize this canonical row.');
        } catch (\RuntimeException $error) {
            $this->assertMatchesRegularExpression('/SOURCE_OBSERVATION|RUN_SOURCE_NO_VALID_DATA/', $error->getMessage());
        }
        $this->assertSame(0, (int) DB::table('eod_current_publication_pointer')->where('trade_date', $date)->count());
    }

    public function test_snapshot_retention_dry_run_and_apply_only_remove_expired_snapshot_rows(): void
    {
        Carbon::setTestNow('2026-03-25 10:30:00');
        R0025SyntheticV2World::build();
        $this->seedFollowingTradingDate();
        $this->bindApiResponse(function () { return $this->responseFor('2026-03-24'); });
        $outputDirectory = $this->outputDirectory();
        [$code, $output] = $this->executeCommand($outputDirectory);
        $this->assertSame(0, $code, $output);

        $tickerId = (int) DB::table('tickers')->orderBy('ticker_id')->value('ticker_id');
        foreach ([['2026-02-01', '09:10:00'], ['2026-03-24', '09:10:00']] as [$date, $clock]) {
            DB::table('md_session_snapshots')->insert([
                'trade_date' => $date, 'snapshot_slot' => 'OPEN_CHECK', 'ticker_id' => $tickerId,
                'captured_at' => $date.' '.$clock, 'last_price' => '100', 'prev_close' => '99',
                'chg_pct' => '1.0101010100', 'volume' => 1000, 'day_high' => '101', 'day_low' => '98',
                'source' => 'manual_file', 'run_id' => null, 'reason_code' => null, 'error_note' => null,
                'created_at' => $date.' 10:00:00', 'updated_at' => $date.' 10:00:00',
            ]);
        }
        DB::table('eod_dataset_corrections')->insert([
            'trade_date' => '2026-03-24', 'correction_reason_code' => 'DATA_CORRECTION_REQUIRED',
            'correction_reason_note' => 'retention guard protected correction-trail sentinel', 'status' => 'REQUESTED',
            'requested_by' => 'e031-retention-guard', 'requested_at' => '2026-03-25 10:30:00',
            'created_at' => '2026-03-25 10:30:00', 'updated_at' => '2026-03-25 10:30:00',
        ]);
        $this->assertGreaterThan(0, (int) DB::table('eod_indicators')->count());
        $this->assertGreaterThan(0, (int) DB::table('eod_eligibility')->count());
        $this->assertGreaterThan(0, (int) DB::table('eod_dataset_corrections')->count());
        $canonicalBefore = $this->protectedCanonicalSnapshot();

        $command = app(\App\Console\Commands\MarketData\PurgeSessionSnapshotCommand::class);
        $command->setLaravel($this->app);
        $dry = new CommandTester($command);
        $dry->execute(['--before_date' => '2026-02-28', '--output_dir' => $outputDirectory]);
        $this->assertSame(2, (int) DB::table('md_session_snapshots')->count());
        $this->assertSame(1, (int) json_decode(file_get_contents($outputDirectory.'/market_data_session_snapshot_purge_summary.json'), true)['candidate_rows']);
        $this->assertSame($canonicalBefore, $this->protectedCanonicalSnapshot(), 'Dry-run must leave every protected canonical family unchanged.');

        $apply = new CommandTester($command);
        $apply->execute(['--before_date' => '2026-02-28', '--output_dir' => $outputDirectory, '--apply' => true]);
        $this->assertSame(1, (int) DB::table('md_session_snapshots')->count());
        $this->assertSame('2026-03-24', DB::table('md_session_snapshots')->value('trade_date'));
        $canonicalAfter = $this->protectedCanonicalSnapshot();
        $this->assertSame($canonicalBefore, $canonicalAfter, 'Session snapshot maintenance must not mutate canonical publication state.');
        $this->assertFileExists($outputDirectory.'/market_data_session_snapshot_purge_summary.json');
    }
}

/** Injects one transient date-scoped import interruption while delegating all successful work to the real MariaDB pipeline. */
class E031FailOncePipelineProxy extends MarketDataPipelineService
{
    private $delegate;
    private $failDate;
    private $failed = false;

    public function didInjectInterruption(): bool
    {
        return $this->failed;
    }

    public function delegate(): MarketDataPipelineService
    {
        return $this->delegate;
    }

    public function __construct(MarketDataPipelineService $delegate, string $failDate)
    {
        $this->delegate = $delegate;
        $this->failDate = $failDate;
    }

    public function importDailyFromAcquiredRows($requestedDate, $sourceMode, array $sourceRows, array $sourceAcquisition = [], $correctionId = null, $requestMode = 'import_only')
    {
        if (! $this->failed && (string) $requestedDate === $this->failDate) {
            $this->failed = true;
            throw new RuntimeException('E032 transient interruption before requested-date import');
        }

        return $this->delegate->importDailyFromAcquiredRows($requestedDate, $sourceMode, $sourceRows, $sourceAcquisition, $correctionId, $requestMode);
    }

    public function promoteDaily($requestedDate, $sourceMode = null, $runId = null, $correctionId = null, $promoteMode = null, $forceReplace = false, $forceReplaceReason = null)
    {
        return $this->delegate->promoteDaily($requestedDate, $sourceMode, $runId, $correctionId, $promoteMode, $forceReplace, $forceReplaceReason);
    }
}

