<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC39IsControlledRedesignWithCoverageBranchGuardsService
{
    public const RUN_CODE = 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS';
    public const ARTIFACT_TYPE = 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS';
    public const DEFAULT_C38_ARTIFACT = 'storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json';
    public const DEFAULT_EXPECTED_C38_HASH = '7fe69c9ee9797615df676b0fe0c7378b452da429';
    public const DEFAULT_C38_FILE_SHA1 = '74AF66E0170D4C6FF8AE3B7E45F8EC72D9774A7B';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json';
    public const DEFAULT_IS_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C38_STATUS = 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED';
    public const EXPECTED_C38_CONCLUSION = 'C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS';
    public const EXPECTED_C38_NEXT_STEP = 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS';
    public const BRANCH_TOP_SHARE_LIMIT = 0.80;

    public function execute(
        string $c38Artifact = self::DEFAULT_C38_ARTIFACT,
        string $expectedC38Hash = self::DEFAULT_EXPECTED_C38_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c38Artifact = trim($c38Artifact) !== '' ? trim($c38Artifact) : self::DEFAULT_C38_ARTIFACT;
        $expectedC38Hash = trim($expectedC38Hash) !== '' ? trim($expectedC38Hash) : self::DEFAULT_EXPECTED_C38_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c38Artifact, $expectedC38Hash, null, null, null, null, $from, $to, $createdAt, self::DEFAULT_IS_EVIDENCE_ARTIFACT);

        if (! is_file($c38Artifact)) {
            return $this->blocked($artifact, 'C39_BLOCKED_MISSING_C38_ARTIFACT', 'WS_BT_C39_C38_ARTIFACT_MISSING', 'C39 requires the locked C38 redesign/evidence expansion diagnostic artifact, but the file is missing.', $outputPath, ['input_c38_artifact' => $c38Artifact]);
        }

        $c38 = json_decode((string) file_get_contents($c38Artifact), true);
        if (! is_array($c38)) {
            return $this->blocked($artifact, 'C39_BLOCKED_MISSING_C38_ARTIFACT', 'WS_BT_C39_C38_ARTIFACT_UNREADABLE', 'C38 artifact is not readable JSON.', $outputPath, ['input_c38_artifact' => $c38Artifact]);
        }

        $actualC38Hash = $this->stableHash($c38);
        $sourceEvidence = $this->sourceEvidencePath($c38, $options);
        $artifact = $this->baseArtifact(
            $c38Artifact,
            $expectedC38Hash,
            $actualC38Hash,
            $c38['status'] ?? null,
            $c38['diagnostic_conclusion'] ?? null,
            $c38['next_step_recommendation'] ?? null,
            $from,
            $to,
            $createdAt,
            $sourceEvidence
        );
        $artifact['source_c38_summary'] = $this->sourceC38Summary($c38, $sourceEvidence);
        $artifact['guard_requirements_from_c38'] = $this->guardRequirementsFromC38($c38);

        if ($actualC38Hash !== $expectedC38Hash) {
            return $this->blocked($artifact, 'C39_BLOCKED_C38_HASH_MISMATCH', 'WS_BT_C39_C38_ARTIFACT_HASH_MISMATCH', 'C38 artifact stable hash does not match the expected locked hash.', $outputPath, ['c38_artifact_hash_field' => $c38['artifact_hash'] ?? null]);
        }

        if (($c38['status'] ?? null) !== self::EXPECTED_C38_STATUS) {
            return $this->blocked($artifact, 'C39_BLOCKED_UNEXPECTED_C38_STATUS', 'WS_BT_C39_UNEXPECTED_C38_STATUS', 'C39 requires a completed C38 IS redesign/evidence expansion diagnostic artifact.', $outputPath, ['expected_c38_status' => self::EXPECTED_C38_STATUS]);
        }

        if (($c38['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C38_CONCLUSION) {
            return $this->blocked($artifact, 'C39_BLOCKED_UNEXPECTED_C38_CONCLUSION', 'WS_BT_C39_UNEXPECTED_C38_CONCLUSION', 'C39 requires C38 to conclude evidence expansion is required before OOS.', $outputPath, ['expected_c38_diagnostic_conclusion' => self::EXPECTED_C38_CONCLUSION]);
        }

        if (($c38['next_step_recommendation'] ?? null) !== self::EXPECTED_C38_NEXT_STEP) {
            return $this->blocked($artifact, 'C39_BLOCKED_UNEXPECTED_C38_NEXT_STEP', 'WS_BT_C39_UNEXPECTED_C38_NEXT_STEP', 'C39 requires C38 next step to be the guarded IS-controlled redesign.', $outputPath, ['expected_c38_next_step' => self::EXPECTED_C38_NEXT_STEP]);
        }

        if (! $this->strictFalse($c38['production_ready'] ?? false)) {
            return $this->blocked($artifact, 'C39_BLOCKED_C38_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C39_C38_PRODUCTION_READY_NOT_FALSE', 'C39 requires C38 production_ready=false.', $outputPath, ['expected_production_ready' => false]);
        }

        if (! $this->strictFalse($c38['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return $this->blocked($artifact, 'C39_BLOCKED_C38_OOS_TUNING_FLAG_NOT_FALSE', 'WS_BT_C39_C38_OOS_TUNING_FLAG_NOT_FALSE', 'C39 requires C38 oos_data_used_for_tuning=false.', $outputPath, ['expected_oos_data_used_for_tuning' => false]);
        }

        if (($c38['c38_decision_summary']['direct_oos_proof_recommended'] ?? true) !== false) {
            return $this->blocked($artifact, 'C39_BLOCKED_C38_DIRECT_OOS_PROOF_NOT_FALSE', 'WS_BT_C39_C38_DIRECT_OOS_PROOF_NOT_FALSE', 'C39 requires C38 direct_oos_proof_recommended=false.', $outputPath, ['expected_direct_oos_proof_recommended' => false]);
        }

        if (($c38['c38_decision_summary']['new_candidate_selected'] ?? true) !== false) {
            return $this->blocked($artifact, 'C39_BLOCKED_C38_NEW_CANDIDATE_FLAG_NOT_FALSE', 'WS_BT_C39_C38_NEW_CANDIDATE_FLAG_NOT_FALSE', 'C39 requires C38 new_candidate_selected=false.', $outputPath, ['expected_new_candidate_selected' => false]);
        }

        if (! $this->hasRequiredC38Requirements($c38)) {
            return $this->blocked($artifact, 'C39_BLOCKED_MISSING_C38_GUARD_REQUIREMENTS', 'WS_BT_C39_C38_GUARD_REQUIREMENTS_MISSING', 'C39 requires C38 month coverage and branch diversification requirements.', $outputPath, ['required_requirements' => ['C38_REQ_MONTH_COVERAGE_GUARD', 'C38_REQ_BRANCH_DIVERSIFICATION_GUARD']]);
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked($artifact, 'C39_BLOCKED_INVALID_IS_PERIOD', 'WS_BT_C39_INVALID_IS_PERIOD', 'C39 requires a valid IS period where from <= to.', $outputPath, ['from' => $from, 'to' => $to]);
        }

        if ($this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C39_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C39_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C39 is IS-only and rejects runtime periods that touch the reserved OOS window.', $outputPath, ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM]);
        }

        if ($sourceEvidence === '' || ! is_file($sourceEvidence)) {
            return $this->blocked($artifact, 'C39_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C39_IS_EVIDENCE_ARTIFACT_MISSING', 'C39 requires C38-linked IS diagnostic evidence rows; no IS evidence artifact is available.', $outputPath, ['source_evidence' => $sourceEvidence]);
        }

        $source = json_decode((string) file_get_contents($sourceEvidence), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked($artifact, 'C39_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C39_IS_EVIDENCE_ROWS_MISSING', 'C39 requires pick_diagnostic_rows from the C38-linked IS artifact; the available artifact does not contain usable rows.', $outputPath, ['source_evidence' => $sourceEvidence]);
        }

        $allRows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21Rows = $this->targetRows($allRows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($allRows, 'G16', 'next_open_delay_after_close_signal');
        $baselineRows = array_merge($g21Rows, $g16Rows);

        if (count($baselineRows) === 0) {
            return $this->blocked($artifact, 'C39_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C39_BASELINE_BRANCH_ROWS_MISSING', 'C39 found IS evidence but not enough G21/G16 target branch rows to form guarded candidates.', $outputPath, ['g21_rows' => count($g21Rows), 'g16_rows' => count($g16Rows)]);
        }

        $baselineMonths = $this->uniqueMonths($baselineRows);
        $zeroPickMonths = $this->c38ZeroPickMonths($c38);
        $quotaRows = $this->metadataMonthlyQuotaRows($g21Rows, $g16Rows, $baselineMonths, self::BRANCH_TOP_SHARE_LIMIT);
        $quotaG21Rows = $quotaRows['rows'];

        $artifact['source_c38_summary']['g21_rows'] = count($g21Rows);
        $artifact['source_c38_summary']['g16_rows'] = count($g16Rows);
        $artifact['guard_configuration'] = [
            'month_coverage_guard_required' => true,
            'branch_diversification_guard_required' => true,
            'baseline_months_required' => count($baselineMonths),
            'baseline_months' => $baselineMonths,
            'c38_zero_pick_months' => $zeroPickMonths,
            'max_top_branch_share' => self::BRANCH_TOP_SHARE_LIMIT,
            'metadata_monthly_g21_quota_per_month' => $quotaRows['quota_per_month'],
            'metadata_monthly_g21_quota_required_rows' => $quotaRows['required_g21_rows_for_branch_limit'],
            'metadata_monthly_g21_quota_selected_rows' => count($quotaG21Rows),
            'selection_ordering_fields' => ['trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
        ];

        $baseline = $this->candidateResult(
            'C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR',
            'BASELINE_COMPARATOR',
            'C38_CONFIRMED_C37_FAILED_CANDIDATE_NEEDS_REDESIGN',
            'G21_G16',
            'EVALUATED',
            $baselineRows,
            $baselineRows,
            [],
            'baseline_current_C36_G21_G16_branch_behavior',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            null,
            $baselineMonths
        );

        $candidateResults = [];
        $candidateResults[] = $baseline;
        $candidateResults[] = $this->candidateResult(
            'C39_REFERENCE_C37_G16_ONLY_FAILED_COVERAGE_BRANCH_GUARD',
            'REFERENCE_FAILED_C37',
            'C37_MONTH_COVERAGE_FAIL_AND_BRANCH_CONCENTRATION_WARNING',
            'G16',
            'EVALUATED',
            $baselineRows,
            $g16Rows,
            $g21Rows,
            'reference_failed_C37_candidate_suppress_all_G21',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            null,
            $baselineMonths
        );

        $zeroMonthG21Rows = $this->filterRowsByMonths($g21Rows, $zeroPickMonths);
        $candidateResults[] = $this->candidateResult(
            'C39_COVERAGE_GUARD_G16_PLUS_C38_ZERO_MONTH_G21_FALLBACK',
            'COVERAGE_GUARD_ONLY',
            'C38_HYP_COVERAGE_GUARD_REQUIRED_BEFORE_OOS',
            'G21_G16',
            'EVALUATED',
            $baselineRows,
            array_merge($g16Rows, $zeroMonthG21Rows),
            $this->rejectRows($baselineRows, array_merge($g16Rows, $zeroMonthG21Rows)),
            'keep_G16_and_reintroduce_G21_only_for_C38_zero_pick_months',
            ['selected_source_code', 'bucket_code', 'trade_month'],
            null,
            $baselineMonths
        );

        $candidateResults[] = $this->candidateResult(
            'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA',
            'COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARD',
            'C38_HYP_COVERAGE_GUARD_REQUIRED_BEFORE_OOS+C38_HYP_G21_REINTRODUCTION_REQUIRES_PRE_TRADE_QUALITY_GATE',
            'G21_G16',
            'EVALUATED',
            $baselineRows,
            array_merge($g16Rows, $quotaG21Rows),
            $this->rejectRows($baselineRows, array_merge($g16Rows, $quotaG21Rows)),
            'keep_G16_and_reintroduce_metadata_sorted_G21_monthly_quota_until_top_branch_share_limit_is_met',
            ['selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            null,
            $baselineMonths
        );

        $candidateResults[] = $this->candidateResult(
            'C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED',
            'PRE_TRADE_EVIDENCE_EXPANSION',
            'C38_REQ_PRE_TRADE_FIELD_EXPANSION_FOR_C36_BLOCKED_CANDIDATES',
            'G21',
            'NOT_EVALUABLE',
            $g21Rows,
            [],
            $g21Rows,
            'requires_pre_trade_quality_fields_not_available_in_C28_IS_rows',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            'C39_BLOCKED_G21_PRE_TRADE_QUALITY_FIELD_UNAVAILABLE',
            $baselineMonths
        );

        $candidateResults[] = $this->candidateResult(
            'C39_ROLLING_STABILITY_PRE_TRADE_SPLIT_EXPANSION_REQUIRED',
            'PRE_TRADE_EVIDENCE_EXPANSION',
            'C38_REQ_ROLLING_STABILITY_EXPANSION',
            'G21_G16',
            'NOT_EVALUABLE',
            $this->rowsForRollingWindows($baselineRows, $c38),
            [],
            $this->rowsForRollingWindows($baselineRows, $c38),
            'requires_additional_pre_trade_split_metadata_for_C38_warning_windows',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            'C39_BLOCKED_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELD_UNAVAILABLE',
            $baselineMonths
        );

        $comparison = $this->candidateComparisonTable($candidateResults, $baseline);
        $formedCandidates = $this->formedCandidates($candidateResults, $comparison);
        $best = $this->bestCandidate($formedCandidates);

        $artifact['baseline_summary'] = $baseline;
        $artifact['candidate_results'] = $candidateResults;
        $artifact['candidate_comparison_table'] = $comparison;
        $artifact['formed_candidate_codes'] = array_map(function (array $row): string {
            return (string) ($row['candidate_code'] ?? '');
        }, $formedCandidates);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($candidateResults);
        $artifact['not_evaluable_reasons'] = $this->notEvaluableReasons($candidateResults);
        $artifact['guard_validation_summary'] = $this->guardValidationSummary($candidateResults, $best, $baselineMonths);
        $artifact['redesign_decision_notes'] = $this->redesignDecisionNotes($candidateResults, $formedCandidates, $best);
        $artifact['candidate_summary'] = [
            'total_candidates' => count($candidateResults),
            'evaluated_candidates' => count(array_filter($candidateResults, function (array $row): bool { return ($row['candidate_status'] ?? null) === 'EVALUATED'; })),
            'not_evaluable_candidates' => count(array_filter($candidateResults, function (array $row): bool { return ($row['candidate_status'] ?? null) === 'NOT_EVALUABLE'; })),
            'candidate_formed' => count($formedCandidates) > 0,
            'best_is_candidate_code' => $best['candidate_code'] ?? null,
            'best_is_candidate_is_not_production' => true,
            'best_candidate_requires_C40_validation' => count($formedCandidates) > 0,
        ];
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($formedCandidates);
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($formedCandidates);
        $artifact['status'] = 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED';
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(
        string $inputC38Path,
        string $expectedC38Hash,
        ?string $actualC38Hash,
        $c38Status,
        $c38Conclusion,
        $c38NextStep,
        string $from,
        string $to,
        string $createdAt,
        string $sourceEvidence
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C39_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c38_artifact' => $inputC38Path,
            'expected_c38_hash' => $expectedC38Hash,
            'actual_c38_hash' => $actualC38Hash,
            'c38_hash_match' => $actualC38Hash !== null && $actualC38Hash === $expectedC38Hash,
            'c38_status' => $c38Status,
            'c38_diagnostic_conclusion' => $c38Conclusion,
            'c38_next_step_recommendation' => $c38NextStep,
            'expected_c38_file_sha1' => self::DEFAULT_C38_FILE_SHA1,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c38_summary' => [
                'source_evidence' => $sourceEvidence,
                'c37_overall_anti_overfit_result' => null,
                'c38_diagnostic_conclusion' => null,
                'zero_pick_months' => [],
                'branch_concentration_confirmed' => null,
                'rolling_warning_confirmed' => null,
                'requirements_count' => 0,
                'g21_rows' => 0,
                'g16_rows' => 0,
            ],
            'guard_requirements_from_c38' => [],
            'guard_configuration' => [],
            'baseline_summary' => [],
            'candidate_results' => [],
            'candidate_comparison_table' => [],
            'formed_candidate_codes' => [],
            'candidate_summary' => [
                'total_candidates' => 0,
                'evaluated_candidates' => 0,
                'not_evaluable_candidates' => 0,
                'candidate_formed' => false,
                'best_is_candidate_code' => null,
                'best_is_candidate_is_not_production' => true,
                'best_candidate_requires_C40_validation' => false,
            ],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'guard_validation_summary' => [],
            'redesign_decision_notes' => [],
            'diagnostic_conclusion' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_PENDING',
            'next_step_recommendation' => 'C39_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C39_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C39 locks C38 as source artifact, forms IS-controlled guarded candidates only, and does not run OOS proof or production promotion.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS' => true,
                'C38_ARTIFACT_HASH_LOCK' => true,
                'C39_FROM_C38_EVIDENCE_EXPANSION_REQUIRED' => true,
                'IS_ONLY_CANDIDATE_FORMATION' => true,
                'COVERAGE_GUARD_REQUIRED' => true,
                'BRANCH_DIVERSIFICATION_GUARD_REQUIRED' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C38_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'CANDIDATE_REQUIRES_C40_VALIDATION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_candidate_selection' => false,
            ],
            'execution_model' => [
                'entry' => 'NEXT_OPEN',
                'exit' => 'STOP_TP_OR_TIME',
                'hold' => 5,
                'fee' => 'IDR_FIXED',
                'slip' => 0,
                'gap' => 'OPEN',
                'px' => 'IDX_BANDS',
            ],
            'created_at' => $createdAt,
        ];
    }

    private function sourceC38Summary(array $c38, string $sourceEvidence): array
    {
        return [
            'source_evidence' => $sourceEvidence,
            'c37_overall_anti_overfit_result' => $c38['source_c37_summary']['overall_anti_overfit_result'] ?? null,
            'c38_diagnostic_conclusion' => $c38['diagnostic_conclusion'] ?? null,
            'zero_pick_months' => $this->c38ZeroPickMonths($c38),
            'branch_concentration_confirmed' => (bool) ($c38['branch_concentration_diagnostic']['branch_concentration_confirmed'] ?? false),
            'rolling_warning_confirmed' => (bool) ($c38['rolling_warning_diagnostic']['rolling_warning_confirmed'] ?? false),
            'requirements_count' => count($c38['evidence_expansion_requirements'] ?? []),
            'g21_rows' => (int) ($c38['source_c38_summary']['g21_rows'] ?? $c38['source_c37_summary']['g21_rows'] ?? 0),
            'g16_rows' => (int) ($c38['source_c38_summary']['g16_rows'] ?? $c38['source_c37_summary']['g16_rows'] ?? 0),
        ];
    }

    private function guardRequirementsFromC38(array $c38): array
    {
        $out = [];
        foreach (($c38['evidence_expansion_requirements'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $code = (string) ($row['requirement_code'] ?? '');
            if ($code === '') {
                continue;
            }
            $out[] = [
                'requirement_code' => $code,
                'priority' => $row['priority'] ?? null,
                'mapped_c39_guard' => $this->mappedGuardForRequirement($code),
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ];
        }
        return $out;
    }

    private function mappedGuardForRequirement(string $code): string
    {
        if ($code === 'C38_REQ_MONTH_COVERAGE_GUARD') {
            return 'C39_MONTH_COVERAGE_GUARD';
        }
        if ($code === 'C38_REQ_BRANCH_DIVERSIFICATION_GUARD') {
            return 'C39_BRANCH_DIVERSIFICATION_GUARD';
        }
        if ($code === 'C38_REQ_ROLLING_STABILITY_EXPANSION') {
            return 'C39_ROLLING_STABILITY_PRE_TRADE_SPLIT_EXPANSION';
        }
        return 'C39_PRE_TRADE_EVIDENCE_EXPANSION';
    }

    private function hasRequiredC38Requirements(array $c38): bool
    {
        $codes = [];
        foreach (($c38['evidence_expansion_requirements'] ?? []) as $row) {
            if (is_array($row) && isset($row['requirement_code'])) {
                $codes[(string) $row['requirement_code']] = true;
            }
        }
        return isset($codes['C38_REQ_MONTH_COVERAGE_GUARD'], $codes['C38_REQ_BRANCH_DIVERSIFICATION_GUARD']);
    }

    private function candidateResult(
        string $code,
        string $group,
        string $hypothesis,
        string $branch,
        string $status,
        array $evaluatedRows,
        array $selectedRows,
        array $rejectedRows,
        string $selectionRule,
        array $selectionFields,
        ?string $notEvaluableReasonCode,
        array $baselineMonths
    ): array {
        $metrics = $this->metrics($selectedRows);
        $guard = $this->guardEvaluation($selectedRows, $baselineMonths);

        return [
            'candidate_code' => $code,
            'candidate_group' => $group,
            'source_hypothesis' => $hypothesis,
            'source_branch' => $branch,
            'candidate_status' => $status,
            'evaluated_rows' => count($evaluatedRows),
            'selected_rows' => count($selectedRows),
            'avg_ret_net' => $metrics['avg_ret_net'],
            'median_ret_net' => $metrics['median_ret_net'],
            'p25_ret_net' => $metrics['p25_ret_net'],
            'win_rate' => $metrics['win_rate'],
            'month_win_rate_min' => $metrics['month_win_rate_min'],
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'],
            'bad_month_like_count' => $metrics['bad_month_like_count'],
            'loss_concentration' => $metrics['loss_concentration'],
            'ticker_concentration' => $this->concentration($selectedRows, 'ticker'),
            'branch_concentration' => $this->concentration($selectedRows, 'selected_source_code'),
            'branch_distribution' => $this->distribution($selectedRows, 'selected_source_code'),
            'month_coverage_guard' => $guard['month_coverage_guard'],
            'branch_diversification_guard' => $guard['branch_diversification_guard'],
            'all_required_guards_passed' => $guard['all_required_guards_passed'],
            'rejected_rows_count' => count($rejectedRows),
            'rejected_rows_reason_distribution' => $this->rejectedRowsDistribution($rejectedRows, $selectionRule),
            'selection_rule' => $selectionRule,
            'selection_input_fields' => $selectionFields,
            'selection_input_safety_check' => [
                'uses_branch_and_calendar_metadata_only' => true,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_data_used_for_tuning' => false,
                'production_ready' => false,
            ],
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
            'not_evaluable_reason_code' => $notEvaluableReasonCode,
            'candidate_is_not_production' => true,
        ];
    }

    private function guardEvaluation(array $rows, array $baselineMonths): array
    {
        $candidateMonths = $this->uniqueMonths($rows);
        $zeroMonths = array_values(array_diff($baselineMonths, $candidateMonths));
        $distribution = $this->distribution($rows, 'selected_source_code');
        $topShare = count($distribution) > 0 ? $distribution[0]['share'] : null;
        $branchCount = count($distribution);
        $monthGuardPassed = count($baselineMonths) > 0 && count($zeroMonths) === 0;
        $branchGuardPassed = $branchCount >= 2 && $topShare !== null && $topShare <= self::BRANCH_TOP_SHARE_LIMIT;

        return [
            'month_coverage_guard' => [
                'required_months_covered' => count($baselineMonths),
                'candidate_months_covered' => count($candidateMonths),
                'zero_pick_months' => $zeroMonths,
                'zero_pick_month_count' => count($zeroMonths),
                'passed' => $monthGuardPassed,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ],
            'branch_diversification_guard' => [
                'max_top_branch_share' => self::BRANCH_TOP_SHARE_LIMIT,
                'candidate_top_branch_share' => $topShare,
                'candidate_branch_count' => $branchCount,
                'g21_share' => $this->valueShare($rows, 'selected_source_code', 'G21'),
                'g16_share' => $this->valueShare($rows, 'selected_source_code', 'G16'),
                'passed' => $branchGuardPassed,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ],
            'all_required_guards_passed' => $monthGuardPassed && $branchGuardPassed,
        ];
    }

    private function metrics(array $rows): array
    {
        $values = [];
        $byMonth = [];
        $losses = 0;
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) {
                continue;
            }
            $values[] = $value;
            if ($value < 0.0) {
                $losses++;
            }
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $byMonth[$month][] = $value;
        }
        sort($values);
        $count = count($values);
        if ($count === 0) {
            return [
                'avg_ret_net' => null,
                'median_ret_net' => null,
                'p25_ret_net' => null,
                'win_rate' => null,
                'month_win_rate_min' => null,
                'month_avg_ret_net_min' => null,
                'bad_month_like_count' => 0,
                'loss_concentration' => null,
            ];
        }

        $monthWinRates = [];
        $monthAvgs = [];
        $badMonthLike = 0;
        foreach ($byMonth as $vals) {
            $mCount = count($vals);
            $mAvg = array_sum($vals) / $mCount;
            $mWin = $this->winCount($vals) / $mCount;
            $monthAvgs[] = $mAvg;
            $monthWinRates[] = $mWin;
            if ($mWin <= 0.0 || $mAvg < 0.0) {
                $badMonthLike++;
            }
        }

        return [
            'avg_ret_net' => array_sum($values) / $count,
            'median_ret_net' => $this->percentileSorted($values, 0.50),
            'p25_ret_net' => $this->percentileSorted($values, 0.25),
            'win_rate' => $this->winCount($values) / $count,
            'month_win_rate_min' => count($monthWinRates) > 0 ? min($monthWinRates) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => $badMonthLike,
            'loss_concentration' => $losses / $count,
        ];
    }

    private function candidateComparisonTable(array $results, array $baseline): array
    {
        $out = [];
        foreach ($results as $row) {
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'candidate_status' => $row['candidate_status'],
                'all_required_guards_passed' => (bool) ($row['all_required_guards_passed'] ?? false),
                'month_coverage_guard_passed' => (bool) ($row['month_coverage_guard']['passed'] ?? false),
                'branch_diversification_guard_passed' => (bool) ($row['branch_diversification_guard']['passed'] ?? false),
                'delta_avg_ret_net_vs_baseline' => $this->delta($row['avg_ret_net'] ?? null, $baseline['avg_ret_net'] ?? null),
                'delta_median_ret_net_vs_baseline' => $this->delta($row['median_ret_net'] ?? null, $baseline['median_ret_net'] ?? null),
                'delta_p25_ret_net_vs_baseline' => $this->delta($row['p25_ret_net'] ?? null, $baseline['p25_ret_net'] ?? null),
                'delta_win_rate_vs_baseline' => $this->delta($row['win_rate'] ?? null, $baseline['win_rate'] ?? null),
                'delta_month_win_rate_min_vs_baseline' => $this->delta($row['month_win_rate_min'] ?? null, $baseline['month_win_rate_min'] ?? null),
                'delta_month_avg_ret_net_min_vs_baseline' => $this->delta($row['month_avg_ret_net_min'] ?? null, $baseline['month_avg_ret_net_min'] ?? null),
                'delta_bad_month_like_count_vs_baseline' => $this->delta($row['bad_month_like_count'] ?? null, $baseline['bad_month_like_count'] ?? null),
                'delta_loss_concentration_vs_baseline' => $this->delta($row['loss_concentration'] ?? null, $baseline['loss_concentration'] ?? null),
                'production_ready' => false,
                'candidate_is_not_production' => true,
            ];
        }
        return $out;
    }

    private function formedCandidates(array $results, array $comparison): array
    {
        $byCode = [];
        foreach ($comparison as $row) {
            $byCode[$row['candidate_code']] = $row;
        }
        $out = [];
        foreach ($results as $row) {
            if (($row['candidate_status'] ?? null) !== 'EVALUATED') {
                continue;
            }
            if (($row['candidate_code'] ?? null) === 'C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR') {
                continue;
            }
            if (($row['candidate_group'] ?? null) === 'REFERENCE_FAILED_C37') {
                continue;
            }
            if (($row['all_required_guards_passed'] ?? false) !== true) {
                continue;
            }
            if (($row['production_ready'] ?? true) !== false) {
                continue;
            }
            if (($row['return_used_for_selection'] ?? true) !== false || ($row['future_path_used_for_selection'] ?? true) !== false || ($row['oos_data_used_for_tuning'] ?? true) !== false) {
                continue;
            }
            $cmp = $byCode[$row['candidate_code']] ?? [];
            $improves = (($cmp['delta_avg_ret_net_vs_baseline'] ?? null) !== null && $cmp['delta_avg_ret_net_vs_baseline'] > 0.0)
                || (($cmp['delta_p25_ret_net_vs_baseline'] ?? null) !== null && $cmp['delta_p25_ret_net_vs_baseline'] > 0.0)
                || (($cmp['delta_win_rate_vs_baseline'] ?? null) !== null && $cmp['delta_win_rate_vs_baseline'] > 0.0)
                || (($cmp['delta_bad_month_like_count_vs_baseline'] ?? null) !== null && $cmp['delta_bad_month_like_count_vs_baseline'] < 0.0)
                || (($cmp['delta_loss_concentration_vs_baseline'] ?? null) !== null && $cmp['delta_loss_concentration_vs_baseline'] < 0.0);
            $downsideNotWorse = ($cmp['delta_p25_ret_net_vs_baseline'] ?? 0.0) >= -0.0025
                && ($cmp['delta_loss_concentration_vs_baseline'] ?? 0.0) <= 0.05;
            if ($improves && $downsideNotWorse) {
                $out[] = $row;
            }
        }
        return $out;
    }

    private function bestCandidate(array $formedCandidates): ?array
    {
        if (count($formedCandidates) === 0) {
            return null;
        }
        usort($formedCandidates, function (array $a, array $b): int {
            $avgA = $this->num($a['avg_ret_net'] ?? null) ?? -999.0;
            $avgB = $this->num($b['avg_ret_net'] ?? null) ?? -999.0;
            if ($avgA === $avgB) {
                return strcmp((string) ($a['candidate_code'] ?? ''), (string) ($b['candidate_code'] ?? ''));
            }
            return $avgB <=> $avgA;
        });
        return $formedCandidates[0];
    }

    private function guardValidationSummary(array $results, ?array $best, array $baselineMonths): array
    {
        return [
            'baseline_months_required' => count($baselineMonths),
            'candidate_with_all_guards_count' => count(array_filter($results, function (array $row): bool {
                return ($row['candidate_status'] ?? null) === 'EVALUATED'
                    && ($row['candidate_group'] ?? null) !== 'BASELINE_COMPARATOR'
                    && ($row['candidate_group'] ?? null) !== 'REFERENCE_FAILED_C37'
                    && ($row['all_required_guards_passed'] ?? false) === true;
            })),
            'best_candidate_code' => $best['candidate_code'] ?? null,
            'best_candidate_month_coverage_passed' => $best ? (bool) ($best['month_coverage_guard']['passed'] ?? false) : false,
            'best_candidate_branch_diversification_passed' => $best ? (bool) ($best['branch_diversification_guard']['passed'] ?? false) : false,
            'best_candidate_top_branch_share' => $best['branch_diversification_guard']['candidate_top_branch_share'] ?? null,
            'best_candidate_zero_pick_month_count' => $best['month_coverage_guard']['zero_pick_month_count'] ?? null,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
        ];
    }

    private function redesignDecisionNotes(array $results, array $formedCandidates, ?array $best): array
    {
        return [
            'c39_candidate_formed' => count($formedCandidates) > 0,
            'best_candidate_code' => $best['candidate_code'] ?? null,
            'best_candidate_is_not_production' => true,
            'candidate_requires_C40_validation' => count($formedCandidates) > 0,
            'c37_failed_candidate_kept_as_reference_only' => true,
            'selection_input_note' => 'C39 candidate rules use branch/calendar metadata and C38 structural guard requirements only; returns are post-selection evaluation evidence.',
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
        ];
    }

    private function diagnosticConclusion(array $formedCandidates): string
    {
        return count($formedCandidates) > 0
            ? 'C39_GUARDED_IS_CANDIDATE_FORMED'
            : 'C39_NO_GUARDED_IS_CANDIDATE_FORMED';
    }

    private function nextStepRecommendation(array $formedCandidates): string
    {
        return count($formedCandidates) > 0
            ? 'C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE'
            : 'C40_EVIDENCE_EXPANSION_OR_REDESIGN_REVIEW';
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED',
                'message' => 'C39 completed IS-controlled candidate formation using C38 coverage and branch diversification guard requirements.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C39_NO_OOS_PROOF_NO_PRODUCTION_PROMOTION',
                'message' => 'C39 did not run OOS proof, did not use OOS returns for tuning, did not create a production catalog, and did not mutate PLAN/CONFIRM.',
                'fatal' => false,
                'extra' => [
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                    'best_is_candidate_is_not_production' => $artifact['candidate_summary']['best_is_candidate_is_not_production'] ?? true,
                ],
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C39_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C39 diagnostic conclusion derived from IS evidence only.',
                'fatal' => false,
            ],
        ];
    }

    private function candidateSafetyAudit(array $results): array
    {
        $out = [];
        foreach ($results as $row) {
            $safe = ($row['return_used_for_selection'] ?? true) === false
                && ($row['future_path_used_for_selection'] ?? true) === false
                && ($row['oos_data_used_for_tuning'] ?? true) === false
                && ($row['production_ready'] ?? true) === false;
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'passed' => $safe,
                'reason_code' => $safe ? 'WS_BT_C39_CANDIDATE_SELECTION_INPUT_SAFE' : 'WS_BT_C39_CANDIDATE_SELECTION_INPUT_UNSAFE',
                'message' => $safe
                    ? 'Candidate selection inputs do not use return, future path, OOS data, or production promotion.'
                    : 'Candidate violates C39 selection input safety boundary.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'production_ready' => false,
            ];
        }
        return $out;
    }

    private function notEvaluableReasons(array $results): array
    {
        $out = [];
        foreach ($results as $row) {
            if (($row['candidate_status'] ?? null) !== 'NOT_EVALUABLE') {
                continue;
            }
            $out[] = [
                'validation_layer' => 'C39_CANDIDATE_FORMATION',
                'validation_slice' => $row['candidate_code'],
                'result' => 'NOT_EVALUABLE',
                'reason_code' => $row['not_evaluable_reason_code'] ?? 'C39_NOT_EVALUABLE',
                'message' => 'Candidate is not evaluable from the available C28 IS diagnostic fields without unsafe return/future-path selection input.',
            ];
        }
        return $out;
    }

    private function sourceEvidencePath(array $c38, array $options): string
    {
        if (isset($options['is_evidence_artifact']) && trim((string) $options['is_evidence_artifact']) !== '') {
            return trim((string) $options['is_evidence_artifact']);
        }
        $value = $c38['source_c38_summary']['source_evidence']
            ?? $c38['source_c37_summary']['source_evidence']
            ?? self::DEFAULT_IS_EVIDENCE_ARTIFACT;
        return trim((string) $value);
    }

    private function c38ZeroPickMonths(array $c38): array
    {
        $months = [];
        foreach (($c38['month_coverage_failure_diagnostic']['zero_pick_months'] ?? []) as $month) {
            $value = (string) $month;
            if ($value !== '') {
                $months[$value] = true;
            }
        }
        $out = array_keys($months);
        sort($out);
        return $out;
    }

    private function rowsForRollingWindows(array $rows, array $c38): array
    {
        $months = [];
        foreach (($c38['rolling_warning_diagnostic']['warning_or_fail_windows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($this->monthsFromSlice((string) ($row['validation_slice'] ?? '')) as $month) {
                $months[$month] = true;
            }
        }
        return $this->filterRowsByMonths($rows, array_keys($months));
    }

    private function validPeriod(string $from, string $to): bool
    {
        return $from !== '' && $to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0;
    }

    private function touchesOos(string $from, string $to): bool
    {
        return strcmp($to, self::OOS_RESERVED_FROM) >= 0 || strcmp($from, self::OOS_RESERVED_FROM) >= 0;
    }

    private function isRows(array $rows, string $from, string $to): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $date = (string) ($row['trade_date'] ?? '');
            if ($date === '' || strcmp($date, $from) < 0 || strcmp($date, $to) > 0) {
                continue;
            }
            if (($row['oos_executed'] ?? false) === true || (int) ($row['oos_executed'] ?? 0) === 1) {
                continue;
            }
            if (($row['production_ready'] ?? 0) === true || (int) ($row['production_ready'] ?? 0) === 1) {
                continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    private function targetRows(array $rows, string $source, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($source, $bucket): bool {
            return (string) ($row['selected_source_code'] ?? '') === $source
                && (string) ($row['bucket_code'] ?? '') === $bucket
                && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
    }

    private function metadataMonthlyQuotaRows(array $g21Rows, array $g16Rows, array $baselineMonths, float $topShareLimit): array
    {
        $byMonth = [];
        foreach ($g21Rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month === '') {
                continue;
            }
            $byMonth[$month][] = $row;
        }
        foreach ($byMonth as $month => $rows) {
            $byMonth[$month] = $this->sortRowsForSelection($rows);
        }

        $requiredG21 = count($g16Rows) > 0
            ? (int) ceil((count($g16Rows) / $topShareLimit) - count($g16Rows))
            : 0;
        $quota = count($baselineMonths) > 0 ? max(1, (int) ceil($requiredG21 / count($baselineMonths))) : 0;
        $maxQuota = 0;
        foreach ($byMonth as $rows) {
            $maxQuota = max($maxQuota, count($rows));
        }

        $selected = [];
        while ($quota <= $maxQuota) {
            $selected = [];
            foreach ($baselineMonths as $month) {
                $selected = array_merge($selected, array_slice($byMonth[$month] ?? [], 0, $quota));
            }
            $combined = array_merge($g16Rows, $selected);
            $topShare = $this->topBranchShare($combined);
            if ($topShare !== null && $topShare <= $topShareLimit) {
                break;
            }
            $quota++;
        }

        if ($quota > $maxQuota) {
            $quota = $maxQuota;
            $selected = [];
            foreach ($baselineMonths as $month) {
                $selected = array_merge($selected, array_slice($byMonth[$month] ?? [], 0, $quota));
            }
        }

        return [
            'rows' => $selected,
            'quota_per_month' => $quota,
            'required_g21_rows_for_branch_limit' => $requiredG21,
        ];
    }

    private function sortRowsForSelection(array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
            $ka = [
                (string) ($a['trade_month'] ?? ''),
                (string) ($a['trade_date'] ?? ''),
                (string) ($a['ticker'] ?? ''),
                sprintf('%010d', (int) ($a['param_id'] ?? 0)),
                (string) ($a['row_code'] ?? ''),
            ];
            $kb = [
                (string) ($b['trade_month'] ?? ''),
                (string) ($b['trade_date'] ?? ''),
                (string) ($b['ticker'] ?? ''),
                sprintf('%010d', (int) ($b['param_id'] ?? 0)),
                (string) ($b['row_code'] ?? ''),
            ];
            return strcmp(implode('|', $ka), implode('|', $kb));
        });
        return $rows;
    }

    private function rejectRows(array $baselineRows, array $selectedRows): array
    {
        $selected = [];
        foreach ($selectedRows as $row) {
            $selected[$this->rowIdentity($row)] = true;
        }
        $out = [];
        foreach ($baselineRows as $row) {
            if (! isset($selected[$this->rowIdentity($row)])) {
                $out[] = $row;
            }
        }
        return $out;
    }

    private function rowIdentity(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            (string) ($row['ticker'] ?? ''),
            (string) ($row['param_id'] ?? ''),
            (string) ($row['row_code'] ?? ''),
            (string) ($row['selected_source_code'] ?? ''),
            (string) ($row['bucket_code'] ?? ''),
            (string) ($row['profile_code'] ?? ''),
        ]);
    }

    private function filterRowsByMonths(array $rows, array $months): array
    {
        $set = array_fill_keys($months, true);
        return array_values(array_filter($rows, function (array $row) use ($set): bool {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            return isset($set[$month]);
        }));
    }

    private function uniqueMonths(array $rows): array
    {
        $months = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month !== '') {
                $months[$month] = true;
            }
        }
        $out = array_keys($months);
        sort($out);
        return $out;
    }

    private function monthsFromSlice(string $slice): array
    {
        if (! preg_match('/^(\d{4}-\d{2})_to_(\d{4}-\d{2})$/', $slice, $m)) {
            return [];
        }
        $months = [];
        $cursor = $m[1];
        while (strcmp($cursor, $m[2]) <= 0) {
            $months[] = $cursor;
            $cursor = $this->nextMonth($cursor);
        }
        return $months;
    }

    private function nextMonth(string $month): string
    {
        $year = (int) substr($month, 0, 4);
        $monthNum = (int) substr($month, 5, 2);
        $monthNum++;
        if ($monthNum > 12) {
            $monthNum = 1;
            $year++;
        }
        return sprintf('%04d-%02d', $year, $monthNum);
    }

    private function distribution(array $rows, string $field): array
    {
        $count = count($rows);
        $counts = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? 'UNKNOWN');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        arsort($counts);
        $out = [];
        foreach ($counts as $value => $valueCount) {
            $out[] = [
                'value' => $value,
                'count' => $valueCount,
                'share' => $count > 0 ? $valueCount / $count : null,
            ];
        }
        return $out;
    }

    private function valueShare(array $rows, string $field, string $value): ?float
    {
        $count = count($rows);
        if ($count === 0) {
            return null;
        }
        $hits = 0;
        foreach ($rows as $row) {
            if ((string) ($row[$field] ?? '') === $value) {
                $hits++;
            }
        }
        return $hits / $count;
    }

    private function concentration(array $rows, string $field): ?float
    {
        return $this->topShare($rows, $field);
    }

    private function topBranchShare(array $rows): ?float
    {
        return $this->topShare($rows, 'selected_source_code');
    }

    private function topShare(array $rows, string $field): ?float
    {
        $count = count($rows);
        if ($count === 0) {
            return null;
        }
        $counts = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? 'UNKNOWN');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        return max($counts) / $count;
    }

    private function winCount(array $values): int
    {
        $wins = 0;
        foreach ($values as $value) {
            if ($value > 0.0) {
                $wins++;
            }
        }
        return $wins;
    }

    private function percentileSorted(array $values, float $percentile): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }
        $index = ($count - 1) * $percentile;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) {
            return $values[$lower];
        }
        $weight = $index - $lower;
        return $values[$lower] + (($values[$upper] - $values[$lower]) * $weight);
    }

    private function delta($candidate, $baseline): ?float
    {
        if ($candidate === null || $baseline === null || ! is_numeric($candidate) || ! is_numeric($baseline)) {
            return null;
        }
        return (float) $candidate - (float) $baseline;
    }

    private function rejectedRowsDistribution(array $rows, string $reason): array
    {
        if (count($rows) === 0) {
            return [];
        }
        return [[
            'reason' => $reason,
            'count' => count($rows),
            'share' => 1.0,
        ]];
    }

    private function strictFalse($value): bool
    {
        return $value === false || $value === 0 || $value === '0';
    }

    private function num($value): ?float
    {
        if ($value === '' || $value === null || ! is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, array $extra = []): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['diagnostic_conclusion'] = 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_BLOCKED';
        $artifact['next_step_recommendation'] = 'C39_BLOCKED_UNTIL_INPUT_VALIDATED';
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact, true);
        }

        return [
            'status' => $status,
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c38_hash' => $artifact['expected_c38_hash'] ?? null,
            'actual_c38_hash' => $artifact['actual_c38_hash'] ?? null,
            'c38_hash_match' => $artifact['c38_hash_match'] ?? false,
            'c38_status' => $artifact['c38_status'] ?? null,
            'c38_diagnostic_conclusion' => $artifact['c38_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
        ];
    }

    private function writeAndReturn(array $artifact, string $outputPath, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($outputPath, $artifact, $overwrite);
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C39_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C39 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c38_hash' => $artifact['expected_c38_hash'] ?? null,
                'actual_c38_hash' => $artifact['actual_c38_hash'] ?? null,
                'c38_hash_match' => $artifact['c38_hash_match'] ?? false,
                'c38_status' => $artifact['c38_status'] ?? null,
                'c38_diagnostic_conclusion' => $artifact['c38_diagnostic_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }

        return [
            'status' => $artifact['status'] ?? 'C39_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C39_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c38_hash' => $artifact['expected_c38_hash'] ?? null,
            'actual_c38_hash' => $artifact['actual_c38_hash'] ?? null,
            'c38_hash_match' => $artifact['c38_hash_match'] ?? false,
            'c38_status' => $artifact['c38_status'] ?? null,
            'c38_diagnostic_conclusion' => $artifact['c38_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c38_summary' => $artifact['source_c38_summary'] ?? [],
            'candidate_summary' => $artifact['candidate_summary'] ?? [],
            'guard_validation_summary' => $artifact['guard_validation_summary'] ?? [],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) {
                return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.'];
            }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C39 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
