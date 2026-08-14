<?php

use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * F-034 — replay must compare an identity that can actually differ.
 *
 * `config_identity` was sourced from `config_version`, which holds `'v1'` on all 72,765 production
 * runs. Every replay therefore compared `'v1'` against `'v1'`, reported config identity as
 * verified, and made REPLAY_CONFIG_IDENTITY_MISMATCH unreachable no matter what happened to the
 * configuration.
 *
 * Two properties are pinned. The identity must change when the configuration changes — otherwise
 * the fix is cosmetic. And a run with no recorded identity must not vanish from the comparison:
 * compareField() skips a null expectation before it even records the field as checked, so a null
 * would move the silence instead of removing it.
 */
class ReplayConfigIdentityVariesWithConfigTest extends TestCase
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

    private function identityFor($run)
    {
        $service = (new ReflectionClass(ReplayVerificationService::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($service, 'configIdentityForRun');
        $method->setAccessible(true);

        return $method->invoke($service, $run);
    }

    /**
     * The real property: a changed configuration produces a different hash, and therefore a
     * different replay identity. This resolves two snapshots through the production resolver rather
     * than asserting on hand-made values, so it fails if the resolver ever stops distinguishing
     * configurations.
     */
    public function test_a_configuration_change_produces_a_different_replay_identity(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        $before = $repository->resolveForRun('2026-03-24');

        config(['market_data.indicators.roc_lookback_days' => 21]);
        $after = $repository->resolveForRun('2026-03-24');

        $this->assertNotSame(
            $before['config_hash'],
            $after['config_hash'],
            'changing an indicator window must change the config hash'
        );
        $this->assertSame(2, (int) DB::table('md_config_snapshots')->count(), 'each configuration is its own snapshot');

        $identityBefore = $this->identityFor((object) ['config_hash' => $before['config_hash'], 'config_version' => 'v1']);
        $identityAfter = $this->identityFor((object) ['config_hash' => $after['config_hash'], 'config_version' => 'v1']);

        $this->assertNotSame(
            $identityBefore,
            $identityAfter,
            'the replay identity must differ across configurations — this is what config_version could never do'
        );
        $this->assertSame($before['config_hash'], $identityBefore, 'the identity is the content-addressed hash');
    }

    /**
     * F-037 — one date-matching rule, used by both branches.
     *
     * The creating branch matched `effective_at` exactly while the as-known branch matched a range,
     * so the same method answered "which snapshot governs this date" two ways. The exact match also
     * meant an unchanged configuration produced a fresh row per date: a full-range recompute would
     * have written one snapshot per trade date, all with the same config_hash, leaving
     * config_snapshot_id a per-run surrogate rather than the identity of a configuration.
     */
    public function test_an_unchanged_configuration_is_one_snapshot_across_dates(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        $first = $repository->resolveForRun('2026-03-24');
        $second = $repository->resolveForRun('2026-03-25');
        $third = $repository->resolveForRun('2026-03-26');

        $this->assertSame(
            (int) $first['config_snapshot_id'],
            (int) $second['config_snapshot_id'],
            'an unchanged configuration must not mint a new snapshot per date'
        );
        $this->assertSame((int) $first['config_snapshot_id'], (int) $third['config_snapshot_id']);
        $this->assertSame(1, (int) DB::table('md_config_snapshots')->count());

        // And a real change still records itself, effective from the date it first applied.
        config(['market_data.indicators.roc_lookback_days' => 21]);
        $changed = $repository->resolveForRun('2026-03-27');

        $this->assertNotSame((int) $first['config_snapshot_id'], (int) $changed['config_snapshot_id']);
        $this->assertSame(2, (int) DB::table('md_config_snapshots')->count());
    }

    /**
     * The two branches agree on the governing row whenever both can answer.
     */
    public function test_both_branches_select_the_same_governing_snapshot(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        $created = $repository->resolveForRun('2026-03-24');
        DB::table('md_config_snapshots')->update(['recorded_at' => '2026-01-10 00:00:00']);

        $asKnown = $repository->resolveForRun('2026-03-26', '2026-04-15 00:00:00');

        $this->assertSame(
            (int) $created['config_snapshot_id'],
            (int) $asKnown['config_snapshot_id'],
            'a snapshot effective 2026-03-24 governs 2026-03-26 in both modes'
        );
        $this->assertSame(1, (int) DB::table('md_config_snapshots')->count(), 'and the as-known read still inserts nothing');
    }

    /**
     * config_version is no longer consulted. Two runs whose configurations differ must not share an
     * identity merely because they share the legacy constant.
     */
    public function test_the_constant_config_version_no_longer_decides_identity(): void
    {
        $first = $this->identityFor((object) ['config_version' => 'v1', 'config_hash' => str_repeat('a', 64)]);
        $second = $this->identityFor((object) ['config_version' => 'v1', 'config_hash' => str_repeat('b', 64)]);

        $this->assertNotSame($first, $second);
        $this->assertNotSame('v1', $first);
        $this->assertNotSame('v1', $second);
    }

    /**
     * An unrecorded identity stays visible. It must not be null, because compareField() returns on a
     * null expectation without recording the field as checked — the evidence would then show config
     * identity neither compared nor missing.
     */
    public function test_a_run_without_config_identity_is_marked_rather_than_silently_skipped(): void
    {
        $identity = $this->identityFor((object) ['config_version' => 'v1']);

        $this->assertNotNull($identity, 'a null identity would be skipped by compareField without being recorded');
        $this->assertSame('CONFIG_IDENTITY_UNRECORDED', $identity);

        $withRef = $this->identityFor((object) ['config_snapshot_ref' => 'snapshot-uid-1']);
        $this->assertSame('snapshot-uid-1', $withRef, 'the snapshot reference is used when no hash was stored');
    }
}
