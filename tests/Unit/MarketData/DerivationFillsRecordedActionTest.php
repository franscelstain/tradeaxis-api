<?php

use App\Application\MarketData\Services\CorporateActionDerivationService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * A recorded corporate action without a ratio adjusts nothing.
 *
 * Derivation used to select only UNEXPLAINED breaks, on the reasoning that an explained break was
 * already handled by the action explaining it. An action says why the scale moved. It does not
 * necessarily carry the ratio, and without a ratio the series stays exactly as unadjusted as if
 * nothing had been recorded at all.
 *
 * Two breaks sat in that state. FISH split 10:1 on 2025-09-01 against a STOCK_SPLIT recorded as
 * 2025-09-09 whose continuity check — measuring the recorded date — found no gap and wrote
 * NO_MATERIAL_GAP across a tenfold change. MLPT split 25:1 on 2026-07-15 against a STOCK_SPLIT
 * recorded as 2026-07-21 and marked GAP_AMBIGUOUS. Neither carried a factor; neither was derived
 * one; both were skipped for being explained.
 */
class DerivationFillsRecordedActionTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TICKER_ID = 700;
    private const ACTION_ID = 9100;
    private const BREAK_ID = 9200;

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

    /**
     * A 10:1 break: 9,000 closes into a 900 open.
     */
    private function seedBreak(array $override = []): void
    {
        DB::table('market_data_price_scale_breaks')->insert(array_merge([
            'price_scale_break_id' => self::BREAK_ID,
            'ticker_id' => self::TICKER_ID,
            'ticker_code' => 'FISH',
            'trade_date' => '2025-09-01',
            'previous_close' => '9000.0000',
            'open_price' => '900.0000',
            'implied_ratio' => '10.0000000000',
            'ratio_direction' => 'PRICE_DECREASED',
            'inferred_ratio' => '10.0000',
            'inferred_ratio_error_pct' => '0.000000',
            'break_type' => 'SCALE_SHIFT',
            'match_status' => 'EXPLAINED',
            'matched_corporate_action_id' => self::ACTION_ID,
            'matched_action_type' => 'STOCK_SPLIT',
            'review_status' => 'DETECTED',
            'detection_contract_version' => 'price_scale_break_v1',
            'detected_at' => '2026-07-31 09:00:00',
            'updated_at' => '2026-07-31 09:00:00',
        ], $override));
    }

    private function seedAction(array $override = []): void
    {
        DB::table('market_data_corporate_actions')->insert(array_merge([
            'corporate_action_id' => self::ACTION_ID,
            'ticker_id' => self::TICKER_ID,
            'ticker_code' => 'FISH',
            // Recorded a week after the series actually moved.
            'action_date' => '2025-09-09',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_corporate_action',
            'ex_date' => null,
            'price_adjustment_factor' => null,
            'volume_adjustment_factor' => null,
            'continuity_check_status' => 'NO_MATERIAL_GAP',
            'observed_gap_pct' => '0.400000',
            'created_at' => '2026-07-31 09:00:00',
            'updated_at' => '2026-07-31 09:00:00',
        ], $override));
    }

    private function derive($apply = false): array
    {
        return (new CorporateActionDerivationService())->derive($apply);
    }

    private function action()
    {
        return DB::table('market_data_corporate_actions')->where('corporate_action_id', self::ACTION_ID)->first();
    }

    public function test_an_explained_break_without_a_factor_is_derived(): void
    {
        $this->seedAction();
        $this->seedBreak();

        $result = $this->derive();

        $this->assertCount(1, $result['derived'], 'A break with no usable factor must be derived even when explained.');
        $this->assertSame('FISH', $result['derived'][0]['ticker_code']);
    }

    /**
     * The factor reconciles the prices the series actually shows, not the rounded inference.
     */
    public function test_the_factor_comes_from_the_observed_prices(): void
    {
        $this->seedAction();
        $this->seedBreak();

        $derived = $this->derive()['derived'][0];

        $this->assertSame(0.1, $derived['price_adjustment_factor']);
        $this->assertSame(10.0, $derived['volume_adjustment_factor']);
    }

    /**
     * The recorded action is filled in rather than duplicated, and it keeps its real type: a
     * STOCK_SPLIT is better information than the synthetic PRICE_RESCALE_UNCLASSIFIED.
     */
    public function test_the_recorded_action_is_filled_in_and_keeps_its_type(): void
    {
        $this->seedAction();
        $this->seedBreak();
        $this->derive(true);

        $action = $this->action();

        $this->assertSame('STOCK_SPLIT', $action->action_type);
        $this->assertSame(0.1, (float) $action->price_adjustment_factor);
        $this->assertSame(1, DB::table('market_data_corporate_actions')->count(), 'No second row for one event.');
    }

    /**
     * ex_date is written from the detected break, because that is the day the scale moved. The
     * adjustment resolver treats ex_date as authoritative, so a wrong one shifts the whole window.
     */
    public function test_the_ex_date_is_the_break_date_not_the_recorded_action_date(): void
    {
        $this->seedAction();
        $this->seedBreak();
        $this->derive(true);

        $action = $this->action();

        $this->assertSame('2025-09-01', substr((string) $action->ex_date, 0, 10));
        $this->assertSame('2025-09-09', substr((string) $action->action_date, 0, 10), 'The recorded date is preserved as recorded.');
    }

    /**
     * The old verdict was reached by measuring the wrong day. Leaving NO_MATERIAL_GAP in place
     * would keep asserting there is no discontinuity across a tenfold change.
     */
    public function test_a_verdict_reached_on_the_wrong_day_is_corrected(): void
    {
        $this->seedAction();
        $this->seedBreak();
        $this->derive(true);

        $action = $this->action();

        $this->assertSame('GAP_BEYOND_EXCHANGE_BAND', $action->continuity_check_status);
        $this->assertSame(90.0, (float) $action->observed_gap_pct);
        $this->assertSame('DERIVED_FROM_PRICE_SERIES', $action->adjustment_source);
    }

    /**
     * A break already covered by a usable factor is left alone. Deriving over it would overwrite
     * an operator-supplied ratio with an inferred one.
     */
    public function test_a_break_already_adjusted_on_its_break_date_is_skipped(): void
    {
        $this->seedAction(['ex_date' => '2025-09-01', 'price_adjustment_factor' => '0.1000000000']);
        $this->seedBreak();

        $result = $this->derive();

        $this->assertSame([], $result['derived']);
        $this->assertSame('already adjusted on the break date', $result['skipped'][0]['reason']);
    }

    /**
     * A factor of exactly 1.0 adjusts nothing, so it cannot count as coverage.
     */
    public function test_a_neutral_factor_does_not_count_as_adjusted(): void
    {
        $this->seedAction(['ex_date' => '2025-09-01', 'price_adjustment_factor' => '1.0000000000']);
        $this->seedBreak();

        $this->assertCount(1, $this->derive()['derived']);
    }

    /**
     * The exchange-band guard survives the widening. A move inside the auto-rejection band is a
     * move the market could have made, and inferring a corporate action from it would invent one.
     */
    public function test_a_move_inside_the_exchange_band_is_never_derived(): void
    {
        $this->seedAction();
        $this->seedBreak([
            'previous_close' => '1000.0000',
            'open_price' => '750.0000',
            'implied_ratio' => '1.3333333333',
            'inferred_ratio' => null,
        ]);

        $result = $this->derive();

        $this->assertSame([], $result['derived']);
        $this->assertSame('within exchange session limit, could be a real move', $result['skipped'][0]['reason']);
    }

    /**
     * An unexplained break still creates its own record, as before. Widening the selection must
     * not have narrowed anything.
     */
    public function test_an_unexplained_break_still_creates_a_synthetic_action(): void
    {
        $this->seedBreak([
            'match_status' => 'UNEXPLAINED',
            'matched_corporate_action_id' => null,
            'matched_action_type' => null,
        ]);

        $this->derive(true);

        $created = DB::table('market_data_corporate_actions')->first();

        $this->assertNotNull($created);
        $this->assertSame('PRICE_RESCALE_UNCLASSIFIED', $created->action_type);
        $this->assertSame('2025-09-01', substr((string) $created->ex_date, 0, 10));
    }

    /**
     * A repaired break has had its stored bars rewritten onto one scale, so deriving a factor
     * would rescale an already-continuous series a second time.
     */
    public function test_a_repaired_break_is_not_derived(): void
    {
        $this->seedAction();
        $this->seedBreak(['review_status' => 'REPAIRED']);

        $this->assertSame([], $this->derive()['derived']);
    }
}
