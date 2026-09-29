<?php

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\FoundationService;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FrozenSourcePackageReader;
use Illuminate\Support\Facades\DB;

class IkpmConsumerWindowOnMariaDbTest extends TestCase
{
    private array $ownedDatabases = [];
    private array $connections = [];
    private $control;
    private string $scratch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scratch = storage_path('app/market_data/evidence/MD-B10-A002/foundation-ikpm-current-window-20260929-v1/test-work-'.bin2hex(random_bytes(6)));
        if (file_exists($this->scratch) || !mkdir($this->scratch, 0777, true)) { throw new \RuntimeException('TEST_WORK_NAMESPACE_NOT_EMPTY'); }
        $base = config('database.connections.mysql');
        config()->set('database.connections.si_ikpm_window_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('si_ikpm_window_control');
        $this->assertSame('mysql', $this->control->getDriverName());
        $this->assertSame('tradeaxis_testing', $this->control->selectOne('SELECT DATABASE() AS name')->name);
        $this->assertStringContainsString('MariaDB', $this->control->selectOne('SELECT VERSION() AS version')->version);
        try {
            $suffix = bin2hex(random_bytes(6));
            foreach (['a', 'b'] as $side) {
                $name = 'tradeaxis_testing_si_ikpm_window_'.$suffix.'_'.$side;
                $this->assertSame(0, (int) $this->control->selectOne('SELECT COUNT(*) AS n FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$name])->n);
                $this->control->statement('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
                $this->ownedDatabases[] = $name;
                $connection = 'si_ikpm_window_'.$side;
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
            if (!preg_match('/^tradeaxis_testing_si_ikpm_window_[a-f0-9]{12}_[ab]$/D', $name)) { throw new \RuntimeException('UNSAFE_TEST_DATABASE_NAME'); }
            if ($this->control->selectOne('SELECT DATABASE() AS name')->name !== 'tradeaxis_testing') { throw new \RuntimeException('TEST_CONTROL_DATABASE_CHANGED'); }
            $this->control->statement('DROP DATABASE `'.$name.'`');
        }
        $this->ownedDatabases = [];
        if (isset($this->scratch) && is_dir($this->scratch)) {
            $root = realpath(storage_path('app/market_data/evidence/MD-B10-A002/foundation-ikpm-current-window-20260929-v1'));
            $resolved = realpath($this->scratch);
            if (!$root || !$resolved || is_link($this->scratch) || dirname($resolved) !== $root || !preg_match('/^test-work-[a-f0-9]{12}$/D', basename($resolved))) { throw new \RuntimeException('UNSAFE_TEST_WORK_PATH'); }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->scratch, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $item) {
                if ($item->isDir()) { rmdir($item->getPathname()); } else { unlink($item->getPathname()); }
            }
            rmdir($this->scratch);
        }
        DB::purge('si_ikpm_window_control');
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
    private function currentPackage(): string { return storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260929-ikpm-current-window-v3'); }
    private function currentAssignments(): string { return base_path('resources/security_identity/foundation-source-basis-20260929-ikpm-current-window-v3.registry.json'); }

    private function bootstrap(string $side = 'a'): FoundationRegistry
    {
        $service = $this->service($side);
        $service->bootstrap($this->corePackage(), $this->coreAssignments());
        $service->bootstrap($this->listingPackage(), $this->listingAssignments());
        return $service->bootstrap($this->currentPackage(), $this->currentAssignments());
    }

    private function restore(array $document, string $side = 'b'): FoundationRegistry
    {
        $path = $this->scratch.'/registry-'.$side.'-'.bin2hex(random_bytes(3)).'.json';
        file_put_contents($path, (new FoundationRegistry($document))->json());
        return $this->service($side)->restore($path, hash_file('sha256', $path));
    }

    private function packageCopy(): string
    {
        $copy = $this->scratch.'/package-'.bin2hex(random_bytes(3));
        mkdir($copy);
        foreach (new \DirectoryIterator($this->currentPackage()) as $entry) {
            if ($entry->isFile()) { copy($entry->getPathname(), $copy.'/'.$entry->getFilename()); }
        }
        return $copy;
    }

    private function rejected(callable $work, string $reason): void
    {
        try { $work(); $this->fail('Expected rejection: '.$reason); }
        catch (\DomainException | \InvalidArgumentException $e) { $this->assertStringContainsString($reason, $e->getMessage()); }
    }

    public function test_resolution_is_confined_to_the_governed_overlap_instant(): void
    {
        $this->bootstrap();

        $before = $this->service()->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:14', '2026-09-29 00:38:16');
        $this->assertSame('HELD', $before->state);
        $this->assertSame('LISTING_EVIDENCE_UNAVAILABLE', $before->reason);

        $inside = $this->service()->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:15', '2026-09-29 00:38:15');
        $this->assertSame('RESOLVED', $inside->state);
        $this->assertSame('EVIDENCED_TEMPORAL_IDENTITY', $inside->reason);
        $this->assertSame('35ff26e0-a043-41c8-8c6e-f7f2d4227214', $inside->issuerId);
        $this->assertSame('93453e6d-87b4-4131-8379-a91fb1766fac', $inside->instrumentId);
        $this->assertSame('6eb68d44-81bf-4469-be4f-27ce32ef03d5', $inside->listingId);
        $this->assertSame('IKPM', $inside->context['exchange_symbol']);
        $this->assertSame('DEVELOPMENT', $inside->context['board']);
        $this->assertSame('IKPM.JK', $inside->context['provider_symbol']);

        $after = $this->service()->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:16', '2026-09-29 00:38:16');
        $this->assertSame('HELD', $after->state);
        $this->assertSame('LISTING_EVIDENCE_UNAVAILABLE', $after->reason);

        $earlierKnowledge = $this->service()->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:15', '2026-09-29 00:38:14');
        $this->assertSame('HELD', $earlierKnowledge->state);
        $this->assertSame('LIFECYCLE_OUTSIDE_VERIFIED_SCOPE', $earlierKnowledge->reason);
    }

    public function test_current_snapshot_keeps_roots_allocation_independent_and_idempotent(): void
    {
        foreach (['si_entities', 'si_revisions'] as $table) {
            DB::connection($this->connections['b'])->statement('ALTER TABLE '.$table.' AUTO_INCREMENT = 19000');
        }
        $a = $this->bootstrap('a');
        $b = $this->bootstrap('b');
        $this->assertSame($a->json(), $b->json());
        $this->assertNotSame(DB::connection($this->connections['a'])->table('si_entities')->min('row_id'), DB::connection($this->connections['b'])->table('si_entities')->min('row_id'));
        $entities = array_column($a->document()['entities'], null, 'identity_id');
        $this->assertArrayHasKey('35ff26e0-a043-41c8-8c6e-f7f2d4227214', $entities);
        $this->assertArrayHasKey('93453e6d-87b4-4131-8379-a91fb1766fac', $entities);
        $this->assertArrayHasKey('6eb68d44-81bf-4469-be4f-27ce32ef03d5', $entities);
        $this->assertSame($a->json(), $this->service('a')->bootstrap($this->currentPackage(), $this->currentAssignments())->json());
        $this->assertSame(4, DB::connection($this->connections['a'])->table('si_entities')->count());
        $this->assertSame(10, DB::connection($this->connections['a'])->table('si_revisions')->count());
        $this->assertSame(1, DB::connection($this->connections['a'])->table('si_entities')->where('entity_type', 'LISTING')->count());
    }

    public function test_export_rebuild_retains_roots_temporal_revisions_and_provenance(): void
    {
        $a = $this->bootstrap('a');
        DB::connection($this->connections['b'])->statement('ALTER TABLE si_entities AUTO_INCREMENT = 27000');
        DB::connection($this->connections['b'])->statement('ALTER TABLE si_revisions AUTO_INCREMENT = 27000');
        $b = $this->restore($a->document(), 'b');
        $this->assertSame($a->json(), $b->json());
        $packages = array_column($b->document()['packages'], null, 'package_version');
        $current = $packages[FrozenSourcePackageReader::IKPM_CURRENT_VERSION];
        $this->assertSame('E-MD-B10-A002-008', $current['admission_evidence']);
        $this->assertSame(FrozenSourcePackageReader::IKPM_CURRENT_ASSIGNMENTS_HASH, $current['assignment_sha256']);
        $this->assertSame(FrozenSourcePackageReader::IKPM_LISTING_MANIFEST_HASH, $current['predecessor_manifest_sha256']);
        $this->assertArrayHasKey('https://idx.id/primary/ListedCompany/GetCompanyProfilesDetail?KodeEmiten=IKPM&language=id-id', $current['sources']);
        $inside = $this->service('b')->resolve('YAHOO_FINANCE', 'IKPM.JK', '2026-09-29 00:38:15', '2026-09-29 00:38:15');
        $this->assertSame('RESOLVED', $inside->state);
        $this->assertSame('6eb68d44-81bf-4469-be4f-27ce32ef03d5', $inside->listingId);
    }

    public function test_package_tamper_and_unadmitted_semantic_changes_fail_closed(): void
    {
        $service = $this->service();
        $service->bootstrap($this->corePackage(), $this->coreAssignments());
        $service->bootstrap($this->listingPackage(), $this->listingAssignments());

        $copy = $this->packageCopy();
        file_put_contents($copy.'/idx_current_profile_response.json', "{}\n");
        $this->rejected(fn () => $service->bootstrap($copy, $this->currentAssignments()), 'SOURCE_MEMBER_FINGERPRINT_MISMATCH:idx_current_profile_response.json');
        $this->assertSame(7, DB::connection($this->connections['a'])->table('si_revisions')->count());

        $copy = $this->packageCopy();
        unlink($copy.'/idx_current_profile_extract.json');
        $this->rejected(fn () => $service->bootstrap($copy, $this->currentAssignments()), 'SOURCE_MEMBER_MISSING:idx_current_profile_extract.json');
        $this->assertSame(7, DB::connection($this->connections['a'])->table('si_revisions')->count());

        $document = $this->bootstrap('a')->document();
        foreach ($document['revisions'] as &$revision) {
            if ($revision['revision_id'] === 'd85d86ab-cc5e-4ded-a993-7fa19ec4e7e2') { $revision['data']['board'] = 'MAIN'; }
        }
        unset($revision);
        $this->rejected(fn () => $this->restore($document, 'b'), 'FACT_NOT_ADMITTED:board');
        $this->assertSame(0, DB::connection($this->connections['b'])->table('si_entities')->count());
    }

    public function test_successor_adds_only_current_revisions_and_preserves_unsupported_scope_as_held(): void
    {
        $registry = $this->bootstrap();
        $document = $registry->document();
        $current = array_values(array_filter($document['revisions'], static fn (array $r): bool => $r['package_hash'] === FrozenSourcePackageReader::IKPM_CURRENT_MANIFEST_HASH));
        $this->assertCount(3, $current);
        $this->assertSame(['LISTING', 'SYMBOL', 'BOARD'], array_values(array_unique(array_column($current, 'revision_type'))));
        $this->assertSame(['6eb68d44-81bf-4469-be4f-27ce32ef03d5'], array_values(array_unique(array_column($current, 'identity_id'))));
        $this->assertCount(1, array_filter($document['revisions'], static fn (array $r): bool => $r['revision_type'] === 'PROVIDER_MAPPING'));
        $holds = array_column($document['holds'], null, 'scope');
        $this->assertArrayHasKey('IKPM:lifecycle-gap', $holds);
        $this->assertArrayHasKey('IKPM:lifecycle-after-current-snapshot', $holds);
        $this->assertArrayHasKey('IKPM:market-data-integration', $holds);
        $this->assertArrayHasKey('IKPM:provider-history', $holds);
    }
}
