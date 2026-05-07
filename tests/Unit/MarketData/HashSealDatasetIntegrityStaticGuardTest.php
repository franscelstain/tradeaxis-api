<?php

class HashSealDatasetIntegrityStaticGuardTest extends TestCase
{
    public function test_hash_service_is_config_driven_and_input_order_independent(): void
    {
        $service = file_get_contents(base_path('app/Application/MarketData/Services/DeterministicHashService.php'));

        foreach (['market_data.hash.algorithm', 'market_data.hash.delimiter', 'market_data.hash.line_separator', 'market_data.hash.null_token', 'sort($lines, SORT_STRING)', 'encodeCanonicalValue'] as $needle) {
            $this->assertStringContainsString($needle, $service);
        }

        $this->assertStringNotContainsString("hash('sha256'", $service);
    }

    public function test_hash_stage_records_hash_contract_context(): void
    {
        $pipeline = file_get_contents(base_path('app/Application/MarketData/Services/MarketDataPipelineService.php'));

        foreach (['DATASET_HASH_CREATED', 'hash_algorithm', 'hash_delimiter', 'hash_line_separator', 'hash_null_token', 'canonical_ordering_rule', 'orderBy($dateColumn)', "orderBy('ticker_id')"] as $needle) {
            $this->assertStringContainsString($needle, $pipeline);
        }
    }

    public function test_seal_and_finalize_reject_missing_or_mismatched_integrity_context(): void
    {
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));

        foreach (['assertPublicationIntegrityContextComplete', 'DATASET_HASH_MISSING', 'DATASET_MANIFEST_INVALID', 'assertSealedPublicationMatchesRunHashes', 'FINALIZE_HASH_MISSING', 'FINALIZE_HASH_MISMATCH', 'FINALIZE_SEAL_MISSING', 'FINALIZE_SEAL_INVALID'] as $needle) {
            $this->assertStringContainsString($needle, $repository);
        }
    }

    public function test_sealed_current_artifact_mutation_is_blocked(): void
    {
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php'));

        foreach (['assertLiveArtifactMutationAllowed', 'SEALED_DATASET_MUTATION_BLOCKED', "pub.seal_state', 'SEALED'", "pub.is_current', 1", "run.terminal_status', 'SUCCESS'", "run.publishability_state', 'READABLE'", 'Use correction history flow instead of mutating the baseline dataset'] as $needle) {
            $this->assertStringContainsString($needle, $repository);
        }
    }

    public function test_manifest_contains_hash_seal_source_coverage_and_column_contract(): void
    {
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));

        foreach (['manifest_schema_version', 'market_data_dataset_integrity_manifest_v1', 'hash_algorithm', 'hash_delimiter', 'hash_line_separator', 'hash_null_token', 'canonical_ordering_rule', 'component_hashes', 'component_row_counts', 'component_column_contract', 'coverage_context', 'source_context', 'seal_verification_status', 'seal_verification_reason_code'] as $needle) {
            $this->assertStringContainsString($needle, $repository);
        }
    }

    public function test_reason_code_registry_and_seed_include_dataset_integrity_codes(): void
    {
        $registry = file_get_contents(base_path('docs/market_data/registry/Reason_Codes_Registry.md'));
        $seed = file_get_contents(base_path('docs/market_data/registry/Reason_Codes_Seed.sql'));

        foreach (['DATASET_HASH_CREATED', 'DATASET_HASH_VERIFIED', 'DATASET_HASH_MISSING', 'DATASET_HASH_MISMATCH', 'DATASET_MANIFEST_INVALID', 'DATASET_SEAL_INVALID', 'SEALED_DATASET_MUTATION_BLOCKED', 'FINALIZE_HASH_MISSING', 'FINALIZE_HASH_MISMATCH', 'FINALIZE_SEAL_MISSING', 'FINALIZE_SEAL_INVALID'] as $needle) {
            $this->assertStringContainsString($needle, $registry);
            $this->assertStringContainsString($needle, $seed);
        }
    }

    public function test_command_summary_outputs_integrity_context(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/MarketData/AbstractMarketDataCommand.php'));

        foreach (['renderDatasetIntegritySummary', 'hash_algorithm', 'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash', 'seal_state', 'sealed_at', 'sealed_by'] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }
    }
}
