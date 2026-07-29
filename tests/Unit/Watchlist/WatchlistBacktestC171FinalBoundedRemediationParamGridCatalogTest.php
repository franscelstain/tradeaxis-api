<?php

use App\Application\Watchlist\Services\WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog;
use App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService;
use App\Application\Watchlist\Services\WeeklySwingC171FinalBoundedRemediationDraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;

class WatchlistBacktestC171FinalBoundedRemediationParamGridCatalogTest extends TestCase
{
    public function testCatalogIsExactlyThreeFinalBoundedCandidatesWithClosureRule(): void
    {
        $rows = WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_FINAL_BOUNDED_REMEDIATION_C01_2026_07', WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE);
        $this->assertSame('FINAL-C01', WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_VERSION);
        $this->assertSame('5bc6dce5a8a96665435bdae8a30857bc75b108b0', WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::hash());
        $this->assertCount(3, $rows);
        $this->assertSame([
            'C171_FINAL_A_RISK_FORWARD_INTERPOLATED',
            'C171_FINAL_B_RISK_FORWARD_ATR_055',
            'C171_FINAL_C_RISK_FORWARD_STOP_125',
        ], array_column($rows, 'row_code'));
        $this->assertSame([null, null, null], array_column($rows, 'max_signal_tick_risk_expansion_pct'));
        $this->assertFalse(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::provenance()['further_remediation_after_this_catalog_allowed']);
        foreach ($rows as $row) {
            $this->assertSame(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertEqualsWithDelta(1.0, $row['w_momentum'] + $row['w_volume'] + $row['w_breakout'] + $row['w_risk'], 0.000001);
        }
    }

    public function testCandidatesAreValidUniqueAndDerivedWithoutTickRiskBlacklistOrGateWeakening(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $validator = new WeeklySwingParamsetValidator();
        $sourceValidation = $validator->validate($source);
        $service = new WeeklySwingC171FinalBoundedRemediationDraftCatalogService();
        $rows = WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows();
        $hashes = $service->deriveExpectedCandidateHashes($sourceValidation['canonical_payload'], $rows);

        $this->assertTrue($sourceValidation['valid']);
        $this->assertCount(3, $hashes);
        $this->assertCount(3, array_unique(array_values($hashes)));

        $a = $validator->validate($service->buildCandidatePayload($sourceValidation['canonical_payload'], $rows[0]));
        $b = $validator->validate($service->buildCandidatePayload($sourceValidation['canonical_payload'], $rows[1]));
        $c = $validator->validate($service->buildCandidatePayload($sourceValidation['canonical_payload'], $rows[2]));
        foreach ([$a, $b, $c] as $validation) {
            $this->assertTrue($validation['valid'], json_encode($validation['errors']));
            $this->assertArrayNotHasKey('max_signal_tick_risk_expansion_pct', $validation['canonical_payload']['risk']);
            $this->assertSame(-0.03, $validation['canonical_payload']['eval']['min_p25_ret_net_top']['value']);
            $this->assertSame(0.45, $validation['canonical_payload']['eval']['min_month_win_rate_min']['value']);
            $this->assertSame(-0.01, $validation['canonical_payload']['eval']['min_month_avg_ret_net_min']['value']);
        }
        $this->assertSame(0.35, $a['canonical_payload']['scoring']['weights']['value']['risk']);
        $this->assertSame(0.055, $b['canonical_payload']['risk']['max_atr14_pct']['value']);
        $this->assertSame(1.25, $c['canonical_payload']['risk']['stop_atr_mult']['value']);
    }

    public function testFinalDecisionArtifactHasStableIdentityAndNoPassClosure(): void
    {
        $path = base_path('docs/watchlist/audit/_artifacts/c171-final-bounded-remediation-catalog-decision.json');
        $artifact = json_decode((string) file_get_contents($path), true);
        $expected = $artifact['artifact_hash'];
        unset($artifact['artifact_hash']);

        $this->assertSame('90b18e7b93f497d7e193c52e18d2cad539015280', sha1_file($path));
        $this->assertSame('81dbe104197cd2e12abb692e1e66668ff94b9725', $expected);
        $this->assertSame($expected, (new WeeklySwingBacktestEvidenceIdentityService())->stableHash($artifact));
        $this->assertSame('C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION', $artifact['closure_rule']['if_no_candidate_passes_all_canonical_is_gates']);
        $this->assertFalse($artifact['closure_rule']['additional_c171_candidate_catalog_allowed']);
    }
}
