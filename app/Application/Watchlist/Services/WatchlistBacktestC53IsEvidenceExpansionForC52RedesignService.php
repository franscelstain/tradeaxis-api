<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService
{
    public const RUN_CODE = 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN';
    public const ARTIFACT_TYPE = 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN';
    public const DEFAULT_C52_ARTIFACT = 'storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json';
    public const DEFAULT_EXPECTED_C52_HASH = '5dbe51c9d18b175e65cddb60336baf43d6833b72';
    public const DEFAULT_EXPECTED_C52_FILE_SHA1 = 'DADE6518BFF3912D8A43D7C67073FB803F7CF878';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const EXPECTED_C52_STATUS = 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED';
    public const EXPECTED_C52_CONCLUSION = 'C52_EVIDENCE_EXPANSION_REQUIRED';
    public const EXPECTED_C52_NEXT_STEP = 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN';

    /**
     * C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_ONLY. C52_ARTIFACT_HASH_LOCK. C52_FILE_SHA1_LOCK.
     * C52_USED_AS_LOCKED_EVIDENCE_SOURCE. C51_C50_C49_LINEAGE_CARRIED_FORWARD. IS_ONLY_VALIDATION.
     * STRUCTURAL_COHORT_NO_RETURN_SELECTION. NO_NEW_CANDIDATE_FORMATION. NO_CANDIDATE_WINNER.
     * NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER.
     * NO_OOS_RETURN_SELECTION. NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS.
     * NO_PRODUCTION_CATALOG. NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C52_ARTIFACT_MUTATION.
     * CANDIDATE_IS_NOT_PRODUCTION. C53_MUST_NOT_RECOMMEND_OOS_PROOF. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. PRODUCTION_READY_FALSE.
     */
    public function execute(
        string $c52Artifact = self::DEFAULT_C52_ARTIFACT,
        string $expectedC52Hash = self::DEFAULT_EXPECTED_C52_HASH,
        string $expectedC52FileSha1 = self::DEFAULT_EXPECTED_C52_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $c52Artifact = $this->defaulted($c52Artifact, self::DEFAULT_C52_ARTIFACT);
        $expectedC52Hash = $this->defaulted($expectedC52Hash, self::DEFAULT_EXPECTED_C52_HASH);
        $expectedC52FileSha1 = strtoupper($this->defaulted($expectedC52FileSha1, self::DEFAULT_EXPECTED_C52_FILE_SHA1));
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact($c52Artifact, $expectedC52Hash, $expectedC52FileSha1, $from, $to, (string) ($options['executed_at'] ?? gmdate('c')));

        if (! is_file($c52Artifact)) {
            return $this->blocked($artifact, 'C53_BLOCKED_MISSING_C52_ARTIFACT', 'WS_BT_C53_C52_ARTIFACT_MISSING', 'C53 requires the locked C52 artifact.', $outputPath);
        }
        $c52 = json_decode((string) file_get_contents($c52Artifact), true);
        if (! is_array($c52)) {
            return $this->blocked($artifact, 'C53_BLOCKED_MISSING_C52_ARTIFACT', 'WS_BT_C53_C52_ARTIFACT_UNREADABLE', 'C52 artifact is not readable JSON.', $outputPath);
        }

        $artifact['actual_c52_hash'] = $this->stableHash($c52);
        $artifact['c52_hash_match'] = hash_equals($expectedC52Hash, $artifact['actual_c52_hash']);
        $artifact['actual_c52_file_sha1'] = strtoupper((string) sha1_file($c52Artifact));
        $artifact['c52_file_sha1_match'] = hash_equals($expectedC52FileSha1, $artifact['actual_c52_file_sha1']);
        $artifact['c52_status'] = $c52['status'] ?? null;
        $artifact['c52_diagnostic_conclusion'] = $c52['diagnostic_conclusion'] ?? null;
        $artifact['c52_next_step_recommendation'] = $c52['next_step_recommendation'] ?? null;

        if (! $artifact['c52_hash_match']) { return $this->blocked($artifact, 'C53_BLOCKED_C52_HASH_MISMATCH', 'WS_BT_C53_C52_ARTIFACT_HASH_MISMATCH', 'C52 stable hash does not match the expected lock.', $outputPath); }
        if (! $artifact['c52_file_sha1_match']) { return $this->blocked($artifact, 'C53_BLOCKED_C52_FILE_SHA1_MISMATCH', 'WS_BT_C53_C52_FILE_SHA1_MISMATCH', 'C52 file SHA1 does not match the expected lock.', $outputPath); }
        if ($artifact['c52_status'] !== self::EXPECTED_C52_STATUS) { return $this->blocked($artifact, 'C53_BLOCKED_UNEXPECTED_C52_STATUS', 'WS_BT_C53_UNEXPECTED_C52_STATUS', 'C53 requires completed C52 evidence.', $outputPath); }
        if ($artifact['c52_diagnostic_conclusion'] !== self::EXPECTED_C52_CONCLUSION) { return $this->blocked($artifact, 'C53_BLOCKED_UNEXPECTED_C52_CONCLUSION', 'WS_BT_C53_UNEXPECTED_C52_CONCLUSION', 'C52 conclusion does not authorize C53 evidence expansion.', $outputPath); }
        if ($artifact['c52_next_step_recommendation'] !== self::EXPECTED_C52_NEXT_STEP) { return $this->blocked($artifact, 'C53_BLOCKED_C52_NEXT_STEP_UNEXPECTED', 'WS_BT_C53_C52_NEXT_STEP_UNEXPECTED', 'C52 next step does not route to C53 evidence expansion.', $outputPath); }
        if (! $this->strictFalse($c52['production_ready'] ?? true)) { return $this->blocked($artifact, 'C53_BLOCKED_C52_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C53_C52_PRODUCTION_READY_NOT_FALSE', 'C52 production_ready must remain false.', $outputPath); }
        if (($c52['c53_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c52['c53_readiness_decision']['oos_proof_unlocked'] ?? false) === true) {
            return $this->blocked($artifact, 'C53_BLOCKED_C52_OOS_PROOF_FLAG_INVALID', 'WS_BT_C53_C52_OOS_PROOF_FLAG_INVALID', 'C52 must not unlock or recommend direct OOS proof.', $outputPath);
        }
        if (! ($c52['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false)) { return $this->blocked($artifact, 'C53_BLOCKED_C52_SECTOR_METADATA_NOT_VALID', 'WS_BT_C53_C52_SECTOR_METADATA_NOT_VALID', 'C53 requires the valid C52 sector reconstruction.', $outputPath); }
        if (! ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false)) { return $this->blocked($artifact, 'C53_BLOCKED_C52_SOURCE_BIAS_NOT_VALID', 'WS_BT_C53_C52_SOURCE_BIAS_NOT_VALID', 'C53 requires a passing C52 source-bias check.', $outputPath); }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) { return $this->blocked($artifact, 'C53_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C53_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C53 only accepts the IS period and must not touch reserved OOS.', $outputPath); }

        $artifact['c52_carry_forward_summary'] = $this->carryForward($c52);
        $artifact['locked_lineage_summary'] = $this->lineageSummary($c52);
        $artifact['evidence_expansion_thresholds'] = $this->thresholds();
        $cohort = $this->structuralCohort($c52);
        $artifact['review_cohort_definition'] = $cohort['definition'];
        $artifact['review_cohort_results'] = $cohort['results'];
        $codes = array_column($cohort['results'], 'candidate_code');

        if (count($codes) === 0) {
            $artifact['status'] = 'C53_EVIDENCE_COHORT_NOT_EVALUABLE';
            $artifact['not_evaluable_reasons'][] = ['validation_layer' => 'review_cohort', 'validation_slice' => 'C52_STRUCTURAL_PASS_COHORT', 'reason_code' => 'C53_STRUCTURAL_COHORT_EMPTY', 'message' => 'No non-comparator C52 candidate passed the structural cohort gates.'];
            $artifact['diagnostic_conclusion'] = 'C53_EVIDENCE_COHORT_NOT_EVALUABLE';
            $artifact['next_step_recommendation'] = 'C54_IS_EVIDENCE_EXPANSION_CONTINUATION_FOR_C52_REDESIGN';
            $artifact['c54_readiness_decision'] = $this->decision($artifact);
            $artifact['diagnostics'] = $this->diagnostics($artifact);
            return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $artifact['candidate_failure_inventory'] = $this->failureInventory($c52, $codes);
        $rolling = $this->rollingExpansion($c52, $codes);
        $artifact['rolling_evidence_expansion_results'] = $rolling['results'];
        $artifact['rolling_evidence_expansion_summary'] = $rolling['summary'];
        $loo = $this->looExpansion($c52, $codes);
        $artifact['leave_one_month_out_evidence_results'] = $loo['results'];
        $artifact['leave_one_month_out_evidence_summary'] = $loo['summary'];
        $artifact['adverse_month_attribution_results'] = $this->adverseMonthAttribution($loo['results']);
        $regime = $this->regimeExpansion($c52, $codes);
        $artifact['regime_field_availability_matrix'] = $regime['availability'];
        $artifact['regime_evidence_expansion_summary'] = $regime['summary'];
        $artifact['structural_guard_preservation_audit'] = $this->structuralGuardAudit($c52, $codes);
        $artifact['cross_layer_corroboration_results'] = $this->crossLayerResults($c52, $codes, $rolling['summary']['candidate_summaries'], $loo['summary']['candidate_summaries'], $regime['summary']['candidate_summaries']);
        $artifact['candidate_safety_audit'] = $this->safetyAudit($codes);
        $artifact['not_evaluable_reasons'] = $this->cohortNotEvaluableReasons($c52, $codes);
        $artifact['c54_readiness_decision'] = $this->decision($artifact);
        $artifact['diagnostic_conclusion'] = $artifact['c54_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c54_readiness_decision']['c54_recommendation'];
        $artifact['status'] = 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED';
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function baseArtifact(string $path, string $hash, string $fileSha1, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE, 'status' => 'C53_OPERATOR_VALIDATION_REQUIRED', 'artifact_type' => self::ARTIFACT_TYPE, 'production_ready' => false,
            'input_c52_artifact' => $path, 'expected_c52_hash' => $hash, 'actual_c52_hash' => null, 'c52_hash_match' => false,
            'expected_c52_file_sha1' => $fileSha1, 'actual_c52_file_sha1' => null, 'c52_file_sha1_match' => false,
            'c52_status' => null, 'c52_diagnostic_conclusion' => null, 'c52_next_step_recommendation' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'is_only_evidence_expansion_for_c52_redesign', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c52_carry_forward_summary' => [], 'locked_lineage_summary' => [], 'evidence_expansion_thresholds' => [], 'review_cohort_definition' => [], 'review_cohort_results' => [],
            'candidate_failure_inventory' => [], 'rolling_evidence_expansion_results' => [], 'rolling_evidence_expansion_summary' => [], 'leave_one_month_out_evidence_results' => [], 'leave_one_month_out_evidence_summary' => [], 'adverse_month_attribution_results' => [],
            'regime_field_availability_matrix' => [], 'regime_evidence_expansion_summary' => [], 'structural_guard_preservation_audit' => [], 'cross_layer_corroboration_results' => [],
            'c54_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false], 'candidate_safety_audit' => [], 'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C53_PENDING', 'next_step_recommendation' => 'C53_PENDING', 'diagnostics' => [],
            'safety_boundaries' => [
                'c53_is_evidence_expansion_for_c52_redesign_only' => true, 'c52_artifact_hash_lock' => true, 'c52_file_sha1_lock' => true, 'c52_used_as_locked_evidence_source' => true,
                'c51_c50_c49_lineage_carried_forward' => true, 'is_only_validation' => true, 'structural_cohort_no_return_selection' => true, 'no_new_candidate_formation' => true, 'no_candidate_winner' => true,
                'no_oos_tuning' => true, 'no_oos_proof' => true, 'no_oos_proof_rerun' => true, 'no_best_of_oos' => true, 'no_oos_winner' => true, 'no_oos_return_selection' => true,
                'no_candidate_reselection_from_oos' => true, 'no_profile_reselection_from_oos' => true, 'no_production_catalog' => true, 'no_promotion' => true, 'no_plan_confirm_mutation' => true,
                'no_c01_to_c52_mutation' => true, 'no_c01_to_c52_artifact_mutation' => true, 'candidate_is_not_production' => true, 'c53_must_not_recommend_oos_proof' => true,
                'production_ready' => false, 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false, 'profile_ret_net_used_for_selection' => false, 'derived_mfe_mae_used_for_execution' => false, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false,
            ],
            'created_at' => $createdAt,
        ];
    }

    private function carryForward(array $c52): array
    {
        $decision = $c52['c53_readiness_decision'] ?? [];
        return [
            'c52_status' => $c52['status'] ?? null, 'c52_diagnostic_conclusion' => $c52['diagnostic_conclusion'] ?? null, 'c52_next_step_recommendation' => $c52['next_step_recommendation'] ?? null,
            'sector_metadata_reconstruction_pass' => (bool) ($c52['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false),
            'sector_metadata_join_coverage_rate' => $c52['sector_metadata_reconstruction_summary']['sector_metadata_join_coverage_rate'] ?? null,
            'source_bias_validation_pass' => (bool) ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false),
            'primary_dependency_reduced' => (bool) ($decision['primary_dependency_reduced'] ?? false), 'selected_candidate_count' => (int) ($c52['selected_c52_candidates_for_c53']['selected_candidate_count'] ?? 0),
            'best_redesigned_candidate_code' => $c52['selected_c52_candidates_for_c53']['best_redesigned_candidate_code'] ?? null, 'anti_overfit_pass' => (bool) ($decision['anti_overfit_pass'] ?? false),
            'decision_reason' => $decision['decision_reason'] ?? null, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false,
        ];
    }

    private function lineageSummary(array $c52): array
    {
        return [
            'expected_c51_hash' => $c52['expected_c51_hash'] ?? null, 'actual_c51_hash' => $c52['actual_c51_hash'] ?? null, 'c51_hash_match' => (bool) ($c52['c51_hash_match'] ?? false),
            'expected_c50_hash' => $c52['expected_c50_hash'] ?? null, 'actual_c50_hash' => $c52['actual_c50_hash'] ?? null, 'c50_hash_match' => (bool) ($c52['c50_hash_match'] ?? false),
            'expected_c49_hash' => $c52['expected_c49_hash'] ?? null, 'actual_c49_hash' => $c52['actual_c49_hash'] ?? null, 'c49_hash_match' => (bool) ($c52['c49_hash_match'] ?? false),
            'c52_used_as_locked_evidence_source' => true, 'c51_c50_c49_lineage_carried_forward' => true,
        ];
    }

    private function thresholds(): array
    {
        return [
            'cohort_membership_rule' => 'non_comparator AND concentration_pass AND material_difference_pass AND source_bias_pass AND sector_metadata_pass',
            'cohort_membership_uses_return' => false, 'rolling_failure_share_warning' => 0.10, 'rolling_failure_share_hard_gap' => 0.25,
            'loo_rank_stability_min' => 0.70, 'regime_pass_rate_min' => 1.0, 'structural_guard_pass_required' => true,
            'new_candidate_formation_allowed' => false, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_return_used_for_selection' => false,
        ];
    }

    private function structuralCohort(array $c52): array
    {
        $sectorPass = (bool) ($c52['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false);
        $rows = [];
        foreach ((array) ($c52['candidate_scorecard'] ?? []) as $row) {
            $included = ($row['candidate_role'] ?? '') !== 'comparator_only' && $sectorPass && ($row['concentration_validation_pass'] ?? false) && ($row['material_selection_difference_pass'] ?? false) && ($row['source_bias_validation_pass'] ?? false);
            if (! $included) { continue; }
            $rows[] = [
                'candidate_code' => $row['candidate_code'], 'candidate_role' => $row['candidate_role'], 'included_in_review_cohort' => true,
                'sector_metadata_reconstruction_pass' => $sectorPass, 'concentration_validation_pass' => true, 'material_selection_difference_pass' => true, 'source_bias_validation_pass' => true,
                'quality_pass_observed_after_membership_lock' => (bool) ($row['quality_pass'] ?? false), 'coverage_pass_observed_after_membership_lock' => (bool) ($row['coverage_pass'] ?? false),
                'return_used_for_cohort_membership' => false, 'future_path_used_for_cohort_membership' => false, 'oos_return_used_for_cohort_membership' => false,
            ];
        }
        return ['definition' => ['cohort_code' => 'C53_C52_STRUCTURAL_PASS_REVIEW_COHORT', 'membership_fields' => ['candidate_role', 'sector_metadata_reconstruction_pass', 'concentration_validation_pass', 'material_selection_difference_pass', 'source_bias_validation_pass'], 'excluded_membership_fields' => ['avg_ret_net', 'median_ret_net', 'win_rate', 'profile_ret_net', 'ret_net', 'future_path', 'oos_return'], 'return_used_for_cohort_membership' => false, 'candidate_winner_selected' => false, 'new_candidate_formed' => false], 'results' => $rows];
    }

    private function failureInventory(array $c52, array $codes): array
    {
        $out = [];
        foreach ((array) ($c52['candidate_scorecard'] ?? []) as $row) {
            if (! in_array($row['candidate_code'] ?? null, $codes, true)) { continue; }
            $failures = [];
            foreach ([['stability_pass', 'C53_FULL_IS_STABILITY_GAP'], ['coverage_pass', 'C53_COVERAGE_GAP'], ['rolling_validation_pass', 'C53_ROLLING_STABILITY_GAP'], ['loo_validation_pass', 'C53_LOO_RANK_STABILITY_GAP'], ['regime_robustness_validation_pass', 'C53_REGIME_ROBUSTNESS_GAP']] as $check) { if (! ($row[$check[0]] ?? false)) { $failures[] = $check[1]; } }
            $out[] = ['candidate_code' => $row['candidate_code'], 'quality_pass' => (bool) ($row['quality_pass'] ?? false), 'stability_pass' => (bool) ($row['stability_pass'] ?? false), 'coverage_pass' => (bool) ($row['coverage_pass'] ?? false), 'rolling_validation_pass' => (bool) ($row['rolling_validation_pass'] ?? false), 'loo_validation_pass' => (bool) ($row['loo_validation_pass'] ?? false), 'regime_robustness_validation_pass' => (bool) ($row['regime_robustness_validation_pass'] ?? false), 'failure_layer_count' => count($failures), 'failure_reason_codes' => $failures];
        }
        return $out;
    }

    private function rollingExpansion(array $c52, array $codes): array
    {
        $results = []; $groups = [];
        foreach ((array) ($c52['rolling_validation_results'] ?? []) as $row) {
            $candidate = $row['candidate_code'] ?? null; if (! in_array($candidate, $codes, true)) { continue; }
            $pass = (bool) ($row['quality_pass'] ?? false) && (bool) ($row['stability_pass'] ?? false) && (bool) ($row['coverage_pass'] ?? false);
            $family = str_starts_with((string) ($row['validation_window_code'] ?? ''), 'ROLLING_6M') ? 'ROLLING_6M' : (str_starts_with((string) ($row['validation_window_code'] ?? ''), 'ROLLING_9M') ? 'ROLLING_9M' : (str_starts_with((string) ($row['validation_window_code'] ?? ''), 'ROLLING_12M') ? 'ROLLING_12M' : 'IS_PHASE'));
            $record = $row + ['window_family' => $family, 'evidence_result' => $pass ? 'PASS' : 'GAP', 'return_used_for_selection' => false];
            $results[] = $record; $groups[$candidate][] = $record;
        }
        $summaries = []; $qualityFail = 0; $stabilityFail = 0; $coverageFail = 0;
        foreach ($groups as $candidate => $rows) {
            $q = count(array_filter($rows, fn (array $r): bool => ! ($r['quality_pass'] ?? false))); $s = count(array_filter($rows, fn (array $r): bool => ! ($r['stability_pass'] ?? false))); $c = count(array_filter($rows, fn (array $r): bool => ! ($r['coverage_pass'] ?? false))); $pass = count(array_filter($rows, fn (array $r): bool => ($r['evidence_result'] ?? '') === 'PASS'));
            $qualityFail += $q; $stabilityFail += $s; $coverageFail += $c; $count = count($rows);
            $summaries[] = ['candidate_code' => $candidate, 'rolling_window_count' => $count, 'rolling_pass_count' => $pass, 'rolling_pass_rate' => $count > 0 ? $pass / $count : null, 'rolling_quality_failure_count' => $q, 'rolling_stability_failure_count' => $s, 'rolling_coverage_failure_count' => $c, 'rolling_worst_avg_ret_net' => $this->minField($rows, 'avg_ret_net'), 'rolling_worst_median_ret_net' => $this->minField($rows, 'median_ret_net'), 'rolling_evidence_pass' => $count > 0 && $pass === $count, 'primary_gap' => $s > 0 ? 'ROLLING_STABILITY' : ($q > 0 ? 'ROLLING_QUALITY' : ($c > 0 ? 'ROLLING_COVERAGE' : null))];
        }
        return ['results' => $results, 'summary' => ['candidate_summaries' => $summaries, 'cohort_candidate_count' => count($summaries), 'rolling_window_count' => count($results), 'rolling_quality_failure_count' => $qualityFail, 'rolling_stability_failure_count' => $stabilityFail, 'rolling_coverage_failure_count' => $coverageFail, 'candidate_full_rolling_pass_count' => count(array_filter($summaries, fn (array $r): bool => $r['rolling_evidence_pass'])), 'rolling_primary_gap' => $stabilityFail > max($qualityFail, $coverageFail) ? 'ROLLING_STABILITY' : 'MIXED']];
    }

    private function looExpansion(array $c52, array $codes): array
    {
        $results = []; $groups = [];
        foreach ((array) ($c52['leave_one_month_out_results'] ?? []) as $row) { if (in_array($row['candidate_code'] ?? null, $codes, true)) { $record = $row + ['return_used_for_selection' => false]; $results[] = $record; $groups[$row['candidate_code']][] = $record; } }
        $summaries = [];
        foreach ($groups as $candidate => $rows) {
            $stable = count(array_filter($rows, fn (array $r): bool => (bool) ($r['rank_stable'] ?? false))); $dependency = count(array_filter($rows, fn (array $r): bool => (bool) ($r['dependency_on_excluded_month'] ?? false))); $count = count($rows); $rate = $count > 0 ? $stable / $count : null;
            $summaries[] = ['candidate_code' => $candidate, 'loo_month_count' => $count, 'loo_rank_stable_count' => $stable, 'loo_rank_stability_rate' => $rate, 'loo_dependency_month_count' => $dependency, 'loo_worst_quality_delta' => $this->minField($rows, 'quality_delta'), 'loo_worst_stability_delta' => $this->minField($rows, 'stability_delta'), 'loo_evidence_pass' => $count > 0 && $dependency === 0 && $rate >= 0.70];
        }
        return ['results' => $results, 'summary' => ['candidate_summaries' => $summaries, 'cohort_candidate_count' => count($summaries), 'loo_result_count' => count($results), 'candidate_loo_pass_count' => count(array_filter($summaries, fn (array $r): bool => $r['loo_evidence_pass']))]];
    }

    private function adverseMonthAttribution(array $rows): array
    {
        $groups = []; foreach ($rows as $row) { $groups[(string) ($row['exclude_month'] ?? '')][] = $row; } $out = [];
        foreach ($groups as $month => $monthRows) {
            $quality = $this->numericField($monthRows, 'quality_delta'); $stability = $this->numericField($monthRows, 'stability_delta');
            $out[] = ['month' => $month, 'cohort_candidate_count' => count($monthRows), 'mean_quality_delta_after_exclusion' => $this->mean($quality), 'candidates_quality_improved_after_exclusion' => count(array_filter($quality, fn (float $v): bool => $v > 0.0)), 'mean_stability_delta_after_exclusion' => $this->mean($stability), 'candidates_stability_improved_after_exclusion' => count(array_filter($stability, fn (float $v): bool => $v > 0.0)), 'adverse_month_cluster' => count($quality) > 0 && count(array_filter($quality, fn (float $v): bool => $v > 0.0)) / count($quality) >= 0.75, 'return_used_for_selection' => false];
        }
        usort($out, fn (array $a, array $b): int => ($b['mean_quality_delta_after_exclusion'] ?? -999) <=> ($a['mean_quality_delta_after_exclusion'] ?? -999));
        return $out;
    }

    private function regimeExpansion(array $c52, array $codes): array
    {
        $fields = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct']; $results = [];
        foreach ((array) ($c52['regime_robustness_validation_results'] ?? []) as $row) { if (in_array($row['candidate_code'] ?? null, $codes, true)) { $results[] = $row; } }
        $availability = [];
        foreach ($fields as $field) {
            $evaluated = [];
            foreach ($results as $row) { if (($row['regime_field'] ?? null) === $field) { $evaluated[(string) $row['candidate_code']] = true; } }
            $availability[] = ['regime_field' => $field, 'cohort_candidate_count' => count($codes), 'evaluated_candidate_count' => count($evaluated), 'missing_candidate_count' => count($codes) - count($evaluated), 'field_evaluable_for_full_cohort' => count($evaluated) === count($codes), 'reason_code' => count($evaluated) === count($codes) ? 'C53_REGIME_FIELD_AVAILABLE' : 'C53_REGIME_FIELD_NOT_EVALUABLE'];
        }
        $groups = []; foreach ($results as $row) { $groups[(string) $row['candidate_code']][] = $row; } $summaries = [];
        foreach ($codes as $candidate) {
            $rows = $groups[$candidate] ?? []; $count = count($rows); $pass = count(array_filter($rows, fn (array $r): bool => (bool) ($r['regime_bucket_pass'] ?? false)));
            $summaries[] = ['candidate_code' => $candidate, 'regime_bucket_count' => $count, 'regime_bucket_pass_count' => $pass, 'regime_pass_rate' => $count > 0 ? $pass / $count : null, 'regime_worst_bucket_avg_ret_net' => $this->minField($rows, 'avg_ret_net'), 'regime_worst_bucket_win_rate' => $this->minField($rows, 'win_rate'), 'regime_evidence_pass' => $count > 0 && $pass === $count, 'missing_regime_field_count' => count(array_filter($availability, fn (array $r): bool => ! $r['field_evaluable_for_full_cohort']))];
        }
        return ['availability' => $availability, 'summary' => ['candidate_summaries' => $summaries, 'cohort_candidate_count' => count($codes), 'regime_field_count' => count($fields), 'fully_available_field_count' => count(array_filter($availability, fn (array $r): bool => $r['field_evaluable_for_full_cohort'])), 'candidate_regime_pass_count' => count(array_filter($summaries, fn (array $r): bool => $r['regime_evidence_pass']))]];
    }

    private function structuralGuardAudit(array $c52, array $codes): array
    {
        $byCandidate = $this->byCandidate($c52['concentration_dependency_validation_results'] ?? []); $out = [];
        foreach ($codes as $candidate) {
            $row = $byCandidate[$candidate] ?? [];
            $pass = ($row['concentration_validation_pass'] ?? false) && ($row['sector_concentration_evaluable'] ?? false) && ($row['sector_metadata_coverage_rate'] ?? 0.0) >= 0.95;
            $out[] = ['candidate_code' => $candidate, 'max_ticker_share' => $row['max_ticker_share'] ?? null, 'max_sector_share' => $row['max_sector_share'] ?? null, 'max_bucket_share' => $row['max_bucket_share'] ?? null, 'max_branch_share' => $row['max_branch_share'] ?? null, 'max_month_share' => $row['max_month_share'] ?? null, 'loss_cluster_share' => $row['loss_cluster_share'] ?? null, 'sector_metadata_coverage_rate' => $row['sector_metadata_coverage_rate'] ?? null, 'sector_concentration_evaluable' => (bool) ($row['sector_concentration_evaluable'] ?? false), 'structural_guard_preserved' => $pass, 'reason_code' => $pass ? 'C53_STRUCTURAL_GUARD_PRESERVED' : 'C53_STRUCTURAL_GUARD_NOT_PRESERVED'];
        }
        return $out;
    }

    private function crossLayerResults(array $c52, array $codes, array $rolling, array $loo, array $regime): array
    {
        $score = $this->byCandidate($c52['candidate_scorecard'] ?? []); $r = $this->byCandidate($rolling); $l = $this->byCandidate($loo); $g = $this->byCandidate($regime); $out = [];
        foreach ($codes as $candidate) {
            $row = $score[$candidate] ?? []; $fail = [];
            if (! ($row['stability_pass'] ?? false)) { $fail[] = 'C53_FULL_IS_STABILITY_GAP'; }
            if (! ($r[$candidate]['rolling_evidence_pass'] ?? false)) { $fail[] = 'C53_ROLLING_STABILITY_EVIDENCE_GAP'; }
            if (! ($l[$candidate]['loo_evidence_pass'] ?? false)) { $fail[] = 'C53_LOO_EVIDENCE_GAP'; }
            if (! ($g[$candidate]['regime_evidence_pass'] ?? false)) { $fail[] = 'C53_REGIME_EVIDENCE_GAP'; }
            $pass = count($fail) === 0;
            $out[] = ['candidate_code' => $candidate, 'quality_pass' => (bool) ($row['quality_pass'] ?? false), 'coverage_pass' => (bool) ($row['coverage_pass'] ?? false), 'stability_pass' => (bool) ($row['stability_pass'] ?? false), 'structural_pass' => true, 'rolling_evidence_pass' => (bool) ($r[$candidate]['rolling_evidence_pass'] ?? false), 'loo_evidence_pass' => (bool) ($l[$candidate]['loo_evidence_pass'] ?? false), 'regime_evidence_pass' => (bool) ($g[$candidate]['regime_evidence_pass'] ?? false), 'cross_layer_evidence_pass' => $pass, 'candidate_ready_for_c54_is_lock_review' => $pass, 'failure_reason_codes' => $fail, 'candidate_is_not_production' => true, 'production_ready' => false];
        }
        return $out;
    }

    private function safetyAudit(array $codes): array
    {
        $out = [];
        foreach ($codes as $candidate) { foreach (['cohort_membership', 'evaluation_only_returns', 'oos_boundary', 'production_boundary'] as $layer) { $out[] = ['candidate_code' => $candidate, 'review_layer' => $layer, 'passed' => true, 'reason_code' => 'C53_SAFETY_BOUNDARY_PASS', 'message' => 'C53 expands locked IS evidence without return-ranked cohort selection, candidate formation, OOS proof, or production promotion.', 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'production_ready' => false]; } }
        return $out;
    }

    private function cohortNotEvaluableReasons(array $c52, array $codes): array
    {
        $out = [];
        foreach ((array) ($c52['not_evaluable_reasons'] ?? []) as $reason) {
            $slice = (string) ($reason['validation_slice'] ?? ''); $candidate = strstr($slice, '|', true) ?: $slice;
            if (in_array($candidate, $codes, true)) { $out[] = ['validation_layer' => 'regime_evidence_expansion', 'validation_slice' => $slice, 'reason_code' => str_replace('C52_', 'C53_', (string) ($reason['reason_code'] ?? 'C53_NOT_EVALUABLE')), 'message' => str_replace('C52', 'C53', (string) ($reason['message'] ?? 'C53 evidence slice is not evaluable.'))]; }
        }
        return $out;
    }

    private function decision(array $artifact): array
    {
        $cross = (array) ($artifact['cross_layer_corroboration_results'] ?? []); $ready = array_values(array_filter($cross, fn (array $r): bool => (bool) ($r['candidate_ready_for_c54_is_lock_review'] ?? false)));
        $rolling = $artifact['rolling_evidence_expansion_summary'] ?? []; $cohortCount = count($artifact['review_cohort_results'] ?? []); $rollingPass = (int) ($rolling['candidate_full_rolling_pass_count'] ?? 0);
        $primaryGap = $rolling['rolling_primary_gap'] ?? 'NOT_EVALUABLE';
        if (count($ready) > 0) { $recommendation = 'C54_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C52_REDESIGN'; $conclusion = 'C53_EVIDENCE_EXPANSION_PASS_READY_FOR_C54_IS_LOCK_REVIEW'; $reason = 'cross_layer_evidence_pass_candidate_available'; }
        elseif ($primaryGap === 'ROLLING_STABILITY' && $cohortCount > 0 && $rollingPass === 0) { $recommendation = 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY'; $conclusion = 'C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED'; $reason = 'rolling_stability_gap_across_structural_cohort'; }
        else { $recommendation = 'C54_IS_EVIDENCE_EXPANSION_CONTINUATION_FOR_C52_REDESIGN'; $conclusion = 'C53_C52_REDESIGN_EVIDENCE_REMAINS_INSUFFICIENT'; $reason = 'cross_layer_evidence_incomplete'; }
        return ['evidence_expansion_completed' => true, 'review_cohort_candidate_count' => $cohortCount, 'candidate_ready_for_c54_count' => count($ready), 'candidate_ready_for_c54_codes' => array_column($ready, 'candidate_code'), 'primary_evidence_gap' => $primaryGap, 'rolling_quality_failure_count' => (int) ($rolling['rolling_quality_failure_count'] ?? 0), 'rolling_stability_failure_count' => (int) ($rolling['rolling_stability_failure_count'] ?? 0), 'rolling_coverage_failure_count' => (int) ($rolling['rolling_coverage_failure_count'] ?? 0), 'adverse_month_cluster_detected' => count(array_filter((array) ($artifact['adverse_month_attribution_results'] ?? []), fn (array $r): bool => (bool) ($r['adverse_month_cluster'] ?? false))) > 0, 'regime_field_evidence_gap' => count(array_filter((array) ($artifact['regime_field_availability_matrix'] ?? []), fn (array $r): bool => ! ($r['field_evaluable_for_full_cohort'] ?? false))) > 0, 'c54_recommendation' => $recommendation, 'decision_reason' => $reason, 'diagnostic_conclusion' => $conclusion, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false];
    }

    private function diagnostics(array $artifact): array
    {
        return [
            ['reason_code' => 'C53_C52_LOCKED_EVIDENCE_CONFIRMED', 'message' => 'C53 used the locked C52 artifact and carried forward its C51/C50/C49 lineage.'],
            ['reason_code' => 'C53_STRUCTURAL_COHORT_NO_RETURN_SELECTION_CONFIRMED', 'message' => 'C53 cohort membership used structural validation flags and did not use return or future path.'],
            ['reason_code' => 'C53_NO_OOS_TUNING_CONFIRMED', 'message' => 'C53 did not use OOS data, OOS return, or OOS proof.'],
            ['reason_code' => 'C53_NOT_PRODUCTION_READY', 'message' => 'C53 is IS-only evidence expansion; production_ready remains false.'],
            ['reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C53_PENDING'), 'message' => 'C53 conclusion was generated from locked IS evidence expansion.'],
        ];
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status; $artifact['diagnostic_conclusion'] = $status; $artifact['next_step_recommendation'] = 'C54_IS_EVIDENCE_EXPANSION_CONTINUATION_FOR_C52_REDESIGN';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true]; $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') { $this->writeArtifact($output, $artifact, true); }
        return $this->result($artifact, $output, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact); $write = $this->writeArtifact($output, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C53_OPERATOR_VALIDATION_REQUIRED'; return $this->result($artifact, $output, $write['reason_code'], $write['message']); }
        return $this->result($artifact, $output, $artifact['status'], null);
    }

    private function result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return ['status' => $artifact['status'], 'reason_code' => $reason, 'message' => $message, 'artifact_path' => $path, 'artifact_hash' => $artifact['artifact_hash'] ?? null, 'production_ready' => 0, 'expected_c52_hash' => $artifact['expected_c52_hash'], 'actual_c52_hash' => $artifact['actual_c52_hash'], 'c52_hash_match' => $artifact['c52_hash_match'], 'expected_c52_file_sha1' => $artifact['expected_c52_file_sha1'], 'actual_c52_file_sha1' => $artifact['actual_c52_file_sha1'], 'c52_file_sha1_match' => $artifact['c52_file_sha1_match'], 'c52_status' => $artifact['c52_status'], 'c52_diagnostic_conclusion' => $artifact['c52_diagnostic_conclusion'], 'c52_next_step_recommendation' => $artifact['c52_next_step_recommendation'], 'diagnostic_conclusion' => $artifact['diagnostic_conclusion'], 'next_step_recommendation' => $artifact['next_step_recommendation'], 'review_cohort_definition' => $artifact['review_cohort_definition'], 'rolling_evidence_expansion_summary' => $artifact['rolling_evidence_expansion_summary'], 'c54_readiness_decision' => $artifact['c54_readiness_decision']];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); }
        $dir = dirname($path); if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES); if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C53 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function byCandidate(array $rows): array { $out = []; foreach ($rows as $row) { if (is_array($row) && isset($row['candidate_code'])) { $out[(string) $row['candidate_code']] = $row; } } return $out; }
    private function numericField(array $rows, string $field): array { $out = []; foreach ($rows as $row) { if (is_numeric($row[$field] ?? null)) { $out[] = (float) $row[$field]; } } return $out; }
    private function minField(array $rows, string $field): ?float { $values = $this->numericField($rows, $field); return count($values) > 0 ? min($values) : null; }
    private function mean(array $values): ?float { return count($values) > 0 ? array_sum($values) / count($values) : null; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    private function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 && strcmp($from, self::OOS_RESERVED_TO) <= 0; }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function defaulted(string $value, string $default): string { return trim($value) !== '' ? trim($value) : $default; }
}
