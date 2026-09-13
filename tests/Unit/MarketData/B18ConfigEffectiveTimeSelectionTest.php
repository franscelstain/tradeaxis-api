<?php

use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * `MD-B18-A002` -- the clause `MD-S082-R0216` and `MD-S082-R0217` share.
 *
 * `Platform_Config_Registry_LOCKED.md`, "Effective-time and replay rules":
 *
 * > Operational production selects the approved configuration effective for the run context and
 * > records when it became known. Two replay modes are distinct:
 * > - publication replay uses the exact snapshot frozen with the publication;
 * > - as-known replay resolves only revisions known by the declared knowledge cutoff, then freezes
 * >   a new replay snapshot.
 *
 * Both rows carry that first sentence and differ only in which replay-mode bullet follows. Both
 * were bound to guards for the bullet, and the recorded basis for each says the same thing: that
 * production selects the approved effective configuration is not asserted. This class asserts it,
 * against the real `MarketDataConfigSnapshotRepository` on the SQLite mirror.
 *
 * The three claims in that sentence are separable and are separated here:
 *
 *  - **approved** -- a configuration the registry does not admit never becomes a snapshot;
 *  - **effective for the run context** -- the snapshot governing a trade date is the one effective
 *    on or before it, and within that interval the latest revision;
 *  - **records when it became known** -- `recorded_at` is persisted and is a different fact from
 *    `effective_at`, which is what lets the as-known branch filter on it.
 *
 * The fixture builds its snapshots by resolving the live configuration once and then moving the
 * resulting row's effective and recorded times, rather than hand-writing a `config_hash`. A
 * hand-written hash would never match what the resolver canonicalises, so production resolution
 * would write a fresh row every time and the selection under test would never run.
 */
class B18ConfigEffectiveTimeSelectionTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-24';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    /**
     * `MD-S082-R0216`/`R0217` -- effective for the run context.
     *
     * Two approved configurations govern different intervals: one effective from 1 March, one from
     * 1 April. A run for 24 March must select the March one. Selecting the April configuration
     * would apply a configuration to a date it did not govern -- and since the April row is the
     * newest, taking "the latest approved configuration" is exactly the wrong answer that this
     * distinguishes.
     */
    public function test_the_configuration_effective_for_the_run_context_governs_and_not_the_newest(): void
    {
        [$march, $april] = $this->seedTwoIntervals();

        $resolved = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);

        $this->assertSame($march, (int) $resolved['config_snapshot_id'],
            'the run selected a configuration that was not yet effective for its trade date');
        $this->assertNotSame($april, (int) $resolved['config_snapshot_id']);
        $this->assertSame('2026-03-01 00:00:00', (string) $resolved['effective_at']);
    }

    /**
     * Within the governing interval, the latest revision wins. A configuration corrected on 15
     * March still takes effect from 1 March; a run for 24 March must use the correction, not the
     * superseded original.
     */
    public function test_the_latest_revision_of_the_governing_interval_is_the_one_selected(): void
    {
        [$march] = $this->seedTwoIntervals();
        $correction = $this->copySnapshot($march, '2026-03-01 00:00:00', '2026-03-15 10:00:00');

        $resolved = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);

        $this->assertSame($correction, (int) $resolved['config_snapshot_id'],
            'the run selected a superseded revision of the interval that governs its trade date');
    }

    /**
     * `MD-S082-R0217` -- as-known resolves only revisions known by the declared cutoff.
     *
     * The same trade date, read as of 10 March, must not see the correction recorded on 15 March.
     * Read together with the test above, the only difference between the two answers is the cutoff,
     * so knowledge time is what selects and not effective time alone.
     */
    public function test_as_known_resolution_sees_only_the_revision_recorded_by_its_cutoff(): void
    {
        [$march] = $this->seedTwoIntervals();
        $correction = $this->copySnapshot($march, '2026-03-01 00:00:00', '2026-03-15 10:00:00');

        $repository = new MarketDataConfigSnapshotRepository();
        $asKnown = $repository->resolveForRun(self::TRADE_DATE, '2026-03-10 00:00:00');
        $today = $repository->resolveForRun(self::TRADE_DATE);

        $this->assertSame($march, (int) $asKnown['config_snapshot_id'],
            'a correction recorded on 15 March cannot be known to a replay reading as of 10 March');
        $this->assertSame($correction, (int) $today['config_snapshot_id'],
            'and it is selected without a cutoff, so the cutoff is a filter rather than a wall');
    }

    /**
     * `MD-S082-R0216`/`R0217` -- records when it became known.
     *
     * `recorded_at` and `effective_at` answer different questions: when the platform learned the
     * configuration, and which dates it governs. They are separate columns carrying different
     * values, which is the only reason the as-known branch above can filter on one without
     * disturbing the other.
     */
    public function test_the_moment_a_configuration_became_known_is_recorded_separately_from_its_effective_time(): void
    {
        Carbon::setTestNow('2026-03-25 09:30:00');

        $resolved = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);

        $this->assertSame(self::TRADE_DATE.' 00:00:00', (string) $resolved['effective_at'],
            'the configuration takes effect from the run context it was resolved for');
        $this->assertSame('2026-03-25 09:30:00', (string) $resolved['recorded_at'],
            'and records the moment it became known, which is when it was resolved');
        $this->assertNotSame((string) $resolved['effective_at'], (string) $resolved['recorded_at'],
            'effective time and knowledge time carry the same value, so one is standing in for the '
                .'other and the as-known filter has nothing to select on');
    }

    /**
     * `MD-S082-R0216`/`R0217` -- **approved**.
     *
     * A configuration the registry does not admit is refused before any snapshot exists. "Approved"
     * has no state column on the snapshot; it is enforced at the only point where it can be, which
     * is admission. A run that snapshotted an unregistered key would make that key part of a frozen
     * identity nothing had ever validated.
     */
    public function test_a_configuration_the_registry_does_not_admit_never_becomes_a_snapshot(): void
    {
        config()->set('market_data.an_unregistered_key_no_definition_covers', 'x');
        $before = (int) DB::table('md_config_snapshots')->count();

        try {
            (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);
            $this->fail('an unregistered configuration key was accepted into a snapshot');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('CONFIG_REGISTRY_KEY_MISMATCH', $e->getMessage());
            $this->assertStringContainsString('an_unregistered_key_no_definition_covers', $e->getMessage(),
                'the refusal must name the key it refused, or an operator cannot act on it');
        }

        $this->assertSame($before, (int) DB::table('md_config_snapshots')->count(),
            'the refused configuration still wrote a snapshot row');
    }

    /**
     * The control on the approval guard: the unmodified configuration *is* admitted and does
     * produce a snapshot. Without it the guard above would pass equally against a resolver that
     * refuses everything.
     */
    public function test_the_unmodified_configuration_is_admitted_and_snapshotted(): void
    {
        $before = (int) DB::table('md_config_snapshots')->count();

        $resolved = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);

        $this->assertGreaterThan($before, (int) DB::table('md_config_snapshots')->count());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $resolved['config_hash']);
    }


    // ---- MD-S065-R0003: an output-affecting change is a contract change ---------------------------

    /**
     * `Config_Change_Protocol_LOCKED.md`: "Any output-affecting config change must be treated as a
     * contract change."
     *
     * Treated as a contract change means it acquires a new identity rather than quietly altering
     * the one already in use. Changing an output-affecting key must produce a distinct
     * `config_hash` and a new snapshot row, and must leave the previous snapshot exactly as it was
     * -- anything that cited the old snapshot id still has to mean what it meant, or every hash
     * taken under it becomes unexplainable.
     */
    public function test_an_output_affecting_change_produces_a_new_configuration_identity(): void
    {
        Carbon::setTestNow('2026-03-24 09:00:00');
        $before = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);
        $beforeRow = (array) DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $before['config_snapshot_id'])->first();

        // An output-affecting key: the ROC lookback changes a published indicator value.
        config()->set('market_data.indicators.roc_lookback_days', 21);

        Carbon::setTestNow('2026-03-24 10:00:00');
        $after = (new MarketDataConfigSnapshotRepository())->resolveForRun(self::TRADE_DATE);

        $this->assertNotSame($before['config_hash'], $after['config_hash'],
            'an output-affecting change did not change the configuration identity, so a run under '
                .'the new semantics is indistinguishable from one under the old');
        $this->assertNotSame(
            (int) $before['config_snapshot_id'],
            (int) $after['config_snapshot_id'],
            'the change reused the existing snapshot id rather than creating a new contract'
        );

        $this->assertSame($beforeRow, (array) DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $before['config_snapshot_id'])->first(),
            'the previous configuration was rewritten in place, so anything already citing it now '
                .'cites something else');
    }

    /**
     * The control. Resolving twice without changing anything reuses the governing snapshot rather
     * than minting a new contract for every run -- which is what makes the identity above mean
     * "the configuration changed" rather than "a run happened".
     */
    public function test_an_unchanged_configuration_does_not_mint_a_new_contract(): void
    {
        $repository = new MarketDataConfigSnapshotRepository();

        Carbon::setTestNow('2026-03-24 09:00:00');
        $first = $repository->resolveForRun(self::TRADE_DATE);
        Carbon::setTestNow('2026-03-24 10:00:00');
        $second = $repository->resolveForRun(self::TRADE_DATE);

        $this->assertSame(
            (int) $first['config_snapshot_id'],
            (int) $second['config_snapshot_id'],
            'an unchanged configuration produced a second snapshot, so config_snapshot_id is a '
                .'per-run surrogate rather than the identity of a configuration'
        );
        $this->assertSame(1, (int) DB::table('md_config_snapshots')->count());
    }
    // ---- fixture ---------------------------------------------------------------------------------

    /**
     * One approved configuration governing from 1 March and a second, identical in content,
     * governing from 1 April.
     *
     * Both carry the hash the resolver actually produces, so production resolution reuses the
     * governing row instead of writing a third — which is what makes the selection observable.
     *
     * @return array{0:int,1:int} the March and April snapshot ids
     */
    private function seedTwoIntervals(): array
    {
        Carbon::setTestNow('2026-03-01 10:00:00');
        $march = (int) (new MarketDataConfigSnapshotRepository())
            ->resolveForRun(self::TRADE_DATE)['config_snapshot_id'];
        Carbon::setTestNow();

        DB::table('md_config_snapshots')->where('config_snapshot_id', $march)->update([
            'effective_at' => '2026-03-01 00:00:00',
            'recorded_at' => '2026-03-01 10:00:00',
        ]);

        $april = $this->copySnapshot($march, '2026-04-01 00:00:00', '2026-04-01 10:00:00');

        return [$march, $april];
    }

    /** Another revision of the same content at a different effective and recorded time. */
    private function copySnapshot(int $sourceId, string $effectiveAt, string $recordedAt): int
    {
        $row = (array) DB::table('md_config_snapshots')->where('config_snapshot_id', $sourceId)->first();
        unset($row['config_snapshot_id']);

        $row['effective_at'] = $effectiveAt;
        $row['recorded_at'] = $recordedAt;
        $row['snapshot_uid'] = hash('sha256', $row['config_hash'].'|'.$effectiveAt.'|'.$recordedAt);

        return (int) DB::table('md_config_snapshots')->insertGetId($row);
    }
}
