<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;

class PublicationSemanticIdentityServiceTest extends TestCase
{
    private function publication(): array
    {
        return [
            'market_scope' => 'IDX_REGULAR_EOD', 'trade_date' => '2026-09-29',
            'publication_version' => '2',
            'lineage' => ['supersedes_manifest_hash' => hash('sha256', 'prior')],
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
            'config_content_hash' => hash('sha256', 'config'),
            'observation_manifest_hash' => hash('sha256', 'observations'),
            'artifacts' => [
                'bars' => hash('sha256', 'bars'), 'indicators' => hash('sha256', 'indicators'),
                'eligibility' => hash('sha256', 'eligibility'),
            ],
            'row_counts' => ['bars' => 1, 'indicators' => 1, 'eligibility' => 1],
            'seal_contract_version' => PublicationSemanticIdentityService::SEAL_CONTRACT_V2,
        ];
    }

    public function test_repeated_and_reordered_documents_have_one_identity(): void
    {
        $service = new PublicationSemanticIdentityService();
        $a = $this->publication();
        $b = array_reverse($a, true);
        $this->assertSame($service->publicationHash($a), $service->publicationHash($a));
        $this->assertSame($service->publicationHash($a), $service->publicationHash($b));
    }

    public function test_each_artifact_root_lineage_and_config_are_semantically_sensitive(): void
    {
        $service = new PublicationSemanticIdentityService();
        $base = $this->publication();
        foreach (['bars', 'indicators', 'eligibility'] as $artifactName) {
            $artifact = $base;
            $artifact['artifacts'][$artifactName] = hash('sha256', 'changed-'.$artifactName);
            $this->assertNotSame(
                $service->publicationHash($base),
                $service->publicationHash($artifact),
                $artifactName.' V2 root must participate in publication identity'
            );
        }
        $config = $base; $config['config_content_hash'] = hash('sha256', 'changed-config');
        $this->assertNotSame($service->publicationHash($base), $service->publicationHash($config));
        $lineage = $base; $lineage['lineage']['supersedes_manifest_hash'] = hash('sha256', 'changed-prior');
        $this->assertNotSame($service->publicationHash($base), $service->publicationHash($lineage));
    }

    public function test_correction_and_seal_identities_are_sensitive_to_semantic_roots(): void
    {
        $service = new PublicationSemanticIdentityService();
        $correction = [
            'trade_date' => '2026-09-29', 'reason_code' => 'SOURCE_CORRECTION',
            'baseline_publication_manifest_hash' => hash('sha256', 'prior'),
            'replacement_artifact_hashes' => [
                'bars' => hash('sha256', 'bars'),
                'indicators' => hash('sha256', 'indicators'),
                'eligibility' => hash('sha256', 'eligibility'),
            ],
            'config_content_hash' => hash('sha256', 'config'),
        ];
        foreach (['bars', 'indicators', 'eligibility'] as $artifactName) {
            $changed = $correction;
            $changed['replacement_artifact_hashes'][$artifactName] = hash('sha256', 'changed-'.$artifactName);
            $this->assertNotSame($service->correctionHash($correction), $service->correctionHash($changed));
        }
        $changedReason = $correction; $changedReason['reason_code'] = 'LATE_SOURCE_CORRECTION';
        $this->assertNotSame($service->correctionHash($correction), $service->correctionHash($changedReason));
        $changedBaseline = $correction; $changedBaseline['baseline_publication_manifest_hash'] = hash('sha256', 'changed-prior');
        $this->assertNotSame($service->correctionHash($correction), $service->correctionHash($changedBaseline));
        $changedConfig = $correction; $changedConfig['config_content_hash'] = hash('sha256', 'changed-config');
        $this->assertNotSame($service->correctionHash($correction), $service->correctionHash($changedConfig));

        $seal = [
            'publication_manifest_hash' => $service->publicationHash($this->publication()),
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
            'seal_contract_version' => PublicationSemanticIdentityService::SEAL_CONTRACT_V2,
            'provenance_scope' => 'FULL',
        ];
        $changedSeal = $seal; $changedSeal['publication_manifest_hash'] = hash('sha256', 'other');
        $this->assertNotSame($service->sealFingerprint($seal), $service->sealFingerprint($changedSeal));
    }

    public function test_volatile_identifiers_are_rejected_at_any_depth(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SEMANTIC_IDENTITY_VOLATILE_MEMBER');
        (new PublicationSemanticIdentityService())->publicationHash([
            'lineage' => ['previous_publication_id' => 42],
        ]);
    }

    public function test_profile_binding_preserves_legacy_and_rejects_mixed_v2(): void
    {
        $service = new PublicationSemanticIdentityService();
        $this->assertSame(
            PublicationSemanticIdentityService::LEGACY_PROFILE_V1,
            $service->assertCompatible(null, null, null)
        );
        $this->assertSame(
            PublicationSemanticIdentityService::PROFILE_V2,
            $service->assertCompatible(
                ArtifactSemanticHashService::PROFILE_V2,
                PublicationSemanticIdentityService::PROFILE_V2,
                PublicationSemanticIdentityService::PROFILE_V2
            )
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PUBLICATION_SEMANTIC_PROFILE_MISMATCH');
        $service->assertCompatible(ArtifactSemanticHashService::PROFILE_V2, null, PublicationSemanticIdentityService::PROFILE_V2);
    }

    public function test_unknown_artifact_profile_fails_closed_without_legacy_fallback(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PUBLICATION_SEMANTIC_PROFILE_UNSUPPORTED');
        (new PublicationSemanticIdentityService())->assertCompatible('unknown-profile/v9', null, null);
    }
}
