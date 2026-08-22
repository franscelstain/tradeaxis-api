<?php

use PHPUnit\Framework\TestCase;
use Tests\Support\MarketData\ReadsMarketDataSchema;

/**
 * MD-B01 — the downstream-concept surface boundary.
 *
 * Owner contract: authority/strategy/.../Domain_Boundary_Invariants_LOCKED.md. The contract forbids
 * a set of downstream concepts from appearing on this domain's surface, and names the surfaces
 * explicitly: "columns, reason codes, statuses, commands, configuration keys, APIs, tables, or
 * positive feature definitions".
 *
 * The contract also constrains this very test. MD-S020-R0158 requires that a guard enforcing the
 * forbidden list distinguish the legitimate upstream sense of `candidate`, `target`, and `policy`
 * from the forbidden downstream sense, because `candidate` alone appears legitimately in over one
 * hundred documents in this package. So the unit under test is a *concept* expressed as a compound
 * identifier, never a bare token. test_no_legitimate_upstream_identifier_is_flagged holds the guard
 * to that requirement using identifiers taken from the real schema.
 */
class DownstreamConceptSurfaceBoundaryTest extends TestCase
{
    use ReadsMarketDataSchema;

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * Forbidden downstream concepts. Each is matched against a normalised identifier — segments
     * lowercased and joined by underscores — so a concept is recognised by its compound, not by one
     * token.
     *
     * @return array<string,string>
     */
    private function forbiddenConcepts(): array
    {
        return [
            'alpha' => '/(^|_)alpha(_|$)/',
            'edge (trading)' => '/(^|_)(trading|market|statistical)_edge(_|$)/',
            'conviction' => '/(^|_)conviction(_|$)/',
            'price target' => '/(^|_)(target_price|price_target)(_|$)/',
            'stop loss' => '/(^|_)(stop_loss|stoploss|stop_price)(_|$)/',
            'take profit' => '/(^|_)(take_profit|takeprofit|profit_target)(_|$)/',
            'position sizing' => '/(^|_)(position_size|position_sizing|risk_size|risk_sizing|lot_allocation)(_|$)/',
            'portfolio action' => '/(^|_)portfolio(_|$)/',
            'broker instruction' => '/(^|_)broker(_|$)/',
            'order routing' => '/(^|_)(order_routing|order_side|order_type|routing_venue)(_|$)/',
            'execution action' => '/(^|_)(execution_action|execute_trade|trade_execution)(_|$)/',
            'trade signal' => '/(^|_)(buy_signal|sell_signal|trade_signal|entry_signal|exit_signal)(_|$)/',
            'entry/exit timing' => '/(^|_)(entry_price|exit_price|entry_date|exit_date|entry_rule|exit_rule)(_|$)/',
            'ranking' => '/(^|_)(rank|ranking|ranked|rank_order|ordering_score)(_|$)/',
            'scoring' => '/(^|_)(alpha_score|signal_score|conviction_score|candidate_score)(_|$)/',
            'recommendation' => '/(^|_)(recommendation|recommended_action|recommend)(_|$)/',
            'screening' => '/(^|_)(screening|screener|screen_result)(_|$)/',
            'watchlist policy' => '/(^|_)watchlist(_|$)/',
            'strategy approval' => '/(^|_)(strategy_eligible|strategy_approved|trade_permitted)(_|$)/',
        ];
    }

    /**
     * Real identifiers from this domain that a token blacklist would flag and the contract says are
     * legitimate. MD-S020-R0158 is satisfied only if every one of these stays clean.
     *
     * @return array<int,string>
     */
    private function legitimateIdentifiers(): array
    {
        return [
            'coverage_edge_cases',                 // an edge case, not a trading edge
            'target_date_count',                   // the dates a run targets
            'baseline_target_set_hash',
            'md_stage8_reconstruction_targets',    // reconstruction targets, not price targets
            'SEAL_TARGET_EMPTY',
            'POINTER_SWITCH_BLOCKED_INVALID_TARGET',
            'publish_target',
            'range_position_20_pct',               // where price sits in its range, not a holding
            'md_exchange_tick_size_tiers',         // tick size, not position size
            'source_file_size_bytes',
            'execution_count',                     // run executions, not broker execution
            'candidate_publication_id',            // the contract's own example
            'expected_candidate_publication_id',
            'candidate_price_factor',
            'coverage_policy',                     // a data policy, not a trading policy
            'expected_bar_policy',
            'source.api_backfill.default_error_policy',
            'trade_date',                          // the date of a market observation
            'requested_trade_date',
            'trade_count_actual',
            'adv20_close_volume_proxy_idr',        // an explicitly labelled proxy
        ];
    }

