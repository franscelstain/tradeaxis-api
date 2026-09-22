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
        mkdir($fixtureRoot, 0777, true);
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
            $fixturePath = $fixtureRoot.'/'.$date.'/publication_'.$publication->publication_id;
            mkdir($fixturePath, 0777, true);
            $replays->shouldReceive('verifyRunAgainstFixture')->once()->with($publication->run_id, $fixturePath, null, $publication->publication_id)->andReturn([
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
        $this->assertSame(str_replace('\\', '/', $fixtureRoot), $summary['fixture_root']);
        $this->assertNull($summary['fixture_path']);
        $this->assertSame(str_replace('\\', '/', $outputDir.'/2026-03-18'), $summary['cases'][0]['evidence_output_dir']);
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
        mkdir($fixtureRoot, 0777, true);
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
        $this->assertSame('NO_READABLE_PUBLICATION', $summary['cases'][0]['reason_code']);
        $this->assertStringContainsString('NO_READABLE_PUBLICATION:', $summary['cases'][0]['error_message']);
    }

    /**
     * `F-MD-B18-A002-015`, `MD-S050-R0053`/`MD-S002-R0009`/`MD-S002-R0010` -- C2: "case/fixture tidak
     * dikenal ... tolak sebelum bekerja; tidak memilih pointer atau menebak expected outcome."
     *
     * Before this, `expectedOutcomeForFixtureCase()` returned `null` for any unrecognised case and
     * `$passed = $expectedOutcome ? ... : true` turned that into an automatic pass for every date in
     * the range -- after doing the full replay/export work for each one. An unknown case must instead
     * be refused outright, before any calendar lookup, directory creation, or replay execution --
     * proven here by `shouldNotReceive` on every collaborator, not merely by asserting the exception.
     */
    public function test_execute_rejects_an_unknown_fixture_case_before_any_work(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $calendar->shouldNotReceive('tradingDatesBetween');
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $replays->shouldNotReceive('verifyRunAgainstFixture');
        $evidence->shouldNotReceive('exportReplayEvidence');

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('REPLAY_BACKFILL_UNKNOWN_FIXTURE_CASE: totally_unrecognised_case');

        $service->execute('2026-03-18', '2026-03-20', 'totally_unrecognised_case');
    }

    /**
     * `F-MD-B18-A002-015`, `MD-S050-R0053` -- the C2 boundary this predicate names directly: `BLOCKED`
     * is not a weaker `PASS`. A fixture case that legitimately expects `MISMATCH`
     * (`reason_code_mismatch_case`) but whose replay comes back `NOT_ADMISSIBLE`/`BLOCKED` (required
     * proof unavailable, per `E-MD-B18-A002-052`) must not be counted as a fixture pass, and the
     * actual replay status recorded must stay `BLOCKED` -- never silently promoted to match the
     * fixture's own unrelated expectation.
     */
    public function test_execute_does_not_count_a_blocked_replay_as_passed_for_a_mismatch_expecting_case(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot, 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18']);
        $publication = (object) ['publication_id' => 61, 'run_id' => 61];
        $publications->shouldReceive('findCurrentPublicationForTradeDate')->once()->with('2026-03-18')->andReturn($publication);
        $fixturePath = $fixtureRoot.'/2026-03-18/publication_61';
        mkdir($fixturePath, 0777, true);
        $replays->shouldReceive('verifyRunAgainstFixture')->once()->with(61, $fixturePath, null, 61)->andReturn([
            'replay_id' => 161,
            'trade_date' => '2026-03-18',
            // The genuine post-E052 shape: required proof was unavailable, so the replay itself
            // reports BLOCKED/NOT_ADMISSIBLE, unrelated to whatever this fixture case expected.
            'comparison_result' => 'NOT_ADMISSIBLE',
            'replay_status' => 'BLOCKED',
            'comparison_note' => 'REPLAY_EXPECTED_PROOF_INCOMPLETE: required fixture proof was unavailable: expected_coverage_context.coverage_reason_code',
        ]);
        $evidence->shouldReceive('exportReplayEvidence')->once()->with(161, '2026-03-18', $outputDir.'/2026-03-18')->andReturn([
            'output_dir' => $outputDir.'/2026-03-18',
            'files' => ['replay_result.json'],
        ]);

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-18', 'reason_code_mismatch_case', $fixtureRoot, $outputDir, false);

        $this->assertFalse($summary['all_passed'],
            'a BLOCKED replay must never be counted as a fixture pass, regardless of what the fixture case expected');
        $this->assertFalse($summary['cases'][0]['passed']);
        $this->assertSame('MISMATCH', $summary['cases'][0]['expected_outcome']);
        $this->assertSame('NOT_ADMISSIBLE', $summary['cases'][0]['observed_outcome']);
        $this->assertSame('BLOCKED', $summary['cases'][0]['replay_status'],
            'the actual replay status must be preserved as BLOCKED -- expectation-match and replay-status are not the same question');
    }
}
