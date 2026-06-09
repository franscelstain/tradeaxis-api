<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestStrategyService
{
    public const DEFAULT_PARAMSET = [
        'policy_code' => 'WS',
        'policy_version' => 'WS_EOD_RUNTIME',
        'schema_version' => 'PARAMSET_JSON',
        'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
        'eval' => [
            'min_trades_oos' => [
                'value' => 40,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical OOS minimum trade-count floor from the locked OOS contract.',
                'change_triggers' => ['OOS window changes', 'Trade definition changes'],
            ],
            'min_trades' => [
                'value' => 120,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical in-sample minimum trade-count floor from the locked metric-sufficiency contract.',
                'change_triggers' => ['Backtest window changes', 'Trade definition changes'],
            ],
            'min_days_covered' => [
                'value' => 0,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Zero is a documented dynamic sentinel; runtime resolves it to ceil(70% of trading days in the evaluation window).',
                'change_triggers' => ['Coverage definition changes', 'Backtest window changes'],
            ],
            'min_p25_ret_net_top' => [
                'value' => -0.03,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical bootstrap downside floor for TOP picks.',
                'change_triggers' => ['Volatility regime changes', 'Risk tolerance changes'],
            ],
            'min_month_win_rate_min' => [
                'value' => 0.45,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical bootstrap monthly win-rate stability floor.',
                'change_triggers' => ['Evaluation period definition changes'],
            ],
            'min_month_avg_ret_net_min' => [
                'value' => -0.01,
                'origin' => 'DET',
                'status' => 'ACTIVE',
                'bt_target' => false,
                'rationale' => 'Canonical bootstrap monthly average-return stability floor.',
                'change_triggers' => ['Evaluation period definition changes', 'Risk tolerance changes'],
            ],
        ],
        'backtest' => [
            'engine_mode' => 'PLAN_RECOMMENDATION_REPLAY_FOUNDATION',
            'replay_mode' => 'EXPLICIT_TRADE_DATE_WINDOW',
            'entry_model' => 'D_PLUS_1_OPEN_DOCUMENTED',
            'exit_model' => 'WEEKLY_SWING_MAX_5_TRADING_DAYS_DOCUMENTED',
            'pricing_model' => 'FOUNDATION_ONLY_PRICE_SERIES_NOT_CONSUMED',
            'fee_model' => 'IDR_FIXED',
            'fee_buy_idr' => 2500.0,
            'fee_sell_idr' => 2500.0,
            'notional_idr' => 10000000.0,
            'lot_size' => 100,
            'slippage_entry_pct' => 0.0,
            'slippage_exit_pct' => 0.0,
            'holding_days' => 5,
            'tradable_bar_rule' => 'POSITIVE_VOLUME_REQUIRED',
            'min_tradable_volume' => 1,
            'sort_keys' => [
                'trade_date_asc',
                'recommendation_rank_asc',
                'plan_rank_asc',
                'ticker_id_asc',
            ],
        ],
    ];

    private WatchlistPlanGroupingService $planGroupingService;
    private WatchlistRecommendationService $recommendationService;
    private WatchlistConfirmOverlayService $confirmOverlayService;

    public function __construct(
        WatchlistPlanGroupingService $planGroupingService = null,
        WatchlistRecommendationService $recommendationService = null,
        WatchlistConfirmOverlayService $confirmOverlayService = null
    ) {
        $this->planGroupingService = $planGroupingService ?: new WatchlistPlanGroupingService();
        $this->recommendationService = $recommendationService ?: new WatchlistRecommendationService($this->planGroupingService);
        $this->confirmOverlayService = $confirmOverlayService
            ?: new WatchlistConfirmOverlayService($this->planGroupingService, $this->recommendationService);
    }

    public function backtestForReplayWindow(
        array $tradeDates,
        array $confirmInputsByTradeDate = [],
        array $paramset = [],
        array $capitalInput = []
    ): array {
        $resolvedParamset = $this->resolveParamset($paramset);
        $replayDates = $this->normalizeTradeDates($tradeDates);
        $payload = $this->basePayload($replayDates, $resolvedParamset);

        if ($replayDates === []) {
            $payload['ready'] = false;
            $payload['is_ready'] = false;
            $payload['reason_code'] = 'WATCHLIST_BACKTEST_REPLAY_WINDOW_EMPTY';
            $payload['diagnostics'][] = $this->diagnosticItem(null, 'WATCHLIST_BACKTEST_REPLAY_WINDOW_EMPTY', [
                'message' => 'Explicit replay window contains no valid trade date.',
            ]);

            return $payload;
        }

        $fatalNoLookahead = false;
        $daysEvaluated = 0;

        foreach ($replayDates as $tradeDate) {
            $planOutput = $this->planGroupingService->groupForTradeDate($tradeDate, $resolvedParamset);
            $recommendationOutput = $this->recommendationService->recommendFromPlanOutput($planOutput, $resolvedParamset, $capitalInput);
            $confirmInputs = $confirmInputsByTradeDate[$tradeDate] ?? [];
            $confirmOutput = $this->confirmOverlayService->confirmFromPlanAndRecommendationOutput(
                $planOutput,
                $recommendationOutput,
                is_array($confirmInputs) ? $confirmInputs : []
            );

            $sourceReadiness = $this->validateSourceReadiness($tradeDate, $planOutput, $recommendationOutput, $confirmOutput);
            if ($sourceReadiness !== []) {
                foreach ($sourceReadiness as $diagnostic) {
                    $payload['diagnostics'][] = $diagnostic;
                    if (($diagnostic['fatal'] ?? false) === true) {
                        $fatalNoLookahead = true;
                    }
                }

                if ($fatalNoLookahead) {
                    continue;
                }
            }

            if (! ($planOutput['is_ready'] ?? $planOutput['ready'] ?? false)) {
                $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_PLAN_NOT_READY', [
                    'source_reason_code' => $planOutput['reason_code'] ?? null,
                ]);
                continue;
            }

            if (! ($recommendationOutput['is_ready'] ?? $recommendationOutput['ready'] ?? false)) {
                $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_RECOMMENDATION_NOT_READY', [
                    'source_reason_code' => $recommendationOutput['reason_code'] ?? null,
                ]);
                continue;
            }

            if (! ($confirmOutput['is_ready'] ?? $confirmOutput['ready'] ?? false)) {
                $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_CONFIRM_NOT_READY', [
                    'source_reason_code' => $confirmOutput['reason_code'] ?? null,
                ]);
                continue;
            }

            $daysEvaluated++;
            $this->appendDailyReplay($payload, $tradeDate, $planOutput, $recommendationOutput, $confirmOutput, $resolvedParamset);
        }

        $payload['items'] = $this->sortItems($payload['items']);
        $payload['trades'] = $this->sortTrades($payload['trades']);
        $payload['evaluations'] = $this->sortEvaluations($payload['evaluations']);
        $payload['summary'] = $this->buildSummary($payload, $daysEvaluated, $fatalNoLookahead);
        $payload['ready'] = ! $fatalNoLookahead && $daysEvaluated > 0;
        $payload['is_ready'] = $payload['ready'];
        $payload['reason_code'] = $this->resolveReasonCode($payload, $fatalNoLookahead, $daysEvaluated);
        $payload['backtest_reason_code'] = $payload['reason_code'];
        $payload['meta']['backtest_reason_code'] = $payload['reason_code'];

        return $payload;
    }

    public static function defaultParamset(): array
    {
        return self::DEFAULT_PARAMSET;
    }

    private function appendDailyReplay(
        array &$payload,
        string $tradeDate,
        array $planOutput,
        array $recommendationOutput,
        array $confirmOutput,
        array $paramset
    ): void {
        $recommendationIndex = $this->indexByTickerIdentity($recommendationOutput['items'] ?? []);
        $confirmIndex = $this->indexByTickerIdentity($confirmOutput['items'] ?? []);
        $recommendedCountForDate = 0;

        foreach (($confirmOutput['items'] ?? []) as $confirmItem) {
            $recommendationItem = $this->firstItemForKeys($recommendationIndex, $this->identityKeys($confirmItem));
            $isRecommended = (bool) (($recommendationItem['recommended_flag'] ?? false) === true);

            $payload['items'][] = $this->replayItem($tradeDate, $confirmItem, $recommendationItem, $isRecommended);
        }

        foreach (($recommendationOutput['items'] ?? []) as $recommendationItem) {
            if (($recommendationItem['recommended_flag'] ?? false) !== true) {
                continue;
            }

            $confirmItem = $this->firstItemForKeys($confirmIndex, $this->identityKeys($recommendationItem));
            $trade = $this->tradeCandidate($tradeDate, $recommendationItem, $confirmItem, $planOutput, $recommendationOutput, $paramset);
            $payload['trades'][] = $trade;
            $payload['evaluations'][] = $this->evaluationItem($tradeDate, $trade, $paramset);
            $recommendedCountForDate++;
        }

        foreach (($confirmOutput['excluded'] ?? []) as $excluded) {
            $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_CONFIRM_EVIDENCE_EXCLUDED', [
                'ticker_id' => $excluded['ticker_id'] ?? null,
                'ticker' => $excluded['ticker'] ?? $excluded['ticker_code'] ?? null,
                'reason_codes' => $excluded['reason_codes'] ?? [],
                'confirm_state' => $excluded['confirm_state'] ?? null,
                'active_trade_evaluation' => false,
            ]);
        }

        if ($recommendedCountForDate === 0) {
            $payload['diagnostics'][] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID', [
                'reason_codes' => ['WS_REC_EMPTY_SET'],
                'active_trade_evaluation' => false,
            ]);
        }
    }

    private function replayItem(string $tradeDate, array $confirmItem, ?array $recommendationItem, bool $isRecommended): array
    {
        $reasonCodes = array_merge($confirmItem['reason_codes'] ?? [], $confirmItem['confirm_reason_codes'] ?? []);
        if ($isRecommended) {
            $reasonCodes[] = 'WS_REC_SELECTED';
        }

        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $this->intOrNull($confirmItem['ticker_id'] ?? null),
            'ticker' => $this->tickerCode($confirmItem),
            'plan_group' => $confirmItem['plan_group'] ?? $confirmItem['group_semantic'] ?? null,
            'plan_rank' => $this->intOrNull($confirmItem['plan_rank'] ?? null),
            'recommendation_rank' => $this->intOrNull($recommendationItem['recommendation_rank'] ?? null),
            'recommended_flag' => $isRecommended,
            'confirm_state' => $confirmItem['confirm_state'] ?? 'NO_DATA',
            'confirm_overlay_applied' => (bool) (($confirmItem['confirm_overlay_applied'] ?? false) === true),
            'active_trade_evaluation' => $isRecommended,
            'reason_codes' => $this->uniqueReasonCodes($reasonCodes),
            'source_snapshot' => [
                'plan_binding_preserved' => true,
                'recommendation_membership_preserved' => true,
                'confirm_overlay_diagnostic_only' => true,
            ],
        ];
    }

    private function tradeCandidate(
        string $tradeDate,
        array $recommendationItem,
        ?array $confirmItem,
        array $planOutput,
        array $recommendationOutput,
        array $paramset
    ): array {
        $planReference = $recommendationItem['plan_reference'] ?? [];
        $reasonCodes = $recommendationItem['reason_codes'] ?? [];
        $reasonCodes[] = 'WS_REC_SELECTED';

        if ($confirmItem !== null && ($confirmItem['confirm_state'] ?? null) === 'CONFIRMED') {
            $reasonCodes[] = 'WS_CONFIRM_APPLIED';
        }

        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $this->intOrNull($recommendationItem['ticker_id'] ?? null),
            'ticker' => $this->tickerCode($recommendationItem),
            'bucket_code' => $planReference['plan_group'] ?? $recommendationItem['plan_group_semantic'] ?? null,
            'plan_rank' => $this->intOrNull($recommendationItem['plan_rank'] ?? $planReference['plan_rank'] ?? null),
            'recommendation_rank' => $this->intOrNull($recommendationItem['recommendation_rank'] ?? null),
            'recommendation_score' => $this->floatOrNull($recommendationItem['recommendation_score'] ?? null),
            'confirm_state' => $confirmItem['confirm_state'] ?? 'NO_DATA',
            'trade_state' => 'EVALUATION_CANDIDATE',
            'entry_model' => $paramset['backtest']['entry_model'],
            'exit_model' => $paramset['backtest']['exit_model'],
            'pricing_model' => $paramset['backtest']['pricing_model'],
            'reason_codes' => $this->uniqueReasonCodes($reasonCodes),
            'source_reference' => [
                'plan_hash' => $planOutput['plan_hash'] ?? null,
                'recommendation_hash' => $recommendationOutput['recommendation_hash'] ?? null,
                'publication_id' => $planOutput['publication_id'] ?? $recommendationOutput['meta']['publication_id'] ?? null,
                'publication_version' => $planOutput['publication_version'] ?? $recommendationOutput['meta']['publication_version'] ?? null,
                'run_id' => $planOutput['run_id'] ?? $recommendationOutput['meta']['run_id'] ?? null,
            ],
            'contract_flags' => [
                'from_recommendation_layer' => true,
                'confirm_does_not_create_recommendation' => true,
                'no_lookahead_price_used' => true,
                'not_broker_surface' => true,
            ],
        ];
    }

    private function evaluationItem(string $tradeDate, array $trade, array $paramset): array
    {
        return [
            'trade_date' => $tradeDate,
            'ticker_id' => $trade['ticker_id'],
            'ticker' => $trade['ticker'],
            'bucket_code' => $trade['bucket_code'],
            'evaluation_state' => 'FOUNDATION_REPLAY_ONLY',
            'metrics_ready' => false,
            'eval_model' => $this->evalModel($paramset),
            'ret_net' => null,
            'is_win' => null,
            'reason_codes' => ['WS_BT_EVAL_METRICS_MISSING'],
            'explainability' => [
                'selected_by_recommendation' => true,
                'confirm_state_observed' => $trade['confirm_state'],
                'price_series_consumed' => false,
                'metrics_deferred_to_runtime_artifact' => true,
            ],
        ];
    }

    private function basePayload(array $replayDates, array $paramset): array
    {
        $sourceContract = [
            'consumer' => 'WatchlistBacktestStrategyService',
            'upstream' => [
                'WatchlistPlanGroupingService',
                'WatchlistRecommendationService',
                'WatchlistConfirmOverlayService',
            ],
            'plan_binding_source' => 'WatchlistPlanGroupingService',
            'recommendation_source' => 'WatchlistRecommendationService',
            'confirm_overlay_source' => 'WatchlistConfirmOverlayService',
            'recommendation_layer_only' => true,
            'confirm_overlay_diagnostic_only' => true,
            'no_raw_market_data' => true,
            'no_latest_shortcut' => true,
            'no_max_trade_date_shortcut' => true,
            'no_plan_mutation' => true,
            'no_recommendation_mutation' => true,
            'no_confirm_mutation' => true,
            'no_portfolio_state' => true,
            'no_broker_surface' => true,
            'no_order_automation' => true,
        ];

        $backtestContract = [
            'foundation_only' => true,
            'no_lookahead' => true,
            'deterministic_replay' => true,
            'publication_aware_replay' => true,
            'explicit_replay_window_only' => true,
            'same_trade_date_source_alignment' => true,
            'entry_exit_assumptions_documented' => true,
            'eod_only' => true,
            'price_series_consumed' => false,
            'metrics_ready' => false,
            'not_production_ready' => true,
        ];

        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => 'WATCHLIST_BACKTEST_NOT_EVALUATED',
            'backtest_reason_code' => 'WATCHLIST_BACKTEST_NOT_EVALUATED',
            'meta' => [
                'strategy_code' => $paramset['policy_code'],
                'policy_code' => $paramset['policy_code'],
                'policy_version' => $paramset['policy_version'],
                'paramset_code' => $paramset['paramset_code'],
                'engine' => 'WatchlistBacktestStrategyService',
                'backtest_reason_code' => 'WATCHLIST_BACKTEST_NOT_EVALUATED',
                'source_contract' => $sourceContract,
                'backtest_contract' => $backtestContract,
                'paramset_snapshot' => $paramset,
                'replay_window' => $this->replayWindow($replayDates),
            ],
            'source_contract' => $sourceContract,
            'backtest_contract' => $backtestContract,
            'paramset_snapshot' => $paramset,
            'replay_window' => $this->replayWindow($replayDates),
            'items' => [],
            'trades' => [],
            'evaluations' => [],
            'summary' => [
                'days_requested' => count($replayDates),
                'days_evaluated' => 0,
                'items_count' => 0,
                'picks_count' => 0,
                'evaluations_count' => 0,
                'empty_recommendation_days' => 0,
                'avg_ret_net_top' => null,
                'win_rate_top' => null,
                'metrics_ready' => false,
                'artifact_runtime_persistence' => false,
                'production_ready' => false,
                'reason_codes' => [],
            ],
            'diagnostics' => [],
            'artifact_manifest' => $this->artifactManifest(),
        ];
    }

    private function validateSourceReadiness(string $tradeDate, array $planOutput, array $recommendationOutput, array $confirmOutput): array
    {
        $diagnostics = [];
        foreach ([
            ['PLAN', $planOutput, $planOutput['trade_date'] ?? null, $planOutput['trade_date_effective'] ?? null],
            ['RECOMMENDATION', $recommendationOutput, $recommendationOutput['meta']['trade_date'] ?? null, $recommendationOutput['meta']['trade_date_effective'] ?? null],
            ['CONFIRM', $confirmOutput, $confirmOutput['trade_date'] ?? null, $confirmOutput['trade_date_effective'] ?? null],
        ] as $source) {
            [$sourceName, $output, $sourceTradeDate, $effectiveDate] = $source;

            if ($sourceTradeDate !== null && (string) $sourceTradeDate !== $tradeDate) {
                $diagnostics[] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_SOURCE_TRADE_DATE_MISMATCH', [
                    'source' => $sourceName,
                    'source_trade_date' => $sourceTradeDate,
                    'expected_trade_date' => $tradeDate,
                    'fatal' => true,
                ]);
            }

            if ($effectiveDate !== null && $this->isDateAfter((string) $effectiveDate, $tradeDate)) {
                $diagnostics[] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION', [
                    'source' => $sourceName,
                    'trade_date_effective' => $effectiveDate,
                    'expected_not_after' => $tradeDate,
                    'reason_codes' => ['WS_BT_OOS_PROOF_MISSING'],
                    'fatal' => true,
                ]);
            }
        }

        $planPublication = $planOutput['publication_id'] ?? null;
        $recommendationPublication = $recommendationOutput['meta']['publication_id'] ?? null;
        $confirmPublication = $confirmOutput['publication_id'] ?? null;

        if ($this->hasMismatch([$planPublication, $recommendationPublication, $confirmPublication])) {
            $diagnostics[] = $this->diagnosticItem($tradeDate, 'WATCHLIST_BACKTEST_PUBLICATION_MISMATCH', [
                'publication_ids' => [$planPublication, $recommendationPublication, $confirmPublication],
                'fatal' => true,
            ]);
        }

        return $diagnostics;
    }

    private function buildSummary(array $payload, int $daysEvaluated, bool $fatalNoLookahead): array
    {
        $emptyRecommendationDays = 0;
        foreach ($payload['diagnostics'] as $diagnostic) {
            if (($diagnostic['reason_code'] ?? null) === 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID') {
                $emptyRecommendationDays++;
            }
        }

        $reasonCodes = [];
        if (count($payload['evaluations']) > 0) {
            $reasonCodes[] = 'WS_BT_EVAL_METRICS_MISSING';
        }
        if ($fatalNoLookahead) {
            $reasonCodes[] = 'WS_BT_OOS_PROOF_MISSING';
        }
        $reasonCodes[] = 'WS_BT_ARTIFACT_MISSING';

        return [
            'days_requested' => count($payload['replay_window']['trade_dates']),
            'days_evaluated' => $daysEvaluated,
            'items_count' => count($payload['items']),
            'picks_count' => count($payload['trades']),
            'evaluations_count' => count($payload['evaluations']),
            'empty_recommendation_days' => $emptyRecommendationDays,
            'avg_ret_net_top' => null,
            'win_rate_top' => null,
            'median_ret_net_top' => null,
            'p25_ret_net_top' => null,
            'p75_ret_net_top' => null,
            'min_ret_net_top' => null,
            'max_ret_net_top' => null,
            'month_win_rate_min' => null,
            'month_avg_ret_net_min' => null,
            'metrics_ready' => false,
            'artifact_runtime_persistence' => false,
            'production_ready' => false,
            'reason_codes' => $this->uniqueReasonCodes($reasonCodes),
        ];
    }

    private function resolveReasonCode(array $payload, bool $fatalNoLookahead, int $daysEvaluated): string
    {
        if ($fatalNoLookahead) {
            return 'WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION';
        }

        if ($daysEvaluated === 0) {
            return 'WATCHLIST_BACKTEST_SOURCE_NOT_READY';
        }

        if (count($payload['trades']) === 0) {
            return 'WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION';
        }

        return 'WATCHLIST_BACKTEST_FOUNDATION_READY';
    }

    private function artifactManifest(): array
    {
        return [
            'official_backtest_tables' => [
                'watchlist_bt_param_grid',
                'watchlist_bt_eval',
                'watchlist_bt_picks_ws',
                'watchlist_bt_universe_ws',
                'watchlist_bt_cutoffs_ws',
                'watchlist_bt_oos_eval_ws',
            ],
            'production_proof_artifacts' => [
                'PLAN_UNIVERSE_SNAPSHOT_SCHEMA',
            ],
            'runtime_artifact_created' => false,
            'runtime_persistence_created' => false,
            'reason_codes' => ['WS_BT_ARTIFACT_MISSING'],
        ];
    }

    private function replayWindow(array $replayDates): array
    {
        return [
            'from_trade_date' => $replayDates[0] ?? null,
            'to_trade_date' => $replayDates === [] ? null : $replayDates[count($replayDates) - 1],
            'trade_dates' => $replayDates,
            'days_requested' => count($replayDates),
            'explicit_window' => true,
        ];
    }

    private function normalizeTradeDates(array $tradeDates): array
    {
        $normalized = [];
        foreach ($tradeDates as $tradeDate) {
            if (! is_scalar($tradeDate)) {
                continue;
            }

            $value = trim((string) $tradeDate);
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                continue;
            }

            $normalized[$value] = $value;
        }

        sort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function resolveParamset(array $paramset): array
    {
        $defaults = self::DEFAULT_PARAMSET;
        $recommendationDefaults = WatchlistRecommendationService::defaultParamset();
        $backtestInput = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $recommendationInput = is_array($paramset['recommendation'] ?? null) ? $paramset['recommendation'] : [];
        $evalInput = is_array($paramset['eval'] ?? null) ? $paramset['eval'] : [];

        return [
            'policy_code' => (string) ($paramset['policy_code'] ?? $defaults['policy_code']),
            'policy_version' => (string) ($paramset['policy_version'] ?? $defaults['policy_version']),
            'schema_version' => (string) ($paramset['schema_version'] ?? $defaults['schema_version']),
            'paramset_code' => (string) ($paramset['paramset_code'] ?? $defaults['paramset_code']),
            'backtest' => array_merge($defaults['backtest'], $backtestInput),
            'recommendation' => array_replace_recursive(
                $recommendationDefaults['recommendation'],
                $recommendationInput
            ),
            'eval' => array_replace_recursive($defaults['eval'], $evalInput),
        ];
    }

    private function indexByTickerIdentity(array $items): array
    {
        $index = [];
        foreach ($items as $item) {
            foreach ($this->identityKeys($item) as $key) {
                $index[$key] = $item;
            }
        }

        return $index;
    }

    private function firstItemForKeys(array $index, array $keys): ?array
    {
        foreach ($keys as $key) {
            if (isset($index[$key])) {
                return $index[$key];
            }
        }

        return null;
    }

    private function identityKeys(array $item): array
    {
        $keys = [];
        $tickerId = $this->intOrNull($item['ticker_id'] ?? null);
        if ($tickerId !== null) {
            $keys[] = 'id:'.$tickerId;
        }

        $tickerCode = $this->tickerCode($item);
        if ($tickerCode !== '') {
            $keys[] = 'code:'.$tickerCode;
        }

        return $keys;
    }

    private function tickerCode(array $item): string
    {
        return strtoupper(trim((string) ($item['ticker'] ?? $item['ticker_code'] ?? '')));
    }

    private function diagnosticItem(?string $tradeDate, string $reasonCode, array $extra = []): array
    {
        return array_merge([
            'trade_date' => $tradeDate,
            'reason_code' => $reasonCode,
            'fatal' => false,
        ], $extra);
    }

    private function sortItems(array $items): array
    {
        usort($items, function (array $left, array $right): int {
            return $this->compareReplayRows($left, $right);
        });

        return $items;
    }

    private function sortTrades(array $trades): array
    {
        usort($trades, function (array $left, array $right): int {
            return $this->compareReplayRows($left, $right);
        });

        return $trades;
    }

    private function sortEvaluations(array $evaluations): array
    {
        usort($evaluations, function (array $left, array $right): int {
            return $this->compareReplayRows($left, $right);
        });

        return $evaluations;
    }

    private function compareReplayRows(array $left, array $right): int
    {
        $leftDate = (string) ($left['trade_date'] ?? '');
        $rightDate = (string) ($right['trade_date'] ?? '');
        if ($leftDate !== $rightDate) {
            return strcmp($leftDate, $rightDate);
        }

        foreach ([
            'recommendation_rank',
            'plan_rank',
            'ticker_id',
        ] as $key) {
            $leftValue = $this->intOrNull($left[$key] ?? null);
            $rightValue = $this->intOrNull($right[$key] ?? null);
            if ($leftValue === $rightValue) {
                continue;
            }
            if ($leftValue === null) {
                return 1;
            }
            if ($rightValue === null) {
                return -1;
            }

            return $leftValue < $rightValue ? -1 : 1;
        }

        return strcmp((string) ($left['ticker'] ?? ''), (string) ($right['ticker'] ?? ''));
    }

    private function evalModel(array $paramset): string
    {
        return implode('|', [
            'entry='.$paramset['backtest']['entry_model'],
            'exit='.$paramset['backtest']['exit_model'],
            'fee='.$paramset['backtest']['fee_model'],
            'slip_entry='.$paramset['backtest']['slippage_entry_pct'],
            'slip_exit='.$paramset['backtest']['slippage_exit_pct'],
        ]);
    }

    private function hasMismatch(array $values): bool
    {
        $filtered = [];
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                $filtered[] = (string) $value;
            }
        }

        return count(array_unique($filtered)) > 1;
    }

    private function isDateAfter(string $leftDate, string $rightDate): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $leftDate) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rightDate)) {
            return false;
        }

        return strcmp($leftDate, $rightDate) > 0;
    }

    private function uniqueReasonCodes(array $reasonCodes): array
    {
        $normalized = [];
        foreach ($reasonCodes as $reasonCode) {
            if (! is_scalar($reasonCode)) {
                continue;
            }
            $value = trim((string) $reasonCode);
            if ($value !== '') {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private function intOrNull($value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function floatOrNull($value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
