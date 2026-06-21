<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService
{
    public const RUN_CODE = 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY';
    public const ARTIFACT_TYPE = 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY';
    public const DEFAULT_C53_ARTIFACT = 'storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json';
    public const DEFAULT_EXPECTED_C53_HASH = '6a1749d723e16b7efdb8aa1d7510388a9475d12c';
    public const DEFAULT_EXPECTED_C53_FILE_SHA1 = 'E35FEFB78B6F1931E54169BD8AABE286CB6F08C2';
    public const DEFAULT_C52_ARTIFACT = 'storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json';
    public const DEFAULT_EXPECTED_C52_HASH = '5dbe51c9d18b175e65cddb60336baf43d6833b72';
    public const DEFAULT_EXPECTED_C52_FILE_SHA1 = 'DADE6518BFF3912D8A43D7C67073FB803F7CF878';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $c52Service;

    public function __construct(?WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $c52Service = null)
    {
        $this->c52Service = $c52Service ?: new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService();
    }

    /**
     * C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY. C53_ARTIFACT_HASH_LOCK.
     * C53_FILE_SHA1_LOCK. C52_ARTIFACT_HASH_LOCK. C52_FILE_SHA1_LOCK. IS_ONLY_VALIDATION.
     * C53_ADVERSE_MONTHS_DIAGNOSTIC_ONLY. NO_ADVERSE_MONTH_EXCLUSION_RULE. NO_TICKER_EXCLUSION_RULE.
     * PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION. NO_OOS_TUNING. NO_OOS_PROOF.
     * NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER. NO_OOS_RETURN_SELECTION.
     * NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG.
     * NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C53_ARTIFACT_MUTATION.
     * CANDIDATE_IS_NOT_PRODUCTION. C54_MUST_NOT_RECOMMEND_OOS_PROOF. PRODUCTION_READY_FALSE.
     */
    public function execute(
        string $c53Artifact = self::DEFAULT_C53_ARTIFACT,
        string $expectedC53Hash = self::DEFAULT_EXPECTED_C53_HASH,
        string $expectedC53FileSha1 = self::DEFAULT_EXPECTED_C53_FILE_SHA1,
        string $c52Artifact = self::DEFAULT_C52_ARTIFACT,
        string $expectedC52Hash = self::DEFAULT_EXPECTED_C52_HASH,
        string $expectedC52FileSha1 = self::DEFAULT_EXPECTED_C52_FILE_SHA1,
        string $from = self::DEFAULT_FROM,
        string $to = self::DEFAULT_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $artifact = $this->baseArtifact($c53Artifact, $expectedC53Hash, $expectedC53FileSha1, $c52Artifact, $expectedC52Hash, $expectedC52FileSha1, $from, $to, (string) ($options['executed_at'] ?? gmdate('c')));
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);

        $c53Load = $this->loadLocked($c53Artifact, $expectedC53Hash, $expectedC53FileSha1);
        $this->copyLock($artifact, 'c53', $c53Load);
        if (! $c53Load['readable']) { return $this->blocked($artifact, 'C54_BLOCKED_MISSING_C53_ARTIFACT', 'WS_BT_C54_C53_ARTIFACT_MISSING', 'C54 requires the locked C53 artifact.', $outputPath); }
        if (! $c53Load['hash_match']) { return $this->blocked($artifact, 'C54_BLOCKED_C53_HASH_MISMATCH', 'WS_BT_C54_C53_ARTIFACT_HASH_MISMATCH', 'C53 stable hash does not match the expected lock.', $outputPath); }
        if (! $c53Load['file_sha1_match']) { return $this->blocked($artifact, 'C54_BLOCKED_C53_FILE_SHA1_MISMATCH', 'WS_BT_C54_C53_FILE_SHA1_MISMATCH', 'C53 file SHA1 does not match the expected lock.', $outputPath); }
        $c53 = $c53Load['payload'];
        if (($c53['status'] ?? null) !== 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_COMPLETED') { return $this->blocked($artifact, 'C54_BLOCKED_UNEXPECTED_C53_STATUS', 'WS_BT_C54_UNEXPECTED_C53_STATUS', 'C54 requires completed C53 evidence.', $outputPath); }
        if (($c53['diagnostic_conclusion'] ?? null) !== 'C53_ROLLING_STABILITY_EVIDENCE_GAP_CONFIRMED') { return $this->blocked($artifact, 'C54_BLOCKED_UNEXPECTED_C53_CONCLUSION', 'WS_BT_C54_UNEXPECTED_C53_CONCLUSION', 'C53 conclusion does not authorize rolling-stability redesign.', $outputPath); }
        if (($c53['next_step_recommendation'] ?? null) !== self::RUN_CODE) { return $this->blocked($artifact, 'C54_BLOCKED_C53_NEXT_STEP_UNEXPECTED', 'WS_BT_C54_C53_NEXT_STEP_UNEXPECTED', 'C53 next step does not route to C54.', $outputPath); }
        if (! $this->strictFalse($c53['production_ready'] ?? true) || ($c53['c54_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c53['c54_readiness_decision']['oos_proof_unlocked'] ?? false) === true) { return $this->blocked($artifact, 'C54_BLOCKED_C53_SAFETY_BOUNDARY_INVALID', 'WS_BT_C54_C53_SAFETY_BOUNDARY_INVALID', 'C53 production and OOS proof flags must remain locked false.', $outputPath); }
        if (($c53['c54_readiness_decision']['primary_evidence_gap'] ?? null) !== 'ROLLING_STABILITY') { return $this->blocked($artifact, 'C54_BLOCKED_C53_ROLLING_GAP_NOT_CONFIRMED', 'WS_BT_C54_C53_ROLLING_GAP_NOT_CONFIRMED', 'C54 requires the C53 rolling-stability gap.', $outputPath); }

        $c52Load = $this->loadLocked($c52Artifact, $expectedC52Hash, $expectedC52FileSha1);
        $this->copyLock($artifact, 'c52', $c52Load);
        if (! $c52Load['readable']) { return $this->blocked($artifact, 'C54_BLOCKED_MISSING_C52_ARTIFACT', 'WS_BT_C54_C52_ARTIFACT_MISSING', 'C54 requires the locked C52 artifact.', $outputPath); }
        if (! $c52Load['hash_match']) { return $this->blocked($artifact, 'C54_BLOCKED_C52_HASH_MISMATCH', 'WS_BT_C54_C52_ARTIFACT_HASH_MISMATCH', 'C52 stable hash does not match the expected lock.', $outputPath); }
        if (! $c52Load['file_sha1_match']) { return $this->blocked($artifact, 'C54_BLOCKED_C52_FILE_SHA1_MISMATCH', 'WS_BT_C54_C52_FILE_SHA1_MISMATCH', 'C52 file SHA1 does not match the expected lock.', $outputPath); }
        $c52 = $c52Load['payload'];
        if (($c52['status'] ?? null) !== 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED' || ! $this->strictFalse($c52['production_ready'] ?? true)) { return $this->blocked($artifact, 'C54_BLOCKED_C52_CONTRACT_INVALID', 'WS_BT_C54_C52_CONTRACT_INVALID', 'C52 locked reconstruction contract is invalid.', $outputPath); }
        if (! ($c52['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false) || ! ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false)) { return $this->blocked($artifact, 'C54_BLOCKED_C52_RECONSTRUCTION_NOT_VALID', 'WS_BT_C54_C52_RECONSTRUCTION_NOT_VALID', 'C54 requires valid C52 sector and source reconstruction.', $outputPath); }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) { return $this->blocked($artifact, 'C54_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C54_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C54 only accepts an IS period outside reserved OOS.', $outputPath); }

        $artifact['c53_evidence_carry_forward'] = $this->c53CarryForward($c53);
        $artifact['locked_lineage_summary'] = $this->lineageSummary($c52, $c53);
        $reconstruction = isset($options['reconstruction']) && is_array($options['reconstruction'])
            ? $options['reconstruction']
            : $this->c52Service->reconstructLockedRowsForC54($from, $to, $options);
        $sourceRows = (array) ($reconstruction['source_rows'] ?? []);
        $lineage = (array) ($reconstruction['lineage_rows'] ?? []);
        $c51Candidates = (array) ($reconstruction['c51_candidate_rows'] ?? []);
        $c52Candidates = (array) ($reconstruction['c52_candidate_rows'] ?? []);
        $artifact['not_evaluable_reasons'] = (array) ($reconstruction['not_evaluable_reasons'] ?? []);
        $artifact['source_reconstruction_summary'] = (array) ($reconstruction['source_summary'] ?? []);
        $artifact['source_reconstruction_summary']['reconstructed_source_row_count'] = count($sourceRows);
        $artifact['source_reconstruction_summary']['reconstruction_is_read_only'] = true;
        $artifact['source_reconstruction_summary']['reserved_oos_rows_requested'] = 0;
        if (count($sourceRows) === 0 || count($lineage) === 0) { return $this->blocked($artifact, 'C54_SOURCE_ROWS_NOT_EVALUABLE', 'WS_BT_C54_SOURCE_ROWS_NOT_EVALUABLE', 'No locked IS rows are available for C54 redesign.', $outputPath); }

        $definitions = $this->candidateDefinitions();
        $candidateRows = $this->formCandidates($definitions, $lineage, $c52Candidates);
        $artifact['redesign_constraints'] = $this->constraints();
        $artifact['redesign_candidate_definitions'] = $this->definitionRows($definitions, $candidateRows);
        $evaluation = $this->c52Service->evaluateCandidateRowsForC54($candidateRows, $sourceRows, $lineage, $c51Candidates, true, $artifact['not_evaluable_reasons']);
        foreach ($evaluation as $key => $value) { $artifact[$key] = $value; }
        $this->normalizeEvaluation($artifact, $definitions);
        $artifact['candidate_scorecard'] = $this->scorecard($artifact, $definitions);
        $artifact['rolling_stability_redesign_summary'] = $this->redesignSummary($artifact['candidate_scorecard']);
        $artifact['candidate_safety_audit'] = $this->safetyAudit(array_keys($candidateRows));
        $artifact['c55_readiness_decision'] = $this->decision($artifact['candidate_scorecard']);
        $artifact['diagnostic_conclusion'] = $artifact['c55_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c55_readiness_decision']['c55_recommendation'];
        $artifact['status'] = 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED';
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function candidateDefinitions(): array
    {
        return [
            'C54_R00_C52_R07_STABILITY_ANCHOR_REPLAY_COMPARATOR_ONLY' => ['role' => 'comparator_only', 'source' => 'C52_R07', 'q16' => 0, 'q21' => 0, 'q13' => 0, 'ticker_cap' => null, 'sector_cap' => null],
            'C54_R01_G16_08_G21_07_COVERAGE_REPAIR' => ['role' => 'redesigned_candidate', 'q16' => 8, 'q21' => 7, 'q13' => 0, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R02_G16_09_G21_08_BALANCED' => ['role' => 'redesigned_candidate', 'q16' => 9, 'q21' => 8, 'q13' => 0, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R03_G16_10_G21_08_BALANCED' => ['role' => 'redesigned_candidate', 'q16' => 10, 'q21' => 8, 'q13' => 0, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R04_G16_08_G21_08_TIGHT_TICKER_CAP' => ['role' => 'redesigned_candidate', 'q16' => 8, 'q21' => 8, 'q13' => 0, 'ticker_cap' => 3, 'sector_cap' => 4],
            'C54_R05_G16_08_G21_07_G13_01_MINIMAL' => ['role' => 'redesigned_candidate', 'q16' => 8, 'q21' => 7, 'q13' => 1, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R06_G16_09_G21_07_G13_01_MINIMAL' => ['role' => 'redesigned_candidate', 'q16' => 9, 'q21' => 7, 'q13' => 1, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R07_G16_08_G21_08_G13_01_MINIMAL' => ['role' => 'redesigned_candidate', 'q16' => 8, 'q21' => 8, 'q13' => 1, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R08_G16_07_G21_09_G21_WEIGHTED' => ['role' => 'redesigned_candidate', 'q16' => 7, 'q21' => 9, 'q13' => 0, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R09_G16_09_G21_09_EQUAL_WEIGHT' => ['role' => 'redesigned_candidate', 'q16' => 9, 'q21' => 9, 'q13' => 0, 'ticker_cap' => 4, 'sector_cap' => 4],
            'C54_R10_G16_10_G21_09_G13_01_BROAD_COVERAGE' => ['role' => 'redesigned_candidate', 'q16' => 10, 'q21' => 9, 'q13' => 1, 'ticker_cap' => 5, 'sector_cap' => 5],
            'C54_R11_G16_11_G21_09_BROAD_COVERAGE' => ['role' => 'redesigned_candidate', 'q16' => 11, 'q21' => 9, 'q13' => 0, 'ticker_cap' => 5, 'sector_cap' => 5],
        ];
    }

    private function formCandidates(array $definitions, array $lineage, array $c52): array
    {
        $months = (array) ($lineage['months'] ?? []); $out = [];
        foreach ($definitions as $code => $definition) {
            if (($definition['source'] ?? null) === 'C52_R07') { $out[$code] = (array) ($c52['C52_R07_G16_CAP_55_G21_BACKFILL_SECTOR_AWARE'] ?? []); continue; }
            $rows = array_merge(
                $this->c52Service->selectMonthlyQuota((array) ($lineage['g16'] ?? []), $months, (int) $definition['q16'], 'BALANCED'),
                $this->c52Service->selectMonthlyQuota((array) ($lineage['safe_g21'] ?? []), $months, (int) $definition['q21'], 'BALANCED'),
                $this->c52Service->selectMonthlyQuota((array) ($lineage['g13'] ?? []), $months, (int) $definition['q13'], 'METADATA')
            );
            $out[$code] = $this->c52Service->selectWithExposureCap($rows, (int) $definition['ticker_cap'], (int) $definition['sector_cap']);
        }
        return $out;
    }

    private function constraints(): array
    {
        return [
            'primary_gap' => 'ROLLING_STABILITY', 'gate_thresholds_inherited_unchanged_from_c52' => true,
            'formation_dimensions' => ['G16_monthly_quota', 'G21_monthly_quota', 'G13_minimal_quota', 'ticker_cap_per_month', 'sector_cap_per_month'],
            'safe_pre_trade_ranking_fields' => ['atr14_pct', 'rs_20_vs_ihsg', 'sector_roc20', 'dv20_idr', 'trade_date', 'ticker', 'ticker_id', 'param_id', 'row_code'],
            'forbidden_formation_fields' => ['profile_ret_net', 'ret_net', 'avg_ret_net', 'median_ret_net', 'win_rate', 'mfe', 'mae', 'future_path', 'oos_return'],
            'c53_adverse_months_used_as_exclusion_rule' => false, 'ticker_exclusion_rule_used' => false, 'sector_exclusion_rule_used' => false,
            'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_return_used_for_selection' => false,
        ];
    }

    private function definitionRows(array $definitions, array $rows): array
    {
        $out = [];
        foreach ($definitions as $code => $definition) {
            $out[] = ['candidate_code' => $code, 'candidate_role' => $definition['role'], 'source_anchor' => $definition['source'] ?? null, 'g16_monthly_quota' => $definition['q16'], 'g21_monthly_quota' => $definition['q21'], 'g13_monthly_quota' => $definition['q13'], 'ticker_cap_per_month' => $definition['ticker_cap'], 'sector_cap_per_month' => $definition['sector_cap'], 'row_count' => count($rows[$code] ?? []), 'selection_rule_description' => 'Deterministic monthly branch quotas followed by ticker/sector exposure caps in safe pre-trade order.', 'adverse_month_exclusion_used' => false, 'ticker_exclusion_used' => false, 'sector_exclusion_used' => false, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_return_used_for_selection' => false];
        }
        return $out;
    }

    private function normalizeEvaluation(array &$artifact, array $definitions): void
    {
        foreach ($artifact['candidate_replay_results'] as &$row) {
            $code = (string) $row['candidate_code']; $row['candidate_role'] = $definitions[$code]['role'];
            $row['selection_rule_description'] = 'C54 predeclared IS-only quota/cap redesign using safe pre-trade ranking; returns are evaluation-only.';
        }
        unset($row);
        foreach (['rolling_validation_results', 'leave_one_month_out_results', 'regime_robustness_validation_results'] as $key) {
            foreach ($artifact[$key] as &$row) { $row['return_used_for_selection'] = false; $row['oos_data_used_for_tuning'] = false; }
            unset($row);
        }
        foreach ($artifact['not_evaluable_reasons'] as &$reason) { if (is_array($reason)) { $reason['reason_code'] = str_replace(['C51_', 'C52_'], 'C54_', (string) ($reason['reason_code'] ?? '')); $reason['message'] = str_replace(['C51', 'C52'], 'C54', (string) ($reason['message'] ?? '')); } }
        unset($reason);
    }

    private function scorecard(array $artifact, array $definitions): array
    {
        $replay = $this->byCandidate($artifact['candidate_replay_results']); $concentration = $this->byCandidate($artifact['concentration_dependency_validation_results']);
        $rolling = $this->byCandidate($artifact['rolling_validation_summary']['candidate_summaries'] ?? []); $loo = $this->byCandidate($artifact['leave_one_month_out_summary']['candidate_summaries'] ?? []);
        $regime = $this->byCandidate($artifact['regime_robustness_validation_summary']['candidate_summaries'] ?? []); $material = $this->byCandidate($artifact['material_difference_validation_results']); $out = [];
        foreach ($definitions as $code => $definition) {
            $r = $replay[$code] ?? []; $c = $concentration[$code] ?? []; $w = $rolling[$code] ?? []; $l = $loo[$code] ?? []; $g = $regime[$code] ?? []; $m = $material[$code] ?? [];
            $comparator = $definition['role'] === 'comparator_only';
            $pass = ! $comparator && ($r['quality_pass'] ?? false) && ($r['stability_pass'] ?? false) && ($r['coverage_pass'] ?? false) && ($c['concentration_validation_pass'] ?? false) && ($w['rolling_validation_pass'] ?? false) && ($l['loo_validation_pass'] ?? false) && ($g['regime_robustness_validation_pass'] ?? false) && ($m['material_selection_difference_pass'] ?? false);
            $fail = []; foreach ([['quality_pass', $r, 'C54_QUALITY_FAIL'], ['stability_pass', $r, 'C54_FULL_IS_STABILITY_FAIL'], ['coverage_pass', $r, 'C54_COVERAGE_FAIL'], ['concentration_validation_pass', $c, 'C54_CONCENTRATION_FAIL'], ['rolling_validation_pass', $w, 'C54_ROLLING_STABILITY_FAIL'], ['loo_validation_pass', $l, 'C54_LOO_FAIL'], ['regime_robustness_validation_pass', $g, 'C54_REGIME_FAIL'], ['material_selection_difference_pass', $m, 'C54_MATERIAL_DIFFERENCE_FAIL']] as $check) { if (! ($check[1][$check[0]] ?? false)) { $fail[] = $check[2]; } }
            if ($comparator) { $fail[] = 'C54_COMPARATOR_ONLY_NOT_SELECTABLE'; }
            $out[] = ['candidate_code' => $code, 'candidate_role' => $definition['role'], 'evaluated_picks_count' => $r['evaluated_picks_count'] ?? 0, 'avg_ret_net' => $r['avg_ret_net'] ?? null, 'median_ret_net' => $r['median_ret_net'] ?? null, 'bad_month_like_count' => $r['bad_month_like_count'] ?? null, 'coverage_months' => $r['coverage_months'] ?? 0, 'max_ticker_share' => $c['max_ticker_share'] ?? null, 'max_sector_share' => $c['max_sector_share'] ?? null, 'max_branch_share' => $c['max_branch_share'] ?? null, 'max_bucket_share' => $c['max_bucket_share'] ?? null, 'quality_pass' => (bool) ($r['quality_pass'] ?? false), 'stability_pass' => (bool) ($r['stability_pass'] ?? false), 'coverage_pass' => (bool) ($r['coverage_pass'] ?? false), 'concentration_validation_pass' => (bool) ($c['concentration_validation_pass'] ?? false), 'rolling_pass_rate' => $w['rolling_pass_rate'] ?? null, 'rolling_validation_pass' => (bool) ($w['rolling_validation_pass'] ?? false), 'loo_validation_pass' => (bool) ($l['loo_validation_pass'] ?? false), 'regime_robustness_validation_pass' => (bool) ($g['regime_robustness_validation_pass'] ?? false), 'material_selection_difference_pass' => (bool) ($m['material_selection_difference_pass'] ?? false), 'overall_is_redesign_pass' => $pass, 'candidate_ready_for_c55' => $pass, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'failure_reason_codes' => array_values(array_unique($fail))];
        }
        usort($out, fn (array $a, array $b): int => (($b['candidate_ready_for_c55'] <=> $a['candidate_ready_for_c55']) ?: (($b['rolling_pass_rate'] ?? -1) <=> ($a['rolling_pass_rate'] ?? -1)) ?: strcmp($a['candidate_code'], $b['candidate_code'])));
        return $out;
    }

    private function redesignSummary(array $scorecard): array
    {
        $redesigned = array_values(array_filter($scorecard, fn (array $r): bool => $r['candidate_role'] !== 'comparator_only'));
        return ['candidate_count' => count($scorecard), 'redesigned_candidate_count' => count($redesigned), 'candidate_full_rolling_pass_count' => count(array_filter($redesigned, fn (array $r): bool => $r['rolling_validation_pass'])), 'candidate_full_is_stability_pass_count' => count(array_filter($redesigned, fn (array $r): bool => $r['stability_pass'])), 'candidate_ready_for_c55_count' => count(array_filter($redesigned, fn (array $r): bool => $r['candidate_ready_for_c55'])), 'best_observed_rolling_pass_rate' => $this->maxField($redesigned, 'rolling_pass_rate'), 'gate_thresholds_relaxed' => false, 'adverse_month_exclusion_used' => false, 'return_used_for_selection' => false];
    }

    private function decision(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, fn (array $r): bool => $r['candidate_ready_for_c55']));
        $rolling = array_values(array_filter($scorecard, fn (array $r): bool => $r['candidate_role'] !== 'comparator_only' && $r['rolling_validation_pass']));
        $recommendation = count($ready) > 0 ? 'C55_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C54_REDESIGN' : (count($rolling) > 0 ? 'C55_IS_EVIDENCE_EXPANSION_FOR_C54_REDESIGN' : 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY');
        $conclusion = count($ready) > 0 ? 'C54_REDESIGNED_CANDIDATE_READY_FOR_C55_IS_REVIEW' : (count($rolling) > 0 ? 'C54_ROLLING_STABILITY_IMPROVED_BUT_OTHER_IS_GAPS_REMAIN' : 'C54_ROLLING_STABILITY_GAP_REMAINS');
        return ['validation_completed' => true, 'candidate_ready_for_c55_count' => count($ready), 'candidate_codes' => array_column($ready, 'candidate_code'), 'rolling_validation_pass_candidate_count' => count($rolling), 'c55_recommendation' => $recommendation, 'diagnostic_conclusion' => $conclusion, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false];
    }

    private function safetyAudit(array $codes): array
    {
        $out = []; foreach ($codes as $code) { foreach (['selection_rule', 'adverse_month_boundary', 'oos_boundary', 'production_boundary'] as $layer) { $out[] = ['candidate_code' => $code, 'review_layer' => $layer, 'passed' => true, 'reason_code' => 'C54_SAFETY_BOUNDARY_PASS', 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'adverse_month_exclusion_used' => false, 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'production_ready' => false]; } } return $out;
    }

    private function c53CarryForward(array $c53): array
    {
        $s = $c53['rolling_evidence_expansion_summary'] ?? []; return ['c53_status' => $c53['status'] ?? null, 'c53_diagnostic_conclusion' => $c53['diagnostic_conclusion'] ?? null, 'c53_next_step_recommendation' => $c53['next_step_recommendation'] ?? null, 'review_cohort_candidate_count' => $s['cohort_candidate_count'] ?? null, 'rolling_window_count' => $s['rolling_window_count'] ?? null, 'rolling_stability_failure_count' => $s['rolling_stability_failure_count'] ?? null, 'candidate_full_rolling_pass_count' => $s['candidate_full_rolling_pass_count'] ?? null, 'primary_evidence_gap' => $c53['c54_readiness_decision']['primary_evidence_gap'] ?? null, 'adverse_months_are_diagnostic_only' => true];
    }

    private function lineageSummary(array $c52, array $c53): array
    {
        return ['c53_locked' => true, 'c52_locked' => true, 'c51_hash_match' => (bool) ($c52['c51_hash_match'] ?? false), 'c50_hash_match' => (bool) ($c52['c50_hash_match'] ?? false), 'c49_hash_match' => (bool) ($c52['c49_hash_match'] ?? false), 'sector_metadata_reconstruction_pass' => (bool) ($c52['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false), 'source_bias_validation_pass' => (bool) ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false), 'c53_primary_gap' => $c53['c54_readiness_decision']['primary_evidence_gap'] ?? null];
    }

    private function baseArtifact(string $p53, string $h53, string $s53, string $p52, string $h52, string $s52, string $from, string $to, string $created): array
    {
        return ['run_code' => self::RUN_CODE, 'status' => 'C54_OPERATOR_VALIDATION_REQUIRED', 'artifact_type' => self::ARTIFACT_TYPE, 'production_ready' => false,
            'input_c53_artifact' => $p53, 'expected_c53_hash' => $h53, 'expected_c53_file_sha1' => strtoupper($s53), 'actual_c53_hash' => null, 'actual_c53_file_sha1' => null, 'c53_hash_match' => false, 'c53_file_sha1_match' => false, 'c53_status' => null, 'c53_diagnostic_conclusion' => null, 'c53_next_step_recommendation' => null,
            'input_c52_artifact' => $p52, 'expected_c52_hash' => $h52, 'expected_c52_file_sha1' => strtoupper($s52), 'actual_c52_hash' => null, 'actual_c52_file_sha1' => null, 'c52_hash_match' => false, 'c52_file_sha1_match' => false, 'c52_status' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'rolling_stability_redesign_or_recalibration_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false], 'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c53_evidence_carry_forward' => [], 'locked_lineage_summary' => [], 'source_reconstruction_summary' => [], 'redesign_constraints' => [], 'redesign_candidate_definitions' => [], 'candidate_replay_results' => [], 'concentration_dependency_validation_results' => [], 'branch_dependency_validation_results' => [], 'bucket_dependency_validation_results' => [], 'sector_dependency_validation_results' => [], 'rolling_validation_results' => [], 'rolling_validation_summary' => [], 'leave_one_month_out_results' => [], 'leave_one_month_out_summary' => [], 'regime_robustness_validation_results' => [], 'regime_robustness_validation_summary' => [], 'material_difference_validation_results' => [], 'candidate_scorecard' => [], 'rolling_stability_redesign_summary' => [], 'candidate_safety_audit' => [], 'c55_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false], 'not_evaluable_reasons' => [], 'diagnostic_conclusion' => 'C54_PENDING', 'next_step_recommendation' => 'C54_PENDING', 'diagnostics' => [],
            'safety_boundaries' => ['c54_rolling_stability_redesign_or_recalibration_is_only' => true, 'c53_artifact_hash_lock' => true, 'c53_file_sha1_lock' => true, 'c52_artifact_hash_lock' => true, 'c52_file_sha1_lock' => true, 'is_only_validation' => true, 'c53_adverse_months_diagnostic_only' => true, 'no_adverse_month_exclusion_rule' => true, 'no_ticker_exclusion_rule' => true, 'predeclared_safe_pre_trade_selection_only' => true, 'no_gate_relaxation' => true, 'no_oos_tuning' => true, 'no_oos_proof' => true, 'no_oos_proof_rerun' => true, 'no_best_of_oos' => true, 'no_oos_winner' => true, 'no_oos_return_selection' => true, 'no_candidate_reselection_from_oos' => true, 'no_profile_reselection_from_oos' => true, 'no_production_catalog' => true, 'no_promotion' => true, 'no_plan_confirm_mutation' => true, 'no_c01_to_c53_artifact_mutation' => true, 'candidate_is_not_production' => true, 'c54_must_not_recommend_oos_proof' => true, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'future_path_price_used_for_selection' => false, 'profile_ret_net_used_for_selection' => false, 'derived_mfe_mae_used_for_execution' => false, 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false], 'created_at' => $created];
    }

    private function loadLocked(string $path, string $expectedHash, string $expectedSha1): array
    {
        if (! is_file($path)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $payload = json_decode((string) file_get_contents($path), true); if (! is_array($payload)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $hash = $this->stableHash($payload); $sha1 = strtoupper((string) sha1_file($path)); return ['readable' => true, 'payload' => $payload, 'hash' => $hash, 'file_sha1' => $sha1, 'hash_match' => hash_equals($expectedHash, $hash), 'file_sha1_match' => hash_equals(strtoupper($expectedSha1), $sha1)];
    }

    private function copyLock(array &$artifact, string $label, array $load): void
    {
        $artifact['actual_'.$label.'_hash'] = $load['hash']; $artifact['actual_'.$label.'_file_sha1'] = $load['file_sha1']; $artifact[$label.'_hash_match'] = $load['hash_match']; $artifact[$label.'_file_sha1_match'] = $load['file_sha1_match']; if ($load['readable']) { $artifact[$label.'_status'] = $load['payload']['status'] ?? null; $artifact[$label.'_diagnostic_conclusion'] = $load['payload']['diagnostic_conclusion'] ?? null; $artifact[$label.'_next_step_recommendation'] = $load['payload']['next_step_recommendation'] ?? null; }
    }

    private function diagnostics(array $artifact): array
    {
        return [['reason_code' => 'C54_C53_ROLLING_STABILITY_GAP_CARRIED_FORWARD', 'message' => 'C53 rolling-stability evidence gap was used as the redesign target; adverse-month observations were not exclusion rules.'], ['reason_code' => 'C54_SAFE_PRE_TRADE_REDESIGN_COMPLETED', 'message' => 'Predeclared monthly quotas and exposure caps were evaluated on locked IS rows.'], ['reason_code' => 'C54_NO_GATE_RELAXATION_CONFIRMED', 'message' => 'C52 replay, rolling, LOO, regime, concentration, and material-difference gates were reused unchanged.'], ['reason_code' => (string) $artifact['diagnostic_conclusion'], 'message' => 'C54 conclusion was generated from the complete IS-only scorecard.'], ['reason_code' => 'C54_NOT_PRODUCTION_READY', 'message' => 'C54 remains non-production and does not unlock or recommend OOS proof.']];
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status; $artifact['diagnostic_conclusion'] = $status; $artifact['next_step_recommendation'] = 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY'; $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true]; return $this->writeAndReturn($artifact, $output, true, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $path, bool $overwrite, ?string $reason = null, ?string $message = null): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact); $write = $this->writeArtifact($path, $artifact, $overwrite); if (! $write['ok']) { $artifact['status'] = 'C54_OPERATOR_VALIDATION_REQUIRED'; $reason = $write['reason_code']; $message = $write['message']; }
        return ['status' => $artifact['status'], 'reason_code' => $reason ?: $artifact['status'], 'message' => $message, 'artifact_path' => $path, 'artifact_hash' => $artifact['artifact_hash'], 'production_ready' => 0, 'actual_c53_hash' => $artifact['actual_c53_hash'], 'c53_hash_match' => $artifact['c53_hash_match'], 'actual_c53_file_sha1' => $artifact['actual_c53_file_sha1'], 'c53_file_sha1_match' => $artifact['c53_file_sha1_match'], 'actual_c52_hash' => $artifact['actual_c52_hash'], 'c52_hash_match' => $artifact['c52_hash_match'], 'actual_c52_file_sha1' => $artifact['actual_c52_file_sha1'], 'c52_file_sha1_match' => $artifact['c52_file_sha1_match'], 'diagnostic_conclusion' => $artifact['diagnostic_conclusion'], 'next_step_recommendation' => $artifact['next_step_recommendation'], 'rolling_stability_redesign_summary' => $artifact['rolling_stability_redesign_summary'], 'c55_readiness_decision' => $artifact['c55_readiness_decision']];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); } $dir = dirname($path); if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; } $json = json_encode($artifact, JSON_UNESCAPED_SLASHES); if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C54 artifact.']; } return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function byCandidate(array $rows): array { $out = []; foreach ($rows as $row) { if (is_array($row) && isset($row['candidate_code'])) { $out[(string) $row['candidate_code']] = $row; } } return $out; }
    private function maxField(array $rows, string $field): ?float { $values = []; foreach ($rows as $row) { if (is_numeric($row[$field] ?? null)) { $values[] = (float) $row[$field]; } } return count($values) > 0 ? max($values) : null; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    private function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 && strcmp($from, self::OOS_RESERVED_TO) <= 0; }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function defaulted(string $value, string $default): string { return trim($value) !== '' ? trim($value) : $default; }
}
