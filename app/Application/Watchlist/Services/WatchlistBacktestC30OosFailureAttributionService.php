<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC30OosFailureAttributionService
{
    public const RUN_CODE = 'C30_OOS_FAILURE_ATTRIBUTION';
    public const ARTIFACT_TYPE = 'C30_OOS_FAILURE_ATTRIBUTION';
    public const DEFAULT_C29_ARTIFACT = 'storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json';
    public const DEFAULT_EXPECTED_C29_HASH = 'c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c30-oos-failure-attribution.json';
    public const EXPECTED_C29_STATUS = 'C29_OOS_PROOF_FAILED';

    public function execute(
        string $c29Artifact = self::DEFAULT_C29_ARTIFACT,
        string $expectedC29Hash = self::DEFAULT_EXPECTED_C29_HASH,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c29Artifact = trim($c29Artifact) !== '' ? trim($c29Artifact) : self::DEFAULT_C29_ARTIFACT;
        $expectedC29Hash = trim($expectedC29Hash) !== '' ? trim($expectedC29Hash) : self::DEFAULT_EXPECTED_C29_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if (! is_file($c29Artifact)) {
            return $this->blocked(
                'C30_BLOCKED_MISSING_C29_ARTIFACT',
                'WS_BT_C30_C29_ARTIFACT_MISSING',
                'C30 requires the locked C29 OOS proof artifact, but the file is missing.',
                $c29Artifact,
                $expectedC29Hash,
                null,
                null,
                $outputPath,
                $createdAt,
                ['input_c29_artifact' => $c29Artifact]
            );
        }

        $decoded = json_decode((string) file_get_contents($c29Artifact), true);
        if (! is_array($decoded)) {
            return $this->blocked(
                'C30_BLOCKED_MISSING_C29_ARTIFACT',
                'WS_BT_C30_C29_ARTIFACT_UNREADABLE',
                'C29 artifact is not readable JSON.',
                $c29Artifact,
                $expectedC29Hash,
                null,
                null,
                $outputPath,
                $createdAt,
                ['input_c29_artifact' => $c29Artifact]
            );
        }

        $actualHash = $this->stableHash($decoded);
        if ($actualHash !== $expectedC29Hash) {
            return $this->blocked(
                'C30_BLOCKED_C29_HASH_MISMATCH',
                'WS_BT_C30_C29_ARTIFACT_HASH_MISMATCH',
                'C29 artifact stable hash does not match the expected locked hash.',
                $c29Artifact,
                $expectedC29Hash,
                $actualHash,
                $decoded['status'] ?? null,
                $outputPath,
                $createdAt,
                ['c29_artifact_hash_field' => $decoded['artifact_hash'] ?? null]
            );
        }

        if (($decoded['status'] ?? null) !== self::EXPECTED_C29_STATUS) {
            return $this->blocked(
                'C30_BLOCKED_UNEXPECTED_C29_STATUS',
                'WS_BT_C30_UNEXPECTED_C29_STATUS',
                'C30 requires a C29 failed artifact. It must not continue silently on a non-failed C29 artifact.',
                $c29Artifact,
                $expectedC29Hash,
                $actualHash,
                $decoded['status'] ?? null,
                $outputPath,
                $createdAt,
                ['expected_c29_status' => self::EXPECTED_C29_STATUS]
            );
        }

        $rows = array_values(array_filter($decoded['oos_pick_rows'] ?? [], function ($row): bool {
            return is_array($row);
        }));

        $classification = $this->classifyRows($rows);
        $cleanRows = $classification['clean_evaluable_rows'];
        $cleanMetrics = $this->cleanMetrics($cleanRows);
        $badMonths = $this->badMonthSummary($rows, $cleanRows, $decoded['metrics']['month_summary'] ?? []);
        $badMonthKeys = [];
        foreach ($badMonths as $row) {
            $month = (string) ($row['trade_month'] ?? '');
            if ($month !== '') {
                $badMonthKeys[$month] = true;
            }
        }
        $branchSummary = $this->sourceBranchSummary($rows, $cleanRows, $badMonthKeys);
        $tickerSummary = $this->tickerFailureSummary($rows, $classification);
        $summary = [
            'total_oos_pick_rows' => count($rows),
            'reported_lookahead_violation_count' => $this->reportedLookaheadViolationCount($decoded, $rows),
            'actual_lookahead_violation_count' => count($classification['actual_lookahead_violation_rows']),
            'selection_leak_count' => count($classification['selection_leak_rows']),
            'missing_path_count' => count($classification['missing_path_rows']),
            'non_evaluable_pick_count' => count($classification['non_evaluable_rows']),
            'clean_evaluable_pick_count' => count($cleanRows),
        ];
        $verdict = $this->attributionVerdict($summary, $cleanMetrics, $badMonths);

        $artifact = $this->baseArtifact($c29Artifact, $expectedC29Hash, $actualHash, $decoded['status'] ?? null, $createdAt);
        $artifact = array_replace_recursive($artifact, [
            'status' => 'C30_ATTRIBUTION_COMPLETED',
            'c29_hash_match' => true,
            'c29_artifact_hash' => $decoded['artifact_hash'] ?? $actualHash,
            'source_c29_metrics' => $this->sourceC29Metrics($decoded),
            'classification_summary' => $summary,
            'clean_metrics' => $cleanMetrics,
            'bad_month_summary' => $badMonths,
            'source_branch_summary' => $branchSummary,
            'ticker_failure_summary' => $tickerSummary,
            'missing_path_rows' => $this->compactRows($classification['missing_path_rows']),
            'actual_lookahead_violation_rows' => $this->compactRows($classification['actual_lookahead_violation_rows']),
            'selection_leak_rows' => $this->compactRows($classification['selection_leak_rows']),
            'diagnostics' => $this->completedDiagnostics($summary, $verdict),
            'attribution_verdict' => $verdict,
        ]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C30_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C30 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
            ];
        }

        return [
            'status' => 'C30_ATTRIBUTION_COMPLETED',
            'reason_code' => 'C30_ATTRIBUTION_COMPLETED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'attribution_verdict' => $verdict,
            'expected_c29_hash' => $expectedC29Hash,
            'actual_c29_hash' => $actualHash,
            'c29_hash_match' => true,
            'c29_status' => $decoded['status'] ?? null,
            'production_ready' => 0,
            'classification_summary' => $summary,
            'clean_metrics' => $cleanMetrics,
        ];
    }

    private function classifyRows(array $rows): array
    {
        $out = [
            'clean_evaluable_rows' => [],
            'missing_path_rows' => [],
            'non_evaluable_rows' => [],
            'actual_lookahead_violation_rows' => [],
            'selection_leak_rows' => [],
        ];

        foreach ($rows as $row) {
            $missingPath = $this->isMissingPathRow($row);
            $selectionLeak = $this->isSelectionLeakRow($row);
            $actualLookahead = $this->isActualLookaheadViolationRow($row, $missingPath);
            $ret = $this->num($row['profile_ret_net'] ?? null);
            $nonEvaluable = $missingPath || $ret === null;

            if ($missingPath) {
                $out['missing_path_rows'][] = $row;
            }
            if ($nonEvaluable) {
                $out['non_evaluable_rows'][] = $row;
            }
            if ($actualLookahead) {
                $out['actual_lookahead_violation_rows'][] = $row;
            }
            if ($selectionLeak) {
                $out['selection_leak_rows'][] = $row;
            }
            if (! $missingPath && ! $actualLookahead && ! $selectionLeak && $ret !== null) {
                $out['clean_evaluable_rows'][] = $row;
            }
        }

        return $out;
    }

    private function isMissingPathRow(array $row): bool
    {
        if (($row['missing_path_data_flag'] ?? false) === true) {
            return true;
        }
        if (array_key_exists('raw_ohlc_validated_flag', $row) && ($row['raw_ohlc_validated_flag'] ?? null) === false) {
            return true;
        }
        $reason = trim((string) ($row['missing_path_reason_code'] ?? ''));
        return $reason !== '';
    }

    private function isSelectionLeakRow(array $row): bool
    {
        return ($row['future_path_price_used_for_selection'] ?? false) === true
            || ($row['profile_ret_net_used_for_selection'] ?? false) === true
            || ($row['derived_mfe_mae_used_for_execution'] ?? false) === true;
    }

    private function isActualLookaheadViolationRow(array $row, bool $missingPath): bool
    {
        $reason = strtoupper((string) ($row['lookahead_violation_reason'] ?? ''));
        $explicitFutureLeak = $reason !== '' && preg_match('/FUTURE|LEAK|SELECTION|PROFILE_RET|MFE|MAE/', $reason) === 1;
        if ($explicitFutureLeak) {
            return true;
        }
        return ($row['lookahead_safe'] ?? true) === false && ! $missingPath;
    }

    private function cleanMetrics(array $rows): array
    {
        $returns = $this->values($rows, 'profile_ret_net');
        $wins = count(array_filter($returns, function (float $value): bool { return $value > 0; }));
        $monthRows = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? 'UNKNOWN');
            $monthRows[$month][] = $row;
        }
        ksort($monthRows, SORT_STRING);
        $monthWinRates = [];
        $monthAvgReturns = [];
        foreach ($monthRows as $items) {
            $vals = $this->values($items, 'profile_ret_net');
            if ($vals === []) {
                continue;
            }
            $monthWins = count(array_filter($vals, function (float $value): bool { return $value > 0; }));
            $monthWinRates[] = $monthWins / count($vals);
            $avg = $this->avg($vals);
            if ($avg !== null) {
                $monthAvgReturns[] = $avg;
            }
        }

        return [
            'clean_evaluated_picks_count' => count($returns),
            'clean_avg_ret_net' => $this->avg($returns),
            'clean_median_ret_net' => $this->median($returns),
            'clean_p25_ret_net' => $this->percentile($returns, 0.25),
            'clean_win_rate' => count($returns) > 0 ? $wins / count($returns) : null,
            'clean_month_win_rate_min' => $monthWinRates === [] ? null : min($monthWinRates),
            'clean_month_avg_ret_net_min' => $monthAvgReturns === [] ? null : min($monthAvgReturns),
        ];
    }

    private function badMonthSummary(array $allRows, array $cleanRows, array $sourceMonthSummary): array
    {
        $badMonths = [];
        foreach ($sourceMonthSummary as $month) {
            if (! is_array($month)) {
                continue;
            }
            $tradeMonth = (string) ($month['trade_month'] ?? $month['month'] ?? '');
            $winRate = $this->num($month['win_rate'] ?? null);
            if ($tradeMonth !== '' && $winRate !== null && $winRate <= 0.0) {
                $badMonths[$tradeMonth] = true;
            }
        }
        $monthCleanRows = [];
        foreach ($cleanRows as $row) {
            $month = (string) ($row['trade_month'] ?? '');
            if ($month !== '') {
                $monthCleanRows[$month][] = $row;
            }
        }
        foreach ($monthCleanRows as $month => $items) {
            $vals = $this->values($items, 'profile_ret_net');
            if ($vals === []) {
                continue;
            }
            $wins = count(array_filter($vals, function (float $value): bool { return $value > 0; }));
            if ($wins === 0) {
                $badMonths[$month] = true;
            }
        }
        ksort($badMonths, SORT_STRING);

        $out = [];
        foreach (array_keys($badMonths) as $month) {
            $monthAll = array_values(array_filter($allRows, function (array $row) use ($month): bool {
                return (string) ($row['trade_month'] ?? '') === $month;
            }));
            $monthClean = array_values(array_filter($cleanRows, function (array $row) use ($month): bool {
                return (string) ($row['trade_month'] ?? '') === $month;
            }));
            $returns = $this->values($monthClean, 'profile_ret_net');
            $wins = count(array_filter($returns, function (float $value): bool { return $value > 0; }));
            $missing = count(array_filter($monthAll, function (array $row): bool { return $this->isMissingPathRow($row); }));
            $out[] = [
                'trade_month' => $month,
                'total_rows' => count($monthAll),
                'clean_rows' => count($monthClean),
                'missing_path_rows' => $missing,
                'avg_ret_net' => $this->avg($returns),
                'median_ret_net' => $this->median($returns),
                'p25_ret_net' => $this->percentile($returns, 0.25),
                'win_rate' => count($returns) > 0 ? $wins / count($returns) : null,
                'dominant_branch' => $this->dominantValue($monthAll, 'selected_source_code'),
                'dominant_ticker' => $this->dominantValue($monthAll, 'ticker'),
            ];
        }

        return $out;
    }

    private function sourceBranchSummary(array $allRows, array $cleanRows, array $badMonthKeys): array
    {
        $branches = [];
        foreach ($allRows as $row) {
            $branch = $this->branchCode($row);
            $branches[$branch]['all'][] = $row;
        }
        foreach ($cleanRows as $row) {
            $branch = $this->branchCode($row);
            $branches[$branch]['clean'][] = $row;
        }
        ksort($branches, SORT_STRING);

        $out = [];
        foreach ($branches as $branch => $groups) {
            $all = $groups['all'] ?? [];
            $clean = $groups['clean'] ?? [];
            $returns = $this->values($clean, 'profile_ret_net');
            $wins = count(array_filter($returns, function (float $value): bool { return $value > 0; }));
            $badContribution = [];
            foreach ($all as $row) {
                $month = (string) ($row['trade_month'] ?? '');
                if ($month === '' || ! isset($badMonthKeys[$month])) {
                    continue;
                }
                $badContribution[$month] = ($badContribution[$month] ?? 0) + 1;
            }
            ksort($badContribution, SORT_STRING);
            $out[] = [
                'selected_source_code' => $branch,
                'count' => count($all),
                'clean_count' => count($clean),
                'missing_count' => count(array_filter($all, function (array $row): bool { return $this->isMissingPathRow($row); })),
                'non_evaluable_count' => count(array_filter($all, function (array $row): bool { return $this->isMissingPathRow($row) || $this->num($row['profile_ret_net'] ?? null) === null; })),
                'avg_ret_net' => $this->avg($returns),
                'median_ret_net' => $this->median($returns),
                'p25_ret_net' => $this->percentile($returns, 0.25),
                'win_rate' => count($returns) > 0 ? $wins / count($returns) : null,
                'bad_month_contribution' => $badContribution,
            ];
        }

        return $out;
    }

    private function tickerFailureSummary(array $rows, array $classification): array
    {
        $failureKeys = [];
        foreach (['missing_path_rows', 'actual_lookahead_violation_rows', 'selection_leak_rows', 'non_evaluable_rows'] as $bucket) {
            foreach ($classification[$bucket] as $row) {
                $failureKeys[$this->rowKey($row)] = true;
            }
        }
        foreach ($rows as $row) {
            $ret = $this->num($row['profile_ret_net'] ?? null);
            if ($ret !== null && $ret <= 0.0) {
                $failureKeys[$this->rowKey($row)] = true;
            }
        }

        $groups = [];
        foreach ($rows as $row) {
            if (! isset($failureKeys[$this->rowKey($row)])) {
                continue;
            }
            $key = implode('|', [
                (string) ($row['ticker'] ?? 'UNKNOWN'),
                (string) ($row['trade_month'] ?? 'UNKNOWN'),
                $this->branchCode($row),
            ]);
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'ticker' => (string) ($row['ticker'] ?? 'UNKNOWN'),
                    'trade_month' => (string) ($row['trade_month'] ?? 'UNKNOWN'),
                    'selected_source_code' => $this->branchCode($row),
                    'rows' => [],
                    'reasons' => [],
                ];
            }
            $groups[$key]['rows'][] = $row;
            foreach ($this->rowReasons($row) as $reason) {
                $groups[$key]['reasons'][$reason] = ($groups[$key]['reasons'][$reason] ?? 0) + 1;
            }
        }
        ksort($groups, SORT_STRING);

        $out = [];
        foreach ($groups as $group) {
            ksort($group['reasons'], SORT_STRING);
            $out[] = [
                'ticker' => $group['ticker'],
                'trade_month' => $group['trade_month'],
                'count' => count($group['rows']),
                'selected_source_code' => $group['selected_source_code'],
                'avg_ret_net' => $this->avg($this->values($group['rows'], 'profile_ret_net')),
                'reason_summary' => $group['reasons'],
            ];
        }

        return $out;
    }

    private function rowReasons(array $row): array
    {
        $reasons = [];
        if ($this->isMissingPathRow($row)) {
            $reasons[] = (string) ($row['missing_path_reason_code'] ?? 'MISSING_PATH');
        }
        if ($this->isActualLookaheadViolationRow($row, $this->isMissingPathRow($row))) {
            $reasons[] = (string) ($row['lookahead_violation_reason'] ?? 'ACTUAL_LOOKAHEAD_VIOLATION');
        }
        if ($this->isSelectionLeakRow($row)) {
            if (($row['future_path_price_used_for_selection'] ?? false) === true) {
                $reasons[] = 'future_path_price_used_for_selection';
            }
            if (($row['profile_ret_net_used_for_selection'] ?? false) === true) {
                $reasons[] = 'profile_ret_net_used_for_selection';
            }
            if (($row['derived_mfe_mae_used_for_execution'] ?? false) === true) {
                $reasons[] = 'derived_mfe_mae_used_for_execution';
            }
        }
        $ret = $this->num($row['profile_ret_net'] ?? null);
        if ($ret === null) {
            $reasons[] = 'NON_EVALUABLE_RETURN';
        } elseif ($ret <= 0.0) {
            $reasons[] = 'NON_POSITIVE_RETURN';
        }
        return array_values(array_unique($reasons));
    }

    private function attributionVerdict(array $summary, array $cleanMetrics, array $badMonths): string
    {
        if (($summary['total_oos_pick_rows'] ?? 0) <= 0) {
            return 'INSUFFICIENT_DIAGNOSTIC_DATA';
        }
        if (($summary['actual_lookahead_violation_count'] ?? 0) > 0 || ($summary['selection_leak_count'] ?? 0) > 0) {
            return 'ACTUAL_LOOKAHEAD_LEAK_CONFIRMED';
        }
        $dataFailure = ($summary['missing_path_count'] ?? 0) > 0 || ($summary['non_evaluable_pick_count'] ?? 0) > 0;
        $strategyFailure = ($cleanMetrics['clean_month_win_rate_min'] ?? null) === 0.0
            || ($cleanMetrics['clean_month_avg_ret_net_min'] ?? null) !== null && ($cleanMetrics['clean_month_avg_ret_net_min'] ?? 0) < 0.0
            || count($badMonths) > 0;
        if ($dataFailure && $strategyFailure) {
            return 'MIXED_DATA_AND_STRATEGY_FAILURE';
        }
        if ($dataFailure) {
            return 'DATA_COMPLETENESS_FAILURE_CONFIRMED';
        }
        if ($strategyFailure) {
            return 'STRATEGY_ROBUSTNESS_FAILURE_CONFIRMED';
        }
        return 'INSUFFICIENT_DIAGNOSTIC_DATA';
    }

    private function completedDiagnostics(array $summary, string $verdict): array
    {
        $items = [[
            'reason_code' => 'WS_BT_C30_ATTRIBUTION_COMPLETED',
            'message' => 'C30 completed failure attribution against the locked C29 failed artifact without retuning, profile reselection, best-of-OOS, promotion, or production readiness.',
            'fatal' => false,
        ]];
        if (($summary['selection_leak_count'] ?? 0) === 0 && ($summary['actual_lookahead_violation_count'] ?? 0) === 0) {
            $items[] = [
                'reason_code' => 'WS_BT_C30_NO_ACTUAL_LOOKAHEAD_LEAK_CONFIRMED',
                'message' => 'No actual future-data selection leak row was found after separating missing path rows from lookahead-safe=false rows.',
                'fatal' => false,
            ];
        }
        if (($summary['missing_path_count'] ?? 0) > 0) {
            $items[] = [
                'reason_code' => 'WS_BT_C30_DATA_COMPLETENESS_FAILURE_CONFIRMED',
                'message' => 'Missing/non-evaluable OHLC path rows exist and are not counted as actual lookahead leaks unless explicitly leak-tagged.',
                'fatal' => false,
            ];
        }
        if ($verdict === 'MIXED_DATA_AND_STRATEGY_FAILURE' || $verdict === 'STRATEGY_ROBUSTNESS_FAILURE_CONFIRMED') {
            $items[] = [
                'reason_code' => 'WS_BT_C30_STRATEGY_ROBUSTNESS_FAILURE_CONFIRMED',
                'message' => 'Clean evaluable rows still show bad-month or negative-regime concentration after missing path rows are separated.',
                'fatal' => false,
            ];
        }
        return $items;
    }

    private function blocked(string $status, string $reasonCode, string $message, string $inputPath, string $expectedHash, ?string $actualHash, $c29Status, string $outputPath, string $createdAt, array $extra = []): array
    {
        $artifact = $this->baseArtifact($inputPath, $expectedHash, $actualHash, $c29Status, $createdAt);
        $artifact['status'] = $status;
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['attribution_verdict'] = 'INSUFFICIENT_DIAGNOSTIC_DATA';
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
            'expected_c29_hash' => $expectedHash,
            'actual_c29_hash' => $actualHash,
            'c29_hash_match' => $actualHash !== null && $actualHash === $expectedHash,
            'c29_status' => $c29Status,
            'attribution_verdict' => 'INSUFFICIENT_DIAGNOSTIC_DATA',
            'production_ready' => 0,
        ];
    }

    private function baseArtifact(string $inputPath, string $expectedHash, ?string $actualHash, $c29Status, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C30_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c29_artifact' => $inputPath,
            'expected_c29_hash' => $expectedHash,
            'actual_c29_hash' => $actualHash,
            'c29_hash_match' => $actualHash !== null && $actualHash === $expectedHash,
            'c29_status' => $c29Status,
            'c29_artifact_hash' => null,
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
            'classification_summary' => [
                'total_oos_pick_rows' => 0,
                'reported_lookahead_violation_count' => 0,
                'actual_lookahead_violation_count' => 0,
                'selection_leak_count' => 0,
                'missing_path_count' => 0,
                'non_evaluable_pick_count' => 0,
                'clean_evaluable_pick_count' => 0,
            ],
            'clean_metrics' => [
                'clean_evaluated_picks_count' => 0,
                'clean_avg_ret_net' => null,
                'clean_median_ret_net' => null,
                'clean_p25_ret_net' => null,
                'clean_win_rate' => null,
                'clean_month_win_rate_min' => null,
                'clean_month_avg_ret_net_min' => null,
            ],
            'bad_month_summary' => [],
            'source_branch_summary' => [],
            'ticker_failure_summary' => [],
            'missing_path_rows' => [],
            'actual_lookahead_violation_rows' => [],
            'selection_leak_rows' => [],
            'diagnostics' => [],
            'attribution_verdict' => 'INSUFFICIENT_DIAGNOSTIC_DATA',
            'safety_boundaries' => [
                'FAILURE_ATTRIBUTION_ONLY' => true,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C29_MUTATION' => true,
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

    private function reportedLookaheadViolationCount(array $c29, array $rows): int
    {
        if (is_numeric($c29['lookahead_violation_count'] ?? null)) {
            return (int) $c29['lookahead_violation_count'];
        }
        return count(array_filter($rows, function (array $row): bool {
            return ($row['lookahead_safe'] ?? true) !== true;
        }));
    }

    private function compactRows(array $rows): array
    {
        return array_values(array_map(function (array $row): array {
            return [
                'trade_month' => $row['trade_month'] ?? null,
                'trade_date' => $row['trade_date'] ?? null,
                'ticker' => $row['ticker'] ?? null,
                'param_id' => $row['param_id'] ?? null,
                'row_code' => $row['row_code'] ?? null,
                'selected_source_code' => $row['selected_source_code'] ?? null,
                'bucket_code' => $row['bucket_code'] ?? null,
                'profile_ret_net' => $row['profile_ret_net'] ?? null,
                'lookahead_safe' => $row['lookahead_safe'] ?? null,
                'lookahead_violation_reason' => $row['lookahead_violation_reason'] ?? null,
                'raw_ohlc_validated_flag' => $row['raw_ohlc_validated_flag'] ?? null,
                'missing_path_data_flag' => $row['missing_path_data_flag'] ?? null,
                'missing_path_reason_code' => $row['missing_path_reason_code'] ?? null,
                'future_path_price_used_for_selection' => $row['future_path_price_used_for_selection'] ?? null,
                'profile_ret_net_used_for_selection' => $row['profile_ret_net_used_for_selection'] ?? null,
                'derived_mfe_mae_used_for_execution' => $row['derived_mfe_mae_used_for_execution'] ?? null,
            ];
        }, $rows));
    }

    private function rowKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            (string) ($row['ticker'] ?? ''),
            (string) ($row['param_id'] ?? ''),
            (string) ($row['selected_source_code'] ?? ''),
            (string) ($row['profile_ret_net'] ?? ''),
        ]);
    }

    private function branchCode(array $row): string
    {
        $branch = strtoupper(trim((string) ($row['selected_source_code'] ?? '')));
        return $branch !== '' ? $branch : 'UNKNOWN';
    }

    private function dominantValue(array $rows, string $key): ?string
    {
        $counts = [];
        foreach ($rows as $row) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value === '') {
                continue;
            }
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        if ($counts === []) {
            return null;
        }
        arsort($counts, SORT_NUMERIC);
        return (string) array_key_first($counts);
    }

    private function values(array $rows, string $key): array
    {
        $values = [];
        foreach ($rows as $row) {
            $n = $this->num($row[$key] ?? null);
            if ($n !== null) {
                $values[] = $n;
            }
        }
        return $values;
    }

    private function avg(array $values): ?float
    {
        if ($values === []) {
            return null;
        }
        return array_sum($values) / count($values);
    }

    private function median(array $values): ?float
    {
        return $this->percentile($values, 0.5);
    }

    private function percentile(array $values, float $p): ?float
    {
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        if ($count === 1) {
            return (float) $values[0];
        }
        $pos = ($count - 1) * $p;
        $lower = (int) floor($pos);
        $upper = (int) ceil($pos);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $pos - $lower;
        return (float) ($values[$lower] * (1 - $weight) + $values[$upper] * $weight);
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C30 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
