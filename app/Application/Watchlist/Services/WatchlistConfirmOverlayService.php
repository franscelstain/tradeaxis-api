<?php

namespace App\Application\Watchlist\Services;

class WatchlistConfirmOverlayService
{
    private const ACTIVE_PLAN_GROUPS = [
        'TOP_PICKS',
        'SECONDARY',
        'WATCH_ONLY',
    ];

    private const ALLOWED_CONFIRM_STATES = [
        'CONFIRMED',
        'NOT_CONFIRMED',
        'REJECTED',
        'NO_DATA',
    ];

    private WatchlistPlanGroupingService $planGroupingService;
    private WatchlistRecommendationService $recommendationService;

    public function __construct(
        WatchlistPlanGroupingService $planGroupingService = null,
        WatchlistRecommendationService $recommendationService = null
    ) {
        $this->planGroupingService = $planGroupingService ?: new WatchlistPlanGroupingService();
        $this->recommendationService = $recommendationService ?: new WatchlistRecommendationService($this->planGroupingService);
    }

    public function confirmForTradeDate(
        string $tradeDate,
        array $confirmInputs = [],
        array $paramset = [],
        array $capitalInput = []
    ): array {
        $planOutput = $this->planGroupingService->groupForTradeDate($tradeDate, $paramset);
        $recommendationOutput = $this->recommendationService->recommendFromPlanOutput($planOutput, $paramset, $capitalInput);

        return $this->confirmFromPlanAndRecommendationOutput($planOutput, $recommendationOutput, $confirmInputs);
    }

    public function confirmFromPlanAndRecommendationOutput(
        array $planOutput,
        array $recommendationOutput,
        array $confirmInputs = []
    ): array {
        $payload = $this->basePayload($planOutput, $recommendationOutput);

        if (! ($planOutput['is_ready'] ?? $planOutput['ready'] ?? false)) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_CONFIRM_SOURCE_PLAN_NOT_READY';
            $payload['source_reason_code'] = $planOutput['reason_code'] ?? null;

            return $payload;
        }

        if (! ($recommendationOutput['is_ready'] ?? $recommendationOutput['ready'] ?? false)) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_CONFIRM_SOURCE_RECOMMENDATION_NOT_READY';
            $payload['source_reason_code'] = $recommendationOutput['reason_code'] ?? null;

