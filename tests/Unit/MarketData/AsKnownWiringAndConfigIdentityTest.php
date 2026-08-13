<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * F-031 and F-032 — the two ways a correct mechanism still failed to run.
 *
 * F-028 gave the event and factor roots a knowledge cutoff and proved it at the repository level.
 * It changed nothing in production, because EodIndicatorsComputeService computed $knownAt, handed
 * it to the sector root, and then called the event, contamination and factor roots without it —
 * with the variable two lines above. The capability was real and unwired.
 *
 * F-032 is the same shape one layer down. MarketDataConfigSnapshotRepository resolves and inserts a
 * snapshot, and getOrCreateOwningRun binds it; createPromoteRunFromSeed carried no config identity
 * at all, so every promote run — including the 843 recompute runs of 2026-08-10/11 — was created
 * unbound and md_config_snapshots stayed empty. Replay then refused those runs as
 * REPLAY_CONFIG_UNBOUND.
 */
class AsKnownWiringAndConfigIdentityTest extends TestCase
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
     * Every temporal root reached from the compute path receives the run's cutoff.
     *
     * This is asserted on the source because the defect was a missing argument, not a wrong result:
     * each root already had behavioural coverage in AsKnownReplayBoundaryTest and passed there while
     * production still leaked. What went unchecked was whether anything ever called them with a
     * cutoff.
     */
    public function test_the_compute_path_passes_its_cutoff_to_every_temporal_root(): void
    {
        $source = (string) file_get_contents(
            __DIR__.'/../../../app/Application/MarketData/Services/EodIndicatorsComputeService.php'
        );

        $this->assertStringContainsString(
            '$knownAt = $run->started_at',
            $source,
            'the compute path must derive a cutoff from the run'
        );

        foreach ([
            'resolveSectorContextForTickerIds',
            'resolveEventRiskContextForTickerIds',
            'resolveCorporateActionContaminationForTickerIds',
            'resolveAdjustmentFactorsForTickerIds',
        ] as $method) {
            $this->assertTrue(
                (bool) preg_match('/'.preg_quote($method, '/').'\((?:[^;]*?)\$knownAt/s', $source),
                $method.' must be called with the run cutoff, not with the default null'
            );
        }

        // The private helpers must forward it rather than accepting and dropping it.
        foreach ([
            'resolveCorporateActionContamination',
            'resolveAdjustmentFactors',
        ] as $helper) {
            $this->assertTrue(
                (bool) preg_match('/private function '.preg_quote($helper, '/').'\([^)]*\$knownAt/s', $source),
                $helper.' must accept the cutoff so it can forward it'
            );
        }
    }

    /**
     * A promote run records the configuration it executed under.
     *
     * The assertion is not merely "the column is non-null": the snapshot row must exist and its
     * hash must match what the run claims, because a run pointing at a snapshot that was never
     * written would reproduce the original defect in a form that reads as fixed.
     */
    public function test_a_promote_run_is_bound_to_a_config_snapshot(): void
    {
        $seedId = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => '2026-03-24',
            'lifecycle_state' => 'COMPLETED',
            'stage' => 'FINALIZE',
            'source' => 'api',
            'created_at' => '2026-03-24 18:00:00',
            'updated_at' => '2026-03-24 18:00:00',
        ]);

        $this->assertSame(0, (int) DB::table('md_config_snapshots')->count(), 'precondition: no snapshot yet');

        $seed = EodRun::query()->findOrFail($seedId);
        $promoted = (new EodRunRepository())->createPromoteRunFromSeed($seed, 'COMPUTE_INDICATORS');

        $this->assertNotNull($promoted->config_snapshot_id, 'a promote run may not be created config-unbound');

        $snapshot = DB::table('md_config_snapshots')
            ->where('config_snapshot_id', $promoted->config_snapshot_id)
            ->first();

        $this->assertNotNull($snapshot, 'the bound snapshot must actually exist');
        $this->assertSame(
            $snapshot->config_hash,
            $promoted->config_hash,
            'the run must carry the hash of the snapshot it points at'
        );
        $this->assertSame($snapshot->snapshot_uid, $promoted->config_snapshot_ref);
    }
}
