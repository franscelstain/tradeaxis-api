<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataReadProductRepository;
use Illuminate\Support\Facades\DB;

class MarketDataReadProductService
{
    private $publications;
    private $readiness;
    private $rows;

    public function __construct(
        EodPublicationRepository $publications = null,
        MarketDataReadinessService $readiness = null,
        MarketDataReadProductRepository $rows = null
    ) {
        $this->publications = $publications ?: new EodPublicationRepository();
        $this->readiness = $readiness ?: new MarketDataReadinessService($this->publications);
        $this->rows = $rows ?: new MarketDataReadProductRepository();
    }

    public function getReadProductForTradeDate(string $tradeDate): array
    {
        $readiness = $this->readiness->readinessForTradeDate($tradeDate);
        if (! $readiness['is_ready']) {
            return $this->emptyPayload($readiness);
        }

        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
        if (! $publication) {
            return $this->emptyPayload($this->readiness->readinessForTradeDate($tradeDate));
        }

        $run = DB::table('eod_runs')->where('run_id', $publication->run_id)->first();
        $rows = $this->rows->rowsForReadablePublication($publication);
        foreach ($rows as &$row) {
            $row['publication_id'] = (int) $publication->publication_id;
            $row['publication_version'] = (int) $publication->publication_version;
            $row['run_id'] = (int) $publication->run_id;
            $row['trade_date_effective'] = $readiness['trade_date_effective'];
            $row['source_name'] = ($run && $run->source_name) ? $run->source_name : $row['source_name'];
        }
        unset($row);

        return [
            'product_code' => (string) config('market_data.scope.canonical_product_code', 'IDX_REGULAR_EOD_RAW_V1'),
            'read_model_version' => 'market_data_read_product_v1',
            'trade_date' => $tradeDate,
            'trade_date_effective' => $readiness['trade_date_effective'],
            'publication_id' => (int) $publication->publication_id,
            'publication_version' => (int) $publication->publication_version,
            'run_id' => (int) $publication->run_id,
            'is_ready' => true,
            // Carried through to the consumer rather than stopping at readiness: a reader deciding
            // whether to act on this product needs to know it came from a development frontier.
            'activation_state' => $readiness['activation_state'] ?? null,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
            'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'rows' => $rows,
            'missing_tickers' => [],
        ];
    }

    private function emptyPayload(array $readiness): array
    {
        return [
            'product_code' => (string) config('market_data.scope.canonical_product_code', 'IDX_REGULAR_EOD_RAW_V1'),
            'read_model_version' => 'market_data_read_product_v1',
            'trade_date' => $readiness['trade_date'],
            'trade_date_effective' => null,
            'publication_id' => null,
            'publication_version' => null,
            'run_id' => null,
            'is_ready' => false,
            'activation_state' => $readiness['activation_state'] ?? null,
            'reason_code' => $readiness['reason_code'],
            'pointer_resolve_status' => $readiness['pointer_resolve_status'],
            'rows' => [],
            'missing_tickers' => [],
        ];
    }
}
