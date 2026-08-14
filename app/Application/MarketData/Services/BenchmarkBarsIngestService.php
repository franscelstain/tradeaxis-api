<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\Ports\ApiEodBarsSource;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository;
use Carbon\Carbon;

class BenchmarkBarsIngestService
{
    private $apiSourceAdapter;
    private $benchmarks;

    public function __construct(ApiEodBarsSource $apiSourceAdapter, MarketBenchmarkRepository $benchmarks)
    {
        $this->apiSourceAdapter = $apiSourceAdapter;
        $this->benchmarks = $benchmarks;
    }

    public function ingest($requestedDate, $sourceMode)
    {
        if ($sourceMode !== 'api') {
            return [
                'benchmark_import_status' => 'SKIPPED',
                'benchmark_skip_reason_code' => 'BENCHMARK_SOURCE_MODE_NOT_API',
                'benchmark_rows_written' => 0,
            ];
        }

        $apiProvider = (string) config('market_data.source.api.provider', 'yahoo_finance');
        $activeBenchmarks = $this->benchmarks->activeBenchmarksForProvider($apiProvider);
        if (empty($activeBenchmarks)) {
            return [
                'benchmark_import_status' => 'SKIPPED',
                'benchmark_skip_reason_code' => 'BENCHMARK_MASTER_EMPTY',
                'benchmark_rows_written' => 0,
            ];
        }

        $sourceRows = $this->apiSourceAdapter->fetchOrLoadBenchmarkBars($requestedDate, $sourceMode, $activeBenchmarks);
        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $rows = [];

        foreach ($sourceRows as $row) {
            $this->assertValidBenchmarkBar($row, $requestedDate);

            $rows[] = [
                'benchmark_code' => $row['benchmark_code'],
                'trade_date' => $requestedDate,
                'open_price' => $row['open'],
                'high_price' => $row['high'],
                'low_price' => $row['low'],
                'close_price' => $row['close'],
                'adjusted_close' => $row['adj_close'],
                'volume' => $row['volume'],
                'provider' => $row['provider'],
                'provider_symbol' => $row['provider_symbol'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->benchmarks->replaceBars($rows);

        return [
            'benchmark_import_status' => 'COMPLETED',
            'benchmark_rows_written' => count($rows),
            'benchmark_codes' => array_values(array_map(function ($row) {
                return $row['benchmark_code'];
            }, $rows)),
        ];
    }

    private function assertValidBenchmarkBar(array $row, $requestedDate)
    {
        foreach (['benchmark_code', 'trade_date', 'open', 'high', 'low', 'close', 'provider', 'provider_symbol'] as $field) {
            if (! isset($row[$field]) || $row[$field] === '' || $row[$field] === null) {
                throw new SourceAcquisitionException('Benchmark source row missing required field: '.$field, 'RUN_SOURCE_NO_VALID_DATA');
            }
        }

        if ((string) $row['trade_date'] !== (string) $requestedDate) {
            throw new SourceAcquisitionException('Benchmark source row trade_date mismatched requested_date.', 'RUN_STALE_DATA');
        }

        foreach (['open', 'high', 'low', 'close'] as $field) {
            if ((float) $row[$field] <= 0) {
                throw new SourceAcquisitionException('Benchmark source row has non-positive price at '.$field.'.', 'RUN_SOURCE_NO_VALID_DATA');
            }
        }
    }
}
