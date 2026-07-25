<?php

use App\Application\Watchlist\Services\WatchlistBacktestC29OosProofService;

class WatchlistBacktestC29OosProofServiceTest extends TestCase
{
    public function test_it_blocks_when_C28_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c29-missing-c28-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            sys_get_temp_dir().'/missing-c28-artifact.json',
            WatchlistBacktestC29OosProofService::DEFAULT_EXPECTED_C28_HASH,
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-18T00:00:00+00:00']
        );

        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $result['status']);
        $this->assertSame('WS_BT_C29_C28_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['gate']['overall_pass']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_C28_hash_mismatches(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('hash-mismatch');
        file_put_contents($c28Path, json_encode($this->c28Artifact(), JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            'wrong-hash',
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $result['status']);
        $this->assertSame('WS_BT_C29_C28_ARTIFACT_HASH_MISMATCH', $result['reason_code']);
        $this->assertFalse((bool) $result['c28_hash_match']);
        $this->cleanup($c28Path, $outputPath);
    }

    public function test_it_blocks_when_candidate_profile_is_missing(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('candidate-missing');
        $artifact = $this->c28Artifact();
        $artifact['candidate_profile_code'] = 'C28_G00_RAW_R09_BASELINE';
        unset($artifact['profile_summary'][WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        file_put_contents($c28Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            $artifact['artifact_hash'],
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $result['status']);
        $this->assertSame('WS_BT_C29_C28_CANDIDATE_NOT_FOUND', $result['reason_code']);
        $this->cleanup($c28Path, $outputPath);
    }

    public function test_it_blocks_when_rule_mapping_does_not_match_C28_G05(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('rule-mismatch');
        $artifact = $this->c28Artifact();
        foreach ($artifact['diagnostic_profiles'] as &$profile) {
            if (($profile['profile_code'] ?? null) === WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE) {
                $profile['source'] = 'g21';
            }
        }
        unset($profile);
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        file_put_contents($c28Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            $artifact['artifact_hash'],
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $result['status']);
        $this->assertSame('WS_BT_C29_C28_RULE_MAPPING_MISMATCH', $result['reason_code']);
        $this->cleanup($c28Path, $outputPath);
    }

    public function test_it_blocks_G05_before_oos_when_rule_route_depends_on_future_path(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('pass');
        $artifact = $this->c28Artifact();
        file_put_contents($c28Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            $artifact['artifact_hash'],
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            [
                'overwrite' => true,
                'executed_at' => '2026-06-18T00:00:00+00:00',
                'oos_raw_rows_fixture' => $this->oosRawRows(45, 0.01),
            ]
        );

        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $result['status']);
        $this->assertSame('WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $out['status']);
        $this->assertSame('C29_OOS_PROOF_C28_G05', $out['run_code']);
        $this->assertSame(['from' => '2025-05-22', 'to' => '2026-05-29'], $out['oos_window']);
        $this->assertFalse($out['production_ready']);
        $this->assertFalse($out['gate']['overall_pass']);
        $this->assertFalse($out['gate']['execution_route_pass']);
        $this->assertSame('RAW_R09', $out['candidate_rule']['candidate_matches_or_beats_c22']);
        $this->assertSame('RAW_G21', $out['candidate_rule']['no_rule_profit_signal_before_fallback']);
        $this->assertSame('RAW_G16', $out['candidate_rule']['next_open_delay_after_close_signal']);
        $this->assertArrayNotHasKey('best_profile_binding_allowed', $out['safety_boundaries']);
        $this->assertTrue($out['safety_boundaries']['FUTURE_DERIVED_RULE_ROUTING_FORBIDDEN']);
        $this->assertSame([], $out['oos_pick_rows'] ?? []);
        $this->cleanup($c28Path, $outputPath);
    }

    public function test_it_fails_closed_when_legacy_C28_artifact_has_no_route_availability_proof(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('legacy-route-metadata-missing');
        $artifact = $this->c28Artifact();
        unset($artifact['candidate_readiness_summary']['execution_time_route_availability_pass']);
        foreach ($artifact['pick_diagnostic_rows'] as &$row) {
            unset($row['route_decision_available_before_entry'], $row['future_path_price_used_for_rule_routing']);
        }
        unset($row);
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        file_put_contents($c28Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            $artifact['artifact_hash'],
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true, 'oos_raw_rows_fixture' => $this->oosRawRows(45, 0.01)]
        );

        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $result['status']);
        $this->assertSame('WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $this->cleanup($c28Path, $outputPath);
    }

    public function test_production_ready_remains_false_when_future_route_guard_blocks(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('production-ready');
        $artifact = $this->c28Artifact();
        file_put_contents($c28Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            $artifact['artifact_hash'],
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true, 'oos_raw_rows_fixture' => $this->oosRawRows(45, 0.01)]
        );

        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertFalse($out['production_ready']);
        $this->assertSame('C29_BLOCKED_INVALID_C28_SOURCE', $out['status']);
        $this->assertSame([], $out['oos_pick_rows'] ?? []);
        $this->cleanup($c28Path, $outputPath);
    }

    public function test_it_does_not_reselect_profile_or_create_best_of_oos(): void
    {
        $service = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php'));
        $this->assertStringContainsString('NO_PROFILE_RESELECTION', $service);
        $this->assertStringContainsString('NO_BEST_OF_OOS', $service);
        $this->assertStringContainsString('oos_return_used_for_profile_selection', $service);
        $this->assertStringNotContainsString('best_profile_code_by_avg', $service);
        $this->assertStringNotContainsString('best_profile_code_by_median', $service);
        $this->assertStringNotContainsString('best_profile_code_by_p25', $service);
    }

    public function test_lookahead_guard_rejects_future_derived_route_before_fixture_evaluation(): void
    {
        [$c28Path, $outputPath] = $this->tempPaths('lookahead');
        $artifact = $this->c28Artifact();
        file_put_contents($c28Path, json_encode($artifact, JSON_UNESCAPED_SLASHES)."\n");

        $result = (new WatchlistBacktestC29OosProofService())->execute(
            $c28Path,
            $artifact['artifact_hash'],
            WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            WatchlistBacktestC29OosProofService::OOS_FROM,
            WatchlistBacktestC29OosProofService::OOS_TO,
            $outputPath,
            ['overwrite' => true, 'oos_raw_rows_fixture' => $this->oosRawRows(45, 0.01)]
        );

        $this->assertSame('WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN', $result['reason_code']);
        $out = json_decode((string) file_get_contents($outputPath), true);
        $this->assertFalse($out['gate']['execution_route_pass']);
        $this->assertFalse($out['gate']['overall_pass']);
        $this->cleanup($c28Path, $outputPath);
    }

    private function c28Artifact(): array
    {
        $artifact = [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'PASS',
            'candidate_profile_code' => WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            'diagnostic_profiles' => [[
                'profile_code' => WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
                'family' => 'bucket_revision',
                'candidate_role' => 'r09_stable_g21_no_signal_g16_delay',
                'source' => 'r09_stable_g21_no_signal_g16_delay',
            ]],
            'profile_summary' => [
                WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE => [
                    'profile_code' => WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
                    'evaluated_picks_count' => 3,
                ],
            ],
            'candidate_readiness_summary' => [
                'c28_revised_candidate_ready' => true,
                'c29_oos_proof_recommended' => true,
                'execution_time_route_availability_pass' => false,
            ],
            'pick_diagnostic_rows' => [
                $this->c28Pick(145, 'candidate_matches_or_beats_c22', 'R09'),
                $this->c28Pick(146, 'no_rule_profit_signal_before_fallback', 'G21'),
                $this->c28Pick(147, 'next_open_delay_after_close_signal', 'G16'),
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function c28Pick(int $paramId, string $bucket, string $source): array
    {
        return [
            'trade_date' => '2025-01-02',
            'ticker' => 'TEST',
            'param_id' => $paramId,
            'row_code' => 'ROW_'.$paramId,
            'profile_code' => WatchlistBacktestC29OosProofService::CANDIDATE_PROFILE,
            'bucket_code' => $bucket,
            'selected_source_code' => $source,
            'route_decision_available_before_entry' => false,
            'future_path_price_used_for_rule_routing' => true,
        ];
    }

    private function oosRawRows(int $count, float $ret): array
    {
        $rows = [];
        $buckets = [
            ['candidate_matches_or_beats_c22', 'r09'],
            ['no_rule_profit_signal_before_fallback', 'g21'],
            ['next_open_delay_after_close_signal', 'g16'],
        ];
        for ($i = 0; $i < $count; $i++) {
            [$bucket, $winner] = $buckets[$i % 3];
            $date = '2025-06-'.str_pad((string) (($i % 20) + 1), 2, '0', STR_PAD_LEFT);
            $row = [
                'trade_date' => $date,
                'trade_month' => '2025-06',
                'ticker_id' => $i + 1,
                'ticker' => 'T'.($i + 1),
                'param_id' => 145 + ($i % 3),
                'row_code' => 'ROW',
                'entry_date' => $date,
                'raw_entry_price' => 100.0,
                'bucket_code' => $bucket,
                'bucket_reason' => 'fixture',
                'raw_ohlc_validated_flag' => true,
                'missing_path_data_flag' => false,
                'lookahead_safe' => true,
                'r09' => $this->exit($winner === 'r09' ? $ret : $ret / 2),
                'g21' => $this->exit($winner === 'g21' ? $ret : $ret / 2),
                'g16' => $this->exit($winner === 'g16' ? $ret : $ret / 2),
            ];
            $rows[] = $row;
        }
        return $rows;
    }

    private function exit(float $ret): array
    {
        return [
            'exit_date' => '2025-06-05',
            'exit_price' => 100.0 * (1 + $ret),
            'exit_day_offset' => 2,
            'exit_reason' => 'fixture_exit',
            'ret_net' => $ret,
            'lookahead_safe' => true,
            'missing_path_data_flag' => false,
        ];
    }

    private function stableHash(array $artifact): string
    {
        unset($artifact['artifact_hash']);
        return sha1(json_encode($artifact, JSON_UNESCAPED_SLASHES));
    }

    private function tempPaths(string $name): array
    {
        $c28Path = sys_get_temp_dir().'/c29-'.$name.'-c28.json';
        $outputPath = sys_get_temp_dir().'/c29-'.$name.'-output.json';
        $this->cleanup($c28Path, $outputPath);
        return [$c28Path, $outputPath];
    }

    private function cleanup(string ...$files): void
    {
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}
