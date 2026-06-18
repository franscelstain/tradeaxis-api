<?php

use App\Application\Watchlist\Services\WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService;

class WatchlistBacktestC25NoSignalFallbackDelayDiagnosticServiceTest extends TestCase
{
    public function test_it_generates_C25_no_signal_fallback_delay_artifact_without_catalog_or_oos(): void
    {
        $c23Path = sys_get_temp_dir().'/c25-c23-source-test.json';
        $c24Path = sys_get_temp_dir().'/c25-c24-source-test.json';
        $c21Path = sys_get_temp_dir().'/c25-c21-source-test.json';
        $outputPath = sys_get_temp_dir().'/c25-no-signal-fallback-delay-test.json';
        foreach ([$c23Path, $c24Path, $c21Path, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($c23Path, json_encode($this->c23Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c24Path, json_encode($this->c24Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c21Path, json_encode($this->c21Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService())->execute($c23Path, $c24Path, $outputPath, [
            'overwrite' => true,
            'input_c21_artifact' => $c21Path,
            'executed_at' => '2025-05-21T23:59:59+07:00',
            'diagnostic_profile_codes' => implode(',', [
                'C25_G00_CANONICAL_BASELINE',
                'C25_G02_C23_R09_BASELINE_BRIDGE',
                'C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN',
                'C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT',
                'C25_G17_PREPLANNED_TARGET_0_75PCT_WITH_STOP_1_50PCT',
                'C25_G19_NEXT_OPEN_DELAY_ROWS_ONLY_R09',
                'C25_G20_NO_SIGNAL_FALLBACK_ROWS_ONLY_R09',
            ]),
        ]);

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC', $result['scope']);
        $this->assertSame(3, $result['evaluated_picks_count']);
        $this->assertSame(1, $result['no_signal_fallback_count']);
        $this->assertSame(1, $result['next_open_delay_count']);
        $this->assertSame(1, $result['c25_catalog_implementation_deferred']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('PASS', $artifact['status']);
        $this->assertSame('abc-c23', $artifact['source_evidence']['c23_all_param_artifact_hash']);
        $this->assertSame('abc-c24', $artifact['source_evidence']['c24_all_param_artifact_hash']);
        $this->assertTrue($artifact['data_availability']['c23_r09_rows_available']);
        $this->assertTrue($artifact['data_availability']['c23_r15_rows_available']);
        $this->assertTrue($artifact['data_availability']['c23_r16_rows_available']);
        $this->assertTrue($artifact['data_availability']['derived_mfe_mae_available']);
        $this->assertFalse($artifact['data_availability']['d1_to_d5_ohlc_available']);
        $this->assertArrayHasKey('diagnostic_profiles', $artifact);
        $this->assertArrayHasKey('pick_diagnostic_rows', $artifact);
        $this->assertArrayHasKey('baseline_summaries', $artifact);
        $this->assertArrayHasKey('bucket_summary', $artifact);
        $this->assertArrayHasKey('no_signal_fallback_summary', $artifact);
        $this->assertArrayHasKey('next_open_delay_summary', $artifact);
        $this->assertArrayHasKey('profile_summary', $artifact);
        $this->assertArrayHasKey('decision', $artifact);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C25_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C24_MUTATION']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_price_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['diagnostic_profiles_used_as_production_rule']);
        $this->assertSame('NEXT_OPEN', $artifact['safety_boundaries']['canonical_model_unchanged']['ENTRY']);
        $this->assertSame('STOP_TP_OR_TIME', $artifact['safety_boundaries']['canonical_model_unchanged']['EXIT']);
        $this->assertGreaterThan(0, count($artifact['pick_diagnostic_rows']));

        $preplannedRows = array_values(array_filter($artifact['pick_diagnostic_rows'], function (array $row): bool {
            return ($row['profile_code'] ?? null) === 'C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT'
                && ($row['preplanned_order'] ?? false) === true;
        }));
        $this->assertNotEmpty($preplannedRows);
        $this->assertTrue($preplannedRows[0]['lookahead_safe']);
        $this->assertTrue($preplannedRows[0]['uses_intraday_high_low']);
        $this->assertFalse($preplannedRows[0]['future_path_price_used_for_selection']);

        @unlink($c23Path);
        @unlink($c24Path);
        @unlink($c21Path);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_required_input_artifacts_are_missing(): void
    {
        $result = (new WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c25-c23.json',
            sys_get_temp_dir().'/missing-c25-c24.json',
            sys_get_temp_dir().'/c25-blocked-output.json',
            ['overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C25_C23_ARTIFACT_UNREADABLE', $result['reason_code']);
        $this->assertSame(1, $result['c25_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c25_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);
    }

    public function test_it_rejects_unknown_diagnostic_profile_codes(): void
    {
        $c23Path = sys_get_temp_dir().'/c25-invalid-profile-c23.json';
        $c24Path = sys_get_temp_dir().'/c25-invalid-profile-c24.json';
        $outputPath = sys_get_temp_dir().'/c25-invalid-profile-output.json';
        foreach ([$c23Path, $c24Path, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($c23Path, json_encode($this->c23Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c24Path, json_encode($this->c24Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService())->execute($c23Path, $c24Path, $outputPath, [
            'overwrite' => true,
            'diagnostic_profile_codes' => 'NOT_A_C25_PROFILE',
        ]);

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C25_DIAGNOSTIC_PROFILE_INVALID', $result['reason_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        @unlink($c23Path);
        @unlink($c24Path);
        @unlink($outputPath);
    }

    private function c23Artifact(): array
    {
        $rows = [];
        foreach ([
            WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R00,
            WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R09,
            WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R13,
            WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R14,
            WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R15,
            WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R16,
        ] as $profile) {
            $rows[] = $this->row('2023-01-02', 148, 'AAA', $profile, null, 5, 5, -0.020, 0.000, -0.020, -0.005, -0.006, -0.004, -0.006, 0.015, -0.010);
            $rows[] = $this->row('2023-01-03', 148, 'BBB', $profile, 1, 2, 1, 0.000, 0.020, -0.010, -0.008, -0.006, -0.004, -0.003, 0.025, -0.012);
            $rows[] = $this->row('2023-01-04', 152, 'CCC', $profile, 2, 3, 2, 0.010, -0.005, 0.000, -0.002, -0.001, 0.006, 0.007, 0.018, -0.006);
        }
        return [
            'artifact_type' => 'C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c23',
            'canonical_summary' => [
                'evaluated_picks_count' => 3,
                'path_missing_count' => 0,
                'canonical_avg_ret_net' => -0.010,
                'canonical_median_ret_net' => -0.010,
                'canonical_p25_ret_net' => -0.020,
                'canonical_win_rate' => 0.0,
            ],
            'c22_shadow_s06_summary' => [
                'c22_shadow_s06_avg_ret_net' => 0.005,
                'c22_shadow_s06_median_ret_net' => 0.000,
                'c22_shadow_s06_p25_ret_net' => -0.005,
                'c22_shadow_s06_win_rate' => 0.3333333333,
            ],
            'rule_profile_summary' => [
                $this->profileSummary(WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R09, -0.0033333333, 0.000, -0.020, 0.3333333333),
                $this->profileSummary(WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R15, -0.0006666667, -0.004, -0.004, 0.3333333333),
                $this->profileSummary(WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R16, -0.0006666667, -0.003, -0.006, 0.3333333333),
            ],
            'pick_rule_rows' => $rows,
            'decision' => ['decision_status' => 'C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND_BUT_SHADOW_GAP_NOT_ACCEPTABLE'],
        ];
    }

    private function c24Artifact(): array
    {
        return [
            'artifact_type' => 'C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c24',
            'decision' => [
                'decision_status' => 'C24_C22_SHADOW_GAP_STILL_MATERIAL',
                'c24_gap_bridge_explained' => true,
            ],
            'gap_component_summary' => [
                'dominant_component' => 'no_rule_profit_signal_before_fallback',
            ],
        ];
    }

    private function c21Artifact(): array
    {
        return [
            'artifact_type' => 'C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c21',
            'pick_path_rows' => [
                $this->c21Row('2023-01-02', 148, 'AAA', 0.012, -0.004),
                $this->c21Row('2023-01-03', 148, 'BBB', 0.020, -0.010),
                $this->c21Row('2023-01-04', 152, 'CCC', 0.008, -0.004),
            ],
        ];
    }

    private function row(string $date, int $paramId, string $ticker, string $profile, ?int $signalOffset, int $ruleExitOffset, int $c22ExitOffset, float $r09Ret, float $c22Ret, float $canonicalRet, float $r13Ret, float $r14Ret, float $r15Ret, float $r16Ret, float $mfe, float $mae): array
    {
        $profileRet = $r09Ret;
        if ($profile === WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R00) {
            $profileRet = $canonicalRet;
        } elseif ($profile === WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R13) {
            $profileRet = $r13Ret;
        } elseif ($profile === WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R14) {
            $profileRet = $r14Ret;
        } elseif ($profile === WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R15) {
            $profileRet = $r15Ret;
        } elseif ($profile === WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService::C23_R16) {
            $profileRet = $r16Ret;
        }
        return [
            'trade_date' => $date,
            'ticker_id' => crc32($ticker) % 1000,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => 'TEST_'.$paramId,
            'entry_date' => '2023-01-05',
            'entry_price' => 100.0,
            'signal_close_price' => 100.0,
            'canonical_exit_date' => '2023-01-10',
            'canonical_exit_price' => 99.0,
            'canonical_exit_reason' => 'exit_hold',
            'canonical_exit_day_offset' => 5,
            'canonical_ret_net' => $canonicalRet,
            'max_favorable_excursion_pct' => $mfe,
            'max_adverse_excursion_pct' => $mae,
            'first_profitable_close_day' => $signalOffset,
            'c22_shadow_s06_exit_date' => '2023-01-08',
            'c22_shadow_s06_exit_price' => 101.0,
            'c22_shadow_s06_exit_day_offset' => $c22ExitOffset,
            'c22_shadow_s06_ret_net' => $c22Ret,
            'rule_profile_code' => $profile,
            'rule_family' => 'test',
            'rule_signal_date' => $signalOffset === null ? null : '2023-01-06',
            'rule_signal_day_offset' => $signalOffset,
            'rule_signal_type' => $signalOffset === null ? null : 'close_profit_gt_threshold',
            'rule_exit_date' => '2023-01-09',
            'rule_exit_price' => 100.5,
            'rule_exit_day_offset' => $ruleExitOffset,
            'rule_exit_reason' => 'rule_test',
            'rule_ret_net' => $profileRet,
            'profit_capture_gap_vs_c22_s06' => $c22Ret - $profileRet,
            'rule_win_flag' => $profileRet > 0,
            'lookahead_safe' => true,
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
        ];
    }

    private function c21Row(string $date, int $paramId, string $ticker, float $mfe, float $mae): array
    {
        return [
            'diagnostic_profile_code' => 'C21_P00_CANONICAL_PATH_BASELINE',
            'trade_date' => $date,
            'ticker_id' => crc32($ticker) % 1000,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => 'TEST_'.$paramId,
            'entry_date' => '2023-01-05',
            'd1_close_ret' => 0.001,
            'd2_close_ret' => 0.002,
            'd3_close_ret' => -0.001,
            'd4_close_ret' => 0.000,
            'd5_close_ret' => 0.003,
            'mfe_1d' => $mfe,
            'mfe_2d' => $mfe,
            'mfe_3d' => $mfe,
            'mfe_4d' => $mfe,
            'mfe_5d' => $mfe,
            'mae_1d' => $mae,
            'mae_2d' => $mae,
            'mae_3d' => $mae,
            'mae_4d' => $mae,
            'mae_5d' => $mae,
            'missing_path_data_flag' => false,
        ];
    }

    private function profileSummary(string $profileCode, float $avg, float $median, float $p25, float $winRate): array
    {
        return [
            'profile_code' => $profileCode,
            'evaluated_picks_count' => 3,
            'avg_ret_net' => $avg,
            'median_ret_net' => $median,
            'p25_ret_net' => $p25,
            'win_rate' => $winRate,
            'lookahead_violation_count' => 0,
        ];
    }
}
