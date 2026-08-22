<?php

namespace App\Infrastructure\MarketData\Observation;

use App\Application\MarketData\Ports\SourceObservationRecorder;

class InMemorySourceObservationRecorder implements SourceObservationRecorder
{
    private $nextId = 1;
    private $rows = [];

    public function capture(array $envelope)
    {
        $payload = array_key_exists('payload', $envelope) ? (string) $envelope['payload'] : null;
        if ($payload === null && ! array_key_exists('payload_hash', $envelope)) {
            throw new \RuntimeException('SOURCE_OBSERVATION_PAYLOAD_IDENTITY_REQUIRED: capture requires payload bytes or verifiable external identity.');
        }
        $row = array_merge($envelope, [
            'source_observation_id' => $this->nextId++,
            'observation_uid' => hash('sha256', uniqid('observation', true)),
            'payload_hash' => $payload === null ? null : hash('sha256', $payload),
            'payload_byte_length' => $payload === null ? null : strlen($payload),
            'outcome_state' => 'CAPTURED',
            'persisted' => false,
        ]);
        $this->rows[] = $row;

        return $row;
    }

    public function recordOutcome(array $capture, $outcomeState, $reasonCode = null, array $context = [])
    {
        $row = array_merge($capture, $context, [
            'source_observation_id' => $this->nextId++,
            'parent_observation_id' => $capture['source_observation_id'] ?? null,
            'observation_uid' => hash('sha256', uniqid('observation-outcome', true)),
            'outcome_state' => strtoupper((string) $outcomeState),
            'reason_code' => $reasonCode,
            'persisted' => false,
        ]);
        $this->rows[] = $row;

        return $row;
    }

    public function recordAcceptedRows(array $capture, array $rows, array $rejectedRows = [])
    {
        $outcome = $this->recordOutcome($capture, 'ACCEPTED', null, [
            'normalized_rows' => array_values($rows),
            'normalized_rejected_rows' => array_values($rejectedRows),
        ]);

        return $outcome + [
            'normalized_row_count' => count($rows),
            'comparison_count' => 0,
            'divergence_finding_count' => 0,
            'rejected_row_count' => count($rejectedRows),
        ];
    }

    public function recordRejectedRows(array $capture, array $rejectedRows, $reasonCode)
    {
        return $this->recordOutcome($capture, 'REJECTED', $reasonCode, [
            'normalized_rejected_rows' => array_values($rejectedRows),
        ]);
    }

    public function recordTransportFailure(array $envelope, $reasonCode)
    {
        $envelope['content_type'] = 'application/vnd.tradeaxis.source-transport-failure+json';
        $envelope['payload'] = json_encode([
            'observation_type' => 'TRANSPORT_FAILURE',
            'reason_code' => strtoupper((string) $reasonCode),
            'requested_trade_date' => $envelope['requested_trade_date'] ?? ($envelope['trade_date'] ?? null),
            'sanitized_request_identity' => $envelope['sanitized_request_identity'] ?? 'unavailable',
        ], JSON_UNESCAPED_SLASHES);

        return $this->recordOutcome($this->capture($envelope), 'FAILED', $reasonCode);
    }

    public function rows()
    {
        return $this->rows;
    }
}
