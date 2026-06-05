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
        $sectorsTable = config('market_data.sectors.table', 'market_data_sectors');

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
            ->leftJoin($sectorsTable.' as sector', function ($join) {
                $join->on('sector.sector_code', '=', 'ind.sector_code')
                    ->where('sector.classification_system', '=', config('market_data.sectors.classification_system', 'IDX-IC'))
                    ->where('sector.is_active', '=', 1);
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
                'tick.'.$tickerIdColumn.' as ticker_id',
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
                'ind.sector_code',
                'sector.sector_name',
                'sector.sector_index_code',
                'ind.roc5',
                'ind.roc10',
                'ind.roc20',
                'ind.hh20',
                'ind.ll20',
                'ind.ma20',
                'ind.ma50',
                'ind.close_to_hh20_pct',
                'ind.close_to_ll20_pct',
                'ind.range_20_pct',
                'ind.range_position_20_pct',
                'ind.close_vs_ma20_pct',
                'ind.close_vs_ma50_pct',
                'ind.ma20_slope_pct',
                'ind.rs_20_vs_ihsg',
                'ind.sector_roc20',
                'ind.rs_20_vs_sector',
                'ind.sector_rs_20_vs_ihsg',
                'ind.corporate_action_flag',
                'ind.corporate_action_types',
                'ind.trading_status_code',
                'ind.is_suspended',
                'ind.is_uma',
                'ind.event_risk_flag',
                'ind.event_risk_reasons',
                'ind.indicator_set_version'
            )
            ->orderBy('tick.'.$tickerCodeColumn)
            ->get()
            ->map(function ($row) {
                return [
                    'trade_date' => (string) $row->trade_date,
                    'ticker_id' => $row->ticker_id !== null ? (int) $row->ticker_id : null,
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
                    'sector_code' => $row->sector_code !== null ? strtoupper(trim((string) $row->sector_code)) : null,
                    'sector_name' => $row->sector_name,
                    'sector_index_code' => $row->sector_index_code,
                    'roc_5' => $this->decimalOrNull($row->roc5),
                    'roc_10' => $this->decimalOrNull($row->roc10),
                    'roc_20' => $this->decimalOrNull($row->roc20),
                    'hh20' => $this->decimalOrNull($row->hh20),
                    'll20' => $this->decimalOrNull($row->ll20),
                    'ma20' => $this->decimalOrNull($row->ma20),
                    'ma50' => $this->decimalOrNull($row->ma50),
                    'close_to_hh20_pct' => $this->decimalOrNull($row->close_to_hh20_pct),
                    'close_to_ll20_pct' => $this->decimalOrNull($row->close_to_ll20_pct),
                    'range_20_pct' => $this->decimalOrNull($row->range_20_pct),
                    'range_position_20_pct' => $this->decimalOrNull($row->range_position_20_pct),
                    'close_vs_ma20_pct' => $this->decimalOrNull($row->close_vs_ma20_pct),
                    'close_vs_ma50_pct' => $this->decimalOrNull($row->close_vs_ma50_pct),
                    'ma20_slope_pct' => $this->decimalOrNull($row->ma20_slope_pct),
                    'rs_20_vs_ihsg' => $this->decimalOrNull($row->rs_20_vs_ihsg),
                    'sector_roc20' => $this->decimalOrNull($row->sector_roc20),
                    'rs_20_vs_sector' => $this->decimalOrNull($row->rs_20_vs_sector),
                    'sector_rs_20_vs_ihsg' => $this->decimalOrNull($row->sector_rs_20_vs_ihsg),
                    'corporate_action_flag' => $this->flagOrNull($row->corporate_action_flag),
                    'corporate_action_types' => $row->corporate_action_types,
                    'trading_status_code' => $row->trading_status_code,
                    'is_suspended' => $this->flagOrNull($row->is_suspended),
                    'is_uma' => $this->flagOrNull($row->is_uma),
                    'event_risk_flag' => $this->flagOrNull($row->event_risk_flag),
                    'event_risk_reasons' => $row->event_risk_reasons,
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

    private function flagOrNull($value): ?int
    {
        return $value === null ? null : ((int) $value === 1 ? 1 : 0);
    }
}
