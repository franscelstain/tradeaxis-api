<?php

use App\Domain\MarketData\MarketDataScope;
use PHPUnit\Framework\TestCase;
use Tests\Support\MarketData\ReadsMarketDataSchema;

/**
 * MD-B01 — canonical scope, the intentional dataset start, the development data frontier, and the
 * four conditions of `decision-grade`.
 *
 * Sixteen rows promoted at `MD-B01-A014`. Each is a child of a governing introducer, so the
 * predicate under proof is the composed one. "satu trade date mengikuti kalender IDX" is not proven
 * by a `market_calendar` table existing; it is proven by the platform resolving a trade date through
 * that calendar and refusing a date it cannot.
 *
 * The `decision-grade` conditions are the delicate ones. `MD-S056-R0004` already establishes that
 * `decision-grade` is a target property and not an achieved claim, so proving conditions 1, 2, and 4
 * means proving the platform *expresses and enforces* each condition — never that it has met them.
 */
class CanonicalScopeFrontierAndDecisionGradeTest extends TestCase
{
    use ReadsMarketDataSchema;

    private function read(string $relative): string
    {
        $path = $this->repositoryRoot().'/'.$relative;
        $body = @file_get_contents($path);
        if ($body === false) {
            $this->fail('Surface under proof is unreadable: '.$relative);
        }

        return $this->stripPhpComments((string) $body);
    }

    // ------------------------------------------------------------------ canonical scope

    /**
     * `MD-S001-R0006` — one trade date follows the IDX calendar and the platform timezone
     * `Asia/Jakarta`.
     *
     * Both halves are enforced, not merely declared: the scope refuses to construct when either
     * timezone key drifts, and a trade date resolves through a governed calendar revision whose
     * provenance must be VERIFIED before the date can be treated as an expected trading day.
     */
    public function test_a_trade_date_resolves_through_the_idx_calendar_in_the_platform_timezone(): void
    {
        $scope = $this->read('app/Domain/MarketData/MarketDataScope.php');
        $this->assertStringContainsString("'Asia/Jakarta'", $scope, 'the platform timezone must be declared');
        $this->assertStringContainsString('MARKET_DATA_TIMEZONE_INVALID', $scope, 'a timezone drift must be refused by name');
        $this->assertMatchesRegularExpression(
            '/\$scopeTimezone\s*!==\s*\'Asia\/Jakarta\'\s*\|\|\s*\$platformTimezone\s*!==\s*\$scopeTimezone/',
            $scope,
            'scope and platform timezone must be required to agree, not merely both exist'
        );

        $calendar = $this->read('app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php');
        $this->assertStringContainsString('md_market_calendar_revisions', $calendar, 'the calendar surface must be the governed revision table');
        $this->assertStringContainsString('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE', $calendar, 'a non-trading date must be refused by name');
        $this->assertStringContainsString('MARKET_CALENDAR_PROVENANCE_NOT_VERIFIED', $calendar, 'an unverified calendar row must not establish an expected trading day');
        $this->assertStringContainsString('assertRequestedDate', $calendar, 'the calendar must resolve the date through the governed scope');
    }

    /**
     * `MD-S001-R0007` and `MD-S056-R0014` — a bar counts as EOD only after the Regular Market
     * session for that trade date has completed.
     */
    public function test_a_bar_is_end_of_day_only_after_the_regular_market_session_completed(): void
    {
        $calendar = $this->read('app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php');

        $this->assertStringContainsString('assertCompletedRegularSession', $calendar, 'the completed-session assertion must exist');
        $this->assertMatchesRegularExpression(
            "/\\\$context\['session_state'\]\s*!==\s*'COMPLETED'/",
            $calendar,
            'an incomplete session must be rejected rather than treated as end-of-day'
        );
        $this->assertStringContainsString('MARKET_SESSION_NOT_COMPLETED', $calendar, 'the rejection must be named');
        $this->assertStringContainsString('session_close_at', $calendar, 'session completion must be anchored to a session close, not to a wall clock');

        $this->assertSame('EOD', MarketDataScope::FREQUENCY, 'the canonical frequency is end-of-day');
    }

