<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC35IsRobustnessRedesignDiagnosticService
{
    public const RUN_CODE = 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC';
    public const ARTIFACT_TYPE = 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC';
    public const DEFAULT_C34_ARTIFACT = 'storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json';
    public const DEFAULT_EXPECTED_C34_HASH = '1dcf355095334796c2f4558823a1882e71e3ed30';
    public const DEFAULT_IS_EVIDENCE_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C34_STATUS = 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED';
    public const PROMPT_ALIAS_C34_STATUS = 'C34_BAD_MONTH_ROBUSTNESS_AFTER_C33_COMPLETED';
    public const EXPECTED_C34_CONCLUSION = 'C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS';

    public function execute(
        string $c34Artifact = self::DEFAULT_C34_ARTIFACT,
        string $expectedC34Hash = self::DEFAULT_EXPECTED_C34_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c34Artifact = trim($c34Artifact) !== '' ? trim($c34Artifact) : self::DEFAULT_C34_ARTIFACT;
        $expectedC34Hash = trim($expectedC34Hash) !== '' ? trim($expectedC34Hash) : self::DEFAULT_EXPECTED_C34_HASH;
        $from = trim($from) !== '' ? trim($from) : self::DEFAULT_FROM;
        $to = trim($to) !== '' ? trim($to) : self::DEFAULT_TO;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        $isEvidenceArtifact = (string) ($options['is_evidence_artifact'] ?? self::DEFAULT_IS_EVIDENCE_ARTIFACT);

        $artifact = $this->baseArtifact($c34Artifact, $expectedC34Hash, null, null, null, $from, $to, $createdAt, $isEvidenceArtifact);

        if (! is_file($c34Artifact)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_MISSING_C34_ARTIFACT',
                'WS_BT_C35_C34_ARTIFACT_MISSING',
                'C35 requires the locked C34 bad-month robustness diagnostic artifact, but the file is missing.',
                $outputPath,
                ['input_c34_artifact' => $c34Artifact]
            );
        }

        $c34 = json_decode((string) file_get_contents($c34Artifact), true);
        if (! is_array($c34)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_MISSING_C34_ARTIFACT',
                'WS_BT_C35_C34_ARTIFACT_UNREADABLE',
                'C34 artifact is not readable JSON.',
                $outputPath,
                ['input_c34_artifact' => $c34Artifact]
            );
        }

        $actualC34Hash = $this->stableHash($c34);
        $c34Conclusion = $this->c34Conclusion($c34);
        $artifact = $this->baseArtifact(
            $c34Artifact,
            $expectedC34Hash,
            $actualC34Hash,
            $c34['status'] ?? null,
            $c34Conclusion,
            $from,
            $to,
            $createdAt,
            $isEvidenceArtifact
        );
        $artifact['source_c34_problem_statement'] = $this->sourceC34ProblemStatement($c34);

        if ($actualC34Hash !== $expectedC34Hash) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_C34_HASH_MISMATCH',
                'WS_BT_C35_C34_ARTIFACT_HASH_MISMATCH',
                'C34 artifact stable hash does not match the expected locked hash.',
                $outputPath,
                ['c34_artifact_hash_field' => $c34['artifact_hash'] ?? null]
            );
        }

        if (! in_array((string) ($c34['status'] ?? ''), [self::EXPECTED_C34_STATUS, self::PROMPT_ALIAS_C34_STATUS], true)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_UNEXPECTED_C34_STATUS',
                'WS_BT_C35_UNEXPECTED_C34_STATUS',
                'C35 requires a completed C34 bad-month robustness diagnostic artifact.',
                $outputPath,
                ['expected_c34_status' => self::EXPECTED_C34_STATUS, 'accepted_alias' => self::PROMPT_ALIAS_C34_STATUS]
            );
        }

        if ($c34Conclusion !== self::EXPECTED_C34_CONCLUSION) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_UNEXPECTED_C34_CONCLUSION',
                'WS_BT_C35_UNEXPECTED_C34_CONCLUSION',
                'C35 requires C34 to confirm bad-month robustness failure after C33 data-path pass.',
                $outputPath,
                ['expected_c34_conclusion' => self::EXPECTED_C34_CONCLUSION]
            );
        }

        if (($c34['production_ready'] ?? false) !== false && (int) ($c34['production_ready'] ?? 0) !== 0) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_UNEXPECTED_C34_STATUS',
                'WS_BT_C35_C34_PRODUCTION_READY_UNEXPECTED',
                'C35 requires C34 production_ready=false before IS-only redesign diagnostic.',
                $outputPath,
                ['expected_production_ready' => false]
            );
        }

        if (! $this->validPeriod($from, $to)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_INVALID_IS_PERIOD',
                'WS_BT_C35_INVALID_IS_PERIOD',
                'C35 requires a valid IS period where from <= to.',
                $outputPath,
                ['from' => $from, 'to' => $to]
            );
        }

        if ($this->touchesOos($from, $to)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'WS_BT_C35_IS_PERIOD_TOUCHES_OOS_RESERVED',
                'C35 is IS-only and rejects runtime periods that touch the reserved OOS window.',
                $outputPath,
                ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM]
            );
        }

        if ($isEvidenceArtifact === '' || ! is_file($isEvidenceArtifact)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C35_IS_EVIDENCE_ARTIFACT_MISSING',
                'C35 requires IS diagnostic rows from an existing IS artifact; no IS evidence artifact is available.',
                $outputPath,
                ['is_evidence_artifact' => $isEvidenceArtifact]
            );
        }

        $source = json_decode((string) file_get_contents($isEvidenceArtifact), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked(
                $artifact,
                'C35_BLOCKED_MISSING_IS_EVIDENCE',
                'WS_BT_C35_IS_EVIDENCE_ROWS_MISSING',
                'C35 requires pick_diagnostic_rows from an IS artifact; the available artifact does not contain usable rows.',
                $outputPath,
                ['is_evidence_artifact' => $isEvidenceArtifact]
            );
        }

        $allRows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21Rows = $this->targetRows($allRows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Rows = $this->targetRows($allRows, 'G16', 'next_open_delay_after_close_signal');
        $months = $this->uniqueValues($allRows, 'trade_month');

        $artifact['is_evidence_summary'] = [
            'source' => $isEvidenceArtifact,
            'total_rows' => count($allRows),
            'g21_rows' => count($g21Rows),
            'g16_rows' => count($g16Rows),
            'months_covered' => count($months),
            'evidence_available' => count($allRows) > 0,
        ];

        if (count($allRows) === 0 || (count($g21Rows) === 0 && count($g16Rows) === 0)) {
            $artifact['status'] = 'C35_INSUFFICIENT_IS_DIAGNOSTIC_DATA';
            $artifact['diagnostic_conclusion'] = 'C35_IS_EVIDENCE_INSUFFICIENT_FOR_REDESIGN';
            $artifact['next_step_recommendation'] = 'C36_BLOCKED_UNTIL_IS_EVIDENCE_AVAILABLE';
            $artifact['diagnostics'][] = [
                'reason_code' => 'WS_BT_C35_INSUFFICIENT_IS_DIAGNOSTIC_DATA',
                'message' => 'C35 found an IS artifact but not enough G21/G16 target branch rows for redesign evidence.',
                'fatal' => false,
            ];
            return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $g21Summary = $this->branchSummary($g21Rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16Summary = $this->branchSummary($g16Rows, 'G16', 'next_open_delay_after_close_signal');
        $artifact['g21_is_summary'] = $g21Summary;
        $artifact['g16_is_summary'] = $g16Summary;
        $artifact['is_bad_month_like_summary'] = $this->badMonthLikeSummary(array_merge($g21Rows, $g16Rows));
        $artifact['is_branch_month_matrix'] = array_merge(
            $this->branchMonthMatrix($g21Rows, 'G21'),
            $this->branchMonthMatrix($g16Rows, 'G16')
        );
        $artifact['is_ticker_failure_cluster'] = array_merge(
            $this->tickerFailureCluster($g21Rows, 'G21'),
            $this->tickerFailureCluster($g16Rows, 'G16')
        );
        $artifact['exit_reason_distribution'] = array_merge(
            $this->exitReasonDistribution($g21Rows, 'G21'),
            $this->exitReasonDistribution($g16Rows, 'G16')
        );
        $artifact['gap_open_damage_summary'] = array_merge(
            $this->damageSummary($g21Rows, 'G21', 'fallback_damage_vs_r09'),
            $this->damageSummary($g16Rows, 'G16', 'delay_damage_vs_r09')
        );
        $artifact['redesign_hypotheses'] = $this->redesignHypotheses($g21Summary, $g16Summary, $artifact['is_bad_month_like_summary']);
        $artifact['diagnostic_conclusion'] = $this->diagnosticConclusion($g21Summary, $g16Summary, $artifact['is_bad_month_like_summary']);
        $artifact['next_step_recommendation'] = $this->nextStepRecommendation($g21Summary, $g16Summary, $artifact['diagnostic_conclusion']);
        $artifact['status'] = 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED';
        $artifact['diagnostics'] = array_merge($artifact['diagnostics'], $this->completedDiagnostics($artifact));

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(
        string $inputC34Path,
        string $expectedC34Hash,
        ?string $actualC34Hash,
        $c34Status,
        $c34Conclusion,
        string $from,
        string $to,
        string $createdAt,
        string $isEvidenceArtifact
    ): array {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C35_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c34_artifact' => $inputC34Path,
            'expected_c34_hash' => $expectedC34Hash,
            'actual_c34_hash' => $actualC34Hash,
            'c34_hash_match' => $actualC34Hash !== null && $actualC34Hash === $expectedC34Hash,
            'c34_status' => $c34Status,
            'c34_final_conclusion' => $c34Conclusion,
            'is_period' => [
                'from' => $from,
                'to' => $to,
                'oos_reserved_from' => self::OOS_RESERVED_FROM,
                'oos_reserved_to' => self::OOS_RESERVED_TO,
                'oos_data_used_for_tuning' => false,
            ],
            'source_c34_problem_statement' => [
                'target_branches' => ['G21', 'G16'],
                'bad_months_oos_for_context_only' => [],
                'g21_c34_class' => null,
                'g16_c34_class' => null,
            ],
            'is_evidence_summary' => [
                'source' => $isEvidenceArtifact,
                'total_rows' => 0,
                'g21_rows' => 0,
                'g16_rows' => 0,
                'months_covered' => 0,
                'evidence_available' => false,
            ],
            'g21_is_summary' => $this->emptyBranchSummary('G21'),
            'g16_is_summary' => $this->emptyBranchSummary('G16'),
            'is_bad_month_like_summary' => [],
            'is_branch_month_matrix' => [],
            'is_ticker_failure_cluster' => [],
            'exit_reason_distribution' => [],
            'gap_open_damage_summary' => [],
            'redesign_hypotheses' => [],
            'diagnostic_conclusion' => 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_PENDING',
            'next_step_recommendation' => 'C35_PENDING',
            'diagnostics' => [
                [
                    'reason_code' => 'WS_BT_C35_SOURCE_TRUTH_COMPATIBILITY_NOTE',
                    'message' => 'C35 locks the repository C34 artifact path/status from source of truth. The prompt alias status is accepted only as a compatibility alias; no OOS tuning is allowed.',
                    'fatal' => false,
                ],
            ],
            'safety_boundaries' => [
                'IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC' => true,
                'C34_ARTIFACT_HASH_LOCK' => true,
                'C34_BAD_MONTHS_CONTEXT_ONLY' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_CANDIDATE_RESELECTION' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C34_MUTATION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
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

    private function c34Conclusion(array $c34): ?string
    {
        $value = $c34['final_conclusion'] ?? ($c34['diagnostic_conclusion'] ?? ($c34['bad_month_robustness_status'] ?? null));
        return $value === null || $value === '' ? null : (string) $value;
    }

    private function sourceC34ProblemStatement(array $c34): array
    {
        $badMonths = [];
        foreach ($c34['bad_month_diagnostic_rows'] ?? [] as $row) {
            if (is_array($row) && isset($row['trade_month'])) {
                $badMonths[] = (string) $row['trade_month'];
            }
        }
        $badMonths = array_values(array_unique($badMonths));
        sort($badMonths);

        $classMap = [];
        foreach ($c34['branch_robustness_rows'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $source = (string) ($row['selected_source_code'] ?? '');
            if ($source !== '') {
                $classMap[$source] = $row['branch_failure_class'] ?? null;
            }
        }

        return [
            'target_branches' => ['G21', 'G16'],
            'bad_months_oos_for_context_only' => $badMonths,
            'g21_c34_class' => $classMap['G21'] ?? null,
            'g16_c34_class' => $classMap['G16'] ?? null,
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

    private function emptyBranchSummary(string $source): array
    {
        return [
            'selected_source_code' => $source,
            'count' => 0,
            'avg_ret_net' => null,
            'median_ret_net' => null,
            'p25_ret_net' => null,
            'win_rate' => null,
            'month_win_rate_min' => null,
            'month_avg_ret_net_min' => null,
            'bad_month_like_count' => 0,
            'dominant_exit_reason' => null,
            'dominant_delay_damage_mode' => null,
            'dominant_failure_mode' => null,
            'is_weakness_confirmed' => false,
        ];
    }

    private function branchSummary(array $rows, string $source, string $bucket): array
    {
        if (count($rows) === 0) {
            return array_replace($this->emptyBranchSummary($source), ['bucket_code' => $bucket]);
        }
        $metrics = $this->metrics($rows);
        $exitReason = $this->mode($rows, 'profile_exit_reason');
        $damage = $this->damageSummary($rows, $source, $source === 'G16' ? 'delay_damage_vs_r09' : 'fallback_damage_vs_r09');
        $damageMode = isset($damage[0]) ? $damage[0]['dominant_damage_mode'] : null;
        $weakness = $this->weaknessConfirmed($metrics, $source);

        return [
            'selected_source_code' => $source,
            'bucket_code' => $bucket,
            'count' => $metrics['count'],
            'avg_ret_net' => $metrics['avg_ret_net'],
            'median_ret_net' => $metrics['median_ret_net'],
            'p25_ret_net' => $metrics['p25_ret_net'],
            'win_rate' => $metrics['win_rate'],
            'month_win_rate_min' => $metrics['month_win_rate_min'],
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'],
            'bad_month_like_count' => $metrics['bad_month_like_count'],
            'dominant_exit_reason' => $exitReason,
            'dominant_delay_damage_mode' => $source === 'G16' ? $damageMode : null,
            'dominant_failure_mode' => $this->dominantFailureMode($metrics, $source, $exitReason, $damageMode),
            'is_weakness_confirmed' => $weakness,
        ];
    }

    private function weaknessConfirmed(array $metrics, string $source): bool
    {
        $avg = $metrics['avg_ret_net'];
        $win = $metrics['win_rate'];
        $p25 = $metrics['p25_ret_net'];
        $badMonthCount = (int) $metrics['bad_month_like_count'];
        if ($source === 'G21') {
            return ($avg !== null && $avg < 0.0) || ($win !== null && $win < 0.45) || ($p25 !== null && $p25 < -0.01);
        }
        if ($source === 'G16') {
            return ($avg !== null && $avg < 0.0) || ($win !== null && $win < 0.45) || ($badMonthCount >= 3 && $metrics['month_win_rate_min'] !== null && $metrics['month_win_rate_min'] <= 0.0);
        }
        return false;
    }

    private function dominantFailureMode(array $metrics, string $source, ?string $exitReason, ?string $damageMode): ?string
    {
        if ((int) $metrics['count'] === 0) {
            return null;
        }
        if ($source === 'G21' && $metrics['avg_ret_net'] !== null && $metrics['avg_ret_net'] < 0.0 && $metrics['win_rate'] !== null && $metrics['win_rate'] < 0.45) {
            return $exitReason === 'raw_damage_control_no_profit_d2_exit_d3_open'
                ? 'G21_NO_PROFIT_FALLBACK_NEGATIVE_AVG_LOW_WIN_RATE'
                : 'G21_NO_PROFIT_BRANCH_NEGATIVE_DISTRIBUTION';
        }
        if ($source === 'G16' && (int) $metrics['bad_month_like_count'] > 0) {
            return $damageMode === 'NEGATIVE_DELTA_VS_R09_CLUSTER'
                ? 'G16_NEXT_OPEN_DELAY_DAMAGE_CLUSTER'
                : 'G16_NEXT_OPEN_DELAY_BAD_MONTH_CONCENTRATION';
        }
        return 'NO_DOMINANT_FAILURE_MODE_CONFIRMED';
    }

    private function metrics(array $rows): array
    {
        $values = [];
        $byMonth = [];
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) {
                continue;
            }
            $values[] = $value;
            $month = (string) ($row['trade_month'] ?? 'UNKNOWN');
            $byMonth[$month][] = $value;
        }
        sort($values);
        $count = count($values);
        if ($count === 0) {
            return [
                'count' => 0,
                'avg_ret_net' => null,
                'median_ret_net' => null,
                'p25_ret_net' => null,
                'win_rate' => null,
                'month_win_rate_min' => null,
                'month_avg_ret_net_min' => null,
                'bad_month_like_count' => 0,
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
            'count' => $count,
            'avg_ret_net' => array_sum($values) / $count,
            'median_ret_net' => $this->percentileSorted($values, 0.50),
            'p25_ret_net' => $this->percentileSorted($values, 0.25),
            'win_rate' => $this->winCount($values) / $count,
            'month_win_rate_min' => count($monthWinRates) > 0 ? min($monthWinRates) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => $badMonthLike,
        ];
    }

    private function badMonthLikeSummary(array $rows): array
    {
        $byMonth = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? '');
            $source = (string) ($row['selected_source_code'] ?? '');
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($month === '' || $source === '' || $value === null) {
                continue;
            }
            $byMonth[$month][] = $row;
        }

        $out = [];
        foreach ($byMonth as $month => $monthRows) {
            $metrics = $this->metrics($monthRows);
            if (! ($metrics['win_rate'] !== null && ($metrics['win_rate'] <= 0.0 || $metrics['avg_ret_net'] < 0.0))) {
                continue;
            }
            $losses = array_values(array_filter($monthRows, function (array $row): bool {
                $value = $this->num($row['profile_ret_net'] ?? null);
                return $value !== null && $value < 0.0;
            }));
            $sourceCounts = $this->counts($losses, 'selected_source_code');
            $tickerCounts = $this->counts($losses, 'ticker');
            $dominantBranch = count($sourceCounts) > 0 ? array_key_first($sourceCounts) : null;
            $dominantTicker = count($tickerCounts) > 0 ? array_key_first($tickerCounts) : null;
            $out[] = [
                'trade_month' => $month,
                'count' => $metrics['count'],
                'avg_ret_net' => $metrics['avg_ret_net'],
                'median_ret_net' => $metrics['median_ret_net'],
                'p25_ret_net' => $metrics['p25_ret_net'],
                'win_rate' => $metrics['win_rate'],
                'dominant_branch' => $dominantBranch,
                'dominant_ticker' => $dominantTicker,
                'loss_count' => count($losses),
                'loss_concentration' => $metrics['count'] > 0 ? count($losses) / $metrics['count'] : null,
                'is_bad_month_like' => true,
            ];
        }
        usort($out, function (array $a, array $b): int {
            return strcmp((string) ($a['trade_month'] ?? ''), (string) ($b['trade_month'] ?? ''));
        });
        return $out;
    }

    private function branchMonthMatrix(array $rows, string $source): array
    {
        $byMonth = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? '');
            if ($month !== '') {
                $byMonth[$month][] = $row;
            }
        }
        $out = [];
        foreach ($byMonth as $month => $monthRows) {
            $m = $this->metrics($monthRows);
            $out[] = [
                'trade_month' => $month,
                'selected_source_code' => $source,
                'count' => $m['count'],
                'avg_ret_net' => $m['avg_ret_net'],
                'median_ret_net' => $m['median_ret_net'],
                'p25_ret_net' => $m['p25_ret_net'],
                'win_rate' => $m['win_rate'],
                'bad_month_like' => $m['win_rate'] !== null && ($m['win_rate'] <= 0.0 || $m['avg_ret_net'] < 0.0),
            ];
        }
        usort($out, function (array $a, array $b): int {
            $cmp = strcmp((string) ($a['trade_month'] ?? ''), (string) ($b['trade_month'] ?? ''));
            return $cmp !== 0 ? $cmp : strcmp((string) ($a['selected_source_code'] ?? ''), (string) ($b['selected_source_code'] ?? ''));
        });
        return $out;
    }

    private function tickerFailureCluster(array $rows, string $source): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $ticker = (string) ($row['ticker'] ?? '');
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($ticker === '' || $value === null || $value >= 0.0) {
                continue;
            }
            $groups[$ticker][] = $row;
        }
        $out = [];
        foreach ($groups as $ticker => $tickerRows) {
            $m = $this->metrics($tickerRows);
            $months = $this->uniqueValues($tickerRows, 'trade_month');
            $out[] = [
                'selected_source_code' => $source,
                'ticker' => $ticker,
                'loss_count' => $m['count'],
                'avg_loss_ret_net' => $m['avg_ret_net'],
                'p25_loss_ret_net' => $m['p25_ret_net'],
                'months_count' => count($months),
                'months' => $months,
            ];
        }
        usort($out, function (array $a, array $b): int {
            if ((int) $a['loss_count'] === (int) $b['loss_count']) {
                return strcmp((string) $a['ticker'], (string) $b['ticker']);
            }
            return (int) $b['loss_count'] <=> (int) $a['loss_count'];
        });
        return array_slice($out, 0, 20);
    }

    private function exitReasonDistribution(array $rows, string $source): array
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
                'selected_source_code' => $source,
                'profile_exit_reason' => $reason,
                'count' => $m['count'],
                'avg_ret_net' => $m['avg_ret_net'],
                'win_rate' => $m['win_rate'],
            ];
        }
        usort($out, function (array $a, array $b): int {
            return (int) $b['count'] <=> (int) $a['count'];
        });
        return $out;
    }

    private function damageSummary(array $rows, string $source, string $damageCode): array
    {
        if (count($rows) === 0) {
            return [];
        }
        $deltas = [];
        foreach ($rows as $row) {
            $delta = $this->num($row['delta_vs_raw_r09'] ?? null);
            if ($delta !== null) {
                $deltas[] = $delta;
            }
        }
        sort($deltas);
        $negativeDeltaCount = count(array_filter($deltas, function (float $value): bool {
            return $value < 0.0;
        }));
        $count = count($deltas);
        $mode = $negativeDeltaCount > 0 ? 'NEGATIVE_DELTA_VS_R09_CLUSTER' : 'NO_NEGATIVE_DELTA_VS_R09_CLUSTER';
        return [[
            'selected_source_code' => $source,
            'damage_code' => $damageCode,
            'fields_available' => $count > 0,
            'basis' => 'delta_vs_raw_r09_from_existing_IS_diagnostic_rows',
            'count' => $count,
            'avg_delta_vs_raw_r09' => $count > 0 ? array_sum($deltas) / $count : null,
            'median_delta_vs_raw_r09' => $count > 0 ? $this->percentileSorted($deltas, 0.50) : null,
            'p25_delta_vs_raw_r09' => $count > 0 ? $this->percentileSorted($deltas, 0.25) : null,
            'negative_delta_rate_vs_raw_r09' => $count > 0 ? $negativeDeltaCount / $count : null,
            'dominant_damage_mode' => $mode,
        ]];
    }

    private function redesignHypotheses(array $g21, array $g16, array $badMonths): array
    {
        $out = [];
        if (($g21['is_weakness_confirmed'] ?? false) === true) {
            $out[] = [
                'hypothesis_code' => 'C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK',
                'source_branch' => 'G21',
                'is_support_level' => $this->supportLevel($g21, 'G21'),
                'supporting_is_metrics' => $this->supportingMetrics($g21),
                'risk' => 'G21 no-rule-profit fallback branch has weak IS distribution and can drag aggregate return before any OOS evidence is considered.',
                'blocked_reason' => null,
                'recommended_next_diagnostic' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_G21_FALLBACK_NO_PROFIT_BRANCH',
            ];
            if (($g21['dominant_exit_reason'] ?? null) === 'raw_damage_control_no_profit_d2_exit_d3_open') {
                $out[] = [
                    'hypothesis_code' => 'C35_HYP_G21_FALLBACK_EXIT_TOO_LATE',
                    'source_branch' => 'G21',
                    'is_support_level' => $this->supportLevel($g21, 'G21'),
                    'supporting_is_metrics' => $this->supportingMetrics($g21),
                    'risk' => 'Dominant G21 no-profit fallback exit appears in the losing branch; C36 should test an IS-controlled earlier fallback alternative without touching OOS.',
                    'blocked_reason' => null,
                    'recommended_next_diagnostic' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_G21_FALLBACK_EXIT_TIMING',
                ];
            }
        }
        if (($g16['is_weakness_confirmed'] ?? false) === true) {
            $out[] = [
                'hypothesis_code' => 'C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE',
                'source_branch' => 'G16',
                'is_support_level' => $this->supportLevel($g16, 'G16'),
                'supporting_is_metrics' => $this->supportingMetrics($g16),
                'risk' => 'G16 next-open-delay branch has IS bad-month-like concentration. Direct gap/open fields may be unavailable, so C36 must keep the test controlled and IS-only.',
                'blocked_reason' => null,
                'recommended_next_diagnostic' => 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_G16_NEXT_OPEN_DELAY_BRANCH',
            ];
        }
        if (count($badMonths) > 0) {
            $out[] = [
                'hypothesis_code' => 'C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER',
                'source_branch' => 'G21_G16',
                'is_support_level' => count($badMonths) >= 3 ? 'MODERATE_IS_SUPPORT' : 'WEAK_IS_SUPPORT',
                'supporting_is_metrics' => [
                    'bad_month_like_count' => count($badMonths),
                    'bad_month_like_months' => array_values(array_map(function (array $row): string {
                        return (string) ($row['trade_month'] ?? '');
                    }, $badMonths)),
                ],
                'risk' => 'IS loss clusters are month-dependent; a branch redesign without regime controls can overfit branch mechanics only.',
                'blocked_reason' => null,
                'recommended_next_diagnostic' => 'C36_IS_REGIME_GATED_CANDIDATE_FORMATION',
            ];
        }
        if (count($out) === 0) {
            $out[] = [
                'hypothesis_code' => 'C35_HYP_INSUFFICIENT_IS_EVIDENCE',
                'source_branch' => 'G21_G16',
                'is_support_level' => 'INSUFFICIENT_IS_SUPPORT',
                'supporting_is_metrics' => [],
                'risk' => 'No supported redesign hypothesis can be formed from available IS evidence.',
                'blocked_reason' => 'INSUFFICIENT_IS_EVIDENCE',
                'recommended_next_diagnostic' => 'C36_EVIDENCE_EXPANSION_DIAGNOSTIC_NO_OOS_TUNING',
            ];
        }
        return $out;
    }

    private function supportLevel(array $summary, string $source): string
    {
        $count = (int) ($summary['count'] ?? 0);
        if ($count === 0) {
            return 'INSUFFICIENT_IS_SUPPORT';
        }
        if ($source === 'G21' && ($summary['avg_ret_net'] ?? null) !== null && $summary['avg_ret_net'] < 0.0 && ($summary['win_rate'] ?? null) !== null && $summary['win_rate'] < 0.45 && (int) ($summary['bad_month_like_count'] ?? 0) >= 3) {
            return 'STRONG_IS_SUPPORT';
        }
        if (($summary['month_win_rate_min'] ?? null) !== null && $summary['month_win_rate_min'] <= 0.0 && (int) ($summary['bad_month_like_count'] ?? 0) >= 3) {
            return 'MODERATE_IS_SUPPORT';
        }
        return 'WEAK_IS_SUPPORT';
    }

    private function supportingMetrics(array $summary): array
    {
        return [
            'count' => $summary['count'] ?? 0,
            'avg_ret_net' => $summary['avg_ret_net'] ?? null,
            'median_ret_net' => $summary['median_ret_net'] ?? null,
            'p25_ret_net' => $summary['p25_ret_net'] ?? null,
            'win_rate' => $summary['win_rate'] ?? null,
            'month_win_rate_min' => $summary['month_win_rate_min'] ?? null,
            'month_avg_ret_net_min' => $summary['month_avg_ret_net_min'] ?? null,
            'bad_month_like_count' => $summary['bad_month_like_count'] ?? 0,
            'dominant_exit_reason' => $summary['dominant_exit_reason'] ?? null,
            'dominant_failure_mode' => $summary['dominant_failure_mode'] ?? null,
        ];
    }

    private function diagnosticConclusion(array $g21, array $g16, array $badMonths): string
    {
        $g21Weak = ($g21['is_weakness_confirmed'] ?? false) === true;
        $g16Weak = ($g16['is_weakness_confirmed'] ?? false) === true;
        if ($g21Weak && $g16Weak) {
            return 'C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED';
        }
        if ($g21Weak) {
            return 'C35_IS_G21_WEAKNESS_CONFIRMED';
        }
        if ($g16Weak) {
            return 'C35_IS_G16_WEAKNESS_CONFIRMED';
        }
        if (count($badMonths) > 0) {
            return 'C35_IS_BAD_MONTH_LIKE_REGIME_CONFIRMED';
        }
        return 'C35_IS_EVIDENCE_INSUFFICIENT_FOR_REDESIGN';
    }

    private function nextStepRecommendation(array $g21, array $g16, string $conclusion): string
    {
        if (($g21['is_weakness_confirmed'] ?? false) === true || ($g16['is_weakness_confirmed'] ?? false) === true) {
            return 'C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION';
        }
        if ($conclusion === 'C35_IS_BAD_MONTH_LIKE_REGIME_CONFIRMED') {
            return 'C36_IS_REGIME_GATED_CANDIDATE_FORMATION';
        }
        return 'C36_EVIDENCE_EXPANSION_DIAGNOSTIC_NO_OOS_TUNING';
    }

    private function completedDiagnostics(array $artifact): array
    {
        return [
            [
                'reason_code' => 'WS_BT_C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED',
                'message' => 'C35 completed IS-only G21/G16 robustness redesign diagnostic using existing IS evidence only.',
                'fatal' => false,
            ],
            [
                'reason_code' => 'WS_BT_C35_NO_OOS_TUNING_ALLOWED',
                'message' => 'C35 did not run OOS proof, did not use OOS returns for tuning, did not create a production catalog, and did not mutate PLAN/CONFIRM.',
                'fatal' => false,
                'extra' => [
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                ],
            ],
            [
                'reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C35_DIAGNOSTIC_CONCLUSION_UNKNOWN'),
                'message' => 'C35 diagnostic conclusion derived from IS evidence only.',
                'fatal' => false,
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
        $artifact['diagnostic_conclusion'] = 'C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_BLOCKED';
        $artifact['next_step_recommendation'] = 'C36_BLOCKED_UNTIL_INPUT_VALIDATED';
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
            'expected_c34_hash' => $artifact['expected_c34_hash'] ?? null,
            'actual_c34_hash' => $artifact['actual_c34_hash'] ?? null,
            'c34_hash_match' => $artifact['c34_hash_match'] ?? false,
            'c34_status' => $artifact['c34_status'] ?? null,
            'c34_final_conclusion' => $artifact['c34_final_conclusion'] ?? null,
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
                'status' => 'C35_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C35 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
                'expected_c34_hash' => $artifact['expected_c34_hash'] ?? null,
                'actual_c34_hash' => $artifact['actual_c34_hash'] ?? null,
                'c34_hash_match' => $artifact['c34_hash_match'] ?? false,
                'c34_status' => $artifact['c34_status'] ?? null,
                'c34_final_conclusion' => $artifact['c34_final_conclusion'] ?? null,
                'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
                'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            ];
        }

        return [
            'status' => $artifact['status'] ?? 'C35_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $artifact['status'] ?? 'C35_OPERATOR_VALIDATION_REQUIRED',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c34_hash' => $artifact['expected_c34_hash'] ?? null,
            'actual_c34_hash' => $artifact['actual_c34_hash'] ?? null,
            'c34_hash_match' => $artifact['c34_hash_match'] ?? false,
            'c34_status' => $artifact['c34_status'] ?? null,
            'c34_final_conclusion' => $artifact['c34_final_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'is_evidence_summary' => $artifact['is_evidence_summary'] ?? [],
            'g21_is_summary' => $artifact['g21_is_summary'] ?? [],
            'g16_is_summary' => $artifact['g16_is_summary'] ?? [],
        ];
    }

    private function uniqueValues(array $rows, string $field): array
    {
        $values = [];
        foreach ($rows as $row) {
            if (isset($row[$field]) && $row[$field] !== '') {
                $values[] = (string) $row[$field];
            }
        }
        $values = array_values(array_unique($values));
        sort($values);
        return $values;
    }

    private function counts(array $rows, string $field): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? '');
            if ($value === '') {
                continue;
            }
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        arsort($counts);
        return $counts;
    }

    private function mode(array $rows, string $field): ?string
    {
        $counts = $this->counts($rows, $field);
        if (count($counts) === 0) {
            return null;
        }
        return (string) array_key_first($counts);
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C35 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
