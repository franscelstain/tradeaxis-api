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

    public function test_artisan_blocks_destructive_testing_migrations_when_database_is_not_testing()
    {
        $artisan = $this->read('artisan');

        $this->assertStringContainsString('tradeaxis_enforce_testing_database_isolation', $artisan);
        $this->assertStringContainsString('BLOCKED_TESTING_DATABASE_ENV', $artisan);
        $this->assertStringContainsString("'migrate:fresh'", $artisan);
        $this->assertStringContainsString("'migrate:refresh'", $artisan);
        $this->assertStringContainsString("'migrate:reset'", $artisan);
        $this->assertStringContainsString("'db:wipe'", $artisan);
        $this->assertStringContainsString("'tradeaxis_testing'", $artisan);
        $this->assertStringContainsString('$app[\'config\']->get(\'database.connections.\'.$connection.\'.database\'', $artisan);
        $this->assertStringContainsString('tradeaxis_enforce_testing_database_isolation($app, $input);', $artisan);
        $this->assertLessThan(
            strpos($artisan, '$kernel->handle('),
            strpos($artisan, 'tradeaxis_enforce_testing_database_isolation($app, $input);'),
            'Testing DB isolation guard must run before the migration command is handled.'
        );
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
}
