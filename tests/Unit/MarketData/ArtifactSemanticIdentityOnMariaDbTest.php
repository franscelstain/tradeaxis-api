<?php

use App\Application\MarketData\Services\ArtifactSemanticHashService;
use App\Application\MarketData\Services\DeterministicHashService;
use App\Application\MarketData\Services\SemanticNestedIdentityService;
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
            $table->string('semantic_nested_identity_version', 64)->nullable();
            foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $column) {
                $table->char($column, 64)->nullable();
            }
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
            $table->dateTime('source_timestamp')->nullable();
            $table->dateTime('acquired_at')->nullable();
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
            $table->string('sector_code', 8)->nullable();
            $table->unsignedBigInteger('sector_membership_id')->nullable();
            $table->decimal('atr14', 20, 10)->nullable();
            $table->string('atr_state_ref', 128)->nullable();
            $table->string('formula_version', 64);
            $table->unsignedBigInteger('config_snapshot_id');
            $table->unsignedBigInteger('factor_set_id');
            $table->char('factor_set_hash', 64);
            $table->string('price_product_code', 64);
            $table->string('price_product_version', 64);
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('publication_id');
        });
        $schema->create('market_data_sectors', function (Blueprint $table): void {
            $table->string('sector_code', 8);
            $table->string('classification_system', 32);
            $table->string('sector_index_code', 32)->nullable();
        });
        $schema->create('ticker_sector_memberships', function (Blueprint $table): void {
            $table->bigIncrements('membership_id');
            $table->unsignedBigInteger('ticker_id');
            $table->unsignedBigInteger('listing_id');
            $table->string('sector_code', 8);
            $table->string('classification_system', 32);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('source_name', 64);
            $table->string('source_ref', 255)->nullable();
            $table->string('source_authority_class', 32);
            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('supersedes_membership_id')->nullable();
            $table->string('operator_name', 128)->nullable();
            $table->string('reason_code', 64)->nullable();
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
            $connection->statement('ALTER TABLE ticker_sector_memberships AUTO_INCREMENT = 5000');
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
        // V2 artifacts read only the V2 nested identities (D-MD-B10-A002-003). They are hand-set here
        // because this test proves the artifact layer; the nested producers have their own proof.
        $semantic = ['semantic_nested_identity_version' => SemanticNestedIdentityService::VERSION];
        foreach (SemanticNestedIdentityService::LINEAGE_COLUMNS as $member => $column) {
            $semantic[$column] = hash('sha256', 'semantic-'.$member);
        }
        $connection->table('md_publication_lineage_bindings')->insert(array_merge($hashes, $semantic, [
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
        // The same two governed revisions of one sector membership on both sides: an earlier
        // revision, and a later one that supersedes it. Only their local ids differ.
        $connection->table('market_data_sectors')->insert([
            'sector_code' => 'HEALTH', 'classification_system' => 'IDX-IC', 'sector_index_code' => 'IDXHLTH',
        ]);
        if ($side === 'b') {
            $connection->table('ticker_sector_memberships')->insert([
                'ticker_id' => 1, 'listing_id' => 424242, 'sector_code' => 'ENERGY', 'classification_system' => 'IDX-IC',
                'effective_from' => '2020-01-01', 'source_name' => 'IDX', 'source_ref' => 'unrelated',
                'source_authority_class' => 'EXCHANGE_AUTHORITATIVE', 'recorded_at' => '2026-01-01 00:00:00',
            ]);
        }
        $olderMembershipId = (int) $connection->table('ticker_sector_memberships')->insertGetId([
            'ticker_id' => $tickerId, 'listing_id' => $listingId, 'sector_code' => 'HEALTH',
            'classification_system' => 'IDX-IC', 'effective_from' => '2020-01-01', 'effective_to' => '2026-06-30',
            'source_name' => 'IDX', 'source_ref' => 'IDX-IC-2026-01', 'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'recorded_at' => '2026-01-05 09:00:00',
        ]);
        $membershipId = (int) $connection->table('ticker_sector_memberships')->insertGetId([
            'ticker_id' => $tickerId, 'listing_id' => $listingId, 'sector_code' => 'HEALTH',
            'classification_system' => 'IDX-IC', 'effective_from' => '2020-01-01', 'effective_to' => null,
            'source_name' => 'IDX', 'source_ref' => 'IDX-IC-2026-07', 'source_authority_class' => 'EXCHANGE_AUTHORITATIVE',
            'recorded_at' => '2026-07-02 09:00:00', 'supersedes_membership_id' => $olderMembershipId,
        ]);
        $connection->table('eod_bars')->insert([
            'trade_date' => '2026-09-29', 'ticker_id' => $tickerId, 'listing_id' => $listingId,
            'source_observation_id' => $sourceId, 'open' => 100, 'high' => 110, 'low' => 95,
            'close' => 105, 'volume' => 1000, 'adj_close' => $side === 'a' ? 105 : 999,
            'previous_close' => 99, 'traded_value_idr_actual' => 105000, 'trade_count_actual' => 25,
            'board_code' => 'DEVELOPMENT', 'session_code' => 'REGULAR',
            'source_timestamp' => '2026-09-29 16:00:00', 'acquired_at' => '2026-09-29 17:20:00',
            'canonicalization_version' => 'eod_canonical_v1', 'price_product_code' => 'RAW',
            'quality_state' => 'VALIDATED', 'config_snapshot_id' => $alternateConfigId,
            'source_scale_state' => 'RAW_CONFIRMED', 'source_scale_assessment_id' => 9000 + $listingId,
            'run_id' => 8000 + $tickerId, 'publication_id' => $publicationId,
        ]);
        $connection->table('eod_indicators')->insert([
            'trade_date' => '2026-09-29', 'ticker_id' => $tickerId, 'listing_id' => $listingId,
            'is_valid' => 1, 'indicator_set_version' => 'weekly_swing_v1', 'roc20' => '0.0400000000',
            'sector_code' => 'HEALTH', 'sector_membership_id' => $membershipId,
            'atr14' => '1.2345678901', 'atr_state_ref' => 'atr-state/v1:'.hash('sha256', 'atr-chain'),
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
            'membership_id' => $membershipId,
            'older_membership_id' => $olderMembershipId,
        ];
    }

    private function artifactHashes(string $side, array $runOverrides = []): array
    {
        config()->set('database.default', $this->connections[$side]);
        $context = $this->contexts[$side];
        $connection = DB::connection($this->connections[$side]);
        $foundation = new FoundationService(new FoundationRepository($connection));
        $service = new ArtifactSemanticHashService(
            new DeterministicHashService(),
            new TemporalIdentityRepository($foundation)
        );
        $run = (object) ($runOverrides + [
            'knowledge_cutoff_at' => '2026-09-29 00:38:15',
            'config_snapshot_id' => $context['config_id'],
            'config_hash' => hash('sha256', 'governed-config-content'),
            'artifact_hash_profile' => ArtifactSemanticHashService::PROFILE_V2,
            'freshness_state' => 'FRESH',
        ]);
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

    // ------------------------------------------------------------------
    // F-MD-B10-A002-005 remediation, through the production loader: stored rows and the stored
    // sector-membership revisions, hashed by hashStoredArtifact with the retained foundation roots.
    // ------------------------------------------------------------------

    public function test_g1_stored_bar_timestamps_move_only_the_bars_hash(): void
    {
        $baseline = $this->artifactHashes('a');
        $connection = DB::connection($this->connections['a']);

        $connection->table('eod_bars')->update(['acquired_at' => '2026-09-29 17:21:00']);
        $acquired = $this->artifactHashes('a');
        $this->assertNotSame($baseline['bars'], $acquired['bars']);
        $this->assertSame($baseline['indicators'], $acquired['indicators']);
        $this->assertSame($baseline['eligibility'], $acquired['eligibility']);
        $connection->table('eod_bars')->update(['acquired_at' => '2026-09-29 17:20:00']);

        $connection->table('eod_bars')->update(['source_timestamp' => '2026-09-29 15:00:00']);
        $this->assertNotSame($baseline['bars'], $this->artifactHashes('a')['bars']);
        $connection->table('eod_bars')->update(['source_timestamp' => null]);
        $this->assertNotSame($baseline['bars'], $this->artifactHashes('a')['bars'], 'an absent source timestamp is a different fact');
        $connection->table('eod_bars')->update(['source_timestamp' => '2026-09-29 16:00:00']);

        $this->assertSame($baseline, $this->artifactHashes('a'), 'control: restored state reproduces');
    }

    public function test_g2_the_stored_atr_state_reference_moves_only_the_indicators_hash(): void
    {
        $baseline = $this->artifactHashes('a');
        $connection = DB::connection($this->connections['a']);

        $connection->table('eod_indicators')->update(['atr_state_ref' => 'atr-state/v1:'.hash('sha256', 'a-different-chain')]);
        $changed = $this->artifactHashes('a');
        $this->assertNotSame($baseline['indicators'], $changed['indicators']);
        $this->assertSame($baseline['bars'], $changed['bars']);
        $this->assertSame($baseline['eligibility'], $changed['eligibility']);

        // The ATR value is still published, so a missing reference is a null required binding.
        $connection->table('eod_indicators')->update(['atr_state_ref' => null]);
        $refusal = null;
        try {
            $this->artifactHashes('a');
        } catch (RuntimeException $e) {
            $refusal = $e;
        }
        $this->assertNotNull($refusal, 'an ATR value without its reference was hashed');
        $this->assertStringContainsString('ARTIFACT_ATR_STATE_REFERENCE_REQUIRED', $refusal->getMessage());

        // A withdrawn (NULL) ATR carries no reference, and that is a different, hashable fact.
        $connection->table('eod_indicators')->update(['atr14' => null, 'atr_state_ref' => null]);
        $withdrawn = $this->artifactHashes('a');
        $this->assertNotSame($baseline['indicators'], $withdrawn['indicators']);
    }

    public function test_g3_sector_membership_revision_content_and_supersession_move_only_the_indicators_hash(): void
    {
        $baseline = $this->artifactHashes('a');
        $connection = DB::connection($this->connections['a']);
        $memberships = $connection->table('ticker_sector_memberships');
        $current = $this->contexts['a']['membership_id'];
        $older = $this->contexts['a']['older_membership_id'];

        // Every field of the governed revision tuple is semantic; none of them is an id.
        $variants = [
            'source_ref' => [$current, ['source_ref' => 'IDX-IC-2026-07-CORRECTED']],
            'effective_from' => [$current, ['effective_from' => '2021-01-01']],
            'effective_to' => [$current, ['effective_to' => '2030-12-31']],
            'source_name' => [$current, ['source_name' => 'IDX-OFFICIAL']],
            'authority class' => [$current, ['source_authority_class' => 'OPERATOR_ENTERED', 'operator_name' => 'op', 'reason_code' => 'RECLASS']],
            'knowledge time' => [$current, ['recorded_at' => '2026-07-03 09:00:00']],
            'superseded revision content' => [$older, ['source_ref' => 'IDX-IC-2026-01-CORRECTED']],
            'superseded revision knowledge time' => [$older, ['recorded_at' => '2026-01-06 09:00:00']],
        ];
        foreach ($variants as $name => [$id, $change]) {
            $original = (array) $connection->table('ticker_sector_memberships')->where('membership_id', $id)->first();
            $connection->table('ticker_sector_memberships')->where('membership_id', $id)->update($change);
            $changed = $this->artifactHashes('a');
            $this->assertNotSame($baseline['indicators'], $changed['indicators'], $name);
            $this->assertSame($baseline['bars'], $changed['bars'], $name.' must not move bars');
            $this->assertSame($baseline['eligibility'], $changed['eligibility'], $name.' must not move eligibility');
            $connection->table('ticker_sector_memberships')->where('membership_id', $id)->update(array_intersect_key($original, $change + ['operator_name' => 1, 'reason_code' => 1]));
            $this->assertSame($baseline, $this->artifactHashes('a'), 'control after '.$name);
        }

        // The sector master's benchmark index mapping is part of the revision the measure used.
        $connection->table('market_data_sectors')->update(['sector_index_code' => 'IDXHLTH2']);
        $this->assertNotSame($baseline['indicators'], $this->artifactHashes('a')['indicators'], 'sector index mapping');
        $connection->table('market_data_sectors')->update(['sector_index_code' => 'IDXHLTH']);
        $this->assertSame($baseline, $this->artifactHashes('a'));
    }

    public function test_g3_the_same_membership_content_under_a_different_allocation_is_the_same_identity(): void
    {
        $a = $this->artifactHashes('a');
        $b = $this->artifactHashes('b');
        $this->assertNotSame($this->contexts['a']['membership_id'], $this->contexts['b']['membership_id']);
        $this->assertNotSame($this->contexts['a']['older_membership_id'], $this->contexts['b']['older_membership_id']);
        $this->assertSame($a['indicators'], $b['indicators']);
    }

    public function test_g3_an_out_of_cutoff_or_non_authoritative_or_foreign_membership_is_refused(): void
    {
        $connection = DB::connection($this->connections['a']);
        $current = $this->contexts['a']['membership_id'];
        $older = $this->contexts['a']['older_membership_id'];
        // The refusal is recorded and asserted after the try: PHPUnit's own failure exception is a
        // RuntimeException, so a fail() inside this try would be caught by its own handler.
        $expect = function (string $code): void {
            $refusal = null;
            try {
                $this->artifactHashes('a');
            } catch (RuntimeException $e) {
                $refusal = $e;
            }
            $this->assertNotNull($refusal, 'the state was hashed instead of being refused: '.$code);
            $this->assertStringContainsString($code, $refusal->getMessage());
        };

        // A revision the run could not yet have known is never silently admitted.
        $connection->table('ticker_sector_memberships')->where('membership_id', $current)->update(['recorded_at' => '2026-09-29 00:38:16']);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_AFTER_KNOWLEDGE_CUTOFF');
        $connection->table('ticker_sector_memberships')->where('membership_id', $current)->update(['recorded_at' => '2026-07-02 09:00:00']);

        // ... and neither is a superseded revision that was learned later than the cutoff.
        $connection->table('ticker_sector_memberships')->where('membership_id', $older)->update(['recorded_at' => '2026-10-01 00:00:00']);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_AFTER_KNOWLEDGE_CUTOFF');
        $connection->table('ticker_sector_memberships')->where('membership_id', $older)->update(['recorded_at' => '2026-01-05 09:00:00']);

        $connection->table('ticker_sector_memberships')->where('membership_id', $current)->update(['source_authority_class' => 'DERIVED_REFERENCE']);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_NOT_AUTHORITATIVE');
        $connection->table('ticker_sector_memberships')->where('membership_id', $current)->update(['source_authority_class' => 'EXCHANGE_AUTHORITATIVE']);

        $connection->table('ticker_sector_memberships')->where('membership_id', $current)->update(['listing_id' => 999999]);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_LISTING_MISMATCH');
        $connection->table('ticker_sector_memberships')->where('membership_id', $current)->update(['listing_id' => $this->contexts['a']['listing_id']]);

        $connection->table('eod_indicators')->update(['sector_code' => 'ENERGY']);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_CODE_MISMATCH');
        $connection->table('eod_indicators')->update(['sector_code' => 'HEALTH']);

        $connection->table('eod_indicators')->update(['sector_membership_id' => 987654]);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_MISSING');
        $connection->table('eod_indicators')->update(['sector_membership_id' => null]);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_REVISION_REQUIRED');
        $connection->table('eod_indicators')->update(['sector_membership_id' => $current]);

        // A supersession cycle can never resolve to an identity.
        $connection->table('ticker_sector_memberships')->where('membership_id', $older)->update(['supersedes_membership_id' => $current]);
        $expect('ARTIFACT_SECTOR_MEMBERSHIP_SUPERSESSION_CYCLE');
        $connection->table('ticker_sector_memberships')->where('membership_id', $older)->update(['supersedes_membership_id' => null]);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $this->artifactHashes('a')['indicators'], 'control: restored state hashes');
    }

    public function test_g3_an_unknown_sector_binds_no_membership_revision(): void
    {
        $connection = DB::connection($this->connections['a']);
        $baseline = $this->artifactHashes('a');
        $connection->table('eod_indicators')->update(['sector_code' => 'UNKNOWN', 'sector_membership_id' => null]);
        $unknown = $this->artifactHashes('a');
        $this->assertNotSame($baseline['indicators'], $unknown['indicators']);

        $connection->table('eod_indicators')->update(['sector_code' => 'UNKNOWN', 'sector_membership_id' => $this->contexts['a']['membership_id']]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ARTIFACT_SECTOR_MEMBERSHIP_CODE_MISMATCH');
        $this->artifactHashes('a');
    }

    public function test_g4_the_frozen_run_freshness_moves_only_the_eligibility_hash(): void
    {
        $baseline = $this->artifactHashes('a');
        $stale = $this->artifactHashes('a', ['freshness_state' => 'STALE']);
        $this->assertNotSame($baseline['eligibility'], $stale['eligibility']);
        $this->assertSame($baseline['bars'], $stale['bars']);
        $this->assertSame($baseline['indicators'], $stale['indicators']);

        // The manifest's normalisation applies: an unevaluated run is NOT_AVAILABLE, never FRESH.
        $unevaluated = $this->artifactHashes('a', ['freshness_state' => 'NOT_EVALUATED']);
        $this->assertSame($this->artifactHashes('a', ['freshness_state' => 'NOT_AVAILABLE'])['eligibility'], $unevaluated['eligibility']);
        $this->assertNotSame($baseline['eligibility'], $unevaluated['eligibility']);
        $this->assertSame($this->artifactHashes('b', ['freshness_state' => 'STALE'])['eligibility'], $stale['eligibility'], 'allocation independent');
    }
}