            return $payload;
        }

        $planCandidates = $this->collectPlanCandidates($planOutput);
        $nonActivePlanIndex = $this->indexByTickerIdentity($this->collectNonActivePlanCandidates($planOutput));
        $recommendationIndex = $this->indexByTickerIdentity($recommendationOutput['items'] ?? []);
        $confirmEvidenceByKey = $this->normalizeConfirmEvidence($confirmInputs);
        $matchedEvidenceKeys = [];
        $confirmedCount = 0;
        $noDataCount = 0;
        $recommendedPlanCandidateCount = 0;
        $nonRecommendedPlanCandidateCount = 0;

        foreach ($planCandidates as $planItem) {
            $lookupKeys = $this->identityKeys($planItem);
            $evidence = $this->firstEvidenceForKeys($confirmEvidenceByKey, $lookupKeys);
            $evidenceKey = $evidence['__evidence_key'] ?? null;

            if ($evidenceKey !== null) {
                $matchedEvidenceKeys[$evidenceKey] = true;
            }

            $recommendationItem = $this->firstItemForKeys($recommendationIndex, $lookupKeys);
            $overlayItem = $this->overlayItem($planItem, $recommendationItem, $evidence);

            if ($overlayItem['recommended_flag'] === true) {
                $recommendedPlanCandidateCount++;
            } else {
                $nonRecommendedPlanCandidateCount++;
            }

            if ($overlayItem['confirm_state'] === 'CONFIRMED') {
                $confirmedCount++;
            }
            if ($overlayItem['confirm_state'] === 'NO_DATA') {
                $noDataCount++;
            }

            $payload['items'][] = $overlayItem;
        }

        foreach ($confirmEvidenceByKey as $evidenceKey => $evidence) {
            if (isset($matchedEvidenceKeys[$evidenceKey])) {
                continue;
            }

            $reasonCode = $this->firstItemForKeys($nonActivePlanIndex, $this->identityKeys($evidence)) !== null
                ? 'WS_CONFIRM_REJECTED_NOT_PLAN_CANDIDATE'
                : 'WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE';
            $payload['excluded'][] = $this->rejectedEvidenceItem($evidence, $reasonCode);
        }

        $payload['summary'] = [
            'plan_candidate_count' => count($planCandidates),
            'evaluated_count' => count($payload['items']),
            'recommended_plan_candidate_count' => $recommendedPlanCandidateCount,
            'non_recommended_plan_candidate_count' => $nonRecommendedPlanCandidateCount,
            'confirmed_count' => $confirmedCount,
            'no_data_count' => $noDataCount,
            'excluded_count' => count($payload['excluded']),
            'unknown_candidate_count' => count($payload['excluded']),
            'recommendation_mutated' => false,
            'score_mutated' => false,
            'rank_mutated' => false,
            'label_mutated' => false,
            'hash_mutated' => false,
        ];
        $payload['ready'] = true;
        $payload['is_ready'] = true;
        $payload['reason_code'] = 'WATCHLIST_CONFIRM_OVERLAY_READY';

        return $payload;
    }

    public function confirmFromRecommendationOutput(array $recommendationOutput, array $confirmInputs = []): array
    {
        $groups = [
            'TOP_PICKS' => [],
            'SECONDARY' => [],
            'WATCH_ONLY' => [],
            'AVOID' => [],
        ];

        foreach (($recommendationOutput['items'] ?? []) as $item) {
            $planGroup = (string) ($item['plan_reference']['plan_group'] ?? $item['plan_group_semantic'] ?? 'SECONDARY');
            if (! in_array($planGroup, self::ACTIVE_PLAN_GROUPS, true)) {
                $planGroup = 'SECONDARY';
            }

            $groups[$planGroup][] = [
                'ticker_id' => $item['ticker_id'] ?? null,
                'ticker_code' => $item['ticker'] ?? $item['ticker_code'] ?? null,
                'plan_group' => $planGroup,
                'group_semantic' => $planGroup,
                'plan_rank' => $item['plan_rank'] ?? null,
                'group_rank' => $item['plan_reference']['group_rank'] ?? null,
                'group_reason_code' => $item['plan_reference']['group_reason_code'] ?? null,
                'score_total' => $item['plan_reference']['score_total'] ?? $item['recommendation_score'] ?? null,
                'reason_codes' => $item['reason_codes'] ?? [],
                'eligible_plan_group' => true,
            ];
        }

        $planOutput = [
            'ready' => $recommendationOutput['ready'] ?? false,
            'is_ready' => $recommendationOutput['is_ready'] ?? false,
            'reason_code' => $recommendationOutput['meta']['source_plan_reference']['reason_code'] ?? null,
            'trade_date' => $recommendationOutput['meta']['trade_date'] ?? null,
            'trade_date_effective' => $recommendationOutput['meta']['trade_date_effective'] ?? null,
            'publication_id' => $recommendationOutput['meta']['publication_id'] ?? null,
            'publication_version' => $recommendationOutput['meta']['publication_version'] ?? null,
            'run_id' => $recommendationOutput['meta']['run_id'] ?? null,
            'policy_code' => $recommendationOutput['meta']['policy_code'] ?? null,
            'policy_version' => $recommendationOutput['meta']['policy_version'] ?? null,
            'paramset_code' => $recommendationOutput['meta']['paramset_code'] ?? null,
            'groups' => $groups,
            'excluded' => [],
        ];

        return $this->confirmFromPlanAndRecommendationOutput($planOutput, $recommendationOutput, $confirmInputs);
    }

    private function basePayload(array $planOutput, array $recommendationOutput): array
    {
        $meta = $recommendationOutput['meta'] ?? [];

        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_CONFIRM_OVERLAY_NOT_EVALUATED',
            'policy_code' => $planOutput['policy_code'] ?? $meta['policy_code'] ?? null,
            'policy_version' => $planOutput['policy_version'] ?? $meta['policy_version'] ?? null,
            'paramset_code' => $planOutput['paramset_code'] ?? $meta['paramset_code'] ?? null,
            'trade_date' => $planOutput['trade_date'] ?? $meta['trade_date'] ?? null,
            'trade_date_effective' => $planOutput['trade_date_effective'] ?? $meta['trade_date_effective'] ?? null,
            'publication_id' => $planOutput['publication_id'] ?? $meta['publication_id'] ?? null,
            'publication_version' => $planOutput['publication_version'] ?? $meta['publication_version'] ?? null,
            'run_id' => $planOutput['run_id'] ?? $meta['run_id'] ?? null,
            'plan_hash' => $planOutput['plan_hash'] ?? $meta['plan_hash'] ?? null,
            'recommendation_hash' => $recommendationOutput['recommendation_hash'] ?? $meta['recommendation_hash'] ?? null,
            'source_contract' => [
                'consumer' => 'WatchlistConfirmOverlayService',
                'upstream' => 'WatchlistPlanGroupingService immutable PLAN candidate binding plus WatchlistRecommendationService immutable recommendation membership',
                'plan_binding_source' => 'WatchlistPlanGroupingService',
                'recommendation_membership_source' => 'WatchlistRecommendationService',
                'no_raw_market_data' => true,
                'no_latest_shortcut' => true,
                'no_plan_mutation' => true,
                'no_recommendation_mutation' => true,
                'no_score_mutation' => true,
                'no_rank_mutation' => true,
                'no_label_mutation' => true,
                'no_hash_mutation' => true,
                'no_portfolio' => true,
                'no_capital' => true,
                'no_execution' => true,
                'no_backtest' => true,
                'no_api_command_runtime' => true,
            ],
            'confirm_contract' => [
                'eligibility_source' => 'candidate PLAN membership',
                'recommended_plan_candidate_can_confirm' => true,
                'non_recommended_plan_candidate_can_confirm' => true,
                'unknown_candidate_rejected_to_diagnostics' => true,
                'confirm_does_not_create_recommendation_membership' => true,
                'confirm_does_not_remove_recommendation_membership' => true,
                'not_execution_instruction' => true,
                'not_backtest_result' => true,
            ],
            'immutability_contract' => [
                'adds_overlay_only' => true,
                'protected_fields' => [
                    'ticker_id',
                    'ticker_code',
                    'plan_group',
                    'plan_rank',
                    'score_total',
                    'score_components',
                    'reason_codes',
                    'recommendation_rank',
                    'recommendation_score',
                    'recommendation_label',
                    'recommended_flag',
                    'hash',
                    'plan_hash',
                    'recommendation_hash',
                ],
            ],
            'items' => [],
            'excluded' => [],
            'summary' => [
                'plan_candidate_count' => 0,
                'evaluated_count' => 0,
                'recommended_plan_candidate_count' => 0,
                'non_recommended_plan_candidate_count' => 0,
                'confirmed_count' => 0,
                'no_data_count' => 0,
                'excluded_count' => 0,
                'unknown_candidate_count' => 0,
                'recommendation_mutated' => false,
                'score_mutated' => false,
                'rank_mutated' => false,
                'label_mutated' => false,
                'hash_mutated' => false,
            ],
        ];
    }

    private function collectPlanCandidates(array $planOutput): array
    {
        $items = [];

        foreach (self::ACTIVE_PLAN_GROUPS as $group) {
            foreach (($planOutput['groups'][$group] ?? []) as $index => $item) {
                $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
                $tickerCode = $this->tickerCode($item);

                if ($tickerId === null && $tickerCode === '') {
                    continue;
                }

                $item['ticker_id'] = $tickerId;
                $item['ticker_code'] = $tickerCode;
                $item['plan_group'] = $item['plan_group'] ?? $group;
                $item['group_semantic'] = $item['group_semantic'] ?? $group;
                $item['plan_rank'] = $this->intOrNull($item['plan_rank'] ?? null) ?? ($index + 1);
                $item['group_rank'] = $this->intOrNull($item['group_rank'] ?? null) ?? ($index + 1);
                $items[] = $item;
            }
        }

        usort($items, function (array $left, array $right): int {
            $leftRank = $this->intOrNull($left['plan_rank'] ?? null) ?? PHP_INT_MAX;
            $rightRank = $this->intOrNull($right['plan_rank'] ?? null) ?? PHP_INT_MAX;
            if ($leftRank !== $rightRank) {
                return $leftRank < $rightRank ? -1 : 1;
            }

            $leftTickerId = $this->intOrNull($left['ticker_id'] ?? null) ?? PHP_INT_MAX;
            $rightTickerId = $this->intOrNull($right['ticker_id'] ?? null) ?? PHP_INT_MAX;
            if ($leftTickerId !== $rightTickerId) {
                return $leftTickerId < $rightTickerId ? -1 : 1;
            }

            return strcmp($this->tickerCode($left), $this->tickerCode($right));
        });

        return $items;
    }

    private function collectNonActivePlanCandidates(array $planOutput): array
    {
        $items = [];

        foreach (($planOutput['groups']['AVOID'] ?? []) as $item) {
            $items[] = $item;
        }
        foreach (($planOutput['excluded'] ?? []) as $item) {
            $items[] = $item;
        }

        return $items;
    }

    private function overlayItem(array $planItem, ?array $recommendationItem, ?array $evidence): array
    {
        $recommendedFlag = $recommendationItem !== null
            ? (bool) ($recommendationItem['recommended_flag'] ?? false)
            : false;
        $state = $evidence !== null ? $evidence['confirm_state'] : 'NO_DATA';
        $confirmReasonCodes = $this->confirmReasonCodes($recommendedFlag, $state, $evidence);

        $item = [
            'ticker' => $this->tickerCode($planItem),
            'ticker_id' => $this->intOrNull($planItem['ticker_id'] ?? null),
            'ticker_code' => $this->tickerCode($planItem),
            'plan_group' => $planItem['plan_group'] ?? null,
            'group_semantic' => $planItem['group_semantic'] ?? $planItem['plan_group'] ?? null,
            'plan_rank' => $this->intOrNull($planItem['plan_rank'] ?? null),
            'group_rank' => $this->intOrNull($planItem['group_rank'] ?? null),
            'score_total' => $planItem['score_total'] ?? null,
            'score_components' => $planItem['score_components'] ?? [],
            'reason_codes' => $planItem['reason_codes'] ?? [],
            'recommendation_rank' => $recommendationItem['recommendation_rank'] ?? null,
            'recommendation_score' => $recommendationItem['recommendation_score'] ?? null,
            'recommendation_label' => $recommendationItem['recommendation_label'] ?? 'NOT_RECOMMENDED_PLAN_CANDIDATE',
            'recommended_flag' => $recommendedFlag,
            'recommendation_reason_codes' => $recommendationItem['reason_codes'] ?? [],
            'confirm_eligible' => true,
            'confirm_state' => $state,
            'confirm_reason_codes' => $confirmReasonCodes,
            'confirm_overlay' => [
                'state' => $state,
                'source' => $evidence['confirm_source'] ?? null,
                'notes' => $evidence['confirm_notes'] ?? null,
                'confirmed_at' => $evidence['confirmed_at'] ?? null,
                'semantics' => $recommendedFlag ? 'strengthening_signal' : 'candidate_validation',
            ],
        ];

        foreach (['hash', 'plan_hash', 'recommendation_hash', 'plan_candidate_id', 'plan_candidate_key'] as $field) {
            if (array_key_exists($field, $planItem)) {
                $item[$field] = $planItem[$field];
            } elseif ($recommendationItem !== null && array_key_exists($field, $recommendationItem)) {
                $item[$field] = $recommendationItem[$field];
            }
        }

        return $item;
    }

    private function confirmReasonCodes(bool $recommendedFlag, string $state, ?array $evidence): array
    {
        if ($state === 'NO_DATA') {
            return ['WS_CONFIRM_NO_DATA'];
        }

        $reasonCodes = [
            $recommendedFlag ? 'WS_CONFIRM_ELIGIBLE_RECOMMENDED' : 'WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED',
        ];

        foreach (($evidence['confirm_reason_codes'] ?? []) as $reasonCode) {
            if (is_string($reasonCode) && trim($reasonCode) !== '') {
                $reasonCodes[] = strtoupper(trim($reasonCode));
            }
        }

        if ($state === 'CONFIRMED') {
            $reasonCodes[] = 'WS_CONFIRM_APPLIED';
        } else {
            $reasonCodes[] = 'WS_CONFIRM_NOT_APPLIED';
        }

        return array_values(array_unique($reasonCodes));
    }

    private function rejectedEvidenceItem(array $evidence, string $reasonCode): array
    {
        $state = $evidence['confirm_state'] ?? 'NO_DATA';

        return [
            'ticker_id' => $this->intOrNull($evidence['ticker_id'] ?? null),
            'ticker_code' => $this->tickerCode($evidence),
            'confirm_eligible' => false,
            'confirm_state' => $state,
            'confirm_reason_codes' => [$reasonCode],
            'confirm_overlay' => [
                'state' => $state,
                'source' => $evidence['confirm_source'] ?? null,
                'notes' => $evidence['confirm_notes'] ?? null,
                'confirmed_at' => $evidence['confirmed_at'] ?? null,
            ],
        ];
    }

    private function normalizeConfirmEvidence(array $confirmInputs): array
    {
        $normalized = [];

        foreach ($confirmInputs as $input) {
            if (! is_array($input)) {
                continue;
            }

            $state = strtoupper(trim((string) ($input['confirm_state'] ?? 'NO_DATA')));
            if (! in_array($state, self::ALLOWED_CONFIRM_STATES, true)) {
                $state = 'NO_DATA';
            }

            $evidence = $input;
            $evidence['ticker_id'] = $this->intOrNull($input['ticker_id'] ?? null);
            $evidence['ticker_code'] = $this->tickerCode($input);
            $evidence['confirm_state'] = $state;

            $keys = $this->identityKeys($evidence);
            if ($keys === []) {
                continue;
            }

            $primaryKey = $keys[0];
            $evidence['__evidence_key'] = $primaryKey;
            $normalized[$primaryKey] = $evidence;
        }

        return $normalized;
    }

    private function firstEvidenceForKeys(array $evidenceByKey, array $keys): ?array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $evidenceByKey)) {
                return $evidenceByKey[$key];
            }
        }

        return null;
    }

    private function firstItemForKeys(array $itemsByKey, array $keys): ?array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $itemsByKey)) {
                return $itemsByKey[$key];
            }
        }

        return null;
    }

    private function indexByTickerIdentity(array $items): array
    {
        $indexed = [];

        foreach ($items as $item) {
            foreach ($this->identityKeys($item) as $key) {
                if (! array_key_exists($key, $indexed)) {
                    $indexed[$key] = $item;
                }
            }
        }

        return $indexed;
    }

    private function identityKeys(array $item): array
    {
        $keys = [];
        $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
        $tickerCode = $this->tickerCode($item);

        if ($tickerId !== null) {
            $keys[] = 'id:'.$tickerId;
        }
        if ($tickerCode !== '') {
            $keys[] = 'code:'.$tickerCode;
        }

        return $keys;
    }

    private function tickerCode(array $item): string
    {
        $ticker = $item['ticker_code'] ?? $item['ticker'] ?? '';

        return strtoupper(trim((string) $ticker));
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
