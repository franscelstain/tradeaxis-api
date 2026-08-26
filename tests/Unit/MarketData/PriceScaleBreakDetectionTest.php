<?php

use App\Application\MarketData\Services\PriceScaleBreakDetectionService;
use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

class PriceScaleBreakDetectionTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    protected function tearDown(): void
    {
        $this->tearDownMarketDataSqlite();
        parent::tearDown();
    }

    private function seedTicker($tickerId, $code): int
    {
        DB::table('tickers')->insert(['ticker_id'=>$tickerId,'ticker_code'=>$code,'company_name'=>$code.' Tbk','is_active'=>1]);
        $listingId = 1000 + (int) $tickerId;
        DB::table('md_listings')->insert([
            'listing_id'=>$listingId,'listing_uid'=>'listing-'.$code,'legacy_ticker_id'=>$tickerId,'instrument_id'=>5000+$tickerId,
            'exchange_code'=>'IDX','market_segment'=>'REGULAR','board_code'=>'MAIN','listed_date'=>'2020-01-01','delisted_date'=>null,
            'source_ref'=>'TEST','listing_state'=>'ACTIVE','recorded_at'=>'2026-01-01 00:00:00','created_at'=>'2026-01-01 00:00:00',
        ]);
        return $listingId;
    }

    private function seedBars($tickerId, array $bars): void
    {
        foreach ($bars as $index => $bar) {
            DB::table('eod_bars')->insert([
                'trade_date'=>$bar[0],'ticker_id'=>$tickerId,'open'=>$bar[1],'high'=>max($bar[1],$bar[2]),'low'=>min($bar[1],$bar[2]),
                'close'=>$bar[2],'volume'=>1000000,'adj_close'=>$bar[2],'source'=>'YAHOO_FINANCE','run_id'=>1,
                'publication_id'=>100+$index,'source_observation_id'=>200+$index,'config_snapshot_id'=>1,'created_at'=>'2026-01-01 00:00:00',
            ]);
            $this->seedVerifiedMarketCalendarDate($bar[0]);
        }
    }

    private function detect($apply = true): array
    {
        return (new PriceScaleBreakDetectionService())->detect(null, null, null, $apply);
    }

    public function test_detects_a_persistent_split_and_infers_the_ratio_from_open(): void
    {
        $this->seedTicker(1, 'RMKE');
        $this->seedBars(1, [['2025-10-02',1805,1810],['2025-10-03',1810,1855],['2025-10-06',377,462],['2025-10-07',490,502]]);
        $result = $this->detect();
        $this->assertCount(1, $result['detected']);
        $break = $result['detected'][0];
        $this->assertSame('2025-10-06', $break['current_trade_date']);
        $this->assertSame('SCALE_SHIFT', $break['candidate_classification']);
        $this->assertSame('NO_LINKAGE_CANDIDATE', $break['linkage_state']);
        $this->assertSame('UNRESOLVED_SCALE_BREAK_CANDIDATE', $break['continuity_verdict']);
        $this->assertSame(5.0, (float) $break['inferred_ratio']);
        $this->assertSame(1, DB::table('md_price_scale_break_candidates')->count());
    }

    public function test_classifies_a_reverting_bar_as_an_isolated_anomaly(): void
    {
        $this->seedTicker(2, 'MLPT');
        $this->seedBars(2, [['2025-08-12',60000,61900],['2025-08-13',2670,2670],['2025-08-14',70000,70500],['2025-08-15',68000,68000]]);
        $types = array_column($this->detect()['detected'], 'candidate_classification');
        $this->assertContains('ISOLATED_ANOMALY', $types);
    }

    public function test_ignores_penny_price_oscillation_below_the_minimum_price(): void
    {
        $this->seedTicker(3, 'MKNT');
        $this->seedBars(3, [['2024-04-16',2,2],['2024-04-17',1,1],['2024-04-18',2,2],['2024-04-19',1,1]]);
        $result = $this->detect();
        $this->assertSame([], $result['detected']);
        $this->assertGreaterThan(0, $result['skipped_below_min_price']);
    }

    public function test_nearby_v2_event_is_diagnostic_linkage_candidate_not_explanation(): void
    {
        $listingId = $this->seedTicker(4, 'RAJA');
        $this->seedBars(4, [['2026-07-14',4500,4590],['2026-07-15',920,920],['2026-07-16',900,875]]);
        DB::table('md_corporate_action_revisions')->insert([
            'event_uid'=>'raja-split','revision_number'=>1,'listing_id'=>$listingId,'action_type_code'=>'STOCK_SPLIT','lifecycle_state'=>'EFFECTIVE',
            'verification_state'=>'PROVIDER_REPORTED','ex_date'=>'2026-07-16','cum_date'=>null,'record_date'=>null,'payment_date'=>null,
            'terms_json'=>null,'source_observation_id'=>null,'effective_at'=>null,'recorded_at'=>'2026-07-14 10:00:00','supersedes_revision_id'=>null,
        ]);
        $result = $this->detect();
        $this->assertCount(1, $result['detected']);
        $this->assertSame('REVISION_LINKAGE_CANDIDATE', $result['detected'][0]['linkage_state']);
        $this->assertNotSame('EXPLAINED', $result['detected'][0]['linkage_state']);
    }

    public function test_only_unresolved_candidates_feed_indicator_quarantine(): void
    {
        $this->seedTicker(5, 'SCCO');
        $this->seedBars(5, [['2024-01-31',9900,9975],['2024-02-01',2506,2500],['2024-02-02',2510,2520]]);
        $this->detect();
        $contamination = (new PriceScaleBreakRepository())->resolveContaminationForTickerIds([5], ['2024-02-01','2024-02-02','2024-02-05']);
        $this->assertArrayHasKey(5, $contamination);
        $this->assertSame('UNRESOLVED_SCALE_BREAK_CANDIDATE', $contamination[5][0]['continuity_verdict']);
    }

    public function test_dismissal_requires_positive_evidence_and_is_append_only(): void
    {
        $this->seedTicker(6, 'PYFA');
        $this->seedBars(6, [['2024-04-15',1030,1040],['2024-04-16',223,230],['2024-04-17',228,232]]);
        $this->detect();
        $candidateId = (int) DB::table('md_price_scale_break_candidates')->value('candidate_id');
        $repo = new PriceScaleBreakRepository();
        try {
            $repo->appendReview($candidateId, 'DISMISSED', 'reviewer', 'verified genuine move');
            $this->fail('dismissal without positive evidence must fail closed');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('POSITIVE_EVIDENCE', $e->getMessage());
        }
        $repo->appendReview($candidateId, 'DISMISSED', 'reviewer', 'verified genuine move', 999);
        $this->assertSame([], $repo->resolveContaminationForTickerIds([6], ['2024-04-16','2024-04-17','2024-04-18']));
        $this->assertSame(1, DB::table('md_price_scale_break_candidate_reviews')->count());
    }

    public function test_detection_is_idempotent_and_dry_run_is_non_mutating(): void
    {
        $this->seedTicker(7, 'RMKE');
        $this->seedBars(7, [['2025-10-03',1810,1855],['2025-10-06',377,462],['2025-10-07',490,502]]);
        $dry = (new PriceScaleBreakDetectionService())->detect(null,null,null,false);
        $this->assertCount(1, $dry['detected']);
        $this->assertSame(0, DB::table('md_price_scale_break_candidates')->count());
        $this->detect(); $this->detect();
        $this->assertSame(1, DB::table('md_price_scale_break_candidates')->count());
    }
}
