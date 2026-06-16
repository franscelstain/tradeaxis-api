<?php

namespace App\Application\Watchlist\Services;

class WatchlistPlanGroupingService
{
    public const DEFAULT_PARAMSET = [
        'policy_code' => 'WS',
        'policy_version' => 'WS_EOD_RUNTIME',
        'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
        'grouping' => [
            'grouping_mode' => 'PLAN_GROUPING_DETERMINISTIC',
            'top_min_score_q' => null,
            'secondary_min_score_q' => null,
            'top_picks' => [
                'min_score_total' => 0.70,
                'max_items' => 5,
            ],
            'secondary' => [
                'min_score_total' => 0.55,
                'max_items' => 10,
            ],
            'watch_only' => [
                'min_score_total' => 0.40,
                'max_items' => 20,
            ],
            'avoid' => [
                'max_score_total_below' => 0.40,
            ],
            'sort_keys' => [
                'score_total_desc',
                'score_breakout_desc',
                'score_momentum_desc',
                'dv20_idr_desc',
                'atr14_pct_asc',
                'ticker_id_asc',
            ],
        ],
    ];

    private const GROUPS = [
        'TOP_PICKS',
        'SECONDARY',
        'WATCH_ONLY',
        'AVOID',
    ];

    private WatchlistScoringService $scoringService;

    public function __construct(WatchlistScoringService $scoringService = null)
    {
        $this->scoringService = $scoringService ?: new WatchlistScoringService();
    }

    public function groupForTradeDate(string $tradeDate, array $paramset = []): array
    {
        $resolvedParamset = $this->resolveParamset($paramset);
        $scoredOutput = $this->scoringService->scoreForTradeDate($tradeDate, $resolvedParamset);

        return $this->groupScoredOutput($scoredOutput, $resolvedParamset, $tradeDate);
    }

    public function groupScoredOutput(array $scoredOutput, array $paramset = [], string $tradeDate = ''): array
    {
        $resolvedParamset = $this->resolveParamset($paramset);
        $payload = $this->basePayload($scoredOutput, $resolvedParamset, $tradeDate);
        $paramsetErrors = $this->validateParamset($resolvedParamset);

        if ($paramsetErrors !== []) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_PLAN_GROUPING_PARAMSET_INVALID';
            $payload['plan_grouping_reason_code'] = 'WATCHLIST_PLAN_GROUPING_PARAMSET_INVALID';
            $payload['paramset_errors'] = $paramsetErrors;

            return $payload;
        }

        if (! ($scoredOutput['is_ready'] ?? $scoredOutput['ready'] ?? false)) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_PLAN_GROUPING_SOURCE_NOT_READY';
            $payload['plan_grouping_reason_code'] = 'WATCHLIST_PLAN_GROUPING_SOURCE_NOT_READY';
            $payload['source_reason_code'] = $scoredOutput['reason_code'] ?? null;

