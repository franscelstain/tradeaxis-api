<?php

namespace App\Application\MarketData\Ports;

interface SourceObservationRecorder
{
    public function capture(array $envelope);

    public function recordOutcome(array $capture, $outcomeState, $reasonCode = null, array $context = []);

    public function recordTransportFailure(array $envelope, $reasonCode);
}
