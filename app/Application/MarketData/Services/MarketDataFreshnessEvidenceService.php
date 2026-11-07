<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Persist observed measurements, never reconstruct an unobserved historical gateway check. */
final class MarketDataFreshnessEvidenceService
{
    public const CLAIM_LIMIT = 'Declared timeliness of observed publications only; not price verification, platform correctness, absence of unobserved breaches, operational activation or SLO compliance.';

    public function assessRun(int $runId): array
    {
        return \App\Infrastructure\Persistence\MarketData\RunWriterContext::observe('freshness', EodRun::findOrFail($runId), function () use ($runId) {
            $run = EodRun::query()->where('run_id', $runId)->lockForUpdate()->firstOrFail();
            $this->assertRunConfiguration($run, 'FRESHNESS_CONFIG_IDENTITY_UNVERIFIED');
            $now = Carbon::now('Asia/Jakarta');
            $date = $run->trade_date_requested;
            $events = DB::table('eod_run_events')->where('run_id', $runId)->orderBy('event_id')->get();
            [$acquisitionRun, $lineageRunIds, $lineageEventIds] = $this->acquisitionLineage($run);
            $acquisitionEvents = (int) $acquisitionRun->run_id === $runId ? $events
                : DB::table('eod_run_events')->where('run_id', $acquisitionRun->run_id)->orderBy('event_id')->get();
            $milestones = array_fill_keys(['first_acquisition_attempt', 'immutable_observation_completion',
                'valid_canonical_completion', 'candidate_seal', 'active_publication_readability', 'consumer_gateway_verification'], null);
            $bindings = [];
            $completionObservationIds = [];
            foreach ($acquisitionEvents as $event) {
                $key = null;
                if ($event->event_type === 'ACQUISITION_STARTED') $key = 'first_acquisition_attempt';
                if ($event->event_type === 'OBSERVATIONS_CAPTURED') $key = 'immutable_observation_completion';
                if ($event->event_type === 'STAGE_COMPLETED' && $event->stage === 'INGEST_BARS') $key = 'valid_canonical_completion';
                if ($key !== null && $milestones[$key] === null) {
                    $milestones[$key] = $event->event_time;
                    $bindings[$key] = (int) $event->event_id;
                    if ($key === 'immutable_observation_completion') {
                        $completionPayload = json_decode($event->event_payload_json ?? '{}', true);
                        $completionObservationIds = array_values(array_unique(array_map('intval', $completionPayload['source_observation_ids'] ?? [])));
                        sort($completionObservationIds, SORT_NUMERIC);
                    }
                }
            }
            // Immutable producer captures bind the observation population to this run.
            $observationIds = [];
            $captureIds = [];
            foreach ((new RunInputCaptureRepository())->forRun((int) $acquisitionRun->run_id) as $capture) {
                if ($capture['component_key'] !== 'source_observations' || $capture['stage_code'] !== 'INGEST_BARS') continue;
                $payload = json_decode($capture['semantic_payload_json'], true);
                if (($payload['selection_context']['operation'] ?? null) !== 'ingest-source-rows/v1') continue;
                $captureIds[] = (int) $capture['input_capture_id'];
                foreach ($payload['rows'] as $row) {
                    if (! empty($row['source_observation_id'])) $observationIds[(int) $row['source_observation_id']] = true;
                }
            }
            $observationIds = array_keys($observationIds);
            sort($observationIds, SORT_NUMERIC);
            if ($completionObservationIds && $completionObservationIds !== $observationIds) {
                throw new \RuntimeException('FRESHNESS_OBSERVATION_PROVENANCE_CONFLICT: completion requires the corresponding immutable producer capture population.');
            }
            if ($milestones['immutable_observation_completion'] !== null && $completionObservationIds === []) {
                $milestones['immutable_observation_completion'] = null;
                unset($bindings['immutable_observation_completion']);
            }
            $publication = null;
            $gatewayError = null;
            $candidate = $run->publication_id ? DB::table('eod_publications')->where('publication_id', $run->publication_id)
                ->where('run_id', $runId)->where('trade_date', $date)->first() : null;
            if ($candidate && $candidate->seal_state === 'SEALED' && $candidate->sealed_at
                && (string) $candidate->sealed_at === (string) $run->sealed_at) $milestones['candidate_seal'] = $candidate->sealed_at;
            [$publication, $gatewayError] = $this->observeGateway($date);
            if ($publication) {
                $milestones['consumer_gateway_verification'] = $now->toDateTimeString();
                // A finalization event binds a successful pointer resolution to its exact
                // publication. A first later diagnostic observation cannot establish when
                // readability originally began, and must not manufacture historical lag.
                $publicationEvents = (int) $publication->run_id === $runId ? $events
                    : DB::table('eod_run_events')->where('run_id', $publication->run_id)->orderBy('event_id')->get();
                $milestones['active_publication_readability'] = $this->firstGatewayObservation($publicationEvents, (int) $publication->publication_id);
            }
            $calendar = new MarketCalendarRepository();
            $context = $calendar->assertCompletedRegularSession($date, $now->toDateTimeString());
            $expected = $this->expectedAssessmentHorizon($date, $now);
            $acquisitionConfig = json_decode(DB::table('md_config_snapshots')->where('config_snapshot_id', $acquisitionRun->config_snapshot_id)->value('resolved_config_json'), true);
            $sourceProvider = $acquisitionRun->source === 'api' ? strtolower((string) data_get($acquisitionConfig, 'resolved_config.source.api.provider', '')) : null;
            [$outcomes, $acquisitionEvidence] = $this->acquisitionOutcomes($now->toDateTimeString(), (string) $acquisitionRun->source, $sourceProvider);
            $evaluation = (new MarketDataFreshnessEvaluator())->evaluateFromGovernedCalendar([
                'requested_date' => $date, 'as_of' => $now->toIso8601String(),
                'gateway_verified_readable_at' => $publication && $milestones['active_publication_readability']
                    ? Carbon::parse($milestones['active_publication_readability'], 'Asia/Jakarta')->toIso8601String() : null,
                'publication_verified' => (bool) $publication, 'acquisition_outcomes' => $outcomes,
            ]);
            if ($publication && $milestones['active_publication_readability'] === null
                && $evaluation['assessment'] !== 'NOT_APPLICABLE') {
                $evaluation = ['assessment' => 'EVIDENCE_INCOMPLETE', 'freshness_target' => null,
                    'expected_session_lag' => null, 'reason' => 'FIRST_READABILITY_NOT_RETAINED'];
            }
            $elapsed = [];
            foreach ($milestones as $key => $time) {
                $elapsed[$key] = $time === null ? null : Carbon::parse($context['completed_at'], 'Asia/Jakarta')
                    ->diffInSeconds(Carbon::parse($time, 'Asia/Jakarta'), false);
            }
            $frontiers = $this->observedFrontiers($now);
            $payload = [
                'schema' => 'freshness-observation/v1', 'run_id' => $runId, 'requested_date' => $date,
                'acquisition_run_id' => (int) $acquisitionRun->run_id,
                'acquisition_config_snapshot_id' => (int) $acquisitionRun->config_snapshot_id,
                'acquisition_config_hash' => $acquisitionRun->config_hash,
                'seed_lineage_run_ids' => $lineageRunIds, 'seed_lineage_created_event_ids' => $lineageEventIds,
                'assessed_at' => $now->toIso8601String(), 'activation_state' => MarketDataScope::fromConfig()->stateFor($date),
                'operational_activation_proven' => false, 'operational_slo_compliance' => 'NOT_CLAIMED',
                'config_snapshot_id' => $run->config_snapshot_id, 'config_hash' => $run->config_hash,
                'calendar_revision_id' => $context['calendar_revision_id'], 'calendar_source_ref' => $context['source_ref'],
                'session_completed_at' => $context['completed_at'], 'milestones' => $milestones,
                'elapsed_seconds_from_session_completion' => $elapsed, 'milestone_event_ids' => $bindings,
                'source_observation_ids' => $observationIds, 'source_capture_ids' => $captureIds,
                'acquisition_outcome_evidence' => $acquisitionEvidence,
                'acquisition_stream_identity' => ['source_mode' => $acquisitionRun->source, 'provider' => $sourceProvider],
                'publication_id' => $publication ? (int) $publication->publication_id : null,
                'publication_run_id' => $publication ? (int) $publication->run_id : null,
                'gateway_error' => $gatewayError, 'gateway_readable' => (bool) $publication,
                'latest_expected_trade_date' => $expected['latest_expected_trade_date'],
                'is_latest_expected_target' => $date === $expected['latest_expected_trade_date'],
                'expected_horizon_provenance' => $expected,
                'latest_acquired_trade_date' => $frontiers['latest_acquired_trade_date'],
                'latest_canonicalized_trade_date' => $frontiers['latest_canonicalized_trade_date'],
                'latest_readable_trade_date' => $frontiers['latest_readable_trade_date'],
                'frontier_provenance' => $frontiers['frontier_provenance'],
                'evaluation' => $evaluation, 'claim_limit' => self::CLAIM_LIMIT,
                'measurement_limit' => 'Requested-run measurement; first readability requires retained successful finalization identity. Missing milestones remain unavailable; no global compliance claim.',
            ];
            $this->append($run, 'FRESHNESS_MEASURED', $payload);
            $this->updateAlerts($run, $payload);
            $this->recordSourceEvaluation($run, $payload);
            return $payload;
        });
    }

