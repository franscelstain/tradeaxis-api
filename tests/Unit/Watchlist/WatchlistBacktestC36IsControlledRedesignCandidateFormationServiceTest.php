<?php

use App\Application\Watchlist\Services\WatchlistBacktestC36IsControlledRedesignCandidateFormationService;

class WatchlistBacktestC36IsControlledRedesignCandidateFormationServiceTest extends TestCase
{
    public function test_it_blocks_when_C35_artifact_is_missing(): void
    {
        $outputPath = sys_get_temp_dir().'/c36-missing-c35-output.json';
        @unlink($outputPath);

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            sys_get_temp_dir().'/missing-c35-artifact.json',
            WatchlistBacktestC36IsControlledRedesignCandidateFormationService::DEFAULT_EXPECTED_C35_HASH,
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C36_BLOCKED_MISSING_C35_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C36_C35_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C36_BLOCKED_MISSING_C35_ARTIFACT', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        @unlink($outputPath);
    }

    public function test_it_blocks_when_expected_C35_hash_mismatches(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('hash-mismatch');
        $c35 = $this->c35Artifact($isPath);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            'wrong-c35-hash',
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_C35_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c35_hash_match']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C35_status_is_unexpected(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('status');
        $c35 = $this->c35Artifact($isPath);
        $c35['status'] = 'C35_OPERATOR_VALIDATION_REQUIRED';
        $c35['artifact_hash'] = $this->stableHash($c35);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_UNEXPECTED_C35_STATUS', $result['status']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C35_diagnostic_conclusion_is_unexpected(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('conclusion');
        $c35 = $this->c35Artifact($isPath);
        $c35['diagnostic_conclusion'] = 'C35_IS_EVIDENCE_INSUFFICIENT_FOR_REDESIGN';
        $c35['artifact_hash'] = $this->stableHash($c35);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_UNEXPECTED_C35_CONCLUSION', $result['status']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C35_production_ready_is_true(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('prod-ready');
        $c35 = $this->c35Artifact($isPath);
        $c35['production_ready'] = true;
        $c35['artifact_hash'] = $this->stableHash($c35);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_C35_PRODUCTION_READY_NOT_FALSE', $result['status']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_C35_oos_data_used_for_tuning_is_true(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('oos-tuning');
        $c35 = $this->c35Artifact($isPath);
        $c35['is_period']['oos_data_used_for_tuning'] = true;
        $c35['artifact_hash'] = $this->stableHash($c35);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_C35_OOS_TUNING_FLAG_NOT_FALSE', $result['status']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_period_touches_OOS_reserved(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('oos-period');
        $c35 = $this->c35Artifact($isPath);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_blocks_when_IS_evidence_is_missing(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('missing-is');
        $c35 = $this->c35Artifact($isPath);
        $this->writeJson($c35Path, $c35);

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true]
        );

