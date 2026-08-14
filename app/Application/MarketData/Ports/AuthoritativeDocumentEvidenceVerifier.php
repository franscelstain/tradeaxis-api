<?php

namespace App\Application\MarketData\Ports;

interface AuthoritativeDocumentEvidenceVerifier
{
    /**
     * Verifies the bytes currently served by the declared authoritative HTTPS document.
     * Returns normally only when length, content type, and SHA-256 match the declaration.
     */
    public function verify(array $sourceDocument);
}
