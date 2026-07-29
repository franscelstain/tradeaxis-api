<?php

use App\Application\Watchlist\Services\WatchlistBacktestC171RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;
use App\Application\Watchlist\Services\WatchlistCandidateUniverseService;
use App\Application\Watchlist\Services\WatchlistPlanGroupingService;
use App\Application\Watchlist\Services\WatchlistScoringService;
use App\Application\Watchlist\Services\WeeklySwingC171RemediationDraftCatalogService;
use App\Application\Watchlist\Services\WeeklySwingParamsetRuntimeAdapter;
use App\Application\Watchlist\Services\WeeklySwingParamsetValidator;

class WatchlistBacktestC171RemediationParamGridCatalogTest extends TestCase
{
    public function testCatalogIsExactImmutableFiveRowDefinitionFromDiagnosticDesign(): void
    {
        $rows = WatchlistBacktestC171RemediationParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07', WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C171-R1', WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(5, WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_COUNT);
        $this->assertSame('82b0fcbf17823fda5ab59bd2dba3d947b4f9e233', WatchlistBacktestC171RemediationParamGridCatalog::hash());
        $this->assertCount(5, $rows);
        $this->assertSame($rows, WatchlistBacktestC171RemediationParamGridCatalog::rows());
        $this->assertSame([
            'C171_DRAFT_A_BROAD_MODERATE_SCORE_CAP',
            'C171_DRAFT_B_BROAD_SAMPLE_RECOVERY',
            'C171_DRAFT_C_MID_LIQ_LOW_ATR_SCORE_CAP',
            'C171_DRAFT_D_LOW_ATR_BALANCED',
            'C171_DRAFT_E_LOWER_VOLUME_BALANCED',
        ], array_column($rows, 'row_code'));

        foreach ($rows as $row) {
            $this->assertSame(WatchlistBacktestC171RemediationParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertGreaterThanOrEqual($row['dv20_strong_idr'], $row['max_dv20_idr']);
            $this->assertGreaterThanOrEqual($row['strong_vol_ratio'], $row['max_vol_ratio']);
            $this->assertGreaterThanOrEqual($row['min_dv20_idr'], $row['max_dv20_idr']);
            $this->assertGreaterThanOrEqual($row['min_vol_ratio'], $row['max_vol_ratio']);
            $this->assertGreaterThanOrEqual(0.0, $row['top_max_score_total']);
            $this->assertLessThanOrEqual(1.0, $row['top_max_score_total']);
        }
    }

    public function testCandidateHashesAreDerivedFromTheProvidedImmutableSourcePayload(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $service = new WeeklySwingC171RemediationDraftCatalogService();
        $validator = new WeeklySwingParamsetValidator();
        $sourceValidation = $validator->validate($source);
        $hashes = $service->deriveExpectedCandidateHashes(
            $sourceValidation['canonical_payload'],
            WatchlistBacktestC171RemediationParamGridCatalog::rows()
        );

        $this->assertTrue($sourceValidation['valid']);
        $this->assertNotSame(WeeklySwingC171RemediationDraftCatalogService::SOURCE_PARAMS_HASH, $sourceValidation['canonical_hash']);
        $this->assertCount(5, $hashes);
        $this->assertCount(5, array_unique(array_values($hashes)));
        $this->assertSame(array_column(WatchlistBacktestC171RemediationParamGridCatalog::rows(), 'row_code'), array_keys($hashes));
        foreach ($hashes as $paramsHash) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $paramsHash);
            $this->assertNotSame(WeeklySwingC171RemediationDraftCatalogService::SOURCE_PARAMS_HASH, $paramsHash);
        }
    }

    public function testCanonicalOptionalBoundsRemainBackwardCompatibleAndNewCandidateHashesThem(): void
    {
        $source = json_decode((string) file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/fixtures/paramset_valid.json'
        )), true);
        $validator = new WeeklySwingParamsetValidator();
        $baseline = $validator->validate($source);
        $this->assertTrue($baseline['valid']);

        $row = WatchlistBacktestC171RemediationParamGridCatalog::rows()[0];
        $row['param_id'] = 17101;
        $payload = (new WeeklySwingC171RemediationDraftCatalogService())->buildCandidatePayload($source, $row);
        $candidate = $validator->validate($payload);

        $this->assertTrue($candidate['valid'], json_encode($candidate['errors']));
        $this->assertNotSame($baseline['canonical_hash'], $candidate['canonical_hash']);
        $this->assertSame(50000000000, $candidate['canonical_payload']['liquidity']['max_dv20_idr']['value']);
        $this->assertSame(5.0, $candidate['canonical_payload']['volume']['max_vol_ratio']['value']);
        $this->assertSame(0.98, $candidate['canonical_payload']['grouping']['top_max_score_total']['value']);
        $this->assertSame('BT', $candidate['canonical_payload']['grouping']['top_max_score_total']['origin']);
        $this->assertSame('TEMP', $candidate['canonical_payload']['grouping']['top_max_score_total']['status']);
        $this->assertTrue($candidate['canonical_payload']['grouping']['top_max_score_total']['bt_target']);

        $runtime = (new WeeklySwingParamsetRuntimeAdapter())->adapt($candidate['canonical_payload']);
        $this->assertSame(50000000000, $runtime['liquidity']['max_dv20_idr']);
        $this->assertSame(5.0, $runtime['volume']['max_vol_ratio']);
        $this->assertSame(0.98, $runtime['grouping']['top_picks']['max_score_total']);
    }

    public function testRuntimeFactoryPreservesAllC171UpperBoundAxesAtDecisionTime(): void
    {
        $row = WatchlistBacktestC171RemediationParamGridCatalog::rows()[4];
        $row['param_id'] = 17105;
        $runtime = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame(50000000000.0, $runtime['liquidity']['max_dv20_idr']);
        $this->assertSame(3.0, $runtime['volume']['max_vol_ratio']);
        $this->assertSame(0.98, $runtime['grouping']['top_picks']['max_score_total']);
        $this->assertTrue($runtime['bt_grid_resolution']['upper_bound_contract']['applied_at_decision_time']);
        $this->assertFalse($runtime['bt_grid_resolution']['upper_bound_contract']['post_return_filtering_used']);
    }

    public function testCandidateUniverseRejectsOnlyRowsAboveExplicitUpperBounds(): void
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
                $this->candidate(1, 'OKAY', 10000000000, 0.05, 2.0),
                $this->candidate(2, 'HIDV', 60000000000, 0.05, 2.0),
                $this->candidate(3, 'HIVR', 10000000000, 0.05, 6.0),
            ],
        ];
        $result = $service->buildCandidateUniverseFromConsumerPayload($source, '2025-01-02', [
            'liquidity' => ['min_dv20_idr' => 1000000000, 'max_dv20_idr' => 50000000000, 'dv20_strong_idr' => 5000000000],
            'volume' => ['min_vol_ratio' => 1.2, 'max_vol_ratio' => 5.0],
            'risk' => ['min_atr14_pct' => 0.02, 'max_atr14_pct' => 0.075, 'atr_ideal_low' => 0.035, 'atr_ideal_high' => 0.075],
        ]);

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['OKAY'], array_column($result['eligible_candidates'], 'ticker_code'));
        $reasons = array_column($result['rejected_candidates'], 'canonical_fail_reason_code', 'ticker_code');
        $this->assertSame('WS_LIQ_HIGH', $reasons['HIDV']);
        $this->assertSame('WS_VOLR_HIGH', $reasons['HIVR']);
        $this->assertSame([
            'WS_ELIGIBLE' => 1,
            'WS_LIQ_HIGH' => 1,
            'WS_VOLR_HIGH' => 1,
        ], $result['reason_counts']);
    }

    public function testTopScoreCapRecalculatesTopPoolAndAllowsDecisionTimeReplacement(): void
    {
        $service = new WatchlistPlanGroupingService(new class extends WatchlistScoringService {});
        $items = [
            $this->scoredItem(1, 'SAT1', 1.00),
            $this->scoredItem(2, 'SAT2', 0.99),
            $this->scoredItem(3, 'KEEP1', 0.98),
            $this->scoredItem(4, 'KEEP2', 0.90),
            $this->scoredItem(5, 'KEEP3', 0.80),
        ];
        $result = $service->groupScoredOutput([
            'ready' => true,
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_SCORING_READY',
            'trade_date' => '2025-01-02',
            'items' => $items,
            'excluded' => [],
        ], [
            'grouping' => [
                'top_min_score_q' => 0.80,
                'secondary_min_score_q' => 0.65,
                'top_picks' => ['max_score_total' => 0.98, 'max_items' => 3],
            ],
        ], '2025-01-02');

        $this->assertTrue($result['is_ready']);
        $this->assertSame(['KEEP1'], array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertSame(3, $result['cutoff_manifest']['top_score_pool_count']);
        $this->assertSame(0.98, $result['cutoff_manifest']['top_picks_max_score_total']);
        $this->assertNotContains('SAT1', array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
        $this->assertNotContains('SAT2', array_column($result['groups']['TOP_PICKS'], 'ticker_code'));
    }

    private function candidate(int $tickerId, string $ticker, float $dv20, float $atr, float $volRatio): array
    {
        return [
            'trade_date' => '2025-01-02', 'trade_date_effective' => '2025-01-02',
            'publication_id' => 1, 'publication_version' => 1, 'run_id' => 1,
            'ticker_id' => $tickerId, 'ticker_code' => $ticker, 'close_price' => 1000,
            'indicators' => ['dv20idr' => $dv20, 'atr14_pct' => $atr, 'vol_ratio' => $volRatio],
        ];
    }

    private function scoredItem(int $tickerId, string $ticker, float $score): array
    {
        return [
            'ticker_id' => $tickerId, 'ticker_code' => $ticker, 'eligible_score' => true,
            'score_total' => $score,
            'score_components' => ['score_momentum' => $score, 'score_breakout' => $score, 'score_volume' => $score, 'score_risk' => $score],
            'score_weights' => ['momentum' => 0.3, 'breakout' => 0.3, 'volume' => 0.2, 'risk' => 0.2],
            'factor_breakdown' => [], 'reason_codes' => [],
            'ranking_keys' => [
                'score_total_desc' => $score, 'score_breakout_desc' => $score,
                'score_momentum_desc' => $score, 'dv20_idr_desc' => 10000000000,
                'atr14_pct_asc' => 0.05, 'ticker_id_asc' => $tickerId,
            ],
            'score_metrics' => ['dv20_idr' => 10000000000, 'atr14_pct' => 0.05, 'vol_ratio' => 2.0],
        ];
    }
}
