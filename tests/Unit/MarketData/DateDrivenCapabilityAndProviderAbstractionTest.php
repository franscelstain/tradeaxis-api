<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Ports\ApiEodBarsSource;
use App\Domain\MarketData\MarketDataScope;
use PHPUnit\Framework\TestCase;

/**
 * MD-B01 — date-driven capability and provider limitation abstraction.
 *
 * Owner contracts:
 *   authority/strategy/MARKET_DATA_PLATFORM_EOD_BASELINE.md — "Date-driven capability (LOCKED)",
 *     "Provider limitation abstraction (LOCKED)"
 *   authority/strategy/book/Terminology_and_Scope.md — "Date-driven capability",
 *     "Provider limitation abstraction"
 *
 * The rule these protect is one sentence: a provider's query window is the provider's problem, not
 * the platform's boundary. `range=10d` is a fact about one HTTP endpoint. If it reaches the domain,
 * it silently becomes the answer to "how far back can this platform go", and nobody ever decided
 * that.
 *
 * Absence is only evidence when you also show where the thing does live. Each isolation check below
 * is paired with a positive check that the vocabulary is present in the adapter, so a rename or a
 * deleted file cannot turn this suite green by making the search find nothing anywhere.
 */
class DateDrivenCapabilityAndProviderAbstractionTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    /**
     * Transport-specific query shape: the parameter names and endpoint of one provider's HTTP API.
     * Deliberately excludes the provider's *name*, which is legitimate provenance and appears in
     * source labels, config defaults, and reason text throughout the application layer.
     */
    private const PROVIDER_QUERY_SHAPE = '/(range=|interval=|period1|period2|query1\.finance|\/v8\/finance\/chart|includePrePost)/i';

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();
        parent::tearDown();
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /**
     * Returns [relativePath => matchedTokens] for files under $dir whose executable code — comments
     * and docblocks removed — contains the provider query shape, plus the number of files scanned.
     */
    private function scanLayer(string $dir): array
    {
        $path = $this->root().'/'.$dir;
        $hits = [];
        $scanned = 0;

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile() || substr($file->getFilename(), -4) !== '.php') {
                continue;
            }
            $scanned++;

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

            if (preg_match_all(self::PROVIDER_QUERY_SHAPE, $code, $matches)) {
                $relative = strtr($file->getPathname(), chr(92), '/');
                $hits[substr($relative, strpos($relative, $dir))] = array_values(array_unique($matches[0]));
            }
        }

        return [$hits, $scanned];
    }

    // ------------------------------------------------- provider limitation abstraction

    /**
     * The domain layer owns market meaning and must not know that a provider exists at all.
     */
    public function test_the_domain_layer_contains_no_provider_query_shape(): void
    {
        [$hits, $scanned] = $this->scanLayer('app/Domain');

        $this->assertGreaterThan(0, $scanned, 'the domain scan must reach at least one file');
        $this->assertSame([], $hits, 'provider transport shape must not reach the domain layer');
    }

    /**
     * The application layer orchestrates acquisition through provider-neutral ports. Provider
     * *identity* legitimately appears here as a provenance label; provider *query shape* must not.
     */
    public function test_the_application_layer_contains_no_provider_query_shape(): void
    {
        [$hits, $scanned] = $this->scanLayer('app/Application');

        $this->assertGreaterThan(40, $scanned, 'the application scan must reach the service tree');
        $this->assertSame([], $hits, 'provider transport shape must be absorbed by the adapter, not the application layer');
    }

    /**
     * The paired positive check. Without it, deleting the adapter would make both isolation tests
     * above pass while the platform lost the capability entirely.
     */
    public function test_the_provider_query_shape_lives_in_the_source_adapter(): void
    {
        [$hits, $scanned] = $this->scanLayer('app/Infrastructure');

        $this->assertGreaterThan(0, $scanned, 'the infrastructure scan must reach at least one file');
        $this->assertArrayHasKey(
            'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php',
            $hits,
            'the adapter is where provider transport shape belongs; finding none there means the isolation checks are proving nothing'
        );

        $this->assertSame(
            ['app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php'],
            array_keys($hits),
            'exactly one adapter may carry provider transport shape'
        );
    }

    /**
     * The port is the contract the application layer sees. It takes explicit dates and knows nothing
     * about windows, ranges, intervals, or endpoints.
     */
    public function test_the_acquisition_port_is_provider_neutral_and_date_addressed(): void
    {
        $reflection = new ReflectionClass(ApiEodBarsSource::class);

        $this->assertTrue($reflection->isInterface(), 'acquisition must be reached through a port, not a concrete provider');

        $source = (string) file_get_contents((string) $reflection->getFileName());
        $this->assertDoesNotMatchRegularExpression(self::PROVIDER_QUERY_SHAPE, $source, 'the port must not name provider transport shape');
        $this->assertStringNotContainsStringIgnoringCase('yahoo', $source, 'the port must not name a provider');

        $range = $reflection->getMethod('fetchOrLoadEodBarsRange');
        $names = array_map(function (ReflectionParameter $p) { return $p->getName(); }, $range->getParameters());

        $this->assertSame('startDate', $names[0], 'the range entry point must be addressed by an explicit start date');
        $this->assertSame('endDate', $names[1], 'the range entry point must be addressed by an explicit end date');
    }

    /**
     * The adapter declares what it cannot do instead of letting the domain discover it. A capability
     * the provider lacks must be visible as a stated `false`, not as an absent field.
     */
    public function test_the_adapter_discloses_its_own_limits_rather_than_exporting_them(): void
    {
        $reflection = new ReflectionClass(ApiEodBarsSource::class);
        $this->assertTrue($reflection->hasMethod('capabilities'), 'the port must require a capability disclosure');

        $adapter = new ReflectionClass(\App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter::class);
        $this->assertTrue($adapter->implementsInterface(ApiEodBarsSource::class));

        $source = (string) file_get_contents((string) $adapter->getFileName());

        foreach ([
            'provides_official_board_or_trading_status',
            'provides_authoritative_corporate_actions',
            'provides_actual_traded_value',
        ] as $limit) {
            $this->assertMatchesRegularExpression(
                '/[\'"]'.$limit.'[\'"]\s*=>\s*false/',
                $source,
                $limit.' must be disclosed as an explicit false, not omitted'
            );
        }

        $this->assertMatchesRegularExpression(
            '/[\'"]forbidden_canonical_basis[\'"]\s*=>\s*\[[^\]]*PROVIDER_ADJ_CLOSE/',
            $source,
            'the adapter must name provider adj_close as a forbidden canonical basis'
        );
    }

    /**
     * The contract names four provider quirks, not one: query window, rate limits, per-ticker
     * request fan-out, and transport parameter shape. Proving only the last would leave three
     * unproven, so each is located in the adapter and shown absent from the application layer.
     */
    public function test_every_named_provider_quirk_is_absorbed_by_the_acquisition_strategy(): void
    {
        $adapter = (string) file_get_contents($this->root().'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        $this->assertMatchesRegularExpression('/requestWithRetry/', $adapter, 'retry handling belongs to the adapter');
        $this->assertMatchesRegularExpression('/circuit_breaker/i', $adapter, 'rate-limit protection belongs to the adapter');
        $this->assertMatchesRegularExpression('/foreach\s*\(\s*\$uniqueTickerCodes/', $adapter, 'per-ticker request fan-out belongs to the adapter');

        [$appHits, $scanned] = $this->scanLayer('app/Application');
        $this->assertGreaterThan(40, $scanned);
        $this->assertSame([], $appHits, 'transport parameter shape must not reach the application layer');

        $quirks = '/(circuit_breaker|requestWithRetry|includePrePost)/i';
        $leaked = [];
        foreach (['app/Domain', 'app/Application'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/'.$dir, FilesystemIterator::SKIP_DOTS));
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
                if (preg_match($quirks, $code)) {
                    $leaked[] = $file->getFilename();
                }
            }
        }

        $this->assertSame([], $leaked, 'provider quirk handling must not be inherited as a domain concern');
    }

    /**
     * `MD-S001-R0075`, `R0076`, and `R0078` — for the active `yahoo_finance` path the request is made
     * per ticker, a free provider may rate limit, and the provider may require `period1`/`period2` or
     * an equivalent windowing parameter.
     *
     * These three were `REFERENCE_ONLY` until `MD-B01-A014`, so the contract's own list of provider
     * quirks was only partly under obligation. Each is located in the adapter and paired with the
     * absence of the same concern from the domain and application layers, because "the adapter owns
     * it" is only meaningful if nothing above the adapter also owns it.
     */
    public function test_the_three_named_provider_quirks_are_owned_by_the_adapter_and_by_nothing_above_it(): void
    {
        $adapterPath = $this->root().'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php';
        $adapter = (string) file_get_contents($adapterPath);

        // R0075 — the request is made per ticker.
        $this->assertMatchesRegularExpression(
            '/foreach\s*\(\s*\$uniqueTickerCodes/',
            $adapter,
            'MD-S001-R0075: the adapter must fan the request out per ticker'
        );

        // R0076 — a free provider may rate limit, and the adapter absorbs it.
        $this->assertMatchesRegularExpression('/circuit_breaker/i', $adapter, 'MD-S001-R0076: rate-limit protection must live in the adapter');
        $this->assertMatchesRegularExpression('/requestWithRetry/', $adapter, 'MD-S001-R0076: retry handling must live in the adapter');

        // R0078 — the provider may require period1/period2 or an equivalent window.
        $this->assertMatchesRegularExpression('/\{period1\}/', $adapter, 'MD-S001-R0078: the provider window parameter must be handled in the adapter');
        $this->assertMatchesRegularExpression('/\{period2\}/', $adapter, 'MD-S001-R0078: both window bounds must be handled in the adapter');
        $this->assertMatchesRegularExpression(
            '/canonicalYahooChartUrl\(\$symbol,\s*\$periodBounds\[\'period1\'\],\s*\$periodBounds\[\'period2\'\]/',
            $adapter,
            'MD-S001-R0078: an absent template must fall back to the canonical windowed URL rather than to a domain default'
        );

        $leaked = [];
        foreach (['app/Domain', 'app/Application'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/'.$dir, FilesystemIterator::SKIP_DOTS));
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
                if (preg_match('/period1|period2|circuit_breaker/i', $code)) {
                    $leaked[] = $file->getFilename();
                }
            }
        }
        $this->assertSame([], $leaked, 'no layer above the adapter may know the provider window or its rate-limit protection');
    }

    /**
     * `MD-S001-R0082` — the import strategy may use windowing, an explicit date range, looping
     * batches, retry, or backoff, as long as the date-driven contract survives.
     *
     * The permission half is proven by the mechanisms actually being present in the acquisition
     * strategy; the constraint half by the requested range still bounding the result, so a windowing
     * mechanism cannot quietly become the answer to a different question.
     */
    public function test_the_import_strategy_may_window_and_retry_while_the_requested_range_still_governs(): void
    {
        $acquisition = (string) file_get_contents(
            $this->root().'/app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php'
        );

        foreach ([
            'windowing' => '/\$windows\b|buildWindowCheckpoints/',
            'explicit date range' => '/\$requestedStart\b.*\$requestedEnd\b/s',
            'looping batch' => '/source_acquisition_batch_id/',
            'retry' => '/retry/i',
        ] as $mechanism => $pattern) {
            $this->assertMatchesRegularExpression($pattern, $acquisition, 'the import strategy may use '.$mechanism);
        }

        $this->assertMatchesRegularExpression(
            "/'source_acquisition_mode'\s*=>\s*'range_window'/",
            $acquisition,
            'the acquisition mode must name the windowing it performs rather than hide it'
        );
        $this->assertMatchesRegularExpression(
            '/buildDateTelemetry\(\$rowsByDate,.*\$requestedStart,\s*\$requestedEnd\)/s',
            $acquisition,
            'the requested range must still govern what the windowed acquisition reports'
        );
    }

    // ------------------------------------------------- date-driven capability

    /**
     * The domain accepts any requested trade date on or after the intentional dataset start, across
     * years, month boundaries, and a leap day. None of these is reachable inside a ten-day provider
     * default window, which is the whole point.
     *
     * @dataProvider arbitraryDateProvider
     */
    public function test_the_domain_accepts_an_arbitrary_requested_date(string $date): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        $this->assertSame($date, $scope->assertRequestedDate($date));
    }

    public function arbitraryDateProvider(): array
    {
        return [
            'dataset start itself' => ['2023-01-02'],
            'mid first year' => ['2023-07-19'],
            'year boundary' => ['2023-12-29'],
            'leap day' => ['2024-02-29'],
            'month end' => ['2024-10-31'],
            'far from any recent window' => ['2025-03-14'],
            'recent' => ['2026-07-07'],
        ];
    }

    /**
     * Ranges are equally unbounded above the dataset start. A three-year span is accepted whole
     * rather than being decomposed into whatever the provider is willing to return per call.
     */
    public function test_the_domain_accepts_an_arbitrary_requested_range(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        $this->assertSame(['2023-01-02', '2026-07-07'], $scope->assertRequestedRange('2023-01-02', '2026-07-07'));
        $this->assertSame(['2024-02-29', '2024-02-29'], $scope->assertRequestedRange('2024-02-29', '2024-02-29'));

        $span = (new DateTimeImmutable('2026-07-07'))->diff(new DateTimeImmutable('2023-01-02'))->days;
        $this->assertGreaterThan(
            10,
            $span,
            'the accepted range must exceed a provider default recent window, otherwise this proves nothing'
        );
    }

    /**
     * The historical boundary is a governed decision recorded in config, not a residue of whatever
     * the provider happened to serve. Moving the provider window must not move the boundary.
     */
    public function test_the_dataset_boundary_is_governed_config_not_a_provider_artifact(): void
    {
        if (! function_exists('env')) {
            eval('function env($key, $default = null) { return $default; }');
        }

        $config = require $this->root().'/config/market_data.php';

        $this->assertSame('2023-01-02', $config['scope']['dataset_start'], 'the dataset start is a scope decision');
        $this->assertSame('2023-01-02', MarketDataScope::DATASET_START, 'the domain constant must agree with governed config');

        $scopeKeys = array_keys($config['scope']);
        foreach ($scopeKeys as $key) {
            $this->assertDoesNotMatchRegularExpression(
                '/(range|window|period|lookback)/i',
                $key,
                'no provider window concept may appear in the scope contract, found: '.$key
            );
        }

        $this->bindMarketDataConfig([]);
        $this->assertSame('2023-01-02', MarketDataScope::fromConfig()->datasetStart());
    }

    /**
     * A date before the boundary is refused by name rather than silently returning nothing, so a
     * scope decision can never be mistaken for a provider limit or an empty result.
     */
    public function test_a_date_outside_the_boundary_is_refused_by_name_not_by_silence(): void
    {
        $scope = new MarketDataScope('Asia/Jakarta', '2023-01-02');

        try {
            $scope->assertRequestedDate('2022-12-30');
            $this->fail('a pre-boundary date must not be silently accepted');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('MARKET_DATA_REQUEST_BEFORE_DATASET_START', $exception->getMessage());
            $this->assertStringContainsString('2023-01-02', $exception->getMessage(), 'the refusal must name the boundary it enforced');
        }
    }
}
