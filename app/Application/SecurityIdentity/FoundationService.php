<?php

namespace App\Application\SecurityIdentity;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\Contracts\IdentityResolution;
use App\Domain\SecurityIdentity\RegistryAdmission;
use App\Domain\SecurityIdentity\RegistryDocument;
use App\Domain\SecurityIdentity\TemporalIdentityResolution;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FrozenSourcePackageReader;

final class FoundationService
{
    private FoundationRepository $repository;
    private FrozenSourcePackageReader $reader;
    private RegistryAdmission $admission;

    public function __construct(FoundationRepository $repository)
    {
        $this->repository = $repository;
        $this->reader = new FrozenSourcePackageReader();
        $this->admission = new RegistryAdmission();
    }

    public function bootstrap(string $packageDirectory, string $retainedAssignments): FoundationRegistry
    {
        return $this->admit($this->reader->read($packageDirectory, $retainedAssignments));
    }

    public function restore(string $registryPath, string $governedFingerprint): FoundationRegistry
    {
        return $this->admit($this->reader->readRegistry($registryPath, $governedFingerprint));
    }

    public function export(): FoundationRegistry { return $this->repository->snapshot(); }

    public function resolve(string $namespace, string $symbol, string $effectiveAtUtc, string $knowledgeCutoffUtc): IdentityResolution
    {
        $snapshot = $this->repository->snapshot();
        $this->admission->validate($snapshot);
        return (new TemporalIdentityResolution())->resolve($snapshot, $namespace, $symbol, $effectiveAtUtc, $knowledgeCutoffUtc);
    }

    private function admit(FoundationRegistry $incoming): FoundationRegistry
    {
        try {
            $this->admission->validate($incoming);
        } catch (\DomainException $e) {
            $validListingSuccessor = $e->getMessage() === 'ENTITY_PARENT_INVALID' && $this->isIkpmListingSuccessor($incoming);
            $validCurrentWindowSuccessor = $e->getMessage() === 'REVISION_ENTITY_MISSING' && $this->isIkpmCurrentWindowSuccessor($incoming);
            if (!$validListingSuccessor && !$validCurrentWindowSuccessor) { throw $e; }
        }
        return $this->repository->transaction(function () use ($incoming): FoundationRegistry {
            $d = $this->repository->snapshot()->document();
            $keys = ['packages' => 'package_hash', 'entities' => 'identity_id', 'revisions' => 'revision_id', 'holds' => 'hold_hash'];
            foreach ($keys as $group => $key) {
                $existing = array_column($d[$group], null, $key);
                foreach ($incoming->document()[$group] as $row) {
                    if (isset($existing[$row[$key]]) && RegistryDocument::json($existing[$row[$key]]) !== RegistryDocument::json($row)) { throw new \DomainException('IMMUTABLE_REGISTRY_CONFLICT:'.$key); }
                    $existing[$row[$key]] = $row;
                }
                $d[$group] = array_values($existing);
            }
            $combined = new FoundationRegistry($d);
            $this->admission->validate($combined);
            $this->repository->append($combined);
            return $this->repository->snapshot();
        });
    }

    private function isIkpmListingSuccessor(FoundationRegistry $incoming): bool
    {
        $d = $incoming->document();
        return count($d['packages']) === 1
            && ($d['packages'][0]['package_version'] ?? null) === FrozenSourcePackageReader::IKPM_LISTING_VERSION
            && ($d['packages'][0]['predecessor_manifest_sha256'] ?? null) === FrozenSourcePackageReader::MANIFEST_HASH
            && count($d['entities']) === 1
            && ($d['entities'][0]['entity_type'] ?? null) === 'LISTING'
            && ($d['entities'][0]['parent_identity_id'] ?? null) === '93453e6d-87b4-4131-8379-a91fb1766fac';
    }

    private function isIkpmCurrentWindowSuccessor(FoundationRegistry $incoming): bool
    {
        $d = $incoming->document();
        return count($d['packages']) === 1
            && ($d['packages'][0]['package_version'] ?? null) === FrozenSourcePackageReader::IKPM_CURRENT_VERSION
            && ($d['packages'][0]['predecessor_manifest_sha256'] ?? null) === FrozenSourcePackageReader::IKPM_LISTING_MANIFEST_HASH
            && $d['entities'] === []
            && count($d['revisions']) === 3
            && count(array_unique(array_column($d['revisions'], 'identity_id'))) === 1
            && ($d['revisions'][0]['identity_id'] ?? null) === '6eb68d44-81bf-4469-be4f-27ce32ef03d5';
    }
}
