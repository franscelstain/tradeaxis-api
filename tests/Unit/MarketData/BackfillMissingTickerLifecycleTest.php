<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\Services\ApiBackfillRangeAcquisitionService;
use App\Application\MarketData\Services\BackfillLifecycleOrchestrator;
use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\ReplayVerificationService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\TickerMasterRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BackfillMissingTickerLifecycleTest extends TestCase
{
    use InteractsWithMarketDataConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bindMarketDataConfig();
    }

    protected function tearDown(): void
    {
        m::close();
        $this->clearMarketDataConfig();

        parent::tearDown();
    }

    public function test_plan_scans_current_bar_gaps_against_ticker_master_universe(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, , , , , , $artifacts] = $this->makeOrchestrator();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([1]);
        $acquisition->shouldReceive('plan')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['BBB'])->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 1,
            'trading_date_count' => 1,
            'estimated_http_requests' => 1,
            'configured_concurrency' => 1,
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'plan' => true,
            'output_dir' => $this->tmpOutputDir('missing-plan'),
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('PLAN_ONLY', $summary['status']);
        $this->assertSame(1, $summary['ticker_count']);
        $this->assertSame(1, $summary['missing_bar_count']);
        $this->assertSame(1, $summary['missing_trade_date_count']);
        $this->assertSame(['BBB'], $summary['plan']['missing_ticker_codes_by_date']['2026-01-02']);
    }

    public function test_candidate_rows_include_current_bars_plus_missing_api_rows(): void
    {
        [$orchestrator, , , , , , , , , $artifacts] = $this->makeOrchestrator();

        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->with('2026-01-02', null)->andReturn([
            1 => [
                'publication_id' => 10,
                'trade_date' => '2026-01-02',
                'ticker_id' => 1,
                'open' => '100.000000',
                'high' => '110.000000',
                'low' => '99.000000',
                'close' => '105.000000',
                'volume' => 1000,
                'adj_close' => '105.000000',
                'source' => 'API_FREE',
                'created_at' => '2026-01-02 18:00:00',
            ],
        ]);

        $method = new ReflectionMethod($orchestrator, 'buildMissingTickerCandidateRows');
        $method->setAccessible(true);

        $rows = $method->invoke($orchestrator, '2026-01-02', [[
            'ticker_code' => 'BBB',
            'trade_date' => '2026-01-02',
            'open' => 50,
            'high' => 55,
            'low' => 49,
            'close' => 54,
            'volume' => 500,
            'adj_close' => 54,
            'source_name' => 'YAHOO_FINANCE',
        ]], ['BBB'], [
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);

        $this->assertSame(['AAA', 'BBB'], array_column($rows, 'ticker_code'));
        $this->assertSame('current:2026-01-02:AAA', $rows[0]['source_row_ref']);
        $this->assertSame(54, $rows[1]['close']);
        $this->assertSame('YAHOO_FINANCE', $rows[0]['source_name']);
        $this->assertSame('YAHOO_FINANCE', $rows[1]['source_name']);
        $this->assertSame('API_FREE', $rows[0]['canonical_source']);
        $this->assertArrayNotHasKey('canonical_source', $rows[1]);
    }

    public function test_ticker_filter_preserves_full_current_universe_in_candidate_rows(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, , , , , $artifacts] = $this->makeOrchestrator();

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([1]);
        $acquisition->shouldReceive('plan')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['BBB'])->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 1,
            'trading_date_count' => 1,
            'estimated_http_requests' => 1,
            'configured_concurrency' => 1,
        ]);
        $acquisition->shouldReceive('acquire')->once()->andReturn([
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_mode' => 'range_window',
            'window_count' => 1,
            'estimated_http_requests' => 1,
            'rows_by_trade_date' => [
                '2026-01-02' => [[
                    'ticker_code' => 'BBB',
                    'trade_date' => '2026-01-02',
                    'open' => 50,
                    'high' => 55,
                    'low' => 49,
                    'close' => 54,
                    'volume' => 500,
                    'adj_close' => 54,
                    'source_name' => 'YAHOO',
                ]],
            ],
            'date_telemetry' => [
                '2026-01-02' => [
                    'source_acquisition_state' => 'SUCCESS',
                    'source_final_status' => 'SUCCESS',
                    'success_ticker_count' => 1,
                    'failed_ticker_count' => 0,
                    'final_reason_code' => null,
                ],
            ],
            'window_telemetry' => [[
                'source_window_start' => '2026-01-02',
                'source_window_end' => '2026-01-02',
                'source_acquisition_state' => 'SUCCESS',
                'source_final_status' => 'SUCCESS',
                'failed_ticker_count' => 0,
            ]],
            'source_acquisition_checkpoints' => [],
        ]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->with('2026-01-02', null)->andReturn([
            1 => [
                'publication_id' => 10,
                'trade_date' => '2026-01-02',
                'ticker_id' => 1,
                'open' => '100.000000',
                'high' => '110.000000',
                'low' => '99.000000',
                'close' => '105.000000',
                'volume' => 1000,
                'adj_close' => '105.000000',
                'source' => 'YAHOO',
                'created_at' => '2026-01-02 18:00:00',
            ],
        ]);
        $pipeline->shouldReceive('importDailyFromAcquiredRows')->once()->with(
            '2026-01-02',
            'api',
            m::on(function ($rows) {
                return array_column($rows, 'ticker_code') === ['AAA', 'BBB']
                    && ($rows[0]['source_row_ref'] ?? null) === 'current:2026-01-02:AAA'
                    && (float) $rows[1]['close'] === 54.0;
            }),
            m::type('array')
        )->andReturn((object) [
            'run_id' => 901,
            'terminal_status' => 'SUCCESS',
            'notes' => 'bar_mutation_changed_count=1',
        ]);
        $pipeline->shouldReceive('promoteDaily')->once()->with('2026-01-02', 'api', 901, null)->andReturn((object) [
            'run_id' => 901,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => 1.0,
            'sealed_at' => '2026-01-02 19:00:00',
            'notes' => '',
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'ticker_codes' => 'BBB',
            'output_dir' => $this->tmpOutputDir('missing-filter-candidate'),
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('SUCCESS', $summary['status']);
        $this->assertSame(2, $summary['candidate_source_row_count']);
        $this->assertSame('SUCCESS', $summary['cases'][0]['status']);
    }

    public function test_plan_excludes_source_backed_suspended_tickers_from_missing_universe(): void
    {
        $eventRiskSources = m::mock(EventRiskSourceRepository::class);
        [$orchestrator, $calendar, $tickers, $acquisition, , , , , , $artifacts] = $this->makeOrchestrator($eventRiskSources);

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $eventRiskSources->shouldReceive('suspendedTickerIdsAsOf')->once()->with([1, 2], '2026-01-02')->andReturn([2]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $acquisition->shouldReceive('plan')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['AAA'])->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 1,
            'trading_date_count' => 1,
            'estimated_http_requests' => 1,
            'configured_concurrency' => 1,
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'plan' => true,
            'output_dir' => $this->tmpOutputDir('missing-suspended-universe'),
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('PLAN_ONLY', $summary['status']);
        $this->assertSame(['AAA'], $summary['plan']['missing_ticker_codes_by_date']['2026-01-02']);
        $this->assertSame(1, $summary['missing_bar_count']);
    }

    public function test_manual_file_missing_ticker_source_rows_can_build_lifecycle_candidate(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, , , , , $artifacts] = $this->makeOrchestrator();
        $inputFile = $this->tmpOutputDir('manual-source').'.csv';
        file_put_contents($inputFile, "ticker_code,trade_date,open,high,low,close,volume,canonical_source,source_row_ref\nBBB,2026-01-02,50,55,49,54,500,SEPUTARFOREX,seputarforex:BBB:2026-01-02\n");

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([1]);
        $acquisition->shouldReceive('plan')->never();
        $acquisition->shouldReceive('acquire')->never();
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->with('2026-01-02', null)->andReturn([
            1 => [
                'publication_id' => 10,
                'trade_date' => '2026-01-02',
                'ticker_id' => 1,
                'open' => '100.000000',
                'high' => '110.000000',
                'low' => '99.000000',
                'close' => '105.000000',
                'volume' => 1000,
                'adj_close' => '105.000000',
                'source' => 'YAHOO',
                'created_at' => '2026-01-02 18:00:00',
            ],
        ]);
        $pipeline->shouldReceive('importDailyFromAcquiredRows')->once()->with(
            '2026-01-02',
            'manual_file',
            m::on(function ($rows) {
                return array_column($rows, 'ticker_code') === ['AAA', 'BBB']
                    && ($rows[0]['source_name'] ?? null) === 'LOCAL_FILE'
                    && ($rows[0]['canonical_source'] ?? null) === 'YAHOO'
                    && ($rows[1]['source_name'] ?? null) === 'LOCAL_FILE'
                    && ($rows[1]['canonical_source'] ?? null) === 'SEPUTARFOREX'
                    && ($rows[1]['source_row_ref'] ?? null) === 'seputarforex:BBB:2026-01-02';
            }),
            m::on(function ($telemetry) {
                return ($telemetry['source_mode'] ?? null) === 'manual_file'
                    && ($telemetry['source_name'] ?? null) === 'LOCAL_FILE'
                    && ($telemetry['source_acquisition_state'] ?? null) === 'SUCCESS'
                    && ($telemetry['success_ticker_count'] ?? null) === 1
                    && ! empty($telemetry['source_file_hash']);
            })
        )->andReturn((object) [
            'run_id' => 902,
            'terminal_status' => 'SUCCESS',
            'notes' => 'bar_mutation_changed_count=1',
        ]);
        $pipeline->shouldReceive('promoteDaily')->once()->with('2026-01-02', 'manual_file', 902, null)->andReturn((object) [
            'run_id' => 902,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => 1.0,
            'sealed_at' => '2026-01-02 19:00:00',
            'notes' => '',
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'manual_file', [
            'input_file' => $inputFile,
            'output_dir' => $this->tmpOutputDir('missing-manual-source'),
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('SUCCESS', $summary['status']);
        $this->assertSame('manual_file', $summary['source_acquisition_mode']);
        $this->assertSame(2, $summary['candidate_source_row_count']);
        $this->assertSame('SUCCESS', $summary['cases'][0]['status']);
    }

    public function test_partial_manual_file_source_blocks_before_import_promote_and_correction(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, , , , , $artifacts] = $this->makeOrchestrator();
        $inputFile = $this->tmpOutputDir('manual-partial-source').'.csv';
        file_put_contents($inputFile, "ticker_code,trade_date,open,high,low,close,volume,canonical_source\nAAA,2026-01-02,100,110,99,105,1000,SEPUTARFOREX\n");

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->never();
        $acquisition->shouldReceive('plan')->never();
        $acquisition->shouldReceive('acquire')->never();
        $pipeline->shouldReceive('importDailyFromAcquiredRows')->never();
        $pipeline->shouldReceive('promoteDaily')->never();

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'manual_file', [
            'input_file' => $inputFile,
            'output_dir' => $this->tmpOutputDir('missing-manual-partial-source'),
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('BLOCKED', $summary['status']);
        $this->assertSame('SOURCE_ACQUISITION', $summary['stage']);
        $this->assertSame('PARTIAL_SUCCESS', $summary['source_acquisition_state']);
        $this->assertSame('RUN_SOURCE_MANUAL_FILE_MISSING_ROW', $summary['reason_code']);
        $this->assertSame(['BBB'], $summary['failed_ticker_codes']);
        $this->assertSame(0, $summary['candidate_source_row_count']);
        $this->assertSame('BLOCKED', $summary['cases'][0]['status']);
    }

    public function test_partial_source_acquisition_blocks_before_import_promote_and_correction(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, $evidence, $replay, , , $artifacts] = $this->makeOrchestrator();
        $outputDir = $this->tmpOutputDir('missing-partial-source-block');

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([1]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->never();

        $acquisition->shouldReceive('plan')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['BBB'])->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 1,
            'trading_date_count' => 1,
            'estimated_http_requests' => 1,
            'configured_concurrency' => 1,
        ]);
        $acquisition->shouldReceive('acquire')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['BBB'], [
            'resume' => false,
            'source_acquisition_context' => 'missing_ticker_backfill',
        ])->andReturn([
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_mode' => 'range_window',
            'window_count' => 1,
            'estimated_http_requests' => 1,
            'rows_by_trade_date' => [
                '2026-01-02' => [],
            ],
            'date_telemetry' => [
                '2026-01-02' => [
                    'source_acquisition_state' => 'FAILED',
                    'source_final_status' => 'FAILED',
                    'success_ticker_count' => 0,
                    'failed_ticker_count' => 1,
                    'missing_ticker_codes' => ['BBB'],
                    'final_reason_code' => 'RUN_SOURCE_INVALID_SYMBOL',
                ],
            ],
            'window_telemetry' => [[
                'source_window_start' => '2026-01-02',
                'source_window_end' => '2026-01-02',
                'source_acquisition_state' => 'PARTIAL_SUCCESS',
                'source_final_status' => 'PARTIAL',
                'failed_ticker_count' => 1,
                'failed_ticker_codes' => ['BBB'],
                'final_reason_code' => 'RUN_SOURCE_INVALID_SYMBOL',
                'final_http_status' => 404,
                'failure_scope' => 'ticker',
            ]],
            'source_acquisition_checkpoints' => [
                '2026-01-02|2026-01-02|BBB' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-02',
                    'window_end' => '2026-01-02',
                    'ticker_code' => 'BBB',
                    'reason_code' => 'RUN_SOURCE_INVALID_SYMBOL',
                    'http_status' => 404,
                    'failure_scope' => 'ticker',
                ],
            ],
        ]);

        $pipeline->shouldReceive('importDailyFromAcquiredRows')->never();
        $pipeline->shouldReceive('promoteDaily')->never();
        $evidence->shouldReceive('exportRunEvidence')->never();
        $replay->shouldReceive('generateFixtureFromRun')->never();
        $replay->shouldReceive('verifyRunAgainstFixture')->never();

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'output_dir' => $outputDir,
            'max_dates_per_run' => 5,
            'with_evidence' => true,
            'with_replay' => true,
        ]);

        $this->assertSame('BLOCKED', $summary['status']);
        $this->assertSame('SOURCE_ACQUISITION', $summary['stage']);
        $this->assertSame('PARTIAL_SUCCESS', $summary['source_acquisition_state']);
        $this->assertSame('RUN_SOURCE_INVALID_SYMBOL', $summary['reason_code']);
        $this->assertSame(['BBB'], $summary['failed_ticker_codes']);
        $this->assertSame(1, $summary['failed_ticker_count']);
        $this->assertSame(1, $summary['dates_blocked']);
        $this->assertSame(0, $summary['candidate_source_row_count']);
        $this->assertSame(0, $summary['bar_mutation_changed_count']);
        $this->assertFalse($summary['all_passed']);
        $this->assertFileExists($outputDir.DIRECTORY_SEPARATOR.'source_acquisition_diagnostics.json');

        $case = $summary['cases'][0];
        $this->assertSame('BLOCKED', $case['status']);
        $this->assertSame('SKIPPED_SOURCE_ACQUISITION_BLOCKED', $case['import_status']);
        $this->assertSame('SKIPPED', $case['promote_status']);
        $this->assertSame('SKIPPED_SOURCE_ACQUISITION_BLOCKED', $case['evidence_status']);
        $this->assertSame('SKIPPED_SOURCE_ACQUISITION_BLOCKED', $case['replay_status']);
        $this->assertSame(['BBB'], $case['failed_ticker_codes']);
        $this->assertArrayNotHasKey('run_id', $case);
    }

    public function test_api_missing_ticker_acquisition_can_overlay_source_backed_manual_rows_for_failed_tickers(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, , , , , $artifacts] = $this->makeOrchestrator();
        $outputDir = $this->tmpOutputDir('missing-api-manual-overlay');
        $inputFile = $this->tmpOutputDir('api-manual-overlay-source').'.csv';
        file_put_contents($inputFile, "ticker_code,trade_date,open,high,low,close,volume,canonical_source,source_row_ref\nBBB,2026-01-02,50,55,49,54,500,INVESTING_COM_FINANCIALDATA_API,investing:BBB:2026-01-02\n");

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 1, 'ticker_code' => 'AAA'],
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $acquisition->shouldReceive('plan')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['AAA', 'BBB'])->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 2,
            'trading_date_count' => 1,
            'estimated_http_requests' => 2,
            'configured_concurrency' => 1,
            'windows' => [[
                'start' => '2026-01-02',
                'end' => '2026-01-02',
            ]],
        ]);
        $acquisition->shouldReceive('acquire')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['AAA', 'BBB'], [
            'resume' => false,
            'source_acquisition_context' => 'missing_ticker_backfill',
        ])->andReturn([
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_mode' => 'range_window',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'warmup_start' => '2026-01-02',
            'windows' => [[
                'start' => '2026-01-02',
                'end' => '2026-01-02',
            ], [
                'start' => '2026-01-03',
                'end' => '2026-01-03',
            ]],
            'window_count' => 1,
            'estimated_http_requests' => 2,
            'rows_by_trade_date' => [
                '2026-01-02' => [[
                    'ticker_code' => 'AAA',
                    'trade_date' => '2026-01-02',
                    'open' => 100,
                    'high' => 110,
                    'low' => 99,
                    'close' => 105,
                    'volume' => 1000,
                    'adj_close' => 105,
                    'source_name' => 'YAHOO',
                ]],
            ],
            'date_telemetry' => [
                '2026-01-02' => [
                    'source_acquisition_state' => 'PARTIAL_SUCCESS',
                    'source_final_status' => 'PARTIAL',
                    'success_ticker_count' => 1,
                    'failed_ticker_count' => 1,
                    'missing_ticker_codes' => ['BBB'],
                    'final_reason_code' => 'RUN_SOURCE_INVALID_SYMBOL',
                ],
            ],
            'window_telemetry' => [[
                'source_window_start' => '2026-01-02',
                'source_window_end' => '2026-01-02',
                'source_acquisition_state' => 'PARTIAL_SUCCESS',
                'source_final_status' => 'PARTIAL',
                'failed_ticker_count' => 1,
                'failed_ticker_codes' => ['BBB'],
                'final_reason_code' => 'RUN_SOURCE_INVALID_SYMBOL',
                'final_http_status' => 404,
                'failure_scope' => 'ticker',
            ]],
            'source_acquisition_checkpoints' => [
                '2026-01-02|2026-01-02|BBB' => [
                    'state' => 'FAILED',
                    'window_start' => '2026-01-02',
                    'window_end' => '2026-01-02',
                    'ticker_code' => 'BBB',
                    'reason_code' => 'RUN_SOURCE_INVALID_SYMBOL',
                    'http_status' => 404,
                    'failure_scope' => 'ticker',
                ],
            ],
        ]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $pipeline->shouldReceive('importDailyFromAcquiredRows')->once()->with(
            '2026-01-02',
            'api',
            m::on(function ($rows) {
                return array_column($rows, 'ticker_code') === ['AAA', 'BBB']
                    && ($rows[1]['canonical_source'] ?? null) === 'INVESTING_COM_FINANCIALDATA_API'
                    && ($rows[1]['source_row_ref'] ?? null) === 'investing:BBB:2026-01-02';
            }),
            m::on(function ($telemetry) {
                return ($telemetry['source_acquisition_mode'] ?? null) === 'api_manual_file_overlay'
                    && ($telemetry['source_acquisition_state'] ?? null) === 'SUCCESS'
                    && ($telemetry['failed_ticker_count'] ?? null) === 0
                    && ($telemetry['manual_overlay_mode'] ?? null) === 'API_MANUAL_FILE_OVERLAY';
            })
        )->andReturn((object) [
            'run_id' => 903,
            'terminal_status' => 'SUCCESS',
            'notes' => 'bar_mutation_changed_count=2',
        ]);
        $pipeline->shouldReceive('promoteDaily')->once()->with('2026-01-02', 'api', 903, null)->andReturn((object) [
            'run_id' => 903,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => 1.0,
            'sealed_at' => '2026-01-02 19:00:00',
            'notes' => '',
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'input_file' => $inputFile,
            'output_dir' => $outputDir,
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('SUCCESS', $summary['status']);
        $this->assertSame('api_manual_file_overlay', $summary['source_acquisition_mode']);
        $this->assertSame('SUCCESS', $summary['source_acquisition_state']);
        $this->assertSame(0, $summary['failed_ticker_count']);
        $this->assertSame(2, $summary['candidate_source_row_count']);
        $this->assertSame('SUCCESS', $summary['cases'][0]['status']);
    }

    public function test_api_manual_overlay_can_replace_successful_api_rows_for_source_backed_corrections(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, , , , , $artifacts] = $this->makeOrchestrator();
        $outputDir = $this->tmpOutputDir('missing-api-manual-success-overlay');
        $inputFile = $this->tmpOutputDir('api-manual-success-overlay-source').'.csv';
        file_put_contents($inputFile, "ticker_code,trade_date,open,high,low,close,volume,canonical_source,source_row_ref\nBBB,2026-01-02,50,55,49,54,500,IDX_STOCK_SUMMARY_PREVIOUS_OPEN_RECONCILED,idx:BBB:2026-01-02\n");

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $acquisition->shouldReceive('plan')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['BBB'])->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 1,
            'trading_date_count' => 1,
            'estimated_http_requests' => 1,
            'configured_concurrency' => 1,
            'windows' => [[
                'start' => '2026-01-02',
                'end' => '2026-01-02',
            ]],
        ]);
        $acquisition->shouldReceive('acquire')->once()->with('2026-01-02', '2026-01-02', '2026-01-02', ['2026-01-02'], ['BBB'], [
            'resume' => false,
            'source_acquisition_context' => 'missing_ticker_backfill',
        ])->andReturn([
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_mode' => 'range_window',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'warmup_start' => '2026-01-02',
            'windows' => [[
                'start' => '2026-01-02',
                'end' => '2026-01-02',
            ]],
            'window_count' => 1,
            'estimated_http_requests' => 1,
            'rows_by_trade_date' => [
                '2026-01-02' => [[
                    'ticker_code' => 'BBB',
                    'trade_date' => '2026-01-02',
                    'open' => 50,
                    'high' => 50,
                    'low' => 49,
                    'close' => 54,
                    'volume' => 500,
                    'adj_close' => 54,
                    'source_name' => 'YAHOO',
                    'source_row_ref' => 'yahoo:BBB:2026-01-02',
                ]],
            ],
            'date_telemetry' => [
                '2026-01-02' => [
                    'source_acquisition_state' => 'SUCCESS',
                    'source_final_status' => 'SUCCESS',
                    'success_ticker_count' => 1,
                    'failed_ticker_count' => 0,
                    'missing_ticker_codes' => [],
                ],
            ],
            'window_telemetry' => [[
                'source_window_start' => '2026-01-02',
                'source_window_end' => '2026-01-02',
                'source_acquisition_state' => 'SUCCESS',
                'source_final_status' => 'SUCCESS',
                'failed_ticker_count' => 0,
                'failed_ticker_codes' => [],
            ]],
            'source_acquisition_checkpoints' => [
                '2026-01-02|2026-01-02|BBB' => [
                    'state' => 'SUCCESS',
                    'window_start' => '2026-01-02',
                    'window_end' => '2026-01-02',
                    'ticker_code' => 'BBB',
                    'rows_count' => 1,
                ],
            ],
        ]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $pipeline->shouldReceive('importDailyFromAcquiredRows')->once()->with(
            '2026-01-02',
            'api',
            m::on(function ($rows) {
                return count($rows) === 1
                    && ($rows[0]['ticker_code'] ?? null) === 'BBB'
                    && (float) ($rows[0]['close'] ?? 0) === 54.0
                    && ($rows[0]['canonical_source'] ?? null) === 'IDX_STOCK_SUMMARY_PREVIOUS_OPEN_RECONCILED'
                    && ($rows[0]['source_row_ref'] ?? null) === 'idx:BBB:2026-01-02';
            }),
            m::on(function ($telemetry) {
                return ($telemetry['source_acquisition_mode'] ?? null) === 'api_manual_file_overlay'
                    && ($telemetry['source_acquisition_state'] ?? null) === 'SUCCESS'
                    && ($telemetry['manual_overlay_mode'] ?? null) === 'API_MANUAL_FILE_OVERLAY';
            })
        )->andReturn((object) [
            'run_id' => 904,
            'terminal_status' => 'SUCCESS',
            'notes' => 'bar_mutation_changed_count=1',
        ]);
        $pipeline->shouldReceive('promoteDaily')->once()->with('2026-01-02', 'api', 904, null)->andReturn((object) [
            'run_id' => 904,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
            'coverage_ratio' => 1.0,
            'sealed_at' => '2026-01-02 19:00:00',
            'notes' => '',
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'input_file' => $inputFile,
            'output_dir' => $outputDir,
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('SUCCESS', $summary['status']);
        $this->assertSame('api_manual_file_overlay', $summary['source_acquisition_mode']);
        $this->assertSame(1, $summary['candidate_source_row_count']);
        $this->assertSame('SUCCESS', $summary['cases'][0]['status']);
    }

    public function test_missing_ticker_import_failure_does_not_reuse_stale_readable_run(): void
    {
        [$orchestrator, $calendar, $tickers, $acquisition, $pipeline, , , $runs, , $artifacts] = $this->makeOrchestrator();
        $outputDir = $this->tmpOutputDir('missing-stale-readable-run');

        $calendar->shouldReceive('tradingDatesBetween')->once()->with('2026-01-02', '2026-01-02')->andReturn(['2026-01-02']);
        $tickers->shouldReceive('getUniverseForTradeDate')->once()->with('2026-01-02')->andReturn([
            ['ticker_id' => 2, 'ticker_code' => 'BBB'],
        ]);
        $artifacts->shouldReceive('loadCanonicalBarTickerIdsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $acquisition->shouldReceive('plan')->once()->andReturn([
            'source_acquisition_mode' => 'range_window',
            'warmup_start' => '2026-01-02',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'window_count' => 1,
            'ticker_count' => 1,
            'trading_date_count' => 1,
            'estimated_http_requests' => 1,
            'configured_concurrency' => 1,
            'windows' => [[
                'start' => '2026-01-02',
                'end' => '2026-01-02',
            ]],
        ]);
        $acquisition->shouldReceive('acquire')->once()->andReturn([
            'source_acquisition_batch_id' => 'API_TEST',
            'source_acquisition_mode' => 'range_window',
            'requested_start' => '2026-01-02',
            'requested_end' => '2026-01-02',
            'warmup_start' => '2026-01-02',
            'windows' => [[
                'start' => '2026-01-02',
                'end' => '2026-01-02',
            ]],
            'window_count' => 1,
            'estimated_http_requests' => 1,
            'rows_by_trade_date' => [
                '2026-01-02' => [[
                    'ticker_code' => 'BBB',
                    'trade_date' => '2026-01-02',
                    'open' => 50,
                    'high' => 55,
                    'low' => 49,
                    'close' => 54,
                    'volume' => 500,
                    'adj_close' => 54,
                    'source_name' => 'YAHOO',
                ]],
            ],
            'date_telemetry' => [
                '2026-01-02' => [
                    'source_acquisition_state' => 'SUCCESS',
                    'source_final_status' => 'SUCCESS',
                    'success_ticker_count' => 1,
                    'failed_ticker_count' => 0,
                    'missing_ticker_codes' => [],
                ],
            ],
            'window_telemetry' => [],
            'source_acquisition_checkpoints' => [],
        ]);
        $artifacts->shouldReceive('loadBarsForTradeDate')->once()->with('2026-01-02', null)->andReturn([]);
        $pipeline->shouldReceive('importDailyFromAcquiredRows')->once()->andThrow(new \RuntimeException('Run request_mode is immutable within a single run and cannot switch across import/promote boundary.'));
        $pipeline->shouldReceive('promoteDaily')->never();
        $runs->shouldReceive('findLatestForRequestedDate')->once()->with('2026-01-02', 'api')->andReturn((object) [
            'run_id' => 901,
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'coverage_gate_state' => 'PASS',
        ]);

        $summary = $orchestrator->executeMissingTickers('2026-01-02', '2026-01-02', 'api', [
            'output_dir' => $outputDir,
            'max_dates_per_run' => 5,
        ]);

        $this->assertSame('PARTIAL', $summary['status']);
        $this->assertSame('FAILED', $summary['cases'][0]['import_status']);
        $this->assertSame('SKIPPED', $summary['cases'][0]['promote_status']);
        $this->assertArrayNotHasKey('run_id', $summary['cases'][0]);
        $this->assertStringContainsString('request_mode is immutable', $summary['cases'][0]['error_message']);
    }

    public function test_api_manual_overlay_recompute_uses_date_scoped_missing_codes_for_delisted_window(): void
    {
        [$orchestrator] = $this->makeOrchestrator();

        $method = new ReflectionMethod($orchestrator, 'recomputeApiManualOverlayAcquisitionTelemetry');
        $method->setAccessible(true);

        $result = $method->invoke($orchestrator, [
            'source_acquisition_batch_id' => 'API_20250401_20250630_001',
            'requested_start' => '2025-04-01',
            'requested_end' => '2025-06-30',
            'warmup_start' => '2025-04-01',
            'windows' => [
                ['start' => '2025-04-01', 'end' => '2025-04-17'],
                ['start' => '2025-06-30', 'end' => '2025-06-30'],
            ],
            'rows_by_trade_date' => [
                '2025-04-16' => [[
                    'ticker_code' => 'FREN',
                    'trade_date' => '2025-04-16',
                    'open' => 20,
                    'high' => 21,
                    'low' => 19,
                    'close' => 20,
                    'volume' => 1000,
                ]],
                '2025-06-30' => [[
                    'ticker_code' => 'MASA',
                    'trade_date' => '2025-06-30',
                    'open' => 6000,
                    'high' => 6100,
                    'low' => 5900,
                    'close' => 6050,
                    'volume' => 1000,
                ]],
            ],
        ], ['2025-04-16', '2025-06-30'], ['FREN', 'MASA'], 'API_MANUAL_FILE_OVERLAY', [
            'missing_ticker_codes_by_date' => [
                '2025-04-16' => ['FREN'],
                '2025-06-30' => ['MASA'],
            ],
        ]);

        $this->assertSame('SUCCESS', $result['source_acquisition_state']);
        $this->assertSame('SUCCESS', $result['window_telemetry'][0]['source_acquisition_state']);
        $this->assertSame('SUCCESS', $result['window_telemetry'][1]['source_acquisition_state']);
        $this->assertSame(1, $result['window_telemetry'][1]['expected_ticker_count']);
        $this->assertSame([], $result['window_telemetry'][1]['missing_ticker_codes']);
        $this->assertArrayHasKey('2025-04-01|2025-04-17|FREN', $result['source_acquisition_checkpoints']);
        $this->assertArrayHasKey('2025-06-30|2025-06-30|MASA', $result['source_acquisition_checkpoints']);
        $this->assertArrayNotHasKey('2025-06-30|2025-06-30|FREN', $result['source_acquisition_checkpoints']);
    }

    private function makeOrchestrator(EventRiskSourceRepository $eventRiskSources = null): array
    {
        $calendar = m::mock(MarketCalendarRepository::class);
        $tickers = m::mock(TickerMasterRepository::class);
        $acquisition = m::mock(ApiBackfillRangeAcquisitionService::class);
        $pipeline = m::mock(MarketDataPipelineService::class);
        $evidence = m::mock(MarketDataEvidenceExportService::class);
        $replay = m::mock(ReplayVerificationService::class);
        $runs = m::mock(EodRunRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $publications = m::mock(EodPublicationRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);

        return [
            new BackfillLifecycleOrchestrator($calendar, $tickers, $acquisition, $pipeline, $evidence, $replay, $runs, $corrections, $publications, $artifacts, $eventRiskSources),
            $calendar,
            $tickers,
            $acquisition,
            $pipeline,
            $evidence,
            $replay,
            $runs,
            $publications,
            $artifacts,
        ];
    }

    private function tmpOutputDir(string $suffix): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'tradeaxis-'.$suffix.'-'.str_replace('.', '', uniqid('', true));
    }
}
