<?php

namespace App\Infrastructure\MarketData\Source;

use Carbon\Carbon;
use Illuminate\Support\Str;

class PublicApiEodBarsAdapter
{
    private $fetcher;
    private $lastAcquisitionTelemetry = [];

    public function __construct(callable $fetcher = null)
    {
        $this->fetcher = $fetcher;
    }

    public function fetchOrLoadEodBars($tradeDate, $sourceMode, array $tickerCodes = [])
    {
        $this->lastAcquisitionTelemetry = [];
        if ($sourceMode !== 'api') {
            throw new \RuntimeException('Source mode '.$sourceMode.' tidak didukung oleh PublicApiEodBarsAdapter.');
        }

        $apiConfig = config('market_data.source.api');
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        if ($urlTemplate === '') {
            throw new SourceAcquisitionException('Source API endpoint template belum dikonfigurasi.', 'RUN_SOURCE_AUTH_ERROR');
        }

        if ($this->providerName($apiConfig) === 'yahoo_finance') {
            return $this->fetchYahooFinanceBars($tradeDate, $tickerCodes, $apiConfig);
        }

        $url = str_replace(
            ['{date}', '{symbols}'],
            [$tradeDate, implode(',', $tickerCodes)],
            $urlTemplate
        );

        $response = $this->requestWithRetry($url, [
            'trade_date' => $tradeDate,
            'requested_ticker_count' => count($tickerCodes),
        ]);
        $requestTelemetry = $this->consumeLastAcquisitionTelemetry();

        try {
            $rows = $this->parsePayload($response['body'], $tradeDate, $response['captured_at']);
        } catch (SourceAcquisitionException $e) {
            $telemetry = $this->buildGenericFailureTelemetry($tradeDate, $tickerCodes, $apiConfig, $requestTelemetry, $e->reasonCode());
            $this->rememberAcquisitionTelemetry($telemetry);

            throw $e->withContext($telemetry);
        }

        if (count($rows) === 0) {
            $telemetry = $this->buildGenericEmptyResponseTelemetry($tradeDate, $tickerCodes, $apiConfig, $requestTelemetry);
            $this->rememberAcquisitionTelemetry($telemetry);

            throw new SourceAcquisitionException(
                'Source API response contained no valid EOD rows for requested trade_date.',
                'RUN_SOURCE_NO_VALID_DATA',
                0,
                null,
                $telemetry
            );
        }

        $normalizedRows = array_map(function ($row, $index) use ($tradeDate, $response, $apiConfig) {
            return $this->normalizeRow($row, $tradeDate, $index + 1, $response['captured_at'], $apiConfig);
        }, $rows, array_keys($rows));

        $this->rememberAcquisitionTelemetry($this->buildGenericSuccessTelemetry($tradeDate, $tickerCodes, $normalizedRows, $apiConfig, $requestTelemetry));

        return $normalizedRows;
    }


