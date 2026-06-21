<?php

use App\Application\Watchlist\Services\WatchlistBacktestC37IsValidationAntiOverfitCheckService;

class WatchlistBacktestC37IsValidationAntiOverfitCheckServiceTest extends TestCase
{
    public function test_it_blocks_when_C36_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c37-missing-c36-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            sys_get_temp_dir().'/missing-c36-artifact.json',
            WatchlistBacktestC37IsValidationAntiOverfitCheckService::DEFAULT_EXPECTED_C36_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C37_BLOCKED_MISSING_C36_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C37_C36_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C37_BLOCKED_MISSING_C36_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C36_hash_mismatches(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('hash-mismatch');
        $c36 = $this->c36Artifact($isPath);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            'wrong-c36-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_C36_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c36_hash_match']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C36_status_is_unexpected(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('status');
        $c36 = $this->c36Artifact($isPath);
        $c36['status'] = 'C36_OPERATOR_VALIDATION_REQUIRED';
        $c36['artifact_hash'] = $this->stableHash($c36);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_UNEXPECTED_C36_STATUS', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C36_diagnostic_conclusion_does_not_form_candidate(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('conclusion');
        $c36 = $this->c36Artifact($isPath);
        $c36['diagnostic_conclusion'] = 'C36_NO_CANDIDATE_FORMED';
        $c36['artifact_hash'] = $this->stableHash($c36);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_NO_C36_CANDIDATE_FORMED', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C36_production_ready_is_true(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('prod-ready');
        $c36 = $this->c36Artifact($isPath);
        $c36['production_ready'] = true;
        $c36['artifact_hash'] = $this->stableHash($c36);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_C36_PRODUCTION_READY_NOT_FALSE', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C36_oos_data_used_for_tuning_is_true(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('oos-tuning');
        $c36 = $this->c36Artifact($isPath);
        $c36['is_period']['oos_data_used_for_tuning'] = true;
        $c36['artifact_hash'] = $this->stableHash($c36);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_C36_OOS_TUNING_FLAG_NOT_FALSE', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C36_best_candidate_is_missing(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('missing-best');
        $c36 = $this->c36Artifact($isPath);
        $c36['candidate_summary']['best_is_candidate_code'] = 'C36_UNKNOWN_CANDIDATE';
        $c36['artifact_hash'] = $this->stableHash($c36);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_MISSING_C36_BEST_CANDIDATE', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C36_best_candidate_production_flag_is_invalid(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('best-prod-flag');
        $c36 = $this->c36Artifact($isPath);
        $c36['candidate_summary']['best_is_candidate_is_not_production'] = false;
        $c36['artifact_hash'] = $this->stableHash($c36);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_C36_BEST_CANDIDATE_PRODUCTION_FLAG_INVALID', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_period_touches_OOS_reserved(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('oos-period');
        $c36 = $this->c36Artifact($isPath);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_evidence_is_missing(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('missing-is');
        $c36 = $this->c36Artifact($isPath);
        $this->writeJson($c36Path, $c36);

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C37_BLOCKED_MISSING_IS_EVIDENCE', $result['status']);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    public function test_it_completes_C37_validation_when_C36_valid_and_IS_evidence_available(): void
    {
        [$c36Path, $isPath, $outputPath] = $this->tempPaths('completed');
        $c36 = $this->c36Artifact($isPath);
        $this->writeJson($c36Path, $c36);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC37IsValidationAntiOverfitCheckService())->execute(
            $c36Path,
            $c36['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c36_hash_match']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK', $artifact['run_code']);
        $this->assertSame('C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertTrue($artifact['source_c36_summary']['candidate_formed']);
        $this->assertSame('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', $artifact['source_c36_summary']['best_is_candidate_code']);
        $this->assertTrue($artifact['source_c36_summary']['best_is_candidate_is_not_production']);
        $this->assertSame('C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR', $artifact['validation_target']['baseline_candidate_code']);
        $this->assertSame('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', $artifact['validation_target']['target_candidate_code']);
        $this->assertTrue($artifact['validation_target']['target_candidate_is_not_production']);
        $this->assertGreaterThanOrEqual(9, $artifact['validation_summary']['total_validation_layers']);
        $this->assertContains($artifact['validation_summary']['overall_anti_overfit_result'], ['PASS', 'WARNING', 'FAIL', 'NOT_EVALUABLE']);
        $this->assertNotEmpty($artifact['full_is_validation']);
        $this->assertNotEmpty($artifact['yearly_validation']);
        $this->assertNotEmpty($artifact['rolling_window_validation']);
        $this->assertNotEmpty($artifact['bad_month_like_stress_validation']);
        $this->assertNotEmpty($artifact['non_bad_month_validation']);
        $this->assertNotEmpty($artifact['ticker_concentration_validation']);
        $this->assertNotEmpty($artifact['branch_concentration_validation']);
        $this->assertNotEmpty($artifact['month_coverage_validation']);
        $this->assertNotEmpty($artifact['downside_stability_validation']);
        $this->assertNotEmpty($artifact['candidate_comparison_table']);
        $this->assertNotEmpty($artifact['anti_overfit_summary']);
        $this->assertNotEmpty($artifact['candidate_safety_audit']);
        $this->assertNotEmpty($artifact['not_evaluable_reasons']);

        $target = $artifact['full_is_validation']['target_candidate'];
        $this->assertSame('EVALUATED', $target['candidate_status']);
        $this->assertFalse($target['return_used_for_selection']);
        $this->assertFalse($target['future_path_used_for_selection']);
        $this->assertFalse($target['oos_data_used_for_tuning']);
        $this->assertFalse($target['production_ready']);
        $this->assertTrue($target['candidate_is_not_production']);

        foreach ($artifact['candidate_safety_audit'] as $row) {
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
            $this->assertTrue($row['candidate_is_not_production']);
            $this->assertTrue($row['no_oos_proof']);
            $this->assertTrue($row['no_best_of_oos']);
            $this->assertTrue($row['no_production_catalog']);
            $this->assertTrue($row['no_plan_confirm_mutation']);
        }

        $this->assertFalse($artifact['anti_overfit_summary']['production_ready']);
        $this->assertFalse($artifact['anti_overfit_summary']['oos_data_used_for_tuning']);
        $this->assertFalse($artifact['anti_overfit_summary']['return_used_for_selection']);
        $this->assertFalse($artifact['anti_overfit_summary']['future_path_used_for_selection']);
        $this->assertTrue($artifact['anti_overfit_summary']['candidate_is_not_production']);
        $this->assertTrue($artifact['anti_overfit_summary']['no_oos_proof']);
        $this->assertTrue($artifact['anti_overfit_summary']['no_production_catalog']);
        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_catalog', $artifact);
        $this->assertArrayNotHasKey('production_candidate', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);
        $this->assertContains($artifact['diagnostic_conclusion'], [
            'C37_CANDIDATE_VALIDATED_FOR_OOS_PROOF_NEXT',
            'C37_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS',
            'C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK',
            'C37_CANDIDATE_TOO_SPARSE_FOR_VALIDATION',
        ]);
        $this->cleanup($c36Path, $isPath, $outputPath);
    }

    private function c36Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION',
            'status' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED',
            'artifact_type' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c35_summary' => [
                'g21_rows' => 6,
                'g16_rows' => 6,
                'g21_weakness_confirmed' => true,
                'g16_weakness_confirmed' => true,
                'source_evidence' => $isPath,
            ],
            'candidate_summary' => [
                'total_candidates' => 7,
                'evaluated_candidates' => 4,
                'not_evaluable_candidates' => 3,
                'candidate_formed' => true,
                'best_is_candidate_code' => 'C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR',
                'best_is_candidate_is_not_production' => true,
            ],
            'baseline_summary' => $this->candidate('C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR', 'BASELINE_COMPARATOR', 'G21_G16'),
            'candidate_results' => [
                $this->candidate('C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR', 'BASELINE_COMPARATOR', 'G21_G16'),
                $this->candidate('C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE', 'G21_PRIMARY_REDESIGN', 'G21'),
                $this->candidate('C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE', 'G16_SECONDARY_REDESIGN', 'G16'),
                $this->candidate('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', 'COMBINED_CONTROLLED_COMPARATOR', 'G21_G16'),
            ],
            'not_evaluable_reasons' => [
                ['candidate_code' => 'C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD', 'reason_code' => 'C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE'],
                ['candidate_code' => 'C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK', 'reason_code' => 'C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE'],
                ['candidate_code' => 'C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE', 'reason_code' => 'C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE'],
            ],
            'diagnostic_conclusion' => 'C36_COMBINED_CANDIDATE_FORMED',
            'next_step_recommendation' => 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function candidate(string $code, string $group, string $branch): array
    {
        return [
            'candidate_code' => $code,
            'candidate_group' => $group,
            'source_hypothesis' => 'C35_TEST_HYPOTHESIS',
            'source_branch' => $branch,
            'candidate_status' => 'EVALUATED',
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
            'candidate_is_not_production' => true,
        ];
    }

    private function isArtifact(): array
    {
        return [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_COMPLETED',
            'pick_diagnostic_rows' => [
                $this->row('2023-03-15', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAG21', -0.0300),
                $this->row('2023-03-20', '2023-03', 'G16', 'next_open_delay_after_close_signal', 'AAG16', 0.0200),
                $this->row('2023-07-15', '2023-07', 'G21', 'no_rule_profit_signal_before_fallback', 'BBG21', -0.0250),
                $this->row('2023-07-20', '2023-07', 'G16', 'next_open_delay_after_close_signal', 'BBG16', 0.0180),
                $this->row('2024-04-15', '2024-04', 'G21', 'no_rule_profit_signal_before_fallback', 'CCG21', -0.0280),
                $this->row('2024-04-20', '2024-04', 'G16', 'next_open_delay_after_close_signal', 'CCG16', 0.0190),
                $this->row('2024-07-15', '2024-07', 'G21', 'no_rule_profit_signal_before_fallback', 'DDG21', -0.0260),
                $this->row('2024-07-20', '2024-07', 'G16', 'next_open_delay_after_close_signal', 'DDG16', 0.0185),
                $this->row('2025-02-10', '2025-02', 'G21', 'no_rule_profit_signal_before_fallback', 'EEG21', -0.0310),
                $this->row('2025-02-12', '2025-02', 'G16', 'next_open_delay_after_close_signal', 'EEG16', 0.0210),
                $this->row('2025-04-10', '2025-04', 'G21', 'no_rule_profit_signal_before_fallback', 'FFG21', -0.0240),
                $this->row('2025-04-12', '2025-04', 'G16', 'next_open_delay_after_close_signal', 'FFG16', 0.0175),
            ],
        ];
    }

    private function row(string $date, string $month, string $source, string $bucket, string $ticker, float $ret): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $ticker,
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'profile_exit_reason' => $source === 'G21' ? 'raw_damage_control_no_profit_d2_exit_d3_open' : 'raw_preplanned_intraday_target_hit',
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
        $base = sys_get_temp_dir().'/c37-'.$suffix.'-'.uniqid();
        return [$base.'-c36.json', $base.'-is.json', $base.'-out.json'];
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
