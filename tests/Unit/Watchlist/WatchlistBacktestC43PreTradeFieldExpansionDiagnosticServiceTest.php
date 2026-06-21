<?php

use App\Application\Watchlist\Services\WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService;

class WatchlistBacktestC43PreTradeFieldExpansionDiagnosticServiceTest extends TestCase
{
    public function test_it_blocks_when_C42_artifact_is_missing(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService())->execute(
            $this->path('missing-c42.json'),
            WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService::DEFAULT_EXPECTED_C42_HASH,
            '2023-01-02', '2025-05-21', $output,
            ['overwrite' => true]
        );
        $this->assertSame('C43_BLOCKED_MISSING_C42_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->assertTrue($artifact['safety_boundaries']['NO_OOS_PROOF']);
        @unlink($output);
    }

    public function test_it_blocks_when_expected_C42_hash_mismatches(): void
    {
        [$c42, $evidence, $output] = $this->fixturePaths('hash');
        $this->writeFixture($c42, $evidence);
        $result = $this->execute($c42, 'wrong-hash', $output);
        $this->assertSame('C43_BLOCKED_C42_HASH_MISMATCH', $result['status']);
        $this->assertFalse($result['c42_hash_match']);
        $this->cleanup($c42, $evidence, $output);
    }

    public function test_it_blocks_invalid_C42_status_conclusion_and_boundary_flags(): void
    {
        $cases = [
            ['status', 'C42_OPERATOR_VALIDATION_REQUIRED', 'C43_BLOCKED_UNEXPECTED_C42_STATUS'],
            ['diagnostic_conclusion', 'C42_VALIDATED_FOR_OOS', 'C43_BLOCKED_UNEXPECTED_C42_CONCLUSION'],
            ['production_ready', true, 'C43_BLOCKED_C42_PRODUCTION_READY_NOT_FALSE'],
            ['is_period.oos_data_used_for_tuning', true, 'C43_BLOCKED_C42_OOS_TUNING_FLAG_NOT_FALSE'],
            ['c42_decision_summary.direct_oos_proof_recommended', true, 'C43_BLOCKED_C42_DIRECT_OOS_FLAG_INVALID'],
            ['c42_decision_summary.oos_proof_unlocked', true, 'C43_BLOCKED_C42_OOS_UNLOCK_FLAG_INVALID'],
            ['c42_decision_summary.requires_c43_pre_trade_field_expansion_diagnostic', false, 'C43_BLOCKED_C42_DOES_NOT_REQUIRE_PRE_TRADE_FIELD_EXPANSION'],
        ];
        foreach ($cases as $idx => $case) {
            [$c42Path, $evidence, $output] = $this->fixturePaths('boundary-'.$idx);
            $c42 = $this->c42Artifact($evidence);
            $this->setNested($c42, $case[0], $case[1]);
            $c42['artifact_hash'] = $this->stableHash($c42);
            $this->writeJson($c42Path, $c42);
            $this->writeJson($evidence, $this->evidenceArtifact());
            $result = $this->execute($c42Path, $c42['artifact_hash'], $output);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($c42Path, $evidence, $output);
        }
    }

