<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC31ControlledGateReclassificationService
{
    public const RUN_CODE = 'C31_CONTROLLED_GATE_RECLASSIFICATION';
    public const ARTIFACT_TYPE = 'C31_CONTROLLED_GATE_RECLASSIFICATION';
    public const DEFAULT_C29_ARTIFACT = 'storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json';
    public const DEFAULT_EXPECTED_C29_HASH = 'c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9';
    public const DEFAULT_C30_ARTIFACT = 'storage/app/watchlist/backtest/c30-oos-failure-attribution.json';
    public const DEFAULT_EXPECTED_C30_HASH = '667b639951d6b566cc9b0fa6cf7dc278db92a8f0';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json';
    public const EXPECTED_C29_STATUS = 'C29_OOS_PROOF_FAILED';
    public const EXPECTED_C30_STATUS = 'C30_ATTRIBUTION_COMPLETED';

    private const VALID_C30_VERDICTS = [
        'DATA_COMPLETENESS_FAILURE_CONFIRMED',
        'ACTUAL_LOOKAHEAD_LEAK_CONFIRMED',
        'STRATEGY_ROBUSTNESS_FAILURE_CONFIRMED',
        'MIXED_DATA_AND_STRATEGY_FAILURE',
        'INSUFFICIENT_DIAGNOSTIC_DATA',
    ];

    public function execute(
        string $c29Artifact = self::DEFAULT_C29_ARTIFACT,
        string $expectedC29Hash = self::DEFAULT_EXPECTED_C29_HASH,
        string $c30Artifact = self::DEFAULT_C30_ARTIFACT,
        string $expectedC30Hash = self::DEFAULT_EXPECTED_C30_HASH,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c29Artifact = trim($c29Artifact) !== '' ? trim($c29Artifact) : self::DEFAULT_C29_ARTIFACT;
        $expectedC29Hash = trim($expectedC29Hash) !== '' ? trim($expectedC29Hash) : self::DEFAULT_EXPECTED_C29_HASH;
        $c30Artifact = trim($c30Artifact) !== '' ? trim($c30Artifact) : self::DEFAULT_C30_ARTIFACT;
        $expectedC30Hash = trim($expectedC30Hash) !== '' ? trim($expectedC30Hash) : self::DEFAULT_EXPECTED_C30_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c29Artifact, $expectedC29Hash, null, null, $c30Artifact, $expectedC30Hash, null, null, null, $createdAt);

        if (! is_file($c29Artifact)) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_MISSING_C29_ARTIFACT',
                'WS_BT_C31_C29_ARTIFACT_MISSING',
                'C31 requires the locked C29 failed OOS proof artifact, but the file is missing.',
                $outputPath,
                ['input_c29_artifact' => $c29Artifact]
            );
        }

        $c29 = json_decode((string) file_get_contents($c29Artifact), true);
        if (! is_array($c29)) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_MISSING_C29_ARTIFACT',
                'WS_BT_C31_C29_ARTIFACT_UNREADABLE',
                'C29 artifact is not readable JSON.',
                $outputPath,
                ['input_c29_artifact' => $c29Artifact]
            );
        }

        $actualC29Hash = $this->stableHash($c29);
        $artifact = $this->baseArtifact(
            $c29Artifact,
            $expectedC29Hash,
            $actualC29Hash,
            $c29['status'] ?? null,
            $c30Artifact,
            $expectedC30Hash,
            null,
            null,
            null,
            $createdAt
        );

        if ($actualC29Hash !== $expectedC29Hash) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_C29_HASH_MISMATCH',
                'WS_BT_C31_C29_ARTIFACT_HASH_MISMATCH',
                'C29 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c29_artifact_hash_field' => $c29['artifact_hash'] ?? null]
            );
        }

        if (($c29['status'] ?? null) !== self::EXPECTED_C29_STATUS) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_UNEXPECTED_C29_STATUS',
                'WS_BT_C31_UNEXPECTED_C29_STATUS',
                'C31 requires a failed C29 OOS proof artifact and must not continue on another C29 status.',
                $outputPath,
                ['expected_c29_status' => self::EXPECTED_C29_STATUS]
            );
        }

        if (($c29['production_ready'] ?? false) !== false && (int) ($c29['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_UNEXPECTED_C29_STATUS',
                'WS_BT_C31_C29_PRODUCTION_READY_UNEXPECTED',
                'C31 requires C29 production_ready=false before controlled reclassification.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (! is_file($c30Artifact)) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_MISSING_C30_ARTIFACT',
                'WS_BT_C31_C30_ARTIFACT_MISSING',
                'C31 requires the locked C30 failure attribution artifact, but the file is missing.',
                $outputPath,
                ['input_c30_artifact' => $c30Artifact]
            );
        }

        $c30 = json_decode((string) file_get_contents($c30Artifact), true);
        if (! is_array($c30)) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_MISSING_C30_ARTIFACT',
                'WS_BT_C31_C30_ARTIFACT_UNREADABLE',
                'C30 artifact is not readable JSON.',
                $outputPath,
                ['input_c30_artifact' => $c30Artifact]
            );
        }

        $actualC30Hash = $this->stableHash($c30);
        $artifact = $this->baseArtifact(
            $c29Artifact,
            $expectedC29Hash,
            $actualC29Hash,
            $c29['status'] ?? null,
            $c30Artifact,
            $expectedC30Hash,
            $actualC30Hash,
            $c30['status'] ?? null,
            $c30['attribution_verdict'] ?? null,
            $createdAt
        );

        if ($actualC30Hash !== $expectedC30Hash) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_C30_HASH_MISMATCH',
                'WS_BT_C31_C30_ARTIFACT_HASH_MISMATCH',
                'C30 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c30_artifact_hash_field' => $c30['artifact_hash'] ?? null]
            );
        }

        if (($c30['status'] ?? null) !== self::EXPECTED_C30_STATUS) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_UNEXPECTED_C30_STATUS',
                'WS_BT_C31_UNEXPECTED_C30_STATUS',
                'C31 requires a completed C30 attribution artifact and must not continue on another C30 status.',
                $outputPath,
                ['expected_c30_status' => self::EXPECTED_C30_STATUS]
            );
        }

        if (($c30['production_ready'] ?? false) !== false && (int) ($c30['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_UNEXPECTED_C30_STATUS',
                'WS_BT_C31_C30_PRODUCTION_READY_UNEXPECTED',
                'C31 requires C30 production_ready=false before controlled reclassification.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        $verdict = (string) ($c30['attribution_verdict'] ?? '');
        if (! in_array($verdict, self::VALID_C30_VERDICTS, true)) {
            return $this->blocked(
                $artifact,
                'C31_BLOCKED_UNEXPECTED_C30_VERDICT',
                'WS_BT_C31_UNEXPECTED_C30_VERDICT',
                'C31 requires a known C30 attribution verdict before controlled gate reclassification.',
                $outputPath,
                ['c30_attribution_verdict' => $verdict]
            );
        }

        $sourceMetrics = $this->sourceC29Metrics($c29);
        $classification = $this->classificationSummary($c30);
        $cleanMetrics = $this->cleanMetrics($c30);
        $gateSummary = $this->separatedGateSummary($sourceMetrics, $classification, $cleanMetrics);
        $reclassificationConclusion = $this->reclassificationConclusion($classification);
        $controlledProofStatus = $this->controlledProofStatus($gateSummary);

        $artifact = array_replace_recursive($artifact, [
            'status' => 'C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED',
            'source_c29_metrics' => $sourceMetrics,
            'source_c30_classification_summary' => $classification,
            'source_c30_clean_metrics' => $cleanMetrics,
            'separated_gate_summary' => $gateSummary,
            'bad_month_summary' => array_values(array_filter($c30['bad_month_summary'] ?? [], 'is_array')),
            'source_branch_summary' => array_values(array_filter($c30['source_branch_summary'] ?? [], 'is_array')),
            'missing_path_rows' => array_values(array_filter($c30['missing_path_rows'] ?? [], 'is_array')),
            'actual_lookahead_violation_rows' => array_values(array_filter($c30['actual_lookahead_violation_rows'] ?? [], 'is_array')),
            'selection_leak_rows' => array_values(array_filter($c30['selection_leak_rows'] ?? [], 'is_array')),
            'reclassification_conclusion' => $reclassificationConclusion,
            'controlled_proof_status' => $controlledProofStatus,
            'diagnostics' => $this->completedDiagnostics($gateSummary, $reclassificationConclusion, $controlledProofStatus),
        ]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C31_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C31 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
            ];
        }

        return [
            'status' => 'C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED',
            'reason_code' => 'C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c29_hash' => $expectedC29Hash,
            'actual_c29_hash' => $actualC29Hash,
            'c29_hash_match' => true,
            'c29_status' => $c29['status'] ?? null,
            'expected_c30_hash' => $expectedC30Hash,
            'actual_c30_hash' => $actualC30Hash,
            'c30_hash_match' => true,
            'c30_status' => $c30['status'] ?? null,
            'c30_attribution_verdict' => $verdict,
            'source_c30_classification_summary' => $classification,
            'source_c30_clean_metrics' => $cleanMetrics,
            'separated_gate_summary' => $gateSummary,
            'reclassification_conclusion' => $reclassificationConclusion,
            'controlled_proof_status' => $controlledProofStatus,
        ];
    }

    private function separatedGateSummary(array $sourceMetrics, array $classification, array $cleanMetrics): array
    {
        $reportedCount = (int) ($classification['reported_lookahead_violation_count'] ?? 0);
        $actualCount = (int) ($classification['actual_lookahead_violation_count'] ?? 0);
        $selectionCount = (int) ($classification['selection_leak_count'] ?? 0);
        $missingCount = (int) ($classification['missing_path_count'] ?? 0);
        $nonEvaluableCount = (int) ($classification['non_evaluable_pick_count'] ?? 0);
        $sourceMonthWinRateMin = $this->num($sourceMetrics['month_win_rate_min'] ?? null);
        $cleanMonthWinRateMin = $this->num($cleanMetrics['clean_month_win_rate_min'] ?? null);

        $reported = $this->gate(
            $reportedCount > 0 ? 'FAIL' : 'PASS',
            $reportedCount > 0 ? 'C31_REPORTED_LOOKAHEAD_GATE_FAIL_FROM_C29_COUNT' : 'C31_REPORTED_LOOKAHEAD_GATE_PASS_NO_REPORTED_COUNT'
        );
        $actual = $this->gate(
            $actualCount === 0 ? 'PASS' : 'FAIL',
            $actualCount === 0 ? 'C31_ACTUAL_LOOKAHEAD_GATE_PASS_NO_ACTUAL_LEAK' : 'C31_ACTUAL_LOOKAHEAD_GATE_FAIL_ACTUAL_LEAK'
        );
        $selection = $this->gate(
            $selectionCount === 0 ? 'PASS' : 'FAIL',
            $selectionCount === 0 ? 'C31_SELECTION_LEAK_GATE_PASS_NO_SELECTION_LEAK' : 'C31_SELECTION_LEAK_GATE_FAIL_SELECTION_LEAK'
        );
        $data = $this->gate(
            $missingCount > 0 || $nonEvaluableCount > 0 ? 'FAIL' : 'PASS',
            $missingCount > 0 || $nonEvaluableCount > 0 ? 'C31_DATA_COMPLETENESS_GATE_FAIL_MISSING_PATH' : 'C31_DATA_COMPLETENESS_GATE_PASS_NO_MISSING_PATH'
        );
        $month = $this->gate(
            $this->isZero($sourceMonthWinRateMin) ? 'FAIL' : 'PASS',
            $this->isZero($sourceMonthWinRateMin) ? 'C31_MONTH_WIN_RATE_GATE_FAIL_SOURCE_MONTH_ZERO' : 'C31_MONTH_WIN_RATE_GATE_PASS_SOURCE_MONTH_NONZERO'
        );
        $cleanMonth = $this->gate(
            $this->isZero($cleanMonthWinRateMin) ? 'FAIL' : 'PASS',
            $this->isZero($cleanMonthWinRateMin) ? 'C31_CLEAN_MONTH_WIN_RATE_GATE_FAIL_CLEAN_MONTH_ZERO' : 'C31_CLEAN_MONTH_WIN_RATE_GATE_PASS_CLEAN_MONTH_NONZERO'
        );
        $overall = $this->overallGate($reported, $actual, $selection, $data, $month, $cleanMonth);

        return [
            'reported_lookahead_gate' => $reported,
            'actual_lookahead_gate' => $actual,
            'selection_leak_gate' => $selection,
            'data_completeness_gate' => $data,
            'month_win_rate_gate' => $month,
            'clean_month_win_rate_gate' => $cleanMonth,
            'overall_controlled_oos_gate' => $overall,
        ];
    }

    private function overallGate(array $reported, array $actual, array $selection, array $data, array $month, array $cleanMonth): array
    {
        if (($actual['status'] ?? null) === 'FAIL' || ($selection['status'] ?? null) === 'FAIL') {
            return $this->gate('FAIL', 'C31_CONTROLLED_OOS_GATE_FAIL_ACTUAL_LEAK_OR_SELECTION_LEAK');
        }
        $dataFail = ($data['status'] ?? null) === 'FAIL';
        $robustnessFail = ($month['status'] ?? null) === 'FAIL' || ($cleanMonth['status'] ?? null) === 'FAIL';
        if ($dataFail && $robustnessFail) {
            return $this->gate('FAIL', 'C31_CONTROLLED_OOS_GATE_FAIL_DATA_COMPLETENESS_AND_ROBUSTNESS');
        }
        if ($dataFail) {
            return $this->gate('FAIL', 'C31_CONTROLLED_OOS_GATE_FAIL_DATA_COMPLETENESS');
        }
        if ($robustnessFail) {
            return $this->gate('FAIL', 'C31_CONTROLLED_OOS_GATE_FAIL_ROBUSTNESS');
        }
        if (($reported['status'] ?? null) === 'FAIL') {
            return $this->gate('FAIL', 'C31_CONTROLLED_OOS_GATE_FAIL_REPORTED_LOOKAHEAD');
        }
        return $this->gate('PASS', 'C31_CONTROLLED_OOS_GATE_PASS');
    }

    private function reclassificationConclusion(array $classification): string
    {
        $reported = (int) ($classification['reported_lookahead_violation_count'] ?? 0);
        $actual = (int) ($classification['actual_lookahead_violation_count'] ?? 0);
        $selection = (int) ($classification['selection_leak_count'] ?? 0);
        $missing = (int) ($classification['missing_path_count'] ?? 0);

        if ($reported > 0 && $actual === 0 && $selection === 0 && $missing > 0) {
            return 'C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK';
        }
        if ($actual > 0) {
            return 'C31_RECLASSIFICATION_ACTUAL_LOOKAHEAD_LEAK_CONFIRMED';
        }
        if ($selection > 0) {
            return 'C31_RECLASSIFICATION_SELECTION_LEAK_CONFIRMED';
        }
        if ($missing > 0) {
            return 'C31_RECLASSIFICATION_DATA_COMPLETENESS_FAILURE_CONFIRMED';
        }
        return 'C31_RECLASSIFICATION_NO_REPORTED_LOOKAHEAD_FAILURE';
    }

    private function controlledProofStatus(array $gateSummary): string
    {
        $actualPass = ($gateSummary['actual_lookahead_gate']['status'] ?? null) === 'PASS';
        $selectionPass = ($gateSummary['selection_leak_gate']['status'] ?? null) === 'PASS';
        $dataFail = ($gateSummary['data_completeness_gate']['status'] ?? null) === 'FAIL';
        $monthFail = ($gateSummary['month_win_rate_gate']['status'] ?? null) === 'FAIL'
            || ($gateSummary['clean_month_win_rate_gate']['status'] ?? null) === 'FAIL';

        if (! $actualPass) {
            return 'C31_CONTROLLED_OOS_PROOF_FAILED_ACTUAL_LOOKAHEAD_LEAK';
        }
        if (! $selectionPass) {
            return 'C31_CONTROLLED_OOS_PROOF_FAILED_SELECTION_LEAK';
        }
        if ($dataFail && $monthFail && $actualPass && $selectionPass) {
            return 'C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS';
        }
        if ($dataFail) {
            return 'C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS';
        }
        if ($monthFail) {
            return 'C31_CONTROLLED_OOS_PROOF_FAILED_ROBUSTNESS';
        }
        return 'C31_CONTROLLED_OOS_PROOF_PASSED_NOT_PRODUCTION_READY';
    }

    private function completedDiagnostics(array $gateSummary, string $conclusion, string $proofStatus): array
    {
        $items = [[
            'reason_code' => 'WS_BT_C31_CONTROLLED_RECLASSIFICATION_COMPLETED',
            'message' => 'C31 completed controlled gate reclassification against locked C29 and C30 artifacts without retuning, profile reselection, best-of-OOS, promotion, or production readiness.',
            'fatal' => false,
        ]];
        if (($gateSummary['actual_lookahead_gate']['status'] ?? null) === 'PASS') {
            $items[] = [
                'reason_code' => 'WS_BT_C31_ACTUAL_LOOKAHEAD_GATE_PASS',
                'message' => 'Actual lookahead gate passes after missing-path rows are separated from actual future-data leaks.',
                'fatal' => false,
            ];
        }
        if (($gateSummary['data_completeness_gate']['status'] ?? null) === 'FAIL') {
            $items[] = [
                'reason_code' => 'WS_BT_C31_DATA_COMPLETENESS_GATE_FAIL',
                'message' => 'Missing/non-evaluable OHLC path rows are classified under data completeness, not actual lookahead leakage.',
                'fatal' => false,
            ];
        }
        $items[] = [
            'reason_code' => $conclusion,
            'message' => 'C31 reclassification conclusion.',
            'fatal' => false,
        ];
        $items[] = [
            'reason_code' => $proofStatus,
            'message' => 'C31 final controlled OOS proof status.',
            'fatal' => false,
        ];
        return $items;
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
        $artifact['reclassification_conclusion'] = 'C31_RECLASSIFICATION_BLOCKED';
        $artifact['controlled_proof_status'] = $status;
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
            'expected_c29_hash' => $artifact['expected_c29_hash'] ?? null,
            'actual_c29_hash' => $artifact['actual_c29_hash'] ?? null,
            'c29_hash_match' => $artifact['c29_hash_match'] ?? false,
            'c29_status' => $artifact['c29_status'] ?? null,
            'expected_c30_hash' => $artifact['expected_c30_hash'] ?? null,
            'actual_c30_hash' => $artifact['actual_c30_hash'] ?? null,
            'c30_hash_match' => $artifact['c30_hash_match'] ?? false,
            'c30_status' => $artifact['c30_status'] ?? null,
            'c30_attribution_verdict' => $artifact['c30_attribution_verdict'] ?? null,
            'production_ready' => 0,
        ];
    }

    private function baseArtifact(
        string $inputC29Path,
        string $expectedC29Hash,
        ?string $actualC29Hash,
        $c29Status,
        string $inputC30Path,
        string $expectedC30Hash,
        ?string $actualC30Hash,
        $c30Status,
        $c30Verdict,
        string $createdAt
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C31_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c29_artifact' => $inputC29Path,
            'expected_c29_hash' => $expectedC29Hash,
            'actual_c29_hash' => $actualC29Hash,
            'c29_hash_match' => $actualC29Hash !== null && $actualC29Hash === $expectedC29Hash,
            'c29_status' => $c29Status,
            'input_c30_artifact' => $inputC30Path,
            'expected_c30_hash' => $expectedC30Hash,
            'actual_c30_hash' => $actualC30Hash,
            'c30_hash_match' => $actualC30Hash !== null && $actualC30Hash === $expectedC30Hash,
            'c30_status' => $c30Status,
            'c30_attribution_verdict' => $c30Verdict,
            'source_c29_metrics' => [
                'evaluated_picks_count' => null,
                'avg_ret_net' => null,
                'median_ret_net' => null,
                'p25_ret_net' => null,
                'win_rate' => null,
                'month_win_rate_min' => null,
                'month_avg_ret_net_min' => null,
                'lookahead_violation_count' => null,
            ],
            'source_c30_classification_summary' => [
                'total_oos_pick_rows' => 0,
                'reported_lookahead_violation_count' => 0,
                'actual_lookahead_violation_count' => 0,
                'selection_leak_count' => 0,
                'missing_path_count' => 0,
                'non_evaluable_pick_count' => 0,
                'clean_evaluable_pick_count' => 0,
            ],
            'source_c30_clean_metrics' => [
                'clean_evaluated_picks_count' => 0,
                'clean_avg_ret_net' => null,
                'clean_median_ret_net' => null,
                'clean_p25_ret_net' => null,
                'clean_win_rate' => null,
                'clean_month_win_rate_min' => null,
                'clean_month_avg_ret_net_min' => null,
            ],
            'separated_gate_summary' => [
                'reported_lookahead_gate' => $this->gate('BLOCKED', 'C31_REPORTED_LOOKAHEAD_GATE_BLOCKED'),
                'actual_lookahead_gate' => $this->gate('BLOCKED', 'C31_ACTUAL_LOOKAHEAD_GATE_BLOCKED'),
                'selection_leak_gate' => $this->gate('BLOCKED', 'C31_SELECTION_LEAK_GATE_BLOCKED'),
                'data_completeness_gate' => $this->gate('BLOCKED', 'C31_DATA_COMPLETENESS_GATE_BLOCKED'),
                'month_win_rate_gate' => $this->gate('BLOCKED', 'C31_MONTH_WIN_RATE_GATE_BLOCKED'),
                'clean_month_win_rate_gate' => $this->gate('BLOCKED', 'C31_CLEAN_MONTH_WIN_RATE_GATE_BLOCKED'),
                'overall_controlled_oos_gate' => $this->gate('BLOCKED', 'C31_CONTROLLED_OOS_GATE_BLOCKED'),
            ],
            'bad_month_summary' => [],
            'source_branch_summary' => [],
            'missing_path_rows' => [],
            'actual_lookahead_violation_rows' => [],
            'selection_leak_rows' => [],
            'reclassification_conclusion' => 'C31_RECLASSIFICATION_PENDING',
            'controlled_proof_status' => 'C31_CONTROLLED_OOS_PROOF_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                'CONTROLLED_GATE_RECLASSIFICATION_ONLY' => true,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C30_MUTATION' => true,
                'production_ready' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_profile_selection' => false,
                'actual_lookahead_gate_separated_from_data_completeness_gate' => true,
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

    private function sourceC29Metrics(array $c29): array
    {
        $metrics = is_array($c29['metrics'] ?? null) ? $c29['metrics'] : [];
        return [
            'evaluated_picks_count' => $metrics['evaluated_picks_count'] ?? null,
            'avg_ret_net' => $metrics['avg_ret_net'] ?? null,
            'median_ret_net' => $metrics['median_ret_net'] ?? null,
            'p25_ret_net' => $metrics['p25_ret_net'] ?? null,
            'win_rate' => $metrics['win_rate'] ?? null,
            'month_win_rate_min' => $metrics['month_win_rate_min'] ?? null,
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'] ?? null,
            'lookahead_violation_count' => $c29['lookahead_violation_count'] ?? null,
        ];
    }

    private function classificationSummary(array $c30): array
    {
        $summary = is_array($c30['classification_summary'] ?? null) ? $c30['classification_summary'] : [];
        return [
            'total_oos_pick_rows' => (int) ($summary['total_oos_pick_rows'] ?? 0),
            'reported_lookahead_violation_count' => (int) ($summary['reported_lookahead_violation_count'] ?? 0),
            'actual_lookahead_violation_count' => (int) ($summary['actual_lookahead_violation_count'] ?? 0),
            'selection_leak_count' => (int) ($summary['selection_leak_count'] ?? 0),
            'missing_path_count' => (int) ($summary['missing_path_count'] ?? 0),
            'non_evaluable_pick_count' => (int) ($summary['non_evaluable_pick_count'] ?? 0),
            'clean_evaluable_pick_count' => (int) ($summary['clean_evaluable_pick_count'] ?? 0),
        ];
    }

    private function cleanMetrics(array $c30): array
    {
        $metrics = is_array($c30['clean_metrics'] ?? null) ? $c30['clean_metrics'] : [];
        return [
            'clean_evaluated_picks_count' => (int) ($metrics['clean_evaluated_picks_count'] ?? 0),
            'clean_avg_ret_net' => $metrics['clean_avg_ret_net'] ?? null,
            'clean_median_ret_net' => $metrics['clean_median_ret_net'] ?? null,
            'clean_p25_ret_net' => $metrics['clean_p25_ret_net'] ?? null,
            'clean_win_rate' => $metrics['clean_win_rate'] ?? null,
            'clean_month_win_rate_min' => $metrics['clean_month_win_rate_min'] ?? null,
            'clean_month_avg_ret_net_min' => $metrics['clean_month_avg_ret_net_min'] ?? null,
        ];
    }

    private function gate(string $status, string $reasonCode): array
    {
        return [
            'status' => $status,
            'reason_code' => $reasonCode,
        ];
    }

    private function isZero(?float $value): bool
    {
        return $value !== null && abs($value) < 0.000000000001;
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C31 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