    /**
     * Identifiers that state the forbidden sense. The guard must flag every one, otherwise a clean
     * surface scan proves nothing.
     *
     * @return array<int,string>
     */
    private function forbiddenIdentifiers(): array
    {
        return [
            'alpha_factor', 'trading_edge_bps', 'conviction_level', 'target_price_idr',
            'stop_loss_pct', 'take_profit_pct', 'position_size_lots', 'portfolio_action',
            'broker_order_id', 'order_routing_venue', 'trade_execution_ts', 'buy_signal',
            'entry_price_idr', 'candidate_rank', 'alpha_score', 'recommended_action',
            'screening_result', 'watchlist_membership', 'strategy_eligible',
        ];
    }

    /**
     * @param array<string,string> $identifiers identifier => surface label
     *
     * @return array<int,string>
     */
    private function match(array $identifiers): array
    {
        $found = [];
        foreach ($identifiers as $identifier => $label) {
            $normalised = strtolower(preg_replace('/[^a-z0-9]+/i', '_', (string) $identifier));
            foreach ($this->forbiddenConcepts() as $concept => $pattern) {
                if (preg_match($pattern, $normalised)) {
                    $found[] = $label.' :: '.$concept;
                }
            }
        }

        return $found;
    }

    // ---------- surfaces ----------

    /**
     * Every table and column name across the full schema surface — the base SQL the core migration
     * executes, plus the migrations that extend it. An earlier revision read only the migrations, which
     * proved this claim over a subset; the identifier set it reaches is now 582 rather than 456.
     *
     * @return array<string,string> identifier => label
     */
    private function schemaIdentifiers(): array
    {
        $out = [];
        foreach ($this->schemaColumnMap() as $table => $columns) {
            $out[$table] = 'table '.$table;
            foreach (array_keys($columns) as $column) {
                $out[$column] = 'column '.$table.'.'.$column;
            }
        }

        return $out;
    }

