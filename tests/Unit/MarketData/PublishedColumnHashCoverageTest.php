<?php

use App\Application\MarketData\Services\MarketDataPipelineService;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Every published column must be covered by its artifact's content hash.
 *
 * `HashSealDatasetIntegrityStaticGuardTest` checked that the hashing service mentions its config
 * keys and sorts its lines. Both were true, and neither says anything about which columns the
 * hash is computed over — which is where the damage actually happens.
 *
 * A column that is published but not hashed makes two publications differing in that column
 * produce the same hash. The correction flow compares hashes to decide whether anything changed,
 * so it declares UNCHANGED, discards the candidate, and the corrected rows are never promoted.
 * The dataset stays wrong and every integrity check reports success.
 *
 * That is not a hypothetical failure mode. It happened in this codebase to
 * corporate_action_window_reasons, and nothing caught it.
 *
 * These tests derive both sides — the live table definition and the hash column list — and
 * compare them, so a column added tomorrow cannot quietly fall outside its hash.
 */
class PublishedColumnHashCoverageTest extends TestCase
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
     * @return string[] the columns of a table that carry published content
     */
    private function contentColumns(string $table): array
    {
        $columns = array_values(array_diff(
            Schema::connection($this->marketDataSqliteConnection)->getColumnListing($table),
            MarketDataPipelineService::HASH_EXCLUDED_BOOKKEEPING_COLUMNS
        ));

        sort($columns);

        return $columns;
    }

    private function sorted(array $columns): array
    {
        sort($columns);

        return $columns;
    }

    public function artifacts(): array
    {
        return [
            'bars' => ['eod_bars', 'eod_bars_history', MarketDataPipelineService::BARS_HASH_COLUMNS],
            'indicators' => ['eod_indicators', 'eod_indicators_history', MarketDataPipelineService::INDICATORS_HASH_COLUMNS],
            'eligibility' => ['eod_eligibility', 'eod_eligibility_history', MarketDataPipelineService::ELIGIBILITY_HASH_COLUMNS],
        ];
    }

    /**
     * @dataProvider artifacts
     */
    public function test_the_hash_covers_every_published_column_of_the_current_table(string $current, string $history, array $hashColumns): void
    {
        $this->assertSame(
            $this->contentColumns($current),
            $this->sorted($hashColumns),
            $current.': every published column must be part of the content hash, and the hash must '
            .'not name a column that is not published. A column present here but missing from the '
            .'hash makes a correction that changes only that column look like no change at all.'
        );
    }

    /**
     * Candidates are hashed from the history table and live publications from the current table.
     * If the two disagree on their columns, the same content hashes differently depending on
     * which path produced it, and a correct republish looks like a change.
     *
     * @dataProvider artifacts
     */
    public function test_the_current_and_history_tables_agree_on_their_published_columns(string $current, string $history, array $hashColumns): void
    {
        $this->assertSame(
            $this->contentColumns($current),
            $this->contentColumns($history),
            $current.' and '.$history.' must carry the same published columns.'
        );
    }

    /**
     * @return string[] the columns the documented MariaDB DDL declares for a table
     */
    private function documentedMariaDbColumns(string $table): array
    {
        $schema = file_get_contents(
            dirname(__DIR__, 3).DIRECTORY_SEPARATOR
            .str_replace('/', DIRECTORY_SEPARATOR, 'docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql')
        );

        $start = strpos($schema, 'CREATE TABLE IF NOT EXISTS '.$table.' (');
        $this->assertNotFalse($start, 'No DDL found for '.$table);

        $body = substr($schema, $start, strpos($schema, 'ENGINE=InnoDB', $start) - $start);

        $columns = [];

        foreach (explode("\n", $body) as $line) {
            $line = trim($line);

            // Column lines start with the name; key and constraint lines start with a keyword.
            if (preg_match('/^([a-z_][a-z0-9_]*)\s+[A-Z]/', $line, $matches)
                && ! in_array(strtoupper($matches[1]), ['PRIMARY', 'KEY', 'UNIQUE', 'CONSTRAINT', 'FOREIGN', 'CREATE', 'INDEX'], true)) {
                $columns[] = $matches[1];
            }
        }

        $columns = array_values(array_diff($columns, MarketDataPipelineService::HASH_EXCLUDED_BOOKKEEPING_COLUMNS));
        sort($columns);

        return $columns;
    }

    /**
     * The check above reads the SQLite mirror the tests run on. That is only meaningful if the
     * mirror still reflects the MariaDB schema the platform actually publishes from — otherwise
     * the hash could cover every column of a table shape that exists nowhere in production.
     *
     * @dataProvider artifacts
     */
    public function test_the_sqlite_mirror_matches_the_documented_production_schema(string $current, string $history, array $hashColumns): void
    {
        $this->assertSame(
            $this->documentedMariaDbColumns($current),
            $this->contentColumns($current),
            $current.': the SQLite test mirror has drifted from the documented MariaDB schema, so '
            .'the hash coverage proven here would not describe production.'
        );
    }

    /**
     * Guards the guard: an empty or near-empty constant would satisfy nothing while making the
     * comparison above pass against a table that had also lost its columns.
     */
    public function test_the_hash_column_lists_are_not_trivially_small(): void
    {
        $this->assertGreaterThan(5, count(MarketDataPipelineService::BARS_HASH_COLUMNS));
        $this->assertGreaterThan(20, count(MarketDataPipelineService::INDICATORS_HASH_COLUMNS));
        $this->assertGreaterThan(3, count(MarketDataPipelineService::ELIGIBILITY_HASH_COLUMNS));
    }

    /**
     * The row key must be in the hash. Without it, moving a value from one ticker to another
     * would leave the multiset of values unchanged and the hash identical.
     *
     * @dataProvider artifacts
     */
    public function test_the_row_key_is_part_of_every_hash(string $current, string $history, array $hashColumns): void
    {
        $this->assertContains('trade_date', $hashColumns);
        $this->assertContains('ticker_id', $hashColumns);
    }

    /**
     * Bookkeeping columns must stay out. Including run_id or created_at would make every
     * recompute of byte-identical data produce a different hash, so every correction would
     * report CHANGED and republish regardless of whether anything actually moved.
     *
     * @dataProvider artifacts
     */
    public function test_no_hash_includes_a_bookkeeping_column(string $current, string $history, array $hashColumns): void
    {
        $this->assertSame(
            [],
            array_values(array_intersect($hashColumns, MarketDataPipelineService::HASH_EXCLUDED_BOOKKEEPING_COLUMNS))
        );
    }
}
