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

        $items = $this->deduplicateBestItems($this->collectEligibleItems($scoredOutput['items'] ?? [], $payload));

        foreach ($items as $item) {
            $scoreTotal = (float) $item['score_total'];

            if ($scoreTotal >= $resolvedParamset['grouping']['top_picks']['min_score_total']
                && count($payload['groups']['TOP_PICKS']) < $resolvedParamset['grouping']['top_picks']['max_items']) {
                $payload['groups']['TOP_PICKS'][] = $this->groupItem($item, 'TOP_PICKS', 'WS_PLAN_TOP_PICK', count($payload['groups']['TOP_PICKS']) + 1);
                continue;
            }

            if ($scoreTotal >= $resolvedParamset['grouping']['secondary']['min_score_total']
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

    private function collectEligibleItems(array $items, array &$payload): array
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

        return [
            'policy_code' => (string) ($paramset['policy_code'] ?? $defaults['policy_code']),
            'policy_version' => (string) ($paramset['policy_version'] ?? $defaults['policy_version']),
            'paramset_code' => (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']),
            'grouping' => [
                'grouping_mode' => (string) $this->paramValueMixed($paramset, ['grouping', 'grouping_mode'], $defaults['grouping']['grouping_mode']),
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
            ],
        ];
    }

    private function validateParamset(array $paramset): array
    {
        $errors = [];

        if ($paramset['grouping']['grouping_mode'] !== 'PLAN_GROUPING_DETERMINISTIC') {
            $errors[] = 'grouping.grouping_mode must be PLAN_GROUPING_DETERMINISTIC';
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