    private function firstGatewayObservation($events, int $publicationId): ?string
    {
        foreach ($events as $event) {
            if ($event->event_type === 'RUN_FINALIZED') {
                $final = json_decode($event->event_payload_json, true);
                if ((int) ($final['current_publication_id'] ?? 0) === $publicationId
                    && ($final['manifest']['readiness_state'] ?? null) === 'READABLE') return $event->event_time;
            }
        }
        return null;
    }

    /** Follow only the recorded acquisition seed, never supersession or latest-by-date. */
    private function acquisitionLineage(EodRun $run): array
    {
        $current = $run; $seen = []; $runIds = []; $eventIds = [];
        $inputs = new RunInputCaptureRepository();
        while (true) {
            $id = (int) $current->run_id;
            if (isset($seen[$id])) throw new \RuntimeException('FRESHNESS_SEED_CYCLE');
            $seen[$id] = true; $runIds[] = $id;
            $created = DB::table('eod_run_events')->where('run_id', $id)->where('event_type', 'RUN_CREATED')->orderBy('event_id')->get();
            if ($created->count() > 1) throw new \RuntimeException('FRESHNESS_SEED_CREATION_AMBIGUOUS');
            $seed = $inputs->resolveSeedRunId($id);
            if ($seed === null) {
                if ((string) $current->request_mode === 'promote') throw new \RuntimeException('FRESHNESS_SEED_LINK_MISSING');
                break;
            }
            if ($created->isEmpty() || (string) $created[0]->trade_date_requested !== (string) $current->trade_date_requested) {
                throw new \RuntimeException('FRESHNESS_SEED_EVENT_SCOPE_MISMATCH');
            }
            $eventIds[] = (int) $created[0]->event_id;
            $parent = EodRun::query()->find($seed);
            if (! $parent) throw new \RuntimeException('FRESHNESS_SEED_RUN_MISSING');
            if (isset($seen[$seed])) throw new \RuntimeException('FRESHNESS_SEED_CYCLE');
            $this->assertRunConfiguration($parent, 'FRESHNESS_SEED_CONFIG_IDENTITY_UNVERIFIED', true);
            foreach (['trade_date_requested', 'source', 'artifact_hash_profile'] as $field) {
                if ((string) $current->{$field} !== (string) $parent->{$field}) {
                    throw new \RuntimeException('FRESHNESS_SEED_SCOPE_MISMATCH: '.$field);
                }
            }
            foreach (['observation_manifest_hash'] as $field) {
                if (! preg_match('/^[a-f0-9]{64}$/D', (string) $current->{$field})
                    || ! hash_equals((string) $current->{$field}, (string) $parent->{$field})) {
                    throw new \RuntimeException('FRESHNESS_SEED_MANIFEST_MISMATCH: '.$field);
                }
            }
            if ((string) $current->artifact_hash_profile === ArtifactSemanticHashService::PROFILE_V2
                && (! preg_match('/^[a-f0-9]{64}$/D', (string) $current->semantic_observation_manifest_hash)
                    || ! hash_equals((string) $current->semantic_observation_manifest_hash, (string) $parent->semantic_observation_manifest_hash))) {
                throw new \RuntimeException('FRESHNESS_SEED_SEMANTIC_MANIFEST_MISMATCH');
            }
            $current = $parent;
        }
        return [$current, $runIds, $eventIds];
    }

