<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\Ports\AuthoritativeDocumentEvidenceVerifier;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthoritativeCorporateActionTermsService
{
    const MANIFEST_SCHEMA = 'market-data-authoritative-corporate-action-terms/v1';
    const TERMS_SCHEMA = 'corporate-action-terms/v1';
    const SOURCE_ADAPTER_VERSION = 'authoritative-corporate-action-terms-v1';

    private $sourceObservations;
    private $documentEvidence;

    public function __construct(
        SourceObservationRepository $sourceObservations,
        AuthoritativeDocumentEvidenceVerifier $documentEvidence
    )
    {
        $this->sourceObservations = $sourceObservations;
        $this->documentEvidence = $documentEvidence;
    }

    public function process($manifestPath, $apply = false)
    {
        $manifest = $this->readManifest($manifestPath);
        $prepared = $this->validateManifest($manifest);

        if (! $apply) {
            return $this->summarize($prepared, false);
        }

        // Network I/O must finish before the database transaction starts. In particular, do not
        // hold an InnoDB next-key lock for up to the HTTPS timeout while proving document bytes.
        // The transaction below resolves every revision again under lock, so a concurrent insert
        // between verification and persistence still becomes an exact no-op or a conflict.
        foreach ($prepared['events'] as $event) {
            if ($event['operation'] === 'INSERT') {
                $this->documentEvidence->verify($event['source']);
            }
        }

        return DB::transaction(function () use ($prepared) {
            $inserted = 0;
            $unchanged = 0;
            $sourceObservationsInserted = 0;
            $recordedAt = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();

            foreach ($prepared['events'] as $event) {
                $existing = $this->findRevision($event, true);
                if ($existing) {
                    $this->assertExistingRevisionMatches($existing, $event);
                    $unchanged++;
                    continue;
                }

                $capture = $this->sourceObservations->capture([
                    'requested_trade_date' => $event['revision']['ex_date'],
                    'source_mode' => 'authority_document',
                    'source_name' => 'KSEI',
                    'provider' => 'KSEI',
                    'provider_symbol' => $event['ticker_code'],
                    'sanitized_request_identity' => $event['source']['document_url'],
                    'content_type' => $event['source']['content_type'],
                    // KSEI publishes a dated document but the source does not supply a time of day.
                    // Keeping the timestamp NULL is the required unknown-preservation behavior.
                    'source_timestamp' => null,
                    'acquired_at' => $recordedAt,
                    'provider_schema_version' => 'ksei-corporate-action-document/v1',
                    'adapter_version' => self::SOURCE_ADAPTER_VERSION,
                    'payload' => $event['source_payload'],
                ]);
                $accepted = $this->sourceObservations->recordOutcome(
                    $capture,
                    'ACCEPTED',
                    'AUTHORITATIVE_TERMS_VALIDATED',
                    ['acquired_at' => $recordedAt]
                );

                DB::table('md_corporate_action_revisions')->insert(array_merge($event['revision'], [
                    'source_observation_id' => (int) $accepted['source_observation_id'],
                    'recorded_at' => $recordedAt,
                ]));

                $inserted++;
                $sourceObservationsInserted += 2;
            }

            return [
                'scope_id' => $prepared['scope_id'],
                'scope_entry_count' => count($prepared['events']),
                'inserted_revision_count' => $inserted,
                'unchanged_revision_count' => $unchanged,
                'source_observation_insert_count' => $sourceObservationsInserted,
                'applied' => true,
            ];
        });
    }

    private function readManifest($manifestPath)
    {
        if (! is_string($manifestPath) || $manifestPath === '' || ! is_file($manifestPath)) {
            throw new \RuntimeException('STAGE_6_MANIFEST_NOT_FOUND: manifest must point to an existing JSON file.');
        }

        $contents = file_get_contents($manifestPath);
        if ($contents === false) {
            throw new \RuntimeException('STAGE_6_MANIFEST_UNREADABLE: manifest could not be read.');
        }

        $decoded = json_decode($contents, true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('STAGE_6_MANIFEST_INVALID_JSON: '.json_last_error_msg());
        }

        return $decoded;
    }

    private function validateManifest(array $manifest)
    {
        $this->assertExactKeys($manifest, [
            'schema_version', 'scope_id', 'scope_statement', 'record_only', 'event_count', 'events',
        ], 'manifest');

        if (($manifest['schema_version'] ?? null) !== self::MANIFEST_SCHEMA) {
            throw new \RuntimeException('STAGE_6_MANIFEST_SCHEMA_UNSUPPORTED: schema_version must be '.self::MANIFEST_SCHEMA.'.');
        }
        if (! is_string($manifest['scope_id']) || ! preg_match('/^[a-z0-9][a-z0-9._-]{2,63}$/', $manifest['scope_id'])) {
            throw new \RuntimeException('STAGE_6_SCOPE_ID_INVALID: scope_id must be a stable lowercase identifier.');
        }
        if (! is_string($manifest['scope_statement']) || trim($manifest['scope_statement']) === '') {
            throw new \RuntimeException('STAGE_6_SCOPE_UNDECLARED: scope_statement is required.');
        }
        if ($manifest['record_only'] !== true) {
            throw new \RuntimeException('STAGE_6_SCOPE_ESCAPE_FORBIDDEN: record_only must be true; Stage 6 cannot apply terms to a series.');
        }
        if (! is_array($manifest['events']) || count($manifest['events']) === 0) {
            throw new \RuntimeException('STAGE_6_SCOPE_EMPTY: events must contain the declared scope.');
        }
        if (! is_int($manifest['event_count']) || $manifest['event_count'] !== count($manifest['events'])) {
            throw new \RuntimeException('STAGE_6_SCOPE_COUNT_MISMATCH: event_count must equal the number of events.');
        }

        $events = [];
        $seen = [];
        foreach ($manifest['events'] as $index => $entry) {
            if (! is_array($entry)) {
                throw new \RuntimeException('STAGE_6_EVENT_INVALID: events['.$index.'] must be an object.');
            }

            $event = $this->validateEvent($entry, $manifest['scope_id'], $index);
            $identity = $event['revision']['event_uid'].'|'.$event['revision']['revision_number'];
            if (isset($seen[$identity])) {
                throw new \RuntimeException('STAGE_6_DUPLICATE_REVISION: duplicate event_uid/revision_number in manifest.');
            }
            $seen[$identity] = true;

            $existing = $this->findRevision($event, false);
            if ($existing) {
                $this->assertExistingRevisionMatches($existing, $event);
                $event['operation'] = 'UNCHANGED';
            } else {
                $event['operation'] = 'INSERT';
            }

            $events[] = $event;
        }

        return [
            'scope_id' => $manifest['scope_id'],
            'events' => $events,
        ];
    }

    private function validateEvent(array $entry, $scopeId, $index)
    {
        $path = 'events['.$index.']';
        $this->assertExactKeys($entry, [
            'event_uid', 'revision_number', 'supersedes_revision_number', 'ticker_code', 'isin',
            'exchange_code', 'market_segment', 'action_type_code', 'lifecycle_state',
            'verification_state', 'announcement_at', 'cum_date', 'ex_date', 'record_date',
            'distribution_date', 'effective_date', 'ratio_from', 'ratio_to', 'old_nominal_value',
            'new_nominal_value', 'currency_code', 'source',
        ], $path);

        foreach (['ticker_code', 'isin', 'exchange_code', 'market_segment', 'action_type_code', 'lifecycle_state', 'verification_state', 'currency_code'] as $field) {
            if (! is_string($entry[$field]) || $entry[$field] === '') {
                throw new \RuntimeException('STAGE_6_REQUIRED_VALUE_MISSING: '.$path.'.'.$field.' is required.');
            }
        }

        if ($entry['ticker_code'] !== strtoupper($entry['ticker_code']) || ! preg_match('/^[A-Z0-9]{4,10}$/', $entry['ticker_code'])) {
            throw new \RuntimeException('STAGE_6_TICKER_INVALID: '.$path.'.ticker_code must be an uppercase exchange symbol.');
        }
        if (! preg_match('/^[A-Z]{2}[A-Z0-9]{10}$/', $entry['isin'])) {
            throw new \RuntimeException('STAGE_6_ISIN_INVALID: '.$path.'.isin must be a 12-character ISIN.');
        }
        if ($entry['exchange_code'] !== 'IDX' || $entry['market_segment'] !== 'REGULAR') {
            throw new \RuntimeException('STAGE_6_SCOPE_ESCAPE_FORBIDDEN: Stage 6 manifest is limited to IDX Regular Market listings.');
        }
        if ($entry['action_type_code'] !== 'STOCK_SPLIT') {
            throw new \RuntimeException('STAGE_6_ACTION_TYPE_OUT_OF_SCOPE: declared Stage 6 scope contains only STOCK_SPLIT.');
        }
        if ($entry['lifecycle_state'] !== 'EFFECTIVE') {
            throw new \RuntimeException('STAGE_6_LIFECYCLE_INVALID: completed historical stock splits must be recorded as EFFECTIVE.');
        }
        if ($entry['verification_state'] !== 'AUTHORITATIVE_VERIFIED') {
            throw new \RuntimeException('STAGE_6_VERIFICATION_INVALID: authoritative scope requires AUTHORITATIVE_VERIFIED.');
        }
        if ($entry['revision_number'] !== 1 || $entry['supersedes_revision_number'] !== null) {
            throw new \RuntimeException('STAGE_6_REVISION_LINEAGE_INVALID: initial manifest entries must be revision 1 with no superseded revision.');
        }
        if ($entry['announcement_at'] !== null && ! $this->isIsoDateTime($entry['announcement_at'])) {
            throw new \RuntimeException('STAGE_6_ANNOUNCEMENT_INVALID: unknown announcement time must stay NULL or use YYYY-MM-DD HH:MM:SS.');
        }

        foreach (['cum_date', 'ex_date', 'record_date', 'distribution_date', 'effective_date'] as $dateField) {
            if (! $this->isIsoDate($entry[$dateField])) {
                throw new \RuntimeException('STAGE_6_DATE_INVALID: '.$path.'.'.$dateField.' must use YYYY-MM-DD.');
            }
        }
        if (! ($entry['cum_date'] < $entry['ex_date']
            && $entry['ex_date'] <= $entry['record_date']
            && $entry['record_date'] < $entry['distribution_date'])) {
            throw new \RuntimeException('STAGE_6_DATE_ORDER_INVALID: cum < ex <= record < distribution is required.');
        }
        if ($entry['effective_date'] !== $entry['ex_date']) {
            throw new \RuntimeException('STAGE_6_EFFECTIVE_DATE_INVALID: STOCK_SPLIT effective_date must equal its authoritative ex_date.');
        }

        foreach (['ratio_from', 'ratio_to', 'old_nominal_value', 'new_nominal_value'] as $integerField) {
            if (! is_int($entry[$integerField]) || $entry[$integerField] <= 0) {
                throw new \RuntimeException('STAGE_6_TERMS_INVALID: '.$path.'.'.$integerField.' must be a positive integer.');
            }
        }
        if ($entry['currency_code'] !== 'IDR') {
            throw new \RuntimeException('STAGE_6_CURRENCY_INVALID: nominal values must use the source currency IDR.');
        }
        if ($entry['old_nominal_value'] * $entry['ratio_from'] !== $entry['new_nominal_value'] * $entry['ratio_to']) {
            throw new \RuntimeException('STAGE_6_TERMS_CONFLICT: share ratio and old/new nominal values disagree.');
        }

        $source = $this->validateSource($entry['source'], $entry, $path.'.source');
        $expectedEventUid = hash('sha256', 'corporate-action|KSEI|'.$entry['isin'].'|'.$source['document_number']);
        if (! is_string($entry['event_uid']) || ! hash_equals($expectedEventUid, $entry['event_uid'])) {
            throw new \RuntimeException('STAGE_6_EVENT_UID_INVALID: event_uid must be content-addressed from KSEI, ISIN, and document number.');
        }

        $listing = $this->resolveListing($entry);
        $this->assertActionTypeRegistered($entry['action_type_code']);

        $terms = [
            'schema_version' => self::TERMS_SCHEMA,
            'scope_id' => $scopeId,
            'ticker_code' => $entry['ticker_code'],
            'isin' => $entry['isin'],
            'announcement_at' => $entry['announcement_at'],
            'effective_date' => $entry['effective_date'],
            'distribution_date' => $entry['distribution_date'],
            'ratio' => [
                'from' => $entry['ratio_from'],
                'to' => $entry['ratio_to'],
            ],
            'nominal_value' => [
                'old' => $entry['old_nominal_value'],
                'new' => $entry['new_nominal_value'],
                'currency_code' => $entry['currency_code'],
            ],
            'source_document' => $source,
        ];
        $termsJson = $this->canonicalJson($terms);
        $sourcePayload = $this->canonicalJson([
            'event_uid' => $entry['event_uid'],
            'revision_number' => $entry['revision_number'],
            'listing_id' => (int) $listing->listing_id,
            'action_type_code' => $entry['action_type_code'],
            'verification_state' => $entry['verification_state'],
            'cum_date' => $entry['cum_date'],
            'ex_date' => $entry['ex_date'],
            'record_date' => $entry['record_date'],
            'distribution_date' => $entry['distribution_date'],
            'terms' => $terms,
        ]);

        return [
            'ticker_code' => $entry['ticker_code'],
            'source' => $source,
            'source_payload' => $sourcePayload,
            'revision' => [
                'event_uid' => $entry['event_uid'],
                'revision_number' => 1,
                'listing_id' => (int) $listing->listing_id,
                'action_type_code' => 'STOCK_SPLIT',
                'lifecycle_state' => 'EFFECTIVE',
                'verification_state' => 'AUTHORITATIVE_VERIFIED',
                'ex_date' => $entry['ex_date'],
                'cum_date' => $entry['cum_date'],
                'record_date' => $entry['record_date'],
                // The table's payment_date field carries the distribution/payment lifecycle date.
                // terms_json preserves that this stock-split date is specifically distribution.
                'payment_date' => $entry['distribution_date'],
                'terms_json' => $termsJson,
                'effective_at' => $entry['effective_date'].' 00:00:00',
                'supersedes_revision_id' => null,
            ],
        ];
    }

    private function validateSource($source, array $event, $path)
    {
        if (! is_array($source)) {
            throw new \RuntimeException('STAGE_6_SOURCE_INVALID: '.$path.' must be an object.');
        }
        $this->assertExactKeys($source, [
            'authority_name', 'authority_class', 'document_number', 'document_date', 'document_url',
            'document_sha256', 'document_byte_length', 'content_type',
        ], $path);

        if ($source['authority_name'] !== 'KSEI' || $source['authority_class'] !== 'CSD') {
            throw new \RuntimeException('STAGE_6_SOURCE_NOT_AUTHORITATIVE: source must be KSEI in its CSD authority role.');
        }
        if (! is_string($source['document_number']) || ! preg_match('/^KSEI-[0-9]+\/JKU\/[0-9]{4}$/', $source['document_number'])) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_NUMBER_INVALID: KSEI document number is required.');
        }
        if (! $this->isIsoDate($source['document_date']) || $source['document_date'] > $event['ex_date']) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_DATE_INVALID: document_date must precede or equal ex_date.');
        }
        if (! is_string($source['document_url'])) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_URL_INVALID: document_url is required.');
        }
        $url = parse_url($source['document_url']);
        if (! is_array($url)
            || strtolower((string) ($url['scheme'] ?? '')) !== 'https'
            || strtolower((string) ($url['host'] ?? '')) !== 'web.ksei.co.id'
            || strpos((string) ($url['path'] ?? ''), '/Announcement/Files/') !== 0) {
            throw new \RuntimeException('STAGE_6_SOURCE_NOT_AUTHORITATIVE: document_url must be an official KSEI announcement file.');
        }
        if (! is_string($source['document_sha256']) || ! preg_match('/^[a-f0-9]{64}$/', $source['document_sha256'])) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_HASH_INVALID: document_sha256 must be a lowercase SHA-256 hash.');
        }
        if (! is_int($source['document_byte_length']) || $source['document_byte_length'] <= 0) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_LENGTH_INVALID: document_byte_length must be a positive integer.');
        }
        if ($source['content_type'] !== 'application/pdf') {
            throw new \RuntimeException('STAGE_6_DOCUMENT_TYPE_INVALID: authoritative document must be application/pdf.');
        }

        return $source;
    }

    private function resolveListing(array $event)
    {
        $exDate = $event['ex_date'];
        $rows = DB::table('md_listings as l')
            ->join('md_listing_symbols as s', 's.listing_id', '=', 'l.listing_id')
            ->where('s.symbol', $event['ticker_code'])
            ->where('s.symbol_type', 'EXCHANGE')
            ->where('l.exchange_code', $event['exchange_code'])
            ->where('l.market_segment', $event['market_segment'])
            ->where('l.listed_date', '<=', $exDate)
            ->where(function ($query) use ($exDate) {
                $query->whereNull('l.delisted_date')->orWhere('l.delisted_date', '>', $exDate);
            })
            ->where('s.effective_from', '<=', $exDate.' 23:59:59')
            ->where(function ($query) use ($exDate) {
                $query->whereNull('s.effective_to')->orWhere('s.effective_to', '>', $exDate.' 00:00:00');
            })
            ->whereNull('s.retracted_at')
            ->select(['l.listing_id', 'l.legacy_ticker_id'])
            ->get();

        if ($rows->count() !== 1) {
            throw new \RuntimeException('STAGE_6_LISTING_UNRESOLVED: '.$event['ticker_code'].' must resolve to exactly one listing as of '.$exDate.'.');
        }

        return $rows->first();
    }

    private function assertActionTypeRegistered($actionTypeCode)
    {
        $type = DB::table('market_data_corporate_action_types')
            ->where('action_type_code', $actionTypeCode)
            ->first();

        if (! $type) {
            throw new \RuntimeException('STAGE_6_ACTION_TYPE_UNREGISTERED: '.$actionTypeCode.' is absent from the governed registry.');
        }
        if ((string) $type->price_continuity_impact !== 'SCALED'
            || (string) $type->volume_continuity_impact !== 'SCALED'
            || (int) $type->share_count_changes !== 1) {
            throw new \RuntimeException('STAGE_6_ACTION_TYPE_CONFLICT: STOCK_SPLIT registry semantics do not match the authoritative terms.');
        }
    }

    private function findRevision(array $event, $lock)
    {
        $query = DB::table('md_corporate_action_revisions')
            ->where('event_uid', $event['revision']['event_uid'])
            ->where('revision_number', $event['revision']['revision_number']);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function assertExistingRevisionMatches($existing, array $event)
    {
        $expected = $event['revision'];
        foreach ([
            'event_uid', 'revision_number', 'listing_id', 'action_type_code', 'lifecycle_state',
            'verification_state', 'ex_date', 'cum_date', 'record_date', 'payment_date', 'effective_at',
            'supersedes_revision_id',
        ] as $field) {
            $actual = $existing->{$field};
            if ($actual !== null && in_array($field, ['revision_number', 'listing_id', 'supersedes_revision_id'], true)) {
                $actual = (int) $actual;
            }
            if ($actual !== $expected[$field]) {
                throw new \RuntimeException('STAGE_6_REVISION_CONFLICT: existing '.$field.' differs; append a new governed revision instead of overwriting.');
            }
        }

        if ($this->canonicalizeStoredJson($existing->terms_json) !== $event['revision']['terms_json']) {
            throw new \RuntimeException('STAGE_6_REVISION_CONFLICT: existing terms_json differs; append a new governed revision instead of overwriting.');
        }

        if (! $existing->source_observation_id) {
            throw new \RuntimeException('STAGE_6_REVISION_CONFLICT: existing revision has no accepted source observation.');
        }
        $accepted = DB::table('md_source_observations')
            ->where('source_observation_id', (int) $existing->source_observation_id)
            ->first();
        $expectedPayloadHash = hash('sha256', $event['source_payload']);
        if (! $accepted
            || ! in_array((string) $accepted->outcome_state, ['ACCEPTED', 'NORMALIZED'], true)
            || (string) $accepted->validation_state !== 'PASSED'
            || (string) $accepted->source_name !== 'KSEI'
            || (string) $accepted->sanitized_request_identity !== $event['source']['document_url']
            || ! hash_equals($expectedPayloadHash, (string) $accepted->payload_hash)) {
            throw new \RuntimeException('STAGE_6_REVISION_CONFLICT: linked source observation does not prove the same authoritative document.');
        }

        $capture = DB::table('md_source_observations')
            ->where('source_observation_id', (int) $accepted->parent_observation_id)
            ->first();
        if (! $capture
            || (string) $capture->outcome_state !== 'CAPTURED'
            || ! hash_equals($expectedPayloadHash, (string) $capture->payload_hash)
            || (string) $capture->bounded_payload_body !== $event['source_payload']) {
            throw new \RuntimeException('STAGE_6_REVISION_CONFLICT: immutable capture behind the accepted observation is missing or differs.');
        }
    }

    private function summarize(array $prepared, $applied)
    {
        $inserted = 0;
        $unchanged = 0;
        foreach ($prepared['events'] as $event) {
            if ($event['operation'] === 'INSERT') {
                $inserted++;
            } else {
                $unchanged++;
            }
        }

        return [
            'scope_id' => $prepared['scope_id'],
            'scope_entry_count' => count($prepared['events']),
            'inserted_revision_count' => $inserted,
            'unchanged_revision_count' => $unchanged,
            'source_observation_insert_count' => $inserted * 2,
            'applied' => (bool) $applied,
        ];
    }

    private function assertExactKeys(array $value, array $expected, $path)
    {
        $actual = array_keys($value);
        sort($actual, SORT_STRING);
        sort($expected, SORT_STRING);
        if ($actual !== $expected) {
            throw new \RuntimeException('STAGE_6_SCHEMA_DRIFT: '.$path.' fields must match the locked v1 manifest exactly.');
        }
    }

    private function canonicalJson(array $value)
    {
        $this->sortRecursively($value);
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('STAGE_6_CANONICALIZATION_FAILED: '.json_last_error_msg());
        }

        return $json;
    }

    private function canonicalizeStoredJson($json)
    {
        $decoded = json_decode((string) $json, true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('STAGE_6_REVISION_CONFLICT: existing terms_json is not valid JSON.');
        }

        return $this->canonicalJson($decoded);
    }

    private function sortRecursively(array &$value)
    {
        foreach ($value as &$child) {
            if (is_array($child)) {
                $this->sortRecursively($child);
            }
        }
        unset($child);

        if ($this->isAssociative($value)) {
            ksort($value, SORT_STRING);
        }
    }

    private function isAssociative(array $value)
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }

    private function isIsoDate($value)
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value;
    }

    private function isIsoDateTime($value)
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);

        return $date && $date->format('Y-m-d H:i:s') === $value;
    }
}
