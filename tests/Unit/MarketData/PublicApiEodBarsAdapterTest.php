<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
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
            'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?interval={interval}&range={range}',
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
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => [
                                'exchangeTimezoneName' => 'Asia/Jakarta',
                            ],
                            'timestamp' => [1773939600],
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
        $this->assertSame('https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d', $requestedUrls[0]);
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
}
