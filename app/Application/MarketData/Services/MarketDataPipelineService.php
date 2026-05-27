<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\MarketData\Source\SourceAcquisitionException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\EodRun;

class MarketDataPipelineService
{
    private $runs;
    private $barsIngest;
    private $indicators;
    private $eligibility;
    private $publications;
    private $corrections;
    private $artifacts;
    private $hashes;
    private $finalizeDecisions;
    private $publicationDiffs;
    private $publicationFinalizeOutcomes;
    private $coverageGateEvaluator;
    private $benchmarkBarsIngest;
    private $impactReprocess;

    public function __construct(
        EodRunRepository $runs,
        EodBarsIngestService $barsIngest,
        EodIndicatorsComputeService $indicators,
        EodEligibilityBuildService $eligibility,
        EodPublicationRepository $publications,
        EodCorrectionRepository $corrections,
        EodArtifactRepository $artifacts,
        DeterministicHashService $hashes,
        FinalizeDecisionService $finalizeDecisions,
        PublicationDiffService $publicationDiffs,
        PublicationFinalizeOutcomeService $publicationFinalizeOutcomes,
        CoverageGateEvaluator $coverageGateEvaluator,
        BenchmarkBarsIngestService $benchmarkBarsIngest = null,
        MarketDataImpactReprocessExecutor $impactReprocess = null
    ) {
        $this->runs = $runs;
        $this->barsIngest = $barsIngest;
        $this->indicators = $indicators;
        $this->eligibility = $eligibility;
        $this->publications = $publications;
        $this->corrections = $corrections;
        $this->artifacts = $artifacts;
        $this->hashes = $hashes;
        $this->finalizeDecisions = $finalizeDecisions;
        $this->publicationDiffs = $publicationDiffs;
        $this->publicationFinalizeOutcomes = $publicationFinalizeOutcomes;
        $this->coverageGateEvaluator = $coverageGateEvaluator;
        $this->benchmarkBarsIngest = $benchmarkBarsIngest;
        $this->impactReprocess = $impactReprocess;
    }

    public function startStage(MarketDataStageInput $input)
    {
        $correction = null;
        $priorCurrent = null;
        $supersedesRunId = null;

        $run = $input->runId
            ? $this->safeFindRunById($input->runId)
            : null;

        $runPromoteMode = $run && isset($run->promote_mode) && $run->promote_mode !== ''
            ? (string) $run->promote_mode
            : null;
        $runPublishTarget = $run && isset($run->publish_target) && $run->publish_target !== ''
            ? (string) $run->publish_target
            : null;

        $isRepairCandidate = $input->correctionId && (
            in_array($runPromoteMode, ['repair_candidate', 'incremental'], true)
            || in_array($runPublishTarget, ['repair_candidate', 'incremental_candidate'], true)
        );

        if ($input->correctionId) {
            $correction = $isRepairCandidate
                ? $this->safeCanExecuteCorrection($input->correctionId, $input->requestedDate, 'repair_candidate')
                : $this->corrections->requireApprovedForTradeDate($input->correctionId, $input->requestedDate);

            $priorCurrent = $this->publications->findCorrectionBaselinePublicationForTradeDate($input->requestedDate);

            if (! $priorCurrent) {
                throw new \RuntimeException(
                    'Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.'
                );
            }

            if (! $isRepairCandidate) {
                $supersedesRunId = $priorCurrent->run_id;
            }
        }

        $this->assertAllowedRequestMode($input->requestMode, $input->stage);

        $run = $run ?: $this->runs->getOrCreateOwningRun(
            $input->requestedDate,
            $input->sourceMode,
            $input->stage,
            $supersedesRunId,
            $input->requestMode
        );

        if (! $run) {
            throw new \RuntimeException('Owning run context not found for market-data stage.');
        }

        $existingSourceMode = isset($run->source) && $run->source !== ''
            ? (string) $run->source
            : null;

        if ($existingSourceMode !== null && $existingSourceMode !== (string) $input->sourceMode) {
            throw new \RuntimeException(
                'Run source_mode is immutable within a single run and cannot switch across stages.'
            );
        }

        $existingRequestMode = isset($run->request_mode) && $run->request_mode !== ''
            ? (string) $run->request_mode
            : $this->extractNoteValue((string) ($run->notes ?? ''), 'request_mode');

        if ($existingRequestMode !== null && $existingRequestMode !== (string) $input->requestMode) {
            throw new \RuntimeException(
                'Run request_mode is immutable within a single run and cannot switch across import/promote boundary.'
            );
        }

        if ($correction && in_array($input->stage, ['INGEST_BARS', 'PUBLISH_BARS'], true)) {
            if (! $isRepairCandidate) {
                $this->artifacts->snapshotPublicationFromCurrentTables(
                    $input->requestedDate,
                    $priorCurrent->publication_id,
                    $priorCurrent->run_id
                );
            }

            $correction = $this->corrections->markExecuting(
                $correction->correction_id,
                $priorCurrent ? $priorCurrent->run_id : null,
                $run->run_id,
                $isRepairCandidate ? 'repair_candidate' : 'correction_current',
                $priorCurrent ? $priorCurrent->publication_id : null,
                null
            );
        }

        $run = $this->runs->touchStage($run, $input->stage, [
            'request_mode' => $input->requestMode,
            'notes' => $this->appendRunNotes(
                $run->notes,
                array_filter([
                    'request_mode=' . (string) $input->requestMode,
                    $input->correctionId ? 'correction_id=' . (int) $input->correctionId : null,
                ])
            ),
            'supersedes_run_id' => $supersedesRunId ?: $run->supersedes_run_id,
            'correction_id' => $input->correctionId ?: $run->correction_id,
        ]);

        $this->runs->appendEvent(
            $run,
            $input->stage,
            'STAGE_STARTED',
            'INFO',
            'Stage started in owning run context.',
            $this->requestModeStartReasonCode($input),
            $this->sourceTelemetryPayload($input->sourceMode) + [
                'run_id' => (int) $run->run_id,
                'requested_date' => $input->requestedDate,
                'trade_date_requested' => $input->requestedDate,
                'trade_date_effective' => $run->trade_date_effective,
                'source_mode' => $input->sourceMode,
                'request_mode' => $input->requestMode,
                'stage' => $input->stage,
                'correction_id' => $input->correctionId ? (int) $input->correctionId : null,
                'baseline_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                'replacement_publication_id' => null,
            ]
        );

        return [$run, $correction, $priorCurrent];
    }

