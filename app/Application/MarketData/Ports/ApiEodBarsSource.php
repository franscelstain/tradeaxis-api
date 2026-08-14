<?php

namespace App\Application\MarketData\Ports;

interface ApiEodBarsSource
{
    public function fetchOrLoadEodBars($tradeDate, $sourceMode, array $tickerCodes = [], array $context = []);

    public function fetchOrLoadEodBarsRange($startDate, $endDate, $sourceMode, array $tickerCodes, array $tradingDates, array $context = []);

    public function fetchOrLoadBenchmarkBars($tradeDate, $sourceMode, array $benchmarks = [], array $context = []);

    public function consumeLastAcquisitionTelemetry();

    public function capabilities();
}
