<?php

use App\Infrastructure\Persistence\MarketData\PriceScaleBreakRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * Contamination is anchored on the date the price series actually broke.
 *
 * A corporate action says why a scale changed and supplies the ratio. It does not reliably say
 * when: IDX actions are recorded against an announcement or record date, and `ex_date` is null
 * across the whole recorded set. The price series is the only thing that knows the day the scale
 * moved.
 *
 * The contamination resolver used to skip any break a corporate action explained, on the
 * assumption that the corporate-action path would cover it. It did not, and the gap was not
 * theoretical:
 *
 *   MLPT split 25:1. The detector found it on 2026-07-15 — previous close 18,725, open 756,
 *   inferred ratio 25 — and marked it EXPLAINED against a STOCK_SPLIT recorded as 2026-07-21.
 *   The quarantine followed the recorded date, so 21–28 July were quarantined and 15–20 July
 *   were not. Those four days carried roc20 = -95.7%, ma20 = 17,197 against a stock trading at
 *   826, and is_valid = 1.
 *
 * Across the fourteen detected-and-explained breaks the same shape left 401 indicator rows wrong
 * and marked valid.
 */
class ContaminationAnchoredOnBreakDateTest extends TestCase
{
    use UsesMarketDataSqlite;

    private const TICKER_ID = 564;

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
     * @return string[] a run of trading dates ending on the requested one
     */
    private function tradingDates(): array
    {
        return [
            '2026-07-08', '2026-07-09', '2026-07-10', '2026-07-13', '2026-07-14',
            '2026-07-15', '2026-07-16', '2026-07-17', '2026-07-20', '2026-07-21',
        ];
    }

    private function seedBreak(array $override = []): void
    {
        DB::table('market_data_price_scale_breaks')->insert(array_merge([
            'ticker_id' => self::TICKER_ID,
            'ticker_code' => 'MLPT',
            'trade_date' => '2026-07-15',
            'previous_close' => '18725.0000',
            'open_price' => '756.0000',
            'implied_ratio' => '24.7685185185',
            'ratio_direction' => 'PRICE_DECREASED',
            'inferred_ratio' => '25.0000',
            'inferred_ratio_error_pct' => '0.926000',
            'break_type' => 'SCALE_SHIFT',
            'match_status' => 'UNEXPLAINED',
            'matched_corporate_action_id' => null,
            'matched_action_type' => null,
            'review_status' => 'DETECTED',
            'detection_contract_version' => 'price_scale_break_v1',
            'detected_at' => '2026-07-31 09:00:00',
            'updated_at' => '2026-07-31 09:00:00',
        ], $override));
    }

    private function seedCorporateAction(array $override = []): int
    {
        $id = 4001;

        DB::table('market_data_corporate_actions')->insert(array_merge([
            'corporate_action_id' => $id,
            'ticker_id' => self::TICKER_ID,
            'ticker_code' => 'MLPT',
            // Four trading days after the series actually broke, as recorded.
            'action_date' => '2026-07-21',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_corporate_action',
            'price_adjustment_factor' => null,
            'continuity_check_status' => 'GAP_AMBIGUOUS',
            'observed_gap_pct' => '10.770000',
            'created_at' => '2026-07-31 09:00:00',
            'updated_at' => '2026-07-31 09:00:00',
        ], $override));

        return $id;
    }

    private function contamination(): array
    {
        return (new PriceScaleBreakRepository())
            ->resolveContaminationForTickerIds([self::TICKER_ID], $this->tradingDates());
    }

    /**
     * The case that was silently missed: a break a corporate action explains still contaminates.
     */
    public function test_an_explained_break_still_contaminates(): void
    {
        $actionId = $this->seedCorporateAction();
        $this->seedBreak([
            'match_status' => 'EXPLAINED',
            'matched_corporate_action_id' => $actionId,
            'matched_action_type' => 'STOCK_SPLIT',
        ]);

        $contamination = $this->contamination();

        $this->assertArrayHasKey(self::TICKER_ID, $contamination, 'An explained scale break must still contaminate.');
        $this->assertCount(1, $contamination[self::TICKER_ID]);
    }

