<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketBenchmarkRepository
{
    private $calendar;

    public function __construct(MarketCalendarRepository $calendar = null)
    {
        $this->calendar = $calendar ?: new MarketCalendarRepository();
    }

    public function activeBenchmarks()
    {
        return $this->activeBenchmarkQuery()
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }

    public function activeBenchmarksForProvider($provider)
    {
        $provider = strtolower(trim((string) $provider));

        return $this->activeBenchmarkQuery()
            ->whereRaw('LOWER(provider) = ?', [$provider])
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }

    private function activeBenchmarkQuery()
    {
        return DB::table('market_benchmarks')
            ->where('is_active', 1)
            ->orderBy('benchmark_code');
    }

    public function findByCode($benchmarkCode)
    {
        return DB::table('market_benchmarks')
            ->where('benchmark_code', Str::upper(trim((string) $benchmarkCode)))
            ->first();
    }

    public function replaceBars(array $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                DB::table('market_benchmark_bars')->updateOrInsert(
                    [
                        'benchmark_code' => $row['benchmark_code'],
                        'trade_date' => $row['trade_date'],
                    ],
                    $row
                );
            }
        });
    }

    public function replaceIndicators(array $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                DB::table('market_benchmark_indicators')->updateOrInsert(
                    [
                        'benchmark_code' => $row['benchmark_code'],
                        'trade_date' => $row['trade_date'],
                        'indicator_set_version' => $row['indicator_set_version'],
                    ],
                    $row
                );
            }
        });
    }

    public function loadBarsWindow($benchmarkCode, $tradeDate, $lookbackTradingDays, $historyStartDate = null)
    {
        $startDate = $this->calendar->tradingDateWindowStart($tradeDate, $lookbackTradingDays);
        if ($historyStartDate !== null && $startDate < $historyStartDate) {
            $startDate = $historyStartDate;
        }

        return DB::table('market_benchmark_bars')
            ->where('benchmark_code', Str::upper(trim((string) $benchmarkCode)))
            ->whereBetween('trade_date', [$startDate, $tradeDate])
            ->orderBy('trade_date')
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
    }

    public function benchmarkRoc20($benchmarkCode, $tradeDate, $indicatorSetVersion)
    {
        $row = DB::table('market_benchmark_indicators')
            ->where('benchmark_code', Str::upper(trim((string) $benchmarkCode)))
            ->where('trade_date', $tradeDate)
            ->where('indicator_set_version', $indicatorSetVersion)
            ->first();

        return $row && $row->roc_20 !== null ? (float) $row->roc_20 : null;
    }

    public function benchmarkRoc20s(array $benchmarkCodes, $tradeDate, $indicatorSetVersion)
    {
        $benchmarkCodes = array_values(array_unique(array_filter(array_map(function ($code) {
            return Str::upper(trim((string) $code));
        }, $benchmarkCodes))));

        if (empty($benchmarkCodes)) {
            return [];
        }

        return DB::table('market_benchmark_indicators')
            ->whereIn('benchmark_code', $benchmarkCodes)
            ->where('trade_date', $tradeDate)
            ->where('indicator_set_version', $indicatorSetVersion)
            ->whereNotNull('roc_20')
            ->get()
            ->mapWithKeys(function ($row) {
                return [Str::upper(trim((string) $row->benchmark_code)) => (float) $row->roc_20];
            })
            ->all();
    }
}
