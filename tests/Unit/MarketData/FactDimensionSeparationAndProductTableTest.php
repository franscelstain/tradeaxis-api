<?php

use PHPUnit\Framework\TestCase;
use Tests\Support\MarketData\ReadsMarketDataSchema;

/**
 * MD-B01 — the five separate fact dimensions, the price-product table, and the date-driven
 * import contract.
 *
 * Owner contracts: `MARKET_DATA_PLATFORM_EOD_BASELINE.md`, `Domain_Boundary_Invariants_LOCKED.md`,
 * and `Terminology_and_Scope.md`. All three state the same separation obligation in their own words,
 * so each is proven against the same surface but through its own assertion.
 *
 * The separation rules were deferred at `MD-B01-A008` and `MD-B01-A009` on the grounds that coverage
 * and liquidity had no implementation surface yet. That was read from a schema view that covered only
 * `database/migrations/**`. `MD-B01-A010` corrected the reader to include the base SQL the core
 * migration executes, and with the full surface visible all five dimensions turn out to be present
 * and, more importantly, to share no column at all. The deferral was an artefact of the instrument,
 * not of the codebase, so the rules are proven here rather than left blocked.
 */
class FactDimensionSeparationAndProductTableTest extends TestCase
{
    use ReadsMarketDataSchema;

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * The five dimensions the contracts require to stay separately inspectable, and how each is
     * recognised in the schema.
     *
     * @return array<string,string>
     */
    private function dimensions(): array
    {
        return [
            'coverage' => '/^coverage_/',
            'quality' => '/quality|is_valid|invalid_reason/',
            'liquidity' => '/adv\d+|turnover|traded_value|volume_proxy/',
            'event risk' => '/event_risk|corporate_action|scale_break|trading_status/',
            'data usability' => '/eligib|data_usab/',
        ];
    }

    /** @return array<string,array<int,string>> dimension => qualified columns */
    private function dimensionColumns(): array
    {
        $out = [];
        foreach ($this->dimensions() as $dimension => $pattern) {
            $out[$dimension] = [];
            foreach ($this->schemaColumnMap() as $table => $columns) {
                foreach (array_keys($columns) as $column) {
                    if (preg_match($pattern, $column)) {
                        $out[$dimension][] = $table.'.'.$column;
                    }
                }
            }
        }

        return $out;
    }

    private function reasonCodes(): string
    {
        return (string) file_get_contents($this->root().'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
    }

    /** @return array<string,string> filename => source, comments stripped */
    private function marketDataSource(): array
    {
        $out = [];
        foreach ([
            'app/Application/MarketData', 'app/Domain/MarketData', 'app/Infrastructure/MarketData',
            'app/Infrastructure/Persistence/MarketData', 'app/Console/Commands/MarketData',
        ] as $dir) {
            $path = $this->root().'/'.$dir;
            if (! is_dir($path)) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                    $out[$file->getFilename()] = $this->stripPhpComments((string) file_get_contents($file->getPathname()));
                }
            }
        }

