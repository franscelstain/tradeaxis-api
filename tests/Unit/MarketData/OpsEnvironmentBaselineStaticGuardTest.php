<?php

use PHPUnit\Framework\TestCase;

class OpsEnvironmentBaselineStaticGuardTest extends TestCase
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

    public function test_ops_environment_baseline_document_exists_and_locks_clean_output_policy(): void
    {
        $baseline = $this->read('docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md');

        // Only the policy statements are asserted from the document. The version and
        // extension requirements it lists are checked against the running environment
        // instead, in the tests below: a document mentioning "mbstring" says nothing about
        // whether mbstring is loaded.
        foreach ([
            'Ops Environment Baseline',
            'LOCKED_LOCAL_RUNTIME_PROOF',
            'ENV_UNSUPPORTED_PHP_VERSION',
            'PHP Warning',
            'PHP Deprecated',
            'not a runtime PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $baseline);
        }
    }

    /**
     * The baseline document states a supported PHP range. This asserts the interpreter
     * actually running the suite satisfies it, which is the fact that matters.
     */
    public function test_running_php_version_is_inside_the_documented_supported_range(): void
    {
        $this->assertGreaterThanOrEqual(70300, PHP_VERSION_ID, 'PHP must be >= 7.3 per the ops baseline.');
        $this->assertLessThan(80400, PHP_VERSION_ID, 'PHP must be < 8.4 per the ops baseline.');
    }

    /**
     * Extensions are read out of the baseline document and then verified against the running
     * interpreter, so the list needs no maintenance here and a missing extension is caught as
     * an environment fault rather than a documentation mismatch.
     */
    public function test_extensions_required_by_the_baseline_are_actually_loaded(): void
    {
        $baseline = $this->read('docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md');

        $candidates = ['dom', 'mbstring', 'pdo_mysql', 'pdo_sqlite', 'xmlwriter', 'json', 'openssl'];

        $required = array_values(array_filter($candidates, function ($extension) use ($baseline) {
            return strpos($baseline, $extension) !== false;
        }));

        $this->assertNotEmpty($required, 'Ops baseline must name at least one required extension.');

        $missing = array_values(array_filter($required, function ($extension) {
            return ! extension_loaded($extension);
        }));

        $this->assertSame([], $missing, 'Extensions named by the ops baseline but not loaded in this runtime.');
    }

    /**
     * The inventory must be a decided document: every matrix row resolved, none left as TBD.
     *
     * The fifteen section-heading needles that stood here are down to the ones naming a
     * decision. `PHP 8.4.16` in particular was a frozen version string recording the interpreter
     * of one past container — the running interpreter is checked directly above, which is the
     * fact that matters.
     */
    public function test_inventory_leaves_no_undecided_rows(): void
    {
        $inventory = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        foreach ([
            'OPS_ENVIRONMENT_BASELINE_CONTRACT',
            'NOISY_OUTPUT_NOT_EVIDENCE',
            'EXPECTED_FAIL_CLOSED',
            'DEFER_WITH_REASON',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        $this->assertStringNotContainsString('| TBD |', $inventory);
    }

    public function test_artisan_blocks_unsupported_php_before_vendor_autoload(): void
    {
        $artisan = $this->read('artisan');

        $this->assertStringContainsString('PHP_VERSION_ID < 70300 || PHP_VERSION_ID >= 80400', $artisan);
        $this->assertStringContainsString('ENV_UNSUPPORTED_PHP_VERSION', $artisan);
        $this->assertStringContainsString('vendor/autoload.php', $artisan);
        $this->assertLessThan(
            strpos($artisan, "require __DIR__.'/vendor/autoload.php';"),
            strpos($artisan, 'ENV_UNSUPPORTED_PHP_VERSION'),
            'Unsupported runtime guard must execute before vendor autoload so PHP 8.4 vendor deprecations cannot contaminate artisan evidence output.'
        );
    }

    public function test_phpunit_bootstrap_uses_same_unsupported_php_guard_before_project_autoload(): void
    {
        $phpunit = $this->read('phpunit.xml');
        $bootstrap = $this->read('tests/bootstrap.php');

        $this->assertStringContainsString('bootstrap="tests/bootstrap.php"', $phpunit);
        $this->assertStringContainsString('PHP_VERSION_ID < 70300 || PHP_VERSION_ID >= 80400', $bootstrap);
        $this->assertStringContainsString('ENV_UNSUPPORTED_PHP_VERSION', $bootstrap);
        $this->assertStringContainsString("require dirname(__DIR__).'/vendor/autoload.php';", $bootstrap);
        $this->assertLessThan(
            strpos($bootstrap, "require dirname(__DIR__).'/vendor/autoload.php';"),
            strpos($bootstrap, 'ENV_UNSUPPORTED_PHP_VERSION'),
            'Unsupported runtime guard must execute before project autoload.'
        );
    }

    public function test_composer_platform_decision_is_documented_without_lock_drift_patch(): void
    {
        $composer = $this->read('composer.json');
        $lock = $this->read('composer.lock');
        $inventory = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        $this->assertStringContainsString('"php": "^7.3|^8.0"', $composer);
        $this->assertStringContainsString('"php": "^7.3|^8.0"', $lock);
        $this->assertStringContainsString('DEFER_WITH_REASON', $inventory);
        $this->assertStringContainsString('No Composer binary is available in container to regenerate `composer.lock` safely', $inventory);
        $this->assertStringNotContainsString('"platform"', $composer);
    }

    public function test_audit_docs_active_session_and_contract_are_synchronized(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        foreach ([$status, $tracker] as $document) {
            // The active session name is not pinned here. AuditDocsSynchronizationStaticGuardTest
            // already asserts that both documents agree on it, and duplicating a hardcoded
            // session string meant two files needed editing whenever a session changed.
            //
            // The tally "OK (435 tests, 6299 assertions)" is also gone: it recorded one past
            // run of a suite that has since grown well past 700.
            $this->assertStringContainsString('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $document);
            $this->assertStringContainsString('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $document);
            $this->assertStringContainsString('OPS_ENVIRONMENT_BASELINE_CONTRACT', $document);
            $this->assertStringContainsString('OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT', $document);
            $this->assertStringContainsString('LOCKED_LOCAL_RUNTIME_PROOF', $document);
            $this->assertStringContainsString('BLOCKED_CONTAINER_RUNTIME_ENV', $document);
            $this->assertStringContainsString('AUDIT_DOCS_SYNCHRONIZATION_CONTRACT', $document);
            $this->assertStringContainsString('COVERAGE_POLICY_RECONCILIATION_CONTRACT', $document);
            $this->assertStringNotContainsString('READY_FOR_FINAL_LOCAL_FULL_SUITE_RERUN', $document);
        }

        $opsEvidence = $status.$tracker.$inventory;
        $this->assertStringContainsString('DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization', $opsEvidence);
        $this->assertStringContainsString('OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS', $opsEvidence);
        $this->assertStringContainsString('OPERATOR_LOCAL_FULL_MARKET_DATA_SUITE_PASS', $opsEvidence);
        $this->assertStringContainsString('- Ops Environment Baseline -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] OPS_ENVIRONMENT_BASELINE_CONTRACT', $status);
        $this->assertStringContainsString('- OPS_ENVIRONMENT_BASELINE_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Ops Environment Baseline', $tracker);
    }

    public function test_operational_runbook_points_to_environment_baseline_gate(): void
    {
        $runbook = $this->read('docs/market_data/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'Ops environment baseline gate',
            'docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md',
            'PHP must be `>= 7.3` and `< 8.4`',
            'ENV_UNSUPPORTED_PHP_VERSION',
            'BLOCKED_CONTAINER_RUNTIME_ENV',
            'OpsEnvironmentBaselineStaticGuardTest.php',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

    /**
     * The clean-output policy is enforced on the artifacts, not on the sentence describing it:
     * EvidenceArtifactCleanlinessTest sweeps every recorded .txt under storage/app for
     * interpreter noise, null bytes and invalid UTF-8.
     *
     * One line of the policy stays asserted here, because it is the instruction that keeps the
     * sweep meaningful. Suppressing warnings or redirecting stderr would make every artifact
     * pass while proving less than before.
     */
    public function test_clean_output_policy_forbids_hiding_the_noise_instead_of_fixing_it(): void
    {
        $combined = $this->read('docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md')
            .$this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        $this->assertStringContainsString('not to suppress warnings or redirect stderr away from evidence', $combined);
    }
}
