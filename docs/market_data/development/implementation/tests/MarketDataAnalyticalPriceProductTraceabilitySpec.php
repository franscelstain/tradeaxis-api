<?php
final class MarketDataAnalyticalPriceProductTraceabilitySpec
{
    public const STAGE='MD-B12';
    public const ATTEMPT='MD-B12-A001';
    public const EXPECTED_DENOMINATOR=75;

    /**
     * MD-B12-A003 completes the reference-population re-check MD-B12-A002 deliberately left partial.
     * A002 took the two unambiguous blocks; A003 takes the remaining 15 promotions and records the
     * decision on the 24 rows that stay reference, which is what admits MD-B12 to
     * DECISION_RECORDED_STAGES.
     */
    public const A003_ATTEMPT='MD-B12-A003';

    public const A003_EVIDENCE='E-MD-B12-A003-001';

    /** Rules MD-B12-A003 promoted. Guard coverage was verified for each before any promotion. */
    public const A003_REMEDIATED_RULES=[
        'MD-S012-R0030'=>'version/change rule: an output-affecting change requires a new product/formula/config version',
        'MD-S012-R0031'=>'version/change rule: an output-affecting change requires new hashes',
        'MD-S012-R0032'=>'version/change rule: recomputation of every affected date from the stable dependency boundary',
        'MD-S012-R0033'=>'version/change rule: new publication/correction lineage where prior output was sealed',
        'MD-S012-R0034'=>'version/change rule: long-chain deterministic oracle proof',
        'MD-S083-R0006'=>'adjustment_source is a closed vocabulary mirrored in EventRiskSourceRepository::ADJUSTMENT_SOURCES',
        'MD-S083-R0017'=>'a refused factor does not become harmless; its action still contaminates the window',
        'MD-S083-R0019'=>'DERIVED_FROM_PRICE_SERIES is a platform observation and the importer refuses it outright',
        'MD-S083-R0030'=>'factor rows are append-only and a factor used by a sealed publication cannot be mutated',
        'MD-S083-R0038'=>'the structural-adjustment formula: ordered product of verified factors over B < ex_date <= D',
        'MD-S083-R0048'=>'contamination behaviour: verified active factor computes a coherent product and discloses its revision',
        'MD-S083-R0049'=>'contamination behaviour: verified event with incomplete factor/date quarantines the affected range',
        'MD-S083-R0050'=>'contamination behaviour: unverified or synthetic candidate quarantines and does not adjust',
        'MD-S083-R0051'=>'contamination behaviour: conflicting factor revisions hold until one governed revision is selected',
        'MD-S083-R0052'=>'contamination behaviour: factor correction creates new analytical artifacts and publication lineage',
    ];

    /**
     * Rows MD-B12-A003 re-derived and confirmed non-executable, with why. Several read like strong
     * promotion candidates and are not: section 7 forbids duplicating a closure obligation a
     * REQUIRED row already owns.
     */
    public const A003_REFERENCE_DECISIONS=[
        'MD-S012-R0007'=>'the one-basis-per-run statement enumerates the binding components; each is owned as a REQUIRED row by MD-S012-R0011 to R0013',
        'MD-S012-R0009'=>'framing sentence for the persistence section; the stored-state obligation is owned by MD-S012-R0011 to R0017',
        'MD-S012-R0023'=>'coherence component; the obligation is owned by the REQUIRED MD-S083-R0039 and MD-S012-R0028',
        'MD-S012-R0024'=>'coherence component; true-range basis is owned by the REQUIRED MD-S012-R0028',
        'MD-S012-R0025'=>'coherence component; volume factor semantics are owned by the REQUIRED MD-S083-R0039 and R0040',
        'MD-S012-R0026'=>'coherence component; window consistency is owned by the REQUIRED MD-S012-R0028 and the A003 version/change recompute rule',
        'MD-S012-R0027'=>'coherence component; raw immutability is owned by the REQUIRED MD-S083-R0054 promoted in MD-B12-A002',
        'MD-S012-R0037'=>'capability boundary consequence: one basis per run guarantees no mixing, not completeness; descriptive context',
        'MD-S012-R0038'=>'acceptance criterion restating obligations owned by the REQUIRED MD-S083-R0069 and MD-S083-R0053',
        'MD-S012-R0039'=>'states that the criterion above is unmeasurable until the price-product identity exists as a field; measurability commentary',
        'MD-S083-R0001'=>'purpose statement',
        'MD-S083-R0014'=>'wrapped-sentence fragment; the obligation is owned by the REQUIRED MD-S083-R0015',
        'MD-S083-R0016'=>'wrapped-sentence fragment continuing MD-S083-R0015',
        'MD-S083-R0018'=>'wrapped-sentence fragment continuing the A003-promoted MD-S083-R0017',
        'MD-S083-R0020'=>'wrapped-sentence fragment continuing the A003-promoted MD-S083-R0019',
        'MD-S083-R0021'=>'wrapped-sentence fragment continuing the A003-promoted MD-S083-R0019',
        'MD-S083-R0043'=>'enumeration of what the price-break detector may emit; capability listing, not an obligation',
        'MD-S083-R0044'=>'enumeration of what the price-break detector may emit',
        'MD-S083-R0045'=>'enumeration of what the price-break detector may emit',
        'MD-S083-R0046'=>'enumeration of what the price-break detector may emit',
        'MD-S083-R0047'=>'capability limit: the detector cannot establish event type, verified ex-date, or an adjustment-active factor',
        'MD-S083-R0065'=>'capability boundary: what the adjustment product cannot prove',
        'MD-S083-R0066'=>'capability boundary: coherence is not correctness',
        'MD-S083-R0067'=>'capability boundary; explicitly a restatement of a prohibition already owned by the A002-promoted MD-S083-R0061',
    ];

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
    public const EXPECTED_REFERENCE=48;
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
