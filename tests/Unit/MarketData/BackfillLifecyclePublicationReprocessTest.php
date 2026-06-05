<?php

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Application\MarketData\Services\EodIndicatorsComputeService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\MarketDataImpactReprocessExecutor;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BackfillLifecyclePublicationReprocessTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function test_lifecycle_publication_reprocess_promotes_non_readable_affected_dates(): void
    {
        [$orchestrator, $runs, $pipeline, $evidence, $replay] = $this->makeOrchestrator();

        $seedRun = (object) [
            'run_id' => 201,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => null,
            'publishability_state' => 'NOT_READABLE',
            'coverage_gate_state' => null,
            'sealed_at' => null,
        ];
        $promotedRun = (object) [
            'run_id' => 202,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => '1.0000',
            'publication_id' => 3002,
            'publication_version' => 1,
            'sealed_at' => '2026-05-27 10:00:00',
        ];

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($seedRun);
        $pipeline->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-05-08', 'api', 201, null, 'full_publish')
            ->andReturn($promotedRun);
        $evidence->shouldReceive('exportRunEvidence')
            ->once()
            ->with(202, m::on(function ($path) {
                return strpos((string) $path, 'publication_reprocess') !== false
                    && strpos((string) $path, '2026-05-08') !== false;
            }));
        $replay->shouldReceive('generateFixtureFromRun')
            ->once()
            ->with(202, m::type('string'), 'valid_case', null)
            ->andReturn(['fixture_path' => 'fixture.json']);
        $replay->shouldReceive('verifyRunAgainstFixture')
            ->once()
            ->with(202, 'fixture.json')
            ->andReturn(['replay_status' => 'PASS']);

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => 'PENDING_PROMOTE',
            'publication_reprocess_summary' => [
                'execution_state' => 'PENDING_PROMOTE',
                'candidate_trade_dates' => ['2026-05-01', '2026-05-08'],
            ],
        ], true, true, true);

        $this->assertSame('REPUBLISHED', $result['publication_reprocess_state']);
        $this->assertSame(1, $result['publication_reprocess_republished_trade_date_count']);
        $this->assertSame(['2026-05-08'], $result['publication_reprocess_republished_trade_dates']);
        $this->assertSame(1, $result['publication_reprocess_evidence_exported_count']);
        $this->assertSame(1, $result['publication_reprocess_replay_verified_count']);
        $this->assertSame(202, $result['publication_reprocess_runs'][0]['run_id']);
    }

    public function test_lifecycle_publication_reprocess_auto_corrects_readable_affected_dates(): void
    {
        [$orchestrator, $runs, $pipeline, $evidence, $replay, $corrections, $publications] = $this->makeOrchestrator();

        $readableRun = (object) [
            'run_id' => 301,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'sealed_at' => '2026-05-27 10:00:00',
        ];
        $baseline = (object) [
            'publication_id' => 4001,
            'run_id' => 301,
        ];
        $requestedCorrection = (object) [
            'correction_id' => 51,
        ];
        $approvedCorrection = (object) [
            'correction_id' => 51,
            'status' => 'APPROVED',
        ];
        $correctedRun = (object) [
            'run_id' => 302,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => '1.0000',
            'publication_id' => 4002,
            'publication_version' => 2,
            'sealed_at' => '2026-05-27 10:00:00',
        ];

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($readableRun);
        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-05-08')
            ->andReturn($baseline);
        $corrections->shouldReceive('createRequest')
            ->once()
            ->with('2026-05-08', 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION', m::type('string'), 'system', 4001, 301)
            ->andReturn($requestedCorrection);
        $corrections->shouldReceive('approve')
            ->once()
            ->with(51, 'system')
            ->andReturn($approvedCorrection);
        $pipeline->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-05-08', 'api', 301, 51, 'correction_current')
            ->andReturn($correctedRun);
        $evidence->shouldReceive('exportRunEvidence')
            ->once()
            ->with(302, m::type('string'));
        $replay->shouldReceive('generateFixtureFromRun')
            ->once()
            ->with(302, m::type('string'), 'valid_case', null)
            ->andReturn(['fixture_path' => 'fixture.json']);
        $replay->shouldReceive('verifyRunAgainstFixture')
            ->once()
            ->with(302, 'fixture.json')
            ->andReturn(['replay_status' => 'PASS']);

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => 'PENDING_PROMOTE',
            'publication_reprocess_summary' => [
                'execution_state' => 'PENDING_PROMOTE',
                'candidate_trade_dates' => ['2026-05-08'],
            ],
        ], true, true, true);

        $this->assertSame('REPUBLISHED', $result['publication_reprocess_state']);
        $this->assertSame(['2026-05-08'], $result['publication_reprocess_republished_trade_dates']);
        $this->assertSame(51, $result['publication_reprocess_runs'][0]['correction_id']);
        $this->assertSame('AUTOMATED_READABLE_CORRECTION', $result['publication_reprocess_runs'][0]['republication_mode']);
    }

    public function test_primary_requested_date_candidate_is_not_left_pending_after_primary_promote(): void
    {
        [$orchestrator, $runs, $pipeline] = $this->makeOrchestrator();

        $runs->shouldNotReceive('findLatestForRequestedDate');
        $pipeline->shouldNotReceive('promoteDaily');

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => 'PENDING_PROMOTE',
            'publication_reprocess_summary' => [
                'execution_state' => 'PENDING_PROMOTE',
                'candidate_trade_dates' => ['2026-05-01'],
            ],
        ], true, false, false);

        $this->assertSame('NOOP', $result['publication_reprocess_state']);
        $this->assertSame('REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE', $result['publication_reprocess_summary']['blocked_reason_code']);
    }

    public function test_requested_date_readable_correction_candidate_uses_correction_current_even_when_seed_run_is_not_readable(): void
    {
        [$orchestrator, $runs, $pipeline, $evidence, $replay, $corrections, $publications] = $this->makeOrchestrator();

        $seedRun = (object) [
            'run_id' => 401,
            'trade_date_requested' => '2026-05-01',
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'coverage_gate_state' => 'PASS',
            'sealed_at' => null,
        ];
        $baseline = (object) [
            'publication_id' => 5001,
            'run_id' => 301,
        ];
        $requestedCorrection = (object) ['correction_id' => 61];
        $approvedCorrection = (object) ['correction_id' => 61, 'status' => 'APPROVED'];
        $correctedRun = (object) [
            'run_id' => 402,
            'trade_date_requested' => '2026-05-01',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => '1.0000',
            'publication_id' => 5002,
            'publication_version' => 2,
            'sealed_at' => '2026-05-27 10:00:00',
        ];

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-01', 'api')
            ->andReturn($seedRun);
        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-05-01')
            ->andReturn($baseline);
        $corrections->shouldReceive('createRequest')
            ->once()
            ->with('2026-05-01', 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION', m::type('string'), 'system', 5001, 301)
            ->andReturn($requestedCorrection);
        $corrections->shouldReceive('approve')
            ->once()
            ->with(61, 'system')
            ->andReturn($approvedCorrection);
        $pipeline->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-05-01', 'api', 401, 61, 'correction_current')
            ->andReturn($correctedRun);
        $evidence->shouldNotReceive('exportRunEvidence');
        $replay->shouldNotReceive('generateFixtureFromRun');
        $replay->shouldNotReceive('verifyRunAgainstFixture');

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => 'PENDING_PROMOTE',
            'publication_reprocess_summary' => [
                'execution_state' => 'PENDING_PROMOTE',
                'candidate_trade_dates' => ['2026-05-01'],
                'readable_correction_candidate_trade_dates' => ['2026-05-01'],
            ],
        ], false, false, false);

        $this->assertSame('REPUBLISHED', $result['publication_reprocess_state']);
        $this->assertSame(['2026-05-01'], $result['publication_reprocess_republished_trade_dates']);
        $this->assertSame([61], $result['publication_reprocess_correction_ids']);
        $this->assertSame('AUTOMATED_READABLE_CORRECTION', $result['publication_reprocess_republication_mode']);
    }

    public function test_readable_correction_candidate_clears_stale_blocked_date_after_successful_republication(): void
    {
        [$orchestrator, $runs, $pipeline, $evidence, $replay, $corrections, $publications] = $this->makeOrchestrator();

        $seedRun = (object) [
            'run_id' => 401,
            'trade_date_requested' => '2026-05-01',
            'terminal_status' => 'HELD',
            'publishability_state' => 'NOT_READABLE',
            'coverage_gate_state' => 'PASS',
            'sealed_at' => null,
        ];
        $baseline = (object) [
            'publication_id' => 5001,
            'run_id' => 301,
        ];
        $correctedRun = (object) [
            'run_id' => 402,
            'trade_date_requested' => '2026-05-01',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => '1.0000',
            'publication_id' => 5002,
            'publication_version' => 2,
            'sealed_at' => '2026-05-27 10:00:00',
        ];

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-01', 'api')
            ->andReturn($seedRun);
        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-05-01')
            ->andReturn($baseline);
        $corrections->shouldReceive('createRequest')
            ->once()
            ->with('2026-05-01', 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION', m::type('string'), 'system', 5001, 301)
            ->andReturn((object) ['correction_id' => 62]);
        $corrections->shouldReceive('approve')
            ->once()
            ->with(62, 'system')
            ->andReturn((object) ['correction_id' => 62, 'status' => 'APPROVED']);
        $pipeline->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-05-01', 'api', 401, 62, 'correction_current')
            ->andReturn($correctedRun);
        $evidence->shouldNotReceive('exportRunEvidence');
        $replay->shouldNotReceive('generateFixtureFromRun');
        $replay->shouldNotReceive('verifyRunAgainstFixture');

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => 'PENDING_PROMOTE',
            'publication_reprocess_blocked_trade_dates' => '2026-05-01',
            'publication_reprocess_blocked_reason_code' => 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION',
            'publication_reprocess_summary' => [
                'execution_state' => 'PENDING_PROMOTE',
                'candidate_trade_dates' => ['2026-05-01'],
                'readable_correction_candidate_trade_dates' => ['2026-05-01'],
                'blocked_trade_dates' => ['2026-05-01'],
                'blocked_reason_code' => 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION',
            ],
        ], false, false, false);

        $this->assertSame('REPUBLISHED', $result['publication_reprocess_state']);
        $this->assertSame(['2026-05-01'], $result['publication_reprocess_republished_trade_dates']);
        $this->assertSame([], $result['publication_reprocess_blocked_trade_dates']);
        $this->assertNull($result['publication_reprocess_blocked_reason_code']);
        $this->assertSame([62], $result['publication_reprocess_correction_ids']);
    }

    public function test_lifecycle_consumes_actual_executor_output_for_mixed_readable_and_non_readable_candidates(): void
    {
        [$orchestrator, $runs, $pipeline, $evidence, $replay, $corrections, $publications] = $this->makeOrchestrator();

        $executorRuns = m::mock(EodRunRepository::class);
        $executorPublications = m::mock(EodPublicationRepository::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);
        $originRun = (object) ['run_id' => 100, 'trade_date_requested' => '2026-05-01'];
        $nonReadableSeed = (object) [
            'run_id' => 201,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => null,
            'publishability_state' => 'NOT_READABLE',
            'coverage_gate_state' => null,
            'sealed_at' => null,
        ];
        $readableSeed = (object) [
            'run_id' => 301,
            'trade_date_requested' => '2026-05-09',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'sealed_at' => '2026-05-27 10:00:00',
        ];
        $promotedNonReadable = (object) [
            'run_id' => 202,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => '1.0000',
            'publication_id' => 3002,
            'publication_version' => 1,
            'sealed_at' => '2026-05-27 10:00:00',
        ];
        $baseline = (object) [
            'publication_id' => 4001,
            'run_id' => 301,
        ];
        $requestedCorrection = (object) ['correction_id' => 51];
        $approvedCorrection = (object) ['correction_id' => 51, 'status' => 'APPROVED'];
        $correctedReadable = (object) [
            'run_id' => 302,
            'trade_date_requested' => '2026-05-09',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => '1.0000',
            'publication_id' => 4002,
            'publication_version' => 2,
            'sealed_at' => '2026-05-27 10:00:00',
        ];

        $executorPublications->shouldReceive('findCurrentPublicationForTradeDate')
            ->once()
            ->with('2026-05-08')
            ->andReturn(null);
        $executorRuns->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($nonReadableSeed);
        $indicators->shouldReceive('compute')->once()->with($nonReadableSeed, '2026-05-08', false);
        $eligibility->shouldReceive('build')->once()->with($nonReadableSeed, '2026-05-08', false);

        $executorSummary = (new MarketDataImpactReprocessExecutor($executorRuns, $executorPublications, $indicators, $eligibility))->execute(
            $originRun,
            'api',
            ['changed_bar_count' => 1],
            ['affected_trade_dates' => ['2026-05-08', '2026-05-09']],
            [
                'publication_impact_state' => 'REQUIRES_REPUBLICATION',
                'impacted_readable_trade_dates' => ['2026-05-09'],
            ]
        );

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($nonReadableSeed);
        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-09', 'api')
            ->andReturn($readableSeed);
        $pipeline->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-05-08', 'api', 201, null, 'full_publish')
            ->andReturn($promotedNonReadable);
        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-05-09')
            ->andReturn($baseline);
        $corrections->shouldReceive('createRequest')
            ->once()
            ->with('2026-05-09', 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION', m::type('string'), 'system', 4001, 301)
            ->andReturn($requestedCorrection);
        $corrections->shouldReceive('approve')
            ->once()
            ->with(51, 'system')
            ->andReturn($approvedCorrection);
        $pipeline->shouldReceive('promoteDaily')
            ->once()
            ->with('2026-05-09', 'api', 301, 51, 'correction_current')
            ->andReturn($correctedReadable);
        $evidence->shouldNotReceive('exportRunEvidence');
        $replay->shouldNotReceive('generateFixtureFromRun');
        $replay->shouldNotReceive('verifyRunAgainstFixture');

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => $executorSummary['publication_reprocess_summary']['execution_state'],
            'publication_reprocess_summary' => $executorSummary['publication_reprocess_summary'],
            'indicator_reprocess_execution_summary' => $executorSummary['indicator_reprocess_execution_summary'],
            'eligibility_reprocess_execution_summary' => $executorSummary['eligibility_reprocess_execution_summary'],
        ], true, false, false);

        $this->assertSame('REPUBLISHED', $result['publication_reprocess_state']);
        $this->assertSame(['2026-05-08', '2026-05-09'], $result['publication_reprocess_republished_trade_dates']);
        $this->assertSame('AUTOMATED_MIXED_IMPACT_REPUBLICATION', $result['publication_reprocess_republication_mode']);
        $this->assertSame([51], $result['publication_reprocess_correction_ids']);
        $this->assertSame(51, $result['publication_reprocess_correction_id']);
        $this->assertSame('AUTOMATED_NON_READABLE_DATES', $result['publication_reprocess_runs'][0]['republication_mode']);
        $this->assertSame('AUTOMATED_READABLE_CORRECTION', $result['publication_reprocess_runs'][1]['republication_mode']);
    }

    private function makeOrchestrator(): array
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $tickers = m::mock(TickerMasterRepository::class);
        $acquisition = m::mock(ApiBackfillRangeAcquisitionService::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $replay = m::mock(ReplayVerificationService::class);
        $runs = m::mock(EodRunRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $publications = m::mock(EodPublicationRepository::class);

        return [
            new BackfillLifecycleOrchestrator($calendar, $tickers, $acquisition, $pipeline, $evidence, $replay, $runs, $corrections, $publications),
            $runs,
            $pipeline,
            $evidence,
            $replay,
            $corrections,
            $publications,
        ];
    }

    private function invokePublicationReprocess(BackfillLifecycleOrchestrator $orchestrator, array $case, bool $skipRequestedDate, bool $withEvidence, bool $withReplay): array
    {
        $method = new ReflectionMethod($orchestrator, 'executePublicationReprocessForCase');
        $method->setAccessible(true);

        return $method->invoke($orchestrator, $case, 'api', $withEvidence, $withReplay, __DIR__.DIRECTORY_SEPARATOR.'tmp', $skipRequestedDate);
    }
}
