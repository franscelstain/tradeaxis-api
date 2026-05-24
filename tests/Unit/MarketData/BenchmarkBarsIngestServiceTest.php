<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\BenchmarkBarsIngestService;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\MarketBenchmarkRepository;
use PHPUnit\Framework\TestCase;

class BenchmarkBarsIngestServiceTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    public function test_benchmark_bars_ingest_writes_active_benchmark_rows_deterministically()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
            ],
        ]);

        $adapter = $this->createMock(PublicApiEodBarsAdapter::class);
        $repository = $this->createMock(MarketBenchmarkRepository::class);

        $benchmarks = [[
            'benchmark_code' => 'IHSG',
            'provider' => 'yahoo_finance',
            'provider_symbol' => '^JKSE',
            'instrument_type' => 'INDEX',
        ]];

        $repository->expects($this->once())
            ->method('activeBenchmarks')
            ->willReturn($benchmarks);

        $adapter->expects($this->once())
            ->method('fetchOrLoadBenchmarkBars')
            ->with('2026-05-19', 'api', $benchmarks)
            ->willReturn([[
                'benchmark_code' => 'IHSG',
                'trade_date' => '2026-05-19',
                'open' => 7100,
                'high' => 7150,
                'low' => 7050,
                'close' => 7125,
                'adj_close' => 7125,
                'volume' => 0,
                'provider' => 'yahoo_finance',
                'provider_symbol' => '^JKSE',
            ]]);

        $repository->expects($this->once())
            ->method('replaceBars')
            ->with($this->callback(function (array $rows) {
                return count($rows) === 1
                    && $rows[0]['benchmark_code'] === 'IHSG'
                    && $rows[0]['trade_date'] === '2026-05-19'
                    && (float) $rows[0]['close_price'] === 7125.0
                    && $rows[0]['provider_symbol'] === '^JKSE';
            }));

        $service = new BenchmarkBarsIngestService($adapter, $repository);
        $result = $service->ingest('2026-05-19', 'api');

        $this->assertSame('COMPLETED', $result['benchmark_import_status']);
        $this->assertSame(1, $result['benchmark_rows_written']);
        $this->assertSame(['IHSG'], $result['benchmark_codes']);
    }

    public function test_benchmark_bars_ingest_skips_non_api_source_mode_without_touching_provider()
    {
        $adapter = $this->createMock(PublicApiEodBarsAdapter::class);
        $repository = $this->createMock(MarketBenchmarkRepository::class);

        $repository->expects($this->never())->method('activeBenchmarks');
        $adapter->expects($this->never())->method('fetchOrLoadBenchmarkBars');
        $repository->expects($this->never())->method('replaceBars');

        $service = new BenchmarkBarsIngestService($adapter, $repository);
        $result = $service->ingest('2026-05-19', 'manual_file');

        $this->assertSame('SKIPPED', $result['benchmark_import_status']);
        $this->assertSame('BENCHMARK_SOURCE_MODE_NOT_API', $result['benchmark_skip_reason_code']);
    }
}
