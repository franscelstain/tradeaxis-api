<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class MarketDataPriceReadRepository
{
    private $publications;

    public function __construct(EodPublicationRepository $publications = null)
    {
        $this->publications = $publications ?: new EodPublicationRepository();
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
                // A row that never recorded its product code does not become RAW by being read.
                // Defaulting here asserted a product for all 756,329 legacy rows that carry NULL,
                // which is a claim about scale the row itself never made.
                'price_product_code' => $row->price_product_code ?: null,
                'price_product_reason_code' => $row->price_product_code ? null : 'PRICE_PRODUCT_UNRECORDED',
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
        $previousDate = DB::table('market_calendar')->where('cal_date', '<', $tradeDate)->where('is_trading_day', 1)->orderByDesc('cal_date')->value('cal_date');
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
