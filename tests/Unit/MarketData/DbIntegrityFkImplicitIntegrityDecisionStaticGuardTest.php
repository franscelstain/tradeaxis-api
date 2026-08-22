<?php

use PHPUnit\Framework\TestCase;

class DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest extends TestCase
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


    // The schema half of this file is now ArtifactIntegrityPolicyTest. It derives both families
    // from the DDL — every *_history table and every live artifact table — and applies the
    // policy to each, so an artifact table added later is covered without editing a list. It
    // also pairs the two families against each other, which the sixteen hand-written fragments
    // here could not express.

    public function test_implicit_repository_and_resolver_guards_cover_non_fk_lifecycle_relations(): void
    {
        $artifactRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $publicationRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $evidenceRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $replayService = $this->read('app/Application/MarketData/Services/ReplayVerificationService.php');
        $correctionRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php');

        // Seven query-fragment assertions used to stand in for this guard. They confirmed a
        // method was named and a join was written, never that a sealed dataset is actually
        // protected, and nothing exercised the guard at all.
        //
        // SealedArtifactMutationGuardTest now drives it: a sealed and readable publication
        // refuses replacement even after it stops being current, an unsealed candidate stays
        // replaceable, a sealed run that never became readable stays replaceable, and a
        // correction may still rewrite its own publication. Those distinctions are the whole
        // point of the guard and no source-text match could express them.

        foreach ([
            'determineCurrentIntegrityViolationReasons',
            "whereColumn('ptr.run_id', 'pub.run_id')",
            "whereColumn('ptr.publication_version', 'pub.publication_version')",
            "whereColumn('run.publication_id', 'ptr.publication_id')",
            "whereColumn('run.publication_version', 'ptr.publication_version')",
            'RUN_PUBLICATION_ID_MISMATCH',
            'RUN_COVERAGE_GATE_NOT_PASS',
            'RUN_COVERAGE_TELEMETRY_INVALID',
            'assertCurrentPointerResolvedAfterSwitch',
            'assertPublicationIntegrityContextComplete',
        ] as $needle) {
            $this->assertStringContainsString($needle, $publicationRepository);
        }

        foreach ([
            'resolvePublicationForEvidenceAudit',
            'dominantReasonCodesForEvidencePublication',
            'exportEligibilityRowsForEvidencePublication',
            'eod_current_publication_pointer',
            'eod_dataset_corrections',
        ] as $needle) {
            $this->assertStringContainsString($needle, $evidenceRepository);
        }

        foreach (['resolvePublicationForReplayActualState', 'actual_replay_resolution_context', 'expected_replay_resolution_context'] as $needle) {
            $this->assertStringContainsString($needle, $replayService);
        }

        foreach (['prior_run_id', 'new_run_id', 'baseline_publication_id', 'replacement_publication_id', 'status'] as $needle) {
            $this->assertStringContainsString($needle, $correctionRepository);
        }
    }


    // The latest-date sweep over four MarketData roots is now applied to every file under app/
    // by ReadPathShortcutProhibitionTest.
}
