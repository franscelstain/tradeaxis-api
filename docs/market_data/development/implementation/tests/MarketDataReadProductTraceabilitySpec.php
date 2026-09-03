<?php

/**
 * Governed `MD-B17` stage-entry traceability specification.
 *
 * `W17` is the atomic versioned market-data read product and the freshness/readiness gateway.
 * Eight owner contracts govern it, from run status and quality gates through the consumer read
 * model, readiness guarantee, decision table, effective-date rules and read-side anti-bypass.
 *
 * The stage arrived with 102 transitional rows, 41 mixed-run siblings, 99 undecided reference
 * rows and 2 conditional-pending alias-retirement rows. What stays reference is document
 * metadata, purpose and scope text, and capability-boundary statements. Citation prohibitions
 * are not reference: a rule forbidding a claim is testable against every surface that could
 * make it.
 */
final class MarketDataReadProductTraceabilitySpec
{
    public const STAGE = 'MD-B17';

    public const ATTEMPT = 'MD-B17-A002';

    /** Rows that arrived transitional and resolve to an unconditional obligation. */
    public const ENTRY_MANDATORY = [
        'MD-S001-R0065' => 'Deterministic platform decisions now locked',
        'MD-S001-R0066' => 'Deterministic platform decisions now locked',
        'MD-S001-R0090' => 'Consumer safety summary (LOCKED)',
        'MD-S001-R0091' => 'Consumer safety summary (LOCKED)',
        'MD-S001-R0092' => 'Consumer safety summary (LOCKED)',
        'MD-S001-R0094' => 'Consumer safety summary (LOCKED)',
        'MD-S001-R0095' => 'Consumer safety summary (LOCKED)',
        'MD-S001-R0096' => 'Consumer safety summary (LOCKED)',
        'MD-S001-R0097' => 'Consumer safety summary (LOCKED)',
        'MD-S006-R0005' => 'Resolution order',
        'MD-S006-R0006' => 'Resolution order',
        'MD-S006-R0007' => 'Resolution order',
        'MD-S006-R0008' => 'Resolution order',
        'MD-S006-R0009' => 'Resolution order',
        'MD-S006-R0010' => 'Resolution order',
        'MD-S006-R0011' => 'Resolution order',
        'MD-S006-R0014' => 'Corrections and repeatability',
        'MD-S006-R0015' => 'Corrections and repeatability',
        'MD-S006-R0022' => 'Forbidden behavior',
        'MD-S006-R0031' => 'Capability boundary (LOCKED)',
        'MD-S009-R0002' => 'Decision table',
        'MD-S009-R0010' => 'Decision table',
        'MD-S009-R0011' => 'Decision table',
        'MD-S009-R0012' => 'Required interpretation',
        'MD-S009-R0015' => 'Correction concurrency',
        'MD-S009-R0022' => 'Capability boundary (LOCKED)',
        'MD-S021-R0005' => 'Product grain and identity',
        'MD-S021-R0009' => 'Product grain and identity',
        'MD-S021-R0014' => 'Analytical price product',
        'MD-S021-R0031' => 'Read surface',
        'MD-S021-R0032' => 'Read surface',
        'MD-S021-R0034' => 'Forbidden shortcuts',
        'MD-S021-R0035' => 'Forbidden shortcuts',
        'MD-S021-R0036' => 'Forbidden shortcuts',
        'MD-S021-R0037' => 'Forbidden shortcuts',
        'MD-S021-R0038' => 'Forbidden shortcuts',
        'MD-S021-R0039' => 'Forbidden shortcuts',
        'MD-S021-R0040' => 'Forbidden shortcuts',
        'MD-S021-R0042' => 'Versioning and compatibility',
        'MD-S021-R0048' => 'Capability boundary (LOCKED)',
        'MD-S022-R0005' => 'Dates tracked independently',
        'MD-S022-R0008' => 'Readiness states',
        'MD-S022-R0021' => '`READABLE` conditions',
        'MD-S022-R0022' => '`READABLE` conditions',
        'MD-S022-R0023' => '`READABLE` conditions',
        'MD-S022-R0024' => '`READABLE` conditions',
        'MD-S022-R0025' => '`READABLE` conditions',
        'MD-S022-R0026' => '`READABLE` conditions',
        'MD-S022-R0027' => '`READABLE` conditions',
        'MD-S022-R0028' => '`READABLE` conditions',
        'MD-S022-R0031' => 'Fallback policy',
        'MD-S022-R0032' => 'Correction behavior',
        'MD-S022-R0033' => 'Acceptance proof',
        'MD-S022-R0039' => 'Capability boundary (LOCKED)',
        'MD-S030-R0003' => 'Date meanings',
        'MD-S030-R0004' => 'Date meanings',
        'MD-S030-R0009' => 'Rules',
        'MD-S030-R0013' => 'No-good-fallback rule',
        'MD-S049-R0002' => 'Authority',
        'MD-S049-R0005' => 'Pointer-only resolution',
        'MD-S049-R0006' => 'Forbidden Bypass Rule',
        'MD-S049-R0014' => 'Fail-Safe Rule',
        'MD-S049-R0022' => 'Capability boundary (LOCKED)',
        'MD-S051-R0023' => 'Publishability state',
        'MD-S051-R0025' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0026' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0027' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0028' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0029' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0030' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0031' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0032' => 'Promote preconditions for readable success (LOCKED)',
        'MD-S051-R0033' => 'HELD',
        'MD-S051-R0041' => 'FAILED',
        'MD-S051-R0042' => 'FAILED',
        'MD-S051-R0043' => 'FAILED',
        'MD-S051-R0044' => 'FAILED',
        'MD-S051-R0051' => 'Coverage interaction (LOCKED)',
        'MD-S051-R0056' => 'Source-blocker interpretation after split',
        'MD-S051-R0058' => 'Required audit-visible final fields',
        'MD-S051-R0059' => 'Required audit-visible final fields',
        'MD-S051-R0060' => 'Required audit-visible final fields',
        'MD-S051-R0061' => 'Required audit-visible final fields',
        'MD-S051-R0062' => 'Required audit-visible final fields',
        'MD-S051-R0063' => 'Required audit-visible final fields',
        'MD-S051-R0064' => 'Required audit-visible final fields',
        'MD-S051-R0070' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0072' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0080' => 'Capability boundary (LOCKED)',
        'MD-S051-R0082' => 'Anti-ambiguity rules',
        'MD-S051-R0083' => 'Anti-ambiguity rules',
        'MD-S051-R0084' => 'Anti-ambiguity rules',
        'MD-S051-R0085' => 'Anti-ambiguity rules',
        'MD-S051-R0087' => 'Cross-contract alignment',
        'MD-S051-R0088' => 'Cross-contract alignment',
        'MD-S051-R0089' => 'Cross-contract alignment',
        'MD-S051-R0090' => 'Cross-contract alignment',
    ];

