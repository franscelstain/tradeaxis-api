<?php

use App\Application\MarketData\Services\AdjustmentFactorSetService;
use App\Application\MarketData\Services\PublicationGovernanceBindingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class StageEightGovernanceBindingTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
        Carbon::setTestNow(Carbon::parse('2026-08-13 12:00:00', 'Asia/Jakarta'));
        $this->seedConfigSnapshot();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    public function test_factor_set_applies_only_as_traded_and_holds_back_adjusted_and_unknown(): void
    {
        foreach ([1 => 'AS_TRADED', 2 => 'PROVIDER_BACK_ADJUSTED', 3 => null] as $listingId => $scaleState) {
            $this->seedListing($listingId, $listingId, 'MAIN', '2020-01-01 00:00:00');
            DB::table('md_corporate_action_revisions')->insert([
                'corporate_action_revision_id' => $listingId,
                'event_uid' => hash('sha256', 'event-'.$listingId),
                'revision_number' => 1,
                'listing_id' => $listingId,
                'action_type_code' => 'STOCK_SPLIT',
                'lifecycle_state' => 'EFFECTIVE',
                'verification_state' => 'AUTHORITATIVE_VERIFIED',
                'ex_date' => '2026-07-1'.($listingId + 4),
                'terms_json' => json_encode(['ratio' => ['from' => 1, 'to' => 5]]),
                'recorded_at' => '2026-08-01 00:00:00',
            ]);

            if ($scaleState !== null) {
                DB::table('md_source_scale_assessments')->insert([
                    'source_scale_assessment_id' => $listingId,
                    'assessment_uid' => hash('sha256', 'assessment-'.$listingId),
                    'revision_number' => 1,
                    'provider' => 'YAHOO_FINANCE',
                    'listing_id' => $listingId,
                    'corporate_action_revision_id' => $listingId,
                    'source_scale_state' => $scaleState,
                    'scale_effective_from' => '2026-07-1'.($listingId + 4),
                    'assessment_version' => AdjustmentFactorSetService::ASSESSMENT_VERSION,
                    'evidence_observation_set_hash' => hash('sha256', 'evidence-'.$listingId),
                    'evidence_json' => '{}',
                    'recorded_at' => '2026-08-02 00:00:00',
                    'created_at' => '2026-08-02 00:00:00',
                ]);
            }

            DB::table('eod_bars')->insert([
                'trade_date' => '2026-07-28',
                'ticker_id' => $listingId,
                'listing_id' => $listingId,
                'open' => 100,
                'high' => 110,
                'low' => 90,
                'close' => 105,
                'volume' => 1000,
                'source' => 'api',
                'run_id' => 77,
                'publication_id' => 99,
                'source_observation_id' => 100 + $listingId,
                'created_at' => '2026-08-13 12:00:00',
            ]);
        }

        $run = (object) [
            'run_id' => 77,
            'config_snapshot_id' => 1,
            'started_at' => '2026-08-13 11:00:00',
        ];
        $bars = [
            1 => [['source_observation_id' => 101]],
            2 => [['source_observation_id' => 102]],
            3 => [['source_observation_id' => 103]],
        ];

        $result = (new AdjustmentFactorSetService())->ensureForPublication($run, 99, '2026-07-28', $bars);

        $this->assertSame([1], array_keys($result['factors_by_ticker']));
        $this->assertSame([2, 3], array_keys($result['held_events_by_ticker']));
        $this->assertSame(1, (int) DB::table('md_adjustment_factors')->count());
        $this->assertSame([
            'APPLIED',
            'HELD_PROVIDER_BACK_ADJUSTED',
            'HELD_SOURCE_SCALE_UNKNOWN',
        ], DB::table('md_adjustment_factor_decisions')->orderBy('listing_id')->pluck('decision_state')->all());
        $this->assertSame([
            'AS_TRADED',
            'PROVIDER_BACK_ADJUSTED',
            'UNKNOWN',
        ], DB::table('eod_bars')->orderBy('ticker_id')->pluck('source_scale_state')->all());
        $this->assertSame(3, (int) DB::table('md_source_scale_assessments')->count());
    }

    public function test_factor_revision_recorded_after_run_start_is_not_in_factor_set(): void
    {
        $this->seedListing(1, 1, 'MAIN', '2020-01-01 00:00:00');
        DB::table('md_corporate_action_revisions')->insert([
            'corporate_action_revision_id' => 1,
            'event_uid' => hash('sha256', 'late-event'),
            'revision_number' => 1,
            'listing_id' => 1,
            'action_type_code' => 'STOCK_SPLIT',
            'lifecycle_state' => 'EFFECTIVE',
            'verification_state' => 'AUTHORITATIVE_VERIFIED',
            'ex_date' => '2026-07-15',
            'terms_json' => json_encode(['ratio' => ['from' => 1, 'to' => 5]]),
            'recorded_at' => '2026-08-13 11:01:00',
        ]);

        $result = (new AdjustmentFactorSetService())->ensureForPublication((object) [
            'run_id' => 1,
            'config_snapshot_id' => 1,
            'started_at' => '2026-08-13 11:00:00',
        ], 1, '2026-07-28', []);

        $this->assertSame([], $result['decisions']);
        $this->assertSame(0, (int) DB::table('md_source_scale_assessments')->count());
        $this->assertSame(0, (int) DB::table('md_adjustment_factor_decisions')->count());
    }

    public function test_publication_binds_authoritative_tiers_and_fails_closed_without_board_evidence(): void
    {
        $this->seedListing(1, 1, 'MAIN', '2020-01-01 00:00:00');
        $this->seedListing(2, 2, null, '2020-01-01 00:00:00');
        $this->seedMarketStructureRevisions('2026-08-13 10:00:00');

        DB::table('md_adjustment_factor_sets')->insert([
            'factor_set_id' => 1,
            'factor_set_uid' => hash('sha256', 'factor-set'),
            'price_product_code' => 'STRUCTURAL_ADJUSTED',
            'factor_formula_version' => 'structural_factor_product_v1',
            'config_snapshot_id' => 1,
            'state' => 'BOUND',
            'content_hash' => hash('sha256', 'factor-set-content'),
            'recorded_at' => '2026-08-13 11:00:00',
            'created_at' => '2026-08-13 11:00:00',
        ]);
        DB::table('md_market_calendar_revisions')->insert([
            'calendar_revision_id' => 1,
            'cal_date' => '2026-07-28',
            'revision_uid' => hash('sha256', 'calendar'),
            'session_state' => 'COMPLETED_REGULAR',
            'recorded_at' => '2026-07-28 17:00:00',
        ]);
        DB::table('eod_publications')->insert([
            'publication_id' => 10,
            'trade_date' => '2026-07-28',
            'run_id' => 7,
            'publication_version' => 2,
            'is_current' => 0,
            'seal_state' => 'DRAFT',
            'factor_set_id' => 1,
            'observation_manifest_hash' => hash('sha256', 'observations'),
            'read_model_version' => 'market_data_read_product_v1',
            'created_at' => '2026-08-13 11:00:00',
        ]);
        foreach ([1, 2] as $listingId) {
            DB::table('eod_eligibility')->insert([
                'trade_date' => '2026-07-28',
                'ticker_id' => $listingId,
                'listing_id' => $listingId,
                'eligible' => 0,
                'run_id' => 7,
                'publication_id' => 10,
                'bar_expectation_state' => 'BAR_EXPECTED',
                'temporal_status_state' => 'ACTIVE',
                'created_at' => '2026-08-13 11:00:00',
            ]);
        }

        $publication = DB::table('eod_publications')->where('publication_id', 10)->first();
        $result = (new PublicationGovernanceBindingService())->bind((object) [
            'config_snapshot_id' => 1,
            'started_at' => '2026-08-13 11:00:00',
        ], $publication, '2026-07-28');

        $resolved = DB::table('md_publication_market_structure_bindings')->where('listing_id', 1)->first();
        $unknown = DB::table('md_publication_market_structure_bindings')->where('listing_id', 2)->first();
        $this->assertSame('RESOLVED_STANDARD_BOARD', $resolved->resolution_state);
        $this->assertSame([1, 2, 3], [
            (int) $resolved->price_band_revision_id,
            (int) $resolved->minimum_price_revision_id,
            (int) $resolved->tick_size_revision_id,
        ]);
        $this->assertSame('FAIL_CLOSED_BOARD_UNKNOWN', $unknown->resolution_state);
        $this->assertSame('MARKET_STRUCTURE_BOARD_UNKNOWN', $unknown->reason_code);
        $this->assertNull($unknown->price_band_revision_id);
        $this->assertSame(2, $result['market_structure_binding_count']);
        $this->assertSame(1, (int) DB::table('md_publication_lineage_bindings')->where('publication_id', 10)->count());
    }

    private function seedConfigSnapshot(): void
    {
        DB::table('md_config_snapshots')->insert([
            'config_snapshot_id' => 1,
            'snapshot_uid' => hash('sha256', 'config'),
            'snapshot_schema_version' => 'v1',
            'serialization_version' => 'v1',
            'resolved_config_json' => '{}',
            'config_hash' => hash('sha256', '{}'),
            'registry_revision' => 'test',
            'effective_at' => '2026-01-01 00:00:00',
            'recorded_at' => '2026-01-01 00:00:00',
            'build_id' => 'test-build',
            'environment_profile' => 'testing',
            'resolver_version' => 'v1',
            'created_at' => '2026-01-01 00:00:00',
        ]);
    }

    private function seedListing(int $listingId, int $tickerId, ?string $board, string $recordedAt): void
    {
        DB::table('md_listings')->insert([
            'listing_id' => $listingId,
            'listing_uid' => hash('sha256', 'listing-'.$listingId),
            'legacy_ticker_id' => $tickerId,
            'instrument_id' => $listingId,
            'exchange_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'board_code' => $board,
            'listed_date' => '2010-01-01',
            'listing_state' => 'LISTED',
            'recorded_at' => $recordedAt,
            'created_at' => $recordedAt,
        ]);
    }

    private function seedMarketStructureRevisions(string $recordedAt): void
    {
        foreach ([1 => 'PRICE_BAND', 2 => 'MINIMUM_PRICE', 3 => 'TICK_SIZE'] as $id => $type) {
            DB::table('md_exchange_market_structure_revisions')->insert([
                'market_structure_revision_id' => $id,
                'rule_uid' => hash('sha256', 'rule-'.$type),
                'revision_number' => 1,
                'rule_type' => $type,
                'exchange_code' => 'IDX',
                'market_segment' => 'REGULAR',
                'instrument_scope_code' => 'IDX_REGULAR_STANDARD_EQUITY',
                'coverage_scope_json' => '{}',
                'effective_from' => '2025-01-01',
                'verification_state' => 'AUTHORITATIVE_VERIFIED',
                'source_uid' => hash('sha256', 'source-'.$type),
                'source_observation_id' => $id,
                'source_reference' => 'IDX-test',
                'content_hash' => hash('sha256', 'content-'.$type),
                'recorded_at' => $recordedAt,
            ]);
        }
    }
}
