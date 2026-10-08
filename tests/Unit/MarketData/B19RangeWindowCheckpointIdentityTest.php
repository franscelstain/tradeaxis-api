<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * `MD-B19` — API range-window checkpoint identity, failure-telemetry isolation, and the resume
 * outcome vocabulary (`MD-S053-R0202..R0217`, "API range-window checkpoint/resume addendum").
 *
 * `ApiBackfillRangeAcquisitionServiceTest` asserts individual checkpoint fields for individual
 * failures. What it does not do is put two *different* failures, and a success, in one window and
 * ask whose telemetry lands on whose row. That is the shape of the defect the contract names:
 *
 *   > failed checkpoint `reason_code`, `http_status`, `error_sample`, `provider_error_sample`,
 *   > `sanitized_url`, `failure_scope`, `attempt_count`, and `rows_count` must come from the same
 *   > checkpoint identity
 *   > timeout/non-HTTP failure must not inherit HTTP status or provider body from a different ticker
 *
 * so every scenario below mixes a provider-rejected ticker (with its own HTTP status, body and
 * URL), a transport-timeout ticker (retried, so its attempt count differs from every other count in
 * the window), and a ticker that succeeds — and runs it with the two failing tickers in both
 * orders, because a leak from "the previous ticker" and a leak from "the window's first failure"
 * are different defects.
 *
 * `MD-S053-R0210` is decided by the project owner (`D-MD-B19-A001-002`, Option A, finding
 * `F-MD-B19-A001-004`): every per-execution request-result field of a checkpoint row comes from the
 * same ticker execution. A success row's `http_status` is the status of its own request and its
 * `attempt_count` the attempts of its own request; neither may come from a neighbour or from the
 * window. Window-level totals belong in the window telemetry, which is asserted to keep them.
 */
class B19RangeWindowCheckpointIdentityTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    private const WINDOW = '2026-05-01|2026-05-07|';

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    // ------------------------------------------------------------------ helpers

    /** @return array<string,mixed> */
    private function config(int $windowDays = 90, int $retryMax = 0, array $backfill = []): array
    {
        return [
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'provider' => ['api_retry_max' => $retryMax, 'api_backoff_ms' => 0, 'api_throttle_qps' => 1000],
                'source' => [
                    'default_source_name' => 'YAHOO_FINANCE',
                    'api_backfill' => array_merge([
                        'window_days' => $windowDays, 'warmup_days' => 120, 'concurrency' => 5,
                        'max_dates_per_run' => 20, 'collect_all_errors' => false, 'default_error_policy' => 'stop_on_error',
                    ], $backfill),
                    'api' => [
                        'provider' => 'yahoo_finance',
                        'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
                        'response_format' => 'json', 'response_rows_path' => '', 'timeout_seconds' => 3,
                        'auth_header_name' => '', 'auth_token' => '', 'source_name' => 'YAHOO_FINANCE',
                        'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
                    ],
                ],
            ],
        ];
    }

    private function adapter(callable $fetcher): PublicApiEodBarsAdapter
    {
        $identities = $this->getMockBuilder(TemporalIdentityRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['resolveProviderContext'])
            ->getMock();
        $identities->method('resolveProviderContext')->willReturnCallback(function ($tickerCode, $provider, $tradeDate) {
            $code = strtoupper(trim((string) $tickerCode));
            $id = (int) sprintf('%u', crc32($code));

            return [
                'listing_id' => $id, 'ticker_id' => $id, 'ticker_code' => $code, 'provider' => $provider,
                'provider_symbol' => $code.'.JK', 'provider_mapping_id' => $id, 'mapping_revision' => 'TEST-'.$code.'-'.$tradeDate,
                'listing_symbol_id' => $id, 'identity_recorded_at' => $tradeDate.' 00:00:00',
            ];
        });

        return new PublicApiEodBarsAdapter($fetcher, new EquityProviderSymbolResolver($identities));
    }

    /** One bar dated at the start of the window the URL asks for (WIB midnight, as the provider returns it). */
    private function barFor(string $url): array
    {
        preg_match('/period1=(\d+)/', $url, $m);
        $ts = isset($m[1]) ? (int) $m[1] : 1777568400;

        return [
            'status' => 200, 'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['chart' => ['result' => [[
                'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'], 'timestamp' => [$ts],
                'indicators' => ['quote' => [['open' => [100], 'high' => [110], 'low' => [99], 'close' => [108], 'volume' => [100000]]], 'adjclose' => [['adjclose' => [108]]]],
            ]]]]),
        ];
    }

    private function service(callable $fetcher): ApiBackfillRangeAcquisitionService
    {
        return new ApiBackfillRangeAcquisitionService($this->adapter($fetcher));
    }

    // ------------------------------------------------------------------ R0202 R0204 R0205 R0206

    /**
     * `MD-S053-R0202`: checkpoint rows at window/ticker granularity. `R0204`, `R0205`, `R0206`:
     * the identity is `window_start`, `window_end`, `ticker_code`.
     *
     * Three windows and two tickers, with one ticker failing in the middle window only. A
     * checkpoint keyed by ticker alone, or by window alone, collapses the six rows; a row whose
     * identity fields disagree with its key cannot be resumed against.
     */
    public function test_every_window_and_ticker_pair_has_its_own_checkpoint_carrying_that_identity(): void
    {
        $this->bindMarketDataConfig($this->config(3));
        $middleStart = Carbon::parse('2026-05-04', 'Asia/Jakarta')->timestamp;

        $service = $this->service(function ($url) use ($middleStart) {
            if (strpos($url, 'AAAA.JK') !== false && strpos($url, 'period1='.$middleStart) !== false) {
                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"AAAA_MIDDLE"}}}'];
            }

            return $this->barFor($url);
        });
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-09', ['2026-05-01', '2026-05-04', '2026-05-07'], ['AAAA', 'BBBB']);

        $expected = [];
        foreach ([['2026-05-01', '2026-05-03'], ['2026-05-04', '2026-05-06'], ['2026-05-07', '2026-05-09']] as [$start, $end]) {
            foreach (['AAAA', 'BBBB'] as $ticker) {
                $expected[] = $start.'|'.$end.'|'.$ticker;
            }
        }
        $rows = $result['source_acquisition_checkpoints'];
        $keys = array_keys($rows);
        sort($keys);
        $this->assertSame($expected, $keys, 'one checkpoint per (window, ticker) pair is required');

        foreach ($rows as $key => $row) {
            [$start, $end, $ticker] = explode('|', $key);
            $this->assertSame($start, $row['window_start'], $key.': window_start does not match the identity');
            $this->assertSame($end, $row['window_end'], $key.': window_end does not match the identity');
            $this->assertSame($ticker, $row['ticker_code'], $key.': ticker_code does not match the identity');
        }

        // Granularity, not just count: the failure of AAAA in the middle window stays on that one row.
        $failed = [];
        foreach ($rows as $key => $row) {
            if ($row['state'] === 'FAILED') {
                $failed[] = $key;
            }
        }
        $this->assertSame(['2026-05-04|2026-05-06|AAAA'], $failed, 'a failure leaked beyond its own window/ticker checkpoint');
    }

    // ------------------------------------------------------------------ R0208 R0209

    /** @return array<string,array{0:string,1:string}> [rejectedTicker, timeoutTicker] in both iteration orders */
    public function failingTickerOrders(): array
    {
        return [
            'provider-rejected ticker iterates before the timeout ticker' => ['AAAA', 'TTTT'],
            'provider-rejected ticker iterates after the timeout ticker' => ['ZZ400', 'TTTT'],
        ];
    }

    /**
     * `MD-S053-R0208`: reason code, HTTP status, error sample, provider error sample, sanitized
     * URL, failure scope, attempt count and rows count come from the same checkpoint identity.
     * `R0209`: a timeout must not inherit the HTTP status or provider body of a different ticker.
     *
     * The timeout ticker is retried twice (three attempts), the rejected ticker is not retried
     * (one attempt), and the window as a whole made five requests — so an attempt count borrowed
     * from the window or from a neighbour matches neither row.
     *
     * @dataProvider failingTickerOrders
     */
    public function test_a_failed_checkpoint_takes_every_failure_field_from_its_own_identity(string $rejected, string $timeout): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $service = $this->service(function ($url) use ($rejected, $timeout) {
            if (strpos($url, $rejected.'.JK') !== false) {
                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"'.$rejected.'_ONLY_BODY"}}}'];
            }
            if (strpos($url, $timeout.'.JK') !== false) {
                throw new RuntimeException('Timeout while fetching '.$timeout.'.JK');
            }

            return $this->barFor($url);
        });

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], [$rejected, $timeout, 'BBCA']);
        $rows = $result['source_acquisition_checkpoints'];

        $a = $rows[self::WINDOW.$rejected];
        $this->assertSame('FAILED', $a['state']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $a['reason_code']);
        $this->assertSame(400, $a['http_status']);
        $this->assertStringContainsString($rejected.'_ONLY_BODY', (string) $a['provider_error_sample']);
        $this->assertStringContainsString($rejected.'_ONLY_BODY', (string) $a['error_sample']);
        $this->assertStringContainsString($rejected.'.JK', (string) $a['sanitized_url']);
        $this->assertSame('ticker', $a['failure_scope']);
        $this->assertSame(1, $a['attempt_count'], 'attempt_count is not the rejected ticker\'s own');
        $this->assertSame(0, $a['rows_count']);

        $t = $rows[self::WINDOW.$timeout];
        $this->assertSame('FAILED', $t['state']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $t['reason_code']);
        $this->assertNull($t['http_status'], 'a timeout inherited an HTTP status from a different ticker');
        $this->assertNull($t['provider_error_sample'], 'a timeout inherited a provider body from a different ticker');
        $this->assertStringContainsString('Timeout while fetching '.$timeout.'.JK', (string) $t['error_sample']);
        $this->assertStringContainsString($timeout.'.JK', (string) $t['sanitized_url']);
        $this->assertSame('ticker', $t['failure_scope']);
        $this->assertSame(3, $t['attempt_count'], 'attempt_count is not the timeout ticker\'s own (three attempts)');
        $this->assertSame(0, $t['rows_count']);

        // Neither failed row mentions the other ticker anywhere.
        $this->assertStringNotContainsString($rejected, json_encode($t), 'the timeout row carries the rejected ticker\'s telemetry');
        $this->assertStringNotContainsString($timeout, json_encode($a), 'the rejected row carries the timeout ticker\'s telemetry');
    }

    /**
     * `MD-S053-R0208`, `rows_count`: a ticker that returned a warmup row but not the requested date
     * fails with the rows it did return, not with the window's total.
     */
    public function test_rows_count_is_the_failed_tickers_own_returned_rows(): void
    {
        $this->bindMarketDataConfig($this->config(90));
        $service = $this->service(function ($url) {
            if (strpos($url, 'BBCA.JK') !== false) {
                // BBCA answers for both the warmup date and the requested date.
                preg_match('/period1=(\d+)/', $url, $m);

                return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode(['chart' => ['result' => [[
                    'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'], 'timestamp' => [(int) $m[1], (int) $m[1] + 3 * 86400],
                    'indicators' => ['quote' => [['open' => [100, 101], 'high' => [110, 111], 'low' => [99, 100], 'close' => [108, 109], 'volume' => [100000, 100001]]], 'adjclose' => [['adjclose' => [108, 109]]]],
                ]]]])];
            }

            return $this->barFor($url); // MISS answers for the warmup date only
        });

        $result = $service->acquire('2026-05-01', '2026-05-04', '2026-05-04', ['2026-05-01', '2026-05-04'], ['BBCA', 'MISS']);
        $rows = $result['source_acquisition_checkpoints'];

        $this->assertSame('SUCCESS', $rows['2026-05-01|2026-05-04|BBCA']['state']);
        $this->assertSame(2, $rows['2026-05-01|2026-05-04|BBCA']['rows_count']);
        $this->assertSame('FAILED', $rows['2026-05-01|2026-05-04|MISS']['state']);
        $this->assertSame(1, $rows['2026-05-01|2026-05-04|MISS']['rows_count'], 'rows_count is not the failed ticker\'s own returned rows');
    }

    // ------------------------------------------------------------------ R0210

    /**
     * `MD-S053-R0210`: successful checkpoint rows must not carry stale failure sample fields.
     *
     * The failure-describing fields. `http_status` and `attempt_count` of a success row are asserted by
     * the Option A guards below.
     *
     * @dataProvider failingTickerOrders
     */
    public function test_a_successful_checkpoint_carries_no_failure_field_of_a_failed_neighbour(string $rejected, string $timeout): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $service = $this->service(function ($url) use ($rejected, $timeout) {
            if (strpos($url, $rejected.'.JK') !== false) {
                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"'.$rejected.'_ONLY_BODY"}}}'];
            }
            if (strpos($url, $timeout.'.JK') !== false) {
                throw new RuntimeException('Timeout while fetching '.$timeout.'.JK');
            }

            return $this->barFor($url);
        });

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], [$rejected, $timeout, 'BBCA']);
        $ok = $result['source_acquisition_checkpoints'][self::WINDOW.'BBCA'];

        $this->assertSame('SUCCESS', $ok['state']);
        foreach (['reason_code', 'error_sample', 'provider_error_sample', 'sanitized_url', 'failure_scope', 'date_level_reason_code'] as $field) {
            $this->assertNull($ok[$field], 'a successful checkpoint carries a stale failure field: '.$field);
        }
        $this->assertSame('PASS', $ok['date_level_status']);
        $this->assertSame([], $ok['missing_trade_dates']);
        $this->assertSame(1, $ok['rows_count']);
        $this->assertStringNotContainsString('ONLY_BODY', json_encode($ok));
        $this->assertStringNotContainsString('Timeout while', json_encode($ok));
    }

    // ------------------------------------------------------------------ R0210 (Option A)

    /**
     * A provider that answers each ticker from a script: ticker => list of answers per attempt
     * ('ok', 'timeout', or an HTTP status). The last answer repeats.
     *
     * @param array<string,array<int,mixed>> $script
     */
    private function scripted(array $script): callable
    {
        $calls = [];

        return function ($url) use ($script, &$calls) {
            foreach ($script as $ticker => $answers) {
                if (strpos($url, $ticker.'.JK') === false) {
                    continue;
                }
                $n = $calls[$ticker] = ($calls[$ticker] ?? 0) + 1;
                $answer = $answers[min($n, count($answers)) - 1];
                if ($answer === 'ok') {
                    return $this->barFor($url);
                }
                if ($answer === 'timeout') {
                    throw new RuntimeException('Timeout while fetching '.$ticker.'.JK');
                }

                return ['status' => $answer, 'body' => $answer >= 500 ? '' : '{"chart":{"error":{"code":"x","description":"'.$ticker.'"}}}'];
            }
            $this->fail('unexpected request '.$url);
        };
    }

    /**
     * `MD-S053-R0210`, Option A: a success row carries the HTTP status and the attempt count of its
     * own request. `BBCA` succeeds on its second attempt after a transient 503, so its own values
     * (200, 2) differ from the status of every neighbour (400) and from the window total.
     */
    public function test_a_success_checkpoint_carries_the_status_and_attempts_of_its_own_request(): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $service = $this->service($this->scripted(['BBCA' => [503, 'ok'], 'TTTT' => ['timeout'], 'ZZ400' => [400]]));

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'TTTT', 'ZZ400']);
        $ok = $result['source_acquisition_checkpoints'][self::WINDOW.'BBCA'];

        $this->assertSame('SUCCESS', $ok['state']);
        $this->assertSame(200, $ok['http_status'], 'a success row must carry the status of its own request, not a neighbour\'s');
        $this->assertSame(2, $ok['attempt_count'], 'a success row must carry the attempts of its own request, not the window total');
        // The totals the row must NOT carry are real and different: 2 + 3 + 1 attempts in the window.
        $this->assertSame(6, $result['window_telemetry'][0]['attempt_count']);
    }

    /**
     * Changing a neighbour's failure, status or retry count does not change the success row; the
     * window-level totals do change, and stay in the window telemetry.
     */
    public function test_a_neighbours_failure_status_and_retries_do_not_change_a_success_row(): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $rows = [];
        $windows = [];
        foreach ([
            'neighbours reject (400) and time out (3 attempts)' => ['BBCA' => [503, 'ok'], 'TTTT' => ['timeout'], 'ZZ400' => [400]],
            'neighbours answer 404 and succeed first time' => ['BBCA' => [503, 'ok'], 'TTTT' => ['ok'], 'ZZ400' => [404]],
            'one neighbour needs two attempts, the other answers 400' => ['BBCA' => [503, 'ok'], 'TTTT' => [503, 'ok'], 'ZZ400' => [400]],
        ] as $label => $script) {
            $result = $this->service($this->scripted($script))->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'TTTT', 'ZZ400']);
            $row = $result['source_acquisition_checkpoints'][self::WINDOW.'BBCA'];
            $rows[$label] = [$row['state'], $row['http_status'], $row['attempt_count']];
            $windows[$label] = $result['window_telemetry'][0]['attempt_count'];
        }

        $this->assertCount(1, array_unique(array_map('json_encode', $rows)), 'a success row changed when only its neighbours changed: '.json_encode($rows));
        $this->assertSame(['SUCCESS', 200, 2], reset($rows));
        $this->assertGreaterThan(1, count(array_unique($windows)), 'the fixture must change the window total, or this guard proves nothing');
    }

    /**
     * Two successful tickers with different attempts of their own: each row keeps its own values in
     * either assignment of the retry to a ticker, so identities cannot have been swapped or averaged.
     */
    public function test_two_success_rows_keep_their_own_attempts_whichever_ticker_was_retried(): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        foreach ([['BBCA', 'BBRI'], ['BBRI', 'BBCA']] as [$retried, $plain]) {
            $service = $this->service($this->scripted([$retried => [503, 503, 'ok'], $plain => ['ok'], 'ZZ400' => [400]]));
            $rows = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BBRI', 'ZZ400'])['source_acquisition_checkpoints'];

            $this->assertSame(3, $rows[self::WINDOW.$retried]['attempt_count'], $retried.' was retried twice');
            $this->assertSame(1, $rows[self::WINDOW.$plain]['attempt_count'], $plain.' made one request');
            $this->assertSame(200, $rows[self::WINDOW.$retried]['http_status']);
            $this->assertSame(200, $rows[self::WINDOW.$plain]['http_status']);
        }
    }

    /**
     * A success row that is processed AFTER a failed neighbour keeps its own status and attempts.
     * (The window's "last status seen" is the neighbour's at that point, so a success row reading the
     * window state instead of its own request shows the neighbour's status here.)
     *
     * @dataProvider failureStatusesBeforeASuccess
     */
    public function test_a_success_row_processed_after_a_failed_neighbour_keeps_its_own_status(int $neighbourStatus): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $service = $this->service($this->scripted(['AAAA' => [$neighbourStatus], 'BBCA' => [503, 'ok']]));

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['AAAA', 'BBCA']);
        $rows = $result['source_acquisition_checkpoints'];

        $this->assertSame('FAILED', $rows[self::WINDOW.'AAAA']['state']);
        $this->assertSame($neighbourStatus, $rows[self::WINDOW.'AAAA']['http_status']);
        $this->assertSame('SUCCESS', $rows[self::WINDOW.'BBCA']['state']);
        $this->assertSame(200, $rows[self::WINDOW.'BBCA']['http_status'], 'a success row after a failed neighbour showed the neighbour\'s status');
        $this->assertSame(2, $rows[self::WINDOW.'BBCA']['attempt_count']);
        $this->assertSame(1, $rows[self::WINDOW.'AAAA']['attempt_count']);
    }

    /** @return array<string,array{0:int}> */
    public function failureStatusesBeforeASuccess(): array
    {
        return ['bad request' => [400], 'unknown symbol' => [404]];
    }

    /**
     * The same rule for a FAILED row without a failure context of its own (the ticker answered, but
     * not for the requested date): its attempts are its own, not the window's.
     */
    public function test_a_date_level_failure_row_carries_the_attempts_of_its_own_request(): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $service = $this->service($this->scripted(['MISS' => [503, 'ok'], 'BBCA' => ['ok'], 'ZZ400' => [400]]));

        $rows = $service->acquire('2026-05-01', '2026-05-04', '2026-05-04', ['2026-05-01', '2026-05-04'], ['BBCA', 'MISS', 'ZZ400'])['source_acquisition_checkpoints'];
        $miss = $rows['2026-05-01|2026-05-04|MISS'];

        $this->assertSame('FAILED', $miss['state']);
        $this->assertSame('RUN_SOURCE_MISSING_REQUESTED_DATE_ROW', $miss['reason_code']);
        $this->assertSame(2, $miss['attempt_count'], 'a date-level failure row carried an attempt count that is not its own');
    }

    /**
     * Window-level aggregates are not lost: they stay on the window telemetry, which is the
     * window-scoped surface Option A names.
     */
    public function test_window_totals_remain_available_on_the_window_telemetry(): void
    {
        $this->bindMarketDataConfig($this->config(90, 2));
        $service = $this->service($this->scripted(['BBCA' => [503, 'ok'], 'TTTT' => ['timeout'], 'ZZ400' => [400]]));

        $window = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'TTTT', 'ZZ400'])['window_telemetry'][0];

        $this->assertSame(6, $window['attempt_count']);
        $this->assertArrayHasKey('final_http_status', $window);
        $this->assertSame(3, $window['expected_ticker_count']);
    }

    // ------------------------------------------------------------------ R0212..R0215

    /** @return array<string,array{0:array<string,string>,1:array<string,string>,2:string,3:array<string,int>}> */
    public function resumeScenarios(): array
    {
        // checkpoint states, ticker => how the retry answers ('ok'|'bad'), expected state, expected counts
        return [
            'every eligible failed checkpoint recovers' => [
                ['BBRI' => 'FAILED', 'TLKM' => 'FAILED'], ['BBRI' => 'ok', 'TLKM' => 'ok'],
                'RETRY_SUCCESS', ['failed_checkpoint_total' => 2, 'failed_checkpoint_retried' => 2, 'retry_success_count' => 2, 'retry_failed_count' => 0],
            ],
            'some recover and some do not' => [
                ['BBRI' => 'FAILED', 'TLKM' => 'FAILED'], ['BBRI' => 'ok', 'TLKM' => 'bad'],
                'PARTIAL_RETRY_SUCCESS', ['failed_checkpoint_total' => 2, 'failed_checkpoint_retried' => 2, 'retry_success_count' => 1, 'retry_failed_count' => 1],
            ],
            'none recover' => [
                ['BBRI' => 'FAILED', 'TLKM' => 'FAILED'], ['BBRI' => 'bad', 'TLKM' => 'bad'],
                'FAILED_RETRY_BLOCKED', ['failed_checkpoint_total' => 2, 'failed_checkpoint_retried' => 2, 'retry_success_count' => 0, 'retry_failed_count' => 2],
            ],
            'nothing had failed' => [
                ['BBRI' => 'SUCCESS', 'TLKM' => 'SUCCESS'], ['BBRI' => 'ok', 'TLKM' => 'ok'],
                'NO_FAILED_CHECKPOINT', ['failed_checkpoint_total' => 0, 'failed_checkpoint_retried' => 0, 'retry_success_count' => 0, 'retry_failed_count' => 0],
            ],
        ];
    }

    /**
     * `MD-S053-R0212..R0215`: `RETRY_SUCCESS`, `PARTIAL_RETRY_SUCCESS`, `FAILED_RETRY_BLOCKED`,
     * `NO_FAILED_CHECKPOINT`. One table: each state is produced by its own situation and by no
     * other, so a state returned for the wrong situation fails a neighbouring row of the table.
     *
     * @dataProvider resumeScenarios
     */
    public function test_each_resume_state_is_reported_only_in_its_own_situation(array $checkpointStates, array $retry, string $expectedState, array $expectedCounts): void
    {
        $this->bindMarketDataConfig($this->config());
        $fetched = [];
        $service = $this->service(function ($url) use ($retry, &$fetched) {
            foreach ($retry as $ticker => $answer) {
                if (strpos($url, $ticker.'.JK') !== false) {
                    $fetched[] = $ticker;

                    return $answer === 'ok' ? $this->barFor($url) : ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"'.$ticker.'"}}}'];
                }
            }
            $this->fail('unexpected fetch '.$url);
        });

        $checkpoint = [];
        foreach ($checkpointStates as $ticker => $state) {
            $checkpoint[self::WINDOW.$ticker] = ['state' => $state];
        }
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], array_keys($checkpointStates), [
            'resume' => true, 'only_failed' => true, 'source_acquisition_checkpoint' => $checkpoint,
        ]);

        $this->assertSame($expectedState, $result['source_acquisition_state']);
        $this->assertSame($expectedState, $result['source_final_status']);
        foreach ($expectedCounts as $field => $count) {
            $this->assertSame($count, $result[$field], $field);
        }
        if ($expectedState === 'NO_FAILED_CHECKPOINT') {
            $this->assertSame([], $fetched, 'a resume with nothing to retry fetched from the provider');
        }
    }

    // ------------------------------------------------------------------ R0216

    /** @return array<string,array{0:callable,1:bool}> provider answer => whether the resume must be reported as systemic */
    public function systemicAndNonSystemicFailures(): array
    {
        return [
            'authentication refused (provider/config)' => [function () { return ['status' => 401, 'body' => '']; }, true],
            'forbidden (provider/config)' => [function () { return ['status' => 403, 'body' => '']; }, true],
            'provider rejects the requested range' => [function () { return ['status' => 422, 'body' => '{"chart":{"error":{"description":"bad period"}}}']; }, true],
            'timeout of one ticker' => [function () { return ['status' => 504, 'body' => '']; }, false],
            'bad request for one ticker' => [function () { return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"x"}}}']; }, false],
            'unknown symbol for one ticker' => [function () { return ['status' => 404, 'body' => '']; }, false],
        ];
    }

    /**
     * `MD-S053-R0216`: `SYSTEMIC_FAILED` only for true global/provider/config acquisition failure.
     *
     * Both directions, because a restriction is invisible to a test that never supplies the case it
     * excludes. Per-ticker failures stay `FAILED_RETRY_BLOCKED`; authentication and provider-range
     * rejection escalate.
     *
     * @dataProvider systemicAndNonSystemicFailures
     */
    public function test_systemic_failed_is_reported_only_for_global_provider_or_config_failures(callable $answer, bool $systemic): void
    {
        $this->bindMarketDataConfig($this->config());
        $service = $this->service($answer);

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BBRI'], [
            'resume' => true, 'only_failed' => true,
            'source_acquisition_checkpoint' => [self::WINDOW.'BBCA' => ['state' => 'FAILED'], self::WINDOW.'BBRI' => ['state' => 'FAILED']],
        ]);

        if ($systemic) {
            $this->assertSame('SYSTEMIC_FAILED', $result['source_acquisition_state']);
        } else {
            $this->assertNotSame('SYSTEMIC_FAILED', $result['source_acquisition_state'], 'a per-ticker failure was escalated to a systemic verdict');
            $this->assertSame('FAILED_RETRY_BLOCKED', $result['source_acquisition_state']);
        }
    }

    /** A missing endpoint template is a configuration fault, not a ticker fault. */
    public function test_a_missing_endpoint_configuration_is_systemic(): void
    {
        $config = $this->config();
        $config['market_data']['source']['api']['endpoint_template'] = '';
        $this->bindMarketDataConfig($config);
        $service = $this->service(function () {
            $this->fail('no request may be made without an endpoint');
        });

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA'], [
            'resume' => true, 'only_failed' => true,
            'source_acquisition_checkpoint' => [self::WINDOW.'BBCA' => ['state' => 'FAILED']],
        ]);

        $this->assertSame('SYSTEMIC_FAILED', $result['source_acquisition_state']);
    }

    // ------------------------------------------------------------------ R0217

    /**
     * `MD-S053-R0217`: resume-only-failed diagnostics carry failed-checkpoint total, eligible,
     * retried and skipped counts, retry success and failure counts, and the skipped reasons.
     *
     * A mixed checkpoint file: one eligible failure that recovers, one eligible failure that does
     * not, one failure outside the current windows, one for a ticker outside the current universe,
     * one with no readable identity, and one that had succeeded. Every named quantity is asserted
     * and the quantities are tied to each other, so a count that is carried but wrong fails.
     */
    public function test_resume_diagnostics_carry_every_count_and_the_skipped_reasons(): void
    {
        $this->bindMarketDataConfig($this->config());
        $service = $this->service(function ($url) {
            if (strpos($url, 'TLKM.JK') !== false) {
                return ['status' => 400, 'body' => '{"chart":{"error":{"code":"Bad Request","description":"TLKM"}}}'];
            }

            return $this->barFor($url);
        });

        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BBRI', 'TLKM'], [
            'resume' => true, 'only_failed' => true,
            'source_acquisition_checkpoint' => [
                self::WINDOW.'BBCA' => ['state' => 'SUCCESS'],
                self::WINDOW.'BBRI' => ['state' => 'FAILED'],
                self::WINDOW.'TLKM' => ['state' => 'FAILED'],
                '2026-04-01|2026-04-30|BBCA' => ['state' => 'FAILED'],
                self::WINDOW.'GOTO' => ['state' => 'FAILED'],
                'unparseable' => ['state' => 'FAILED'],
            ],
        ]);

        $this->assertSame(5, $result['failed_checkpoint_total']);
        $this->assertSame(2, $result['failed_checkpoint_eligible']);
        $this->assertSame(2, $result['failed_checkpoint_retried']);
        $this->assertSame(1, $result['retry_success_count']);
        $this->assertSame(1, $result['retry_failed_count']);
        $this->assertSame(3, $result['failed_checkpoint_skipped']);
        $this->assertSame(3, $result['skipped_failed_checkpoint_count']);
        $this->assertSame(['WINDOW_OUT_OF_SCOPE' => 1, 'TICKER_NOT_IN_CURRENT_UNIVERSE' => 1, 'CHECKPOINT_CORRUPTED' => 1], $result['skipped_failed_checkpoint_reasons']);

        // The quantities are tied together.
        $this->assertSame($result['failed_checkpoint_total'], $result['failed_checkpoint_eligible'] + $result['failed_checkpoint_skipped']);
        $this->assertSame($result['failed_checkpoint_retried'], $result['retry_success_count'] + $result['retry_failed_count']);
        $this->assertSame($result['failed_checkpoint_skipped'], array_sum($result['skipped_failed_checkpoint_reasons']));
        $this->assertSame('PARTIAL_RETRY_SUCCESS', $result['source_acquisition_state']);
    }
}
