<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Infrastructure\Persistence\MarketData\RunWriterContext;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

/** Results about historical processing; never an update to the historical run. */
final class PublicationReprocessObservationService
{
    private const FIELDS = ['publication_reprocess_state', 'publication_reprocess_republished_trade_date_count',
        'publication_reprocess_republished_trade_dates', 'publication_reprocess_candidate_trade_dates',
        'publication_reprocess_readable_correction_candidate_trade_dates', 'publication_reprocess_blocked_trade_dates',
        'publication_reprocess_failed_trade_dates', 'publication_reprocess_blocked_reason_code',
        'publication_reprocess_failure_reason_code', 'publication_reprocess_republication_mode',
        'publication_reprocess_correction_ids', 'publication_reprocess_correction_id'];

    public function record(EodRun $run, array $notes, string $type = 'PUBLICATION_REPROCESS_OBSERVED'): void
    {
        $notes = array_intersect_key($notes, array_flip(self::FIELDS));
        if ($notes === []) return;
        foreach ($notes as &$value) {
            if (is_array($value)) { $value = array_values(array_unique($value)); sort($value); $value = implode(',', $value); }
            if (! is_scalar($value) && $value !== null) throw new \RuntimeException('IMPACT_OBSERVATION_VALUE_INVALID');
            $value = $value === null ? '' : (string) $value;
        }
        unset($value);
        RunWriterContext::observe('impact', $run, function () use ($run, $notes, $type) {
            $payload = ['schema' => 'publication-reprocess-observation/v1', 'run_id' => (int) $run->run_id,
                'requested_date' => (string) $run->trade_date_requested, 'source_mode' => (string) $run->source,
                'notes_delta' => $notes];
            $hash = hash('sha256', RunInputCaptureRepository::canonicalJson($payload));
            foreach (DB::table('eod_run_events')->where('run_id', $run->run_id)->where('event_type', $type)->get() as $event) {
                $prior = json_decode($event->event_payload_json, true);
                if (($prior['content_sha256'] ?? null) === $hash) return;
            }
            $payload['content_sha256'] = $hash;
            (new EodRunRepository())->appendObservation($run, 'FINALIZE', $type, 'INFO',
                'Derived publication-reprocess result observed; historical run unchanged.', null, $payload);
        });
    }

    public function notesFor($run): array
    {
        if (is_array($run)) $run = (object) $run;
        if (! is_object($run) || empty($run->run_id)) return ['notes' => [], 'event_ids' => []];
        return $this->notesFromEvents($run, DB::table('eod_run_events')->where('run_id', $run->run_id)
            ->whereIn('event_type', ['PUBLICATION_REPROCESS_OBSERVED', 'IMPACT_PUBLICATION_REPROCESS_COMPLETED'])
            ->orderBy('event_id')->get());
    }

    public function notesFromEvents($run, iterable $events): array
    {
        if (is_array($run)) $run = (object) $run;
        $notes = []; $ids = [];
        foreach ($events as $event) {
            if (is_array($event)) $event = (object) $event;
            $payload = json_decode($event->event_payload_json ?? '{}', true);
            // Older native events remain historical evidence, not new observation deltas.
            if (($payload['schema'] ?? null) !== 'publication-reprocess-observation/v1') continue;
            $hash = $payload['content_sha256'] ?? ''; unset($payload['content_sha256']);
            if (! hash_equals(hash('sha256', RunInputCaptureRepository::canonicalJson($payload)), $hash)
                || ($payload['run_id'] ?? null) !== (int) $run->run_id
                || ($payload['requested_date'] ?? null) !== (string) $run->trade_date_requested
                || ($payload['source_mode'] ?? null) !== (string) $run->source
                || $event->writer_kind !== 'OBSERVATION_ONLY' || ! $event->writer_actor_id
                || $event->writer_scope_key !== 'impact|'.$run->trade_date_requested
                || (int) $event->writer_fencing_token < 1 || ! is_array($payload['notes_delta'] ?? null)
                || array_diff(array_keys($payload['notes_delta']), self::FIELDS) !== []) {
                throw new \RuntimeException('IMPACT_OBSERVATION_IDENTITY_INVALID');
            }
            foreach ($payload['notes_delta'] as $value) if (! is_string($value)) throw new \RuntimeException('IMPACT_OBSERVATION_VALUE_INVALID');
            $notes = array_replace($notes, $payload['notes_delta']); $ids[] = (int) $event->event_id;
        }
        return ['notes' => $notes, 'event_ids' => $ids];
    }
}
