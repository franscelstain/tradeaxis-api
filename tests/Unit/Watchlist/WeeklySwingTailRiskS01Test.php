<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestMetricsService;
use App\Application\Watchlist\Services\WatchlistBacktestStrategyService;
use App\Application\Watchlist\Services\WatchlistBacktestTailRiskS01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestTailRiskS01RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;
use App\Application\Watchlist\Services\WeeklySwingTailRiskS01DraftCatalogService;
use TestCase;

class WeeklySwingTailRiskS01Test extends TestCase
{
    public function test_catalog_locks_exactly_three_one_idea_candidates(): void
    {
        $rows = WatchlistBacktestTailRiskS01ParamGridCatalog::rows();

        $this->assertCount(3, $rows);
        $this->assertCount(3, array_unique(array_column($rows, 'row_code')));
        $this->assertCount(3, array_unique(array_column($rows, 'row_hash')));
        $this->assertCount(1, array_unique(array_column($rows, 'catalog_hash')));
        $this->assertSame(
            WatchlistBacktestTailRiskS01ParamGridCatalog::hash(),
            $rows[0]['catalog_hash']
        );
        foreach ($rows as $row) {
            $selection = WatchlistBacktestTailRiskS01ParamGridCatalog::researchSelectionForRow(
                $row['row_code']
            );
            $execution = WatchlistBacktestTailRiskS01ParamGridCatalog::researchExecutionForRow(
                $row['row_code']
            );
            $this->assertTrue($selection['signal_date_only']);
            $this->assertFalse($selection['oos_used']);
            $this->assertTrue($execution['fixed_before_entry']);
            $this->assertFalse($execution['future_derived_route_used']);
            $this->assertFalse($execution['oos_used']);
        }
    }

    public function test_draft_builder_produces_valid_distinct_candidate_identities(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'storage/app/watchlist/backtest/ws-new-strategy-r02-draft-catalog/'
            .'r02_h2_roc20_persistence_10_to_15.json'
        )), true);
        $this->assertIsArray($source);
        $source['research_execution'] =
            \App\Application\Watchlist\Services\WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();

        $service = new WeeklySwingTailRiskS01DraftCatalogService();
        $hashes = [];
        foreach (WatchlistBacktestTailRiskS01ParamGridCatalog::rows() as $row) {
            $payload = $service->buildCandidatePayload($source, $row);
            $validation = (new WeeklySwingParamsetValidator())->validate($payload);
            $this->assertTrue($validation['valid'], json_encode($validation['errors']));
            $hashes[] = $validation['canonical_hash'];
        }
        $this->assertCount(3, array_unique($hashes));
    }

    public function test_loss_containment_execution_is_chronological_next_open(): void
    {
        $execution = WatchlistBacktestTailRiskS01ParamGridCatalog::lossContainmentExecution();
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
                    'exit_model' => 'WS_S01_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEXT_OPEN_TIME',
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
                '2026-05-20' => $bar(1000, 1004, 950, 965),
                '2026-05-21' => $bar(960, 970, 940, 950),
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
        $trade = $metrics['evaluated_trades'][0];
        $this->assertTrue($metrics['ready']);
        $this->assertSame('2026-05-21', $trade['exit_trade_date']);
        $this->assertSame('WATCHLIST_BACKTEST_EXIT_S01_LOSS_NEXT_OPEN', $trade['exit_reason_code']);
        $this->assertSame(960.0, $trade['exit_price']);
        $this->assertSame(1, $trade['loss_signal_day_offset']);
        $this->assertSame(2, $trade['loss_signal_exit_day_offset']);
        $this->assertTrue($trade['lookahead_safe']);
        $this->assertFalse($trade['future_derived_route_used']);
    }

    public function test_runtime_identity_distinguishes_loss_containment(): void
    {
        $payload = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
        $payload['research_selection'] =
            WatchlistBacktestTailRiskS01ParamGridCatalog::researchSelectionForRow(
                WatchlistBacktestTailRiskS01ParamGridCatalog::H3_ROW_CODE
            );
        $payload['research_execution'] =
            WatchlistBacktestTailRiskS01ParamGridCatalog::lossContainmentExecution();

        $validation = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue($validation['valid'], json_encode($validation['errors']));
        $roundTripped = json_decode(
            (string) json_encode($validation['canonical_payload']),
            true
        );
        $roundTripValidation = (new WeeklySwingParamsetValidator())->validate($roundTripped);
        $this->assertTrue(
            $roundTripValidation['valid'],
            json_encode($roundTripValidation['errors'])
        );
        $this->assertSame(
            $validation['canonical_hash'],
            $roundTripValidation['canonical_hash']
        );
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt(
            $validation['canonical_payload']
        );
        $this->assertSame(
            'WS_S01_SEQUENTIAL_TARGET_0P5_PROFIT_OR_LOSS_NEXT_OPEN_TIME',
            $runtime['backtest']['exit_model']
        );
        $this->assertSame(
            'ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_OR_PCLNO_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            WatchlistBacktestStrategyService::canonicalEvalModel($runtime)
        );
    }

    public function test_single_remediation_is_exactly_one_h1_plus_loss_floor_contract(): void
    {
        $this->assertCount(
            1,
            WatchlistBacktestTailRiskS01RemediationParamGridCatalog::rows()
        );
        $selection =
            WatchlistBacktestTailRiskS01RemediationParamGridCatalog::researchSelection();
        $execution =
            WatchlistBacktestTailRiskS01RemediationParamGridCatalog::researchExecution();

        $this->assertSame(
            WatchlistBacktestTailRiskS01ParamGridCatalog::H1_ROW_CODE,
            $selection['hypothesis_code']
        );
        $this->assertSame(-0.01, $execution['loss_close_threshold_pct']);
        $this->assertTrue($execution['fixed_before_entry']);
        $this->assertFalse($execution['future_derived_route_used']);
        $this->assertFalse($execution['oos_used']);

        $payload = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/PARAMSET_WS_ACTIVE_EXAMPLE.json'
        )), true);
        $payload['research_selection'] = $selection;
        $payload['research_execution'] = $execution;
        $validation = (new WeeklySwingParamsetValidator())->validate($payload);
        $this->assertTrue($validation['valid'], json_encode($validation['errors']));
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt(
            $validation['canonical_payload']
        );
        $this->assertSame(
            'ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            WatchlistBacktestStrategyService::canonicalEvalModel($runtime)
        );
    }
}
