<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

/** Observed-condition response, persisted on the existing append-only event trail. */
final class MarketDataIncidentResponseService implements \App\Application\MarketData\Ports\IncidentResponsePolicy
{
    public const CLAIM_LIMIT = 'Observed conditions handled under the declared matrix only; no completeness, health, consumer consequence, solved-cause, incident closure, activation or release-readiness claim.';
    private const CONDITIONS = [
        'DATASET_HASH_MISMATCH' => 'integrity', 'FINALIZE_HASH_MISMATCH' => 'integrity',
        'FINALIZE_SEAL_FINGERPRINT_MISMATCH' => 'integrity', 'SEALED_PUBLICATION_IMMUTABLE' => 'integrity',
        'POINTER_ORPHAN_DETECTED' => 'integrity', 'POINTER_PUBLICATION_STATE_INVALID' => 'integrity',
        'RUN_PUBLICATION_MIRROR_MISMATCH' => 'integrity', 'CONFIG_SNAPSHOT_HASH_MISMATCH' => 'integrity',
        'HISTORY_MUTATION_ATTEMPT' => 'integrity', 'CONFIG_HASH_MISMATCH' => 'integrity',
        'SEAL_HASH_MISMATCH' => 'integrity', 'MIXED_PUBLICATION' => 'integrity',
        'RUN_SOURCE_AUTH_ERROR' => 'security/provenance', 'SOURCE_OBSERVATION_PROVENANCE_MISSING' => 'security/provenance',
        'SOURCE_OBSERVATION_IDENTITY_MISSING' => 'security/provenance',
        'SECRET_LEAKAGE' => 'security/provenance', 'UNAUTHENTICATED_PAYLOAD' => 'security/provenance',
        'MIXED_PRICE_BASIS' => 'semantic', 'UNVERIFIED_FACTOR' => 'semantic', 'ATR_RESEED' => 'semantic',
        'WRONG_IDENTITY' => 'semantic', 'WRONG_DATE' => 'semantic', 'WRONG_STATUS' => 'semantic',
        'RUN_SOURCE_TIMEOUT' => 'source/schema', 'RUN_SOURCE_RATE_LIMIT' => 'source/schema',
        'RUN_SOURCE_RESPONSE_CHANGED' => 'source/schema', 'RUN_SOURCE_MALFORMED_PAYLOAD' => 'source/schema',
        'RUN_SOURCE_NO_VALID_DATA' => 'source/schema', 'RUN_SOURCE_PARTIAL_COVERAGE' => 'coverage/quality',
        'COVERAGE_BELOW_THRESHOLD' => 'coverage/quality', 'DATE_LEVEL_QUALITY_CANDIDATE_CONFLICT' => 'integrity',
        'RUN_LOCK_CONFLICT' => 'scheduler/lock', 'RUN_OWNER_CONFLICT' => 'scheduler/lock',
        'RUN_REPLAY_BLOCKED' => 'proof/monitoring', 'RUN_EVIDENCE_EXPORT_FAILED' => 'proof/monitoring',
    ];

    /** Promote only a native typed-prefix failure from the hash/seal/finalize boundary.
     * Diagnostic messages and provider response bodies are never parsed by observeRun.
     */
    public static function nativeFailureIdentity(string $stage, \Throwable $error): ?string
    {
        if (! in_array($stage, ['HASH', 'SEAL', 'FINALIZE'], true)
            || ! preg_match('/^([A-Z][A-Z0-9_]+):/', $error->getMessage(), $match)
            || ! isset(self::CONDITIONS[$match[1]])) return null;
        return $match[1];
    }

