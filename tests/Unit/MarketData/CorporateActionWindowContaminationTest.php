<?php

use App\Application\MarketData\Services\EligibilityDecisionService;
use App\Application\MarketData\Services\IndicatorVectorService;
use PHPUnit\Framework\TestCase;

/**
 * Owner contract: docs/market_data/registry/Indicator_Registry_Baseline_LOCKED.md
 * (Amendment 2026-07-29 - Corporate action window contamination)
 */
class CorporateActionWindowContaminationTest extends TestCase
{
    private function config(array $contamination = [], $atrHorizon = 60)
    {
        return [
            'set_version' => 'ind_v1',
            'price_basis_default' => 'close',
            'dv_window_days' => 20,
            'atr_window_days' => 14,
            'vol_ratio_lookback_days' => 20,
            'roc_lookback_days' => 20,
            'hh_window_days' => 20,
            'benchmark_roc20_pct' => 5.0,
            'sector_roc20_pct' => 2.5,
            'sector_code' => 'G',
            'corporate_action_contamination' => $contamination,
            'atr_contamination_horizon_days' => $atrHorizon,
        ];
    }

    private function bars($days = 55)
    {
        $rows = [];
        for ($i = 1; $i <= $days; $i++) {
            $close = 100 + $i;
            $rows[] = [
                'trade_date' => date('Y-m-d', strtotime('2026-04-01 +'.($i - 1).' days')),
                'open' => $close - 1,
                'high' => $close + 1,
                'low' => $close - 2,
                'close' => $close,
                'adj_close' => $close,
                'volume' => 1000 + ($i * 10),
            ];
        }

        return $rows;
    }

    private function entry($depth, $type = 'STOCK_SPLIT', $breaksPrice = true, $breaksVolume = true, $date = '2026-05-10')
    {
        return [
            'action_type_code' => $type,
            'action_date' => $date,
            'depth' => $depth,
            'breaks_price_continuity' => $breaksPrice,
            'breaks_volume_continuity' => $breaksVolume,
            'is_unmapped_type' => false,
        ];
    }

    private function buildRow(array $config)
    {
        $service = new IndicatorVectorService();

        return $service->buildRow(101, $this->bars(55), '2026-05-25', 55, 9001, '2026-05-25 18:00:00', $config);
    }

    public function test_absent_contamination_leaves_row_untouched(): void
    {
        $row = $this->buildRow($this->config());

        $this->assertSame(1, $row['is_valid']);
        $this->assertNull($row['invalid_reason_code']);
        $this->assertNull($row['corporate_action_window_reasons']);
        $this->assertIsFloat($row['roc20']);
        $this->assertIsFloat($row['vol_ratio']);
    }

    public function test_scaling_action_inside_window_quarantines_price_and_volume_indicators(): void
    {
        $row = $this->buildRow($this->config([$this->entry(3)]));

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_CORPORATE_ACTION_DISCONTINUITY', $row['invalid_reason_code']);
        $this->assertSame('STOCK_SPLIT@2026-05-10', $row['corporate_action_window_reasons']);

        foreach (['dv20_idr', 'atr14_pct', 'vol_ratio', 'roc5', 'roc10', 'roc20', 'hh20', 'll20', 'ma20', 'ma50'] as $field) {
            $this->assertNull($row[$field], $field.' must be quarantined');
        }
    }

    /**
     * At depth == W the action lands on the window start, so every bar in the window already
     * sits on the post-action scale. Quarantining it would discard valid data.
     */
    public function test_roc5_boundary_contaminates_below_horizon_but_not_at_horizon(): void
    {
        $contaminated = $this->buildRow($this->config([$this->entry(5)]));
        $clean = $this->buildRow($this->config([$this->entry(6)]));

        $this->assertNull($contaminated['roc5'], 'depth 5 < horizon 6 must contaminate');
        $this->assertIsFloat(
            $clean['roc5'],
            'at depth 6 the action lands on the roc5 window start, so both reference bars are post-action'
        );

        // roc10 has horizon 11, so both depths stay inside its window and it must be nulled
        // either way. This proves the boundary is evaluated per indicator, not per row.
        $this->assertNull($contaminated['roc10']);
        $this->assertNull($clean['roc10']);
    }

