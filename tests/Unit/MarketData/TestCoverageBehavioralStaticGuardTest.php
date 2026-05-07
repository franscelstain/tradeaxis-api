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

    public function test_db_backed_lifecycle_proof_files_are_not_internal_mock_heavy(): void
    {
        $proofFiles = [
            'tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php',
            'tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php',
            'tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php',
            'tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php',
            'tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php',
        ];

        foreach ($proofFiles as $file) {
            $source = $this->read($file);

            $this->assertStringContainsString('UsesMarketDataSqlite', $source, $file.' must stay DB-backed.');
            $this->assertStringContainsString('DB::table', $source, $file.' must assert persisted state.');
            $this->assertStringNotContainsString('use Mockery', $source, $file.' must not become internal Mockery-based lifecycle proof.');
            $this->assertStringNotContainsString('shouldReceive', $source, $file.' must not prove lifecycle with mocked internal behavior.');
            $this->assertStringNotContainsString('m::mock', $source, $file.' must not prove lifecycle with mocked internal behavior.');
        }
    }

    public function test_pipeline_integration_proves_import_promote_finalize_coverage_pointer_correction_and_fallback_state(): void
    {
        $pipeline = $this->read('tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php');

        foreach ([
            'test_manual_file_import_only_writes_candidate_bars_without_finalize_or_pointer_switch',
            'test_manual_file_promote_from_imported_partial_dataset_enforces_coverage_gate_and_does_not_switch_pointer',
            'test_run_daily_persists_full_db_backed_pipeline_and_current_publication',
            'test_run_daily_full_coverage_persists_finalize_coverage_payload_and_readable_publication',
            'test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication',
            'test_run_daily_low_coverage_without_fallback_finishes_not_readable_and_emits_coverage_reason_code',
            'test_complete_finalize_is_idempotent_for_already_completed_success_run',
            'test_completed_success_finalize_rerun_with_invalid_pointer_fails_safe_without_duplicate_publication',
            'test_run_daily_correction_replaces_current_publication_and_marks_correction_published',
            'test_run_daily_correction_with_unchanged_artifacts_cancels_request_and_preserves_current_publication',
            'test_run_daily_correction_with_reseal_failure_keeps_prior_current_and_leaves_candidate_non_current',
            'test_run_daily_correction_with_changed_artifacts_and_promotion_failure_holds_and_preserves_prior_current_publication',
        ] as $testName) {
            $this->assertStringContainsString($testName, $pipeline, $testName.' must remain as runtime-like proof.');
        }

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
        $guidance = $this->read('docs/market_data/tests/Test_Implementation_Guidance_LOCKED.md');

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
