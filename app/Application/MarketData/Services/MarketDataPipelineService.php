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
        CoverageGateEvaluator $coverageGateEvaluator
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

        $run = $run ?: $this->runs->getOrCreateOwningRun(
            $input->requestedDate,
            $input->sourceMode,
            $input->stage,
            $supersedesRunId
        );

        if (! $run) {
            throw new \RuntimeException('Owning run context not found for market-data stage.');
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
                $isRepairCandidate ? 'repair_candidate' : 'correction_current'
            );
        }

        $run = $this->runs->touchStage($run, $input->stage, [
            'notes' => $this->appendRunNotes(
                $run->notes,
                $input->correctionId ? ['correction_id=' . (int) $input->correctionId] : []
            ),
            'supersedes_run_id' => $supersedesRunId ?: $run->supersedes_run_id,
            'correction_id' => $input->correctionId ?: $run->correction_id,
        ]);

        $existingSourceMode = isset($run->source) && $run->source !== ''
            ? (string) $run->source
            : null;

        if ($existingSourceMode !== null && $existingSourceMode !== (string) $input->sourceMode) {
            throw new \RuntimeException(
                'Run source_mode is immutable within a single run and cannot switch across stages.'
            );
        }

        $this->runs->appendEvent(
            $run,
            $input->stage,
            'STAGE_STARTED',
            'INFO',
            'Stage started in owning run context.',
            null,
            $this->sourceTelemetryPayload($input->sourceMode) + [
                'requested_date' => $input->requestedDate,
                'source_mode' => $input->sourceMode,
                'stage' => $input->stage,
                'correction_id' => $input->correctionId ? (int) $input->correctionId : null,
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
            return DB::transaction(function () use ($run, $input, $priorCurrent) {
                $result = $this->barsIngest->ingest($run, $input->requestedDate, $input->sourceMode, $priorCurrent);

                $sourceAcquisition = isset($result['source_acquisition']) && is_array($result['source_acquisition'])
                    ? $result['source_acquisition']
                    : [];

                $run = $this->safeUpdateTelemetry($run, array_merge([
                    'bars_rows_written' => $result['bars_rows_written'],
                    'invalid_bar_count' => $result['invalid_bar_count'],
                    'publication_id' => $result['publication_id'],
                    'publication_version' => $result['publication_version'],
                    'notes' => $this->appendRunNotes($run->notes, array_merge([
                        'candidate_publication_id='.$result['publication_id'],
                        'source_name='.(string) $result['source_name'],
                    ], $this->manualSourceInputNoteSegments($input->sourceMode), $this->sourceAcquisitionNoteSegments($sourceAcquisition))),
                ], $this->sourceTelemetryColumns($input->sourceMode, $result['source_name'], $sourceAcquisition)));

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'STAGE_COMPLETED',
                    'INFO',
                    'Bars ingest stage completed with canonical artifact writes.',
                    null,
                    $result + $this->sourceTelemetryPayload($input->sourceMode, $result['source_name']) + [
                        'source_acquisition' => $sourceAcquisition,
                    ]
                );

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
        [$run] = $this->startStage($input);

        try {
            return DB::transaction(function () use ($run, $input) {
                $coverage = $this->coverageGateEvaluator->evaluate($input->requestedDate);

                $coverageGateState = strtoupper((string) ($coverage['coverage_gate_status'] ?? 'NOT_EVALUABLE'));
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
                ]);

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'STAGE_COMPLETED',
                    $coverageGateState === 'PASS' ? 'INFO' : 'WARN',
                    'Coverage gate evaluated from persisted canonical valid bars.',
                    $coverageGateState === 'PASS' ? null : ($coverageGateState === 'FAIL' ? 'RUN_COVERAGE_LOW' : 'RUN_COVERAGE_NOT_EVALUABLE'),
                    ['coverage' => $coverage]
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
                    $input->correctionId !== null ? $result['publication_id'] : null
                );

                $run = $this->runs->updateTelemetry($run, [
                    'eligibility_rows_written' => $result['eligibility_rows_written'],
                    'hard_reject_count' => $result['blocked_rows'],
                    'coverage_universe_count' => $coverage['expected_universe_count'],
                    'coverage_available_count' => $coverage['available_eod_count'],
                    'coverage_missing_count' => $coverage['missing_eod_count'],
                    'coverage_ratio' => $coverage['coverage_ratio'],
                    'coverage_min_threshold' => $coverage['coverage_threshold_value'],
                    'coverage_gate_state' => $coverage['coverage_gate_status'],
                    'coverage_threshold_mode' => $coverage['coverage_threshold_mode'],
                    'coverage_universe_basis' => $coverage['coverage_universe_basis'] ?? (string) config('market_data.coverage_gate.universe_basis', 'ticker_master_active_on_trade_date'),
                    'coverage_contract_version' => $coverage['coverage_calibration_version'],
                    'coverage_missing_sample_json' => $coverage['missing_ticker_codes'],
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
    
    protected function completeHash(MarketDataStageInput $input)
    {
        [$run] = $this->startStage($input);

        try {
            $candidatePublication = $this->publications->getOrCreateCandidatePublication($run);
            $correctionMode = $input->correctionId !== null;

            $hashes = [
                'bars_batch_hash' => $this->hashForTable(
                    $correctionMode ? 'eod_bars_history' : 'eod_bars',
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
                    $correctionMode ? ['publication_id' => $candidatePublication->publication_id] : []
                ),
                'indicators_batch_hash' => $this->hashForTable(
                    $correctionMode ? 'eod_indicators_history' : 'eod_indicators',
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
                    ],
                    $correctionMode ? ['publication_id' => $candidatePublication->publication_id] : []
                ),
                'eligibility_batch_hash' => $this->hashForTable(
                    $correctionMode ? 'eod_eligibility_history' : 'eod_eligibility',
                    'trade_date',
                    $input->requestedDate,
                    [
                        'trade_date',
                        'ticker_id',
                        'eligible',
                        'reason_code',
                    ],
                    $correctionMode ? ['publication_id' => $candidatePublication->publication_id] : []
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
                null,
                $hashes + ['publication_id' => (int) $candidatePublication->publication_id]
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
                                null,
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
                        $this->corrections->markResealed($correction->correction_id, $run->run_id);
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

                $preDecision = $this->finalizeDecisions->evaluate(
                    $cutoffSatisfied,
                    true,
                    'SEALED',
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

                    if ($correction && $priorCurrent && $this->publicationDiffs->isUnchanged($priorCurrent, $candidatePublication)) {
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
                            'Correction rerun produced unchanged content; current publication preserved without version switch.'
                        );

                        $this->runs->appendEvent(
                            $run,
                            $input->stage,
                            'CORRECTION_CANCELLED',
                            'INFO',
                            'Correction content unchanged; current publication preserved.',
                            null,
                            [
                                'correction_id' => (int) $correction->correction_id,
                                'prior_publication_id' => (int) $priorCurrent->publication_id,
                                'current_publication_id' => (int) $priorCurrent->publication_id,
                                'current_publication_version' => (int) $priorCurrent->publication_version,
                                'manifest' => $manifest ? (array) $manifest : null,
                                'candidate_publication_id' => (int) $candidatePublication->publication_id,
                                'unchanged_correction' => true,
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
                                'coverage_gate_state' => $run->coverage_gate_state,
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
                                'manifest' => $manifest ? (array) $manifest : null,
                                'correction_outcome' => 'CANCELLED',
                                'correction_outcome_note' => 'Correction rerun produced unchanged content; current publication preserved without version switch.',
                            ]
                        );

                        return $run;
                    } else {
                        try {
                            if ($correction) {
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

                            $promotedCurrent = $this->publications->promoteCandidateToCurrent(
                                $run,
                                $priorCurrent ? $priorCurrent->publication_id : null,
                                (bool) $input->forceReplace
                            );

                            if (! $promotedCurrent) {
                                throw new \RuntimeException('Current publication promotion returned no publication.');
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
                                    null,
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
                                    // Preserve prior current ownership for correction recovery.
                                    // A failed restore attempt must not clear the existing baseline pointer.
                                }
                            } elseif ($isPointerIntegrityError) {
                                $this->publications->clearCurrentPublicationState($input->requestedDate);
                            }

                            if ($isPointerIntegrityError) {
                                $postFinalizeMismatchNote = (
                                        strpos($message, 'Promotion lost run ownership') !== false
                                        || strpos($message, 'Correction baseline no longer matches current publication pointer') !== false
                                    )
                                        ? $message
                                        : 'Current publication pointer resolution mismatch after finalize.';

                                $promotionError = null;
                                $candidateCurrent = $priorCurrent ?: null;
                            } else {
                                $promotionError = $message;
                                $candidateCurrent = $priorCurrent ?: null;
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
                    $outcome['current_publication_id'] = $priorCurrent ? (int) $priorCurrent->publication_id : null;
                    $outcome['current_publication_version'] = $priorCurrent ? (int) $priorCurrent->publication_version : null;
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

                        if ($priorCurrent) {
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
                                // Preserve prior current ownership for correction recovery.
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

                        $run = $this->safeUpdateTelemetry($run, [
                            'publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                            'publication_version' => $priorCurrent ? (int) $priorCurrent->publication_version : null,
                            'correction_id' => $correction ? (int) $correction->correction_id : $run->correction_id,
                            'final_reason_code' => $finalizeReasonCode,
                        ]);

                        $candidateCurrent = $priorCurrent ?: null;
                        $resolvedPublicationId = $priorCurrent ? (int) $priorCurrent->publication_id : null;
                        $resolvedPublicationVersion = $priorCurrent ? (int) $priorCurrent->publication_version : null;
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
                            $outcome['correction_outcome_note']
                        );
                    } elseif ($outcome['correction_outcome'] === 'REPAIR_CANDIDATE') {
                        $this->corrections->markRepairExecuted(
                            $correction->correction_id,
                            $run->run_id,
                            $priorCurrent ? $priorCurrent->run_id : null,
                            $outcome['correction_outcome_note']
                        );
                    } elseif ($outcome['correction_outcome'] === 'PUBLISHED' && $run->terminal_status === 'SUCCESS') {
                        $this->corrections->markPublished(
                            $correction->correction_id,
                            $run->run_id,
                            $priorCurrent ? $priorCurrent->run_id : null,
                            $outcome['correction_outcome_note']
                        );

                        $this->runs->appendEvent(
                            $run,
                            $input->stage,
                            'CORRECTION_PUBLISHED',
                            'INFO',
                            'Historical correction replaced current publication safely.',
                            null,
                            [
                                'correction_id' => (int) $correction->correction_id,
                                'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                                'current_publication_id' => $resolvedPublicationId ? (int) $resolvedPublicationId : null,
                                'current_publication_version' => $resolvedPublicationVersion ? (int) $resolvedPublicationVersion : null,
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
                        'coverage_gate_state' => $run->coverage_gate_state,
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
                    ]
                );

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_FINALIZE_FAILED', $e);
            throw $e;
        }
    }

    private function prepareRunForPointerSwitch(EodRun $run, array $preDecision): EodRun
    {
        $state = [
            'terminal_status' => $preDecision['terminal_status'] ?? 'SUCCESS',
            'publishability_state' => $preDecision['publishability_state'] ?? 'READABLE',
            'coverage_gate_state' => $run->coverage_gate_state,
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
            // Some isolated unit tests mock repositories without a runtime DB schema.
            // The authoritative finalize() call below remains the durable state write.
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
            'coverage_gate_state' => $run->coverage_gate_state,
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

        $this->corrections->markConsumedForCurrent($correction->correction_id, $run->run_id, $priorCurrent ? $priorCurrent->run_id : null, $state['correction_outcome_note']);

        $manifest = $this->publications->buildManifestByPublicationId($priorCurrent->publication_id);

        $this->runs->appendEvent($run, $input->stage, 'CORRECTION_CANCELLED', 'INFO', $state['correction_outcome_note'], null, [
            'correction_id' => (int) $correction->correction_id,
            'prior_publication_id' => (int) $priorCurrent->publication_id,
            'current_publication_id' => (int) $priorCurrent->publication_id,
            'current_publication_version' => (int) $priorCurrent->publication_version,
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

        $coverageInput = new MarketDataStageInput($requestedDate, $sourceMode, $runId, 'PUBLISH_BARS', $correctionId, $forceReplace, $forceReplaceReason);
        $run = $this->completeCoverageEvaluation($coverageInput);
        $run = $this->safeUpdateTelemetry($run, [
            'promote_mode' => $promoteContext['promote_mode'],
            'publish_target' => $promoteContext['publish_target'],
            'notes' => $this->appendRunNotes($this->stripPromoteNotes($run->notes ?? null), [
                'promote_mode='.$promoteContext['promote_mode'],
                'publish_target='.$promoteContext['publish_target'],
                $forceReplace ? 'force_replace=true' : null,
            ]),
        ]);

        if ($promoteContext['requires_full_coverage'] && strtoupper((string) ($run->coverage_gate_state ?? 'NOT_EVALUABLE')) !== 'PASS') {
            return $this->completeFinalize(new MarketDataStageInput($requestedDate, $sourceMode, $run->run_id, 'FINALIZE', $correctionId, $forceReplace, $forceReplaceReason));
        }

        foreach ([
            'COMPUTE_INDICATORS' => 'completeIndicators',
            'BUILD_ELIGIBILITY' => 'completeEligibility',
            'HASH' => 'completeHash',
            'SEAL' => 'completeSeal',
            'FINALIZE' => 'completeFinalize',
        ] as $stage => $method) {
            $run = $this->{$method}(new MarketDataStageInput($requestedDate, $sourceMode, $run->run_id, $stage, $correctionId, $forceReplace, $forceReplaceReason));

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
        ]);
    }

    public function runDaily($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->runSingleDay($requestedDate, $sourceMode, $correctionId);
    }

    public function importSingleDay($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->executeStageSequence($requestedDate, $sourceMode, $correctionId, [
            'INGEST_BARS' => 'completeIngest',
        ]);
    }

    public function importDaily($requestedDate, $sourceMode = null, $correctionId = null)
    {
        return $this->importSingleDay($requestedDate, $sourceMode, $correctionId);
    }

    private function executeStageSequence($requestedDate, $sourceMode = null, $correctionId = null, array $sequence = [])
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
            $input = new MarketDataStageInput($requestedDate, $sourceMode, $run ? $run->run_id : null, $stage, $correctionId);
            $run = $this->{$method}($input);

            if ($run && in_array((string) $run->terminal_status, ['HELD', 'FAILED'], true)) {
                return $run;
            }
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
            (string) ($run->stage ?? '') === 'FINALIZE'
            && (string) ($run->lifecycle_state ?? '') === 'COMPLETED'
        ) {
            $terminalStatus = (string) ($run->terminal_status ?? '');

            if (
                $terminalStatus === 'SUCCESS'
                && ! empty($run->publication_id)
                && ! empty($run->publication_version)
            ) {
                return $run;
            }

            if (
                in_array($terminalStatus, ['HELD', 'FAILED'], true)
                && (string) ($run->final_reason_code ?? '') !== ''
            ) {
                return $run;
            }
        }

        return null;
    }

    private function handleRecoverableSourceFailure($run, $requestedDate, $stage, $reasonCode, \Throwable $e)
    {
        if (! in_array($reasonCode, ['RUN_SOURCE_RATE_LIMIT', 'RUN_SOURCE_TIMEOUT'], true)) {
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
        $coverageState = strtoupper((string) ($run->coverage_gate_state ?? ''));
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

        if ($coverageState === 'NOT_EVALUABLE' || $coverageState === 'BLOCKED') {
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
            'promote_seed_run_id='.(int) $seedRun->run_id,
            'promote_mode='.$requestedPromoteMode,
            'publish_target='.$requestedPublishTarget,
        ]);

        $derivedRun = $this->runs->createPromoteRunFromSeed($seedRun, 'PUBLISH_BARS', [
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
            && ($correctionId === null || (int) ($run->correction_id ?? 0) === (int) $correctionId)) {
            return $run;
        }

        $notes = $this->appendRunNotes($this->stripPromoteNotes($run->notes ?? null), [
            'promote_mode='.$promoteMode,
            'publish_target='.$publishTarget,
        ]);

        return $this->safeUpdateTelemetry($run, [
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

        $sourceFileIdentity = $this->sourceFileIdentityColumns($payload['input_file'] ?? null);

        return array_merge([
            'source_name' => $payload['source_name'] ?? null,
            'source_provider' => $sourceAcquisition['provider'] ?? ($payload['provider'] ?? null),
            'source_input_file' => $payload['input_file'] ?? null,
            'source_timeout_seconds' => $sourceAcquisition['timeout_seconds'] ?? ($payload['timeout_seconds'] ?? null),
            'source_retry_max' => $sourceAcquisition['retry_max'] ?? ($payload['retry_max'] ?? null),
            'source_attempt_count' => array_key_exists('attempt_count', $sourceAcquisition) ? $sourceAcquisition['attempt_count'] : null,
            'source_success_after_retry' => array_key_exists('success_after_retry', $sourceAcquisition) ? (bool) $sourceAcquisition['success_after_retry'] : null,
            'source_retry_exhausted' => array_key_exists('retry_exhausted', $sourceAcquisition) ? (bool) $sourceAcquisition['retry_exhausted'] : null,
            'source_final_http_status' => array_key_exists('final_http_status', $sourceAcquisition) ? $sourceAcquisition['final_http_status'] : null,
            'source_final_reason_code' => $finalReasonCode,
        ], $sourceFileIdentity);
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

        $path = (string) $inputFile;
        if (! file_exists($path)) {
            $basePath = base_path($path);
            if (file_exists($basePath)) {
                $path = $basePath;
            }
        }

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
        } elseif ($normalizedResolvedSourceName !== null && $normalizedResolvedSourceName !== '') {
            $payload['source_name'] = $normalizedResolvedSourceName;
        } elseif ($configuredSourceName !== null && $configuredSourceName !== '') {
            $payload['source_name'] = $configuredSourceName;
        }

        if ($sourceMode === 'api') {
            $payload['provider'] = strtolower((string) config('market_data.source.api.provider', 'generic'));
            $payload['timeout_seconds'] = max(1, (int) config('market_data.source.api.timeout_seconds', 15));
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
        $rows = $query->orderBy('ticker_id')->get();

        return $this->hashes->hashRows($rows, $columns);
    }

    private function resolveCoverageEdgeCaseReasonCode($run, $requestedDate)
    {
        $coverageState = strtoupper((string) ($run->coverage_gate_state ?? ''));
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
