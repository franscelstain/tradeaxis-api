<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC50IsValidationAntiOverfitCheckService
{
    public const RUN_CODE = 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK';
    public const ARTIFACT_TYPE = 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK';
    public const DEFAULT_C49_ARTIFACT = 'storage/app/watchlist/backtest/c49-broader-strategy-redesign.json';
    public const DEFAULT_EXPECTED_C49_HASH = '9266ec2b59a6ea11c21b830cd9b769635afc91a8';
    public const DEFAULT_C48_ARTIFACT = 'storage/app/watchlist/backtest/c48-oos-failure-attribution.json';
    public const DEFAULT_EXPECTED_C48_HASH = '1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7';
    public const DEFAULT_SOURCE_EVIDENCE = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const PRIMARY_PROFILE = 'C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL';
    public const PRIMARY_CANDIDATE = 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL';
    public const DEFENSIVE_PROFILE = 'C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN';
    public const DEFENSIVE_CANDIDATE = 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN';
    public const C44_COMPARATOR_PROFILE = 'C49_F00_C44_SHARED_CORE_COMPARATOR';
    public const C44_COMPARATOR_CANDIDATE = 'C49_CANDIDATE_F00_C44_SHARED_CORE_COMPARATOR';
    private const CURRENT_G21_QUOTA = 13;

    private const VALID_C49_CONCLUSIONS = [
        'C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION',
        'C49_BROADER_STRATEGY_REDESIGN_COMPLETED',
        'C49_SHARED_CORE_ESCAPE_CANDIDATE_IDENTIFIED',
        'C49_MATERIAL_SELECTION_DIFFERENCE_IDENTIFIED',
        'C49_REGIME_AWARE_REDESIGN_PROMISING',
        'C49_CONCENTRATION_GUARD_PROMISING',
        'C49_POST_ENTRY_PROXY_REDESIGN_PROMISING',
    ];

    private const VALID_C49_NEXT_STEPS = [
        'C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN',
        'C50_REGIME_AWARE_IS_VALIDATION_FOR_C49_REDESIGN',
        'C50_CONCENTRATION_GUARD_IS_VALIDATION_FOR_C49_REDESIGN',
        'C50_POST_ENTRY_PROXY_IS_VALIDATION_FOR_C49_REDESIGN',
        'C50_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN',
    ];

    /**
     * C50_IS_VALIDATION_ANTI_OVERFIT_CHECK_ONLY. C49_ARTIFACT_HASH_LOCK. C49_USED_AS_LOCKED_CANDIDATE_SOURCE.
     * LOCKED_C49_CANDIDATE_REPLAY_ONLY. IS_ONLY_VALIDATION. NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN.
     * NO_BEST_OF_OOS. NO_OOS_WINNER. NO_OOS_RETURN_SELECTION. NO_OOS_BAD_MONTH_THRESHOLD_SELECTION.
     * NO_OOS_TICKER_SECTOR_EXCLUSION_RULE. NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG.
     * NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C49_MUTATION. NO_C01_TO_C49_ARTIFACT_MUTATION.
     * CANDIDATE_IS_NOT_PRODUCTION. C50_MUST_NOT_RECOMMEND_OOS_PROOF.
     */
    public function execute(
        string $c49Artifact = self::DEFAULT_C49_ARTIFACT,
        string $expectedC49Hash = self::DEFAULT_EXPECTED_C49_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c49Artifact = trim($c49Artifact) !== '' ? trim($c49Artifact) : self::DEFAULT_C49_ARTIFACT;
        $expectedC49Hash = trim($expectedC49Hash) !== '' ? trim($expectedC49Hash) : self::DEFAULT_EXPECTED_C49_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;
        $artifact = $this->baseArtifact($c49Artifact, $expectedC49Hash, $from, $to, $createdAt);

        if (! is_file($c49Artifact)) {
            return $this->blocked($artifact, 'C50_BLOCKED_MISSING_C49_ARTIFACT', 'WS_BT_C50_C49_ARTIFACT_MISSING', 'C50 requires the locked C49 broader strategy redesign artifact.', $outputPath);
        }

        $c49 = json_decode((string) file_get_contents($c49Artifact), true);
        if (! is_array($c49)) {
            return $this->blocked($artifact, 'C50_BLOCKED_MISSING_C49_ARTIFACT', 'WS_BT_C50_C49_ARTIFACT_UNREADABLE', 'C49 artifact is not readable JSON.', $outputPath);
        }

        $actualC49Hash = $this->stableHash($c49);
        $artifact['actual_c49_hash'] = $actualC49Hash;
        $artifact['c49_hash_match'] = $actualC49Hash === $expectedC49Hash;
        $artifact['c49_status'] = $c49['status'] ?? null;
        $artifact['c49_diagnostic_conclusion'] = $c49['diagnostic_conclusion'] ?? null;
        $artifact['c49_next_step_recommendation'] = $c49['next_step_recommendation'] ?? null;
        $artifact['c49_primary_candidate'] = $c49['selected_c49_candidates_for_c50']['primary_candidate'] ?? ($c49['c50_readiness_decision']['primary_candidate_code'] ?? null);
        $artifact['c49_primary_profile_code'] = $c49['selected_c49_candidates_for_c50']['primary_profile_code'] ?? null;
        $artifact['c49_defensive_comparator'] = $c49['selected_c49_candidates_for_c50']['defensive_comparator'] ?? ($c49['c50_readiness_decision']['defensive_comparator_code'] ?? null);
        $artifact['c49_defensive_profile_code'] = $c49['selected_c49_candidates_for_c50']['defensive_profile_code'] ?? self::DEFENSIVE_PROFILE;
        $artifact['c49_carry_forward_summary'] = $this->c49CarryForwardSummary($c49, $actualC49Hash, $expectedC49Hash);

        if ($actualC49Hash !== $expectedC49Hash) {
            return $this->blocked($artifact, 'C50_BLOCKED_C49_HASH_MISMATCH', 'WS_BT_C50_C49_ARTIFACT_HASH_MISMATCH', 'C49 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c49['status'] ?? null) !== 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED') {
            return $this->blocked($artifact, 'C50_BLOCKED_UNEXPECTED_C49_STATUS', 'WS_BT_C50_UNEXPECTED_C49_STATUS', 'C50 requires completed C49 broader strategy redesign.', $outputPath);
        }
        if (! in_array((string) ($c49['diagnostic_conclusion'] ?? ''), self::VALID_C49_CONCLUSIONS, true)) {
            return $this->blocked($artifact, 'C50_BLOCKED_UNEXPECTED_C49_CONCLUSION', 'WS_BT_C50_UNEXPECTED_C49_CONCLUSION', 'C49 diagnostic conclusion does not authorize C50 validation.', $outputPath);
        }
        if (($c49['next_step_recommendation'] ?? null) !== null && ! in_array((string) ($c49['next_step_recommendation'] ?? ''), self::VALID_C49_NEXT_STEPS, true)) {
            return $this->blocked($artifact, 'C50_BLOCKED_C49_NEXT_STEP_UNEXPECTED', 'WS_BT_C50_C49_NEXT_STEP_UNEXPECTED', 'C49 next step does not route to C50 validation.', $outputPath);
        }
        if (! $this->strictFalse($c49['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C50_BLOCKED_C49_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C50_C49_PRODUCTION_READY_NOT_FALSE', 'C49 production_ready must be false.', $outputPath);
        }
        if (($c49['direct_oos_proof_recommended'] ?? false) === true || ($c49['oos_proof_unlocked'] ?? false) === true || ($c49['c50_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c49['c50_readiness_decision']['oos_proof_unlocked'] ?? false) === true) {
            return $this->blocked($artifact, 'C50_BLOCKED_C49_OOS_PROOF_FLAG_INVALID', 'WS_BT_C50_C49_OOS_PROOF_FLAG_INVALID', 'C49 must not unlock or recommend direct OOS proof.', $outputPath);
        }
        if (trim((string) ($artifact['c49_primary_candidate'] ?? '')) === '') {
            return $this->blocked($artifact, 'C50_BLOCKED_MISSING_C49_PRIMARY_CANDIDATE', 'WS_BT_C50_C49_PRIMARY_CANDIDATE_MISSING', 'C50 requires the locked C49 primary candidate.', $outputPath);
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C50_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C50_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C50 only accepts IS period and must not touch OOS reserved period.', $outputPath);
        }

        $sourceLoad = $this->loadSourceRows($from, $to, $options, $c49, $artifact['not_evaluable_reasons']);
        $rows = $sourceLoad['rows'];
        $artifact['source_reconstruction_summary'] = $sourceLoad['summary'];
        $artifact['source_reconstruction_bias_check'] = $this->sourceBiasCheck($rows, $sourceLoad['summary']);
        if (($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false) !== true) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_reconstruction_bias_check', 'source_rows', 'C50_SOURCE_RECONSTRUCTION_BIAS_RISK', 'Source reconstruction is partial or missing required fields.');
        }

        if (count($rows) === 0) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_reconstruction', 'pick_rows', 'C50_SOURCE_ROWS_NOT_EVALUABLE', 'No IS source rows are available for locked C49 replay.');
            $artifact['status'] = 'C50_SOURCE_ROWS_NOT_EVALUABLE';
            $artifact['diagnostic_conclusion'] = 'C50_EVIDENCE_EXPANSION_REQUIRED';
            $artifact['next_step_recommendation'] = 'C51_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN';
            $artifact['c51_readiness_decision'] = $this->c51Decision('C51_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN', false, 'source_rows_not_evaluable');
            $artifact['diagnostics'] = $this->diagnostics($artifact);
            return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $candidateRows = $this->candidateRows($rows, $artifact['not_evaluable_reasons']);
        $baselineRows = $candidateRows['baseline'];
        $c44Rows = $candidateRows['c44'];
        $selectedCandidateRows = [
            self::PRIMARY_CANDIDATE => $candidateRows['primary'],
            self::DEFENSIVE_CANDIDATE => $candidateRows['defensive'],
            self::C44_COMPARATOR_CANDIDATE => $c44Rows,
        ];
        $months = $this->uniqueMonths($rows);

        $artifact['locked_candidate_replay_results'] = $this->lockedReplayResults($selectedCandidateRows, $baselineRows, $c44Rows, $months);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($selectedCandidateRows, $months);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($artifact['rolling_validation_results']);
        $artifact['leave_one_month_out_results'] = $this->leaveOneMonthOutResults($selectedCandidateRows, $months);
        $artifact['leave_one_month_out_summary'] = $this->leaveOneMonthOutSummary($artifact['leave_one_month_out_results']);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessResults($selectedCandidateRows, $artifact['not_evaluable_reasons']);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessSummary($artifact['regime_robustness_validation_results']);
        $artifact['concentration_dependency_validation_results'] = $this->concentrationDependencyResults($selectedCandidateRows);
        $artifact['branch_dependency_validation_results'] = $this->branchDependencyResults($selectedCandidateRows);
        $artifact['material_difference_validation'] = $this->materialDifferenceValidation($candidateRows['primary'], $baselineRows, $c44Rows);
        $artifact['candidate_validation_scorecard'] = $this->candidateValidationScorecard($artifact);
        $artifact['selected_c50_candidates_for_c51'] = $this->selectedForC51($artifact['candidate_validation_scorecard']);
        $artifact['c51_readiness_decision'] = $this->readinessDecision($artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($artifact['candidate_validation_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c51_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c51_readiness_decision']['c51_recommendation'];
        $artifact['status'] = 'C50_IS_VALIDATION_COMPLETED';
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $c49Artifact, string $expectedC49Hash, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C50_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c49_artifact' => $c49Artifact,
            'expected_c49_hash' => $expectedC49Hash,
            'actual_c49_hash' => null,
            'c49_hash_match' => false,
            'c49_status' => null,
            'c49_diagnostic_conclusion' => null,
            'c49_next_step_recommendation' => null,
            'c49_primary_candidate' => null,
            'c49_primary_profile_code' => null,
            'c49_defensive_comparator' => null,
            'c49_defensive_profile_code' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'is_validation_and_anti_overfit_check', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c49_carry_forward_summary' => [],
            'source_reconstruction_summary' => [],
            'locked_candidate_replay_results' => [],
            'rolling_validation_results' => [],
            'rolling_validation_summary' => [],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => [],
            'concentration_dependency_validation_results' => [],
            'branch_dependency_validation_results' => [],
            'material_difference_validation' => [],
            'source_reconstruction_bias_check' => [],
            'candidate_validation_scorecard' => [],
            'selected_c50_candidates_for_c51' => [],
            'c51_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C50_PENDING',
            'next_step_recommendation' => 'C50_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                // Lowercase snake_case only: PowerShell ConvertFrom-Json treats keys case-insensitively.
                'c50_is_validation_anti_overfit_check_only' => true,
                'c49_artifact_hash_lock' => true,
                'c49_used_as_locked_candidate_source' => true,
                'locked_c49_candidate_replay_only' => true,
                'is_only_validation' => true,
                'no_oos_tuning' => true,
                'no_oos_proof' => true,
                'no_oos_proof_rerun' => true,
                'no_best_of_oos' => true,
                'no_oos_winner' => true,
                'no_oos_return_selection' => true,
                'no_oos_bad_month_threshold_selection' => true,
                'no_oos_ticker_sector_exclusion_rule' => true,
                'no_candidate_reselection_from_oos' => true,
                'no_profile_reselection_from_oos' => true,
                'no_production_catalog' => true,
                'no_promotion' => true,
                'no_plan_confirm_mutation' => true,
                'no_c01_to_c49_mutation' => true,
                'no_c01_to_c49_artifact_mutation' => true,
                'candidate_is_not_production' => true,
                'c50_must_not_recommend_oos_proof' => true,
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

    private function c49CarryForwardSummary(array $c49, string $actualHash, string $expectedHash): array
    {
        return [
            'c49_status' => $c49['status'] ?? null,
            'c49_diagnostic_conclusion' => $c49['diagnostic_conclusion'] ?? null,
            'c49_next_step_recommendation' => $c49['next_step_recommendation'] ?? null,
            'c49_artifact_hash_match' => $actualHash === $expectedHash,
            'c49_used_as_locked_candidate_source' => true,
            'c49_selected_candidates' => $c49['selected_c49_candidates_for_c50'] ?? [],
            'c49_c50_readiness_decision' => $c49['c50_readiness_decision'] ?? [],
            'c49_source_universe_summary' => $c49['source_universe_summary'] ?? [],
            'c49_selected_primary_candidate' => $c49['selected_c49_candidates_for_c50']['primary_candidate'] ?? ($c49['c50_readiness_decision']['primary_candidate_code'] ?? null),
            'c49_selected_defensive_comparator' => $c49['selected_c49_candidates_for_c50']['defensive_comparator'] ?? ($c49['c50_readiness_decision']['defensive_comparator_code'] ?? null),
            'production_ready' => false,
            'oos_return_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'oos_proof_executed' => false,
        ];
    }

    private function loadSourceRows(string $from, string $to, array $options, array $c49, array &$notEvaluable): array
    {
        $sourceRows = [];
        $sourcePath = null;
        if (array_key_exists('source_rows', $options)) {
            $sourceRows = array_values(array_filter((array) $options['source_rows'], function ($row): bool { return is_array($row); }));
            $sourceMode = 'INJECTED_TEST_SOURCE_ROWS';
        } else {
            $sourcePath = trim((string) ($options['source_evidence_artifact'] ?? ($c49['source_universe_summary']['source_evidence_artifact'] ?? self::DEFAULT_SOURCE_EVIDENCE)));
            if ($sourcePath === '' || ! is_file($sourcePath)) {
                $this->addNotEvaluable($notEvaluable, 'source_reconstruction', 'source_evidence_artifact', 'C50_SOURCE_ROWS_NOT_EVALUABLE', 'C50 could not locate IS source evidence artifact.');
                return ['rows' => [], 'summary' => ['source_evidence_artifact' => $sourcePath, 'source_rows_available' => false, 'source_mode' => 'MISSING_SOURCE_ARTIFACT']];
            }
            $source = json_decode((string) file_get_contents($sourcePath), true);
            if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
                $this->addNotEvaluable($notEvaluable, 'source_reconstruction', 'pick_diagnostic_rows', 'C50_SOURCE_ROWS_NOT_EVALUABLE', 'C50 source evidence has no pick diagnostic rows.');
                return ['rows' => [], 'summary' => ['source_evidence_artifact' => $sourcePath, 'source_rows_available' => false, 'source_mode' => 'UNREADABLE_SOURCE_ROWS']];
            }
            $sourceRows = $source['pick_diagnostic_rows'];
            $sourceMode = 'C28_PICK_DIAGNOSTIC_ROWS';
        }

        $rows = $this->isRows($sourceRows, $from, $to);
        $preTradeLoad = $this->loadPreTradeSources($rows, $options);
        $rows = $this->enrichRows($rows, $preTradeLoad['rows']);
        if ($preTradeLoad['mode'] !== 'INJECTED_PRE_TRADE_SOURCE_ROWS' && count($preTradeLoad['rows']) === 0) {
            $this->addNotEvaluable($notEvaluable, 'source_reconstruction', 'pre_trade_source_join', 'C50_SOURCE_RECONSTRUCTION_PARTIAL', 'Pre-trade indicator source rows were not joined; locked candidate replay may rely on fields already present in source rows.');
        }

        $summary = [
            'source_evidence_artifact' => $sourcePath,
            'source_rows_available' => count($rows) > 0,
            'source_mode' => $sourceMode,
            'source_is_rows' => count($rows),
            'source_g21_rows' => count($this->branchBucketRows($rows, 'G21', 'no_rule_profit_signal_before_fallback')),
            'source_g16_rows' => count($this->branchBucketRows($rows, 'G16', 'next_open_delay_after_close_signal')),
            'source_g13_rows' => count($this->branchBucketRows($rows, 'G13', 'no_rule_profit_signal_before_fallback')),
            'source_months' => count($this->uniqueMonths($rows)),
            'pre_trade_source_mode' => $preTradeLoad['mode'],
            'pre_trade_source_row_count' => count($preTradeLoad['rows']),
            'pre_trade_source_error' => $preTradeLoad['error'],
            'fields_present' => $this->fieldsPresent($rows),
            'source_row_count_vs_candidate_row_count' => null,
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
        ];
        return ['rows' => $rows, 'summary' => $summary];
    }

    private function candidateRows(array $rows, array &$notEvaluable): array
    {
        $g16 = $this->branchBucketRows($rows, 'G16', 'next_open_delay_after_close_signal');
        $g21 = $this->branchBucketRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g13 = $this->branchBucketRows($rows, 'G13', 'no_rule_profit_signal_before_fallback');
        $months = $this->uniqueMonths(array_merge($g16, $g21, $g13));
        $baseline = array_merge($g16, $this->selectMonthlyQuota($g21, $months, self::CURRENT_G21_QUOTA, 'METADATA'));
        $c44 = array_merge($g16, $this->selectMonthlyQuota($g21, $months, self::CURRENT_G21_QUOTA, 'MARKET_EXTENSION'));
        $safeG21 = $this->safeRegimeSubset($g21);
        if (count($safeG21) === 0 && count($g21) > 0) {
            $this->addNotEvaluable($notEvaluable, 'locked_candidate_replay', self::PRIMARY_CANDIDATE, 'C50_REGIME_SOURCE_RECONSTRUCTION_PARTIAL', 'F03 regime-aware replay fell back to metadata because regime fields were unavailable or all filtered out.');
            $safeG21 = $g21;
        }
        $primary = array_merge($g16, $this->selectMonthlyQuota($safeG21, $months, 10, 'BALANCED'));
        $defensive = array_merge(
            $this->selectMonthlyQuota($g16, $months, 15, 'METADATA'),
            $this->selectMonthlyQuota($g21, $months, 6, 'METADATA'),
            $this->selectMonthlyQuota($g13, $months, 6, 'METADATA')
        );
        return ['baseline' => $baseline, 'c44' => $c44, 'primary' => $primary, 'defensive' => $defensive];
    }

    private function lockedReplayResults(array $candidateRows, array $baselineRows, array $c44Rows, array $months): array
    {
        $out = [];
        foreach ($candidateRows as $candidateCode => $rows) {
            $profile = $this->profileForCandidate($candidateCode);
            $m = $this->metrics($rows);
            $concentration = $this->concentrationSummary($rows);
            $overlapC44 = $this->overlapShare($rows, $c44Rows);
            $overlapBaseline = $this->overlapShare($rows, $baselineRows);
            $material = $candidateCode === self::C44_COMPARATOR_CANDIDATE ? false : (($overlapC44 ?? 1.0) <= 0.85 || ($overlapBaseline ?? 1.0) <= 0.85);
            $coverageMonths = count($this->uniqueMonths($rows));
            $coveragePass = $coverageMonths >= max(1, (int) floor(count($months) * 0.75)) && count($rows) >= max(1, (int) floor(count($baselineRows) * 0.25));
            $qualityPass = $m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.01;
            $stabilityPass = $m['month_avg_ret_net_min'] !== null && $m['bad_month_like_count'] <= max(1, $this->metrics($baselineRows)['bad_month_like_count'] + 1);
            $failure = [];
            if (! $coveragePass) { $failure[] = 'C50_COVERAGE_FAIL'; }
            if (! $qualityPass) { $failure[] = 'C50_QUALITY_FAIL'; }
            if (! $stabilityPass) { $failure[] = 'C50_STABILITY_FAIL'; }
            if ($candidateCode !== self::C44_COMPARATOR_CANDIDATE && ! $material) { $failure[] = 'C50_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
            $out[] = [
                'candidate_code' => $candidateCode,
                'profile_code' => $profile['profile_code'],
                'family_code' => $profile['family_code'],
                'candidate_role' => $profile['candidate_role'],
                'selection_rule_description' => $profile['selection_rule_description'],
                'safe_pre_trade_fields_used' => $profile['safe_pre_trade_fields_used'],
                'row_count' => count($rows),
                'evaluated_picks_count' => $m['evaluated_picks_count'],
                'avg_ret_net' => $m['avg_ret_net'],
                'median_ret_net' => $m['median_ret_net'],
                'p25_ret_net' => $m['p25_ret_net'],
                'p10_ret_net' => $m['p10_ret_net'],
                'win_rate' => $m['win_rate'],
                'month_win_rate_min' => $m['month_win_rate_min'],
                'month_avg_ret_net_min' => $m['month_avg_ret_net_min'],
                'bad_month_like_count' => $m['bad_month_like_count'],
                'coverage_days' => count($this->uniqueDates($rows)),
                'coverage_months' => $coverageMonths,
                'overlap_with_c44' => $overlapC44,
                'overlap_with_baseline' => $overlapBaseline,
                'material_selection_difference_pass' => $candidateCode === self::C44_COMPARATOR_CANDIDATE ? false : $material,
                'coverage_pass' => $coveragePass,
                'quality_pass' => $qualityPass,
                'stability_pass' => $stabilityPass,
                'failure_reason_codes' => $failure,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'candidate_is_not_production' => true,
                'production_ready' => false,
                '_keys' => array_values(array_map(function (array $row): string { return $this->pickKey($row); }, $rows)),
                '_concentration' => $concentration,
            ];
        }
        return $out;
    }

    private function rollingValidationResults(array $candidateRows, array $months): array
    {
        $windows = [];
        foreach ([6, 9, 12] as $length) {
            for ($start = 0; $start + $length <= count($months); $start++) {
                $slice = array_slice($months, $start, $length);
                $windows[] = ['code' => 'ROLLING_'.$length.'M_STEP_1M_'.$slice[0].'_TO_'.$slice[count($slice) - 1], 'months' => $slice];
            }
        }
        if (count($months) > 0) {
            $third = max(1, (int) floor(count($months) / 3));
            $windows[] = ['code' => 'EARLY_IS', 'months' => array_slice($months, 0, $third)];
            $windows[] = ['code' => 'MID_IS', 'months' => array_slice($months, $third, $third)];
            $windows[] = ['code' => 'LATE_IS', 'months' => array_slice($months, $third * 2)];
        }
        $out = [];
        foreach ($windows as $window) {
            foreach ($candidateRows as $candidateCode => $rows) {
                $sliceRows = $this->filterMonths($rows, $window['months']);
                $m = $this->metrics($sliceRows);
                $coverageMonths = count($this->uniqueMonths($sliceRows));
                $qualityPass = $m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.01;
                $stabilityPass = $m['month_avg_ret_net_min'] !== null && $m['bad_month_like_count'] <= max(1, (int) ceil($coverageMonths / 4));
                $coveragePass = $coverageMonths >= max(1, (int) floor(count($window['months']) * 0.50)) && $m['evaluated_picks_count'] > 0;
                $out[] = [
                    'validation_window_code' => $window['code'],
                    'window_from' => count($window['months']) > 0 ? $window['months'][0].'-01' : null,
                    'window_to' => count($window['months']) > 0 ? $window['months'][count($window['months']) - 1].'-31' : null,
                    'candidate_code' => $candidateCode,
                    'profile_code' => $this->profileForCandidate($candidateCode)['profile_code'],
                    'evaluated_picks_count' => $m['evaluated_picks_count'],
                    'avg_ret_net' => $m['avg_ret_net'],
                    'median_ret_net' => $m['median_ret_net'],
                    'p25_ret_net' => $m['p25_ret_net'],
                    'win_rate' => $m['win_rate'],
                    'month_win_rate_min' => $m['month_win_rate_min'],
                    'month_avg_ret_net_min' => $m['month_avg_ret_net_min'],
                    'bad_month_like_count' => $m['bad_month_like_count'],
                    'coverage_days' => count($this->uniqueDates($sliceRows)),
                    'coverage_months' => $coverageMonths,
                    'quality_pass' => $qualityPass,
                    'stability_pass' => $stabilityPass,
                    'coverage_pass' => $coveragePass,
                    'failure_reason_codes' => array_values(array_filter([$qualityPass ? null : 'C50_ROLLING_QUALITY_FAIL', $stabilityPass ? null : 'C50_ROLLING_STABILITY_FAIL', $coveragePass ? null : 'C50_ROLLING_COVERAGE_FAIL'])),
                ];
            }
        }
        return $out;
    }

    private function rollingValidationSummary(array $results): array
    {
        $byCandidate = [];
        foreach ($results as $row) { $byCandidate[(string) $row['candidate_code']][] = $row; }
        $summary = [];
        foreach ($byCandidate as $candidate => $rows) {
            $pass = count(array_filter($rows, function (array $row): bool { return (bool) $row['quality_pass'] && (bool) $row['stability_pass'] && (bool) $row['coverage_pass']; }));
            $summary[$candidate] = [
                'candidate_code' => $candidate,
                'rolling_window_count' => count($rows),
                'rolling_pass_count' => $pass,
                'rolling_pass_rate' => count($rows) > 0 ? $pass / count($rows) : null,
                'rolling_avg_ret_net_min' => $this->minValue($rows, 'avg_ret_net'),
                'rolling_median_ret_net_min' => $this->minValue($rows, 'median_ret_net'),
                'rolling_month_win_rate_min' => $this->minValue($rows, 'month_win_rate_min'),
                'rolling_bad_month_like_max' => $this->maxValue($rows, 'bad_month_like_count'),
                'rolling_coverage_months_min' => $this->minValue($rows, 'coverage_months'),
                'rolling_validation_pass' => count($rows) > 0 && $pass / count($rows) >= 0.60,
            ];
        }
        return [
            'candidate_summaries' => array_values($summary),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'primary_rolling_validation_pass' => (bool) ($summary[self::PRIMARY_CANDIDATE]['rolling_validation_pass'] ?? false),
            'defensive_comparator_code' => self::DEFENSIVE_CANDIDATE,
            'defensive_rolling_validation_pass' => (bool) ($summary[self::DEFENSIVE_CANDIDATE]['rolling_validation_pass'] ?? false),
        ];
    }

    private function leaveOneMonthOutResults(array $candidateRows, array $months): array
    {
        $fullMetrics = [];
        foreach ($candidateRows as $candidate => $rows) { $fullMetrics[$candidate] = $this->metrics($rows); }
        $fullRank = $this->rankCandidates($fullMetrics);
        $out = [];
        foreach ($months as $excludeMonth) {
            $metricsAfter = [];
            foreach ($candidateRows as $candidate => $rows) { $metricsAfter[$candidate] = $this->metrics($this->filterOutMonth($rows, $excludeMonth)); }
            $rankAfter = $this->rankCandidates($metricsAfter);
            foreach ($candidateRows as $candidate => $rows) {
                $afterRows = $this->filterOutMonth($rows, $excludeMonth);
                $m = $metricsAfter[$candidate];
                $qualityDelta = ($m['avg_ret_net'] !== null && $fullMetrics[$candidate]['avg_ret_net'] !== null) ? $m['avg_ret_net'] - $fullMetrics[$candidate]['avg_ret_net'] : null;
                $stabilityDelta = ($m['month_win_rate_min'] !== null && $fullMetrics[$candidate]['month_win_rate_min'] !== null) ? $m['month_win_rate_min'] - $fullMetrics[$candidate]['month_win_rate_min'] : null;
                $rankStable = ($rankAfter[$candidate] ?? 999) <= ($fullRank[$candidate] ?? 999) + 1;
                $out[] = [
                    'exclude_month' => $excludeMonth,
                    'candidate_code' => $candidate,
                    'row_count_after_exclusion' => count($afterRows),
                    'evaluated_picks_count_after_exclusion' => $m['evaluated_picks_count'],
                    'avg_ret_net_after_exclusion' => $m['avg_ret_net'],
                    'median_ret_net_after_exclusion' => $m['median_ret_net'],
                    'win_rate_after_exclusion' => $m['win_rate'],
                    'month_win_rate_min_after_exclusion' => $m['month_win_rate_min'],
                    'quality_delta' => $qualityDelta,
                    'stability_delta' => $stabilityDelta,
                    'candidate_rank_after_exclusion' => $rankAfter[$candidate] ?? null,
                    'rank_stable' => $rankStable,
                    'dependency_on_excluded_month' => ($qualityDelta !== null && $qualityDelta < -0.005) || ! $rankStable,
                ];
            }
        }
        return $out;
    }

    private function leaveOneMonthOutSummary(array $results): array
    {
        $byCandidate = [];
        foreach ($results as $row) { $byCandidate[(string) $row['candidate_code']][] = $row; }
        $summary = [];
        foreach ($byCandidate as $candidate => $rows) {
            $stable = count(array_filter($rows, function (array $row): bool { return (bool) $row['rank_stable']; }));
            $dependency = count(array_filter($rows, function (array $row): bool { return (bool) $row['dependency_on_excluded_month']; })) > 0;
            $summary[$candidate] = [
                'candidate_code' => $candidate,
                'loo_month_count' => count($rows),
                'loo_rank_stable_count' => $stable,
                'loo_rank_stability_rate' => count($rows) > 0 ? $stable / count($rows) : null,
                'loo_worst_quality_delta' => $this->minValue($rows, 'quality_delta'),
                'loo_worst_stability_delta' => $this->minValue($rows, 'stability_delta'),
                'loo_single_month_dependency_detected' => $dependency,
                'loo_validation_pass' => count($rows) > 0 && $stable / count($rows) >= 0.60 && ! $dependency,
            ];
        }
        return [
            'candidate_summaries' => array_values($summary),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'primary_loo_validation_pass' => (bool) ($summary[self::PRIMARY_CANDIDATE]['loo_validation_pass'] ?? false),
            'defensive_comparator_code' => self::DEFENSIVE_CANDIDATE,
            'defensive_loo_validation_pass' => (bool) ($summary[self::DEFENSIVE_CANDIDATE]['loo_validation_pass'] ?? false),
        ];
    }

    private function regimeRobustnessResults(array $candidateRows, array &$notEvaluable): array
    {
        $fields = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'];
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
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
                    $pass = $m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.01;
                    $out[] = [
                        'candidate_code' => $candidate,
                        'regime_field' => $field,
                        'regime_bucket' => $bucket,
                        'row_count' => count($bucketRows),
                        'evaluated_picks_count' => $m['evaluated_picks_count'],
                        'avg_ret_net' => $m['avg_ret_net'],
                        'median_ret_net' => $m['median_ret_net'],
                        'win_rate' => $m['win_rate'],
                        'loss_count' => $lossCount,
                        'loss_share' => count($bucketRows) > 0 ? $lossCount / count($bucketRows) : null,
                        'bad_month_like_contribution' => $m['bad_month_like_count'],
                        'coverage_share' => count($rows) > 0 ? count($bucketRows) / count($rows) : null,
                        'regime_bucket_pass' => $pass,
                        'regime_failure_reason_codes' => $pass ? [] : ['C50_REGIME_BUCKET_FAIL'],
                    ];
                }
            }
        }
        if (count($out) === 0) {
            $this->addNotEvaluable($notEvaluable, 'regime_robustness_validation', 'regime_fields', 'C50_REGIME_ROBUSTNESS_NOT_EVALUABLE', 'No joined pre-trade regime fields are available.');
        }
        return $out;
    }

    private function regimeRobustnessSummary(array $results): array
    {
        $byCandidate = [];
        foreach ($results as $row) { $byCandidate[(string) $row['candidate_code']][] = $row; }
        $summary = [];
        foreach ($byCandidate as $candidate => $rows) {
            $pass = count(array_filter($rows, function (array $row): bool { return (bool) $row['regime_bucket_pass']; }));
            $summary[$candidate] = [
                'candidate_code' => $candidate,
                'regime_bucket_count' => count($rows),
                'regime_bucket_pass_count' => $pass,
                'regime_pass_rate' => count($rows) > 0 ? $pass / count($rows) : null,
                'regime_worst_bucket_avg_ret_net' => $this->minValue($rows, 'avg_ret_net'),
                'regime_worst_bucket_win_rate' => $this->minValue($rows, 'win_rate'),
                'regime_loss_concentration_max' => $this->maxValue($rows, 'loss_share'),
                'regime_robustness_validation_pass' => count($rows) > 0 && $pass / count($rows) >= 0.60,
            ];
        }
        return [
            'candidate_summaries' => array_values($summary),
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'primary_regime_robustness_validation_pass' => (bool) ($summary[self::PRIMARY_CANDIDATE]['regime_robustness_validation_pass'] ?? false),
            'regime_bucket_count' => count($results),
        ];
    }

    private function concentrationDependencyResults(array $candidateRows): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $summary = $this->concentrationSummary($rows);
            $lossRows = array_values(array_filter($rows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
            $pass = (($summary['max_ticker_share'] ?? 0.0) <= 0.25)
                && (($summary['max_sector_share'] ?? 0.0) <= 0.65 || $summary['max_sector_share'] === null)
                && (($summary['max_branch_share'] ?? 0.0) <= 0.90)
                && (($summary['max_month_share'] ?? 0.0) <= 0.20);
            $out[] = [
                'candidate_code' => $candidate,
                'max_ticker_share' => $summary['max_ticker_share'],
                'max_sector_share' => $summary['max_sector_share'],
                'max_bucket_share' => $summary['max_bucket_share'],
                'max_branch_share' => $summary['max_branch_share'],
                'max_month_share' => $summary['max_month_share'],
                'unique_ticker_count' => $summary['unique_ticker_count'],
                'unique_sector_count' => $summary['unique_sector_count'],
                'unique_bucket_count' => $summary['unique_bucket_count'],
                'unique_branch_count' => $summary['unique_branch_count'],
                'loss_cluster_share' => $summary['loss_cluster_share'],
                'top_loss_ticker_share' => $this->concentration($lossRows, 'ticker'),
                'top_loss_sector_share' => $this->sectorConcentration($lossRows),
                'top_loss_branch_share' => $this->concentration($lossRows, 'selected_source_code'),
                'concentration_validation_pass' => $pass,
                'failure_reason_codes' => $pass ? [] : ['C50_CONCENTRATION_DEPENDENCY_WARNING'],
            ];
        }
        return $out;
    }

    private function branchDependencyResults(array $candidateRows): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $total = count($rows);
            foreach ($this->groupByField($rows, 'selected_source_code') as $branch => $branchRows) {
                $m = $this->metrics($branchRows);
                $lossCount = count(array_filter($branchRows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
                $share = $total > 0 ? count($branchRows) / $total : null;
                $out[] = [
                    'candidate_code' => $candidate,
                    'branch_code' => $branch,
                    'branch_row_count' => count($branchRows),
                    'branch_share' => $share,
                    'branch_avg_ret_net' => $m['avg_ret_net'],
                    'branch_median_ret_net' => $m['median_ret_net'],
                    'branch_win_rate' => $m['win_rate'],
                    'branch_loss_share' => count($branchRows) > 0 ? $lossCount / count($branchRows) : null,
                    'branch_dependency_detected' => $share !== null && $share > 0.90,
                ];
            }
        }
        return $out;
    }

    private function materialDifferenceValidation(array $primaryRows, array $baselineRows, array $c44Rows): array
    {
        $shared = $this->intersectRows($primaryRows, $c44Rows);
        $only = $this->diffRows($primaryRows, $c44Rows);
        $sharedMetrics = $this->metrics($shared);
        $onlyMetrics = $this->metrics($only);
        $overlapC44 = $this->overlapShare($primaryRows, $c44Rows);
        $overlapBaseline = $this->overlapShare($primaryRows, $baselineRows);
        $score = 1.0 - max($overlapC44 ?? 1.0, $overlapBaseline ?? 1.0);
        $pass = $score >= 0.15 && count($only) > 0;
        return [
            'candidate_code' => self::PRIMARY_CANDIDATE,
            'overlap_with_c44' => $overlapC44,
            'overlap_with_baseline' => $overlapBaseline,
            'shared_core_row_count' => count($shared),
            'candidate_only_row_count' => count($only),
            'shared_core_avg_ret_net' => $sharedMetrics['avg_ret_net'],
            'candidate_only_avg_ret_net' => $onlyMetrics['avg_ret_net'],
            'candidate_only_win_rate' => $onlyMetrics['win_rate'],
            'material_difference_score' => $score,
            'material_selection_difference_pass' => $pass,
            'anti_shared_core_pass' => $pass,
        ];
    }

    private function sourceBiasCheck(array $rows, array $summary): array
    {
        $requiredPreTrade = ['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio'];
        $missingPreTrade = 0;
        foreach ($rows as $row) { foreach ($requiredPreTrade as $field) { if (! array_key_exists($field, $row) || $row[$field] === null || $row[$field] === '') { $missingPreTrade++; } } }
        $missingReturn = count(array_filter($rows, function (array $row): bool { return $this->num($row['profile_ret_net'] ?? null) === null; }));
        $missingSector = count(array_filter($rows, function (array $row): bool { return trim((string) ($row['sector_code'] ?? $row['sector_name'] ?? '')) === ''; }));
        $missingBucket = count(array_filter($rows, function (array $row): bool { return trim((string) ($row['bucket_code'] ?? '')) === ''; }));
        $totalPreTrade = max(1, count($rows) * count($requiredPreTrade));
        $preTradeMissingShare = $missingPreTrade / $totalPreTrade;
        $risk = count($rows) === 0 ? 'HIGH' : ($missingReturn > 0 ? 'HIGH' : ($preTradeMissingShare > 0.50 ? 'MEDIUM' : 'LOW'));
        return [
            'source_evidence_artifact' => $summary['source_evidence_artifact'] ?? null,
            'source_mode' => $summary['source_mode'] ?? null,
            'source_rows_available' => (bool) ($summary['source_rows_available'] ?? false),
            'pre_trade_source_mode' => $summary['pre_trade_source_mode'] ?? null,
            'pre_trade_source_row_count' => (int) ($summary['pre_trade_source_row_count'] ?? 0),
            'source_row_count_vs_candidate_row_count' => $summary['source_is_rows'] ?? count($rows),
            'missing_pre_trade_field_count' => $missingPreTrade,
            'missing_return_field_count' => $missingReturn,
            'missing_sector_field_count' => $missingSector,
            'missing_bucket_field_count' => $missingBucket,
            'source_bias_risk_level' => $risk,
            'source_bias_validation_pass' => $risk !== 'HIGH',
            'source_bias_notes' => $risk === 'LOW' ? 'C50 source reconstruction has sufficient IS rows and required evaluation fields.' : 'C50 source reconstruction is partial; do not treat this as production or OOS proof.',
        ];
    }

    private function candidateValidationScorecard(array $artifact): array
    {
        $rolling = $this->candidateSummaryMap($artifact['rolling_validation_summary']['candidate_summaries'] ?? [], 'rolling_validation_pass');
        $loo = $this->candidateSummaryMap($artifact['leave_one_month_out_summary']['candidate_summaries'] ?? [], 'loo_validation_pass');
        $regime = $this->candidateSummaryMap($artifact['regime_robustness_validation_summary']['candidate_summaries'] ?? [], 'regime_robustness_validation_pass');
        $concentration = [];
        foreach ((array) ($artifact['concentration_dependency_validation_results'] ?? []) as $row) { $concentration[$row['candidate_code']] = (bool) ($row['concentration_validation_pass'] ?? false); }
        $out = [];
        foreach ((array) ($artifact['locked_candidate_replay_results'] ?? []) as $row) {
            $candidate = (string) $row['candidate_code'];
            $material = $candidate === self::PRIMARY_CANDIDATE ? (bool) ($artifact['material_difference_validation']['material_selection_difference_pass'] ?? false) : (bool) ($row['material_selection_difference_pass'] ?? false);
            $antiShared = $candidate === self::PRIMARY_CANDIDATE ? (bool) ($artifact['material_difference_validation']['anti_shared_core_pass'] ?? false) : (bool) ($row['material_selection_difference_pass'] ?? false);
            $sourcePass = (bool) ($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false);
            $overall = (bool) $row['quality_pass'] && (bool) $row['stability_pass'] && (bool) $row['coverage_pass'] && (bool) ($rolling[$candidate] ?? false) && (bool) ($loo[$candidate] ?? false) && (bool) ($concentration[$candidate] ?? false) && $sourcePass;
            if ($candidate === self::PRIMARY_CANDIDATE) { $overall = $overall && (bool) ($regime[$candidate] ?? false) && $material && $antiShared; }
            $out[] = [
                'candidate_code' => $candidate,
                'profile_code' => $row['profile_code'],
                'family_code' => $row['family_code'],
                'candidate_role' => $row['candidate_role'],
                'selected_from_c49' => in_array($candidate, [self::PRIMARY_CANDIDATE, self::DEFENSIVE_CANDIDATE], true),
                'selection_rule_description' => $row['selection_rule_description'],
                'safe_pre_trade_fields_used' => $row['safe_pre_trade_fields_used'],
                'evaluated_picks_count' => $row['evaluated_picks_count'],
                'avg_ret_net' => $row['avg_ret_net'],
                'median_ret_net' => $row['median_ret_net'],
                'p25_ret_net' => $row['p25_ret_net'],
                'p10_ret_net' => $row['p10_ret_net'],
                'win_rate' => $row['win_rate'],
                'month_win_rate_min' => $row['month_win_rate_min'],
                'month_avg_ret_net_min' => $row['month_avg_ret_net_min'],
                'bad_month_like_count' => $row['bad_month_like_count'],
                'coverage_months' => $row['coverage_months'],
                'rolling_validation_pass' => (bool) ($rolling[$candidate] ?? false),
                'loo_validation_pass' => (bool) ($loo[$candidate] ?? false),
                'regime_robustness_validation_pass' => (bool) ($regime[$candidate] ?? false),
                'concentration_validation_pass' => (bool) ($concentration[$candidate] ?? false),
                'material_selection_difference_pass' => $material,
                'anti_shared_core_pass' => $antiShared,
                'source_bias_validation_pass' => $sourcePass,
                'overall_is_validation_pass' => $overall,
                'anti_overfit_pass' => $overall,
                'candidate_ready_for_c51' => $overall,
                'failure_reason_codes' => $this->scorecardFailures($row, $rolling[$candidate] ?? false, $loo[$candidate] ?? false, $regime[$candidate] ?? false, $concentration[$candidate] ?? false, $material, $antiShared, $sourcePass),
                'production_ready' => false,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
            ];
        }
        return $out;
    }

    private function selectedForC51(array $scorecard): array
    {
        $primary = $this->scorecardByCandidate($scorecard, self::PRIMARY_CANDIDATE);
        $defensive = $this->scorecardByCandidate($scorecard, self::DEFENSIVE_CANDIDATE);
        return [
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'primary_profile_code' => self::PRIMARY_PROFILE,
            'primary_candidate_ready_for_c51' => (bool) ($primary['candidate_ready_for_c51'] ?? false),
            'defensive_comparator_code' => self::DEFENSIVE_CANDIDATE,
            'defensive_profile_code' => self::DEFENSIVE_PROFILE,
            'defensive_comparator_ready_for_c51' => (bool) ($defensive['candidate_ready_for_c51'] ?? false),
            'candidate_is_not_production' => true,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
        ];
    }

    private function readinessDecision(array $artifact): array
    {
        $primary = $this->scorecardByCandidate($artifact['candidate_validation_scorecard'] ?? [], self::PRIMARY_CANDIDATE);
        $defensive = $this->scorecardByCandidate($artifact['candidate_validation_scorecard'] ?? [], self::DEFENSIVE_CANDIDATE);
        $layerState = [
            'validation_completed' => true,
            'primary_candidate_validation_pass' => (bool) ($primary['overall_is_validation_pass'] ?? false),
            'defensive_comparator_validation_pass' => (bool) ($defensive['overall_is_validation_pass'] ?? false),
            'rolling_validation_pass' => (bool) ($primary['rolling_validation_pass'] ?? false),
            'loo_validation_pass' => (bool) ($primary['loo_validation_pass'] ?? false),
            'regime_robustness_validation_pass' => (bool) ($primary['regime_robustness_validation_pass'] ?? false),
            'concentration_validation_pass' => (bool) ($primary['concentration_validation_pass'] ?? false),
            'material_difference_validation_pass' => (bool) ($primary['material_selection_difference_pass'] ?? false),
            'source_bias_validation_pass' => (bool) ($primary['source_bias_validation_pass'] ?? false),
            'anti_overfit_pass' => (bool) ($primary['anti_overfit_pass'] ?? false),
        ];

        if ($layerState['anti_overfit_pass']) {
            $rec = 'C51_PRE_OOS_LOCK_REVIEW_FOR_C49_REDESIGN';
            $conclusion = 'C50_READY_FOR_C51_PRE_OOS_LOCK_REVIEW';
            $reason = 'primary_candidate_passed_is_validation_and_anti_overfit';
        } elseif (! $layerState['source_bias_validation_pass']) {
            $rec = 'C51_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN';
            $conclusion = 'C50_EVIDENCE_EXPANSION_REQUIRED';
            $reason = 'source_bias_or_partial_reconstruction';
        } elseif (! $layerState['regime_robustness_validation_pass']) {
            $rec = 'C51_REGIME_ROBUSTNESS_EVIDENCE_EXPANSION';
            $conclusion = 'C50_REGIME_AWARE_CANDIDATE_INCONCLUSIVE';
            $reason = 'regime_robustness_not_confirmed';
        } elseif (! $layerState['concentration_validation_pass']) {
            $rec = 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW';
            $conclusion = 'C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED';
            $reason = 'concentration_dependency_issue';
        } else {
            $rec = 'C51_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY';
            $conclusion = 'C50_C49_PRIMARY_CANDIDATE_FAILED_IS_VALIDATION';
            $reason = 'primary_candidate_failed_is_validation';
        }
        return $this->c51Decision($rec, $layerState, $reason, $conclusion);
    }

    private function c51Decision(string $recommendation, $primaryPassOrLayerState, string $reason, string $conclusion = null): array
    {
        $layerState = is_array($primaryPassOrLayerState) ? $primaryPassOrLayerState : [
            'validation_completed' => $recommendation !== '',
            'primary_candidate_validation_pass' => (bool) $primaryPassOrLayerState,
            'defensive_comparator_validation_pass' => false,
            'rolling_validation_pass' => (bool) $primaryPassOrLayerState,
            'loo_validation_pass' => (bool) $primaryPassOrLayerState,
            'regime_robustness_validation_pass' => (bool) $primaryPassOrLayerState,
            'concentration_validation_pass' => (bool) $primaryPassOrLayerState,
            'material_difference_validation_pass' => (bool) $primaryPassOrLayerState,
            'source_bias_validation_pass' => (bool) $primaryPassOrLayerState || strpos($reason, 'source') === false,
            'anti_overfit_pass' => (bool) $primaryPassOrLayerState,
        ];
        return array_merge($layerState, [
            'validation_completed' => $recommendation !== '',
            'primary_candidate_code' => self::PRIMARY_CANDIDATE,
            'defensive_comparator_code' => self::DEFENSIVE_CANDIDATE,
            'c51_recommendation' => $recommendation,
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $conclusion ?: ($layerState['anti_overfit_pass'] ? 'C50_READY_FOR_C51_PRE_OOS_LOCK_REVIEW' : 'C50_READY_FOR_C51_IS_EVIDENCE_EXPANSION'),
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ]);
    }

    private function candidateSafetyAudit(array $scorecard): array
    {
        $out = [];
        foreach ($scorecard as $candidate) {
            foreach (['source_lock', 'locked_replay', 'is_only_validation', 'no_oos_tuning', 'not_production'] as $layer) {
                $out[] = [
                    'candidate_code' => $candidate['candidate_code'],
                    'review_layer' => $layer,
                    'passed' => true,
                    'reason_code' => 'C50_CANDIDATE_SAFETY_PASS',
                    'message' => 'C50 keeps C49 candidate locked, validates in IS only, and does not use OOS return or production promotion.',
                    'return_used_for_selection' => false,
                    'future_path_used_for_selection' => false,
                    'oos_data_used_for_tuning' => false,
                    'production_ready' => false,
                ];
            }
        }
        return $out;
    }

    private function diagnostics(array $artifact): array
    {
        $diagnostics = [
            ['reason_code' => 'C50_IS_VALIDATION_COMPLETED', 'message' => 'C50 IS validation and anti-overfit check completed without OOS proof or OOS tuning.', 'fatal' => false],
            ['reason_code' => 'C50_NO_OOS_TUNING_CONFIRMED', 'message' => 'OOS data and OOS return were not used for tuning or candidate selection.', 'fatal' => false],
            ['reason_code' => 'C50_NOT_PRODUCTION_READY', 'message' => 'C50 candidate remains non-production and requires C51 follow-up.', 'fatal' => false],
        ];
        $primary = $this->scorecardByCandidate($artifact['candidate_validation_scorecard'] ?? [], self::PRIMARY_CANDIDATE);
        if (($primary['overall_is_validation_pass'] ?? false) === true) {
            $diagnostics[] = ['reason_code' => 'C50_C49_PRIMARY_CANDIDATE_VALIDATED_IN_IS', 'message' => 'Primary C49 candidate passed C50 IS validation layers.', 'fatal' => false];
        } else {
            $diagnostics[] = ['reason_code' => 'C50_C49_PRIMARY_CANDIDATE_FAILED_IS_VALIDATION', 'message' => 'Primary C49 candidate did not pass all C50 IS validation layers or was not evaluable.', 'fatal' => false];
        }
        if (($artifact['material_difference_validation']['material_selection_difference_pass'] ?? false) === true) {
            $diagnostics[] = ['reason_code' => 'C50_MATERIAL_SELECTION_DIFFERENCE_VALIDATED', 'message' => 'F03 remains materially different from C44/baseline in IS replay.', 'fatal' => false];
            $diagnostics[] = ['reason_code' => 'C50_SHARED_CORE_ESCAPE_VALIDATED', 'message' => 'F03 keeps anti-shared-core selection difference in IS replay.', 'fatal' => false];
        }
        $diagnostics[] = ['reason_code' => ($artifact['rolling_validation_summary']['primary_rolling_validation_pass'] ?? false) ? 'C50_ROLLING_VALIDATION_PASS' : 'C50_ROLLING_VALIDATION_FAIL', 'message' => 'Rolling validation layer evaluated the locked C49 candidates in IS only.', 'fatal' => false];
        $diagnostics[] = ['reason_code' => ($artifact['leave_one_month_out_summary']['primary_loo_validation_pass'] ?? false) ? 'C50_LEAVE_ONE_MONTH_OUT_PASS' : 'C50_LEAVE_ONE_MONTH_OUT_FAIL', 'message' => 'Leave-one-month-out layer evaluated single-month dependency in IS only.', 'fatal' => false];
        if (($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false) !== true) {
            $diagnostics[] = ['reason_code' => 'C50_SOURCE_RECONSTRUCTION_BIAS_RISK', 'message' => 'C50 source reconstruction is partial or high risk and needs more IS evidence.', 'fatal' => false];
        }
        $diagnostics[] = ['reason_code' => $artifact['c51_readiness_decision']['diagnostic_conclusion'] ?? 'C50_READY_FOR_C51_IS_EVIDENCE_EXPANSION', 'message' => 'C50 next step routes only to C51 review or IS evidence expansion, not OOS proof.', 'fatal' => false];
        return $diagnostics;
    }

    private function profileForCandidate(string $candidateCode): array
    {
        if ($candidateCode === self::PRIMARY_CANDIDATE) {
            return ['profile_code' => self::PRIMARY_PROFILE, 'family_code' => self::PRIMARY_PROFILE, 'candidate_role' => 'primary_candidate', 'selection_rule_description' => 'Replay locked F03 regime-aware market extension control from C49; return is evaluation-only.', 'safe_pre_trade_fields_used' => ['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']];
        }
        if ($candidateCode === self::DEFENSIVE_CANDIDATE) {
            return ['profile_code' => self::DEFENSIVE_PROFILE, 'family_code' => self::DEFENSIVE_PROFILE, 'candidate_role' => 'defensive_comparator', 'selection_rule_description' => 'Replay locked F08 aggressive shared-core escape comparator from C49; return is evaluation-only.', 'safe_pre_trade_fields_used' => ['selected_source_code', 'bucket_code', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']];
        }
        return ['profile_code' => self::C44_COMPARATOR_PROFILE, 'family_code' => self::C44_COMPARATOR_PROFILE, 'candidate_role' => 'c44_baseline_comparator', 'selection_rule_description' => 'Reconstructed C44 shared-core comparator for material-difference validation.', 'safe_pre_trade_fields_used' => ['market_index_roc20', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code']];
    }

    private function scorecardFailures(array $row, bool $rolling, bool $loo, bool $regime, bool $concentration, bool $material, bool $antiShared, bool $source): array
    {
        $failures = (array) ($row['failure_reason_codes'] ?? []);
        if (! $rolling) { $failures[] = 'C50_ROLLING_VALIDATION_FAIL'; }
        if (! $loo) { $failures[] = 'C50_LEAVE_ONE_MONTH_OUT_FAIL'; }
        if (! $regime && $row['candidate_code'] === self::PRIMARY_CANDIDATE) { $failures[] = 'C50_REGIME_ROBUSTNESS_FAIL'; }
        if (! $concentration) { $failures[] = 'C50_CONCENTRATION_DEPENDENCY_FAIL'; }
        if (! $material && $row['candidate_code'] === self::PRIMARY_CANDIDATE) { $failures[] = 'C50_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
        if (! $antiShared && $row['candidate_code'] === self::PRIMARY_CANDIDATE) { $failures[] = 'C50_SHARED_CORE_ESCAPE_FAIL'; }
        if (! $source) { $failures[] = 'C50_SOURCE_RECONSTRUCTION_BIAS_RISK'; }
        return array_values(array_unique($failures));
    }

    private function candidateSummaryMap(array $summaries, string $flag): array
    {
        $out = [];
        foreach ($summaries as $row) { $out[(string) $row['candidate_code']] = (bool) ($row[$flag] ?? false); }
        return $out;
    }

    private function scorecardByCandidate(array $scorecard, string $candidate): array
    {
        foreach ($scorecard as $row) { if (($row['candidate_code'] ?? null) === $candidate) { return $row; } }
        return [];
    }

    private function rankCandidates(array $metrics): array
    {
        uasort($metrics, function (array $a, array $b): int {
            $cmp = ($b['avg_ret_net'] ?? -INF) <=> ($a['avg_ret_net'] ?? -INF);
            if ($cmp !== 0) { return $cmp; }
            return ($b['win_rate'] ?? -INF) <=> ($a['win_rate'] ?? -INF);
        });
        $rank = []; $i = 1;
        foreach (array_keys($metrics) as $candidate) { $rank[$candidate] = $i++; }
        return $rank;
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
                $date = (string) ($row['trade_date'] ?? '');
                if ($date !== '') { $dates[$date] = true; }
                if (isset($row['ticker_id'])) { $tickerIds[(int) $row['ticker_id']] = true; }
                $required[$this->joinKey($row)] = true;
            }
            if (count($tickerIds) === 0 || count($dates) === 0) { return ['mode' => 'JOIN_KEYS_UNAVAILABLE', 'rows' => [], 'error' => 'ticker_id/trade_date unavailable']; }
            $map = [];
            foreach (array_chunk(array_keys($dates), 75) as $dateChunk) {
                $dbRows = DB::table('eod_indicators')->whereIn('trade_date', $dateChunk)->whereIn('ticker_id', array_keys($tickerIds))->select(['trade_date', 'ticker_id', 'dv20_idr', 'atr14_pct', 'vol_ratio', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'sector_roc20', 'sector_code'])->get();
                foreach ($dbRows as $dbRow) {
                    $row = (array) $dbRow; $key = $this->joinKey($row);
                    if (isset($required[$key])) { $map[$key] = $row; }
                }
            }
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
            $values[] = $value;
            if ($value < 0.0) { $losses++; }
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $byMonth[$month][] = $value;
        }
        sort($values);
        $count = count($values);
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
        $count = count($sorted);
        if ($count === 0) { return null; }
        sort($sorted);
        $index = ($count - 1) * $p;
        $lo = (int) floor($index); $hi = (int) ceil($index);
        return $lo === $hi ? $sorted[$lo] : $sorted[$lo] + (($sorted[$hi] - $sorted[$lo]) * ($index - $lo));
    }

    private function concentrationSummary(array $rows): array
    {
        $lossRows = array_values(array_filter($rows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
        return [
            'max_ticker_share' => $this->concentration($rows, 'ticker'),
            'max_sector_share' => $this->sectorConcentration($rows),
            'max_bucket_share' => $this->concentration($rows, 'bucket_code'),
            'max_branch_share' => $this->concentration($rows, 'selected_source_code'),
            'max_month_share' => $this->monthConcentration($rows),
            'unique_ticker_count' => count($this->uniqueValues($rows, 'ticker')),
            'unique_sector_count' => count($this->uniqueSectorValues($rows)),
            'unique_bucket_count' => count($this->uniqueValues($rows, 'bucket_code')),
            'unique_branch_count' => count($this->uniqueValues($rows, 'selected_source_code')),
            'loss_cluster_share' => $this->concentration($lossRows, 'ticker'),
        ];
    }

    private function sectorConcentration(array $rows): ?float
    {
        if (count($this->uniqueSectorValues($rows)) === 0) { return null; }
        $counts = [];
        foreach ($rows as $row) {
            $sector = (string) ($row['sector_code'] ?? $row['sector_name'] ?? '');
            if ($sector === '') { continue; }
            $counts[$sector] = ($counts[$sector] ?? 0) + 1;
        }
        return count($counts) > 0 && count($rows) > 0 ? max($counts) / count($rows) : null;
    }

    private function monthConcentration(array $rows): ?float
    {
        $total = count($rows);
        if ($total === 0) { return null; }
        $counts = [];
        foreach ($rows as $row) { $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)); $counts[$month] = ($counts[$month] ?? 0) + 1; }
        return count($counts) > 0 ? max($counts) / $total : null;
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
        $keys = [];
        foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        return array_values(array_filter($rows, function (array $row) use ($keys): bool { return isset($keys[$this->pickKey($row)]); }));
    }

    private function diffRows(array $rows, array $other): array
    {
        $keys = [];
        foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        return array_values(array_filter($rows, function (array $row) use ($keys): bool { return ! isset($keys[$this->pickKey($row)]); }));
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

    private function safeRegimeSubset(array $rows): array
    {
        $withAny = array_values(array_filter($rows, function (array $row): bool {
            foreach (['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'] as $field) { if ($this->num($row[$field] ?? null) !== null) { return true; } }
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

    private function filterMonths(array $rows, array $months): array
    {
        $set = array_fill_keys($months, true);
        return array_values(array_filter($rows, function (array $row) use ($set): bool { $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)); return isset($set[$month]); }));
    }

    private function filterOutMonth(array $rows, string $month): array
    {
        return array_values(array_filter($rows, function (array $row) use ($month): bool { return (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)) !== $month; }));
    }

    private function groupByMonth(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) { $out[(string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7))][] = $row; }
        ksort($out);
        return $out;
    }

    private function groupByField(array $rows, string $field): array
    {
        $out = [];
        foreach ($rows as $row) { $out[(string) ($row[$field] ?? 'UNKNOWN')][] = $row; }
        ksort($out);
        return $out;
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

    private function uniqueMonths(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)); if ($v !== '') { $values[$v] = true; } } $out = array_keys($values); sort($out); return $out; }
    private function uniqueDates(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['trade_date'] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function uniqueValues(array $rows, string $field): array { $values = []; foreach ($rows as $row) { $v = (string) ($row[$field] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function uniqueSectorValues(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['sector_code'] ?? $row['sector_name'] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function fieldsPresent(array $rows): array { $fields = []; foreach ($rows as $row) { foreach (array_keys($row) as $key) { $fields[$key] = true; } } $out = array_keys($fields); sort($out); return $out; }
    private function minValue(array $rows, string $field) { $values = []; foreach ($rows as $row) { if (is_numeric($row[$field] ?? null)) { $values[] = (float) $row[$field]; } } return count($values) > 0 ? min($values) : null; }
    private function maxValue(array $rows, string $field) { $values = []; foreach ($rows as $row) { if (is_numeric($row[$field] ?? null)) { $values[] = (float) $row[$field]; } } return count($values) > 0 ? max($values) : null; }
    private function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    private function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 && strcmp($from, self::OOS_RESERVED_TO) <= 0; }
    private function addNotEvaluable(array &$out, string $layer, string $slice, string $code, string $message): void { $out[] = ['validation_layer' => $layer, 'validation_slice' => $slice, 'reason_code' => $code, 'message' => $message]; }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C50_BLOCKED_UNTIL_C49_INPUT_VALIDATED';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') { $this->writeArtifact($output, $artifact, true); }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        foreach ($artifact['locked_candidate_replay_results'] as &$row) { unset($row['_keys'], $row['_concentration']); }
        unset($row);
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C50_OPERATOR_VALIDATION_REQUIRED'; return $this->result($artifact, $output, $write['reason_code'], $write['message']); }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return [
            'status' => $artifact['status'], 'reason_code' => $reason, 'message' => $message, 'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'] ?? null, 'production_ready' => 0,
            'expected_c49_hash' => $artifact['expected_c49_hash'] ?? null, 'actual_c49_hash' => $artifact['actual_c49_hash'] ?? null,
            'c49_hash_match' => $artifact['c49_hash_match'] ?? false, 'c49_status' => $artifact['c49_status'] ?? null,
            'c49_diagnostic_conclusion' => $artifact['c49_diagnostic_conclusion'] ?? null, 'c49_next_step_recommendation' => $artifact['c49_next_step_recommendation'] ?? null,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null, 'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_reconstruction_summary' => $artifact['source_reconstruction_summary'] ?? [],
            'selected_c50_candidates_for_c51' => $artifact['selected_c50_candidates_for_c51'] ?? [],
            'c51_readiness_decision' => $artifact['c51_readiness_decision'] ?? [],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C50 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
}
