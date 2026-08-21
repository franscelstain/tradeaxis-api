<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Domain\MarketData\MarketDataScope;
use PHPUnit\Framework\TestCase;

/**
 * MD-B01 — scope, boundary, dataset start, development frontier, activation semantics, non-goals.
 *
 * Stage exit intent: "tidak ada ambiguity market, product, time boundary, atau kebocoran policy
 * watchlist."
 *
 * Owner contracts:
 *   authority/strategy/MARKET_DATA_PLATFORM_EOD_BASELINE.md
 *   authority/strategy/book/Terminology_and_Scope.md
 *   authority/strategy/book/Domain_Boundary_Invariants_LOCKED.md
 *
 * Every check here executes the rule against real code, real config, the real canonical schema, and
 * the real command surface. None of them asserts that a document contains a sentence.
 *
 * The population assertions matter as much as the violation assertions. A guard that scans an empty
 * set reports zero violations and looks identical to a guard that scans everything, which is how a
 * check keeps passing long after it stopped inspecting anything.
 */
class ScopeProductAndTimeBoundaryTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    /**
     * Policy vocabulary is matched by meaning, not by token, and covers all ten categories the
     * boundary contract enumerates: screening, ranking/score/alpha, conviction, buy/sell intent,
     * entry, exit/stop/target, sizing/risk budget, portfolio, expected return, and broker/routing.
     *
     * Morphological variants are included deliberately. `ranked_picks` is one of the contract's own
     * forbidden examples and a bare `rank` alternative does not match it.
     */
    private const POLICY_VOCABULARY = '/(^|_)('
        .'screen|screening|screener'
        .'|rank|ranked|ranking|rankings'
        .'|score|scored|scoring'
        .'|alpha|conviction'
        .'|buy|sell'
        .'|entry|exit'
        .'|stoploss|stop|takeprofit|profit|profitability'
        .'|target_price|targetprice'
        .'|position_size|position_sizing|sizing'
        .'|risk_budget|capital_allocation'
        .'|portfolio|rebalance|rebalancing'
        .'|expected_return|pnl'
        .'|broker|order_routing|routing'
        .'|watchlist|tradability'
        .'|recommend|recommendation|recommendations|pick|picks'
        .')($|_)/i';

    /**
     * `candidate`, `signal`, `execution`, `long`, `short`, `target`, and `position` carry legitimate
     * upstream meanings — publication candidate state, run execution, long suspension,
     * `range_position_20_pct` — so banning the bare token would be wrong. The boundary contract
     * requires such a guard to check the surrounding contract rather than the token, which is what
     * this compound pattern does: the policy qualifier, not the overloaded noun, is the signal.
     */
    private const POLICY_COMPOUND = '/(^|_)(trade|buy|sell|strategy|policy)_(candidate|candidates|signal|signals|list|lists|pick|picks)($|_)/i';

    private function violatesPolicyBoundary(string $name): bool
    {
        return (bool) preg_match(self::POLICY_VOCABULARY, $name)
            || (bool) preg_match(self::POLICY_COMPOUND, $name);
    }

    /**
     * The vocabulary above is the load-bearing part of every naming guard in this class. If it were
     * too narrow, all of them would report clean while policy names sat in the schema — so its
     * adequacy is asserted here rather than assumed, against the contract's own two example lists.
     */
    public function test_the_policy_vocabulary_matches_the_contract_examples_in_both_directions(): void
    {
        foreach (['trade_candidates', 'entry_signals', 'ranked_picks', 'buy_watchlist', 'position_recommendations'] as $forbidden) {
            $this->assertTrue($this->violatesPolicyBoundary($forbidden), $forbidden.' is a forbidden example and must be caught');
        }

        foreach (['eod_bars', 'eod_indicators', 'eod_eligibility', 'eod_publications', 'market_data_read_product_v1', 'session_snapshots'] as $valid) {
            $this->assertFalse($this->violatesPolicyBoundary($valid), $valid.' is a valid upstream name and must not be flagged');
        }

        foreach (['candidate_publication_id', 'publication_candidate', 'trade_date', 'trade_count_actual', 'traded_value_idr_actual'] as $overloaded) {
            $this->assertFalse($this->violatesPolicyBoundary($overloaded), $overloaded.' uses an overloaded word in its upstream sense and must not be flagged');
        }
    }

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        parent::tearDown();
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function readCanonicalArtifact(string $relative): string
    {
        $path = $this->root().'/'.$relative;

        $this->assertFileExists($path, $relative.' must resolve; a dead documentation path silently disables this check');

        return (string) file_get_contents($path);
    }

    // ---------------------------------------------------------------- market scope

    /**
     * The canonical scope is IDX Regular-Market EOD. Each of the three identity fields is widened
     * independently, because a guard that only rejects one of them leaves the other two open.
     *
     * @dataProvider scopeDriftProvider
     */
    public function test_canonical_market_scope_fails_closed_on_every_identity_field(string $field, string $value): void
    {
        $this->bindMarketDataConfig(['market_data' => ['scope' => [$field => $value]]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MARKET_DATA_SCOPE_INVALID/');

        MarketDataScope::fromConfig();
    }

    public function scopeDriftProvider(): array
    {
        return [
            'negotiated market' => ['market_segment', 'NEGOTIATED'],
            'cash market' => ['market_segment', 'CASH'],
            'foreign exchange' => ['market_code', 'NYSE'],
            'intraday frequency' => ['frequency', 'INTRADAY'],
        ];
    }

    /**
     * Both timezone keys must be `Asia/Jakarta`, and they must agree. A platform clock that drifts
     * from the scope clock changes which calendar day a bar belongs to without changing any value,
     * which is the kind of defect that survives every arithmetic check.
     *
     * @dataProvider timezoneDriftProvider
     */
    public function test_timezone_fails_closed_when_either_key_drifts(array $overrides): void
    {
        $this->bindMarketDataConfig(['market_data' => $overrides]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MARKET_DATA_TIMEZONE_INVALID/');

        MarketDataScope::fromConfig();
    }

    public function timezoneDriftProvider(): array
    {
        return [
            'scope clock moved' => [['scope' => ['timezone' => 'UTC']]],
            'platform clock moved' => [['platform' => ['timezone' => 'UTC']]],
            'both moved together' => [['scope' => ['timezone' => 'UTC'], 'platform' => ['timezone' => 'UTC']]],
        ];
    }

    /**
     * Case normalisation is intended behaviour, not drift. Recording it keeps a later reader from
     * "fixing" the strtoupper and turning a lowercase config into a scope violation.
     */
    public function test_scope_identity_is_case_normalised_rather_than_rejected(): void
    {
        $this->bindMarketDataConfig(['market_data' => ['scope' => [
            'market_code' => 'idx', 'market_segment' => 'regular', 'frequency' => 'eod',
        ]]]);

        $this->assertSame('Asia/Jakarta', MarketDataScope::fromConfig()->timezone());
    }

    // ---------------------------------------------------------------- time boundaries

    /**
     * `2023-01-02` is an intentional dataset start, not a source limit. A request before it is
     * refused with a named reason rather than answered with an empty result, because an empty
     * result is indistinguishable from a provider failure.
     */
    public function test_requests_before_the_intentional_dataset_start_are_refused_by_name(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        $this->assertSame('2023-01-02', $scope->assertRequestedDate('2023-01-02'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MARKET_DATA_REQUEST_BEFORE_DATASET_START/');

        $scope->assertRequestedDate('2022-12-30');
    }

    /**
     * Operational activation is the fourth and most abusable of the four time boundaries. With no
     * governed marker set, no date may report as operational — including dates already backfilled,
     * already proven, and already published.
     */
    public function test_without_a_governed_marker_no_date_is_operationally_activated(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        $this->assertNull($scope->operationalStartDate());

        foreach (['2023-01-02', '2024-06-14', '2026-07-07', '2026-08-20'] as $date) {
            $this->assertFalse($scope->isOperationallyActivatedFor($date), $date.' must not be operational without a marker');
            $this->assertSame('DEVELOPMENT', $scope->stateFor($date), $date.' must report the development frontier');
        }
    }

    /**
     * The marker, once set, splits the timeline at exactly one point and is inclusive of itself.
     */
    public function test_the_activation_marker_splits_the_timeline_at_exactly_one_point(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02', '2026-01-01');

        $this->assertSame('DEVELOPMENT', $scope->stateFor('2025-12-31'));
        $this->assertSame('OPERATIONAL', $scope->stateFor('2026-01-01'));
        $this->assertSame('OPERATIONAL', $scope->stateFor('2026-08-20'));
    }

    /**
     * Activation cannot be backdated behind the dataset start. Freshness obligations over a range
     * the platform never intended to hold would be unsatisfiable by construction.
     */
    public function test_activation_cannot_be_backdated_behind_the_dataset_start(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MARKET_DATA_OPERATIONAL_START_DATE_INVALID/');

        new MarketDataScope('Asia/Jakarta', '2023-01-02', '2022-12-31');
    }

    /**
     * The marker reaches the domain from exactly one place: governed configuration. This walks the
     * real source tree and asserts that no service, command, migration, or repository assigns the
     * config key — a backfill or proof run must never be able to imply activation.
     */
    public function test_no_runtime_path_assigns_the_activation_marker(): void
    {
        $assignments = [];
        $inspected = 0;

        foreach (['app', 'database'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/'.$dir, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                    continue;
                }
                $inspected++;
                foreach (preg_split('/\R/', (string) file_get_contents($file->getPathname())) as $number => $line) {
                    // config()->set(...) or Config::set(...) targeting the activation marker
                    if (preg_match('/(config\(\)\s*->\s*set|Config::set)\s*\(\s*[\'"]market_data\.scope\.operational_start_date/', $line)) {
                        $assignments[] = $file->getFilename().':'.($number + 1);
                    }
                }
            }
        }

        $this->assertGreaterThan(100, $inspected, 'the source scan must actually reach the application tree');
        $this->assertSame([], $assignments, 'operational activation must come from governed config alone');
    }

    /**
     * A requested date is a domain input in one shape. Accepting a locale shape would let the same
     * string mean two different days depending on who sent it.
     */
    public function test_a_requested_date_must_be_iso_shaped(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MARKET_DATA_REQUESTED_DATE_INVALID/');

        $scope->assertRequestedDate('02/01/2023');
    }

    // ---------------------------------------------------------------- policy leakage

    /**
     * `Domain_Boundary_Invariants_LOCKED.md` forbids policy appearing as columns, reason codes,
     * statuses, commands, configuration keys, APIs, or tables. Configuration keys are already
     * covered by `TerminologyOwnerVocabularyTest`; the persisted surfaces are covered here.
     */
    public function test_no_canonical_table_or_column_carries_policy_vocabulary(): void
    {
        $sql = $this->readCanonicalArtifact('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)?\s+`?([A-Za-z0-9_]+)`?\s*\((.*?)\n\)\s*ENGINE/is', $sql, $tables, PREG_SET_ORDER);

        $violations = [];
        $columnCount = 0;

        foreach ($tables as $table) {
            if ($this->violatesPolicyBoundary($table[1])) {
                $violations[] = 'TABLE '.$table[1];
            }

            foreach (preg_split('/\R/', $table[2]) as $line) {
                if (! preg_match('/^\s+`?([A-Za-z_][A-Za-z0-9_]*)`?\s+[A-Za-z]/', $line, $column)) {
                    continue;
                }
                if (preg_match('/^(PRIMARY|UNIQUE|KEY|INDEX|CONSTRAINT|FOREIGN|FULLTEXT|SPATIAL|CHECK)$/i', $column[1])) {
                    continue;
                }
                $columnCount++;
                if ($this->violatesPolicyBoundary($column[1])) {
                    $violations[] = $table[1].'.'.$column[1];
                }
            }
        }

        $this->assertGreaterThan(20, count($tables), 'the schema parse must reach the canonical tables');
        $this->assertGreaterThan(500, $columnCount, 'the schema parse must reach the canonical columns');
        $this->assertSame([], $violations, 'canonical schema must stay upstream-neutral');
    }

    /**
     * The forbidden list names statuses alongside columns and tables. A status lives in an enum
     * body rather than in an identifier, so a guard that only reads column names would report clean
     * while `status ENUM('RANKED','REJECTED')` sat in the schema.
     */
    public function test_no_status_enum_value_carries_policy_vocabulary(): void
    {
        $sql = $this->readCanonicalArtifact('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        preg_match_all('/ENUM\s*\(([^)]*)\)/i', $sql, $enums);

        $values = [];
        foreach ($enums[1] as $body) {
            preg_match_all("/'([^']*)'/", $body, $literal);
            foreach ($literal[1] as $value) {
                $values[$value] = true;
            }
        }
        $values = array_keys($values);

        $violations = array_values(array_filter($values, function ($value) {
            return $this->violatesPolicyBoundary($value);
        }));

        $this->assertGreaterThan(30, count($values), 'the enum parse must reach the canonical status vocabulary');
        $this->assertSame([], $violations, 'status vocabulary must stay upstream-neutral');
    }

    /**
     * A reason code is the explanation a downstream consumer reads when a row is unusable. A policy
     * word here would make market-data appear to have rejected a trade rather than a datum.
     */
    public function test_no_reason_code_carries_policy_vocabulary(): void
    {
        $seed = $this->readCanonicalArtifact('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');

        preg_match_all("/^\(\s*'([A-Z0-9_]+)'\s*,/m", $seed, $matches);
        $codes = array_values(array_unique($matches[1]));

        $violations = array_values(array_filter($codes, function ($code) {
            return $this->violatesPolicyBoundary($code);
        }));

        $this->assertGreaterThan(300, count($codes), 'the reason code parse must reach the registry body');
        $this->assertSame([], $violations, 'reason codes must stay upstream-neutral');
    }

    /**
     * The command surface is the operator-facing name of this domain. `market-data:rank-candidates`
     * would announce a capability the domain must never have.
     */
    public function test_no_command_signature_carries_policy_vocabulary(): void
    {
        $violations = [];
        $signatures = 0;

        foreach (glob($this->root().'/app/Console/Commands/MarketData/*.php') as $file) {
            if (! preg_match('/\$signature\s*=\s*[\'"]([^\'"]+)[\'"]/', (string) file_get_contents($file), $match)) {
                continue;
            }
            $signatures++;
            $name = explode(' ', $match[1])[0];
            foreach (preg_split('/[:\s{}\-]+/', $name) as $token) {
                if ($token !== '' && $this->violatesPolicyBoundary($token)) {
                    $violations[] = $name;
                    break;
                }
            }
        }

        $this->assertGreaterThan(30, $signatures, 'the command scan must reach the market-data command surface');
        $this->assertSame([], $violations, 'command names must stay upstream-neutral');
    }

    /**
     * `TerminologyOwnerVocabularyTest` already walks the config tree, but with a narrower list.
     * The naming rule is only proven end to end if every named surface is checked against the same
     * vocabulary, so the config tree is re-walked here with the full one.
     */
    public function test_no_configuration_key_carries_policy_vocabulary(): void
    {
        if (! function_exists('env')) {
            eval('function env($key, $default = null) { return $default; }');
        }

        $config = require $this->root().'/config/market_data.php';

        $violations = [];
        $inspected = 0;
        $walk = function (array $node, string $prefix) use (&$walk, &$violations, &$inspected) {
            foreach ($node as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                $inspected++;
                if ($this->violatesPolicyBoundary((string) $key)) {
                    $violations[] = $path;
                }
                if (is_array($value)) {
                    $walk($value, $path);
                }
            }
        };
        $walk($config, '');

        $this->assertGreaterThan(100, $inspected, 'the config walk must reach the whole market-data tree');
        $this->assertSame([], $violations, 'configuration keys must stay upstream-neutral');
    }

    /**
     * Market-data facts may be inputs to watchlist policy and never outputs of it. The direction is
     * enforced structurally: no market-data class may import a policy namespace, so policy cannot
     * be a compile-time dependency of any upstream fact.
     */
    public function test_no_market_data_class_depends_on_a_policy_namespace(): void
    {
        $imports = [];
        $scanned = 0;

        foreach ([
            'app/Domain/MarketData',
            'app/Application/MarketData',
            'app/Infrastructure/MarketData',
            'app/Infrastructure/Persistence/MarketData',
            'app/Console/Commands/MarketData',
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
                $scanned++;
                foreach (preg_split('/\R/', (string) file_get_contents($file->getPathname())) as $line) {
                    if (preg_match('/^use\s+([A-Za-z0-9_\\\\]+)/', $line, $match)) {
                        $imports[$match[1]] = true;
                    }
                }
            }
        }

        $violations = array_values(array_filter(array_keys($imports), function ($fqcn) {
            return (bool) preg_match('/(Watchlist|Strategy|Ranking|Screening|Portfolio|Backtest)/i', $fqcn);
        }));

        $this->assertGreaterThan(100, $scanned, 'the dependency scan must reach the market-data tree');
        $this->assertNotSame([], $imports, 'the import scan must actually collect imports');
        $this->assertSame([], $violations, 'market-data facts must be inputs to policy, never outputs of it');
    }

    /**
     * Readiness answers whether data is safe to expose. If it could reach a policy component, a
     * downstream opinion would become a precondition for upstream correctness.
     */
    public function test_readiness_depends_only_on_scope_and_publication_state(): void
    {
        $source = $this->readCanonicalArtifact('app/Application/MarketData/Services/MarketDataReadinessService.php');

        preg_match_all('/^use\s+([A-Za-z0-9_\\\\]+);/m', $source, $matches);
        $imports = $matches[1];

        $this->assertNotEmpty($imports, 'the readiness service must declare its dependencies explicitly');

        foreach ($imports as $import) {
            $this->assertDoesNotMatchRegularExpression(
                '/(Watchlist|Strategy|Ranking|Screening|Portfolio|Backtest|Signal)/i',
                $import,
                'readiness must not depend on '.$import
            );
        }

        $this->assertContains('App\Domain\MarketData\MarketDataScope', $imports, 'readiness is scope-bound');
        $this->assertContains('App\Infrastructure\Persistence\MarketData\EodPublicationRepository', $imports, 'readiness is publication-bound');
    }

    /**
     * The compatibility alias may be preserved where it already exists and must never reach a new
     * surface. The persisted inventory is pinned, so adding `eligible` to another table, an enum, a
     * reason code, a command, or a config key fails here rather than quietly becoming permanent.
     */
    public function test_the_eligible_alias_is_not_propagated_to_a_new_surface(): void
    {
        $sql = $this->readCanonicalArtifact('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)?\s+`?([A-Za-z0-9_]+)`?\s*\((.*?)\n\)\s*ENGINE/is', $sql, $tables, PREG_SET_ORDER);

        $carriers = [];
        foreach ($tables as $table) {
            foreach (preg_split('/\R/', $table[2]) as $line) {
                if (preg_match('/^\s+`?eligible`?\s+[A-Za-z]/', $line)) {
                    $carriers[] = $table[1];
                }
            }
        }
        sort($carriers);

        $this->assertGreaterThan(20, count($tables), 'the schema parse must reach the canonical tables');
        $this->assertSame(
            ['eod_eligibility', 'eod_eligibility_history'],
            $carriers,
            'the bare eligible column may exist only on the eligibility artifact and its history'
        );

        preg_match_all('/ENUM\s*\(([^)]*)\)/i', $sql, $enums);
        foreach ($enums[1] as $body) {
            $this->assertDoesNotMatchRegularExpression('/[\'"]eligible[\'"]/i', $body, 'no status vocabulary may adopt the alias');
        }

        $seed = $this->readCanonicalArtifact('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        preg_match_all("/^\(\s*'([A-Z0-9_]+)'\s*,/m", $seed, $codes);
        $this->assertGreaterThan(300, count($codes[1]), 'the reason code parse must reach the registry body');
        foreach ($codes[1] as $code) {
            $this->assertNotSame('ELIGIBLE', $code, 'no reason code may be named for the alias');
        }

        foreach (glob($this->root().'/app/Console/Commands/MarketData/*.php') as $file) {
            if (preg_match('/\$signature\s*=\s*[\'"]([^\'"\s]+)/', (string) file_get_contents($file), $match)) {
                $this->assertStringNotContainsStringIgnoringCase('eligible', $match[1], 'no command may be named for the alias');
            }
        }
    }

    // ---------------------------------------------------------------- product identity

    /**
     * The three price products are distinct codes owned by the terminology contract, and provider
     * `adj_close` is none of them.
     */
    public function test_the_three_price_products_are_distinct_and_exclude_provider_adj_close(): void
    {
        $codes = [
            MarketDataScope::RAW_PRODUCT,
            MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT,
            MarketDataScope::TOTAL_RETURN_PRODUCT,
        ];

        $this->assertSame(['RAW', 'STRUCTURAL_ADJUSTED', 'TOTAL_RETURN'], $codes);
        $this->assertCount(3, array_unique($codes));
        $this->assertNotContains('adj_close', $codes);
        $this->assertNotContains('ADJ_CLOSE', $codes);
    }

    /**
     * `eligible` survives only as a derived compatibility alias. The read product must compute it
     * from `data_usable` rather than carry an independent value, because two independently written
     * fields can disagree and the alias is the one consumers misread as permission to trade.
     */
    public function test_the_eligible_alias_is_derived_from_data_usable_and_never_independent(): void
    {
        $source = $this->readCanonicalArtifact('app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');

        $this->assertMatchesRegularExpression(
            '/\$result\[\'data_usable\'\]\s*=\s*\(int\)\s*\$row->eligible\s*===\s*1;/',
            $source,
            'data_usable must be the computed canonical field'
        );
        $this->assertMatchesRegularExpression(
            '/\$result\[\'eligible\'\]\s*=\s*\$result\[\'data_usable\'\];/',
            $source,
            'eligible must be assigned from data_usable, not resolved separately'
        );
        $this->assertMatchesRegularExpression(
            '/\$result\[\'eligibility_state\'\]\s*=\s*\$result\[\'data_usable\'\]\s*\?/',
            $source,
            'the explicit state field must also derive from data_usable'
        );
    }
}
