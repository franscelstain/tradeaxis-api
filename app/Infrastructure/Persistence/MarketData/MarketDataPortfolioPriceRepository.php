<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class MarketDataPortfolioPriceRepository
{
    private EodPublicationRepository $publications;

    public function __construct(EodPublicationRepository $publications = null)
    {
        $this->publications = $publications ?: new EodPublicationRepository();
    }

    public function pricesForReadablePublication($publication, array $tickerCodes): array
    {
        $requestedCodes = $this->normalizeTickerCodes($tickerCodes);
        if ($requestedCodes === []) {
            return [
                'prices' => [],
                'missing_tickers' => [],
            ];
        }

        $tickersTable = config('market_data.tickers.table', 'tickers');
        $tickerIdColumn = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCodeColumn = config('market_data.tickers.code_column', 'ticker_code');

        $currentRows = DB::table('eod_bars as bar')
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'bar.ticker_id')
            ->where('bar.trade_date', $publication->trade_date)
            ->where('bar.publication_id', $publication->publication_id)
            ->whereIn('tick.'.$tickerCodeColumn, $requestedCodes)
            ->select(
                'tick.'.$tickerCodeColumn.' as ticker_code',
                'bar.trade_date',
                'bar.close',
                'bar.adj_close',
                'bar.source'
            )
            ->orderBy('tick.'.$tickerCodeColumn)
            ->get();

        $previousCloseByTicker = $this->previousCloseByTicker($publication->trade_date, $requestedCodes);
        $returnedCodes = [];
        $prices = [];

        foreach ($currentRows as $row) {
            $tickerCode = strtoupper(trim((string) $row->ticker_code));
            $closePrice = (float) $row->close;
            $previousClose = $previousCloseByTicker[$tickerCode] ?? null;
            $changeAmount = $previousClose !== null ? $closePrice - $previousClose : null;

            $returnedCodes[] = $tickerCode;
            $prices[] = [
                'trade_date' => (string) $row->trade_date,
                'ticker_code' => $tickerCode,
                'close_price' => $closePrice,
                'adjusted_close' => $row->adj_close !== null ? (float) $row->adj_close : $closePrice,
                'previous_close_price' => $previousClose,
                'previous_close_reason_code' => $previousClose === null ? 'NO_READABLE_PUBLICATION' : null,
                'change_amount' => $changeAmount,
                'change_pct' => $previousClose !== null && (float) $previousClose !== 0.0
                    ? ($changeAmount / $previousClose) * 100
                    : null,
                'source_name' => $row->source,
            ];
        }

        return [
            'prices' => $prices,
            'missing_tickers' => array_values(array_diff($requestedCodes, $returnedCodes)),
        ];
    }

    private function previousCloseByTicker(string $tradeDate, array $tickerCodes): array
    {
        $previousTradeDate = $this->previousTradingDate($tradeDate);
        if ($previousTradeDate === null) {
            return [];
        }

        $previousPublication = $this->publications->resolveCurrentReadablePublicationForTradeDate($previousTradeDate);
        if (! $previousPublication) {
            return [];
        }

        $tickersTable = config('market_data.tickers.table', 'tickers');
        $tickerIdColumn = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCodeColumn = config('market_data.tickers.code_column', 'ticker_code');

        $rows = DB::table('eod_bars as bar')
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'bar.ticker_id')
            ->where('bar.trade_date', $previousTradeDate)
            ->where('bar.publication_id', $previousPublication->publication_id)
            ->whereIn('tick.'.$tickerCodeColumn, $tickerCodes)
            ->select('tick.'.$tickerCodeColumn.' as ticker_code', 'bar.close')
            ->get();

        $previous = [];
        foreach ($rows as $row) {
            $previous[strtoupper(trim((string) $row->ticker_code))] = (float) $row->close;
        }

        return $previous;
    }

    private function previousTradingDate(string $tradeDate): ?string
    {
        $row = DB::table('market_calendar')
            ->where('cal_date', '<', $tradeDate)
            ->where('is_trading_day', 1)
            ->orderBy('cal_date', 'desc')
            ->select('cal_date')
            ->first();

        return $row ? (string) $row->cal_date : null;
    }

    private function normalizeTickerCodes(array $tickerCodes): array
    {
        $normalized = [];
        foreach ($tickerCodes as $tickerCode) {
            $code = strtoupper(trim((string) $tickerCode));
            if ($code !== '') {
                $normalized[$code] = $code;
            }
        }

        return array_values($normalized);
    }
}
