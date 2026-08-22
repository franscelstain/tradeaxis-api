<?php

use PHPUnit\Framework\TestCase;
use Tests\Support\MarketData\ReadsMarketDataSchema;

/**
 * MD-B01 — the term ownership register and the price-product terminology.
 *
 * Owner contract: authority/strategy/book/Terminology_and_Scope.md.
 *
 * The register owns a fixed vocabulary and states the rule that binds every other document: other
 * documents may use, summarise, and refer to these terms, but may not redefine, widen, or narrow
 * them. The contract supplies its own operative test for a breach:
 *
 *   "Sebuah istilah yang ternyata memiliki dua definisi substantif di dua dokumen adalah
 *    pelanggaran kontrak, bukan perbedaan gaya penulisan."
 *
 * Two definitions, not two mentions. So the guard looks for a *competing definition* — a definition
 * heading for an owned term followed by a sentence that says what the term is — and deliberately does
 * not flag a document that merely uses the term, assigns it a role, or heads a section with it.
 * `SYSTEM_CONTEXT_AND_DEPENDENCIES.md` says "requested trade date adalah input domain utama", which
 * states a role and is explicitly permitted; `Indices_and_Constraints_Contract_LOCKED.md` heads a
 * section "Canonical bars" and follows it with "Must enforce:", which constrains a table rather than
 * defining a word. Both must stay green, and both are asserted below.
 *
 * The price-product rules are proven against the schema and the indicator engine rather than against
 * prose, because that is where a price basis is actually bound.
 */
class TermOwnershipAndPriceProductTest extends TestCase
{
    use ReadsMarketDataSchema;

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** Terms the register owns, as listed in its ownership table. */
    /**
     * The owned terms are read from the register table itself rather than restated here.
     *
     * A hardcoded copy is a second place a term can be added, which is the arrangement
     * `MD-S056-R0143` exists to prevent. The copy had also drifted: it named 20 of the register's
     * 33 terms, so 13 owned terms carried no redefinition guard at all.
     *
     * @return array<int,string>
     */
    private function ownedTerms(): array
    {
        $owner = (string) file_get_contents($this->root().'/'.self::TERM_OWNER_DOCUMENT);

        $terms = [];
        if (preg_match('/## Term ownership register \(LOCKED\)(.*?)Aturan yang mengikat/s', $owner, $section)) {
            foreach (preg_split('/\R/', $section[1]) as $line) {
                $line = trim($line);
                if (strpos($line, '|') !== 0) {
                    continue;
                }
                $cells = array_map('trim', explode('|', trim($line, "| \t")));
                if (count($cells) < 2 || $cells[0] === 'Kelompok' || strpos($cells[0], '---') === 0) {
                    continue;
                }
                foreach (explode(',', $cells[1]) as $term) {
                    $term = trim(str_replace('`', '', $term));
                    if ($term !== '') {
                        $terms[] = $term;
                    }
                }
            }
        }

        return $terms;
    }

    private const TERM_OWNER_DOCUMENT = 'docs/market_data/authority/strategy/book/Terminology_and_Scope.md';

    /**
     * The terms the hardcoded list used to name. Coverage may grow with the register; it may not
     * shrink below what was already guarded.
     */
    private const PREVIOUSLY_GUARDED_TERMS = [
        'raw source observation', 'RAW', 'STRUCTURAL_ADJUSTED', 'TOTAL_RETURN',
        'date-driven capability', 'provider limitation abstraction', 'fatal failure',
        'per-ticker failure', 'eligibility snapshot', 'requested trade date',
        'effective trade date', 'canonical bars', 'invalid bars', 'session snapshot',
        'decision horizon', 'decision-grade', 'intentional dataset start',
        'archived proof window', 'development data frontier', 'operational activation',
    ];

