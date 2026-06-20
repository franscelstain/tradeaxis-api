<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC32DataPathAndBadMonthDiagnosticService
{
    public const RUN_CODE = 'C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC';
    public const ARTIFACT_TYPE = 'C32_DATA_PATH_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC';
    public const DEFAULT_C31_ARTIFACT = 'storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json';
    public const DEFAULT_EXPECTED_C31_HASH = '4c6203621ed53ade368328a3aad567cbfc12f3a0';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json';
    public const EXPECTED_C31_STATUS = 'C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED';
    public const EXPECTED_C31_CONCLUSION = 'C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK';
    public const EXPECTED_C31_PROOF_STATUS = 'C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS';

    public function execute(
        string $c31Artifact = self::DEFAULT_C31_ARTIFACT,
        string $expectedC31Hash = self::DEFAULT_EXPECTED_C31_HASH,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c31Artifact = trim($c31Artifact) !== '' ? trim($c31Artifact) : self::DEFAULT_C31_ARTIFACT;
        $expectedC31Hash = trim($expectedC31Hash) !== '' ? trim($expectedC31Hash) : self::DEFAULT_EXPECTED_C31_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c31Artifact, $expectedC31Hash, null, null, null, null, $createdAt);

        if (! is_file($c31Artifact)) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_MISSING_C31_ARTIFACT',
                'WS_BT_C32_C31_ARTIFACT_MISSING',
                'C32 requires the locked C31 controlled gate reclassification artifact, but the file is missing.',
                $outputPath,
                ['input_c31_artifact' => $c31Artifact]
            );
        }

        $c31 = json_decode((string) file_get_contents($c31Artifact), true);
        if (! is_array($c31)) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_MISSING_C31_ARTIFACT',
                'WS_BT_C32_C31_ARTIFACT_UNREADABLE',
                'C31 artifact is not readable JSON.',
                $outputPath,
                ['input_c31_artifact' => $c31Artifact]
            );
        }

        $actualC31Hash = $this->stableHash($c31);
        $artifact = $this->baseArtifact(
            $c31Artifact,
            $expectedC31Hash,
            $actualC31Hash,
            $c31['status'] ?? null,
            $c31['reclassification_conclusion'] ?? null,
            $c31['controlled_proof_status'] ?? null,
            $createdAt
        );

        if ($actualC31Hash !== $expectedC31Hash) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_C31_HASH_MISMATCH',
                'WS_BT_C32_C31_ARTIFACT_HASH_MISMATCH',
                'C31 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c31_artifact_hash_field' => $c31['artifact_hash'] ?? null]
            );
        }

        if (($c31['status'] ?? null) !== self::EXPECTED_C31_STATUS) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_UNEXPECTED_C31_STATUS',
                'WS_BT_C32_UNEXPECTED_C31_STATUS',
                'C32 requires a completed C31 controlled gate reclassification artifact.',
                $outputPath,
                ['expected_c31_status' => self::EXPECTED_C31_STATUS]
            );
        }

        if (($c31['production_ready'] ?? false) !== false && (int) ($c31['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_UNEXPECTED_C31_STATUS',
                'WS_BT_C32_C31_PRODUCTION_READY_UNEXPECTED',
                'C32 requires C31 production_ready=false before diagnostic split.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (($c31['reclassification_conclusion'] ?? null) !== self::EXPECTED_C31_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_UNEXPECTED_C31_CONCLUSION',
                'WS_BT_C32_UNEXPECTED_C31_CONCLUSION',
                'C32 requires C31 to confirm missing-path-not-lookahead-leak before splitting the next diagnostic work.',
                $outputPath,
                ['expected_c31_conclusion' => self::EXPECTED_C31_CONCLUSION]
            );
        }

        if (($c31['controlled_proof_status'] ?? null) !== self::EXPECTED_C31_PROOF_STATUS) {
            return $this->blocked(
                $artifact,
                'C32_BLOCKED_UNEXPECTED_C31_PROOF_STATUS',
                'WS_BT_C32_UNEXPECTED_C31_PROOF_STATUS',
                'C32 requires the C31 data-completeness-and-robustness failed proof status.',
                $outputPath,
                ['expected_c31_proof_status' => self::EXPECTED_C31_PROOF_STATUS]
            );
        }

        $missingRows = $this->enrichedMissingRows($c31);
        $dataPathScope = $this->dataPathRemediationScope($missingRows);
        $badMonthSummary = $this->badMonthRobustnessSummary($c31);
        $branchSummary = $this->branchRobustnessSummary($c31);
        $splitDecision = $this->splitDecision($c31, $dataPathScope, $badMonthSummary, $branchSummary);
        $conclusion = $this->diagnosticConclusion($splitDecision);

        $artifact = array_replace_recursive($artifact, [
            'source_c31_gate_summary' => is_array($c31['separated_gate_summary'] ?? null) ? $c31['separated_gate_summary'] : [],
            'source_c31_classification_summary' => is_array($c31['source_c30_classification_summary'] ?? null) ? $c31['source_c30_classification_summary'] : [],
            'source_c31_clean_metrics' => is_array($c31['source_c30_clean_metrics'] ?? null) ? $c31['source_c30_clean_metrics'] : [],
            'data_path_remediation_status' => $dataPathScope['missing_path_count'] > 0
                ? 'C32_DATA_PATH_REMEDIATION_REQUIRED'
                : 'C32_DATA_PATH_REMEDIATION_NOT_REQUIRED',
            'data_path_remediation_scope' => $dataPathScope,
            'missing_path_replay_rows' => $missingRows,
            'bad_month_robustness_status' => count($badMonthSummary) > 0
                ? 'C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED'
                : 'C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NOT_REQUIRED',
            'bad_month_robustness_summary' => $badMonthSummary,
            'source_branch_robustness_summary' => $branchSummary,
            'split_decision' => $splitDecision,
            'diagnostic_conclusion' => $conclusion,
            'next_step' => $this->nextStep($splitDecision),
            'status' => 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED',
            'diagnostics' => $this->completedDiagnostics($splitDecision, $conclusion),
        ]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C32_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C32 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
            ];
        }

        return [
            'status' => 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED',
            'reason_code' => 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c31_hash' => $expectedC31Hash,
            'actual_c31_hash' => $actualC31Hash,
            'c31_hash_match' => true,
            'c31_status' => $c31['status'] ?? null,
            'c31_reclassification_conclusion' => $c31['reclassification_conclusion'] ?? null,
            'c31_controlled_proof_status' => $c31['controlled_proof_status'] ?? null,
            'data_path_remediation_status' => $artifact['data_path_remediation_status'],
            'bad_month_robustness_status' => $artifact['bad_month_robustness_status'],
            'split_decision' => $splitDecision,
            'diagnostic_conclusion' => $conclusion,
            'next_step' => $artifact['next_step'],
        ];
    }

    private function enrichedMissingRows(array $c31): array
    {
        $rows = array_values(array_filter($c31['missing_path_rows'] ?? [], 'is_array'));
        $c29Rows = $this->c29RowsByKey($c31);

        return array_values(array_map(function (array $row) use ($c29Rows): array {
            $full = $c29Rows[$this->rowKey($row)] ?? [];
            return [
                'trade_month' => $row['trade_month'] ?? ($full['trade_month'] ?? null),
                'trade_date' => $row['trade_date'] ?? ($full['trade_date'] ?? null),
                'ticker' => $row['ticker'] ?? ($full['ticker'] ?? null),
                'ticker_id' => $full['ticker_id'] ?? null,
                'param_id' => $row['param_id'] ?? ($full['param_id'] ?? null),
                'row_code' => $row['row_code'] ?? ($full['row_code'] ?? null),
                'selected_source_code' => $row['selected_source_code'] ?? ($full['selected_source_code'] ?? null),
                'selected_source_reason' => $full['selected_source_reason'] ?? null,
                'entry_date' => $full['entry_date'] ?? null,
                'required_path_scope' => 'D1_TO_D5_RAW_OHLC_PATH',
                'missing_path_reason_code' => $row['missing_path_reason_code'] ?? ($full['missing_path_reason_code'] ?? null),
                'raw_ohlc_validated_flag' => $row['raw_ohlc_validated_flag'] ?? ($full['raw_ohlc_validated_flag'] ?? null),
                'missing_path_data_flag' => $row['missing_path_data_flag'] ?? ($full['missing_path_data_flag'] ?? null),
                'future_path_price_used_for_selection' => $row['future_path_price_used_for_selection'] ?? ($full['future_path_price_used_for_selection'] ?? false),
                'profile_ret_net_used_for_selection' => $row['profile_ret_net_used_for_selection'] ?? ($full['profile_ret_net_used_for_selection'] ?? false),
                'derived_mfe_mae_used_for_execution' => $row['derived_mfe_mae_used_for_execution'] ?? ($full['derived_mfe_mae_used_for_execution'] ?? false),
                'remediation_status' => 'REQUIRES_DATA_PATH_REPLAY',
            ];
        }, $rows));
    }

    private function c29RowsByKey(array $c31): array
    {
        $path = (string) ($c31['input_c29_artifact'] ?? '');
        if ($path === '' || ! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded) || $this->stableHash($decoded) !== (string) ($c31['actual_c29_hash'] ?? '')) {
            return [];
        }

        $out = [];
        foreach ($decoded['oos_pick_rows'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[$this->rowKey($row)] = $row;
        }
        return $out;
    }

    private function dataPathRemediationScope(array $missingRows): array
    {
        $tradeDates = [];
        $entryDates = [];
        $tickers = [];
        $paramIds = [];
        $sourceCodes = [];
        $reasonCounts = [];

        foreach ($missingRows as $row) {
            $this->collect($tradeDates, $row['trade_date'] ?? null);
            $this->collect($entryDates, $row['entry_date'] ?? null);
            $this->collect($tickers, $row['ticker'] ?? null);
            $this->collect($paramIds, $row['param_id'] ?? null);
            $this->collect($sourceCodes, $row['selected_source_code'] ?? null);
            $reason = (string) ($row['missing_path_reason_code'] ?? 'UNKNOWN');
            $reasonCounts[$reason] = ($reasonCounts[$reason] ?? 0) + 1;
        }

        ksort($reasonCounts, SORT_STRING);

        return [
            'missing_path_count' => count($missingRows),
            'affected_trade_dates' => array_values($tradeDates),
            'affected_entry_dates' => array_values($entryDates),
            'affected_tickers' => array_values($tickers),
            'affected_param_ids' => array_values($paramIds),
            'affected_source_codes' => array_values($sourceCodes),
            'missing_path_reason_counts' => $reasonCounts,
            'required_remediation_action' => count($missingRows) > 0
                ? 'C32_REPLAY_RAW_OHLC_D1_TO_D5_FOR_MISSING_PATH_ROWS_BEFORE_GATE_RETEST'
                : 'C32_NO_DATA_PATH_REMEDIATION_REQUIRED',
            'can_claim_data_completeness_pass' => false,
            'can_claim_oos_pass' => false,
        ];
    }

    private function badMonthRobustnessSummary(array $c31): array
    {
        $rows = array_values(array_filter($c31['bad_month_summary'] ?? [], 'is_array'));
        usort($rows, function (array $a, array $b): int {
            return strcmp((string) ($a['trade_month'] ?? ''), (string) ($b['trade_month'] ?? ''));
        });

        return array_values(array_map(function (array $row): array {
            $missing = (int) ($row['missing_path_rows'] ?? 0);
            $clean = (int) ($row['clean_rows'] ?? 0);
            $winRate = $this->num($row['win_rate'] ?? null);
            $dataAffected = $missing > 0;
            $cleanRobustnessFail = $clean > 0 && $winRate !== null && $winRate <= 0.0;

            return [
                'trade_month' => $row['trade_month'] ?? null,
                'total_rows' => $row['total_rows'] ?? null,
                'clean_rows' => $clean,
                'missing_path_rows' => $missing,
                'avg_ret_net' => $row['avg_ret_net'] ?? null,
                'median_ret_net' => $row['median_ret_net'] ?? null,
                'p25_ret_net' => $row['p25_ret_net'] ?? null,
                'win_rate' => $winRate,
                'dominant_branch' => $row['dominant_branch'] ?? null,
                'dominant_ticker' => $row['dominant_ticker'] ?? null,
                'data_path_affected' => $dataAffected,
                'clean_robustness_failure' => $cleanRobustnessFail,
                'failure_class' => $this->badMonthFailureClass($dataAffected, $cleanRobustnessFail),
            ];
        }, $rows));
    }

    private function branchRobustnessSummary(array $c31): array
    {
        $rows = array_values(array_filter($c31['source_branch_summary'] ?? [], 'is_array'));
        usort($rows, function (array $a, array $b): int {
            return strcmp((string) ($a['selected_source_code'] ?? ''), (string) ($b['selected_source_code'] ?? ''));
        });

        return array_values(array_map(function (array $row): array {
            $badMonthContribution = is_array($row['bad_month_contribution'] ?? null) ? $row['bad_month_contribution'] : [];
            $badMonthCount = array_sum(array_map('intval', $badMonthContribution));
            $missing = (int) ($row['missing_count'] ?? 0);
            $avg = $this->num($row['avg_ret_net'] ?? null);
            $winRate = $this->num($row['win_rate'] ?? null);
            $cleanBadMonthContribution = max(0, $badMonthCount - $missing);
            $robustnessFlag = $cleanBadMonthContribution > 0 || ($winRate !== null && $winRate < 0.45) || ($avg !== null && $avg < 0.0);

            return [
                'selected_source_code' => $row['selected_source_code'] ?? null,
                'count' => $row['count'] ?? null,
                'clean_count' => $row['clean_count'] ?? null,
                'missing_count' => $missing,
                'non_evaluable_count' => $row['non_evaluable_count'] ?? null,
                'avg_ret_net' => $row['avg_ret_net'] ?? null,
                'median_ret_net' => $row['median_ret_net'] ?? null,
                'p25_ret_net' => $row['p25_ret_net'] ?? null,
                'win_rate' => $winRate,
                'bad_month_contribution' => $badMonthContribution,
                'bad_month_contribution_count' => $badMonthCount,
                'clean_bad_month_contribution_count' => $cleanBadMonthContribution,
                'data_path_affected' => $missing > 0,
                'robustness_diagnostic_flag' => $robustnessFlag,
                'failure_class' => $this->branchFailureClass($missing > 0, $robustnessFlag),
            ];
        }, $rows));
    }

    private function splitDecision(array $c31, array $dataPathScope, array $badMonthSummary, array $branchSummary): array
    {
        $gates = is_array($c31['separated_gate_summary'] ?? null) ? $c31['separated_gate_summary'] : [];
        $actualLookaheadPass = ($gates['actual_lookahead_gate']['status'] ?? null) === 'PASS';
        $selectionLeakPass = ($gates['selection_leak_gate']['status'] ?? null) === 'PASS';
        $dataPathRequired = ($dataPathScope['missing_path_count'] ?? 0) > 0;
        $robustnessRequired = count(array_filter($badMonthSummary, function (array $row): bool {
            return ($row['clean_robustness_failure'] ?? false) === true;
        })) > 0 || count(array_filter($branchSummary, function (array $row): bool {
            return ($row['robustness_diagnostic_flag'] ?? false) === true;
        })) > 0;

        return [
            'actual_lookahead_fix_required' => ! $actualLookaheadPass,
            'selection_leak_fix_required' => ! $selectionLeakPass,
            'data_path_remediation_required' => $dataPathRequired,
            'bad_month_robustness_diagnostic_required' => $robustnessRequired,
            'oos_tuning_allowed' => false,
            'profile_reselection_allowed' => false,
            'production_promotion_allowed' => false,
            'production_ready' => false,
        ];
    }

    private function diagnosticConclusion(array $splitDecision): string
    {
        if (($splitDecision['actual_lookahead_fix_required'] ?? false) === true) {
            return 'C32_SPLIT_BLOCKED_ACTUAL_LOOKAHEAD_FIX_REQUIRED';
        }
        if (($splitDecision['selection_leak_fix_required'] ?? false) === true) {
            return 'C32_SPLIT_BLOCKED_SELECTION_LEAK_FIX_REQUIRED';
        }
        if (($splitDecision['data_path_remediation_required'] ?? false) === true
            && ($splitDecision['bad_month_robustness_diagnostic_required'] ?? false) === true) {
            return 'C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED';
        }
        if (($splitDecision['data_path_remediation_required'] ?? false) === true) {
            return 'C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_REQUIRED';
        }
        if (($splitDecision['bad_month_robustness_diagnostic_required'] ?? false) === true) {
            return 'C32_SPLIT_CONFIRMED_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED';
        }
        return 'C32_SPLIT_NO_FOLLOWUP_REQUIRED';
    }

    private function nextStep(array $splitDecision): string
    {
        if (($splitDecision['actual_lookahead_fix_required'] ?? false) === true) {
            return 'FIX_ACTUAL_LOOKAHEAD_LEAK_BEFORE_ANY_STRATEGY_DECISION';
        }
        if (($splitDecision['selection_leak_fix_required'] ?? false) === true) {
            return 'FIX_SELECTION_LEAK_BEFORE_ANY_STRATEGY_DECISION';
        }
        if (($splitDecision['data_path_remediation_required'] ?? false) === true
            && ($splitDecision['bad_month_robustness_diagnostic_required'] ?? false) === true) {
            return 'C33_DATA_PATH_REPLAY_PROOF_THEN_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING';
        }
        if (($splitDecision['data_path_remediation_required'] ?? false) === true) {
            return 'C33_DATA_PATH_REPLAY_PROOF_NO_TUNING';
        }
        if (($splitDecision['bad_month_robustness_diagnostic_required'] ?? false) === true) {
            return 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING';
        }
        return 'NO_C32_FOLLOWUP_REQUIRED';
    }

    private function completedDiagnostics(array $splitDecision, string $conclusion): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C32_DIAGNOSTIC_SPLIT_COMPLETED',
                'message' => 'C32 split C31 follow-up work into data-path remediation scope and bad-month robustness diagnostic scope without tuning or production promotion.',
                'fatal' => false,
            ],
            [
                'reason_code' => $conclusion,
                'message' => 'C32 diagnostic conclusion.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C32_NO_OOS_TUNING_ALLOWED',
                'message' => 'C32 does not allow OOS tuning, profile reselection, best-of-OOS, production catalog creation, or production readiness.',
                'fatal' => false,
                'extra' => $splitDecision,
            ],
        ];
    }

    private function badMonthFailureClass(bool $dataAffected, bool $cleanRobustnessFail): string
    {
        if ($dataAffected && $cleanRobustnessFail) {
            return 'MIXED_DATA_PATH_AND_CLEAN_ROBUSTNESS_FAILURE';
        }
        if ($dataAffected) {
            return 'DATA_PATH_AFFECTED_BAD_MONTH';
        }
        if ($cleanRobustnessFail) {
            return 'CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE';
        }
        return 'BAD_MONTH_REVIEW_ONLY';
    }

    private function branchFailureClass(bool $dataAffected, bool $robustnessFlag): string
    {
        if ($dataAffected && $robustnessFlag) {
            return 'MIXED_DATA_PATH_AND_BRANCH_ROBUSTNESS_REVIEW';
        }
        if ($dataAffected) {
            return 'DATA_PATH_AFFECTED_BRANCH';
        }
        if ($robustnessFlag) {
            return 'CLEAN_BRANCH_ROBUSTNESS_REVIEW';
        }
        return 'BRANCH_REVIEW_ONLY';
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
        $artifact['diagnostic_conclusion'] = 'C32_DIAGNOSTIC_BLOCKED';
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
            'expected_c31_hash' => $artifact['expected_c31_hash'] ?? null,
            'actual_c31_hash' => $artifact['actual_c31_hash'] ?? null,
            'c31_hash_match' => $artifact['c31_hash_match'] ?? false,
            'c31_status' => $artifact['c31_status'] ?? null,
            'c31_reclassification_conclusion' => $artifact['c31_reclassification_conclusion'] ?? null,
            'c31_controlled_proof_status' => $artifact['c31_controlled_proof_status'] ?? null,
            'production_ready' => 0,
        ];
    }

    private function baseArtifact(
        string $inputC31Path,
        string $expectedC31Hash,
        ?string $actualC31Hash,
        $c31Status,
        $c31Conclusion,
        $c31ProofStatus,
        string $createdAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C32_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c31_artifact' => $inputC31Path,
            'expected_c31_hash' => $expectedC31Hash,
            'actual_c31_hash' => $actualC31Hash,
            'c31_hash_match' => $actualC31Hash !== null && $actualC31Hash === $expectedC31Hash,
            'c31_status' => $c31Status,
            'c31_reclassification_conclusion' => $c31Conclusion,
            'c31_controlled_proof_status' => $c31ProofStatus,
            'source_c31_gate_summary' => [],
            'source_c31_classification_summary' => [],
            'source_c31_clean_metrics' => [],
            'data_path_remediation_status' => 'C32_DATA_PATH_REMEDIATION_PENDING',
            'data_path_remediation_scope' => [],
            'missing_path_replay_rows' => [],
            'bad_month_robustness_status' => 'C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_PENDING',
            'bad_month_robustness_summary' => [],
            'source_branch_robustness_summary' => [],
            'split_decision' => [
                'actual_lookahead_fix_required' => null,
                'selection_leak_fix_required' => null,
                'data_path_remediation_required' => null,
                'bad_month_robustness_diagnostic_required' => null,
                'oos_tuning_allowed' => false,
                'profile_reselection_allowed' => false,
                'production_promotion_allowed' => false,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => 'C32_DIAGNOSTIC_PENDING',
            'next_step' => 'C32_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                'DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_ONLY' => true,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C31_MUTATION' => true,
                'production_ready' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_profile_selection' => false,
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

    private function rowKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            strtoupper((string) ($row['ticker'] ?? '')),
            (string) ($row['param_id'] ?? ''),
            strtoupper((string) ($row['selected_source_code'] ?? '')),
        ]);
    }

    private function collect(array &$values, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $values[(string) $value] = $value;
        ksort($values, SORT_STRING);
    }

    private function num($value): ?float
    {
        if ($value === '' || $value === null || ! is_numeric($value)) {
            return null;
        }
        return (float) $value;
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C32 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