    /**
     * And it is anchored on the break date, not the recorded action date. This is the whole
     * point: a window anchored four days late quarantines days that are fine and leaves the days
     * that are wrong.
     */
    public function test_the_anchor_is_the_break_date_not_the_recorded_action_date(): void
    {
        $actionId = $this->seedCorporateAction();
        $this->seedBreak([
            'match_status' => 'EXPLAINED',
            'matched_corporate_action_id' => $actionId,
            'matched_action_type' => 'STOCK_SPLIT',
        ]);

        $entry = $this->contamination()[self::TICKER_ID][0];

        $this->assertSame('2026-07-15', $entry['trade_date']);
        $this->assertNotSame('2026-07-21', $entry['trade_date']);
    }

    /**
     * Depth is measured back from the requested date, so a break five sessions ago is five deep.
     * The horizon rules use this to decide which indicators the break still reaches.
     */
    public function test_depth_is_measured_from_the_requested_date(): void
    {
        $this->seedBreak();

        // 2026-07-15 is the sixth of ten dates, so four sessions sit after it.
        $this->assertSame(4, $this->contamination()[self::TICKER_ID][0]['depth']);
    }

    /**
     * An unexplained break behaves exactly as before. Widening the resolver must not have
     * narrowed anything.
     */
    public function test_an_unexplained_break_still_contaminates(): void
    {
        $this->seedBreak();

        $this->assertArrayHasKey(self::TICKER_ID, $this->contamination());
    }

    /**
     * A usable factor rescales the window in memory, so the series the calculation sees is
     * continuous and there is nothing left to quarantine.
     */
    public function test_a_break_with_a_usable_adjustment_factor_is_not_quarantined(): void
    {
        $actionId = $this->seedCorporateAction(['price_adjustment_factor' => '25.0000000000']);
        $this->seedBreak([
            'match_status' => 'EXPLAINED',
            'matched_corporate_action_id' => $actionId,
            'matched_action_type' => 'STOCK_SPLIT',
        ]);

        $this->assertSame([], $this->contamination(), 'An adjusted window is already continuous.');
    }

    /**
     * A factor of exactly 1.0 is a recorded action that leaves the scale alone. It cannot excuse
     * a break the series demonstrably shows.
     */
    public function test_a_neutral_factor_does_not_excuse_a_detected_break(): void
    {
        $actionId = $this->seedCorporateAction(['price_adjustment_factor' => '1.0000000000']);
        $this->seedBreak([
            'match_status' => 'EXPLAINED',
            'matched_corporate_action_id' => $actionId,
            'matched_action_type' => 'STOCK_SPLIT',
        ]);

        $this->assertArrayHasKey(self::TICKER_ID, $this->contamination());
    }

    /**
     * A repaired break means the stored bars were rewritten onto one scale, so the window is
     * continuous in storage and must not be quarantined again.
     *
     * @dataProvider settledReviewStatuses
     */
    public function test_a_settled_break_no_longer_quarantines(string $reviewStatus): void
    {
        $this->seedBreak(['review_status' => $reviewStatus]);

        $this->assertSame([], $this->contamination());
    }

    public function settledReviewStatuses(): array
    {
        return [
            'repaired' => ['REPAIRED'],
            'dismissed' => ['DISMISSED'],
        ];
    }

    /**
     * The resolver carries the explanation through so the quarantine token can name it. A token
     * reading STOCK_SPLIT@2026-07-15 tells an operator what happened and when; one reading
     * PRICE_SCALE_SCALE_SHIFT@2026-07-15 only says the price moved.
     */
    public function test_the_explanation_is_carried_through_for_the_quarantine_token(): void
    {
        $actionId = $this->seedCorporateAction();
        $this->seedBreak([
            'match_status' => 'EXPLAINED',
            'matched_corporate_action_id' => $actionId,
            'matched_action_type' => 'STOCK_SPLIT',
        ]);

        $entry = $this->contamination()[self::TICKER_ID][0];

        $this->assertSame('EXPLAINED', $entry['match_status']);
        $this->assertSame('STOCK_SPLIT', $entry['matched_action_type']);
    }

    /**
     * A break outside the loaded window is not this window's problem.
     */
    public function test_a_break_outside_the_window_is_ignored(): void
    {
        $this->seedBreak(['trade_date' => '2026-06-01']);

        $this->assertSame([], $this->contamination());
    }
}
