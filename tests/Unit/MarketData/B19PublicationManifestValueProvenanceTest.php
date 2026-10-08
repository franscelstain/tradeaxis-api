<?php

use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B19` — what the fields of `publication_manifest.json` CONTAIN (`MD-S075` section 2, `R0079..R0111`).
 *
 * The manifest is publication-shaped evidence assembled from several tables: the publication row, the run
 * that produced it, the config snapshot, the lineage binding and the bars history. A guard that checks the
 * 29 minimum fields exist, or that two NULL placeholders match, proves nothing about which table a value
 * was read from. This guard seeds a history in which
 *
 *  - every column of the publication, the run, the config snapshot and the lineage binding holds a
 *    DIFFERENT value, so a field read from the wrong column or the wrong table cannot coincide with the
 *    right answer;
 *  - the run carries "mirror" values under the names the manifest uses (`publication_id`,
 *    `publication_version`, `is_current_publication`, `supersedes_run_id`, `sealed_at`, the three batch
 *    hashes, `observation_manifest_hash`, `config_snapshot_id`, `factor_set_hash`, `price_product_code`),
 *    all different from the publication's own, because the contract forbids a run mirror standing in for a
 *    publication field (`R0109`, `R0110`);
 *  - neighbouring publications exist — the predecessor of the same trade date, and one of another date —
 *    each with its own run, config snapshot, lineage binding and bars history, so a lookup that picks the
 *    wrong publication, the latest, the current one or "the first" returns somebody else's value.
 *
 * The expected value of every field is a literal written here, per rule id. The producer is the real
 * `EodPublicationRepository::buildManifestByPublicationId()` and, through
 * `MarketDataEvidenceExportService`, the real `publication_manifest.json` writer.
 */
class B19PublicationManifestValueProvenanceTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const DATE = '2026-04-21';
    private const OWN_PUB = 7101;
    private const PREDECESSOR_PUB = 7100;
    private const OTHER_DATE_PUB = 7102;
    private const OWN_RUN = 4001;

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

    // ------------------------------------------------------------------ the seeded history

    private static function h(string $c): string
    {
        return str_repeat($c, 64);
    }

    /** One run row; every value is a function of $n so that no two runs agree on anything. */
    private function insertRun(int $id, int $n, array $override = []): void
    {
        DB::table('eod_runs')->insert(array_merge([
            'run_id' => $id, 'trade_date_requested' => '2026-04-'.str_pad((string) (10 + $n), 2, '0', STR_PAD_LEFT), 'trade_date_effective' => '2026-04-'.str_pad((string) (9 + $n), 2, '0', STR_PAD_LEFT),
            'lifecycle_state' => 'COMPLETED', 'quality_gate_state' => 'PASS', 'stage' => 'FINALIZE', 'source' => 'manual_file',
            'terminal_status' => 'SUCCESS', 'publishability_state' => 'NOT_READABLE_RUN_STATE', 'coverage_gate_state' => 'PASS', 'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET',
            'coverage_universe_count' => 100, 'coverage_available_count' => 100, 'coverage_missing_count' => 0, 'coverage_ratio' => 1.0, 'coverage_min_threshold' => 0.98,
            'coverage_threshold_mode' => 'MIN_RATIO', 'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE', 'coverage_contract_version' => 'coverage_gate_v1',
            'bars_rows_written' => 100 * $n + 1, 'indicators_rows_written' => 100 * $n + 2, 'eligibility_rows_written' => 100 * $n + 3,
            'freshness_state' => 'STALE', 'config_version' => 'cfg-run-'.$n,
            // the run's MIRRORS of publication-shaped names: all different from the publication's own
            'publication_id' => 9000 + $n, 'publication_version' => 70 + $n, 'is_current_publication' => 0, 'sealed_at' => '2026-04-30 0'.$n.':00:00',
            'bars_batch_hash' => self::h((string) $n), 'indicators_batch_hash' => self::h((string) ($n + 1)), 'eligibility_batch_hash' => self::h((string) ($n + 2)),
            'observation_manifest_hash' => self::h('9'), 'config_snapshot_id' => 9500 + $n, 'factor_set_hash' => self::h('8'), 'price_product_code' => 'RUN_MIRROR_PRODUCT',
            'supersedes_run_id' => 3000 + $n, 'started_at' => '2026-04-21 17:0'.$n.':00', 'created_at' => '2026-04-21 17:0'.$n.':00', 'updated_at' => '2026-04-21 17:3'.$n.':00',
        ], $override));
    }

    /**
     * @param array<string,mixed> $override
     */
    private function insertPublication(int $id, int $runId, string $date, int $version, array $override = []): void
    {
        DB::table('eod_publications')->insert(array_merge([
            'publication_id' => $id, 'trade_date' => $date, 'run_id' => $runId, 'publication_version' => $version, 'is_current' => 0, 'seal_state' => 'SEALED',
            'sealed_at' => '2026-04-21 19:00:00', 'created_at' => '2026-04-21 18:00:00', 'updated_at' => '2026-04-21 19:00:00',
            'bars_batch_hash' => self::h('1'), 'indicators_batch_hash' => self::h('2'), 'eligibility_batch_hash' => self::h('3'),
            'observation_manifest_hash' => self::h('4'), 'config_snapshot_id' => 9100, 'factor_set_id' => 6100, 'factor_set_hash' => self::h('5'),
            'price_product_code' => 'DECOY_PRODUCT', 'price_product_version' => 'decoy_v1', 'read_model_version' => 'decoy_read_pub', 'readiness_state' => 'DECOY_READINESS',
            'publication_manifest_hash' => self::h('6'), 'artifact_hash_profile' => 'market-data-semantic-hash/v2', 'publication_semantic_profile' => 'market-data-publication-semantic/v2',
        ], $override));
    }

    private function insertConfigSnapshot(int $id, string $hash): void
    {
        DB::table('md_config_snapshots')->insert([
            'config_snapshot_id' => $id, 'snapshot_uid' => hash('sha256', 'uid'.$id), 'snapshot_schema_version' => 'market_data_config_snapshot_v1', 'serialization_version' => 'canonical_json_v1',
            'resolved_config_json' => '{}', 'config_hash' => $hash, 'registry_revision' => 'registry-'.$id, 'effective_at' => '2026-04-21 00:00:00', 'recorded_at' => '2026-04-21 00:00:00',
            'build_id' => 'build', 'environment_profile' => 'testing', 'resolver_version' => 'r1', 'created_at' => '2026-04-21 00:00:00',
        ]);
    }

    private function insertLineage(int $publicationId, string $tag, array $override = []): void
    {
        DB::table('md_publication_lineage_bindings')->insert(array_merge([
            'publication_id' => $publicationId, 'config_snapshot_id' => 9100, 'factor_set_id' => 6100, 'observation_manifest_hash' => self::h('a'),
            'identity_revision_set_hash' => hash('sha256', 'identity-'.$tag), 'calendar_revision_set_hash' => hash('sha256', 'calendar-'.$tag), 'status_revision_set_hash' => hash('sha256', 'status-'.$tag),
            'event_revision_set_hash' => hash('sha256', 'event-'.$tag), 'source_scale_assessment_set_hash' => self::h('b'), 'market_structure_revision_set_hash' => self::h('c'), 'factor_decision_set_hash' => self::h('d'),
            'formula_version' => 'formula_'.$tag, 'build_id' => 'build-'.$tag, 'read_model_version' => 'read_'.$tag, 'created_at' => '2026-04-21 18:00:00',
        ], $override));
    }

    private function insertBarsHistory(int $publicationId, int $runId, string $date, string $canonicalization): void
    {
        DB::table('eod_bars_history')->insert([
            'publication_id' => $publicationId, 'trade_date' => $date, 'ticker_id' => 500 + $publicationId, 'source' => 'MANUAL_FILE', 'run_id' => $runId,
            'canonicalization_version' => $canonicalization, 'price_product_code' => 'RAW', 'quality_state' => 'VALIDATED', 'created_at' => '2026-04-21 18:00:00',
        ]);
    }

    /** The publication under test and its neighbours. */
    private function seedHistory(array $ownPublication = [], array $ownRun = []): void
    {
        $this->insertConfigSnapshot(9100, self::h('e'));       // decoy: the predecessor's snapshot
        $this->insertConfigSnapshot(9101, self::h('7'));       // own
        $this->insertConfigSnapshot(9102, self::h('f'));       // other date

        // neighbours: the predecessor of the same trade date (version 2) and a publication of another date
        $this->insertRun(4000, 2);
        $this->insertPublication(self::PREDECESSOR_PUB, 4000, self::DATE, 2, ['is_current' => 0, 'config_snapshot_id' => 9100, 'factor_set_id' => 6100]);
        $this->insertLineage(self::PREDECESSOR_PUB, 'predecessor');
        $this->insertBarsHistory(self::PREDECESSOR_PUB, 4000, self::DATE, 'canon_predecessor');
        $this->insertRun(4002, 3);
        $this->insertPublication(self::OTHER_DATE_PUB, 4002, '2026-04-22', 1, ['is_current' => 1, 'config_snapshot_id' => 9102, 'factor_set_id' => 6102]);
        $this->insertLineage(self::OTHER_DATE_PUB, 'otherdate', ['config_snapshot_id' => 9102, 'factor_set_id' => 6102]);
        $this->insertBarsHistory(self::OTHER_DATE_PUB, 4002, '2026-04-22', 'canon_otherdate');

        // the publication under test: its run's requested date is the publication's trade date
        $this->insertRun(self::OWN_RUN, 1, $ownRun + ['trade_date_requested' => self::DATE, 'trade_date_effective' => '2026-04-20', 'freshness_state' => 'FRESH']);
        $this->insertPublication(self::OWN_PUB, self::OWN_RUN, self::DATE, 3, $ownPublication + [
            'is_current' => 1, 'supersedes_publication_id' => self::PREDECESSOR_PUB, 'previous_publication_id' => 7090, 'replaced_publication_id' => 7091,
            'sealed_at' => '2026-04-21 18:05:06', 'bars_batch_hash' => self::h('b'), 'indicators_batch_hash' => self::h('c'), 'eligibility_batch_hash' => self::h('d'),
            'observation_manifest_hash' => self::h('a'), 'config_snapshot_id' => 9101, 'factor_set_id' => 6101, 'factor_set_hash' => self::h('f'),
            'price_product_code' => 'PUB_PRODUCT', 'price_product_version' => 'pub_product_v1', 'read_model_version' => 'read_pub_column', 'readiness_state' => 'READABLE',
            'publication_manifest_hash' => self::h('9'),
        ]);
        $this->insertLineage(self::OWN_PUB, 'own', ['config_snapshot_id' => 9101, 'factor_set_id' => 6101]);
        $this->insertBarsHistory(self::OWN_PUB, self::OWN_RUN, self::DATE, 'canon_own');
        // the run of the publication under test points its mirrors at the neighbour
        DB::table('eod_runs')->where('run_id', self::OWN_RUN)->update(['publication_id' => self::PREDECESSOR_PUB]);
    }

    /** @return array<string,mixed> */
    private function manifest(int $publicationId = self::OWN_PUB): array
    {
        $manifest = (new EodPublicationRepository())->buildManifestByPublicationId($publicationId);
        $this->assertNotNull($manifest, 'no manifest was built for publication '.$publicationId);

        return (array) $manifest;
    }

    private function temporalHash(string $tag, string $date = self::DATE): string
    {
        return (new DeterministicHashService())->hashCanonicalDocument([
            'trade_date' => $date,
            'identity_revision_set_hash' => hash('sha256', 'identity-'.$tag),
            'calendar_revision_set_hash' => hash('sha256', 'calendar-'.$tag),
            'status_revision_set_hash' => hash('sha256', 'status-'.$tag),
        ]);
    }

    // ------------------------------------------------------------------ the 29 minimum fields

    /** @return array<string,array{0:string,1:mixed}> field => [rule, expected] */
    public function minimumFields(): array
    {
        $t = [
            'publication_id' => ['R0079', self::OWN_PUB], 'trade_date' => ['R0080', self::DATE], 'run_id' => ['R0081', self::OWN_RUN], 'publication_version' => ['R0082', 3],
            'is_current' => ['R0083', true], 'supersedes_publication_id' => ['R0084', self::PREDECESSOR_PUB], 'seal_state' => ['R0085', 'SEALED'], 'sealed_at' => ['R0086', '2026-04-21 18:05:06'],
            'observation_manifest_hash' => ['R0087', self::h('a')], 'config_snapshot_id' => ['R0088', 9101], 'config_snapshot_hash' => ['R0089', self::h('7')],
            'factor_set_id' => ['R0091', 6101], 'factor_set_hash' => ['R0092', self::h('f')], 'price_product_code' => ['R0093', 'PUB_PRODUCT'],
            'canonicalization_version' => ['R0094', 'canon_own'], 'formula_version' => ['R0095', 'formula_own'], 'read_model_version' => ['R0096', 'read_own'],
            'bars_batch_hash' => ['R0097', self::h('b')], 'indicators_batch_hash' => ['R0098', self::h('c')], 'eligibility_batch_hash' => ['R0099', self::h('d')],
            'publication_manifest_hash' => ['R0100', self::h('9')], 'bars_rows_written' => ['R0101', 101], 'indicators_rows_written' => ['R0102', 102], 'eligibility_rows_written' => ['R0103', 103],
            'trade_date_requested' => ['R0104', self::DATE], 'trade_date_effective' => ['R0105', '2026-04-20'], 'readiness_state' => ['R0106', 'READABLE'], 'freshness_state' => ['R0107', 'FRESH'],
        ];
        $cases = [];
        foreach ($t as $field => [$rule, $expected]) {
            $cases[$field.' ('.$rule.')'] = [$field, $rule, $expected];
        }

        return $cases;
    }

    /**
     * `MD-S075-R0079..R0107` (and `R0108`, `R0109`): each minimum field carries the value of its own persisted
     * source for THE publication under test — not the run mirror of the same name, not the predecessor, not the
     * publication of another date.
     *
     * @dataProvider minimumFields
     */
    public function test_a_minimum_field_carries_the_value_of_its_own_persisted_source($field, $rule, $expected): void
    {
        $this->seedHistory();
        $manifest = $this->manifest();

        $this->assertArrayHasKey($field, $manifest, $rule.': '.$field.' is absent from the manifest');
        $this->assertSame($expected, $manifest[$field], $rule.': '.$field.' does not carry the value of its own persisted source');
    }

    /** `MD-S075-R0090`: the temporal revision set hash is composed from THIS publication's three lineage hashes and its trade date. */
    public function test_the_temporal_revision_set_hash_is_composed_from_the_publications_own_lineage(): void
    {
        $this->seedHistory();
        $manifest = $this->manifest();

        $this->assertSame($this->temporalHash('own'), $manifest['temporal_revision_set_hash'], 'R0090');
        $this->assertNotSame($this->temporalHash('predecessor'), $manifest['temporal_revision_set_hash'], 'R0090: the predecessor\'s lineage was used');
        $this->assertNotSame($this->temporalHash('otherdate', '2026-04-22'), $manifest['temporal_revision_set_hash'], 'R0090: the other date\'s lineage was used');
        $this->assertSame(hash('sha256', 'identity-own'), $manifest['identity_revision_set_hash']);
        $this->assertSame(hash('sha256', 'calendar-own'), $manifest['calendar_revision_set_hash']);
        $this->assertSame(hash('sha256', 'status-own'), $manifest['status_revision_set_hash']);
    }

    /**
     * `MD-S075-R0090`, `R0094`, `R0089`, `R0084`, `R0086`, `R0101..R0103`: a value the publication cannot supply is NULL — never borrowed from a
     * neighbour, never defaulted. A lineage hash that is not a SHA-256 value leaves the temporal hash NULL rather than
     * hashing a partial document.
     */
    public function test_a_value_the_publication_cannot_supply_is_null_not_borrowed(): void
    {
        $this->seedHistory(['config_snapshot_id' => null, 'factor_set_id' => null, 'supersedes_publication_id' => null, 'sealed_at' => null, 'seal_state' => 'UNSEALED'],
            ['bars_rows_written' => null, 'indicators_rows_written' => null, 'eligibility_rows_written' => null]);
        DB::table('md_publication_lineage_bindings')->where('publication_id', self::OWN_PUB)->update(['status_revision_set_hash' => 'not-a-sha256']);
        DB::table('eod_bars_history')->where('publication_id', self::OWN_PUB)->delete();
        $manifest = $this->manifest();

        foreach (['config_snapshot_id', 'config_snapshot_hash', 'factor_set_id', 'supersedes_publication_id', 'sealed_at', 'temporal_revision_set_hash', 'canonicalization_version',
            'bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written'] as $field) {
            $this->assertNull($manifest[$field], 'a value the publication cannot supply was exported as '.json_encode($manifest[$field]).' in '.$field);
        }
        $this->assertSame('UNSEALED', $manifest['seal_state']);
    }

    // ------------------------------------------------------------------ R0083 / R0084 / R0110 / R0111

    /** `MD-S075-R0110`: the publication-shaped names are used and the run-mirror names are not. */
    public function test_current_and_supersession_use_the_publication_names_and_not_the_run_mirror_names(): void
    {
        $this->seedHistory();
        $manifest = $this->manifest();

        $this->assertArrayHasKey('is_current', $manifest);
        $this->assertArrayHasKey('supersedes_publication_id', $manifest);
        $this->assertArrayNotHasKey('is_current_publication', $manifest, 'R0110: a run-mirror name replaces is_current');
        $this->assertArrayNotHasKey('supersedes_run_id', $manifest, 'R0110: a run-mirror name replaces supersedes_publication_id');
        // the run's mirrors say the opposite: not current, supersedes a RUN id — the manifest must follow the publication
        $this->assertSame(0, (int) DB::table('eod_runs')->where('run_id', self::OWN_RUN)->value('is_current_publication'));
        $this->assertTrue($manifest['is_current'], 'R0083: is_current was read from the run mirror');
        $this->assertSame(self::PREDECESSOR_PUB, $manifest['supersedes_publication_id'], 'R0084');
        $this->assertSame([7090, 7091], [$manifest['previous_publication_id'], $manifest['replaced_publication_id']], 'R0084: the three lineage links are three different columns');
        $this->assertNotSame((int) DB::table('eod_runs')->where('run_id', self::OWN_RUN)->value('supersedes_run_id'), $manifest['supersedes_publication_id'], 'R0110: a run id was exported as a publication id');
    }

    /**
     * `MD-S075-R0111`, `R0083`: a superseded publication's manifest stays what it was. Demoting a publication changes
     * `is_current` and NOTHING ELSE in its manifest; its manifest hash is untouched; it is not made to look current
     * even while its run still mirrors the current marking.
     */
    public function test_a_superseded_publication_manifest_is_not_rewritten_to_look_current(): void
    {
        $this->seedHistory();
        DB::table('eod_runs')->where('run_id', self::OWN_RUN)->update(['is_current_publication' => 1]);   // a stale mirror that still says "current"
        $whileCurrent = $this->manifest();

        DB::table('eod_publications')->where('publication_id', self::OWN_PUB)->update(['is_current' => 0]);
        $afterSupersession = $this->manifest();

        $changed = array_keys(array_filter($whileCurrent, function ($v, $k) use ($afterSupersession) { return $afterSupersession[$k] !== $v; }, ARRAY_FILTER_USE_BOTH));
        $this->assertSame(['is_current'], $changed, 'R0111: superseding a publication rewrote more than its current marking: '.implode(',', $changed));
        $this->assertFalse($afterSupersession['is_current'], 'R0111/R0083: a superseded publication is reported as current because its run mirror says so');
        $this->assertSame($whileCurrent['publication_manifest_hash'], $afterSupersession['publication_manifest_hash']);
        $this->assertSame('SEALED', $afterSupersession['seal_state']);
        $this->assertSame('2026-04-21 18:05:06', $afterSupersession['sealed_at']);
    }

    /** The predecessor's own manifest is its own: not current, not carrying the successor's version, hashes or dates. */
    public function test_the_predecessors_manifest_is_its_own_and_is_audit_valid(): void
    {
        $this->seedHistory();
        $predecessor = $this->manifest(self::PREDECESSOR_PUB);

        $this->assertSame(self::PREDECESSOR_PUB, $predecessor['publication_id']);
        $this->assertFalse($predecessor['is_current']);
        $this->assertSame(2, $predecessor['publication_version']);
        $this->assertSame('SEALED', $predecessor['seal_state']);
        $this->assertSame('canon_predecessor', $predecessor['canonicalization_version']);
        $this->assertSame(self::h('e'), $predecessor['config_snapshot_hash']);
        $this->assertSame($this->temporalHash('predecessor'), $predecessor['temporal_revision_set_hash']);
        $this->assertNull($predecessor['supersedes_publication_id']);
    }

    // ------------------------------------------------------------------ R0106 / R0107

    /** @return array<string,array{0:string,1:string}> */
    public function freshnessLabels(): array
    {
        return [
            'FRESH' => ['FRESH', 'FRESH'], 'STALE' => ['STALE', 'STALE'], 'DEGRADED' => ['DEGRADED', 'DEGRADED'], 'NOT_AVAILABLE' => ['NOT_AVAILABLE', 'NOT_AVAILABLE'],
            'NOT_APPLICABLE' => ['NOT_APPLICABLE', 'NOT_APPLICABLE'], 'lower case' => ['fresh', 'FRESH'],
            'an unknown label is not FRESH' => ['DEVELOPMENT_NOT_OPERATIONAL', 'NOT_AVAILABLE'], 'an empty label is not FRESH' => ['', 'NOT_AVAILABLE'], 'a NULL label is not FRESH' => [null, 'NOT_AVAILABLE'],
        ];
    }

    /**
     * `MD-S075-R0107`: freshness is the run's recorded label inside the governed vocabulary; anything outside it is
     * NOT_AVAILABLE and can never be exported as FRESH.
     *
     * @dataProvider freshnessLabels
     */
    public function test_freshness_is_the_recorded_label_inside_the_vocabulary_and_never_an_optimistic_default($label, string $expected): void
    {
        $this->seedHistory([], ['freshness_state' => $label]);

        $this->assertSame($expected, $this->manifest()['freshness_state']);
        $this->assertSame('STALE', $this->manifest(self::PREDECESSOR_PUB)['freshness_state'], 'R0107: the predecessor\'s own label was lost');
    }

    /** `MD-S075-R0106`: readiness is the PUBLICATION's state, not the run's publishability and not a default. */
    public function test_readiness_is_the_publications_state_and_not_the_runs_publishability(): void
    {
        $this->seedHistory(['readiness_state' => 'HELD']);
        $manifest = $this->manifest();

        $this->assertSame('HELD', $manifest['readiness_state'], 'R0106: readiness was derived instead of read from the publication');
        $this->assertNotSame('NOT_READABLE_RUN_STATE', $manifest['readiness_state']);
        $this->assertNotSame('READABLE', $manifest['readiness_state']);
    }

    // ------------------------------------------------------------------ R0104 / R0105

    /** `MD-S075-R0104`, `R0105`: the requested date and the effective date are the run's two different dates. */
    public function test_requested_and_effective_dates_are_the_runs_two_dates(): void
    {
        $this->seedHistory([], ['trade_date_requested' => self::DATE, 'trade_date_effective' => '2026-04-17']);
        $manifest = $this->manifest();

        $this->assertSame(self::DATE, $manifest['trade_date_requested']);
        $this->assertSame('2026-04-17', $manifest['trade_date_effective']);
        $this->assertSame(self::DATE, $manifest['trade_date'], 'R0080: the publication\'s trade_date moved with the effective date');
    }

    /**
     * `MD-S075-R0080`, `R0104`: the publication's `trade_date` is the PUBLICATION's, and `trade_date_requested` is the RUN's.
     * The production invariant (checked by the evidence resolver) is that the two are equal, which makes a swap invisible
     * on any valid history; this history deliberately separates them so that a manifest that takes one for the other is caught.
     */
    public function test_the_publication_date_and_the_runs_requested_date_are_kept_apart(): void
    {
        $this->seedHistory([], ['trade_date_requested' => '2026-04-19', 'trade_date_effective' => '2026-04-18']);
        $manifest = $this->manifest();

        $this->assertSame(self::DATE, $manifest['trade_date'], 'R0080: trade_date follows the run');
        $this->assertSame('2026-04-19', $manifest['trade_date_requested'], 'R0104: trade_date_requested follows the publication');
        $this->assertSame('2026-04-18', $manifest['trade_date_effective']);
        $this->assertSame($this->temporalHash('own'), $manifest['temporal_revision_set_hash'], 'the temporal hash binds the publication\'s date, not the run\'s');
    }

    // ------------------------------------------------------------------ the written file

    /** `MD-S075-R0108`: the file the exporter writes is the manifest, field for field — nothing renamed, dropped or added by the writer. */
    public function test_the_exported_file_is_the_manifest_the_repository_builds(): void
    {
        $this->seedHistory([], ['publishability_state' => 'READABLE', 'terminal_status' => 'SUCCESS', 'sealed_at' => '2026-04-21 18:05:06']);
        DB::table('eod_runs')->where('run_id', self::OWN_RUN)->update(['publication_id' => self::OWN_PUB, 'publication_version' => 3, 'is_current_publication' => 1]);
        DB::table('eod_current_publication_pointer')->insert(['trade_date' => self::DATE, 'publication_id' => self::OWN_PUB, 'run_id' => self::OWN_RUN, 'publication_version' => 3, 'sealed_at' => '2026-04-21 18:05:06', 'updated_at' => '2026-04-21 18:05:06']);

        $dir = sys_get_temp_dir().'/md_b19_pm_value_'.uniqid('', true);
        app(MarketDataEvidenceExportService::class)->exportRunEvidence(self::OWN_RUN, $dir);
        $file = json_decode((string) file_get_contents($dir.'/publication_manifest.json'), true);

        $expected = json_decode(json_encode($this->manifest()), true);
        $this->assertSame($expected, $file, 'R0108: the written publication_manifest.json differs from the manifest the repository builds');
        $this->assertSame(self::OWN_PUB, $file['publication_id']);
    }
}
