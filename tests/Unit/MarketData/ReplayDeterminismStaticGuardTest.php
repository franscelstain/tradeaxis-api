<?php

class ReplayDeterminismStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function test_replay_verifier_requires_stable_fixture_metadata_expected_actual_context_and_reason_coded_mismatches(): void
    {
        $source = $this->read('app/Application/MarketData/Services/ReplayVerificationService.php');

        foreach ([
            'fixture_id',
            'fixture_version',
            'fixture_schema_version',
            'fixture_created_at',
            'fixture_source',
            'expected_context_json',
            'actual_context_json',
            'mismatches_json',
            'mismatch_reason_codes_json',
            'replay_status',
            'PASS',
            'FAIL',
            'BLOCKED',
            'ignored_volatile_fields_json',
            'deterministic_fields_checked_json',
            'REPLAY_EXPECTED_PROOF_INCOMPLETE',
            'REPLAY_ACTUAL_PROOF_INCOMPLETE',
            'REPLAY_NON_DETERMINISTIC_OUTPUT',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source, $needle.' must remain part of replay determinism proof.');
        }

        $this->assertMatchesRegularExpression('/function\s+validateExpectedProofCompleteness\s*\(/', $source);
        $this->assertMatchesRegularExpression('/function\s+buildActualReplayState\s*\(/', $source);
        $this->assertMatchesRegularExpression('/function\s+compareExpectedAndActual\s*\(/', $source);
        $this->assertMatchesRegularExpression('/function\s+appendMismatch\s*\([^)]*\$reasonCode/s', $source);
        $this->assertStringContainsString("'reason_code' => \$reasonCode", $source);
    }

    public function test_replay_comparison_covers_source_coverage_artifact_publication_pointer_fallback_correction_reason_and_lineage(): void
    {
        $source = $this->read('app/Application/MarketData/Services/ReplayVerificationService.php');

        foreach ([
            'expected_source_context',
            'actual_source_context',
            'source_file_hash',
            'source_provider',
            'expected_coverage_context',
            'actual_coverage_context',
            'coverage_gate_state',
            'coverage_ratio',
            'coverage_reason_code',
            'expected_artifact_context',
            'actual_artifact_context',
            'bars_batch_hash',
            'expected_seal_context',
            'actual_seal_context',
            'expected_publication_context',
            'actual_publication_context',
            'expected_pointer_context',
            'actual_pointer_context',
            'pointer_resolve_status',
            'expected_fallback_context',
            'actual_fallback_context',
            'expected_correction_context',
            'actual_correction_context',
            'expected_final_state',
            'actual_final_state',
            'expected_lineage',
            'actual_lineage',
            'compareReasonCodeCounts',
        ] as $needle) {
            $this->assertStringContainsString($needle, $source, $needle.' must be compared by replay verifier.');
        }
    }

    public function test_replay_verifier_and_commands_do_not_use_latest_max_or_raw_shortcuts(): void
    {
        $files = [
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Application/MarketData/Services/ReplayBackfillService.php',
            'app/Application/MarketData/Services/ReplaySmokeSuiteService.php',
            'app/Console/Commands/MarketData/VerifyReplayCommand.php',
            'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
        ];

        foreach ($files as $file) {
            $source = $this->read($file);
            $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*latest\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/ORDER\s+BY\s+trade_date\s+DESC/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/eod_(raw|staging|stg)_/i', $source, $file);
        }
    }

    public function test_replay_schema_and_command_surface_expose_operator_grade_artifacts(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php');
        $evidence = $this->read('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $command = $this->read('app/Console/Commands/MarketData/VerifyReplayCommand.php');
        $schema = $this->read('docs/market_data/db/Database_Schema_MariaDB.sql');

        foreach (['expected_context_json', 'actual_context_json', 'mismatch_reason_codes_json', 'mismatches_json', 'final_reason_code', 'replay_status'] as $needle) {
            $this->assertStringContainsString($needle, $repository);
            $this->assertStringContainsString($needle, $schema);
        }

        foreach (['replay_status=', 'mismatch_count=', 'mismatch_reason_codes=', 'source_summary=', 'coverage_summary=', 'publication_summary=', 'pointer_summary=', 'fallback_summary=', 'correction_summary=', 'replay_artifact_path='] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }

        foreach (['replay_status', 'expected_context', 'actual_context', 'ignored_volatile_fields', 'deterministic_fields_checked'] as $needle) {
            $this->assertStringContainsString($needle, $evidence);
        }
    }

    public function test_replay_reason_codes_are_registered_and_seeded(): void
    {
        $registry = $this->read('docs/market_data/registry/Reason_Codes_Registry.md');
        $seed = $this->read('docs/market_data/registry/Reason_Codes_Seed.sql');

        foreach ([
            'REPLAY_FIXTURE_SCHEMA_MISMATCH',
            'REPLAY_EXPECTED_PROOF_INCOMPLETE',
            'REPLAY_ACTUAL_PROOF_INCOMPLETE',
            'REPLAY_SOURCE_MODE_MISMATCH',
            'REPLAY_SOURCE_FILE_HASH_MISMATCH',
            'REPLAY_PROVIDER_CONTEXT_MISMATCH',
            'REPLAY_COVERAGE_STATE_MISMATCH',
            'REPLAY_COVERAGE_RATIO_MISMATCH',
            'REPLAY_COVERAGE_REASON_MISMATCH',
            'REPLAY_ARTIFACT_HASH_MISMATCH',
            'REPLAY_SEAL_STATE_MISMATCH',
            'REPLAY_PUBLICATION_STATE_MISMATCH',
            'REPLAY_PUBLICATION_VERSION_MISMATCH',
            'REPLAY_POINTER_TARGET_MISMATCH',
            'REPLAY_POINTER_RESOLUTION_MISMATCH',
            'REPLAY_FALLBACK_CONTEXT_MISMATCH',
            'REPLAY_CORRECTION_BASELINE_MISMATCH',
            'REPLAY_FINAL_STATUS_MISMATCH',
            'REPLAY_FINAL_REASON_CODE_MISMATCH',
            'REPLAY_LINEAGE_MISMATCH',
            'REPLAY_NON_DETERMINISTIC_OUTPUT',
        ] as $code) {
            $this->assertStringContainsString($code, $registry);
            $this->assertStringContainsString($code, $seed);
        }
    }
}
