<?php

namespace App\Application\SecurityIdentity\Contracts;

/** Shared read contract. Consumers supply both effective and knowledge instants explicitly. */
interface IdentityResolver
{
    public function resolve(
        string $namespace,
        string $symbol,
        string $effectiveAtUtc,
        string $knowledgeCutoffUtc
    ): IdentityResolution;
}
