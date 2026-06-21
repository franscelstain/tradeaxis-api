<?php

use App\Application\Watchlist\Services\WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService;

class WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateServiceTest extends TestCase
{
    public function test_it_blocks_when_C39_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c40-missing-c39-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            sys_get_temp_dir().'/missing-c39-artifact.json',
            WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService::DEFAULT_EXPECTED_C39_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C40_BLOCKED_MISSING_C39_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C40_C39_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C39_hash_mismatches(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('hash-mismatch');
        $c39 = $this->c39Artifact($isPath);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            'wrong-c39-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_C39_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c39_hash_match']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C39_status_is_unexpected(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('status');
        $c39 = $this->c39Artifact($isPath);
        $c39['status'] = 'C39_OPERATOR_VALIDATION_REQUIRED';
        $c39['artifact_hash'] = $this->stableHash($c39);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_UNEXPECTED_C39_STATUS', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C39_did_not_form_guarded_candidate(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('conclusion');
        $c39 = $this->c39Artifact($isPath);
        $c39['diagnostic_conclusion'] = 'C39_NO_GUARDED_IS_CANDIDATE_FORMED';
        $c39['artifact_hash'] = $this->stableHash($c39);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_NO_C39_CANDIDATE_FORMED', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C39_next_step_is_unexpected(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('next-step');
        $c39 = $this->c39Artifact($isPath);
        $c39['next_step_recommendation'] = 'C40_OOS_PROOF';
        $c39['artifact_hash'] = $this->stableHash($c39);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_UNEXPECTED_C39_NEXT_STEP', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C39_best_candidate_is_not_marked_for_C40_validation(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('validation-flag');
        $c39 = $this->c39Artifact($isPath);
        $c39['candidate_summary']['best_candidate_requires_C40_validation'] = false;
        $c39['artifact_hash'] = $this->stableHash($c39);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_C39_BEST_CANDIDATE_VALIDATION_FLAG_INVALID', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C39_best_candidate_did_not_pass_guards(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('guards');
        $c39 = $this->c39Artifact($isPath);
        foreach ($c39['candidate_results'] as &$candidate) {
            if (($candidate['candidate_code'] ?? null) === 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA') {
                $candidate['all_required_guards_passed'] = false;
            }
        }
        unset($candidate);
        $c39['artifact_hash'] = $this->stableHash($c39);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_C39_BEST_CANDIDATE_GUARDS_NOT_PASSED', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_period_touches_reserved_OOS_window(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('oos-window');
        $c39 = $this->c39Artifact($isPath);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_evidence_is_missing(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('missing-is');
        $c39 = $this->c39Artifact($isPath);
        $this->writeJson($c39Path, $c39);

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C40_BLOCKED_MISSING_IS_EVIDENCE', $result['status']);
        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    public function test_it_completes_C40_validation_for_guarded_C39_candidate(): void
    {
        [$c39Path, $isPath, $outputPath] = $this->tempPaths('completed');
        $c39 = $this->c39Artifact($isPath);
        $this->writeJson($c39Path, $c39);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC40IsValidationAntiOverfitCheckForC39GuardedCandidateService())->execute(
            $c39Path,
            $c39['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c39_hash_match']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertSame('C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA', $artifact['validation_target']['target_candidate_code']);
        $this->assertSame(9, $artifact['validation_summary']['total_validation_layers']);
        $this->assertContains($artifact['anti_overfit_summary']['overall_anti_overfit_result'], ['PASS', 'WARNING', 'FAIL', 'NOT_EVALUABLE']);
        $this->assertFalse($artifact['anti_overfit_summary']['production_ready']);
        $this->assertTrue($artifact['anti_overfit_summary']['no_oos_proof']);
        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);

        foreach ($artifact['candidate_safety_audit'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
            $this->assertTrue($row['no_oos_proof']);
            $this->assertTrue($row['no_best_of_oos']);
            $this->assertTrue($row['no_production_catalog']);
        }

        $this->cleanup($c39Path, $isPath, $outputPath);
    }

    private function c39Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
            'status' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED',
            'artifact_type' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c38_summary' => [
                'source_evidence' => $isPath,
                'g21_rows' => 5,
                'g16_rows' => 8,
            ],
            'candidate_summary' => [
                'candidate_formed' => true,
                'best_is_candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                'best_is_candidate_is_not_production' => true,
                'best_candidate_requires_C40_validation' => true,
            ],
            'candidate_results' => [
                [
                    'candidate_code' => 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
                    'candidate_status' => 'EVALUATED',
                    'source_branch' => 'G21_G16',
                    'selected_rows' => 13,
                    'selection_rule' => 'keep_G16_and_reintroduce_metadata_sorted_G21_monthly_quota_until_top_branch_share_limit_is_met',
                    'all_required_guards_passed' => true,
                    'candidate_is_not_production' => true,
                    'production_ready' => false,
                ],
            ],
            'guard_validation_summary' => [
                'best_candidate_zero_pick_month_count' => 0,
                'best_candidate_month_coverage_passed' => true,
                'best_candidate_branch_diversification_passed' => true,
                'best_candidate_top_branch_share' => 0.6153846153846154,
            ],
            'not_evaluable_reasons' => [
                ['validation_slice' => 'C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED', 'reason_code' => 'C39_BLOCKED_G21_PRE_TRADE_QUALITY_FIELD_UNAVAILABLE'],
            ],
            'diagnostic_conclusion' => 'C39_GUARDED_IS_CANDIDATE_FORMED',
            'next_step_recommendation' => 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function isArtifact(): array
    {
        $rows = [];
        for ($i = 1; $i <= 4; $i++) {
            $rows[] = $this->row('2024-04-0'.$i, '2024-04', 'G16', 'next_open_delay_after_close_signal', 0.0200, 100 + $i);
            $rows[] = $this->row('2024-06-0'.$i, '2024-06', 'G16', 'next_open_delay_after_close_signal', 0.0200, 200 + $i);
        }
        $rows[] = $this->row('2024-04-15', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 301);
        $rows[] = $this->row('2024-04-16', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 302);
        $rows[] = $this->row('2024-05-15', '2024-05', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 401);
        $rows[] = $this->row('2024-06-15', '2024-06', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 501);
        $rows[] = $this->row('2024-06-16', '2024-06', 'G21', 'no_rule_profit_signal_before_fallback', -0.0100, 502);

        return [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_COMPLETED',
            'pick_diagnostic_rows' => $rows,
        ];
    }

    private function row(string $date, string $month, string $source, string $bucket, float $ret, int $paramId): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $source.$month.$paramId,
            'param_id' => $paramId,
            'row_code' => 'TEST_'.$paramId,
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'profile_code' => 'TEST_'.$source.'_'.$paramId,
            'profile_ret_net' => $ret,
            'oos_executed' => false,
            'production_ready' => 0,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c40-validation-'.$suffix.'-'.uniqid();
        return [$base.'-c39.json', $base.'-is.json', $base.'-out.json'];
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
