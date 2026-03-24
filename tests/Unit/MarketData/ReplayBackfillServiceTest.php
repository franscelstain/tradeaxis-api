<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayBackfillService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ReplayBackfillServiceTest extends TestCase
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

    public function test_execute_runs_verification_for_each_trading_date_and_writes_summary()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot.'/valid_case', 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-18', '2026-03-20')->andReturn([
            '2026-03-18',
            '2026-03-19',
            '2026-03-20',
        ]);

        foreach ([
            '2026-03-18' => (object) ['publication_id' => 23, 'run_id' => 23],
            '2026-03-19' => (object) ['publication_id' => 24, 'run_id' => 24],
            '2026-03-20' => (object) ['publication_id' => 28, 'run_id' => 28],
        ] as $date => $publication) {
            $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with($date)->andReturn($publication);
            $replays->shouldReceive('verifyRunAgainstFixture')->once()->with($publication->run_id, $fixtureRoot.'/valid_case')->andReturn([
                'replay_id' => $publication->run_id + 100,
                'trade_date' => $date,
                'comparison_result' => 'MATCH',
                'comparison_note' => 'matched',
            ]);
            $evidence->shouldReceive('exportReplayEvidence')->once()->with($publication->run_id + 100, $date, $outputDir.'/'.$date)->andReturn([
                'output_dir' => $outputDir.'/'.$date,
                'files' => ['replay_result.json'],
            ]);
        }

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-20', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertTrue($summary['all_passed']);
        $this->assertCount(3, $summary['cases']);
        $this->assertFileExists($outputDir.'/market_data_replay_backfill_summary.json');
    }

    public function test_execute_marks_error_and_stops_when_publication_is_missing_and_continue_is_false()
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot.'/valid_case', 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18', '2026-03-19']);
        $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with('2026-03-18')->andReturn(null);
        $replays->shouldNotReceive('verifyRunAgainstFixture');
        $evidence->shouldNotReceive('exportReplayEvidence');

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-19', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertCount(1, $summary['cases']);
        $this->assertSame('ERROR', $summary['cases'][0]['status']);
    }
}
