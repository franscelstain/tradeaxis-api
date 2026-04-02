<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\MarketDataBackfillService;
use App\Application\MarketData\Services\MarketDataPipelineService;
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
            ];
            $pipeline->shouldReceive('runDaily')->once()->with($date, 'manual_file', null)->andReturn($run);
        }

        $service = new MarketDataBackfillService($calendar, $pipeline);
        $summary = $service->execute('2026-03-20', '2026-03-24', 'manual_file', $outputDir, false);

        $this->assertTrue($summary['all_passed']);
        $this->assertCount(3, $summary['cases']);
        $this->assertFileExists($outputDir.'/market_data_backfill_summary.json');
    }



    public function test_execute_marks_fail_when_pipeline_returns_non_readable_terminal_state()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
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
        ]);
        $pipeline->shouldNotReceive('runDaily')->with('2026-03-21', 'manual_file', null);

        $service = new MarketDataBackfillService($calendar, $pipeline);
        $summary = $service->execute('2026-03-20', '2026-03-21', 'manual_file', $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertCount(1, $summary['cases']);
        $this->assertSame('FAIL', $summary['cases'][0]['status']);
        $this->assertSame('HELD', $summary['cases'][0]['terminal_status']);
        $this->assertSame('NOT_READABLE', $summary['cases'][0]['publishability_state']);
        $this->assertFileExists($outputDir.'/market_data_backfill_summary.json');
    }

    public function test_execute_marks_failure_and_stops_when_continue_on_error_is_false()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $outputDir = sys_get_temp_dir().'/market_data_backfill_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-20', '2026-03-21']);
        $pipeline->shouldReceive('runDaily')->once()->with('2026-03-20', 'manual_file', null)->andThrow(new RuntimeException('boom'));
        $pipeline->shouldNotReceive('runDaily')->with('2026-03-21', 'manual_file', null);

        $service = new MarketDataBackfillService($calendar, $pipeline);
        $summary = $service->execute('2026-03-20', '2026-03-21', 'manual_file', $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertCount(1, $summary['cases']);
        $this->assertSame('ERROR', $summary['cases'][0]['status']);
    }
}
