<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class EligibilitySnapshotScopeRepository
{
    public function getScopeForTradeDate($tradeDate)
    {
        $tickersTable = config('market_data.tickers.table');
        $tickerIdColumn = config('market_data.tickers.id_column');
        $tickerCodeColumn = config('market_data.tickers.code_column');
        $scopeDefault = config('market_data.session_snapshot.scope_default', 'universe_only');

        $query = DB::table('eod_eligibility as elig')
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'elig.ticker_id')
            ->where('elig.trade_date', $tradeDate)
            ->select('elig.ticker_id', 'tick.'.$tickerCodeColumn.' as ticker_code', 'elig.eligible');

        if ($scopeDefault === 'eligible_only') {
            $query->where('elig.eligible', 1);
        }

        return $query->orderBy('elig.ticker_id')->get()->map(function ($row) {
            return [
                'ticker_id' => (int) $row->ticker_id,
                'ticker_code' => strtoupper(trim($row->ticker_code)),
                'eligible' => (int) $row->eligible,
            ];
        })->all();
    }
}
