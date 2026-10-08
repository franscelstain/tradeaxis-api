<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use App\Application\MarketData\Services\SemanticNestedIdentityService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — the manifest of a publication that has been SUPERSEDED, on a history produced by the repository's own
 * seal and promotion path (`MD-S075-R0083`, `R0084`, `R0100`, `R0110`, `R0111`).
 *
 * `B19PublicationManifestValueProvenanceTest` flips `is_current` by hand. That cannot show what the production
 * path does to the predecessor when a correction is promoted over it. This guard runs the real path twice on one
 * trade date — a first publication is sealed and promoted, a correction of it is sealed and promoted over it —
 * and reads both manifests before and after:
 *
 *  - the predecessor's manifest changes in `is_current` and nothing else, its manifest hash still verifies under
 *    its governed profile, and it is not rewritten to look current;
 *  - the successor names the predecessor with the publication-shaped `supersedes_publication_id`, is current,
 *    and carries a different manifest hash;
 *  - the evidence export of the predecessor's own run, made after it was superseded, writes that same
 *    historical manifest.
 *
 * The rows the repository does not produce (runs, config snapshot, factor set, lineage binding) are seeded the
 * way `PublicationSemanticIdentityProductionPathTest` seeds them; everything under test — candidate creation,
 * seal, promotion, supersession, the manifest builder — is the real code.
 */
class B19PublicationManifestSupersessionTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const DATE = '2026-03-20';

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

    // ------------------------------------------------------------------ history

    /** @return array<string,int|string> */
    private function spec(string $which): array
    {
        return $which === 'first'
            ? ['run' => 41, 'config' => 61, 'factor' => 6001, 'ticker' => 811, 'tag' => 'first', 'at' => '17:01:00']
            : ['run' => 42, 'config' => 62, 'factor' => 6002, 'ticker' => 822, 'tag' => 'second', 'at' => '17:31:00', 'correction' => 55];
    }

    private function insertRun(array $s, bool $correction): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => $s['run'], 'trade_date_requested' => self::DATE, 'trade_date_effective' => self::DATE, 'knowledge_cutoff_at' => self::DATE.' 18:00:00',
            'lifecycle_state' => 'COMPLETED', 'quality_gate_state' => 'PASS', 'stage' => 'FINALIZE', 'source' => 'manual_file', 'config_version' => 'cfg-'.$s['tag'],
            'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE', 'coverage_gate_state' => 'PASS', 'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'coverage_universe_count' => 100, 'coverage_available_count' => 100, 'coverage_missing_count' => 0, 'coverage_ratio' => 1.0, 'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO', 'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE', 'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 2, 'indicators_rows_written' => 3, 'eligibility_rows_written' => 4, 'is_current_publication' => 0,
            'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'structural_adjusted_v1',
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2, 'publication_semantic_profile' => PublicationSemanticIdentityService::PROFILE_V2,
            'correction_id' => $correction ? $s['correction'] : null, 'promote_mode' => $correction ? 'correction_current' : null, 'publish_target' => $correction ? 'current_replace' : null,
            'freshness_state' => 'NOT_APPLICABLE', 'started_at' => self::DATE.' '.$s['at'], 'created_at' => self::DATE.' '.$s['at'], 'updated_at' => self::DATE.' 17:50:00',
        ]);
    }

    /** Seals and promotes one publication through the repository's own order; returns its publication id. */
    private function sealAndPromote(array $s, ?int $baselinePublication, ?int $baselineRun): int
    {
        $correction = $baselinePublication !== null;
        $this->insertRun($s, $correction);
        if ($correction) {
            DB::table('eod_dataset_corrections')->insert([
                'correction_id' => $s['correction'], 'trade_date' => self::DATE, 'baseline_publication_id' => $baselinePublication, 'correction_reason_code' => 'SOURCE_CORRECTION',
                'status' => 'APPROVED', 'requested_by' => 'test', 'requested_at' => self::DATE.' 17:00:00', 'created_at' => self::DATE.' 17:00:00', 'updated_at' => self::DATE.' 17:00:00',
            ]);
        }
        $repository = new EodPublicationRepository();
        $run = EodRun::query()->findOrFail($s['run']);
        $candidate = $repository->getOrCreateCandidatePublication($run, $baselinePublication);
        $repository->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => hash('sha256', 'bars-'.$s['tag']), 'indicators_batch_hash' => hash('sha256', 'indicators-'.$s['tag']),
            'eligibility_batch_hash' => hash('sha256', 'eligibility-'.$s['tag']), 'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
        ]);
        $factorHash = hash('sha256', 'factor-set-'.$s['tag']);
        $repository->bindCandidateAnalyticalProduct($candidate->publication_id, $s['run'], 'STRUCTURAL_ADJUSTED', 'structural_adjusted_v1', $factorHash, $s['factor']);
        $this->bindLineage($candidate->publication_id, $factorHash, $s);
        $repository->prepareCandidateManifestForSeal(EodRun::query()->findOrFail($s['run']), $candidate->publication_id);

        $sealed = $repository->sealCandidatePublication($run->fresh(), 'system');
        (new EodRunRepository())->markSealed($run->fresh(), 'system', 'b19 manifest supersession proof');
        $repository->assertPublicationManifestHashValid($sealed->publication_id);
        if ($correction) {
            (new EodCorrectionRepository())->markResealed($s['correction'], $s['run'], $sealed->publication_id);
        }
        $repository->promoteCandidateToCurrent(EodRun::query()->findOrFail($s['run']), $baselinePublication);
        if ($correction) {
            (new EodCorrectionRepository())->markPublished($s['correction'], $s['run'], $baselineRun, 'b19 manifest supersession proof', $baselinePublication, $sealed->publication_id);
        }

        return (int) $sealed->publication_id;
    }

    private function bindLineage(int $publicationId, string $factorHash, array $s): void
    {
        $tag = $s['tag'];
        DB::table('md_adjustment_factor_sets')->insert([
            'factor_set_id' => $s['factor'], 'factor_set_uid' => $factorHash, 'price_product_code' => 'STRUCTURAL_ADJUSTED', 'factor_formula_version' => 'structural_factor_product_v1',
            'config_snapshot_id' => $s['config'], 'state' => 'BOUND', 'content_hash' => $factorHash, 'recorded_at' => self::DATE.' 17:10:00', 'created_at' => self::DATE.' 17:10:00',
        ]);
        DB::table('md_config_snapshots')->insert([
            'config_snapshot_id' => $s['config'], 'snapshot_uid' => hash('sha256', 'config-uid-'.$tag), 'snapshot_schema_version' => 'market_data_config_snapshot_v1',
            'serialization_version' => 'canonical_json_v1', 'resolved_config_json' => '{}', 'config_hash' => hash('sha256', 'config-content-'.$tag), 'registry_revision' => 'registry-'.$tag,
            'effective_at' => self::DATE.' 17:00:00', 'recorded_at' => self::DATE.' 17:00:00', 'build_id' => 'test-build', 'environment_profile' => 'testing', 'resolver_version' => 'r1', 'created_at' => self::DATE.' 17:00:00',
        ]);
        $observation = hash('sha256', 'observation-'.$tag);
        $scale = hash('sha256', 'scale-'.$tag);
        $structure = hash('sha256', 'structure-'.$tag);
        $decisions = hash('sha256', 'decisions-'.$tag);
        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        DB::table('eod_publications')->where('publication_id', $publicationId)->update([
            'source_scale_assessment_set_hash' => $scale, 'market_structure_revision_set_hash' => $structure, 'factor_decision_set_hash' => $decisions,
            'config_snapshot_id' => $s['config'], 'observation_manifest_hash' => $observation,
        ]);
        $contextJson = json_encode(['schema_version' => 'md_publication_inputs_v2', 'scope' => ['config_snapshot_id' => $s['config']], 'components' => [], 'component_manifest' => ['status' => 'COMPLETE']]);
        $nested = [];
        foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $column) {
            $nested[$column] = hash('sha256', $tag.'-'.$column);
        }
        DB::table('md_publication_lineage_bindings')->insert([
            'publication_id' => $publicationId, 'config_snapshot_id' => $s['config'], 'factor_set_id' => $s['factor'], 'observation_manifest_hash' => $observation,
            'identity_revision_set_hash' => hash('sha256', 'identity-'.$tag), 'calendar_revision_set_hash' => hash('sha256', 'calendar-'.$tag), 'status_revision_set_hash' => hash('sha256', 'status-'.$tag),
            'event_revision_set_hash' => hash('sha256', 'event-'.$tag), 'source_scale_assessment_set_hash' => $scale, 'market_structure_revision_set_hash' => $structure, 'factor_decision_set_hash' => $decisions,
            'formula_version' => 'eod_indicators_v1', 'build_id' => 'test-build', 'read_model_version' => 'market_data_read_product_v1', 'bound_input_schema_version' => 'md_publication_inputs_v2',
            'bound_input_context_json' => $contextJson, 'bound_input_context_hash' => hash('sha256', $contextJson), 'bound_input_capture_manifest_json' => '{}', 'created_at' => self::DATE.' 17:10:00',
            'semantic_nested_identity_version' => SemanticNestedIdentityService::VERSION,
        ] + $nested);
        DB::table('eod_runs')->where('run_id', $publication->run_id)->update([
            'config_snapshot_id' => $s['config'], 'observation_manifest_hash' => $observation,
            'bars_batch_hash' => $publication->bars_batch_hash, 'indicators_batch_hash' => $publication->indicators_batch_hash, 'eligibility_batch_hash' => $publication->eligibility_batch_hash,
        ]);
        DB::table('eod_bars_history')->insert([
            'publication_id' => $publicationId, 'trade_date' => self::DATE, 'ticker_id' => $s['ticker'], 'source' => 'MANUAL_FILE', 'run_id' => $publication->run_id,
            'canonicalization_version' => 'eod_canonical_v1', 'price_product_code' => 'RAW', 'quality_state' => 'VALIDATED', 'created_at' => self::DATE.' 17:10:00',
        ]);
    }

    /** @return array<string,mixed> */
    private function manifest(int $publicationId): array
    {
        return (array) (new EodPublicationRepository())->buildManifestByPublicationId($publicationId);
    }

    /** @return array{first:int,second:int,before:array<string,mixed>,after:array<string,mixed>,successor:array<string,mixed>} */
    private function twoPublications(): array
    {
        $first = $this->sealAndPromote($this->spec('first'), null, null);
        $before = $this->manifest($first);
        $second = $this->sealAndPromote($this->spec('second'), $first, $this->spec('first')['run']);

        return ['first' => $first, 'second' => $second, 'before' => $before, 'after' => $this->manifest($first), 'successor' => $this->manifest($second)];
    }

    // ------------------------------------------------------------------ guards

    /**
     * `MD-S075-R0111`, `R0083`: promoting a correction over a publication changes its manifest in `is_current`
     * and nothing else — not its version, seal, hashes, row counts, dates or manifest hash.
     */
    public function test_superseding_a_publication_changes_its_current_marking_and_nothing_else(): void
    {
        $h = $this->twoPublications();

        $this->assertTrue($h['before']['is_current'], 'precondition: the first publication was current before the correction');
        $this->assertFalse($h['after']['is_current'], 'R0111/R0083: the superseded publication is still reported as current');
        $changed = array_keys(array_filter($h['before'], function ($v, $k) use ($h) { return $h['after'][$k] !== $v; }, ARRAY_FILTER_USE_BOTH));
        $this->assertSame(['is_current'], $changed, 'R0111: superseding the publication rewrote its manifest beyond the current marking: '.implode(',', $changed));
        $this->assertSame('SEALED', $h['after']['seal_state']);
        $this->assertSame($h['before']['publication_manifest_hash'], $h['after']['publication_manifest_hash']);
    }

    /** `MD-S075-R0100`, `R0111`: a superseded manifest remains audit-valid — its stored hash still verifies under its governed profile. */
    public function test_a_superseded_manifest_hash_still_verifies_under_its_governed_profile(): void
    {
        $h = $this->twoPublications();
        $repository = new EodPublicationRepository();

        $this->assertTrue($repository->assertPublicationManifestHashValid($h['first']), 'R0111: the superseded publication no longer verifies');
        $this->assertTrue($repository->assertPublicationManifestHashValid($h['second']));
        $this->assertSame(PublicationSemanticIdentityService::PROFILE_V2, $h['after']['publication_semantic_profile']);
        $this->assertSame(ArtifactSemanticHashService::PROFILE_V2, $h['after']['artifact_hash_profile']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $h['after']['publication_manifest_hash']);
    }

    /**
     * `MD-S075-R0100`: the manifest hash is not a stored constant of the file — damaging the stored value is caught by
     * the governed verifier, and damaging the SUCCESSOR's value does not reach back into the superseded publication.
     */
    public function test_a_damaged_stored_manifest_hash_is_refused_by_the_governed_verifier(): void
    {
        $h = $this->twoPublications();
        DB::table('eod_publications')->where('publication_id', $h['second'])->update(['publication_manifest_hash' => hash('sha256', 'damaged')]);

        try {
            (new EodPublicationRepository())->assertPublicationManifestHashValid($h['second']);
            $this->fail('R0100: a manifest hash that does not match the canonical semantic content verified');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('DATASET_MANIFEST_INVALID', $e->getMessage());
        }
        $this->assertTrue((new EodPublicationRepository())->assertPublicationManifestHashValid($h['first']), 'R0111: damaging the successor made the superseded manifest invalid');
    }

    /**
     * `MD-S075-R0100`: the successor's identity binds its predecessor's manifest hash, so a damaged predecessor hash is
     * refused for the successor too — the hash is part of the lineage, not a label.
     */
    public function test_a_damaged_predecessor_hash_is_refused_for_the_predecessor_and_its_successor(): void
    {
        $h = $this->twoPublications();
        DB::table('eod_publications')->where('publication_id', $h['first'])->update(['publication_manifest_hash' => hash('sha256', 'damaged')]);

        foreach (['first', 'second'] as $which) {
            try {
                (new EodPublicationRepository())->assertPublicationManifestHashValid($h[$which]);
                $this->fail('R0100: the '.$which.' publication verified although the predecessor\'s manifest hash was damaged');
            } catch (RuntimeException $e) {
                $this->assertMatchesRegularExpression('/DATASET_MANIFEST_INVALID|CORRECTION_SEMANTIC_IDENTITY_MISMATCH/', $e->getMessage(), $which);
            }
        }
    }

    /** `MD-S075-R0083`, `R0084`, `R0110`: the successor is current, names its predecessor by PUBLICATION id, and has a manifest of its own. */
    public function test_the_successor_names_its_predecessor_as_a_publication_and_is_current(): void
    {
        $h = $this->twoPublications();
        $successor = $h['successor'];

        $this->assertTrue($successor['is_current']);
        $this->assertSame($h['first'], $successor['supersedes_publication_id'], 'R0084');
        $this->assertNull($h['before']['supersedes_publication_id'], 'the first publication supersedes nothing');
        $this->assertSame(2, $successor['publication_version']);
        $this->assertSame(1, $h['after']['publication_version']);
        $this->assertNotSame($h['after']['publication_manifest_hash'], $successor['publication_manifest_hash'], 'a corrected publication must produce a new manifest');
        $this->assertArrayNotHasKey('supersedes_run_id', $successor, 'R0110');
        $this->assertArrayNotHasKey('is_current_publication', $successor, 'R0110');
        // the run ids are not the publication ids on this history, so a run id exported as a publication id would show
        $this->assertNotSame($this->spec('first')['run'], $successor['supersedes_publication_id']);
        $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', self::DATE)->first();
        $this->assertSame($h['second'], (int) $pointer->publication_id, 'the current pointer and the manifest\'s is_current disagree');
    }

    /**
     * `MD-S075-R0111`, `R0108`: the evidence export of the superseded publication's own run, made AFTER the correction,
     * writes that publication's historical manifest — not the current one and not a rewritten one.
     */
    public function test_the_export_of_a_superseded_run_writes_its_historical_manifest(): void
    {
        $h = $this->twoPublications();
        $dir = sys_get_temp_dir().'/md_b19_pm_superseded_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($this->spec('first')['run'], $dir);
        $file = json_decode((string) file_get_contents($dir.'/publication_manifest.json'), true);
        $summary = json_decode((string) file_get_contents($dir.'/run_summary.json'), true);

        $this->assertSame(json_decode(json_encode($h['after']), true), $file, 'R0111: the exported historical manifest differs from the stored state');
        $this->assertSame($h['first'], $file['publication_id']);
        $this->assertFalse($file['is_current'], 'R0111: the exported historical manifest claims to be current');
        $this->assertSame($h['before']['publication_manifest_hash'], $file['publication_manifest_hash']);
        $this->assertFalse($summary['is_current_publication'], 'the run summary of the superseded run disagrees with its manifest');
    }
}
