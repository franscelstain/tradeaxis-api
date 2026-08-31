<?php

use PHPUnit\Framework\TestCase;

/**
 * `MD-B15-A001` boundary guards for coverage edge-case obligations that were satisfied by
 * construction and proven by nothing, so a later edit could have removed them silently.
 *
 * `Coverage_Edge_Cases_Contract_LOCKED.md` states that multi-source mixing is pruned rather than
 * merely unused, that the delay window and every retry control are governed configuration, that a
 * row outside the requested trade date is refused as stale, that the fallback target set is closed,
 * and that no edge case reaches `READABLE` without a coverage `PASS`.
 * `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` adds that a coverage `PASS` may never be cited as evidence
 * of data correctness, calendar correctness, or session completeness.
 */
class CoverageEdgeCaseBoundaryB15Test extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    private function read(string $relative): string
    {
        $path = $this->root().'/'.$relative;
        $this->assertFileExists($path, $relative.' must exist for this guard to mean anything');

        return (string) file_get_contents($path);
    }

    /** @return array<int,string> every PHP source file under app/ */
    private function appSources(): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root().'/app'));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        sort($files);

        return $files;
    }

    /**
     * MD-S014-R0008 and R0010: one run has one primary source identity, and no active configuration
     * key permits row-level mixing.
     */
    public function test_no_configuration_permits_mixing_sources_inside_one_run(): void
    {
        $config = $this->read('config/market_data.php');

        foreach (['multi_source', 'allow_mixed_sources', 'mixed_source'] as $token) {
            $this->assertStringNotContainsString(
                $token,
                $config,
                'config/market_data.php exposes '.$token.', which would permit row-level source mixing '
                    .'that no locked source-combination contract allows'
            );
        }

        // The source identity that a run does carry must still exist, or the prohibition has no
        // subject and this guard would pass on an empty concept.
        $this->assertStringContainsString(
            'default_source_name',
            $config,
            'the single primary source identity must remain a governed configuration'
        );
    }

    /** MD-S014-R0011: the two named keys are pruned, not merely unset. */
    public function test_the_pruned_multi_source_config_keys_have_not_returned(): void
    {
        $scanned = 0;
        foreach ($this->appSources() as $path) {
            $source = (string) file_get_contents($path);
            $scanned++;

            foreach (['MARKET_DATA_MULTI_SOURCE_MODE', 'MARKET_DATA_ALLOW_MIXED_SOURCES'] as $key) {
                $this->assertStringNotContainsString(
                    $key,
                    $source,
                    basename($path).' reintroduces the pruned config surface '.$key
                );
            }
        }

        $this->assertGreaterThan(100, $scanned, 'the scan must reach the application surface');
    }

    /** MD-S014-R0024 and R0025: the delay window is governed configuration with a locked default. */
    public function test_the_delay_window_is_a_governed_configuration_with_the_locked_default(): void
    {
        $config = $this->read('config/market_data.php');

        $this->assertMatchesRegularExpression(
            "/'delay_window_minutes'\s*=>\s*\(int\)\s*env\(\s*'MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES'\s*,\s*60\s*\)/",
            $config,
            'the delay window must resolve from MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES with the locked default of 60'
        );
    }

    /** MD-S014-R0032 to R0034: every retry control is a governed configuration key. */
    public function test_every_retry_control_is_a_governed_configuration_key(): void
    {
        $config = $this->read('config/market_data.php');

        foreach ([
            'api_retry_max' => 'MARKET_DATA_API_RETRY_MAX',
            'api_backoff_ms' => 'MARKET_DATA_API_BACKOFF_MS',
            'api_throttle_qps' => 'MARKET_DATA_API_THROTTLE_QPS',
        ] as $key => $env) {
            $this->assertMatchesRegularExpression(
                "/'".preg_quote($key, '/')."'\s*=>\s*\(int\)\s*env\(\s*'".preg_quote($env, '/')."'/",
                $config,
                $key.' must resolve from '.$env.' rather than a literal'
            );
        }
    }

    /**
     * MD-S014-R0040: retry exhaustion produces no automatic `READABLE` state. The source path may
     * hold or fail; it may never promote.
     */
    public function test_retry_exhaustion_never_reaches_a_readable_state(): void
    {
        $adapter = $this->read('app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php');

        // The adapter is a source surface. It must not decide readability at all.
        foreach (['READABLE', 'publishability_state', 'promoteDaily'] as $token) {
            $this->assertStringNotContainsString(
                $token,
                $adapter,
                'the source adapter references '.$token.', so retry exhaustion could reach a readability decision'
            );
        }

        // Retry telemetry must still exist there, or the prohibition guards an empty surface.
        $this->assertStringContainsString(
            'api_retry_max',
            $adapter,
            'the adapter must still own retry behaviour for this boundary to have a subject'
        );
    }

    /** MD-S014-R0044: a row outside the requested trade date is refused under its own reason. */
    public function test_a_row_outside_the_requested_trade_date_is_refused_as_stale(): void
    {
        $ingest = $this->read('app/Application/MarketData/Services/EodBarsIngestService.php');

        $this->assertStringContainsString(
            'RUN_STALE_DATA',
            $ingest,
            'the single-day ingest boundary must refuse a stale row under RUN_STALE_DATA'
        );
        $this->assertMatchesRegularExpression(
            '/RUN_STALE_DATA/',
            $this->read('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql'),
            'RUN_STALE_DATA must be a registered reason code, not an ad-hoc string'
        );
    }

    /**
     * MD-S014-R0070 to R0077: the fallback target set is closed to the previous readable
     * publication. A `MAX(date)` shortcut is named explicitly because it is the easy wrong answer.
     */
    public function test_no_forbidden_fallback_target_is_reachable(): void
    {
        $surfaces = [
            'app/Application/MarketData/Services/FinalizeDecisionService.php',
            'app/Application/MarketData/Services/PublicationFinalizeOutcomeService.php',
        ];

        $scanned = 0;
        foreach ($surfaces as $relative) {
            $source = $this->read($relative);
            $scanned++;

            // A fallback resolved by taking the newest date is the MAX(date) shortcut the contract
            // forbids: it cannot tell a readable publication from a stale or unsealed one.
            $this->assertSame(
                0,
                preg_match_all('/max\(\s*[\'"]?trade_date/i', $source),
                $relative.' resolves a fallback by newest trade date, which is the MAX(date) shortcut'
            );
        }

        $this->assertSame(2, $scanned, 'both finalize surfaces must be scanned');
    }

    /**
     * MD-S024-R0063 and MD-S015-R0113: a coverage `PASS` may never be cited as evidence of data
     * correctness, calendar correctness, or session completeness.
     */
    public function test_no_surface_cites_a_coverage_pass_as_proof_of_correctness(): void
    {
        $forbidden = [
            '/coverage[_ ](pass|passed)[^.\n]{0,40}(data|values?)[_ ]?(are|is)?[_ ]?correct/i',
            '/coverage[_ ](pass|passed)[^.\n]{0,40}session[_ ]?complete/i',
            '/coverage[_ ](pass|passed)[^.\n]{0,40}calendar[_ ]?(is[_ ])?correct/i',
        ];

        $scanned = 0;
        foreach ($this->appSources() as $path) {
            $source = (string) file_get_contents($path);
            $scanned++;
            foreach ($forbidden as $pattern) {
                $this->assertSame(
                    0,
                    preg_match_all($pattern, $source),
                    basename($path).' cites a coverage pass as proof of something coverage cannot prove'
                );
            }
        }

        $this->assertGreaterThan(100, $scanned, 'the scan must reach the application surface');
    }

    /**
     * MD-S014-R0079 to R0084: the contract names the runtime classes that own each edge. Each must
     * exist and must still carry the responsibility the contract assigns it.
     */
    public function test_every_runtime_class_the_contract_names_exists_and_owns_its_role(): void
    {
        $mapping = [
            'app/Application/MarketData/Services/CoverageGateEvaluator.php' => 'coverage_ratio',
            'app/Application/MarketData/Services/FinalizeDecisionService.php' => 'coverage',
            'app/Application/MarketData/Services/MarketDataPipelineService.php' => 'coverage',
            'app/Application/MarketData/Services/EodBarsIngestService.php' => 'RUN_STALE_DATA',
            'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php' => 'api_retry_max',
            'docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql' => 'RUN_COVERAGE_LOW',
        ];

        foreach ($mapping as $relative => $token) {
            $this->assertStringContainsString(
                $token,
                $this->read($relative),
                $relative.' no longer carries the responsibility the edge-case contract assigns it'
            );
        }

        $this->assertCount(6, $mapping, 'every named runtime surface must be checked');
    }
}
