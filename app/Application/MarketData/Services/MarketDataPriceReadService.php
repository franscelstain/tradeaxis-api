<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataPriceReadRepository;
use Illuminate\Support\Facades\DB;

class MarketDataPriceReadService
{
    private $publications;
    private $readiness;
    private $prices;

    public function __construct(EodPublicationRepository $publications = null, MarketDataReadinessService $readiness = null, MarketDataPriceReadRepository $prices = null)
    {
        $this->publications = $publications ?: new EodPublicationRepository();
        $this->readiness = $readiness ?: new MarketDataReadinessService($this->publications);
        $this->prices = $prices ?: new MarketDataPriceReadRepository($this->publications);
    }

    public function getPriceFacts(string $tradeDate, array $tickerCodes): array
    {
        $requested = $this->normalize($tickerCodes);
        $readiness = $this->readiness->readinessForTradeDate($tradeDate);
        if (! $readiness['is_ready']) return $this->blocked($tradeDate, $requested, $readiness);

        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
        if (! $publication) return $this->blocked($tradeDate, $requested, ['reason_code' => 'NO_READABLE_PUBLICATION', 'pointer_resolve_status' => 'NOT_RESOLVED_READABLE_CURRENT']);

        $result = $this->prices->priceFactsForReadablePublication($publication, $requested);
        $run = DB::table('eod_runs')->where('run_id', $publication->run_id)->first();
        foreach ($result['prices'] as &$price) {
            $price['publication_id'] = (int) $publication->publication_id;
            $price['publication_version'] = (int) $publication->publication_version;
            $price['run_id'] = (int) $publication->run_id;
            $price['trade_date_effective'] = $readiness['trade_date_effective'];
            $price['source_name'] = ($run && $run->source_name) ? $run->source_name : $price['source_name'];
        }
        unset($price);

        return [
            'trade_date' => $tradeDate, 'trade_date_effective' => $readiness['trade_date_effective'],
            'publication_id' => (int) $publication->publication_id, 'publication_version' => (int) $publication->publication_version,
            'run_id' => (int) $publication->run_id, 'is_ready' => true,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED', 'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'prices' => $result['prices'], 'missing_tickers' => $result['missing_tickers'],
        ];
    }

    private function blocked($date, array $codes, array $readiness): array
    {
        return [
            'trade_date' => $date, 'trade_date_effective' => null, 'publication_id' => null,
            'publication_version' => null, 'run_id' => null, 'is_ready' => false,
            'reason_code' => $readiness['reason_code'], 'pointer_resolve_status' => $readiness['pointer_resolve_status'],
            'prices' => [], 'missing_tickers' => $codes,
        ];
    }

    private function normalize(array $codes): array
    {
        $result = [];
        foreach ($codes as $code) { $code = strtoupper(trim((string) $code)); if ($code !== '') $result[$code] = $code; }
        return array_values($result);
    }
}
