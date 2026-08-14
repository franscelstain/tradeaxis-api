<?php

use PHPUnit\Framework\TestCase;

class TestingDatabaseIsolationStaticGuardTest extends TestCase
{
    private function read($path)
    {
        $fullPath = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);

        $this->assertFileExists($fullPath, $path.' must exist.');

        return file_get_contents($fullPath);
    }

    public function test_bootstrap_loads_environment_specific_file_for_cli_env_option()
    {
        $bootstrap = $this->read('bootstrap/app.php');

        $this->assertStringContainsString('tradeaxis_runtime_environment_name', $bootstrap);
        $this->assertStringContainsString("strpos((string) \$argument, '--env=') === 0", $bootstrap);
        $this->assertStringContainsString("'.env.'.\$environment", $bootstrap);
        $this->assertStringContainsString('$environmentFile = tradeaxis_runtime_environment_file($basePath);', $bootstrap);
        $this->assertStringContainsString('$environmentFile', $bootstrap);
        $this->assertStringContainsString('new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables', $bootstrap);
    }

    /**
     * Ordering, which execution cannot show from inside the process: the guard must run before
     * the command is handed to the kernel. A guard that ran afterwards would refuse a database
     * that had already been dropped.
     *
     * What the guard decides is proven by DestructiveMigrationGuardTest. The command list and the
     * expected database name are no longer asserted as strings here — they live on the guard
     * class and that test drives every one of them.
     */
    public function test_the_destructive_migration_guard_runs_before_the_kernel_handles_the_command()
    {
        $artisan = $this->read('artisan');

        $this->assertStringContainsString('tradeaxis_enforce_testing_database_isolation($app, $input);', $artisan);
        $this->assertLessThan(
            strpos($artisan, '$kernel->handle('),
            strpos($artisan, 'tradeaxis_enforce_testing_database_isolation($app, $input);'),
            'Destructive migration guard must run before the migration command is handled.'
        );
    }

    /**
     * The environment must not gate the decision.
     *
     * Both guards used to return early unless the environment was already `testing`, which left
     * `php artisan migrate:fresh` with no --env — resolving to .env and therefore to the
     * production database — completely unguarded. The prohibition is kept as a source check
     * because it is the shape of the old defect, and reintroducing it would restore a guard that
     * only refuses the one database it is safe to drop.
     */
    public function test_the_guard_does_not_return_early_based_on_environment()
    {
        $artisan = $this->read('artisan');

        $this->assertStringNotContainsString('if (! $isTestingEnvironment) {', $artisan);
        $this->assertStringNotContainsString("!== 'testing') {\n            return;", $artisan);
    }

    public function test_testing_env_database_is_explicitly_isolated_from_local_database()
    {
        $env = $this->read('.env');
        $envTesting = $this->read('.env.testing');
        $envExample = $this->read('.env.example');

        $this->assertMatchesRegularExpression('/^DB_DATABASE=tradeaxis$/m', $env);
        $this->assertMatchesRegularExpression('/^APP_ENV=testing$/m', $envTesting);
        $this->assertMatchesRegularExpression('/^DB_DATABASE=tradeaxis_testing$/m', $envTesting);
        $this->assertMatchesRegularExpression('/^DB_DATABASE=tradeaxis$/m', $envExample);
        $this->assertStringNotContainsString('DB_DATABASE=tradeaxis_testing', $env);
    }

    public function test_audit_docs_record_testing_database_isolation_contract()
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');
        $inventory = $this->read('docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md');

        foreach ([$status, $tracker, $inventory] as $document) {
            $this->assertStringContainsString('TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT', $document);
            $this->assertStringContainsString('BLOCKED_TESTING_DATABASE_ENV', $document);
            $this->assertStringContainsString('tradeaxis_testing', $document);
            $this->assertStringContainsString('TestingDatabaseIsolationStaticGuardTest.php', $document);
        }
    }

    public function test_artisan_blocks_explicit_unsafe_testing_db_before_unsupported_php_guard()
    {
        $artisan = $this->read('artisan');

        $this->assertStringContainsString('tradeaxis_early_enforce_testing_database_env_override', $artisan);
        $this->assertStringContainsString('tradeaxis_early_runtime_env', $artisan);
        $this->assertStringContainsString("getenv('DB_DATABASE')", $artisan);
        $this->assertStringContainsString('BLOCKED_TESTING_DATABASE_ENV', $artisan);
        $this->assertLessThan(
            strpos($artisan, 'ENV_UNSUPPORTED_PHP_VERSION'),
            strpos($artisan, 'tradeaxis_early_enforce_testing_database_env_override($_SERVER'),
            'Explicit unsafe DB_DATABASE override must be blocked before unsupported PHP exits so negative DB proof remains available.'
        );
    }
}
