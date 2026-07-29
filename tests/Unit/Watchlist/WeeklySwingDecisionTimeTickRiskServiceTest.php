<?php

use App\Application\Watchlist\Services\WeeklySwingDecisionTimeTickRiskService;

class WeeklySwingDecisionTimeTickRiskServiceTest extends TestCase
{
    public function testSignalDateCloseProxyUsesTheCanonicalIdxStopFloorRule(): void
    {
        $result = (new WeeklySwingDecisionTimeTickRiskService())->calculate(60.0, 0.04, 1.5);

        $this->assertTrue($result['valid']);
        $this->assertSame(WeeklySwingDecisionTimeTickRiskService::CONTRACT, $result['contract']);
        $this->assertEqualsWithDelta(0.06, $result['theoretical_stop_risk_pct'], 0.0000001);
        $this->assertEqualsWithDelta(56.4, $result['theoretical_stop_price'], 0.0000001);
        $this->assertSame(56.0, $result['normalized_stop_trigger_price']);
        $this->assertEqualsWithDelta(0.0666666667, $result['normalized_stop_risk_pct'], 0.0000001);
        $this->assertEqualsWithDelta(0.0066666667, $result['signal_tick_risk_expansion_pct'], 0.0000001);
    }

    public function testInvalidDecisionTimeInputsFailClosedWithoutInventingRisk(): void
    {
        $service = new WeeklySwingDecisionTimeTickRiskService();
        foreach ([[null, 0.04, 1.5], [60.0, null, 1.5], [60.0, 0.04, 0.0], [1.0, 0.9, 1.5]] as $input) {
            $result = $service->calculate($input[0], $input[1], $input[2]);
            $this->assertFalse($result['valid']);
            $this->assertNull($result['signal_tick_risk_expansion_pct']);
        }
    }
}
