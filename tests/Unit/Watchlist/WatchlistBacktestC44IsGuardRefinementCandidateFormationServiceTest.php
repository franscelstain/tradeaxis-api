<?php

use App\Application\Watchlist\Services\WatchlistBacktestC44IsGuardRefinementCandidateFormationService;

class WatchlistBacktestC44IsGuardRefinementCandidateFormationServiceTest extends TestCase
{
    public function test_it_blocks_missing_C43_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC44IsGuardRefinementCandidateFormationService())->execute($this->path('missing-c43.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C44_BLOCKED_MISSING_C43_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_C43_hash_mismatch(): void
    {
        [$c43, $evidence, $output] = $this->fixturePaths('hash'); $this->writeFixture($c43, $evidence);
        $result = $this->execute($c43, 'wrong-hash', $output);
        $this->assertSame('C44_BLOCKED_C43_HASH_MISMATCH', $result['status']);
        $this->assertFalse($result['c43_hash_match']); $this->cleanup($c43, $evidence, $output);
    }

    public function test_it_blocks_invalid_C43_contract_flags(): void
    {
        $cases = [
            ['status', 'C43_OPERATOR_VALIDATION_REQUIRED', 'C44_BLOCKED_UNEXPECTED_C43_STATUS'],
            ['diagnostic_conclusion', 'C43_NO_SAFE_PRE_TRADE_FIELD_AVAILABLE', 'C44_BLOCKED_UNEXPECTED_C43_CONCLUSION'],
            ['next_step_recommendation', 'C44_PRE_TRADE_FIELD_DATA_PLUMBING', 'C44_BLOCKED_UNEXPECTED_C43_NEXT_STEP'],
            ['production_ready', true, 'C44_BLOCKED_C43_PRODUCTION_READY_NOT_FALSE'],
            ['is_period.oos_data_used_for_tuning', true, 'C44_BLOCKED_C43_OOS_TUNING_FLAG_NOT_FALSE'],
            ['c43_decision_summary.direct_oos_proof_recommended', true, 'C44_BLOCKED_C43_OOS_FLAGS_INVALID'],
            ['c43_decision_summary.oos_proof_unlocked', true, 'C44_BLOCKED_C43_OOS_FLAGS_INVALID'],
            ['c43_decision_summary.requires_c44_guard_refinement_candidate_formation', false, 'C44_BLOCKED_C43_DOES_NOT_REQUIRE_CANDIDATE_FORMATION'],
        ];
        foreach ($cases as $idx => $case) {
            [$path, $evidence, $output] = $this->fixturePaths('boundary-'.$idx); $c43 = $this->c43Artifact($evidence);
            $this->setNested($c43, $case[0], $case[1]); $c43['artifact_hash'] = $this->stableHash($c43);
            $this->writeJson($path, $c43); $this->writeJson($evidence, $this->evidenceArtifact());
            $result = $this->execute($path, $c43['artifact_hash'], $output);
            $this->assertSame($case[2], $result['status'], $case[0]); $this->cleanup($path, $evidence, $output);
        }
    }

    public function test_it_blocks_period_touching_OOS(): void
    {
        [$c43, $evidence, $output] = $this->fixturePaths('oos'); $source = $this->writeFixture($c43, $evidence);
        $result = (new WatchlistBacktestC44IsGuardRefinementCandidateFormationService())->execute($c43, $source['artifact_hash'], '2023-01-02', '2025-05-22', $output, ['overwrite' => true, 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C44_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']); $this->cleanup($c43, $evidence, $output);
    }

    public function test_valid_C43_forms_fixed_quota_refinement_candidates(): void
    {
        $artifact = $this->completedArtifact('completed');
        $this->assertSame('C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED', $artifact['status']);
        $this->assertSame('C44_GUARD_REFINEMENT_CANDIDATE_FORMED', $artifact['diagnostic_conclusion']);
        $this->assertCount(7, $artifact['candidate_results']);
        $this->assertTrue($artifact['candidate_summary']['candidate_formed']);
        $this->assertNotSame(WatchlistBacktestC44IsGuardRefinementCandidateFormationService::BASELINE_CANDIDATE_CODE, $artifact['candidate_summary']['best_is_candidate_code']);
        $this->assertSame('C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT', $artifact['next_step_recommendation']);
    }

    public function test_every_candidate_preserves_fixed_quota_and_C39_guards(): void
    {
        $artifact = $this->completedArtifact('guards');
        foreach ($artifact['candidate_results'] as $row) {
            $this->assertTrue($row['all_required_guards_passed'], $row['candidate_code']);
            $this->assertTrue($row['month_coverage_guard']['passed']);
            $this->assertSame(0, $row['month_coverage_guard']['zero_pick_month_count']);
            $this->assertTrue($row['branch_diversification_guard']['passed']);
            $this->assertGreaterThan(0, $row['branch_diversification_guard']['g21_share']);
            $this->assertSame(3, $row['selected_g21_rows']);
        }
    }

    public function test_candidate_selection_fields_are_safe_and_exclude_returns_future_path_and_OOS(): void
    {
        $artifact = $this->completedArtifact('safety');
        foreach ($artifact['candidate_results'] as $row) {
            $this->assertTrue($row['selection_input_safety_check']['all_fields_safe_pre_trade']);
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
        }
        $this->assertFalse($artifact['c44_decision_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c44_decision_summary']['oos_proof_unlocked']);
        $this->assertFalse($artifact['production_ready']);
    }

    public function test_artifact_contains_comparison_guard_summary_and_safety_audit(): void
    {
        $artifact = $this->completedArtifact('layers');
        $this->assertNotEmpty($artifact['candidate_comparison_table']);
        $this->assertNotEmpty($artifact['guard_preservation_summary']);
        $this->assertNotEmpty($artifact['candidate_safety_audit']);
        $this->assertTrue($artifact['guard_preservation_summary']['c39_coverage_guard_preserved']);
        $this->assertTrue($artifact['guard_preservation_summary']['c39_branch_guard_preserved']);
        $this->assertTrue($artifact['c44_decision_summary']['requires_c45_is_validation_and_anti_overfit_check']);
    }

    private function completedArtifact(string $suffix): array
    {
        [$c43, $evidence, $output] = $this->fixturePaths($suffix); $source = $this->writeFixture($c43, $evidence);
        $this->execute($c43, $source['artifact_hash'], $output); $artifact = json_decode((string) file_get_contents($output), true);
        $this->cleanup($c43, $evidence, $output); return $artifact;
    }

    private function execute(string $c43, string $hash, string $output): array
    {
        return (new WatchlistBacktestC44IsGuardRefinementCandidateFormationService())->execute($c43, $hash, '2023-01-02', '2025-05-21', $output, ['overwrite' => true, 'executed_at' => '2026-06-21T00:00:00+00:00', 'pre_trade_source_rows' => $this->preTradeRows()]);
    }

    private function writeFixture(string $c43Path, string $evidencePath): array
    {
        $c43 = $this->c43Artifact($evidencePath); $this->writeJson($c43Path, $c43); $this->writeJson($evidencePath, $this->evidenceArtifact()); return $c43;
    }

    private function c43Artifact(string $evidence): array
    {
        $artifact = [
            'status' => WatchlistBacktestC44IsGuardRefinementCandidateFormationService::EXPECTED_C43_STATUS,
            'diagnostic_conclusion' => WatchlistBacktestC44IsGuardRefinementCandidateFormationService::EXPECTED_C43_CONCLUSION,
            'next_step_recommendation' => WatchlistBacktestC44IsGuardRefinementCandidateFormationService::EXPECTED_C43_NEXT_STEP,
            'production_ready' => false, 'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_c42_summary' => ['target_candidate_code' => WatchlistBacktestC44IsGuardRefinementCandidateFormationService::TARGET_CANDIDATE_CODE, 'suspected_warning_month' => '2024-03'],
            'source_evidence_summary' => ['source_evidence_artifact' => $evidence],
            'refinement_readiness_assessment' => ['refinement_readiness_result' => 'C43_SAFE_PRE_TRADE_FIELDS_READY_FOR_C44_CANDIDATE_FORMATION', 'safe_fields_for_future_refinement' => ['dv20_idr','atr14_pct','rs_20_vs_ihsg','rs_20_vs_sector','sector_roc20','market_index_roc20'], 'cluster_supporting_fields' => ['atr14_pct','sector_roc20','market_index_roc20']],
            'c43_decision_summary' => ['requires_c44_guard_refinement_candidate_formation' => true, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => ['oos_data_used_for_tuning' => false],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact); return $artifact;
    }

    private function evidenceArtifact(): array
    {
        $rows = [];
        foreach (['2023-04','2023-05','2024-03'] as $m => $month) {
            $rows[] = $this->row($month, 100+$m, 'G16A'.$m, 'G16', 160, 0.02);
            $rows[] = $this->row($month, 110+$m, 'G16B'.$m, 'G16', 161, 0.03);
            $rows[] = $this->row($month, 200+$m, 'A_BAD'.$m, 'G21', 210, -0.04);
            $rows[] = $this->row($month, 210+$m, 'Z_GOOD'.$m, 'G21', 211, 0.03);
        }
        return ['pick_diagnostic_rows' => $rows];
    }

    private function row(string $month, int $tickerId, string $ticker, string $source, int $param, float $ret): array
    {
        return ['trade_date' => $month.'-10', 'trade_month' => $month, 'ticker_id' => $tickerId, 'ticker' => $ticker, 'param_id' => $param, 'row_code' => 'ROW_'.$param,
            'selected_source_code' => $source, 'bucket_code' => $source === 'G21' ? 'no_rule_profit_signal_before_fallback' : 'next_open_delay_after_close_signal',
            'profile_ret_net' => $ret, 'oos_executed' => false, 'production_ready' => 0];
    }

    private function preTradeRows(): array
    {
        $out = [];
        foreach ($this->evidenceArtifact()['pick_diagnostic_rows'] as $row) {
            if ($row['selected_source_code'] !== 'G21') { continue; }
            $good = strpos($row['ticker'], 'Z_GOOD') === 0;
            $out[] = ['trade_date' => $row['trade_date'], 'ticker_id' => $row['ticker_id'], 'ticker' => $row['ticker'], 'dv20_idr' => $good ? 6000000000 : 1000000000,
                'atr14_pct' => $good ? 0.01 : 0.03, 'roc20' => $good ? 0.05 : 0.15, 'rs_20_vs_ihsg' => $good ? 0.10 : -0.05,
                'rs_20_vs_sector' => $good ? 0.08 : -0.03, 'sector_roc20' => $good ? 0.06 : -0.02, 'market_index_roc20' => 0.05];
        }
        return $out;
    }

    private function fixturePaths(string $suffix): array { $base = sys_get_temp_dir().'/c44-'.$suffix.'-'.uniqid('', true); return [$base.'-c43.json',$base.'-evidence.json',$base.'-output.json']; }
    private function path(string $name): string { return sys_get_temp_dir().'/c44-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $idx => $part) { if ($idx === count($parts)-1) { $cursor[$part] = $value; return; } $cursor =& $cursor[$part]; } }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
