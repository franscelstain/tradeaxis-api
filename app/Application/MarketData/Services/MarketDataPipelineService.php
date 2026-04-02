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

        if ($input->correctionId) {
            $correction = $this->corrections->requireApprovedForTradeDate($input->correctionId, $input->requestedDate);
            $priorCurrent = $this->publications->findCorrectionBaselinePublicationForTradeDate($input->requestedDate);
            if (! $priorCurrent) {
                throw new \RuntimeException('Correction requires an existing current sealed publication baseline resolved from current pointer/current publication for target trade date.');
            }
            $supersedesRunId = $priorCurrent->run_id;
        }

        $run = $input->runId
            ? $this->runs->findByRunId($input->runId)
            : $this->runs->getOrCreateOwningRun($input->requestedDate, $input->sourceMode, $input->stage, $supersedesRunId);

        if (! $run) {
            throw new \RuntimeException('Owning run context not found for market-data stage.');
        }

        if ($correction && $input->stage === 'INGEST_BARS') {
            $this->artifacts->snapshotPublicationFromCurrentTables($input->requestedDate, $priorCurrent->publication_id, $priorCurrent->run_id);
            $correction = $this->corrections->markExecuting($correction->correction_id, $priorCurrent->run_id, $run->run_id);
        }

        $run = $this->runs->touchStage($run, $input->stage, [
            'notes' => $input->correctionId ? 'correction_id='.$input->correctionId : $run->notes,
            'supersedes_run_id' => $supersedesRunId ?: $run->supersedes_run_id,
        ]);

        $this->runs->appendEvent(
            $run,
            $input->stage,
            'STAGE_STARTED',
            'INFO',
            'Stage started in owning run context.',
            null,
            [
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

        return DB::transaction(function () use ($run, $input, $priorCurrent) {
            try {
                $result = $this->barsIngest->ingest($run, $input->requestedDate, $input->sourceMode, $priorCurrent);
            } catch (\Throwable $e) {
                if ($e instanceof SourceAcquisitionException) {
                    $reasonCode = $e->reasonCode();
                } else {
                    $reasonCode = strpos($e->getMessage(), 'current publication') !== false
                        ? 'RUN_LOCK_CONFLICT'
                        : 'RUN_SOURCE_MALFORMED_PAYLOAD';
                }

                $this->handleStageFailure($run, $input->stage, $reasonCode, $e);
                throw $e;
            }

            $run = $this->runs->updateTelemetry($run, [
                'bars_rows_written' => $result['bars_rows_written'],
                'invalid_bar_count' => $result['invalid_bar_count'],
                'publication_version' => $result['publication_version'],
                'notes' => trim(($run->notes ? $run->notes.'; ' : '').'candidate_publication_id='.$result['publication_id']),
            ]);

            $this->runs->appendEvent($run, $input->stage, 'STAGE_COMPLETED', 'INFO', 'Bars ingest stage completed with canonical artifact writes.', null, $result);

            return $run;
        });
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
                    'coverage_universe_basis' => (string) config('market_data.coverage_gate.universe_basis', 'ticker_master_active_on_trade_date'),
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
        [$run, $correction] = $this->startStage($input);

        if (! $run->bars_batch_hash || ! $run->indicators_batch_hash || ! $run->eligibility_batch_hash || empty($run->bars_rows_written) || empty($run->indicators_rows_written) || empty($run->eligibility_rows_written)) {
            $this->runs->appendEvent($run, $input->stage, 'SEAL_BLOCKED', 'ERROR', 'Seal blocked because one or more mandatory hashes are missing.', 'RUN_SEAL_PRECONDITION_FAILED');
            $this->runs->failStage($run, $input->stage, 'RUN_SEAL_PRECONDITION_FAILED', 'Cannot seal dataset before all mandatory hashes exist.');
            throw new \RuntimeException('Cannot seal dataset before all mandatory hashes exist.');
        }

        try {
            return DB::transaction(function () use ($run, $input, $correction) {
            try {
                $publication = $this->publications->sealCandidatePublication($run, 'system', 'Seal recorded after publication preconditions passed.');
                $run = $this->runs->markSealed($run, 'system', 'Seal recorded after publication preconditions passed.');
                $this->artifacts->snapshotPublicationFromCurrentTables($input->requestedDate, $publication->publication_id, $run->run_id);
                if ($correction) {
                    $this->corrections->markResealed($correction->correction_id, $run->run_id);
                }
            } catch (\Throwable $e) {
                $this->runs->appendEvent($run, $input->stage, 'SEAL_FAILED', 'ERROR', $e->getMessage(), 'RUN_SEAL_WRITE_FAILED');
                throw $e;
            }

                $this->runs->appendEvent($run, $input->stage, 'STAGE_COMPLETED', 'INFO', 'Dataset seal metadata recorded on eod_runs and eod_publications.', null, [
                    'sealed_at' => (string) $run->sealed_at,
                    'sealed_by' => $run->sealed_by,
                    'publication_id' => (int) $publication->publication_id,
                    'seal_state' => $publication->seal_state,
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
        [$run, $correction, $priorCurrent] = $this->startStage($input);

        try {
            return DB::transaction(function () use ($run, $input, $correction, $priorCurrent) {
                $fallback = $this->publications->findLatestReadablePublicationBefore($input->requestedDate);
                $cutoffSatisfied = $this->isFinalizeCutoffSatisfied($input->requestedDate);

                $candidatePublication = $this->publications->getOrCreateCandidatePublication(
                    $run,
                    $priorCurrent ? $priorCurrent->publication_id : null
                );

                $candidateCurrent = null;

                $preDecision = $this->finalizeDecisions->evaluate(
                    $cutoffSatisfied,
                    (bool) $run->sealed_at,
                    $candidatePublication->seal_state,
                    $run->coverage_ratio,
                    (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min')),
                    $fallback ? $fallback->readable_trade_date : null
                );

                $unchangedCorrection = false;
                $promotionError = null;
                $postFinalizeMismatchNote = null;

                if ($preDecision['promotion_allowed']) {
                    try {
                        if ($correction && $this->publicationDiffs->isUnchanged($priorCurrent, $candidatePublication)) {
                            $unchangedCorrection = true;

                            $this->publications->discardCandidatePublication(
                                $candidatePublication->publication_id
                            );

                            $candidateCurrent = $priorCurrent;

                            $this->runs->appendEvent(
                                $run,
                                $input->stage,
                                'CORRECTION_CANCELLED',
                                'INFO',
                                'Correction content unchanged; current publication preserved.',
                                null,
                                [
                                    'correction_id' => (int) $correction->correction_id,
                                    'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                                    'candidate_publication_id' => (int) $candidatePublication->publication_id,
                                ]
                            );
                        } else {
                            if ($correction) {
                                $this->artifacts->promotePublicationHistoryToCurrent(
                                    $input->requestedDate,
                                    $candidatePublication->publication_id,
                                    $run->run_id
                                );
                            }

                            $promotedCurrent = $this->publications->promoteCandidateToCurrent(
                                $run,
                                $priorCurrent ? $priorCurrent->publication_id : null
                            );

                            $this->runs->syncCurrentPublicationMirror(
                                $input->requestedDate,
                                $run->run_id
                            );

                            /*
                            * Provisional only.
                            * Jangan pakai strict readable pointer resolver di titik ini.
                            */
                            $candidateCurrent = $promotedCurrent;

                            if (! $candidateCurrent) {
                                throw new \RuntimeException(
                                    'Current publication promotion returned no publication.'
                                );
                            }

                            if ((int) $candidateCurrent->publication_id !== (int) $candidatePublication->publication_id) {
                                throw new \RuntimeException(
                                    'Current publication pointer resolution mismatch after finalize.'
                                );
                            }

                            if (
                                isset($candidateCurrent->publication_version) &&
                                (int) $candidateCurrent->publication_version !== (int) $candidatePublication->publication_version
                            ) {
                                throw new \RuntimeException(
                                    'Current publication version mismatch after finalize.'
                                );
                            }

                            if (
                                isset($candidateCurrent->run_id) &&
                                (int) $candidateCurrent->run_id !== (int) $run->run_id
                            ) {
                                throw new \RuntimeException(
                                    'Current publication run mismatch after finalize.'
                                );
                            }

                            if (
                                isset($candidateCurrent->trade_date) &&
                                (string) $candidateCurrent->trade_date !== (string) $input->requestedDate
                            ) {
                                throw new \RuntimeException(
                                    'Current publication trade date mismatch after finalize.'
                                );
                            }
                        }
                    } catch (\Throwable $e) {
                        if ($correction && $priorCurrent) {
                            $this->publications->restorePriorCurrentPublication(
                                $input->requestedDate,
                                (int) $priorCurrent->publication_id,
                                (int) $priorCurrent->run_id
                            );

                            $this->runs->syncCurrentPublicationMirror(
                                $input->requestedDate,
                                (int) $priorCurrent->run_id
                            );
                        }

                        $promotionError = $e->getMessage();
                        $candidateCurrent = $priorCurrent ?: null;
                    }
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

                $finalState = [
                    'trade_date_effective' => $outcome['trade_date_effective'],
                    'quality_gate_state' => $outcome['quality_gate_state'],
                    'publishability_state' => $outcome['publishability_state'],
                    'terminal_status' => $outcome['terminal_status'],
                    'lifecycle_state' => 'COMPLETED',
                ];

                $run = $this->runs->finalize($run, $finalState);

                /*
                * Strict post-finalize validation hanya setelah run benar-benar finalized.
                * Ini yang menutup family post_switch_resolution_mismatch tanpa membunuh
                * correction publish normal.
                */
                if (
                    $correction
                    && ! $unchangedCorrection
                    && $outcome['terminal_status'] === 'SUCCESS'
                    && $outcome['correction_outcome'] === 'PUBLISHED'
                ) {
                    $resolved = $this->publications->findPointerResolvedPublicationForTradeDate(
                        $input->requestedDate
                    );

                    $strictMismatch = false;

                    if (! $resolved) {
                        $strictMismatch = true;
                    } elseif ((int) $resolved->publication_id !== (int) $candidatePublication->publication_id) {
                        $strictMismatch = true;
                    } elseif (
                        isset($resolved->publication_version) &&
                        (int) $resolved->publication_version !== (int) $candidatePublication->publication_version
                    ) {
                        $strictMismatch = true;
                    }

                    if ($strictMismatch) {
                        $postFinalizeMismatchNote = 'Current publication pointer resolution mismatch after finalize.';

                        if ($priorCurrent) {
                            $this->publications->restorePriorCurrentPublication(
                                $input->requestedDate,
                                (int) $priorCurrent->publication_id,
                                (int) $priorCurrent->run_id
                            );

                            $this->runs->syncCurrentPublicationMirror(
                                $input->requestedDate,
                                (int) $priorCurrent->run_id
                            );
                        }

                        $run = $this->runs->finalize($run, [
                            'trade_date_effective' => $fallback ? $fallback->readable_trade_date : null,
                            'quality_gate_state' => $outcome['quality_gate_state'],
                            'publishability_state' => 'NOT_READABLE',
                            'terminal_status' => 'HELD',
                            'lifecycle_state' => 'COMPLETED',
                        ]);

                        $candidateCurrent = $priorCurrent ?: null;
                        $resolvedPublicationId = $priorCurrent ? (int) $priorCurrent->publication_id : null;
                        $resolvedPublicationVersion = $priorCurrent ? (int) $priorCurrent->publication_version : null;
                    }

                    $candidateCurrent = $resolved;
                }

                $resolvedPublicationId = $outcome['current_publication_id'];
                $resolvedPublicationVersion = $outcome['current_publication_version'];

                if (
                    $resolvedPublicationId &&
                    (! $candidateCurrent || (int) $candidateCurrent->publication_id !== (int) $resolvedPublicationId)
                ) {
                    $candidateCurrent = (object) [
                        'publication_id' => $resolvedPublicationId,
                        'publication_version' => $resolvedPublicationVersion,
                    ];
                }

                if ($correction) {
                    if ($outcome['correction_outcome'] === 'CANCELLED') {
                        $this->corrections->markCancelled(
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

                $manifest = $resolvedPublicationId
                    ? $this->publications->buildManifestByPublicationId($resolvedPublicationId)
                    : null;

                $finalRunMessage = $outcome['message'];

                if ($postFinalizeMismatchNote !== null) {
                    $finalRunMessage = $postFinalizeMismatchNote;
                } elseif ($promotionError) {
                    $finalRunMessage = $promotionError;
                }

                $this->runs->appendEvent(
                    $run,
                    $input->stage,
                    'RUN_FINALIZED',
                    $run->terminal_status === 'SUCCESS' ? 'INFO' : 'WARN',
                    $finalRunMessage,
                    $run->terminal_status === 'SUCCESS' ? $outcome['reason_code'] : 'RUN_LOCK_CONFLICT',
                    [
                        'cutoff_satisfied' => $cutoffSatisfied,
                        'coverage_ratio' => $run->coverage_ratio,
                        'coverage_min' => (float) config('market_data.coverage_gate.min_ratio', config('market_data.platform.coverage_min')),
                        'quality_gate_state' => $run->quality_gate_state,
                        'requested_date' => $input->requestedDate,
                        'trade_date_effective' => $run->trade_date_effective,
                        'current_publication_id' => $resolvedPublicationId,
                        'current_publication_version' => $resolvedPublicationVersion,
                        'fallback_publication_id' => $fallback ? (int) $fallback->publication_id : null,
                        'fallback_trade_date' => $fallback ? $fallback->readable_trade_date : null,
                        'correction_id' => $correction ? (int) $correction->correction_id : null,
                        'prior_publication_id' => $priorCurrent ? (int) $priorCurrent->publication_id : null,
                        'manifest' => $manifest ? (array) $manifest : null,
                        'correction_outcome' => $outcome['correction_outcome'],
                        'correction_outcome_note' => $outcome['correction_outcome_note'],
                    ]
                );

                return $run;
            });
        } catch (\Throwable $e) {
            $this->handleStageFailure($run, $input->stage, 'RUN_FINALIZE_FAILED', $e);
            throw $e;
        }
    }

    public function runDaily($requestedDate, $sourceMode = null, $correctionId = null)
    {
        $sourceMode = $sourceMode ?: config('market_data.pipeline.default_source_mode');
        $sequence = [
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
        }

        return $run;
    }


    private function handleStageFailure($run, $stage, $reasonCode, \Throwable $e)
    {
        $payload = [
            'exception_class' => get_class($e),
            'exception_message' => $e->getMessage(),
        ];

        if ($e instanceof \PDOException && $e->getCode()) {
            $payload['sqlstate'] = (string) $e->getCode();
        }

        if (method_exists($e, 'getTraceAsString')) {
            $payload['trace'] = mb_substr($e->getTraceAsString(), 0, 4000);
        }

        $this->runs->failStage($run, $stage, $reasonCode, $this->summarizeThrowable($e), $payload);
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

    private function isFinalizeCutoffSatisfied($requestedDate)
    {
        $timezone = config('market_data.platform.timezone');
        $now = Carbon::now($timezone);
        $cutoff = Carbon::parse($requestedDate.' '.config('market_data.platform.cutoff_time'), $timezone);
        return $now->greaterThanOrEqualTo($cutoff);
    }

}
