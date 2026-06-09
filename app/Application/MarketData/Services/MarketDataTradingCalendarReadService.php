<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;

class MarketDataTradingCalendarReadService
{
    private MarketCalendarRepository $calendar;

    public function __construct(MarketCalendarRepository $calendar = null)
    {
        $this->calendar = $calendar ?: new MarketCalendarRepository();
    }

    public function resolveTradingDates(string $fromDate, string $toDate): array
    {
        if (! $this->isValidRange($fromDate, $toDate)) {
            return $this->blocked($fromDate, $toDate, 0, [
                'message' => 'Trading-date range must use valid explicit YYYY-MM-DD dates with from_date <= to_date.',
            ]);
        }

        $rows = $this->calendar->tradingCalendarRowsBetween($fromDate, $toDate);
        $tradeDates = array_values(array_map(function (array $row): string {
            return $row['trade_date'];
        }, $rows));

        if ($tradeDates === []) {
            return $this->blocked($fromDate, $toDate, 0, [
                'message' => 'Official market calendar contains no trading date in the explicit range.',
            ]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'READABLE_TRADING_CALENDAR_RESOLVED',
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'trade_dates' => $tradeDates,
            'calendar_dates' => $tradeDates,
            'forward_horizon_days' => 0,
            'calendar_source' => 'market_calendar',
            'calendar_sources' => $this->calendarSources($rows),
            'calendar_hash' => $this->stableHash($rows),
            'coverage' => [
                'replay_date_count' => count($tradeDates),
                'calendar_date_count' => count($tradeDates),
                'forward_date_count' => 0,
                'horizon_complete' => true,
            ],
            'diagnostics' => [],
        ];
    }

    public function resolveReplayWindow(string $fromDate, string $toDate, int $forwardTradingDays = 5): array
    {
        $forwardTradingDays = max(1, $forwardTradingDays);
        $range = $this->resolveTradingDates($fromDate, $toDate);
        if (! ($range['is_ready'] ?? false)) {
            $range['forward_horizon_days'] = $forwardTradingDays;

            return $range;
        }

        $replayDates = $range['trade_dates'];
        $lastReplayDate = $replayDates[count($replayDates) - 1];
        $forwardRows = $this->calendar->tradingCalendarRowsAfter($lastReplayDate, $forwardTradingDays);
        if (count($forwardRows) < $forwardTradingDays) {
            return $this->blocked($fromDate, $toDate, $forwardTradingDays, [
                'message' => 'Official market calendar does not cover the required D+1 through evaluation horizon.',
                'trade_dates' => $replayDates,
                'available_forward_dates' => array_column($forwardRows, 'trade_date'),
            ]);
        }

        $rangeRows = $this->calendar->tradingCalendarRowsBetween($fromDate, $toDate);
        $calendarRows = array_merge($rangeRows, $forwardRows);
        $calendarDates = array_values(array_unique(array_column($calendarRows, 'trade_date')));
        sort($calendarDates, SORT_STRING);

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'READABLE_TRADING_CALENDAR_RESOLVED',
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'trade_dates' => $replayDates,
            'calendar_dates' => $calendarDates,
            'forward_horizon_days' => $forwardTradingDays,
            'calendar_source' => 'market_calendar',
            'calendar_sources' => $this->calendarSources($calendarRows),
            'calendar_hash' => $this->stableHash($calendarRows),
            'coverage' => [
                'replay_date_count' => count($replayDates),
                'calendar_date_count' => count($calendarDates),
                'forward_date_count' => count($forwardRows),
                'horizon_complete' => true,
            ],
            'diagnostics' => [],
        ];
    }

    private function blocked(string $fromDate, string $toDate, int $forwardTradingDays, array $context): array
    {
        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE',
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'trade_dates' => $context['trade_dates'] ?? [],
            'calendar_dates' => [],
            'forward_horizon_days' => max(0, $forwardTradingDays),
            'calendar_source' => 'market_calendar',
            'calendar_sources' => [],
            'calendar_hash' => null,
            'coverage' => [
                'replay_date_count' => count($context['trade_dates'] ?? []),
                'calendar_date_count' => 0,
                'forward_date_count' => count($context['available_forward_dates'] ?? []),
                'horizon_complete' => false,
            ],
            'diagnostics' => [[
                'trade_date' => null,
                'reason_code' => 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE',
                'message' => $context['message'] ?? 'Official trading calendar is unavailable.',
                'fatal' => true,
            ]],
        ];
    }

    private function calendarSources(array $rows): array
    {
        $sources = [];
        foreach ($rows as $row) {
            $source = trim((string) ($row['source_name'] ?? ''));
            if ($source !== '') {
                $sources[$source] = $source;
            }
        }
        ksort($sources, SORT_STRING);

        return array_values($sources);
    }

    private function isValidRange(string $fromDate, string $toDate): bool
    {
        return $this->isValidDate($fromDate)
            && $this->isValidDate($toDate)
            && strcmp($fromDate, $toDate) <= 0;
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
