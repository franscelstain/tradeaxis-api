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
                    $diagnostics[] = $this->diagnosticItem(
                        $trade['trade_date'] ?? null,
                        $evaluation['reason_code'] ?? 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE',
                        $this->evaluationDiagnosticContext($trade, $evaluation)
                    );
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
        $paramsetSnapshot = is_array($backtestPayload['paramset_snapshot'] ?? null)
            ? $backtestPayload['paramset_snapshot']
            : [];
        $canonicalEvalMetrics = $this->canonicalEvalMetrics(
            $evaluatedTrades,
            $replayDates,
            $backtestPayload,
            $paramsetSnapshot
        );
        $metricSufficiency = $this->metricSufficiency(
            $canonicalEvalMetrics,
            count($replayDates),
            $paramsetSnapshot
        );

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
                'positive_volume_required_for_execution' => true,
                'zero_volume_bar_is_published_but_non_executable' => true,
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
            'canonical_eval_metrics' => $canonicalEvalMetrics,
            'metric_sufficiency' => $metricSufficiency,
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
        $paramset = is_array($backtestPayload['paramset_snapshot'] ?? null)
            ? $backtestPayload['paramset_snapshot']
            : [];
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $minTradableVolume = max(1, $this->intOrNull($backtest['min_tradable_volume'] ?? null) ?? 1);
        $tradableBarRule = (string) ($backtest['tradable_bar_rule'] ?? 'POSITIVE_VOLUME_REQUIRED');

        if ($ticker === '' || $tradeDate === '' || $entryDate === null) {
            return $this->skippedEvaluation($trade, 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE');
        }

        $entryBar = $this->publishedBar($publishedPriceSeriesByTicker, $ticker, $entryDate);
        if ($entryBar === null) {
            return $this->skippedEvaluation($trade, 'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE', [
                'entry_trade_date' => $entryDate,
            ]);
        }

        $entryVolume = $this->intOrNull($entryBar['volume'] ?? null);
        if (! $this->isTradableVolume($entryVolume, $minTradableVolume)) {
            return $this->skippedEvaluation($trade, 'BT_SKIP_NO_TRADABLE_ENTRY', [
                'entry_trade_date' => $entryDate,
                'entry_volume' => $entryVolume,
                'tradable_bar_rule' => $tradableBarRule,
                'min_tradable_volume' => $minTradableVolume,
                'message' => 'Published entry bar exists but has no executable market volume.',
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
                'entry_volume' => $entryVolume,
            ]);
        }

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
        $exitBar = null;
        $reasonCodes = [];
        $ignoredNonTradableExitDates = [];
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
                        'ignored_non_tradable_exit_dates' => $ignoredNonTradableExitDates,
                    ]);
                }
                continue;
            }

            $barVolume = $this->intOrNull($bar['volume'] ?? null);
            if (! $this->isTradableVolume($barVolume, $minTradableVolume)) {
                $ignoredNonTradableExitDates[] = $date;
                if ($index === count($exitDates) - 1) {
                    return $this->skippedEvaluation($trade, 'BT_SKIP_NO_TRADABLE_EXIT', [
                        'exit_trade_date' => $date,
                        'exit_volume' => $barVolume,
                        'ignored_non_tradable_exit_dates' => $ignoredNonTradableExitDates,
                        'tradable_bar_rule' => $tradableBarRule,
                        'min_tradable_volume' => $minTradableVolume,
                        'message' => 'Published exit bar exists but has no executable market volume.',
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
                    $exitBar = $bar;
                    if ($high !== null && $high >= $levels['target_price']) {
                        $reasonCodes[] = 'BT_AMBIGUOUS_HIT_STOP_PRIOR';
                    }
                    break;
                }

                if ($high !== null && $high >= $levels['target_price']) {
                    $exitPrice = $levels['target_price'];
                    $exitDate = $date;
                    $exitReasonCode = 'WATCHLIST_BACKTEST_EXIT_TARGET';
                    $exitBar = $bar;
                    break;
                }
            }

            if ($index === count($exitDates) - 1) {
                $close = $this->floatOrNull($bar['close'] ?? null);
                if ($close === null || $close <= 0) {
                    return $this->skippedEvaluation($trade, 'BT_SKIP_MISSING_OHLC_EXIT', [
                        'exit_trade_date' => $date,
                        'exit_volume' => $barVolume,
                        'ignored_non_tradable_exit_dates' => $ignoredNonTradableExitDates,
                    ]);
                }
                $exitPrice = $close;
                $exitDate = $date;
                $exitReasonCode = 'WATCHLIST_BACKTEST_EXIT_HOLD_EXPIRED';
                $exitBar = $bar;
            }
        }

        if ($exitPrice === null || $exitDate === null || $exitReasonCode === null) {
            return $this->skippedEvaluation($trade, 'BT_SKIP_MISSING_OHLC_EXIT', [
                'ignored_non_tradable_exit_dates' => $ignoredNonTradableExitDates,
            ]);
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
                'entry_volume' => $entryVolume,
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
            'bucket_code' => $trade['bucket_code'] ?? null,
            'metrics_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_EVALUATION_READY',
            'entry_trade_date' => $entryDate,
            'exit_trade_date' => $exitDate,
            'exit_reason_code' => $exitReasonCode,
            'entry_price' => $entryPrice,
            'exit_price' => $exitPrice,
            'entry_volume' => $entryVolume,
            'exit_volume' => $this->intOrNull($exitBar['volume'] ?? null),
            'ignored_non_tradable_exit_dates' => $ignoredNonTradableExitDates,
            'tradable_bar_rule' => $tradableBarRule,
            'min_tradable_volume' => $minTradableVolume,
            'entry_publication_id' => $entryBar['publication_id'] ?? null,
            'entry_publication_version' => $entryBar['publication_version'] ?? null,
            'entry_run_id' => $entryBar['run_id'] ?? null,
            'exit_publication_id' => $exitBar['publication_id'] ?? null,
            'exit_publication_version' => $exitBar['publication_version'] ?? null,
            'exit_run_id' => $exitBar['run_id'] ?? null,
            'source_name' => $entryBar['source_name'] ?? null,
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
            'bucket_code' => $trade['bucket_code'] ?? null,
            'metrics_ready' => false,
            'reason_code' => $reasonCode,
            'message' => $this->reasonMessage($reasonCode),
            'ret_net' => null,
            'is_win' => null,
            'reason_codes' => [$reasonCode],
        ], $extra);
    }

    private function evaluationDiagnosticContext(array $trade, array $evaluation): array
    {
        $context = [
            'ticker_id' => $trade['ticker_id'] ?? null,
            'ticker' => $trade['ticker'] ?? $trade['ticker_code'] ?? null,
        ];
        foreach ([
            'message',
            'entry_trade_date',
            'entry_volume',
            'exit_trade_date',
            'exit_volume',
            'ignored_non_tradable_exit_dates',
            'tradable_bar_rule',
            'min_tradable_volume',
            'required_holding_days',
            'available_holding_days',
        ] as $key) {
            if (array_key_exists($key, $evaluation)) {
                $context[$key] = $evaluation[$key];
            }
        }

        return $context;
    }

    private function isTradableVolume(?int $volume, int $minimum): bool
    {
        return $volume !== null && $volume >= $minimum;
    }

    private function reasonMessage(string $reasonCode): string
    {
        $messages = [
            'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE' => 'Required trading-calendar horizon is unavailable.',
            'WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE' => 'Required exact-date published price row is unavailable.',
            'BT_SKIP_MISSING_OHLC_ENTRY' => 'Entry OHLC is incomplete or non-positive.',
            'BT_SKIP_MISSING_OHLC_EXIT' => 'No valid OHLC was available for the required exit evaluation.',
            'BT_SKIP_NO_TRADABLE_ENTRY' => 'Entry bar is published but has no positive executable volume.',
            'BT_SKIP_NO_TRADABLE_EXIT' => 'Exit bar is published but has no positive executable volume.',
            'BT_SKIP_NOT_ENOUGH_NOTIONAL' => 'Configured notional cannot purchase one board lot.',
        ];

        return $messages[$reasonCode] ?? 'Backtest evaluation was skipped by a reason-coded fail-safe rule.';
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

        if (($bar['published'] ?? false) !== true || ($bar['readable'] ?? true) !== true) {
            return null;
        }
        if (isset($bar['trade_date']) && (string) $bar['trade_date'] !== $tradeDate) {
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

    private function canonicalEvalMetrics(
        array $evaluatedTrades,
        array $replayDates,
        array $backtestPayload,
        array $paramset
    ): array {
        $topTrades = array_values(array_filter($evaluatedTrades, function (array $evaluation): bool {
            $bucket = strtoupper((string) ($evaluation['bucket_code'] ?? 'TOP_PICKS'));

            return ($evaluation['metrics_ready'] ?? false) === true
                && in_array($bucket, ['TOP', 'TOP_PICKS'], true)
                && $this->floatOrNull($evaluation['ret_net'] ?? null) !== null;
        }));
        $returns = array_values(array_map(function (array $evaluation): float {
            return (float) $evaluation['ret_net'];
        }, $topTrades));
        sort($returns, SORT_NUMERIC);

        $monthly = [];
        foreach ($topTrades as $evaluation) {
            $tradeDate = (string) ($evaluation['trade_date'] ?? '');
            if (strlen($tradeDate) < 7) {
                continue;
            }
            $month = substr($tradeDate, 0, 7);
            $monthly[$month][] = (float) $evaluation['ret_net'];
        }
        ksort($monthly, SORT_STRING);

        $monthWinRates = [];
        $monthAverages = [];
        $thresholds = $this->evaluationThresholds($paramset);
        $monthlyThresholdsResolved = $thresholds['min_month_win_rate_min'] !== null
            && $thresholds['min_month_avg_ret_net_min'] !== null;
        $periodFailCount = $monthlyThresholdsResolved ? 0 : null;
        foreach ($monthly as $monthReturns) {
            $wins = count(array_filter($monthReturns, function (float $value): bool {
                return $value > 0;
            }));
            $winRate = count($monthReturns) > 0 ? $wins / count($monthReturns) : null;
            $average = count($monthReturns) > 0 ? array_sum($monthReturns) / count($monthReturns) : null;
            if ($winRate !== null) {
                $monthWinRates[] = $winRate;
            }
            if ($average !== null) {
                $monthAverages[] = $average;
            }
            if ($monthlyThresholdsResolved
                && ($winRate === null
                    || $average === null
                    || $winRate < $thresholds['min_month_win_rate_min']
                    || $average < $thresholds['min_month_avg_ret_net_min'])) {
                $periodFailCount++;
            }
        }

        $wins = count(array_filter($returns, function (float $value): bool {
            return $value > 0;
        }));

        return [
            'picks_count' => count($returns),
            'days_covered' => $this->coveredReplayDateCount(
                $evaluatedTrades,
                $backtestPayload,
                $replayDates
            ),
            'avg_ret_net_top' => count($returns) > 0 ? array_sum($returns) / count($returns) : null,
            'win_rate_top' => count($returns) > 0 ? $wins / count($returns) : null,
            'median_ret_net_top' => $this->median($returns),
            'p25_ret_net_top' => $this->percentile($returns, 0.25),
            'p75_ret_net_top' => $this->percentile($returns, 0.75),
            'min_ret_net_top' => count($returns) > 0 ? min($returns) : null,
            'max_ret_net_top' => count($returns) > 0 ? max($returns) : null,
            'month_win_rate_min' => $monthWinRates !== [] ? min($monthWinRates) : null,
            'month_avg_ret_net_min' => $monthAverages !== [] ? min($monthAverages) : null,
            'periods_count' => count($monthly),
            'period_fail_count' => $periodFailCount,
            'derived_query_metrics' => [
                'loss_rate_top' => count($returns) > 0 ? 1 - ($wins / count($returns)) : null,
            ],
        ];
    }

    private function coveredReplayDateCount(
        array $evaluatedTrades,
        array $backtestPayload,
        array $replayDates
    ): int {
        $allowedDates = array_fill_keys($replayDates, true);
        $coveredDates = [];

        foreach ($evaluatedTrades as $evaluation) {
            if (($evaluation['metrics_ready'] ?? false) !== true) {
                continue;
            }

            $tradeDate = (string) ($evaluation['trade_date'] ?? '');
            if ($tradeDate !== '' && isset($allowedDates[$tradeDate])) {
                $coveredDates[$tradeDate] = true;
            }
        }

        foreach (($backtestPayload['diagnostics'] ?? []) as $diagnostic) {
            if (! is_array($diagnostic)
                || ($diagnostic['reason_code'] ?? null) !== 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID') {
                continue;
            }

            $tradeDate = (string) ($diagnostic['trade_date'] ?? '');
            if ($tradeDate !== '' && isset($allowedDates[$tradeDate])) {
                $coveredDates[$tradeDate] = true;
            }
        }

        return count($coveredDates);
    }

    private function metricSufficiency(array $metrics, int $totalTradingDays, array $paramset): array
    {
        $required = [
            'picks_count',
            'days_covered',
            'avg_ret_net_top',
            'win_rate_top',
            'median_ret_net_top',
            'p25_ret_net_top',
            'p75_ret_net_top',
            'min_ret_net_top',
            'max_ret_net_top',
            'month_win_rate_min',
            'month_avg_ret_net_min',
        ];
        $missing = [];
        foreach ($required as $field) {
            if (! array_key_exists($field, $metrics) || $metrics[$field] === null) {
                $missing[] = $field;
            }
        }

        $configuredThresholds = $this->evaluationThresholds($paramset);
        $thresholds = $configuredThresholds;
        if ($thresholds['min_days_covered'] !== null && $thresholds['min_days_covered'] === 0) {
            $thresholds['min_days_covered'] = (int) ceil(0.70 * max(0, $totalTradingDays));
        }
        $requiredThresholds = [
            'min_trades',
            'min_days_covered',
            'min_p25_ret_net_top',
            'min_month_win_rate_min',
            'min_month_avg_ret_net_min',
        ];
        $missingThresholds = [];
        foreach ($requiredThresholds as $field) {
            if (! array_key_exists($field, $thresholds) || $thresholds[$field] === null) {
                $missingThresholds[] = $field;
            }
        }
        $thresholdsResolved = $missingThresholds === [];

        $gates = [
            'minimum_trade_count' => $thresholdsResolved
                ? ($metrics['picks_count'] ?? 0) >= $thresholds['min_trades']
                : null,
            'minimum_coverage' => $thresholdsResolved
                ? ($metrics['days_covered'] ?? 0) >= $thresholds['min_days_covered']
                : null,
            'average_return_positive' => ($metrics['avg_ret_net_top'] ?? null) !== null
                ? $metrics['avg_ret_net_top'] > 0
                : null,
            'median_return_non_negative' => ($metrics['median_ret_net_top'] ?? null) !== null
                ? $metrics['median_ret_net_top'] >= 0
                : null,
            'p25_downside_bound' => $thresholdsResolved && ($metrics['p25_ret_net_top'] ?? null) !== null
                ? $metrics['p25_ret_net_top'] >= $thresholds['min_p25_ret_net_top']
                : null,
            'monthly_win_rate_floor' => $thresholdsResolved && ($metrics['month_win_rate_min'] ?? null) !== null
                ? $metrics['month_win_rate_min'] >= $thresholds['min_month_win_rate_min']
                : null,
            'monthly_average_floor' => $thresholdsResolved && ($metrics['month_avg_ret_net_min'] ?? null) !== null
                ? $metrics['month_avg_ret_net_min'] >= $thresholds['min_month_avg_ret_net_min']
                : null,
        ];

        $allGatesPass = ! in_array(false, $gates, true) && ! in_array(null, $gates, true);

        return [
            'required_fields_available' => $missing === [],
            'missing_required_fields' => $missing,
            'thresholds_resolved' => $thresholdsResolved,
            'missing_thresholds' => $missingThresholds,
            'threshold_source' => $thresholdsResolved ? 'paramset_snapshot.eval' : 'UNRESOLVED_PARAMSET_EVAL_THRESHOLDS',
            'configured_thresholds' => $configuredThresholds,
            'effective_thresholds' => $thresholds,
            'coverage_threshold_rule' => ($configuredThresholds['min_days_covered'] ?? null) === 0
                ? 'CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS'
                : 'PARAMSET_EXPLICIT_VALUE',
            'canonical_persisted_metrics' => $required,
            'derived_query_report_metrics' => ['loss_rate_top'],
            'diagnostic_counters' => [
                'total_replay_dates',
                'total_recommendations',
                'total_evaluated_trades',
                'hit_target_count',
                'hit_stop_count',
                'timeout_hold_expired_count',
                'empty_recommendation_days',
                'rejected_no_data_evaluation_count',
                'reason_code_distribution',
            ],
            'gating_thresholds' => array_merge($thresholds, [
                'total_trading_days_in_window' => $totalTradingDays,
            ]),
            'gates' => $gates,
            'calibration_valid' => $missing === [] && $thresholdsResolved && $allGatesPass,
            'production_ready' => false,
        ];
    }

    private function evaluationThresholds(array $paramset): array
    {
        $eval = is_array($paramset['eval'] ?? null) ? $paramset['eval'] : [];
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $fallback = is_array($backtest['metric_thresholds'] ?? null) ? $backtest['metric_thresholds'] : [];

        return [
            'min_trades' => $this->thresholdInt($eval['min_trades'] ?? $fallback['min_trades'] ?? null),
            'min_days_covered' => $this->thresholdInt($eval['min_days_covered'] ?? $fallback['min_days_covered'] ?? null),
            'min_p25_ret_net_top' => $this->thresholdFloat($eval['min_p25_ret_net_top'] ?? $fallback['min_p25_ret_net_top'] ?? null),
            'min_month_win_rate_min' => $this->thresholdFloat($eval['min_month_win_rate_min'] ?? $fallback['min_month_win_rate_min'] ?? null),
            'min_month_avg_ret_net_min' => $this->thresholdFloat($eval['min_month_avg_ret_net_min'] ?? $fallback['min_month_avg_ret_net_min'] ?? null),
        ];
    }

    private function thresholdInt($value): ?int
    {
        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }

        return is_numeric($value) ? max(0, (int) $value) : null;
    }

    private function thresholdFloat($value): ?float
    {
        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function percentile(array $values, float $percentile): ?float
    {
        if ($values === []) {
            return null;
        }

        sort($values, SORT_NUMERIC);
        $index = (count($values) - 1) * $percentile;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }

        $weight = $index - $lower;

        return (float) $values[$lower] + (((float) $values[$upper] - (float) $values[$lower]) * $weight);
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
