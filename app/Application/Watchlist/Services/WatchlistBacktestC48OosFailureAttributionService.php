<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC48OosFailureAttributionService
{
    public const RUN_CODE = 'C48_OOS_FAILURE_ATTRIBUTION';
    public const ARTIFACT_TYPE = 'C48_OOS_FAILURE_ATTRIBUTION';
    public const DEFAULT_C47_ARTIFACT = 'storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json';
    public const DEFAULT_EXPECTED_C47_HASH = '1c742e257847752def1f582dc24d6061a4c4e735';
    public const DEFAULT_C47_FILE_SHA1 = '351B0805F43D2B610B6826C4CDE1513B93FF2FE0';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c48-oos-failure-attribution.json';
    public const OOS_FROM = '2025-05-22';
    public const OOS_TO = '2026-05-29';
    public const EXPECTED_C47_STATUS = 'C47_OOS_PROOF_FAILED';
    public const EXPECTED_C47_CONCLUSION = 'C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED';
    public const EXPECTED_C47_NEXT_STEP = 'C48_OOS_FAILURE_ATTRIBUTION_FOR_C44_REFINEMENT';
    public const TARGET_CANDIDATE_CODE = 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA';
    public const BASELINE_CANDIDATE_CODE = 'C44_BASELINE_C39_METADATA_G21_MONTHLY_QUOTA';
    public const MONTHLY_G21_QUOTA = 13;

    /**
     * C48_OOS_FAILURE_ATTRIBUTION_ONLY: OOS rows may be read only after the locked C47 proof failed.
     * NO_OOS_TUNING: no threshold search, no candidate reselection, no production catalog, no OOS proof rerun.
     */
    public function execute(
        string $c47Artifact = self::DEFAULT_C47_ARTIFACT,
        string $expectedC47Hash = self::DEFAULT_EXPECTED_C47_HASH,
        string $from = self::OOS_FROM,
        string $to = self::OOS_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c47Artifact = trim($c47Artifact) !== '' ? trim($c47Artifact) : self::DEFAULT_C47_ARTIFACT;
        $expectedC47Hash = trim($expectedC47Hash) !== '' ? trim($expectedC47Hash) : self::DEFAULT_EXPECTED_C47_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        $artifact = $this->baseArtifact($c47Artifact, $expectedC47Hash, $from, $to, $createdAt);

        if (! is_file($c47Artifact)) {
            return $this->blocked($artifact, 'C48_BLOCKED_MISSING_C47_ARTIFACT', 'WS_BT_C48_C47_ARTIFACT_MISSING', 'C48 requires the locked C47 failed OOS proof artifact.', $outputPath);
        }

        $c47 = json_decode((string) file_get_contents($c47Artifact), true);
        if (! is_array($c47)) {
            return $this->blocked($artifact, 'C48_BLOCKED_MISSING_C47_ARTIFACT', 'WS_BT_C48_C47_ARTIFACT_UNREADABLE', 'C47 artifact is not readable JSON.', $outputPath);
        }

        $actualC47Hash = $this->stableHash($c47);
        $artifact['actual_c47_hash'] = $actualC47Hash;
        $artifact['c47_hash_match'] = $actualC47Hash === $expectedC47Hash;
        $artifact['c47_status'] = $c47['status'] ?? null;
        $artifact['c47_diagnostic_conclusion'] = $c47['diagnostic_conclusion'] ?? null;
        $artifact['c47_next_step_recommendation'] = $c47['next_step_recommendation'] ?? null;

        if ($actualC47Hash !== $expectedC47Hash) {
            return $this->blocked($artifact, 'C48_BLOCKED_C47_HASH_MISMATCH', 'WS_BT_C48_C47_ARTIFACT_HASH_MISMATCH', 'C47 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c47['status'] ?? null) !== self::EXPECTED_C47_STATUS) {
            return $this->blocked($artifact, 'C48_BLOCKED_UNEXPECTED_C47_STATUS', 'WS_BT_C48_UNEXPECTED_C47_STATUS', 'C48 requires the failed C47 OOS proof artifact.', $outputPath);
        }
        if (($c47['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C47_CONCLUSION) {
            return $this->blocked($artifact, 'C48_BLOCKED_UNEXPECTED_C47_CONCLUSION', 'WS_BT_C48_UNEXPECTED_C47_CONCLUSION', 'C47 diagnostic conclusion is not the locked failed refinement conclusion.', $outputPath);
        }
        if (! $this->strictFalse($c47['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C48_BLOCKED_C47_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C48_C47_PRODUCTION_READY_NOT_FALSE', 'C47 production_ready must remain false.', $outputPath);
        }
        if (! $this->strictFalse($this->safetyValue($c47, 'oos_data_used_for_tuning', true))) {
            return $this->blocked($artifact, 'C48_BLOCKED_C47_OOS_TUNING_FLAG_NOT_FALSE', 'WS_BT_C48_C47_OOS_TUNING_FLAG_NOT_FALSE', 'C47 must not have used OOS data for tuning.', $outputPath);
        }
        if (! $this->strictFalse($c47['oos_source_and_selection_audit']['best_of_oos_created'] ?? $this->safetyValue($c47, 'best_of_oos_created', false))) {
            return $this->blocked($artifact, 'C48_BLOCKED_C47_BEST_OF_OOS_FLAG_INVALID', 'WS_BT_C48_C47_BEST_OF_OOS_FLAG_INVALID', 'C47 must not create best-of-OOS.', $outputPath);
        }
        if (! $this->c47SelectionSafetyValid($c47)) {
            return $this->blocked($artifact, 'C48_BLOCKED_C47_SELECTION_SAFETY_INVALID', 'WS_BT_C48_C47_SELECTION_SAFETY_INVALID', 'C47 selection safety contains lookahead, future path, or return-selection violations.', $outputPath);
        }
        if (($c47['next_step_recommendation'] ?? null) !== self::EXPECTED_C47_NEXT_STEP) {
            return $this->blocked($artifact, 'C48_BLOCKED_C47_NEXT_STEP_UNEXPECTED', 'WS_BT_C48_C47_NEXT_STEP_UNEXPECTED', 'C47 next-step recommendation must route to C48 failure attribution.', $outputPath);
        }
        if ($from !== self::OOS_FROM || $to !== self::OOS_TO || ($c47['oos_window']['from'] ?? null) !== self::OOS_FROM || ($c47['oos_window']['to'] ?? null) !== self::OOS_TO) {
            return $this->blocked($artifact, 'C48_BLOCKED_ATTRIBUTION_PERIOD_MISMATCH', 'WS_BT_C48_ATTRIBUTION_PERIOD_MISMATCH', 'C48 attribution period must match the reserved C47 OOS window.', $outputPath);
        }

        $targetRows = array_values(array_filter((array) ($c47['oos_pick_rows'] ?? []), function ($row): bool {
            return is_array($row);
        }));
        $baselineRows = $this->loadBaselineRows($c47, $options, $artifact['not_evaluable_reasons']);

        $sourceC47Summary = $this->sourceC47Summary($c47);
        $artifact['source_c47_summary'] = $sourceC47Summary;
        $artifact['oos_attribution_period'] = [
            'from' => $from,
            'to' => $to,
            'purpose' => 'failure_attribution_only',
            'oos_data_used_for_tuning' => false,
        ];
        $artifact['month_failure_attribution'] = $this->monthFailureAttribution($targetRows, $baselineRows, $c47);
        $branch = $this->branchFailureAttribution($targetRows);
        $artifact['branch_failure_attribution'] = $branch['rows'];
        $artifact['baseline_target_overlap_attribution'] = $this->baselineTargetOverlapAttribution($targetRows, $baselineRows, $artifact['not_evaluable_reasons']);
        $ticker = $this->tickerFailureAttribution($targetRows, $artifact['not_evaluable_reasons']);
        $artifact['ticker_failure_attribution'] = $ticker['rows'];
        $artifact['sector_bucket_failure_attribution'] = $this->sectorBucketFailureAttribution($targetRows, $artifact['not_evaluable_reasons']);
        $artifact['market_regime_failure_attribution'] = $this->marketRegimeFailureAttribution($targetRows, $artifact['not_evaluable_reasons']);
        $artifact['entry_path_failure_attribution'] = $this->entryPathFailureAttribution($targetRows, $artifact['not_evaluable_reasons']);
        $artifact['is_vs_oos_contrast'] = $this->isVsOosContrast($c47, $sourceC47Summary, $artifact['not_evaluable_reasons']);
        $summary = $this->failureAttributionSummary($artifact, $branch['dominant_branch'], $ticker);
        $artifact['failure_attribution_summary'] = $summary;
        $artifact['c49_readiness_decision'] = $this->c49ReadinessDecision($summary);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($c47);
        $artifact['diagnostic_conclusion'] = $summary['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c49_readiness_decision']['c49_recommendation'];
        $artifact['status'] = 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED';
        $artifact['diagnostics'] = $this->completedDiagnostics($artifact, $summary);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            $artifact['status'] = 'C48_OPERATOR_VALIDATION_REQUIRED';
            return $this->result($artifact, $outputPath, (string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), (string) ($write['message'] ?? 'Unable to write C48 artifact.'));
        }

        return $this->result($artifact, $outputPath, 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED', null);
    }

    private function sourceC47Summary(array $c47): array
    {
        $target = (array) ($c47['target_oos_result'] ?? []);
        $comparison = (array) ($c47['comparison_vs_baseline'] ?? []);
        return [
            'candidate_code' => $target['candidate_code'] ?? self::TARGET_CANDIDATE_CODE,
            'monthly_g21_quota' => (int) ($c47['locked_candidate']['monthly_g21_quota'] ?? $c47['oos_source_and_selection_audit']['monthly_g21_quota'] ?? self::MONTHLY_G21_QUOTA),
            'selection_rule' => $c47['locked_candidate']['selection_rule'] ?? 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota',
            'oos_period' => $c47['oos_window'] ?? ['from' => self::OOS_FROM, 'to' => self::OOS_TO],
            'evaluated_picks_count' => (int) ($target['evaluated_picks_count'] ?? 0),
            'avg_ret_net' => $this->num($target['avg_ret_net'] ?? null),
            'median_ret_net' => $this->num($target['median_ret_net'] ?? null),
            'p25_ret_net' => $this->num($target['p25_ret_net'] ?? null),
            'p10_ret_net' => $this->num($target['p10_ret_net'] ?? null),
            'win_rate' => $this->num($target['win_rate'] ?? null),
            'month_win_rate_min' => $this->num($target['month_win_rate_min'] ?? null),
            'month_avg_ret_net_min' => $this->num($target['month_avg_ret_net_min'] ?? null),
            'bad_month_like_count' => (int) ($target['bad_month_like_count'] ?? 0),
            'bad_like_oos_months' => array_values((array) ($target['bad_month_like_months'] ?? [])),
            'failed_gates' => array_values((array) ($c47['oos_gate']['failed_checks'] ?? [])),
            'delta_avg_ret_net_vs_baseline' => $this->num($comparison['delta_avg_ret_net'] ?? null),
            'delta_win_rate_vs_baseline' => $this->num($comparison['delta_win_rate'] ?? null),
            'delta_bad_month_like_count_vs_baseline' => (int) ($comparison['delta_bad_month_like_count'] ?? 0),
            'production_ready' => false,
        ];
    }

    private function monthFailureAttribution(array $targetRows, ?array $baselineRows, array $c47): array
    {
        $months = $this->monthsFromRowsOrSummary($targetRows, (array) ($c47['target_oos_result']['month_summary'] ?? []));
        $targetByMonth = $this->groupBy($targetRows, 'trade_month');
        $baselineByMonth = $baselineRows === null ? [] : $this->groupBy($baselineRows, 'trade_month');
        $baselineSummaries = [];
        foreach ((array) ($c47['baseline_oos_result']['month_summary'] ?? []) as $row) {
            if (is_array($row) && isset($row['trade_month'])) {
                $baselineSummaries[(string) $row['trade_month']] = $row;
            }
        }
        $out = [];
        foreach ($months as $month) {
            $target = $targetByMonth[$month] ?? [];
            $baseline = $baselineByMonth[$month] ?? null;
            $targetMetrics = $this->metrics($target);
            $baselineMetrics = $baseline !== null ? $this->metrics($baseline) : (isset($baselineSummaries[$month]) ? $this->summaryMetrics($baselineSummaries[$month]) : []);
            $out[] = [
                'trade_month' => $month,
                'target_selected_rows' => count($target),
                'baseline_selected_rows' => $baseline !== null ? count($baseline) : ($baselineMetrics['evaluated_picks_count'] ?? null),
                'target_avg_ret_net' => $targetMetrics['avg_ret_net'],
                'baseline_avg_ret_net' => $baselineMetrics['avg_ret_net'] ?? null,
                'target_median_ret_net' => $targetMetrics['median_ret_net'],
                'target_p25_ret_net' => $targetMetrics['p25_ret_net'],
                'target_p10_ret_net' => $targetMetrics['p10_ret_net'],
                'target_win_rate' => $targetMetrics['win_rate'],
                'target_loss_count' => $targetMetrics['loss_count'],
                'target_bad_like_month' => $this->badLike($targetMetrics),
                'target_vs_baseline_delta_avg_ret_net' => $this->delta($targetMetrics['avg_ret_net'], $baselineMetrics['avg_ret_net'] ?? null),
                'target_vs_baseline_delta_win_rate' => $this->delta($targetMetrics['win_rate'], $baselineMetrics['win_rate'] ?? null),
                'branch_mix' => $this->countMix($target, 'selected_source_code'),
                'ticker_loss_cluster' => $this->topLossCluster($target, 'ticker', 5),
                'sector_loss_cluster' => $this->topLossCluster($target, 'sector_code', 5),
                'bucket_loss_cluster' => $this->topLossCluster($target, 'bucket_code', 5),
            ];
        }
        return $out;
    }

    private function branchFailureAttribution(array $rows): array
    {
        $groups = $this->groupBy($rows, 'selected_source_code');
        $totalLosses = max(1, count(array_filter($rows, function (array $row): bool { return $this->num($row['profile_ret_net'] ?? null) !== null && (float) $row['profile_ret_net'] < 0.0; })));
        $branchLossShare = [];
        $out = [];
        foreach ($groups as $code => $branchRows) {
            $metrics = $this->metrics($branchRows);
            $monthly = $this->monthlyDistribution($branchRows);
            $branchLossShare[$code] = ($metrics['loss_count'] ?? 0) / $totalLosses;
            $out[] = [
                'selected_source_code' => $code,
                'row_count' => count($branchRows),
                'avg_ret_net' => $metrics['avg_ret_net'],
                'median_ret_net' => $metrics['median_ret_net'],
                'p25_ret_net' => $metrics['p25_ret_net'],
                'p10_ret_net' => $metrics['p10_ret_net'],
                'win_rate' => $metrics['win_rate'],
                'loss_count' => $metrics['loss_count'],
                'loss_share' => ($metrics['loss_count'] ?? 0) / $totalLosses,
                'bad_month_like_contribution' => count(array_filter($monthly, function (array $month): bool { return (bool) ($month['bad_like'] ?? false); })),
                'monthly_distribution' => $monthly,
                'failure_dominant_branch' => null,
            ];
        }
        usort($out, function (array $a, array $b): int { return strcmp((string) $a['selected_source_code'], (string) $b['selected_source_code']); });
        $dominant = $this->dominantBranch($branchLossShare);
        foreach ($out as &$row) {
            $row['failure_dominant_branch'] = $dominant;
        }
        unset($row);
        return ['rows' => $out, 'dominant_branch' => $dominant];
    }

    private function baselineTargetOverlapAttribution(array $targetRows, ?array $baselineRows, array &$notEvaluable): array
    {
        if ($baselineRows === null) {
            $this->addNotEvaluable($notEvaluable, 'baseline_target_overlap', 'baseline_rows', 'C48_BASELINE_COMPARATOR_NOT_EVALUABLE', 'Baseline comparator row evidence is unavailable; C47 aggregate baseline metrics are still carried forward.');
            return [
                'target_pick_count' => count($targetRows),
                'baseline_pick_count' => null,
                'overlap_pick_count' => null,
                'target_only_pick_count' => null,
                'baseline_only_pick_count' => null,
                'overlap_share_of_target' => null,
                'overlap_share_of_baseline' => null,
                'target_only_avg_ret_net' => null,
                'baseline_only_avg_ret_net' => null,
                'overlap_avg_ret_net' => null,
                'overlap_failure_label' => 'C48_BASELINE_COMPARATOR_NOT_EVALUABLE',
            ];
        }
        $targetMap = $this->rowMap($targetRows);
        $baselineMap = $this->rowMap($baselineRows);
        $overlapKeys = array_values(array_intersect(array_keys($targetMap), array_keys($baselineMap)));
        $targetOnlyKeys = array_values(array_diff(array_keys($targetMap), array_keys($baselineMap)));
        $baselineOnlyKeys = array_values(array_diff(array_keys($baselineMap), array_keys($targetMap)));
        $overlapRows = $this->rowsByKeys($targetMap, $overlapKeys);
        $targetOnlyRows = $this->rowsByKeys($targetMap, $targetOnlyKeys);
        $baselineOnlyRows = $this->rowsByKeys($baselineMap, $baselineOnlyKeys);
        $overlapShare = count($targetRows) > 0 ? count($overlapKeys) / count($targetRows) : null;
        $label = 'C48_BASELINE_TARGET_OVERLAP_EVALUATED';
        if ($overlapShare !== null && $overlapShare >= 0.85 && ($this->metrics($overlapRows)['avg_ret_net'] ?? 0) < 0.0) {
            $label = 'C48_SHARED_CORE_SELECTION_DROVE_OOS_FAILURE';
        } elseif (count($targetOnlyRows) > 0 && ($this->metrics($targetOnlyRows)['avg_ret_net'] ?? 0) < ($this->metrics($baselineOnlyRows)['avg_ret_net'] ?? 0)) {
            $label = 'C48_TARGET_ONLY_PICKS_DROVE_OOS_UNDERPERFORMANCE';
        } elseif ($overlapShare !== null && $overlapShare >= 0.85) {
            $label = 'C48_REFINEMENT_DID_NOT_CREATE_MATERIAL_OOS_SELECTION_DIFFERENCE';
        }
        return [
            'target_pick_count' => count($targetRows),
            'baseline_pick_count' => count($baselineRows),
            'overlap_pick_count' => count($overlapKeys),
            'target_only_pick_count' => count($targetOnlyKeys),
            'baseline_only_pick_count' => count($baselineOnlyKeys),
            'overlap_share_of_target' => $overlapShare,
            'overlap_share_of_baseline' => count($baselineRows) > 0 ? count($overlapKeys) / count($baselineRows) : null,
            'target_only_avg_ret_net' => $this->metrics($targetOnlyRows)['avg_ret_net'],
            'baseline_only_avg_ret_net' => $this->metrics($baselineOnlyRows)['avg_ret_net'],
            'overlap_avg_ret_net' => $this->metrics($overlapRows)['avg_ret_net'],
            'overlap_failure_label' => $label,
        ];
    }

    private function tickerFailureAttribution(array $rows, array &$notEvaluable): array
    {
        if (! $this->fieldAvailable($rows, 'ticker')) {
            $this->addNotEvaluable($notEvaluable, 'ticker_attribution', 'ticker', 'C48_TICKER_ATTRIBUTION_NOT_EVALUABLE', 'Ticker field is not available in C47 OOS rows.');
            return ['rows' => [], 'top_loss_tickers' => [], 'loss_cluster_ticker_share' => null, 'ticker_concentration_failure' => false];
        }
        $groups = $this->groupBy($rows, 'ticker');
        $totalLosses = max(1, count(array_filter($rows, function (array $row): bool { return $this->num($row['profile_ret_net'] ?? null) !== null && (float) $row['profile_ret_net'] < 0.0; })));
        $out = [];
        foreach ($groups as $ticker => $tickerRows) {
            $metrics = $this->metrics($tickerRows);
            $lossShare = ($metrics['loss_count'] ?? 0) / $totalLosses;
            $out[] = [
                'ticker' => $ticker,
                'selected_rows' => count($tickerRows),
                'avg_ret_net' => $metrics['avg_ret_net'],
                'median_ret_net' => $metrics['median_ret_net'],
                'win_rate' => $metrics['win_rate'],
                'loss_count' => $metrics['loss_count'],
                'loss_share' => $lossShare,
                'worst_ret_net' => $metrics['min_ret_net'],
                'months_present' => array_values(array_unique(array_map(function (array $row): string { return $this->rowMonth($row); }, $tickerRows))),
                'branches_present' => array_values(array_unique(array_map(function (array $row): string { return (string) ($row['selected_source_code'] ?? 'UNKNOWN'); }, $tickerRows))),
                'share_of_total_losses' => $lossShare,
            ];
        }
        usort($out, function (array $a, array $b): int {
            if ((float) $a['share_of_total_losses'] === (float) $b['share_of_total_losses']) {
                return ((float) ($a['avg_ret_net'] ?? 0.0) <=> (float) ($b['avg_ret_net'] ?? 0.0));
            }
            return ((float) $b['share_of_total_losses'] <=> (float) $a['share_of_total_losses']);
        });
        $top = array_slice($out, 0, 5);
        $topShare = array_sum(array_map(function (array $row): float { return (float) ($row['share_of_total_losses'] ?? 0.0); }, $top));
        return ['rows' => $out, 'top_loss_tickers' => array_values(array_column($top, 'ticker')), 'loss_cluster_ticker_share' => $topShare, 'ticker_concentration_failure' => $topShare >= 0.50];
    }

    private function sectorBucketFailureAttribution(array $rows, array &$notEvaluable): array
    {
        $fields = ['sector_code', 'sector_name', 'liquidity_bucket', 'volume_bucket', 'volatility_bucket', 'trend_bucket', 'relative_strength_bucket', 'market_extension_control_bucket', 'market_index_roc20_bucket', 'selected_source_code', 'bucket_code', 'param_id', 'row_code'];
        $out = [];
        foreach ($fields as $field) {
            if (! $this->fieldAvailable($rows, $field)) {
                $this->addNotEvaluable($notEvaluable, 'sector_bucket_attribution', $field, 'C48_FIELD_NOT_AVAILABLE_FOR_OOS_ATTRIBUTION', $field.' is not available in C47 OOS rows.');
                continue;
            }
            foreach ($this->groupBy($rows, $field) as $value => $groupRows) {
                $metrics = $this->metrics($groupRows);
                $out[] = [
                    'field_name' => $field,
                    'field_value' => (string) $value,
                    'row_count' => count($groupRows),
                    'avg_ret_net' => $metrics['avg_ret_net'],
                    'median_ret_net' => $metrics['median_ret_net'],
                    'win_rate' => $metrics['win_rate'],
                    'loss_count' => $metrics['loss_count'],
                    'loss_share' => $metrics['loss_share_within_group'],
                    'bad_month_like_contribution' => count(array_filter($this->monthlyDistribution($groupRows), function (array $row): bool { return (bool) ($row['bad_like'] ?? false); })),
                    'months_present' => array_values(array_unique(array_map(function (array $row): string { return $this->rowMonth($row); }, $groupRows))),
                    'failure_label' => ($metrics['avg_ret_net'] !== null && $metrics['avg_ret_net'] < 0.0) ? 'C48_METADATA_BUCKET_NEGATIVE_AVG' : 'C48_METADATA_BUCKET_EVALUATED',
                ];
            }
        }
        return $out;
    }

    private function marketRegimeFailureAttribution(array $rows, array &$notEvaluable): array
    {
        $fields = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio'];
        $out = [];
        foreach ($fields as $field) {
            if (! $this->fieldAvailable($rows, $field)) {
                $this->addNotEvaluable($notEvaluable, 'market_regime_attribution', $field, 'C48_FIELD_NOT_AVAILABLE_FOR_OOS_ATTRIBUTION', $field.' is not available in C47 OOS rows.');
                continue;
            }
            $bucketed = [];
            foreach ($rows as $row) {
                $bucketed[] = array_merge($row, ['_regime_bucket' => $this->regimeBucket($field, $row[$field] ?? null)]);
            }
            foreach ($this->groupBy($bucketed, '_regime_bucket') as $bucket => $groupRows) {
                $metrics = $this->metrics($groupRows);
                $out[] = [
                    'regime_field' => $field,
                    'regime_bucket' => $bucket,
                    'row_count' => count($groupRows),
                    'avg_ret_net' => $metrics['avg_ret_net'],
                    'median_ret_net' => $metrics['median_ret_net'],
                    'win_rate' => $metrics['win_rate'],
                    'loss_count' => $metrics['loss_count'],
                    'loss_share' => $metrics['loss_share_within_group'],
                    'bad_month_like_contribution' => count(array_filter($this->monthlyDistribution($groupRows), function (array $row): bool { return (bool) ($row['bad_like'] ?? false); })),
                    'market_regime_failure' => $metrics['avg_ret_net'] !== null && $metrics['avg_ret_net'] < 0.0,
                    'oos_regime_shift_vs_is' => 'not_evaluable',
                ];
            }
        }
        return $out;
    }

    private function entryPathFailureAttribution(array $rows, array &$notEvaluable): array
    {
        $fields = ['entry_gap', 'next_open_damage', 'intraday_adverse_path', 'profile_exit_reason', 'profile_exit_day_offset', 'bucket_code'];
        $out = [];
        foreach ($fields as $field) {
            if (! $this->fieldAvailable($rows, $field)) {
                $this->addNotEvaluable($notEvaluable, 'entry_path_attribution', $field, 'C48_PATH_ATTRIBUTION_NOT_EVALUABLE', $field.' is not available in C47 OOS rows.');
                continue;
            }
            foreach ($this->groupBy($rows, $field) as $bucket => $groupRows) {
                $metrics = $this->metrics($groupRows);
                $out[] = [
                    'path_field' => $field,
                    'path_bucket' => (string) $bucket,
                    'row_count' => count($groupRows),
                    'avg_ret_net' => $metrics['avg_ret_net'],
                    'median_ret_net' => $metrics['median_ret_net'],
                    'win_rate' => $metrics['win_rate'],
                    'loss_count' => $metrics['loss_count'],
                    'loss_share' => $metrics['loss_share_within_group'],
                    'failure_label' => ($metrics['avg_ret_net'] !== null && $metrics['avg_ret_net'] < 0.0) ? 'C48_PATH_BUCKET_NEGATIVE_AVG' : 'C48_PATH_BUCKET_EVALUATED',
                    'safe_for_selection' => false,
                    'diagnostic_only' => true,
                ];
            }
        }
        return $out;
    }

    private function isVsOosContrast(array $c47, array $sourceSummary, array &$notEvaluable): array
    {
        $isCandidate = $this->loadIsCandidate($c47, $notEvaluable);
        $pairs = [
            'avg_ret_net' => [$isCandidate['avg_ret_net'] ?? null, $sourceSummary['avg_ret_net'] ?? null],
            'win_rate' => [$isCandidate['win_rate'] ?? null, $sourceSummary['win_rate'] ?? null],
            'bad_month_like_count' => [$isCandidate['bad_month_like_count'] ?? null, $sourceSummary['bad_month_like_count'] ?? null],
            'month_avg_ret_net_min' => [$isCandidate['month_avg_ret_net_min'] ?? null, $sourceSummary['month_avg_ret_net_min'] ?? null],
            'month_win_rate_min' => [$isCandidate['month_win_rate_min'] ?? null, $sourceSummary['month_win_rate_min'] ?? null],
            'selected_rows' => [$isCandidate['selected_rows'] ?? null, $sourceSummary['evaluated_picks_count'] ?? null],
        ];
        $out = [];
        foreach ($pairs as $metric => $values) {
            $out[] = [
                'metric_name' => $metric,
                'is_value' => $values[0],
                'oos_value' => $values[1],
                'delta_oos_vs_is' => $this->delta($values[1], $values[0]),
                'interpretation' => ($values[0] === null || $values[1] === null) ? 'C48_IS_OOS_CONTRAST_NOT_EVALUABLE' : (($this->delta($values[1], $values[0]) ?? 0) < 0 ? 'C48_OOS_DETERIORATED_VS_IS' : 'C48_OOS_NOT_LOWER_THAN_IS'),
            ];
        }
        return $out;
    }

    private function failureAttributionSummary(array $artifact, string $dominantBranch, array $ticker): array
    {
        $months = $artifact['month_failure_attribution'];
        $badMonths = array_values(array_filter($months, function (array $row): bool { return (bool) ($row['target_bad_like_month'] ?? false); }));
        usort($badMonths, function (array $a, array $b): int { return ((float) ($a['target_avg_ret_net'] ?? 0.0) <=> (float) ($b['target_avg_ret_net'] ?? 0.0)); });
        $badMonthNames = array_values(array_map(function (array $row): string { return (string) $row['trade_month']; }, $badMonths));
        $initialCluster = array_values(array_intersect(['2025-06', '2025-07', '2025-08', '2025-09', '2025-10'], $badMonthNames));
        $overlap = (array) ($artifact['baseline_target_overlap_attribution'] ?? []);
        $monthCluster = count($initialCluster) >= 4 ? implode(',', $initialCluster) : implode(',', array_slice($badMonthNames, 0, 5));
        $marketRegimeEvaluable = count($artifact['market_regime_failure_attribution']) > 0;
        $entryRows = $artifact['entry_path_failure_attribution'];
        $pathNegative = count(array_filter($entryRows, function (array $row): bool { return ($row['avg_ret_net'] ?? null) !== null && (float) $row['avg_ret_net'] < 0.0; })) > 0;
        $selectionOverlapFailure = in_array(($overlap['overlap_failure_label'] ?? null), ['C48_SHARED_CORE_SELECTION_DROVE_OOS_FAILURE', 'C48_REFINEMENT_DID_NOT_CREATE_MATERIAL_OOS_SELECTION_DIFFERENCE'], true);
        $marketInsufficient = in_array('avg_pass', (array) ($artifact['source_c47_summary']['failed_gates'] ?? []), true) && (int) ($artifact['source_c47_summary']['bad_month_like_count'] ?? 0) >= 5;
        $diagnostic = 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED';
        if ($selectionOverlapFailure) {
            $diagnostic = 'C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED';
        } elseif ($dominantBranch === 'G21' && $marketInsufficient) {
            $diagnostic = 'C48_G21_QUOTA_FRAGILITY_IDENTIFIED';
        } elseif ($pathNegative) {
            $diagnostic = 'C48_POST_ENTRY_PATH_FAILURE_IDENTIFIED';
        }
        return [
            'failure_attribution_completed' => true,
            'dominant_failure_source' => $selectionOverlapFailure ? 'shared_core_selection_and_oos_month_cluster' : 'absolute_oos_performance_failure',
            'dominant_failure_month_cluster' => $monthCluster,
            'worst_oos_month' => $badMonths[0]['trade_month'] ?? null,
            'bad_like_oos_months' => $badMonthNames,
            'consecutive_bad_like_month_cluster' => $initialCluster,
            'dominant_failure_branch' => $dominantBranch,
            'g21_quota_fragility' => $dominantBranch === 'G21' || $dominantBranch === 'BOTH',
            'g21_quota_too_high_diagnostic' => $dominantBranch === 'G21' || $dominantBranch === 'BOTH',
            'g16_core_failure_contribution' => $dominantBranch === 'G16' || $dominantBranch === 'BOTH',
            'branch_refinement_still_promising' => ($artifact['source_c47_summary']['delta_avg_ret_net_vs_baseline'] ?? 0) > 0,
            'market_extension_control_insufficient' => $marketInsufficient,
            'market_regime_failure' => $marketRegimeEvaluable && count(array_filter($artifact['market_regime_failure_attribution'], function (array $row): bool { return (bool) ($row['market_regime_failure'] ?? false); })) > 0,
            'ticker_concentration_failure' => (bool) ($ticker['ticker_concentration_failure'] ?? false),
            'top_loss_tickers' => $ticker['top_loss_tickers'] ?? [],
            'loss_cluster_ticker_share' => $ticker['loss_cluster_ticker_share'] ?? null,
            'single_ticker_dependency_risk' => (bool) ($ticker['ticker_concentration_failure'] ?? false),
            'sector_bucket_failure' => count(array_filter($artifact['sector_bucket_failure_attribution'], function (array $row): bool { return ($row['avg_ret_net'] ?? null) !== null && (float) $row['avg_ret_net'] < 0.0; })) > 0,
            'entry_gap_failure' => false,
            'post_entry_path_failure' => $pathNegative,
            'selection_overlap_failure' => $selectionOverlapFailure,
            'is_oos_generalization_failure' => true,
            'oos_regime_shift_vs_is' => $marketRegimeEvaluable ? 'not_evaluable' : 'not_evaluable',
            'regime_condition_most_associated_with_loss' => null,
            'failure_dominant_branch' => $dominantBranch,
            'diagnostic_conclusion' => $diagnostic,
        ];
    }

    private function c49ReadinessDecision(array $summary): array
    {
        $recommendation = 'C49_IS_REDESIGN_FROM_OOS_FAILURE_ATTRIBUTION_HYPOTHESIS';
        $status = 'C48_FAILURE_ATTRIBUTION_COMPLETED_C49_IS_REDESIGN_RECOMMENDED';
        if ((bool) ($summary['selection_overlap_failure'] ?? false)) {
            $recommendation = 'C49_BROADER_STRATEGY_REDESIGN';
            $status = 'C48_FAILURE_ATTRIBUTION_COMPLETED_C49_BROADER_STRATEGY_REDESIGN_RECOMMENDED';
        } elseif ((bool) ($summary['market_regime_failure'] ?? false) || (bool) ($summary['market_extension_control_insufficient'] ?? false)) {
            $recommendation = 'C49_REGIME_AWARE_IS_DIAGNOSTIC';
            $status = 'C48_FAILURE_ATTRIBUTION_COMPLETED_C49_REGIME_AWARE_IS_DIAGNOSTIC_RECOMMENDED';
        } elseif ((bool) ($summary['post_entry_path_failure'] ?? false)) {
            $recommendation = 'C49_ENTRY_EXIT_PATH_FAILURE_DIAGNOSTIC';
            $status = 'C48_FAILURE_ATTRIBUTION_COMPLETED_C49_ENTRY_EXIT_PATH_DIAGNOSTIC_RECOMMENDED';
        }
        return [
            'decision_status' => $status,
            'c49_recommendation' => $recommendation,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function candidateSafetyAudit(array $c47): array
    {
        $audit = (array) ($c47['oos_source_and_selection_audit'] ?? []);
        return [
            ['candidate_code' => self::TARGET_CANDIDATE_CODE, 'review_layer' => 'c47_hash_lock', 'passed' => true, 'reason_code' => 'C48_C47_HASH_LOCK_PASS', 'message' => 'C47 stable hash matched expected lock.'],
            ['candidate_code' => self::TARGET_CANDIDATE_CODE, 'review_layer' => 'no_oos_tuning', 'passed' => $this->strictFalse($this->safetyValue($c47, 'oos_data_used_for_tuning', true)), 'reason_code' => 'C48_NO_OOS_TUNING_CONFIRMED', 'message' => 'OOS rows are used only for failure attribution diagnostic.'],
            ['candidate_code' => self::TARGET_CANDIDATE_CODE, 'review_layer' => 'selection_rule_reconstruction', 'passed' => (bool) ($audit['selection_rule_reconstruction_pass'] ?? false), 'reason_code' => 'C48_SELECTION_RULE_RECONSTRUCTION_CARRIED_FORWARD', 'message' => 'C47 selection rule reconstruction is carried forward.'],
            ['candidate_code' => self::TARGET_CANDIDATE_CODE, 'review_layer' => 'fixed_quota', 'passed' => (bool) ($audit['fixed_quota_pass'] ?? false), 'reason_code' => 'C48_FIXED_QUOTA_CARRIED_FORWARD', 'message' => 'C47 fixed quota validation is carried forward.'],
            ['candidate_code' => self::TARGET_CANDIDATE_CODE, 'review_layer' => 'selection_safety', 'passed' => $this->c47SelectionSafetyValid($c47), 'reason_code' => 'C48_SELECTION_SAFETY_PASS', 'message' => 'No return/future path/lookahead selection violation is carried forward from C47.'],
            ['candidate_code' => self::TARGET_CANDIDATE_CODE, 'review_layer' => 'candidate_not_production', 'passed' => ! (bool) ($c47['production_ready'] ?? true), 'reason_code' => 'C48_NOT_PRODUCTION_READY', 'message' => 'C48 does not authorize production.'],
        ];
    }

    private function completedDiagnostics(array $artifact, array $summary): array
    {
        $diagnostics = [
            ['reason_code' => 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED', 'message' => 'C48 completed OOS failure attribution without tuning, OOS proof rerun, promotion, or production catalog.', 'fatal' => false],
            ['reason_code' => 'C48_NO_OOS_TUNING_CONFIRMED', 'message' => 'OOS return/path evidence was used for diagnostic attribution only, not candidate selection.', 'fatal' => false],
            ['reason_code' => 'C48_NOT_PRODUCTION_READY', 'message' => 'Locked C44 refinement remains non-production after failed C47 OOS proof.', 'fatal' => false],
        ];
        if ((bool) ($summary['is_oos_generalization_failure'] ?? false)) {
            $diagnostics[] = ['reason_code' => 'C48_OOS_GENERALIZATION_FAILURE_IDENTIFIED', 'message' => 'IS behavior did not generalize to absolute OOS profitability.', 'fatal' => false];
        }
        if ((bool) ($summary['g21_quota_fragility'] ?? false)) {
            $diagnostics[] = ['reason_code' => 'C48_G21_QUOTA_FRAGILITY_IDENTIFIED', 'message' => 'G21 quota/branch contribution is a diagnostic hypothesis for C49 IS-only redesign, not an OOS-tuned quota change.', 'fatal' => false];
        }
        if ((bool) ($summary['selection_overlap_failure'] ?? false)) {
            $diagnostics[] = ['reason_code' => 'C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED', 'message' => 'Target and baseline overlap suggests the C44 refinement did not materially change the failing OOS core.', 'fatal' => false];
        }
        if ((bool) ($summary['market_extension_control_insufficient'] ?? false)) {
            $diagnostics[] = ['reason_code' => 'C48_MARKET_EXTENSION_CONTROL_INSUFFICIENT', 'message' => 'Market extension control did not prevent absolute OOS failure.', 'fatal' => false];
        }
        return $diagnostics;
    }

    private function loadBaselineRows(array $c47, array $options, array &$notEvaluable): ?array
    {
        if (array_key_exists('baseline_rows', $options)) {
            return array_values(array_filter((array) $options['baseline_rows'], function ($row): bool { return is_array($row); }));
        }
        $sourcePath = (string) ($c47['input_oos_source_artifact'] ?? '');
        if ($sourcePath === '' || ! is_file($sourcePath)) {
            $this->addNotEvaluable($notEvaluable, 'baseline_rows', 'input_oos_source_artifact', 'C48_BASELINE_COMPARATOR_NOT_EVALUABLE', 'Input OOS source artifact is unavailable, so baseline rows cannot be reconstructed.');
            return null;
        }
        $source = json_decode((string) file_get_contents($sourcePath), true);
        if (! is_array($source)) {
            $this->addNotEvaluable($notEvaluable, 'baseline_rows', 'input_oos_source_artifact', 'C48_BASELINE_COMPARATOR_NOT_EVALUABLE', 'Input OOS source artifact is not readable JSON.');
            return null;
        }
        $sourceRows = array_values(array_filter((array) ($source['oos_pick_rows'] ?? []), function ($row): bool {
            return is_array($row) && in_array((string) ($row['selected_source_code'] ?? ''), ['G16', 'G21'], true);
        }));
        if (count($sourceRows) === 0) {
            $this->addNotEvaluable($notEvaluable, 'baseline_rows', 'oos_pick_rows', 'C48_BASELINE_COMPARATOR_NOT_EVALUABLE', 'Input OOS source artifact has no G16/G21 rows.');
            return null;
        }
        $g16 = array_values(array_filter($sourceRows, function (array $row): bool { return (string) ($row['selected_source_code'] ?? '') === 'G16'; }));
        $g21 = array_values(array_filter($sourceRows, function (array $row): bool { return (string) ($row['selected_source_code'] ?? '') === 'G21'; }));
        $months = $this->monthsFromRowsOrSummary($sourceRows, []);
        return array_merge($g16, $this->selectMonthlyQuotaByMetadata($g21, $months, self::MONTHLY_G21_QUOTA));
    }

    private function selectMonthlyQuotaByMetadata(array $rows, array $months, int $quota): array
    {
        $byMonth = $this->groupBy($rows, 'trade_month');
        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[$month] ?? [];
            usort($monthRows, function (array $a, array $b): int { return strcmp($this->metadataKey($a), $this->metadataKey($b)); });
            $selected = array_merge($selected, array_slice($monthRows, 0, $quota));
        }
        return $selected;
    }

    private function loadIsCandidate(array $c47, array &$notEvaluable): array
    {
        $path = (string) ($c47['input_c44_artifact'] ?? '');
        if ($path === '' || ! is_file($path)) {
            $this->addNotEvaluable($notEvaluable, 'is_vs_oos_contrast', 'input_c44_artifact', 'C48_IS_CANDIDATE_NOT_EVALUABLE', 'C44 artifact is unavailable; IS vs OOS contrast can only carry OOS values.');
            return [];
        }
        $c44 = json_decode((string) file_get_contents($path), true);
        if (! is_array($c44)) {
            $this->addNotEvaluable($notEvaluable, 'is_vs_oos_contrast', 'input_c44_artifact', 'C48_IS_CANDIDATE_NOT_EVALUABLE', 'C44 artifact is not readable JSON.');
            return [];
        }
        foreach ((array) ($c44['candidate_results'] ?? []) as $candidate) {
            if (is_array($candidate) && (string) ($candidate['candidate_code'] ?? '') === self::TARGET_CANDIDATE_CODE) {
                return $candidate;
            }
        }
        $this->addNotEvaluable($notEvaluable, 'is_vs_oos_contrast', 'candidate_results', 'C48_IS_CANDIDATE_NOT_EVALUABLE', 'Target C44 candidate is not present in C44 artifact.');
        return [];
    }

    private function baseArtifact(string $c47Artifact, string $expectedC47Hash, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C48_PENDING',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c47_artifact' => $c47Artifact,
            'expected_c47_hash' => $expectedC47Hash,
            'expected_c47_file_sha1' => self::DEFAULT_C47_FILE_SHA1,
            'actual_c47_hash' => null,
            'c47_hash_match' => false,
            'c47_status' => null,
            'c47_diagnostic_conclusion' => null,
            'c47_next_step_recommendation' => null,
            'oos_attribution_period' => ['from' => $from, 'to' => $to, 'purpose' => 'failure_attribution_only', 'oos_data_used_for_tuning' => false],
            'source_c47_summary' => [],
            'month_failure_attribution' => [],
            'branch_failure_attribution' => [],
            'baseline_target_overlap_attribution' => [],
            'ticker_failure_attribution' => [],
            'sector_bucket_failure_attribution' => [],
            'market_regime_failure_attribution' => [],
            'entry_path_failure_attribution' => [],
            'is_vs_oos_contrast' => [],
            'failure_attribution_summary' => [],
            'c49_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => null,
            'next_step_recommendation' => null,
            'diagnostics' => [],
            'safety_boundaries' => [
                'C48_OOS_FAILURE_ATTRIBUTION_ONLY' => true,
                'C47_ARTIFACT_HASH_LOCK' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF_RERUN' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_CANDIDATE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C47_MUTATION' => true,
                'NO_C01_TO_C47_ARTIFACT_MUTATION' => true,
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
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C48_BLOCKED_UNTIL_C47_INPUT_VALIDATED';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') {
            $this->writeArtifact($output, $artifact, true);
        }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return [
            'status' => $artifact['status'] ?? null,
            'reason_code' => $reason,
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'] ?? null,
            'production_ready' => 0,
            'expected_c47_hash' => $artifact['expected_c47_hash'] ?? null,
            'actual_c47_hash' => $artifact['actual_c47_hash'] ?? null,
            'c47_hash_match' => $artifact['c47_hash_match'] ?? false,
            'c47_status' => $artifact['c47_status'] ?? null,
            'c47_diagnostic_conclusion' => $artifact['c47_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_c47_summary' => $artifact['source_c47_summary'] ?? [],
            'failure_attribution_summary' => $artifact['failure_attribution_summary'] ?? [],
            'c49_readiness_decision' => $artifact['c49_readiness_decision'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C48 artifact.'];
        }
        return ['ok' => true];
    }

    private function metrics(array $rows): array
    {
        $values = [];
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value !== null && ! (bool) ($row['missing_path_data_flag'] ?? false)) {
                $values[] = $value;
            }
        }
        sort($values);
        $count = count($values);
        $lossCount = count(array_filter($values, function (float $value): bool { return $value < 0.0; }));
        return [
            'row_count' => count($rows),
            'evaluated_picks_count' => $count,
            'avg_ret_net' => $count > 0 ? array_sum($values) / $count : null,
            'median_ret_net' => $this->percentile($values, 0.50),
            'p25_ret_net' => $this->percentile($values, 0.25),
            'p10_ret_net' => $this->percentile($values, 0.10),
            'win_rate' => $count > 0 ? count(array_filter($values, function (float $value): bool { return $value > 0.0; })) / $count : null,
            'loss_count' => $lossCount,
            'loss_share_within_group' => $count > 0 ? $lossCount / $count : null,
            'min_ret_net' => $count > 0 ? min($values) : null,
        ];
    }

    private function summaryMetrics(array $row): array
    {
        return [
            'evaluated_picks_count' => isset($row['evaluated_picks_count']) ? (int) $row['evaluated_picks_count'] : null,
            'avg_ret_net' => $this->num($row['avg_ret_net'] ?? null),
            'win_rate' => $this->num($row['win_rate'] ?? null),
        ];
    }

    private function monthlyDistribution(array $rows): array
    {
        $out = [];
        foreach ($this->groupBy($rows, 'trade_month') as $month => $monthRows) {
            $metrics = $this->metrics($monthRows);
            $out[] = [
                'trade_month' => $month,
                'row_count' => count($monthRows),
                'avg_ret_net' => $metrics['avg_ret_net'],
                'win_rate' => $metrics['win_rate'],
                'loss_count' => $metrics['loss_count'],
                'bad_like' => $this->badLike($metrics),
            ];
        }
        return $out;
    }

    private function badLike(array $metrics): bool
    {
        return ($metrics['avg_ret_net'] !== null && (float) $metrics['avg_ret_net'] < 0.0)
            || ($metrics['win_rate'] !== null && (float) $metrics['win_rate'] <= 0.0);
    }

    private function groupBy(array $rows, string $field): array
    {
        $groups = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $value = $field === 'trade_month' ? $this->rowMonth($row) : (string) ($row[$field] ?? 'UNKNOWN');
            $groups[$value][] = $row;
        }
        ksort($groups, SORT_STRING);
        return $groups;
    }

    private function monthsFromRowsOrSummary(array $rows, array $summary): array
    {
        $months = [];
        foreach ($rows as $row) {
            $month = $this->rowMonth($row);
            if ($month !== '') {
                $months[$month] = true;
            }
        }
        foreach ($summary as $row) {
            if (is_array($row) && isset($row['trade_month'])) {
                $months[(string) $row['trade_month']] = true;
            }
        }
        $months = array_keys($months);
        sort($months, SORT_STRING);
        return $months;
    }

    private function topLossCluster(array $rows, string $field, int $limit): array
    {
        if (! $this->fieldAvailable($rows, $field)) {
            return [];
        }
        $lossRows = array_values(array_filter($rows, function (array $row): bool { return $this->num($row['profile_ret_net'] ?? null) !== null && (float) $row['profile_ret_net'] < 0.0; }));
        $total = max(1, count($lossRows));
        $out = [];
        foreach ($this->groupBy($lossRows, $field) as $value => $groupRows) {
            $out[] = ['value' => $value, 'loss_count' => count($groupRows), 'loss_share' => count($groupRows) / $total, 'avg_ret_net' => $this->metrics($groupRows)['avg_ret_net']];
        }
        usort($out, function (array $a, array $b): int { return ((int) $b['loss_count'] <=> (int) $a['loss_count']); });
        return array_slice($out, 0, $limit);
    }

    private function countMix(array $rows, string $field): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $key = (string) ($row[$field] ?? 'UNKNOWN');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        ksort($counts, SORT_STRING);
        return $counts;
    }

    private function rowMap(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[$this->rowKey($row)] = $row;
        }
        return $out;
    }

    private function rowsByKeys(array $map, array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            if (isset($map[$key])) {
                $out[] = $map[$key];
            }
        }
        return $out;
    }

    private function rowKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            (string) ($row['ticker'] ?? ''),
            (string) ($row['param_id'] ?? ''),
            (string) ($row['row_code'] ?? ''),
            (string) ($row['selected_source_code'] ?? ''),
        ]);
    }

    private function metadataKey(array $row): string
    {
        return implode('|', [$this->rowMonth($row), (string) ($row['trade_date'] ?? ''), (string) ($row['ticker'] ?? ''), sprintf('%010d', (int) ($row['param_id'] ?? 0)), (string) ($row['row_code'] ?? '')]);
    }

    private function regimeBucket(string $field, $value): string
    {
        $num = $this->num($value);
        if ($num === null) {
            return $field.':missing';
        }
        if (in_array($field, ['atr14_pct', 'vol_ratio'], true)) {
            if ($num < 0.02) {
                return $field.':low';
            }
            if ($num < 0.05) {
                return $field.':medium';
            }
            return $field.':high';
        }
        return $num < 0.0 ? $field.':negative' : $field.':non_negative';
    }

    private function dominantBranch(array $shares): string
    {
        if (count($shares) === 0) {
            return 'NOT_EVALUABLE';
        }
        arsort($shares);
        $topBranch = (string) array_key_first($shares);
        $topShare = (float) current($shares);
        if ($topShare >= 0.60) {
            return in_array($topBranch, ['G16', 'G21'], true) ? $topBranch : 'OTHER';
        }
        return 'BOTH';
    }

    private function c47SelectionSafetyValid(array $c47): bool
    {
        $audit = (array) ($c47['oos_source_and_selection_audit'] ?? []);
        return (int) ($audit['target_lookahead_violation_count'] ?? 0) === 0
            && (int) ($audit['target_future_or_return_selection_violation_count'] ?? 0) === 0
            && (int) ($audit['target_missing_path_count'] ?? 0) === 0
            && (bool) ($audit['selection_rule_reconstruction_pass'] ?? true)
            && (bool) ($audit['fixed_quota_pass'] ?? true)
            && (bool) ($audit['return_not_used_for_selection'] ?? true)
            && (bool) ($audit['future_path_not_used_for_selection'] ?? true);
    }

    private function safetyValue(array $artifact, string $key, $default)
    {
        $safety = array_change_key_case((array) ($artifact['safety_boundaries'] ?? []), CASE_LOWER);
        return $safety[strtolower($key)] ?? $default;
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

    private function rowMonth(array $row): string
    {
        return (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
    }

    private function percentile(array $values, float $percentile): ?float
    {
        if (count($values) === 0) {
            return null;
        }
        sort($values);
        $position = (count($values) - 1) * $percentile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        return $lower === $upper ? (float) $values[$lower] : (float) $values[$lower] + (((float) $values[$upper] - (float) $values[$lower]) * ($position - $lower));
    }

    private function delta($target, $baseline): ?float
    {
        $target = $this->num($target);
        $baseline = $this->num($baseline);
        return $target === null || $baseline === null ? null : $target - $baseline;
    }

    private function num($value): ?float
    {
        return $value === null || $value === '' || ! is_numeric($value) ? null : (float) $value;
    }

    private function strictFalse($value): bool
    {
        return $value === false || $value === 0 || $value === '0';
    }

    private function addNotEvaluable(array &$out, string $layer, string $slice, string $reason, string $message): void
    {
        foreach ($out as $row) {
            if (($row['validation_layer'] ?? null) === $layer && ($row['validation_slice'] ?? null) === $slice && ($row['reason_code'] ?? null) === $reason) {
                return;
            }
        }
        $out[] = ['validation_layer' => $layer, 'validation_slice' => $slice, 'reason_code' => $reason, 'message' => $message];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
