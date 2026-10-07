<?php

use App\Application\MarketData\Services\ReplayV2IdentityProjection;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * MD-B04-A003 — the reason-registry semantic identity is a derived member of every new immutable
 * configuration snapshot (owner decision D-MD-B18-A002-018, Q9 = A1; derivation D-MD-B18-A002-014, Q4 = A).
 *
 * Owner contract: docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md
 * (MD-S082-R0036 "data-usability decision/reason registry versions"; MD-S082-R0044 "contamination
 * horizons and reason-code registry version").
 *
 * What each test would let through if it were removed:
 *  - a snapshot that carries no reason-registry identity (the state F-MD-B18-A002-034 recorded),
 *  - a registry change that leaves the snapshot hash where it was,
 *  - a placeholder identity that makes an unreadable registry look like a known one,
 *  - a historical snapshot rewritten to look as if it had always carried the member.
 */
class ReasonRegistrySnapshotMemberTest extends TestCase
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

    private function independentIdentity(): string
    {
        $entries = [];
        foreach (DB::table('eod_reason_codes')->orderBy('code')->get() as $row) {
            $entries[] = ['code' => $row->code, 'category' => $row->category, 'description' => $row->description,
                'severity' => $row->severity, 'is_active' => (bool) $row->is_active];
        }
        $identity = ReplayV2IdentityProjection::reasonRegistryIdentity(['reason_entries' => $entries]);
        $this->assertNotNull($identity, 'the seeded registry must be derivable');

        return $identity;
    }

    private function member(array $snapshot): array
    {
        $payload = json_decode($snapshot['resolved_config_json'], true);
        $this->assertArrayHasKey('reason_registry', $payload, 'a new snapshot must carry the derived reason-registry member');

        return $payload['reason_registry'];
    }

    private function rewriteRegistry(callable $mutate): void
    {
        $rows = array_map(static function ($r) { return (array) $r; }, DB::table('eod_reason_codes')->orderBy('code')->get()->all());
        $rows = $mutate($rows);
        DB::table('eod_reason_codes')->delete();
        foreach ($rows as $row) {
            DB::table('eod_reason_codes')->insert($row);
        }
    }

    private function snapshotCount(): int
    {
        return DB::table('md_config_snapshots')->count();
    }

    /** A pre-change snapshot: the same canonical content a snapshot had before the member existed. */
    private function insertLegacySnapshot(string $effectiveAt, string $recordedAt): array
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $content = json_decode($repository->currentContent(), true);
        unset($content['reason_registry']);
        $json = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        $hash = hash('sha256', $json);
        $schema = (string) config('market_data.governance.config_snapshot_schema_version', 'market_data_config_snapshot_v1');
        $profile = (string) config('market_data.governance.environment_profile', 'local');
        $id = DB::table('md_config_snapshots')->insertGetId([
            'snapshot_uid' => hash('sha256', 'legacy|'.$effectiveAt.'|'.$hash),
            'snapshot_schema_version' => $schema, 'serialization_version' => 'canonical_json_v1',
            'resolved_config_json' => $json, 'config_hash' => $hash,
            'registry_revision' => 'platform_config_registry_v2', 'effective_at' => $effectiveAt, 'recorded_at' => $recordedAt,
            'build_id' => 'legacy-build', 'environment_profile' => $profile, 'resolver_version' => 'market_data_config_resolver_v1',
            'created_at' => $recordedAt,
        ]);

        return (array) DB::table('md_config_snapshots')->where('config_snapshot_id', $id)->first();
    }

    // ---- producer -> member -> canonical content -> hash

    public function test_a_new_snapshot_carries_the_reason_registry_identity_derived_from_the_registry_content(): void
    {
        $snapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
        $member = $this->member($snapshot);

        $this->assertSame(['identity_contract', 'semantic_identity'], array_keys($member));
        $this->assertSame(ReplayV2IdentityProjection::REASON_SCHEMA, $member['identity_contract']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $member['semantic_identity']);
        $this->assertSame($this->independentIdentity(), $member['semantic_identity'], 'the member is the semantic identity of the registry content, derived by the single projection');
    }

    public function test_the_member_is_part_of_the_canonical_content_and_of_the_content_hash(): void
    {
        $snapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
        $this->assertSame(hash('sha256', $snapshot['resolved_config_json']), $snapshot['config_hash'], 'the stored hash is the hash of the stored canonical content');

        $withoutMember = json_decode($snapshot['resolved_config_json'], true);
        unset($withoutMember['reason_registry']);
        $withoutJson = json_encode($withoutMember, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        $this->assertNotSame($snapshot['config_hash'], hash('sha256', $withoutJson), 'omitting the member would change the hash, so the hash covers it');

        $top = array_keys(json_decode($snapshot['resolved_config_json'], true));
        $sorted = $top; sort($sorted, SORT_STRING);
        $this->assertSame($sorted, $top, 'the member sits in the canonical, key-sorted position');
        $this->assertSame(['reason_registry', 'resolved_config', 'semantic_bindings'], $top);
    }

    public function test_the_member_is_deterministic_across_independent_resolutions(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $first = $repository->currentContent();
        $second = (new MarketDataConfigSnapshotRepository())->currentContent();

        $this->assertSame($first, $second);
        $this->assertSame(hash('sha256', $first), hash('sha256', $second));
    }

    /** @dataProvider semanticFields */
    public function test_a_change_in_registry_content_moves_the_member_and_the_hash(string $field, $value): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $before = $repository->resolveForRun('2026-03-20');

        $code = DB::table('eod_reason_codes')->orderBy('code')->value('code');
        DB::table('eod_reason_codes')->where('code', $code)->update([$field => $value]);
        $after = $repository->resolveForRun('2026-03-20');

        $this->assertNotSame($this->member($before)['semantic_identity'], $this->member($after)['semantic_identity'], $field.' is semantic registry content');
        $this->assertNotSame($before['config_hash'], $after['config_hash'], 'a registry content change must move the snapshot hash');
        $this->assertNotSame($before['config_snapshot_id'], $after['config_snapshot_id'], 'and must not reuse the earlier snapshot');
        $this->assertSame(2, $this->snapshotCount());
    }

    public function semanticFields(): array
    {
        return ['category' => ['category', 'CHANGED'], 'description' => ['description', 'A materially different meaning.'],
            'severity' => ['severity', 'CRITICAL_X'], 'is_active' => ['is_active', 0]];
    }

    public function test_an_added_or_removed_code_moves_the_member_and_the_hash(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $before = $repository->resolveForRun('2026-03-20');

        DB::table('eod_reason_codes')->insert(['code' => 'ZZ_NEW_CODE', 'category' => 'RUN', 'description' => 'new', 'severity' => 'INFO', 'is_active' => 1]);
        $added = $repository->resolveForRun('2026-03-20');
        $this->assertNotSame($before['config_hash'], $added['config_hash']);

        DB::table('eod_reason_codes')->where('code', 'ZZ_NEW_CODE')->delete();
        DB::table('eod_reason_codes')->orderBy('code')->limit(1)->delete();
        $removed = $repository->resolveForRun('2026-03-20');
        $this->assertNotSame($before['config_hash'], $removed['config_hash']);
        $this->assertNotSame($added['config_hash'], $removed['config_hash']);
    }

    public function test_unchanged_registry_content_reuses_the_snapshot_whatever_the_row_order_and_audit_columns(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $first = $repository->resolveForRun('2026-03-20');

        $this->rewriteRegistry(static function (array $rows) {
            foreach ($rows as $i => $row) {
                $rows[$i]['created_at'] = '2031-01-01 00:00:00';
                $rows[$i]['updated_at'] = '2031-01-02 03:04:05';
            }

            return array_reverse($rows);
        });
        $second = $repository->resolveForRun('2026-03-20');

        $this->assertSame($first['config_snapshot_id'], $second['config_snapshot_id'], 'insertion order and audit timestamps are not semantic content');
        $this->assertSame($first['config_hash'], $second['config_hash']);
        $this->assertSame(1, $this->snapshotCount());
    }

    public function test_the_member_is_not_a_registered_platform_config_key(): void
    {
        $this->assertArrayNotHasKey('reason_registry', config('market_data'));
        $snapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
        $resolved = json_decode($snapshot['resolved_config_json'], true)['resolved_config'];
        $this->assertArrayNotHasKey('reason_registry', $resolved, 'the identity is derived, never configured');
        $this->assertStringNotContainsString('reason_registry', json_encode($resolved));
    }

    // ---- fail closed: no placeholder

    public function test_an_empty_registry_blocks_snapshot_creation_and_leaves_no_placeholder_snapshot(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        DB::table('eod_reason_codes')->delete();

        try {
            $repository->resolveForRun('2026-03-20');
            $this->fail('An empty registry has no identity and must block snapshot creation.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('CONFIG_SNAPSHOT_REASON_REGISTRY_IDENTITY_UNDERIVABLE', $e->getMessage());
        }
        $this->assertSame(0, $this->snapshotCount(), 'nothing stands in for the missing identity');
        $this->expectException(RuntimeException::class);
        $repository->currentContent();
    }

    /** @dataProvider malformedRegistries */
    public function test_a_malformed_registry_blocks_snapshot_creation_and_leaves_no_snapshot(callable $corrupt): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        $good = $repository->resolveForRun('2026-03-20');
        $corrupt();

        try {
            $repository->resolveForRun('2026-03-21');
            $this->fail('A malformed registry must block snapshot creation.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('CONFIG_SNAPSHOT_REASON_REGISTRY_IDENTITY_UNDERIVABLE', $e->getMessage());
        }
        $this->assertSame(1, $this->snapshotCount(), 'no placeholder or partial snapshot was written');
        $this->assertSame($good, (array) DB::table('md_config_snapshots')->first(), 'the earlier snapshot is untouched');
    }

    public function malformedRegistries(): array
    {
        return [
            'empty code' => [static function () { DB::table('eod_reason_codes')->orderBy('code')->limit(1)->update(['code' => '']); }],
            'empty category' => [static function () { DB::table('eod_reason_codes')->orderBy('code')->limit(1)->update(['category' => '']); }],
            'empty severity' => [static function () { DB::table('eod_reason_codes')->orderBy('code')->limit(1)->update(['severity' => '']); }],
        ];
    }

    public function test_an_unreadable_registry_blocks_snapshot_creation(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();
        Schema::drop('eod_reason_codes');

        try {
            $repository->resolveForRun('2026-03-20');
            $this->fail('An unreadable registry must block snapshot creation.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('CONFIG_SNAPSHOT_REASON_REGISTRY_UNREADABLE', $e->getMessage());
        }
        $this->assertSame(0, $this->snapshotCount());
    }

    // ---- historical snapshots are immutable and never back-filled

    public function test_a_historical_snapshot_without_the_member_is_never_modified_or_back_filled(): void
    {
        $legacy = $this->insertLegacySnapshot('2026-01-05 00:00:00', '2026-01-05 08:00:00');
        $this->assertArrayNotHasKey('reason_registry', json_decode($legacy['resolved_config_json'], true));

        $new = (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');

        $this->assertNotSame($legacy['config_snapshot_id'], $new['config_snapshot_id'], 'the legacy hash differs from the live one, so it is not reused');
        $this->assertArrayHasKey('reason_registry', json_decode($new['resolved_config_json'], true));
        $this->assertSame($legacy, (array) DB::table('md_config_snapshots')->where('config_snapshot_id', $legacy['config_snapshot_id'])->first(), 'every column of the historical row is byte-identical');
        $this->assertArrayNotHasKey('reason_registry', json_decode(DB::table('md_config_snapshots')->where('config_snapshot_id', $legacy['config_snapshot_id'])->value('resolved_config_json'), true));
    }

    public function test_as_known_resolution_returns_the_historical_snapshot_as_it_was_without_a_member(): void
    {
        $legacy = $this->insertLegacySnapshot('2026-01-05 00:00:00', '2026-01-05 08:00:00');
        $repository = new MarketDataConfigSnapshotRepository();
        $new = $repository->resolveForRun('2026-03-20');

        $known = $repository->resolveForRun('2026-03-20', '2026-02-01 00:00:00');

        $this->assertSame($legacy['config_snapshot_id'], $known['config_snapshot_id'], 'as-known returns the historical snapshot exactly as recorded');
        $this->assertSame($legacy['resolved_config_json'], $known['resolved_config_json'], 'as-known returns the historical snapshot exactly as recorded');
        $this->assertArrayNotHasKey('reason_registry', json_decode($known['resolved_config_json'], true), 'as-known never manufactures the identity for a snapshot that has none');
        $this->assertSame(2, $this->snapshotCount(), 'resolving as-known never inserts');
        $this->assertNotSame($new['config_snapshot_id'], $known['config_snapshot_id']);
    }

    // ---- run-bound behaviour

    public function test_a_run_bound_to_a_pre_change_snapshot_is_refused_and_not_migrated(): void
    {
        // A run as it stood before the change: bound to a snapshot without the member and with no capture of its own yet.
        $template = (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'md-b04-a003')->getAttributes();
        $legacy = $this->insertLegacySnapshot('2026-03-25 00:00:00', '2026-03-25 08:00:00');
        unset($template['run_id']);
        $runId = DB::table('eod_runs')->insertGetId(array_merge($template, [
            'trade_date_requested' => '2026-03-25', 'config_snapshot_id' => $legacy['config_snapshot_id'],
            'config_hash' => $legacy['config_hash'], 'config_snapshot_ref' => $legacy['snapshot_uid'],
        ]));
        $run = DB::table('eod_runs')->where('run_id', $runId)->first();
        $bound = DB::table('eod_runs')->where('run_id', $run->run_id)->first();
        $snapshotsBefore = DB::table('md_config_snapshots')->orderBy('config_snapshot_id')->get()->all();

        try {
            (new RunInputCaptureRepository())->assertConsumedConfiguration($bound);
            $this->fail('A producer must not run under a snapshot that differs from the live configuration.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE', $e->getMessage());
        }
        $this->assertEquals($bound, DB::table('eod_runs')->where('run_id', $run->run_id)->first(), 'the run keeps the snapshot it was bound to; nothing switches it');
        $this->assertEquals($snapshotsBefore, DB::table('md_config_snapshots')->orderBy('config_snapshot_id')->get()->all(), 'no snapshot is created, changed or back-filled by the refusal');
    }

    public function test_a_run_is_refused_when_the_registry_content_changes_after_it_was_bound(): void
    {
        $run = (new EodRunRepository())->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'md-b04-a003');
        $capture = new RunInputCaptureRepository();
        $capture->assertConsumedConfiguration($run);

        $code = DB::table('eod_reason_codes')->orderBy('code')->value('code');
        DB::table('eod_reason_codes')->where('code', $code)->update(['description' => 'Changed after the run was bound.']);

        try {
            $capture->assertConsumedConfiguration($run);
            $this->fail('A reason-registry change after binding must refuse the producer.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE', $e->getMessage());
        }
    }
}
