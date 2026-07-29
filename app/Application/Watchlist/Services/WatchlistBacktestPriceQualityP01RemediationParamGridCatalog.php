<?php

namespace App\Application\Watchlist\Services;

class WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
{
    public const CATALOG_CODE =
        'WS_BT_GRID_PRICE_QUALITY_P01_REMEDIATION_2026_07';
    public const CATALOG_VERSION = 'P01M1';
    public const CATALOG_COUNT = 1;
    public const ROW_CODE =
        'P01_M1_C1_MIN_PRICE_50_LOSS_CLOSE_NEG1_NEXT_OPEN';

    public static function rows(): array
    {
        $row = WatchlistBacktestPriceQualityP01ParamGridCatalog::rows()[0];
        $row['catalog_code'] = self::CATALOG_CODE;
        $row['catalog_version'] = self::CATALOG_VERSION;
        $row['catalog_hash'] = '';
        $row['row_code'] = self::ROW_CODE;
        $row['row_hash'] = '';
        $row['rationale'] = 'Single P01 remediation: retain exact C1 price-quality selection and add only a D1-D3 close loss signal at the unchanged canonical monthly floor -1%, executed next open.';
        $row['notes'] = self::CATALOG_CODE.'_'.self::ROW_CODE;
        $hashPayload = $row;
        unset($hashPayload['catalog_hash'], $hashPayload['row_hash']);
        $row['catalog_hash'] = self::hashPayload([$hashPayload]);
        $row['row_hash'] = sha1(self::CATALOG_CODE.'|'.self::ROW_CODE);

        return [$row];
    }

    public static function hash(): string
    {
        return (string) self::rows()[0]['catalog_hash'];
    }

    public static function researchSelection(): array
    {
        return WatchlistBacktestPriceQualityP01ParamGridCatalog
            ::researchSelectionForRow(
                WatchlistBacktestPriceQualityP01ParamGridCatalog::C1_ROW_CODE
            );
    }

    public static function researchExecution(): array
    {
        $execution =
            WatchlistBacktestTailRiskS01RemediationParamGridCatalog
                ::researchExecution();
        $execution['remediation_code'] = 'P01_M1_SINGLE_ALLOWED_REMEDIATION';

        return $execution;
    }

    private static function hashPayload(array $payload): string
    {
        return sha1(json_encode(self::normalize($payload), JSON_UNESCAPED_SLASHES));
    }

    private static function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map([self::class, 'normalize'], $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = self::normalize($item);
        }

        return $value;
    }
}
