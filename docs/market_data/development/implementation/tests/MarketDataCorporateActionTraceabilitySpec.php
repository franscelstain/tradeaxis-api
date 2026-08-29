<?php
final class MarketDataCorporateActionTraceabilitySpec
{
    public const STAGE='MD-B11'; public const ATTEMPT='MD-B11-A001'; public const EXPECTED_DENOMINATOR=202; public const EXPECTED_REFERENCE=209;

    /**
     * MD-B11-A003 completes the reference population MD-B11-A002 left at 167 rows: 30 promotions
     * and 137 recorded reference decisions, which is what admits MD-B11 to
     * DECISION_RECORDED_STAGES.
     *
     * MD-S079 contributes 90 of the 137 and zero promotions. That is not convenience. The
     * document already carries 36 REQUIRED rows covering its operative obligations - the
     * unknown-type policy, the forbidden-behaviour list promoted in A002, the dictionary
     * contract, the lifecycle corrections. What remains is hard-wrapped narrative and enum
     * vocabulary whose sentences complete in those REQUIRED rows.
     */
    public const A003_ATTEMPT='MD-B11-A003';

    public const A003_EVIDENCE='E-MD-B11-A003-001';

    /** Rules MD-B11-A003 promoted. Guard coverage was verified for each before any promotion. */
    public const A003_REMEDIATED_RULES=[
        'MD-S010-R0017'=>'event-day/window flags are factual context, never a buy/sell, ranking or avoidance recommendation',
        'MD-S010-R0018'=>'an adjustment-active verified event may restore arithmetic continuity while remaining disclosed as event context',
        'MD-S010-R0019'=>'an unresolved event or break blocks affected eligibility/indicator validity under governed reasons',
        'MD-S010-R0020'=>'absence of an event row means no evidence, not proof of no risk',
        'MD-S010-R0025'=>'acceptance criterion: any blocked or released row is explainable by revision, anchor, factor state, window, reason and publication',
        'MD-S011-R0003'=>'core safety rule: a price discontinuity is evidence of a possible event, not proof of an action, type, ex-date or factor',
        'MD-S011-R0037'=>'GAP_AMBIGUOUS may be resolved by authoritative or manual verification of the event terms',
        'MD-S011-R0038'=>'GAP_AMBIGUOUS may be resolved by authoritative evidence that no adjusting event occurred at the anchor',
        'MD-S011-R0040'=>'GAP_AMBIGUOUS may not be resolved by the absence of a detected break; detection silence is not evidence',
        'MD-S011-R0041'=>'GAP_AMBIGUOUS may not be resolved by the gap being small, which is what made the verdict ambiguous',
        'MD-S011-R0042'=>'GAP_AMBIGUOUS may not be resolved by the passage of time, re-running the check, or absence of complaints',
        'MD-S011-R0055'=>'candidate-break linkage: a break with missing or conflicting verification stays quarantining and no command may mark it repaired because bars were rewritten',
        'MD-S080-R0010'=>'each band tier row carries effective start/end dates',
        'MD-S080-R0011'=>'upper and lower band limits are stored separately',
        'MD-S080-R0012'=>'each band row carries source reference and verification state under governed reference-data rules',
        'MD-S080-R0026'=>'the Regular-Market price floor requires stored value, effective dating and source reference',
        'MD-S080-R0028'=>'the tick/price fraction ladder is stored as a tiered, effective-dated table with source reference',
        'MD-S080-R0032'=>'a listing with missing or unrecognized point-in-time board identity is FAIL_CLOSED and a consumer may not silently inherit standard-board tiers',
        'MD-S080-R0045'=>'a consumer may cite these facts but may not own them, hardcode them, or hold a second copy that can drift',
        'MD-S080-R0061'=>'acceptance criterion: every band, floor or tick value used in a decision resolves from an effective-dated, source-referenced row, and no unsourced constant reaches a published output',
        'MD-S084-R0002'=>'detector-only boundary: the detector is not a repair engine, corporate-action authority, factor authority, or permission to mutate data',
        'MD-S084-R0004'=>'the detector may not establish corporate-action identity or type',
        'MD-S084-R0005'=>'the detector may not establish a verified ex-date or effective date',
        'MD-S084-R0006'=>'the detector may not establish whether source data is wrong versus legitimately as-traded',
        'MD-S084-R0007'=>'the detector may not establish an adjustment-active price or volume factor',
        'MD-S084-R0030'=>'candidate classifications remain diagnostic and separately store evidence',
        'MD-S084-R0031'=>'proximity, band exceedance, persistence, common-ratio tolerance or consensus cannot promote a candidate automatically',
        'MD-S084-R0036'=>'undismissed candidates contaminate every dependent analytical window and emit explicit quality/eligibility reasons',
        'MD-S084-R0042'=>'no-repair rule: the detector family may not update eod_bars, history, sealed snapshots, factor rows or verification in place',
        'MD-S084-R0045'=>'idempotency: identical input produces the same candidate identities without duplicate active candidates',
    ];

