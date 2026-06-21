<?php

use App\Application\Watchlist\Services\WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService;

class WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsServiceTest extends TestCase
{
    public function test_it_blocks_when_C38_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c39-missing-c38-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            sys_get_temp_dir().'/missing-c38-artifact.json',
            WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService::DEFAULT_EXPECTED_C38_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C39_BLOCKED_MISSING_C38_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C39_C38_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C38_hash_mismatches(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('hash-mismatch');
        $c38 = $this->c38Artifact($isPath);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            'wrong-c38-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_C38_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c38_hash_match']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C38_status_is_unexpected(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('status');
        $c38 = $this->c38Artifact($isPath);
        $c38['status'] = 'C38_OPERATOR_VALIDATION_REQUIRED';
        $c38['artifact_hash'] = $this->stableHash($c38);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_UNEXPECTED_C38_STATUS', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C38_conclusion_is_unexpected(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('conclusion');
        $c38 = $this->c38Artifact($isPath);
        $c38['diagnostic_conclusion'] = 'C38_DIRECT_OOS_PROOF_READY';
        $c38['artifact_hash'] = $this->stableHash($c38);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_UNEXPECTED_C38_CONCLUSION', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C38_next_step_is_unexpected(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('next-step');
        $c38 = $this->c38Artifact($isPath);
        $c38['next_step_recommendation'] = 'C39_OOS_PROOF';
        $c38['artifact_hash'] = $this->stableHash($c38);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_UNEXPECTED_C38_NEXT_STEP', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C38_production_ready_is_true(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('prod-ready');
        $c38 = $this->c38Artifact($isPath);
        $c38['production_ready'] = true;
        $c38['artifact_hash'] = $this->stableHash($c38);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_C38_PRODUCTION_READY_NOT_FALSE', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C38_direct_oos_flag_is_true(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('direct-oos');
        $c38 = $this->c38Artifact($isPath);
        $c38['c38_decision_summary']['direct_oos_proof_recommended'] = true;
        $c38['artifact_hash'] = $this->stableHash($c38);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_C38_DIRECT_OOS_PROOF_NOT_FALSE', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C38_guard_requirements_are_missing(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('missing-reqs');
        $c38 = $this->c38Artifact($isPath);
        $c38['evidence_expansion_requirements'] = [];
        $c38['artifact_hash'] = $this->stableHash($c38);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_MISSING_C38_GUARD_REQUIREMENTS', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_period_touches_reserved_OOS_window(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('oos-window');
        $c38 = $this->c38Artifact($isPath);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_evidence_is_missing(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('missing-is');
        $c38 = $this->c38Artifact($isPath);
        $this->writeJson($c38Path, $c38);

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C39_BLOCKED_MISSING_IS_EVIDENCE', $result['status']);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    public function test_it_forms_guarded_C39_candidate_when_C38_valid_and_IS_evidence_available(): void
    {
        [$c38Path, $isPath, $outputPath] = $this->tempPaths('completed');
        $c38 = $this->c38Artifact($isPath);
        $this->writeJson($c38Path, $c38);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService())->execute(
            $c38Path,
            $c38['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c38_hash_match']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS', $artifact['run_code']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertTrue($artifact['candidate_summary']['candidate_formed']);
        $this->assertTrue($artifact['candidate_summary']['best_is_candidate_is_not_production']);
        $this->assertTrue($artifact['candidate_summary']['best_candidate_requires_C40_validation']);
        $this->assertSame('C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA', $artifact['candidate_summary']['best_is_candidate_code']);
        $this->assertSame(0, $artifact['guard_validation_summary']['best_candidate_zero_pick_month_count']);
        $this->assertTrue($artifact['guard_validation_summary']['best_candidate_month_coverage_passed']);
        $this->assertTrue($artifact['guard_validation_summary']['best_candidate_branch_diversification_passed']);
        $this->assertLessThanOrEqual(0.80, $artifact['guard_validation_summary']['best_candidate_top_branch_share']);
        $this->assertSame('C39_GUARDED_IS_CANDIDATE_FORMED', $artifact['diagnostic_conclusion']);
        $this->assertSame('C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE', $artifact['next_step_recommendation']);

        foreach ($artifact['candidate_results'] as $candidate) {
            $this->assertFalse($candidate['return_used_for_selection']);
            $this->assertFalse($candidate['future_path_used_for_selection']);
            $this->assertFalse($candidate['oos_data_used_for_tuning']);
            $this->assertFalse($candidate['production_ready']);
            $this->assertTrue($candidate['candidate_is_not_production']);
        }

        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);
        $this->cleanup($c38Path, $isPath, $outputPath);
    }

    private function c38Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC',
            'status' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c37_summary' => [
                'overall_anti_overfit_result' => 'FAIL',
                'candidate_c37_decision' => 'C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION',
                'source_evidence' => $isPath,
                'g21_rows' => 5,
                'g16_rows' => 8,
            ],
            'month_coverage_failure_diagnostic' => [
                'coverage_failure_confirmed' => true,
                'zero_pick_months' => ['2024-05'],
                'zero_pick_month_count' => 1,
                'result' => 'CONFIRMED_REDESIGN_REQUIRED',
            ],
            'branch_concentration_diagnostic' => [
                'branch_concentration_confirmed' => true,
                'result' => 'CONFIRMED_BRANCH_DIVERSIFICATION_REQUIRED',
            ],
            'rolling_warning_diagnostic' => [
                'rolling_warning_confirmed' => true,
                'warning_or_fail_windows' => [
                    ['validation_slice' => '2024-04_to_2024-06', 'result' => 'WARNING'],
                ],
                'result' => 'CONFIRMED_ROLLING_STABILITY_REVIEW_REQUIRED',
            ],
            'evidence_expansion_requirements' => [
                ['requirement_code' => 'C38_REQ_MONTH_COVERAGE_GUARD', 'priority' => 'HIGH'],
                ['requirement_code' => 'C38_REQ_BRANCH_DIVERSIFICATION_GUARD', 'priority' => 'HIGH'],
                ['requirement_code' => 'C38_REQ_ROLLING_STABILITY_EXPANSION', 'priority' => 'MEDIUM'],
            ],
            'c38_decision_summary' => [
                'direct_oos_proof_recommended' => false,
                'new_candidate_selected' => false,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => 'C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS',
            'next_step_recommendation' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
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
        $base = sys_get_temp_dir().'/c39-guard-'.$suffix.'-'.uniqid();
        return [$base.'-c38.json', $base.'-is.json', $base.'-out.json'];
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
