<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestMetricsService
{
    private const PRICE_SERIES_CONTRACT = 'PUBLISHED_EOD_PRICE_SERIES_INPUT';
    private const CALENDAR_CONTRACT = 'EXPLICIT_TRADING_CALENDAR_INPUT';

    public function buildMetrics(
        array $backtestPayload,
        array $publishedPriceSeriesByTicker = [],
        array $tradingCalendar = []
    ): array {
        $trades = $this->sortReplayRows($backtestPayload['trades'] ?? []);
        $evaluations = $backtestPayload['evaluations'] ?? [];
        $replayDates = $this->normalizedTradeDates($backtestPayload['replay_window']['trade_dates'] ?? []);
        $diagnostics = [];
        $evaluatedTrades = [];
        $returnValues = [];
        $winCount = 0;
        $hitTargetCount = 0;
        $hitStopCount = 0;
        $holdExpiredCount = 0;
        $rejectedNoDataCount = 0;

        $priceSeriesAvailable = $publishedPriceSeriesByTicker !== [];
        $calendarDates = $this->normalizedTradeDates($tradingCalendar);
        $calendarAvailable = $calendarDates !== [];

        if ($trades !== [] && ! $priceSeriesAvailable) {
            $diagnostics[] = $this->diagnosticItem(null, 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE', [
                'message' => 'Published EOD price series input is required for completed backtest pricing metrics.',
            ]);
        }

        if ($trades !== [] && ! $calendarAvailable) {
            $diagnostics[] = $this->diagnosticItem(null, 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', [
                'message' => 'Explicit trading calendar input is required for D+1 through holding-window evaluation.',
            ]);
        }

        if ($priceSeriesAvailable && $calendarAvailable) {
            foreach ($trades as $trade) {
                $evaluation = $this->evaluateTrade($trade, $backtestPayload, $publishedPriceSeriesByTicker, $calendarDates);
                $evaluatedTrades[] = $evaluation;

                if (($evaluation['metrics_ready'] ?? false) === true) {
                    $return = $this->floatOrNull($evaluation['ret_net'] ?? null);
                    if ($return !== null) {
                        $returnValues[] = $return;
                        if (($evaluation['is_win'] ?? false) === true) {
                            $winCount++;
                        }
                    }

                    if (($evaluation['exit_reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EXIT_TARGET') {
                        $hitTargetCount++;
                    } elseif (($evaluation['exit_reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EXIT_STOP') {
                        $hitStopCount++;
                    } elseif (($evaluation['exit_reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED') {
                        $holdExpiredCount++;
                    }
                } else {
                    $rejectedNoDataCount++;
                    $diagnostics[] = $this->diagnosticItem($trade['trade_date'] ?? null, $evaluation['reason_code'] ?? 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', [
                        'ticker_id' => $trade['ticker_id'] ?? null,
                        'ticker' => $trade['ticker'] ?? $trade['ticker_code'] ?? null,
                    ]);
                }
            }
        } else {
            foreach ($trades as $trade) {
                $rejectedNoDataCount++;
                $evaluatedTrades[] = $this->skippedEvaluation($trade, 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE');
                $diagnostics[] = $this->diagnosticItem($trade['trade_date'] ?? null, 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', [
                    'ticker_id' => $trade['ticker_id'] ?? null,
                    'ticker' => $trade['ticker'] ?? $trade['ticker_code'] ?? null,
                ]);
            }
        }

        $reasonCodeDistribution = $this->reasonCodeDistribution($backtestPayload, $evaluatedTrades, $diagnostics);
        $ready = count($returnValues) > 0 && $rejectedNoDataCount === 0;

        return [
            'ready' => $ready,
            'is_ready' => $ready,
            'reason_code' => $this->resolveReasonCode($ready, $trades, $priceSeriesAvailable, $calendarAvailable, $returnValues),
            'metrics_contract' => [
                'consumer' => 'WatchlistBacktestMetricsService',
                'source_payload' => 'WatchlistBacktestStrategyService output',
                'price_series_source' => self::PRICE_SERIES_CONTRACT,
                'calendar_source' => self::CALENDAR_CONTRACT,
                'explicit_replay_window_only' => true,
                'published_price_series_only' => true,
                'no_raw_market_data' => true,
                'no_staging_market_data' => true,
                'no_unsealed_market_data' => true,
                'no_latest_shortcut' => true,
                'no_max_trade_date_shortcut' => true,
                'no_plan_mutation' => true,
                'no_recommendation_mutation' => true,
                'no_confirm_mutation' => true,
                'no_allocation_runtime' => true,
                'not_execution_runtime' => true,
            ],
            'counts' => [
                'total_replay_dates' => count($replayDates),
                'total_recommendations' => count($trades),
                'total_evaluated_trades' => count($returnValues),
                'empty_recommendation_days' => $this->emptyRecommendationDays($backtestPayload),
                'rejected_no_data_evaluation_count' => $rejectedNoDataCount,
                'diagnostics_count' => count($diagnostics),
            ],
            'returns' => [
                'win_rate' => count($returnValues) > 0
                    ? (float) $winCount / (float) count($returnValues)
                    : null,
                'average_return' => count($returnValues) > 0
                    ? (float) array_sum($returnValues) / (float) count($returnValues)
                    : null,
                'median_return' => $this->median($returnValues),
                'max_gain' => count($returnValues) > 0 ? max($returnValues) : null,
                'max_loss' => count($returnValues) > 0 ? min($returnValues) : null,
            ],
            'exit_outcomes' => [
                'hit_target_count' => $hitTargetCount,
                'hit_stop_count' => $hitStopCount,
                'timeout_hold_expired_count' => $holdExpiredCount,
            ],
            'reason_code_distribution' => $reasonCodeDistribution,
            'diagnostics' => $diagnostics,
            'evaluated_trades' => $evaluatedTrades,
        ];
    }

    private function evaluateTrade(array $trade, array $backtestPayload, array $publishedPriceSeriesByTicker, array $calendarDates): array
    {
        $ticker = $this->tickerCode($trade);
        $tradeDate = (string) ($trade['trade_date'] ?? '');
        $entryDate = $this->nextTradingDate($tradeDate, $calendarDates);

        if ($ticker === '' || $tradeDate === '' || $entryDate === null) {
            return $this->skippedEvaluation($trade, 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE');
        }

        $entryBar = $this->publishedBar($publishedPriceSeriesByTicker, $ticker, $entryDate);
        if ($entryBar === null) {
            return $this->skippedEvaluation($trade, 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', [
                'entry_trade_date' => $entryDate,
            ]);
        }

        $entryPrice = $this->floatOrNull($entryBar['open'] ?? null);
        $entryFallbackReason = null;
        if ($entryPrice === null || $entryPrice <= 0) {
            $entryPrice = $this->floatOrNull($entryBar['close'] ?? null);
            $entryFallbackReason = 'BT_FALLBACK_ENTRY_PRICE';
        }

        if ($entryPrice === null || $entryPrice <= 0) {
            return $this->skippedEvaluation($trade, 'BT_SKIP_MISSING_OHLC_ENTRY', [
                'entry_trade_date' => $entryDate,
            ]);
        }

        $paramset = $backtestPayload['paramset_snapshot'] ?? [];
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $holdingDays = max(1, $this->intOrNull($backtest['holding_days'] ?? null) ?? 5);
        $exitDates = $this->tradingWindowFrom($entryDate, $calendarDates, $holdingDays);
        if (count($exitDates) < $holdingDays) {
            return $this->skippedEvaluation($trade, 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', [
                'entry_trade_date' => $entryDate,
                'required_holding_days' => $holdingDays,
                'available_holding_days' => count($exitDates),
            ]);
        }

        $levels = $this->targetStopLevels($entryPrice, $trade, $backtest);
        $exitPrice = null;
        $exitDate = null;
        $exitReasonCode = null;
        $reasonCodes = [];
        if ($entryFallbackReason !== null) {
            $reasonCodes[] = $entryFallbackReason;
        }
        if (! $levels['has_target_stop']) {
            $reasonCodes[] = 'WATCHLIST_BACKTEST_TARGET_STOP_LEVEL_UNAVAILABLE';
        }

        foreach ($exitDates as $index => $date) {
            $bar = $this->publishedBar($publishedPriceSeriesByTicker, $ticker, $date);
            if ($bar === null) {
                if ($index === count($exitDates) - 1) {
                    return $this->skippedEvaluation($trade, 'BT_SKIP_MISSING_OHLC_EXIT', [
                        'exit_trade_date' => $date,
                    ]);
                }
                continue;
            }

            if ($levels['has_target_stop']) {
                $low = $this->floatOrNull($bar['low'] ?? null);
                $high = $this->floatOrNull($bar['high'] ?? null);

                if ($low !== null && $low <= $levels['stop_price']) {
                    $exitPrice = $levels['stop_price'];
                    $exitDate = $date;
                    $exitReasonCode = 'WATCHLIST_BACKTEST_EXIT_STOP';
                    if ($high !== null && $high >= $levels['target_price']) {
                        $reasonCodes[] = 'BT_AMBIGUOUS_HIT_STOP_PRIOR';
                    }
                    break;
                }

                if ($high !== null && $high >= $levels['target_price']) {
                    $exitPrice = $levels['target_price'];
                    $exitDate = $date;
                    $exitReasonCode = 'WATCHLIST_BACKTEST_EXIT_TARGET';
                    break;
                }
            }

            if ($index === count($exitDates) - 1) {
                $close = $this->floatOrNull($bar['close'] ?? null);
                if ($close === null || $close <= 0) {
                    return $this->skippedEvaluation($trade, 'BT_SKIP_MISSING_OHLC_EXIT', [
                        'exit_trade_date' => $date,
                    ]);
                }
                $exitPrice = $close;
                $exitDate = $date;
                $exitReasonCode = 'WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED';
            }
        }

        if ($exitPrice === null || $exitDate === null || $exitReasonCode === null) {
            return $this->skippedEvaluation($trade, 'BT_SKIP_MISSING_OHLC_EXIT');
        }

        $notionalIdr = $this->floatOrNull($backtest['notional_idr'] ?? null) ?? 10000000.0;
        $lotSize = max(1, $this->intOrNull($backtest['lot_size'] ?? null) ?? 100);
        $feeBuy = $this->floatOrNull($backtest['fee_buy_idr'] ?? null) ?? 2500.0;
        $feeSell = $this->floatOrNull($backtest['fee_sell_idr'] ?? null) ?? 2500.0;
        $slippageEntryPct = $this->floatOrNull($backtest['slippage_entry_pct'] ?? null) ?? 0.0;
        $slippageExitPct = $this->floatOrNull($backtest['slippage_exit_pct'] ?? null) ?? 0.0;
        $entryEffective = $entryPrice * (1 + $slippageEntryPct);
        $exitEffective = $exitPrice * (1 - $slippageExitPct);
        $lots = (int) floor($notionalIdr / ($entryEffective * $lotSize));

        if ($lots < 1) {
            return $this->skippedEvaluation($trade, 'BT_SKIP_NOT_ENOUGH_NOTIONAL', [
                'entry_trade_date' => $entryDate,
                'entry_price' => $entryPrice,
                'notional_idr' => $notionalIdr,
            ]);
        }

        $quantity = $lots * $lotSize;
        $grossBuy = $entryEffective * $quantity;
        $grossSell = $exitEffective * $quantity;
        $netPnl = $grossSell - $grossBuy - $feeBuy - $feeSell;
        $retNet = $netPnl / ($grossBuy + $feeBuy);

        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $trade['ticker_id'] ?? null,
            'ticker' => $ticker,
            'metrics_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_EVALUATION_READY',
            'entry_trade_date' => $entryDate,
            'exit_trade_date' => $exitDate,
            'exit_reason_code' => $exitReasonCode,
            'entry_price' => $entryPrice,
            'exit_price' => $exitPrice,
            'quantity' => $quantity,
            'gross_buy_idr' => $grossBuy,
            'gross_sell_idr' => $grossSell,
            'net_pnl_idr' => $netPnl,
            'ret_net' => $retNet,
            'is_win' => $retNet > 0,
            'reason_codes' => $this->uniqueReasonCodes(array_merge($reasonCodes, [$exitReasonCode])),
        ];
    }

    private function skippedEvaluation(array $trade, string $reasonCode, array $extra = []): array
    {
        return array_merge([
            'trade_date' => $trade['trade_date'] ?? null,
            'ticker_id' => $trade['ticker_id'] ?? null,
            'ticker' => $trade['ticker'] ?? $trade['ticker_code'] ?? null,
            'metrics_ready' => false,
            'reason_code' => $reasonCode,
            'ret_net' => null,
            'is_win' => null,
            'reason_codes' => [$reasonCode],
        ], $extra);
    }

    private function targetStopLevels(float $entryPrice, array $trade, array $backtest): array
    {
        $targetPrice = $this->floatOrNull($trade['target_price'] ?? null);
        $stopPrice = $this->floatOrNull($trade['stop_price'] ?? null);

        $targetPct = $this->floatOrNull($backtest['target_pct'] ?? null);
        $stopPct = $this->floatOrNull($backtest['stop_pct'] ?? null);

        if ($targetPrice === null && $targetPct !== null && $targetPct > 0) {
            $targetPrice = $entryPrice * (1 + $targetPct);
        }
        if ($stopPrice === null && $stopPct !== null && $stopPct > 0) {
            $stopPrice = $entryPrice * (1 - $stopPct);
        }

        return [
            'has_target_stop' => $targetPrice !== null && $stopPrice !== null && $targetPrice > 0 && $stopPrice > 0,
            'target_price' => $targetPrice,
            'stop_price' => $stopPrice,
        ];
    }

    private function publishedBar(array $priceSeries, string $ticker, string $tradeDate): ?array
    {
        $ticker = strtoupper($ticker);
        $bar = null;
        if (isset($priceSeries[$ticker][$tradeDate]) && is_array($priceSeries[$ticker][$tradeDate])) {
            $bar = $priceSeries[$ticker][$tradeDate];
        } elseif (isset($priceSeries[$ticker.'.JK'][$tradeDate]) && is_array($priceSeries[$ticker.'.JK'][$tradeDate])) {
            $bar = $priceSeries[$ticker.'.JK'][$tradeDate];
        }

        if ($bar === null) {
            return null;
        }

        if (($bar['published'] ?? true) !== true) {
            return null;
        }

        return $bar;
    }

    private function nextTradingDate(string $tradeDate, array $calendarDates): ?string
    {
        foreach ($calendarDates as $date) {
            if (strcmp($date, $tradeDate) > 0) {
                return $date;
            }
        }

        return null;
    }

    private function tradingWindowFrom(string $startDate, array $calendarDates, int $length): array
    {
        $window = [];
        $collect = false;
        foreach ($calendarDates as $date) {
            if ($date === $startDate) {
                $collect = true;
            }
            if ($collect) {
                $window[] = $date;
                if (count($window) >= $length) {
                    break;
                }
            }
        }

        return $window;
    }

    private function reasonCodeDistribution(array $backtestPayload, array $evaluatedTrades, array $diagnostics): array
    {
        $counts = [];
        foreach (($backtestPayload['items'] ?? []) as $item) {
            $this->countReasonCodes($counts, $item['reason_codes'] ?? []);
        }
        foreach (($backtestPayload['trades'] ?? []) as $trade) {
            $this->countReasonCodes($counts, $trade['reason_codes'] ?? []);
        }
        foreach (($backtestPayload['evaluations'] ?? []) as $evaluation) {
            $this->countReasonCodes($counts, $evaluation['reason_codes'] ?? []);
        }
        foreach ($evaluatedTrades as $evaluation) {
            $this->countReasonCodes($counts, $evaluation['reason_codes'] ?? []);
        }
        foreach ($diagnostics as $diagnostic) {
            $this->countReasonCodes($counts, [$diagnostic['reason_code'] ?? null]);
            $this->countReasonCodes($counts, $diagnostic['reason_codes'] ?? []);
        }

        ksort($counts, SORT_STRING);

        return $counts;
    }

    private function countReasonCodes(array &$counts, array $reasonCodes): void
    {
        foreach ($reasonCodes as $reasonCode) {
            if (! is_scalar($reasonCode)) {
                continue;
            }
            $value = trim((string) $reasonCode);
            if ($value === '') {
                continue;
            }
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
    }

    private function emptyRecommendationDays(array $backtestPayload): int
    {
        if (isset($backtestPayload['summary']['empty_recommendation_days']) && is_numeric($backtestPayload['summary']['empty_recommendation_days'])) {
            return (int) $backtestPayload['summary']['empty_recommendation_days'];
        }

        $count = 0;
        foreach (($backtestPayload['diagnostics'] ?? []) as $diagnostic) {
            if (($diagnostic['reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID') {
                $count++;
            }
        }

        return $count;
    }

    private function resolveReasonCode(bool $ready, array $trades, bool $priceSeriesAvailable, bool $calendarAvailable, array $returnValues): string
    {
        if ($trades === []) {
            return 'WATCHLIST_BACKTEST_METRICS_NO_RECOMMENDATIONS';
        }
        if (! $priceSeriesAvailable) {
            return 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE';
        }
        if (! $calendarAvailable) {
            return 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE';
        }
        if ($ready) {
            return 'WATCHLIST_BACKTEST_METRICS_READY';
        }
        if ($returnValues !== []) {
            return 'WATCHLIST_BACKTEST_METRICS_PARTIAL';
        }

        return 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE';
    }

    private function median(array $values): ?float
    {
        $values = array_values(array_filter($values, function ($value): bool {
            return is_numeric($value);
        }));
        $count = count($values);
        if ($count === 0) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $middle = (int) floor($count / 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle - 1] + (float) $values[$middle]) / 2;
    }

    private function normalizedTradeDates(array $tradeDates): array
    {
        $normalized = [];
        foreach ($tradeDates as $tradeDate) {
            if (! is_scalar($tradeDate)) {
                continue;
            }
            $value = trim((string) $tradeDate);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $normalized[$value] = $value;
            }
        }
        sort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function sortReplayRows(array $rows): array
    {
        usort($rows, function (array $left, array $right): int {
            foreach (['trade_date', 'recommendation_rank', 'plan_rank', 'ticker_id', 'ticker'] as $key) {
                $leftValue = $left[$key] ?? null;
                $rightValue = $right[$key] ?? null;
                if ($leftValue === $rightValue) {
                    continue;
                }
                if ($leftValue === null) {
                    return 1;
                }
                if ($rightValue === null) {
                    return -1;
                }
                if (is_numeric($leftValue) && is_numeric($rightValue)) {
                    return ((float) $leftValue < (float) $rightValue) ? -1 : 1;
                }

                return strcmp((string) $leftValue, (string) $rightValue);
            }

            return 0;
        });

        return $rows;
    }

    private function diagnosticItem(?string $tradeDate, string $reasonCode, array $extra = []): array
    {
        return array_merge([
            'trade_date' => $tradeDate,
            'reason_code' => $reasonCode,
            'fatal' => false,
        ], $extra);
    }

    private function tickerCode(array $item): string
    {
        return strtoupper(trim((string) ($item['ticker'] ?? $item['ticker_code'] ?? '')));
    }

    private function uniqueReasonCodes(array $reasonCodes): array
    {
        $normalized = [];
        foreach ($reasonCodes as $reasonCode) {
            if (! is_scalar($reasonCode)) {
                continue;
            }
            $value = trim((string) $reasonCode);
            if ($value !== '') {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function floatOrNull($value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
