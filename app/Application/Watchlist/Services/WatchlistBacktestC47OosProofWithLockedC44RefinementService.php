<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC47OosProofWithLockedC44RefinementService
{
    public const RUN_CODE = 'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT';
    public const ARTIFACT_TYPE = 'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT';
    public const DEFAULT_C46_ARTIFACT = 'storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json';
    public const DEFAULT_EXPECTED_C46_HASH = 'd531dd5b911f55d8824ac514ccc7600470a076bd';
    public const DEFAULT_C46_FILE_SHA1 = '59A80EA0BAE12034F42395EA0605536D9F9B2E5D';
    public const DEFAULT_C44_ARTIFACT = 'storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json';
    public const DEFAULT_EXPECTED_C44_HASH = '606cd3109371b0d99419082daee18ff65f1cd99b';
    public const DEFAULT_C44_FILE_SHA1 = '4A9A7A915DD37278D9F44634C5D08006B310ED71';
    public const DEFAULT_OOS_SOURCE_ARTIFACT = 'storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json';
    public const DEFAULT_EXPECTED_OOS_SOURCE_HASH = 'c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9';
    public const DEFAULT_OOS_SOURCE_FILE_SHA1 = '62744E652235799A38CBCA57F81D2F1C3BE25FF4';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json';
    public const OOS_FROM = '2025-05-22';
    public const OOS_TO = '2026-05-29';
    public const EXPECTED_C46_STATUS = 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED';
    public const EXPECTED_C46_CONCLUSION = 'C46_C45_WARNING_ACCEPTED_FOR_LOCKED_OOS_PROOF';
    public const EXPECTED_C46_NEXT_STEP = 'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT';
    public const EXPECTED_C44_STATUS = 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED';
    public const TARGET_CANDIDATE_CODE = 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA';
    public const BASELINE_CANDIDATE_CODE = 'C44_BASELINE_C39_METADATA_G21_MONTHLY_QUOTA';
    public const MONTHLY_G21_QUOTA = 13;

    private const MIN_EVALUATED_PICKS = 40;
    private const MIN_AVG_RET_NET = 0.0;
    private const MIN_MEDIAN_RET_NET = 0.0;
    private const MIN_P25_RET_NET = -0.03;
    private const MIN_MONTH_WIN_RATE = 0.45;

    public function execute(
        string $c46Artifact = self::DEFAULT_C46_ARTIFACT,
        string $expectedC46Hash = self::DEFAULT_EXPECTED_C46_HASH,
        string $c44Artifact = self::DEFAULT_C44_ARTIFACT,
        string $expectedC44Hash = self::DEFAULT_EXPECTED_C44_HASH,
        string $oosSourceArtifact = self::DEFAULT_OOS_SOURCE_ARTIFACT,
        string $expectedOosSourceHash = self::DEFAULT_EXPECTED_OOS_SOURCE_HASH,
        string $from = self::OOS_FROM,
        string $to = self::OOS_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($c46Artifact, $expectedC46Hash, $c44Artifact, $expectedC44Hash, $oosSourceArtifact, $expectedOosSourceHash, $from, $to, $createdAt);
        if ($from !== self::OOS_FROM || $to !== self::OOS_TO) {
            return $this->blocked($artifact, 'C47_BLOCKED_RESERVED_OOS_WINDOW_MISMATCH', 'WS_BT_C47_RESERVED_OOS_WINDOW_MISMATCH', 'C47 must use the reserved OOS window exactly.', $outputPath);
        }
        if (! is_file($c46Artifact)) {
            return $this->blocked($artifact, 'C47_BLOCKED_MISSING_C46_ARTIFACT', 'WS_BT_C47_C46_ARTIFACT_MISSING', 'C47 requires the locked C46 review artifact.', $outputPath);
        }
        $c46 = json_decode((string) file_get_contents($c46Artifact), true);
        if (! is_array($c46)) {
            return $this->blocked($artifact, 'C47_BLOCKED_MISSING_C46_ARTIFACT', 'WS_BT_C47_C46_ARTIFACT_UNREADABLE', 'C46 artifact is not readable JSON.', $outputPath);
        }
        $actualC46Hash = $this->stableHash($c46);
        $artifact['actual_c46_hash'] = $actualC46Hash;
        $artifact['c46_hash_match'] = $actualC46Hash === $expectedC46Hash;
        $artifact['c46_status'] = $c46['status'] ?? null;
        $artifact['c46_diagnostic_conclusion'] = $c46['diagnostic_conclusion'] ?? null;
        $artifact['c46_next_step_recommendation'] = $c46['next_step_recommendation'] ?? null;
        $artifact['source_c46_summary'] = $this->sourceC46Summary($c46);
        if ($actualC46Hash !== $expectedC46Hash) {
            return $this->blocked($artifact, 'C47_BLOCKED_C46_HASH_MISMATCH', 'WS_BT_C47_C46_ARTIFACT_HASH_MISMATCH', 'C46 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c46['status'] ?? null) !== self::EXPECTED_C46_STATUS) {
            return $this->blocked($artifact, 'C47_BLOCKED_UNEXPECTED_C46_STATUS', 'WS_BT_C47_UNEXPECTED_C46_STATUS', 'C47 requires completed C46 review.', $outputPath);
        }
        if (($c46['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C46_CONCLUSION || ($c46['next_step_recommendation'] ?? null) !== self::EXPECTED_C46_NEXT_STEP) {
            return $this->blocked($artifact, 'C47_BLOCKED_C46_NOT_AUTHORIZED', 'WS_BT_C47_C46_NOT_AUTHORIZED', 'C46 did not authorize this locked C47 OOS proof.', $outputPath);
        }
        $decision = (array) ($c46['review_decision_summary'] ?? []);
        if (($decision['all_review_checks_passed'] ?? false) !== true
            || ($decision['warning_acceptable_for_locked_oos_proof'] ?? false) !== true
            || ($decision['direct_oos_proof_recommended'] ?? false) !== true
            || ($decision['oos_proof_unlocked'] ?? false) !== true
            || ($decision['requires_c47_oos_proof'] ?? false) !== true
            || ! $this->strictFalse($decision['oos_proof_executed'] ?? true)
            || ! $this->strictFalse($c46['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C47_BLOCKED_C46_OOS_AUTHORIZATION_INVALID', 'WS_BT_C47_C46_OOS_AUTHORIZATION_INVALID', 'C46 authorization flags are incomplete or unsafe.', $outputPath);
        }

        if (! is_file($c44Artifact)) {
            return $this->blocked($artifact, 'C47_BLOCKED_MISSING_C44_ARTIFACT', 'WS_BT_C47_C44_ARTIFACT_MISSING', 'C47 requires the locked C44 candidate artifact.', $outputPath);
        }
        $c44 = json_decode((string) file_get_contents($c44Artifact), true);
        if (! is_array($c44)) {
            return $this->blocked($artifact, 'C47_BLOCKED_MISSING_C44_ARTIFACT', 'WS_BT_C47_C44_ARTIFACT_UNREADABLE', 'C44 artifact is not readable JSON.', $outputPath);
        }
        $actualC44Hash = $this->stableHash($c44);
        $artifact['actual_c44_hash'] = $actualC44Hash;
        $artifact['c44_hash_match'] = $actualC44Hash === $expectedC44Hash;
        $artifact['c44_status'] = $c44['status'] ?? null;
        if ($actualC44Hash !== $expectedC44Hash) {
            return $this->blocked($artifact, 'C47_BLOCKED_C44_HASH_MISMATCH', 'WS_BT_C47_C44_ARTIFACT_HASH_MISMATCH', 'C44 stable hash does not match the expected candidate lock.', $outputPath);
        }
        $best = $this->candidateByCode($c44, (string) ($c44['candidate_summary']['best_is_candidate_code'] ?? ''));
        if (($c44['status'] ?? null) !== self::EXPECTED_C44_STATUS
            || ($c44['candidate_summary']['best_is_candidate_code'] ?? null) !== self::TARGET_CANDIDATE_CODE
            || ! is_array($best)
            || ($best['all_required_guards_passed'] ?? false) !== true
            || ($best['advancement_gate']['passed'] ?? false) !== true
            || ($best['selection_rule'] ?? null) !== 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota'
            || (int) ($c44['source_evidence_summary']['monthly_g21_quota'] ?? 0) !== self::MONTHLY_G21_QUOTA
            || ! $this->strictFalse($c44['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C47_BLOCKED_C44_CANDIDATE_LOCK_INVALID', 'WS_BT_C47_C44_CANDIDATE_LOCK_INVALID', 'C44 target rule, quota, guards, or non-production lock is invalid.', $outputPath);
        }
        $artifact['locked_candidate'] = $this->lockedCandidateSummary($best, $c44);

        if (! is_file($oosSourceArtifact)) {
            return $this->blocked($artifact, 'C47_BLOCKED_MISSING_OOS_SOURCE', 'WS_BT_C47_OOS_SOURCE_MISSING', 'C47 requires the frozen C29 OOS row source.', $outputPath);
        }
        $oosSource = json_decode((string) file_get_contents($oosSourceArtifact), true);
        if (! is_array($oosSource)) {
            return $this->blocked($artifact, 'C47_BLOCKED_MISSING_OOS_SOURCE', 'WS_BT_C47_OOS_SOURCE_UNREADABLE', 'C29 OOS source artifact is not readable JSON.', $outputPath);
        }
        $actualOosSourceHash = $this->stableHash($oosSource);
        $artifact['actual_oos_source_hash'] = $actualOosSourceHash;
        $artifact['oos_source_hash_match'] = $actualOosSourceHash === $expectedOosSourceHash;
        if ($actualOosSourceHash !== $expectedOosSourceHash) {
            return $this->blocked($artifact, 'C47_BLOCKED_OOS_SOURCE_HASH_MISMATCH', 'WS_BT_C47_OOS_SOURCE_HASH_MISMATCH', 'Frozen OOS row source stable hash does not match.', $outputPath);
        }
        if (($oosSource['artifact_type'] ?? null) !== 'C29_OOS_PROOF'
            || ($oosSource['oos_window']['from'] ?? null) !== self::OOS_FROM
            || ($oosSource['oos_window']['to'] ?? null) !== self::OOS_TO
            || ($oosSource['oos_proof'] ?? false) !== true
            || ! is_array($oosSource['oos_pick_rows'] ?? null)
            || count($oosSource['oos_pick_rows']) === 0
            || ! $this->strictFalse($oosSource['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C47_BLOCKED_OOS_SOURCE_INVALID', 'WS_BT_C47_OOS_SOURCE_INVALID', 'Frozen OOS row source contract is invalid.', $outputPath);
        }

        $sourceRows = array_values(array_filter($oosSource['oos_pick_rows'], function ($row): bool {
            return is_array($row)
                && in_array((string) ($row['selected_source_code'] ?? ''), ['G16', 'G21'], true)
                && (string) ($row['trade_date'] ?? '') >= self::OOS_FROM
                && (string) ($row['trade_date'] ?? '') <= self::OOS_TO;
        }));
        if (count($sourceRows) === 0) {
            return $this->blocked($artifact, 'C47_BLOCKED_OOS_SOURCE_ROWS_EMPTY', 'WS_BT_C47_OOS_SOURCE_ROWS_EMPTY', 'Frozen OOS source has no G16/G21 rows for C47.', $outputPath);
        }
        $g16 = $this->filterBranch($sourceRows, 'G16');
        $g21 = $this->filterBranch($sourceRows, 'G21');
        $months = $this->uniqueMonths($sourceRows);
        $marketLoad = $this->loadMarketSources($g21, $options);
        if (count($marketLoad['rows']) === 0) {
            return $this->blocked($artifact, 'C47_BLOCKED_PRE_TRADE_SOURCE_UNAVAILABLE', 'WS_BT_C47_PRE_TRADE_SOURCE_UNAVAILABLE', 'C47 could not join exact-date IHSG ROC20 for the locked OOS selection rule.', $outputPath);
        }
        $g21 = $this->enrichRows($g21, $marketLoad['rows']);
        $marketMissing = count(array_filter($g21, function (array $row): bool { return $this->num($row['market_index_roc20'] ?? null) === null; }));
        if ($marketMissing > 0) {
            return $this->blocked($artifact, 'C47_BLOCKED_PRE_TRADE_SOURCE_INCOMPLETE', 'WS_BT_C47_PRE_TRADE_SOURCE_INCOMPLETE', 'C47 requires complete exact-date market_index_roc20 coverage for every OOS G21 row.', $outputPath);
        }

        $baselineG21 = $this->selectMonthlyQuota($g21, $months, self::MONTHLY_G21_QUOTA, 'METADATA');
        $targetG21 = $this->selectMonthlyQuota($g21, $months, self::MONTHLY_G21_QUOTA, 'MARKET_EXTENSION');
        $baselineRows = array_merge($g16, $baselineG21);
        $targetRows = array_merge($g16, $targetG21);
        $baselineMetrics = $this->metrics($baselineRows, $months);
        $targetMetrics = $this->metrics($targetRows, $months);
        $comparison = $this->comparison($targetMetrics, $baselineMetrics);
        $sourceAudit = $this->sourceAudit($sourceRows, $targetRows, $g16, $g21, $months, $marketLoad, $marketMissing);
        $gate = $this->gate($targetMetrics, $sourceAudit);
        $passed = (bool) ($gate['overall_pass'] ?? false);

        $artifact['oos_source_summary'] = [
            'source_artifact' => $oosSourceArtifact,
            'source_status' => $oosSource['status'] ?? null,
            'source_artifact_type' => $oosSource['artifact_type'] ?? null,
            'source_oos_window' => $oosSource['oos_window'] ?? [],
            'source_all_pick_rows' => count($oosSource['oos_pick_rows']),
            'source_g16_g21_rows' => count($sourceRows),
            'source_g16_rows' => count($g16),
            'source_g21_rows' => count($g21),
            'source_months' => count($months),
            'pre_trade_source_mode' => $marketLoad['mode'],
            'pre_trade_source_row_count' => count($marketLoad['rows']),
            'pre_trade_source_error' => $marketLoad['error'],
            'market_index_roc20_missing_count' => $marketMissing,
            'oos_return_used_for_candidate_selection' => false,
        ];
        $artifact['baseline_oos_result'] = array_merge(['candidate_code' => self::BASELINE_CANDIDATE_CODE, 'selected_g21_rows' => count($baselineG21)], $baselineMetrics);
        $artifact['target_oos_result'] = array_merge(['candidate_code' => self::TARGET_CANDIDATE_CODE, 'selected_g21_rows' => count($targetG21)], $targetMetrics);
        $artifact['comparison_vs_baseline'] = $comparison;
        $artifact['oos_source_and_selection_audit'] = $sourceAudit;
        $artifact['oos_gate'] = $gate;
        $artifact['oos_pick_rows'] = $targetRows;
        $artifact['status'] = $passed ? 'C47_OOS_PROOF_PASSED_NOT_PRODUCTION_READY' : 'C47_OOS_PROOF_FAILED';
        $artifact['diagnostic_conclusion'] = $passed
            ? 'C47_LOCKED_C44_REFINEMENT_OOS_PROOF_PASSED'
            : 'C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED';
        $artifact['next_step_recommendation'] = $passed
            ? 'C48_POST_OOS_GOVERNANCE_REVIEW'
            : 'C48_OOS_FAILURE_ATTRIBUTION_FOR_C44_REFINEMENT';
        $artifact['diagnostics'] = $this->gateDiagnostics($gate);
        $artifact['safety_boundaries']['OOS_PROOF_EXECUTED'] = true;
        $artifact['safety_boundaries']['OOS_PROOF_RESULT_USED_FOR_RETUNING'] = false;
        $artifact['safety_boundaries']['OOS_PROOF_RESULT_USED_FOR_CANDIDATE_RESELECTION'] = false;

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function gate(array $metrics, array $audit): array
    {
        $checks = [
            'c46_authorization_pass' => true,
            'c44_candidate_lock_pass' => true,
            'oos_source_hash_pass' => true,
            'reserved_window_pass' => true,
            'selection_rule_reconstruction_pass' => (bool) ($audit['selection_rule_reconstruction_pass'] ?? false),
            'fixed_quota_pass' => (bool) ($audit['fixed_quota_pass'] ?? false),
            'pre_trade_market_field_coverage_pass' => (int) ($audit['market_index_roc20_missing_count'] ?? -1) === 0,
            'min_picks_pass' => (int) ($metrics['evaluated_picks_count'] ?? 0) >= self::MIN_EVALUATED_PICKS,
            'avg_pass' => $this->num($metrics['avg_ret_net'] ?? null) !== null && (float) $metrics['avg_ret_net'] > self::MIN_AVG_RET_NET,
            'median_pass' => $this->num($metrics['median_ret_net'] ?? null) !== null && (float) $metrics['median_ret_net'] >= self::MIN_MEDIAN_RET_NET,
            'p25_pass' => $this->num($metrics['p25_ret_net'] ?? null) !== null && (float) $metrics['p25_ret_net'] >= self::MIN_P25_RET_NET,
            'month_win_rate_pass' => $this->num($metrics['month_win_rate_min'] ?? null) !== null && (float) $metrics['month_win_rate_min'] >= self::MIN_MONTH_WIN_RATE,
            'missing_path_pass' => (int) ($audit['target_missing_path_count'] ?? -1) === 0,
            'lookahead_pass' => (int) ($audit['target_lookahead_violation_count'] ?? -1) === 0,
            'return_not_used_for_selection_pass' => (bool) ($audit['return_not_used_for_selection'] ?? false),
            'future_path_not_used_for_selection_pass' => (bool) ($audit['future_path_not_used_for_selection'] ?? false),
            'production_ready_remains_false_pass' => true,
        ];
        return [
            'thresholds' => [
                'minimum_evaluated_picks' => self::MIN_EVALUATED_PICKS,
                'minimum_avg_ret_net_exclusive' => self::MIN_AVG_RET_NET,
                'minimum_median_ret_net' => self::MIN_MEDIAN_RET_NET,
                'minimum_p25_ret_net' => self::MIN_P25_RET_NET,
                'minimum_month_win_rate' => self::MIN_MONTH_WIN_RATE,
                'maximum_missing_path_count' => 0,
                'maximum_lookahead_violation_count' => 0,
                'threshold_source' => 'LOCKED_C29_OOS_ACCEPTANCE_GATE_REUSED_WITHOUT_RETUNING',
            ],
            'checks' => $checks,
            'failed_checks' => array_keys(array_filter($checks, function (bool $value): bool { return ! $value; })),
            'passed_check_count' => count(array_filter($checks)),
            'failed_check_count' => count(array_filter($checks, function (bool $value): bool { return ! $value; })),
            'overall_pass' => ! in_array(false, $checks, true),
            'production_ready' => false,
        ];
    }

    private function metrics(array $rows, array $scopeMonths): array
    {
        $valid = array_values(array_filter($rows, function (array $row): bool {
            return ! (bool) ($row['missing_path_data_flag'] ?? false) && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
        $values = [];
        $byMonth = [];
        foreach ($valid as $row) {
            $value = (float) $row['profile_ret_net'];
            $values[] = $value;
            $byMonth[$this->rowMonth($row)][] = $value;
        }
        sort($values);
        $monthSummary = [];
        $monthWins = [];
        $monthAvgs = [];
        $badMonths = [];
        foreach ($scopeMonths as $month) {
            $monthValues = $byMonth[$month] ?? [];
            if (count($monthValues) === 0) {
                continue;
            }
            $avg = array_sum($monthValues) / count($monthValues);
            $winRate = $this->winCount($monthValues) / count($monthValues);
            $monthAvgs[] = $avg;
            $monthWins[] = $winRate;
            if ($avg < 0.0 || $winRate <= 0.0) {
                $badMonths[] = $month;
            }
            $monthSummary[] = ['trade_month' => $month, 'evaluated_picks_count' => count($monthValues), 'avg_ret_net' => $avg, 'win_rate' => $winRate];
        }
        $count = count($values);
        return [
            'selected_rows' => count($rows),
            'evaluated_picks_count' => $count,
            'avg_ret_net' => $count > 0 ? array_sum($values) / $count : null,
            'median_ret_net' => $this->percentile($values, 0.50),
            'p25_ret_net' => $this->percentile($values, 0.25),
            'p10_ret_net' => $this->percentile($values, 0.10),
            'win_rate' => $count > 0 ? $this->winCount($values) / $count : null,
            'month_win_rate_min' => count($monthWins) > 0 ? min($monthWins) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => count($badMonths),
            'bad_month_like_months' => $badMonths,
            'month_summary' => $monthSummary,
            'months_required' => count($scopeMonths),
            'months_covered' => count($byMonth),
        ];
    }

    private function comparison(array $target, array $baseline): array
    {
        $out = [];
        foreach (['avg_ret_net', 'median_ret_net', 'p25_ret_net', 'p10_ret_net', 'win_rate', 'month_win_rate_min', 'month_avg_ret_net_min'] as $field) {
            $out['delta_'.$field] = $this->delta($target[$field] ?? null, $baseline[$field] ?? null);
        }
        $out['delta_bad_month_like_count'] = (int) ($target['bad_month_like_count'] ?? 0) - (int) ($baseline['bad_month_like_count'] ?? 0);
        $out['delta_evaluated_picks_count'] = (int) ($target['evaluated_picks_count'] ?? 0) - (int) ($baseline['evaluated_picks_count'] ?? 0);
        return $out;
    }

    private function sourceAudit(array $sourceRows, array $targetRows, array $g16, array $g21, array $months, array $marketLoad, int $marketMissing): array
    {
        $targetMissing = count(array_filter($targetRows, function (array $row): bool { return (bool) ($row['missing_path_data_flag'] ?? false) || $this->num($row['profile_ret_net'] ?? null) === null; }));
        $lookahead = count(array_filter($targetRows, function (array $row): bool { return ($row['lookahead_safe'] ?? false) !== true; }));
        $futureSelection = count(array_filter($targetRows, function (array $row): bool { return (bool) ($row['future_path_price_used_for_selection'] ?? false) || (bool) ($row['profile_ret_net_used_for_selection'] ?? false); }));
        $expectedG21Count = 0;
        $g21Counts = $this->monthCounts($g21);
        foreach ($months as $month) {
            $expectedG21Count += min(self::MONTHLY_G21_QUOTA, (int) ($g21Counts[$month] ?? 0));
        }
        return [
            'source_g16_g21_rows' => count($sourceRows),
            'source_g16_rows' => count($g16),
            'source_g21_rows' => count($g21),
            'source_months' => count($months),
            'target_selected_rows' => count($targetRows),
            'target_selected_g16_rows' => count($g16),
            'target_selected_g21_rows' => count($targetRows) - count($g16),
            'expected_target_selected_g21_rows' => $expectedG21Count,
            'monthly_g21_quota' => self::MONTHLY_G21_QUOTA,
            'fixed_quota_pass' => count($targetRows) - count($g16) === $expectedG21Count,
            'selection_rule_reconstruction_pass' => true,
            'market_source_mode' => $marketLoad['mode'],
            'market_index_roc20_missing_count' => $marketMissing,
            'target_missing_path_count' => $targetMissing,
            'target_lookahead_violation_count' => $lookahead,
            'target_future_or_return_selection_violation_count' => $futureSelection,
            'return_not_used_for_selection' => $futureSelection === 0,
            'future_path_not_used_for_selection' => $futureSelection === 0,
            'oos_result_used_for_retuning' => false,
            'oos_result_used_for_candidate_reselection' => false,
            'best_of_oos_created' => false,
            'production_ready' => false,
        ];
    }

    private function loadMarketSources(array $rows, array $options): array
    {
        if (array_key_exists('pre_trade_source_rows', $options)) {
            $map = [];
            foreach ((array) $options['pre_trade_source_rows'] as $row) {
                if (is_array($row) && isset($row['trade_date'])) {
                    $map['DATE:'.(string) $row['trade_date']] = $row;
                }
            }
            return ['mode' => 'INJECTED_TEST_SOURCE', 'rows' => $map, 'error' => null];
        }
        try {
            if (! Schema::hasTable('market_benchmark_indicators')) {
                return ['mode' => 'SOURCE_NOT_MIGRATED', 'rows' => [], 'error' => 'market_benchmark_indicators unavailable'];
            }
            $dates = [];
            foreach ($rows as $row) {
                $dates[(string) ($row['trade_date'] ?? '')] = true;
            }
            unset($dates['']);
            $map = [];
            foreach (array_chunk(array_keys($dates), 200) as $chunk) {
                $dbRows = DB::table('market_benchmark_indicators')->where('benchmark_code', 'IHSG')->whereIn('trade_date', $chunk)->select(['trade_date', 'roc_20'])->get();
                foreach ($dbRows as $dbRow) {
                    $row = (array) $dbRow;
                    $map['DATE:'.(string) $row['trade_date']] = ['trade_date' => $row['trade_date'], 'market_index_roc20' => $row['roc_20'] ?? null];
                }
            }
            return ['mode' => 'DATABASE_EXACT_SIGNAL_DATE_JOIN', 'rows' => $map, 'error' => null];
        } catch (Throwable $e) {
            return ['mode' => 'DATABASE_JOIN_FAILED', 'rows' => [], 'error' => get_class($e).': '.$e->getMessage()];
        }
    }

    private function enrichRows(array $rows, array $sources): array
    {
        return array_map(function (array $row) use ($sources): array {
            return array_merge($row, $sources['DATE:'.(string) ($row['trade_date'] ?? '')] ?? []);
        }, $rows);
    }

    private function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array
    {
        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[$this->rowMonth($row)][] = $row;
        }
        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[$month] ?? [];
            usort($monthRows, function (array $a, array $b) use ($ranking): int {
                $quality = strcmp($this->qualityKey($a, $ranking), $this->qualityKey($b, $ranking));
                return $quality !== 0 ? $quality : strcmp($this->metadataKey($a), $this->metadataKey($b));
            });
            $selected = array_merge($selected, array_slice($monthRows, 0, $quota));
        }
        return $selected;
    }

    private function qualityKey(array $row, string $ranking): string
    {
        if ($ranking === 'METADATA') {
            return '';
        }
        $roc = $this->num($row['market_index_roc20'] ?? null);
        $rank = $roc === null ? 9 : ($roc < 0.0 ? 1 : ($roc < 0.10 ? 0 : 2));
        return sprintf('%02d|%020.10f', $rank, abs((float) ($roc ?? 999)));
    }

    private function metadataKey(array $row): string
    {
        return implode('|', [$this->rowMonth($row), (string) ($row['trade_date'] ?? ''), (string) ($row['ticker'] ?? ''), sprintf('%010d', (int) ($row['param_id'] ?? 0)), (string) ($row['row_code'] ?? '')]);
    }

    private function baseArtifact(string $c46Path, string $expectedC46, string $c44Path, string $expectedC44, string $oosPath, string $expectedOos, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C47_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'is_only' => false,
            'oos_proof' => true,
            'production_ready' => false,
            'input_c46_artifact' => $c46Path,
            'expected_c46_hash' => $expectedC46,
            'actual_c46_hash' => null,
            'c46_hash_match' => false,
            'expected_c46_file_sha1' => self::DEFAULT_C46_FILE_SHA1,
            'c46_status' => null,
            'c46_diagnostic_conclusion' => null,
            'c46_next_step_recommendation' => null,
            'input_c44_artifact' => $c44Path,
            'expected_c44_hash' => $expectedC44,
            'actual_c44_hash' => null,
            'c44_hash_match' => false,
            'expected_c44_file_sha1' => self::DEFAULT_C44_FILE_SHA1,
            'c44_status' => null,
            'input_oos_source_artifact' => $oosPath,
            'expected_oos_source_hash' => $expectedOos,
            'actual_oos_source_hash' => null,
            'oos_source_hash_match' => false,
            'expected_oos_source_file_sha1' => self::DEFAULT_OOS_SOURCE_FILE_SHA1,
            'oos_window' => ['from' => $from, 'to' => $to],
            'source_c46_summary' => [],
            'locked_candidate' => [],
            'oos_source_summary' => [],
            'baseline_oos_result' => [],
            'target_oos_result' => [],
            'comparison_vs_baseline' => [],
            'oos_source_and_selection_audit' => [],
            'oos_gate' => [],
            'oos_pick_rows' => [],
            'diagnostic_conclusion' => 'C47_OOS_PROOF_PENDING',
            'next_step_recommendation' => 'C47_PENDING',
            'diagnostics' => [['reason_code' => 'WS_BT_C47_LOCKED_ONE_SHOT_OOS_NOTE', 'message' => 'C47 applies only the C44 locked candidate to the reserved OOS window and cannot retune or reselect.', 'fatal' => false]],
            'safety_boundaries' => [
                'C47_OOS_PROOF_WITH_LOCKED_C44_REFINEMENT' => true,
                'C46_ARTIFACT_HASH_LOCK' => true,
                'C44_CANDIDATE_AND_RULE_HASH_LOCK' => true,
                'FROZEN_OOS_SOURCE_HASH_LOCK' => true,
                'RESERVED_OOS_WINDOW_ONLY' => true,
                'ONE_SHOT_LOCKED_OOS_PROOF' => true,
                'NO_OOS_TUNING' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER_SELECTION' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_CANDIDATE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C46_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'oos_return_used_for_candidate_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
            ],
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function sourceC46Summary(array $c46): array
    {
        $decision = (array) ($c46['review_decision_summary'] ?? []);
        return [
            'target_candidate_code' => $c46['source_c45_summary']['target_candidate_code'] ?? null,
            'warning_review_result' => $decision['warning_review_result'] ?? null,
            'candidate_decision' => $decision['candidate_decision'] ?? null,
            'all_review_checks_passed' => $decision['all_review_checks_passed'] ?? false,
            'direct_oos_proof_recommended' => $decision['direct_oos_proof_recommended'] ?? false,
            'oos_proof_unlocked' => $decision['oos_proof_unlocked'] ?? false,
            'oos_proof_executed_before_c47' => $decision['oos_proof_executed'] ?? null,
            'requires_c47_oos_proof' => $decision['requires_c47_oos_proof'] ?? false,
            'candidate_reselected' => $decision['candidate_reselected'] ?? null,
            'production_ready' => false,
        ];
    }

    private function lockedCandidateSummary(array $best, array $c44): array
    {
        return [
            'candidate_code' => $best['candidate_code'] ?? null,
            'selection_rule' => $best['selection_rule'] ?? null,
            'selection_input_fields' => $best['selection_input_fields'] ?? [],
            'monthly_g21_quota' => (int) ($c44['source_evidence_summary']['monthly_g21_quota'] ?? 0),
            'all_required_guards_passed_in_is' => $best['all_required_guards_passed'] ?? false,
            'advancement_gate_passed_in_is' => $best['advancement_gate']['passed'] ?? false,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'candidate_is_not_production' => true,
            'production_ready' => false,
        ];
    }

    private function candidateByCode(array $c44, string $code): ?array
    {
        foreach ((array) ($c44['candidate_results'] ?? []) as $candidate) {
            if (is_array($candidate) && (string) ($candidate['candidate_code'] ?? '') === $code) {
                return $candidate;
            }
        }
        return null;
    }

    private function filterBranch(array $rows, string $branch): array
    {
        return array_values(array_filter($rows, function (array $row) use ($branch): bool { return (string) ($row['selected_source_code'] ?? '') === $branch; }));
    }

    private function uniqueMonths(array $rows): array
    {
        $months = [];
        foreach ($rows as $row) {
            $months[$this->rowMonth($row)] = true;
        }
        unset($months['']);
        $months = array_keys($months);
        sort($months);
        return $months;
    }

    private function monthCounts(array $rows): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $month = $this->rowMonth($row);
            $counts[$month] = ($counts[$month] ?? 0) + 1;
        }
        return $counts;
    }

    private function rowMonth(array $row): string
    {
        return (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
    }

    private function gateDiagnostics(array $gate): array
    {
        $out = [];
        foreach ((array) ($gate['checks'] ?? []) as $check => $passed) {
            if (! $passed) {
                $out[] = ['reason_code' => 'WS_BT_C47_'.strtoupper($check).'_FAILED', 'message' => 'Locked C47 OOS gate check failed: '.$check, 'fatal' => false];
            }
        }
        if (count($out) === 0) {
            $out[] = ['reason_code' => 'C47_LOCKED_OOS_GATE_PASS', 'message' => 'All locked C47 OOS proof gates passed; production readiness remains false pending governance review.', 'fatal' => false];
        }
        return $out;
    }

    private function winCount(array $values): int
    {
        return count(array_filter($values, function ($value): bool { return (float) $value > 0.0; }));
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

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = 'C47_INPUT_LOCK_OR_BOUNDARY_BLOCKED';
        $artifact['next_step_recommendation'] = 'C47_BLOCKED_UNTIL_INPUT_VALIDATED';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') {
            $this->writeArtifact($output, $artifact, true);
        }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! ($write['ok'] ?? false)) {
            $artifact['status'] = 'C47_OPERATOR_VALIDATION_REQUIRED';
            return $this->result($artifact, $output, (string) ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED'), (string) ($write['message'] ?? 'Unable to write C47 artifact.'));
        }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return [
            'status' => $artifact['status'],
            'reason_code' => $reason,
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'] ?? null,
            'production_ready' => 0,
            'expected_c46_hash' => $artifact['expected_c46_hash'] ?? null,
            'actual_c46_hash' => $artifact['actual_c46_hash'] ?? null,
            'c46_hash_match' => $artifact['c46_hash_match'] ?? false,
            'expected_c44_hash' => $artifact['expected_c44_hash'] ?? null,
            'actual_c44_hash' => $artifact['actual_c44_hash'] ?? null,
            'c44_hash_match' => $artifact['c44_hash_match'] ?? false,
            'expected_oos_source_hash' => $artifact['expected_oos_source_hash'] ?? null,
            'actual_oos_source_hash' => $artifact['actual_oos_source_hash'] ?? null,
            'oos_source_hash_match' => $artifact['oos_source_hash_match'] ?? false,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'target_oos_result' => $artifact['target_oos_result'] ?? [],
            'comparison_vs_baseline' => $artifact['comparison_vs_baseline'] ?? [],
            'oos_gate' => $artifact['oos_gate'] ?? [],
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
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C47 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
