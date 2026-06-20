<?php

use App\Application\Watchlist\Services\WatchlistBacktestC35IsRobustnessRedesignDiagnosticService;

class WatchlistBacktestC35IsRobustnessRedesignDiagnosticServiceTest extends TestCase
{
    public function test_it_blocks_when_C34_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c35-missing-c34-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c34-artifact.json',
            WatchlistBacktestC35IsRobustnessRedesignDiagnosticService::DEFAULT_EXPECTED_C34_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C35_BLOCKED_MISSING_C34_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C35_C34_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C35_BLOCKED_MISSING_C34_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C34_hash_mismatches(): void
    {
        [$c34Path, $isPath, $outputPath] = $this->tempPaths('hash-mismatch');
        $c34 = $this->c34Artifact();
        $this->writeJson($c34Path, $c34);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService())->execute(
            $c34Path,
            'wrong-c34-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'is_evidence_artifact' => $isPath]
        );

        $this->assertSame('C35_BLOCKED_C34_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c34_hash_match']);
        $this->cleanup($c34Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C34_status_is_unexpected(): void
    {
        [$c34Path, $isPath, $outputPath] = $this->tempPaths('status');
        $c34 = $this->c34Artifact();
        $c34['status'] = 'C34_OPERATOR_VALIDATION_REQUIRED';
        $c34['artifact_hash'] = $this->stableHash($c34);
        $this->writeJson($c34Path, $c34);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService())->execute(
            $c34Path,
            $c34['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'is_evidence_artifact' => $isPath]
        );

        $this->assertSame('C35_BLOCKED_UNEXPECTED_C34_STATUS', $result['status']);
        $this->cleanup($c34Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C34_final_conclusion_is_unexpected(): void
    {
        [$c34Path, $isPath, $outputPath] = $this->tempPaths('conclusion');
        $c34 = $this->c34Artifact();
        $c34['diagnostic_conclusion'] = 'C34_BAD_MONTH_ROBUSTNESS_NOT_CONFIRMED_AFTER_C33_DATA_PATH_PASS';
        $c34['artifact_hash'] = $this->stableHash($c34);
        $this->writeJson($c34Path, $c34);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService())->execute(
            $c34Path,
            $c34['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'is_evidence_artifact' => $isPath]
        );

        $this->assertSame('C35_BLOCKED_UNEXPECTED_C34_CONCLUSION', $result['status']);
        $this->cleanup($c34Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_period_touches_OOS_reserved(): void
    {
        [$c34Path, $isPath, $outputPath] = $this->tempPaths('oos-period');
        $c34 = $this->c34Artifact();
        $this->writeJson($c34Path, $c34);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService())->execute(
            $c34Path,
            $c34['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $outputPath,
            ['overwrite' => true, 'is_evidence_artifact' => $isPath]
        );

        $this->assertSame('C35_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c34Path, $isPath, $outputPath);
    }

    public function test_it_completes_IS_diagnostic_when_C34_valid_and_IS_evidence_available(): void
    {
        [$c34Path, $isPath, $outputPath] = $this->tempPaths('completed');
        $c34 = $this->c34Artifact();
        $this->writeJson($c34Path, $c34);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC35IsRobustnessRedesignDiagnosticService())->execute(
            $c34Path,
            $c34['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'is_evidence_artifact' => $isPath, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c34_hash_match']);
        $this->assertSame('C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED', $result['diagnostic_conclusion']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertSame(6, $artifact['is_evidence_summary']['total_rows']);
        $this->assertSame(3, $artifact['is_evidence_summary']['g21_rows']);
        $this->assertSame(3, $artifact['is_evidence_summary']['g16_rows']);
        $this->assertSame(3, $artifact['g21_is_summary']['count']);
        $this->assertSame(3, $artifact['g16_is_summary']['count']);
        $this->assertTrue($artifact['g21_is_summary']['is_weakness_confirmed']);
        $this->assertTrue($artifact['g16_is_summary']['is_weakness_confirmed']);
        $this->assertNotEmpty($artifact['is_bad_month_like_summary']);
        $this->assertNotEmpty($artifact['is_branch_month_matrix']);
        $this->assertNotEmpty($artifact['is_ticker_failure_cluster']);
        $this->assertNotEmpty($artifact['redesign_hypotheses']);
        foreach ($artifact['redesign_hypotheses'] as $hypothesis) {
            $this->assertArrayHasKey('is_support_level', $hypothesis);
        }
        $this->assertSame(['2025-06', '2025-08', '2026-03'], $artifact['source_c34_problem_statement']['bad_months_oos_for_context_only']);
        $this->assertArrayHasKey('safety_boundaries', $artifact);
        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('candidate_reselection', $artifact);
        $this->assertArrayNotHasKey('profile_reselection', $artifact);

        $safetyBoundaries = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        $this->assertArrayHasKey('no_best_of_oos', $safetyBoundaries);
        $this->assertArrayHasKey('no_candidate_reselection', $safetyBoundaries);
        $this->assertArrayHasKey('no_profile_reselection', $safetyBoundaries);
        $this->assertTrue($safetyBoundaries['no_best_of_oos']);
        $this->assertTrue($safetyBoundaries['no_candidate_reselection']);
        $this->assertTrue($safetyBoundaries['no_profile_reselection']);
        $this->cleanup($c34Path, $isPath, $outputPath);
    }

    private function c34Artifact(): array
    {
        $artifact = [
            'run_code' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC',
            'status' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC',
            'production_ready' => false,
            'bad_month_diagnostic_rows' => [
                ['trade_month' => '2025-06'],
                ['trade_month' => '2025-08'],
                ['trade_month' => '2026-03'],
            ],
            'branch_robustness_rows' => [
                ['selected_source_code' => 'G21', 'branch_failure_class' => 'C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED'],
                ['selected_source_code' => 'G16', 'branch_failure_class' => 'C34_BRANCH_BAD_MONTH_CONCENTRATION_REVIEW'],
            ],
            'diagnostic_conclusion' => 'C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function isArtifact(): array
    {
        return [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_COMPLETED',
            'pick_diagnostic_rows' => [
                $this->row('2023-03-15', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAA', -0.0200, 'raw_damage_control_no_profit_d2_exit_d3_open', -0.0010),
                $this->row('2023-03-16', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAA', -0.0100, 'raw_damage_control_no_profit_d2_exit_d3_open', 0.0000),
                $this->row('2023-04-10', '2023-04', 'G21', 'no_rule_profit_signal_before_fallback', 'BBB', -0.0040, 'raw_damage_control_no_profit_d2_exit_d3_open', 0.0020),
                $this->row('2023-03-20', '2023-03', 'G16', 'next_open_delay_after_close_signal', 'CCC', -0.0060, 'raw_r09_next_open_after_close_profit', -0.0030),
                $this->row('2023-04-11', '2023-04', 'G16', 'next_open_delay_after_close_signal', 'DDD', -0.0050, 'raw_preplanned_intraday_target_hit', -0.0020),
                $this->row('2023-05-15', '2023-05', 'G16', 'next_open_delay_after_close_signal', 'EEE', -0.0040, 'raw_preplanned_intraday_target_hit', -0.0010),
            ],
        ];
    }

    private function row(string $date, string $month, string $source, string $bucket, string $ticker, float $ret, string $exitReason, float $delta): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $ticker,
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'profile_exit_reason' => $exitReason,
            'profile_ret_net' => $ret,
            'delta_vs_raw_r09' => $delta,
            'oos_executed' => false,
            'production_ready' => 0,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c35-'.$suffix.'-'.uniqid();
        return [$base.'-c34.json', $base.'-is.json', $base.'-out.json'];
    }

    private function writeJson(string $path, array $payload): void
    {
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }
}
