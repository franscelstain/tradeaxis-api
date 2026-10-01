<?php

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\PublicationGovernanceBindingService;
use App\Application\MarketData\Services\PublicationInputBindingService;
use App\Application\MarketData\Services\SemanticNestedIdentityService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;

/**
 * Where the hash stage derives V2 nested identities (D-MD-B10-A002-003).
 *
 * The V2 nested identities read the governance state that V1 governance binding materializes and
 * are consumed by V2 artifact hashing, so a V2 run must bind them after governance and input
 * binding and before any artifact hash. A V1 run must never derive them.
 */
class SemanticNestedIdentityPipelineWiringTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_a_v2_hash_stage_binds_nested_identity_after_governance_and_before_any_artifact_hash(): void
    {
        $this->assertSame(
            ['governance', 'input', 'nested', 'artifact:bars', 'artifact:indicators', 'artifact:eligibility'],
            $this->hashStageCalls(ArtifactSemanticHashService::PROFILE_V2)
        );
    }

    public function test_a_v1_hash_stage_never_derives_nested_identity(): void
    {
        $this->assertSame(['governance', 'input'], $this->hashStageCalls(ArtifactSemanticHashService::LEGACY_PROFILE_V1));
    }

    private function hashStageCalls(string $profile): array
    {
        $calls = new ArrayObject();
        $run = (new EodRun())->forceFill(['run_id' => 31, 'artifact_hash_profile' => $profile, 'trade_date_requested' => '2026-09-29', 'notes' => null]);
        $candidate = (object) ['publication_id' => 77, 'run_id' => 31, 'publication_version' => 1,
            'supersedes_publication_id' => null, 'previous_publication_id' => null, 'replaced_publication_id' => null];

        $pipeline = Mockery::mock(MarketDataPipelineService::class)->makePartial();
        $pipeline->shouldReceive('startStage')->andReturn([$run, null, null]);

        $publications = Mockery::mock(EodPublicationRepository::class);
        $publications->shouldReceive('getOrCreateCandidatePublication')->andReturn($candidate);
        $publications->shouldReceive('findByRunId')->with(31)->andReturn($candidate);
        $publications->shouldReceive('updateCandidateHashes');

        $runs = Mockery::mock(EodRunRepository::class);
        $runs->shouldReceive('storeHashes')->andReturn($run);
        $runs->shouldReceive('appendEvent');
        $runs->shouldReceive('failStage')->andReturn($run);

        $governance = Mockery::mock(PublicationGovernanceBindingService::class);
        $governance->shouldReceive('bind')->andReturnUsing(function () use ($calls) {
            $calls[] = 'governance';
            return [];
        });
        $inputs = Mockery::mock(PublicationInputBindingService::class);
        $inputs->shouldReceive('bind')->andReturnUsing(function () use ($calls) {
            $calls[] = 'input';
            return [];
        });
        $nested = Mockery::mock(SemanticNestedIdentityService::class);
        $nested->shouldReceive('bind')
            ->with($run, $candidate, '2026-09-29', false)
            ->andReturnUsing(function () use ($calls) {
                $calls[] = 'nested';
                return [];
            });
        $artifacts = new class($calls) extends ArtifactSemanticHashService {
            private $calls;
            public function __construct($calls) { $this->calls = $calls; }
            public function hashStoredArtifact(string $artifact, string $table, string $tradeDate, $run, $publication, array $extraWhere = []): string
            {
                $this->calls[] = 'artifact:'.$artifact;
                return hash('sha256', $artifact);
            }
        };

        foreach ([
            'publications' => $publications, 'runs' => $runs, 'governanceBindings' => $governance,
            'inputBindings' => $inputs, 'semanticNestedIdentities' => $nested,
            'artifactSemanticHashes' => $artifacts, 'hashes' => new DeterministicHashService(),
        ] as $property => $value) {
            $reflection = new ReflectionProperty(MarketDataPipelineService::class, $property);
            $reflection->setAccessible(true);
            $reflection->setValue($pipeline, $value);
        }

        $pipeline->completeHash(new MarketDataStageInput('2026-09-29', 'api', 31, 'HASH'));

        return $calls->getArrayCopy();
    }
}
