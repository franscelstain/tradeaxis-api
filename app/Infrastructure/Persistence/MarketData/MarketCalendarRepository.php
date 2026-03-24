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
}
