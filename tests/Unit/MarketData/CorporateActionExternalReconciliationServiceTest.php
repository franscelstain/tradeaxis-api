<?php

use App\Application\MarketData\Services\CorporateActionExternalReconciliationService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class CorporateActionExternalReconciliationServiceTest extends TestCase
{
    use UsesMarketDataSqlite;
    protected function setUp(): void { parent::setUp(); $this->bootMarketDataSqlite(); $this->seedEvent(); }
    protected function tearDown(): void { $this->tearDownMarketDataSqlite(); parent::tearDown(); }

    private function seedEvent(): void
    {
        DB::table('md_listings')->insert(['listing_id'=>910,'listing_uid'=>'IDX:TEST:MAIN','legacy_ticker_id'=>910,'instrument_id'=>1,'exchange_code'=>'IDX','market_segment'=>'REGULAR','board_code'=>'MAIN','listed_date'=>'2020-01-01','delisted_date'=>null,'source_ref'=>'TEST','listing_state'=>'ACTIVE','recorded_at'=>'2026-01-01 00:00:00','created_at'=>'2026-01-01 00:00:00']);
        $terms=['ratio'=>['from'=>1,'to'=>5]];
        DB::table('md_corporate_action_revisions')->insert(['event_uid'=>'test-split','revision_number'=>1,'listing_id'=>910,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE','verification_state'=>'AUTHORITATIVE_VERIFIED','ex_date'=>'2026-07-15','cum_date'=>null,'record_date'=>null,'payment_date'=>null,'terms_json'=>json_encode($terms),'source_observation_id'=>1,'effective_at'=>null,'recorded_at'=>'2026-07-01 10:00:00','supersedes_revision_id'=>null]);
    }
    private function manifest($complete=true,array $events=null): array
    {
        $terms=['ratio'=>['from'=>1,'to'=>5]];
        return ['schema_version'=>CorporateActionExternalReconciliationService::MANIFEST_SCHEMA,'authority_name'=>'IDX TEST','authority_class'=>'EXCHANGE','scope_start'=>'2023-01-02','scope_end'=>'2026-07-31','scope_complete'=>$complete,'action_types'=>['STOCK_SPLIT'],'events'=>$events===null ? [['listing_uid'=>'IDX:TEST:MAIN','action_type_code'=>'STOCK_SPLIT','ex_date'=>'2026-07-15','terms_sha256'=>hash('sha256',json_encode($terms))]] : $events];
    }

    public function test_complete_authoritative_corpus_reconciles_both_directions(): void
    {
        $result=(new CorporateActionExternalReconciliationService())->reconcileManifest($this->manifest(true));
        $this->assertSame('PASS',$result['reconciliation_state']);
        $this->assertSame(0,$result['missing_platform_count']);
        $this->assertSame(0,$result['unexpected_platform_count']);
        $this->assertSame(0,$result['mismatch_count']);
    }

    public function test_incomplete_scope_never_qualifies_period_as_action_complete(): void
    {
        $manifest=$this->manifest(false); $manifest['scope_start']='2026-07-01';
        $result=(new CorporateActionExternalReconciliationService())->reconcileManifest($manifest);
        $this->assertSame('AUTHORITY_SCOPE_INCOMPLETE',$result['reconciliation_state']);
        $this->assertSame('PERIOD_NOT_ACTION_COMPLETE',$result['details']['qualification']);
    }


    public function test_utf8_bom_manifest_from_windows_powershell_is_decoded_without_changing_raw_hash(): void
    {
        $manifest=$this->manifest(false);
        $manifest['scope_start']='2026-07-01';
        $raw="\xEF\xBB\xBF".json_encode($manifest, JSON_UNESCAPED_SLASHES);
        $path=tempnam(sys_get_temp_dir(),'md-b11-manifest-');
        file_put_contents($path,$raw);
        try {
            $result=(new CorporateActionExternalReconciliationService())->reconcileFile($path,false);
            $this->assertSame('AUTHORITY_SCOPE_INCOMPLETE',$result['reconciliation_state']);
            $this->assertSame('PERIOD_NOT_ACTION_COMPLETE',$result['details']['qualification']);
            $this->assertSame(hash('sha256',$raw),$result['manifest_sha256']);
        } finally {
            @unlink($path);
        }
    }

    public function test_authority_action_missing_in_platform_is_detected(): void
    {
        DB::table('md_corporate_action_revisions')->delete();
        $result=(new CorporateActionExternalReconciliationService())->reconcileManifest($this->manifest(true));
        $this->assertSame('FAIL',$result['reconciliation_state']);
        $this->assertSame(1,$result['missing_platform_count']);
    }
}
