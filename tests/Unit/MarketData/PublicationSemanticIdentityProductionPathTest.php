<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * V2 publication, correction and seal identity through the production repository path.
 *
 * PublicationCorrectionSealSemanticIdentityOnMariaDbTest hashes documents the test assembles
 * itself, and those documents never contain an allocated key, so their equality holds whatever the
 * repository does. An allocation can only leak through the repository's own assembly: predecessor
 * publication ids resolved to manifest hashes, the correction baseline resolved the same way, and
 * seal material read back from the persisted publication.
 *
 * This drives that assembly end to end -- candidate, V2 artifact roots, analytical product,
 * lineage binding, manifest, correction identity, seal and promotion -- in histories where every
 * local key differs and rows arrive in a different order, for both a correction republication and
 * a plain first publication, and requires identical identities. A further history changes only the
 * predecessor's manifest and must diverge, and sealed material changed out of band must stop
 * promotion.
 */
class PublicationSemanticIdentityProductionPathTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';

    private const ALLOCATION_A = [
        'baseline_run' => 25, 'candidate_run' => 27, 'baseline_publication' => 10,
        'correction' => 55, 'config_snapshot' => 1, 'factor_set' => 1, 'ticker' => 999999,
        'unrelated_rows' => 0, 'candidate_first' => false,
    ];

    // Every key differs from A, run keys are in the opposite order, unrelated rows move the
    // auto-allocated candidate publication id, and the candidate side is inserted first.
    private const ALLOCATION_B = [
        'baseline_run' => 4025, 'candidate_run' => 3027, 'baseline_publication' => 810,
        'correction' => 9055, 'config_snapshot' => 71, 'factor_set' => 13, 'ticker' => 424242,
        'unrelated_rows' => 5, 'candidate_first' => true,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_local_allocations_leave_v2_publication_correction_and_seal_identity_unchanged(): void
    {
        $a = $this->history(self::ALLOCATION_A, 'correction', 'stable-predecessor-manifest');
        $b = $this->history(self::ALLOCATION_B, 'correction', 'stable-predecessor-manifest');

        $this->assertAllocatedDifferently($a['keys'], $b['keys']);
        foreach ($a['identity'] as $name => $value) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $value, $name);
        }

        $this->assertSame($a['identity'], $b['identity']);
    }

    public function test_a_plain_first_publication_is_allocation_independent_through_seal_and_promotion(): void
    {
        $a = $this->history(self::ALLOCATION_A, 'plain', 'stable-predecessor-manifest');
        $b = $this->history(self::ALLOCATION_B, 'plain', 'stable-predecessor-manifest');

        $this->assertAllocatedDifferently($a['keys'], $b['keys']);
        $this->assertNull($a['identity']['correction_semantic_hash'], 'a plain publication carries no correction identity');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $a['identity']['publication_manifest_hash']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $a['identity']['seal_fingerprint']);

        $this->assertSame($a['identity'], $b['identity']);
    }

    public function test_the_predecessor_manifest_is_the_lineage_the_repository_binds(): void
    {
        $a = $this->history(self::ALLOCATION_A, 'correction', 'stable-predecessor-manifest');
        $c = $this->history(self::ALLOCATION_A, 'correction', 'changed-predecessor-manifest');

        foreach ($a['identity'] as $name => $value) {
            $this->assertNotSame($value, $c['identity'][$name], $name.' must follow the predecessor manifest');
        }
    }

    /**
     * Seal verification on the promotion path. A V2 candidate is sealed, one piece of persisted
     * semantic material is then changed directly in the database -- as an out-of-band edit would,
     * past the repository's own mutability guard -- and promotion must refuse it with the check
     * that owns that material, leaving the current pointer where it was.
     *
     * @dataProvider tamperedSealedMaterial
     */
    public function test_promotion_refuses_tampered_v2_material_and_leaves_the_pointer_unchanged(
        string $flow,
        string $tamper,
        string $expectedCode
    ): void {
        $ids = self::ALLOCATION_A;
        $publicationId = $this->sealedCandidate($ids, $flow, 'stable-predecessor-manifest');
        $this->tamper($tamper, $publicationId, $ids);

        try {
            (new EodPublicationRepository())->promoteCandidateToCurrent(
                EodRun::query()->findOrFail($ids['candidate_run']),
                $flow === 'correction' ? $ids['baseline_publication'] : null
            );
            $this->fail('promotion accepted tampered V2 material: '.$flow.'/'.$tamper);
        } catch (RuntimeException $e) {
            $this->assertStringContainsString($expectedCode, $e->getMessage(), $flow.'/'.$tamper);
        }

        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', self::TRADE_DATE)->first();
        if ($flow === 'correction') {
            $this->assertSame($ids['baseline_publication'], (int) $pointer->publication_id, 'the prior publication must stay current');
        } else {
            $this->assertNull($pointer, 'a refused first publication must not become current');
        }
        $this->assertSame(0, (int) DB::table('eod_publications')->where('publication_id', $publicationId)->value('is_current'));
    }

    public function tamperedSealedMaterial(): array
    {
        return [
            'correction: publication manifest hash' => ['correction', 'manifest', 'FINALIZE_SEAL_FINGERPRINT_INVALID'],
            'correction: publication correction identity' => ['correction', 'correction_identity', 'FINALIZE_SEAL_FINGERPRINT_INVALID'],
            'correction: seal fingerprint on both sides' => ['correction', 'seal_both', 'FINALIZE_SEAL_FINGERPRINT_INVALID'],
            'correction: seal fingerprint on the run only' => ['correction', 'seal_run_only', 'FINALIZE_SEAL_FINGERPRINT_MISMATCH'],
            // A correction's identity binds its replacement roots and is verified before the manifest.
            'correction: artifact root on both sides' => ['correction', 'artifact', 'CORRECTION_SEMANTIC_IDENTITY_MISMATCH'],
            'correction: semantic predecessor' => ['correction', 'predecessor', 'DATASET_MANIFEST_INVALID'],
            'correction: correction reason' => ['correction', 'correction_reason', 'CORRECTION_SEMANTIC_IDENTITY_MISMATCH'],
            'correction: unknown publication profile' => ['correction', 'publication_profile', 'PUBLICATION_SEMANTIC_PROFILE_MISMATCH'],
            'correction: unknown artifact profile' => ['correction', 'artifact_profile', 'PUBLICATION_SEMANTIC_PROFILE_UNSUPPORTED'],
            'correction: legacy artifact profile on the run' => ['correction', 'run_legacy_profile', 'FINALIZE_HASH_PROFILE_MISMATCH'],
            'plain: publication manifest hash' => ['plain', 'manifest', 'FINALIZE_SEAL_FINGERPRINT_INVALID'],
            'plain: artifact root on both sides' => ['plain', 'artifact', 'DATASET_MANIFEST_INVALID'],
            'plain: seal fingerprint on both sides' => ['plain', 'seal_both', 'FINALIZE_SEAL_FINGERPRINT_INVALID'],
            'plain: correction identity injected' => ['plain', 'correction_identity', 'FINALIZE_SEAL_FINGERPRINT_INVALID'],
            'plain: legacy artifact profile on the run' => ['plain', 'run_legacy_profile', 'FINALIZE_HASH_PROFILE_MISMATCH'],
        ];
    }

    private function tamper(string $tamper, int $publicationId, array $ids): void
    {
        $publication = DB::table('eod_publications')->where('publication_id', $publicationId);
        $run = DB::table('eod_runs')->where('run_id', $ids['candidate_run']);
        switch ($tamper) {
            case 'manifest':
                $publication->update(['publication_manifest_hash' => hash('sha256', 'tampered-manifest')]);
                break;
            case 'correction_identity':
                $publication->update(['correction_semantic_hash' => hash('sha256', 'tampered-correction')]);
                break;
            case 'seal_both':
                $publication->update(['seal_fingerprint' => hash('sha256', 'forged-seal')]);
                $run->update(['seal_fingerprint' => hash('sha256', 'forged-seal')]);
                break;
            case 'seal_run_only':
                $run->update(['seal_fingerprint' => hash('sha256', 'forged-seal')]);
                break;
            case 'artifact':
                // Both sides, so the run/publication hash comparison cannot be what catches it.
                $publication->update(['bars_batch_hash' => hash('sha256', 'tampered-bars')]);
                $run->update(['bars_batch_hash' => hash('sha256', 'tampered-bars')]);
                break;
            case 'predecessor':
                DB::table('eod_publications')->insert([
                    'publication_id' => 999001,
                    'trade_date' => '2026-03-19',
                    'run_id' => 999001,
                    'publication_version' => 1,
                    'is_current' => 0,
                    'seal_state' => 'SEALED',
                    'publication_manifest_hash' => hash('sha256', 'decoy-predecessor-manifest'),
                    'created_at' => '2026-03-19 17:00:00',
                    'updated_at' => '2026-03-19 17:00:00',
                ]);
                $publication->update([
                    'supersedes_publication_id' => 999001,
                    'previous_publication_id' => 999001,
                    'replaced_publication_id' => 999001,
                ]);
                break;
            case 'correction_reason':
                DB::table('eod_dataset_corrections')->where('correction_id', $ids['correction'])
                    ->update(['correction_reason_code' => 'LATE_SOURCE_CORRECTION']);
                break;
            case 'publication_profile':
                $publication->update(['publication_semantic_profile' => 'market-data-publication-semantic/v9']);
                break;
            case 'artifact_profile':
                $publication->update(['artifact_hash_profile' => 'market-data-semantic-hash/v9']);
                $run->update(['artifact_hash_profile' => 'market-data-semantic-hash/v9']);
                break;
            case 'run_legacy_profile':
                $run->update(['artifact_hash_profile' => ArtifactSemanticHashService::LEGACY_PROFILE_V1]);
                break;
            default:
                throw new LogicException('unknown tamper '.$tamper);
        }
    }

    private function assertAllocatedDifferently(array $a, array $b): void
    {
        $this->assertSame(array_keys($a), array_keys($b));
        $this->assertNotEmpty($a);
        foreach ($a as $key => $value) {
            $this->assertNotSame($value, $b[$key], 'precondition: '.$key.' must be allocated differently');
        }
    }

    /**
     * @return array{keys: array<string,int>, identity: array<string,?string>}
     */
    private function history(array $ids, string $flow, string $predecessorManifestSeed): array
    {
        $publicationId = $this->sealedCandidate($ids, $flow, $predecessorManifestSeed);

        $repository = new EodPublicationRepository();
        $repository->promoteCandidateToCurrent(
            EodRun::query()->findOrFail($ids['candidate_run']),
            $flow === 'correction' ? $ids['baseline_publication'] : null
        );
        if ($flow === 'correction') {
            (new EodCorrectionRepository())->markPublished(
                $ids['correction'],
                $ids['candidate_run'],
                $ids['baseline_run'],
                'production-path allocation proof',
                $ids['baseline_publication'],
                $publicationId
            );
        }

        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        $runRow = DB::table('eod_runs')->where('run_id', $ids['candidate_run'])->first();
        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', self::TRADE_DATE)->first();

        $this->assertSame(1, (int) $publication->is_current, 'the V2 candidate must be promoted');
        $this->assertSame((int) $publicationId, (int) $pointer->publication_id);
        $this->assertSame(PublicationSemanticIdentityService::PROFILE_V2, $publication->publication_semantic_profile);
        $this->assertSame(PublicationSemanticIdentityService::PROFILE_V2, $runRow->publication_semantic_profile);
        $this->assertSame($publication->seal_fingerprint, $runRow->seal_fingerprint);
        $this->assertTrue($repository->assertPublicationManifestHashValid($publicationId));

        $keys = [
            'candidate_run' => (int) $runRow->run_id,
            'candidate_publication' => (int) $publication->publication_id,
            'config_snapshot' => (int) $publication->config_snapshot_id,
            'factor_set' => (int) $publication->factor_set_id,
        ];
        if ($flow === 'correction') {
            $correction = DB::table('eod_dataset_corrections')->where('correction_id', $ids['correction'])->first();
            $this->assertSame('PUBLISHED', $correction->status);
            $this->assertSame(PublicationSemanticIdentityService::PROFILE_V2, $correction->semantic_identity_profile);
            $this->assertSame($publication->correction_semantic_hash, $correction->semantic_identity_hash);
            $keys += [
                'baseline_run' => (int) $ids['baseline_run'],
                'baseline_publication' => (int) $publication->supersedes_publication_id,
                'correction' => (int) $correction->correction_id,
            ];
        } else {
            $this->assertNull($publication->supersedes_publication_id);
        }

        return [
            'keys' => $keys,
            'identity' => [
                'publication_manifest_hash' => $publication->publication_manifest_hash,
                'correction_semantic_hash' => $publication->correction_semantic_hash,
                'seal_fingerprint' => $publication->seal_fingerprint,
            ],
        ];
    }

    /**
     * Builds one history in a fresh database and takes its V2 candidate through the pipeline's
     * seal order: seal, run seal, manifest re-verification and, for a correction, reseal.
     */
    private function sealedCandidate(array $ids, string $flow, string $predecessorManifestSeed): int
    {
        // A fresh in-memory database per history: nothing is shared between them but the facts.
        $this->bootMarketDataSqlite();
        $this->seedUnrelatedRows($ids);
        $correction = $flow === 'correction';
        if ($ids['candidate_first']) {
            $this->seedCandidateRun($ids, $correction);
            if ($correction) {
                $this->seedBaseline($ids, hash('sha256', $predecessorManifestSeed));
            }
        } else {
            if ($correction) {
                $this->seedBaseline($ids, hash('sha256', $predecessorManifestSeed));
            }
            $this->seedCandidateRun($ids, $correction);
        }

        $repository = new EodPublicationRepository();
        $run = EodRun::query()->findOrFail($ids['candidate_run']);
        $candidate = $repository->getOrCreateCandidatePublication($run, $correction ? $ids['baseline_publication'] : null);
        $repository->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => hash('sha256', 'v2-bars'),
            'indicators_batch_hash' => hash('sha256', 'v2-indicators'),
            'eligibility_batch_hash' => hash('sha256', 'v2-eligibility'),
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
        ]);
        $factorHash = hash('sha256', 'publication-semantic-factor-set');
        $repository->bindCandidateAnalyticalProduct(
            $candidate->publication_id,
            $ids['candidate_run'],
            'STRUCTURAL_ADJUSTED',
            'structural_adjusted_v1',
            $factorHash,
            $ids['factor_set']
        );
        $this->bindLineageAndPrepareManifest($candidate->publication_id, $factorHash, $ids);

        // The pipeline's own order (MarketDataPipelineService seal step).
        $sealed = $repository->sealCandidatePublication($run->fresh(), 'system');
        (new EodRunRepository())->markSealed($run->fresh(), 'system', 'production-path allocation proof');
        $repository->assertPublicationManifestHashValid($sealed->publication_id);
        if ($correction) {
            (new EodCorrectionRepository())->markResealed($ids['correction'], $ids['candidate_run'], $sealed->publication_id);
        }

        $this->assertSame('SEALED', $sealed->seal_state);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $sealed->seal_fingerprint);

        return (int) $sealed->publication_id;
    }

    private function seedUnrelatedRows(array $ids): void
    {
        for ($i = 1; $i <= $ids['unrelated_rows']; $i++) {
            DB::table('eod_runs')->insert([
                'run_id' => $ids['baseline_run'] + $i,
                'trade_date_requested' => '2026-03-19',
                'lifecycle_state' => 'COMPLETED',
                'stage' => 'FINALIZE',
                'source' => 'manual_file',
                'created_at' => '2026-03-19 17:00:00',
                'updated_at' => '2026-03-19 17:00:00',
            ]);
            DB::table('eod_publications')->insert([
                'publication_id' => $ids['baseline_publication'] + $i,
                'trade_date' => '2026-03-19',
                'run_id' => $ids['baseline_run'] + $i,
                'publication_version' => $i,
                'is_current' => 0,
                'seal_state' => 'UNSEALED',
                'created_at' => '2026-03-19 17:00:00',
                'updated_at' => '2026-03-19 17:00:00',
            ]);
            DB::table('eod_dataset_corrections')->insert([
                'correction_id' => $ids['correction'] + $i,
                'trade_date' => '2026-03-19',
                'correction_reason_code' => 'SOURCE_CORRECTION',
                'status' => 'REQUESTED',
                'requested_by' => 'test',
                'requested_at' => '2026-03-19 17:00:00',
                'created_at' => '2026-03-19 17:00:00',
                'updated_at' => '2026-03-19 17:00:00',
            ]);
        }
    }

    private function seedBaseline(array $ids, string $predecessorManifestHash): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => $ids['baseline_run'],
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'config_version' => 'cfg-old',
            'publication_id' => $ids['baseline_publication'],
            'publication_version' => 1,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 100,
            'coverage_available_count' => 100,
            'coverage_missing_count' => 0,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'is_current_publication' => 1,
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => hash('sha256', 'publication-semantic-baseline-factor-set'),
            'sealed_at' => '2026-03-20 17:20:00',
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
        DB::table('eod_publications')->insert([
            'publication_id' => $ids['baseline_publication'],
            'trade_date' => self::TRADE_DATE,
            'run_id' => $ids['baseline_run'],
            'publication_version' => 1,
            'is_current' => 1,
            'supersedes_publication_id' => null,
            'seal_state' => 'SEALED',
            'bars_batch_hash' => hash('sha256', 'bars-old'),
            'indicators_batch_hash' => hash('sha256', 'ind-old'),
            'eligibility_batch_hash' => hash('sha256', 'elig-old'),
            'publication_manifest_hash' => $predecessorManifestHash,
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => hash('sha256', 'publication-semantic-baseline-factor-set'),
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => self::TRADE_DATE,
            'publication_id' => $ids['baseline_publication'],
            'run_id' => $ids['baseline_run'],
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
    }

    private function seedCandidateRun(array $ids, bool $correction): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => $ids['candidate_run'],
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'knowledge_cutoff_at' => '2026-03-20 18:00:00',
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'config_version' => 'cfg-new',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 100,
            'coverage_available_count' => 100,
            'coverage_missing_count' => 0,
            'coverage_ratio' => 1.0,
            'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 2,
            'indicators_rows_written' => 2,
            'eligibility_rows_written' => 2,
            'is_current_publication' => 0,
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
            'publication_semantic_profile' => PublicationSemanticIdentityService::PROFILE_V2,
            'correction_id' => $correction ? $ids['correction'] : null,
            'promote_mode' => $correction ? 'correction_current' : null,
            'publish_target' => $correction ? 'current_replace' : null,
            'started_at' => '2026-03-20 17:01:00',
            'created_at' => '2026-03-20 17:01:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);
        if (! $correction) {
            return;
        }
        DB::table('eod_dataset_corrections')->insert([
            'correction_id' => $ids['correction'],
            'trade_date' => self::TRADE_DATE,
            'baseline_publication_id' => $ids['baseline_publication'],
            'correction_reason_code' => 'SOURCE_CORRECTION',
            'status' => 'APPROVED',
            'requested_by' => 'test',
            'requested_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:00:00',
        ]);
    }

    /**
     * The stage-eight lineage bundle from PublicationRepositoryIntegrationTest, with every local key
     * taken from the history instead of fixed.
     */
    private function bindLineageAndPrepareManifest(int $publicationId, string $factorHash, array $ids): void
    {
        DB::table('md_adjustment_factor_sets')->insert([
            'factor_set_id' => $ids['factor_set'],
            'factor_set_uid' => $factorHash,
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'factor_formula_version' => 'structural_factor_product_v1',
            'config_snapshot_id' => $ids['config_snapshot'],
            'state' => 'BOUND',
            'content_hash' => $factorHash,
            'recorded_at' => '2026-03-20 17:10:00',
            'created_at' => '2026-03-20 17:10:00',
        ]);
        DB::table('md_config_snapshots')->insert([
            'config_snapshot_id' => $ids['config_snapshot'],
            'snapshot_uid' => hash('sha256', 'publication-semantic-config'),
            'snapshot_schema_version' => 'market_data_config_snapshot_v1',
            'serialization_version' => 'canonical_json_v1',
            'resolved_config_json' => '{}',
            'config_hash' => hash('sha256', 'publication-semantic-config-content'),
            'registry_revision' => 'test-registry-revision',
            'effective_at' => '2026-03-20 17:00:00',
            'recorded_at' => '2026-03-20 17:00:00',
            'build_id' => 'test-build',
            'environment_profile' => 'testing',
            'resolver_version' => 'test-resolver-v1',
            'created_at' => '2026-03-20 17:00:00',
        ]);

        $sourceScaleHash = hash('sha256', 'test-source-scale');
        $marketStructureHash = hash('sha256', 'test-market-structure');
        $factorDecisionHash = hash('sha256', 'test-factor-decisions');
        $observationManifestHash = hash('sha256', 'test-observation-manifest');
        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->first();

        DB::table('eod_publications')->where('publication_id', $publicationId)->update([
            'source_scale_assessment_set_hash' => $sourceScaleHash,
            'market_structure_revision_set_hash' => $marketStructureHash,
            'factor_decision_set_hash' => $factorDecisionHash,
            'config_snapshot_id' => $ids['config_snapshot'],
            'observation_manifest_hash' => $observationManifestHash,
        ]);
        $boundInputContextJson = json_encode([
            'schema_version' => 'md_publication_inputs_v2',
            'scope' => ['config_snapshot_id' => $ids['config_snapshot']],
            'components' => [],
            'component_manifest' => ['status' => 'COMPLETE'],
        ]);
        DB::table('md_publication_lineage_bindings')->insert([
            'publication_id' => $publicationId,
            'config_snapshot_id' => $ids['config_snapshot'],
            'factor_set_id' => $ids['factor_set'],
            'observation_manifest_hash' => $observationManifestHash,
            'identity_revision_set_hash' => hash('sha256', 'test-identity'),
            'calendar_revision_set_hash' => hash('sha256', 'test-calendar'),
            'status_revision_set_hash' => hash('sha256', 'test-status'),
            'event_revision_set_hash' => hash('sha256', 'test-event'),
            'source_scale_assessment_set_hash' => $sourceScaleHash,
            'market_structure_revision_set_hash' => $marketStructureHash,
            'factor_decision_set_hash' => $factorDecisionHash,
            'formula_version' => 'eod_indicators_v1',
            'build_id' => 'test-build',
            'read_model_version' => 'market_data_read_product_v1',
            'bound_input_schema_version' => 'md_publication_inputs_v2',
            'bound_input_context_json' => $boundInputContextJson,
            'bound_input_context_hash' => hash('sha256', $boundInputContextJson),
            'bound_input_capture_manifest_json' => '{}',
            'created_at' => '2026-03-20 17:10:00',
        ]);
        DB::table('eod_runs')->where('run_id', $publication->run_id)->update([
            'config_snapshot_id' => $ids['config_snapshot'],
            'observation_manifest_hash' => $observationManifestHash,
            'bars_batch_hash' => $publication->bars_batch_hash,
            'indicators_batch_hash' => $publication->indicators_batch_hash,
            'eligibility_batch_hash' => $publication->eligibility_batch_hash,
        ]);
        DB::table('eod_bars_history')->insert([
            'publication_id' => $publicationId,
            'trade_date' => self::TRADE_DATE,
            'ticker_id' => $ids['ticker'],
            'source' => 'MANUAL_FILE',
            'run_id' => $publication->run_id,
            'canonicalization_version' => 'eod_canonical_v1',
            'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED',
            'created_at' => '2026-03-20 17:10:00',
        ]);

        (new EodPublicationRepository())->prepareCandidateManifestForSeal(
            EodRun::query()->findOrFail($publication->run_id),
            $publicationId
        );
    }
}
