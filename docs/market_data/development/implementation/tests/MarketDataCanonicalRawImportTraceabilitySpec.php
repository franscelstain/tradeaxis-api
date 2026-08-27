<?php

final class MarketDataCanonicalRawImportTraceabilitySpec
{
    public const STAGE = 'MD-B09';
    public const ATTEMPT = 'MD-B09-A002';
    public const EXPECTED_DENOMINATOR = 140;

    /**
     * MD-B09-A003 reference-population re-check. MD-S008-R0018 is the paragraph immediately after
     * MD-S008-R0017, which is REQUIRED; both are prohibitions on how an adapter may present itself
     * downstream. R0018 sat REFERENCE_ONLY with empty notes. Same shape as MD-S066-R0002 in
     * MD-B07-A002 and MD-S067-R0010 in MD-B08-A002: a standalone paragraph no enumerated-run
     * invariant can reach.
     */
    public const REMEDIATION_ATTEMPT = 'MD-B09-A003';

    public const REMEDIATION_EVIDENCE = 'E-MD-B09-A003-001';

    /** Rules MD-B09-A003 promoted, with the basis for each. */
    public const REMEDIATED_RULES = [
        'MD-S008-R0018' => 'a003_correction=promoted from REFERENCE_ONLY; a prohibition on leaking source '
            .'JSON paths, suffix rules, or proprietary status codes into consumer contracts, adjacent to the '
            .'already-REQUIRED MD-S008-R0017 and indistinguishable from it in form',
    ];

    /**
     * Reference rows this attempt re-derived and confirmed non-executable, with why. Recording the
     * call is the point: an empty-notes reference row is indistinguishable from one nobody read,
     * which is exactly how the three corrected defects survived.
     */
    public const REFERENCE_DECISIONS = [
        'MD-S023-R0045' => 'explanatory prose contrasting the zero-volume rule with the zero-price rule; the '
            .'executable predicate is owned by MD-S023-R0044, which is REQUIRED and SATISFIED',
        'MD-S036-R0001' => 'document status and historical guard marker recording a past docs-review run; '
            .'states no obligation on implementation',
        'MD-S036-R0032' => 'capability boundary disclaimer declaring that this contract produces no verdict, '
            .'state, flag or signal, so it has no blind spot to declare; section 2 descriptive context',
        'MD-S039-R0005' => 'capability boundary disclaimer of the same form as MD-S036-R0032; the storage and '
            .'no-price-source prohibitions it refers to are owned by the REQUIRED rows of this contract',
    ];
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
