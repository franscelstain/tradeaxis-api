<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodEvidenceRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\ReplayResultRepository;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Tests\Support\UsesMarketDataSqlite;

// Test classes are not autoloaded, so a single-file run needs the borrowed fixture class loaded.
require_once __DIR__.'/B18ReplayComparisonExhaustivenessTest.php';

/**
 * `MD-B18-A002` -- `MD-S003-R0023` (per-run evidence) and `MD-S004-R0004` (required input
 * identity), proven on the path that actually writes and exports them.
 *
 * `B18ReplayEvidenceSelfExplanationTest` exports a hand-built metric row through a mocked
 * repository. That establishes what the exporter does with a row somebody typed; it cannot fail if
 * the real writer stops recording an identity, records a constant, or reads today's environment,
 * because the row never came from the writer. Here the writer is real end to end:
 *
 *   `ReplayVerificationService` -> `ReplayResultRepository::upsertMetric()` -> `md_replay_daily_metrics`
 *   -> `EodEvidenceRepository::findReplayMetric()` -> `MarketDataEvidenceExportService`
 *
 * Only what the writer *consumes* is stubbed (the run, the publication, and the already-verified
 * bound-input context Reader projects) -- those are the canonical sources each identity is asserted
 * against. Two questions are asked of every frozen identity:
 *
 *  - is it recorded, and does the export reproduce exactly what was persisted? (`R0023`, `R0004`)
 *  - does it follow its own canonical source and nothing else? Perturbing exactly one source must
 *    move exactly the identity that source feeds. A constant, a current-configuration read, or one
 *    identity standing in for another fails here, which a check for presence cannot do.
 *
 * The fixture, run and publication rows are borrowed from `B18ReplayComparisonExhaustivenessTest`
 * rather than duplicated, so this class cannot drift from the fixture that guard already maintains.
 */
class B18ReplayPersistedEvidenceBindingTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const RUN_ID = 91;

    private const TRADE_DATE = '2026-03-20';

    private const PUBLICATION_ID = 44;

    /** The twelve frozen identities `MD-S003-R0023` calls "all frozen revision/snapshot IDs". */
    private const FROZEN_IDENTITIES = [
        'source_observation_manifest_hash', 'canonical_raw_input_hash', 'temporal_identity_hash',
        'calendar_status_hash', 'event_factor_hash', 'config_snapshot_id', 'config_snapshot_hash',
        'formula_registry_hash', 'reason_registry_hash', 'read_model_version',
        'serialization_version', 'executable_build_identity',
    ];

    /** @var B18ReplayComparisonExhaustivenessTest */
    private $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->fixtures = new B18ReplayComparisonExhaustivenessTest();
    }

    protected function tearDown(): void
    {
        m::close();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    // ---- MD-S003-R0023 ---------------------------------------------------------------------------

    /**
     * Every frozen identity is recorded by the real writer and reproduced exactly by the export, and
     * the values that have a single unambiguous canonical source equal it.
     */
    public function test_the_real_writer_records_every_frozen_identity_and_the_export_reproduces_it(): void
    {
        $run = $this->persist();

        foreach (self::FROZEN_IDENTITIES as $identity) {
            $persisted = (string) $run['row']->{$identity};
            $this->assertNotSame('', $persisted, $identity.' was not recorded by the writer');
            $this->assertSame(
                $persisted,
                (string) $run['bound'][$identity],
                $identity.' is exported differently from what was persisted'
            );
        }

        // The identities whose canonical source is a single value the fixture states outright.
        $this->assertSame(str_repeat('1', 64), (string) $run['bound']['source_observation_manifest_hash']);
        $this->assertSame('A1', (string) $run['bound']['canonical_raw_input_hash']);
        $this->assertSame('7001', (string) $run['bound']['config_snapshot_id']);
        $this->assertSame(str_repeat('7', 64), (string) $run['bound']['config_snapshot_hash']);
        $this->assertSame('market_data_read_product_v1', (string) $run['bound']['read_model_version']);
        $this->assertSame('canonical_json_v1_probe', (string) $run['bound']['serialization_version']);
        $this->assertSame('sha256:probe_build_identity', (string) $run['bound']['executable_build_identity']);
    }

    /**
     * The rest of the `MD-S003` per-run evidence line, read back from the same real export: mode,
     * manifest hash, requested/effective dates, artifact hashes, the result, and the named mismatch
     * path of a replay that actually diverged.
     */
    public function test_the_rest_of_the_per_run_evidence_line_is_recorded_by_the_real_writer(): void
    {
        $matched = $this->persist();
        $result = $matched['result_json'];

        $this->assertSame('PUBLICATION_EXACT', $result['replay_mode']);
        $this->assertNull($result['knowledge_cutoff_at'], 'a publication replay is pinned to a publication, not a moment');
        $this->assertNotSame('', (string) $result['fixture_manifest_hash']);
        $this->assertSame((string) $matched['row']->fixture_manifest_hash, (string) $result['fixture_manifest_hash']);
        $this->assertSame(self::TRADE_DATE, (string) $result['trade_date']);
        $this->assertSame(self::TRADE_DATE, (string) $result['trade_date_effective']);
        $this->assertSame('A1', (string) $result['bars_batch_hash']);
        $this->assertSame('PASS', (string) $result['replay_status']);
        $this->assertSame([], $result['mismatches'], 'a matching replay records no mismatch');

        // A replay that diverges names the path that diverged, from the same writer.
        $diverged = $this->persist(['bars_batch_hash' => 'A9']);
        $fields = array_column($diverged['result_json']['mismatches'], 'field');
        $this->assertSame('FAIL', (string) $diverged['result_json']['replay_status']);
        $this->assertContains('bars_batch_hash', $fields, 'the diverging path is not named in the recorded evidence');
    }

    /**
     * One canonical source perturbed at a time. The identity it feeds must become exactly the new
     * value (or at least move), and no other identity may move -- so a constant, a live read, or one
     * identity substituting for another is caught, not just a missing key.
     *
     * @dataProvider canonicalSourcePerturbations
     *
     * @param array<string,mixed> $run
     * @param array<string,mixed> $manifest
     * @param array<int,string>   $expectedMoved
     * @param array<string,string> $exactValues identity => the exact value its source now carries
     */
    public function test_each_frozen_identity_follows_only_its_own_canonical_source(
        array $run,
        array $manifest,
        ?string $contextMutator,
        array $expectedMoved,
        array $exactValues
    ): void {
        $baseline = $this->persist();
        $perturbed = $this->persist($run, $manifest, $contextMutator);

        $moved = [];
        foreach (self::FROZEN_IDENTITIES as $identity) {
            if ((string) $baseline['bound'][$identity] !== (string) $perturbed['bound'][$identity]) {
                $moved[] = $identity;
            }
        }
        sort($moved);
        $expected = $expectedMoved;
        sort($expected);

        $this->assertSame($expected, $moved,
            'perturbing one canonical source must move exactly the identity it feeds');

        foreach ($exactValues as $identity => $value) {
            $this->assertSame($value, (string) $perturbed['bound'][$identity],
                $identity.' does not carry the value its canonical source now holds');
        }
    }

    /** @return array<string,array{0:array,1:array,2:?string,3:array<int,string>,4:array<string,string>}> */
    public function canonicalSourcePerturbations(): array
    {
        return [
            'observation manifest' => [
                ['observation_manifest_hash' => str_repeat('9', 64)], [], null,
                ['source_observation_manifest_hash'],
                ['source_observation_manifest_hash' => str_repeat('9', 64)],
            ],
            'canonical raw input set' => [
                ['bars_batch_hash' => 'A9'], [], null,
                ['canonical_raw_input_hash'],
                ['canonical_raw_input_hash' => 'A9'],
            ],
            'temporal universe' => [
                [], [], 'universe',
                ['temporal_identity_hash'],
                [],
            ],
            'calendar and status revisions' => [
                [], ['calendar_revision_set_hash' => str_repeat('8', 64)], null,
                ['calendar_status_hash'],
                [],
            ],
            'factor set' => [
                [], ['factor_set_hash' => str_repeat('6', 64)], null,
                ['event_factor_hash'],
                [],
            ],
            'configuration snapshot hash' => [
                ['config_hash' => str_repeat('8', 64)], [], null,
                ['config_snapshot_hash'],
                ['config_snapshot_hash' => str_repeat('8', 64)],
            ],
            'configuration snapshot id' => [
                ['config_snapshot_id' => 7002], [], null,
                ['config_snapshot_id'],
                ['config_snapshot_id' => '7002'],
            ],
            'registry versions payload' => [
                [], [], 'registry',
                ['formula_registry_hash', 'reason_registry_hash'],
                [],
            ],
            'read-model version' => [
                [], [], 'read_model',
                ['read_model_version'],
                ['read_model_version' => 'market_data_read_product_v2_probe'],
            ],
            'serialization version' => [
                [], [], 'serialization',
                ['serialization_version'],
                ['serialization_version' => 'canonical_json_v2_probe'],
            ],
            'executable build' => [
                [], [], 'build',
                ['executable_build_identity'],
                ['executable_build_identity' => 'sha256:other_build'],
            ],
        ];
    }

    /**
     * "Later environment drift cannot change exact evidence." A second real replay of the same
     * frozen inputs, run after the live configuration and build identity have changed, records the
     * same identities -- nothing is read from today's environment.
     */
    public function test_later_environment_drift_cannot_change_the_recorded_identities(): void
    {
        $before = $this->persist();

        config([
            'market_data.governance.build_id' => 'LIVE-DRIFTED-BUILD',
            'market_data.governance.config_serialization_version' => 'LIVE-DRIFTED-SERIALIZATION',
            'market_data.indicators' => ['drifted' => true],
            'market_data.eligibility.contract_version' => 'LIVE-DRIFTED-ELIGIBILITY',
        ]);

        $after = $this->persist();

        foreach (self::FROZEN_IDENTITIES as $identity) {
            $this->assertSame((string) $before['bound'][$identity], (string) $after['bound'][$identity],
                $identity.' changed when only the live environment changed');
        }
    }

    // ---- MD-S004-R0004 ---------------------------------------------------------------------------

    /**
     * Factor identity and formula identity are two different bindings. Each is perturbed through its
     * own source, and the other must not move: a valid formula hash cannot hide a missing factor
     * identity, and the reverse. (`canonicalSourcePerturbations()` asserts the same exclusivity for
     * every identity; this names the pair the predicate's "factor/formula versions" clause needs.)
     */
    public function test_factor_identity_and_formula_identity_are_bound_independently(): void
    {
        $baseline = $this->persist()['bound'];
        $factorMoved = $this->persist([], ['factor_set_hash' => str_repeat('6', 64)])['bound'];
        $formulaMoved = $this->persist([], [], 'registry')['bound'];

        $this->assertNotSame((string) $baseline['event_factor_hash'], (string) $factorMoved['event_factor_hash']);
        $this->assertSame((string) $baseline['formula_registry_hash'], (string) $factorMoved['formula_registry_hash'],
            'a factor-set change moved the formula identity, so one stands in for the other');

        $this->assertNotSame((string) $baseline['formula_registry_hash'], (string) $formulaMoved['formula_registry_hash']);
        $this->assertSame((string) $baseline['event_factor_hash'], (string) $formulaMoved['event_factor_hash'],
            'a formula/registry change moved the factor identity, so one stands in for the other');
    }

    /**
     * The verifier's own definition of lineage (`actual_lineage`, the block `REPLAY_LINEAGE_MISMATCH`
     * is raised over) is persisted and exported with real content -- including the factor-set
     * identity and each artifact hash -- rather than as a present-but-empty structure.
     */
    public function test_lineage_is_recorded_with_content_and_follows_its_sources(): void
    {
        $lineage = $this->persist()['result_json']['actual_context']['actual_lineage'];

        $this->assertSame(self::PUBLICATION_ID, (int) $lineage['publication_id']);
        $this->assertSame(self::RUN_ID, (int) $lineage['run_id']);
        $this->assertSame('A1', $lineage['bars_batch_hash']);
        $this->assertSame('B1', $lineage['indicators_batch_hash']);
        $this->assertSame('C1', $lineage['eligibility_batch_hash']);
        $this->assertSame(str_repeat('4', 64), $lineage['factor_set_hash'],
            'the factor-set identity is not carried in the recorded lineage');

        $moved = $this->persist(['factor_set_hash' => str_repeat('5', 64), 'indicators_batch_hash' => 'B9'])['result_json']['actual_context']['actual_lineage'];
        $this->assertSame(str_repeat('5', 64), $moved['factor_set_hash']);
        $this->assertSame('B9', $moved['indicators_batch_hash']);
    }

    /**
     * Every export binds the requested and the effective trade date, and the availability timestamp
     * is a separate field from the market trade date (`R0004`, final clause), taken from what the
     * writer actually stored rather than a value a test typed.
     */
    public function test_dates_and_the_availability_timestamp_are_bound_by_the_real_writer(): void
    {
        $run = $this->persist();
        $result = $run['result_json'];

        $this->assertSame(self::TRADE_DATE, (string) $result['trade_date'], 'requested trade date is not bound');
        $this->assertSame(self::TRADE_DATE, (string) $result['trade_date_effective'], 'effective trade date is not bound');
        $this->assertNotSame('', (string) $result['replay_id']);
        $this->assertSame(self::PUBLICATION_ID, (int) $result['publication_context']['publication_id'],
            'the publication-like artifact id is not bound');

        $this->assertNotSame('', (string) $result['created_at'], 'no availability timestamp was stored');
        $this->assertNotSame(
            substr((string) $result['created_at'], 0, 10),
            (string) $result['trade_date'],
            'the availability timestamp collapsed onto the market trade date'
        );
    }

    // ---- harness ---------------------------------------------------------------------------------

    /**
     * Runs the real verifier and the real writer once, then reads the persisted row back and exports
     * it through the real evidence repository.
     *
     * @param array<string,mixed> $run      overrides on the run row the verifier reads
     * @param array<string,mixed> $manifest overrides on the publication build manifest
     * @return array{row:object,bound:array<string,mixed>,result_json:array<string,mixed>}
     */
    private function persist(array $run = [], array $manifest = [], ?string $contextMutator = null): array
    {
        $evidence = $this->borrow('mocks', true, $run, [])[0];
        $publications = m::mock(EodPublicationRepository::class);
        $publications->shouldReceive('buildManifestByPublicationId')
            ->andReturn((object) $this->manifest($manifest, $contextMutator));

        $fixtureDir = $this->borrow('fixtureDir', [], true);

        $result = (new ReplayVerificationService($evidence, $publications, new ReplayResultRepository()))
            ->verifyRunAgainstFixture(self::RUN_ID, $fixtureDir);

        $row = DB::table('md_replay_daily_metrics')->where('replay_id', $result['replay_id'])->first();
        $this->assertNotNull($row, 'the real writer persisted nothing');

        $outDir = sys_get_temp_dir().'/md_b18_persisted_evidence_'.uniqid();
        (new MarketDataEvidenceExportService(new EodEvidenceRepository(), new EodPublicationRepository(), new EodCorrectionRepository()))
            ->exportReplayEvidence($result['replay_id'], self::TRADE_DATE, $outDir);

        $json = json_decode((string) file_get_contents($outDir.'/replay_result.json'), true);
        $this->assertIsArray($json, 'the export wrote no replay_result.json');

        return ['row' => $row, 'bound' => $json['bound_inputs'], 'result_json' => $json];
    }

    /** The VERIFIED bound context and build manifest Reader would project for the publication. */
    private function manifest(array $override, ?string $contextMutator): array
    {
        $components = [
            ['stage_code' => 'COMPUTE_ELIGIBILITY', 'component_key' => 'universe_identity', 'slot_hash' => str_repeat('1', 64), 'payload_hash' => str_repeat('u', 64)],
            ['stage_code' => 'COMPUTE_INDICATORS', 'component_key' => 'ancillary', 'slot_hash' => str_repeat('2', 64), 'payload_hash' => str_repeat('n', 64)],
            ['stage_code' => 'RUN_CONTEXT', 'component_key' => 'registry_versions', 'slot_hash' => str_repeat('3', 64), 'payload_hash' => str_repeat('r', 64)],
        ];
        $registry = [
            'read_model_version' => 'market_data_read_product_v1',
            'serialization_version' => 'canonical_json_v1_probe',
            'executable_build' => ['build_id' => 'sha256:probe_build_identity'],
        ];

        if ($contextMutator === 'universe') {
            $components[0]['payload_hash'] = str_repeat('x', 64);
        } elseif ($contextMutator === 'registry') {
            $components[2]['payload_hash'] = str_repeat('q', 64);
        } elseif ($contextMutator === 'read_model') {
            $registry['read_model_version'] = 'market_data_read_product_v2_probe';
        } elseif ($contextMutator === 'serialization') {
            $registry['serialization_version'] = 'canonical_json_v2_probe';
        } elseif ($contextMutator === 'build') {
            $registry['executable_build']['build_id'] = 'sha256:other_build';
        }

        return array_merge([
            'bound_input_context' => [
                'available' => true, 'status' => 'VERIFIED', 'schema_version' => 'md_publication_inputs_v2',
                'reason' => null, 'bound_input_context_hash' => str_repeat('f', 64),
                'components' => $components,
                'scope' => [], 'component_manifest' => ['status' => 'COMPLETE'],
                'registry_content' => $registry,
            ],
            'identity_revision_set_hash' => str_repeat('a', 64),
            'calendar_revision_set_hash' => str_repeat('b', 64),
            'status_revision_set_hash' => str_repeat('c', 64),
            'event_revision_set_hash' => str_repeat('d', 64),
            'source_scale_assessment_set_hash' => str_repeat('e', 64),
            'factor_decision_set_hash' => str_repeat('f', 64),
            'factor_set_hash' => str_repeat('4', 64),
        ], $override);
    }

    /** Calls a private fixture helper of the exhaustiveness guard, so the fixture is defined once. */
    private function borrow(string $method, ...$arguments)
    {
        $reflection = new ReflectionMethod($this->fixtures, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($this->fixtures, ...$arguments);
    }
}
