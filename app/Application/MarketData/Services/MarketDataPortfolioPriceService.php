<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataPortfolioPriceRepository;
use Illuminate\Support\Facades\DB;

class MarketDataPortfolioPriceService
{
    private EodPublicationRepository $publications;
    private MarketDataReadinessService $readiness;
    private MarketDataPortfolioPriceRepository $prices;

    public function __construct(
        EodPublicationRepository $publications = null,
        MarketDataReadinessService $readiness = null,
        MarketDataPortfolioPriceRepository $prices = null
    ) {
        $this->publications = $publications ?: new EodPublicationRepository();
        $this->readiness = $readiness ?: new MarketDataReadinessService($this->publications);
        $this->prices = $prices ?: new MarketDataPortfolioPriceRepository($this->publications);
    }

    public function getOfficialPricesForPortfolio(string $tradeDate, array $tickerCodes): array
    {
        return $this->getOfficialPriceMap($tradeDate, $tickerCodes);
    }

    public function getOfficialPriceMap(string $tradeDate, array $tickerCodes): array
    {
        $readiness = $this->readiness->readinessForTradeDate($tradeDate);
        $requestedCodes = $this->normalizeTickerCodes($tickerCodes);

        if (! $readiness['is_ready']) {
            return [
                'trade_date' => $tradeDate,
                'trade_date_effective' => null,
                'publication_id' => null,
                'publication_version' => null,
                'run_id' => null,
                'is_ready' => false,
                'reason_code' => $readiness['reason_code'],
                'pointer_resolve_status' => $readiness['pointer_resolve_status'],
                'prices' => [],
                'missing_tickers' => $requestedCodes,
            ];
        }

        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
        if (! $publication) {
            return [
                'trade_date' => $tradeDate,
                'trade_date_effective' => null,
                'publication_id' => null,
                'publication_version' => null,
                'run_id' => null,
                'is_ready' => false,
                'reason_code' => 'NO_READABLE_PUBLICATION',
                'pointer_resolve_status' => 'NOT_RESOLVED_READABLE_CURRENT',
                'prices' => [],
                'missing_tickers' => $requestedCodes,
            ];
        }

        $run = DB::table('eod_runs')->where('run_id', $publication->run_id)->first();
        $priceResult = $this->prices->pricesForReadablePublication($publication, $requestedCodes);
        $sourceName = $run ? $run->source_name : null;

        foreach ($priceResult['prices'] as &$price) {
            $price['publication_id'] = (int) $publication->publication_id;
            $price['publication_version'] = (int) $publication->publication_version;
            $price['run_id'] = (int) $publication->run_id;
            $price['trade_date_effective'] = $readiness['trade_date_effective'];
            $price['source_name'] = $sourceName ?: $price['source_name'];
        }
        unset($price);

        return [
            'trade_date' => $tradeDate,
            'trade_date_effective' => $readiness['trade_date_effective'],
            'publication_id' => (int) $publication->publication_id,
            'publication_version' => (int) $publication->publication_version,
            'run_id' => (int) $publication->run_id,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
            'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'prices' => $priceResult['prices'],
            'missing_tickers' => $priceResult['missing_tickers'],
        ];
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
