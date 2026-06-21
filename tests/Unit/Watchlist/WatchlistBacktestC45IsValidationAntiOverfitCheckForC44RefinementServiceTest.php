<?php

use App\Application\Watchlist\Services\WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService;

class WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementServiceTest extends TestCase
{
    public function test_it_blocks_missing_C44_artifact(): void
    {
        $output = $this->path('missing-output.json');
        $result = (new WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService())->execute($this->path('missing-c44.json'), 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $this->assertSame('C45_BLOCKED_MISSING_C44_ARTIFACT', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        @unlink($output);
    }

    public function test_it_blocks_C44_hash_mismatch(): void
    {
        [$c44, $evidence, $output] = $this->fixturePaths('hash');
        $this->writeFixture($c44, $evidence);
        $result = $this->execute($c44, 'wrong-hash', $output);
        $this->assertSame('C45_BLOCKED_C44_HASH_MISMATCH', $result['status']);
        $this->assertFalse($result['c44_hash_match']);
        $this->cleanup($c44, $evidence, $output);
    }

    public function test_it_blocks_invalid_C44_contract_flags(): void
    {
        $cases = [
            ['status', 'C44_OPERATOR_VALIDATION_REQUIRED', 'C45_BLOCKED_UNEXPECTED_C44_STATUS'],
            ['diagnostic_conclusion', 'C44_NO_GUARD_REFINEMENT_CANDIDATE_FORMED', 'C45_BLOCKED_UNEXPECTED_C44_CONCLUSION'],
            ['next_step_recommendation', 'C45_PRE_TRADE_FIELD_REFINEMENT_CONTINUATION', 'C45_BLOCKED_UNEXPECTED_C44_NEXT_STEP'],
            ['production_ready', true, 'C45_BLOCKED_C44_SAFETY_FLAGS_INVALID'],
            ['is_period.oos_data_used_for_tuning', true, 'C45_BLOCKED_C44_SAFETY_FLAGS_INVALID'],
            ['c44_decision_summary.direct_oos_proof_recommended', true, 'C45_BLOCKED_C44_OOS_FLAGS_INVALID'],
            ['c44_decision_summary.oos_proof_unlocked', true, 'C45_BLOCKED_C44_OOS_FLAGS_INVALID'],
            ['c44_decision_summary.requires_c45_is_validation_and_anti_overfit_check', false, 'C45_BLOCKED_C44_VALIDATION_NOT_REQUIRED'],
            ['candidate_summary.best_candidate_requires_c45_validation', false, 'C45_BLOCKED_C44_VALIDATION_NOT_REQUIRED'],
        ];
        foreach ($cases as $index => $case) {
            [$path, $evidence, $output] = $this->fixturePaths('boundary-'.$index);
            $c44 = $this->c44Artifact($evidence);
            $this->setNested($c44, $case[0], $case[1]);
            $c44['artifact_hash'] = $this->stableHash($c44);
            $this->writeJson($path, $c44);
            $this->writeJson($evidence, $this->evidenceArtifact());
            $result = $this->execute($path, $c44['artifact_hash'], $output);
            $this->assertSame($case[2], $result['status'], $case[0]);
            $this->cleanup($path, $evidence, $output);
        }
    }

    public function test_it_blocks_period_touching_reserved_OOS(): void
    {
        [$c44, $evidence, $output] = $this->fixturePaths('oos');
        $source = $this->writeFixture($c44, $evidence);
        $result = (new WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService())->execute($c44, $source['artifact_hash'], '2023-01-02', '2025-05-22', $output, ['overwrite' => true, 'pre_trade_source_rows' => $this->preTradeRows()]);
        $this->assertSame('C45_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c44, $evidence, $output);
    }

    public function test_valid_C44_candidate_completes_all_independent_IS_layers(): void
    {
        $artifact = $this->completedArtifact('completed');
        $this->assertSame('C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT_COMPLETED', $artifact['status']);
        $this->assertSame(9, $artifact['validation_summary']['total_validation_layers']);
        $this->assertContains($artifact['validation_summary']['overall_anti_overfit_result'], ['PASS', 'WARNING']);
        $this->assertNotEmpty($artifact['yearly_validation']['slices']);
        $this->assertNotEmpty($artifact['rolling_window_validation']['slices']);
        $this->assertSame([6, 9, 12], $artifact['rolling_window_validation']['window_lengths_months']);
        $this->assertNotEmpty($artifact['candidate_comparison_table']);
    }

    public function test_reconstruction_preserves_C44_selection_guards_and_no_OOS_boundary(): void
    {
        $artifact = $this->completedArtifact('guards');
        $this->assertTrue($artifact['validation_target']['c44_selected_rows_match']);
        $this->assertTrue($artifact['validation_target']['c44_selected_g21_rows_match']);
        $this->assertSame('PASS', $artifact['month_coverage_validation']['result']);
        $this->assertSame('PASS', $artifact['branch_concentration_validation']['result']);
        $this->assertSame(0, $artifact['month_coverage_validation']['target_zero_pick_month_count']);
        $this->assertFalse($artifact['validation_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['validation_summary']['oos_proof_unlocked']);
        $this->assertTrue($artifact['validation_summary']['requires_human_review_before_any_oos_step']);
        $this->assertFalse($artifact['production_ready']);
    }

    public function test_validation_uses_returns_only_for_evaluation_and_keeps_candidate_non_production(): void
    {
        $artifact = $this->completedArtifact('safety');
        $audit = $artifact['candidate_safety_audit'];
        $this->assertTrue($audit['selection_reconstructed_from_locked_c44_rule']);
        $this->assertFalse($audit['return_used_for_selection']);
        $this->assertFalse($audit['future_path_used_for_selection']);
        $this->assertFalse($audit['oos_data_used_for_tuning']);
        $this->assertFalse($audit['oos_proof_executed']);
        $this->assertTrue($audit['candidate_is_not_production']);
        $this->assertFalse($audit['production_ready']);
    }

    private function completedArtifact(string $suffix): array
    {
        [$c44, $evidence, $output] = $this->fixturePaths($suffix);
        $source = $this->writeFixture($c44, $evidence);
        $this->execute($c44, $source['artifact_hash'], $output);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->cleanup($c44, $evidence, $output);
        return $artifact;
    }

    private function execute(string $c44, string $hash, string $output): array
    {
        return (new WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService())->execute($c44, $hash, '2023-01-02', '2025-05-21', $output, [
            'overwrite' => true,
            'executed_at' => '2026-06-21T00:00:00+00:00',
            'pre_trade_source_rows' => $this->preTradeRows(),
        ]);
    }

    private function writeFixture(string $c44Path, string $evidencePath): array
    {
        $c44 = $this->c44Artifact($evidencePath);
        $this->writeJson($c44Path, $c44);
        $this->writeJson($evidencePath, $this->evidenceArtifact());
        return $c44;
    }

    private function c44Artifact(string $evidence): array
    {
        $artifact = [
            'status' => WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService::EXPECTED_C44_STATUS,
            'diagnostic_conclusion' => WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService::EXPECTED_C44_CONCLUSION,
            'next_step_recommendation' => WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService::EXPECTED_C44_NEXT_STEP,
            'production_ready' => false,
            'is_period' => ['from' => '2023-01-02', 'to' => '2025-05-21', 'oos_data_used_for_tuning' => false],
            'source_evidence_summary' => ['source_evidence_artifact' => $evidence, 'monthly_g21_quota' => 1],
            'candidate_summary' => [
                'candidate_formed' => true,
                'best_is_candidate_code' => WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService::TARGET_CANDIDATE_CODE,
                'best_is_candidate_is_not_production' => true,
                'best_candidate_requires_c45_validation' => true,
                'advancement_gate_pass_count' => 1,
            ],
            'candidate_results' => [[
                'candidate_code' => WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService::TARGET_CANDIDATE_CODE,
                'selected_rows' => 18,
                'selected_g21_rows' => 6,
                'all_required_guards_passed' => true,
                'advancement_gate' => ['passed' => true],
                'candidate_is_not_production' => true,
                'production_ready' => false,
                'selection_rule' => 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota',
                'minimum_monthly_rows_guard' => ['required_minimum' => 3],
            ]],
            'c44_decision_summary' => [
                'c39_guard_coverage_preserved' => true,
                'c39_branch_diversification_preserved' => true,
                'requires_c45_is_validation_and_anti_overfit_check' => true,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'production_ready' => false,
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function evidenceArtifact(): array
    {
        $rows = [];
        foreach (['2023-03', '2023-06', '2023-09', '2024-03', '2024-06', '2025-03'] as $index => $month) {
            $rows[] = $this->row($month.'-15', 100 + $index, 'G16A'.$index, 'G16', 160, 0.01);
            $rows[] = $this->row($month.'-20', 110 + $index, 'G16B'.$index, 'G16', 161, 0.02);
            $rows[] = $this->row($month.'-05', 200 + $index, 'A_BAD'.$index, 'G21', 210, -0.03);
            $rows[] = $this->row($month.'-10', 210 + $index, 'Z_GOOD'.$index, 'G21', 211, 0.04);
        }
        return ['pick_diagnostic_rows' => $rows];
    }

    private function row(string $date, int $tickerId, string $ticker, string $source, int $param, float $ret): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => substr($date, 0, 7),
            'ticker_id' => $tickerId,
            'ticker' => $ticker,
            'param_id' => $param,
            'row_code' => 'ROW_'.$param,
            'selected_source_code' => $source,
            'bucket_code' => $source === 'G21' ? 'no_rule_profit_signal_before_fallback' : 'next_open_delay_after_close_signal',
            'profile_ret_net' => $ret,
            'oos_executed' => false,
            'production_ready' => 0,
        ];
    }

    private function preTradeRows(): array
    {
        $out = [];
        foreach ($this->evidenceArtifact()['pick_diagnostic_rows'] as $row) {
            if ($row['selected_source_code'] !== 'G21') {
                continue;
            }
            $out[] = [
                'trade_date' => $row['trade_date'],
                'ticker_id' => $row['ticker_id'],
                'ticker' => $row['ticker'],
                'market_index_roc20' => strpos($row['ticker'], 'Z_GOOD') === 0 ? 0.05 : 0.20,
            ];
        }
        return $out;
    }

    private function fixturePaths(string $suffix): array { $base = sys_get_temp_dir().'/c45-'.$suffix.'-'.uniqid('', true); return [$base.'-c44.json', $base.'-evidence.json', $base.'-output.json']; }
    private function path(string $name): string { return sys_get_temp_dir().'/c45-'.uniqid('', true).'-'.$name; }
    private function writeJson(string $path, array $payload): void { file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n"); }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function setNested(array &$payload, string $path, $value): void { $cursor =& $payload; $parts = explode('.', $path); foreach ($parts as $index => $part) { if ($index === count($parts) - 1) { $cursor[$part] = $value; return; } $cursor =& $cursor[$part]; } }
    private function cleanup(string ...$paths): void { foreach ($paths as $path) { @unlink($path); } }
}
