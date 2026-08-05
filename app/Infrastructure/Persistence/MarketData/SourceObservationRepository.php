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
        $payloadHash = $payload !== null ? hash('sha256', $payload) : null;
        $now = $envelope['acquired_at'] ?? Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();
        $boundedBytes = max(0, (int) config('market_data.source.bounded_payload_bytes', 65536));
        $boundedBody = $payload === null ? null : $this->redactSensitiveText(substr($payload, 0, $boundedBytes));

        $row = $this->baseRow($envelope, [
            'observation_uid' => (string) Str::uuid(),
            'acquired_at' => $now,
            'schema_fingerprint' => $this->schemaFingerprint($payload),
            'payload_hash' => $payloadHash,
            'payload_ref' => $payloadHash ? 'sha256:'.$payloadHash : null,
            'bounded_payload_body' => $boundedBody,
            'payload_byte_length' => $payload === null ? null : strlen($payload),
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
            'sanitized_request_identity' => substr((string) ($source['sanitized_request_identity'] ?? 'unavailable'), 0, 255),
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

    private function redactSensitiveText($text)
    {
        $text = preg_replace('/([?&](?:token|key|api_key|crumb|signature|auth|authorization)=)[^&\s]+/i', '$1[REDACTED]', (string) $text);
        $text = preg_replace('/("(?:token|secret|password|cookie|authorization|api_key)"\s*:\s*")[^"]*(")/i', '$1[REDACTED]$2', $text);

        return $text;
    }
}
