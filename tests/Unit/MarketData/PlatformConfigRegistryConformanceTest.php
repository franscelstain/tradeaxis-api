<?php

use App\Infrastructure\MarketData\Config\PlatformConfigRegistry;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use Tests\Support\UsesMarketDataSqlite;

/** B04 executable proof for the strategy-owned resolved config-key register. */
class PlatformConfigRegistryConformanceTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_current_runtime_configuration_exactly_matches_the_locked_resolved_key_register(): void
    {
        $registry = new PlatformConfigRegistry();
        $registry->assertResolvedConfiguration(config('market_data'));

        $this->assertGreaterThan(100, count($registry->definitions()), 'registry parsing must not pass vacuously');
        $this->assertSame('empty string (zero bytes)', $registry->definitions()['market_data.hash.null_token']['default']);
        $this->assertSame('—', $registry->definitions()['market_data.hash.null_token']['environment_input']);
        $this->assertSame('', config('market_data.hash.null_token'));
        $this->assertStringNotContainsString('MARKET_DATA_HASH_NULL_TOKEN', file_get_contents(base_path('.env.example')));
        $this->assertStringNotContainsString('MARKET_DATA_HASH_NULL_TOKEN', file_get_contents(base_path('.env.testing')));
    }

    public function test_an_unregistered_runtime_key_blocks_snapshot_creation(): void
    {
        config()->set('market_data.unregistered_output_knob', 'unsafe');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CONFIG_REGISTRY_KEY_MISMATCH');
        $this->expectExceptionMessage('unregistered=market_data.unregistered_output_knob');

        (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
    }

    public function test_a_missing_registered_runtime_key_blocks_snapshot_creation(): void
    {
        $marketData = config('market_data');
        unset($marketData['hash']['algorithm']);
        config()->set('market_data', $marketData);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CONFIG_REGISTRY_KEY_MISMATCH');
        $this->expectExceptionMessage('missing=market_data.hash.algorithm');

        (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
    }

    public function test_a_registered_key_with_the_wrong_type_blocks_snapshot_creation(): void
    {
        config()->set('market_data.coverage_gate.min_ratio', '0.98');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CONFIG_REGISTRY_TYPE_MISMATCH');

        (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
    }

    public function test_a_non_empty_hash_null_token_blocks_snapshot_creation(): void
    {
        config()->set('market_data.hash.null_token', '[empty]');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CONFIG_HASH_NULL_TOKEN_NOT_EMPTY');

        (new MarketDataConfigSnapshotRepository())->resolveForRun('2026-03-20');
    }

    public function test_registry_metadata_cannot_reintroduce_the_removed_environment_override(): void
    {
        $source = file_get_contents(base_path(
            'docs/market_data/authority/strategy/registry/Platform_Config_Registry_LOCKED.md'
        ));
        $mutated = str_replace(
            'empty string (zero bytes) | — | `../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md`',
            'empty string (zero bytes) | `MARKET_DATA_HASH_NULL_TOKEN` | `../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md`',
            $source
        );
        $path = tempnam(sys_get_temp_dir(), 'platform-config-registry-');
        file_put_contents($path, $mutated);

        try {
            (new PlatformConfigRegistry($path))->assertResolvedConfiguration(config('market_data'));
            $this->fail('A registry environment override must fail closed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'CONFIG_REGISTRY_HASH_NULL_TOKEN_AUTHORITY_MISMATCH',
                $exception->getMessage()
            );
        } finally {
            @unlink($path);
        }
    }
}
