<?php

use App\Application\MarketData\Services\MarketDataReadinessService;
use App\Domain\MarketData\MarketDataScope;
use Tests\Support\MarketData\SeedsConsumerReadModelFixture;
use Tests\Support\UsesMarketDataSqlite;

/**
 * MD-B01 — what the platform is allowed to claim about itself.
 *
 * Owner contract: authority/strategy/book/Terminology_and_Scope.md, "Locked interpretation rules".
 *
 * These rules are unusual: each forbids a *description* rather than a behaviour. That makes them
 * easy to treat as documentation etiquette and skip. They are not. A development frontier reported
 * with the same shape as an operational guarantee is a false freshness claim regardless of what any
 * document says, and the readiness payload is where that claim would actually be made.
 *
 * So the activation rules are tested through the service that answers the question, not by reading
 * the service's source.
 */
class ActivationClaimAndTerminologyBoundaryTest extends TestCase
{
    use UsesMarketDataSqlite;
    use SeedsConsumerReadModelFixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        $this->configureConsumerReadModelFixture();
        config()->set('market_data.scope.operational_start_date', null);
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function executableCode(array $dirs): array
    {
        $out = [];
        foreach ($dirs as $dir) {
            $path = $this->root().'/'.$dir;
            if (! is_dir($path)) {
                continue;
            }
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                    continue;
                }
                $code = '';
                foreach (token_get_all((string) file_get_contents($file->getPathname())) as $token) {
                    if (is_array($token)) {
                        if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                            continue;
                        }
                        $code .= $token[1];
                    } else {
                        $code .= $token;
                    }
                }
                $out[$file->getFilename()] = $code;
            }
        }

        return $out;
    }

    // ------------------------------------------------- activation claims

    /**
     * A readable publication is the strongest thing this platform can say about a date, and it is
     * still not a freshness claim while the platform is being built. The ready payload must carry
     * the activation state alongside `is_ready` so the two cannot be conflated by a reader.
     */
    public function test_a_ready_date_still_reports_the_development_frontier_before_activation(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2);

        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready'], 'the fixture is a genuinely readable publication');
        $this->assertArrayHasKey('activation_state', $result, 'readiness must state which world it is answering in');
        $this->assertSame('DEVELOPMENT', $result['activation_state'], 'no governed marker is set, so this is a frontier, not freshness');
    }

    /**
     * The blocked path must answer in the same vocabulary. If only the ready payload carried the
     * activation state, a consumer would learn the distinction exactly when it least matters.
     */
    public function test_a_blocked_date_reports_the_same_activation_vocabulary(): void
    {
        $result = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertArrayHasKey('activation_state', $result);
        $this->assertSame('DEVELOPMENT', $result['activation_state']);
    }

    /**
     * The state is derived, not decorative: setting the governed marker moves it, and only for
     * dates at or after the marker.
     */
    public function test_the_reported_activation_state_follows_the_governed_marker(): void
    {
        $this->seedReadablePublication('2026-05-19', 3, 2);

        config()->set('market_data.scope.operational_start_date', '2026-05-19');
        $onBoundary = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');
        $this->assertSame('OPERATIONAL', $onBoundary['activation_state']);

        config()->set('market_data.scope.operational_start_date', '2026-05-20');
        $beforeBoundary = (new MarketDataReadinessService())->readinessForTradeDate('2026-05-19');
        $this->assertSame('DEVELOPMENT', $beforeBoundary['activation_state'], 'a date before the marker is never operational');
    }

    /**
     * Consecutive-SLO measurement is the specific claim the terminology contract singles out. No
     * executable surface may make it, because none of them is gated on the activation boundary; the
     * only boundary-aware answer is the activation state above.
     */
    public function test_no_executable_surface_makes_a_consecutive_slo_claim(): void
    {
        $code = $this->executableCode(['app', 'config']);
        $this->assertGreaterThan(100, count($code), 'the scan must reach the application tree');

        $claims = [];
        foreach ($code as $name => $body) {
            if (preg_match('/\b(consecutive|slo)\b/i', $body)) {
                $claims[] = $name;
            }
        }

        $this->assertSame([], $claims, 'an SLO claim may begin only from an explicit governed activation boundary');
    }

    /**
     * A capability limit is a legitimate disclosure by a source or a detector. What it must never be
     * is a description of the development frontier. The distinction is that a disclosed limit is
     * attached to an acquisition or detection capability, never to scope or activation.
     */
    public function test_a_capability_limit_is_never_attached_to_scope_or_activation(): void
    {
        $scope = (string) file_get_contents($this->root().'/app/Domain/MarketData/MarketDataScope.php');

        $this->assertDoesNotMatchRegularExpression('/capability[_ ]?limit/i', $scope, 'scope must not describe itself as a capability limit');

        foreach ($this->executableCode(['app']) as $name => $body) {
            if (! preg_match('/capability[_ ]?limit/i', $body)) {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression(
                '/capability[_ ]?limit[^;]{0,200}(operational_start_date|activation|frontier|dataset_start)/i',
                $body,
                $name.' must not describe the development frontier as a capability limit'
            );
        }
    }

    // ------------------------------------------------- terminology claims

    /**
     * Four terms, four meanings. The contract names them as never interchangeable, and the failure
     * mode is aliasing one to another so a downstream reader silently gets a different series.
     */
    public function test_the_four_source_and_product_terms_are_never_interchangeable(): void
    {
        $codes = [
            MarketDataScope::RAW_PRODUCT,
            MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT,
            MarketDataScope::TOTAL_RETURN_PRODUCT,
        ];

        $this->assertSame(['RAW', 'STRUCTURAL_ADJUSTED', 'TOTAL_RETURN'], $codes);
        $this->assertCount(3, array_unique($codes));

        $aliases = [];
        foreach ($this->executableCode(['app', 'config']) as $name => $body) {
            if (! preg_match_all(
                '/[\'"](RAW|STRUCTURAL_ADJUSTED|TOTAL_RETURN|PROVIDER_ADJ_CLOSE)[\'"]\s*(===|==|=>)\s*[\'"](RAW|STRUCTURAL_ADJUSTED|TOTAL_RETURN|PROVIDER_ADJ_CLOSE)[\'"]/',
                $body,
                $matches,
                PREG_SET_ORDER
            )) {
                continue;
            }
            foreach ($matches as $match) {
                if ($match[1] !== $match[3]) {
                    $aliases[] = $name.': '.$match[1].' '.$match[2].' '.$match[3];
                }
            }
        }

        $this->assertSame([], $aliases, 'no product term may be treated as equivalent to another');

        $adapter = (string) file_get_contents($this->root().'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');
        $this->assertMatchesRegularExpression(
            '/[\'"]forbidden_canonical_basis[\'"]\s*=>\s*\[[^\]]*PROVIDER_ADJ_CLOSE/',
            $adapter,
            'the provider payload must be named as a forbidden canonical basis, not as a fourth product'
        );
    }

    /**
     * Eligibility is a data-usability classification. The eligibility surface must therefore carry
     * none of the four meanings the contract forbids: alpha approval, candidate ranking, tradability
     * approval, or a trading signal.
     */
    public function test_the_eligibility_surface_carries_no_approval_or_ranking_vocabulary(): void
    {
        $forbidden = '/\b(alpha|approval|approved|ranking|ranked|tradability|tradable|conviction|signal|signals|recommend\w*)\b/i';

        $surface = [
            'app/Application/MarketData/Services/EligibilityDecisionService.php',
            'app/Application/MarketData/Services/EodEligibilityBuildService.php',
            'app/Infrastructure/Persistence/MarketData/EligibilitySnapshotScopeRepository.php',
            'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php',
        ];

        foreach ($surface as $relative) {
            $path = $this->root().'/'.$relative;
            $this->assertFileExists($path);

            $code = '';
            foreach (token_get_all((string) file_get_contents($path)) as $token) {
                if (is_array($token)) {
                    if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                        continue;
                    }
                    $code .= $token[1];
                } else {
                    $code .= $token;
                }
            }

            $this->assertDoesNotMatchRegularExpression($forbidden, $code, basename($relative).' must not describe eligibility as a trading decision');
        }

        $seed = (string) file_get_contents($this->root().'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        preg_match_all("/^\(\s*'(ELIG[A-Z0-9_]*)'\s*,/m", $seed, $matches);
        $eligibilityCodes = array_values(array_unique($matches[1]));

        $this->assertGreaterThan(5, count($eligibilityCodes), 'the eligibility reason-code family must be reached');
        foreach ($eligibilityCodes as $code) {
            $this->assertDoesNotMatchRegularExpression(
                '/(ALPHA|APPROV|RANK|TRADABL|SIGNAL|CONVICTION|RECOMMEND)/i',
                $code,
                $code.' names a trading decision rather than a data-usability reason'
            );
        }
    }

    /**
     * `decision-grade` is the platform's headline quality claim, and the terminology contract states
     * plainly that it is a target property rather than a proven one until the order-22 re-audit.
     * No executable surface may assert it — not a field, not a state, not a reason code, not a
     * command, not a test name.
     */
    public function test_the_headline_quality_property_is_never_asserted_by_an_executable_surface(): void
    {
        $claims = [];

        foreach ($this->executableCode(['app', 'config']) as $name => $body) {
            if (preg_match('/decision[_ -]?grade/i', $body)) {
                $claims[] = 'code:'.$name;
            }
        }

        $seed = (string) file_get_contents($this->root().'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        preg_match_all("/^\(\s*'([A-Z0-9_]+)'\s*,/m", $seed, $codes);
        $this->assertGreaterThan(300, count($codes[1]), 'the reason code parse must reach the registry body');
        foreach ($codes[1] as $code) {
            if (preg_match('/DECISION_GRADE/i', $code)) {
                $claims[] = 'reason:'.$code;
            }
        }

        foreach (glob($this->root().'/tests/Unit/MarketData/*.php') as $file) {
            foreach (preg_split('/\R/', (string) file_get_contents($file)) as $line) {
                if (preg_match('/function\s+(test_[A-Za-z0-9_]*decision[_]?grade[A-Za-z0-9_]*)/i', $line, $match)) {
                    $claims[] = 'test:'.$match[1];
                }
            }
        }

        // The documentary surface matters as much as the executable one, because this rule forbids a
        // description. Frozen strategy is excluded because it owns the definition, and the
        // traceability matrix is excluded because it stores strategy rule text verbatim — including
        // the sentence that forbids the claim — and every row is already fingerprinted against its
        // source line by the documentation integrity gate.
        $docRoot = $this->root().'/docs/market_data';
        $scanned = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $relative = substr(strtr($file->getPathname(), chr(92), '/'), strlen(strtr($docRoot, chr(92), '/')) + 1);

            if (strpos($relative, 'authority/strategy/') === 0
                || strpos($relative, 'records/history/') === 0
                || strpos($relative, 'records/evidence/legacy/') === 0
                || strpos($relative, 'records/decisions/legacy/') === 0
                || $relative === 'authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv') {
                continue;
            }
            if (! preg_match('/\.(md|json|csv|sql)$/', $relative)) {
                continue;
            }

            $scanned++;
            $text = (string) file_get_contents($file->getPathname());
            if (preg_match('/\b(is|are|already|now|has been|telah|sudah)\s+decision[_ -]?grade\b/i', $text, $match)) {
                $claims[] = 'doc:'.$relative.' ("'.$match[0].'")';
            }
        }

        $this->assertGreaterThan(50, $scanned, 'the documentary scan must reach the active document set');
        $this->assertSame([], $claims, 'decision-grade is a target property and must not be asserted before the order-22 re-audit');
    }
}
