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

        foreach ([
            'Ops Environment Baseline',
            'LOCKED_LOCAL_RUNTIME_PROOF',
            'CONTAINER_RUNTIME_BLOCKED_BY_UNSUPPORTED_PHP',
            'PHP 8.3.x',
            '`>= 7.3` and `< 8.4`',
            'Lumen 8.3.4',
            'PHPUnit 9.6.x',
            'dom',
            'mbstring',
            'pdo_mysql',
            'pdo_sqlite',
            'xmlwriter',
            'Asia/Jakarta',
            'ENV_UNSUPPORTED_PHP_VERSION',
            'PHP Warning',
            'PHP Deprecated',
            'vendor/framework deprecation',
            'not a runtime PASS',
        ] as $needle) {
            $this->assertStringContainsString($needle, $baseline);
        }
    }

    public function test_inventory_records_runtime_baseline_matrix_patch_matrix_and_validation_status(): void
    {
        $inventory = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');

        foreach ([
            'OPS_ENVIRONMENT_BASELINE_CONTRACT',
            'LOCKED_LOCAL_RUNTIME_PROOF',
            'OPERATOR_LOCAL_TARGETED_RUNTIME_PROOF_PASS',
            'BLOCKED_CONTAINER_RUNTIME_ENV',
            'Runtime environment baseline matrix',
            'Command output matrix',
            'Patch matrix',
            'Composer / platform decision matrix',
            'Validation matrix',
            'PHP 8.4.16',
            'NOISY_OUTPUT_NOT_EVIDENCE',
            'EXPECTED_FAIL_CLOSED',
            'DEFER_WITH_REASON',
            'DO_NOT_ADD_IN_THIS_PATCH',
            'Final local validation completed',
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
            $this->assertStringContainsString("ACTIVE SESSION:
- Final Provider Smoke Passed / Ops Runtime Parity Lock", $document);
            $this->assertStringContainsString('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $document);
            $this->assertStringContainsString('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $document);
            $this->assertStringContainsString('OPS_ENVIRONMENT_BASELINE_CONTRACT', $document);
            $this->assertStringContainsString('OPS_COMMAND_SURFACE_RUNTIME_MATRIX_CONTRACT', $document);
            $this->assertStringContainsString('LOCKED_LOCAL_RUNTIME_PROOF', $document);
            $this->assertStringContainsString('OK (435 tests, 6299 assertions)', $document);
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

    public function test_clean_output_policy_forbids_warning_and_deprecation_evidence_noise(): void
    {
        $baseline = $this->read('docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md');
        $inventory = $this->read('docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md');
        $combined = $baseline.$inventory;

        foreach ([
            'PHP Warning',
            'PHP Deprecated',
            'Deprecated:',
            'PHP Notice',
            'vendor/framework deprecation',
            'missing-extension warning',
            'timezone warning',
            'debug noise',
            'stack trace caused by environment mismatch',
            'not to suppress warnings or redirect stderr away from evidence',
        ] as $needle) {
            $this->assertStringContainsString($needle, $combined);
        }
    }
}
