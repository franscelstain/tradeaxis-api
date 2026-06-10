<?php

use App\Application\Watchlist\Services\WatchlistBacktestStrategyService;
use App\Application\Watchlist\Services\WatchlistConfirmOverlayService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistRecommendationService;

class WatchlistBacktestStrategyServiceTest extends TestCase
{
    public function test_backtest_replays_plan_recommendation_and_confirm_without_mutating_sources(): void
    {
        $planMap = [
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(1, 'TOPA', 'TOP_PICKS', 0.90, 1),
                ],
                'WATCH_ONLY' => [
                    $this->planItem(2, 'WCHA', 'WATCH_ONLY', 0.62, 3),
                ],
            ]),
        ];
        $service = $this->serviceForPlanMap($planMap);

        $result = $service->backtestForReplayWindow(['2026-05-19'], [
            '2026-05-19' => [
                ['ticker_code' => 'TOPA', 'confirm_state' => 'CONFIRMED'],
                ['ticker_code' => 'WCHA', 'confirm_state' => 'CONFIRMED'],
                ['ticker_code' => 'MISS', 'confirm_state' => 'CONFIRMED'],
            ],
        ]);

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_FOUNDATION_READY', $result['reason_code']);
        $this->assertSame('WatchlistBacktestStrategyService', $result['source_contract']['consumer']);
        $this->assertTrue($result['source_contract']['recommendation_layer_only']);
        $this->assertTrue($result['source_contract']['confirm_overlay_diagnostic_only']);
        $this->assertTrue($result['backtest_contract']['no_lookahead']);
        $this->assertTrue($result['backtest_contract']['deterministic_replay']);
        $this->assertTrue($result['backtest_contract']['publication_aware_replay']);

        $this->assertSame(['TOPA'], array_column($result['trades'], 'ticker'));
        $this->assertSame(['TOPA'], array_column($result['evaluations'], 'ticker'));
        $this->assertSame(2, $result['summary']['items_count']);
        $this->assertSame(1, $result['summary']['picks_count']);
        $this->assertSame(1, $result['summary']['evaluations_count']);
        $this->assertFalse($result['summary']['metrics_ready']);
        $this->assertContains('WS_BT_EVAL_METRICS_MISSING', $result['summary']['reason_codes']);
        $this->assertContains('WS_BT_ARTIFACT_MISSING', $result['artifact_manifest']['reason_codes']);

        $itemsByTicker = [];
        foreach ($result['items'] as $item) {
            $itemsByTicker[$item['ticker']] = $item;
        }

        $this->assertTrue($itemsByTicker['TOPA']['active_trade_evaluation']);
        $this->assertTrue($itemsByTicker['TOPA']['recommended_flag']);
        $this->assertSame('CONFIRMED', $itemsByTicker['TOPA']['confirm_state']);
        $this->assertFalse($itemsByTicker['WCHA']['active_trade_evaluation']);
        $this->assertFalse($itemsByTicker['WCHA']['recommended_flag']);
        $this->assertSame('CONFIRMED', $itemsByTicker['WCHA']['confirm_state']);

        $this->assertSame('EVALUATION_CANDIDATE', $result['trades'][0]['trade_state']);
        $this->assertSame(0.05, $result['trades'][0]['atr14_pct']);
        $this->assertSame(1.5, $result['trades'][0]['stop_atr_mult']);
        $this->assertSame(1.5, $result['trades'][0]['min_rr']);
        $this->assertSame('PUBLISHED_EOD_OHLCV_REQUIRED_AT_RUNTIME', $result['trades'][0]['pricing_model']);
        $this->assertTrue($result['trades'][0]['contract_flags']['from_recommendation_layer']);
        $this->assertTrue($result['trades'][0]['contract_flags']['confirm_does_not_create_recommendation']);
        $this->assertFalse($result['evaluations'][0]['metrics_ready']);
        $this->assertSame('FOUNDATION_REPLAY_ONLY', $result['evaluations'][0]['evaluation_state']);

        $unknownDiagnostics = array_values(array_filter($result['diagnostics'], function (array $diagnostic): bool {
            return ($diagnostic['ticker'] ?? null) === 'MISS';
        }));
        $this->assertCount(1, $unknownDiagnostics);
        $this->assertFalse($unknownDiagnostics[0]['active_trade_evaluation']);
    }

    public function test_backtest_fails_closed_on_future_effective_date_to_preserve_no_lookahead(): void
    {
        $planMap = [
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(1, 'FUTR', 'TOP_PICKS', 0.90, 1),
                ],
            ], [
                'trade_date_effective' => '2026-05-20',
            ]),
        ];

        $result = $this->serviceForPlanMap($planMap)->backtestForReplayWindow(['2026-05-19']);

        $this->assertFalse($result['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_NO_LOOKAHEAD_VIOLATION', $result['reason_code']);
        $this->assertSame([], $result['trades']);
        $this->assertSame([], $result['evaluations']);
        $this->assertContains('WS_BT_OOS_PROOF_MISSING', $result['summary']['reason_codes']);
    }

    public function test_backtest_output_is_deterministic_for_same_replay_window_and_source_outputs(): void
    {
        $planMap = [
            '2026-05-20' => $this->planOutput('2026-05-20', [
                'TOP_PICKS' => [
                    $this->planItem(20, 'T20', 'TOP_PICKS', 0.88, 2),
                    $this->planItem(10, 'T10', 'TOP_PICKS', 0.88, 1),
                ],
            ]),
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(30, 'T30', 'TOP_PICKS', 0.90, 1),
                ],
            ]),
        ];
        $service = $this->serviceForPlanMap($planMap);

        $first = $service->backtestForReplayWindow(['2026-05-20', '2026-05-19']);
        $second = $service->backtestForReplayWindow(['2026-05-19', '2026-05-20']);

        $this->assertSame($first, $second);
        $this->assertSame(['2026-05-19', '2026-05-20'], $first['replay_window']['trade_dates']);
        $this->assertSame(['T30', 'T10', 'T20'], array_column($first['trades'], 'ticker'));
    }

    public function test_empty_recommendation_is_valid_and_does_not_create_active_evaluations(): void
    {
        $planMap = [
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(1, 'LOWA', 'TOP_PICKS', 0.72, 1),
                ],
            ]),
        ];

        $result = $this->serviceForPlanMap($planMap)->backtestForReplayWindow(
            ['2026-05-19'],
            [],
            [
                'recommendation' => [
                    'min_recommendation_score' => 0.95,
                    'borderline_min_score' => 0.70,
                ],
            ]
        );

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION', $result['reason_code']);
        $this->assertSame([], $result['trades']);
        $this->assertSame([], $result['evaluations']);
        $this->assertSame(1, $result['summary']['empty_recommendation_days']);
        $this->assertContains('WS_REC_EMPTY_SET', $result['diagnostics'][0]['reason_codes']);
    }

    public function test_backtest_output_contains_required_explainable_top_level_shape(): void
    {
        $planMap = [
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(1, 'SHPE', 'TOP_PICKS', 0.90, 1),
                ],
            ]),
        ];

        $result = $this->serviceForPlanMap($planMap)->backtestForReplayWindow(['2026-05-19']);

        foreach ([
            'meta',
            'source_contract',
            'backtest_contract',
            'paramset_snapshot',
            'replay_window',
            'items',
            'trades',
            'evaluations',
            'summary',
            'diagnostics',
            'artifact_manifest',
        ] as $requiredKey) {
            $this->assertArrayHasKey($requiredKey, $result);
        }

        $this->assertSame('watchlist_bt_eval', $result['artifact_manifest']['official_backtest_tables'][1]);
        $this->assertFalse($result['artifact_manifest']['runtime_artifact_created']);
        $this->assertFalse($result['summary']['production_ready']);
    }

    public function test_backtest_preserves_eval_thresholds_in_paramset_snapshot(): void
    {
        $planMap = [
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(1, 'EVAL', 'TOP_PICKS', 0.90, 1),
                ],
            ]),
        ];
        $eval = [
            'min_trades' => 120,
            'min_days_covered' => 40,
            'min_p25_ret_net_top' => -0.03,
            'min_month_win_rate_min' => 0.45,
            'min_month_avg_ret_net_min' => -0.01,
        ];

        $result = $this->serviceForPlanMap($planMap)->backtestForReplayWindow(
            ['2026-05-19'],
            [],
            ['eval' => $eval]
        );

        foreach ($eval as $key => $value) {
            $this->assertSame($value, $result['paramset_snapshot']['eval'][$key]);
            $this->assertSame($value, $result['meta']['paramset_snapshot']['eval'][$key]);
        }
        $this->assertArrayHasKey('min_trades_oos', $result['paramset_snapshot']['eval']);
    }

    public function test_backtest_default_paramset_binds_canonical_eval_thresholds_and_tradable_bar_rule(): void
    {
        $defaults = WatchlistBacktestStrategyService::defaultParamset();

        $this->assertSame('PARAMSET_JSON', $defaults['schema_version']);
        foreach ([
            'min_trades_oos',
            'min_trades',
            'min_days_covered',
            'min_p25_ret_net_top',
            'min_month_win_rate_min',
            'min_month_avg_ret_net_min',
        ] as $key) {
            $this->assertArrayHasKey($key, $defaults['eval']);
            $this->assertArrayHasKey('value', $defaults['eval'][$key]);
            $this->assertSame('ACTIVE', $defaults['eval'][$key]['status']);
        }
        $this->assertSame(40, $defaults['eval']['min_trades_oos']['value']);
        $this->assertSame(120, $defaults['eval']['min_trades']['value']);
        $this->assertSame(0, $defaults['eval']['min_days_covered']['value']);
        $this->assertSame('POSITIVE_VOLUME_REQUIRED', $defaults['backtest']['tradable_bar_rule']);
        $this->assertSame(1, $defaults['backtest']['min_tradable_volume']);
    }

    public function test_backtest_foundation_does_not_emit_portfolio_broker_order_or_runtime_surface_fields(): void
    {
        $planMap = [
            '2026-05-19' => $this->planOutput('2026-05-19', [
                'TOP_PICKS' => [
                    $this->planItem(1, 'SAFE', 'TOP_PICKS', 0.90, 1),
                ],
            ]),
        ];

        $result = $this->serviceForPlanMap($planMap)->backtestForReplayWindow(['2026-05-19']);

        foreach ([
            'portfolio_allocation',
            'capital_allocation',
            'suggested_lots',
            'order_instruction',
            'execution_action',
            'broker_instruction',
            'entry_price_instruction',
            'take_profit_instruction',
            'stop_loss_instruction',
            'buy_signal',
            'sell_signal',
            'api_endpoint',
            'artisan_command',
        ] as $forbiddenKey) {
            $this->assertFalse($this->payloadHasExactKey($result, $forbiddenKey), $forbiddenKey);
        }
    }

    private function serviceForPlanMap(array $planMap): WatchlistBacktestStrategyService
    {
        $planGrouping = $this->fakePlanGrouping($planMap);
        $recommendation = new WatchlistRecommendationService($planGrouping);
        $confirm = new WatchlistConfirmOverlayService($planGrouping, $recommendation);

        return new WatchlistBacktestStrategyService($planGrouping, $recommendation, $confirm);
    }

    private function fakePlanGrouping(array $payloadByDate): WatchlistPlanGroupingService
    {
        return new class($payloadByDate) extends WatchlistPlanGroupingService {
            private array $payloadByDate;

            public function __construct(array $payloadByDate)
            {
                $this->payloadByDate = $payloadByDate;
            }

            public function groupForTradeDate(string $tradeDate, array $paramset = []): array
            {
                return $this->payloadByDate[$tradeDate] ?? [
                    'ready' => false,
                    'is_ready' => false,
                    'reason_code' => 'WATCHLIST_PLAN_GROUPING_SOURCE_NOT_READY',
                    'trade_date' => $tradeDate,
                    'groups' => [
                        'TOP_PICKS' => [],
                        'SECONDARY' => [],
                        'WATCH_ONLY' => [],
                        'AVOID' => [],
                    ],
                ];
            }
        };
    }

    private function planOutput(string $tradeDate, array $groups, array $overrides = []): array
    {
        return array_merge([
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_PLAN_GROUPING_READY',
            'trade_date' => $tradeDate,
            'trade_date_effective' => $tradeDate,
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            'plan_hash' => 'plan-'.$tradeDate,
            'source_contract' => [
                'consumer' => 'WatchlistPlanGroupingService',
                'upstream' => 'WatchlistScoringService',
            ],
            'group_contract' => [
                'not_final_recommendation' => true,
                'groups' => ['TOP_PICKS', 'SECONDARY', 'WATCH_ONLY', 'AVOID'],
            ],
            'paramset_snapshot' => [
                'policy_code' => 'WS',
                'policy_version' => 'WS_EOD_RUNTIME',
                'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
                'grouping' => [],
            ],
            'groups' => array_merge([
                'TOP_PICKS' => [],
                'SECONDARY' => [],
                'WATCH_ONLY' => [],
                'AVOID' => [],
            ], $groups),
            'excluded' => [],
            'summary' => [
                'top_picks_count' => count($groups['TOP_PICKS'] ?? []),
                'secondary_count' => count($groups['SECONDARY'] ?? []),
                'watch_only_count' => count($groups['WATCH_ONLY'] ?? []),
                'avoid_count' => count($groups['AVOID'] ?? []),
            ],
        ], $overrides);
    }

    private function planItem(int $tickerId, string $tickerCode, string $group, float $scoreTotal, ?int $planRank): array
    {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'eligible_score' => true,
            'score_total' => $scoreTotal,
            'score_components' => [
                'score_momentum' => 0.80,
                'score_breakout' => 0.80,
                'score_volume' => 0.80,
                'score_risk' => 0.80,
            ],
            'reason_codes' => ['WS_PLAN_TOP_PICK'],
            'ranking_keys' => [
                'score_total_desc' => $scoreTotal,
                'ticker_id_asc' => $tickerId,
            ],
            'score_metrics' => [
                'dv20_idr' => 7000000000.0,
                'atr14_pct' => 0.0500,
                'vol_ratio' => 1.50,
            ],
            'plan_group' => $group,
            'group_semantic' => $group,
            'group_reason_code' => $group === 'SECONDARY' ? 'WS_PLAN_SECONDARY' : 'WS_PLAN_TOP_PICK',
            'group_rank' => $planRank,
            'plan_rank' => $planRank,
        ];
    }

    private function payloadHasExactKey(array $payload, string $key): bool
    {
        foreach ($payload as $payloadKey => $value) {
            if ($payloadKey === $key) {
                return true;
            }

            if (is_array($value) && $this->payloadHasExactKey($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
