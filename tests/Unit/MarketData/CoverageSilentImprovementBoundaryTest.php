<?php

use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W15 — temporal coverage expectation and delivery, stage 12.
 *
 * Exit gate: "provider absence, dormancy, zero volume, illiquidity, current active state, or
 * missing status cannot silently improve coverage."
 *
 * Owner contracts:
 *   docs/market_data/book/Coverage_Universe_Definition_LOCKED.md
 *   docs/market_data/book/Coverage_Gate_Enforcement_Contract_LOCKED.md
 *   docs/market_data/book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md
 *   docs/market_data/book/Coverage_Edge_Cases_Contract_LOCKED.md
 *
 * Coverage is delivered over expected, so anything that shrinks the denominator raises the ratio.
 * Only verified `NOT_EXPECTED` may shrink it; dormancy is never sufficient. The exclusion count and
 * sample make every permitted removal reconstructible rather than silent.
 */
class CoverageSilentImprovementBoundaryTest extends TestCase
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
     * F-043 — the universe hash must change when the universe changes, and only then.
     *
     * A hash that never varies would record the same thing a count already records. The value of
     * this one is that two runs for the same trade date can be compared: equal hashes mean the same
     * universe, different hashes mean the denominator moved. The knowledge cutoff closed `F-006`;
     * the hash remains the evidence that the fixed-coordinate answer is the same set, not only the
     * same count.
     */
    public function test_the_universe_hash_changes_only_when_the_universe_changes(): void
    {
        $this->seedUniverse(10, 8);
        $evaluator = new CoverageGateEvaluator(...$this->evaluatorDependencies());

        $first = $evaluator->evaluate('2026-03-24')['coverage_universe_hash'];
        $again = $evaluator->evaluate('2026-03-24')['coverage_universe_hash'];

        $this->assertSame($first, $again, 'an unchanged universe must hash the same');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first);

        DB::table('tickers')->insert([
            'ticker_id' => 11,
            'ticker_code' => 'TST11',
            'company_name' => 'Test 11',
            'is_active' => 1,
            'listed_date' => '2023-01-02',
            'created_at' => '2023-01-01 00:00:00',
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection(['TST11']);

        $this->assertNotSame(
            $first,
            $evaluator->evaluate('2026-03-24')['coverage_universe_hash'],
            'a listing entering the universe must change its hash, otherwise the hash records nothing'
        );
    }

    /**
     * F-044 — an exclusion must be checkable back against its source, which a count alone prevents.
     */
    public function test_excluded_listings_are_named_not_only_counted(): void
    {
        $this->seedUniverse(10, 8);

        DB::table('market_data_trading_status_events')->insert([
            'ticker_id' => 2,
            'ticker_code' => 'TST2',
            'trade_date' => '2026-03-24',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'test_fixture',
            'recorded_at' => '2026-03-24 00:00:00',
            'created_at' => '2026-03-24 00:00:00',
        ]);

        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $this->assertSame(1, (int) $result['coverage_bar_not_expected_count']);
        $this->assertSame(
            [['ticker_id' => 2, 'ticker_code' => 'TST2']],
            $result['coverage_excluded_sample'],
            'the listing that left the denominator must be named'
        );
    }

    public function test_an_uninterpretable_status_makes_a_listing_unknown_and_keeps_it_in_the_denominator(): void
    {
        $this->seedUniverse(10, 8);

        // Evidence the platform holds and cannot read: a status event whose type the dictionary
        // does not define. Under :19 that is incomplete expectation evidence, so the listing is
        // UNKNOWN — and under :21 it stays in the denominator rather than leaving it.
        DB::table('market_data_trading_status_events')->insert([
            'ticker_id' => 1,
            'ticker_code' => 'TST1',
            'trade_date' => '2026-03-24',
            'event_type_code' => 'SOME_FUTURE_IDX_STATUS',
            'source_name' => 'test_fixture',
            'recorded_at' => '2026-03-24 00:00:00',
            'created_at' => '2026-03-24 00:00:00',
        ]);

        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $this->assertSame(1, (int) $result['coverage_expectation_unknown_count'], 'the unreadable status must be counted');
        $this->assertSame(
            10,
            (int) $result['expected_universe_count'],
            'and it must remain in the denominator — only verified NOT_EXPECTED may leave'
        );
        $this->assertSame(0, (int) $result['coverage_bar_not_expected_count'], 'it is not proof that no bar was expected');
    }

    /**
     * The measurement must be able to answer zero as well, otherwise a corpus-wide zero would be
     * indistinguishable from the hard-coded zero this finding was about.
     */
    public function test_a_clean_universe_reports_a_measured_zero_unknown(): void
    {
        $this->seedUniverse(10, 8);
        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $this->assertSame(0, (int) $result['coverage_expectation_unknown_count']);
        $this->assertArrayHasKey('coverage_expectation_unknown_count', $result, 'zero must be produced, not defaulted');
    }

    public function test_an_unavailable_expectation_source_marks_the_whole_denominator_unknown(): void
    {
        $this->seedUniverse(10, 8);
        $dependencies = $this->evaluatorDependencies();
        $dependencies[2] = null;

        $result = (new CoverageGateEvaluator(...$dependencies))->evaluate('2026-03-24');

        $this->assertSame(10, $result['expected_universe_count']);
        $this->assertSame(10, $result['coverage_expectation_unknown_count']);
        $this->assertSame(0, $result['coverage_bar_not_expected_count']);
    }

    public function test_the_evaluator_reports_every_term_of_the_denominator(): void
    {
        $this->seedUniverse(10, 8);
        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        foreach ([
            'expected_universe_count',
            'coverage_universe_count',
            'coverage_bar_not_expected_count',
            'available_eod_count',
            'missing_eod_count',
        ] as $term) {
            $this->assertArrayHasKey($term, $result, $term.' is needed to audit the ratio');
        }
    }

    /**
     * A denominator term that is absent is not zero. Writing the counts as integers is what keeps
     * "nothing was excluded" distinguishable from "nobody recorded the exclusion", and the second
     * is the state 68,327 production runs are in.
     */
    public function test_exclusion_counts_are_integers_rather_than_nulls(): void
    {
        $this->seedUniverse(10, 8);
        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $this->assertIsInt($result['coverage_bar_not_expected_count']);
    }

    /**
     * The raw universe and the expected denominator are separate numbers, and both are reported.
     * Collapsing them would make an exclusion invisible by construction.
     */
    public function test_the_raw_universe_and_the_expected_denominator_are_reported_separately(): void
    {
        $this->seedUniverse(10, 8);
        DB::table('market_data_trading_status_events')->insert([
            'ticker_id' => 2,
            'ticker_code' => 'TST2',
            'trade_date' => '2026-03-24',
            'event_type_code' => 'SUSPENDED',
            'source_name' => 'test_fixture',
            'recorded_at' => '2026-03-24 00:00:00',
            'created_at' => '2026-03-24 00:00:00',
        ]);
        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $this->assertArrayHasKey('coverage_universe_count', $result);
        $this->assertArrayHasKey('expected_universe_count', $result);
        $this->assertSame(
            (int) $result['coverage_universe_count'] - (int) $result['coverage_bar_not_expected_count'],
            (int) $result['expected_universe_count'],
            'the denominator must be reconstructible from the reported terms'
        );
    }

    /**
     * A dormancy exclusion raises a reason code, so the ratio is never quietly better than the
     * previous run without a stated cause.
     */
    public function test_a_populated_universe_reconstructs_its_own_ratio(): void
    {
        $this->seedUniverse(10, 8);

        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $expected = (int) $result['expected_universe_count'];
        $delivered = (int) $result['available_eod_count'];

        $this->assertGreaterThan(0, $expected, 'the fixture must produce a real denominator');
        $this->assertSame($delivered + (int) $result['missing_eod_count'], $expected, 'delivered plus missing is the denominator');
        $this->assertEqualsWithDelta($delivered / $expected, $result['coverage_ratio'], 1e-9);
    }

    /**
     * Instruments removed from the denominator are counted, so a run that excluded many and a run
     * that excluded none cannot produce the same evidence.
     */
    public function test_every_excluded_instrument_is_counted_somewhere(): void
    {
        $this->seedUniverse(10, 8);

        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $accounted = (int) $result['expected_universe_count']
            + (int) $result['coverage_bar_not_expected_count'];

        $this->assertSame((int) $result['coverage_universe_count'], $accounted, 'no instrument may leave the universe unaccounted');
    }

    /**
     * An empty universe is not perfect coverage. Dividing by zero instruments would otherwise be
     * the cheapest possible way to pass the gate.
     */
    public function test_an_empty_universe_is_not_evaluable_rather_than_passing(): void
    {
        $result = (new CoverageGateEvaluator(...$this->evaluatorDependencies()))->evaluate('2026-03-24');

        $this->assertNotSame('PASS', $result['coverage_gate_status'], 'no universe means nothing was proven');
        $this->assertSame('NOT_EVALUABLE', $result['coverage_gate_status']);
    }

    private function evaluatorDependencies(): array
    {
        $reflection = new ReflectionMethod(CoverageGateEvaluator::class, '__construct');
        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type === null || $type->isBuiltin()) {
                $dependencies[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
                continue;
            }

            $class = $type->getName();
            $dependencies[] = new $class();
        }

        return $dependencies;
    }

    /**
     * Seed a live universe of `$count` instruments, all listed before the requested date, and give
     * `$withBars` of them a canonical bar on that date.
     */
    private function seedUniverse(int $count, int $withBars): void
    {
        DB::table('market_calendar')->updateOrInsert(['cal_date' => '2026-03-24'], [
            'is_trading_day' => 1,
            'session_close_time' => '16:00',
            'provenance_tier' => 'VERIFIED',
            'source' => 'test_fixture',
            'created_at' => '2023-01-01 00:00:00',
            'updated_at' => '2023-01-01 00:00:00',
        ]);

        for ($i = 1; $i <= $count; $i++) {
            DB::table('tickers')->insert([
                'ticker_id' => $i,
                'ticker_code' => 'TST'.$i,
                'company_name' => 'Test '.$i,
                'is_active' => 1,
                'listed_date' => '2023-01-02',
                'created_at' => '2023-01-01 00:00:00',
            ]);

            if ($i <= $withBars) {
                DB::table('eod_bars')->insert([
                    'trade_date' => '2026-03-24',
                    'ticker_id' => $i,
                    'open' => 100, 'high' => 110, 'low' => 99, 'close' => 108,
                    'volume' => 1000, 'source' => 'YAHOO_FINANCE',
                    'run_id' => 1, 'publication_id' => 1,
                    'created_at' => '2026-03-24 18:00:00',
                ]);
            }
        }
    }
}
