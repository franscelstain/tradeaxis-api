<?php

namespace App\Application\MarketData\Ports;

interface IncidentResponsePolicy
{
    public function assertActionAllowed(string $date, string $action, ?int $targetRunId = null): void;
}
