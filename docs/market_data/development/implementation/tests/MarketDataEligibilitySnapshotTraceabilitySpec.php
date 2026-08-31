<?php

/**
 * Governed `MD-B16` stage-entry traceability specification.
 *
 * `W16` is explainable row-level data usability. Its owner contracts —
 * `EOD_Eligibility_Snapshot_Contract_LOCKED.md`, `Eligibility_Partial_Data_Behavior_LOCKED.md`
 * and `Invalid_Bar_Storage_Policy_LOCKED.md` — arrived with 28 rows still carrying the
 * transitional value, 17 mixed-run siblings, and 36 reference rows with no recorded decision.
 *
 * The reference rows again held real obligations: the liquidity and status dimension lists,
 * the decision-and-explanation fields, the prohibition on reconstructing a dimension from an
 * overloaded reason code, the registry-only reason rule, and the run-level distinction. What
 * stays reference is purpose text, editorial commentary on the contract's own wording, and
 * capability-boundary disclaimers.
 */
final class MarketDataEligibilitySnapshotTraceabilitySpec
{
    public const STAGE = 'MD-B16';

    public const ATTEMPT = 'MD-B16-A001';

    /** Rows that arrived transitional and resolve to an unconditional obligation. */
    public const ENTRY_MANDATORY = [
        'MD-S027-R0004' => 'Row scope and identity (LOCKED)',
        'MD-S027-R0006' => 'Expectation and delivery',
        'MD-S027-R0007' => 'Expectation and delivery',
        'MD-S027-R0008' => 'Expectation and delivery',
        'MD-S027-R0009' => 'Expectation and delivery',
        'MD-S027-R0010' => 'Quality',
        'MD-S027-R0011' => 'Quality',
        'MD-S027-R0012' => 'Quality',
        'MD-S027-R0013' => 'Quality',
        'MD-S027-R0028' => 'Decision and explanation',
        'MD-S027-R0029' => 'Decision and explanation',
        'MD-S027-R0030' => 'Decision and explanation',
        'MD-S027-R0031' => 'Decision and explanation',
        'MD-S027-R0042' => 'Capability boundary (LOCKED)',
        'MD-S027-R0043' => 'Gate separation',
        'MD-S027-R0048' => 'Gate separation',
        'MD-S027-R0049' => 'Reason-code model (LOCKED)',
        'MD-S027-R0061' => 'Publication/readability relationship',
        'MD-S031-R0002' => 'Core rule (LOCKED)',
        'MD-S031-R0006' => 'Required degraded behavior',
        'MD-S031-R0007' => 'Required degraded behavior',
        'MD-S031-R0010' => 'Required degraded behavior',
        'MD-S031-R0011' => 'Multiple-reason behavior',
        'MD-S031-R0019' => 'Determinism and immutability',
        'MD-S031-R0025' => 'Capability boundary (LOCKED)',
        'MD-S031-R0026' => 'Acceptance criterion (LOCKED)',
    ];

    /** Mixed-run siblings and undecided reference rows that carry an executable obligation. A coverage rule filed as context is still a coverage rule. */
    public const ENTRY_PROMOTED = [
        'MD-S027-R0003' => 'Row scope and identity (LOCKED)',
        'MD-S027-R0014' => 'Liquidity',
        'MD-S027-R0015' => 'Liquidity',
        'MD-S027-R0016' => 'Liquidity',
        'MD-S027-R0017' => 'Liquidity',
        'MD-S027-R0018' => 'Status and event risk',
        'MD-S027-R0019' => 'Status and event risk',
        'MD-S027-R0020' => 'Status and event risk',
        'MD-S027-R0021' => 'Decision and explanation',
        'MD-S027-R0022' => 'Decision and explanation',
        'MD-S027-R0023' => 'Decision and explanation',
        'MD-S027-R0024' => 'Decision and explanation',
        'MD-S027-R0025' => 'Decision and explanation',
        'MD-S027-R0032' => 'Eligibility meaning (LOCKED)',
        'MD-S027-R0033' => 'Eligibility meaning (LOCKED)',
        'MD-S027-R0034' => 'Eligibility meaning (LOCKED)',
        'MD-S027-R0044' => 'Gate separation',
        'MD-S027-R0045' => 'Gate separation',
        'MD-S027-R0046' => 'Gate separation',
        'MD-S027-R0047' => 'Gate separation',
        'MD-S027-R0051' => 'Reason-code model (LOCKED)',
        'MD-S027-R0052' => 'Reason-code model (LOCKED)',
        'MD-S027-R0053' => 'Reason-code model (LOCKED)',
        'MD-S027-R0054' => 'Reason-code model (LOCKED)',
        'MD-S027-R0055' => 'Reason-code model (LOCKED)',
        'MD-S027-R0056' => 'Reason-code model (LOCKED)',
        'MD-S027-R0058' => 'Reason-code model (LOCKED)',
        'MD-S027-R0059' => 'Reason-code model (LOCKED)',
        'MD-S027-R0060' => 'Publication/readability relationship',
        'MD-S027-R0062' => 'Immutability and replay',
        'MD-S027-R0063' => 'Acceptance criterion (LOCKED)',
        'MD-S031-R0003' => 'Required degraded behavior',
        'MD-S031-R0004' => 'Required degraded behavior',
        'MD-S031-R0005' => 'Required degraded behavior',
        'MD-S031-R0008' => 'Required degraded behavior',
        'MD-S031-R0009' => 'Required degraded behavior',
        'MD-S031-R0012' => 'Multiple-reason behavior',
        'MD-S031-R0014' => 'Run-level distinction',
        'MD-S031-R0015' => 'Run-level distinction',
        'MD-S031-R0016' => 'Run-level distinction',
        'MD-S031-R0017' => 'Run-level distinction',
    ];

    /** Rows with no executable obligation, each with the reason it carries none. */
    public const ENTRY_REFERENCE = [
        'MD-S027-R0001' => 'purpose statement declaring what the document defines',
        'MD-S027-R0002' => 'purpose statement drawing the boundary against ranking and portfolio policy; the operative prohibition is MD-S027-R0034',
        'MD-S027-R0026' => 'editorial note recording why a qualifier was removed from MD-S027-R0025; the obligation is R0025 itself',
        'MD-S027-R0035' => 'transitional sentence introducing the correctness boundary that follows',
        'MD-S027-R0037' => 'capability disclaimer: coverage sees delivery, not values',
        'MD-S027-R0038' => 'capability disclaimer: canonical validation sees internal consistency, not fidelity',
        'MD-S027-R0039' => 'capability disclaimer: contamination sees detected events, not unrecorded ones',
        'MD-S027-R0040' => 'capability disclaimer: replay sees reproducibility, not correctness',
        'MD-S027-R0041' => 'capability disclaimer: an undetectably wrong instrument is indistinguishable from a clean one',
        'MD-S031-R0001' => 'purpose statement declaring what the document defines',
        'MD-S031-R0020' => 'capability summary of what partial-data handling proves; the obligations it summarises are held by their own rules',
        'MD-S031-R0022' => 'capability disclaimer: partiality is about which facts arrived, not whether they are correct',
        'MD-S031-R0023' => 'capability disclaimer: a blocked row does not claim the missing input would have changed the decision',
        'MD-S031-R0024' => 'capability disclaimer: a dimension nobody declared is never detected as missing',
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