    /**
     * Section-level bases for the rows that stay reference. Rows inside one section share a
     * reason because they are the same kind of text - a wrapped sentence, an enum definition,
     * a capability disclaimer - so a per-row string would repeat rather than inform.
     */
    /**
     * Rows that stay REFERENCE_ONLY inside a run whose siblings are REQUIRED. The classification gate
     * allows this only when the stage records both the exception and an owner basis from its closed
     * list, which is the mechanism for semantic context owned by another stage or by a capability
     * boundary. Recorded here so the call is reproducible rather than a hand edit to the matrix.
     */
    public const A003_MIXED_RUN_EXCEPTIONS=[
        'MD-S011-R0057'=>'downstream_price_product',
        'MD-S011-R0058'=>'downstream_price_product',
        'MD-S011-R0059'=>'downstream_price_product',
        'MD-S011-R0076'=>'capability_limitation',
        'MD-S011-R0077'=>'capability_limitation',
        'MD-S011-R0078'=>'capability_limitation',
        'MD-S079-R0138'=>'capability_limitation',
        'MD-S079-R0140'=>'capability_limitation',
    ];

    public static function a003ReferenceBasis(string $document, string $section): string
    {
        $byDocument = [
            'MD-S079' => 'MD-S079 already carries 36 REQUIRED rows covering its operative obligations; this row is hard-wrapped narrative, enum vocabulary, or a sentence fragment whose predicate completes in one of them',
            'MD-S080' => 'implementation-state narrative, recorded interval values, or a wrapped continuation; the enforceable requirements of this contract are promoted in this attempt or already REQUIRED',
            'MD-S011' => 'purpose, ownership pointer, or narrative; the enforceable rules of this contract are promoted in MD-B11-A002 and this attempt',
            'MD-S010' => 'purpose or narrative context',
            'MD-S084' => 'purpose, the detection-sensitivity blind region, or a restatement of a prohibition owned elsewhere',
        ];
        $bySection = [
            'Capability boundary (LOCKED)' => 'capability boundary: a statement of what this contract cannot prove, which section 2 classifies as descriptive context',
            'Capability boundary - detection sensitivity (LOCKED)' => 'capability boundary: the detector blind region below the ratio floor',
            'Purpose' => 'purpose statement',
            'Silence is not evidence (LOCKED)' => 'explicitly mirrors a prohibition already owned by Price_Adjustment_Contract_LOCKED.md; promoting it would duplicate that closure obligation',
            'Product boundary' => 'product semantics owned by the REQUIRED MD-S083-R0002 to R0005; promoting these would duplicate that closure obligation',
        ];

        $basis = $bySection[$section] ?? $byDocument[$document] ?? 'descriptive context carrying no executable proof obligation';

        return $basis.' [document '.$document.', section '.$section.']';
    }

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
