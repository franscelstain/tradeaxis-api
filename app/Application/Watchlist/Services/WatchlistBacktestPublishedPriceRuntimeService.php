<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;

class WatchlistBacktestPublishedPriceRuntimeService
{
    private WatchlistBacktestStrategyService $strategy;
    private MarketDataTradingCalendarReadService $calendar;
    private MarketDataPublishedEodSeriesReadService $priceSeries;
    private WatchlistBacktestRuntimeArtifactService $artifacts;

    public function __construct(
        WatchlistBacktestStrategyService $strategy = null,
        MarketDataTradingCalendarReadService $calendar = null,
        MarketDataPublishedEodSeriesReadService $priceSeries = null,
        WatchlistBacktestRuntimeArtifactService $artifacts = null
    ) {
        $this->strategy = $strategy ?: new WatchlistBacktestStrategyService();
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->priceSeries = $priceSeries ?: new MarketDataPublishedEodSeriesReadService();
        $this->artifacts = $artifacts ?: new WatchlistBacktestRuntimeArtifactService();
    }

    public function execute(
        string $fromDate,
        string $toDate,
        string $outputPath,
        array $options = []
    ): array {
        $calendar = $this->calendar->resolveReplayWindow($fromDate, $toDate, 5);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $calendar['diagnostics'] ?? []);
        }

        $runtimeParamset = $this->runtimeParamset(
            is_array($options['paramset'] ?? null) ? $options['paramset'] : []
        );
        $backtestPayload = $this->strategy->backtestForReplayWindow(
            $calendar['trade_dates'],
            is_array($options['confirm_inputs_by_trade_date'] ?? null) ? $options['confirm_inputs_by_trade_date'] : [],
            $runtimeParamset,
            is_array($options['capital_input'] ?? null) ? $options['capital_input'] : []
        );
        $frozenStrategyHash = $this->stableHash($backtestPayload);

        if (! ($backtestPayload['is_ready'] ?? false)) {
            return $this->blocked(
                $backtestPayload['reason_code'] ?? 'WATCHLIST_BACKTEST_SOURCE_PLAN_NOT_READY',
                $backtestPayload['diagnostics'] ?? [],
                [
                    'calendar' => $calendar,
                    'backtest_payload' => $backtestPayload,
                    'strategy_payload_hash' => $frozenStrategyHash,
                ]
            );
        }

        $tickerCodes = $this->tickerCodes($backtestPayload['trades'] ?? []);
        $requiredPriceDates = $this->requiredPriceDates(
            $calendar['trade_dates'],
            $calendar['calendar_dates'],
            5
        );

        $priceFromDate = $requiredPriceDates[0] ?? $fromDate;
        $priceToDate = $requiredPriceDates[count($requiredPriceDates) - 1] ?? $toDate;
        $priceRead = $tickerCodes === []
            ? $this->emptyPriceRead($priceFromDate, $priceToDate)
            : $this->priceSeries->readPublishedSeries($priceFromDate, $priceToDate, $tickerCodes, $requiredPriceDates);

        if (! ($priceRead['is_ready'] ?? false)) {
            return $this->blocked(
                $priceRead['reason_code'] ?? 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
                array_merge($backtestPayload['diagnostics'] ?? [], $priceRead['diagnostics'] ?? []),
                [
                    'calendar' => $calendar,
                    'backtest_payload' => $backtestPayload,
                    'price_read' => $priceRead,
                    'strategy_payload_hash' => $frozenStrategyHash,
                ]
            );
        }

        $executedAt = isset($options['executed_at']) ? (string) $options['executed_at'] : date(DATE_ATOM);
        $artifact = $this->artifacts->buildArtifact(
            $backtestPayload,
            $priceRead['series_by_ticker'] ?? [],
            $calendar['calendar_dates'] ?? [],
            [
                'generated_at' => $executedAt,
                'runtime_context' => [
                    'calendar_manifest' => [
                        'requested_from_date' => $fromDate,
                        'requested_to_date' => $toDate,
                        'resolved_trade_dates' => $calendar['trade_dates'],
                        'calendar_dates' => $calendar['calendar_dates'],
                        'calendar_source' => $calendar['calendar_source'],
                        'calendar_sources' => $calendar['calendar_sources'],
                        'calendar_hash' => $calendar['calendar_hash'],
                        'coverage' => $calendar['coverage'],
                    ],
                    'price_series_manifest' => $priceRead['price_series_manifest'] ?? [],
                    'publication_manifest' => $priceRead['publication_manifest'] ?? [],
                    'runtime_execution' => [
                        'executed_at' => $executedAt,
                        'output_path' => $outputPath,
                        'trade_candidates_frozen_before_price_read' => true,
                        'future_price_used_for_evaluation_only' => true,
                        'strategy_payload_hash' => $frozenStrategyHash,
                        'strategy_payload_hash_after_price_read' => $this->stableHash($backtestPayload),
                        'strategy_payload_immutable' => $frozenStrategyHash === $this->stableHash($backtestPayload),
                        'ticker_count' => count($tickerCodes),
                        'required_price_date_count' => count($requiredPriceDates),
                        'trade_candidate_count' => count($backtestPayload['trades'] ?? []),
                    ],
                ],
            ]
        );

        if (! ($artifact['is_ready'] ?? false)) {
            return $this->blocked(
                $artifact['reason_code'] ?? 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_NOT_READY',
                $artifact['diagnostics'] ?? [],
                [
                    'calendar' => $calendar,
                    'backtest_payload' => $backtestPayload,
                    'price_read' => $priceRead,
                    'artifact' => $artifact,
                    'strategy_payload_hash' => $frozenStrategyHash,
                ]
            );
        }

        $metricSufficiency = is_array($artifact['metrics']['metric_sufficiency'] ?? null)
            ? $artifact['metrics']['metric_sufficiency']
            : [];
        if (! ($metricSufficiency['thresholds_resolved'] ?? false)) {
            return $this->blocked('WS_BT_EVAL_METRICS_MISSING', [[
                'trade_date' => null,
                'reason_code' => 'WS_BT_EVAL_METRICS_MISSING',
                'message' => 'Canonical eval thresholds are unresolved in paramset_snapshot.eval; runtime artifact export is blocked.',
                'missing_thresholds' => $metricSufficiency['missing_thresholds'] ?? [],
                'fatal' => true,
            ]], [
                'calendar' => $calendar,
                'backtest_payload' => $backtestPayload,
                'price_read' => $priceRead,
                'artifact' => $artifact,
                'strategy_payload_hash' => $frozenStrategyHash,
            ]);
        }

        if (is_file($outputPath)) {
            if (! ($options['overwrite'] ?? false)) {
                return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', [[
                    'trade_date' => null,
                    'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID',
                    'message' => 'Output file already exists. Use --overwrite to replace it explicitly.',
                    'fatal' => true,
                ]], [
                    'calendar' => $calendar,
                    'backtest_payload' => $backtestPayload,
                    'price_read' => $priceRead,
                    'artifact' => $artifact,
                    'strategy_payload_hash' => $frozenStrategyHash,
                ]);
            }

            if (! @unlink($outputPath)) {
                return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', [[
                    'trade_date' => null,
                    'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                    'message' => 'Existing output file could not be removed for explicit overwrite.',
                    'fatal' => true,
                ]]);
            }
        }

        $write = $this->artifacts->writeJsonArtifact($artifact, $outputPath);
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', [[
                'trade_date' => null,
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => 'Deterministic runtime artifact export failed.',
                'fatal' => true,
            ]], [
                'calendar' => $calendar,
                'backtest_payload' => $backtestPayload,
                'price_read' => $priceRead,
                'artifact' => $artifact,
                'write' => $write,
                'strategy_payload_hash' => $frozenStrategyHash,
            ]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => $artifact['reason_code'],
            'calendar' => $calendar,
            'backtest_payload' => $backtestPayload,
            'price_read' => $priceRead,
            'artifact' => $artifact,
            'write' => $write,
            'strategy_payload_hash' => $frozenStrategyHash,
            'artifact_hash' => $artifact['validation']['artifact_hash'] ?? null,
            'metrics_ready' => (bool) ($artifact['metrics']['ready'] ?? false),
            'metric_sufficiency_available' => (bool) ($artifact['metrics']['metric_sufficiency']['required_fields_available'] ?? false),
            'metric_thresholds_resolved' => (bool) ($artifact['metrics']['metric_sufficiency']['thresholds_resolved'] ?? false),
            'metric_calibration_valid' => (bool) ($artifact['metrics']['metric_sufficiency']['calibration_valid'] ?? false),
            'metric_gating_thresholds' => $artifact['metrics']['metric_sufficiency']['gating_thresholds'] ?? [],
            'metric_coverage_threshold_rule' => $artifact['metrics']['metric_sufficiency']['coverage_threshold_rule'] ?? null,
            'evaluated_trade_count' => (int) ($artifact['metrics']['counts']['total_evaluated_trades'] ?? 0),
            'diagnostic_count' => count($artifact['diagnostics'] ?? []),
            'production_ready' => false,
        ];
    }

    private function runtimeParamset(array $paramset): array
    {
        $defaults = WatchlistBacktestStrategyService::defaultParamset();
        $resolved = array_replace_recursive($defaults, $paramset);
        $backtest = is_array($resolved['backtest'] ?? null) ? $resolved['backtest'] : [];
        $resolved['backtest'] = array_replace_recursive($backtest, [
            'engine_mode' => 'PLAN_RECOMMENDATION_PUBLISHED_PRICE_REPLAY',
            'pricing_model' => 'PUBLISHED_EOD_OHLC_EXACT_DATE',
            'holding_days' => 5,
            'tradable_bar_rule' => 'POSITIVE_VOLUME_REQUIRED',
            'min_tradable_volume' => 1,
        ]);

        return $resolved;
    }

    private function requiredPriceDates(array $replayDates, array $calendarDates, int $holdingDays): array
    {
        $required = [];
        $calendarIndex = array_flip($calendarDates);

        foreach ($replayDates as $tradeDate) {
            if (! isset($calendarIndex[$tradeDate])) {
                continue;
            }
            $start = (int) $calendarIndex[$tradeDate] + 1;
            for ($offset = 0; $offset < $holdingDays; $offset++) {
                if (isset($calendarDates[$start + $offset])) {
                    $required[$calendarDates[$start + $offset]] = $calendarDates[$start + $offset];
                }
            }
        }

        ksort($required, SORT_STRING);

        return array_values($required);
    }

    private function tickerCodes(array $trades): array
    {
        $codes = [];
        foreach ($trades as $trade) {
            $code = strtoupper(trim((string) ($trade['ticker'] ?? $trade['ticker_code'] ?? '')));
            if ($code !== '') {
                $codes[$code] = $code;
            }
        }
        ksort($codes, SORT_STRING);

        return array_values($codes);
    }

    private function emptyPriceRead(string $fromDate, string $toDate): array
    {
        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED',
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'requested_trade_dates' => [],
            'requested_tickers' => [],
            'series_by_ticker' => [],
            'publication_manifest' => [],
            'price_series_manifest' => [
                'ticker_count' => 0,
                'required_price_date_count' => 0,
                'resolved_publication_date_count' => 0,
                'resolved_price_date_count' => 0,
                'resolved_price_row_count' => 0,
                'missing_publication_dates' => [],
                'missing_price_dates' => [],
                'missing_price_rows' => [],
                'source_payload_hash' => $this->stableHash([]),
                'exact_date_resolution_only' => true,
                'no_latest_fallback' => true,
                'no_max_trade_date' => true,
            ],
            'diagnostics' => [],
        ];
    }

    private function blocked(string $reasonCode, array $diagnostics, array $context = []): array
    {
        return array_merge([
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'diagnostics' => $diagnostics,
            'artifact_hash' => null,
            'metrics_ready' => false,
            'evaluated_trade_count' => 0,
            'diagnostic_count' => count($diagnostics),
            'production_ready' => false,
        ], $context);
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalizeForHash($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalizeForHash($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($this->isList($value)) {
            return array_map(function ($item) {
                return $this->normalizeForHash($item);
            }, $value);
        }

        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeForHash($item);
        }

        return $value;
    }

    private function isList(array $value): bool
    {
        $expected = 0;
        foreach (array_keys($value) as $key) {
            if ($key !== $expected) {
                return false;
            }
            $expected++;
        }

        return true;
    }
}
