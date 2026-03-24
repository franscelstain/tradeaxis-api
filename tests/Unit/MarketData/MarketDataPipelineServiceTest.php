<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\EodBarsIngestService;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Application\MarketData\Services\EodIndicatorsComputeService;
use App\Application\MarketData\Services\FinalizeDecisionService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\PublicationDiffService;
use App\Application\MarketData\Services\PublicationFinalizeOutcomeService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MarketDataPipelineServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $container->instance('config', new Repository([
            'market_data' => require dirname(__DIR__, 3).'/config/market_data.php',
        ]));

        $db = new class {
            public function transaction(callable $callback)
            {
                return $callback();
            }
        };

        $container->instance('db', $db);
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        m::close();

        parent::tearDown();
    }

    private function makeRun(
        int $runId = 55,
        string $coverageRatio = '1.0000',
        string $sealedAt = '2026-03-24 23:06:08'
    ): EodRun {
        $run = new EodRun();
        $run->run_id = $runId;
        $run->coverage_ratio = $coverageRatio;
        $run->sealed_at = $sealedAt;

        return $run;
    }

    public function test_complete_finalize_marks_correction_published_with_final_outcome_note_after_outcome_resolution(): void
    {
        $run = $this->makeRun(55);

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 55, 'FINALIZE', 4);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
        ];

        $correction = (object) [
            'correction_id' => 4,
            'status' => 'RESEALED',
        ];

        $this->runs->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($run);

        $this->runs->shouldReceive('appendEvent')
            ->zeroOrMoreTimes();

        $this->runs->shouldReceive('failStage')
            ->never();

        $this->publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $this->publications->shouldReceive('findCandidatePublicationByRunId')
            ->once()
            ->with(55)
            ->andReturn($candidatePublication);

        $this->corrections->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($correction);

        $this->publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(false);

        $this->artifacts->shouldReceive('promotePublicationHistoryToCurrent')
            ->once()
            ->with('2026-03-17', 32, 55);

        $this->publications->shouldReceive('promoteCandidateToCurrent')
            ->once()
            ->with($run, 31);

        $this->runs->shouldReceive('syncCurrentPublicationMirror')
            ->once()
            ->with('2026-03-17', 55);

        $this->publications->shouldReceive('findPointerResolvedPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($candidatePublication);

        $this->finalizeDecisions->shouldReceive('decide')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'reason_code' => null,
                'message' => 'Finalize succeeded.',
            ]);

        $this->corrections->shouldReceive('markPublished')
            ->once()
            ->with(
                4,
                55,
                31,
                'Historical correction published safely via new sealed current publication.'
            );

        $this->corrections->shouldReceive('markCancelled')->never();

        $service = $this->makeService();

        $result = $service->completeFinalize($input);

        $this->assertSame('SUCCESS', $result['terminal_status']);
        $this->assertSame('READABLE', $result['publishability_state']);
        $this->assertSame('PUBLISHED', $result['correction_outcome']);
        $this->assertSame(
            'Historical correction published safely via new sealed current publication.',
            $result['correction_outcome_note']
        );
    }

    public function test_complete_finalize_marks_correction_cancelled_with_final_outcome_note_when_content_is_unchanged(): void
    {
        $run = $this->makeRun(55);

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 55, 'FINALIZE', 4);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
        ];

        $correction = (object) [
            'correction_id' => 4,
            'status' => 'RESEALED',
        ];

        $this->runs->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($run);

        $this->runs->shouldReceive('appendEvent')
            ->zeroOrMoreTimes();

        $this->runs->shouldReceive('failStage')
            ->never();

        $this->publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $this->publications->shouldReceive('findCandidatePublicationByRunId')
            ->once()
            ->with(55)
            ->andReturn($candidatePublication);

        $this->corrections->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($correction);

        $this->publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(true);

        $this->publications->shouldReceive('discardCandidatePublication')
            ->once()
            ->with(32);

        $this->finalizeDecisions->shouldReceive('decide')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'reason_code' => null,
                'message' => 'Finalize succeeded.',
            ]);

        $this->corrections->shouldReceive('markCancelled')
            ->once()
            ->with(
                4,
                55,
                31,
                'Correction rerun produced unchanged content; current publication preserved without version switch.'
            );

        $this->corrections->shouldReceive('markPublished')->never();

        $service = $this->makeService();

        $result = $service->completeFinalize($input);

        $this->assertSame('SUCCESS', $result['terminal_status']);
        $this->assertSame('READABLE', $result['publishability_state']);
        $this->assertSame('CANCELLED', $result['correction_outcome']);
        $this->assertSame(
            'Correction rerun produced unchanged content; current publication preserved without version switch.',
            $result['correction_outcome_note']
        );
    }

    public function test_complete_finalize_keeps_resealed_when_finalize_outcome_is_conflict(): void
    {
        $run = $this->makeRun(55);

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 55, 'FINALIZE', 4);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
        ];

        $conflictingCurrent = (object) [
            'publication_id' => 99,
            'run_id' => 99,
            'publication_version' => 9,
        ];

        $correction = (object) [
            'correction_id' => 4,
            'status' => 'RESEALED',
        ];

        $this->runs->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($run);

        $this->runs->shouldReceive('appendEvent')
            ->zeroOrMoreTimes();

        $this->runs->shouldReceive('failStage')
            ->never();

        $this->publications->shouldReceive('findCurrentPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $this->publications->shouldReceive('findCandidatePublicationByRunId')
            ->once()
            ->with(55)
            ->andReturn($candidatePublication);

        $this->corrections->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($correction);

        $this->publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(false);

        $this->artifacts->shouldReceive('promotePublicationHistoryToCurrent')
            ->once()
            ->with('2026-03-17', 32, 55);

        $this->publications->shouldReceive('promoteCandidateToCurrent')
            ->once()
            ->with($run, 31);

        $this->runs->shouldReceive('syncCurrentPublicationMirror')
            ->once()
            ->with('2026-03-17', 55);

        $this->publications->shouldReceive('findPointerResolvedPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($conflictingCurrent);

        $this->finalizeDecisions->shouldReceive('decide')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'HELD',
                'publishability_state' => 'NOT_READABLE',
                'reason_code' => 'RUN_LOCK_CONFLICT',
                'message' => 'Finalize held due to conflicting current publication state.',
            ]);

        $this->corrections->shouldReceive('markPublished')->never();
        $this->corrections->shouldReceive('markCancelled')->never();

        $service = $this->makeService();

        $result = $service->completeFinalize($input);

        $this->assertSame('HELD', $result['terminal_status']);
        $this->assertSame('NOT_READABLE', $result['publishability_state']);
        $this->assertSame('RUN_LOCK_CONFLICT', $result['reason_code']);
        $this->assertNull($result['correction_outcome']);
        $this->assertNull($result['correction_outcome_note']);
    }

    private function makeService(): array
    {
        $runs = m::mock(EodRunRepository::class);
        $barsIngest = m::mock(EodBarsIngestService::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);
        $hashes = m::mock(DeterministicHashService::class);
        $finalizeDecisions = m::mock(FinalizeDecisionService::class);
        $publicationDiffs = m::mock(PublicationDiffService::class);
        $publicationFinalizeOutcomes = new PublicationFinalizeOutcomeService();

        $service = m::mock(MarketDataPipelineService::class, [
            $runs,
            $barsIngest,
            $indicators,
            $eligibility,
            $publications,
            $corrections,
            $artifacts,
            $hashes,
            $finalizeDecisions,
            $publicationDiffs,
            $publicationFinalizeOutcomes,
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        return [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs];
    }
}
