<?php

use PHPUnit\Framework\TestCase;

class TestCoverageBehavioralStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path, $relativePath.' must exist.');

        return file_get_contents($path);
    }

    public function test_behavioral_inventory_documents_all_critical_market_data_areas_and_mock_policy(): void
    {
        $inventory = $this->read('docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md');

        foreach ([
            'TEST_COVERAGE_BEHAVIORAL_CONTRACT',
            'source/manual/API boundary',
            'manual import/promote split',
            'coverage gate',
            'finalize',
            'publishability state',
            'publication repository',
            'current pointer',
            'fallback',
            'correction lifecycle',
            'evidence export',
            'replay verification',
            'read-side consumer',
            'command surface',
            'static guard',
            'migration/schema',
            'reason code propagation',
            'full pipeline integration',
            'INTERNAL_MOCK_HEAVY',
            'SUPPORT_ONLY',
            'ENFORCED_PENDING_LOCAL_PHPUNIT',
            'MarketDataPipelineIntegrationTest',
            'PublicationRepositoryIntegrationTest',
            'ReadablePublicationReadContractIntegrationTest',
            'CorrectionRepositoryIntegrationTest',
            'ReplayResultRepositoryIntegrationTest',
            'MarketDataSqliteSchemaSyncTest',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must remain in behavioral coverage inventory.');
        }
    }

    // The five named proof files are now covered by LifecycleProofIsNotMockedTest, which derives
    // the set instead: every *IntegrationTest must be DB-backed and free of test doubles, and no
    // DB-backed test anywhere may mock an application service or a persistence repository. Only
    // the source adapter may be stood in for, because a test cannot call a third-party API.

    /**
     * The pipeline integration proof must keep covering every terminal outcome.
     *
     * The twelve test METHOD NAMES that used to be pinned here are gone. Pinning a name breaks
     * when a test is renamed for clarity, catches nothing when one is deleted and replaced by a
     * weaker test of the same name, and says nothing about what is asserted.
     *
     * What survives is the substance: the state combinations and persisted tables the proof has
     * to reach. A run cannot end in a state this file does not mention.
     */
    public function test_pipeline_integration_covers_every_terminal_outcome_and_persisted_surface(): void
    {
        $pipeline = $this->read('tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php');

        foreach ([
            "'SUCCESS'",
            "'READABLE'",
            "'HELD'",
            "'FAILED'",
            "'NOT_READABLE'",
            "'RUN_PARTIAL_DATA'",
            "'RUN_LOCK_CONFLICT'",
            'eod_current_publication_pointer',
            'eod_publications',
            'eod_runs',
            'eod_run_events',
            'coverage_gate_state',
            'coverage_available_count',
            'coverage_universe_count',
            'coverage_missing_count',
            'RUN_FINALIZED',
            'STAGE_COMPLETED',
            'CORRECTION_PUBLISHED',
        ] as $needle) {
            $this->assertStringContainsString($needle, $pipeline, $needle.' must remain asserted by pipeline integration proof.');
        }
    }

    public function test_command_surface_mock_heavy_tests_are_explicitly_not_counted_as_lifecycle_proof(): void
    {
        $ops = $this->read('tests/Unit/MarketData/OpsCommandSurfaceTest.php');
        $inventory = $this->read('docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md');

        $this->assertStringContainsString('use Mockery', $ops);
        $this->assertStringContainsString('shouldReceive', $ops);
        $this->assertStringContainsString('INTERNAL_MOCK_HEAVY', $inventory);
        $this->assertStringContainsString('OpsCommandSurfaceTest', $inventory);
        $this->assertStringContainsString('Do not count command tests as lifecycle proof', $inventory);
        $this->assertStringContainsString('command output test without DB/proof state assertion', $inventory);
    }

    public function test_behavioral_guard_keeps_static_checks_as_support_not_runtime_replacement(): void
    {
        $inventory = $this->read('docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md');
        $guidance = $this->read('docs/market_data/development/implementation/tests/specs/Test_Implementation_Guidance_LOCKED.md');

        foreach ([
            'Static guard is not runtime proof',
            'static guard alone',
            'Unit tests and static guards are support proof only',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory, $needle.' must stay documented.');
        }

        foreach ([
            'Row-level assertions',
            'Run-level assertions',
            'Hash-level assertions',
            'Publication-level assertions',
            'Replay-level assertions',
            'No fake-proof rule',
        ] as $needle) {
            $this->assertStringContainsString($needle, $guidance, $needle.' must remain locked test guidance.');
        }
    }
}
