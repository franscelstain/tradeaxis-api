<?php

use App\Application\Watchlist\Services\WatchlistBacktestC33DataPathReplayProofService;

class WatchlistBacktestC33DataPathReplayProofServiceTest extends TestCase
{
    public function test_it_blocks_when_C32_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c33-missing-c32-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            sys_get_temp_dir().'/missing-c32-artifact.json',
            WatchlistBacktestC33DataPathReplayProofService::DEFAULT_EXPECTED_C32_HASH,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C33_BLOCKED_MISSING_C32_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C33_C32_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C33_BLOCKED_MISSING_C32_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertSame('C33_DATA_PATH_REPLAY_BLOCKED', $artifact['diagnostic_conclusion']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C32_hash_mismatches(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('hash-mismatch');
        $c32 = $this->c32Artifact();
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            'wrong-c32-hash',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C33_BLOCKED_C32_HASH_MISMATCH', $result['status']);
        $this->assertSame('WS_BT_C33_C32_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c32_hash_match']);
        $this->cleanup($c32Path, $outputPath);
    }

    public function test_it_blocks_when_C32_status_is_unexpected(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('status');
        $c32 = $this->c32Artifact();
        $c32['status'] = 'C32_OPERATOR_VALIDATION_REQUIRED';
        $c32['artifact_hash'] = $this->stableHash($c32);
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            $c32['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C33_BLOCKED_UNEXPECTED_C32_STATUS', $result['status']);
        $this->assertSame('WS_BT_C33_UNEXPECTED_C32_STATUS', $result['reason_code']);
        $this->cleanup($c32Path, $outputPath);
    }

    public function test_it_blocks_when_C32_conclusion_is_unexpected(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('conclusion');
        $c32 = $this->c32Artifact();
        $c32['diagnostic_conclusion'] = 'C32_SPLIT_BLOCKED_ACTUAL_LOOKAHEAD_FIX_REQUIRED';
        $c32['artifact_hash'] = $this->stableHash($c32);
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            $c32['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C33_BLOCKED_UNEXPECTED_C32_CONCLUSION', $result['status']);
        $this->assertSame('WS_BT_C33_UNEXPECTED_C32_CONCLUSION', $result['reason_code']);
        $this->cleanup($c32Path, $outputPath);
    }

    public function test_it_blocks_when_C32_data_path_status_is_unexpected(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('data-path-status');
        $c32 = $this->c32Artifact();
        $c32['data_path_remediation_status'] = 'C32_DATA_PATH_REMEDIATION_NOT_REQUIRED';
        $c32['artifact_hash'] = $this->stableHash($c32);
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            $c32['artifact_hash'],
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C33_BLOCKED_UNEXPECTED_C32_DATA_PATH_STATUS', $result['status']);
        $this->assertSame('WS_BT_C33_UNEXPECTED_C32_DATA_PATH_STATUS', $result['reason_code']);
        $this->cleanup($c32Path, $outputPath);
    }

    public function test_it_completes_replay_proof_when_all_D1_to_D5_raw_ohlc_paths_are_available(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('pass');
        $c32 = $this->c32Artifact();
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            $c32['artifact_hash'],
            $outputPath,
            [
                'overwrite' => true,
                'executed_at' => '2026-06-20T00:00:00+00:00',
                'calendar_dates_fixture' => $this->calendarFixture(),
                'bars_fixture' => $this->barsFixture(),
            ]
        );

