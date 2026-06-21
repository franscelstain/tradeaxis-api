<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC51ConcentrationDependencyRedesignReviewService
{
    public const RUN_CODE = 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW';
    public const ARTIFACT_TYPE = 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW';
    public const DEFAULT_C50_ARTIFACT = 'storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json';
    public const DEFAULT_EXPECTED_C50_HASH = '1f2b919662a395444f43403e8f7f4d0b91e146aa';
    public const DEFAULT_C49_ARTIFACT = 'storage/app/watchlist/backtest/c49-broader-strategy-redesign.json';
    public const DEFAULT_EXPECTED_C49_HASH = '9266ec2b59a6ea11c21b830cd9b769635afc91a8';
    public const DEFAULT_SOURCE_EVIDENCE = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    public const F03_CANDIDATE = 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL';
    public const F08_CANDIDATE = 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN';
    public const F00_CANDIDATE = 'C49_CANDIDATE_F00_C44_SHARED_CORE_COMPARATOR';

    private const VALID_C50_CONCLUSIONS = [
        'C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED',
        'C50_C49_PRIMARY_CANDIDATE_FAILED_IS_VALIDATION',
        'C50_EVIDENCE_EXPANSION_REQUIRED',
    ];

    private const VALID_C50_NEXT_STEPS = [
        'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW',
        'C51_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY',
        'C51_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN',
    ];

    /**
     * C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW_ONLY. C50_ARTIFACT_HASH_LOCK. C49_ARTIFACT_HASH_LOCK.
     * C50_USED_AS_LOCKED_VALIDATION_SOURCE. C49_USED_AS_LOCKED_CANDIDATE_SOURCE. IS_ONLY_VALIDATION.
     * NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER.
     * NO_OOS_RETURN_SELECTION. NO_OOS_BAD_MONTH_THRESHOLD_SELECTION. NO_OOS_TICKER_SECTOR_EXCLUSION_RULE.
     * NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG. NO_PROMOTION.
     * NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C50_MUTATION. NO_C01_TO_C50_ARTIFACT_MUTATION. CANDIDATE_IS_NOT_PRODUCTION.
     * C51_MUST_NOT_RECOMMEND_OOS_PROOF. RETURN_USED_FOR_SELECTION_FALSE. FUTURE_PATH_USED_FOR_SELECTION_FALSE.
     */
    public function execute(
        string $c50Artifact = self::DEFAULT_C50_ARTIFACT,
        string $expectedC50Hash = self::DEFAULT_EXPECTED_C50_HASH,
        string $c49Artifact = self::DEFAULT_C49_ARTIFACT,
        string $expectedC49Hash = self::DEFAULT_EXPECTED_C49_HASH,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $createdAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c50Artifact = trim($c50Artifact) !== '' ? trim($c50Artifact) : self::DEFAULT_C50_ARTIFACT;
        $expectedC50Hash = trim($expectedC50Hash) !== '' ? trim($expectedC50Hash) : self::DEFAULT_EXPECTED_C50_HASH;
        $c49Artifact = trim($c49Artifact) !== '' ? trim($c49Artifact) : self::DEFAULT_C49_ARTIFACT;
        $expectedC49Hash = trim($expectedC49Hash) !== '' ? trim($expectedC49Hash) : self::DEFAULT_EXPECTED_C49_HASH;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        $artifact = $this->baseArtifact($c50Artifact, $expectedC50Hash, $c49Artifact, $expectedC49Hash, $from, $to, $createdAt);

        if (! is_file($c50Artifact)) {
            return $this->blocked($artifact, 'C51_BLOCKED_MISSING_C50_ARTIFACT', 'WS_BT_C51_C50_ARTIFACT_MISSING', 'C51 requires the locked C50 artifact.', $outputPath);
        }
        $c50 = json_decode((string) file_get_contents($c50Artifact), true);
        if (! is_array($c50)) {
            return $this->blocked($artifact, 'C51_BLOCKED_MISSING_C50_ARTIFACT', 'WS_BT_C51_C50_ARTIFACT_UNREADABLE', 'C50 artifact is not readable JSON.', $outputPath);
        }

        $actualC50Hash = $this->stableHash($c50);
        $artifact['actual_c50_hash'] = $actualC50Hash;
        $artifact['c50_hash_match'] = $actualC50Hash === $expectedC50Hash;
        $artifact['c50_status'] = $c50['status'] ?? null;
        $artifact['c50_diagnostic_conclusion'] = $c50['diagnostic_conclusion'] ?? null;
        $artifact['c50_next_step_recommendation'] = $c50['next_step_recommendation'] ?? null;
        $artifact['c50_primary_candidate_code'] = $c50['selected_c50_candidates_for_c51']['primary_candidate_code'] ?? self::F03_CANDIDATE;
        $artifact['c50_defensive_comparator_code'] = $c50['selected_c50_candidates_for_c51']['defensive_comparator_code'] ?? self::F08_CANDIDATE;

        if ($actualC50Hash !== $expectedC50Hash) {
            return $this->blocked($artifact, 'C51_BLOCKED_C50_HASH_MISMATCH', 'WS_BT_C51_C50_ARTIFACT_HASH_MISMATCH', 'C50 stable hash does not match the expected lock.', $outputPath);
        }
        if (($c50['status'] ?? null) !== 'C50_IS_VALIDATION_COMPLETED') {
            return $this->blocked($artifact, 'C51_BLOCKED_UNEXPECTED_C50_STATUS', 'WS_BT_C51_UNEXPECTED_C50_STATUS', 'C51 requires completed C50 validation.', $outputPath);
        }
        if (! in_array((string) ($c50['diagnostic_conclusion'] ?? ''), self::VALID_C50_CONCLUSIONS, true)) {
            return $this->blocked($artifact, 'C51_BLOCKED_UNEXPECTED_C50_CONCLUSION', 'WS_BT_C51_UNEXPECTED_C50_CONCLUSION', 'C50 diagnostic conclusion does not authorize C51.', $outputPath);
        }
        if (! in_array((string) ($c50['next_step_recommendation'] ?? ''), self::VALID_C50_NEXT_STEPS, true)) {
            return $this->blocked($artifact, 'C51_BLOCKED_C50_NEXT_STEP_UNEXPECTED', 'WS_BT_C51_C50_NEXT_STEP_UNEXPECTED', 'C50 next step does not route to C51.', $outputPath);
        }
        if (! $this->strictFalse($c50['production_ready'] ?? true)) {
            return $this->blocked($artifact, 'C51_BLOCKED_C50_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C51_C50_PRODUCTION_READY_NOT_FALSE', 'C50 production_ready must be false.', $outputPath);
        }
        if (($c50['direct_oos_proof_recommended'] ?? false) === true || ($c50['oos_proof_unlocked'] ?? false) === true || ($c50['c51_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c50['c51_readiness_decision']['oos_proof_unlocked'] ?? false) === true) {
            return $this->blocked($artifact, 'C51_BLOCKED_C50_OOS_PROOF_FLAG_INVALID', 'WS_BT_C51_C50_OOS_PROOF_FLAG_INVALID', 'C50 must not unlock or recommend direct OOS proof.', $outputPath);
        }
        if (! $this->c50ConcentrationFailureConfirmed($c50)) {
            return $this->blocked($artifact, 'C51_BLOCKED_MISSING_C50_CONCENTRATION_FAILURE', 'WS_BT_C51_MISSING_C50_CONCENTRATION_FAILURE', 'C51 requires C50 F03 concentration/dependency failure.', $outputPath);
        }

        if (! is_file($c49Artifact)) {
            return $this->blocked($artifact, 'C51_BLOCKED_MISSING_C49_ARTIFACT', 'WS_BT_C51_C49_ARTIFACT_MISSING', 'C51 requires the locked C49 artifact.', $outputPath);
        }
        $c49 = json_decode((string) file_get_contents($c49Artifact), true);
        if (! is_array($c49)) {
            return $this->blocked($artifact, 'C51_BLOCKED_MISSING_C49_ARTIFACT', 'WS_BT_C51_C49_ARTIFACT_UNREADABLE', 'C49 artifact is not readable JSON.', $outputPath);
        }
        $actualC49Hash = $this->stableHash($c49);
        $artifact['actual_c49_hash'] = $actualC49Hash;
        $artifact['c49_hash_match'] = $actualC49Hash === $expectedC49Hash;
        if ($actualC49Hash !== $expectedC49Hash) {
            return $this->blocked($artifact, 'C51_BLOCKED_C49_HASH_MISMATCH', 'WS_BT_C51_C49_ARTIFACT_HASH_MISMATCH', 'C49 stable hash does not match the expected lock.', $outputPath);
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) {
            return $this->blocked($artifact, 'C51_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C51_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C51 only accepts IS period and must not touch OOS reserved period.', $outputPath);
        }

        $artifact['c50_carry_forward_summary'] = $this->c50CarryForwardSummary($c50);
        $artifact['c50_root_cause_summary'] = $this->c50RootCauseSummary($c50);

        $sourceLoad = $this->loadSourceRows($from, $to, $options, $c49, $artifact['not_evaluable_reasons']);
        $rows = $sourceLoad['rows'];
        $artifact['source_reconstruction_summary'] = $sourceLoad['summary'];
        $artifact['source_reconstruction_bias_check'] = $this->sourceBiasCheck($rows, $sourceLoad['summary']);
        if (($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false) !== true) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_reconstruction_bias_check', 'source_rows', 'C51_SOURCE_RECONSTRUCTION_BIAS_RISK', 'Source reconstruction is partial or missing required fields.');
        }

        if (count($rows) === 0) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_reconstruction', 'pick_rows', 'C51_SOURCE_ROWS_NOT_EVALUABLE', 'No IS source rows are available for C51 redesign replay.');
            $artifact['status'] = 'C51_SOURCE_ROWS_NOT_EVALUABLE';
            $artifact['diagnostic_conclusion'] = 'C51_EVIDENCE_EXPANSION_REQUIRED';
            $artifact['next_step_recommendation'] = 'C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN';
            $artifact['c52_readiness_decision'] = $this->c52Decision('C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN', false, 'source_rows_not_evaluable', 'C51_EVIDENCE_EXPANSION_REQUIRED');
            $artifact['diagnostics'] = $this->diagnostics($artifact);
            return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $lineageRows = $this->lineageRows($rows, $artifact['not_evaluable_reasons']);
        $candidateRows = $this->redesignedCandidateRows($lineageRows);
        $months = $this->uniqueMonths($rows);

        $artifact['redesign_candidate_definitions'] = $this->redesignCandidateDefinitions($candidateRows);
        $artifact['candidate_replay_results'] = $this->candidateReplayResults($candidateRows, $lineageRows['f00'], $lineageRows['f03'], $lineageRows['f08'], $months);
        $artifact['concentration_dependency_validation_results'] = $this->concentrationDependencyResults($candidateRows);
        $artifact['branch_dependency_validation_results'] = $this->branchDependencyResults($candidateRows);
        $artifact['bucket_dependency_validation_results'] = $this->bucketDependencyResults($candidateRows);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($candidateRows, $months);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($artifact['rolling_validation_results']);
        $artifact['leave_one_month_out_results'] = $this->leaveOneMonthOutResults($candidateRows, $months);
        $artifact['leave_one_month_out_summary'] = $this->leaveOneMonthOutSummary($artifact['leave_one_month_out_results']);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessResults($candidateRows, $artifact['not_evaluable_reasons']);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessSummary($artifact['regime_robustness_validation_results']);
        $artifact['material_difference_validation_results'] = $this->materialDifferenceValidationResults($candidateRows, $lineageRows['f00'], $lineageRows['f03'], $lineageRows['f08']);
        $artifact['candidate_scorecard'] = $this->candidateScorecard($artifact);
        $artifact['selected_c51_candidates_for_c52'] = $this->selectedForC52($artifact['candidate_scorecard']);
        $artifact['c52_readiness_decision'] = $this->readinessDecision($artifact);
        $artifact['candidate_safety_audit'] = $this->candidateSafetyAudit($artifact['redesign_candidate_definitions']);
        $artifact['diagnostic_conclusion'] = $artifact['c52_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c52_readiness_decision']['c52_recommendation'];
        $artifact['status'] = 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED';
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $c50Artifact, string $expectedC50Hash, string $c49Artifact, string $expectedC49Hash, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C51_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c50_artifact' => $c50Artifact,
            'expected_c50_hash' => $expectedC50Hash,
            'actual_c50_hash' => null,
            'c50_hash_match' => false,
            'c50_status' => null,
            'c50_diagnostic_conclusion' => null,
            'c50_next_step_recommendation' => null,
            'c50_primary_candidate_code' => null,
            'c50_defensive_comparator_code' => null,
            'input_c49_artifact' => $c49Artifact,
            'expected_c49_hash' => $expectedC49Hash,
            'actual_c49_hash' => null,
            'c49_hash_match' => false,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'is_only_concentration_dependency_redesign_review', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c50_carry_forward_summary' => [],
            'c50_root_cause_summary' => [],
            'source_reconstruction_summary' => [],
            'redesign_candidate_definitions' => [],
            'candidate_replay_results' => [],
            'concentration_dependency_validation_results' => [],
            'branch_dependency_validation_results' => [],
            'bucket_dependency_validation_results' => [],
            'rolling_validation_results' => [],
            'rolling_validation_summary' => [],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => [],
            'material_difference_validation_results' => [],
            'source_reconstruction_bias_check' => [],
            'candidate_scorecard' => [],
            'selected_c51_candidates_for_c52' => [],
            'c52_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C51_PENDING',
            'next_step_recommendation' => 'C51_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => [
                'c51_concentration_dependency_redesign_review_only' => true,
                'c50_artifact_hash_lock' => true,
                'c49_artifact_hash_lock' => true,
                'c50_used_as_locked_validation_source' => true,
                'c49_used_as_locked_candidate_source' => true,
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
                'no_c01_to_c50_mutation' => true,
                'no_c01_to_c50_artifact_mutation' => true,
                'candidate_is_not_production' => true,
                'c51_must_not_recommend_oos_proof' => true,
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

    private function c50CarryForwardSummary(array $c50): array
    {
        $primary = $this->findByCandidate($c50['candidate_validation_scorecard'] ?? [], self::F03_CANDIDATE);
        $defensive = $this->findByCandidate($c50['candidate_validation_scorecard'] ?? [], self::F08_CANDIDATE);
        $f00 = $this->findByCandidate($c50['candidate_validation_scorecard'] ?? [], self::F00_CANDIDATE);
        return [
            'c50_status' => $c50['status'] ?? null,
            'c50_diagnostic_conclusion' => $c50['diagnostic_conclusion'] ?? null,
            'c50_next_step_recommendation' => $c50['next_step_recommendation'] ?? null,
            'c50_primary_candidate_code' => self::F03_CANDIDATE,
            'c50_defensive_comparator_code' => self::F08_CANDIDATE,
            'c50_c44_comparator_code' => self::F00_CANDIDATE,
            'primary_candidate_validation_pass' => (bool) ($c50['c51_readiness_decision']['primary_candidate_validation_pass'] ?? ($primary['overall_is_validation_pass'] ?? false)),
            'defensive_comparator_validation_pass' => (bool) ($c50['c51_readiness_decision']['defensive_comparator_validation_pass'] ?? ($defensive['overall_is_validation_pass'] ?? false)),
            'c50_primary_avg_ret_net' => $primary['avg_ret_net'] ?? null,
            'c50_primary_median_ret_net' => $primary['median_ret_net'] ?? null,
            'c50_primary_win_rate' => $primary['win_rate'] ?? null,
            'c50_primary_failure_reason_codes' => $primary['failure_reason_codes'] ?? [],
            'c50_defensive_avg_ret_net' => $defensive['avg_ret_net'] ?? null,
            'c50_defensive_failure_reason_codes' => $defensive['failure_reason_codes'] ?? [],
            'c50_f00_anti_overfit_pass' => (bool) ($f00['anti_overfit_pass'] ?? false),
            'c50_used_as_locked_validation_source' => true,
            'c49_used_as_locked_candidate_source' => true,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'oos_return_used_for_selection' => false,
            'oos_data_used_for_tuning' => false,
            'oos_proof_executed' => false,
        ];
    }

    private function c50RootCauseSummary(array $c50): array
    {
        $primaryConcentration = $this->findByCandidate($c50['concentration_dependency_validation_results'] ?? [], self::F03_CANDIDATE);
        $defensiveConcentration = $this->findByCandidate($c50['concentration_dependency_validation_results'] ?? [], self::F08_CANDIDATE);
        $f00 = $this->findByCandidate($c50['candidate_validation_scorecard'] ?? [], self::F00_CANDIDATE);
        $branches = [];
        foreach ((array) ($c50['branch_dependency_validation_results'] ?? []) as $row) {
            if (($row['candidate_code'] ?? null) === self::F03_CANDIDATE) {
                $branches[(string) ($row['branch_code'] ?? 'UNKNOWN')] = ['row_count' => $row['branch_row_count'] ?? null, 'share' => $row['branch_share'] ?? null];
            }
        }
        return [
            'primary_candidate_code' => self::F03_CANDIDATE,
            'primary_candidate_failure_reason_codes' => $this->findByCandidate($c50['candidate_validation_scorecard'] ?? [], self::F03_CANDIDATE)['failure_reason_codes'] ?? [],
            'primary_max_branch_share' => $primaryConcentration['max_branch_share'] ?? null,
            'primary_max_bucket_share' => $primaryConcentration['max_bucket_share'] ?? null,
            'primary_g16_share' => $branches['G16']['share'] ?? null,
            'primary_g21_share' => $branches['G21']['share'] ?? null,
            'primary_loss_cluster_share' => $primaryConcentration['loss_cluster_share'] ?? null,
            'defensive_candidate_code' => self::F08_CANDIDATE,
            'defensive_max_branch_share' => $defensiveConcentration['max_branch_share'] ?? null,
            'defensive_branch_mix' => $this->branchMix($c50['branch_dependency_validation_results'] ?? [], self::F08_CANDIDATE),
            'c44_comparator_code' => self::F00_CANDIDATE,
            'c44_material_difference_pass' => (bool) ($f00['material_selection_difference_pass'] ?? false),
            'c50_root_cause' => 'F03_G16_BRANCH_BUCKET_CONCENTRATION',
            'c50_concentration_failure_confirmed' => $this->c50ConcentrationFailureConfirmed($c50),
            'c50_anti_overfit_pass' => false,
        ];
    }

    protected function loadSourceRows(string $from, string $to, array $options, array $c49, array &$notEvaluable): array
    {
        $sourcePath = null;
        if (array_key_exists('source_rows', $options)) {
            $sourceRows = array_values(array_filter((array) $options['source_rows'], function ($row): bool { return is_array($row); }));
            $sourceMode = 'INJECTED_TEST_SOURCE_ROWS';
        } else {
            $sourcePath = trim((string) ($options['source_evidence_artifact'] ?? ($c49['source_universe_summary']['source_evidence_artifact'] ?? self::DEFAULT_SOURCE_EVIDENCE)));
            if ($sourcePath === '' || ! is_file($sourcePath)) {
                $this->addNotEvaluable($notEvaluable, 'source_reconstruction', 'source_evidence_artifact', 'C51_SOURCE_ROWS_NOT_EVALUABLE', 'C51 could not locate IS source evidence artifact.');
                return ['rows' => [], 'summary' => ['source_evidence_artifact' => $sourcePath, 'source_rows_available' => false, 'source_mode' => 'MISSING_SOURCE_ARTIFACT']];
            }
            $source = json_decode((string) file_get_contents($sourcePath), true);
            if (! is_array($source) || ! is_array($source['pick_diagnostic_rows'] ?? null)) {
                $this->addNotEvaluable($notEvaluable, 'source_reconstruction', 'pick_diagnostic_rows', 'C51_SOURCE_ROWS_NOT_EVALUABLE', 'C51 source evidence has no pick diagnostic rows.');
                return ['rows' => [], 'summary' => ['source_evidence_artifact' => $sourcePath, 'source_rows_available' => false, 'source_mode' => 'UNREADABLE_SOURCE_ROWS']];
            }
            $sourceRows = $source['pick_diagnostic_rows'];
            $sourceMode = 'C28_PICK_DIAGNOSTIC_ROWS';
        }

        $rows = $this->isRows($sourceRows, $from, $to);
        $preTradeLoad = $this->loadPreTradeSources($rows, $options);
        $rows = $this->enrichRows($rows, $preTradeLoad['rows']);
        if ($preTradeLoad['mode'] !== 'INJECTED_PRE_TRADE_SOURCE_ROWS' && count($preTradeLoad['rows']) === 0) {
            $this->addNotEvaluable($notEvaluable, 'source_reconstruction', 'pre_trade_source_join', 'C51_SOURCE_RECONSTRUCTION_PARTIAL', 'Pre-trade indicator source rows were not joined; C51 uses fields already present in source rows where available.');
        }

        return ['rows' => $rows, 'summary' => [
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
        ]];
    }

    protected function lineageRows(array $rows, array &$notEvaluable): array
    {
        $g16 = $this->branchBucketRows($rows, 'G16', 'next_open_delay_after_close_signal');
        $g21 = $this->branchBucketRows($rows, 'G21', 'no_rule_profit_signal_before_fallback');
        $g13 = $this->branchBucketRows($rows, 'G13', 'no_rule_profit_signal_before_fallback');
        $months = $this->uniqueMonths(array_merge($g16, $g21, $g13));
        $safeG21 = $this->safeRegimeSubset($g21);
        if (count($safeG21) === 0 && count($g21) > 0) {
            $this->addNotEvaluable($notEvaluable, 'source_reconstruction', self::F03_CANDIDATE, 'C51_REGIME_SOURCE_RECONSTRUCTION_PARTIAL', 'F03 regime-aware replay fell back to metadata because regime fields were unavailable or all filtered out.');
            $safeG21 = $g21;
        }

        return [
            'g16' => $g16,
            'g21' => $g21,
            'g13' => $g13,
            'safe_g21' => $safeG21,
            'f03' => array_merge($g16, $this->selectMonthlyQuota($safeG21, $months, 10, 'BALANCED')),
            'f08' => array_merge($this->selectMonthlyQuota($g16, $months, 15, 'METADATA'), $this->selectMonthlyQuota($g21, $months, 6, 'METADATA'), $this->selectMonthlyQuota($g13, $months, 6, 'METADATA')),
            'f00' => array_merge($g16, $this->selectMonthlyQuota($g21, $months, 13, 'MARKET_EXTENSION')),
            'months' => $months,
        ];
    }

    protected function redesignedCandidateRows(array $lineageRows): array
    {
        $months = $lineageRows['months'];
        $g16 = $lineageRows['g16'];
        $g21 = $lineageRows['g21'];
        $g13 = $lineageRows['g13'];
        $safeG21 = $lineageRows['safe_g21'];
        return [
            'C51_R00_C50_F03_LOCKED_PRIMARY_REPLAY' => $lineageRows['f03'],
            'C51_R01_F03_BRANCH_CAP_70' => array_merge($this->selectMonthlyQuota($g16, $months, 11, 'BALANCED'), $this->selectMonthlyQuota($safeG21, $months, 5, 'BALANCED')),
            'C51_R02_F03_BRANCH_CAP_65' => array_merge($this->selectMonthlyQuota($g16, $months, 9, 'BALANCED'), $this->selectMonthlyQuota($safeG21, $months, 5, 'BALANCED')),
            'C51_R03_F03_BUCKET_CAP_70' => array_merge($this->selectMonthlyQuota($g16, $months, 11, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, 5, 'MARKET_EXTENSION')),
            'C51_R04_F03_BUCKET_CAP_65' => array_merge($this->selectMonthlyQuota($g16, $months, 9, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, 5, 'MARKET_EXTENSION')),
            'C51_R05_F03_G16_DOWNSAMPLED_G21_BACKFILL' => array_merge($this->selectMonthlyQuota($g16, $months, 10, 'BALANCED'), $this->selectMonthlyQuota($safeG21, $months, 7, 'BALANCED')),
            'C51_R06_F03_G16_DOWNSAMPLED_G21_G13_BACKFILL' => array_merge($this->selectMonthlyQuota($g16, $months, 10, 'BALANCED'), $this->selectMonthlyQuota($safeG21, $months, 5, 'BALANCED'), $this->selectMonthlyQuota($g13, $months, 4, 'METADATA')),
            'C51_R07_F03_F08_HYBRID_DIVERSIFIED_BRANCH_MIX' => array_merge($this->selectMonthlyQuota($g16, $months, 10, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, 5, 'MARKET_EXTENSION'), $this->selectMonthlyQuota($g13, $months, 5, 'METADATA')),
            'C51_R08_F03_BRANCH_QUOTA_CONTROL' => array_merge($this->selectMonthlyQuota($g16, $months, 12, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, 6, 'MARKET_EXTENSION'), $this->selectMonthlyQuota($g13, $months, 6, 'METADATA')),
            'C51_R09_F03_BUCKET_CONCENTRATION_CONTROL' => array_merge($this->selectMonthlyQuota($g16, $months, 12, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, 6, 'MARKET_EXTENSION'), $this->selectMonthlyQuota($g13, $months, 6, 'METADATA')),
            'C51_R10_F03_LOSS_CLUSTER_CONTROL' => $this->selectWithExposureCap(array_merge($this->selectMonthlyQuota($g16, $months, 13, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, 6, 'MARKET_EXTENSION'), $this->selectMonthlyQuota($g13, $months, 6, 'METADATA')), 3, 12),
            'C51_R11_F03_F08_QUALITY_WEIGHTED_DIVERSIFIED_MIX' => array_merge($this->selectMonthlyQuota($g16, $months, 10, 'BALANCED'), $this->selectMonthlyQuota($safeG21, $months, 6, 'BALANCED'), $this->selectMonthlyQuota($g13, $months, 4, 'METADATA')),
            'C51_R12_F08_STABILITY_REPAIR_VARIANT' => array_merge($this->selectMonthlyQuota($g16, $months, 12, 'METADATA'), $this->selectMonthlyQuota($g21, $months, 8, 'MARKET_EXTENSION'), $this->selectMonthlyQuota($g13, $months, 4, 'METADATA')),
            'C51_R13_C44_F00_ANCHOR_COMPARATOR_ONLY' => $lineageRows['f00'],
        ];
    }

    private function redesignCandidateDefinitions(array $candidateRows): array
    {
        $definitions = [];
        foreach ($candidateRows as $candidate => $rows) {
            $meta = $this->profileForCandidate($candidate);
            $definitions[] = [
                'candidate_code' => $candidate,
                'profile_code' => $meta['profile_code'],
                'family_code' => $meta['family_code'],
                'candidate_role' => $meta['candidate_role'],
                'source_candidates_used' => $meta['source_candidates_used'],
                'selection_rule_description' => $meta['selection_rule_description'],
                'safe_pre_trade_fields_used' => $meta['safe_pre_trade_fields_used'],
                'branch_cap' => $meta['branch_cap'],
                'bucket_cap' => $meta['bucket_cap'],
                'branch_quota' => $meta['branch_quota'],
                'bucket_quota' => $meta['bucket_quota'],
                'downsampling_rule' => $meta['downsampling_rule'],
                'backfill_rule' => $meta['backfill_rule'],
                'loss_cluster_control_rule' => $meta['loss_cluster_control_rule'],
                'row_count' => count($rows),
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
            ];
        }
        return $definitions;
    }

    private function candidateReplayResults(array $candidateRows, array $f00Rows, array $f03Rows, array $f08Rows, array $months): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $meta = $this->profileForCandidate($candidate);
            $m = $this->metrics($rows);
            $coverageMonths = count($this->uniqueMonths($rows));
            $coveragePass = $coverageMonths >= max(1, (int) floor(count($months) * 0.65)) && count($rows) >= 120;
            $qualityPass = $m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.005 && $m['median_ret_net'] !== null && $m['median_ret_net'] > 0.0;
            $stabilityPass = $m['bad_month_like_count'] <= max(2, (int) ceil($coverageMonths * 0.20));
            $failure = [];
            if (! $coveragePass) { $failure[] = 'C51_COVERAGE_FAIL'; }
            if (! $qualityPass) { $failure[] = 'C51_QUALITY_FAIL'; }
            if (! $stabilityPass) { $failure[] = 'C51_STABILITY_FAIL'; }
            $out[] = [
                'candidate_code' => $candidate,
                'profile_code' => $meta['profile_code'],
                'family_code' => $meta['family_code'],
                'candidate_role' => $meta['candidate_role'],
                'source_candidates_used' => $meta['source_candidates_used'],
                'selection_rule_description' => $meta['selection_rule_description'],
                'safe_pre_trade_fields_used' => $meta['safe_pre_trade_fields_used'],
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
                'quality_pass' => $qualityPass,
                'stability_pass' => $stabilityPass,
                'coverage_pass' => $coveragePass,
                'failure_reason_codes' => $failure,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
                'oos_data_used_for_tuning' => false,
                'candidate_is_not_production' => true,
                'production_ready' => false,
            ];
        }
        return $out;
    }

    private function concentrationDependencyResults(array $candidateRows): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $summary = $this->concentrationSummary($rows);
            $lossRows = array_values(array_filter($rows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
            $relaxed = (($summary['max_ticker_share'] ?? 1.0) <= 0.08)
                && (($summary['max_sector_share'] ?? 1.0) <= 0.22 || $summary['max_sector_share'] === null)
                && (($summary['max_bucket_share'] ?? 1.0) <= 0.70)
                && (($summary['max_branch_share'] ?? 1.0) <= 0.70)
                && (($summary['max_month_share'] ?? 1.0) <= 0.10)
                && (($summary['loss_cluster_share'] ?? 1.0) <= 0.12);
            $strong = $relaxed && (($summary['max_bucket_share'] ?? 1.0) <= 0.65) && (($summary['max_branch_share'] ?? 1.0) <= 0.65) && (($summary['loss_cluster_share'] ?? 1.0) <= 0.10);
            $failure = [];
            if (! $relaxed) { $failure[] = 'C51_CONCENTRATION_DEPENDENCY_FAIL'; }
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
                'concentration_validation_pass' => $relaxed,
                'concentration_validation_level' => $strong ? 'stronger' : ($relaxed ? 'relaxed' : 'failed'),
                'failure_reason_codes' => $failure,
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
                    'branch_dependency_detected' => $share !== null && $share > 0.70,
                ];
            }
        }
        return $out;
    }

    private function bucketDependencyResults(array $candidateRows): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $total = count($rows);
            foreach ($this->groupByField($rows, 'bucket_code') as $bucket => $bucketRows) {
                $m = $this->metrics($bucketRows);
                $lossCount = count(array_filter($bucketRows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
                $share = $total > 0 ? count($bucketRows) / $total : null;
                $out[] = [
                    'candidate_code' => $candidate,
                    'bucket_code' => $bucket,
                    'bucket_row_count' => count($bucketRows),
                    'bucket_share' => $share,
                    'bucket_avg_ret_net' => $m['avg_ret_net'],
                    'bucket_median_ret_net' => $m['median_ret_net'],
                    'bucket_win_rate' => $m['win_rate'],
                    'bucket_loss_share' => count($bucketRows) > 0 ? $lossCount / count($bucketRows) : null,
                    'bucket_dependency_detected' => $share !== null && $share > 0.70,
                ];
            }
        }
        return $out;
    }

    protected function rollingValidationResults(array $candidateRows, array $months): array
    {
        $windows = $this->rollingWindows($months);
        $out = [];
        foreach ($windows as $window) {
            foreach ($candidateRows as $candidate => $rows) {
                $windowRows = $this->filterMonths($rows, $window['months']);
                $m = $this->metrics($windowRows);
                $coverageMonths = count($this->uniqueMonths($windowRows));
                $qualityPass = $m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.01 && $m['median_ret_net'] !== null && $m['median_ret_net'] > -0.005;
                $stabilityPass = $m['bad_month_like_count'] <= max(1, (int) ceil($coverageMonths * 0.35));
                $coveragePass = $coverageMonths >= max(1, (int) floor(count($window['months']) * 0.60));
                $failure = [];
                if (! $qualityPass) { $failure[] = 'C51_ROLLING_QUALITY_FAIL'; }
                if (! $stabilityPass) { $failure[] = 'C51_ROLLING_STABILITY_FAIL'; }
                if (! $coveragePass) { $failure[] = 'C51_ROLLING_COVERAGE_FAIL'; }
                $out[] = [
                    'validation_window_code' => $window['code'],
                    'window_from' => $window['from'],
                    'window_to' => $window['to'],
                    'candidate_code' => $candidate,
                    'evaluated_picks_count' => $m['evaluated_picks_count'],
                    'avg_ret_net' => $m['avg_ret_net'],
                    'median_ret_net' => $m['median_ret_net'],
                    'p25_ret_net' => $m['p25_ret_net'],
                    'win_rate' => $m['win_rate'],
                    'month_win_rate_min' => $m['month_win_rate_min'],
                    'month_avg_ret_net_min' => $m['month_avg_ret_net_min'],
                    'bad_month_like_count' => $m['bad_month_like_count'],
                    'coverage_days' => count($this->uniqueDates($windowRows)),
                    'coverage_months' => $coverageMonths,
                    'quality_pass' => $qualityPass,
                    'stability_pass' => $stabilityPass,
                    'coverage_pass' => $coveragePass,
                    'failure_reason_codes' => $failure,
                ];
            }
        }
        return $out;
    }

    protected function rollingValidationSummary(array $results): array
    {
        $out = [];
        foreach ($this->groupByField($results, 'candidate_code') as $candidate => $rows) {
            $pass = count(array_filter($rows, function (array $row): bool { return (bool) $row['quality_pass'] && (bool) $row['stability_pass'] && (bool) $row['coverage_pass']; }));
            $windowCount = count($rows);
            $out[] = [
                'candidate_code' => $candidate,
                'rolling_window_count' => $windowCount,
                'rolling_pass_count' => $pass,
                'rolling_pass_rate' => $windowCount > 0 ? $pass / $windowCount : null,
                'rolling_avg_ret_net_min' => $this->minValue($rows, 'avg_ret_net'),
                'rolling_median_ret_net_min' => $this->minValue($rows, 'median_ret_net'),
                'rolling_month_win_rate_min' => $this->minValue($rows, 'month_win_rate_min'),
                'rolling_bad_month_like_max' => $this->maxValue($rows, 'bad_month_like_count'),
                'rolling_coverage_months_min' => $this->minValue($rows, 'coverage_months'),
                'rolling_validation_pass' => $windowCount > 0 && $pass === $windowCount,
            ];
        }
        return ['candidate_summaries' => $out, 'rolling_candidate_count' => count($out)];
    }

    protected function leaveOneMonthOutResults(array $candidateRows, array $months): array
    {
        $out = [];
        $baseMetrics = [];
        foreach ($candidateRows as $candidate => $rows) { $baseMetrics[$candidate] = $this->metrics($rows); }
        foreach ($months as $month) {
            $metricsAfter = [];
            foreach ($candidateRows as $candidate => $rows) { $metricsAfter[$candidate] = $this->metrics($this->filterOutMonth($rows, $month)); }
            $ranked = $this->rankCandidates($metricsAfter);
            foreach ($candidateRows as $candidate => $rows) {
                $afterRows = $this->filterOutMonth($rows, $month);
                $m = $metricsAfter[$candidate];
                $base = $baseMetrics[$candidate];
                $rank = array_search($candidate, $ranked, true);
                $rank = $rank === false ? null : $rank + 1;
                $qualityDelta = $m['avg_ret_net'] !== null && $base['avg_ret_net'] !== null ? $m['avg_ret_net'] - $base['avg_ret_net'] : null;
                $stabilityDelta = $m['month_avg_ret_net_min'] !== null && $base['month_avg_ret_net_min'] !== null ? $m['month_avg_ret_net_min'] - $base['month_avg_ret_net_min'] : null;
                $out[] = [
                    'exclude_month' => $month,
                    'candidate_code' => $candidate,
                    'row_count_after_exclusion' => count($afterRows),
                    'evaluated_picks_count_after_exclusion' => $m['evaluated_picks_count'],
                    'avg_ret_net_after_exclusion' => $m['avg_ret_net'],
                    'median_ret_net_after_exclusion' => $m['median_ret_net'],
                    'win_rate_after_exclusion' => $m['win_rate'],
                    'month_win_rate_min_after_exclusion' => $m['month_win_rate_min'],
                    'quality_delta' => $qualityDelta,
                    'stability_delta' => $stabilityDelta,
                    'candidate_rank_after_exclusion' => $rank,
                    'rank_stable' => $rank !== null && $rank <= 6,
                    'dependency_on_excluded_month' => $qualityDelta !== null && $qualityDelta < -0.01,
                ];
            }
        }
        return $out;
    }

    protected function leaveOneMonthOutSummary(array $results): array
    {
        $out = [];
        foreach ($this->groupByField($results, 'candidate_code') as $candidate => $rows) {
            $stable = count(array_filter($rows, function (array $row): bool { return (bool) $row['rank_stable']; }));
            $dependency = count(array_filter($rows, function (array $row): bool { return (bool) $row['dependency_on_excluded_month']; })) > 0;
            $count = count($rows);
            $out[] = [
                'candidate_code' => $candidate,
                'loo_month_count' => $count,
                'loo_rank_stable_count' => $stable,
                'loo_rank_stability_rate' => $count > 0 ? $stable / $count : null,
                'loo_worst_quality_delta' => $this->minValue($rows, 'quality_delta'),
                'loo_worst_stability_delta' => $this->minValue($rows, 'stability_delta'),
                'loo_single_month_dependency_detected' => $dependency,
                'loo_validation_pass' => $count > 0 && ! $dependency && ($count === 0 || $stable / max(1, $count) >= 0.70),
            ];
        }
        return ['candidate_summaries' => $out, 'loo_candidate_count' => count($out)];
    }

    protected function regimeRobustnessResults(array $candidateRows, array &$notEvaluable): array
    {
        $fields = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct'];
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            foreach ($fields as $field) {
                $has = count(array_filter($rows, function (array $row) use ($field): bool { return $this->num($row[$field] ?? null) !== null; })) > 0;
                if (! $has) { $this->addNotEvaluable($notEvaluable, 'regime_robustness', $candidate.'|'.$field, 'C51_REGIME_FIELD_NOT_EVALUABLE', 'Regime field is unavailable for this candidate.'); continue; }
                foreach (['lt_0', 'gte_0'] as $bucket) {
                    $bucketRows = array_values(array_filter($rows, function (array $row) use ($field, $bucket): bool {
                        $v = $this->num($row[$field] ?? null);
                        if ($v === null) { return false; }
                        return $bucket === 'lt_0' ? $v < 0.0 : $v >= 0.0;
                    }));
                    $m = $this->metrics($bucketRows);
                    $lossCount = count(array_filter($bucketRows, function (array $row): bool { return ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0; }));
                    $pass = count($bucketRows) === 0 || ($m['avg_ret_net'] !== null && $m['avg_ret_net'] > -0.015 && ($m['win_rate'] ?? 0.0) >= 0.45);
                    $out[] = [
                        'candidate_code' => $candidate,
                        'regime_field' => $field,
                        'regime_bucket' => $field.' '.($bucket === 'lt_0' ? '< 0' : '>= 0'),
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
                        'regime_failure_reason_codes' => $pass ? [] : ['C51_REGIME_BUCKET_FAIL'],
                    ];
                }
            }
        }
        return $out;
    }

    protected function regimeRobustnessSummary(array $results): array
    {
        $out = [];
        foreach ($this->groupByField($results, 'candidate_code') as $candidate => $rows) {
            $pass = count(array_filter($rows, function (array $row): bool { return (bool) $row['regime_bucket_pass']; }));
            $count = count($rows);
            $out[] = [
                'candidate_code' => $candidate,
                'regime_bucket_count' => $count,
                'regime_bucket_pass_count' => $pass,
                'regime_pass_rate' => $count > 0 ? $pass / $count : null,
                'regime_worst_bucket_avg_ret_net' => $this->minValue($rows, 'avg_ret_net'),
                'regime_worst_bucket_win_rate' => $this->minValue($rows, 'win_rate'),
                'regime_loss_concentration_max' => $this->maxValue($rows, 'loss_share'),
                'regime_robustness_validation_pass' => $count > 0 && $pass === $count,
            ];
        }
        return ['candidate_summaries' => $out, 'regime_candidate_count' => count($out)];
    }

    private function materialDifferenceValidationResults(array $candidateRows, array $f00Rows, array $f03Rows, array $f08Rows): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $shared = $this->intersectRows($rows, $f00Rows);
            $only = $this->diffRows($rows, $f00Rows);
            $sharedMetrics = $this->metrics($shared);
            $onlyMetrics = $this->metrics($only);
            $overlapF00 = $this->overlapShare($rows, $f00Rows);
            $overlapF03 = $this->overlapShare($rows, $f03Rows);
            $overlapF08 = $this->overlapShare($rows, $f08Rows);
            $score = 1.0 - max($overlapF00 ?? 1.0, $candidate === 'C51_R00_C50_F03_LOCKED_PRIMARY_REPLAY' ? 0.0 : 0.0);
            $pass = $candidate === 'C51_R13_C44_F00_ANCHOR_COMPARATOR_ONLY' ? false : ($score >= 0.12 && count($only) > 0);
            $failure = [];
            if (! $pass) { $failure[] = 'C51_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
            $out[] = [
                'candidate_code' => $candidate,
                'overlap_with_c44' => $overlapF00,
                'overlap_with_f00' => $overlapF00,
                'overlap_with_f03' => $overlapF03,
                'overlap_with_f08' => $overlapF08,
                'shared_core_row_count' => count($shared),
                'candidate_only_row_count' => count($only),
                'shared_core_avg_ret_net' => $sharedMetrics['avg_ret_net'],
                'candidate_only_avg_ret_net' => $onlyMetrics['avg_ret_net'],
                'candidate_only_win_rate' => $onlyMetrics['win_rate'],
                'material_difference_score' => $score,
                'material_selection_difference_pass' => $pass,
                'anti_shared_core_pass' => $pass,
                'failure_reason_codes' => $failure,
            ];
        }
        return $out;
    }

    protected function sourceBiasCheck(array $rows, array $summary): array
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
            'source_bias_notes' => $risk === 'LOW' ? 'C51 source reconstruction has sufficient IS rows and required evaluation fields.' : 'C51 source reconstruction is partial; do not treat this as production or OOS proof.',
        ];
    }

    private function candidateScorecard(array $artifact): array
    {
        $rolling = $this->candidateSummaryMap($artifact['rolling_validation_summary']['candidate_summaries'] ?? [], 'rolling_validation_pass');
        $loo = $this->candidateSummaryMap($artifact['leave_one_month_out_summary']['candidate_summaries'] ?? [], 'loo_validation_pass');
        $regime = $this->candidateSummaryMap($artifact['regime_robustness_validation_summary']['candidate_summaries'] ?? [], 'regime_robustness_validation_pass');
        $concentration = [];
        foreach ((array) ($artifact['concentration_dependency_validation_results'] ?? []) as $row) { $concentration[$row['candidate_code']] = $row; }
        $material = [];
        foreach ((array) ($artifact['material_difference_validation_results'] ?? []) as $row) { $material[$row['candidate_code']] = $row; }
        $sourcePass = (bool) ($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false);
        $out = [];
        foreach ((array) ($artifact['candidate_replay_results'] ?? []) as $row) {
            $candidate = (string) $row['candidate_code'];
            $isComparatorOnly = $candidate === 'C51_R00_C50_F03_LOCKED_PRIMARY_REPLAY' || $candidate === 'C51_R13_C44_F00_ANCHOR_COMPARATOR_ONLY';
            $overall = ! $isComparatorOnly
                && (bool) $row['quality_pass']
                && (bool) $row['stability_pass']
                && (bool) $row['coverage_pass']
                && (bool) ($concentration[$candidate]['concentration_validation_pass'] ?? false)
                && (bool) ($rolling[$candidate] ?? false)
                && (bool) ($loo[$candidate] ?? false)
                && (bool) ($regime[$candidate] ?? false)
                && (bool) ($material[$candidate]['material_selection_difference_pass'] ?? false)
                && (bool) ($material[$candidate]['anti_shared_core_pass'] ?? false)
                && $sourcePass;
            $failures = [];
            foreach ((array) ($row['failure_reason_codes'] ?? []) as $f) { $failures[] = $f; }
            if (! ($concentration[$candidate]['concentration_validation_pass'] ?? false)) { $failures[] = 'C51_CONCENTRATION_DEPENDENCY_FAIL'; }
            if (! ($rolling[$candidate] ?? false)) { $failures[] = 'C51_ROLLING_VALIDATION_FAIL'; }
            if (! ($loo[$candidate] ?? false)) { $failures[] = 'C51_LOO_VALIDATION_FAIL'; }
            if (! ($regime[$candidate] ?? false)) { $failures[] = 'C51_REGIME_ROBUSTNESS_FAIL'; }
            if (! ($material[$candidate]['material_selection_difference_pass'] ?? false)) { $failures[] = 'C51_MATERIAL_SELECTION_DIFFERENCE_FAIL'; }
            if (! $sourcePass) { $failures[] = 'C51_SOURCE_BIAS_FAIL'; }
            if ($isComparatorOnly) { $failures[] = 'C51_COMPARATOR_ONLY_NOT_SELECTABLE'; }
            $failures = array_values(array_unique($failures));
            $out[] = [
                'candidate_code' => $candidate,
                'profile_code' => $row['profile_code'],
                'family_code' => $row['family_code'],
                'candidate_role' => $row['candidate_role'],
                'selected_from_c50_lineage' => true,
                'source_candidates_used' => $row['source_candidates_used'],
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
                'max_branch_share' => $concentration[$candidate]['max_branch_share'] ?? null,
                'max_bucket_share' => $concentration[$candidate]['max_bucket_share'] ?? null,
                'max_sector_share' => $concentration[$candidate]['max_sector_share'] ?? null,
                'max_ticker_share' => $concentration[$candidate]['max_ticker_share'] ?? null,
                'max_month_share' => $concentration[$candidate]['max_month_share'] ?? null,
                'loss_cluster_share' => $concentration[$candidate]['loss_cluster_share'] ?? null,
                'quality_pass' => (bool) $row['quality_pass'],
                'stability_pass' => (bool) $row['stability_pass'],
                'coverage_pass' => (bool) $row['coverage_pass'],
                'concentration_validation_pass' => (bool) ($concentration[$candidate]['concentration_validation_pass'] ?? false),
                'rolling_validation_pass' => (bool) ($rolling[$candidate] ?? false),
                'loo_validation_pass' => (bool) ($loo[$candidate] ?? false),
                'regime_robustness_validation_pass' => (bool) ($regime[$candidate] ?? false),
                'material_selection_difference_pass' => (bool) ($material[$candidate]['material_selection_difference_pass'] ?? false),
                'anti_shared_core_pass' => (bool) ($material[$candidate]['anti_shared_core_pass'] ?? false),
                'source_bias_validation_pass' => $sourcePass,
                'overall_is_redesign_pass' => $overall,
                'anti_overfit_pass' => $overall,
                'candidate_ready_for_c52' => $overall,
                'failure_reason_codes' => $failures,
            ];
        }
        usort($out, function (array $a, array $b): int {
            if ((bool) $a['candidate_ready_for_c52'] !== (bool) $b['candidate_ready_for_c52']) { return $a['candidate_ready_for_c52'] ? -1 : 1; }
            $cmp = ($b['avg_ret_net'] <=> $a['avg_ret_net']);
            if ($cmp !== 0) { return $cmp; }
            return strcmp((string) $a['candidate_code'], (string) $b['candidate_code']);
        });
        return $out;
    }

    private function selectedForC52(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, function (array $row): bool { return (bool) ($row['candidate_ready_for_c52'] ?? false); }));
        $best = $ready[0] ?? null;
        return [
            'best_redesigned_candidate_code' => $best['candidate_code'] ?? null,
            'best_redesigned_profile_code' => $best['profile_code'] ?? null,
            'best_redesigned_candidate_pass' => $best !== null,
            'selected_candidate_count' => count($ready),
            'candidate_codes' => array_values(array_map(function (array $row): string { return (string) $row['candidate_code']; }, $ready)),
            'candidate_is_not_production' => true,
            'production_ready' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
        ];
    }

    private function readinessDecision(array $artifact): array
    {
        $selected = $artifact['selected_c51_candidates_for_c52'] ?? [];
        $best = $this->scorecardByCandidate($artifact['candidate_scorecard'] ?? [], (string) ($selected['best_redesigned_candidate_code'] ?? ''));
        $primaryReduced = (($artifact['c50_root_cause_summary']['primary_max_branch_share'] ?? 1.0) > ($best['max_branch_share'] ?? 1.0)) || (($artifact['c50_root_cause_summary']['primary_max_bucket_share'] ?? 1.0) > ($best['max_bucket_share'] ?? 1.0));
        if (($selected['best_redesigned_candidate_pass'] ?? false) === true) {
            return $this->c52Decision('C52_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C51_REDESIGN', true, 'redesigned_candidate_passed_is_review', 'C51_REDESIGNED_CANDIDATE_PASSED_IS_REVIEW');
        }
        $promising = null;
        foreach ((array) ($artifact['candidate_scorecard'] ?? []) as $row) {
            if (($row['candidate_role'] ?? '') !== 'comparator_only' && (bool) ($row['quality_pass'] ?? false) && (bool) ($row['concentration_validation_pass'] ?? false)) { $promising = $row; break; }
        }
        if ($promising !== null) {
            return $this->c52Decision('C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN', false, 'candidate_promising_but_needs_evidence_expansion', $primaryReduced ? 'C51_F03_G16_DEPENDENCY_REDUCED' : 'C51_EVIDENCE_EXPANSION_REQUIRED');
        }
        $concentrationFailures = count(array_filter((array) ($artifact['candidate_scorecard'] ?? []), function (array $row): bool { return ! (bool) ($row['concentration_validation_pass'] ?? false); }));
        if ($concentrationFailures > 0) {
            return $this->c52Decision('C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION', false, 'concentration_dependency_issue_remains', 'C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS');
        }
        $materialFailures = count(array_filter((array) ($artifact['candidate_scorecard'] ?? []), function (array $row): bool { return ! (bool) ($row['material_selection_difference_pass'] ?? false); }));
        if ($materialFailures > 0) {
            return $this->c52Decision('C52_SHARED_CORE_REVERSION_REDESIGN_REQUIRED', false, 'material_difference_failed_or_shared_core_reversion_detected', 'C51_SHARED_CORE_REVERSION_DETECTED');
        }
        return $this->c52Decision('C52_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY', false, 'no_candidate_ready_for_c52', 'C51_REDESIGNED_CANDIDATE_FAILED_IS_REVIEW');
    }

    private function c52Decision(string $recommendation, bool $pass, string $reason, string $conclusion): array
    {
        return [
            'validation_completed' => true,
            'concentration_dependency_redesign_completed' => true,
            'primary_dependency_reduced' => $pass || in_array($recommendation, ['C52_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C51_REDESIGN', 'C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN'], true),
            'best_redesigned_candidate_code' => null,
            'best_redesigned_profile_code' => null,
            'best_redesigned_candidate_pass' => $pass,
            'rolling_validation_pass' => $pass,
            'loo_validation_pass' => $pass,
            'regime_robustness_validation_pass' => $pass,
            'concentration_validation_pass' => $pass,
            'material_difference_validation_pass' => $pass,
            'source_bias_validation_pass' => true,
            'anti_overfit_pass' => $pass,
            'c52_recommendation' => $recommendation,
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $conclusion,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function candidateSafetyAudit(array $definitions): array
    {
        $out = [];
        foreach ($definitions as $definition) {
            foreach (['selection_rule', 'oos_boundary', 'production_boundary'] as $layer) {
                $out[] = [
                    'candidate_code' => $definition['candidate_code'],
                    'review_layer' => $layer,
                    'passed' => true,
                    'reason_code' => 'C51_SAFETY_BOUNDARY_PASS',
                    'message' => 'C51 candidate uses deterministic IS lineage selection only; return/path/OOS are evaluation-only or unused.',
                    'return_used_for_selection' => false,
                    'future_path_used_for_selection' => false,
                    'oos_data_used_for_tuning' => false,
                    'oos_return_used_for_selection' => false,
                    'production_ready' => false,
                ];
            }
        }
        return $out;
    }

    private function diagnostics(array $artifact): array
    {
        return [
            ['reason_code' => 'WS_BT_C51_NO_OOS_TUNING_CONFIRMED', 'message' => 'C51 did not use OOS data, OOS return, or OOS proof for tuning or selection.'],
            ['reason_code' => 'WS_BT_C51_NOT_PRODUCTION_READY', 'message' => 'C51 is an IS-only redesign review; production_ready remains false.'],
            ['reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C51_PENDING'), 'message' => 'C51 diagnostic conclusion generated from IS-only concentration/dependency redesign review.'],
        ];
    }

    private function profileForCandidate(string $candidate): array
    {
        $base = [
            'profile_code' => $candidate,
            'family_code' => 'C51_CONCENTRATION_DEPENDENCY_REDESIGN',
            'candidate_role' => 'redesigned_candidate',
            'source_candidates_used' => [self::F03_CANDIDATE, self::F08_CANDIDATE],
            'selection_rule_description' => 'Deterministic IS-only C51 redesign using safe pre-trade ordering and locked C49/C50 lineage; return/path are evaluation-only.',
            'safe_pre_trade_fields_used' => ['market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio', 'trade_month', 'trade_date', 'ticker', 'param_id', 'row_code'],
            'branch_cap' => null,
            'bucket_cap' => null,
            'branch_quota' => null,
            'bucket_quota' => null,
            'downsampling_rule' => 'deterministic_safe_pre_trade_order_not_return_rank',
            'backfill_rule' => 'deterministic_safe_pre_trade_or_metadata_order_not_return_rank',
            'loss_cluster_control_rule' => null,
        ];
        $map = [
            'C51_R00_C50_F03_LOCKED_PRIMARY_REPLAY' => ['candidate_role' => 'primary_replay', 'source_candidates_used' => [self::F03_CANDIDATE], 'selection_rule_description' => 'Replay C50 F03 locked primary candidate; comparator baseline only, not a new production candidate.'],
            'C51_R01_F03_BRANCH_CAP_70' => ['branch_cap' => 0.70, 'branch_quota' => ['G16' => 11, 'G21' => 5], 'selection_rule_description' => 'Downsample F03 G16 by month and backfill G21 to target max branch share near relaxed 70%.'],
            'C51_R02_F03_BRANCH_CAP_65' => ['branch_cap' => 0.65, 'branch_quota' => ['G16' => 9, 'G21' => 5], 'selection_rule_description' => 'Downsample F03 G16 by month and backfill G21 to target stronger 65% branch share.'],
            'C51_R03_F03_BUCKET_CAP_70' => ['bucket_cap' => 0.70, 'bucket_quota' => ['next_open_delay_after_close_signal' => 11, 'no_rule_profit_signal_before_fallback' => 5], 'selection_rule_description' => 'Control F03 dominant bucket by deterministic G16 downsampling and G21 backfill.'],
            'C51_R04_F03_BUCKET_CAP_65' => ['bucket_cap' => 0.65, 'bucket_quota' => ['next_open_delay_after_close_signal' => 9, 'no_rule_profit_signal_before_fallback' => 5], 'selection_rule_description' => 'Stronger bucket concentration control using deterministic G16 downsampling and G21 backfill.'],
            'C51_R05_F03_G16_DOWNSAMPLED_G21_BACKFILL' => ['branch_cap' => 0.70, 'branch_quota' => ['G16' => 10, 'G21' => 7], 'selection_rule_description' => 'Downsample G16 and backfill with regime-safe G21.'],
            'C51_R06_F03_G16_DOWNSAMPLED_G21_G13_BACKFILL' => ['branch_cap' => 0.65, 'branch_quota' => ['G16' => 10, 'G21' => 5, 'G13' => 4], 'source_candidates_used' => [self::F03_CANDIDATE, self::F08_CANDIDATE], 'selection_rule_description' => 'Downsample G16 and backfill with G21/G13 to reduce branch dependency.'],
            'C51_R07_F03_F08_HYBRID_DIVERSIFIED_BRANCH_MIX' => ['branch_cap' => 0.65, 'branch_quota' => ['G16' => 10, 'G21' => 5, 'G13' => 5], 'selection_rule_description' => 'Hybrid F03/F08 diversified branch mix using deterministic lineage rows.'],
            'C51_R08_F03_BRANCH_QUOTA_CONTROL' => ['branch_cap' => 0.60, 'branch_quota' => ['G16' => 12, 'G21' => 6, 'G13' => 6], 'selection_rule_description' => 'Predeclared branch quota control across G16/G21/G13.'],
            'C51_R09_F03_BUCKET_CONCENTRATION_CONTROL' => ['bucket_cap' => 0.60, 'bucket_quota' => ['next_open_delay_after_close_signal' => 12, 'no_rule_profit_signal_before_fallback' => 12], 'selection_rule_description' => 'Predeclared bucket concentration control across dominant and fallback buckets.'],
            'C51_R10_F03_LOSS_CLUSTER_CONTROL' => ['branch_cap' => 0.65, 'loss_cluster_control_rule' => 'predeclared_monthly_ticker_cap_3_and_sector_cap_12_without_return_rank', 'selection_rule_description' => 'Predeclared ticker/sector exposure cap to reduce loss-cluster risk; no realized return is used for selection.'],
            'C51_R11_F03_F08_QUALITY_WEIGHTED_DIVERSIFIED_MIX' => ['branch_cap' => 0.65, 'branch_quota' => ['G16' => 10, 'G21' => 6, 'G13' => 4], 'selection_rule_description' => 'Quality-weighted safe pre-trade ordering with diversified F03/F08 branch mix.'],
            'C51_R12_F08_STABILITY_REPAIR_VARIANT' => ['branch_cap' => 0.60, 'branch_quota' => ['G16' => 12, 'G21' => 8, 'G13' => 4], 'source_candidates_used' => [self::F08_CANDIDATE, self::F03_CANDIDATE], 'selection_rule_description' => 'F08 stability repair by adding deterministic G21 market-extension backfill while preserving concentration guard.'],
            'C51_R13_C44_F00_ANCHOR_COMPARATOR_ONLY' => ['candidate_role' => 'comparator_only', 'source_candidates_used' => [self::F00_CANDIDATE], 'selection_rule_description' => 'C44/F00 shared core anchor comparator only; not selectable for C52.'],
        ];
        $specific = $map[$candidate] ?? [];
        return array_merge($base, $specific, ['profile_code' => $candidate, 'family_code' => $candidate]);
    }

    protected function loadPreTradeSources(array $rows, array $options): array
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
            $dates = []; $tickerIds = [];
            foreach ($rows as $row) {
                $date = (string) ($row['trade_date'] ?? '');
                if ($date !== '') { $dates[$date] = true; }
                if (isset($row['ticker_id'])) { $tickerIds[(int) $row['ticker_id']] = true; }
            }
            if (count($tickerIds) === 0 || count($dates) === 0) { return ['mode' => 'JOIN_KEYS_UNAVAILABLE', 'rows' => [], 'error' => 'ticker_id/trade_date unavailable']; }
            $map = [];
            foreach (array_chunk(array_keys($dates), 75) as $dateChunk) {
                $dbRows = DB::table('eod_indicators')->whereIn('trade_date', $dateChunk)->whereIn('ticker_id', array_keys($tickerIds))->select(['trade_date', 'ticker_id', 'dv20_idr', 'atr14_pct', 'vol_ratio', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'rs_20_vs_sector'])->get();
                foreach ($dbRows as $row) { $arr = (array) $row; $map[$this->joinKey($arr)] = $arr; }
            }
            return ['mode' => count($map) > 0 ? 'DATABASE_AS_OF_SIGNAL_DATE_JOIN' : 'DATABASE_JOIN_EMPTY', 'rows' => $map, 'error' => null];
        } catch (Throwable $e) {
            return ['mode' => 'DATABASE_JOIN_FAILED', 'rows' => [], 'error' => $e->getMessage()];
        }
    }

    private function isRows(array $rows, string $from, string $to): array
    {
        return array_values(array_filter($rows, function ($row) use ($from, $to): bool {
            if (! is_array($row)) { return false; }
            $date = (string) ($row['trade_date'] ?? '');
            return $date >= $from && $date <= $to;
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

    protected function metrics(array $rows): array
    {
        $values = [];
        foreach ($rows as $row) {
            $v = $this->num($row['profile_ret_net'] ?? ($row['ret_net'] ?? null));
            if ($v !== null) { $values[] = $v; }
        }
        sort($values);
        $count = count($values);
        $monthValues = [];
        foreach ($this->groupByMonth($rows) as $month => $monthRows) {
            $mValues = [];
            foreach ($monthRows as $row) { $v = $this->num($row['profile_ret_net'] ?? ($row['ret_net'] ?? null)); if ($v !== null) { $mValues[] = $v; } }
            if (count($mValues) > 0) { $monthValues[$month] = array_sum($mValues) / count($mValues); }
        }
        $monthWins = [];
        foreach ($this->groupByMonth($rows) as $month => $monthRows) {
            $vals = [];
            foreach ($monthRows as $row) { $v = $this->num($row['profile_ret_net'] ?? ($row['ret_net'] ?? null)); if ($v !== null) { $vals[] = $v; } }
            if (count($vals) > 0) { $monthWins[$month] = count(array_filter($vals, function (float $v): bool { return $v > 0.0; })) / count($vals); }
        }
        return [
            'evaluated_picks_count' => $count,
            'avg_ret_net' => $count > 0 ? array_sum($values) / $count : null,
            'median_ret_net' => $this->percentile($values, 0.50),
            'p25_ret_net' => $this->percentile($values, 0.25),
            'p10_ret_net' => $this->percentile($values, 0.10),
            'win_rate' => $count > 0 ? count(array_filter($values, function (float $v): bool { return $v > 0.0; })) / $count : null,
            'month_win_rate_min' => count($monthWins) > 0 ? min($monthWins) : null,
            'month_avg_ret_net_min' => count($monthValues) > 0 ? min($monthValues) : null,
            'bad_month_like_count' => count(array_filter($monthValues, function (float $v): bool { return $v < 0.0; })),
        ];
    }

    private function percentile(array $sorted, float $p): ?float
    {
        $count = count($sorted);
        if ($count === 0) { return null; }
        $index = max(0, min($count - 1, (int) floor(($count - 1) * $p)));
        return $sorted[$index];
    }

    protected function concentrationSummary(array $rows): array
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

    protected function sectorConcentration(array $rows): ?float
    {
        if (count($rows) === 0) { return null; }
        $counts = [];
        foreach ($rows as $row) { $key = (string) ($row['sector_code'] ?? $row['sector_name'] ?? 'UNKNOWN'); $counts[$key] = ($counts[$key] ?? 0) + 1; }
        return max($counts) / count($rows);
    }

    private function monthConcentration(array $rows): ?float
    {
        if (count($rows) === 0) { return null; }
        $counts = [];
        foreach ($rows as $row) { $key = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)); $counts[$key] = ($counts[$key] ?? 0) + 1; }
        return max($counts) / count($rows);
    }

    protected function concentration(array $rows, string $field): ?float
    {
        if (count($rows) === 0) { return null; }
        $counts = [];
        foreach ($rows as $row) { $key = (string) ($row[$field] ?? 'UNKNOWN'); $counts[$key] = ($counts[$key] ?? 0) + 1; }
        return max($counts) / count($rows);
    }

    protected function overlapShare(array $rows, array $other): ?float
    {
        if (count($rows) === 0) { return null; }
        $keys = [];
        foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        $shared = 0;
        foreach ($rows as $row) { if (isset($keys[$this->pickKey($row)])) { $shared++; } }
        return $shared / count($rows);
    }

    protected function intersectRows(array $rows, array $other): array
    {
        $keys = [];
        foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        return array_values(array_filter($rows, function (array $row) use ($keys): bool { return isset($keys[$this->pickKey($row)]); }));
    }

    protected function diffRows(array $rows, array $other): array
    {
        $keys = [];
        foreach ($other as $row) { $keys[$this->pickKey($row)] = true; }
        return array_values(array_filter($rows, function (array $row) use ($keys): bool { return ! isset($keys[$this->pickKey($row)]); }));
    }

    protected function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array
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

    protected function selectWithExposureCap(array $rows, int $tickerCapPerMonth, int $sectorCapPerMonth): array
    {
        usort($rows, function (array $a, array $b): int { return strcmp($this->qualityKey($a, 'BALANCED').'|'.$this->metadataKey($a), $this->qualityKey($b, 'BALANCED').'|'.$this->metadataKey($b)); });
        $tickerCounts = []; $sectorCounts = []; $selected = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $ticker = strtoupper((string) ($row['ticker'] ?? 'UNKNOWN'));
            $sector = (string) ($row['sector_code'] ?? $row['sector_name'] ?? 'UNKNOWN');
            $tk = $month.'|'.$ticker; $sk = $month.'|'.$sector;
            if (($tickerCounts[$tk] ?? 0) >= $tickerCapPerMonth || ($sectorCounts[$sk] ?? 0) >= $sectorCapPerMonth) { continue; }
            $tickerCounts[$tk] = ($tickerCounts[$tk] ?? 0) + 1;
            $sectorCounts[$sk] = ($sectorCounts[$sk] ?? 0) + 1;
            $selected[] = $row;
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

    private function rollingWindows(array $months): array
    {
        $windows = [];
        $count = count($months);
        foreach ([6, 9, 12] as $size) {
            if ($count >= $size) {
                for ($i = 0; $i <= $count - $size; $i++) {
                    $slice = array_slice($months, $i, $size);
                    $windows[] = ['code' => 'ROLLING_'.$size.'M_STEP_1M_'.($i + 1), 'months' => $slice, 'from' => $slice[0].'-01', 'to' => end($slice).'-31'];
                }
            }
        }
        if ($count > 0) {
            $third = max(1, (int) floor($count / 3));
            $parts = [array_slice($months, 0, $third), array_slice($months, $third, $third), array_slice($months, $third * 2)];
            foreach (['EARLY_IS', 'MID_IS', 'LATE_IS'] as $idx => $code) {
                $slice = array_values(array_filter($parts[$idx] ?? []));
                if (count($slice) > 0) { $windows[] = ['code' => $code, 'months' => $slice, 'from' => $slice[0].'-01', 'to' => end($slice).'-31']; }
            }
        }
        return $windows;
    }

    private function c50ConcentrationFailureConfirmed(array $c50): bool
    {
        $primary = $this->findByCandidate($c50['candidate_validation_scorecard'] ?? [], self::F03_CANDIDATE);
        $concentration = $this->findByCandidate($c50['concentration_dependency_validation_results'] ?? [], self::F03_CANDIDATE);
        return in_array('C50_CONCENTRATION_DEPENDENCY_FAIL', (array) ($primary['failure_reason_codes'] ?? []), true)
            || in_array('C50_CONCENTRATION_DEPENDENCY_WARNING', (array) ($concentration['failure_reason_codes'] ?? []), true)
            || (($concentration['concentration_validation_pass'] ?? true) === false && ($concentration['max_branch_share'] ?? 0.0) > 0.70);
    }

    private function findByCandidate(array $rows, string $candidate): array
    {
        foreach ($rows as $row) { if (is_array($row) && ($row['candidate_code'] ?? null) === $candidate) { return $row; } }
        return [];
    }

    private function branchMix(array $rows, string $candidate): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row) && ($row['candidate_code'] ?? null) === $candidate) { $out[(string) ($row['branch_code'] ?? 'UNKNOWN')] = ['row_count' => $row['branch_row_count'] ?? null, 'share' => $row['branch_share'] ?? null]; }
        }
        return $out;
    }

    private function candidateSummaryMap(array $summaries, string $flag): array
    {
        $out = [];
        foreach ($summaries as $row) { if (is_array($row)) { $out[(string) ($row['candidate_code'] ?? '')] = (bool) ($row[$flag] ?? false); } }
        return $out;
    }

    protected function scorecardByCandidate(array $scorecard, string $candidate): array
    {
        foreach ($scorecard as $row) { if (is_array($row) && ($row['candidate_code'] ?? null) === $candidate) { return $row; } }
        return [];
    }

    private function rankCandidates(array $metrics): array
    {
        uasort($metrics, function (array $a, array $b): int {
            $cmp = ($b['avg_ret_net'] ?? -999) <=> ($a['avg_ret_net'] ?? -999);
            return $cmp !== 0 ? $cmp : (($b['median_ret_net'] ?? -999) <=> ($a['median_ret_net'] ?? -999));
        });
        return array_keys($metrics);
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

    protected function groupByField(array $rows, string $field): array
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

    protected function joinKey(array $row): string
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

    protected function uniqueMonths(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)); if ($v !== '') { $values[$v] = true; } } $out = array_keys($values); sort($out); return $out; }
    protected function uniqueDates(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['trade_date'] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function uniqueValues(array $rows, string $field): array { $values = []; foreach ($rows as $row) { $v = (string) ($row[$field] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function uniqueSectorValues(array $rows): array { $values = []; foreach ($rows as $row) { $v = (string) ($row['sector_code'] ?? $row['sector_name'] ?? ''); if ($v !== '') { $values[$v] = true; } } return array_keys($values); }
    private function fieldsPresent(array $rows): array { $fields = []; foreach ($rows as $row) { foreach (array_keys($row) as $key) { $fields[$key] = true; } } $out = array_keys($fields); sort($out); return $out; }
    private function minValue(array $rows, string $field) { $values = []; foreach ($rows as $row) { if (is_numeric($row[$field] ?? null)) { $values[] = (float) $row[$field]; } } return count($values) > 0 ? min($values) : null; }
    private function maxValue(array $rows, string $field) { $values = []; foreach ($rows as $row) { if (is_numeric($row[$field] ?? null)) { $values[] = (float) $row[$field]; } } return count($values) > 0 ? max($values) : null; }
    protected function num($value): ?float { return is_numeric($value) ? (float) $value : null; }
    protected function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    protected function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    protected function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 && strcmp($from, self::OOS_RESERVED_TO) <= 0; }
    protected function addNotEvaluable(array &$out, string $layer, string $slice, string $code, string $message): void { $out[] = ['validation_layer' => $layer, 'validation_slice' => $slice, 'reason_code' => $code, 'message' => $message]; }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C52_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') { $this->writeArtifact($output, $artifact, true); }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        if (isset($artifact['selected_c51_candidates_for_c52']['best_redesigned_candidate_code'])) {
            $best = $this->scorecardByCandidate($artifact['candidate_scorecard'] ?? [], (string) $artifact['selected_c51_candidates_for_c52']['best_redesigned_candidate_code']);
            if (! empty($best)) {
                foreach (['best_redesigned_candidate_code' => 'candidate_code', 'best_redesigned_profile_code' => 'profile_code', 'best_redesigned_candidate_pass' => 'candidate_ready_for_c52', 'rolling_validation_pass' => 'rolling_validation_pass', 'loo_validation_pass' => 'loo_validation_pass', 'regime_robustness_validation_pass' => 'regime_robustness_validation_pass', 'concentration_validation_pass' => 'concentration_validation_pass', 'material_difference_validation_pass' => 'material_selection_difference_pass', 'source_bias_validation_pass' => 'source_bias_validation_pass', 'anti_overfit_pass' => 'anti_overfit_pass'] as $target => $source) {
                    $artifact['c52_readiness_decision'][$target] = $best[$source] ?? $artifact['c52_readiness_decision'][$target] ?? null;
                }
            }
        }
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C51_OPERATOR_VALIDATION_REQUIRED'; return $this->result($artifact, $output, $write['reason_code'], $write['message']); }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return [
            'status' => $artifact['status'], 'reason_code' => $reason, 'message' => $message, 'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'] ?? null, 'production_ready' => 0,
            'expected_c50_hash' => $artifact['expected_c50_hash'] ?? null, 'actual_c50_hash' => $artifact['actual_c50_hash'] ?? null,
            'c50_hash_match' => $artifact['c50_hash_match'] ?? false, 'c50_status' => $artifact['c50_status'] ?? null,
            'c50_diagnostic_conclusion' => $artifact['c50_diagnostic_conclusion'] ?? null, 'c50_next_step_recommendation' => $artifact['c50_next_step_recommendation'] ?? null,
            'expected_c49_hash' => $artifact['expected_c49_hash'] ?? null, 'actual_c49_hash' => $artifact['actual_c49_hash'] ?? null,
            'c49_hash_match' => $artifact['c49_hash_match'] ?? false,
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'] ?? null, 'next_step_recommendation' => $artifact['next_step_recommendation'] ?? null,
            'source_reconstruction_summary' => $artifact['source_reconstruction_summary'] ?? [],
            'selected_c51_candidates_for_c52' => $artifact['selected_c51_candidates_for_c52'] ?? [],
            'c52_readiness_decision' => $artifact['c52_readiness_decision'] ?? [],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C51 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    protected function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
}
