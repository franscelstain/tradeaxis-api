<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Domain\MarketData\MarketDataScope;

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

    /**
     * The universe for a trade date, optionally as the platform knew it at a moment in time.
     *
     * `universeAsOf` has accepted a knowledge cutoff since it became a temporal root, and this
     * wrapper never passed one — so the coverage denominator answered "as of now" and moved when a
     * listing was recorded between two runs of the same trade date. `F-006` recorded that as
     * 950 → 949 → 950 across three runs on one execution day.
     */
    public function getUniverseForTradeDate($tradeDate, $knownAt = null)
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
        }, $this->temporalIdentity->universeAsOf($tradeDate, $knownAt));
    }

    public function getProjectedUniverseForTradeDate($tradeDate, $knownAt = null)
    {
        $tickerTable = config('market_data.tickers.table');
        $tickerIdColumn = config('market_data.tickers.id_column');
        $missingProjectionCount = (int) DB::table($tickerTable.' as ticker')
            ->leftJoin('md_listings as listing', 'listing.legacy_ticker_id', '=', 'ticker.'.$tickerIdColumn)
            ->whereNull('listing.listing_id')
            ->count();
        if ($missingProjectionCount !== 0) {
            throw new \RuntimeException('TEMPORAL_IDENTITY_PROJECTION_INCOMPLETE: '.$missingProjectionCount.' ticker rows are not projected.');
        }

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
        }, $this->temporalIdentity->readProjectedUniverseAsOf($tradeDate, $knownAt));
    }

    /**
     * Read-only union of exchange symbols effective on at least one supplied trading date.
     *
     * A Stage 8 plan must not call universeAsOf hundreds of times: that root first performs the
     * legacy projection writer. The projection is a prerequisite established by earlier stages,
     * not work that a corpus reconstruction plan is allowed to do silently.
     */
    public function getTickerCodesForTradeDates(array $tradeDates): array
    {
        return $this->tickerScopeForTradeDates($tradeDates)['ticker_codes'];
    }

    public function getTickerCountsForTradeDates(array $tradeDates): array
    {
        return $this->tickerScopeForTradeDates($tradeDates)['ticker_counts'];
    }

    private function tickerScopeForTradeDates(array $tradeDates): array
    {
        $tradeDates = array_values(array_unique(array_map(function ($date) {
            return MarketDataScope::fromConfig()->assertRequestedDate($date);
        }, $tradeDates)));
        sort($tradeDates, SORT_STRING);
        if ($tradeDates === []) {
            return ['ticker_codes' => [], 'ticker_counts' => []];
        }

        $tickerTable = config('market_data.tickers.table');
        $tickerIdColumn = config('market_data.tickers.id_column');
        $missingProjectionCount = (int) DB::table($tickerTable.' as ticker')
            ->leftJoin('md_listings as listing', 'listing.legacy_ticker_id', '=', 'ticker.'.$tickerIdColumn)
            ->whereNull('listing.listing_id')
            ->count();
        if ($missingProjectionCount !== 0) {
            throw new \RuntimeException('TEMPORAL_IDENTITY_PROJECTION_INCOMPLETE: '.$missingProjectionCount.' ticker rows are not projected.');
        }

        $start = $tradeDates[0];
        $end = $tradeDates[count($tradeDates) - 1];
        $rows = DB::table('md_listings as listing')
            ->join('md_listing_symbols as symbol', 'symbol.listing_id', '=', 'listing.listing_id')
            ->where('listing.exchange_code', config('market_data.scope.market_code', 'IDX'))
            ->where('listing.market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->where('listing.listed_date', '<=', $end)
            ->where(function ($query) use ($start) {
                $query->whereNull('listing.delisted_date')->orWhere('listing.delisted_date', '>', $start);
            })
            ->where('symbol.symbol_type', 'EXCHANGE')
            ->whereNull('symbol.retracted_at')
            ->where('symbol.effective_from', '<=', $end.' 23:59:59')
            ->where(function ($query) use ($start) {
                $query->whereNull('symbol.effective_to')->orWhere('symbol.effective_to', '>', $start.' 00:00:00');
            })
            ->get([
                'listing.listed_date', 'listing.delisted_date',
                'symbol.symbol', 'symbol.effective_from', 'symbol.effective_to',
            ]);

        $codes = [];
        $counts = array_fill_keys($tradeDates, 0);
        $seenByDate = [];
        foreach ($rows as $row) {
            foreach ($tradeDates as $tradeDate) {
                if ((string) $row->listed_date <= $tradeDate
                    && ($row->delisted_date === null || (string) $row->delisted_date > $tradeDate)
                    && substr((string) $row->effective_from, 0, 10) <= $tradeDate
                    && ($row->effective_to === null || (string) $row->effective_to > $tradeDate.' 00:00:00')) {
                    $code = Str::upper(trim((string) $row->symbol));
                    if ($code !== '') {
                        $codes[$code] = true;
                        if (! isset($seenByDate[$tradeDate][$code])) {
                            $seenByDate[$tradeDate][$code] = true;
                            $counts[$tradeDate]++;
                        }
                    }
                }
            }
        }

        $codes = array_keys($codes);
        sort($codes, SORT_STRING);

        return ['ticker_codes' => $codes, 'ticker_counts' => $counts];
    }

    public function resolveTemporalContextsByCodes(array $tickerCodes, $tradeDate, $knownAt = null)
    {
        return $this->temporalIdentity->resolveByTickerCodes($tickerCodes, $tradeDate, $knownAt);
    }
}
