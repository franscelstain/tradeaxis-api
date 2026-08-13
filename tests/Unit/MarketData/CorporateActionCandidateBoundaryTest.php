<?php

use App\Infrastructure\Persistence\MarketData\EventRiskSourceRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesMarketDataSqlite;

/**
 * W11 — corporate-action event and factor lifecycle, stage 10.
 *
 * Exit gate: "price jump/proximity/provider adjusted field tidak dapat membuat verified
 * action/factor atau mengubah history. Tidak ada keputusan yang mencapai published output memakai
 * band/floor/tick tanpa sumber dan effective date."
 *
 * Owner contracts:
 *   docs/market_data/book/Corporate_Action_and_Adjustment_Policy.md
 *   docs/market_data/book/Corporate_Action_Impact_Flags_Contract.md
 *   docs/market_data/registry/Corporate_Action_Type_Registry_LOCKED.md
 *   docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 *   docs/market_data/registry/Exchange_Market_Structure_Facts_LOCKED.md
 *
 * A price discontinuity proves that something happened. It cannot establish what, on which terms,
 * or effective when. Using the ratio implied by the gap as an adjustment factor closes the loop on
 * the platform's own guess: the series is smoothed with a number derived from the very gap being
 * explained, and the output becomes indistinguishable from a sourced adjustment.
 */
class CorporateActionCandidateBoundaryTest extends TestCase
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

    private function action(array $override = []): void
    {
        DB::table('market_data_corporate_actions')->insert(array_merge([
            'ticker_id' => 10,
            'ticker_code' => 'TMAS',
            'action_date' => '2026-03-24',
            'ex_date' => '2026-03-24',
            'action_type' => 'STOCK_SPLIT',
            'source_name' => 'idx_corporate_action',
            'price_adjustment_factor' => 0.25,
            'volume_adjustment_factor' => 4,
            // The fixture always meant a source-backed factor — its source_name is an IDX feed and
            // the test that uses it is named for exactly that. It could not say so until
            // adjustment_source had a declared vocabulary, so it defaulted to NULL and the rule
            // "only an attributed factor adjusts" had nothing to bite on.
            'adjustment_source' => 'EXCHANGE_ANNOUNCEMENT',
            'created_at' => '2026-03-20 09:00:00',
        ], $override));
    }

    private function factorsFor(array $tickerIds, $windowStart, $windowEnd): array
    {
        return (new EventRiskSourceRepository())
            ->resolveAdjustmentFactorsForTickerIds($tickerIds, $windowStart, $windowEnd);
    }

    /**
     * A factor the platform inferred from its own price series must not adjust published output.
     * Production carries 15 such rows, every one typed as a corporate action and carrying a usable
     * factor, in a table with no verification-state column to tell them apart.
     */
    public function test_a_price_derived_factor_never_reaches_the_adjustment_path(): void
    {
        $this->action(['adjustment_source' => 'DERIVED_FROM_PRICE_SERIES']);

        $factors = $this->factorsFor([10], '2026-03-23', '2026-03-25');

        $this->assertArrayNotHasKey(10, $factors, 'an inferred ratio is candidate evidence, not an adjustment');
    }

    /**
     * A source-backed factor still adjusts. Without this the guard would be indistinguishable from
     * a platform that cannot adjust for corporate actions at all.
     */
    public function test_a_source_backed_factor_still_adjusts(): void
    {
        $this->action();

        $factors = $this->factorsFor([10], '2026-03-23', '2026-03-25');

        $this->assertArrayHasKey(10, $factors);
        $this->assertEqualsWithDelta(0.25, $factors[10][0]['price_factor'], 1e-9);
    }

    /**
     * A factor nobody attributed does not adjust either.
     *
     * The rule used to exclude one known-bad source and admit everything else, so a row with
     * adjustment_source NULL — or any value nobody declared — rescaled published prices while the
     * platform could not say where the number came from. The allowlist is positive now, and the
     * two cases below are the ones that used to slip through.
     */
    public function test_an_unattributed_factor_does_not_adjust(): void
    {
        foreach ([null, 'SOME_VENDOR_FEED'] as $unattributed) {
            DB::table('market_data_corporate_actions')->delete();
            $this->action(['adjustment_source' => $unattributed]);

            $this->assertSame(
                [],
                $this->factorsFor([10], '2026-03-23', '2026-03-25'),
                'a factor with adjustment_source '.var_export($unattributed, true).' must not adjust'
            );

            $this->assertArrayHasKey(
                10,
                (new EventRiskSourceRepository())->resolveCorporateActionContaminationForTickerIds(
                    [10],
                    ['2026-03-23', '2026-03-24', '2026-03-25']
                ),
                'and refusing it must leave the window quarantined rather than silently clean'
            );
        }
    }

    /**
     * Refusing the factor is only half the rule. If the row still counted as adjustable it would
     * suppress the contamination flag, and the window would be published as clean while carrying
     * an unexplained discontinuity — worse than either adjusting or quarantining honestly.
     */
    public function test_a_price_derived_action_does_not_suppress_contamination(): void
    {
        $this->action(['adjustment_source' => 'DERIVED_FROM_PRICE_SERIES']);

        $repository = new EventRiskSourceRepository();
        $method = new ReflectionMethod($repository, 'isAdjustable');
        $method->setAccessible(true);

        $row = DB::table('market_data_corporate_actions')->first();

        $this->assertFalse($method->invoke($repository, $row), 'a derived row must fall through to quarantine');
    }

    /**
     * A factor of exactly 1 adjusts nothing, so it must not be treated as an adjustment that
     * resolves a discontinuity either.
     */
    public function test_a_neutral_factor_is_not_treated_as_an_adjustment(): void
    {
        $this->action(['price_adjustment_factor' => 1]);

        $repository = new EventRiskSourceRepository();
        $method = new ReflectionMethod($repository, 'isAdjustable');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($repository, DB::table('market_data_corporate_actions')->first()));
    }

    /**
     * The derivation writer stays retired. Its dry-run and apply paths both report detection-only
     * and perform no mutation, so no new inferred event can be created regardless of flags.
     */
    public function test_the_derivation_service_cannot_create_an_event_or_factor(): void
    {
        $service = new App\Application\MarketData\Services\CorporateActionDerivationService();

        $derived = $service->derive(true);
        $checked = $service->checkRecordedActions(true);

        $this->assertSame([], $derived['derived']);
        $this->assertSame('DETECTION_ONLY', $derived['capability_state']);
        $this->assertFalse($derived['mutation_performed']);
        $this->assertFalse($checked['mutation_performed']);
        $this->assertTrue($checked['apply_requested'], 'apply was genuinely requested and still refused');
    }

    /**
     * An unmapped action type is not in the dictionary, and an event carrying one must be treated
     * as contaminating rather than harmless. `PRICE_RESCALE_UNCLASSIFIED` is deliberately absent
     * from the seeded dictionary — seeding it would let a price-derived rescale authorize its own
     * adjustment, which is the same loop this work order closes on the factor side.
     */
    public function test_an_unmapped_action_type_is_absent_rather_than_silently_safe(): void
    {
        $types = (new EventRiskSourceRepository())->corporateActionTypes();

        $this->assertArrayNotHasKey('SOME_UNKNOWN_ACTION', $types);
        $this->assertArrayNotHasKey('PRICE_RESCALE_UNCLASSIFIED', $types);
        $this->assertSame('SCALED', $types['STOCK_SPLIT']['price_continuity_impact']);
        $this->assertSame('NONE', $types['IPO']['price_continuity_impact']);
    }
}
