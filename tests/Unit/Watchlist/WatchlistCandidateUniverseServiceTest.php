<?php

use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistMarketDataConsumerReadService;

class WatchlistCandidateUniverseServiceTest extends TestCase
{
    public function test_candidate_universe_applies_liquidity_risk_and_volume_guards_deterministically(): void
    {
        $payload = $this->sourcePayload([
            $this->candidate('BBCA', 7000000000.0, 0.0500, 1.50),
            $this->candidate('BBRI', 999999999.0, 0.0500, 1.50),
            $this->candidate('BRPT', 7000000000.0, 0.1300, 1.50),
            $this->candidate('ANTM', 7000000000.0, 0.0500, 1.10),
        ]);

        $service = new WatchlistCandidateUniverseService($this->fakeReadModel($payload));
        $result = $service->buildCandidateUniverseForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertTrue($result['has_eligible_candidates']);
        $this->assertSame('WATCHLIST_CANDIDATE_UNIVERSE_READY', $result['reason_code']);
        $this->assertSame('WS', $result['policy_code']);
        $this->assertSame('WS_EOD_RUNTIME', $result['policy_version']);
        $this->assertSame('WS_ACTIVE_BOOTSTRAP', $result['paramset_code']);
        $this->assertSame(4, $result['input_candidate_count']);
        $this->assertSame(1, $result['eligible_count']);
        $this->assertSame(3, $result['rejected_count']);
        $this->assertSame('BBCA', $result['eligible_candidates'][0]['ticker_code']);
        $this->assertSame(['WS_ELIGIBLE' => 1, 'WS_LIQ_FAIL' => 1, 'WS_ATR_HIGH' => 1, 'WS_VOLR_FAIL' => 1], $result['reason_counts']);
        $this->assertTrue($result['universe_contract']['applies_liquidity_guard']);
        $this->assertTrue($result['universe_contract']['applies_risk_guard']);
        $this->assertTrue($result['universe_contract']['applies_volume_participation_guard']);
        $this->assertTrue($result['universe_contract']['does_not_score']);
        $this->assertTrue($result['universe_contract']['does_not_recommend']);
        $this->assertTrue($result['universe_contract']['does_not_backtest']);

        $rejections = [];
        foreach ($result['rejected_candidates'] as $row) {
            $rejections[$row['ticker_code']] = $row['canonical_fail_reason_code'];
        }

        $this->assertSame('WS_LIQ_FAIL', $rejections['BBRI']);
        $this->assertSame('WS_ATR_HIGH', $rejections['BRPT']);
        $this->assertSame('WS_VOLR_FAIL', $rejections['ANTM']);
        $this->assertArrayNotHasKey('score', $result['eligible_candidates'][0]);
        $this->assertArrayNotHasKey('recommendation', $result['eligible_candidates'][0]);
    }

    public function test_candidate_universe_preserves_canonical_reason_priority_when_multiple_guards_fail(): void
    {
        $payload = $this->sourcePayload([
            $this->candidate('SMBR', 100000000.0, 0.2500, 0.50),
            $this->candidate('ELSA', 7000000000.0, 0.0100, 1.50),
        ]);

        $service = new WatchlistCandidateUniverseService($this->fakeReadModel($payload));
        $result = $service->buildCandidateUniverseForTradeDate('2026-05-19');

        $this->assertTrue($result['is_ready']);
        $this->assertFalse($result['has_eligible_candidates']);
        $this->assertSame('WATCHLIST_CANDIDATE_UNIVERSE_EMPTY', $result['reason_code']);
        $this->assertSame(0, $result['eligible_count']);
        $this->assertSame(2, $result['rejected_count']);
        $this->assertSame('WS_LIQ_FAIL', $result['rejected_candidates'][0]['canonical_fail_reason_code']);
        $this->assertContains('WS_ATR_HIGH', $result['rejected_candidates'][0]['reason_codes']);
        $this->assertContains('WS_VOLR_FAIL', $result['rejected_candidates'][0]['reason_codes']);
        $this->assertSame('WS_ATR_LOW', $result['rejected_candidates'][1]['canonical_fail_reason_code']);
    }

