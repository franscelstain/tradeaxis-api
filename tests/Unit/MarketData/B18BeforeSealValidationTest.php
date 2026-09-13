<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- `MD-S082-R0224` and `MD-S082-R0225`.
 *
 * `Platform_Config_Registry_LOCKED.md`, "Validation and acceptance proof", items 5 and 6:
 *
 * > Before seal, validation proves:
 * > 5. current environment drift cannot change publication replay;
 * > 6. as-known replay cannot see later revisions;
 *
 * Both were `UNSUPPORTED` with the same recorded reason -- no seal-time validation is executed --
 * and the distinction they turn on is *when*. Each names a property of a publication's **replay**,
 * and each has to be decided before the seal, because afterwards the artifact is immutable.
 *
 * The two are not in the same state, and the first attempt at this class assumed they were.
 *
 * **Item 5 is already enforced.** `PublicationGovernanceBindingService` refuses with
 * `CONFIG_SNAPSHOT_NOT_FOUND` when the run's configuration snapshot does not exist; it is the
 * only writer of the publication lineage binding; and
 * `assertPublicationIntegrityContextComplete()` makes that binding mandatory at seal and
 * requires the publication and run configuration identities to agree. A publication therefore
 * cannot reach a seal without a frozen configuration, and this class proves that chain rather
 * than adding a fourth check to it. A null check was written first, and removed once it turned
 * out its failure mode could only be produced by hand-writing a lineage row production cannot
 * emit -- a guard whose red state is unreachable proves nothing.
 *
 * **Item 6 was not enforced at all.** Nothing anywhere compared the frozen configuration's
 * `recorded_at` against the run's `knowledge_cutoff_at`, so a publication could freeze a
 * configuration recorded after its own knowledge boundary -- an ordinary outcome for a run
 * whose cutoff is 18:00 and which resolves configuration at 18:05. As-known replay at that
 * cutoff can never see it, so the publication is unreproducible the moment it is sealed.
 * `assertReplayDeterminismBeforeSeal()` was added for that one comparison and runs inside
 * `sealCandidatePublication()`.
 */