    private function assertRunConfiguration(EodRun $run, string $reason, bool $requireProducerBinding = false): void
    {
        $snapshot = DB::table('md_config_snapshots')->where('config_snapshot_id', $run->config_snapshot_id)->first();
        if (! $snapshot || ! hash_equals(hash('sha256', $snapshot->resolved_config_json), (string) $snapshot->config_hash)
            || ! hash_equals((string) $snapshot->config_hash, (string) $run->config_hash)
            || (string) $snapshot->snapshot_uid !== (string) $run->config_snapshot_ref) {
            throw new \RuntimeException($reason);
        }
        if (! $requireProducerBinding) return;
        $matches = 0;
        foreach ((new RunInputCaptureRepository())->forRun((int) $run->run_id) as $capture) {
            if ($capture['stage_code'] !== 'RUN_CONTEXT' || $capture['component_key'] !== 'run_config') continue;
            $payload = json_decode($capture['semantic_payload_json'], true);
            $selection = $payload['selection_context'] ?? [];
            $row = $payload['rows'][0] ?? [];
            if (($selection['operation'] ?? null) !== 'owning-run-config/v1'
                || ($selection['requested_date'] ?? null) !== (string) $run->trade_date_requested
                || ($selection['source'] ?? null) !== (string) $run->source
                || ($selection['request_mode'] ?? null) !== $run->request_mode
                || (int) ($row['config_snapshot_id'] ?? 0) !== (int) $run->config_snapshot_id
                || ($row['config_hash'] ?? null) !== $run->config_hash
                || ($row['snapshot_uid'] ?? null) !== $run->config_snapshot_ref) throw new \RuntimeException($reason);
            $matches++;
        }
        if ($matches !== 1) throw new \RuntimeException($reason);
    }

