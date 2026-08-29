<?php

/**
 * MD-B14 stage-entry traceability specification.
 *
 * MD-B14 opens with 383 active rows, 73 of them still carrying the transitional
 * MANDATORY_OR_CONDITIONAL value that STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md
 * section 4 calls legacy. Section 8 requires every one of them resolved to an explicit
 * applicability class before the stage denominator can be treated as final.
 *
 * This file records that resolution so it is reproducible and reviewable rather than a hand
 * edit to the matrix, and so a later reader can challenge a specific call rather than the
 * whole pass.
 */
final class MarketDataIndicatorEngineTraceabilitySpec
{
    public const STAGE = 'MD-B14';

    public const ATTEMPT = 'MD-B14-A001';

    public const BASELINE = 'MD-B14-A001-BL001';

    public const CI = 'CI-MD-B14-A001-001';

    public const EVIDENCE = 'E-MD-B14-A001-001';

    /** Transitional rows resolved to MANDATORY, with the obligation class each belongs to. */
    public const ENTRY_MANDATORY = [
        'MD-S017-R0006' => 'recompute command boundary obligation',
        'MD-S017-R0037' => 'recompute command boundary obligation',
        'MD-S017-R0043' => 'recompute command boundary obligation',
        'MD-S017-R0044' => 'recompute command boundary obligation',
        'MD-S017-R0045' => 'recompute command boundary obligation',
        'MD-S017-R0048' => 'recompute command boundary obligation',
        'MD-S028-R0008' => 'indicator fact contract obligation',
        'MD-S028-R0025' => 'indicator fact contract obligation',
        'MD-S028-R0028' => 'indicator fact contract obligation',
        'MD-S028-R0029' => 'indicator fact contract obligation',
        'MD-S028-R0035' => 'indicator fact contract obligation',
        'MD-S037-R0004' => 'nullability and gap contract obligation',
        'MD-S037-R0010' => 'nullability and gap contract obligation',
        'MD-S037-R0011' => 'nullability and gap contract obligation',
        'MD-S037-R0012' => 'nullability and gap contract obligation',
        'MD-S038-R0007' => 'recompute source-scope obligation',
        'MD-S038-R0008' => 'recompute source-scope obligation',
        'MD-S038-R0009' => 'recompute source-scope obligation',
        'MD-S038-R0010' => 'recompute source-scope obligation',
        'MD-S038-R0011' => 'recompute source-scope obligation',
        'MD-S038-R0012' => 'recompute source-scope obligation',
        'MD-S038-R0013' => 'recompute source-scope obligation',
        'MD-S038-R0014' => 'recompute source-scope obligation',
        'MD-S038-R0026' => 'recompute source-scope obligation',
        'MD-S038-R0034' => 'recompute source-scope obligation',
        'MD-S060-R0003' => 'formula specification obligation',
        'MD-S060-R0011' => 'formula specification obligation',
        'MD-S060-R0013' => 'formula specification obligation',
        'MD-S060-R0020' => 'formula specification obligation',
        'MD-S060-R0029' => 'formula specification obligation',
        'MD-S060-R0030' => 'formula specification obligation',
        'MD-S060-R0032' => 'formula specification obligation',
        'MD-S060-R0060' => 'formula specification obligation',
        'MD-S060-R0061' => 'formula specification obligation',
        'MD-S060-R0069' => 'formula specification obligation',
        'MD-S061-R0006' => 'computation procedure obligation',
        'MD-S061-R0012' => 'computation procedure obligation',
        'MD-S061-R0013' => 'computation procedure obligation',
        'MD-S061-R0021' => 'computation procedure obligation',
        'MD-S061-R0022' => 'computation procedure obligation',
        'MD-S061-R0025' => 'computation procedure obligation',
        'MD-S061-R0030' => 'computation procedure obligation',
        'MD-S061-R0031' => 'computation procedure obligation',
        'MD-S081-R0003' => 'indicator registry baseline obligation',
        'MD-S081-R0013' => 'indicator registry baseline obligation',
        'MD-S081-R0015' => 'indicator registry baseline obligation',
        'MD-S081-R0026' => 'indicator registry baseline obligation',
        'MD-S081-R0028' => 'indicator registry baseline obligation',
        'MD-S081-R0034' => 'indicator registry baseline obligation',
        'MD-S081-R0044' => 'indicator registry baseline obligation',
        'MD-S081-R0045' => 'indicator registry baseline obligation',
        'MD-S081-R0049' => 'indicator registry baseline obligation',
        'MD-S081-R0050' => 'indicator registry baseline obligation',
        'MD-S081-R0051' => 'indicator registry baseline obligation',
        'MD-S081-R0052' => 'indicator registry baseline obligation',
        'MD-S081-R0053' => 'indicator registry baseline obligation',
        'MD-S081-R0054' => 'indicator registry baseline obligation',
        'MD-S081-R0057' => 'indicator registry baseline obligation',
        'MD-S081-R0064' => 'indicator registry baseline obligation',
    ];