    public function observeRun(int $runId): array
    {
        return \App\Infrastructure\Persistence\MarketData\RunWriterContext::observe('incident', EodRun::findOrFail($runId), function () use ($runId) {
            $run = EodRun::query()->where('run_id', $runId)->lockForUpdate()->firstOrFail();
            $config = DB::table('md_config_snapshots')->where('config_snapshot_id', $run->config_snapshot_id)->first();
            $configVerified = $config && hash_equals(hash('sha256', $config->resolved_config_json), (string) $config->config_hash)
                && hash_equals((string) $config->config_hash, (string) $run->config_hash)
                && (string) $config->snapshot_uid === (string) $run->config_snapshot_ref;
            $observations = [];
            $classes = [];
            $reasons = [];
            $unclassified = [];
            $development = MarketDataScope::fromConfig()->stateFor($run->trade_date_requested) !== 'OPERATIONAL';
            $freshnessBreach = false;
            foreach (DB::table('eod_run_events')->where('run_id', $runId)->orderBy('event_id')->get() as $event) {
                $payload = json_decode($event->event_payload_json ?? '{}', true);
                if ($event->event_type === 'FRESHNESS_MEASURED'
                    && ($payload['evaluation']['freshness_target'] ?? null) === 'BREACH') $freshnessBreach = true;
                if (! in_array($event->event_type, ['STAGE_FAILED', 'STAGE_HELD', 'OBSERVED_CONDITION'], true)) continue;
                if ((string) $event->trade_date_requested !== (string) $run->trade_date_requested) throw new \RuntimeException('INCIDENT_OBSERVATION_SCOPE_MISMATCH');
                // Supporting free text never becomes incident authority. A detector supplies
                // a condition identity; actual stage failures already carry a reason identity.
                $identities = array_values(array_unique(array_filter([
                    $event->reason_code, $payload['observed_condition'] ?? null,
                    $payload['exception_context']['reason_code'] ?? null,
                    $event->event_type === 'STAGE_FAILED' ? $run->source_final_reason_code : null,
                ])));
                [$verifiedObservationIds, $unverifiedObservationIds] = $this->observationProvenance($run, $event, $payload['source_observation_ids'] ?? []);
                if ($unverifiedObservationIds) $identities[] = 'SOURCE_OBSERVATION_PROVENANCE_MISSING';
                $matched = [];
                $recognized = false;
                foreach ($identities as $reason) {
                    if (! isset(self::CONDITIONS[$reason])) continue;
                    $recognized = true;
                    $class = self::CONDITIONS[$reason];
                    if ($development && ! in_array($class, ['integrity', 'security/provenance', 'semantic'], true)) continue;
                    $classes[$class] = true;
                    $reasons[$reason] = true;
                    $matched[] = $reason;
                }
                if (! $recognized && $identities !== []) $unclassified[] = [
                    'event_id' => (int) $event->event_id, 'reason_identities' => $identities,
                    'observed_at' => $event->event_time,
                ];
                if ($matched) $observations[] = ['event_id' => (int) $event->event_id, 'reason_identities' => $matched,
                    'observed_at' => $event->event_time, 'all_reason_identities' => $identities,
                    'source_observation_ids' => $verifiedObservationIds, 'unverified_source_observation_ids' => $unverifiedObservationIds];
            }
            $classNames = array_keys($classes); sort($classNames, SORT_STRING);
            $reasonNames = array_keys($reasons); sort($reasonNames, SORT_STRING);
            $severity = null;
            foreach ($classNames as $class) {
                $floor = in_array($class, ['integrity', 'security/provenance'], true) ? 3
                    : (in_array($class, ['semantic', 'proof/monitoring'], true) || $freshnessBreach ? 2 : 1);
                $severity = max($severity ?? 0, $floor);
            }
            $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', $run->trade_date_requested)->first();
            $observedPointer = $pointer ? ['trade_date' => (string) $pointer->trade_date,
                'publication_id' => (int) $pointer->publication_id, 'run_id' => (int) $pointer->run_id,
                'publication_version' => (int) $pointer->publication_version, 'sealed_at' => $pointer->sealed_at] : null;
            $payload = [
                'schema' => 'observed-incident-response/v3', 'run_id' => $runId, 'requested_date' => $run->trade_date_requested,
                'source_mode' => (string) $run->source,
                'publication_id' => $run->publication_id === null ? null : (int) $run->publication_id,
                'publication_version' => $run->publication_version === null ? null : (int) $run->publication_version,
                'observed_artifact' => $this->artifactIdentity($run->publication_id === null ? null : (int) $run->publication_id),
                // This is the observed raw pointer, not a claim that the pointer
                // is verified. A mismatched/orphan pointer is incident evidence.
                'observed_pointer' => $observedPointer,
                'observed_pointer_matches_artifact' => $this->pointerMatchesArtifact($observedPointer, (int) $run->run_id,
                    $run->publication_id === null ? null : (int) $run->publication_id,
                    $run->publication_version === null ? null : (int) $run->publication_version),
                'config_snapshot_id' => $run->config_snapshot_id, 'config_hash' => $run->config_hash,
                'config_identity_verified' => (bool) $configVerified,
                'activation_state' => MarketDataScope::fromConfig()->stateFor($run->trade_date_requested),
                'classes' => $classNames, 'reasons' => $reasonNames, 'observations' => $observations,
                'unclassified_observations' => $unclassified,
                'severity_floor' => [1 => 'MEDIUM', 2 => 'HIGH', 3 => 'CRITICAL'][$severity] ?? null,
                'severity_escalation' => 'UNRESOLVED_F018', 'incident_closure' => 'UNSUPPORTED_PENDING_F018',
                'state' => $unclassified ? 'EVIDENCE_INCOMPLETE_UNCLASSIFIED_OBSERVATION'
                    : (! $configVerified ? 'EVIDENCE_INCOMPLETE_CONFIGURATION'
                        : ($classes ? 'OPEN_OBSERVED_CONDITION' : 'NO_CLASSIFIED_OBSERVED_CONDITION')),
                'protection' => $this->protection($classNames, $reasonNames), 'claim_limit' => self::CLAIM_LIMIT,
                'operational_activation_proven' => false,
            ];
            if ($unclassified || ! $configVerified) $payload['protection']['block_release_claim'] = true;
            $hash = hash('sha256', RunInputCaptureRepository::canonicalJson($payload));
            foreach (DB::table('eod_run_events')->where('run_id', $runId)->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->get() as $event) {
                $prior = json_decode($event->event_payload_json, true);
                if (($prior['content_sha256'] ?? null) === $hash) return $prior;
            }
            $payload['content_sha256'] = $hash;
            (new EodRunRepository())->appendObservation($run, 'FINALIZE', 'INCIDENT_RESPONSE_RECORDED', $classes ? 'ERROR' : 'INFO',
                'Observed-condition response persisted; escalation and closure remain governed holds.', null, $payload);
            return $payload;
        });
    }