    private function acquisitionOutcomes(string $knownAt, string $sourceMode, ?string $provider): array
    {
        $outcomes = []; $evidence = [];
        foreach (DB::table('eod_run_events as event')->join('eod_runs as source_run', 'source_run.run_id', '=', 'event.run_id')
            ->where('source_run.source', $sourceMode)->where('event.stage', 'INGEST_BARS')->where('event.event_time', '<=', $knownAt)
            ->whereIn('event.event_type', ['STAGE_COMPLETED', 'STAGE_FAILED'])->orderBy('event.event_id')->select('event.*')->get() as $event) {
            $payload = json_decode($event->event_payload_json ?? '{}', true);
            $source = $payload['source_acquisition'] ?? $payload['exception_context'] ?? [];
            $raw = $source['source_final_status'] ?? null;
            $observedProvider = $source['provider'] ?? $payload['provider'] ?? null;
            if ($sourceMode === 'api' && $observedProvider !== null && strtolower((string) $observedProvider) !== $provider) continue;
            $providerVerified = $sourceMode !== 'api' || ($provider !== '' && $observedProvider !== null);
            // PARTIAL, missing, and contradictory status do not become SUCCESS from
            // positive bar counts or FAILED from an unrelated downstream run outcome.
            $outcome = ! $providerVerified ? null : ($raw === 'SUCCESS' ? 'SUCCESS' : (in_array($raw, ['FAILED', 'SYSTEMIC_FAILED'], true) ? 'FAILED' : null));
            $outcomes[$event->trade_date_requested] = $outcome;
            $evidence[$event->trade_date_requested] = ['outcome' => $outcome, 'raw_source_final_status' => $raw,
                'run_id' => (int) $event->run_id, 'event_id' => (int) $event->event_id, 'event_time' => $event->event_time,
                'source_mode' => $sourceMode, 'provider' => $observedProvider, 'source_identity_verified' => $providerVerified];
        }
        return [$outcomes, $evidence];
    }

