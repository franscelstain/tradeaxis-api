<?php

namespace App\Infrastructure\MarketData\Source;

use App\Application\MarketData\Ports\ApiEodBarsSource;
use App\Application\MarketData\Ports\SourceObservationRecorder;
use App\Infrastructure\MarketData\Observation\InMemorySourceObservationRecorder;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicApiEodBarsAdapter implements ApiEodBarsSource
{
    private const YAHOO_ADAPTER_VERSION = 'yahoo_chart_v2';

    private const YAHOO_SCHEMA_VERSION = 'yahoo_chart_schema_v1';

    private $fetcher;
    private $lastAcquisitionTelemetry = [];
    private $equityProviderSymbols;
    private $benchmarkProviderSymbols;
    private $observations;

    public function __construct(
        callable $fetcher = null,
        EquityProviderSymbolResolver $equityProviderSymbols = null,
        BenchmarkProviderSymbolResolver $benchmarkProviderSymbols = null,
        SourceObservationRecorder $observations = null
    )
    {
        $this->fetcher = $fetcher;
        $this->equityProviderSymbols = $equityProviderSymbols ?: new EquityProviderSymbolResolver();
        $this->benchmarkProviderSymbols = $benchmarkProviderSymbols ?: new BenchmarkProviderSymbolResolver();
        $this->observations = $observations ?: $this->defaultObservationRecorder();
    }

    public function fetchOrLoadEodBars($tradeDate, $sourceMode, array $tickerCodes = [], array $context = [])
    {
        $this->lastAcquisitionTelemetry = [];
        if ($sourceMode !== 'api') {
            throw new \RuntimeException('Source mode '.$sourceMode.' tidak didukung oleh PublicApiEodBarsAdapter.');
        }

        $apiConfig = config('market_data.source.api');
        $this->assertActiveSchemaContract($apiConfig);
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        if ($urlTemplate === '') {
            throw new SourceAcquisitionException('Source API endpoint template belum dikonfigurasi.', 'RUN_SOURCE_AUTH_ERROR');
        }

        if ($this->providerName($apiConfig) === 'yahoo_finance') {
            return $this->fetchYahooFinanceBars($tradeDate, $tickerCodes, $apiConfig, $context);
        }

        $url = str_replace(
            ['{date}', '{symbols}'],
            [$tradeDate, implode(',', $tickerCodes)],
            $urlTemplate
        );

        $response = $this->requestWithRetry($url, array_merge($context, [
            'trade_date' => $tradeDate,
            'requested_ticker_count' => count($tickerCodes),
        ]));
        $requestTelemetry = $this->consumeLastAcquisitionTelemetry();

        try {
            $rows = $this->parsePayload($response['body'], $tradeDate, $response['captured_at']);
        } catch (SourceAcquisitionException $e) {
            $this->rejectResponse($response, $e->reasonCode());
            $telemetry = $this->buildGenericFailureTelemetry($tradeDate, $tickerCodes, $apiConfig, $requestTelemetry, $e->reasonCode());
            $this->rememberAcquisitionTelemetry($telemetry);

            throw $e->withContext($telemetry);
        }

        if (count($rows) === 0) {
            $this->rejectResponse($response, 'RUN_SOURCE_NO_VALID_DATA');
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
        $normalizedRows = $this->acceptResponseRows($response, $normalizedRows);

        $this->rememberAcquisitionTelemetry($this->buildGenericSuccessTelemetry($tradeDate, $tickerCodes, $normalizedRows, $apiConfig, $requestTelemetry));

        return $normalizedRows;
    }

    public function fetchOrLoadEodBarsRange($startDate, $endDate, $sourceMode, array $tickerCodes, array $tradingDates, array $context = [])
    {
        $this->lastAcquisitionTelemetry = [];
        if ($sourceMode !== 'api') {
            throw new \RuntimeException('Source mode '.$sourceMode.' tidak didukung oleh PublicApiEodBarsAdapter range acquisition.');
        }

        $apiConfig = config('market_data.source.api');
        $this->assertActiveSchemaContract($apiConfig);
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        if ($urlTemplate === '') {
            throw new SourceAcquisitionException('Source API endpoint template belum dikonfigurasi.', 'RUN_SOURCE_AUTH_ERROR');
        }

        if ($this->providerName($apiConfig) !== 'yahoo_finance') {
            throw new SourceAcquisitionException('API range-window acquisition currently supports yahoo_finance only.', 'SOURCE_SCHEMA_INVALID');
        }

        return $this->fetchYahooFinanceBarsRange($startDate, $endDate, $tickerCodes, $tradingDates, $apiConfig, $context);
    }

    public function fetchOrLoadBenchmarkBars($tradeDate, $sourceMode, array $benchmarks = [], array $context = [])
    {
        $this->lastAcquisitionTelemetry = [];
        if ($sourceMode !== 'api') {
            throw new \RuntimeException('Source mode '.$sourceMode.' tidak didukung oleh PublicApiEodBarsAdapter.');
        }

        $apiConfig = config('market_data.source.api');
        $this->assertActiveSchemaContract($apiConfig);
        if ($this->providerName($apiConfig) !== 'yahoo_finance') {
            throw new SourceAcquisitionException('Benchmark API source currently supports yahoo_finance only.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        if (empty($benchmarks)) {
            throw new SourceAcquisitionException('Benchmark source membutuhkan benchmark master aktif.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $rows = [];
        $requestTelemetry = [];
        $failureTelemetry = [];
        $circuitBreakerTelemetry = null;

        foreach ($benchmarks as $benchmark) {
            $circuitBreakerTelemetry = $this->circuitBreakerTelemetry(
                $failureTelemetry,
                count($benchmarks),
                count($rows)
            );
            if ($circuitBreakerTelemetry !== null) {
                break;
            }
            $benchmark = (array) $benchmark;
            $benchmarkCode = $this->normalizeTickerCode($benchmark['benchmark_code'] ?? null);
            $providerSymbol = $this->benchmarkProviderSymbols->resolve(
                $benchmarkCode,
                $benchmark['provider_symbol'] ?? null,
                $benchmark['instrument_type'] ?? null
            );

            $url = $this->buildYahooFinanceUrl($tradeDate, $benchmarkCode, $apiConfig, $providerSymbol);
            $requestContext = array_merge($context, [
                'trade_date' => $tradeDate,
                'benchmark_code' => $benchmarkCode,
                'provider_symbol' => $providerSymbol,
                'requested_benchmark_count' => count($benchmarks),
            ]);
            $lastBenchmarkRequestTelemetry = [];
            $response = null;

            try {
                $response = $this->requestWithRetry($url, $requestContext);
                $telemetry = $this->consumeLastAcquisitionTelemetry();
                $lastBenchmarkRequestTelemetry = $telemetry;
                $requestTelemetry[] = $telemetry + [
                    'benchmark_code' => $benchmarkCode,
                    'provider_symbol' => $providerSymbol,
                ];

                $parsed = $this->parseYahooFinancePayloadRowsForCode(
                    $response['body'],
                    [(string) $tradeDate => true],
                    $benchmarkCode,
                    $response['captured_at'],
                    $apiConfig,
                    'benchmark',
                    $providerSymbol
                );
                $row = $parsed['rows'][0] ?? null;

                if ($row === null) {
                    $rejectedRows = array_map(function (array $invalid) use ($benchmarkCode, $providerSymbol) {
                        return array_merge($invalid, [
                            'benchmark_code' => $benchmarkCode,
                            'provider_symbol' => $providerSymbol,
                        ]);
                    }, $parsed['invalid_rows'] ?? []);
                    if ($rejectedRows !== []) {
                        $this->rejectResponseRows($response, $rejectedRows, 'RUN_SOURCE_NO_VALID_DATA');
                    } else {
                        $this->rejectResponse($response, 'RUN_SOURCE_NO_VALID_DATA');
                    }
                    $failureTelemetry[] = array_merge($lastBenchmarkRequestTelemetry, [
                        'benchmark_code' => $benchmarkCode,
                        'provider_symbol' => $providerSymbol,
                        'final_reason_code' => 'RUN_SOURCE_NO_VALID_DATA',
                        'source_final_status' => 'FAILED',
                        'trade_date_not_found_in_response' => true,
                    ]);
                    continue;
                }

                $accepted = $this->acceptResponseRows($response, [
                    $this->normalizeBenchmarkRow($row, $benchmark, $providerSymbol, $response['captured_at'], $apiConfig),
                ]);
                $rows[] = $accepted[0];
            } catch (SourceAcquisitionException $e) {
                if (isset($response) && is_array($response)) {
                    $this->rejectResponse($response, $e->reasonCode());
                }
                $failureTelemetry[] = ($e->context() ?: $lastBenchmarkRequestTelemetry) + [
                    'benchmark_code' => $benchmarkCode,
                    'provider_symbol' => $providerSymbol,
                    'final_reason_code' => $e->reasonCode(),
                    'source_final_status' => 'FAILED',
                ];

                if (! $this->isYahooPartialTolerantFailure($e->reasonCode())) {
                    $aggregate = $this->buildYahooBenchmarkAggregateTelemetry($tradeDate, $benchmarks, $rows, $requestTelemetry, $failureTelemetry, $apiConfig);
                    $this->rememberAcquisitionTelemetry($aggregate);
                    throw $e->withContext($aggregate);
                }
            }
        }

        $aggregate = $this->buildYahooBenchmarkAggregateTelemetry($tradeDate, $benchmarks, $rows, $requestTelemetry, $failureTelemetry, $apiConfig);
        if ($circuitBreakerTelemetry !== null) {
            $aggregate = array_merge($aggregate, $circuitBreakerTelemetry);
        }
        $this->rememberAcquisitionTelemetry($aggregate);

        if (empty($rows) && ! empty($failureTelemetry)) {
            throw new SourceAcquisitionException(
                'Yahoo Finance benchmark acquisition failed for all requested benchmarks.',
                $this->dominantYahooFailureReasonCode($failureTelemetry),
                0,
                null,
                $aggregate
            );
        }

        if (empty($rows)) {
            throw new SourceAcquisitionException(
                'Yahoo Finance source returned no valid benchmark bars for requested trade_date.',
                'RUN_SOURCE_NO_VALID_DATA',
                0,
                null,
                $aggregate
            );
        }

        return $rows;
    }


    private function fetchYahooFinanceBars($tradeDate, array $tickerCodes, array $apiConfig, array $context = [])
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

        $circuitBreakerTelemetry = null;

        foreach ($uniqueTickerCodes as $tickerCode) {
            // Our own retry behaviour is the likeliest cause of losing access to an unofficial
            // free source. A universe of this size already issues hundreds of requests per date
            // before any retry; continuing through a wholesale failure multiplies load exactly
            // when the source is refusing. Stopping early to protect access is a legitimate
            // outcome that must be visible, not a failure to disguise.
            //
            // Owner contract: docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md
            //                 — "Source access self-protection (LOCKED)"
            $circuitBreakerTelemetry = $this->circuitBreakerTelemetry(
                $failureTelemetry,
                count($uniqueTickerCodes),
                $index
            );
            if ($circuitBreakerTelemetry !== null) {
                break;
            }

            $identity = $this->resolveEquityIdentity($tickerCode, $apiConfig, $tradeDate, $context);
            $providerSymbol = $identity['provider_symbol'];
            $url = $this->buildYahooFinanceUrl($tradeDate, $tickerCode, $apiConfig, $providerSymbol);
            $requestContext = array_merge($context, $identity, [
                'trade_date' => $tradeDate,
                'ticker_code' => $tickerCode,
                'requested_ticker_count' => count($tickerCodes),
                'unique_ticker_count' => count($uniqueTickerCodes),
            ]);
            $lastTickerRequestTelemetry = [];
            $response = null;

            try {
                $response = $this->requestWithRetry($url, $requestContext);
                $telemetry = $this->consumeLastAcquisitionTelemetry();
                $lastTickerRequestTelemetry = $telemetry;
                $requestTelemetry[] = $this->withTickerTelemetry($telemetry, $tickerCode);

                $parsed = $this->parseYahooFinancePayloadRowsForCode(
                    $response['body'],
                    [(string) $tradeDate => true],
                    $tickerCode,
                    $response['captured_at'],
                    $apiConfig,
                    'yahoo',
                    $providerSymbol
                );
                $row = $parsed['rows'][0] ?? null;
                if ($row === null) {
                    $rejectedRows = array_map(function (array $invalid) use ($providerSymbol) {
                        $invalid['provider_symbol'] = $providerSymbol;
                        return $invalid;
                    }, $parsed['invalid_rows'] ?? []);
                    if ($rejectedRows !== []) {
                        $this->rejectResponseRows($response, $rejectedRows, 'RUN_SOURCE_NO_VALID_DATA');
                    } else {
                        $this->rejectResponse($response, 'RUN_SOURCE_NO_VALID_DATA');
                    }
                    $failureTelemetry[] = $this->withTickerTelemetry(array_merge($lastTickerRequestTelemetry, [
                        'final_reason_code' => 'RUN_SOURCE_NO_VALID_DATA',
                        'source_final_status' => 'FAILED',
                        'trade_date_not_found_in_response' => true,
                    ]), $tickerCode);
                    continue;
                }

                $index++;
                $normalized = $this->normalizeRow($row, $tradeDate, $index, $response['captured_at'], $apiConfig);
                $normalized = array_merge($normalized, [
                    'listing_id' => $identity['listing_id'] ?? null,
                    'provider_symbol' => $providerSymbol,
                    'provider_mapping_id' => $identity['provider_mapping_id'] ?? null,
                    'mapping_revision' => $identity['mapping_revision'] ?? null,
                ]);
                $accepted = $this->acceptResponseRows($response, [$normalized]);
                $rows[] = $accepted[0];
            } catch (SourceAcquisitionException $e) {
                if (is_array($response)) {
                    $this->rejectResponse($response, $e->reasonCode());
                }
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
        if ($circuitBreakerTelemetry !== null) {
            $aggregate = array_merge($aggregate, $circuitBreakerTelemetry);
        }

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

    private function fetchYahooFinanceBarsRange($startDate, $endDate, array $tickerCodes, array $tradingDates, array $apiConfig, array $context = [])
    {
        if (empty($tickerCodes)) {
            throw new SourceAcquisitionException('Yahoo Finance range source membutuhkan ticker universe yang tidak kosong.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $tradingDates = array_values(array_unique(array_filter(array_map('strval', $tradingDates))));
        sort($tradingDates);
        if (empty($tradingDates)) {
            throw new SourceAcquisitionException('Yahoo Finance range source membutuhkan trading date filter yang tidak kosong.', 'CONFIG_INVALID');
        }

        $rowsByDate = array_fill_keys($tradingDates, []);
        $rows = [];
        $requestTelemetry = [];
        $failureTelemetry = [];
        $invalidOhlcvSkipped = 0;
        $successfulTickerCount = 0;
        $circuitBreakerTelemetry = null;

        $uniqueTickerCodes = array_values(array_unique(array_filter(array_map(function ($tickerCode) {
            return $this->normalizeTickerCode($tickerCode);
        }, $tickerCodes))));

        foreach ($uniqueTickerCodes as $tickerCode) {
            $circuitBreakerTelemetry = $this->circuitBreakerTelemetry(
                $failureTelemetry,
                count($uniqueTickerCodes),
                $successfulTickerCount
            );
            if ($circuitBreakerTelemetry !== null) {
                break;
            }

            $identitiesByDate = [];
            foreach ($tradingDates as $tradingDate) {
                $identitiesByDate[$tradingDate] = $this->resolveEquityIdentity($tickerCode, $apiConfig, $tradingDate, $context);
            }
            $providerSymbols = array_values(array_unique(array_map(function (array $resolved) {
                return (string) $resolved['provider_symbol'];
            }, $identitiesByDate)));
            if (count($providerSymbols) !== 1) {
                throw new SourceAcquisitionException(
                    'A single range request crosses provider-symbol mapping intervals and cannot prove one transport identity.',
                    'PROVIDER_SYMBOL_MAPPING_AMBIGUOUS',
                    0,
                    null,
                    [
                        'ticker_code' => $tickerCode,
                        'source_window_start' => $startDate,
                        'source_window_end' => $endDate,
                        'provider_symbols' => $providerSymbols,
                    ]
                );
            }
            $lastTradingDate = $tradingDates[count($tradingDates) - 1];
            $identity = $identitiesByDate[$lastTradingDate];
            $providerSymbol = $identity['provider_symbol'];
            $url = $this->buildYahooFinanceRangeUrl($startDate, $endDate, $tickerCode, $apiConfig, $providerSymbol);
            $requestContext = array_merge($context, $identity, [
                'source_acquisition_mode' => 'range_window',
                'source_window_start' => $startDate,
                'source_window_end' => $endDate,
                'ticker_code' => $tickerCode,
                'requested_ticker_count' => count($tickerCodes),
                'unique_ticker_count' => count($uniqueTickerCodes),
                'requested_trade_date_count' => count($tradingDates),
            ]);
            $lastTickerRequestTelemetry = [];
            $response = null;

            try {
                $response = $this->requestWithRetry($url, $requestContext);
                $telemetry = $this->consumeLastAcquisitionTelemetry();
                $lastTickerRequestTelemetry = $telemetry;
                $requestTelemetry[] = $this->withTickerTelemetry($telemetry, $tickerCode);

                $parsed = $this->parseYahooFinancePayloadRowsForCode(
                    $response['body'],
                    array_fill_keys($tradingDates, true),
                    $tickerCode,
                    $response['captured_at'],
                    $apiConfig,
                    'yahoo',
                    $providerSymbol
                );

                $rejectedRows = array_map(function (array $invalid) use ($providerSymbol) {
                    $invalid['provider_symbol'] = $providerSymbol;
                    return $invalid;
                }, $parsed['invalid_rows'] ?? []);

                if (empty($parsed['rows'])) {
                    if ($rejectedRows !== []) {
                        $this->rejectResponseRows($response, $rejectedRows, 'RUN_SOURCE_NO_VALID_DATA');
                    } else {
                        $this->rejectResponse($response, 'RUN_SOURCE_NO_VALID_DATA');
                    }
                    $failureTelemetry[] = $this->withTickerTelemetry(array_merge($lastTickerRequestTelemetry, [
                        'final_reason_code' => 'RUN_SOURCE_NO_VALID_DATA',
                        'source_final_status' => 'FAILED',
                        'trade_date_not_found_in_response' => true,
                    ]), $tickerCode);
                    $invalidOhlcvSkipped += (int) ($parsed['invalid_ohlcv_skipped'] ?? 0);
                    continue;
                }

                $normalizedRows = array_map(function (array $row) use ($identitiesByDate, $providerSymbol) {
                    $identity = $identitiesByDate[(string) $row['trade_date']];
                    return array_merge($row, [
                        'listing_id' => $identity['listing_id'] ?? null,
                        'provider_symbol' => $providerSymbol,
                        'provider_mapping_id' => $identity['provider_mapping_id'] ?? null,
                        'mapping_revision' => $identity['mapping_revision'] ?? null,
                    ]);
                }, $parsed['rows']);
                $normalizedRows = $this->acceptResponseRows($response, $normalizedRows, $rejectedRows);

                foreach ($normalizedRows as $row) {
                    $rowDate = (string) $row['trade_date'];
                    $rowsByDate[$rowDate][] = $row;
                    $rows[] = $row;
                }
                $successfulTickerCount++;
                $invalidOhlcvSkipped += (int) ($parsed['invalid_ohlcv_skipped'] ?? 0);
            } catch (SourceAcquisitionException $e) {
                if (is_array($response)) {
                    $this->rejectResponse($response, $e->reasonCode());
                }
                $exceptionTelemetry = $e->context();
                $telemetry = $this->withTickerTelemetry($exceptionTelemetry ?: $lastTickerRequestTelemetry, $tickerCode);
                if (empty($telemetry)) {
                    $telemetry = $this->withTickerTelemetry(array_merge($context, [
                        'source_acquisition_mode' => 'range_window',
                        'source_window_start' => $startDate,
                        'source_window_end' => $endDate,
                        'ticker_code' => $tickerCode,
                        'provider' => $this->providerName($apiConfig),
                        'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
                        'final_reason_code' => $e->reasonCode(),
                        'source_final_status' => 'FAILED',
                    ]), $tickerCode);
                }

                if (! empty($exceptionTelemetry) || empty($lastTickerRequestTelemetry)) {
                    $requestTelemetry[] = $telemetry;
                }
                $failureTelemetry[] = array_merge($telemetry, [
                    'final_reason_code' => $e->reasonCode(),
                    'source_final_status' => 'FAILED',
                ]);

                if (! $this->isYahooPartialTolerantFailure($e->reasonCode())) {
                    $aggregate = $this->buildYahooRangeAggregateTelemetry(
                        $startDate,
                        $endDate,
                        $tradingDates,
                        $tickerCodes,
                        $uniqueTickerCodes,
                        $rows,
                        $rowsByDate,
                        $requestTelemetry,
                        $failureTelemetry,
                        $apiConfig,
                        $context,
                        $invalidOhlcvSkipped
                    );

                    $aggregate['source_acquisition_state'] = 'SYSTEMIC_FAILED';
                    $aggregate['source_final_status'] = 'SYSTEMIC_FAILED';
                    $aggregate['final_reason_code'] = $e->reasonCode();
                    $this->rememberAcquisitionTelemetry($aggregate);
                    throw $e->withContext($aggregate);
                }
            }
        }

        $aggregate = $this->buildYahooRangeAggregateTelemetry(
            $startDate,
            $endDate,
            $tradingDates,
            $tickerCodes,
            $uniqueTickerCodes,
            $rows,
            $rowsByDate,
            $requestTelemetry,
            $failureTelemetry,
            $apiConfig,
            $context,
            $invalidOhlcvSkipped
        );
        if ($circuitBreakerTelemetry !== null) {
            $aggregate = array_merge($aggregate, $circuitBreakerTelemetry);
        }

        $this->rememberAcquisitionTelemetry($aggregate);

        if (empty($rows) && ! empty($failureTelemetry)) {
            throw new SourceAcquisitionException(
                'Yahoo Finance range acquisition failed for all requested tickers.',
                $this->dominantYahooFailureReasonCode($failureTelemetry),
                0,
                null,
                $aggregate
            );
        }

        if (empty($rows)) {
            $aggregate['source_acquisition_state'] = 'FAILED';
            $aggregate['source_final_status'] = 'FAILED';
            $aggregate['final_reason_code'] = 'RUN_SOURCE_NO_VALID_DATA';
            $aggregate['no_valid_data'] = true;
            $aggregate['empty_response_blocked'] = true;
            $aggregate['failure_reason_summary'] = ['RUN_SOURCE_NO_VALID_DATA' => 1];

            $this->rememberAcquisitionTelemetry($aggregate);

            throw new SourceAcquisitionException(
                'Yahoo Finance range source returned no valid EOD bars for requested trade_date set.',
                'RUN_SOURCE_NO_VALID_DATA',
                0,
                null,
                $aggregate
            );
        }

        return $rowsByDate;
    }

    private function buildYahooRangeAggregateTelemetry($startDate, $endDate, array $tradingDates, array $requestedTickerCodes, array $uniqueTickerCodes, array $rows, array $rowsByDate, array $requestTelemetry, array $failureTelemetry, array $apiConfig, array $context = [], $invalidOhlcvSkipped = 0)
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
        $returnedRowsByDate = [];
        foreach ($rowsByDate as $date => $dateRows) {
            $returnedRowsByDate[$date] = count($dateRows);
        }

        $state = empty($rows)
            ? (! empty($failureTelemetry) ? 'SYSTEMIC_FAILED' : 'FAILED')
            : (empty($failureTelemetry) && empty($missingTickerCodes) ? 'SUCCESS' : 'PARTIAL_SUCCESS');
        $failedTickerContexts = $this->failureContextsByTicker($failureTelemetry);
        $firstFailureContext = ! empty($failedTickerContexts) ? reset($failedTickerContexts) : [];
        $firstFailureHttpStatus = array_key_exists('http_status', $firstFailureContext)
            ? $firstFailureContext['http_status']
            : $lastHttpStatus;
        $firstFailureProviderSample = array_key_exists('provider_error_sample', $firstFailureContext)
            ? $firstFailureContext['provider_error_sample']
            : $lastResponseBodySample;
        $firstFailureSanitizedUrl = array_key_exists('sanitized_url', $firstFailureContext)
            ? $firstFailureContext['sanitized_url']
            : $lastUrl;

        return array_merge($context, [
            'source_mode' => 'api',
            'provider' => $this->providerName($apiConfig),
            'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
            'timeout_seconds' => $this->timeoutSeconds(),
            'retry_max' => $this->retryMax(),
            'attempt_count' => $attemptCount,
            'attempts' => $attempts,
            'success_after_retry' => $successAfterRetry,
            'retry_exhausted' => $retryExhausted,
            'final_reason_code' => $state === 'SUCCESS' ? null : (empty($failureReasonSummary) ? 'RUN_SOURCE_PARTIAL_RESPONSE' : $this->dominantYahooFailureReasonCode($failureTelemetry)),
            'final_http_status' => $lastHttpStatus,
            'http_status' => $firstFailureHttpStatus,
            'url' => $lastUrl,
            'sanitized_url' => $firstFailureSanitizedUrl,
            'response_body_sample' => $lastResponseBodySample,
            'provider_error_sample' => $firstFailureProviderSample,
            'failure_scope' => $firstFailureContext['failure_scope'] ?? null,
            'source_final_status' => $state === 'PARTIAL_SUCCESS' ? 'PARTIAL' : $state,
            'source_acquisition_state' => $state,
            'source_acquisition_mode' => 'range_window',
            'source_window_start' => $startDate,
            'source_window_end' => $endDate,
            'requested_ticker_count' => count($requestedTickerCodes),
            'unique_ticker_count' => count($uniqueTickerCodes),
            'expected_ticker_count' => count($uniqueTickerCodes),
            'success_ticker_count' => count($returnedTickerCodes),
            'failed_ticker_count' => count($missingTickerCodes),
            'missing_ticker_count' => count($missingTickerCodes),
            'failed_ticker_codes' => $missingTickerCodes,
            'missing_ticker_codes' => $missingTickerCodes,
            'failed_ticker_contexts' => $failedTickerContexts,
            'failures_sample' => array_slice(array_values($failedTickerContexts), 0, 10),
            'requested_trade_date_count' => count($tradingDates),
            'returned_trade_date_count' => count(array_filter($returnedRowsByDate)),
            'returned_rows_by_date' => $returnedRowsByDate,
            'returned_row_count' => count($rows),
            'accepted_row_count' => count($rows),
            'rejected_row_count' => (int) $invalidOhlcvSkipped,
            'invalid_row_count' => (int) $invalidOhlcvSkipped,
            'failure_reason_summary' => $failureReasonSummary,
        ]);
    }


    private function failureContextsByTicker(array $failureTelemetry)
    {
        $contexts = [];

        foreach ($failureTelemetry as $telemetry) {
            $tickerCode = strtoupper(trim((string) ($telemetry['ticker_code'] ?? '')));
            if ($tickerCode === '') {
                continue;
            }

            $contexts[$tickerCode] = [
                'ticker_code' => $tickerCode,
                'source_window_start' => $telemetry['source_window_start'] ?? null,
                'source_window_end' => $telemetry['source_window_end'] ?? null,
                'final_reason_code' => $telemetry['final_reason_code'] ?? null,
                'source_final_status' => $telemetry['source_final_status'] ?? 'FAILED',
                'final_http_status' => array_key_exists('final_http_status', $telemetry) ? $telemetry['final_http_status'] : ($telemetry['http_status'] ?? null),
                'http_status' => array_key_exists('http_status', $telemetry) ? $telemetry['http_status'] : ($telemetry['final_http_status'] ?? null),
                'error_sample' => $telemetry['error_sample'] ?? ($telemetry['provider_error_sample'] ?? ($telemetry['response_body_sample'] ?? null)),
                'provider_error_sample' => array_key_exists('provider_error_sample', $telemetry) ? $telemetry['provider_error_sample'] : ($telemetry['response_body_sample'] ?? null),
                'response_body_sample' => array_key_exists('response_body_sample', $telemetry) ? $telemetry['response_body_sample'] : ($telemetry['provider_error_sample'] ?? null),
                'sanitized_url' => $telemetry['sanitized_url'] ?? ($telemetry['url'] ?? null),
                'url' => $telemetry['url'] ?? ($telemetry['sanitized_url'] ?? null),
                'failure_scope' => $telemetry['failure_scope'] ?? 'ticker',
                'attempt_count' => $telemetry['attempt_count'] ?? null,
            ];
        }

        return $contexts;
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
        return in_array($reasonCode, ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT', 'RUN_SOURCE_BAD_REQUEST', 'RUN_SOURCE_INVALID_SYMBOL', 'RUN_SOURCE_NO_VALID_DATA'], true);
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

    private function buildYahooFinanceUrl($tradeDate, $tickerCode, array $apiConfig, $providerSymbol = null)
    {
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        $symbol = $providerSymbol !== null
            ? (string) $providerSymbol
            : $this->equityProviderSymbols->resolve($tickerCode, $apiConfig);
        $symbolSuffix = '';
        $range = (string) data_get($apiConfig, 'yahoo.range', '10d');
        $interval = (string) data_get($apiConfig, 'yahoo.interval', '1d');
        $periodBounds = $this->yahooPeriodBounds($tradeDate);

        if (strpos($urlTemplate, '{period1}') === false || strpos($urlTemplate, '{period2}') === false) {
            return $this->canonicalYahooChartUrl($symbol, $periodBounds['period1'], $periodBounds['period2'], $interval);
        }

        return str_replace(
            ['{date}', '{symbol}', '{symbols}', '{symbol_suffix}', '{range}', '{interval}', '{period1}', '{period2}'],
            [$tradeDate, $symbol, $symbol, $symbolSuffix, $range, $interval, $periodBounds['period1'], $periodBounds['period2']],
            $urlTemplate
        );
    }

    private function buildYahooFinanceRangeUrl($startDate, $endDate, $tickerCode, array $apiConfig, $providerSymbol = null)
    {
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        $symbol = $providerSymbol ?: $this->equityProviderSymbols->resolve($tickerCode, $apiConfig);
        $symbolSuffix = '';
        $range = (string) data_get($apiConfig, 'yahoo.range', '10d');
        $interval = (string) data_get($apiConfig, 'yahoo.interval', '1d');
        $periodBounds = $this->yahooRangePeriodBounds($startDate, $endDate);

        if (strpos($urlTemplate, '{period1}') === false || strpos($urlTemplate, '{period2}') === false) {
            return $this->canonicalYahooChartUrl($symbol, $periodBounds['period1'], $periodBounds['period2'], $interval);
        }

        return str_replace(
            ['{date}', '{symbol}', '{symbols}', '{symbol_suffix}', '{range}', '{interval}', '{period1}', '{period2}'],
            [$startDate, $symbol, $symbol, $symbolSuffix, $range, $interval, $periodBounds['period1'], $periodBounds['period2']],
            $urlTemplate
        );
    }

    private function canonicalYahooChartUrl($symbol, $period1, $period2, $interval)
    {
        return 'https://query1.finance.yahoo.com/v8/finance/chart/'
            .(string) $symbol
            .'?period1='.(string) $period1
            .'&period2='.(string) $period2
            .'&interval='.(string) $interval
            .'&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com';
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

    private function yahooRangePeriodBounds($startDate, $endDate)
    {
        $timezone = config('market_data.platform.timezone', 'Asia/Jakarta');
        $start = Carbon::parse($startDate, $timezone)->startOfDay();
        $endExclusive = Carbon::parse($endDate, $timezone)->addDay()->startOfDay();

        return [
            'period1' => (string) $start->timestamp,
            'period2' => (string) $endExclusive->timestamp,
        ];
    }
    
    private function parseYahooFinancePayloadRowsForCode($body, array $tradeDateSet, $code, $capturedAt, array $apiConfig, $sourceRowRefPrefix, $expectedProviderSymbol)
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
            if ($this->isYahooEmptyChartSeries($timestamps, $quote)) {
                throw new SourceAcquisitionException('Yahoo Finance chart payload contains no price series for requested range.', 'RUN_SOURCE_NO_VALID_DATA');
            }

            throw new SourceAcquisitionException('Yahoo Finance chart payload is missing timestamp/quote data.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $adjclose = data_get($result, 'indicators.adjclose.0.adjclose', []);
        $meta = is_array(data_get($result, 'meta')) ? data_get($result, 'meta') : [];
        $metaSymbol = strtoupper(trim((string) ($meta['symbol'] ?? '')));
        if ($metaSymbol !== '' && $metaSymbol !== strtoupper(trim((string) $expectedProviderSymbol))) {
            throw new SourceAcquisitionException(
                'Yahoo Finance response symbol does not match the effective provider mapping used by the request.',
                'RUN_SOURCE_RESPONSE_CHANGED',
                0,
                null,
                ['expected_provider_symbol' => $expectedProviderSymbol, 'response_provider_symbol' => $metaSymbol]
            );
        }
        $exchangeTimezone = trim((string) ($meta['exchangeTimezoneName'] ?? ''));
        $expectedTimezone = (string) config('market_data.platform.timezone', 'Asia/Jakarta');
        if ($exchangeTimezone === '' || $exchangeTimezone !== $expectedTimezone) {
            throw new SourceAcquisitionException(
                'Yahoo Finance response timezone is missing or inconsistent with the requested market boundary.',
                'RUN_SOURCE_RESPONSE_CHANGED',
                0,
                null,
                ['expected_timezone' => $expectedTimezone, 'response_timezone' => $exchangeTimezone ?: null]
            );
        }
        $rows = [];
        $invalidRows = [];
        $invalidOhlcvSkipped = 0;

        $expectedCardinality = count($timestamps);
        foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! isset($quote[$field]) || ! is_array($quote[$field]) || count($quote[$field]) !== $expectedCardinality) {
                throw new SourceAcquisitionException(
                    'Yahoo Finance quote arrays are missing or misaligned with timestamps.',
                    'RUN_SOURCE_RESPONSE_CHANGED',
                    0,
                    null,
                    ['misaligned_field' => $field, 'timestamp_count' => $expectedCardinality]
                );
            }
        }
        if ($adjclose !== [] && (! is_array($adjclose) || count($adjclose) !== $expectedCardinality)) {
            throw new SourceAcquisitionException(
                'Yahoo Finance adjusted-close evidence is misaligned with timestamps.',
                'RUN_SOURCE_RESPONSE_CHANGED'
            );
        }

        foreach (array_values($timestamps) as $position => $timestamp) {
            if (! is_numeric($timestamp)) {
                throw new SourceAcquisitionException(
                    'Yahoo Finance timestamp array contains a non-numeric value.',
                    'RUN_SOURCE_RESPONSE_CHANGED'
                );
            }

            $rowTradeDate = Carbon::createFromTimestampUTC((int) $timestamp)
                ->setTimezone($exchangeTimezone)
                ->toDateString();

            if (! isset($tradeDateSet[$rowTradeDate])) {
                continue;
            }

            $row = [
                'ticker_code' => $code,
                'trade_date' => $rowTradeDate,
                'open' => $quote['open'][$position] ?? null,
                'high' => $quote['high'][$position] ?? null,
                'low' => $quote['low'][$position] ?? null,
                'close' => $quote['close'][$position] ?? null,
                'volume' => $quote['volume'][$position] ?? null,
                // Provider adjusted close is evidence only. Missing data must stay null;
                // it is never repaired with close or promoted as canonical price truth.
                'adj_close' => $adjclose[$position] ?? null,
                'source_name' => isset($apiConfig['source_name']) ? $apiConfig['source_name'] : 'YAHOO_FINANCE',
                'source_row_ref' => $sourceRowRefPrefix.':'.$code.':'.$rowTradeDate,
                'captured_at' => $capturedAt,
            ];

            $validation = $this->validateYahooOhlcvRow($row);
            if (! $validation['valid']) {
                $invalidOhlcvSkipped++;
                $invalidRows[] = $row + [
                    'invalid_reason_code' => $validation['reason_code'],
                    'invalid_note' => $validation['note'],
                ];
                continue;
            }

            $rows[] = $row;
        }

        return [
            'rows' => $rows,
            'invalid_rows' => $invalidRows,
            'invalid_ohlcv_skipped' => $invalidOhlcvSkipped,
        ];
    }

    private function isYahooEmptyChartSeries($timestamps, $quote)
    {
        if (is_array($timestamps)) {
            foreach ($timestamps as $timestamp) {
                if (is_numeric($timestamp)) {
                    return false;
                }
            }
        }

        if (! is_array($quote)) {
            return false;
        }

        foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! array_key_exists($field, $quote)) {
                continue;
            }

            $values = is_array($quote[$field]) ? $quote[$field] : [$quote[$field]];
            foreach ($values as $value) {
                if ($value !== null && $value !== '') {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateYahooOhlcvRow(array $row)
    {
        foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! array_key_exists($field, $row) || $row[$field] === null || $row[$field] === '') {
                return ['valid' => false, 'reason_code' => 'BAR_MISSING_REQUIRED_FIELD', 'note' => 'Missing provider field: '.$field];
            }
            if (! is_numeric($row[$field])) {
                return ['valid' => false, 'reason_code' => 'SOURCE_PROVIDER_MALFORMED_RESPONSE', 'note' => 'Non-numeric provider field: '.$field];
            }
        }

        foreach (['open', 'high', 'low', 'close'] as $field) {
            if ((float) $row[$field] <= 0) {
                return ['valid' => false, 'reason_code' => 'BAR_NON_POSITIVE_PRICE', 'note' => 'Non-positive provider field: '.$field];
            }
        }
        if ((float) $row['volume'] < 0) {
            return ['valid' => false, 'reason_code' => 'BAR_NEGATIVE_VOLUME', 'note' => 'Provider volume is negative.'];
        }
        if ((float) $row['high'] < max((float) $row['open'], (float) $row['low'], (float) $row['close'])
            || (float) $row['low'] > min((float) $row['open'], (float) $row['high'], (float) $row['close'])) {
            return ['valid' => false, 'reason_code' => 'BAR_INVALID_OHLC_ORDER', 'note' => 'Provider OHLC values are internally inconsistent.'];
        }

        return ['valid' => true, 'reason_code' => null, 'note' => null];
    }

    private function normalizeBenchmarkRow(array $row, array $benchmark, $providerSymbol, $capturedAt, array $apiConfig)
    {
        return [
            'benchmark_code' => $this->normalizeTickerCode($benchmark['benchmark_code'] ?? $row['ticker_code']),
            'trade_date' => $row['trade_date'],
            'open' => $row['open'],
            'high' => $row['high'],
            'low' => $row['low'],
            'close' => $row['close'],
            'volume' => $row['volume'],
            'adj_close' => $row['adj_close'],
            'provider' => $this->providerName($apiConfig),
            'provider_symbol' => $providerSymbol,
            'source_name' => isset($apiConfig['source_name']) ? $apiConfig['source_name'] : 'YAHOO_FINANCE',
            'source_row_ref' => 'benchmark:'.$providerSymbol.':'.$row['trade_date'],
            'captured_at' => $capturedAt,
        ];
    }

    private function buildYahooBenchmarkAggregateTelemetry($tradeDate, array $benchmarks, array $rows, array $requestTelemetry, array $failureTelemetry, array $apiConfig)
    {
        $attempts = [];
        $attemptCount = 0;
        $lastHttpStatus = null;
        $lastUrl = null;
        $lastResponseBodySample = null;

        foreach ($requestTelemetry as $telemetry) {
            $attemptCount += (int) ($telemetry['attempt_count'] ?? 0);
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

        $returnedCodes = array_values(array_filter(array_unique(array_map(function ($row) {
            return isset($row['benchmark_code']) ? (string) $row['benchmark_code'] : '';
        }, $rows))));
        $requestedCodes = array_values(array_filter(array_unique(array_map(function ($benchmark) {
            $benchmark = (array) $benchmark;
            return isset($benchmark['benchmark_code']) ? (string) $benchmark['benchmark_code'] : null;
        }, $benchmarks))));
        $missingCodes = array_values(array_diff($requestedCodes, $returnedCodes));

        return [
            'source_mode' => 'api',
            'provider' => $this->providerName($apiConfig),
            'source_name' => strtoupper((string) data_get($apiConfig, 'source_name', config('market_data.source.default_source_name', 'API_FREE'))),
            'timeout_seconds' => $this->timeoutSeconds(),
            'retry_max' => $this->retryMax(),
            'attempt_count' => $attemptCount,
            'attempts' => $attempts,
            'final_reason_code' => empty($rows)
                ? (empty($failureTelemetry) ? 'RUN_SOURCE_NO_VALID_DATA' : $this->dominantYahooFailureReasonCode($failureTelemetry))
                : (empty($failureTelemetry) ? null : 'RUN_SOURCE_PARTIAL_RESPONSE'),
            'final_http_status' => $lastHttpStatus,
            'url' => $lastUrl,
            'response_body_sample' => $lastResponseBodySample,
            'source_final_status' => empty($rows) ? 'FAILED' : (empty($failureTelemetry) ? 'SUCCESS' : 'PARTIAL'),
            'trade_date' => $tradeDate,
            'requested_benchmark_count' => count($benchmarks),
            'returned_benchmark_count' => count($returnedCodes),
            'missing_benchmark_count' => count($missingCodes),
            'missing_benchmark_codes' => $missingCodes,
            'failed_benchmark_count' => count($failureTelemetry),
            'failure_reason_summary' => $this->summarizeYahooFailureReasons($failureTelemetry),
        ];
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

    public function capabilities()
    {
        return [
            'provider' => 'yahoo_finance',
            'phase' => 'bootstrap_free_source',
            'frequency' => 'EOD',
            'market_segment' => 'IDX_REGULAR',
            'provides_ohlcv' => true,
            'provides_provider_adjusted_close_as_evidence_only' => true,
            'provides_actual_traded_value' => false,
            'provides_official_board_or_trading_status' => false,
            'provides_authoritative_corporate_actions' => false,
            'supports_point_in_time_identity_without_internal_mapping' => false,
            'canonical_price_basis_candidates' => ['RAW', 'SPLIT_ADJUSTED'],
            'forbidden_canonical_basis' => ['PROVIDER_ADJ_CLOSE'],
        ];
    }

    private function rememberAcquisitionTelemetry(array $telemetry)
    {
        $this->lastAcquisitionTelemetry = $this->withResilienceAuditTelemetry($telemetry);
    }

    private function withResilienceAuditTelemetry(array $telemetry)
    {
        $sourceMode = strtolower(trim((string) ($telemetry['source_mode'] ?? 'api')));
        $isPrimaryApiSource = in_array($sourceMode, ['api', 'api_free'], true);
        $telemetry['source_priority'] = $telemetry['source_priority'] ?? ($isPrimaryApiSource ? 'PRIMARY' : 'SECONDARY_CONTROLLED_RECOVERY');
        $telemetry['active_source_decision'] = $telemetry['active_source_decision'] ?? ($isPrimaryApiSource ? 'api_free' : $sourceMode);
        $telemetry['retry_attempt_count'] = $this->retryAttemptCount($telemetry['attempts'] ?? []);
        $telemetry['failure_class_summary'] = $this->failureClassSummary($telemetry);

        return $telemetry;
    }

    private function retryAttemptCount($attempts)
    {
        if (! is_array($attempts)) {
            return 0;
        }

        $count = 0;
        foreach ($attempts as $attempt) {
            if (is_array($attempt) && (int) ($attempt['attempt_number'] ?? 0) > 1) {
                $count++;
            }
        }

        return $count;
    }

    private function failureClassSummary(array $telemetry)
    {
        $reasonSummary = isset($telemetry['failure_reason_summary']) && is_array($telemetry['failure_reason_summary'])
            ? $telemetry['failure_reason_summary']
            : [];

        if ($reasonSummary === [] && isset($telemetry['attempts']) && is_array($telemetry['attempts'])) {
            foreach ($telemetry['attempts'] as $attempt) {
                if (! is_array($attempt)) {
                    continue;
                }
                $attemptReasonCode = trim((string) ($attempt['reason_code'] ?? ''));
                if ($attemptReasonCode === '') {
                    continue;
                }
                $reasonSummary[$attemptReasonCode] = ($reasonSummary[$attemptReasonCode] ?? 0) + 1;
            }
        }

        if ($reasonSummary === [] && ! empty($telemetry['final_reason_code'])) {
            $reasonSummary[(string) $telemetry['final_reason_code']] = 1;
        }

        $summary = [];
        foreach ($reasonSummary as $reasonCode => $count) {
            if ($reasonCode === null || trim((string) $reasonCode) === '') {
                continue;
            }
            $class = in_array((string) $reasonCode, ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT'], true)
                ? 'TRANSIENT'
                : 'NON_TRANSIENT';
            $summary[$class] = ($summary[$class] ?? 0) + max(0, (int) $count);
        }

        ksort($summary);
        return $summary;
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
            $capture = null;

            try {
                $response = $this->performHttpRequest($url);
                $status = (int) $response['status'];
                $contentType = $this->extractContentType($response['headers'] ?? []);
                $responseBodySample = $this->responseBodySample($response['body']);
                $capture = $this->persistCapture($this->observationEnvelope(
                    $url,
                    $requestContext,
                    $response['body'],
                    $status,
                    $contentType,
                    $capturedAt,
                    $attemptNumber
                ));

                if (in_array($status, [401, 403], true)) {
                    throw new SourceAcquisitionException('Source API authentication/config failed with HTTP '.$status.'.', 'RUN_SOURCE_AUTH_ERROR', 0, null, [
                        'final_http_status' => $status,
                        'response_body_sample' => $responseBodySample,
                    'provider_error_sample' => $responseBodySample,
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
                    $reasonCode = $this->classifyHttpFailure($status, $responseBodySample, $requestContext);
                    throw new SourceAcquisitionException($this->httpFailureMessage($status, $reasonCode), $reasonCode, 0, null, [
                        'final_http_status' => $status,
                        'http_status' => $status,
                        'response_body_sample' => $responseBodySample,
                        'provider_error_sample' => $responseBodySample,
                    ]);
                }

                $this->assertCompatibleContentType($contentType);

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
                    'url' => $this->sanitizeUrl($url),
                    'sanitized_url' => $this->sanitizeUrl($url),
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
                    'http_status' => $status,
                    'response_body_sample' => $responseBodySample,
                    'provider_error_sample' => $responseBodySample,
                    'failure_scope' => null,
                    'captured_at' => $capturedAt,
                ]);

                return [
                    'body' => $response['body'],
                    'captured_at' => $capturedAt,
                    'content_type' => $contentType,
                    'observation_capture' => $capture,
                ];
            } catch (SourceAcquisitionException $e) {
                if (is_array($capture)) {
                    $this->persistOutcome($capture, 'REJECTED', $e->reasonCode());
                    $capture = null;
                }
                $willRetry = $this->shouldRetry($e->reasonCode(), $attempt, $retryMax);
                $backoffDelayMs = $willRetry ? $this->backoff($attempt, $baseBackoffMs) : 0;

                $exceptionContext = $e->context();
                $httpStatus = $exceptionContext['final_http_status'] ?? $this->extractStatusFromExceptionContext($e);
                if ($httpStatus === 0) {
                    $httpStatus = null;
                }
                $responseBodySample = $exceptionContext['response_body_sample'] ?? null;
                $errorSample = $this->sanitizeErrorSample($exceptionContext['error_sample'] ?? ($responseBodySample ?? $e->getMessage()));
                $providerErrorSample = array_key_exists('provider_error_sample', $exceptionContext)
                    ? $exceptionContext['provider_error_sample']
                    : $responseBodySample;

                $attemptLog[] = [
                    'attempt_number' => $attemptNumber,
                    'reason_code' => $e->reasonCode(),
                    'http_status' => $httpStatus,
                    'throttle_delay_ms' => $throttleDelayMs,
                    'backoff_delay_ms' => $backoffDelayMs,
                    'will_retry' => $willRetry,
                ];

                $failureContext = $requestContext + [
                    'url' => $this->sanitizeUrl($url),
                    'sanitized_url' => $this->sanitizeUrl($url),
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
                    'http_status' => $httpStatus,
                    'response_body_sample' => $responseBodySample,
                    'provider_error_sample' => $providerErrorSample,
                    'error_sample' => $errorSample,
                    'failure_scope' => $this->failureScopeForContext($requestContext, $e->reasonCode()),
                    'captured_at' => $capturedAt,
                ];

                $this->rememberAcquisitionTelemetry($failureContext);

                $lastException = $e->withContext($failureContext);

                if (! $willRetry) {
                    throw $lastException;
                }
            } catch (\Throwable $e) {
                $reasonCode = 'RUN_SOURCE_TIMEOUT';
                $willRetry = $this->shouldRetry($reasonCode, $attempt, $retryMax);
                $backoffDelayMs = $willRetry ? $this->backoff($attempt, $baseBackoffMs) : 0;
                $errorSample = $this->sanitizeErrorSample($e->getMessage() !== '' ? $e->getMessage() : get_class($e));

                $attemptLog[] = [
                    'attempt_number' => $attemptNumber,
                    'reason_code' => $reasonCode,
                    'http_status' => null,
                    'throttle_delay_ms' => $throttleDelayMs,
                    'backoff_delay_ms' => $backoffDelayMs,
                    'will_retry' => $willRetry,
                ];

                $failureContext = $requestContext + [
                    'url' => $this->sanitizeUrl($url),
                    'sanitized_url' => $this->sanitizeUrl($url),
                    'provider' => $provider,
                    'source_name' => $sourceName,
                    'timeout_seconds' => $timeoutSeconds,
                    'retry_max' => $retryMax,
                    'attempt_count' => count($attemptLog),
                    'attempts' => $attemptLog,
                    'success_after_retry' => false,
                    'retry_exhausted' => ! $willRetry,
                    'final_reason_code' => $reasonCode,
                    'final_http_status' => null,
                    'http_status' => null,
                    'response_body_sample' => null,
                    'provider_error_sample' => null,
                    'error_sample' => $errorSample,
                    'failure_scope' => $this->failureScopeForContext($requestContext, $reasonCode),
                    'captured_at' => $capturedAt,
                ];

                $this->observations->recordTransportFailure(
                    $this->observationEnvelope($url, $requestContext, null, null, null, $capturedAt, $attemptNumber),
                    $reasonCode
                );

                $this->rememberAcquisitionTelemetry($failureContext);

                $lastException = new SourceAcquisitionException(
                    'Source API request failed before an HTTP response: '.$errorSample,
                    $reasonCode,
                    0,
                    $e,
                    $failureContext
                );

                if (! $willRetry) {
                    throw $lastException;
                }
            }
        }

        throw $lastException ?: new SourceAcquisitionException('Unknown source API acquisition failure.', 'RUN_SOURCE_TIMEOUT');
    }

    private function acceptResponseRows(array $response, array $rows, array $rejectedRows = [])
    {
        $capture = $response['observation_capture'] ?? null;
        if (! is_array($capture)) {
            throw new SourceAcquisitionException(
                'Raw provider response was not captured before normalization.',
                'SOURCE_OBSERVATION_CAPTURE_REQUIRED'
            );
        }

        $outcome = $this->persistAcceptedRows($capture, $rows, $rejectedRows);

        return array_map(function (array $row) use ($capture, $outcome) {
            return array_merge($row, [
                'source_observation_id' => $outcome['source_observation_id'],
                'source_capture_observation_id' => $capture['source_observation_id'],
                'source_payload_hash' => $capture['payload_hash'] ?? null,
                'source_schema_fingerprint' => $capture['schema_fingerprint'] ?? null,
                'source_observation_persisted' => ! empty($outcome['persisted']),
            ]);
        }, $rows);
    }

    private function rejectResponse(array $response, $reasonCode)
    {
        $capture = $response['observation_capture'] ?? null;
        if (is_array($capture)) {
            $this->persistOutcome($capture, 'REJECTED', $reasonCode);
        }
    }

    private function observationEnvelope($url, array $context, $payload, $status, $contentType, $capturedAt, $attemptNumber)
    {
        $batchId = $context['source_acquisition_batch_id'] ?? null;
        $checkpointId = $context['acquisition_checkpoint_id'] ?? null;
        if ($checkpointId === null && $batchId !== null) {
            $checkpointId = hash('sha256', implode('|', [
                $batchId,
                $context['source_window_start'] ?? ($context['trade_date'] ?? ''),
                $context['source_window_end'] ?? ($context['trade_date'] ?? ''),
                $context['provider_symbol'] ?? ($context['ticker_code'] ?? ''),
            ]));
        }

        return array_merge($context, [
            'attempt_uid' => hash('sha256', $this->sanitizeUrl($url).'|'.$capturedAt.'|'.$attemptNumber),
            'acquisition_batch_id' => $batchId,
            'acquisition_checkpoint_id' => $checkpointId,
            'requested_trade_date' => $context['trade_date'] ?? ($context['requested_end'] ?? ($context['source_window_end'] ?? null)),
            'requested_start_date' => $context['requested_start'] ?? ($context['source_window_start'] ?? null),
            'requested_end_date' => $context['requested_end'] ?? ($context['source_window_end'] ?? null),
            'source_mode' => $context['source_mode'] ?? 'api_free',
            'source_name' => strtoupper((string) data_get(config('market_data.source.api'), 'source_name', 'YAHOO_FINANCE')),
            'provider' => $this->providerName(config('market_data.source.api')),
            'sanitized_request_identity' => $this->sanitizeUrl($url),
            'response_status' => $status,
            'content_type' => $contentType,
            'acquired_at' => $capturedAt,
            'adapter_version' => (string) config('market_data.source.api.adapter_version', 'public_api_eod_v1'),
            'provider_schema_version' => (string) config('market_data.source.api.schema_version', 'provider_schema_observed_v1'),
            'payload' => $payload,
        ]);
    }

    private function extractContentType(array $headers)
    {
        foreach ($headers as $name => $value) {
            if (is_string($name) && strtolower($name) === 'content-type') {
                return trim(is_array($value) ? implode(';', $value) : (string) $value);
            }

            if (is_int($name) && stripos((string) $value, 'Content-Type:') === 0) {
                return trim(substr((string) $value, strlen('Content-Type:')));
            }
        }

        return null;
    }

    private function persistCapture(array $envelope)
    {
        try {
            return $this->observations->capture($envelope);
        } catch (SourceAcquisitionException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Raw source response could not be persisted before validation.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }

    private function persistOutcome(array $capture, $state, $reasonCode = null)
    {
        try {
            return $this->observations->recordOutcome($capture, $state, $reasonCode);
        } catch (SourceAcquisitionException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Source observation outcome could not be persisted immutably.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }

    private function rejectResponseRows(array $response, array $rejectedRows, $reasonCode)
    {
        $capture = $response['observation_capture'] ?? null;
        if (! is_array($capture)) {
            throw new SourceAcquisitionException(
                'Rejected provider rows cannot be persisted without their raw observation capture.',
                'SOURCE_OBSERVATION_CAPTURE_REQUIRED'
            );
        }

        try {
            $this->observations->recordRejectedRows($capture, $rejectedRows, $reasonCode);
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Rejected source rows could not be persisted as immutable evidence.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }

    private function persistAcceptedRows(array $capture, array $rows, array $rejectedRows = [])
    {
        try {
            return $this->observations->recordAcceptedRows($capture, $rows, $rejectedRows);
        } catch (SourceAcquisitionException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SourceAcquisitionException(
                'Normalized source observation rows could not be persisted and compared immutably.',
                'SOURCE_OBSERVATION_PERSISTENCE_FAILED',
                0,
                $e
            );
        }
    }

    private function assertCompatibleContentType($contentType)
    {
        $normalized = strtolower(trim((string) $contentType));
        $format = strtolower((string) config('market_data.source.api.response_format', 'json'));
        $compatible = $format === 'csv'
            ? preg_match('/^(text|application)\/(?:[^;]+\+)?csv(?:\s*;|$)/', $normalized) === 1
            : preg_match('/^(?:application|text)\/(?:[^;]+\+)?json(?:\s*;|$)/', $normalized) === 1;

        if ($normalized === '' || ! $compatible) {
            throw new SourceAcquisitionException(
                'Source API response content type is missing or incompatible with the active parser.',
                'RUN_SOURCE_RESPONSE_CHANGED',
                0,
                null,
                ['content_type' => $normalized === '' ? null : $normalized]
            );
        }
    }

    /**
     * Schema-version changes are executable adapter changes, not operator-tunable labels. The
     * active Yahoo adapter therefore refuses a version pair that this implementation was not
     * released to parse; changing either side requires a code-reviewed successor contract.
     */
    private function assertActiveSchemaContract(array $apiConfig)
    {
        if ($this->providerName($apiConfig) !== 'yahoo_finance') {
            return;
        }

        $adapterVersion = trim((string) ($apiConfig['adapter_version'] ?? self::YAHOO_ADAPTER_VERSION));
        $schemaVersion = trim((string) ($apiConfig['schema_version'] ?? self::YAHOO_SCHEMA_VERSION));
        if ($adapterVersion !== self::YAHOO_ADAPTER_VERSION || $schemaVersion !== self::YAHOO_SCHEMA_VERSION) {
            throw new SourceAcquisitionException(
                'Active Yahoo schema/version pair is not supported by this adapter implementation.',
                'RUN_SOURCE_RESPONSE_CHANGED',
                0,
                null,
                [
                    'active_adapter_version' => $adapterVersion,
                    'active_schema_version' => $schemaVersion,
                    'supported_adapter_version' => self::YAHOO_ADAPTER_VERSION,
                    'supported_schema_version' => self::YAHOO_SCHEMA_VERSION,
                ]
            );
        }
    }


    private function classifyHttpFailure($status, $responseBodySample, array $requestContext = [])
    {
        $status = (int) $status;
        $body = strtolower((string) $responseBodySample);

        if ($status === 400) {
            if (strpos($body, 'invalid symbol') !== false || strpos($body, 'not found') !== false) {
                return 'RUN_SOURCE_INVALID_SYMBOL';
            }

            if (strpos($body, 'period') !== false || strpos($body, 'range') !== false || strpos($body, 'parameter') !== false) {
                return 'RUN_SOURCE_PROVIDER_REJECTED_RANGE';
            }

            return 'RUN_SOURCE_BAD_REQUEST';
        }

        if ($status === 404) {
            return 'RUN_SOURCE_INVALID_SYMBOL';
        }

        if (in_array($status, [409, 422], true)) {
            return 'RUN_SOURCE_PROVIDER_REJECTED_RANGE';
        }

        return 'RUN_SOURCE_MALFORMED_PAYLOAD';
    }

    private function httpFailureMessage($status, $reasonCode)
    {
        if ($reasonCode === 'RUN_SOURCE_BAD_REQUEST') {
            return 'Source API returned bad request HTTP '.$status.'.';
        }

        if ($reasonCode === 'RUN_SOURCE_INVALID_SYMBOL') {
            return 'Source API rejected ticker/symbol with HTTP '.$status.'.';
        }

        if ($reasonCode === 'RUN_SOURCE_PROVIDER_REJECTED_RANGE') {
            return 'Source API rejected requested range with HTTP '.$status.'.';
        }

        return 'Source API returned unexpected HTTP status '.$status.'.';
    }

    private function failureScopeForContext(array $requestContext, $reasonCode)
    {
        if (isset($requestContext['ticker_code']) && (string) $requestContext['ticker_code'] !== '') {
            return 'ticker';
        }

        if (isset($requestContext['source_window_start']) || isset($requestContext['source_window_end'])) {
            return 'window';
        }

        return in_array($reasonCode, ['RUN_SOURCE_RESPONSE_CHANGED', 'RUN_SOURCE_MALFORMED_PAYLOAD', 'RUN_SOURCE_PROVIDER_REJECTED_RANGE'], true)
            ? 'systemic'
            : 'request';
    }

    private function sanitizeUrl($url)
    {
        $url = (string) $url;
        if ($url === '') {
            return $url;
        }

        return preg_replace('/([?&](?:token|apikey|api_key|auth|authorization|signature|sig)=)[^&]+/i', '$1[redacted]', $url);
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
        $context = $e->context();
        if (isset($context['final_http_status'])) {
            return (int) $context['final_http_status'];
        }
        if (isset($context['http_status'])) {
            return (int) $context['http_status'];
        }

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
        // The locked resilience contract makes the registered config value the effective retry
        // budget. A hidden implementation clamp would create a second, unregistered policy.
        return max(0, (int) config('market_data.provider.api_retry_max'));
    }

    private function responseBodySample($body)
    {
        $sample = substr((string) $body, 0, 1000);

        return str_replace(["\r", "\n", "\0"], [' ', ' ', ''], $sample);
    }

    private function sanitizeErrorSample($message)
    {
        $sample = $this->sanitizeUrl(str_replace(["\r", "\n", "\0"], [' ', ' ', ''], (string) $message));
        $sample = preg_replace('/\b(token|apikey|api_key|auth|authorization|signature|sig)=\S+/i', '$1=[redacted]', $sample);

        return substr($sample, 0, 1000);
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

    /**
     * Returns true once the observed failure ratio strictly crosses the configured breaker
     * threshold, or false while acquisition may continue. The breaker is transport protection,
     * not a reason-code source: terminal reason remains the registered underlying source failure.
     */
    private function openCircuitBreaker($failureCount, $universeCount, $successCount)
    {
        $threshold = (float) config('market_data.provider.circuit_breaker_error_rate', 0.5);
        if ($threshold <= 0 || $threshold >= 1) {
            throw new SourceAcquisitionException(
                'Configured source circuit-breaker error rate must be greater than 0 and less than 1.',
                'CONFIG_INVALID'
            );
        }

        if ($universeCount <= 0) {
            return false;
        }

        // The locked contract is partial-tolerant and defines the breaker against the run's
        // acquisition universe. Using attempts-so-far would make the first failed unit a 100%
        // failure rate and silently reintroduce an effective one-sample breaker. The only
        // threshold remains the registered configured ratio, applied strictly to failed planned
        // acquisition units over the planned universe.
        return ($failureCount / $universeCount) > $threshold;
    }

    private function circuitBreakerTelemetry(array $failureTelemetry, $universeCount, $successCount)
    {
        $failureCount = count($failureTelemetry);
        if (! $this->openCircuitBreaker($failureCount, $universeCount, $successCount)) {
            return null;
        }

        $attempted = $failureCount + $successCount;

        return [
            'circuit_breaker_open' => true,
            'source_protection_state' => 'CIRCUIT_OPEN',
            'circuit_breaker_threshold' => (float) config('market_data.provider.circuit_breaker_error_rate', 0.5),
            'circuit_breaker_failure_count' => $failureCount,
            'circuit_breaker_success_count' => $successCount,
            // Generic unit counts are the canonical audit-facing representation because this
            // helper protects both ticker and benchmark fanout. The ticker aliases are retained
            // for existing acquisition diagnostics that already consume them.
            'attempted_acquisition_unit_count' => $attempted,
            'unattempted_acquisition_unit_count' => max(0, $universeCount - $attempted),
            'attempted_ticker_count' => $attempted,
            'unattempted_ticker_count' => max(0, $universeCount - $attempted),
            'circuit_breaker_trigger_reason_code' => $this->dominantYahooFailureReasonCode($failureTelemetry),
        ];
    }

    private function normalizeTickerCode($value)
    {
        if ($value === null) {
            return null;
        }

        return Str::upper(trim((string) $value));
    }

    private function defaultObservationRecorder()
    {
        try {
            if (Schema::hasTable('md_source_observations')) {
                return new SourceObservationRepository();
            }
        } catch (\Throwable $e) {
            // Isolated adapter tests intentionally have no persistence foundation.
        }

        return new InMemorySourceObservationRecorder();
    }

    private function resolveEquityIdentity($tickerCode, array $apiConfig, $tradeDate, array $context)
    {
        try {
            return $this->equityProviderSymbols->resolveContext($tickerCode, $apiConfig, $tradeDate, $context);
        } catch (\RuntimeException $e) {
            $reasonCode = strpos($e->getMessage(), 'PROVIDER_SYMBOL_MAPPING_AMBIGUOUS') === 0
                ? 'PROVIDER_SYMBOL_MAPPING_AMBIGUOUS'
                : 'PROVIDER_SYMBOL_MAPPING_MISSING';

            throw new SourceAcquisitionException($e->getMessage(), $reasonCode, 0, $e, [
                'trade_date' => $tradeDate,
                'ticker_code' => $tickerCode,
                'provider' => $this->providerName($apiConfig),
                'source_final_status' => 'FAILED',
                'final_reason_code' => $reasonCode,
                'failure_scope' => 'TICKER',
            ]);
        }
    }
}
