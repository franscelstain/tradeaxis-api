<?php

use PHPUnit\Framework\TestCase;

class CoveragePolicyDocsStaticGuardTest extends TestCase
{
    private function projectPath(string $path): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function read(string $path): string
    {
        $fullPath = $this->projectPath($path);
        $this->assertFileExists($fullPath);

        return file_get_contents($fullPath);
    }


    public function test_locked_schema_and_migration_normalize_legacy_blocked_coverage_state(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');
        $metadata = $this->read('docs/market_data/development/implementation/db/DB_FIELDS_AND_METADATA.md');
        $migration = $this->read('database/migrations/2026_05_18_000001_normalize_legacy_blocked_coverage_gate_state.php');

        $this->assertStringContainsString("coverage_gate_state ENUM('PASS','FAIL','NOT_EVALUABLE') NULL", $schema);
        $this->assertStringNotContainsString("coverage_gate_state ENUM('PASS','FAIL','NOT_EVALUABLE','BLOCKED') NULL", $schema);
        $this->assertStringContainsString("Final allowed values: `PASS`, `FAIL`, `NOT_EVALUABLE`", $metadata);
        $this->assertStringContainsString("->where(\$column, 'BLOCKED')", $migration);
        $this->assertStringContainsString("->update([\$column => 'NOT_EVALUABLE'])", $migration);
        $this->assertStringContainsString("'eod_runs', 'coverage_gate_state'", $migration);
        $this->assertStringContainsString("'md_replay_daily_metrics', 'coverage_gate_state'", $migration);
        $this->assertStringContainsString("'md_replay_daily_metrics', 'expected_coverage_gate_state'", $migration);
        $this->assertStringNotContainsString('quality_gate_state', $migration);
    }

    public function test_market_data_coverage_min_threshold_stays_locked_to_098(): void
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');
        $contract = $this->read('docs/market_data/authority/strategy/book/Coverage_Gate_Enforcement_Contract_LOCKED.md');

        $this->assertStringContainsString("env('MARKET_DATA_COVERAGE_MIN', 0.98)", $config);
        $this->assertStringContainsString('MARKET_DATA_COVERAGE_MIN=0.98', $envExample);
        $this->assertStringContainsString('MARKET_DATA_COVERAGE_MIN=0.98', $envTesting);
        $this->assertStringContainsString('MARKET_DATA_COVERAGE_MIN = 0.98', $contract);
        $this->assertStringNotContainsString('MARKET_DATA_COVERAGE_MIN=0.95', $envExample.$envTesting.$config.$contract);
    }

    /**
     * The normaliser must be applied wherever a coverage state reaches an operator surface.
     *
     * What the normaliser does is now driven by CoverageStateNormalizationTest — that BLOCKED
     * never survives, that an unrecognised state fails closed to NOT_EVALUABLE rather than PASS,
     * that absent stays absent, and that the original verdict remains recoverable. The four state
     * strings previously asserted inside the normaliser file are gone with it: a file naming all
     * four and returning its input unchanged satisfied every one of them.
     *
     * What stays is the wiring, because it is a claim about three separate files calling it and
     * no single execution shows that a fourth surface forgot to.
     */
    public function test_operator_surfaces_normalise_coverage_state_and_keep_the_legacy_trace(): void
    {
        foreach ([
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
            'app/Application/MarketData/Services/ReplayVerificationService.php',
            'app/Console/Commands/MarketData/AbstractMarketDataCommand.php',
        ] as $surface) {
            $source = $this->read($surface);

            $this->assertStringContainsString('CoverageGateStateNormalizer::normalize', $source, $surface);
            $this->assertStringContainsString('legacy_coverage_gate_state_raw', $source, $surface);
        }
    }

    public function test_locked_contract_docs_require_coverage_aliases_and_legacy_raw_trace(): void
    {
        $coverage = $this->read('docs/market_data/authority/strategy/book/Coverage_Gate_Enforcement_Contract_LOCKED.md');
        $eod = $this->read('docs/market_data/authority/strategy/book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md');
        $evidence = $this->read('docs/market_data/authority/strategy/ops/Audit_Evidence_Pack_Contract_LOCKED.md');

        foreach (['expected_bar_count', 'available_bar_count', 'missing_bar_count', 'legacy_coverage_gate_state_raw'] as $field) {
            $this->assertStringContainsString($field, $coverage.$eod.$evidence);
        }

        $this->assertStringContainsString('coverage_gate_state=NOT_EVALUABLE', $coverage.$eod);
        $this->assertStringContainsString('legacy input `BLOCKED` must not appear as final `coverage_gate_state`', $evidence);
    }
}
