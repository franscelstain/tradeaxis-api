<?php

require_once __DIR__.'/../../Support/InteractsWithMarketDataConfig.php';

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\CoverageGateEvaluator;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\EodBarsIngestService;
use App\Application\MarketData\Services\EodEligibilityBuildService;
use App\Application\MarketData\Services\EodIndicatorsComputeService;
use App\Application\MarketData\Services\FinalizeDecisionService;
use App\Application\MarketData\Services\MarketDataPipelineService;
use App\Application\MarketData\Services\PublicationDiffService;
use App\Application\MarketData\Services\PublicationFinalizeOutcomeService;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Models\EodRun;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepositoryContract;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MarketDataPipelineServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();

        Container::setInstance($container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);

        $container->instance('app', $container);

        $config = new Repository([
            'app.env' => 'testing',
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'market_data' => require dirname(__DIR__, 3).'/config/market_data.php',
        ]);

        $container->instance('config', $config);
        $container->instance(ConfigRepositoryContract::class, $config);

        $db = new class {
            public function transaction(callable $callback)
            {
                return $callback();
            }
        };

        $container->instance('db', $db);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        m::close();

        parent::tearDown();
    }

    private function makeRun(
        int $runId = 55,
        string $coverageRatio = '1.0000',
        ?string $sealedAt = '2026-03-24 23:06:08'
    ): EodRun {
        $run = new EodRun();
        $run->run_id = $runId;
        $run->coverage_ratio = $coverageRatio;
        $run->sealed_at = $sealedAt;

        return $run;
    }


    public function test_start_stage_logs_api_source_context_in_stage_started_event(): void
    {
        [$service, $runs, $publications, $corrections] = $this->makeService();

        $run = $this->makeRun(91);
        $run->notes = null;
        $run->supersedes_run_id = null;

        config()->set('market_data.source.api.provider', 'generic');
        config()->set('market_data.source.api.source_name', 'API_FREE');
        config()->set('market_data.source.api.timeout_seconds', 9);
        config()->set('market_data.provider.api_retry_max', 4);
        config()->set('market_data.provider.api_throttle_qps', 11);

        $input = new MarketDataStageInput('2026-04-05', 'api', null, 'INGEST_BARS', null);

        $runs->shouldReceive('getOrCreateOwningRun')
            ->once()
            ->with('2026-04-05', 'api', 'INGEST_BARS', null)
            ->andReturn($run);

        $runs->shouldReceive('touchStage')
            ->once()
            ->with($run, 'INGEST_BARS', m::on(function ($attributes) {
                return is_array($attributes)
                    && array_key_exists('notes', $attributes)
                    && $attributes['notes'] === null
                    && array_key_exists('supersedes_run_id', $attributes)
                    && $attributes['supersedes_run_id'] === null;
            }))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->once()
            ->with(
                $run,
                'INGEST_BARS',
                'STAGE_STARTED',
                'INFO',
                'Stage started in owning run context.',
                null,
                m::on(function ($payload) {
                    return is_array($payload)
                        && ($payload['requested_date'] ?? null) === '2026-04-05'
                        && ($payload['source_mode'] ?? null) === 'api'
                        && ($payload['source_name'] ?? null) === 'API_FREE'
                        && ($payload['provider'] ?? null) === 'generic'
                        && ($payload['timeout_seconds'] ?? null) === 9
                        && ($payload['retry_max'] ?? null) === 4
                        && ($payload['throttle_qps'] ?? null) === 11
                        && ($payload['stage'] ?? null) === 'INGEST_BARS'
                        && array_key_exists('correction_id', $payload)
                        && $payload['correction_id'] === null;
                })
            );

        $result = $service->startStage($input);

        $this->assertSame($run, $result[0]);
        $this->assertNull($result[1]);
        $this->assertNull($result[2]);
    }

    public function test_complete_ingest_persists_manual_input_file_in_notes_and_event_payload(): void
    {
        config()->set('market_data.source.local_input_file', 'storage/app/market_data/operator/manual-2026-03-24.csv');

        $runs = m::mock(EodRunRepository::class);
        $bars = m::mock(EodBarsIngestService::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);
        $hashes = m::mock(DeterministicHashService::class);
        $finalize = m::mock(FinalizeDecisionService::class);
        $diffs = m::mock(PublicationDiffService::class);
        $outcomes = m::mock(PublicationFinalizeOutcomeService::class);
        $coverageGate = m::mock(CoverageGateEvaluator::class);

        $run = (object) [
            'run_id' => 88,
            'trade_date_requested' => '2026-03-24',
            'stage' => 'INGEST_BARS',
            'source' => 'manual_file',
            'notes' => null,
            'supersedes_run_id' => null,
        ];

        $runs->shouldReceive('getOrCreateOwningRun')->once()->andReturn($run);
        $runs->shouldReceive('touchStage')->once()->andReturn($run);
        $runs->shouldReceive('appendEvent')->twice()->withArgs(function ($runArg, $stage, $eventType, $severity, $message, $reasonCode, $payload) {
            if ($eventType === 'STAGE_STARTED') {
                return ($payload['source_mode'] ?? null) === 'manual_file'
                    && ($payload['input_file'] ?? null) === 'storage/app/market_data/operator/manual-2026-03-24.csv';
            }

            return ($payload['source_mode'] ?? null) === 'manual_file'
                && ($payload['input_file'] ?? null) === 'storage/app/market_data/operator/manual-2026-03-24.csv';
        });
        $runs->shouldReceive('updateTelemetry')->once()->with($run, m::on(function ($telemetry) {
            return ($telemetry['notes'] ?? null) === 'candidate_publication_id=44; source_name=LOCAL_FILE; source_input_file=manual-2026-03-24.csv';
        }))->andReturn($run);

        $bars->shouldReceive('ingest')->once()->andReturn([
            'publication_id' => 44,
            'publication_version' => 1,
            'bars_rows_written' => 10,
            'invalid_bar_count' => 0,
            'source_name' => 'LOCAL_FILE',
            'storage_target' => 'eod_bars',
            'source_acquisition' => [],
        ]);

        $service = new MarketDataPipelineService($runs, $bars, $indicators, $eligibility, $publications, $corrections, $artifacts, $hashes, $finalize, $diffs, $outcomes, $coverageGate);

        $input = new MarketDataStageInput('2026-03-24', 'manual_file', null, 'INGEST_BARS', null);
        $result = $service->completeIngest($input);

        $this->assertSame(88, $result->run_id);
        config()->set('market_data.source.local_input_file', null);
    }

    public function test_complete_ingest_persists_source_name_in_notes_and_event_payload(): void
    {
        [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs, $coverageGateEvaluator, $eligibility, $barsIngest] = $this->makeService();

        $run = $this->makeRun(92);
        $run->notes = 'correction_id=7';
        $run->supersedes_run_id = null;

        $input = new MarketDataStageInput('2026-04-05', 'api', 92, 'INGEST_BARS', null);

        config()->set('market_data.source.api.provider', 'generic');
        config()->set('market_data.source.api.source_name', 'API_FREE');
        config()->set('market_data.source.api.timeout_seconds', 15);
        config()->set('market_data.provider.api_retry_max', 3);
        config()->set('market_data.provider.api_throttle_qps', 5);

        $service->shouldReceive('startStage')
            ->once()
            ->with($input)
            ->andReturn([$run, null, null]);

        $barsIngest->shouldReceive('ingest')
            ->once()
            ->with($run, '2026-04-05', 'api', null)
            ->andReturn([
                'publication_id' => 44,
                'publication_version' => 6,
                'bars_rows_written' => 900,
                'invalid_bar_count' => 3,
                'source_name' => 'API_FREE',
                'storage_target' => 'eod_bars',
                'source_acquisition' => [
                    'provider' => 'generic',
                    'source_name' => 'API_FREE',
                    'attempt_count' => 2,
                    'success_after_retry' => true,
                    'final_http_status' => 200,
                ],
            ]);

        $runs->shouldReceive('updateTelemetry')
            ->once()
            ->with($run, m::on(function ($telemetry) {
                return is_array($telemetry)
                    && ($telemetry['bars_rows_written'] ?? null) === 900
                    && ($telemetry['invalid_bar_count'] ?? null) === 3
                    && ($telemetry['publication_version'] ?? null) === 6
                    && ($telemetry['notes'] ?? null) === 'correction_id=7; candidate_publication_id=44; source_name=API_FREE; source_attempt_count=2; source_success_after_retry=yes; source_final_http_status=200';
            }))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->once()
            ->with(
                $run,
                'INGEST_BARS',
                'STAGE_COMPLETED',
                'INFO',
                'Bars ingest stage completed with canonical artifact writes.',
                null,
                m::on(function ($payload) {
                    return is_array($payload)
                        && ($payload['publication_id'] ?? null) === 44
                        && ($payload['source_mode'] ?? null) === 'api'
                        && ($payload['source_name'] ?? null) === 'API_FREE'
                        && ($payload['provider'] ?? null) === 'generic'
                        && ($payload['timeout_seconds'] ?? null) === 15
                        && ($payload['retry_max'] ?? null) === 3
                        && ($payload['throttle_qps'] ?? null) === 5
                        && is_array($payload['source_acquisition'] ?? null)
                        && ($payload['source_acquisition']['attempt_count'] ?? null) === 2
                        && ($payload['source_acquisition']['success_after_retry'] ?? null) === true
                        && ($payload['source_acquisition']['final_http_status'] ?? null) === 200;
                })
            );

        $runs->shouldReceive('failStage')->never();

        $result = $service->completeIngest($input);

        $this->assertSame($run, $result);
    }

    public function test_complete_eligibility_stores_coverage_telemetry_separately_from_eligibility_metrics(): void
    {
        [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs, $coverageGateEvaluator, $eligibility] = $this->makeService();

        $run = $this->makeRun(77, '0.0000', null);

        $input = new MarketDataStageInput('2026-04-03', 'manual_file', 77, 'BUILD_ELIGIBILITY', null);

        $service->shouldReceive('startStage')
            ->once()
            ->with($input)
            ->andReturn([$run]);

        $eligibility->shouldReceive('build')
            ->once()
            ->with($run, '2026-04-03', false)
            ->andReturn([
                'publication_id' => 15,
                'publication_version' => 2,
                'eligibility_rows_written' => 900,
                'blocked_rows' => 40,
                'eligible_rows' => 860,
                'eligibility_pass_ratio' => 0.9556,
                'storage_target' => 'eod_eligibility',
            ]);

        $coverageGateEvaluator->shouldReceive('evaluate')
            ->once()
            ->with('2026-04-03', null)
            ->andReturn([
                'expected_universe_count' => 900,
                'available_eod_count' => 870,
                'missing_eod_count' => 30,
                'coverage_ratio' => 0.9666667,
                'coverage_gate_status' => 'PASS',
                'coverage_threshold_value' => 0.95,
                'coverage_threshold_mode' => 'MIN_RATIO',
                'coverage_calibration_version' => 'coverage_gate_v1',
                'reason_code' => 'COVERAGE_THRESHOLD_MET',
                'reason_codes' => ['COVERAGE_THRESHOLD_MET'],
                'missing_ticker_ids' => [101, 102],
                'missing_ticker_codes' => ['AAA', 'BBB'],
            ]);

        $runs->shouldReceive('updateTelemetry')
            ->once()
            ->with($run, m::on(function ($telemetry) {
                return $telemetry['eligibility_rows_written'] === 900
                    && $telemetry['hard_reject_count'] === 40
                    && $telemetry['coverage_universe_count'] === 900
                    && $telemetry['coverage_available_count'] === 870
                    && $telemetry['coverage_missing_count'] === 30
                    && abs($telemetry['coverage_ratio'] - 0.9666667) < 0.000001
                    && $telemetry['coverage_gate_state'] === 'PASS'
                    && abs($telemetry['coverage_min_threshold'] - 0.95) < 0.000001
                    && $telemetry['coverage_threshold_mode'] === 'MIN_RATIO'
                    && $telemetry['coverage_contract_version'] === 'coverage_gate_v1'
                    && $telemetry['coverage_missing_sample_json'] === ['AAA', 'BBB'];
            }))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->once()
            ->with($run, 'BUILD_ELIGIBILITY', 'STAGE_COMPLETED', 'INFO', m::type('string'), null, m::on(function ($payload) {
                return isset($payload['eligibility_pass_ratio'])
                    && ! isset($payload['coverage_ratio'])
                    && isset($payload['coverage']['coverage_ratio'])
                    && $payload['coverage']['coverage_gate_status'] === 'PASS';
            }));

        $runs->shouldReceive('failStage')->never();

        $result = $service->completeEligibility($input);

        $this->assertSame($run, $result);
    }

    public function test_complete_finalize_marks_correction_published_with_final_outcome_note_after_outcome_resolution(): void
    {
        [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs] = $this->makeService();

        $run = $this->makeRun(55);
        $run->notes = null;
        $run->supersedes_run_id = null;
        $run->terminal_status = 'SUCCESS';
        $run->quality_gate_state = 'PASS';
        $run->trade_date_effective = '2026-03-17';

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 55, 'FINALIZE', 4);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
            'seal_state' => 'SEALED',
            'trade_date' => '2026-03-17',
        ];

        $resolvedCurrent = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
            'trade_date' => '2026-03-17',
        ];

        $correction = (object) [
            'correction_id' => 4,
            'status' => 'RESEALED',
        ];

        $runs->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($run);

        $corrections->shouldReceive('requireApprovedForTradeDate')
            ->once()
            ->with(4, '2026-03-17')
            ->andReturn($correction);

        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $runs->shouldReceive('touchStage')
            ->once()
            ->with($run, 'FINALIZE', m::type('array'))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->zeroOrMoreTimes();

        $runs->shouldReceive('failStage')
            ->never();

        $publications->shouldReceive('findLatestReadablePublicationBefore')
            ->once()
            ->with('2026-03-17')
            ->andReturn(null);

        $publications->shouldReceive('getOrCreateCandidatePublication')
            ->once()
            ->with($run, 31)
            ->andReturn($candidatePublication);

        $finalizeDecisions->shouldReceive('evaluate')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'trade_date_effective' => null,
                'quality_gate_state' => 'PASS',
                'reason_code' => null,
                'message' => 'Finalize succeeded.',
            ]);

        $publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(false);

        $artifacts->shouldReceive('promotePublicationHistoryToCurrent')
            ->once()
            ->with('2026-03-17', 32, 55);

        $publications->shouldReceive('promoteCandidateToCurrent')
            ->once()
            ->with($run, 31)
            ->andReturn($candidatePublication);

        $runs->shouldReceive('syncCurrentPublicationMirror')
            ->once()
            ->with('2026-03-17', 55);

        $runs->shouldReceive('finalize')
            ->once()
            ->with($run, [
                'trade_date_effective' => '2026-03-17',
                'quality_gate_state' => 'PASS',
                'publishability_state' => 'READABLE',
                'terminal_status' => 'SUCCESS',
                'lifecycle_state' => 'COMPLETED',
            ])
            ->andReturn($run);

        $publications->shouldReceive('findPointerResolvedPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($resolvedCurrent);

        $publications->shouldReceive('restorePriorCurrentPublication')
            ->never();

        $publications->shouldReceive('buildManifestByPublicationId')
            ->once()
            ->with(32)
            ->andReturn((object) ['publication_id' => 32]);

        $corrections->shouldReceive('markPublished')
            ->once()
            ->with(
                4,
                55,
                31,
                'Historical correction published safely via new sealed current publication.'
            );

        $corrections->shouldReceive('markCancelled')->never();

        $result = $service->completeFinalize($input);

        $this->assertSame($run, $result);
    }

    public function test_complete_finalize_marks_correction_cancelled_with_final_outcome_note_when_content_is_unchanged(): void
    {
        [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs] = $this->makeService();

        $run = $this->makeRun(55);
        $run->notes = null;
        $run->supersedes_run_id = null;
        $run->terminal_status = 'SUCCESS';
        $run->quality_gate_state = 'PASS';
        $run->trade_date_effective = '2026-03-17';

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 55, 'FINALIZE', 4);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
            'seal_state' => 'SEALED',
        ];

        $correction = (object) [
            'correction_id' => 4,
            'status' => 'RESEALED',
        ];

        $runs->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($run);

        $corrections->shouldReceive('requireApprovedForTradeDate')
            ->once()
            ->with(4, '2026-03-17')
            ->andReturn($correction);

        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $runs->shouldReceive('touchStage')
            ->once()
            ->with($run, 'FINALIZE', m::type('array'))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->zeroOrMoreTimes();

        $runs->shouldReceive('failStage')
            ->never();

        $publications->shouldReceive('findLatestReadablePublicationBefore')
            ->once()
            ->with('2026-03-17')
            ->andReturn(null);

        $publications->shouldReceive('getOrCreateCandidatePublication')
            ->once()
            ->with($run, 31)
            ->andReturn($candidatePublication);

        $finalizeDecisions->shouldReceive('evaluate')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'trade_date_effective' => null,
                'quality_gate_state' => 'PASS',
                'reason_code' => null,
                'message' => 'Finalize succeeded.',
            ]);

        $publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(true);

        $publications->shouldReceive('discardCandidatePublication')
            ->once()
            ->with(32);

        $publications->shouldReceive('findPointerResolvedPublicationForTradeDate')
            ->never();

        $publications->shouldReceive('restorePriorCurrentPublication')
            ->never();

        $runs->shouldReceive('syncCurrentPublicationMirror')
            ->never();

        $runs->shouldReceive('finalize')
            ->once()
            ->with($run, [
                'trade_date_effective' => '2026-03-17',
                'quality_gate_state' => 'PASS',
                'publishability_state' => 'READABLE',
                'terminal_status' => 'SUCCESS',
                'lifecycle_state' => 'COMPLETED',
            ])
            ->andReturn($run);

        $publications->shouldReceive('buildManifestByPublicationId')
            ->once()
            ->with(31)
            ->andReturn((object) ['publication_id' => 31]);

        $corrections->shouldReceive('markCancelled')
            ->once()
            ->with(
                4,
                55,
                31,
                'Correction rerun produced unchanged content; current publication preserved without version switch.'
            );

        $corrections->shouldReceive('markPublished')->never();

        $result = $service->completeFinalize($input);

        $this->assertSame($run, $result);
    }

    public function test_complete_finalize_keeps_resealed_when_publication_promotion_throws_lock_conflict(): void
    {
        [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs] = $this->makeService();

        $run = $this->makeRun(57);
        $run->notes = null;
        $run->supersedes_run_id = null;
        $run->terminal_status = 'HELD';
        $run->quality_gate_state = 'PASS';
        $run->trade_date_effective = '2026-03-16';

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 57, 'FINALIZE', 6);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 34,
            'run_id' => 57,
            'publication_version' => 4,
            'seal_state' => 'SEALED',
        ];

        $correction = (object) [
            'correction_id' => 6,
            'status' => 'RESEALED',
        ];

        $runs->shouldReceive('findByRunId')
            ->once()
            ->with(57)
            ->andReturn($run);

        $corrections->shouldReceive('requireApprovedForTradeDate')
            ->once()
            ->with(6, '2026-03-17')
            ->andReturn($correction);

        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $runs->shouldReceive('touchStage')
            ->once()
            ->with($run, 'FINALIZE', m::type('array'))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->once()
            ->with(
                $run,
                'FINALIZE',
                'STAGE_STARTED',
                'INFO',
                'Stage started in owning run context.',
                null,
                m::on(function ($payload) {
                    return is_array($payload)
                        && ($payload['requested_date'] ?? null) === '2026-03-17'
                        && ($payload['source_mode'] ?? null) === 'manual_file'
                        && ($payload['stage'] ?? null) === 'FINALIZE'
                        && (string) ($payload['correction_id'] ?? null) === '6';
                })
            );

        $runs->shouldReceive('appendEvent')
            ->once()
            ->with(
                $run,
                'FINALIZE',
                'RUN_FINALIZED',
                'WARN',
                'Promotion lost run ownership while switching current publication.',
                'RUN_LOCK_CONFLICT',
                m::on(function ($payload) {
                    return is_array($payload)
                        && (string) ($payload['correction_id'] ?? null) === '6'
                        && ($payload['prior_publication_id'] ?? null) === 31
                        && ($payload['current_publication_id'] ?? null) === null
                        && array_key_exists('correction_outcome', $payload)
                        && $payload['correction_outcome'] === null;
                })
            );

        $runs->shouldReceive('failStage')->never();

        $publications->shouldReceive('findLatestReadablePublicationBefore')
            ->once()
            ->with('2026-03-17')
            ->andReturn((object) [
                'publication_id' => 30,
                'readable_trade_date' => '2026-03-16',
            ]);

        $publications->shouldReceive('getOrCreateCandidatePublication')
            ->once()
            ->with($run, 31)
            ->andReturn($candidatePublication);

        $finalizeDecisions->shouldReceive('evaluate')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'trade_date_effective' => null,
                'quality_gate_state' => 'PASS',
                'reason_code' => null,
                'message' => 'Finalize succeeded.',
            ]);

        $publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(false);

        $artifacts->shouldReceive('promotePublicationHistoryToCurrent')
            ->once()
            ->with('2026-03-17', 34, 57);

        $publications->shouldReceive('promoteCandidateToCurrent')
            ->once()
            ->with($run, 31)
            ->andThrow(new RuntimeException('Promotion lost run ownership while switching current publication.'));

        $publications->shouldReceive('restorePriorCurrentPublication')
            ->once()
            ->with('2026-03-17', 31, 31);

        $runs->shouldReceive('syncCurrentPublicationMirror')
            ->once()
            ->with('2026-03-17', 31);

        $publications->shouldReceive('findPointerResolvedPublicationForTradeDate')
            ->never();

        $runs->shouldReceive('finalize')
            ->once()
            ->with($run, [
                'trade_date_effective' => '2026-03-16',
                'quality_gate_state' => 'PASS',
                'publishability_state' => 'NOT_READABLE',
                'terminal_status' => 'HELD',
                'lifecycle_state' => 'COMPLETED',
            ])
            ->andReturn($run);

        $corrections->shouldReceive('markCancelled')->never();
        $corrections->shouldReceive('markPublished')->never();
        $publications->shouldReceive('buildManifestByPublicationId')->never();

        $result = $service->completeFinalize($input);

        $this->assertSame($run, $result);
    }

    public function test_complete_finalize_keeps_resealed_when_finalize_outcome_is_conflict(): void
    {
        [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs] = $this->makeService();

        $run = $this->makeRun(55);
        $run->notes = null;
        $run->supersedes_run_id = null;
        $run->terminal_status = 'HELD';
        $run->quality_gate_state = 'PASS';
        $run->trade_date_effective = null;

        $input = new MarketDataStageInput('2026-03-17', 'manual_file', 55, 'FINALIZE', 4);

        $priorCurrent = (object) [
            'publication_id' => 31,
            'run_id' => 31,
            'publication_version' => 3,
        ];

        $candidatePublication = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
            'seal_state' => 'SEALED',
            'trade_date' => '2026-03-17',
        ];

        $publishedCandidate = (object) [
            'publication_id' => 32,
            'run_id' => 55,
            'publication_version' => 4,
            'trade_date' => '2026-03-17',
        ];

        $conflictingCurrent = (object) [
            'publication_id' => 99,
            'publication_version' => 9,
        ];

        $correction = (object) [
            'correction_id' => 4,
            'status' => 'RESEALED',
        ];

        $runs->shouldReceive('findByRunId')
            ->once()
            ->with(55)
            ->andReturn($run);

        $corrections->shouldReceive('requireApprovedForTradeDate')
            ->once()
            ->with(4, '2026-03-17')
            ->andReturn($correction);

        $publications->shouldReceive('findCorrectionBaselinePublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($priorCurrent);

        $runs->shouldReceive('touchStage')
            ->once()
            ->with($run, 'FINALIZE', m::type('array'))
            ->andReturn($run);

        $runs->shouldReceive('appendEvent')
            ->zeroOrMoreTimes();

        $runs->shouldReceive('failStage')
            ->never();

        $publications->shouldReceive('findLatestReadablePublicationBefore')
            ->once()
            ->with('2026-03-17')
            ->andReturn(null);

        $publications->shouldReceive('getOrCreateCandidatePublication')
            ->once()
            ->with($run, 31)
            ->andReturn($candidatePublication);

        $finalizeDecisions->shouldReceive('evaluate')
            ->once()
            ->andReturn([
                'promotion_allowed' => true,
                'terminal_status' => 'SUCCESS',
                'publishability_state' => 'READABLE',
                'trade_date_effective' => null,
                'quality_gate_state' => 'PASS',
                'reason_code' => null,
                'message' => 'Finalize succeeded.',
            ]);

        $publicationDiffs->shouldReceive('isUnchanged')
            ->once()
            ->with($priorCurrent, $candidatePublication)
            ->andReturn(false);

        $artifacts->shouldReceive('promotePublicationHistoryToCurrent')
            ->once()
            ->with('2026-03-17', 32, 55);

        $publications->shouldReceive('promoteCandidateToCurrent')
            ->once()
            ->with($run, 31)
            ->andReturn($publishedCandidate);

        $runs->shouldReceive('syncCurrentPublicationMirror')
            ->once()
            ->with('2026-03-17', 55);

        $runs->shouldReceive('finalize')
            ->once()
            ->with($run, [
                'trade_date_effective' => '2026-03-17',
                'quality_gate_state' => 'PASS',
                'publishability_state' => 'READABLE',
                'terminal_status' => 'SUCCESS',
                'lifecycle_state' => 'COMPLETED',
            ])
            ->andReturn($run);

        $publications->shouldReceive('findPointerResolvedPublicationForTradeDate')
            ->once()
            ->with('2026-03-17')
            ->andReturn($conflictingCurrent);

        $publications->shouldReceive('restorePriorCurrentPublication')
            ->once()
            ->with('2026-03-17', 31, 31);

        $runs->shouldReceive('syncCurrentPublicationMirror')
            ->once()
            ->with('2026-03-17', 31);

        $runs->shouldReceive('finalize')
            ->once()
            ->with($run, [
                'trade_date_effective' => null,
                'quality_gate_state' => 'PASS',
                'publishability_state' => 'NOT_READABLE',
                'terminal_status' => 'HELD',
                'lifecycle_state' => 'COMPLETED',
            ])
            ->andReturn($run);

        $publications->shouldReceive('buildManifestByPublicationId')
            ->once()
            ->with(32)
            ->andReturn((object) ['publication_id' => 32]);

        $corrections->shouldReceive('markPublished')->never();
        $corrections->shouldReceive('markCancelled')->never();

        $result = $service->completeFinalize($input);

        $this->assertSame($run, $result);
    }

    private function makeService(): array
    {
        $runs = m::mock(EodRunRepository::class);
        $barsIngest = m::mock(EodBarsIngestService::class);
        $indicators = m::mock(EodIndicatorsComputeService::class);
        $eligibility = m::mock(EodEligibilityBuildService::class);
        $publications = m::mock(EodPublicationRepository::class);
        $corrections = m::mock(EodCorrectionRepository::class);
        $artifacts = m::mock(EodArtifactRepository::class);
        $hashes = m::mock(DeterministicHashService::class);
        $finalizeDecisions = m::mock(FinalizeDecisionService::class);
        $publicationDiffs = m::mock(PublicationDiffService::class);
        $publicationFinalizeOutcomes = new PublicationFinalizeOutcomeService();
        $coverageGateEvaluator = m::mock(CoverageGateEvaluator::class);

        $service = m::mock(MarketDataPipelineService::class, [
            $runs,
            $barsIngest,
            $indicators,
            $eligibility,
            $publications,
            $corrections,
            $artifacts,
            $hashes,
            $finalizeDecisions,
            $publicationDiffs,
            $publicationFinalizeOutcomes,
            $coverageGateEvaluator,
        ])->makePartial()->shouldAllowMockingProtectedMethods();

        return [$service, $runs, $publications, $corrections, $artifacts, $finalizeDecisions, $publicationDiffs, $coverageGateEvaluator, $eligibility, $barsIngest];
    }
}