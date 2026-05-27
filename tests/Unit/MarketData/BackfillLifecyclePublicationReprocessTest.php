<?php

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\ReplayVerificationService;
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

    public function test_lifecycle_publication_reprocess_blocks_readable_affected_dates(): void
    {
        [$orchestrator, $runs, $pipeline, $evidence, $replay] = $this->makeOrchestrator();

        $readableRun = (object) [
            'run_id' => 301,
            'trade_date_requested' => '2026-05-08',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'sealed_at' => '2026-05-27 10:00:00',
        ];

        $runs->shouldReceive('findLatestForRequestedDate')
            ->once()
            ->with('2026-05-08', 'api')
            ->andReturn($readableRun);
        $pipeline->shouldNotReceive('promoteDaily');
        $evidence->shouldNotReceive('exportRunEvidence');
        $replay->shouldNotReceive('generateFixtureFromRun');
        $replay->shouldNotReceive('verifyRunAgainstFixture');

        $result = $this->invokePublicationReprocess($orchestrator, [
            'requested_date' => '2026-05-01',
            'publication_reprocess_state' => 'PENDING_PROMOTE',
            'publication_reprocess_summary' => [
                'execution_state' => 'PENDING_PROMOTE',
                'candidate_trade_dates' => ['2026-05-08'],
            ],
        ], true, true, true);

        $this->assertSame('BLOCKED_REQUIRES_CORRECTION', $result['publication_reprocess_state']);
        $this->assertSame('AFFECTED_PUBLICATION_REQUIRES_CORRECTION', $result['publication_reprocess_blocked_reason_code']);
        $this->assertSame(['2026-05-08'], $result['publication_reprocess_blocked_trade_dates']);
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

    private function makeOrchestrator(): array
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $tickers = m::mock(TickerMasterRepository::class);
        $acquisition = m::mock(ApiBackfillRangeAcquisitionService::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $replay = m::mock(ReplayVerificationService::class);
        $runs = m::mock(EodRunRepository::class);

        return [
            new BackfillLifecycleOrchestrator($calendar, $tickers, $acquisition, $pipeline, $evidence, $replay, $runs),
            $runs,
            $pipeline,
            $evidence,
            $replay,
        ];
    }

    private function invokePublicationReprocess(BackfillLifecycleOrchestrator $orchestrator, array $case, bool $skipRequestedDate, bool $withEvidence, bool $withReplay): array
    {
        $method = new ReflectionMethod($orchestrator, 'executePublicationReprocessForCase');
        $method->setAccessible(true);

        return $method->invoke($orchestrator, $case, 'api', $withEvidence, $withReplay, __DIR__.DIRECTORY_SEPARATOR.'tmp', $skipRequestedDate);
    }
}
