<?php

final class MarketDataCanonicalRawImportTraceabilitySpec
{
    public const STAGE = 'MD-B09';
    public const ATTEMPT = 'MD-B09-A002';
    public const EXPECTED_DENOMINATOR = 139;
    public const EXPECTED_OPTIONAL = 12;
    public const EXPECTED_MOVED = 46;

    public static function matrixPath(string $root): string
    {
        return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    }

    public static function rows(string $root): array
    {
        $p = self::matrixPath($root);
        $h = fopen($p, 'r');
        $headers = fgetcsv($h);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($r = fgetcsv($h)) !== false) {
            if (count($r) !== count($headers)) continue;
            $rows[] = array_combine($headers, $r);
        }
        fclose($h);
        return $rows;
    }

    public static function denominator(string $root): array
    {
        return array_values(array_filter(self::rows($root), static function ($r) {
            return $r['active'] === 'YES' && $r['primary_stage'] === self::STAGE
                && $r['coverage_requirement'] === 'REQUIRED' && $r['applicability'] === 'MANDATORY';
        }));
    }
}
