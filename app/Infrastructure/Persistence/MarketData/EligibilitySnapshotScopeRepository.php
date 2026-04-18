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
            ->join('eod_publications as pub', 'pub.publication_id', '=', 'elig.publication_id')
            ->join('eod_current_publication_pointer as ptr', function ($join) {
                $join->on('ptr.trade_date', '=', 'elig.trade_date')
                    ->on('ptr.publication_id', '=', 'elig.publication_id')
                    ->on('ptr.run_id', '=', 'pub.run_id')
                    ->on('ptr.publication_version', '=', 'pub.publication_version');
            })
            ->join('eod_runs as runs', 'runs.run_id', '=', 'pub.run_id')
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'elig.ticker_id')
            ->where('elig.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('pub.trade_date', 'elig.trade_date')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('runs.sealed_at')
            ->whereColumn('runs.trade_date_requested', 'ptr.trade_date')
            ->where('runs.terminal_status', 'SUCCESS')
            ->where('runs.publishability_state', 'READABLE')
            ->where('runs.is_current_publication', 1)
            ->whereColumn('elig.run_id', 'pub.run_id')
            ->whereColumn('elig.run_id', 'ptr.run_id')
            ->whereColumn('elig.run_id', 'runs.run_id')
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
