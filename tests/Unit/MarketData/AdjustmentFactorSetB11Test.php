<?php

use App\Application\MarketData\Services\AdjustmentFactorSetService;
use Tests\Support\UsesMarketDataSqlite;

class AdjustmentFactorSetB11Test extends TestCase
{
    use UsesMarketDataSqlite;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); }
    protected function tearDown(): void { $this->tearDownMarketDataSqlite(); parent::tearDown(); }

    private function factorTerms($type,array $terms): array
    {
        $service=new AdjustmentFactorSetService();
        $method=new ReflectionMethod($service,'factorTermsForEvent'); $method->setAccessible(true);
        $event=(object)['action_type_code'=>$type,'terms_json'=>json_encode($terms)];
        return $method->invoke($service,$event);
    }

    private function seedAuthoritativeRevisionWithObservation($payloadHash): void
    {
        DB::table('tickers')->insert(['ticker_id'=>501,'ticker_code'=>'B12X','company_name'=>'B12X Tbk','is_active'=>1]);
        DB::table('md_listings')->insert([
            'listing_id'=>501,'listing_uid'=>'listing-b12x','legacy_ticker_id'=>501,'instrument_id'=>9501,
            'exchange_code'=>'IDX','market_segment'=>'REGULAR','board_code'=>'MAIN','listed_date'=>'2020-01-01',
            'source_ref'=>'TEST','listing_state'=>'ACTIVE','recorded_at'=>'2026-01-01 00:00:00','created_at'=>'2026-01-01 00:00:00',
        ]);
        $observationId=DB::table('md_source_observations')->insertGetId([
            'observation_uid'=>str_repeat('b',64),'attempt_uid'=>'b12-proof','requested_trade_date'=>'2026-02-01',
            'source_name'=>'IDX','provider'=>'IDX','sanitized_request_identity'=>'b12-proof','acquired_at'=>'2026-02-01 10:00:00',
            'adapter_version'=>'test-v1','payload_hash'=>$payloadHash,'outcome_state'=>'ACCEPTED','created_at'=>'2026-02-01 10:00:00',
        ]);
        DB::table('md_corporate_action_revisions')->insert([
            'corporate_action_revision_id'=>701,'event_uid'=>'evt-b12x','revision_number'=>1,'listing_id'=>501,
            'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'AUTHORITATIVE_VERIFIED',
            'ex_date'=>'2026-02-01','terms_json'=>json_encode(['ratio'=>['from'=>1,'to'=>2]]),'source_observation_id'=>$observationId,
            'recorded_at'=>'2026-02-01 11:00:00',
        ]);
    }

    public function test_authoritative_factor_revision_requires_accepted_source_payload_hash(): void
    {
        $this->seedAuthoritativeRevisionWithObservation(null);
        $service=new AdjustmentFactorSetService();
        $method=new ReflectionMethod($service,'authoritativeEventsThrough'); $method->setAccessible(true);
        $this->expectExceptionMessage('AUTHORITATIVE_FACTOR_PROVENANCE_INCOMPLETE');
        $method->invoke($service,'2026-02-02','2026-02-02 00:00:00');
    }

    public function test_authoritative_factor_revision_carries_canonical_source_payload_hash(): void
    {
        $hash=str_repeat('a',64); $this->seedAuthoritativeRevisionWithObservation($hash);
        $service=new AdjustmentFactorSetService();
        $method=new ReflectionMethod($service,'authoritativeEventsThrough'); $method->setAccessible(true);
        $rows=$method->invoke($service,'2026-02-02','2026-02-02 00:00:00');
        $this->assertCount(1,$rows); $this->assertSame($hash,$rows[0]->source_observation_hash);
    }

    public function test_verified_split_ratio_maps_deterministically_to_price_and_volume_factors(): void
    {
        $terms=$this->factorTerms('STOCK_SPLIT',['ratio'=>['from'=>1,'to'=>5]]);
        $this->assertTrue($terms['factor_required']); $this->assertSame(0.2,$terms['price_factor']); $this->assertSame(5.0,$terms['volume_factor']);
    }

    public function test_non_adjusting_registry_type_requires_no_factor(): void
    {
        $terms=$this->factorTerms('IPO',[]);
        $this->assertFalse($terms['factor_required']); $this->assertNull($terms['price_factor']); $this->assertNull($terms['volume_factor']);
    }

    public function test_volume_scaled_action_requires_explicit_action_specific_volume_factor(): void
    {
        $terms=$this->factorTerms('BONUS_SHARE',['adjustment'=>['price_factor'=>0.8]]);
        $this->assertTrue($terms['factor_required']);
        $this->assertNull($terms['price_factor']);
        $this->assertNull($terms['volume_factor']);
        $this->assertTrue($terms['volume_factor_required']);

        $complete=$this->factorTerms('BONUS_SHARE',['adjustment'=>['price_factor'=>0.8,'volume_factor'=>1.25]]);
        $this->assertSame(0.8,$complete['price_factor']); $this->assertSame(1.25,$complete['volume_factor']);
    }

    public function test_non_rescaling_share_count_action_does_not_invent_a_volume_factor(): void
    {
        // Current locked registry classifies PRIVATE_PLACEMENT as NONE/NONE. Share-count change
        // alone is not authority to rescale historical traded volume.
        $terms=$this->factorTerms('PRIVATE_PLACEMENT',['adjustment'=>['volume_factor'=>1.10]]);
        $this->assertFalse($terms['factor_required']);
        $this->assertNull($terms['price_factor']);
        $this->assertNull($terms['volume_factor']);
        $this->assertFalse($terms['volume_factor_required']);
    }

    public function test_gap_unknown_cash_distribution_is_not_manufactured_into_structural_factor(): void
    {
        $terms=$this->factorTerms('CASH_DIVIDEND',['amount'=>100]);
        $this->assertFalse($terms['factor_required']);
        $this->assertTrue($terms['breaks_price_continuity']);
    }
}
