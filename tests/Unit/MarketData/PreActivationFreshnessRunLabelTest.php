<?php

use App\Domain\MarketData\FreshnessState;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * MD-B10-A003 (F-MD-B18-A002-033), MD-S045-R0058: the freshness state a run is created with is the truthful consumer
 * state for its requested trade date. A pre-activation run is NOT_APPLICABLE; the decision comes from the requested
 * trade date and the effective activation marker alone (never the wall clock, never the label of another run), in all
 * three run creators, and no existing run is ever relabelled.
 *
 * A finding made while writing this test: the strategy's configuration registry declares
 * `market_data.scope.operational_start_date` with type `null`, so a configured marker string is refused when a run
 * resolves its configuration snapshot. The activated branch of the two configuration-driven creators is therefore not
 * reachable in this build; it is proven in the domain rule (PreActivationFreshnessStateTest) and, through the marker a
 * seed run carries, in the promote creator.
 */
class PreActivationFreshnessRunLabelTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const DATE = '2026-03-24';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow('2026-03-25 10:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function owningRun(string $date = self::DATE, string $mode = 'a003'): EodRun
    {
        return (new EodRunRepository())->getOrCreateOwningRun($date, 'api', 'INGEST_BARS', null, $mode);
    }

    private function seed(?string $label, ?string $marker, string $date = self::DATE): EodRun
    {
        $id = DB::table('eod_runs')->insertGetId([
            'trade_date_requested' => $date,
            'lifecycle_state' => 'COMPLETED',
            'stage' => 'FINALIZE',
            'source' => 'api',
            'operational_start_date' => $marker,
            'freshness_state' => $label,
            'created_at' => '2026-03-24 18:00:00',
            'updated_at' => '2026-03-24 18:00:00',
        ]);

        return EodRun::query()->findOrFail($id);
    }

    public function test_a_new_run_with_no_marker_is_not_applicable_and_records_no_marker(): void
    {
        $run = $this->owningRun();

        $this->assertNull(config('market_data.scope.operational_start_date'), 'control: no marker is configured');
        $this->assertSame('NOT_APPLICABLE', $run->freshness_state);
        $this->assertNull($run->operational_start_date);
        $this->assertNotSame('DEVELOPMENT_NOT_OPERATIONAL', $run->freshness_state);
        $this->assertNotSame('NOT_AVAILABLE', $run->freshness_state, 'NOT_AVAILABLE keeps its meaning: no consumer-safe result');
        $this->assertNotSame('FRESH', $run->freshness_state);
    }

    public function test_the_label_of_a_new_run_does_not_follow_the_wall_clock(): void
    {
        $labels = [];
        foreach (['2020-01-01 00:00:00', '2026-03-25 10:30:00', '2035-12-31 23:59:59'] as $i => $now) {
            Carbon::setTestNow($now);
            $labels[] = $this->owningRun(self::DATE, 'clock-'.$i)->freshness_state;
        }
        $this->assertSame(['NOT_APPLICABLE', 'NOT_APPLICABLE', 'NOT_APPLICABLE'], $labels);
    }

    public function test_the_configuration_registry_still_refuses_a_configured_marker_so_the_activated_branch_is_not_reachable_here(): void
    {
        config()->set('market_data.scope.operational_start_date', '2026-03-01');
        try {
            $this->owningRun(self::DATE, 'registry');
            $this->fail('a configured marker was accepted by the configuration registry');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('CONFIG_REGISTRY_TYPE_MISMATCH: market_data.scope.operational_start_date', $e->getMessage());
        }
    }

    public function test_an_as_known_replay_run_with_no_marker_is_not_applicable(): void
    {
        $this->owningRun(self::DATE, 'records-the-configuration'); // an as-known replay needs a configuration recorded at or before its cutoff
        $run = (new EodRunRepository())->createAsKnownReplayRun(self::DATE, '2099-01-01 00:00:00');

        $this->assertSame('NOT_APPLICABLE', $run->freshness_state);
        $this->assertNull($run->operational_start_date);
    }

    public function test_a_promote_run_decides_again_and_never_copies_a_legacy_label(): void
    {
        $runs = new EodRunRepository();

        $legacy = $this->seed('DEVELOPMENT_NOT_OPERATIONAL', null);
        $promoted = $runs->createPromoteRunFromSeed($legacy, 'COMPUTE_INDICATORS');
        $this->assertSame('NOT_APPLICABLE', $promoted->freshness_state, 'a corrected publication must not carry the pre-correction label');

        $activated = $this->seed('NOT_EVALUATED', '2026-03-01');
        $this->assertSame('NOT_EVALUATED', $runs->createPromoteRunFromSeed($activated, 'COMPUTE_INDICATORS')->freshness_state, 'an activated date keeps the pending label and its existing rules');

        $boundary = $this->seed(null, self::DATE);
        $this->assertSame('NOT_EVALUATED', $runs->createPromoteRunFromSeed($boundary, 'COMPUTE_INDICATORS')->freshness_state, 'the marker date itself is in force');

        $later = $this->seed('NOT_EVALUATED', '2026-04-01');
        $this->assertSame('NOT_APPLICABLE', $runs->createPromoteRunFromSeed($later, 'COMPUTE_INDICATORS')->freshness_state, 'a date before the marker is never backdated into operational freshness');
    }

    public function test_the_promote_decision_uses_the_seed_marker_and_not_the_wall_clock(): void
    {
        $runs = new EodRunRepository();
        $results = [];
        foreach (['2020-01-01 00:00:00', '2031-01-01 00:00:00'] as $i => $now) {
            Carbon::setTestNow($now);
            $results[] = [
                $runs->createPromoteRunFromSeed($this->seed(null, '2026-04-01'), 'COMPUTE_INDICATORS')->freshness_state,
                $runs->createPromoteRunFromSeed($this->seed(null, '2026-03-24'), 'COMPUTE_INDICATORS')->freshness_state,
            ];
        }
        $this->assertSame([['NOT_APPLICABLE', 'NOT_EVALUATED'], ['NOT_APPLICABLE', 'NOT_EVALUATED']], $results);
    }

    public function test_creating_a_run_never_rewrites_an_existing_run(): void
    {
        $runs = new EodRunRepository();
        $legacy = $this->seed('DEVELOPMENT_NOT_OPERATIONAL', null);
        $before = (array) DB::table('eod_runs')->where('run_id', $legacy->run_id)->first();

        $runs->createPromoteRunFromSeed($legacy, 'COMPUTE_INDICATORS');
        $this->owningRun('2026-03-23', 'other');

        $this->assertSame($before, (array) DB::table('eod_runs')->where('run_id', $legacy->run_id)->first(), 'sealed history is not relabelled');
        $this->assertSame('DEVELOPMENT_NOT_OPERATIONAL', DB::table('eod_runs')->where('run_id', $legacy->run_id)->value('freshness_state'));
    }

    public function test_every_creator_label_is_what_the_domain_rule_says(): void
    {
        $runs = new EodRunRepository();
        foreach ([[null, self::DATE], ['2026-04-01', self::DATE], [self::DATE, self::DATE], ['2026-01-01', self::DATE]] as [$marker, $date]) {
            $this->assertSame(
                FreshnessState::runLabelFor($marker, $date),
                $runs->createPromoteRunFromSeed($this->seed(null, $marker, $date), 'COMPUTE_INDICATORS')->freshness_state,
                var_export($marker, true)
            );
        }
        $this->assertSame(FreshnessState::runLabelFor(null, self::DATE), $this->owningRun(self::DATE, 'grid')->freshness_state);
    }
}
