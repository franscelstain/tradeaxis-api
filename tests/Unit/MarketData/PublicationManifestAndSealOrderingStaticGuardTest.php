<?php

use App\Application\MarketData\Services\DeterministicHashService;

class PublicationManifestAndSealOrderingStaticGuardTest extends TestCase
{
    private function read(string $path): string
    {
        return file_get_contents(dirname(__DIR__, 3).'/'.$path);
    }

    public function test_complete_seal_orders_snapshot_completeness_hash_and_manifest_before_seal(): void
    {
        $source = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $start = strpos($source, 'public function completeSeal');
        $end = strpos($source, 'public function completeFinalize', $start);
        $method = substr($source, $start, $end - $start);

        $snapshot = strpos($method, 'snapshotPublicationFromCurrentTables');
        $complete = strpos($method, 'assertPublicationSnapshotComplete');
        $manifest = strpos($method, 'prepareCandidateManifestForSeal');
        $seal = strpos($method, 'sealCandidatePublication(');

        $this->assertNotFalse($snapshot);
        $this->assertNotFalse($complete);
        $this->assertNotFalse($manifest);
        $this->assertNotFalse($seal);
        $this->assertTrue($snapshot < $complete && $complete < $manifest && $manifest < $seal);
        $this->assertStringContainsString('DATASET_HASH_MISMATCH', $method);
    }

    public function test_manifest_hash_is_prepared_and_verified_and_partial_candidates_cannot_fake_sealed_state(): void
    {
        $source = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');

        $this->assertStringContainsString('prepareCandidateManifestForSeal', $source);
        $this->assertStringContainsString("'publication_manifest_hash' => \$hash", $source);
        $this->assertStringContainsString("'readiness_state' => 'READABLE'", $source);
        $this->assertStringContainsString('assertPublicationManifestHashValid', $source);
        $this->assertStringContainsString('RUN_SEAL_PRECONDITION_FAILED: partial publication candidates cannot be sealed', $source);
        $this->assertStringContainsString("'BUILDING'", $source);
    }

    public function test_manifest_semantic_payload_contains_locked_binding_families_and_avoids_volatile_execution_identity(): void
    {
        $source = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $start = strpos($source, 'private function publicationManifestSemanticPayload');
        $end = strpos($source, 'public function sealCandidatePublication', $start);
        $method = substr($source, $start, $end - $start);
        $returnStart = strpos($method, 'return [');
        $payload = substr($method, $returnStart);

        foreach ([
            'observation_manifest_hash', 'config_snapshot_hash', 'config_registry_revision',
            'temporal_revision_set_hash', 'identity_revision_set_hash', 'calendar_revision_set_hash',
            'status_revision_set_hash', 'event_revision_set_hash', 'source_scale_assessment_set_hash',
            'market_structure_revision_set_hash', 'factor_decision_set_hash', 'factor_set_hash',
            'price_product_code', 'price_product_version', 'canonicalization_version', 'formula_version',
            'read_model_version', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash',
            'bars_row_count', 'indicators_row_count', 'eligibility_row_count', 'quality_state',
            'coverage_gate_state', 'coverage_ratio', 'readiness_state', 'freshness_state',
        ] as $field) {
            $this->assertStringContainsString("'{$field}'", $payload);
        }

        $this->assertStringNotContainsString("'publication_manifest_hash'", $payload, 'manifest hash must not hash itself');
        $this->assertStringNotContainsString("'sealed_at'", $payload, 'volatile seal timestamp must not define content identity');
        $this->assertStringNotContainsString("'run_id' =>", $payload, 'execution identity must not contaminate semantic manifest hash');
        $this->assertStringNotContainsString("'publication_id' =>", $payload, 'database identity must not contaminate semantic manifest hash');
        $this->assertStringNotContainsString("'sorted_reasons'", $payload, 'mutable run terminal reasons must not invalidate an already sealed publication manifest');
        $this->assertStringNotContainsString('final_reason_code', $payload, 'mutable run terminal reasons belong to operational evidence, not sealed semantic identity');
    }

    public function test_canonical_document_hash_is_order_independent_but_semantic_change_sensitive(): void
    {
        $hashes = new DeterministicHashService();
        $left = ['b' => 2, 'a' => ['y' => 2, 'x' => 1]];
        $same = ['a' => ['x' => 1, 'y' => 2], 'b' => 2];
        $changed = ['a' => ['x' => 1, 'y' => 3], 'b' => 2];

        $this->assertSame($hashes->hashCanonicalDocument($left), $hashes->hashCanonicalDocument($same));
        $this->assertNotSame($hashes->hashCanonicalDocument($left), $hashes->hashCanonicalDocument($changed));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hashes->hashCanonicalDocument($left));
    }
}
