<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC38IsRedesignEvidenceExpansionDiagnosticService
{
    public const RUN_CODE = 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC';
    public const ARTIFACT_TYPE = 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC';
    public const DEFAULT_C37_ARTIFACT = 'storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json';
    public const DEFAULT_EXPECTED_C37_HASH = '5938e353296cb2188b6668093522d0b40d6cb9d2';
    public const DEFAULT_C37_FILE_SHA1 = 'C17254C01D2405DE8F77999DD7131AEE0663A287';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json';
    public const DEFAULT_IS_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C37_STATUS = 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED';
    public const EXPECTED_C37_CONCLUSION = 'C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK';
    public const EXPECTED_C37_NEXT_STEP = 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC';

    public function execute(
        string $c37Artifact = self::DEFAULT_C37_ARTIFACT,
        string $expectedC37Hash = self::DEFAULT_EXPECTED_C37_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c37Artifact = trim($c37Artifact) !== '' ? trim($c37Artifact) : self::DEFAULT_C37_ARTIFACT;
        $expectedC37Hash = trim($expectedC37Hash) !== '' ? trim($expectedC37Hash) : self::DEFAULT_EXPECTED_C37_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c37Artifact, $expectedC37Hash, null, null, null, null, $from, $to, $createdAt, self::DEFAULT_IS_EVIDENCE_ARTIFACT);

        if (! is_file($c37Artifact)) {
            return $this->blocked($artifact, 'C38_BLOCKED_MISSING_C37_ARTIFACT', 'WS_BT_C38_C37_ARTIFACT_MISSING', 'C38 requires the locked C37 IS validation artifact, but the file is missing.', $outputPath, ['input_c37_artifact' => $c37Artifact]);
        }

        $c37 = json_decode((string) file_get_contents($c37Artifact), true);
        if (! is_array($c37)) {
            return $this->blocked($artifact, 'C38_BLOCKED_MISSING_C37_ARTIFACT', 'WS_BT_C38_C37_ARTIFACT_UNREADABLE', 'C37 artifact is not readable JSON.', $outputPath, ['input_c37_artifact' => $c37Artifact]);
        }

        $actualC37Hash = $this->stableHash($c37);
        $sourceEvidence = $this->sourceEvidencePath($c37, $options);
        $artifact = $this->baseArtifact(
            $c37Artifact,
            $expectedC37Hash,
            $actualC37Hash,
            $c37['status'] ?? null,
            $c37['diagnostic_conclusion'] ?? null,
            $c37['next_step_recommendation'] ?? null,
            $from,
            $to,
            $createdAt,
            $sourceEvidence
        );
        $artifact['source_c37_summary'] = $this->sourceC37Summary($c37, $sourceEvidence);
        $artifact['validation_target'] = $this->validationTarget($c37);

        if ($actualC37Hash !== $expectedC37Hash) {
            return $this->blocked($artifact, 'C38_BLOCKED_C37_HASH_MISMATCH', 'WS_BT_C38_C37_ARTIFACT_HASH_MISMATCH', 'C37 artifact stable hash does not match the expected locked hash.', $outputPath, ['c37_artifact_hash_field' => $c37['artifact_hash'] ?? null]);
        }

        if (($c37['status'] ?? null) !== self::EXPECTED_C37_STATUS) {
            return $this->blocked($artifact, 'C38_BLOCKED_UNEXPECTED_C37_STATUS', 'WS_BT_C38_UNEXPECTED_C37_STATUS', 'C38 requires a completed C37 IS validation and anti-overfit artifact.', $outputPath, ['expected_c37_status' => self::EXPECTED_C37_STATUS]);
        }

        if (($c37['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C37_CONCLUSION) {
            return $this->blocked($artifact, 'C38_BLOCKED_C37_NOT_FAILED_ANTI_OVERFIT_INPUT', 'WS_BT_C38_C37_NOT_FAILED_ANTI_OVERFIT_INPUT', 'C38 redesign/evidence expansion expects C37 to have failed anti-overfit validation.', $outputPath, ['expected_c37_diagnostic_conclusion' => self::EXPECTED_C37_CONCLUSION]);
        }

        if (($c37['next_step_recommendation'] ?? null) !== self::EXPECTED_C37_NEXT_STEP) {
            return $this->blocked($artifact, 'C38_BLOCKED_UNEXPECTED_C37_NEXT_STEP', 'WS_BT_C38_UNEXPECTED_C37_NEXT_STEP', 'C38 requires C37 next step to be redesign or evidence expansion diagnostic.', $outputPath, ['expected_c37_next_step' => self::EXPECTED_C37_NEXT_STEP]);
        }

        if (! $this->strictFalse($c37['production_ready'] ?? false)) {
            return $this->blocked($artifact, 'C38_BLOCKED_C37_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C38_C37_PRODUCTION_READY_NOT_FALSE', 'C38 requires C37 production_ready=false.', $outputPath, ['expected_production_ready' => false]);
        }

        if (! $this->strictFalse($c37['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return $this->blocked($artifact, 'C38_BLOCKED_C37_OOS_TUNING_FLAG_NOT_FALSE', 'WS_BT_C38_C37_OOS_TUNING_FLAG_NOT_FALSE', 'C38 requires C37 oos_data_used_for_tuning=false.', $outputPath, ['expected_oos_data_used_for_tuning' => false]);
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked($artifact, 'C38_BLOCKED_INVALID_IS_PERIOD', 'WS_BT_C38_INVALID_IS_PERIOD', 'C38 requires a valid IS period where from <= to.', $outputPath, ['from' => $from, 'to' => $to]);
        }

        if ($this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C38_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C38_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C38 is IS-only and rejects runtime periods that touch the reserved OOS window.', $outputPath, ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM]);
        }

        if ($sourceEvidence === '' || ! is_file($sourceEvidence)) {
            return $this->blocked($artifact, 'C38_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C38_IS_EVIDENCE_ARTIFACT_MISSING', 'C38 requires C37-linked IS diagnostic evidence rows; no IS evidence artifact is available.', $outputPath, ['source_evidence' => $sourceEvidence]);
        }

        $source = json_decode((string) file_get_contents($sourceEvidence), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked($artifact, 'C38_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C38_IS_EVIDENCE_ROWS_MISSING', 'C38 requires pick_diagnostic_rows from the C37-linked IS artifact; the available artifact does not contain usable rows.', $outputPath, ['source_evidence' => $sourceEvidence]);
        }

        $allRows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21Rows = $this->targetRows($allRows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($allRows, 'G16', 'next_open_delay_after_close_signal');
        $baselineRows = array_merge($g21Rows, $g16Rows);

        if (count($baselineRows) === 0) {
            return $this->blocked($artifact, 'C38_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C38_BASELINE_BRANCH_ROWS_MISSING', 'C38 found IS evidence but not enough G21/G16 target branch rows to diagnose redesign needs.', $outputPath, ['g21_rows' => count($g21Rows), 'g16_rows' => count($g16Rows)]);
        }

        $artifact['source_c37_summary']['g21_rows'] = count($g21Rows);
        $artifact['source_c37_summary']['g16_rows'] = count($g16Rows);
        $artifact['month_coverage_failure_diagnostic'] = $this->monthCoverageFailureDiagnostic($baselineRows, $g16Rows, $g21Rows, $c37);
        $artifact['branch_concentration_diagnostic'] = $this->branchConcentrationDiagnostic($baselineRows, $g16Rows, $g21Rows, $c37);
        $artifact['rolling_warning_diagnostic'] = $this->rollingWarningDiagnostic($c37, $baselineRows, $g16Rows, $g21Rows);
        $artifact['not_evaluable_candidate_diagnostic'] = $this->notEvaluableCandidateDiagnostic($c37);
        $artifact['evidence_expansion_requirements'] = $this->evidenceExpansionRequirements($artifact);
        $artifact['redesign_hypotheses'] = $this->redesignHypotheses($artifact);
        $artifact['c38_decision_summary'] = $this->decisionSummary($artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($artifact);
        $artifact['diagnostic_conclusion'] = $artifact['c38_decision_summary']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c38_decision_summary']['next_step_recommendation'];
        $artifact['status'] = 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED';
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(
        string $inputC37Path,
        string $expectedC37Hash,
        ?string $actualC37Hash,
        $c37Status,
        $c37Conclusion,
        $c37NextStep,
        string $from,
        string $to,
        string $createdAt,
        string $sourceEvidence
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C38_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c37_artifact' => $inputC37Path,
            'expected_c37_hash' => $expectedC37Hash,
            'actual_c37_hash' => $actualC37Hash,
            'c37_hash_match' => $actualC37Hash !== null && $actualC37Hash === $expectedC37Hash,
            'c37_status' => $c37Status,
            'c37_diagnostic_conclusion' => $c37Conclusion,
            'c37_next_step_recommendation' => $c37NextStep,
            'expected_c37_file_sha1' => self::DEFAULT_C37_FILE_SHA1,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c37_summary' => [
                'overall_anti_overfit_result' => null,
                'candidate_c37_decision' => null,
                'failing_layers' => [],
                'warning_layers' => [],
                'source_evidence' => $sourceEvidence,
                'g21_rows' => 0,
                'g16_rows' => 0,
            ],
            'validation_target' => [
                'baseline_candidate_code' => null,
                'target_candidate_code' => null,
                'target_candidate_is_not_production' => true,
            ],
            'diagnostic_scope' => [
                'c38_is_only_evidence_expansion' => true,
                'no_new_candidate_selected' => true,
                'no_oos_proof' => true,
                'no_oos_tuning' => true,
                'no_best_of_oos' => true,
                'no_production_catalog' => true,
                'no_candidate_promoted' => true,
                'production_ready' => false,
            ],
            'month_coverage_failure_diagnostic' => [],
            'branch_concentration_diagnostic' => [],
            'rolling_warning_diagnostic' => [],
            'not_evaluable_candidate_diagnostic' => [],
            'evidence_expansion_requirements' => [],
            'redesign_hypotheses' => [],
            'c38_decision_summary' => [],
            'candidate_safety_audit' => [],
            'diagnostic_conclusion' => 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_PENDING',
            'next_step_recommendation' => 'C38_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C38_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C38 locks C37 as source artifact, diagnoses IS redesign/evidence expansion needs only, and does not run OOS proof or production promotion.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC' => true,
                'C37_ARTIFACT_HASH_LOCK' => true,
                'C38_FROM_C37_FAILED_ANTI_OVERFIT' => true,
                'IS_ONLY_DIAGNOSTIC' => true,
                'NO_NEW_CANDIDATE_SELECTED' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C37_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
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

    private function sourceC37Summary(array $c37, string $sourceEvidence): array
    {
        $anti = is_array($c37['anti_overfit_summary'] ?? null) ? $c37['anti_overfit_summary'] : [];

        return [
            'overall_anti_overfit_result' => $anti['overall_anti_overfit_result'] ?? null,
            'candidate_c37_decision' => $anti['candidate_c37_decision'] ?? null,
            'failing_layers' => $this->layerNamesByResult($anti, 'FAIL'),
            'warning_layers' => $this->layerNamesByResult($anti, 'WARNING'),
            'source_evidence' => $sourceEvidence,
            'g21_rows' => (int) ($c37['source_c37_summary']['g21_rows'] ?? $c37['source_c36_summary']['g21_rows'] ?? 0),
            'g16_rows' => (int) ($c37['source_c37_summary']['g16_rows'] ?? $c37['source_c36_summary']['g16_rows'] ?? 0),
        ];
    }

    private function validationTarget(array $c37): array
    {
        return [
            'baseline_candidate_code' => $c37['validation_target']['baseline_candidate_code'] ?? null,
            'target_candidate_code' => $c37['validation_target']['target_candidate_code'] ?? null,
            'target_candidate_is_not_production' => (bool) ($c37['validation_target']['target_candidate_is_not_production'] ?? true),
        ];
    }

    private function monthCoverageFailureDiagnostic(array $baselineRows, array $candidateRows, array $g21Rows, array $c37): array
    {
        $baselineMonths = $this->uniqueMonths($baselineRows);
        $candidateMonths = $this->uniqueMonths($candidateRows);
        $zeroMonths = array_values(array_diff($baselineMonths, $candidateMonths));
        $zeroDetails = [];

        foreach ($zeroMonths as $month) {
            $baselineMonthRows = $this->filterRowsByMonths($baselineRows, [$month]);
            $g21MonthRows = $this->filterRowsByMonths($g21Rows, [$month]);
            $candidateMonthRows = $this->filterRowsByMonths($candidateRows, [$month]);
            $zeroDetails[] = [
                'trade_month' => $month,
                'baseline_rows' => count($baselineMonthRows),
                'candidate_rows' => count($candidateMonthRows),
                'g21_rows_available_for_diagnostic' => count($g21MonthRows),
                'g16_rows_available_for_diagnostic' => count($candidateMonthRows),
                'baseline_avg_ret_net' => $this->avg($baselineMonthRows),
                'g21_avg_ret_net_evaluation_only' => $this->avg($g21MonthRows),
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'diagnostic_note' => 'This month is identified from C37 structural month coverage failure; return is evaluation-only.',
            ];
        }

        return [
            'validation_layer' => 'MONTH_COVERAGE_FAILURE',
            'c37_result' => $c37['month_coverage_validation']['result'] ?? null,
            'c37_reason_code' => $c37['month_coverage_validation']['reason_code'] ?? null,
            'baseline_months_covered' => count($baselineMonths),
            'candidate_months_covered' => count($candidateMonths),
            'zero_pick_months' => $zeroMonths,
            'zero_pick_month_count' => count($zeroMonths),
            'zero_pick_month_details' => $zeroDetails,
            'coverage_failure_confirmed' => count($zeroMonths) > 0,
            'result' => count($zeroMonths) > 0 ? 'CONFIRMED_REDESIGN_REQUIRED' : 'NOT_CONFIRMED',
        ];
    }

    private function branchConcentrationDiagnostic(array $baselineRows, array $candidateRows, array $g21Rows, array $c37): array
    {
        $baseline = $this->branchSummary($baselineRows);
        $candidate = $this->branchSummary($candidateRows);
        $suppressedG21Rows = max(0, count($g21Rows) - count($this->filterRowsByBranch($candidateRows, 'G21')));

        return [
            'validation_layer' => 'BRANCH_CONCENTRATION_WARNING',
            'c37_result' => $c37['branch_concentration_validation']['result'] ?? null,
            'c37_reason_code' => $c37['branch_concentration_validation']['reason_code'] ?? null,
            'baseline' => $baseline,
            'candidate' => $candidate,
            'suppressed_g21_rows' => $suppressedG21Rows,
            'candidate_selected_rows_share_vs_baseline' => count($baselineRows) > 0 ? count($candidateRows) / count($baselineRows) : null,
            'branch_concentration_confirmed' => ($candidate['top_branch_share'] ?? 0.0) >= 0.95,
            'result' => ($candidate['top_branch_share'] ?? 0.0) >= 0.95 ? 'CONFIRMED_BRANCH_DIVERSIFICATION_REQUIRED' : 'NOT_CONFIRMED',
        ];
    }

    private function rollingWarningDiagnostic(array $c37, array $baselineRows, array $candidateRows, array $g21Rows): array
    {
        $warnings = [];
        foreach (($c37['rolling_window_validation'] ?? []) as $row) {
            if (! is_array($row) || ! in_array((string) ($row['result'] ?? ''), ['WARNING', 'FAIL'], true)) {
                continue;
            }
            $slice = (string) ($row['validation_slice'] ?? '');
            $months = $this->monthsFromSlice($slice);
            $warnings[] = [
                'validation_slice' => $slice,
                'window_code' => $row['window_code'] ?? null,
                'result' => $row['result'] ?? null,
                'reason_code' => $row['reason_code'] ?? null,
                'candidate_selected_rows' => $row['target_candidate']['selected_rows'] ?? null,
                'delta_avg_ret_net_vs_baseline' => $row['comparison_vs_baseline']['delta_avg_ret_net_vs_baseline'] ?? null,
                'delta_month_win_rate_min_vs_baseline' => $row['comparison_vs_baseline']['delta_month_win_rate_min_vs_baseline'] ?? null,
                'g21_rows_in_window_for_diagnostic' => count($this->filterRowsByMonths($g21Rows, $months)),
                'candidate_rows_in_window' => count($this->filterRowsByMonths($candidateRows, $months)),
                'baseline_rows_in_window' => count($this->filterRowsByMonths($baselineRows, $months)),
            ];
        }

        return [
            'validation_layer' => 'ROLLING_WARNING',
            'warning_or_fail_window_count' => count($warnings),
            'warning_or_fail_windows' => $warnings,
            'rolling_warning_confirmed' => count($warnings) > 0,
            'result' => count($warnings) > 0 ? 'CONFIRMED_ROLLING_STABILITY_REVIEW_REQUIRED' : 'NOT_CONFIRMED',
        ];
    }

    private function notEvaluableCandidateDiagnostic(array $c37): array
    {
        $items = [];
        foreach (($c37['not_evaluable_reasons'] ?? []) as $row) {
            if (! is_array($row) || ($row['validation_layer'] ?? null) !== 'C36_CANDIDATE_FORMATION') {
                continue;
            }
            $items[] = [
                'candidate_code' => $row['validation_slice'] ?? null,
                'reason_code' => $row['reason_code'] ?? null,
                'message' => $row['message'] ?? null,
                'c38_interpretation' => 'Additional pre-trade evidence is required; C38 does not force this C36 candidate into evaluated status.',
            ];
        }

        return [
            'validation_layer' => 'C36_NOT_EVALUABLE_CANDIDATES',
            'not_evaluable_count' => count($items),
            'items' => $items,
            'result' => count($items) > 0 ? 'PRE_TRADE_FIELD_EXPANSION_REQUIRED' : 'NO_C36_NOT_EVALUABLE_CANDIDATES',
        ];
    }

    private function evidenceExpansionRequirements(array $artifact): array
    {
        $requirements = [];
        if (($artifact['month_coverage_failure_diagnostic']['coverage_failure_confirmed'] ?? false) === true) {
            $requirements[] = [
                'requirement_code' => 'C38_REQ_MONTH_COVERAGE_GUARD',
                'priority' => 'HIGH',
                'reason' => 'C37 candidate creates zero-pick IS month coverage.',
                'required_evidence' => 'Pre-trade coverage guard or branch fallback evidence that can preserve monthly picks without using realized return.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ];
        }
        if (($artifact['branch_concentration_diagnostic']['branch_concentration_confirmed'] ?? false) === true) {
            $requirements[] = [
                'requirement_code' => 'C38_REQ_BRANCH_DIVERSIFICATION_GUARD',
                'priority' => 'HIGH',
                'reason' => 'C37 candidate is concentrated in G16 after suppressing all G21 rows.',
                'required_evidence' => 'Pre-trade G21 quality/diversification metadata that can decide whether any G21 row may be retained.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ];
        }
        if (($artifact['rolling_warning_diagnostic']['rolling_warning_confirmed'] ?? false) === true) {
            $requirements[] = [
                'requirement_code' => 'C38_REQ_ROLLING_STABILITY_EXPANSION',
                'priority' => 'MEDIUM',
                'reason' => 'C37 rolling validation has at least one warning window.',
                'required_evidence' => 'Additional IS split diagnostics for the warning window before any OOS proof.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ];
        }
        if (($artifact['not_evaluable_candidate_diagnostic']['not_evaluable_count'] ?? 0) > 0) {
            $requirements[] = [
                'requirement_code' => 'C38_REQ_PRE_TRADE_FIELD_EXPANSION_FOR_C36_BLOCKED_CANDIDATES',
                'priority' => 'MEDIUM',
                'reason' => 'C36 had candidates blocked by unavailable pre-trade fields.',
                'required_evidence' => 'D2 close/path availability, regime pre-trade fields, or gap/delay pre-trade fields depending on candidate.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
            ];
        }

        return $requirements;
    }

    private function redesignHypotheses(array $artifact): array
    {
        return [
            [
                'hypothesis_code' => 'C38_HYP_COVERAGE_GUARD_REQUIRED_BEFORE_OOS',
                'support' => ($artifact['month_coverage_failure_diagnostic']['coverage_failure_confirmed'] ?? false) ? 'STRONG_IS_SUPPORT' : 'NOT_CONFIRMED',
                'basis' => 'C37 month coverage validation result and C28 IS month coverage rows.',
                'candidate_selected' => false,
                'production_ready' => false,
            ],
            [
                'hypothesis_code' => 'C38_HYP_G21_REINTRODUCTION_REQUIRES_PRE_TRADE_QUALITY_GATE',
                'support' => ($artifact['branch_concentration_diagnostic']['branch_concentration_confirmed'] ?? false) ? 'STRONG_IS_SUPPORT' : 'NOT_CONFIRMED',
                'basis' => 'C37 branch concentration warning and suppressed G21 row count.',
                'candidate_selected' => false,
                'production_ready' => false,
            ],
            [
                'hypothesis_code' => 'C38_HYP_ROLLING_WARNING_REQUIRES_IS_SPLIT_EXPANSION',
                'support' => ($artifact['rolling_warning_diagnostic']['rolling_warning_confirmed'] ?? false) ? 'MODERATE_IS_SUPPORT' : 'NOT_CONFIRMED',
                'basis' => 'C37 rolling warning windows.',
                'candidate_selected' => false,
                'production_ready' => false,
            ],
        ];
    }

    private function decisionSummary(array $artifact): array
    {
        $requirements = count($artifact['evidence_expansion_requirements'] ?? []);
        $coverageFail = ($artifact['month_coverage_failure_diagnostic']['coverage_failure_confirmed'] ?? false) === true;
        $branchWarning = ($artifact['branch_concentration_diagnostic']['branch_concentration_confirmed'] ?? false) === true;

        return [
            'requirements_count' => $requirements,
            'month_coverage_failure_confirmed' => $coverageFail,
            'branch_concentration_warning_confirmed' => $branchWarning,
            'candidate_c38_decision' => 'C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_NEW_CANDIDATE',
            'candidate_c38_decision_reason' => 'C37 anti-overfit failed; C38 confirms IS redesign/evidence expansion is required before any new candidate formation or OOS proof.',
            'direct_oos_proof_recommended' => false,
            'new_candidate_selected' => false,
            'production_ready' => false,
            'diagnostic_conclusion' => 'C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS',
            'next_step_recommendation' => 'C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS',
        ];
    }

    private function candidateSafetyAudit(array $artifact): array
    {
        return [[
            'validation_layer' => 'C38_IS_REDESIGN_EVIDENCE_EXPANSION_DIAGNOSTIC',
            'passed' => true,
            'reason_code' => 'WS_BT_C38_DIAGNOSTIC_BOUNDARY_SAFE',
            'message' => 'C38 diagnoses IS evidence expansion needs only; it does not select a production candidate or run OOS proof.',
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
            'candidate_is_not_production' => true,
            'no_new_candidate_selected' => true,
            'no_oos_proof' => true,
            'no_best_of_oos' => true,
            'no_oos_winner' => true,
            'no_production_catalog' => true,
            'no_candidate_promoted' => true,
            'no_plan_confirm_mutation' => true,
            'requirement_count' => count($artifact['evidence_expansion_requirements'] ?? []),
        ]];
    }

    private function sourceEvidencePath(array $c37, array $options): string
    {
        if (isset($options['is_evidence_artifact']) && trim((string) $options['is_evidence_artifact']) !== '') {
            return trim((string) $options['is_evidence_artifact']);
        }
        $value = $c37['source_c37_summary']['source_evidence']
            ?? $c37['source_c36_summary']['source_evidence']
            ?? self::DEFAULT_IS_EVIDENCE_ARTIFACT;
        return trim((string) $value);
    }

    private function layerNamesByResult(array $anti, string $result): array
    {
        $out = [];
        foreach ($anti as $key => $value) {
            if (substr((string) $key, -7) === '_result' && (string) $value === $result) {
                $out[] = (string) $key;
            }
        }
        return $out;
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED',
                'message' => 'C38 completed IS-only redesign/evidence expansion diagnostic from the locked C37 artifact and C28/C36/C37 IS evidence.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C38_NO_OOS_PROOF_NO_PRODUCTION_PROMOTION',
                'message' => 'C38 did not run OOS proof, did not use OOS returns for tuning, did not create a production catalog, and did not mutate PLAN/CONFIRM.',
                'fatal' => false,
                'extra' => [
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                    'new_candidate_selected' => false,
                ],
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C38_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C38 diagnostic conclusion derived from IS diagnostic evidence only.',
                'fatal' => false,
            ],
        ];
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

    private function filterRowsByMonths(array $rows, array $months): array
    {
        $set = array_fill_keys($months, true);
        return array_values(array_filter($rows, function (array $row) use ($set): bool {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            return isset($set[$month]);
        }));
    }

    private function filterRowsByBranch(array $rows, string $branch): array
    {
        return array_values(array_filter($rows, function (array $row) use ($branch): bool {
            return (string) ($row['selected_source_code'] ?? '') === $branch;
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

    private function branchSummary(array $rows): array
    {
        $count = count($rows);
        $distribution = $this->distribution($rows, 'selected_source_code');

        return [
            'selected_rows' => $count,
            'branch_distribution' => $distribution,
            'top_branch_share' => count($distribution) > 0 ? $distribution[0]['share'] : null,
            'g21_share' => $this->valueShare($rows, 'selected_source_code', 'G21'),
            'g16_share' => $this->valueShare($rows, 'selected_source_code', 'G16'),
        ];
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

    private function avg(array $rows): ?float
    {
        $values = [];
        foreach ($rows as $row) {
            $num = $this->num($row['profile_ret_net'] ?? null);
            if ($num !== null) {
                $values[] = $num;
            }
        }
        return count($values) > 0 ? array_sum($values) / count($values) : null;
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
        $artifact['diagnostic_conclusion'] = 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_BLOCKED';
        $artifact['next_step_recommendation'] = 'C38_BLOCKED_UNTIL_INPUT_VALIDATED';
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
            'expected_c37_hash' => $artifact['expected_c37_hash'] ?? null,
            'actual_c37_hash' => $artifact['actual_c37_hash'] ?? null,
            'c37_hash_match' => $artifact['c37_hash_match'] ?? false,
            'c37_status' => $artifact['c37_status'] ?? null,
            'c37_diagnostic_conclusion' => $artifact['c37_diagnostic_conclusion'] ?? null,
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
                'status' => 'C38_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C38 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c37_hash' => $artifact['expected_c37_hash'] ?? null,
                'actual_c37_hash' => $artifact['actual_c37_hash'] ?? null,
                'c37_hash_match' => $artifact['c37_hash_match'] ?? false,
                'c37_status' => $artifact['c37_status'] ?? null,
                'c37_diagnostic_conclusion' => $artifact['c37_diagnostic_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }

        return [
            'status' => $artifact['status'] ?? 'C38_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C38_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c37_hash' => $artifact['expected_c37_hash'] ?? null,
            'actual_c37_hash' => $artifact['actual_c37_hash'] ?? null,
            'c37_hash_match' => $artifact['c37_hash_match'] ?? false,
            'c37_status' => $artifact['c37_status'] ?? null,
            'c37_diagnostic_conclusion' => $artifact['c37_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c37_summary' => $artifact['source_c37_summary'] ?? [],
            'c38_decision_summary' => $artifact['c38_decision_summary'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C38 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