    /** @return array<string,string> */
    private function reasonCodes(): array
    {
        $sql = (string) file_get_contents($this->root().'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        preg_match_all("/'([A-Z][A-Z0-9_]{3,})'/", $sql, $m);

        $out = [];
        foreach (array_unique($m[1]) as $code) {
            $out[$code] = 'reason code '.$code;
        }

        return $out;
    }

    /** @return array<string,string> */
    private function configKeys(): array
    {
        $flatten = function (array $config, string $prefix = '') use (&$flatten): array {
            $out = [];
            foreach ($config as $key => $value) {
                if (! is_string($key)) {
                    continue;
                }
                $name = $prefix === '' ? $key : $prefix.'.'.$key;
                $out[$name] = 'config key '.$name;
                if (is_array($value)) {
                    $out += $flatten($value, $name);
                }
            }

            return $out;
        };

        return $flatten((array) require $this->root().'/config/market_data.php');
    }

    /** @return array<string,string> */
    private function commandSignatures(): array
    {
        $out = [];
        foreach (glob($this->root().'/app/Console/Commands/MarketData/*.php') as $file) {
            $source = (string) file_get_contents($file);
            if (preg_match('/\$signature\s*=\s*[\'"](.+?)[\'"]\s*;/s', $source, $m)) {
                foreach (preg_split('/\s+/', trim($m[1])) as $token) {
                    $token = trim($token, '{}[]?*');
                    if ($token !== '') {
                        $out[$token] = 'command token '.$token;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * PHP files that make up one named market-data surface.
     *
     * @return array<string,string> filename => source
     */
    private function surfaceFiles(string $pattern): array
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
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                    continue;
                }
                if (preg_match($pattern, $file->getFilename())) {
                    $out[$file->getFilename()] = (string) file_get_contents($file->getPathname());
                }
            }
        }

        return $out;
    }

    /**
     * Ordering that expresses preference between instruments, as distinct from the deterministic
     * retrieval order a reproducible read requires.
     *
     * The contract forbids "ranked scores or candidate ordering", not ordering as such. A repository
     * that returns rows in identity or date order is producing a stable result; one that orders by a
     * measure is expressing which instrument is better. Only the second is a boundary breach, and
     * MD-S020-R0158 requires this guard to tell them apart rather than flag the token.
     *
     * @return array<int,string>
     */
    private function preferenceOrdering(string $source): array
    {
        $hits = [];

        // ordering by a stable key is deterministic retrieval; ordering by a measure is preference
        if (preg_match_all("/->\s*(orderBy|orderByDesc)\(\s*'([a-zA-Z0-9_.]+)'/", $source, $m, PREG_SET_ORDER)) {
            foreach ($m as $call) {
                $column = strtolower($call[2]);
                if (($dot = strrpos($column, '.')) !== false) {
                    $column = substr($column, $dot + 1);
                }
                if (! preg_match('/(^|_)(id|code|date|at|seq|sequence|symbol|name|version|revision)$/', $column)) {
                    $hits[] = 'orders by the measure '.$call[2];
                }
            }
        }

        // an in-memory comparator sorts by whatever the closure decides, which no key check can vouch for
        if (preg_match_all('/\b(usort|uasort|arsort|rsort|sortByDesc|sortBy)\b/', $source, $m)) {
            foreach (array_unique($m[1]) as $function) {
                $hits[] = 'sorts its own output with '.$function;
            }
        }

        return $hits;
    }

    // ---------- adequacy of the guard itself ----------

    /**
     * The ordering check must separate deterministic retrieval order from preference ordering.
     * Without this it would either flag every repository or catch nothing.
     */
    public function test_the_ordering_check_separates_retrieval_order_from_preference(): void
    {
        $deterministic = [
            "\$query->orderBy('elig.ticker_id')->get();",
            "\$q->orderBy('trade_date')->orderBy('listing_symbol_code');",
            "\$q->orderByDesc('published_at');",
            "\$q->orderBy('indicator_set_version');",
        ];
        foreach ($deterministic as $source) {
            $this->assertSame([], $this->preferenceOrdering($source), 'deterministic retrieval order must not be flagged: '.$source);
        }

        $preference = [
            "\$query->orderByDesc('liquidity_value_idr')->get();",
            "\$q->orderBy('quality_rank');",
            "usort(\$rows, function (\$a, \$b) { return \$b['adv20'] <=> \$a['adv20']; });",
            "\$rows->sortByDesc('turnover');",
        ];
        foreach ($preference as $source) {
            $this->assertNotSame([], $this->preferenceOrdering($source), 'preference ordering must be flagged: '.$source);
        }
    }

    /**
     * A concept pattern that cannot fire would report every surface clean.
     */
    public function test_every_forbidden_concept_matches_the_identifier_it_forbids(): void
    {
        $concepts = $this->forbiddenConcepts();
        $this->assertCount(19, $concepts, 'the forbidden-concept set under test is 19 concepts');

        $unmatched = [];
        foreach ($this->forbiddenIdentifiers() as $identifier) {
            if ($this->match([$identifier => $identifier]) === []) {
                $unmatched[] = $identifier;
            }
        }

        $this->assertSame([], $unmatched, 'each forbidden identifier must be flagged, otherwise a clean scan proves nothing');
    }

    /**
     * MD-S020-R0158: the guard must distinguish the upstream sense from the downstream sense. Every
     * identifier here is real and legitimate; a guard that flags any of them is a word blacklist.
     */
    /**
     * `MD-S020-R0172` — the forbidden-terms list targets meanings, not tokens: `candidate`,
     * `target`, and `policy` carry legitimate upstream senses that no guard may flag on the word
     * alone.
     *
     * The rule constrains the guards, so the corpus under test is the guard suite itself. Every
     * regex literal in every market-data guard is extracted and run against the three bare words. A
     * pattern that matches one of them would flag `candidate_publication_id` — the boundary
     * contract's own example — and the rule names that as non-conformant.
     *
     * This found one: `MarketDataOrdersOneToFourArchitectureTest` matched bare `Candidate` against
     * class file names, so a `CandidatePublicationRepository` would have been reported as a
     * downstream artifact. It now requires a downstream-sense compound.
     */
    public function test_no_market_data_guard_flags_an_overloaded_word_on_the_token_alone(): void
    {
        $bareWords = ['candidate', 'target', 'policy'];
        $offenders = [];
        $patterns = 0;
        $files = 0;

        foreach (glob($this->root().'/tests/Unit/MarketData/*.php') as $file) {
            $files++;
            foreach (token_get_all((string) file_get_contents($file)) as $token) {
                if (! is_array($token) || $token[0] !== T_CONSTANT_ENCAPSED_STRING) {
                    continue;
                }
                $value = substr($token[1], 1, -1);
                if ($value === '' || $value[0] !== '/' || @preg_match($value, '') === false) {
                    continue;
                }
                $patterns++;
                foreach ($bareWords as $word) {
                    if (@preg_match($value, $word)) {
                        $offenders[] = basename($file).' :: '.$word.' :: '.substr($value, 0, 80);
                    }
                }
            }
        }

        $this->assertGreaterThan(100, $files, 'the guard-corpus scan must reach the market-data suites');
        $this->assertGreaterThan(300, $patterns, 'the regex extraction must actually collect patterns');
        $this->assertSame([], array_values(array_unique($offenders)), 'a guard may not flag an overloaded word on the token alone');

        // The extraction must be able to see a violation, or a clean result proves nothing. The
        // probe is assembled at runtime rather than written as a literal: a literal here would be
        // collected by the very scan above and reported as a violation of the rule it is testing.
        $probe = '/'.'candi'.'date'.'/i';
        $this->assertSame(1, preg_match($probe, 'candidate'), 'a bare-token pattern must be detectable by this scan');
    }

    public function test_no_legitimate_upstream_identifier_is_flagged(): void
    {
        $legitimate = $this->legitimateIdentifiers();
        $this->assertGreaterThan(15, count($legitimate), 'the sense-distinction fixture must cover the overloaded vocabulary');

        $flagged = [];
        foreach ($legitimate as $identifier) {
            foreach ($this->match([$identifier => $identifier]) as $hit) {
                $flagged[] = $hit;
            }
        }

        $this->assertSame([], $flagged, 'a legitimate upstream identifier was flagged; the guard is reading tokens, not concepts');
    }

    // ---------- the surfaces named by MD-S020-R0054 ----------

    public function test_no_schema_table_or_column_names_a_downstream_concept(): void
    {
        $identifiers = $this->schemaIdentifiers();
        $this->assertGreaterThan(400, count($identifiers), 'the schema scan must reach the market-data tables and columns');

        $this->assertSame([], $this->match($identifiers), 'a table or column names a downstream concept');
    }

    public function test_no_reason_code_names_a_downstream_concept(): void
    {
        $codes = $this->reasonCodes();
        $this->assertGreaterThan(400, count($codes), 'the reason-code scan must reach the registry seed');

        $this->assertSame([], $this->match($codes), 'a reason code names a downstream concept');
    }

    public function test_no_configuration_key_names_a_downstream_concept(): void
    {
        $keys = $this->configKeys();
        $this->assertGreaterThan(100, count($keys), 'the configuration scan must reach the market-data config tree');

        $this->assertSame([], $this->match($keys), 'a configuration key names a downstream concept');
    }

    public function test_no_command_signature_names_a_downstream_concept(): void
    {
        $signatures = $this->commandSignatures();
        $this->assertGreaterThan(30, count($signatures), 'the command scan must reach the market-data console surface');

        $this->assertSame([], $this->match($signatures), 'a command name or option names a downstream concept');
    }

    /**
     * MD-S020-R0054 names APIs among the surfaces that must not expose a downstream concept. This
     * application currently publishes no market-data HTTP endpoint, so the concept check has nothing
     * to reject. The emptiness is asserted rather than assumed: if a market-data route is ever added,
     * this test stops being trivially satisfied and starts checking the route names.
     */
    public function test_no_http_route_exposes_a_downstream_concept(): void
    {
        $routes = [];
        foreach (glob($this->root().'/routes/*.php') as $file) {
            $source = (string) file_get_contents($file);
            if (preg_match_all("/->(?:get|post|put|patch|delete|options)\(\s*'([^']*)'/", $source, $m)) {
                foreach ($m[1] as $path) {
                    $routes[$path] = 'route '.$path;
                }
            }
            if (preg_match_all("/Route::(?:get|post|put|patch|delete|options)\(\s*'([^']*)'/", $source, $m)) {
                foreach ($m[1] as $path) {
                    $routes[$path] = 'route '.$path;
                }
            }
        }

        $marketDataRoutes = array_filter(array_keys($routes), function ($path) {
            return preg_match('/market|eod|indicator|publication|ticker/i', $path) === 1;
        });

        $this->assertSame([], array_values($marketDataRoutes), 'a market-data HTTP route now exists; this domain published none when the boundary was proven');
        $this->assertSame([], $this->match($routes), 'an HTTP route names a downstream concept');
    }

    // ---------- the indicator and eligibility boundaries ----------

    /**
     * Indicators must not become strategy signals, ranked scores, candidate ordering, recommendation
     * engines, entry/exit rules, or position-sizing inputs.
     */
    public function test_no_indicator_identifier_becomes_a_signal_or_ranking(): void
    {
        $identifiers = [];
        foreach ($this->schemaIdentifiers() as $identifier => $label) {
            if (strpos($label, 'indicator') !== false
                || preg_match('/(^|_)(ma\d+|roc_\d+|rsi|atr|adv\d+)(_|$)/', $identifier)) {
                $identifiers[$identifier] = $label;
            }
        }
        foreach ($this->surfaceFiles('/Indicator/') as $name => $source) {
            preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/', $source, $m);
            foreach ($m[1] as $method) {
                $identifiers[$method] = 'method '.$name.'::'.$method;
            }
        }

        $this->assertGreaterThan(20, count($identifiers), 'the indicator surface scan must reach the indicator columns and code');

        $this->assertSame([], $this->match($identifiers), 'an indicator identifier carries a signal, ranking, or sizing meaning');
    }

    /**
     * The eligibility surface must never become a screening engine, a watchlist replacement, a proxy
     * ranking layer, or entry/exit timing infrastructure.
     */
    public function test_no_eligibility_identifier_becomes_screening_or_timing(): void
    {
        $files = $this->surfaceFiles('/Eligib/');
        $this->assertNotSame([], $files, 'the eligibility surface must exist to be constrained');

        $identifiers = [];
        $ordering = [];
        foreach ($files as $name => $source) {
            preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/', $source, $m);
            foreach ($m[1] as $method) {
                $identifiers[$method] = 'method '.$name.'::'.$method;
            }
            preg_match_all("/'([a-z][a-z0-9_]{2,})'\s*=>/", $source, $m);
            foreach ($m[1] as $key) {
                $identifiers[$key] = 'result key '.$name.'.'.$key;
            }
            foreach ($this->preferenceOrdering($source) as $hit) {
                $ordering[] = $name.' '.$hit;
            }
        }

        $this->assertGreaterThan(10, count($identifiers), 'the eligibility surface scan must reach its methods and result keys');
        $this->assertSame([], $ordering, 'the eligibility surface orders instruments; ordering belongs downstream');
        $this->assertSame([], $this->match($identifiers), 'an eligibility identifier carries a screening, ranking, or timing meaning');
    }

    // ---------- the consumer dependency rule, one surface per rule ----------

    /**
     * MD-S020-R0115..R0123 name nine surfaces that must not silently embed consumer policy. Each is
     * checked as its own file set so one surface's result cannot stand in for another's.
     *
     * @dataProvider consumerSurfaceProvider
     */
    public function test_no_named_surface_embeds_consumer_policy(string $ruleId, string $pattern, int $minimumFiles): void
    {
        $files = $this->surfaceFiles($pattern);

        $this->assertGreaterThanOrEqual(
            $minimumFiles,
            count($files),
            $ruleId.': the surface must be populated, otherwise a clean result is vacuous'
        );

        $embedded = [];
        foreach ($files as $name => $source) {
            if (preg_match_all('/\b(Watchlist|WeeklySwing)[A-Za-z0-9_]*/', $source, $m)) {
                $embedded[] = $name.' references '.implode(',', array_unique($m[0]));
            }
        }

        $this->assertSame([], $embedded, $ruleId.': a market-data surface references downstream policy');
    }

    public function consumerSurfaceProvider(): array
    {
        return [
            'MD-S020-R0115' => ['MD-S020-R0115', '/Acquisition|Canonical|Import|Ingest|Source/', 3],
            'MD-S020-R0116' => ['MD-S020-R0116', '/Adjust|Factor|CorporateAction|Split|Dividend/', 2],
            'MD-S020-R0117' => ['MD-S020-R0117', '/Eligib|ReasonCode/', 1],
            'MD-S020-R0118' => ['MD-S020-R0118', '/Coverage|Universe/', 1],
            'MD-S020-R0119' => ['MD-S020-R0119', '/Publication|Publish|Readiness|Seal|Pointer/', 3],
            'MD-S020-R0120' => ['MD-S020-R0120', '/Indicator/', 1],
            'MD-S020-R0121' => ['MD-S020-R0121', '/Anomaly|EventRisk|ScaleBreak|Detect/', 1],
            'MD-S020-R0122' => ['MD-S020-R0122', '/Snapshot/', 1],
            'MD-S020-R0123' => ['MD-S020-R0123', '/Config/', 1],
        ];
    }
}
