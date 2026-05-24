<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketBenchmarkReadRepository;

class MarketBenchmarkReadService
{
    private MarketDataReadinessService $readiness;
    private MarketBenchmarkReadRepository $benchmarks;

    public function __construct(
        MarketDataReadinessService $readiness = null,
        MarketBenchmarkReadRepository $benchmarks = null
    ) {
        $this->readiness = $readiness ?: new MarketDataReadinessService();
        $this->benchmarks = $benchmarks ?: new MarketBenchmarkReadRepository();
    }

    public function getBenchmarkMarketDataForTradeDate(string $tradeDate, string $benchmarkCode = 'IHSG'): array
    {
        $readiness = $this->readiness->readinessForTradeDate($tradeDate);
        if (! $readiness['is_ready']) {
            return [
                'trade_date' => $tradeDate,
                'is_ready' => false,
                'reason_code' => $readiness['reason_code'],
                'pointer_resolve_status' => $readiness['pointer_resolve_status'],
                'benchmark' => null,
            ];
        }

        $benchmark = $this->benchmarks->getBenchmarkContext($benchmarkCode, $tradeDate);

        return [
            'trade_date' => $tradeDate,
            'is_ready' => true,
            'reason_code' => 'READABLE_PUBLICATION_RESOLVED',
            'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
            'publication_id' => $readiness['publication_id'],
            'publication_version' => $readiness['publication_version'],
            'run_id' => $readiness['run_id'],
            'benchmark' => $benchmark,
        ];
    }
}
