<?php

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\Contracts\IdentityResolution;
use App\Application\SecurityIdentity\Contracts\IdentityResolver;
use App\Application\SecurityIdentity\FoundationService;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketDataFoundationIdentityConsumerOnMariaDbTest extends TestCase
{
    private array $ownedDatabases = [];
    private array $connections = [];
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        $base = config('database.connections.mysql');
        config()->set('database.connections.si_md_consumer_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('si_md_consumer_control');
        $this->assertSame('mysql', $this->control->getDriverName());
        $this->assertSame('tradeaxis_testing', $this->control->selectOne('SELECT DATABASE() AS name')->name);
        $this->assertStringContainsString('MariaDB', $this->control->selectOne('SELECT VERSION() AS version')->version);

        try {
            $suffix = bin2hex(random_bytes(6));
            foreach (['a', 'b'] as $side) {
                $name = 'tradeaxis_testing_md_foundation_consumer_'.$suffix.'_'.$side;
                $this->assertSame(0, (int) $this->control->selectOne(
                    'SELECT COUNT(*) AS n FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
                    [$name]
                )->n);
                $this->control->statement('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
                $this->ownedDatabases[] = $name;
                $connection = 'si_md_consumer_'.$side;
                config()->set('database.connections.'.$connection, array_merge($base, ['database' => $name, 'collation' => 'utf8mb4_bin']));
                DB::purge($connection);
                $this->connections[$side] = $connection;
                config()->set('database.default', $connection);
                $this->assertSame($name, DB::connection($connection)->selectOne('SELECT DATABASE() AS name')->name);
                $this->migration()->up();
                $this->createLegacyNavigationTables($connection);
            }
            $this->seedDifferentAllocationHistories();
        } catch (\Throwable $e) {
            $this->cleanup();
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        foreach ($this->connections as $connection) { DB::purge($connection); }
        foreach ($this->ownedDatabases as $name) {
            if (!preg_match('/^tradeaxis_testing_md_foundation_consumer_[a-f0-9]{12}_[ab]$/D', $name)) {
                throw new \RuntimeException('UNSAFE_TEST_DATABASE_NAME');
            }
            if ($this->control->selectOne('SELECT DATABASE() AS name')->name !== 'tradeaxis_testing') {
                throw new \RuntimeException('TEST_CONTROL_DATABASE_CHANGED');
            }
            $this->control->statement('DROP DATABASE `'.$name.'`');
        }
        $this->ownedDatabases = [];
        DB::purge('si_md_consumer_control');
    }

    private function migration()
    {
        require_once base_path('database/migrations/2026_09_29_000001_create_shared_security_identity_foundation.php');
        return new CreateSharedSecurityIdentityFoundation();
    }

    private function createLegacyNavigationTables(string $connection): void
    {
        $schema = Schema::connection($connection);
        $schema->create('tickers', function (Blueprint $table): void {
            $table->bigIncrements('ticker_id');
            $table->string('ticker_code', 32);
        });
        $schema->create('md_issuers', function (Blueprint $table): void {
            $table->bigIncrements('issuer_id');
            $table->string('issuer_uid', 64);
        });
        $schema->create('md_instruments', function (Blueprint $table): void {
            $table->bigIncrements('instrument_id');
            $table->string('instrument_uid', 64);
        });
        $schema->create('md_listings', function (Blueprint $table): void {
            $table->bigIncrements('listing_id');
            $table->string('listing_uid', 64);
            $table->unsignedBigInteger('legacy_ticker_id');
        });
    }

    private function seedDifferentAllocationHistories(): void
    {
        $a = DB::connection($this->connections['a']);
        $b = DB::connection($this->connections['b']);
        $a->table('tickers')->insert(['ticker_code' => 'IKPM']);
        $a->table('md_issuers')->insert(['issuer_uid' => 'legacy-a']);
        $a->table('md_instruments')->insert(['instrument_uid' => 'legacy-a']);
        $a->table('md_listings')->insert(['listing_uid' => 'legacy-a', 'legacy_ticker_id' => 1]);

        foreach (range(1, 11) as $n) {
            $b->table('tickers')->insert(['ticker_code' => 'DUMMY'.$n]);
            $b->table('md_issuers')->insert(['issuer_uid' => 'dummy-'.$n]);
            $b->table('md_instruments')->insert(['instrument_uid' => 'dummy-'.$n]);
            $b->table('md_listings')->insert(['listing_uid' => 'dummy-'.$n, 'legacy_ticker_id' => $n]);
        }
        $b->table('tickers')->insert(['ticker_code' => 'IKPM']);
        $b->table('md_issuers')->insert(['issuer_uid' => 'legacy-b']);
        $b->table('md_instruments')->insert(['instrument_uid' => 'legacy-b']);
        $b->table('md_listings')->insert(['listing_uid' => 'legacy-b', 'legacy_ticker_id' => 12]);

        $b->statement('ALTER TABLE si_entities AUTO_INCREMENT = 19000');
        $b->statement('ALTER TABLE si_revisions AUTO_INCREMENT = 23000');
    }

    private function service(string $side = 'a'): FoundationService
    {
        return new FoundationService(new FoundationRepository(DB::connection($this->connections[$side])));
    }

    private function consumer(string $side = 'a'): TemporalIdentityRepository
    {
        return new TemporalIdentityRepository($this->service($side));
    }

    private function bootstrap(string $side = 'a'): FoundationRegistry
    {
        $service = $this->service($side);
        $service->bootstrap(
            storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260928-v1'),
            base_path('resources/security_identity/foundation-source-basis-20260928-v1.registry.json')
        );
        $service->bootstrap(
            storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260929-ikpm-listing-v2'),
            base_path('resources/security_identity/foundation-source-basis-20260929-ikpm-listing-v2.registry.json')
        );
        return $service->bootstrap(
            storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260929-ikpm-current-window-v3'),
            base_path('resources/security_identity/foundation-source-basis-20260929-ikpm-current-window-v3.registry.json')
        );
    }

    private function target(string $side = 'a'): array
    {
        config()->set('database.default', $this->connections[$side]);
        return $this->consumer($side)->resolveFoundationProviderContext(
            'YAHOO_FINANCE',
            'IKPM.JK',
            '2026-09-29 00:38:15',
            '2026-09-29 00:38:15'
        );
    }

    private function blocked(callable $work, string $reason): void
    {
        $caught = null;
        try {
            $work();
        } catch (\RuntimeException $e) {
            $caught = $e;
        }
        $this->assertNotNull($caught, 'Expected foundation-required intake to fail closed: '.$reason);
        $this->assertStringContainsString($reason, $caught->getMessage());
    }

    public function test_exact_ikpm_instant_propagates_retained_roots_to_market_data(): void
    {
        $this->bootstrap();
        $result = $this->target();

        $this->assertSame('SHARED_SECURITY_IDENTITY_FOUNDATION', $result['identity_source']);
        $this->assertSame('RESOLVED', $result['resolution_state']);
        $this->assertSame('EVIDENCED_TEMPORAL_IDENTITY', $result['resolution_reason']);
        $this->assertSame('35ff26e0-a043-41c8-8c6e-f7f2d4227214', $result['issuer_id']);
        $this->assertSame('93453e6d-87b4-4131-8379-a91fb1766fac', $result['instrument_id']);
        $this->assertSame('6eb68d44-81bf-4469-be4f-27ce32ef03d5', $result['listing_id']);
        $this->assertSame('IDX', $result['exchange_namespace']);
        $this->assertSame('IKPM', $result['exchange_symbol']);
        $this->assertSame('YAHOO_FINANCE', $result['provider_namespace']);
        $this->assertSame('IKPM.JK', $result['provider_symbol']);
        $this->assertSame('2026-09-29 00:38:15', $result['effective_at']);
        $this->assertSame('2026-09-29 00:38:15', $result['knowledge_cutoff']);
        $this->assertArrayNotHasKey('ticker_id', $result);
        $this->assertCount(4, $result['foundation_revisions']);
    }

    public function test_temporal_and_provider_gaps_fail_closed_without_legacy_fallback(): void
    {
        $this->bootstrap();
        $consumer = $this->consumer();

        $this->blocked(fn () => $consumer->resolveFoundationProviderContext('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:14', '2026-09-29 00:38:16'), 'FOUNDATION_IDENTITY_HELD:LISTING_EVIDENCE_UNAVAILABLE');
        $this->blocked(fn () => $consumer->resolveFoundationProviderContext('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:16', '2026-09-29 00:38:16'), 'FOUNDATION_IDENTITY_HELD:LISTING_EVIDENCE_UNAVAILABLE');
        $this->blocked(fn () => $consumer->resolveFoundationProviderContext('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:15', '2026-09-29 00:38:14'), 'FOUNDATION_IDENTITY_HELD:LIFECYCLE_OUTSIDE_VERIFIED_SCOPE');
        $this->blocked(fn () => $consumer->resolveFoundationProviderContext('YAHOO_FINANCE', 'IKPM', '2026-09-29 00:38:15', '2026-09-29 00:38:15'), 'FOUNDATION_IDENTITY_HELD:IDENTITY_OR_MAPPING_UNAVAILABLE');

        $this->assertSame(1, DB::connection($this->connections['a'])->table('tickers')->where('ticker_code', 'IKPM')->count());
        $this->assertSame(1, DB::connection($this->connections['a'])->table('md_listings')->count());
    }

    public function test_ambiguous_or_incomplete_foundation_result_cannot_become_legacy_identity(): void
    {
        $ambiguous = new class implements IdentityResolver {
            public function resolve(string $namespace, string $symbol, string $effectiveAtUtc, string $knowledgeCutoffUtc): IdentityResolution
            {
                return new IdentityResolution('AMBIGUOUS', 'OVERLAPPING_PROVIDER_MAPPING');
            }
        };
        $this->blocked(
            fn () => (new TemporalIdentityRepository($ambiguous))->resolveFoundationProviderContext('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:15', '2026-09-29 00:38:15'),
            'FOUNDATION_IDENTITY_AMBIGUOUS:OVERLAPPING_PROVIDER_MAPPING'
        );

        $incomplete = new class implements IdentityResolver {
            public function resolve(string $namespace, string $symbol, string $effectiveAtUtc, string $knowledgeCutoffUtc): IdentityResolution
            {
                return new IdentityResolution('RESOLVED', 'EVIDENCED_TEMPORAL_IDENTITY', null, null, null, [
                    'exchange_symbol' => 'IKPM', 'exchange_namespace' => 'IDX',
                    'provider_namespace' => 'YAHOO_FINANCE', 'provider_symbol' => 'IKPM.JK',
                    'venue' => 'IDX', 'market_segment' => 'REGULAR', 'board' => 'DEVELOPMENT',
                    'effective_at' => $effectiveAtUtc, 'knowledge_cutoff' => $knowledgeCutoffUtc,
                ]);
            }
        };
        $this->blocked(
            fn () => (new TemporalIdentityRepository($incomplete))->resolveFoundationProviderContext('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:15', '2026-09-29 00:38:15'),
            'FOUNDATION_IDENTITY_INVALID:STABLE_ROOTS_REQUIRED'
        );
    }

    public function test_market_data_result_is_identical_across_local_allocation_histories(): void
    {
        $this->bootstrap('a');
        $this->bootstrap('b');

        $a = $this->target('a');
        $b = $this->target('b');
        $this->assertSame($a, $b);
        $this->assertSame($a, $this->target('a'));
        $this->assertNotSame(
            DB::connection($this->connections['a'])->table('tickers')->where('ticker_code', 'IKPM')->value('ticker_id'),
            DB::connection($this->connections['b'])->table('tickers')->where('ticker_code', 'IKPM')->value('ticker_id')
        );
        $this->assertNotSame(
            DB::connection($this->connections['a'])->table('md_listings')->max('listing_id'),
            DB::connection($this->connections['b'])->table('md_listings')->max('listing_id')
        );
        $this->assertNotSame(
            DB::connection($this->connections['a'])->table('si_entities')->min('row_id'),
            DB::connection($this->connections['b'])->table('si_entities')->min('row_id')
        );
        $this->assertSame('6eb68d44-81bf-4469-be4f-27ce32ef03d5', $a['listing_id']);
        $this->assertSame('IKPM.JK', $a['provider_symbol']);
    }

    public function test_shared_resolver_contract_is_available_without_querying_on_registration(): void
    {
        $this->assertInstanceOf(IdentityResolver::class, app(IdentityResolver::class));
    }
}
