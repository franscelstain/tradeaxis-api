<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W04 — immutable configuration snapshot and semantic bindings, stage 16 foundation.
 *
 * Exit gate: "semua writer berikut dapat menerima non-null config/reason/build identity sejak
 * pertama kali dibuat."
 *
 * Owner contract: docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md
 *
 * The existing foundation test proves the snapshot object itself is deterministic and redacted.
 * What was unproven is the part the exit gate actually names: that a writer creating a run
 * receives non-null config identity, and that a semantic change moves the identity rather than
 * silently reusing it.
 */
class ConfigIdentityBindingTest extends TestCase
{
    use UsesMarketDataSqlite;

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

    /**
     * A run created through the repository must carry all three identity fields, non-null, and
     * they must resolve to a real stored snapshot. Without this the run is `CONFIG_UNBOUND` and
     * nothing produced under it is admissible as evidence of reproducibility.
     */
    public function test_a_created_run_receives_non_null_config_identity(): void
    {
        $run = (new EodRunRepository())->getOrCreateOwningRun('2026-03-20', 'api', 'IMPORT');

        $this->assertNotEmpty($run->config_snapshot_id, 'run must bind a config snapshot id');
        $this->assertNotEmpty($run->config_hash, 'run must bind a config hash');
        $this->assertNotEmpty($run->config_snapshot_ref, 'run must bind a snapshot reference');

        $snapshot = DB::table('md_config_snapshots')->where('config_snapshot_id', $run->config_snapshot_id)->first();

        $this->assertNotNull($snapshot, 'the bound snapshot id must resolve to a stored snapshot');
        $this->assertSame($run->config_hash, $snapshot->config_hash);
        $this->assertSame($run->config_snapshot_ref, $snapshot->snapshot_uid);
    }

    /**
     * The snapshot must record enough provenance to explain the run later: which registry
     * revision, which resolver, which build, which environment. A hash without provenance
     * identifies content but not origin.
     */
    public function test_the_bound_snapshot_carries_complete_provenance(): void
    {
        $run = (new EodRunRepository())->getOrCreateOwningRun('2026-03-20', 'api', 'IMPORT');
        $snapshot = DB::table('md_config_snapshots')->where('config_snapshot_id', $run->config_snapshot_id)->first();

        foreach ([
            'snapshot_schema_version',
            'serialization_version',
            'registry_revision',
            'resolver_version',
            'build_id',
            'environment_profile',
            'effective_at',
            'recorded_at',
        ] as $field) {
            $this->assertNotEmpty($snapshot->{$field}, $field.' must be recorded');
        }
    }

    public function test_compiled_semantic_versions_and_feature_state_are_inside_the_snapshot_identity(): void
    {
        $snapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
        $payload = json_decode($snapshot['resolved_config_json'], true);

        $this->assertSame('structural_adjusted_v1', $payload['semantic_bindings']['price_product_version']);
        $this->assertSame('structural_factor_product_v1', $payload['semantic_bindings']['factor_formula_version']);
        $this->assertSame('DISABLED', $payload['semantic_bindings']['session_snapshot_feature_state']);
        $this->assertArrayNotHasKey('price_product_version', $payload['resolved_config']['indicators']);
        $this->assertArrayNotHasKey('factor_formula_version', $payload['resolved_config']['indicators']);
        $this->assertArrayNotHasKey('enabled', $payload['resolved_config']['session_snapshot']);
    }

    /**
     * Stage 16 exit gate, foundation half: one semantic change must change the identity. A
     * configuration that alters output while reusing an identity makes two different runs
     * indistinguishable in the record.
     */
    public function test_one_semantic_config_change_produces_a_different_identity(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $before = $repository->resolveForRun('2026-03-20');

        config()->set('market_data.coverage_gate.min_ratio', 0.5);
        $after = $repository->resolveForRun('2026-03-20');

        $this->assertNotSame($before['config_hash'], $after['config_hash'], 'a semantic change must move the hash');
        $this->assertNotSame($before['config_snapshot_id'], $after['config_snapshot_id']);
        $this->assertNotSame($before['snapshot_uid'], $after['snapshot_uid']);
    }

    /**
     * The inverse must hold too: resolving twice without any change must reuse the identity
     * rather than minting a new one. Otherwise the record accumulates identities that differ
     * without any semantic difference, and hash equality stops meaning anything.
     */
    public function test_an_unchanged_config_reuses_its_identity(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        $first = $repository->resolveForRun('2026-03-20');
        $second = $repository->resolveForRun('2026-03-20');

        $this->assertSame($first['config_snapshot_id'], $second['config_snapshot_id']);
        $this->assertSame($first['config_hash'], $second['config_hash']);
        $this->assertCount(1, DB::table('md_config_snapshots')->get());
    }

    /**
     * Secret material must never reach the stored snapshot, and the redaction must be visible
     * rather than silent — a removed key and a redacted key are different facts.
     */
    public function test_secret_material_is_redacted_and_the_redaction_is_visible(): void
    {
        config()->set('market_data.source.api.auth_token', 'super-secret-token');

        $snapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');

        $this->assertStringNotContainsString('super-secret-token', $snapshot['resolved_config_json']);
        $this->assertStringContainsString('[REDACTED:', $snapshot['resolved_config_json']);
        $this->assertSame($snapshot['config_hash'], hash('sha256', $snapshot['resolved_config_json']));
    }

    /**
     * Two configurations differing only in secret value must share an identity, because the
     * redacted content is what was recorded. If they differed, the hash would leak the
     * existence of a secret change into an artifact that claims to contain none.
     */
    public function test_rotating_a_secret_alone_does_not_move_the_identity(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        config()->set('market_data.source.api.auth_token', 'token-one');
        $first = $repository->resolveForRun('2026-03-20');

        config()->set('market_data.source.api.auth_token', 'token-two');
        $second = $repository->resolveForRun('2026-03-20');

        $this->assertSame($first['config_hash'], $second['config_hash']);
        $this->assertSame($first['config_snapshot_id'], $second['config_snapshot_id']);
    }
}
