<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC37IsValidationAntiOverfitCheckService
{
    public const RUN_CODE = 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK';
    public const ARTIFACT_TYPE = 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK';
    public const DEFAULT_C36_ARTIFACT = 'storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json';
    public const DEFAULT_EXPECTED_C36_HASH = '8bc5198cf3b79fc9b58c39fc19f319826406b4b1';
    public const DEFAULT_C36_FILE_SHA1 = 'A5D7E25594238C2743E5DB2E68657AE95BA8B927';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json';
    public const DEFAULT_IS_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C36_STATUS = 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED';
    public const BASELINE_CANDIDATE_CODE = 'C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR';
    public const PRIMARY_TARGET_CANDIDATE_CODE = 'C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR';

    private const VALID_C36_CONCLUSIONS = [
        'C36_COMBINED_CANDIDATE_FORMED',
        'C36_G21_REDESIGN_CANDIDATE_FORMED',
        'C36_G16_REDESIGN_CANDIDATE_FORMED',
        'C36_REGIME_GATED_CANDIDATE_FORMED',
    ];

    private const BAD_MONTH_LIKE_MONTHS = [
        '2023-03',
        '2023-09',
        '2024-04',
        '2024-05',
        '2024-06',
        '2024-09',
        '2024-10',
        '2024-12',
        '2025-02',
    ];

    public function execute(
        string $c36Artifact = self::DEFAULT_C36_ARTIFACT,
        string $expectedC36Hash = self::DEFAULT_EXPECTED_C36_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c36Artifact = trim($c36Artifact) !== '' ? trim($c36Artifact) : self::DEFAULT_C36_ARTIFACT;
        $expectedC36Hash = trim($expectedC36Hash) !== '' ? trim($expectedC36Hash) : self::DEFAULT_EXPECTED_C36_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact(
            $c36Artifact,
            $expectedC36Hash,
            null,
            null,
            null,
            $from,
            $to,
            $createdAt,
            self::DEFAULT_IS_EVIDENCE_ARTIFACT
        );

        if (! is_file($c36Artifact)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_MISSING_C36_ARTIFACT',
                'WS_BT_C37_C36_ARTIFACT_MISSING',
                'C37 requires the locked C36 candidate-formation artifact, but the file is missing.',
                $outputPath,
                ['input_c36_artifact' => $c36Artifact]
            );
        }

        $c36 = json_decode((string) file_get_contents($c36Artifact), true);
        if (! is_array($c36)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_MISSING_C36_ARTIFACT',
                'WS_BT_C37_C36_ARTIFACT_UNREADABLE',
                'C36 artifact is not readable JSON.',
                $outputPath,
                ['input_c36_artifact' => $c36Artifact]
            );
        }

        $actualC36Hash = $this->stableHash($c36);
        $sourceEvidence = $this->sourceEvidencePath($c36, $options);
        $artifact = $this->baseArtifact(
            $c36Artifact,
            $expectedC36Hash,
            $actualC36Hash,
            $c36['status'] ?? null,
            $c36['diagnostic_conclusion'] ?? null,
            $from,
            $to,
            $createdAt,
            $sourceEvidence
        );
        $artifact['source_c36_summary'] = $this->sourceC36Summary($c36, $sourceEvidence);

        if ($actualC36Hash !== $expectedC36Hash) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_C36_HASH_MISMATCH',
                'WS_BT_C37_C36_ARTIFACT_HASH_MISMATCH',
                'C36 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c36_artifact_hash_field' => $c36['artifact_hash'] ?? null]
            );
        }

        if (($c36['status'] ?? null) !== self::EXPECTED_C36_STATUS) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_UNEXPECTED_C36_STATUS',
                'WS_BT_C37_UNEXPECTED_C36_STATUS',
                'C37 requires a completed C36 IS-controlled redesign candidate formation artifact.',
                $outputPath,
                ['expected_c36_status' => self::EXPECTED_C36_STATUS]
            );
        }

        if (! in_array((string) ($c36['diagnostic_conclusion'] ?? ''), self::VALID_C36_CONCLUSIONS, true)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_NO_C36_CANDIDATE_FORMED',
                'WS_BT_C37_NO_C36_CANDIDATE_FORMED',
                'C37 requires C36 to form a valid IS candidate before validation.',
                $outputPath,
                ['valid_c36_diagnostic_conclusions' => self::VALID_C36_CONCLUSIONS]
            );
        }

        if (! $this->strictFalse($c36['production_ready'] ?? false)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_C36_PRODUCTION_READY_NOT_FALSE',
                'WS_BT_C37_C36_PRODUCTION_READY_NOT_FALSE',
                'C37 requires C36 production_ready=false.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (! $this->strictFalse($c36['is_period']['oos_data_used_for_tuning'] ?? false)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_C36_OOS_TUNING_FLAG_NOT_FALSE',
                'WS_BT_C37_C36_OOS_TUNING_FLAG_NOT_FALSE',
                'C37 requires C36 oos_data_used_for_tuning=false.',
                $outputPath,
                ['expected_oos_data_used_for_tuning' => false]
            );
        }

        if (($c36['candidate_summary']['best_is_candidate_is_not_production'] ?? null) !== true) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_C36_BEST_CANDIDATE_PRODUCTION_FLAG_INVALID',
                'WS_BT_C37_C36_BEST_CANDIDATE_PRODUCTION_FLAG_INVALID',
                'C37 requires the C36 best candidate to remain marked as not production.',
                $outputPath,
                ['best_is_candidate_is_not_production' => $c36['candidate_summary']['best_is_candidate_is_not_production'] ?? null]
            );
        }

        $bestCandidateCode = (string) ($c36['candidate_summary']['best_is_candidate_code'] ?? '');
        $bestCandidate = $this->candidateByCode($c36, $bestCandidateCode);
        if ($bestCandidateCode === '' || ! is_array($bestCandidate)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_MISSING_C36_BEST_CANDIDATE',
                'WS_BT_C37_C36_BEST_CANDIDATE_MISSING',
                'C37 requires the C36 best candidate result to be present in candidate_results.',
                $outputPath,
                ['best_is_candidate_code' => $bestCandidateCode]
            );
        }

        if (($bestCandidate['candidate_is_not_production'] ?? null) !== true || ! $this->strictFalse($bestCandidate['production_ready'] ?? false)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_C36_BEST_CANDIDATE_PRODUCTION_FLAG_INVALID',
                'WS_BT_C37_C36_BEST_CANDIDATE_RESULT_PRODUCTION_FLAG_INVALID',
                'C37 requires the C36 best candidate result to remain not production-ready.',
                $outputPath,
                ['best_is_candidate_code' => $bestCandidateCode]
            );
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_INVALID_IS_PERIOD',
                'WS_BT_C37_INVALID_IS_PERIOD',
                'C37 requires a valid IS period where from <= to.',
                $outputPath,
                ['from' => $from, 'to' => $to]
            );
        }

        if ($this->touchesOos($from, $to)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'WS_BT_C37_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'C37 is IS-only and rejects runtime periods that touch the reserved OOS window.',
                $outputPath,
                ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM]
            );
        }

        if ($sourceEvidence === '' || ! is_file($sourceEvidence)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C37_IS_EVIDENCE_ARTIFACT_MISSING',
                'C37 requires C36-linked IS diagnostic evidence rows; no IS evidence artifact is available.',
                $outputPath,
                ['source_evidence' => $sourceEvidence]
            );
        }

        $source = json_decode((string) file_get_contents($sourceEvidence), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C37_IS_EVIDENCE_ROWS_MISSING',
                'C37 requires pick_diagnostic_rows from the C36-linked IS artifact; the available artifact does not contain usable rows.',
                $outputPath,
                ['source_evidence' => $sourceEvidence]
            );
        }

        $allRows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21Rows = $this->targetRows($allRows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($allRows, 'G16', 'next_open_delay_after_close_signal');
        $baselineRows = array_merge($g21Rows, $g16Rows);
        $targetRows = $this->selectedRowsForCandidate($bestCandidateCode, $g21Rows, $g16Rows, $baselineRows, $bestCandidate);

        if (count($baselineRows) === 0) {
            return $this->blocked(
                $artifact,
                'C37_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C37_BASELINE_BRANCH_ROWS_MISSING',
                'C37 found IS evidence but not enough G21/G16 target branch rows to validate C36.',
                $outputPath,
                ['g21_rows' => count($g21Rows), 'g16_rows' => count($g16Rows)]
            );
        }

        $artifact['source_c36_summary']['g21_rows'] = count($g21Rows);
        $artifact['source_c36_summary']['g16_rows'] = count($g16Rows);
        $artifact['validation_target'] = [
            'baseline_candidate_code' => self::BASELINE_CANDIDATE_CODE,
            'target_candidate_code' => $bestCandidateCode,
            'target_candidate_is_not_production' => true,
            'secondary_candidate_codes' => $this->secondaryCandidateCodes($c36),
        ];

        $validation = $this->buildValidation($baselineRows, $targetRows, $g21Rows, $g16Rows, $from, $to, $bestCandidateCode, $c36);
        foreach ($validation as $key => $value) {
            $artifact[$key] = $value;
        }

        $artifact['status'] = 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED';
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($artifact['anti_overfit_summary']['overall_anti_overfit_result'] ?? 'NOT_EVALUABLE');
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($artifact['anti_overfit_summary']['overall_anti_overfit_result'] ?? 'NOT_EVALUABLE');
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(
        string $inputC36Path,
        string $expectedC36Hash,
        ?string $actualC36Hash,
        $c36Status,
        $c36Conclusion,
        string $from,
        string $to,
        string $createdAt,
        string $sourceEvidence
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C37_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c36_artifact' => $inputC36Path,
            'expected_c36_hash' => $expectedC36Hash,
            'actual_c36_hash' => $actualC36Hash,
            'c36_hash_match' => $actualC36Hash !== null && $actualC36Hash === $expectedC36Hash,
            'c36_status' => $c36Status,
            'c36_diagnostic_conclusion' => $c36Conclusion,
            'expected_c36_file_sha1' => self::DEFAULT_C36_FILE_SHA1,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c36_summary' => [
                'candidate_formed' => false,
                'best_is_candidate_code' => null,
                'best_is_candidate_is_not_production' => true,
                'source_evidence' => $sourceEvidence,
                'g21_rows' => 0,
                'g16_rows' => 0,
            ],
            'validation_target' => [
                'baseline_candidate_code' => self::BASELINE_CANDIDATE_CODE,
                'target_candidate_code' => self::PRIMARY_TARGET_CANDIDATE_CODE,
                'target_candidate_is_not_production' => true,
                'secondary_candidate_codes' => [],
            ],
            'validation_summary' => [
                'total_validation_layers' => 0,
                'passed_layers' => 0,
                'warning_layers' => 0,
                'failed_layers' => 0,
                'not_evaluable_layers' => 0,
                'overall_anti_overfit_result' => 'NOT_EVALUABLE',
                'candidate_c37_decision' => 'C37_PENDING',
                'candidate_c37_decision_reason' => 'C37 validation has not completed.',
            ],
            'full_is_validation' => [],
            'yearly_validation' => [],
            'rolling_window_validation' => [],
            'bad_month_like_stress_validation' => [],
            'non_bad_month_validation' => [],
            'ticker_concentration_validation' => [],
            'branch_concentration_validation' => [],
            'month_coverage_validation' => [],
            'downside_stability_validation' => [],
            'candidate_comparison_table' => [],
            'anti_overfit_summary' => [],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'redesign_decision_notes' => [],
            'diagnostic_conclusion' => 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_PENDING',
            'next_step_recommendation' => 'C37_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C37_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C37 locks C36 as the source candidate artifact, validates IS only, and does not run OOS proof or production promotion.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK' => true,
                'C36_ARTIFACT_HASH_LOCK' => true,
                'C37_CANDIDATE_FROM_C36_CANDIDATE' => true,
                'IS_ONLY_VALIDATION' => true,
                'ANTI_OVERFIT_VALIDATION' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C36_MUTATION' => true,
                'NO_C01_TO_C36_ARTIFACT_MUTATION' => true,
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

    private function sourceEvidencePath(array $c36, array $options): string
    {
        if (isset($options['is_evidence_artifact']) && trim((string) $options['is_evidence_artifact']) !== '') {
            return trim((string) $options['is_evidence_artifact']);
        }

        $value = $c36['source_c36_summary']['source_evidence']
            ?? $c36['source_c35_summary']['source_evidence']
            ?? self::DEFAULT_IS_EVIDENCE_ARTIFACT;

        return trim((string) $value);
    }

    private function sourceC36Summary(array $c36, string $sourceEvidence): array
    {
        return [
            'candidate_formed' => (bool) ($c36['candidate_summary']['candidate_formed'] ?? false),
            'best_is_candidate_code' => $c36['candidate_summary']['best_is_candidate_code'] ?? null,
            'best_is_candidate_is_not_production' => (bool) ($c36['candidate_summary']['best_is_candidate_is_not_production'] ?? false),
            'source_evidence' => $sourceEvidence,
            'g21_rows' => (int) ($c36['source_c36_summary']['g21_rows'] ?? $c36['source_c35_summary']['g21_rows'] ?? 0),
            'g16_rows' => (int) ($c36['source_c36_summary']['g16_rows'] ?? $c36['source_c35_summary']['g16_rows'] ?? 0),
        ];
    }

    private function buildValidation(
        array $baselineRows,
        array $targetRows,
        array $g21Rows,
        array $g16Rows,
        string $from,
        string $to,
        string $targetCandidateCode,
        array $c36
    ): array {
        $comparisonTable = [];
        $notEvaluableReasons = $this->c36NotEvaluableReasons($c36);

        $full = $this->validationSlice('FULL_IS', $from.'_to_'.$to, $baselineRows, $targetRows, $baselineRows, $targetCandidateCode);
        $comparisonTable[] = $this->comparisonRow($full);

        $yearly = [];
        foreach ($this->yearlySlices($from, $to) as $sliceCode => $bounds) {
            $baselineSliceRows = $this->filterRowsByDate($baselineRows, $bounds['from'], $bounds['to']);
            $targetSliceRows = $this->filterRowsByDate($targetRows, $bounds['from'], $bounds['to']);
            $row = $this->validationSlice('YEARLY_IS', $sliceCode, $baselineSliceRows, $targetSliceRows, $baselineSliceRows, $targetCandidateCode);
            $yearly[] = $row;
            $comparisonTable[] = $this->comparisonRow($row);
            $notEvaluableReasons = array_merge($notEvaluableReasons, $this->sliceNotEvaluableReasons($row));
        }

        $rolling = [];
        $months = $this->uniqueMonths($baselineRows);
        foreach ([6, 9, 12] as $windowMonths) {
            if (count($months) < $windowMonths) {
                $notEvaluableReasons[] = [
                    'validation_layer' => 'ROLLING_IS',
                    'validation_slice' => $windowMonths.'_month_window',
                    'reason_code' => 'C37_ROLLING_WINDOW_NOT_ENOUGH_MONTHS',
                    'message' => 'Rolling IS window is not evaluable because available IS months are fewer than the requested window size.',
                ];
                continue;
            }

            for ($i = 0; $i <= count($months) - $windowMonths; $i++) {
                $window = array_slice($months, $i, $windowMonths);
                $sliceCode = $window[0].'_to_'.$window[count($window) - 1];
                $windowCode = $windowMonths.'_month_window';
                $baselineWindowRows = $this->filterRowsByMonths($baselineRows, $window);
                $targetWindowRows = $this->filterRowsByMonths($targetRows, $window);
                $row = $this->validationSlice('ROLLING_IS', $sliceCode, $baselineWindowRows, $targetWindowRows, $baselineWindowRows, $targetCandidateCode, $windowCode);
                $rolling[] = $row;
                $comparisonTable[] = $this->comparisonRow($row);
                $notEvaluableReasons = array_merge($notEvaluableReasons, $this->sliceNotEvaluableReasons($row));
            }
        }

        $badMonthRows = $this->filterRowsByMonths($baselineRows, self::BAD_MONTH_LIKE_MONTHS);
        $badTargetRows = $this->filterRowsByMonths($targetRows, self::BAD_MONTH_LIKE_MONTHS);
        $bad = $this->validationSlice('BAD_MONTH_LIKE_STRESS', 'C35_C36_IS_BAD_MONTH_LIKE_MONTHS', $badMonthRows, $badTargetRows, $badMonthRows, $targetCandidateCode);
        $comparisonTable[] = $this->comparisonRow($bad);
        $notEvaluableReasons = array_merge($notEvaluableReasons, $this->sliceNotEvaluableReasons($bad));

        $normalMonthList = array_values(array_diff($months, self::BAD_MONTH_LIKE_MONTHS));
        $normalRows = $this->filterRowsByMonths($baselineRows, $normalMonthList);
        $normalTargetRows = $this->filterRowsByMonths($targetRows, $normalMonthList);
        $normal = $this->validationSlice('NON_BAD_MONTH', 'NON_BAD_MONTH_IS_MONTHS', $normalRows, $normalTargetRows, $normalRows, $targetCandidateCode);
        $comparisonTable[] = $this->comparisonRow($normal);
        $notEvaluableReasons = array_merge($notEvaluableReasons, $this->sliceNotEvaluableReasons($normal));

        $ticker = $this->tickerConcentrationValidation($baselineRows, $targetRows);
        if (($ticker['result'] ?? null) === 'NOT_EVALUABLE') {
            $notEvaluableReasons[] = [
                'validation_layer' => 'TICKER_CONCENTRATION',
                'validation_slice' => 'FULL_IS',
                'reason_code' => $ticker['reason_code'] ?? 'C37_TICKER_FIELD_UNAVAILABLE',
                'message' => $ticker['message'] ?? 'Ticker concentration validation is not evaluable.',
            ];
        }

        $branch = $this->branchConcentrationValidation($baselineRows, $targetRows, $g21Rows, $g16Rows);
        if (($branch['result'] ?? null) === 'NOT_EVALUABLE') {
            $notEvaluableReasons[] = [
                'validation_layer' => 'BRANCH_CONCENTRATION',
                'validation_slice' => 'FULL_IS',
                'reason_code' => $branch['reason_code'] ?? 'C37_BRANCH_FIELD_UNAVAILABLE',
                'message' => $branch['message'] ?? 'Branch concentration validation is not evaluable.',
            ];
        }

        $monthCoverage = $this->monthCoverageValidation($full);
        $downside = $this->downsideStabilityValidation($full);
        $comparisonTable[] = $this->specialComparisonRow('TICKER_CONCENTRATION', 'FULL_IS', $targetCandidateCode, $ticker);
        $comparisonTable[] = $this->specialComparisonRow('BRANCH_CONCENTRATION', 'FULL_IS', $targetCandidateCode, $branch);
        $comparisonTable[] = $this->specialComparisonRow('MONTH_COVERAGE', 'FULL_IS', $targetCandidateCode, $monthCoverage);
        $comparisonTable[] = $this->specialComparisonRow('DOWNSIDE_STABILITY', 'FULL_IS', $targetCandidateCode, $downside);

        $anti = $this->antiOverfitSummary($full, $yearly, $rolling, $bad, $normal, $ticker, $branch, $monthCoverage, $downside);
        $summary = $this->validationSummary($anti);

        return [
            'validation_summary' => $summary,
            'full_is_validation' => $full,
            'yearly_validation' => $yearly,
            'rolling_window_validation' => $rolling,
            'bad_month_like_stress_validation' => $bad,
            'non_bad_month_validation' => $normal,
            'ticker_concentration_validation' => $ticker,
            'branch_concentration_validation' => $branch,
            'month_coverage_validation' => $monthCoverage,
            'downside_stability_validation' => $downside,
            'candidate_comparison_table' => array_values($comparisonTable),
            'anti_overfit_summary' => $anti,
            'candidate_safety_audit' => $this->candidateSafetyAudit($targetCandidateCode, $anti),
            'not_evaluable_reasons' => array_values($notEvaluableReasons),
            'redesign_decision_notes' => $this->redesignDecisionNotes($targetCandidateCode, $anti),
        ];
    }

    private function validationSlice(
        string $layer,
        string $slice,
        array $baselineRows,
        array $targetRows,
        array $evaluatedRows,
        string $targetCandidateCode,
        ?string $windowCode = null
    ): array {
        $scopeMonths = $this->uniqueMonths($baselineRows);
        $baseline = $this->candidateValidationMetrics(
            self::BASELINE_CANDIDATE_CODE,
            $layer,
            $slice,
            count($baselineRows) > 0 ? 'EVALUATED' : 'NOT_EVALUABLE',
            $baselineRows,
            $baselineRows,
            $scopeMonths
        );
        $target = $this->candidateValidationMetrics(
            $targetCandidateCode,
            $layer,
            $slice,
            count($targetRows) > 0 ? 'EVALUATED' : 'NOT_EVALUABLE',
            $evaluatedRows,
            $targetRows,
            $scopeMonths
        );
        $comparison = $this->candidateComparison($target, $baseline);
        $decision = $this->sliceResult($target, $baseline, $comparison);

        return [
            'validation_layer' => $layer,
            'validation_slice' => $slice,
            'window_code' => $windowCode,
            'baseline_candidate' => $baseline,
            'target_candidate' => $target,
            'comparison_vs_baseline' => $comparison,
            'result' => $decision['result'],
            'reason_code' => $decision['reason_code'],
            'message' => $decision['message'],
        ];
    }

    private function candidateValidationMetrics(
        string $candidateCode,
        string $layer,
        string $slice,
        string $status,
        array $evaluatedRows,
        array $selectedRows,
        array $scopeMonths
    ): array {
        $metrics = $this->metrics($selectedRows, $scopeMonths);

        return array_merge([
            'candidate_code' => $candidateCode,
            'validation_layer' => $layer,
            'validation_slice' => $slice,
            'candidate_status' => $status,
            'evaluated_rows' => count($evaluatedRows),
            'selected_rows' => count($selectedRows),
        ], $metrics, [
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
            'candidate_is_not_production' => true,
        ]);
    }

    private function metrics(array $rows, array $scopeMonths): array
    {
        $values = [];
        $byMonth = [];
        $losses = 0;
        $lossesByMonth = [];

        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) {
                continue;
            }
            $values[] = $value;
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month === '') {
                $month = 'UNKNOWN';
            }
            $byMonth[$month][] = $value;
            if ($value < 0.0) {
                $losses++;
                $lossesByMonth[$month] = ($lossesByMonth[$month] ?? 0) + 1;
            }
        }

        sort($values);
        $count = count($values);
        $coverage = $this->monthCoverageMetrics($rows, $scopeMonths);

        if ($count === 0) {
            return array_merge([
                'avg_ret_net' => null,
                'median_ret_net' => null,
                'p25_ret_net' => null,
                'p10_ret_net' => null,
                'win_rate' => null,
                'month_win_rate_min' => null,
                'month_avg_ret_net_min' => null,
                'bad_month_like_count' => 0,
                'loss_concentration' => null,
                'ticker_concentration' => null,
                'branch_concentration' => null,
                'worst_month_avg_ret_net' => null,
                'max_month_loss_cluster' => null,
            ], $coverage);
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

        $maxLossCluster = null;
        if ($losses > 0 && count($lossesByMonth) > 0) {
            $maxLossCluster = max($lossesByMonth) / $losses;
        }

        return array_merge([
            'avg_ret_net' => array_sum($values) / $count,
            'median_ret_net' => $this->percentileSorted($values, 0.50),
            'p25_ret_net' => $this->percentileSorted($values, 0.25),
            'p10_ret_net' => $this->percentileSorted($values, 0.10),
            'win_rate' => $this->winCount($values) / $count,
            'month_win_rate_min' => count($monthWinRates) > 0 ? min($monthWinRates) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => $badMonthLike,
            'loss_concentration' => $losses / $count,
            'ticker_concentration' => $this->concentration($rows, 'ticker'),
            'branch_concentration' => $this->concentration($rows, 'selected_source_code'),
            'worst_month_avg_ret_net' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'max_month_loss_cluster' => $maxLossCluster,
        ], $coverage);
    }

    private function monthCoverageMetrics(array $rows, array $scopeMonths): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            if ($month === '') {
                continue;
            }
            $counts[$month] = ($counts[$month] ?? 0) + 1;
        }

        $selectedMonths = array_keys($counts);
        sort($selectedMonths);
        if (count($scopeMonths) === 0) {
            $scopeMonths = $selectedMonths;
        }

        $perMonth = [];
        foreach ($scopeMonths as $month) {
            $perMonth[] = (int) ($counts[$month] ?? 0);
        }
        sort($perMonth);

        return [
            'months_covered' => count($selectedMonths),
            'months_in_scope' => count($scopeMonths),
            'months_with_selected_rows' => count($selectedMonths),
            'min_selected_rows_per_month' => count($perMonth) > 0 ? min($perMonth) : null,
            'median_selected_rows_per_month' => count($perMonth) > 0 ? $this->percentileSorted($perMonth, 0.50) : null,
            'zero_pick_months' => count(array_filter($perMonth, function (int $count): bool { return $count === 0; })),
        ];
    }

    private function candidateComparison(array $target, array $baseline): array
    {
        return [
            'delta_avg_ret_net_vs_baseline' => $this->delta($target['avg_ret_net'] ?? null, $baseline['avg_ret_net'] ?? null),
            'delta_median_ret_net_vs_baseline' => $this->delta($target['median_ret_net'] ?? null, $baseline['median_ret_net'] ?? null),
            'delta_p25_ret_net_vs_baseline' => $this->delta($target['p25_ret_net'] ?? null, $baseline['p25_ret_net'] ?? null),
            'delta_p10_ret_net_vs_baseline' => $this->delta($target['p10_ret_net'] ?? null, $baseline['p10_ret_net'] ?? null),
            'delta_win_rate_vs_baseline' => $this->delta($target['win_rate'] ?? null, $baseline['win_rate'] ?? null),
            'delta_month_win_rate_min_vs_baseline' => $this->delta($target['month_win_rate_min'] ?? null, $baseline['month_win_rate_min'] ?? null),
            'delta_month_avg_ret_net_min_vs_baseline' => $this->delta($target['month_avg_ret_net_min'] ?? null, $baseline['month_avg_ret_net_min'] ?? null),
            'delta_bad_month_like_count_vs_baseline' => $this->delta($target['bad_month_like_count'] ?? null, $baseline['bad_month_like_count'] ?? null),
            'delta_loss_concentration_vs_baseline' => $this->delta($target['loss_concentration'] ?? null, $baseline['loss_concentration'] ?? null),
            'delta_ticker_concentration_vs_baseline' => $this->delta($target['ticker_concentration'] ?? null, $baseline['ticker_concentration'] ?? null),
            'delta_branch_concentration_vs_baseline' => $this->delta($target['branch_concentration'] ?? null, $baseline['branch_concentration'] ?? null),
            'delta_months_covered_vs_baseline' => $this->delta($target['months_covered'] ?? null, $baseline['months_covered'] ?? null),
            'delta_min_selected_rows_per_month_vs_baseline' => $this->delta($target['min_selected_rows_per_month'] ?? null, $baseline['min_selected_rows_per_month'] ?? null),
            'delta_max_month_loss_cluster_vs_baseline' => $this->delta($target['max_month_loss_cluster'] ?? null, $baseline['max_month_loss_cluster'] ?? null),
        ];
    }

    private function sliceResult(array $target, array $baseline, array $comparison): array
    {
        if (($baseline['candidate_status'] ?? null) !== 'EVALUATED' || ($target['candidate_status'] ?? null) !== 'EVALUATED') {
            return [
                'result' => 'NOT_EVALUABLE',
                'reason_code' => 'C37_VALIDATION_SLICE_NOT_EVALUABLE',
                'message' => 'Baseline or target candidate has no selected rows in this IS validation slice.',
            ];
        }

        $avg = $comparison['delta_avg_ret_net_vs_baseline'];
        $median = $comparison['delta_median_ret_net_vs_baseline'];
        $p25 = $comparison['delta_p25_ret_net_vs_baseline'];
        $p10 = $comparison['delta_p10_ret_net_vs_baseline'];
        $win = $comparison['delta_win_rate_vs_baseline'];
        $monthAvgMin = $comparison['delta_month_avg_ret_net_min_vs_baseline'];
        $badMonths = $comparison['delta_bad_month_like_count_vs_baseline'];
        $loss = $comparison['delta_loss_concentration_vs_baseline'];

        $pass = ($avg !== null && $avg >= 0.0)
            && ($median === null || $median >= -0.0010)
            && ($p25 === null || $p25 >= -0.0025)
            && ($p10 === null || $p10 >= -0.0040)
            && ($win === null || $win >= -0.0200)
            && ($monthAvgMin === null || $monthAvgMin >= -0.0050)
            && ($badMonths === null || $badMonths <= 0.0)
            && ($loss === null || $loss <= 0.0500);

        if ($pass) {
            return [
                'result' => 'PASS',
                'reason_code' => 'C37_VALIDATION_SLICE_PASS',
                'message' => 'Candidate is improved or not materially worse than baseline in this IS validation slice.',
            ];
        }

        $warning = (($avg !== null && $avg >= 0.0) || ($median !== null && $median >= 0.0) || ($p25 !== null && $p25 >= 0.0))
            && ($p25 === null || $p25 >= -0.0050)
            && ($p10 === null || $p10 >= -0.0080)
            && ($win === null || $win >= -0.0500)
            && ($monthAvgMin === null || $monthAvgMin >= -0.0100)
            && ($badMonths === null || $badMonths <= 1.0)
            && ($loss === null || $loss <= 0.1000);

        if ($warning) {
            return [
                'result' => 'WARNING',
                'reason_code' => 'C37_VALIDATION_SLICE_WARNING',
                'message' => 'Candidate improves part of the IS slice but needs review because one stability metric is borderline.',
            ];
        }

        return [
            'result' => 'FAIL',
            'reason_code' => 'C37_VALIDATION_SLICE_FAIL',
            'message' => 'Candidate is materially worse than baseline in this IS validation slice.',
        ];
    }

    private function tickerConcentrationValidation(array $baselineRows, array $targetRows): array
    {
        if (! $this->fieldAvailable($baselineRows, 'ticker') || ! $this->fieldAvailable($targetRows, 'ticker')) {
            return [
                'validation_layer' => 'TICKER_CONCENTRATION',
                'validation_slice' => 'FULL_IS',
                'result' => 'NOT_EVALUABLE',
                'reason_code' => 'C37_TICKER_FIELD_UNAVAILABLE',
                'message' => 'Ticker field is unavailable for ticker concentration validation.',
            ];
        }

        $baseline = $this->tickerConcentrationSummary($baselineRows);
        $target = $this->tickerConcentrationSummary($targetRows);
        $delta = [
            'delta_top_1_ticker_share_vs_baseline' => $this->delta($target['top_1_ticker_share'], $baseline['top_1_ticker_share']),
            'delta_top_3_ticker_share_vs_baseline' => $this->delta($target['top_3_ticker_share'], $baseline['top_3_ticker_share']),
            'delta_top_5_ticker_share_vs_baseline' => $this->delta($target['top_5_ticker_share'], $baseline['top_5_ticker_share']),
            'delta_loss_top_1_ticker_share_vs_baseline' => $this->delta($target['loss_top_1_ticker_share'], $baseline['loss_top_1_ticker_share']),
            'delta_loss_top_3_ticker_share_vs_baseline' => $this->delta($target['loss_top_3_ticker_share'], $baseline['loss_top_3_ticker_share']),
            'delta_unique_ticker_count_vs_baseline' => $this->delta($target['unique_ticker_count'], $baseline['unique_ticker_count']),
        ];

        $result = 'PASS';
        $reason = 'C37_TICKER_CONCENTRATION_PASS';
        $message = 'Ticker concentration is not materially worse than baseline.';
        if (($delta['delta_top_1_ticker_share_vs_baseline'] ?? 0.0) > 0.15 || ($delta['delta_loss_top_1_ticker_share_vs_baseline'] ?? 0.0) > 0.20) {
            $result = 'FAIL';
            $reason = 'C37_TICKER_CONCENTRATION_FAIL';
            $message = 'Ticker concentration worsens materially versus baseline.';
        } elseif (($delta['delta_top_1_ticker_share_vs_baseline'] ?? 0.0) > 0.05 || ($delta['delta_loss_top_1_ticker_share_vs_baseline'] ?? 0.0) > 0.10 || ($delta['delta_top_3_ticker_share_vs_baseline'] ?? 0.0) > 0.15) {
            $result = 'WARNING';
            $reason = 'C37_TICKER_CONCENTRATION_WARNING';
            $message = 'Ticker concentration is higher than baseline and should be reviewed before OOS proof.';
        }

        return [
            'validation_layer' => 'TICKER_CONCENTRATION',
            'validation_slice' => 'FULL_IS',
            'baseline' => $baseline,
            'candidate' => $target,
            'comparison_vs_baseline' => $delta,
            'ticker_concentration_not_worse_materially' => in_array($result, ['PASS', 'WARNING'], true),
            'result' => $result,
            'reason_code' => $reason,
            'message' => $message,
        ];
    }

    private function branchConcentrationValidation(array $baselineRows, array $targetRows, array $g21Rows, array $g16Rows): array
    {
        if (! $this->fieldAvailable($baselineRows, 'selected_source_code') || ! $this->fieldAvailable($targetRows, 'selected_source_code')) {
            return [
                'validation_layer' => 'BRANCH_CONCENTRATION',
                'validation_slice' => 'FULL_IS',
                'result' => 'NOT_EVALUABLE',
                'reason_code' => 'C37_BRANCH_FIELD_UNAVAILABLE',
                'message' => 'Branch field is unavailable for branch concentration validation.',
            ];
        }

        $baseline = $this->branchConcentrationSummary($baselineRows);
        $target = $this->branchConcentrationSummary($targetRows);
        $deltaTop = $this->delta($target['top_branch_share'], $baseline['top_branch_share']);
        $selectedShare = count($baselineRows) > 0 ? count($targetRows) / count($baselineRows) : null;
        $removedG21 = max(0, count($g21Rows) - count($this->filterRowsByBranch($targetRows, 'G21')));
        $keptG16 = count($this->filterRowsByBranch($targetRows, 'G16'));

        $result = 'PASS';
        $reason = 'C37_BRANCH_CONCENTRATION_PASS';
        $message = 'Branch concentration is controlled by the C36 suppression rule and keeps enough G16 comparator rows.';
        if ($selectedShare !== null && $selectedShare < 0.20) {
            $result = 'FAIL';
            $reason = 'C37_BRANCH_CONCENTRATION_FAIL';
            $message = 'Candidate branch selection is too sparse versus baseline.';
        } elseif (($deltaTop ?? 0.0) > 0.25) {
            $result = 'WARNING';
            $reason = 'C37_BRANCH_CONCENTRATION_WARNING';
            $message = 'Branch concentration increases because C36 suppresses G21 and keeps G16 as comparator; review before OOS proof.';
        }

        return [
            'validation_layer' => 'BRANCH_CONCENTRATION',
            'validation_slice' => 'FULL_IS',
            'baseline' => $baseline,
            'candidate' => $target,
            'comparison_vs_baseline' => [
                'delta_top_branch_share_vs_baseline' => $deltaTop,
                'delta_g21_share_vs_baseline' => $this->delta($target['g21_share'], $baseline['g21_share']),
                'delta_g16_share_vs_baseline' => $this->delta($target['g16_share'], $baseline['g16_share']),
                'selected_rows_share_vs_baseline' => $selectedShare,
            ],
            'branch_distribution' => $target['branch_distribution'],
            'top_branch_share' => $target['top_branch_share'],
            'g21_share' => $target['g21_share'],
            'g16_share' => $target['g16_share'],
            'removed_or_suppressed_g21_rows' => $removedG21,
            'kept_g16_rows' => $keptG16,
            'result' => $result,
            'reason_code' => $reason,
            'message' => $message,
        ];
    }

    private function monthCoverageValidation(array $full): array
    {
        $baseline = $full['baseline_candidate'] ?? [];
        $target = $full['target_candidate'] ?? [];
        $baselineMin = $this->num($baseline['min_selected_rows_per_month'] ?? null);
        $targetMin = $this->num($target['min_selected_rows_per_month'] ?? null);
        $baselineMedian = $this->num($baseline['median_selected_rows_per_month'] ?? null);
        $targetMedian = $this->num($target['median_selected_rows_per_month'] ?? null);
        $zero = (int) ($target['zero_pick_months'] ?? 0);
        $monthsCoveredDelta = $this->delta($target['months_covered'] ?? null, $baseline['months_covered'] ?? null);
        $minDelta = $this->delta($target['min_selected_rows_per_month'] ?? null, $baseline['min_selected_rows_per_month'] ?? null);

        $result = 'PASS';
        $reason = 'C37_MONTH_COVERAGE_PASS';
        $message = 'Candidate keeps month coverage across the IS period.';
        if ($zero > 0) {
            $result = 'FAIL';
            $reason = 'C37_MONTH_COVERAGE_FAIL';
            $message = 'Candidate creates zero-pick IS months.';
        } elseif ($baselineMedian !== null && $targetMedian !== null && $targetMedian < $baselineMedian * 0.25) {
            $result = 'WARNING';
            $reason = 'C37_MONTH_COVERAGE_WARNING';
            $message = 'Candidate keeps coverage but selected rows per month are materially thinner than baseline.';
        } elseif ($baselineMin !== null && $targetMin !== null && $targetMin < max(1.0, $baselineMin * 0.20)) {
            $result = 'WARNING';
            $reason = 'C37_MONTH_COVERAGE_WARNING';
            $message = 'Candidate minimum selected rows per month is thinner than baseline.';
        }

        return [
            'validation_layer' => 'MONTH_COVERAGE',
            'validation_slice' => 'FULL_IS',
            'baseline' => [
                'months_covered' => $baseline['months_covered'] ?? null,
                'months_with_selected_rows' => $baseline['months_with_selected_rows'] ?? null,
                'min_selected_rows_per_month' => $baseline['min_selected_rows_per_month'] ?? null,
                'median_selected_rows_per_month' => $baseline['median_selected_rows_per_month'] ?? null,
                'zero_pick_months' => $baseline['zero_pick_months'] ?? null,
            ],
            'candidate' => [
                'months_covered' => $target['months_covered'] ?? null,
                'months_with_selected_rows' => $target['months_with_selected_rows'] ?? null,
                'min_selected_rows_per_month' => $target['min_selected_rows_per_month'] ?? null,
                'median_selected_rows_per_month' => $target['median_selected_rows_per_month'] ?? null,
                'zero_pick_months' => $target['zero_pick_months'] ?? null,
            ],
            'comparison_vs_baseline' => [
                'delta_months_covered_vs_baseline' => $monthsCoveredDelta,
                'delta_min_selected_rows_per_month_vs_baseline' => $minDelta,
                'delta_median_selected_rows_per_month_vs_baseline' => $this->delta($target['median_selected_rows_per_month'] ?? null, $baseline['median_selected_rows_per_month'] ?? null),
            ],
            'month_coverage_not_too_sparse' => in_array($result, ['PASS', 'WARNING'], true),
            'result' => $result,
            'reason_code' => $reason,
            'message' => $message,
        ];
    }

    private function downsideStabilityValidation(array $full): array
    {
        $baseline = $full['baseline_candidate'] ?? [];
        $target = $full['target_candidate'] ?? [];
        $comparison = $full['comparison_vs_baseline'] ?? [];
        $decision = $this->sliceResult($target, $baseline, $comparison);
        $result = $decision['result'];
        $reason = $result === 'FAIL' ? 'C37_DOWNSIDE_STABILITY_FAIL' : ($result === 'WARNING' ? 'C37_DOWNSIDE_STABILITY_WARNING' : 'C37_DOWNSIDE_STABILITY_PASS');

        return [
            'validation_layer' => 'DOWNSIDE_STABILITY',
            'validation_slice' => 'FULL_IS',
            'baseline' => [
                'p25_ret_net' => $baseline['p25_ret_net'] ?? null,
                'p10_ret_net' => $baseline['p10_ret_net'] ?? null,
                'worst_month_avg_ret_net' => $baseline['worst_month_avg_ret_net'] ?? null,
                'month_avg_ret_net_min' => $baseline['month_avg_ret_net_min'] ?? null,
                'bad_month_like_count' => $baseline['bad_month_like_count'] ?? null,
                'loss_concentration' => $baseline['loss_concentration'] ?? null,
                'max_month_loss_cluster' => $baseline['max_month_loss_cluster'] ?? null,
            ],
            'candidate' => [
                'p25_ret_net' => $target['p25_ret_net'] ?? null,
                'p10_ret_net' => $target['p10_ret_net'] ?? null,
                'worst_month_avg_ret_net' => $target['worst_month_avg_ret_net'] ?? null,
                'month_avg_ret_net_min' => $target['month_avg_ret_net_min'] ?? null,
                'bad_month_like_count' => $target['bad_month_like_count'] ?? null,
                'loss_concentration' => $target['loss_concentration'] ?? null,
                'max_month_loss_cluster' => $target['max_month_loss_cluster'] ?? null,
            ],
            'comparison_vs_baseline' => [
                'delta_p25_ret_net_vs_baseline' => $comparison['delta_p25_ret_net_vs_baseline'] ?? null,
                'delta_p10_ret_net_vs_baseline' => $comparison['delta_p10_ret_net_vs_baseline'] ?? null,
                'delta_month_avg_ret_net_min_vs_baseline' => $comparison['delta_month_avg_ret_net_min_vs_baseline'] ?? null,
                'delta_bad_month_like_count_vs_baseline' => $comparison['delta_bad_month_like_count_vs_baseline'] ?? null,
                'delta_loss_concentration_vs_baseline' => $comparison['delta_loss_concentration_vs_baseline'] ?? null,
                'delta_max_month_loss_cluster_vs_baseline' => $comparison['delta_max_month_loss_cluster_vs_baseline'] ?? null,
            ],
            'downside_stability_not_worse_materially' => in_array($result, ['PASS', 'WARNING'], true),
            'result' => $result,
            'reason_code' => $reason,
            'message' => $decision['message'],
        ];
    }

    private function antiOverfitSummary(array $full, array $yearly, array $rolling, array $bad, array $normal, array $ticker, array $branch, array $coverage, array $downside): array
    {
        $fullResult = (string) ($full['result'] ?? 'NOT_EVALUABLE');
        $yearlyResult = $this->aggregateResults(array_map(function (array $row): string { return (string) ($row['result'] ?? 'NOT_EVALUABLE'); }, $yearly));
        $rollingResult = $this->aggregateResults(array_map(function (array $row): string { return (string) ($row['result'] ?? 'NOT_EVALUABLE'); }, $rolling));
        $badResult = (string) ($bad['result'] ?? 'NOT_EVALUABLE');
        $normalResult = (string) ($normal['result'] ?? 'NOT_EVALUABLE');
        $tickerResult = (string) ($ticker['result'] ?? 'NOT_EVALUABLE');
        $branchResult = (string) ($branch['result'] ?? 'NOT_EVALUABLE');
        $coverageResult = (string) ($coverage['result'] ?? 'NOT_EVALUABLE');
        $downsideResult = (string) ($downside['result'] ?? 'NOT_EVALUABLE');

        $layerResults = [
            'full_is_result' => $fullResult,
            'yearly_validation_result' => $yearlyResult,
            'rolling_validation_result' => $rollingResult,
            'bad_month_stress_result' => $badResult,
            'normal_month_result' => $normalResult,
            'ticker_concentration_result' => $tickerResult,
            'branch_concentration_result' => $branchResult,
            'month_coverage_result' => $coverageResult,
            'downside_stability_result' => $downsideResult,
        ];

        $fullComparison = $full['comparison_vs_baseline'] ?? [];
        $checks = [
            'production_ready' => false,
            'oos_data_used_for_tuning' => false,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'candidate_is_not_production' => true,
            'full_is_improved' => (($fullComparison['delta_avg_ret_net_vs_baseline'] ?? null) !== null && $fullComparison['delta_avg_ret_net_vs_baseline'] >= 0.0)
                || (($fullComparison['delta_p25_ret_net_vs_baseline'] ?? null) !== null && $fullComparison['delta_p25_ret_net_vs_baseline'] >= 0.0)
                || (($fullComparison['delta_win_rate_vs_baseline'] ?? null) !== null && $fullComparison['delta_win_rate_vs_baseline'] >= 0.0),
            'yearly_validation_pass_or_warning' => $this->passOrWarning($yearlyResult),
            'rolling_validation_pass_or_warning' => $this->passOrWarning($rollingResult),
            'bad_month_stress_not_worse_materially' => $this->passOrWarning($badResult),
            'normal_month_not_worse_materially' => $this->passOrWarning($normalResult),
            'ticker_concentration_not_worse_materially' => $this->passOrWarning($tickerResult),
            'branch_concentration_not_worse_materially' => $this->passOrWarning($branchResult),
            'month_coverage_not_too_sparse' => $this->passOrWarning($coverageResult),
            'downside_stability_not_worse_materially' => $this->passOrWarning($downsideResult),
        ];

        $overall = $this->aggregateResults(array_values($layerResults));
        if ($overall === 'PASS' && ! $checks['full_is_improved']) {
            $overall = 'WARNING';
        }

        $decision = 'C37_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS';
        $reason = 'Candidate has IS validation warnings that require review before any C38 OOS proof.';
        if ($overall === 'PASS') {
            $decision = 'C37_CANDIDATE_CAN_ADVANCE_TO_C38_OOS_PROOF';
            $reason = 'Candidate passed C37 IS anti-overfit validation and may be locked for C38 OOS proof; it is not production-ready.';
        } elseif ($overall === 'FAIL') {
            $decision = 'C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION';
            $reason = 'Candidate failed at least one material IS anti-overfit validation layer.';
        } elseif ($overall === 'NOT_EVALUABLE') {
            $decision = 'C37_CANDIDATE_TOO_SPARSE_FOR_VALIDATION';
            $reason = 'Candidate does not have enough IS evidence for anti-overfit validation.';
        }

        return array_merge($layerResults, $checks, [
            'overall_anti_overfit_result' => $overall,
            'candidate_c37_decision' => $decision,
            'candidate_c37_decision_reason' => $reason,
            'no_oos_proof' => true,
            'no_oos_tuning' => true,
            'no_best_of_oos' => true,
            'no_production_catalog' => true,
            'no_candidate_promoted' => true,
            'production_ready' => false,
        ]);
    }

    private function validationSummary(array $anti): array
    {
        $results = [
            $anti['full_is_result'] ?? 'NOT_EVALUABLE',
            $anti['yearly_validation_result'] ?? 'NOT_EVALUABLE',
            $anti['rolling_validation_result'] ?? 'NOT_EVALUABLE',
            $anti['bad_month_stress_result'] ?? 'NOT_EVALUABLE',
            $anti['normal_month_result'] ?? 'NOT_EVALUABLE',
            $anti['ticker_concentration_result'] ?? 'NOT_EVALUABLE',
            $anti['branch_concentration_result'] ?? 'NOT_EVALUABLE',
            $anti['month_coverage_result'] ?? 'NOT_EVALUABLE',
            $anti['downside_stability_result'] ?? 'NOT_EVALUABLE',
        ];

        return [
            'total_validation_layers' => count($results),
            'passed_layers' => count(array_filter($results, function (string $result): bool { return $result === 'PASS'; })),
            'warning_layers' => count(array_filter($results, function (string $result): bool { return $result === 'WARNING'; })),
            'failed_layers' => count(array_filter($results, function (string $result): bool { return $result === 'FAIL'; })),
            'not_evaluable_layers' => count(array_filter($results, function (string $result): bool { return $result === 'NOT_EVALUABLE'; })),
            'overall_anti_overfit_result' => $anti['overall_anti_overfit_result'] ?? 'NOT_EVALUABLE',
            'candidate_c37_decision' => $anti['candidate_c37_decision'] ?? 'C37_PENDING',
            'candidate_c37_decision_reason' => $anti['candidate_c37_decision_reason'] ?? 'C37 validation has not completed.',
        ];
    }

    private function candidateSafetyAudit(string $targetCandidateCode, array $anti): array
    {
        $rows = [];
        foreach ([
            'FULL_IS' => $anti['full_is_result'] ?? 'NOT_EVALUABLE',
            'YEARLY_IS' => $anti['yearly_validation_result'] ?? 'NOT_EVALUABLE',
            'ROLLING_IS' => $anti['rolling_validation_result'] ?? 'NOT_EVALUABLE',
            'BAD_MONTH_LIKE_STRESS' => $anti['bad_month_stress_result'] ?? 'NOT_EVALUABLE',
            'NON_BAD_MONTH' => $anti['normal_month_result'] ?? 'NOT_EVALUABLE',
            'TICKER_CONCENTRATION' => $anti['ticker_concentration_result'] ?? 'NOT_EVALUABLE',
            'BRANCH_CONCENTRATION' => $anti['branch_concentration_result'] ?? 'NOT_EVALUABLE',
            'MONTH_COVERAGE' => $anti['month_coverage_result'] ?? 'NOT_EVALUABLE',
            'DOWNSIDE_STABILITY' => $anti['downside_stability_result'] ?? 'NOT_EVALUABLE',
        ] as $layer => $result) {
            $passed = in_array($result, ['PASS', 'WARNING'], true);
            $rows[] = [
                'candidate_code' => $targetCandidateCode,
                'validation_layer' => $layer,
                'passed' => $passed,
                'reason_code' => $passed ? 'WS_BT_C37_CANDIDATE_SELECTION_INPUT_SAFE' : 'WS_BT_C37_CANDIDATE_VALIDATION_LAYER_NOT_SAFE_TO_ADVANCE',
                'message' => $passed
                    ? 'Candidate selection inputs do not use return, future path, OOS data, OOS proof, or production promotion.'
                    : 'Candidate validation layer does not pass or warn safely enough to advance without more evidence.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_data_used_for_tuning' => false,
                'production_ready' => false,
                'candidate_is_not_production' => true,
                'no_oos_proof' => true,
                'no_best_of_oos' => true,
                'no_oos_winner' => true,
                'no_production_catalog' => true,
                'no_candidate_promoted' => true,
                'no_plan_confirm_mutation' => true,
            ];
        }

        return $rows;
    }

    private function redesignDecisionNotes(string $targetCandidateCode, array $anti): array
    {
        return [
            [
                'note_code' => 'C37_CANDIDATE_NOT_PRODUCTION',
                'candidate_code' => $targetCandidateCode,
                'message' => 'C37 validation target remains a diagnostic C36 candidate and production_ready=false.',
            ],
            [
                'note_code' => 'C37_NO_OOS_PROOF_EXECUTED',
                'candidate_code' => $targetCandidateCode,
                'message' => 'C37 is IS validation only; it does not execute OOS proof, best-of-OOS selection, catalog promotion, or PLAN/CONFIRM mutation.',
            ],
            [
                'note_code' => 'C37_ANTI_OVERFIT_RESULT',
                'candidate_code' => $targetCandidateCode,
                'message' => 'C37 anti-overfit result is '.$this->scalar($anti['overall_anti_overfit_result'] ?? 'NOT_EVALUABLE').'.',
            ],
        ];
    }

    private function comparisonRow(array $validation): array
    {
        $target = $validation['target_candidate'] ?? [];
        $baseline = $validation['baseline_candidate'] ?? [];
        $comparison = $validation['comparison_vs_baseline'] ?? [];

        return array_merge([
            'validation_layer' => $validation['validation_layer'] ?? null,
            'validation_slice' => $validation['validation_slice'] ?? null,
            'window_code' => $validation['window_code'] ?? null,
            'baseline_candidate_code' => $baseline['candidate_code'] ?? self::BASELINE_CANDIDATE_CODE,
            'candidate_code' => $target['candidate_code'] ?? null,
            'candidate_status' => $target['candidate_status'] ?? null,
            'evaluated_rows' => $target['evaluated_rows'] ?? null,
            'selected_rows' => $target['selected_rows'] ?? null,
            'avg_ret_net' => $target['avg_ret_net'] ?? null,
            'median_ret_net' => $target['median_ret_net'] ?? null,
            'p25_ret_net' => $target['p25_ret_net'] ?? null,
            'p10_ret_net' => $target['p10_ret_net'] ?? null,
            'win_rate' => $target['win_rate'] ?? null,
            'month_win_rate_min' => $target['month_win_rate_min'] ?? null,
            'month_avg_ret_net_min' => $target['month_avg_ret_net_min'] ?? null,
            'bad_month_like_count' => $target['bad_month_like_count'] ?? null,
            'loss_concentration' => $target['loss_concentration'] ?? null,
            'ticker_concentration' => $target['ticker_concentration'] ?? null,
            'branch_concentration' => $target['branch_concentration'] ?? null,
            'months_covered' => $target['months_covered'] ?? null,
            'min_selected_rows_per_month' => $target['min_selected_rows_per_month'] ?? null,
            'result' => $validation['result'] ?? null,
            'production_ready' => false,
            'candidate_is_not_production' => true,
        ], $comparison);
    }

    private function specialComparisonRow(string $layer, string $slice, string $targetCandidateCode, array $validation): array
    {
        $comparison = $validation['comparison_vs_baseline'] ?? [];

        return array_merge([
            'validation_layer' => $layer,
            'validation_slice' => $slice,
            'window_code' => null,
            'baseline_candidate_code' => self::BASELINE_CANDIDATE_CODE,
            'candidate_code' => $targetCandidateCode,
            'candidate_status' => 'EVALUATED',
            'evaluated_rows' => null,
            'selected_rows' => null,
            'result' => $validation['result'] ?? 'NOT_EVALUABLE',
            'production_ready' => false,
            'candidate_is_not_production' => true,
        ], $comparison);
    }

    private function tickerConcentrationSummary(array $rows): array
    {
        return [
            'top_1_ticker_share' => $this->topShare($rows, 'ticker', 1),
            'top_3_ticker_share' => $this->topShare($rows, 'ticker', 3),
            'top_5_ticker_share' => $this->topShare($rows, 'ticker', 5),
            'unique_ticker_count' => $this->uniqueCount($rows, 'ticker'),
            'loss_top_1_ticker_share' => $this->topShare($this->lossRows($rows), 'ticker', 1),
            'loss_top_3_ticker_share' => $this->topShare($this->lossRows($rows), 'ticker', 3),
        ];
    }

    private function branchConcentrationSummary(array $rows): array
    {
        $distribution = $this->distribution($rows, 'selected_source_code');

        return [
            'branch_distribution' => $distribution,
            'top_branch_share' => $this->topShare($rows, 'selected_source_code', 1),
            'g21_share' => $this->valueShare($rows, 'selected_source_code', 'G21'),
            'g16_share' => $this->valueShare($rows, 'selected_source_code', 'G16'),
        ];
    }

    private function c36NotEvaluableReasons(array $c36): array
    {
        $out = [];
        foreach (($c36['not_evaluable_reasons'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'validation_layer' => 'C36_CANDIDATE_FORMATION',
                'validation_slice' => (string) ($row['candidate_code'] ?? 'C36_NOT_EVALUABLE_CANDIDATE'),
                'reason_code' => (string) ($row['reason_code'] ?? 'C36_NOT_EVALUABLE'),
                'message' => (string) ($row['message'] ?? 'C36 candidate remained not evaluable from available pre-trade fields.'),
            ];
        }
        return $out;
    }

    private function sliceNotEvaluableReasons(array $validation): array
    {
        if (($validation['result'] ?? null) !== 'NOT_EVALUABLE') {
            return [];
        }

        return [[
            'validation_layer' => (string) ($validation['validation_layer'] ?? 'UNKNOWN'),
            'validation_slice' => (string) ($validation['validation_slice'] ?? 'UNKNOWN'),
            'reason_code' => (string) ($validation['reason_code'] ?? 'C37_VALIDATION_SLICE_NOT_EVALUABLE'),
            'message' => (string) ($validation['message'] ?? 'Validation slice is not evaluable.'),
        ]];
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED',
                'message' => 'C37 completed IS validation and anti-overfit checks from the locked C36 candidate and C28/C35/C36 IS evidence only.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C37_NO_OOS_PROOF_NO_PRODUCTION_PROMOTION',
                'message' => 'C37 did not run OOS proof, did not use OOS returns for tuning, did not create a production catalog, and did not mutate PLAN/CONFIRM.',
                'fatal' => false,
                'extra' => [
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                    'candidate_is_not_production' => true,
                ],
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C37_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C37 diagnostic conclusion derived from IS validation layers only.',
                'fatal' => false,
            ],
        ];
    }

    private function candidateByCode(array $c36, string $code): ?array
    {
        foreach (($c36['candidate_results'] ?? []) as $row) {
            if (is_array($row) && (string) ($row['candidate_code'] ?? '') === $code) {
                return $row;
            }
        }
        return null;
    }

    private function secondaryCandidateCodes(array $c36): array
    {
        $wanted = [
            'C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE',
            'C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE',
        ];
        $out = [];
        foreach (($c36['candidate_results'] ?? []) as $row) {
            $code = is_array($row) ? (string) ($row['candidate_code'] ?? '') : '';
            if (in_array($code, $wanted, true)) {
                $out[] = $code;
            }
        }
        return $out;
    }

    private function selectedRowsForCandidate(string $code, array $g21Rows, array $g16Rows, array $baselineRows, array $bestCandidate): array
    {
        if ($code === self::BASELINE_CANDIDATE_CODE) {
            return $baselineRows;
        }

        if (in_array($code, [
            self::PRIMARY_TARGET_CANDIDATE_CODE,
            'C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE',
            'C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE',
        ], true)) {
            return $g16Rows;
        }

        if (($bestCandidate['source_branch'] ?? null) === 'G16') {
            return $g16Rows;
        }

        if (($bestCandidate['source_branch'] ?? null) === 'G21') {
            return $this->filterRowsByBranch($baselineRows, 'G21');
        }

        return $baselineRows;
    }

    private function yearlySlices(string $from, string $to): array
    {
        return [
            '2023' => ['from' => max($from, '2023-01-01'), 'to' => min($to, '2023-12-31')],
            '2024' => ['from' => max($from, '2024-01-01'), 'to' => min($to, '2024-12-31')],
            '2025_partial_to_2025_05_21' => ['from' => max($from, '2025-01-01'), 'to' => min($to, '2025-05-21')],
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

    private function filterRowsByDate(array $rows, string $from, string $to): array
    {
        if (strcmp($from, $to) > 0) {
            return [];
        }
        return array_values(array_filter($rows, function (array $row) use ($from, $to): bool {
            $date = (string) ($row['trade_date'] ?? '');
            return $date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0;
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

    private function fieldAvailable(array $rows, string $field): bool
    {
        foreach ($rows as $row) {
            if (is_array($row) && array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== '') {
                return true;
            }
        }
        return false;
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

    private function uniqueCount(array $rows, string $field): int
    {
        $values = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? '');
            if ($value !== '') {
                $values[$value] = true;
            }
        }
        return count($values);
    }

    private function topShare(array $rows, string $field, int $topN): ?float
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
        arsort($counts);
        return array_sum(array_slice(array_values($counts), 0, $topN)) / $count;
    }

    private function concentration(array $rows, string $field): ?float
    {
        return $this->topShare($rows, $field, 1);
    }

    private function lossRows(array $rows): array
    {
        return array_values(array_filter($rows, function (array $row): bool {
            $value = $this->num($row['profile_ret_net'] ?? null);
            return $value !== null && $value < 0.0;
        }));
    }

    private function aggregateResults(array $results): string
    {
        $results = array_values(array_filter($results, function (string $result): bool { return $result !== ''; }));
        if (count($results) === 0) {
            return 'NOT_EVALUABLE';
        }
        if (in_array('FAIL', $results, true)) {
            return 'FAIL';
        }
        if (in_array('WARNING', $results, true)) {
            return 'WARNING';
        }
        if (in_array('NOT_EVALUABLE', $results, true)) {
            $hasPass = in_array('PASS', $results, true);
            return $hasPass ? 'WARNING' : 'NOT_EVALUABLE';
        }
        return 'PASS';
    }

    private function passOrWarning(string $result): bool
    {
        return in_array($result, ['PASS', 'WARNING'], true);
    }

    private function diagnosticConclusion(string $overall): string
    {
        if ($overall === 'PASS') {
            return 'C37_CANDIDATE_VALIDATED_FOR_OOS_PROOF_NEXT';
        }
        if ($overall === 'WARNING') {
            return 'C37_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS';
        }
        if ($overall === 'FAIL') {
            return 'C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK';
        }
        return 'C37_CANDIDATE_TOO_SPARSE_FOR_VALIDATION';
    }

    private function nextStepRecommendation(string $overall): string
    {
        if ($overall === 'PASS') {
            return 'C38_OOS_PROOF_WITH_LOCKED_C37_CANDIDATE';
        }
        if ($overall === 'WARNING') {
            return 'C38_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS';
        }
        if ($overall === 'FAIL') {
            return 'C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC';
        }
        return 'C38_EVIDENCE_EXPANSION_DIAGNOSTIC';
    }

    private function strictFalse($value): bool
    {
        return $value === false || $value === 0 || $value === '0';
    }

    private function winCount(array $values): int
    {
        $count = 0;
        foreach ($values as $value) {
            if ((float) $value > 0.0) {
                $count++;
            }
        }
        return $count;
    }

    private function percentileSorted(array $values, float $percentile): ?float
    {
        $count = count($values);
        if ($count === 0) {
            return null;
        }
        if ($count === 1) {
            return (float) $values[0];
        }
        $position = ($count - 1) * $percentile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $position - $lower;
        return (float) $values[$lower] * (1.0 - $weight) + (float) $values[$upper] * $weight;
    }

    private function delta($candidate, $baseline): ?float
    {
        $candidateNum = $this->num($candidate);
        $baselineNum = $this->num($baseline);
        if ($candidateNum === null || $baselineNum === null) {
            return null;
        }
        return $candidateNum - $baselineNum;
    }

    private function num($value): ?float
    {
        if ($value === '' || $value === null || ! is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value === null) {
            return '';
        }
        return (string) $value;
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
        $artifact['diagnostic_conclusion'] = $status === 'C37_BLOCKED_MISSING_IS_EVIDENCE'
            ? 'C37_INSUFFICIENT_IS_EVIDENCE_FOR_VALIDATION'
            : 'C37_NO_VALID_C36_CANDIDATE_FOUND';
        $artifact['next_step_recommendation'] = 'C38_EVIDENCE_EXPANSION_DIAGNOSTIC';
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
            'expected_c36_hash' => $artifact['expected_c36_hash'] ?? null,
            'actual_c36_hash' => $artifact['actual_c36_hash'] ?? null,
            'c36_hash_match' => $artifact['c36_hash_match'] ?? false,
            'c36_status' => $artifact['c36_status'] ?? null,
            'c36_diagnostic_conclusion' => $artifact['c36_diagnostic_conclusion'] ?? null,
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
                'status' => 'C37_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C37 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c36_hash' => $artifact['expected_c36_hash'] ?? null,
                'actual_c36_hash' => $artifact['actual_c36_hash'] ?? null,
                'c36_hash_match' => $artifact['c36_hash_match'] ?? false,
                'c36_status' => $artifact['c36_status'] ?? null,
                'c36_diagnostic_conclusion' => $artifact['c36_diagnostic_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }

        return [
            'status' => $artifact['status'] ?? 'C37_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C37_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c36_hash' => $artifact['expected_c36_hash'] ?? null,
            'actual_c36_hash' => $artifact['actual_c36_hash'] ?? null,
            'c36_hash_match' => $artifact['c36_hash_match'] ?? false,
            'c36_status' => $artifact['c36_status'] ?? null,
            'c36_diagnostic_conclusion' => $artifact['c36_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c36_summary' => $artifact['source_c36_summary'] ?? [],
            'validation_target' => $artifact['validation_target'] ?? [],
            'validation_summary' => $artifact['validation_summary'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C37 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
