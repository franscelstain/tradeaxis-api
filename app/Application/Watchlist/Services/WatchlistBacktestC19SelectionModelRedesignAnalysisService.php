<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC19SelectionModelRedesignAnalysisService
{
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c19-selection-model-redesign-analysis.json';
    public const ARTIFACT_TYPE = 'C19_SELECTION_MODEL_REDESIGN_ANALYSIS';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;
    public const CANONICAL_SAMPLE_TARGET = 120;

    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;
    private WatchlistCandidateUniverseService $candidateUniverse;
    private WatchlistScoringService $scoring;
    private WatchlistPlanGroupingService $planGrouping;
    private WatchlistRecommendationService $recommendation;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null,
        WatchlistCandidateUniverseService $candidateUniverse = null,
        WatchlistScoringService $scoring = null,
        WatchlistPlanGroupingService $planGrouping = null,
        WatchlistRecommendationService $recommendation = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
        $this->candidateUniverse = $candidateUniverse ?: new WatchlistCandidateUniverseService();
        $this->scoring = $scoring ?: new WatchlistScoringService();
        $this->planGrouping = $planGrouping ?: new WatchlistPlanGroupingService();
        $this->recommendation = $recommendation ?: new WatchlistRecommendationService($this->planGrouping);
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode) !== '' ? trim($catalogCode) : self::DEFAULT_SOURCE_CATALOG_CODE;
        $fromDate = trim($fromDate);
        $toDate = trim($toDate);
        $outputPath = trim($outputPath);

        if (! $this->validDate($fromDate) || ! $this->validDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked('WS_BT_C19_INVALID_IS_WINDOW', 'Explicit from/to date window is invalid.');
        }
        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C19_IS_ONLY_WINDOW_MISMATCH', 'C19 selection diagnostic requires the exact frozen IS window.');
        }
        if ($outputPath === '' || is_dir($outputPath)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit non-directory output path is required.');
        }
        if (is_file($outputPath) && ! ($options['overwrite'] ?? false)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Output file already exists. Use overwrite explicitly.');
        }

        $policyCode = (string) ($options['policy_code'] ?? 'WS');
        try {
            $rows = $this->paramGrid->allForCatalog($catalogCode, $policyCode);
        } catch (\Throwable $e) {
            return $this->blocked($this->reasonCode($e), $e->getMessage());
        }
        if ($rows === []) {
            return $this->blocked('WS_BT_C19_SOURCE_CATALOG_MISSING', 'No rows found for explicit source catalog_code.', [
                'catalog_code' => $catalogCode,
                'policy_code' => $policyCode,
            ]);
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? []);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C19_PARAM_IDS_INVALID', 'Optional param_ids must be positive integers.');
        }
        if ($paramIds !== []) {
            $allowed = array_fill_keys($paramIds, true);
            $rows = array_values(array_filter($rows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_C19_ROW_FILTER_NO_MATCH', 'No source catalog rows matched the explicit param filter.');
            }
        }

        usort($rows, function (array $left, array $right): int {
            $comparison = strcmp((string) ($left['row_code'] ?? ''), (string) ($right['row_code'] ?? ''));
            return $comparison !== 0 ? $comparison : (((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0)));
        });

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C19 selection diagnostic.', [
                'calendar' => $calendar,
            ]);
        }
        $tradeDates = $this->normalizeDateList($calendar['trade_dates'] ?? []);
        if ($tradeDates === []) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar returned no trade dates.');
        }

        $executedAt = (string) ($options['executed_at'] ?? ($toDate.'T23:59:59+07:00'));
        $diagnostics = [];
        foreach ($rows as $row) {
            $paramset = $this->paramsetFactory->make($row);
            $diagnostics[] = $this->diagnoseParam($row, $paramset, $tradeDates);
        }

        $summary = $this->crossParamSummary($diagnostics);
        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY',
            'scope' => 'IS_ONLY_DIAGNOSTIC',
            'generated_at' => $executedAt,
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'row_count' => count($rows),
                'policy_code' => $policyCode,
            ],
            'is_window' => [
                'from' => $fromDate,
                'to' => $toDate,
                'trade_date_count' => count($tradeDates),
            ],
            'c18_final_evidence_used' => [
                'runtime_first_full_12_pass' => true,
                'deep_funnel_param_150_pass' => true,
                'deep_funnel_param_149_pass' => true,
                'primary_root_cause' => 'selection_collapse_after_scored_pool',
                'catalog_implementation_deferred' => true,
                'source' => 'operator-provided C18 final evidence in session prompt and repository C18 audit docs',
            ],
            'source_path_findings' => $this->sourcePathFindings(),
            'proposed_selection_model' => $this->proposedSelectionModel(),
            'diagnostics' => $diagnostics,
            'cross_param_summary' => $summary,
            'c19_catalog_decision' => [
                'C19_CATALOG_IMPLEMENTATION_DEFERRED' => true,
                'C19_IMPLEMENTED_SOURCE_LEVEL' => false,
                'C19_CATALOG_CODE' => 'NOT_CREATED',
                'defer_reason' => 'Fase A/B diagnostic/prototype only: selector simulation is not a canonical price-evaluated IS calibration and does not justify catalog creation yet.',
                'next_gate_before_catalog' => [
                    'run C19 v3 diagnostic on source catalog',
                    'verify current path mapping against PlanGrouping and Recommendation summaries',
                    'verify proposed selector recovery has non-zero TOP/SECONDARY/recommended buffers',
                    'implement source-level runtime mode only if diagnostic shows enough sample recovery',
                    'run price-evaluated IS calibration twice before any catalog decision',
                ],
            ],
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message']);
        }

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY',
            'scope' => 'IS_ONLY_DIAGNOSTIC',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'diagnostic_param_count' => count($diagnostics),
            'max_current_top_count' => (int) ($summary['max_current_top_count'] ?? 0),
            'max_current_secondary_count' => (int) ($summary['max_current_secondary_count'] ?? 0),
            'max_current_recommended_count' => (int) ($summary['max_current_recommended_count'] ?? 0),
            'max_proposed_top_count' => (int) ($summary['max_proposed_top_count'] ?? 0),
            'max_proposed_secondary_count' => (int) ($summary['max_proposed_secondary_count'] ?? 0),
            'max_proposed_recommended_count' => (int) ($summary['max_proposed_recommended_count'] ?? 0),
            'params_with_proposed_secondary_recovery' => (int) ($summary['params_with_proposed_secondary_recovery'] ?? 0),
            'params_with_non_unknown_drop_reasons' => (int) ($summary['params_with_non_unknown_drop_reasons'] ?? 0),
            'c19_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function diagnoseParam(array $row, array $paramset, array $tradeDates): array
    {
        $holdingDays = max(1, (int) ($paramset['backtest']['holding_days'] ?? 5));
        $strategyTradeDates = count($tradeDates) > $holdingDays
            ? array_slice($tradeDates, 0, count($tradeDates) - $holdingDays)
            : [];

        $current = [
            'candidate_raw_count' => 0,
            'candidate_eligible_count' => 0,
            'candidate_rejected_count' => 0,
            'scored_candidate_count' => 0,
            'scoring_excluded_count' => 0,
            'plan_top_picks_count' => 0,
            'plan_secondary_count' => 0,
            'plan_watch_only_count' => 0,
            'plan_avoid_count' => 0,
            'plan_excluded_count' => 0,
            'recommendation_input_count' => 0,
            'recommendation_evaluated_count' => 0,
            'recommendation_output_count' => 0,
            'requested_pairs_count' => 0,
            'evaluated_picks_count' => 0,
            'price_evaluation_not_run' => true,
        ];
        $monthly = $this->monthSkeleton($strategyTradeDates);
        $buffers = [];
        $currentDropReasons = [];
        $currentExtensionFailureReasons = [];
        $proposalFatalReasons = [];
        $proposalPenaltyReasons = [];
        $debugOutputKeys = [
            'candidate_universe_keys' => [],
            'scored_output_keys' => [],
            'plan_grouping_keys' => [],
            'plan_grouping_group_keys' => [],
            'recommendation_keys' => [],
        ];

        foreach ($strategyTradeDates as $tradeDate) {
            $month = substr($tradeDate, 0, 7);
            $universe = $this->candidateUniverse->buildCandidateUniverseForTradeDate($tradeDate, $paramset);
            $scored = $this->scoring->scoreCandidateUniverse($universe, $paramset, $tradeDate);
            $grouped = $this->planGrouping->groupScoredOutput($scored, $paramset, $tradeDate);
            $recommended = $this->recommendation->recommendFromPlanOutput($grouped, $paramset, []);

            $debugOutputKeys = $this->mergeDebugOutputKeys($debugOutputKeys, $universe, $scored, $grouped, $recommended);

            $topItems = $this->groupItems($grouped, 'TOP_PICKS');
            $secondaryItems = $this->groupItems($grouped, 'SECONDARY');
            $watchOnlyItems = $this->groupItems($grouped, 'WATCH_ONLY');
            $avoidItems = $this->groupItems($grouped, 'AVOID');
            $recommendationInputCount = count($topItems) + count($secondaryItems);
            $recommendationOutputCount = $this->recommendationOutputCount($recommended);
            $recommendationEvaluatedCount = $this->recommendationEvaluatedCount($recommended);

            $current['candidate_raw_count'] += $this->candidateRawCount($universe);
            $current['candidate_eligible_count'] += $this->candidateEligibleCount($universe);
            $current['candidate_rejected_count'] += $this->candidateRejectedCount($universe);
            $current['scored_candidate_count'] += $this->scoredCandidateCount($scored);
            $current['scoring_excluded_count'] += $this->scoringExcludedCount($scored);
            $current['plan_top_picks_count'] += count($topItems);
            $current['plan_secondary_count'] += count($secondaryItems);
            $current['plan_watch_only_count'] += count($watchOnlyItems);
            $current['plan_avoid_count'] += count($avoidItems);
            $current['plan_excluded_count'] += count(array_filter($grouped['excluded'] ?? [], 'is_array'));
            $current['recommendation_input_count'] += $recommendationInputCount;
            $current['recommendation_evaluated_count'] += $recommendationEvaluatedCount;
            $current['recommendation_output_count'] += $recommendationOutputCount;

            $monthly[$month]['current_top_count'] += count($topItems);
            $monthly[$month]['current_secondary_count'] += count($secondaryItems);
            $monthly[$month]['current_watch_only_count'] += count($watchOnlyItems);
            $monthly[$month]['current_recommendation_input_count'] += $recommendationInputCount;
            $monthly[$month]['current_recommended_count'] += $recommendationOutputCount;

            foreach ($avoidItems as $avoid) {
                $this->addReasonCounts($currentDropReasons, $this->reasonCodesFromItem($avoid));
            }
            foreach (($grouped['excluded'] ?? []) as $excluded) {
                if (is_array($excluded)) {
                    $this->addReasonCounts($currentDropReasons, $this->reasonCodesFromItem($excluded));
                }
            }
            foreach (($scored['excluded'] ?? []) as $excluded) {
                if (is_array($excluded)) {
                    $this->addReasonCounts($currentDropReasons, $this->reasonCodesFromItem($excluded));
                }
            }
            foreach (($scored['items'] ?? []) as $item) {
                if (is_array($item)) {
                    $this->addReasonCounts($currentExtensionFailureReasons, $this->currentExtensionFailureReasons($item, $paramset));
                }
            }

            $dateBuffers = $this->proposeDateBuffers($scored, $paramset, $tradeDate, $proposalFatalReasons, $proposalPenaltyReasons);
            foreach ($dateBuffers as $buffer) {
                $buffers[] = $buffer;
                $monthly[$month]['proposed_'.$buffer['proposed_plan_group'].'_buffer_count']++;
            }
        }

        $proposal = $this->finalizeCoverageAwareProposal($buffers, $monthly, $paramset);
        $currentDropReasons = $this->mergeReasonCounts($currentDropReasons, $currentExtensionFailureReasons);

        return [
            'param_id' => (int) ($row['param_id'] ?? 0),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'strategy_trade_date_count' => count($strategyTradeDates),
            'debug_output_keys' => $debugOutputKeys,
            'current_path' => $current,
            'proposed_path' => [
                'top_count' => $proposal['top_count'],
                'secondary_count' => $proposal['secondary_count'],
                'candidate_buffer_count' => $proposal['candidate_buffer_count'],
                'recommended_count' => $proposal['recommended_count'],
                'estimated_sample_recovery_target' => self::CANONICAL_SAMPLE_TARGET,
                'target_reached_by_diagnostic_only' => $proposal['recommended_count'] >= self::CANONICAL_SAMPLE_TARGET,
                'price_evaluation_not_run' => true,
                'fatal_reject_reason_counts' => $this->topCounts($proposalFatalReasons, 12),
                'penalty_reason_counts' => $this->topCounts($proposalPenaltyReasons, 12),
            ],
            'monthly_distribution' => array_values($proposal['monthly']),
            'dominant_current_drop_reasons' => $this->topCounts($currentDropReasons, 12),
            'dominant_current_extension_failure_reasons' => $this->topCounts($currentExtensionFailureReasons, 12),
            'root_cause_code_level' => [
                'selection_collapse_after_scored_pool' => ($current['scored_candidate_count'] > 0 && $current['recommendation_output_count'] < self::CANONICAL_SAMPLE_TARGET),
                'secondary_zero_observed' => $current['plan_secondary_count'] === 0,
                'secondary_zero_interpretation' => 'design_cutoff_guard_behavior_not_runtime_bug',
                'current_path_mapping_fixed_v3' => true,
                'proposal_selector_simulation_from_scored_pool' => true,
                'hard_guard_hotspots' => [
                    'WatchlistPlanGroupingService::candidateSelectionExtensionFailures',
                    'WatchlistPlanGroupingService::c17QualityFloorFailures',
                    'WatchlistRecommendationService::dynamicTargetCount',
                ],
            ],
        ];
    }

    private function proposeDateBuffers(array $scoredOutput, array $paramset, string $tradeDate, array &$fatalReasons, array &$penaltyReasons): array
    {
        $items = array_values(array_filter($scoredOutput['items'] ?? [], 'is_array'));
        $ranked = [];
        foreach ($items as $item) {
            $assessment = $this->assessCandidateForC19Buffer($item, $paramset);
            if (! $assessment['eligible']) {
                $this->addReasonCounts($fatalReasons, $assessment['fatal_reasons']);
                continue;
            }
            $this->addReasonCounts($penaltyReasons, array_keys($assessment['penalties']));
            $ranked[] = array_merge($assessment, [
                'trade_date' => $tradeDate,
                'month' => substr($tradeDate, 0, 7),
                'ticker_id' => (int) ($item['ticker_id'] ?? 0),
                'ticker_code' => (string) ($item['ticker_code'] ?? ''),
                'score_total' => $this->floatValue($item['score_total'] ?? 0.0),
            ]);
        }

        usort($ranked, function (array $left, array $right): int {
            foreach ([['quality_score', false], ['score_total', false], ['penalty_total', true], ['ticker_id', true]] as $sort) {
                [$key, $ascending] = $sort;
                $a = $left[$key] ?? 0;
                $b = $right[$key] ?? 0;
                if ($a == $b) {
                    continue;
                }
                $cmp = $a <=> $b;
                return $ascending ? $cmp : -$cmp;
            }
            return strcmp((string) ($left['ticker_code'] ?? ''), (string) ($right['ticker_code'] ?? ''));
        });

        $topMax = max(1, (int) ($paramset['grouping']['top_picks']['max_items'] ?? 5));
        $secondaryMax = max(1, (int) ($paramset['grouping']['secondary']['max_items'] ?? 10));
        $buffers = [];
        $top = 0;
        $secondary = 0;
        foreach ($ranked as $item) {
            if ($item['quality_score'] >= 0.62 && $top < $topMax) {
                $item['proposed_plan_group'] = 'top';
                $item['proposed_rank'] = $top + 1;
                $top++;
                $buffers[] = $item;
                continue;
            }
            if ($item['quality_score'] >= 0.52 && $secondary < $secondaryMax) {
                $item['proposed_plan_group'] = 'secondary';
                $item['proposed_rank'] = $secondary + 1;
                $secondary++;
                $buffers[] = $item;
            }
        }

        return $buffers;
    }

    private function assessCandidateForC19Buffer(array $item, array $paramset): array
    {
        $score = $this->floatValue($item['score_total'] ?? null);
        $fatal = [];
        $penalties = [];
        $currentFailures = $this->currentExtensionFailureReasons($item, $paramset);

        if ($score <= 0.0 || $score < 0.50) {
            $fatal[] = 'WATCHLIST_C19_SCORE_BELOW_BUFFER_FLOOR';
        }
        if ($this->hasReason($currentFailures, 'WATCHLIST_C17_SCORE_CHASE_BLOCKED') || $score >= 0.90) {
            $fatal[] = 'WATCHLIST_C19_SCORE_CHASE_BLOCKED';
        }

        foreach ($currentFailures as $reason) {
            if ($reason === 'WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL') {
                $penalties['dv20_bucket_overflow_penalty'] = 0.02;
            } elseif ($reason === 'WATCHLIST_C17_VOLUME_RECOVERY_RANGE_FAIL') {
                $penalties['volume_recovery_range_penalty'] = 0.03;
            } elseif ($reason === 'WATCHLIST_C17_ATR_SEGMENT_RANGE_FAIL') {
                $penalties['atr_segment_penalty'] = 0.02;
            } elseif ($reason === 'WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL') {
                $penalties['roc20_segment_penalty'] = 0.025;
            } elseif ($reason === 'WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL') {
                $penalties['roc5_pullback_penalty'] = 0.025;
            } elseif ($reason === 'WATCHLIST_C17_SCORE_WINDOW_LOW_FAIL') {
                $penalties['score_window_low_penalty'] = 0.04;
            } elseif ($reason === 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL') {
                $penalties['score_overextension_penalty'] = 0.04;
            } elseif ($reason === 'WATCHLIST_C17_SCORE_COMPONENT_COUNT_FAIL' || $reason === 'WATCHLIST_C17_SCORE_COMPONENT_AVERAGE_FAIL') {
                $fatal[] = 'WATCHLIST_C19_COMPONENT_BALANCE_FAIL';
            } elseif ($reason === 'WATCHLIST_C17_TREND_CONFIRM_COUNT_FAIL') {
                $fatal[] = 'WATCHLIST_C19_TREND_SAFETY_FAIL';
            }
        }

        $risk = $this->componentValue($item, 'risk');
        $breakout = $this->componentValue($item, 'breakout');
        if ($risk !== null && $risk < 0.42) {
            $fatal[] = 'WATCHLIST_C19_RISK_COMPONENT_FLOOR_FAIL';
        }
        if ($breakout !== null && $breakout < 0.36) {
            $fatal[] = 'WATCHLIST_C19_BREAKOUT_COMPONENT_FLOOR_FAIL';
        }

        $trendPasses = $this->trendPassCount($item);
        if ($trendPasses < 1) {
            $fatal[] = 'WATCHLIST_C19_TREND_SAFETY_FAIL';
        } elseif ($trendPasses < 2) {
            $penalties['trend_safety_penalty'] = 0.03;
        }

        $penaltyTotal = array_sum($penalties);
        $quality = max(0.0, min(1.0, $score - $penaltyTotal));
        if ($quality < 0.52) {
            $fatal[] = 'WATCHLIST_C19_QUALITY_SCORE_AFTER_PENALTY_FAIL';
        }

        return [
            'eligible' => array_values(array_unique($fatal)) === [],
            'fatal_reasons' => array_values(array_unique($fatal)),
            'current_extension_failures' => $currentFailures,
            'penalties' => $penalties,
            'penalty_total' => round($penaltyTotal, 6),
            'quality_score' => round($quality, 6),
            'trend_pass_count' => $trendPasses,
        ];
    }

    private function currentExtensionFailureReasons(array $item, array $paramset): array
    {
        $extension = $this->candidateSelectionExtension($paramset);
        if ($extension === []) {
            return [];
        }

        $failures = [];
        $bounds = is_array($extension['runtime_metric_bounds'] ?? null) ? $extension['runtime_metric_bounds'] : [];
        $score = $this->numericOrNull($item['score_total'] ?? null);

        if (($bounds['dv20_between_catalog_min_and_strong'] ?? false) === true) {
            $dv20 = $this->metricValue($item, 'dv20_idr');
            $min = $this->numericOrNull($paramset['liquidity']['min_dv20_idr'] ?? null);
            $max = $this->numericOrNull($paramset['liquidity']['dv20_strong_idr'] ?? null);
            if ($dv20 === null || $min === null || $max === null || $dv20 < $min || $dv20 > $max) {
                $failures[] = 'WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL';
            }
        }

        if (($bounds['vol_ratio_between_catalog_min_and_strong'] ?? false) === true) {
            $volRatio = $this->metricValue($item, 'vol_ratio');
            $min = $this->numericOrNull($paramset['volume']['min_vol_ratio'] ?? null);
            $max = $this->numericOrNull($paramset['volume']['strong_vol_ratio'] ?? null);
            if ($volRatio === null || $min === null || $max === null || $volRatio < $min || $volRatio > $max) {
                $failures[] = 'WATCHLIST_C17_VOLUME_RECOVERY_RANGE_FAIL';
            }
        }

        if (($bounds['atr14_between_catalog_min_and_max'] ?? false) === true) {
            $atr = $this->metricValue($item, 'atr14_pct');
            $min = $this->numericOrNull($paramset['risk']['min_atr14_pct'] ?? null);
            $max = $this->numericOrNull($paramset['risk']['max_atr14_pct'] ?? null);
            if ($atr === null || $min === null || $max === null || $atr < $min || $atr > $max) {
                $failures[] = 'WATCHLIST_C17_ATR_SEGMENT_RANGE_FAIL';
            }
        }

        if (($bounds['roc20_between_catalog_roc_lo_and_roc_hi'] ?? false) === true) {
            $roc20 = $this->metricValue($item, 'roc20');
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            if ($roc20 === null || $rocLo === null || $rocHi === null || $roc20 < $rocLo || $roc20 > $rocHi) {
                $failures[] = 'WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL';
            }
        }

        $shortTermBounds = is_array($extension['short_term_momentum_bounds'] ?? null)
            ? $extension['short_term_momentum_bounds']
            : [];
        foreach ($shortTermBounds as $metric => $range) {
            if (! is_array($range)) {
                continue;
            }
            $value = $this->metricValue($item, (string) $metric);
            $min = $this->numericOrNull($range['min'] ?? null);
            $max = $this->numericOrNull($range['max'] ?? null);
            if ($value === null || $min === null || $max === null || $value < $min || $value > $max) {
                $failures[] = 'WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL';
            }
        }

        [$scoreMin, $scoreMax] = $this->c17ScoreWindowBounds($extension, (string) ($paramset['bt_catalog']['row_code'] ?? ''));
        if ($scoreMin !== null && ($score === null || $score < $scoreMin)) {
            $failures[] = 'WATCHLIST_C17_SCORE_WINDOW_LOW_FAIL';
        }
        if ($scoreMax !== null && ($score === null || $score > $scoreMax)) {
            $failures[] = 'WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL';
        }

        $scoreChase = is_array($extension['blocked_score_chase'] ?? null) ? $extension['blocked_score_chase'] : [];
        $chaseMin = $this->numericOrNull($scoreChase['score_total_min'] ?? null);
        $chaseMax = $this->numericOrNull($scoreChase['score_total_max'] ?? null);
        if ($score !== null && $chaseMin !== null && $chaseMax !== null && $score >= $chaseMin && $score <= $chaseMax) {
            $failures[] = (string) ($scoreChase['reason_code'] ?? 'WATCHLIST_C17_SCORE_CHASE_BLOCKED');
        }

        $componentMinimums = is_array($extension['score_component_min'] ?? null)
            ? $extension['score_component_min']
            : [];
        $componentValues = [];
        $componentPassCount = 0;
        foreach ($componentMinimums as $component => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->componentValue($item, (string) $component);
            if ($value === null) {
                $failures[] = 'WATCHLIST_C17_SCORE_COMPONENT_COUNT_FAIL';
                continue;
            }
            $componentValues[] = $value;
            if ($value >= (float) $minimum) {
                $componentPassCount++;
            }
        }
        $requiredComponentPassCount = (int) ($extension['score_component_required_pass_count'] ?? count($componentMinimums));
        if ($componentMinimums !== [] && $componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C17_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== [] && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
            $failures[] = 'WATCHLIST_C17_SCORE_COMPONENT_AVERAGE_FAIL';
        }

        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        $trendPassCount = 0;
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->metricValue($item, (string) $metric);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendFloors !== [] && $trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C17_TREND_CONFIRM_COUNT_FAIL';
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }

    private function finalizeCoverageAwareProposal(array $buffers, array $monthly, array $paramset): array
    {
        usort($buffers, function (array $left, array $right): int {
            $monthCmp = strcmp((string) $left['month'], (string) $right['month']);
            if ($monthCmp !== 0) {
                return $monthCmp;
            }
            if (($left['quality_score'] ?? 0) == ($right['quality_score'] ?? 0)) {
                return ((int) ($left['ticker_id'] ?? 0)) <=> ((int) ($right['ticker_id'] ?? 0));
            }
            return (($right['quality_score'] ?? 0) <=> ($left['quality_score'] ?? 0));
        });

        $months = array_keys($monthly);
        $monthlyTarget = max(1, (int) ceil(self::CANONICAL_SAMPLE_TARGET / max(1, count($months))));
        $dailyCap = max(1, (int) ($paramset['recommendation']['max_recommended_items'] ?? 3));
        $selected = [];
        $selectedKeys = [];
        $selectedPerDate = [];

        foreach ($months as $month) {
            $monthSelected = 0;
            foreach ($buffers as $buffer) {
                if ($buffer['month'] !== $month || $monthSelected >= $monthlyTarget) {
                    continue;
                }
                if ($this->selectProposalBuffer($buffer, $selectedKeys, $selectedPerDate, $dailyCap)) {
                    $selected[] = $buffer;
                    $selectedKeys[$this->proposalKey($buffer)] = true;
                    $selectedPerDate[$buffer['trade_date']] = ($selectedPerDate[$buffer['trade_date']] ?? 0) + 1;
                    $monthSelected++;
                }
            }
        }

        usort($buffers, function (array $left, array $right): int {
            if (($left['quality_score'] ?? 0) == ($right['quality_score'] ?? 0)) {
                return ((int) ($left['ticker_id'] ?? 0)) <=> ((int) ($right['ticker_id'] ?? 0));
            }
            return (($right['quality_score'] ?? 0) <=> ($left['quality_score'] ?? 0));
        });
        foreach ($buffers as $buffer) {
            if (count($selected) >= self::CANONICAL_SAMPLE_TARGET) {
                break;
            }
            if ($this->selectProposalBuffer($buffer, $selectedKeys, $selectedPerDate, $dailyCap)) {
                $selected[] = $buffer;
                $selectedKeys[$this->proposalKey($buffer)] = true;
                $selectedPerDate[$buffer['trade_date']] = ($selectedPerDate[$buffer['trade_date']] ?? 0) + 1;
            }
        }

        foreach ($selected as $buffer) {
            $month = $buffer['month'];
            $monthly[$month]['proposed_recommended_count']++;
        }

        return [
            'top_count' => count(array_filter($buffers, function (array $buffer): bool {
                return ($buffer['proposed_plan_group'] ?? '') === 'top';
            })),
            'secondary_count' => count(array_filter($buffers, function (array $buffer): bool {
                return ($buffer['proposed_plan_group'] ?? '') === 'secondary';
            })),
            'candidate_buffer_count' => count($buffers),
            'recommended_count' => count($selected),
            'monthly' => $monthly,
        ];
    }

    private function selectProposalBuffer(array $buffer, array $selectedKeys, array $selectedPerDate, int $dailyCap): bool
    {
        if (isset($selectedKeys[$this->proposalKey($buffer)])) {
            return false;
        }
        if (($selectedPerDate[$buffer['trade_date']] ?? 0) >= $dailyCap) {
            return false;
        }
        return ($buffer['quality_score'] ?? 0.0) >= 0.52;
    }

    private function groupItems(array $grouped, string $group): array
    {
        $groupPayload = $grouped['groups'][$group] ?? [];
        if (! is_array($groupPayload)) {
            return [];
        }
        if (isset($groupPayload['items']) && is_array($groupPayload['items'])) {
            return array_values(array_filter($groupPayload['items'], 'is_array'));
        }
        return array_values(array_filter($groupPayload, 'is_array'));
    }

    private function recommendationOutputCount(array $recommended): int
    {
        if (isset($recommended['summary']['recommended_count']) && is_numeric($recommended['summary']['recommended_count'])) {
            return (int) $recommended['summary']['recommended_count'];
        }
        $items = array_values(array_filter($recommended['items'] ?? [], 'is_array'));
        $flagged = array_filter($items, function (array $item): bool {
            return ($item['recommended_flag'] ?? null) === true;
        });
        return count($flagged) > 0 ? count($flagged) : count($items);
    }

    private function recommendationEvaluatedCount(array $recommended): int
    {
        if (isset($recommended['summary']['evaluated_count']) && is_numeric($recommended['summary']['evaluated_count'])) {
            return (int) $recommended['summary']['evaluated_count'];
        }
        return count(array_filter($recommended['items'] ?? [], 'is_array'));
    }

    private function candidateRawCount(array $universe): int
    {
        if (isset($universe['input_candidate_count']) && is_numeric($universe['input_candidate_count'])) {
            return (int) $universe['input_candidate_count'];
        }
        if (isset($universe['summary']['raw_count']) && is_numeric($universe['summary']['raw_count'])) {
            return (int) $universe['summary']['raw_count'];
        }
        return count(array_filter($universe['universe_rows'] ?? [], 'is_array'));
    }

    private function candidateEligibleCount(array $universe): int
    {
        if (isset($universe['eligible_count']) && is_numeric($universe['eligible_count'])) {
            return (int) $universe['eligible_count'];
        }
        if (isset($universe['summary']['eligible_count']) && is_numeric($universe['summary']['eligible_count'])) {
            return (int) $universe['summary']['eligible_count'];
        }
        return count(array_filter($universe['eligible_candidates'] ?? [], 'is_array'));
    }

    private function candidateRejectedCount(array $universe): int
    {
        if (isset($universe['rejected_count']) && is_numeric($universe['rejected_count'])) {
            return (int) $universe['rejected_count'];
        }
        if (isset($universe['summary']['rejected_count']) && is_numeric($universe['summary']['rejected_count'])) {
            return (int) $universe['summary']['rejected_count'];
        }
        return count(array_filter($universe['rejected_candidates'] ?? [], 'is_array'));
    }

    private function scoredCandidateCount(array $scored): int
    {
        if (isset($scored['summary']['scored_count']) && is_numeric($scored['summary']['scored_count'])) {
            return (int) $scored['summary']['scored_count'];
        }
        return count(array_filter($scored['items'] ?? [], 'is_array'));
    }

    private function scoringExcludedCount(array $scored): int
    {
        if (isset($scored['summary']['excluded_count']) && is_numeric($scored['summary']['excluded_count'])) {
            return (int) $scored['summary']['excluded_count'];
        }
        return count(array_filter($scored['excluded'] ?? [], 'is_array'));
    }

    private function reasonCodesFromItem(array $item): array
    {
        $codes = [];
        foreach (['group_reason_code', 'reason_code', 'canonical_fail_reason_code'] as $key) {
            if (isset($item[$key]) && trim((string) $item[$key]) !== '') {
                $codes[] = trim((string) $item[$key]);
            }
        }
        $reasonPayload = $item['reason_codes'] ?? [];
        if (! is_array($reasonPayload)) {
            $reasonPayload = [$reasonPayload];
        }
        foreach ($reasonPayload as $code) {
            if (trim((string) $code) !== '') {
                $codes[] = trim((string) $code);
            }
        }
        return array_values(array_unique($codes));
    }

    private function addReasonCounts(array &$counts, array $reasonCodes): void
    {
        foreach ($reasonCodes as $reason) {
            $reason = trim((string) $reason);
            if ($reason === '' || $reason === 'UNKNOWN') {
                continue;
            }
            $counts[$reason] = ($counts[$reason] ?? 0) + 1;
        }
    }

    private function mergeReasonCounts(array $left, array $right): array
    {
        foreach ($right as $reason => $count) {
            $left[$reason] = ($left[$reason] ?? 0) + (int) $count;
        }
        return $left;
    }

    private function mergeDebugOutputKeys(array $debug, array $universe, array $scored, array $grouped, array $recommended): array
    {
        $debug['candidate_universe_keys'] = $this->mergeKeys($debug['candidate_universe_keys'], array_keys($universe));
        $debug['scored_output_keys'] = $this->mergeKeys($debug['scored_output_keys'], array_keys($scored));
        $debug['plan_grouping_keys'] = $this->mergeKeys($debug['plan_grouping_keys'], array_keys($grouped));
        $debug['plan_grouping_group_keys'] = $this->mergeKeys($debug['plan_grouping_group_keys'], array_keys($grouped['groups'] ?? []));
        $debug['recommendation_keys'] = $this->mergeKeys($debug['recommendation_keys'], array_keys($recommended));
        return $debug;
    }

    private function mergeKeys(array $left, array $right): array
    {
        $keys = array_values(array_unique(array_merge($left, array_map('strval', $right))));
        sort($keys);
        return $keys;
    }

    private function proposalKey(array $buffer): string
    {
        return (string) $buffer['trade_date'].'#'.(string) $buffer['ticker_id'];
    }

    private function monthSkeleton(array $strategyTradeDates): array
    {
        $months = [];
        foreach ($strategyTradeDates as $tradeDate) {
            $month = substr($tradeDate, 0, 7);
            $months[$month] = $months[$month] ?? [
                'month' => $month,
                'current_top_count' => 0,
                'current_secondary_count' => 0,
                'current_watch_only_count' => 0,
                'current_recommendation_input_count' => 0,
                'current_recommended_count' => 0,
                'proposed_top_buffer_count' => 0,
                'proposed_secondary_buffer_count' => 0,
                'proposed_recommended_count' => 0,
            ];
        }
        return $months;
    }

    private function crossParamSummary(array $diagnostics): array
    {
        $summary = [
            'max_current_top_count' => 0,
            'max_current_secondary_count' => 0,
            'max_current_recommended_count' => 0,
            'max_proposed_top_count' => 0,
            'max_proposed_secondary_count' => 0,
            'max_proposed_recommended_count' => 0,
            'params_with_secondary_zero_current' => 0,
            'params_with_proposed_secondary_recovery' => 0,
            'params_with_non_unknown_drop_reasons' => 0,
            'params_with_proposed_sample_target_reached' => 0,
        ];
        foreach ($diagnostics as $diag) {
            $current = $diag['current_path'];
            $proposed = $diag['proposed_path'];
            $summary['max_current_top_count'] = max($summary['max_current_top_count'], (int) $current['plan_top_picks_count']);
            $summary['max_current_secondary_count'] = max($summary['max_current_secondary_count'], (int) $current['plan_secondary_count']);
            $summary['max_current_recommended_count'] = max($summary['max_current_recommended_count'], (int) $current['recommendation_output_count']);
            $summary['max_proposed_top_count'] = max($summary['max_proposed_top_count'], (int) $proposed['top_count']);
            $summary['max_proposed_secondary_count'] = max($summary['max_proposed_secondary_count'], (int) $proposed['secondary_count']);
            $summary['max_proposed_recommended_count'] = max($summary['max_proposed_recommended_count'], (int) $proposed['recommended_count']);
            if ((int) $current['plan_secondary_count'] === 0) {
                $summary['params_with_secondary_zero_current']++;
            }
            if ((int) $current['plan_secondary_count'] === 0 && (int) $proposed['secondary_count'] > 0) {
                $summary['params_with_proposed_secondary_recovery']++;
            }
            if (($diag['dominant_current_drop_reasons'] ?? []) !== []) {
                $summary['params_with_non_unknown_drop_reasons']++;
            }
            if (($proposed['target_reached_by_diagnostic_only'] ?? false) === true) {
                $summary['params_with_proposed_sample_target_reached']++;
            }
        }
        return $summary;
    }

    private function sourcePathFindings(): array
    {
        return [
            'candidate_universe' => 'DV20, volume ratio, and ATR canonical minima/maxima are hard eligibility gates before scoring.',
            'scoring' => 'Scored pool can remain large because score_total is ranked after eligible candidates pass canonical source guards.',
            'plan_grouping' => 'C17 candidate_selection_extension applies score window, DV20/volume recovery range, ATR segment, ROC5/ROC20, component floor, trend floor, and score-chase checks as hard rejects before TOP/SECONDARY allocation.',
            'plan_grouping_output_shape_v3' => 'WatchlistPlanGroupingService exposes groups.TOP_PICKS and groups.SECONDARY as item arrays, not groups.TOP_PICKS.items; C19 v3 maps both shapes safely.',
            'recommendation' => 'Recommendation output count must come from summary.recommended_count when available; items can include evaluated but not selected entries.',
            'secondary_zero_cause' => 'SECONDARY is not a separate recovery buffer today; it receives only remaining candidates after TOP assignment that still survive the same hard selection extension and secondary cutoff.',
            'price_runtime' => 'C19 diagnostic intentionally does not call price runtime; canonical ENTRY/EXIT/HOLD/FEE/SLIP/GAP/PX model remains untouched.',
        ];
    }

    private function proposedSelectionModel(): array
    {
        return [
            'name' => 'C19_V3_SELECTION_MODEL_REDESIGN_MAPPING_AND_SELECTOR_SIMULATION',
            'principle' => 'Keep canonical candidate-universe gates fatal; transform selected C17 post-scoring extension hard rejects into bounded ranking penalties for already-scored candidates.',
            'selector_source' => 'scored candidates, not existing TOP/SECONDARY after collapse',
            'secondary_role' => 'Controlled candidate buffer, not a final trade signal; still must pass recommendation and future price evaluation.',
            'monthly_coverage_selector' => 'Rank eligible buffers per month first, then fill globally up to sample target; no month blacklist and no forced bad picks.',
            'fatal_guards_preserved' => [
                'candidate universe DV20 minimum because only scored candidates are used',
                'candidate universe volume minimum because only scored candidates are used',
                'candidate universe ATR min/max because only scored candidates are used',
                'score chase block >= 0.90',
                'risk component floor',
                'breakout component floor',
                'minimum component balance',
                'minimum trend safety',
            ],
            'ranking_penalty_candidates' => [
                'DV20 above strong liquidity bucket after canonical minimum passed',
                'volume above strong volume bucket after canonical minimum passed',
                'ATR segment miss inside canonical min/max',
                'ROC5 pullback miss',
                'ROC20 segment miss',
                'score window low or overextension below score-chase block',
                'borderline trend confirmation with at least one trend pass',
            ],
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'C19_STRATEGY_MODEL_REDESIGN' => true,
            'C19_NOT_CATALOG_CHURN' => true,
            'C19_SELECTION_MODEL_ANALYSIS_DONE' => true,
            'C19_DIAGNOSTIC_IMPLEMENTED' => true,
            'C19_V3_DIAGNOSTIC_MAPPING_FIXED' => true,
            'C19_V3_SELECTOR_SIMULATION_FROM_SCORED_POOL' => true,
            'C19_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'C19_CATALOG_CODE' => 'NOT_CREATED',
            'C18_UNCHANGED' => true,
            'C01_TO_C18_IMMUTABLE' => true,
            'WATCHLIST_SCOPE_ONLY' => true,
            'PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'no_best_of_failed_binding' => true,
            'no_ticker_blacklist' => true,
            'no_month_blacklist' => true,
            'no_sector_whitelist' => true,
            'canonical_evaluation_model_unchanged' => [
                'ENTRY' => 'NEXT_OPEN',
                'EXIT' => 'STOP_TP_OR_TIME',
                'HOLD' => 5,
                'FEE' => 'IDR_FIXED',
                'SLIP' => 0,
                'GAP' => 'OPEN',
                'PX' => 'IDX_BANDS',
            ],
        ];
    }

    private function candidateSelectionExtension(array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? [];
        return is_array($extension) ? $extension : [];
    }

    private function c17ScoreWindowBounds(array $extension, string $rowCode): array
    {
        $windows = is_array($extension['score_windows_by_row_code'] ?? null)
            ? $extension['score_windows_by_row_code']
            : [];
        $window = is_array($windows[$rowCode] ?? null) ? $windows[$rowCode] : [];

        return [
            $this->numericOrNull($window['min'] ?? $extension['score_total_min'] ?? null),
            $this->numericOrNull($window['max'] ?? $extension['score_total_max'] ?? null),
        ];
    }

    private function metricValue(array $item, string $metric): ?float
    {
        $metrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null) ? $item['factor_breakdown']['momentum'] : [];
        $breakout = is_array($item['factor_breakdown']['breakout'] ?? null) ? $item['factor_breakdown']['breakout'] : [];
        $aliases = [
            $metric,
            $metric === 'close_to_ma20_pct' ? 'close_vs_ma20_pct' : $metric,
            $metric === 'close_vs_ma20_pct' ? 'close_to_ma20_pct' : $metric,
            $metric === 'close_to_ma50_pct' ? 'close_vs_ma50_pct' : $metric,
            $metric === 'close_vs_ma50_pct' ? 'close_to_ma50_pct' : $metric,
            $metric === 'roc5' ? 'roc_5' : $metric,
            $metric === 'roc20' ? 'roc_20' : $metric,
        ];
        foreach ([$metrics, $momentum, $breakout] as $source) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $source)) {
                    $value = $this->numericOrNull($source[$alias]);
                    if ($value !== null) {
                        return $value;
                    }
                }
            }
        }
        return null;
    }

    private function componentValue(array $item, string $component): ?float
    {
        $components = is_array($item['score_components'] ?? null) ? $item['score_components'] : [];
        $aliases = [$component, 'score_'.$component];
        if (strpos($component, 'score_') === 0) {
            $aliases[] = substr($component, 6);
        }

        foreach (array_values(array_unique($aliases)) as $key) {
            if (array_key_exists($key, $components)) {
                return $this->numericOrNull($components[$key]);
            }
        }
        return null;
    }

    private function trendPassCount(array $item): int
    {
        $checks = [
            [$this->metricValue($item, 'ma20_slope_pct'), -0.022],
            [$this->metricValue($item, 'rs_20_vs_ihsg'), -0.055],
            [$this->metricValue($item, 'close_vs_ma20_pct'), -0.065],
            [$this->metricValue($item, 'close_vs_ma50_pct'), -0.095],
        ];
        $count = 0;
        foreach ($checks as $check) {
            if ($check[0] !== null && $check[0] >= $check[1]) {
                $count++;
            }
        }
        return $count;
    }

    private function hasReason(array $reasons, string $needle): bool
    {
        return in_array($needle, $reasons, true);
    }

    private function topCounts(array $counts, int $limit): array
    {
        arsort($counts);
        $out = [];
        foreach (array_slice($counts, 0, $limit, true) as $key => $count) {
            $out[] = ['reason_code' => (string) $key, 'count' => (int) $count];
        }
        return $out;
    }

    private function normalizeDateList(array $dates): array
    {
        $out = [];
        foreach ($dates as $date) {
            $date = (string) $date;
            if ($this->validDate($date)) {
                $out[] = $date;
            }
        }
        $out = array_values(array_unique($out));
        sort($out);
        return $out;
    }

    private function paramIds($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $ids = [];
        foreach ($value as $item) {
            if (! is_numeric($item) || (int) $item <= 0) {
                return false;
            }
            $ids[] = (int) $item;
        }
        $ids = array_values(array_unique($ids));
        sort($ids);
        return $ids;
    }

    private function numericOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function floatValue($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function validDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    private function writeArtifact(string $path, array $artifact): array
    {
        $dir = dirname($path);
        if ($dir !== '' && ! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write artifact file.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        $normalized = $payload;
        unset($normalized['artifact_hash'], $normalized['generated_at']);
        return sha1(json_encode($this->normalize($normalized), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        $keys = array_keys($value);
        if ($keys !== range(0, count($value) - 1)) {
            ksort($value);
        }
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_replace_recursive([
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'c19_catalog_implementation_deferred' => 1,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ], $extra);
    }

    private function reasonCode(\Throwable $e): string
    {
        $message = strtoupper($e->getMessage());
        if (strpos($message, 'CATALOG') !== false) {
            return 'WS_BT_C19_SOURCE_CATALOG_UNAVAILABLE';
        }
        return 'WS_BT_C19_SOURCE_AUDIT_FAILED';
    }
}
