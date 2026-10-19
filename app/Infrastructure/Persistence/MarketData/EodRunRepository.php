<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\CoverageGateStateNormalizer;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use App\Domain\MarketData\FreshnessState;
use App\Domain\MarketData\MarketDataScope;
use App\Models\EodRun;
use App\Models\EodRunEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodRunRepository
{
    public const REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS = [
        'coverage_expected_count',
        'coverage_bar_not_expected_count',
        'coverage_expectation_unknown_count',
        'coverage_delivered_count',
        'coverage_delivered_valid_count',
    ];

    private $configSnapshots;

    public function __construct(MarketDataConfigSnapshotRepository $configSnapshots = null)
    {
        $this->configSnapshots = $configSnapshots ?: new MarketDataConfigSnapshotRepository();
    }

    public function getOrCreateOwningRun($requestedDate, $sourceMode, $stage, $supersedesRunId = null, $requestMode = null)
    {
        $scope = MarketDataScope::fromConfig();
        $requestedDate = $scope->assertRequestedDate($requestedDate);

        $result = DB::transaction(function () use ($requestedDate, $sourceMode, $stage, $supersedesRunId, $requestMode, $scope) {
            $publishCritical = $this->isPublishCriticalMode($requestMode);
            if ($publishCritical) {
                $this->lockDateOwnerFence($requestedDate);
            }
            $recoveredRunIds = [];
            $recoveryBlock = $this->cancelStaleActiveRuns($requestedDate, $sourceMode, $requestMode, $publishCritical, $recoveredRunIds);
            if ($recoveryBlock !== null) {
                return ['owner_error' => $recoveryBlock];
            }

            $activeRun = EodRun::query()
                ->where('trade_date_requested', $requestedDate)
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
                ->where(function ($query) use ($requestMode, $sourceMode, $publishCritical) {
                    if ($publishCritical) {
                        $query->whereIn('request_mode', $this->publishCriticalModes())
                            ->orWhereNull('request_mode');
                    } else {
                        $query->where('source', $sourceMode)->where('request_mode', $requestMode);
                    }
                })
                ->orderByDesc('run_id')
                ->lockForUpdate()
                ->first();

            if ($activeRun) {
                if ($publishCritical && ((string) $activeRun->source !== (string) $sourceMode
                    || (string) $activeRun->request_mode !== (string) $requestMode)) {
                    $blockedRun = $this->recordFencedWorkerOutcome($requestedDate, $sourceMode, $requestMode, $stage, (int) $activeRun->run_id);
                    $this->appendEvent($activeRun, $stage, 'RUN_OWNER_CONFLICT', 'WARN',
                        'Competing publish-critical request fenced before candidate execution.', 'RUN_LOCK_CONFLICT', [
                            'owner_run_id' => (int) $activeRun->run_id,
                            'blocked_run_id' => (int) $blockedRun->run_id,
                            'competing_source_mode' => (string) $sourceMode,
                            'competing_request_mode' => (string) $requestMode,
                            'trade_date_requested' => $requestedDate,
                            'decision' => 'BLOCKED',
                        ]);
                    return ['owner_error' => 'RUN_OWNER_CONFLICT: active owner run '.(int) $activeRun->run_id.' holds requested date '.$requestedDate.'; blocked run '.(int) $blockedRun->run_id];
                }
                $activeRun->assertKnowledgeCutoffForExecution();

                if (empty($activeRun->config_snapshot_id)) {
                    throw new \RuntimeException('INPUT_CAPTURE_CONFIG_MISSING: an existing run cannot acquire a historical configuration identity from current state.');
                }

                $boundSnapshot = $this->configSnapshots->find($activeRun->config_snapshot_id);
                (new RunInputCaptureRepository())->captureRunConfiguration($activeRun, $boundSnapshot ?: []);
                if ($publishCritical) {
                    $this->setDateOwnerFence($requestedDate, (int) $activeRun->run_id);
                }
                return $activeRun;
            }

            $snapshot = $this->configSnapshots->resolveForRun($requestedDate);
            $now = $this->captureKnowledgeCutoff();

            $run = EodRun::query()->create([
                'trade_date_requested' => $requestedDate,
                'trade_date_effective' => null,
                'lifecycle_state' => 'PENDING',
                'terminal_status' => null,
                'quality_gate_state' => 'PENDING',
                'publishability_state' => 'NOT_READABLE',
                'stage' => $stage,
                'source' => $sourceMode,
                'request_mode' => $requestMode,
                'source_name' => null,
                'source_provider' => null,
                'source_input_file' => null,
                'source_timeout_seconds' => null,
                'source_retry_max' => null,
                'source_attempt_count' => null,
                'source_success_after_retry' => null,
                'source_retry_exhausted' => null,
                'source_final_http_status' => null,
                'source_final_reason_code' => null,
                'source_file_hash' => null,
                'source_file_hash_algorithm' => null,
                'source_file_size_bytes' => null,
                'source_file_row_count' => null,
                'coverage_universe_count' => null,
                'coverage_available_count' => null,
                'coverage_missing_count' => null,
                'coverage_ratio' => null,
                'coverage_min_threshold' => null,
                'coverage_gate_state' => null,
                'coverage_threshold_mode' => null,
                'coverage_universe_basis' => null,
                'knowledge_cutoff_at' => $now,
                'coverage_contract_version' => null,
                'coverage_missing_sample_json' => null,
                'bars_rows_written' => null,
                'indicators_rows_written' => null,
                'eligibility_rows_written' => null,
                'invalid_bar_count' => null,
                'invalid_indicator_count' => null,
                'hard_reject_count' => null,
                'warning_count' => null,
                'notes' => null,
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'artifact_hash_profile' => $this->artifactHashProfile(),
                'publication_semantic_profile' => $this->publicationSemanticProfile(),
                'config_version' => config('market_data.indicators.set_version'),
                'config_hash' => $snapshot['config_hash'],
                'config_snapshot_ref' => $snapshot['snapshot_uid'],
                'config_snapshot_id' => $snapshot['config_snapshot_id'],
                'observation_manifest_hash' => null,
                'operational_start_date' => $scope->operationalStartDate(),
                'freshness_state' => FreshnessState::runLabelFor($scope->operationalStartDate(), $requestedDate),
                'supersedes_run_id' => $supersedesRunId,
                'publication_id' => null,
                'publication_version' => null,
                'is_current_publication' => 0,
                'correction_id' => null,
                'promote_mode' => null,
                'publish_target' => null,
                'final_reason_code' => null,
                'sealed_at' => null,
                'sealed_by' => null,
                'seal_note' => null,
                'started_at' => $now,
                'finished_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->appendEvent(
                $run,
                $stage,
                'RUN_CREATED',
                'INFO',
                'Market-data run created with owning run context.',
                null,
                [
                    'run_id' => (int) $run->run_id,
                    'trade_date_requested' => $requestedDate,
                    'trade_date_effective' => null,
                    'source_mode' => $sourceMode,
                    'request_mode' => $requestMode,
                    'supersedes_run_id' => $supersedesRunId,
                    'lifecycle_state' => 'PENDING',
                    'publishability_state' => 'NOT_READABLE',
                    'config_snapshot_id' => (int) $snapshot['config_snapshot_id'],
                    'config_hash' => $snapshot['config_hash'],
                ]
            );

            (new RunInputCaptureRepository())->captureRunConfiguration($run, $snapshot);
            if ($publishCritical) {
                $this->setDateOwnerFence($requestedDate, (int) $run->run_id);
                if ($recoveredRunIds !== []) {
                    $this->appendEvent($run, $stage, 'RUN_OWNER_TAKEOVER', 'WARN',
                        'New owner acquired requested-date fence after verified stale-owner abandonment.', 'RUN_OWNER_TAKEOVER', [
                            'owner_run_id' => (int) $run->run_id,
                            'previous_owner_run_ids' => $recoveredRunIds,
                            'trade_date_requested' => $requestedDate,
                            'candidate_trust' => 'UNTRUSTWORTHY_ABANDONED',
                            'partial_publication_excluded' => true,
                            'prior_current_resolution' => 'VERIFIED_OR_ABSENT',
                            'decision' => 'TAKEOVER_ALLOWED',
                        ]);
                }
            }
            return $run;
        }, 3);
        if (is_array($result) && isset($result['owner_error'])) {
            throw new \RuntimeException($result['owner_error']);
        }
        return $result;
    }


    /**
     * Create an isolated replay run at an explicit historical knowledge coordinate.
     *
     * Unlike a production run, this never resolves or creates today's configuration. The caller
     * must execute it inside a transaction that is rolled back after deterministic replay output
     * is captured; only the replay-result record is persisted outside that transaction.
     */
    public function createAsKnownReplayRun($requestedDate, $knowledgeCutoff, $sourceMode = 'api')
    {
        return DB::transaction(function () use ($requestedDate, $knowledgeCutoff, $sourceMode) {
            return $this->createAsKnownReplayRunWithinTransaction($requestedDate, $knowledgeCutoff, $sourceMode);
        });
    }

    private function createAsKnownReplayRunWithinTransaction($requestedDate, $knowledgeCutoff, $sourceMode = 'api')
    {
        $scope = MarketDataScope::fromConfig();
        $requestedDate = $scope->assertRequestedDate($requestedDate);
        $knowledgeCutoff = trim((string) $knowledgeCutoff);
        if ($knowledgeCutoff === '') {
            throw new \RuntimeException('REPLAY_KNOWLEDGE_CUTOFF_REQUIRED: isolated replay run requires knowledge_cutoff.');
        }
        $snapshot = $this->configSnapshots->resolveForRun($requestedDate, $knowledgeCutoff);
        $now = Carbon::now($scope->timezone())->toDateTimeString();

        $run = EodRun::query()->create([
            'trade_date_requested' => $requestedDate,
            'trade_date_effective' => null,
            'lifecycle_state' => 'PENDING',
            'terminal_status' => null,
            'quality_gate_state' => 'PENDING',
            'publishability_state' => 'NOT_READABLE',
            'stage' => 'INGEST_BARS',
            'source' => (string) $sourceMode,
            'request_mode' => 'replay_verify',
            'artifact_hash_profile' => $this->artifactHashProfile(),
            'publication_semantic_profile' => $this->publicationSemanticProfile(),
            'knowledge_cutoff_at' => $knowledgeCutoff,
            'config_version' => config('market_data.indicators.set_version'),
            'config_hash' => $snapshot['config_hash'],
            'config_snapshot_ref' => $snapshot['snapshot_uid'],
            'config_snapshot_id' => $snapshot['config_snapshot_id'],
            'operational_start_date' => $scope->operationalStartDate(),
            'freshness_state' => FreshnessState::runLabelFor($scope->operationalStartDate(), $requestedDate),
            'is_current_publication' => 0,
            'notes' => 'replay_mode=AS_KNOWN;replay_isolation=true',
            'started_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->appendEvent(
            $run,
            'INGEST_BARS',
            'RUN_CREATED',
            'INFO',
            'Isolated AS_KNOWN replay run created at explicit historical cutoff.',
            null,
            [
                'run_id' => (int) $run->run_id,
                'trade_date_requested' => $requestedDate,
                'knowledge_cutoff_at' => $knowledgeCutoff,
                'source_mode' => (string) $sourceMode,
                'request_mode' => 'replay_verify',
                'config_snapshot_id' => (int) $snapshot['config_snapshot_id'],
                'config_hash' => (string) $snapshot['config_hash'],
                'replay_isolation' => true,
            ]
        );

        (new RunInputCaptureRepository())->captureRunConfiguration($run, $snapshot);
        return $run;
    }


    public function createPromoteRunFromSeed(EodRun $seedRun, $stage, array $overrides = [])
    {
        $result = DB::transaction(function () use ($seedRun, $stage, $overrides) {
            return $this->createPromoteRunFromSeedWithinTransaction($seedRun, $stage, $overrides);
        });
        if (is_array($result) && isset($result['owner_error'])) {
            throw new \RuntimeException($result['owner_error']);
        }
        return $result;
    }

    private function createPromoteRunFromSeedWithinTransaction(EodRun $seedRun, $stage, array $overrides = [])
    {
        if (array_key_exists('knowledge_cutoff_at', $overrides)) {
            throw new \LogicException(
                'RUN_KNOWLEDGE_CUTOFF_IMMUTABLE: promote overrides cannot replace the creation coordinate.'
            );
        }

        $date = $seedRun->trade_date_requested;
        $this->lockDateOwnerFence($date);
        $recoveredRunIds = [];
        $recoveryBlock = $this->cancelStaleActiveRuns($date, $seedRun->source, $overrides['request_mode'] ?? 'promote', true, $recoveredRunIds);
        if ($recoveryBlock !== null) {
            return ['owner_error' => $recoveryBlock];
        }
        $activeOwner = EodRun::query()->where('trade_date_requested', $date)
            ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
            ->where(function ($query) {
                $query->whereIn('request_mode', $this->publishCriticalModes())->orWhereNull('request_mode');
            })->lockForUpdate()->first();
        if ($activeOwner !== null) {
            $this->appendEvent($activeOwner, $stage, 'RUN_OWNER_CONFLICT', 'WARN',
                'Competing promote run fenced before candidate execution.', 'RUN_OWNER_CONFLICT', [
                    'owner_run_id' => (int) $activeOwner->run_id,
                    'competing_seed_run_id' => (int) $seedRun->run_id,
                    'trade_date_requested' => $date,
                    'decision' => 'BLOCKED',
                ]);
            return ['owner_error' => 'RUN_OWNER_CONFLICT: active owner run '.(int) $activeOwner->run_id.' holds requested date '.$date];
        }

        $now = $this->captureKnowledgeCutoff();

        $payload = [
            'trade_date_requested' => $seedRun->trade_date_requested,
            'trade_date_effective' => null,
            'lifecycle_state' => 'PENDING',
            'terminal_status' => null,
            'quality_gate_state' => 'PENDING',
            'publishability_state' => 'NOT_READABLE',
            'stage' => $stage,
            'source' => $seedRun->source,
            'request_mode' => $overrides['request_mode'] ?? 'promote',
            'source_name' => $seedRun->source_name,
            'source_provider' => $seedRun->source_provider,
            'source_input_file' => $seedRun->source_input_file,
            'source_timeout_seconds' => $seedRun->source_timeout_seconds,
            'source_retry_max' => $seedRun->source_retry_max,
            'source_attempt_count' => $seedRun->source_attempt_count,
            'source_success_after_retry' => $seedRun->source_success_after_retry,
            'source_retry_exhausted' => $seedRun->source_retry_exhausted,
            'source_final_http_status' => $seedRun->source_final_http_status,
            'source_final_reason_code' => $seedRun->source_final_reason_code,
            'source_file_hash' => $seedRun->source_file_hash,
            'source_file_hash_algorithm' => $seedRun->source_file_hash_algorithm,
            'source_file_size_bytes' => $seedRun->source_file_size_bytes,
            'source_file_row_count' => $seedRun->source_file_row_count,
            'coverage_universe_count' => null,
            'coverage_available_count' => null,
            'coverage_missing_count' => null,
            'coverage_ratio' => null,
            'coverage_min_threshold' => null,
            'coverage_gate_state' => null,
            'coverage_threshold_mode' => null,
            'coverage_universe_basis' => null,
            'knowledge_cutoff_at' => $now,
            'coverage_contract_version' => null,
            'coverage_missing_sample_json' => null,
            'bars_rows_written' => $seedRun->bars_rows_written,
            'indicators_rows_written' => null,
            'eligibility_rows_written' => null,
            'invalid_bar_count' => $seedRun->invalid_bar_count,
            'invalid_indicator_count' => null,
            'hard_reject_count' => null,
            'warning_count' => null,
            'notes' => $seedRun->notes,
            'bars_batch_hash' => null,
            'indicators_batch_hash' => null,
            'eligibility_batch_hash' => null,
            'artifact_hash_profile' => $this->artifactHashProfile(),
            'publication_semantic_profile' => $this->publicationSemanticProfile(),
            'config_version' => $seedRun->config_version ?: config('market_data.indicators.set_version'),
            'config_hash' => $seedRun->config_hash,
            'config_snapshot_ref' => $seedRun->config_snapshot_ref,
            'config_snapshot_id' => $seedRun->config_snapshot_id,
            'observation_manifest_hash' => $seedRun->observation_manifest_hash,
            'operational_start_date' => $seedRun->operational_start_date,
            // Decided again from the requested date and the marker the seed run carries, never copied: a copied legacy label would
            // make a corrected publication carry the pre-correction state.
            'freshness_state' => FreshnessState::runLabelFor($seedRun->operational_start_date, $seedRun->trade_date_requested),
            'supersedes_run_id' => $seedRun->supersedes_run_id,
            'publication_id' => null,
            'publication_version' => null,
            'is_current_publication' => 0,
            'correction_id' => null,
            'promote_mode' => null,
            'publish_target' => null,
            'final_reason_code' => null,
            'sealed_at' => null,
            'sealed_by' => null,
            'seal_note' => null,
            'started_at' => $now,
            'finished_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        /*
         * A promote run must record the configuration it actually executed under.
         *
         * This payload carried no config identity at all, so every run created here — including the
         * 843 recompute runs of 2026-08-10/11 — was born with config_snapshot_id NULL, and
         * md_config_snapshots stayed empty even though the resolver was wired into
         * getOrCreateOwningRun. Replay then blocked such runs as REPLAY_CONFIG_UNBOUND.
         *
         * The snapshot is resolved fresh rather than copied from the seed. The seed describes an
         * earlier execution; this run executes under today's resolved config, and claiming the
         * seed's identity would attribute a configuration this run never used.
         */
        $snapshot = $this->configSnapshots->resolveForRun($seedRun->trade_date_requested);
        $payload['config_snapshot_id'] = $snapshot['config_snapshot_id'];
        $payload['config_hash'] = $snapshot['config_hash'];
        $payload['config_snapshot_ref'] = $snapshot['snapshot_uid'];

        // A V2 promote run inherits the seed's V2 observation manifest exactly as it inherits the V1
        // one; a V1 seed has none, and the V2 nested binding then fails closed instead of guessing.
        if ($payload['artifact_hash_profile'] === \App\Application\MarketData\Services\ArtifactSemanticHashService::PROFILE_V2
            && ! empty($seedRun->semantic_observation_manifest_hash)) {
            $payload['semantic_observation_manifest_hash'] = $seedRun->semantic_observation_manifest_hash;
        }

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        $run = EodRun::query()->create($payload);

        $this->appendEvent(
            $run,
            $stage,
            'RUN_CREATED',
            'INFO',
            'Market-data promote run created from seed run context.',
            null,
            [
                'run_id' => (int) $run->run_id,
                'seed_run_id' => (int) $seedRun->run_id,
                'trade_date_requested' => $run->trade_date_requested,
                'trade_date_effective' => null,
                'source_mode' => $run->source,
                'request_mode' => $run->request_mode ?? ($overrides['request_mode'] ?? 'promote'),
                'promote_mode' => $run->promote_mode,
                'publish_target' => $run->publish_target,
                'lifecycle_state' => 'PENDING',
                'publishability_state' => 'NOT_READABLE',
            ]
        );

        (new RunInputCaptureRepository())->captureRunConfiguration($run, $snapshot);
        $this->setDateOwnerFence($date, (int) $run->run_id);
        if ($recoveredRunIds !== []) {
            $this->appendEvent($run, $stage, 'RUN_OWNER_TAKEOVER', 'WARN',
                'Promote run acquired requested-date fence after verified stale-owner abandonment.', 'RUN_OWNER_TAKEOVER', [
                    'owner_run_id' => (int) $run->run_id,
                    'previous_owner_run_ids' => $recoveredRunIds,
                    'trade_date_requested' => $date,
                    'candidate_trust' => 'UNTRUSTWORTHY_ABANDONED',
                    'partial_publication_excluded' => true,
                    'prior_current_resolution' => 'VERIFIED_OR_ABSENT',
                    'decision' => 'TAKEOVER_ALLOWED',
                ]);
        }
        return $run;
    }

    public function findByRunId($runId)
    {
        return EodRun::query()->where('run_id', $runId)->first();
    }


    public function findLatestForRequestedDate($requestedDate, $sourceMode = null)
    {
        $query = EodRun::query()
            ->where('trade_date_requested', $requestedDate)
            ->orderByDesc('run_id');

        if ($sourceMode !== null && trim((string) $sourceMode) !== '') {
            $query->where('source', $sourceMode);
        }

        return $query->first();
    }


    public function findLatestTerminalEventForRun($runId)
    {
        return EodRunEvent::query()
            ->where('run_id', $runId)
            ->whereIn('event_type', ['RUN_FINALIZED', 'STAGE_FAILED'])
            ->orderByDesc('event_id')
            ->first();
    }

    public function touchStage(EodRun $run, $stage, array $attributes = [])
    {
        $run->stage = $stage;

        if ($stage === 'FINALIZE') {
            $run->lifecycle_state = 'FINALIZING';
        } elseif ($run->lifecycle_state === 'PENDING') {
            $run->lifecycle_state = 'RUNNING';
        }

        foreach ($attributes as $key => $value) {
            $run->{$key} = $value;
        }

        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function appendEvent(EodRun $run, $stage, $eventType, $severity, $message, $reasonCode = null, array $payload = [])
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $severity = $this->normalizeSeverity($severity);
        $message = $this->truncateMessage($message);

        return EodRunEvent::query()->create([
            'run_id' => $run->run_id,
            'trade_date_requested' => $run->trade_date_requested,
            'event_time' => $now,
            'stage' => $stage,
            'event_type' => $eventType,
            'severity' => $severity,
            'reason_code' => $reasonCode,
            'message' => $message,
            'event_payload_json' => empty($payload) ? null : json_encode($payload),
            'created_at' => $now,
        ]);
    }

    public function failStage(EodRun $run, $stage, $reasonCode, $message, array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'STAGE_FAILED',
            'ERROR',
            $message,
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->lifecycle_state = 'FAILED';
        $run->terminal_status = 'FAILED';
        $run->quality_gate_state = 'FAIL';
        $run->publishability_state = 'NOT_READABLE';
        $run->final_reason_code = $reasonCode;
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function holdStage(EodRun $run, $stage, $reasonCode, $message, $tradeDateEffective = null, array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
            'trade_date_effective' => $tradeDateEffective,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'STAGE_FAILED',
            'WARN',
            $message,
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->lifecycle_state = 'COMPLETED';
        $run->terminal_status = 'HELD';
        $run->quality_gate_state = 'BLOCKED';
        $run->publishability_state = 'NOT_READABLE';
        $run->final_reason_code = $reasonCode;
        $run->trade_date_effective = $tradeDateEffective;
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function completeImportOnly(EodRun $run, $stage, $reasonCode = 'IMPORT_ONLY_COMPLETED_NOT_PROMOTED', array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
            'request_mode' => 'import_only',
            'import_status' => 'COMPLETED',
            'promote_status' => 'NOT_PROMOTED',
            'promoted' => false,
            'pointer_switched' => false,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'IMPORT_ONLY_COMPLETED_NOT_PROMOTED',
            'INFO',
            'Import-only run closed as completed non-readable candidate; promote remains explicit.',
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->stage = $stage;
        $run->lifecycle_state = 'COMPLETED';
        $run->terminal_status = null;
        $run->publishability_state = 'NOT_READABLE';
        $run->is_current_publication = 0;
        $run->final_reason_code = $reasonCode;
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    /**
     * Return the knowledge coordinate captured when this run was created.
     *
     * Deliberately not `started_at`. Identity projection happens as a side effect of the first
     * universe read, which lands after the run row is created, so every listing it writes carries a
     * `recorded_at` later than `started_at` and a cutoff taken from there emptied the universe
     * outright — 31 errors and 5 failures on 2026-08-11, reverted the same day. Projection is driven
     * to completion first and the coordinate is stamped after it, so the rows projection just wrote
     * fall inside the cutoff instead of outside it.
     *
     * New runs are stamped by both repository creation paths after identity projection completes.
     * Runs that predate the column keep a null coordinate as faithful historical state; this reader
     * must never invent a coordinate later, because no cutoff was honoured by their earlier stages.
     * Execution entry points reject that legacy state instead of resuming it without a boundary.
     */
    public function resolveKnowledgeCutoff(EodRun $run)
    {
        if (! empty($run->knowledge_cutoff_at)) {
            return (string) $run->knowledge_cutoff_at;
        }

        return null;
    }

    /**
     * Project legacy identity first, then capture one immutable coordinate for the new run.
     */
    private function captureKnowledgeCutoff(): Carbon
    {
        (new TemporalIdentityRepository())->ensureLegacyProjection();

        return Carbon::now(config('market_data.platform.timezone'));
    }

    public function updateTelemetry(EodRun $run, array $telemetry)
    {
        $telemetry = $this->normalizeTelemetry($telemetry);
        $this->assertCompleteCoverageTelemetry($run, $telemetry);

        foreach ($telemetry as $key => $value) {
            $run->{$key} = $value;
        }

        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function storeHashes(EodRun $run, array $hashes)
    {
        $run->bars_batch_hash = $hashes['bars_batch_hash'];
        $run->indicators_batch_hash = $hashes['indicators_batch_hash'];
        $run->eligibility_batch_hash = $hashes['eligibility_batch_hash'];
        if (isset($hashes['artifact_hash_profile'])) {
            $run->artifact_hash_profile = $hashes['artifact_hash_profile'];
            $run->publication_semantic_profile = (new PublicationSemanticIdentityService())
                ->profileForArtifactProfile($hashes['artifact_hash_profile']);
        }
        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    private function artifactHashProfile(): string
    {
        // This selects the persisted serializer profile; it is not artifact content and therefore
        // does not belong to the strategy-owned resolved configuration snapshot, so it is read
        // from config/market_data_runtime.php. Production defaults to V2. Legacy
        // verification/test harnesses opt into V1 explicitly. A missing value fails closed.
        $profile = trim((string) config('market_data_runtime.artifact_hash_profile'));
        if (!in_array($profile, [
            ArtifactSemanticHashService::PROFILE_V2,
            ArtifactSemanticHashService::LEGACY_PROFILE_V1,
        ], true)) {
            throw new \RuntimeException('ARTIFACT_HASH_PROFILE_UNSUPPORTED: '.($profile === '' ? 'NULL' : $profile));
        }

        return $profile;
    }

    private function publicationSemanticProfile(): string
    {
        return (new PublicationSemanticIdentityService())
            ->profileForArtifactProfile($this->artifactHashProfile());
    }

    public function markSealed(EodRun $run, $sealedBy, $sealNote)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->sealed_at = $now;
        $run->sealed_by = $sealedBy;
        $run->seal_note = $sealNote;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function syncCurrentPublicationMirror($tradeDate, $currentRunId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodRun::query()
            ->where('trade_date_requested', $tradeDate)
            ->update([
                'is_current_publication' => 0,
                'updated_at' => $now,
            ]);

        EodRun::query()
            ->where('run_id', $currentRunId)
            ->update([
                'is_current_publication' => 1,
                'updated_at' => $now,
            ]);
    }


    private function recordFencedWorkerOutcome(string $requestedDate, string $sourceMode, ?string $requestMode, string $stage, int $ownerRunId): EodRun
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $blocked = EodRun::query()->create([
            'trade_date_requested' => $requestedDate,
            'lifecycle_state' => 'COMPLETED',
            'terminal_status' => 'BLOCKED',
            'quality_gate_state' => 'BLOCKED',
            'publishability_state' => 'NOT_READABLE',
            'stage' => $stage,
            'source' => $sourceMode,
            'request_mode' => $requestMode,
            'is_current_publication' => 0,
            'final_reason_code' => 'RUN_LOCK_CONFLICT',
            'started_at' => $now,
            'finished_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->appendEvent($blocked, $stage, 'RUN_OWNER_CONFLICT', 'WARN',
            'Worker blocked before execution by an active requested-date owner.', 'RUN_LOCK_CONFLICT', [
                'owner_run_id' => $ownerRunId,
                'blocked_run_id' => (int) $blocked->run_id,
                'trade_date_requested' => $requestedDate,
                'decision' => 'BLOCKED',
                'publication_unchanged' => true,
            ]);
        return $blocked;
    }

    private function publishCriticalModes(): array
    {
        return ['full_publish', 'promote', 'correction', 'corpus_reconstruction', 'repair_candidate'];
    }

    private function isPublishCriticalMode($requestMode): bool
    {
        return $requestMode === null || in_array((string) $requestMode, $this->publishCriticalModes(), true);
    }

    private function lockDateOwnerFence($requestedDate): void
    {
        DB::table('eod_run_owner_fences')->insertOrIgnore([
            'trade_date_requested' => $requestedDate,
            'owner_run_id' => null,
            'updated_at' => Carbon::now(config('market_data.platform.timezone')),
        ]);
        DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $requestedDate)->lockForUpdate()->first();
    }

    private function setDateOwnerFence($requestedDate, int $runId): void
    {
        DB::table('eod_run_owner_fences')->where('trade_date_requested', $requestedDate)->update([
            'owner_run_id' => $runId,
            'updated_at' => Carbon::now(config('market_data.platform.timezone')),
        ]);
    }

    public function assertPublishCriticalOwner(EodRun $run): void
    {
        if (! $this->isPublishCriticalMode($run->request_mode)) {
            throw new \RuntimeException('RUN_OWNER_INVALID_MODE: non-publish run cannot enter publish-critical stage.');
        }
        DB::transaction(function () use ($run) {
            $this->lockDateOwnerFence($run->trade_date_requested);
            $active = EodRun::query()
                ->where('trade_date_requested', $run->trade_date_requested)
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
                ->where(function ($query) {
                    $query->whereIn('request_mode', $this->publishCriticalModes())->orWhereNull('request_mode');
                })
                ->lockForUpdate()->pluck('run_id')->map(static fn ($id) => (int) $id)->all();
            if (count($active) !== 1 || $active[0] !== (int) $run->run_id) {
                throw new \RuntimeException('RUN_OWNER_CONFLICT: publish-critical stage requires exactly one active owner for requested date.');
            }
            $fence = DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)->first();
            if ($fence->owner_run_id !== null && (int) $fence->owner_run_id !== (int) $run->run_id) {
                throw new \RuntimeException('RUN_OWNER_CONFLICT: run lost its requested-date owner fence.');
            }
            $this->setDateOwnerFence($run->trade_date_requested, (int) $run->run_id);
        });
    }

    private function staleTakeoverBlockReason(EodRun $staleRun): ?string
    {
        foreach (['publication_id', 'sealed_at', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'seal_fingerprint'] as $field) {
            if ($staleRun->{$field} !== null && (string) $staleRun->{$field} !== '') {
                return 'candidate_'.$field.'_present';
            }
        }
        if ((int) $staleRun->is_current_publication !== 0 || DB::table('eod_publications')->where('run_id', $staleRun->run_id)->exists()) {
            return 'candidate_publication_state_ambiguous';
        }
        $pointer = (new EodPublicationRepository())->findRawCurrentPublicationStateForTradeDate($staleRun->trade_date_requested);
        if ($pointer !== null) {
            if ((int) $pointer->pointer_run_id === (int) $staleRun->run_id) {
                return 'stale_owner_is_current_pointer';
            }
            if ((new EodPublicationRepository())->determineCurrentIntegrityViolationReasons($pointer) !== []) {
                return 'prior_current_integrity_unverified';
            }
        }
        return null;
    }

    private function cancelStaleActiveRuns($requestedDate, $sourceMode, $requestMode = null, bool $publishCritical = false, array &$recoveredRunIds = []): ?string
    {
        if ($publishCritical) {
            $active = EodRun::query()->where('trade_date_requested', $requestedDate)
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
                ->where(function ($query) {
                    $query->whereIn('request_mode', $this->publishCriticalModes())->orWhereNull('request_mode');
                })->lockForUpdate()->get();
            if ($active->count() > 1) {
                foreach ($active as $run) {
                    $this->appendEvent($run, $run->stage ?: 'INGEST_BARS', 'RUN_OWNER_AMBIGUOUS', 'ERROR',
                        'Multiple active publish-critical runs prevent owner selection or takeover.', 'RUN_OWNER_AMBIGUOUS', [
                            'active_run_ids' => $active->pluck('run_id')->map(static fn ($id) => (int) $id)->all(),
                            'trade_date_requested' => $requestedDate,
                            'decision' => 'BLOCKED',
                        ]);
                }
                return 'RUN_OWNER_AMBIGUOUS: multiple active publish-critical runs for requested date '.$requestedDate;
            }
        }
        $staleMinutes = (int) config('market_data.pipeline.active_run_stale_minutes', 1440);

        if ($staleMinutes <= 0) {
            return null;
        }

        $now = Carbon::now(config('market_data.platform.timezone'));
        $cutoff = $now->copy()->subMinutes($staleMinutes);

        $query = EodRun::query()
            ->where('trade_date_requested', $requestedDate)
            ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
            ->whereNull('finished_at')
            ->where('updated_at', '<', $cutoff)
            ->where(function ($query) use ($requestMode, $sourceMode, $publishCritical) {
                if ($publishCritical) {
                    $query->whereIn('request_mode', $this->publishCriticalModes())->orWhereNull('request_mode');
                } else {
                    $query->where('source', $sourceMode)->where('request_mode', $requestMode);
                }
            })
            ->orderBy('run_id')
            ->lockForUpdate();

        $staleRuns = $query->get();
        foreach ($staleRuns as $staleRun) {
            $blockReason = $this->staleTakeoverBlockReason($staleRun);
            if ($blockReason !== null) {
                $this->appendEvent($staleRun, $staleRun->stage ?: 'INGEST_BARS', 'STALE_OWNER_TAKEOVER_BLOCKED', 'WARN',
                    'Stale owner recovery blocked because candidate or prior current state cannot be trusted.', 'STALE_OWNER_TAKEOVER_BLOCKED', [
                        'owner_run_id' => (int) $staleRun->run_id,
                        'trade_date_requested' => $requestedDate,
                        'candidate_trust' => 'UNVERIFIED',
                        'partial_publication_excluded' => false,
                        'decision' => 'BLOCKED',
                        'block_reason' => $blockReason,
                    ]);
                return 'STALE_OWNER_TAKEOVER_BLOCKED: '.$blockReason.' for owner run '.(int) $staleRun->run_id;
            }
        }

        foreach ($staleRuns as $staleRun) {
            $previousState = (string) ($staleRun->lifecycle_state ?? '');
            $previousUpdatedAt = $staleRun->updated_at ? (string) $staleRun->updated_at : null;

            $this->appendEvent(
                $staleRun,
                $staleRun->stage ?: 'INGEST_BARS',
                'STALE_ACTIVE_RUN_CANCELLED',
                'WARN',
                'Stale active run cancelled before creating or reusing an owning run.',
                'STALE_ACTIVE_RUN_CANCELLED',
                [
                    'run_id' => (int) $staleRun->run_id,
                    'trade_date_requested' => $requestedDate,
                    'source_mode' => $sourceMode,
                    'request_mode' => $requestMode,
                    'previous_lifecycle_state' => $previousState,
                    'previous_updated_at' => $previousUpdatedAt,
                    'stale_after_minutes' => $staleMinutes,
                    'cutoff_at' => (string) $cutoff,
                    'candidate_trust' => 'UNTRUSTWORTHY_ABANDONED',
                    'partial_publication_excluded' => true,
                    'prior_current_resolution' => 'VERIFIED_OR_ABSENT',
                    'decision' => 'TAKEOVER_ALLOWED',
                ]
            );

            $staleRun->lifecycle_state = 'CANCELLED';
            $staleRun->terminal_status = null;
            $staleRun->publishability_state = 'NOT_READABLE';
            $staleRun->final_reason_code = 'STALE_ACTIVE_RUN_CANCELLED';
            $staleRun->finished_at = $now;
            $staleRun->updated_at = $now;
            $staleRun->save();
            $recoveredRunIds[] = (int) $staleRun->run_id;
        }
        return null;
    }

    private function normalizeTelemetry(array $telemetry)
    {
        if (array_key_exists('expected_universe_count', $telemetry) && ! array_key_exists('coverage_universe_count', $telemetry)) {
            $telemetry['coverage_universe_count'] = $telemetry['expected_universe_count'];
        }

        if (array_key_exists('available_eod_count', $telemetry) && ! array_key_exists('coverage_available_count', $telemetry)) {
            $telemetry['coverage_available_count'] = $telemetry['available_eod_count'];
        }

        if (array_key_exists('missing_eod_count', $telemetry) && ! array_key_exists('coverage_missing_count', $telemetry)) {
            $telemetry['coverage_missing_count'] = $telemetry['missing_eod_count'];
        }

        if (array_key_exists('coverage_gate_status', $telemetry) && ! array_key_exists('coverage_gate_state', $telemetry)) {
            $telemetry['coverage_gate_state'] = $telemetry['coverage_gate_status'];
        }
        unset($telemetry['coverage_gate_status']);

        if (array_key_exists('coverage_gate_state', $telemetry)) {
            $telemetry['coverage_gate_state'] = CoverageGateStateNormalizer::normalize($telemetry['coverage_gate_state']);
        }

        if (array_key_exists('coverage_threshold_value', $telemetry) && ! array_key_exists('coverage_min_threshold', $telemetry)) {
            $telemetry['coverage_min_threshold'] = $telemetry['coverage_threshold_value'];
        }

        if (array_key_exists('coverage_calibration_version', $telemetry) && ! array_key_exists('coverage_contract_version', $telemetry)) {
            $telemetry['coverage_contract_version'] = $telemetry['coverage_calibration_version'];
        }

        if (array_key_exists('missing_ticker_codes', $telemetry) && ! array_key_exists('coverage_missing_sample_json', $telemetry)) {
            $telemetry['coverage_missing_sample_json'] = $telemetry['missing_ticker_codes'];
        }

        if (array_key_exists('coverage_missing_sample_json', $telemetry) && is_array($telemetry['coverage_missing_sample_json'])) {
            $telemetry['coverage_missing_sample_json'] = json_encode(array_values($telemetry['coverage_missing_sample_json']));
        }

        foreach (['source_success_after_retry', 'source_retry_exhausted'] as $booleanField) {
            if (array_key_exists($booleanField, $telemetry) && $telemetry[$booleanField] !== null) {
                $telemetry[$booleanField] = $telemetry[$booleanField] ? 1 : 0;
            }
        }

        return $telemetry;
    }

    private function assertCompleteCoverageTelemetry(EodRun $run, array $telemetry): void
    {
        $coverageTouched = false;
        foreach (array_keys($telemetry) as $field) {
            if (strpos((string) $field, 'coverage_') === 0) {
                $coverageTouched = true;
                break;
            }
        }

        if (! $coverageTouched) {
            return;
        }

        $merged = [];
        foreach (self::REQUIRED_COVERAGE_EVIDENCE_WRITE_FIELDS as $field) {
            $merged[$field] = array_key_exists($field, $telemetry)
                ? $telemetry[$field]
                : $run->{$field};
        }

        $missing = [];
        $invalid = [];
        foreach ($merged as $field => $value) {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $missing[] = $field;
                continue;
            }

            if (! is_numeric($value) || (float) $value < 0 || floor((float) $value) !== (float) $value) {
                $invalid[] = $field;
            }
        }

        if ($missing !== [] || $invalid !== []) {
            $details = [];
            if ($missing !== []) {
                $details[] = 'missing '.implode(', ', $missing);
            }
            if ($invalid !== []) {
                $details[] = 'non-negative integer required for '.implode(', ', $invalid);
            }

            throw new \LogicException(
                'COVERAGE_EVIDENCE_WRITE_INCOMPLETE: '.implode('; ', $details).'.'
            );
        }
    }

    private function normalizeSeverity($severity)
    {
        if ($severity === 'WARNING') {
            return 'WARN';
        }

        return in_array($severity, ['INFO', 'WARN', 'ERROR'], true) ? $severity : 'ERROR';
    }

    private function truncateMessage($message)
    {
        $message = trim((string) $message);
        if ($message === '') {
            return null;
        }

        return mb_strlen($message) <= 255 ? $message : mb_substr($message, 0, 252).'...';
    }

    public function finalize(EodRun $run, array $finalState)
    {
        if (($finalState['terminal_status'] ?? null) === 'SUCCESS'
            || ($finalState['publishability_state'] ?? null) === 'READABLE') {
            $this->assertPublishCriticalOwner($run);
        }
        $now = Carbon::now(config('market_data.platform.timezone'));

        foreach ($finalState as $key => $value) {
            $run->{$key} = $value;
        }

        $run->lifecycle_state = $finalState['lifecycle_state'];
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }
}
