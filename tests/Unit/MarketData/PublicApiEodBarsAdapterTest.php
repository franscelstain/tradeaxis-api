<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Observation\InMemorySourceObservationRecorder;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use PHPUnit\Framework\TestCase;

class PublicApiEodBarsAdapterTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    public function test_api_adapter_normalizes_json_rows_using_configured_field_map()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
            'response_rows_path' => 'data.items',
            'source_name' => 'API_FREE',
            'field_map' => [
                'ticker_code' => 'symbol',
                'trade_date' => 'date',
                'open' => 'ohlc.open',
                'high' => 'ohlc.high',
                'low' => 'ohlc.low',
                'close' => 'ohlc.close',
                'volume' => 'volume',
                'adj_close' => 'adjClose',
                'source_row_ref' => 'rowRef',
                'captured_at' => 'capturedAt',
            ],
        ]));

        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'data' => [
                        'items' => [
                            [
                                'symbol' => 'bbca',
                                'date' => '2026-03-20',
                                'ohlc' => ['open' => '100', 'high' => '110', 'low' => '99', 'close' => '108'],
                                'volume' => '100000',
                                'adjClose' => '108',
                                'rowRef' => 'api-1',
                                'capturedAt' => '2026-03-20T17:20:00+07:00',
                            ],
                        ],
                    ],
                ]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-20', 'api');

        $this->assertCount(1, $rows);
        $this->assertSame('BBCA', $rows[0]['ticker_code']);
        $this->assertSame('2026-03-20', $rows[0]['trade_date']);
        $this->assertSame('API_FREE', $rows[0]['source_name']);
        $this->assertSame('api-1', $rows[0]['source_row_ref']);
    }


    public function test_api_adapter_normalizes_yahoo_finance_chart_payload_using_ticker_universe()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ]));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [1773828000],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100],
                                    'high' => [110],
                                    'low' => [99],
                                    'close' => [108],
                                    'volume' => [100000],
                                ]],
                                'adjclose' => [[
                                    'adjclose' => [108],
                                ]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-18', 'api', ['bbca']);

        $this->assertCount(1, $rows);
        $this->assertSame('BBCA', $rows[0]['ticker_code']);
        $this->assertSame('2026-03-18', $rows[0]['trade_date']);
        $this->assertSame('YAHOO_FINANCE', $rows[0]['source_name']);
        $this->assertSame('yahoo:BBCA:2026-03-18', $rows[0]['source_row_ref']);
        $this->assertSame('https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?period1=1773680400&period2=1773853200&interval=1d', $requestedUrls[0]);
    }


    public function test_yahoo_finance_adapter_fans_out_one_request_per_ticker_without_duplicate_requests()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;
            preg_match('#/chart/([^?]+)#', $url, $matches);
            $symbol = $matches[1] ?? 'UNKNOWN.JK';
            $ticker = str_replace('.JK', '', $symbol);

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [1773828000],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100],
                                    'high' => [110],
                                    'low' => [99],
                                    'close' => [108],
                                    'volume' => [100000],
                                ]],
                                'adjclose' => [[
                                    'adjclose' => [108],
                                ]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-18', 'api', ['bbca', 'bbri', 'tlkm']);

        $this->assertCount(3, $rows);
        $this->assertSame([
            'https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?period1=1773680400&period2=1773853200&interval=1d',
            'https://query1.finance.yahoo.com/v8/finance/chart/BBRI.JK?period1=1773680400&period2=1773853200&interval=1d',
            'https://query1.finance.yahoo.com/v8/finance/chart/TLKM.JK?period1=1773680400&period2=1773853200&interval=1d',
        ], $requestedUrls);
        $this->assertSame($requestedUrls, array_values(array_unique($requestedUrls)));
    }

    public function test_yahoo_finance_adapter_retries_per_ticker_so_request_count_scales_with_universe_size()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 1, 0));

        $callsBySymbol = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$callsBySymbol) {
            preg_match('#/chart/([^?]+)#', $url, $matches);
            $symbol = $matches[1] ?? 'UNKNOWN.JK';
            $callsBySymbol[$symbol] = ($callsBySymbol[$symbol] ?? 0) + 1;

            if ($callsBySymbol[$symbol] === 1) {
                return [
                    'status' => 429,
                    'body' => '{"error":"rate limit"}',
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [1773828000],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100],
                                    'high' => [110],
                                    'low' => [99],
                                    'close' => [108],
                                    'volume' => [100000],
                                ]],
                                'adjclose' => [[
                                    'adjclose' => [108],
                                ]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-18', 'api', ['bbca', 'bbri', 'tlkm']);

        $this->assertCount(3, $rows);
        $this->assertSame([
            'BBCA.JK' => 2,
            'BBRI.JK' => 2,
            'TLKM.JK' => 2,
        ], $callsBySymbol);
        $this->assertSame(6, array_sum($callsBySymbol));
    }



    public function test_yahoo_finance_adapter_deduplicates_single_day_ticker_inputs_and_tracks_aggregate_counts()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [1773828000],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100],
                                    'high' => [110],
                                    'low' => [99],
                                    'close' => [108],
                                    'volume' => [100000],
                                ]],
                                'adjclose' => [[
                                    'adjclose' => [108],
                                ]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-18', 'api', ['bbca', 'BBCA', 'bbri']);
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();

        $this->assertCount(2, $rows);
        $this->assertSame([
            'https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?period1=1773680400&period2=1773853200&interval=1d',
            'https://query1.finance.yahoo.com/v8/finance/chart/BBRI.JK?period1=1773680400&period2=1773853200&interval=1d',
        ], $requestedUrls);
        $this->assertSame(3, $telemetry['requested_ticker_count']);
        $this->assertSame(2, $telemetry['unique_ticker_count']);
        $this->assertSame(2, $telemetry['returned_row_count']);
        $this->assertSame(0, $telemetry['missing_ticker_count']);
        $this->assertSame('BBRI', $telemetry['ticker_code']);
    }

    public function test_yahoo_finance_range_adapter_groups_multi_date_rows_without_date_fanout()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;
            preg_match('#/chart/([^?]+)#', $url, $matches);
            $ticker = str_replace('.JK', '', $matches[1] ?? 'UNKNOWN.JK');

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [1777568400, 1777827600],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100, 101],
                                    'high' => [110, 111],
                                    'low' => [99, 100],
                                    'close' => [108, 109],
                                    'volume' => [100000, 100001],
                                ]],
                                'adjclose' => [[
                                    'adjclose' => [108, 109],
                                ]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rowsByDate = $adapter->fetchOrLoadEodBarsRange(
            '2026-05-01',
            '2026-05-31',
            'api',
            ['bbca', 'bbri'],
            ['2026-05-01', '2026-05-04'],
            ['source_acquisition_batch_id' => 'API_20260501_20260531_001']
        );
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();

        $this->assertCount(2, $requestedUrls);
        $this->assertSame([
            'https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?period1=1777568400&period2=1780246800&interval=1d',
            'https://query1.finance.yahoo.com/v8/finance/chart/BBRI.JK?period1=1777568400&period2=1780246800&interval=1d',
        ], $requestedUrls);
        $this->assertCount(2, $rowsByDate['2026-05-01']);
        $this->assertCount(2, $rowsByDate['2026-05-04']);
        $this->assertSame('BBCA', $rowsByDate['2026-05-01'][0]['ticker_code']);
        $this->assertSame('yahoo:BBCA:2026-05-01', $rowsByDate['2026-05-01'][0]['source_row_ref']);
        $this->assertSame('range_window', $telemetry['source_acquisition_mode']);
        $this->assertSame('API_20260501_20260531_001', $telemetry['source_acquisition_batch_id']);
        $this->assertSame('SUCCESS', $telemetry['source_acquisition_state']);
        $this->assertSame(2, $telemetry['expected_ticker_count']);
        $this->assertSame(2, $telemetry['success_ticker_count']);
        $this->assertSame(0, $telemetry['failed_ticker_count']);
        $this->assertSame(4, $telemetry['returned_row_count']);
    }

    public function test_yahoo_empty_chart_series_is_classified_as_no_valid_data()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $this->yahooEmptyChartSeriesBody('DUCK.JK'),
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-07-13', 'api', ['DUCK']);
            $this->fail('Expected empty Yahoo chart series to be blocked as no valid data.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();

            $this->assertSame('RUN_SOURCE_NO_VALID_DATA', $e->reasonCode());
            $this->assertSame('FAILED', $context['source_final_status']);
            $this->assertSame(1, $context['missing_ticker_count']);
            $this->assertSame(['DUCK'], $context['failed_ticker_codes']);
            $this->assertStringContainsString('"quote":[{}]', $context['response_body_sample']);
        }
    }

    public function test_yahoo_range_empty_chart_series_is_per_ticker_partial_failure()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'DUCK.JK') !== false) {
                return [
                    'status' => 200,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => $this->yahooEmptyChartSeriesBody('DUCK.JK'),
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $this->yahooOneBarBody(),
            ];
        });

        $rowsByDate = $adapter->fetchOrLoadEodBarsRange(
            '2026-05-01',
            '2026-05-07',
            'api',
            ['BBCA', 'DUCK'],
            ['2026-05-01'],
            ['source_acquisition_batch_id' => 'API_20260501_20260507_001']
        );
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();

        $this->assertCount(1, $rowsByDate['2026-05-01']);
        $this->assertSame('BBCA', $rowsByDate['2026-05-01'][0]['ticker_code']);
        $this->assertSame('PARTIAL_SUCCESS', $telemetry['source_acquisition_state']);
        $this->assertSame('RUN_SOURCE_NO_VALID_DATA', $telemetry['final_reason_code']);
        $this->assertSame(1, $telemetry['failed_ticker_count']);
        $this->assertSame(['DUCK'], $telemetry['failed_ticker_codes']);
        $this->assertSame(200, $telemetry['http_status']);
        $this->assertStringContainsString('"quote":[{}]', $telemetry['provider_error_sample']);
    }

    public function test_api_adapter_uses_the_registered_retry_budget_without_hidden_clamp()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
        ], 5, 0));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;

            return [
                'status' => 429,
                'body' => '{"error":"rate limit"}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
            $this->fail('Expected rate-limit exception after configured retry exhaustion.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();

            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $e->reasonCode());
            $this->assertSame(6, $calls);
            $this->assertSame(5, $context['retry_max']);
            $this->assertSame(6, $context['attempt_count']);
            $this->assertCount(6, $context['attempts']);
            $this->assertFalse($context['attempts'][5]['will_retry']);
        }
    }

    public function test_api_adapter_retries_rate_limit_then_succeeds()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
            'response_rows_path' => 'rows',
            'field_map' => [
                'ticker_code' => 'ticker_code',
                'trade_date' => 'trade_date',
                'open' => 'open',
                'high' => 'high',
                'low' => 'low',
                'close' => 'close',
                'volume' => 'volume',
                'adj_close' => 'adj_close',
                'source_row_ref' => 'source_row_ref',
                'captured_at' => 'captured_at',
            ],
        ], 1, 0));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;
            if ($calls === 1) {
                throw new SourceAcquisitionException('rate limited', 'RUN_SOURCE_RATE_LIMIT');
            }
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['rows' => [[
                    'ticker_code' => 'BBRI',
                    'trade_date' => '2026-03-20',
                    'open' => 1,
                    'high' => 2,
                    'low' => 1,
                    'close' => 2,
                    'volume' => 10,
                ]]]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-20', 'api');

        $this->assertCount(1, $rows);
        $this->assertSame(2, $calls);
    }



    public function test_api_adapter_retries_timeout_then_succeeds()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
            'response_rows_path' => 'rows',
        ], 1, 0));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;
            if ($calls === 1) {
                return [
                    'status' => 504,
                    'body' => '{"error":"gateway timeout"}',
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['rows' => [[
                    'ticker_code' => 'BBCA',
                    'trade_date' => '2026-03-20',
                    'open' => 1,
                    'high' => 2,
                    'low' => 1,
                    'close' => 2,
                    'volume' => 10,
                ]]]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-20', 'api');

        $this->assertCount(1, $rows);
        $this->assertSame(2, $calls);
    }

    public function test_api_adapter_exposes_success_after_retry_telemetry()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
            'response_rows_path' => 'rows',
        ], 1, 0));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;
            if ($calls === 1) {
                return [
                    'status' => 429,
                    'body' => '{"error":"rate limit"}',
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['rows' => [[
                    'ticker_code' => 'BBCA',
                    'trade_date' => '2026-03-20',
                    'open' => 1,
                    'high' => 2,
                    'low' => 1,
                    'close' => 2,
                    'volume' => 10,
                ]]]),
            ];
        });

        $rows = $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();

        $this->assertCount(1, $rows);
        $this->assertSame(2, $calls);
        $this->assertSame('generic', $telemetry['provider']);
        $this->assertSame('API_FREE', $telemetry['source_name']);
        $this->assertSame('PRIMARY', $telemetry['source_priority']);
        $this->assertSame('api_free', $telemetry['active_source_decision']);
        $this->assertSame(1, $telemetry['retry_attempt_count']);
        $this->assertSame(['TRANSIENT' => 1], $telemetry['failure_class_summary']);
        $this->assertSame(2, $telemetry['attempt_count']);
        $this->assertTrue($telemetry['success_after_retry']);
        $this->assertFalse($telemetry['retry_exhausted']);
        $this->assertNull($telemetry['final_reason_code']);
        $this->assertSame(200, $telemetry['final_http_status']);
        $this->assertCount(2, $telemetry['attempts']);
        $this->assertSame('RUN_SOURCE_RATE_LIMIT', $telemetry['attempts'][0]['reason_code']);
        $this->assertTrue($telemetry['attempts'][0]['will_retry']);
        $this->assertNull($telemetry['attempts'][1]['reason_code']);
        $this->assertSame(200, $telemetry['attempts'][1]['http_status']);
        $this->assertFalse($telemetry['attempts'][1]['will_retry']);
        $this->assertSame([], $adapter->consumeLastAcquisitionTelemetry());
    }


    public function test_api_adapter_raises_timeout_after_retry_exhaustion_with_attempt_log_context()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
        ], 2, 5));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;

            return [
                'status' => 500,
                'body' => '{"error":"upstream unavailable"}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
            $this->fail('Expected timeout classification after retry exhaustion.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();

            $this->assertSame('RUN_SOURCE_TIMEOUT', $e->reasonCode());
            $this->assertSame(3, $calls);
            $this->assertSame('generic', $context['provider']);
            $this->assertSame('API_FREE', $context['source_name']);
            $this->assertSame(3, $context['timeout_seconds']);
            $this->assertSame(2, $context['retry_max']);
            $this->assertSame(3, $context['attempt_count']);
            $this->assertTrue($context['retry_exhausted']);
            $this->assertCount(3, $context['attempts']);
            $this->assertSame(1, $context['attempts'][0]['attempt_number']);
            $this->assertSame('RUN_SOURCE_TIMEOUT', $context['attempts'][0]['reason_code']);
            $this->assertTrue($context['attempts'][0]['will_retry']);
            $this->assertGreaterThan(0, $context['attempts'][0]['backoff_delay_ms']);
            $this->assertSame(3, $context['attempts'][2]['attempt_number']);
            $this->assertFalse($context['attempts'][2]['will_retry']);
            $this->assertSame(0, $context['attempts'][2]['backoff_delay_ms']);
        }
    }

    public function test_api_adapter_raises_timeout_after_retry_exhaustion()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
        ], 2, 0));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;

            return [
                'status' => 500,
                'body' => '{"error":"upstream unavailable"}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
            $this->fail('Expected timeout classification after retry exhaustion.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_TIMEOUT', $e->reasonCode());
            $this->assertSame(3, $calls);
        }
    }

    public function test_api_adapter_raises_auth_error_without_retry()
    {
        $this->bindMarketDataConfig($this->config(['endpoint_template' => 'https://example.test/eod/{date}'], 3, 0));

        $calls = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$calls) {
            $calls++;
            return [
                'status' => 401,
                'body' => '{"error":"unauthorized"}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
            $this->fail('Expected auth error exception.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_AUTH_ERROR', $e->reasonCode());
            $this->assertSame(1, $calls);
        }
    }


    public function test_api_adapter_blocks_empty_success_response_as_no_valid_data()
    {
        $this->bindMarketDataConfig($this->config([
            'endpoint_template' => 'https://example.test/eod/{date}',
            'response_rows_path' => 'data.items',
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => ['items' => []]]),
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
            $this->fail('Expected empty API response to be blocked as no valid data.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();

            $this->assertSame('RUN_SOURCE_NO_VALID_DATA', $e->reasonCode());
            $this->assertSame('FAILED', $context['source_final_status']);
            $this->assertSame(0, $context['returned_row_count']);
            $this->assertSame(0, $context['accepted_row_count']);
            $this->assertTrue($context['empty_response_blocked']);
        }
    }

    /**
     * A parseable body does not excuse missing or incompatible transport metadata. This is the
     * fail-closed guard for HTML/error bodies that happen to contain JSON-shaped text.
     */
    public function test_success_status_with_missing_or_incompatible_content_type_is_rejected_before_parse()
    {
        foreach ([[], ['Content-Type' => 'text/html; charset=utf-8']] as $headers) {
            $this->bindMarketDataConfig($this->config([
                'endpoint_template' => 'https://example.test/eod/{date}',
                'response_rows_path' => 'data.items',
            ], 0, 0));

            $adapter = new PublicApiEodBarsAdapter(function () use ($headers) {
                return [
                    'status' => 200,
                    'headers' => $headers,
                    'body' => json_encode(['data' => ['items' => [['ticker_code' => 'BBCA']]]]),
                ];
            });

            try {
                $adapter->fetchOrLoadEodBars('2026-03-20', 'api');
                $this->fail('Missing/HTML content type must not reach the configured parser.');
            } catch (SourceAcquisitionException $e) {
                $this->assertSame('RUN_SOURCE_RESPONSE_CHANGED', $e->reasonCode());
            }
        }
    }



    public function test_http_400_is_classified_as_source_bad_request_with_diagnostic_context()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}&token=SECRET',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 400,
                'body' => '{"chart":{"error":{"code":"Bad Request","description":"Invalid request"}}}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBarsRange(
                '2026-05-01',
                '2026-05-07',
                'api',
                ['BBCA'],
                ['2026-05-01'],
                ['source_acquisition_batch_id' => 'API_20260501_20260507_001']
            );
            $this->fail('Expected HTTP 400 to be classified as source bad request.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();

            $this->assertSame('RUN_SOURCE_BAD_REQUEST', $e->reasonCode());
            $this->assertSame('SYSTEMIC_FAILED', $context['source_acquisition_state']);
            $this->assertSame(400, $context['final_http_status']);
            $this->assertSame(400, $context['http_status']);
            $this->assertSame('ticker', $context['failure_scope']);
            $this->assertStringContainsString('Bad Request', $context['provider_error_sample']);
            $this->assertStringContainsString('token=[redacted]', $context['sanitized_url']);
            $this->assertStringNotContainsString('SECRET', $context['sanitized_url']);
        }
    }

    public function test_range_http_400_for_one_ticker_is_partial_failure_not_command_blocking_when_other_ticker_succeeds()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'BAD.JK') !== false) {
                return [
                    'status' => 400,
                    'body' => '{"chart":{"error":{"code":"Bad Request"}}}',
                ];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                            'timestamp' => [1777568400],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100],
                                    'high' => [110],
                                    'low' => [99],
                                    'close' => [108],
                                    'volume' => [100000],
                                ]],
                                'adjclose' => [['adjclose' => [108]]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rowsByDate = $adapter->fetchOrLoadEodBarsRange(
            '2026-05-01',
            '2026-05-07',
            'api',
            ['BBCA', 'BAD'],
            ['2026-05-01'],
            ['source_acquisition_batch_id' => 'API_20260501_20260507_001']
        );
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();

        $this->assertCount(1, $rowsByDate['2026-05-01']);
        $this->assertSame('PARTIAL_SUCCESS', $telemetry['source_acquisition_state']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $telemetry['final_reason_code']);
        $this->assertSame(1, $telemetry['failed_ticker_count']);
        $this->assertContains('BAD', $telemetry['failed_ticker_codes']);
        $this->assertSame(400, $telemetry['final_http_status']);
    }

    public function test_range_response_persists_partial_invalid_row_evidence_instead_of_silently_skipping_it()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $recorder = new InMemorySourceObservationRecorder();
        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => ['result' => [[
                        'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                        'timestamp' => [
                            strtotime('2026-05-01 09:00:00 Asia/Jakarta'),
                            strtotime('2026-05-04 09:00:00 Asia/Jakarta'),
                        ],
                        'indicators' => [
                            'quote' => [[
                                'open' => [100, 200], 'high' => [110, 210],
                                'low' => [90, 190], 'close' => [105, null],
                                'volume' => [1000, 2000],
                            ]],
                            'adjclose' => [['adjclose' => [105, null]]],
                        ],
                    ]]],
                ]),
            ];
        }, null, null, $recorder);

        $rows = $adapter->fetchOrLoadEodBarsRange(
            '2026-05-01',
            '2026-05-04',
            'api',
            ['BBCA'],
            ['2026-05-01', '2026-05-04']
        );

        $this->assertCount(1, $rows['2026-05-01']);
        $this->assertCount(0, $rows['2026-05-04']);
        $accepted = collect($recorder->rows())->firstWhere('outcome_state', 'ACCEPTED');
        $this->assertCount(1, $accepted['normalized_rejected_rows']);
        $this->assertSame('BAR_MISSING_REQUIRED_FIELD', $accepted['normalized_rejected_rows'][0]['invalid_reason_code']);
    }

    public function test_misaligned_quote_arrays_fail_as_schema_change_before_row_acceptance()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => ['result' => [[
                        'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                        'timestamp' => [strtotime('2026-05-01 09:00:00 Asia/Jakarta')],
                        'indicators' => ['quote' => [[
                            'open' => [100], 'high' => [110], 'low' => [90],
                            'close' => [], 'volume' => [1000],
                        ]]],
                    ]]],
                ]),
            ];
        });

        try {
            $adapter->fetchOrLoadEodBarsRange('2026-05-01', '2026-05-01', 'api', ['BBCA'], ['2026-05-01']);
            $this->fail('Misaligned quote arrays must fail closed.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_RESPONSE_CHANGED', $e->reasonCode());
        }
    }

    public function test_response_provider_symbol_mismatch_fails_closed_against_requested_mapping()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $adapter = new PublicApiEodBarsAdapter(function () {
            $payload = json_decode($this->yahooOneBarBody(), true);
            $payload['chart']['result'][0]['meta']['symbol'] = 'BBRI.JK';

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-05-01', 'api', ['BBCA']);
            $this->fail('Response symbol mismatch must not be normalized as the requested listing.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_RESPONSE_CHANGED', $e->reasonCode());
        }
    }

    public function test_active_yahoo_schema_version_must_match_the_released_adapter_contract()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'adapter_version' => 'yahoo_chart_v2',
            'schema_version' => 'yahoo_chart_schema_unreviewed',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $requests = 0;
        $adapter = new PublicApiEodBarsAdapter(function () use (&$requests) {
            $requests++;
            return ['status' => 200, 'headers' => ['Content-Type' => 'application/json'], 'body' => $this->yahooOneBarBody()];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-05-01', 'api', ['BBCA']);
            $this->fail('Unreviewed schema drift must fail before provider transport or normalization.');
        } catch (SourceAcquisitionException $e) {
            $this->assertSame('RUN_SOURCE_RESPONSE_CHANGED', $e->reasonCode());
            $this->assertSame('yahoo_chart_v2', $e->context()['active_adapter_version']);
            $this->assertSame('yahoo_chart_schema_unreviewed', $e->context()['active_schema_version']);
            $this->assertSame(0, $requests);
        }
    }

    public function test_single_date_circuit_breaker_stops_fanout_and_preserves_registered_root_failure_reason()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 429,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"error":"rate limit"}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBars('2026-05-01', 'api', ['A001', 'A002', 'A003', 'A004', 'A005', 'A006', 'A007', 'A008', 'A009', 'A010']);
            $this->fail('Circuit breaker must stop a wholesale failing fan-out.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $e->reasonCode());
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $context['final_reason_code']);
            $this->assertTrue($context['circuit_breaker_open']);
            $this->assertSame('CIRCUIT_OPEN', $context['source_protection_state']);
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $context['circuit_breaker_trigger_reason_code']);
            $this->assertSame(6, $context['attempted_ticker_count']);
            $this->assertSame(4, $context['unattempted_ticker_count']);
            $this->assertSame(6, count($requestedUrls));
        }
    }

    public function test_range_circuit_breaker_protects_range_window_fanout()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 429,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"error":"rate limit"}',
            ];
        });

        try {
            $adapter->fetchOrLoadEodBarsRange(
                '2026-05-01',
                '2026-05-07',
                'api',
                ['A001', 'A002', 'A003', 'A004', 'A005', 'A006', 'A007', 'A008', 'A009', 'A010'],
                ['2026-05-01'],
                ['source_acquisition_batch_id' => 'B08_RANGE_BREAKER']
            );
            $this->fail('Range-window fan-out must stop once the breaker opens.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $e->reasonCode());
            $this->assertSame('SYSTEMIC_FAILED', $context['source_acquisition_state']);
            $this->assertTrue($context['circuit_breaker_open']);
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $context['circuit_breaker_trigger_reason_code']);
            $this->assertSame(6, $context['attempted_ticker_count']);
            $this->assertSame(4, $context['unattempted_ticker_count']);
            $this->assertSame(6, count($requestedUrls));
        }
    }

    public function test_benchmark_fanout_uses_the_same_circuit_breaker_and_root_reason_taxonomy()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => ['symbol_suffix' => '.JK', 'range' => '10d', 'interval' => '1d'],
        ], 0, 0));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 429,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"error":"rate limit"}',
            ];
        });

        $benchmarks = [];
        for ($i = 1; $i <= 10; $i++) {
            $benchmarks[] = [
                'benchmark_code' => 'B'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'provider_symbol' => '^B'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'instrument_type' => 'index',
            ];
        }

        try {
            $adapter->fetchOrLoadBenchmarkBars('2026-05-01', 'api', $benchmarks);
            $this->fail('Benchmark fan-out must stop once the breaker opens.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $e->reasonCode());
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $context['final_reason_code']);
            $this->assertTrue($context['circuit_breaker_open']);
            $this->assertSame('RUN_SOURCE_RATE_LIMIT', $context['circuit_breaker_trigger_reason_code']);
            $this->assertSame(6, $context['attempted_ticker_count']);
            $this->assertSame(4, $context['unattempted_ticker_count']);
            $this->assertSame(6, count($requestedUrls));
        }
    }

    private function yahooEmptyChartSeriesBody($symbol)
    {
        return json_encode([
            'chart' => [
                'result' => [[
                    'meta' => [
                        'symbol' => $symbol,
                        'instrumentType' => 'MUTUALFUND',
                        'exchangeTimezoneName' => 'Asia/Jakarta',
                        'validRanges' => ['1mo', '3mo', '6mo', 'ytd', '1y', '2y', '5y', '10y', 'max'],
                    ],
                    'indicators' => [
                        'quote' => [(object) []],
                        'adjclose' => [(object) []],
                    ],
                ]],
                'error' => null,
            ],
        ]);
    }

    private function yahooOneBarBody()
    {
        return json_encode([
            'chart' => [
                'result' => [[
                    'meta' => [
                        'exchangeTimezoneName' => 'Asia/Jakarta',
                    ],
                    'timestamp' => [1777568400],
                    'indicators' => [
                        'quote' => [[
                            'open' => [100],
                            'high' => [110],
                            'low' => [99],
                            'close' => [108],
                            'volume' => [100000],
                        ]],
                        'adjclose' => [[
                            'adjclose' => [108],
                        ]],
                    ],
                ]],
                'error' => null,
            ],
        ]);
    }

    /**
     * MD-S067-R0010: "All reason codes are retained. A primary reason is routing compatibility only."
     *
     * Before MD-B08-A002 nothing exercised this. The only test naming `failure_reason_summary`
     * asserted the string appears in the adapter source, which stays green while the behaviour is
     * gone: collapsing the retained map to its most frequent entry passed the whole suite.
     */
    public function test_every_distinct_failure_reason_is_retained_and_the_primary_reason_does_not_replace_them()
    {
        $this->bindMarketDataConfig($this->config([
            'provider' => 'yahoo_finance',
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
            'source_name' => 'YAHOO_FINANCE',
            'yahoo' => [
                'symbol_suffix' => '.JK',
                'range' => '10d',
                'interval' => '1d',
            ],
        ], 0, 0));

        // Two tickers fail for genuinely different reasons; one succeeds so the run is partial
        // rather than a single-cause total failure.
        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'BBCA.JK') !== false) {
                return ['status' => 429, 'headers' => ['Content-Type' => 'application/json'], 'body' => '{"error":"rate limited"}'];
            }
            if (strpos($url, 'BBRI.JK') !== false) {
                return ['status' => 500, 'headers' => ['Content-Type' => 'application/json'], 'body' => '{"error":"upstream"}'];
            }

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                            'timestamp' => [1773828000],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100], 'high' => [110], 'low' => [99],
                                    'close' => [108], 'volume' => [100000],
                                ]],
                                'adjclose' => [['adjclose' => [108]]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $adapter->fetchOrLoadEodBars('2026-03-18', 'api', ['tlkm', 'bbca', 'bbri']);
        $telemetry = $adapter->consumeLastAcquisitionTelemetry();
        $summary = $telemetry['failure_reason_summary'] ?? [];

        // Retained: both distinct causes survive, each with its own count.
        $this->assertArrayHasKey('RUN_SOURCE_RATE_LIMIT', $summary, 'the rate-limit cause was dropped from the retained set');
        $this->assertArrayHasKey('RUN_SOURCE_TIMEOUT', $summary, 'the transient-server cause was dropped from the retained set');
        $this->assertCount(2, $summary, 'distinct reason codes must not be collapsed into one');
        $this->assertSame(1, $summary['RUN_SOURCE_RATE_LIMIT']);
        $this->assertSame(1, $summary['RUN_SOURCE_TIMEOUT']);

        // Routing compatibility only. The published primary reason here is the routing label
        // RUN_SOURCE_PARTIAL_RESPONSE, which is not itself one of the two causes -- exactly why the
        // contract forbids reading it as the reason. It must never become the sole record.
        $this->assertArrayHasKey('final_reason_code', $telemetry);
        $this->assertNotSame('', (string) $telemetry['final_reason_code']);
        $this->assertNotSame(
            [(string) $telemetry['final_reason_code']],
            array_keys($summary),
            'the retained set must not be reduced to the single routing reason'
        );
        $this->assertGreaterThan(1, count($summary), 'the primary reason must not replace the retained set');
    }

    private function config(array $apiSource = [], $retryMax = 3, $backoffMs = 0)
    {
        return [
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'provider' => [
                    'api_retry_max' => $retryMax,
                    'api_backoff_ms' => $backoffMs,
                    'api_throttle_qps' => 1000,
                ],
                'source' => [
                    'api' => array_replace([
                        'provider' => 'generic',
                        'endpoint_template' => '',
                        'response_format' => 'json',
                        'response_rows_path' => '',
                        'timeout_seconds' => 3,
                        'auth_header_name' => '',
                        'auth_token' => '',
                        'source_name' => 'API_FREE',
                        'yahoo' => [
                            'symbol_suffix' => '.JK',
                            'range' => '10d',
                            'interval' => '1d',
                        ],
                        'field_map' => [
                            'ticker_code' => 'ticker_code',
                            'trade_date' => 'trade_date',
                            'open' => 'open',
                            'high' => 'high',
                            'low' => 'low',
                            'close' => 'close',
                            'volume' => 'volume',
                            'adj_close' => 'adj_close',
                            'source_row_ref' => 'source_row_ref',
                            'captured_at' => 'captured_at',
                        ],
                    ], $apiSource),
                ],
            ],
        ];
    }

    public function test_provider_row_with_zero_volume_and_price_movement_is_rejected_with_canonical_reason()
    {
        $adapter = new PublicApiEodBarsAdapter(function () { return []; });
        $method = new ReflectionMethod(PublicApiEodBarsAdapter::class, 'validateYahooOhlcvRow');
        $method->setAccessible(true);

        $result = $method->invoke($adapter, [
            'open' => 100, 'high' => 110, 'low' => 99, 'close' => 108, 'volume' => 0,
        ]);

        $this->assertFalse($result['valid']);
        $this->assertSame('BAR_ZERO_VOLUME_PRICE_MOVEMENT', $result['reason_code']);
    }

    public function test_provider_row_with_flat_positive_ohlc_and_zero_volume_remains_valid()
    {
        $adapter = new PublicApiEodBarsAdapter(function () { return []; });
        $method = new ReflectionMethod(PublicApiEodBarsAdapter::class, 'validateYahooOhlcvRow');
        $method->setAccessible(true);

        $result = $method->invoke($adapter, [
            'open' => 100, 'high' => 100, 'low' => 100, 'close' => 100, 'volume' => 0,
        ]);

        $this->assertTrue($result['valid']);
        $this->assertNull($result['reason_code']);
    }

}
