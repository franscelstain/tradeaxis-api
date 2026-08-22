<?php

use App\Infrastructure\Persistence\MarketData\SectorClassificationRepository;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W05 / MD-B05 — the legacy `ticker_id` alias and the `market_data.tickers.*` projection boundary.
 *
 * Owner contracts:
 *   docs/market_data/authority/strategy/book/Tickers_and_Identity_Dependency_Contract_LOCKED.md
 *   docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md
 *
 * The identity contract permits `ticker_id` as a compatibility identity "only when its exact
 * equivalence to `instrument_id` or `listing_id` is documented and invariant", forbids any new
 * table, column, contract, or API field from keying on it until retirement, requires retirement to
 * be a versioned schema change rather than a silent column drop, and makes an alias whose
 * equivalence is not documented and invariant an unresolved identity that must fail closed.
 *
 * The config registry adds the runtime half: the `market_data.tickers.*` keys describe the legacy
 * projection and must not determine point-in-time universe membership, provider-symbol resolution,
 * canonical row identity, or new API and schema keys.
 *
 * The alias is easy to keep and easy to spread. These tests are about the second one.
 */
class LegacyTickerAliasBoundaryTest extends TestCase
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
     * Equivalence is documented and enforced as one-to-one. A nullable, non-unique alias column
     * would satisfy "the column exists" and none of the contract.
     */
    public function test_the_alias_is_carried_as_a_uniquely_bound_column_on_the_stable_listing(): void
    {
        $this->assertTrue(Schema::hasColumn('md_listings', 'legacy_ticker_id'), 'the alias is retained');
        $this->assertFalse(
            Schema::hasColumn('md_listings', 'ticker_id'),
            'the alias is named as legacy, so no surface can mistake it for the stable identity'
        );

        $first = $this->projectTicker(1, 'BBCA');
        DB::table('tickers')->insert([
            'ticker_id' => 2, 'ticker_code' => 'BBRI', 'company_name' => 'BRI',
            'listed_date' => '2023-01-02', 'is_active' => 1,
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection(['BBRI']);
        $second = (int) DB::table('md_listings')->where('legacy_ticker_id', 2)->value('listing_id');

        $this->assertNotSame($first, $second, 'two aliases resolve to two listings');
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('md_listings')->where('listing_id', $second)->update(['legacy_ticker_id' => 1]);
    }

    /**
     * An alias that does not match the stable identity it claims is an unresolved identity. The
     * write path refuses it rather than accepting the closest match.
     */
    public function test_an_alias_that_does_not_match_its_stable_identity_fails_closed(): void
    {
        $this->projectTicker(1, 'BBCA');
        $repository = new SectorClassificationRepository();
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 1)->value('listing_id');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SECTOR_LISTING_TICKER_MISMATCH');

        $repository->appendMembership(
            $listingId, 999, 'G', '2024-01-02', null, 'idx', 'idx-ref', 'IDX-IC',
            'EXCHANGE_AUTHORITATIVE', '2024-01-02 09:00:00'
        );
    }

    /** An alias with no stable identity at all resolves to nothing rather than creating one. */
    public function test_an_alias_with_no_stable_identity_resolves_to_unknown_rather_than_creating_one(): void
    {
        $before = DB::table('md_listings')->count();

        $context = (new SectorClassificationRepository())
            ->resolveSectorContextForTickerIds([4242], '2024-05-02', null, '2024-05-03 00:00:00')[4242];

        $this->assertSame('UNKNOWN', $context['sector_code']);
        $this->assertSame('SECTOR_LISTING_IDENTITY_UNKNOWN', $context['resolution_reason_code']);
        $this->assertSame($before, DB::table('md_listings')->count(), 'nothing was fabricated');
    }

    /**
     * No table created since the V2 identity foundation keys on `ticker_id`. The scan is over the
     * migration corpus rather than the deployed schema, because the rule governs what may be added.
     */
    public function test_no_table_created_since_the_v2_foundation_keys_on_the_legacy_alias(): void
    {
        $directory = dirname(__DIR__, 3).'/database/migrations';
        $offenders = [];
        $scannedTables = 0;
        $scannedFiles = 0;

        foreach (glob($directory.'/*.php') as $file) {
            $name = basename($file);
            // The V2 identity foundation is the boundary: before it there was no stable identity to
            // key on, so a pre-existing alias column is the compatibility the contract preserves.
            if (strcmp(substr($name, 0, 10), '2026_08_02') < 0) {
                continue;
            }
            $scannedFiles++;
            $source = (string) file_get_contents($file);
            if (! preg_match_all('/Schema::create\(\s*\'([a-z0-9_]+)\'.*?\n(\s*)\}\);/s', $source, $matches, PREG_SET_ORDER)) {
                continue;
            }
            foreach ($matches as $match) {
                $scannedTables++;
                if (preg_match('/[\'"]ticker_id[\'"]/', $match[0])) {
                    $offenders[] = $name.' :: '.$match[1];
                }
            }
        }

        $this->assertGreaterThan(5, $scannedFiles, 'the migration scan must reach the post-foundation corpus');
        $this->assertGreaterThan(10, $scannedTables, 'the scan must actually find created tables');
        $this->assertSame([], $offenders, 'a new table keys on the legacy alias instead of stable identity');
    }

    /**
     * The retirement condition is a demonstrated fact, not a judgement call, and right now it is
     * demonstrably unmet: the downstream read product still emits `ticker_id`, so a reader outside
     * this package depends on it and retiring the alias would break the read model.
     *
     * Asserting the condition is unmet is the honest proof of this rule while it stays unmet. If the
     * read product stops emitting the alias, this test fails and the retirement question is reopened
     * deliberately rather than drifting.
     */
    public function test_retirement_stays_blocked_while_the_read_product_still_emits_the_alias(): void
    {
        $readProduct = (string) file_get_contents(
            dirname(__DIR__, 3).'/app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php'
        );

        // Assembled rather than written as one literal: the array-access form is what the read
        // product actually emits, and quoting it whole here is the kind of detail that breaks
        // silently when the file is edited by a script rather than by hand.
        $emits = '$result['."'".'ticker_id'."'".']';

        $this->assertNotFalse(
            strpos($readProduct, $emits),
            'the read product no longer emits the alias, so the retirement condition must be re-evaluated'
        );

        // And no reader inside `app/` outside the market-data package depends on it, so the only
        // dependency keeping the alias alive is the published read model — which is what makes
        // retirement a versioned read-model change rather than a cleanup.
        $outside = [];
        foreach ($this->phpFiles(dirname(__DIR__, 3).'/app') as $file) {
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen(dirname(__DIR__, 3)) + 1));
            if (strpos($relative, 'MarketData') !== false) {
                continue;
            }
            if (strpos((string) file_get_contents($file), 'ticker_id') !== false) {
                $outside[] = $relative;
            }
        }

        $this->assertSame([], $outside, 'an in-repository reader outside the package depends on the alias');
    }

    /**
     * The prohibition covers new columns as well as new tables. A column added to an existing table
     * is the easier way to spread the alias, and the one a table-only scan would miss.
     */
    public function test_no_column_added_since_the_v2_foundation_introduces_the_legacy_alias(): void
    {
        $offenders = [];
        $scanned = 0;

        foreach (glob(dirname(__DIR__, 3).'/database/migrations/*.php') as $file) {
            $name = basename($file);
            if (strcmp(substr($name, 0, 10), '2026_08_02') < 0) {
                continue;
            }
            $scanned++;
            $source = (string) file_get_contents($file);
            foreach (preg_split('/\r?\n/', $source) as $line) {
                if (! preg_match($this->columnPattern(), $line)) {
                    continue;
                }
                $offenders[] = $name.' :: '.trim($line);
            }
        }

        $fixture = '$table->unsignedBigInteger('."'".'ticker_id'."'".');';

        $this->assertGreaterThan(5, $scanned, 'the column scan must reach the post-foundation corpus');
        $this->assertSame(1, preg_match($this->columnPattern(), $fixture), 'the column pattern must be able to fire');
        $this->assertSame([], $offenders, 'a new column keys on the legacy alias instead of stable identity');
    }

    /**
     * Retirement is a versioned schema change. The guard here is the negative one that matters: no
     * migration drops the alias column, so it cannot disappear without a governed replacement.
     */
    public function test_the_alias_is_never_silently_dropped_by_a_migration(): void
    {
        $offenders = [];
        foreach (glob(dirname(__DIR__, 3).'/database/migrations/*.php') as $file) {
            $source = (string) file_get_contents($file);
            if (preg_match('/dropColumn\(\s*\[?[^)]*legacy_ticker_id/', $source)) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame([], $offenders, 'the compatibility alias may be preserved or governed away, not dropped');
    }

    /**
     * The legacy projection is a source of identity records, not a resolver. It reads the master
     * without consulting `is_active`, so a security inactive today still projects the interval that
     * makes it resolvable on a date it was live.
     */
    public function test_the_projection_reads_the_legacy_master_without_consulting_the_current_active_flag(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => 7, 'ticker_code' => 'GONE', 'company_name' => 'Delisted Co',
            'listed_date' => '2023-01-02', 'delisted_date' => '2024-06-01', 'is_active' => 0,
        ]);

        (new TemporalIdentityRepository())->ensureLegacyProjection(['GONE']);
        $listingId = (int) DB::table('md_listings')->where('legacy_ticker_id', 7)->value('listing_id');

        $this->assertGreaterThan(0, $listingId, 'an inactive ticker still projects an identity');
        $this->assertSame('DELISTED', (string) DB::table('md_listings')->where('listing_id', $listingId)->value('listing_state'));

        $codes = [];
        foreach ((new TemporalIdentityRepository())->readProjectedUniverseAsOf('2024-05-30') as $row) {
            $codes[] = $row['ticker_code'];
        }
        $this->assertContains('GONE', $codes, 'inactive now, resolvable then');
    }

    /**
     * The read-only universe surface does not project. Planning and admission measurement must not
     * be able to turn legacy master rows into V2 identity facts as a side effect of reading.
     */
    public function test_the_fail_closed_read_surface_does_not_create_identity_while_reading(): void
    {
        DB::table('tickers')->insert([
            'ticker_id' => 8, 'ticker_code' => 'NEWCO', 'company_name' => 'New Co',
            'listed_date' => '2023-01-02', 'is_active' => 1,
        ]);

        $rows = (new TemporalIdentityRepository())->readProjectedUniverseAsOf('2024-05-02');

        $this->assertSame([], $rows, 'nothing is resolvable before the identity record exists');
        $this->assertSame(0, DB::table('md_listings')->count(), 'and reading did not create one');
    }

    /**
     * Provider-symbol resolution is driven by the mapping table, not by the legacy projection config.
     * A resolver that read `market_data.tickers.*` for the provider symbol would satisfy every
     * behavioral test above and still be the substitution the config registry forbids.
     */
    public function test_provider_symbol_resolution_does_not_read_the_legacy_projection_config(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 3).'/app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php'
        );
        $resolveProviderContext = $this->methodBody($source, 'resolveProviderContext');
        $baseIdentityQuery = $this->methodBody($source, 'baseIdentityQuery');

        $this->assertNotSame('', $resolveProviderContext, 'the resolver method must be found for this scan to mean anything');
        $this->assertNotSame('', $baseIdentityQuery);

        foreach (['resolveProviderContext' => $resolveProviderContext, 'baseIdentityQuery' => $baseIdentityQuery] as $label => $body) {
            $this->assertFalse(
                strpos($body, 'market_data.tickers.'),
                $label.' resolves identity from the legacy projection config'
            );
            $this->assertFalse(strpos($body, 'is_active'), $label.' consults the current active flag');
        }
        $this->assertNotFalse(
            strpos($resolveProviderContext, 'md_provider_symbol_mappings'),
            'the provider symbol comes from the mapping record'
        );
    }

    /** @return array<int,string> */
    private function phpFiles(string $root): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        return $files;
    }

    /** Assembled at runtime so the probe is not itself a Blueprint column declaration. */
    private function columnPattern(): string
    {
        return '/\$table->[a-zA-Z]+\(\s*.(ticker_id).\s*[,)]/';
    }

    private function methodBody(string $source, string $method): string
    {
        $start = strpos($source, ' function '.$method.'(');
        if ($start === false) {
            return '';
        }
        $open = strpos($source, '{', $start);
        $depth = 0;
        for ($i = $open; $i < strlen($source); $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $open, $i - $open + 1);
                }
            }
        }

        return '';
    }

    private function projectTicker(int $tickerId, string $code): int
    {
        DB::table('tickers')->insert([
            'ticker_id' => $tickerId, 'ticker_code' => $code, 'company_name' => $code.' Co',
            'listed_date' => '2023-01-02', 'is_active' => 1,
        ]);
        (new TemporalIdentityRepository())->ensureLegacyProjection([$code]);

        return (int) DB::table('md_listings')->where('legacy_ticker_id', $tickerId)->value('listing_id');
    }
}
