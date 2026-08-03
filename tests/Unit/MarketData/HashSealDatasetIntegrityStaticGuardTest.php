<?php

/**
 * Most of this file has moved to tests that run the code.
 *
 * - The hashing contract was asserted as six strings present in DeterministicHashService.
 *   DeterministicHashServiceTest already hashes real rows and proves the properties those
 *   strings stand for: input order does not change the hash, numeric shape variation does not
 *   change it, null and empty string stay distinguishable, and a changed value changes it.
 * - The reason-code list was checked against the registry and the seed file by hand.
 *   ReasonCodeSeedExecutionTest executes the seed and proves registry and table match exactly,
 *   and EmittedReasonCodeRegistrationTest derives the emitted codes from the runtime.
 * - The sealed-artifact mutation block was asserted as seven source strings.
 *   SealedArtifactMutationGuardTest drives it: a sealed and readable publication refuses
 *   replacement even after it stops being current, while an unsealed candidate stays replaceable.
 * - Which columns the hash actually covers was checked by nothing at all. That is the gap that
 *   mattered, and it is now PublishedColumnHashCoverageTest: it reads the live table definitions
 *   and the hash column lists and proves neither can drift from the other.
 *
 * What remains are prohibitions and cross-artifact wiring that execution cannot reach.
 */
class HashSealDatasetIntegrityStaticGuardTest extends TestCase
{
    /**
     * The algorithm is configuration, not a literal. A hardcoded sha256 call would ignore
     * market_data.hash.algorithm, so a deployment that configured a different algorithm would
     * silently keep producing the old one and every stored hash would still verify.
     */
    public function test_the_hash_algorithm_is_never_hardcoded(): void
    {
        $service = file_get_contents(base_path('app/Application/MarketData/Services/DeterministicHashService.php'));

        $this->assertStringNotContainsString("hash('sha256'", $service);
        $this->assertStringContainsString('market_data.hash.algorithm', $service);
    }

    /**
     * The hash stage records the parameters the hash was produced under. Without them a stored
     * hash cannot be re-derived years later, because the delimiter, separator and null token
     * that shaped it would be unknown.
     */
    public function test_the_hash_stage_records_the_parameters_the_hash_was_produced_under(): void
    {
        $pipeline = file_get_contents(base_path('app/Application/MarketData/Services/MarketDataPipelineService.php'));

        foreach ([
            'DATASET_HASH_CREATED',
            'hash_algorithm',
            'hash_delimiter',
            'hash_line_separator',
            'hash_null_token',
            'canonical_ordering_rule',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pipeline);
        }
    }

    /**
     * The integrity manifest is the artifact an auditor reads instead of the database. These are
     * its required sections; nothing else asserts that the manifest still carries them.
     */
    public function test_manifest_contains_hash_seal_source_coverage_and_column_contract(): void
    {
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));

        foreach ([
            'manifest_schema_version',
            'market_data_dataset_integrity_manifest_v1',
            'component_hashes',
            'component_row_counts',
            'component_column_contract',
            'coverage_context',
            'source_context',
            'seal_verification_status',
            'seal_verification_reason_code',
        ] as $needle) {
            $this->assertStringContainsString($needle, $repository);
        }
    }

    /**
     * Seal and finalize refuse to proceed on missing or mismatched integrity context. The
     * distinct reason codes matter to the operator: a missing hash and a mismatched hash demand
     * different responses.
     */
    public function test_seal_and_finalize_reject_missing_or_mismatched_integrity_context(): void
    {
        $repository = file_get_contents(base_path('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php'));

        foreach ([
            'assertPublicationIntegrityContextComplete',
            'DATASET_HASH_MISSING',
            'DATASET_MANIFEST_INVALID',
            'assertSealedPublicationMatchesRunHashes',
            'FINALIZE_HASH_MISSING',
            'FINALIZE_HASH_MISMATCH',
            'FINALIZE_SEAL_MISSING',
            'FINALIZE_SEAL_INVALID',
        ] as $needle) {
            $this->assertStringContainsString($needle, $repository);
        }
    }

    /**
     * Command output carries the integrity context an operator needs to verify a run without
     * opening the database.
     */
    public function test_command_summary_outputs_integrity_context(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/MarketData/AbstractMarketDataCommand.php'));

        foreach ([
            'renderDatasetIntegritySummary',
            'bars_batch_hash',
            'indicators_batch_hash',
            'eligibility_batch_hash',
            'seal_state',
            'sealed_at',
            'sealed_by',
        ] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }
    }
}
