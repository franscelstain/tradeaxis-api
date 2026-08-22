<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class MarketDataPriceReadRepository
{
    private $publications;
    private $calendar;

    public function __construct(EodPublicationRepository $publications = null, MarketCalendarRepository $calendar = null)
    {
        $this->publications = $publications ?: new EodPublicationRepository();
        $this->calendar = $calendar ?: new MarketCalendarRepository();
    }

    public function priceFactsForReadablePublication($publication, array $tickerCodes): array
    {
        $requested = $this->normalize($tickerCodes);
        if ($requested === []) {
            return ['prices' => [], 'missing_tickers' => []];
        }

        $tickerTable = config('market_data.tickers.table', 'tickers');
        $tickerId = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCode = config('market_data.tickers.code_column', 'ticker_code');
        $rows = DB::table('eod_bars as bar')
            ->join($tickerTable.' as tick', 'tick.'.$tickerId, '=', 'bar.ticker_id')
            ->where('bar.trade_date', $publication->trade_date)
            ->where('bar.publication_id', $publication->publication_id)
            ->whereRaw('HEX(bar.price_product_code) = HEX(?)', [(string) config('market_data.scope.raw_product_code', 'RAW')])
            ->whereIn('tick.'.$tickerCode, $requested)
            ->select('tick.'.$tickerCode.' as ticker_code', 'bar.trade_date', 'bar.close', 'bar.adj_close', 'bar.price_product_code', 'bar.source')
            ->orderBy('tick.'.$tickerCode)
            ->get();

        $previous = $this->previousRawClose($publication->trade_date, $requested);
        $returned = [];
        $prices = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) $row->ticker_code));
            $rawClose = (float) $row->close;
            $previousClose = $previous[$code] ?? null;
            $change = $previousClose === null ? null : $rawClose - $previousClose;
            $returned[] = $code;
            $prices[] = [
                'trade_date' => (string) $row->trade_date,
                'ticker_code' => $code,
                'raw_close' => $rawClose,
                'provider_adjusted_close_evidence' => $row->adj_close === null ? null : (float) $row->adj_close,
                'price_product_code' => (string) $row->price_product_code,
                'previous_raw_close' => $previousClose,
                'previous_close_reason_code' => $previousClose === null ? 'NO_READABLE_PUBLICATION' : null,
                'change_amount' => $change,
                'change_pct' => $previousClose !== null && (float) $previousClose !== 0.0 ? ($change / $previousClose) * 100 : null,
                'source_name' => $row->source,
            ];
        }

        return ['prices' => $prices, 'missing_tickers' => array_values(array_diff($requested, $returned))];
    }

    private function previousRawClose($tradeDate, array $tickerCodes): array
    {
        $end = date('Y-m-d', strtotime($tradeDate.' -1 day'));
        $start = config('market_data.scope.dataset_start', '2023-01-02');
        if ($end < $start) return [];
        $dates = $this->calendar->tradingDatesBetween($start, $end);
        $previousDate = $dates === [] ? null : (string) end($dates);
        if (! $previousDate) return [];
        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate((string) $previousDate);
        if (! $publication) return [];

        $tickerTable = config('market_data.tickers.table', 'tickers');
        $tickerId = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCode = config('market_data.tickers.code_column', 'ticker_code');
        return DB::table('eod_bars as bar')
            ->join($tickerTable.' as tick', 'tick.'.$tickerId, '=', 'bar.ticker_id')
            ->where('bar.trade_date', $previousDate)
            ->where('bar.publication_id', $publication->publication_id)
            ->whereRaw('HEX(bar.price_product_code) = HEX(?)', [(string) config('market_data.scope.raw_product_code', 'RAW')])
            ->whereIn('tick.'.$tickerCode, $tickerCodes)
            ->pluck('bar.close', 'tick.'.$tickerCode)
            ->map(function ($value) { return (float) $value; })
            ->all();
    }

    private function normalize(array $codes): array
    {
        $result = [];
        foreach ($codes as $code) {
            $code = strtoupper(trim((string) $code));
            if ($code !== '') $result[$code] = $code;
        }
        return array_values($result);
    }
}
