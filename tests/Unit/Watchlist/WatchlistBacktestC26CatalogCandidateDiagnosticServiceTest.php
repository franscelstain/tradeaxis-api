<?php

use App\Application\Watchlist\Services\WatchlistBacktestC26CatalogCandidateDiagnosticService;

class WatchlistBacktestC26CatalogCandidateDiagnosticServiceTest extends TestCase
{
    public function test_it_generates_C26_catalog_candidate_diagnostic_artifact_without_catalog_or_oos(): void
    {
        $c21Path = sys_get_temp_dir().'/c26-c21-source-test.json';
        $c23Path = sys_get_temp_dir().'/c26-c23-source-test.json';
        $c24Path = sys_get_temp_dir().'/c26-c24-source-test.json';
        $c25Path = sys_get_temp_dir().'/c26-c25-source-test.json';
        $outputPath = sys_get_temp_dir().'/c26-catalog-candidate-test.json';
        foreach ([$c21Path, $c23Path, $c24Path, $c25Path, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($c21Path, json_encode($this->c21Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c23Path, json_encode($this->c23Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c24Path, json_encode($this->c24Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        file_put_contents($c25Path, json_encode($this->c25Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC26CatalogCandidateDiagnosticService())->execute($c25Path, $outputPath, [
            'overwrite' => true,
            'input_c21_artifact' => $c21Path,
            'input_c23_artifact' => $c23Path,
            'input_c24_artifact' => $c24Path,
            'executed_at' => '2025-05-21T23:59:59+07:00',
            'profile_codes' => implode(',', [
                'C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE',
                'C26_G04_C25_G13_DEFENSIVE_DISTRIBUTION_COMPARATOR',
                'C26_G05_C25_G16_NEXT_OPEN_DELAY_COMPARATOR',
                'C26_G08_G21_WITH_PARAM_CONSISTENCY_GATE',
                'C26_G16_CATALOG_CANDIDATE_READINESS_SCORE',
            ]),
        ]);

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C26_CATALOG_CANDIDATE_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC', $result['scope']);
        $this->assertSame(4, $result['evaluated_picks_count']);
        $this->assertSame(1, $result['raw_ohlc_validation_required']);
        $this->assertSame(1, $result['derived_mfe_mae_dependency_detected']);
        $this->assertSame(1, $result['g21_primary_candidate_ready']);
        $this->assertSame(1, $result['g13_defensive_candidate_ready']);
        $this->assertSame(1, $result['g16_next_open_delay_component_ready']);
        $this->assertSame(1, $result['c27_catalog_candidate_implementation_recommended']);
        $this->assertSame(1, $result['c27_requires_raw_ohlc_validation_first']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C26_CATALOG_CANDIDATE_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('PASS', $artifact['status']);
        $this->assertSame('C26_RAW_OHLC_VALIDATION_REQUIRED', $artifact['decision']['decision_status']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertTrue($artifact['data_availability']['g21_rows_available']);
        $this->assertTrue($artifact['data_availability']['g13_rows_available']);
        $this->assertTrue($artifact['data_availability']['g16_rows_available']);
        $this->assertTrue($artifact['data_availability']['r09_rows_available']);
        $this->assertTrue($artifact['data_availability']['r15_rows_available']);
        $this->assertTrue($artifact['data_availability']['r16_rows_available']);
        $this->assertFalse($artifact['data_availability']['raw_high_low_available']);
        $this->assertTrue($artifact['data_availability']['raw_ohlc_validation_required']);
        $this->assertArrayHasKey('diagnostic_profiles', $artifact);
        $this->assertArrayHasKey('pick_diagnostic_rows', $artifact);
        $this->assertArrayHasKey('baseline_summaries', $artifact);
        $this->assertArrayHasKey('candidate_summary', $artifact);
        $this->assertArrayHasKey('param_consistency_summary', $artifact);
        $this->assertArrayHasKey('month_stability_summary', $artifact);
        $this->assertArrayHasKey('bucket_stability_summary', $artifact);
        $this->assertArrayHasKey('data_quality_summary', $artifact);
        $this->assertArrayHasKey('lookahead_safety_summary', $artifact);
        $this->assertArrayHasKey('candidate_readiness_summary', $artifact);
        $this->assertTrue($artifact['candidate_readiness_summary']['g21_primary_candidate_ready']);
        $this->assertTrue($artifact['candidate_readiness_summary']['g13_defensive_candidate_ready']);
        $this->assertTrue($artifact['candidate_readiness_summary']['g16_next_open_delay_component_ready']);
        $this->assertTrue($artifact['candidate_readiness_summary']['c27_catalog_candidate_implementation_recommended']);
        $this->assertTrue($artifact['candidate_readiness_summary']['c27_requires_raw_ohlc_validation_first']);
        $this->assertSame(0, $artifact['lookahead_safety_summary']['lookahead_violation_count']);
        $this->assertSame(2, $artifact['lookahead_safety_summary']['close_signal_next_open_rule_checks']['d1_close_signal_min_exit_day']);
        $this->assertSame(3, $artifact['lookahead_safety_summary']['close_signal_next_open_rule_checks']['d2_close_signal_min_exit_day']);
        $this->assertSame(4, $artifact['lookahead_safety_summary']['close_signal_next_open_rule_checks']['d3_close_signal_min_exit_day']);
        $this->assertTrue($artifact['bucket_stability_summary']['no_signal_fallback_improved']);
        $this->assertTrue($artifact['bucket_stability_summary']['next_open_delay_improved']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C26_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C25_MUTATION']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_price_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['diagnostic_profiles_used_as_production_rule']);
        $this->assertSame('NEXT_OPEN', $artifact['safety_boundaries']['canonical_model_unchanged']['ENTRY']);
        $this->assertSame('STOP_TP_OR_TIME', $artifact['safety_boundaries']['canonical_model_unchanged']['EXIT']);

        $g21Rows = array_values(array_filter($artifact['pick_diagnostic_rows'], function (array $row): bool {
            return ($row['profile_code'] ?? null) === 'C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE';
        }));
        $this->assertNotEmpty($g21Rows);
        $this->assertArrayHasKey('delta_vs_c25_g13', $g21Rows[0]);
        $this->assertArrayHasKey('delta_vs_c25_g16', $g21Rows[0]);
        $this->assertArrayHasKey('delta_vs_c25_g21', $g21Rows[0]);
        $this->assertTrue($g21Rows[0]['preplanned_order']);
        $this->assertSame(0.0100, $g21Rows[0]['preplanned_threshold_pct']);
        $this->assertFalse($g21Rows[0]['raw_ohlc_validated_flag']);
        $this->assertTrue($g21Rows[0]['derived_mfe_mae_dependency_flag']);
        $this->assertFalse($g21Rows[0]['future_path_price_used_for_selection']);
        $this->assertFalse($g21Rows[0]['profile_ret_net_used_for_selection']);

        foreach ([$c21Path, $c23Path, $c24Path, $c25Path, $outputPath] as $file) {
            @unlink($file);
        }
    }

    public function test_it_writes_blocked_artifact_when_C25_input_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c26-blocked-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC26CatalogCandidateDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c26-c25.json',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C26_C25_ARTIFACT_UNREADABLE', $result['reason_code']);
        $this->assertSame(1, $result['c26_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c26_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C26_CATALOG_CANDIDATE_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('BLOCKED', $artifact['status']);
        $this->assertSame('C26_DIAGNOSTIC_BLOCKED', $artifact['decision']['decision_status']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);

        @unlink($outputPath);
    }

    public function test_it_does_not_recommend_C27_when_G21_is_limited_to_one_param(): void
    {
        $c25Path = sys_get_temp_dir().'/c26-one-param-c25.json';
        $outputPath = sys_get_temp_dir().'/c26-one-param-output.json';
        foreach ([$c25Path, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($c25Path, json_encode($this->c25Artifact(true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC26CatalogCandidateDiagnosticService())->execute($c25Path, $outputPath, [
            'overwrite' => true,
            'diagnostic_profile_codes' => 'C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE,C26_G16_CATALOG_CANDIDATE_READINESS_SCORE',
        ]);

        $this->assertSame('PASS', $result['status']);
        $this->assertSame(0, $result['g21_primary_candidate_ready']);
        $this->assertSame(0, $result['c27_catalog_candidate_implementation_recommended']);
        $this->assertSame(1, $result['selection_quality_revisit_needed']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C26_CANDIDATE_NOT_READY', $artifact['decision']['decision_status']);
        $this->assertContains('G21_IMPROVEMENT_LIMITED_TO_ONE_PARAM', $artifact['candidate_readiness_summary']['g21_failure_reason_codes']);
        $this->assertFalse($artifact['candidate_readiness_summary']['c27_catalog_candidate_implementation_recommended']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);

        @unlink($c25Path);
        @unlink($outputPath);
    }

    private function c21Artifact(): array
    {
        return [
            'artifact_type' => 'C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c21',
            'pick_path_rows' => [
                ['trade_date' => '2023-01-02', 'ticker' => 'AAA', 'param_id' => 148, 'mfe_1d' => 0.02, 'mae_1d' => -0.01],
            ],
        ];
    }

    private function c23Artifact(): array
    {
        return [
            'artifact_type' => 'C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c23',
        ];
    }

    private function c24Artifact(): array
    {
        return [
            'artifact_type' => 'C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c24',
        ];
    }

    private function c25Artifact(bool $oneParam = false): array
    {
        $picks = [
            ['date' => '2023-01-02', 'param' => 148, 'ticker' => 'AAA', 'bucket' => 'no_rule_profit_signal_before_fallback', 'signal' => null, 'exit' => 5, 'r09' => -0.020, 'g21' => -0.002, 'g13' => 0.004, 'g16' => 0.006, 'r15' => -0.010, 'r16' => -0.011, 'canonical' => -0.025, 'c22' => -0.005],
            ['date' => '2023-01-03', 'param' => 148, 'ticker' => 'BBB', 'bucket' => 'next_open_delay_after_close_signal', 'signal' => 1, 'exit' => 2, 'r09' => -0.015, 'g21' => 0.010, 'g13' => 0.004, 'g16' => 0.012, 'r15' => -0.006, 'r16' => -0.007, 'canonical' => -0.018, 'c22' => 0.015],
            ['date' => '2023-02-02', 'param' => $oneParam ? 148 : 152, 'ticker' => 'CCC', 'bucket' => 'candidate_matches_or_beats_c22', 'signal' => 1, 'exit' => 2, 'r09' => -0.018, 'g21' => 0.012, 'g13' => 0.004, 'g16' => -0.010, 'r15' => -0.005, 'r16' => -0.005, 'canonical' => -0.020, 'c22' => 0.006],
            ['date' => '2023-02-03', 'param' => $oneParam ? 148 : 152, 'ticker' => 'DDD', 'bucket' => 'other_gap_rows', 'signal' => 2, 'exit' => 3, 'r09' => -0.012, 'g21' => 0.009, 'g13' => -0.026, 'g16' => 0.011, 'r15' => -0.004, 'r16' => -0.004, 'canonical' => -0.014, 'c22' => 0.012],
        ];

        $rows = [];
        foreach ($picks as $pick) {
            $rows[] = $this->c25Row($pick, 'C25_G00_CANONICAL_BASELINE', 'baseline', $pick['canonical'], false);
            $rows[] = $this->c25Row($pick, 'C25_G01_C22_S06_SHADOW_BENCHMARK', 'shadow_benchmark', $pick['c22'], false);
            $rows[] = $this->c25Row($pick, WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_R09, 'r09_bridge', $pick['r09'], false);
            $rows[] = $this->c25Row($pick, WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_R15, 'downside_comparator', $pick['r15'], false);
            $rows[] = $this->c25Row($pick, WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_R16, 'downside_comparator', $pick['r16'], false);
            $rows[] = $this->c25Row($pick, WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G13, 'preplanned_intraday_target', $pick['g13'], true);
            $rows[] = $this->c25Row($pick, WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G16, 'preplanned_intraday_target', $pick['g16'], true);
            $rows[] = $this->c25Row($pick, WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G21, 'combo_intraday_no_signal', $pick['g21'], true);
        }

        return [
            'artifact_type' => 'C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c25',
            'source_evidence' => [
                'c23_all_param_artifact_hash' => 'abc-c23',
                'c24_all_param_artifact_hash' => 'abc-c24',
            ],
            'baseline_summaries' => [
                'canonical_summary' => [
                    'evaluated_picks_count' => 4,
                    'path_missing_count' => 1,
                    'canonical_avg_ret_net' => -0.01925,
                    'canonical_median_ret_net' => -0.019,
                    'canonical_p25_ret_net' => -0.02125,
                    'canonical_win_rate' => 0.0,
                ],
                'c22_s06_summary' => [
                    'evaluated_picks_count' => 4,
                    'c22_shadow_s06_avg_ret_net' => 0.007,
                    'c22_shadow_s06_median_ret_net' => 0.009,
                    'c22_shadow_s06_p25_ret_net' => 0.00325,
                    'c22_shadow_s06_win_rate' => 0.75,
                ],
            ],
            'data_availability' => [
                'c23_all_param_artifact_available' => true,
                'c24_all_param_artifact_available' => true,
                'c21_path_artifact_available' => true,
                'canonical_baseline_available' => true,
                'c22_shadow_s06_available_or_recomputable' => true,
                'd1_to_d5_ohlc_available' => false,
                'derived_mfe_mae_available' => true,
                'next_open_after_close_signal_available' => true,
                'market_calendar_continuity_available' => true,
                'published_price_availability_available' => true,
                'notes' => ['C25 fixture uses derived MFE/MAE only.'],
            ],
            'profile_summary' => $this->profileSummaries($rows),
            'pick_diagnostic_rows' => $rows,
            'lookahead_safety_summary' => [
                'lookahead_violation_count' => 0,
                'lookahead_safe' => true,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'preplanned_order_threshold_fixed_before_path_evaluation' => true,
                'close_signal_same_day_exit_forbidden' => true,
            ],
            'decision' => [
                'decision_status' => 'C25_GAP_FIX_CANDIDATE_FOUND',
                'c26_catalog_candidate_diagnostic_recommended' => true,
                'catalog_allowed' => false,
                'oos_allowed' => false,
            ],
        ];
    }

    private function c25Row(array $pick, string $profile, string $family, float $ret, bool $preplanned): array
    {
        return [
            'trade_date' => $pick['date'],
            'ticker_id' => crc32($pick['ticker']) % 1000,
            'ticker' => $pick['ticker'],
            'param_id' => $pick['param'],
            'row_code' => 'TEST_'.$pick['param'],
            'entry_date' => $pick['date'],
            'entry_price' => 100.0,
            'signal_close_price' => 100.0,
            'bucket_code' => $pick['bucket'],
            'bucket_reason' => 'fixture bucket',
            'canonical_exit_date' => $pick['date'],
            'canonical_exit_price' => 98.0,
            'canonical_exit_reason' => 'exit_hold',
            'canonical_ret_net' => $pick['canonical'],
            'c22_s06_exit_date' => $pick['date'],
            'c22_s06_exit_price' => 101.0,
            'c22_s06_ret_net' => $pick['c22'],
            'c23_r09_exit_date' => $pick['date'],
            'c23_r09_exit_price' => 99.0,
            'c23_r09_ret_net' => $pick['r09'],
            'c23_r15_ret_net' => $pick['r15'],
            'c23_r16_ret_net' => $pick['r16'],
            'profile_code' => $profile,
            'profile_family' => $family,
            'profile_exit_date' => $pick['date'],
            'profile_exit_price' => 100.0 * (1 + $ret),
            'profile_exit_reason' => $preplanned ? 'preplanned_intraday_target_hit' : 'fixture_exit',
            'profile_ret_net' => $ret,
            'max_favorable_excursion_pct' => 0.020,
            'max_adverse_excursion_pct' => -0.010,
            'first_profitable_close_day' => $pick['signal'],
            'first_intraday_target_hit_day' => $preplanned ? 1 : null,
            'close_signal_day' => $pick['signal'],
            'next_open_exit_day' => $pick['exit'],
            'next_open_delay_return_impact' => $pick['bucket'] === 'next_open_delay_after_close_signal' ? $pick['c22'] - $pick['r09'] : null,
            'lookahead_safe' => true,
            'uses_intraday_high_low' => $preplanned,
            'preplanned_order' => $preplanned,
            'intraday_sequence_known' => false,
            'ambiguous_intraday_sequence_flag' => false,
            'conservative_fill_policy' => 'STOP_FIRST_IF_TARGET_AND_STOP_SAME_DAILY_CANDLE',
            'missing_path_data_flag' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
        ];
    }

    private function profileSummaries(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['profile_code']][] = $row;
        }
        $summaries = [];
        foreach ($groups as $profile => $profileRows) {
            $returns = array_map(function (array $row): float { return (float) $row['profile_ret_net']; }, $profileRows);
            sort($returns, SORT_NUMERIC);
            $wins = count(array_filter($returns, function (float $value): bool { return $value > 0; }));
            $summaries[] = [
                'profile_code' => $profile,
                'profile_family' => $profileRows[0]['profile_family'],
                'evaluated_picks_count' => count($profileRows),
                'avg_ret_net' => array_sum($returns) / count($returns),
                'median_ret_net' => ($returns[1] + $returns[2]) / 2,
                'p25_ret_net' => $returns[0] + (($returns[1] - $returns[0]) * 0.75),
                'win_rate' => $wins / count($returns),
                'lookahead_violation_count' => 0,
                'ambiguous_intraday_sequence_count' => 0,
                'uses_intraday_high_low_count' => count(array_filter($profileRows, function (array $row): bool { return (bool) $row['uses_intraday_high_low']; })),
                'preplanned_order_count' => count(array_filter($profileRows, function (array $row): bool { return (bool) $row['preplanned_order']; })),
                'distinct_param_count' => count(array_unique(array_map(function (array $row): int { return (int) $row['param_id']; }, $profileRows))),
            ];
        }
        return $summaries;
    }
}
