<?php

namespace App\Application\Watchlist\Services;

class WatchlistRecommendationService
{
    public const DEFAULT_PARAMSET = [
        'policy_code' => 'WS',
        'policy_version' => 'WS_EOD_RUNTIME',
        'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
        'recommendation' => [
            'recommendation_mode' => 'PLAN_DERIVED_DETERMINISTIC',
            'source_groups' => [
                'TOP_PICKS',
                'SECONDARY',
            ],
            'score_source' => 'score_total',
            'min_recommendation_score' => 0.70,
            'borderline_min_score' => 0.55,
            'max_recommended_items' => 3,
            'dynamic_count_mode' => 'THRESHOLD_AND_CAP',
            'sort_keys' => [
                'recommendation_score_desc',
                'plan_rank_asc',
                'plan_group_priority_asc',
                'ticker_id_asc',
            ],
            'capital' => [
                'minimum_input_capital_idr' => 0.0,
                'default_min_lot_value_idr' => 0.0,
            ],
        ],
    ];

    private const SOURCE_GROUPS = [
        'TOP_PICKS',
        'SECONDARY',
    ];

    private WatchlistPlanGroupingService $planGroupingService;

    public function __construct(WatchlistPlanGroupingService $planGroupingService = null)
    {
        $this->planGroupingService = $planGroupingService ?: new WatchlistPlanGroupingService();
    }

    public function recommendForTradeDate(string $tradeDate, array $paramset = [], array $capitalInput = []): array
    {
        $resolvedParamset = $this->resolveParamset($paramset);
        $planOutput = $this->planGroupingService->groupForTradeDate($tradeDate, $resolvedParamset);

        return $this->recommendFromPlanOutput($planOutput, $resolvedParamset, $capitalInput);
    }

    public function recommendFromPlanOutput(array $planOutput, array $paramset = [], array $capitalInput = []): array
    {
        $resolvedParamset = $this->resolveParamset($paramset);
        $capitalContext = $this->resolveCapitalContext($capitalInput, $resolvedParamset);
        $payload = $this->basePayload($planOutput, $resolvedParamset, $capitalContext);
        $paramsetErrors = $this->validateParamset($resolvedParamset, $capitalContext);

        if ($paramsetErrors !== []) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_RECOMMENDATION_PARAMSET_INVALID';
            $payload['meta']['recommendation_reason_code'] = 'WATCHLIST_RECOMMENDATION_PARAMSET_INVALID';
            $payload['meta']['paramset_errors'] = $paramsetErrors;

            return $payload;
        }

        if (! ($planOutput['is_ready'] ?? $planOutput['ready'] ?? false)) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_RECOMMENDATION_SOURCE_NOT_READY';
            $payload['meta']['recommendation_reason_code'] = 'WATCHLIST_RECOMMENDATION_SOURCE_NOT_READY';
            $payload['meta']['source_reason_code'] = $planOutput['reason_code'] ?? null;

