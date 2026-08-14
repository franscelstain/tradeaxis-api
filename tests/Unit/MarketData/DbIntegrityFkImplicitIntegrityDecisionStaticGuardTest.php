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

    public function test_decision_inventory_locks_hybrid_fk_vs_implicit_policy_without_schema_sync_false_claim(): void
    {
        $inventory = $this->read('docs/market_data/audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md');

        foreach ([
            'DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT',
            'DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT',
            'Audit terakhir tidak menyatakan seluruh schema sync gagal',
            'Final policy: `HYBRID_REQUIRED`',
            '`EXPLICIT_FK_REQUIRED`',
            '`IMPLICIT_GUARD_ACCEPTED`',
            '`HYBRID_REQUIRED`',
            'No relation may remain `TBD` without blocker',
            'live artifact → ticker',
            'live artifact → publication',
            'history artifact → publication',
            'pointer → publication',
            'correction → prior/new run/publication',
            'replay/evidence → historical publication',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        $this->assertStringNotContainsString('| TBD |', $inventory);
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

    public function test_audit_docs_record_locked_decision_after_operator_local_runtime_proof(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([$status, $tracker] as $document) {
            $this->assertStringContainsString('CURRENT CANONICAL', $document);
            $this->assertStringContainsString('HISTORICAL SESSION:', $document);
            $this->assertStringContainsString('NON-AUTHORITATIVE UNDER V2', $document);
            $this->assertStringNotContainsString("\nACTIVE SESSION:\n", $document);
            $this->assertStringContainsString('DB Integrity FK / Implicit Integrity Decision', $document);
            $this->assertStringContainsString('BLOCKED_CONTAINER_RUNTIME_ENV', $document);
            $this->assertStringContainsString('operator-local PHPUnit', $document);
            $this->assertStringContainsString('HYBRID_REQUIRED', $document);
            $this->assertStringContainsString('DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md', $document);
            $this->assertStringContainsString('DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php', $document);
        }

        $this->assertStringContainsString('- DB Integrity FK / Implicit Integrity Decision -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT', $status);
        $this->assertStringContainsString('- DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] DB Integrity FK / Implicit Integrity Decision', $tracker);

        $implementationBlock = $this->implementationBlock($status, 'DB Integrity FK / Implicit Integrity Decision');
        $contractBlock = $this->contractBlock($tracker, 'DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT');

        $this->assertStringContainsString('-> DONE', strtok($implementationBlock, "\n"));
        $this->assertStringContainsString('-> LOCKED', strtok($contractBlock, "\n"));
        $this->assertStringContainsString('DONE_LOCAL_PHPUNIT_PASS', $implementationBlock);
        $this->assertStringContainsString('LOCKED_LOCAL_PHPUNIT_PASS', $contractBlock);

        // Four frozen tallies were asserted here — 5, 416, 11 and 146 tests — against a suite
        // that now holds over a thousand. They record what happened once.
    }

    // The latest-date sweep over four MarketData roots is now applied to every file under app/
    // by ReadPathShortcutProhibitionTest.

    private function implementationBlock(string $status, string $name): string
    {
        $pattern = '/^- '.preg_quote($name, '/').' -> [A-Z_]+\R.*?(?=^- .+ -> [A-Z_]+\R|\z)/ms';
        $this->assertMatchesRegularExpression($pattern, $status);
        preg_match($pattern, $status, $match);

        return $match[0];
    }

    private function contractBlock(string $tracker, string $name): string
    {
        $pattern = '/^- '.preg_quote($name, '/').' (?:->|→) [A-Z_]+\R.*?(?=^- [A-Z0-9_]+_CONTRACT (?:->|→) [A-Z_]+\R|\z)/msu';
        $this->assertMatchesRegularExpression($pattern, $tracker);
        preg_match($pattern, $tracker, $match);

        return $match[0];
    }
}
