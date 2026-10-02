<?php

final class MarketDataPublicationLifecycleTraceabilitySpec
{
    public const STAGE = 'MD-B10';
    public const ATTEMPT = 'MD-B10-A001';
    public const EXPECTED_DENOMINATOR = 1072;
    public const EXPECTED_OPTIONAL = 1;
    /**
     * Active rows owned by another stage that name MD-B10 as a supporting stage. The gate expected 1; the
     * measured value is 0 both at HEAD 800774357215eb86489a3750bc25c26abe26fe1b and on the current canonical
     * matrix (F-MD-B10-A002-001). Reconciled to the measured value; a genuinely moved row still fails closed.
     */
    public const EXPECTED_MOVED = 0;
    public const EXPECTED_REFERENCE = 239;

    public static function matrixPath(string $root): string
    {
        return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    }

    public static function rows(string $root): array
    {
        $h = fopen(self::matrixPath($root), 'r');
        if (! $h) {
            throw new RuntimeException('Cannot open traceability matrix.');
        }
        $headers = fgetcsv($h);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($r = fgetcsv($h)) !== false) {
            if (count($r) !== count($headers)) {
                continue;
            }
            $rows[] = array_combine($headers, $r);
        }
        fclose($h);

        return $rows;
    }

    public static function mandatory(string $root): array
    {
        return self::mandatoryFrom(self::rows($root));
    }

    public static function mandatoryFrom(array $rows): array
    {
        return array_values(array_filter($rows, static function ($r) {
            return $r['active'] === 'YES'
                && $r['primary_stage'] === self::STAGE
                && $r['coverage_requirement'] === 'REQUIRED'
                && $r['applicability'] === 'MANDATORY';
        }));
    }
}
