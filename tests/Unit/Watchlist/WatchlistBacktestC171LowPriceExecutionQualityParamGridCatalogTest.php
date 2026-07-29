<?php

use App\Application\Watchlist\Services\WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WeeklySwingC171LowPriceExecutionQualityDraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingDecisionTimeTickRiskService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;

class WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalogTest extends TestCase
{
    public function testCatalogIsExactFiveRowSemanticC01Definition(): void
    {
        $rows = WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_LOW_PRICE_EXECUTION_QUALITY_C01_2026_07', WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C01', WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_VERSION);
        $this->assertSame('bad53b5880f183a55163565fb1e073420c29a080', WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::hash());
        $this->assertCount(5, $rows);
        $this->assertSame([
            'C171_C01_A_TICK_EXPANSION_MILD',
            'C171_C01_B_TICK_EXPANSION_BALANCED',
            'C171_C01_C_TICK_EXPANSION_STRICT',
            'C171_C01_D_SCORE_BALANCED_RECALIBRATION',
            'C171_C01_E_SCORE_RISK_FORWARD_RECALIBRATION',
        ], array_column($rows, 'row_code'));
        $this->assertSame([0.015, 0.01, 0.005, null, null], array_column($rows, 'max_signal_tick_risk_expansion_pct'));
        foreach ($rows as $row) {
            $this->assertSame(WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertEqualsWithDelta(1.0, $row['w_momentum'] + $row['w_volume'] + $row['w_breakout'] + $row['w_risk'], 0.000001);
        }
    }

    public function testCandidateHashesDeriveFromProvidedCanonicalSourceAndRemainUnique(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $validator = new WeeklySwingParamsetValidator();
        $sourceValidation = $validator->validate($source);
        $service = new WeeklySwingC171LowPriceExecutionQualityDraftCatalogService();
        $hashes = $service->deriveExpectedCandidateHashes(
            $sourceValidation['canonical_payload'],
            WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows()
        );

        $this->assertTrue($sourceValidation['valid']);
        $this->assertCount(5, $hashes);
        $this->assertCount(5, array_unique(array_values($hashes)));
        foreach ($hashes as $hash) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $hash);
            $this->assertNotSame(WeeklySwingC171LowPriceExecutionQualityDraftCatalogService::SOURCE_PARAMS_HASH, $hash);
        }
    }

    public function testTickGuardCandidatesHashAndAdaptWhileScoreCandidatesOmitTheOptionalGuard(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $service = new WeeklySwingC171LowPriceExecutionQualityDraftCatalogService();
        $validator = new WeeklySwingParamsetValidator();
        $rows = WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows();

        $tickPayload = $service->buildCandidatePayload($source, $rows[1]);
        $tickValidation = $validator->validate($tickPayload);
        $this->assertTrue($tickValidation['valid'], json_encode($tickValidation['errors']));
        $this->assertSame(0.01, $tickValidation['canonical_payload']['risk']['max_signal_tick_risk_expansion_pct']['value']);
        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt($tickValidation['canonical_payload']);
        $this->assertSame(0.01, $runtime['risk']['max_signal_tick_risk_expansion_pct']);

        $scorePayload = $service->buildCandidatePayload($source, $rows[3]);
        $scoreValidation = $validator->validate($scorePayload);
        $this->assertTrue($scoreValidation['valid'], json_encode($scoreValidation['errors']));
        $this->assertArrayNotHasKey('max_signal_tick_risk_expansion_pct', $scoreValidation['canonical_payload']['risk']);
        $this->assertSame(0.35, $scoreValidation['canonical_payload']['scoring']['weights']['value']['momentum']);
        $this->assertSame(0.15, $scoreValidation['canonical_payload']['scoring']['weights']['value']['volume']);
    }

    public function testRuntimeFactoryPreservesDecisionTimeTickRiskContract(): void
    {
        $row = WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows()[2];
        $row['param_id'] = 171203;
        $runtime = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame(0.005, $runtime['risk']['max_signal_tick_risk_expansion_pct']);
        $this->assertTrue($runtime['bt_grid_resolution']['decision_time_tick_risk_contract']['enabled']);
        $this->assertSame(WeeklySwingDecisionTimeTickRiskService::CONTRACT, $runtime['bt_grid_resolution']['decision_time_tick_risk_contract']['contract']);
        $this->assertTrue($runtime['bt_grid_resolution']['decision_time_tick_risk_contract']['signal_date_close_only']);
        $this->assertFalse($runtime['bt_grid_resolution']['decision_time_tick_risk_contract']['future_entry_price_used']);
    }

    public function testCandidateUniverseAppliesTickRiskAtDecisionTimeAndFailsClosedWhenCloseMissing(): void
    {
        $service = new WatchlistCandidateUniverseService();
        $source = [
            'trade_date' => '2025-01-02',
            'trade_date_effective' => '2025-01-02',
            'publication_id' => 1,
            'publication_version' => 1,
            'run_id' => 1,
            'is_ready' => true,
            'candidates' => [
                $this->candidate(1, 'PASS', 200.0),
                $this->candidate(2, 'TICK', 60.0),
                $this->candidate(3, 'MISS', null),
            ],
        ];
        $paramset = [
            'liquidity' => ['min_dv20_idr' => 1000000000, 'max_dv20_idr' => 50000000000, 'dv20_strong_idr' => 5000000000],
            'volume' => ['min_vol_ratio' => 1.2, 'max_vol_ratio' => 5.0],
            'risk' => [
                'min_atr14_pct' => 0.02,
                'max_atr14_pct' => 0.06,
                'atr_ideal_low' => 0.035,
                'atr_ideal_high' => 0.06,
                'stop_atr_mult' => 1.5,
                'min_rr' => 1.5,
                'max_signal_tick_risk_expansion_pct' => 0.005,
            ],
        ];
        $result = $service->buildCandidateUniverseFromConsumerPayload($source, '2025-01-02', $paramset);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['PASS'], array_column($result['eligible_candidates'], 'ticker_code'));
        $reasons = array_column($result['rejected_candidates'], 'canonical_fail_reason_code', 'ticker_code');
        $this->assertSame('WS_TICK_RISK_HIGH', $reasons['TICK']);
        $this->assertSame('WS_DATA_MISSING', $reasons['MISS']);
        $tick = array_values(array_filter($result['universe_rows'], function (array $row): bool {
            return $row['ticker_code'] === 'TICK';
        }))[0];
        $this->assertGreaterThan(0.005, $tick['gate_metrics']['signal_tick_risk_expansion_pct']);
        $this->assertSame(WeeklySwingDecisionTimeTickRiskService::CONTRACT, $tick['gate_thresholds']['signal_tick_risk_contract']);
    }

    private function candidate(int $tickerId, string $ticker, ?float $close): array
    {
        return [
            'trade_date' => '2025-01-02',
            'trade_date_effective' => '2025-01-02',
            'publication_id' => 1,
            'publication_version' => 1,
            'run_id' => 1,
            'ticker_id' => $tickerId,
            'ticker_code' => $ticker,
            'close_price' => $close,
            'indicators' => [
                'dv20_idr' => 10000000000,
                'atr14_pct' => 0.04,
                'vol_ratio' => 2.0,
                'roc_20' => 0.08,
                'hh20' => 100.0,
            ],
        ];
    }
}
