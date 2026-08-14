<?php

use PHPUnit\Framework\TestCase;

/**
 * Environment variables are read in config/ and nowhere else.
 *
 * `ConfigEnvGovernanceCleanupStaticGuardTest` checks that the env templates declare exactly the
 * keys config/market_data.php reads — a genuinely derived rule, and it stays where it is. What
 * nothing checked is the other direction: that no runtime code reaches past config to the
 * environment itself.
 *
 * That rule holds today, which is why it is worth writing down now rather than after it breaks.
 *
 * Two things go wrong when it breaks, and both are silent.
 *
 * Types. config/ applies a cast to every value it reads — `(int) env(...)`, `(float) env(...)`.
 * A service calling env() directly receives the raw string. A coverage threshold of "0.98"
 * compared against a float, or a lot size of "100" passed where an int is expected, behaves
 * differently in ways that produce numbers rather than errors.
 *
 * Config caching. Laravel's cached config is built once and env() outside config/ returns null
 * afterwards. A threshold read that way silently becomes its fallback — or null — in the one
 * environment where it matters, and only there. Nothing in a test run would show it.
 */
class ConfigIsTheOnlyEnvReaderTest extends TestCase
{
    /**
     * @return array<string, string> relative path => source
     */
    private function runtimeSources(): array
    {
        $root = dirname(__DIR__, 3);
        $appRoot = $root.DIRECTORY_SEPARATOR.'app';

        $sources = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appRoot));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $sources[str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname())] = file_get_contents($file->getPathname());
            }
        }

        ksort($sources);

        return $sources;
    }

    public function test_the_scan_reads_the_runtime(): void
    {
        $this->assertGreaterThan(80, count($this->runtimeSources()));
    }

    /**
     * `$app->environment('testing')` is a different thing and stays allowed — it asks which
     * environment is active, not what a variable contains.
     */
    public function test_no_runtime_file_reads_the_environment_directly(): void
    {
        $violations = [];

        foreach ($this->runtimeSources() as $path => $source) {
            if (preg_match('/(?<![>$\w])env\s*\(\s*[\'"]/', $source)) {
                $violations[] = $path.' calls env()';
            }

            if (strpos($source, 'getenv(') !== false) {
                $violations[] = $path.' calls getenv()';
            }

            if (preg_match('/\$_ENV\s*\[/', $source) || preg_match('/\$_SERVER\s*\[\s*[\'"][A-Z_]+[\'"]\s*\]/', $source)) {
                $violations[] = $path.' reads a superglobal environment array';
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Environment variables are read in config/ and nowhere else.\n"
            ."config/ applies the cast and the default; a direct read gets neither, and returns "
            ."null once config is cached."
        );
    }

    /**
     * Every market-data config key must carry a default.
     *
     * env('X') with no default returns null when the variable is unset. For a threshold, a
     * window length or a lookback that is not a loud failure — it is a null flowing into a
     * comparison, which is how a coverage gate ends up evaluating against nothing.
     *
     * APP_KEY is excluded and is the one case where absence should be fatal rather than
     * defaulted: a framework key with a quietly invented default would be worse than none.
     */
    public function test_every_market_data_config_key_has_a_default(): void
    {
        $config = file_get_contents(dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'market_data.php');

        preg_match_all("/env\(\s*'([A-Z0-9_]+)'\s*\)/", $config, $matches);

        $this->assertSame(
            [],
            array_values(array_unique($matches[1])),
            'These config keys would resolve to null when the variable is unset.'
        );
    }

    /**
     * Guards the guard: the pattern above must actually match the shape config uses, or a file
     * full of defaultless keys would pass.
     */
    public function test_the_default_check_recognises_a_defaultless_key(): void
    {
        $sample = "'threshold' => env('SOME_KEY'), 'other' => env('OTHER_KEY', 5),";

        preg_match_all("/env\(\s*'([A-Z0-9_]+)'\s*\)/", $sample, $matches);

        $this->assertSame(['SOME_KEY'], $matches[1]);
    }

    /**
     * And the env-reading prohibition must recognise a real call rather than passing because the
     * pattern never matches anything.
     */
    public function test_the_env_prohibition_recognises_a_real_call(): void
    {
        foreach ([
            "\$ratio = env('MARKET_DATA_COVERAGE_MIN_RATIO', 0.98);",
            '$value = getenv("SOME_KEY");',
            "\$mode = \$_ENV['APP_ENV'];",
        ] as $offending) {
            $detected = preg_match('/(?<![>$\w])env\s*\(\s*[\'"]/', $offending)
                || strpos($offending, 'getenv(') !== false
                || preg_match('/\$_ENV\s*\[/', $offending);

            $this->assertTrue((bool) $detected, 'Should have been detected: '.$offending);
        }

        // And must not fire on the legitimate environment query.
        $this->assertSame(0, preg_match('/(?<![>$\w])env\s*\(\s*[\'"]/', "\$app->environment('testing')"));
    }
}
