<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\SecurityIdentity\FoundationService;
use App\Infrastructure\Persistence\MarketData\TemporalIdentityRepository;
use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ArtifactSemanticIdentityOnMariaDbTest extends TestCase
{
    private array $ownedDatabases = [];
    private array $connections = [];
    private array $contexts = [];
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        $base = config('database.connections.mysql');
        config()->set('database.connections.artifact_semantic_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $this->control = DB::connection('artifact_semantic_control');
        $suffix = bin2hex(random_bytes(6));

        try {
            foreach (['a', 'b'] as $side) {
                $name = 'tradeaxis_testing_artifact_semantic_'.$suffix.'_'.$side;
                $this->control->statement('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
                $this->ownedDatabases[] = $name;
                $connection = 'artifact_semantic_'.$side;
                config()->set('database.connections.'.$connection, array_merge($base, [
                    'database' => $name,
                    'collation' => 'utf8mb4_bin',
                ]));
                DB::purge($connection);
                $this->connections[$side] = $connection;
                config()->set('database.default', $connection);
                $this->foundationMigration()->up();
                $this->createArtifactSchema($connection);
            }
            $this->seed('a', 1, 10, 1, 11, 21);
            $this->seed('b', 41, 510, 31, 111, 221);
        } catch (Throwable $e) {
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
        foreach ($this->connections as $connection) {
            DB::purge($connection);
        }
        foreach ($this->ownedDatabases as $name) {
            if (!preg_match('/^tradeaxis_testing_artifact_semantic_[a-f0-9]{12}_[ab]$/D', $name)) {
                throw new RuntimeException('UNSAFE_TEST_DATABASE_NAME');
            }
            $this->control->statement('DROP DATABASE `'.$name.'`');
        }
        $this->ownedDatabases = [];
        DB::purge('artifact_semantic_control');
    }

    private function foundationMigration()
    {
        require_once base_path('database/migrations/2026_09_29_000001_create_shared_security_identity_foundation.php');
        return new CreateSharedSecurityIdentityFoundation();
    }

    private function createArtifactSchema(string $connection): void
    {
        $schema = Schema::connection($connection);
        $schema->create('md_config_snapshots', function (Blueprint $table): void {
            $table->bigIncrements('config_snapshot_id');
            $table->char('config_hash', 64);
        });
        $schema->create('md_publication_lineage_bindings', function (Blueprint $table): void {
            $table->bigIncrements('publication_lineage_id');
            $table->unsignedBigInteger('publication_id');
            $table->char('observation_manifest_hash', 64);
            $table->char('identity_revision_set_hash', 64);
            $table->char('status_revision_set_hash', 64);
            $table->char('event_revision_set_hash', 64);
            $table->char('source_scale_assessment_set_hash', 64);
            $table->char('market_structure_revision_set_hash', 64);
            $table->char('factor_decision_set_hash', 64);
            $table->string('read_model_version', 64);
        });
        $schema->create('md_source_observation_rows', function (Blueprint $table): void {
            $table->bigIncrements('source_observation_row_id');
            $table->unsignedBigInteger('source_observation_id');
            $table->unsignedBigInteger('listing_id');
            $table->string('provider', 64);
            $table->string('provider_symbol', 128);
            $table->date('trade_date');
        });
        $schema->create('eod_bars', function (Blueprint $table): void {
            $table->date('trade_date');
            $table->unsignedBigInteger('ticker_id');
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('source_observation_id');
            $table->decimal('open', 18, 4);
            $table->decimal('high', 18, 4);
            $table->decimal('low', 18, 4);
            $table->decimal('close', 18, 4);
            $table->unsignedBigInteger('volume');
            $table->decimal('adj_close', 18, 4)->nullable();
            $table->decimal('previous_close', 18, 4)->nullable();
            $table->decimal('traded_value_idr_actual', 24, 2)->nullable();
            $table->unsignedBigInteger('trade_count_actual')->nullable();
            $table->string('board_code', 32)->nullable();
            $table->string('session_code', 32)->nullable();
            $table->string('canonicalization_version', 64);
            $table->string('price_product_code', 64);
            $table->string('quality_state', 32);
            $table->unsignedBigInteger('config_snapshot_id');
            $table->string('source_scale_state', 32)->nullable();
            $table->unsignedBigInteger('source_scale_assessment_id')->nullable();
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('publication_id');
        });
        $schema->create('eod_indicators', function (Blueprint $table): void {
            $table->date('trade_date');
            $table->unsignedBigInteger('ticker_id');
            $table->unsignedBigInteger('listing_id');
            $table->boolean('is_valid');
            $table->string('indicator_set_version', 64);
            $table->decimal('roc20', 18, 10)->nullable();
            $table->string('formula_version', 64);
            $table->unsignedBigInteger('config_snapshot_id');
            $table->unsignedBigInteger('factor_set_id');
            $table->char('factor_set_hash', 64);
            $table->string('price_product_code', 64);
            $table->string('price_product_version', 64);
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('publication_id');
        });
        $schema->create('eod_eligibility', function (Blueprint $table): void {
            $table->date('trade_date');
            $table->unsignedBigInteger('ticker_id');
            $table->unsignedBigInteger('listing_id');
            $table->boolean('eligible');
            $table->string('reason_code', 64);
            $table->string('universe_membership_state', 32);
            $table->string('bar_expectation_state', 32);
            $table->string('delivery_state', 32);
            $table->string('canonical_quality_state', 32);
            $table->string('liquidity_state', 32);
            $table->string('temporal_status_state', 32);
            $table->string('event_risk_state', 32);
            $table->string('source_provenance_state', 32);
            $table->string('price_basis_state', 32);
            $table->string('contamination_state', 32);
            $table->string('indicator_state', 32);
            $table->text('eligibility_reasons_json');
            $table->unsignedBigInteger('config_snapshot_id');
            $table->string('market_structure_resolution_state', 32);
            $table->unsignedBigInteger('trading_status_revision_id');
            $table->unsignedBigInteger('price_band_revision_id');
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('publication_id');
        });
    }

    private function seed(string $side, int $dummyCount, int $listingId, int $publicationId, int $sourceId, int $tickerId): void
    {
        $connection = DB::connection($this->connections[$side]);
        config()->set('database.default', $this->connections[$side]);

        if ($side === 'b') {
            $connection->statement('ALTER TABLE si_entities AUTO_INCREMENT = 19000');
            $connection->statement('ALTER TABLE si_revisions AUTO_INCREMENT = 23000');
        }

        for ($i = 0; $i < $dummyCount; $i++) {
            $connection->table('md_config_snapshots')->insert(['config_hash' => hash('sha256', 'dummy-'.$side.'-'.$i)]);
        }
        $configId = (int) $connection->table('md_config_snapshots')->insertGetId([
            'config_hash' => hash('sha256', 'governed-config-content'),
        ]);
        $alternateConfigId = (int) $connection->table('md_config_snapshots')->insertGetId([
            'config_hash' => hash('sha256', 'governed-config-content'),
        ]);

        $foundation = new FoundationService(new FoundationRepository($connection));
        $foundation->bootstrap(
            storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260928-v1'),
            base_path('resources/security_identity/foundation-source-basis-20260928-v1.registry.json')
        );
        $foundation->bootstrap(
            storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260929-ikpm-listing-v2'),
            base_path('resources/security_identity/foundation-source-basis-20260929-ikpm-listing-v2.registry.json')
        );
        $foundation->bootstrap(
            storage_path('app/market_data/evidence/MD-B10-A002/foundation-source-basis-20260929-ikpm-current-window-v3'),
            base_path('resources/security_identity/foundation-source-basis-20260929-ikpm-current-window-v3.registry.json')
        );

        $hashes = [
            'observation_manifest_hash' => hash('sha256', 'observation-manifest'),
            'identity_revision_set_hash' => hash('sha256', 'identity-revisions'),
            'status_revision_set_hash' => hash('sha256', 'status-revisions'),
            'event_revision_set_hash' => hash('sha256', 'event-revisions'),
            'source_scale_assessment_set_hash' => hash('sha256', 'scale-assessments'),
            'market_structure_revision_set_hash' => hash('sha256', 'market-structure'),
            'factor_decision_set_hash' => hash('sha256', 'factor-decisions'),
        ];
        $connection->table('md_publication_lineage_bindings')->insert(array_merge($hashes, [
            'publication_id' => $publicationId,
            'read_model_version' => 'market_data_read_product_v1',
        ]));
        $connection->table('md_source_observation_rows')->insert([
            'source_observation_id' => $sourceId,
            'listing_id' => $listingId,
            'provider' => 'YAHOO_FINANCE',
            'provider_symbol' => 'IKPM.JK',
            'trade_date' => '2026-09-29',
        ]);
        $connection->table('eod_bars')->insert([
            'trade_date' => '2026-09-29', 'ticker_id' => $tickerId, 'listing_id' => $listingId,
            'source_observation_id' => $sourceId, 'open' => 100, 'high' => 110, 'low' => 95,
            'close' => 105, 'volume' => 1000, 'adj_close' => $side === 'a' ? 105 : 999,
            'previous_close' => 99, 'traded_value_idr_actual' => 105000, 'trade_count_actual' => 25,
            'board_code' => 'DEVELOPMENT', 'session_code' => 'REGULAR',
            'canonicalization_version' => 'eod_canonical_v1', 'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED', 'config_snapshot_id' => $alternateConfigId,
            'source_scale_state' => 'RAW_CONFIRMED', 'source_scale_assessment_id' => 9000 + $listingId,
            'run_id' => 8000 + $tickerId, 'publication_id' => $publicationId,
        ]);
        $connection->table('eod_indicators')->insert([
            'trade_date' => '2026-09-29', 'ticker_id' => $tickerId, 'listing_id' => $listingId,
            'is_valid' => 1, 'indicator_set_version' => 'weekly_swing_v1', 'roc20' => '0.0400000000',
            'formula_version' => 'formula-v1', 'config_snapshot_id' => $alternateConfigId,
            'factor_set_id' => 7000 + $listingId, 'factor_set_hash' => hash('sha256', 'factor-set'),
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'price_product_version' => 'structural_adjusted_v1',
            'run_id' => 8000 + $tickerId, 'publication_id' => $publicationId,
        ]);
        $connection->table('eod_eligibility')->insert([
            'trade_date' => '2026-09-29', 'ticker_id' => $tickerId, 'listing_id' => $listingId,
            'eligible' => 1, 'reason_code' => 'ELIGIBLE', 'universe_membership_state' => 'IN_SCOPE',
            'bar_expectation_state' => 'EXPECTED', 'delivery_state' => 'DELIVERED',
            'canonical_quality_state' => 'VALIDATED', 'liquidity_state' => 'PASS',
            'temporal_status_state' => 'ACTIVE', 'event_risk_state' => 'CLEAR',
            'source_provenance_state' => 'TRACEABLE', 'price_basis_state' => 'STRUCTURAL_ADJUSTED',
            'contamination_state' => 'CLEAN', 'indicator_state' => 'VALID',
            'eligibility_reasons_json' => '[]', 'config_snapshot_id' => $alternateConfigId,
            'market_structure_resolution_state' => 'RESOLVED',
            'trading_status_revision_id' => 10000 + $listingId,
            'price_band_revision_id' => 11000 + $listingId,
            'run_id' => 8000 + $tickerId, 'publication_id' => $publicationId,
        ]);

        $this->contexts[$side] = [
            'config_id' => $configId,
            'alternate_config_id' => $alternateConfigId,
            'publication_id' => $publicationId,
            'listing_id' => $listingId,
            'ticker_id' => $tickerId,
        ];
    }

    private function artifactHashes(string $side): array
    {
        config()->set('database.default', $this->connections[$side]);
        $context = $this->contexts[$side];
        $connection = DB::connection($this->connections[$side]);
        $foundation = new FoundationService(new FoundationRepository($connection));
        $service = new ArtifactSemanticHashService(
            new DeterministicHashService(),
            new TemporalIdentityRepository($foundation)
        );
        $run = (object) [
            'knowledge_cutoff_at' => '2026-09-29 00:38:15',
            'config_snapshot_id' => $context['config_id'],
            'config_hash' => hash('sha256', 'governed-config-content'),
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
        ];
        $publication = (object) [
            'publication_id' => $context['publication_id'],
            'config_snapshot_id' => $context['config_id'],
            'factor_set_hash' => hash('sha256', 'factor-set'),
            'read_model_version' => 'market_data_read_product_v1',
        ];

        return [
            'bars' => $service->hashStoredArtifact('bars', 'eod_bars', '2026-09-29', $run, $publication),
            'indicators' => $service->hashStoredArtifact('indicators', 'eod_indicators', '2026-09-29', $run, $publication),
            'eligibility' => $service->hashStoredArtifact('eligibility', 'eod_eligibility', '2026-09-29', $run, $publication),
        ];
    }

    public function test_all_three_market_data_hashes_are_equal_across_local_allocation_histories(): void
    {
        $a = $this->artifactHashes('a');
        $b = $this->artifactHashes('b');
        $this->assertSame($a, $b);
        $this->assertSame($a, $this->artifactHashes('a'));

        $this->assertNotSame($this->contexts['a']['ticker_id'], $this->contexts['b']['ticker_id']);
        $this->assertNotSame($this->contexts['a']['listing_id'], $this->contexts['b']['listing_id']);
        $this->assertNotSame($this->contexts['a']['config_id'], $this->contexts['b']['config_id']);
        $this->assertNotSame(
            DB::connection($this->connections['a'])->table('si_entities')->min('row_id'),
            DB::connection($this->connections['b'])->table('si_entities')->min('row_id')
        );
    }

    public function test_real_semantic_changes_change_only_the_owning_artifact_hash(): void
    {
        $baseline = $this->artifactHashes('a');
        $connection = DB::connection($this->connections['a']);

        $connection->table('eod_bars')->update(['close' => 106]);
        $barChanged = $this->artifactHashes('a');
        $this->assertNotSame($baseline['bars'], $barChanged['bars']);
        $connection->table('eod_bars')->update(['close' => 105]);

        $connection->table('eod_indicators')->update(['roc20' => '0.0500000000']);
        $indicatorChanged = $this->artifactHashes('a');
        $this->assertNotSame($baseline['indicators'], $indicatorChanged['indicators']);
        $connection->table('eod_indicators')->update(['roc20' => '0.0400000000']);

        $connection->table('eod_eligibility')->update([
            'eligible' => 0,
            'reason_code' => 'LIQUIDITY_LOW',
            'liquidity_state' => 'FAIL',
            'eligibility_reasons_json' => '["LIQUIDITY_LOW"]',
        ]);
        $eligibilityChanged = $this->artifactHashes('a');
        $this->assertNotSame($baseline['eligibility'], $eligibilityChanged['eligibility']);
    }

    public function test_foundation_or_content_binding_gaps_fail_closed(): void
    {
        $connection = DB::connection($this->connections['a']);
        $connection->table('md_source_observation_rows')->update(['provider_symbol' => 'UNKNOWN.JK']);
        try {
            $this->artifactHashes('a');
            $this->fail('Missing foundation provider mapping was accepted.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('FOUNDATION_IDENTITY_HELD', $e->getMessage());
        }

        $connection->table('md_source_observation_rows')->update(['provider_symbol' => 'IKPM.JK']);
        $connection->table('md_config_snapshots')
            ->where('config_snapshot_id', $this->contexts['a']['alternate_config_id'])
            ->update(['config_hash' => hash('sha256', 'changed-content')]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ARTIFACT_CONFIG_CONTENT_MISMATCH');
        $this->artifactHashes('a');
    }
}
