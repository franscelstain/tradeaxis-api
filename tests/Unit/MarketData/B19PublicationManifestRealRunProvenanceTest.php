<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\R0025SyntheticV2World;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * `MD-B19` — `publication_manifest.json` of a REAL sealed publication, read against the database
 * (`MD-S075-R0079..R0111`).
 *
 * `B19PublicationManifestValueProvenanceTest` proves the manifest builder's mapping on seeded rows in which every
 * source differs. This guard proves the other half: that on a publication produced by the real pipeline — real
 * run, real seal, real lineage binding, real config snapshot — the FILE the exporter writes agrees with an
 * INDEPENDENT read of `eod_publications`, `eod_runs`, `md_config_snapshots`, `md_publication_lineage_bindings`,
 * `eod_bars_history` and the pointer table. Expectations are read from those tables directly, not from the
 * manifest builder, and the hash is verified by the repository's governed verifier.
 *
 * The world is the one the R0025 candidate fixture is built on. It does not mock a repository.
 */
class B19PublicationManifestRealRunProvenanceTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    /** @return array{0:array<string,mixed>,1:array<string,mixed>,2:array<string,mixed>,3:array<string,mixed>,4:array<string,mixed>,5:array<string,mixed>} file, publication, run, lineage, config snapshot, pointer */
    private function exportRealPublication(): array
    {
        $w = R0025SyntheticV2World::build();
        $dir = sys_get_temp_dir().'/md_b19_pm_real_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence($w['run_id'], $dir);
        $this->assertFileExists($dir.'/publication_manifest.json', 'the real sealed run exported no publication_manifest.json');
        $file = json_decode((string) file_get_contents($dir.'/publication_manifest.json'), true);
        $publication = (array) DB::table('eod_publications')->where('run_id', $w['run_id'])->first();
        $run = (array) DB::table('eod_runs')->where('run_id', $w['run_id'])->first();
        $lineage = (array) DB::table('md_publication_lineage_bindings')->where('publication_id', $publication['publication_id'])->first();
        $config = (array) DB::table('md_config_snapshots')->where('config_snapshot_id', $publication['config_snapshot_id'])->first();
        $pointer = (array) DB::table('eod_current_publication_pointer')->where('trade_date', $publication['trade_date'])->first();
        foreach (['publication' => $publication, 'run' => $run, 'lineage' => $lineage, 'config snapshot' => $config, 'pointer' => $pointer] as $name => $row) {
            $this->assertNotSame([], $row, 'the world produced no '.$name);
        }

        return [$file, $publication, $run, $lineage, $config, $pointer];
    }

    private function same($expected, $actual): bool
    {
        if ($expected === null || $actual === null) {
            return $expected === $actual;
        }
        if (is_bool($actual)) {
            $actual = $actual ? 1 : 0;
        }
        if (is_numeric($expected) && is_numeric($actual)) {
            return (float) $expected === (float) $actual;
        }

        return (string) $expected === (string) $actual;
    }

    /**
     * `MD-S075-R0079..R0107`: every one of the 29 minimum fields of a real sealed publication equals the value read
     * directly from its own source table.
     */
    public function test_every_minimum_field_of_a_real_publication_equals_its_source_row(): void
    {
        [$file, $pub, $run, $lineage, $config] = $this->exportRealPublication();
        $id = (int) $pub['publication_id'];
        $canonicalization = DB::table('eod_bars_history')->where('publication_id', $id)->distinct()->pluck('canonicalization_version')->all();
        $this->assertCount(1, $canonicalization, 'precondition: a sealed publication has exactly one canonicalization version');
        $temporal = (new DeterministicHashService())->hashCanonicalDocument([
            'trade_date' => (string) $pub['trade_date'], 'identity_revision_set_hash' => strtolower($lineage['identity_revision_set_hash']),
            'calendar_revision_set_hash' => strtolower($lineage['calendar_revision_set_hash']), 'status_revision_set_hash' => strtolower($lineage['status_revision_set_hash']),
        ]);

        $expected = [
            'R0079' => ['publication_id', $pub['publication_id']], 'R0080' => ['trade_date', $pub['trade_date']], 'R0081' => ['run_id', $pub['run_id']],
            'R0082' => ['publication_version', $pub['publication_version']], 'R0083' => ['is_current', $pub['is_current']], 'R0084' => ['supersedes_publication_id', $pub['supersedes_publication_id']],
            'R0085' => ['seal_state', $pub['seal_state']], 'R0086' => ['sealed_at', $pub['sealed_at']], 'R0087' => ['observation_manifest_hash', $pub['observation_manifest_hash']],
            'R0088' => ['config_snapshot_id', $pub['config_snapshot_id']], 'R0089' => ['config_snapshot_hash', $config['config_hash']], 'R0090' => ['temporal_revision_set_hash', $temporal],
            'R0091' => ['factor_set_id', $pub['factor_set_id']], 'R0092' => ['factor_set_hash', $pub['factor_set_hash']], 'R0093' => ['price_product_code', $pub['price_product_code']],
            'R0094' => ['canonicalization_version', $canonicalization[0]], 'R0095' => ['formula_version', $lineage['formula_version']], 'R0096' => ['read_model_version', $lineage['read_model_version']],
            'R0097' => ['bars_batch_hash', $pub['bars_batch_hash']], 'R0098' => ['indicators_batch_hash', $pub['indicators_batch_hash']], 'R0099' => ['eligibility_batch_hash', $pub['eligibility_batch_hash']],
            'R0100' => ['publication_manifest_hash', $pub['publication_manifest_hash']], 'R0101' => ['bars_rows_written', $run['bars_rows_written']],
            'R0102' => ['indicators_rows_written', $run['indicators_rows_written']], 'R0103' => ['eligibility_rows_written', $run['eligibility_rows_written']],
            'R0104' => ['trade_date_requested', $run['trade_date_requested']], 'R0105' => ['trade_date_effective', $run['trade_date_effective']], 'R0106' => ['readiness_state', $pub['readiness_state']],
            'R0107' => ['freshness_state', ArtifactSemanticHashService::normalizeFreshnessState($run['freshness_state'])],
        ];
        $this->assertCount(29, $expected);
        foreach ($expected as $rule => [$field, $value]) {
            $this->assertArrayHasKey($field, $file, $rule.': '.$field.' is absent');
            if ($rule === 'R0084') {
                continue;   // NULL for a first publication; asserted below as the legitimate NULL it is
            }
            $this->assertNotNull($value, $rule.': precondition: the world left '.$field.' NULL, so this guard could not tell a mapping from a NULL placeholder');
        }
        // R0084 is legitimately NULL for a first publication: it is checked as NULL, everything else as a value
        unset($expected['R0084']);
        foreach ($expected as $rule => [$field, $value]) {
            $this->assertTrue($this->same($value, $file[$field]), $rule.': '.$field.' is '.json_encode($file[$field]).', the tables say '.json_encode($value));
        }
        $this->assertNull($file['supersedes_publication_id'], 'R0084: a first publication supersedes nothing');
        $this->assertNull($pub['supersedes_publication_id']);
    }

    /**
     * `MD-S075-R0100`: the manifest hash of a real sealed publication is a SHA-256 value, equals the stored column,
     * names its governed profile, and VERIFIES under that profile; the same verifier refuses it once the stored value is
     * damaged — the exported value is checkable, not a decoration.
     */
    public function test_the_manifest_hash_of_a_real_publication_verifies_under_its_governed_profile(): void
    {
        [$file, $pub] = $this->exportRealPublication();
        $repository = new EodPublicationRepository();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $file['publication_manifest_hash']);
        $this->assertSame($pub['publication_manifest_hash'], $file['publication_manifest_hash']);
        $this->assertSame(PublicationSemanticIdentityService::PROFILE_V2, $file['publication_semantic_profile']);
        $this->assertSame($pub['publication_semantic_profile'], $file['publication_semantic_profile']);
        $this->assertSame($pub['artifact_hash_profile'], $file['artifact_hash_profile']);
        $this->assertTrue($repository->assertPublicationManifestHashValid((int) $pub['publication_id']), 'R0100: the exported hash does not verify under its governed profile');

        DB::table('eod_publications')->where('publication_id', $pub['publication_id'])->update(['publication_manifest_hash' => hash('sha256', 'damaged')]);
        try {
            $repository->assertPublicationManifestHashValid((int) $pub['publication_id']);
            $this->fail('R0100: a damaged manifest hash verified');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('DATASET_MANIFEST_INVALID', $e->getMessage());
        }
    }

    /**
     * `MD-S075-R0087`, `R0089`, `R0090`, `R0092`, `R0108`, `R0109`: the operator artifact is publication-shaped evidence of
     * the PERSISTED columns, and it is a different structure from the canonical semantic payload the manifest hash is computed
     * over. On a V2 publication the two carry different observation identities: `eod_publications.observation_manifest_hash`
     * (what the artifact exports under that name) and the lineage's `semantic_observation_manifest_hash` (what the hash binds).
     * The artifact exports the first under its own name and does not pass the second off under it; the hash verifier is what
     * proves the second is the one bound. Nothing is claimed here about exposing the semantic identities in the artifact.
     */
    public function test_the_artifact_exports_the_persisted_observation_identity_and_not_the_semantic_one_under_its_name(): void
    {
        [$file, $pub, , $lineage] = $this->exportRealPublication();

        $this->assertSame($pub['observation_manifest_hash'], $file['observation_manifest_hash'], 'R0087');
        $this->assertNotEmpty($lineage['semantic_observation_manifest_hash'], 'precondition: a V2 publication binds a semantic observation identity');
        $this->assertNotSame($lineage['semantic_observation_manifest_hash'], $file['observation_manifest_hash'], 'R0087/R0109: the semantic identity is exported under the persisted name');
        $this->assertTrue((new EodPublicationRepository())->assertPublicationManifestHashValid((int) $pub['publication_id']), 'the semantic identity bound by the hash does not verify');
    }

    /**
     * `MD-S075-R0083`, `R0110`, `R0106`, `R0107`: the current marking, the readiness and the freshness of a real
     * publication are the truth of the tables — the pointer names this publication, `is_current` is a boolean
     * agreeing with it, the publication is READABLE and its freshness is inside the governed vocabulary — and the
     * run-mirror names do not appear.
     */
    public function test_current_readiness_and_freshness_of_a_real_publication_are_the_stored_truth(): void
    {
        [$file, $pub, $run, , , $pointer] = $this->exportRealPublication();

        $this->assertTrue($file['is_current'], 'R0083');
        $this->assertSame((int) $pub['publication_id'], (int) $pointer['publication_id'], 'the pointer does not name the publication under test');
        $this->assertSame(1, (int) $pub['is_current']);
        $this->assertSame((int) $pointer['publication_version'], $file['publication_version']);
        $this->assertSame('READABLE', $file['readiness_state'], 'R0106');
        $this->assertContains($file['freshness_state'], ArtifactSemanticHashService::FRESHNESS_STATES, 'R0107: freshness is outside the governed vocabulary');
        $this->assertSame(ArtifactSemanticHashService::normalizeFreshnessState($run['freshness_state']), $file['freshness_state']);
        $this->assertArrayNotHasKey('is_current_publication', $file, 'R0110');
        $this->assertArrayNotHasKey('supersedes_run_id', $file, 'R0110');
        $this->assertSame('SEALED', $file['seal_state']);
        $this->assertNotNull($file['sealed_at']);
    }

    /**
     * `MD-S075-R0101..R0103`: the row counts of a real publication equal the rows its history actually holds — a count that
     * merely mirrors the run column would also pass when the two disagree, so the history is counted directly.
     */
    public function test_the_row_counts_of_a_real_publication_equal_the_rows_of_its_history(): void
    {
        [$file, $pub] = $this->exportRealPublication();
        $id = (int) $pub['publication_id'];

        $this->assertSame((int) DB::table('eod_bars_history')->where('publication_id', $id)->count(), $file['bars_rows_written'], 'R0101');
        $this->assertSame((int) DB::table('eod_indicators_history')->where('publication_id', $id)->count(), $file['indicators_rows_written'], 'R0102');
        $this->assertSame((int) DB::table('eod_eligibility_history')->where('publication_id', $id)->count(), $file['eligibility_rows_written'], 'R0103');
    }
}
