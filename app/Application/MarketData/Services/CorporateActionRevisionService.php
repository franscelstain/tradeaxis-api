<?php

namespace App\Application\MarketData\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only owner for governed corporate-action revisions.
 *
 * Verification is evidence, not a price-series inference. Unknown lifecycle dates and terms stay
 * null/unknown. A correction always appends a revision and points at the superseded row.
 */
class CorporateActionRevisionService
{
    private const VERIFIED_STATES = ['AUTHORITATIVE_VERIFIED', 'MANUAL_VERIFIED'];
    private const ALLOWED_VERIFICATION_STATES = ['AUTHORITATIVE_VERIFIED', 'MANUAL_VERIFIED', 'PROVIDER_REPORTED', 'SYNTHETIC_CANDIDATE'];
    private const ALLOWED_LIFECYCLE_STATES = ['ANNOUNCED', 'EFFECTIVE', 'CANCELLED', 'UNKNOWN'];

    public function append(array $input): array
    {
        $this->assertFoundation();
        $normalized = $this->normalize($input);
        $this->assertValid($normalized);

        return DB::transaction(function () use ($normalized) {
            $latest = DB::table('md_corporate_action_revisions')
                ->where('event_uid', $normalized['event_uid'])
                ->orderByDesc('revision_number')
                ->lockForUpdate()
                ->first();

            if ($latest && $this->semanticallyEqual($latest, $normalized)) {
                return ['state' => 'IDEMPOTENT_EXISTING', 'revision' => (array) $latest];
            }

            $revisionNumber = $latest ? ((int) $latest->revision_number + 1) : 1;
            $now = $normalized['recorded_at'] ?: Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
            $id = DB::table('md_corporate_action_revisions')->insertGetId([
                'event_uid' => $normalized['event_uid'],
                'revision_number' => $revisionNumber,
                'listing_id' => $normalized['listing_id'],
                'action_type_code' => $normalized['action_type_code'],
                'lifecycle_state' => $normalized['lifecycle_state'],
                'verification_state' => $normalized['verification_state'],
                'ex_date' => $normalized['ex_date'],
                'cum_date' => $normalized['cum_date'],
                'record_date' => $normalized['record_date'],
                'payment_date' => $normalized['payment_date'],
                'terms_json' => $normalized['terms_json'],
                'source_observation_id' => $normalized['source_observation_id'],
                'effective_at' => $normalized['effective_at'],
                'recorded_at' => $now,
                'supersedes_revision_id' => $latest ? (int) $latest->corporate_action_revision_id : null,
            ]);

            $row = DB::table('md_corporate_action_revisions')->where('corporate_action_revision_id', $id)->first();

            return ['state' => 'APPENDED', 'revision' => (array) $row];
        });
    }

    public function terminalVerifiedForListing($listingId, $knownAt = null)
    {
        $query = DB::table('md_corporate_action_revisions as revision')
            ->leftJoin('md_corporate_action_revisions as newer', 'newer.supersedes_revision_id', '=', 'revision.corporate_action_revision_id')
            ->whereNull('newer.corporate_action_revision_id')
            ->where('revision.listing_id', (int) $listingId)
            ->whereIn('revision.verification_state', self::VERIFIED_STATES)
            ->where('revision.lifecycle_state', 'EFFECTIVE');

        if ($knownAt !== null && $knownAt !== '') {
            $query->where('revision.recorded_at', '<=', $knownAt);
        }

        return $query->orderBy('revision.ex_date')->orderBy('revision.corporate_action_revision_id')->get(['revision.*']);
    }

    private function normalize(array $input): array
    {
        $terms = $input['terms'] ?? null;
        if (is_string($terms)) {
            $decoded = json_decode($terms, true);
            if (! is_array($decoded) && trim($terms) !== '') {
                throw new \InvalidArgumentException('CORPORATE_ACTION_TERMS_JSON_INVALID.');
            }
            $terms = $decoded;
        }
        if ($terms !== null && ! is_array($terms)) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_TERMS_MUST_BE_OBJECT.');
        }
        if (is_array($terms)) {
            $terms = $this->sortRecursive($terms);
        }

