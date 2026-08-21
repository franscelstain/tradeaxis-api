<?php

use App\Application\MarketData\Services\MarketDataEvidenceExportService;
use PHPUnit\Framework\TestCase;

class EvidenceExportCompletenessStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    public function test_run_evidence_export_contains_operator_grade_sections(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));

        foreach ([
            'evidence_admission',
            'evidence_admission_state',
            'evidence_admission_reason_code',
            'silent_missing_metadata_allowed',
            'evidence_completeness',
            'evidence_completeness_state',
            'evidence_completeness_reason_code',
            'missing_sections',
            'database_lookup_required_after_export',
            'deterministic_export',
            'run_summary',
            'source_context',
            'coverage_context',
            'artifact_context',
            'publication_context',
            'pointer_context',
            'fallback_context',
            'correction_context',
            'lineage',
            'lineage.json',
            'evidence_admission.json',
            'evidence_completeness.json',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service, 'Evidence export must keep section '.$needle);
        }
    }

    public function test_run_source_coverage_publication_pointer_and_lineage_fields_are_exported(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));

        foreach ([
            'run_uuid',
            'requested_date',
            'trade_date_requested',
            'trade_date_effective',
            'platform_timezone',
            'request_mode',
            'promote_mode',
            'publish_target',
            'stage_reached',
            'terminal_status',
            'publishability_state',
            'final_reason_code',
            'final_outcome_note',
            'source_identity',
            'source_mode',
            'source_name',
            'source_provider',
            'source_attempt_count',
            'source_success_after_retry',
            'source_retry_exhausted',
            'source_final_http_status',
            'source_final_reason_code',
            'source_input_file',
            'source_file_hash',
            'source_file_hash_algorithm',
            'source_file_size_bytes',
            'source_file_row_count',
            'accepted_row_count',
            'rejected_row_count',
            'invalid_row_count',
            'coverage_gate_state',
            'coverage_reason_code',
            'coverage_expected_count',
            'coverage_available_count',
            'coverage_missing_count',
            'expected_bar_count',
            'available_bar_count',
            'missing_bar_count',
            'coverage_ratio',
            'coverage_min_threshold',
            'coverage_passed',
            'coverage_failed_symbols',
            'artifact_hash',
            'seal_state',
            'seal_hash',
            'seal_manifest',
            'mandatory_artifact_presence',
            'publication_id',
            'publication_version',
            'publication_run_id',
            'publication_trade_date_effective',
            'publication_seal_state',
            'publication_publishability_state',
            'publication_terminal_status',
            'run_publication_mirror_valid',
            'publication_artifact_lineage',
            'pointer_publication_id',
            'pointer_resolved_publication_id',
            'pointer_resolve_status',
            'pointer_switched',
            'pointer_switch_allowed',
            'pointer_post_switch_validation',
            'readable_pointer_validated',
            'pointer_mismatch_reason',
            'fallback_used',
            'fallback_reason_code',
            'fallback_publication_id',
            'fallback_lineage',
            'correction_baseline_publication_id',
            'ingest_source_to_run',
            'run_to_artifacts',
            'run_to_coverage_decision',
            'run_to_finalize_decision',
            'run_to_publication',
            'publication_to_pointer',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service, 'Evidence export must keep field '.$needle);
        }
    }

    public function test_every_stored_run_coverage_field_declares_an_evidence_export_path(): void
    {
        $schemaMirror = file_get_contents($this->projectPath('tests/Support/UsesMarketDataSqlite.php'));
        $matched = preg_match(
            '/\$schema->create\(\'eod_runs\'.*?^\s*}\);/ms',
            $schemaMirror,
            $tableMatch
        );
        $this->assertSame(1, $matched, 'The eod_runs schema mirror could not be inspected.');

        preg_match_all(
            '/\$table->\w+\(\'(?<field>coverage_[a-z0-9_]+)\'/',
            $tableMatch[0],
            $fieldMatches
        );

        $storedFields = array_values(array_unique($fieldMatches['field']));
        $declaredFields = array_keys(MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS);
        $declaredPaths = array_values(MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS);
        sort($storedFields);
        sort($declaredFields);

        $this->assertSame(
            $storedFields,
            $declaredFields,
            'Every coverage_* field stored on eod_runs must declare its evidence payload path.'
        );
        $this->assertSame(
            array_keys(MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS),
            $declaredPaths,
            'A stored coverage field must export under its own key; aliases can hide an unread source column.'
        );

        $canonicalSchema = file_get_contents($this->projectPath('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql'));
        $matched = preg_match(
            '/CREATE TABLE IF NOT EXISTS eod_runs \((.*?)\n\) ENGINE=/s',
            $canonicalSchema,
            $canonicalTableMatch
        );
        $this->assertSame(1, $matched, 'The canonical eod_runs schema could not be inspected.');
        preg_match_all(
            '/^\s*(?<field>coverage_[a-z0-9_]+)\s+/m',
            $canonicalTableMatch[1],
            $canonicalFieldMatches
        );
        $canonicalFields = array_values(array_unique($canonicalFieldMatches['field']));
        sort($canonicalFields);

        $this->assertSame(
            $canonicalFields,
            $storedFields,
            'The canonical schema and SQLite mirror must expose the same coverage_* columns.'
        );
    }

    public function test_every_declared_run_coverage_path_reads_only_its_own_stored_field(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $matched = preg_match(
            '/private function buildCoverageState\(\$record\)(.*?)private function resolveCoverageReasonCodeFromState/s',
            $service,
            $methodMatch
        );
        $this->assertSame(1, $matched, 'The run coverage export method could not be inspected.');

        foreach (MarketDataEvidenceExportService::RUN_COVERAGE_STORAGE_EXPORT_PATHS as $storedField => $payloadField) {
            $fieldPattern = preg_quote($payloadField, '/');
            $matched = preg_match(
                "/'{$fieldPattern}'\\s*=>\\s*(?<expression>[^\\r\\n]+),/",
                $methodMatch[1],
                $exportMatch
            );
            $this->assertSame(1, $matched, 'Missing declared coverage export expression for '.$payloadField);

            $sourceExpression = trim($exportMatch['expression']);
            if (preg_match('/^\$(?<variable>[A-Za-z_][A-Za-z0-9_]*)$/', $sourceExpression, $variableMatch) === 1) {
                $variablePattern = preg_quote($variableMatch['variable'], '/');
                $matched = preg_match(
                    '/\$'.$variablePattern.'\s*=\s*(?<expression>[^;]+);/',
                    $methodMatch[1],
                    $assignmentMatch
                );
                $this->assertSame(1, $matched, 'The coverage export source variable could not be resolved for '.$payloadField);
                $sourceExpression = $assignmentMatch['expression'];
            }

            preg_match_all(
                '/\$record->(?<direct>coverage_[a-z0-9_]+)|\$this->field\(\$record,\s*\'(?<helper>coverage_[a-z0-9_]+)\'\)/',
                $sourceExpression,
                $sourceMatches,
                PREG_SET_ORDER
            );
            $sourceFields = [];
            foreach ($sourceMatches as $sourceMatch) {
                $sourceFields[] = $sourceMatch['direct'] !== '' ? $sourceMatch['direct'] : $sourceMatch['helper'];
            }
            $sourceFields = array_values(array_unique($sourceFields));
            sort($sourceFields);

            $this->assertSame(
                [$storedField],
                $sourceFields,
                'Coverage export path '.$payloadField.' must read only its own stored field '.$storedField.'.'
            );
        }
    }

    public function test_replay_evidence_compares_expected_and_actual_lifecycle_context(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $replay = file_get_contents($this->projectPath('app/Application/MarketData/Services/ReplayVerificationService.php'));

        foreach ([
            'buildReplayActualPublicationContext',
            'buildReplayExpectedPublicationContext',
            'buildReplayActualPointerContext',
            'buildReplayExpectedPointerContext',
            'buildReplayActualSourceContext',
            'buildReplayExpectedSourceContext',
            'buildReplayActualCorrectionLifecycle',
            'buildReplayExpectedCorrectionLifecycle',
            'publication_context',
            'expected_publication_context',
            'pointer_context',
            'expected_pointer_context',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service, 'Replay evidence export must keep comparison field '.$needle);
        }

        foreach ([
            'expectedSourceContext',
            'appendManualFilePolicyMismatches',
            'coverage_reason_code',
            'publication_id',
            'publication_run_id',
            'publication_version',
            'is_current_publication',
            'correction_id',
            'correction_status',
            'baseline_publication_id',
            'compareReasonCodeCounts',
        ] as $needle) {
            $this->assertStringContainsString($needle, $replay, 'Replay verification must keep mismatch check '.$needle);
        }
    }

    // The latest-date shortcut check that stood here is now applied to every file under app/ by
    // ReadPathShortcutProhibitionTest, rather than to these three by name.
    public function test_evidence_export_does_not_claim_readable_when_it_is_not(): void
    {
        $service = file_get_contents($this->projectPath('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
        $this->assertStringNotContainsString('Run evidence export requires a SUCCESS + READABLE run', $service);
        $this->assertStringContainsString('NOT_CREATED_OR_NOT_READABLE', $service);
        $this->assertStringContainsString('NO_READABLE_FALLBACK_CLAIMED', $service);
    }

    public function test_command_surface_warns_on_incomplete_evidence(): void
    {
        $command = file_get_contents($this->projectPath('app/Console/Commands/MarketData/ExportEvidenceCommand.php'));

        $this->assertStringContainsString('evidence_completeness_state', $command);
        $this->assertStringContainsString('evidence_warning=EVIDENCE_INCOMPLETE', $command);
        $this->assertStringContainsString('evidence_admission.json', $command);
        $this->assertStringContainsString('evidence_completeness.json', $command);
    }
}
