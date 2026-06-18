<?php

use App\Application\Watchlist\Services\WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService;

class WatchlistBacktestC28RuleRevisionTiebreakDiagnosticServiceTest extends TestCase
{
    public function test_it_generates_C28_rule_revision_tiebreak_artifact_without_catalog_or_oos(): void
    {
        $c27Path = sys_get_temp_dir().'/c28-c27-source-test.json';
        $outputPath = sys_get_temp_dir().'/c28-rule-revision-output-test.json';
        foreach ([$c27Path, $outputPath] as $file) {
            @unlink($file);
        }
        file_put_contents($c27Path, json_encode($this->c27Artifact(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService())->execute($c27Path, $outputPath, [
            'overwrite' => true,
            'executed_at' => '2025-05-21T23:59:59+07:00',
        ]);

        $this->assertSame('PASS', $result['status']);
        $this->assertSame('WS_BT_C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_READY', $result['reason_code']);
        $this->assertSame('IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC', $result['scope']);
        $this->assertSame(3, $result['evaluated_picks_count']);
        $this->assertSame(1, $result['raw_ohlc_validation_pass']);
        $this->assertSame(1, $result['c28_revised_candidate_ready']);
        $this->assertSame(1, $result['c29_oos_proof_recommended']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('PASS', $artifact['status']);
        $this->assertSame('C28_REVISED_RAW_CANDIDATE_READY_FOR_C29_OOS_PROOF', $artifact['decision']['decision_status']);
        $this->assertSame(WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService::PRIMARY_PROFILE, $artifact['candidate_profile_code']);
        $this->assertTrue($artifact['candidate_readiness_summary']['c28_revised_candidate_ready']);
        $this->assertTrue($artifact['candidate_readiness_summary']['c29_oos_proof_recommended']);
        $this->assertTrue($artifact['candidate_readiness_summary']['candidate_bucket_stability_pass']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);
        $this->assertSame('NOT_CREATED', $artifact['safety_boundaries']['C28_CATALOG_CODE']);
        $this->assertTrue($artifact['safety_boundaries']['NO_C01_TO_C27_MUTATION']);
        $this->assertFalse($artifact['safety_boundaries']['best_profile_binding_allowed']);
        $this->assertTrue($artifact['safety_boundaries']['raw_ohlc_used_for_execution']);
        $this->assertFalse($artifact['safety_boundaries']['derived_mfe_mae_used_for_execution']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_price_used_for_selection']);
        $this->assertSame('NEXT_OPEN', $artifact['price_evaluation_model']['ENTRY']);
        $this->assertSame('STOP_TP_OR_TIME', $artifact['price_evaluation_model']['EXIT']);

        $primaryRows = array_values(array_filter($artifact['pick_diagnostic_rows'], function (array $row): bool {
            return ($row['profile_code'] ?? null) === WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService::PRIMARY_PROFILE;
        }));
        $this->assertCount(3, $primaryRows);
        $this->assertSame('R09', $primaryRows[0]['selected_source_code']);
        $this->assertSame('G21', $primaryRows[1]['selected_source_code']);
        $this->assertSame('G16', $primaryRows[2]['selected_source_code']);
        $this->assertFalse($primaryRows[0]['future_path_price_used_for_selection']);
        $this->assertFalse($primaryRows[0]['profile_ret_net_used_for_selection']);
        $this->assertFalse($primaryRows[0]['derived_mfe_mae_used_for_execution']);

        foreach ([$c27Path, $outputPath] as $file) {
            @unlink($file);
        }
    }

    public function test_it_writes_blocked_artifact_when_C27_input_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c28-blocked-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService())->execute(
            sys_get_temp_dir().'/missing-c28-c27.json',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('BLOCKED', $result['status']);
        $this->assertSame('WS_BT_C28_C27_ARTIFACT_UNREADABLE', $result['reason_code']);
        $this->assertSame(1, $result['c28_catalog_implementation_deferred']);
        $this->assertSame('NOT_CREATED', $result['c28_catalog_code']);
        $this->assertSame(0, $result['oos_executed']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC', $artifact['artifact_type']);
        $this->assertSame('BLOCKED', $artifact['status']);
        $this->assertSame('C28_DIAGNOSTIC_BLOCKED', $artifact['decision']['decision_status']);
        $this->assertFalse($artifact['decision']['catalog_allowed']);
        $this->assertFalse($artifact['decision']['oos_allowed']);

        @unlink($outputPath);
    }

    private function c27Artifact(): array
    {
        return [
            'artifact_type' => 'C27_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION',
            'status' => 'PASS',
            'artifact_hash' => 'abc-c27',
            'raw_ohlc_validation_summary' => [
                'raw_ohlc_validation_pass' => true,
            ],
            'raw_pick_rows' => [
                $this->rawRow('2023-01-02', 'AAA', 101, 'candidate_matches_or_beats_c22', 0.010, -0.020, 0.005, 0.004),
                $this->rawRow('2023-01-03', 'BBB', 102, 'no_rule_profit_signal_before_fallback', -0.030, -0.005, -0.020, -0.030),
                $this->rawRow('2023-01-04', 'CCC', 103, 'next_open_delay_after_close_signal', -0.005, 0.010, 0.006, 0.015),
            ],
        ];
    }

    private function rawRow(string $date, string $ticker, int $paramId, string $bucket, float $r09, float $g21, float $g13, float $g16): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => substr($date, 0, 7),
            'ticker_id' => crc32($ticker) % 1000,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => 'TEST_'.$paramId,
            'entry_date' => $date,
            'raw_entry_price' => 100.0,
            'bucket_code' => $bucket,
            'bucket_reason' => 'fixture bucket',
            'raw_ohlc_validated_flag' => true,
            'missing_path_data_flag' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'r09' => $this->exit($r09, 'raw_r09'),
            'g21' => $this->exit($g21, 'raw_g21'),
            'g13' => $this->exit($g13, 'raw_g13'),
            'g16' => $this->exit($g16, 'raw_g16'),
        ];
    }

    private function exit(float $ret, string $reason): array
    {
        return [
            'exit_date' => '2023-01-05',
            'exit_price' => 100.0 * (1.0 + $ret),
            'exit_day_offset' => 2,
            'exit_reason' => $reason,
            'ret_net' => $ret,
            'lookahead_safe' => true,
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
        ];
    }
}