        $eventUid = trim((string) ($input['event_uid'] ?? ''));
        if ($eventUid === '') {
            $identity = [
                'listing_id' => (int) ($input['listing_id'] ?? 0),
                'source_event_id' => trim((string) ($input['source_event_id'] ?? '')),
                'action_type_code' => strtoupper(trim((string) ($input['action_type_code'] ?? ''))),
            ];
            if ($identity['source_event_id'] === '') {
                throw new \InvalidArgumentException('CORPORATE_ACTION_EVENT_UID_OR_SOURCE_EVENT_ID_REQUIRED.');
            }
            $eventUid = hash('sha256', json_encode($identity, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return [
            'event_uid' => $eventUid,
            'listing_id' => (int) ($input['listing_id'] ?? 0),
            'action_type_code' => strtoupper(trim((string) ($input['action_type_code'] ?? ''))),
            'lifecycle_state' => strtoupper(trim((string) ($input['lifecycle_state'] ?? 'UNKNOWN'))),
            'verification_state' => strtoupper(trim((string) ($input['verification_state'] ?? 'PROVIDER_REPORTED'))),
            'ex_date' => $this->nullableDate($input['ex_date'] ?? null),
            'cum_date' => $this->nullableDate($input['cum_date'] ?? null),
            'record_date' => $this->nullableDate($input['record_date'] ?? null),
            'payment_date' => $this->nullableDate($input['payment_date'] ?? null),
            'terms' => $terms,
            'terms_json' => $terms === null ? null : json_encode($terms, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'source_observation_id' => ! empty($input['source_observation_id']) ? (int) $input['source_observation_id'] : null,
            'effective_at' => $input['effective_at'] ?? null,
            'recorded_at' => $input['recorded_at'] ?? null,
        ];
    }

    private function assertValid(array $row): void
    {
        if ($row['listing_id'] <= 0) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_LISTING_ID_REQUIRED.');
        }
        if ($row['action_type_code'] === '') {
            throw new \InvalidArgumentException('CORPORATE_ACTION_TYPE_REQUIRED.');
        }
        if (! in_array($row['verification_state'], self::ALLOWED_VERIFICATION_STATES, true)) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_VERIFICATION_STATE_INVALID.');
        }
        if (! in_array($row['lifecycle_state'], self::ALLOWED_LIFECYCLE_STATES, true)) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_LIFECYCLE_STATE_INVALID.');
        }

        if (in_array($row['verification_state'], self::VERIFIED_STATES, true) && $row['source_observation_id'] === null) {
            throw new \InvalidArgumentException('CORPORATE_ACTION_VERIFIED_SOURCE_OBSERVATION_REQUIRED.');
        }
        if ($row['verification_state'] === 'MANUAL_VERIFIED') {
            $manual = is_array($row['terms']) ? ($row['terms']['manual_evidence'] ?? null) : null;
            if (! is_array($manual)
                || trim((string) ($manual['reviewer'] ?? '')) === ''
                || trim((string) ($manual['evidence_ref'] ?? '')) === '') {
                throw new \InvalidArgumentException('CORPORATE_ACTION_MANUAL_VERIFICATION_EVIDENCE_REQUIRED.');
            }
        }
    }

    private function semanticallyEqual($latest, array $candidate): bool
    {
        foreach (['listing_id', 'action_type_code', 'lifecycle_state', 'verification_state', 'ex_date', 'cum_date', 'record_date', 'payment_date', 'source_observation_id', 'effective_at'] as $field) {
            if ((string) ($latest->{$field} ?? '') !== (string) ($candidate[$field] ?? '')) {
                return false;
            }
        }

        return (string) ($latest->terms_json ?? '') === (string) ($candidate['terms_json'] ?? '');
    }

    private function nullableDate($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $date = Carbon::parse((string) $value)->toDateString();

        return $date;
    }

    private function sortRecursive(array $value): array
    {
        foreach ($value as $key => $child) {
            if (is_array($child)) {
                $value[$key] = $this->sortRecursive($child);
            }
        }
        if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
            ksort($value, SORT_STRING);
        }

        return $value;
    }

    private function assertFoundation(): void
    {
        if (! Schema::hasTable('md_corporate_action_revisions')) {
            throw new \RuntimeException('CORPORATE_ACTION_REVISION_FOUNDATION_MISSING.');
        }
    }
}