    /**
     * `MD-S001-R0009` — tick-by-tick, order book/market depth, intraday or ultra-low-latency data,
     * execution routing, and multi-exchange are outside the current phase.
     *
     * Absence is paired with a positive locator: the canonical scope declares exactly one market,
     * one segment, and an end-of-day frequency. A rename that emptied both searches would fail on
     * the locator rather than pass on the silence.
     */
    public function test_no_out_of_phase_market_structure_reaches_the_canonical_surface(): void
    {
        $this->assertSame('IDX', MarketDataScope::MARKET_CODE);
        $this->assertSame('REGULAR', MarketDataScope::MARKET_SEGMENT);
        $this->assertSame('EOD', MarketDataScope::FREQUENCY);

        $schema = $this->schemaColumnMap();
        $this->assertGreaterThan(45, count($schema), 'the schema parse must reach the full surface');

        $forbidden = [
            'tick-by-tick' => '/(^|_)(tick_by_tick|ticks|trade_ticks)(_|$)/',
            'order book' => '/(^|_)(order_book|orderbook|bid_size|ask_size|market_depth|depth_level)(_|$)/',
            'intraday bar' => '/(^|_)(intraday|minute_bar|second_bar|realtime_bar)(_|$)/',
            'execution routing' => '/(^|_)(order_routing|routing_venue|execution_venue|broker_order)(_|$)/',
            'multi-exchange' => '/(^|_)(exchange_id|exchange_list|secondary_exchange|venue_code)(_|$)/',
        ];

        $violations = [];
        foreach ($schema as $table => $columns) {
            foreach (array_merge([$table], array_keys($columns)) as $identifier) {
                foreach ($forbidden as $concept => $pattern) {
                    if (preg_match($pattern, $identifier)) {
                        $violations[] = $table.'.'.$identifier.' :: '.$concept;
                    }
                }
            }
        }

        $this->assertSame([], $violations, 'the canonical schema may not carry an out-of-phase market-structure surface');

        // The patterns must be able to fire, or the clean result above proves nothing.
        foreach (['tick_by_tick', 'order_book', 'intraday_bar', 'order_routing', 'exchange_id'] as $probe) {
            $matched = false;
            foreach ($forbidden as $pattern) {
                $matched = $matched || (bool) preg_match($pattern, $probe);
            }
            $this->assertTrue($matched, $probe.' must be matched by the out-of-phase patterns');
        }
    }

    /**
     * `MD-S001-R0062` — the locked cross-document decision that the system prefers data complete
     * enough but later over data fast but not yet complete enough.
     *
     * The decision is behavioural, so the proof is the promote path refusing to continue to the
     * publishing stages while coverage has not passed. Speed cannot overtake completeness because
     * the stages that make a date readable are downstream of the coverage decision.
     */
    public function test_completeness_gates_publication_rather_than_speed(): void
    {
        $pipeline = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');

        $this->assertSame(
            1,
            preg_match('/function promoteSingleDay\b.*?\n    \}/s', $pipeline, $promote),
            'the promote entry point must be locatable'
        );
        $this->assertMatchesRegularExpression(
            "/requires_full_coverage.*?coverage_gate_state.*?!==\s*'PASS'/s",
            $promote[0],
            'a run that has not passed coverage must not proceed to the publishing stages'
        );

        $position = strpos($promote[0], 'requires_full_coverage');
        $this->assertIsInt($position, 'the coverage decision must appear in the promote sequence');
        foreach (['COMPUTE_INDICATORS', 'BUILD_ELIGIBILITY', 'HASH', 'SEAL', 'FINALIZE'] as $stage) {
            $this->assertGreaterThan(
                $position,
                strpos($promote[0], $stage),
                $stage.' must be sequenced after the coverage decision, not before it'
            );
        }
    }