    /** Mixed-run siblings and undecided reference rows that carry an executable obligation. A coverage rule filed as context is still a coverage rule. */
    public const ENTRY_PROMOTED = [
        'MD-S006-R0001' => 'Allowed input surface',
        'MD-S006-R0002' => 'Allowed input surface',
        'MD-S006-R0012' => 'Response invariant',
        'MD-S006-R0013' => 'Response invariant',
        'MD-S006-R0016' => 'Forbidden behavior',
        'MD-S006-R0017' => 'Forbidden behavior',
        'MD-S006-R0018' => 'Forbidden behavior',
        'MD-S006-R0019' => 'Forbidden behavior',
        'MD-S006-R0020' => 'Forbidden behavior',
        'MD-S006-R0021' => 'Forbidden behavior',
        'MD-S006-R0023' => 'Forbidden behavior',
        'MD-S006-R0024' => 'Enforcement',
        'MD-S006-R0025' => 'Enforcement',
        'MD-S009-R0013' => 'Required interpretation',
        'MD-S009-R0014' => 'Required interpretation',
        'MD-S009-R0016' => 'Fail-safe default',
        'MD-S021-R0001' => 'Purpose',
        'MD-S021-R0004' => 'Product grain and identity',
        'MD-S021-R0006' => 'Product grain and identity',
        'MD-S021-R0007' => 'Product grain and identity',
        'MD-S021-R0008' => 'Product grain and identity',
        'MD-S021-R0010' => 'Canonical market facts',
        'MD-S021-R0011' => 'Canonical market facts',
        'MD-S021-R0012' => 'Canonical market facts',
        'MD-S021-R0013' => 'Canonical market facts',
        'MD-S021-R0015' => 'Analytical price product',
        'MD-S021-R0016' => 'Analytical price product',
        'MD-S021-R0017' => 'Indicators and daily context',
        'MD-S021-R0018' => 'Indicators and daily context',
        'MD-S021-R0019' => 'Indicators and daily context',
        'MD-S021-R0020' => 'Indicators and daily context',
        'MD-S021-R0021' => 'Indicators and daily context',
        'MD-S021-R0022' => 'Eligibility facts',
        'MD-S021-R0023' => 'Eligibility facts',
        'MD-S021-R0024' => 'Eligibility facts',
        'MD-S021-R0025' => 'Eligibility facts',
        'MD-S021-R0026' => 'Readiness and freshness',
        'MD-S021-R0027' => 'Readiness and freshness',
        'MD-S021-R0028' => 'Readiness and freshness',
        'MD-S021-R0029' => 'Readiness and freshness',
        'MD-S021-R0030' => 'Readiness and freshness',
        'MD-S021-R0041' => 'Versioning and compatibility',
        'MD-S022-R0001' => 'Guarantee',
        'MD-S022-R0002' => 'Dates tracked independently',
        'MD-S022-R0003' => 'Dates tracked independently',
        'MD-S022-R0004' => 'Dates tracked independently',
        'MD-S022-R0006' => 'Dates tracked independently',
        'MD-S022-R0007' => 'Dates tracked independently',
        'MD-S022-R0009' => 'Readiness states',
        'MD-S022-R0010' => 'Readiness states',
        'MD-S022-R0011' => 'Readiness states',
        'MD-S022-R0012' => 'Readiness states',
        'MD-S022-R0013' => 'Readiness states',
        'MD-S022-R0014' => 'Readiness states',
        'MD-S022-R0015' => 'Freshness states',
        'MD-S022-R0016' => 'Freshness states',
        'MD-S022-R0017' => 'Freshness states',
        'MD-S022-R0018' => 'Freshness states',
        'MD-S022-R0019' => 'Freshness states',
        'MD-S022-R0029' => '`READABLE` conditions',
        'MD-S022-R0030' => 'Fallback policy',
        'MD-S030-R0001' => 'Date meanings',
        'MD-S030-R0002' => 'Date meanings',
        'MD-S030-R0005' => 'Rules',
        'MD-S030-R0006' => 'Rules',
        'MD-S030-R0007' => 'Rules',
        'MD-S030-R0008' => 'Rules',
        'MD-S030-R0010' => 'Rules',
        'MD-S030-R0011' => 'Rules',
        'MD-S030-R0012' => 'Cache and audit invariant',
        'MD-S049-R0003' => 'Pointer-only resolution',
        'MD-S049-R0004' => 'Pointer-only resolution',
        'MD-S049-R0007' => 'Defense in depth',
        'MD-S049-R0008' => 'Defense in depth',
        'MD-S049-R0009' => 'Defense in depth',
        'MD-S049-R0010' => 'Defense in depth',
        'MD-S049-R0011' => 'Defense in depth',
        'MD-S049-R0012' => 'Audit access',
        'MD-S049-R0013' => 'Failure behavior',
        'MD-S049-R0015' => 'Acceptance evidence',
        'MD-S049-R0016' => 'Acceptance evidence',
        'MD-S051-R0004' => 'Import phase',
        'MD-S051-R0005' => 'Import phase',
        'MD-S051-R0006' => 'Import phase',
        'MD-S051-R0007' => 'Import phase',
        'MD-S051-R0008' => 'Import phase',
        'MD-S051-R0009' => 'Import phase',
        'MD-S051-R0010' => 'Import phase',
        'MD-S051-R0012' => 'Promote phase',
        'MD-S051-R0013' => 'Promote phase',
        'MD-S051-R0014' => 'Promote phase',
        'MD-S051-R0015' => 'Promote phase',
        'MD-S051-R0016' => 'Promote phase',
        'MD-S051-R0017' => 'Promote phase',
        'MD-S051-R0019' => 'Allowed terminal status',
        'MD-S051-R0035' => 'HELD',
        'MD-S051-R0036' => 'HELD',
        'MD-S051-R0037' => 'HELD',
        'MD-S051-R0038' => 'HELD',
        'MD-S051-R0039' => 'HELD',
        'MD-S051-R0045' => 'FAILED',
        'MD-S051-R0046' => 'Coverage interaction (LOCKED)',
        'MD-S051-R0047' => 'Coverage interaction (LOCKED)',
        'MD-S051-R0048' => 'Coverage interaction (LOCKED)',
        'MD-S051-R0049' => 'Coverage interaction (LOCKED)',
        'MD-S051-R0050' => 'Coverage interaction (LOCKED)',
        'MD-S051-R0052' => 'Source-blocker interpretation after split',
        'MD-S051-R0053' => 'Source-blocker interpretation after split',
        'MD-S051-R0054' => 'Source-blocker interpretation after split',
        'MD-S051-R0055' => 'Source-blocker interpretation after split',
        'MD-S051-R0065' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0067' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0068' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0069' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0071' => 'Date-level anomaly checks (LOCKED)',
        'MD-S051-R0073' => 'Date-level anomaly checks (LOCKED)',
    ];