        $this->assertSame('C36_BLOCKED_MISSING_IS_EVIDENCE', $result['status']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    public function test_it_completes_C36_candidate_formation_when_C35_valid_and_IS_evidence_available(): void
    {
        [$c35Path, $isPath, $outputPath] = $this->tempPaths('completed');
        $c35 = $this->c35Artifact($isPath);
        $this->writeJson($c35Path, $c35);
        $this->writeJson($isPath, $this->isArtifact());

        $result = (new WatchlistBacktestC36IsControlledRedesignCandidateFormationService())->execute(
            $c35Path,
            $c35['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $outputPath,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED', $result['status']);
        $this->assertSame(0, $result['production_ready']);
        $this->assertTrue($result['c35_hash_match']);

        $artifact = json_decode((string) file_get_contents($outputPath), true);
        $this->assertSame('C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION', $artifact['run_code']);
        $this->assertSame('C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED', $artifact['status']);
        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['is_period']['oos_data_used_for_tuning']);
        $this->assertSame(3, $artifact['source_c35_summary']['g21_rows']);
        $this->assertSame(3, $artifact['source_c35_summary']['g16_rows']);
        $this->assertTrue($artifact['source_c35_summary']['g21_weakness_confirmed']);
        $this->assertTrue($artifact['source_c35_summary']['g16_weakness_confirmed']);
        $this->assertSame(7, $artifact['candidate_summary']['total_candidates']);
        $this->assertGreaterThanOrEqual(4, $artifact['candidate_summary']['evaluated_candidates']);
        $this->assertGreaterThanOrEqual(3, $artifact['candidate_summary']['not_evaluable_candidates']);
        $this->assertTrue($artifact['candidate_summary']['candidate_formed']);
        $this->assertTrue($artifact['candidate_summary']['best_is_candidate_is_not_production']);
        $this->assertNotEmpty($artifact['baseline_summary']);
        $this->assertNotEmpty($artifact['candidate_results']);
        $this->assertNotEmpty($artifact['candidate_comparison_table']);
        $this->assertNotEmpty($artifact['candidate_safety_audit']);
        $this->assertNotEmpty($artifact['not_evaluable_reasons']);

        $byCode = [];
        foreach ($artifact['candidate_results'] as $row) {
            $byCode[$row['candidate_code']] = $row;
            $this->assertFalse($row['return_used_for_selection']);
            $this->assertFalse($row['future_path_used_for_selection']);
            $this->assertFalse($row['oos_data_used_for_tuning']);
            $this->assertFalse($row['production_ready']);
        }
        $this->assertArrayHasKey('C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR', $byCode);
        $this->assertArrayHasKey('C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD', $byCode);
        $this->assertArrayHasKey('C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE', $byCode);
        $this->assertArrayHasKey('C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK', $byCode);
        $this->assertArrayHasKey('C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE', $byCode);
        $this->assertArrayHasKey('C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE', $byCode);
        $this->assertSame('EVALUATED', $byCode['C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR']['candidate_status']);
        $this->assertSame('EVALUATED', $byCode['C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE']['candidate_status']);
        $this->assertSame('NOT_EVALUABLE', $byCode['C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD']['candidate_status']);
        $this->assertSame('NOT_EVALUABLE', $byCode['C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE']['candidate_status']);
        $this->assertContains($artifact['diagnostic_conclusion'], [
            'C36_G21_REDESIGN_CANDIDATE_FORMED',
            'C36_G16_REDESIGN_CANDIDATE_FORMED',
            'C36_REGIME_GATED_CANDIDATE_FORMED',
            'C36_COMBINED_CANDIDATE_FORMED',
            'C36_NO_CANDIDATE_FORMED',
            'C36_INSUFFICIENT_PRE_TRADE_FIELDS_FOR_REDESIGN',
            'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED',
        ]);
        $this->assertNotSame('C37_OOS_PROOF', $artifact['next_step_recommendation']);
        $this->assertArrayNotHasKey('best_of_oos', $artifact);
        $this->assertArrayNotHasKey('oos_winner', $artifact);
        $this->assertArrayNotHasKey('production_catalog', $artifact);
        $this->assertArrayNotHasKey('candidate_promoted', $artifact);
        $this->assertArrayNotHasKey('profile_reselection_from_oos', $artifact);

        $safetyBoundaries = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        $this->assertTrue($safetyBoundaries['no_best_of_oos']);
        $this->assertTrue($safetyBoundaries['no_oos_proof']);
        $this->assertTrue($safetyBoundaries['no_production_catalog']);
        $this->assertFalse($safetyBoundaries['production_ready']);
        $this->assertFalse($safetyBoundaries['oos_data_used_for_tuning']);
        $this->assertFalse($safetyBoundaries['return_used_for_selection']);
        $this->assertFalse($safetyBoundaries['future_path_used_for_selection']);
        $this->cleanup($c35Path, $isPath, $outputPath);
    }

    private function c35Artifact(string $isPath): array
    {
        $artifact = [
            'run_code' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC',
            'status' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED',
            'artifact_type' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'is_evidence_summary' => [
                'source' => $isPath,
                'total_rows' => 6,
                'g21_rows' => 3,
                'g16_rows' => 3,
                'months_covered' => 3,
                'evidence_available' => true,
            ],
            'g21_is_summary' => [
                'is_weakness_confirmed' => true,
                'dominant_exit_reason' => 'raw_damage_control_no_profit_d2_exit_d3_open',
            ],
            'g16_is_summary' => [
                'is_weakness_confirmed' => true,
                'dominant_delay_damage_mode' => 'NEGATIVE_DELTA_VS_R09_CLUSTER',
            ],
            'redesign_hypotheses' => [
                ['hypothesis_code' => 'C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK'],
                ['hypothesis_code' => 'C35_HYP_G21_FALLBACK_EXIT_TOO_LATE'],
                ['hypothesis_code' => 'C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE'],
                ['hypothesis_code' => 'C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER'],
            ],
            'diagnostic_conclusion' => 'C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED',
            'next_step_recommendation' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION',
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function isArtifact(): array
    {
        return [
            'artifact_type' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC',
            'status' => 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_COMPLETED',
            'pick_diagnostic_rows' => [
                $this->row('2023-03-15', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAA', -0.0200, 'raw_damage_control_no_profit_d2_exit_d3_open', -0.0010),
                $this->row('2023-03-16', '2023-03', 'G21', 'no_rule_profit_signal_before_fallback', 'AAA', -0.0100, 'raw_damage_control_no_profit_d2_exit_d3_open', 0.0000),
                $this->row('2023-04-10', '2023-04', 'G21', 'no_rule_profit_signal_before_fallback', 'BBB', -0.0040, 'raw_damage_control_no_profit_d2_exit_d3_open', 0.0020),
                $this->row('2023-03-20', '2023-03', 'G16', 'next_open_delay_after_close_signal', 'CCC', 0.0160, 'raw_preplanned_intraday_target_hit', -0.0030),
                $this->row('2023-04-11', '2023-04', 'G16', 'next_open_delay_after_close_signal', 'DDD', 0.0150, 'raw_preplanned_intraday_target_hit', -0.0020),
                $this->row('2023-05-15', '2023-05', 'G16', 'next_open_delay_after_close_signal', 'EEE', 0.0140, 'raw_preplanned_intraday_target_hit', -0.0010),
            ],
        ];
    }

    private function row(string $date, string $month, string $source, string $bucket, string $ticker, float $ret, string $exitReason, float $delta): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $ticker,
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'profile_exit_reason' => $exitReason,
            'profile_ret_net' => $ret,
            'delta_vs_raw_r09' => $delta,
            'oos_executed' => false,
            'production_ready' => 0,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c36-'.$suffix.'-'.uniqid();
        return [$base.'-c35.json', $base.'-is.json', $base.'-out.json'];
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
