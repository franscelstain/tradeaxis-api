<?php

namespace App\Application\MarketData\Ports;

interface AuthoritativeTradingStatusEvidenceVerifier
{
    public function verifySnapshot(array $source, array $expectedEntries);

    public function verifyTransitionSearch(array $source, array $tickerCodes);
}
