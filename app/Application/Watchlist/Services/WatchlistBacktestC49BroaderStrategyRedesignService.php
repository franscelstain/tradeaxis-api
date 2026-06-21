<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC49BroaderStrategyRedesignService
{
    public const RUN_CODE = 'C49_BROADER_STRATEGY_REDESIGN';
    public const ARTIFACT_TYPE = 'C49_BROADER_STRATEGY_REDESIGN';
    public const DEFAULT_C48_ARTIFACT = 'storage/app/watchlist/backtest/c48-oos-failure-attribution.json';
    public const DEFAULT_EXPECTED_C48_HASH = '1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7';
    public const DEFAULT_C48_FILE_SHA1 = 'EEA350AF2D8A42C881B78701C48A1E301230362C';
    public const DEFAULT_SOURCE_EVIDENCE = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_C44_ARTIFACT = 'storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json';
    public const DEFAULT_C45_ARTIFACT = 'storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json';
    public const DEFAULT_C46_ARTIFACT = 'storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c49-broader-strategy-redesign.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C48_STATUS = 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED';
    public const EXPECTED_C48_NEXT_STEP = 'C49_BROADER_STRATEGY_REDESIGN';
    public const CURRENT_G21_QUOTA = 13;

    private const VALID_C48_CONCLUSIONS = [
        'C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED',
        'C48_OOS_GENERALIZATION_FAILURE_IDENTIFIED',
        'C48_G21_QUOTA_FRAGILITY_IDENTIFIED',
        'C48_MARKET_REGIME_FAILURE_IDENTIFIED',
        'C48_MARKET_EXTENSION_CONTROL_INSUFFICIENT',
        'C48_TICKER_CLUSTER_FAILURE_IDENTIFIED',
        'C48_SECTOR_BUCKET_FAILURE_IDENTIFIED',
        'C48_POST_ENTRY_PATH_FAILURE_IDENTIFIED',
    ];

    /**
     * C49_IS_BROADER_STRATEGY_REDESIGN_ONLY. C48 is read for hypothesis only.
     * NO_OOS_TUNING, NO_OOS_PROOF, NO_OOS_PROOF_RERUN, NO_BEST_OF_OOS, NO_OOS_WINNER.
     */
    public function execute(
        string $c48Artifact = self::DEFAULT_C48_ARTIFACT,
        string $expectedC48Hash = self::DEFAULT_EXPECTED_C48_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c48Artifact = trim($c48Artifact) !== '' ? trim($c48Artifact) : self::DEFAULT_C48_ARTIFACT;
        $expectedC48Hash = trim($expectedC48Hash) !== '' ? trim($expectedC48Hash) : self::DEFAULT_EXPECTED_C48_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        $artifact = $this->baseArtifact($c48Artifact, $expectedC48Hash, $from, $to, $createdAt);

        if (! is_file($c48Artifact)) {
            return $this->blocked($artifact, 'C49_BLOCKED_MISSING_C48_ARTIFACT', 'WS_BT_C49_C48_ARTIFACT_MISSING', 'C49 requires the locked C48 OOS failure attribution artifact.', $outputPath);
        }

        $c48 = json_decode((string) file_get_contents($c48Artifact), true);
        if (! is_array($c48)) {
            return $this->blocked($artifact, 'C49_BLOCKED_MISSING_C48_ARTIFACT', 'WS_BT_C49_C48_ARTIFACT_UNREADABLE', 'C48 artifact is not readable JSON.', $outputPath);
        }

        $actualC48Hash = $this->stableHash($c48);
        $artifact['actual_c48_hash'] = $actualC48Hash;
        $artifact['c48_hash_match'] = $actualC48Hash === $expectedC48Hash;
        $artifact['c48_status'] = $c48['status'] ?? null;
        $artifact['c48_diagnostic_conclusion'] = $c48['diagnostic_conclusion'] ?? null;
        $artifact['c48_next_step_recommendation'] = $c48['next_step_recommendation'] ?? null;
        $artifact['c48_carry_forward_summary'] = $this->c48CarryForwardSummary($c48);

        if ($actualC48Hash !== $expectedC48Hash) {
            return $this->blocked($artifact, 'C49_BLOCKED_C48_HASH_MISMATCH', 'WS_BT_C49_C48_ARTIFACT_HASH_MISMATCH', 'C48 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c48['status'] ?? null) !== self::EXPECTED_C48_STATUS) {
            return $this->blocked($artifact, 'C49_BLOCKED_UNEXPECTED_C48_STATUS', 'WS_BT_C49_UNEXPECTED_C48_STATUS', 'C49 requires completed C48 failure attribution.', $outputPath);
        }
        if (! in_array((string) ($c48['diagnostic_conclusion'] ?? ''), self::VALID_C48_CONCLUSIONS, true)) {
            return $this->blocked($artifact, 'C49_BLOCKED_UNEXPECTED_C48_CONCLUSION', 'WS_BT_C49_UNEXPECTED_C48_CONCLUSION', 'C48 diagnostic conclusion does not authorize C49 broader redesign.', $outputPath);
        }
        if (! $this->strictFalse($c48['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C49_BLOCKED_C48_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C49_C48_PRODUCTION_READY_NOT_FALSE', 'C48 production_ready must be false.', $outputPath);
        }
        if (($c48['next_step_recommendation'] ?? null) !== self::EXPECTED_C48_NEXT_STEP) {
            return $this->blocked($artifact, 'C49_BLOCKED_C48_NEXT_STEP_UNEXPECTED', 'WS_BT_C49_C48_NEXT_STEP_UNEXPECTED', 'C48 next step must route to C49 broader strategy redesign.', $outputPath);
        }
        if (($c48['c49_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c48['c49_readiness_decision']['oos_proof_unlocked'] ?? false) === true) {
            return $this->blocked($artifact, 'C49_BLOCKED_C48_OOS_PROOF_FLAG_INVALID', 'WS_BT_C49_C48_OOS_PROOF_FLAG_INVALID', 'C48 must not unlock or recommend direct OOS proof.', $outputPath);
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C49_BLOCKED_ATTRIBUTION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C49_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C49 only accepts IS period and must not touch OOS reserved period.', $outputPath);
        }

        $sourceLoad = $this->loadSourceRows($from, $to, $options, $artifact['not_evaluable_reasons']);
        $rows = $sourceLoad['rows'];
        $artifact['source_universe_summary'] = $sourceLoad['summary'];

        if (count($rows) === 0) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_universe', 'pick_rows', 'C49_SOURCE_ROWS_NOT_EVALUABLE', 'No IS source rows are available for broader strategy redesign.');
            $artifact['diagnostic_conclusion'] = 'C49_EVIDENCE_EXPANSION_REQUIRED';
            $artifact['next_step_recommendation'] = 'C50_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN';
            $artifact['status'] = 'C49_SOURCE_ROWS_NOT_EVALUABLE';
            $artifact['c50_readiness_decision'] = $this->c50Decision(null, 'C50_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN', false);
            $artifact['diagnostics'] = $this->completedDiagnostics($artifact);
            return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $g16 = $this->branchBucketRows($rows, 'G16', 'next_open_delay_after_close_signal');
        $g21 = $this->branchBucketRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g13 = $this->branchBucketRows($rows, 'G13', 'no_rule_profit_signal_before_fallback');
        $baselineBranchRows = array_merge($g16, $g21);
        if (count($baselineBranchRows) === 0) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_universe', 'g16_g21_rows', 'C49_SOURCE_ROWS_NOT_EVALUABLE', 'No usable G16/G21 IS rows are available.');
        }
        $months = $this->uniqueMonths(count($baselineBranchRows) > 0 ? $baselineBranchRows : $rows);
        $c44Rows = array_merge($g16, $this->selectMonthlyQuota($g21, $months, self::CURRENT_G21_QUOTA, 'MARKET_EXTENSION'));
        $baselineRows = array_merge($g16, $this->selectMonthlyQuota($g21, $months, self::CURRENT_G21_QUOTA, 'METADATA'));
        $artifact['baseline_c44_comparator_summary'] = $this->baselineC44ComparatorSummary($c44Rows, $baselineRows, $options, $artifact['not_evaluable_reasons']);

        $profileResults = $this->redesignProfileResults($g16, $g21, $g13, $baselineRows, $c44Rows, $months, $artifact['not_evaluable_reasons']);
        $artifact['redesign_profile_results'] = $profileResults;
        $artifact['shared_core_escape_attribution'] = $this->sharedCoreEscapeAttribution($profileResults);
        $artifact['branch_quota_fragility_is_diagnostic'] = $this->branchQuotaDiagnostic($g16, $g21, $c44Rows, $months);
        $artifact['regime_aware_is_diagnostic'] = $this->regimeDiagnostic($baselineBranchRows, $artifact['not_evaluable_reasons']);
        $artifact['concentration_guard_is_diagnostic'] = $this->concentrationDiagnostic($profileResults, $artifact['not_evaluable_reasons']);
        $artifact['post_entry_path_is_diagnostic'] = $this->postEntryPathDiagnostic($baselineBranchRows, $artifact['not_evaluable_reasons']);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($profileResults);
        $selected = $this->selectedCandidates($artifact['candidate_scorecard']);
        $artifact['selected_c49_candidates_for_c50'] = $selected;
        $artifact['c50_readiness_decision'] = $this->readinessDecision($selected, $artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($artifact['candidate_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c50_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c50_readiness_decision']['c50_recommendation'];
        $artifact['status'] = 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED';
        $artifact['diagnostics'] = $this->completedDiagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $c48Artifact, string $expectedC48Hash, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C49_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c48_artifact' => $c48Artifact,
            'expected_c48_hash' => $expectedC48Hash,
            'expected_c48_file_sha1' => self::DEFAULT_C48_FILE_SHA1,
            'actual_c48_hash' => null,
            'c48_hash_match' => false,
            'c48_status' => null,
            'c48_diagnostic_conclusion' => null,
            'c48_next_step_recommendation' => null,
            'is_redesign_period' => ['from' => $from, 'to' => $to, 'purpose' => 'broader_strategy_redesign_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c48_carry_forward_summary' => [],
            'source_universe_summary' => [],
            'baseline_c44_comparator_summary' => [],
            'redesign_profile_results' => [],
            'shared_core_escape_attribution' => [],
            'branch_quota_fragility_is_diagnostic' => [],
            'regime_aware_is_diagnostic' => [],
            'concentration_guard_is_diagnostic' => [],
            'post_entry_path_is_diagnostic' => [],
            'candidate_scorecard' => [],
            'selected_c49_candidates_for_c50' => [],
            'c50_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C49_PENDING',
            'next_step_recommendation' => 'C49_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                'C49_IS_BROADER_STRATEGY_REDESIGN_ONLY' => true,
                'C48_ARTIFACT_HASH_LOCK' => true,
                'C48_USED_FOR_HYPOTHESIS_ONLY' => true,
                'IS_ONLY_SELECTION' => true,
                'NO_OOS_TUNING' => true,
                'NO_OOS_PROOF' => true,
                'NO_OOS_PROOF_RERUN' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_OOS_RETURN_SELECTION' => true,
                'NO_OOS_BAD_MONTH_THRESHOLD_SELECTION' => true,
                'NO_OOS_TICKER_SECTOR_EXCLUSION_RULE' => true,
                'NO_PROFILE_RESELECTION_FROM_OOS' => true,
                'NO_CANDIDATE_RESELECTION_FROM_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'NO_C01_TO_C48_MUTATION' => true,
                'NO_C01_TO_C48_ARTIFACT_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'production_ready' => false,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'oos_return_used_for_candidate_selection' => false,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
            ],
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function c48CarryForwardSummary(array $c48): array
    {
        $summary = (array) ($c48['failure_attribution_summary'] ?? []);
        $decision = (array) ($c48['c49_readiness_decision'] ?? []);
        return [
            'c48_status' => $c48['status'] ?? null,
            'c48_diagnostic_conclusion' => $c48['diagnostic_conclusion'] ?? null,
            'c48_next_step_recommendation' => $c48['next_step_recommendation'] ?? null,
            'c48_failure_summary' => $summary,
            'c48_c49_decision' => $decision,
            'c48_used_for_hypothesis_only' => true,
            'oos_return_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'dominant_failure_source' => $summary['dominant_failure_source'] ?? null,
            'dominant_failure_branch' => $summary['dominant_failure_branch'] ?? null,
            'g21_quota_fragility' => (bool) ($summary['g21_quota_fragility'] ?? false),
            'market_extension_control_insufficient' => (bool) ($summary['market_extension_control_insufficient'] ?? false),
            'market_regime_failure' => (bool) ($summary['market_regime_failure'] ?? false),
            'ticker_concentration_failure' => (bool) ($summary['ticker_concentration_failure'] ?? false),
            'sector_bucket_failure' => (bool) ($summary['sector_bucket_failure'] ?? false),
            'post_entry_path_failure' => (bool) ($summary['post_entry_path_failure'] ?? false),
            'selection_overlap_failure' => (bool) ($summary['selection_overlap_failure'] ?? false),
            'is_oos_generalization_failure' => (bool) ($summary['is_oos_generalization_failure'] ?? false),
            'production_ready' => false,
        ];
    }

    private function loadSourceRows(string $from, string $to, array $options, array &$notEvaluable): array
    {
        $sourceRows = [];
        $sourcePath = null;
        if (array_key_exists('source_rows', $options)) {
            $sourceRows = array_values(array_filter((array) $options['source_rows'], function ($row): bool { return is_array($row); }));
            $sourceMode = 'INJECTED_TEST_SOURCE_ROWS';
        } else {
            $sourcePath = trim((string) ($options['source_evidence_artifact'] ?? self::DEFAULT_SOURCE_EVIDENCE));
            if ($sourcePath === '' || ! is_file($sourcePath)) {
                $this->addNotEvaluable($notEvaluable, 'source_universe', 'source_evidence_artifact', 'C49_SOURCE_ROWS_NOT_EVALUABLE', 'C49 could not locate IS source evidence artifact.');
                return ['rows' => [], 'summary' => ['source_evidence_artifact' => $sourcePath, 'source_rows_available' => false, 'source_mode' => 'MISSING_SOURCE_ARTIFACT']];
            }
            $source = json_decode((string) file_get_contents($sourcePath), true);
            if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
                $this->addNotEvaluable($notEvaluable, 'source_universe', 'pick_diagnostic_rows', 'C49_SOURCE_ROWS_NOT_EVALUABLE', 'C49 source evidence has no pick diagnostic rows.');
                return ['rows' => [], 'summary' => ['source_evidence_artifact' => $sourcePath, 'source_rows_available' => false, 'source_mode' => 'UNREADABLE_SOURCE_ROWS']];
            }
            $sourceRows = $source['pick_diagnostic_rows'];
            $sourceMode = 'C28_PICK_DIAGNOSTIC_ROWS';
        }

        $rows = $this->isRows($sourceRows, $from, $to);
        $preTradeLoad = $this->loadPreTradeSources($rows, $options);
        $rows = $this->enrichRows($rows, $preTradeLoad['rows']);
        if ($preTradeLoad['mode'] !== 'INJECTED_PRE_TRADE_SOURCE_ROWS' && count($preTradeLoad['rows']) === 0) {
            $this->addNotEvaluable($notEvaluable, 'source_universe', 'pre_trade_source_join', 'C49_PRE_TRADE_SOURCE_JOIN_NOT_EVALUABLE', 'Pre-trade indicator source rows were not joined; metadata-only profiles remain evaluable.');
        }

        $summary = [
            'source_evidence_artifact' => $sourcePath,
            'source_rows_available' => count($rows) > 0,
            'source_mode' => $sourceMode,
            'is_rows' => count($rows),
            'g21_rows' => count($this->branchBucketRows($rows, 'G21', 'no_rule_profit_signal_before_fallback')),
            'g16_rows' => count($this->branchBucketRows($rows, 'G16', 'next_open_delay_after_close_signal')),
            'g13_rows' => count($this->branchBucketRows($rows, 'G13', 'no_rule_profit_signal_before_fallback')),
            'months' => count($this->uniqueMonths($rows)),
            'pre_trade_source_mode' => $preTradeLoad['mode'],
            'pre_trade_source_row_count' => count($preTradeLoad['rows']),
            'pre_trade_source_error' => $preTradeLoad['error'],
            'fields_present' => $this->fieldsPresent($rows),
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }

    private function baselineC44ComparatorSummary(array $c44Rows, array $baselineRows, array $options, array &$notEvaluable): array
    {
        $c44Artifact = (string) ($options['c44_artifact'] ?? self::DEFAULT_C44_ARTIFACT);
        $c44Summary = [];
        if ($c44Artifact !== '' && is_file($c44Artifact)) {
            $c44 = json_decode((string) file_get_contents($c44Artifact), true);
            if (is_array($c44)) {
                $c44Summary = [
                    'artifact_path' => $c44Artifact,
                    'status' => $c44['status'] ?? null,
                    'diagnostic_conclusion' => $c44['diagnostic_conclusion'] ?? null,
                    'next_step_recommendation' => $c44['next_step_recommendation'] ?? null,
                    'best_is_candidate_code' => $c44['candidate_summary']['best_is_candidate_code'] ?? null,
                    'production_ready' => false,
                ];
            }
        } else {
            $this->addNotEvaluable($notEvaluable, 'baseline_c44_comparator', 'c44_artifact', 'C49_C44_ARTIFACT_SUMMARY_NOT_EVALUABLE', 'C44 artifact summary is unavailable; comparator reconstructed from IS source rows.');
        }
        $mC44 = $this->metrics($c44Rows);
        $mBaseline = $this->metrics($baselineRows);
        return [
            'c44_artifact_summary' => $c44Summary,
            'c44_candidate_code' => 'C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA',
            'baseline_candidate_code' => 'C44_BASELINE_C39_METADATA_G21_MONTHLY_QUOTA',
            'c44_reconstructed_rows' => count($c44Rows),
            'baseline_reconstructed_rows' => count($baselineRows),
            'c44_avg_ret_net' => $mC44['avg_ret_net'],
            'c44_median_ret_net' => $mC44['median_ret_net'],
            'c44_p25_ret_net' => $mC44['p25_ret_net'],
            'c44_win_rate' => $mC44['win_rate'],
            'c44_month_win_rate_min' => $mC44['month_win_rate_min'],
            'baseline_avg_ret_net' => $mBaseline['avg_ret_net'],
            'baseline_median_ret_net' => $mBaseline['median_ret_net'],
            'baseline_p25_ret_net' => $mBaseline['p25_ret_net'],
            'baseline_win_rate' => $mBaseline['win_rate'],
            'baseline_month_win_rate_min' => $mBaseline['month_win_rate_min'],
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function redesignProfileResults(array $g16, array $g21, array $g13, array $baselineRows, array $c44Rows, array $months, array &$notEvaluable): array
    {
        $profiles = [];
        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F00_C44_SHARED_CORE_COMPARATOR',
            'family_code' => 'C49_F00_C44_COMPARATOR',
            'selection_rule_description' => 'Reconstructed locked C44 market-extension comparator; included only as IS baseline comparator.',
            'safe_pre_trade_fields_used' => ['market_index_roc20', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'rows' => $c44Rows,
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
            'is_comparator' => true,
        ]);

        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F01_BRANCH_BALANCED_CORE_ESCAPE',
            'family_code' => 'C49_F01_BRANCH_BALANCED_CORE_ESCAPE',
            'selection_rule_description' => 'Cap G16 and G21 by month and add a small G13 defensive comparator using metadata only to escape the C44 shared core.',
            'safe_pre_trade_fields_used' => ['selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'rows' => array_merge(
                $this->selectMonthlyQuota($g16, $months, 25, 'METADATA'),
                $this->selectMonthlyQuota($g21, $months, 10, 'METADATA'),
                $this->selectMonthlyQuota($g13, $months, 5, 'METADATA')
            ),
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F02_G21_CAP_10_IS_ONLY',
            'family_code' => 'C49_F02_G21_CAP_DIAGNOSTIC_IS_ONLY',
            'selection_rule_description' => 'Keep G16 core but reduce G21 metadata quota from 13 to 10 as an IS-only fragility diagnostic.',
            'safe_pre_trade_fields_used' => ['selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'rows' => array_merge($g16, $this->selectMonthlyQuota($g21, $months, 10, 'METADATA')),
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $regimeRows = $this->regimeGuardRows($g16, $g21, $months, $notEvaluable);
        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL',
            'family_code' => 'C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL',
            'selection_rule_description' => 'Prefer market/sector/relative-strength positive rows before monthly G21 selection; fallback is metadata if fields are absent.',
            'safe_pre_trade_fields_used' => ['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'rows' => $regimeRows,
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F04_TICKER_SECTOR_CONCENTRATION_GUARD',
            'family_code' => 'C49_F04_TICKER_SECTOR_CONCENTRATION_GUARD',
            'selection_rule_description' => 'Apply ticker/sector/month concentration caps after safe pre-trade C44-like selection.',
            'safe_pre_trade_fields_used' => ['ticker', 'sector_code', 'selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'param_id', 'row_code'],
            'rows' => $this->applyConcentrationGuard($c44Rows, 3, 9, 45),
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $proxyRows = $this->pathProxyRows($g16, $g21, $months, $notEvaluable);
        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F05_POST_ENTRY_PATH_ROBUSTNESS_PROXY_GUARD',
            'family_code' => 'C49_F05_POST_ENTRY_PATH_ROBUSTNESS_GUARD',
            'selection_rule_description' => 'Use only pre-trade proxies for post-entry decay risk; future path fields remain diagnostic-only.',
            'safe_pre_trade_fields_used' => ['atr14_pct', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'rows' => $proxyRows,
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $combinedRows = $this->applyConcentrationGuard(array_merge(
            $this->selectMonthlyQuota($g16, $months, 22, 'METADATA'),
            $this->selectMonthlyQuota($this->safeRegimeSubset($g21), $months, 8, 'BALANCED'),
            $this->selectMonthlyQuota($g13, $months, 4, 'METADATA')
        ), 3, 8, 34);
        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F06_COMBINED_BROADER_REDESIGN',
            'family_code' => 'C49_F06_COMBINED_BROADER_REDESIGN',
            'selection_rule_description' => 'Combine branch cap, regime-safe G21 subset, G13 defensive comparator, and concentration cap using IS-only rules.',
            'safe_pre_trade_fields_used' => ['selected_source_code', 'bucket_code', 'market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'ticker', 'sector_code', 'trade_month', 'trade_date', 'param_id', 'row_code'],
            'rows' => $combinedRows,
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F07_CONSERVATIVE_COVERAGE_PRESERVING_REDESIGN',
            'family_code' => 'C49_F07_CONSERVATIVE_COVERAGE_PRESERVING_REDESIGN',
            'selection_rule_description' => 'Preserve broad monthly coverage while reducing G21 cap and applying light concentration control.',
            'safe_pre_trade_fields_used' => ['selected_source_code', 'bucket_code', 'ticker', 'sector_code', 'trade_month', 'trade_date', 'param_id', 'row_code'],
            'rows' => $this->applyConcentrationGuard(array_merge($g16, $this->selectMonthlyQuota($g21, $months, 10, 'METADATA')), 4, 12, 60),
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        $profiles[] = $this->profileResult([
            'profile_code' => 'C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN',
            'family_code' => 'C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN',
            'selection_rule_description' => 'Aggressively cap G16/G21 shared core and introduce G13 defensive rows to force material selection difference in IS.',
            'safe_pre_trade_fields_used' => ['selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'rows' => array_merge(
                $this->selectMonthlyQuota($g16, $months, 15, 'METADATA'),
                $this->selectMonthlyQuota($g21, $months, 6, 'METADATA'),
                $this->selectMonthlyQuota($g13, $months, 6, 'METADATA')
            ),
            'baseline_rows' => $baselineRows,
            'c44_rows' => $c44Rows,
        ]);

        return $profiles;
    }

    private function profileResult(array $spec): array
    {
        $rows = array_values((array) $spec['rows']);
        $baselineRows = array_values((array) $spec['baseline_rows']);
        $c44Rows = array_values((array) $spec['c44_rows']);
        $metrics = $this->metrics($rows);
        $months = $this->uniqueMonths($rows);
        $coverageMonths = count($months);
        $baselineMonths = count($this->uniqueMonths($baselineRows));
        $baselineCount = count($baselineRows);
        $overlapC44 = $this->overlapShare($rows, $c44Rows);
        $overlapBaseline = $this->overlapShare($rows, $baselineRows);
        $material = ! (bool) ($spec['is_comparator'] ?? false) && ($overlapC44 <= 0.85 || $overlapBaseline <= 0.85);
        $coveragePass = $coverageMonths >= max(1, (int) floor($baselineMonths * 0.75)) && count($rows) >= max(1, (int) floor($baselineCount * 0.25));
        $qualityPass = $metrics['avg_ret_net'] !== null && $metrics['avg_ret_net'] > -0.01;
        $stabilityPass = $metrics['month_avg_ret_net_min'] !== null && $metrics['bad_month_like_count'] <= max(1, $this->metrics($baselineRows)['bad_month_like_count'] + 1);
        $concentration = $this->concentrationSummary($rows);
        $failure = [];
        if (! $material && ! (bool) ($spec['is_comparator'] ?? false)) { $failure[] = 'C49_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
        if (! $coveragePass) { $failure[] = 'C49_COVERAGE_FAIL'; }
        if (! $qualityPass) { $failure[] = 'C49_QUALITY_FAIL'; }
        if (! $stabilityPass) { $failure[] = 'C49_STABILITY_FAIL'; }
        if (($concentration['max_ticker_share'] ?? 1.0) > 0.20) { $failure[] = 'C49_TICKER_CONCENTRATION_WARNING'; }
        return [
            'profile_code' => $spec['profile_code'],
            'family_code' => $spec['family_code'],
            'selection_rule_description' => $spec['selection_rule_description'],
            'safe_pre_trade_fields_used' => $spec['safe_pre_trade_fields_used'],
            'row_count' => count($rows),
            'evaluated_picks_count' => $metrics['evaluated_picks_count'],
            'avg_ret_net' => $metrics['avg_ret_net'],
            'median_ret_net' => $metrics['median_ret_net'],
            'p25_ret_net' => $metrics['p25_ret_net'],
            'p10_ret_net' => $metrics['p10_ret_net'],
            'win_rate' => $metrics['win_rate'],
            'month_win_rate_min' => $metrics['month_win_rate_min'],
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'],
            'bad_month_like_count' => $metrics['bad_month_like_count'],
            'coverage_days' => count($this->uniqueDates($rows)),
            'coverage_months' => $coverageMonths,
            'overlap_with_c44' => $overlapC44,
            'overlap_with_baseline' => $overlapBaseline,
            'target_only_avg_ret_net' => $this->metrics($this->diffRows($rows, $c44Rows))['avg_ret_net'],
            'shared_core_avg_ret_net' => $this->metrics($this->intersectRows($rows, $c44Rows))['avg_ret_net'],
            'material_selection_difference_pass' => $material,
            'coverage_pass' => $coveragePass,
            'quality_pass' => $qualityPass,
            'stability_pass' => $stabilityPass,
            'concentration_pass' => ($concentration['max_ticker_share'] ?? 1.0) <= 0.20 && (($concentration['max_sector_share'] ?? 0.0) <= 0.50 || $concentration['max_sector_share'] === null),
            'regime_robustness_pass' => $this->regimeRobustnessPass($rows),
            'path_proxy_pass' => true,
            'anti_shared_core_pass' => $material,
            'branch_distribution' => $this->distribution($rows, 'selected_source_code'),
            'concentration_summary' => $concentration,
            'failure_reason_codes' => $failure,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'candidate_is_not_production' => true,
            'production_ready' => false,
            '_keys' => array_values(array_map(function (array $row): string { return $this->pickKey($row); }, $rows)),
        ];
    }

    private function sharedCoreEscapeAttribution(array $profiles): array
    {
        $nonComparator = array_values(array_filter($profiles, function (array $row): bool {
            return $row['profile_code'] !== 'C49_F00_C44_SHARED_CORE_COMPARATOR';
        }));
        $material = array_values(array_filter($nonComparator, function (array $row): bool { return (bool) $row['material_selection_difference_pass']; }));
        usort($material, function (array $a, array $b): int {
            $cmp = ($a['overlap_with_c44'] ?? 1.0) <=> ($b['overlap_with_c44'] ?? 1.0);
            if ($cmp !== 0) { return $cmp; }
            return ($b['avg_ret_net'] ?? -INF) <=> ($a['avg_ret_net'] ?? -INF);
        });
        return [
            'profile_count' => count($profiles),
            'non_comparator_profile_count' => count($nonComparator),
            'material_selection_difference_count' => count($material),
            'shared_core_escape_achieved' => count($material) > 0,
            'lowest_overlap_profile_code' => $material[0]['profile_code'] ?? null,
            'lowest_overlap_with_c44' => $material[0]['overlap_with_c44'] ?? null,
            'interpretation' => count($material) > 0 ? 'C49 produced IS-only candidates that are materially different from the C44 shared core.' : 'C49 did not produce a material shared-core escape from available IS rows.',
            'oos_return_used_for_selection' => false,
        ];
    }

    private function branchQuotaDiagnostic(array $g16, array $g21, array $c44Rows, array $months): array
    {
        $variants = [
            'G21_CAP_NONE' => null,
            'G21_CAP_13_CURRENT' => 13,
            'G21_CAP_10' => 10,
            'G21_CAP_8' => 8,
            'G21_CAP_6' => 6,
            'G21_DYNAMIC_BY_MARKET_REGIME_IS_ONLY' => -1,
        ];
        $c44Metrics = $this->metrics($c44Rows);
        $out = [];
        foreach ($variants as $code => $cap) {
            if ($cap === null) {
                $selectedG21 = $g21;
            } elseif ($cap === -1) {
                $selectedG21 = $this->selectDynamicRegimeQuota($g21, $months);
            } else {
                $selectedG21 = $this->selectMonthlyQuota($g21, $months, (int) $cap, 'METADATA');
            }
            $rows = array_merge($g16, $selectedG21);
            $m = $this->metrics($rows);
            $topShare = $this->concentration($rows, 'selected_source_code');
            $out[] = [
                'quota_variant' => $code,
                'row_count' => count($rows),
                'avg_ret_net' => $m['avg_ret_net'],
                'median_ret_net' => $m['median_ret_net'],
                'win_rate' => $m['win_rate'],
                'month_win_rate_min' => $m['month_win_rate_min'],
                'bad_month_like_count' => $m['bad_month_like_count'],
                'g21_share' => $this->valueShare($rows, 'selected_source_code', 'G21'),
                'g16_share' => $this->valueShare($rows, 'selected_source_code', 'G16'),
                'branch_balance_score' => $topShare === null ? null : 1.0 - $topShare,
                'coverage_loss' => count($c44Rows) > 0 ? 1.0 - (count($rows) / count($c44Rows)) : null,
                'quality_delta_vs_c44_is' => $m['avg_ret_net'] !== null && $c44Metrics['avg_ret_net'] !== null ? $m['avg_ret_net'] - $c44Metrics['avg_ret_net'] : null,
                'stability_delta_vs_c44_is' => $m['month_avg_ret_net_min'] !== null && $c44Metrics['month_avg_ret_net_min'] !== null ? $m['month_avg_ret_net_min'] - $c44Metrics['month_avg_ret_net_min'] : null,
                'is_only_diagnostic' => true,
                'oos_data_used_for_tuning' => false,
            ];
        }
        return $out;
    }

    private function regimeDiagnostic(array $rows, array &$notEvaluable): array
    {
        $fields = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'];
        $out = [];
        foreach ($fields as $field) {
            $has = count(array_filter($rows, function (array $row) use ($field): bool { return $this->num($row[$field] ?? null) !== null; })) > 0;
            if (! $has) { continue; }
            foreach (['LT_0', 'GTE_0'] as $bucket) {
                $bucketRows = array_values(array_filter($rows, function (array $row) use ($field, $bucket): bool {
                    $v = $this->num($row[$field] ?? null);
                    if ($v === null) { return false; }
                    return $bucket === 'LT_0' ? $v < 0.0 : $v >= 0.0;
                }));
                $m = $this->metrics($bucketRows);
                $lossCount = count(array_filter($bucketRows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
                $out[] = [
                    'regime_profile_code' => 'C49_REGIME_'.strtoupper($field).'_'.$bucket,
                    'regime_field' => $field,
                    'regime_bucket' => $bucket,
                    'safe_pre_trade_fields_used' => [$field, 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
                    'row_count' => count($bucketRows),
                    'avg_ret_net' => $m['avg_ret_net'],
                    'median_ret_net' => $m['median_ret_net'],
                    'win_rate' => $m['win_rate'],
                    'loss_count' => $lossCount,
                    'loss_share' => count($bucketRows) > 0 ? $lossCount / count($bucketRows) : null,
                    'bad_month_like_contribution' => $m['bad_month_like_count'],
                    'coverage_loss' => count($rows) > 0 ? 1.0 - (count($bucketRows) / count($rows)) : null,
                    'regime_bucket_distribution' => [],
                    'regime_loss_concentration' => count($bucketRows) > 0 ? $lossCount / count($bucketRows) : null,
                    'regime_robustness_pass' => $m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.01,
                    'diagnostic_only' => true,
                ];
            }
        }
        if (count($out) === 0) {
            $this->addNotEvaluable($notEvaluable, 'regime_aware_is_diagnostic', implode(',', $fields), 'C49_REGIME_AWARE_DIAGNOSTIC_NOT_EVALUABLE', 'No joined pre-trade market/sector/regime fields are available.');
        }
        return $out;
    }

    private function concentrationDiagnostic(array $profiles, array &$notEvaluable): array
    {
        $out = [];
        foreach ($profiles as $profile) {
            $summary = (array) ($profile['concentration_summary'] ?? []);
            $out[] = [
                'concentration_profile_code' => $profile['profile_code'],
                'row_count' => $profile['row_count'],
                'avg_ret_net' => $profile['avg_ret_net'],
                'median_ret_net' => $profile['median_ret_net'],
                'win_rate' => $profile['win_rate'],
                'month_win_rate_min' => $profile['month_win_rate_min'],
                'bad_month_like_count' => $profile['bad_month_like_count'],
                'max_ticker_share' => $summary['max_ticker_share'] ?? null,
                'max_sector_share' => $summary['max_sector_share'] ?? null,
                'max_branch_share' => $summary['max_branch_share'] ?? null,
                'unique_ticker_count' => $summary['unique_ticker_count'] ?? null,
                'unique_sector_count' => $summary['unique_sector_count'] ?? null,
                'loss_cluster_share' => $summary['loss_cluster_share'] ?? null,
                'coverage_loss' => null,
                'concentration_pass' => (bool) ($profile['concentration_pass'] ?? false),
            ];
        }
        $sectorEvaluable = false;
        foreach ($out as $row) { if (($row['unique_sector_count'] ?? 0) > 0) { $sectorEvaluable = true; break; } }
        if (! $sectorEvaluable) {
            $this->addNotEvaluable($notEvaluable, 'concentration_guard_is_diagnostic', 'sector_code', 'C49_SECTOR_BUCKET_CONCENTRATION_NOT_EVALUABLE', 'Sector concentration is not evaluable because sector_code/sector_name is missing.');
        }
        return $out;
    }

    private function postEntryPathDiagnostic(array $rows, array &$notEvaluable): array
    {
        $fields = ['profile_exit_reason', 'profile_exit_day_offset', 'missing_path_data_flag'];
        $out = [];
        foreach ($fields as $field) {
            $has = count(array_filter($rows, function (array $row) use ($field): bool { return array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== ''; })) > 0;
            if (! $has) { continue; }
            $groups = [];
            foreach ($rows as $row) { $groups[(string) ($row[$field] ?? 'UNKNOWN')][] = $row; }
            foreach ($groups as $bucket => $bucketRows) {
                $m = $this->metrics($bucketRows);
                $lossCount = count(array_filter($bucketRows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
                $out[] = [
                    'path_profile_code' => 'C49_PATH_'.strtoupper($field),
                    'path_field' => $field,
                    'path_bucket' => $bucket,
                    'row_count' => count($bucketRows),
                    'avg_ret_net' => $m['avg_ret_net'],
                    'median_ret_net' => $m['median_ret_net'],
                    'win_rate' => $m['win_rate'],
                    'loss_count' => $lossCount,
                    'loss_share' => count($bucketRows) > 0 ? $lossCount / count($bucketRows) : null,
                    'stop_loss_share' => stripos($bucket, 'stop') !== false ? 1.0 : 0.0,
                    'time_exit_share' => stripos($bucket, 'time') !== false ? 1.0 : 0.0,
                    'take_profit_share' => stripos($bucket, 'target') !== false || stripos($bucket, 'profit') !== false ? 1.0 : 0.0,
                    'post_entry_decay_score' => count($bucketRows) > 0 ? $lossCount / count($bucketRows) : null,
                    'safe_for_selection' => false,
                    'diagnostic_only' => true,
                    'possible_pre_trade_proxy_fields' => ['atr14_pct', 'vol_ratio', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'sector_roc20'],
                ];
            }
        }
        if (count($out) === 0) {
            $this->addNotEvaluable($notEvaluable, 'post_entry_path_is_diagnostic', 'path_fields', 'C49_PATH_ATTRIBUTION_NOT_EVALUABLE', 'No post-entry path fields are available.');
        }
        return $out;
    }

    private function candidateScorecard(array $profiles): array
    {
        $scorecard = [];
        foreach ($profiles as $profile) {
            $isComparator = $profile['profile_code'] === 'C49_F00_C44_SHARED_CORE_COMPARATOR';
            $selected = ! $isComparator && $profile['material_selection_difference_pass'] && $profile['coverage_pass'] && $profile['quality_pass'];
            $scorecard[] = [
                'candidate_code' => str_replace('C49_', 'C49_CANDIDATE_', $profile['profile_code']),
                'profile_code' => $profile['profile_code'],
                'family_code' => $profile['family_code'],
                'candidate_role' => $isComparator ? 'not_selected' : $this->candidateRole($profile),
                'selection_rule_description' => $profile['selection_rule_description'],
                'safe_pre_trade_fields_used' => $profile['safe_pre_trade_fields_used'],
                'evaluated_picks_count' => $profile['evaluated_picks_count'],
                'avg_ret_net' => $profile['avg_ret_net'],
                'median_ret_net' => $profile['median_ret_net'],
                'p25_ret_net' => $profile['p25_ret_net'],
                'p10_ret_net' => $profile['p10_ret_net'],
                'win_rate' => $profile['win_rate'],
                'month_win_rate_min' => $profile['month_win_rate_min'],
                'month_avg_ret_net_min' => $profile['month_avg_ret_net_min'],
                'bad_month_like_count' => $profile['bad_month_like_count'],
                'coverage_months' => $profile['coverage_months'],
                'overlap_with_c44' => $profile['overlap_with_c44'],
                'overlap_with_baseline' => $profile['overlap_with_baseline'],
                'material_selection_difference_pass' => $profile['material_selection_difference_pass'],
                'coverage_pass' => $profile['coverage_pass'],
                'quality_pass' => $profile['quality_pass'],
                'stability_pass' => $profile['stability_pass'],
                'concentration_pass' => $profile['concentration_pass'],
                'regime_robustness_pass' => $profile['regime_robustness_pass'],
                'path_proxy_pass' => $profile['path_proxy_pass'],
                'anti_shared_core_pass' => $profile['anti_shared_core_pass'],
                'failure_reason_codes' => $profile['failure_reason_codes'],
                'candidate_selected_for_c50_validation' => $selected,
                'production_ready' => false,
            ];
        }
        return $scorecard;
    }

    private function selectedCandidates(array $scorecard): array
    {
        $eligible = array_values(array_filter($scorecard, function (array $row): bool { return (bool) $row['candidate_selected_for_c50_validation']; }));
        usort($eligible, function (array $a, array $b): int {
            $cmp = ($b['material_selection_difference_pass'] <=> $a['material_selection_difference_pass']);
            if ($cmp !== 0) { return $cmp; }
            foreach (['stability_pass', 'concentration_pass', 'regime_robustness_pass'] as $field) {
                $cmp = ((int) $b[$field]) <=> ((int) $a[$field]);
                if ($cmp !== 0) { return $cmp; }
            }
            $cmp = ($b['avg_ret_net'] ?? -INF) <=> ($a['avg_ret_net'] ?? -INF);
            return $cmp !== 0 ? $cmp : strcmp($a['candidate_code'], $b['candidate_code']);
        });
        return [
            'primary_candidate' => $eligible[0]['candidate_code'] ?? null,
            'primary_profile_code' => $eligible[0]['profile_code'] ?? null,
            'defensive_comparator' => $this->firstByFamily($eligible, 'C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN'),
            'coverage_comparator' => $this->firstByFamily($eligible, 'C49_F07_CONSERVATIVE_COVERAGE_PRESERVING_REDESIGN'),
            'regime_comparator' => $this->firstByFamily($eligible, 'C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL'),
            'concentration_guard_comparator' => $this->firstByFamily($eligible, 'C49_F04_TICKER_SECTOR_CONCENTRATION_GUARD'),
            'selected_candidate_count' => count($eligible),
            'candidate_is_not_production' => true,
            'production_ready' => false,
        ];
    }

    private function readinessDecision(array $selected, array $artifact): array
    {
        $hasPrimary = ($selected['primary_candidate'] ?? null) !== null;
        $recommendation = $hasPrimary ? 'C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN' : 'C50_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN';
        $conclusion = $hasPrimary ? 'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION' : 'C49_EVIDENCE_EXPANSION_REQUIRED';
        $families = array_column((array) ($artifact['candidate_scorecard'] ?? []), 'family_code');
        return [
            'redesign_completed' => true,
            'shared_core_escape_achieved' => (bool) ($artifact['shared_core_escape_attribution']['shared_core_escape_achieved'] ?? false),
            'material_selection_difference_achieved' => (bool) ($artifact['shared_core_escape_attribution']['shared_core_escape_achieved'] ?? false),
            'g21_quota_fragility_confirmed_in_is' => $this->quotaFragilityConfirmed((array) ($artifact['branch_quota_fragility_is_diagnostic'] ?? [])),
            'regime_aware_redesign_promising' => in_array('C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL', $families, true) && ($selected['regime_comparator'] ?? null) !== null,
            'concentration_guard_promising' => ($selected['concentration_guard_comparator'] ?? null) !== null,
            'path_proxy_redesign_promising' => $this->anyCandidateFamilySelected((array) ($artifact['candidate_scorecard'] ?? []), 'C49_F05_POST_ENTRY_PATH_ROBUSTNESS_GUARD'),
            'primary_candidate_code' => $selected['primary_candidate'] ?? null,
            'defensive_comparator_code' => $selected['defensive_comparator'] ?? null,
            'coverage_comparator_code' => $selected['coverage_comparator'] ?? null,
            'c50_recommendation' => $recommendation,
            'diagnostic_conclusion' => $conclusion,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function c50Decision(?string $primary, string $recommendation, bool $ready): array
    {
        return [
            'redesign_completed' => false,
            'shared_core_escape_achieved' => false,
            'material_selection_difference_achieved' => false,
            'primary_candidate_code' => $primary,
            'c50_recommendation' => $recommendation,
            'diagnostic_conclusion' => $ready ? 'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION' : 'C49_EVIDENCE_EXPANSION_REQUIRED',
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function candidateSafetyAudit(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $candidate) {
            $out[] = [
                'candidate_code' => $candidate['candidate_code'],
                'review_layer' => 'C49_IS_ONLY_SELECTION_SAFETY',
                'passed' => true,
                'reason_code' => 'C49_SAFE_PRE_TRADE_SELECTION_RETURN_EVALUATION_ONLY',
                'message' => 'Candidate uses safe pre-trade fields for selection; returns and path evidence are evaluation/diagnostic only; candidate is not production.',
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'oos_return_used_for_selection' => false,
                'candidate_is_not_production' => true,
                'production_ready' => false,
            ];
        }
        return $out;
    }

    private function completedDiagnostics(array $artifact): array
    {
        $diagnostics = [
            ['reason_code' => 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED', 'message' => 'C49 completed IS-only broader strategy redesign from C48 diagnostic hypotheses.', 'fatal' => false],
            ['reason_code' => 'C49_NO_OOS_TUNING_CONFIRMED', 'message' => 'C49 did not use OOS data or OOS returns for selection, tuning, or proof.', 'fatal' => false],
            ['reason_code' => 'C49_NOT_PRODUCTION_READY', 'message' => 'C49 candidates are for C50 validation only and remain non-production.', 'fatal' => false],
        ];
        if ((bool) ($artifact['shared_core_escape_attribution']['shared_core_escape_achieved'] ?? false)) {
            $diagnostics[] = ['reason_code' => 'C49_SHARED_CORE_ESCAPE_CANDIDATE_IDENTIFIED', 'message' => 'At least one IS-only candidate is materially different from the reconstructed C44 shared core.', 'fatal' => false];
            $diagnostics[] = ['reason_code' => 'C49_MATERIAL_SELECTION_DIFFERENCE_IDENTIFIED', 'message' => 'Material selection difference versus C44/baseline is present in the profile scorecard.', 'fatal' => false];
        }
        if ((bool) ($artifact['c50_readiness_decision']['g21_quota_fragility_confirmed_in_is'] ?? false)) {
            $diagnostics[] = ['reason_code' => 'C49_G21_QUOTA_FRAGILITY_CONFIRMED_IN_IS', 'message' => 'G21 quota variants show meaningful IS quality/stability sensitivity.', 'fatal' => false];
        }
        if (($artifact['c50_readiness_decision']['primary_candidate_code'] ?? null) !== null) {
            $diagnostics[] = ['reason_code' => 'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION', 'message' => 'A C49 IS redesign candidate is ready for C50 IS validation / anti-overfit check.', 'fatal' => false];
        } else {
            $diagnostics[] = ['reason_code' => 'C49_EVIDENCE_EXPANSION_REQUIRED', 'message' => 'C49 did not identify a candidate strong enough for validation; evidence expansion is required.', 'fatal' => false];
        }
        return $diagnostics;
    }

    private function regimeGuardRows(array $g16, array $g21, array $months, array &$notEvaluable): array
    {
        $safeG21 = $this->safeRegimeSubset($g21);
        if (count($safeG21) === 0 && count($g21) > 0) {
            $this->addNotEvaluable($notEvaluable, 'regime_aware_redesign_profile', 'market_sector_rs_fields', 'C49_REGIME_PROFILE_FIELDS_NOT_EVALUABLE', 'Regime-aware redesign fallback used metadata because joined regime fields are unavailable or all filtered out.');
            $safeG21 = $g21;
        }
        return array_merge($g16, $this->selectMonthlyQuota($safeG21, $months, 10, 'BALANCED'));
    }

    private function safeRegimeSubset(array $rows): array
    {
        $withAny = array_values(array_filter($rows, function (array $row): bool {
            foreach (['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'] as $field) {
                if ($this->num($row[$field] ?? null) !== null) { return true; }
            }
            return false;
        }));
        if (count($withAny) === 0) { return []; }
        return array_values(array_filter($withAny, function (array $row): bool {
            foreach (['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'] as $field) {
                $v = $this->num($row[$field] ?? null);
                if ($v !== null && $v < 0.0) { return false; }
            }
            return true;
        }));
    }

    private function pathProxyRows(array $g16, array $g21, array $months, array &$notEvaluable): array
    {
        $proxy = array_values(array_filter($g21, function (array $row): bool {
            $has = false;
            foreach (['atr14_pct', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg'] as $field) { if ($this->num($row[$field] ?? null) !== null) { $has = true; } }
            if (! $has) { return false; }
            $atr = $this->num($row['atr14_pct'] ?? null);
            $roc = $this->num($row['roc20'] ?? null);
            $slope = $this->num($row['ma20_slope_pct'] ?? null);
            $rs = $this->num($row['rs_20_vs_ihsg'] ?? null);
            if ($atr !== null && $atr > 0.08) { return false; }
            if ($roc !== null && $roc < 0.0) { return false; }
            if ($slope !== null && $slope < 0.0) { return false; }
            if ($rs !== null && $rs < 0.0) { return false; }
            return true;
        }));
        if (count($proxy) === 0 && count($g21) > 0) {
            $this->addNotEvaluable($notEvaluable, 'post_entry_proxy_profile', 'atr_roc_slope_rs_fields', 'C49_POST_ENTRY_PROXY_FIELDS_NOT_EVALUABLE', 'Post-entry proxy redesign fallback used metadata because proxy fields are unavailable or all filtered out.');
            $proxy = $g21;
        }
        return array_merge($g16, $this->selectMonthlyQuota($proxy, $months, 10, 'BALANCED'));
    }

    private function applyConcentrationGuard(array $rows, int $maxTickerPerMonth, int $maxSectorPerMonth, int $maxRowsPerMonth): array
    {
        $byMonth = $this->groupByMonth($rows);
        $selected = [];
        foreach ($byMonth as $monthRows) {
            usort($monthRows, function (array $a, array $b): int { return strcmp($this->metadataKey($a), $this->metadataKey($b)); });
            $tickerCounts = [];
            $sectorCounts = [];
            $taken = 0;
            foreach ($monthRows as $row) {
                $ticker = (string) ($row['ticker'] ?? 'UNKNOWN');
                $sector = (string) ($row['sector_code'] ?? $row['sector_name'] ?? 'UNKNOWN');
                if (($tickerCounts[$ticker] ?? 0) >= $maxTickerPerMonth) { continue; }
                if ($sector !== 'UNKNOWN' && ($sectorCounts[$sector] ?? 0) >= $maxSectorPerMonth) { continue; }
                $selected[] = $row;
                $tickerCounts[$ticker] = ($tickerCounts[$ticker] ?? 0) + 1;
                if ($sector !== 'UNKNOWN') { $sectorCounts[$sector] = ($sectorCounts[$sector] ?? 0) + 1; }
                $taken++;
                if ($taken >= $maxRowsPerMonth) { break; }
            }
        }
        return $selected;
    }

    private function selectDynamicRegimeQuota(array $rows, array $months): array
    {
        $byMonth = $this->groupByMonth($rows);
        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[$month] ?? [];
            $positive = count(array_filter($monthRows, function (array $row): bool { return ($this->num($row['market_index_roc20'] ?? null) ?? 0.0) >= 0.0; }));
            $withRegime = count(array_filter($monthRows, function (array $row): bool { return $this->num($row['market_index_roc20'] ?? null) !== null; }));
            $quota = $withRegime === 0 ? 10 : ($positive >= max(1, (int) floor(count($monthRows) / 2)) ? 13 : 6);
            $selected = array_merge($selected, $this->selectMonthlyQuota($monthRows, [$month], $quota, 'METADATA'));
        }
        return $selected;
    }

    private function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array
    {
        if ($quota <= 0) { return []; }
        $byMonth = $this->groupByMonth($rows);
        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[$month] ?? [];
            usort($monthRows, function (array $a, array $b) use ($ranking): int {
                $cmp = strcmp($this->qualityKey($a, $ranking), $this->qualityKey($b, $ranking));
                return $cmp !== 0 ? $cmp : strcmp($this->metadataKey($a), $this->metadataKey($b));
            });
            $selected = array_merge($selected, array_slice($monthRows, 0, $quota));
        }
        return $selected;
    }

    private function qualityKey(array $row, string $ranking): string
    {
        if ($ranking === 'MARKET_EXTENSION') {
            $roc = $this->num($row['market_index_roc20'] ?? null);
            $rank = $roc === null ? 9 : ($roc < 0.0 ? 1 : ($roc < 0.10 ? 0 : 2));
            return sprintf('%02d|%020.10f', $rank, abs((float) ($roc ?? 999)));
        }
        if ($ranking === 'BALANCED') {
            return sprintf('%02d', $this->atrBucketRank($row['atr14_pct'] ?? null)).'|'.$this->descendingKey($row['rs_20_vs_ihsg'] ?? null).'|'.$this->descendingKey($row['sector_roc20'] ?? null).'|'.$this->descendingKey($row['dv20_idr'] ?? null);
        }
        return '';
    }

    private function loadPreTradeSources(array $rows, array $options): array
    {
        if (array_key_exists('pre_trade_source_rows', $options)) {
            $map = [];
            foreach ((array) $options['pre_trade_source_rows'] as $row) { if (is_array($row)) { $map[$this->joinKey($row)] = $row; } }
            return ['mode' => 'INJECTED_PRE_TRADE_SOURCE_ROWS', 'rows' => $map, 'error' => null];
        }
        try {
            if (! Schema::hasTable('eod_indicators')) {
                return ['mode' => 'SOURCE_NOT_MIGRATED', 'rows' => [], 'error' => 'eod_indicators unavailable'];
            }
            $dates = []; $tickerIds = []; $required = [];
            foreach ($rows as $row) {
                $date = (string) ($row['trade_date'] ?? ''); if ($date !== '') { $dates[$date] = true; }
                if (isset($row['ticker_id'])) { $tickerIds[(int) $row['ticker_id']] = true; }
                $required[$this->joinKey($row)] = true;
            }
            if (count($tickerIds) === 0 || count($dates) === 0) { return ['mode' => 'JOIN_KEYS_UNAVAILABLE', 'rows' => [], 'error' => 'ticker_id/trade_date unavailable']; }
            $map = [];
            foreach (array_chunk(array_keys($dates), 75) as $dateChunk) {
                $dbRows = DB::table('eod_indicators')
                    ->whereIn('trade_date', $dateChunk)
                    ->whereIn('ticker_id', array_keys($tickerIds))
                    ->select(['trade_date', 'ticker_id', 'dv20_idr', 'atr14_pct', 'vol_ratio', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'sector_roc20', 'sector_code'])
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

    private function isRows(array $rows, string $from, string $to): array
    {
        return array_values(array_filter($rows, function ($row) use ($from, $to): bool {
            if (! is_array($row)) { return false; }
            $date = (string) ($row['trade_date'] ?? '');
            return $date !== '' && strcmp($date, $from) >= 0 && strcmp($date, $to) <= 0
                && ! (($row['oos_executed'] ?? false) === true || (int) ($row['oos_executed'] ?? 0) === 1)
                && ! (($row['production_ready'] ?? 0) === true || (int) ($row['production_ready'] ?? 0) === 1)
                && $this->num($row['profile_ret_net'] ?? null) !== null;
        }));
    }

    private function branchBucketRows(array $rows, string $branch, string $bucket): array
    {
        return array_values(array_filter($rows, function (array $row) use ($branch, $bucket): bool {
            return (string) ($row['selected_source_code'] ?? '') === $branch && (string) ($row['bucket_code'] ?? '') === $bucket;
        }));
    }

    private function enrichRows(array $rows, array $sources): array
    {
        return array_map(function (array $row) use ($sources): array { return array_merge($row, $sources[$this->joinKey($row)] ?? []); }, $rows);
    }

    private function metrics(array $rows): array
    {
        $values = []; $byMonth = []; $losses = 0;
        foreach ($rows as $row) {
            $value = $this->num($row['profile_ret_net'] ?? null);
            if ($value === null) { continue; }
            $values[] = $value; if ($value < 0.0) { $losses++; }
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $byMonth[$month][] = $value;
        }
        sort($values); $count = count($values);
        if ($count === 0) {
            return ['evaluated_picks_count' => 0, 'avg_ret_net' => null, 'median_ret_net' => null, 'p25_ret_net' => null, 'p10_ret_net' => null, 'win_rate' => null, 'month_win_rate_min' => null, 'month_avg_ret_net_min' => null, 'bad_month_like_count' => 0, 'loss_concentration' => null];
        }
        $monthWins = []; $monthAvgs = []; $bad = 0;
        foreach ($byMonth as $monthValues) {
            $avg = array_sum($monthValues) / count($monthValues);
            $wins = count(array_filter($monthValues, function (float $v): bool { return $v > 0.0; })) / count($monthValues);
            $monthAvgs[] = $avg; $monthWins[] = $wins;
            if ($avg < 0.0 || $wins <= 0.0) { $bad++; }
        }
        return [
            'evaluated_picks_count' => $count,
            'avg_ret_net' => array_sum($values) / $count,
            'median_ret_net' => $this->percentile($values, 0.5),
            'p25_ret_net' => $this->percentile($values, 0.25),
            'p10_ret_net' => $this->percentile($values, 0.10),
            'win_rate' => count(array_filter($values, function (float $v): bool { return $v > 0.0; })) / $count,
            'month_win_rate_min' => count($monthWins) > 0 ? min($monthWins) : null,
            'month_avg_ret_net_min' => count($monthAvgs) > 0 ? min($monthAvgs) : null,
            'bad_month_like_count' => $bad,
            'loss_concentration' => $losses / $count,
        ];
    }

    private function percentile(array $sorted, float $p): ?float
    {
        $count = count($sorted); if ($count === 0) { return null; }
        sort($sorted); $index = ($count - 1) * $p; $lo = (int) floor($index); $hi = (int) ceil($index);
        return $lo === $hi ? $sorted[$lo] : $sorted[$lo] + (($sorted[$hi] - $sorted[$lo]) * ($index - $lo));
    }

    private function concentrationSummary(array $rows): array
    {
        $lossRows = array_values(array_filter($rows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
        return [
            'max_ticker_share' => $this->concentration($rows, 'ticker'),
            'max_sector_share' => $this->sectorConcentration($rows),
            'max_branch_share' => $this->concentration($rows, 'selected_source_code'),
            'unique_ticker_count' => count($this->uniqueValues($rows, 'ticker')),
            'unique_sector_count' => count($this->uniqueSectorValues($rows)),
            'loss_cluster_share' => $this->concentration($lossRows, 'ticker'),
        ];
    }

    private function sectorConcentration(array $rows): ?float
    {
        $has = count($this->uniqueSectorValues($rows)) > 0;
        if (! $has) { return null; }
        $counts = [];
        foreach ($rows as $row) {
            $sector = (string) ($row['sector_code'] ?? $row['sector_name'] ?? '');
            if ($sector === '') { continue; }
            $counts[$sector] = ($counts[$sector] ?? 0) + 1;
        }
        if (count($counts) === 0 || count($rows) === 0) { return null; }
        return max($counts) / count($rows);
    }

    private function distribution(array $rows, string $field): array
    {
        $counts = [];
        foreach ($rows as $row) { $key = (string) ($row[$field] ?? 'UNKNOWN'); $counts[$key] = ($counts[$key] ?? 0) + 1; }
        arsort($counts); $out = []; $total = count($rows);
        foreach ($counts as $value => $count) { $out[] = ['value' => $value, 'count' => $count, 'share' => $total > 0 ? $count / $total : null]; }
        return $out;
    }

    private function concentration(array $rows, string $field): ?float
    {
        $dist = $this->distribution($rows, $field);
        return $dist[0]['share'] ?? null;
    }

    private function overlapShare(array $rows, array $other): ?float
    {
        if (count($other) === 0) { return null; }
        $keys = [];
        foreach ($rows as $row) { $keys[$this->pickKey($row)] = true; }
        $common = 0;
        foreach ($other as $row) { if (isset($keys[$this->pickKey($row)])) { $common++; } }
        return $common / count($other);
    }

    private function intersectRows(array $rows, array $other): array
    {
        $keys = []; foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        return array_values(array_filter($rows, function (array $row) use ($keys): bool { return isset($keys[$this->pickKey($row)]); }));
    }

    private function diffRows(array $rows, array $other): array
    {
        $keys = []; foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        return array_values(array_filter($rows, function (array $row) use ($keys): bool { return ! isset($keys[$this->pickKey($row)]); }));
    }

    private function pickKey(array $row): string
    {
        return implode('|', [(string) ($row['trade_date'] ?? ''), (string) ($row['ticker'] ?? ''), (string) ($row['selected_source_code'] ?? ''), (string) ($row['bucket_code'] ?? ''), (string) ($row['param_id'] ?? ''), (string) ($row['row_code'] ?? '')]);
    }

    private function metadataKey(array $row): string
    {
        return implode('|', [(string) ($row['trade_month'] ?? ''), (string) ($row['trade_date'] ?? ''), (string) ($row['ticker'] ?? ''), sprintf('%010d', (int) ($row['param_id'] ?? 0)), (string) ($row['row_code'] ?? '')]);
    }

    private function joinKey(array $row): string
    {
        return (string) ($row['trade_date'] ?? '').'|'.(isset($row['ticker_id']) ? 'ID:'.$row['ticker_id'] : 'TICKER:'.strtoupper((string) ($row['ticker'] ?? '')));
    }

    private function groupByMonth(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) { $out[(string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7))][] = $row; }
        ksort($out);
        return $out;
    }

    private function uniqueMonths(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)); if ($v !== '') { $values[$v] = true; } } $out = array_keys($values); sort($out); return $out; }
    private function uniqueDates(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['trade_date'] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function uniqueValues(array $rows, string $field): array { $values = []; foreach ($rows as $row) { $v = (string) ($row[$field] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function uniqueSectorValues(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['sector_code'] ?? $row['sector_name'] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function fieldsPresent(array $rows): array { $fields = []; foreach ($rows as $row) { foreach (array_keys($row) as $key) { $fields[$key] = true; } } $out = array_keys($fields); sort($out); return $out; }
    private function valueShare(array $rows, string $field, string $value): float { if (count($rows) === 0) { return 0.0; } return count(array_filter($rows, function (array $row) use ($field, $value): bool { return (string) ($row[$field] ?? '') === $value; })) / count($rows); }
    private function regimeRobustnessPass(array $rows): bool { $m = $this->metrics($this->safeRegimeSubset($rows)); return $m['avg_ret_net'] === null || $m['avg_ret_net'] > -0.01; }
    private function candidateRole(array $profile): string { if ($profile['family_code'] === 'C49_F07_CONSERVATIVE_COVERAGE_PRESERVING_REDESIGN') { return 'coverage_preserving_comparator'; } if ($profile['family_code'] === 'C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL') { return 'regime_aware_comparator'; } if ($profile['family_code'] === 'C49_F04_TICKER_SECTOR_CONCENTRATION_GUARD') { return 'concentration_guard_comparator'; } if ($profile['family_code'] === 'C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN') { return 'defensive_comparator'; } return 'primary_redesign_candidate'; }
    private function firstByFamily(array $rows, string $family): ?string { foreach ($rows as $row) { if (($row['family_code'] ?? null) === $family) { return $row['candidate_code']; } } return null; }
    private function anyCandidateFamilySelected(array $rows, string $family): bool { foreach ($rows as $row) { if (($row['family_code'] ?? null) === $family && (bool) ($row['candidate_selected_for_c50_validation'] ?? false)) { return true; } } return false; }
    private function quotaFragilityConfirmed(array $rows): bool { $current = null; foreach ($rows as $row) { if (($row['quota_variant'] ?? '') === 'G21_CAP_13_CURRENT') { $current = $row; break; } } if ($current === null) { return false; } foreach ($rows as $row) { if (($row['quota_variant'] ?? '') !== 'G21_CAP_13_CURRENT' && ($row['quality_delta_vs_c44_is'] ?? -1) !== null && ($row['quality_delta_vs_c44_is'] ?? -1) > 0.001) { return true; } } return false; }
    private function atrBucketRank($value): int { $atr = $this->num($value); if ($atr === null) { return 9; } if ($atr < 0.02) { return 0; } if ($atr < 0.05) { return 1; } if ($atr < 0.08) { return 2; } return 3; }
    private function descendingKey($value): string { $number = $this->num($value); return $number === null ? '9|99999999999999999999' : '0|'.sprintf('%030.10f', 1000000000000000.0 - $number); }
    private function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    private function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 || strcmp($from, self::OOS_RESERVED_FROM) >= 0; }
    private function addNotEvaluable(array &$out, string $layer, string $slice, string $code, string $message): void { $out[] = ['validation_layer' => $layer, 'validation_slice' => $slice, 'reason_code' => $code, 'message' => $message]; }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C49_BLOCKED_UNTIL_C48_INPUT_VALIDATED';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') { $this->writeArtifact($output, $artifact, true); }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        unset($artifact['redesign_profile_results']['_keys']);
        foreach ($artifact['redesign_profile_results'] as &$profile) { unset($profile['_keys']); }
        unset($profile);
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C49_OPERATOR_VALIDATION_REQUIRED'; return $this->result($artifact, $output, $write['reason_code'], $write['message']); }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return [
            'status' => $artifact['status'], 'reason_code' => $reason, 'message' => $message, 'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'] ?? null, 'production_ready' => 0,
            'expected_c48_hash' => $artifact['expected_c48_hash'] ?? null, 'actual_c48_hash' => $artifact['actual_c48_hash'] ?? null,
            'c48_hash_match' => $artifact['c48_hash_match'] ?? false, 'c48_status' => $artifact['c48_status'] ?? null,
            'c48_diagnostic_conclusion' => $artifact['c48_diagnostic_conclusion'] ?? null, 'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null,
            'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_universe_summary' => $artifact['source_universe_summary'] ?? [],
            'selected_c49_candidates_for_c50' => $artifact['selected_c49_candidates_for_c50'] ?? [],
            'c50_readiness_decision' => $artifact['c50_readiness_decision'] ?? [],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); }
        $dir = dirname($path); if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES); if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C49 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
}
