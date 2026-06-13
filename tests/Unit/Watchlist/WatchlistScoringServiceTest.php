<?php

use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistScoringService;

class WatchlistScoringServiceTest extends TestCase
{
    public function test_scoring_computes_deterministic_weighted_score_from_eligible_candidate_universe(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'BBCA'),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame('WATCHLIST_SCORING_READY', $result['reason_code']);
        $this->assertSame('WS', $result['policy_code']);
        $this->assertSame('WS_EOD_RUNTIME', $result['policy_version']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $result['paramset_code']);
        $this->assertSame('WEIGHTED_MEAN', $result['score_contract']['combine_mode']);
        $this->assertSame(1, $result['summary']['scored_count']);

        $item = $result['items'][0];
        $this->assertSame(1, $item['ticker_id']);
        $this->assertSame('BBCA', $item['ticker_code']);
        $this->assertTrue($item['eligible_score']);
        $this->assertSame([
            'momentum' => 0.30,
            'breakout' => 0.30,
            'volume' => 0.20,
            'risk' => 0.20,
        ], $item['score_weights']);
        $this->assertArrayHasKey('score_momentum', $item['score_components']);
        $this->assertArrayHasKey('score_breakout', $item['score_components']);
        $this->assertArrayHasKey('score_volume', $item['score_components']);
        $this->assertArrayHasKey('score_risk', $item['score_components']);
        $this->assertArrayHasKey('momentum', $item['factor_breakdown']);
        $this->assertArrayHasKey('breakout', $item['factor_breakdown']);
        $this->assertArrayHasKey('volume', $item['factor_breakdown']);
        $this->assertArrayHasKey('risk', $item['factor_breakdown']);
        $this->assertContains('WS_MOM_STRONG', $item['reason_codes']);
        $this->assertContains('WS_BO_NEAR', $item['reason_codes']);
    }

    public function test_scoring_excludes_candidate_where_eligible_plan_or_guard_ok_is_false(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'BBCA'),
        ], [
            array_merge($this->candidate(2, 'BRPT'), [
                'eligible_plan' => false,
                'guard_ok' => false,
                'canonical_fail_reason_code' => 'WS_ATR_HIGH',
                'reason_codes' => ['WS_ATR_HIGH'],
            ]),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame(1, $result['summary']['scored_count']);
        $this->assertSame(1, $result['summary']['excluded_count']);
        $this->assertSame('BBCA', $result['items'][0]['ticker_code']);
        $this->assertSame('BRPT', $result['excluded'][0]['ticker_code']);
        $this->assertFalse($result['excluded'][0]['eligible_score']);
        $this->assertContains('WS_ATR_HIGH', $result['excluded'][0]['reason_codes']);
    }

    public function test_scoring_fails_closed_when_candidate_universe_source_is_not_ready(): void
    {
        $payload = [
            'trade_date' => '2026-05-19',
            'trade_date_effective' => null,
            'publication_id' => null,
            'publication_version' => null,
            'run_id' => null,
            'is_ready' => false,
            'reason_code' => 'MARKET_DATA_NOT_READY',
            'eligible_candidates' => [],
            'rejected_candidates' => [],
        ];

        $service = new WatchlistScoringService($this->fakeCandidateUniverse($payload));
        $result = $service->scoreForTradeDate('2026-05-19');

        $this->assertFalse($result['ready']);
        $this->assertSame('MARKET_DATA_NOT_READY', $result['reason_code']);
        $this->assertSame('WATCHLIST_SCORING_SOURCE_NOT_READY', $result['scoring_reason_code']);
        $this->assertSame([], $result['items']);
        $this->assertSame([], $result['excluded']);
    }

    public function test_scoring_keeps_score_components_and_score_total_in_zero_to_one_range(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'BBCA', [
                'roc20' => 99.0,
                'ma20_slope_pct' => 99.0,
                'rs_20_vs_ihsg' => 99.0,
                'close_vs_ma20_pct' => 99.0,
                'close_vs_ma50_pct' => 99.0,
                'close_to_hh20_pct' => 99.0,
                'vol_ratio' => 99.0,
            ]),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');
        $item = $result['items'][0];

        $this->assertGreaterThanOrEqual(0.0, $item['score_total']);
        $this->assertLessThanOrEqual(1.0, $item['score_total']);

        foreach ($item['score_components'] as $score) {
            $this->assertGreaterThanOrEqual(0.0, $score);
            $this->assertLessThanOrEqual(1.0, $score);
        }
    }

    public function test_scoring_rejects_invalid_atr_unit_drift_where_atr14_pct_is_above_one(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'SMBR', ['atr14_pct' => 2.0]),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');

        $this->assertTrue($result['ready']);
        $this->assertSame([], $result['items']);
        $this->assertSame(1, $result['summary']['excluded_count']);
        $this->assertSame('SMBR', $result['excluded'][0]['ticker_code']);
        $this->assertContains('WATCHLIST_SCORING_ATR_UNIT_DRIFT', $result['excluded'][0]['reason_codes']);
    }

    public function test_scoring_applies_deterministic_tie_break_order(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(20, 'BBBB'),
            $this->candidate(10, 'AAAA'),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');

        $this->assertSame([10, 20], array_column($result['items'], 'ticker_id'));
        $this->assertSame([
            'score_total_desc',
            'score_breakout_desc',
            'score_momentum_desc',
            'dv20_idr_desc',
            'atr14_pct_asc',
            'ticker_id_asc',
        ], $result['score_contract']['sort_keys']);
    }

    public function test_scoring_output_contains_paramset_policy_score_contract_and_source_contract(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'BBCA'),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');

        $this->assertSame('WS', $result['policy_code']);
        $this->assertSame('WS_EOD_RUNTIME', $result['policy_version']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $result['paramset_code']);
        $this->assertSame('WatchlistScoringService', $result['source_contract']['consumer']);
        $this->assertSame('WatchlistCandidateUniverseService', $result['source_contract']['upstream']);
        $this->assertTrue($result['source_contract']['no_raw_market_data']);
        $this->assertTrue($result['source_contract']['no_latest_shortcut']);
        $this->assertTrue($result['source_contract']['no_recommendation']);
        $this->assertTrue($result['source_contract']['no_confirm']);
        $this->assertTrue($result['source_contract']['no_execution']);
        $this->assertSame('WEIGHTED_MEAN', $result['score_contract']['combine_mode']);
        $this->assertSame('0..1', $result['score_contract']['range']);
        $this->assertArrayHasKey('paramset_snapshot', $result);
    }

    public function test_scoring_does_not_produce_recommendation_confirm_or_execution_fields(): void
    {
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'BBCA'),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19');
        $encoded = json_encode($result);

        foreach (['recommendation_label', 'confirm_state', 'portfolio_allocation', 'order_instruction', 'execution_action', 'buy_signal', 'sell_signal', 'backtest_metric'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $encoded);
        }
    }


    public function test_scoring_preserves_c15_extended_runtime_metrics_for_grouping_guards(): void
    {
        $row = WatchlistBacktestC15ParamGridCatalog::rows()[1];
        $row['param_id'] = 15001;
        $paramset = (new WatchlistBacktestParamGridParamsetFactory())->make($row);
        $service = new WatchlistScoringService($this->fakeCandidateUniverse($this->universe([
            $this->candidate(1, 'BBCA', [
                'dv20_idr' => 3500000000.0,
                'atr14_pct' => 0.0250,
                'vol_ratio' => 1.30,
                'roc20' => 0.010,
                'roc5' => -0.010,
                'roc10' => -0.006,
                'close_to_ll20_pct' => 0.10,
                'range_20_pct' => 0.18,
                'range_position_20_pct' => 0.55,
                'sector_roc20' => 0.005,
                'rs_20_vs_sector' => 0.002,
                'sector_rs_20_vs_ihsg' => 0.001,
                'corporate_action_flag' => 0,
                'corporate_action_types' => '',
                'trading_status_code' => 'ACTIVE',
                'is_suspended' => 0,
                'is_uma' => 0,
                'event_risk_flag' => 0,
                'event_risk_reasons' => '',
            ]),
        ])));

        $result = $service->scoreForTradeDate('2026-05-19', $paramset);

        $this->assertTrue($result['ready']);
        $metrics = $result['items'][0]['score_metrics'];
        foreach (['roc5', 'roc10', 'close_to_ll20_pct', 'range_20_pct', 'range_position_20_pct', 'sector_roc20', 'rs_20_vs_sector', 'sector_rs_20_vs_ihsg'] as $field) {
            $this->assertArrayHasKey($field, $metrics);
            $this->assertNotNull($metrics[$field]);
        }
        foreach (['corporate_action_flag', 'trading_status_code', 'is_suspended', 'is_uma', 'event_risk_flag', 'event_risk_reasons'] as $field) {
            $this->assertArrayHasKey($field, $metrics);
        }
        $this->assertSame(-0.010, $metrics['roc5']);
        $this->assertArrayHasKey('score_components', $result['items'][0]);
        $this->assertArrayHasKey('factor_breakdown', $result['items'][0]);
    }

    private function fakeCandidateUniverse(array $payload): WatchlistCandidateUniverseService
    {
        return new class($payload) extends WatchlistCandidateUniverseService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function buildCandidateUniverseForTradeDate(string $tradeDate, array $paramset = []): array
            {
                return $this->payload;
            }
        };
    }

    private function universe(array $eligibleCandidates, array $rejectedCandidates = []): array
    {
        return [
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_CANDIDATE_UNIVERSE_READY',
            'candidate_universe_reason_code' => 'WATCHLIST_CANDIDATE_UNIVERSE_READY',
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            'eligible_candidates' => $eligibleCandidates,
            'rejected_candidates' => $rejectedCandidates,
        ];
    }

    private function candidate(int $tickerId, string $tickerCode, array $metrics = []): array
    {
        $defaults = [
            'dv20_idr' => 7000000000.0,
            'atr14_pct' => 0.0500,
            'vol_ratio' => 1.50,
            'roc20' => 0.08,
            'hh20' => 9100.0,
            'ma20' => 8750.0,
            'ma50' => 8600.0,
            'close_to_hh20_pct' => -0.011,
            'close_vs_ma20_pct' => 0.028,
            'close_vs_ma50_pct' => 0.046,
            'ma20_slope_pct' => 0.012,
            'rs_20_vs_ihsg' => 0.034,
        ];

        return [
            'ticker_id' => $tickerId,
            'ticker_code' => $tickerCode,
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'policy_code' => 'WS',
            'policy_version' => 'WS_EOD_RUNTIME',
            'paramset_code' => 'WS_ACTIVE_BOOTSTRAP',
            'eligible_plan' => true,
            'guard_ok' => true,
            'reason_codes' => ['WS_LIQ_STRONG', 'WS_RISK_IDEAL'],
            'missing_fields' => [],
            'gate_metrics' => array_merge($defaults, $metrics),
            'gate_thresholds' => [
                'min_dv20_idr' => 1000000000.0,
                'dv20_strong_idr' => 5000000000.0,
                'min_atr14_pct' => 0.02,
                'max_atr14_pct' => 0.12,
                'atr_ideal_low' => 0.035,
                'atr_ideal_high' => 0.075,
                'min_vol_ratio' => 1.2,
            ],
        ];
    }
}
