<?php
final class MarketDataCorporateActionTraceabilitySpec
{
    public const STAGE='MD-B11'; public const ATTEMPT='MD-B11-A001'; public const EXPECTED_DENOMINATOR=172; public const EXPECTED_REFERENCE=239;

    /**
     * MD-B11-A002 reference-population re-check, high-severity scope.
     *
     * B11 held the last four whole prohibition sections in the package at required=0, plus the
     * verification- and effective-date hierarchies. Among them sat MD-S011-R0023, the invariant the
     * reference-population audit demonstrated was enforced by one line at AdjustmentFactorSetService
     * and protected by nothing: widening its filter passed all 1946 tests. Its MD-B12 twin was
     * guarded in MD-B12-A002; this is the remaining copy.
     *
     * All four documents already map to a proof family, so these rows inherit their guards. Only the
     * two continuity-diagnostic rules needed a family of their own, because inheriting
     * verified_event_lifecycle would have bound them to guards that do not prove them.
     */
    public const REMEDIATION_ATTEMPT='MD-B11-A002';

    public const REMEDIATION_EVIDENCE='E-MD-B11-A002-001';

    /** Rules MD-B11-A002 promoted. Fragments of wrapped sentences are deliberately excluded. */
    public const REMEDIATED_RULES=[
        'MD-S011-R0023'=>'verification hierarchy: only verified revisions may be adjustment-active',
        'MD-S011-R0044'=>'effective-date hierarchy: ex_date is the primary continuity anchor',
        'MD-S011-R0045'=>'effective-date hierarchy: a named verified effective date needs proving semantics',
        'MD-S011-R0046'=>'effective-date hierarchy: other dates are not interchangeable with ex-date',
        'MD-S011-R0047'=>'effective-date hierarchy: no verified anchor means adjustment is forbidden',
        'MD-S011-R0048'=>'effective-date hierarchy: a detector break date does not overwrite the event ex-date',
        'MD-S011-R0061'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0062'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0063'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0064'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0065'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0066'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0067'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0068'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0069'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S011-R0070'=>'forbidden behaviour; proven by the continuity-diagnostic boundary guard',
        'MD-S011-R0071'=>'forbidden behaviour; proven by the continuity-diagnostic boundary guard',
        'MD-S011-R0072'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S079-R0129'=>'forbidden behaviour; wrapped sentence, predicate composed with its continuation',
        'MD-S079-R0130'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S079-R0131'=>'forbidden behaviour; wrapped sentence, predicate composed with R0132 to R0134',
        'MD-S079-R0135'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S080-R0046'=>'prohibited use, no REQUIRED row claimed it',
        'MD-S080-R0047'=>'prohibited use, no REQUIRED row claimed it',
        'MD-S080-R0048'=>'prohibited use, no REQUIRED row claimed it',
        'MD-S080-R0049'=>'prohibited use, no REQUIRED row claimed it',
        'MD-S080-R0050'=>'prohibited use, no REQUIRED row claimed it',
        'MD-S084-R0046'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S084-R0047'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S084-R0048'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S084-R0049'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S084-R0050'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S084-R0051'=>'forbidden behaviour, no REQUIRED row claimed it',
        'MD-S084-R0052'=>'forbidden behaviour, no REQUIRED row claimed it',
    ];
    public static function matrixPath(string $root): string { return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'; }
    public static function rows(string $root): array { $h=fopen(self::matrixPath($root),'r'); if(!$h) throw new RuntimeException('Cannot open traceability matrix.'); $headers=fgetcsv($h); $headers[0]=preg_replace('/^\\xEF\\xBB\\xBF/','',$headers[0]); $rows=[]; while(($r=fgetcsv($h))!==false){ if(count($r)===count($headers)) $rows[]=array_combine($headers,$r); } fclose($h); return $rows; }
    public static function mandatory(string $root): array { return array_values(array_filter(self::rows($root),static function($r){return ($r['active']??'')==='YES'&&($r['primary_stage']??'')===self::STAGE&&($r['coverage_requirement']??'')==='REQUIRED'&&($r['applicability']??'')==='MANDATORY';})); }
}
