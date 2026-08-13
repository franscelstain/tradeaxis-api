<?php

use App\Console\Commands\MarketData\RepairCurrentPublicationIntegrityCommand;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for the current-pointer integrity scan.
 *
 * `PublicationCurrentPointerReadinessStaticGuardTest` asserted that three reason-code strings
 * appear inside determineCurrentIntegrityViolationReasons(). That proves the strings exist, not
 * that a broken pointer produces them.
 *
 * The scan matters more than most invariants because it is the detect half of
 * market-data:current-publication:repair, and that command is detect-only by default: without
 * --apply it reports what it found and changes nothing. So the scan is the only thing standing
 * between a silently unreadable trading day and an operator who believes the platform is fine.
 *
 * Two properties are proven here:
 *
 *   1. Complementarity. Every state that the consumer read path refuses to resolve must be
 *      flagged by the scan. A state that fails one and passes the other is a date that serves
 *      nothing to consumers while every diagnostic reports health.
 *
 *   2. Diagnosability. Every flagged state must name at least one reason in the operator-facing
 *      output, because the command tells the operator to review integrity_reasons before
 *      authorising a destructive clear.
 */
class CurrentPointerIntegrityScanTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->seedValidCurrentPublication();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedValidCurrentPublication(): void
    {
        $factorSetHash = hash('sha256', 'pointer-integrity|'.self::TRADE_DATE.'|25|10');

        DB::table('eod_runs')->insert([
            'run_id' => 25,
            'trade_date_requested' => self::TRADE_DATE,
            'trade_date_effective' => self::TRADE_DATE,
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
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'is_current_publication' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'started_at' => '2026-03-20 17:00:00',
            'created_at' => '2026-03-20 17:00:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => self::TRADE_DATE,
            'run_id' => 25,
            'publication_version' => 1,
            'is_current' => 1,
            'seal_state' => 'SEALED',
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => $factorSetHash,
            'sealed_at' => '2026-03-20 17:20:00',
            'created_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);

        DB::table('eod_current_publication_pointer')->insert([
            'trade_date' => self::TRADE_DATE,
            'publication_id' => 10,
            'run_id' => 25,
            'publication_version' => 1,
            'sealed_at' => '2026-03-20 17:20:00',
            'updated_at' => '2026-03-20 17:20:00',
        ]);
    }

    private function repository(): EodPublicationRepository
    {
        return new EodPublicationRepository();
    }

    private function applyBreakage(array $case): void
    {
        $query = DB::table($case['table']);

        if (! empty($case['delete'])) {
            $query->delete();

            return;
        }

        $query->update($case['update']);
    }

    /**
     * Runs the real repair command without --apply and returns its output.
     */
    private function runRepairCommandDryRun(): string
    {
        $command = new RepairCurrentPublicationIntegrityCommand();
        $command->setLaravel($this->app);

        $tester = new CommandTester($command);
        $tester->execute(['--trade_date' => self::TRADE_DATE]);

        return $tester->getDisplay();
    }

    private function reportedReasons(string $display): array
    {
        if (! preg_match('/integrity_reasons=(.*)/', $display, $matches)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', trim($matches[1])))));
    }

    public function test_a_healthy_current_pointer_is_not_flagged(): void
    {
        $this->assertNotNull(
            $this->repository()->resolveCurrentReadablePublicationForTradeDate(self::TRADE_DATE)
        );

        $this->assertCount(0, $this->repository()->findInvalidCurrentPublicationStates(self::TRADE_DATE));
        $this->assertStringContainsString('status=OK', $this->runRepairCommandDryRun());
    }

    public function test_an_indicator_with_a_different_analytical_identity_invalidates_the_publication(): void
    {
        DB::table('eod_indicators')->insert([
            'trade_date' => self::TRADE_DATE,
            'ticker_id' => 1,
            'is_valid' => 1,
            'indicator_set_version' => 'v1',
            'price_product_code' => 'RAW',
            'price_product_version' => 'structural_adjusted_v1',
            'factor_set_hash' => hash('sha256', 'pointer-integrity|'.self::TRADE_DATE.'|25|10'),
            'run_id' => 25,
            'publication_id' => 10,
            'created_at' => '2026-03-20 17:20:00',
        ]);

        $this->assertNull($this->repository()->resolveCurrentReadablePublicationForTradeDate(self::TRADE_DATE));
        $this->assertContains(
            'ANALYTICAL_ROW_IDENTITY_MISMATCH',
            $this->reportedReasons($this->runRepairCommandDryRun())
        );
    }

    /**
     * The invariant that matters most: a date the consumer cannot read must never look healthy
     * to the operator.
     *
     * @dataProvider brokenPointerStates
     */
    public function test_states_the_consumer_cannot_read_are_flagged_by_the_scan(array $case, string $why): void
    {
        $this->applyBreakage($case);

        $this->assertNull(
            $this->repository()->resolveCurrentReadablePublicationForTradeDate(self::TRADE_DATE),
            'consumer read must reject: '.$why
        );

        $this->assertCount(
            1,
            $this->repository()->findInvalidCurrentPublicationStates(self::TRADE_DATE),
            'integrity scan must flag: '.$why
        );
    }

    /**
     * A flagged state with no reason leaves the operator holding a destructive command and no
     * diagnosis. The command itself instructs them to review integrity_reasons first.
     *
     * @dataProvider brokenPointerStates
     */
    public function test_the_repair_command_names_a_reason_for_every_state_it_flags(array $case, string $why): void
    {
        $this->applyBreakage($case);

        $display = $this->runRepairCommandDryRun();

        $this->assertStringContainsString('status=INVALID_CURRENT_PUBLICATION', $display);
        $this->assertNotEmpty(
            $this->reportedReasons($display),
            'repair command reported no integrity reason for: '.$why
        );
    }

    /**
     * Where the broken state maps to one specific reason, the operator should see that reason
     * and not merely a non-empty list.
     *
     * @dataProvider brokenPointerStates
     */
    public function test_the_repair_command_names_the_specific_reason(array $case, string $why): void
    {
        if (! isset($case['reason'])) {
            $this->assertTrue(true, 'Covered by its own test: '.$why);

            return;
        }

        $this->applyBreakage($case);

        $this->assertContains(
            $case['reason'],
            $this->reportedReasons($this->runRepairCommandDryRun()),
            'expected reason missing for: '.$why
        );
    }

    /**
     * A deleted run is described symptomatically rather than by RUN_ROW_MISSING, because the
     * scan reads run_id from the publication row, and eod_publications.run_id is NOT NULL. The
     * state is still fully diagnosed, so this is a naming gap and not a detection gap — pinned
     * here so that a future change to the label is a deliberate one.
     */
    public function test_a_deleted_run_is_diagnosed_through_its_symptoms(): void
    {
        DB::table('eod_runs')->delete();

        $reasons = $this->reportedReasons($this->runRepairCommandDryRun());

        $this->assertContains('RUN_SEALED_AT_MISSING', $reasons);
        $this->assertContains('RUN_TERMINAL_STATUS_NOT_SUCCESS', $reasons);
        $this->assertContains('RUN_PUBLISHABILITY_NOT_READABLE', $reasons);
        $this->assertContains('RUN_CURRENT_MIRROR_NOT_SET', $reasons);
        $this->assertContains('RUN_PUBLICATION_ID_MISMATCH', $reasons);
        $this->assertNotContains('RUN_ROW_MISSING', $reasons);
    }

    public function brokenPointerStates(): array
    {
        return [
            'publication row deleted' => [
                ['table' => 'eod_publications', 'delete' => true, 'reason' => 'PUBLICATION_ROW_MISSING'],
                'the pointer references a publication row that no longer exists',
            ],
            'publication trade date mismatch' => [
                ['table' => 'eod_publications', 'update' => ['trade_date' => '2026-03-19'], 'reason' => 'PUBLICATION_TRADE_DATE_MISMATCH'],
                'the publication belongs to a different trading day than the pointer',
            ],
            'publication not marked current' => [
                ['table' => 'eod_publications', 'update' => ['is_current' => 0], 'reason' => 'PUBLICATION_NOT_MARKED_CURRENT'],
                'the publication no longer claims to be current',
            ],
            'publication not sealed' => [
                ['table' => 'eod_publications', 'update' => ['seal_state' => 'UNSEALED'], 'reason' => 'PUBLICATION_NOT_SEALED'],
                'the publication is mutable',
            ],
            'publication seal timestamp missing' => [
                ['table' => 'eod_publications', 'update' => ['sealed_at' => null], 'reason' => 'PUBLICATION_SEALED_AT_MISSING'],
                'the publication carries no proof of when it was sealed',
            ],
            'publication analytical identity missing' => [
                ['table' => 'eod_publications', 'update' => ['factor_set_hash' => null], 'reason' => 'PUBLICATION_ANALYTICAL_IDENTITY_INVALID'],
                'the publication does not identify the factor set used by its analytical product',
            ],
            'pointer seal timestamp missing' => [
                ['table' => 'eod_current_publication_pointer', 'update' => ['sealed_at' => null], 'reason' => 'POINTER_SEALED_AT_MISSING'],
                'the pointer carries no proof of when it was sealed',
            ],
            'pointer run mismatch' => [
                ['table' => 'eod_current_publication_pointer', 'update' => ['run_id' => 99], 'reason' => 'POINTER_RUN_ID_MISMATCH'],
                'the pointer names a different run than the publication it points at',
            ],
            'pointer version mismatch' => [
                ['table' => 'eod_current_publication_pointer', 'update' => ['publication_version' => 9], 'reason' => 'POINTER_PUBLICATION_VERSION_MISMATCH'],
                'the pointer names a different version than the publication it points at',
            ],
            'run row deleted' => [
                ['table' => 'eod_runs', 'delete' => true],
                'the run that produced the publication no longer exists',
            ],
            'run seal timestamp missing' => [
                ['table' => 'eod_runs', 'update' => ['sealed_at' => null], 'reason' => 'RUN_SEALED_AT_MISSING'],
                'the run carries no proof of when it was sealed',
            ],
            'run did not succeed' => [
                ['table' => 'eod_runs', 'update' => ['terminal_status' => 'HELD'], 'reason' => 'RUN_TERMINAL_STATUS_NOT_SUCCESS'],
                'the run behind the current publication did not succeed',
            ],
            'run not readable' => [
                ['table' => 'eod_runs', 'update' => ['publishability_state' => 'NOT_READABLE'], 'reason' => 'RUN_PUBLISHABILITY_NOT_READABLE'],
                'the run behind the current publication is not readable',
            ],
            'run current mirror not set' => [
                ['table' => 'eod_runs', 'update' => ['is_current_publication' => 0], 'reason' => 'RUN_CURRENT_MIRROR_NOT_SET'],
                'the run does not mirror the pointer that names it',
            ],
            'run coverage gate not pass' => [
                ['table' => 'eod_runs', 'update' => ['coverage_gate_state' => 'FAIL'], 'reason' => 'RUN_COVERAGE_GATE_NOT_PASS'],
                'the publication was built from a run that failed the coverage gate',
            ],
            // Each coverage column is listed separately because the read path guards them with
            // eight individual whereNotNull clauses, written out in four places. Dropping any
            // one of them would let a publication become readable while unable to prove part of
            // the coverage it claims, and a single representative case would not notice.
            'run coverage ratio missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_ratio' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the run cannot prove the ratio it claims',
            ],
            'run coverage universe count missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_universe_count' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the run does not say how many tickers it expected',
            ],
            'run coverage available count missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_available_count' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the run does not say how many tickers it actually got',
            ],
            'run coverage missing count missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_missing_count' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the run does not say how many tickers it lost',
            ],
            'run coverage threshold missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_min_threshold' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the run declares no bar to clear',
            ],
            'run coverage threshold mode missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_threshold_mode' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the threshold the run cleared cannot be interpreted',
            ],
            'run coverage universe basis missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_universe_basis' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the universe the run measured cannot be reconstructed later',
            ],
            'run coverage contract version missing' => [
                ['table' => 'eod_runs', 'update' => ['coverage_contract_version' => null], 'reason' => 'RUN_COVERAGE_TELEMETRY_INVALID'],
                'the rules the run was judged by are unknown',
            ],
            'run publication mirror id mismatch' => [
                ['table' => 'eod_runs', 'update' => ['publication_id' => 99], 'reason' => 'RUN_PUBLICATION_ID_MISMATCH'],
                'the run points at a different publication than the pointer does',
            ],
            'run publication mirror version mismatch' => [
                ['table' => 'eod_runs', 'update' => ['publication_version' => 9], 'reason' => 'RUN_PUBLICATION_VERSION_MISMATCH'],
                'the run mirrors a different publication version than the pointer does',
            ],
            'run analytical identity mismatch' => [
                ['table' => 'eod_runs', 'update' => ['factor_set_hash' => str_repeat('f', 64)], 'reason' => 'RUN_ANALYTICAL_IDENTITY_MISMATCH'],
                'the run and publication name different analytical factor sets',
            ],
        ];
    }
}