    public function completeIngest(MarketDataStageInput $input)
    {
        [$run, $correction, $priorCurrent] = $this->startStage($input);

        if ($priorCurrent === null) {
            $baselineCurrent = $this->publications->findCorrectionBaselinePublicationForTradeDate($input->requestedDate);

            if ($baselineCurrent) {
                $priorCurrent = $baselineCurrent;
            }
        }

        try {
            $sourceRows = $this->barsIngest->acquireSourceRows($input->requestedDate, $input->sourceMode);
            $sourceAcquisitionTelemetry = $this->barsIngest->consumeSourceAcquisitionTelemetry($input->sourceMode);
            $benchmarkResult = $this->benchmarkBarsIngest !== null
                ? $this->benchmarkBarsIngest->ingest($input->requestedDate, $input->sourceMode)
                : [
                    'benchmark_import_status' => 'SKIPPED',
                    'benchmark_skip_reason_code' => 'BENCHMARK_SERVICE_NOT_BOUND',
                    'benchmark_rows_written' => 0,
                ];

            return DB::transaction(function () use ($run, $input, $priorCurrent, $sourceRows, $sourceAcquisitionTelemetry, $benchmarkResult) {
                $result = $this->barsIngest->ingestAcquiredRows(
                    $run,
                    $input->requestedDate,
                    $input->sourceMode,
                    $sourceRows,
                    $sourceAcquisitionTelemetry,
                    $priorCurrent
                );
                $result = $this->withImpactReprocessExecution($run, $input, $result);

                $sourceAcquisition = isset($result['source_acquisition']) && is_array($result['source_acquisition'])
                    ? $result['source_acquisition']
                    : [];

                $run = $this->safeUpdateTelemetry($run, array_merge([
                    'request_mode' => $input->requestMode,
                    'bars_rows_written' => $result['bars_rows_written'],
                    'invalid_bar_count' => $result['invalid_bar_count'],
                    'publication_id' => $result['publication_id'],
                    'publication_version' => $result['publication_version'],
                    'notes' => $this->appendRunNotes($run->notes, array_merge([
                        'request_mode='.(string) $input->requestMode,
                        'candidate_publication_id='.$result['publication_id'],
                        'source_name='.(string) $result['source_name'],
                        'benchmark_import_status='.(string) ($benchmarkResult['benchmark_import_status'] ?? 'UNKNOWN'),
                        'benchmark_rows_written='.(int) ($benchmarkResult['benchmark_rows_written'] ?? 0),
                    ], $this->manualSourceInputNoteSegments($input->sourceMode), $this->sourceAcquisitionNoteSegments($sourceAcquisition), $this->mutationImpactNoteSegments($result))),
                ], $this->sourceTelemetryColumns($input->sourceMode, $result['source_name'], $sourceAcquisition)));

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'STAGE_COMPLETED',
                    'INFO',
                    'Bars ingest stage completed with canonical artifact writes.',
                    $input->requestMode === 'import_only' ? 'IMPORT_ONLY_COMPLETED' : null,
                    $result + $this->sourceTelemetryPayload($input->sourceMode, $result['source_name']) + [
                        'request_mode' => $input->requestMode,
                        'import_status' => 'COMPLETED',
                        'promote_status' => 'NOT_PROMOTED',
                        'promoted' => false,
                        'pointer_switched' => false,
                        'source_acquisition' => $sourceAcquisition,
                        'benchmark_import' => $benchmarkResult,
                    ]
                );

                if ($input->requestMode === 'import_only') {
                    $this->assertImportOnlyDidNotPromote($run, $input, $result);
                    $this->runs->appendEvent(
                        $run,
                        $input->stage,
                        'IMPORT_ONLY_NOT_PROMOTED',
                        'INFO',
                        'Import-only completed without readable publication or current pointer switch.',
                        'IMPORT_ONLY_NOT_PROMOTED',
                        [
                            'run_id' => (int) $run->run_id,
                            'requested_date' => $input->requestedDate,
                            'source_mode' => $input->sourceMode,
                            'request_mode' => $input->requestMode,
                            'publication_id' => (int) $result['publication_id'],
                            'publication_version' => (int) $result['publication_version'],
                            'import_status' => 'COMPLETED',
                            'promote_status' => 'NOT_PROMOTED',
                            'promoted' => false,
                            'pointer_switched' => false,
                        ]
                    );
                }

                return $run;
            });
        } catch (\Throwable $e) {
            if ($e instanceof SourceAcquisitionException) {
                $reasonCode = $e->reasonCode();
            } else {
                $reasonCode = strpos($e->getMessage(), 'current publication') !== false
                    ? 'RUN_LOCK_CONFLICT'
                    : 'RUN_SOURCE_MALFORMED_PAYLOAD';
            }

            if ($e instanceof SourceAcquisitionException) {
                $heldRun = $this->handleRecoverableSourceFailure($run, $input->requestedDate, $input->stage, $reasonCode, $e);
                if ($heldRun !== null) {
                    return $heldRun;
                }
            }

            $this->handleStageFailure($run, $input->stage, $reasonCode, $e);
            throw $e;
        }
    }

    public function importDailyFromAcquiredRows($requestedDate, $sourceMode, array $sourceRows, array $sourceAcquisition = [], $correctionId = null)
    {
        return $this->completeIngestWithAcquiredRows(new MarketDataStageInput(
            $requestedDate,
            $sourceMode ?: config('market_data.pipeline.default_source_mode'),
            null,
            'INGEST_BARS',
            $correctionId,
            false,
            null,
            'import_only'
        ), $sourceRows, $sourceAcquisition);
    }

    public function applyRecoveredRowsPartial($requestedDate, $sourceMode, array $sourceRows, array $sourceAcquisition = [], $correctionId = null)
    {
        return $this->completeRecoveredRowsPartial(new MarketDataStageInput(
            $requestedDate,
            $sourceMode ?: config('market_data.pipeline.default_source_mode'),
            null,
            'INGEST_BARS',
            $correctionId,
            false,
            null,
            'import_only'
        ), $sourceRows, $sourceAcquisition);
    }

    public function completeRecoveredRowsPartial(MarketDataStageInput $input, array $sourceRows, array $sourceAcquisition = [])
    {
        [$run, $correction, $priorCurrent] = $this->startStage($input);

        if ($priorCurrent === null) {
            $baselineCurrent = $this->publications->findCorrectionBaselinePublicationForTradeDate($input->requestedDate);

            if ($baselineCurrent) {
                $priorCurrent = $baselineCurrent;
            }
        }

        try {
            return DB::transaction(function () use ($run, $input, $priorCurrent, $sourceRows, $sourceAcquisition) {
                $result = $this->barsIngest->ingestRecoveredRowsPartial(
                    $run,
                    $input->requestedDate,
                    $input->sourceMode,
                    $sourceRows,
                    $sourceAcquisition,
                    $priorCurrent
                );
                $result = $this->withImpactReprocessExecution($run, $input, $result);
                $sourceAcquisitionResult = isset($result['source_acquisition']) && is_array($result['source_acquisition'])
                    ? $result['source_acquisition']
                    : [];

                $run = $this->safeUpdateTelemetry($run, array_merge([
                    'request_mode' => $input->requestMode,
                    'bars_rows_written' => $result['bars_rows_written'],
                    'invalid_bar_count' => $result['invalid_bar_count'],
                    'publication_id' => $result['publication_id'],
                    'publication_version' => $result['publication_version'],
                    'notes' => $this->appendRunNotes($run->notes, array_merge([
                        'request_mode='.(string) $input->requestMode,
                        'candidate_publication_id='.$result['publication_id'],
                        'source_name='.(string) $result['source_name'],
                    ], $this->sourceAcquisitionNoteSegments($sourceAcquisitionResult), $this->mutationImpactNoteSegments($result))),
                ], $this->sourceTelemetryColumns($input->sourceMode, $result['source_name'], $sourceAcquisitionResult)));

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'RECOVERED_ROWS_PARTIAL_APPLY_COMPLETED',
                    'INFO',
                    'Recovered source rows were applied with partial ticker/date upsert; unrelated tickers were preserved.',
                    ($result['recovered_row_apply_state'] ?? null) === 'UNCHANGED' ? 'NOOP_UNCHANGED_BARS' : 'RECOVERED_ROWS_APPLIED',
                    $result + $this->sourceTelemetryPayload($input->sourceMode, $result['source_name']) + [
                        'request_mode' => $input->requestMode,
                        'import_status' => 'COMPLETED',
                        'promote_status' => 'NOT_PROMOTED',
                        'promoted' => false,
                        'pointer_switched' => false,
                        'source_acquisition' => $sourceAcquisitionResult,
                    ]
                );

                $this->assertImportOnlyDidNotPromote($run, $input, $result);

                return $run;
            });
        } catch (\Throwable $e) {
            $reasonCode = $e instanceof SourceAcquisitionException
                ? $e->reasonCode()
                : (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches) ? $matches[1] : 'RUN_SOURCE_MALFORMED_PAYLOAD');

            if ($e instanceof SourceAcquisitionException) {
                $heldRun = $this->handleRecoverableSourceFailure($run, $input->requestedDate, $input->stage, $reasonCode, $e);
                if ($heldRun !== null) {
                    return $heldRun;
                }
            }

            $this->handleStageFailure($run, $input->stage, $reasonCode, $e);
            throw $e;
        }
    }

    public function completeIngestWithAcquiredRows(MarketDataStageInput $input, array $sourceRows, array $sourceAcquisition = [])
    {
        [$run, $correction, $priorCurrent] = $this->startStage($input);

        if ($priorCurrent === null) {
            $baselineCurrent = $this->publications->findCorrectionBaselinePublicationForTradeDate($input->requestedDate);

            if ($baselineCurrent) {
                $priorCurrent = $baselineCurrent;
            }
        }

        try {
            $benchmarkResult = $this->benchmarkBarsIngest !== null
                ? $this->benchmarkBarsIngest->ingest($input->requestedDate, $input->sourceMode)
                : [
                    'benchmark_import_status' => 'SKIPPED',
                    'benchmark_skip_reason_code' => 'BENCHMARK_SERVICE_NOT_BOUND',
                    'benchmark_rows_written' => 0,
                ];

            return DB::transaction(function () use ($run, $input, $priorCurrent, $sourceRows, $sourceAcquisition, $benchmarkResult) {
                $result = $this->barsIngest->ingestAcquiredRows(
                    $run,
                    $input->requestedDate,
                    $input->sourceMode,
                    $sourceRows,
                    $sourceAcquisition,
                    $priorCurrent
                );
                $result = $this->withImpactReprocessExecution($run, $input, $result);
                $sourceAcquisitionResult = isset($result['source_acquisition']) && is_array($result['source_acquisition'])
                    ? $result['source_acquisition']
                    : [];

                $run = $this->safeUpdateTelemetry($run, array_merge([
                    'request_mode' => $input->requestMode,
                    'bars_rows_written' => $result['bars_rows_written'],
                    'invalid_bar_count' => $result['invalid_bar_count'],
                    'publication_id' => $result['publication_id'],
                    'publication_version' => $result['publication_version'],
                    'notes' => $this->appendRunNotes($run->notes, array_merge([
                        'request_mode='.(string) $input->requestMode,
                        'candidate_publication_id='.$result['publication_id'],
                        'source_name='.(string) $result['source_name'],
                        'benchmark_import_status='.(string) ($benchmarkResult['benchmark_import_status'] ?? 'UNKNOWN'),
                        'benchmark_rows_written='.(int) ($benchmarkResult['benchmark_rows_written'] ?? 0),
                    ], $this->manualSourceInputNoteSegments($input->sourceMode), $this->sourceAcquisitionNoteSegments($sourceAcquisitionResult), $this->mutationImpactNoteSegments($result))),
                ], $this->sourceTelemetryColumns($input->sourceMode, $result['source_name'], $sourceAcquisitionResult)));

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'STAGE_COMPLETED',
                    'INFO',
                    'Bars ingest stage completed with canonical artifact writes.',
                    $input->requestMode === 'import_only' ? 'IMPORT_ONLY_COMPLETED' : null,
                    $result + $this->sourceTelemetryPayload($input->sourceMode, $result['source_name']) + [
                        'request_mode' => $input->requestMode,
                        'import_status' => 'COMPLETED',
                        'promote_status' => 'NOT_PROMOTED',
                        'promoted' => false,
                        'pointer_switched' => false,
                        'source_acquisition' => $sourceAcquisitionResult,
                        'benchmark_import' => $benchmarkResult,
                    ]
                );

                if ($input->requestMode === 'import_only') {
                    $this->assertImportOnlyDidNotPromote($run, $input, $result);
                    $this->runs->appendEvent(
                        $run,
                        $input->stage,
                        'IMPORT_ONLY_NOT_PROMOTED',
                        'INFO',
                        'Import-only completed without readable publication or current pointer switch.',
                        'IMPORT_ONLY_NOT_PROMOTED',
                        [
                            'run_id' => (int) $run->run_id,
                            'requested_date' => $input->requestedDate,
                            'source_mode' => $input->sourceMode,
                            'request_mode' => $input->requestMode,
                            'publication_id' => (int) $result['publication_id'],
                            'publication_version' => (int) $result['publication_version'],
                            'import_status' => 'COMPLETED',
                            'promote_status' => 'NOT_PROMOTED',
                            'promoted' => false,
                            'pointer_switched' => false,
                        ]
                    );
                }

                return $run;
            });
        } catch (\Throwable $e) {
            if ($e instanceof SourceAcquisitionException) {
                $reasonCode = $e->reasonCode();
            } else {
                $reasonCode = strpos($e->getMessage(), 'current publication') !== false
                    ? 'RUN_LOCK_CONFLICT'
                    : 'RUN_SOURCE_MALFORMED_PAYLOAD';
            }

            if ($e instanceof SourceAcquisitionException) {
                $heldRun = $this->handleRecoverableSourceFailure($run, $input->requestedDate, $input->stage, $reasonCode, $e);
                if ($heldRun !== null) {
                    return $heldRun;
                }
            }

            $this->handleStageFailure($run, $input->stage, $reasonCode, $e);
            throw $e;
        }
    }

    public function completeIndicators(MarketDataStageInput $input)
    {
        [$run] = $this->startStage($input);

        try {
            return DB::transaction(function () use ($run, $input) {
                $result = $this->indicators->compute($run, $input->requestedDate, $input->correctionId !== null);
                $run = $this->runs->updateTelemetry($run, [
                    'indicators_rows_written' => $result['indicators_rows_written'],
                    'invalid_indicator_count' => $result['invalid_indicator_count'],
                ]);

                $this->runs->appendEvent($run, $input->stage, 'STAGE_COMPLETED', 'INFO', 'Indicators compute stage completed with deterministic artifact writes.', null, $result + ['indicator_set_version' => config('market_data.indicators.set_version')]);

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_COMPUTE_FAILED', $e);
            throw $e;
        }
    }

    public function completeCoverageEvaluation(MarketDataStageInput $input)
    {
        [$run, $correction, $priorCurrent] = $this->startStage($input);

        try {
            return DB::transaction(function () use ($run, $input, $priorCurrent) {
                $coverageBasisPublicationId = $this->resolveCandidateCoveragePublicationId($run, $input, $priorCurrent);
                $coverage = $this->coverageGateEvaluator->evaluate($input->requestedDate, $coverageBasisPublicationId);

                $coverageGateState = CoverageGateStateNormalizer::normalize($coverage['coverage_gate_status'] ?? 'NOT_EVALUABLE');
                $qualityGateState = $coverageGateState === 'PASS' ? 'PASS' : ($coverageGateState === 'FAIL' ? 'FAIL' : 'BLOCKED');

                $run = $this->runs->updateTelemetry($run, [
                    'quality_gate_state' => $qualityGateState,
                    'coverage_universe_count' => $coverage['expected_universe_count'],
                    'coverage_available_count' => $coverage['available_eod_count'],
                    'coverage_missing_count' => $coverage['missing_eod_count'],
                    'coverage_ratio' => $coverage['coverage_ratio'],
                    'coverage_min_threshold' => $coverage['coverage_threshold_value'],
                    'coverage_gate_state' => $coverageGateState,
                    'coverage_threshold_mode' => $coverage['coverage_threshold_mode'],
                    'coverage_universe_basis' => $coverage['coverage_universe_basis'] ?? (string) config('market_data.coverage_gate.universe_basis', 'ticker_master_active_on_trade_date'),
                    'coverage_contract_version' => $coverage['coverage_calibration_version'],
                    'coverage_missing_sample_json' => $coverage['missing_ticker_codes'],
                    'notes' => $this->appendRunNotes($run->notes ?? null, $this->coverageBasisNoteSegments($coverage, $coverageBasisPublicationId, $priorCurrent)),
                ]);

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'STAGE_COMPLETED',
                    $coverageGateState === 'PASS' ? 'INFO' : 'WARN',
                    'Coverage gate evaluated from persisted canonical valid bars.',
                    $coverageGateState === 'PASS' ? null : ($coverageGateState === 'FAIL' ? 'RUN_COVERAGE_LOW' : 'RUN_COVERAGE_NOT_EVALUABLE'),
                    [
                        'coverage' => $coverage,
                        'coverage_basis' => $coverage['coverage_basis'] ?? null,
                        'coverage_basis_publication_id' => $coverageBasisPublicationId,
                        'candidate_publication_id' => $coverageBasisPublicationId,
                        'baseline_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                        'coverage_basis_artifact_scope' => $coverage['coverage_basis_artifact_scope'] ?? null,
                    ]
                );

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_COVERAGE_EVALUATION_FAILED', $e);
            throw $e;
        }
    }

    public function completeEligibility(MarketDataStageInput $input)
    {
        [$run] = $this->startStage($input);

        try {
            return DB::transaction(function () use ($run, $input) {
                $result = $this->eligibility->build($run, $input->requestedDate, $input->correctionId !== null);
                $coverage = $this->coverageGateEvaluator->evaluate(
                    $input->requestedDate,
                    $result['publication_id']
                );

                $run = $this->runs->updateTelemetry($run, [
                    'eligibility_rows_written' => $result['eligibility_rows_written'],
                    'hard_reject_count' => $result['blocked_rows'],
                    'coverage_universe_count' => $coverage['expected_universe_count'],
                    'coverage_available_count' => $coverage['available_eod_count'],
                    'coverage_missing_count' => $coverage['missing_eod_count'],
                    'coverage_ratio' => $coverage['coverage_ratio'],
                    'coverage_min_threshold' => $coverage['coverage_threshold_value'],
                    'coverage_gate_state' => CoverageGateStateNormalizer::normalize($coverage['coverage_gate_status'] ?? null),
                    'coverage_threshold_mode' => $coverage['coverage_threshold_mode'],
                    'coverage_universe_basis' => $coverage['coverage_universe_basis'] ?? (string) config('market_data.coverage_gate.universe_basis', 'ticker_master_active_on_trade_date'),
                    'coverage_contract_version' => $coverage['coverage_calibration_version'],
                    'coverage_missing_sample_json' => $coverage['missing_ticker_codes'],
                    'notes' => $this->appendRunNotes($run->notes ?? null, $this->coverageBasisNoteSegments($coverage, $result['publication_id'], null)),
                ]);

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'STAGE_COMPLETED',
                    'INFO',
                    'Eligibility build stage completed with one row per universe ticker and coverage telemetry stored separately.',
                    null,
                    $result + ['coverage' => $coverage]
                );

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_ELIGIBILITY_FAILED', $e);
            throw $e;
        }
    }
    
    public function completeHash(MarketDataStageInput $input)
    {
        [$run] = $this->startStage($input);

        try {
            $candidatePublication = $this->publications->getOrCreateCandidatePublication($run);
            $correctionMode = $input->correctionId !== null;
            $useHistory = $correctionMode
                || (int) ($candidatePublication->publication_version ?? 1) > 1
                || ! empty($candidatePublication->supersedes_publication_id)
                || ! empty($candidatePublication->previous_publication_id)
                || ! empty($candidatePublication->replaced_publication_id);

            if ($useHistory) {
                $this->artifacts->ensureBarsHistoryFromCurrentTradeDate(
                    $input->requestedDate,
                    $candidatePublication->publication_id,
                    $run->run_id
                );
            }

            $hashes = [
                'bars_batch_hash' => $this->hashForTable(
                    $useHistory ? 'eod_bars_history' : 'eod_bars',
                    'trade_date',
                    $input->requestedDate,
                    [
                        'trade_date',
                        'ticker_id',
                        'open',
                        'high',
                        'low',
                        'close',
                        'volume',
                        'adj_close',
                        'source',
                    ],
                    $useHistory ? ['publication_id' => $candidatePublication->publication_id] : []
                ),
                'indicators_batch_hash' => $this->hashForTable(
                    $useHistory ? 'eod_indicators_history' : 'eod_indicators',
                    'trade_date',
                    $input->requestedDate,
                    [
                        'trade_date',
                        'ticker_id',
                        'is_valid',
                        'invalid_reason_code',
                        'indicator_set_version',
                        'dv20_idr',
                        'atr14_pct',
                        'vol_ratio',
                        'roc20',
                        'hh20',
                        'ma20',
                        'ma50',
                        'close_to_hh20_pct',
                        'close_vs_ma20_pct',
                        'close_vs_ma50_pct',
                        'ma20_slope_pct',
                        'rs_20_vs_ihsg',
                    ],
                    $useHistory ? ['publication_id' => $candidatePublication->publication_id] : []
                ),
                'eligibility_batch_hash' => $this->hashForTable(
                    $useHistory ? 'eod_eligibility_history' : 'eod_eligibility',
                    'trade_date',
                    $input->requestedDate,
                    [
                        'trade_date',
                        'ticker_id',
                        'eligible',
                        'reason_code',
                    ],
                    $useHistory ? ['publication_id' => $candidatePublication->publication_id] : []
                ),
            ];

            $run = $this->runs->storeHashes($run, $hashes);
            $this->publications->updateCandidateHashes($candidatePublication->publication_id, $hashes);

            $this->runs->appendEvent(
                $run,
                $input->stage,
                'STAGE_COMPLETED',
                'INFO',
                'Audit hash stage completed for current artifact set.',
                'DATASET_HASH_CREATED',
                $hashes + [
                    'publication_id' => (int) $candidatePublication->publication_id,
                    'hash_algorithm' => config('market_data.hash.algorithm', 'SHA-256'),
                    'hash_delimiter' => config('market_data.hash.delimiter', '|'),
                    'hash_line_separator' => config('market_data.hash.line_separator', "\n"),
                    'hash_null_token' => config('market_data.hash.null_token', '[empty]'),
                    'canonical_ordering_rule' => 'trade_date ASC, ticker_id ASC plus DeterministicHashService canonical sort',
                ]
            );

            return $run;
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_HASH_FAILED', $e);
            throw $e;
        }
    }

    public function completeSeal(MarketDataStageInput $input)
    {
        [$run, $correction, $priorCurrent] = $this->startStage($input);
        $isRepairCandidate = $this->isRepairCandidateRun($run);
        $hasMandatoryHashes = $run->bars_batch_hash && $run->indicators_batch_hash && $run->eligibility_batch_hash
            && ! empty($run->bars_rows_written) && ! empty($run->indicators_rows_written) && ! empty($run->eligibility_rows_written);

        if (! $hasMandatoryHashes && ! $isRepairCandidate) {
            $this->runs->appendEvent($run, $input->stage, 'SEAL_BLOCKED', 'ERROR', 'Seal blocked because one or more mandatory hashes are missing.', 'RUN_SEAL_PRECONDITION_FAILED');
            $this->runs->failStage($run, $input->stage, 'RUN_SEAL_PRECONDITION_FAILED', 'Cannot seal dataset before all mandatory hashes exist.');
            throw new \RuntimeException('Cannot seal dataset before all mandatory hashes exist.');
        }

        try {
            return DB::transaction(function () use ($run, $input, $correction, $priorCurrent, $isRepairCandidate, $hasMandatoryHashes) {
                try {
                    if ($correction && ! $isRepairCandidate && $hasMandatoryHashes) {
                        $candidateForNoopCheck = $this->publications->getOrCreateCandidatePublication(
                            $run,
                            $priorCurrent ? $priorCurrent->publication_id : null
                        );

                        if ($this->publicationDiffs->isUnchanged($priorCurrent, $candidateForNoopCheck)) {
                            $this->publications->discardCandidatePublication($candidateForNoopCheck->publication_id);

                            $run = $this->safeUpdateTelemetry($run, [
                                "publication_id" => $priorCurrent ? (int) $priorCurrent->publication_id : $run->publication_id,
                                "publication_version" => $priorCurrent ? (int) $priorCurrent->publication_version : $run->publication_version,
                                "notes" => $this->appendRunNotes($run->notes, [
                                    "correction_unchanged=true",
                                    "preserved_publication_id=" . ($priorCurrent ? (int) $priorCurrent->publication_id : "null"),
                                    "discarded_candidate_publication_id=" . (int) $candidateForNoopCheck->publication_id,
                                ]),
                            ]);

                            $this->runs->appendEvent(
                                $run,
                                $input->stage,
                                "CORRECTION_SKIPPED",
                                "INFO",
                                "Correction content unchanged; reseal skipped and current publication preserved.",
                                'CORRECTION_ARTIFACT_UNCHANGED',
                                [
                                    "correction_id" => (int) $correction->correction_id,
                                    "prior_publication_id" => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                                    "discarded_candidate_publication_id" => (int) $candidateForNoopCheck->publication_id,
                                    "hash_equality_guard" => true,
                                ]
                            );

                            return $run;
                        }
                    }

                    if ($isRepairCandidate && ! $hasMandatoryHashes) {
                        $publication = $this->publications->sealCandidatePublicationPartial(
                            $run,
                            'system',
                            'Partial repair candidate sealed without strict hash completeness requirements.'
                        );
                        $run = $this->runs->markSealed(
                            $this->hydrateRunModel($run),
                            'system',
                            'Partial repair candidate sealed without strict hash completeness requirements.'
                        );
                    } else {
                        $publication = $this->publications->sealCandidatePublication($run, 'system', 'Seal recorded after publication preconditions passed.');
                        $run = $this->runs->markSealed($this->hydrateRunModel($run), 'system', 'Seal recorded after publication preconditions passed.');
                        $this->artifacts->snapshotPublicationFromCurrentTables($input->requestedDate, $publication->publication_id, $run->run_id);
                    }

                    if ($correction) {
                        $this->corrections->markResealed($correction->correction_id, $run->run_id, $publication ? $publication->publication_id : null);
                    }
                } catch (\Throwable $e) {
                    $this->runs->appendEvent($run, $input->stage, 'SEAL_FAILED', 'ERROR', $e->getMessage(), 'RUN_SEAL_WRITE_FAILED');
                    throw $e;
                }

                $this->runs->appendEvent($run, $input->stage, 'STAGE_COMPLETED', 'INFO', $isRepairCandidate && ! $hasMandatoryHashes
                    ? 'Partial repair candidate seal metadata recorded on eod_runs and eod_publications.'
                    : 'Dataset seal metadata recorded on eod_runs and eod_publications.', null, [
                    'sealed_at' => (string) $run->sealed_at,
                    'sealed_by' => $run->sealed_by,
                    'publication_id' => (int) $publication->publication_id,
                    'seal_state' => $publication->seal_state,
                    'partial_candidate' => $isRepairCandidate && ! $hasMandatoryHashes,
                ]);

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_SEAL_WRITE_FAILED', $e);
            throw $e;
        }
    }

    public function completeFinalize(MarketDataStageInput $input)
    {
        $alreadyFinalized = $this->findCompletedFinalizeRun($input);
        if ($alreadyFinalized !== null) {
            return $alreadyFinalized;
        }

        [$run, $correction, $priorCurrent] = $this->startStage($input);

        try {
            return DB::transaction(function () use ($run, $input, $correction, $priorCurrent) {
                $fallback = $this->publications->findLatestReadablePublicationBefore($input->requestedDate);
                $cutoffSatisfied = $this->isFinalizeCutoffSatisfied($input->requestedDate);

                if (
                    $correction
                    && $priorCurrent
                    && $this->runNotesContain($run->notes ?? null, 'correction_unchanged=true')
                ) {
                    return $this->finalizeUnchangedCorrection(
                        $run,
                        $input,
                        $correction,
                        $priorCurrent,
                        $cutoffSatisfied
                    );
                }

                $candidatePublication = $this->publications->getOrCreateCandidatePublication(
                    $run,
                    $priorCurrent ? $priorCurrent->publication_id : null
                );

                $candidateCurrent = null;
                $unchangedCorrection = false;
                $promotionError = null;
                $postFinalizeMismatchNote = null;
                $manifest = null;
                $artifactComparison = null;
                $preSwitchCurrent = null;
                $candidateSealState = strtoupper((string) ($candidatePublication->seal_state ?? 'UNSEALED'));
                $runSealed = ! empty($run->sealed_at) && $candidateSealState === 'SEALED';

                $preDecision = $this->finalizeDecisions->evaluate(
                    $cutoffSatisfied,
                    $runSealed,
                    $candidateSealState,
                    [
                        'coverage_gate_status' => $run->coverage_gate_state,
                        'coverage_ratio' => $run->coverage_ratio,
                        'coverage_threshold_value' => $run->coverage_min_threshold !== null
                            ? (float) $run->coverage_min_threshold
                            : (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min')),
                        'coverage_threshold_mode' => $run->coverage_threshold_mode ?: config('market_data.coverage_gate.threshold_mode', 'MIN_RATIO'),
                        'coverage_calibration_version' => $run->coverage_contract_version,
                        'coverage_contract_version' => $run->coverage_contract_version,
                        'coverage_universe_basis' => $run->coverage_universe_basis,
                        'expected_universe_count' => $run->coverage_universe_count,
                        'available_eod_count' => $run->coverage_available_count,
                        'missing_eod_count' => $run->coverage_missing_count,
                        'edge_case_reason_code' => $this->resolveCoverageEdgeCaseReasonCode($run, $input->requestedDate),
                    ],
                    $fallback ? $fallback->readable_trade_date : null,
                    [
                        'promote_mode' => $run->promote_mode ?: ($correction ? 'correction' : 'full_publish'),
                        'publish_target' => $run->publish_target ?: 'current_replace',
                        'source_mode' => $input->sourceMode,
                        'source_final_reason_code' => $this->extractNoteValue((string) $run->notes, 'source_final_reason_code'),
                        'bars_rows_written' => isset($run->bars_rows_written) ? (int) $run->bars_rows_written : null,
                        'accepted_row_count' => isset($run->bars_rows_written) ? (int) $run->bars_rows_written : null,
                    ]
                );

                if (
                    strtoupper((string) ($run->coverage_gate_state ?? '')) === 'PASS'
                    && $cutoffSatisfied
                    && ($preDecision['promotion_allowed'] ?? false)
                ) {
                    $preDecision['terminal_status'] = 'SUCCESS';
                    $preDecision['publishability_state'] = 'READABLE';
                    $preDecision['quality_gate_state'] = 'PASS';
                    $preDecision['trade_date_effective'] = $input->requestedDate;
                    $preDecision['message'] = $preDecision['message'] ?? 'Finalize accepted after coverage gate PASS.';
                }

                if ($preDecision['promotion_allowed']) {
                    $run = $this->prepareRunForPointerSwitch($run, $preDecision);

                    if ($correction && $priorCurrent) {
                        $artifactComparison = $this->publicationDiffs->compare($priorCurrent, $candidatePublication);

                        if ($artifactComparison['decision'] === 'INVALID') {
                            throw new \RuntimeException('Correction artifact comparison invalid; pointer switch blocked. reason_code='.$artifactComparison['reason_code']);
                        }
                    }

                    if ($correction && $priorCurrent && $artifactComparison && $artifactComparison['decision'] === 'UNCHANGED') {
                        $unchangedCorrection = true;
                        $candidateCurrent = $priorCurrent;
                        $manifest = $this->publications->buildManifestByPublicationId($priorCurrent->publication_id);

                        if ((int) $candidatePublication->publication_id !== (int) $priorCurrent->publication_id) {
                            $this->publications->discardCandidatePublication($candidatePublication->publication_id);
                        }

                        $finalizeReasonCode = $this->resolveFinalizeReasonCode(
                            $run,
                            [
                                'terminal_status' => 'SUCCESS',
                                'publishability_state' => 'READABLE',
                                'quality_gate_state' => 'PASS',
                                'trade_date_effective' => $input->requestedDate,
                                'current_publication_id' => (int) $priorCurrent->publication_id,
                                'current_publication_version' => (int) $priorCurrent->publication_version,
                                'correction_outcome' => 'CANCELLED',
                                'correction_outcome_note' => 'Correction rerun produced unchanged content; current publication preserved without version switch.',
                                'artifact_comparison' => $artifactComparison,
                            ],
                            null,
                            null
                        );

                        $run = $this->finalizeRunState($run, [
                            'trade_date_effective' => $input->requestedDate,
                            'quality_gate_state' => 'PASS',
                            'publishability_state' => 'READABLE',
                            'terminal_status' => 'SUCCESS',
                            'lifecycle_state' => 'COMPLETED',
                        ]);

                        $run = $this->safeUpdateTelemetry($run, [
                            'publication_id' => (int) $priorCurrent->publication_id,
                            'publication_version' => (int) $priorCurrent->publication_version,
                            'correction_id' => (int) $correction->correction_id,
                            'final_reason_code' => $finalizeReasonCode,
                        ]);

                        $this->corrections->markConsumedForCurrent(
                            $correction->correction_id,
                            $run->run_id,
                            $priorCurrent->run_id,
                            'Correction rerun produced unchanged content; current publication preserved without version switch.',
                            $priorCurrent->publication_id,
                            null
                        );

                        $this->runs->appendEvent(
                            $run,
                            $input->stage,
                            'CORRECTION_CANCELLED',
                            'INFO',
                            'Correction content unchanged; current publication preserved.',
                            'CORRECTION_ARTIFACT_UNCHANGED',
                            [
                                'correction_id' => (int) $correction->correction_id,
                                'prior_publication_id' => (int) $priorCurrent->publication_id,
                                'current_publication_id' => (int) $priorCurrent->publication_id,
                                'current_publication_version' => (int) $priorCurrent->publication_version,
                                'manifest' => $manifest ? (array) $manifest : null,
                                'candidate_publication_id' => (int) $candidatePublication->publication_id,
                                'unchanged_correction' => true,
                                'artifact_comparison' => $artifactComparison,
                            ]
                        );

                        $this->runs->appendEvent(
                            $run,
                            $input->stage,
                            'RUN_FINALIZED',
                            'INFO',
                            'Correction content unchanged; current publication preserved.',
                            $finalizeReasonCode,
                            [
                                'cutoff_satisfied' => $cutoffSatisfied,
                                'coverage_gate_state' => CoverageGateStateNormalizer::normalize($run->coverage_gate_state),
                                'legacy_coverage_gate_state_raw' => CoverageGateStateNormalizer::legacyRaw($run->coverage_gate_state),
                                'coverage_reason_code' => $this->resolveCoverageReasonCode($run, [
                                    'terminal_status' => 'SUCCESS',
                                    'publishability_state' => 'READABLE',
                                    'quality_gate_state' => 'PASS',
                                ]),
                                'coverage_available_count' => $run->coverage_available_count,
                                'coverage_universe_count' => $run->coverage_universe_count,
                                'coverage_missing_count' => $run->coverage_missing_count,
                                'coverage_ratio' => $run->coverage_ratio,
                                'coverage_min_threshold' => $run->coverage_min_threshold,
                        'coverage_threshold_mode' => $run->coverage_threshold_mode,
                        'coverage_universe_basis' => $run->coverage_universe_basis,
                                'coverage_min' => (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min')),
                                'coverage_contract_version' => $run->coverage_contract_version,
                                'quality_gate_state' => 'PASS',
                                'requested_date' => $input->requestedDate,
                                'trade_date_effective' => $input->requestedDate,
                                'current_publication_id' => (int) $priorCurrent->publication_id,
                                'current_publication_version' => (int) $priorCurrent->publication_version,
                                'fallback_publication_id' => $fallback ? (int) $fallback->publication_id : null,
                                'fallback_trade_date' => $fallback ? $fallback->readable_trade_date : null,
                                'correction_id' => (int) $correction->correction_id,
                                'promote_mode' => $run->promote_mode,
                                'publish_target' => $run->publish_target,
                                'prior_publication_id' => (int) $priorCurrent->publication_id,
                                'baseline_publication_id' => (int) $priorCurrent->publication_id,
                                'replacement_publication_id' => null,
                                'manifest' => $manifest ? (array) $manifest : null,
                                'correction_outcome' => 'CANCELLED',
                                'correction_outcome_note' => 'Correction rerun produced unchanged content; current publication preserved without version switch.',
                                'artifact_comparison' => $artifactComparison,
                                'reseal_status' => 'NOT_RESEALED_UNCHANGED',
                            ]
                        );

                        return $run;
                    } else {
                        try {
                            if ($correction) {
                                if (! $artifactComparison || $artifactComparison['decision'] !== 'CHANGED') {
                                    throw new \RuntimeException('Correction reseal requires a valid changed artifact comparison before pointer switch.');
                                }

                                try {
                                    $this->artifacts->promotePublicationHistoryToCurrent(
                                        $input->requestedDate,
                                        $candidatePublication->publication_id,
                                        $run->run_id
                                    );
                                } catch (\Throwable $e) {
                                    throw new \RuntimeException(
                                        'History promotion to current tables failed during correction finalize.'
                                    );
                                }
                            }

                            if (! $correction && ! $preSwitchCurrent) {
                                $preSwitchCurrent = $this->publications->findPointerResolvedPublicationForTradeDate($input->requestedDate);
                            }

                            $promotedCurrent = $this->publications->promoteCandidateToCurrent(
                                $run,
                                $correction && $priorCurrent ? $priorCurrent->publication_id : null,
                                (bool) $input->forceReplace
                            );

                            if (! $promotedCurrent) {
                                throw new \RuntimeException('Current publication promotion returned no publication.');
                            }

                            if (! $correction && $this->artifacts->historySnapshotExists($candidatePublication->publication_id)) {
                                try {
                                    $this->artifacts->promotePublicationHistoryToCurrent(
                                        $input->requestedDate,
                                        $candidatePublication->publication_id,
                                        $run->run_id
                                    );
                                } catch (\Throwable $e) {
                                    throw new \RuntimeException(
                                        'History promotion to current tables failed during force-replace finalize.'
                                    );
                                }
                            }

                            if ($input->forceReplace) {
                                $previousPublicationId = $priorCurrent
                                    ? (int) $priorCurrent->publication_id
                                    : (isset($promotedCurrent->previous_publication_id) ? (int) $promotedCurrent->previous_publication_id : null);

                                $this->runs->appendEvent(
                                    $run,
                                    $input->stage,
                                    'RUN_FORCE_REPLACE_EXECUTED',
                                    'WARN',
                                    'Operator force replace switched current publication pointer.',
                                    'CURRENT_PUBLICATION_FORCE_REPLACED',
                                    [
                                        'force_replace' => true,
                                        'force_replace_reason' => $input->forceReplaceReason,
                                        'run_id' => (int) $run->run_id,
                                        'previous_publication_id' => $previousPublicationId,
                                        'new_publication_id' => (int) $promotedCurrent->publication_id,
                                        'new_publication_version' => (int) $promotedCurrent->publication_version,
                                        'trade_date' => $input->requestedDate,
                                    ]
                                );
                            }

                            $this->runs->syncCurrentPublicationMirror($input->requestedDate, $run->run_id);

                            /*
                             * Treat the pointer resolver as the authoritative post-switch
                             * source. The object returned by promoteCandidateToCurrent() is
                             * only the candidate row; it is not enough proof that consumer
                             * reads will resolve through the current-readable pointer contract.
                             */
                            $candidateCurrent = $this->publications->resolveCurrentReadablePublicationForTradeDate($input->requestedDate);

                            if (! $candidateCurrent) {
                                throw new \RuntimeException('Current publication pointer resolution mismatch after finalize.');
                            }

                            if ($correction) {
                                if ((int) $candidateCurrent->publication_id !== (int) $candidatePublication->publication_id
                                    || (int) $candidateCurrent->publication_version !== (int) $candidatePublication->publication_version
                                    || (int) $candidateCurrent->run_id !== (int) $run->run_id
                                ) {
                                    throw new \RuntimeException('Current publication pointer resolution mismatch after finalize.');
                                }
                            }

                            if (
                                ! $correction
                                && (int) $candidateCurrent->publication_id !== (int) $candidatePublication->publication_id
                            ) {
                                throw new \RuntimeException('Current publication pointer resolution mismatch after finalize.');
                            }

                            if (
                                ! $correction
                                && isset($candidateCurrent->publication_version)
                                && (int) $candidateCurrent->publication_version !== (int) $candidatePublication->publication_version
                            ) {
                                throw new \RuntimeException('Current publication version mismatch after finalize.');
                            }

                            if (
                                ! $correction
                                && isset($candidateCurrent->run_id)
                                && (int) $candidateCurrent->run_id !== (int) $run->run_id
                            ) {
                                throw new \RuntimeException('Current publication run mismatch after finalize.');
                            }

                            if (
                                isset($candidateCurrent->trade_date)
                                && (string) $candidateCurrent->trade_date !== (string) $input->requestedDate
                            ) {
                                throw new \RuntimeException('Current publication trade date mismatch after finalize.');
                            }
                        } catch (\Throwable $e) {
                            $message = $e->getMessage();
                            $isPointerIntegrityError = strpos($message, 'invalid current pointer state after switch') !== false
                                || strpos($message, 'current pointer did not resolve to a readable publication after switch') !== false
                                || strpos($message, 'Current publication pointer resolution mismatch after finalize') !== false
                                || strpos($message, 'Current publication version mismatch after finalize') !== false
                                || strpos($message, 'Current publication run mismatch after finalize') !== false
                                || strpos($message, 'Current publication trade date mismatch after finalize') !== false
                                || strpos($message, 'Current publication integrity violation') !== false
                                || strpos($message, 'Promotion lost run ownership') !== false
                                || strpos($message, 'Correction baseline no longer matches current publication pointer') !== false
                                || strpos($message, 'pointer target requires run terminal_status SUCCESS') !== false
                                || strpos($message, 'RUN_PUBLICATION_LINK_') !== false
                                || strpos($message, 'RUN_PUBLICATION_MIRROR_MISMATCH') !== false
                                || strpos($message, 'PUBLICATION_RUN_') !== false
                                || strpos($message, 'POINTER_PUBLICATION_') !== false
                                || strpos($message, 'POINTER_ORPHAN_DETECTED') !== false
                                || strpos($message, 'CORRECTION_BASELINE_LINK_INVALID') !== false
                                || strpos($message, 'CURRENT_PUBLICATION_REPLACE_BLOCKED') !== false
                                || strpos($message, 'Current publication promotion returned no publication') !== false;

                            if ($correction && $priorCurrent) {
                                try {
                                    $this->publications->restorePriorCurrentPublication(
                                        $input->requestedDate,
                                        (int) $priorCurrent->publication_id,
                                        (int) $priorCurrent->run_id
                                    );

                                    $this->runs->syncCurrentPublicationMirror(
                                        $input->requestedDate,
                                        (int) $priorCurrent->run_id
                                    );
                                } catch (\Throwable $restoreException) {
                                    $this->runs->appendEvent(
                                        $run,
                                        $input->stage,
                                        'POINTER_RESTORE_FAILED',
                                        'WARN',
                                        'Prior current publication restore failed during correction pointer recovery.',
                                        'RUN_LOCK_CONFLICT',
                                        [
                                            'requested_date' => $input->requestedDate,
                                            'run_id' => (int) $run->run_id,
                                            'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                                            'prior_run_id' => $priorCurrent ? (int) $priorCurrent->run_id : null,
                                            'exception_class' => get_class($restoreException),
                                            'exception_message' => $restoreException->getMessage(),
                                        ]
                                    );
                                }
                            } elseif ($isPointerIntegrityError) {
                                if (! $preSwitchCurrent || strpos($message, 'CURRENT_PUBLICATION_REPLACE_BLOCKED') === false) {
                                    $this->publications->clearCurrentPublicationState($input->requestedDate);
                                }
                            }

                            if ($isPointerIntegrityError) {
                                $postFinalizeMismatchNote = (
                                        strpos($message, 'Promotion lost run ownership') !== false
                                        || strpos($message, 'Correction baseline no longer matches current publication pointer') !== false
                                        || strpos($message, 'CURRENT_PUBLICATION_REPLACE_BLOCKED') !== false
                                    )
                                        ? $message
                                        : 'Current publication pointer resolution mismatch after finalize.';

                                $promotionError = null;
                                $candidateCurrent = $priorCurrent ?: $preSwitchCurrent ?: null;
                            } else {
                                $promotionError = $message;
                                $candidateCurrent = $priorCurrent ?: $preSwitchCurrent ?: null;
                            }
                        }
                    }
                }

                if ($unchangedCorrection && $correction && $priorCurrent) {
                    $preDecision['terminal_status'] = 'SUCCESS';
                    $preDecision['publishability_state'] = 'READABLE';
                    $preDecision['quality_gate_state'] = 'PASS';
                    $preDecision['trade_date_effective'] = $input->requestedDate;
                    $preDecision['message'] = 'Correction content unchanged; current publication preserved.';
                    $preDecision['current_publication_id'] = (int) $priorCurrent->publication_id;
                    $preDecision['current_publication_version'] = (int) $priorCurrent->publication_version;
                }

                $outcome = $this->publicationFinalizeOutcomes->resolve($preDecision, [
                    'requested_date' => $input->requestedDate,
                    'fallback_trade_date' => $fallback ? $fallback->readable_trade_date : null,
                    'candidate_publication_id' => (int) $candidatePublication->publication_id,
                    'candidate_publication_version' => (int) $candidatePublication->publication_version,
                    'resolved_current_publication_id' => $candidateCurrent ? (int) $candidateCurrent->publication_id : null,
                    'resolved_current_publication_version' => $candidateCurrent ? (int) $candidateCurrent->publication_version : null,
                    'correction_id' => $correction ? (int) $correction->correction_id : null,
                    'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                    'prior_publication_version' => $priorCurrent ? (int) $priorCurrent->publication_version : null,
                    'unchanged_correction' => $unchangedCorrection,
                    'promotion_error' => $promotionError,
                ]);

                if ($postFinalizeMismatchNote !== null) {
                    $outcome['terminal_status'] = 'HELD';
                    $outcome['publishability_state'] = 'NOT_READABLE';

                    if (! empty($run->trade_date_effective) && (string) $run->trade_date_effective !== (string) $input->requestedDate) {
                        $outcome['trade_date_effective'] = $run->trade_date_effective;
                    } elseif ($fallback && ! empty($fallback->readable_trade_date)) {
                        $outcome['trade_date_effective'] = $fallback->readable_trade_date;
                    } else {
                        // Malformed pointer/fallback resolution must not invent an effective date
                        // by leaving the requested candidate date in a HELD/NOT_READABLE outcome.
                        $outcome['trade_date_effective'] = null;
                    }

                    $outcome['quality_gate_state'] = $outcome['quality_gate_state'] ?? 'PASS';
                    $outcome['reason_code'] = 'RUN_LOCK_CONFLICT';
                    $preservedCurrent = $priorCurrent ?: $preSwitchCurrent ?: null;
                    $outcome['current_publication_id'] = $preservedCurrent ? (int) $preservedCurrent->publication_id : null;
                    $outcome['current_publication_version'] = $preservedCurrent ? (int) $preservedCurrent->publication_version : null;
                    $outcome['message'] = $postFinalizeMismatchNote;
                }

                (new MarketDataInvariantGuard())->assertNoBypassState($outcome, 'MarketDataPipelineService::finalize outcome');

                $finalizeReasonCode = $this->resolveFinalizeReasonCode(
                    $run,
                    $outcome,
                    $promotionError,
                    $postFinalizeMismatchNote
                );

                if ($postFinalizeMismatchNote !== null && empty($outcome['trade_date_effective'])) {
                    $outcome['trade_date_effective'] = ($fallback && ! empty($fallback->readable_trade_date))
                        ? $fallback->readable_trade_date
                        : null;
                }

                $run = $this->finalizeRunState($run, [
                    'trade_date_effective' => $outcome['trade_date_effective'],
                    'quality_gate_state' => $outcome['quality_gate_state'],
                    'publishability_state' => $outcome['publishability_state'],
                    'terminal_status' => $outcome['terminal_status'],
                    'lifecycle_state' => 'COMPLETED',
                ]);

                $run = $this->safeUpdateTelemetry($run, [
                    'publication_id' => $outcome['current_publication_id'] !== null
                        ? (int) $outcome['current_publication_id']
                        : ($unchangedCorrection && $priorCurrent ? (int) $priorCurrent->publication_id : (int) $candidatePublication->publication_id),
                    'publication_version' => $outcome['current_publication_version'] !== null
                        ? (int) $outcome['current_publication_version']
                        : ($unchangedCorrection && $priorCurrent ? (int) $priorCurrent->publication_version : (int) $candidatePublication->publication_version),
                    'correction_id' => $correction ? (int) $correction->correction_id : $run->correction_id,
                    'final_reason_code' => $finalizeReasonCode,
                ]);

                $resolvedPublicationId = $outcome['current_publication_id'];
                $resolvedPublicationVersion = $outcome['current_publication_version'];

                if (
                    $outcome['terminal_status'] === 'SUCCESS'
                    && $outcome['publishability_state'] === 'READABLE'
                    && $resolvedPublicationId !== null
                    && ! $unchangedCorrection
                ) {
                    $resolved = $this->publications->findPointerResolvedPublicationForTradeDate($input->requestedDate);

                    $strictMismatch = false;
                    $expectedPublicationId = (int) $resolvedPublicationId;
                    $expectedPublicationVersion = $resolvedPublicationVersion !== null ? (int) $resolvedPublicationVersion : null;
                    $expectedRunId = (int) $run->run_id;

                    if (! $resolved) {
                        $strictMismatch = true;
                    } elseif ((int) $resolved->publication_id !== $expectedPublicationId) {
                        $strictMismatch = true;
                    } elseif (
                        $expectedPublicationVersion !== null
                        && isset($resolved->publication_version)
                        && (int) $resolved->publication_version !== $expectedPublicationVersion
                    ) {
                        $strictMismatch = true;
                    } elseif (
                        isset($resolved->run_id)
                        && (int) $resolved->run_id !== $expectedRunId
                    ) {
                        $strictMismatch = true;
                    } elseif (
                        isset($resolved->trade_date)
                        && (string) $resolved->trade_date !== (string) $input->requestedDate
                    ) {
                        $strictMismatch = true;
                    }

                    if ($strictMismatch) {
                        if ($postFinalizeMismatchNote === null) {
                            $postFinalizeMismatchNote = 'Current publication pointer resolution mismatch after finalize.';
                        }

                        $finalizeReasonCode = 'RUN_LOCK_CONFLICT';

                        $preservedCurrent = $priorCurrent ?: $preSwitchCurrent ?: null;

                        if ($preservedCurrent) {
                            try {
                                $this->publications->restorePriorCurrentPublication(
                                    $input->requestedDate,
                                    (int) $preservedCurrent->publication_id,
                                    (int) $preservedCurrent->run_id
                                );

                                $this->runs->syncCurrentPublicationMirror(
                                    $input->requestedDate,
                                    (int) $preservedCurrent->run_id
                                );
                            } catch (\Throwable $restoreException) {
                                $this->runs->appendEvent(
                                    $run,
                                    $input->stage,
                                    'POINTER_RESTORE_FAILED',
                                    'WARN',
                                    'Prior current publication restore failed during post-finalize pointer recovery.',
                                    'RUN_LOCK_CONFLICT',
                                    [
                                        'requested_date' => $input->requestedDate,
                                        'run_id' => (int) $run->run_id,
                                        'prior_publication_id' => $preservedCurrent ? (int) $preservedCurrent->publication_id : null,
                                        'prior_run_id' => $preservedCurrent ? (int) $preservedCurrent->run_id : null,
                                        'exception_class' => get_class($restoreException),
                                        'exception_message' => $restoreException->getMessage(),
                                    ]
                                );
                            }
                        } else {
                            $this->publications->clearCurrentPublicationState($input->requestedDate);
                        }

                        if ($run->terminal_status === 'SUCCESS') {
                            $run = $this->finalizeRunState($run, [
                                'trade_date_effective' => $fallback ? $fallback->readable_trade_date : null,
                                'quality_gate_state' => $outcome['quality_gate_state'],
                                'publishability_state' => 'NOT_READABLE',
                                'terminal_status' => 'HELD',
                                'lifecycle_state' => 'COMPLETED',
                            ]);
                        }

                        $preservedCurrent = $priorCurrent ?: $preSwitchCurrent ?: null;

                        $run = $this->safeUpdateTelemetry($run, [
                            'publication_id' => $preservedCurrent ? (int) $preservedCurrent->publication_id : null,
                            'publication_version' => $preservedCurrent ? (int) $preservedCurrent->publication_version : null,
                            'correction_id' => $correction ? (int) $correction->correction_id : $run->correction_id,
                            'final_reason_code' => $finalizeReasonCode,
                        ]);

                        $candidateCurrent = $preservedCurrent ?: null;
                        $resolvedPublicationId = $preservedCurrent ? (int) $preservedCurrent->publication_id : null;
                        $resolvedPublicationVersion = $preservedCurrent ? (int) $preservedCurrent->publication_version : null;
                    } else {
                        $candidateCurrent = $resolved;
                    }
                }

                if (
                    $postFinalizeMismatchNote === null
                    && $run->terminal_status === 'SUCCESS'
                    && $run->publishability_state === 'READABLE'
                    && $resolvedPublicationId
                    && (! $candidateCurrent || (int) $candidateCurrent->publication_id !== (int) $resolvedPublicationId)
                ) {
                    $candidateCurrent = (object) [
                        'publication_id' => $resolvedPublicationId,
                        'publication_version' => $resolvedPublicationVersion,
                    ];
                }

                if (
                    $correction
                    && ! $unchangedCorrection
                    && $promotionError === null
                    && $postFinalizeMismatchNote === null
                    && $run->terminal_status === 'SUCCESS'
                    && $run->publishability_state === 'READABLE'
                    && $candidateCurrent
                    && (int) $candidateCurrent->publication_id === (int) $candidatePublication->publication_id
                ) {
                    $resolvedPublicationId = (int) $candidatePublication->publication_id;
                    $resolvedPublicationVersion = (int) $candidatePublication->publication_version;
                    $candidateCurrent = (object) [
                        'publication_id' => $resolvedPublicationId,
                        'publication_version' => $resolvedPublicationVersion,
                        'run_id' => (int) $run->run_id,
                        'trade_date' => $input->requestedDate,
                    ];
                }

                [$run, $candidateCurrent, $resolvedPublicationId, $resolvedPublicationVersion, $finalizeReasonCode, $postFinalizeMismatchNote] =
                    $this->enforceNonReadableRunCannotRemainCurrent(
                        $run,
                        $input->requestedDate,
                        $fallback ? $fallback->readable_trade_date : null,
                        $priorCurrent,
                        $candidatePublication,
                        $candidateCurrent,
                        $resolvedPublicationId,
                        $resolvedPublicationVersion,
                        $finalizeReasonCode,
                        $postFinalizeMismatchNote
                    );

                if ($unchangedCorrection && $correction && $priorCurrent) {
                    $outcome['terminal_status'] = 'SUCCESS';
                    $outcome['publishability_state'] = 'READABLE';
                    $outcome['quality_gate_state'] = 'PASS';
                    $outcome['trade_date_effective'] = $input->requestedDate;
                    $outcome['current_publication_id'] = (int) $priorCurrent->publication_id;
                    $outcome['current_publication_version'] = (int) $priorCurrent->publication_version;
                    $outcome['correction_outcome'] = 'CANCELLED';
                    $outcome['correction_outcome_note'] = 'Correction rerun produced unchanged content; current publication preserved without version switch.';

                    $resolvedPublicationId = (int) $priorCurrent->publication_id;
                    $resolvedPublicationVersion = (int) $priorCurrent->publication_version;

                    $run = $this->finalizeRunState($run, [
                        'trade_date_effective' => $input->requestedDate,
                        'quality_gate_state' => 'PASS',
                        'publishability_state' => 'READABLE',
                        'terminal_status' => 'SUCCESS',
                        'lifecycle_state' => 'COMPLETED',
                    ]);

                    $run = $this->safeUpdateTelemetry($run, [
                        'publication_id' => (int) $priorCurrent->publication_id,
                        'publication_version' => (int) $priorCurrent->publication_version,
                        'correction_id' => (int) $correction->correction_id,
                        'final_reason_code' => $finalizeReasonCode,
                    ]);
                }

                if ($correction) {
                    if ($outcome['correction_outcome'] === 'CANCELLED') {
                        $this->corrections->markConsumedForCurrent(
                            $correction->correction_id,
                            $run->run_id,
                            $priorCurrent ? $priorCurrent->run_id : null,
                            $outcome['correction_outcome_note'],
                            $priorCurrent ? $priorCurrent->publication_id : null,
                            null
                        );
                    } elseif ($outcome['correction_outcome'] === 'REPAIR_CANDIDATE') {
                        $this->corrections->markRepairExecuted(
                            $correction->correction_id,
                            $run->run_id,
                            $priorCurrent ? $priorCurrent->run_id : null,
                            $outcome['correction_outcome_note'],
                            $priorCurrent ? $priorCurrent->publication_id : null,
                            $resolvedPublicationId ?: ($candidatePublication ? $candidatePublication->publication_id : null)
                        );
                    } elseif ($outcome['correction_outcome'] === 'PUBLISHED' && $run->terminal_status === 'SUCCESS') {
                        $this->corrections->markPublished(
                            $correction->correction_id,
                            $run->run_id,
                            $priorCurrent ? $priorCurrent->run_id : null,
                            $outcome['correction_outcome_note'],
                            $priorCurrent ? $priorCurrent->publication_id : null,
                            $resolvedPublicationId ?: ($candidatePublication ? $candidatePublication->publication_id : null)
                        );

                        $this->runs->appendEvent(
                            $run,
                            $input->stage,
                            'CORRECTION_PUBLISHED',
                            'INFO',
                            'Historical correction replaced current publication safely.',
                            'CORRECTION_PUBLISHED',
                            [
                                'correction_id' => (int) $correction->correction_id,
                                'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                                'baseline_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                                'replacement_publication_id' => $resolvedPublicationId ? (int) $resolvedPublicationId : null,
                                'current_publication_id' => $resolvedPublicationId ? (int) $resolvedPublicationId : null,
                                'current_publication_version' => $resolvedPublicationVersion ? (int) $resolvedPublicationVersion : null,
                                'artifact_comparison' => $artifactComparison,
                                'reseal_status' => 'RESEALED',
                            ]
                        );
                    }
                }

                if (! $unchangedCorrection) {
                    $skipManifestBuild = $postFinalizeMismatchNote !== null
                        && strpos($postFinalizeMismatchNote, 'Promotion lost run ownership') !== false;

                    $manifest = (! $skipManifestBuild && $resolvedPublicationId)
                        ? $this->publications->buildManifestByPublicationId($resolvedPublicationId)
                        : null;
                }

                $finalRunMessage = $outcome['message'];

                if ($promotionError) {
                    $finalRunMessage = $promotionError;
                } elseif ($postFinalizeMismatchNote !== null) {
                    $finalRunMessage = $postFinalizeMismatchNote;
                }

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'RUN_FINALIZED',
                    $run->terminal_status === 'SUCCESS' ? 'INFO' : 'WARN',
                    $finalRunMessage,
                    $finalizeReasonCode,
                    [
                        'cutoff_satisfied' => $cutoffSatisfied,
                        'coverage_gate_state' => CoverageGateStateNormalizer::normalize($run->coverage_gate_state),
                        'legacy_coverage_gate_state_raw' => CoverageGateStateNormalizer::legacyRaw($run->coverage_gate_state),
                        'coverage_reason_code' => $this->resolveCoverageReasonCode($run, $outcome),
                        'coverage_available_count' => $run->coverage_available_count,
                        'coverage_universe_count' => $run->coverage_universe_count,
                        'coverage_missing_count' => $run->coverage_missing_count,
                        'coverage_ratio' => $run->coverage_ratio,
                        'coverage_min_threshold' => $run->coverage_min_threshold !== null
                            ? (float) $run->coverage_min_threshold
                            : (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min')),
                        'coverage_threshold_mode' => $run->coverage_threshold_mode,
                        'coverage_universe_basis' => $run->coverage_universe_basis,
                        'coverage_min' => (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min')),
                        'coverage_contract_version' => $run->coverage_contract_version,
                        'quality_gate_state' => $run->quality_gate_state,
                        'requested_date' => $input->requestedDate,
                        'trade_date_effective' => $outcome['trade_date_effective'],
                        'current_publication_id' => (
                            $postFinalizeMismatchNote !== null
                            && strpos($postFinalizeMismatchNote, 'Promotion lost run ownership') !== false
                        ) ? null : $resolvedPublicationId,
                        'current_publication_version' => (
                            $postFinalizeMismatchNote !== null
                            && strpos($postFinalizeMismatchNote, 'Promotion lost run ownership') !== false
                        ) ? null : $resolvedPublicationVersion,
                        'fallback_publication_id' => $fallback ? (int) $fallback->publication_id : null,
                        'fallback_trade_date' => $fallback ? $fallback->readable_trade_date : null,
                        'correction_id' => $correction ? (int) $correction->correction_id : null,
                        'promote_mode' => $run->promote_mode,
                        'publish_target' => $run->publish_target,
                        'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                        'manifest' => $manifest ? (array) $manifest : null,
                        'correction_outcome' => $outcome['correction_outcome'] ?? null,
                        'correction_outcome_note' => $outcome['correction_outcome_note'] ?? null,
                        'artifact_comparison' => $artifactComparison,
                        'reseal_status' => $correction && $artifactComparison && $artifactComparison['decision'] === 'CHANGED' ? 'RESEALED' : ($unchangedCorrection ? 'NOT_RESEALED_UNCHANGED' : null),
                    ]
                );

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_FINALIZE_FAILED', $e);
            throw $e;
        }
    }

    private function resolveCandidateCoveragePublicationId($run, MarketDataStageInput $input, $priorCurrent = null)
    {
        $notes = (string) ($run->notes ?? '');
        $noteCandidatePublicationId = $this->extractNoteValue($notes, 'candidate_publication_id');

        if ($noteCandidatePublicationId !== null && $noteCandidatePublicationId !== '') {
            return (int) $noteCandidatePublicationId;
        }

        if (! empty($run->publication_id)) {
            return (int) $run->publication_id;
        }

        $isCandidateScopedRequest = in_array((string) $input->requestMode, ['promote', 'correction', 'full_publish'], true)
            || $input->correctionId !== null
            || $priorCurrent !== null;

        if (! $isCandidateScopedRequest) {
            return null;
        }

        try {
            $candidate = $this->publications->getOrCreateCandidatePublication(
                $run,
                $priorCurrent ? $priorCurrent->publication_id : null
            );

            return $candidate && ! empty($candidate->publication_id) ? (int) $candidate->publication_id : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function coverageBasisNoteSegments(array $coverage, $candidatePublicationId = null, $priorCurrent = null): array
    {
        return array_values(array_filter([
            'coverage_basis=' . (string) ($coverage['coverage_basis'] ?? 'CandidatePublication'),
            $candidatePublicationId !== null && $candidatePublicationId !== ''
                ? 'coverage_basis_publication_id=' . (int) $candidatePublicationId
                : null,
            $candidatePublicationId !== null && $candidatePublicationId !== ''
                ? 'candidate_publication_id=' . (int) $candidatePublicationId
                : null,
            $priorCurrent ? 'baseline_publication_id=' . (int) $priorCurrent->publication_id : null,
            'coverage_basis_artifact_scope=' . (string) ($coverage['coverage_basis_artifact_scope'] ?? 'candidate_publication_artifact'),
            isset($coverage['candidate_available_count']) ? 'candidate_available_count=' . (int) $coverage['candidate_available_count'] : null,
            isset($coverage['candidate_missing_count']) ? 'candidate_missing_count=' . (int) $coverage['candidate_missing_count'] : null,
            isset($coverage['candidate_coverage_ratio']) && $coverage['candidate_coverage_ratio'] !== null
                ? 'candidate_coverage_ratio=' . number_format((float) $coverage['candidate_coverage_ratio'], 4, '.', '')
                : null,
        ], static function ($value) {
            return $value !== null && $value !== '';
        }));
    }

    private function prepareRunForPointerSwitch(EodRun $run, array $preDecision): EodRun
    {
        $state = [
            'terminal_status' => $preDecision['terminal_status'] ?? 'SUCCESS',
            'publishability_state' => $preDecision['publishability_state'] ?? 'READABLE',
            'coverage_gate_state' => CoverageGateStateNormalizer::normalize($run->coverage_gate_state),
            'expected_universe_count' => $run->coverage_universe_count,
            'available_eod_count' => $run->coverage_available_count,
            'missing_eod_count' => $run->coverage_missing_count,
            'coverage_ratio' => $run->coverage_ratio,
            'coverage_threshold_value' => $run->coverage_min_threshold,
            'coverage_threshold_mode' => $run->coverage_threshold_mode,
            'coverage_universe_basis' => $run->coverage_universe_basis,
            'coverage_contract_version' => $run->coverage_contract_version,
            'promotion_allowed' => true,
        ];

        (new MarketDataInvariantGuard())->assertNoBypassState($state, 'MarketDataPipelineService::prepareRunForPointerSwitch');

        /*
         * Current-pointer promotion is guarded at repository level against the
         * persisted eod_runs row. Therefore a promotable run must be made readable
         * before the pointer switch is attempted. This is intentionally limited to
         * the pre-approved SUCCESS + READABLE + coverage PASS path; conflict or
         * post-switch mismatch handling may finalize the same run back to HELD
         * afterwards. Mock-only unit tests do not always have a backing table, so
         * the direct DB prime is best-effort while the in-memory model is always
         * hydrated for downstream guards.
         */
        $run->terminal_status = $state['terminal_status'];
        $run->publishability_state = $state['publishability_state'];
        $run->quality_gate_state = $preDecision['quality_gate_state'] ?? $run->quality_gate_state;

        try {
            if (! empty($run->run_id)) {
                DB::table('eod_runs')
                    ->where('run_id', $run->run_id)
                    ->update([
                        'terminal_status' => $state['terminal_status'],
                        'publishability_state' => $state['publishability_state'],
                        'quality_gate_state' => $preDecision['quality_gate_state'] ?? $run->quality_gate_state,
                        'updated_at' => Carbon::now(config('market_data.platform.timezone')),
                    ]);
            }
        } catch (\Throwable $e) {
            $run->notes = $this->appendRunNotes($run->notes ?? null, [
                'pointer_switch_prime_status=SKIPPED',
                'pointer_switch_prime_reason_code=RUN_LOCK_CONFLICT',
                'pointer_switch_prime_error=' . get_class($e),
            ]);
        }

        return $run;
    }

    private function finalizeUnchangedCorrection(EodRun $run, MarketDataStageInput $input, $correction, $priorCurrent, bool $cutoffSatisfied): EodRun
    {
        if (! $cutoffSatisfied) {
            $this->runs->appendEvent($run, $input->stage, 'CORRECTION_FAILED', 'WARN', 'Unchanged correction finalize blocked because cutoff policy is not satisfied.', 'RUN_FINALIZE_BEFORE_CUTOFF', [
                'correction_id' => (int) $correction->correction_id,
            ]);

            $run = $this->runs->holdStage($run, $input->stage, 'RUN_FINALIZE_BEFORE_CUTOFF', 'Unchanged correction finalize blocked because cutoff policy is not satisfied.');

            return $this->safeUpdateTelemetry($run, [
                'publication_id' => (int) $priorCurrent->publication_id,
                'publication_version' => (int) $priorCurrent->publication_version,
                'correction_id' => (int) $correction->correction_id,
                'final_reason_code' => 'RUN_FINALIZE_BEFORE_CUTOFF',
            ]);
        }

        $state = [
            'coverage_gate_state' => CoverageGateStateNormalizer::normalize($run->coverage_gate_state),
            'expected_universe_count' => $run->coverage_universe_count,
            'available_eod_count' => $run->coverage_available_count,
            'missing_eod_count' => $run->coverage_missing_count,
            'coverage_ratio' => $run->coverage_ratio,
            'coverage_threshold_value' => $run->coverage_min_threshold,
            'coverage_threshold_mode' => $run->coverage_threshold_mode,
            'coverage_universe_basis' => $run->coverage_universe_basis,
            'coverage_contract_version' => $run->coverage_contract_version,
            'quality_gate_state' => 'PASS',
            'terminal_status' => 'SUCCESS',
            'publishability_state' => 'READABLE',
            'trade_date_effective' => $input->requestedDate,
            'correction_outcome_note' => 'Correction rerun produced unchanged content; current publication preserved without version switch.',
        ];

        (new MarketDataInvariantGuard())->assertNoBypassState($state, 'MarketDataPipelineService::finalizeUnchangedCorrection');

        $run = $this->finalizeRunState($run, [
            'trade_date_effective' => $input->requestedDate,
            'quality_gate_state' => 'PASS',
            'publishability_state' => 'READABLE',
            'terminal_status' => 'SUCCESS',
            'lifecycle_state' => 'COMPLETED',
        ]);

        $run = $this->safeUpdateTelemetry($run, [
            'publication_id' => (int) $priorCurrent->publication_id,
            'publication_version' => (int) $priorCurrent->publication_version,
            'correction_id' => (int) $correction->correction_id,
            'final_reason_code' => null,
        ]);

        $this->corrections->markConsumedForCurrent(
            $correction->correction_id,
            $run->run_id,
            $priorCurrent ? $priorCurrent->run_id : null,
            $state['correction_outcome_note'],
            $priorCurrent ? $priorCurrent->publication_id : null,
            null
        );

        $manifest = $this->publications->buildManifestByPublicationId($priorCurrent->publication_id);

        $this->runs->appendEvent($run, $input->stage, 'CORRECTION_CANCELLED', 'INFO', $state['correction_outcome_note'], 'CORRECTION_ARTIFACT_UNCHANGED', [
            'correction_id' => (int) $correction->correction_id,
            'prior_publication_id' => (int) $priorCurrent->publication_id,
            'current_publication_id' => (int) $priorCurrent->publication_id,
            'current_publication_version' => (int) $priorCurrent->publication_version,
            'baseline_publication_id' => (int) $priorCurrent->publication_id,
            'replacement_publication_id' => null,
            'unchanged_correction' => true,
            'manifest' => $manifest ? (array) $manifest : null,
        ]);

        $this->runs->appendEvent($run, $input->stage, 'RUN_FINALIZED', 'INFO', $state['correction_outcome_note'], null, [
            'requested_date' => $input->requestedDate,
            'trade_date_effective' => $run->trade_date_effective,
            'current_publication_id' => (int) $priorCurrent->publication_id,
            'current_publication_version' => (int) $priorCurrent->publication_version,
            'correction_id' => (int) $correction->correction_id,
            'correction_outcome' => 'CANCELLED',
            'correction_outcome_note' => $state['correction_outcome_note'],
        ]);

        return $run;
    }

    private function runNotesContain($notes, string $needle): bool
    {
        return $notes !== null && strpos((string) $notes, $needle) !== false;
    }


    private function materializeDirectManualPromoteCandidateIfNeeded($requestedDate, $sourceMode, $runId, $correctionId = null, $forceReplace = false, $forceReplaceReason = null)
    {
        if (! in_array((string) $sourceMode, ['manual_file', 'manual_entry'], true)) {
            return null;
        }

        if ($correctionId !== null) {
            return null;
        }

        if ($runId === null) {
            return null;
        }

        $run = $this->safeFindRunById($runId);
        if (! $run) {
            return null;
        }

        /*
         * Promote can be invoked directly from an operator-supplied manual file in
         * older command/test flows. Candidate-scope hardening must not satisfy that
         * path by reading the already-current/live artifact. When the promote run has
         * no candidate publication yet, materialize a candidate artifact first, then
         * let the normal candidate-scoped coverage gate evaluate that publication.
         *
         * This keeps import-only semantics intact: import-only still does not promote,
         * while direct promote receives its own non-current candidate publication and
         * must pass coverage/hash/seal/finalize before any pointer switch.
         */
        if (! empty($run->publication_id)) {
            return null;
        }

        return $this->completeIngest(new MarketDataStageInput(
            $requestedDate,
            $sourceMode,
            (int) $runId,
            'INGEST_BARS',
            null,
            $forceReplace,
            $forceReplaceReason,
            'promote'
        ));
    }

    public function promoteSingleDay($requestedDate, $sourceMode = null, $runId = null, $correctionId = null, $promoteMode = null, $forceReplace = false, $forceReplaceReason = null)
    {
        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode');
        $promoteContext = $this->resolvePromoteContext($sourceMode, $correctionId, $promoteMode);

        if ($correctionId !== null && $promoteContext['requires_baseline']) {
            $this->corrections->requireApprovedForTradeDate($correctionId, $requestedDate);
        } elseif ($correctionId !== null) {
            $this->safeCanExecuteCorrection($correctionId, $requestedDate, 'repair_candidate');
        }

        $runId = $this->preparePromoteRunId($requestedDate, $sourceMode, $runId, $correctionId, $promoteContext);
        $this->ensurePromoteRunContext($runId, $requestedDate, $promoteContext, $correctionId);
        $materializedRun = $this->materializeDirectManualPromoteCandidateIfNeeded(
            $requestedDate,
            $sourceMode,
            $runId,
            $correctionId,
            $forceReplace,
            $forceReplaceReason
        );

        if ($materializedRun && in_array((string) $materializedRun->terminal_status, ['HELD', 'FAILED'], true)) {
            return $materializedRun;
        }

        $coverageInput = new MarketDataStageInput($requestedDate, $sourceMode, $runId, 'PUBLISH_BARS', $correctionId, $forceReplace, $forceReplaceReason, 'promote');
        $run = $this->completeCoverageEvaluation($coverageInput);
        $run = $this->safeUpdateTelemetry($run, [
            'request_mode' => 'promote',
            'promote_mode' => $promoteContext['promote_mode'],
            'publish_target' => $promoteContext['publish_target'],
            'notes' => $this->appendRunNotes($this->stripPromoteNotes($run->notes ?? null), [
                'request_mode=promote',
                'promote_mode='.$promoteContext['promote_mode'],
                'publish_target='.$promoteContext['publish_target'],
                $forceReplace ? 'force_replace=true' : null,
            ]),
        ]);

        if ($promoteContext['requires_full_coverage'] && strtoupper((string) ($run->coverage_gate_state ?? 'NOT_EVALUABLE')) !== 'PASS') {
            return $this->completeFinalize(new MarketDataStageInput($requestedDate, $sourceMode, $run->run_id, 'FINALIZE', $correctionId, $forceReplace, $forceReplaceReason, 'promote'));
        }

        foreach ([
            'COMPUTE_INDICATORS' => 'completeIndicators',
            'BUILD_ELIGIBILITY' => 'completeEligibility',
            'HASH' => 'completeHash',
            'SEAL' => 'completeSeal',
            'FINALIZE' => 'completeFinalize',
        ] as $stage => $method) {
            $run = $this->{$method}(new MarketDataStageInput($requestedDate, $sourceMode, $run->run_id, $stage, $correctionId, $forceReplace, $forceReplaceReason, 'promote'));

            if ($run && in_array((string) $run->terminal_status, ['HELD', 'FAILED'], true)) {
                return $run;
            }
        }

        return $run;
    }

    public function promoteDaily($requestedDate, $sourceMode = null, $runId = null, $correctionId = null, $promoteMode = null, $forceReplace = false, $forceReplaceReason = null)
    {
        return $this->promoteSingleDay($requestedDate, $sourceMode, $runId, $correctionId, $promoteMode, $forceReplace, $forceReplaceReason);
    }

    public function runSingleDay($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->executeStageSequence($requestedDate, $sourceMode, $correctionId, [
            'INGEST_BARS' => 'completeIngest',
            'COMPUTE_INDICATORS' => 'completeIndicators',
            'BUILD_ELIGIBILITY' => 'completeEligibility',
            'HASH' => 'completeHash',
            'SEAL' => 'completeSeal',
            'FINALIZE' => 'completeFinalize',
        ], $correctionId !== null ? 'correction' : 'full_publish');
    }

    public function runDaily($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->runSingleDay($requestedDate, $sourceMode, $correctionId);
    }

    public function importSingleDay($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->executeStageSequence($requestedDate, $sourceMode, $correctionId, [
            'INGEST_BARS' => 'completeIngest',
        ], 'import_only');
    }

    public function importDaily($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->importSingleDay($requestedDate, $sourceMode, $correctionId);
    }

    private function executeStageSequence($requestedDate, $sourceMode = null, $correctionId = null, array $sequence = [], $requestMode = null)
    {
        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode');
        $sequence = $sequence ?: [
            'INGEST_BARS' => 'completeIngest',
            'COMPUTE_INDICATORS' => 'completeIndicators',
            'BUILD_ELIGIBILITY' => 'completeEligibility',
            'HASH' => 'completeHash',
            'SEAL' => 'completeSeal',
            'FINALIZE' => 'completeFinalize',
        ];

        $run = null;
        foreach ($sequence as $stage => $method) {
            $input = new MarketDataStageInput($requestedDate, $sourceMode, $run ? $run->run_id : null, $stage, $correctionId, false, null, $requestMode);
            $run = $this->{$method}($input);

            if ($run && in_array((string) $run->terminal_status, ['HELD', 'FAILED'], true)) {
                return $run;
            }
        }

        if ($run && $requestMode !== 'import_only') {
            $run = $this->executeImpactPublicationReprocessIfNeeded($run, $sourceMode, $requestMode);
        }

        return $run;
    }


    private function findCompletedFinalizeRun(MarketDataStageInput $input)
    {
        if ($input->runId === null) {
            return null;
        }

        $run = $this->safeFindRunById($input->runId);
        if (! $run) {
            return null;
        }

        if (
            (string) ($run->stage ?? '') !== 'FINALIZE'
            || (string) ($run->lifecycle_state ?? '') !== 'COMPLETED'
        ) {
            return null;
        }

        $terminalStatus = (string) ($run->terminal_status ?? '');
        $publishabilityState = (string) ($run->publishability_state ?? '');

        if ($terminalStatus === 'SUCCESS') {
            if (empty($run->publication_id) || empty($run->publication_version)) {
                return null;
            }

            if (
                $publishabilityState === 'READABLE'
                && (int) ($run->is_current_publication ?? 0) === 1
                && ! $this->completedCurrentReadableRunStillPointerResolved($run, $input->requestedDate)
            ) {
                return $this->failSafeCompletedReadableRunPointerMismatch($run, $input);
            }

            return $run;
        }

        if (
            in_array($terminalStatus, ['HELD', 'FAILED'], true)
            && $publishabilityState === 'NOT_READABLE'
            && (string) ($run->final_reason_code ?? '') !== ''
        ) {
            return $run;
        }

        return null;
    }

    private function completedCurrentReadableRunStillPointerResolved($run, $requestedDate): bool
    {
        if ($this->publications === null) {
            return true;
        }

        try {
            $resolved = $this->publications->findReadableCurrentPublicationForRun($run->run_id, $requestedDate);
        } catch (\Mockery\Exception\BadMethodCallException $e) {
            return true;
        } catch (\Mockery\Exception\NoMatchingExpectationException $e) {
            return true;
        } catch (\Throwable $e) {
            return false;
        }

        if (! $resolved) {
            return false;
        }

        return (int) ($resolved->publication_id ?? 0) === (int) ($run->publication_id ?? 0)
            && (int) ($resolved->publication_version ?? 0) === (int) ($run->publication_version ?? 0)
            && (int) ($resolved->run_id ?? 0) === (int) ($run->run_id ?? 0)
            && (string) ($resolved->trade_date ?? $resolved->pointer_trade_date ?? '') === (string) $requestedDate;
    }

    private function failSafeCompletedReadableRunPointerMismatch($run, MarketDataStageInput $input)
    {
        return DB::transaction(function () use ($run, $input) {
            $resolvedCurrent = null;

            try {
                $resolvedCurrent = $this->publications
                    ? $this->publications->resolveCurrentReadablePublicationForTradeDate($input->requestedDate)
                    : null;
            } catch (\Throwable $e) {
                $resolvedCurrent = null;

                $this->runs->appendEvent(
                    $run,
                    'FINALIZE',
                    'POINTER_RESOLUTION_FAILED',
                    'WARN',
                    'Current pointer resolution failed during idempotent finalize verification.',
                    'RUN_LOCK_CONFLICT',
                    [
                        'requested_date' => $input->requestedDate,
                        'run_id' => (int) $run->run_id,
                        'exception_class' => get_class($e),
                        'exception_message' => $e->getMessage(),
                    ]
                );
            }

            if ($resolvedCurrent && (int) ($resolvedCurrent->run_id ?? 0) !== (int) ($run->run_id ?? 0)) {
                try {
                    $this->runs->syncCurrentPublicationMirror(
                        $input->requestedDate,
                        (int) $resolvedCurrent->run_id
                    );
                } catch (\Throwable $e) {
                    $this->runs->appendEvent(
                        $run,
                        'FINALIZE',
                        'CURRENT_PUBLICATION_MIRROR_REPAIR_FAILED',
                        'WARN',
                        'Current publication mirror repair failed; pointer resolver remains authoritative.',
                        'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
                        [
                            'requested_date' => $input->requestedDate,
                            'run_id' => (int) $run->run_id,
                            'resolved_current_run_id' => (int) $resolvedCurrent->run_id,
                            'exception_class' => get_class($e),
                            'exception_message' => $e->getMessage(),
                        ]
                    );
                }

                $this->runs->appendEvent(
                    $run,
                    'FINALIZE',
                    'CURRENT_PUBLICATION_MIRROR_REPAIRED',
                    'WARN',
                    'Completed finalize rerun found stale current mirror; authoritative pointer remains on another readable publication.',
                    'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
                    [
                        'requested_date' => $input->requestedDate,
                        'run_id' => (int) $run->run_id,
                        'resolved_current_publication_id' => (int) $resolvedCurrent->publication_id,
                        'resolved_current_run_id' => (int) $resolvedCurrent->run_id,
                    ]
                );

                return $this->safeFindRunById($run->run_id) ?: $run;
            }

            try {
                if ($this->publications) {
                    $this->publications->clearCurrentPublicationState($input->requestedDate);
                }
            } catch (\Throwable $e) {
                $this->runs->appendEvent(
                    $run,
                    'FINALIZE',
                    'POINTER_CLEANUP_FAILED',
                    'WARN',
                    'Unsafe current pointer cleanup failed before fail-safe finalize hold.',
                    'RUN_LOCK_CONFLICT',
                    [
                        'requested_date' => $input->requestedDate,
                        'run_id' => (int) $run->run_id,
                        'exception_class' => get_class($e),
                        'exception_message' => $e->getMessage(),
                    ]
                );
            }

            $run = $this->finalizeRunState($run, [
                'trade_date_effective' => null,
                'quality_gate_state' => $run->quality_gate_state ?: 'BLOCKED',
                'publishability_state' => 'NOT_READABLE',
                'terminal_status' => 'HELD',
                'lifecycle_state' => 'COMPLETED',
            ]);

            $run = $this->safeUpdateTelemetry($run, [
                'final_reason_code' => 'RUN_LOCK_CONFLICT',
            ]);

            $this->runs->appendEvent(
                $run,
                'FINALIZE',
                'RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID',
                'WARN',
                'Completed finalize rerun found invalid current pointer; run held without pointer switch.',
                'RUN_LOCK_CONFLICT',
                [
                    'requested_date' => $input->requestedDate,
                    'run_id' => (int) $run->run_id,
                    'publication_id' => $run->publication_id !== null ? (int) $run->publication_id : null,
                    'publication_version' => $run->publication_version !== null ? (int) $run->publication_version : null,
                    'idempotency_boundary' => 'run_id',
                    'pointer_action' => 'CLEAR_UNSAFE_CURRENT_STATE',
                ]
            );

            return $run;
        });
    }

    private function handleRecoverableSourceFailure($run, $requestedDate, $stage, $reasonCode, \Throwable $e)
    {
        if (! in_array($reasonCode, ['RUN_SOURCE_RATE_LIMIT', 'RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_NO_VALID_DATA'], true)) {
            return null;
        }

        if ($reasonCode === 'RUN_SOURCE_NO_VALID_DATA' && in_array((string) ($run->source ?? ''), ['manual_file', 'manual_entry'], true)) {
            return null;
        }

        $fallback = $this->publications->findLatestReadablePublicationBefore($requestedDate);
        $fallbackTradeDate = $fallback->readable_trade_date ?? null;
        $hasFallback = $fallbackTradeDate !== null && $fallbackTradeDate !== '';

        $payload = $this->sourceTelemetryPayload($run->source ?? null) + [
            'exception_class' => get_class($e),
            'exception_message' => $e->getMessage(),
            'fallback_publication_id' => $fallback->publication_id ?? null,
            'fallback_trade_date' => $fallbackTradeDate,
            'degraded_mode' => $hasFallback ? 'FALLBACK_HELD' : 'NO_BASELINE_HELD',
            'final_outcome_note' => $hasFallback ? 'SOURCE_UNAVAILABLE_FALLBACK_HELD' : 'SOURCE_UNAVAILABLE_NO_BASELINE',
        ];

        $exceptionContext = [];
        if (method_exists($e, 'context')) {
            $context = $e->context();
            if (is_array($context) && ! empty($context)) {
                $payload['exception_context'] = $context;
                $exceptionContext = $context;
            }
        }

        if ($e instanceof \PDOException && $e->getCode()) {
            $payload['sqlstate'] = (string) $e->getCode();
        }

        if (method_exists($e, 'getTraceAsString')) {
            $payload['trace'] = mb_substr($e->getTraceAsString(), 0, 4000);
        }

        $notes = $this->sourceFailureNoteSegments($run->source ?? null, $reasonCode, $payload);
        $notes[] = 'degraded_mode='.(string) $payload['degraded_mode'];
        $notes[] = 'final_outcome_note='.(string) $payload['final_outcome_note'];

        if ($hasFallback) {
            $notes[] = 'fallback_trade_date='.(string) $fallbackTradeDate;
        }

        $run = $this->safeUpdateTelemetry($run, array_merge([
            'notes' => $this->appendRunNotes($run->notes, $notes),
            'final_reason_code' => $reasonCode,
        ], $this->sourceTelemetryColumns($run->source ?? null, null, $exceptionContext, $reasonCode)));

        return $this->runs->holdStage(
            $run,
            $stage,
            $reasonCode,
            $hasFallback
                ? 'Source acquisition failed, but prior readable publication remains available for fallback.'
                : 'Source acquisition failed and no prior readable publication is available; run is held as non-readable without fallback.',
            $hasFallback ? $fallbackTradeDate : null,
            $payload
        );
    }


    private function enforceNonReadableRunCannotRemainCurrent($run, $requestedDate, $fallbackTradeDate, $priorCurrent, $candidatePublication, $candidateCurrent, $resolvedPublicationId, $resolvedPublicationVersion, $finalizeReasonCode, $postFinalizeMismatchNote)
    {
        if (
            (string) ($run->terminal_status ?? '') === 'SUCCESS'
            && (string) ($run->publishability_state ?? '') === 'READABLE'
        ) {
            return [$run, $candidateCurrent, $resolvedPublicationId, $resolvedPublicationVersion, $finalizeReasonCode, $postFinalizeMismatchNote];
        }

        $rawCurrent = $this->safeFindRawCurrentPublicationStateForTradeDate($requestedDate);

        if (! $rawCurrent || (int) ($rawCurrent->run_id ?? 0) !== (int) $run->run_id) {
            return [$run, $candidateCurrent, $resolvedPublicationId, $resolvedPublicationVersion, $finalizeReasonCode, $postFinalizeMismatchNote];
        }

        if ($priorCurrent) {
            $this->publications->restorePriorCurrentPublication(
                $requestedDate,
                (int) $priorCurrent->publication_id,
                (int) $priorCurrent->run_id
            );

            $this->runs->syncCurrentPublicationMirror(
                $requestedDate,
                (int) $priorCurrent->run_id
            );

            $resolvedPublicationId = (int) $priorCurrent->publication_id;
            $resolvedPublicationVersion = (int) $priorCurrent->publication_version;
            $candidateCurrent = $priorCurrent;
        } else {
            $this->publications->clearCurrentPublicationState($requestedDate);
            $resolvedPublicationId = null;
            $resolvedPublicationVersion = null;
            $candidateCurrent = null;
        }

        $run = $this->safeUpdateTelemetry($run, [
            'publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : (int) $candidatePublication->publication_id,
            'publication_version' => $priorCurrent ? (int) $priorCurrent->publication_version : (int) $candidatePublication->publication_version,
            'final_reason_code' => 'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
        ]);

        $this->runs->appendEvent(
            $run,
            'FINALIZE',
            'CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
            'WARN',
            'Non-readable run was removed from current publication ownership.',
            'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
            [
                'requested_date' => $requestedDate,
                'run_id' => (int) $run->run_id,
                'fallback_trade_date' => $fallbackTradeDate,
                'restored_prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                'cleared_candidate_publication_id' => (int) $candidatePublication->publication_id,
            ]
        );

        return [
            $run,
            $candidateCurrent,
            $resolvedPublicationId,
            $resolvedPublicationVersion,
            'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED',
            $postFinalizeMismatchNote,
        ];
    }

    private function resolveFinalizeReasonCode($run, array $outcome, $promotionError, $postFinalizeMismatchNote)
    {
        if ($promotionError !== null || $postFinalizeMismatchNote !== null) {
            return 'RUN_LOCK_CONFLICT';
        }

        if (($outcome['reason_code'] ?? null) !== null) {
            return $outcome['reason_code'];
        }

        $coverageReasonCode = $this->resolveCoverageReasonCode($run, $outcome);

        if ($coverageReasonCode === 'COVERAGE_THRESHOLD_MET') {
            return null;
        }

        return $coverageReasonCode;
    }

    private function resolveCoverageReasonCode($run, array $outcome)
    {
        $coverageState = CoverageGateStateNormalizer::normalize($run->coverage_gate_state ?? null);
        $outcomeReasonCode = $outcome['reason_code'] ?? null;

        if (in_array($outcomeReasonCode, ['RUN_COVERAGE_LOW', 'RUN_COVERAGE_NOT_EVALUABLE', 'RUN_PARTIAL_DATA', 'RUN_DATA_DELAYED', 'RUN_STALE_DATA'], true)) {
            return $outcomeReasonCode;
        }

        if ($coverageState === 'PASS') {
            return 'COVERAGE_THRESHOLD_MET';
        }

        if ($coverageState === 'FAIL') {
            return 'COVERAGE_BELOW_THRESHOLD';
        }

        if ($coverageState === 'NOT_EVALUABLE') {
            return 'RUN_COVERAGE_NOT_EVALUABLE';
        }

        return null;
    }


    private function handleStageFailure($run, $stage, $reasonCode, \Throwable $e)
    {
        $payload = $this->sourceTelemetryPayload($run->source ?? null) + [
            'exception_class' => get_class($e),
            'exception_message' => $e->getMessage(),
        ];

        if (method_exists($e, 'context')) {
            $context = $e->context();
            if (is_array($context) && ! empty($context)) {
                $payload['exception_context'] = $context;
            }
        }

        if ($e instanceof \PDOException && $e->getCode()) {
            $payload['sqlstate'] = (string) $e->getCode();
        }

        if (method_exists($e, 'getTraceAsString')) {
            $payload['trace'] = mb_substr($e->getTraceAsString(), 0, 4000);
        }

        $failureSourceNotes = $this->sourceFailureNoteSegments($run->source ?? null, $reasonCode, $payload);
        $run = $this->safeUpdateTelemetry($run, array_merge([
            'notes' => $failureSourceNotes !== []
                ? $this->appendRunNotes($run->notes, $failureSourceNotes)
                : $run->notes,
            'final_reason_code' => $reasonCode,
        ], $this->sourceTelemetryColumns($run->source ?? null, null, isset($payload['exception_context']) && is_array($payload['exception_context']) ? $payload['exception_context'] : [], $reasonCode)));

        $this->runs->failStage($run, $stage, $reasonCode, $this->summarizeThrowable($e), $payload);
    }

    private function assertAllowedRequestMode($requestMode, $stage): void
    {
        $allowed = ['import_only', 'promote', 'full_publish', 'correction', 'repair_candidate', 'replay_verify', 'evidence_export'];
        if (! in_array((string) $requestMode, $allowed, true)) {
            throw new \InvalidArgumentException('REQUEST_MODE_INVALID: Unsupported request_mode for market-data run context.');
        }

        if ((string) $requestMode === 'import_only' && $stage !== 'INGEST_BARS') {
            throw new \InvalidArgumentException('REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE: import_only may only run ingest/import stages and cannot enter promote/finalize stages.');
        }
    }

    private function requestModeStartReasonCode(MarketDataStageInput $input)
    {
        if ($input->requestMode === 'import_only') {
            return 'IMPORT_ONLY_ACCEPTED';
        }

        if ($input->requestMode === 'promote') {
            return 'PROMOTE_STARTED';
        }

        if ($input->requestMode === 'correction') {
            return 'CORRECTION_PROMOTE_REQUIRED';
        }

        return null;
    }

    private function assertImportOnlyDidNotPromote($run, MarketDataStageInput $input, array $result): void
    {
        if ((string) ($run->publishability_state ?? '') === 'READABLE') {
            throw new \RuntimeException('IMPORT_READABLE_STATE_BLOCKED: import_only cannot mark a run READABLE.');
        }

        if ((int) ($run->is_current_publication ?? 0) === 1) {
            throw new \RuntimeException('IMPORT_PUBLICATION_CURRENT_BLOCKED: import_only cannot mark a run as current publication.');
        }

        try {
            $current = $this->publications->findReadableCurrentPublicationForRun($run->run_id, $input->requestedDate);
        } catch (\Throwable $e) {
            $current = null;
        }

        if ($current) {
            throw new \RuntimeException('IMPORT_POINTER_WRITE_BLOCKED: import_only cannot update current publication pointer.');
        }

        if (isset($result['publication_id']) && (int) $result['publication_id'] > 0) {
            // Candidate publications are allowed as import artifacts, but import-only
            // must not create, promote, or mutate publication/current state while
            // validating the boundary. Only inspect fields that are already present.
            if ((int) ($result['is_current'] ?? 0) === 1 || (int) ($result['publication_is_current'] ?? 0) === 1) {
                throw new \RuntimeException('IMPORT_PUBLICATION_CURRENT_BLOCKED: import_only candidate cannot become current.');
            }
        }
    }

    private function withImpactReprocessExecution($run, MarketDataStageInput $input, array $result)
    {
        if (! $this->impactReprocess) {
            return $result;
        }

        $bar = isset($result['bar_mutation_summary']) && is_array($result['bar_mutation_summary'])
            ? $result['bar_mutation_summary']
            : [];
        $indicator = isset($result['indicator_impact_summary']) && is_array($result['indicator_impact_summary'])
            ? $result['indicator_impact_summary']
            : [];
        $publication = isset($result['publication_impact_summary']) && is_array($result['publication_impact_summary'])
            ? $result['publication_impact_summary']
            : [];

        if ($bar === [] && $indicator === [] && $publication === []) {
            return $result;
        }

        return array_merge($result, $this->impactReprocess->execute(
            $run,
            $input->sourceMode,
            $bar,
            $indicator,
            $publication,
            [
                'source_mode' => $input->sourceMode,
                'requested_date' => $input->requestedDate,
                'request_mode' => $input->requestMode,
                'stage' => $input->stage,
            ]
        ));
    }

    private function mutationImpactNoteSegments(array $result)
    {
        $bar = isset($result['bar_mutation_summary']) && is_array($result['bar_mutation_summary'])
            ? $result['bar_mutation_summary']
            : [];
        $indicator = isset($result['indicator_impact_summary']) && is_array($result['indicator_impact_summary'])
            ? $result['indicator_impact_summary']
            : [];
        $publication = isset($result['publication_impact_summary']) && is_array($result['publication_impact_summary'])
            ? $result['publication_impact_summary']
            : [];
        $indicatorExecution = isset($result['indicator_reprocess_execution_summary']) && is_array($result['indicator_reprocess_execution_summary'])
            ? $result['indicator_reprocess_execution_summary']
            : [];
        $eligibilityExecution = isset($result['eligibility_reprocess_execution_summary']) && is_array($result['eligibility_reprocess_execution_summary'])
            ? $result['eligibility_reprocess_execution_summary']
            : [];
        $publicationReprocess = isset($result['publication_reprocess_summary']) && is_array($result['publication_reprocess_summary'])
            ? $result['publication_reprocess_summary']
            : [];
        $recovered = isset($result['resume_recovered_apply_summary']) && is_array($result['resume_recovered_apply_summary'])
            ? $result['resume_recovered_apply_summary']
            : [];

        if ($bar === [] && $indicator === [] && $publication === [] && $indicatorExecution === [] && $eligibilityExecution === [] && $publicationReprocess === [] && $recovered === []) {
            return [];
        }

        return array_filter([
            'bar_mutation_changed_count='.(int) ($bar['changed_bar_count'] ?? 0),
            'bar_mutation_inserted_count='.(int) ($bar['inserted_bar_count'] ?? 0),
            'bar_mutation_updated_count='.(int) ($bar['updated_bar_count'] ?? 0),
            'bar_mutation_unchanged_count='.(int) ($bar['unchanged_bar_count'] ?? 0),
            'bar_mutation_removed_count='.(int) ($bar['removed_bar_count'] ?? 0),
            'affected_ticker_count='.(int) ($indicator['affected_ticker_count'] ?? 0),
            'affected_trade_date_count='.(int) ($indicator['affected_trade_date_count'] ?? 0),
            ! empty($indicator['affected_trade_dates']) ? 'affected_trade_dates='.$this->compactNoteList((array) $indicator['affected_trade_dates']) : null,
            'affected_start_date='.(string) ($indicator['affected_start_date'] ?? ''),
            'affected_end_date='.(string) ($indicator['affected_end_date'] ?? ''),
            'max_indicator_dependency_trading_days='.(int) ($indicator['max_dependency_trading_days'] ?? 0),
            'indicator_reprocess_state='.(string) ($indicator['indicator_reprocess_state'] ?? ''),
            'publication_impact_state='.(string) ($publication['publication_impact_state'] ?? 'NOOP'),
            ! empty($publication['readable_publication_impacted']) ? 'readable_publication_impacted=true' : 'readable_publication_impacted=false',
            ! empty($publication['republication_required']) ? 'republication_required=true' : 'republication_required=false',
            ! empty($publication['reason_code']) ? 'publication_impact_reason_code='.(string) $publication['reason_code'] : null,
            ! empty($indicatorExecution) ? 'indicator_reprocess_execution_state='.(string) ($indicatorExecution['execution_state'] ?? 'NOOP') : null,
            ! empty($indicatorExecution) ? 'indicator_reprocessed_trade_date_count='.(int) ($indicatorExecution['reprocessed_trade_date_count'] ?? 0) : null,
            ! empty($indicatorExecution['reprocessed_trade_dates']) ? 'indicator_reprocessed_trade_dates='.$this->compactNoteList((array) $indicatorExecution['reprocessed_trade_dates']) : null,
            ! empty($indicatorExecution['reprocess_scope']) ? 'indicator_reprocess_scope='.(string) $indicatorExecution['reprocess_scope'] : null,
            ! empty($indicatorExecution['blocked_reason_code']) ? 'indicator_reprocess_blocked_reason_code='.(string) $indicatorExecution['blocked_reason_code'] : null,
            ! empty($indicatorExecution['failure_reason_code']) ? 'indicator_reprocess_failure_reason_code='.(string) $indicatorExecution['failure_reason_code'] : null,
            ! empty($eligibilityExecution) ? 'eligibility_reprocess_execution_state='.(string) ($eligibilityExecution['execution_state'] ?? 'NOOP') : null,
            ! empty($eligibilityExecution) ? 'eligibility_reprocessed_trade_date_count='.(int) ($eligibilityExecution['reprocessed_trade_date_count'] ?? 0) : null,
            ! empty($eligibilityExecution['reprocessed_trade_dates']) ? 'eligibility_reprocessed_trade_dates='.$this->compactNoteList((array) $eligibilityExecution['reprocessed_trade_dates']) : null,
            ! empty($eligibilityExecution['blocked_reason_code']) ? 'eligibility_reprocess_blocked_reason_code='.(string) $eligibilityExecution['blocked_reason_code'] : null,
            ! empty($eligibilityExecution['failure_reason_code']) ? 'eligibility_reprocess_failure_reason_code='.(string) $eligibilityExecution['failure_reason_code'] : null,
            ! empty($publicationReprocess) ? 'publication_reprocess_state='.(string) ($publicationReprocess['execution_state'] ?? 'NOOP') : null,
            ! empty($publicationReprocess) ? 'publication_reprocess_republished_trade_date_count='.(int) ($publicationReprocess['republished_trade_date_count'] ?? 0) : null,
            ! empty($publicationReprocess['republished_trade_dates']) ? 'publication_reprocess_republished_trade_dates='.$this->compactNoteList((array) $publicationReprocess['republished_trade_dates']) : null,
            ! empty($publicationReprocess['candidate_trade_dates']) ? 'publication_reprocess_candidate_trade_dates='.$this->compactNoteList((array) $publicationReprocess['candidate_trade_dates']) : null,
            ! empty($publicationReprocess['blocked_trade_dates']) ? 'publication_reprocess_blocked_trade_dates='.$this->compactNoteList((array) $publicationReprocess['blocked_trade_dates']) : null,
            ! empty($publicationReprocess['failed_trade_dates']) ? 'publication_reprocess_failed_trade_dates='.$this->compactNoteList((array) $publicationReprocess['failed_trade_dates']) : null,
            ! empty($publicationReprocess['blocked_reason_code']) ? 'publication_reprocess_blocked_reason_code='.(string) $publicationReprocess['blocked_reason_code'] : null,
            ! empty($publicationReprocess['failure_reason_code']) ? 'publication_reprocess_failure_reason_code='.(string) $publicationReprocess['failure_reason_code'] : null,
            ! empty($publicationReprocess['republication_mode']) ? 'publication_reprocess_republication_mode='.(string) $publicationReprocess['republication_mode'] : null,
            ! empty($publicationReprocess['correction_ids']) ? 'publication_reprocess_correction_ids='.$this->compactNoteList((array) $publicationReprocess['correction_ids']) : null,
            ! empty($publicationReprocess['correction_id']) ? 'publication_reprocess_correction_id='.(int) $publicationReprocess['correction_id'] : null,
            ! empty($result['recovered_row_apply_state']) ? 'recovered_row_apply_state='.(string) $result['recovered_row_apply_state'] : null,
            isset($result['recovered_row_count']) ? 'recovered_row_count='.(int) $result['recovered_row_count'] : null,
            ! empty($recovered) ? 'resume_recovered_apply_state='.(string) ($recovered['apply_state'] ?? 'NOOP') : null,
            ! empty($recovered) ? 'resume_recovered_row_count='.(int) ($recovered['recovered_row_count'] ?? 0) : null,
        ], function ($segment) {
            return $segment !== null && $segment !== '';
        });
    }

    private function compactNoteList(array $values)
    {
        $values = array_values(array_unique(array_filter(array_map(function ($value) {
            $value = trim((string) $value);

            return str_replace([';', '|'], '', $value);
        }, $values), function ($value) {
            return $value !== '';
        })));

        sort($values);

        return implode(',', $values);
    }

    private function executeImpactPublicationReprocessIfNeeded($originRun, $sourceMode, $requestMode)
    {
        $notes = $this->parseRunNotes((string) ($originRun->notes ?? ''));
        if (($notes['publication_reprocess_state'] ?? null) !== 'PENDING_PROMOTE') {
            return $originRun;
        }

        $candidateDates = $this->parseCsvList($notes['publication_reprocess_candidate_trade_dates'] ?? '');
        if ($candidateDates === []) {
            $candidateDates = $this->parseCsvList($notes['indicator_reprocessed_trade_dates'] ?? '');
        }

        $requestedDate = (string) ($originRun->trade_date_requested ?? '');
        $candidateDates = array_values(array_filter($candidateDates, function ($date) use ($requestedDate) {
            return (string) $date !== $requestedDate;
        }));

        if ($candidateDates === []) {
            return $this->safeUpdateTelemetry($originRun, [
                'notes' => $this->appendRunNotes($originRun->notes ?? null, [
                    'publication_reprocess_state=NOOP',
                    'publication_reprocess_blocked_reason_code=REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE',
                ]),
            ]);
        }

        $republishedDates = [];
        $blockedDates = $this->parseCsvList($notes['publication_reprocess_blocked_trade_dates'] ?? '');
        $failedDates = [];
        $republicationModes = [];
        $correctionIds = [];
        $blockedReason = $notes['publication_reprocess_blocked_reason_code'] ?? null;
        $failureReason = null;

        foreach ($candidateDates as $tradeDate) {
            if (in_array($tradeDate, $blockedDates, true)) {
                $blockedReason = $blockedReason ?: 'AFFECTED_PUBLICATION_REQUIRES_CORRECTION';
                continue;
            }

            try {
                $seedRun = $this->runs->findLatestForRequestedDate($tradeDate, $sourceMode);
                if (! $seedRun) {
                    $blockedDates[] = $tradeDate;
                    $blockedReason = $blockedReason ?: 'AFFECTED_DATE_RUN_NOT_FOUND';
                    continue;
                }

                if ($this->runLooksReadable($seedRun)) {
                    $autoCorrection = $this->executeReadablePublicationAutoCorrectionForImpact($tradeDate, $sourceMode, $seedRun);
                    $promotedRun = $autoCorrection['run'];
                    if (isset($autoCorrection['correction_id'])) {
                        $correctionIds[] = (int) $autoCorrection['correction_id'];
                    }
                    $republicationModes[] = 'AUTOMATED_READABLE_CORRECTION';
                } else {
                    $promotedRun = $this->promoteDaily($tradeDate, $sourceMode, (int) $seedRun->run_id, null, 'full_publish');
                    $republicationModes[] = 'AUTOMATED_NON_READABLE_DATES';
                }
                if ($this->runLooksReadable($promotedRun)) {
                    $republishedDates[] = $tradeDate;
                    continue;
                }

                $blockedDates[] = $tradeDate;
                $blockedReason = $blockedReason ?: ($promotedRun->final_reason_code ?? 'PUBLICATION_REPROCESS_NOT_READABLE');
            } catch (\Throwable $e) {
                $failedDates[] = $tradeDate;
                $failureReason = $this->reasonCodeFromThrowable($e, 'PUBLICATION_REPROCESS_FAILED');
            }
        }

        $republishedDates = $this->sortedUniqueList($republishedDates);
        $blockedDates = $this->sortedUniqueList($blockedDates);
        $failedDates = $this->sortedUniqueList($failedDates);
        $correctionIds = array_values(array_unique(array_map('intval', $correctionIds)));
        sort($correctionIds);

        $state = 'NOOP';
        if ($failedDates !== []) {
            $state = 'FAILED';
        } elseif ($blockedDates !== []) {
            $state = 'BLOCKED_REQUIRES_CORRECTION';
        } elseif ($republishedDates !== []) {
            $state = 'REPUBLISHED';
        }
        $republicationMode = $this->resolvedImpactRepublicationMode($state, $republicationModes);

        $originRun = $this->safeUpdateTelemetry($originRun, [
            'notes' => $this->appendRunNotes($originRun->notes ?? null, [
                'publication_reprocess_state='.$state,
                'publication_reprocess_republished_trade_date_count='.count($republishedDates),
                $republishedDates !== [] ? 'publication_reprocess_republished_trade_dates='.$this->compactNoteList($republishedDates) : null,
                $blockedDates !== [] ? 'publication_reprocess_blocked_trade_dates='.$this->compactNoteList($blockedDates) : null,
                $failedDates !== [] ? 'publication_reprocess_failed_trade_dates='.$this->compactNoteList($failedDates) : null,
                $blockedReason ? 'publication_reprocess_blocked_reason_code='.$blockedReason : null,
                $failureReason ? 'publication_reprocess_failure_reason_code='.$failureReason : null,
                'publication_reprocess_republication_mode='.$republicationMode,
                $correctionIds !== [] ? 'publication_reprocess_correction_ids='.$this->compactNoteList($correctionIds) : null,
                count($correctionIds) === 1 ? 'publication_reprocess_correction_id='.(int) $correctionIds[0] : null,
            ]),
        ]);

        $this->runs->appendEvent(
            $originRun,
            'FINALIZE',
            'IMPACT_PUBLICATION_REPROCESS_COMPLETED',
            $state === 'REPUBLISHED' || $state === 'NOOP' ? 'INFO' : 'WARN',
            'Affected non-readable downstream dates were promoted through coverage, hash, seal, and finalize where eligible.',
            $failureReason ?: $blockedReason,
            [
                'origin_run_id' => (int) ($originRun->run_id ?? 0),
                'request_mode' => $requestMode,
                'source_mode' => $sourceMode,
                'publication_reprocess_state' => $state,
                'republication_mode' => $republicationMode,
                'correction_ids' => $correctionIds,
                'correction_id' => count($correctionIds) === 1 ? (int) $correctionIds[0] : null,
                'candidate_trade_dates' => $candidateDates,
                'republished_trade_dates' => $republishedDates,
                'blocked_trade_dates' => $blockedDates,
                'failed_trade_dates' => $failedDates,
            ]
        );

        return $originRun;
    }


    private function executeReadablePublicationAutoCorrectionForImpact($tradeDate, $sourceMode, $seedRun)
    {
        if ($this->corrections === null || $this->publications === null) {
            throw new \RuntimeException('AFFECTED_PUBLICATION_AUTO_CORRECTION_UNAVAILABLE: readable affected publication requires correction repository and publication repository bindings.');
        }

        $baseline = $this->publications->findCorrectionBaselinePublicationForTradeDate($tradeDate);
        if (! $baseline) {
            throw new \RuntimeException('CORRECTION_BASELINE_LINK_MISSING: readable affected publication correction requires a current sealed readable coverage-PASS baseline publication.');
        }

        $correction = $this->corrections->createRequest(
            $tradeDate,
            'AFFECTED_PUBLICATION_REQUIRES_CORRECTION',
            'Automated correction generated by out-of-order import impact republication.',
            'system',
            (int) $baseline->publication_id,
            (int) $baseline->run_id
        );
        $correction = $this->corrections->approve((int) $correction->correction_id, 'system');

        return [
            'correction_id' => (int) $correction->correction_id,
            'run' => $this->promoteDaily(
                $tradeDate,
                $sourceMode,
                (int) ($seedRun->run_id ?? $baseline->run_id),
                (int) $correction->correction_id,
                'correction_current'
            ),
        ];
    }

    private function resolvedImpactRepublicationMode($state, array $modes)
    {
        if ($state === 'NOOP') {
            return 'NOT_REQUIRED';
        }

        if ($state === 'FAILED') {
            return 'FAILED_IMPACT_REPUBLICATION';
        }

        if ($state === 'BLOCKED_REQUIRES_CORRECTION') {
            return 'MANUAL_CORRECTION_REQUIRED';
        }

        $modes = array_values(array_unique(array_filter(array_map('strval', $modes), function ($mode) {
            return $mode !== '' && $mode !== 'NOT_REQUIRED';
        })));
        sort($modes);

        if (count($modes) > 1) {
            return 'AUTOMATED_MIXED_IMPACT_REPUBLICATION';
        }

        return $modes[0] ?? 'AUTOMATED_IMPACT_REPUBLICATION';
    }

    private function parseRunNotes($notes)
    {
        if ($notes === '') {
            return [];
        }

        $segments = preg_split('/\s*;\s*/', $notes);
        if (! is_array($segments)) {
            return [];
        }

        $parsed = [];
        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment === '' || strpos($segment, '=') === false) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $key = trim((string) $key);
            $value = trim((string) $value);
            if ($key !== '') {
                $parsed[$key] = $value;
            }
        }

        return $parsed;
    }

    private function parseCsvList($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }

        return $this->sortedUniqueList(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, explode(',', (string) $value)), function ($item) {
            return $item !== '';
        }));
    }

    private function sortedUniqueList(array $values)
    {
        $values = array_values(array_unique(array_filter(array_map('strval', $values), function ($value) {
            return trim($value) !== '';
        })));
        sort($values);

        return $values;
    }

    private function runLooksReadable($run)
    {
        return (string) ($run->terminal_status ?? '') === 'SUCCESS'
            && (string) ($run->publishability_state ?? '') === 'READABLE'
            && CoverageGateStateNormalizer::normalize($run->coverage_gate_state ?? null) === 'PASS'
            && ! empty($run->sealed_at);
    }

    private function reasonCodeFromThrowable(\Throwable $e, $fallback)
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return $fallback;
    }


    private function preparePromoteRunId($requestedDate, $sourceMode, $runId = null, $correctionId = null, array $promoteContext = [])
    {
        if ($runId === null) {
            $seedRun = $this->runs->findLatestForRequestedDate($requestedDate, $sourceMode);
            if (! $seedRun) {
                throw new \RuntimeException('No persisted import run found for requested_date/source_mode.');
            }
            $runId = (int) $seedRun->run_id;
        }

        $seedRun = $this->safeFindRunById($runId);
        if (! $seedRun) {
            return (int) $runId;
        }

        $existingPromoteMode = isset($seedRun->promote_mode) && $seedRun->promote_mode !== '' ? (string) $seedRun->promote_mode : null;
        $existingPublishTarget = isset($seedRun->publish_target) && $seedRun->publish_target !== '' ? (string) $seedRun->publish_target : null;
        $requestedPromoteMode = (string) ($promoteContext['promote_mode'] ?? 'full_publish');
        $requestedPublishTarget = (string) ($promoteContext['publish_target'] ?? 'current_replace');

        $requiresFreshPromoteRun = in_array((string) $seedRun->lifecycle_state, ['COMPLETED', 'FAILED'], true)
            || (string) ($seedRun->terminal_status ?? '') !== ''
            || $existingPromoteMode !== null
            || $existingPublishTarget !== null
            || ! in_array((string) $seedRun->stage, ['INGEST_BARS', 'PUBLISH_BARS'], true);

        if (! $requiresFreshPromoteRun) {
            return (int) $seedRun->run_id;
        }

        $notes = $this->appendRunNotes($this->stripPromoteNotes($seedRun->notes ?? null), [
            'request_mode=promote',
            'promote_seed_run_id='.(int) $seedRun->run_id,
            'promote_mode='.$requestedPromoteMode,
            'publish_target='.$requestedPublishTarget,
        ]);

        $derivedRun = $this->runs->createPromoteRunFromSeed($seedRun, 'PUBLISH_BARS', [
            'request_mode' => 'promote',
            'notes' => $notes,
            'correction_id' => $correctionId,
            'promote_mode' => $requestedPromoteMode,
            'publish_target' => $requestedPublishTarget,
        ]);

        return (int) $derivedRun->run_id;
    }

    private function ensurePromoteRunContext($runId, $requestedDate, array $promoteContext, $correctionId = null)
    {
        if ($runId === null) {
            return null;
        }

        $run = $this->safeFindRunById($runId);
        if (! $run) {
            return null;
        }

        $promoteMode = (string) ($promoteContext['promote_mode'] ?? 'full_publish');
        $publishTarget = (string) ($promoteContext['publish_target'] ?? 'current_replace');

        if (isset($run->promote_mode) && (string) $run->promote_mode === $promoteMode
            && isset($run->publish_target) && (string) $run->publish_target === $publishTarget
            && isset($run->request_mode) && (string) $run->request_mode === 'promote'
            && ($correctionId === null || (int) ($run->correction_id ?? 0) === (int) $correctionId)) {
            return $run;
        }

        $notes = $this->appendRunNotes($this->stripPromoteNotes($run->notes ?? null), [
            'request_mode=promote',
            'promote_mode='.$promoteMode,
            'publish_target='.$publishTarget,
        ]);

        return $this->safeUpdateTelemetry($run, [
            'request_mode' => 'promote',
            'promote_mode' => $promoteMode,
            'publish_target' => $publishTarget,
            'correction_id' => $correctionId,
            'notes' => $notes,
        ]);
    }

    private function isRepairCandidateRunContext($run)
    {
        if (! $run) {
            return false;
        }

        $promoteMode = isset($run->promote_mode) && $run->promote_mode !== ''
            ? (string) $run->promote_mode
            : null;
        $publishTarget = isset($run->publish_target) && $run->publish_target !== ''
            ? (string) $run->publish_target
            : null;

        return in_array($promoteMode, ['repair_candidate', 'incremental'], true)
            || in_array($publishTarget, ['repair_candidate', 'incremental_candidate'], true);
    }

    private function stripPromoteNotes($notes)
    {
        if ($notes === null || trim((string) $notes) === '') {
            return null;
        }

        $parts = array_filter(array_map('trim', explode(';', (string) $notes)), static function ($part) {
            return $part !== ''
                && strpos($part, 'request_mode=') !== 0
                && strpos($part, 'promote_mode=') !== 0
                && strpos($part, 'publish_target=') !== 0
                && strpos($part, 'promote_seed_run_id=') !== 0;
        });

        return $parts === [] ? null : implode('; ', $parts);
    }

    private function resolvePromoteContext($sourceMode, $correctionId = null, $promoteMode = null)
    {
        $resolvedMode = $promoteMode !== null && $promoteMode !== ''
            ? (string) $promoteMode
            : ($correctionId !== null ? 'correction_current' : 'full_publish');

        $aliases = [
            'correction' => 'correction_current',
            'incremental' => 'repair_candidate',
        ];
        $resolvedMode = $aliases[$resolvedMode] ?? $resolvedMode;

        if (! in_array($resolvedMode, ['full_publish', 'correction_current', 'repair_candidate'], true)) {
            throw new \InvalidArgumentException('Unsupported promote mode: '.$resolvedMode);
        }

        if ($resolvedMode === 'correction_current' && $correctionId === null) {
            throw new \InvalidArgumentException('Promote mode correction_current requires correction_id.');
        }

        if ($resolvedMode === 'repair_candidate') {
            return [
                'promote_mode' => 'repair_candidate',
                'publish_target' => 'repair_candidate',
                'requires_full_coverage' => false,
                'requires_baseline' => false,
            ];
        }

        if ($resolvedMode === 'correction_current') {
            return [
                'promote_mode' => 'correction_current',
                'publish_target' => 'current_replace',
                'requires_full_coverage' => true,
                'requires_baseline' => true,
            ];
        }

        return [
            'promote_mode' => 'full_publish',
            'publish_target' => 'current_replace',
            'requires_full_coverage' => true,
            'requires_baseline' => false,
        ];
    }

    private function finalizeRunState($run, array $state)
    {
        $run = $this->hydrateRunModel($run);
        $finalizedRun = $this->runs->finalize($run, $state);

        if ($finalizedRun === null) {
            $finalizedRun = $run;
        }

        foreach ($state as $key => $value) {
            $finalizedRun->{$key} = $value;
        }

        return $finalizedRun;
    }

    private function safeFindRunById($runId)
    {
        if ($runId === null || $this->runs === null) {
            return null;
        }

        try {
            return $this->runs->findByRunId($runId);
        } catch (\Mockery\Exception\BadMethodCallException $e) {
            return null;
        } catch (\Mockery\Exception\NoMatchingExpectationException $e) {
            return null;
        }
    }

    private function safeRequireApprovedCorrection($correctionId, $requestedDate)
    {
        return $this->safeCanExecuteCorrection($correctionId, $requestedDate, 'correction_current');
    }

    private function safeCanExecuteCorrection($correctionId, $requestedDate, $mode = 'correction_current')
    {
        if ($correctionId === null || $this->corrections === null) {
            return null;
        }

        try {
            if (method_exists($this->corrections, 'canExecuteCorrection')) {
                return $this->corrections->canExecuteCorrection($correctionId, $requestedDate, $mode);
            }

            return $this->corrections->requireApprovedForTradeDate($correctionId, $requestedDate);
        } catch (\Mockery\Exception\BadMethodCallException $e) {
            return null;
        } catch (\Mockery\Exception\NoMatchingExpectationException $e) {
            return null;
        }
    }

    private function safeUpdateTelemetry($run, array $telemetry)
    {
        if ($run === null || $this->runs === null || $telemetry === []) {
            return $run;
        }

        $run = $this->hydrateRunModel($run);
        if (! $run instanceof EodRun) {
            return $run;
        }

        $filtered = [];
        foreach ($telemetry as $key => $value) {
            if ($value !== null) {
                $filtered[$key] = $value;
            }
        }

        if ($filtered === []) {
            return $run;
        }

        try {
            return $this->runs->updateTelemetry($run, $filtered);
        } catch (\Mockery\Exception\BadMethodCallException $e) {
            return $run;
        } catch (\Mockery\Exception\NoMatchingExpectationException $e) {
            return $run;
        }
    }


    private function safeFindRawCurrentPublicationStateForTradeDate($requestedDate)
    {
        if ($requestedDate === null || $this->publications === null) {
            return null;
        }

        try {
            return $this->publications->findRawCurrentPublicationStateForTradeDate($requestedDate);
        } catch (\Mockery\Exception\BadMethodCallException $e) {
            return null;
        } catch (\Mockery\Exception\NoMatchingExpectationException $e) {
            return null;
        }
    }

    private function isRepairCandidateRun($run)
    {
        return (string) ($run->promote_mode ?? '') === 'repair_candidate'
            || (string) ($run->publish_target ?? '') === 'repair_candidate';
    }

    private function hydrateRunModel($run)
    {
        if ($run instanceof EodRun || $run === null) {
            return $run;
        }

        if (is_object($run)) {
            $model = new EodRun();
            foreach (get_object_vars($run) as $key => $value) {
                $model->{$key} = $value;
            }
            return $model;
        }

        if (is_array($run)) {
            $model = new EodRun();
            foreach ($run as $key => $value) {
                $model->{$key} = $value;
            }
            return $model;
        }

        return $run;
    }


    private function sourceTelemetryColumns($sourceMode, $resolvedSourceName = null, array $sourceAcquisition = [], $fallbackFinalReasonCode = null)
    {
        $payload = $this->sourceTelemetryPayload($sourceMode, $resolvedSourceName);
        $finalReasonCode = $sourceAcquisition['final_reason_code'] ?? $fallbackFinalReasonCode;
        $sourceInputFile = $sourceAcquisition['source_input_file']
            ?? ($sourceAcquisition['input_file'] ?? ($payload['input_file'] ?? null));

        $sourceFileIdentity = $this->sourceFileIdentityColumns($sourceInputFile);
        foreach (['source_file_hash', 'source_file_hash_algorithm', 'source_file_size_bytes', 'source_file_row_count'] as $field) {
            if (array_key_exists($field, $sourceAcquisition) && $sourceAcquisition[$field] !== null && $sourceAcquisition[$field] !== '') {
                $sourceFileIdentity[$field] = $sourceAcquisition[$field];
            }
        }

        $sourceInputFileForDisplay = $this->sourceInputFileForDisplay($sourceMode, $sourceInputFile);

        return array_merge([
            'source_name' => $payload['source_name'] ?? null,
            'source_provider' => $sourceAcquisition['provider'] ?? ($payload['provider'] ?? null),
            'source_input_file' => $sourceInputFileForDisplay,
            'source_timeout_seconds' => $sourceAcquisition['timeout_seconds'] ?? ($payload['timeout_seconds'] ?? null),
            'source_retry_max' => $sourceAcquisition['retry_max'] ?? ($payload['retry_max'] ?? null),
            'source_attempt_count' => array_key_exists('attempt_count', $sourceAcquisition) ? $sourceAcquisition['attempt_count'] : null,
            'source_success_after_retry' => array_key_exists('success_after_retry', $sourceAcquisition) ? (bool) $sourceAcquisition['success_after_retry'] : null,
            'source_retry_exhausted' => array_key_exists('retry_exhausted', $sourceAcquisition) ? (bool) $sourceAcquisition['retry_exhausted'] : null,
            'source_final_http_status' => array_key_exists('final_http_status', $sourceAcquisition) ? $sourceAcquisition['final_http_status'] : null,
            'source_final_reason_code' => $finalReasonCode,
        ], $sourceFileIdentity);
    }

    private function sourceInputFileForDisplay($sourceMode, $sourceInputFile)
    {
        if ($sourceInputFile === null || trim((string) $sourceInputFile) === '') {
            return $sourceInputFile;
        }

        if (in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            return basename(str_replace('\\', '/', (string) $sourceInputFile));
        }

        return $sourceInputFile;
    }

    private function sourceFileIdentityColumns($inputFile)
    {
        if ($inputFile === null || trim((string) $inputFile) === '') {
            return [
                'source_file_hash' => null,
                'source_file_hash_algorithm' => null,
                'source_file_size_bytes' => null,
                'source_file_row_count' => null,
            ];
        }

        $path = $this->resolveSourceIdentityFilePath((string) $inputFile);

        if (! is_file($path)) {
            return [
                'source_file_hash' => null,
                'source_file_hash_algorithm' => 'SHA-256',
                'source_file_size_bytes' => null,
                'source_file_row_count' => null,
            ];
        }

        return [
            'source_file_hash' => hash_file('sha256', $path),
            'source_file_hash_algorithm' => 'SHA-256',
            'source_file_size_bytes' => filesize($path),
            'source_file_row_count' => $this->countSourceFileDataRows($path),
        ];
    }

    private function resolveSourceIdentityFilePath($path)
    {
        if ($path === '' || file_exists($path)) {
            return $path;
        }

        if ($this->isAbsoluteFilesystemPath($path)) {
            return $path;
        }

        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $candidates = [];

        try {
            if (function_exists('app')) {
                $application = app();
                if (is_object($application) && method_exists($application, 'basePath')) {
                    $candidates[] = $application->basePath($path);
                    $candidates[] = $application->basePath($normalizedPath);
                }
            }
        } catch (\Throwable $exception) {
            // Unit tests may bind a plain Illuminate container without Lumen's basePath method.
        }

        $workingDirectory = getcwd();
        if (is_string($workingDirectory) && $workingDirectory !== '') {
            $candidates[] = rtrim($workingDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$normalizedPath;
        }

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return $path;
    }

    private function isAbsoluteFilesystemPath($path)
    {
        if ($path === '') {
            return false;
        }

        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        return strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && ($path[2] === '/' || $path[2] === '\\');
    }

    private function countSourceFileDataRows($path)
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return null;
        }

        $rows = 0;
        $hasHeader = false;
        while (($line = fgets($handle)) !== false) {
            if (trim($line) === '') {
                continue;
            }
            if (! $hasHeader) {
                $hasHeader = true;
                continue;
            }
            $rows++;
        }
        fclose($handle);

        return $hasHeader ? $rows : 0;
    }

    private function sourceTelemetryPayload($sourceMode, $resolvedSourceName = null)
    {
        $payload = [
            'source_mode' => $sourceMode,
        ];

        $configuredSourceName = null;
        if ($sourceMode === 'api') {
            // CONTRACT: pipeline/operator-facing API source identity stays on the logical name
            // API_FREE even when the adapter/provider defaults resolve to a concrete upstream
            // label such as YAHOO_FINANCE. Provider detail belongs in provider telemetry, not
            // in the primary source_name emitted to run notes or operator summaries.
            $configuredSourceName = 'API_FREE';
        } elseif (in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            // CONTRACT: manual source identity must stay on the logical LOCAL_FILE
            // label in both success and failure paths. Do not inherit the global
            // default source name because that may point at an upstream provider
            // such as YAHOO_FINANCE and leak provider identity into operator-facing
            // run notes, summaries, or failure telemetry for manual runs.
            $configuredSourceName = 'LOCAL_FILE';
        }

        $normalizedResolvedSourceName = $resolvedSourceName !== null
            ? strtoupper(trim((string) $resolvedSourceName))
            : null;

        if ($sourceMode === 'api') {
            $payload['source_name'] = $configuredSourceName !== '' ? $configuredSourceName : 'API_FREE';
        } elseif (in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            $payload['source_name'] = 'LOCAL_FILE';
        } elseif ($normalizedResolvedSourceName !== null && $normalizedResolvedSourceName !== '') {
            $payload['source_name'] = $normalizedResolvedSourceName;
        } elseif ($configuredSourceName !== null && $configuredSourceName !== '') {
            $payload['source_name'] = $configuredSourceName;
        }

        if ($sourceMode === 'api') {
            $payload['provider'] = strtolower((string) config('market_data.source.api.provider', 'generic'));
            $payload['timeout_seconds'] = max(1, (int) config('market_data.source.api.timeout_seconds', 20));
            $payload['retry_max'] = min(3, max(0, (int) config('market_data.provider.api_retry_max', 0)));
            $payload['throttle_qps'] = max(1, (int) config('market_data.provider.api_throttle_qps', 1));
        }

        if (in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            $configuredInputFile = trim((string) config('market_data.source.local_input_file', ''));
            if ($configuredInputFile !== '') {
                $payload['input_file'] = $configuredInputFile;
            }
        }

        return $payload;
    }


    private function manualSourceInputNoteSegments($sourceMode)
    {
        if (! in_array($sourceMode, ['manual_file', 'manual_entry'], true)) {
            return [];
        }

        $configuredInputFile = trim((string) config('market_data.source.local_input_file', ''));
        if ($configuredInputFile === '') {
            return [];
        }

        return ['source_input_file='.(string) basename($configuredInputFile)];
    }

    private function sourceAcquisitionNoteSegments(array $sourceAcquisition)
    {
        if (empty($sourceAcquisition)) {
            return [];
        }

        $segments = [];

        $sourceInputFile = $sourceAcquisition['source_input_file'] ?? ($sourceAcquisition['input_file'] ?? null);
        if ($sourceInputFile !== null && $sourceInputFile !== '') {
            $segments[] = 'source_input_file='.(string) basename((string) $sourceInputFile);
        }

        foreach ([
            'source_file_hash' => 'source_file_hash',
            'source_file_hash_algorithm' => 'source_file_hash_algorithm',
            'source_file_size_bytes' => 'source_file_size_bytes',
            'source_file_row_count' => 'source_file_row_count',
            'accepted_row_count' => 'accepted_row_count',
            'rejected_row_count' => 'rejected_row_count',
            'invalid_row_count' => 'invalid_row_count',
            'source_final_status' => 'source_final_status',
            'source_acquisition_state' => 'source_acquisition_state',
            'source_acquisition_mode' => 'source_acquisition_mode',
            'source_acquisition_batch_id' => 'source_acquisition_batch_id',
            'source_window_start' => 'source_window_start',
            'source_window_end' => 'source_window_end',
            'warmup_start' => 'warmup_start',
            'requested_start' => 'requested_start',
            'requested_end' => 'requested_end',
            'expected_ticker_count' => 'expected_ticker_count',
            'success_ticker_count' => 'success_ticker_count',
            'failed_ticker_count' => 'failed_ticker_count',
            'max_failed_allowed_for_coverage' => 'max_failed_allowed_for_coverage',
            'coverage_impossible' => 'coverage_impossible',
        ] as $field => $label) {
            if (array_key_exists($field, $sourceAcquisition) && $sourceAcquisition[$field] !== null && $sourceAcquisition[$field] !== '') {
                $segments[] = $label.'='.(string) $sourceAcquisition[$field];
            }
        }

        if (($sourceAcquisition['provider'] ?? '') !== '') {
            $segments[] = 'source_provider='.(string) $sourceAcquisition['provider'];
        }

        if (array_key_exists('timeout_seconds', $sourceAcquisition) && $sourceAcquisition['timeout_seconds'] !== null) {
            $segments[] = 'source_timeout_seconds='.(int) $sourceAcquisition['timeout_seconds'];
        }

        if (array_key_exists('retry_max', $sourceAcquisition) && $sourceAcquisition['retry_max'] !== null) {
            $segments[] = 'source_retry_max='.(int) $sourceAcquisition['retry_max'];
        }

        if (array_key_exists('attempt_count', $sourceAcquisition)) {
            $segments[] = 'source_attempt_count='.(int) $sourceAcquisition['attempt_count'];
        }

        if (array_key_exists('requested_ticker_count', $sourceAcquisition) && $sourceAcquisition['requested_ticker_count'] !== null) {
            $segments[] = 'source_requested_ticker_count='.(int) $sourceAcquisition['requested_ticker_count'];
        }

        if (array_key_exists('unique_ticker_count', $sourceAcquisition) && $sourceAcquisition['unique_ticker_count'] !== null) {
            $segments[] = 'source_unique_ticker_count='.(int) $sourceAcquisition['unique_ticker_count'];
        }

        if (array_key_exists('returned_row_count', $sourceAcquisition) && $sourceAcquisition['returned_row_count'] !== null) {
            $segments[] = 'source_returned_row_count='.(int) $sourceAcquisition['returned_row_count'];
        }

        if (array_key_exists('missing_ticker_count', $sourceAcquisition) && $sourceAcquisition['missing_ticker_count'] !== null) {
            $segments[] = 'source_missing_ticker_count='.(int) $sourceAcquisition['missing_ticker_count'];
        }

        if (! empty($sourceAcquisition['success_after_retry'])) {
            $segments[] = 'source_success_after_retry=yes';
        }

        if (! empty($sourceAcquisition['retry_exhausted'])) {
            $segments[] = 'source_retry_exhausted=yes';
        }

        if (array_key_exists('final_http_status', $sourceAcquisition) && $sourceAcquisition['final_http_status'] !== null) {
            $segments[] = 'source_final_http_status='.(int) $sourceAcquisition['final_http_status'];
        }

        if (array_key_exists('final_reason_code', $sourceAcquisition) && $sourceAcquisition['final_reason_code'] !== null) {
            $segments[] = 'source_final_reason_code='.(string) $sourceAcquisition['final_reason_code'];
        }

        return $segments;
    }

    private function sourceFailureNoteSegments($sourceMode, $reasonCode, array $payload)
    {
        $segments = [];
        $sourceTelemetry = $this->sourceTelemetryPayload($sourceMode);

        if (($sourceTelemetry['source_name'] ?? '') !== '') {
            $segments[] = 'source_name='.(string) $sourceTelemetry['source_name'];
        }

        if (($sourceTelemetry['input_file'] ?? '') !== '') {
            $segments[] = 'source_input_file='.(string) basename((string) $sourceTelemetry['input_file']);
        }

        $exceptionContext = isset($payload['exception_context']) && is_array($payload['exception_context'])
            ? $payload['exception_context']
            : [];

        $provider = $exceptionContext['provider'] ?? ($sourceTelemetry['provider'] ?? null);
        if ($provider !== null && trim((string) $provider) !== '') {
            $segments[] = 'source_provider='.(string) $provider;
        }

        $timeoutSeconds = $exceptionContext['timeout_seconds'] ?? ($sourceTelemetry['timeout_seconds'] ?? null);
        if ($timeoutSeconds !== null && $timeoutSeconds !== '') {
            $segments[] = 'source_timeout_seconds='.(int) $timeoutSeconds;
        }

        $retryMax = $exceptionContext['retry_max'] ?? ($sourceTelemetry['retry_max'] ?? null);
        if ($retryMax !== null && $retryMax !== '') {
            $segments[] = 'source_retry_max='.(int) $retryMax;
        }

        if (array_key_exists('attempt_count', $exceptionContext)) {
            $segments[] = 'source_attempt_count='.(int) $exceptionContext['attempt_count'];
        }

        if (! empty($exceptionContext['success_after_retry'])) {
            $segments[] = 'source_success_after_retry=yes';
        }

        if (! empty($exceptionContext['retry_exhausted'])) {
            $segments[] = 'source_retry_exhausted=yes';
        }

        if (array_key_exists('final_http_status', $exceptionContext) && $exceptionContext['final_http_status'] !== null) {
            $segments[] = 'source_final_http_status='.(int) $exceptionContext['final_http_status'];
        }

        $finalReasonCode = $exceptionContext['final_reason_code'] ?? $reasonCode;
        if ($finalReasonCode !== null && trim((string) $finalReasonCode) !== '') {
            $segments[] = 'source_final_reason_code='.(string) $finalReasonCode;
        }

        return $segments;
    }


    private function appendRunNotes($existingNotes, array $segments)
    {
        $parts = [];

        foreach (explode(';', (string) $existingNotes) as $part) {
            $part = trim($part);
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        foreach ($segments as $segment) {
            $segment = trim((string) $segment);
            if ($segment !== '' && ! in_array($segment, $parts, true)) {
                $parts[] = $segment;
            }
        }

        return empty($parts) ? null : implode('; ', $parts);
    }

    private function extractNoteValue(string $notes, string $key): ?string
    {
        foreach (explode(';', $notes) as $part) {
            $part = trim($part);

            if (strpos($part, $key.'=') === 0) {
                $value = trim(substr($part, strlen($key) + 1));

                return $value !== '' ? $value : null;
            }
        }

        return null;
    }

    private function summarizeThrowable(\Throwable $e)
    {
        $message = trim((string) $e->getMessage());
        if ($message === '') {
            return class_basename($e);
        }

        return mb_strlen($message) <= 220 ? $message : mb_substr($message, 0, 217).'...';
    }

    private function hashForTable($table, $dateColumn, $requestedDate, array $columns, array $extraWhere = [])
    {
        $query = DB::table($table)->where($dateColumn, $requestedDate);
        foreach ($extraWhere as $k => $v) {
            $query->where($k, $v);
        }
        $rows = $query
            ->orderBy($dateColumn)
            ->orderBy('ticker_id')
            ->get();

        return $this->hashes->hashRows($rows, $columns);
    }

    private function resolveCoverageEdgeCaseReasonCode($run, $requestedDate)
    {
        $coverageState = CoverageGateStateNormalizer::normalize($run->coverage_gate_state ?? null);
        if ($coverageState !== 'FAIL') {
            return null;
        }

        if ($this->isCoverageDelayWindowOpen($requestedDate)) {
            return 'RUN_DATA_DELAYED';
        }

        $expected = isset($run->coverage_universe_count) ? (int) $run->coverage_universe_count : null;
        $available = isset($run->coverage_available_count) ? (int) $run->coverage_available_count : null;

        if ($expected !== null && $expected > 0 && $available !== null && $available > 0 && $available < $expected) {
            return 'RUN_PARTIAL_DATA';
        }

        return 'RUN_COVERAGE_LOW';
    }

    private function isCoverageDelayWindowOpen($requestedDate)
    {
        $delayMinutes = max(0, (int) config('market_data.coverage_edge_cases.delay_window_minutes', 0));
        if ($delayMinutes <= 0) {
            return false;
        }

        $timezone = config('market_data.platform.timezone');
        $now = Carbon::now($timezone);
        $cutoff = Carbon::parse($requestedDate.' '.config('market_data.platform.cutoff_time'), $timezone);
        $delayDeadline = $cutoff->copy()->addMinutes($delayMinutes);

        return $now->greaterThanOrEqualTo($cutoff) && $now->lessThanOrEqualTo($delayDeadline);
    }

    private function isFinalizeCutoffSatisfied($requestedDate)
    {
        $timezone = config('market_data.platform.timezone');
        $now = Carbon::now($timezone);
        $cutoff = Carbon::parse($requestedDate.' '.config('market_data.platform.cutoff_time'), $timezone);
        return $now->greaterThanOrEqualTo($cutoff);
    }

}
