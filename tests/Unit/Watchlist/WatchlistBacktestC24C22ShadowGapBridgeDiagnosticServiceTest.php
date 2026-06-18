<?php

use App\Application\Watchlist\Services\WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService;

class WatchlistBacktestC24C22ShadowGapBridgeDiagnosticServiceTest extends TestCase
{
    public function test_it_generates_compact_C24_gap_bridge_artifact_without_catalog_or_oos(): void
    {
        $inputPath = sys_get_temp_dir().'/c24-c23-source-test.json';
        $outputPath = sys_get_temp_dir().'/c24-gap-bridge-test.json';
        foreach ([$inputPath, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($inputPath, json_encode($this->c23Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService())->execute($inputPath, $outputPath, [
            'overwrite' => true,
            'executed_at' => '2025-05-21T23:59:59+07:00',
        ]);

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC', $result['scope']);
        $this->assertSame(1, $result['c24_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c24_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertSame(3, $result['evaluated_picks_count']);
        $this->assertSame('next_open_delay_after_close_signal', $result['dominant_gap_component']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('PASS', $artifact['status']);
        $this->assertSame('abc123', $artifact['source_evidence']['c23_artifact_hash']);
        $this->assertSame(WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService::DEFAULT_CANDIDATE_PROFILE, $artifact['candidate_profile_code']);
        $this->assertArrayHasKey('metric_bridge_summary', $artifact);
        $this->assertArrayHasKey('gap_component_summary', $artifact);
        $this->assertArrayHasKey('segment_summaries', $artifact);
        $this->assertArrayNotHasKey('pick_rule_rows', $artifact);
        $this->assertTrue($artifact['decision']['c24_gap_bridge_explained']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C24_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C23_MUTATION']);
        $this->assertTrue($artifact['safety_boundaries']['reads_c23_artifact_only']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_price_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['c22_shadow_s06_used_for_selection']);

        @unlink($inputPath);
        @unlink($outputPath);
    }

    public function test_it_blocks_unreadable_c23_source_artifact(): void
    {
        $result = (new WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c23-source.json',
            sys_get_temp_dir().'/c24-missing-output.json',
            ['overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C24_C23_ARTIFACT_UNREADABLE', $result['reason_code']);
        $this->assertSame(1, $result['c24_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c24_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }

    private function c23Artifact(): array
    {
        return [
            'artifact_type' => 'C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc123',
            'canonical_summary' => [
                'canonical_avg_ret_net' => -0.010,
                'canonical_median_ret_net' => -0.005,
                'canonical_p25_ret_net' => -0.030,
                'canonical_win_rate' => 0.40,
            ],
            'c22_shadow_s06_summary' => [
                'c22_shadow_s06_avg_ret_net' => 0.010,
                'c22_shadow_s06_median_ret_net' => 0.012,
                'c22_shadow_s06_p25_ret_net' => -0.004,
                'c22_shadow_s06_win_rate' => 0.65,
            ],
            'decision' => [
                'decision_status' => 'C23_FIRST_PROFIT_CAPTURE_RULE_SIGNAL_FOUND',
                'non_lookahead_rule_candidate_found' => true,
                'c22_shadow_gap_acceptable' => false,
            ],
            'rule_profile_summary' => [[
                'profile_code' => WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService::DEFAULT_CANDIDATE_PROFILE,
                'profile_family' => 'first_profit_capture',
                'evaluated_picks_count' => 3,
                'avg_ret_net' => 0.0016666666666667,
                'median_ret_net' => 0.000,
                'p25_ret_net' => -0.010,
                'win_rate' => 0.66666666666667,
                'lookahead_violation_count' => 0,
            ]],
            'pick_rule_rows' => [
                $this->row('2023-01-02', 148, 1, 2, 1, 0.000, 0.010, -0.010),
                $this->row('2023-01-03', 148, 2, 3, 2, 0.005, 0.020, -0.010),
                $this->row('2023-01-04', 148, 3, 4, 3, 0.030, 0.000, -0.010),
            ],
        ];
    }

    private function row(string $date, int $paramId, int $signalOffset, int $ruleExitOffset, int $c22ExitOffset, float $ruleRet, float $c22Ret, float $canonicalRet): array
    {
        return [
            'trade_date' => $date,
            'ticker' => 'AAA',
            'param_id' => $paramId,
            'row_code' => 'TEST',
            'rule_profile_code' => WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService::DEFAULT_CANDIDATE_PROFILE,
            'rule_signal_day_offset' => $signalOffset,
            'rule_exit_day_offset' => $ruleExitOffset,
            'c22_shadow_s06_exit_day_offset' => $c22ExitOffset,
            'rule_exit_reason' => 'rule_first_profit_capture_next_open',
            'canonical_ret_net' => $canonicalRet,
            'rule_ret_net' => $ruleRet,
            'c22_shadow_s06_ret_net' => $c22Ret,
            'missing_path_data_flag' => false,
        ];
    }
}
