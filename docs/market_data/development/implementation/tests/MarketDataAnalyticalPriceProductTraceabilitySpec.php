<?php
final class MarketDataAnalyticalPriceProductTraceabilitySpec
{
    public const STAGE='MD-B12';
    public const ATTEMPT='MD-B12-A001';
    public const EXPECTED_DENOMINATOR=60;

    /**
     * MD-B12-A002, first of two scoped re-check attempts. A002 takes the two blocks whose rows are
     * unambiguous prohibitions and eligibility conditions with no REQUIRED row anywhere claiming
     * them; A003 takes contamination behaviour, the version/change rule, and the remaining
     * provenance and formula rows.
     *
     * Neither block was reachable by MIXED_RUN: `Forbidden behavior (LOCKED)` and
     * `Eligibility for adjustment (LOCKED)` are uniformly REFERENCE_ONLY runs, so there was no
     * mixture to detect. They surfaced through UNEXPLAINED_REFERENCE, added in MD-B00-A004.
     */
    public const REMEDIATION_ATTEMPT='MD-B12-A002';

    public const REMEDIATION_EVIDENCE='E-MD-B12-A002-001';

    /** Rules MD-B12-A002 promoted, with the basis for each. */
    public const REMEDIATED_RULES=[
        'MD-S083-R0032'=>'a002_correction=promoted from REFERENCE_ONLY; adjustment-eligibility condition 1, the B12-side twin of MD-S011-R0023, implemented at AdjustmentFactorSetService but unguarded until this attempt',
        'MD-S083-R0033'=>'a002_correction=promoted from REFERENCE_ONLY; adjustment-eligibility condition 2',
        'MD-S083-R0034'=>'a002_correction=promoted from REFERENCE_ONLY; adjustment-eligibility condition 3',
        'MD-S083-R0035'=>'a002_correction=promoted from REFERENCE_ONLY; adjustment-eligibility condition 4',
        'MD-S083-R0036'=>'a002_correction=promoted from REFERENCE_ONLY; adjustment-eligibility condition 5',
        'MD-S083-R0037'=>'a002_correction=promoted from REFERENCE_ONLY; the exclusion half of the eligibility rule',
        'MD-S083-R0054'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0055'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0056'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0057'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0058'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0059'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0060'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0061'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
        'MD-S083-R0062'=>'a002_correction=promoted from REFERENCE_ONLY; forbidden behaviour, no REQUIRED row claimed it',
    ];
    public const EXPECTED_REFERENCE=63;
    public static function matrixPath(string $root): string { return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv'; }
    public static function rows(string $root): array {
        $h=fopen(self::matrixPath($root),'r'); if(!$h) throw new RuntimeException('Cannot open traceability matrix.');
        $headers=fgetcsv($h); $headers[0]=preg_replace('/^\xEF\xBB\xBF/','',$headers[0]); $rows=[];
        while(($r=fgetcsv($h))!==false){ if(count($r)===count($headers)) $rows[]=array_combine($headers,$r); }
        fclose($h); return $rows;
    }
    public static function stageRows(string $root): array { return array_values(array_filter(self::rows($root),static function($r){return ($r['active']??'')==='YES'&&($r['primary_stage']??'')===self::STAGE;})); }
    public static function mandatory(string $root): array { return array_values(array_filter(self::stageRows($root),static function($r){return ($r['coverage_requirement']??'')==='REQUIRED'&&($r['applicability']??'')==='MANDATORY';})); }
    public static function reference(string $root): array { return array_values(array_filter(self::stageRows($root),static function($r){return ($r['coverage_requirement']??'')==='REFERENCE_ONLY';})); }
}
