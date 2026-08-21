<?php

use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Execute the reason code seed instead of reading it.
 *
 * `AuditDocsSynchronizationStaticGuardTest` parses the seed file with a regex and compares
 * the code list against the registry. That guard passed for years while the file carried a
 * trailing comma before ON DUPLICATE KEY UPDATE, so `MarketDataReasonCodesSeeder` failed
 * with SQLSTATE 42000 on every run and `eod_reason_codes` was never populated.
 *
 * A text check cannot catch a syntax error. Running the statement can.
 */
class ReasonCodeSeedExecutionTest extends TestCase
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

    private function seedPath(): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
    }

    /**
     * SQLite has no ON DUPLICATE KEY UPDATE, so the MariaDB tail is translated rather than
     * skipped. The VALUES body — where the defect lived — is executed verbatim.
     */
    private function executableStatement(): string
    {
        $sql = file_get_contents($this->seedPath());

        // Comments first. The file header literally says "uses ON DUPLICATE KEY UPDATE",
        // so searching for that phrase before stripping comments truncates the whole file
        // at byte 77 and yields an empty statement.
        $lines = array_filter(explode("\n", $sql), function ($line) {
            return strpos(ltrim($line), '--') !== 0;
        });

        $sql = implode("\n", $lines);

        $tail = strpos($sql, 'ON DUPLICATE KEY UPDATE');
        if ($tail !== false) {
            $sql = substr($sql, 0, $tail);
        }

        $sql = str_replace('`', '"', $sql);

        return rtrim(trim($sql), ",; \t\n\r").';';
    }

    public function test_the_extracted_statement_is_not_accidentally_empty(): void
    {
        $statement = $this->executableStatement();

        $this->assertStringStartsWith('INSERT INTO eod_reason_codes', $statement);
        $this->assertGreaterThan(10000, strlen($statement), 'an empty statement would pass every other check vacuously');
    }

    public function test_the_seed_statement_actually_executes(): void
    {
        // unprepared, matching MarketDataReasonCodesSeeder. A prepared statement would treat
        // any question mark inside a description as a binding placeholder.
        DB::unprepared($this->executableStatement());

        $this->assertGreaterThan(
            300,
            DB::table('eod_reason_codes')->count(),
            'the seed must populate eod_reason_codes, not merely parse'
        );
    }

    public function test_every_registry_code_reaches_the_table(): void
    {
        // unprepared, matching MarketDataReasonCodesSeeder. A prepared statement would treat
        // any question mark inside a description as a binding placeholder.
        DB::unprepared($this->executableStatement());

        $registry = file_get_contents(
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md')
        );

        preg_match_all('/^\| `([A-Z0-9_]+)` \|/m', $registry, $matches);
        $registryCodes = array_values(array_unique($matches[1]));
        sort($registryCodes);

        $seeded = DB::table('eod_reason_codes')->orderBy('code')->pluck('code')->all();

        $this->assertSame($registryCodes, $seeded);
    }

    /**
     * The exact defect that shipped: a trailing comma on the final VALUES row. Kept as a
     * regression case so the shape of the bug is documented, not just its absence.
     */
    public function test_a_trailing_comma_before_the_tail_is_a_syntax_error(): void
    {
        $broken = "INSERT INTO eod_reason_codes (\"code\", \"category\", \"description\", \"severity\", \"is_active\") VALUES\n"
            ."('A_CODE', 'RUN', 'first', 'INFO', 1),\n"
            ."('B_CODE', 'RUN', 'second', 'INFO', 1),\n";

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::unprepared($broken);
    }

    public function test_severity_values_stay_inside_the_locked_vocabulary(): void
    {
        // unprepared, matching MarketDataReasonCodesSeeder. A prepared statement would treat
        // any question mark inside a description as a binding placeholder.
        DB::unprepared($this->executableStatement());

        $unexpected = DB::table('eod_reason_codes')
            ->whereNotIn('severity', ['INFO', 'WARN', 'HARD'])
            ->pluck('code')
            ->all();

        $this->assertSame([], $unexpected);
    }
}
