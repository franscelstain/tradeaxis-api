<?php

/**
 * Reviewed `MD-B05-A001` predicate classification and proof-owner assignment.
 *
 * Three contracts are held entirely by this stage: `MD-S052` sector classification, `MD-S055` symbol
 * lifecycle and mapping, `MD-S057` tickers and identity dependency. 166 active rows, of which the
 * matrix carried 77 as `REQUIRED` and 89 as `REFERENCE_ONLY`.
 *
 * The reference set was not derived from structure. It excluded hard prohibitions — "no other
 * classification system may be stored as `IDX-IC`", "overlapping mappings to different instruments
 * are invalid", the entire failure-behavior list of the mapping contract — while including their
 * siblings, and it excluded two of the three contracts' acceptance criteria while marking the third
 * required. That is the second direction of `F-MD-B01-A001-001`, measured here for the first time.
 *
 * Two rules govern this spec.
 *
 *   1. A row is reference only when it is structurally not a predicate: a heading, a colon-terminated
 *      introducer, a bare label such as "**What it cannot prove.**", a table header, or a bare
 *      document reference. Grammatical mood is never an input. Encoding it would rebuild the defect.
 *   2. A required row is owned by the stage that can execute its proof. These contracts name
 *      observations, publications, coverage, replay, and sector-derived measures; naming them does
 *      not make them provable here. Those move, and `MD-B05` stays as supporting stage so the
 *      obligation remains visible from this contract.
 *
 * The result is 135 required predicates, 117 of them owned by `MD-B05`.
 */
final class MarketDataTemporalIdentityTraceabilitySpec
{
    public const ATTEMPT = 'MD-B05-A001';

    public const STAGE = 'MD-B05';

    public const EXPECTED_B05_DENOMINATOR = 117;

    /** Active row count per owned document, so a shrinking corpus cannot pass as a clean one. */
    public const DOCUMENT_COUNTS = [
        'MD-S052' => 52,
        'MD-S055' => 41,
        'MD-S057' => 73,
    ];

    /**
     * Rows this stage owns that live in documents owned elsewhere. Both were assigned here by
     * earlier attempts and are re-validated rather than re-derived.
     */
    public const IMPORTED_B05_RULES = ['MD-S020-R0011', 'MD-S082-R0163'];

    /**
     * Structural reference rows, stated positively rather than inferred by subtraction.
     *
     * Keeping the list explicit is what lets the gate check both directions: a row that leaves this
     * set must acquire an owner, and a row that enters it must lose one.
     *
     * @var array<string,array<int,int>>
     */
    public const STRUCTURAL_REFERENCE = [
        // 2, 8, 15, 25, 28, 40 are colon-terminated introducers; 33 is a bare label; 9 is the table
        // header; 47-52 are cross-contract pointers.
        'MD-S052' => [2, 8, 9, 15, 25, 28, 33, 40, 47, 48, 49, 50, 51, 52],
        // 3 and 19 introduce lists; 31 is a bare label; 38-41 are cross-contract pointers.
        'MD-S055' => [3, 19, 31, 38, 39, 40, 41],
        // 3, 10, 18, 34, 57 introduce lists; 51 is a bare label; 70-73 are cross-contract pointers.
        'MD-S057' => [3, 10, 18, 34, 51, 57, 70, 71, 72, 73],
    ];