    /**
     * Transitional rows resolved to REFERENCE_ONLY. Every one is a purpose statement, an
     * ownership pointer, a capability boundary, or a status marker - the classes section 2
     * names as non-executable.
     */
    public const ENTRY_REFERENCE = [
        'MD-S017-R0002' => 'V2 proof-boundary note recording which historical runtime proof remains valid evidence; a status marker, not an obligation',
        'MD-S028-R0001' => 'purpose statement',
        'MD-S037-R0014' => 'capability boundary: what nullability rules prove',
        'MD-S037-R0019' => 'capability boundary: the consequence clause stating what a populated row may never be cited as',
        'MD-S060-R0001' => 'purpose statement',
        'MD-S060-R0070' => 'capability boundary: what a formula proves',
        'MD-S060-R0074' => 'capability boundary: that a defined value is not necessarily a meaningful one',
        'MD-S060-R0075' => 'capability boundary: the consequence clause bounding what a formula result may be cited as',
        'MD-S061-R0038' => 'capability boundary: that the dependency set was complete cannot be proven by the procedure',
        'MD-S061-R0040' => 'capability boundary: the consequence clause bounding what a deterministic recomputation proves',
        'MD-S081-R0001' => 'purpose and boundary statement naming the intended first consumer',
        'MD-S081-R0002' => 'ownership pointer to the formula, orchestration and nullability owner contracts',
        'MD-S081-R0067' => 'status statement recording that the document is strategy-locked while the implementation is not production-relocked',
    ];

    /**
     * Transitional rows whose obligation exists only when an external condition is true.
     * Section 6 starts these at CONDITIONAL_PENDING; the condition is evaluated during the
     * attempt and resolved with evidence, never assumed false because it is convenient.
     */
    public const ENTRY_CONDITIONAL = [
        'MD-S038-R0028' => 'technical-only mode boundary; the contract states the mode does not currently exist as an accepted production command, so the obligation exists only if such a mode is introduced',
    ];

