<?php

use App\Application\Watchlist\Services\WatchlistBacktestC48OosFailureAttributionService;

class WatchlistBacktestC48OosFailureAttributionServiceTest extends TestCase
{
    public function test_it_blocks_missing_C47_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC48OosFailureAttributionService())->execute($this->path('missing-c47.json'), 'hash', '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
        $this->assertSame('C48_BLOCKED_MISSING_C47_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_expected_C47_hash_mismatch(): void
    {
        [$c47, $source, $c44, $output] = $this->writeFixtures('hash-mismatch');
        $result = (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, 'wrong-hash', '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
        $this->assertSame('C48_BLOCKED_C47_HASH_MISMATCH', $result['status']);
        $this->cleanup($c47, $source, $c44, $output);
    }

    public function test_it_blocks_invalid_C47_boundary_flags(): void
    {
        $cases = [
            ['status', 'C47_OOS_PROOF_PASSED_NOT_PRODUCTION_READY', 'C48_BLOCKED_UNEXPECTED_C47_STATUS'],
            ['diagnostic_conclusion', 'C47_LOCKED_C44_REFINEMENT_OOS_PROOF_PASSED', 'C48_BLOCKED_UNEXPECTED_C47_CONCLUSION'],
            ['production_ready', true, 'C48_BLOCKED_C47_PRODUCTION_READY_NOT_FALSE'],
            ['safety_boundaries.oos_data_used_for_tuning', true, 'C48_BLOCKED_C47_OOS_TUNING_FLAG_NOT_FALSE'],
            ['oos_source_and_selection_audit.best_of_oos_created', true, 'C48_BLOCKED_C47_BEST_OF_OOS_FLAG_INVALID'],
            ['oos_source_and_selection_audit.target_lookahead_violation_count', 1, 'C48_BLOCKED_C47_SELECTION_SAFETY_INVALID'],
            ['next_step_recommendation', 'C49_SOMETHING_ELSE', 'C48_BLOCKED_C47_NEXT_STEP_UNEXPECTED'],
        ];
        foreach ($cases as $index => $case) {
            [$c47, $source, $c44, $output] = $this->writeFixtures('boundary-'.$index);
            $artifact = json_decode((string) file_get_contents($c47), true);
            $this->setNested($artifact, $case[0], $case[1]);
            $artifact['artifact_hash'] = $this->stableHash($artifact);
            $this->writeJson($c47, $artifact);
            $result = (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, $artifact['artifact_hash'], '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c47, $source, $c44, $output);
        }
    }

    public function test_it_blocks_attribution_period_mismatch(): void
    {
        [$c47, $source, $c44, $output] = $this->writeFixtures('window');
        $hash = $this->hashFromFile($c47);
        $result = (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, $hash, '2025-05-23', '2026-05-29', $output, ['overwrite' => true]);
        $this->assertSame('C48_BLOCKED_ATTRIBUTION_PERIOD_MISMATCH', $result['status']);
        $this->cleanup($c47, $source, $c44, $output);
    }

    public function test_valid_C47_failed_artifact_completes_failure_attribution_without_tuning(): void
    {
        [$c47, $source, $c44, $output] = $this->writeFixtures('completed');
        $result = (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, $this->hashFromFile($c47), '2025-05-22', '2026-05-29', $output, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00']);
        $this->assertSame('C48_OOS_FAILURE_ATTRIBUTION_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertFileExists($output);

        $out = json_decode((string) file_get_contents($output), true);
        $this->assertSame('C48_OOS_FAILURE_ATTRIBUTION', $out['run_code']);
        $this->assertSame('C48_OOS_FAILURE_ATTRIBUTION', $out['artifact_type']);
        $this->assertTrue($out['c47_hash_match']);
        $this->assertSame('C47_OOS_PROOF_FAILED', $out['c47_status']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame('failure_attribution_only', $out['oos_attribution_period']['purpose']);
        $this->assertFalse($out['oos_attribution_period']['oos_data_used_for_tuning']);
        $this->assertSame(6, $out['source_c47_summary']['evaluated_picks_count']);
        $this->assertNotEmpty($out['month_failure_attribution']);
        $this->assertNotEmpty($out['branch_failure_attribution']);
        $this->assertNotEmpty($out['baseline_target_overlap_attribution']);
        $this->assertNotEmpty($out['ticker_failure_attribution']);
        $this->assertNotEmpty($out['sector_bucket_failure_attribution']);
        $this->assertNotEmpty($out['entry_path_failure_attribution']);
        $this->assertNotEmpty($out['is_vs_oos_contrast']);
        $this->assertTrue($out['failure_attribution_summary']['failure_attribution_completed']);
        $this->assertFalse($out['c49_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($out['c49_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($out['c49_readiness_decision']['production_ready']);
        $this->assertNotEmpty($out['candidate_safety_audit']);
        $this->assertArrayHasKey('NO_OOS_TUNING', $out['safety_boundaries']);
        $this->assertTrue($out['safety_boundaries']['NO_OOS_TUNING']);
        $this->cleanup($c47, $source, $c44, $output);
    }

    public function test_it_records_not_evaluable_reasons_when_metadata_fields_are_missing(): void
    {
        [$c47, $source, $c44, $output] = $this->writeFixtures('not-evaluable', false);
        $result = (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, $this->hashFromFile($c47), '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
        $this->assertSame('C48_OOS_FAILURE_ATTRIBUTION_COMPLETED', $result['status']);
        $out = json_decode((string) file_get_contents($output), true);
        $codes = array_column($out['not_evaluable_reasons'], 'reason_code');
        $this->assertContains('C48_FIELD_NOT_AVAILABLE_FOR_OOS_ATTRIBUTION', $codes);
        $this->assertContains('C48_PATH_ATTRIBUTION_NOT_EVALUABLE', $codes);
        $this->cleanup($c47, $source, $c44, $output);
    }

    public function test_baseline_target_overlap_and_ticker_concentration_are_reported(): void
    {
        [$c47, $source, $c44, $output] = $this->writeFixtures('overlap');
        (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, $this->hashFromFile($c47), '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
        $out = json_decode((string) file_get_contents($output), true);
        $this->assertSame(6, $out['baseline_target_overlap_attribution']['target_pick_count']);
        $this->assertSame(7, $out['baseline_target_overlap_attribution']['baseline_pick_count']);
        $this->assertGreaterThanOrEqual(4, $out['baseline_target_overlap_attribution']['overlap_pick_count']);
        $this->assertContains($out['failure_attribution_summary']['dominant_failure_branch'], ['G16', 'G21', 'BOTH', 'NOT_EVALUABLE']);
        $this->assertIsArray($out['failure_attribution_summary']['top_loss_tickers']);
        $this->cleanup($c47, $source, $c44, $output);
    }

    public function test_return_and_path_fields_are_marked_diagnostic_only_not_selection(): void
    {
        [$c47, $source, $c44, $output] = $this->writeFixtures('diagnostic-only');
        (new WatchlistBacktestC48OosFailureAttributionService())->execute($c47, $this->hashFromFile($c47), '2025-05-22', '2026-05-29', $output, ['overwrite' => true]);
        $out = json_decode((string) file_get_contents($output), true);
        $this->assertFalse($out['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($out['safety_boundaries']['future_path_used_for_selection']);
        foreach ($out['entry_path_failure_attribution'] as $row) {
            $this->assertFalse($row['safe_for_selection']);
            $this->assertTrue($row['diagnostic_only']);
        }
        $this->cleanup($c47, $source, $c44, $output);
    }

    private function writeFixtures(string $suffix, bool $withOptionalFields = true): array
    {
        $base = sys_get_temp_dir().'/c48-'.$suffix.'-'.uniqid('', true);
        $paths = [$base.'-c47.json', $base.'-source.json', $base.'-c44.json', $base.'-output.json'];
        $this->writeJson($paths[1], $this->sourceArtifact($withOptionalFields));
        $this->writeJson($paths[2], $this->c44Artifact());
        $this->writeJson($paths[0], $this->c47Artifact($paths[1], $paths[2], $withOptionalFields));
        return $paths;
    }

    private function c47Artifact(string $sourcePath, string $c44Path, bool $withOptionalFields): array
    {
        $rows = array_values(array_filter($this->sourceRows($withOptionalFields), function (array $row): bool {
            return in_array($row['ticker'], ['AAA', 'BBB', 'CCC', 'DDD', 'EEE', 'FFF'], true);
        }));
        $targetMetrics = $this->metrics($rows);
        $baselineRows = $rows;
        $baselineMetrics = $this->metrics($baselineRows);
        $artifact = [
            'run_code' => 'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT',
            'artifact_type' => 'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT',
            'status' => 'C47_OOS_PROOF_FAILED',
            'diagnostic_conclusion' => 'C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED',
            'next_step_recommendation' => 'C48_OOS_FAILURE_ATTRIBUTION_FOR_C44_REFINEMENT',
            'production_ready' => false,
            'input_oos_source_artifact' => $sourcePath,
            'input_c44_artifact' => $c44Path,
            'oos_window' => ['from' => '2025-05-22', 'to' => '2026-05-29'],
            'locked_candidate' => [
                'candidate_code' => 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA',
                'selection_rule' => 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota',
                'monthly_g21_quota' => 13,
                'production_ready' => false,
            ],
            'target_oos_result' => array_merge(['candidate_code' => 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA', 'bad_month_like_months' => ['2025-06', '2025-07'], 'bad_month_like_count' => 2, 'month_summary' => $this->monthSummary($rows)], $targetMetrics),
            'baseline_oos_result' => array_merge(['candidate_code' => 'C44_BASELINE_C39_METADATA_G21_MONTHLY_QUOTA', 'bad_month_like_months' => ['2025-06', '2025-07'], 'bad_month_like_count' => 2, 'month_summary' => $this->monthSummary($baselineRows)], $baselineMetrics),
            'comparison_vs_baseline' => ['delta_avg_ret_net' => 0.001, 'delta_win_rate' => 0.10, 'delta_bad_month_like_count' => 0],
            'oos_gate' => ['failed_checks' => ['avg_pass', 'median_pass', 'month_win_rate_pass'], 'overall_pass' => false],
            'oos_source_and_selection_audit' => [
                'selection_rule_reconstruction_pass' => true,
                'fixed_quota_pass' => true,
                'market_index_roc20_missing_count' => 0,
                'target_missing_path_count' => 0,
                'target_lookahead_violation_count' => 0,
                'target_future_or_return_selection_violation_count' => 0,
                'return_not_used_for_selection' => true,
                'future_path_not_used_for_selection' => true,
                'best_of_oos_created' => false,
            ],
            'oos_pick_rows' => $rows,
            'safety_boundaries' => [
                'NO_OOS_TUNING' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER_SELECTION' => true,
                'NO_CANDIDATE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function sourceArtifact(bool $withOptionalFields): array
    {
        $artifact = ['artifact_type' => 'C29_OOS_PROOF', 'status' => 'C29_OOS_PROOF_FAILED', 'production_ready' => false, 'oos_pick_rows' => $this->sourceRows($withOptionalFields)];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function c44Artifact(): array
    {
        $artifact = [
            'status' => 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED',
            'production_ready' => false,
            'candidate_results' => [[
                'candidate_code' => 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA',
                'selected_rows' => 300,
                'avg_ret_net' => 0.011,
                'win_rate' => 0.58,
                'bad_month_like_count' => 1,
                'month_avg_ret_net_min' => -0.004,
                'month_win_rate_min' => 0.42,
            ]],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function sourceRows(bool $withOptionalFields): array
    {
        $rows = [
            $this->row('2025-06-02', 'AAA', 'G16', -0.04, $withOptionalFields),
            $this->row('2025-06-03', 'BBB', 'G16', -0.02, $withOptionalFields),
            $this->row('2025-06-04', 'CCC', 'G21', -0.03, $withOptionalFields),
            $this->row('2025-07-02', 'DDD', 'G21', -0.01, $withOptionalFields),
            $this->row('2025-07-03', 'EEE', 'G21', 0.02, $withOptionalFields),
            $this->row('2025-08-04', 'FFF', 'G21', 0.01, $withOptionalFields),
            $this->row('2025-08-05', 'GGG', 'G21', -0.01, $withOptionalFields),
        ];
        return $rows;
    }

    private function row(string $date, string $ticker, string $branch, float $ret, bool $withOptionalFields): array
    {
        $row = [
            'trade_date' => $date,
            'trade_month' => substr($date, 0, 7),
            'ticker' => $ticker,
            'ticker_id' => crc32($ticker) % 100000,
            'param_id' => 150,
            'row_code' => 'ROW_'.$ticker,
            'selected_source_code' => $branch,
            'profile_ret_net' => $ret,
            'missing_path_data_flag' => false,
            'lookahead_safe' => true,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'production_ready' => 0,
        ];
        if ($withOptionalFields) {
            $row['bucket_code'] = $branch === 'G16' ? 'next_open_delay_after_close_signal' : 'stable_g21';
            $row['profile_exit_reason'] = $ret < 0 ? 'time_exit' : 'raw_preplanned_intraday_target_hit';
            $row['profile_exit_day_offset'] = 5;
            $row['sector_code'] = $ticker === 'AAA' ? 'TECH' : 'MISC';
        }
        return $row;
    }

    private function metrics(array $rows): array
    {
        $values = array_values(array_map(function (array $row): float { return (float) $row['profile_ret_net']; }, $rows));
        sort($values);
        $count = count($values);
        $avg = $count > 0 ? array_sum($values) / $count : null;
        $wins = count(array_filter($values, function (float $v): bool { return $v > 0; }));
        return [
            'selected_rows' => count($rows),
            'evaluated_picks_count' => $count,
            'avg_ret_net' => $avg,
            'median_ret_net' => $count ? $values[(int) floor(($count - 1) / 2)] : null,
            'p25_ret_net' => $count ? $values[0] : null,
            'p10_ret_net' => $count ? $values[0] : null,
            'win_rate' => $count ? $wins / $count : null,
            'month_win_rate_min' => 0,
            'month_avg_ret_net_min' => -0.03,
        ];
    }

    private function monthSummary(array $rows): array
    {
        $out = [];
        foreach (['2025-06', '2025-07', '2025-08'] as $month) {
            $monthRows = array_values(array_filter($rows, function (array $row) use ($month): bool { return $row['trade_month'] === $month; }));
            if (count($monthRows) === 0) {
                continue;
            }
            $m = $this->metrics($monthRows);
            $out[] = ['trade_month' => $month, 'evaluated_picks_count' => count($monthRows), 'avg_ret_net' => $m['avg_ret_net'], 'win_rate' => $m['win_rate']];
        }
        return $out;
    }

    private function hashFromFile(string $path): string { $artifact = json_decode((string) file_get_contents($path), true); return $this->stableHash($artifact); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function path(string $name): string { return sys_get_temp_dir().'/c48-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $index => $part) { if ($index === count($parts) - 1) { $cursor[$part] = $value; return; } if (! isset($cursor[$part]) || ! is_array($cursor[$part])) { $cursor[$part] = []; } $cursor =& $cursor[$part]; } }
}
