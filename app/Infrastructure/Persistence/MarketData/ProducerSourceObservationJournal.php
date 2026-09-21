<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** Append-only acquisition journal. This is not a declaration of C06 completeness. */
final class ProducerSourceObservationJournal
{
    public static function record(int $observationId): void
    {
        if (! ProducerInputScope::active()) return;
        ProducerInputScope::observationJournal([
            'operation' => 'source-observation-journal/v1', 'source_observation_id' => $observationId,
        ], static function () use ($observationId) {
            $row = DB::table('md_source_observations')->where('source_observation_id', $observationId)->first();
            if (! $row) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_MISSING');
            $references = [];
            foreach (['parent_observation_id', 'supersedes_observation_id'] as $field) {
                $id = $row->$field ?? null;
                $reference = $id === null ? null : DB::table('md_source_observations')->where('source_observation_id', $id)->first();
                if ($id !== null && ! $reference) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_REFERENCE_MISSING: '.$field);
                $references[$field] = $reference === null ? null : (array) $reference;
            }
            $payload = ['observation' => (array) $row, 'references' => $references,
                'payload_basis' => 'PERSISTED_HASH_REFERENCE_AND_BOUNDED_REDACTED_DIAGNOSTIC',
                'scope' => 'PERSISTED_OBSERVATION_ONLY_NOT_CONSUMPTION_COMPLETENESS'];
            self::assertValid($payload, $observationId);
            return [$payload];
        });
    }

    /** Validate the captured content without looking up a live/current observation. */
    public static function assertValid(array $payload, int $observationId): void
    {
        $row = $payload['observation'] ?? [];
        if ((int) ($row['source_observation_id'] ?? 0) !== $observationId || $observationId <= 0) {
            throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_IDENTITY');
        }
        foreach (['observation_uid', 'parent_observation_id', 'supersedes_observation_id', 'run_id', 'attempt_uid',
            'acquisition_batch_id', 'requested_trade_date', 'requested_start_date', 'requested_end_date',
            'source_mode', 'source_name', 'provider', 'provider_symbol', 'provider_mapping_id', 'mapping_revision',
            'sanitized_request_identity', 'source_timestamp', 'acquired_at', 'adapter_version', 'provider_schema_version',
            'schema_fingerprint', 'payload_hash', 'payload_ref', 'payload_byte_length', 'bounded_payload_body',
            'outcome_state', 'validation_state', 'reason_code', 'created_at'] as $field) {
            if (! array_key_exists($field, $row)) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_FIELD: '.$field);
        }
        if (($payload['payload_basis'] ?? null) !== 'PERSISTED_HASH_REFERENCE_AND_BOUNDED_REDACTED_DIAGNOSTIC'
            || ($payload['scope'] ?? null) !== 'PERSISTED_OBSERVATION_ONLY_NOT_CONSUMPTION_COMPLETENESS') {
            throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_BASIS');
        }
        foreach (['parent_observation_id', 'supersedes_observation_id'] as $field) {
            if (! array_key_exists($field, $payload['references'] ?? [])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_REFERENCE_MISSING');
            $ref = $payload['references'][$field];
            if ($row[$field] === null ? $ref !== null : (! is_array($ref) || (int) ($ref['source_observation_id'] ?? 0) !== (int) $row[$field])) {
                throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_REFERENCE_IDENTITY');
            }
        }
        $parent = $payload['references']['parent_observation_id'];
        if ($parent !== null) {
            if (($parent['outcome_state'] ?? null) !== 'CAPTURED') throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_PARENT_STATE');
            foreach (['payload_hash', 'payload_ref', 'payload_byte_length', 'schema_fingerprint'] as $field) {
                if (! array_key_exists($field, $parent) || (string) $parent[$field] !== (string) $row[$field]) {
                    throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_PARENT_PAYLOAD');
                }
            }
        }
    }
}
