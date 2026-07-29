<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService;
use TestCase;

class WeeklySwingC171ComparativeOfficialIsFailureDiagnosticTest extends TestCase
{
    public function testComparativeAnalysisSelectsBestCoverageValidFailedIsAnchorAndKeepsBoundaries(): void
    {
        $service = new WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService();
        $result = $service->analyzeComparativeEvidence(
            $this->pickRowsByEval(),
            $this->replayRowsByEval(),
            $this->artifacts(),
            []
        );

        $this->assertTrue($result['ready']);
        $this->assertSame(192, $result['anchor_eval_id']);
        $this->assertSame('LOCKED', $result['hypothesis_lock_status']);
        $this->assertNotEmpty($result['locked_hypotheses']);
        $this->assertStringStartsWith('WS_BT_GRID_', $result['next_semantic_catalog_code']);
        $this->assertStringNotContainsString('_R3_', $result['next_semantic_catalog_code']);
        $detailPriceSegments = array_values(array_filter($result['price_risk_rows'], function (array $row): bool {
            return $row['eval_id'] === 192 && $row['segment_type'] === 'ENTRY_PRICE_BAND_DETAIL';
        }));
        $this->assertNotEmpty($detailPriceSegments);
        $this->assertCount(15, $result['trade_overlap_rows']);
    }

    public function testPopulationReconciliationLocksOfficialTopOnlyMeaning(): void
    {
        $service = new WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService();
        $result = $service->analyzeComparativeEvidence(
            $this->pickRowsByEval(),
            $this->replayRowsByEval(),
            $this->artifacts(),
            []
        );

        $this->assertTrue($result['population_reconciliation_summary']['all_official_picks_match_metrics_picks_count']);
        $this->assertSame(
            'OFFICIAL_PICKS_EQUALS_METRICS_READY_TOP_ONLY;TRADE_EVIDENCE_INCLUDES_ALL_EVALUATED_BUCKETS',
            $result['population_reconciliation_summary']['population_contract']
        );
        $this->assertCount(6, $result['population_reconciliation_rows']);
    }

    public function testIdenticalAAndEOfficialPopulationsRejectStandaloneVolumeCapHypothesis(): void
    {
        $service = new WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService();
        $result = $service->analyzeComparativeEvidence(
            $this->pickRowsByEval(),
            $this->replayRowsByEval(),
            $this->artifacts(),
            []
        );

        $rejected = array_values(array_filter($result['rejected_hypotheses'], function (array $row): bool {
            return $row['hypothesis_code'] === 'MAX_VOL_RATIO_5_TO_3_AS_STANDALONE_FOCUS';
        }));
        $this->assertCount(1, $rejected);
        $this->assertSame('EVAL_189_AND_193_OFFICIAL_PICK_POPULATIONS_AND_RETURNS_ARE_IDENTICAL', $rejected[0]['reason']);
    }

