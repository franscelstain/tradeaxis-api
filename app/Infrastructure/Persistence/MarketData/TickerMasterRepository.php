<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TickerMasterRepository
{
    public function resolveTickerIdsByCodes(array $tickerCodes)
    {
        $normalized = collect($tickerCodes)
            ->filter()
            ->map(function ($code) {
                return Str::upper(trim($code));
            })
            ->unique()
            ->values()
            ->all();

        if (empty($normalized)) {
            return [];
        }

        $table = config('market_data.tickers.table');
        $codeColumn = config('market_data.tickers.code_column');
        $idColumn = config('market_data.tickers.id_column');

        return DB::table($table)
            ->select([$idColumn, $codeColumn])
            ->whereIn(DB::raw('UPPER(TRIM('.$codeColumn.'))'), $normalized)
            ->get()
            ->mapWithKeys(function ($row) use ($idColumn, $codeColumn) {
                return [Str::upper(trim($row->{$codeColumn})) => (int) $row->{$idColumn}];
            })
            ->all();
    }

    public function getUniverseForTradeDate($tradeDate)
    {
        $table = config('market_data.tickers.table');
        $idColumn = config('market_data.tickers.id_column');
        $codeColumn = config('market_data.tickers.code_column');
        $activeColumn = config('market_data.tickers.active_column');
        $activeYesValue = config('market_data.tickers.active_yes_value');
        $listedDateColumn = config('market_data.tickers.listed_date_column');
        $delistedDateColumn = config('market_data.tickers.delisted_date_column');

        $query = DB::table($table)->select([$idColumn, $codeColumn]);

        if ($activeColumn) {
            $query->where(function ($sub) use ($activeColumn, $activeYesValue) {
                $sub->where($activeColumn, $activeYesValue)
                    ->orWhere($activeColumn, 1)
                    ->orWhere($activeColumn, true);
            });
        }

        if ($listedDateColumn) {
            $query->where(function ($sub) use ($listedDateColumn, $tradeDate) {
                $sub->whereNull($listedDateColumn)
                    ->orWhere($listedDateColumn, '<=', $tradeDate);
            });
        }

        if ($delistedDateColumn) {
            $query->where(function ($sub) use ($delistedDateColumn, $tradeDate) {
                $sub->whereNull($delistedDateColumn)
                    ->orWhere($delistedDateColumn, '>=', $tradeDate);
            });
        }

        return $query
            ->orderBy($idColumn)
            ->get()
            ->map(function ($row) use ($idColumn, $codeColumn) {
                return [
                    'ticker_id' => (int) $row->{$idColumn},
                    'ticker_code' => Str::upper(trim($row->{$codeColumn})),
                ];
            })
            ->all();
    }
}
