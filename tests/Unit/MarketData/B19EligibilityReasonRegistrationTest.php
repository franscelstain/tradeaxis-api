<?php

use App\Application\MarketData\Services\EligibilityDecisionService;

/**
 * `MD-B19` — blocked rows of `eligibility_export.csv` carry REGISTERED reason codes (`MD-S075-R0144`).
 *
 * The export writes the reasons the pipeline persisted, so the contract can only hold if everything the pipeline can
 * persist for a blocked row is in the reason-code registry. The registry is read from its seed
 * (`db/registry/Reason_Codes_Seed.sql`), not from the code under test:
 *
 *  - every blocking reason `EligibilityDecisionService::decide()` returns, on every branch, is a registered, active code;
 *  - every `ELIG_*` literal the two producers contain (the decision service and the eligibility build service, which
 *    leads the set with `ELIG_TRADING_SUSPENDED`) is a registered, active code, so a code added to a producer without
 *    registering it fails here.
 *
 * `B19EligibilityExportRealRunProvenanceTest` proves the same on the blocked row of a real run, against the registry table.
 */
class B19EligibilityReasonRegistrationTest extends TestCase
{
    private function root(): string
    {
        return dirname(__DIR__, 3);
    }

    /** @return array<string,bool> code => is_active, parsed from the seed file */
    private function registry(): array
    {
        $sql = (string) file_get_contents($this->root().'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');
        preg_match_all("/^\('([A-Z0-9_]+)', '[A-Z_]+', '(?:[^'\\\\]|\\\\.|'')*', '(?:INFO|WARN|HARD)', ([01])\)/m", $sql, $m, PREG_SET_ORDER);
        $out = [];
        foreach ($m as $row) {
            $out[$row[1]] = $row[2] === '1';
        }

        return $out;
    }

    public function test_the_seed_parser_finds_the_eligibility_codes(): void
    {
        $registry = $this->registry();
        $this->assertGreaterThan(100, count($registry));
        foreach (['ELIG_MISSING_BAR', 'ELIG_MISSING_INDICATORS', 'ELIG_TRADING_SUSPENDED'] as $code) {
            $this->assertArrayHasKey($code, $registry);
        }
    }

    /** @return array<string,array{0:?array,1:?array}> */
    public static function decisionBranches(): array
    {
        return [
            'no bar' => [null, null],
            'bar, no indicators' => [['close' => 1], null],
            'invalid indicators, unmapped reason' => [['close' => 1], ['is_valid' => 0, 'invalid_reason_code' => 'IND_SOMETHING_ELSE']],
            'invalid, insufficient history' => [['close' => 1], ['is_valid' => 0, 'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY']],
            'invalid, corporate action discontinuity' => [['close' => 1], ['is_valid' => 0, 'invalid_reason_code' => 'IND_CORPORATE_ACTION_DISCONTINUITY']],
            'invalid, price scale discontinuity' => [['close' => 1], ['is_valid' => 0, 'invalid_reason_code' => 'IND_PRICE_SCALE_DISCONTINUITY']],
        ];
    }

    /**
     * @dataProvider decisionBranches
     */
    public function test_every_blocking_reason_the_decision_returns_is_registered_and_active(?array $bar, ?array $indicator): void
    {
        $decision = (new EligibilityDecisionService())->decide($bar, $indicator);
        $this->assertSame(0, $decision['eligible'], 'precondition: the branch blocks');
        $this->assertNotEmpty($decision['reason_code'], 'a blocked row has a reason');
        $registry = $this->registry();
        $this->assertArrayHasKey($decision['reason_code'], $registry, $decision['reason_code'].' is registered');
        $this->assertTrue($registry[$decision['reason_code']], $decision['reason_code'].' is active');
    }

    public function test_an_eligible_decision_carries_no_blocking_reason(): void
    {
        $decision = (new EligibilityDecisionService())->decide(['close' => 1], ['is_valid' => 1, 'invalid_reason_code' => null]);
        $this->assertSame(1, $decision['eligible']);
        $this->assertNull($decision['reason_code']);
    }

    public function test_every_eligibility_reason_literal_in_the_producers_is_registered_and_active(): void
    {
        $registry = $this->registry();
        $found = [];
        foreach (['EligibilityDecisionService.php', 'EodEligibilityBuildService.php'] as $file) {
            $src = (string) file_get_contents($this->root().'/app/Application/MarketData/Services/'.$file);
            preg_match_all("/'(ELIG_[A-Z_]+)'/", $src, $m);
            $found = array_merge($found, $m[1]);
        }
        $found = array_values(array_unique($found));
        $this->assertContains('ELIG_TRADING_SUSPENDED', $found, 'precondition: the scan reaches the build service');
        $this->assertContains('ELIG_MISSING_BAR', $found, 'precondition: the scan reaches the decision service');
        foreach ($found as $code) {
            $this->assertArrayHasKey($code, $registry, $code.' is produced but not registered');
            $this->assertTrue($registry[$code], $code.' is registered but inactive');
        }
    }
}
