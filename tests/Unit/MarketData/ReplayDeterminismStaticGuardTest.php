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

    /**
     * Replay must never read a raw or staging table.
     *
     * The latest-date half of this check is now applied to every file under app/ by
     * ReadPathShortcutProhibitionTest. What stays is the raw/staging prohibition, which is
     * specific to replay: replay re-derives a run from published artifacts, so reaching into an
     * intermediate table would let it "reproduce" data that was never published — the one thing
     * a determinism proof must not be able to do.
     */
    public function test_replay_never_reads_a_raw_or_staging_table(): void
    {
        foreach ([
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Application/MarketData/Services/ReplayBackfillService.php',
            'app/Application/MarketData/Services/ReplaySmokeSuiteService.php',
            'app/Console/Commands/MarketData/VerifyReplayCommand.php',
            'app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php',
        ] as $file) {
            $this->assertDoesNotMatchRegularExpression('/eod_(raw|staging|stg)_/i', $this->read($file), $file);
        }
    }

    public function test_replay_schema_and_command_surface_expose_operator_grade_artifacts(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php');
        $evidence = $this->read('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $command = $this->read('app/Console/Commands/MarketData/VerifyReplayCommand.php');
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

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

    // The twenty-one hand-listed reason codes are gone. EmittedReasonCodeRegistrationTest now
    // reads the replay codes out of the service and checks every one against the seeded
    // dictionary — thirty-eight of them, not twenty-one. The seventeen the list never mentioned
    // included REPLAY_MISMATCH, the fallback the comparator uses when a difference carries no
    // specific code, which was registered nowhere at all.
}
