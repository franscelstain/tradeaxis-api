<?php

use App\Application\MarketData\Services\EligibilityDecisionService;

/**
 * W16 — explainable data usability, stage 13.
 *
 * Exit gate: "blocked row tidak hilang; true tidak berarti tradable/selected; liquidity/event
 * preference watchlist tidak masuk upstream decision."
 *
 * Owner contracts:
 *   docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md
 *   docs/market_data/book/Eligibility_Partial_Data_Behavior_LOCKED.md
 *   docs/market_data/book/Sector_Classification_Contract_LOCKED.md
 *
 * `eligible` means the data is usable, and nothing more. It is not a trading opinion, not a
 * liquidity screen, and not a selection. Letting a preference leak into it would make an upstream
 * fact carry a downstream judgement, and every consumer would inherit that judgement without
 * being able to see it or disagree with it.
 */
class EligibilityExplainabilityBoundaryTest extends TestCase
{
    private function decide($bar, $indicator): array
    {
        return (new EligibilityDecisionService())->decide($bar, $indicator);
    }

    private function bar(array $override = []): array
    {
        return array_merge([
            'ticker_id' => 1,
            'trade_date' => '2026-03-24',
            'close' => 108,
            'volume' => 1000,
            'quality_state' => 'VALIDATED',
        ], $override);
    }

    private function indicator(array $override = []): array
    {
        return array_merge([
            'is_valid' => 1,
            'invalid_reason_code' => null,
            'dv20_idr' => 100000,
        ], $override);
    }

    /**
     * A missing bar is a stated reason, not a dropped row. The decision names the absence so the
     * snapshot can carry it.
     */
    public function test_a_missing_bar_produces_a_blocked_decision_with_a_reason(): void
    {
        $decision = $this->decide(null, null);

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_MISSING_BAR', $decision['reason_code']);
    }

    /**
     * A present bar with missing indicators is blocked for its own distinct reason. Collapsing the
     * two would hide which half of the pipeline fell short.
     */
    public function test_missing_indicators_are_reported_separately_from_a_missing_bar(): void
    {
        $decision = $this->decide($this->bar(), null);

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_MISSING_INDICATORS', $decision['reason_code']);
    }

    /**
     * An invalid indicator carries its specific cause through to the eligibility reason rather
     * than flattening into a generic failure. An operator reading
     * ELIG_CORPORATE_ACTION_DISCONTINUITY knows what happened; ELIG_INVALID_INDICATORS does not.
     */
    public function test_an_invalid_indicator_carries_its_specific_cause(): void
    {
        $contaminated = $this->decide($this->bar(), $this->indicator([
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_CORPORATE_ACTION_DISCONTINUITY',
        ]));

        $scaleBreak = $this->decide($this->bar(), $this->indicator([
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_PRICE_SCALE_DISCONTINUITY',
        ]));

        $unmapped = $this->decide($this->bar(), $this->indicator([
            'is_valid' => 0,
            'invalid_reason_code' => 'IND_SOMETHING_NEW',
        ]));

        $this->assertSame('ELIG_CORPORATE_ACTION_DISCONTINUITY', $contaminated['reason_code']);
        $this->assertSame('ELIG_PRICE_SCALE_DISCONTINUITY', $scaleBreak['reason_code']);
        $this->assertSame('ELIG_INVALID_INDICATORS', $unmapped['reason_code'], 'an unmapped cause still blocks');
    }

    /**
     * An illiquid instrument with valid data is eligible. Liquidity is a preference belonging to
     * whoever selects instruments, not a fact about whether the data can be used — and a screen
     * that cannot see the illiquid rows cannot decide to include them.
     */
    public function test_low_liquidity_does_not_block_a_row_whose_data_is_usable(): void
    {
        $illiquid = $this->decide(
            $this->bar(['volume' => 1]),
            $this->indicator(['dv20_idr' => 1.0])
        );

        $this->assertSame(1, $illiquid['eligible'], 'thin trading is a preference, not a data defect');
        $this->assertNull($illiquid['reason_code']);
    }

    /**
     * Zero volume with an otherwise valid bar is still usable data. A session with no trades is a
     * fact about the market, not a fault in the record of it.
     */
    public function test_zero_volume_alone_does_not_block_a_row(): void
    {
        $decision = $this->decide($this->bar(['volume' => 0]), $this->indicator());

        $this->assertSame(1, $decision['eligible']);
    }

    /**
     * The decision reads only bar presence, indicator presence, and indicator validity. Asserting
     * the shape of the inputs it consults is what keeps a liquidity threshold or an event-risk
     * preference from being added later without anyone noticing.
     */
    public function test_the_decision_consults_no_preference_input(): void
    {
        $source = file_get_contents(
            __DIR__.'/../../../app/Application/MarketData/Services/EligibilityDecisionService.php'
        );

        foreach ([
            'dv20_idr',
            'adv20_close_volume_proxy_idr',
            'liquidity',
            'watchlist',
            'min_turnover',
            'event_risk_flag',
        ] as $preference) {
            $this->assertStringNotContainsString(
                $preference,
                $source,
                'the upstream data-usability decision must not consult '.$preference
            );
        }
    }

    /**
     * Eligible is the absence of a data defect, not an endorsement. It carries no reason precisely
     * because there is nothing blocking it — the row is usable, and what to do with it is somebody
     * else's decision.
     */
    public function test_eligible_means_usable_data_and_carries_no_selection_verdict(): void
    {
        $decision = $this->decide($this->bar(), $this->indicator());

        $this->assertSame(1, $decision['eligible']);
        $this->assertArrayNotHasKey('tradable', $decision);
        $this->assertArrayNotHasKey('selected', $decision);
        $this->assertSame(['eligible', 'reason_code'], array_keys($decision));
    }
}
