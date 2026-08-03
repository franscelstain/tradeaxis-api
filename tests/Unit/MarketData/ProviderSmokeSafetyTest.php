<?php

use App\Console\Commands\MarketData\ProviderSmokeCommand;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Behavioural cover for the provider smoke command.
 *
 * `ProviderSmokeSafeModeStaticGuardTest` asserted that eleven publishing and sealing symbols are
 * absent from the command's source, and pinned one classification by matching an exact two-line
 * block of formatted PHP. The prohibition is worth keeping; the formatting match would break on
 * an indentation change while proving nothing about behaviour.
 *
 * Two properties matter here and neither was driven.
 *
 * The command exists to ask a live provider one question. It must leave nothing behind: no bars,
 * no publication, no pointer. Absence of a symbol in a file is a proxy for that; counting rows
 * after a run is the thing itself.
 *
 * And it must distinguish a provider-side condition from a platform fault. BLOCKED with exit 2
 * means the provider was rate limited, slow, or returned nothing usable — retry later. FAILED
 * with exit 1 means the platform is wrong. A scheduler decides whether to page someone on that
 * difference, so classifying a rate limit as FAILED would raise an alert nobody can act on.
 *
 * The adapter is resolved from the container, so every path here runs the real command with no
 * network call.
 */
class ProviderSmokeSafetyTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TRADE_DATE = '2026-03-20';

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        m::close();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function bindAdapterReturning(array $rows): void
    {
        $adapter = m::mock(PublicApiEodBarsAdapter::class);
        $adapter->shouldReceive('fetchOrLoadEodBars')->andReturn($rows);
        $adapter->shouldReceive('consumeLastAcquisitionTelemetry')->andReturn([
            'final_http_status' => 200,
        ]);

        $this->app->instance(PublicApiEodBarsAdapter::class, $adapter);
    }

    private function bindAdapterThrowing(string $reasonCode, string $message, array $context = []): void
    {
        $adapter = m::mock(PublicApiEodBarsAdapter::class);
        $adapter->shouldReceive('fetchOrLoadEodBars')
            ->andThrow(new SourceAcquisitionException($message, $reasonCode, 0, null, $context));

        $this->app->instance(PublicApiEodBarsAdapter::class, $adapter);
    }

    private function bar(): array
    {
        return [
            'ticker_code' => 'BBCA',
            'trade_date' => self::TRADE_DATE,
            'open' => 8000,
            'high' => 8200,
            'low' => 7900,
            'close' => 8150,
            'volume' => 12000,
            'adj_close' => 8150,
        ];
    }

    /**
     * @return array{0:int,1:string}
     */
    private function runSmoke(array $options = []): array
    {
        $command = new ProviderSmokeCommand();
        $command->setLaravel($this->app);

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array_merge([
            '--ticker' => 'BBCA',
            '--trade_date' => self::TRADE_DATE,
        ], $options));

        return [$exitCode, $tester->getDisplay()];
    }

    /**
     * @return array<string,int> table => row count
     */
    private function artifactRowCounts(): array
    {
        $counts = [];

        foreach ([
            'eod_bars',
            'eod_bars_history',
            'eod_indicators',
            'eod_eligibility',
            'eod_publications',
            'eod_current_publication_pointer',
            'eod_runs',
        ] as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }

    /**
     * A run writes no evidence file unless one was asked for.
     *
     * The artifact used to be written unconditionally to a single hardcoded path on every
     * invocation. Three audit tests read that file as the recorded proof of a passing live
     * provider check, so a later run — a failed one, one for a different ticker, or one started
     * from a test — silently destroyed it. This test exists because that is exactly what
     * happened while it was being written.
     */
    public function test_no_evidence_artifact_is_written_unless_one_is_requested(): void
    {
        $legacyPath = base_path('storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt');
        $before = is_file($legacyPath) ? file_get_contents($legacyPath) : null;

        $this->bindAdapterReturning([$this->bar()]);
        $this->runSmoke();

        $this->assertSame(
            $before,
            is_file($legacyPath) ? file_get_contents($legacyPath) : null,
            'A smoke run without --output_dir must not touch any recorded evidence.'
        );
    }

    public function test_an_evidence_artifact_is_written_where_it_is_asked_for(): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tradeaxis_smoke_'.uniqid();

        $this->bindAdapterReturning([$this->bar()]);
        $this->runSmoke(['--output_dir' => $directory]);

        // Named for the ticker actually checked, not a fixed one.
        $path = $directory.DIRECTORY_SEPARATOR.'provider-smoke-bbca.txt';
        $this->assertFileExists($path);
        $this->assertStringContainsString('provider_smoke_status=PASS', file_get_contents($path));

        @unlink($path);
        @rmdir($directory);
    }

    /**
     * The property the forbidden-symbol list stands in for: a successful smoke run writes
     * nothing anywhere.
     */
    public function test_a_successful_smoke_run_writes_nothing(): void
    {
        $this->bindAdapterReturning([$this->bar()]);

        $before = $this->artifactRowCounts();

        [$exitCode, $display] = $this->runSmoke();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('provider_smoke_status=PASS', $display);
        $this->assertStringContainsString('reason_code=PROVIDER_SMOKE_OK', $display);
        $this->assertSame($before, $this->artifactRowCounts(), 'Provider smoke must not write to any artifact table.');
    }

    /**
     * And it says so in its own output, so an operator reading the artifact does not have to
     * take it on trust.
     */
    public function test_the_output_states_that_nothing_was_published(): void
    {
        $this->bindAdapterReturning([$this->bar()]);

        [, $display] = $this->runSmoke();

        foreach ([
            'full_universe_fetch=false',
            'publication_created=false',
            'seal_executed=false',
            'finalize_executed=false',
            'pointer_switched=false',
            'readable_publication_created=false',
        ] as $claim) {
            $this->assertStringContainsString($claim, $display);
        }
    }

    /**
     * A provider that returned nothing usable is a provider problem, not a platform fault.
     * This replaces an assertion that matched two exact lines of formatted source.
     */
    public function test_an_empty_response_is_blocked_rather_than_failed(): void
    {
        $this->bindAdapterReturning([]);

        [$exitCode, $display] = $this->runSmoke();

        $this->assertStringContainsString('provider_smoke_status=BLOCKED', $display);
        $this->assertStringContainsString('reason_code=PROVIDER_EMPTY_OR_INVALID_RESPONSE', $display);
        $this->assertStringNotContainsString('provider_smoke_status=FAILED', $display);
        $this->assertSame(2, $exitCode);
    }

    /**
     * Every provider-side condition is BLOCKED with exit 2. The exit code is what a scheduler
     * reads, so this is the difference between "retry later" and "wake someone up".
     *
     * @dataProvider providerSideConditions
     */
    public function test_provider_side_conditions_are_blocked_with_exit_two(
        string $sourceReasonCode,
        array $context,
        string $expectedReasonCode,
        string $message
    ): void {
        $this->bindAdapterThrowing($sourceReasonCode, $message, $context);

        [$exitCode, $display] = $this->runSmoke();

        $this->assertStringContainsString('provider_smoke_status=BLOCKED', $display);
        $this->assertStringContainsString('reason_code='.$expectedReasonCode, $display);
        $this->assertSame(2, $exitCode);
    }

    public function providerSideConditions(): array
    {
        return [
            'rate limited by http status' => ['RUN_SOURCE_HTTP_ERROR', ['final_http_status' => 429], 'PROVIDER_RATE_LIMITED', 'Too Many Requests'],
            'rate limited by source code' => ['RUN_SOURCE_RATE_LIMIT', [], 'PROVIDER_RATE_LIMITED', 'rate limited'],
            'header context rejected' => ['RUN_SOURCE_HTTP_ERROR', ['header_context_mismatch' => true], 'PROVIDER_REQUEST_HEADER_CONTEXT_MISMATCH', 'forbidden'],
            'trade date absent from response' => ['RUN_SOURCE_NO_VALID_DATA', ['trade_date_not_found_in_response' => true], 'PROVIDER_TRADE_DATE_NOT_FOUND_IN_RESPONSE', 'no row for date'],
            'timeout' => ['RUN_SOURCE_TIMEOUT', ['final_http_status' => 408], 'PROVIDER_TIMEOUT', 'request timed out'],
            'malformed payload' => ['RUN_SOURCE_MALFORMED_PAYLOAD', [], 'PROVIDER_RESPONSE_PARSE_FAILED', 'bad json'],
        ];
    }

    /**
     * A SourceAcquisitionException always describes something the adapter observed at the
     * provider, so an unrecognised source code still resolves to a provider-side condition. The
     * fallback is deliberately BLOCKED rather than FAILED: the adapter said the source misbehaved
     * and this command has no better information.
     */
    public function test_an_unrecognised_source_code_still_resolves_to_a_provider_condition(): void
    {
        $this->bindAdapterThrowing('RUN_SOURCE_UNKNOWN_STATE', 'something the adapter did not classify', []);

        [$exitCode, $display] = $this->runSmoke();

        $this->assertStringContainsString('provider_smoke_status=BLOCKED', $display);
        $this->assertStringContainsString('source_reason_code=RUN_SOURCE_UNKNOWN_STATE', $display);
        $this->assertSame(2, $exitCode);
    }

    /**
     * A crash that is not a SourceAcquisitionException is a fault in this platform.
     *
     * It used to be reported as PROVIDER_NETWORK_ERROR with exit 2, which tells a scheduler the
     * network was flaky and to retry later — so a bug inside the adapter would be retried
     * indefinitely and never looked at. FAILED with exit 1 is what sends someone to the code.
     */
    public function test_an_unexpected_crash_is_reported_as_a_platform_failure(): void
    {
        $adapter = m::mock(PublicApiEodBarsAdapter::class);
        $adapter->shouldReceive('fetchOrLoadEodBars')->andThrow(new TypeError('Argument must be array, string given'));
        $this->app->instance(PublicApiEodBarsAdapter::class, $adapter);

        [$exitCode, $display] = $this->runSmoke();

        $this->assertStringContainsString('provider_smoke_status=FAILED', $display);
        $this->assertStringContainsString('reason_code=COMMAND_EXECUTION_FAILED', $display);
        $this->assertStringNotContainsString('reason_code=PROVIDER_NETWORK_ERROR', $display);
        $this->assertSame(1, $exitCode);
    }

    /**
     * A smoke check is one question about one ticker. Fanning out would turn a health probe into
     * a full provider sweep against a rate-limited free API.
     *
     * @dataProvider fanOutAttempts
     */
    public function test_a_request_for_more_than_one_ticker_is_refused(array $options): void
    {
        $this->bindAdapterReturning([$this->bar()]);

        [$exitCode, $display] = $this->runSmoke($options);

        $this->assertStringContainsString('reason_code=PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED', $display);
        $this->assertSame(1, $exitCode);
    }

    public function fanOutAttempts(): array
    {
        return [
            'several tickers in one option' => [['--ticker' => 'BBCA,BBRI,TLKM']],
            'tickers separated by spaces' => [['--ticker' => 'BBCA BBRI']],
            'max tickers raised' => [['--max-tickers' => '50']],
        ];
    }

    /**
     * @dataProvider malformedRequests
     */
    public function test_a_malformed_request_is_refused_before_the_provider_is_called(array $options, string $expectedReasonCode): void
    {
        $adapter = m::mock(PublicApiEodBarsAdapter::class);
        $adapter->shouldNotReceive('fetchOrLoadEodBars');
        $this->app->instance(PublicApiEodBarsAdapter::class, $adapter);

        [$exitCode, $display] = $this->runSmoke($options);

        $this->assertStringContainsString('reason_code='.$expectedReasonCode, $display);
        $this->assertSame(1, $exitCode);
    }

    public function malformedRequests(): array
    {
        return [
            'no ticker' => [['--ticker' => ''], 'PROVIDER_SMOKE_TICKER_REQUIRED'],
            'ticker with illegal characters' => [['--ticker' => 'BB;DROP'], 'PROVIDER_SMOKE_INVALID_TICKER'],
            'unparseable date' => [['--trade_date' => 'yesterday'], 'COMMAND_INVALID_DATE_FORMAT'],
        ];
    }
}
