<?php

use App\Application\MarketData\Services\CorporateActionRevisionService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class CorporateActionRevisionServiceTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); $this->seedListing(); }
    protected function tearDown(): void { $this->tearDownMarketDataSqlite(); parent::tearDown(); }

    private function seedListing(): void
    {
        DB::table('md_listings')->insert(['listing_id'=>901,'listing_uid'=>'listing-901','legacy_ticker_id'=>901,'instrument_id'=>1,'exchange_code'=>'IDX','market_segment'=>'REGULAR','board_code'=>'MAIN','listed_date'=>'2020-01-01','delisted_date'=>null,'source_ref'=>'TEST','listing_state'=>'ACTIVE','recorded_at'=>'2026-01-01 00:00:00','created_at'=>'2026-01-01 00:00:00']);
    }

    private function sourceObservation($id): void
    {
        DB::table('md_source_observations')->insert([
            'source_observation_id'=>$id,'observation_uid'=>str_repeat((string) ($id % 10),64),'parent_observation_id'=>null,'run_id'=>null,'attempt_uid'=>'test-'.$id,
            'requested_trade_date'=>'2026-07-01','requested_start_date'=>null,'requested_end_date'=>null,'source_mode'=>'MANUAL','source_name'=>'IDX_OFFICIAL','provider'=>null,'provider_symbol'=>null,'provider_mapping_id'=>null,'mapping_revision'=>null,'config_snapshot_id'=>null,
            'sanitized_request_identity'=>'test','response_status'=>200,'content_type'=>'application/pdf','source_timestamp'=>null,'acquired_at'=>'2026-07-01 10:00:00','provider_schema_version'=>null,'schema_fingerprint'=>null,'adapter_version'=>'test','payload_hash'=>hash('sha256','obs-'.$id),'payload_ref'=>'test://obs/'.$id,'payload_byte_length'=>10,'bounded_payload_body'=>null,'outcome_state'=>'ACQUIRED','validation_state'=>'VALID','reason_code'=>null,'supersedes_observation_id'=>null,'created_at'=>'2026-07-01 10:00:00',
        ]);
    }

    public function test_verified_revision_requires_traceable_source_observation(): void
    {
        $service = new CorporateActionRevisionService();
        try {
            $service->append(['event_uid'=>'e1','listing_id'=>901,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'AUTHORITATIVE_VERIFIED','ex_date'=>'2026-07-15','terms'=>['ratio'=>['from'=>1,'to'=>5]]]);
            $this->fail('verified event without source observation must fail');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('SOURCE_OBSERVATION_REQUIRED', $e->getMessage());
        }
        $this->assertSame(0, DB::table('md_corporate_action_revisions')->count());
    }

    public function test_manual_verified_requires_reviewer_and_evidence_reference(): void
    {
        $this->sourceObservation(77);
        $service = new CorporateActionRevisionService();
        try {
            $service->append(['event_uid'=>'e2','listing_id'=>901,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'MANUAL_VERIFIED','ex_date'=>'2026-07-15','source_observation_id'=>77,'terms'=>['ratio'=>['from'=>1,'to'=>5]]]);
            $this->fail('manual verification without evidence metadata must fail');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('MANUAL_VERIFICATION_EVIDENCE_REQUIRED', $e->getMessage());
        }
    }

    public function test_revision_is_append_only_and_supersession_preserves_event_identity(): void
    {
        $this->sourceObservation(78); $this->sourceObservation(79);
        $service = new CorporateActionRevisionService();
        $first = $service->append(['event_uid'=>'stable-event','listing_id'=>901,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'AUTHORITATIVE_VERIFIED','ex_date'=>'2026-07-15','source_observation_id'=>78,'terms'=>['ratio'=>['from'=>1,'to'=>5]]]);
        $second = $service->append(['event_uid'=>'stable-event','listing_id'=>901,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'AUTHORITATIVE_VERIFIED','ex_date'=>'2026-07-16','source_observation_id'=>79,'terms'=>['ratio'=>['from'=>1,'to'=>5]]]);
        $this->assertSame('APPENDED',$first['state']);
        $this->assertSame('APPENDED',$second['state']);
        $this->assertSame(2, DB::table('md_corporate_action_revisions')->count());
        $rows=DB::table('md_corporate_action_revisions')->orderBy('revision_number')->get();
        $this->assertSame('stable-event',(string)$rows[0]->event_uid);
        $this->assertSame('stable-event',(string)$rows[1]->event_uid);
        $this->assertSame((int)$rows[0]->corporate_action_revision_id,(int)$rows[1]->supersedes_revision_id);
    }
}
