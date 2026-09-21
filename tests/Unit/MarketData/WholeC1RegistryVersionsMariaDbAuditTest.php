<?php

use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\ProducerInputCompletionManifest;
use App\Infrastructure\Persistence\MarketData\ProducerInputScope;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataMariaDb;

/**
 * MD-B18-A002 E032 successor: the SQLite all-engaged audit found exactly one whole-C1 gap,
 * registry_versions.reason_registry.entries, caused by the SQLite mirror never seeding
 * eod_reason_codes. This targets only that one check against the real migrated MariaDB
 * (tradeaxis_testing), inside a rolled-back transaction, without seeding or writing anything new.
 */
class WholeC1RegistryVersionsMariaDbAuditTest extends TestCase
{
    use UsesMarketDataMariaDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataMariaDb();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataMariaDb();
        parent::tearDown();
    }

    public function test_eod_reason_codes_exactly_matches_the_canonical_seed_file(): void
    {
        $seedSql = file_get_contents(base_path('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql'));
        preg_match_all("/\\('([A-Z0-9_]+)'/", $seedSql, $matches);
        $seedCodes = array_values(array_unique($matches[1]));
        sort($seedCodes);
        $this->assertNotEmpty($seedCodes, 'The canonical seed file must declare at least one code.');

        $dbCodes = $this->marketDataMariaDb()->table('eod_reason_codes')->orderBy('code')->pluck('code')->map(function ($c) {
            return (string) $c;
        })->all();
        sort($dbCodes);

        $this->assertSame($seedCodes, $dbCodes, 'tradeaxis_testing.eod_reason_codes must exactly match the canonical seed file, not a subset, superset, or foreign row.');
    }

    public function test_registry_versions_reason_registry_entries_is_satisfied_against_real_migrated_mariadb(): void
    {
        $reasonCodeCount = $this->marketDataMariaDb()->table('eod_reason_codes')->count();
        $this->assertGreaterThan(0, $reasonCodeCount, 'Precondition: eod_reason_codes must be non-empty before this check can be meaningful.');

        $runs = new EodRunRepository();
        $run = $runs->getOrCreateOwningRun('2026-03-24', 'api', 'INGEST_BARS', null, 'c1-registry-mariadb-audit');
        $repository = new RunInputCaptureRepository();

        ProducerInputScope::during($run, 'RUN_CONTEXT', 'registry-audit/v1', function () {
            return null;
        }, $repository);

        $validator = new ProducerInputCompletionManifest();
        $result = $validator->inspect($run, $repository);

        $this->assertNotContains(
            'registry_versions.reason_registry.entries',
            $result['missing_paths'],
            'Against real migrated MariaDB with the canonical reason-code seed applied, this gap must not appear.'
        );

        $registryCapture = null;
        foreach ($repository->forRun((int) $run->run_id) as $row) {
            if ($row['component_key'] !== 'registry_versions') continue;
            $registryCapture = $repository->verify($row);
        }
        $this->assertNotNull($registryCapture, 'Expected a registry_versions capture from the producer scope.');
        $this->assertNotEmpty($registryCapture['rows'][0]['reason_entries'], 'reason_entries must be non-empty when eod_reason_codes has real rows.');
        $this->assertSame([], $registryCapture['rows'][0]['missing_paths'], 'ProducerRegistrySnapshot itself must not self-report a reason-registry gap.');
        $this->assertCount($reasonCodeCount, $registryCapture['rows'][0]['reason_entries']);
    }
}
