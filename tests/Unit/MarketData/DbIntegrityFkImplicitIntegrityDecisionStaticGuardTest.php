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

    public function test_locked_schema_documents_selective_fk_and_implicit_live_artifact_guard_policy(): void
    {
        $schema = $this->read('docs/market_data/db/Database_Schema_MariaDB.sql');

        foreach ([
            'DB INTEGRITY FK / IMPLICIT INTEGRITY DECISION - LOCKED POLICY',
            'Final policy: HYBRID_REQUIRED',
            'Current live artifact tables intentionally keep mandatory run_id/publication_id columns',
            'do not add physical FK constraints to',
            'import/promote/correction/replay lifecycles',
            'Stable immutable proof tables keep explicit publication FK constraints',
            'Current pointer keeps explicit FK to eod_publications(publication_id)',
            'does not mean the whole schema sync failed',
            'CONSTRAINT fk_current_publication_pointer_publication',
            'FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)',
            'CONSTRAINT fk_bars_history_publication',
            'CONSTRAINT fk_indicators_history_publication',
            'CONSTRAINT fk_eligibility_history_publication',
            'KEY idx_eod_bars_publication_date_ticker (publication_id, trade_date, ticker_id)',
            'KEY idx_eod_indicators_publication_date_ticker (publication_id, trade_date, ticker_id)',
            'KEY idx_eod_eligibility_publication_date_ticker (publication_id, trade_date, ticker_id)',
        ] as $needle) {
            $this->assertStringContainsString($needle, $schema);
        }

        foreach (['eod_bars', 'eod_indicators', 'eod_eligibility'] as $table) {
            $block = $this->sqlTableBlock($schema, $table);
            $this->assertStringContainsString('publication_id BIGINT UNSIGNED NOT NULL', $block, $table.' must carry mandatory publication context.');
            $this->assertStringContainsString('run_id BIGINT UNSIGNED NOT NULL', $block, $table.' must carry mandatory run context.');
            $this->assertStringContainsString('KEY idx_'.$table.'_publication', $block, $table.' must index publication context.');
            $this->assertStringContainsString('publication_id, trade_date, ticker_id', $block, $table.' must support publication-scoped artifact lookup.');
            $this->assertStringNotContainsString('CONSTRAINT fk_'.$table, $block, $table.' current/live artifact table must not add a physical FK without a separate policy/migration proof.');
        }
    }

    public function test_implicit_repository_and_resolver_guards_cover_non_fk_lifecycle_relations(): void
    {
        $artifactRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php');
        $publicationRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $evidenceRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $replayService = $this->read('app/Application/MarketData/Services/ReplayVerificationService.php');
        $correctionRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php');

        foreach ([
            'assertLiveArtifactMutationAllowed',
            'SEALED_DATASET_MUTATION_BLOCKED',
            "join('eod_publications as pub'",
            "leftJoin('eod_runs as run'",
            "where('pub.seal_state', 'SEALED')",
            "where('run.terminal_status', 'SUCCESS')",
            "where('run.publishability_state', 'READABLE')",
        ] as $needle) {
            $this->assertStringContainsString($needle, $artifactRepository);
        }

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
            $this->assertStringContainsString('ACTIVE SESSION:', $document);
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
        $this->assertStringContainsString('OK (5 tests, 434 assertions)', $implementationBlock);
        $this->assertStringContainsString('OK (416 tests, 6066 assertions)', $implementationBlock);
        $this->assertStringContainsString('OK (11 tests, 874 assertions)', $contractBlock);
        $this->assertStringContainsString('OK (146 tests, 3470 assertions)', $contractBlock);
    }

    public function test_runtime_paths_do_not_reintroduce_latest_or_max_trade_date_shortcuts_for_artifact_integrity(): void
    {
        foreach ($this->runtimePhpFiles() as $file) {
            $source = $this->read($file);

            $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/latest\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/ORDER\s+BY\s+trade_date\s+DESC/i', $source, $file);
        }
    }

    private function sqlTableBlock(string $schema, string $table): string
    {
        $pattern = '/CREATE TABLE IF NOT EXISTS '.preg_quote($table, '/').' \(.*?\) ENGINE=InnoDB;/ms';
        $this->assertMatchesRegularExpression($pattern, $schema);
        preg_match($pattern, $schema, $match);

        return $match[0];
    }

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

    private function runtimePhpFiles(): array
    {
        $roots = [
            $this->projectPath('app/Application/MarketData'),
            $this->projectPath('app/Infrastructure/Persistence/MarketData'),
            $this->projectPath('app/Infrastructure/MarketData'),
            $this->projectPath('app/Console/Commands/MarketData'),
        ];

        $files = [];
        foreach ($roots as $root) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = str_replace(dirname(__DIR__, 3).DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        sort($files);

        return $files;
    }
}
