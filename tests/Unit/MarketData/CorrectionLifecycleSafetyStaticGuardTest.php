<?php

use PHPUnit\Framework\TestCase;

class CorrectionLifecycleSafetyStaticGuardTest extends TestCase
{
    public function test_correction_baseline_is_current_readable_pointer_resolved_and_not_latest_date_shortcut()
    {
        $source = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $method = $this->extractMethod($source, 'findCorrectionBaselinePublicationForTradeDate');

        $this->assertStringContainsString("eod_current_publication_pointer as ptr", $method);
        $this->assertStringContainsString("run.terminal_status', 'SUCCESS'", $method);
        $this->assertStringContainsString("run.publishability_state', 'READABLE'", $method);
        $this->assertStringContainsString("run.coverage_gate_state', 'PASS'", $method);
        $this->assertStringContainsString("pub.seal_state', 'SEALED'", $method);
        $this->assertStringContainsString('readablePublicationRowOrNull', $method);
        $this->assertStringNotContainsString("max('trade_date')", $method);
        $this->assertStringNotContainsString('MAX(trade_date)', $method);
        $this->assertStringNotContainsString("latest('trade_date')", $method);
        $this->assertStringNotContainsString("orderByDesc('trade_date')", $method);
    }

    public function test_correction_finalize_blocks_invalid_diff_and_preserves_pointer_for_unchanged_or_failed_switch()
    {
        $source = $this->readProjectFile('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $method = $this->extractMethod($source, 'completeFinalize');

        $this->assertStringContainsString('publicationDiffs->compare', $method);
        $this->assertStringContainsString("\$artifactComparison['decision'] === 'INVALID'", $method);
        $this->assertStringContainsString('pointer switch blocked', $method);
        $this->assertStringContainsString("\$artifactComparison['decision'] === 'UNCHANGED'", $method);
        $this->assertStringContainsString('discardCandidatePublication', $method);
        $this->assertStringContainsString("'CORRECTION_CANCELLED'", $method);
        $this->assertStringContainsString("\$artifactComparison['decision'] !== 'CHANGED'", $method);
        $this->assertStringContainsString('promotePublicationHistoryToCurrent', $method);
        $this->assertStringContainsString('restorePriorCurrentPublication', $method);
        $this->assertStringContainsString('resolveCurrentReadablePublicationForTradeDate($input->requestedDate)', $method);
        $this->assertStringContainsString('Current publication pointer resolution mismatch after finalize.', $method);
    }

    public function test_correction_evidence_derives_publication_context_from_run_linkage()
    {
        $repo = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $method = $this->extractMethod($repo, 'findCorrectionById');

        $this->assertStringContainsString('eod_dataset_corrections as corr', $method);
        $this->assertStringContainsString('prior_run.publication_id as prior_publication_id', $method);
        $this->assertStringContainsString('new_run.publication_id as new_publication_id', $method);
        $this->assertStringContainsString('prior_pub.seal_state as prior_publication_seal_state', $method);
        $this->assertStringContainsString('new_pub.seal_state as new_publication_seal_state', $method);

        $evidence = $this->readProjectFile('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $this->assertStringContainsString("'correction_lifecycle'", $evidence);
        $this->assertStringContainsString("'changed_decision'", $evidence);
        $this->assertStringContainsString("'reseal_status'", $evidence);
        $this->assertStringContainsString("'baseline_publication_id'", $evidence);
        $this->assertStringContainsString("'candidate_publication_id'", $evidence);
    }

    public function test_replay_persists_and_compares_correction_lifecycle_context()
    {
        $service = $this->readProjectFile('app/Application/MarketData/Services/ReplayVerificationService.php');
        $repo = $this->readProjectFile('app/Infrastructure/Persistence/MarketData/ReplayResultRepository.php');

        foreach (['correction_id', 'correction_status', 'correction_outcome', 'correction_reseal_status', 'correction_publication_switch', 'baseline_publication_id', 'candidate_publication_id'] as $field) {
            $this->assertStringContainsString($field, $service);
            $this->assertStringContainsString($field, $repo);
        }

        $this->assertStringContainsString('findCorrectionByRunId', $service);
        foreach ([
            'expected_correction_context',
            'actual_correction_context',
            'REPLAY_CORRECTION_BASELINE_MISMATCH',
        ] as $needle) {
            $this->assertStringContainsString($needle, $service);
        }
        $this->assertStringContainsString('buildReplayActualCorrectionLifecycle', $this->readProjectFile('app/Application/MarketData/Services/MarketDataEvidenceExportService.php'));
    }

    public function test_correction_command_surfaces_lifecycle_state()
    {
        $command = $this->readProjectFile('app/Console/Commands/MarketData/RunCorrectionCommand.php');

        foreach (['correction_outcome=', 'correction_reseal_status=', 'baseline_publication_id=', 'candidate_publication_id=', 'candidate_publication_switch=', 'final_outcome_note='] as $needle) {
            $this->assertStringContainsString($needle, $command);
        }
    }

    private function readProjectFile($relativePath)
    {
        $path = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    private function extractMethod($source, $methodName, $visibility = 'public')
    {
        $pattern = '/'.$visibility.' function '.preg_quote($methodName, '/').'\([^)]*\)\s*(?::\s*[^\s{]+)?\s*\{(?P<body>.*?)\n    \}/s';
        $this->assertSame(1, preg_match($pattern, $source, $matches), 'Method not found: '.$methodName);

        return $matches[0];
    }
}