    // ------------------------------------------------------------------ decision-grade conditions

    /**
     * `MD-S056-R0006` — as-known: every input resolves from facts recorded and effective by `T`,
     * with no later revision leaking backward.
     *
     * The platform implements this as an explicit knowledge cutoff applied to revision tables. The
     * proof is that the cutoff is a comparison against `recorded_at`, not a comment about intent.
     */
    public function test_as_known_resolution_applies_an_explicit_knowledge_cutoff(): void
    {
        $eventRisk = $this->read('app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php');
        $this->assertMatchesRegularExpression(
            "/where\(\s*\\\$column\s*,\s*'<='\s*,\s*\\\$knownAt\s*\)/",
            $eventRisk,
            'the knowledge cutoff must restrict rows to what was recorded by the cutoff'
        );
        $this->assertStringContainsString('applyKnowledgeCutoff', $eventRisk, 'the cutoff must be a named, reusable restriction');

        $calendar = $this->read('app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php');
        $this->assertMatchesRegularExpression(
            "/where\('recorded_at',\s*'<=',\s*\\\$knownAt\)/",
            $calendar,
            'calendar revisions must also resolve as-known'
        );

        $schema = $this->schemaColumnMap();
        foreach (['md_market_calendar_revisions', 'md_trading_status_revisions', 'md_corporate_action_revisions'] as $table) {
            $this->assertArrayHasKey($table, $schema, $table.' must exist for as-known resolution');
            $this->assertArrayHasKey('recorded_at', $schema[$table], $table.' must record when the platform learned the fact');
        }
    }

    /**
     * `MD-S056-R0007` — single declared basis: one explicit versioned price basis and one formula
     * version, with no per-row fallback or mixing across dates.
     *
     * Proven from the identity carried on the indicator row and the publication that seals it. A
     * per-row fallback would require the basis to be nullable or resolvable at row level; it is
     * neither, and the publication binds a single product code and version for the whole date.
     */
    public function test_one_price_basis_and_one_formula_version_identify_an_indicator_row(): void
    {
        $schema = $this->schemaColumnMap();

        foreach (['price_product_code', 'price_product_version', 'formula_version', 'indicator_set_version'] as $column) {
            $this->assertArrayHasKey($column, $schema['eod_indicators'], 'an indicator row must carry '.$column);
        }
        foreach (['price_product_code', 'price_product_version'] as $column) {
            $this->assertArrayHasKey($column, $schema['eod_publications'], 'the sealed publication must bind '.$column);
        }

        $identity = $this->read('app/Application/MarketData/Services/AnalyticalProductIdentityService.php');
        $this->assertStringContainsString('price_product_code', $identity, 'the identity service must resolve the declared basis');
        $this->assertStringContainsString('factorSetHash', $identity, 'the identity must bind the adjustment factor set it was derived under');
        $this->assertStringContainsString('factor_formula_version', $identity, 'the factor formula version must be part of that identity');

        $readProduct = $this->read('app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php');
        $this->assertMatchesRegularExpression(
            "/where\('ind\.price_product_code',\s*\\\$publication->price_product_code\)/",
            $readProduct,
            'the read product must select one declared basis rather than mixing bases'
        );
        $this->assertMatchesRegularExpression(
            "/where\('ind\.factor_set_hash',\s*\\\$publication->factor_set_hash\)/",
            $readProduct,
            'the read product must select one factor set rather than mixing revisions across dates'
        );
    }

