<?php

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for the sealed-dataset mutation guard.
 *
 * `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest` asserted that the string
 * "assertLiveArtifactMutationAllowed" appears in the repository source. That proves a method
 * is named, not that a sealed dataset is actually protected, and until now nothing exercised
 * the guard at all.
 *
 * The invariant matters: once a publication is sealed and readable, its rows are the audit
 * baseline. Changing them silently would break every hash and replay proof that references
 * them, which is why the correction and reseal flow exists.
 */
class SealedArtifactMutationGuardTest extends TestCase
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

    private function seedPublication(string $sealState, int $isCurrent, string $terminalStatus = 'SUCCESS', string $publishability = 'READABLE'): void
    {
        DB::table('eod_runs')->insert([
            'run_id' => 25,
            'trade_date_requested' => '2026-03-20',
            'trade_date_effective' => '2026-03-20',
            'lifecycle_state' => 'COMPLETED',
            'quality_gate_state' => 'PASS',
            'stage' => 'FINALIZE',
            'source' => 'manual_file',
            'publication_id' => 10,
            'publication_version' => 1,
            'terminal_status' => $terminalStatus,
            'publishability_state' => $publishability,
            'coverage_gate_state' => 'PASS',
            'is_current_publication' => $isCurrent,
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => '2026-03-20',
            'run_id' => 25,
            'publication_version' => 1,
            'is_current' => $isCurrent,
            'seal_state' => $sealState,
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_indicators')->insert([
            'trade_date' => '2026-03-20',
            'ticker_id' => 1,
            'is_valid' => 1,
            'indicator_set_version' => 'v1',
            'run_id' => 25,
            'publication_id' => 10,
            'created_at' => '2026-03-20 17:20:00',
        ]);
    }

    private function replaceIndicatorsForAnotherPublication(): void
    {
        (new EodArtifactRepository())->replaceIndicators('2026-03-20', 99, [[
            'trade_date' => '2026-03-20',
            'ticker_id' => 2,
            'is_valid' => 1,
            'indicator_set_version' => 'v1',
            'run_id' => 99,
            'publication_id' => 77,
            'created_at' => '2026-03-21 10:00:00',
        ]], 77, false);
    }

    public function test_mutating_a_sealed_current_publication_is_blocked(): void
    {
        $this->seedPublication('SEALED', 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/SEALED_DATASET_MUTATION_BLOCKED/');

        $this->replaceIndicatorsForAnotherPublication();
    }

    /**
     * Sealed and readable is enough on its own. A publication that has been superseded as
     * current is still an audit baseline that hashes and replays point at.
     */
    public function test_mutating_a_sealed_readable_but_no_longer_current_publication_is_blocked(): void
    {
        $this->seedPublication('SEALED', 0);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/SEALED_DATASET_MUTATION_BLOCKED/');

        $this->replaceIndicatorsForAnotherPublication();
    }

    /** An unsealed candidate is still being built, so replacing it is legitimate. */
    public function test_mutating_an_unsealed_publication_is_allowed(): void
    {
        $this->seedPublication('UNSEALED', 1);

        $this->replaceIndicatorsForAnotherPublication();

        $this->assertSame(
            1,
            DB::table('eod_indicators')->where('publication_id', 77)->count(),
            'an unsealed dataset must remain replaceable'
        );
    }

    /** A sealed run that never became readable is not an audit baseline. */
    public function test_mutating_a_sealed_but_never_readable_publication_is_allowed(): void
    {
        $this->seedPublication('SEALED', 0, 'HELD', 'NOT_READABLE');

        $this->replaceIndicatorsForAnotherPublication();

        $this->assertSame(1, DB::table('eod_indicators')->where('publication_id', 77)->count());
    }

    /**
     * Rewriting the same publication is how a correction reseals its own dataset, so the
     * guard must not stand in its way.
     */
    public function test_rewriting_the_same_publication_is_allowed(): void
    {
        $this->seedPublication('SEALED', 1);

        (new EodArtifactRepository())->replaceIndicators('2026-03-20', 25, [[
            'trade_date' => '2026-03-20',
            'ticker_id' => 1,
            'is_valid' => 0,
            'indicator_set_version' => 'v1',
            'run_id' => 25,
            'publication_id' => 10,
            'created_at' => '2026-03-21 10:00:00',
        ]], 10, false);

        $this->assertSame(
            0,
            (int) DB::table('eod_indicators')->where('publication_id', 10)->value('is_valid')
        );
    }
}
