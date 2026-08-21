<?php

use PHPUnit\Framework\TestCase;

class ConfigEnvGovernanceCleanupStaticGuardTest extends TestCase
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

    public function test_inventory_records_schema_config_env_pruning_and_validation_status(): void
    {
        $inventory = $this->read('docs/market_data/audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md');

        foreach ([
            'CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT',
            'Config / ENV Governance Cleanup',
            'Schema / Config Alignment Matrix',
            'Config / ENV Inventory Matrix',
            '`tickers.is_active` Decision Matrix',
            'Pruning Matrix',
            'Caller Trace Matrix',
            'Patch Matrix',
            'Validation Matrix',
            'READY_FOR_LOCAL_RUNTIME_VALIDATION',
            'BLOCKED_CONTAINER_RUNTIME_ENV',
            'MARKET_DATA_TICKERS_ACTIVE_VALUE',
            'MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES',
            'MARKET_DATA_TICKERS_ACTIVE_YES_VALUE',
            'REMOVE',
            'RENAME_AND_UPDATE_CALLERS',
            'DEFER_WITH_REASON',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        $this->assertStringNotContainsString('| TBD |', $inventory);
    }

    /**
     * Four tests used to guard one invariant here: the ticker universe must select active
     * tickers by numeric value and never by a 'Yes'/'No' string. They did it by matching
     * strings across the migration, the DDL, the SQLite bootstrap, the generic ticker doc,
     * the config, both env templates, the repository source and two fixture files.
     *
     * TickerMasterRepositoryTest proves the same thing by execution: it seeds a ticker with
     * `is_active => 'Yes'` and asserts the universe excludes it. That holds however the
     * filter is written, and would still fail if someone reintroduced a string alias with
     * different wording than the strings these tests happened to look for.
     *
     * Only the config-value assertion is kept, because the numeric default is what the
     * behavioural test depends on.
     */
    public function test_ticker_active_value_config_stays_numeric(): void
    {
        // No container here: this class extends the plain PHPUnit TestCase, so config() is
        // unavailable. The runtime value is exercised by TickerMasterRepositoryTest, which
        // boots the application.
        $this->assertStringContainsString(
            "'active_value' => (int) env('MARKET_DATA_TICKERS_ACTIVE_VALUE', 1)",
            $this->read('config/market_data.php')
        );
    }

    public function test_active_env_keys_are_synchronized_between_env_templates_and_config(): void
    {
        $config = $this->read('config/market_data.php');
        preg_match_all("/env\('([^']+)'/", $config, $configMatches);
        $configEnvKeys = array_values(array_unique($configMatches[1]));
        sort($configEnvKeys);

        foreach (['.env.example', '.env.testing'] as $envFile) {
            $env = $this->read($envFile);
            preg_match_all('/^(MARKET_DATA_[A-Z0-9_]+)=/m', $env, $envMatches);
            $templateEnvKeys = array_values(array_unique($envMatches[1]));
            sort($templateEnvKeys);

            $this->assertSame($configEnvKeys, $templateEnvKeys, $envFile.' must contain exactly the active MARKET_DATA_* keys declared in config/market_data.php.');
        }
    }

    public function test_unused_multi_source_keys_are_pruned_not_left_as_active_config(): void
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');
        $coverageContract = $this->read('docs/market_data/authority/strategy/book/Coverage_Edge_Cases_Contract_LOCKED.md');

        foreach ([$config, $envExample, $envTesting] as $document) {
            $this->assertStringNotContainsString('MARKET_DATA_MULTI_SOURCE_MODE', $document);
            $this->assertStringNotContainsString('MARKET_DATA_ALLOW_MIXED_SOURCES', $document);
            $this->assertStringNotContainsString('multi_source_mode', $document);
            $this->assertStringNotContainsString('allow_mixed_sources', $document);
        }

        $this->assertStringContainsString('there is no active env/config key that permits multi-source row mixing', $coverageContract);
        $this->assertStringContainsString('are pruned as unused/stale config surfaces', $coverageContract);
    }

    /**
     * The delay window is read as an int with an explicit default, and read through config.
     *
     * That it is present in the env templates is covered by the key-synchronisation test above,
     * and what the window does — a dataset arriving inside it is HELD rather than published — is
     * driven by FinalizeDecisionServiceTest. The cast is what stays here: a string minute count
     * compared against an integer is a comparison that succeeds and answers wrongly.
     */
    public function test_delay_window_is_read_as_an_integer_with_a_default(): void
    {
        $this->assertStringContainsString(
            "'delay_window_minutes' => (int) env('MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES', 60)",
            $this->read('config/market_data.php')
        );
    }

    public function test_cleanup_does_not_regress_source_mode_read_side_or_db_integrity_contracts(): void
    {
        $inventory = $this->read('docs/market_data/audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md');
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([
            'source-mode, coverage, read-side pointer, publication, replay, evidence, or DB integrity policy',
            'Source mode non-regression',
            'Read-side non-regression',
            'DB integrity FK/implicit policy non-regression',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker);
        }

        foreach ([
            'IMPORT_PROMOTE_SEPARATION_CONTRACT',
            'READ_SIDE_POINTER_ENFORCEMENT_CONTRACT',
            'DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT',
        ] as $contract) {
            $this->assertStringContainsString($contract, $tracker);
        }
    }

    /**
     * F-024: the legacy price-basis selector is pruned, not merely deprecated in prose.
     *
     * Its registry entry licensed it only "while compatibility code exists". Nothing read it —
     * EodIndicatorsComputeService::vectorConfig() wrote it into the indicator vector config and
     * IndicatorVectorService never looked at it — so the key advertised an authority over price
     * basis that the platform had already moved to AnalyticalProductIdentityService. A deprecated
     * key that still ships in config and both env templates reads to an operator as a live knob.
     *
     * The sibling test test_active_env_keys_are_synchronized_between_env_templates_and_config
     * enforces exact key parity, so a partial removal fails there. This test pins the intent: the
     * key must be absent everywhere, and it must not creep back into the vector config.
     */
    public function test_legacy_price_basis_selector_is_pruned_not_left_as_active_config(): void
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');
        $computeService = $this->read('app/Application/MarketData/Services/EodIndicatorsComputeService.php');

        foreach ([$envExample, $envTesting] as $document) {
            $this->assertStringNotContainsString('MARKET_DATA_PRICE_BASIS_DEFAULT=', $document);
        }

        $this->assertStringNotContainsString("env('MARKET_DATA_PRICE_BASIS_DEFAULT'", $config);
        $this->assertStringNotContainsString("'price_basis_default' =>", $config);
        $this->assertStringNotContainsString("'price_basis_default' =>", $computeService);

        $registry = $this->read('docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md');
        $this->assertStringContainsString('PRUNED 2026-08-11', $registry);
        $this->assertStringContainsString('do not reintroduce', $registry);
    }

    public function test_audit_docs_preserve_config_env_cleanup_history_without_requiring_it_as_active_session(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([$status, $tracker] as $document) {
            $this->assertStringContainsString('CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md', $document);
            $this->assertStringContainsString('ConfigEnvGovernanceCleanupStaticGuardTest.php', $document);
            $this->assertStringContainsString('TickerMasterRepositoryTest.php', $document);
            $this->assertStringContainsString('BLOCKED_CONTAINER_RUNTIME_ENV', $document);
            $this->assertStringContainsString('LOCKED_LOCAL_PHPUNIT_PASS', $document);
            $this->assertStringContainsString('DB Integrity FK / Implicit Integrity Decision', $document, 'Previous audit history must remain present.');
        }

        // The active session NAME was pinned here, in both documents.
        //
        // That contradicted the rule the sibling guard already records: pinning it freezes the
        // audit trail to one past session and forces an edit here whenever a new session starts,
        // without ever catching a defect. Worse, the two guards disagreed — one asserted the name
        // must be a specific string while the other deliberately refused to.
        //
        // AuditCrossReferenceIntegrityTest holds the property that does matter: both documents
        // must name the SAME active session, whichever it is.
        $this->assertStringContainsString('MARKET_DATA_CONSUMER_READ_MODEL_CONTRACT', $status.$tracker);
        $this->assertStringContainsString('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $status.$tracker);
        $this->assertStringContainsString('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $status.$tracker);
        $this->assertStringContainsString('DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization', $status.$tracker);
        $this->assertStringContainsString('Ops Environment Baseline', $status.$tracker, 'Latest Ops Environment history must remain present.');
        $this->assertStringContainsString('- Config / ENV Governance Cleanup -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT', $status);
        $this->assertStringContainsString('- CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Config / ENV Governance Cleanup', $tracker);
        // The historical tally "OK (427 tests, 6198 assertions)" is no longer asserted. It
        // records one past run against a suite that has since more than doubled, so it could
        // only be archaeology or churn.
    }
}
