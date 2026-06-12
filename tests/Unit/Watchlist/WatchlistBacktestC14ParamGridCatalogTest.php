<?php

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;

class WatchlistBacktestC14ParamGridCatalogTest extends TestCase
{
    public function test_catalog_is_new_deterministic_and_distinct_from_failed_prior_catalogs(): void
    {
        $first = WatchlistBacktestC14ParamGridCatalog::rows();
        $second = WatchlistBacktestC14ParamGridCatalog::rows();

        $this->assertSame('WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06', WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertSame('C14', WatchlistBacktestC14ParamGridCatalog::CATALOG_VERSION);
        $this->assertSame(12, WatchlistBacktestC14ParamGridCatalog::CATALOG_COUNT);
        $this->assertCount(WatchlistBacktestC14ParamGridCatalog::CATALOG_COUNT, $first);
        $this->assertSame($first, $second);
        $this->assertSame('079430de7c94fd0226d0f3b47d5eb1e9f906fd6a', WatchlistBacktestC14ParamGridCatalog::hash());
        $this->assertNotSame(WatchlistBacktestParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
        $this->assertNotSame(WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE, WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE);
    }

    public function test_rows_are_unique_valid_and_use_c13_variable_risk_exit_axes(): void
    {
        $rowCodes = [];
        $parameterHashes = [];
        $stopValues = [];
        $rrValues = [];

        foreach (WatchlistBacktestC14ParamGridCatalog::rows() as $row) {
            $this->assertSame('WS', $row['policy_code']);
            $this->assertSame(WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE, $row['catalog_code']);
            $this->assertSame(WatchlistBacktestC14ParamGridCatalog::CATALOG_VERSION, $row['catalog_version']);
            $this->assertSame(WatchlistBacktestC14ParamGridCatalog::hash(), $row['catalog_hash']);
            $this->assertSame(sha1($row['catalog_code'].'|'.$row['row_code']), $row['row_hash']);
            $this->assertSame(WatchlistBacktestC14ParamGridCatalog::FIXED_TOP_PICKS_TARGET, $row['top_picks_target']);
            $this->assertSame(WatchlistBacktestC14ParamGridCatalog::FIXED_SECONDARY_TARGET, $row['secondary_target']);
            $this->assertGreaterThanOrEqual(0.80, $row['stop_atr_mult']);
            $this->assertLessThanOrEqual(1.70, $row['stop_atr_mult']);
            $this->assertGreaterThanOrEqual(0.75, $row['min_rr']);
            $this->assertLessThanOrEqual(1.20, $row['min_rr']);
            $this->assertLessThanOrEqual($row['dv20_strong_idr'], $row['min_dv20_idr']);
            $this->assertLessThanOrEqual($row['strong_vol_ratio'], $row['min_vol_ratio']);
            $this->assertLessThanOrEqual($row['atr_ideal_low'], $row['min_atr14_pct']);
            $this->assertLessThanOrEqual($row['atr_ideal_high'], $row['atr_ideal_low']);
            $this->assertLessThanOrEqual($row['max_atr14_pct'], $row['atr_ideal_high']);
            $this->assertLessThan($row['roc_hi'], $row['roc_lo']);
            $this->assertEqualsWithDelta(1.0, $row['w_momentum'] + $row['w_breakout'] + $row['w_volume'] + $row['w_risk'], 0.000001);
            $this->assertLessThanOrEqual($row['top_min_score_q'], $row['secondary_min_score_q']);

            $this->assertArrayNotHasKey($row['row_code'], $rowCodes);
            $rowCodes[$row['row_code']] = true;
            $stopValues[(string) $row['stop_atr_mult']] = true;
            $rrValues[(string) $row['min_rr']] = true;

            $hash = $this->parameterHash($row);
            $this->assertArrayNotHasKey($hash, $parameterHashes);
            $parameterHashes[$hash] = true;
        }

        $this->assertGreaterThan(3, count($stopValues));
        $this->assertGreaterThan(2, count($rrValues));
    }

    public function test_axes_reuse_c07_confirmation_and_c13_exit_axis_support_only(): void
    {
        $axes = WatchlistBacktestC14ParamGridCatalog::parameterAxes();
        $provenance = WatchlistBacktestC14ParamGridCatalog::provenance();
        $policy = WatchlistBacktestC14ParamGridCatalog::exitAxisPolicy();

        $this->assertSame(WatchlistBacktestC07ParamGridCatalog::candidateSelectionExtension(), WatchlistBacktestC14ParamGridCatalog::candidateSelectionExtension());
        $this->assertContains('c14.variable_exit_axis.risk.stop_atr_mult', $axes);
        $this->assertContains('c14.variable_exit_axis.risk.min_rr', $axes);
        $this->assertSame($axes, array_keys(WatchlistBacktestC14ParamGridCatalog::axisRationale()));
        $this->assertSame(WatchlistBacktestExitAxisSupport::POLICY_VARIABLE_RISK_EXIT_AXIS, $policy['policy']);
        $this->assertSame([
            WatchlistBacktestExitAxisSupport::AXIS_RISK_STOP_ATR_MULT,
            WatchlistBacktestExitAxisSupport::AXIS_RISK_MIN_RR,
        ], $policy['runtime_axes']);
        $this->assertContains(WatchlistBacktestExitAxisSupport::AXIS_HOLDING_DAYS, $policy['blocked_first_phase_axes']);
        $this->assertContains(WatchlistBacktestExitAxisSupport::AXIS_TARGET_PCT, $policy['blocked_first_phase_axes']);
        $this->assertContains(WatchlistBacktestExitAxisSupport::AXIS_STOP_PCT, $policy['blocked_first_phase_axes']);
        $this->assertFalse($provenance['oos_used']);
        $this->assertFalse($provenance['sector_filter_used']);
        $this->assertSame('C07_CONFIRMATION_ONLY_NO_SECTOR_WHITELIST', $provenance['sector_evidence_usage']);
        $this->assertCount(WatchlistBacktestC14ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC14ParamGridCatalog::manifestRows());
    }

    private function parameterHash(array $row): string
    {
        $columns = [
            'min_dv20_idr', 'dv20_strong_idr',
            'min_vol_ratio', 'strong_vol_ratio', 'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'stop_atr_mult', 'min_rr',
            'top_picks_target', 'secondary_target',
            'top_min_score_q', 'secondary_min_score_q',
        ];
        $payload = [];
        foreach ($columns as $column) {
            $payload[$column] = $row[$column];
        }

        return sha1(json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map([$this, 'normalize'], $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }
}