        $this->assertSame('C33_DATA_PATH_REPLAY_PROOF_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertSame('C33_DATA_PATH_REPLAY_PASS', $result['data_path_replay_status']);
        $this->assertSame('PASS', $result['data_completeness_gate_after_replay']);
        $this->assertSame(4, $result['replay_pass_count']);
        $this->assertSame(0, $result['replay_fail_count']);
        $this->assertSame(0, $result['replay_blocked_count']);
        $this->assertSame('C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE', $result['diagnostic_conclusion']);
        $this->assertSame('C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_AFTER_C33_NO_OOS_TUNING', $result['next_step']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C33_DATA_PATH_REPLAY_PROOF', $out['run_code']);
        $this->assertSame('C33_DATA_PATH_REPLAY_PROOF', $out['artifact_type']);
        $this->assertFalse($out['production_ready']);
        $this->assertSame($c32['artifact_hash'], $out['expected_c32_hash']);
        $this->assertTrue($out['c32_hash_match']);
        $this->assertSame(4, $out['replay_scope']['source_scope_count']);
        $this->assertSame(['2025-06-05', '2025-08-19'], $out['replay_scope']['affected_entry_dates']);
        $this->assertSame(['BBSI', 'MICE'], $out['replay_scope']['affected_tickers']);
        $this->assertSame('PASS', $out['data_completeness_gate_after_replay']['status']);
        $this->assertTrue($out['data_completeness_gate_after_replay']['can_claim_data_completeness_pass']);
        $this->assertFalse($out['data_completeness_gate_after_replay']['can_claim_oos_pass']);
        $this->assertCount(4, $out['replay_rows']);
        $this->assertSame(['PASS'], array_values(array_unique(array_column($out['replay_rows'], 'raw_ohlc_replay_status'))));
        $this->assertFalse($out['replay_summary']['actual_lookahead_fix_required']);
        $this->assertFalse($out['replay_summary']['selection_leak_fix_required']);
        $this->assertFalse($out['replay_summary']['oos_tuning_allowed']);
        $this->assertFalse($out['safety_boundaries']['eod_bars_write_executed']);
        $this->cleanup($c32Path, $outputPath);
    }

    public function test_it_completes_with_failed_replay_when_a_required_path_bar_is_missing(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('failed');
        $c32 = $this->c32Artifact();
        $bars = $this->barsFixture();
        unset($bars['MICE|2025-06-10']);
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            $c32['artifact_hash'],
            $outputPath,
            [
                'overwrite' => true,
                'calendar_dates_fixture' => $this->calendarFixture(),
                'bars_fixture' => $bars,
            ]
        );

        $this->assertSame('C33_DATA_PATH_REPLAY_PROOF_COMPLETED', $result['status']);
        $this->assertSame('C33_DATA_PATH_REPLAY_FAILED_MISSING_OR_INVALID_PATH', $result['data_path_replay_status']);
        $this->assertSame('FAIL', $result['data_completeness_gate_after_replay']);
        $this->assertSame(2, $result['replay_pass_count']);
        $this->assertSame(2, $result['replay_fail_count']);
        $this->assertSame(0, $result['replay_blocked_count']);
        $this->assertSame('C33_DATA_PATH_REPLAY_CONFIRMED_MISSING_OR_INVALID_D1_TO_D5_RAW_OHLC', $result['diagnostic_conclusion']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame(['2025-06-10'], $out['replay_summary']['missing_path_dates']);
        $this->assertFalse($out['data_completeness_gate_after_replay']['can_claim_data_completeness_pass']);
        $this->assertSame('REMEDIATE_MISSING_D1_TO_D5_RAW_OHLC_PATH_THEN_RERUN_C33', $out['next_step']);
        $this->cleanup($c32Path, $outputPath);
    }

    public function test_it_completes_with_blocked_replay_when_calendar_window_is_insufficient(): void
    {
        [$c32Path, $outputPath] = $this->tempPaths('blocked-calendar');
        $c32 = $this->c32Artifact();
        $calendar = $this->calendarFixture();
        $calendar['2025-06-05'] = array_slice($calendar['2025-06-05'], 0, 4);
        $this->writeJson($c32Path, $c32);

        $result = (new WatchlistBacktestC33DataPathReplayProofService())->execute(
            $c32Path,
            $c32['artifact_hash'],
            $outputPath,
            [
                'overwrite' => true,
                'calendar_dates_fixture' => $calendar,
                'bars_fixture' => $this->barsFixture(),
            ]
        );

        $this->assertSame('C33_DATA_PATH_REPLAY_PROOF_COMPLETED', $result['status']);
        $this->assertSame('C33_DATA_PATH_REPLAY_BLOCKED_RUNTIME_OR_CALENDAR_UNAVAILABLE', $result['data_path_replay_status']);
        $this->assertSame('BLOCKED', $result['data_completeness_gate_after_replay']);
        $this->assertSame(2, $result['replay_pass_count']);
        $this->assertSame(0, $result['replay_fail_count']);
        $this->assertSame(2, $result['replay_blocked_count']);
        $this->assertSame('C33_DATA_PATH_REPLAY_BLOCKED_RUNTIME_OR_CALENDAR_UNAVAILABLE', $result['diagnostic_conclusion']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('RESOLVE_C33_RUNTIME_BLOCKER_THEN_RERUN_C33', $out['next_step']);
        $this->assertSame(2, $out['replay_summary']['replay_reason_counts']['WS_BT_C33_MARKET_CALENDAR_INSUFFICIENT_D1_D5_WINDOW']);
        $this->cleanup($c32Path, $outputPath);
    }

    private function c32Artifact(): array
    {
        $artifact = [
            'run_code' => 'C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC',
            'status' => 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC',
            'production_ready' => false,
            'data_path_remediation_status' => 'C32_DATA_PATH_REMEDIATION_REQUIRED',
            'data_path_remediation_scope' => [
                'missing_path_count' => 4,
                'affected_trade_dates' => ['2025-06-04', '2025-08-15'],
                'affected_entry_dates' => ['2025-06-05', '2025-08-19'],
                'affected_tickers' => ['BBSI', 'MICE'],
                'affected_param_ids' => [151, 152],
                'affected_source_codes' => ['R09'],
                'missing_path_reason_counts' => ['WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING' => 4],
            ],
            'missing_path_replay_rows' => [
                $this->missingPathRow('2025-06', '2025-06-04', '2025-06-05', 'MICE', 551, 151, '06_VOL_150_250_LOW_ATR_NEG_ROC20'),
                $this->missingPathRow('2025-06', '2025-06-04', '2025-06-05', 'MICE', 551, 152, '07_VOL_150_250_ONE_R_LOW_ATR'),
                $this->missingPathRow('2025-08', '2025-08-15', '2025-08-19', 'BBSI', 100, 151, '06_VOL_150_250_LOW_ATR_NEG_ROC20'),
                $this->missingPathRow('2025-08', '2025-08-15', '2025-08-19', 'BBSI', 100, 152, '07_VOL_150_250_ONE_R_LOW_ATR'),
            ],
            'split_decision' => [
                'actual_lookahead_fix_required' => false,
                'selection_leak_fix_required' => false,
                'data_path_remediation_required' => true,
                'bad_month_robustness_diagnostic_required' => true,
                'oos_tuning_allowed' => false,
                'profile_reselection_allowed' => false,
                'production_promotion_allowed' => false,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => 'C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED',
            'next_step' => 'C33_DATA_PATH_REPLAY_PROOF_THEN_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function missingPathRow(string $month, string $date, string $entryDate, string $ticker, int $tickerId, int $paramId, string $rowCode): array
    {
        return [
            'trade_month' => $month,
            'trade_date' => $date,
            'entry_date' => $entryDate,
            'ticker' => $ticker,
            'ticker_id' => $tickerId,
            'param_id' => $paramId,
            'row_code' => $rowCode,
            'selected_source_code' => 'R09',
            'selected_source_reason' => 'r09_default_non_primary_bucket',
            'required_path_scope' => 'D1_TO_D5_RAW_OHLC_PATH',
            'missing_path_reason_code' => 'WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING',
            'raw_ohlc_validated_flag' => false,
            'missing_path_data_flag' => true,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function calendarFixture(): array
    {
        return [
            '2025-06-05' => ['2025-06-05', '2025-06-06', '2025-06-09', '2025-06-10', '2025-06-11'],
            '2025-08-19' => ['2025-08-19', '2025-08-20', '2025-08-21', '2025-08-22', '2025-08-25'],
        ];
    }

    private function barsFixture(): array
    {
        $bars = [];
        foreach ($this->calendarFixture()['2025-06-05'] as $index => $date) {
            $bars['MICE|'.$date] = $this->bar($date, 'MICE', 551, 100 + $index, 11);
        }
        foreach ($this->calendarFixture()['2025-08-19'] as $index => $date) {
            $bars['BBSI|'.$date] = $this->bar($date, 'BBSI', 100, 200 + $index, 22);
        }
        return $bars;
    }

    private function bar(string $date, string $ticker, int $tickerId, float $base, int $publicationId): array
    {
        return [
            'trade_date' => $date,
            'ticker' => $ticker,
            'ticker_id' => $tickerId,
            'open' => $base,
            'high' => $base + 5,
            'low' => $base - 3,
            'close' => $base + 1,
            'volume' => 0,
            'adj_close' => null,
            'source_name' => 'fixture',
            'run_id' => $publicationId + 1000,
            'publication_id' => $publicationId,
        ];
    }

    private function writeJson(string $path, array $payload): void
    {
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash']);
        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES));
    }

    private function tempPaths(string $name): array
    {
        $c32Path = sys_get_temp_dir().'/c33-'.$name.'-c32.json';
        $outputPath = sys_get_temp_dir().'/c33-'.$name.'-output.json';
        $this->cleanup($c32Path, $outputPath);
        return [$c32Path, $outputPath];
    }

    private function cleanup(string ...$files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
