<?php

use PHPUnit\Framework\TestCase;
use Tests\Support\MarketData\ReadsMarketDataSchema;

/**
 * MD-B01 — the dual-use fact rule, the cross-contract alignment list, and the domain ownership
 * assertions.
 *
 * Owner contract: authority/strategy/book/Domain_Boundary_Invariants_LOCKED.md.
 *
 * Three obligations, each proven against a surface that exists rather than against prose:
 *
 * 1. **Cross-contract alignment.** The owner contract names seven documents it must stay aligned
 *    with. Each must exist at the path the contract states, resolved the way the contract writes it,
 *    and must be current authority — not merely a file that happens to be there.
 *
 * 2. **The dual-use fact rule.** Five facts are legitimately required on both sides of the boundary.
 *    Market-data owns the value, unit, source, effective date, and quality state; downstream owns any
 *    threshold, ordering, or acceptance decision derived from it. The load-bearing case is exchange
 *    lot size, whose market-data half the contract records as "None — explicitly disowned by the
 *    volume contract". A disownment is only real if the surface is absent, so that is what is checked.
 *
 * 3. **Domain ownership.** Four capabilities the contract says this domain owns must have a real
 *    surface here. An ownership claim with nothing behind it is a claim, not ownership.
 */
class DualUseFactAndContractAlignmentTest extends TestCase
{
    use ReadsMarketDataSchema;

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function strategyRoot(): string
    {
        return $this->root().'/docs/market_data/authority/strategy';
    }

    /**
     * The owner contract lives in book/, and writes its alignment targets the way a reader standing
     * in that directory would: bare names for siblings, ../ for other sections. Resolving them any
     * other way would prove a different claim than the one the contract makes.
     *
     * rule id => path as written in the contract
     *
     * @return array<string,string>
     */
    private function alignmentTargets(): array
    {
        return [
            'MD-S020-R0182' => 'Terminology_and_Scope.md',
            'MD-S020-R0183' => 'Downstream_Consumer_Read_Model_Contract_LOCKED.md',
            'MD-S020-R0184' => 'Downstream_Data_Readiness_Guarantee_LOCKED.md',
            'MD-S020-R0185' => 'EOD_Eligibility_Snapshot_Contract_LOCKED.md',
            'MD-S020-R0186' => '../registry/Exchange_Market_Structure_Facts_LOCKED.md',
            'MD-S020-R0187' => '../registry/Volume_and_Turnover_Normalization_LOCKED.md',
            'MD-S020-R0188' => '../session_snapshot/Session_Snapshot_Contract_LOCKED.md',
        ];
    }

    private function resolve(string $asWritten): string
    {
        $path = $this->strategyRoot().'/book/'.$asWritten;
        $real = realpath($path);

        return $real === false ? $path : strtr($real, chr(92), '/');
    }

    /** @return array<string,string> path => contents, for every registered active document */
    private function registryContents(): array
    {
        $out = [];
        foreach ([
            'authority/governance/DOCUMENT_ROLE_REGISTRY.csv',
            'authority/governance/CURRENT_VERIFICATION_REGISTRY.csv',
            'authority/governance/DOCUMENT_ID_REGISTRY.csv',
        ] as $registry) {
            $out[$registry] = (string) file_get_contents($this->root().'/docs/market_data/'.$registry);
        }

        return $out;
    }

    private function schemaText(): string
    {
        return $this->schemaSurface();
    }

