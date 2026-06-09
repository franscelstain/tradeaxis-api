<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class MarketDataPublishedEodSeriesReadRepository
{
    public function barsForPublicationIdentity(
        string $tradeDate,
        int $publicationId,
        int $runId,
        array $tickerCodes
    ): array {
        $requestedCodes = $this->normalizeTickerCodes($tickerCodes);
        if ($requestedCodes === []) {
            return [];
        }

        $tickersTable = config('market_data.tickers.table', 'tickers');
        $tickerIdColumn = config('market_data.tickers.id_column', 'ticker_id');
        $tickerCodeColumn = config('market_data.tickers.code_column', 'ticker_code');

        return DB::table('eod_bars as bar')
            ->join($tickersTable.' as tick', 'tick.'.$tickerIdColumn, '=', 'bar.ticker_id')
            ->where('bar.trade_date', $tradeDate)
            ->where('bar.publication_id', $publicationId)
            ->where('bar.run_id', $runId)
            ->whereIn('tick.'.$tickerCodeColumn, $requestedCodes)
            ->select(
                'bar.trade_date',
                'bar.ticker_id',
                'tick.'.$tickerCodeColumn.' as ticker_code',
                'bar.open',
                'bar.high',
                'bar.low',
                'bar.close',
                'bar.volume',
                'bar.adj_close',
                'bar.source'
            )
            ->orderBy('tick.'.$tickerCodeColumn)
            ->get()
            ->map(function ($row) use ($publicationId, $runId) {
                return [
                    'trade_date' => (string) $row->trade_date,
                    'ticker_id' => (int) $row->ticker_id,
                    'ticker_code' => strtoupper(trim((string) $row->ticker_code)),
                    'open' => $row->open !== null ? (float) $row->open : null,
                    'high' => $row->high !== null ? (float) $row->high : null,
                    'low' => $row->low !== null ? (float) $row->low : null,
                    'close' => $row->close !== null ? (float) $row->close : null,
                    'volume' => $row->volume !== null ? (int) $row->volume : null,
                    'adj_close' => $row->adj_close !== null ? (float) $row->adj_close : null,
                    'publication_id' => $publicationId,
                    'run_id' => $runId,
                    'source_name' => $row->source,
                ];
            })
            ->all();
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

        ksort($normalized, SORT_STRING);

        return array_values($normalized);
    }
}
