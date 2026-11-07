<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Models\EodRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Append expected-session evidence even when no worker/run was ever created. */
final class MarketDataSessionFreshnessService
{
    public function measure(string $date, ?int $runId = null): array
    {
        MarketDataScope::fromConfig()->assertRequestedDate($date);
        // Monitoring coordination gives no publication ownership or write authority.
        // Zero wait means competing assessment is an explicit refusal, never silent loss.
        if (DB::connection()->getDriverName() !== 'mysql') throw new \RuntimeException('FRESHNESS_MONITOR_COORDINATION_REQUIRES_MARIADB');
        $lock = 'md-freshness:'.substr(hash('sha256', DB::connection()->getDatabaseName().'|'.$date), 0, 48);
        $locked = DB::selectOne('SELECT GET_LOCK(?, 0) AS acquired', [$lock]);
        if ((int) $locked->acquired !== 1) throw new \RuntimeException('FRESHNESS_MEASUREMENT_LOCK_CONFLICT');
        try {
            return \App\Infrastructure\Persistence\MarketData\RunWriterContext::observeDate('session', $date, null, function () use ($date, $runId) {
                $now = Carbon::now('Asia/Jakarta');
                $calendar = new MarketCalendarRepository();
                $context = $calendar->assertCompletedRegularSession($date, $now->toDateTimeString());
                if ($runId !== null) {
                    $run = EodRun::query()->where('run_id', $runId)->where('trade_date_requested', $date)->first();
                    if (! $run) throw new \RuntimeException('FRESHNESS_REQUEST_RUN_SCOPE_MISMATCH');
                } else {
                    [$current, $gatewayError] = (new MarketDataFreshnessEvidenceService())->observeGateway($date);
                    $run = $current ? EodRun::query()->find($current->run_id) : null;
                    if (! $run && EodRun::query()->where('trade_date_requested', $date)->exists()) {
                        throw new \RuntimeException('FRESHNESS_RUN_SELECTION_REQUIRED: no verified current owner; supply the exact run_id.');
                    }
                }
                if ($run) {
                    $report = (new MarketDataFreshnessEvidenceService())->assessRun((int) $run->run_id);
                    $report['measurement_scope'] = 'EXACT_RUN_AND_EXPECTED_SESSION';
                } else {
                    // The monitoring config is recorded as monitoring config, never
                    // fabricated as an acquisition, observation or publication identity.
                    $snapshot = (new MarketDataConfigSnapshotRepository())->resolveForRun($date);
                    $frontiers = (new MarketDataFreshnessEvidenceService())->observedFrontiers($now);
                    $expected = (new MarketDataFreshnessEvidenceService())->expectedAssessmentHorizon($date, $now);
                    $report = $frontiers + [
                        'schema' => 'freshness-observation/v1', 'measurement_scope' => 'EXPECTED_SESSION_WITHOUT_RUN',
                        'requested_date' => $date, 'run_id' => null, 'acquisition_run_id' => null, 'publication_id' => null, 'gateway_readable' => false,
                        'gateway_error' => $gatewayError ?? null, 'assessed_at' => $now->toIso8601String(),
                        'activation_state' => MarketDataScope::fromConfig()->stateFor($date),
                        'monitoring_config_snapshot_id' => (int) $snapshot['config_snapshot_id'], 'monitoring_config_hash' => $snapshot['config_hash'],
                        'calendar_revision_id' => $context['calendar_revision_id'], 'calendar_source_ref' => $context['source_ref'],
                        'session_completed_at' => $context['completed_at'],
                        'latest_expected_trade_date' => $expected['latest_expected_trade_date'],
                        'is_latest_expected_target' => $date === $expected['latest_expected_trade_date'],
                        'expected_horizon_provenance' => $expected,
                        'milestones' => array_fill_keys(['first_acquisition_attempt', 'immutable_observation_completion',
                            'valid_canonical_completion', 'candidate_seal', 'active_publication_readability', 'consumer_gateway_verification'], null),
                        'elapsed_seconds_from_session_completion' => array_fill_keys(['first_acquisition_attempt', 'immutable_observation_completion',
                            'valid_canonical_completion', 'candidate_seal', 'active_publication_readability', 'consumer_gateway_verification'], null),
                        'source_observation_ids' => [], 'milestone_event_ids' => [],
                        'operational_activation_proven' => false, 'operational_slo_compliance' => 'NOT_CLAIMED',
                        'evaluation' => (new MarketDataFreshnessEvaluator())->evaluateFromGovernedCalendar([
                            'requested_date' => $date, 'as_of' => $now->toIso8601String(), 'publication_verified' => false,
                            'gateway_verified_readable_at' => null, 'acquisition_outcomes' => [],
                        ]),
                        'unavailable_evidence' => ['RUN_NOT_CREATED', 'ACQUISITION_AND_PUBLICATION_MILESTONES_UNAVAILABLE'],
                        'claim_limit' => MarketDataFreshnessEvidenceService::CLAIM_LIMIT,
                    ];
                }
                $report['observed_pointer'] = ($pointer = DB::table('eod_current_publication_pointer')->where('trade_date', $date)->first()) ? (array) $pointer : null;
                $id = $this->append($date, $run ? (int) $run->run_id : null, 'ASSESSMENT', $report, $now);
                $this->alerts($date, $run ? (int) $run->run_id : null, $report, $now);
                if (! $run && ! empty($report['gateway_error'])) (new MarketDataIncidentResponseService())->observeSessionAssessment($id);
                return ['measurement_id' => $id] + $report;
            });
        } finally { DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lock]); }
    }

    private function alerts(string $date, ?int $runId, array $report, Carbon $now): void
    {
        $active = [];
        foreach (DB::table('md_freshness_measurements')->where('trade_date', $date)->whereIn('record_type', ['ALERT_OPENED', 'ALERT_ESCALATED', 'ALERT_RECOVERED'])->orderBy('measurement_id')->get() as $row) {
            $payload = json_decode($row->measurement_json, true);
            if (! is_array($payload) || ! hash_equals(hash('sha256', RunInputCaptureRepository::canonicalJson($payload)), $row->content_sha256)
                || ($payload['requested_date'] ?? null) !== $date || ($payload['record_type'] ?? null) !== $row->record_type) {
                throw new \RuntimeException('FRESHNESS_ALERT_EVIDENCE_IDENTITY_INVALID');
            }
            if ($row->record_type === 'ALERT_RECOVERED') unset($active[$payload['dedup_key']]);
            else $active[$payload['dedup_key']] = $payload;
        }
        $root = ! empty($report['gateway_error']) ? 'GATEWAY_INTEGRITY' : 'REQUESTED_DATE_UNREADABLE';
        $severity = ! empty($report['gateway_error']) ? 'CRITICAL' : null;
        if (empty($report['gateway_error']) && ($report['evaluation']['assessment'] ?? null) === 'CONDITIONAL_EVALUATION' && ! $report['gateway_readable']) {
            $severity = ($report['evaluation']['freshness_target'] ?? null) === 'BREACH' ? 'CRITICAL' : 'WARNING';
        }
        $key = hash('sha256', ($runId ?? 0).'|'.$date.'|'.$root);
        $alert = ['dedup_key' => $key, 'root_cause' => $root, 'alert_severity' => $severity,
            'requested_date' => $date, 'run_id' => $runId, 'source_observation_ids' => $report['source_observation_ids'],
            'observed_at' => $now->toIso8601String(), 'conditional_mechanism_only' => true];
        if ($severity && ! isset($active[$key])) $this->append($date, $runId, 'ALERT_OPENED', $alert, $now);
        elseif ($severity === 'CRITICAL' && ($active[$key]['alert_severity'] ?? null) === 'WARNING') $this->append($date, $runId, 'ALERT_ESCALATED', $alert, $now);
        if ($report['gateway_readable']) foreach ($active as $old) $this->append($date, $runId, 'ALERT_RECOVERED', [
            'dedup_key' => $old['dedup_key'], 'requested_date' => $date, 'run_id' => $runId,
            'publication_id' => $report['publication_id'], 'gateway_verified_at' => $report['assessed_at'],
        ], $now);
    }

    private function append(string $date, ?int $runId, string $type, array $payload, Carbon $now): int
    {
        $payload['record_type'] = $type;
        $json = RunInputCaptureRepository::canonicalJson($payload); $hash = hash('sha256', $json);
        $existing = DB::table('md_freshness_measurements')->where('content_sha256', $hash)->first();
        if ($existing) {
            if ($existing->measurement_json !== $json || $existing->trade_date !== $date || $existing->record_type !== $type) {
                throw new \RuntimeException('FRESHNESS_MEASUREMENT_IDENTITY_CONFLICT');
            }
            return (int) $existing->measurement_id;
        }
        $actor = \App\Infrastructure\Persistence\MarketData\RunWriterContext::dateObservation($date, $runId, $type);
        return (int) DB::table('md_freshness_measurements')->insertGetId([
            'trade_date' => $date, 'run_id' => $runId, 'record_type' => $type, 'measurement_json' => $json,
            'content_sha256' => $hash, 'recorded_at' => $now->toDateTimeString(),
            'writer_actor_id' => $actor['actor_id'], 'writer_scope_key' => $actor['scope_key'],
            'writer_fencing_token' => $actor['fencing_token'],
        ], 'measurement_id');
    }
}
