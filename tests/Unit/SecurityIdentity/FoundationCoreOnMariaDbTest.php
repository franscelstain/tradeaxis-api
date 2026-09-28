<?php

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\FoundationService;
use App\Domain\SecurityIdentity\RegistryAdmission;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FrozenSourcePackageReader;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityIdentityFixture;

class FoundationCoreOnMariaDbTest extends TestCase
{
    private array $ownedDatabases = [];
    private array $connections = [];
    private $control;
    private string $scratch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scratch = storage_path('app/market_data/evidence/MD-B10-A002/foundation-core-20260929-v1/test-work-'.bin2hex(random_bytes(6)));
        if (file_exists($this->scratch) || !mkdir($this->scratch, 0777, true)) { throw new \RuntimeException('TEST_WORK_NAMESPACE_NOT_EMPTY'); }
        $base = config('database.connections.mysql');
        config()->set('database.connections.si_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('si_control');
        $this->assertSame('mysql', $this->control->getDriverName());
        $this->assertSame('tradeaxis_testing', $this->control->selectOne('SELECT DATABASE() AS name')->name);
        $this->assertStringContainsString('MariaDB', $this->control->selectOne('SELECT VERSION() AS version')->version);
        try {
            $suffix = bin2hex(random_bytes(6));
            foreach (['a', 'b'] as $side) {
                $name = 'tradeaxis_testing_si_core_'.$suffix.'_'.$side;
                $this->assertSame(0, (int)$this->control->selectOne('SELECT COUNT(*) AS n FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$name])->n);
                $this->control->statement('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
                $this->ownedDatabases[] = $name;
                $connection = 'si_core_'.$side;
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
            if (!preg_match('/^tradeaxis_testing_si_core_[a-f0-9]{12}_[ab]$/D', $name)) { throw new \RuntimeException('UNSAFE_TEST_DATABASE_NAME'); }
            if ($this->control->selectOne('SELECT DATABASE() AS name')->name !== 'tradeaxis_testing') { throw new \RuntimeException('TEST_CONTROL_DATABASE_CHANGED'); }
            $this->control->statement('DROP DATABASE `'.$name.'`');
        }
        $this->ownedDatabases = [];
        if (isset($this->scratch) && is_dir($this->scratch)) {
            $root = realpath(storage_path('app/market_data/evidence/MD-B10-A002/foundation-core-20260929-v1'));
            $resolved = realpath($this->scratch);
            if (!$root || !$resolved || is_link($this->scratch) || dirname($resolved) !== $root || !preg_match('/^test-work-[a-f0-9]{12}$/D', basename($resolved))) { throw new \RuntimeException('UNSAFE_TEST_WORK_PATH'); }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->scratch, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $item) {
                if ($item->isDir()) { rmdir($item->getPathname()); } else { unlink($item->getPathname()); }
            }
            rmdir($this->scratch);
        }
        DB::purge('si_control');
    }

    private function migration()
    {
        require_once base_path('database/migrations/2026_09_29_000001_create_shared_security_identity_foundation.php');
        return new CreateSharedSecurityIdentityFoundation();
    }

    private function service(string $side = 'a'): FoundationService { return new FoundationService(new FoundationRepository(DB::connection($this->connections[$side]))); }
    private function package(): string { return storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260928-v1'); }
    private function assignments(): string { return base_path('resources/security_identity/foundation-source-basis-20260928-v1.registry.json'); }

    private function restore(array $document, string $side = 'a'): FoundationRegistry
    {
        $path = $this->scratch.'/registry.json';
        file_put_contents($path, (new FoundationRegistry($document))->json());
        return $this->service($side)->restore($path, hash_file('sha256', $path));
    }

    private function rejected(callable $work, string $reason): void
    {
        try { $work(); $this->fail('Expected rejection: '.$reason); }
        catch (\DomainException | \InvalidArgumentException $e) { $this->assertStringContainsString($reason, $e->getMessage()); }
    }

    public function test_frozen_package_roots_ignore_different_local_allocations_and_repeat_import_is_identical(): void
    {
        foreach (['si_entities', 'si_revisions'] as $table) {
            DB::connection($this->connections['b'])->statement('ALTER TABLE '.$table.' AUTO_INCREMENT = 9000');
        }
        $a = $this->service('a')->bootstrap($this->package(), $this->assignments());
        $b = $this->service('b')->bootstrap($this->package(), $this->assignments());
        $this->assertSame($a->json(), $b->json());
        $this->assertNotSame(DB::connection($this->connections['a'])->table('si_entities')->min('row_id'), DB::connection($this->connections['b'])->table('si_entities')->min('row_id'));
        $this->assertSame(['ISSUER' => 2, 'INSTRUMENT' => 1], array_count_values(array_column($a->document()['entities'], 'entity_type')));
        $this->assertCount(3, $a->document()['revisions']);
        $this->assertCount(3, $a->document()['holds']);
        $this->assertSame($a->json(), $this->service()->bootstrap($this->package(), $this->assignments())->json());
        $this->assertSame(3, DB::connection($this->connections['a'])->table('si_entities')->count());
        $result = $this->service()->resolve('YAHOO', 'IKPM.JK', '2024-01-01 00:00:00', '2026-09-29 00:00:00');
        $this->assertSame('HELD', $result->state);
        $this->assertNull($result->listingId);
        $this->assertNull($result->issuerId);
    }

    public function test_registry_export_and_rebuild_retains_all_ids_and_source_provenance(): void
    {
        $a = $this->service()->bootstrap($this->package(), $this->assignments());
        $b = $this->restore($a->document(), 'b');
        $this->assertSame($a->json(), $b->json());
        $p = $b->document()['packages'][0];
        $this->assertSame('E-MD-B10-A002-005', $p['admission_evidence']);
        $this->assertSame(FrozenSourcePackageReader::ASSIGNMENTS_HASH, $p['assignment_sha256']);
        foreach ($p['sources'] as $locator => $source) {
            $this->assertStringStartsWith('https://', $locator);
            $this->assertNotEmpty($source['capture_metadata']['limitations']);
            $this->assertNull($source['source_known_at']);
            $this->assertSame('BOUNDED_FACT', $source['evidence_class']);
        }
        foreach ($b->document()['revisions'] as $rev) {
            $this->assertArrayNotHasKey('listing_date_reported', $rev['data']);
            $this->assertArrayNotHasKey('announced_listing_date', $rev['data']);
        }
    }

    public function test_synthetic_listing_roots_and_resolution_survive_allocation_and_rebuild(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        DB::connection($this->connections['b'])->statement('ALTER TABLE si_entities AUTO_INCREMENT = 700');
        $a = $this->restore($d);
        $b = $this->restore($d, 'b');
        $this->assertSame($a->json(), $b->json());
        foreach (['a', 'b'] as $side) {
            $v = $this->service($side)->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2021-01-01 00:00:00');
            $this->assertSame('RESOLVED', $v->state);
            $this->assertSame(SecurityIdentityFixture::ISSUER, $v->issuerId);
            $this->assertSame(SecurityIdentityFixture::INSTRUMENT, $v->instrumentId);
            $this->assertSame(SecurityIdentityFixture::LISTING, $v->listingId);
            $this->assertSame('TEST_BOARD', $v->context['board']);
        }
        $this->assertSame(1, DB::connection($this->connections['a'])->table('si_entities')->where('entity_type', 'LISTING')->count());
    }

    public function test_listing_validity_knowledge_and_verified_scope_fail_closed(): void
    {
        $this->restore(SecurityIdentityFixture::registry()->document());
        foreach ([['2019-12-31 00:00:00', '2021-01-01 00:00:00'], ['2021-01-01 00:00:00', '2019-12-31 00:00:00'], ['2031-01-01 00:00:00', '2031-01-01 00:00:00']] as [$effective, $known]) {
            $v = $this->service()->resolve('TEST_PROVIDER', 'OLD', $effective, $known);
            $this->assertSame('HELD', $v->state);
            $this->assertNull($v->listingId);
        }
        $this->assertSame('HELD', $this->service()->resolve('TEST_PROVIDER', 'UNKNOWN', '2021-01-01 00:00:00', '2021-01-01 00:00:00')->state);
    }

    public function test_missing_provenance_and_unadmitted_field_are_rejected_before_any_write(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $bad = $d;
        $locator = array_key_first($bad['packages'][0]['sources']);
        unset($bad['packages'][0]['sources'][$locator]['review_reference']);
        $this->rejected(fn () => $this->restore($bad), 'PROVENANCE_OR_REGISTRY_FIELD_REQUIRED:review_reference');
        $bad = $d;
        $bad['revisions'][0]['data']['unsupported_fact'] = 'guessed';
        $this->rejected(fn () => $this->restore($bad), 'FACT_NOT_ADMITTED');
        $bad = $d;
        $bad['packages'][0]['sources'][$locator]['evidence_class'] = 'CORROBORATING';
        $this->rejected(fn () => $this->restore($bad), 'CORROBORATING_FACT_NOT_AUTHORITATIVE');
        $bad = $d;
        $bad['packages'][0]['sources'][$locator]['facts'] = 'not-a-record';
        $this->rejected(fn () => $this->restore($bad), 'SOURCE_FACT_SHAPE_INVALID');
        $this->assertSame(0, DB::connection($this->connections['a'])->table('si_packages')->count());
    }

    public function test_unknown_historical_knowledge_is_not_backfilled_from_capture_time(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        foreach ($d['packages'][0]['sources'] as &$source) { $source['source_known_at'] = null; }
        unset($source);
        foreach ($d['revisions'] as &$rev) { $rev['known_at'] = null; }
        unset($rev);
        $this->restore($d);
        $this->assertSame('HELD', $this->service()->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2021-01-01 00:00:00')->state);
        $bad = $d;
        $bad['revisions'][0]['known_at'] = $bad['revisions'][0]['recorded_at'];
        $this->rejected(fn () => (new RegistryAdmission())->validate(new FoundationRegistry($bad)), 'HISTORICAL_KNOWLEDGE_NOT_EVIDENCED');
    }

    public function test_missing_listing_admission_and_unsupported_continuity_never_create_a_listing(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $d['revisions'] = array_values(array_filter($d['revisions'], static fn (array $r): bool => $r['revision_type'] !== 'LISTING'));
        $this->rejected(fn () => $this->restore($d), 'LISTING_ADMISSION_MISSING');
        $d = SecurityIdentityFixture::registry()->document();
        foreach ($d['packages'][0]['sources'] as &$s) { $s['facts']['continuity'] = 'UNRESOLVED'; }
        unset($s);
        foreach ($d['revisions'] as &$r) { if ($r['revision_type'] === 'LISTING') { $r['data']['continuity'] = 'UNRESOLVED'; $r['state'] = 'HELD'; } }
        unset($r);
        $this->rejected(fn () => $this->restore($d), 'LISTING_ADMISSION_MISSING');
        $this->assertSame(0, DB::connection($this->connections['a'])->table('si_entities')->count());
        foreach ($d['revisions'] as &$r) { if ($r['revision_type'] === 'LISTING') { $r['state'] = 'ADMITTED'; } }
        unset($r);
        $this->rejected(fn () => $this->restore($d), 'CONTINUITY_UNRESOLVED');
    }

    public function test_schema_foreign_keys_and_migration_roundtrip_are_real_mariadb(): void
    {
        $db = DB::connection($this->connections['a']);
        try { $db->table('si_entities')->insert(['identity_id' => SecurityIdentityFixture::INSTRUMENT, 'entity_type' => 'INSTRUMENT', 'parent_identity_id' => SecurityIdentityFixture::ISSUER, 'package_hash' => str_repeat('a', 64), 'document_json' => '{}']); $this->fail('Foreign key must reject orphan'); }
        catch (\Illuminate\Database\QueryException $e) { $this->assertStringContainsString('1452', $e->getMessage()); }
        config()->set('database.default', $this->connections['a']);
        $this->migration()->down();
        $this->assertSame(0, (int)$db->selectOne("SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'si\\_%'")->n);
        $this->migration()->up();
        $this->assertCount(3, $this->service()->bootstrap($this->package(), $this->assignments())->document()['entities']);
        try { $this->migration()->down(); $this->fail('Populated foundation history must not be dropped'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString('FOUNDATION_HISTORY_PRESENT', $e->getMessage()); }
        $this->assertSame(3, $db->table('si_entities')->count());
    }

    public function test_package_members_versions_and_retained_registry_fingerprints_fail_closed(): void
    {
        $copy = $this->scratch.'/package';
        mkdir($copy);
        foreach (glob($this->package().'/*.json') as $file) { copy($file, $copy.'/'.basename($file)); }
        $changed = $copy.'/admitted_facts.json';
        $original = file_get_contents($changed);
        file_put_contents($changed, $original.' ');
        $this->rejected(fn () => $this->service()->bootstrap($copy, $this->assignments()), 'SOURCE_MEMBER_FINGERPRINT_MISMATCH');
        file_put_contents($changed, $original);
        unlink($copy.'/unresolved.json');
        $this->rejected(fn () => $this->service()->bootstrap($copy, $this->assignments()), 'SOURCE_MEMBER_MISSING');
        copy($this->package().'/unresolved.json', $copy.'/unresolved.json');
        $manifest = json_decode(file_get_contents($copy.'/manifest.json'), true);
        $manifest['package_version'] = 'unreviewed-v2';
        file_put_contents($copy.'/manifest.json', json_encode($manifest));
        $this->rejected(fn () => $this->service()->bootstrap($copy, $this->assignments()), 'SOURCE_PACKAGE_VERSION_UNSUPPORTED');
        $registry = $this->scratch.'/registry.json';
        file_put_contents($registry, SecurityIdentityFixture::registry()->json());
        $fingerprint = hash_file('sha256', $registry);
        file_put_contents($registry, file_get_contents($registry).' ');
        $this->rejected(fn () => $this->service()->restore($registry, $fingerprint), 'REGISTRY_FINGERPRINT_MISMATCH');
        $this->rejected(fn () => $this->service()->restore($registry, ''), 'REGISTRY_APPROVED_FINGERPRINT_REQUIRED');
        $assignmentCopy = $this->scratch.'/assignments.json';
        file_put_contents($assignmentCopy, file_get_contents($this->assignments()).' ');
        $this->rejected(fn () => $this->service()->bootstrap($this->package(), $assignmentCopy), 'REGISTRY_FINGERPRINT_MISMATCH');
        $this->assertSame(0, DB::connection($this->connections['a'])->table('si_entities')->count());
    }

    public function test_evidenced_rename_keeps_roots_and_preserves_as_known_old_symbol(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $this->restore($d);
        $newHash = hash('sha256', 'SYNTHETIC_EXPLICIT_RENAME_WITH_PROVEN_CONTINUITY_V2');
        $package = $d['packages'][0];
        $package['package_hash'] = $newHash;
        $package['package_version'] = 'test-only-explicit-rename/v2';
        $package['recorded_at'] = '2022-01-01 00:00:00';
        $package['sources'] = [];
        foreach (['SYMBOL', 'PROVIDER_MAPPING'] as $kind) {
            $old = array_values(array_filter($d['revisions'], static fn (array $r): bool => $r['revision_type'] === $kind))[0];
            foreach (['closed', 'renamed'] as $mode) {
                $locator = 'test-only://proven-rename/'.$kind.'/'.$mode;
                $from = $mode === 'closed' ? '2020-01-01 00:00:00' : '2022-01-01 00:00:00';
                $to = $mode === 'closed' ? '2022-01-01 00:00:00' : null;
                $symbol = $mode === 'closed' ? 'OLD' : 'NEW';
                $source = ['facts' => ['namespace' => 'TEST_PROVIDER', 'symbol' => $symbol, 'valid_from' => $from, 'valid_to' => $to], 'admitted_fields' => ['namespace', 'symbol', 'valid_from', 'valid_to'], 'evidence_class' => 'PRIMARY_MASTER_EVIDENCE', 'source_revision' => 'explicit-rename-v2', 'source_known_at' => '2022-01-01 00:00:00', 'captured_at' => '2022-01-01 00:00:00', 'review_reference' => 'TEST_ONLY_EXPLICIT_CONTINUITY'];
                $package['sources'][$locator] = $source;
                $rev = $old;
                $ordinal = $kind === 'SYMBOL' ? ($mode === 'closed' ? '1' : '2') : ($mode === 'closed' ? '3' : '4');
                $rev['revision_id'] = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb'.$ordinal;
                $rev['package_hash'] = $newHash;
                $rev['source_locator'] = $locator;
                $rev['data']['symbol'] = $symbol;
                $rev['valid_from'] = $from;
                $rev['valid_to'] = $to;
                $rev['known_at'] = $source['source_known_at'];
                $rev['recorded_at'] = $source['captured_at'];
                $rev['supersedes_revision_id'] = $mode === 'closed' ? $old['revision_id'] : null;
                $d['revisions'][] = $rev;
            }
        }
        $d['packages'][] = $package;
        $this->restore($d);
        $old = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2021-06-01 00:00:00');
        $historical = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2023-01-01 00:00:00');
        $new = $this->service()->resolve('TEST_PROVIDER', 'NEW', '2023-01-01 00:00:00', '2023-01-01 00:00:00');
        foreach ([$old, $historical, $new] as $resolved) {
            $this->assertSame('RESOLVED', $resolved->state);
            $this->assertSame(SecurityIdentityFixture::LISTING, $resolved->listingId);
            $this->assertSame(SecurityIdentityFixture::INSTRUMENT, $resolved->instrumentId);
        }
        $this->assertSame('OLD', $historical->context['exchange_symbol']);
        $this->assertSame('NEW', $new->context['exchange_symbol']);
        $this->assertSame('HELD', $this->service()->resolve('TEST_PROVIDER', 'OLD', '2023-01-01 00:00:00', '2023-01-01 00:00:00')->state);
        $this->assertCount(3, $this->service()->export()->document()['entities']);
    }

    public function test_overlapping_mapping_is_ambiguous_and_duplicate_registry_keys_are_rejected(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $extra = array_values(array_filter($d['revisions'], static fn (array $r): bool => $r['revision_type'] === 'PROVIDER_MAPPING'))[0];
        $extra['revision_id'] = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
        $d['revisions'][] = $extra;
        $this->restore($d);
        $v = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2021-01-01 00:00:00');
        $this->assertSame('AMBIGUOUS', $v->state);
        $this->assertNull($v->listingId);
        $d['entities'][] = $d['entities'][0];
        $this->rejected(fn () => $this->restore($d), 'DUPLICATE_OR_MISSING_REGISTRY_KEY');
        $this->assertCount(3, $this->service()->export()->document()['entities']);
    }

    public function test_unknown_registry_version_and_existing_identity_rewrite_are_rejected(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $this->restore($d);
        $bad = $d;
        $bad['registry_version'] = 'security-identity-registry/unreviewed';
        $this->rejected(fn () => $this->restore($bad), 'REGISTRY_VERSION_UNSUPPORTED');
        $bad = $d;
        $bad['entities'][0]['source_locator'] = 'test-only://changed-source';
        $this->rejected(fn () => $this->restore($bad), 'SOURCE_PROVENANCE_REQUIRED');
        $bad = $d;
        $bad['entities'][0]['assignment_note'] = 'silently-reinterpreted';
        $this->rejected(fn () => $this->restore($bad), 'IMMUTABLE_REGISTRY_CONFLICT');
        $this->assertSame((new FoundationRegistry($d))->json(), $this->service()->export()->json());
    }

    public function test_registered_cli_boundary_imports_the_real_frozen_package(): void
    {
        config()->set('database.default', $this->connections['a']);
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $this->assertSame(0, $kernel->call('security-identity:bootstrap', ['package' => $this->package()]));
        $output = json_decode(trim($kernel->output()), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('BOUNDED_CORE_ADMITTED', $output['state']);
        $this->assertSame(2, $output['entity_counts']['ISSUER']);
        $this->assertSame(1, $output['entity_counts']['INSTRUMENT']);
        $this->assertArrayNotHasKey('LISTING', $output['entity_counts']);
        $this->assertFalse($output['dependency_resolved']);
    }

    public function test_evidenced_display_profile_revision_does_not_replace_issuer_identity(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $before = $this->restore($d);
        $package = $d['packages'][0];
        $package['package_hash'] = hash('sha256', 'TEST_ONLY_EXPLICIT_DISPLAY_NAME_REVISION');
        $package['package_version'] = 'test-only-display-revision/v2';
        $package['recorded_at'] = '2022-01-01 00:00:00';
        $locator = 'test-only://evidenced-display-name-revision';
        $package['sources'] = [$locator => ['facts' => ['name' => 'Synthetic renamed issuer'], 'admitted_fields' => ['name'], 'evidence_class' => 'PRIMARY_MASTER_EVIDENCE', 'source_revision' => 'display-v2', 'source_known_at' => '2022-01-01 00:00:00', 'captured_at' => '2022-01-01 00:00:00', 'review_reference' => 'TEST_ONLY_EXPLICIT_SAME_ISSUER']];
        $old = array_values(array_filter($d['revisions'], static fn (array $r): bool => $r['revision_type'] === 'PROFILE'))[0];
        $new = $old;
        $new['revision_id'] = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';
        $new['package_hash'] = $package['package_hash'];
        $new['source_locator'] = $locator;
        $new['known_at'] = $new['recorded_at'] = $package['recorded_at'];
        $new['supersedes_revision_id'] = $old['revision_id'];
        $new['data'] = ['name' => 'Synthetic renamed issuer'];
        $d['packages'][] = $package;
        $d['revisions'][] = $new;
        $after = $this->restore($d);
        $this->assertSame($before->document()['entities'], $after->document()['entities']);
        $profiles = array_values(array_filter($after->document()['revisions'], static fn (array $r): bool => $r['revision_type'] === 'PROFILE'));
        $this->assertCount(2, $profiles);
        foreach ($profiles as $profile) { $this->assertSame(SecurityIdentityFixture::ISSUER, $profile['identity_id']); }
    }

    public function test_symbol_reuse_resolves_distinct_instruments_without_merging_roots(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $newInstrument = '44444444-4444-4444-8444-444444444444';
        $newListing = '55555555-5555-4555-8555-555555555555';
        $pkg = $d['packages'][0];
        $locator = array_key_first($pkg['sources']);
        $d['entities'][] = ['identity_id' => $newInstrument, 'entity_type' => 'INSTRUMENT', 'parent_identity_id' => SecurityIdentityFixture::ISSUER, 'package_hash' => $pkg['package_hash'], 'source_locator' => $locator];
        $d['entities'][] = ['identity_id' => $newListing, 'entity_type' => 'LISTING', 'parent_identity_id' => $newInstrument, 'package_hash' => $pkg['package_hash'], 'source_locator' => $locator];
        $newLocator = 'test-only://declared-distinct-security-symbol-reuse';
        $source = $pkg['sources'][$locator];
        $source['facts']['valid_from'] = '2022-01-01 00:00:00';
        $d['packages'][0]['sources'][$newLocator] = $source;
        $closedLocator = 'test-only://declared-old-provider-mapping-end';
        $closed = $pkg['sources'][$locator];
        $closed['facts']['valid_to'] = '2022-01-01 00:00:00';
        $d['packages'][0]['sources'][$closedLocator] = $closed;
        $extra = [];
        foreach ($d['revisions'] as &$rev) {
            if ($rev['revision_type'] === 'PROFILE') { continue; }
            $copy = $rev;
            $copy['revision_id'] = str_replace('aaaaaaaa-aaaa-4aaa-8aaa-', 'eeeeeeee-eeee-4eee-8eee-', $copy['revision_id']);
            $copy['identity_id'] = $newListing;
            $copy['source_locator'] = $newLocator;
            $copy['valid_from'] = '2022-01-01 00:00:00';
            $extra[] = $copy;
            if ($rev['revision_type'] === 'PROVIDER_MAPPING') { $rev['source_locator'] = $closedLocator; $rev['valid_to'] = '2022-01-01 00:00:00'; }
        }
        unset($rev);
        $d['revisions'] = array_merge($d['revisions'], $extra);
        $this->restore($d);
        $old = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2023-01-01 00:00:00');
        $new = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2022-01-01 00:00:00', '2023-01-01 00:00:00');
        $this->assertSame('RESOLVED', $old->state);
        $this->assertSame('RESOLVED', $new->state);
        $this->assertSame(SecurityIdentityFixture::INSTRUMENT, $old->instrumentId);
        $this->assertSame($newInstrument, $new->instrumentId);
        $this->assertNotSame($old->listingId, $new->listingId);
        $this->assertCount(5, $this->service()->export()->document()['entities']);
    }

    public function test_evidenced_delisting_preserves_past_membership_and_holds_terminated_listing(): void
    {
        $d = SecurityIdentityFixture::registry()->document();
        $this->restore($d);
        $old = array_values(array_filter($d['revisions'], static fn (array $r): bool => $r['revision_type'] === 'LISTING'))[0];
        $pkg = $d['packages'][0];
        $pkg['package_hash'] = hash('sha256', 'TEST_ONLY_EXPLICIT_DELISTING_REVISION_V2');
        $pkg['package_version'] = 'test-only-delisting/v2';
        $pkg['recorded_at'] = '2022-01-01 00:00:00';
        $pkg['sources'] = [];
        foreach (['closed', 'terminated'] as $mode) {
            $locator = 'test-only://executed-termination/'.$mode;
            $rev = $old;
            $rev['revision_id'] = $mode === 'closed' ? 'ffffffff-ffff-4fff-8fff-fffffffffff1' : 'ffffffff-ffff-4fff-8fff-fffffffffff2';
            $rev['valid_from'] = $mode === 'closed' ? '2020-01-01 00:00:00' : '2022-01-01 00:00:00';
            $rev['valid_to'] = $mode === 'closed' ? '2022-01-01 00:00:00' : null;
            $rev['known_at'] = $rev['recorded_at'] = $pkg['recorded_at'];
            $rev['data']['listing_state'] = $mode === 'closed' ? 'LISTED' : 'DELISTED';
            $rev['data']['change_reason'] = 'SYNTHETIC_EXECUTED_TERMINATION';
            $rev['package_hash'] = $pkg['package_hash'];
            $rev['source_locator'] = $locator;
            $rev['supersedes_revision_id'] = $mode === 'closed' ? $old['revision_id'] : null;
            $facts = array_merge($rev['data'], ['valid_from' => $rev['valid_from'], 'valid_to' => $rev['valid_to']]);
            $pkg['sources'][$locator] = ['facts' => $facts, 'admitted_fields' => array_keys($facts), 'evidence_class' => 'PRIMARY_MASTER_EVIDENCE', 'source_revision' => 'termination-v2', 'source_known_at' => $pkg['recorded_at'], 'captured_at' => $pkg['recorded_at'], 'review_reference' => 'TEST_ONLY_EXECUTED_TERMINATION'];
            $d['revisions'][] = $rev;
        }
        $d['packages'][] = $pkg;
        $this->restore($d);
        $past = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2021-01-01 00:00:00', '2023-01-01 00:00:00');
        $terminated = $this->service()->resolve('TEST_PROVIDER', 'OLD', '2022-01-01 00:00:00', '2023-01-01 00:00:00');
        $this->assertSame('RESOLVED', $past->state);
        $this->assertSame(SecurityIdentityFixture::LISTING, $past->listingId);
        $this->assertSame('HELD', $terminated->state);
        $this->assertSame('LISTING_NOT_VALID', $terminated->reason);
        $this->assertNull($terminated->listingId);
    }
}
