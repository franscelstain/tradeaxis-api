<?php

namespace App\Application\MarketData\Services;

/**
 * Allocation-independent identity for V2 publication, correction, and seal material.
 *
 * Numeric database keys may be used by repositories to locate the records from which these
 * documents are assembled. They are deliberately absent from every document accepted here.
 */
class PublicationSemanticIdentityService
{
    public const LEGACY_PROFILE_V1 = 'market-data-publication-semantic/v1';
    public const PROFILE_V2 = 'market-data-publication-semantic/v2';
    public const SEAL_CONTRACT_V2 = 'dataset_seal_v2';

    private DeterministicHashService $hashes;

    public function __construct(?DeterministicHashService $hashes = null)
    {
        $this->hashes = $hashes ?: new DeterministicHashService();
    }

    public function profileForArtifactProfile(?string $artifactProfile): string
    {
        $artifactProfile = trim((string) $artifactProfile);
        if ($artifactProfile === '' || $artifactProfile === ArtifactSemanticHashService::LEGACY_PROFILE_V1) {
            return self::LEGACY_PROFILE_V1;
        }
        if ($artifactProfile === ArtifactSemanticHashService::PROFILE_V2) {
            return self::PROFILE_V2;
        }

        throw new \RuntimeException('PUBLICATION_SEMANTIC_PROFILE_UNSUPPORTED: artifact='.$artifactProfile);
    }

    public function assertCompatible(?string $artifactProfile, ?string $runProfile, ?string $publicationProfile): string
    {
        $expected = $this->profileForArtifactProfile($artifactProfile);
        foreach (['run' => $runProfile, 'publication' => $publicationProfile] as $owner => $actual) {
            $actual = trim((string) $actual);
            if ($actual === '' && $expected === self::LEGACY_PROFILE_V1) {
                continue;
            }
            if (!hash_equals($expected, $actual)) {
                throw new \RuntimeException(
                    'PUBLICATION_SEMANTIC_PROFILE_MISMATCH: '.$owner.' expected='.$expected.' actual='.($actual ?: 'NULL')
                );
            }
        }

        return $expected;
    }

    public function publicationHash(array $payload): string
    {
        return $this->hashDocument('publication_manifest', $payload);
    }

    public function correctionHash(array $payload): string
    {
        return $this->hashDocument('correction_republication', $payload);
    }

    public function sealFingerprint(array $payload): string
    {
        return $this->hashDocument('dataset_seal', $payload);
    }

    private function hashDocument(string $domain, array $payload): string
    {
        $this->assertNoVolatileMembers($payload);

        return $this->hashes->hashCanonicalDocument([
            'semantic_profile' => self::PROFILE_V2,
            'domain' => $domain,
            'payload' => $payload,
        ]);
    }

    private function assertNoVolatileMembers(array $payload, string $path = ''): void
    {
        foreach ($payload as $key => $value) {
            $member = $path === '' ? (string) $key : $path.'.'.$key;
            if (is_string($key) && preg_match('/(^|_)(run|publication|correction|config_snapshot|factor_set|source_observation)_id$/', $key)) {
                throw new \RuntimeException('SEMANTIC_IDENTITY_VOLATILE_MEMBER: '.$member);
            }
            if (is_array($value)) $this->assertNoVolatileMembers($value, $member);
        }
    }
}