    public function test_action_beyond_every_horizon_leaves_all_indicators_intact(): void
    {
        $row = $this->buildRow($this->config([$this->entry(60)]));

        $this->assertSame(1, $row['is_valid']);
        $this->assertNull($row['corporate_action_window_reasons']);
        $this->assertIsFloat($row['atr14_pct']);
        $this->assertIsFloat($row['ma50']);
    }

    public function test_volume_only_action_spares_price_derived_indicators(): void
    {
        $row = $this->buildRow($this->config([
            $this->entry(2, 'PRIVATE_PLACEMENT', false, true),
        ]));

        $this->assertNull($row['dv20_idr'], 'turnover uses volume');
        $this->assertNull($row['vol_ratio'], 'volume ratio uses volume');

        $this->assertIsFloat($row['roc20'], 'price-only indicator must survive a volume-only action');
        $this->assertIsFloat($row['hh20']);
        $this->assertIsFloat($row['ma50']);
        $this->assertIsFloat($row['atr14_pct']);

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame('IND_CORPORATE_ACTION_DISCONTINUITY', $row['invalid_reason_code']);
    }

    public function test_price_only_action_spares_volume_ratio(): void
    {
        $row = $this->buildRow($this->config([
            $this->entry(2, 'CASH_DIVIDEND', true, false),
        ]));

        $this->assertIsFloat($row['vol_ratio'], 'vol_ratio is volume-only and must survive');
        $this->assertNull($row['roc20']);
        $this->assertNull($row['dv20_idr'], 'turnover mixes price and volume, so price impact reaches it');
    }

    public function test_long_horizon_action_quarantines_only_long_horizon_indicators(): void
    {
        // Depth 30 sits inside ma50 (50) and atr14_pct (60) but outside roc20 (21) and hh20 (20).
        $row = $this->buildRow($this->config([$this->entry(30)]));

        $this->assertNull($row['ma50']);
        $this->assertNull($row['close_vs_ma50_pct']);
        $this->assertNull($row['atr14_pct']);

        $this->assertIsFloat($row['roc20']);
        $this->assertIsFloat($row['hh20']);
        $this->assertIsFloat($row['ma20']);
        $this->assertIsFloat($row['vol_ratio']);
    }

    public function test_tokens_are_sorted_and_deduplicated(): void
    {
        $row = $this->buildRow($this->config([
            $this->entry(3, 'STOCK_SPLIT', true, true, '2026-05-20'),
            $this->entry(4, 'BONUS_SHARE', true, true, '2026-05-19'),
            $this->entry(5, 'STOCK_SPLIT', true, true, '2026-05-20'),
        ]));

        $this->assertSame(
            'BONUS_SHARE@2026-05-19,STOCK_SPLIT@2026-05-20',
            $row['corporate_action_window_reasons']
        );
    }

    public function test_hard_structural_reason_code_is_not_overridden_by_contamination(): void
    {
        $bars = $this->bars(55);
        $bars[40]['close'] = null;

        $service = new IndicatorVectorService();
        $row = $service->buildRow(
            101,
            $bars,
            '2026-05-25',
            55,
            9001,
            '2026-05-25 18:00:00',
            $this->config([$this->entry(3)])
        );

        $this->assertSame(0, $row['is_valid']);
        $this->assertSame(
            'IND_MISSING_DEPENDENCY_BAR',
            $row['invalid_reason_code'],
            'a genuine data hole stays more actionable than the quarantine annotation'
        );
        $this->assertNotNull($row['corporate_action_window_reasons'], 'quarantine trail is still recorded');
    }

    public function test_eligibility_maps_contamination_to_its_own_reason_code(): void
    {
        $decision = (new EligibilityDecisionService())->decide(
            ['close' => 100],
            ['is_valid' => 0, 'invalid_reason_code' => 'IND_CORPORATE_ACTION_DISCONTINUITY']
        );

        $this->assertSame(0, $decision['eligible']);
        $this->assertSame('ELIG_CORPORATE_ACTION_DISCONTINUITY', $decision['reason_code']);
    }

    public function test_eligibility_still_falls_back_for_unmapped_indicator_reasons(): void
    {
        $decision = (new EligibilityDecisionService())->decide(
            ['close' => 100],
            ['is_valid' => 0, 'invalid_reason_code' => 'IND_INVALID_BAR_INPUT']
        );

        $this->assertSame('ELIG_INVALID_INDICATORS', $decision['reason_code']);
    }
}