    /**
     * PHP source with comments removed.
     *
     * A comment is where a contract obligation gets explained, so scanning raw text confuses a
     * surface with a description of one. `IndicatorVectorService` documents at length that turnover
     * applies no lot multiplier and why the alternative is wrong — that docblock is the disownment
     * being honoured, and an earlier revision of this test read it as the disownment being broken.
     */
    private function stripComments(string $source): string
    {
        $out = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token)) {
                if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    continue;
                }
                $out .= $token[1];
                continue;
            }
            $out .= $token;
        }

        return $out;
    }

    /** @return array<string,string> filename => source, across the market-data application tree */
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
                    $out[$file->getFilename()] = (string) file_get_contents($file->getPathname());
                }
            }
        }

        return $out;
    }

    // ---------- 1. cross-contract alignment ----------

    /**
     * @dataProvider alignmentProvider
     */
    public function test_each_alignment_target_exists_and_is_current_authority(string $ruleId, string $asWritten): void
    {
        $resolved = $this->resolve($asWritten);

        $this->assertFileExists(
            $resolved,
            $ruleId.': the owner contract names this document as an alignment target; it must exist where the contract points'
        );

        $relative = substr($resolved, strpos($resolved, 'authority/strategy/'));
        $registered = 0;
        foreach ($this->registryContents() as $name => $contents) {
            if (strpos($contents, $relative) !== false) {
                $registered++;
            }
        }

        $this->assertGreaterThanOrEqual(
            2,
            $registered,
            $ruleId.': an alignment target must be registered as current authority, not merely present on disk ('.$relative.')'
        );
    }

    public function alignmentProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->alignmentTargets() as $ruleId => $asWritten) {
            $out[$ruleId] = [$ruleId, $asWritten];
        }

        return $out;
    }

    /**
     * DOC-CHG-20260821-004 made the parent predicate explicit: the seven named documents must
     * remain semantically aligned with this owner contract. File existence is necessary but no
     * longer sufficient. These are the shared boundary predicates each target must actually carry.
     *
     * @return array<string,array{path:string,patterns:array<int,string>}>
     */
    private function semanticAlignmentRequirements(): array
    {
        return [
            'MD-S020-R0182' => [
                'path' => 'Terminology_and_Scope.md',
                'patterns' => [
                    '/This module remains upstream-only\..*does not promise profit or define watchlist scoring, alpha ranking/is',
                    '/Eligibility means the upstream data passes.*It is not a buy\/sell signal, alpha approval, ranking preference/is',
                ],
            ],
            'MD-S020-R0183' => [
                'path' => 'Downstream_Consumer_Read_Model_Contract_LOCKED.md',
                'patterns' => [
                    '/Eligibility is an explainable upstream data-usability fact\..*it is not watchlist selection, tradability approval, alpha, ranking/is',
                    '/Market-data conformance ends.*Watchlist implementation, ranking behavior, strategy metrics, and profitability are outside/is',
                ],
            ],
            'MD-S020-R0184' => [
                'path' => 'Downstream_Data_Readiness_Guarantee_LOCKED.md',
                'patterns' => [
                    '/These conditions are entirely data-facing\..*No candidate count, ranking stability, signal outcome, P&L/is',
                    '/Job success, row counts, eligibility, or a seal record alone do not imply `READABLE`/i',
                ],
            ],
            'MD-S020-R0185' => [
                'path' => 'EOD_Eligibility_Snapshot_Contract_LOCKED.md',
                'patterns' => [
                    '/It does not encode ranking, alpha, picks, buy\/sell signals, liquidity\/tradability preference, event-avoidance preference, or portfolio policy/i',
                    '/True does not mean selected, liquid enough for a strategy, ranked, attractive, event-safe under a strategy, or approved for a trade/i',
                ],
            ],
            'MD-S020-R0186' => [
                'path' => '../registry/Exchange_Market_Structure_Facts_LOCKED.md',
                'patterns' => [
                    '/Market-data owns only the first\..*not.*market-data/is',
                    '/using these facts to score, rank, or filter instruments for tradability.*belongs downstream/is',
                ],
            ],
            'MD-S020-R0187' => [
                'path' => '../registry/Volume_and_Turnover_Normalization_LOCKED.md',
                'patterns' => [
                    '/Exchange lot size belongs to downstream order\/position sizing, not market-data traded-value normalization/i',
                    '/proxy.*not actual turnover\/traded value/is',
                ],
            ],
            'MD-S020-R0188' => [
                'path' => '../session_snapshot/Session_Snapshot_Contract_LOCKED.md',
                'patterns' => [
                    '/optional, non-streaming upstream artifact/i',
                    '/must never assume picks, rankings, tradability, portfolio subsets/i',
                ],
            ],
        ];
    }

    /** @param array<string,string> $overrides rule id => in-memory document contents */
    private function semanticAlignmentErrors(array $overrides = []): array
    {
        $errors = [];
        foreach ($this->semanticAlignmentRequirements() as $ruleId => $spec) {
            $contents = $overrides[$ruleId] ?? (string) file_get_contents($this->resolve($spec['path']));
            foreach ($spec['patterns'] as $index => $pattern) {
                if (!preg_match($pattern, $contents)) {
                    $errors[] = $ruleId.'#'.($index + 1);
                }
            }
        }

        return $errors;
    }

    public function test_each_alignment_target_carries_the_shared_boundary_predicate(): void
    {
        $this->assertSame(
            [],
            $this->semanticAlignmentErrors(),
            'MD-S020-R0182..R0188 require semantic alignment; target existence alone is not proof'
        );
    }

    public function test_semantic_alignment_guard_fails_closed_when_any_required_relation_is_removed(): void
    {
        foreach ($this->semanticAlignmentRequirements() as $ruleId => $spec) {
            $original = (string) file_get_contents($this->resolve($spec['path']));
            foreach ($spec['patterns'] as $index => $pattern) {
                $mutated = preg_replace($pattern, '__SEMANTIC_RELATION_REMOVED__', $original, 1, $count);
                $this->assertSame(1, $count, $ruleId.' mutation '.($index + 1).' must land before the guard is judged');
                $this->assertContains(
                    $ruleId.'#'.($index + 1),
                    $this->semanticAlignmentErrors([$ruleId => $mutated]),
                    $ruleId.' semantic alignment mutation '.($index + 1).' must fail closed'
                );
            }
        }
    }

    /**
     * MD-S020-R0186 and MD-S020-R0187 do not merely name a document — they say what it owns. A target
     * that exists but does not carry the split it is credited with would leave the boundary unowned.
     */
    public function test_the_two_credited_owners_carry_the_split_they_are_credited_with(): void
    {
        $exchange = (string) file_get_contents($this->resolve('../registry/Exchange_Market_Structure_Facts_LOCKED.md'));
        $volume = (string) file_get_contents($this->resolve('../registry/Volume_and_Turnover_Normalization_LOCKED.md'));

        // R0186 — owns the dual-use exchange facts split
        $this->assertMatchesRegularExpression(
            '/(auto-?rejection|price band)/i',
            $exchange,
            'MD-S020-R0186: the exchange facts contract must carry the auto-rejection band it is credited with owning'
        );
        $this->assertMatchesRegularExpression(
            '/(tick|price fraction)/i',
            $exchange,
            'MD-S020-R0186: the exchange facts contract must carry the tick ladder it is credited with owning'
        );

        // R0187 — owns the actual-versus-proxy split and the lot-size disownment
        $this->assertMatchesRegularExpression(
            '/proxy/i',
            $volume,
            'MD-S020-R0187: the volume contract must carry the actual-versus-proxy split'
        );
        $this->assertMatchesRegularExpression(
            '/lot[\s_-]?size/i',
            $volume,
            'MD-S020-R0187: the volume contract must carry the lot-size disownment it is credited with owning'
        );
    }

    /**
     * MD-S020-R0189: where a dependent document uses older wording, this owner boundary takes
     * precedence. Precedence is only meaningful if nothing active claims to override it, so no active
     * document may define the compatibility alias as anything other than data-usability.
     */
    public function test_no_active_document_overrides_the_owner_boundary_definition(): void
    {
        $root = $this->root().'/docs/market_data';
        $overrides = [];
        $scanned = 0;

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if (! $file->isFile() || ! preg_match('/\.(md|json)$/', $file->getFilename())) {
                continue;
            }
            $relative = substr(strtr($file->getPathname(), chr(92), '/'), strlen(strtr($root, chr(92), '/')) + 1);
            if (strpos($relative, 'authority/strategy/') === 0 || strpos($relative, 'records/history/') === 0) {
                continue;
            }
            $scanned++;
            $text = (string) file_get_contents($file->getPathname());

            // an active document may not redefine the alias as approval, ranking, or tradability
            if (preg_match('/[`"]?eligible[`"]?\s+(means|berarti|is defined as)\s+(?!.{0,40}(data[_ -]?usab))/i', $text, $m)) {
                $overrides[] = $relative.' :: '.trim($m[0]);
            }
        }

        $this->assertGreaterThan(100, $scanned, 'the precedence scan must reach the active corpus');
        $this->assertSame([], $overrides, 'MD-S020-R0189: an active document redefines the alias against the owner boundary');
    }

    // ---------- 2. the dual-use fact rule ----------

    /**
     * The five dual-use facts and how each half is recognised here.
     *
     * `market_data` is a pattern that must be present in this domain; `downstream` is a pattern that
     * must be absent from it. Lot size is the sharp case: its market-data half is `null`, so the only
     * conformant state is no surface at all.
     *
     * @return array<string,array{market_data:?string,downstream:string,note:string}>
     */
    private function dualUseFacts(): array
    {
        return [
            'exchange auto-rejection band' => [
                'market_data' => '/price[_ ]?band|auto[_ -]?rejection|scale[_ ]?break/i',
                'downstream' => '/(can_enter|can_exit|enterable|exitable|entry_allowed|exit_allowed)/i',
                'note' => 'market-data distinguishes a session move from a scale change; whether a locked instrument can be entered is downstream',
            ],
            'tick / price fraction ladder' => [
                'market_data' => '/tick[_ ]?size|price[_ ]?fraction/i',
                'downstream' => '/(slippage|order_price|price_construction)/i',
                'note' => 'market-data bounds the smallest meaningful proportional move; order construction and slippage are downstream',
            ],
            'exchange lot size' => [
                'market_data' => null,
                'downstream' => '/(lot[_ ]?size|board[_ ]?lot|round[_ ]?lot)/i',
                'note' => 'explicitly disowned by the volume contract; the market-data half is None',
            ],
            'traded-value measures and proxies' => [
                'market_data' => '/proxy|traded[_ ]?value|adv\d+/i',
                'downstream' => '/(liquid_enough|min_liquidity|liquidity_threshold|tradable_by_liquidity)/i',
                'note' => 'market-data measures with declared unit and basis; whether it is liquid enough to trade is downstream',
            ],
            'trading status and event risk' => [
                'market_data' => '/trading[_ ]?status|event[_ ]?risk/i',
                'downstream' => '/(avoid_event|event_avoidance|skip_on_event)/i',
                'note' => 'market-data uses it for bar expectation and usability; event-avoidance preference is downstream',
            ],
        ];
    }

    /**
     * MD-S020-R0088 and MD-S020-R0096: each dual-use fact must be present here with the market-data
     * half and without the downstream half.
     */
    public function test_each_dual_use_fact_carries_only_its_market_data_half(): void
    {
        $surface = $this->schemaText();
        foreach ($this->marketDataSource() as $source) {
            $surface .= $this->stripComments($source)."\n";
        }
        $surface .= $this->stripComments((string) file_get_contents($this->root().'/config/market_data.php'));

        $this->assertGreaterThan(200000, strlen($surface), 'the dual-use scan must reach the schema, source, and configuration');

        $missingHalf = [];
        $strayHalf = [];
        foreach ($this->dualUseFacts() as $fact => $spec) {
            if ($spec['market_data'] === null) {
                if (preg_match($spec['downstream'], $surface, $m)) {
                    $strayHalf[] = $fact.' :: disowned but present as '.trim($m[0]);
                }
                continue;
            }
            if (! preg_match($spec['market_data'], $surface)) {
                $missingHalf[] = $fact.' :: market-data half absent';
            }
            if (preg_match($spec['downstream'], $surface, $m)) {
                $strayHalf[] = $fact.' :: downstream half present as '.trim($m[0]);
            }
        }

        $this->assertSame([], $missingHalf, 'MD-S020-R0088: a dual-use fact this domain owns has no surface here');
        $this->assertSame([], $strayHalf, 'MD-S020-R0096: a downstream half has entered market-data');
    }

    /**
     * MD-S020-R0101 and MD-S020-R0171: a dual-use fact must have both halves stated by its owner
     * contract before it reaches a published output. Recording only the upstream half is how the
     * boundary erodes, so the table is checked for both columns on every row.
     */
    public function test_every_dual_use_fact_has_both_halves_stated_by_the_owner_contract(): void
    {
        $contract = (string) file_get_contents($this->strategyRoot().'/book/Domain_Boundary_Invariants_LOCKED.md');

        $rows = [];
        foreach (explode("\n", $contract) as $line) {
            if (substr(trim($line), 0, 1) !== '|') {
                continue;
            }
            $cells = array_map('trim', explode('|', trim($line, "| \t")));
            if (count($cells) !== 3 || preg_match('/^-+$/', $cells[0]) || strtolower($cells[0]) === 'fact') {
                continue;
            }
            $rows[$cells[0]] = [$cells[1], $cells[2]];
        }

        $this->assertGreaterThanOrEqual(5, count($rows), 'the dual-use table must be read; five facts are declared');

        $incomplete = [];
        foreach ($rows as $fact => [$upstream, $downstream]) {
            if ($upstream === '' || $downstream === '') {
                $incomplete[] = $fact;
            }
        }

        $this->assertSame([], $incomplete, 'MD-S020-R0101: a dual-use fact records only one half');

        // every fact this test constrains must actually appear in that table
        $unlisted = [];
        foreach (array_keys($this->dualUseFacts()) as $fact) {
            $found = false;
            foreach (array_keys($rows) as $row) {
                if (stripos($row, explode(' ', $fact)[0]) !== false || stripos($fact, explode(' ', $row)[0]) !== false) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $unlisted[] = $fact;
            }
        }

        $this->assertSame([], $unlisted, 'MD-S020-R0171: a dual-use fact under test is not declared in the owner contract table');
    }

    // ---------- 3. domain ownership ----------

    /**
     * Capabilities the contract says this domain owns, and the surface that makes each real.
     *
     * @return array<string,array{0:string,1:array<int,string>}>
     */
    private function ownedCapabilities(): array
    {
        return [
            'MD-S020-R0014' => ['immutable publication, lineage, reproducibility, and replay',
                ['eod_publications', 'md_publication_lineage_bindings', 'md_replay_daily_metrics']],
            'MD-S020-R0015' => ['explicit readiness/freshness states and fail-safe operations',
                ['eod_runs', 'freshness_state', 'coverage_gate_state']],
            'MD-S020-R0018' => ['immutable source observations and provenance',
                ['md_source_observations']],
            'MD-S020-R0023' => ['deterministic, versioned indicators and factual benchmark context',
                ['market_benchmark_indicators', 'indicator_set_version', 'market_benchmarks']],
        ];
    }

    /**
     * @dataProvider ownedCapabilityProvider
     */
    public function test_an_owned_capability_has_a_real_surface_in_this_domain(string $ruleId, string $capability, array $identifiers): void
    {
        $schema = $this->schemaText();
        $this->assertGreaterThan(50000, strlen($schema), 'the schema scan must reach the migrations');

        $absent = [];
        foreach ($identifiers as $identifier) {
            if (strpos($schema, $identifier) === false) {
                $absent[] = $identifier;
            }
        }

        $this->assertSame(
            [],
            $absent,
            $ruleId.': the contract says this domain owns '.$capability.', but the surface is absent'
        );
    }

    public function ownedCapabilityProvider(): array
    {
        $out = [];
        foreach ((new self('x'))->ownedCapabilities() as $ruleId => [$capability, $identifiers]) {
            $out[$ruleId] = [$ruleId, $capability, $identifiers];
        }

        return $out;
    }

    // ---------- adequacy of the guard itself ----------

    /**
     * Every downstream pattern must be able to fire, otherwise the stray-half check reports clean
     * because it cannot see rather than because nothing is there.
     */
    public function test_every_downstream_pattern_can_fire(): void
    {
        $fixtures = [
            'exchange auto-rejection band' => 'can_enter_locked_instrument',
            'tick / price fraction ladder' => 'slippage_assumption_bps',
            'exchange lot size' => 'board_lot_size',
            'traded-value measures and proxies' => 'min_liquidity_threshold_idr',
            'trading status and event risk' => 'event_avoidance_flag',
        ];

        $facts = $this->dualUseFacts();
        $this->assertCount(5, $facts, 'the dual-use family under test is five facts');

        foreach ($facts as $fact => $spec) {
            $this->assertSame(
                1,
                preg_match($spec['downstream'], $fixtures[$fact]),
                $fact.': the downstream pattern must match a downstream identifier, otherwise a clean result proves nothing'
            );
        }
    }

    /**
     * The disownment check must read surfaces, not explanations of surfaces.
     *
     * A comment saying lot size is deliberately not applied is the contract being honoured; a variable
     * holding a lot size is the contract being broken. A check that cannot separate the two either
     * flags a conformant codebase or, if loosened the wrong way, stops seeing the real thing.
     */
    public function test_a_comment_about_the_disownment_is_not_read_as_a_surface(): void
    {
        $lotSize = $this->dualUseFacts()['exchange lot size']['downstream'];

        $explains = "<?php\n/**\n * Turnover is price times share volume with no lot multiplier;\n * applying a lot size of 100 would overstate it by two orders of magnitude.\n */\nclass X { private function t() { return 1; } }\n";
        $this->assertSame(
            0,
            preg_match($lotSize, $this->stripComments($explains)),
            'a comment explaining the disownment must not be read as owning the fact'
        );
        $this->assertSame(
            1,
            preg_match($lotSize, $explains),
            'the fixture must actually contain the phrase, otherwise the check above proves nothing'
        );

        $owns = "<?php\nclass X { private \$lot_size = 100; }\n";
        $this->assertSame(
            1,
            preg_match($lotSize, $this->stripComments($owns)),
            'a real lot-size identifier must still be flagged after comments are stripped'
        );
    }

    /**
     * And every market-data pattern must fire on its own surface identifier, so a missing-half result
     * means the surface is missing rather than the pattern being wrong.
     */
    public function test_every_market_data_pattern_can_fire(): void
    {
        $fixtures = [
            'exchange auto-rejection band' => 'md_exchange_price_band_tiers',
            'tick / price fraction ladder' => 'md_exchange_tick_size_tiers',
            'traded-value measures and proxies' => 'adv20_close_volume_proxy_idr',
            'trading status and event risk' => 'market_data_trading_status_events',
        ];

        foreach ($this->dualUseFacts() as $fact => $spec) {
            if ($spec['market_data'] === null) {
                continue;
            }
            $this->assertSame(
                1,
                preg_match($spec['market_data'], $fixtures[$fact]),
                $fact.': the market-data pattern must match this domain own identifier'
            );
        }
    }
}
