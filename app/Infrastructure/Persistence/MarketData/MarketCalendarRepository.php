<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class MarketCalendarRepository
{
    public function tradingDatesBetween($startDate, $endDate)
    {
        return DB::table('market_calendar')
            ->whereBetween('cal_date', [$startDate, $endDate])
            ->where('is_trading_day', 1)
            ->orderBy('cal_date')
            ->pluck('cal_date')
            ->map(function ($value) {
                return (string) $value;
            })
            ->values()
            ->all();
    }

    public function tradingDateWindowStart($endDate, $requiredTradingDates, $allowPartialWindow = true)
    {
        $requiredTradingDates = max(1, (int) $requiredTradingDates);

        $dates = DB::table('market_calendar')
            ->where('cal_date', '<=', $endDate)
            ->where('is_trading_day', 1)
            ->orderBy('cal_date', 'desc')
            ->limit($requiredTradingDates)
            ->pluck('cal_date')
            ->map(function ($value) {
                return (string) $value;
            })
            ->values()
            ->all();

        if (empty($dates) || (string) $dates[0] !== (string) $endDate) {
            throw new \RuntimeException('MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE: requested date is not an active trading day in market_calendar.');
        }

        if (count($dates) < $requiredTradingDates && ! $allowPartialWindow) {
            throw new \RuntimeException('MARKET_CALENDAR_INSUFFICIENT_TRADING_WINDOW: market_calendar does not contain enough prior trading dates for the requested indicator window.');
        }

        return (string) $dates[count($dates) - 1];
    }
}
