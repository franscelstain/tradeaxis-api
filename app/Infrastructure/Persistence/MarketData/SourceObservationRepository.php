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

    public function recordTransportFailure(array $envelope, $reasonCode)
    {
        $capture = $this->capture($envelope);

        return $this->recordOutcome($capture, 'FAILED', $reasonCode);
    }

    public function existsAccepted($observationId)
    {
        return DB::table('md_source_observations')
            ->where('source_observation_id', $observationId)
            ->whereIn('outcome_state', ['ACCEPTED', 'NORMALIZED'])
            ->exists();
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
            'supersedes_observation_id' => $source['supersedes_observation_id'] ?? null,
            'created_at' => $source['created_at'] ?? Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString(),
        ], $overrides);
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
