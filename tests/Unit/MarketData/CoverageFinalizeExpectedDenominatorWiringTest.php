<?php

class CoverageFinalizeExpectedDenominatorWiringTest extends TestCase
{
    public function test_finalize_consumes_the_measured_expected_denominator_not_the_raw_universe(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 3).'/app/Application/MarketData/Services/MarketDataPipelineService.php'
        );

        $this->assertStringContainsString(
            "'expected_universe_count' => \$run->coverage_expected_count",
            $source
        );
        $this->assertStringNotContainsString(
            "'expected_universe_count' => \$run->coverage_universe_count",
            $source,
            'verified BAR_NOT_EXPECTED rows belong outside the expected denominator at finalize'
        );
    }
}
