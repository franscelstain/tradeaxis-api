<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC36IsControlledRedesignCandidateFormationService
{
    public const RUN_CODE = 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION';
    public const ARTIFACT_TYPE = 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION';
    public const DEFAULT_C35_ARTIFACT = 'storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json';
    public const DEFAULT_EXPECTED_C35_HASH = '1ab43b0dcee6d41d11b2ab0ed904721836dee3b1';
    public const DEFAULT_C35_FILE_SHA1 = '733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C35_STATUS = 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED';
    public const EXPECTED_C35_CONCLUSION = 'C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED';
    public const DEFAULT_IS_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';

    public function execute(
        string $c35Artifact = self::DEFAULT_C35_ARTIFACT,
        string $expectedC35Hash = self::DEFAULT_EXPECTED_C35_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c35Artifact = trim($c35Artifact) !== '' ? trim($c35Artifact) : self::DEFAULT_C35_ARTIFACT;
        $expectedC35Hash = trim($expectedC35Hash) !== '' ? trim($expectedC35Hash) : self::DEFAULT_EXPECTED_C35_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c35Artifact, $expectedC35Hash, null, null, null, $from, $to, $createdAt, self::DEFAULT_IS_EVIDENCE_ARTIFACT);

        if (! is_file($c35Artifact)) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_MISSING_C35_ARTIFACT',
                'WS_BT_C36_C35_ARTIFACT_MISSING',
                'C36 requires the locked C35 IS robustness redesign diagnostic artifact, but the file is missing.',
                $outputPath,
                ['input_c35_artifact' => $c35Artifact]
            );
        }

        $c35 = json_decode((string) file_get_contents($c35Artifact), true);
        if (! is_array($c35)) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_MISSING_C35_ARTIFACT',
                'WS_BT_C36_C35_ARTIFACT_UNREADABLE',
                'C35 artifact is not readable JSON.',
                $outputPath,
                ['input_c35_artifact' => $c35Artifact]
            );
        }

        $actualC35Hash = $this->stableHash($c35);
        $sourceEvidence = $this->sourceEvidencePath($c35, $options);
        $artifact = $this->baseArtifact(
            $c35Artifact,
            $expectedC35Hash,
            $actualC35Hash,
            $c35['status'] ?? null,
            $c35['diagnostic_conclusion'] ?? null,
            $from,
            $to,
            $createdAt,
            $sourceEvidence
        );
        $artifact['source_c35_summary'] = $this->sourceC35Summary($c35, $sourceEvidence);

        if ($actualC35Hash !== $expectedC35Hash) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_C35_HASH_MISMATCH',
                'WS_BT_C36_C35_ARTIFACT_HASH_MISMATCH',
                'C35 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c35_artifact_hash_field' => $c35['artifact_hash'] ?? null]
            );
        }

        if (($c35['status'] ?? null) !== self::EXPECTED_C35_STATUS) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_UNEXPECTED_C35_STATUS',
                'WS_BT_C36_UNEXPECTED_C35_STATUS',
                'C36 requires a completed C35 IS robustness redesign diagnostic artifact.',
                $outputPath,
                ['expected_c35_status' => self::EXPECTED_C35_STATUS]
            );
        }

        if (($c35['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C35_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_UNEXPECTED_C35_CONCLUSION',
                'WS_BT_C36_UNEXPECTED_C35_CONCLUSION',
                'C36 requires C35 to confirm G21 and G16 weakness from IS evidence.',
                $outputPath,
                ['expected_c35_diagnostic_conclusion' => self::EXPECTED_C35_CONCLUSION]
            );
        }

        if (($c35['production_ready'] ?? false) !== false && (int) ($c35['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_C35_PRODUCTION_READY_NOT_FALSE',
                'WS_BT_C36_C35_PRODUCTION_READY_NOT_FALSE',
                'C36 requires C35 production_ready=false before candidate formation.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (($c35['is_period']['oos_data_used_for_tuning'] ?? false) !== false && (int) ($c35['is_period']['oos_data_used_for_tuning'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_C35_OOS_TUNING_FLAG_NOT_FALSE',
                'WS_BT_C36_C35_OOS_TUNING_FLAG_NOT_FALSE',
                'C36 requires C35 oos_data_used_for_tuning=false.',
                $outputPath,
                ['expected_oos_data_used_for_tuning' => false]
            );
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_INVALID_IS_PERIOD',
                'WS_BT_C36_INVALID_IS_PERIOD',
                'C36 requires a valid IS period where from <= to.',
                $outputPath,
                ['from' => $from, 'to' => $to]
            );
        }

        if ($this->touchesOos($from, $to)) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'WS_BT_C36_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'C36 is IS-only and rejects runtime periods that touch the reserved OOS window.',
                $outputPath,
                ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM]
            );
        }

        if ($sourceEvidence === '' || ! is_file($sourceEvidence)) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C36_IS_EVIDENCE_ARTIFACT_MISSING',
                'C36 requires C35-linked IS diagnostic evidence rows; no IS evidence artifact is available.',
                $outputPath,
                ['source_evidence' => $sourceEvidence]
            );
        }

        $source = json_decode((string) file_get_contents($sourceEvidence), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C36_IS_EVIDENCE_ROWS_MISSING',
                'C36 requires pick_diagnostic_rows from the C35-linked IS artifact; the available artifact does not contain usable rows.',
                $outputPath,
                ['source_evidence' => $sourceEvidence]
            );
        }

        $allRows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21Rows = $this->targetRows($allRows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($allRows, 'G16', 'next_open_delay_after_close_signal');
        $targetRows = array_merge($g21Rows, $g16Rows);

        $artifact['source_c35_summary'] = [
            'g21_rows' => count($g21Rows),
            'g16_rows' => count($g16Rows),
            'g21_weakness_confirmed' => (bool) ($c35['g21_is_summary']['is_weakness_confirmed'] ?? false),
            'g16_weakness_confirmed' => (bool) ($c35['g16_is_summary']['is_weakness_confirmed'] ?? false),
            'source_evidence' => $sourceEvidence,
        ];

        if (count($targetRows) === 0) {
            return $this->blocked(
                $artifact,
                'C36_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C36_TARGET_BRANCH_ROWS_MISSING',
                'C36 found IS evidence but not enough G21/G16 target branch rows to form controlled candidates.',
                $outputPath,
                ['g21_rows' => count($g21Rows), 'g16_rows' => count($g16Rows)]
            );
        }

        $baseline = $this->candidateResult(
            'C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR',
            'BASELINE_COMPARATOR',
            'C35_BASELINE_CURRENT_BRANCH_BEHAVIOR',
            'G21_G16',
            'EVALUATED',
            $targetRows,
            $targetRows,
            [],
            'current_C35_G21_G16_branch_behavior',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            null
        );

        $candidateResults = [];
        $candidateResults[] = $baseline;

        $candidateResults[] = $this->candidateResult(
            'C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD',
            'G21_PRIMARY_REDESIGN',
            'C35_HYP_G21_FALLBACK_EXIT_TOO_LATE',
            'G21',
            'NOT_EVALUABLE',
            $g21Rows,
            [],
            $g21Rows,
            'requires_d2_close_or_intraday_path_field_not_available_in_C28_IS_rows',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            'C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE'
        );

        $candidateResults[] = $this->candidateResult(
            'C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE',
            'G21_PRIMARY_REDESIGN',
            'C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK',
            'G21',
            'EVALUATED',
            $targetRows,
            $g16Rows,
            $g21Rows,
            'suppress_G21_no_rule_profit_signal_before_fallback_using_branch_metadata_only',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            null
        );

        $candidateResults[] = $this->candidateResult(
            'C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK',
            'G21_PRIMARY_REDESIGN',
            'C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER',
            'G21',
            'NOT_EVALUABLE',
            $g21Rows,
            [],
            $g21Rows,
            'regime_feature_available_before_selection_not_present_in_C28_IS_rows',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            'C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE'
        );

        $candidateResults[] = $this->candidateResult(
            'C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE',
            'G16_SECONDARY_REDESIGN',
            'C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE',
            'G16',
            'NOT_EVALUABLE',
            $g16Rows,
            [],
            $g16Rows,
            'direct_gap_or_open_damage_pre_trade_field_not_available_in_C28_IS_rows',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            'C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE'
        );

        $candidateResults[] = $this->candidateResult(
            'C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE',
            'G16_SECONDARY_REDESIGN',
            'C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE',
            'G16',
            'EVALUATED',
            $g16Rows,
            $g16Rows,
            [],
            'keep_G16_as_positive_IS_comparator_no_change',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            null
        );

        $candidateResults[] = $this->candidateResult(
            'C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR',
            'COMBINED_CONTROLLED_COMPARATOR',
            'C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK+C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE',
            'G21_G16',
            'EVALUATED',
            $targetRows,
            $g16Rows,
            $g21Rows,
            'combine_G21_no_profit_suppression_with_G16_no_change_comparator',
            ['selected_source_code', 'bucket_code', 'trade_date', 'trade_month'],
            null
        );

        $comparison = $this->candidateComparisonTable($candidateResults, $baseline);
        $safetyAudit = $this->candidateSafetyAudit($candidateResults);
        $notEvaluable = $this->notEvaluableReasons($candidateResults);
        $formedCandidates = $this->formedCandidates($candidateResults, $comparison);
        $best = $this->bestCandidate($formedCandidates);

        $artifact['baseline_summary'] = $baseline;
        $artifact['candidate_results'] = $candidateResults;
        $artifact['candidate_comparison_table'] = $comparison;
        $artifact['candidate_safety_audit'] = $safetyAudit;
        $artifact['not_evaluable_reasons'] = $notEvaluable;
        $artifact['is_bad_month_like_candidate_effect'] = $this->badMonthLikeCandidateEffect($candidateResults);
        $artifact['ticker_failure_cluster_after_candidate'] = $this->tickerFailureClusterAfterCandidate($candidateResults);
        $artifact['redesign_decision_notes'] = $this->redesignDecisionNotes($candidateResults, $formedCandidates);
        $artifact['candidate_summary'] = [
            'total_candidates' => count($candidateResults),
            'evaluated_candidates' => count(array_filter($candidateResults, function (array $row): bool { return ($row['candidate_status'] ?? null) === 'EVALUATED'; })),
            'not_evaluable_candidates' => count(array_filter($candidateResults, function (array $row): bool { return ($row['candidate_status'] ?? null) === 'NOT_EVALUABLE'; })),
            'candidate_formed' => count($formedCandidates) > 0,
            'best_is_candidate_code' => $best['candidate_code'] ?? null,
            'best_is_candidate_is_not_production' => true,
        ];
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($candidateResults, $formedCandidates);
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($formedCandidates);
        $artifact['status'] = 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED';
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(
        string $inputC35Path,
        string $expectedC35Hash,
        ?string $actualC35Hash,
        $c35Status,
        $c35Conclusion,
        string $from,
        string $to,
        string $createdAt,
        string $sourceEvidence
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C36_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c35_artifact' => $inputC35Path,
            'expected_c35_hash' => $expectedC35Hash,
            'actual_c35_hash' => $actualC35Hash,
            'c35_hash_match' => $actualC35Hash !== null && $actualC35Hash === $expectedC35Hash,
            'c35_status' => $c35Status,
            'c35_diagnostic_conclusion' => $c35Conclusion,
            'expected_c35_file_sha1' => self::DEFAULT_C35_FILE_SHA1,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c35_summary' => [
                'g21_rows' => 0,
                'g16_rows' => 0,
                'g21_weakness_confirmed' => false,
                'g16_weakness_confirmed' => false,
                'source_evidence' => $sourceEvidence,
            ],
            'candidate_summary' => [
                'total_candidates' => 0,
                'evaluated_candidates' => 0,
                'not_evaluable_candidates' => 0,
                'candidate_formed' => false,
                'best_is_candidate_code' => null,
                'best_is_candidate_is_not_production' => true,
            ],
            'baseline_summary' => [],
            'candidate_results' => [],
            'candidate_comparison_table' => [],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'is_bad_month_like_candidate_effect' => [],
            'ticker_failure_cluster_after_candidate' => [],
            'redesign_decision_notes' => [],
            'diagnostic_conclusion' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_PENDING',
            'next_step_recommendation' => 'C36_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C36_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C36 locks C35 as source diagnostic evidence, forms IS-controlled candidates only, and does not run OOS proof or production promotion.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION' => true,
                'C35_ARTIFACT_HASH_LOCK' => true,
                'C36_CANDIDATE_FROM_C35_HYPOTHESES' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C35_MUTATION' => true,
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

    private function sourceEvidencePath(array $c35, array $options): string
    {
        if (isset($options['is_evidence_artifact']) && trim((string) $options['is_evidence_artifact']) !== '') {
            return trim((string) $options['is_evidence_artifact']);
        }
        $value = $c35['is_evidence_summary']['source']
            ?? $c35['source_c35_summary']['source_evidence']
            ?? self::DEFAULT_IS_EVIDENCE_ARTIFACT;
        return trim((string) $value);
    }

    private function sourceC35Summary(array $c35, string $sourceEvidence): array
    {
        return [
            'g21_rows' => (int) ($c35['is_evidence_summary']['g21_rows'] ?? $c35['source_c35_summary']['g21_rows'] ?? 0),
            'g16_rows' => (int) ($c35['is_evidence_summary']['g16_rows'] ?? $c35['source_c35_summary']['g16_rows'] ?? 0),
            'g21_weakness_confirmed' => (bool) ($c35['g21_is_summary']['is_weakness_confirmed'] ?? false),
            'g16_weakness_confirmed' => (bool) ($c35['g16_is_summary']['is_weakness_confirmed'] ?? false),
            'source_evidence' => $sourceEvidence,
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
        ?string $notEvaluableReasonCode
    ): array {
        $metrics = $this->metrics($selectedRows);
        $exitDistribution = $this->exitReasonDistribution($selectedRows);
        $gapSummary = $this->gapOpenDamageSummary($selectedRows);

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
            'exit_reason_distribution' => $exitDistribution,
            'gap_open_damage_summary' => $gapSummary,
            'rejected_rows_count' => count($rejectedRows),
            'rejected_rows_reason_distribution' => $this->rejectedRowsDistribution($rejectedRows, $selectionRule),
            'selection_rule' => $selectionRule,
            'selection_input_fields' => $selectionFields,
            'selection_input_safety_check' => [
                'uses_branch_metadata_only' => true,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'production_ready' => false,
            ],
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'production_ready' => false,
            'not_evaluable_reason_code' => $notEvaluableReasonCode,
            'candidate_is_not_production' => true,
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
            $month = (string) ($row['trade_month'] ?? 'UNKNOWN');
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
        foreach ($byMonth as $month => $vals) {
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
            if (($row['candidate_code'] ?? null) === 'C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR') {
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

    private function diagnosticConclusion(array $results, array $formedCandidates): string
    {
        if (count($formedCandidates) === 0) {
            $notEvaluableCount = count(array_filter($results, function (array $row): bool { return ($row['candidate_status'] ?? null) === 'NOT_EVALUABLE'; }));
            return $notEvaluableCount > 0 ? 'C36_INSUFFICIENT_PRE_TRADE_FIELDS_FOR_REDESIGN' : 'C36_NO_CANDIDATE_FORMED';
        }

        $codes = array_map(function (array $row): string { return (string) ($row['candidate_code'] ?? ''); }, $formedCandidates);
        if (in_array('C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR', $codes, true)) {
            return 'C36_COMBINED_CANDIDATE_FORMED';
        }
        foreach ($formedCandidates as $row) {
            if (($row['source_branch'] ?? null) === 'G21') {
                return 'C36_G21_REDESIGN_CANDIDATE_FORMED';
            }
        }
        foreach ($formedCandidates as $row) {
            if (($row['source_branch'] ?? null) === 'G16') {
                return 'C36_G16_REDESIGN_CANDIDATE_FORMED';
            }
        }
        return 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED';
    }

    private function nextStepRecommendation(array $formedCandidates): string
    {
        if (count($formedCandidates) === 0) {
            return 'C37_EVIDENCE_EXPANSION_DIAGNOSTIC';
        }
        $hasG21 = false;
        $hasG16 = false;
        $hasCombined = false;
        foreach ($formedCandidates as $row) {
            $hasG21 = $hasG21 || ($row['source_branch'] ?? null) === 'G21';
            $hasG16 = $hasG16 || ($row['source_branch'] ?? null) === 'G16';
            $hasCombined = $hasCombined || ($row['candidate_group'] ?? null) === 'COMBINED_CONTROLLED_COMPARATOR';
        }
        if ($hasCombined || ($hasG21 && $hasG16)) {
            return 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK';
        }
        if ($hasG21) {
            return 'C37_G21_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK';
        }
        if ($hasG16) {
            return 'C37_G16_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK';
        }
        return 'C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK';
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED',
                'message' => 'C36 completed IS-controlled redesign candidate formation from C35 hypotheses and C28 IS evidence only.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C36_NO_OOS_TUNING_ALLOWED',
                'message' => 'C36 did not run OOS proof, did not use OOS returns for tuning, did not create a production catalog, and did not mutate PLAN/CONFIRM.',
                'fatal' => false,
                'extra' => [
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                    'best_is_candidate_is_not_production' => $artifact['candidate_summary']['best_is_candidate_is_not_production'] ?? true,
                ],
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C36_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C36 diagnostic conclusion derived from IS evidence only.',
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
                'reason_code' => $safe ? 'WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE' : 'WS_BT_C36_CANDIDATE_SELECTION_INPUT_UNSAFE',
                'message' => $safe
                    ? 'Candidate selection inputs do not use return, future path, OOS data, or production promotion.'
                    : 'Candidate violates C36 selection input safety boundary.',
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
                'candidate_code' => $row['candidate_code'],
                'reason_code' => $row['not_evaluable_reason_code'] ?? 'C36_NOT_EVALUABLE',
                'message' => 'Candidate is not evaluable from the available C28 IS diagnostic fields without unsafe return/future-path selection input.',
            ];
        }
        return $out;
    }

    private function badMonthLikeCandidateEffect(array $results): array
    {
        $out = [];
        foreach ($results as $row) {
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'candidate_status' => $row['candidate_status'],
                'bad_month_like_count' => $row['bad_month_like_count'],
                'month_win_rate_min' => $row['month_win_rate_min'],
                'month_avg_ret_net_min' => $row['month_avg_ret_net_min'],
            ];
        }
        return $out;
    }

    private function tickerFailureClusterAfterCandidate(array $results): array
    {
        $out = [];
        foreach ($results as $row) {
            $out[] = [
                'candidate_code' => $row['candidate_code'],
                'candidate_status' => $row['candidate_status'],
                'ticker_concentration' => $row['ticker_concentration'],
                'loss_concentration' => $row['loss_concentration'],
                'branch_concentration' => $row['branch_concentration'],
            ];
        }
        return $out;
    }

    private function redesignDecisionNotes(array $results, array $formedCandidates): array
    {
        $notes = [];
        $notes[] = [
            'note_code' => 'C36_CANDIDATE_NOT_PRODUCTION',
            'message' => 'Any best_is_candidate_code in C36 is a diagnostic comparator only and remains production_ready=false.',
        ];
        $notes[] = [
            'note_code' => 'C36_NO_OOS_PROOF_UNLOCKED',
            'message' => 'C36 does not unlock OOS proof; C37 IS validation / anti-overfit check is required first.',
        ];
        if (count($formedCandidates) > 0) {
            $notes[] = [
                'note_code' => 'C36_CONTROLLED_CANDIDATE_FORMED',
                'message' => 'At least one controlled IS candidate improved a C35 key weakness without using return or future path as selection input.',
            ];
        }
        foreach ($results as $row) {
            if (($row['candidate_status'] ?? null) === 'NOT_EVALUABLE') {
                $notes[] = [
                    'note_code' => 'C36_NOT_EVALUABLE_FIELDS_MISSING',
                    'candidate_code' => $row['candidate_code'],
                    'message' => 'Candidate requires additional pre-trade field evidence before it can be treated as an evaluable redesign candidate.',
                ];
            }
        }
        return $notes;
    }

    private function exitReasonDistribution(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $reason = (string) ($row['profile_exit_reason'] ?? 'UNKNOWN');
            $groups[$reason][] = $row;
        }
        $out = [];
        foreach ($groups as $reason => $reasonRows) {
            $m = $this->metrics($reasonRows);
            $out[] = [
                'profile_exit_reason' => $reason,
                'count' => count($reasonRows),
                'avg_ret_net' => $m['avg_ret_net'],
                'win_rate' => $m['win_rate'],
            ];
        }
        usort($out, function (array $a, array $b): int {
            return (int) $b['count'] <=> (int) $a['count'];
        });
        return $out;
    }

    private function gapOpenDamageSummary(array $rows): array
    {
        $deltas = [];
        foreach ($rows as $row) {
            $delta = $this->num($row['delta_vs_raw_r09'] ?? null);
            if ($delta !== null) {
                $deltas[] = $delta;
            }
        }
        sort($deltas);
        $count = count($deltas);
        if ($count === 0) {
            return [
                'fields_available' => false,
                'basis' => 'delta_vs_raw_r09_unavailable',
                'count' => 0,
                'avg_delta_vs_raw_r09' => null,
                'negative_delta_rate_vs_raw_r09' => null,
            ];
        }
        $negative = count(array_filter($deltas, function (float $value): bool { return $value < 0.0; }));
        return [
            'fields_available' => true,
            'basis' => 'delta_vs_raw_r09_from_existing_IS_diagnostic_rows_for_evaluation_only',
            'count' => $count,
            'avg_delta_vs_raw_r09' => array_sum($deltas) / $count,
            'median_delta_vs_raw_r09' => $this->percentileSorted($deltas, 0.50),
            'p25_delta_vs_raw_r09' => $this->percentileSorted($deltas, 0.25),
            'negative_delta_rate_vs_raw_r09' => $negative / $count,
        ];
    }

    private function rejectedRowsDistribution(array $rows, string $reason): array
    {
        if (count($rows) === 0) {
            return [];
        }
        return [[
            'reason_code' => $reason,
            'count' => count($rows),
        ]];
    }

    private function concentration(array $rows, string $field): ?float
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
        return ((int) reset($counts)) / $count;
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

    private function blocked(array $artifact, string $status, string $reasonCode, string $message, string $outputPath, array $extra = []): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['diagnostic_conclusion'] = 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_BLOCKED';
        $artifact['next_step_recommendation'] = 'C37_BLOCKED_UNTIL_INPUT_VALIDATED';
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
            'expected_c35_hash' => $artifact['expected_c35_hash'] ?? null,
            'actual_c35_hash' => $artifact['actual_c35_hash'] ?? null,
            'c35_hash_match' => $artifact['c35_hash_match'] ?? false,
            'c35_status' => $artifact['c35_status'] ?? null,
            'c35_diagnostic_conclusion' => $artifact['c35_diagnostic_conclusion'] ?? null,
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
                'status' => 'C36_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C36 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c35_hash' => $artifact['expected_c35_hash'] ?? null,
                'actual_c35_hash' => $artifact['actual_c35_hash'] ?? null,
                'c35_hash_match' => $artifact['c35_hash_match'] ?? false,
                'c35_status' => $artifact['c35_status'] ?? null,
                'c35_diagnostic_conclusion' => $artifact['c35_diagnostic_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }

        return [
            'status' => $artifact['status'] ?? 'C36_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C36_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c35_hash' => $artifact['expected_c35_hash'] ?? null,
            'actual_c35_hash' => $artifact['actual_c35_hash'] ?? null,
            'c35_hash_match' => $artifact['c35_hash_match'] ?? false,
            'c35_status' => $artifact['c35_status'] ?? null,
            'c35_diagnostic_conclusion' => $artifact['c35_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c35_summary' => $artifact['source_c35_summary'] ?? [],
            'candidate_summary' => $artifact['candidate_summary'] ?? [],
            'baseline_summary' => $artifact['baseline_summary'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C36 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
