<?php

namespace App\Application\SecurityIdentity\Contracts;

use App\Domain\SecurityIdentity\RegistryDocument;

/** Inert, versioned handoff; entity/revision shapes are checked by RegistryAdmission. */
final class FoundationRegistry
{
    private array $document;

    public function __construct(array $document)
    {
        foreach (['packages', 'entities', 'revisions', 'holds'] as $field) {
            if (!isset($document[$field]) || !is_array($document[$field])) {
                throw new \InvalidArgumentException('REGISTRY_SHAPE_INVALID:'.$field);
            }
        }
        if (($document['registry_version'] ?? null) !== 'security-identity-registry/v1') {
            throw new \InvalidArgumentException('REGISTRY_VERSION_UNSUPPORTED');
        }
        $keys = ['packages' => 'package_hash', 'entities' => 'identity_id', 'revisions' => 'revision_id', 'holds' => 'hold_hash'];
        foreach ($keys as $field => $key) {
            usort($document[$field], static fn (array $a, array $b): int => strcmp($a[$key] ?? '', $b[$key] ?? ''));
        }
        $this->document = $document;
    }

    public function document(): array { return $this->document; }
    public function json(): string { return RegistryDocument::json($this->document); }
}
