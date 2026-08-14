<?php

use App\Application\MarketData\Services\CorporateActionDerivationService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/** Supersedes the retired price-derived corporate-action contract. */
class DerivationFillsRecordedActionTest extends TestCase
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

    private function seedBreak($matchStatus = 'UNEXPLAINED'): void
    {
        DB::table('market_data_price_scale_breaks')->insert([
            'price_scale_break_id' => 9200,
            'ticker_id' => 700,
            'ticker_code' => 'FISH',
            'trade_date' => '2025-09-01',
            'previous_close' => 9000,
            'open_price' => 900,
            'implied_ratio' => 10,
            'ratio_direction' => 'PRICE_DECREASED',
            'inferred_ratio' => 10,
            'inferred_ratio_error_pct' => 0,
            'break_type' => 'SCALE_SHIFT',
            'match_status' => $matchStatus,
            'matched_corporate_action_id' => $matchStatus === 'EXPLAINED' ? 9100 : null,
            'matched_action_type' => $matchStatus === 'EXPLAINED' ? 'STOCK_SPLIT' : null,
            'review_status' => 'DETECTED',
            'detection_contract_version' => 'price_scale_break_v1',
            'detected_at' => '2026-07-31 09:00:00',
            'updated_at' => '2026-07-31 09:00:00',
        ]);
    }

    private function seedAction(): void
    {
        DB::table('market_data_corporate_actions')->insert([
            'corporate_action_id' => 9100,
            'ticker_id' => 700,
            'ticker_code' => 'FISH',
            'action_date' => '2025-09-09',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_corporate_action',
            'continuity_check_status' => 'NO_MATERIAL_GAP',
            'created_at' => '2026-07-31 09:00:00',
            'updated_at' => '2026-07-31 09:00:00',
        ]);
    }

    public function test_unexplained_price_break_never_creates_a_synthetic_action(): void
    {
        $this->seedBreak();
        $result = (new CorporateActionDerivationService())->derive(true);

        $this->assertSame([], $result['derived']);
        $this->assertSame('DETECTION_ONLY', $result['capability_state']);
        $this->assertSame('CORPORATE_ACTION_AUTHORITATIVE_EVIDENCE_REQUIRED', $result['skipped'][0]['reason_code']);
        $this->assertSame(0, DB::table('market_data_corporate_actions')->count());
    }

    public function test_explained_action_without_terms_is_not_filled_from_price_geometry(): void
    {
        $this->seedAction();
        $this->seedBreak('EXPLAINED');
        (new CorporateActionDerivationService())->derive(true);
        $action = DB::table('market_data_corporate_actions')->where('corporate_action_id', 9100)->first();

        $this->assertNull($action->ex_date);
        $this->assertNull($action->price_adjustment_factor);
        $this->assertSame('NO_MATERIAL_GAP', $action->continuity_check_status);
        $this->assertSame(1, DB::table('market_data_corporate_actions')->count());
    }

    public function test_apply_flag_cannot_enable_mutation(): void
    {
        $this->seedBreak();
        $before = (array) DB::table('market_data_price_scale_breaks')->first();
        $result = (new CorporateActionDerivationService())->derive(true);
        $after = (array) DB::table('market_data_price_scale_breaks')->first();

        $this->assertFalse($result['mutation_performed']);
        $this->assertSame($before, $after);
    }
}
