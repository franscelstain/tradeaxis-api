<?php

/**
 * Governed `MD-B15` stage-entry traceability specification.
 *
 * `W15` is the temporal coverage expectation and delivery gate. Its four owner contracts —
 * `Coverage_Universe_Definition_LOCKED.md`, `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`,
 * `Coverage_Gate_Enforcement_Contract_LOCKED.md` and `Coverage_Edge_Cases_Contract_LOCKED.md` —
 * arrived with 89 rows still carrying the transitional `MANDATORY_OR_CONDITIONAL` value, 37
 * mixed-run siblings, and 94 reference rows nobody had recorded a decision on.
 *
 * The reference rows were the interesting part. Filed as context, they included the forbidden
 * expectation-exclusion list, the numerator exclusions, the three gate-state definitions, the
 * reason-code mapping, the required audit-visible fields and the forbidden fallback targets —
 * every one an obligation a test can hold the platform to. What remains reference here is
 * document metadata, capability-boundary statements, and routing to another contract's owner.
 */
final class MarketDataCoverageGateTraceabilitySpec
{
    public const STAGE = 'MD-B15';

    public const ATTEMPT = 'MD-B15-A001';

    /** Rows that arrived transitional and resolve to an unconditional obligation. */
    public const ENTRY_MANDATORY = [
        'MD-S001-R0059' => 'Deterministic platform decisions now locked',
        'MD-S014-R0005' => '1. Core Rule',
        'MD-S014-R0006' => '1. Core Rule',
        'MD-S014-R0008' => '2. Multi-Source Rule',
        'MD-S014-R0019' => '3. Partial Dataset Rule',
        'MD-S014-R0020' => '3. Partial Dataset Rule',
        'MD-S014-R0022' => '3. Partial Dataset Rule',
        'MD-S014-R0025' => '4. Delayed Data Rule',
        'MD-S014-R0028' => '4. Delayed Data Rule',
        'MD-S014-R0029' => '4. Delayed Data Rule',
        'MD-S014-R0032' => '5. Retry Window Rule',
        'MD-S014-R0033' => '5. Retry Window Rule',
        'MD-S014-R0034' => '5. Retry Window Rule',
        'MD-S014-R0035' => '5. Retry Window Rule',
        'MD-S014-R0036' => '5. Retry Window Rule',
        'MD-S014-R0038' => '5. Retry Window Rule',
        'MD-S014-R0039' => '5. Retry Window Rule',
        'MD-S014-R0040' => '5. Retry Window Rule',
        'MD-S014-R0042' => '6. Stale Data Rule',
        'MD-S014-R0043' => '6. Stale Data Rule',
        'MD-S014-R0044' => '6. Stale Data Rule',
        'MD-S014-R0045' => '6. Stale Data Rule',
        'MD-S014-R0056' => '7. Reason Code Contract',
        'MD-S014-R0087' => '12. Dormancy, zero-volume, and provider-outage correction (LOCKED)',
        'MD-S014-R0088' => '12. Dormancy, zero-volume, and provider-outage correction (LOCKED)',
        'MD-S014-R0094' => 'Capability boundary (LOCKED)',
        'MD-S015-R0004' => 'Purpose',
        'MD-S015-R0005' => 'Coverage Inputs',
        'MD-S015-R0034' => 'Coverage Inputs',
        'MD-S015-R0039' => 'Calculation Rules',
        'MD-S015-R0040' => 'Calculation Rules',
        'MD-S015-R0045' => 'Calculation Rules',
        'MD-S015-R0050' => 'Gate Status',
        'MD-S015-R0054' => 'Finalize Enforcement',
        'MD-S015-R0056' => 'Finalize Enforcement',
        'MD-S015-R0057' => 'Finalize Enforcement',
        'MD-S015-R0058' => 'Finalize Enforcement',
        'MD-S015-R0060' => 'Publishability Enforcement',
        'MD-S015-R0061' => 'Publishability Enforcement',
        'MD-S015-R0062' => 'Publishability Enforcement',
        'MD-S015-R0064' => 'Pointer Enforcement',
        'MD-S015-R0066' => 'Evidence Enforcement',
        'MD-S015-R0067' => 'Evidence Enforcement',
        'MD-S015-R0068' => 'Evidence Enforcement',
        'MD-S015-R0069' => 'Evidence Enforcement',
        'MD-S015-R0070' => 'Evidence Enforcement',
        'MD-S015-R0071' => 'Evidence Enforcement',
        'MD-S015-R0072' => 'Evidence Enforcement',
        'MD-S015-R0073' => 'Evidence Enforcement',
        'MD-S015-R0074' => 'Replay Enforcement',
        'MD-S015-R0099' => 'Command Enforcement',
        'MD-S015-R0107' => 'Anti-Bypass Rules',
        'MD-S015-R0113' => 'Capability boundary (LOCKED)',
        'MD-S016-R0008' => 'Bar expectation states',
        'MD-S016-R0022' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0024' => 'Counts and evidence',
        'MD-S016-R0025' => 'Counts and evidence',
        'MD-S016-R0026' => 'Counts and evidence',
        'MD-S016-R0027' => 'Counts and evidence',
        'MD-S016-R0028' => 'Counts and evidence',
        'MD-S016-R0029' => 'Counts and evidence',
        'MD-S016-R0031' => 'Acceptance criterion (LOCKED)',
        'MD-S016-R0042' => 'Capability boundary (LOCKED)',
        'MD-S024-R0002' => 'Purpose',
        'MD-S024-R0009' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0011' => 'Denominator (LOCKED)',
        'MD-S024-R0012' => 'Denominator (LOCKED)',
        'MD-S024-R0013' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0020' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0021' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0024' => 'Formula and threshold',
        'MD-S024-R0028' => 'Formula and threshold',
        'MD-S024-R0029' => 'Formula and threshold',
        'MD-S024-R0033' => 'Date-driven and source behavior',
        'MD-S024-R0041' => 'Promote/readability rule',
        'MD-S024-R0047' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0050' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0051' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0055' => 'Denominator exclusion path (LOCKED)',
        'MD-S024-R0063' => 'Consequences (LOCKED)',
        'MD-S024-R0064' => 'Consequences (LOCKED)',
        'MD-S024-R0065' => 'Consequences (LOCKED)',
    ];