    public static function ruleId(string $document, int $number): string
    {
        return $document.'-R'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /** @return array<string,array<int,string>> document => rule number => proof-owning stage */
    public static function requiredOwners(): array
    {
        $owners = [];
        $assign = function (string $document, array $numbers, string $stage) use (&$owners): void {
            foreach ($numbers as $number) {
                if (isset($owners[$document][$number])) {
                    throw new RuntimeException('Duplicate B05 traceability spec: '.$document.' R'.$number);
                }
                if (in_array($number, self::STRUCTURAL_REFERENCE[$document], true)) {
                    throw new RuntimeException('Structural row assigned an owner: '.$document.' R'.$number);
                }
                $owners[$document][$number] = $stage;
            }
        };

        // ---- MD-S052 Sector classification -------------------------------------------------
        // Scope, authority, source classes, temporal membership, resolution, and the capability
        // boundary are the membership foundation this stage builds.
        $assign('MD-S052', [1, 3, 5, 6, 7], 'MD-B05');
        $assign('MD-S052', [10, 11, 12, 13, 14], 'MD-B05');
        $assign('MD-S052', range(16, 24), 'MD-B05');
        $assign('MD-S052', [32, 34, 35, 36, 37, 38], 'MD-B05');
        $assign('MD-S052', [43, 44, 45, 46], 'MD-B05');
        // A sector index bar is an acquired observation, subject to the acquisition rules the
        // observation stage owns.
        $assign('MD-S052', [26], 'MD-B07');
        // A sector-derived measure is an analytical product. Its versioning, membership binding,
        // window behavior, and field naming are provable where the product is computed.
        $assign('MD-S052', [4, 27, 29, 30, 31], 'MD-B14');
        // External completeness reconciliation and its authority/cadence parameters are executed
        // under global gate 13, not by the resolver.
        $assign('MD-S052', [39, 41, 42], 'MD-B21');

        // ---- MD-S055 Symbol lifecycle and mapping ------------------------------------------
        $assign('MD-S055', [1, 2], 'MD-B05');
        $assign('MD-S055', range(4, 18), 'MD-B05');
        $assign('MD-S055', [20, 21, 22, 23], 'MD-B05');
        $assign('MD-S055', range(26, 30), 'MD-B05');
        $assign('MD-S055', [32, 33, 34, 35, 36, 37], 'MD-B05');
        // Observation storage of the requested provider symbol and the mapping identity used.
        $assign('MD-S055', [24], 'MD-B07');
        // The historical replay rule is proven where replay runs.
        $assign('MD-S055', [25], 'MD-B18');

        // ---- MD-S057 Tickers and identity dependency ---------------------------------------
        $assign('MD-S057', [1, 2], 'MD-B05');
        $assign('MD-S057', range(4, 9), 'MD-B05');
        $assign('MD-S057', range(11, 17), 'MD-B05');
        $assign('MD-S057', range(19, 27), 'MD-B05');
        $assign('MD-S057', [30, 31, 32], 'MD-B05');
        $assign('MD-S057', range(35, 42), 'MD-B05');
        $assign('MD-S057', [44, 45, 46], 'MD-B05');
        $assign('MD-S057', [49, 50, 52, 53, 54, 55], 'MD-B05');
        $assign('MD-S057', [60, 61], 'MD-B05');
        $assign('MD-S057', range(62, 69), 'MD-B05');
        // Canonical artifacts binding stable identity, and display codes never being durable join
        // keys, are claims about the whole persisted surface. Convergence owns them.
        $assign('MD-S057', [28, 29], 'MD-B21');
        // Identity corrections against sealed publications, and the run/publication recording the
        // identity snapshot, both require a publication to exist.
        $assign('MD-S057', [33, 43], 'MD-B10');
        // Coverage and promote exposing a per-instrument identity gap.
        $assign('MD-S057', [47], 'MD-B15');
        // The downstream read projection.
        $assign('MD-S057', [48], 'MD-B17');
        // External completeness reconciliation and its authority/cadence parameters.
        $assign('MD-S057', [56, 58, 59], 'MD-B21');

        foreach ($owners as &$documentOwners) {
            ksort($documentOwners, SORT_NUMERIC);
        }
        unset($documentOwners);

        return $owners;
    }

    /**
     * Predicate context for rows whose text is a fragment of an enumerated list.
     *
     * Section 3 of the traceability standard forbids treating such a row as proof-complete because
     * the referenced target exists; the obligation lives in the introducer, so the introducer is
     * named and the composed predicate recorded.
     *
     * @return array<string,int> rule id => introducer rule number
     */
    public static function predicateParents(): array
    {
        $parents = [];
        $bind = function (string $document, array $numbers, int $parent) use (&$parents): void {
            foreach ($numbers as $number) {
                $parents[self::ruleId($document, $number)] = self::ruleId($document, $parent);
            }
        };

        $bind('MD-S052', [3, 4, 5], 2);              // What follows from that placement:
        $bind('MD-S052', [10, 11, 12], 8);           // source authority class table
        $bind('MD-S052', range(16, 20), 15);         // Each membership record binds:
        $bind('MD-S052', [26, 27], 25);              // Two distinct things must not be conflated:
        $bind('MD-S052', [29, 30, 31], 28);          // Consequences:
        $bind('MD-S052', [34, 35, 36, 37], 33);      // What it cannot prove.
        $bind('MD-S052', [41, 42, 43, 44], 40);      // Domain parameters owned by this contract:
        $bind('MD-S055', range(4, 11), 3);           // Each mapping must include:
        $bind('MD-S055', [20, 21, 22, 23], 19);      // For one provider/namespace and instant:
        $bind('MD-S055', [32, 33, 34], 31);          // What it cannot prove.
        $bind('MD-S057', range(4, 8), 3);            // The following concepts must remain distinct:
        $bind('MD-S057', range(11, 16), 10);         // Records must provide:
        $bind('MD-S057', range(19, 24), 18);         // Universe membership resolved as-of T:
        $bind('MD-S057', range(35, 42), 34);         // Point-in-time resolution output:
        $bind('MD-S057', [52, 53, 54], 51);          // What it cannot prove.
        $bind('MD-S057', range(58, 61), 57);         // Domain parameters owned by this contract:

        return $parents;
    }
}
