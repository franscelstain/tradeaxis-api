<?php

namespace App\Application\MarketData\Ports;

interface ExchangeMarketStructureEvidenceVerifier
{
    public function verify(array $sourceDocument);
}
