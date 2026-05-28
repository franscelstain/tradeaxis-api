<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class MarketDataWatchlistReadRepository
{
    private const REQUIRED_WATCHLIST_INDICATOR_COLUMNS = [
        'dv20_idr',
        'atr14_pct',
        'vol_ratio',
        'roc20',
        'hh20',
        'ma20',
        'ma50',
        'close_to_hh20_pct',
        'close_vs_ma20_pct',
        'close_vs_ma50_pct',
        'ma20_slope_pct',
        'rs_20_vs_ihsg',
    ];

    public function rowsForReadablePublication($publication): array
    {
        $tickersTable = config('market_data.tickers.table', 'tickers');
        $tickerIdColumn = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCodeColumn = config('market_data.tickers.code_column', 'ticker_code');
        $tickerActiveColumn = config('market_data.tickers.active_column', 'is_active');
        $tickerActiveValue = config('market_data.tickers.active_value', 1);

        $query = DB::table('eod_eligibility as elig')
            ->join('eod_bars as bar', function ($join) {
                $join->on('bar.trade_date', '=', 'elig.trade_date')
                    ->on('bar.ticker_id', '=', 'elig.ticker_id')
                    ->on('bar.publication_id', '=', 'elig.publication_id')
                    ->on('bar.run_id', '=', 'elig.run_id');
            })
            ->join('eod_indicators as ind', function ($join) {
                $join->on('ind.trade_date', '=', 'elig.trade_date')
                    ->on('ind.ticker_id', '=', 'elig.ticker_id')
                    ->on('ind.publication_id', '=', 'elig.publication_id')
                    ->on('ind.run_id', '=', 'elig.run_id');
            })
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'elig.ticker_id')
            ->where('elig.trade_date', $publication->trade_date)
            ->where('elig.publication_id', $publication->publication_id)
            ->where('elig.run_id', $publication->run_id)
            ->where('elig.eligible', 1)
            ->where('bar.publication_id', $publication->publication_id)
            ->where('bar.run_id', $publication->run_id)
            ->where('ind.publication_id', $publication->publication_id)
            ->where('ind.run_id', $publication->run_id)
            ->where('ind.is_valid', 1)
            ->whereNull('ind.invalid_reason_code')
            ->where('tick.'.$tickerActiveColumn, $tickerActiveValue)
            ->whereNotNull('ind.indicator_set_version')
            ->whereNotNull('bar.close')
            ->whereNotNull('bar.volume');

        foreach (self::REQUIRED_WATCHLIST_INDICATOR_COLUMNS as $column) {
            $query->whereNotNull('ind.'.$column);
        }

        return $query
            ->select(
                'elig.trade_date',
                'elig.eligible',
                'elig.reason_code as eligibility_reason_code',
                'tick.'.$tickerCodeColumn.' as ticker_code',
                'tick.company_name as ticker_name',
                'bar.close as close_price',
                'bar.volume',
                'bar.source',
                'ind.is_valid as indicator_is_valid',
                'ind.invalid_reason_code as indicator_invalid_reason_code',
                'ind.dv20_idr',
                'ind.atr14_pct',
                'ind.vol_ratio',
                'ind.roc20',
                'ind.hh20',
                'ind.ma20',
                'ind.ma50',
                'ind.close_to_hh20_pct',
                'ind.close_vs_ma20_pct',
                'ind.close_vs_ma50_pct',
                'ind.ma20_slope_pct',
                'ind.rs_20_vs_ihsg',
                'ind.indicator_set_version'
            )
            ->orderBy('tick.'.$tickerCodeColumn)
            ->get()
            ->map(function ($row) {
                return [
                    'trade_date' => (string) $row->trade_date,
                    'ticker_code' => strtoupper(trim((string) $row->ticker_code)),
                    'ticker_name' => $row->ticker_name,
                    'close_price' => $this->decimalOrNull($row->close_price),
                    'volume' => $row->volume !== null ? (int) $row->volume : null,
                    'eligibility_state' => (int) $row->eligible === 1 ? 'ELIGIBLE' : 'NOT_ELIGIBLE',
                    'eligibility_reason_code' => $row->eligibility_reason_code,
                    'indicator_is_valid' => (int) $row->indicator_is_valid,
                    'indicator_invalid_reason_code' => $row->indicator_invalid_reason_code,
                    'dv20idr' => $this->decimalOrNull($row->dv20_idr),
                    'atr14_pct' => $this->decimalOrNull($row->atr14_pct),
                    'vol_ratio' => $this->decimalOrNull($row->vol_ratio),
                    'roc_20' => $this->decimalOrNull($row->roc20),
                    'hh20' => $this->decimalOrNull($row->hh20),
                    'ma20' => $this->decimalOrNull($row->ma20),
                    'ma50' => $this->decimalOrNull($row->ma50),
                    'close_to_hh20_pct' => $this->decimalOrNull($row->close_to_hh20_pct),
                    'close_vs_ma20_pct' => $this->decimalOrNull($row->close_vs_ma20_pct),
                    'close_vs_ma50_pct' => $this->decimalOrNull($row->close_vs_ma50_pct),
                    'ma20_slope_pct' => $this->decimalOrNull($row->ma20_slope_pct),
                    'rs_20_vs_ihsg' => $this->decimalOrNull($row->rs_20_vs_ihsg),
                    'indicator_set_version' => $row->indicator_set_version,
                    'source_name' => $row->source,
                ];
            })
            ->all();
    }

    private function decimalOrNull($value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
