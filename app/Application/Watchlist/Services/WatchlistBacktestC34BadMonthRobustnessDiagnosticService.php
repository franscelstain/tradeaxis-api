<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC34BadMonthRobustnessDiagnosticService
{
    public const RUN_CODE = 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC';
    public const ARTIFACT_TYPE = 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC';
    public const DEFAULT_C33_ARTIFACT = 'storage/app/watchlist/backtest/c33-data-path-replay-proof.json';
    public const DEFAULT_EXPECTED_C33_HASH = '84bb77871515643b203de644fd34b4c748d1b2af';
    public const DEFAULT_EXPECTED_C32_HASH = '4bd92dfcf70dd0b02398d3ecf62d08c0356292ab';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json';
    public const EXPECTED_C33_STATUS = 'C33_DATA_PATH_REPLAY_PROOF_COMPLETED';
    public const EXPECTED_C33_CONCLUSION = 'C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE';
    public const EXPECTED_C33_REPLAY_STATUS = 'C33_DATA_PATH_REPLAY_PASS';
    public const EXPECTED_C32_STATUS = 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED';
    public const EXPECTED_C32_CONCLUSION = 'C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED';
    public const EXPECTED_C32_BAD_MONTH_STATUS = 'C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED';

    public function execute(
        string $c33Artifact = self::DEFAULT_C33_ARTIFACT,
        string $expectedC33Hash = self::DEFAULT_EXPECTED_C33_HASH,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c33Artifact = trim($c33Artifact) !== '' ? trim($c33Artifact) : self::DEFAULT_C33_ARTIFACT;
        $expectedC33Hash = trim($expectedC33Hash) !== '' ? trim($expectedC33Hash) : self::DEFAULT_EXPECTED_C33_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c33Artifact, $expectedC33Hash, null, null, null, null, null, $createdAt);

        if (! is_file($c33Artifact)) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_MISSING_C33_ARTIFACT',
                'WS_BT_C34_C33_ARTIFACT_MISSING',
                'C34 requires the locked C33 data-path replay proof artifact, but the file is missing.',
                $outputPath,
                ['input_c33_artifact' => $c33Artifact]
            );
        }

        $c33 = json_decode((string) file_get_contents($c33Artifact), true);
        if (! is_array($c33)) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_MISSING_C33_ARTIFACT',
                'WS_BT_C34_C33_ARTIFACT_UNREADABLE',
                'C33 artifact is not readable JSON.',
                $outputPath,
                ['input_c33_artifact' => $c33Artifact]
            );
        }

        $actualC33Hash = $this->stableHash($c33);
        $artifact = $this->baseArtifact(
            $c33Artifact,
            $expectedC33Hash,
            $actualC33Hash,
            $c33['status'] ?? null,
            $c33['diagnostic_conclusion'] ?? null,
            $c33['data_path_replay_status'] ?? null,
            $this->gateStatus($c33['data_completeness_gate_after_replay'] ?? null),
            $createdAt
        );

        if ($actualC33Hash !== $expectedC33Hash) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_C33_HASH_MISMATCH',
                'WS_BT_C34_C33_ARTIFACT_HASH_MISMATCH',
                'C33 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c33_artifact_hash_field' => $c33['artifact_hash'] ?? null]
            );
        }

        if (($c33['status'] ?? null) !== self::EXPECTED_C33_STATUS) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_UNEXPECTED_C33_STATUS',
                'WS_BT_C34_UNEXPECTED_C33_STATUS',
                'C34 requires a completed C33 data-path replay proof artifact.',
                $outputPath,
                ['expected_c33_status' => self::EXPECTED_C33_STATUS]
            );
        }

        if (($c33['production_ready'] ?? false) !== false && (int) ($c33['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_UNEXPECTED_C33_STATUS',
                'WS_BT_C34_C33_PRODUCTION_READY_UNEXPECTED',
                'C34 requires C33 production_ready=false before bad-month diagnostic.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (($c33['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C33_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_UNEXPECTED_C33_CONCLUSION',
                'WS_BT_C34_UNEXPECTED_C33_CONCLUSION',
                'C34 requires C33 to confirm D1-D5 raw OHLC availability before bad-month robustness diagnostic.',
                $outputPath,
                ['expected_c33_conclusion' => self::EXPECTED_C33_CONCLUSION]
            );
        }

        if (($c33['data_path_replay_status'] ?? null) !== self::EXPECTED_C33_REPLAY_STATUS) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_C33_DATA_PATH_REPLAY_NOT_PASS',
                'WS_BT_C34_C33_DATA_PATH_REPLAY_NOT_PASS',
                'C34 requires C33 data-path replay PASS before clean bad-month robustness diagnostic.',
                $outputPath,
                ['expected_c33_replay_status' => self::EXPECTED_C33_REPLAY_STATUS]
            );
        }

        if ($this->gateStatus($c33['data_completeness_gate_after_replay'] ?? null) !== 'PASS') {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_C33_DATA_COMPLETENESS_GATE_NOT_PASS',
                'WS_BT_C34_C33_DATA_COMPLETENESS_GATE_NOT_PASS',
                'C34 requires the C33 data-completeness gate after replay to be PASS.',
                $outputPath,
                ['expected_c33_data_completeness_gate_after_replay' => 'PASS']
            );
        }

        $c32Path = $this->c32Path($c33, $options);
        $expectedC32Hash = $this->expectedC32Hash($c33, $options);
        $artifact = array_replace($artifact, [
            'input_c32_artifact' => $c32Path,
            'expected_c32_hash' => $expectedC32Hash,
        ]);

        if ($c32Path === '' || ! is_file($c32Path)) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_MISSING_C32_ARTIFACT',
                'WS_BT_C34_C32_ARTIFACT_MISSING',
                'C34 requires the locked C32 bad-month diagnostic source artifact, but the file is missing.',
                $outputPath,
                ['input_c32_artifact' => $c32Path]
            );
        }

        $c32 = json_decode((string) file_get_contents($c32Path), true);
        if (! is_array($c32)) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_MISSING_C32_ARTIFACT',
                'WS_BT_C34_C32_ARTIFACT_UNREADABLE',
                'C32 artifact is not readable JSON.',
                $outputPath,
                ['input_c32_artifact' => $c32Path]
            );
        }

        $actualC32Hash = $this->stableHash($c32);
        $artifact = array_replace($artifact, [
            'actual_c32_hash' => $actualC32Hash,
            'c32_hash_match' => $actualC32Hash === $expectedC32Hash,
            'c32_status' => $c32['status'] ?? null,
            'c32_diagnostic_conclusion' => $c32['diagnostic_conclusion'] ?? null,
            'c32_bad_month_robustness_status' => $c32['bad_month_robustness_status'] ?? null,
        ]);

        if ($actualC32Hash !== $expectedC32Hash) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_C32_HASH_MISMATCH',
                'WS_BT_C34_C32_ARTIFACT_HASH_MISMATCH',
                'C32 artifact stable hash does not match the C33-linked expected hash.',
                $outputPath,
                ['c32_artifact_hash_field' => $c32['artifact_hash'] ?? null]
            );
        }

        if (($c32['status'] ?? null) !== self::EXPECTED_C32_STATUS) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_UNEXPECTED_C32_STATUS',
                'WS_BT_C34_UNEXPECTED_C32_STATUS',
                'C34 requires a completed C32 diagnostic artifact.',
                $outputPath,
                ['expected_c32_status' => self::EXPECTED_C32_STATUS]
            );
        }

        if (($c32['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C32_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_UNEXPECTED_C32_CONCLUSION',
                'WS_BT_C34_UNEXPECTED_C32_CONCLUSION',
                'C34 requires C32 to have identified bad-month robustness diagnostic scope.',
                $outputPath,
                ['expected_c32_conclusion' => self::EXPECTED_C32_CONCLUSION]
            );
        }

        if (($c32['bad_month_robustness_status'] ?? null) !== self::EXPECTED_C32_BAD_MONTH_STATUS) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_UNEXPECTED_C32_BAD_MONTH_STATUS',
                'WS_BT_C34_UNEXPECTED_C32_BAD_MONTH_STATUS',
                'C34 requires C32 bad-month robustness status to be diagnostic-required.',
                $outputPath,
                ['expected_c32_bad_month_status' => self::EXPECTED_C32_BAD_MONTH_STATUS]
            );
        }

        $badMonthRows = $this->badMonthDiagnosticRows($c32, $c33);
        $branchRows = $this->branchDiagnosticRows($c32, $c33);
        if (count($badMonthRows) === 0 && count($branchRows) === 0) {
            return $this->blocked(
                $artifact,
                'C34_BLOCKED_NO_BAD_MONTH_SCOPE',
                'WS_BT_C34_NO_BAD_MONTH_SCOPE',
                'C34 requires C32 bad-month or branch robustness scope.',
                $outputPath
            );
        }

        $decision = $this->robustnessDecision($badMonthRows, $branchRows, $c33);
        $conclusion = $decision['diagnostic_conclusion'];

        $artifact = array_replace_recursive($artifact, [
            'source_c33_replay_summary' => is_array($c33['replay_summary'] ?? null) ? $c33['replay_summary'] : [],
            'source_c33_data_completeness_gate_after_replay' => is_array($c33['data_completeness_gate_after_replay'] ?? null)
                ? $c33['data_completeness_gate_after_replay']
                : [],
            'source_c32_clean_metrics' => is_array($c32['source_c31_clean_metrics'] ?? null) ? $c32['source_c31_clean_metrics'] : [],
            'source_c32_bad_month_robustness_summary' => is_array($c32['bad_month_robustness_summary'] ?? null)
                ? $c32['bad_month_robustness_summary']
                : [],
            'source_c32_branch_robustness_summary' => is_array($c32['source_branch_robustness_summary'] ?? null)
                ? $c32['source_branch_robustness_summary']
                : [],
            'bad_month_diagnostic_rows' => $badMonthRows,
            'branch_robustness_rows' => $branchRows,
            'robustness_decision' => $decision,
            'bad_month_robustness_status' => $decision['bad_month_robustness_status'],
            'diagnostic_conclusion' => $conclusion,
            'next_step' => $decision['next_step'],
            'status' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED',
            'diagnostics' => $this->completedDiagnostics($decision, $conclusion),
        ]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C34_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C34 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
            ];
        }

        return [
            'status' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED',
            'reason_code' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c33_hash' => $expectedC33Hash,
            'actual_c33_hash' => $actualC33Hash,
            'c33_hash_match' => true,
            'c33_status' => $c33['status'] ?? null,
            'c33_data_path_replay_status' => $c33['data_path_replay_status'] ?? null,
            'expected_c32_hash' => $expectedC32Hash,
            'actual_c32_hash' => $actualC32Hash,
            'c32_hash_match' => true,
            'c32_status' => $c32['status'] ?? null,
            'bad_month_robustness_status' => $decision['bad_month_robustness_status'],
            'bad_month_failure_count' => $decision['bad_month_failure_count'],
            'branch_robustness_flag_count' => $decision['branch_robustness_flag_count'],
            'strategy_robustness_redesign_required' => $decision['strategy_robustness_redesign_required'],
            'diagnostic_conclusion' => $conclusion,
            'next_step' => $decision['next_step'],
        ];
    }

    private function badMonthDiagnosticRows(array $c32, array $c33): array
    {
        $rows = array_values(array_filter($c32['bad_month_robustness_summary'] ?? [], 'is_array'));
        $clearedMonths = $this->clearedReplayMonths($c33);

        usort($rows, function (array $a, array $b): int {
            return strcmp((string) ($a['trade_month'] ?? ''), (string) ($b['trade_month'] ?? ''));
        });

        return array_values(array_map(function (array $row) use ($clearedMonths): array {
            $month = (string) ($row['trade_month'] ?? '');
            $cleanRows = (int) ($row['clean_rows'] ?? 0);
            $missingRows = (int) ($row['missing_path_rows'] ?? 0);
            $winRate = $this->num($row['win_rate'] ?? null);
            $avg = $this->num($row['avg_ret_net'] ?? null);
            $cleanFailure = (bool) ($row['clean_robustness_failure'] ?? false)
                || ($cleanRows > 0 && $winRate !== null && $winRate <= 0.0);
            $dataPathAffected = (bool) ($row['data_path_affected'] ?? false);
            $dataPathCleared = $dataPathAffected && (($clearedMonths[$month] ?? false) === true);

            return [
                'trade_month' => $month,
                'total_rows' => $row['total_rows'] ?? null,
                'clean_rows' => $cleanRows,
                'missing_path_rows_before_c33' => $missingRows,
                'data_path_affected_before_c33' => $dataPathAffected,
                'data_path_cleared_by_c33' => $dataPathAffected ? $dataPathCleared : null,
                'avg_ret_net' => $row['avg_ret_net'] ?? null,
                'median_ret_net' => $row['median_ret_net'] ?? null,
                'p25_ret_net' => $row['p25_ret_net'] ?? null,
                'win_rate' => $winRate,
                'dominant_branch' => $row['dominant_branch'] ?? null,
                'dominant_ticker' => $row['dominant_ticker'] ?? null,
                'clean_robustness_failure' => $cleanFailure,
                'bad_month_failure_class' => $this->badMonthFailureClass($cleanFailure, $dataPathAffected, $dataPathCleared),
                'severity' => $this->badMonthSeverity($cleanFailure, $winRate, $avg),
                'diagnostic_action' => $cleanFailure
                    ? 'C34_IS_ONLY_BAD_MONTH_ROBUSTNESS_REDESIGN_DIAGNOSTIC_REQUIRED'
                    : 'C34_BAD_MONTH_REVIEW_ONLY',
            ];
        }, $rows));
    }

    private function branchDiagnosticRows(array $c32, array $c33): array
    {
        $rows = array_values(array_filter($c32['source_branch_robustness_summary'] ?? [], 'is_array'));
        $clearedSources = $this->clearedReplaySources($c33);

        usort($rows, function (array $a, array $b): int {
            return strcmp((string) ($a['selected_source_code'] ?? ''), (string) ($b['selected_source_code'] ?? ''));
        });

        return array_values(array_map(function (array $row) use ($clearedSources): array {
            $source = (string) ($row['selected_source_code'] ?? '');
            $avg = $this->num($row['avg_ret_net'] ?? null);
            $winRate = $this->num($row['win_rate'] ?? null);
            $cleanContribution = (int) ($row['clean_bad_month_contribution_count'] ?? 0);
            $badMonthContribution = (int) ($row['bad_month_contribution_count'] ?? 0);
            $dataPathAffected = (bool) ($row['data_path_affected'] ?? false);
            $dataPathCleared = $dataPathAffected && (($clearedSources[$source] ?? false) === true);
            $aggregateWeakness = ($avg !== null && $avg < 0.0) || ($winRate !== null && $winRate < 0.45);
            $flag = (bool) ($row['robustness_diagnostic_flag'] ?? false)
                || $cleanContribution > 0
                || $aggregateWeakness;

            return [
                'selected_source_code' => $source,
                'count' => $row['count'] ?? null,
                'clean_count' => $row['clean_count'] ?? null,
                'missing_count_before_c33' => $row['missing_count'] ?? null,
                'non_evaluable_count_before_c33' => $row['non_evaluable_count'] ?? null,
                'data_path_affected_before_c33' => $dataPathAffected,
                'data_path_cleared_by_c33' => $dataPathAffected ? $dataPathCleared : null,
                'avg_ret_net' => $row['avg_ret_net'] ?? null,
                'median_ret_net' => $row['median_ret_net'] ?? null,
                'p25_ret_net' => $row['p25_ret_net'] ?? null,
                'win_rate' => $winRate,
                'bad_month_contribution' => is_array($row['bad_month_contribution'] ?? null) ? $row['bad_month_contribution'] : [],
                'bad_month_contribution_count' => $badMonthContribution,
                'clean_bad_month_contribution_count' => $cleanContribution,
                'aggregate_weakness_flag' => $aggregateWeakness,
                'robustness_diagnostic_flag' => $flag,
                'branch_failure_class' => $this->branchFailureClass($flag, $aggregateWeakness, $dataPathAffected, $dataPathCleared),
                'diagnostic_action' => $flag
                    ? 'C34_IS_ONLY_BRANCH_ROBUSTNESS_DIAGNOSTIC_REQUIRED'
                    : 'C34_BRANCH_REVIEW_ONLY',
            ];
        }, $rows));
    }

    private function robustnessDecision(array $badMonthRows, array $branchRows, array $c33): array
    {
        $badMonthFailures = array_values(array_filter($badMonthRows, function (array $row): bool {
            return ($row['clean_robustness_failure'] ?? false) === true;
        }));
        $dataClearedBadMonths = array_values(array_filter($badMonthRows, function (array $row): bool {
            return ($row['data_path_cleared_by_c33'] ?? false) === true;
        }));
        $branchFlags = array_values(array_filter($branchRows, function (array $row): bool {
            return ($row['robustness_diagnostic_flag'] ?? false) === true;
        }));
        $aggregateBranchWeakness = array_values(array_filter($branchRows, function (array $row): bool {
            return ($row['aggregate_weakness_flag'] ?? false) === true;
        }));

        $failureConfirmed = count($badMonthFailures) > 0 || count($branchFlags) > 0;

        return [
            'data_path_blocker_cleared_by_c33' => ($c33['data_path_replay_status'] ?? null) === self::EXPECTED_C33_REPLAY_STATUS,
            'bad_month_failure_count' => count($badMonthFailures),
            'data_path_cleared_bad_month_count' => count($dataClearedBadMonths),
            'branch_robustness_flag_count' => count($branchFlags),
            'aggregate_branch_weakness_count' => count($aggregateBranchWeakness),
            'bad_months_requiring_review' => array_values(array_map(function (array $row) {
                return $row['trade_month'];
            }, $badMonthFailures)),
            'branches_requiring_review' => array_values(array_map(function (array $row) {
                return $row['selected_source_code'];
            }, $branchFlags)),
            'bad_month_robustness_status' => $failureConfirmed
                ? 'C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS'
                : 'C34_BAD_MONTH_ROBUSTNESS_NO_FAILURE_AFTER_C33_DATA_PATH_PASS',
            'strategy_robustness_redesign_required' => $failureConfirmed,
            'oos_tuning_allowed' => false,
            'profile_reselection_allowed' => false,
            'production_promotion_allowed' => false,
            'production_ready' => false,
            'diagnostic_conclusion' => $failureConfirmed
                ? 'C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS'
                : 'C34_BAD_MONTH_ROBUSTNESS_NOT_CONFIRMED_AFTER_C33_DATA_PATH_PASS',
            'next_step' => $failureConfirmed
                ? 'C35_IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC_NO_OOS_TUNING'
                : 'CONTROLLED_GATE_RETEST_CAN_BE_CONSIDERED_NO_OOS_TUNING',
        ];
    }

    private function badMonthFailureClass(bool $cleanFailure, bool $dataPathAffected, bool $dataPathCleared): string
    {
        if ($cleanFailure && $dataPathAffected && $dataPathCleared) {
            return 'CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED';
        }
        if ($cleanFailure) {
            return 'CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED';
        }
        if ($dataPathAffected && ! $dataPathCleared) {
            return 'DATA_PATH_BLOCKED_BAD_MONTH_NOT_DIAGNOSED';
        }
        return 'BAD_MONTH_REVIEW_ONLY';
    }

    private function branchFailureClass(bool $flag, bool $aggregateWeakness, bool $dataPathAffected, bool $dataPathCleared): string
    {
        if ($dataPathAffected && $dataPathCleared && ! $flag) {
            return 'DATA_PATH_CLEARED_BRANCH_REVIEW_ONLY';
        }
        if ($flag && $aggregateWeakness) {
            return 'C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED';
        }
        if ($flag) {
            return 'C34_BRANCH_BAD_MONTH_CONCENTRATION_REVIEW';
        }
        return 'C34_BRANCH_REVIEW_ONLY';
    }

    private function badMonthSeverity(bool $cleanFailure, ?float $winRate, ?float $avg): string
    {
        if ($cleanFailure && $winRate !== null && $winRate <= 0.0 && $avg !== null && $avg < 0.0) {
            return 'HIGH_RISK';
        }
        if ($cleanFailure) {
            return 'MEDIUM_RISK';
        }
        return 'REVIEW_ONLY';
    }

    private function clearedReplayMonths(array $c33): array
    {
        $counts = [];
        foreach ($c33['replay_rows'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $month = (string) ($row['trade_month'] ?? '');
            if ($month === '') {
                continue;
            }
            $counts[$month]['total'] = ($counts[$month]['total'] ?? 0) + 1;
            if (($row['raw_ohlc_replay_status'] ?? null) === 'PASS') {
                $counts[$month]['pass'] = ($counts[$month]['pass'] ?? 0) + 1;
            }
        }

        $out = [];
        foreach ($counts as $month => $count) {
            $out[$month] = ($count['total'] ?? 0) > 0 && ($count['total'] ?? 0) === ($count['pass'] ?? 0);
        }
        return $out;
    }

    private function clearedReplaySources(array $c33): array
    {
        $counts = [];
        foreach ($c33['replay_rows'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $source = (string) ($row['selected_source_code'] ?? '');
            if ($source === '') {
                continue;
            }
            $counts[$source]['total'] = ($counts[$source]['total'] ?? 0) + 1;
            if (($row['raw_ohlc_replay_status'] ?? null) === 'PASS') {
                $counts[$source]['pass'] = ($counts[$source]['pass'] ?? 0) + 1;
            }
        }

        $out = [];
        foreach ($counts as $source => $count) {
            $out[$source] = ($count['total'] ?? 0) > 0 && ($count['total'] ?? 0) === ($count['pass'] ?? 0);
        }
        return $out;
    }

    private function completedDiagnostics(array $decision, string $conclusion): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED',
                'message' => 'C34 diagnosed C32 bad-month and branch robustness after C33 data-path replay proof, without tuning or production promotion.',
                'fatal' => false,
                'extra' => $decision,
            ],
            [
                'reason_code' => $conclusion,
                'message' => 'C34 bad-month robustness diagnostic conclusion.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C34_NO_OOS_TUNING_ALLOWED',
                'message' => 'C34 does not allow OOS tuning, profile reselection, best-of-OOS, production catalog creation, or production readiness.',
                'fatal' => false,
                'extra' => [
                    'oos_tuning_allowed' => false,
                    'profile_reselection_allowed' => false,
                    'production_promotion_allowed' => false,
                    'production_ready' => false,
                ],
            ],
        ];
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
        $artifact['diagnostic_conclusion'] = 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_BLOCKED';
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
            'expected_c33_hash' => $artifact['expected_c33_hash'] ?? null,
            'actual_c33_hash' => $artifact['actual_c33_hash'] ?? null,
            'c33_hash_match' => $artifact['c33_hash_match'] ?? false,
            'c33_status' => $artifact['c33_status'] ?? null,
            'c33_data_path_replay_status' => $artifact['c33_data_path_replay_status'] ?? null,
            'expected_c32_hash' => $artifact['expected_c32_hash'] ?? null,
            'actual_c32_hash' => $artifact['actual_c32_hash'] ?? null,
            'c32_hash_match' => $artifact['c32_hash_match'] ?? false,
            'c32_status' => $artifact['c32_status'] ?? null,
            'production_ready' => 0,
        ];
    }

    private function baseArtifact(
        string $inputC33Path,
        string $expectedC33Hash,
        ?string $actualC33Hash,
        $c33Status,
        $c33Conclusion,
        $c33ReplayStatus,
        $c33DataCompletenessGateStatus,
        string $createdAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C34_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c33_artifact' => $inputC33Path,
            'expected_c33_hash' => $expectedC33Hash,
            'actual_c33_hash' => $actualC33Hash,
            'c33_hash_match' => $actualC33Hash !== null && $actualC33Hash === $expectedC33Hash,
            'c33_status' => $c33Status,
            'c33_diagnostic_conclusion' => $c33Conclusion,
            'c33_data_path_replay_status' => $c33ReplayStatus,
            'c33_data_completeness_gate_after_replay' => $c33DataCompletenessGateStatus,
            'input_c32_artifact' => null,
            'expected_c32_hash' => null,
            'actual_c32_hash' => null,
            'c32_hash_match' => false,
            'c32_status' => null,
            'c32_diagnostic_conclusion' => null,
            'c32_bad_month_robustness_status' => null,
            'source_c33_replay_summary' => [],
            'source_c33_data_completeness_gate_after_replay' => [],
            'source_c32_clean_metrics' => [],
            'source_c32_bad_month_robustness_summary' => [],
            'source_c32_branch_robustness_summary' => [],
            'bad_month_robustness_status' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_PENDING',
            'bad_month_diagnostic_rows' => [],
            'branch_robustness_rows' => [],
            'robustness_decision' => [
                'strategy_robustness_redesign_required' => null,
                'oos_tuning_allowed' => false,
                'profile_reselection_allowed' => false,
                'production_promotion_allowed' => false,
                'production_ready' => false,
            ],
            'diagnostic_conclusion' => 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_PENDING',
            'next_step' => 'C34_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                'BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_ONLY' => true,
                'FILE_ARTIFACT_DIAGNOSTIC_ONLY' => true,
                'NO_MARKET_DATA_REPLAY' => true,
                'NO_DB_READ' => true,
                'NO_DB_WRITE' => true,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C33_MUTATION' => true,
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

    private function c32Path(array $c33, array $options): string
    {
        $path = (string) ($options['c32_artifact'] ?? ($c33['input_c32_artifact'] ?? ''));
        return trim($path);
    }

    private function expectedC32Hash(array $c33, array $options): string
    {
        $hash = (string) ($options['expected_c32_hash'] ?? ($c33['actual_c32_hash'] ?? self::DEFAULT_EXPECTED_C32_HASH));
        return trim($hash) !== '' ? trim($hash) : self::DEFAULT_EXPECTED_C32_HASH;
    }

    private function gateStatus($gate): ?string
    {
        if (is_array($gate)) {
            return isset($gate['status']) ? (string) $gate['status'] : null;
        }
        if ($gate === null || $gate === '') {
            return null;
        }
        return (string) $gate;
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C34 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
