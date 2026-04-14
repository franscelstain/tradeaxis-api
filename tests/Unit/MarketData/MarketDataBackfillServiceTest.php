<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MarketDataBackfillServiceTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bindMarketDataConfig();
    }

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        m::close();

        parent::tearDown();
    }

    public function test_execute_runs_daily_pipeline_for_each_trading_date_and_writes_summary()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-20', '2026-03-24')->andReturn([
            '2026-03-20',
            '2026-03-23',
            '2026-03-24',
        ]);

        foreach ([1001 => '2026-03-20', 1002 => '2026-03-23', 1003 => '2026-03-24'] as $runId => $date) {
            $run = (object) [
                'run_id' => $runId,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'trade_date_effective' => $date,
                'notes' => 'source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=2; source_success_after_retry=yes; source_final_http_status=200; source_final_reason_code=RUN_SOURCE_TIMEOUT',
            ];
            $pipeline->shouldReceive('runDaily')->once()->with($date, 'manual_file', null)->andReturn($run);
        }

        $runs->shouldNotReceive('findLatestForRequestedDate');

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs);
        $summary = $service->execute('2026-03-20', '2026-03-24', 'manual_file', $outputDir, false);

        $this->assertTrue($summary['all_passed']);
        $this->assertSame('range', $summary['request_mode']);
        $this->assertCount(3, $summary['cases']);
        $this->assertSame('API_FREE', $summary['cases'][0]['source_name']);
        $this->assertArrayNotHasKey('source_attempt_event_type', $summary['cases'][0]);
        $this->assertArrayNotHasKey('source_attempt_count', $summary['cases'][0]);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $summary['cases'][0]['source_summary']);

        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);
        $this->assertSame('API_FREE', $summaryFile['cases'][0]['source_name']);
        $this->assertArrayNotHasKey('source_attempt_event_type', $summaryFile['cases'][0]);
        $this->assertArrayNotHasKey('source_attempt_count', $summaryFile['cases'][0]);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $summaryFile['cases'][0]['source_summary']);
    }




    public function test_execute_recovers_source_summary_from_attempt_telemetry_when_notes_are_thin()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $evidence = m::mock(EodEvidenceRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-20', '2026-03-20')->andReturn([
            '2026-03-20',
        ]);

        $run = (object) [
            'run_id' => 1001,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => '2026-03-20',
            'notes' => 'source_name=API_FREE',
        ];

        $pipeline->shouldReceive('runSingleDay')->once()->with('2026-03-20', 'manual_file', null)->andReturn($run);
        $runs->shouldNotReceive('findLatestForRequestedDate');
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')->once()->with(1001)->andReturn([
            'event_id' => 9001,
            'event_time' => '2026-03-20 16:00:00',
            'event_type' => 'STAGE_COMPLETED',
            'provider' => 'generic',
            'source_name' => 'API_FREE',
            'timeout_seconds' => 15,
            'retry_max' => 3,
            'attempt_count' => 2,
            'success_after_retry' => 'yes',
            'final_http_status' => 200,
            'final_reason_code' => 'RUN_SOURCE_TIMEOUT',
            'captured_at' => '2026-03-20T16:00:00+07:00',
            'attempts' => [
                ['attempt_number' => 1, 'reason_code' => 'RUN_SOURCE_TIMEOUT', 'http_status' => 504, 'throttle_delay_ms' => 120, 'backoff_delay_ms' => 250, 'will_retry' => true],
                ['attempt_number' => 2, 'reason_code' => null, 'http_status' => 200, 'throttle_delay_ms' => 120, 'backoff_delay_ms' => 0, 'will_retry' => false],
            ],
        ]);

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs, $evidence);
        $summary = $service->execute('2026-03-20', '2026-03-20', 'manual_file', $outputDir, false);

        $this->assertTrue($summary['all_passed']);
        $this->assertSame('single_day', $summary['request_mode']);
        $this->assertSame('API_FREE', $summary['cases'][0]['source_name']);
        $this->assertSame('STAGE_COMPLETED', $summary['cases'][0]['source_attempt_event_type']);
        $this->assertSame(2, $summary['cases'][0]['source_attempt_count']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $summary['cases'][0]['source_summary']);

        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);
        $this->assertSame('STAGE_COMPLETED', $summaryFile['cases'][0]['source_attempt_event_type']);
        $this->assertSame(2, $summaryFile['cases'][0]['source_attempt_count']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $summaryFile['cases'][0]['source_summary']);
    }

    public function test_execute_normalizes_manual_source_input_file_in_summary_artifact(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-24', '2026-03-24')->andReturn([
            '2026-03-24',
        ]);

        $pipeline->shouldReceive('runSingleDay')->once()->with('2026-03-24', 'manual_file', null)->andReturn((object) [
            'run_id' => 1001,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => '2026-03-24',
            'notes' => 'source_name=LOCAL_FILE; source_input_file=C:\\ops\\manual-2026-03-24.csv',
        ]);

        $runs->shouldNotReceive('findLatestForRequestedDate');

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs);
        $summary = $service->execute('2026-03-24', '2026-03-24', 'manual_file', $outputDir, false);

        $this->assertSame('C:/ops/manual-2026-03-24.csv', $summary['cases'][0]['source_input_file']);

        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);
        $this->assertSame('C:/ops/manual-2026-03-24.csv', $summaryFile['cases'][0]['source_input_file']);
    }



    public function test_execute_writes_source_attempt_telemetry_artifact_for_failed_backfill_case_when_persisted_attempts_exist(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $evidence = m::mock(EodEvidenceRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-20', '2026-03-20')->andReturn([
            '2026-03-20',
        ]);

        $pipeline->shouldReceive('runSingleDay')->once()->with('2026-03-20', 'manual_file', null)->andThrow(new RuntimeException('boom'));
        $runs->shouldReceive('findLatestForRequestedDate')->once()->with('2026-03-20', 'manual_file')->andReturn((object) [
            'run_id' => 1001,
            'terminal_status' => 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => null,
            'notes' => 'source_name=API_FREE',
        ]);
        $evidence->shouldReceive('exportRunSourceAttemptTelemetry')->once()->with(1001)->andReturn([
            'event_id' => 9001,
            'event_time' => '2026-03-20 16:00:00',
            'event_type' => 'STAGE_FAILED',
            'provider' => 'yahoo_finance',
            'source_name' => 'API_FREE',
            'timeout_seconds' => 20,
            'retry_max' => 5,
            'attempt_count' => 6,
            'final_reason_code' => 'RUN_SOURCE_RATE_LIMIT',
            'attempts' => [
                ['attempt_number' => 1, 'reason_code' => 'RUN_SOURCE_RATE_LIMIT', 'http_status' => 429, 'backoff_delay_ms' => 250, 'will_retry' => true],
                ['attempt_number' => 6, 'reason_code' => 'RUN_SOURCE_RATE_LIMIT', 'http_status' => 429, 'backoff_delay_ms' => 0, 'will_retry' => false],
            ],
        ]);

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs, $evidence);
        $summary = $service->execute('2026-03-20', '2026-03-20', 'manual_file', $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertSame(str_replace('\\', '/', $outputDir).'/source_attempt_telemetry.json', str_replace('\\', '/', $summary['source_attempt_telemetry_artifact']));
        $this->assertFileExists($outputDir.'/source_attempt_telemetry.json');

        $artifact = json_decode(file_get_contents($outputDir.'/source_attempt_telemetry.json'), true);
        $this->assertSame('2026-03-20', $artifact['range']['start_date']);
        $this->assertSame('2026-03-20', $artifact['cases'][0]['requested_date']);
        $this->assertSame(1001, $artifact['cases'][0]['run_id']);
        $this->assertSame('STAGE_FAILED', $artifact['cases'][0]['telemetry']['event_type']);
        $this->assertCount(2, $artifact['cases'][0]['telemetry']['attempts']);
        $this->assertSame(429, $artifact['cases'][0]['telemetry']['attempts'][0]['http_status']);
    }

    public function test_execute_marks_fail_when_pipeline_returns_non_readable_terminal_state()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-20', '2026-03-21')->andReturn([
            '2026-03-20',
            '2026-03-21',
        ]);

        $pipeline->shouldReceive('runDaily')->once()->with('2026-03-20', 'manual_file', null)->andReturn((object) [
            'run_id' => 1001,
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => '2026-03-19',
            'notes' => 'source_name=LOCAL_FILE; source_input_file=manual-2026-03-20.csv',
        ]);
        $pipeline->shouldNotReceive('runSingleDay');
        $pipeline->shouldNotReceive('runDaily')->with('2026-03-21', 'manual_file', null);

        $runs->shouldNotReceive('findLatestForRequestedDate');

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs);
        $summary = $service->execute('2026-03-20', '2026-03-21', 'manual_file', $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertCount(1, $summary['cases']);
        $this->assertSame('FAIL', $summary['cases'][0]['status']);
        $this->assertSame('HELD', $summary['cases'][0]['terminal_status']);
        $this->assertSame('NOT_READABLE', $summary['cases'][0]['publishability_state']);
        $this->assertSame('LOCAL_FILE', $summary['cases'][0]['source_name']);
        $this->assertSame('manual-2026-03-20.csv', $summary['cases'][0]['source_input_file']);
        $this->assertFileExists($outputDir.'/market_data_backfill_summary.json');

        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);
        $this->assertSame('manual-2026-03-20.csv', $summaryFile['cases'][0]['source_input_file']);
    }


    public function test_execute_records_held_no_baseline_source_failure_as_fail_not_error(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-02', '2026-03-02')->andReturn([
            '2026-03-02',
        ]);

        $pipeline->shouldReceive('runSingleDay')->once()->with('2026-03-02', 'api', null)->andReturn((object) [
            'run_id' => 1070,
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => null,
            'notes' => 'source_name=API_FREE; source_provider=yahoo_finance; source_timeout_seconds=20; source_retry_max=3; source_attempt_count=4; source_final_reason_code=RUN_SOURCE_RATE_LIMIT; final_outcome_note=SOURCE_UNAVAILABLE_NO_BASELINE',
        ]);

        $runs->shouldNotReceive('findLatestForRequestedDate');

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs);
        $summary = $service->execute('2026-03-02', '2026-03-02', 'api', $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertSame('FAIL', $summary['cases'][0]['status']);
        $this->assertSame('HELD', $summary['cases'][0]['terminal_status']);
        $this->assertSame('SOURCE_UNAVAILABLE_NO_BASELINE', $summary['cases'][0]['final_outcome_note']);
        $this->assertArrayNotHasKey('error_message', $summary['cases'][0]);

        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);
        $this->assertSame('FAIL', $summaryFile['cases'][0]['status']);
        $this->assertSame('HELD', $summaryFile['cases'][0]['terminal_status']);
        $this->assertSame('SOURCE_UNAVAILABLE_NO_BASELINE', $summaryFile['cases'][0]['final_outcome_note']);
        $this->assertArrayNotHasKey('error_message', $summaryFile['cases'][0]);
    }

    public function test_execute_marks_failure_and_stops_when_continue_on_error_is_false()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-20', '2026-03-21']);
        $pipeline->shouldReceive('runDaily')->once()->with('2026-03-20', 'manual_file', null)->andThrow(new RuntimeException('boom'));
        $pipeline->shouldNotReceive('runSingleDay');
        $pipeline->shouldNotReceive('runDaily')->with('2026-03-21', 'manual_file', null);
        $runs->shouldReceive('findLatestForRequestedDate')->once()->with('2026-03-20', 'manual_file')->andReturn((object) [
            'run_id' => 1001,
            'terminal_status' => 'FAILED',
            'publishability_state' => 'NOT_READABLE',
            'trade_date_effective' => null,
            'notes' => 'source_name=API_FREE; source_provider=generic; source_timeout_seconds=15; source_retry_max=3; source_attempt_count=3; source_final_reason_code=RUN_SOURCE_TIMEOUT',
        ]);

        $service = new MarketDataBackfillService($calendar, $pipeline, $runs);
        $summary = $service->execute('2026-03-20', '2026-03-21', 'manual_file', $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertCount(1, $summary['cases']);
        $this->assertSame('FAIL', $summary['cases'][0]['status']);
        $this->assertArrayNotHasKey('error_message', $summary['cases'][0]);
        $this->assertSame(1001, $summary['cases'][0]['run_id']);
        $this->assertSame('API_FREE', $summary['cases'][0]['source_name']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $summary['cases'][0]['source_summary']);
    }
}