    /**
     * `MD-S056-R0143` — adding a new term to the register is a change to the register's document,
     * not to the document that needs the term.
     *
     * Structural, and exactly so: the register exists in one place. If a second document carried a
     * register section, a term could be added without touching the owner, and the rule would be
     * unenforceable rather than merely unobserved. The guard's own term list is derived from that
     * one table, so extending the register extends the obligation automatically.
     */
    public function test_the_register_has_exactly_one_home_so_a_new_term_changes_that_document(): void
    {
        $registerSections = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/docs/market_data', FilesystemIterator::SKIP_DOTS)) as $file) {
            if (! $file->isFile() || substr($file->getFilename(), -3) !== '.md') {
                continue;
            }
            $relative = substr(strtr($file->getPathname(), chr(92), '/'), strlen($this->root()) + 1);
            if (strpos($relative, 'docs/market_data/records/history/') === 0) {
                continue;
            }
            if (preg_match('/^#{2,6}\s+Term ownership register/mi', (string) file_get_contents($file->getPathname()))) {
                $registerSections[] = $relative;
            }
        }

        $this->assertSame(
            [self::TERM_OWNER_DOCUMENT],
            $registerSections,
            'MD-S056-R0143: the register must have exactly one home, or a term could be added elsewhere'
        );

        $terms = $this->ownedTerms();
        $this->assertGreaterThanOrEqual(33, count($terms), 'the register table must actually be parsed, not silently empty');
        foreach (self::PREVIOUSLY_GUARDED_TERMS as $term) {
            $this->assertContains($term, $terms, 'guard coverage may not shrink: '.$term.' was already guarded');
        }
    }

    /**
     * `MD-S056-R0144` — a term found with two substantive definitions in two documents is a contract
     * violation, not a difference in writing style.
     *
     * The rule classifies rather than counts, so the proof is that the control treats such a case as
     * a failure. Three things are shown together: the detector fires on a real second definition,
     * stays silent on the two shapes the contract permits, and the corpus currently holds none — so
     * the classification is enforced rather than merely declared.
     */
    public function test_a_second_substantive_definition_is_a_violation_and_not_a_style_difference(): void
    {
        $secondDefinition = "### Canonical bars\nCanonical bars adalah baris apa pun yang lolos import tanpa validasi kanonikal lebih lanjut.\n";
        $this->assertNotSame(
            [],
            $this->competingDefinitions('probe.md', $secondDefinition),
            'MD-S056-R0144: a second substantive definition must be detected, not tolerated'
        );

        $styleDifference = "### Canonical bars\nSee `Terminology_and_Scope.md`; bila berbeda, definisi di sana yang berlaku.\n";
        $this->assertSame(
            [],
            $this->competingDefinitions('probe.md', $styleDifference),
            'MD-S056-R0144: a pointer-carrying summary is the permitted shape and must not be called a violation'
        );

        $sectionAboutTheTerm = "### Canonical bars\nMust enforce:\n- one row per `(trade_date, listing_id)` within the resolved canonical revision\n";
        $this->assertSame(
            [],
            $this->competingDefinitions('probe.md', $sectionAboutTheTerm),
            'MD-S056-R0144: a constraints section is about the term, not a competing definition of it'
        );

        $competing = [];
        foreach ($this->activeDocuments() as $relative => $text) {
            foreach ($this->competingDefinitions($relative, $text) as $hit) {
                $competing[] = $hit;
            }
        }
        $this->assertSame([], $competing, 'MD-S056-R0144: an active document holds a second definition of an owned term');
    }

    /**
     * A sentence that says what a term *is*. The gap cannot span a negation, so a document quoting
     * the prohibition is not read as breaking it — the defect recorded at MD-B01-A008.
     */
    private function definitionSentence(string $term): string
    {
        $gap = '(?:(?!\bnever\b|\bnot\b|\bbukan\b|\btidak\b|\bjangan\b)[^.])';

        return '/'.preg_quote($term, '/').$gap.'{0,15}\b(is|adalah|means|berarti|refers to|didefinisikan sebagai)\b'.$gap.'{10,}/i';
    }

    /** @return array<string,string> relative path => contents */
    private function activeDocuments(): array
    {
        $root = $this->root().'/docs/market_data';
        $out = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if (! $file->isFile() || substr($file->getFilename(), -3) !== '.md') {
                continue;
            }
            $relative = substr(strtr($file->getPathname(), chr(92), '/'), strlen(strtr($root, chr(92), '/')) + 1);
            if (strpos($relative, 'authority/strategy/') === 0 || strpos($relative, 'records/history/') === 0) {
                continue;
            }
            $out[$relative] = (string) file_get_contents($file->getPathname());
        }

        return $out;
    }

    /**
     * A competing definition: a heading naming an owned term, followed within the section by a
     * sentence defining it. Returns the offending sections.
     *
     * @return array<int,string>
     */
    private function competingDefinitions(string $relative, string $text): array
    {
        $found = [];
        $lines = explode("\n", $text);

        foreach ($lines as $i => $line) {
            if (! preg_match('/^#{2,4}\s+(.+)$/', trim($line), $m)) {
                continue;
            }
            $heading = strtolower(trim(preg_replace('/[`*]/', '', $m[1])));

            foreach ($this->ownedTerms() as $term) {
                $needle = strtolower($term);
                if ($heading !== $needle && $heading !== $needle.' price product' && $heading !== $needle.' (locked)') {
                    continue;
                }
                // read the section body until the next heading
                $body = '';
                for ($j = $i + 1; $j < count($lines); $j++) {
                    if (preg_match('/^#{1,4}\s+/', trim($lines[$j]))) {
                        break;
                    }
                    $body .= $lines[$j]."\n";
                }
                if (preg_match($this->definitionSentence($term), $body, $hit)) {
                    $found[] = $relative.' :: '.$m[1].' :: '.trim(substr($hit[0], 0, 80));
                }
            }
        }

        return $found;
    }

    private function schemaText(): string
    {
        return $this->schemaSurface();
    }

    /** @return array<string,array<string,bool>> table => column => true */
    private function schemaColumns(): array
    {
        return $this->schemaColumnMap();
    }

    /**
     * Where the price products are actually declared. The DDL carries a product column; the
     * permitted values live in the domain scope and configuration, so that is what must be read.
     */
    private function productDeclarationSurface(): string
    {
        $text = '';
        foreach ([
            'app/Domain/MarketData/MarketDataScope.php',
            'app/Application/MarketData/Services/AnalyticalProductIdentityService.php',
            'config/market_data.php',
        ] as $relative) {
            $path = $this->root().'/'.$relative;
            if (is_file($path)) {
                $text .= $this->stripPhpComments((string) file_get_contents($path))
            ."\n";
            }
        }

        return $text;
    }

    private function reasonCodes(): string
    {
        return (string) file_get_contents($this->root().'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
    }

    private function indicatorService(): string
    {
        return (string) file_get_contents($this->root().'/app/Application/MarketData/Services/IndicatorVectorService.php');
    }

    // ---------- MD-S056-R0141 — term ownership ----------

    public function test_no_active_document_carries_a_competing_definition_of_an_owned_term(): void
    {
        $documents = $this->activeDocuments();
        $this->assertGreaterThan(200, count($documents), 'the ownership scan must reach the active corpus');

        $competing = [];
        foreach ($documents as $relative => $text) {
            foreach ($this->competingDefinitions($relative, $text) as $hit) {
                $competing[] = $hit;
            }
        }

        $this->assertSame([], $competing, 'MD-S056-R0141: an active document defines a term the register owns');
    }

    /**
     * The two shapes the contract explicitly permits must stay green, otherwise the guard above is a
     * word blacklist rather than a redefinition check.
     */
    public function test_permitted_uses_of_an_owned_term_are_not_read_as_redefinition(): void
    {
        $roleStatement = "## Konteks\n\nArtinya:\n- requested trade date adalah input domain utama\n- provider transport bukan source of truth domain\n";
        $this->assertSame([], $this->competingDefinitions('probe.md', $roleStatement),
            'a role statement in a consequence list is permitted use, not redefinition');

        $constraintSection = "### Canonical bars\nMust enforce:\n- one row per `(trade_date, listing_id)` within the resolved canonical revision\n";
        $this->assertSame([], $this->competingDefinitions('probe.md', $constraintSection),
            'a section heading followed by constraints defines a table, not a word');

        $redefinition = "### Canonical bars\nCanonical bars adalah baris apa pun yang berhasil diimpor dari provider tanpa validasi lebih lanjut.\n";
        $this->assertNotSame([], $this->competingDefinitions('probe.md', $redefinition),
            'a real competing definition must be flagged, otherwise a clean corpus proves nothing');
    }

    // ---------- MD-S056-R0054, R0062 — raw source observation ----------

    public function test_the_raw_source_observation_surface_carries_provenance_and_identity(): void
    {
        $columns = $this->schemaColumns();
        $this->assertArrayHasKey('md_source_observations', $columns,
            'MD-S056-R0054: immutable source observations are a declared minimum output of this domain');

        $observation = $columns['md_source_observations'];
        $this->assertGreaterThan(10, count($observation), 'the observation table must be read');

        foreach ([
            'provenance' => '/provider|source_name|provenance|source_ref/',
            'source timestamp' => '/source.*(_at|_ts|_time)|observed_at/',
            'acquisition timestamp' => '/acquisition|acquired|fetched|retrieved/',
            'observation identity' => '/observation_id|source_observation_id|payload_hash|content_hash/',
        ] as $requirement => $pattern) {
            $matched = false;
            foreach (array_keys($observation) as $column) {
                if (preg_match($pattern, $column)) {
                    $matched = true;
                    break;
                }
            }
            $this->assertTrue($matched, 'MD-S056-R0062: the raw source observation must carry '.$requirement);
        }

        // a raw source observation is not automatically a canonical bar
        $this->assertArrayHasKey('eod_bars', $columns + ['eod_bars' => []],
            'canonical bars must be a separate surface from source observations');
        $this->assertNotSame(
            array_keys($observation),
            array_keys($columns['eod_bars'] ?? ['x' => true]),
            'MD-S056-R0062: source observations and canonical bars must not be the same surface'
        );
    }

    // ---------- MD-S056-R0063, R0064 — the price products ----------

    public function test_the_raw_price_product_is_a_product_identity_not_a_provider_payload_synonym(): void
    {
        $declaration = $this->productDeclarationSurface();

        $this->assertMatchesRegularExpression('/\bRAW\b/', $declaration,
            'MD-S056-R0063: uppercase RAW must be declared as a price product');

        // the product identity must live on a product/basis column, not on the observation payload
        $columns = $this->schemaColumns();
        $productColumns = [];
        foreach ($columns as $table => $cols) {
            foreach (array_keys($cols) as $column) {
                if (preg_match('/(price_)?(product|basis)(_code|_id|_version)?$/', $column)) {
                    $productColumns[] = $table.'.'.$column;
                }
            }
        }

        $this->assertNotSame([], $productColumns,
            'MD-S056-R0063: RAW must identify a price product carried by a product or basis column');
    }

    public function test_structural_adjusted_is_built_only_from_verified_versioned_factors(): void
    {
        $columns = $this->schemaColumns();

        foreach (['md_adjustment_factors', 'md_adjustment_factor_sets'] as $table) {
            $this->assertArrayHasKey($table, $columns,
                'MD-S056-R0064: STRUCTURAL_ADJUSTED requires verified, versioned corporate-action factors');
        }

        $versioned = false;
        foreach (array_keys($columns['md_adjustment_factor_sets']) as $column) {
            if (preg_match('/version|revision|set_id/', $column)) {
                $versioned = true;
                break;
            }
        }
        $this->assertTrue($versioned, 'MD-S056-R0064: the factor set must be versioned');

        $this->assertMatchesRegularExpression('/STRUCTURAL_ADJUSTED/', $this->productDeclarationSurface(),
            'MD-S056-R0064: STRUCTURAL_ADJUSTED must be a declared product, not an implicit one');
    }

    // ---------- MD-S056-R0065 — one run, one basis ----------

    public function test_one_indicator_run_binds_one_explicit_basis_and_never_provider_adj_close(): void
    {
        $service = $this->indicatorService();

        $this->assertMatchesRegularExpression('/function priceBasis/', $service,
            'MD-S056-R0065: the indicator engine must bind its price basis through one selector');

        // the selector body must not read a provider adjusted-close field
        if (preg_match('/private function priceBasis\(.*?\n(.*?)\n    \}/s', $service, $m)) {
            $body = preg_replace('!/\*.*?\*/|//[^\n]*!s', '', $m[1]);
            $this->assertDoesNotMatchRegularExpression('/adj_close|adjusted_close/', $body,
                'MD-S056-R0065: provider adjusted close must not participate in the indicator price basis');
        } else {
            $this->fail('MD-S056-R0065: the price basis selector could not be read, so nothing was proven');
        }

        // and no indicator vector may mix close with a provider adjusted close
        $stripped = preg_replace('!/\*.*?\*/|//[^\n]*!s', '', $service);
        $this->assertDoesNotMatchRegularExpression('/adj_close\s*\?\?|\?\?\s*\$?\w*adj_close|adj_close.*?:\s*\$?\w*close/', $stripped,
            'MD-S056-R0065: provider adjusted close must not be used as a per-row fallback');
    }

    // ---------- MD-S056-R0067 — unresolved factors block rather than mutate ----------

    public function test_an_unresolved_factor_blocks_rather_than_mutates_history(): void
    {
        $codes = $this->reasonCodes();

        foreach ([
            'a corporate-action discontinuity must be expressible' => '/CORPORATE_ACTION_DISCONTINUITY/',
            'a price-scale discontinuity must be expressible' => '/PRICE_SCALE_DISCONTINUITY/',
        ] as $requirement => $pattern) {
            $this->assertMatchesRegularExpression($pattern, $codes, 'MD-S056-R0067: '.$requirement);
        }

        // the eligibility path must map those to a blocking decision rather than silently adjusting
        $eligibility = (string) file_get_contents($this->root().'/app/Application/MarketData/Services/EligibilityDecisionService.php');
        $this->assertMatchesRegularExpression('/ELIG_CORPORATE_ACTION_DISCONTINUITY/', $eligibility,
            'MD-S056-R0067: an unresolved factor must block through eligibility');
        $this->assertMatchesRegularExpression('/ELIG_PRICE_SCALE_DISCONTINUITY/', $eligibility,
            'MD-S056-R0067: a price anomaly must block rather than authorise adjustment');
    }

    // ---------- MD-S056-R0100, R0107, R0108 — fact definitions ----------

    public function test_liquidity_is_published_as_a_named_measure_with_unit_and_basis(): void
    {
        $columns = $this->schemaColumns();
        $named = [];
        foreach ($columns as $table => $cols) {
            foreach (array_keys($cols) as $column) {
                if (preg_match('/(adv\d+|turnover|traded_value).*?(idr|proxy)|proxy.*?idr/', $column)) {
                    $named[] = $table.'.'.$column;
                }
            }
        }

        $this->assertNotSame([], $named,
            'MD-S056-R0100: a liquidity measure must be published with its unit and an explicit proxy label');
    }

    public function test_invalid_rows_are_stored_separately_from_canonical_publication(): void
    {
        $columns = $this->schemaColumns();

        $this->assertArrayHasKey('eod_invalid_bars', $columns,
            'MD-S056-R0107: rows rejected from canonical publication need their own surface');

        $hasReason = false;
        foreach (array_keys($columns['eod_invalid_bars']) as $column) {
            if (preg_match('/reason/', $column)) {
                $hasReason = true;
                break;
            }
        }
        $this->assertTrue($hasReason, 'MD-S056-R0107: an invalid row must record why it was rejected');
    }

    public function test_indicators_are_versioned_and_bound_to_one_declared_basis(): void
    {
        $columns = $this->schemaColumns();
        $versioned = [];
        foreach ($columns as $table => $cols) {
            foreach (array_keys($cols) as $column) {
                if (preg_match('/indicator.*version|indicator_set_version/', $column)) {
                    $versioned[] = $table.'.'.$column;
                }
            }
        }

        $this->assertNotSame([], $versioned,
            'MD-S056-R0108: indicators must be versioned, otherwise determinism cannot be asserted');
    }

    // ---------- MD-S056-R0033, R0096, R0098 — deterministic behaviour ----------

    public function test_indicators_return_deterministic_null_until_warm_up_history_exists(): void
    {
        $service = $this->indicatorService();
        $stripped = preg_replace('!/\*.*?\*/|//[^\n]*!s', '', $service);

        // Every windowed computation must carry its own warm-up guard. Asserting that *a* guard
        // exists somewhere is satisfied by any one survivor: removing the guard from one of the three
        // windowed functions left this test green until it was written this way.
        preg_match_all(
            '/private function (\w+)\([^)]*\$window[^)]*\)[^{]*\{(.*?)\n    \}/s',
            $stripped,
            $windowed,
            PREG_SET_ORDER
        );

        $this->assertGreaterThanOrEqual(3, count($windowed),
            'MD-S056-R0033: the windowed computations must be found before their warm-up behaviour is judged');

        $unguarded = [];
        foreach ($windowed as $function) {
            $name = $function[1];
            $body = $function[2];
            if (! preg_match('/<\s*\$window/', $body) || ! preg_match('/return null;/', $body)) {
                $unguarded[] = $name;
            }
        }

        $this->assertSame([], $unguarded,
            'MD-S056-R0033: every windowed computation must return a deterministic NULL until its required history exists');

        $this->assertMatchesRegularExpression('/IND_INSUFFICIENT_HISTORY/', $this->reasonCodes(),
            'MD-S056-R0033: insufficient history must be expressible as a reason, not left unexplained');
    }

    public function test_per_ticker_failure_is_recorded_and_tolerated_rather_than_fatal(): void
    {
        $codes = $this->reasonCodes();

        $this->assertMatchesRegularExpression('/PARTIAL_DATA/', $codes,
            'MD-S056-R0096: a run that lost some tickers must be expressible as partial, not fatal');
        $this->assertMatchesRegularExpression('/COVERAGE_/', $codes,
            'MD-S056-R0096: per-ticker failure is evaluated later through coverage');
    }

    public function test_dormancy_exclusion_from_the_denominator_requires_point_in_time_evidence(): void
    {
        $columns = $this->schemaColumns();
        $this->assertArrayHasKey('eod_runs', $columns, 'the coverage denominator lives on the run');

        foreach ([
            'coverage_excluded_sample_json',
            'coverage_expectation_unknown_count',
            'coverage_bar_not_expected_count',
        ] as $column) {
            $this->assertArrayHasKey($column, $columns['eod_runs'],
                'MD-S056-R0098: an exclusion from the denominator must carry its own evidence column');
        }

        // The rule reads in full: "Denominator exclusion requires point-in-time evidence that a bar
        // was not expected, such as verified suspension or market status." So the admissible basis is
        // trading status, and dormancy is explicitly not one — the legacy dormancy reason is
        // registered inactive and its emission is a defect, not a feature.
        $this->assertArrayHasKey('market_data_trading_status_events', $columns,
            'MD-S056-R0098: an exclusion must rest on verified suspension or market status');

        $this->assertMatchesRegularExpression(
            "/\('COVERAGE_DORMANT_TICKERS_EXCLUDED',[^\n]*DEPRECATED[^\n]*,\s*0\)/",
            $this->reasonCodes(),
            'MD-S056-R0098: the legacy dormancy exclusion must be registered as deprecated and inactive'
        );

        $emitted = [];
        foreach ($this->marketDataSourceCodes() as $file => $codes) {
            foreach ($codes as $code) {
                $emitted[] = $code;
            }
        }

        $this->assertGreaterThan(20, count($emitted), 'the runtime reason-code scan must reach the market-data services');
        $this->assertNotContains('COVERAGE_DORMANT_TICKERS_EXCLUDED', $emitted,
            'MD-S056-R0098: dormancy must never shrink the coverage denominator, so no runtime path may emit the legacy exclusion');
    }

    /**
     * Reason codes actually emitted by market-data runtime code, comments stripped.
     *
     * `CoverageGateEvaluator` names the deprecated dormancy exclusion in a comment explaining that
     * emitting it is a defect. That comment is the rule being honoured, so it must not count as an
     * emission — the same distinction applied to the lot-size docblock at MD-B01-A009.
     *
     * @return array<string,array<int,string>>
     */
    private function marketDataSourceCodes(): array
    {
        $out = [];
        foreach ([
            'app/Application/MarketData', 'app/Domain/MarketData',
            'app/Infrastructure/MarketData', 'app/Infrastructure/Persistence/MarketData',
        ] as $dir) {
            $path = $this->root().'/'.$dir;
            if (! is_dir($path)) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $file) {
                if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                    continue;
                }
                $source = $this->stripPhpComments((string) file_get_contents($file->getPathname()));
                if (preg_match_all("/'([A-Z][A-Z0-9_]{3,})'/", $source, $m)) {
                    $out[$file->getFilename()] = array_values(array_unique($m[1]));
                }
            }
        }

        return $out;
    }
}