    private function fetchYahooFinanceBars($tradeDate, array $tickerCodes, array $apiConfig)
    {
        if (empty($tickerCodes)) {
            throw new SourceAcquisitionException('Yahoo Finance source membutuhkan ticker universe yang tidak kosong.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $rows = [];
        $index = 0;
        $requestTelemetry = [];
        $failureTelemetry = [];

        $uniqueTickerCodes = array_values(array_unique(array_filter(array_map(function ($tickerCode) {
            return $this->normalizeTickerCode($tickerCode);
        }, $tickerCodes))));

        foreach ($uniqueTickerCodes as $tickerCode) {
            $url = $this->buildYahooFinanceUrl($tradeDate, $tickerCode, $apiConfig);
            $requestContext = [
                'trade_date' => $tradeDate,
                'ticker_code' => $tickerCode,
                'requested_ticker_count' => count($tickerCodes),
                'unique_ticker_count' => count($uniqueTickerCodes),
            ];
            $lastTickerRequestTelemetry = [];

            try {
                $response = $this->requestWithRetry($url, $requestContext);
                $telemetry = $this->consumeLastAcquisitionTelemetry();
                $lastTickerRequestTelemetry = $telemetry;
                $requestTelemetry[] = $this->withTickerTelemetry($telemetry, $tickerCode);

                $row = $this->parseYahooFinancePayload($response['body'], $tradeDate, $tickerCode, $response['captured_at'], $apiConfig);
                if ($row === null) {
                    $failureTelemetry[] = $this->withTickerTelemetry(array_merge($lastTickerRequestTelemetry, [
                        'final_reason_code' => 'RUN_SOURCE_NO_VALID_DATA',
                        'source_final_status' => 'FAILED',
                        'trade_date_not_found_in_response' => true,
                    ]), $tickerCode);
                    continue;
                }

                $index++;
                $rows[] = $this->normalizeRow($row, $tradeDate, $index, $response['captured_at'], $apiConfig);
            } catch (SourceAcquisitionException $e) {
                $exceptionTelemetry = $e->context();
                $telemetry = $this->withTickerTelemetry($exceptionTelemetry ?: $lastTickerRequestTelemetry, $tickerCode);
                if (empty($telemetry)) {
                    $telemetry = $this->withTickerTelemetry([
                        'trade_date' => $tradeDate,
                        'ticker_code' => $tickerCode,
                        'provider' => $this->providerName($apiConfig),
                        'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
                        'final_reason_code' => $e->reasonCode(),
                        'source_final_status' => 'FAILED',
                    ], $tickerCode);
                }

                if (! empty($exceptionTelemetry) || empty($lastTickerRequestTelemetry)) {
                    $requestTelemetry[] = $telemetry;
                }
                $failureTelemetry[] = array_merge($telemetry, [
                    'final_reason_code' => $e->reasonCode(),
                    'source_final_status' => 'FAILED',
                ]);

                if (! $this->isYahooPartialTolerantFailure($e->reasonCode())) {
                    $aggregate = $this->buildYahooAggregateTelemetry(
                        $tradeDate,
                        $tickerCodes,
                        $uniqueTickerCodes,
                        $rows,
                        $requestTelemetry,
                        $failureTelemetry,
                        $apiConfig
                    );

                    $this->rememberAcquisitionTelemetry($aggregate);
                    throw $e->withContext($aggregate);
                }
            }
        }

        $aggregate = $this->buildYahooAggregateTelemetry(
            $tradeDate,
            $tickerCodes,
            $uniqueTickerCodes,
            $rows,
            $requestTelemetry,
            $failureTelemetry,
            $apiConfig
        );

        $this->rememberAcquisitionTelemetry($aggregate);

        if (empty($rows) && ! empty($failureTelemetry)) {
            throw new SourceAcquisitionException(
                'Yahoo Finance source acquisition failed for all requested tickers.',
                $this->dominantYahooFailureReasonCode($failureTelemetry),
                0,
                null,
                $aggregate
            );
        }

        if (empty($rows)) {
            $aggregate['source_final_status'] = 'FAILED';
            $aggregate['final_reason_code'] = 'RUN_SOURCE_NO_VALID_DATA';
            $aggregate['no_valid_data'] = true;
            $aggregate['empty_response_blocked'] = true;
            $aggregate['failure_reason_summary'] = ['RUN_SOURCE_NO_VALID_DATA' => 1];

            $this->rememberAcquisitionTelemetry($aggregate);

            throw new SourceAcquisitionException(
                'Yahoo Finance source returned no valid EOD bars for requested trade_date.',
                'RUN_SOURCE_NO_VALID_DATA',
                0,
                null,
                $aggregate
            );
        }

        return $rows;
    }

    private function buildGenericSuccessTelemetry($tradeDate, array $tickerCodes, array $rows, array $apiConfig, array $requestTelemetry = [])
    {
        return $this->mergeGenericTelemetry($requestTelemetry, [
            'source_mode' => 'api',
            'provider' => $this->providerName($apiConfig),
            'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
            'timeout_seconds' => $this->timeoutSeconds(),
            'retry_max' => $this->retryMax(),
            'final_reason_code' => null,
            'source_final_status' => 'SUCCESS',
            'trade_date' => $tradeDate,
            'requested_ticker_count' => count($tickerCodes),
            'returned_row_count' => count($rows),
            'accepted_row_count' => count($rows),
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
        ]);
    }

    private function buildGenericEmptyResponseTelemetry($tradeDate, array $tickerCodes, array $apiConfig, array $requestTelemetry = [])
    {
        return $this->mergeGenericTelemetry($requestTelemetry, [
            'source_mode' => 'api',
            'provider' => $this->providerName($apiConfig),
            'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
            'timeout_seconds' => $this->timeoutSeconds(),
            'retry_max' => $this->retryMax(),
            'attempt_count' => null,
            'attempts' => [],
            'success_after_retry' => false,
            'retry_exhausted' => false,
            'final_reason_code' => 'RUN_SOURCE_NO_VALID_DATA',
            'source_final_status' => 'FAILED',
            'trade_date' => $tradeDate,
            'requested_ticker_count' => count($tickerCodes),
            'returned_row_count' => 0,
            'accepted_row_count' => 0,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'no_valid_data' => true,
            'empty_response_blocked' => true,
            'failure_reason_summary' => ['RUN_SOURCE_NO_VALID_DATA' => 1],
        ]);
    }

    private function buildGenericFailureTelemetry($tradeDate, array $tickerCodes, array $apiConfig, array $requestTelemetry, string $reasonCode)
    {
        return $this->mergeGenericTelemetry($requestTelemetry, [
            'source_mode' => 'api',
            'provider' => $this->providerName($apiConfig),
            'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
            'timeout_seconds' => $this->timeoutSeconds(),
            'retry_max' => $this->retryMax(),
            'final_reason_code' => $reasonCode,
            'source_final_status' => 'FAILED',
            'trade_date' => $tradeDate,
            'requested_ticker_count' => count($tickerCodes),
            'returned_row_count' => 0,
            'accepted_row_count' => 0,
            'rejected_row_count' => 0,
            'invalid_row_count' => 0,
            'no_valid_data' => true,
            'empty_response_blocked' => true,
            'failure_reason_summary' => [$reasonCode => 1],
        ]);
    }

    private function mergeGenericTelemetry(array $requestTelemetry, array $terminalTelemetry)
    {
        $merged = array_merge($terminalTelemetry, array_filter($requestTelemetry, function ($value) {
            return $value !== null;
        }));

        foreach ($terminalTelemetry as $key => $value) {
            if (in_array($key, [
                'final_reason_code',
                'source_final_status',
                'returned_row_count',
                'accepted_row_count',
                'rejected_row_count',
                'invalid_row_count',
                'no_valid_data',
                'empty_response_blocked',
                'failure_reason_summary',
            ], true)) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    private function withTickerTelemetry(array $telemetry, $tickerCode)
    {
        $telemetry['ticker_code'] = $tickerCode;

        if (isset($telemetry['attempts']) && is_array($telemetry['attempts'])) {
            $telemetry['attempts'] = array_map(function ($attempt) use ($tickerCode) {
                return is_array($attempt) ? ($attempt + ['ticker_code' => $tickerCode]) : $attempt;
            }, $telemetry['attempts']);
        }

        return $telemetry;
    }

    private function isYahooPartialTolerantFailure($reasonCode)
    {
        return in_array($reasonCode, ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT'], true);
    }

    private function buildYahooAggregateTelemetry($tradeDate, array $requestedTickerCodes, array $uniqueTickerCodes, array $rows, array $requestTelemetry, array $failureTelemetry, array $apiConfig)
    {
        $attempts = [];
        $attemptCount = 0;
        $successAfterRetry = false;
        $retryExhausted = false;
        $lastHttpStatus = null;
        $lastUrl = null;
        $lastResponseBodySample = null;

        foreach ($requestTelemetry as $telemetry) {
            $attemptCount += (int) ($telemetry['attempt_count'] ?? 0);
            $successAfterRetry = $successAfterRetry || (bool) ($telemetry['success_after_retry'] ?? false);
            $retryExhausted = $retryExhausted || (bool) ($telemetry['retry_exhausted'] ?? false);
            if (array_key_exists('final_http_status', $telemetry) && $telemetry['final_http_status'] !== null) {
                $lastHttpStatus = $telemetry['final_http_status'];
            }
            if (array_key_exists('url', $telemetry) && $telemetry['url'] !== null && $telemetry['url'] !== '') {
                $lastUrl = $telemetry['url'];
            }
            if (array_key_exists('response_body_sample', $telemetry) && $telemetry['response_body_sample'] !== null && $telemetry['response_body_sample'] !== '') {
                $lastResponseBodySample = $telemetry['response_body_sample'];
            }
            if (isset($telemetry['attempts']) && is_array($telemetry['attempts'])) {
                $attempts = array_merge($attempts, $telemetry['attempts']);
            }
        }

        $returnedTickerCodes = array_values(array_filter(array_unique(array_map(function ($row) {
            return isset($row['ticker_code']) ? (string) $row['ticker_code'] : '';
        }, $rows))));

        $missingTickerCodes = array_values(array_diff($uniqueTickerCodes, $returnedTickerCodes));
        $failureReasonSummary = $this->summarizeYahooFailureReasons($failureTelemetry);
        $tradeDateNotFound = $this->hasYahooFailureFlag($failureTelemetry, 'trade_date_not_found_in_response');
        $isPartial = count($rows) > 0 && (count($failureTelemetry) > 0 || count($missingTickerCodes) > 0);
        $isFailed = count($rows) === 0 && count($failureTelemetry) > 0;
        $finalReasonCode = null;
        if ($isFailed) {
            $finalReasonCode = $this->dominantYahooFailureReasonCode($failureTelemetry);
        } elseif ($isPartial) {
            $finalReasonCode = 'RUN_SOURCE_PARTIAL_RESPONSE';
        }

        return [
            'source_mode' => 'api',
            'provider' => $this->providerName($apiConfig),
            'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
            'timeout_seconds' => $this->timeoutSeconds(),
            'retry_max' => $this->retryMax(),
            'attempt_count' => $attemptCount,
            'attempts' => $attempts,
            'success_after_retry' => $successAfterRetry,
            'retry_exhausted' => $retryExhausted,
            'final_reason_code' => $finalReasonCode,
            'final_http_status' => $lastHttpStatus,
            'url' => $lastUrl,
            'response_body_sample' => $lastResponseBodySample,
            'source_final_status' => $isFailed ? 'FAILED' : ($isPartial ? 'PARTIAL' : 'SUCCESS'),
            'trade_date' => $tradeDate,
            'ticker_code' => count($uniqueTickerCodes) > 0 ? (string) $uniqueTickerCodes[count($uniqueTickerCodes) - 1] : null,
            'requested_ticker_count' => count($requestedTickerCodes),
            'unique_ticker_count' => count($uniqueTickerCodes),
            'returned_row_count' => count($rows),
            'returned_ticker_count' => count($returnedTickerCodes),
            'missing_ticker_count' => max(0, count($missingTickerCodes)),
            'missing_ticker_codes' => $missingTickerCodes,
            'failed_ticker_count' => count($failureTelemetry),
            'failed_ticker_codes' => array_values(array_filter(array_map(function ($telemetry) {
                return isset($telemetry['ticker_code']) ? (string) $telemetry['ticker_code'] : null;
            }, $failureTelemetry))),
            'failure_reason_summary' => $failureReasonSummary,
            'trade_date_not_found_in_response' => $tradeDateNotFound,
        ];
    }

    private function hasYahooFailureFlag(array $failureTelemetry, $flag)
    {
        foreach ($failureTelemetry as $telemetry) {
            if (! empty($telemetry[$flag])) {
                return true;
            }
        }

        return false;
    }
    
    private function dominantYahooFailureReasonCode(array $failureTelemetry)
    {
        $summary = $this->summarizeYahooFailureReasons($failureTelemetry);
        if (empty($summary)) {
            return 'RUN_SOURCE_TIMEOUT';
        }

        arsort($summary);
        return (string) array_key_first($summary);
    }

    private function summarizeYahooFailureReasons(array $failureTelemetry)
    {
        $summary = [];
        foreach ($failureTelemetry as $telemetry) {
            $reasonCode = (string) ($telemetry['final_reason_code'] ?? 'RUN_SOURCE_TIMEOUT');
            if ($reasonCode === '') {
                $reasonCode = 'RUN_SOURCE_TIMEOUT';
            }
            $summary[$reasonCode] = ($summary[$reasonCode] ?? 0) + 1;
        }

        return $summary;
    }

    private function buildYahooFinanceUrl($tradeDate, $tickerCode, array $apiConfig)
    {
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        $symbolSuffix = (string) data_get($apiConfig, 'yahoo.symbol_suffix', '');
        $range = (string) data_get($apiConfig, 'yahoo.range', '10d');
        $interval = (string) data_get($apiConfig, 'yahoo.interval', '1d');
        $periodBounds = $this->yahooPeriodBounds($tradeDate);

        return str_replace(
            ['{date}', '{symbol}', '{symbols}', '{symbol_suffix}', '{range}', '{interval}', '{period1}', '{period2}'],
            [$tradeDate, $tickerCode, $tickerCode, $symbolSuffix, $range, $interval, $periodBounds['period1'], $periodBounds['period2']],
            $urlTemplate
        );
    }

    private function yahooPeriodBounds($tradeDate)
    {
        $timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
        $targetDate = Carbon::parse($tradeDate, $timezone)->startOfDay();

        return [
            'period1' => (string) $targetDate->copy()->subDay()->timestamp,
            'period2' => (string) $targetDate->copy()->addDay()->timestamp,
        ];
    }
    
    private function parseYahooFinancePayload($body, $tradeDate, $tickerCode, $capturedAt, array $apiConfig)
    {
        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            throw new SourceAcquisitionException('Yahoo Finance payload is not valid JSON.', 'RUN_SOURCE_MALFORMED_PAYLOAD');
        }

        $result = data_get($decoded, 'chart.result.0');
        if (! is_array($result)) {
            throw new SourceAcquisitionException('Yahoo Finance chart payload is missing result[0].', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $timestamps = data_get($result, 'timestamp');
        $quote = data_get($result, 'indicators.quote.0');
        if (! is_array($timestamps) || ! is_array($quote)) {
            throw new SourceAcquisitionException('Yahoo Finance chart payload is missing timestamp/quote data.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $adjclose = data_get($result, 'indicators.adjclose.0.adjclose', []);
        $meta = is_array(data_get($result, 'meta')) ? data_get($result, 'meta') : [];
        $exchangeTimezone = isset($meta['exchangeTimezoneName']) ? (string) $meta['exchangeTimezoneName'] : config('market_data.platform.timezone');

        foreach (array_values($timestamps) as $position => $timestamp) {
            if (! is_numeric($timestamp)) {
                continue;
            }

            $rowTradeDate = Carbon::createFromTimestampUTC((int) $timestamp)
                ->setTimezone($exchangeTimezone)
                ->toDateString();

            if ($rowTradeDate !== $tradeDate) {
                continue;
            }

            return [
                'ticker_code' => $tickerCode,
                'trade_date' => $tradeDate,
                'open' => $quote['open'][$position] ?? null,
                'high' => $quote['high'][$position] ?? null,
                'low' => $quote['low'][$position] ?? null,
                'close' => $quote['close'][$position] ?? null,
                'volume' => $quote['volume'][$position] ?? null,
                'adj_close' => $adjclose[$position] ?? ($quote['close'][$position] ?? null),
                'source_name' => isset($apiConfig['source_name']) ? $apiConfig['source_name'] : 'YAHOO_FINANCE',
                'source_row_ref' => 'yahoo:'.$tickerCode.':'.$tradeDate,
                'captured_at' => $capturedAt,
            ];
        }

        return null;
    }

    private function providerName(array $apiConfig)
    {
        return Str::lower(trim((string) ($apiConfig['provider'] ?? 'generic')));
    }

    public function consumeLastAcquisitionTelemetry()
    {
        $telemetry = $this->lastAcquisitionTelemetry;
        $this->lastAcquisitionTelemetry = [];

        return is_array($telemetry) ? $telemetry : [];
    }

    private function rememberAcquisitionTelemetry(array $telemetry)
    {
        $this->lastAcquisitionTelemetry = $telemetry;
    }

    private function requestWithRetry($url, array $requestContext = [])
    {
        $retryMax = $this->retryMax();
        $baseBackoffMs = max(0, (int) config('market_data.provider.api_backoff_ms'));
        $capturedAt = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $provider = (string) $this->providerName(config('market_data.source.api'));
        $sourceName = strtoupper((string) data_get(config('market_data.source.api'), 'source_name', config('market_data.source.default_source_name', 'API_FREE')));
        $timeoutSeconds = $this->timeoutSeconds();
        $lastException = null;
        $attemptLog = [];

        for ($attempt = 0; $attempt <= $retryMax; $attempt++) {
            $attemptNumber = $attempt + 1;
            $throttleDelayMs = $this->applyThrottleAndJitter($attempt);

            try {
                $response = $this->performHttpRequest($url);
                $status = (int) $response['status'];
                $responseBodySample = $this->responseBodySample($response['body']);

                if (in_array($status, [401, 403], true)) {
                    throw new SourceAcquisitionException('Source API authentication/config failed with HTTP '.$status.'.', 'RUN_SOURCE_AUTH_ERROR', 0, null, [
                        'final_http_status' => $status,
                        'response_body_sample' => $responseBodySample,
                    ]);
                }

                if ($status === 429) {
                    throw new SourceAcquisitionException('Source API rate limited the request.', 'RUN_SOURCE_RATE_LIMIT', 0, null, [
                        'final_http_status' => $status,
                        'response_body_sample' => $responseBodySample,
                    ]);
                }

                if ($status === 408 || $status >= 500 || $status === 0) {
                    throw new SourceAcquisitionException('Source API request timed out or returned transient server error.', 'RUN_SOURCE_TIMEOUT', 0, null, [
                        'final_http_status' => $status,
                        'response_body_sample' => $responseBodySample,
                    ]);
                }

                if ($status < 200 || $status >= 300) {
                    throw new SourceAcquisitionException('Source API returned unexpected HTTP status '.$status.'.', 'RUN_SOURCE_MALFORMED_PAYLOAD', 0, null, [
                        'final_http_status' => $status,
                        'response_body_sample' => $responseBodySample,
                    ]);
                }

                $attemptCount = count($attemptLog) + 1;
                $attempts = $attemptLog;
                $attempts[] = [
                    'attempt_number' => $attemptNumber,
                    'reason_code' => null,
                    'http_status' => $status,
                    'throttle_delay_ms' => $throttleDelayMs,
                    'backoff_delay_ms' => 0,
                    'will_retry' => false,
                ];

                $this->rememberAcquisitionTelemetry($requestContext + [
                    'url' => $url,
                    'provider' => $provider,
                    'source_name' => $sourceName,
                    'timeout_seconds' => $timeoutSeconds,
                    'retry_max' => $retryMax,
                    'attempt_count' => $attemptCount,
                    'attempts' => $attempts,
                    'success_after_retry' => $attemptCount > 1,
                    'retry_exhausted' => false,
                    'final_reason_code' => null,
                    'final_http_status' => $status,
                    'response_body_sample' => $responseBodySample,
                    'captured_at' => $capturedAt,
                ]);

                return [
                    'body' => $response['body'],
                    'captured_at' => $capturedAt,
                ];
            } catch (SourceAcquisitionException $e) {
                $willRetry = $this->shouldRetry($e->reasonCode(), $attempt, $retryMax);
                $backoffDelayMs = $willRetry ? $this->backoff($attempt, $baseBackoffMs) : 0;

                $exceptionContext = $e->context();
                $httpStatus = $exceptionContext['final_http_status'] ?? $this->extractStatusFromExceptionContext($e);
                $responseBodySample = $exceptionContext['response_body_sample'] ?? null;

                $attemptLog[] = [
                    'attempt_number' => $attemptNumber,
                    'reason_code' => $e->reasonCode(),
                    'http_status' => $httpStatus,
                    'throttle_delay_ms' => $throttleDelayMs,
                    'backoff_delay_ms' => $backoffDelayMs,
                    'will_retry' => $willRetry,
                ];

                $failureContext = $requestContext + [
                    'url' => $url,
                    'provider' => $provider,
                    'source_name' => $sourceName,
                    'timeout_seconds' => $timeoutSeconds,
                    'retry_max' => $retryMax,
                    'attempt_count' => count($attemptLog),
                    'attempts' => $attemptLog,
                    'success_after_retry' => false,
                    'retry_exhausted' => ! $willRetry && in_array($e->reasonCode(), ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT'], true),
                    'final_reason_code' => $e->reasonCode(),
                    'final_http_status' => $httpStatus,
                    'response_body_sample' => $responseBodySample,
                    'captured_at' => $capturedAt,
                ];

                $this->rememberAcquisitionTelemetry($failureContext);

                $lastException = $e->withContext($failureContext);

                if (! $willRetry) {
                    throw $lastException;
                }
            }
        }

        throw $lastException ?: new SourceAcquisitionException('Unknown source API acquisition failure.', 'RUN_SOURCE_TIMEOUT');
    }

    private function shouldRetry($reasonCode, $attempt, $retryMax)
    {
        if ($attempt >= $retryMax) {
            return false;
        }

        return in_array($reasonCode, ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT'], true);
    }

    private function backoff($attempt, $baseBackoffMs)
    {
        $multiplier = (int) pow(2, $attempt);
        $jitterMs = random_int(50, 150);
        $delayMs = $baseBackoffMs * $multiplier + $jitterMs;
        usleep($delayMs * 1000);

        return $delayMs;
    }

    private function applyThrottleAndJitter($attempt)
    {
        $qps = max(1, (int) config('market_data.provider.api_throttle_qps'));
        $throttleUs = (int) floor(1000000 / $qps);
        $jitterUs = random_int(25000, 125000);
        $delayUs = $throttleUs + $jitterUs;

        if ($attempt > 0 || $throttleUs > 0) {
            usleep($delayUs);
        }

        return (int) floor($delayUs / 1000);
    }

    private function extractStatusFromExceptionContext(SourceAcquisitionException $e)
    {
        if (preg_match('/HTTP\s+(\d{3})/i', $e->getMessage(), $matches)) {
            return (int) $matches[1];
        }

        if ($e->reasonCode() === 'RUN_SOURCE_RATE_LIMIT') {
            return 429;
        }

        if ($e->reasonCode() === 'RUN_SOURCE_AUTH_ERROR') {
            return 401;
        }

        return null;
    }

    private function performHttpRequest($url)
    {
        if ($this->fetcher) {
            return call_user_func($this->fetcher, $url, $this->buildHeaders(), $this->timeoutSeconds());
        }

        $headers = implode("\r\n", $this->buildHeaders());
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headers,
                'timeout' => $this->timeoutSeconds(),
                'ignore_errors' => true,
            ],
        ]);

        $warning = null;
        set_error_handler(function ($severity, $message) use (&$warning) {
            $warning = $message;
            return true;
        });

        try {
            $body = file_get_contents($url, false, $context);
        } finally {
            restore_error_handler();
        }

        if ($body === false) {
            throw new SourceAcquisitionException($warning ?: 'Source API request failed.', 'RUN_SOURCE_TIMEOUT');
        }

        return [
            'status' => $this->extractHttpStatus(isset($http_response_header) ? $http_response_header : []),
            'body' => $body,
            'headers' => isset($http_response_header) ? $http_response_header : [],
        ];
    }

    private function buildHeaders()
    {
         $userAgent = (string) config('market_data.source.api.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36');
        $headers = [
            'User-Agent: '.$userAgent,
            'Accept: application/json,text/plain,*/*',
            'Accept-Language: en-US,en;q=0.9,id;q=0.8',
            'Connection: close',
        ];
        $configuredHeaders = config('market_data.source.api.headers', []);

        if (is_array($configuredHeaders)) {
            foreach ($configuredHeaders as $headerName => $headerValue) {
                if (is_int($headerName)) {
                    $header = trim((string) $headerValue);
                    if ($header !== '') {
                        $headers[] = $header;
                    }
                    continue;
                }

                $headerName = trim((string) $headerName);
                $headerValue = trim((string) $headerValue);
                if ($headerName !== '' && $headerValue !== '') {
                    $headers[] = $headerName.': '.$headerValue;
                }
            }
        }

        $headerName = trim((string) config('market_data.source.api.auth_header_name'));
        $token = trim((string) config('market_data.source.api.auth_token'));

        if ($headerName !== '' && $token !== '') {
            $headers[] = $headerName.': '.$token;
        }

        return $headers;
    }

    private function retryMax()
    {
        return min(3, max(0, (int) config('market_data.provider.api_retry_max')));
    }

    private function responseBodySample($body)
    {
        $sample = substr((string) $body, 0, 1000);

        return str_replace(["\r", "\n", "\0"], [' ', ' ', ''], $sample);
    }
    
    private function timeoutSeconds()
    {
        return max(1, (int) config('market_data.source.api.timeout_seconds'));
    }

    private function extractHttpStatus(array $headers)
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $header, $matches)) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    private function parsePayload($body, $tradeDate, $capturedAt)
    {
        $format = strtolower((string) config('market_data.source.api.response_format', 'json'));

        if ($format === 'csv') {
            return $this->parseCsv($body);
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            throw new SourceAcquisitionException('Source API payload is not valid JSON.', 'RUN_SOURCE_MALFORMED_PAYLOAD');
        }

        $rows = $this->extractRowsByPath($decoded, (string) config('market_data.source.api.response_rows_path', ''));
        if (! is_array($rows)) {
            throw new SourceAcquisitionException('Source API response rows path is missing or not iterable.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        return array_values($rows);
    }

    private function parseCsv($body)
    {
        $lines = preg_split('/\r\n|\n|\r/', trim((string) $body));
        if (count($lines) < 2) {
            throw new SourceAcquisitionException('Source API CSV payload has no data rows.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $header = str_getcsv(array_shift($lines));
        $normalizedHeader = array_map(function ($item) {
            return Str::snake(trim($item));
        }, $header);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line);
            $rows[] = array_combine($normalizedHeader, $values);
        }

        return $rows;
    }

    private function extractRowsByPath(array $decoded, $path)
    {
        if ($path === '' || $path === '.') {
            return $decoded;
        }

        $current = $decoded;
        foreach (explode('.', $path) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private function normalizeRow(array $row, $tradeDate, $index, $capturedAt, array $apiConfig)
    {
        $fieldMap = isset($apiConfig['field_map']) && is_array($apiConfig['field_map']) ? $apiConfig['field_map'] : [];
        $sourceCapturedAt = $this->extractField($row, isset($fieldMap['captured_at']) ? $fieldMap['captured_at'] : null);

        return [
            'ticker_code' => $this->normalizeTickerCode($this->extractField($row, isset($fieldMap['ticker_code']) ? $fieldMap['ticker_code'] : 'ticker_code')),
            'trade_date' => $this->extractField($row, isset($fieldMap['trade_date']) ? $fieldMap['trade_date'] : 'trade_date') ?: $tradeDate,
            'open' => $this->extractField($row, isset($fieldMap['open']) ? $fieldMap['open'] : 'open'),
            'high' => $this->extractField($row, isset($fieldMap['high']) ? $fieldMap['high'] : 'high'),
            'low' => $this->extractField($row, isset($fieldMap['low']) ? $fieldMap['low'] : 'low'),
            'close' => $this->extractField($row, isset($fieldMap['close']) ? $fieldMap['close'] : 'close'),
            'volume' => $this->extractField($row, isset($fieldMap['volume']) ? $fieldMap['volume'] : 'volume'),
            'adj_close' => $this->extractField($row, isset($fieldMap['adj_close']) ? $fieldMap['adj_close'] : 'adj_close'),
            'source_name' => isset($apiConfig['source_name']) ? $apiConfig['source_name'] : 'API_FREE',
            'source_row_ref' => $this->extractField($row, isset($fieldMap['source_row_ref']) ? $fieldMap['source_row_ref'] : 'source_row_ref') ?: 'api:'.$index,
            'captured_at' => $sourceCapturedAt ? Carbon::parse($sourceCapturedAt)->setTimezone(config('market_data.platform.timezone'))->toDateTimeString() : $capturedAt,
        ];
    }

    private function extractField(array $row, $field)
    {
        if ($field === null || $field === '') {
            return null;
        }

        if (array_key_exists($field, $row)) {
            return $row[$field];
        }

        $current = $row;
        foreach (explode('.', $field) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private function normalizeTickerCode($value)
    {
        if ($value === null) {
            return null;
        }

        return Str::upper(trim((string) $value));
    }
}
