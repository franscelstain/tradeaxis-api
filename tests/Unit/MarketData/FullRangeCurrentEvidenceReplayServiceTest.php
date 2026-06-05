<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\FullRangeCurrentEvidenceReplayService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FullRangeCurrentEvidenceReplayServiceTest extends TestCase
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

    public function test_execute_generates_current_publication_evidence_fixture_replay_and_summary(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $replays = m::mock(ReplayVerificationService::class);
        $outputDir = sys_get_temp_dir().'/full_range_current_evidence_replay_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-03-18', '2026-03-19')->andReturn([
            '2026-03-18',
            '2026-03-19',
        ]);

        foreach ([
            '2026-03-18' => (object) ['publication_id' => 23, 'publication_version' => 1, 'run_id' => 123],
            '2026-03-19' => (object) ['publication_id' => 24, 'publication_version' => 1, 'run_id' => 124],
        ] as $date => $publication) {
            $caseDir = $outputDir.'/dates/'.$date.'/run_'.$publication->run_id.'_publication_'.$publication->publication_id;
            $fixturePath = $caseDir.'/fixture';

            $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with($date)->andReturn($publication);
            $evidence->shouldReceive('exportRunEvidence')->once()->with($publication->run_id, $caseDir.'/run-evidence')->andReturn([
                'summary' => [
                    'evidence_admission_state' => 'ADMITTED_COMPLETE',
                    'evidence_completeness_state' => 'COMPLETE',
                ],
                'output_dir' => $caseDir.'/run-evidence',
                'file_count' => 10,
                'files' => ['run_summary.json'],
            ]);
            $replays->shouldReceive('generateFixtureFromRun')->once()->with($publication->run_id, $fixturePath, 'valid_case', $publication->publication_id)->andReturn([
                'fixture_path' => $fixturePath,
            ]);
            $replays->shouldReceive('verifyRunAgainstFixture')->once()->with($publication->run_id, $fixturePath)->andReturn([
                'replay_id' => $publication->run_id + 1000,
                'trade_date' => $date,
                'comparison_result' => 'MATCH',
                'replay_status' => 'PASS',
                'mismatch_count' => 0,
            ]);
            $evidence->shouldReceive('exportReplayEvidence')->once()->with($publication->run_id + 1000, $date, $caseDir.'/replay-evidence')->andReturn([
                'summary' => [
                    'evidence_admission_state' => 'ADMITTED_COMPLETE',
                ],
                'output_dir' => $caseDir.'/replay-evidence',
                'file_count' => 6,
                'files' => ['replay_result.json'],
            ]);
        }

        $service = new FullRangeCurrentEvidenceReplayService($calendar, $publications, $evidence, $replays);
        $summary = $service->execute('2026-03-18', '2026-03-19', [
            'fixture_case' => 'valid_case',
            'output_dir' => $outputDir,
        ]);

        $this->assertTrue($summary['all_passed']);
        $this->assertSame(2, $summary['success_count']);
        $this->assertSame(0, $summary['failed_count']);
        $this->assertSame(0, $summary['error_count']);
        $this->assertSame('ADMITTED_COMPLETE', $summary['cases'][0]['run_evidence_admission_state']);
        $this->assertSame('MATCH', $summary['cases'][1]['comparison_result']);
        $this->assertFileExists($outputDir.'/market_data_full_range_current_evidence_replay_summary.json');
    }

    public function test_execute_marks_missing_current_publication_as_error_and_stops(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $replays = m::mock(ReplayVerificationService::class);
        $outputDir = sys_get_temp_dir().'/full_range_current_evidence_replay_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18', '2026-03-19']);
        $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with('2026-03-18')->andReturn(null);
        $evidence->shouldNotReceive('exportRunEvidence');
        $replays->shouldNotReceive('generateFixtureFromRun');
        $replays->shouldNotReceive('verifyRunAgainstFixture');

        $service = new FullRangeCurrentEvidenceReplayService($calendar, $publications, $evidence, $replays);
        $summary = $service->execute('2026-03-18', '2026-03-19', [
            'output_dir' => $outputDir,
        ]);

        $this->assertFalse($summary['all_passed']);
        $this->assertSame(1, $summary['processed_count']);
        $this->assertSame('ERROR', $summary['cases'][0]['status']);
        $this->assertSame('NO_READABLE_PUBLICATION', $summary['cases'][0]['reason_code']);
    }
}
