<?php

use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory;

class WatchlistBacktestC07ParamGridParamsetFactoryTest extends TestCase
{
    public function test_factory_maps_c07_fields_and_embeds_feature_confirmation_extension(): void
    {
        $row = WatchlistBacktestC07ParamGridCatalog::rows()[1];
        $row['param_id'] = 12001;
        $snapshot = (new WatchlistBacktestParamGridParamsetFactory())->make($row);

        $this->assertSame($row['catalog_code'], $snapshot['bt_catalog']['catalog_code']);
        $this->assertSame($row['catalog_version'], $snapshot['bt_catalog']['catalog_version']);
        $this->assertSame($row['catalog_hash'], $snapshot['bt_catalog']['catalog_hash']);
        $this->assertSame($row['row_code'], $snapshot['bt_catalog']['row_code']);
        $this->assertSame((float) $row['min_dv20_idr'], $snapshot['liquidity']['min_dv20_idr']);
        $this->assertSame((float) $row['dv20_strong_idr'], $snapshot['liquidity']['dv20_strong_idr']);
        $this->assertSame($row['min_vol_ratio'], $snapshot['volume']['min_vol_ratio']);
        $this->assertSame($row['strong_vol_ratio'], $snapshot['volume']['strong_vol_ratio']);
        $this->assertSame($row['top_min_score_q'], $snapshot['grouping']['top_min_score_q']);
        $this->assertSame($row['secondary_min_score_q'], $snapshot['grouping']['secondary_min_score_q']);
        $this->assertSame(
            WatchlistBacktestC07ParamGridCatalog::candidateSelectionExtension(),
            $snapshot['bt_grid_resolution']['candidate_selection_extension']
        );
    }

    public function test_c07_extension_is_distinct_from_c06_extension(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $c06 = WatchlistBacktestC06ParamGridCatalog::rows()[0];
        $c07 = WatchlistBacktestC07ParamGridCatalog::rows()[0];
        $c06['param_id'] = 11000;
        $c07['param_id'] = 12000;

        $c06Snapshot = $factory->make($c06);
        $c07Snapshot = $factory->make($c07);

        $this->assertSame('C06_MODERATE_LIQUIDITY_VOLUME_ROC_STABILITY_FLOOR', $c06Snapshot['bt_grid_resolution']['candidate_selection_extension']['mode']);
        $this->assertSame('C07_SHORT_TERM_RANGE_SECTOR_CONFIRMATION', $c07Snapshot['bt_grid_resolution']['candidate_selection_extension']['mode']);
        $this->assertNotSame(
            sha1(json_encode($this->normalize($c06Snapshot), JSON_UNESCAPED_SLASHES)),
            sha1(json_encode($this->normalize($c07Snapshot), JSON_UNESCAPED_SLASHES))
        );
    }

    public function test_c07_paramset_hash_does_not_depend_on_database_surrogate_param_id(): void
    {
        $factory = new WatchlistBacktestParamGridParamsetFactory();
        $left = WatchlistBacktestC07ParamGridCatalog::rows()[2];
        $right = $left;
        $left['param_id'] = 107;
        $right['param_id'] = 120107;

        $leftSnapshot = $factory->make($left);
        $rightSnapshot = $factory->make($right);

        $this->assertArrayNotHasKey('param_id', $leftSnapshot['bt_grid']);
        $this->assertSame($leftSnapshot, $rightSnapshot);
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