    public function test_candidate_universe_fails_closed_when_source_read_model_is_not_ready(): void
    {
        $payload = [
            'trade_date' => '2026-05-19',
            'trade_date_effective' => null,
            'publication_id' => null,
            'publication_version' => null,
            'run_id' => null,
            'is_ready' => false,
            'reason_code' => 'NO_READABLE_PUBLICATION',
            'watchlist_reason_code' => 'MARKET_DATA_NOT_READY',
            'candidates' => [],
        ];

        $service = new WatchlistCandidateUniverseService($this->fakeReadModel($payload));
        $result = $service->buildCandidateUniverseForTradeDate('2026-05-19');

        $this->assertFalse($result['is_ready']);
        $this->assertSame('MARKET_DATA_NOT_READY', $result['reason_code']);
        $this->assertSame('WATCHLIST_CANDIDATE_UNIVERSE_SOURCE_NOT_READY', $result['candidate_universe_reason_code']);
        $this->assertSame([], $result['eligible_candidates']);
        $this->assertSame([], $result['rejected_candidates']);
    }

    public function test_candidate_universe_accepts_nested_paramset_value_shape_and_rejects_invalid_atr_units(): void
    {
        $payload = $this->sourcePayload([
            $this->candidate('BBCA', 1500000000.0, 0.0500, 1.30),
        ]);

        $validParamset = [
            'paramset_code' => 'WS_TEST_PARAMSET',
            'liquidity' => [
                'min_dv20_idr' => ['value' => 1000000000],
                'dv20_strong_idr' => ['value' => 5000000000],
            ],
            'volume' => [
                'min_vol_ratio' => ['value' => 1.2],
            ],
            'risk' => [
                'min_atr14_pct' => ['value' => 0.02],
                'max_atr14_pct' => ['value' => 0.12],
                'atr_ideal_low' => ['value' => 0.035],
                'atr_ideal_high' => ['value' => 0.075],
            ],
        ];

        $service = new WatchlistCandidateUniverseService($this->fakeReadModel($payload));
        $valid = $service->buildCandidateUniverseForTradeDate('2026-05-19', $validParamset);

        $this->assertTrue($valid['is_ready']);
        $this->assertSame('WS_TEST_PARAMSET', $valid['paramset_code']);
        $this->assertSame(1, $valid['eligible_count']);

        $invalid = $service->buildCandidateUniverseForTradeDate('2026-05-19', [
            'risk' => [
                'min_atr14_pct' => ['value' => 2.0],
                'max_atr14_pct' => ['value' => 12.0],
            ],
        ]);

        $this->assertFalse($invalid['is_ready']);
        $this->assertSame('WATCHLIST_PARAMSET_INVALID', $invalid['reason_code']);
        $this->assertContains('risk.min_atr14_pct must be a fraction between 0 and 1, not percent-points', $invalid['paramset_errors']);
        $this->assertContains('risk.max_atr14_pct must be a fraction between 0 and 1, not percent-points', $invalid['paramset_errors']);
    }

    private function fakeReadModel(array $payload): WatchlistMarketDataConsumerReadService
    {
        return new class($payload) extends WatchlistMarketDataConsumerReadService {
            private array $payload;

            public function __construct(array $payload)
            {
                $this->payload = $payload;
            }

            public function getCandidateUniverseForTradeDate(string $tradeDate): array
            {
                return $this->payload;
            }
        };
    }

    private function sourcePayload(array $candidates): array
    {
        return [
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_MARKET_DATA_READY',
            'watchlist_reason_code' => 'WATCHLIST_MARKET_DATA_READY',
            'source_contract' => [
                'resolution_mode' => 'current_readable_publication_pointer',
                'forbids_raw_staging_latest_max_date_bypass' => true,
            ],
            'candidates' => $candidates,
            'candidate_count' => count($candidates),
            'excluded_rows' => [],
            'excluded_count' => 0,
        ];
    }

    private function candidate(string $tickerCode, float $dv20Idr, float $atr14Pct, float $volRatio): array
    {
        return [
            'trade_date' => '2026-05-19',
            'trade_date_effective' => '2026-05-19',
            'publication_id' => 2,
            'publication_version' => 1,
            'run_id' => 3,
            'ticker_code' => $tickerCode,
            'ticker_name' => $tickerCode.' Tbk',
            'close_price' => 9000.0,
            'volume' => 123456,
            'eligibility_state' => 'ELIGIBLE',
            'indicator_set_version' => 'v1',
            'indicators' => [
                'dv20idr' => $dv20Idr,
                'atr14_pct' => $atr14Pct,
                'vol_ratio' => $volRatio,
                'roc_20' => 5.2,
                'hh20' => 9100.0,
                'ma20' => 8750.0,
                'ma50' => 8600.0,
                'close_to_hh20_pct' => -1.1,
                'close_vs_ma20_pct' => 2.8,
                'close_vs_ma50_pct' => 4.6,
                'ma20_slope_pct' => 1.2,
                'rs_20_vs_ihsg' => 3.4,
            ],
        ];
    }
}
