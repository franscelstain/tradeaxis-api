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

    public function recordTransportFailure(array $envelope, $reasonCode)
    {
        return $this->recordOutcome($this->capture($envelope), 'FAILED', $reasonCode);
    }

    public function rows()
    {
        return $this->rows;
    }
}
