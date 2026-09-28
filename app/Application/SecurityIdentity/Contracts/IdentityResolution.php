<?php

namespace App\Application\SecurityIdentity\Contracts;

final class IdentityResolution
{
    public string $state;
    public string $reason;
    public ?string $issuerId;
    public ?string $instrumentId;
    public ?string $listingId;
    public array $context;

    public function __construct(string $state, string $reason, ?string $issuerId = null, ?string $instrumentId = null, ?string $listingId = null, array $context = [])
    {
        $this->state = $state;
        $this->reason = $reason;
        $this->issuerId = $issuerId;
        $this->instrumentId = $instrumentId;
        $this->listingId = $listingId;
        $this->context = $context;
    }
}
