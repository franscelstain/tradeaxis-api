<?php

use PHPUnit\Framework\TestCase;

class B18ReplayContractStaticGuardTest extends TestCase
{
    private function source(string $path): string
    {
        $content = file_get_contents(dirname(__DIR__, 3).'/'.$path);
        $this->assertNotFalse($content, $path);
        return (string) $content;
    }

    public function test_exact_requires_explicit_publication_and_never_latest(): void
    {
        $s = $this->source('app/Console/Commands/MarketData/VerifyReplayCommand.php');
        $this->assertStringContainsString('REPLAY_EXPLICIT_PUBLICATION_REQUIRED', $s);
        $this->assertStringContainsString('ReplayMode::PUBLICATION_EXACT', $s);
    }
    public function test_exact_missing_publication_fails_closed(): void
    {
        $s = $this->source('app/Application/MarketData/Services/ReplayVerificationService.php');
        $this->assertStringContainsString('REPLAY_EXPLICIT_PUBLICATION_REQUIRED', $s);
        $this->assertStringNotContainsString('findCurrentPublicationForTradeDate($fixture', $s);
    }
    public function test_as_known_has_a_separate_cutoff_bound_execution_path(): void
    {
        $s = $this->source('app/Application/MarketData/Services/ReplayVerificationService.php');
        $execution = $this->source('app/Application/MarketData/Services/AsKnownReplayExecutionService.php');
        $this->assertStringContainsString('verifyAsKnownAgainstFixture', $s);
        $this->assertStringContainsString('AsKnownReplayExecutionService::EXECUTION_SCOPE', $s);
        $this->assertStringContainsString('as_known_execution.json', $s);
        $this->assertStringContainsString('REPLAY_KNOWLEDGE_CUTOFF_REQUIRED', $s);
        $this->assertStringContainsString('EodBarsIngestService', $execution);
        $this->assertStringContainsString('ingestAcquiredRows', $execution);
        $this->assertStringContainsString('DB::rollBack()', $execution);
    }
    public function test_as_known_cannot_impersonate_a_historical_publication(): void
    {
        $s = $this->source('app/Application/MarketData/Services/ReplayVerificationService.php');
        $this->assertStringContainsString("'publication_id' => null", $s);
        $this->assertStringContainsString("'seal_state' => 'UNSEALED'", $s);
    }
    public function test_bound_inputs_are_first_class_persisted_fields(): void
    {
        $s = $this->source('database/migrations/2026_09_04_000001_add_replay_v2_bound_input_context.php');
        foreach (['replay_mode','knowledge_cutoff_at','source_observation_manifest_hash','canonical_raw_input_hash','temporal_identity_hash','calendar_status_hash','event_factor_hash','config_snapshot_hash','formula_registry_hash','reason_registry_hash','bound_input_context_json'] as $field) {
            $this->assertStringContainsString("'{$field}'", $s);
        }
    }
    public function test_missing_bound_input_is_not_silently_admitted(): void
    {
        $s = $this->source('app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php');
        $this->assertStringContainsString('REPLAY_BOUND_INPUT_INCOMPLETE', $s);
        $this->assertStringContainsString('assertModeInputs', $s);
    }
    public function test_self_generated_fixture_is_diagnostic_only(): void
    {
        $s = $this->source('app/Console/Commands/MarketData/GenerateReplayFixtureCommand.php');
        $this->assertStringContainsString('NOT_ADMISSIBLE', $s);
        $this->assertStringContainsString('DIAGNOSTIC_ONLY_SELF_GENERATED_ORACLE', $s);
    }
    public function test_same_run_oracle_is_rejected_as_positive_proof(): void
    {
        $s = $this->source('app/Application/MarketData/Services/ReplayVerificationService.php');
        $this->assertStringContainsString('REPLAY_FIXTURE_SELF_GENERATED', $s);
        $this->assertStringContainsString('runtime_generated_diagnostic_case', $s);
    }
    public function test_temporal_retraction_is_cutoff_aware(): void
    {
        $s = $this->source('app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php');
        $this->assertStringContainsString('orWhere(\'pm.retracted_at\', \'>\', $knownAt)', $s);
        $this->assertStringContainsString('orWhere(\'ls.retracted_at\', \'>\', $knownAt)', $s);
    }
    public function test_current_identity_still_excludes_retracted_rows(): void
    {
        $s = $this->source('app/Infrastructure/Persistence/MarketData/TemporalIdentityRepository.php');
        $this->assertStringContainsString("whereNull('pm.retracted_at')", $s);
        $this->assertStringContainsString("whereNull('ls.retracted_at')", $s);
    }
    public function test_source_rows_bind_observation_and_identity_known_time(): void
    {
        $s = $this->source('app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php');
        $this->assertStringContainsString("where('obs.acquired_at', '<='", $s);
        $this->assertStringContainsString("where('binding.recorded_at', '<='", $s);
    }
    public function test_source_manifest_does_not_filter_by_current_universe(): void
    {
        $s = $this->source('app/Infrastructure/Persistence/MarketData/SourceObservationRepository.php');
        $method = substr($s, strpos($s, 'public function normalizedRowsAsKnown'), 3200);
        $this->assertStringNotContainsString('md_listings', $method);
        $this->assertStringNotContainsString('is_current', $method);
    }
    public function test_evidence_exports_mode_cutoff_and_bound_inputs(): void
    {
        $s = $this->source('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $this->assertStringContainsString("'replay_mode'", $s);
        $this->assertStringContainsString("'bound_inputs'", $s);
        $this->assertStringContainsString("'knowledge_cutoff_at'", $s);
    }
    public function test_unclassified_historical_result_cannot_be_complete_b18_evidence(): void
    {
        $s = $this->source('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $this->assertStringContainsString('replay_mode_invalid_or_historical_unclassified', $s);
    }
    public function test_lifecycle_replay_requires_independent_fixture_root(): void
    {
        $s = $this->source('app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php');
        $this->assertStringContainsString('REPLAY_INDEPENDENT_FIXTURE_ROOT_REQUIRED', $s);
        $this->assertStringContainsString('requireIndependentReplayFixture', $s);
    }
    public function test_lifecycle_positive_replay_never_generates_same_run_fixture(): void
    {
        foreach (['BackfillLifecycleOrchestrator.php','FullRangeCurrentEvidenceReplayService.php','ReplayBackfillService.php'] as $file) {
            $s = $this->source('app/Application/MarketData/Services/'.$file);
            $this->assertStringNotContainsString('generateFixtureFromRun(', $s, $file);
        }
    }
    public function test_replay_modes_are_first_class_and_exhaustive(): void
    {
        $s = $this->source('app/Application/MarketData/Services/ReplayMode.php');
        $this->assertStringContainsString("PUBLICATION_EXACT", $s);
        $this->assertStringContainsString("AS_KNOWN", $s);
    }
    public function test_unknown_or_missing_mode_fails_closed(): void
    {
        $s = $this->source('app/Application/MarketData/Services/ReplayMode.php');
        $this->assertStringContainsString('REPLAY_MODE_REQUIRED', $s);
        $this->assertStringContainsString('REPLAY_MODE_UNSUPPORTED', $s);
    }
}
