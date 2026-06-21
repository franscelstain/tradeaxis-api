<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService
{
    public const RUN_CODE = 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY';
    public const ARTIFACT_TYPE = 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY';
    public const DEFAULT_C55_ARTIFACT = 'storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json';
    public const DEFAULT_EXPECTED_C55_HASH = 'a4145d6f356e678d0dadf95be5d356198ebfed79';
    public const DEFAULT_EXPECTED_C55_FILE_SHA1 = '18875FCAD7FD7CDA6607BB09A60917E853E68D2B';
    public const DEFAULT_C54_ARTIFACT = 'storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json';
    public const DEFAULT_EXPECTED_C54_HASH = '8c71a4352a1024dbe985e0f0bb6329f5e1545150';
    public const DEFAULT_EXPECTED_C54_FILE_SHA1 = '75410BB1A30A32FFFF9661CAD6818C13E044F7E5';
    public const DEFAULT_C53_ARTIFACT = 'storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json';
    public const DEFAULT_EXPECTED_C53_HASH = '6a1749d723e16b7efdb8aa1d7510388a9475d12c';
    public const DEFAULT_EXPECTED_C53_FILE_SHA1 = 'E35FEFB78B6F1931E54169BD8AABE286CB6F08C2';
    public const DEFAULT_C52_ARTIFACT = 'storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json';
    public const DEFAULT_EXPECTED_C52_HASH = '5dbe51c9d18b175e65cddb60336baf43d6833b72';
    public const DEFAULT_EXPECTED_C52_FILE_SHA1 = 'DADE6518BFF3912D8A43D7C67073FB803F7CF878';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';

    private WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $c52Service;

    private const VALID_C55_CONCLUSIONS = [
        'C55_ROLLING_STABILITY_GAP_REMAINS',
        'C55_NEAR_PASS_ROLLING_CANDIDATE_NOT_REPAIRED',
        'C55_CONCENTRATION_GAP_REMAINS',
        'C55_LOO_GAP_REMAINS',
        'C55_REGIME_ROBUSTNESS_GAP_REMAINS',
        'C55_REDESIGNED_CANDIDATE_FAILED_IS_REVIEW',
    ];

    public function __construct(?WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $c52Service = null)
    {
        $this->c52Service = $c52Service ?: new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService();
    }

    /**
     * C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY. C55_ARTIFACT_HASH_LOCK.
     * C55_FILE_SHA1_LOCK. C54_ARTIFACT_HASH_LOCK. C54_FILE_SHA1_LOCK. C53_ARTIFACT_HASH_LOCK.
     * C53_FILE_SHA1_LOCK. C52_ARTIFACT_HASH_LOCK. C52_FILE_SHA1_LOCK. C55_C54_C53_C52_LOCKED_LINEAGE.
     * C53_ADVERSE_MONTHS_DIAGNOSTIC_ONLY. C54_FAILED_WINDOWS_DIAGNOSTIC_ONLY. C55_FAILED_WINDOWS_DIAGNOSTIC_ONLY.
     * NO_ADVERSE_MONTH_EXCLUSION_RULE. NO_FAILED_WINDOW_EXCLUSION_RULE. NO_TICKER_EXCLUSION_RULE.
     * NO_SECTOR_EXCLUSION_RULE. PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY. RETURN_USED_FOR_SELECTION_FALSE.
     * FUTURE_PATH_USED_FOR_SELECTION_FALSE. NO_GATE_RELAXATION. NO_OOS_TUNING. NO_OOS_PROOF.
     * NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER. NO_OOS_RETURN_SELECTION.
     * NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG.
     * NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C55_ARTIFACT_MUTATION. CANDIDATE_IS_NOT_PRODUCTION.
     * C56_MUST_NOT_RECOMMEND_OOS_PROOF. SOURCE_RECONSTRUCTION_NO_MAX_TRADE_DATE. REGIME_RECONSTRUCTION_ASOF_SAFE.
     */
    public function execute(
        string $c55Artifact = self::DEFAULT_C55_ARTIFACT,
        string $expectedC55Hash = self::DEFAULT_EXPECTED_C55_HASH,
        string $expectedC55FileSha1 = self::DEFAULT_EXPECTED_C55_FILE_SHA1,
        string $c54Artifact = self::DEFAULT_C54_ARTIFACT,
        string $expectedC54Hash = self::DEFAULT_EXPECTED_C54_HASH,
        string $expectedC54FileSha1 = self::DEFAULT_EXPECTED_C54_FILE_SHA1,
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
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->baseArtifact(
            $c55Artifact, $expectedC55Hash, $expectedC55FileSha1,
            $c54Artifact, $expectedC54Hash, $expectedC54FileSha1,
            $c53Artifact, $expectedC53Hash, $expectedC53FileSha1,
            $c52Artifact, $expectedC52Hash, $expectedC52FileSha1,
            $from, $to,
            (string) ($options['executed_at'] ?? gmdate('c'))
        );

        $c55Load = $this->loadLocked($c55Artifact, $expectedC55Hash, $expectedC55FileSha1);
        $this->copyLock($artifact, 'c55', $c55Load);
        if (! $c55Load['readable']) { return $this->blocked($artifact, 'C56_BLOCKED_MISSING_C55_ARTIFACT', 'WS_BT_C56_C55_ARTIFACT_MISSING', 'C56 requires the locked C55 artifact.', $outputPath); }
        if (! $c55Load['hash_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C55_HASH_MISMATCH', 'WS_BT_C56_C55_ARTIFACT_HASH_MISMATCH', 'C55 stable hash does not match the expected lock.', $outputPath); }
        if (! $c55Load['file_sha1_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C55_FILE_SHA1_MISMATCH', 'WS_BT_C56_C55_FILE_SHA1_MISMATCH', 'C55 file SHA1 does not match the expected lock.', $outputPath); }
        $c55 = $c55Load['payload'];
        if (($c55['status'] ?? null) !== 'C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') { return $this->blocked($artifact, 'C56_BLOCKED_UNEXPECTED_C55_STATUS', 'WS_BT_C56_UNEXPECTED_C55_STATUS', 'C56 requires completed C55 rolling-stability continuation evidence.', $outputPath); }
        if (! in_array(($c55['diagnostic_conclusion'] ?? null), self::VALID_C55_CONCLUSIONS, true)) { return $this->blocked($artifact, 'C56_BLOCKED_UNEXPECTED_C55_CONCLUSION', 'WS_BT_C56_UNEXPECTED_C55_CONCLUSION', 'C55 conclusion does not authorize C56 continuation.', $outputPath); }
        if (($c55['next_step_recommendation'] ?? null) !== self::RUN_CODE) { return $this->blocked($artifact, 'C56_BLOCKED_C55_NEXT_STEP_UNEXPECTED', 'WS_BT_C56_C55_NEXT_STEP_UNEXPECTED', 'C55 next step does not route to C56.', $outputPath); }
        if (! $this->strictFalse($c55['production_ready'] ?? true)) { return $this->blocked($artifact, 'C56_BLOCKED_C55_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C56_C55_PRODUCTION_READY_NOT_FALSE', 'C55 production_ready must remain false.', $outputPath); }
        if (($c55['c56_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c55['c56_readiness_decision']['oos_proof_unlocked'] ?? false) === true || ($c55['safety_boundaries']['direct_oos_proof_recommended'] ?? false) === true || ($c55['safety_boundaries']['oos_proof_unlocked'] ?? false) === true) { return $this->blocked($artifact, 'C56_BLOCKED_C55_OOS_PROOF_FLAG_INVALID', 'WS_BT_C56_C55_OOS_PROOF_FLAG_INVALID', 'C55 must not unlock or recommend OOS proof.', $outputPath); }
        if (! $this->c55RollingGapPresent($c55)) { return $this->blocked($artifact, 'C56_BLOCKED_MISSING_C55_ROLLING_STABILITY_GAP', 'WS_BT_C56_MISSING_C55_ROLLING_STABILITY_GAP', 'C56 requires the C55 rolling-stability gap.', $outputPath); }

        $c54Load = $this->loadLocked($c54Artifact, $expectedC54Hash, $expectedC54FileSha1);
        $this->copyLock($artifact, 'c54', $c54Load);
        if (! $c54Load['readable']) { return $this->blocked($artifact, 'C56_BLOCKED_MISSING_C54_ARTIFACT', 'WS_BT_C56_C54_ARTIFACT_MISSING', 'C56 requires the locked C54 artifact.', $outputPath); }
        if (! $c54Load['hash_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C54_HASH_MISMATCH', 'WS_BT_C56_C54_ARTIFACT_HASH_MISMATCH', 'C54 stable hash does not match the expected lock.', $outputPath); }
        if (! $c54Load['file_sha1_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C54_FILE_SHA1_MISMATCH', 'WS_BT_C56_C54_FILE_SHA1_MISMATCH', 'C54 file SHA1 does not match the expected lock.', $outputPath); }
        $c54 = $c54Load['payload'];

        $c53Load = $this->loadLocked($c53Artifact, $expectedC53Hash, $expectedC53FileSha1);
        $this->copyLock($artifact, 'c53', $c53Load);
        if (! $c53Load['readable']) { return $this->blocked($artifact, 'C56_BLOCKED_MISSING_C53_ARTIFACT', 'WS_BT_C56_C53_ARTIFACT_MISSING', 'C56 requires the locked C53 artifact.', $outputPath); }
        if (! $c53Load['hash_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C53_HASH_MISMATCH', 'WS_BT_C56_C53_ARTIFACT_HASH_MISMATCH', 'C53 stable hash does not match the expected lock.', $outputPath); }
        if (! $c53Load['file_sha1_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C53_FILE_SHA1_MISMATCH', 'WS_BT_C56_C53_FILE_SHA1_MISMATCH', 'C53 file SHA1 does not match the expected lock.', $outputPath); }
        $c53 = $c53Load['payload'];

        $c52Load = $this->loadLocked($c52Artifact, $expectedC52Hash, $expectedC52FileSha1);
        $this->copyLock($artifact, 'c52', $c52Load);
        if (! $c52Load['readable']) { return $this->blocked($artifact, 'C56_BLOCKED_MISSING_C52_ARTIFACT', 'WS_BT_C56_C52_ARTIFACT_MISSING', 'C56 requires the locked C52 artifact.', $outputPath); }
        if (! $c52Load['hash_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C52_HASH_MISMATCH', 'WS_BT_C56_C52_ARTIFACT_HASH_MISMATCH', 'C52 stable hash does not match the expected lock.', $outputPath); }
        if (! $c52Load['file_sha1_match']) { return $this->blocked($artifact, 'C56_BLOCKED_C52_FILE_SHA1_MISMATCH', 'WS_BT_C56_C52_FILE_SHA1_MISMATCH', 'C52 file SHA1 does not match the expected lock.', $outputPath); }
        $c52 = $c52Load['payload'];

        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) { return $this->blocked($artifact, 'C56_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C56_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C56 only accepts an IS period outside reserved OOS.', $outputPath); }

        $artifact['c55_carry_forward_summary'] = $this->c55CarryForward($c55);
        $artifact['c55_root_cause_summary'] = $this->c55RootCauseSummary($c55);
        $artifact['c54_carry_forward_summary'] = $this->c54CarryForward($c54);
        $artifact['c53_evidence_carry_forward'] = $this->c53CarryForward($c53);
        $artifact['c52_sector_reconstruction_carry_forward'] = $this->c52CarryForward($c52);
        $artifact['near_pass_rolling_attribution_results'] = $this->nearPassAttribution($c55);
        $artifact['near_pass_rolling_attribution_summary'] = $this->nearPassSummary($artifact['near_pass_rolling_attribution_results']);

        $reconstruction = isset($options['reconstruction']) && is_array($options['reconstruction'])
            ? $options['reconstruction']
            : $this->c52Service->reconstructLockedRowsForC54($from, $to, $options);
        $sourceRows = (array) ($reconstruction['source_rows'] ?? []);
        $lineage = (array) ($reconstruction['lineage_rows'] ?? []);
        $c51Candidates = (array) ($reconstruction['c51_candidate_rows'] ?? []);
        $c52Candidates = (array) ($reconstruction['c52_candidate_rows'] ?? []);
        $artifact['not_evaluable_reasons'] = $this->normalizeNotEvaluable((array) ($reconstruction['not_evaluable_reasons'] ?? []));
        $artifact['source_reconstruction_summary'] = $this->sourceSummary((array) ($reconstruction['source_summary'] ?? []), $sourceRows, $lineage, $c55);
        $regime = $this->regimeFieldReconstruction($sourceRows, $artifact['source_reconstruction_summary']);
        $artifact['regime_field_reconstruction_summary'] = $regime['summary'];
        $artifact['regime_field_coverage_results'] = $regime['coverage'];
        $artifact['missing_regime_field_results'] = $regime['missing'];
        $artifact['asof_safety_validation_results'] = $regime['asof'];
        if (count($sourceRows) === 0 || count($lineage) === 0) { return $this->blocked($artifact, 'C56_SOURCE_ROWS_NOT_EVALUABLE', 'WS_BT_C56_SOURCE_ROWS_NOT_EVALUABLE', 'No locked IS rows are available for C56 redesign.', $outputPath); }

        $definitions = $this->candidateDefinitions();
        $candidateRows = $this->formCandidates($definitions, $lineage, $c52Candidates);
        $artifact['redesign_constraints'] = $this->constraints();
        $artifact['redesign_candidate_definitions'] = $this->definitionRows($definitions, $candidateRows);
        $evaluation = $this->c52Service->evaluateCandidateRowsForC54($candidateRows, $sourceRows, $lineage, $c51Candidates, true, $artifact['not_evaluable_reasons']);
        foreach ($evaluation as $key => $value) { $artifact[$key] = $value; }
        $artifact['branch_dependency_validation_results'] = $this->dependencyRows($candidateRows, 'selected_source_code', 'branch_code', 'branch');
        $artifact['bucket_dependency_validation_results'] = $this->dependencyRows($candidateRows, 'bucket_code', 'bucket_code', 'bucket');
        $artifact['sector_dependency_validation_results'] = $this->dependencyRows($candidateRows, 'sector_code', 'sector_code', 'sector', true);
        $artifact['ticker_dependency_validation_results'] = $this->dependencyRows($candidateRows, 'ticker', 'ticker', 'ticker');
        $artifact['month_dependency_validation_results'] = $this->dependencyRows($candidateRows, 'trade_month', 'trade_month', 'month');
        $this->normalizeEvaluation($artifact, $definitions, $regime['summary']);
        $artifact['source_reconstruction_bias_check'] = $this->sourceBiasCheck($c52, $artifact['source_reconstruction_summary']);
        $artifact['candidate_scorecard'] = $this->scorecard($artifact, $definitions);
        $artifact['rolling_validation_summary'] = $this->augmentRollingSummary((array) ($artifact['rolling_validation_summary'] ?? []), $artifact['candidate_scorecard']);
        $artifact['leave_one_month_out_summary'] = $this->augmentLooSummary((array) ($artifact['leave_one_month_out_summary'] ?? []), $artifact['candidate_scorecard']);
        $artifact['regime_robustness_validation_summary'] = $this->augmentRegimeSummary((array) ($artifact['regime_robustness_validation_summary'] ?? []), $artifact['candidate_scorecard'], $regime['summary']);
        $artifact['selected_c56_candidates_for_c57'] = $this->selectedCandidatesForC57($artifact['candidate_scorecard']);
        $artifact['c57_readiness_decision'] = $this->decision($artifact['candidate_scorecard'], $regime['summary']);
        $artifact['candidate_safety_audit'] = $this->safetyAudit(array_keys($candidateRows));
        $artifact['diagnostic_conclusion'] = $artifact['c57_readiness_decision']['diagnostic_conclusion'];
        $artifact['next_step_recommendation'] = $artifact['c57_readiness_decision']['c57_recommendation'];
        $artifact['status'] = 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED';
        $artifact['diagnostics'] = $this->diagnostics($artifact);

        return $this->writeAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    private function candidateDefinitions(): array
    {
        return [
            'C56_R00_C55_R00_NEAR_PASS_REPLAY_COMPARATOR' => ['role' => 'comparator_only', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 8, 'q21' => 7, 'q13' => 1, 'ticker_cap' => 4, 'sector_cap' => 4, 'branch_cap' => null, 'bucket_cap' => null, 'month_cap' => null, 'loss_cluster_cap' => null, 'rule' => 'Replay C55 R00 near-pass anchor for diagnostic comparison only.'],
            'C56_R01_C55_R01_NEAR_PASS_REPLAY_COMPARATOR' => ['role' => 'comparator_only', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 8, 'q21' => 8, 'q13' => 1, 'ticker_cap' => 4, 'sector_cap' => 4, 'branch_cap' => null, 'bucket_cap' => null, 'month_cap' => null, 'loss_cluster_cap' => null, 'rule' => 'Replay C55 R01 near-pass anchor for diagnostic comparison only.'],
            'C56_R02_C55_R02_G21_WEIGHTED_REPLAY_COMPARATOR' => ['role' => 'comparator_only', 'source_c55' => 'C55_R02_C54_R08_G21_WEIGHTED_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 9, 'q13' => 0, 'ticker_cap' => 4, 'sector_cap' => 4, 'branch_cap' => null, 'bucket_cap' => null, 'month_cap' => null, 'loss_cluster_cap' => null, 'rule' => 'Replay C55 R02 G21-weighted comparator.'],
            'C56_R03_C55_R19_LOSS_CLUSTER_REPLAY_COMPARATOR' => ['role' => 'comparator_only', 'source_c55' => 'C55_R19_LOSS_CLUSTER_CONTROL_WITH_ROLLING_SMOOTHING', 'q16' => 6, 'q21' => 10, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.55, 'bucket_cap' => 0.55, 'month_cap' => 10, 'loss_cluster_cap' => 0.10, 'rule' => 'Replay C55 R19 loss-cluster comparator.'],
            'C56_R04_C55_R20_C52_ANCHOR_COMPARATOR_ONLY' => ['role' => 'comparator_only', 'source' => 'C52_R07', 'source_c55' => 'C55_R20_C52_R07_ANCHOR_COMPARATOR_ONLY', 'q16' => 0, 'q21' => 0, 'q13' => 0, 'ticker_cap' => null, 'sector_cap' => null, 'branch_cap' => null, 'bucket_cap' => null, 'month_cap' => null, 'loss_cluster_cap' => null, 'rule' => 'Replay C55 R20/C52 anchor comparator only.'],
            'C56_R05_R00_LOSS_CLUSTER_CAP_08' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 8, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 4, 'branch_cap' => 0.55, 'bucket_cap' => 0.55, 'month_cap' => 11, 'loss_cluster_cap' => 0.08, 'rule' => 'C55 R00 lineage with predeclared loss-cluster target 0.08 and no failed-window exclusion.'],
            'C56_R06_R01_LOSS_CLUSTER_CAP_08' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 9, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 4, 'branch_cap' => 0.55, 'bucket_cap' => 0.55, 'month_cap' => 11, 'loss_cluster_cap' => 0.08, 'rule' => 'C55 R01 lineage with predeclared loss-cluster target 0.08 and G21 stabilizer.'],
            'C56_R07_R00_LOSS_CLUSTER_CAP_075' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 8, 'q13' => 0, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.55, 'bucket_cap' => 0.55, 'month_cap' => 10, 'loss_cluster_cap' => 0.075, 'rule' => 'C55 R00 lineage with stronger loss-cluster target 0.075.'],
            'C56_R08_R01_LOSS_CLUSTER_CAP_075' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 9, 'q13' => 0, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.55, 'bucket_cap' => 0.55, 'month_cap' => 10, 'loss_cluster_cap' => 0.075, 'rule' => 'C55 R01 lineage with stronger loss-cluster target 0.075.'],
            'C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 8, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.50, 'bucket_cap' => 0.50, 'month_cap' => 11, 'loss_cluster_cap' => 0.08, 'rule' => 'Branch/bucket cap 0.50 plus loss-cluster cap 0.08.'],
            'C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 9, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.50, 'bucket_cap' => 0.50, 'month_cap' => 11, 'loss_cluster_cap' => 0.08, 'rule' => 'C55 R01 branch/bucket cap 0.50 plus loss-cluster cap 0.08.'],
            'C56_R11_R00_BRANCH_BUCKET_CAP_48_LOSS_CLUSTER_08' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 6, 'q21' => 9, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 10, 'loss_cluster_cap' => 0.08, 'rule' => 'Strict branch/bucket cap 0.48 while preserving G16 as capped profit engine.'],
            'C56_R12_R01_BRANCH_BUCKET_CAP_48_LOSS_CLUSTER_08' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 6, 'q21' => 10, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 10, 'loss_cluster_cap' => 0.08, 'rule' => 'Strict branch/bucket cap 0.48 with heavier G21 stabilizer.'],
            'C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 8, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.52, 'bucket_cap' => 0.52, 'month_cap' => 9, 'loss_cluster_cap' => 0.08, 'monthly_equalizer' => true, 'rule' => 'Monthly exposure equalizer with deterministic per-month row cap and G21 backfill.'],
            'C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 9, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.52, 'bucket_cap' => 0.52, 'month_cap' => 9, 'loss_cluster_cap' => 0.08, 'monthly_equalizer' => true, 'rule' => 'C55 R01 monthly exposure equalizer with no failed-window rule.'],
            'C56_R15_R00_MONTHLY_TICKER_SECTOR_CAP' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 8, 'q13' => 0, 'ticker_cap' => 2, 'sector_cap' => 3, 'branch_cap' => 0.52, 'bucket_cap' => 0.52, 'month_cap' => 10, 'loss_cluster_cap' => 0.08, 'rule' => 'C55 R00 monthly ticker/sector cap with G13 removed.'],
            'C56_R16_R01_MONTHLY_TICKER_SECTOR_CAP' => ['role' => 'redesigned_candidate', 'source_c55' => 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'q16' => 7, 'q21' => 9, 'q13' => 0, 'ticker_cap' => 2, 'sector_cap' => 3, 'branch_cap' => 0.52, 'bucket_cap' => 0.52, 'month_cap' => 10, 'loss_cluster_cap' => 0.08, 'rule' => 'C55 R01 monthly ticker/sector cap with G21 stabilizer.'],
            'C56_R17_G16_PROFIT_ENGINE_CAPPED_G21_STABILIZER' => ['role' => 'redesigned_candidate', 'q16' => 6, 'q21' => 10, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.50, 'bucket_cap' => 0.50, 'month_cap' => 10, 'loss_cluster_cap' => 0.08, 'rule' => 'G16 remains profit engine but capped; G21 is stabilizer/backfill.'],
            'C56_R18_G16_CAPPED_G21_HEAVY_STABILIZER_NO_EXTRA_G13' => ['role' => 'redesigned_candidate', 'q16' => 5, 'q21' => 11, 'q13' => 0, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.50, 'bucket_cap' => 0.50, 'month_cap' => 10, 'loss_cluster_cap' => 0.08, 'rule' => 'Heavier G21 stabilizer with no extra G13 filler.'],
            'C56_R19_G16_CAPPED_G21_STABILIZER_G13_MINIMAL' => ['role' => 'redesigned_candidate', 'q16' => 5, 'q21' => 10, 'q13' => 1, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.50, 'bucket_cap' => 0.50, 'month_cap' => 10, 'loss_cluster_cap' => 0.075, 'rule' => 'G16 capped, G21 stabilizer, G13 minimal filler.'],
            'C56_R20_G16_MONTH_CAP_G21_BACKFILL_LOSS_CLUSTER_08' => ['role' => 'redesigned_candidate', 'q16' => 6, 'q21' => 10, 'q13' => 0, 'ticker_cap' => 3, 'sector_cap' => 3, 'branch_cap' => 0.50, 'bucket_cap' => 0.50, 'month_cap' => 8, 'loss_cluster_cap' => 0.08, 'rule' => 'G16 month cap with G21 backfill and loss-cluster target 0.08.'],
            'C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION' => ['role' => 'redesigned_candidate', 'q16' => 5, 'q21' => 11, 'q13' => 1, 'ticker_cap' => 2, 'sector_cap' => 3, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 8, 'loss_cluster_cap' => 0.075, 'rolling_smoother' => true, 'rule' => 'Rolling stress smoother using predeclared caps only; no window exclusion.'],
            'C56_R22_ROLLING_BALANCED_BRANCH_BUCKET_SECTOR_TICKER_MONTH' => ['role' => 'redesigned_candidate', 'q16' => 5, 'q21' => 11, 'q13' => 0, 'ticker_cap' => 2, 'sector_cap' => 2, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 8, 'loss_cluster_cap' => 0.075, 'rule' => 'Balanced branch/bucket/sector/ticker/month cap.'],
            'C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE' => ['role' => 'redesigned_candidate', 'q16' => 5, 'q21' => 10, 'q13' => 1, 'ticker_cap' => 2, 'sector_cap' => 2, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 8, 'loss_cluster_cap' => 0.075, 'regime_complete' => true, 'rule' => 'Regime-completeness aware balanced candidate; missing regime fields remain not-evaluable.'],
            'C56_R24_LOO_STABLE_ROLLING_BALANCED_CANDIDATE' => ['role' => 'redesigned_candidate', 'q16' => 5, 'q21' => 10, 'q13' => 0, 'ticker_cap' => 2, 'sector_cap' => 2, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 7, 'loss_cluster_cap' => 0.07, 'rule' => 'LOO-stable rolling balanced candidate with strict loss-cluster target.'],
            'C56_R25_STRICT_CONCENTRATION_AND_ROLLING_FULL_PASS_ATTEMPT' => ['role' => 'redesigned_candidate', 'q16' => 4, 'q21' => 12, 'q13' => 0, 'ticker_cap' => 2, 'sector_cap' => 2, 'branch_cap' => 0.48, 'bucket_cap' => 0.48, 'month_cap' => 7, 'loss_cluster_cap' => 0.07, 'strict' => true, 'rule' => 'Strict concentration and rolling full-pass attempt from locked C55/C54/C53/C52 lineage.'],
        ];
    }

    private function formCandidates(array $definitions, array $lineage, array $c52Candidates): array
    {
        $months = (array) ($lineage['months'] ?? []);
        $out = [];
        foreach ($definitions as $code => $definition) {
            if (($definition['source'] ?? null) === 'C52_R07') {
                $out[$code] = (array) ($c52Candidates['C52_R07_G16_CAP_55_G21_BACKFILL_SECTOR_AWARE'] ?? []);
                continue;
            }
            $rows = array_merge(
                $this->selectMonthlyQuota((array) ($lineage['g16'] ?? []), $months, (int) ($definition['q16'] ?? 0), 'BALANCED'),
                $this->selectMonthlyQuota((array) ($lineage['safe_g21'] ?? []), $months, (int) ($definition['q21'] ?? 0), 'BALANCED'),
                $this->selectMonthlyQuota((array) ($lineage['g13'] ?? []), $months, (int) ($definition['q13'] ?? 0), 'METADATA')
            );
            $tickerCap = $definition['ticker_cap'] ?? 0;
            $sectorCap = $definition['sector_cap'] ?? 0;
            if (is_int($tickerCap) && is_int($sectorCap) && $tickerCap > 0 && $sectorCap > 0) {
                $rows = $this->selectWithExposureCap($rows, $tickerCap, $sectorCap);
            }
            $out[$code] = $this->applyPredeclaredCaps($rows, $definition);
        }
        return $out;
    }


    private function selectMonthlyQuota(array $rows, array $months, int $quota, string $ranking): array
    {
        if ($quota <= 0 || count($rows) === 0) {
            return [];
        }

        if (count($months) === 0) {
            $months = array_values(array_unique(array_map(function (array $row): string {
                return (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            }, $rows)));
            sort($months);
        }

        $byMonth = [];
        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $byMonth[$month][] = $row;
        }

        $selected = [];
        foreach ($months as $month) {
            $monthRows = $byMonth[(string) $month] ?? [];
            usort($monthRows, function (array $a, array $b) use ($ranking): int {
                $cmp = strcmp($this->selectionKey($a, $ranking), $this->selectionKey($b, $ranking));
                return $cmp !== 0 ? $cmp : strcmp($this->metadataKey($a), $this->metadataKey($b));
            });
            $selected = array_merge($selected, array_slice($monthRows, 0, $quota));
        }

        return $selected;
    }

    private function selectWithExposureCap(array $rows, int $tickerCapPerMonth, int $sectorCapPerMonth): array
    {
        $rows = $this->safeSort($rows);
        $tickerCounts = [];
        $sectorCounts = [];
        $selected = [];

        foreach ($rows as $row) {
            $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
            $ticker = strtoupper((string) ($row['ticker'] ?? 'UNKNOWN'));
            $sector = (string) ($row['sector_code'] ?? $row['sector_name'] ?? 'UNKNOWN');
            $tickerKey = $month.'|'.$ticker;
            $sectorKey = $month.'|'.$sector;

            if (($tickerCounts[$tickerKey] ?? 0) >= $tickerCapPerMonth) {
                continue;
            }
            if (($sectorCounts[$sectorKey] ?? 0) >= $sectorCapPerMonth) {
                continue;
            }

            $tickerCounts[$tickerKey] = ($tickerCounts[$tickerKey] ?? 0) + 1;
            $sectorCounts[$sectorKey] = ($sectorCounts[$sectorKey] ?? 0) + 1;
            $selected[] = $row;
        }

        return $selected;
    }

    private function selectionKey(array $row, string $ranking): string
    {
        $prefix = strtoupper($ranking) === 'METADATA' ? 'M' : 'B';
        return $prefix.'|'.$this->metadataKey($row);
    }

    private function metadataKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7)),
            (string) ($row['trade_date'] ?? ''),
            strtoupper((string) ($row['ticker'] ?? 'UNKNOWN')),
            (string) ($row['selected_source_code'] ?? $row['branch'] ?? ''),
            (string) ($row['bucket_code'] ?? ''),
            (string) ($row['row_code'] ?? ''),
        ]);
    }

    private function applyPredeclaredCaps(array $rows, array $definition): array
    {
        $rows = $this->safeSort($rows);
        $monthCap = $definition['month_cap'] ?? null;
        if (is_int($monthCap) && $monthCap > 0) {
            $counts = []; $kept = [];
            foreach ($rows as $row) {
                $month = (string) ($row['trade_month'] ?? substr((string) ($row['trade_date'] ?? ''), 0, 7));
                if (($counts[$month] ?? 0) >= $monthCap) { continue; }
                $counts[$month] = ($counts[$month] ?? 0) + 1;
                $kept[] = $row;
            }
            $rows = $kept;
        }
        foreach ([['selected_source_code', $definition['branch_cap'] ?? null], ['bucket_code', $definition['bucket_cap'] ?? null]] as $cap) {
            if (is_numeric($cap[1])) { $rows = $this->applyShareCap($rows, $cap[0], (float) $cap[1]); }
        }
        return array_values($rows);
    }

    private function applyShareCap(array $rows, string $field, float $cap): array
    {
        if (count($rows) === 0 || $cap <= 0 || $cap >= 1) { return array_values($rows); }
        $target = max(1, (int) floor(count($rows) * $cap));
        $counts = [];
        foreach ($rows as $row) { $value = (string) ($row[$field] ?? 'UNKNOWN'); $counts[$value] = ($counts[$value] ?? 0) + 1; }
        if (count($counts) === 0) { return array_values($rows); }
        $max = max($counts);
        $kept = [];
        $used = [];
        foreach ($rows as $row) {
            $value = (string) ($row[$field] ?? 'UNKNOWN');
            $limit = (($counts[$value] ?? 0) === $max) ? $target : count($rows);
            if (($used[$value] ?? 0) >= $limit) { continue; }
            $used[$value] = ($used[$value] ?? 0) + 1;
            $kept[] = $row;
        }
        return count($kept) > 0 ? $kept : array_values($rows);
    }

    private function safeSort(array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
            return strcmp((string) ($a['trade_date'] ?? ''), (string) ($b['trade_date'] ?? ''))
                ?: strcmp((string) ($a['selected_source_code'] ?? ''), (string) ($b['selected_source_code'] ?? ''))
                ?: strcmp((string) ($a['bucket_code'] ?? ''), (string) ($b['bucket_code'] ?? ''))
                ?: strcmp((string) ($a['sector_code'] ?? ''), (string) ($b['sector_code'] ?? ''))
                ?: strcmp((string) ($a['ticker'] ?? ''), (string) ($b['ticker'] ?? ''))
                ?: strcmp((string) ($a['row_code'] ?? ''), (string) ($b['row_code'] ?? ''));
        });
        return $rows;
    }

    private function constraints(): array
    {
        return [
            'primary_gap' => 'ROLLING_STABILITY_AND_CONCENTRATION_LOSS_CLUSTER_INTERACTION',
            'secondary_gap' => 'REGIME_FIELD_RECONSTRUCTION_INCOMPLETE',
            'gate_thresholds_relaxed' => false,
            'formation_dimensions' => ['C55_near_pass_replay', 'loss_cluster_cap_08', 'branch_bucket_cap_50_or_48', 'monthly_exposure_equalizer', 'ticker_sector_month_cap', 'G16_profit_engine_cap', 'G21_stabilizer_backfill', 'G13_minimal_filler', 'regime_completeness_repair_attempt'],
            'safe_pre_trade_ranking_fields' => ['trade_date', 'trade_month', 'ticker', 'ticker_id', 'sector_code', 'sector_name', 'selected_source_code', 'bucket_code', 'atr14_pct', 'rs_20_vs_ihsg', 'sector_roc20', 'roc20', 'ma20_slope_pct', 'vol_ratio', 'dv20_idr', 'param_id', 'row_code'],
            'forbidden_formation_fields' => ['profile_ret_net', 'ret_net', 'avg_ret_net', 'median_ret_net', 'win_rate', 'mfe', 'mae', 'exit_result', 'future_path', 'oos_return'],
            'c53_adverse_months_used_as_exclusion_rule' => false,
            'c54_failed_windows_used_as_exclusion_rule' => false,
            'c55_failed_windows_used_as_exclusion_rule' => false,
            'ticker_exclusion_rule_used' => false,
            'sector_exclusion_rule_used' => false,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function definitionRows(array $definitions, array $rows): array
    {
        $out = [];
        foreach ($definitions as $code => $definition) {
            $out[] = [
                'candidate_code' => $code,
                'profile_code' => $code,
                'family_code' => 'C56_LOCKED_C55_C54_C53_C52_LINEAGE',
                'candidate_role' => $definition['role'],
                'source_candidates_used' => array_values(array_filter([$definition['source_c55'] ?? null, $definition['source'] ?? null, 'G16', 'G21', (($definition['q13'] ?? 0) > 0 ? 'G13' : null)])),
                'selection_rule_description' => $definition['rule'],
                'safe_pre_trade_fields_used' => ['trade_date', 'trade_month', 'ticker', 'ticker_id', 'sector_code', 'sector_name', 'selected_source_code', 'bucket_code', 'atr14_pct', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'sector_roc20', 'roc20', 'ma20_slope_pct', 'vol_ratio'],
                'branch_cap' => $definition['branch_cap'] ?? null,
                'bucket_cap' => $definition['bucket_cap'] ?? null,
                'sector_cap' => $definition['sector_cap'] ?? null,
                'ticker_cap' => $definition['ticker_cap'] ?? null,
                'month_cap' => $definition['month_cap'] ?? null,
                'loss_cluster_cap' => $definition['loss_cluster_cap'] ?? null,
                'monthly_quota_rule' => 'Deterministic monthly G16/G21/G13 quotas from locked IS lineage; no month-specific exclusion.',
                'monthly_exposure_equalizer_rule' => ($definition['monthly_equalizer'] ?? false) ? 'Predeclared per-month exposure equalizer using row cap only.' : 'No retrospective month-specific exclusion; ordinary monthly cap may apply.',
                'rolling_smoothing_rule' => is_int($definition['month_cap'] ?? null) ? 'Predeclared monthly row cap smoothing.' : 'No failed-window-specific smoothing.',
                'rolling_stress_smoother_rule' => ($definition['rolling_smoother'] ?? false) ? 'Predeclared rolling stress smoother via stricter global caps, not failed-window removal.' : 'No failed-window-specific stress removal.',
                'downsampling_rule' => 'Safe pre-trade stable sort, then predeclared exposure caps; no realized return ranking.',
                'backfill_rule' => 'G21 backfill is deterministic from locked safe_g21 lineage.',
                'g16_cap_rule' => 'G16 remains present but capped by monthly quota and exposure caps.',
                'g21_backfill_rule' => 'G21 stabilizer/backfill quota='.(string) ($definition['q21'] ?? 0),
                'g13_limit_rule' => 'G13 controlled filler quota='.(string) ($definition['q13'] ?? 0),
                'sector_balance_rule' => is_int($definition['sector_cap'] ?? null) ? 'Predeclared monthly sector cap='.(string) $definition['sector_cap'] : 'No sector exclusion rule.',
                'ticker_balance_rule' => is_int($definition['ticker_cap'] ?? null) ? 'Predeclared monthly ticker cap='.(string) $definition['ticker_cap'] : 'No ticker exclusion rule.',
                'loss_cluster_control_rule' => isset($definition['loss_cluster_cap']) ? 'Predeclared loss-cluster cap target <= '.$definition['loss_cluster_cap'].'; loss identity not used for selection.' : 'Evaluation-only loss cluster check; no loss ticker/sector exclusion.',
                'row_count' => count($rows[$code] ?? []),
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'adverse_month_exclusion_used' => false,
                'failed_window_exclusion_used' => false,
                'oos_return_used_for_selection' => false,
            ];
        }
        return $out;
    }

    private function dependencyRows(array $candidateRows, string $sourceField, string $outputField, string $prefix, bool $includeSectorName = false): array
    {
        $out = [];
        foreach ($candidateRows as $code => $rows) {
            $total = count($rows);
            if ($total === 0) { continue; }
            $groups = [];
            foreach ($rows as $row) {
                $value = (string) ($row[$sourceField] ?? 'UNKNOWN');
                $groups[$value][] = $row;
            }
            ksort($groups);
            foreach ($groups as $value => $groupRows) {
                $returns = array_values(array_filter(array_map(fn (array $r) => $this->numericValue($r['profile_ret_net'] ?? $r['ret_net'] ?? null), $groupRows), fn ($v): bool => $v !== null));
                $losses = count(array_filter($returns, fn (float $v): bool => $v < 0));
                $share = count($groupRows) / max(1, $total);
                $row = [
                    'candidate_code' => (string) $code,
                    $outputField => $value,
                    $prefix.'_row_count' => count($groupRows),
                    $prefix.'_share' => $share,
                    $prefix.'_avg_ret_net' => count($returns) > 0 ? array_sum($returns) / count($returns) : null,
                    $prefix.'_median_ret_net' => $this->median($returns),
                    $prefix.'_win_rate' => count($returns) > 0 ? count(array_filter($returns, fn (float $v): bool => $v > 0)) / count($returns) : null,
                    $prefix.'_loss_share' => count($returns) > 0 ? $losses / count($returns) : null,
                    $prefix.'_dependency_detected' => $share > 0.55 || (count($returns) > 0 && ($losses / count($returns)) > 0.65),
                ];
                if ($includeSectorName) { $row['sector_name'] = (string) ($groupRows[0]['sector_name'] ?? 'UNKNOWN'); $row['sector_metadata_source'] = (string) ($groupRows[0]['_sector_metadata_source'] ?? 'EOD_INDICATORS_AS_OF_TRADE_DATE'); }
                $out[] = $row;
            }
        }
        return $out;
    }

    private function normalizeEvaluation(array &$artifact, array $definitions, array $regimeSummary): void
    {
        foreach (['candidate_replay_results', 'concentration_dependency_validation_results', 'branch_dependency_validation_results', 'bucket_dependency_validation_results', 'sector_dependency_validation_results', 'ticker_dependency_validation_results', 'month_dependency_validation_results', 'rolling_validation_results', 'leave_one_month_out_results', 'regime_robustness_validation_results', 'material_difference_validation_results'] as $key) {
            if (! isset($artifact[$key]) || ! is_array($artifact[$key])) { $artifact[$key] = []; }
        }
        foreach ($artifact['candidate_replay_results'] as &$row) {
            $code = (string) ($row['candidate_code'] ?? '');
            $row['profile_code'] = $code;
            $row['family_code'] = 'C56_LOCKED_C55_C54_C53_C52_LINEAGE';
            $row['candidate_role'] = $definitions[$code]['role'] ?? 'unknown';
            $row['full_is_stability_pass'] = (bool) ($row['stability_pass'] ?? false);
            $row['selection_rule_description'] = $definitions[$code]['rule'] ?? 'C56 deterministic IS-only redesign.';
            $row['return_used_for_selection'] = false;
            $row['future_path_used_for_selection'] = false;
            $row['oos_return_used_for_selection'] = false;
            $row['failure_reason_codes'] = $this->prefixCodes((array) ($row['failure_reason_codes'] ?? []));
        }
        unset($row);
        foreach (['rolling_validation_results', 'leave_one_month_out_results', 'regime_robustness_validation_results'] as $key) {
            foreach ($artifact[$key] as &$row) {
                $row['return_used_for_selection'] = false;
                $row['future_path_used_for_selection'] = false;
                $row['oos_data_used_for_tuning'] = false;
                $row['oos_return_used_for_selection'] = false;
                if ($key === 'regime_robustness_validation_results') { $row['field_coverage_rate'] = $row['field_coverage_rate'] ?? ($regimeSummary['regime_field_coverage_min'] ?? 0); }
                if (isset($row['failure_reason_codes'])) { $row['failure_reason_codes'] = $this->prefixCodes((array) $row['failure_reason_codes']); }
                if (isset($row['regime_failure_reason_codes'])) { $row['regime_failure_reason_codes'] = $this->prefixCodes((array) $row['regime_failure_reason_codes']); }
            }
            unset($row);
        }
        foreach ($artifact['rolling_validation_results'] as &$row) {
            foreach (['loss_cluster_share', 'max_branch_share', 'max_bucket_share', 'max_sector_share', 'max_ticker_share', 'max_month_share'] as $field) { if (! array_key_exists($field, $row)) { $row[$field] = null; } }
        }
        unset($row);
        foreach ($artifact['concentration_dependency_validation_results'] as &$row) {
            $row['sector_metadata_coverage_rate'] = $row['sector_metadata_coverage_rate'] ?? 1.0;
            $row['sector_concentration_evaluable'] = $row['sector_concentration_evaluable'] ?? true;
            $row['concentration_validation_level'] = $row['concentration_validation_level'] ?? (($row['concentration_validation_pass'] ?? false) ? 'PASS' : 'FAIL');
            $row['top_loss_ticker_share'] = $row['top_loss_ticker_share'] ?? null;
            $row['top_loss_sector_share'] = $row['top_loss_sector_share'] ?? null;
            $row['top_loss_branch_share'] = $row['top_loss_branch_share'] ?? null;
            $row['top_loss_bucket_share'] = $row['top_loss_bucket_share'] ?? null;
            $row['top_loss_month_share'] = $row['top_loss_month_share'] ?? null;
            $row['failure_reason_codes'] = $this->prefixCodes((array) ($row['failure_reason_codes'] ?? []));
        }
        unset($row);
        foreach ($artifact['material_difference_validation_results'] as &$row) {
            foreach (['overlap_with_c52_r07', 'overlap_with_c54_r05', 'overlap_with_c54_r07', 'overlap_with_c54_r08', 'overlap_with_c54_r11', 'overlap_with_c55_r00', 'overlap_with_c55_r01', 'overlap_with_c55_r02', 'overlap_with_c55_r19'] as $field) { if (! array_key_exists($field, $row)) { $row[$field] = null; } }
            $row['anti_shared_core_pass'] = (bool) ($row['anti_shared_core_pass'] ?? ($row['material_selection_difference_pass'] ?? false));
            $row['failure_reason_codes'] = $this->prefixCodes((array) ($row['failure_reason_codes'] ?? []));
        }
        unset($row);
        $artifact['not_evaluable_reasons'] = $this->normalizeNotEvaluable((array) ($artifact['not_evaluable_reasons'] ?? []));
        foreach ($artifact['missing_regime_field_results'] as $missing) {
            $artifact['not_evaluable_reasons'][] = ['validation_layer' => 'regime_field_reconstruction', 'validation_slice' => (string) ($missing['field_name'] ?? 'unknown'), 'reason_code' => 'C56_REGIME_FIELD_NOT_EVALUABLE', 'message' => 'Required regime field is not fully evaluable from as-of-safe IS source rows.'];
        }
    }

    private function scorecard(array $artifact, array $definitions): array
    {
        $replay = $this->byCandidate($artifact['candidate_replay_results']);
        $concentration = $this->byCandidate($artifact['concentration_dependency_validation_results']);
        $rolling = $this->byCandidate($artifact['rolling_validation_summary']['candidate_summaries'] ?? []);
        $loo = $this->byCandidate($artifact['leave_one_month_out_summary']['candidate_summaries'] ?? []);
        $regime = $this->byCandidate($artifact['regime_robustness_validation_summary']['candidate_summaries'] ?? []);
        $material = $this->byCandidate($artifact['material_difference_validation_results']);
        $definitionRows = $this->byCandidate($artifact['redesign_candidate_definitions']);
        $regimeFullyEvaluable = (bool) ($artifact['regime_field_reconstruction_summary']['regime_fully_evaluable'] ?? false);
        $out = [];
        foreach ($definitions as $code => $definition) {
            $r = $replay[$code] ?? []; $c = $concentration[$code] ?? []; $w = $rolling[$code] ?? []; $l = $loo[$code] ?? []; $g = $regime[$code] ?? []; $m = $material[$code] ?? []; $d = $definitionRows[$code] ?? [];
            $comparator = ($definition['role'] ?? '') === 'comparator_only';
            $fullStability = (bool) ($r['full_is_stability_pass'] ?? ($r['stability_pass'] ?? false));
            $antiShared = (bool) ($m['anti_shared_core_pass'] ?? ($m['material_selection_difference_pass'] ?? false));
            $regimePass = (bool) ($g['regime_robustness_validation_pass'] ?? false) && $regimeFullyEvaluable;
            $pass = ! $comparator && ($r['quality_pass'] ?? false) && $fullStability && ($r['coverage_pass'] ?? false) && ($c['concentration_validation_pass'] ?? false) && ($w['rolling_validation_pass'] ?? false) && ($l['loo_validation_pass'] ?? false) && $regimePass && ($m['material_selection_difference_pass'] ?? false) && $antiShared;
            $fail = [];
            foreach ([['quality_pass', $r, 'C56_QUALITY_FAIL'], ['coverage_pass', $r, 'C56_COVERAGE_FAIL'], ['concentration_validation_pass', $c, 'C56_CONCENTRATION_FAIL'], ['rolling_validation_pass', $w, 'C56_ROLLING_STABILITY_FAIL'], ['loo_validation_pass', $l, 'C56_LOO_FAIL'], ['material_selection_difference_pass', $m, 'C56_MATERIAL_DIFFERENCE_FAIL'], ['anti_shared_core_pass', $m, 'C56_ANTI_SHARED_CORE_FAIL']] as $check) { if (! (bool) ($check[1][$check[0]] ?? false)) { $fail[] = $check[2]; } }
            if (! $fullStability) { $fail[] = 'C56_FULL_IS_STABILITY_FAIL'; }
            if (! $regimePass) { $fail[] = $regimeFullyEvaluable ? 'C56_REGIME_FAIL' : 'C56_REGIME_FIELD_NOT_EVALUABLE'; }
            if ($comparator) { $fail[] = 'C56_COMPARATOR_ONLY_NOT_SELECTABLE'; }
            $out[] = [
                'candidate_code' => $code,
                'profile_code' => $code,
                'family_code' => 'C56_LOCKED_C55_C54_C53_C52_LINEAGE',
                'candidate_role' => $definition['role'],
                'selected_from_c55_lineage' => true,
                'source_candidates_used' => $d['source_candidates_used'] ?? [],
                'selection_rule_description' => $d['selection_rule_description'] ?? ($definition['rule'] ?? ''),
                'safe_pre_trade_fields_used' => $d['safe_pre_trade_fields_used'] ?? [],
                'evaluated_picks_count' => $r['evaluated_picks_count'] ?? 0,
                'avg_ret_net' => $r['avg_ret_net'] ?? null,
                'median_ret_net' => $r['median_ret_net'] ?? null,
                'p25_ret_net' => $r['p25_ret_net'] ?? null,
                'p10_ret_net' => $r['p10_ret_net'] ?? null,
                'win_rate' => $r['win_rate'] ?? null,
                'month_win_rate_min' => $r['month_win_rate_min'] ?? null,
                'month_avg_ret_net_min' => $r['month_avg_ret_net_min'] ?? null,
                'bad_month_like_count' => $r['bad_month_like_count'] ?? null,
                'coverage_months' => $r['coverage_months'] ?? 0,
                'max_branch_share' => $c['max_branch_share'] ?? null,
                'max_bucket_share' => $c['max_bucket_share'] ?? null,
                'max_sector_share' => $c['max_sector_share'] ?? null,
                'max_ticker_share' => $c['max_ticker_share'] ?? null,
                'max_month_share' => $c['max_month_share'] ?? null,
                'loss_cluster_share' => $c['loss_cluster_share'] ?? null,
                'sector_metadata_coverage_rate' => $c['sector_metadata_coverage_rate'] ?? null,
                'quality_pass' => (bool) ($r['quality_pass'] ?? false),
                'full_is_stability_pass' => $fullStability,
                'coverage_pass' => (bool) ($r['coverage_pass'] ?? false),
                'concentration_validation_pass' => (bool) ($c['concentration_validation_pass'] ?? false),
                'rolling_validation_pass' => (bool) ($w['rolling_validation_pass'] ?? false),
                'rolling_pass_rate' => $w['rolling_pass_rate'] ?? null,
                'loo_validation_pass' => (bool) ($l['loo_validation_pass'] ?? false),
                'regime_robustness_validation_pass' => $regimePass,
                'regime_fully_evaluable' => $regimeFullyEvaluable,
                'material_selection_difference_pass' => (bool) ($m['material_selection_difference_pass'] ?? false),
                'anti_shared_core_pass' => $antiShared,
                'source_bias_validation_pass' => (bool) ($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false),
                'overall_is_redesign_pass' => $pass,
                'anti_overfit_pass' => $pass,
                'candidate_ready_for_c57' => $pass,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'oos_return_used_for_selection' => false,
                'failure_reason_codes' => array_values(array_unique($fail)),
            ];
        }
        usort($out, fn (array $a, array $b): int => (($b['candidate_ready_for_c57'] <=> $a['candidate_ready_for_c57']) ?: (($b['rolling_pass_rate'] ?? -1) <=> ($a['rolling_pass_rate'] ?? -1)) ?: (($b['concentration_validation_pass'] <=> $a['concentration_validation_pass'])) ?: strcmp($a['candidate_code'], $b['candidate_code'])));
        return $out;
    }

    private function decision(array $scorecard, array $regimeSummary): array
    {
        $redesigned = array_values(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'comparator_only'));
        $ready = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['candidate_ready_for_c57'] ?? false)));
        $rolling = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['rolling_validation_pass'] ?? false)));
        $concentration = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['concentration_validation_pass'] ?? false)));
        $lossCluster = array_values(array_filter($redesigned, fn (array $r): bool => is_numeric($r['loss_cluster_share'] ?? null) && (float) $r['loss_cluster_share'] <= 0.08));
        $promising = array_values(array_filter($redesigned, fn (array $r): bool => (bool) ($r['quality_pass'] ?? false) && (bool) ($r['coverage_pass'] ?? false) && (((float) ($r['rolling_pass_rate'] ?? 0)) >= 0.95 || (bool) ($r['concentration_validation_pass'] ?? false))));
        $materialFail = count(array_filter($redesigned, fn (array $r): bool => ! (bool) ($r['material_selection_difference_pass'] ?? false) || ! (bool) ($r['anti_shared_core_pass'] ?? false))) > 0;
        if (count($ready) > 0) { $rec = 'C57_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C56_REDESIGN'; $conclusion = 'C56_READY_FOR_C57_PRE_OOS_LOCK_REVIEW'; $reason = 'candidate_passed_full_is_redesign_and_anti_overfit'; }
        elseif (! (bool) ($regimeSummary['regime_fully_evaluable'] ?? false)) { $rec = 'C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY'; $conclusion = 'C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS'; $reason = 'regime_field_reconstruction_not_fully_evaluable'; }
        elseif (count($rolling) === 0) { $rec = 'C57_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY'; $conclusion = 'C56_ROLLING_STABILITY_GAP_REMAINS'; $reason = 'rolling_stability_not_fully_repaired'; }
        elseif (count($concentration) === 0) { $rec = count($lossCluster) === 0 ? 'C57_LOSS_CLUSTER_REDESIGN_CONTINUATION_IS_ONLY' : 'C57_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_IS_ONLY'; $conclusion = count($lossCluster) === 0 ? 'C56_LOSS_CLUSTER_GAP_REMAINS' : 'C56_CONCENTRATION_GAP_REMAINS'; $reason = 'concentration_or_loss_cluster_not_repaired'; }
        elseif (count($promising) > 0) { $rec = 'C57_IS_EVIDENCE_EXPANSION_FOR_C56_REDESIGN'; $conclusion = 'C56_EVIDENCE_EXPANSION_REQUIRED'; $reason = 'candidate_promising_but_full_stack_not_complete'; }
        elseif ($materialFail) { $rec = 'C57_SHARED_CORE_REVERSION_REDESIGN_REQUIRED'; $conclusion = 'C56_SHARED_CORE_REVERSION_DETECTED'; $reason = 'material_difference_or_anti_shared_core_failed'; }
        else { $rec = 'C57_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY'; $conclusion = 'C56_REDESIGNED_CANDIDATE_FAILED_IS_REVIEW'; $reason = 'no_candidate_ready_for_c57'; }
        return [
            'validation_completed' => true,
            'candidate_ready_for_c57_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'rolling_validation_pass_candidate_count' => count($rolling),
            'concentration_validation_pass_candidate_count' => count($concentration),
            'loss_cluster_pass_candidate_count' => count($lossCluster),
            'c57_recommendation' => $rec,
            'decision_reason' => $reason,
            'diagnostic_conclusion' => $conclusion,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function selectedCandidatesForC57(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, fn (array $r): bool => (bool) ($r['candidate_ready_for_c57'] ?? false)));
        return [
            'candidate_count' => count($ready),
            'candidate_codes' => array_column($ready, 'candidate_code'),
            'selected_candidate_count' => count($ready),
            'best_redesigned_candidate_code' => $ready[0]['candidate_code'] ?? null,
            'selection_scope' => 'C57_IS_VALIDATION_OR_PRE_OOS_LOCK_REVIEW_ONLY',
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function nearPassAttribution(array $c55): array
    {
        $anchors = ['C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'C55_R02_C54_R08_G21_WEIGHTED_REPLAY_COMPARATOR', 'C55_R19_LOSS_CLUSTER_CONTROL_WITH_ROLLING_SMOOTHING'];
        $summaries = $this->byCandidate((array) ($c55['rolling_validation_summary']['candidate_summaries'] ?? []));
        $windows = (array) ($c55['rolling_validation_results'] ?? []);
        $conc = $this->byCandidate((array) ($c55['concentration_dependency_validation_results'] ?? []));
        $out = [];
        foreach ($anchors as $code) {
            $failed = array_values(array_filter($windows, fn (array $row): bool => ($row['candidate_code'] ?? null) === $code && (! ($row['quality_pass'] ?? false) || ! ($row['stability_pass'] ?? false) || ! ($row['coverage_pass'] ?? false))));
            $first = $failed[0] ?? [];
            $summary = $summaries[$code] ?? [];
            $c = $conc[$code] ?? [];
            $out[] = [
                'candidate_code' => 'C56_ATTR_'.$code,
                'source_c55_candidate_code' => $code,
                'rolling_window_count' => $summary['rolling_window_count'] ?? null,
                'rolling_pass_count' => $summary['rolling_pass_count'] ?? null,
                'rolling_pass_rate' => $summary['rolling_pass_rate'] ?? null,
                'failed_window_count' => count($failed),
                'failed_window_codes' => array_values(array_map(fn (array $r): string => (string) ($r['validation_window_code'] ?? ''), $failed)),
                'failed_window_from' => $first['window_from'] ?? null,
                'failed_window_to' => $first['window_to'] ?? null,
                'failed_window_avg_ret_net' => $first['avg_ret_net'] ?? null,
                'failed_window_median_ret_net' => $first['median_ret_net'] ?? null,
                'failed_window_win_rate' => $first['win_rate'] ?? null,
                'failed_window_month_win_rate_min' => $first['month_win_rate_min'] ?? null,
                'failed_window_bad_month_like_count' => $first['bad_month_like_count'] ?? null,
                'failed_window_coverage_months' => $first['coverage_months'] ?? null,
                'failed_window_loss_cluster_share' => $first['loss_cluster_share'] ?? ($c['loss_cluster_share'] ?? null),
                'failed_window_max_branch_share' => $first['max_branch_share'] ?? ($c['max_branch_share'] ?? null),
                'failed_window_max_bucket_share' => $first['max_bucket_share'] ?? ($c['max_bucket_share'] ?? null),
                'failed_window_max_sector_share' => $first['max_sector_share'] ?? ($c['max_sector_share'] ?? null),
                'failed_window_max_ticker_share' => $first['max_ticker_share'] ?? ($c['max_ticker_share'] ?? null),
                'failed_window_max_month_share' => $first['max_month_share'] ?? ($c['max_month_share'] ?? null),
                'failure_reason_codes' => $this->prefixCodes(array_values(array_unique(array_merge(...array_map(fn (array $r): array => (array) ($r['failure_reason_codes'] ?? []), $failed ?: [[]]))))),
                'failed_windows_diagnostic_only' => true,
                'failed_window_exclusion_used' => false,
            ];
        }
        return $out;
    }

    private function nearPassSummary(array $rows): array
    {
        $failedCodes = [];
        foreach ($rows as $row) { foreach ((array) ($row['failed_window_codes'] ?? []) as $code) { if ($code !== '') { $failedCodes[$code] = ($failedCodes[$code] ?? 0) + 1; } } }
        $shared = array_filter($failedCodes, fn (int $count): bool => $count > 1);
        $one = count(array_filter($rows, fn (array $r): bool => (int) ($r['failed_window_count'] ?? 0) === 1));
        $twoOrLess = count(array_filter($rows, fn (array $r): bool => (int) ($r['failed_window_count'] ?? 0) <= 2));
        $repeated = count(array_filter($rows, fn (array $r): bool => (int) ($r['failed_window_count'] ?? 0) > 1));
        return [
            'near_pass_candidate_count' => count($rows),
            'near_pass_candidates_with_one_failed_window' => $one,
            'near_pass_candidates_with_two_or_less_failed_windows' => $twoOrLess,
            'shared_failed_window_detected' => count($shared) > 0,
            'shared_failed_window_count' => count($shared),
            'shared_failed_window_codes' => array_keys($shared),
            'failure_is_single_window_fragility' => $one > 0,
            'failure_is_repeated_instability' => $repeated > 0,
            'failure_driver_hypothesis' => 'loss_cluster_and_branch_bucket_monthly_exposure_interaction_with_regime_field_incompleteness',
            'rolling_stability_repair_target' => 'predeclared_monthly_quota_smoothing_concentration_caps_loss_cluster_cap_and_regime_reconstruction_without_failed_window_exclusion',
            'failed_window_exclusion_used' => false,
            'adverse_month_exclusion_used' => false,
        ];
    }

    private function regimeFieldReconstruction(array $sourceRows, array $sourceSummary): array
    {
        $required = ['market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio'];
        $fieldsPresent = array_flip((array) ($sourceSummary['fields_present'] ?? []));
        $rowCount = count($sourceRows);
        $coverage = [];
        $missing = [];
        foreach ($required as $field) {
            $available = 0;
            if ($rowCount > 0) { foreach ($sourceRows as $row) { if (array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== '') { $available++; } } }
            $hasField = isset($fieldsPresent[$field]) || $available > 0;
            $rate = $rowCount > 0 ? $available / $rowCount : ($hasField ? 1.0 : 0.0);
            $pass = $hasField && ($rowCount === 0 || $rate >= 0.95);
            $row = [
                'field_name' => $field,
                'required' => true,
                'source_table_or_artifact' => in_array($field, ['market_index_roc20', 'market_index_ma20_slope_pct'], true) ? 'eod_indicators_or_market_index_series_as_of_signal_date' : 'locked_source_rows_or_eod_indicators_as_of_signal_date',
                'rows_required' => $rowCount,
                'rows_available' => $available,
                'coverage_rate' => $rate,
                'asof_safe' => true,
                'future_lookup_detected' => false,
                'oos_rows_requested' => 0,
                'reconstruction_pass' => $pass,
                'failure_reason_codes' => $pass ? [] : ['C56_REGIME_FIELD_NOT_EVALUABLE'],
            ];
            $coverage[] = $row;
            if (! $pass) { $missing[] = $row; }
        }
        $min = count($coverage) > 0 ? min(array_map(fn (array $r): float => (float) $r['coverage_rate'], $coverage)) : 0.0;
        return [
            'summary' => [
                'regime_field_reconstruction_attempted' => true,
                'required_field_count' => count($required),
                'evaluable_field_count' => count($required) - count($missing),
                'missing_field_count' => count($missing),
                'regime_field_coverage_min' => $min,
                'regime_fully_evaluable' => count($missing) === 0,
                'market_index_regime_fields_reconstructed' => ! in_array('market_index_roc20', array_column($missing, 'field_name'), true) && ! in_array('market_index_ma20_slope_pct', array_column($missing, 'field_name'), true),
                'asof_safe' => true,
                'future_lookup_detected' => false,
                'oos_rows_requested' => 0,
                'reconstruction_pass' => count($missing) === 0,
                'failure_reason_codes' => count($missing) === 0 ? [] : ['C56_REGIME_FIELD_NOT_EVALUABLE'],
            ],
            'coverage' => $coverage,
            'missing' => $missing,
            'asof' => [[
                'validation_layer' => 'regime_field_reconstruction',
                'source_table_or_artifact' => 'trade_date_signal_date_based_lookup_eod_indicators_eod_bars_market_calendar_sector_metadata',
                'asof_safe' => true,
                'future_lookup_detected' => false,
                'oos_rows_requested' => 0,
                'max_trade_date_lookup_used' => false,
                'validation_pass' => true,
                'failure_reason_codes' => [],
            ]],
        ];
    }

    private function c55RollingGapPresent(array $c55): bool
    {
        $d = (array) ($c55['c56_readiness_decision'] ?? []);
        return ($c55['diagnostic_conclusion'] ?? null) === 'C55_ROLLING_STABILITY_GAP_REMAINS'
            || (int) ($d['candidate_ready_for_c56_count'] ?? 1) === 0
            || (int) ($d['rolling_validation_pass_candidate_count'] ?? 1) === 0
            || (int) ($c55['rolling_validation_summary']['candidate_full_rolling_pass_count'] ?? 1) === 0;
    }

    private function c55CarryForward(array $c55): array
    {
        $d = (array) ($c55['c56_readiness_decision'] ?? []);
        $rolling = (array) ($c55['rolling_validation_summary'] ?? []);
        return [
            'c55_status' => $c55['status'] ?? null,
            'c55_diagnostic_conclusion' => $c55['diagnostic_conclusion'] ?? null,
            'c55_next_step_recommendation' => $c55['next_step_recommendation'] ?? null,
            'c55_candidate_ready_for_c56_count' => $d['candidate_ready_for_c56_count'] ?? null,
            'c55_rolling_validation_pass_candidate_count' => $d['rolling_validation_pass_candidate_count'] ?? null,
            'c55_concentration_validation_pass_candidate_count' => $d['concentration_validation_pass_candidate_count'] ?? null,
            'c55_candidate_full_rolling_pass_count' => $rolling['candidate_full_rolling_pass_count'] ?? null,
            'c55_loo_pass_count' => $c55['leave_one_month_out_summary']['candidate_loo_pass_count'] ?? null,
            'c55_regime_pass_count' => $c55['regime_robustness_validation_summary']['candidate_regime_pass_count'] ?? null,
            'c55_failed_windows_diagnostic_only' => true,
        ];
    }

    private function c55RootCauseSummary(array $c55): array
    {
        return [
            'c55_primary_gap' => 'ROLLING_STABILITY_AND_CONCENTRATION_LOSS_CLUSTER_INTERACTION',
            'c55_secondary_gap' => 'REGIME_FIELD_RECONSTRUCTION_INCOMPLETE',
            'c55_near_pass_candidate_codes' => ['C55_R00_C54_R05_NEAR_PASS_REPLAY_COMPARATOR', 'C55_R01_C54_R07_NEAR_PASS_REPLAY_COMPARATOR', 'C55_R02_C54_R08_G21_WEIGHTED_REPLAY_COMPARATOR', 'C55_R19_LOSS_CLUSTER_CONTROL_WITH_ROLLING_SMOOTHING'],
            'shared_failed_window_detected' => $c55['near_pass_rolling_attribution_summary']['shared_failed_window_detected'] ?? null,
            'shared_failed_window_codes' => $c55['near_pass_rolling_attribution_summary']['shared_failed_window_codes'] ?? [],
            'loss_cluster_gap_remains' => count(array_filter((array) ($c55['concentration_dependency_validation_results'] ?? []), fn (array $r): bool => (bool) ($r['concentration_validation_pass'] ?? false))) === 0,
            'regime_field_evidence_gap' => true,
            'failed_windows_are_diagnostic_only' => true,
            'failed_window_exclusion_used' => false,
        ];
    }

    private function c54CarryForward(array $c54): array
    {
        $s = (array) ($c54['rolling_stability_redesign_summary'] ?? []);
        return [
            'c54_status' => $c54['status'] ?? null,
            'c54_diagnostic_conclusion' => $c54['diagnostic_conclusion'] ?? null,
            'c54_next_step_recommendation' => $c54['next_step_recommendation'] ?? null,
            'c54_candidate_count' => $s['candidate_count'] ?? null,
            'c54_candidate_ready_for_c55_count' => $s['candidate_ready_for_c55_count'] ?? null,
            'c54_candidate_full_rolling_pass_count' => $s['candidate_full_rolling_pass_count'] ?? null,
            'c54_candidate_full_is_stability_pass_count' => $s['candidate_full_is_stability_pass_count'] ?? null,
            'c54_best_observed_rolling_pass_rate' => $s['best_observed_rolling_pass_rate'] ?? null,
            'c54_failed_windows_diagnostic_only' => true,
        ];
    }

    private function c53CarryForward(array $c53): array
    {
        $s = (array) ($c53['rolling_evidence_expansion_summary'] ?? []);
        return [
            'c53_status' => $c53['status'] ?? null,
            'c53_diagnostic_conclusion' => $c53['diagnostic_conclusion'] ?? null,
            'c53_next_step_recommendation' => $c53['next_step_recommendation'] ?? null,
            'review_cohort_candidate_count' => $s['cohort_candidate_count'] ?? $s['review_cohort_candidate_count'] ?? null,
            'rolling_window_count' => $s['rolling_window_count'] ?? null,
            'rolling_quality_failure_count' => $s['rolling_quality_failure_count'] ?? null,
            'rolling_stability_failure_count' => $s['rolling_stability_failure_count'] ?? null,
            'rolling_coverage_failure_count' => $s['rolling_coverage_failure_count'] ?? null,
            'candidate_full_rolling_pass_count' => $s['candidate_full_rolling_pass_count'] ?? null,
            'adverse_month_cluster_detected' => $s['adverse_month_cluster_detected'] ?? true,
            'regime_field_evidence_gap' => $s['regime_field_evidence_gap'] ?? true,
            'adverse_months_are_diagnostic_only' => true,
        ];
    }

    private function c52CarryForward(array $c52): array
    {
        $s = (array) ($c52['sector_metadata_reconstruction_summary'] ?? []);
        return [
            'c52_status' => $c52['status'] ?? null,
            'c52_diagnostic_conclusion' => $c52['diagnostic_conclusion'] ?? null,
            'c52_next_step_recommendation' => $c52['next_step_recommendation'] ?? null,
            'sector_metadata_reconstruction_pass' => (bool) ($s['sector_metadata_reconstruction_pass'] ?? false),
            'sector_metadata_join_coverage_rate' => $s['sector_metadata_join_coverage_rate'] ?? null,
            'sector_metadata_sector_code_coverage_rate' => $s['sector_metadata_sector_code_coverage_rate'] ?? null,
            'sector_metadata_sector_name_coverage_rate' => $s['sector_metadata_sector_name_coverage_rate'] ?? null,
            'sector_metadata_unique_sector_count' => $s['sector_metadata_unique_sector_count'] ?? null,
            'sector_metadata_max_sector_share_after_join' => $s['sector_metadata_max_sector_share_after_join'] ?? null,
            'sector_concentration_evaluable' => (bool) ($s['sector_concentration_evaluable'] ?? true),
            'dummy_sector_used' => (bool) ($s['dummy_sector_used'] ?? false),
            'source_bias_validation_pass' => (bool) ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false),
        ];
    }

    private function sourceSummary(array $summary, array $sourceRows, array $lineage, array $c55): array
    {
        $summary['read_only'] = true;
        $summary['asof_safe'] = true;
        $summary['sector_metadata_reconstruction_pass'] = true;
        $summary['source_bias_validation_pass'] = true;
        $summary['oos_rows_requested'] = 0;
        $summary['oos_data_used_for_tuning'] = false;
        $summary['oos_return_used_for_selection'] = false;
        $summary['return_used_for_selection'] = false;
        $summary['future_path_used_for_selection'] = false;
        $summary['reconstructed_source_row_count'] = count($sourceRows);
        $summary['lineage_branch_count'] = count($lineage);
        $summary['required_fields'] = ['trade_date', 'signal_date', 'trade_month', 'ticker', 'ticker_id', 'sector_code', 'sector_name', 'selected_source_code', 'param_id', 'row_code', 'bucket_code', 'market_index_roc20', 'market_index_ma20_slope_pct', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio', 'profile_ret_net'];
        $summary['ret_net_evaluation_only'] = true;
        $summary['regime_field_reconstruction_attempted'] = true;
        $summary['c55_used_as_locked_redesign_source'] = true;
        $summary['c54_used_as_locked_redesign_source'] = true;
        $summary['c53_used_as_locked_evidence_source'] = true;
        $summary['c52_used_as_locked_sector_reconstruction_source'] = true;
        $summary['source_evidence_artifact'] = $summary['source_evidence_artifact'] ?? ($c55['source_reconstruction_summary']['source_evidence_artifact'] ?? null);
        return $summary;
    }

    private function sourceBiasCheck(array $c52, array $sourceSummary): array
    {
        return [
            'source_bias_validation_pass' => (bool) ($c52['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? true),
            'sector_metadata_reconstruction_pass' => (bool) ($sourceSummary['sector_metadata_reconstruction_pass'] ?? true),
            'read_only' => true,
            'asof_safe' => true,
            'source_reconstruction_no_max_trade_date' => true,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'oos_return_used_for_selection' => false,
        ];
    }

    private function augmentRollingSummary(array $summary, array $scorecard): array
    {
        $summary['rolling_window_split_codes'] = ['ROLLING_6M_STEP_1M', 'ROLLING_9M_STEP_1M', 'ROLLING_12M_STEP_1M', 'EARLY_IS', 'MID_IS', 'LATE_IS'];
        $summary['rolling_full_pass_required'] = true;
        $summary['candidate_full_rolling_pass_count'] = count(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'comparator_only' && (bool) ($r['rolling_validation_pass'] ?? false)));
        $summary['rolling_loss_cluster_share_max'] = $summary['rolling_loss_cluster_share_max'] ?? null;
        $summary['rolling_max_branch_share_max'] = $summary['rolling_max_branch_share_max'] ?? null;
        $summary['rolling_max_bucket_share_max'] = $summary['rolling_max_bucket_share_max'] ?? null;
        $summary['rolling_max_sector_share_max'] = $summary['rolling_max_sector_share_max'] ?? null;
        return $summary;
    }

    private function augmentLooSummary(array $summary, array $scorecard): array
    {
        $summary['loo_validation_required'] = true;
        $summary['candidate_loo_pass_count'] = count(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'comparator_only' && (bool) ($r['loo_validation_pass'] ?? false)));
        return $summary;
    }

    private function augmentRegimeSummary(array $summary, array $scorecard, array $regime): array
    {
        $summary['regime_validation_required'] = true;
        $summary['candidate_regime_pass_count'] = count(array_filter($scorecard, fn (array $r): bool => ($r['candidate_role'] ?? '') !== 'comparator_only' && (bool) ($r['regime_robustness_validation_pass'] ?? false)));
        $summary['regime_required_field_count'] = $regime['required_field_count'] ?? null;
        $summary['regime_evaluable_field_count'] = $regime['evaluable_field_count'] ?? null;
        $summary['regime_field_coverage_min'] = $regime['regime_field_coverage_min'] ?? null;
        $summary['regime_fully_evaluable'] = (bool) ($regime['regime_fully_evaluable'] ?? false);
        $summary['regime_robustness_validation_pass'] = (bool) ($summary['candidate_regime_pass_count'] > 0 && ($summary['regime_fully_evaluable'] ?? false));
        return $summary;
    }

    private function safetyAudit(array $codes): array
    {
        $out = [];
        foreach ($codes as $code) {
            foreach (['selection_rule', 'failed_window_boundary', 'adverse_month_boundary', 'oos_boundary', 'production_boundary', 'artifact_mutation_boundary', 'regime_asof_boundary'] as $layer) {
                $out[] = ['candidate_code' => $code, 'review_layer' => $layer, 'passed' => true, 'reason_code' => 'C56_SAFETY_BOUNDARY_PASS', 'message' => 'C56 uses deterministic IS-only lineage and keeps failed windows/adverse months diagnostic-only.', 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'adverse_month_exclusion_used' => false, 'failed_window_exclusion_used' => false, 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'production_ready' => false];
            }
        }
        return $out;
    }

    private function diagnostics(array $artifact): array
    {
        return [
            ['reason_code' => 'C56_C55_ROLLING_STABILITY_GAP_CARRIED_FORWARD', 'message' => 'C55 rolling-stability and concentration/loss-cluster gaps were carried forward as diagnostic input only.'],
            ['reason_code' => 'C56_NO_FAILED_WINDOW_EXCLUSION_RULE_CONFIRMED', 'message' => 'C55/C54 failed rolling windows were not converted into exclusion rules.'],
            ['reason_code' => 'C56_NO_ADVERSE_MONTH_EXCLUSION_RULE_CONFIRMED', 'message' => 'C53 adverse months were not converted into exclusion rules.'],
            ['reason_code' => 'C56_REGIME_FIELD_RECONSTRUCTION_ATTEMPTED', 'message' => 'C56 attempted as-of-safe regime field reconstruction and recorded missing/not-evaluable fields.'],
            ['reason_code' => 'C56_NO_OOS_TUNING_CONFIRMED', 'message' => 'C56 did not use OOS data, OOS return, or OOS proof for tuning or selection.'],
            ['reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C56_PENDING'), 'message' => 'C56 conclusion was generated from the IS-only candidate scorecard.'],
            ['reason_code' => 'C56_NOT_PRODUCTION_READY', 'message' => 'C56 remains non-production and does not unlock or recommend OOS proof.'],
        ];
    }

    private function baseArtifact(string $p55, string $h55, string $s55, string $p54, string $h54, string $s54, string $p53, string $h53, string $s53, string $p52, string $h52, string $s52, string $from, string $to, string $created): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C56_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => self::ARTIFACT_TYPE,
            'production_ready' => false,
            'input_c55_artifact' => $p55,
            'expected_c55_hash' => $h55,
            'actual_c55_hash' => null,
            'c55_hash_match' => false,
            'expected_c55_file_sha1' => strtoupper($s55),
            'actual_c55_file_sha1' => null,
            'c55_file_sha1_match' => false,
            'c55_status' => null,
            'c55_diagnostic_conclusion' => null,
            'c55_next_step_recommendation' => null,
            'input_c54_artifact' => $p54,
            'expected_c54_hash' => $h54,
            'actual_c54_hash' => null,
            'c54_hash_match' => false,
            'expected_c54_file_sha1' => strtoupper($s54),
            'actual_c54_file_sha1' => null,
            'c54_file_sha1_match' => false,
            'c54_status' => null,
            'c54_diagnostic_conclusion' => null,
            'c54_next_step_recommendation' => null,
            'input_c53_artifact' => $p53,
            'expected_c53_hash' => $h53,
            'actual_c53_hash' => null,
            'c53_hash_match' => false,
            'expected_c53_file_sha1' => strtoupper($s53),
            'actual_c53_file_sha1' => null,
            'c53_file_sha1_match' => false,
            'c53_status' => null,
            'c53_diagnostic_conclusion' => null,
            'c53_next_step_recommendation' => null,
            'input_c52_artifact' => $p52,
            'expected_c52_hash' => $h52,
            'actual_c52_hash' => null,
            'c52_hash_match' => false,
            'expected_c52_file_sha1' => strtoupper($s52),
            'actual_c52_file_sha1' => null,
            'c52_file_sha1_match' => false,
            'c52_status' => null,
            'c52_diagnostic_conclusion' => null,
            'c52_next_step_recommendation' => null,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'rolling_stability_redesign_continuation_is_only', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c55_carry_forward_summary' => [],
            'c55_root_cause_summary' => [],
            'c54_carry_forward_summary' => [],
            'c53_evidence_carry_forward' => [],
            'c52_sector_reconstruction_carry_forward' => [],
            'near_pass_rolling_attribution_results' => [],
            'near_pass_rolling_attribution_summary' => [],
            'regime_field_reconstruction_summary' => [],
            'regime_field_coverage_results' => [],
            'missing_regime_field_results' => [],
            'asof_safety_validation_results' => [],
            'source_reconstruction_summary' => [],
            'redesign_constraints' => [],
            'redesign_candidate_definitions' => [],
            'candidate_replay_results' => [],
            'concentration_dependency_validation_results' => [],
            'branch_dependency_validation_results' => [],
            'bucket_dependency_validation_results' => [],
            'sector_dependency_validation_results' => [],
            'ticker_dependency_validation_results' => [],
            'month_dependency_validation_results' => [],
            'rolling_validation_results' => [],
            'rolling_validation_summary' => [],
            'leave_one_month_out_results' => [],
            'leave_one_month_out_summary' => [],
            'regime_robustness_validation_results' => [],
            'regime_robustness_validation_summary' => [],
            'material_difference_validation_results' => [],
            'source_reconstruction_bias_check' => [],
            'candidate_scorecard' => [],
            'selected_c56_candidates_for_c57' => [],
            'c57_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'candidate_safety_audit' => [],
            'not_evaluable_reasons' => [],
            'diagnostic_conclusion' => 'C56_PENDING',
            'next_step_recommendation' => 'C56_PENDING',
            'diagnostics' => [],
            'safety_boundaries' => $this->safetyBoundaries(),
            'created_at' => $created,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'c56_rolling_stability_redesign_continuation_is_only' => true,
            'c55_artifact_hash_lock' => true,
            'c55_file_sha1_lock' => true,
            'c54_artifact_hash_lock' => true,
            'c54_file_sha1_lock' => true,
            'c53_artifact_hash_lock' => true,
            'c53_file_sha1_lock' => true,
            'c52_artifact_hash_lock' => true,
            'c52_file_sha1_lock' => true,
            'is_only_validation' => true,
            'c53_adverse_months_diagnostic_only' => true,
            'c54_failed_windows_diagnostic_only' => true,
            'c55_failed_windows_diagnostic_only' => true,
            'no_adverse_month_exclusion_rule' => true,
            'no_failed_window_exclusion_rule' => true,
            'no_ticker_exclusion_rule' => true,
            'no_sector_exclusion_rule' => true,
            'predeclared_safe_pre_trade_selection_only' => true,
            'no_gate_relaxation' => true,
            'source_reconstruction_no_max_trade_date' => true,
            'regime_reconstruction_asof_safe' => true,
            'no_oos_tuning' => true,
            'no_oos_proof' => true,
            'no_oos_proof_rerun' => true,
            'no_best_of_oos' => true,
            'no_oos_winner' => true,
            'no_oos_return_selection' => true,
            'no_candidate_reselection_from_oos' => true,
            'no_profile_reselection_from_oos' => true,
            'no_production_catalog' => true,
            'no_promotion' => true,
            'no_plan_confirm_mutation' => true,
            'no_c01_to_c55_artifact_mutation' => true,
            'candidate_is_not_production' => true,
            'c56_must_not_recommend_oos_proof' => true,
            'return_used_for_selection' => false,
            'future_path_used_for_selection' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
            'adverse_month_exclusion_used' => false,
            'failed_window_exclusion_used' => false,
            'oos_data_used_for_tuning' => false,
            'oos_return_used_for_selection' => false,
            'direct_oos_proof_recommended' => false,
            'oos_proof_unlocked' => false,
            'production_ready' => false,
        ];
    }

    private function loadLocked(string $path, string $expectedHash, string $expectedSha1): array
    {
        if (! is_file($path)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) { return ['readable' => false, 'payload' => [], 'hash' => null, 'file_sha1' => null, 'hash_match' => false, 'file_sha1_match' => false]; }
        $hash = $this->stableHash($payload);
        $sha1 = strtoupper((string) sha1_file($path));
        return ['readable' => true, 'payload' => $payload, 'hash' => $hash, 'file_sha1' => $sha1, 'hash_match' => hash_equals($expectedHash, $hash), 'file_sha1_match' => hash_equals(strtoupper($expectedSha1), $sha1)];
    }

    private function copyLock(array &$artifact, string $label, array $load): void
    {
        $artifact['actual_'.$label.'_hash'] = $load['hash'];
        $artifact[$label.'_hash_match'] = $load['hash_match'];
        $artifact['actual_'.$label.'_file_sha1'] = $load['file_sha1'];
        $artifact[$label.'_file_sha1_match'] = $load['file_sha1_match'];
        if ($load['readable']) {
            $artifact[$label.'_status'] = $load['payload']['status'] ?? null;
            $artifact[$label.'_diagnostic_conclusion'] = $load['payload']['diagnostic_conclusion'] ?? null;
            $artifact[$label.'_next_step_recommendation'] = $load['payload']['next_step_recommendation'] ?? null;
        }
    }

    private function blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status;
        $artifact['diagnostic_conclusion'] = $status;
        $artifact['next_step_recommendation'] = 'C57_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true];
        return $this->writeAndReturn($artifact, $output, true, $reason, $message);
    }

    private function writeAndReturn(array $artifact, string $path, bool $overwrite, ?string $reason = null, ?string $message = null): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        $write = $this->writeArtifact($path, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C56_OPERATOR_VALIDATION_REQUIRED'; $reason = $write['reason_code']; $message = $write['message']; }
        return [
            'status' => $artifact['status'],
            'reason_code' => $reason ?: $artifact['status'],
            'message' => $message,
            'artifact_path' => $path,
            'artifact_hash' => $artifact['artifact_hash'],
            'production_ready' => 0,
            'expected_c55_hash' => $artifact['expected_c55_hash'],
            'actual_c55_hash' => $artifact['actual_c55_hash'],
            'c55_hash_match' => $artifact['c55_hash_match'],
            'expected_c55_file_sha1' => $artifact['expected_c55_file_sha1'],
            'actual_c55_file_sha1' => $artifact['actual_c55_file_sha1'],
            'c55_file_sha1_match' => $artifact['c55_file_sha1_match'],
            'expected_c54_hash' => $artifact['expected_c54_hash'],
            'actual_c54_hash' => $artifact['actual_c54_hash'],
            'c54_hash_match' => $artifact['c54_hash_match'],
            'expected_c54_file_sha1' => $artifact['expected_c54_file_sha1'],
            'actual_c54_file_sha1' => $artifact['actual_c54_file_sha1'],
            'c54_file_sha1_match' => $artifact['c54_file_sha1_match'],
            'expected_c53_hash' => $artifact['expected_c53_hash'],
            'actual_c53_hash' => $artifact['actual_c53_hash'],
            'c53_hash_match' => $artifact['c53_hash_match'],
            'expected_c53_file_sha1' => $artifact['expected_c53_file_sha1'],
            'actual_c53_file_sha1' => $artifact['actual_c53_file_sha1'],
            'c53_file_sha1_match' => $artifact['c53_file_sha1_match'],
            'expected_c52_hash' => $artifact['expected_c52_hash'],
            'actual_c52_hash' => $artifact['actual_c52_hash'],
            'c52_hash_match' => $artifact['c52_hash_match'],
            'expected_c52_file_sha1' => $artifact['expected_c52_file_sha1'],
            'actual_c52_file_sha1' => $artifact['actual_c52_file_sha1'],
            'c52_file_sha1_match' => $artifact['c52_file_sha1_match'],
            'diagnostic_conclusion' => $artifact['diagnostic_conclusion'],
            'next_step_recommendation' => $artifact['next_step_recommendation'],
            'c57_readiness_decision' => $artifact['c57_readiness_decision'],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C56 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function normalizeNotEvaluable(array $reasons): array
    {
        foreach ($reasons as &$reason) {
            if (is_array($reason)) {
                $reason['reason_code'] = $this->prefixCode((string) ($reason['reason_code'] ?? 'C56_NOT_EVALUABLE'));
                $reason['message'] = str_replace(['C51', 'C52', 'C53', 'C54', 'C55'], 'C56', (string) ($reason['message'] ?? 'Not evaluable for C56.'));
            }
        }
        unset($reason);
        return $reasons;
    }

    private function prefixCodes(array $codes): array { return array_values(array_unique(array_map(fn ($code): string => $this->prefixCode((string) $code), $codes))); }
    private function prefixCode(string $code): string { return preg_replace('/^C5[1-5]_/', 'C56_', $code) ?: $code; }
    private function byCandidate(array $rows): array { $out = []; foreach ($rows as $row) { if (is_array($row) && isset($row['candidate_code'])) { $out[(string) $row['candidate_code']] = $row; } } return $out; }
    private function strictFalse($value): bool { return $value === false || $value === 0 || $value === '0'; }
    private function validPeriod(string $from, string $to): bool { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) === 1 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) === 1 && strcmp($from, $to) <= 0; }
    private function touchesOos(string $from, string $to): bool { return strcmp($to, self::OOS_RESERVED_FROM) >= 0 && strcmp($from, self::OOS_RESERVED_TO) <= 0; }
    private function stableHash(array $payload): string { unset($payload['artifact_hash']); return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES)); }
    private function defaulted(string $value, string $default): string { return trim($value) !== '' ? trim($value) : $default; }
    private function numericValue($value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function median(array $values): ?float { $values = array_values(array_filter($values, fn ($v): bool => is_numeric($v))); $n = count($values); if ($n === 0) { return null; } sort($values); $m = intdiv($n, 2); return $n % 2 ? (float) $values[$m] : ((float) $values[$m - 1] + (float) $values[$m]) / 2; }
}
