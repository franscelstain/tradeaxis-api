<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\Ports\AuthoritativeTradingStatusEvidenceVerifier;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthoritativeTradingStatusSnapshotService
{
    const MANIFEST_SCHEMA = 'market-data-authoritative-trading-status-snapshot/v1';
    const SOURCE_ADAPTER_VERSION = 'idx-authoritative-trading-status-v1';

    private $observations;
    private $verifier;

    public function __construct(
        SourceObservationRepository $observations,
        AuthoritativeTradingStatusEvidenceVerifier $verifier
    ) {
        $this->observations = $observations;
        $this->verifier = $verifier;
    }

    public function process($manifestPath, $apply = false): array
    {
        $manifest = $this->readManifest($manifestPath);
        $prepared = $this->validateManifest($manifest);
        $resolvedEntries = $this->resolveListings($prepared);
        $existing = $this->existingRevisionState($resolvedEntries, $prepared);

        if (! $apply) {
            return $this->summary($prepared, $existing, false, null, null);
        }

        if ($existing['insert_count'] === 0) {
            return $this->summary(
                $prepared,
                $existing,
                true,
                $existing['snapshot_observation_id'],
                $this->existingAcceptedObservationId($prepared['transition_search_source'], 'AUTHORITATIVE_TRADING_STATUS_TRANSITIONS_VALIDATED')
            );
        }

        $snapshotEvidence = $this->verifier->verifySnapshot(
            $prepared['snapshot_source'],
            $prepared['entries']
        );
        $transitionEvidence = $this->verifier->verifyTransitionSearch(
            $prepared['transition_search_source'],
            array_column($prepared['entries'], 'ticker_code')
        );
        $this->assertEvidenceResult($snapshotEvidence);
        $this->assertEvidenceResult($transitionEvidence);

        return DB::transaction(function () use ($prepared, $resolvedEntries, $snapshotEvidence, $transitionEvidence) {
            $recordedAt = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
            $snapshotObservationId = $this->captureAcceptedEvidence(
                $prepared['snapshot_source'],
                $snapshotEvidence,
                'AUTHORITATIVE_TRADING_STATUS_VALIDATED',
                $recordedAt
            );
            $transitionObservationId = $this->captureAcceptedEvidence(
                $prepared['transition_search_source'],
                $transitionEvidence,
                'AUTHORITATIVE_TRADING_STATUS_TRANSITIONS_VALIDATED',
                $recordedAt
            );
            $snapshotPayloadHash = (string) DB::table('md_source_observations')
                ->where('source_observation_id', $snapshotObservationId)
                ->value('payload_hash');

            $inserted = 0;
            $unchanged = 0;
            foreach ($resolvedEntries as $entry) {
                $existing = $this->findExactRevision($entry['listing_id'], $prepared['snapshot_source']['observed_as_of']);
                if ($existing) {
                    $this->assertRevisionMatches($existing, $entry, $prepared, (int) $snapshotObservationId);
                    $unchanged++;
                    continue;
                }

                DB::table('md_trading_status_revisions')->insert([
                    'listing_id' => (int) $entry['listing_id'],
                    'instrument_id' => (int) $entry['instrument_id'],
                    'status_event_uid' => hash('sha256', implode('|', [
                        'IDX_LONG_SUSPENSION_SNAPSHOT', (int) $entry['listing_id'],
                        $prepared['snapshot_source']['observed_as_of'], 'SUSPENSION_OBSERVED',
                    ])),
                    'status_type_code' => 'SUSPENSION_OBSERVED',
                    'status_code' => 'SUSPENSION_OBSERVED',
                    'bar_expectation_state' => 'BAR_NOT_EXPECTED',
                    'board_code' => $entry['board_code'],
                    'authority_class' => 'EXCHANGE_AUTHORITATIVE',
                    'source_name' => 'IDX_LONG_SUSPENSION_SNAPSHOT',
                    'source_payload_hash' => $snapshotPayloadHash,
                    'full_session_verified' => 1,
                    // The monthly snapshot proves status on its as-of date. The older reported
                    // suspension date is retained in the evidence payload, never projected back
                    // as the effective boundary of this revision.
                    'effective_from' => $prepared['snapshot_source']['observed_as_of'].' 00:00:00',
                    'effective_to' => null,
                    'recorded_at' => $recordedAt,
                    'retracted_at' => null,
                    'source_observation_id' => (int) $snapshotObservationId,
                    'supersedes_revision_id' => null,
                    'source_ref' => $prepared['snapshot_source']['document_url'],
                    'verification_state' => 'VERIFIED',
                    'observed_at' => $prepared['snapshot_source']['observed_as_of'].' 00:00:00',
                    'announced_at' => $prepared['snapshot_source']['observed_as_of'].' 00:00:00',
                    'operator_name' => null,
                    'governed_reason_code' => null,
                    'authoritative_source_ref' => null,
                ]);
                $inserted++;
            }

            return [
                'scope_id' => $prepared['scope_id'],
                'entry_count' => count($resolvedEntries),
                'inserted_revision_count' => $inserted,
                'unchanged_revision_count' => $unchanged,
                'source_observation_insert_count' => 4,
                'status_snapshot_observation_id' => (int) $snapshotObservationId,
                'transition_search_observation_id' => (int) $transitionObservationId,
                'observed_as_of' => $prepared['snapshot_source']['observed_as_of'],
                'transition_search_end' => $prepared['transition_search_source']['search_end'],
                'applied' => true,
            ];
        });
    }

    private function readManifest($path): array
    {
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            throw new \RuntimeException('STAGE8_STATUS_MANIFEST_NOT_FOUND: expected a local governed manifest.');
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('STAGE8_STATUS_MANIFEST_INVALID: manifest is not valid JSON.');
        }

        return $decoded;
    }

    private function validateManifest(array $manifest): array
    {
        $this->assertExactKeys($manifest, [
            'schema_version', 'scope_id', 'record_only', 'snapshot_source',
            'transition_search_source', 'entries',
        ], '$');
        if (($manifest['schema_version'] ?? null) !== self::MANIFEST_SCHEMA
            || ($manifest['record_only'] ?? null) !== true
            || trim((string) ($manifest['scope_id'] ?? '')) === '') {
            throw new \RuntimeException('STAGE8_STATUS_MANIFEST_SCOPE_INVALID: schema, scope, or record-only boundary differs.');
        }

        $snapshot = $manifest['snapshot_source'] ?? null;
        $transition = $manifest['transition_search_source'] ?? null;
        $entries = $manifest['entries'] ?? null;
        if (! is_array($snapshot) || ! is_array($transition) || ! is_array($entries) || $entries === []) {
            throw new \RuntimeException('STAGE8_STATUS_MANIFEST_SCOPE_INVALID: source or entries are absent.');
        }
        $this->assertExactKeys($snapshot, [
            'authority_name', 'authority_class', 'document_url', 'content_type', 'observed_as_of',
        ], '$.snapshot_source');
        $this->assertExactKeys($transition, [
            'authority_name', 'authority_class', 'document_url', 'content_type',
            'search_start', 'search_end', 'expected_in_scope_events',
        ], '$.transition_search_source');

        if ($snapshot['authority_name'] !== 'IDX'
            || $snapshot['authority_class'] !== 'EXCHANGE_AUTHORITATIVE'
            || $snapshot['content_type'] !== 'text/html'
            || strtolower((string) parse_url($snapshot['document_url'], PHP_URL_HOST)) !== 'block.idx.id'
            || ! $this->isIsoDate($snapshot['observed_as_of'])) {
            throw new \RuntimeException('STAGE8_STATUS_SNAPSHOT_SOURCE_INVALID: snapshot authority or boundary is invalid.');
        }
        if ($transition['authority_name'] !== 'IDX'
            || $transition['authority_class'] !== 'EXCHANGE_AUTHORITATIVE'
            || $transition['content_type'] !== 'application/json'
            || strtolower((string) parse_url($transition['document_url'], PHP_URL_HOST)) !== 'www.idx.id'
            || ! $this->isIsoDate($transition['search_start'])
            || ! $this->isIsoDate($transition['search_end'])
            || $transition['search_start'] !== $snapshot['observed_as_of']
            || $transition['search_end'] < $transition['search_start']) {
            throw new \RuntimeException('STAGE8_STATUS_TRANSITION_SOURCE_INVALID: transition authority or interval is invalid.');
        }

        $seen = [];
        foreach ($entries as $index => $entry) {
            if (! is_array($entry)) {
                throw new \RuntimeException('STAGE8_STATUS_MANIFEST_ENTRY_INVALID: entry '.$index.' is not an object.');
            }
            $this->assertExactKeys($entry, ['ticker_code', 'reported_suspension_date'], '$.entries['.$index.']');
            $code = strtoupper(trim((string) $entry['ticker_code']));
            if (! preg_match('/^[A-Z0-9]{4}$/', $code)
                || isset($seen[$code])
                || ! $this->isIsoDate($entry['reported_suspension_date'])
                || $entry['reported_suspension_date'] > $snapshot['observed_as_of']) {
                throw new \RuntimeException('STAGE8_STATUS_MANIFEST_ENTRY_INVALID: entry '.$index.' is invalid or duplicated.');
            }
            $seen[$code] = true;
            $entries[$index]['ticker_code'] = $code;
        }
        sort($entries);

        $events = $transition['expected_in_scope_events'];
        if (! is_array($events)) {
            throw new \RuntimeException('STAGE8_STATUS_TRANSITION_EXPECTATION_INVALID: expected events must be a list.');
        }
        foreach ($events as $index => $event) {
            $this->assertExactKeys($event, ['ticker_code', 'event_type', 'event_at'], '$.transition.events['.$index.']');
            if (! isset($seen[$event['ticker_code']])
                || ! in_array($event['event_type'], ['SPT', 'UPT'], true)
                || ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $event['event_at'])
                || substr($event['event_at'], 0, 10) < $transition['search_start']
                || substr($event['event_at'], 0, 10) > $transition['search_end']) {
                throw new \RuntimeException('STAGE8_STATUS_TRANSITION_EXPECTATION_INVALID: event '.$index.' is invalid.');
            }
        }

        $manifest['entries'] = $entries;

        return $manifest;
    }

    private function resolveListings(array $prepared): array
    {
        $asOf = $prepared['snapshot_source']['observed_as_of'];
        $codes = array_column($prepared['entries'], 'ticker_code');
        $rows = DB::table('md_listing_symbols as symbol')
            ->join('md_listings as listing', 'listing.listing_id', '=', 'symbol.listing_id')
            ->whereIn('symbol.symbol', $codes)
            ->where('symbol.symbol_type', 'EXCHANGE')
            ->where('listing.exchange_code', config('market_data.scope.market_code', 'IDX'))
            ->where('listing.market_segment', config('market_data.scope.market_segment', 'REGULAR'))
            ->whereNull('symbol.retracted_at')
            ->where('symbol.effective_from', '<=', $asOf.' 23:59:59')
            ->where(function ($query) use ($asOf) {
                $query->whereNull('symbol.effective_to')->orWhere('symbol.effective_to', '>', $asOf.' 00:00:00');
            })
            ->get(['symbol.symbol', 'listing.listing_id', 'listing.instrument_id', 'listing.board_code']);

        $byCode = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) $row->symbol));
            if (isset($byCode[$code])) {
                throw new \RuntimeException('STAGE8_STATUS_LISTING_MAPPING_AMBIGUOUS: '.$code.' maps to multiple effective listings.');
            }
            $byCode[$code] = $row;
        }
        if (count($byCode) !== count($codes)) {
            $missing = array_values(array_diff($codes, array_keys($byCode)));
            throw new \RuntimeException('STAGE8_STATUS_LISTING_MAPPING_MISSING: '.implode(',', $missing).'.');
        }

        return array_map(function ($entry) use ($byCode) {
            $row = $byCode[$entry['ticker_code']];

            return $entry + [
                'listing_id' => (int) $row->listing_id,
                'instrument_id' => (int) $row->instrument_id,
                'board_code' => $row->board_code === null ? null : (string) $row->board_code,
            ];
        }, $prepared['entries']);
    }

    private function existingRevisionState(array $entries, array $prepared): array
    {
        $insert = 0;
        $unchanged = 0;
        $observationId = null;
        foreach ($entries as $entry) {
            $row = $this->findExactRevision($entry['listing_id'], $prepared['snapshot_source']['observed_as_of']);
            if (! $row) {
                $insert++;
                continue;
            }
            $this->assertRevisionMatches($row, $entry, $prepared, null);
            if ($observationId !== null && (int) $row->source_observation_id !== $observationId) {
                throw new \RuntimeException('STAGE8_STATUS_REVISION_EVIDENCE_SPLIT: one snapshot scope binds multiple observations.');
            }
            $observationId = (int) $row->source_observation_id;
            $unchanged++;
        }
        if ($insert > 0 && $unchanged > 0) {
            throw new \RuntimeException('STAGE8_STATUS_REVISION_PARTIAL_SCOPE: partial authority scope cannot be silently completed.');
        }

        return [
            'insert_count' => $insert,
            'unchanged_count' => $unchanged,
            'snapshot_observation_id' => $observationId,
        ];
    }

    private function findExactRevision($listingId, $asOf)
    {
        return DB::table('md_trading_status_revisions')
            ->where('listing_id', (int) $listingId)
            ->where('status_code', 'SUSPENSION_OBSERVED')
            ->where('effective_from', $asOf.' 00:00:00')
            ->whereNull('retracted_at')
            ->first();
    }

    private function assertRevisionMatches($row, array $entry, array $prepared, $expectedObservationId): void
    {
        $expected = [
            'listing_id' => (int) $entry['listing_id'],
            'instrument_id' => (int) $entry['instrument_id'],
            'status_event_uid' => hash('sha256', implode('|', [
                'IDX_LONG_SUSPENSION_SNAPSHOT', (int) $entry['listing_id'],
                $prepared['snapshot_source']['observed_as_of'], 'SUSPENSION_OBSERVED',
            ])),
            'status_type_code' => 'SUSPENSION_OBSERVED',
            'status_code' => 'SUSPENSION_OBSERVED',
            'bar_expectation_state' => 'BAR_NOT_EXPECTED',
            'board_code' => $entry['board_code'],
            'authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'source_name' => 'IDX_LONG_SUSPENSION_SNAPSHOT',
            'full_session_verified' => 1,
            'effective_from' => $prepared['snapshot_source']['observed_as_of'].' 00:00:00',
            'effective_to' => null,
            'retracted_at' => null,
            'source_ref' => $prepared['snapshot_source']['document_url'],
            'verification_state' => 'VERIFIED',
            'observed_at' => $prepared['snapshot_source']['observed_as_of'].' 00:00:00',
            'announced_at' => $prepared['snapshot_source']['observed_as_of'].' 00:00:00',
        ];
        foreach ($expected as $field => $value) {
            if (($value === null && $row->{$field} !== null)
                || ($value !== null && (string) $row->{$field} !== (string) $value)) {
                throw new \RuntimeException('STAGE8_STATUS_REVISION_CONFLICT: immutable revision differs at '.$field.'.');
            }
        }
        if ((int) $row->source_observation_id <= 0
            || ($expectedObservationId !== null && (int) $row->source_observation_id !== (int) $expectedObservationId)) {
            throw new \RuntimeException('STAGE8_STATUS_REVISION_EVIDENCE_INVALID: immutable revision has the wrong source observation.');
        }
        $payloadHash = (string) DB::table('md_source_observations')
            ->where('source_observation_id', (int) $row->source_observation_id)
            ->where('outcome_state', 'ACCEPTED')
            ->value('payload_hash');
        if (! preg_match('/^[a-f0-9]{64}$/', strtolower($payloadHash))
            || strtolower($payloadHash) !== strtolower((string) $row->source_payload_hash)) {
            throw new \RuntimeException('STAGE8_STATUS_REVISION_EVIDENCE_INVALID: immutable revision payload hash is not bound to accepted evidence.');
        }
    }

    private function captureAcceptedEvidence(array $source, array $verified, $reasonCode, $recordedAt): int
    {
        $capture = $this->observations->capture([
            'requested_trade_date' => $source['observed_as_of'] ?? $source['search_end'],
            'requested_start_date' => $source['search_start'] ?? ($source['observed_as_of'] ?? null),
            'requested_end_date' => $source['search_end'] ?? ($source['observed_as_of'] ?? null),
            'source_mode' => 'authority_document',
            'source_name' => 'IDX',
            'provider' => 'IDX',
            'sanitized_request_identity' => $source['document_url'],
            'response_status' => $verified['http_status'],
            'content_type' => $verified['content_type'],
            'source_timestamp' => ($source['observed_as_of'] ?? $source['search_end']).' 00:00:00',
            'acquired_at' => $recordedAt,
            'provider_schema_version' => self::MANIFEST_SCHEMA,
            'schema_fingerprint' => $verified['schema_fingerprint'],
            'adapter_version' => self::SOURCE_ADAPTER_VERSION,
            'payload_hash' => $verified['document_sha256'],
            'payload_ref' => $verified['payload_ref'],
            'payload_byte_length' => (int) $verified['document_byte_length'],
            'bounded_payload_body' => $verified['bounded_payload_body'],
        ]);
        $accepted = $this->observations->recordOutcome($capture, 'ACCEPTED', $reasonCode, [
            'acquired_at' => $recordedAt,
        ]);

        return (int) $accepted['source_observation_id'];
    }

    private function existingAcceptedObservationId(array $source, $reasonCode): int
    {
        $id = DB::table('md_source_observations')
            ->where('source_name', 'IDX')
            ->where('provider', 'IDX')
            ->where('sanitized_request_identity', $source['document_url'])
            ->where('adapter_version', self::SOURCE_ADAPTER_VERSION)
            ->where('outcome_state', 'ACCEPTED')
            ->where('validation_state', 'PASSED')
            ->where('reason_code', $reasonCode)
            ->orderByDesc('source_observation_id')
            ->value('source_observation_id');
        if (! $id) {
            throw new \RuntimeException('STAGE8_STATUS_TRANSITION_EVIDENCE_MISSING: accepted transition evidence is absent.');
        }

        return (int) $id;
    }

    private function assertEvidenceResult(array $result): void
    {
        if ((int) ($result['http_status'] ?? 0) !== 200
            || ! preg_match('/^[a-f0-9]{64}$/', (string) ($result['document_sha256'] ?? ''))
            || ! preg_match('/^[a-f0-9]{64}$/', (string) ($result['schema_fingerprint'] ?? ''))
            || (int) ($result['document_byte_length'] ?? 0) <= 0
            || (string) ($result['payload_ref'] ?? '') !== 'sha256:'.$result['document_sha256']
            || trim((string) ($result['bounded_payload_body'] ?? '')) === '') {
            throw new \RuntimeException('STAGE8_STATUS_VERIFICATION_RESULT_INVALID: verifier did not return hash-verifiable evidence.');
        }
    }

    private function summary(array $prepared, array $existing, $applied, $snapshotObservationId, $transitionObservationId): array
    {
        return [
            'scope_id' => $prepared['scope_id'],
            'entry_count' => count($prepared['entries']),
            'inserted_revision_count' => $existing['insert_count'],
            'unchanged_revision_count' => $existing['unchanged_count'],
            'source_observation_insert_count' => 0,
            'status_snapshot_observation_id' => $snapshotObservationId,
            'transition_search_observation_id' => $transitionObservationId,
            'observed_as_of' => $prepared['snapshot_source']['observed_as_of'],
            'transition_search_end' => $prepared['transition_search_source']['search_end'],
            'applied' => $applied,
        ];
    }

    private function assertExactKeys(array $value, array $expected, $path): void
    {
        $actual = array_keys($value);
        sort($actual, SORT_STRING);
        sort($expected, SORT_STRING);
        if ($actual !== $expected) {
            throw new \RuntimeException('STAGE8_STATUS_MANIFEST_SCHEMA_DRIFT: '.$path.' keys differ.');
        }
    }

    private function isIsoDate($value): bool
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value;
    }
}
