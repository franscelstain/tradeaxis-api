<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TickerMasterRepository
{
    private $temporalIdentity;

    public function __construct(TemporalIdentityRepository $temporalIdentity = null)
    {
        $this->temporalIdentity = $temporalIdentity ?: new TemporalIdentityRepository();
    }

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

    public function resolveTickerCodesByIds(array $tickerIds)
    {
        $normalized = collect($tickerIds)
            ->filter(function ($tickerId) {
                return (int) $tickerId > 0;
            })
            ->map(function ($tickerId) {
                return (int) $tickerId;
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
            ->whereIn($idColumn, $normalized)
            ->get()
            ->mapWithKeys(function ($row) use ($idColumn, $codeColumn) {
                return [(int) $row->{$idColumn} => Str::upper(trim($row->{$codeColumn}))];
            })
            ->all();
    }

    public function getUniverseForTradeDate($tradeDate)
    {
        return array_map(function (array $row) {
            return [
                'ticker_id' => $row['ticker_id'],
                'ticker_code' => $row['ticker_code'],
                'issuer_id' => $row['issuer_id'],
                'instrument_id' => $row['instrument_id'],
                'listing_id' => $row['listing_id'],
                'exchange_code' => $row['exchange_code'],
                'market_segment' => $row['market_segment'],
                'board_code' => $row['board_code'],
                'identity_recorded_at' => $row['identity_recorded_at'],
            ];
        }, $this->temporalIdentity->universeAsOf($tradeDate));
    }

    public function resolveTemporalContextsByCodes(array $tickerCodes, $tradeDate, $knownAt = null)
    {
        return $this->temporalIdentity->resolveByTickerCodes($tickerCodes, $tradeDate, $knownAt);
    }
}