class B18BeforeSealValidationTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';

    private const RUN_ID = 71;

    private const CUTOFF = '2026-03-20 18:00:00';

    private const CONFIG_SNAPSHOT_ID = 1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedRun();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /**
     * The control. A candidate carrying everything the contract requires seals, so every refusal
     * below is caused by the one thing that test removes rather than by an incomplete fixture.
     */
    public function test_a_completely_bound_candidate_seals(): void
    {
        $publicationId = $this->prepareCandidate();

        $sealed = (new EodPublicationRepository())
            ->sealCandidatePublication(EodRun::query()->findOrFail(self::RUN_ID), 'operator');

        $this->assertSame('SEALED', (string) $sealed->seal_state);
        $this->assertSame($publicationId, (int) $sealed->publication_id);
        $this->assertNotNull($sealed->sealed_at);
    }

    /**
     * `MD-S082-R0224` -- current environment drift cannot change publication replay.
     *
     * The enforcement is a chain, and this is its load-bearing link: the publication lineage
     * binding is mandatory at seal. Without it the seal is refused, and the only service that
     * writes it refuses to run for a configuration that does not exist -- so a sealed publication
     * always carries a frozen configuration, and a replay of it never has to ask the live
     * environment what the configuration was.
     */
    public function test_a_candidate_without_its_lineage_binding_cannot_seal(): void
    {
        $publicationId = $this->prepareCandidate();
        DB::table('md_publication_lineage_bindings')->where('publication_id', $publicationId)->delete();

        try {
            (new EodPublicationRepository())
                ->sealCandidatePublication(EodRun::query()->findOrFail(self::RUN_ID), 'operator');
            $this->fail('a publication with no lineage binding was sealed');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('DATASET_MANIFEST_INVALID', $e->getMessage());
            $this->assertStringContainsString('publication_lineage_binding', $e->getMessage());
        }

        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')
            ->where('publication_id', $publicationId)->value('seal_state'));
    }

    /**
     * `MD-S082-R0224` -- the other end of the same chain. The service that writes the lineage
     * binding refuses a run whose configuration snapshot does not exist, so the binding can never
     * come into being for an unbound configuration in the first place.
     */
    public function test_the_lineage_binder_refuses_a_run_whose_configuration_does_not_exist(): void
    {
        $this->prepareCandidate();
        DB::table('eod_runs')->where('run_id', self::RUN_ID)->update(['config_snapshot_id' => 9999]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/CONFIG_SNAPSHOT_NOT_FOUND/');
        $publication = DB::table('eod_publications')->where('trade_date', self::TRADE_DATE)->first();
        app(\App\Application\MarketData\Services\PublicationGovernanceBindingService::class)
            ->bind(EodRun::query()->findOrFail(self::RUN_ID), $publication, self::TRADE_DATE);
    }

    /**
     * The neighbouring case, kept so the two are not confused: when the run *does* carry a
     * configuration and the publication disagrees, the pre-existing integrity check catches
     * it under its own reason. The new validation exists for the case that check cannot
     * reach, not to replace it.
     */
    public function test_a_publication_disagreeing_with_its_run_is_still_caught_by_the_older_check(): void
    {
        $publicationId = $this->prepareCandidate();
        DB::table('eod_publications')->where('publication_id', $publicationId)
            ->update(['config_snapshot_id' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/DATASET_MANIFEST_INVALID.*config_snapshot_id/s');
        (new EodPublicationRepository())
            ->sealCandidatePublication(EodRun::query()->findOrFail(self::RUN_ID), 'operator');
    }

    /**
     * Why it has to be *before* the seal: afterwards the publication is immutable, so the binding
     * that replay needs can never be added. This is the whole content of "before seal".
     */
    public function test_after_the_seal_the_missing_binding_can_no_longer_be_added(): void
    {
        $publicationId = $this->prepareCandidate();
        $repository = new EodPublicationRepository();
        $repository->sealCandidatePublication(EodRun::query()->findOrFail(self::RUN_ID), 'operator');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/SEALED_PUBLICATION_IMMUTABLE/');
        $repository->assertPublicationMutable($publicationId);
    }

    /**
     * `MD-S082-R0225` -- as-known replay cannot see later revisions.
     *
     * The configuration frozen with this publication was recorded *after* the run's own knowledge
     * cutoff. An as-known replay bounded by that cutoff cannot see it, so the publication contains
     * a revision its own replay could never reach -- the two disagree by construction rather than
     * by drift. Refused at the only moment it can still be corrected.
     */
    public function test_a_configuration_recorded_after_the_run_cutoff_is_refused_before_seal(): void
    {
        $publicationId = $this->prepareCandidate();
        DB::table('md_config_snapshots')->where('config_snapshot_id', self::CONFIG_SNAPSHOT_ID)
            ->update(['recorded_at' => '2026-03-21 09:00:00']);

        try {
            (new EodPublicationRepository())
                ->sealCandidatePublication(EodRun::query()->findOrFail(self::RUN_ID), 'operator');
            $this->fail('a publication froze a configuration recorded after its own knowledge cutoff');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('RUN_SEAL_PRECONDITION_FAILED', $e->getMessage());
            $this->assertStringContainsString('2026-03-21 09:00:00', $e->getMessage(),
                'the refusal must name the revision time it refused');
            $this->assertStringContainsString(self::CUTOFF, $e->getMessage(),
                'and the cutoff it was measured against');
        }

        $this->assertSame('UNSEALED', (string) DB::table('eod_publications')
            ->where('publication_id', $publicationId)->value('seal_state'));
    }

    /**
     * The boundary is inclusive: a configuration recorded exactly at the cutoff was knowable, so it
     * seals. Without this the guard above would be satisfied by refusing every configuration
     * whose recording time is anywhere near the cutoff.
     */
    public function test_a_configuration_recorded_exactly_at_the_cutoff_still_seals(): void
    {
        $this->prepareCandidate();
        DB::table('md_config_snapshots')->where('config_snapshot_id', self::CONFIG_SNAPSHOT_ID)
            ->update(['recorded_at' => self::CUTOFF]);

        $sealed = (new EodPublicationRepository())
            ->sealCandidatePublication(EodRun::query()->findOrFail(self::RUN_ID), 'operator');

        $this->assertSame('SEALED', (string) $sealed->seal_state);
    }

    /**
     * `MD-S082-R0225`, the other way the boundary goes missing: a run with no knowledge cutoff at
     * all. An as-known replay would then have nothing to resolve revisions against.
     *
     * Enforced upstream rather than at the seal, by the same service that writes the lineage
     * binding a seal requires. Asserting it here rather than adding a second cutoff check to the
     * seal keeps one enforcement point per rule.
     */
    public function test_the_lineage_binder_refuses_a_run_without_a_knowledge_cutoff(): void
    {
        $this->prepareCandidate();
        DB::table('eod_runs')->where('run_id', self::RUN_ID)->update(['knowledge_cutoff_at' => null]);
        $publication = DB::table('eod_publications')->where('trade_date', self::TRADE_DATE)->first();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/RUN_KNOWLEDGE_CUTOFF_MISSING/');
        app(\App\Application\MarketData\Services\PublicationGovernanceBindingService::class)
            ->bind(EodRun::query()->findOrFail(self::RUN_ID), $publication, self::TRADE_DATE);
    }

    /**
     * The contract's before-seal list is numbered, and this class covers items 5 and 6. Reading
     * them from the document rather than restating them means a renumbering or a rewording fails
     * here instead of leaving these two bound to text that moved.
     */
    public function test_the_contract_still_numbers_these_two_validations_five_and_six(): void
    {
        $path = dirname(__DIR__, 3).'/docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md';
        $this->assertFileExists($path);
        $source = (string) file_get_contents($path);

        $start = strpos($source, 'Before seal, validation proves:');
        $this->assertNotFalse($start, 'the before-seal introducer moved; re-read the contract');

        preg_match_all('/^(\d+)\.\s+(.+?);?(?: and)?$/m', substr($source, $start, 900), $matches, PREG_SET_ORDER);
        $items = [];
        foreach ($matches as $m) {
            $items[(int) $m[1]] = rtrim(trim($m[2]), '.');
        }

        $this->assertCount(7, $items, 'the before-seal validation list no longer has seven items');
        $this->assertSame('current environment drift cannot change publication replay', $items[5]);
        $this->assertSame('as-known replay cannot see later revisions', $items[6]);
    }

    // ---- fixture ---------------------------------------------------------------------------------

    /**
     * A candidate carrying everything else the seal requires, so the tests above remove exactly one
     * thing each. This mirrors the production sequence: hashes, analytical product, then the stage
     * eight governance bindings and the deterministic manifest.
     *
     * @return int the candidate publication id
     */
    private function prepareCandidate(): int
    {
        $repository = new EodPublicationRepository();
        $run = EodRun::query()->findOrFail(self::RUN_ID);
        $factorHash = hash('sha256', 'before-seal-factor-set');

        $candidate = $repository->getOrCreateCandidatePublication($run, null);
        $repository->updateCandidateHashes($candidate->publication_id, [
            'bars_batch_hash' => hash('sha256', 'before-seal-bars'),
            'indicators_batch_hash' => hash('sha256', 'before-seal-indicators'),
            'eligibility_batch_hash' => hash('sha256', 'before-seal-eligibility'),
        ]);
        $repository->bindCandidateAnalyticalProduct(
            $candidate->publication_id,
            $run->run_id,
            'STRUCTURAL_ADJUSTED',
            'structural_adjusted_v1',
            $factorHash,
            1
        );
        $this->bindGovernance($candidate->publication_id, $factorHash);

        return (int) $candidate->publication_id;
    }

    private function bindGovernance(int $publicationId, string $factorHash): void
    {
        DB::table('md_adjustment_factor_sets')->updateOrInsert(
            ['factor_set_id' => 1],
            [
                'factor_set_uid' => $factorHash,
                'price_product_code' => 'STRUCTURAL_ADJUSTED',
                'factor_formula_version' => 'structural_factor_product_v1',
                'config_snapshot_id' => self::CONFIG_SNAPSHOT_ID,
                'state' => 'BOUND',
                'content_hash' => $factorHash,
                'recorded_at' => '2026-03-20 17:10:00',
                'created_at' => '2026-03-20 17:10:00',
            ]
        );

        // Recorded before the run's knowledge cutoff, which is the state the contract requires and
        // the tests above move away from one at a time.
        DB::table('md_config_snapshots')->updateOrInsert(
            ['config_snapshot_id' => self::CONFIG_SNAPSHOT_ID],
            [
                'snapshot_uid' => hash('sha256', 'before-seal-config'),
                'snapshot_schema_version' => 'market_data_config_snapshot_v1',
                'serialization_version' => 'canonical_json_v1',
                'resolved_config_json' => '{}',
                'config_hash' => hash('sha256', 'before-seal-config-content'),
                'registry_revision' => 'platform_config_registry_v2',
                'effective_at' => self::TRADE_DATE.' 00:00:00',
                'recorded_at' => self::TRADE_DATE.' 17:00:00',
                'build_id' => 'test-build',
                'environment_profile' => 'testing',
                'resolver_version' => 'test-resolver-v1',
                'created_at' => self::TRADE_DATE.' 17:00:00',
            ]
        );

        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->first();
        $observationManifestHash = hash('sha256', 'before-seal-observation-manifest');

        DB::table('eod_publications')->where('publication_id', $publicationId)->update([
            'source_scale_assessment_set_hash' => hash('sha256', 'before-seal-source-scale'),
            'market_structure_revision_set_hash' => hash('sha256', 'before-seal-market-structure'),
            'factor_decision_set_hash' => hash('sha256', 'before-seal-factor-decisions'),
            'config_snapshot_id' => self::CONFIG_SNAPSHOT_ID,
            'observation_manifest_hash' => $observationManifestHash,
        ]);

        DB::table('md_publication_lineage_bindings')->updateOrInsert(
            ['publication_id' => $publicationId],
            [
                'config_snapshot_id' => self::CONFIG_SNAPSHOT_ID,
                'factor_set_id' => 1,
                'observation_manifest_hash' => $observationManifestHash,
                'identity_revision_set_hash' => hash('sha256', 'before-seal-identity'),
                'calendar_revision_set_hash' => hash('sha256', 'before-seal-calendar'),
                'status_revision_set_hash' => hash('sha256', 'before-seal-status'),
                'event_revision_set_hash' => hash('sha256', 'before-seal-event'),
                'source_scale_assessment_set_hash' => hash('sha256', 'before-seal-source-scale'),
                'market_structure_revision_set_hash' => hash('sha256', 'before-seal-market-structure'),
                'factor_decision_set_hash' => hash('sha256', 'before-seal-factor-decisions'),
                'formula_version' => 'eod_indicators_v1',
                'build_id' => 'test-build',
                'read_model_version' => 'market_data_read_product_v1',
                'created_at' => '2026-03-20 17:10:00',
            ]
        );

        DB::table('eod_runs')->where('run_id', self::RUN_ID)->update([
            'config_snapshot_id' => self::CONFIG_SNAPSHOT_ID,
            'observation_manifest_hash' => $observationManifestHash,
            'bars_batch_hash' => $publication->bars_batch_hash,
            'indicators_batch_hash' => $publication->indicators_batch_hash,
            'eligibility_batch_hash' => $publication->eligibility_batch_hash,
        ]);

        DB::table('eod_bars_history')->updateOrInsert(
            ['publication_id' => $publicationId, 'trade_date' => self::TRADE_DATE, 'ticker_id' => 999999],
            [
                'source' => 'MANUAL_FILE',
                'run_id' => self::RUN_ID,
                'canonicalization_version' => 'eod_canonical_v1',
                'price_product_code' => 'RAW',
                'quality_state' => 'VALIDATED',
                'created_at' => '2026-03-20 17:10:00',
            ]
        );

        (new EodPublicationRepository())->prepareCandidateManifestForSeal(
            EodRun::query()->findOrFail(self::RUN_ID),
            $publicationId
        );
    }

    private function seedRun(): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => self::RUN_ID,
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
            'knowledge_cutoff_at' => self::CUTOFF,
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
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
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => hash('sha256', 'before-seal-factor-set'),
            'started_at' => '2026-03-20 17:01:00',
            'created_at' => '2026-03-20 17:01:00',
            'updated_at' => '2026-03-20 17:21:00',
        ]);
    }
}
