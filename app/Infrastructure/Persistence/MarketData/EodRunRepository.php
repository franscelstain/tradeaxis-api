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

    public function getOrCreateOwningRun($requestedDate, $sourceMode, $stage, $supersedesRunId = null, $requestMode = null, $preApplyDisclosure = null)
    {
        if ($requestMode === 'replay_verify') {
            ReplayIsolation::assertActive();
        }
        $scope = MarketDataScope::fromConfig();
        $requestedDate = $scope->assertRequestedDate($requestedDate);

        $result = DB::transaction(function () use ($requestedDate, $sourceMode, $stage, $supersedesRunId, $requestMode, $scope, $preApplyDisclosure) {
            $publishCritical = $this->isPublishCriticalMode($requestMode);
            if ($preApplyDisclosure !== null) {
                if ($requestMode !== 'correction' || (string) $preApplyDisclosure['requested_date'] !== $requestedDate
                    || (string) $preApplyDisclosure['source_mode'] !== (string) $sourceMode) {
                    throw new \RuntimeException('COMMAND_CORRECTION_PREFLIGHT_SCOPE_MISMATCH');
                }
                app(\App\Application\MarketData\Services\CorrectionCommandPreflightService::class)
                    ->assertMatches($preApplyDisclosure);
            }
            $this->lockDateOwnerFence($requestedDate);
            return RunWriterContext::observeDate('conflict', $requestedDate, null, function () use ($requestedDate, $sourceMode, $stage, $supersedesRunId, $requestMode, $scope, $publishCritical, $preApplyDisclosure) {
            if ($preApplyDisclosure !== null) {
                app(\App\Application\MarketData\Services\CorrectionCommandPreflightService::class)
                    ->assertMatches($preApplyDisclosure, true);
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
                ->whereNotIn('run_id', $recoveredRunIds)
                ->orderByDesc('run_id')
                ->lockForUpdate()
                ->first();

            if ($activeRun) {
                if ($preApplyDisclosure !== null && ((int) $preApplyDisclosure['new_run_id'] !== (int) $activeRun->run_id
                    || (int) $preApplyDisclosure['new_config_snapshot_id'] !== (int) $activeRun->config_snapshot_id
                    || (string) $preApplyDisclosure['candidate_config_content_hash'] !== (string) $activeRun->config_hash)) {
                    throw new \RuntimeException('COMMAND_CORRECTION_PREFLIGHT_CHANGED');
                }
                if ((string) $activeRun->source !== (string) $sourceMode || (string) $activeRun->request_mode !== (string) $requestMode) {
                    $blockedRun = $this->recordFencedWorkerOutcome($requestedDate, $sourceMode, $requestMode, $stage, (int) $activeRun->run_id);
                    $this->recordObservation($activeRun, 'conflict', $stage, 'RUN_OWNER_CONFLICT', 'WARN',
                        'Competing request observed by its own diagnostic actor; incumbent authority is not borrowed.', 'RUN_LOCK_CONFLICT', [
                            'owner_run_id' => (int) $activeRun->run_id, 'blocked_run_id' => (int) $blockedRun->run_id,
                            'competing_source_mode' => (string) $sourceMode, 'competing_request_mode' => (string) $requestMode,
                            'trade_date_requested' => $requestedDate, 'decision' => 'BLOCKED',
                        ]);
                    return ['owner_error' => 'RUN_OWNER_CONFLICT: active owner run '.(int) $activeRun->run_id.'; blocked run '.(int) $blockedRun->run_id];
                }
                (new RequestedDateWriterFence())->lockRun($requestedDate, $activeRun);
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

            $lease = DB::table('eod_run_owner_fences')->where('trade_date_requested', $requestedDate)->first();
            if ($lease && $lease->owner_run_id && ! in_array((int) $lease->owner_run_id, $recoveredRunIds, true) && $lease->released_at === null
                && EodRun::where('run_id', $lease->owner_run_id)->whereIn('lifecycle_state', ['PENDING','RUNNING','FINALIZING'])->exists()) {
                return ['owner_error' => 'RUN_OWNER_CONFLICT: requested date belongs to another execution scope.'];
            }
            $snapshot = $this->configSnapshots->resolveForRun($requestedDate);
            if ($preApplyDisclosure !== null && ((string) $preApplyDisclosure['new_run_id'] !== 'PENDING_ALLOCATION'
                || (string) $preApplyDisclosure['new_config_snapshot_id'] !== 'PENDING_ALLOCATION'
                || (string) $snapshot['config_hash'] !== (string) $preApplyDisclosure['candidate_config_content_hash'])) {
                throw new \RuntimeException('COMMAND_CORRECTION_PREFLIGHT_CHANGED');
            }
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

            $this->setDateOwnerFence($requestedDate, (int) $run->run_id);
            $this->issueExecutionCredential($run);
            $this->cancelVerifiedPredecessors($run, $recoveredRunIds);

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
                    'pre_apply_disclosure_sha256' => $preApplyDisclosure === null ? null
                        : hash('sha256', RunInputCaptureRepository::canonicalJson($preApplyDisclosure)),
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
            });
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
     * must execute it inside ReplayIsolation, which owns the dedicated rollback-only root after output
     * is captured; only the replay-result record is persisted outside that transaction.
     */
    public function createAsKnownReplayRun($requestedDate, $knowledgeCutoff, $sourceMode = 'api')
    {
        ReplayIsolation::assertActive();
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

        $this->lockDateOwnerFence($requestedDate);
        $lease = DB::table('eod_run_owner_fences')->where('trade_date_requested', $requestedDate)->first();
        if ($lease->owner_run_id && $lease->released_at === null) throw new \RuntimeException('RUN_OWNER_CONFLICT: replay cannot borrow live date ownership.');

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

        $this->setDateOwnerFence($requestedDate, (int) $run->run_id);
        $this->issueExecutionCredential($run);

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

        // Acquisition accepts intent, never caller-supplied persisted state or actor identity.
        $allowedOverrides = ['request_mode', 'notes', 'correction_id', 'promote_mode', 'publish_target'];
        if (array_diff(array_keys($overrides), $allowedOverrides) !== []) {
            if (array_key_exists('config_hash', $overrides)) {
                throw new \RuntimeException('INPUT_CAPTURE_CONFIG_IDENTITY_MISMATCH: promote overrides cannot replace execution configuration.');
            }
            throw new \RuntimeException('RUN_OWNER_CONFLICT: PROMOTE_OVERRIDE_INVALID: only explicit promote intent may be supplied.');
        }
        $requestMode = array_key_exists('request_mode', $overrides) ? $overrides['request_mode'] : 'promote';
        if (! in_array($requestMode, $this->publishCriticalModes(), true)) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: PROMOTE_SCOPE_INVALID: isolated execution requires its own acquisition boundary.');
        }

        $seedIdentity = EodRun::query()->select(['run_id', 'trade_date_requested'])->findOrFail($seedRun->getKey());
        $date = (string) $seedIdentity->trade_date_requested;
        $this->lockDateOwnerFence($date);
        return RunWriterContext::observeDate('conflict', (string) $date, null, function () use ($seedRun, $seedIdentity, $stage, $overrides, $date) {
        // Date, observer, then run: retain the existing acquisition lock order.
        $seedRun = EodRun::query()->where('run_id', $seedIdentity->run_id)->lockForUpdate()->firstOrFail();
        if ((string) $seedRun->trade_date_requested !== $date) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: PROMOTE_SCOPE_INVALID: persisted seed date changed during acquisition.');
        }
        $recoveredRunIds = [];
        $recoveryBlock = $this->cancelStaleActiveRuns($date, $seedRun->source, $overrides['request_mode'] ?? 'promote', true, $recoveredRunIds);
        if ($recoveryBlock !== null) {
            return ['owner_error' => $recoveryBlock];
        }
        $activeOwner = EodRun::query()->where('trade_date_requested', $date)
            ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
            ->where(function ($query) {
                $query->whereIn('request_mode', $this->publishCriticalModes())->orWhereNull('request_mode');
            })->whereNotIn('run_id', $recoveredRunIds)->lockForUpdate()->first();
        if ($activeOwner !== null) {
            $this->recordObservation($activeOwner, 'conflict', $stage, 'RUN_OWNER_CONFLICT', 'WARN',
                'Competing promote run fenced before candidate execution.', 'RUN_OWNER_CONFLICT', [
                    'owner_run_id' => (int) $activeOwner->run_id,
                    'competing_seed_run_id' => (int) $seedRun->run_id,
                    'trade_date_requested' => $date,
                    'decision' => 'BLOCKED',
                ]);
            return ['owner_error' => 'RUN_OWNER_CONFLICT: active owner run '.(int) $activeOwner->run_id.' holds requested date '.$date];
        }

        $lease = DB::table('eod_run_owner_fences')->where('trade_date_requested', $date)->first();
        if ($lease && $lease->owner_run_id && $lease->released_at === null
            && ! in_array((int) $lease->owner_run_id, $recoveredRunIds, true)
            && EodRun::where('run_id', $lease->owner_run_id)->whereIn('lifecycle_state', ['PENDING','RUNNING','FINALIZING'])->exists()) {
            return ['owner_error' => 'RUN_OWNER_CONFLICT: promotion cannot displace another active execution scope.'];
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
        $this->setDateOwnerFence($date, (int) $run->run_id);

        $this->issueExecutionCredential($run);
        $this->cancelVerifiedPredecessors($run, $recoveredRunIds);

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
        });
    }

    public function acquireLinkedRecoveryOwner(string $date, string $source, ?int $historicalRunId): EodRun
    {
        return DB::transaction(function () use ($date, $source, $historicalRunId) {
            $this->lockDateOwnerFence($date);
            if (EodRun::where('trade_date_requested', $date)->whereIn('lifecycle_state', ['PENDING','RUNNING','FINALIZING'])
                ->lockForUpdate()->exists()) {
                throw new \RuntimeException('RUN_OWNER_CONFLICT: operator recovery cannot borrow or displace an active execution.');
            }
            return $this->getOrCreateOwningRun($date, $source, 'FINALIZE', $historicalRunId, 'repair_candidate');
        });
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
        return DB::transaction(function () use ($run, $stage, $attributes) {
            $this->assertExistingWriteFence($run, $attributes);
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
        });
    }

    private function assertRunAttributes(EodRun $run, array $attributes, bool $initialSeal = false): void
    {
        $proposed = array_merge($run->getDirty(), $attributes);
        foreach (['run_id', 'trade_date_requested', 'source', 'request_mode'] as $field) {
            if (array_key_exists($field, $proposed) && (string) $proposed[$field] !== (string) $run->$field) {
                throw new \RuntimeException('RUN_OWNER_CONFLICT: acquired execution identity is immutable: '.$field);
            }
        }
        if (in_array($run->request_mode, ['import_only', 'replay_verify'], true)
            && (($proposed['publishability_state'] ?? null) === 'READABLE'
                || ($proposed['terminal_status'] ?? null) === 'SUCCESS'
                || ! empty($proposed['is_current_publication']) || ! empty($proposed['sealed_at']))) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: isolated execution has no readable publication rights.');
        }
        $this->assertSealedRunContent($run, $attributes, $initialSeal);
    }

    /** Compare with the locked database record, never a caller's dirty model snapshot. */
    private function assertSealedRunContent(EodRun $run, array $attributes, bool $initialSeal): void
    {
        $persisted = EodRun::query()->where('run_id', $run->run_id)->lockForUpdate()->firstOrFail();
        $sealed = $persisted->sealed_at !== null || DB::table('eod_publications')->where('run_id', $run->run_id)->where('seal_state', 'SEALED')->exists();
        if (! $sealed) return;
        // Run publication_id/version select the operational outcome; sealed publication identity
        // lives on eod_publications. The active writer fence still protects these ledger updates.
        $protected = ['knowledge_cutoff_at', 'config_snapshot_id', 'config_snapshot_ref', 'config_hash',
            'seal_fingerprint', 'config_version', 'source_name', 'source_provider', 'promote_mode', 'publish_target',
            'correction_id', 'supersedes_run_id', 'corpus_admission_decision_id',
            'artifact_hash_profile', 'publication_semantic_profile',
            'price_product_code', 'price_product_version', 'factor_set_id', 'factor_set_hash',
            'source_file_hash', 'source_file_hash_algorithm', 'source_file_size_bytes', 'source_file_row_count',
            'observation_manifest_hash', 'semantic_observation_manifest_hash',
            'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash',
            'bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written',
            'sealed_at', 'sealed_by', 'seal_note'];
        $normalize = static function ($value, $cast) {
            if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d H:i:s');
            if ($value !== null && in_array($cast, ['integer','int'], true)) return (string) (int) $value;
            if ($value !== null && in_array($cast, ['float','double'], true)) return (string) (float) $value;
            return $value === null ? null : (string) $value;
        };
        $before = $persisted->getAttributes();
        $casts = $persisted->getCasts();
        foreach (array_merge($run->getDirty(), $attributes) as $field => $value) {
            if (! in_array($field, $protected, true) && strpos((string) $field, 'coverage_') !== 0) continue;
            // Publication seal precedes the first run seal record in the same lifecycle transaction.
            if ($initialSeal && $persisted->sealed_at === null && in_array($field, ['sealed_at','sealed_by','seal_note'], true)) continue;
            $proposed = clone $persisted;
            $proposed->setAttribute($field, $value);
            $after = $proposed->getAttributes();
            if ($normalize($before[$field] ?? null, $casts[$field] ?? null) !== $normalize($after[$field] ?? null, $casts[$field] ?? null)) {
                throw new \RuntimeException('SEALED_PUBLICATION_IMMUTABLE: protected run field '.$field);
            }
        }
    }

    /** One mutation boundary: actual ownership and complete proposed model state, no acquisition. */
    private function assertExistingWriteFence(EodRun $run, array $attributes = [], bool $initialSeal = false): void
    {
        (new RequestedDateWriterFence())->lockRun($run->trade_date_requested, $run);
        $this->assertRunAttributes($run, $attributes, $initialSeal);
    }

    public function appendEvent(EodRun $run, $stage, $eventType, $severity, $message, $reasonCode = null, array $payload = [])
    {
        return DB::transaction(function () use ($run, $stage, $eventType, $severity, $message, $reasonCode, $payload) {
            (new RequestedDateWriterFence())->lockRun($run->trade_date_requested, $run);
            if (in_array($run->request_mode, ['import_only','replay_verify'], true)
                && in_array($eventType, ['RUN_FINALIZED','PUBLICATION_PROMOTED','PUBLICATION_SWITCHED'], true)) {
                throw new \RuntimeException('RUN_OWNER_CONFLICT: isolated execution cannot assert publication finalization.');
            }
            return $this->insertEvent($run, $stage, $eventType, $severity, $message, $reasonCode, $payload);
        }, 3);
    }

    public function appendObservation(EodRun $target, $stage, $type, $severity, $message, $reason = null, array $payload = [])
    {
        $identity = RunWriterContext::observation($target, $type);
        return $this->insertEvent($target, $stage, $type, $severity, $message, $reason, $payload, $identity);
    }

    public function recordObservation(EodRun $target, string $role, $stage, $type, $severity, $message, $reason = null, array $payload = [])
    {
        return RunWriterContext::observe($role, $target, function () use ($target, $stage, $type, $severity, $message, $reason, $payload) {
            return $this->appendObservation($target, $stage, $type, $severity, $message, $reason, $payload);
        });
    }

    private function insertEvent(EodRun $run, $stage, $eventType, $severity, $message, $reasonCode, array $payload, array $identity = [])
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        return EodRunEvent::query()->create([
            'run_id' => $run->run_id, 'trade_date_requested' => $run->trade_date_requested,
            'event_time' => $now, 'stage' => $stage, 'event_type' => $eventType,
            'severity' => $this->normalizeSeverity($severity), 'reason_code' => $reasonCode,
            'message' => $this->truncateMessage($message),
            'event_payload_json' => empty($payload) ? null : json_encode($payload), 'created_at' => $now,
            'writer_kind' => $identity['kind'] ?? 'RUN_OWNER',
            'writer_actor_id' => $identity['actor_id'] ?? DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)->value('execution_id'),
            'writer_scope_key' => $identity['scope_key'] ?? ('owner|'.($run->request_mode ?: 'full_publish').'|'.$run->trade_date_requested),
            'writer_fencing_token' => $identity['fencing_token'] ?? (int) $run->run_id,
        ]);
    }

    public function failStage(EodRun $run, $stage, $reasonCode, $message, array $payload = [])
    {
        return DB::transaction(function () use ($run, $stage, $reasonCode, $message, $payload) {
        $this->assertExistingWriteFence($run, ['terminal_status'=>'FAILED', 'publishability_state'=>'NOT_READABLE']);
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
        DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)
            ->where('owner_run_id', $run->run_id)->whereNull('released_at')->update(['released_at' => $now]);

        return $run->fresh();
        });
    }

    public function holdStage(EodRun $run, $stage, $reasonCode, $message, $tradeDateEffective = null, array $payload = [])
    {
        return DB::transaction(function () use ($run, $stage, $reasonCode, $message, $tradeDateEffective, $payload) {
        $this->assertExistingWriteFence($run, ['terminal_status'=>'HELD', 'publishability_state'=>'NOT_READABLE']);
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
        DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)
            ->where('owner_run_id', $run->run_id)->whereNull('released_at')->update(['released_at' => $now]);

        return $run->fresh();
        });
    }

    public function completeImportOnly(EodRun $run, $stage, $reasonCode = 'IMPORT_ONLY_COMPLETED_NOT_PROMOTED', array $payload = [])
    {
        if ($run->request_mode !== 'import_only') throw new \RuntimeException('RUN_OWNER_CONFLICT: import terminal transition requires import-only scope.');
        return DB::transaction(function () use ($run, $stage, $reasonCode, $payload) {
        $this->assertExistingWriteFence($run, ['terminal_status'=>null, 'publishability_state'=>'NOT_READABLE', 'is_current_publication'=>0]);
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
        DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)
            ->where('owner_run_id', $run->run_id)->whereNull('released_at')->update(['released_at' => $now]);

        return $run->fresh();
        });
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
        return DB::transaction(function () use ($run, $telemetry) {
            $this->assertExistingWriteFence($run, $telemetry);
            foreach ($telemetry as $key => $value) {
                $run->{$key} = $value;
            }
            $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
            $run->save();
            return $run->fresh();
        });
    }

    public function storeHashes(EodRun $run, array $hashes)
    {
        return DB::transaction(function () use ($run, $hashes) {
            $this->assertExistingWriteFence($run, $hashes);
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
        });
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
        return DB::transaction(function () use ($run, $sealedBy, $sealNote) {
            $this->assertExistingWriteFence($run, [], true);
            $persisted = EodRun::query()->where('run_id', $run->run_id)->firstOrFail();
            $now = $persisted->sealed_at ?: Carbon::now(config('market_data.platform.timezone'));
            $this->assertRunAttributes($run, ['sealed_at'=>$now, 'sealed_by'=>$sealedBy, 'seal_note'=>$sealNote], true);
            $run->sealed_at = $now;
            $run->sealed_by = $sealedBy;
            $run->seal_note = $sealNote;
            $run->updated_at = $now;
            $run->save();
            return $run->fresh();
        });
    }

    public function syncCurrentPublicationMirror($tradeDate, $currentRunId, ?EodRun $actingOwner = null)
    {
        return DB::transaction(function () use ($tradeDate, $currentRunId, $actingOwner) {
        (new RequestedDateWriterFence())->lock($tradeDate, $actingOwner);
        $target = EodRun::query()->where('run_id', $currentRunId)->lockForUpdate()->first();
        if (! $target || (string) $target->trade_date_requested !== (string) $tradeDate) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: mirror target must belong to the acting owner date.');
        }
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
        });
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
        $this->recordObservation($blocked, 'conflict', $stage, 'RUN_OWNER_CONFLICT', 'WARN',
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

    private function issueExecutionCredential(EodRun $run): void
    {
        if (DB::transactionLevel() < 1 || ! $run->wasRecentlyCreated) {
            throw new \LogicException('RUN_OWNER_CONFLICT: credentials require fresh transactional acquisition.');
        }
        $secret = bin2hex(random_bytes(32)); $execution = bin2hex(random_bytes(16));
        $changed = DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)
            ->where('owner_run_id', $run->run_id)->whereNull('released_at')->update([
                'execution_id' => $execution, 'credential_hash' => hash('sha256', $secret),
            ]);
        if ($changed !== 1) throw new \RuntimeException('RUN_OWNER_CONFLICT: acquisition did not hold its date lease.');
        RunWriterContext::remember((int) $run->run_id, $execution, $secret, [
            'date' => (string) $run->trade_date_requested, 'source' => (string) $run->source, 'mode' => (string) $run->request_mode,
        ]);
    }

    private function setDateOwnerFence($requestedDate, int $runId): void
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $prior = DB::table('eod_run_owner_fences')->where('trade_date_requested', $requestedDate)->first();
        $ttl = (int) config('market_data.pipeline.active_run_stale_minutes', 1440);
        DB::table('eod_run_owner_fences')->where('trade_date_requested', $requestedDate)->update([
            'owner_run_id' => $runId,
            // The immutable, monotonically allocated owner_run_id is the fencing token.
            'acquired_at' => $prior && (int) $prior->owner_run_id === $runId && $prior->acquired_at ? $prior->acquired_at : $now,
            'heartbeat_at' => $now,
            'expires_at' => $ttl > 0 ? $now->copy()->addMinutes($ttl) : null,
            'released_at' => null,
            'updated_at' => $now,
        ]);
    }

    public function assertPublishCriticalOwner(EodRun $run): void
    {
        if (! $this->isPublishCriticalMode($run->request_mode)) {
            throw new \RuntimeException('RUN_OWNER_INVALID_MODE: non-publish run cannot enter publish-critical stage.');
        }
        DB::transaction(function () use ($run) {
            (new RequestedDateWriterFence())->lockRun($run->trade_date_requested, $run);
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
                    $this->recordObservation($run, 'conflict', $run->stage ?: 'INGEST_BARS', 'RUN_OWNER_AMBIGUOUS', 'ERROR',
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
            if ($publishCritical) {
                $lease = DB::table('eod_run_owner_fences')
                    ->where('trade_date_requested', $requestedDate)->first();
                if ($lease && (int) $lease->owner_run_id === (int) $staleRun->run_id
                    && $lease->released_at === null && $lease->expires_at !== null
                    && (string) $lease->expires_at > (string) $now) {
                    $this->recordObservation($staleRun, 'conflict', $staleRun->stage ?: 'INGEST_BARS',
                        'RUN_OWNER_CONFLICT', 'WARN',
                        'Stale run timestamp cannot displace a live requested-date lease.',
                        'RUN_LOCK_CONFLICT', [
                            'owner_run_id' => (int) $staleRun->run_id,
                            'heartbeat_at' => (string) $lease->heartbeat_at,
                            'expires_at' => (string) $lease->expires_at,
                            'decision' => 'BLOCKED',
                        ]);
                    return 'RUN_OWNER_CONFLICT: live lease held by run '.(int) $staleRun->run_id;
                }
            }
            $blockReason = $this->staleTakeoverBlockReason($staleRun);
            if ($blockReason !== null) {
                $this->recordObservation($staleRun, 'conflict', $staleRun->stage ?: 'INGEST_BARS', 'STALE_OWNER_TAKEOVER_BLOCKED', 'WARN',
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

        foreach ($staleRuns as $staleRun) $recoveredRunIds[] = (int) $staleRun->run_id;
        return null;
    }

    private function cancelVerifiedPredecessors(EodRun $owner, array $runIds): void
    {
        if ($runIds === []) return;
        (new RequestedDateWriterFence())->lockRun($owner->trade_date_requested, $owner);
        $requestedDate = $owner->trade_date_requested; $sourceMode = $owner->source; $requestMode = $owner->request_mode;
        $staleMinutes = (int) config('market_data.pipeline.active_run_stale_minutes', 1440);
        $now = Carbon::now(config('market_data.platform.timezone')); $cutoff = $now->copy()->subMinutes($staleMinutes);
        foreach ($runIds as $runId) {
            $staleRun = EodRun::where('run_id', $runId)->lockForUpdate()->firstOrFail();
            if ((string) $staleRun->trade_date_requested !== (string) $requestedDate
                || $this->staleTakeoverBlockReason($staleRun) !== null) throw new \RuntimeException('STALE_OWNER_TAKEOVER_BLOCKED: predecessor changed during recovery.');
            $previousState = (string) ($staleRun->lifecycle_state ?? '');
            $previousUpdatedAt = $staleRun->updated_at ? (string) $staleRun->updated_at : null;

            $this->recordObservation(
                $staleRun,
                'conflict',
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

        }
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

        // EodRun's array cast serializes this value exactly once; keep its original type here.

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
        return DB::transaction(function () use ($run, $finalState) {
            $this->assertExistingWriteFence($run, $finalState);
            $now = Carbon::now(config('market_data.platform.timezone'));
            // Audit the release while the acting token is still valid. Event, terminal row and
            // release commit together; none can survive a failure of the other writes.
            if (($finalState['lifecycle_state'] ?? null) === 'COMPLETED') {
                $this->appendEvent($run, $run->stage, 'RUN_OWNER_RELEASED', 'INFO',
                    'Publish-critical owner completed; historical fencing token retained.', null,
                    ['owner_run_id' => (int) $run->run_id, 'fencing_token' => (int) $run->run_id, 'released_at' => (string) $now]);
            }
            foreach ($finalState as $key => $value) {
                $run->{$key} = $value;
            }
            $run->finished_at = $now;
            $run->updated_at = $now;
            $run->save();
            if ($run->lifecycle_state === 'COMPLETED') {
                DB::table('eod_run_owner_fences')->where('trade_date_requested', $run->trade_date_requested)
                    ->where('owner_run_id', $run->run_id)->whereNull('released_at')->update(['released_at' => $now]);
            }
            return $run->fresh();
        });
    }
}