    /** Mixed-run siblings and undecided reference rows that carry an executable obligation. A coverage rule filed as context is still a coverage rule. */
    public const ENTRY_PROMOTED = [
        'MD-S014-R0007' => '2. Multi-Source Rule',
        'MD-S014-R0010' => '2. Multi-Source Rule',
        'MD-S014-R0011' => '2. Multi-Source Rule',
        'MD-S014-R0012' => '2. Multi-Source Rule',
        'MD-S014-R0013' => '2. Multi-Source Rule',
        'MD-S014-R0017' => '3. Partial Dataset Rule',
        'MD-S014-R0018' => '3. Partial Dataset Rule',
        'MD-S014-R0021' => '3. Partial Dataset Rule',
        'MD-S014-R0023' => '4. Delayed Data Rule',
        'MD-S014-R0026' => '4. Delayed Data Rule',
        'MD-S014-R0027' => '4. Delayed Data Rule',
        'MD-S014-R0030' => '5. Retry Window Rule',
        'MD-S014-R0041' => '6. Stale Data Rule',
        'MD-S014-R0068' => '9. Fallback Rule',
        'MD-S014-R0070' => '9. Fallback Rule',
        'MD-S014-R0072' => '9. Fallback Rule',
        'MD-S014-R0073' => '9. Fallback Rule',
        'MD-S014-R0074' => '9. Fallback Rule',
        'MD-S014-R0075' => '9. Fallback Rule',
        'MD-S014-R0076' => '9. Fallback Rule',
        'MD-S014-R0077' => '9. Fallback Rule',
        'MD-S014-R0079' => '10. Implementation Mapping',
        'MD-S014-R0080' => '10. Implementation Mapping',
        'MD-S014-R0081' => '10. Implementation Mapping',
        'MD-S014-R0082' => '10. Implementation Mapping',
        'MD-S014-R0083' => '10. Implementation Mapping',
        'MD-S014-R0084' => '10. Implementation Mapping',
        'MD-S014-R0085' => '11. Non-Negotiable Invariant',
        'MD-S014-R0086' => '12. Dormancy, zero-volume, and provider-outage correction (LOCKED)',
        'MD-S015-R0008' => 'Coverage Inputs',
        'MD-S015-R0011' => 'Coverage Inputs',
        'MD-S015-R0013' => 'Coverage Inputs',
        'MD-S015-R0015' => 'Coverage Inputs',
        'MD-S015-R0017' => 'Coverage Inputs',
        'MD-S015-R0021' => 'Coverage Inputs',
        'MD-S015-R0022' => 'Coverage Inputs',
        'MD-S015-R0023' => 'Coverage Inputs',
        'MD-S015-R0026' => 'Coverage Inputs',
        'MD-S015-R0029' => 'Coverage Inputs',
        'MD-S015-R0036' => 'Calculation Rules',
        'MD-S015-R0037' => 'Calculation Rules',
        'MD-S015-R0041' => 'Calculation Rules',
        'MD-S015-R0043' => 'Calculation Rules',
        'MD-S015-R0047' => 'Gate Status',
        'MD-S015-R0048' => 'Gate Status',
        'MD-S015-R0049' => 'Gate Status',
        'MD-S015-R0051' => 'Reason Code Mapping',
        'MD-S015-R0052' => 'Reason Code Mapping',
        'MD-S015-R0053' => 'Reason Code Mapping',
        'MD-S015-R0055' => 'Finalize Enforcement',
        'MD-S015-R0059' => 'Publishability Enforcement',
        'MD-S015-R0063' => 'Pointer Enforcement',
        'MD-S015-R0091' => 'Replay Enforcement',
        'MD-S015-R0101' => 'Anti-Bypass Rules',
        'MD-S015-R0102' => 'Anti-Bypass Rules',
        'MD-S015-R0103' => 'Anti-Bypass Rules',
        'MD-S015-R0104' => 'Anti-Bypass Rules',
        'MD-S015-R0105' => 'Anti-Bypass Rules',
        'MD-S015-R0106' => 'Anti-Bypass Rules',
        'MD-S016-R0002' => 'Temporal universe (LOCKED)',
        'MD-S016-R0003' => 'Temporal universe (LOCKED)',
        'MD-S016-R0005' => 'Bar expectation states',
        'MD-S016-R0006' => 'Bar expectation states',
        'MD-S016-R0007' => 'Bar expectation states',
        'MD-S016-R0010' => 'Valid `NOT_EXPECTED` evidence',
        'MD-S016-R0011' => 'Valid `NOT_EXPECTED` evidence',
        'MD-S016-R0012' => 'Valid `NOT_EXPECTED` evidence',
        'MD-S016-R0013' => 'Valid `NOT_EXPECTED` evidence',
        'MD-S016-R0015' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0016' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0017' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0018' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0019' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0020' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0021' => 'Forbidden expectation exclusions (LOCKED)',
        'MD-S016-R0030' => 'Re-entry behavior',
        'MD-S024-R0003' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0004' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0005' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0006' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0007' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0008' => 'Separate dimensions (LOCKED)',
        'MD-S024-R0015' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0016' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0017' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0018' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0019' => 'Numerator and delivery classification (LOCKED)',
        'MD-S024-R0025' => 'Formula and threshold',
        'MD-S024-R0026' => 'Formula and threshold',
        'MD-S024-R0027' => 'Formula and threshold',
        'MD-S024-R0031' => 'Gate states',
        'MD-S024-R0032' => 'Date-driven and source behavior',
        'MD-S024-R0034' => 'Required audit-visible fields',
        'MD-S024-R0035' => 'Required audit-visible fields',
        'MD-S024-R0036' => 'Required audit-visible fields',
        'MD-S024-R0037' => 'Required audit-visible fields',
        'MD-S024-R0038' => 'Required audit-visible fields',
        'MD-S024-R0039' => 'Required audit-visible fields',
        'MD-S024-R0040' => 'Required audit-visible fields',
        'MD-S024-R0045' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0046' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0049' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0052' => 'Evidence validity boundary (LOCKED)',
        'MD-S024-R0053' => 'Denominator exclusion path (LOCKED)',
        'MD-S024-R0054' => 'Denominator exclusion path (LOCKED)',
        'MD-S024-R0056' => 'Denominator exclusion path (LOCKED)',
        'MD-S024-R0057' => 'Denominator exclusion path (LOCKED)',
        'MD-S024-R0067' => 'Acceptance criterion (LOCKED)',
    ];