    /**
     * `MD-S056-R0009` — timely enough to be usable: the output is available while the horizon it
     * serves is still open.
     *
     * `MD-S056-R0004` establishes `decision-grade` as a target property, so this condition is proven
     * by the platform *tracking* timeliness against an explicit activation state — never by claiming
     * it has been met. A run with no operational start reports `DEVELOPMENT_NOT_OPERATIONAL` rather
     * than a freshness figure.
     */
    public function test_timeliness_is_tracked_against_activation_and_never_claimed_as_achieved(): void
    {
        $schema = $this->schemaColumnMap();
        foreach (['freshness_state', 'latest_readable_trade_date', 'latest_expected_trade_date', 'operational_start_date'] as $column) {
            $this->assertArrayHasKey($column, $schema['eod_runs'], 'a run must record '.$column.' for timeliness to be evaluable');
        }

        $runs = $this->read('app/Infrastructure/Persistence/MarketData/EodRunRepository.php');
        $this->assertMatchesRegularExpression(
            "/operationalStartDate\(\)\s*\?\s*'NOT_EVALUATED'\s*:\s*'DEVELOPMENT_NOT_OPERATIONAL'/",
            $runs,
            'freshness must resolve to a development state until an operational start exists'
        );

        $readiness = $this->read('app/Application/MarketData/Services/MarketDataReadinessService.php');
        $this->assertStringContainsString('activation_state', $readiness, 'a readiness answer must state which world it is in');
        $this->assertStringContainsString('stateFor(', $readiness, 'the activation state must be resolved, not asserted');
    }

    // ------------------------------------------------------------------ market and horizon scope

    /**
     * `MD-S056-R0012` — the target market is IDX-listed equities.
     */
    public function test_the_target_market_is_idx_listed_equities(): void
    {
        $this->assertSame('IDX', MarketDataScope::MARKET_CODE);

        $schema = $this->schemaColumnMap();
        $this->assertArrayHasKey('md_listings', $schema, 'listed instruments must have a listing surface');
        $this->assertArrayHasKey('listing_id', $schema['md_listings']);
        $this->assertArrayHasKey('md_instruments', $schema, 'the instrument identity surface must exist');

        $scope = $this->read('app/Domain/MarketData/MarketDataScope.php');
        $this->assertStringContainsString('MARKET_DATA_SCOPE_INVALID', $scope, 'a market outside IDX Regular-Market EOD must be refused by name');
    }

    // ------------------------------------------------------------------ intentional dataset start

    /**
     * `MD-S056-R0031` and `MD-S056-R0032` — absence before the intentional dataset start is not an
     * active-scope missing-data defect, and coverage may be claimed only from that boundary.
     *
     * Behavioural: a request before the boundary is refused by a named reason rather than answered
     * with an empty result that a caller could read as missing data.
     */
    public function test_a_request_before_the_dataset_start_is_refused_by_name_not_reported_as_missing(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', MarketDataScope::DATASET_START);

        $this->assertSame('2023-01-02', $scope->datasetStart());
        $this->assertSame('2023-01-02', $scope->assertRequestedDate('2023-01-02'));

        try {
            $scope->assertRequestedDate('2022-12-30');
            $this->fail('a pre-boundary date must not resolve');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('MARKET_DATA_REQUEST_BEFORE_DATASET_START', $e->getMessage());
            $this->assertStringNotContainsString('MISSING', strtoupper($e->getMessage()), 'a pre-boundary date is out of scope, not missing data');
        }

        try {
            $scope->assertRequestedRange('2022-12-01', '2023-02-01');
            $this->fail('a range starting before the boundary must not resolve');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('MARKET_DATA_REQUEST_BEFORE_DATASET_START', $e->getMessage());
        }
    }

