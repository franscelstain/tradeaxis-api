<?php

/**
 * B13 static invariants for actual/proxy liquidity metrics.
 *
 * Four of these are prohibitions, and a prohibition is proven by absence. That makes them the
 * easiest checks in the package to write badly: a scan that matches nothing looks identical to a
 * scan that ran against nothing. Every prohibition here therefore carries a positive floor — the
 * files it searched, and a required-presence check on the same surface — so a gate that silently
 * stopped reading fails instead of passing.
 */
final class MarketDataLiquidityMetricStaticGate
{
    /** Surfaces where a new `dv*` name, or a lot multiplier, would actually matter. */
    private const SCANNED_TREES = ['app', 'database/migrations', 'config'];

    private const MIN_SCANNED_FILES = 150;

    public static function run(string $root): array
    {
        $errors = [];

        // 1. Required presence: the labelling surface exists and states its own invariants.
        $must = [
            'app/Domain/MarketData/LiquidityMetricLabelRegistry.php' => [
                'ACTUAL', 'PROXY', 'adv20_close_volume_proxy_idr', 'adv20_traded_value_idr_actual',
                'traded_value_idr_actual', 'DV20_RETIREMENT_CONDITION', 'is_compatibility_alias',
            ],
            'app/Application/MarketData/Services/LiquidityMetricLabelService.php' => [
                'UNLABELLED_LIQUIDITY_METRIC', 'driftAgainstDeclared', 'unlabelledPublishedMetrics', 'md_liquidity_metric_labels',
            ],
            'app/Application/MarketData/Services/VolumeUnitNormalizationService.php' => [
                'VOLUME_UNIT_NORMALIZATION_UNVERIFIED', 'STATE_VERIFIED', 'UNIT_SHARES', 'volume_unit_evidence_ref',
            ],
            'app/Application/MarketData/Services/MarketDataPipelineService.php' => [
                'RUN_SEAL_UNLABELLED_LIQUIDITY_METRIC', 'assertLiquidityMetricsLabelled',
            ],
            'app/Domain/MarketData/MarketDataSemanticBindings.php' => [
                'LIQUIDITY_FORMULA_VERSION', 'SOURCE_REPORTED_LIQUIDITY_VERSION', 'CANONICAL_VOLUME_UNIT_CODE',
            ],
            'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php' => [
                'volume_unit', 'volume_unit_evidence_ref',
            ],
            'app/Infrastructure/Persistence/MarketData/MarketDataReadProductRepository.php' => [
                'liquidity_metric_labels',
            ],
        ];
        foreach ($must as $path => $needles) {
            if (! is_file($root.'/'.$path)) {
                $errors[] = 'MISSING:'.$path;

                continue;
            }
            $source = (string) file_get_contents($root.'/'.$path);
            foreach ($needles as $needle) {
                if (strpos($source, $needle) === false) {
                    $errors[] = 'INVARIANT_MISSING:'.$path.':'.$needle;
                }
            }
        }

        $files = self::scan($root);
        if (count($files) < self::MIN_SCANNED_FILES) {
            $errors[] = 'SCAN_FLOOR_NOT_REACHED:'.count($files);
        }

        $newDvNames = [];
        $lotMultipliers = [];
        $adjustedTimesRawVolume = [];
        $aggregateSurface = [];

        foreach ($files as $relative => $source) {
            // 2. `dv*` naming prohibition. The one alias that predates the prohibition is named
            //    exactly, so preserving it stays legal and propagating it does not.
            if (preg_match_all('/[\'"`]([A-Za-z_]*\bdv[a-z_]*\d*_?[a-z_]*)[\'"`]/', $source, $matches)) {
                foreach ($matches[1] as $name) {
                    $lower = strtolower($name);
                    if (strpos($lower, 'dv') !== 0) {
                        continue;
                    }
                    if (in_array($lower, ['dv20_idr', 'dv20idr', 'dv_window_days', 'dv20'], true)) {
                        continue;
                    }
                    $newDvNames[] = $relative.':'.$name;
                }
            }

            // 3. Lot-size boundary. Market-data owns no position-sizing configuration and never
            //    multiplies share volume by a lot size.
            if (preg_match('/\b(lot_size|lotSize|LOT_SIZE|lot_multiplier|lotMultiplier)\b/', $source)) {
                $lotMultipliers[] = $relative;
            }

            // 4. Dimensional consistency: adjusted price must never meet raw volume.
            if (preg_match('/adjusted[A-Za-z_]*Close\s*\*\s*\$?raw[A-Za-z_]*Volume|adjusted_close\s*\*\s*raw_volume/i', $source)) {
                $adjustedTimesRawVolume[] = $relative;
            }

            // 5. Aggregate-capability condition. This is the evidence side of the fourteen
            //    conditional MD-S042 rows: the condition is false only for as long as no aggregate
            //    surface exists, and this is what notices when one appears.
            if (strpos($source, 'market_daily_metrics') !== false
                || strpos($source, 'total_traded_value_idr_actual') !== false
                || strpos($source, 'total_close_volume_proxy_idr') !== false) {
                $aggregateSurface[] = $relative;
            }
        }

        foreach ($newDvNames as $hit) {
            $errors[] = 'DV_NAME_PROPAGATED:'.$hit;
        }
        foreach ($lotMultipliers as $hit) {
            $errors[] = 'LOT_SIZE_IN_MARKET_DATA:'.$hit;
        }
        foreach ($adjustedTimesRawVolume as $hit) {
            $errors[] = 'ADJUSTED_PRICE_TIMES_RAW_VOLUME:'.$hit;
        }

        // 6. The alias must not stand in for the field it aliases: the explicitly named proxy
        //    column has to exist in its own right, not merely as a second name for dv20_idr.
        $registry = (string) @file_get_contents($root.'/app/Domain/MarketData/LiquidityMetricLabelRegistry.php');
        if (strpos($registry, "'aliases_metric_field' => 'adv20_close_volume_proxy_idr'") === false) {
            $errors[] = 'ALIAS_TARGET_NOT_DECLARED';
        }
        $indicatorMigration = (string) @file_get_contents($root.'/database/migrations/2026_08_02_000001_add_market_data_strategy_v2_foundation.php');
        if (strpos($indicatorMigration, 'adv20_close_volume_proxy_idr') === false) {
            $errors[] = 'EXPLICIT_PROXY_COLUMN_ABSENT';
        }

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'scanned_files' => count($files),
            'aggregate_surface_hits' => array_values(array_unique($aggregateSurface)),
            'aggregate_capability_state' => $aggregateSurface === [] ? 'NOT_REQUESTED_NO_SURFACE' : 'SURFACE_PRESENT',
            'errors' => $errors,
        ];
    }

    /** @return array<string,string> relative path => source */
    private static function scan(string $root): array
    {
        $out = [];
        foreach (self::SCANNED_TREES as $tree) {
            $dir = $root.'/'.$tree;
            if (! is_dir($dir)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
                    continue;
                }
                $path = str_replace('\\', '/', $file->getPathname());
                $out[substr($path, strlen(str_replace('\\', '/', $root)) + 1)] = (string) file_get_contents($path);
            }
        }
        ksort($out);

        return $out;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $result = MarketDataLiquidityMetricStaticGate::run(dirname(__DIR__, 5));
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
