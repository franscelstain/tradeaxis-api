<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketBenchmarkReadRepository
{
    public function getBenchmarkContext(string $benchmarkCode, string $tradeDate): ?array
    {
        $indicatorSetVersion = config('market_data.indicators.set_version', 'v1');
        $code = Str::upper(trim($benchmarkCode));

        $row = DB::table('market_benchmarks as bench')
            ->leftJoin('market_benchmark_bars as bar', function ($join) use ($tradeDate) {
                $join->on('bar.benchmark_code', '=', 'bench.benchmark_code')
                    ->where('bar.trade_date', '=', $tradeDate);
            })
            ->leftJoin('market_benchmark_indicators as ind', function ($join) use ($tradeDate, $indicatorSetVersion) {
                $join->on('ind.benchmark_code', '=', 'bench.benchmark_code')
                    ->where('ind.trade_date', '=', $tradeDate)
                    ->where('ind.indicator_set_version', '=', $indicatorSetVersion);
            })
            ->where('bench.benchmark_code', $code)
            ->where('bench.is_active', 1)
            ->select(
                'bench.benchmark_code',
                'bench.provider_symbol',
                'bar.trade_date',
                'bar.close_price',
                'ind.roc_20',
                'ind.ma20',
                'ind.ma50',
                'ind.indicator_set_version',
                'ind.is_valid',
                'ind.invalid_reason_code'
            )
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'benchmark_code' => $row->benchmark_code,
            'provider_symbol' => $row->provider_symbol,
            'trade_date' => $row->trade_date ? (string) $row->trade_date : $tradeDate,
            'close_price' => $row->close_price !== null ? (float) $row->close_price : null,
            'roc_20' => $row->roc_20 !== null ? (float) $row->roc_20 : null,
            'ma20' => $row->ma20 !== null ? (float) $row->ma20 : null,
            'ma50' => $row->ma50 !== null ? (float) $row->ma50 : null,
            'indicator_set_version' => $row->indicator_set_version ?: $indicatorSetVersion,
            'is_valid' => $row->is_valid !== null ? (bool) $row->is_valid : false,
            'invalid_reason_code' => $row->invalid_reason_code ?: ($row->close_price === null ? 'NO_READABLE_PUBLICATION' : 'IND_INSUFFICIENT_HISTORY'),
        ];
    }
}
