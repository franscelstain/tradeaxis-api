<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataScope;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketCalendarRepository;
use Illuminate\Support\Facades\DB;

/** Read-only operational diagnosis. Persisted run facts govern; event messages never override them. */
class MarketDataOperationalStatusService
{
    public function forDate(string $date, ?int $runId = null): array
    {
        MarketDataScope::fromConfig()->assertRequestedDate($date);
        $run = DB::table('eod_runs')->where('trade_date_requested', $date)
            ->when($runId !== null, static function ($q) use ($runId) { $q->where('run_id', $runId); })
            ->orderByDesc('run_id')->first();
        $events = $run ? DB::table('eod_run_events')->where('run_id', $run->run_id)->orderBy('event_id')->get()->all() : [];
        $defects = [];
        $reasons = [];
        $stages = [];
        foreach ($events as $event) {
            if ($event->reason_code) $reasons[$event->reason_code] = true;
            if (in_array($event->event_type, ['STAGE_FAILED', 'STAGE_HELD', 'SEAL_BLOCKED'], true)) $stages[$event->stage] = true;
        }
        if (! $run) $defects[] = 'RUN_OUTCOME_MISSING';
        if ($events === []) $defects[] = 'RUN_EVENT_TRAIL_MISSING';
        $terminal = $run->terminal_status ?? null;
        if (! in_array($terminal, ['SUCCESS', 'HELD', 'FAILED', 'SKIPPED', 'BLOCKED'], true)) $defects[] = 'TERMINAL_OUTCOME_UNPROVEN';
        if ($run && in_array($terminal, ['HELD', 'FAILED'], true)) {
            $coverageReason = (string) ($run->coverage_reason_code ?? '');
            if ((string) ($run->coverage_gate_state ?? '') === 'FAIL' && $coverageReason !== '') {
                foreach ($events as $event) if ((string) $event->reason_code === $coverageReason) $stages[$event->stage] = true;
            }
        }
        $sourceReason = $run->source_final_reason_code ?? null;
        if ($sourceReason) $reasons[$sourceReason] = true;
        $counts = [];
        foreach (['bars_rows_written', 'indicators_rows_written', 'eligibility_rows_written', 'invalid_bar_count', 'invalid_indicator_count', 'warning_count', 'hard_reject_count'] as $field) {
            $counts[$field] = $run->{$field} ?? null;
            // D-MD-B19-A001-003 preserves the persisted warning_count, including NULL, because
            // no warning population is defined. Expose the limitation without inventing a count.
            if ($field === 'warning_count' && $counts[$field] === null) continue;
            if ($counts[$field] === null || ! is_numeric($counts[$field]) || $counts[$field] < 0) $defects[] = 'UNAVAILABLE:'.$field;
        }
        $hashes = [];
        foreach (['bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash'] as $field) {
            $hashes[$field] = $run->{$field} ?? null;
            if (! preg_match('/^[a-f0-9]{64}$/Di', (string) $hashes[$field])) $defects[] = 'UNAVAILABLE:'.$field;
        }
        $config = $run && $run->config_snapshot_id ? DB::table('md_config_snapshots')->where('config_snapshot_id', $run->config_snapshot_id)->first() : null;
        $configVerified = $config && hash_equals(hash('sha256', $config->resolved_config_json), (string) $config->config_hash)
            && hash_equals((string) $config->config_hash, (string) $run->config_hash)
            && (string) $config->snapshot_uid === (string) $run->config_snapshot_ref;
        if (! $configVerified) $defects[] = 'CONFIG_IDENTITY_UNPROVEN';
        if (! $run || ! $run->sealed_at) $defects[] = 'SEAL_PRESENCE_UNPROVEN';
        $publications = new EodPublicationRepository();
        $publication = null;
        try { $publication = $publications->resolveCurrentReadablePublicationForTradeDate($date); }
        catch (\Throwable $e) { $defects[] = 'CONSUMER_PUBLICATION_UNVERIFIED'; }
        if (! $publication) $defects[] = 'REQUESTED_DATE_NOT_READABLE';
        if (! $run || (string) $run->trade_date_effective !== $date) $defects[] = 'REQUESTED_EFFECTIVE_DATE_UNPROVEN';
        if ($run && $run->final_reason_code) $reasons[$run->final_reason_code] = true;
        $expected = (new MarketCalendarRepository())->latestExpectedSession();
        $assessedAt = \Carbon\Carbon::now('Asia/Jakarta');
        $frontiers = (new MarketDataFreshnessEvidenceService())->observedFrontiers($assessedAt);
        return [
            'requested_date' => $date, 'run_id' => $run ? (int) $run->run_id : null,
            'terminal_status' => $terminal, 'effective_date' => $run->trade_date_effective ?? null,
            'publishability_state' => $run->publishability_state ?? null,
            'source_final_reason_code' => $sourceReason,
            'coverage_gate_state' => $run->coverage_gate_state ?? null,
            'counts' => $counts, 'hashes' => $hashes, 'sealed_at' => $run->sealed_at ?? null,
            'warning_count_provenance' => 'PERSISTED_EOD_RUN; NULL_PRESERVED_UNDER_D-MD-B19-A001-003; WARNING_POPULATION_UNDEFINED',
            'configuration_binding_corpus' => (new MarketDataConfigurationCorpusService())->report(),
            'config_snapshot_id' => $run->config_snapshot_id ?? null, 'config_hash' => $run->config_hash ?? null,
            'config_verified' => (bool) $configVerified, 'event_count' => count($events),
            'events' => $events, 'failed_or_held_stages' => array_keys($stages), 'reason_codes' => array_keys($reasons),
            'publication_id' => $publication ? (int) $publication->publication_id : null,
            'latest_expected_trade_date' => $expected['latest_expected_trade_date'],
            'current_session_state' => $expected['current_session_state'],
            'evidence_as_of' => $assessedAt->toIso8601String(),
            'latest_acquired_trade_date' => $frontiers['latest_acquired_trade_date'],
            'latest_canonicalized_trade_date' => $frontiers['latest_canonicalized_trade_date'],
            'latest_readable_trade_date' => $frontiers['latest_readable_trade_date'],
            'frontier_provenance' => $frontiers['frontier_provenance'],
            'activation_state' => MarketDataScope::fromConfig()->stateFor($date),
            'operationally_healthy' => $defects === [] && $terminal === 'SUCCESS'
                && $publication && (int) $publication->run_id === (int) $run->run_id,
            'observability_defects' => array_values(array_unique($defects)),
            'authority' => 'eod_runs; eod_run_events supporting; verified config snapshot and consumer publication gateway',
        ];
    }
}