    public function testCommandAndServiceAreRegisteredReadOnlyAndExposeRequiredOutputs(): void
    {
        $kernel = (string) file_get_contents(base_path('app/Console/Kernel.php'));
        $command = (string) file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC171ComparativeOfficialIsFailureDiagnosticCommand.php'));
        $service = (string) file_get_contents(base_path('app/Application/Watchlist/Services/WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService.php'));

        $this->assertStringContainsString('RunBacktestC171ComparativeOfficialIsFailureDiagnosticCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c171-comparative-official-is-failure-diagnostic', $command);
        foreach ([
            'trade_overlap_csv', 'added_removed_trades_csv', 'price_risk_segments_csv',
            'monthly_stability_csv', 'score_deciles_csv', 'market_regime_csv',
            'exit_distribution_csv', 'population_reconciliation_csv', 'r2_hypothesis_lock_json',
            "'draft_paramset_created' => false", "'official_is_runtime_invoked' => false",
            "'oos_runtime_invoked' => false", "'oos_table_read' => false", "'paramset_promoted' => false",
            "'plan_run_created' => false", "'production_ready' => false",
            'EXPECTED_ARTIFACT_FILE_SHA1', 'ENTRY_LINEAGE_MISMATCH',
            'ENTRY_PRICE_BAND_DETAIL', "'next_recommendation' => 'C171_IMPLEMENT_AND_PERSIST_IMMUTABLE_'",
        ] as $token) {
            $this->assertStringContainsString($token, $service);
        }
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $service);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $service);
        $this->assertStringNotContainsString('WeeklySwingParamsetPromotionService', $service);
        $this->assertStringNotContainsString('persistDraft(', $service);
        $this->assertStringNotContainsString('WatchlistBacktestIsCalibrationService', $service);
    }

    private function pickRowsByEval(): array
    {
        $rows = [];
        foreach ([188, 189, 190, 191, 192, 193] as $evalId) {
            $rows[$evalId] = [];
            for ($i = 1; $i <= 120; $i++) {
                $date = (new \DateTimeImmutable('2024-01-01'))->modify('+'.$i.' days')->format('Y-m-d');
                $cycle = $i % 4;
                $ret = $evalId === 192
                    ? [-0.08, -0.02, 0.03, 0.10][$cycle]
                    : [-0.09, -0.03, 0.02, 0.07][$cycle];
                $rows[$evalId][] = [
                    'eval_id' => $evalId,
                    'param_id' => $evalId - 187,
                    'asof_eod_date' => $date,
                    'ticker_id' => $i,
                    'ticker_code' => 'T'.$i,
                    'bucket_code' => 'TOP_PICKS',
                    'ret_net' => $ret,
                    'score_total' => 1.0 - (($i % 20) * 0.01),
                    'atr14_pct' => 0.04 + (($i % 4) * 0.005),
                    'dv20_idr' => 5000000000,
                    'vol_ratio' => 1.5,
                ];
            }
        }
        // A and E must be exact official-population/return twins for redundancy proof.
        $rows[193] = $rows[189];
        foreach ($rows[193] as &$row) {
            $row['eval_id'] = 193;
            $row['param_id'] = 6;
        }
        unset($row);
        return $rows;
    }

    private function replayRowsByEval(): array
    {
        $rows = $this->pickRowsByEval();
        foreach ($rows as $evalId => &$items) {
            foreach ($items as $index => &$row) {
                $row['official_ret_net'] = $row['ret_net'];
                $row['replay_ret_net'] = $row['ret_net'];
                $row['ret_net_parity'] = true;
                $lowPrice = ($index % 4) < 2;
                $row['entry_price'] = $lowPrice ? 100.0 : 500.0;
                $row['stop_price'] = $lowPrice ? 94.4 : 470.0;
                $row['stop_trigger_price'] = $lowPrice ? 94.0 : 470.0;
                $row['exit_reason_code'] = ($index % 4) < 3 ? 'WATCHLIST_BACKTEST_EXIT_STOP' : 'WATCHLIST_BACKTEST_EXIT_TARGET';
                $row['fill_rule'] = ($index % 5) === 0 ? 'GAP_THROUGH_STOP_AT_OPEN' : 'INTRADAY_STOP_AT_NORMALIZED_TRIGGER';
                $row['market_regime'] = ($index % 4) < 2 ? 'WEAK' : 'STRONG';
            }
            unset($row);
        }
        unset($items);
        return $rows;
    }

    private function artifacts(): array
    {
        $metrics = [
            188 => [-0.0025, -0.049, -0.082, 0.399, 0.241, -0.0345, 20],
            189 => [0.0030, -0.027, -0.071, 0.430, 0.285, -0.0275, 13],
            190 => [-0.0009, -0.045, -0.078, 0.403, 0.237, -0.0318, 17],
            191 => [0.0013, -0.030, -0.067, 0.420, 0.215, -0.0267, 15],
            192 => [0.0044, -0.025, -0.067, 0.432, 0.265, -0.0187, 15],
            193 => [0.0030, -0.027, -0.071, 0.430, 0.285, -0.0275, 13],
        ];
        $artifacts = [];
        foreach ($metrics as $evalId => $m) {
            $artifacts[$evalId] = [
                'canonical_is_gates_pass' => false,
                'is_calibration' => [
                    'evaluations' => [[
                        'eval_id' => $evalId,
                        'row_code' => 'ROW_'.$evalId,
                        'metrics' => [
                            'picks_count' => 120,
                            'days_covered' => 400,
                            'avg_ret_net_top' => $m[0],
                            'median_ret_net_top' => $m[1],
                            'p25_ret_net_top' => $m[2],
                            'win_rate_top' => $m[3],
                            'month_win_rate_min' => $m[4],
                            'month_avg_ret_net_min' => $m[5],
                            'period_fail_count' => $m[6],
                        ],
                        'trade_evidence' => ['evaluated_trade_count' => 240],
                    ]],
                ],
            ];
        }
        return $artifacts;
    }
}
