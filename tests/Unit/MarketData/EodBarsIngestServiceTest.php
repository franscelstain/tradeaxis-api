<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\EodBarsIngestService;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
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

    private function completedCalendar(): MarketCalendarRepository
    {
        $calendar = $this->createMock(MarketCalendarRepository::class);
        $calendar->method('assertCompletedRegularSession')->willReturn([
            'calendar_revision_id' => 7001,
            'revision_uid' => hash('sha256', 'unit-test-calendar-revision'),
            'source_version' => 'idx-unit-test-v1',
        ]);

        return $calendar;
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
                    'source_observation_id' => 901,
                    'source_observation_persisted' => true,
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
                    'source_observation_id' => 902,
                    'source_observation_persisted' => true,
                ],
            ]);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['BBCA', 'BBRI'])
            ->willReturn(['BBCA' => 1, 'BBRI' => 2]);

        $tickers->method('resolveTemporalContextsByCodes')
            ->willReturnCallback(function (array $codes) {
                $contexts = [];
                foreach ($codes as $index => $code) {
                    $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
                }

                return $contexts;
            });

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

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

        $result = $service->ingest($run, '2026-03-24', 'api');

        $this->assertSame(2, $result['bars_rows_written']);
        $this->assertSame(0, $result['invalid_bar_count']);
        $this->assertSame('YAHOO_FINANCE', $result['source_name']);
        $this->assertSame('eod_bars', $result['storage_target']);
    }

    public function test_acquired_rows_can_preserve_canonical_source_with_single_source_boundary()
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
            'run_id' => 59,
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
                'source_name' => 'YAHOO_FINANCE',
                'canonical_source' => 'API_FREE',
                'source_row_ref' => 'current:2026-03-24:BBCA',
                'captured_at' => '2026-03-24T17:00:00+07:00',
                'source_observation_id' => 903,
                'source_observation_persisted' => true,
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
                'source_observation_id' => 904,
                'source_observation_persisted' => true,
            ],
        ];

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-03-24')
            ->willReturn(null);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['BBCA', 'BBRI'])
            ->willReturn(['BBCA' => 1, 'BBRI' => 2]);

        $tickers->method('resolveTemporalContextsByCodes')
            ->willReturnCallback(function (array $codes) {
                $contexts = [];
                foreach ($codes as $index => $code) {
                    $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
                }

                return $contexts;
            });

        $publications->expects($this->once())
            ->method('getOrCreateCandidatePublication')
            ->willReturn((object) [
                'publication_id' => 703,
                'publication_version' => 3,
            ]);

        $artifacts->expects($this->once())
            ->method('replaceBars')
            ->with(
                '2026-03-24',
                703,
                59,
                $this->callback(function (array $validRows) {
                    return count($validRows) === 2
                        && $validRows[0]['source'] === 'API_FREE'
                        && $validRows[1]['source'] === 'YAHOO_FINANCE';
                }),
                $this->callback(function (array $invalidRows) {
                    return count($invalidRows) === 0;
                }),
                false
            );

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

        $result = $service->ingestAcquiredRows($run, '2026-03-24', 'api', $sourceRows, [
            'source_acquisition_state' => 'SUCCESS',
        ]);

        $this->assertSame(2, $result['bars_rows_written']);
        $this->assertSame('YAHOO_FINANCE', $result['source_name']);
    }

    public function test_long_manual_canonical_source_is_stored_as_bounded_source_code()
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
            'run_id' => 60,
            'trade_date_requested' => '2026-03-24',
        ]);

        $sourceRows = [[
            'ticker_code' => 'MASA',
            'trade_date' => '2026-03-24',
            'open' => 2230,
            'high' => 2230,
            'low' => 2230,
            'close' => 2230,
            'volume' => 0,
            'adj_close' => 2230,
            'source_name' => 'LOCAL_FILE',
            'canonical_source' => 'IDX_STOCK_SUMMARY_NO_TRADE_CLOSE_CARRY_FORWARD',
            'source_row_ref' => 'https://www.idx.id/primary/TradingSummary/GetStockSummary?date=20260324&start=0&length=1000#MASA',
            'captured_at' => '2026-03-24T17:00:00+07:00',
            'source_observation_id' => 905,
            'source_observation_persisted' => true,
        ]];

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-03-24')
            ->willReturn(null);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['MASA'])
            ->willReturn(['MASA' => 10]);

        $tickers->method('resolveTemporalContextsByCodes')
            ->willReturnCallback(function (array $codes) {
                $contexts = [];
                foreach ($codes as $index => $code) {
                    $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
                }

                return $contexts;
            });

        $publications->expects($this->once())
            ->method('getOrCreateCandidatePublication')
            ->willReturn((object) [
                'publication_id' => 704,
                'publication_version' => 4,
            ]);

        $artifacts->expects($this->once())
            ->method('replaceBars')
            ->with(
                '2026-03-24',
                704,
                60,
                $this->callback(function (array $validRows) {
                    return count($validRows) === 1
                        && $validRows[0]['source'] === 'IDX_NO_TRADE_CARRY_FORWARD'
                        && strlen($validRows[0]['source']) <= 32
                        && (int) $validRows[0]['volume'] === 0;
                }),
                $this->callback(function (array $invalidRows) {
                    return count($invalidRows) === 0;
                }),
                false
            );

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

        $result = $service->ingestAcquiredRows($run, '2026-03-24', 'manual_file', $sourceRows, [
            'source_acquisition_state' => 'SUCCESS',
        ]);

        $this->assertSame(1, $result['bars_rows_written']);
        $this->assertSame('LOCAL_FILE', $result['source_name']);
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
                    'source_observation_id' => 906,
                    'source_observation_persisted' => true,
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
                    'source_observation_id' => 907,
                    'source_observation_persisted' => true,
                ],
            ]);

        $tickers->expects($this->never())
            ->method('resolveTickerIdsByCodes');

        $publications->expects($this->never())
            ->method('getOrCreateCandidatePublication');

        $artifacts->expects($this->never())
            ->method('replaceBars');

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

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
                    'source_observation_id' => 908,
                    'source_observation_persisted' => true,
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

        $tickers->method('resolveTemporalContextsByCodes')
            ->willReturnCallback(function (array $codes) {
                $contexts = [];
                foreach ($codes as $index => $code) {
                    $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
                }

                return $contexts;
            });

        $publications->expects($this->once())
            ->method('getOrCreateCandidatePublication')
            ->willReturn((object) [
                'publication_id' => 702,
                'publication_version' => 2,
            ]);

        $artifacts->expects($this->once())
            ->method('replaceBars');

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

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
                'source_observation_id' => 909,
                'source_observation_persisted' => true,
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
                'source_observation_id' => 910,
                'source_observation_persisted' => true,
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

        $tickers->method('resolveTemporalContextsByCodes')
            ->willReturnCallback(function (array $codes) {
                $contexts = [];
                foreach ($codes as $index => $code) {
                    $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
                }

                return $contexts;
            });

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

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

        $result = $service->ingest($run, '2026-03-24', 'manual_file');

        $this->assertSame(1, $result['bars_rows_written']);
        $this->assertSame(1, $result['invalid_bar_count']);
        $this->assertSame('LOCAL_FILE', $result['source_name']);
        $this->assertSame('eod_bars', $result['storage_target']);
    }

    public function test_zero_canonical_rows_preserve_acquisition_telemetry_and_expose_invalid_reason_summary()
    {
        $this->bindMarketDataConfig([
            'market_data' => [
                'platform' => ['timezone' => 'Asia/Jakarta'],
                'source' => ['default_source_name' => 'API_FREE'],
            ],
        ]);

        $localSource = $this->createMock(LocalFileEodBarsAdapter::class);
        $apiSource = $this->createMock(PublicApiEodBarsAdapter::class);
        $tickers = $this->createMock(TickerMasterRepository::class);
        $artifacts = $this->createMock(EodArtifactRepository::class);
        $publications = $this->createMock(EodPublicationRepository::class);

        $run = new EodRun([
            'run_id' => 61,
            'trade_date_requested' => '2026-06-09',
        ]);

        $sourceRows = [[
            'ticker_code' => 'BBCA',
            'trade_date' => '2026-06-09',
            'open' => 9000,
            'high' => 9100,
            'low' => 8950,
            'close' => 9050,
            'volume' => 1000,
            'adj_close' => 9050,
            'source_name' => 'API_FREE',
            'source_row_ref' => 'yahoo:BBCA:2026-06-09',
            'captured_at' => '2026-06-10 11:42:34',
            'source_observation_id' => 911,
            'source_observation_persisted' => true,
        ]];

        $publications->expects($this->once())
            ->method('findCurrentPublicationForTradeDate')
            ->with('2026-06-09')
            ->willReturn(null);

        $tickers->expects($this->once())
            ->method('resolveTickerIdsByCodes')
            ->with(['BBCA'])
            ->willReturn([]);

        $tickers->method('resolveTemporalContextsByCodes')
            ->willReturnCallback(function (array $codes) {
                $contexts = [];
                foreach ($codes as $index => $code) {
                    $contexts[$code] = ['listing_id' => 5000 + $index, 'board_code' => 'RG'];
                }

                return $contexts;
            });

        $publications->expects($this->never())->method('getOrCreateCandidatePublication');
        $artifacts->expects($this->never())->method('replaceBars');

        $observations = $this->createMock(SourceObservationRepository::class);
        $observations->method('existsAccepted')->willReturn(true);
        $observations->method('manifestHashForRun')->willReturn('manifest-hash-test');

        $service = new EodBarsIngestService($localSource, $apiSource, $tickers, $artifacts, $publications, null, $observations, $this->completedCalendar());

        try {
            $service->ingestAcquiredRows($run, '2026-06-09', 'api', $sourceRows, [
                'source_acquisition_batch_id' => 'API_20260609_20260609_001',
                'source_acquisition_state' => 'SUCCESS',
                'expected_ticker_count' => 1,
                'success_ticker_count' => 1,
            ]);
            $this->fail('Expected SourceAcquisitionException was not thrown.');
        } catch (SourceAcquisitionException $e) {
            $context = $e->context();
            $this->assertSame('API_20260609_20260609_001', $context['source_acquisition_batch_id']);
            $this->assertSame(1, $context['returned_row_count']);
            $this->assertSame(1, $context['invalid_row_count']);
            $this->assertSame(['BAR_TICKER_MAPPING_MISSING' => 1], $context['invalid_reason_summary']);
            $this->assertSame('BAR_TICKER_MAPPING_MISSING', $context['invalid_rows_sample'][0]['invalid_reason_code']);
        }
    }

}
