<?php

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\FoundationService;
use App\Domain\SecurityIdentity\RegistryAdmission;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FrozenSourcePackageReader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class IkpmListingAdmissionOnMariaDbTest extends TestCase
{
    private array $ownedDatabases = [];
    private array $connections = [];
    private $control;
    private string $scratch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scratch = storage_path('app/market_data/evidence/MD-B10-A002/foundation-ikpm-listing-20260929-v1/test-work-'.bin2hex(random_bytes(6)));
        if (file_exists($this->scratch) || !mkdir($this->scratch, 0777, true)) { throw new \RuntimeException('TEST_WORK_NAMESPACE_NOT_EMPTY'); }
        $base = config('database.connections.mysql');
        config()->set('database.connections.si_ikpm_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('si_ikpm_control');
        $this->assertSame('mysql', $this->control->getDriverName());
        $this->assertSame('tradeaxis_testing', $this->control->selectOne('SELECT DATABASE() AS name')->name);
        $this->assertStringContainsString('MariaDB', $this->control->selectOne('SELECT VERSION() AS version')->version);
        try {
            $suffix = bin2hex(random_bytes(6));
            foreach (['a', 'b'] as $side) {
                $name = 'tradeaxis_testing_si_ikpm_'.$suffix.'_'.$side;
                $this->assertSame(0, (int)$this->control->selectOne('SELECT COUNT(*) AS n FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$name])->n);
                $this->control->statement('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
                $this->ownedDatabases[] = $name;
                $connection = 'si_ikpm_'.$side;
                config()->set('database.connections.'.$connection, array_merge($base, ['database' => $name, 'collation' => 'utf8mb4_bin']));
                DB::purge($connection);
                $this->connections[$side] = $connection;
                config()->set('database.default', $connection);
                $this->assertSame($name, DB::connection($connection)->selectOne('SELECT DATABASE() AS name')->name);
                $this->migration()->up();
            }
        } catch (\Throwable $e) { $this->cleanup(); throw $e; }
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
            if (!preg_match('/^tradeaxis_testing_si_ikpm_[a-f0-9]{12}_[ab]$/D', $name)) { throw new \RuntimeException('UNSAFE_TEST_DATABASE_NAME'); }
            if ($this->control->selectOne('SELECT DATABASE() AS name')->name !== 'tradeaxis_testing') { throw new \RuntimeException('TEST_CONTROL_DATABASE_CHANGED'); }
            $this->control->statement('DROP DATABASE `'.$name.'`');
        }
        $this->ownedDatabases = [];
        if (isset($this->scratch) && is_dir($this->scratch)) {
            $root = realpath(storage_path('app/market_data/evidence/MD-B10-A002/foundation-ikpm-listing-20260929-v1'));
            $resolved = realpath($this->scratch);
            if (!$root || !$resolved || is_link($this->scratch) || dirname($resolved) !== $root || !preg_match('/^test-work-[a-f0-9]{12}$/D', basename($resolved))) { throw new \RuntimeException('UNSAFE_TEST_WORK_PATH'); }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->scratch, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $item) {
                if ($item->isDir()) { rmdir($item->getPathname()); } else { unlink($item->getPathname()); }
            }
            rmdir($this->scratch);
        }
        DB::purge('si_ikpm_control');
    }

    private function migration()
    {
        require_once base_path('database/migrations/2026_09_29_000001_create_shared_security_identity_foundation.php');
        return new CreateSharedSecurityIdentityFoundation();
    }

    private function service(string $side = 'a'): FoundationService { return new FoundationService(new FoundationRepository(DB::connection($this->connections[$side]))); }
    private function corePackage(): string { return storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260928-v1'); }
    private function coreAssignments(): string { return base_path('resources/security_identity/foundation-source-basis-20260928-v1.registry.json'); }
    private function listingPackage(): string { return storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260929-ikpm-listing-v2'); }
    private function listingAssignments(): string { return base_path('resources/security_identity/foundation-source-basis-20260929-ikpm-listing-v2.registry.json'); }

    private function bootstrap(string $side = 'a'): FoundationRegistry
    {
        $this->service($side)->bootstrap($this->corePackage(), $this->coreAssignments());
        return $this->service($side)->bootstrap($this->listingPackage(), $this->listingAssignments());
    }

    private function restore(array $document, string $side = 'a'): FoundationRegistry
    {
        $path = $this->scratch.'/registry-'.$side.'-'.bin2hex(random_bytes(3)).'.json';
        file_put_contents($path, (new FoundationRegistry($document))->json());
        return $this->service($side)->restore($path, hash_file('sha256', $path));
    }

    private function packageCopy(): string
    {
        $copy = $this->scratch.'/package-'.bin2hex(random_bytes(3));
        mkdir($copy);
        foreach (new \DirectoryIterator($this->listingPackage()) as $entry) {
            if ($entry->isFile()) { copy($entry->getPathname(), $copy.'/'.$entry->getFilename()); }
        }
        return $copy;
    }

    private function rejected(callable $work, string $reason): void
    {
        try { $work(); $this->fail('Expected rejection: '.$reason); }
        catch (\DomainException | \InvalidArgumentException $e) { $this->assertStringContainsString($reason, $e->getMessage()); }
    }

    public function test_real_ikpm_listing_keeps_existing_roots_and_ignores_local_allocations(): void
    {
        foreach (['si_entities', 'si_revisions'] as $table) {
            DB::connection($this->connections['b'])->statement('ALTER TABLE '.$table.' AUTO_INCREMENT = 9000');
        }
        $a = $this->bootstrap('a');
        $b = $this->bootstrap('b');
        $this->assertSame($a->json(), $b->json());
        $this->assertNotSame(DB::connection($this->connections['a'])->table('si_entities')->min('row_id'), DB::connection($this->connections['b'])->table('si_entities')->min('row_id'));
        $entities = array_column($a->document()['entities'], null, 'identity_id');
        $this->assertArrayHasKey('35ff26e0-a043-41c8-8c6e-f7f2d4227214', $entities);
        $this->assertArrayHasKey('93453e6d-87b4-4131-8379-a91fb1766fac', $entities);
        $this->assertSame('93453e6d-87b4-4131-8379-a91fb1766fac', $entities['6eb68d44-81bf-4469-be4f-27ce32ef03d5']['parent_identity_id']);
        $counts = array_count_values(array_column($a->document()['entities'], 'entity_type'));
        $this->assertSame(2, $counts['ISSUER']);
        $this->assertSame(1, $counts['INSTRUMENT']);
        $this->assertSame(1, $counts['LISTING']);
        $this->assertSame($a->json(), $this->bootstrap('a')->json());
        $this->assertSame(4, DB::connection($this->connections['a'])->table('si_entities')->count());
        $this->assertSame(7, DB::connection($this->connections['a'])->table('si_revisions')->count());
    }

    public function test_export_rebuild_retains_ikpm_listing_identity_and_provenance(): void
    {
        $a = $this->bootstrap('a');
        DB::connection($this->connections['b'])->statement('ALTER TABLE si_entities AUTO_INCREMENT = 15000');
        DB::connection($this->connections['b'])->statement('ALTER TABLE si_revisions AUTO_INCREMENT = 15000');
        $b = $this->restore($a->document(), 'b');
        $this->assertSame($a->json(), $b->json());
        $this->assertSame('6eb68d44-81bf-4469-be4f-27ce32ef03d5', array_values(array_filter($b->document()['entities'], static fn (array $e): bool => $e['entity_type'] === 'LISTING'))[0]['identity_id']);
        $package = array_column($b->document()['packages'], null, 'package_version')[FrozenSourcePackageReader::IKPM_LISTING_VERSION];
        $this->assertSame('E-MD-B10-A002-007', $package['admission_evidence']);
        $this->assertSame(FrozenSourcePackageReader::IKPM_LISTING_ASSIGNMENTS_HASH, $package['assignment_sha256']);
        $this->assertSame(FrozenSourcePackageReader::MANIFEST_HASH, $package['predecessor_manifest_sha256']);
        $this->assertArrayHasKey('https://query1.finance.yahoo.com/v8/finance/chart/IKPM.JK?range=1mo&interval=1d', $package['sources']);
    }

    public function test_admitted_listing_and_board_do_not_overstate_consumer_history(): void
    {
        $registry = $this->bootstrap();
        $revisions = array_column($registry->document()['revisions'], null, 'revision_id');
        $this->assertSame('LISTING', $revisions['214a9276-5359-4d7f-9fd2-597215001e29']['revision_type']);
        $this->assertSame('2023-11-08 16:59:59', $revisions['214a9276-5359-4d7f-9fd2-597215001e29']['data']['history_verified_through']);
        $this->assertSame('REGULAR', $revisions['44334144-1ff3-494c-8b10-8a15c3094d50']['data']['market_segment']);
        $this->assertSame('DEVELOPMENT', $revisions['44334144-1ff3-494c-8b10-8a15c3094d50']['data']['board']);
        $this->assertSame('2026-09-28 18:35:16', $revisions['4ccda414-4c16-4ab2-b8c1-78058ce98bb3']['valid_from']);
        $historical = $this->service()->resolve('YAHOO_FINANCE', 'IKPM.JK', '2023-11-08 02:00:00', '2026-09-29 00:00:00');
        $this->assertSame('HELD', $historical->state);
        $this->assertSame('IDENTITY_OR_MAPPING_UNAVAILABLE', $historical->reason);
        $this->assertNull($historical->listingId);
        $current = $this->service()->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-28 18:35:16', '2026-09-29 00:00:00');
        $this->assertSame('HELD', $current->state);
        $this->assertSame('LIFECYCLE_OUTSIDE_VERIFIED_SCOPE', $current->reason);
        $this->assertNull($current->issuerId);
        $holds = array_column($registry->document()['holds'], null, 'scope');
        $this->assertArrayHasKey('IKPM:provider-history', $holds);
        $this->assertArrayHasKey('IKPM:lifecycle-after-first-day', $holds);
        $this->assertArrayHasKey('IKPM:consumer-resolution', $holds);
    }

    public function test_package_tamper_missing_listing_evidence_and_wrong_relationship_fail_closed(): void
    {
        $this->service()->bootstrap($this->corePackage(), $this->coreAssignments());
        $copy = $this->packageCopy();
        file_put_contents($copy.'/yahoo_chart_response.json', "{}\n");
        $this->rejected(fn () => $this->service()->bootstrap($copy, $this->listingAssignments()), 'SOURCE_MEMBER_FINGERPRINT_MISMATCH:yahoo_chart_response.json');
        $this->assertSame(3, DB::connection($this->connections['a'])->table('si_entities')->count());

        $copy = $this->packageCopy();
        unlink($copy.'/idx_listing_announcement_extract.json');
        $this->rejected(fn () => $this->service()->bootstrap($copy, $this->listingAssignments()), 'SOURCE_MEMBER_MISSING:idx_listing_announcement_extract.json');
        $this->assertSame(0, DB::connection($this->connections['a'])->table('si_entities')->where('entity_type', 'LISTING')->count());

        $incoming = (new FrozenSourcePackageReader())->read($this->listingPackage(), $this->listingAssignments())->document();
        $incoming['entities'][0]['parent_identity_id'] = '35ff26e0-a043-41c8-8c6e-f7f2d4227214';
        $this->rejected(fn () => $this->restore($incoming), 'ENTITY_PARENT_INVALID');
        $this->assertSame(0, DB::connection($this->connections['a'])->table('si_entities')->where('entity_type', 'LISTING')->count());
    }

    public function test_complete_registry_rejects_listing_parented_to_issuer(): void
    {
        $reader = new FrozenSourcePackageReader();
        $core = $reader->read($this->corePackage(), $this->coreAssignments())->document();
        $successor = $reader->read($this->listingPackage(), $this->listingAssignments())->document();
        $document = $core;
        foreach (['packages', 'entities', 'revisions', 'holds'] as $group) {
            $document[$group] = array_merge($document[$group], $successor[$group]);
        }
        foreach ($document['entities'] as &$entity) {
            if ($entity['entity_type'] === 'LISTING') { $entity['parent_identity_id'] = '35ff26e0-a043-41c8-8c6e-f7f2d4227214'; }
        }
        unset($entity);
        $this->rejected(fn () => (new RegistryAdmission())->validate(new FoundationRegistry($document)), 'ENTITY_PARENT_INVALID');
    }

    public function test_missing_board_or_provider_mapping_never_gets_fabricated(): void
    {
        foreach (['a', 'b'] as $side) { $this->service($side)->bootstrap($this->corePackage(), $this->coreAssignments()); }
        $incoming = (new FrozenSourcePackageReader())->read($this->listingPackage(), $this->listingAssignments())->document();

        $withoutBoard = $incoming;
        $withoutBoard['revisions'] = array_values(array_filter($withoutBoard['revisions'], static fn (array $r): bool => $r['revision_type'] !== 'BOARD'));
        $this->restore($withoutBoard, 'a');
        $board = $this->service('a')->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-28 18:35:16', '2026-09-29 00:00:00');
        $this->assertSame('HELD', $board->state);
        $this->assertSame('BOARD_EVIDENCE_UNAVAILABLE', $board->reason);

        $withoutProvider = $incoming;
        $withoutProvider['revisions'] = array_values(array_filter($withoutProvider['revisions'], static fn (array $r): bool => $r['revision_type'] !== 'PROVIDER_MAPPING'));
        $this->restore($withoutProvider, 'b');
        $provider = $this->service('b')->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-28 18:35:16', '2026-09-29 00:00:00');
        $this->assertSame('HELD', $provider->state);
        $this->assertSame('IDENTITY_OR_MAPPING_UNAVAILABLE', $provider->reason);
        $this->assertNull($provider->listingId);
    }

    public function test_registered_command_admits_the_successor_package_without_direct_sql(): void
    {
        config()->set('database.default', $this->connections['a']);
        $this->service()->bootstrap($this->corePackage(), $this->coreAssignments());
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $exit = $kernel->call('security-identity:bootstrap', [
            'package' => $this->listingPackage(),
            '--assignments' => 'resources/security_identity/foundation-source-basis-20260929-ikpm-listing-v2.registry.json',
        ]);
        $output = $kernel->output();
        $this->assertSame(0, $exit, $output);
        $this->assertStringContainsString('BOUNDED_CORE_ADMITTED', $output);
        $this->assertStringContainsString('"LISTING":1', $output);
        $this->assertSame(1, DB::connection($this->connections['a'])->table('si_entities')->where('entity_type', 'LISTING')->count());
    }
}
