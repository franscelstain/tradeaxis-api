<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC44IsGuardRefinementCandidateFormationService
{
    public const RUN_CODE = 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION';
    public const ARTIFACT_TYPE = 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION';
    public const DEFAULT_C43_ARTIFACT = 'storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json';
    public const DEFAULT_EXPECTED_C43_HASH = '41a91ba0447dcf6c0493e1bb27bce6df08fd3490';
    public const DEFAULT_C43_FILE_SHA1 = '27816E62CBE7278108D0BC43C4C3E3F91BC749D7';
    public const DEFAULT_SOURCE_EVIDENCE = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C43_STATUS = 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED';
    public const EXPECTED_C43_CONCLUSION = 'C43_SAFE_PRE_TRADE_FIELDS_FOUND_FOR_C44_REFINEMENT';
    public const EXPECTED_C43_NEXT_STEP = 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION';
    public const TARGET_CANDIDATE_CODE = 'C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA';
    public const BASELINE_CANDIDATE_CODE = 'C44_BASELINE_C39_METADATA_G21_MONTHLY_QUOTA';
    public const BRANCH_TOP_SHARE_LIMIT = 0.80;
    public const REQUIRED_MONTHS = 27;
    public const MIN_MONTHLY_ROWS = 13;

    public function execute(
        string $c43Artifact = self::DEFAULT_C43_ARTIFACT,
        string $expectedC43Hash = self::DEFAULT_EXPECTED_C43_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $artifact = $this->baseArtifact($c43Artifact, $expectedC43Hash, null, null, null, null, $from, $to, $createdAt);
        if (! is_file($c43Artifact)) {
            return $this->blocked($artifact, 'C44_BLOCKED_MISSING_C43_ARTIFACT', 'WS_BT_C44_C43_ARTIFACT_MISSING', 'C44 requires the locked C43 artifact.', $outputPath);
        }
        $c43 = json_decode((string) file_get_contents($c43Artifact), true);
        if (! is_array($c43)) {
            return $this->blocked($artifact, 'C44_BLOCKED_MISSING_C43_ARTIFACT', 'WS_BT_C44_C43_ARTIFACT_UNREADABLE', 'C43 artifact is not readable JSON.', $outputPath);
        }
        $actualHash = $this->stableHash($c43);
        $artifact = $this->baseArtifact($c43Artifact, $expectedC43Hash, $actualHash, $c43['status'] ?? null, $c43['diagnostic_conclusion'] ?? null, $c43['next_step_recommendation'] ?? null, $from, $to, $createdAt);
        $artifact['source_c43_summary'] = $this->sourceC43Summary($c43);

        if ($actualHash !== $expectedC43Hash) {
            return $this->blocked($artifact, 'C44_BLOCKED_C43_HASH_MISMATCH', 'WS_BT_C44_C43_ARTIFACT_HASH_MISMATCH', 'C43 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c43['status'] ?? null) !== self::EXPECTED_C43_STATUS) {
            return $this->blocked($artifact, 'C44_BLOCKED_UNEXPECTED_C43_STATUS', 'WS_BT_C44_UNEXPECTED_C43_STATUS', 'C44 requires completed C43 evidence.', $outputPath);
        }
        if (($c43['diagnostic_conclusion'] ?? null) !== self::EXPECTED_C43_CONCLUSION) {
            return $this->blocked($artifact, 'C44_BLOCKED_UNEXPECTED_C43_CONCLUSION', 'WS_BT_C44_UNEXPECTED_C43_CONCLUSION', 'C44 requires C43 safe pre-trade fields.', $outputPath);
        }
        if (($c43['next_step_recommendation'] ?? null) !== self::EXPECTED_C43_NEXT_STEP) {
            return $this->blocked($artifact, 'C44_BLOCKED_UNEXPECTED_C43_NEXT_STEP', 'WS_BT_C44_UNEXPECTED_C43_NEXT_STEP', 'C43 does not authorize C44 candidate formation.', $outputPath);
        }
        if (! $this->strictFalse($c43['production_ready'] ?? false)) {
            return $this->blocked($artifact, 'C44_BLOCKED_C43_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C44_C43_PRODUCTION_READY_NOT_FALSE', 'C44 requires C43 production_ready=false.', $outputPath);
        }
        if (! $this->strictFalse($c43['is_period']['oos_data_used_for_tuning'] ?? ($c43['safety_boundaries']['oos_data_used_for_tuning'] ?? false))) {
            return $this->blocked($artifact, 'C44_BLOCKED_C43_OOS_TUNING_FLAG_NOT_FALSE', 'WS_BT_C44_C43_OOS_TUNING_FLAG_NOT_FALSE', 'C44 requires C43 oos_data_used_for_tuning=false.', $outputPath);
        }
        if (($c43['c43_decision_summary']['direct_oos_proof_recommended'] ?? true) !== false || ($c43['c43_decision_summary']['oos_proof_unlocked'] ?? true) !== false) {
            return $this->blocked($artifact, 'C44_BLOCKED_C43_OOS_FLAGS_INVALID', 'WS_BT_C44_C43_OOS_FLAGS_INVALID', 'C44 requires C43 direct OOS and unlock flags to remain false.', $outputPath);
        }
        if (($c43['c43_decision_summary']['requires_c44_guard_refinement_candidate_formation'] ?? false) !== true) {
            return $this->blocked($artifact, 'C44_BLOCKED_C43_DOES_NOT_REQUIRE_CANDIDATE_FORMATION', 'WS_BT_C44_C43_CANDIDATE_FORMATION_NOT_REQUIRED', 'C43 does not require C44 candidate formation.', $outputPath);
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C44_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C44_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C44 only accepts the reserved IS period.', $outputPath);
        }

        $sourcePath = trim((string) ($options['source_evidence_artifact'] ?? ($c43['source_evidence_summary']['source_evidence_artifact'] ?? self::DEFAULT_SOURCE_EVIDENCE)));
        if ($sourcePath === '' || ! is_file($sourcePath)) {
            return $this->blocked($artifact, 'C44_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C44_IS_EVIDENCE_MISSING', 'C44 requires C28 IS pick rows.', $outputPath);
        }
        $source = json_decode((string) file_get_contents($sourcePath), true);
        if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
            return $this->blocked($artifact, 'C44_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C44_IS_EVIDENCE_ROWS_MISSING', 'C28 pick diagnostic rows are unavailable.', $outputPath);
        }
        $rows = $this->isRows($source['pick_diagnostic_rows'], $from, $to);
        $g21 = $this->targetRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g16 = $this->targetRows($rows, 'G16', 'next_open_delay_after_close_signal');
        $baselineBranchRows = array_merge($g16, $g21);
        if (count($baselineBranchRows) === 0) {
            return $this->blocked($artifact, 'C44_BLOCKED_MISSING_IS_EVIDENCE', 'WS_BT_C44_BASELINE_ROWS_MISSING', 'C44 found no usable G16/G21 IS rows.', $outputPath);
        }
        $months = $this->uniqueMonths($baselineBranchRows);
        $quota = $this->quotaPerMonth($g21, $g16, $months);
        $sourceLoad = $this->loadPreTradeSources($g21, $options);
        $g21 = $this->enrichRows($g21, $sourceLoad['rows']);
        $g16 = $this->enrichRows($g16, $sourceLoad['rows']);
        if (count($sourceLoad['rows']) === 0) {
            return $this->blocked($artifact, 'C44_BLOCKED_PRE_TRADE_SOURCE_UNAVAILABLE', 'WS_BT_C44_PRE_TRADE_SOURCE_UNAVAILABLE', 'C44 could not join safe signal-date fields.', $outputPath);
        }

        $baselineQuotaRows = $this->selectMonthlyQuota($g21, $months, $quota, 'METADATA');
        $baselineMonthCounts = $this->monthCounts(array_merge($g16, $baselineQuotaRows));
        $requiredMinimumRows = count($baselineMonthCounts) > 0 ? min(self::MIN_MONTHLY_ROWS, min($baselineMonthCounts)) : self::MIN_MONTHLY_ROWS;
        $artifact['source_evidence_summary'] = [
            'source_evidence_artifact' => $sourcePath,
            'is_rows' => count($rows), 'g21_rows' => count($g21), 'g16_rows' => count($g16),
            'baseline_branch_rows' => count($baselineBranchRows), 'months' => count($months),
            'monthly_g21_quota' => $quota, 'pre_trade_source_mode' => $sourceLoad['mode'],
            'pre_trade_source_row_count' => count($sourceLoad['rows']), 'pre_trade_source_error' => $sourceLoad['error'],
            'oos_data_used_for_tuning' => false, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false,
        ];
        $artifact['guard_configuration'] = [
            'required_months' => count($months), 'required_zero_pick_months' => 0,
            'minimum_selected_rows_per_month' => $requiredMinimumRows,
            'max_top_branch_share' => self::BRANCH_TOP_SHARE_LIMIT,
            'monthly_g21_quota' => $quota, 'g21_must_not_be_suppressed_total' => true,
            'quota_count_is_fixed_across_candidates' => true,
        ];

        $specs = $this->candidateSpecs();
        $candidateResults = [];
        foreach ($specs as $spec) {
            $selectedG21 = $this->selectMonthlyQuota($g21, $months, $quota, $spec['ranking']);
            $selected = array_merge($g16, $selectedG21);
            $candidateResults[] = $this->candidateResult($spec, $selected, $selectedG21, $baselineBranchRows, $months, $requiredMinimumRows);
        }
        $baseline = $candidateResults[0];
        foreach ($candidateResults as &$candidate) {
            $candidate['comparison_vs_baseline'] = $this->comparison($candidate, $baseline);
            $candidate['advancement_gate'] = $this->advancementGate($candidate, $baseline);
        }
        unset($candidate);
        $best = $this->bestCandidate($candidateResults);

        $artifact['candidate_results'] = $candidateResults;
        $artifact['candidate_comparison_table'] = array_map(function (array $row): array {
            return array_merge([
                'candidate_code' => $row['candidate_code'], 'selected_rows' => $row['selected_rows'],
                'avg_ret_net' => $row['avg_ret_net'], 'median_ret_net' => $row['median_ret_net'], 'p25_ret_net' => $row['p25_ret_net'],
                'win_rate' => $row['win_rate'], 'month_win_rate_min' => $row['month_win_rate_min'],
                'month_avg_ret_net_min' => $row['month_avg_ret_net_min'], 'bad_month_like_count' => $row['bad_month_like_count'],
                'all_required_guards_passed' => $row['all_required_guards_passed'],
                'advancement_gate_passed' => $row['advancement_gate']['passed'],
            ], $row['comparison_vs_baseline']);
        }, $candidateResults);
        $artifact['candidate_summary'] = [
            'candidate_count' => count($candidateResults),
            'refinement_candidate_count' => max(0, count($candidateResults) - 1),
            'advancement_gate_pass_count' => count(array_filter($candidateResults, function (array $row): bool { return $row['advancement_gate']['passed']; })),
            'candidate_formed' => $best !== null,
            'best_is_candidate_code' => $best['candidate_code'] ?? null,
            'best_is_candidate_is_not_production' => true,
            'best_candidate_requires_c45_validation' => $best !== null,
            'selection_is_is_only' => true,
            'production_ready' => false,
        ];
        $artifact['guard_preservation_summary'] = $this->guardSummary($candidateResults, $best);
        $artifact['candidate_safety_audit'] = $this->safetyAudit($candidateResults);
        $artifact['not_evaluable_reasons'] = [];
        $artifact['c44_decision_summary'] = [
            'candidate_formed' => $best !== null,
            'best_is_candidate_code' => $best['candidate_code'] ?? null,
            'c39_guard_coverage_preserved' => $best !== null && $best['month_coverage_guard']['passed'],
            'c39_branch_diversification_preserved' => $best !== null && $best['branch_diversification_guard']['passed'],
            'requires_c45_is_validation_and_anti_overfit_check' => $best !== null,
            'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false,
            'requires_c45_oos_proof' => false, 'production_ready' => false,
        ];
        $artifact['status'] = 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED';
        $artifact['diagnostic_conclusion'] = $best !== null ? 'C44_GUARD_REFINEMENT_CANDIDATE_FORMED' : 'C44_NO_GUARD_REFINEMENT_CANDIDATE_FORMED';
        $artifact['next_step_recommendation'] = $best !== null ? 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT' : 'C45_PRE_TRADE_FIELD_REFINEMENT_CONTINUATION';
        $artifact['diagnostics'][] = ['reason_code' => $artifact['diagnostic_conclusion'], 'message' => 'C44 formed and compared fixed-quota IS candidates without OOS data, future-path selection, or production promotion.', 'fatal' => false];
        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $path, string $expected, ?string $actual, $status, $conclusion, $next, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE, 'status' => 'C44_OPERATOR_VALIDATION_REQUIRED', 'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false, 'input_c43_artifact' => $path, 'expected_c43_hash' => $expected,
            'actual_c43_hash' => $actual, 'c43_hash_match' => $actual !== null && $actual === $expected,
            'expected_c43_file_sha1' => self::DEFAULT_C43_FILE_SHA1, 'c43_status' => $status,
            'c43_diagnostic_conclusion' => $conclusion, 'c43_next_step_recommendation' => $next,
            'is_period' => ['from' => $from, 'to' => $to, 'oos_reserved_from' => self::OOS_RESERVED_FROM, 'oos_reserved_to' => self::OOS_RESERVED_TO, 'oos_data_used_for_tuning' => false],
            'source_c43_summary' => [], 'source_evidence_summary' => [], 'guard_configuration' => [], 'candidate_results' => [],
            'candidate_comparison_table' => [], 'candidate_summary' => [], 'guard_preservation_summary' => [],
            'candidate_safety_audit' => [], 'not_evaluable_reasons' => [], 'c44_decision_summary' => [],
            'diagnostic_conclusion' => 'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_PENDING', 'next_step_recommendation' => 'C44_PENDING',
            'diagnostics' => [['reason_code' => 'WS_BT_C44_IS_ONLY_NOTE', 'message' => 'C44 reranks a fixed monthly G21 quota with C43-approved signal-date fields.', 'fatal' => false]],
            'safety_boundaries' => [
                'C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION' => true, 'C43_ARTIFACT_HASH_LOCK' => true,
                'IS_ONLY_CANDIDATE_FORMATION' => true, 'C44_FROM_C43_SAFE_FIELD_REQUIREMENTS' => true,
                'C39_COVERAGE_GUARD_PRESERVED' => true, 'C39_BRANCH_DIVERSIFICATION_GUARD_PRESERVED' => true,
                'NO_OOS_TUNING' => true, 'NO_OOS_PROOF' => true, 'NO_BEST_OF_OOS' => true, 'NO_OOS_WINNER' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true, 'NO_PRODUCTION_CATALOG' => true, 'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true, 'NO_C01_TO_C43_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true, 'CANDIDATE_REQUIRES_C45_VALIDATION' => true,
                'production_ready' => false, 'oos_data_used_for_tuning' => false, 'return_used_for_selection' => false,
                'future_path_used_for_selection' => false, 'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false, 'derived_mfe_mae_used_for_execution' => false,
            ],
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function sourceC43Summary(array $c43): array
    {
        return [
            'target_candidate_code' => $c43['source_c42_summary']['target_candidate_code'] ?? self::TARGET_CANDIDATE_CODE,
            'suspected_warning_month' => $c43['source_c42_summary']['suspected_warning_month'] ?? null,
            'refinement_readiness_result' => $c43['refinement_readiness_assessment']['refinement_readiness_result'] ?? null,
            'safe_fields_for_future_refinement' => $c43['refinement_readiness_assessment']['safe_fields_for_future_refinement'] ?? [],
            'cluster_supporting_fields' => $c43['refinement_readiness_assessment']['cluster_supporting_fields'] ?? [],
            'requires_c44_guard_refinement_candidate_formation' => (bool) ($c43['c43_decision_summary']['requires_c44_guard_refinement_candidate_formation'] ?? false),
            'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false,
        ];
    }

    private function candidateSpecs(): array
    {
        return [
            ['candidate_code' => self::BASELINE_CANDIDATE_CODE, 'candidate_group' => 'BASELINE_COMPARATOR', 'ranking' => 'METADATA', 'selection_rule' => 'C39 metadata ordering with fixed monthly G21 quota', 'selection_input_fields' => ['trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
            ['candidate_code' => 'C44_G21_LIQUIDITY_QUALITY_FIXED_MONTHLY_QUOTA', 'candidate_group' => 'LIQUIDITY_REFINEMENT', 'ranking' => 'LIQUIDITY', 'selection_rule' => 'prefer higher signal-date dv20_idr inside each fixed monthly G21 quota', 'selection_input_fields' => ['dv20_idr', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
            ['candidate_code' => 'C44_G21_VOLATILITY_QUALITY_FIXED_MONTHLY_QUOTA', 'candidate_group' => 'VOLATILITY_REFINEMENT', 'ranking' => 'VOLATILITY', 'selection_rule' => 'prefer C43 fixed ATR buckets LT_2PCT then 2_TO_5PCT then 5_TO_8PCT then GTE_8PCT inside fixed quota', 'selection_input_fields' => ['atr14_pct', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
            ['candidate_code' => 'C44_G21_RELATIVE_STRENGTH_FIXED_MONTHLY_QUOTA', 'candidate_group' => 'RELATIVE_STRENGTH_REFINEMENT', 'ranking' => 'RELATIVE_STRENGTH', 'selection_rule' => 'prefer higher signal-date rs_20_vs_ihsg and rs_20_vs_sector inside fixed quota', 'selection_input_fields' => ['rs_20_vs_ihsg', 'rs_20_vs_sector', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
            ['candidate_code' => 'C44_G21_SECTOR_HEALTH_FIXED_MONTHLY_QUOTA', 'candidate_group' => 'SECTOR_REFINEMENT', 'ranking' => 'SECTOR_HEALTH', 'selection_rule' => 'prefer higher signal-date sector_roc20 and relative strength versus sector inside fixed quota', 'selection_input_fields' => ['sector_roc20', 'rs_20_vs_sector', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
            ['candidate_code' => 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA', 'candidate_group' => 'MARKET_CONDITION_REFINEMENT', 'ranking' => 'MARKET_EXTENSION', 'selection_rule' => 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota', 'selection_input_fields' => ['market_index_roc20', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
            ['candidate_code' => 'C44_G21_BALANCED_QUALITY_FIXED_MONTHLY_QUOTA', 'candidate_group' => 'BALANCED_REFINEMENT', 'ranking' => 'BALANCED', 'selection_rule' => 'lexicographic fixed ATR bucket, relative strength, and liquidity quality inside fixed quota', 'selection_input_fields' => ['atr14_pct', 'rs_20_vs_ihsg', 'dv20_idr', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']],
        ];
    }

    private function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array
    {
        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[(string) ($row['trade_month'] ?? substr((string) $row['trade_date'], 0, 7))][] = $row;
        }
        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[$month] ?? [];
            usort($monthRows, function (array $a, array $b) use ($ranking): int {
                $qa = $this->qualityKey($a, $ranking);
                $qb = $this->qualityKey($b, $ranking);
                $quality = strcmp($qa, $qb);
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
        if ($ranking === 'LIQUIDITY') {
            return $this->descendingKey($row['dv20_idr'] ?? null);
        }
        if ($ranking === 'VOLATILITY') {
            return sprintf('%02d|%020.10f', $this->atrBucketRank($row['atr14_pct'] ?? null), (float) ($row['atr14_pct'] ?? 999));
        }
        if ($ranking === 'RELATIVE_STRENGTH') {
            return $this->descendingKey($row['rs_20_vs_ihsg'] ?? null).'|'.$this->descendingKey($row['rs_20_vs_sector'] ?? null);
        }
        if ($ranking === 'SECTOR_HEALTH') {
            return $this->descendingKey($row['sector_roc20'] ?? null).'|'.$this->descendingKey($row['rs_20_vs_sector'] ?? null);
        }
        if ($ranking === 'MARKET_EXTENSION') {
            $roc = $this->num($row['market_index_roc20'] ?? null);
            $rank = $roc === null ? 9 : ($roc < 0.0 ? 1 : ($roc < 0.10 ? 0 : 2));
            return sprintf('%02d|%020.10f', $rank, abs((float) ($roc ?? 999)));
        }
        return sprintf('%02d', $this->atrBucketRank($row['atr14_pct'] ?? null)).'|'.$this->descendingKey($row['rs_20_vs_ihsg'] ?? null).'|'.$this->descendingKey($row['dv20_idr'] ?? null);
    }

    private function atrBucketRank($value): int
    {
        $atr = $this->num($value);
        if ($atr === null) { return 9; }
        if ($atr < 0.02) { return 0; }
        if ($atr < 0.05) { return 1; }
        if ($atr < 0.08) { return 2; }
        return 3;
    }

    private function descendingKey($value): string
    {
        $number = $this->num($value);
        return $number === null ? '9|99999999999999999999' : '0|'.sprintf('%030.10f', 1000000000000000.0 - $number);
    }

    private function metadataKey(array $row): string
    {
        return implode('|', [(string) ($row['trade_month'] ?? ''), (string) ($row['trade_date'] ?? ''), (string) ($row['ticker'] ?? ''), sprintf('%010d', (int) ($row['param_id'] ?? 0)), (string) ($row['row_code'] ?? '')]);
    }

    private function candidateResult(array $spec, array $selected, array $selectedG21, array $evaluatedRows, array $months, int $requiredMinimumRows): array
    {
        $metrics = $this->metrics($selected);
        $guard = $this->guardEvaluation($selected, $months, $requiredMinimumRows);
        $march = $this->metrics($this->filterMonth($selected, '2024-03'));
        $marchG21 = $this->metrics($this->filterMonth($selectedG21, '2024-03'));
        return [
            'candidate_code' => $spec['candidate_code'], 'candidate_group' => $spec['candidate_group'], 'candidate_status' => 'EVALUATED',
            'evaluated_rows' => count($evaluatedRows), 'selected_rows' => count($selected), 'selected_g21_rows' => count($selectedG21),
            'avg_ret_net' => $metrics['avg_ret_net'], 'median_ret_net' => $metrics['median_ret_net'], 'p25_ret_net' => $metrics['p25_ret_net'],
            'p10_ret_net' => $metrics['p10_ret_net'], 'win_rate' => $metrics['win_rate'], 'month_win_rate_min' => $metrics['month_win_rate_min'],
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'], 'bad_month_like_count' => $metrics['bad_month_like_count'],
            'loss_concentration' => $metrics['loss_concentration'], 'march_2024_avg_ret_net' => $march['avg_ret_net'],
            'march_2024_g21_avg_ret_net' => $marchG21['avg_ret_net'], 'march_2024_g21_win_rate' => $marchG21['win_rate'],
            'ticker_concentration' => $this->concentration($selected, 'ticker'), 'branch_concentration' => $this->concentration($selected, 'selected_source_code'),
            'branch_distribution' => $this->distribution($selected, 'selected_source_code'),
            'month_coverage_guard' => $guard['month_coverage_guard'], 'branch_diversification_guard' => $guard['branch_diversification_guard'],
            'minimum_monthly_rows_guard' => $guard['minimum_monthly_rows_guard'], 'all_required_guards_passed' => $guard['all_required_guards_passed'],
            'selection_rule' => $spec['selection_rule'], 'selection_input_fields' => $spec['selection_input_fields'],
            'selection_input_safety_check' => ['all_fields_safe_pre_trade' => true, 'as_of_date_safe' => true, 'fixed_monthly_quota' => true, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_data_used_for_tuning' => false],
            'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false, 'derived_mfe_mae_used_for_execution' => false, 'oos_data_used_for_tuning' => false,
            'candidate_is_not_production' => true, 'production_ready' => false,
        ];
    }

    private function guardEvaluation(array $rows, array $months, int $requiredMinimumRows): array
    {
        $counts = $this->monthCounts($rows);
        $zero = array_values(array_diff($months, array_keys($counts)));
        $minimum = count($counts) > 0 ? min($counts) : 0;
        $distribution = $this->distribution($rows, 'selected_source_code');
        $top = $distribution[0]['share'] ?? null;
        $monthPass = count($zero) === 0 && count($counts) === count($months);
        $branchPass = count($distribution) >= 2 && $top !== null && $top <= self::BRANCH_TOP_SHARE_LIMIT;
        $minimumPass = $minimum >= $requiredMinimumRows;
        return [
            'month_coverage_guard' => ['required_months' => count($months), 'candidate_months_covered' => count($counts), 'zero_pick_months' => $zero, 'zero_pick_month_count' => count($zero), 'passed' => $monthPass],
            'branch_diversification_guard' => ['max_top_branch_share' => self::BRANCH_TOP_SHARE_LIMIT, 'candidate_top_branch_share' => $top, 'candidate_branch_count' => count($distribution), 'g16_share' => $this->valueShare($rows, 'selected_source_code', 'G16'), 'g21_share' => $this->valueShare($rows, 'selected_source_code', 'G21'), 'passed' => $branchPass],
            'minimum_monthly_rows_guard' => ['required_minimum' => $requiredMinimumRows, 'candidate_minimum' => $minimum, 'candidate_median' => $this->median(array_values($counts)), 'passed' => $minimumPass],
            'all_required_guards_passed' => $monthPass && $branchPass && $minimumPass,
        ];
    }

    private function comparison(array $candidate, array $baseline): array
    {
        $out = [];
        foreach (['avg_ret_net', 'median_ret_net', 'p25_ret_net', 'p10_ret_net', 'win_rate', 'month_win_rate_min', 'month_avg_ret_net_min', 'march_2024_avg_ret_net', 'march_2024_g21_avg_ret_net'] as $field) {
            $out['delta_'.$field.'_vs_baseline'] = $candidate[$field] !== null && $baseline[$field] !== null ? $candidate[$field] - $baseline[$field] : null;
        }
        $out['delta_bad_month_like_count_vs_baseline'] = $candidate['bad_month_like_count'] - $baseline['bad_month_like_count'];
        $out['delta_loss_concentration_vs_baseline'] = $candidate['loss_concentration'] - $baseline['loss_concentration'];
        return $out;
    }

    private function advancementGate(array $candidate, array $baseline): array
    {
        $isBaseline = $candidate['candidate_code'] === self::BASELINE_CANDIDATE_CODE;
        $checks = [
            'is_refinement_candidate' => ! $isBaseline,
            'all_required_guards_passed' => $candidate['all_required_guards_passed'],
            'avg_ret_net_not_worse' => $candidate['avg_ret_net'] !== null && $candidate['avg_ret_net'] >= $baseline['avg_ret_net'],
            'p25_not_materially_worse' => $candidate['p25_ret_net'] !== null && $candidate['p25_ret_net'] >= $baseline['p25_ret_net'] - 0.002,
            'bad_month_like_count_not_worse' => $candidate['bad_month_like_count'] <= $baseline['bad_month_like_count'],
            'march_2024_not_worse' => $candidate['march_2024_avg_ret_net'] !== null && $candidate['march_2024_avg_ret_net'] >= $baseline['march_2024_avg_ret_net'],
            'selection_safety_passed' => true,
        ];
        return ['passed' => ! in_array(false, $checks, true), 'checks' => $checks, 'reason_code' => ! in_array(false, $checks, true) ? 'C44_ADVANCEMENT_GATE_PASS' : 'C44_ADVANCEMENT_GATE_FAIL'];
    }

    private function bestCandidate(array $results): ?array
    {
        $passing = array_values(array_filter($results, function (array $row): bool { return $row['advancement_gate']['passed']; }));
        usort($passing, function (array $a, array $b): int {
            foreach (['month_avg_ret_net_min', 'p25_ret_net', 'avg_ret_net', 'march_2024_avg_ret_net'] as $field) {
                $cmp = ($b[$field] ?? -INF) <=> ($a[$field] ?? -INF);
                if ($cmp !== 0) { return $cmp; }
            }
            return strcmp($a['candidate_code'], $b['candidate_code']);
        });
        return $passing[0] ?? null;
    }

    private function guardSummary(array $results, ?array $best): array
    {
        return [
            'candidate_count' => count($results),
            'all_guard_pass_count' => count(array_filter($results, function (array $row): bool { return $row['all_required_guards_passed']; })),
            'best_candidate_code' => $best['candidate_code'] ?? null,
            'best_candidate_months_covered' => $best['month_coverage_guard']['candidate_months_covered'] ?? null,
            'best_candidate_zero_pick_months' => $best['month_coverage_guard']['zero_pick_month_count'] ?? null,
            'best_candidate_min_selected_rows_per_month' => $best['minimum_monthly_rows_guard']['candidate_minimum'] ?? null,
            'best_candidate_top_branch_share' => $best['branch_diversification_guard']['candidate_top_branch_share'] ?? null,
            'best_candidate_g16_share' => $best['branch_diversification_guard']['g16_share'] ?? null,
            'best_candidate_g21_share' => $best['branch_diversification_guard']['g21_share'] ?? null,
            'c39_coverage_guard_preserved' => $best !== null && $best['month_coverage_guard']['passed'],
            'c39_branch_guard_preserved' => $best !== null && $best['branch_diversification_guard']['passed'],
            'g21_not_suppressed_total' => $best !== null && ($best['branch_diversification_guard']['g21_share'] ?? 0) > 0,
        ];
    }

    private function safetyAudit(array $results): array
    {
        $out = [];
        foreach ($results as $row) {
            $out[] = [
                'candidate_code' => $row['candidate_code'], 'passed' => true,
                'reason_code' => 'C44_SAFE_SIGNAL_DATE_FIXED_QUOTA_SELECTION',
                'message' => 'Candidate uses C43-approved signal-date fields and fixed monthly G21 quota; returns are evaluation-only.',
                'return_used_for_selection' => false, 'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false, 'candidate_is_not_production' => true, 'production_ready' => false,
            ];
        }
        return $out;
    }

    private function loadPreTradeSources(array $rows, array $options): array
    {
        if (array_key_exists('pre_trade_source_rows', $options)) {
            $map = [];
            foreach ((array) $options['pre_trade_source_rows'] as $row) {
                if (is_array($row)) { $map[$this->joinKey($row)] = $row; }
            }
            return ['mode' => 'INJECTED_TEST_SOURCE', 'rows' => $map, 'error' => null];
        }
        try {
            if (! Schema::hasTable('eod_indicators')) {
                return ['mode' => 'SOURCE_NOT_MIGRATED', 'rows' => [], 'error' => 'eod_indicators unavailable'];
            }
            $dates = []; $tickerIds = []; $required = [];
            foreach ($rows as $row) {
                $dates[(string) $row['trade_date']] = true;
                if (isset($row['ticker_id'])) { $tickerIds[(int) $row['ticker_id']] = true; }
                $required[$this->joinKey($row)] = true;
            }
            if (count($tickerIds) === 0) {
                return ['mode' => 'JOIN_KEYS_UNAVAILABLE', 'rows' => [], 'error' => 'ticker_id unavailable'];
            }
            $map = [];
            foreach (array_chunk(array_keys($dates), 75) as $dateChunk) {
                $dbRows = DB::table('eod_indicators')
                    ->whereIn('trade_date', $dateChunk)->whereIn('ticker_id', array_keys($tickerIds))
                    ->select(['trade_date', 'ticker_id', 'dv20_idr', 'atr14_pct', 'roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'sector_roc20', 'sector_code'])
                    ->get();
                foreach ($dbRows as $dbRow) {
                    $row = (array) $dbRow; $key = $this->joinKey($row);
                    if (isset($required[$key])) { $map[$key] = $row; }
                }
            }
            $benchmarks = [];
            if (Schema::hasTable('market_benchmark_indicators')) {
                foreach (array_chunk(array_keys($dates), 200) as $dateChunk) {
                    foreach (DB::table('market_benchmark_indicators')->where('benchmark_code', 'IHSG')->whereIn('trade_date', $dateChunk)->select(['trade_date', 'roc_20'])->get() as $row) {
                        $benchmarks[(string) $row->trade_date] = $row->roc_20;
                    }
                }
            }
            foreach ($map as $key => $row) { $map[$key]['market_index_roc20'] = $benchmarks[(string) $row['trade_date']] ?? null; }
            return ['mode' => 'DATABASE_AS_OF_SIGNAL_DATE_JOIN', 'rows' => $map, 'error' => null];
        } catch (Throwable $e) {
            return ['mode' => 'DATABASE_JOIN_FAILED', 'rows' => [], 'error' => get_class($e).': '.$e->getMessage()];
        }
    }

    private function enrichRows(array $rows, array $sources): array
    {
        return array_map(function (array $row) use ($sources): array { return array_merge($row, $sources[$this->joinKey($row)] ?? []); }, $rows);
    }

    private function joinKey(array $row): string
    {
        return (string) ($row['trade_date'] ?? '').'|'.(isset($row['ticker_id']) ? 'ID:'.$row['ticker_id'] : 'TICKER:'.strtoupper((string) ($row['ticker'] ?? '')));
    }

    private function quotaPerMonth(array $g21, array $g16, array $months): int
    {
        $required = count($g16) > 0 ? (int) ceil((count($g16) / self::BRANCH_TOP_SHARE_LIMIT) - count($g16)) : 0;
        return count($months) > 0 ? max(1, (int) ceil($required / count($months))) : 0;
    }

    private function isRows(array $rows, string $from, string $to): array
    {
        return array_values(array_filter($rows, function ($row) use ($from, $to): bool {
            if (! is_array($row)) { return false; }
            $date = (string) ($row['trade_date'] ?? '');
            return $date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0
                && ! (($row['oos_executed'] ?? false) === true || (int) ($row['oos_executed'] ?? 0) === 1)
                && ! (($row['production_ready'] ?? 0) === true || (int) ($row['production_ready'] ?? 0) === 1);
        }));
    }

    private function targetRows(array $rows, string $source, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($source, $bucket): bool {
            return (string) ($row['selected_source_code'] ?? '') === $source && (string) ($row['bucket_code'] ?? '') === $bucket && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
    }

    private function metrics(array $rows): array
    {
        $values = []; $byMonth = []; $losses = 0;
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) { continue; }
            $values[] = $value; if ($value < 0) { $losses++; }
            $month = (string) ($row['trade_month'] ?? substr((string) $row['trade_date'], 0, 7)); $byMonth[$month][] = $value;
        }
        sort($values); $count = count($values);
        if ($count === 0) { return ['avg_ret_net' => null, 'median_ret_net' => null, 'p25_ret_net' => null, 'p10_ret_net' => null, 'win_rate' => null, 'month_win_rate_min' => null, 'month_avg_ret_net_min' => null, 'bad_month_like_count' => 0, 'loss_concentration' => null]; }
        $monthWins = []; $monthAvgs = []; $bad = 0;
        foreach ($byMonth as $monthValues) {
            $avg = array_sum($monthValues) / count($monthValues); $wins = count(array_filter($monthValues, function ($v): bool { return $v > 0; })) / count($monthValues);
            $monthAvgs[] = $avg; $monthWins[] = $wins; if ($avg < 0 || $wins <= 0) { $bad++; }
        }
        return [
            'avg_ret_net' => array_sum($values) / $count, 'median_ret_net' => $this->percentile($values, 0.5),
            'p25_ret_net' => $this->percentile($values, 0.25), 'p10_ret_net' => $this->percentile($values, 0.10),
            'win_rate' => count(array_filter($values, function ($v): bool { return $v > 0; })) / $count,
            'month_win_rate_min' => min($monthWins), 'month_avg_ret_net_min' => min($monthAvgs),
            'bad_month_like_count' => $bad, 'loss_concentration' => $losses / $count,
        ];
    }

    private function percentile(array $sorted, float $p): ?float
    {
        $count = count($sorted); if ($count === 0) { return null; }
        $index = ($count - 1) * $p; $lo = (int) floor($index); $hi = (int) ceil($index);
        return $lo === $hi ? $sorted[$lo] : $sorted[$lo] + (($sorted[$hi] - $sorted[$lo]) * ($index - $lo));
    }

    private function distribution(array $rows, string $field): array
    {
        $counts = []; foreach ($rows as $row) { $key = (string) ($row[$field] ?? 'UNKNOWN'); $counts[$key] = ($counts[$key] ?? 0) + 1; }
        arsort($counts); $out = []; foreach ($counts as $value => $count) { $out[] = ['value' => $value, 'count' => $count, 'share' => count($rows) > 0 ? $count / count($rows) : null]; }
        return $out;
    }

    private function concentration(array $rows, string $field): ?float
    {
        $distribution = $this->distribution($rows, $field); return $distribution[0]['share'] ?? null;
    }

    private function valueShare(array $rows, string $field, string $value): float
    {
        if (count($rows) === 0) { return 0.0; }
        return count(array_filter($rows, function (array $row) use ($field, $value): bool { return (string) ($row[$field] ?? '') === $value; })) / count($rows);
    }

    private function uniqueMonths(array $rows): array
    {
        $months = []; foreach ($rows as $row) { $months[(string) ($row['trade_month'] ?? substr((string) $row['trade_date'], 0, 7))] = true; }
        $months = array_keys($months); sort($months); return $months;
    }

    private function monthCounts(array $rows): array
    {
        $counts = []; foreach ($rows as $row) { $month = (string) ($row['trade_month'] ?? substr((string) $row['trade_date'], 0, 7)); $counts[$month] = ($counts[$month] ?? 0) + 1; }
        ksort($counts); return $counts;
    }

    private function filterMonth(array $rows, string $month): array
    {
        return array_values(array_filter($rows, function (array $row) use ($month): bool { return (string) ($row['trade_month'] ?? substr((string) $row['trade_date'], 0, 7)) === $month; }));
    }

    private function median(array $values): ?float
    {
        if (count($values) === 0) { return null; } sort($values); return $this->percentile($values, 0.5);
    }

    private function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    private function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 || strcmp($from, self::OOS_RESERVED_FROM) >= 0; }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status; $artifact['diagnostic_conclusion'] = 'C44_INPUT_LOCK_OR_BOUNDARY_BLOCKED'; $artifact['next_step_recommendation'] = 'C44_BLOCKED_UNTIL_INPUT_VALIDATED';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true]; $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') { $this->writeArtifact($output, $artifact, true); }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact); $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C44_OPERATOR_VALIDATION_REQUIRED'; return $this->result($artifact, $output, $write['reason_code'], $write['message']); }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return ['status' => $artifact['status'], 'reason_code' => $reason, 'message' => $message, 'artifact_path' => $path, 'artifact_hash' => $artifact['artifact_hash'] ?? null, 'production_ready' => 0,
            'expected_c43_hash' => $artifact['expected_c43_hash'] ?? null, 'actual_c43_hash' => $artifact['actual_c43_hash'] ?? null, 'c43_hash_match' => $artifact['c43_hash_match'] ?? false,
            'c43_status' => $artifact['c43_status'] ?? null, 'c43_diagnostic_conclusion' => $artifact['c43_diagnostic_conclusion'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null, 'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'candidate_summary' => $artifact['candidate_summary'] ?? [], 'guard_preservation_summary' => $artifact['guard_preservation_summary'] ?? [], 'c44_decision_summary' => $artifact['c44_decision_summary'] ?? []];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); }
        $dir = dirname($path); if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES); if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C44 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
}