    /** Verify attribution, without requiring missing provenance to be present
     * before its critical containment response can be emitted.
     */
    private function observationProvenance(EodRun $run, $event, array $declaredIds): array
    {
        $bound = [];
        foreach ((new RunInputCaptureRepository())->forRun((int) $run->run_id) as $capture) {
            if ($capture['component_key'] !== 'source_observations') continue;
            $input = json_decode($capture['semantic_payload_json'], true);
            foreach ($input['rows'] ?? [] as $row) if (! empty($row['source_observation_id'])) $bound[(int) $row['source_observation_id']] = true;
        }
        if ($run->publication_id) foreach (DB::table('eod_bars_history')->where('publication_id', $run->publication_id)
            ->where('trade_date', $run->trade_date_requested)->pluck('source_observation_id') as $id) $bound[(int) $id] = true;
        $verified = []; $unverified = [];
        foreach (array_values(array_unique($declaredIds)) as $id) {
            $validIdentity = (is_int($id) || (is_string($id) && ctype_digit($id))) && (int) $id > 0;
            $observation = $validIdentity ? DB::table('md_source_observations')->where('source_observation_id', (int) $id)->first() : null;
            $inDate = $observation && ((string) $observation->requested_trade_date === (string) $run->trade_date_requested
                || DB::table('md_source_observation_rows')->where('source_observation_id', $id)->where('trade_date', $run->trade_date_requested)->exists());
            $owned = $observation && ((int) $observation->run_id === (int) $run->run_id || isset($bound[(int) $id]));
            if ($inDate && $owned && $observation->created_at && $observation->created_at <= $event->event_time) $verified[] = (int) $id;
            else $unverified[] = $id;
        }
        return [$verified, $unverified];
    }

    private function protection(array $classes, array $reasons): array
    {
        $integrity = in_array('integrity', $classes, true);
        $security = in_array('security/provenance', $classes, true);
        $semantic = in_array('semantic', $classes, true);
        return [
            'block_requested_read' => $integrity || $security || $semantic,
            'block_affected_evidence' => $security,
            'block_ordinary_retry' => $integrity || $security || $semantic,
            'block_new_acquisition' => $integrity || $semantic,
            'block_correction_baseline' => $integrity || $security,
            'block_release_claim' => $classes !== [],
            'gateway_integrity_ambiguous' => array_intersect($reasons, ['POINTER_ORPHAN_DETECTED', 'POINTER_PUBLICATION_STATE_INVALID', 'RUN_PUBLICATION_MIRROR_MISMATCH', 'MIXED_PUBLICATION']) !== [],
            'in_place_repair_authorized' => false, 'source_change_authorized' => false,
            'required_recovery' => $security ? 'CONTAIN_AND_NEW_SANITIZED_ARTIFACTS'
                : ($semantic ? 'IMMUTABLE_CORRECTION_REVISION' : ($integrity ? 'VERIFIED_INTEGRITY_RECOVERY' : 'EXISTING_BOUNDED_FENCED_WORKFLOW')),
        ];
    }

