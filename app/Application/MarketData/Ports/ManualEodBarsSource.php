<?php

namespace App\Application\MarketData\Ports;

interface ManualEodBarsSource
{
    public function fetchOrLoadEodBars($tradeDate, $sourceMode, array $tickerCodes = [], array $context = []);

    public function consumeLastAcquisitionTelemetry();
}
