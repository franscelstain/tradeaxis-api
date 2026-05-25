<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use PHPUnit\Framework\TestCase;

class ApiBackfillRangeAcquisitionServiceTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    public function test_plan_keeps_one_week_inside_one_configured_window()
    {
        $this->bindMarketDataConfig($this->config(90));

        $service = new ApiBackfillRangeAcquisitionService(new PublicApiEodBarsAdapter(function () {
            $this->fail('Plan must not perform HTTP acquisition.');
        }));

        $plan = $service->plan('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01', '2026-05-04'], ['BBCA', 'BBRI']);

        $this->assertSame('range_window', $plan['source_acquisition_mode']);
        $this->assertSame(1, $plan['window_count']);
        $this->assertSame(2, $plan['ticker_count']);
        $this->assertSame(2, $plan['estimated_http_requests']);
    }

    public function test_plan_splits_range_when_it_exceeds_configured_window_days()
    {
        $this->bindMarketDataConfig($this->config(3));

        $service = new ApiBackfillRangeAcquisitionService(new PublicApiEodBarsAdapter(function () {
            $this->fail('Plan must not perform HTTP acquisition.');
        }));

        $plan = $service->plan('2026-05-01', '2026-05-01', '2026-05-10', ['2026-05-01', '2026-05-04', '2026-05-07'], ['BBCA', 'BBRI']);

        $this->assertSame([
            ['start' => '2026-05-01', 'end' => '2026-05-03'],
            ['start' => '2026-05-04', 'end' => '2026-05-06'],
            ['start' => '2026-05-07', 'end' => '2026-05-09'],
            ['start' => '2026-05-10', 'end' => '2026-05-10'],
        ], $plan['windows']);
        $this->assertSame(4, $plan['window_count']);
        $this->assertSame(8, $plan['estimated_http_requests']);
    }

    public function test_acquire_uses_window_by_ticker_requests_instead_of_date_by_ticker_requests()
    {
        $this->bindMarketDataConfig($this->config(90));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 200,
                'body' => json_encode([
                    'chart' => [
                        'result' => [[
                            'meta' => ['exchangeTimezoneName' => 'Asia/Jakarta'],
                            'timestamp' => [1777568400, 1777827600],
                            'indicators' => [
                                'quote' => [[
                                    'open' => [100, 101],
                                    'high' => [110, 111],
                                    'low' => [99, 100],
                                    'close' => [108, 109],
                                    'volume' => [100000, 100001],
                                ]],
                                'adjclose' => [['adjclose' => [108, 109]]],
                            ],
                        ]],
                    ],
                ]),
            ];
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01', '2026-05-04'], ['BBCA', 'BBRI']);

        $this->assertSame(2, $result['estimated_http_requests']);
        $this->assertCount(2, $requestedUrls);
        $this->assertCount(2, $result['rows_by_trade_date']['2026-05-01']);
        $this->assertCount(2, $result['rows_by_trade_date']['2026-05-04']);
        $this->assertSame('SUCCESS', $result['date_telemetry']['2026-05-01']['source_acquisition_state']);
        $this->assertSame('range_window', $result['date_telemetry']['2026-05-01']['source_acquisition_mode']);
    }



    public function test_acquire_writes_window_ticker_checkpoints_for_resume()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'BAD.JK') !== false) {
                return [
                    'status' => 400,
                    'body' => '{"chart":{"error":{"code":"Bad Request"}}}',
                ];
            }

            return [
                'status' => 200,
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

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BAD']);

        $checkpoint = $result['source_acquisition_checkpoints'];
        $this->assertSame('SUCCESS', $checkpoint['2026-05-01|2026-05-07|BBCA']['state']);
        $this->assertSame('FAILED', $checkpoint['2026-05-01|2026-05-07|BAD']['state']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $checkpoint['2026-05-01|2026-05-07|BAD']['reason_code']);
        $this->assertSame(400, $checkpoint['2026-05-01|2026-05-07|BAD']['http_status']);
    }

    public function test_timeout_checkpoint_does_not_reuse_successful_ticker_http_status_or_error_sample()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'BAD.JK') !== false) {
                throw new RuntimeException('Timeout while fetching BAD.JK token=SECRET');
            }

            return $this->oneBarResponse('ZYRX_SUCCESS_SAMPLE');
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BAD', 'ZYRX']);
        $checkpoint = $result['source_acquisition_checkpoints']['2026-05-01|2026-05-07|BAD'];

        $this->assertSame('FAILED', $checkpoint['state']);
        $this->assertSame('RUN_SOURCE_TIMEOUT', $checkpoint['reason_code']);
        $this->assertNull($checkpoint['http_status']);
        $this->assertStringContainsString('Timeout while fetching BAD.JK', $checkpoint['error_sample']);
        $this->assertStringContainsString('token=[redacted]', $checkpoint['error_sample']);
        $this->assertStringNotContainsString('ZYRX_SUCCESS_SAMPLE', (string) $checkpoint['error_sample']);
        $this->assertNull($checkpoint['provider_error_sample']);
    }

    public function test_success_checkpoint_does_not_keep_stale_failure_sample_from_previous_ticker()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'BAD.JK') !== false) {
                return [
                    'status' => 400,
                    'body' => '{"chart":{"error":{"code":"Bad Request","description":"BAD_ONLY"}}}',
                ];
            }

            return $this->oneBarResponse();
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BAD', 'BBCA']);
        $checkpoint = $result['source_acquisition_checkpoints'];

        $this->assertSame('FAILED', $checkpoint['2026-05-01|2026-05-07|BAD']['state']);
        $this->assertStringContainsString('BAD_ONLY', $checkpoint['2026-05-01|2026-05-07|BAD']['error_sample']);
        $this->assertSame('SUCCESS', $checkpoint['2026-05-01|2026-05-07|BBCA']['state']);
        $this->assertNull($checkpoint['2026-05-01|2026-05-07|BBCA']['error_sample']);
        $this->assertNull($checkpoint['2026-05-01|2026-05-07|BBCA']['failure_scope']);
    }

    public function test_http_400_checkpoint_keeps_ticker_window_url_and_provider_error_for_same_ticker()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'WBSA.JK') !== false) {
                return [
                    'status' => 400,
                    'body' => '{"chart":{"error":{"code":"Bad Request","description":"WBSA_ONLY"}}}',
                ];
            }

            return $this->oneBarResponse();
        });

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'WBSA']);
        $checkpoint = $result['source_acquisition_checkpoints']['2026-05-01|2026-05-07|WBSA'];

        $this->assertSame('WBSA', $checkpoint['ticker_code']);
        $this->assertSame('2026-05-01', $checkpoint['window_start']);
        $this->assertSame('2026-05-07', $checkpoint['window_end']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $checkpoint['reason_code']);
        $this->assertSame(400, $checkpoint['http_status']);
        $this->assertSame('ticker', $checkpoint['failure_scope']);
        $this->assertStringContainsString('WBSA_ONLY', $checkpoint['provider_error_sample']);
        $this->assertStringContainsString('WBSA.JK', $checkpoint['sanitized_url']);
    }

    public function test_resume_only_failed_requests_failed_tickers_only()
    {
        $this->bindMarketDataConfig($this->config(90));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return [
                'status' => 200,
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

        $checkpoint = [
            '2026-05-01|2026-05-07|BBCA' => ['state' => 'SUCCESS'],
            '2026-05-01|2026-05-07|BBRI' => ['state' => 'FAILED'],
        ];

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BBRI'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => $checkpoint,
        ]);

        $this->assertCount(1, $requestedUrls);
        $this->assertStringContainsString('BBRI.JK', $requestedUrls[0]);
        $this->assertSame(1, $result['skipped_checkpoint_count']);
        $this->assertArrayNotHasKey('2026-05-01|2026-05-07|BBCA', $result['source_acquisition_checkpoints']);
        $this->assertSame('SUCCESS', $result['source_acquisition_checkpoints']['2026-05-01|2026-05-07|BBRI']['state']);
    }

    public function test_resume_only_failed_accounts_for_all_eligible_failed_checkpoints()
    {
        $this->bindMarketDataConfig($this->config(90));

        $requestedUrls = [];
        $adapter = new PublicApiEodBarsAdapter(function ($url) use (&$requestedUrls) {
            $requestedUrls[] = $url;

            return $this->oneBarResponse();
        });

        $checkpoint = [
            '2026-05-01|2026-05-07|BBCA' => ['state' => 'SUCCESS'],
            '2026-05-01|2026-05-07|BBRI' => ['state' => 'FAILED'],
            '2026-05-01|2026-05-07|TLKM' => ['state' => 'FAILED'],
        ];

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBCA', 'BBRI', 'TLKM'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => $checkpoint,
        ]);

        $this->assertCount(2, $requestedUrls);
        $this->assertSame(2, $result['failed_checkpoint_total']);
        $this->assertSame(2, $result['failed_checkpoint_eligible']);
        $this->assertSame(2, $result['failed_checkpoint_retried']);
        $this->assertSame(2, $result['retry_success_count']);
        $this->assertSame(0, $result['retry_failed_count']);
        $this->assertSame('RETRY_SUCCESS', $result['source_acquisition_state']);
    }

    public function test_resume_only_failed_partial_retry_success_state_is_not_systemic()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            if (strpos($url, 'TLKM.JK') !== false) {
                return [
                    'status' => 400,
                    'body' => '{"chart":{"error":{"code":"Bad Request","description":"TLKM_ONLY"}}}',
                ];
            }

            return $this->oneBarResponse();
        });

        $checkpoint = [
            '2026-05-01|2026-05-07|BBRI' => ['state' => 'FAILED'],
            '2026-05-01|2026-05-07|TLKM' => ['state' => 'FAILED'],
        ];

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBRI', 'TLKM'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => $checkpoint,
        ]);

        $this->assertSame('PARTIAL_RETRY_SUCCESS', $result['source_acquisition_state']);
        $this->assertSame(1, $result['retry_success_count']);
        $this->assertSame(1, $result['retry_failed_count']);
        $this->assertSame('FAILED', $result['source_acquisition_checkpoints']['2026-05-01|2026-05-07|TLKM']['state']);
    }

    public function test_resume_only_failed_single_ticker_retry_failure_is_failed_retry_blocked()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function () {
            return [
                'status' => 400,
                'body' => '{"chart":{"error":{"code":"Bad Request","description":"WBSA_ONLY"}}}',
            ];
        });

        $checkpoint = [
            '2026-05-01|2026-05-07|WBSA' => ['state' => 'FAILED'],
        ];

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['WBSA'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => $checkpoint,
        ]);

        $this->assertSame('FAILED_RETRY_BLOCKED', $result['source_acquisition_state']);
        $this->assertSame(1, $result['failed_checkpoint_total']);
        $this->assertSame(1, $result['failed_checkpoint_retried']);
        $this->assertSame(0, $result['retry_success_count']);
        $this->assertSame(1, $result['retry_failed_count']);
        $this->assertSame('RUN_SOURCE_BAD_REQUEST', $result['source_acquisition_checkpoints']['2026-05-01|2026-05-07|WBSA']['reason_code']);
    }

    public function test_resume_only_failed_reports_skipped_failed_checkpoint_reason()
    {
        $this->bindMarketDataConfig($this->config(90));

        $adapter = new PublicApiEodBarsAdapter(function ($url) {
            $this->assertStringContainsString('BBRI.JK', $url);

            return $this->oneBarResponse();
        });

        $checkpoint = [
            '2026-04-01|2026-04-30|BBCA' => ['state' => 'FAILED'],
            '2026-05-01|2026-05-07|BBRI' => ['state' => 'FAILED'],
        ];

        $service = new ApiBackfillRangeAcquisitionService($adapter);
        $result = $service->acquire('2026-05-01', '2026-05-01', '2026-05-07', ['2026-05-01'], ['BBRI'], [
            'resume' => true,
            'only_failed' => true,
            'source_acquisition_checkpoint' => $checkpoint,
        ]);

        $this->assertSame(2, $result['failed_checkpoint_total']);
        $this->assertSame(1, $result['failed_checkpoint_eligible']);
        $this->assertSame(1, $result['failed_checkpoint_retried']);
        $this->assertSame(1, $result['skipped_failed_checkpoint_count']);
        $this->assertSame(1, $result['skipped_failed_checkpoint_reasons']['WINDOW_OUT_OF_SCOPE']);
    }

    private function oneBarResponse($marker = null)
    {
        $payload = [
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
        ];

        if ($marker !== null) {
            $payload['marker'] = $marker;
        }

        return [
            'status' => 200,
            'body' => json_encode($payload),
        ];
    }

    private function config($windowDays)
    {
        return [
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'provider' => [
                    'api_retry_max' => 0,
                    'api_backoff_ms' => 0,
                    'api_throttle_qps' => 1000,
                ],
                'source' => [
                    'default_source_name' => 'YAHOO_FINANCE',
                    'api_backfill' => [
                        'window_days' => $windowDays,
                        'warmup_days' => 120,
                        'concurrency' => 5,
                        'max_dates_per_run' => 20,
                        'collect_all_errors' => false,
                        'default_error_policy' => 'stop_on_error',
                    ],
                    'api' => [
                        'provider' => 'yahoo_finance',
                        'endpoint_template' => 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}',
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
        ];
    }
}