    /** The guard reads persisted evidence; callers cannot submit a severity or PASS decision. */
    public function assertActionAllowed(string $date, string $action, ?int $targetRunId = null): void
    {
        $field = ['consumer_read' => 'block_requested_read', 'evidence_export' => 'block_affected_evidence',
            'ordinary_retry' => 'block_ordinary_retry', 'release_claim' => 'block_release_claim',
            'new_acquisition' => 'block_new_acquisition',
            'correction_baseline' => 'block_correction_baseline',
            'prior_fallback' => 'block_requested_read'][$action] ?? null;
        if ($field === null) throw new \InvalidArgumentException('INCIDENT_ACTION_UNSUPPORTED');
        $this->assertSessionContainmentAllows($date, $action);
        foreach (DB::table('eod_run_events')->where('trade_date_requested', $date)
            ->where('event_type', 'INCIDENT_RESPONSE_RECORDED')->get() as $event) {
            $payload = json_decode($event->event_payload_json, true);
            $hash = $payload['content_sha256'] ?? ''; unset($payload['content_sha256']);
            $owner = DB::table('eod_runs')->where('run_id', $event->run_id)->first();
            $pointer = $payload['observed_pointer'] ?? null;
            $pointerContextValid = array_key_exists('observed_pointer', $payload)
                && ($pointer === null || (is_array($pointer) && ($pointer['trade_date'] ?? null) === $date
                    && (int) ($pointer['publication_id'] ?? 0) > 0 && (int) ($pointer['run_id'] ?? 0) > 0
                    && (int) ($pointer['publication_version'] ?? 0) > 0 && array_key_exists('sealed_at', $pointer)));
            if (! hash_equals(hash('sha256', RunInputCaptureRepository::canonicalJson($payload)), $hash)
                || ! in_array($payload['schema'] ?? null, ['observed-incident-response/v2', 'observed-incident-response/v3'], true) || ! $owner
                || (string) $owner->trade_date_requested !== $date
                || ($payload['source_mode'] ?? null) !== (string) $owner->source
                || ! array_key_exists('publication_id', $payload) || ! array_key_exists('publication_version', $payload)
                || ! $this->historicalArtifactMatches($payload, $owner)
                || ! $pointerContextValid
                || ($payload['observed_pointer_matches_artifact'] ?? null) !== $this->pointerMatchesArtifact($pointer,
                    (int) $event->run_id, $payload['publication_id'] ?? null, $payload['publication_version'] ?? null)
                || ($payload['requested_date'] ?? null) !== $date || (int) ($payload['run_id'] ?? 0) !== (int) $event->run_id) {
                throw new \RuntimeException('INCIDENT_EVIDENCE_IDENTITY_INVALID');
            }
            // A new immutable correction/sanitized artifact has its own run identity.
            // Integrity ambiguity remains date-scoped and is never bypassed this way.
            if ($targetRunId !== null && $targetRunId !== (int) $event->run_id
                && ! ($payload['protection']['gateway_integrity_ambiguous'] ?? true)
                && in_array($action, ['consumer_read', 'evidence_export'], true)) continue;
            if ($action === 'new_acquisition' && in_array('security/provenance', $payload['classes'], true)
                && DB::table('eod_runs')->where('run_id', $event->run_id)
                    ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])->exists()) {
                throw new \RuntimeException('INCIDENT_RESPONSE_BLOCKED: implicated active worker cannot reuse a security-contaminated attempt.');
            }
            if ($payload['protection'][$field] ?? true) throw new \RuntimeException('INCIDENT_RESPONSE_BLOCKED: '.$action.' for '.$date);
        }
    }

    /** Publication identity is immutable; a run's selected operational outcome is not. */
    private function artifactIdentity(?int $id): ?array
    {
        if ($id === null) return null;
        $publication = DB::table('eod_publications')->where('publication_id', $id)->first();
        if (! $publication) return null;
        return ['publication_id' => (int) $publication->publication_id, 'publication_version' => (int) $publication->publication_version,
            'run_id' => (int) $publication->run_id, 'trade_date' => (string) $publication->trade_date];
    }

    private function historicalArtifactMatches(array $payload, $owner): bool
    {
        $id = $payload['publication_id']; $version = $payload['publication_version'];
        if ($id !== null && (! is_int($id) || $id < 1 || ! is_int($version) || $version < 1)) return false;
        if ($id === null && $version !== null) return false;
        $artifact = $this->artifactIdentity($id);
        if (($payload['schema'] ?? null) === 'observed-incident-response/v3') {
            // Missing context is not a legacy record, and a rehashed selector cannot replace this tuple.
            return array_key_exists('observed_artifact', $payload) && $payload['observed_artifact'] === $artifact
                && ($id === null || ($artifact !== null && $artifact['publication_version'] === $version));
        }
        // Preserve v2 compatibility without guessing an unrelated historical outcome selection.
        return ($id === null && $version === null)
            || ($id === ($owner->publication_id === null ? null : (int) $owner->publication_id)
                && $version === ($owner->publication_version === null ? null : (int) $owner->publication_version))
            || ($artifact !== null && $artifact['run_id'] === (int) $owner->run_id
                && $artifact['trade_date'] === (string) $owner->trade_date_requested && $artifact['publication_version'] === $version);
    }

    /** A surfaced gateway incident can precede creation of any acquisition run.
     * Keep that fact date-scoped; never borrow the run/source of a corrupt pointer.
     */
    public function observeSessionAssessment(int $measurementId): array
    {
        $row = DB::table('md_freshness_measurements')->where('measurement_id', $measurementId)->first();
        $report = $row ? json_decode($row->measurement_json, true) : null;
        if (! $row || $row->record_type !== 'ASSESSMENT' || $row->run_id !== null || ! is_array($report)
            || ! hash_equals(hash('sha256', RunInputCaptureRepository::canonicalJson($report)), $row->content_sha256)
            || ($report['record_type'] ?? null) !== 'ASSESSMENT' || ($report['requested_date'] ?? null) !== $row->trade_date
            || ($report['measurement_scope'] ?? null) !== 'EXPECTED_SESSION_WITHOUT_RUN' || ($report['run_id'] ?? null) !== null
            || empty($report['gateway_error']) || ! array_key_exists('observed_pointer', $report)) {
            throw new \RuntimeException('INCIDENT_SESSION_ASSESSMENT_IDENTITY_INVALID');
        }
        return \App\Infrastructure\Persistence\MarketData\RunWriterContext::observeDate('session_incident', (string) $row->trade_date, null, function () use ($row, $report) {
        $payload = [
            'schema' => 'observed-session-incident/v1', 'requested_date' => $row->trade_date,
            'run_id' => null, 'source_mode' => null, 'publication_id' => null,
            'assessment_id' => (int) $row->measurement_id, 'assessment_sha256' => $row->content_sha256,
            'observed_at' => $row->recorded_at, 'observed_pointer' => $report['observed_pointer'],
            'classes' => ['integrity'], 'reasons' => ['GATEWAY_POINTER_UNVERIFIED'], 'severity_floor' => 'CRITICAL',
            'state' => 'OPEN_OBSERVED_CONDITION', 'protection' => $this->protection(['integrity'], ['POINTER_ORPHAN_DETECTED']),
            'severity_escalation' => 'UNRESOLVED_F018', 'incident_closure' => 'UNSUPPORTED_PENDING_F018',
            'claim_limit' => self::CLAIM_LIMIT, 'operational_activation_proven' => false,
        ];
        $json = RunInputCaptureRepository::canonicalJson($payload); $hash = hash('sha256', $json);
        $existing = DB::table('md_freshness_measurements')->where('content_sha256', $hash)->first();
        if ($existing) {
            if ($existing->record_type !== 'INCIDENT_RESPONSE' || $existing->measurement_json !== $json) {
                throw new \RuntimeException('INCIDENT_SESSION_ASSESSMENT_IDENTITY_INVALID');
            }
            return ['incident_measurement_id' => (int) $existing->measurement_id] + $payload;
        }
        $actor = \App\Infrastructure\Persistence\MarketData\RunWriterContext::dateObservation((string) $row->trade_date, null, 'INCIDENT_RESPONSE');
        $id = DB::table('md_freshness_measurements')->insertGetId([
            'trade_date' => $row->trade_date, 'run_id' => null, 'record_type' => 'INCIDENT_RESPONSE',
            'measurement_json' => $json, 'content_sha256' => $hash, 'recorded_at' => $row->recorded_at,
            'writer_actor_id' => $actor['actor_id'], 'writer_scope_key' => $actor['scope_key'],
            'writer_fencing_token' => $actor['fencing_token'],
        ], 'measurement_id');
        return ['incident_measurement_id' => (int) $id] + $payload;
        });
    }

    private function assertSessionContainmentAllows(string $date, string $action): void
    {
        foreach (DB::table('md_freshness_measurements')->where('trade_date', $date)
            ->where('record_type', 'INCIDENT_RESPONSE')->get() as $row) {
            $payload = json_decode($row->measurement_json, true);
            $assessment = is_array($payload) ? DB::table('md_freshness_measurements')->where('measurement_id', $payload['assessment_id'] ?? 0)->first() : null;
            $report = $assessment ? json_decode($assessment->measurement_json, true) : null;
            if (! is_array($payload) || ! hash_equals(hash('sha256', RunInputCaptureRepository::canonicalJson($payload)), $row->content_sha256)
                || ($payload['schema'] ?? null) !== 'observed-session-incident/v1' || ($payload['requested_date'] ?? null) !== $date
                || $row->run_id !== null || ($payload['run_id'] ?? null) !== null
                || ! array_key_exists('source_mode', $payload) || $payload['source_mode'] !== null
                || ! array_key_exists('publication_id', $payload) || $payload['publication_id'] !== null
                || ! $assessment || $assessment->record_type !== 'ASSESSMENT' || $assessment->trade_date !== $date || $assessment->run_id !== null
                || ! is_array($report) || ! hash_equals(hash('sha256', RunInputCaptureRepository::canonicalJson($report)), $assessment->content_sha256)
                || ($payload['assessment_sha256'] ?? null) !== $assessment->content_sha256
                || ($report['requested_date'] ?? null) !== $date || ($report['record_type'] ?? null) !== 'ASSESSMENT'
                || ($report['measurement_scope'] ?? null) !== 'EXPECTED_SESSION_WITHOUT_RUN' || empty($report['gateway_error'])
                || ! array_key_exists('observed_pointer', $report) || ($payload['observed_pointer'] ?? null) !== $report['observed_pointer']
                || ($payload['observed_at'] ?? null) !== $assessment->recorded_at || $row->recorded_at !== $assessment->recorded_at) {
                throw new \RuntimeException('INCIDENT_SESSION_ASSESSMENT_IDENTITY_INVALID');
            }
            // This is current gateway re-verification, NOT incident closure. Missing
            // pointer plus current mirrors is ambiguity, not a safe empty date. A new
            // bounded acquisition can proceed only after that ambiguity is absent.
            $publications = new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository();
            $raw = $publications->findRawCurrentPublicationStateForTradeDate($date);
            $ambiguous = $raw ? $publications->determineCurrentIntegrityViolationReasons($raw) !== []
                : DB::table('eod_publications')->where('trade_date', $date)->where('is_current', 1)->exists()
                    || DB::table('eod_runs')->where('trade_date_requested', $date)->where('is_current_publication', 1)->exists();
            if ($raw && ! $ambiguous) {
                try { $publications->assertPublicationManifestHashValid((int) $raw->publication_id); }
                catch (\Throwable $error) { $ambiguous = true; }
            }
            if ($ambiguous && $action !== 'evidence_export') throw new \RuntimeException('INCIDENT_RESPONSE_BLOCKED: '.$action.' for '.$date);
        }
    }

    private function pointerMatchesArtifact($pointer, int $runId, ?int $publicationId, ?int $version): bool
    {
        return is_array($pointer) && $publicationId !== null && $version !== null
            && ($pointer['run_id'] ?? null) === $runId && ($pointer['publication_id'] ?? null) === $publicationId
            && ($pointer['publication_version'] ?? null) === $version;
    }

    public function requestClosure(): void
    {
        throw new \RuntimeException('INCIDENT_CLOSURE_AUTHORITY_UNRESOLVED_F018');
    }
}