    /** Rows with no executable obligation, each with the reason it carries none. */
    public const ENTRY_REFERENCE = [
        'MD-S014-R0001' => 'document status header, not a platform obligation',
        'MD-S014-R0002' => 'document owner header, not a platform obligation',
        'MD-S014-R0003' => 'document scope header naming the sections that follow',
        'MD-S014-R0004' => 'section heading assertion; the operative rules are MD-S014-R0005 and R0006',
        'MD-S014-R0015' => 'definitional restatement; the operative partial-data rules follow it',
        'MD-S014-R0089' => 'capability summary of what edge-case handling proves; the obligations it summarises are held by their own rules',
        'MD-S014-R0091' => 'capability disclaimer: the rule sorts observations rather than diagnosing the market',
        'MD-S014-R0092' => 'capability disclaimer: the enumerated edge-case set may be incomplete',
        'MD-S014-R0093' => 'capability disclaimer: rarity of a branch is not evidence it works',
        'MD-S015-R0001' => 'document status header, not a platform obligation',
        'MD-S015-R0002' => 'document owner header, not a platform obligation',
        'MD-S015-R0003' => 'document revision date header, not a platform obligation',
        'MD-S015-R0108' => 'capability summary of what gate enforcement proves; the obligations it summarises are held by their own rules',
        'MD-S015-R0110' => 'capability disclaimer: a passing ratio says nothing about value correctness',
        'MD-S015-R0111' => 'capability disclaimer: a configured threshold expresses tolerance, not soundness',
        'MD-S015-R0112' => 'capability disclaimer: the gate computes a precise ratio of possibly wrong counts',
        'MD-S016-R0001' => 'purpose statement declaring what the document defines',
        'MD-S016-R0032' => 'ownership boundary routing the delivery formula to EOD_COVERAGE_GATE_CONTRACT_LOCKED.md',
        'MD-S016-R0037' => 'capability summary of what universe definition proves; the obligations it summarises are held by their own rules',
        'MD-S016-R0039' => 'capability disclaimer: a listing the identity master never recorded is absent rather than excluded',
        'MD-S016-R0040' => 'capability disclaimer: the definition applies whichever basis is configured',
        'MD-S016-R0041' => 'capability disclaimer: equal universe counts on two dates are not a stable universe',
        'MD-S024-R0001' => 'purpose statement declaring what the document defines',
        'MD-S024-R0042' => 'explanatory statement of what coverage_contract_version does not record; the obligation is MD-S024-R0045 and R0046',
        'MD-S024-R0043' => 'consequence narrative; the admissibility obligation is MD-S024-R0049',
        'MD-S024-R0058' => 'capability summary of what the gate proves; the obligations it summarises are held by their own rules',
        'MD-S024-R0060' => 'capability disclaimer: coverage counts observations, not prices',
        'MD-S024-R0061' => 'capability disclaimer: coverage is self-consistent under a wrong calendar',
        'MD-S024-R0062' => 'capability disclaimer: the gate answers per-listing delivery, not session completeness',
        'MD-S024-R0066' => 'consequence narrative on the cost of a silently absent session at a five-day horizon',
    ];

    public static function matrixPath(string $root): string
    {
        return $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    }

    /** @return array<int,array<string,string>> */
    public static function rows(string $root): array
    {
        $handle = fopen(self::matrixPath($root), 'r');
        if ($handle === false) {
            throw new RuntimeException('Cannot open traceability matrix.');
        }
        $headers = fgetcsv($handle);
        $headers[0] = str_replace(chr(0xEF).chr(0xBB).chr(0xBF), '', $headers[0]);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) === count($headers)) {
                $rows[] = array_combine($headers, $values);
            }
        }
        fclose($handle);

        return $rows;
    }

    /** @return array<int,array<string,string>> */
    public static function stageRows(string $root): array
    {
        return array_values(array_filter(self::rows($root), static function ($r) {
            return ($r['active'] ?? '') === 'YES' && ($r['primary_stage'] ?? '') === self::STAGE;
        }));
    }

    /** @return array<int,array<string,string>> */
    public static function mandatory(string $root): array
    {
        return array_values(array_filter(self::stageRows($root), static function ($r) {
            return ($r['coverage_requirement'] ?? '') === 'REQUIRED'
                && ($r['applicability'] ?? '') === 'MANDATORY';
        }));
    }
}
