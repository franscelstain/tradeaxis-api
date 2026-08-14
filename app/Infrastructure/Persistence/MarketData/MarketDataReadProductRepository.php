<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/**
 * Consumer-neutral, publication-bound market-data facts.
 *
 * This repository deliberately does not screen candidates, apply current is_active,
 * rank instruments, or require a strategy-specific indicator subset.
 */
class MarketDataReadProductRepository
{
    public function rowsForReadablePublication($publication): array
    {
        $tickersTable = config('market_data.tickers.table', 'tickers');
        $tickerId = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCode = config('market_data.tickers.code_column', 'ticker_code');
        $sectorsTable = config('market_data.sectors.table', 'market_data_sectors');

        return DB::table('eod_eligibility as elig')
            ->join($tickersTable.' as tick', 'tick.'.$tickerId, '=', 'elig.ticker_id')
            ->leftJoin('eod_bars as bar', function ($join) {
                $join->on('bar.trade_date', '=', 'elig.trade_date')
                    ->on('bar.ticker_id', '=', 'elig.ticker_id')
                    ->on('bar.publication_id', '=', 'elig.publication_id')
                    ->on('bar.run_id', '=', 'elig.run_id')
                    ->whereRaw('HEX(bar.price_product_code) = HEX(?)', [(string) config('market_data.scope.raw_product_code', 'RAW')]);
            })
            ->leftJoin('eod_indicators as ind', function ($join) {
                $join->on('ind.trade_date', '=', 'elig.trade_date')
                    ->on('ind.ticker_id', '=', 'elig.ticker_id')
                    ->on('ind.publication_id', '=', 'elig.publication_id')
                    ->on('ind.run_id', '=', 'elig.run_id');
            })
            ->leftJoin($sectorsTable.' as sector', function ($join) {
                $join->on('sector.sector_code', '=', 'ind.sector_code')
                    ->where('sector.classification_system', '=', config('market_data.sectors.classification_system', 'IDX-IC'));
            })
            ->where('elig.trade_date', $publication->trade_date)
            ->where('elig.publication_id', $publication->publication_id)
            ->where('elig.run_id', $publication->run_id)
            ->where('ind.price_product_code', $publication->price_product_code)
            ->where('ind.price_product_version', $publication->price_product_version)
            ->where('ind.factor_set_hash', $publication->factor_set_hash)
            ->select([
                'elig.trade_date', 'elig.eligible', 'elig.reason_code as data_usability_reason_code',
                'elig.universe_membership_state', 'elig.bar_expectation_state', 'elig.delivery_state',
                'elig.canonical_quality_state', 'elig.liquidity_state', 'elig.temporal_status_state',
                'elig.trading_status_revision_id', 'elig.trading_status_source_observation_id',
                'elig.event_risk_state', 'elig.eligibility_reasons_json',
                'tick.'.$tickerId.' as ticker_id', 'tick.'.$tickerCode.' as ticker_code',
                'tick.company_name as ticker_name', 'bar.close as close_price', 'bar.volume', 'bar.source',
                'bar.listing_id', 'bar.price_product_code as canonical_price_product_code', 'bar.quality_state as bar_quality_state',
                'ind.is_valid as indicator_is_valid', 'ind.invalid_reason_code as indicator_invalid_reason_code',
                'ind.price_product_code', 'ind.price_product_version', 'ind.factor_set_id', 'ind.factor_set_hash',
                'ind.config_snapshot_id as analytical_config_snapshot_id', 'ind.sector_membership_id',
                'ind.dv20_idr', 'ind.adv20_traded_value_idr_actual', 'ind.adv20_close_volume_proxy_idr',
                'ind.atr14_pct', 'ind.vol_ratio', 'ind.sector_code', 'sector.sector_name', 'sector.sector_index_code',
                'ind.roc5', 'ind.roc10', 'ind.roc20', 'ind.hh20', 'ind.ll20', 'ind.ma20', 'ind.ma50',
                'ind.close_to_hh20_pct', 'ind.close_to_ll20_pct', 'ind.range_20_pct', 'ind.range_position_20_pct',
                'ind.close_vs_ma20_pct', 'ind.close_vs_ma50_pct', 'ind.ma20_slope_pct', 'ind.rs_20_vs_ihsg',
                'ind.sector_roc20', 'ind.rs_20_vs_sector', 'ind.sector_rs_20_vs_ihsg',
                'ind.corporate_action_flag', 'ind.corporate_action_types', 'ind.trading_status_code',
                'ind.is_suspended', 'ind.is_uma', 'ind.event_risk_flag', 'ind.event_risk_reasons',
                'ind.indicator_set_version', 'ind.formula_version', 'ind.null_reasons_json',
            ])
            ->orderBy('tick.'.$tickerCode)
            ->get()
            ->map(function ($row) {
                $result = (array) $row;
                $result['trade_date'] = (string) $row->trade_date;
                $result['ticker_id'] = $row->ticker_id === null ? null : (int) $row->ticker_id;
                $result['ticker_code'] = strtoupper(trim((string) $row->ticker_code));
                $result['data_usable'] = (int) $row->eligible === 1;
                $result['eligible'] = $result['data_usable']; // compatibility alias only
                $result['eligibility_state'] = $result['data_usable'] ? 'DATA_USABLE' : 'DATA_NOT_USABLE';
                $result['eligibility_reason_code'] = $row->data_usability_reason_code;
                $result['source_name'] = $row->source;
                $result['dv20idr'] = $this->decimalOrNull($row->dv20_idr);
                $result['roc_5'] = $this->decimalOrNull($row->roc5);
                $result['roc_10'] = $this->decimalOrNull($row->roc10);
                $result['roc_20'] = $this->decimalOrNull($row->roc20);

                foreach ([
                    'close_price', 'atr14_pct', 'vol_ratio', 'hh20', 'll20', 'ma20', 'ma50',
                    'close_to_hh20_pct', 'close_to_ll20_pct', 'range_20_pct', 'range_position_20_pct',
                    'close_vs_ma20_pct', 'close_vs_ma50_pct', 'ma20_slope_pct', 'rs_20_vs_ihsg',
                    'sector_roc20', 'rs_20_vs_sector', 'sector_rs_20_vs_ihsg',
                    'adv20_traded_value_idr_actual', 'adv20_close_volume_proxy_idr',
                ] as $field) {
                    $result[$field] = $this->decimalOrNull($result[$field] ?? null);
                }

                foreach (['volume', 'indicator_is_valid', 'corporate_action_flag', 'is_suspended', 'is_uma', 'event_risk_flag'] as $field) {
                    $result[$field] = $result[$field] === null ? null : (int) $result[$field];
                }

                unset($result['roc5'], $result['roc10'], $result['roc20'], $result['dv20_idr'], $result['data_usability_reason_code']);

                return $result;
            })
            ->all();
    }

    private function decimalOrNull($value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