    /**
     * Enumerated-run siblings that stayed REFERENCE_ONLY while other members of the same run
     * became REQUIRED. The classification gate states the reasoning this resolution follows:
     * the matrix marking some members required is its own admission that the list carries
     * obligations. Each was read before promotion; all are required metadata, nullability
     * rules, bound inputs, computation-order steps, gap handling, contamination radius, or
     * acceptance-proof items.
     */
    public const ENTRY_MIXED_RUN_PROMOTIONS = [
        'MD-S017-R0005' => 'mixed-run sibling in Purpose; the run already carries REQUIRED members',
        'MD-S017-R0039' => 'mixed-run sibling in Nullability rule; the run already carries REQUIRED members',
        'MD-S017-R0040' => 'mixed-run sibling in Nullability rule; the run already carries REQUIRED members',
        'MD-S017-R0041' => 'mixed-run sibling in Nullability rule; the run already carries REQUIRED members',
        'MD-S017-R0042' => 'mixed-run sibling in Nullability rule; the run already carries REQUIRED members',
        'MD-S028-R0005' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0006' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0007' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0009' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0010' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0011' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0012' => 'mixed-run sibling in Required metadata; the run already carries REQUIRED members',
        'MD-S028-R0022' => 'mixed-run sibling in Nullability and row validity; the run already carries REQUIRED members',
        'MD-S028-R0023' => 'mixed-run sibling in Nullability and row validity; the run already carries REQUIRED members',
        'MD-S028-R0024' => 'mixed-run sibling in Nullability and row validity; the run already carries REQUIRED members',
        'MD-S028-R0026' => 'mixed-run sibling in Nullability and row validity; the run already carries REQUIRED members',
        'MD-S037-R0001' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S037-R0002' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S037-R0003' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S037-R0005' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S037-R0006' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S037-R0007' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S037-R0008' => 'mixed-run sibling in Core rules; the run already carries REQUIRED members',
        'MD-S060-R0004' => 'mixed-run sibling in Input identity (LOCKED); the run already carries REQUIRED members',
        'MD-S060-R0005' => 'mixed-run sibling in Input identity (LOCKED); the run already carries REQUIRED members',
        'MD-S060-R0006' => 'mixed-run sibling in Input identity (LOCKED); the run already carries REQUIRED members',
        'MD-S060-R0007' => 'mixed-run sibling in Input identity (LOCKED); the run already carries REQUIRED members',
        'MD-S060-R0010' => 'mixed-run sibling in Trading-day and precision rules; the run already carries REQUIRED members',
        'MD-S060-R0012' => 'mixed-run sibling in Trading-day and precision rules; the run already carries REQUIRED members',
        'MD-S060-R0014' => 'mixed-run sibling in Trading-day and precision rules; the run already carries REQUIRED members',
        'MD-S061-R0003' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0004' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0005' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0007' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0008' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0009' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0010' => 'mixed-run sibling in Bound inputs; the run already carries REQUIRED members',
        'MD-S061-R0014' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0015' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0016' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0017' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0018' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0019' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0020' => 'mixed-run sibling in Computation order (LOCKED); the run already carries REQUIRED members',
        'MD-S061-R0024' => 'mixed-run sibling in Gaps and invalid inputs; the run already carries REQUIRED members',
        'MD-S061-R0026' => 'mixed-run sibling in Gaps and invalid inputs; the run already carries REQUIRED members',
        'MD-S061-R0027' => 'mixed-run sibling in Gaps and invalid inputs; the run already carries REQUIRED members',
        'MD-S061-R0028' => 'mixed-run sibling in Gaps and invalid inputs; the run already carries REQUIRED members',
        'MD-S061-R0029' => 'mixed-run sibling in Gaps and invalid inputs; the run already carries REQUIRED members',
        'MD-S081-R0014' => 'mixed-run sibling in Volume and liquidity; the run already carries REQUIRED members',
        'MD-S081-R0035' => 'mixed-run sibling in Contamination radius (LOCKED); the run already carries REQUIRED members',
        'MD-S081-R0036' => 'mixed-run sibling in Contamination radius (LOCKED); the run already carries REQUIRED members',
        'MD-S081-R0037' => 'mixed-run sibling in Contamination radius (LOCKED); the run already carries REQUIRED members',
        'MD-S081-R0043' => 'mixed-run sibling in Consequences (LOCKED); the run already carries REQUIRED members',
        'MD-S081-R0059' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
        'MD-S081-R0060' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
        'MD-S081-R0061' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
        'MD-S081-R0062' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
        'MD-S081-R0063' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
        'MD-S081-R0065' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
        'MD-S081-R0066' => 'mixed-run sibling in Acceptance proof; the run already carries REQUIRED members',
    ];
    /**
     * The MD-B14 executable denominator established by the stage-entry normalization: 59 rows that
     * were already mandatory or transitional, 61 promoted mixed-run siblings, and the 27 rows the
     * matrix already carried as REQUIRED/MANDATORY before this stage opened.
     */
    public const EXPECTED_DENOMINATOR = 147;

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
            return ($r['coverage_requirement'] ?? '') === 'REQUIRED' && ($r['applicability'] ?? '') === 'MANDATORY';
        }));
    }
}
