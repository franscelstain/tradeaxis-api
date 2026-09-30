<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\PublicationSemanticIdentityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicationCorrectionSealSemanticIdentityOnMariaDbTest extends TestCase
{
    private array $owned = [];
    private array $connections = [];
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        $base = config('database.connections.mysql');
        config()->set('database.connections.pub_semantic_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('pub_semantic_control');
        $suffix = bin2hex(random_bytes(5));
        foreach (['a', 'b'] as $side) {
            $db = 'tradeaxis_testing_pub_semantic_'.$suffix.'_'.$side;
            $this->control->statement('CREATE DATABASE `'.$db.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
            $this->owned[] = $db;
            $name = 'pub_semantic_'.$side;
            config()->set('database.connections.'.$name, array_merge($base, ['database' => $db, 'collation' => 'utf8mb4_bin']));
            DB::purge($name); $this->connections[$side] = $name;
            $schema = Schema::connection($name);
            $schema->create('semantic_material', function (Blueprint $table): void {
                $table->bigIncrements('local_row_id');
                $table->unsignedBigInteger('local_publication_id');
                $table->unsignedBigInteger('local_run_id');
                $table->unsignedBigInteger('local_correction_id');
                $table->string('member_name');
                $table->char('member_hash', 64);
            });
            $dummy = $side === 'a' ? 2 : 17;
            for ($i = 0; $i < $dummy; $i++) DB::connection($name)->table('semantic_material')->insert([
                'local_publication_id' => $i + 1, 'local_run_id' => $i + 10, 'local_correction_id' => $i + 20,
                'member_name' => 'dummy-'.$i, 'member_hash' => hash('sha256', $side.'-'.$i),
            ]);
            DB::connection($name)->table('semantic_material')->insert([
                'local_publication_id' => $side === 'a' ? 31 : 9901,
                'local_run_id' => $side === 'a' ? 41 : 8801,
                'local_correction_id' => $side === 'a' ? 51 : 7701,
                'member_name' => 'bars', 'member_hash' => hash('sha256', 'same-v2-bars'),
            ]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->connections as $name) DB::purge($name);
        foreach ($this->owned as $db) {
            if (!preg_match('/^tradeaxis_testing_pub_semantic_[a-f0-9]{10}_[ab]$/D', $db)) throw new RuntimeException('UNSAFE_TEST_DATABASE_NAME');
            $this->control->statement('DROP DATABASE `'.$db.'`');
        }
        DB::purge('pub_semantic_control');
        parent::tearDown();
    }

    private function identities(string $side): array
    {
        $row = DB::connection($this->connections[$side])->table('semantic_material')->where('member_name', 'bars')->first();
        $service = new PublicationSemanticIdentityService();
        $publication = $service->publicationHash([
            'trade_date' => '2026-09-29', 'publication_version' => '2',
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
            'config_content_hash' => hash('sha256', 'same-config'),
            'artifacts' => ['bars' => $row->member_hash, 'indicators' => hash('sha256', 'same-indicators'), 'eligibility' => hash('sha256', 'same-eligibility')],
            'lineage' => ['supersedes_manifest_hash' => hash('sha256', 'same-prior')],
        ]);
        $correction = $service->correctionHash([
            'trade_date' => '2026-09-29', 'reason_code' => 'SOURCE_CORRECTION',
            'baseline_publication_manifest_hash' => hash('sha256', 'same-prior'),
            'replacement_artifact_hashes' => ['bars' => $row->member_hash, 'indicators' => hash('sha256', 'same-indicators'), 'eligibility' => hash('sha256', 'same-eligibility')],
            'config_content_hash' => hash('sha256', 'same-config'),
        ]);
        $seal = $service->sealFingerprint([
            'publication_manifest_hash' => $publication, 'correction_semantic_hash' => $correction,
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
            'publication_semantic_profile' => PublicationSemanticIdentityService::PROFILE_V2,
            'seal_contract_version' => PublicationSemanticIdentityService::SEAL_CONTRACT_V2,
            'provenance_scope' => 'FULL',
        ]);
        return compact('publication', 'correction', 'seal');
    }

    public function test_all_identities_are_equal_across_different_database_allocations(): void
    {
        $this->assertSame($this->identities('a'), $this->identities('b'));
        $this->assertSame($this->identities('a'), $this->identities('a'));
        $a = DB::connection($this->connections['a'])->table('semantic_material')->where('member_name', 'bars')->first();
        $b = DB::connection($this->connections['b'])->table('semantic_material')->where('member_name', 'bars')->first();
        $this->assertNotSame((int) $a->local_row_id, (int) $b->local_row_id);
        $this->assertNotSame((int) $a->local_publication_id, (int) $b->local_publication_id);
        $this->assertNotSame((int) $a->local_run_id, (int) $b->local_run_id);
        $this->assertNotSame((int) $a->local_correction_id, (int) $b->local_correction_id);
    }

    public function test_changed_v2_artifact_changes_publication_correction_and_seal(): void
    {
        $before = $this->identities('a');
        DB::connection($this->connections['a'])->table('semantic_material')->where('member_name', 'bars')
            ->update(['member_hash' => hash('sha256', 'changed-v2-bars')]);
        $after = $this->identities('a');
        $this->assertNotSame($before['publication'], $after['publication']);
        $this->assertNotSame($before['correction'], $after['correction']);
        $this->assertNotSame($before['seal'], $after['seal']);
    }
}
