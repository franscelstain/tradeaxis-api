<?php

use App\Application\Watchlist\Services\WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService;

class WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionServiceTest extends TestCase
{
    public function test_it_blocks_when_C41_artifact_is_missing(): void
    {
        $output = sys_get_temp_dir().'/c42-missing-c41-output.json';
        @unlink($output);

        $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            sys_get_temp_dir().'/missing-c41-artifact.json',
            WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::DEFAULT_EXPECTED_C41_HASH,
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C42_BLOCKED_MISSING_C41_ARTIFACT', $result['status']);
        $this->assertSame('WS_BT_C42_C41_ARTIFACT_MISSING', $result['reason_code']);
        $this->assertSame(0, $result['production_ready']);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertTrue($artifact['safety_boundaries']['NO_OOS_PROOF']);
        @unlink($output);
    }

    public function test_it_blocks_when_expected_C41_hash_mismatches(): void
    {
        [$c41Path, $evidencePath, $output] = $this->tempPaths('hash-mismatch');
        $c41 = $this->c41Artifact($evidencePath);
        $this->writeJson($c41Path, $c41);
        $this->writeJson($evidencePath, $this->evidenceArtifact());

        $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            'wrong-c41-hash',
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );

        $this->assertSame('C42_BLOCKED_C41_HASH_MISMATCH', $result['status']);
        $this->assertFalse((bool) $result['c41_hash_match']);
        $this->cleanup($c41Path, $evidencePath, $output);
    }

    public function test_it_blocks_on_unexpected_C41_status_conclusion_and_safety_flags(): void
    {
        $cases = [
            ['status', 'C41_OPERATOR_VALIDATION_REQUIRED', 'C42_BLOCKED_UNEXPECTED_C41_STATUS'],
            ['diagnostic_conclusion', 'C41_VALIDATED_FOR_OOS', 'C42_BLOCKED_UNEXPECTED_C41_CONCLUSION'],
            ['production_ready', true, 'C42_BLOCKED_C41_PRODUCTION_READY_NOT_FALSE'],
            ['is_period.oos_data_used_for_tuning', true, 'C42_BLOCKED_C41_OOS_TUNING_FLAG_NOT_FALSE'],
            ['review_decision_summary.direct_oos_proof_recommended', true, 'C42_BLOCKED_C41_DIRECT_OOS_FLAG_INVALID'],
            ['review_decision_summary.oos_proof_unlocked', true, 'C42_BLOCKED_C41_OOS_UNLOCK_FLAG_INVALID'],
            ['review_decision_summary.target_candidate_code', '', 'C42_BLOCKED_MISSING_C41_TARGET_CANDIDATE'],
        ];

        foreach ($cases as $idx => $case) {
            [$field, $value, $expectedStatus] = $case;
            [$c41Path, $evidencePath, $output] = $this->tempPaths('boundary-'.$idx);
            $c41 = $this->c41Artifact($evidencePath);
            $this->setNested($c41, $field, $value);
            $c41['artifact_hash'] = $this->stableHash($c41);
            $this->writeJson($c41Path, $c41);
            $this->writeJson($evidencePath, $this->evidenceArtifact());

            $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
                $c41Path,
                $c41['artifact_hash'],
                '2023-01-02',
                '2025-05-21',
                $output,
                ['overwrite' => true]
            );

            $this->assertSame($expectedStatus, $result['status']);
            $this->cleanup($c41Path, $evidencePath, $output);
        }
    }

    public function test_it_blocks_when_IS_period_touches_reserved_OOS_window(): void
    {
        [$c41Path, $evidencePath, $output] = $this->tempPaths('oos-period');
        $c41 = $this->c41Artifact($evidencePath);
        $this->writeJson($c41Path, $c41);
        $this->writeJson($evidencePath, $this->evidenceArtifact());

        $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            $c41['artifact_hash'],
            '2023-01-02',
            '2025-05-22',
            $output,
            ['overwrite' => true]
        );

        $this->assertSame('C42_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', $result['status']);
        $this->cleanup($c41Path, $evidencePath, $output);
    }

    public function test_it_blocks_when_IS_evidence_is_missing(): void
    {
        [$c41Path, $evidencePath, $output] = $this->tempPaths('missing-evidence');
        @unlink($evidencePath);
        $c41 = $this->c41Artifact($evidencePath);
        $this->writeJson($c41Path, $c41);

        $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            $c41['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );

        $this->assertSame('C42_BLOCKED_MISSING_IS_EVIDENCE', $result['status']);
        $this->cleanup($c41Path, $evidencePath, $output);
    }

    public function test_it_completes_diagnostic_and_builds_required_C42_layers(): void
    {
        [$c41Path, $evidencePath, $output] = $this->tempPaths('completed');
        $c41 = $this->c41Artifact($evidencePath);
        $this->writeJson($c41Path, $c41);
        $this->writeJson($evidencePath, $this->evidenceArtifact());

        $result = (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            $c41['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true, 'executed_at' => '2026-06-20T00:00:00+00:00']
        );

        $this->assertSame('C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED', $result['status']);
        $artifact = json_decode((string) file_get_contents($output), true);
        $this->assertFalse($artifact['production_ready']);
        $this->assertTrue($artifact['c41_hash_match']);
        $this->assertCount(3, $artifact['warning_window_expansion']);
        $this->assertSame('2023-10_to_2024-03', $artifact['warning_window_expansion'][0]['validation_slice']);
        $this->assertSame('6_month_window', $artifact['warning_window_expansion'][0]['window_code']);
        $this->assertArrayHasKey('non_bad_month_warning_expansion', $artifact);
        $this->assertTrue($artifact['non_bad_month_warning_expansion']['normal_month_warning_explained']);
        $this->assertArrayHasKey('guard_preservation_audit', $artifact);
        $this->assertSame('PASS', $artifact['guard_preservation_audit']['c39_guard_preservation_result']);
        $this->assertArrayHasKey('guard_refinement_feasibility', $artifact);
        $this->assertFalse($artifact['guard_refinement_feasibility']['safe_refinement_candidate_formed']);
        $this->assertSame([], $artifact['refinement_candidate_results']);
        $this->assertNotEmpty($artifact['candidate_comparison_table']);
        $this->assertNotEmpty($artifact['warning_explanation_summary']);
        $this->assertNotEmpty($artifact['c42_decision_summary']);
        $this->assertFalse($artifact['c42_decision_summary']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c42_decision_summary']['oos_proof_unlocked']);
        $this->assertNotEmpty($artifact['candidate_safety_audit']);
        $this->assertNotEmpty($artifact['not_evaluable_reasons']);
        $this->assertContains($artifact['diagnostic_conclusion'], [
            'C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE',
            'C42_WARNING_EXPLAINED_BUT_REQUIRES_GUARD_REFINEMENT',
            'C42_WARNING_NOT_EXPLAINED_REQUIRES_MORE_EVIDENCE',
        ]);
        $this->cleanup($c41Path, $evidencePath, $output);
    }

    public function test_field_classification_separates_safe_diagnostic_unsafe_and_unavailable_fields(): void
    {
        [$c41Path, $evidencePath, $output] = $this->tempPaths('fields');
        $c41 = $this->c41Artifact($evidencePath);
        $this->writeJson($c41Path, $c41);
        $this->writeJson($evidencePath, $this->evidenceArtifact());

        (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            $c41['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );
        $artifact = json_decode((string) file_get_contents($output), true);
        $matrix = [];
        foreach ($artifact['pre_trade_field_availability_matrix'] as $row) {
            $matrix[$row['field_name']] = $row;
        }

        $this->assertSame('SAFE_PRE_TRADE_SELECTION_FIELD', $matrix['trade_date']['field_classification']);
        $this->assertTrue($matrix['bucket_code']['safe_for_selection']);
        $this->assertSame('DIAGNOSTIC_ONLY_EVALUATION_FIELD', $matrix['profile_exit_reason']['field_classification']);
        $this->assertFalse($matrix['profile_exit_reason']['safe_for_selection']);
        $this->assertSame('UNSAFE_FUTURE_OR_RETURN_FIELD', $matrix['profile_ret_net']['field_classification']);
        $this->assertFalse($matrix['profile_ret_net']['safe_for_selection']);
        $this->assertSame('UNAVAILABLE_FIELD', $matrix['market_regime']['field_classification']);
        $this->cleanup($c41Path, $evidencePath, $output);
    }

    public function test_refinement_candidate_safety_flags_stay_false_when_no_candidate_is_formed(): void
    {
        [$c41Path, $evidencePath, $output] = $this->tempPaths('safety');
        $c41 = $this->c41Artifact($evidencePath);
        $this->writeJson($c41Path, $c41);
        $this->writeJson($evidencePath, $this->evidenceArtifact());

        (new WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService())->execute(
            $c41Path,
            $c41['artifact_hash'],
            '2023-01-02',
            '2025-05-21',
            $output,
            ['overwrite' => true]
        );
        $artifact = json_decode((string) file_get_contents($output), true);

        $this->assertFalse($artifact['guard_refinement_feasibility']['safe_refinement_candidate_formed']);
        $this->assertFalse($artifact['safety_boundaries']['return_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['future_path_used_for_selection']);
        $this->assertFalse($artifact['safety_boundaries']['oos_data_used_for_tuning']);
        $this->assertFalse($artifact['safety_boundaries']['production_ready']);
        $this->assertSame([], $artifact['refinement_candidate_results']);
        $this->cleanup($c41Path, $evidencePath, $output);
    }

    private function c41Artifact(string $sourceEvidence): array
    {
        $artifact = [
            'run_code' => 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS',
            'status' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::EXPECTED_C41_STATUS,
            'artifact_type' => 'C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS',
            'production_ready' => false,
            'is_period' => [
                'from' => '2023-01-02',
                'to' => '2025-05-21',
                'oos_reserved_from' => '2025-05-22',
                'oos_reserved_to' => '2026-05-29',
                'oos_data_used_for_tuning' => false,
            ],
            'source_c40_summary' => [
                'source_evidence' => $sourceEvidence,
                'overall_anti_overfit_result' => 'WARNING',
            ],
            'warning_layer_review' => [
                'warning_layer_count' => 2,
                'rolling_warning_review' => [
                    'warning_or_fail_window_count' => 3,
                    'window_reviews' => [
                        $this->window('2023-10_to_2024-03', '6_month_window'),
                        $this->window('2023-07_to_2024-03', '9_month_window'),
                        $this->window('2023-04_to_2024-03', '12_month_window'),
                    ],
                ],
                'non_bad_month_warning_review' => [
                    'needs_review' => true,
                    'validation_slice' => 'NON_BAD_MONTH_IS_MONTHS',
                    'result' => 'WARNING',
                    'baseline_selected_rows' => 24,
                    'target_selected_rows' => 36,
                    'target_month_avg_ret_net_min' => -0.10,
                    'baseline_month_avg_ret_net_min' => 0.01,
                    'delta_avg_ret_net_vs_baseline' => 0.01,
                    'delta_p25_ret_net_vs_baseline' => 0.01,
                    'delta_win_rate_vs_baseline' => 0.10,
                    'delta_month_avg_ret_net_vs_baseline' => -0.11,
                    'delta_bad_month_like_count_vs_baseline' => 1,
                ],
                'review_result' => 'C41_WARNING_LAYERS_REQUIRE_EVIDENCE_EXPANSION',
            ],
            'guard_blocker_recheck' => [
                'candidate_months_covered' => 12,
                'candidate_zero_pick_months' => 0,
                'candidate_min_selected_rows_per_month' => 3,
                'candidate_top_branch_share' => 0.67,
                'candidate_g16_share' => 0.67,
                'candidate_g21_share' => 0.33,
                'removed_or_suppressed_g21_rows' => 12,
                'prior_c37_coverage_branch_blocker_resolved' => true,
            ],
            'not_evaluable_evidence_gap_review' => [
                'carry_forward_gap_count' => 2,
                'gaps' => [
                    [
                        'validation_layer' => 'C39_CANDIDATE_FORMATION',
                        'validation_slice' => 'C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED',
                        'reason_code' => 'C39_BLOCKED_G21_PRE_TRADE_QUALITY_FIELD_UNAVAILABLE',
                        'message' => 'Candidate is not evaluable from available fields.',
                        'c41_review_action' => 'C41_EXPAND_G21_PRE_TRADE_QUALITY_FIELDS',
                    ],
                    [
                        'validation_layer' => 'C39_CANDIDATE_FORMATION',
                        'validation_slice' => 'C39_ROLLING_STABILITY_PRE_TRADE_SPLIT_EXPANSION_REQUIRED',
                        'reason_code' => 'C39_BLOCKED_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELD_UNAVAILABLE',
                        'message' => 'Rolling split field unavailable.',
                        'c41_review_action' => 'C41_EXPAND_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELDS',
                    ],
                ],
            ],
            'review_decision_summary' => [
                'target_candidate_code' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::TARGET_CANDIDATE_CODE,
                'overall_anti_overfit_result' => 'WARNING',
                'warning_layers_count' => 2,
                'failed_layers_count' => 0,
                'rolling_warning_windows' => 3,
                'non_bad_month_warning' => true,
                'guard_blockers_resolved' => true,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::EXPECTED_C41_CONCLUSION,
            'next_step_recommendation' => WatchlistBacktestC42IsRollingNormalMonthEvidenceExpansionService::EXPECTED_C41_NEXT_STEP,
            'safety_boundaries' => [
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
            ],
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        return $artifact;
    }

    private function window(string $slice, string $code): array
    {
        return [
            'validation_slice' => $slice,
            'window_code' => $code,
            'result' => 'WARNING',
            'baseline_selected_rows' => 0,
            'target_selected_rows' => 0,
            'target_month_avg_ret_net_min' => -0.10,
            'baseline_month_avg_ret_net_min' => 0.01,
            'delta_avg_ret_net_vs_baseline' => 0.01,
            'delta_month_avg_ret_net_vs_baseline' => -0.11,
            'delta_bad_month_like_count_vs_baseline' => 1,
        ];
    }

    private function evidenceArtifact(): array
    {
        $rows = [];
        $months = ['2023-04', '2023-05', '2023-06', '2023-07', '2023-08', '2023-09', '2023-10', '2023-11', '2023-12', '2024-01', '2024-02', '2024-03'];
        foreach ($months as $idx => $month) {
            $day = $month.'-10';
            for ($i = 1; $i <= 2; $i++) {
                $rows[] = $this->row($day, $month, 'G16', 'next_open_delay_after_close_signal', 'AAA'.$i, 160 + $i, 0.03);
            }
            $firstG21Ret = $month === '2024-03' ? -0.20 : 0.02;
            $secondG21Ret = $month === '2024-03' ? 0.30 : 0.02;

            $rows[] = $this->row($day, $month, 'G21', 'no_rule_profit_signal_before_fallback', 'BAD'.$idx, 210, $firstG21Ret);
            $rows[] = $this->row($day, $month, 'G21', 'no_rule_profit_signal_before_fallback', 'GOOD'.$idx, 211, $secondG21Ret);
        }
        return ['pick_diagnostic_rows' => $rows];
    }

    private function row(string $date, string $month, string $source, string $bucket, string $ticker, int $paramId, float $ret): array
    {
        return [
            'trade_date' => $date,
            'trade_month' => $month,
            'ticker' => $ticker,
            'param_id' => $paramId,
            'row_code' => 'ROW_'.$paramId,
            'bucket_code' => $bucket,
            'profile_code' => 'C28_TEST_PROFILE',
            'selected_source_code' => $source,
            'profile_exit_reason' => 'diagnostic_exit',
            'profile_ret_net' => $ret,
            'delta_vs_raw_r09' => $ret - 0.01,
            'oos_executed' => false,
            'production_ready' => 0,
        ];
    }

    private function tempPaths(string $suffix): array
    {
        $base = sys_get_temp_dir().'/c42-'.$suffix.'-'.uniqid('', true);
        return [$base.'-c41.json', $base.'-evidence.json', $base.'-output.json'];
    }

    private function writeJson(string $path, array $payload): void
    {
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES)."\n");
    }

    private function cleanup(string ...$paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
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
            if (! isset($cursor[$part]) || ! is_array($cursor[$part])) {
                $cursor[$part] = [];
            }
            $cursor =& $cursor[$part];
        }
    }
}