    /**
     * `MD-S056-R0034` — an instrument listed after the boundary starts warm-up from its own listed
     * date.
     *
     * Every windowed indicator function must refuse to emit until it has its own history. One guard
     * satisfying the check is not enough: `MD-B01-A010` found three windowed functions where only
     * one was verified, so all of them are enumerated here.
     */
    public function test_every_windowed_indicator_refuses_to_emit_before_its_own_history_exists(): void
    {
        $source = $this->read('app/Application/MarketData/Services/IndicatorVectorService.php');

        $this->assertSame(
            1,
            preg_match_all('/function\s+\w+\([^)]*\$window[^)]*\)/', $source, $signatures) > 0 ? 1 : 0,
            'the windowed functions must be locatable'
        );
        $this->assertGreaterThanOrEqual(3, count($signatures[0]), 'every windowed function must be enumerated, not just the first');

        $guards = preg_match_all('/\$index\s*<\s*0\s*\|\|\s*\(\$index\s*\+\s*1\)\s*<\s*\$window|\$index\s*<\s*\$window/', $source);
        $this->assertGreaterThanOrEqual(
            count($signatures[0]),
            $guards,
            'each windowed function must carry its own warm-up guard'
        );
    }

    /**
     * `MD-S056-R0035` — pre-boundary historical expansion is a possible future scope, not a current
     * requirement or blocker.
     *
     * Proven by absence of any current requirement or blocker that depends on it, paired with the
     * positive locator that the boundary is a governed configuration value rather than a hard limit.
     */
    public function test_pre_boundary_expansion_is_neither_a_current_requirement_nor_a_blocker(): void
    {
        $dependencies = (string) file_get_contents(
            $this->repositoryRoot().'/docs/market_data/development/implementation/MD_DEPENDENCY_REGISTRY.csv'
        );
        $this->assertNotSame('', $dependencies, 'the dependency registry must be readable before it is searched');
        $this->assertDoesNotMatchRegularExpression(
            '/OPEN_BLOCKING[^\n]*(pre-boundary|before 2023-01-02|pre-2023)/i',
            $dependencies,
            'no open blocking dependency may rest on pre-boundary expansion'
        );

        $config = $this->read('config/market_data.php');
        $this->assertMatchesRegularExpression(
            "/'dataset_start'\s*=>\s*env\(/",
            $config,
            'the dataset start must be governed configuration, so expanding it later is a decision rather than a code limit'
        );
    }

    // ------------------------------------------------------------------ development data frontier

    /**
     * `MD-S056-R0039`, `MD-S056-R0040`, and `MD-S056-R0041` — before operational activation a gap
     * after the frontier is not a production incident, `daily_enabled=false` is a valid development
     * state, and the gap blocks no contract, schema, integrity, corporate-action, indicator, or
     * replay correction work.
     */
    public function test_the_development_frontier_gap_is_a_valid_state_and_blocks_no_correction_path(): void
    {
        $config = $this->read('config/market_data.php');
        $this->assertMatchesRegularExpression(
            "/'daily_enabled'\s*=>\s*\(bool\)\s*env\('MARKET_DATA_DAILY_ENABLED',\s*false\)/",
            $config,
            'the daily schedule must default to disabled, which is what makes a frontier gap a development state'
        );

        $kernel = $this->read('app/Console/Kernel.php');
        $this->assertMatchesRegularExpression(
            "/if\s*\(\s*!\s*config\('market_data\.pipeline\.daily_enabled'\)\s*\)/",
            $kernel,
            'a disabled daily schedule must be an accepted branch, not an error'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/daily_enabled[^;]{0,80}(throw|abort|Exception)/i',
            $kernel,
            'a disabled daily schedule must not raise'
        );

        // The correction paths remain reachable regardless of the frontier gap.
        foreach ([
            'app/Console/Commands/MarketData/RequestCorrectionCommand.php',
            'app/Console/Commands/MarketData/RunCorrectionCommand.php',
            'app/Console/Commands/MarketData/ReplayBackfillCommand.php',
            'app/Console/Commands/MarketData/ComputeIndicatorsCommand.php',
            'app/Console/Commands/MarketData/DeriveCorporateActionsCommand.php',
        ] as $command) {
            $source = $this->read($command);
            $this->assertMatchesRegularExpression('/\$signature\s*=/', $source, $command.' must expose a command signature');
            $this->assertDoesNotMatchRegularExpression(
                "/daily_enabled/",
                $source,
                $command.' must not be gated on the daily schedule state'
            );
        }
    }
}
