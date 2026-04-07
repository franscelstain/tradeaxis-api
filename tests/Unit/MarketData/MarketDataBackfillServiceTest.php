<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\MarketDataPipelineService;
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
        $this->assertCount(3, $summary['cases']);
        $this->assertSame('API_FREE', $summary['cases'][0]['source_name']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $summary['cases'][0]['source_summary']);

        $summaryFile = json_decode(file_get_contents($outputDir.'/market_data_backfill_summary.json'), true);
        $this->assertSame('API_FREE', $summaryFile['cases'][0]['source_name']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=2 | success_after_retry=yes | final_http_status=200 | final_reason_code=RUN_SOURCE_TIMEOUT', $summaryFile['cases'][0]['source_summary']);
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
    }

    public function test_execute_marks_failure_and_stops_when_continue_on_error_is_false()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $runs = m::mock(EodRunRepository::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-20', '2026-03-21']);
        $pipeline->shouldReceive('runDaily')->once()->with('2026-03-20', 'manual_file', null)->andThrow(new RuntimeException('boom'));
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
        $this->assertSame('ERROR', $summary['cases'][0]['status']);
        $this->assertSame(1001, $summary['cases'][0]['run_id']);
        $this->assertSame('API_FREE', $summary['cases'][0]['source_name']);
        $this->assertSame('provider=generic | timeout_seconds=15 | retry_max=3 | attempt_count=3 | final_reason_code=RUN_SOURCE_TIMEOUT', $summary['cases'][0]['source_summary']);
    }
}