        return $out;
    }

    // ---------- the five dimensions, one assertion per owner ----------

    /**
     * Every dimension must be present as its own set of columns. A dimension with no surface cannot
     * be inspected at all, so this is checked before the separation itself.
     */
    private function assertEveryDimensionIsPopulated(string $ruleId): array
    {
        $columns = $this->dimensionColumns();

        $empty = [];
        foreach ($columns as $dimension => $found) {
            if ($found === []) {
                $empty[] = $dimension;
            }
        }
        $this->assertSame([], $empty, $ruleId.': a dimension with no surface cannot be separately inspected');

        return $columns;
    }

    /**
     * The separation itself. If two dimensions shared a column, their meanings and evidence would
     * have collapsed into one field, which all three owner contracts forbid in their own words.
     */
    private function assertDimensionsDoNotCollapse(string $ruleId, array $columns): void
    {
        $owners = [];
        foreach ($columns as $dimension => $found) {
            foreach ($found as $qualified) {
                $owners[$qualified][] = $dimension;
            }
        }

        $shared = [];
        foreach ($owners as $qualified => $claimedBy) {
            if (count($claimedBy) > 1) {
                $shared[] = $qualified.' claimed by '.implode(' + ', $claimedBy);
            }
        }

        $this->assertSame([], $shared, $ruleId.': two dimensions share a column, so their evidence has collapsed into one field');
    }

    public function test_the_five_fact_dimensions_are_separate_under_the_platform_baseline(): void
    {
        // MD-S001-R0056 — five dimensi terpisah
        $columns = $this->assertEveryDimensionIsPopulated('MD-S001-R0056');
        $this->assertGreaterThan(90, array_sum(array_map('count', $columns)),
            'MD-S001-R0056: the dimension scan must reach the full schema surface');
        $this->assertDimensionsDoNotCollapse('MD-S001-R0056', $columns);
    }

    public function test_the_five_fact_dimensions_are_separate_under_the_boundary_contract(): void
    {
        // MD-S020-R0066 — must remain separately inspectable; combining them does not permit collapse
        $columns = $this->assertEveryDimensionIsPopulated('MD-S020-R0066');
        $this->assertDimensionsDoNotCollapse('MD-S020-R0066', $columns);

        // the eligibility result may combine them, but must still carry its own explanatory field
        $usability = $columns['data usability'];
        $explanatory = false;
        foreach ($usability as $qualified) {
            if (preg_match('/reason/', $qualified)) {
                $explanatory = true;
                break;
            }
        }
        $this->assertTrue($explanatory,
            'MD-S020-R0066: an eligibility result must remain explainable through its own reason field');
    }

    public function test_the_five_fact_dimensions_are_separate_under_the_terminology_register(): void
    {
        // MD-S056-R0125 — separately explainable
        $columns = $this->assertEveryDimensionIsPopulated('MD-S056-R0125');
        $this->assertDimensionsDoNotCollapse('MD-S056-R0125', $columns);

        // separately *explainable* means each dimension owns reason codes of its own
        $codes = $this->reasonCodes();
        foreach (['COVERAGE_', 'IND_', 'ELIG_'] as $prefix) {
            $this->assertGreaterThan(1, substr_count($codes, "('".$prefix),
                'MD-S056-R0125: each dimension must own reason codes rather than share one explanation');
        }
    }

    // ---------- the price-product table ----------

    public function test_the_raw_source_observation_is_not_yet_a_canonical_bar(): void
    {
        // MD-S001-R0051
        $columns = $this->schemaColumnMap();

        $this->assertArrayHasKey('md_source_observations', $columns,
            'MD-S001-R0051: the provider payload with provenance must have its own immutable surface');
        $this->assertArrayHasKey('eod_bars', $columns,
            'MD-S001-R0051: canonical bars must be a distinct surface');

        $shared = array_intersect(array_keys($columns['md_source_observations']), array_keys($columns['eod_bars']));
        $this->assertNotSame(
            array_keys($columns['md_source_observations']),
            array_keys($columns['eod_bars']),
            'MD-S001-R0051: an observation is not automatically a canonical bar'
        );
        $this->assertLessThan(
            count($columns['md_source_observations']),
            count($shared),
            'MD-S001-R0051: the two surfaces must not be column-identical'
        );
    }

    public function test_structural_adjusted_pairs_ohlc_adjustment_with_inverse_volume(): void
    {
        // MD-S001-R0053
        $surface = $this->schemaSurface();
        foreach ($this->marketDataSource() as $source) {
            $surface .= $source."\n";
        }

        $this->assertMatchesRegularExpression('/STRUCTURAL_ADJUSTED/', $surface,
            'MD-S001-R0053: the coherent adjusted product must be declared');

        $columns = $this->schemaColumnMap();
        $this->assertArrayHasKey('md_adjustment_factors', $columns,
            'MD-S001-R0053: adjustment must rest on verified corporate-action factors');

        $hasVolumeFactor = false;
        foreach ($columns['md_adjustment_factors'] as $column => $_) {
            if (preg_match('/volume/', $column)) {
                $hasVolumeFactor = true;
                break;
            }
        }
        $this->assertTrue($hasVolumeFactor,
            'MD-S001-R0053: volume must be adjustable inversely, so a volume factor must exist alongside the price factor');
    }

    public function test_provider_adjusted_close_is_not_a_price_product_and_never_a_fallback(): void
    {
        // MD-S001-R0055 and MD-S056-R0124
        $declared = '';
        foreach ([
            'app/Domain/MarketData/MarketDataScope.php',
            'app/Application/MarketData/Services/AnalyticalProductIdentityService.php',
            'config/market_data.php',
        ] as $relative) {
            $path = $this->root().'/'.$relative;
            if (is_file($path)) {
                $declared .= $this->stripPhpComments((string) file_get_contents($path))."\n";
            }
        }

        // A price product is what a *_PRODUCT declaration names. The provider field map is a
        // different thing entirely: config carries 'close', 'volume', 'adj_close' as the names of
        // fields in the provider payload, which is exactly what reading a raw source observation
        // requires. Treating that entry as a product declaration would flag the domain for doing
        // what MD-S001-R0051 obliges it to do.
        preg_match_all("/const\s+([A-Z0-9_]*PRODUCT)\s*=\s*'([^']+)'/", $declared, $m, PREG_SET_ORDER);

        $declaredProducts = [];
        foreach ($m as $declaration) {
            $declaredProducts[$declaration[1]] = $declaration[2];
        }

        $this->assertCount(3, $declaredProducts,
            'MD-S001-R0055: exactly three price products are declared by the baseline contract');
        $this->assertSame(['RAW', 'STRUCTURAL_ADJUSTED', 'TOTAL_RETURN'], array_values($declaredProducts),
            'MD-S001-R0055: the declared products must be exactly the three the contract names');

        $strays = [];
        foreach ($declaredProducts as $constant => $value) {
            if (preg_match('/adj.*close/i', $constant) || preg_match('/adj.*close/i', $value)) {
                $strays[] = $constant.' = '.$value;
            }
        }
        $this->assertSame([], $strays,
            'MD-S001-R0055: provider adjusted close must not be declared as one of the price products');

        // and no indicator vector may fall back to it or mix bases across dates
        $indicator = $this->stripPhpComments(
            (string) file_get_contents($this->root().'/app/Application/MarketData/Services/IndicatorVectorService.php')
        );
        $this->assertDoesNotMatchRegularExpression('/adj_close|adjusted_close/', $indicator,
            'MD-S056-R0124: one indicator run must not mix close with a provider adjusted close');

        $this->assertMatchesRegularExpression('/function priceBasis/', $indicator,
            'MD-S056-R0124: the basis must be bound through one selector rather than chosen per row');
    }

    // ---------- date-driven import and its boundaries ----------

    public function test_the_import_path_accepts_historical_and_latest_dates_alike(): void
    {
        // MD-S001-R0035
        $commands = [];
        foreach (glob($this->root().'/app/Console/Commands/MarketData/*.php') as $file) {
            $source = $this->stripPhpComments((string) file_get_contents($file));
            if (preg_match('/\$signature\s*=\s*[\'"](.+?)[\'"]\s*;/s', $source, $m)) {
                $commands[basename($file)] = $m[1];
            }
        }
        $this->assertGreaterThan(30, count($commands), 'the console scan must reach the market-data commands');

        $dateDriven = [];
        foreach ($commands as $name => $signature) {
            if (preg_match('/\{--?\s*(date|start[-_]?date|trade[-_]?date)/i', $signature)) {
                $dateDriven[] = $name;
            }
        }
        $this->assertNotSame([], $dateDriven,
            'MD-S001-R0035: import must be driven by a requested date rather than by whatever the provider returns');

        // no import command may hard-code a recency default in place of the requested date
        $hardcoded = [];
        foreach ($commands as $name => $signature) {
            if (preg_match('/\{--?(date|trade[-_]?date)=(today|now|latest)/i', $signature)) {
                $hardcoded[] = $name;
            }
        }
        $this->assertSame([], $hardcoded,
            'MD-S001-R0035: a date default of today or latest would make the historical path unreachable');
    }

    public function test_the_provider_default_query_window_is_isolated_in_the_adapter(): void
    {
        // MD-S001-R0077
        $sources = $this->marketDataSource();

        $windowAware = [];
        foreach ($sources as $name => $source) {
            if (preg_match("/'range'|range_days|window_days|period1/", $source)) {
                $windowAware[] = $name;
            }
        }

        $this->assertNotSame([], $windowAware,
            'MD-S001-R0077: the provider default query window must be represented explicitly rather than assumed');

        // the provider window must live in the acquisition layer, not in the read or publication layer
        $leaked = [];
        foreach ($windowAware as $name) {
            if (preg_match('/(Read|Publication|Seal|Pointer|Eligib)/', $name)) {
                $leaked[] = $name;
            }
        }
        $this->assertSame([], $leaked,
            'MD-S001-R0077: a provider transport window must not reach the read or publication layer');
    }

    public function test_a_partial_import_cannot_become_a_readable_publication(): void
    {
        // MD-S001-R0061
        $codes = $this->reasonCodes();

        $this->assertMatchesRegularExpression('/PARTIAL/', $codes,
            'MD-S001-R0061: a partial import must be nameable');

        // every partial reason must be recorded as blocking or as a warning the gate must decide on,
        // never as an admissible readable state
        preg_match_all("/\('([A-Z0-9_]*PARTIAL[A-Z0-9_]*)',\s*'[A-Z_]+',\s*'[^']*',\s*'([A-Z]+)'/", $codes, $m, PREG_SET_ORDER);
        $this->assertNotSame([], $m, 'the partial-import reason codes must be readable from the registry');

        $admissible = [];
        foreach ($m as $row) {
            if (! in_array($row[2], ['HARD', 'WARN'], true)) {
                $admissible[] = $row[1].' severity '.$row[2];
            }
        }
        $this->assertSame([], $admissible,
            'MD-S001-R0061: a partial import must be blocking or gate-deciding, never an ordinary readable outcome');
    }

    /**
     * The product check must separate a product declaration from a provider field-map entry. An
     * earlier revision of this test flagged `adj_close` in the provider field map, which is the
     * domain reading the payload it is obliged to read.
     */
    public function test_a_provider_field_map_entry_is_not_read_as_a_product_declaration(): void
    {
        $fieldMap = "<?php\nreturn ['fields' => ['close' => 'close', 'volume' => 'volume', 'adj_close' => 'adj_close']];\n";
        preg_match_all("/const\s+([A-Z0-9_]*PRODUCT)\s*=\s*'([^']+)'/", $fieldMap, $m, PREG_SET_ORDER);
        $this->assertSame([], $m, 'a provider field map declares no price product');

        $fourthProduct = "<?php\nclass X { const ADJ_CLOSE_PRODUCT = 'ADJ_CLOSE'; }\n";
        preg_match_all("/const\s+([A-Z0-9_]*PRODUCT)\s*=\s*'([^']+)'/", $fourthProduct, $m, PREG_SET_ORDER);
        $this->assertCount(1, $m, 'a real product declaration must be found, otherwise the check above proves nothing');
        $this->assertMatchesRegularExpression('/adj.*close/i', $m[0][1],
            'a product declaration naming the provider adjusted close must be recognisable as one');
    }

    public function test_immutable_source_observations_carry_provenance(): void
    {
        // MD-S001-R0100
        $columns = $this->schemaColumnMap();
        $this->assertArrayHasKey('md_source_observations', $columns,
            'MD-S001-R0100: this domain owns immutable source observations and provenance');

        // Provenance is not one column. Asking whether any provenance-ish column exists is satisfied
        // by a single survivor — the failure shape recorded at MD-B01-A010 — so each aspect that
        // makes an observation traceable back to its origin is required separately.
        $aspects = [
            'the source it came from' => '/^(provider|source_name)$/',
            'the payload identity' => '/payload_hash|content_hash/',
            'the provider symbol it was fetched under' => '/provider_symbol|provider_mapping_id/',
            'the provider schema it was read against' => '/provider_schema_version|schema_version/',
        ];

        $missing = [];
        foreach ($aspects as $aspect => $pattern) {
            $found = false;
            foreach (array_keys($columns['md_source_observations']) as $column) {
                if (preg_match($pattern, $column)) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $missing[] = $aspect;
            }
        }

        $this->assertSame([], $missing,
            'MD-S001-R0100: an observation missing part of its provenance is stored, not owned');
    }
}
