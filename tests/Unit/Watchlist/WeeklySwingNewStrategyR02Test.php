<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestNewStrategyR02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestNewStrategyR02RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestMetricsService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistScoringService;
use App\Application\Watchlist\Services\WatchlistBacktestStrategyService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;
use App\Infrastructure\Persistence\MarketData\MarketBenchmarkReadRepository;
use TestCase;

class WeeklySwingNewStrategyR02Test extends TestCase
{
    public function test_catalog_locks_three_one_idea_candidates_from_r01(): void
    {
        $rows = WatchlistBacktestNewStrategyR02ParamGridCatalog::rows();

        $this->assertCount(3, $rows);
        $this->assertSame(3, WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(3, array_unique(array_column($rows, 'row_code')));
        $this->assertCount(3, array_unique(array_column($rows, 'row_hash')));
        $this->assertCount(1, array_unique(array_column($rows, 'catalog_hash')));
        $this->assertSame(
            WatchlistBacktestNewStrategyR02ParamGridCatalog::hash(),
            $rows[0]['catalog_hash']
        );
        $hypotheses = [];
        foreach ($rows as $row) {
            $selection = WatchlistBacktestNewStrategyR02ParamGridCatalog::researchSelectionForRow(
                $row['row_code']
            );
            $this->assertTrue($selection['signal_date_only']);
            $this->assertFalse($selection['oos_used']);
            $hypotheses[] = $selection['hypothesis_code'];
        }
        $this->assertSame([
            'H1_BREAKOUT_QUALITY_CONFIRMATION',
            'H2_MOMENTUM_PERSISTENCE',
            'H3_MARKET_REGIME_COMPATIBILITY',
        ], $hypotheses);
        $this->assertFalse(
            WatchlistBacktestNewStrategyR02ParamGridCatalog::provenance()['canonical_gate_lowered']
        );
    }

    public function test_validator_accepts_only_locked_optional_research_selection_contract(): void
    {
        $payload = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
        $this->assertIsArray($payload);
        $payload['research_selection'] =
            WatchlistBacktestNewStrategyR02ParamGridCatalog::researchSelectionForRow(
                'R02_H2_ROC20_PERSISTENCE_10_TO_15'
            );

        $valid = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue($valid['valid'], json_encode($valid['errors']));

        $payload['research_selection']['thresholds']['min_roc20'] = 0.09;
        $invalid = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertFalse($invalid['valid']);
        $this->assertContains(
            'WS_PARAMSET_RESEARCH_SELECTION_INVALID',
            array_column($invalid['errors'], 'reason_code')
        );
    }

    public function test_h1_and_h2_guards_filter_only_their_locked_signal_features(): void
    {
        $service = new WatchlistPlanGroupingService();
        $items = [
            $this->item(1, 0.90, 0.01, 0.12),
            $this->item(2, 0.89, -0.01, 0.07),
            $this->item(3, 0.88, 0.03, 0.18),
        ];

        $h1 = $this->group($service, $items, 'R02_H1_BREAKOUT_QUALITY_0_TO_2');
        $this->assertTrue($h1['is_ready']);
        $this->assertSame([1], array_column($h1['groups']['TOP_PICKS'], 'ticker_id'));
        $this->assertContains(
            'WATCHLIST_R02_H1_BREAKOUT_QUALITY_FAIL',
            $h1['excluded'][0]['reason_codes']
        );

        $h2 = $this->group($service, $items, 'R02_H2_ROC20_PERSISTENCE_10_TO_15');
        $this->assertTrue($h2['is_ready']);
        $this->assertSame([1], array_column($h2['groups']['TOP_PICKS'], 'ticker_id'));
        $this->assertContains(
            'WATCHLIST_R02_H2_MOMENTUM_PERSISTENCE_FAIL',
            $h2['excluded'][0]['reason_codes']
        );
    }

    public function test_h3_uses_exact_signal_date_ihsg_context_and_fails_closed(): void
    {
        $mixedRepository = new class extends MarketBenchmarkReadRepository {
            public function getBenchmarkContext(string $benchmarkCode, string $tradeDate): ?array
            {
                return [
                    'benchmark_code' => $benchmarkCode,
                    'trade_date' => $tradeDate,
                    'roc_20' => 0.05,
                    'ma20_slope_pct' => -0.01,
                    'indicator_set_version' => 'v1',
                    'is_valid' => true,
                ];
            }
        };
        $service = new WatchlistPlanGroupingService(new WatchlistScoringService(), $mixedRepository);
        $result = $this->group(
            $service,
            [$this->item(1, 0.90, 0.01, 0.12)],
            'R02_H3_IHSG_MIXED_REGIME_ONLY'
        );

        $this->assertTrue($result['is_ready']);
        $this->assertSame('MIXED', $result['research_selection_context']['market_regime']);
        $this->assertSame([1], array_column($result['groups']['TOP_PICKS'], 'ticker_id'));

        $missingRepository = new class extends MarketBenchmarkReadRepository {
            public function getBenchmarkContext(string $benchmarkCode, string $tradeDate): ?array
            {
                return null;
            }
        };
        $blocked = $this->group(
            new WatchlistPlanGroupingService(new WatchlistScoringService(), $missingRepository),
            [$this->item(1, 0.90, 0.01, 0.12)],
            'R02_H3_IHSG_MIXED_REGIME_ONLY'
        );
        $this->assertFalse($blocked['is_ready']);
        $this->assertSame(
            'WATCHLIST_R02_SIGNAL_DATE_BENCHMARK_CONTEXT_NOT_READY',
            $blocked['reason_code']
        );
    }

    public function test_commands_are_registered_and_oos_is_not_routed(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $draftCommand = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/PersistWeeklySwingNewStrategyR02DraftCatalogCommand.php'
        ));
        $isCommand = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/RunWeeklySwingNewStrategyR02OfficialIsCommand.php'
        ));
        $remediationCommand = (string) file_get_contents(base_path(
            'app/Console/Commands/Watchlist/PersistWeeklySwingNewStrategyR02RemediationDraftCommand.php'
        ));
        $officialService = (string) file_get_contents(base_path(
            'app/Application/Watchlist/Services/WeeklySwingNewStrategyR02OfficialIsEvidenceService.php'
        ));

        $this->assertStringContainsString(
            'PersistWeeklySwingNewStrategyR02DraftCatalogCommand::class',
            $kernel
        );
        $this->assertStringContainsString(
            'RunWeeklySwingNewStrategyR02OfficialIsCommand::class',
            $kernel
        );
        $this->assertStringContainsString(
            'PersistWeeklySwingNewStrategyR02RemediationDraftCommand::class',
            $kernel
        );
        $this->assertStringContainsString(
            'weekly-swing-new-strategy-r02-persist-draft-catalog',
            $draftCommand
        );
        $this->assertStringContainsString(
            'weekly-swing-new-strategy-r02-official-is',
            $isCommand
        );
        $this->assertStringContainsString(
            'weekly-swing-new-strategy-r02-persist-remediation-draft',
            $remediationCommand
        );
        $this->assertStringNotContainsString('WatchlistBacktestOos', $officialService);
        $this->assertStringContainsString("'oos_runtime_invoked' => false", $officialService);
        $this->assertStringContainsString("'paramset_promoted' => false", $officialService);
        $this->assertStringContainsString("'plan_run_created' => false", $officialService);
    }

    public function test_single_remediation_contract_is_exact_and_has_distinct_execution_identity(): void
    {
        $this->assertCount(1, WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::rows());
        $payload = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
        $payload['paramset_code'] =
            WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::ROW_CODE;
        $payload['research_selection'] =
            WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchSelection();
        $payload['research_execution'] =
            WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();

        $validation = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue($validation['valid'], json_encode($validation['errors']));
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt(
            $validation['canonical_payload']
        );
        $this->assertSame(
            'WS_R02_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN_TIME',
            $runtime['backtest']['exit_model']
        );
        $this->assertSame(
            'ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_OR_PCNO_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            WatchlistBacktestStrategyService::canonicalEvalModel($runtime)
        );
        $this->assertTrue($runtime['research_execution']['fixed_before_entry']);
        $this->assertFalse($runtime['research_execution']['future_derived_route_used']);

        $payload['research_execution']['preplanned_target_pct'] = 0.006;
        $invalid = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertFalse($invalid['valid']);
        $this->assertContains(
            'WS_PARAMSET_RESEARCH_EXECUTION_INVALID',
            array_column($invalid['errors'], 'reason_code')
        );
    }

    public function test_single_remediation_exit_runs_sequentially_without_future_route(): void
    {
        $execution =
            WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();
        $payload = [
            'paramset_snapshot' => [
                'eval' => [
                    'min_trades' => 1,
                    'min_days_covered' => 1,
                    'min_p25_ret_net_top' => -0.03,
                    'min_month_win_rate_min' => 0.45,
                    'min_month_avg_ret_net_min' => -0.01,
                ],
                'backtest' => [
                    'exit_model' => 'WS_R02_SEQUENTIAL_TARGET_0P5_PROFIT_NEXT_OPEN_TIME',
                    'research_execution' => $execution,
                    'holding_days' => 5,
                    'notional_idr' => 10000000,
                    'lot_size' => 100,
                    'fee_buy_idr' => 0,
                    'fee_sell_idr' => 0,
                    'slippage_entry_pct' => 0,
                    'slippage_exit_pct' => 0,
                    'tradable_bar_rule' => 'POSITIVE_VOLUME_REQUIRED',
                    'min_tradable_volume' => 1,
                ],
            ],
            'replay_window' => ['trade_dates' => ['2026-05-19']],
            'trades' => [
                ['trade_date' => '2026-05-19', 'ticker_id' => 1, 'ticker' => 'AAA'],
                ['trade_date' => '2026-05-19', 'ticker_id' => 2, 'ticker' => 'BBB'],
            ],
            'diagnostics' => [],
            'summary' => ['empty_recommendation_days' => 0],
        ];
        $bar = function (float $open, float $high, float $low, float $close): array {
            return [
                'published' => true,
                'volume' => 100000,
                'open' => $open,
                'high' => $high,
                'low' => $low,
                'close' => $close,
            ];
        };
        $prices = [
            'AAA' => [
                '2026-05-20' => $bar(1000, 1005, 990, 1001),
            ],
            'BBB' => [
                '2026-05-20' => $bar(1000, 1000, 980, 1002),
                '2026-05-21' => $bar(1003, 1004, 990, 1000),
            ],
        ];
        $calendar = [
            '2026-05-19', '2026-05-20', '2026-05-21',
            '2026-05-22', '2026-05-25', '2026-05-26',
        ];

        $metrics = (new WatchlistBacktestMetricsService())->buildMetrics(
            $payload,
            $prices,
            $calendar
        );
        $this->assertTrue($metrics['ready']);
        $this->assertSame(
            'WATCHLIST_BACKTEST_EXIT_TARGET',
            $metrics['evaluated_trades'][0]['exit_reason_code']
        );
        $this->assertSame(1005.0, $metrics['evaluated_trades'][0]['exit_price']);
        $this->assertSame(
            'WATCHLIST_BACKTEST_EXIT_R02_PROFIT_NEXT_OPEN',
            $metrics['evaluated_trades'][1]['exit_reason_code']
        );
        $this->assertSame('2026-05-21', $metrics['evaluated_trades'][1]['exit_trade_date']);
        $this->assertSame(1, $metrics['evaluated_trades'][1]['profit_signal_day_offset']);
        $this->assertSame(2, $metrics['evaluated_trades'][1]['profit_signal_exit_day_offset']);
        $this->assertTrue($metrics['evaluated_trades'][1]['lookahead_safe']);
        $this->assertFalse($metrics['evaluated_trades'][1]['future_derived_route_used']);
    }

    private function group(
        WatchlistPlanGroupingService $service,
        array $items,
        string $rowCode
    ): array {
        return $service->groupScoredOutput([
            'ready' => true,
            'is_ready' => true,
            'trade_date' => '2024-01-10',
            'publication_id' => 10,
            'publication_version' => 1,
            'run_id' => 'run-10',
            'items' => $items,
            'excluded' => [],
        ], [
            'research_selection' =>
                WatchlistBacktestNewStrategyR02ParamGridCatalog::researchSelectionForRow($rowCode),
        ], '2024-01-10');
    }

    private function item(
        int $tickerId,
        float $score,
        float $closeToHh20,
        float $roc20
    ): array {
        return [
            'ticker_id' => $tickerId,
            'ticker_code' => 'T'.$tickerId,
            'eligible_score' => true,
            'score_total' => $score,
            'score_components' => [
                'score_momentum' => 0.8,
                'score_breakout' => 0.8,
                'score_volume' => 0.8,
                'score_risk' => 0.8,
            ],
            'score_metrics' => [
                'close_to_hh20_pct' => $closeToHh20,
                'roc20' => $roc20,
                'dv20_idr' => 5000000000,
                'atr14_pct' => 0.04,
                'vol_ratio' => 2.0,
            ],
            'factor_breakdown' => [
                'breakout' => ['close_to_hh20_pct' => $closeToHh20],
                'momentum' => ['roc20' => $roc20],
            ],
            'reason_codes' => [],
        ];
    }
}
