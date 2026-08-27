<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Application\MarketData\Ports\SourceObservationRecorder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SourceObservationRepository implements SourceObservationRecorder
{
    public function capture(array $envelope)
    {
        $payload = array_key_exists('payload', $envelope) ? (string) $envelope['payload'] : null;
        $externalIdentity = $payload === null && array_key_exists('payload_hash', $envelope);
        if ($payload === null && ! $externalIdentity) {
            throw new \RuntimeException(
                'SOURCE_OBSERVATION_PAYLOAD_IDENTITY_REQUIRED: capture requires payload bytes or verifiable external identity.'
            );
        }
        if ($externalIdentity) {
            $this->assertExternalPayloadIdentity($envelope);
        }
        $payloadHash = $payload !== null
            ? hash('sha256', $payload)
            : ($externalIdentity ? strtolower((string) $envelope['payload_hash']) : null);
        $now = $envelope['acquired_at'] ?? Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
        $boundedBytes = max(0, (int) config('market_data.source.bounded_payload_bytes', 65536));
        $boundedBody = $payload !== null
            ? $this->redactSensitiveText(substr($payload, 0, $boundedBytes))
            : ($externalIdentity
                ? $this->redactSensitiveText(substr((string) ($envelope['bounded_payload_body'] ?? ''), 0, $boundedBytes))
                : null);
        if ($boundedBody === '') {
            $boundedBody = null;
        }
        $payloadByteLength = $payload !== null
            ? strlen($payload)
            : ($externalIdentity ? (int) $envelope['payload_byte_length'] : null);
        $payloadRef = $payloadHash
            ? (string) ($envelope['payload_ref'] ?? 'sha256:'.$payloadHash)
            : null;
        $schemaFingerprint = $payload !== null
            ? $this->schemaFingerprint($payload)
            : ($externalIdentity ? (string) $envelope['schema_fingerprint'] : null);

        $row = $this->baseRow($envelope, [
            'observation_uid' => (string) Str::uuid(),
            'acquired_at' => $now,
            'schema_fingerprint' => $schemaFingerprint,
            'payload_hash' => $payloadHash,
            'payload_ref' => $payloadRef,
            'bounded_payload_body' => $boundedBody,
            'payload_byte_length' => $payloadByteLength,
            'outcome_state' => 'CAPTURED',
            'validation_state' => 'PENDING',
            'reason_code' => null,
            'created_at' => $now,
        ]);

        $id = DB::table('md_source_observations')->insertGetId($row);

        return $row + [
            'source_observation_id' => (int) $id,
            'persisted' => true,
        ];
    }

    public function recordOutcome(array $capture, $outcomeState, $reasonCode = null, array $context = [])
    {
        if (empty($capture['source_observation_id']) || empty($capture['persisted'])) {
            throw new \RuntimeException('SOURCE_OBSERVATION_CAPTURE_REQUIRED: final outcome cannot exist without a persisted capture.');
        }

        $now = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
        $row = $this->baseRow(array_merge($capture, $context), [
            'observation_uid' => (string) Str::uuid(),
            'parent_observation_id' => (int) $capture['source_observation_id'],
            'payload_hash' => $capture['payload_hash'] ?? null,
            'payload_ref' => $capture['payload_ref'] ?? null,
            'payload_byte_length' => $capture['payload_byte_length'] ?? null,
            'bounded_payload_body' => null,
            'schema_fingerprint' => $capture['schema_fingerprint'] ?? null,
            'outcome_state' => strtoupper((string) $outcomeState),
            'validation_state' => in_array(strtoupper((string) $outcomeState), ['ACCEPTED', 'NORMALIZED'], true) ? 'PASSED' : 'FAILED',
            'reason_code' => $reasonCode,
            'created_at' => $now,
        ]);

        $id = DB::table('md_source_observations')->insertGetId($row);

        return $row + [
            'source_observation_id' => (int) $id,
            'capture_observation_id' => (int) $capture['source_observation_id'],
            'persisted' => true,
        ];
    }

    public function recordAcceptedRows(array $capture, array $rows, array $rejectedRows = [])
    {
        if ($rows === []) {
            throw new \RuntimeException('SOURCE_OBSERVATION_ACCEPTED_ROWS_REQUIRED: an accepted observation must contain normalized rows.');
        }

        return DB::transaction(function () use ($capture, $rows, $rejectedRows) {
            $outcome = $this->recordOutcome($capture, 'ACCEPTED');
            $comparisonCount = 0;
            $divergenceCount = 0;

            foreach (array_values($rows) as $index => $row) {
                $persisted = $this->persistNormalizedRow($capture, $outcome, $row, $index + 1);
                if ($persisted['comparison_created']) {
                    $comparisonCount++;
                }
                if ($persisted['divergence_created']) {
                    $divergenceCount++;
                }
            }
            foreach (array_values($rejectedRows) as $index => $row) {
                $this->persistRejectedRow($capture, $outcome, $row, $index + 1);
            }

            return $outcome + [
                'normalized_row_count' => count($rows),
                'comparison_count' => $comparisonCount,
                'divergence_finding_count' => $divergenceCount,
                'rejected_row_count' => count($rejectedRows),
            ];
        });
    }

    public function recordRejectedRows(array $capture, array $rejectedRows, $reasonCode)
    {
        if ($rejectedRows === []) {
            throw new \RuntimeException('SOURCE_OBSERVATION_REJECTED_ROWS_REQUIRED: row rejection evidence cannot be empty.');
        }

        return DB::transaction(function () use ($capture, $rejectedRows, $reasonCode) {
            $outcome = $this->recordOutcome($capture, 'REJECTED', $reasonCode);
            foreach (array_values($rejectedRows) as $index => $row) {
                $this->persistRejectedRow($capture, $outcome, $row, $index + 1);
            }

            return $outcome + ['rejected_row_count' => count($rejectedRows)];
        });
    }

    public function recordTransportFailure(array $envelope, $reasonCode)
    {
        $envelope['content_type'] = 'application/vnd.tradeaxis.source-transport-failure+json';
        $envelope['payload'] = json_encode([
            'observation_type' => 'TRANSPORT_FAILURE',
            'reason_code' => strtoupper((string) $reasonCode),
            'requested_trade_date' => $envelope['requested_trade_date'] ?? ($envelope['trade_date'] ?? null),
            'requested_start_date' => $envelope['requested_start_date'] ?? ($envelope['source_window_start'] ?? null),
            'requested_end_date' => $envelope['requested_end_date'] ?? ($envelope['source_window_end'] ?? null),
            'sanitized_request_identity' => $this->redactSensitiveText((string) ($envelope['sanitized_request_identity'] ?? 'unavailable')),
        ], JSON_UNESCAPED_SLASHES);
        $capture = $this->capture($envelope);

        return $this->recordOutcome($capture, 'FAILED', $reasonCode);
    }

    public function existsAccepted($observationId, $sourceRowRef = null)
    {
        $outcome = DB::table('md_source_observations')
            ->where('source_observation_id', $observationId)
            ->whereIn('outcome_state', ['ACCEPTED', 'NORMALIZED'])
            ->first();
        if (! $outcome || empty($outcome->parent_observation_id)) {
            return false;
        }

        $capture = DB::table('md_source_observations')
            ->where('source_observation_id', (int) $outcome->parent_observation_id)
            ->where('outcome_state', 'CAPTURED')
            ->first();
        if (! $capture) {
            return false;
        }

        $hash = strtolower((string) $capture->payload_hash);
        $schema = strtolower((string) $capture->schema_fingerprint);
        $hasExecutionIdentity = ! empty($capture->run_id) || ! empty($capture->acquisition_batch_id);
        $normalizedRows = DB::table('md_source_observation_rows')
            ->where('source_observation_id', (int) $outcome->source_observation_id);
        $identityBindings = DB::table('md_source_observation_identity_bindings')
            ->where('source_observation_id', (int) $outcome->source_observation_id);
        if ($sourceRowRef !== null) {
            $normalizedRows->where('source_row_ref', (string) $sourceRowRef);
            $rowIds = $normalizedRows->pluck('source_observation_row_id')->all();
            $identityBindings->whereIn('source_observation_row_id', $rowIds);
        }
        $normalizedRowCount = $normalizedRows->count();
        $identityBindingCount = $identityBindings->count();
        $hasNormalizedRows = $normalizedRowCount > 0;

        return $hasExecutionIdentity
            && $hasNormalizedRows
            && $identityBindingCount === $normalizedRowCount
            && ! empty($capture->attempt_uid)
            && ! empty($capture->requested_trade_date)
            && ! empty($capture->source_mode)
            && ! empty($capture->source_name)
            && ! empty($capture->sanitized_request_identity)
            && ! empty($capture->acquired_at)
            && ! empty($capture->adapter_version)
            && preg_match('/^[a-f0-9]{64}$/', $hash) === 1
            && preg_match('/^[a-f0-9]{64}$/', $schema) === 1
            && (int) $capture->payload_byte_length > 0
            && (string) $capture->payload_ref === 'sha256:'.$hash
            && strtolower((string) $outcome->payload_hash) === $hash
            && strtolower((string) $outcome->schema_fingerprint) === $schema;
    }

    public function bindResolvedIdentity($observationId, $sourceRowRef, array $identity)
    {
        foreach (['listing_id', 'trade_date'] as $field) {
            if (empty($identity[$field])) {
                throw new \RuntimeException('SOURCE_OBSERVATION_IDENTITY_BINDING_INVALID: missing '.$field.'.');
            }
        }

        $observationRow = DB::table('md_source_observation_rows')
            ->where('source_observation_id', (int) $observationId)
            ->where('source_row_ref', (string) $sourceRowRef)
            ->first();
        if (! $observationRow) {
            throw new \RuntimeException('SOURCE_OBSERVATION_ROW_MISSING: normalized observation row cannot be identity-bound.');
        }

        $mappingRevision = trim((string) ($identity['mapping_revision'] ?? ''));
        if ($mappingRevision === '') {
            $mappingRevision = 'IDENTITY-'.strtoupper(substr(hash('sha256', implode('|', [
                $identity['listing_id'],
                $identity['listing_symbol_id'] ?? '',
                $identity['identity_recorded_at'] ?? '',
                $identity['trade_date'],
            ])), 0, 32));
        }

        $binding = [
            'source_observation_row_id' => (int) $observationRow->source_observation_row_id,
            'source_observation_id' => (int) $observationId,
            'listing_id' => (int) $identity['listing_id'],
            'provider_mapping_id' => ! empty($identity['provider_mapping_id']) ? (int) $identity['provider_mapping_id'] : null,
            'mapping_revision' => $mappingRevision,
            'effective_trade_date' => (string) $identity['trade_date'],
            'recorded_at' => Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ];

        $existing = DB::table('md_source_observation_identity_bindings')
            ->where('source_observation_row_id', (int) $observationRow->source_observation_row_id)
            ->first();
        if ($existing) {
            foreach (['source_observation_id', 'listing_id', 'provider_mapping_id', 'mapping_revision', 'effective_trade_date'] as $field) {
                if ((string) $existing->{$field} !== (string) $binding[$field]) {
                    throw new \RuntimeException('SOURCE_OBSERVATION_IDENTITY_BINDING_CONFLICT: immutable binding differs for '.$field.'.');
                }
            }

            return (int) $existing->source_observation_identity_binding_id;
        }

        return (int) DB::table('md_source_observation_identity_bindings')->insertGetId($binding);
    }

    public function manifestHashForRun($runId)
    {
        $rows = DB::table('md_source_observations')
            ->where('run_id', $runId)
            ->orderBy('observation_uid')
            ->get();
        return $this->manifestHashForRows($rows);
    }

    public function manifestHashForObservationIds(array $observationIds)
    {
        $observationIds = array_values(array_unique(array_filter(array_map('intval', $observationIds))));
        sort($observationIds, SORT_NUMERIC);

        $rows = DB::table('md_source_observations')
            ->whereIn('source_observation_id', $observationIds)
            ->orderBy('observation_uid')
            ->get();

        if (count($rows) !== count($observationIds)) {
            throw new \RuntimeException('SOURCE_OBSERVATION_MANIFEST_INCOMPLETE: one or more selected observations are missing.');
        }

        return $this->manifestHashForRows($rows);
    }

    private function manifestHashForRows($rows)
    {
        $lines = [];

        foreach ($rows as $row) {
            $lines[] = implode('|', [
                $row->observation_uid,
                $row->parent_observation_id ?? '',
                $row->source_mode ?? '',
                $row->provider ?? '',
                $row->provider_symbol ?? '',
                $row->provider_mapping_id ?? '',
                $row->requested_trade_date,
                $row->payload_hash ?? '',
                $row->schema_fingerprint ?? '',
                $row->adapter_version,
                $row->outcome_state,
                $row->reason_code ?? '',
            ]);
        }

        return hash('sha256', implode("\n", $lines));
    }

    private function assertExternalPayloadIdentity(array $envelope)
    {
        $hash = strtolower((string) ($envelope['payload_hash'] ?? ''));
        $length = $envelope['payload_byte_length'] ?? null;
        $schemaFingerprint = strtolower((string) ($envelope['schema_fingerprint'] ?? ''));
        $payloadRef = (string) ($envelope['payload_ref'] ?? '');

        if (! preg_match('/^[a-f0-9]{64}$/', $hash)
            || ! is_int($length) || $length <= 0
            || ! preg_match('/^[a-f0-9]{64}$/', $schemaFingerprint)
            || $payloadRef !== 'sha256:'.$hash
            || trim((string) ($envelope['bounded_payload_body'] ?? '')) === '') {
            throw new \RuntimeException(
                'SOURCE_OBSERVATION_EXTERNAL_IDENTITY_INVALID: external payload evidence requires exact hash, length, schema fingerprint, content-addressed reference, and bounded sample.'
            );
        }
    }

    private function baseRow(array $source, array $overrides)
    {
        return array_merge([
            'observation_uid' => $source['observation_uid'] ?? (string) Str::uuid(),
            'parent_observation_id' => $source['parent_observation_id'] ?? null,
            'run_id' => $source['run_id'] ?? null,
            'attempt_uid' => $source['attempt_uid'] ?? (string) Str::uuid(),
            'acquisition_batch_id' => $source['acquisition_batch_id'] ?? ($source['source_acquisition_batch_id'] ?? null),
            'acquisition_checkpoint_id' => $source['acquisition_checkpoint_id'] ?? null,
            'requested_trade_date' => $source['requested_trade_date'] ?? ($source['trade_date'] ?? ($source['requested_start_date'] ?? null)),
            'requested_start_date' => $source['requested_start_date'] ?? ($source['source_window_start'] ?? null),
            'requested_end_date' => $source['requested_end_date'] ?? ($source['source_window_end'] ?? null),
            'source_mode' => $source['source_mode'] ?? 'api_free',
            'source_name' => $source['source_name'] ?? 'UNKNOWN',
            'provider' => $source['provider'] ?? null,
            'provider_symbol' => $source['provider_symbol'] ?? null,
            'provider_mapping_id' => $source['provider_mapping_id'] ?? null,
            'mapping_revision' => $source['mapping_revision'] ?? null,
            'config_snapshot_id' => $source['config_snapshot_id'] ?? null,
            // The field is named sanitized, but naming is not sanitising. Redacting here rather
            // than trusting every caller keeps a leaked query credential out of a table that
            // cannot be edited afterwards.
            'sanitized_request_identity' => substr($this->redactSensitiveText((string) ($source['sanitized_request_identity'] ?? 'unavailable')), 0, 255),
            'response_status' => $source['response_status'] ?? ($source['http_status'] ?? null),
            'content_type' => $source['content_type'] ?? null,
            'source_timestamp' => $source['source_timestamp'] ?? null,
            'acquired_at' => $source['acquired_at'] ?? Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
            'provider_schema_version' => $source['provider_schema_version'] ?? config('market_data.source.api.schema_version'),
            'schema_fingerprint' => $source['schema_fingerprint'] ?? null,
            'adapter_version' => $source['adapter_version'] ?? config('market_data.source.api.adapter_version', 'unknown-adapter'),
            'payload_hash' => $source['payload_hash'] ?? null,
            'payload_ref' => $source['payload_ref'] ?? null,
            'payload_byte_length' => $source['payload_byte_length'] ?? null,
            'bounded_payload_body' => $source['bounded_payload_body'] ?? null,
            'outcome_state' => $source['outcome_state'] ?? 'CAPTURED',
            'validation_state' => $source['validation_state'] ?? 'PENDING',
            'reason_code' => $source['reason_code'] ?? null,
            // Provider unit identity travels with the observation that carried the volume, so a
            // stored volume can always be traced to the declaration that made its unit knowable.
            'source_volume_unit_code' => $source['source_volume_unit_code'] ?? null,
            'volume_unit_normalization_factor' => $source['volume_unit_normalization_factor'] ?? null,
            'volume_unit_normalization_state' => $source['volume_unit_normalization_state'] ?? null,
            'volume_unit_evidence_ref' => $source['volume_unit_evidence_ref'] ?? null,
            'supersedes_observation_id' => $source['supersedes_observation_id'] ?? null,
            'created_at' => $source['created_at'] ?? Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ], $overrides);
    }

    private function persistNormalizedRow(array $capture, array $outcome, array $row, $fallbackIndex)
    {
        $instrumentCode = $row['ticker_code'] ?? ($row['benchmark_code'] ?? null);
        if ($instrumentCode === null || trim((string) $instrumentCode) === '') {
            throw new \RuntimeException('SOURCE_OBSERVATION_NORMALIZED_ROW_INVALID: missing instrument code.');
        }
        foreach (['trade_date', 'open', 'high', 'low', 'close', 'volume'] as $field) {
            if (! array_key_exists($field, $row) || $row[$field] === null || $row[$field] === '') {
                throw new \RuntimeException('SOURCE_OBSERVATION_NORMALIZED_ROW_INVALID: missing '.$field.'.');
            }
        }

        $values = $this->normalizedOhlcvValues($row);
        $sourceRowRef = trim((string) ($row['source_row_ref'] ?? 'row:'.$fallbackIndex));
        if ($sourceRowRef === '') {
            $sourceRowRef = 'row:'.$fallbackIndex;
        }

        $provider = $row['provider'] ?? ($capture['provider'] ?? null);
        $providerSymbol = $row['provider_symbol'] ?? ($capture['provider_symbol'] ?? null);
        $listingId = ! empty($row['listing_id']) ? (int) $row['listing_id'] : null;
        $tradeDate = (string) $row['trade_date'];
        $prior = $this->priorNormalizedObservationRow($listingId, $provider, $providerSymbol, (string) $instrumentCode, $tradeDate, (int) $outcome['source_observation_id']);
        $now = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();

        $stored = [
            'source_observation_id' => (int) $outcome['source_observation_id'],
            'capture_observation_id' => (int) $capture['source_observation_id'],
            'source_row_ref' => substr($sourceRowRef, 0, 255),
            'listing_id' => $listingId,
            'provider' => $provider,
            'provider_symbol' => $providerSymbol,
            'provider_mapping_id' => ! empty($row['provider_mapping_id']) ? (int) $row['provider_mapping_id'] : null,
            'mapping_revision' => $row['mapping_revision'] ?? ($capture['mapping_revision'] ?? null),
            'ticker_code' => Str::upper(trim((string) $instrumentCode)),
            'trade_date' => $tradeDate,
            'source_timestamp' => $row['source_timestamp'] ?? ($row['captured_at'] ?? ($capture['source_timestamp'] ?? null)),
            'open_value' => $values['open'],
            'high_value' => $values['high'],
            'low_value' => $values['low'],
            'close_value' => $values['close'],
            'volume_value' => $values['volume'],
            'adj_close_value' => $values['adj_close'],
            'row_fingerprint' => hash('sha256', json_encode([
                $listingId, $provider, $providerSymbol, $tradeDate, $values,
            ], JSON_UNESCAPED_SLASHES)),
            'created_at' => $now,
        ];
        $rowId = (int) DB::table('md_source_observation_rows')->insertGetId($stored);

        if (! $prior) {
            return ['comparison_created' => false, 'divergence_created' => false];
        }

        $current = (object) ($stored + ['source_observation_row_id' => $rowId]);
        $this->persistRevisionComparison($prior, $current, $now);

        return [
            'comparison_created' => true,
            'divergence_created' => (string) $prior->row_fingerprint !== (string) $current->row_fingerprint,
        ];
    }

    private function persistRejectedRow(array $capture, array $outcome, array $row, $fallbackIndex)
    {
        $instrumentCode = Str::upper(trim((string) ($row['ticker_code'] ?? ($row['benchmark_code'] ?? 'UNKNOWN'))));
        $tradeDate = (string) ($row['trade_date'] ?? ($capture['requested_trade_date'] ?? ''));
        $reasonCode = strtoupper(trim((string) ($row['invalid_reason_code'] ?? 'BAR_INVALID_SOURCE_ROW')));
        $sourceRowRef = trim((string) ($row['source_row_ref'] ?? 'rejected-row:'.$fallbackIndex));
        if ($tradeDate === '' || $reasonCode === '') {
            throw new \RuntimeException('SOURCE_OBSERVATION_REJECTED_ROW_INVALID: trade date and reason are required.');
        }

        DB::table('md_source_observation_rejected_rows')->insert([
            'source_observation_id' => (int) $outcome['source_observation_id'],
            'capture_observation_id' => (int) $capture['source_observation_id'],
            'source_row_ref' => substr($sourceRowRef, 0, 255),
            'instrument_code' => $instrumentCode === '' ? 'UNKNOWN' : $instrumentCode,
            'provider_symbol' => $row['provider_symbol'] ?? ($capture['provider_symbol'] ?? null),
            'trade_date' => $tradeDate,
            'open_value' => $this->nullableObservedValue($row['open'] ?? null),
            'high_value' => $this->nullableObservedValue($row['high'] ?? null),
            'low_value' => $this->nullableObservedValue($row['low'] ?? null),
            'close_value' => $this->nullableObservedValue($row['close'] ?? null),
            'volume_value' => $this->nullableObservedValue($row['volume'] ?? null),
            'adj_close_value' => $this->nullableObservedValue($row['adj_close'] ?? null),
            'reason_code' => substr($reasonCode, 0, 64),
            'reason_note' => substr((string) ($row['invalid_note'] ?? 'Provider row failed normalized OHLCV validation.'), 0, 255),
            'created_at' => Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ]);
    }

    private function nullableObservedValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_scalar($value)) {
            return substr((string) $value, 0, 64);
        }

        return substr((string) json_encode($value, JSON_UNESCAPED_SLASHES), 0, 64);
    }

    private function priorNormalizedObservationRow($listingId, $provider, $providerSymbol, $tickerCode, $tradeDate, $currentObservationId)
    {
        $query = DB::table('md_source_observation_rows')
            ->where('trade_date', $tradeDate)
            ->where('source_observation_id', '<>', $currentObservationId);

        if ($listingId !== null) {
            $query->where('listing_id', $listingId);
        } else {
            $query->whereNull('listing_id')
                ->where('provider', $provider)
                ->where('provider_symbol', $providerSymbol)
                ->where('ticker_code', Str::upper(trim($tickerCode)));
        }

        return $query->orderByDesc('source_observation_row_id')->first();
    }

    private function persistRevisionComparison($prior, $current, $now)
    {
        $priorValues = $this->storedOhlcvValues($prior);
        $currentValues = $this->storedOhlcvValues($current);
        $differing = [];
        $deltas = [];
        foreach (array_keys($priorValues) as $field) {
            if ($priorValues[$field] !== $currentValues[$field]) {
                $differing[] = $field;
                $deltas[$field] = $this->numericDelta($priorValues[$field], $currentValues[$field]);
            }
        }

        $isDivergent = $differing !== [];
        $comparisonUid = hash('sha256', implode('|', [
            $prior->source_observation_row_id,
            $current->source_observation_row_id,
            $prior->row_fingerprint,
            $current->row_fingerprint,
        ]));

        DB::table('md_source_observation_revision_comparisons')->insert([
            'comparison_uid' => $comparisonUid,
            'prior_source_observation_row_id' => (int) $prior->source_observation_row_id,
            'current_source_observation_row_id' => (int) $current->source_observation_row_id,
            'prior_source_observation_id' => (int) $prior->source_observation_id,
            'current_source_observation_id' => (int) $current->source_observation_id,
            'listing_id' => $current->listing_id !== null ? (int) $current->listing_id : null,
            'provider' => $current->provider,
            'provider_symbol' => $current->provider_symbol,
            'ticker_code' => $current->ticker_code,
            'trade_date' => $current->trade_date,
            'comparison_state' => $isDivergent ? 'OPEN_DIVERGENCE' : 'CONFIRMED_SAME',
            'divergence_finding_uid' => $isDivergent ? 'MD-SOURCE-DIV-'.strtoupper(substr($comparisonUid, 0, 32)) : null,
            'finding_state' => $isDivergent ? 'OPEN' : 'NOT_APPLICABLE',
            'differing_fields_json' => $isDivergent ? json_encode($differing) : null,
            'prior_values_json' => json_encode($priorValues, JSON_UNESCAPED_SLASHES),
            'current_values_json' => json_encode($currentValues, JSON_UNESCAPED_SLASHES),
            'value_deltas_json' => json_encode($deltas, JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
        ]);
    }

    private function normalizedOhlcvValues(array $row)
    {
        $values = [];
        foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
            $values[$field] = $this->normalizeNumericValue($row[$field], $field);
        }
        $values['adj_close'] = array_key_exists('adj_close', $row) && $row['adj_close'] !== null && $row['adj_close'] !== ''
            ? $this->normalizeNumericValue($row['adj_close'], 'adj_close')
            : null;

        return $values;
    }

    private function storedOhlcvValues($row)
    {
        return [
            'open' => (string) $row->open_value,
            'high' => (string) $row->high_value,
            'low' => (string) $row->low_value,
            'close' => (string) $row->close_value,
            'volume' => (string) $row->volume_value,
            'adj_close' => $row->adj_close_value === null ? null : (string) $row->adj_close_value,
        ];
    }

    private function normalizeNumericValue($value, $field)
    {
        if (! is_numeric($value)) {
            throw new \RuntimeException('SOURCE_OBSERVATION_NORMALIZED_ROW_INVALID: '.$field.' is not numeric.');
        }

        $normalized = rtrim(rtrim(sprintf('%.10F', (float) $value), '0'), '.');
        return $normalized === '-0' || $normalized === '' ? '0' : $normalized;
    }

    private function numericDelta($prior, $current)
    {
        if ($prior === null || $current === null) {
            return null;
        }

        return $this->normalizeNumericValue((float) $current - (float) $prior, 'delta');
    }

    private function schemaFingerprint($payload)
    {
        if ($payload === null) {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (! is_array($decoded)) {
            return hash('sha256', 'non-json|'.strlen($payload));
        }

        $shape = [];
        $this->collectShape($decoded, '$', $shape);
        sort($shape, SORT_STRING);

        return hash('sha256', implode("\n", $shape));
    }

    private function collectShape($value, $path, array &$shape)
    {
        if (! is_array($value)) {
            $shape[] = $path.':'.gettype($value);
            return;
        }

        $shape[] = $path.':array';
        foreach ($value as $key => $child) {
            $segment = is_int($key) ? '[]' : '.'.$key;
            $this->collectShape($child, $path.$segment, $shape);
        }
    }

    /**
     * Redaction must cover every shape a credential arrives in, because this table is immutable:
     * a secret written here is written permanently.
     *
     * The two patterns previously disagreed. `crumb` — Yahoo's session credential — was redacted
     * as a query parameter but survived as a JSON field, so a payload carrying it in the body was
     * stored verbatim. Keeping one keyword list for both shapes is what prevents that class of
     * gap from reopening.
     *
     * Owner contract: docs/market_data/book/Source_Data_Acquisition_Contract_LOCKED.md —
     * "Credential, API key, cookie rahasia, authorization header, dan sensitive query value tidak
     * boleh masuk envelope atau diagnostic sample."
     */
    private function redactSensitiveText($text)
    {
        $keywords = 'token|key|api_key|apikey|crumb|signature|auth|authorization|secret|password|cookie|session|bearer';

        $text = preg_replace('/([?&](?:'.$keywords.')=)[^&\s]+/i', '$1[REDACTED]', (string) $text);
        $text = preg_replace('/("(?:'.$keywords.')"\s*:\s*")[^"]*(")/i', '$1[REDACTED]$2', $text);
        $text = preg_replace('/((?:'.$keywords.')\s*[:=]\s*)(?!\[REDACTED\])[A-Za-z0-9._\-]{8,}/i', '$1[REDACTED]', $text);

        return $text;
    }
}
