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


    /**
     * The baseline document states a supported PHP range. This asserts the interpreter
     * actually running the suite satisfies it, which is the fact that matters.
     */
    public function test_running_php_version_is_inside_the_documented_supported_range(): void
    {
        $this->assertGreaterThanOrEqual(70300, PHP_VERSION_ID, 'PHP must be >= 7.3 per the ops baseline.');
        $this->assertLessThan(80400, PHP_VERSION_ID, 'PHP must be < 8.4 per the ops baseline.');
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



    public function test_operational_runbook_points_to_environment_baseline_gate(): void
    {
        $runbook = $this->read('docs/market_data/development/implementation/ops/OPERATIONAL_RUNBOOK.md');

        foreach ([
            'Ops environment baseline gate',
            'No current document owns the environment baseline as a contract',
            'PHP must be `>= 7.3` and `< 8.4`',
            'ENV_UNSUPPORTED_PHP_VERSION',
            'BLOCKED_CONTAINER_RUNTIME_ENV',
            'OpsEnvironmentBaselineStaticGuardTest.php',
        ] as $needle) {
            $this->assertStringContainsString($needle, $runbook);
        }
    }

}
