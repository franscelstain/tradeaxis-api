<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository;
use Carbon\Carbon;

class BenchmarkIndicatorComputeService
{
    private $benchmarks;
    private $vectors;

    public function __construct(MarketBenchmarkRepository $benchmarks, BenchmarkIndicatorVectorService $vectors)
    {
        $this->benchmarks = $benchmarks;
        $this->vectors = $vectors;
    }

    public function compute($requestedDate, $historyStartDate = null)
    {
        $rows = [];
        $invalid = 0;
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $config = ['set_version' => config('market_data.indicators.set_version')];

        foreach ($this->benchmarks->activeBenchmarks() as $benchmark) {
            $code = (string) $benchmark['benchmark_code'];
            $bars = $this->benchmarks->loadBarsWindow($code, $requestedDate, 50, $historyStartDate);
            $row = $this->vectors->buildRow($code, $bars, $requestedDate, $now, $config);

            if ($row === null) {
                continue;
            }

            if ((int) $row['is_valid'] === 0) {
                $invalid++;
            }

            $rows[] = $row;
        }

        $this->benchmarks->replaceIndicators($rows);

        return [
            'benchmark_indicators_rows_written' => count($rows),
            'invalid_benchmark_indicator_count' => $invalid,
        ];
    }

    public function roc20($benchmarkCode, $requestedDate)
    {
        return $this->benchmarks->benchmarkRoc20(
            $benchmarkCode,
            $requestedDate,
            config('market_data.indicators.set_version')
        );
    }

    public function roc20s(array $benchmarkCodes, $requestedDate)
    {
        return $this->benchmarks->benchmarkRoc20s(
            $benchmarkCodes,
            $requestedDate,
            config('market_data.indicators.set_version')
        );
    }
}
