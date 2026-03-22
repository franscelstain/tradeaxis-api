<?php

use App\Application\MarketData\Services\EligibilityDecisionService;
use PHPUnit\Framework\TestCase;

class EligibilityDecisionServiceTest extends TestCase
{
    public function test_missing_bar_blocks_with_missing_bar_reason()
    {
        $service = new EligibilityDecisionService();
        $decision = $service->decide(null, ['is_valid' => 1, 'invalid_reason_code' => null]);

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_MISSING_BAR', $decision['reason_code']);
    }

    public function test_missing_indicator_blocks_with_missing_indicator_reason()
    {
        $service = new EligibilityDecisionService();
        $decision = $service->decide(['ticker_id' => 101], null);

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_MISSING_INDICATORS', $decision['reason_code']);
    }

    public function test_invalid_indicator_maps_insufficient_history_to_specific_eligibility_reason()
    {
        $service = new EligibilityDecisionService();
        $decision = $service->decide(['ticker_id' => 101], ['is_valid' => 0, 'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY']);

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_INSUFFICIENT_HISTORY', $decision['reason_code']);
    }

    public function test_invalid_indicator_maps_other_invalid_reason_to_generic_block_reason()
    {
        $service = new EligibilityDecisionService();
        $decision = $service->decide(['ticker_id' => 101], ['is_valid' => 0, 'invalid_reason_code' => 'IND_MISSING_DEPENDENCY_BAR']);

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_INVALID_INDICATORS', $decision['reason_code']);
    }

    public function test_valid_bar_and_indicator_are_eligible()
    {
        $service = new EligibilityDecisionService();
        $decision = $service->decide(['ticker_id' => 101], ['is_valid' => 1, 'invalid_reason_code' => null]);

        $this->assertSame(1, $decision['eligible']);
        $this->assertNull($decision['reason_code']);
    }
}
