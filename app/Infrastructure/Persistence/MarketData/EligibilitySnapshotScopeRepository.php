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
            ->join('eod_runs as run', 'run.run_id', '=', 'pub.run_id')
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'elig.ticker_id')
            ->where('elig.trade_date', $tradeDate)
            ->whereColumn('pub.trade_date', 'ptr.trade_date')
            ->whereColumn('pub.trade_date', 'elig.trade_date')
            ->where('pub.is_current', 1)
            ->where('pub.seal_state', 'SEALED')
            ->whereNotNull('ptr.sealed_at')
            ->whereNotNull('pub.sealed_at')
            ->whereNotNull('run.sealed_at')
            ->whereColumn('run.trade_date_requested', 'ptr.trade_date')
            ->where('run.terminal_status', 'SUCCESS')
            ->where('run.publishability_state', 'READABLE')
            ->where('run.coverage_gate_state', 'PASS')
            ->whereNotNull('run.coverage_universe_count')
            ->where('run.coverage_universe_count', '>', 0)
            ->whereNotNull('run.coverage_available_count')
            ->whereNotNull('run.coverage_missing_count')
            ->whereNotNull('run.coverage_ratio')
            ->whereNotNull('run.coverage_min_threshold')
            ->whereNotNull('run.coverage_threshold_mode')
            ->whereNotNull('run.coverage_universe_basis')
            ->whereNotNull('run.coverage_contract_version')
            ->where('run.is_current_publication', 1)
            ->whereColumn('run.publication_id', 'ptr.publication_id')
            ->whereColumn('run.publication_version', 'ptr.publication_version')
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
