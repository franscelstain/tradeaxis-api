<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\EodBarsIngestService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use App\Models\EodRun;
use PHPUnit\Framework\TestCase;

class EodBarsIngestServiceTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function tearDown(): void
    {
        $this->clearMarketDataConfig();

        parent::tearDown();
    }


    public function test_api_ingest_uses_ticker_universe_when_fetching_provider_rows()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'YAHOO_FINANCE'],
            ],
        ]);

        $localSource = $this->createMock(LocalFileEodBarsAdapter::class);
        $apiSource = $this->createMock(PublicApiEodBarsAdapter::class);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);

        $run = new EodRun([
            'run_id' => 56,
            'trade_date_requested' => '2026-03-24',
        ]);

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-03-24')
            ->willReturn(null);

        $localSource->expects($this->never())
            ->method('fetchOrLoadEodBars');

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-03-24')
            ->willReturn([
                ['ticker_id' => 1, 'ticker_code' => 'BBCA'],
                ['ticker_id' => 2, 'ticker_code' => 'BBRI'],
            ]);

        $apiSource->expects($this->once())
            ->method('fetchOrLoadEodBars')
            ->with('2026-03-24', 'api', ['BBCA', 'BBRI'])
            ->willReturn([
                [
                    'ticker_code' => 'BBCA',
                    'trade_date' => '2026-03-24',
                    'open' => 100,
                    'high' => 110,
                    'low' => 99,
                    'close' => 108,
                    'volume' => 1000,
                    'adj_close' => 108,
                    'source_name' => 'YAHOO_FINANCE',
                    'source_row_ref' => 'yahoo:BBCA:2026-03-24',
                    'captured_at' => '2026-03-24T17:00:00+07:00',
                ],
                [
                    'ticker_code' => 'BBRI',
                    'trade_date' => '2026-03-24',
                    'open' => 200,
                    'high' => 210,
                    'low' => 198,
                    'close' => 205,
                    'volume' => 1500,
                    'adj_close' => 205,
                    'source_name' => 'YAHOO_FINANCE',
                    'source_row_ref' => 'yahoo:BBRI:2026-03-24',
                    'captured_at' => '2026-03-24T17:00:00+07:00',
                ],
            ]);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['BBCA', 'BBRI'])
            ->willReturn(['BBCA' => 1, 'BBRI' => 2]);

        $publications->expects($this->once())
            ->method('getOrCreateCandidatePublication')
            ->willReturn((object) [
                'publication_id' => 701,
                'publication_version' => 1,
            ]);

        $artifacts->expects($this->once())
            ->method('replaceBars')
            ->with(
                '2026-03-24',
                701,
                56,
                $this->callback(function (array $validRows) {
                    return count($validRows) === 2
                        && $validRows[0]['source'] === 'YAHOO_FINANCE'
                        && $validRows[1]['source'] === 'YAHOO_FINANCE';
                }),
                $this->callback(function (array $invalidRows) {
                    return count($invalidRows) === 0;
                }),
                false
            );

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications);

        $result = $service->ingest($run, '2026-03-24', 'api');

        $this->assertSame(2, $result['bars_rows_written']);
        $this->assertSame(0, $result['invalid_bar_count']);
        $this->assertSame('YAHOO_FINANCE', $result['source_name']);
        $this->assertSame('eod_bars', $result['storage_target']);
    }

    public function test_single_day_ingest_rejects_mixed_source_names_within_one_run_boundary()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'YAHOO_FINANCE'],
            ],
        ]);

        $localSource = $this->createMock(LocalFileEodBarsAdapter::class);
        $apiSource = $this->createMock(PublicApiEodBarsAdapter::class);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);

        $run = new EodRun([
            'run_id' => 58,
            'trade_date_requested' => '2026-03-24',
        ]);

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-03-24')
            ->willReturn(null);

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-03-24')
            ->willReturn([
                ['ticker_id' => 1, 'ticker_code' => 'BBCA'],
                ['ticker_id' => 2, 'ticker_code' => 'BBRI'],
            ]);

        $apiSource->expects($this->once())
            ->method('fetchOrLoadEodBars')
            ->with('2026-03-24', 'api', ['BBCA', 'BBRI'])
            ->willReturn([
                [
                    'ticker_code' => 'BBCA',
                    'trade_date' => '2026-03-24',
                    'open' => 100,
                    'high' => 110,
                    'low' => 99,
                    'close' => 108,
                    'volume' => 1000,
                    'adj_close' => 108,
                    'source_name' => 'YAHOO_FINANCE',
                    'source_row_ref' => 'yahoo:BBCA:2026-03-24',
                    'captured_at' => '2026-03-24T17:00:00+07:00',
                ],
                [
                    'ticker_code' => 'BBRI',
                    'trade_date' => '2026-03-24',
                    'open' => 200,
                    'high' => 210,
                    'low' => 198,
                    'close' => 205,
                    'volume' => 1500,
                    'adj_close' => 205,
                    'source_name' => 'ALT_PROVIDER',
                    'source_row_ref' => 'alt:BBRI:2026-03-24',
                    'captured_at' => '2026-03-24T17:00:00+07:00',
                ],
            ]);

        $tickers->expects($this->never())
            ->method('resolveTickerIdsByCodes');

        $publications->expects($this->never())
            ->method('getOrCreateCandidatePublication');

        $artifacts->expects($this->never())
            ->method('replaceBars');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('mixed source_name rows');

        $service->ingest($run, '2026-03-24', 'api');
    }

    public function test_api_ingest_returns_source_acquisition_summary_from_adapter()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'YAHOO_FINANCE'],
            ],
        ]);

        $localSource = $this->createMock(LocalFileEodBarsAdapter::class);
        $apiSource = $this->createMock(PublicApiEodBarsAdapter::class);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);

        $run = new EodRun([
            'run_id' => 57,
            'trade_date_requested' => '2026-03-24',
        ]);

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-03-24')
            ->willReturn(null);

        $tickers->expects($this->once())
            ->method('getUniverseForTradeDate')
            ->with('2026-03-24')
            ->willReturn([
                ['ticker_id' => 1, 'ticker_code' => 'BBCA'],
            ]);

        $apiSource->expects($this->once())
            ->method('fetchOrLoadEodBars')
            ->with('2026-03-24', 'api', ['BBCA'])
            ->willReturn([
                [
                    'ticker_code' => 'BBCA',
                    'trade_date' => '2026-03-24',
                    'open' => 100,
                    'high' => 110,
                    'low' => 99,
                    'close' => 108,
                    'volume' => 1000,
                    'adj_close' => 108,
                    'source_name' => 'YAHOO_FINANCE',
                    'source_row_ref' => 'yahoo:BBCA:2026-03-24',
                    'captured_at' => '2026-03-24T17:00:00+07:00',
                ],
            ]);

        $apiSource->expects($this->once())
            ->method('consumeLastAcquisitionTelemetry')
            ->willReturn([
                'provider' => 'yahoo_finance',
                'source_name' => 'YAHOO_FINANCE',
                'attempt_count' => 2,
                'success_after_retry' => true,
                'final_http_status' => 200,
            ]);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['BBCA'])
            ->willReturn(['BBCA' => 1]);

        $publications->expects($this->once())
            ->method('getOrCreateCandidatePublication')
            ->willReturn((object) [
                'publication_id' => 702,
                'publication_version' => 2,
            ]);

        $artifacts->expects($this->once())
            ->method('replaceBars');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications);

        $result = $service->ingest($run, '2026-03-24', 'api');

        $this->assertSame(2, $result['source_acquisition']['attempt_count']);
        $this->assertTrue($result['source_acquisition']['success_after_retry']);
        $this->assertSame(200, $result['source_acquisition']['final_http_status']);
    }


    public function test_unknown_ticker_code_is_written_as_invalid_row_instead_of_failing_whole_ingest()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'LOCAL_FILE'],
            ],
        ]);

        $localSource = $this->createMock(LocalFileEodBarsAdapter::class);
        $apiSource = $this->createMock(PublicApiEodBarsAdapter::class);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);

        $run = new EodRun([
            'run_id' => 55,
            'trade_date_requested' => '2026-03-24',
        ]);

        $sourceRows = [
            [
                'ticker_code' => 'BBCA',
                'trade_date' => '2026-03-24',
                'open' => 100,
                'high' => 110,
                'low' => 99,
                'close' => 108,
                'volume' => 1000,
                'adj_close' => 108,
                'source_name' => 'LOCAL_FILE',
                'source_row_ref' => 'row-1',
                'captured_at' => '2026-03-24T17:00:00+07:00',
            ],
            [
                'ticker_code' => 'XXXX',
                'trade_date' => '2026-03-24',
                'open' => 50,
                'high' => 55,
                'low' => 49,
                'close' => 54,
                'volume' => 200,
                'adj_close' => 54,
                'source_name' => 'LOCAL_FILE',
                'source_row_ref' => 'row-2',
                'captured_at' => '2026-03-24T17:00:00+07:00',
            ],
        ];

        $apiSource->expects($this->never())
            ->method('fetchOrLoadEodBars');

        $localSource->expects($this->once())
            ->method('fetchOrLoadEodBars')
            ->with('2026-03-24', 'manual_file')
            ->willReturn($sourceRows);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['BBCA', 'XXXX'])
            ->willReturn(['BBCA' => 1]);

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-03-24')
            ->willReturn(null);

        $publications->expects($this->once())
            ->method('getOrCreateCandidatePublication')
            ->willReturn((object) [
                'publication_id' => 700,
                'publication_version' => 3,
            ]);

        $artifacts->expects($this->once())
            ->method('replaceBars')
            ->with(
                '2026-03-24',
                700,
                55,
                $this->callback(function (array $validRows) {
                    return count($validRows) === 1
                        && $validRows[0]['ticker_id'] === 1
                        && $validRows[0]['source'] === 'LOCAL_FILE';
                }),
                $this->callback(function (array $invalidRows) {
                    return count($invalidRows) === 1
                        && $invalidRows[0]['ticker_id'] === null
                        && $invalidRows[0]['source'] === 'LOCAL_FILE'
                        && $invalidRows[0]['invalid_reason_code'] === 'BAR_TICKER_MAPPING_MISSING'
                        && $invalidRows[0]['invalid_note'] === 'ticker_code not found in ticker master: XXXX';
                }),
                false
            );

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications);

        $result = $service->ingest($run, '2026-03-24', 'manual_file');

        $this->assertSame(1, $result['bars_rows_written']);
        $this->assertSame(1, $result['invalid_bar_count']);
        $this->assertSame('LOCAL_FILE', $result['source_name']);
        $this->assertSame('eod_bars', $result['storage_target']);
    }
}
