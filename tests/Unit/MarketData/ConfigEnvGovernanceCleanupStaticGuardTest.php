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

}
