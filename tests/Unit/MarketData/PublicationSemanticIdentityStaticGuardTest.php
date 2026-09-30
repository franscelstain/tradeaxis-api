<?php

class PublicationSemanticIdentityStaticGuardTest extends TestCase
{
    private function source(string $path): string
    {
        return file_get_contents(dirname(__DIR__, 3).'/'.$path);
    }

    public function test_v2_manifest_uses_semantic_lineage_and_v2_artifact_roots(): void
    {
        $source = $this->source('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $start = strpos($source, 'private function publicationManifestSemanticPayloadV2');
        $end = strpos($source, 'private function assertSemanticProfilesCompatible', $start);
        $method = substr($source, $start, $end - $start);
        $this->assertStringContainsString("'supersedes_manifest_hash'", $method);
        $this->assertStringContainsString("'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2", $method);
        $this->assertStringContainsString("'config_content_hash'", $method);
        $this->assertStringNotContainsString("'supersedes_publication_id'", $method);
        $this->assertStringNotContainsString("'correction_id'", $method);
        $this->assertStringNotContainsString("'config_snapshot_id'", $method);
    }

    public function test_legacy_v1_payload_remains_separate_and_unchanged_in_meaning(): void
    {
        $source = $this->source('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $start = strpos($source, 'private function publicationManifestSemanticPayloadV1');
        $end = strpos($source, 'private function publicationManifestSemanticPayloadV2', $start);
        $method = substr($source, $start, $end - $start);
        $this->assertStringContainsString("'supersedes_publication_id'", $method);
        $this->assertStringContainsString("'correction_id'", $method);
        $this->assertStringContainsString("'seal_contract_version' => 'dataset_seal_v1'", $method);
    }

    public function test_schema_and_migration_expose_additive_profile_and_identity_fields(): void
    {
        $migration = $this->source('database/migrations/2026_09_29_000003_add_publication_semantic_identity_profile.php');
        $schema = $this->source('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');
        foreach (['publication_semantic_profile', 'correction_semantic_hash', 'seal_fingerprint', 'semantic_identity_hash'] as $field) {
            $this->assertStringContainsString($field, $migration);
            $this->assertStringContainsString($field, $schema);
        }
    }
}