    /** Rows with no executable obligation, each with the reason it carries none. */
    public const ENTRY_REFERENCE = [
        'MD-S006-R0026' => 'capability summary of what the mechanism proves; the obligations it summarises are held by their own rules: What the read contract proves. That a consumer receives data resolved from one sealed publication, w',
        'MD-S006-R0028' => 'capability disclaimer of what the mechanism cannot prove: - That the delivered content is correct. The contract governs how data is handed over, not how it wa',
        'MD-S006-R0029' => 'capability disclaimer of what the mechanism cannot prove: - That the effective date is the requested date. A conforming response may carry a prior sealed date',
        'MD-S006-R0030' => 'capability disclaimer of what the mechanism cannot prove: - That a consumer will read the fields it is given. Stating basis, effective date, and readiness sat',
        'MD-S009-R0017' => 'capability summary of what the mechanism proves; the obligations it summarises are held by their own rules: What the decision table proves. That every enumerated combination of coverage, seal, pointer, and fr',
        'MD-S009-R0019' => 'capability disclaimer of what the mechanism cannot prove: - That the enumeration is complete. A table decides the combinations someone listed. A state combina',
        'MD-S009-R0020' => 'capability disclaimer of what the mechanism cannot prove: - That the inputs it consumed were right. The table is a pure function of states supplied to it. Wro',
        'MD-S009-R0021' => 'capability disclaimer of what the mechanism cannot prove: - That a rarely reached row works. Determinism is a property of the mapping, not evidence that every',
        'MD-S021-R0002' => 'scope boundary statement declaring what the contract does not own',
        'MD-S021-R0043' => 'capability summary of what the mechanism proves; the obligations it summarises are held by their own rules: What the read model proves. That a versioned, stable field set is delivered with declared units, bas',
        'MD-S021-R0045' => 'capability disclaimer of what the mechanism cannot prove: - That a present field is a meaningful one. Field presence is a schema property. A liquidity proxy,',
        'MD-S021-R0046' => 'capability disclaimer of what the mechanism cannot prove: - That the field set is sufficient for the consumer\'s decision. The model delivers what market-data',
        'MD-S021-R0047' => 'capability disclaimer of what the mechanism cannot prove: - That version stability implies semantic stability. A field whose upstream meaning shifted while it',
        'MD-S022-R0034' => 'capability summary of what the mechanism proves; the obligations it summarises are held by their own rules: What the readiness guarantee proves. That a readiness state is derived from publication and freshnes',
        'MD-S022-R0036' => 'capability disclaimer of what the mechanism cannot prove: - That ready means right. Readiness is a statement about publication resolution and freshness. It ca',
        'MD-S022-R0037' => 'capability disclaimer of what the mechanism cannot prove: - That fresh means current in market terms. Freshness measures the platform\'s knowledge against the',
        'MD-S022-R0038' => 'capability disclaimer of what the mechanism cannot prove: - That readiness reflects consumer need. The horizon a consumer serves determines how much lateness',
        'MD-S049-R0001' => 'document status header: Status: LOCKED at strategy level; implementation and production relock remain unproven.',
        'MD-S049-R0017' => 'capability summary of what the mechanism proves; the obligations it summarises are held by their own rules: What read-side enforcement proves. That governed read paths resolve through publication context, rej',
        'MD-S049-R0019' => 'capability disclaimer of what the mechanism cannot prove: - That no bypass occurred. Enforcement lives in application code. A consumer with direct database ac',
        'MD-S049-R0020' => 'capability disclaimer of what the mechanism cannot prove: - That the absence of violations means the rules were exercised. No consumer surface is currently ex',
        'MD-S049-R0021' => 'capability disclaimer of what the mechanism cannot prove: - That a governed path is a correct path. Enforcement checks how data is reached, not whether the re',
        'MD-S051-R0001' => 'purpose statement declaring what the document locks',
        'MD-S051-R0002' => 'purpose statement naming the architecture the document follows',
        'MD-S051-R0074' => 'capability summary of what the mechanism proves; the obligations it summarises are held by their own rules: What the quality gates prove. That each declared gate was evaluated for the run, that its outcome an',
        'MD-S051-R0076' => 'capability disclaimer of what the mechanism cannot prove: - That the data is correct. Every gate here tests a declared property. A defect with no declared gat',
        'MD-S051-R0077' => 'capability disclaimer of what the mechanism cannot prove: - That a defect exists at all, when it is evenly spread. The date-level checks detect concentration',
        'MD-S051-R0078' => 'capability disclaimer of what the mechanism cannot prove: - That a passing threshold means a normal date. Thresholds are configured boundaries, not statements',
        'MD-S051-R0079' => 'capability disclaimer of what the mechanism cannot prove: - That the gate set is complete. Gates are added when a failure mode is known. Silence about an unkn',
    ];

    /**
     * The two alias-retirement conditions. Both contracts make retirement conditional on a
     * demonstration that no reader outside this package depends on the alias, and both say the
     * demonstration must be made rather than assumed. No consumer surface is exposed, so no
     * demonstration exists and the retirement obligation does not apply — which also means the
     * alias must stay. The guard named in the normalization holds both halves.
     */
    public const ENTRY_CONDITIONAL_NOT_APPLICABLE = [
        'MD-S020-R0069' => 'the `eligible` alias may be retired only once no consumer outside this package reads it, demonstrated rather than assumed; no consumer surface is exposed and no demonstration exists',
        'MD-S086-R0025' => 'the `dv20_idr` alias may be retired only once no reader outside this package depends on it, demonstrated rather than assumed through a versioned read-model change; no such change has been made',
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