    public function observeGateway(string $date): array
    {
        try {
            $publication = (new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($date);
            $pointer = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first();
            if (! $publication && $pointer) {
                return [null, 'FRESHNESS_GATEWAY_POINTER_UNVERIFIED: date='.$date.' publication_id='.$pointer->publication_id.' run_id='.$pointer->run_id];
            }
            return [$publication, null];
        } catch (\Throwable $error) { return [null, $error->getMessage()]; }
    }

    public function expectedAssessmentHorizon(string $date, Carbon $asOf): array
    {
        try {
            $sessions = (new MarketCalendarRepository())->completedExpectedSessionsBetween($date, $asOf->toDateString(), $asOf->toDateTimeString());
            $latest = $sessions ? $sessions[count($sessions) - 1]['trade_date'] : null;
            return ['latest_expected_trade_date' => $latest, 'assessment_horizon_verified' => true,
                'calendar_revision_ids' => array_column($sessions, 'calendar_revision_id', 'trade_date'), 'error' => null];
        } catch (\Throwable $error) {
            return ['latest_expected_trade_date' => null, 'assessment_horizon_verified' => false,
                'calendar_revision_ids' => [], 'error' => $error->getMessage()];
        }
    }

    /** Current observed rows known by the assessment clock, never historical reconstruction. */
    public function observedFrontiers(Carbon $asOf): array
    {
        $knownAt = $asOf->toDateTimeString(); $date = $asOf->toDateString();
        $acquired = DB::table('md_source_observation_rows as normalized')
            ->join('md_source_observations as observation', 'observation.source_observation_id', '=', 'normalized.source_observation_id')
            ->where('normalized.trade_date', '<=', $date)->where('observation.acquired_at', '<=', $knownAt)
            ->where('normalized.created_at', '<=', $knownAt)->max('normalized.trade_date');
        $canonical = DB::table('eod_bars')->where('trade_date', '<=', $date)->where('created_at', '<=', $knownAt)->max('trade_date');
        $readable = null;
        $dates = DB::table('eod_current_publication_pointer as pointer')
            ->join('eod_publications as publication', 'publication.publication_id', '=', 'pointer.publication_id')
            ->where('pointer.trade_date', '<=', $date)->where('pointer.updated_at', '<=', $knownAt)
            ->where('publication.created_at', '<=', $knownAt)->where('publication.sealed_at', '<=', $knownAt)
            ->orderByDesc('pointer.trade_date')->pluck('pointer.trade_date');
        foreach ($dates as $candidateDate) {
            try { if ((new EodPublicationRepository())->resolveCurrentReadablePublicationForTradeDate($candidateDate)) { $readable = $candidateDate; break; } }
            catch (\Throwable $ignored) { /* Invalid identity never becomes a readable frontier. */ }
        }
        return ['latest_acquired_trade_date' => $acquired, 'latest_canonicalized_trade_date' => $canonical,
            'latest_readable_trade_date' => $readable,
            'frontier_provenance' => 'Current retained rows with acquisition/creation/update/seal facts known by assessment; missing knowledge timestamps are unavailable. This is not archival as-known replay.'];
    }

    private function updateAlerts(EodRun $run, array $measurement): void
    {
        $root = $measurement['gateway_error'] ? 'GATEWAY_INTEGRITY' : 'REQUESTED_DATE_UNREADABLE';
        $severity = $measurement['gateway_error'] ? 'CRITICAL' : null;
        if (! $measurement['gateway_error'] && $measurement['evaluation']['assessment'] === 'CONDITIONAL_EVALUATION' && ! $measurement['gateway_readable']) {
            $severity = ($measurement['evaluation']['freshness_target'] === 'BREACH') ? 'CRITICAL' : 'WARNING';
        }
        $key = hash('sha256', $run->run_id.'|'.$run->trade_date_requested.'|'.$root);
        $alerts = DB::table('eod_run_events')->where('run_id', $run->run_id)
            ->whereIn('event_type', ['FRESHNESS_ALERT_OPENED', 'FRESHNESS_ALERT_ESCALATED', 'FRESHNESS_ALERT_RECOVERED'])->orderBy('event_id')->get();
        $active = [];
        foreach ($alerts as $event) {
            $payload = json_decode($event->event_payload_json, true);
            if ($event->event_type !== 'FRESHNESS_ALERT_RECOVERED') $active[$payload['dedup_key']] = $payload;
            else unset($active[$payload['dedup_key']]);
        }
        if ($severity && ! isset($active[$key])) $this->append($run, 'FRESHNESS_ALERT_OPENED', [
            'dedup_key' => $key, 'root_cause' => $root, 'alert_severity' => $severity,
            'requested_date' => $run->trade_date_requested, 'source_observation_ids' => $measurement['source_observation_ids'],
            'measurement_at' => $measurement['assessed_at'], 'conditional_mechanism_only' => true,
        ]);
        elseif ($severity === 'CRITICAL' && ($active[$key]['alert_severity'] ?? null) === 'WARNING') {
            $this->append($run, 'FRESHNESS_ALERT_ESCALATED', [
                'dedup_key' => $key, 'root_cause' => $root, 'alert_severity' => 'CRITICAL',
                'requested_date' => $run->trade_date_requested, 'source_observation_ids' => $measurement['source_observation_ids'],
                'measurement_at' => $measurement['assessed_at'], 'conditional_mechanism_only' => true,
            ]);
        }
        // A successful job, terminal status, or a later date alone cannot recover an alert.
        if ($measurement['gateway_readable']) foreach ($active as $alert) $this->append($run, 'FRESHNESS_ALERT_RECOVERED', [
            'dedup_key' => $alert['dedup_key'], 'publication_id' => $measurement['publication_id'],
            'requested_date' => $run->trade_date_requested, 'gateway_verified_at' => $measurement['assessed_at'],
        ]);
    }

    private function recordSourceEvaluation(EodRun $run, array $measurement): void
    {
        $evaluation = $measurement['evaluation'];
        if (($evaluation['source_evaluation'] ?? null) !== 'OPEN_FUTURE_SOURCE_EVALUATION') return;
        $dates = $evaluation['failed_expected_session_dates'];
        $provider = $measurement['acquisition_stream_identity']['provider'];
        $identity = hash('sha256', $run->source.'|'.$provider.'|'.implode('|', $dates));
        foreach (DB::table('eod_run_events')->where('event_type', 'SOURCE_CAPABILITY_EVALUATION_OPENED')->orderBy('event_id')->get() as $event) {
            $prior = json_decode($event->event_payload_json, true);
            if (($prior['evaluation_identity'] ?? null) === $identity) return;
        }
        $this->append($run, 'SOURCE_CAPABILITY_EVALUATION_OPENED', [
            'evaluation_identity' => $identity, 'source_mode' => $run->source, 'provider' => $provider,
            'failed_expected_session_dates' => $dates,
            'acquisition_outcome_evidence' => array_intersect_key($measurement['acquisition_outcome_evidence'], array_fill_keys($dates, true)),
            'calendar_revision_ids' => array_intersect_key($evaluation['calendar_revision_ids'] ?? [], array_fill_keys($dates, true)),
            'source_capability_evidence' => 'OBSERVED_FAILURE_ACROSS_FULL_FIVE_SESSION_HORIZON',
            'evaluation_state' => 'OPEN_FOR_FUTURE_SOURCE_REVIEW', 'source_change_authorized' => false,
            'external_source_review_executed' => false,
            'conditional_mechanism_only' => true, 'operational_activation_proven' => false,
            'opened_at' => $measurement['assessed_at'],
        ]);
    }

    private function append(EodRun $run, string $type, array $payload): void
    {
        $payload['content_sha256'] = hash('sha256', RunInputCaptureRepository::canonicalJson($payload));
        (new EodRunRepository())->appendObservation($run, 'FINALIZE', $type, 'INFO',
            'Persisted conditional freshness evidence; no activation or compliance claim.', null, $payload);
    }
}