            return $payload;
        }

        $sourceItems = $this->collectSourceItems($planOutput);
        $evaluatedItems = $this->sortItems($this->evaluateItems($sourceItems, $resolvedParamset, $capitalContext));
        $dynamicTargetCount = $this->dynamicTargetCount($evaluatedItems, $resolvedParamset, $capitalContext);
        $recommendedCount = 0;

        foreach ($evaluatedItems as $index => $item) {
            $recommendationRank = $index + 1;
            $isSelected = $this->isSelectedItem($item, $recommendationRank, $dynamicTargetCount, $resolvedParamset, $capitalContext);
            if ($isSelected) {
                $recommendedCount++;
            }

            $payload['items'][] = $this->recommendationItem(
                $item,
                $recommendationRank,
                $isSelected,
                $dynamicTargetCount,
                $resolvedParamset,
                $capitalContext
            );
        }

        $payload['summary'] = [
            'source_plan_item_count' => count($sourceItems),
            'evaluated_count' => count($payload['items']),
            'recommended_count' => $recommendedCount,
            'not_selected_count' => count($payload['items']) - $recommendedCount,
            'capital_mode' => $capitalContext['capital_mode'],
            'input_capital' => $capitalContext['input_capital'],
            'dynamic_target_count' => $dynamicTargetCount,
            'empty_recommendation_flag' => $recommendedCount === 0,
            'recommended_tickers' => array_values(array_map(function (array $item): string {
                return $item['ticker'];
            }, array_filter($payload['items'], function (array $item): bool {
                return $item['recommended_flag'] === true;
            }))),
            'reason_codes' => $recommendedCount === 0 ? ['WS_REC_EMPTY_SET'] : [],
        ];

        $payload['ready'] = true;
        $payload['is_ready'] = true;
        $payload['reason_code'] = $recommendedCount > 0
            ? 'WATCHLIST_RECOMMENDATION_READY'
            : 'WATCHLIST_RECOMMENDATION_EMPTY';
        $payload['meta']['recommendation_reason_code'] = $payload['reason_code'];

        return $payload;
    }

    public static function defaultParamset(): array
    {
        return self::DEFAULT_PARAMSET;
    }

    private function basePayload(array $planOutput, array $paramset, array $capitalContext): array
    {
        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_RECOMMENDATION_NOT_EVALUATED',
            'meta' => [
                'strategy_code' => $paramset['policy_code'],
                'policy_code' => $paramset['policy_code'],
                'policy_version' => $paramset['policy_version'],
                'paramset_code' => $paramset['paramset_code'],
                'trade_date' => $planOutput['trade_date'] ?? null,
                'trade_date_effective' => $planOutput['trade_date_effective'] ?? null,
                'publication_id' => $planOutput['publication_id'] ?? null,
                'publication_version' => $planOutput['publication_version'] ?? null,
                'run_id' => $planOutput['run_id'] ?? null,
                'capital_mode' => $capitalContext['capital_mode'],
                'input_capital' => $capitalContext['input_capital'],
                'recommendation_reason_code' => 'WATCHLIST_RECOMMENDATION_NOT_EVALUATED',
                'source_contract' => [
                    'consumer' => 'WatchlistRecommendationService',
                    'upstream' => 'WatchlistPlanGroupingService',
                    'plan_only' => true,
                    'source_groups' => self::SOURCE_GROUPS,
                    'same_trade_date' => true,
                    'available_without_confirm' => true,
                    'no_raw_market_data' => true,
                    'no_latest_shortcut' => true,
                    'no_confirm' => true,
                    'no_execution' => true,
                    'no_backtest' => true,
                    'no_portfolio_state' => true,
                ],
                'recommendation_contract' => [
                    'recommendation_mode' => $paramset['recommendation']['recommendation_mode'],
                    'score_source' => $paramset['recommendation']['score_source'],
                    'source_groups' => $paramset['recommendation']['source_groups'],
                    'dynamic_count_mode' => $paramset['recommendation']['dynamic_count_mode'],
                    'sort_keys' => $paramset['recommendation']['sort_keys'],
                    'can_be_empty' => true,
                    'available_without_confirm' => true,
                    'confirm_does_not_mutate' => true,
                    'not_execution' => true,
                    'not_broker_instruction' => true,
                ],
                'source_plan_reference' => [
                    'trade_date' => $planOutput['trade_date'] ?? null,
                    'trade_date_effective' => $planOutput['trade_date_effective'] ?? null,
                    'publication_id' => $planOutput['publication_id'] ?? null,
                    'publication_version' => $planOutput['publication_version'] ?? null,
                    'run_id' => $planOutput['run_id'] ?? null,
                    'reason_code' => $planOutput['reason_code'] ?? null,
                ],
                'paramset_snapshot' => [
                    'policy_code' => $paramset['policy_code'],
                    'policy_version' => $paramset['policy_version'],
                    'paramset_code' => $paramset['paramset_code'],
                    'recommendation' => $paramset['recommendation'],
                    'source_plan_paramset_snapshot' => $planOutput['paramset_snapshot'] ?? [],
                ],
                'paramset_errors' => [],
            ],
            'items' => [],
            'summary' => [
                'source_plan_item_count' => 0,
                'evaluated_count' => 0,
                'recommended_count' => 0,
                'not_selected_count' => 0,
                'capital_mode' => $capitalContext['capital_mode'],
                'input_capital' => $capitalContext['input_capital'],
                'dynamic_target_count' => 0,
                'empty_recommendation_flag' => true,
                'recommended_tickers' => [],
                'reason_codes' => [],
            ],
        ];
    }

    private function collectSourceItems(array $planOutput): array
    {
        $items = [];

        foreach (self::SOURCE_GROUPS as $group) {
            foreach (($planOutput['groups'][$group] ?? []) as $index => $item) {
                $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
                $scoreTotal = $item['score_total'] ?? null;

                if ($tickerId === null || ! $this->isNumericValue($scoreTotal) || $scoreTotal < 0.0 || $scoreTotal > 1.0) {
                    continue;
                }

                $item['plan_group'] = $item['plan_group'] ?? $group;
                $item['group_semantic'] = $item['group_semantic'] ?? $group;
                $item['plan_rank'] = $this->intOrNull($item['plan_rank'] ?? null) ?? (count($items) + 1);
                $item['group_rank'] = $this->intOrNull($item['group_rank'] ?? null) ?? ($index + 1);
                $items[] = $item;
            }
        }

        return $items;
    }

    private function evaluateItems(array $items, array $paramset, array $capitalContext): array
    {
        $evaluated = [];

        foreach ($items as $item) {
            $recommendationScore = $this->roundScore((float) $item['score_total']);
            $minLotValue = $this->minLotValueForItem($item, $capitalContext, $paramset);
            $capitalFeasible = true;
            $capitalReasonCodes = [];

            if ($capitalContext['capital_mode'] === 'CAPITAL_AWARE') {
                $capitalReasonCodes[] = 'WS_REC_CAPITAL_AWARE';

                if ($capitalContext['input_capital'] < $paramset['recommendation']['capital']['minimum_input_capital_idr']) {
                    $capitalFeasible = false;
                    $capitalReasonCodes[] = 'WS_REC_CAPITAL_INSUFFICIENT';
                } elseif ($minLotValue > 0.0 && $capitalContext['input_capital'] < $minLotValue) {
                    $capitalFeasible = false;
                    $capitalReasonCodes[] = 'WS_REC_MIN_LOT_NOT_AFFORDABLE';
                }
            }

            $item['recommendation_score'] = $recommendationScore;
            $item['recommendation_capital_feasible'] = $capitalFeasible;
            $item['recommendation_capital_reason_codes'] = $capitalReasonCodes;
            $item['min_lot_value_idr'] = $minLotValue;

            $evaluated[] = $item;
        }

        return $evaluated;
    }

    private function dynamicTargetCount(array $items, array $paramset, array $capitalContext): int
    {
        $eligibleCount = 0;

        foreach ($items as $item) {
            if ($this->isEligibleForSelection($item, $paramset, $capitalContext)) {
                $eligibleCount++;
            }
        }

        return min($eligibleCount, $paramset['recommendation']['max_recommended_items']);
    }

    private function isSelectedItem(array $item, int $recommendationRank, int $dynamicTargetCount, array $paramset, array $capitalContext): bool
    {
        return $this->isEligibleForSelection($item, $paramset, $capitalContext)
            && $recommendationRank <= $dynamicTargetCount;
    }

    private function isEligibleForSelection(array $item, array $paramset, array $capitalContext): bool
    {
        if ($capitalContext['capital_mode'] === 'CAPITAL_AWARE' && ! ($item['recommendation_capital_feasible'] ?? false)) {
            return false;
        }

        return $item['recommendation_score'] >= $paramset['recommendation']['min_recommendation_score'];
    }

    private function recommendationItem(
        array $item,
        int $recommendationRank,
        bool $isSelected,
        int $dynamicTargetCount,
        array $paramset,
        array $capitalContext
    ): array {
        $recommendationScore = $item['recommendation_score'];
        $reasonCodes = $item['recommendation_capital_reason_codes'] ?? [];
        $label = 'NOT_SELECTED';

        if ($isSelected) {
            $reasonCodes[] = 'WS_REC_SELECTED';
            $label = 'RECOMMENDED';
        } else {
            if ($recommendationScore >= $paramset['recommendation']['min_recommendation_score']
                && $recommendationRank > $dynamicTargetCount
                && ($item['recommendation_capital_feasible'] ?? true)) {
                $reasonCodes[] = 'WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET';
            }

            if ($recommendationScore >= $paramset['recommendation']['borderline_min_score']
                && $recommendationScore < $paramset['recommendation']['min_recommendation_score']) {
                $reasonCodes[] = 'WS_REC_BORDERLINE';
                $label = 'BORDERLINE';
            }

            $reasonCodes[] = 'WS_REC_NOT_SELECTED';
        }

        return [
            'ticker' => strtoupper(trim((string) ($item['ticker_code'] ?? ''))),
            'ticker_id' => $this->intOrNull($item['ticker_id'] ?? null),
            'plan_rank' => $this->intOrNull($item['plan_rank'] ?? null),
            'plan_group_semantic' => $item['group_semantic'] ?? $item['plan_group'] ?? null,
            'recommendation_score' => $recommendationScore,
            'recommendation_rank' => $recommendationRank,
            'recommendation_label' => $label,
            'recommended_flag' => $isSelected,
            'capital_mode' => $capitalContext['capital_mode'],
            'reason_codes' => array_values(array_unique($reasonCodes)),
            'plan_reference' => [
                'plan_group' => $item['plan_group'] ?? null,
                'group_rank' => $item['group_rank'] ?? null,
                'group_reason_code' => $item['group_reason_code'] ?? null,
                'score_total' => $item['score_total'] ?? null,
            ],
        ];
    }

    private function sortItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            foreach ([
                ['recommendation_score', 'desc'],
                ['plan_rank', 'asc'],
                ['plan_group_priority', 'asc'],
                ['ticker_id', 'asc'],
            ] as $rule) {
                $leftValue = $this->sortValue($left, $rule[0]);
                $rightValue = $this->sortValue($right, $rule[0]);
                if ($leftValue == $rightValue) {
                    continue;
                }

                if ($rule[1] === 'desc') {
                    return $leftValue < $rightValue ? 1 : -1;
                }

                return $leftValue > $rightValue ? 1 : -1;
            }

            return strcmp((string) ($left['ticker_code'] ?? ''), (string) ($right['ticker_code'] ?? ''));
        });

        return $items;
    }

    private function sortValue(array $item, string $key): float
    {
        if ($key === 'plan_group_priority') {
            return (float) $this->planGroupPriority((string) ($item['group_semantic'] ?? $item['plan_group'] ?? ''));
        }

        if ($key === 'ticker_id') {
            return (float) ($this->intOrNull($item['ticker_id'] ?? null) ?? PHP_INT_MAX);
        }

        return (float) ($item[$key] ?? 0.0);
    }

    private function planGroupPriority(string $group): int
    {
        if ($group === 'TOP_PICKS') {
            return 1;
        }
        if ($group === 'SECONDARY') {
            return 2;
        }

        return 99;
    }

    private function resolveParamset(array $paramset): array
    {
        $defaults = self::DEFAULT_PARAMSET;

        return [
            'policy_code' => (string) ($paramset['policy_code'] ?? $defaults['policy_code']),
            'policy_version' => (string) ($paramset['policy_version'] ?? $defaults['policy_version']),
            'paramset_code' => (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']),
            'recommendation' => [
                'recommendation_mode' => (string) $this->paramValueMixed($paramset, ['recommendation', 'recommendation_mode'], $defaults['recommendation']['recommendation_mode']),
                'source_groups' => $defaults['recommendation']['source_groups'],
                'score_source' => (string) $this->paramValueMixed($paramset, ['recommendation', 'score_source'], $defaults['recommendation']['score_source']),
                'min_recommendation_score' => $this->paramValueMixed($paramset, ['recommendation', 'min_recommendation_score'], $defaults['recommendation']['min_recommendation_score']),
                'borderline_min_score' => $this->paramValueMixed($paramset, ['recommendation', 'borderline_min_score'], $defaults['recommendation']['borderline_min_score']),
                'max_recommended_items' => $this->paramValueMixed($paramset, ['recommendation', 'max_recommended_items'], $defaults['recommendation']['max_recommended_items']),
                'dynamic_count_mode' => (string) $this->paramValueMixed($paramset, ['recommendation', 'dynamic_count_mode'], $defaults['recommendation']['dynamic_count_mode']),
                'sort_keys' => $defaults['recommendation']['sort_keys'],
                'capital' => [
                    'minimum_input_capital_idr' => $this->paramValueMixed($paramset, ['recommendation', 'capital', 'minimum_input_capital_idr'], $defaults['recommendation']['capital']['minimum_input_capital_idr']),
                    'default_min_lot_value_idr' => $this->paramValueMixed($paramset, ['recommendation', 'capital', 'default_min_lot_value_idr'], $defaults['recommendation']['capital']['default_min_lot_value_idr']),
                ],
            ],
        ];
    }

    private function validateParamset(array $paramset, array $capitalContext): array
    {
        $errors = [];
        $recommendation = $paramset['recommendation'];

        if ($recommendation['recommendation_mode'] !== 'PLAN_DERIVED_DETERMINISTIC') {
            $errors[] = 'recommendation.recommendation_mode must be PLAN_DERIVED_DETERMINISTIC';
        }
        if ($recommendation['score_source'] !== 'score_total') {
            $errors[] = 'recommendation.score_source must be score_total';
        }
        if ($recommendation['dynamic_count_mode'] !== 'THRESHOLD_AND_CAP') {
            $errors[] = 'recommendation.dynamic_count_mode must be THRESHOLD_AND_CAP';
        }

        foreach ([
            'recommendation.min_recommendation_score' => $recommendation['min_recommendation_score'],
            'recommendation.borderline_min_score' => $recommendation['borderline_min_score'],
        ] as $name => $value) {
            if (! $this->isNumericValue($value)) {
                $errors[] = $name.' must be numeric';
                continue;
            }

            if ((float) $value < 0.0 || (float) $value > 1.0) {
                $errors[] = $name.' must be between 0 and 1';
            }
        }

        if (! is_int($recommendation['max_recommended_items']) || $recommendation['max_recommended_items'] <= 0) {
            $errors[] = 'recommendation.max_recommended_items must be integer > 0';
        }

        foreach ([
            'recommendation.capital.minimum_input_capital_idr' => $recommendation['capital']['minimum_input_capital_idr'],
            'recommendation.capital.default_min_lot_value_idr' => $recommendation['capital']['default_min_lot_value_idr'],
        ] as $name => $value) {
            if (! $this->isNumericValue($value) || (float) $value < 0.0) {
                $errors[] = $name.' must be numeric and >= 0';
            }
        }

        if ($capitalContext['capital_mode'] === 'CAPITAL_AWARE'
            && ($capitalContext['input_capital'] === null || $capitalContext['input_capital'] < 0.0)) {
            $errors[] = 'capital_input.input_capital must be numeric and >= 0 for CAPITAL_AWARE mode';
        }
        if (! in_array($capitalContext['capital_mode'], ['CAPITAL_FREE', 'CAPITAL_AWARE'], true)) {
            $errors[] = 'capital_input.capital_mode must be CAPITAL_FREE or CAPITAL_AWARE';
        }

        if ($errors !== []) {
            return $errors;
        }

        if ($recommendation['borderline_min_score'] > $recommendation['min_recommendation_score']) {
            $errors[] = 'recommendation.borderline_min_score must be <= recommendation.min_recommendation_score';
        }

        return $errors;
    }

    private function resolveCapitalContext(array $capitalInput, array $paramset): array
    {
        $capitalMode = (string) ($capitalInput['capital_mode'] ?? '');

        if ($capitalMode === '') {
            $capitalMode = array_key_exists('input_capital', $capitalInput) ? 'CAPITAL_AWARE' : 'CAPITAL_FREE';
        }

        $inputCapital = null;
        if ($capitalMode === 'CAPITAL_AWARE') {
            $value = $capitalInput['input_capital'] ?? null;
            $inputCapital = $this->isNumericValue($value) ? (float) $value : null;
        }

        return [
            'capital_mode' => $capitalMode,
            'input_capital' => $inputCapital,
            'minimum_lot_values_idr' => is_array($capitalInput['minimum_lot_values_idr'] ?? null)
                ? $capitalInput['minimum_lot_values_idr']
                : [],
            'default_min_lot_value_idr' => (float) $paramset['recommendation']['capital']['default_min_lot_value_idr'],
        ];
    }

    private function minLotValueForItem(array $item, array $capitalContext, array $paramset): float
    {
        $map = $capitalContext['minimum_lot_values_idr'];
        $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
        $tickerCode = strtoupper(trim((string) ($item['ticker_code'] ?? '')));

        foreach ([$tickerId, (string) $tickerId, $tickerCode] as $key) {
            if ($key !== null && array_key_exists($key, $map) && $this->isNumericValue($map[$key])) {
                return (float) $map[$key];
            }
        }

        if ($this->isNumericValue($item['min_lot_value_idr'] ?? null)) {
            return (float) $item['min_lot_value_idr'];
        }

        return (float) $paramset['recommendation']['capital']['default_min_lot_value_idr'];
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

    private function roundScore(float $value): float
    {
        return round(max(0.0, min(1.0, $value)), 6);
    }
}
