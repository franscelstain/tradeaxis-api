<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService;
use App\Application\Watchlist\Services\WeeklySwingC171FinalFailedNotReadyClosureService;
use TestCase;

class WeeklySwingC171FinalFailedNotReadyClosureTest extends TestCase
{
    public function testFinalClosureDecisionKeepsAnchorAndAllowsNoFurtherRemediation(): void
    {
        $service = new WeeklySwingC171FinalFailedNotReadyClosureService();
        $anchor = [
            'param_set_id' => 11,
            'eval_id' => 204,
            'metrics' => [
                'avg_ret_net_top' => 0.011903278369749106,
                'win_rate_top' => 0.4648318042813456,
                'median_ret_net_top' => -0.0005006759124818505,
                'p25_ret_net_top' => -0.06309412630637606,
                'month_win_rate_min' => 0.27586206896551724,
                'month_avg_ret_net_min' => -0.016505902083925335,
                'period_fail_count' => 12,
            ],
        ];
        $finals = [
            ['param_set_id' => 12, 'eval_id' => 205, 'canonical_is_gates_pass' => false, 'metrics' => [
                'avg_ret_net_top' => 0.01145464438312777, 'win_rate_top' => 0.46178092986603625,
                'median_ret_net_top' => -0.0005011275369581559, 'p25_ret_net_top' => -0.0625970253893535,
                'month_win_rate_min' => 0.26666666666666666, 'month_avg_ret_net_min' => -0.016955608051394025,
                'period_fail_count' => 14,
            ]],
            ['param_set_id' => 13, 'eval_id' => 206, 'canonical_is_gates_pass' => false, 'metrics' => [
                'avg_ret_net_top' => 0.009389918348920764, 'win_rate_top' => 0.45433436532507737,
                'median_ret_net_top' => -0.0005015799971175188, 'p25_ret_net_top' => -0.061090836374543815,
                'month_win_rate_min' => 0.17857142857142858, 'month_avg_ret_net_min' => -0.029277972698896083,
                'period_fail_count' => 15,
            ]],
            ['param_set_id' => 14, 'eval_id' => 207, 'canonical_is_gates_pass' => false, 'metrics' => [
                'avg_ret_net_top' => 0.009125925123639177, 'win_rate_top' => 0.45760122230710465,
                'median_ret_net_top' => -0.00458068710306546, 'p25_ret_net_top' => -0.05722343340600436,
                'month_win_rate_min' => 0.2542372881355932, 'month_avg_ret_net_min' => -0.019596458044718254,
                'period_fail_count' => 15,
            ]],
        ];

        $decision = $service->buildClosureDecision($anchor, $finals);

        $this->assertTrue($decision['closure_allowed']);
        $this->assertTrue($decision['anchor_remains_best_overall']);
        $this->assertSame(0, $decision['passing_candidate_count']);
        $this->assertSame([], $decision['passing_param_set_ids']);
        $this->assertSame('C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION', $decision['final_decision']);
        $this->assertSame(3, count($decision['candidate_deltas_vs_anchor']));
    }

    public function testClosureDecisionArtifactIdentityIsExact(): void
    {
        $path = base_path('docs/watchlist/audit/_artifacts/c171-final-failed-not-ready-closure-decision.json');
        $artifact = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($artifact);
        $this->assertSame(WeeklySwingC171FinalFailedNotReadyClosureService::DECISION_FILE_SHA1, sha1_file($path));
        $hash = $artifact['artifact_hash'];
        unset($artifact['artifact_hash']);
        $this->assertSame(WeeklySwingC171FinalFailedNotReadyClosureService::DECISION_ARTIFACT_HASH, $hash);
        $this->assertSame($hash, (new WeeklySwingBacktestEvidenceIdentityService())->stableHash($artifact));
        $this->assertSame(0, $artifact['final_passing_candidate_count']);
        $this->assertFalse($artifact['additional_c171_candidate_catalog_allowed']);
        $this->assertFalse($artifact['oos_allowed']);
        $this->assertFalse($artifact['c172_allowed']);
    }

    public function testClosureDecisionFailsClosedWhenAnyFinalCandidatePasses(): void
    {
        $decision = (new WeeklySwingC171FinalFailedNotReadyClosureService())->buildClosureDecision(
            ['param_set_id' => 11, 'eval_id' => 204, 'metrics' => ['avg_ret_net_top' => 0.01, 'win_rate_top' => 0.45, 'period_fail_count' => 12]],
            [
                ['param_set_id' => 12, 'eval_id' => 205, 'canonical_is_gates_pass' => true, 'metrics' => ['avg_ret_net_top' => 0.02, 'win_rate_top' => 0.50, 'period_fail_count' => 8]],
                ['param_set_id' => 13, 'eval_id' => 206, 'canonical_is_gates_pass' => false, 'metrics' => []],
                ['param_set_id' => 14, 'eval_id' => 207, 'canonical_is_gates_pass' => false, 'metrics' => []],
            ]
        );

        $this->assertFalse($decision['closure_allowed']);
        $this->assertSame(1, $decision['passing_candidate_count']);
        $this->assertSame('C171_FINAL_REVIEW_REQUIRED_BEFORE_C172', $decision['final_decision']);
    }
    public function testPowerShellExportCsvUtf8BomIsRemovedBeforeHeaderParsing(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'c171-summary-bom-');
        $this->assertNotFalse($path);
        $csv = "\xEF\xBB\xBF"
            . '"param_set_id","eval_id","params_hash","canonical_is_gates_pass","pipeline_version","pipeline_hash","artifact_hash","file_sha1"' . "\r\n"
            . '"12","205","hash","False","pipeline","pipeline-hash","artifact","sha1"' . "\r\n";
        file_put_contents($path, $csv);

        try {
            $service = new WeeklySwingC171FinalFailedNotReadyClosureService();
            $method = new \ReflectionMethod($service, 'readSummaryCsv');
            $method->setAccessible(true);
            $parsed = $method->invoke($service, $path);

            $this->assertTrue($parsed['valid']);
            $this->assertSame('param_set_id', $parsed['header'][0]);
            $this->assertSame('12', $parsed['rows'][0]['param_set_id']);
            $this->assertArrayNotHasKey('"param_set_id"', $parsed['rows'][0]);
        } finally {
            @unlink($path);
        }
    }

}
