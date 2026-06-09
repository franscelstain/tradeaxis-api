<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketDataPublishedEodSeriesReadRepository;

class MarketDataPublishedEodSeriesReadService
{
    private MarketDataReadinessService $readiness;
    private MarketDataTradingCalendarReadService $calendar;
    private MarketDataPublishedEodSeriesReadRepository $bars;

    public function __construct(
        MarketDataReadinessService $readiness = null,
        MarketDataTradingCalendarReadService $calendar = null,
        MarketDataPublishedEodSeriesReadRepository $bars = null
    ) {
        $this->readiness = $readiness ?: new MarketDataReadinessService();
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->bars = $bars ?: new MarketDataPublishedEodSeriesReadRepository();
    }

    public function readPublishedSeries(
        string $fromDate,
        string $toDate,
        array $tickerCodes,
        array $exactTradeDates = []
    ): array {
        $requestedCodes = $this->normalizeTickerCodes($tickerCodes);
        $tradeDates = $this->normalizeTradeDates($exactTradeDates);

        if (! $this->isValidDate($fromDate) || ! $this->isValidDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked($fromDate, $toDate, $requestedCodes, $tradeDates, 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', [[
                'trade_date' => null,
                'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
                'message' => 'Published EOD series range must use valid explicit YYYY-MM-DD dates with from_date <= to_date.',
                'fatal' => true,
            ]]);
        }

        foreach ($tradeDates as $tradeDate) {
            if (! $this->isValidDate($tradeDate) || strcmp($tradeDate, $fromDate) < 0 || strcmp($tradeDate, $toDate) > 0) {
                return $this->blocked($fromDate, $toDate, $requestedCodes, $tradeDates, 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', [[
                    'trade_date' => $tradeDate,
                    'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
                    'message' => 'Every exact price trade date must be valid and inside the explicit published-series range.',
                    'fatal' => true,
                ]]);
            }
        }

        if ($tradeDates === []) {
            $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
            if (! ($calendar['is_ready'] ?? false)) {
                return $this->blocked($fromDate, $toDate, $requestedCodes, [], 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', $calendar['diagnostics'] ?? []);
            }
            $tradeDates = $calendar['trade_dates'];
        }

        $series = [];
        $publicationManifest = [];
        $missingPublicationDates = [];
        $missingPriceDates = [];
        $missingPriceRows = [];
        $diagnostics = [];
        $resolvedRowCount = 0;

        foreach ($tradeDates as $tradeDate) {
            $readiness = $this->readiness->readinessForTradeDate($tradeDate);
            if (! ($readiness['is_ready'] ?? false)) {
                $missingPublicationDates[] = $tradeDate;
                $publicationManifest[] = [
                    'trade_date' => $tradeDate,
                    'is_readable' => false,
                    'reason_code' => $readiness['reason_code'] ?? 'NO_READABLE_PUBLICATION',
                    'publication_id' => $readiness['publication_id'] ?? null,
                    'publication_version' => $readiness['publication_version'] ?? null,
                    'run_id' => $readiness['run_id'] ?? null,
                    'pointer_resolve_status' => $readiness['pointer_resolve_status'] ?? 'NOT_RESOLVED_READABLE_CURRENT',
                    'source_name' => $readiness['source_name'] ?? null,
                    'row_count' => 0,
                    'payload_hash' => null,
                ];
                $diagnostics[] = [
                    'trade_date' => $tradeDate,
                    'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
                    'source_reason_code' => $readiness['reason_code'] ?? 'NO_READABLE_PUBLICATION',
                    'message' => 'Exact trade date has no official readable publication.',
                    'fatal' => true,
                ];
                continue;
            }

            $rows = $this->bars->barsForPublicationIdentity(
                $tradeDate,
                (int) $readiness['publication_id'],
                (int) $readiness['run_id'],
                $requestedCodes
            );
            $resolvedRowCount += count($rows);
            $returnedCodes = [];

            foreach ($rows as $row) {
                $tickerCode = $row['ticker_code'];
                $returnedCodes[$tickerCode] = true;
                $bar = array_merge($row, [
                    'publication_version' => (int) $readiness['publication_version'],
                    'trade_date_effective' => $readiness['trade_date_effective'],
                    'publishability_state' => $readiness['publishability_state'],
                    'seal_state' => $readiness['seal_state'],
                    'pointer_resolve_status' => $readiness['pointer_resolve_status'],
                    'published' => true,
                    'readable' => true,
                    'source_name' => $readiness['source_name'] ?: ($row['source_name'] ?? null),
                ]);
                $series[$tickerCode][$tradeDate] = $bar;
            }

            $missingCodes = array_values(array_filter($requestedCodes, function (string $code) use ($returnedCodes): bool {
                return ! isset($returnedCodes[$code]);
            }));

            if ($rows === [] && $requestedCodes !== []) {
                $missingPriceDates[] = $tradeDate;
            }
            foreach ($missingCodes as $missingCode) {
                $missingPriceRows[] = [
                    'trade_date' => $tradeDate,
                    'ticker_code' => $missingCode,
                    'reason_code' => 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE',
                ];
                $diagnostics[] = [
                    'trade_date' => $tradeDate,
                    'ticker_code' => $missingCode,
                    'reason_code' => 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE',
                    'message' => 'Exact-date readable publication exists, but the requested ticker has no published OHLC row.',
                    'fatal' => false,
                ];
            }

            $publicationManifest[] = [
                'trade_date' => $tradeDate,
                'is_readable' => true,
                'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
                'publication_id' => (int) $readiness['publication_id'],
                'publication_version' => (int) $readiness['publication_version'],
                'run_id' => (int) $readiness['run_id'],
                'pointer_resolve_status' => $readiness['pointer_resolve_status'],
                'source_name' => $readiness['source_name'],
                'row_count' => count($rows),
                'missing_tickers' => $missingCodes,
                'payload_hash' => $this->stableHash($rows),
            ];
        }

        ksort($series, SORT_STRING);
        foreach ($series as &$rowsByDate) {
            ksort($rowsByDate, SORT_STRING);
        }
        unset($rowsByDate);

        $ready = $missingPublicationDates === [];

        return [
            'ready' => $ready,
            'is_ready' => $ready,
            'reason_code' => $ready ? 'READABLE_PUBLISHED_EOD_SERIES_RESOLVED' : 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'requested_trade_dates' => $tradeDates,
            'requested_tickers' => $requestedCodes,
            'series_by_ticker' => $series,
            'publication_manifest' => $publicationManifest,
            'price_series_manifest' => [
                'ticker_count' => count($requestedCodes),
                'required_price_date_count' => count($tradeDates),
                'resolved_publication_date_count' => count($tradeDates) - count($missingPublicationDates),
                'resolved_price_date_count' => count($tradeDates) - count(array_unique(array_merge($missingPublicationDates, $missingPriceDates))),
                'resolved_price_row_count' => $resolvedRowCount,
                'missing_publication_dates' => $missingPublicationDates,
                'missing_price_dates' => $missingPriceDates,
                'missing_price_rows' => $missingPriceRows,
                'source_payload_hash' => $this->stableHash($series),
                'exact_date_resolution_only' => true,
                'no_latest_fallback' => true,
                'no_max_trade_date' => true,
            ],
            'diagnostics' => $diagnostics,
        ];
    }

    private function blocked(
        string $fromDate,
        string $toDate,
        array $tickerCodes,
        array $tradeDates,
        string $reasonCode,
        array $diagnostics
    ): array {
        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'requested_trade_dates' => $tradeDates,
            'requested_tickers' => $tickerCodes,
            'series_by_ticker' => [],
            'publication_manifest' => [],
            'price_series_manifest' => [
                'ticker_count' => count($tickerCodes),
                'required_price_date_count' => count($tradeDates),
                'resolved_publication_date_count' => 0,
                'resolved_price_date_count' => 0,
                'resolved_price_row_count' => 0,
                'missing_publication_dates' => $tradeDates,
                'missing_price_dates' => $tradeDates,
                'missing_price_rows' => [],
                'source_payload_hash' => $this->stableHash([]),
                'exact_date_resolution_only' => true,
                'no_latest_fallback' => true,
                'no_max_trade_date' => true,
            ],
            'diagnostics' => $diagnostics,
        ];
    }

    private function normalizeTickerCodes(array $tickerCodes): array
    {
        $normalized = [];
        foreach ($tickerCodes as $tickerCode) {
            $code = strtoupper(trim((string) $tickerCode));
            if ($code !== '') {
                $normalized[$code] = $code;
            }
        }
        ksort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function normalizeTradeDates(array $tradeDates): array
    {
        $normalized = [];
        foreach ($tradeDates as $tradeDate) {
            $value = trim((string) $tradeDate);
            if ($value !== '') {
                $normalized[$value] = $value;
            }
        }
        ksort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
    }

    private function stableHash(array $value): string
    {
        return sha1(json_encode($value, JSON_UNESCAPED_SLASHES));
    }
}
