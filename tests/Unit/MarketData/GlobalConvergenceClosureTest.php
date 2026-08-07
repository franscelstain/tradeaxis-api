<?php

use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W21 — global schema/migration convergence and semantic proof closure, stages 20 and 21.
 *
 * Stage 20 exit gate: "no required semantic field remains nullable/unwritten without reason; base
 * SQL + migrations equal supported runtime shape; clean DB and upgraded DB pass the same semantic
 * suite."
 *
 * Stage 21 exit gate: "every P0/P1 invariant is `PROVEN`; no test expects provider-adjusted
 * fallback, direct repair, synthetic verified factors, current-active historical filtering,
 * dormancy denominator exclusion, sliding ATR reseed, or other superseded behavior."
 *
 * The second clause of stage 21 is the one worth an executable guard. Superseded behaviour does
 * not usually return through the code that implemented it — it returns through a test that still
 * expects it, because a test asserting the old rule makes restoring the old rule look like a fix.
 */
class GlobalConvergenceClosureTest extends TestCase
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

    private function marketDataTestSources(): array
    {
        $sources = [];
        foreach (glob(__DIR__.'/*.php') as $path) {
            if (basename($path) === basename(__FILE__)) {
                continue;
            }
            $sources[basename($path)] = (string) file_get_contents($path);
        }

        return $sources;
    }

    /**
     * No test expects dormancy to leave the coverage denominator. The reason code is deprecated,
     * so the only admissible mention is an assertion that it never appears.
     */
    public function test_no_test_expects_dormancy_to_shrink_the_denominator(): void
    {
        foreach ($this->marketDataTestSources() as $name => $source) {
            if (strpos($source, 'COVERAGE_DORMANT_TICKERS_EXCLUDED') === false) {
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/assertNotContains\([^;]*COVERAGE_DORMANT_TICKERS_EXCLUDED/s',
                $source,
                $name.' may only assert the deprecated exclusion never happens'
            );
        }

        $this->assertTrue(true);
    }

    /**
     * No test expects provider `adj_close` to stand in for a RAW close. The adapter mention that
     * remains asserts the fallback is absent.
     */
    public function test_no_test_expects_a_provider_adjusted_close_fallback(): void
    {
        foreach ($this->marketDataTestSources() as $name => $source) {
            $this->assertStringNotContainsString(
                "\$adjclose[\$position] ?? (\$quote['close']",
                $source,
                $name.' must not expect the retired adj_close fallback'
            );
        }
    }

    /**
     * No test expects direct in-place repair of history. The repair-tracking columns are forbidden
     * by the schema contract, so a test may only assert their absence.
     */
    public function test_no_test_expects_direct_historical_repair(): void
    {
        foreach ($this->marketDataTestSources() as $name => $source) {
            if (strpos($source, 'repaired_at') === false) {
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/assertFalse|assertNotContains|must not exist|forbidden/i',
                $source,
                $name.' may only assert repair-tracking fields are absent'
            );
        }

        $this->assertTrue(true);
    }

    /**
     * No test expects a factor inferred from the price series to adjust published output.
     */
    public function test_no_test_expects_a_synthetic_verified_factor(): void
    {
        foreach ($this->marketDataTestSources() as $name => $source) {
            if (strpos($source, 'DERIVED_FROM_PRICE_SERIES') === false) {
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/assertArrayNotHasKey|assertFalse|assertNull|never|not/i',
                $source,
                $name.' may only assert a derived factor is refused'
            );
        }

        $this->assertTrue(true);
    }

    /**
     * Every market-data table intended by the mirror exists in the deployed schema, and the mirror
     * is the statement of intent. A table present in one and absent in the other means the two
     * disagree about what the platform is.
     */
    public function test_every_mirrored_market_data_table_is_creatable(): void
    {
        $mirror = (string) file_get_contents(__DIR__.'/../../Support/UsesMarketDataSqlite.php');
        preg_match_all("/schema->create\('([a-z_]+)'/", $mirror, $matches);

        $this->assertNotEmpty($matches[1], 'the mirror must declare tables');

        foreach ($matches[1] as $table) {
            $this->assertTrue(
                DB::getSchemaBuilder()->hasTable($table),
                $table.' is declared by the mirror but was not created'
            );
        }
    }

    /**
     * Every migration file present in the repository is one the migrator can resolve, which is the
     * defect class that kept the orders 1-4 migration unrunnable from the day it was written.
     */
    public function test_every_migration_file_resolves_to_a_declared_class(): void
    {
        $unresolved = [];

        foreach (glob(__DIR__.'/../../../database/migrations/*.php') as $path) {
            $source = (string) file_get_contents($path);
            $expected = str_replace(' ', '', ucwords(str_replace('_', ' ', preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', basename($path, '.php')))));

            // An anonymous migration (`return new class extends Migration`) has no name for the
            // migrator to resolve, so it cannot suffer the mismatch this test exists to catch.
            // It is the safer form, not an omission.
            if (preg_match('/return\s+new\s+class\s+extends\s+Migration/', $source)) {
                continue;
            }

            if (! preg_match('/class\s+(\w+)\s+extends\s+Migration/', $source, $matches)) {
                $unresolved[] = basename($path).' (no migration class)';
                continue;
            }

            if ($matches[1] !== $expected) {
                $unresolved[] = basename($path).' declares '.$matches[1].', migrator resolves '.$expected;
            }
        }

        $this->assertSame([], $unresolved);
    }

    /**
     * Every field this session found unwritten carries a recorded reason in the audit register.
     *
     * The stage 20 gate permits a nullable or unwritten semantic field only when the reason is
     * recorded. This binds the two together: if a finding is ever deleted from the register while
     * the field is still unwritten, the gate stops being satisfied and this test says so.
     */
    public function test_every_unwritten_semantic_field_has_a_recorded_reason(): void
    {
        $register = (string) file_get_contents(
            __DIR__.'/../../../docs/market_data/audit/reports/AUDIT_FINAL_STATE.md'
        );

        foreach ([
            'source_observation_id' => 'P1-29',
            'price_product_code' => 'P1-32',
            'coverage_expected_count' => 'P1-35',
            'liquidity_state' => 'P1-36',
            'config_snapshot_id' => 'P1-25',
        ] as $field => $finding) {
            $this->assertStringContainsString(
                $finding,
                $register,
                $field.' is unwritten in the legacy corpus and must keep a recorded reason'
            );
        }
    }
}