    public function test_it_blocks_when_IS_period_touches_reserved_OOS(): void
    {
        [$c42, $evidence, $output] = $this->fixturePaths('oos');
        $artifact = $this->writeFixture($c42, $evidence);
        $result = (new WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService())->execute(
            $c42, $artifact['artifact_hash'], '2023-01-02', '2025-05-22', $output,
            ['overwrite' => true, 'pre_trade_source_rows' => $this->preTradeRows()]
        );
        $this->assertSame('C43_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c42, $evidence, $output);
    }

    public function test_valid_C42_builds_all_required_C43_diagnostic_layers(): void
    {
        [$c42, $evidence, $output] = $this->fixturePaths('completed');
        $source = $this->writeFixture($c42, $evidence);
        $result = $this->execute($c42, $source['artifact_hash'], $output);
        $this->assertSame('C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->assertTrue($artifact['c42_hash_match']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertNotEmpty($artifact['source_c42_summary']);
        $this->assertNotEmpty($artifact['field_discovery_matrix']);
        $this->assertNotEmpty($artifact['timing_and_leakage_audit']);
        $this->assertNotEmpty($artifact['join_feasibility_matrix']);
        $this->assertNotEmpty($artifact['warning_cluster_enrichment']);
        $this->assertNotEmpty($artifact['cluster_field_explanation_table']);
        $this->assertNotEmpty($artifact['refinement_readiness_assessment']);
        $this->assertNotEmpty($artifact['guard_preservation_feasibility']);
        $this->assertNotEmpty($artifact['candidate_safety_audit']);
        $this->assertArrayHasKey('not_evaluable_reasons', $artifact);
        $this->assertSame('C43_SAFE_PRE_TRADE_FIELDS_FOUND_FOR_C44_REFINEMENT', $artifact['diagnostic_conclusion']);
        $this->assertSame('C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION', $artifact['next_step_recommendation']);
        $this->cleanup($c42, $evidence, $output);
    }

    public function test_field_classification_distinguishes_all_timing_and_leakage_classes(): void
    {
        $artifact = $this->completedArtifact('classes');
        $matrix = [];
        foreach ($artifact['field_discovery_matrix'] as $row) {
            $matrix[$row['field_name']] = $row;
        }
        $this->assertSame('SAFE_PRE_TRADE_SELECTION_FIELD', $matrix['trade_date']['field_classification']);
        $this->assertSame('SAFE_PRE_TRADE_JOINABLE_FIELD', $matrix['dv20_idr']['field_classification']);
        $this->assertSame('DIAGNOSTIC_ONLY_EVALUATION_FIELD', $matrix['profile_code']['field_classification']);
        $this->assertSame('UNSAFE_FUTURE_OR_RETURN_FIELD', $matrix['profile_ret_net']['field_classification']);
        $this->assertSame('UNSAFE_NEXT_OPEN_OR_EXECUTION_FIELD', $matrix['entry_open']['field_classification']);
        $this->assertSame('UNSAFE_DERIVED_FROM_EXIT_PATH', $matrix['mfe']['field_classification']);
        $this->assertSame('UNAVAILABLE_FIELD', $matrix['breadth_fields']['field_classification']);
        $this->assertSame('SOURCE_EXISTS_BUT_NOT_JOINED', $matrix['market_calendar_session_context']['field_classification']);
        $this->assertSame('SOURCE_EXISTS_BUT_TIMING_UNCLEAR', $matrix['raw_trading_status_event_notes']['field_classification']);
    }

    public function test_returns_next_open_and_exit_path_are_never_safe_selection_fields(): void
    {
        $artifact = $this->completedArtifact('unsafe');
        $unsafe = ['ret_net', 'avg_ret_net', 'profile_ret_net', 'delta_vs_raw_r09', 'entry_open', 'next_open', 'gap_open_diagnostic', 'mfe', 'mae', 'exit_result', 'future_path_price'];
        foreach ($artifact['field_discovery_matrix'] as $row) {
            if (in_array($row['field_name'], $unsafe, true)) {
                $this->assertFalse($row['safe_for_selection'], $row['field_name']);
            }
        }
        $this->assertFalse($artifact['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['oos_data_used_for_tuning']);
    }

    public function test_joined_EOD_fields_require_signal_date_as_of_rule_and_report_coverage(): void
    {
        $artifact = $this->completedArtifact('join');
        $rows = array_column($artifact['join_feasibility_matrix'], null, 'field_name');
        $this->assertTrue($rows['dv20_idr']['required_join_keys_available']);
        $this->assertTrue($rows['dv20_idr']['as_of_date_safe']);
        $this->assertEquals(1.0, $rows['dv20_idr']['coverage_pct']);
        $this->assertSame('SAFE_PRE_TRADE_JOINABLE_FIELD', $rows['rs_20_vs_ihsg']['field_classification']);
    }

    public function test_C43_never_forms_an_OOS_or_production_candidate(): void
    {
        $artifact = $this->completedArtifact('safety');
        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_catalog', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertFalse($artifact['c43_decision_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c43_decision_summary']['oos_proof_unlocked']);
        $this->assertTrue($artifact['safety_boundaries']['NO_PLAN_CONFIRM_MUTATION']);
    }

    private function completedArtifact(string $suffix): array
    {
        [$c42, $evidence, $output] = $this->fixturePaths($suffix);
        $source = $this->writeFixture($c42, $evidence);
        $this->execute($c42, $source['artifact_hash'], $output);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->cleanup($c42, $evidence, $output);
        return $artifact;
    }

    private function execute(string $c42, string $hash, string $output): array
    {
        return (new WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService())->execute(
            $c42, $hash, '2023-01-02', '2025-05-21', $output,
            ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'pre_trade_source_rows' => $this->preTradeRows()]
        );
    }

    private function writeFixture(string $c42Path, string $evidencePath): array
    {
        $c42 = $this->c42Artifact($evidencePath);
        $this->writeJson($c42Path, $c42);
        $this->writeJson($evidencePath, $this->evidenceArtifact());
        return $c42;
    }

    private function c42Artifact(string $sourceEvidence): array
    {
        $artifact = [
            'status' => WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService::EXPECTED_C42_STATUS,
            'production_ready' => false,
            'diagnostic_conclusion' => 'C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE',
            'next_step_recommendation' => 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC',
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_c41_summary' => ['target_candidate_code' => WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService::TARGET_CANDIDATE_CODE],
            'source_evidence_summary' => ['source_evidence_artifact' => $sourceEvidence],
            'warning_window_expansion' => [['suspected_warning_month' => '2024-03']],
            'warning_explanation_summary' => [
                'warning_interpretation' => 'STRUCTURAL_METADATA_QUOTA_WEAKNESS',
                'rolling_warning_explanation_result' => 'C42_ROLLING_WARNING_EXPLAINED',
                'normal_month_warning_explanation_result' => 'C42_NORMAL_MONTH_WARNING_EXPLAINED',
            ],
            'guard_preservation_audit' => ['c39_guard_preservation_result' => 'PASS'],
            'pre_trade_field_availability_matrix' => [['field_name' => 'trade_date', 'available' => true]],
            'c42_decision_summary' => [
                'safe_refinement_field_available' => false,
                'safe_refinement_candidate_formed' => false,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'requires_c43_pre_trade_field_expansion_diagnostic' => true,
                'production_ready' => false,
            ],
            'safety_boundaries' => ['oos_data_used_for_tuning' => false],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function evidenceArtifact(): array
    {
        $rows = [];
        foreach (['2023-04', '2023-05', '2024-03'] as $m => $month) {
            $rows[] = $this->row($month, 100 + $m, 'AAA'.$m, 'G16', 'next_open_delay_after_close_signal', 160, 0.02);
            $rows[] = $this->row($month, 110 + $m, 'BBB'.$m, 'G16', 'next_open_delay_after_close_signal', 161, 0.03);
            $rows[] = $this->row($month, 200 + $m, 'CCC'.$m, 'G21', 'no_rule_profit_signal_before_fallback', 210, $month === '2024-03' ? -0.04 : 0.01);
            $rows[] = $this->row($month, 210 + $m, 'DDD'.$m, 'G21', 'no_rule_profit_signal_before_fallback', 211, 0.02);
        }
        return ['pick_diagnostic_rows' => $rows];
    }

    private function row(string $month, int $tickerId, string $ticker, string $source, string $bucket, int $param, float $ret): array
    {
        return [
            'trade_date' => $month.'-10', 'trade_month' => $month, 'ticker_id' => $tickerId, 'ticker' => $ticker,
            'param_id' => $param, 'row_code' => 'ROW_'.$param, 'bucket_code' => $bucket, 'profile_code' => 'C28_TEST',
            'selected_source_code' => $source, 'profile_exit_reason' => 'diagnostic_exit', 'profile_ret_net' => $ret,
            'delta_vs_raw_r09' => $ret - 0.001, 'oos_executed' => false, 'production_ready' => 0,
        ];
    }

    private function preTradeRows(): array
    {
        $out = [];
        foreach ($this->evidenceArtifact()['pick_diagnostic_rows'] as $i => $row) {
            $out[] = [
                'trade_date' => $row['trade_date'], 'ticker_id' => $row['ticker_id'], 'ticker' => $row['ticker'],
                'signal_open' => 100, 'signal_high' => 105, 'signal_low' => 98, 'signal_close' => 103, 'signal_volume' => 1000000,
                'dv20_idr' => $i % 2 ? 6000000000 : 2000000000, 'atr14_pct' => $i % 2 ? 0.03 : 0.06,
                'vol_ratio' => $i % 2 ? 1.2 : 2.0, 'roc20' => $i % 2 ? -0.02 : 0.08, 'hh20' => 110,
                'ma20' => 100, 'ma50' => 95, 'close_to_hh20_pct' => -0.03, 'close_vs_ma20_pct' => 0.03,
                'close_vs_ma50_pct' => 0.08, 'ma20_slope_pct' => $i % 2 ? -0.01 : 0.02, 'rs_20_vs_ihsg' => $i % 2 ? -0.01 : 0.05,
                'rs_20_vs_sector' => 0.02, 'sector_roc20' => 0.03, 'sector_code' => $i % 2 ? 'A' : 'G',
                'sector_name' => $i % 2 ? 'Energy' : 'Financials', 'market_index_roc20' => 0.02,
                'market_index_ma20_slope_pct' => 0.01, 'eligibility_status' => 1, 'is_suspended' => 0,
                'is_uma' => 0, 'corporate_action_flag' => 0, 'event_risk_flag' => 0,
            ];
        }
        return $out;
    }

    private function fixturePaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c43-'.$suffix.'-'.uniqid('', true);
        return [$base.'-c42.json', $base.'-evidence.json', $base.'-output.json'];
    }

    private function path(string $name): string
    {
        return sys_get_temp_dir().'/c43-'.uniqid('', true).'-'.$name;
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

    private function setNested(array &$payload, string $path, $value): void
    {
        $parts = explode('.', $path);
        $cursor =& $payload;
        foreach ($parts as $idx => $part) {
            if ($idx === count($parts) - 1) {
                $cursor[$part] = $value;
                return;
            }
            $cursor =& $cursor[$part];
        }
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }
}
