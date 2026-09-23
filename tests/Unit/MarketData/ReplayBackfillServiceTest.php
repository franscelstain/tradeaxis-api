<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayBackfillService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * `F-MD-B18-A002-016`, `MD-S050-R0027`/`MD-S003-R0002` (D-MD-B18-A002-005 Q3): "historical/backfill
 * verification uses explicit publication/fixture identity ... No latest/current substitution."
 *
 * `execute()` previously chose each date's publication with
 * `EodPublicationRepository::findCurrentPublicationForTradeDate($tradeDate)` and then passed that
 * pointer-derived id to `ReplayVerificationService::verifyRunAgainstFixture()` as though it had been
 * declared -- laundering a "whatever is current right now" lookup into an apparently explicit
 * identity. After a correction moved the pointer, the same backfill command would silently verify a
 * different publication than the one its fixture was created against.
 *
 * The fixture directory itself is now the declared manifest: `{fixtureRoot}/{tradeDate}/publication_{N}`
 * names the immutable publication this date's replay targets, resolved via
 * `EodPublicationRepository::buildManifestByPublicationId()` -- a pure identity-keyed lookup with no
 * pointer/current concept at all. `findCurrentPublicationForTradeDate` must never be called from this
 * class again; every test below proves that with an explicit `shouldNotReceive`, not merely by leaving
 * the method unstubbed.
 */
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

    /** @return array<string,mixed> the shape EodPublicationRepository::buildManifestByPublicationId() returns */
    private function manifestFor($publicationId, $runId, $tradeDate): array
    {
        return ['publication_id' => $publicationId, 'run_id' => $runId, 'trade_date' => $tradeDate];
    }

    /**
     * The positive control: three dates, each with exactly one declared `publication_<id>` fixture
     * directory, each resolved purely by identity. `findCurrentPublicationForTradeDate` is proven
     * never called -- the remediation is not "reject everything", it is "stop consulting the pointer".
     */
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
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');

        foreach ([
            '2026-03-18' => 23,
            '2026-03-19' => 24,
            '2026-03-20' => 28,
        ] as $date => $publicationId) {
            $fixturePath = $fixtureRoot.'/'.$date.'/publication_'.$publicationId;
            mkdir($fixturePath, 0777, true);
            $publications->shouldReceive('buildManifestByPublicationId')->once()->with($publicationId)
                ->andReturn($this->manifestFor($publicationId, $publicationId, $date));
            $replays->shouldReceive('verifyRunAgainstFixture')->once()->with($publicationId, $fixturePath, null, $publicationId)->andReturn([
                'replay_id' => $publicationId + 100,
                'trade_date' => $date,
                'comparison_result' => 'MATCH',
                'comparison_note' => 'matched',
            ]);
            $evidence->shouldReceive('exportReplayEvidence')->once()->with($publicationId + 100, $date, $outputDir.'/'.$date)->andReturn([
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
        $this->assertSame(23, $summary['cases'][0]['publication_id']);
        $this->assertFileExists($outputDir.'/market_data_replay_backfill_summary.json');
    }

    /**
     * The defect this predicate names directly: a correction moves the current pointer to a
     * different publication (P62) after the fixture was created and declared against P61. The
     * backfill must still target P61 -- proven by never calling `findCurrentPublicationForTradeDate`
     * at all and by asserting the resolved/verified publication is exactly the one the fixture
     * directory names, not whatever the (unconsulted) current pointer would now resolve to.
     */
    public function test_pointer_moving_after_fixture_creation_does_not_retarget_the_replay(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot, 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18']);
        // The fixture was created declaring publication 61. Whatever the pointer is "now" (62, in the
        // scenario this predicate describes) is irrelevant and is never looked up.
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        mkdir($fixtureRoot.'/2026-03-18/publication_61', 0777, true);
        $publications->shouldReceive('buildManifestByPublicationId')->once()->with(61)
            ->andReturn($this->manifestFor(61, 161, '2026-03-18'));
        $replays->shouldReceive('verifyRunAgainstFixture')->once()
            ->with(161, $fixtureRoot.'/2026-03-18/publication_61', null, 61)
            ->andReturn(['replay_id' => 9001, 'trade_date' => '2026-03-18', 'comparison_result' => 'MATCH', 'comparison_note' => 'matched']);
        $evidence->shouldReceive('exportReplayEvidence')->once()->andReturn(['output_dir' => $outputDir.'/2026-03-18', 'files' => []]);

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-18', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertTrue($summary['all_passed']);
        $this->assertSame(61, $summary['cases'][0]['publication_id'],
            'the replay must target the publication the fixture declared, never a pointer-resolved substitute');
        $this->assertSame(161, $summary['cases'][0]['run_id']);
    }

    /**
     * No `publication_<id>` directory declared under the date at all. Rejected before any replay
     * work, on the same "reject outright, never guess" boundary this class already enforces for an
     * unknown fixture case -- proven by `shouldNotReceive` on every downstream collaborator.
     */
    public function test_execute_rejects_a_date_with_no_declared_publication_before_any_work(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot.'/2026-03-18', 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18', '2026-03-19']);
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $publications->shouldNotReceive('buildManifestByPublicationId');
        $replays->shouldNotReceive('verifyRunAgainstFixture');
        $evidence->shouldNotReceive('exportReplayEvidence');

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-19', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertFalse($summary['all_passed']);
        $this->assertCount(1, $summary['cases']);
        $this->assertSame('ERROR', $summary['cases'][0]['status']);
        $this->assertSame('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_UNDECLARED', $summary['cases'][0]['reason_code']);
    }

    /**
     * Two `publication_<id>` directories declared under the same date. The contract requires exactly
     * one explicit identity per date; an ambiguous declaration must fail closed rather than silently
     * choosing either one.
     */
    public function test_execute_rejects_a_date_with_more_than_one_declared_publication(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot.'/2026-03-18/publication_61', 0777, true);
        mkdir($fixtureRoot.'/2026-03-18/publication_62', 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18']);
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $publications->shouldNotReceive('buildManifestByPublicationId');
        $replays->shouldNotReceive('verifyRunAgainstFixture');

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-18', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertSame('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_AMBIGUOUS', $summary['cases'][0]['reason_code']);
    }

    /**
     * The declared identity does not correspond to any real publication row. Fails closed rather than
     * treating a non-existent explicit id as an implicit "look up whatever is current instead".
     */
    public function test_execute_rejects_a_declared_publication_id_that_does_not_exist(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot.'/2026-03-18/publication_999', 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18']);
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $publications->shouldReceive('buildManifestByPublicationId')->once()->with(999)->andReturn(null);
        $replays->shouldNotReceive('verifyRunAgainstFixture');

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-18', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertSame('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_NOT_FOUND', $summary['cases'][0]['reason_code']);
    }

    /**
     * The declared identity resolves to a real publication, but that publication belongs to a
     * different trade date than the directory declaring it -- an internally inconsistent fixture.
     * Fails closed rather than silently accepting either date as authoritative.
     */
    public function test_execute_rejects_a_publication_whose_actual_trade_date_disagrees_with_the_fixture_directory(): void
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $replays = m::mock(ReplayVerificationService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $fixtureRoot = sys_get_temp_dir().'/replay_backfill_root_'.uniqid();
        mkdir($fixtureRoot.'/2026-03-18/publication_61', 0777, true);
        $outputDir = sys_get_temp_dir().'/replay_backfill_output_'.uniqid();

        $calendar->shouldReceive('tradingDatesBetween')->once()->andReturn(['2026-03-18']);
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $publications->shouldReceive('buildManifestByPublicationId')->once()->with(61)
            ->andReturn($this->manifestFor(61, 161, '2026-03-19'));
        $replays->shouldNotReceive('verifyRunAgainstFixture');

        $service = new ReplayBackfillService($calendar, $publications, $replays, $evidence);
        $summary = $service->execute('2026-03-18', '2026-03-18', 'valid_case', $fixtureRoot, $outputDir, false);

        $this->assertSame('REPLAY_BACKFILL_EXPLICIT_PUBLICATION_TRADE_DATE_MISMATCH', $summary['cases'][0]['reason_code']);
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
        $publications->shouldNotReceive('buildManifestByPublicationId');
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
        $publications->shouldNotReceive('findCurrentPublicationForTradeDate');
        $fixturePath = $fixtureRoot.'/2026-03-18/publication_61';
        mkdir($fixturePath, 0777, true);
        $publications->shouldReceive('buildManifestByPublicationId')->once()->with(61)
            ->andReturn($this->manifestFor(61, 61, '2026-03-18'));
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
