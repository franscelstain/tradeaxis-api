<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Infrastructure\MarketData\Source\BenchmarkProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\EquityProviderSymbolResolver;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use PHPUnit\Framework\TestCase;

class BenchmarkProviderSymbolResolverTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    public function test_equity_resolver_appends_jk_suffix_only_for_equity_symbols()
    {
        $resolver = new EquityProviderSymbolResolver();

        $this->assertSame('BBCA.JK', $resolver->resolve('bbca', ['yahoo' => ['symbol_suffix' => '.JK']]));
        $this->assertSame('BBCA.JK', $resolver->resolve('BBCA.JK', ['yahoo' => ['symbol_suffix' => '.JK']]));
    }

    public function test_benchmark_resolver_keeps_ihsg_provider_symbol_as_jkse_without_jk_suffix()
    {
        $resolver = new BenchmarkProviderSymbolResolver();

        $this->assertSame('^JKSE', $resolver->resolve('IHSG', '^JKSE', 'INDEX'));
        $this->assertNotSame('^JKSE.JK', $resolver->resolve('IHSG', '^JKSE', 'INDEX'));
    }

    public function test_public_api_adapter_fetches_benchmark_symbol_without_suffix()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'provider' => [
                    'api_retry_max' => 0,
                    'api_backoff_ms' => 0,
                    'api_throttle_qps' => 1000,
                ],
                'source' => [
                    'default_source_name' => 'YAHOO_FINANCE',
                    'api' => [
                        'provider' => 'yahoo_finance',
                        'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?interval={interval}&range={range}',
                        'response_format' => 'json',
                        'response_rows_path' => '',
                        'timeout_seconds' => 3,
                        'auth_header_name' => '',
                        'auth_token' => '',
                        'source_name' => 'YAHOO_FINANCE',
                        'yahoo' => [
                            'symbol_suffix' => '.JK',
                            'range' => '10d',
                            'interval' => '1d',
                        ],
                    ],
                ],
            ],
        ]);

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 200,
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                            'timestamp' => [1773828000],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [7000],
                                    'high' => [7100],
                                    'low' => [6950],
                                    'close' => [7050],
                                    'volume' => [0],
                                ]],
                                'adjclose' => [['adjclose' => [7050]]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $rows = $adapter->fetchOrLoadBenchmarkBars('2026-03-18', 'api', [[
            'benchmark_code' => 'IHSG',
            'provider' => 'yahoo_finance',
            'provider_symbol' => '^JKSE',
            'instrument_type' => 'INDEX',
        ]]);

        $this->assertCount(1, $rows);
        $this->assertSame('IHSG', $rows[0]['benchmark_code']);
        $this->assertSame('^JKSE', $rows[0]['provider_symbol']);
        $this->assertSame('https://query1.finance.yahoo.com/v8/finance/chart/^JKSE?interval=1d&range=10d', $requestedUrls[0]);
        $this->assertStringNotContainsString('^JKSE.JK', $requestedUrls[0]);
    }
}
