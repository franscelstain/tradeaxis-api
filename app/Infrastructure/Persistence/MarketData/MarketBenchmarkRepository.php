<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketBenchmarkRepository
{
    public function activeBenchmarks()
    {
        return DB::table('market_benchmarks')
            ->where('is_active', 1)
            ->orderBy('benchmark_code')
            ->get()
            ->map(function ($row) {
                return (array) $row;
            })
            ->all();
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

    public function loadBarsWindow($benchmarkCode, $tradeDate, $lookbackTradingDays)
    {
        $startDate = Carbon::parse($tradeDate)
            ->subDays(max(90, ((int) $lookbackTradingDays * 3)))
            ->toDateString();

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
}