            return $payload;
        }

        $items = $this->deduplicateBestItems($this->collectEligibleItems(
            $scoredOutput['items'] ?? [],
            $payload,
            $resolvedParamset
        ));
        $resolvedThresholds = $this->resolvedThresholds($items, $resolvedParamset);
        $payload['group_contract']['resolved_thresholds'] = $resolvedThresholds;
        $payload['cutoff_manifest'] = $resolvedThresholds;

        foreach ($items as $item) {
            $scoreTotal = (float) $item['score_total'];

            if ($scoreTotal >= $resolvedThresholds['top_picks_min_score_total']
                && count($payload['groups']['TOP_PICKS']) < $resolvedParamset['grouping']['top_picks']['max_items']) {
                $payload['groups']['TOP_PICKS'][] = $this->groupItem($item, 'TOP_PICKS', 'WS_PLAN_TOP_PICK', count($payload['groups']['TOP_PICKS']) + 1);
                continue;
            }

            if ($scoreTotal >= $resolvedThresholds['secondary_min_score_total']
                && count($payload['groups']['SECONDARY']) < $resolvedParamset['grouping']['secondary']['max_items']) {
                $payload['groups']['SECONDARY'][] = $this->groupItem($item, 'SECONDARY', 'WS_PLAN_SECONDARY', count($payload['groups']['SECONDARY']) + 1);
                continue;
            }

            if ($scoreTotal >= $resolvedParamset['grouping']['watch_only']['min_score_total']
                && count($payload['groups']['WATCH_ONLY']) < $resolvedParamset['grouping']['watch_only']['max_items']) {
                $payload['groups']['WATCH_ONLY'][] = $this->groupItem($item, 'WATCH_ONLY', 'WS_PLAN_WATCH_ONLY', count($payload['groups']['WATCH_ONLY']) + 1);
                continue;
            }

            $payload['groups']['AVOID'][] = $this->avoidItem($item, 'WS_PLAN_AVOID_LOW_SCORE');
        }

        foreach (($scoredOutput['excluded'] ?? []) as $excluded) {
            $avoidItem = $this->avoidItem($excluded, 'WS_PLAN_AVOID_EXCLUDED');
            $payload['excluded'][] = $avoidItem;
            $payload['groups']['AVOID'][] = $avoidItem;
        }

        $this->fillPlanRanks($payload);

        $payload['summary'] = [
            'input_count' => count($scoredOutput['items'] ?? []) + count($scoredOutput['excluded'] ?? []),
            'top_picks_count' => count($payload['groups']['TOP_PICKS']),
            'secondary_count' => count($payload['groups']['SECONDARY']),
            'watch_only_count' => count($payload['groups']['WATCH_ONLY']),
            'avoid_count' => count($payload['groups']['AVOID']),
            'excluded_count' => count($payload['excluded']),
        ];
        $payload['ready'] = true;
        $payload['is_ready'] = true;
        $payload['reason_code'] = 'WATCHLIST_PLAN_GROUPING_READY';
        $payload['plan_grouping_reason_code'] = 'WATCHLIST_PLAN_GROUPING_READY';

        return $payload;
    }

    public static function defaultParamset(): array
    {
        return self::DEFAULT_PARAMSET;
    }

    private function basePayload(array $scoredOutput, array $paramset, string $tradeDate): array
    {
        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_NOT_EVALUATED',
            'plan_grouping_reason_code' => 'WATCHLIST_PLAN_GROUPING_NOT_EVALUATED',
            'trade_date' => isset($scoredOutput['trade_date']) ? (string) $scoredOutput['trade_date'] : $tradeDate,
            'trade_date_effective' => $scoredOutput['trade_date_effective'] ?? null,
            'publication_id' => $scoredOutput['publication_id'] ?? null,
            'publication_version' => $scoredOutput['publication_version'] ?? null,
            'run_id' => $scoredOutput['run_id'] ?? null,
            'policy_code' => $paramset['policy_code'],
            'policy_version' => $paramset['policy_version'],
            'paramset_code' => $paramset['paramset_code'],
            'source_contract' => [
                'consumer' => 'WatchlistPlanGroupingService',
                'upstream' => 'WatchlistScoringService',
                'no_raw_market_data' => true,
                'no_latest_shortcut' => true,
                'no_recommendation' => true,
                'no_confirm' => true,
                'no_execution' => true,
                'no_backtest' => true,
            ],
            'group_contract' => [
                'grouping_mode' => $paramset['grouping']['grouping_mode'],
                'groups' => self::GROUPS,
                'not_final_recommendation' => true,
                'sort_source' => 'WatchlistScoringService.score_contract.sort_keys',
                'sort_keys' => $paramset['grouping']['sort_keys'],
                'dedupe_key' => 'ticker_id',
                'thresholds' => [
                    'top_picks_min_score_total' => $paramset['grouping']['top_picks']['min_score_total'],
                    'secondary_min_score_total' => $paramset['grouping']['secondary']['min_score_total'],
                    'watch_only_min_score_total' => $paramset['grouping']['watch_only']['min_score_total'],
                    'avoid_max_score_total_below' => $paramset['grouping']['avoid']['max_score_total_below'],
                ],
                'limits' => [
                    'top_picks_max_items' => $paramset['grouping']['top_picks']['max_items'],
                    'secondary_max_items' => $paramset['grouping']['secondary']['max_items'],
                    'watch_only_max_items' => $paramset['grouping']['watch_only']['max_items'],
                ],
            ],
            'paramset_snapshot' => [
                'policy_code' => $paramset['policy_code'],
                'policy_version' => $paramset['policy_version'],
                'paramset_code' => $paramset['paramset_code'],
                'grouping' => $paramset['grouping'],
            ],
            'groups' => [
                'TOP_PICKS' => [],
                'SECONDARY' => [],
                'WATCH_ONLY' => [],
                'AVOID' => [],
            ],
            'excluded' => [],
            'summary' => [
                'input_count' => 0,
                'top_picks_count' => 0,
                'secondary_count' => 0,
                'watch_only_count' => 0,
                'avoid_count' => 0,
                'excluded_count' => 0,
            ],
            'paramset_errors' => [],
        ];
    }

    private function collectEligibleItems(array $items, array &$payload, array $paramset): array
    {
        $eligible = [];

        foreach ($items as $item) {
            if (! ($item['eligible_score'] ?? false)
                || ! array_key_exists('score_total', $item)
                || ! $this->isNumericValue($item['score_total'])
                || (float) $item['score_total'] < 0.0
                || (float) $item['score_total'] > 1.0
                || $this->intOrNull($item['ticker_id'] ?? null) === null) {
                $avoidItem = $this->avoidItem($item, 'WS_PLAN_AVOID_EXCLUDED');
                $payload['excluded'][] = $avoidItem;
                $payload['groups']['AVOID'][] = $avoidItem;
                continue;
            }

            $qualityReasonCodes = $this->candidateSelectionExtensionFailures($item, $paramset);
            if ($qualityReasonCodes !== []) {
                $floorReasonCode = end($qualityReasonCodes) ?: 'WATCHLIST_ENTRY_QUALITY_FLOOR_FAIL';
                $payload['excluded'][] = $this->avoidItem(
                    $this->withReasonCodes($item, $qualityReasonCodes),
                    (string) $floorReasonCode
                );
                continue;
            }

            $eligible[] = $item;
        }

        return $this->sortItems($eligible);
    }

    private function deduplicateBestItems(array $items): array
    {
        $seen = [];
        $unique = [];

        foreach ($items as $item) {
            $tickerId = (int) $item['ticker_id'];
            if (isset($seen[$tickerId])) {
                continue;
            }

            $seen[$tickerId] = true;
            $unique[] = $item;
        }

        return $unique;
    }

    private function resolvedThresholds(array $items, array $paramset): array
    {
        $scores = array_values(array_map(function (array $item): float {
            return (float) $item['score_total'];
        }, $items));
        sort($scores, SORT_NUMERIC);

        $topQuantile = $paramset['grouping']['top_min_score_q'];
        $secondaryQuantile = $paramset['grouping']['secondary_min_score_q'];
        $quantileMode = $topQuantile !== null && $secondaryQuantile !== null && $scores !== [];

        return [
            'mode' => $quantileMode ? 'DAILY_SCORE_QUANTILE' : 'STATIC_SCORE_THRESHOLD',
            'top_min_score_q' => $topQuantile,
            'secondary_min_score_q' => $secondaryQuantile,
            'top_picks_min_score_total' => $quantileMode
                ? $this->percentile($scores, (float) $topQuantile)
                : (float) $paramset['grouping']['top_picks']['min_score_total'],
            'secondary_min_score_total' => $quantileMode
                ? $this->percentile($scores, (float) $secondaryQuantile)
                : (float) $paramset['grouping']['secondary']['min_score_total'],
            'score_count' => count($scores),
            'score_payload_hash' => sha1(json_encode($scores, JSON_UNESCAPED_SLASHES)),
        ];
    }

    private function percentile(array $values, float $percentile): float
    {
        $index = (count($values) - 1) * $percentile;
        $lower = (int) floor($index);
        $upper = (int) ceil($index);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $index - $lower;

        return (float) $values[$lower] + (((float) $values[$upper] - (float) $values[$lower]) * $weight);
    }

    private function groupItem(array $item, string $group, string $reasonCode, int $groupRank): array
    {
        $reasonCodes = $item['reason_codes'] ?? [];
        $reasonCodes[] = $reasonCode;

        $item['plan_group'] = $group;
        $item['group_semantic'] = $group;
        $item['group_reason_code'] = $reasonCode;
        $item['group_rank'] = $groupRank;
        $item['reason_codes'] = array_values(array_unique($reasonCodes));

        return $item;
    }

    private function avoidItem(array $item, string $reasonCode): array
    {
        $reasonCodes = $item['reason_codes'] ?? [];
        $reasonCodes[] = $reasonCode;

        $item['ticker_id'] = $this->intOrNull($item['ticker_id'] ?? null);
        $item['ticker_code'] = strtoupper(trim((string) ($item['ticker_code'] ?? '')));
        $item['plan_group'] = 'AVOID';
        $item['group_semantic'] = 'AVOID';
        $item['group_reason_code'] = $reasonCode;
        $item['reason_codes'] = array_values(array_unique($reasonCodes));
        $item['eligible_plan_group'] = false;

        return $item;
    }

    private function withReasonCodes(array $item, array $reasonCodes): array
    {
        $item['reason_codes'] = array_values(array_unique(array_merge(
            $item['reason_codes'] ?? [],
            $reasonCodes
        )));

        return $item;
    }

    private function c04QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C04_BALANCED_COMPONENT_AND_TREND_FLOOR') {
            return [];
        }

        $failures = [];
        $componentMinimums = is_array($extension['score_component_min'] ?? null)
            ? $extension['score_component_min']
            : [];
        foreach ($componentMinimums as $component => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->componentValue($item, (string) $component);
            if ($value === null || $value < (float) $minimum) {
                $failures[] = 'WATCHLIST_C04_SCORE_COMPONENT_FLOOR_FAIL';
                break;
            }
        }

        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];
        $breakout = is_array($item['factor_breakdown']['breakout'] ?? null)
            ? $item['factor_breakdown']['breakout']
            : [];
        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->numericOrNull($momentum[$metric] ?? null);
            if ($value === null || $value < (float) $minimum) {
                $failures[] = 'WATCHLIST_C04_TREND_CONFIRM_FAIL';
                break;
            }
        }

        if (($extension['raw_setup_guards']['roc20_between_catalog_roc_lo_and_roc_hi'] ?? false) === true) {
            $roc20 = $this->numericOrNull($momentum['roc20'] ?? null);
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            if ($roc20 === null || $rocLo === null || $rocHi === null || $roc20 < $rocLo || $roc20 > $rocHi) {
                $failures[] = 'WATCHLIST_C04_SETUP_RANGE_FAIL';
            }
        }

        if (($extension['raw_setup_guards']['close_to_hh20_between_negative_bo_near_below_and_bo_max_ext'] ?? false) === true) {
            $closeToHh20 = $this->numericOrNull($breakout['close_to_hh20_pct'] ?? null);
            $nearBelow = $this->numericOrNull($paramset['setup']['bo_near_below_pct'] ?? null);
            $maxExtension = $this->numericOrNull($paramset['setup']['bo_max_ext_pct'] ?? null);
            if ($closeToHh20 === null || $nearBelow === null || $maxExtension === null
                || $closeToHh20 < -$nearBelow || $closeToHh20 > $maxExtension) {
                $failures[] = 'WATCHLIST_C04_SETUP_RANGE_FAIL';
            }
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C04_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }

    private function candidateSelectionExtensionFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)) {
            return [];
        }

        $mode = (string) ($extension['mode'] ?? '');
        if ($mode === 'C04_BALANCED_COMPONENT_AND_TREND_FLOOR') {
            return $this->c04QualityFloorFailures($item, $paramset);
        }
        if ($mode === 'C05_SOFT_BALANCED_SAMPLE_STABILITY_FLOOR') {
            return $this->c05QualityFloorFailures($item, $paramset);
        }
        if ($mode === 'C06_MODERATE_LIQUIDITY_VOLUME_ROC_STABILITY_FLOOR') {
            return $this->c06QualityFloorFailures($item, $paramset);
        }
        if ($mode === 'C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION') {
            return $this->c07QualityFloorFailures($item, $paramset);
        }
        if ($mode === 'C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION') {
            return $this->c15QualityFloorFailures($item, $paramset);
        }
        if ($mode === 'C16_CONTROLLED_PULLBACK_SCORE_WINDOW_VOLUME_QUALITY_RECOVERY') {
            return $this->c16QualityFloorFailures($item, $paramset);
        }
        if ($mode === 'C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16') {
            return $this->c17QualityFloorFailures($item, $paramset);
        }

        return [];
    }

    private function c05QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C05_SOFT_BALANCED_SAMPLE_STABILITY_FLOOR') {
            return [];
        }

        $failures = [];
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
                $failures[] = 'WATCHLIST_C05_SCORE_COMPONENT_COUNT_FAIL';
                continue;
            }
            $componentValues[] = $value;
            if ($value >= (float) $minimum) {
                $componentPassCount++;
            }
        }

        $requiredComponentPassCount = (int) ($extension['score_component_required_pass_count'] ?? count($componentMinimums));
        if ($componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C05_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== []
            && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
            $failures[] = 'WATCHLIST_C05_SCORE_COMPONENT_AVERAGE_FAIL';
        }

        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];
        $breakout = is_array($item['factor_breakdown']['breakout'] ?? null)
            ? $item['factor_breakdown']['breakout']
            : [];
        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        $trendPassCount = 0;
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->numericOrNull($momentum[$metric] ?? null);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C05_TREND_CONFIRM_COUNT_FAIL';
        }

        $rawSetupGuards = is_array($extension['raw_setup_guards'] ?? null)
            ? $extension['raw_setup_guards']
            : [];
        if (($rawSetupGuards['roc20_between_catalog_roc_lo_and_roc_hi_with_tolerance'] ?? false) === true) {
            $roc20 = $this->numericOrNull($momentum['roc20'] ?? null);
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            $lowerTolerance = $this->numericOrNull($rawSetupGuards['roc20_lower_tolerance'] ?? null) ?? 0.0;
            $upperTolerance = $this->numericOrNull($rawSetupGuards['roc20_upper_tolerance'] ?? null) ?? 0.0;
            if ($roc20 === null || $rocLo === null || $rocHi === null
                || $roc20 < ($rocLo - $lowerTolerance) || $roc20 > ($rocHi + $upperTolerance)) {
                $failures[] = 'WATCHLIST_C05_SETUP_RANGE_FAIL';
            }
        }

        if (($rawSetupGuards['close_to_hh20_between_negative_bo_near_below_and_bo_max_ext_with_tolerance'] ?? false) === true) {
            $closeToHh20 = $this->numericOrNull($breakout['close_to_hh20_pct'] ?? null);
            $nearBelow = $this->numericOrNull($paramset['setup']['bo_near_below_pct'] ?? null);
            $maxExtension = $this->numericOrNull($paramset['setup']['bo_max_ext_pct'] ?? null);
            $lowerTolerance = $this->numericOrNull($rawSetupGuards['close_to_hh20_lower_tolerance'] ?? null) ?? 0.0;
            $upperTolerance = $this->numericOrNull($rawSetupGuards['close_to_hh20_upper_tolerance'] ?? null) ?? 0.0;
            if ($closeToHh20 === null || $nearBelow === null || $maxExtension === null
                || $closeToHh20 < (-$nearBelow - $lowerTolerance) || $closeToHh20 > ($maxExtension + $upperTolerance)) {
                $failures[] = 'WATCHLIST_C05_SETUP_RANGE_FAIL';
            }
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C05_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }

    private function c06QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C06_MODERATE_LIQUIDITY_VOLUME_ROC_STABILITY_FLOOR') {
            return [];
        }

        $failures = [];
        $scoreMetrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];
        $breakout = is_array($item['factor_breakdown']['breakout'] ?? null)
            ? $item['factor_breakdown']['breakout']
            : [];
        $bounds = is_array($extension['runtime_metric_bounds'] ?? null)
            ? $extension['runtime_metric_bounds']
            : [];

        if (($bounds['dv20_between_catalog_min_and_strong'] ?? false) === true) {
            $dv20 = $this->numericOrNull($scoreMetrics['dv20_idr'] ?? null);
            $min = $this->numericOrNull($paramset['liquidity']['min_dv20_idr'] ?? null);
            $max = $this->numericOrNull($paramset['liquidity']['dv20_strong_idr'] ?? null);
            if ($dv20 === null || $min === null || $max === null || $dv20 < $min || $dv20 > $max) {
                $failures[] = 'WATCHLIST_C06_LIQUIDITY_STABILITY_RANGE_FAIL';
            }
        }

        if (($bounds['vol_ratio_between_catalog_min_and_strong'] ?? false) === true) {
            $volRatio = $this->numericOrNull($scoreMetrics['vol_ratio'] ?? null);
            $min = $this->numericOrNull($paramset['volume']['min_vol_ratio'] ?? null);
            $max = $this->numericOrNull($paramset['volume']['strong_vol_ratio'] ?? null);
            if ($volRatio === null || $min === null || $max === null || $volRatio < $min || $volRatio > $max) {
                $failures[] = 'WATCHLIST_C06_VOLUME_STABILITY_RANGE_FAIL';
            }
        }

        if (($bounds['atr14_between_catalog_min_and_max'] ?? false) === true) {
            $atr = $this->numericOrNull($scoreMetrics['atr14_pct'] ?? null);
            $min = $this->numericOrNull($paramset['risk']['min_atr14_pct'] ?? null);
            $max = $this->numericOrNull($paramset['risk']['max_atr14_pct'] ?? null);
            if ($atr === null || $min === null || $max === null || $atr < $min || $atr > $max) {
                $failures[] = 'WATCHLIST_C06_ATR_REGIME_RANGE_FAIL';
            }
        }

        if (($bounds['roc20_between_catalog_roc_lo_and_roc_hi'] ?? false) === true) {
            $roc20 = $this->numericOrNull($momentum['roc20'] ?? null);
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            if ($roc20 === null || $rocLo === null || $rocHi === null || $roc20 < $rocLo || $roc20 > $rocHi) {
                $failures[] = 'WATCHLIST_C06_ROC_REGIME_RANGE_FAIL';
            }
        }

        if (($bounds['close_to_hh20_between_negative_bo_near_below_and_bo_max_ext'] ?? false) === true) {
            $closeToHh20 = $this->numericOrNull($breakout['close_to_hh20_pct'] ?? null);
            $nearBelow = $this->numericOrNull($paramset['setup']['bo_near_below_pct'] ?? null);
            $maxExtension = $this->numericOrNull($paramset['setup']['bo_max_ext_pct'] ?? null);
            if ($closeToHh20 === null || $nearBelow === null || $maxExtension === null
                || $closeToHh20 < -$nearBelow || $closeToHh20 > $maxExtension) {
                $failures[] = 'WATCHLIST_C06_BREAKOUT_RANGE_FAIL';
            }
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
                $failures[] = 'WATCHLIST_C06_SCORE_COMPONENT_COUNT_FAIL';
                continue;
            }
            $componentValues[] = $value;
            if ($value >= (float) $minimum) {
                $componentPassCount++;
            }
        }

        $requiredComponentPassCount = (int) ($extension['score_component_required_pass_count'] ?? count($componentMinimums));
        if ($componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C06_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== []
            && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
            $failures[] = 'WATCHLIST_C06_SCORE_COMPONENT_AVERAGE_FAIL';
        }

        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        $trendPassCount = 0;
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->numericOrNull($momentum[$metric] ?? null);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C06_TREND_CONFIRM_COUNT_FAIL';
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C06_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }

    private function c07QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION') {
            return [];
        }

        $failures = [];
        $scoreMetrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];
        $breakout = is_array($item['factor_breakdown']['breakout'] ?? null)
            ? $item['factor_breakdown']['breakout']
            : [];

        $eventRiskFlags = is_array($extension['event_risk_disallow_flags'] ?? null)
            ? $extension['event_risk_disallow_flags']
            : [];
        foreach ($eventRiskFlags as $metric) {
            $value = $scoreMetrics[$metric] ?? null;
            if ($value !== null && (int) $value === 1) {
                $failures[] = 'WATCHLIST_C07_EVENT_RISK_FLAG_FAIL';
                break;
            }
        }

        $rangeBounds = is_array($extension['range_position_20_pct_between'] ?? null)
            ? $extension['range_position_20_pct_between']
            : [];
        if ($rangeBounds !== []) {
            $rangePosition = $this->fractionOrNull($scoreMetrics['range_position_20_pct'] ?? null);
            $min = $this->numericOrNull($rangeBounds['min'] ?? null);
            $max = $this->numericOrNull($rangeBounds['max'] ?? null);
            if ($rangePosition === null || $min === null || $max === null || $rangePosition < $min || $rangePosition > $max) {
                $failures[] = 'WATCHLIST_C07_RANGE_POSITION_FAIL';
            }
        }

        $confirmationFloors = is_array($extension['confirmation_metric_floor'] ?? null)
            ? $extension['confirmation_metric_floor']
            : [];
        $confirmationPassCount = 0;
        foreach ($confirmationFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->c07MetricValue($metric, $scoreMetrics, $momentum, $breakout);
            if ($value !== null && $value >= (float) $minimum) {
                $confirmationPassCount++;
            }
        }
        $requiredConfirmationPassCount = (int) ($extension['confirmation_metric_required_pass_count'] ?? count($confirmationFloors));
        if ($confirmationPassCount < $requiredConfirmationPassCount) {
            $failures[] = 'WATCHLIST_C07_CONFIRMATION_COUNT_FAIL';
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
                $failures[] = 'WATCHLIST_C07_SCORE_COMPONENT_COUNT_FAIL';
                continue;
            }
            $componentValues[] = $value;
            if ($value >= (float) $minimum) {
                $componentPassCount++;
            }
        }

        $requiredComponentPassCount = (int) ($extension['score_component_required_pass_count'] ?? count($componentMinimums));
        if ($componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C07_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== []
            && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
            $failures[] = 'WATCHLIST_C07_SCORE_COMPONENT_AVERAGE_FAIL';
        }

        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        $trendPassCount = 0;
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->numericOrNull($momentum[$metric] ?? null);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C07_TREND_CONFIRM_COUNT_FAIL';
        }

        $rawSetupGuards = is_array($extension['raw_setup_guards'] ?? null)
            ? $extension['raw_setup_guards']
            : [];
        if (($rawSetupGuards['roc20_between_catalog_roc_lo_and_roc_hi_with_tolerance'] ?? false) === true) {
            $roc20 = $this->numericOrNull($momentum['roc20'] ?? null);
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            $lowerTolerance = $this->numericOrNull($rawSetupGuards['roc20_lower_tolerance'] ?? null) ?? 0.0;
            $upperTolerance = $this->numericOrNull($rawSetupGuards['roc20_upper_tolerance'] ?? null) ?? 0.0;
            if ($roc20 === null || $rocLo === null || $rocHi === null
                || $roc20 < ($rocLo - $lowerTolerance) || $roc20 > ($rocHi + $upperTolerance)) {
                $failures[] = 'WATCHLIST_C07_SETUP_RANGE_FAIL';
            }
        }

        if (($rawSetupGuards['close_to_hh20_between_negative_bo_near_below_and_bo_max_ext_with_tolerance'] ?? false) === true) {
            $closeToHh20 = $this->numericOrNull($breakout['close_to_hh20_pct'] ?? null);
            $nearBelow = $this->numericOrNull($paramset['setup']['bo_near_below_pct'] ?? null);
            $maxExtension = $this->numericOrNull($paramset['setup']['bo_max_ext_pct'] ?? null);
            $lowerTolerance = $this->numericOrNull($rawSetupGuards['close_to_hh20_lower_tolerance'] ?? null) ?? 0.0;
            $upperTolerance = $this->numericOrNull($rawSetupGuards['close_to_hh20_upper_tolerance'] ?? null) ?? 0.0;
            if ($closeToHh20 === null || $nearBelow === null || $maxExtension === null
                || $closeToHh20 < (-$nearBelow - $lowerTolerance) || $closeToHh20 > ($maxExtension + $upperTolerance)) {
                $failures[] = 'WATCHLIST_C07_SETUP_RANGE_FAIL';
            }
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C07_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }

    private function c15QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION') {
            return [];
        }

        $failures = [];
        $scoreMetrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];

        $bounds = is_array($extension['runtime_metric_bounds'] ?? null)
            ? $extension['runtime_metric_bounds']
            : [];
        if (($bounds['dv20_between_catalog_min_and_strong'] ?? false) === true) {
            $dv20 = $this->numericOrNull($scoreMetrics['dv20_idr'] ?? null);
            $min = $this->numericOrNull($paramset['liquidity']['min_dv20_idr'] ?? null);
            $max = $this->numericOrNull($paramset['liquidity']['dv20_strong_idr'] ?? null);
            if ($dv20 === null || $min === null || $max === null || $dv20 < $min || $dv20 > $max) {
                $failures[] = 'WATCHLIST_C15_DV20_MID_LIQUIDITY_RANGE_FAIL';
            }
        }

        if (($bounds['vol_ratio_between_catalog_min_and_strong'] ?? false) === true) {
            $volRatio = $this->numericOrNull($scoreMetrics['vol_ratio'] ?? null);
            $min = $this->numericOrNull($paramset['volume']['min_vol_ratio'] ?? null);
            $max = $this->numericOrNull($paramset['volume']['strong_vol_ratio'] ?? null);
            if ($volRatio === null || $min === null || $max === null || $volRatio < $min || $volRatio > $max) {
                $failures[] = 'WATCHLIST_C15_VOLUME_SPIKE_RANGE_FAIL';
            }
        }

        if (($bounds['atr14_between_catalog_min_and_max'] ?? false) === true) {
            $atr = $this->numericOrNull($scoreMetrics['atr14_pct'] ?? null);
            $min = $this->numericOrNull($paramset['risk']['min_atr14_pct'] ?? null);
            $max = $this->numericOrNull($paramset['risk']['max_atr14_pct'] ?? null);
            if ($atr === null || $min === null || $max === null || $atr < $min || $atr > $max) {
                $failures[] = 'WATCHLIST_C15_ATR_REGIME_RANGE_FAIL';
            }
        }

        if (($bounds['roc20_between_catalog_roc_lo_and_roc_hi'] ?? false) === true) {
            $roc20 = $this->fractionOrNull($momentum['roc20'] ?? $scoreMetrics['roc20'] ?? null);
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            if ($roc20 === null || $rocLo === null || $rocHi === null || $roc20 < $rocLo || $roc20 > $rocHi) {
                $failures[] = 'WATCHLIST_C15_ROC20_NEUTRAL_RANGE_FAIL';
            }
        }

        $shortTermBounds = is_array($extension['short_term_momentum_bounds'] ?? null)
            ? $extension['short_term_momentum_bounds']
            : [];
        foreach ($shortTermBounds as $metric => $range) {
            if (! is_array($range)) {
                continue;
            }
            $value = $this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null);
            $min = $this->numericOrNull($range['min'] ?? null);
            $max = $this->numericOrNull($range['max'] ?? null);
            if ($value === null || $min === null || $max === null || $value < $min || $value > $max) {
                $failures[] = 'WATCHLIST_C15_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL';
            }
        }

        $scoreMax = $this->numericOrNull($extension['score_total_max'] ?? null);
        if ($scoreMax !== null) {
            $score = $this->numericOrNull($item['score_total'] ?? null);
            if ($score === null || $score > $scoreMax) {
                $failures[] = 'WATCHLIST_C15_SCORE_OVEREXTENSION_FAIL';
            }
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
                $failures[] = 'WATCHLIST_C15_SCORE_COMPONENT_COUNT_FAIL';
                continue;
            }
            $componentValues[] = $value;
            if ($value >= (float) $minimum) {
                $componentPassCount++;
            }
        }

        $requiredComponentPassCount = (int) ($extension['score_component_required_pass_count'] ?? count($componentMinimums));
        if ($componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C15_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== []
            && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
            $failures[] = 'WATCHLIST_C15_SCORE_COMPONENT_AVERAGE_FAIL';
        }

        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        $trendPassCount = 0;
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C15_TREND_CONFIRM_COUNT_FAIL';
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C15_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }


    private function c16QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C16_CONTROLLED_PULLBACK_SCORE_WINDOW_VOLUME_QUALITY_RECOVERY') {
            return [];
        }

        $failures = [];
        $scoreMetrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];

        $bounds = is_array($extension['runtime_metric_bounds'] ?? null)
            ? $extension['runtime_metric_bounds']
            : [];
        if (($bounds['dv20_between_catalog_min_and_strong'] ?? false) === true) {
            $dv20 = $this->numericOrNull($scoreMetrics['dv20_idr'] ?? null);
            $min = $this->numericOrNull($paramset['liquidity']['min_dv20_idr'] ?? null);
            $max = $this->numericOrNull($paramset['liquidity']['dv20_strong_idr'] ?? null);
            if ($dv20 === null || $min === null || $max === null || $dv20 < $min || $dv20 > $max) {
                $failures[] = 'WATCHLIST_C16_DV20_QUALITY_RANGE_FAIL';
            }
        }

        if (($bounds['vol_ratio_between_catalog_min_and_strong'] ?? false) === true) {
            $volRatio = $this->numericOrNull($scoreMetrics['vol_ratio'] ?? null);
            $min = $this->numericOrNull($paramset['volume']['min_vol_ratio'] ?? null);
            $max = $this->numericOrNull($paramset['volume']['strong_vol_ratio'] ?? null);
            if ($volRatio === null || $min === null || $max === null || $volRatio < $min || $volRatio > $max) {
                $failures[] = 'WATCHLIST_C16_VOLUME_15_20_RANGE_FAIL';
            }
        }

        if (($bounds['atr14_between_catalog_min_and_max'] ?? false) === true) {
            $atr = $this->numericOrNull($scoreMetrics['atr14_pct'] ?? null);
            $min = $this->numericOrNull($paramset['risk']['min_atr14_pct'] ?? null);
            $max = $this->numericOrNull($paramset['risk']['max_atr14_pct'] ?? null);
            if ($atr === null || $min === null || $max === null || $atr < $min || $atr > $max) {
                $failures[] = 'WATCHLIST_C16_ATR_REGIME_RANGE_FAIL';
            }
        }

        if (($bounds['roc20_between_catalog_roc_lo_and_roc_hi'] ?? false) === true) {
            $roc20 = $this->fractionOrNull($momentum['roc20'] ?? $scoreMetrics['roc20'] ?? null);
            $rocLo = $this->numericOrNull($paramset['setup']['roc_lo'] ?? null);
            $rocHi = $this->numericOrNull($paramset['setup']['roc_hi'] ?? null);
            if ($roc20 === null || $rocLo === null || $rocHi === null || $roc20 < $rocLo || $roc20 > $rocHi) {
                $failures[] = 'WATCHLIST_C16_ROC20_SEGMENT_RANGE_FAIL';
            }
        }

        $shortTermBounds = is_array($extension['short_term_momentum_bounds'] ?? null)
            ? $extension['short_term_momentum_bounds']
            : [];
        foreach ($shortTermBounds as $metric => $range) {
            if (! is_array($range)) {
                continue;
            }
            $value = $this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null);
            $min = $this->numericOrNull($range['min'] ?? null);
            $max = $this->numericOrNull($range['max'] ?? null);
            if ($value === null || $min === null || $max === null || $value < $min || $value > $max) {
                $failures[] = 'WATCHLIST_C16_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL';
            }
        }

        $score = $this->numericOrNull($item['score_total'] ?? null);
        $scoreMin = $this->numericOrNull($extension['score_total_min'] ?? null);
        if ($scoreMin !== null && ($score === null || $score < $scoreMin)) {
            $failures[] = 'WATCHLIST_C16_SCORE_WINDOW_LOW_FAIL';
        }
        $scoreMax = $this->numericOrNull($extension['score_total_max'] ?? null);
        if ($scoreMax !== null && ($score === null || $score > $scoreMax)) {
            $failures[] = 'WATCHLIST_C16_SCORE_OVEREXTENSION_FAIL';
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
                $failures[] = 'WATCHLIST_C16_SCORE_COMPONENT_COUNT_FAIL';
                continue;
            }
            $componentValues[] = $value;
            if ($value >= (float) $minimum) {
                $componentPassCount++;
            }
        }

        $requiredComponentPassCount = (int) ($extension['score_component_required_pass_count'] ?? count($componentMinimums));
        if ($componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C16_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== []
            && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
            $failures[] = 'WATCHLIST_C16_SCORE_COMPONENT_AVERAGE_FAIL';
        }

        $trendFloors = is_array($extension['trend_metric_floor'] ?? null)
            ? $extension['trend_metric_floor']
            : [];
        $trendPassCount = 0;
        foreach ($trendFloors as $metric => $minimum) {
            if (! is_numeric($minimum)) {
                continue;
            }
            $value = $this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C16_TREND_CONFIRM_COUNT_FAIL';
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C16_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
    }


    private function c17QualityFloorFailures(array $item, array $paramset): array
    {
        $extension = $paramset['bt_grid_resolution']['candidate_selection_extension'] ?? null;
        if (! is_array($extension)
            || (string) ($extension['mode'] ?? '') !== 'C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16') {
            return [];
        }

        $failures = [];
        $scoreMetrics = is_array($item['score_metrics'] ?? null) ? $item['score_metrics'] : [];
        $momentum = is_array($item['factor_breakdown']['momentum'] ?? null)
            ? $item['factor_breakdown']['momentum']
            : [];

        $bounds = is_array($extension['runtime_metric_bounds'] ?? null)
            ? $extension['runtime_metric_bounds']
            : [];
        if (($bounds['dv20_between_catalog_min_and_strong'] ?? false) === true) {
            $dv20 = $this->numericOrNull($scoreMetrics['dv20_idr'] ?? null);
            $min = $this->numericOrNull($paramset['liquidity']['min_dv20_idr'] ?? null);
            $max = $this->numericOrNull($paramset['liquidity']['dv20_strong_idr'] ?? null);
            if ($dv20 === null || $min === null || $max === null || $dv20 < $min || $dv20 > $max) {
                $failures[] = 'WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL';
            }
        }

        if (($bounds['vol_ratio_between_catalog_min_and_strong'] ?? false) === true) {
            $volRatio = $this->numericOrNull($scoreMetrics['vol_ratio'] ?? null);
            $min = $this->numericOrNull($paramset['volume']['min_vol_ratio'] ?? null);
            $max = $this->numericOrNull($paramset['volume']['strong_vol_ratio'] ?? null);
            if ($volRatio === null || $min === null || $max === null || $volRatio < $min || $volRatio > $max) {
                $failures[] = 'WATCHLIST_C17_VOLUME_RECOVERY_RANGE_FAIL';
            }
        }

        if (($bounds['atr14_between_catalog_min_and_max'] ?? false) === true) {
            $atr = $this->numericOrNull($scoreMetrics['atr14_pct'] ?? null);
            $min = $this->numericOrNull($paramset['risk']['min_atr14_pct'] ?? null);
            $max = $this->numericOrNull($paramset['risk']['max_atr14_pct'] ?? null);
            if ($atr === null || $min === null || $max === null || $atr < $min || $atr > $max) {
                $failures[] = 'WATCHLIST_C17_ATR_SEGMENT_RANGE_FAIL';
            }
        }

        if (($bounds['roc20_between_catalog_roc_lo_and_roc_hi'] ?? false) === true) {
            $roc20 = $this->fractionOrNull($momentum['roc20'] ?? $scoreMetrics['roc20'] ?? null);
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
            $value = $this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null);
            $min = $this->numericOrNull($range['min'] ?? null);
            $max = $this->numericOrNull($range['max'] ?? null);
            if ($value === null || $min === null || $max === null || $value < $min || $value > $max) {
                $failures[] = 'WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL';
            }
        }

        $score = $this->numericOrNull($item['score_total'] ?? null);
        [$scoreMin, $scoreMax] = $this->c17ScoreWindowBounds(
            $extension,
            (string) ($paramset['bt_catalog']['row_code'] ?? '')
        );
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
        if ($componentPassCount < $requiredComponentPassCount) {
            $failures[] = 'WATCHLIST_C17_SCORE_COMPONENT_COUNT_FAIL';
        }
        $componentAverageMin = $this->numericOrNull($extension['score_component_average_min'] ?? null);
        if ($componentAverageMin !== null && $componentValues !== []
            && (array_sum($componentValues) / count($componentValues)) < $componentAverageMin) {
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
            $value = $this->fractionOrNull($momentum[$metric] ?? $scoreMetrics[$metric] ?? null);
            if ($value !== null && $value >= (float) $minimum) {
                $trendPassCount++;
            }
        }
        $requiredTrendPassCount = (int) ($extension['trend_metric_required_pass_count'] ?? count($trendFloors));
        if ($trendPassCount < $requiredTrendPassCount) {
            $failures[] = 'WATCHLIST_C17_TREND_CONFIRM_COUNT_FAIL';
        }

        $failures = array_values(array_unique($failures));
        if ($failures !== []) {
            $failures[] = (string) ($extension['reason_code'] ?? 'WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL');
        }

        return array_values(array_unique($failures));
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

    private function c07MetricValue(string $metric, array $scoreMetrics, array $momentum, array $breakout): ?float
    {
        if (array_key_exists($metric, $momentum)) {
            return $this->numericOrNull($momentum[$metric]);
        }
        if (array_key_exists($metric, $breakout)) {
            return $this->numericOrNull($breakout[$metric]);
        }
        if (! array_key_exists($metric, $scoreMetrics)) {
            return null;
        }

        if (in_array($metric, [
            'roc5',
            'roc10',
            'roc20',
            'close_to_ll20_pct',
            'range_20_pct',
            'range_position_20_pct',
            'sector_roc20',
            'rs_20_vs_sector',
            'sector_rs_20_vs_ihsg',
        ], true)) {
            return $this->fractionOrNull($scoreMetrics[$metric]);
        }

        return $this->numericOrNull($scoreMetrics[$metric]);
    }

    private function componentValue(array $item, string $component): ?float
    {
        $value = $item['score_components'][$component] ?? $item[$component] ?? null;

        return $this->numericOrNull($value);
    }

    private function numericOrNull($value): ?float
    {
        return $this->isNumericValue($value) ? (float) $value : null;
    }

    private function fractionOrNull($value): ?float
    {
        $numeric = $this->numericOrNull($value);
        if ($numeric === null) {
            return null;
        }

        return abs($numeric) > 1.0 ? $numeric / 100.0 : $numeric;
    }

    private function fillPlanRanks(array &$payload): void
    {
        $rank = 1;
        foreach (['TOP_PICKS', 'SECONDARY', 'WATCH_ONLY'] as $group) {
            foreach ($payload['groups'][$group] as $index => $item) {
                $payload['groups'][$group][$index]['plan_rank'] = $rank;
                $rank++;
            }
        }
    }

    private function sortItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            foreach ([
                ['score_total', 'desc'],
                ['score_breakout', 'desc', 'score_components'],
                ['score_momentum', 'desc', 'score_components'],
                ['dv20_idr', 'desc', 'score_metrics'],
                ['atr14_pct', 'asc', 'score_metrics'],
            ] as $rule) {
                $leftValue = $this->sortValue($left, $rule[0], $rule[2] ?? null);
                $rightValue = $this->sortValue($right, $rule[0], $rule[2] ?? null);
                if ($leftValue == $rightValue) {
                    continue;
                }

                if ($rule[1] === 'desc') {
                    return $leftValue < $rightValue ? 1 : -1;
                }

                return $leftValue > $rightValue ? 1 : -1;
            }

            $leftTickerId = $this->intOrNull($left['ticker_id'] ?? null);
            $rightTickerId = $this->intOrNull($right['ticker_id'] ?? null);
            if ($leftTickerId !== $rightTickerId) {
                if ($leftTickerId === null) {
                    return 1;
                }
                if ($rightTickerId === null) {
                    return -1;
                }

                return $leftTickerId < $rightTickerId ? -1 : 1;
            }

            return strcmp((string) ($left['ticker_code'] ?? ''), (string) ($right['ticker_code'] ?? ''));
        });

        return $items;
    }

    private function sortValue(array $item, string $key, ?string $nestedKey = null): float
    {
        if ($nestedKey !== null) {
            return (float) ($item[$nestedKey][$key] ?? 0.0);
        }

        return (float) ($item[$key] ?? 0.0);
    }

    private function resolveParamset(array $paramset): array
    {
        $defaults = self::DEFAULT_PARAMSET;
        $resolved = $paramset;
        $resolved['policy_code'] = (string) ($paramset['policy_code'] ?? $defaults['policy_code']);
        $resolved['policy_version'] = (string) ($paramset['policy_version'] ?? $defaults['policy_version']);
        $resolved['paramset_code'] = (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']);
        $resolved['grouping'] = [
            'grouping_mode' => (string) $this->paramValueMixed($paramset, ['grouping', 'grouping_mode'], $defaults['grouping']['grouping_mode']),
            'top_min_score_q' => $this->paramValueMixed($paramset, ['grouping', 'top_min_score_q'], $defaults['grouping']['top_min_score_q']),
            'secondary_min_score_q' => $this->paramValueMixed($paramset, ['grouping', 'secondary_min_score_q'], $defaults['grouping']['secondary_min_score_q']),
            'top_picks' => [
                'min_score_total' => $this->paramValueMixed($paramset, ['grouping', 'top_picks', 'min_score_total'], $defaults['grouping']['top_picks']['min_score_total']),
                'max_items' => $this->paramValueMixed($paramset, ['grouping', 'top_picks', 'max_items'], $defaults['grouping']['top_picks']['max_items']),
            ],
            'secondary' => [
                'min_score_total' => $this->paramValueMixed($paramset, ['grouping', 'secondary', 'min_score_total'], $defaults['grouping']['secondary']['min_score_total']),
                'max_items' => $this->paramValueMixed($paramset, ['grouping', 'secondary', 'max_items'], $defaults['grouping']['secondary']['max_items']),
            ],
            'watch_only' => [
                'min_score_total' => $this->paramValueMixed($paramset, ['grouping', 'watch_only', 'min_score_total'], $defaults['grouping']['watch_only']['min_score_total']),
                'max_items' => $this->paramValueMixed($paramset, ['grouping', 'watch_only', 'max_items'], $defaults['grouping']['watch_only']['max_items']),
            ],
            'avoid' => [
                'max_score_total_below' => $this->paramValueMixed($paramset, ['grouping', 'avoid', 'max_score_total_below'], $defaults['grouping']['avoid']['max_score_total_below']),
            ],
            'sort_keys' => $defaults['grouping']['sort_keys'],
        ];

        return $resolved;
    }

    private function validateParamset(array $paramset): array
    {
        $errors = [];

        if ($paramset['grouping']['grouping_mode'] !== 'PLAN_GROUPING_DETERMINISTIC') {
            $errors[] = 'grouping.grouping_mode must be PLAN_GROUPING_DETERMINISTIC';
        }

        foreach (['top_min_score_q', 'secondary_min_score_q'] as $field) {
            $value = $paramset['grouping'][$field];
            if ($value !== null && (! $this->isNumericValue($value) || (float) $value < 0.0 || (float) $value > 1.0)) {
                $errors[] = 'grouping.'.$field.' must be null or numeric between 0 and 1';
            }
        }
        if ($paramset['grouping']['top_min_score_q'] !== null
            && $paramset['grouping']['secondary_min_score_q'] !== null
            && (float) $paramset['grouping']['top_min_score_q'] < (float) $paramset['grouping']['secondary_min_score_q']) {
            $errors[] = 'grouping.top_min_score_q must be >= grouping.secondary_min_score_q';
        }

        foreach ([
            'grouping.top_picks.min_score_total' => $paramset['grouping']['top_picks']['min_score_total'],
            'grouping.secondary.min_score_total' => $paramset['grouping']['secondary']['min_score_total'],
            'grouping.watch_only.min_score_total' => $paramset['grouping']['watch_only']['min_score_total'],
            'grouping.avoid.max_score_total_below' => $paramset['grouping']['avoid']['max_score_total_below'],
        ] as $name => $value) {
            if (! $this->isNumericValue($value)) {
                $errors[] = $name.' must be numeric';
                continue;
            }

            if ((float) $value < 0.0 || (float) $value > 1.0) {
                $errors[] = $name.' must be between 0 and 1';
            }
        }

        foreach ([
            'grouping.top_picks.max_items' => $paramset['grouping']['top_picks']['max_items'],
            'grouping.secondary.max_items' => $paramset['grouping']['secondary']['max_items'],
            'grouping.watch_only.max_items' => $paramset['grouping']['watch_only']['max_items'],
        ] as $name => $value) {
            if (! is_int($value) || $value <= 0) {
                $errors[] = $name.' must be integer > 0';
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        if ($paramset['grouping']['top_picks']['min_score_total'] < $paramset['grouping']['secondary']['min_score_total']) {
            $errors[] = 'grouping.top_picks.min_score_total must be >= grouping.secondary.min_score_total';
        }
        if ($paramset['grouping']['secondary']['min_score_total'] < $paramset['grouping']['watch_only']['min_score_total']) {
            $errors[] = 'grouping.secondary.min_score_total must be >= grouping.watch_only.min_score_total';
        }
        if ($paramset['grouping']['avoid']['max_score_total_below'] > $paramset['grouping']['watch_only']['min_score_total']) {
            $errors[] = 'grouping.avoid.max_score_total_below must be <= grouping.watch_only.min_score_total';
        }

        return $errors;
    }

    private function paramValueMixed(array $paramset, array $path, $default)
    {
        $cursor = $paramset;
        foreach ($path as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return $default;
            }
            $cursor = $cursor[$segment];
        }

        if (is_array($cursor) && array_key_exists('value', $cursor)) {
            return $cursor['value'];
        }

        return $cursor;
    }

    private function isNumericValue($value): bool
    {
        return is_int($value) || is_float($value);
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
