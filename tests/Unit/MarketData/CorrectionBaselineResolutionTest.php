<?php

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for the correction baseline, and for the fact that it must agree with
 * the consumer read path.
 *
 * `findCorrectionBaselinePublicationForTradeDate` is a verbatim copy of
 * `resolveCurrentReadablePublicationForTradeDate`: the same twenty-five conditions, written
 * out twice. PublicationRepositoryIntegrationTest exercises the second one thoroughly, but
 * that coverage does not reach the first, so a condition fixed in one and missed in the other
 * would let a correction rebase itself on a publication no consumer is allowed to read.
 *
 * These tests drive both methods over the same fixtures and assert they never disagree, which
 * keeps the duplication safe until it is removed.
 */
class CorrectionBaselineResolutionTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedReadablePublication();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedReadablePublication(): void
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
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_universe_count' => 2,
            'coverage_available_count' => 2,
            'coverage_missing_count' => 0,
            'coverage_ratio' => '1.000000',
            'coverage_min_threshold' => '0.980000',
            'coverage_threshold_mode' => 'MIN_RATIO',
            'coverage_universe_basis' => 'ACTIVE_TICKER_MASTER_FOR_TRADE_DATE',
            'coverage_contract_version' => 'coverage_gate_v1',
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => '2026-03-20',
            'run_id' => 25,
            'publication_version' => 1,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => '2026-03-20',
            'publication_id' => 10,
            'run_id' => 25,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
    }

    private function baseline()
    {
        return (new EodPublicationRepository())->findCorrectionBaselinePublicationForTradeDate('2026-03-20');
    }

    private function consumerRead()
    {
        return (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate('2026-03-20');
    }

    public function test_a_valid_readable_publication_is_accepted_as_correction_baseline(): void
    {
        $baseline = $this->baseline();

        $this->assertNotNull($baseline);
        $this->assertSame(10, (int) $baseline->publication_id);
    }

    /**
     * Every rejection case is asserted against both methods. A divergence here is the defect
     * the duplication invites.
     *
     * @dataProvider rejectionCases
     */
    public function test_correction_baseline_and_consumer_read_reject_the_same_states(string $table, array $update, string $why): void
    {
        DB::table($table)->update($update);

        $this->assertNull($this->baseline(), 'correction baseline must reject: '.$why);
        $this->assertNull($this->consumerRead(), 'consumer read must reject: '.$why);
    }

    public function rejectionCases(): array
    {
        return [
            'run not readable' => ['eod_runs', ['publishability_state' => 'NOT_READABLE'], 'run is not readable'],
            'run not successful' => ['eod_runs', ['terminal_status' => 'HELD'], 'run did not succeed'],
            'coverage gate failed' => ['eod_runs', ['coverage_gate_state' => 'FAIL'], 'coverage gate did not pass'],
            'run seal missing' => ['eod_runs', ['sealed_at' => null], 'run carries no seal proof'],
            'run mirror mismatch' => ['eod_runs', ['publication_id' => 99], 'run publication mirror disagrees with the pointer'],
            'run not current' => ['eod_runs', ['is_current_publication' => 0], 'run is not the current publication'],
            'coverage telemetry incomplete' => ['eod_runs', ['coverage_ratio' => null], 'coverage context is only partly recorded'],
            'publication unsealed' => ['eod_publications', ['seal_state' => 'UNSEALED'], 'publication is not sealed'],
            'publication superseded' => ['eod_publications', ['is_current' => 0], 'publication is no longer current'],
            'publication seal missing' => ['eod_publications', ['sealed_at' => null], 'publication carries no seal timestamp'],
            'pointer seal missing' => ['eod_current_publication_pointer', ['sealed_at' => null], 'pointer carries no seal timestamp'],
            'pointer version mismatch' => ['eod_current_publication_pointer', ['publication_version' => 9], 'pointer version disagrees with the publication'],
            'pointer run mismatch' => ['eod_current_publication_pointer', ['run_id' => 99], 'pointer run disagrees with the publication run'],
        ];
    }

    /**
     * There are four public entry points that answer "which publication may be read for this
     * date": the consumer gateway, two aliases that exist for caller readability, and the
     * correction baseline. Callers pick between them by name, so they must be
     * indistinguishable in behaviour — an alias that drifted into its own implementation would
     * be a bypass that reads like the real thing at every call site.
     *
     * @dataProvider rejectionCases
     */
    public function test_every_publication_entry_point_agrees_on_rejection(string $table, array $update, string $why): void
    {
        DB::table($table)->update($update);

        $repository = new EodPublicationRepository();

        foreach ([
            'resolveCurrentReadablePublicationForTradeDate',
            'findCurrentPublicationForTradeDate',
            'findPointerResolvedPublicationForTradeDate',
            'findCorrectionBaselinePublicationForTradeDate',
        ] as $method) {
            $this->assertNull(
                $repository->{$method}('2026-03-20'),
                $method.' must reject: '.$why
            );
        }
    }

    public function test_every_publication_entry_point_agrees_on_acceptance(): void
    {
        $repository = new EodPublicationRepository();

        foreach ([
            'resolveCurrentReadablePublicationForTradeDate',
            'findCurrentPublicationForTradeDate',
            'findPointerResolvedPublicationForTradeDate',
            'findCorrectionBaselinePublicationForTradeDate',
        ] as $method) {
            $resolved = $repository->{$method}('2026-03-20');

            $this->assertNotNull($resolved, $method.' must accept a valid readable publication');
            $this->assertSame(10, (int) $resolved->publication_id, $method.' resolved a different publication');
        }
    }

    /**
     * Correcting a date that was never published has no baseline to rebase on, and must not
     * silently fall back to a neighbouring date.
     */
    public function test_an_unpublished_trade_date_has_no_correction_baseline(): void
    {
        $this->assertNull(
            (new EodPublicationRepository())->findCorrectionBaselinePublicationForTradeDate('2026-03-19')
        );
    }
}
