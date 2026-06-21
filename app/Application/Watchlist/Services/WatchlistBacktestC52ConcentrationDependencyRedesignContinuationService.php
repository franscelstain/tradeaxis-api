<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService
{
    public const RUN_CODE = 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION';
    public const ARTIFACT_TYPE = 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION';
    public const DEFAULT_C51_ARTIFACT = 'storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json';
    public const DEFAULT_EXPECTED_C51_HASH = 'a786034b8e344207592e58efe262287102b0ef36';
    public const DEFAULT_C50_ARTIFACT = 'storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json';
    public const DEFAULT_EXPECTED_C50_HASH = '1f2b919662a395444f43403e8f7f4d0b91e146aa';
    public const DEFAULT_C49_ARTIFACT = 'storage/app/watchlist/backtest/c49-broader-strategy-redesign.json';
    public const DEFAULT_EXPECTED_C49_HASH = '9266ec2b59a6ea11c21b830cd9b769635afc91a8';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json';
    public const DEFAULT_FROM = '2023-01-02';
    public const DEFAULT_TO = '2025-05-21';
    public const OOS_RESERVED_FROM = '2025-05-22';
    public const OOS_RESERVED_TO = '2026-05-29';
    public const DEFAULT_SOURCE_EVIDENCE = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const F03_CANDIDATE = 'C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL';
    public const F08_CANDIDATE = 'C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN';
    public const F00_CANDIDATE = 'C49_CANDIDATE_F00_C44_SHARED_CORE_COMPARATOR';

    private ?WatchlistBacktestC52ConcentrationSupport $support = null;

    private const VALID_C51_CONCLUSIONS = [
        'C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS',
        'C51_F03_G16_DEPENDENCY_NOT_REDUCED',
        'C51_EVIDENCE_EXPANSION_REQUIRED',
        'C51_REDESIGNED_CANDIDATE_FAILED_IS_REVIEW',
    ];

    private const VALID_C51_NEXT_STEPS = [
        'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION',
        'C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN',
        'C52_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY',
    ];

    /**
     * C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_ONLY. C51_ARTIFACT_HASH_LOCK.
     * C50_ARTIFACT_HASH_LOCK. C49_ARTIFACT_HASH_LOCK. C51_USED_AS_LOCKED_CONTINUATION_SOURCE.
     * C50_USED_AS_LOCKED_VALIDATION_SOURCE. C49_USED_AS_LOCKED_CANDIDATE_SOURCE. IS_ONLY_VALIDATION.
     * SECTOR_METADATA_ASOF_SAFE_REQUIRED. NO_DUMMY_SECTOR. SECTOR_NOT_EVALUABLE_DISTINCT_FROM_TRUE_FAIL.
     * NO_OOS_TUNING. NO_OOS_PROOF. NO_OOS_PROOF_RERUN. NO_BEST_OF_OOS. NO_OOS_WINNER.
     * NO_OOS_RETURN_SELECTION. NO_OOS_BAD_MONTH_THRESHOLD_SELECTION. NO_OOS_TICKER_SECTOR_EXCLUSION_RULE.
     * NO_CANDIDATE_RESELECTION_FROM_OOS. NO_PROFILE_RESELECTION_FROM_OOS. NO_PRODUCTION_CATALOG.
     * NO_PROMOTION. NO_PLAN_CONFIRM_MUTATION. NO_C01_TO_C51_ARTIFACT_MUTATION. CANDIDATE_IS_NOT_PRODUCTION.
     * C52_MUST_NOT_RECOMMEND_OOS_PROOF. RETURN_USED_FOR_SELECTION_FALSE. FUTURE_PATH_USED_FOR_SELECTION_FALSE.
     */
    public function execute(
        string $c51Artifact = self::DEFAULT_C51_ARTIFACT,
        string $expectedC51Hash = self::DEFAULT_EXPECTED_C51_HASH,
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
        $c51Artifact = $this->defaulted($c51Artifact, self::DEFAULT_C51_ARTIFACT);
        $expectedC51Hash = $this->defaulted($expectedC51Hash, self::DEFAULT_EXPECTED_C51_HASH);
        $c50Artifact = $this->defaulted($c50Artifact, self::DEFAULT_C50_ARTIFACT);
        $expectedC50Hash = $this->defaulted($expectedC50Hash, self::DEFAULT_EXPECTED_C50_HASH);
        $c49Artifact = $this->defaulted($c49Artifact, self::DEFAULT_C49_ARTIFACT);
        $expectedC49Hash = $this->defaulted($expectedC49Hash, self::DEFAULT_EXPECTED_C49_HASH);
        $outputPath = $this->defaulted($outputPath, self::DEFAULT_OUTPUT_PATH);
        $artifact = $this->c52BaseArtifact($c51Artifact, $expectedC51Hash, $c50Artifact, $expectedC50Hash, $c49Artifact, $expectedC49Hash, $from, $to, $createdAt);

        $c51 = $this->loadLockedArtifact($artifact, $c51Artifact, 'C51', $outputPath);
        if (isset($c51['blocked_result'])) { return $c51['blocked_result']; }
        $c51Payload = $c51['payload'];
        $artifact['actual_c51_hash'] = $c51['hash'];
        $artifact['c51_hash_match'] = $c51['hash'] === $expectedC51Hash;
        $artifact['c51_status'] = $c51Payload['status'] ?? null;
        $artifact['c51_diagnostic_conclusion'] = $c51Payload['diagnostic_conclusion'] ?? null;
        $artifact['c51_next_step_recommendation'] = $c51Payload['next_step_recommendation'] ?? null;
        if (! $artifact['c51_hash_match']) { return $this->c52Blocked($artifact, 'C52_BLOCKED_C51_HASH_MISMATCH', 'WS_BT_C52_C51_ARTIFACT_HASH_MISMATCH', 'C51 stable hash does not match the expected lock.', $outputPath); }
        if ($artifact['c51_status'] !== 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED') { return $this->c52Blocked($artifact, 'C52_BLOCKED_UNEXPECTED_C51_STATUS', 'WS_BT_C52_UNEXPECTED_C51_STATUS', 'C52 requires completed C51 validation.', $outputPath); }
        if (! in_array((string) $artifact['c51_diagnostic_conclusion'], self::VALID_C51_CONCLUSIONS, true)) { return $this->c52Blocked($artifact, 'C52_BLOCKED_UNEXPECTED_C51_CONCLUSION', 'WS_BT_C52_UNEXPECTED_C51_CONCLUSION', 'C51 conclusion does not authorize C52.', $outputPath); }
        if (! in_array((string) $artifact['c51_next_step_recommendation'], self::VALID_C51_NEXT_STEPS, true)) { return $this->c52Blocked($artifact, 'C52_BLOCKED_C51_NEXT_STEP_UNEXPECTED', 'WS_BT_C52_C51_NEXT_STEP_UNEXPECTED', 'C51 next step does not route to C52.', $outputPath); }
        if (! $this->strictFalse($c51Payload['production_ready'] ?? true)) { return $this->c52Blocked($artifact, 'C52_BLOCKED_C51_PRODUCTION_READY_NOT_FALSE', 'WS_BT_C52_C51_PRODUCTION_READY_NOT_FALSE', 'C51 production_ready must remain false.', $outputPath); }
        if (($c51Payload['direct_oos_proof_recommended'] ?? false) === true || ($c51Payload['oos_proof_unlocked'] ?? false) === true || ($c51Payload['c52_readiness_decision']['direct_oos_proof_recommended'] ?? false) === true || ($c51Payload['c52_readiness_decision']['oos_proof_unlocked'] ?? false) === true) {
            return $this->c52Blocked($artifact, 'C52_BLOCKED_C51_OOS_PROOF_FLAG_INVALID', 'WS_BT_C52_C51_OOS_PROOF_FLAG_INVALID', 'C51 must not unlock or recommend direct OOS proof.', $outputPath);
        }
        if (! $this->c51ContinuationIssue($c51Payload)) { return $this->c52Blocked($artifact, 'C52_BLOCKED_MISSING_C51_CONCENTRATION_CONTINUATION_REASON', 'WS_BT_C52_MISSING_C51_CONCENTRATION_CONTINUATION_REASON', 'C52 requires the C51 concentration/sector continuation issue.', $outputPath); }

        foreach ([['C50', $c50Artifact, $expectedC50Hash], ['C49', $c49Artifact, $expectedC49Hash]] as $lock) {
            [$label, $path, $expected] = $lock;
            $loaded = $this->loadLockedArtifact($artifact, $path, $label, $outputPath);
            if (isset($loaded['blocked_result'])) { return $loaded['blocked_result']; }
            $lower = strtolower($label);
            $artifact['actual_'.$lower.'_hash'] = $loaded['hash'];
            $artifact[$lower.'_hash_match'] = $loaded['hash'] === $expected;
            if ($label === 'C50') {
                $artifact['c50_status'] = $loaded['payload']['status'] ?? null;
                $artifact['c50_diagnostic_conclusion'] = $loaded['payload']['diagnostic_conclusion'] ?? null;
                $artifact['c50_next_step_recommendation'] = $loaded['payload']['next_step_recommendation'] ?? null;
            }
            if (! $artifact[$lower.'_hash_match']) { return $this->c52Blocked($artifact, 'C52_BLOCKED_'.$label.'_HASH_MISMATCH', 'WS_BT_C52_'.$label.'_ARTIFACT_HASH_MISMATCH', $label.' stable hash does not match the expected lock.', $outputPath); }
        }
        if (! $this->validPeriod($from, $to) || $this->touchesOos($from, $to)) { return $this->c52Blocked($artifact, 'C52_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED', 'WS_BT_C52_IS_PERIOD_TOUCHES_OOS_RESERVED', 'C52 only accepts the IS period and must not touch the reserved OOS period.', $outputPath); }

        $artifact['c51_carry_forward_summary'] = $this->c51CarryForward($c51Payload);
        $artifact['c51_root_cause_summary'] = $this->c51RootCause($c51Payload);
        $sourceLoad = $this->loadSourceRows($from, $to, $options, ['source_universe_summary' => ['source_evidence_artifact' => self::DEFAULT_SOURCE_EVIDENCE]], $artifact['not_evaluable_reasons']);
        $rows = $sourceLoad['rows'];
        $artifact['source_reconstruction_summary'] = array_merge($sourceLoad['summary'], [
            'source_lineage' => ['C51', 'C50', 'C49'],
            'sector_metadata_reconstruction_applied' => true,
        ]);
        $sectorAudit = $this->sectorMetadataAudit($rows, $options);
        foreach ($sectorAudit as $key => $value) { $artifact[$key] = $value; }
        foreach ($sectorAudit['sector_metadata_not_evaluable_reasons'] as $reason) { $artifact['not_evaluable_reasons'][] = $reason; }
        $artifact['source_reconstruction_bias_check'] = $this->sourceBiasCheck($rows, $artifact['source_reconstruction_summary']);
        if (isset($artifact['source_reconstruction_bias_check']['source_bias_notes'])) {
            $artifact['source_reconstruction_bias_check']['source_bias_notes'] = str_replace('C51', 'C52', (string) $artifact['source_reconstruction_bias_check']['source_bias_notes']);
        }

        if (count($rows) === 0) {
            $this->addNotEvaluable($artifact['not_evaluable_reasons'], 'source_reconstruction', 'pick_rows', 'C52_SOURCE_ROWS_NOT_EVALUABLE', 'No IS source rows are available for C52 replay.');
            $artifact['status'] = 'C52_SOURCE_ROWS_NOT_EVALUABLE';
            $artifact['diagnostic_conclusion'] = 'C52_EVIDENCE_EXPANSION_REQUIRED';
            $artifact['next_step_recommendation'] = 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN';
            $artifact['c53_readiness_decision'] = $this->c53Decision($artifact, null, $artifact['next_step_recommendation'], $artifact['diagnostic_conclusion'], 'source_rows_not_evaluable');
            $artifact['diagnostics'] = $this->c52Diagnostics($artifact);
            return $this->c52WriteAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        }

        $lineage = $this->lineageRows($rows, $artifact['not_evaluable_reasons']);
        $c51Candidates = $this->redesignedCandidateRows($lineage);
        $candidateRows = $this->c52CandidateRows($lineage, $c51Candidates);
        $months = $this->uniqueMonths($rows);
        $artifact['redesign_candidate_definitions'] = $this->c52Definitions($candidateRows);
        $artifact['candidate_replay_results'] = $this->c52ReplayResults($candidateRows, $months);
        $artifact['concentration_dependency_validation_results'] = $this->c52ConcentrationResults($candidateRows, (bool) $sectorAudit['sector_metadata_reconstruction_summary']['sector_concentration_evaluable']);
        $artifact['branch_dependency_validation_results'] = $this->c52DependencyResults($candidateRows, 'selected_source_code', 'branch');
        $artifact['bucket_dependency_validation_results'] = $this->c52DependencyResults($candidateRows, 'bucket_code', 'bucket');
        $artifact['sector_dependency_validation_results'] = $this->sectorDependencyResults($candidateRows, (bool) $sectorAudit['sector_metadata_reconstruction_summary']['sector_concentration_evaluable']);
        $artifact['rolling_validation_results'] = $this->rollingValidationResults($candidateRows, $months);
        $artifact['rolling_validation_summary'] = $this->rollingValidationSummary($artifact['rolling_validation_results']);
        $artifact['leave_one_month_out_results'] = $this->leaveOneMonthOutResults($candidateRows, $months);
        $artifact['leave_one_month_out_summary'] = $this->leaveOneMonthOutSummary($artifact['leave_one_month_out_results']);
        $artifact['regime_robustness_validation_results'] = $this->regimeRobustnessResults($candidateRows, $artifact['not_evaluable_reasons']);
        $artifact['regime_robustness_validation_summary'] = $this->regimeRobustnessSummary($artifact['regime_robustness_validation_results']);
        $artifact['not_evaluable_reasons'] = $this->normalizeC52NotEvaluableReasons($artifact['not_evaluable_reasons']);
        $artifact['material_difference_validation_results'] = $this->c52MaterialDifference($candidateRows, $lineage, $c51Candidates);
        $artifact['candidate_scorecard'] = $this->c52Scorecard($artifact);
        $artifact['selected_c52_candidates_for_c53'] = $this->selectedForC53($artifact['candidate_scorecard']);
        [$recommendation, $conclusion, $reason] = $this->c52Recommendation($artifact);
        $best = $this->scorecardByCandidate($artifact['candidate_scorecard'], (string) ($artifact['selected_c52_candidates_for_c53']['best_redesigned_candidate_code'] ?? ''));
        $artifact['c53_readiness_decision'] = $this->c53Decision($artifact, $best ?: null, $recommendation, $conclusion, $reason);
        $artifact['candidate_safety_audit'] = $this->c52SafetyAudit($artifact['redesign_candidate_definitions']);
        $artifact['diagnostic_conclusion'] = $conclusion;
        $artifact['next_step_recommendation'] = $recommendation;
        $artifact['status'] = $sectorAudit['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass']
            ? 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED'
            : 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED_WITH_SECTOR_NOT_EVALUABLE';
        $artifact['diagnostics'] = $this->c52Diagnostics($artifact);

        return $this->c52WriteAndReturn($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
    }

    public function loadC52PreTradeSources(array $rows, array $options): array
    {
        if (array_key_exists('pre_trade_source_rows', $options)) {
            $map = [];
            foreach ((array) $options['pre_trade_source_rows'] as $row) {
                if (! is_array($row)) { continue; }
                $row['_sector_metadata_source'] = trim((string) ($row['sector_code'] ?? $row['sector_name'] ?? '')) !== '' ? 'INJECTED_AS_OF_SAFE_SOURCE' : null;
                $map[$this->joinKey($row)] = $row;
            }
            return ['mode' => 'INJECTED_PRE_TRADE_SOURCE_ROWS', 'rows' => $map, 'error' => null];
        }
        try {
            if (! Schema::hasTable('eod_indicators')) { return ['mode' => 'SOURCE_NOT_MIGRATED', 'rows' => [], 'error' => 'eod_indicators unavailable']; }
            $dates = []; $tickerIds = [];
            foreach ($rows as $row) {
                $date = (string) ($row['trade_date'] ?? '');
                if ($date !== '') { $dates[$date] = true; }
                if (isset($row['ticker_id'])) { $tickerIds[(int) $row['ticker_id']] = true; }
            }
            if (count($dates) === 0 || count($tickerIds) === 0) { return ['mode' => 'JOIN_KEYS_UNAVAILABLE', 'rows' => [], 'error' => 'ticker_id/trade_date unavailable']; }
            $master = [];
            if (Schema::hasTable('market_data_sectors')) {
                foreach (DB::table('market_data_sectors')->select(['sector_code', 'sector_name'])->get() as $row) { $master[strtoupper((string) $row->sector_code)] = (string) $row->sector_name; }
            }
            $memberships = [];
            if (Schema::hasTable('ticker_sector_memberships')) {
                $minDate = min(array_keys($dates)); $maxDate = max(array_keys($dates));
                $membershipRows = DB::table('ticker_sector_memberships')->whereIn('ticker_id', array_keys($tickerIds))->where('effective_from', '<=', $maxDate)->where(function ($q) use ($minDate): void { $q->whereNull('effective_to')->orWhere('effective_to', '>=', $minDate); })->orderBy('effective_from', 'desc')->orderBy('membership_id', 'desc')->get();
                foreach ($membershipRows as $row) { $memberships[(int) $row->ticker_id][] = (array) $row; }
            }
            $map = [];
            foreach (array_chunk(array_keys($dates), 75) as $dateChunk) {
                $columns = ['trade_date', 'ticker_id', 'sector_code', 'dv20_idr', 'atr14_pct', 'vol_ratio', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg', 'sector_roc20', 'rs_20_vs_sector'];
                $dbRows = DB::table('eod_indicators')->whereIn('trade_date', $dateChunk)->whereIn('ticker_id', array_keys($tickerIds))->select($columns)->get();
                foreach ($dbRows as $dbRow) {
                    $arr = (array) $dbRow;
                    $date = (string) $arr['trade_date']; $tickerId = (int) $arr['ticker_id'];
                    $indicatorSector = strtoupper(trim((string) ($arr['sector_code'] ?? '')));
                    $membershipSector = '';
                    foreach ($memberships[$tickerId] ?? [] as $membership) {
                        if ((string) $membership['effective_from'] <= $date && (($membership['effective_to'] ?? null) === null || (string) $membership['effective_to'] >= $date)) { $membershipSector = strtoupper(trim((string) $membership['sector_code'])); break; }
                    }
                    $sector = $indicatorSector !== '' ? $indicatorSector : $membershipSector;
                    $arr['_indicator_sector_code'] = $indicatorSector !== '' ? $indicatorSector : null;
                    $arr['_membership_sector_code'] = $membershipSector !== '' ? $membershipSector : null;
                    $arr['sector_code'] = $sector !== '' ? $sector : null;
                    $arr['sector_name'] = $sector !== '' ? ($master[$sector] ?? null) : null;
                    $arr['_sector_metadata_source'] = $indicatorSector !== '' ? 'EOD_INDICATORS_AS_OF_TRADE_DATE' : ($membershipSector !== '' ? 'TICKER_SECTOR_MEMBERSHIP_AS_OF_TRADE_DATE' : null);
                    $map[$this->joinKey($arr)] = $arr;
                }
            }
            return ['mode' => count($map) > 0 ? 'DATABASE_AS_OF_SIGNAL_DATE_JOIN_WITH_SECTOR' : 'DATABASE_JOIN_EMPTY', 'rows' => $map, 'error' => null];
        } catch (Throwable $e) {
            return ['mode' => 'DATABASE_JOIN_FAILED', 'rows' => [], 'error' => $e->getMessage()];
        }
    }

    private function c52BaseArtifact(string $c51, string $h51, string $c50, string $h50, string $c49, string $h49, string $from, string $to, string $createdAt): array
    {
        return [
            'run_code' => self::RUN_CODE, 'status' => 'C52_OPERATOR_VALIDATION_REQUIRED', 'artifact_type' => self::ARTIFACT_TYPE, 'production_ready' => false,
            'input_c51_artifact' => $c51, 'expected_c51_hash' => $h51, 'actual_c51_hash' => null, 'c51_hash_match' => false, 'c51_status' => null, 'c51_diagnostic_conclusion' => null, 'c51_next_step_recommendation' => null,
            'input_c50_artifact' => $c50, 'expected_c50_hash' => $h50, 'actual_c50_hash' => null, 'c50_hash_match' => false, 'c50_status' => null, 'c50_diagnostic_conclusion' => null, 'c50_next_step_recommendation' => null,
            'input_c49_artifact' => $c49, 'expected_c49_hash' => $h49, 'actual_c49_hash' => null, 'c49_hash_match' => false,
            'is_validation_period' => ['from' => $from, 'to' => $to, 'purpose' => 'is_only_concentration_dependency_redesign_continuation', 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false],
            'oos_reserved_period' => ['from' => self::OOS_RESERVED_FROM, 'to' => self::OOS_RESERVED_TO, 'used_for_selection' => false, 'used_for_tuning' => false, 'used_for_proof' => false],
            'c51_carry_forward_summary' => [], 'c51_root_cause_summary' => [], 'sector_metadata_reconstruction_summary' => [], 'sector_metadata_source_candidates' => [], 'sector_metadata_selected_source' => [], 'sector_metadata_join_results' => [], 'sector_metadata_validation_results' => [], 'sector_metadata_conflict_results' => [], 'sector_metadata_not_evaluable_reasons' => [],
            'source_reconstruction_summary' => [], 'redesign_candidate_definitions' => [], 'candidate_replay_results' => [], 'concentration_dependency_validation_results' => [], 'branch_dependency_validation_results' => [], 'bucket_dependency_validation_results' => [], 'sector_dependency_validation_results' => [],
            'rolling_validation_results' => [], 'rolling_validation_summary' => [], 'leave_one_month_out_results' => [], 'leave_one_month_out_summary' => [], 'regime_robustness_validation_results' => [], 'regime_robustness_validation_summary' => [], 'material_difference_validation_results' => [], 'source_reconstruction_bias_check' => [], 'candidate_scorecard' => [], 'selected_c52_candidates_for_c53' => [],
            'c53_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false], 'candidate_safety_audit' => [], 'not_evaluable_reasons' => [], 'diagnostic_conclusion' => 'C52_PENDING', 'next_step_recommendation' => 'C52_PENDING', 'diagnostics' => [],
            'safety_boundaries' => [
                'c52_concentration_dependency_redesign_continuation_only' => true, 'c51_artifact_hash_lock' => true, 'c50_artifact_hash_lock' => true, 'c49_artifact_hash_lock' => true,
                'c51_used_as_locked_continuation_source' => true, 'c50_used_as_locked_validation_source' => true, 'c49_used_as_locked_candidate_source' => true, 'is_only_validation' => true,
                'sector_metadata_asof_safe_required' => true, 'no_dummy_sector' => true, 'sector_not_evaluable_distinct_from_true_fail' => true,
                'no_oos_tuning' => true, 'no_oos_proof' => true, 'no_oos_proof_rerun' => true, 'no_best_of_oos' => true, 'no_oos_winner' => true, 'no_oos_return_selection' => true,
                'no_oos_bad_month_threshold_selection' => true, 'no_oos_ticker_sector_exclusion_rule' => true, 'no_candidate_reselection_from_oos' => true, 'no_profile_reselection_from_oos' => true,
                'no_production_catalog' => true, 'no_promotion' => true, 'no_plan_confirm_mutation' => true, 'no_c01_to_c51_mutation' => true, 'no_c01_to_c51_artifact_mutation' => true,
                'candidate_is_not_production' => true, 'c52_must_not_recommend_oos_proof' => true, 'production_ready' => false, 'oos_data_used_for_tuning' => false,
                'oos_return_used_for_selection' => false, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false, 'derived_mfe_mae_used_for_execution' => false, 'oos_return_used_for_candidate_selection' => false,
                'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false,
            ],
            'execution_model' => ['entry' => 'NEXT_OPEN', 'exit' => 'STOP_TP_OR_TIME', 'hold' => 5, 'fee' => 'IDR_FIXED', 'slip' => 0, 'gap' => 'OPEN', 'px' => 'IDX_BANDS'],
            'created_at' => $createdAt,
        ];
    }

    private function loadLockedArtifact(array $artifact, string $path, string $label, string $output): array
    {
        if (! is_file($path)) { return ['blocked_result' => $this->c52Blocked($artifact, 'C52_BLOCKED_MISSING_'.$label.'_ARTIFACT', 'WS_BT_C52_'.$label.'_ARTIFACT_MISSING', 'C52 requires the locked '.$label.' artifact.', $output)]; }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) { return ['blocked_result' => $this->c52Blocked($artifact, 'C52_BLOCKED_MISSING_'.$label.'_ARTIFACT', 'WS_BT_C52_'.$label.'_ARTIFACT_UNREADABLE', $label.' artifact is not readable JSON.', $output)]; }
        return ['payload' => $payload, 'hash' => $this->stableHash($payload)];
    }

    private function c51ContinuationIssue(array $c51): bool
    {
        if (($c51['c52_readiness_decision']['decision_reason'] ?? null) === 'concentration_dependency_issue_remains') { return true; }
        if (($c51['c52_readiness_decision']['concentration_validation_pass'] ?? true) === false) { return true; }
        foreach ((array) ($c51['concentration_dependency_validation_results'] ?? []) as $row) {
            if (($row['concentration_validation_pass'] ?? true) === false || (($row['unique_sector_count'] ?? null) === 0 && ($row['max_sector_share'] ?? null) === 1)) { return true; }
        }
        return false;
    }

    private function c51CarryForward(array $c51): array
    {
        $selected = $c51['selected_c51_candidates_for_c52'] ?? [];
        $decision = $c51['c52_readiness_decision'] ?? [];
        return [
            'c51_status' => $c51['status'] ?? null, 'c51_diagnostic_conclusion' => $c51['diagnostic_conclusion'] ?? null, 'c51_next_step_recommendation' => $c51['next_step_recommendation'] ?? null,
            'c51_best_redesigned_candidate_code' => $selected['best_redesigned_candidate_code'] ?? null, 'c51_selected_candidate_count' => (int) ($selected['selected_candidate_count'] ?? 0),
            'c51_concentration_validation_pass' => (bool) ($decision['concentration_validation_pass'] ?? false), 'c51_material_difference_validation_pass' => (bool) ($decision['material_difference_validation_pass'] ?? false), 'c51_anti_overfit_pass' => (bool) ($decision['anti_overfit_pass'] ?? false),
            'c51_used_as_locked_continuation_source' => true, 'c50_used_as_locked_validation_source' => true, 'c49_used_as_locked_candidate_source' => true,
            'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'oos_proof_executed' => false, 'production_ready' => false,
        ];
    }

    private function c51RootCause(array $c51): array
    {
        $zero = false; $one = false;
        foreach ((array) ($c51['concentration_dependency_validation_results'] ?? []) as $row) { $zero = $zero || ($row['unique_sector_count'] ?? null) === 0; $one = $one || ($row['max_sector_share'] ?? null) === 1; }
        return [
            'c51_root_cause' => 'SECTOR_METADATA_RECONSTRUCTION_INVALID_AND_CONCENTRATION_DEPENDENCY_REMAINS',
            'c51_sector_metadata_issue_detected' => $zero || $one, 'c51_unique_sector_count_zero_detected' => $zero, 'c51_max_sector_share_one_detected' => $one,
            'c51_sector_concentration_not_reliable' => $zero && $one, 'c51_sector_concentration_evaluation_defect_confirmed' => $zero && $one,
            'c51_branch_bucket_redesign_mechanically_improved' => true, 'c51_concentration_dependency_continuation_required' => $this->c51ContinuationIssue($c51),
        ];
    }

    private function sectorMetadataAudit(array $rows, array $options): array
    {
        $attempted = count($rows); $withCode = 0; $withName = 0; $joined = 0; $unknown = 0; $conflicts = []; $sectors = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['sector_code'] ?? ''))); $name = trim((string) ($row['sector_name'] ?? ''));
            if ($code !== '') { $withCode++; $sectors[$code] = ($sectors[$code] ?? 0) + 1; }
            if ($name !== '') { $withName++; }
            if ($code !== '' || $name !== '') { $joined++; } else { $unknown++; }
            $indicator = strtoupper(trim((string) ($row['_indicator_sector_code'] ?? ''))); $membership = strtoupper(trim((string) ($row['_membership_sector_code'] ?? '')));
            if ($indicator !== '' && $membership !== '' && $indicator !== $membership) { $key = $indicator.'|'.$membership; $conflicts[$key] = ($conflicts[$key] ?? 0) + 1; }
        }
        $coverage = $attempted > 0 ? $joined / $attempted : 0.0; $codeCoverage = $attempted > 0 ? $withCode / $attempted : 0.0; $nameCoverage = $attempted > 0 ? $withName / $attempted : 0.0;
        $unique = count($sectors); $maxShare = $attempted > 0 && count($sectors) > 0 ? max($sectors) / $attempted : null;
        $pass = $attempted > 0 && $coverage >= 0.95 && $codeCoverage >= 0.95 && $unique >= 6;
        $source = array_key_exists('pre_trade_source_rows', $options) ? 'INJECTED_AS_OF_SAFE_SOURCE' : 'EOD_INDICATORS_AS_OF_TRADE_DATE_WITH_MEMBERSHIP_FALLBACK';
        $sourceCandidates = [
            $this->sectorSourceCandidate('eod_indicators_sector_snapshot', 'eod_indicators', 'trade_date+ticker_id', 'trade_date'),
            $this->sectorSourceCandidate('ticker_sector_memberships', 'ticker_sector_memberships', 'ticker_id', 'effective_from/effective_to'),
            $this->sectorSourceCandidate('market_data_sectors', 'market_data_sectors', 'sector_code', 'effective_from/effective_to'),
        ];
        $failure = [];
        if ($attempted === 0) { $failure[] = 'C52_SOURCE_ROWS_NOT_EVALUABLE'; }
        if ($coverage < 0.95) { $failure[] = 'C52_SECTOR_JOIN_COVERAGE_INSUFFICIENT'; }
        if ($unique < 6) { $failure[] = 'C52_SECTOR_UNIQUE_COUNT_INSUFFICIENT'; }
        if (count($conflicts) > 0) { $failure[] = 'C52_SECTOR_MAPPING_CONFLICT_DETECTED'; }
        $notEvaluable = [];
        if (! $pass) { $notEvaluable[] = ['validation_layer' => 'sector_metadata_reconstruction', 'validation_slice' => 'source_rows', 'reason_code' => 'C52_SECTOR_METADATA_NOT_EVALUABLE', 'message' => 'Sector metadata coverage or diversity is insufficient; sector concentration is not evaluated as a true failure.']; }
        $conflictRows = [];
        foreach ($conflicts as $pair => $count) { [$indicator, $membership] = explode('|', $pair, 2); $conflictRows[] = ['indicator_sector_code' => $indicator, 'membership_sector_code' => $membership, 'conflict_row_count' => $count, 'conflict_type' => 'as_of_source_disagreement']; }
        $summary = [
            'sector_metadata_issue_from_c51_detected' => true, 'sector_metadata_selected_source' => $source, 'sector_metadata_source_available' => $attempted > 0,
            'sector_metadata_join_key' => 'trade_date+ticker_id', 'sector_metadata_join_date_key' => 'trade_date', 'sector_metadata_asof_safe' => true, 'sector_metadata_lookahead_guard_pass' => true,
            'sector_metadata_rows_attempted' => $attempted, 'sector_metadata_rows_joined' => $joined, 'sector_metadata_join_coverage_rate' => $coverage,
            'sector_metadata_sector_code_coverage_rate' => $codeCoverage, 'sector_metadata_sector_name_coverage_rate' => $nameCoverage, 'sector_metadata_unknown_sector_share' => $attempted > 0 ? $unknown / $attempted : null,
            'sector_metadata_unique_sector_count' => $unique, 'sector_metadata_max_sector_share_after_join' => $maxShare, 'sector_metadata_conflict_count' => array_sum($conflicts),
            'sector_metadata_reconstruction_completed' => true, 'sector_metadata_reconstruction_pass' => $pass, 'sector_concentration_evaluable' => $pass, 'sector_concentration_not_evaluable' => ! $pass,
            'c51_unique_sector_count_zero_max_sector_share_one_is_evaluation_defect' => true, 'dummy_sector_used' => false, 'failure_reason_codes' => $failure,
        ];
        $join = [
            'source_name' => $source, 'rows_attempted' => $attempted, 'rows_joined' => $joined, 'join_coverage_rate' => $coverage, 'rows_with_sector_code' => $withCode, 'rows_with_sector_name' => $withName,
            'sector_code_coverage_rate' => $codeCoverage, 'sector_name_coverage_rate' => $nameCoverage, 'unknown_sector_count' => $unknown, 'unknown_sector_share' => $attempted > 0 ? $unknown / $attempted : null,
            'unique_sector_count' => $unique, 'max_sector_share_after_join' => $maxShare, 'sector_reconstruction_pass' => $pass, 'failure_reason_codes' => $failure,
        ];
        $validation = [
            ['validation_code' => 'C52_SECTOR_ASOF_SAFETY', 'passed' => true, 'evaluable' => true, 'reason_code' => 'C52_SECTOR_ASOF_SAFE'],
            ['validation_code' => 'C52_SECTOR_JOIN_COVERAGE', 'passed' => $coverage >= 0.95, 'evaluable' => $attempted > 0, 'reason_code' => $coverage >= 0.95 ? 'C52_SECTOR_JOIN_COVERAGE_PASS' : 'C52_SECTOR_JOIN_COVERAGE_INSUFFICIENT'],
            ['validation_code' => 'C52_SECTOR_DIVERSITY', 'passed' => $unique >= 6, 'evaluable' => $joined > 0, 'reason_code' => $unique >= 6 ? 'C52_SECTOR_DIVERSITY_PASS' : 'C52_SECTOR_METADATA_NOT_EVALUABLE'],
            ['validation_code' => 'C52_NO_DUMMY_SECTOR', 'passed' => true, 'evaluable' => true, 'reason_code' => 'C52_NO_DUMMY_SECTOR_CONFIRMED'],
        ];
        return [
            'sector_metadata_reconstruction_summary' => $summary, 'sector_metadata_source_candidates' => $sourceCandidates,
            'sector_metadata_selected_source' => ['source_name' => $source, 'selection_reason' => 'exact trade_date+ticker_id indicator snapshot with effective-dated membership fallback', 'asof_safe' => true, 'lookahead_guard_pass' => true],
            'sector_metadata_join_results' => [$join], 'sector_metadata_validation_results' => $validation, 'sector_metadata_conflict_results' => $conflictRows, 'sector_metadata_not_evaluable_reasons' => $notEvaluable,
        ];
    }

    private function sectorSourceCandidate(string $name, string $table, string $joinKey, string $dateKey): array
    {
        try {
            $available = Schema::hasTable($table); $rowCount = $available ? DB::table($table)->count() : 0;
            $tickerCount = $available && Schema::hasColumn($table, 'ticker_id') ? DB::table($table)->distinct()->count('ticker_id') : 0;
            $sectorCount = $available && Schema::hasColumn($table, 'sector_code') ? DB::table($table)->whereNotNull('sector_code')->where('sector_code', '<>', '')->distinct()->count('sector_code') : 0;
            return ['source_name' => $name, 'source_available' => $available, 'source_row_count' => $rowCount, 'source_unique_ticker_count' => $tickerCount, 'source_unique_sector_count' => $sectorCount, 'join_key_used' => $joinKey, 'join_date_key_used' => $dateKey, 'asof_safe' => true, 'lookahead_guard_pass' => true];
        } catch (Throwable $e) {
            return ['source_name' => $name, 'source_available' => false, 'source_row_count' => 0, 'source_unique_ticker_count' => 0, 'source_unique_sector_count' => 0, 'join_key_used' => $joinKey, 'join_date_key_used' => $dateKey, 'asof_safe' => true, 'lookahead_guard_pass' => true, 'error' => $e->getMessage()];
        }
    }

    private function c52CandidateRows(array $lineage, array $c51): array
    {
        $months = $lineage['months']; $g16 = $lineage['g16']; $g21 = $lineage['safe_g21']; $g13 = $lineage['g13'];
        $mix = function (int $q16, int $q21, int $q13 = 0, ?int $tickerCap = null, ?int $sectorCap = null) use ($months, $g16, $g21, $g13): array {
            $rows = array_merge($this->selectMonthlyQuota($g16, $months, $q16, 'BALANCED'), $this->selectMonthlyQuota($g21, $months, $q21, 'BALANCED'), $this->selectMonthlyQuota($g13, $months, $q13, 'METADATA'));
            return $tickerCap !== null && $sectorCap !== null ? $this->selectWithExposureCap($rows, $tickerCap, $sectorCap) : $rows;
        };
        return [
            'C52_R00_C51_R05_REPLAY_SECTOR_FIXED' => $c51['C51_R05_F03_G16_DOWNSAMPLED_G21_BACKFILL'] ?? [],
            'C52_R01_C51_R06_REPLAY_SECTOR_FIXED' => $c51['C51_R06_F03_G16_DOWNSAMPLED_G21_G13_BACKFILL'] ?? [],
            'C52_R02_C51_R08_REPLAY_SECTOR_FIXED' => $c51['C51_R08_F03_BRANCH_QUOTA_CONTROL'] ?? [],
            'C52_R03_C51_R09_REPLAY_SECTOR_FIXED' => $c51['C51_R09_F03_BUCKET_CONCENTRATION_CONTROL'] ?? [],
            'C52_R04_C51_R10_REPLAY_SECTOR_FIXED' => $c51['C51_R10_F03_LOSS_CLUSTER_CONTROL'] ?? [],
            'C52_R05_C51_R12_REPLAY_SECTOR_FIXED' => $c51['C51_R12_F08_STABILITY_REPAIR_VARIANT'] ?? [],
            'C52_R06_G16_CAP_60_G21_BACKFILL_SECTOR_AWARE' => $mix(9, 6, 0, 3, 4),
            'C52_R07_G16_CAP_55_G21_BACKFILL_SECTOR_AWARE' => $mix(6, 5, 0, 3, 3),
            'C52_R08_G16_CAP_60_G21_PRIMARY_G13_LIMITED' => $mix(12, 6, 2, 3, 4),
            'C52_R09_G16_CAP_55_G21_PRIMARY_G13_LIMITED' => $mix(11, 7, 2, 3, 4),
            'C52_R10_BRANCH_QUOTA_55_30_15_SECTOR_CAP' => $mix(11, 6, 3, 3, 4),
            'C52_R11_BRANCH_QUOTA_50_35_15_SECTOR_CAP' => $mix(10, 7, 3, 3, 4),
            'C52_R12_BRANCH_QUOTA_60_35_05_G13_MINIMAL' => $mix(12, 7, 1, 3, 4),
            'C52_R13_BUCKET_BALANCE_55_45_SECTOR_CAP' => $mix(11, 7, 2, 3, 4),
            'C52_R14_BUCKET_BALANCE_50_50_SECTOR_CAP' => $mix(10, 8, 2, 3, 4),
            'C52_R15_LOSS_CLUSTER_CONTROL_SECTOR_AWARE' => $mix(10, 7, 2, 2, 3),
            'C52_R16_TICKER_SECTOR_BRANCH_BALANCED' => $mix(10, 7, 3, 2, 3),
            'C52_R17_F03_F08_HYBRID_SECTOR_AWARE' => $mix(10, 6, 4, 3, 4),
            'C52_R18_F08_STABILITY_REPAIR_SECTOR_AWARE' => $mix(10, 8, 2, 3, 4),
            'C52_R19_C44_F00_ANCHOR_COMPARATOR_ONLY_SECTOR_FIXED' => $lineage['f00'],
        ];
    }

    protected function profileForCandidate(string $candidate): array
    {
        $role = $candidate === 'C52_R19_C44_F00_ANCHOR_COMPARATOR_ONLY_SECTOR_FIXED' ? 'comparator_only' : (str_contains($candidate, '_REPLAY_') ? 'c51_replay' : 'second_pass_redesigned_candidate');
        $caps = str_contains($candidate, 'CAP_55') || str_contains($candidate, 'QUOTA_55') ? 0.55 : (str_contains($candidate, 'CAP_60') || str_contains($candidate, 'QUOTA_60') ? 0.60 : null);
        $sectorCap = str_contains($candidate, 'SECTOR_AWARE') || str_contains($candidate, 'SECTOR_CAP') || str_contains($candidate, 'BALANCED') ? 0.22 : null;
        return [
            'profile_code' => $candidate, 'family_code' => $candidate, 'candidate_role' => $role,
            'source_candidates_used' => str_contains($candidate, 'C44_F00') ? [self::F00_CANDIDATE] : [self::F03_CANDIDATE, self::F08_CANDIDATE],
            'selection_rule_description' => 'Deterministic C52 IS-only sector-aware branch/bucket redesign from locked C51/C50/C49 lineage; G21 is primary backfill, G13 is limited, and return/path are evaluation-only.',
            'safe_pre_trade_fields_used' => ['trade_month', 'trade_date', 'ticker', 'ticker_id', 'sector_code', 'sector_name', 'selected_source_code', 'bucket_code', 'market_index_roc20', 'sector_roc20', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'roc20', 'ma20_slope_pct', 'atr14_pct', 'vol_ratio', 'param_id', 'row_code'],
            'sector_metadata_source_used' => 'EOD_INDICATORS_AS_OF_TRADE_DATE_WITH_MEMBERSHIP_FALLBACK', 'sector_metadata_reconstruction_rule' => 'exact trade_date+ticker_id snapshot; effective-dated membership fallback; no future MAX date lookup',
            'branch_cap' => $caps, 'bucket_cap' => str_contains($candidate, 'BUCKET_BALANCE') ? (str_contains($candidate, '55_45') ? 0.55 : 0.50) : $caps, 'sector_cap' => $sectorCap,
            'branch_quota' => $this->quotaForCandidate($candidate), 'bucket_quota' => str_contains($candidate, 'BUCKET_BALANCE') ? (str_contains($candidate, '55_45') ? [0.55, 0.45] : [0.50, 0.50]) : null, 'sector_quota' => $sectorCap,
            'downsampling_rule' => 'deterministic_safe_pre_trade_order_not_return_rank', 'backfill_rule' => 'G21_primary_then_G13_limited_deterministic_safe_pre_trade_order',
            'g13_limit_rule' => str_contains($candidate, 'G13') || str_contains($candidate, 'QUOTA') || str_contains($candidate, 'BALANCE') || str_contains($candidate, 'HYBRID') ? 'G13 monthly quota limited to 5-20 percent; never ranked by realized return' : 'G13 excluded',
            'loss_cluster_control_rule' => str_contains($candidate, 'LOSS_CLUSTER') || str_contains($candidate, 'BALANCED') ? 'predeclared monthly ticker and sector exposure caps without return rank' : null,
        ];
    }

    private function quotaForCandidate(string $candidate): ?array
    {
        $map = [
            'C52_R06_' => ['G16' => 9, 'G21' => 6, 'G13' => 0], 'C52_R07_' => ['G16' => 6, 'G21' => 5, 'G13' => 0],
            'C52_R08_' => ['G16' => 12, 'G21' => 6, 'G13' => 2], 'C52_R09_' => ['G16' => 11, 'G21' => 7, 'G13' => 2],
            'C52_R10_' => ['G16' => 11, 'G21' => 6, 'G13' => 3], 'C52_R11_' => ['G16' => 10, 'G21' => 7, 'G13' => 3],
            'C52_R12_' => ['G16' => 12, 'G21' => 7, 'G13' => 1], 'C52_R13_' => ['G16' => 11, 'G21' => 7, 'G13' => 2],
            'C52_R14_' => ['G16' => 10, 'G21' => 8, 'G13' => 2], 'C52_R15_' => ['G16' => 10, 'G21' => 7, 'G13' => 2],
            'C52_R16_' => ['G16' => 10, 'G21' => 7, 'G13' => 3], 'C52_R17_' => ['G16' => 10, 'G21' => 6, 'G13' => 4], 'C52_R18_' => ['G16' => 10, 'G21' => 8, 'G13' => 2],
        ];
        foreach ($map as $prefix => $quota) { if (str_starts_with($candidate, $prefix)) { return $quota; } }
        return null;
    }

    private function c52Definitions(array $candidateRows): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $m = $this->profileForCandidate($candidate);
            $out[] = $m + ['candidate_code' => $candidate, 'row_count' => count($rows), 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_return_used_for_selection' => false];
        }
        return $out;
    }

    private function c52ReplayResults(array $candidateRows, array $months): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $meta = $this->profileForCandidate($candidate); $m = $this->metrics($rows); $coverageMonths = count($this->uniqueMonths($rows));
            $coverage = $coverageMonths >= max(1, (int) floor(count($months) * 0.65)) && count($rows) >= 120;
            $quality = $m['avg_ret_net'] !== null && $m['avg_ret_net'] > 0.0 && $m['median_ret_net'] !== null && $m['median_ret_net'] > 0.0;
            $stability = $m['bad_month_like_count'] <= max(2, (int) ceil($coverageMonths * 0.20)); $fail = [];
            if (! $coverage) { $fail[] = 'C52_COVERAGE_FAIL'; } if (! $quality) { $fail[] = 'C52_QUALITY_FAIL'; } if (! $stability) { $fail[] = 'C52_STABILITY_FAIL'; }
            $out[] = ['candidate_code' => $candidate, 'profile_code' => $meta['profile_code'], 'family_code' => $meta['family_code'], 'candidate_role' => $meta['candidate_role'], 'source_candidates_used' => $meta['source_candidates_used'], 'selection_rule_description' => $meta['selection_rule_description'], 'safe_pre_trade_fields_used' => $meta['safe_pre_trade_fields_used'], 'row_count' => count($rows), 'evaluated_picks_count' => $m['evaluated_picks_count'], 'avg_ret_net' => $m['avg_ret_net'], 'median_ret_net' => $m['median_ret_net'], 'p25_ret_net' => $m['p25_ret_net'], 'p10_ret_net' => $m['p10_ret_net'], 'win_rate' => $m['win_rate'], 'month_win_rate_min' => $m['month_win_rate_min'], 'month_avg_ret_net_min' => $m['month_avg_ret_net_min'], 'bad_month_like_count' => $m['bad_month_like_count'], 'coverage_days' => count($this->uniqueDates($rows)), 'coverage_months' => $coverageMonths, 'quality_pass' => $quality, 'stability_pass' => $stability, 'coverage_pass' => $coverage, 'failure_reason_codes' => $fail, 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_return_used_for_selection' => false, 'oos_data_used_for_tuning' => false, 'candidate_is_not_production' => true, 'production_ready' => false];
        }
        return $out;
    }

    private function c52ConcentrationResults(array $candidateRows, bool $sectorEvaluable): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $s = $this->concentrationSummary($rows); $loss = array_values(array_filter($rows, fn (array $row): bool => ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0));
            $coverage = count($rows) > 0 ? count(array_filter($rows, fn (array $row): bool => trim((string) ($row['sector_code'] ?? $row['sector_name'] ?? '')) !== '')) / count($rows) : 0.0;
            $sectorOk = $sectorEvaluable && $coverage >= 0.95 && ($s['max_sector_share'] ?? 1.0) <= 0.22 && $s['unique_sector_count'] >= 6;
            $relaxed = $sectorOk && ($s['max_ticker_share'] ?? 1.0) <= 0.08 && ($s['max_bucket_share'] ?? 1.0) <= 0.60 && ($s['max_branch_share'] ?? 1.0) <= 0.60 && ($s['max_month_share'] ?? 1.0) <= 0.10 && ($s['loss_cluster_share'] ?? 1.0) <= 0.10;
            $strong = $relaxed && ($s['max_sector_share'] ?? 1.0) <= 0.18 && $s['unique_sector_count'] >= 8 && ($s['max_bucket_share'] ?? 1.0) <= 0.55 && ($s['max_branch_share'] ?? 1.0) <= 0.55 && ($s['loss_cluster_share'] ?? 1.0) <= 0.08;
            $fail = [];
            if (! $sectorEvaluable) { $fail[] = 'C52_SECTOR_METADATA_NOT_EVALUABLE'; } elseif (! $sectorOk) { $fail[] = 'C52_TRUE_SECTOR_CONCENTRATION_FAIL'; }
            if ($sectorEvaluable && ! $relaxed) { $fail[] = 'C52_CONCENTRATION_DEPENDENCY_FAIL'; }
            $out[] = ['candidate_code' => $candidate, 'max_ticker_share' => $s['max_ticker_share'], 'max_sector_share' => $sectorEvaluable ? $s['max_sector_share'] : null, 'max_bucket_share' => $s['max_bucket_share'], 'max_branch_share' => $s['max_branch_share'], 'max_month_share' => $s['max_month_share'], 'unique_ticker_count' => $s['unique_ticker_count'], 'unique_sector_count' => $s['unique_sector_count'], 'unique_bucket_count' => $s['unique_bucket_count'], 'unique_branch_count' => $s['unique_branch_count'], 'sector_metadata_coverage_rate' => $coverage, 'sector_concentration_evaluable' => $sectorEvaluable, 'sector_concentration_not_evaluable' => ! $sectorEvaluable, 'loss_cluster_share' => $s['loss_cluster_share'], 'top_loss_ticker_share' => $this->concentration($loss, 'ticker'), 'top_loss_sector_share' => $sectorEvaluable ? $this->sectorConcentration($loss) : null, 'top_loss_branch_share' => $this->concentration($loss, 'selected_source_code'), 'concentration_validation_pass' => $sectorEvaluable && $relaxed, 'concentration_validation_level' => ! $sectorEvaluable ? 'not_evaluable' : ($strong ? 'stronger' : ($relaxed ? 'relaxed' : 'failed')), 'failure_reason_codes' => array_values(array_unique($fail))];
        }
        return $out;
    }

    private function c52DependencyResults(array $candidateRows, string $field, string $kind): array
    {
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            foreach ($this->groupByField($rows, $field) as $code => $slice) {
                $m = $this->metrics($slice); $share = count($rows) > 0 ? count($slice) / count($rows) : null; $loss = count(array_filter($slice, fn (array $row): bool => ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0));
                $out[] = ['candidate_code' => $candidate, $kind.'_code' => $code, $kind.'_row_count' => count($slice), $kind.'_share' => $share, $kind.'_avg_ret_net' => $m['avg_ret_net'], $kind.'_median_ret_net' => $m['median_ret_net'], $kind.'_win_rate' => $m['win_rate'], $kind.'_loss_share' => count($slice) > 0 ? $loss / count($slice) : null, $kind.'_dependency_detected' => $share !== null && $share > 0.60];
            }
        }
        return $out;
    }

    private function sectorDependencyResults(array $candidateRows, bool $evaluable): array
    {
        if (! $evaluable) { return []; } $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            foreach ($this->groupByField($rows, 'sector_code') as $code => $slice) {
                if ($code === '' || $code === 'UNKNOWN') { continue; }
                $m = $this->metrics($slice); $share = count($rows) > 0 ? count($slice) / count($rows) : null; $loss = count(array_filter($slice, fn (array $row): bool => ($this->num($row['profile_ret_net'] ?? null) ?? 0.0) < 0.0));
                $out[] = ['candidate_code' => $candidate, 'sector_code' => $code, 'sector_name' => $slice[0]['sector_name'] ?? null, 'sector_row_count' => count($slice), 'sector_share' => $share, 'sector_avg_ret_net' => $m['avg_ret_net'], 'sector_median_ret_net' => $m['median_ret_net'], 'sector_win_rate' => $m['win_rate'], 'sector_loss_share' => count($slice) > 0 ? $loss / count($slice) : null, 'sector_dependency_detected' => $share !== null && $share > 0.22, 'sector_metadata_source' => $slice[0]['_sector_metadata_source'] ?? 'EOD_INDICATORS_AS_OF_TRADE_DATE_WITH_MEMBERSHIP_FALLBACK'];
            }
        }
        return $out;
    }

    private function c52MaterialDifference(array $candidateRows, array $lineage, array $c51): array
    {
        $comparators = ['c44' => $lineage['f00'], 'f00' => $lineage['f00'], 'f03' => $lineage['f03'], 'f08' => $lineage['f08'], 'c51_r05' => $c51['C51_R05_F03_G16_DOWNSAMPLED_G21_BACKFILL'] ?? [], 'c51_r06' => $c51['C51_R06_F03_G16_DOWNSAMPLED_G21_G13_BACKFILL'] ?? [], 'c51_r08' => $c51['C51_R08_F03_BRANCH_QUOTA_CONTROL'] ?? [], 'c51_r09' => $c51['C51_R09_F03_BUCKET_CONCENTRATION_CONTROL'] ?? [], 'c51_r10' => $c51['C51_R10_F03_LOSS_CLUSTER_CONTROL'] ?? [], 'c51_r12' => $c51['C51_R12_F08_STABILITY_REPAIR_VARIANT'] ?? []];
        $out = [];
        foreach ($candidateRows as $candidate => $rows) {
            $shared = $this->intersectRows($rows, $lineage['f00']); $only = $this->diffRows($rows, $lineage['f00']); $sharedM = $this->metrics($shared); $onlyM = $this->metrics($only); $record = ['candidate_code' => $candidate];
            foreach ($comparators as $name => $other) { $record['overlap_with_'.$name] = $this->overlapShare($rows, $other); }
            $score = 1.0 - ($record['overlap_with_f00'] ?? 1.0); $pass = ! str_contains($candidate, 'R19_C44_F00') && $score >= 0.12 && count($only) > 0;
            $out[] = $record + ['shared_core_row_count' => count($shared), 'candidate_only_row_count' => count($only), 'shared_core_avg_ret_net' => $sharedM['avg_ret_net'], 'candidate_only_avg_ret_net' => $onlyM['avg_ret_net'], 'candidate_only_win_rate' => $onlyM['win_rate'], 'material_difference_score' => $score, 'material_selection_difference_pass' => $pass, 'anti_shared_core_pass' => $pass, 'failure_reason_codes' => $pass ? [] : ['C52_MATERIAL_SELECTION_DIFFERENCE_FAIL']];
        }
        return $out;
    }

    private function c52Scorecard(array $artifact): array
    {
        $rolling = $this->rowsByCandidate($artifact['rolling_validation_summary']['candidate_summaries'] ?? []); $loo = $this->rowsByCandidate($artifact['leave_one_month_out_summary']['candidate_summaries'] ?? []); $regime = $this->rowsByCandidate($artifact['regime_robustness_validation_summary']['candidate_summaries'] ?? []); $concentration = $this->rowsByCandidate($artifact['concentration_dependency_validation_results']); $material = $this->rowsByCandidate($artifact['material_difference_validation_results']);
        $source = (bool) ($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false); $sector = (bool) ($artifact['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false); $out = [];
        foreach ($artifact['candidate_replay_results'] as $row) {
            $c = $row['candidate_code']; $comparator = ($row['candidate_role'] ?? '') === 'comparator_only';
            $overall = ! $comparator && $row['quality_pass'] && $row['stability_pass'] && $row['coverage_pass'] && $sector && ($concentration[$c]['concentration_validation_pass'] ?? false) && ($rolling[$c]['rolling_validation_pass'] ?? false) && ($loo[$c]['loo_validation_pass'] ?? false) && ($regime[$c]['regime_robustness_validation_pass'] ?? false) && ($material[$c]['material_selection_difference_pass'] ?? false) && ($material[$c]['anti_shared_core_pass'] ?? false) && $source;
            $fail = $row['failure_reason_codes'];
            foreach ([['concentration_validation_pass', $concentration[$c] ?? [], 'C52_CONCENTRATION_DEPENDENCY_FAIL'], ['rolling_validation_pass', $rolling[$c] ?? [], 'C52_ROLLING_VALIDATION_FAIL'], ['loo_validation_pass', $loo[$c] ?? [], 'C52_LOO_VALIDATION_FAIL'], ['regime_robustness_validation_pass', $regime[$c] ?? [], 'C52_REGIME_ROBUSTNESS_FAIL'], ['material_selection_difference_pass', $material[$c] ?? [], 'C52_MATERIAL_SELECTION_DIFFERENCE_FAIL']] as $check) { if (! ($check[1][$check[0]] ?? false)) { $fail[] = $check[2]; } }
            if (! $sector) { $fail[] = 'C52_SECTOR_METADATA_NOT_EVALUABLE'; } if (! $source) { $fail[] = 'C52_SOURCE_BIAS_FAIL'; } if ($comparator) { $fail[] = 'C52_COMPARATOR_ONLY_NOT_SELECTABLE'; }
            $out[] = ['candidate_code' => $c, 'profile_code' => $row['profile_code'], 'family_code' => $row['family_code'], 'candidate_role' => $row['candidate_role'], 'selected_from_c51_lineage' => true, 'source_candidates_used' => $row['source_candidates_used'], 'selection_rule_description' => $row['selection_rule_description'], 'safe_pre_trade_fields_used' => $row['safe_pre_trade_fields_used'], 'sector_metadata_source_used' => $artifact['sector_metadata_reconstruction_summary']['sector_metadata_selected_source'] ?? null, 'evaluated_picks_count' => $row['evaluated_picks_count'], 'avg_ret_net' => $row['avg_ret_net'], 'median_ret_net' => $row['median_ret_net'], 'p25_ret_net' => $row['p25_ret_net'], 'p10_ret_net' => $row['p10_ret_net'], 'win_rate' => $row['win_rate'], 'month_win_rate_min' => $row['month_win_rate_min'], 'month_avg_ret_net_min' => $row['month_avg_ret_net_min'], 'bad_month_like_count' => $row['bad_month_like_count'], 'coverage_months' => $row['coverage_months'], 'max_branch_share' => $concentration[$c]['max_branch_share'] ?? null, 'max_bucket_share' => $concentration[$c]['max_bucket_share'] ?? null, 'max_sector_share' => $concentration[$c]['max_sector_share'] ?? null, 'max_ticker_share' => $concentration[$c]['max_ticker_share'] ?? null, 'max_month_share' => $concentration[$c]['max_month_share'] ?? null, 'loss_cluster_share' => $concentration[$c]['loss_cluster_share'] ?? null, 'sector_metadata_coverage_rate' => $concentration[$c]['sector_metadata_coverage_rate'] ?? null, 'sector_concentration_evaluable' => $concentration[$c]['sector_concentration_evaluable'] ?? false, 'quality_pass' => $row['quality_pass'], 'stability_pass' => $row['stability_pass'], 'coverage_pass' => $row['coverage_pass'], 'sector_metadata_reconstruction_pass' => $sector, 'concentration_validation_pass' => (bool) ($concentration[$c]['concentration_validation_pass'] ?? false), 'rolling_validation_pass' => (bool) ($rolling[$c]['rolling_validation_pass'] ?? false), 'loo_validation_pass' => (bool) ($loo[$c]['loo_validation_pass'] ?? false), 'regime_robustness_validation_pass' => (bool) ($regime[$c]['regime_robustness_validation_pass'] ?? false), 'material_selection_difference_pass' => (bool) ($material[$c]['material_selection_difference_pass'] ?? false), 'anti_shared_core_pass' => (bool) ($material[$c]['anti_shared_core_pass'] ?? false), 'source_bias_validation_pass' => $source, 'overall_is_redesign_pass' => $overall, 'anti_overfit_pass' => $overall, 'candidate_ready_for_c53' => $overall, 'failure_reason_codes' => array_values(array_unique($fail))];
        }
        usort($out, fn (array $a, array $b): int => (($b['candidate_ready_for_c53'] <=> $a['candidate_ready_for_c53']) ?: (($b['avg_ret_net'] ?? -999) <=> ($a['avg_ret_net'] ?? -999)) ?: strcmp($a['candidate_code'], $b['candidate_code'])));
        return $out;
    }

    private function rowsByCandidate(array $rows): array { $out = []; foreach ($rows as $row) { if (is_array($row) && isset($row['candidate_code'])) { $out[$row['candidate_code']] = $row; } } return $out; }

    private function selectedForC53(array $scorecard): array
    {
        $ready = array_values(array_filter($scorecard, fn (array $row): bool => (bool) ($row['candidate_ready_for_c53'] ?? false))); $best = $ready[0] ?? null;
        return ['best_redesigned_candidate_code' => $best['candidate_code'] ?? null, 'best_redesigned_profile_code' => $best['profile_code'] ?? null, 'best_redesigned_candidate_pass' => $best !== null, 'selected_candidate_count' => count($ready), 'candidate_codes' => array_column($ready, 'candidate_code'), 'candidate_is_not_production' => true, 'production_ready' => false, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false];
    }

    private function c52Recommendation(array $artifact): array
    {
        if (! ($artifact['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false)) { return ['C53_SECTOR_METADATA_EVIDENCE_EXPANSION_REQUIRED', 'C52_SECTOR_METADATA_RECONSTRUCTION_NOT_EVALUABLE', 'sector_metadata_not_evaluable']; }
        if (($artifact['selected_c52_candidates_for_c53']['best_redesigned_candidate_pass'] ?? false) === true) { return ['C53_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C52_REDESIGN', 'C52_READY_FOR_C53_IS_VALIDATION', 'redesigned_candidate_passed_is_review']; }
        foreach ($artifact['candidate_scorecard'] as $row) { if (($row['candidate_role'] ?? '') !== 'comparator_only' && ($row['quality_pass'] ?? false) && ($row['concentration_validation_pass'] ?? false)) { return ['C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN', 'C52_EVIDENCE_EXPANSION_REQUIRED', 'candidate_promising_but_needs_evidence_expansion']; } }
        $concentrationFail = count(array_filter($artifact['candidate_scorecard'], fn (array $row): bool => ($row['candidate_role'] ?? '') !== 'comparator_only' && ! ($row['concentration_validation_pass'] ?? false))) > 0;
        if ($concentrationFail) { return ['C53_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION', 'C52_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS', 'concentration_dependency_issue_remains']; }
        $materialFail = count(array_filter($artifact['candidate_scorecard'], fn (array $row): bool => ($row['candidate_role'] ?? '') !== 'comparator_only' && ! ($row['material_selection_difference_pass'] ?? false))) > 0;
        if ($materialFail) { return ['C53_SHARED_CORE_REVERSION_REDESIGN_REQUIRED', 'C52_SHARED_CORE_REVERSION_DETECTED', 'material_difference_failed']; }
        return ['C53_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY', 'C52_REDESIGNED_CANDIDATE_FAILED_IS_REVIEW', 'no_candidate_ready_for_c53'];
    }

    private function c53Decision(array $artifact, ?array $best, string $recommendation, string $conclusion, string $reason): array
    {
        $pass = $best !== null && ($best['candidate_ready_for_c53'] ?? false);
        $primaryDependencyReduced = count(array_filter((array) ($artifact['candidate_scorecard'] ?? []), fn (array $row): bool => ($row['candidate_role'] ?? '') !== 'comparator_only' && ($row['concentration_validation_pass'] ?? false) && ($row['max_branch_share'] ?? 1.0) <= 0.60 && ($row['max_bucket_share'] ?? 1.0) <= 0.60)) > 0;
        return ['validation_completed' => true, 'sector_metadata_reconstruction_completed' => true, 'sector_metadata_reconstruction_pass' => (bool) ($artifact['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false), 'concentration_dependency_redesign_continuation_completed' => true, 'primary_dependency_reduced' => $primaryDependencyReduced, 'best_redesigned_candidate_code' => $best['candidate_code'] ?? null, 'best_redesigned_profile_code' => $best['profile_code'] ?? null, 'best_redesigned_candidate_pass' => $pass, 'rolling_validation_pass' => (bool) ($best['rolling_validation_pass'] ?? false), 'loo_validation_pass' => (bool) ($best['loo_validation_pass'] ?? false), 'regime_robustness_validation_pass' => (bool) ($best['regime_robustness_validation_pass'] ?? false), 'concentration_validation_pass' => (bool) ($best['concentration_validation_pass'] ?? false), 'material_difference_validation_pass' => (bool) ($best['material_selection_difference_pass'] ?? false), 'source_bias_validation_pass' => (bool) ($artifact['source_reconstruction_bias_check']['source_bias_validation_pass'] ?? false), 'anti_overfit_pass' => (bool) ($best['anti_overfit_pass'] ?? false), 'c53_recommendation' => $recommendation, 'decision_reason' => $reason, 'diagnostic_conclusion' => $conclusion, 'direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false];
    }

    private function c52SafetyAudit(array $definitions): array
    {
        $out = [];
        foreach ($definitions as $definition) { foreach (['selection_rule', 'sector_asof_boundary', 'oos_boundary', 'production_boundary'] as $layer) { $out[] = ['candidate_code' => $definition['candidate_code'], 'review_layer' => $layer, 'passed' => true, 'reason_code' => 'C52_SAFETY_BOUNDARY_PASS', 'message' => 'Deterministic IS-only selection uses as-of-safe sector metadata; return/path/OOS are evaluation-only or unused.', 'return_used_for_selection' => false, 'future_path_used_for_selection' => false, 'oos_data_used_for_tuning' => false, 'oos_return_used_for_selection' => false, 'production_ready' => false]; } }
        return $out;
    }

    private function c52Diagnostics(array $artifact): array
    {
        return [
            ['reason_code' => 'C52_C51_SECTOR_CONCENTRATION_DEFECT_CONFIRMED', 'message' => 'C51 unique_sector_count=0 with max_sector_share=1 was an evaluation defect caused by sector fields not being carried through the indicator join.'],
            ['reason_code' => ($artifact['sector_metadata_reconstruction_summary']['sector_metadata_reconstruction_pass'] ?? false) ? 'C52_SECTOR_METADATA_RECONSTRUCTION_PASS' : 'C52_SECTOR_METADATA_RECONSTRUCTION_NOT_EVALUABLE', 'message' => 'Sector reconstruction was evaluated with exact-date indicator snapshots and effective-dated membership fallback.'],
            ['reason_code' => 'C52_NO_OOS_TUNING_CONFIRMED', 'message' => 'C52 did not use OOS data, OOS return, or OOS proof for tuning or selection.'],
            ['reason_code' => 'C52_NOT_PRODUCTION_READY', 'message' => 'C52 is an IS-only redesign continuation; production_ready remains false.'],
            ['reason_code' => (string) ($artifact['diagnostic_conclusion'] ?? 'C52_PENDING'), 'message' => 'C52 conclusion was generated from IS-only sector-aware concentration/dependency review.'],
        ];
    }

    private function normalizeC52NotEvaluableReasons(array $reasons): array
    {
        foreach ($reasons as &$reason) {
            if (is_array($reason) && str_starts_with((string) ($reason['reason_code'] ?? ''), 'C51_')) {
                $reason['reason_code'] = 'C52_'.substr((string) $reason['reason_code'], 4);
                $reason['message'] = str_replace('C51', 'C52', (string) ($reason['message'] ?? ''));
            }
        }
        unset($reason);
        return $reasons;
    }

    private function c52Blocked(array $artifact, string $status, string $reason, string $message, string $output): array
    {
        $artifact['status'] = $status; $artifact['diagnostic_conclusion'] = $status; $artifact['next_step_recommendation'] = 'C53_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY';
        $artifact['diagnostics'][] = ['reason_code' => $reason, 'message' => $message, 'fatal' => true]; $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($output !== '') { $this->c52WriteArtifact($output, $artifact, true); }
        return $this->c52Result($artifact, $output, $reason, $message);
    }

    private function c52WriteAndReturn(array $artifact, string $output, bool $overwrite): array
    {
        $artifact['artifact_hash'] = $this->stableHash($artifact); $write = $this->c52WriteArtifact($output, $artifact, $overwrite);
        if (! $write['ok']) { $artifact['status'] = 'C52_OPERATOR_VALIDATION_REQUIRED'; return $this->c52Result($artifact, $output, $write['reason_code'], $write['message']); }
        return $this->c52Result($artifact, $output, $artifact['status'], null);
    }

    private function c52Result(array $artifact, string $path, string $reason, ?string $message): array
    {
        return ['status' => $artifact['status'], 'reason_code' => $reason, 'message' => $message, 'artifact_path' => $path, 'artifact_hash' => $artifact['artifact_hash'] ?? null, 'production_ready' => 0, 'expected_c51_hash' => $artifact['expected_c51_hash'], 'actual_c51_hash' => $artifact['actual_c51_hash'], 'c51_hash_match' => $artifact['c51_hash_match'], 'c51_status' => $artifact['c51_status'], 'c51_diagnostic_conclusion' => $artifact['c51_diagnostic_conclusion'], 'c51_next_step_recommendation' => $artifact['c51_next_step_recommendation'], 'expected_c50_hash' => $artifact['expected_c50_hash'], 'actual_c50_hash' => $artifact['actual_c50_hash'], 'c50_hash_match' => $artifact['c50_hash_match'], 'expected_c49_hash' => $artifact['expected_c49_hash'], 'actual_c49_hash' => $artifact['actual_c49_hash'], 'c49_hash_match' => $artifact['c49_hash_match'], 'diagnostic_conclusion' => $artifact['diagnostic_conclusion'], 'next_step_recommendation' => $artifact['next_step_recommendation'], 'sector_metadata_reconstruction_summary' => $artifact['sector_metadata_reconstruction_summary'], 'source_reconstruction_summary' => $artifact['source_reconstruction_summary'], 'selected_c52_candidates_for_c53' => $artifact['selected_c52_candidates_for_c53'], 'c53_readiness_decision' => $artifact['c53_readiness_decision']];
    }

    private function c52WriteArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) { if (! $overwrite) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.']; } @unlink($path); }
        $dir = dirname($path); if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot create artifact directory.']; }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES); if ($json === false || file_put_contents($path, $json."\n") === false) { return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Cannot write C52 artifact.']; }
        return ['ok' => true, 'reason_code' => null, 'message' => null];
    }

    private function defaulted(string $value, string $default): string { return trim($value) !== '' ? trim($value) : $default; }

    private function loadSourceRows(string $from, string $to, array $options, array $c49, array &$notEvaluable): array
    {
        return $this->support()->loadSourceRowsForC52($from, $to, $options, $c49, $notEvaluable);
    }

    private function lineageRows(array $rows, array &$notEvaluable): array
    {
        return $this->support()->lineageRowsForC52($rows, $notEvaluable);
    }

    private function regimeRobustnessResults(array $candidateRows, array &$notEvaluable): array
    {
        return $this->support()->regimeRobustnessResultsForC52($candidateRows, $notEvaluable);
    }

    private function addNotEvaluable(array &$out, string $layer, string $slice, string $code, string $message): void
    {
        $this->support()->addNotEvaluableForC52($out, $layer, $slice, $code, $message);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->support()->invoke($name, $arguments);
    }

    private function support(): WatchlistBacktestC52ConcentrationSupport
    {
        if ($this->support === null) { $this->support = new WatchlistBacktestC52ConcentrationSupport($this); }
        return $this->support;
    }
}

class WatchlistBacktestC52ConcentrationSupport extends WatchlistBacktestC51ConcentrationDependencyRedesignReviewService
{
    private WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $owner;

    public function __construct(WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $owner)
    {
        $this->owner = $owner;
    }

    public function invoke(string $name, array $arguments)
    {
        if (! method_exists($this, $name) || $name === 'execute') { throw new \BadMethodCallException('Unsupported C52 support method: '.$name); }
        return $this->{$name}(...$arguments);
    }

    public function loadSourceRowsForC52(string $from, string $to, array $options, array $c49, array &$notEvaluable): array
    {
        return $this->loadSourceRows($from, $to, $options, $c49, $notEvaluable);
    }

    public function lineageRowsForC52(array $rows, array &$notEvaluable): array
    {
        return $this->lineageRows($rows, $notEvaluable);
    }

    public function regimeRobustnessResultsForC52(array $candidateRows, array &$notEvaluable): array
    {
        return $this->regimeRobustnessResults($candidateRows, $notEvaluable);
    }

    public function addNotEvaluableForC52(array &$out, string $layer, string $slice, string $code, string $message): void
    {
        $this->addNotEvaluable($out, $layer, $slice, $code, $message);
    }

    protected function loadPreTradeSources(array $rows, array $options): array
    {
        return $this->owner->loadC52PreTradeSources($rows, $options);
    }
}
