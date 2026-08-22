<?php

/**
 * Reviewed MD-B06-A001 predicate classification and proof-owner assignment.
 *
 * The two source contracts contain 158 active extracted rows. A row is excluded only when it is
 * an introducer, table header, bare label/document pointer, or explanatory prose without an
 * observable conformity condition. Predicate-bearing table/list fragments remain required and
 * are bound to their governing parent. Proof ownership follows the executable responsibility in
 * the current build sequence; MD-B06 remains a supporting stage when a predicate moves.
 */
final class MarketDataCalendarStatusTraceabilitySpec
{
    public const ATTEMPT = 'MD-B06-A001';

    public const STAGE = 'MD-B06';

    public const EXPECTED_B06_DENOMINATOR = 78;

    public const DOCUMENT_COUNTS = [
        'MD-S041' => 84,
        'MD-S058' => 74,
    ];

    /** Rows that do not independently state an executable semantic predicate. */
    public const NON_PREDICATE_REFERENCE = [
        'MD-S041' => [5, 16, 25, 26, 33, 40, 44, 47, 58, 69, 73, 80, 81, 82, 83, 84],
        'MD-S058' => [3, 16, 17, 38, 57, 64, 70, 71, 72, 73, 74],
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
                    throw new RuntimeException('Duplicate B06 traceability assignment: '.$document.' R'.$number);
                }
                if (in_array($number, self::NON_PREDICATE_REFERENCE[$document], true)) {
                    throw new RuntimeException('Non-predicate row assigned an owner: '.$document.' R'.$number);
                }
                $owners[$document][$number] = $stage;
            }
        };

        // Calendar/session primitives, point-in-time expectation inputs, fail-closed behavior, and
        // the capability statement that this resolver can actually prove.
        $assign('MD-S041', [1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13], 'MD-B06');
        $assign('MD-S041', [15, 17, 18, 19, 20, 22, 23, 24], 'MD-B06');
        $assign('MD-S041', [27, 28, 31, 34, 35, 36, 38, 39, 41], 'MD-B06');
        $assign('MD-S041', [50, 51, 52, 63, 64, 66, 68], 'MD-B06');

        // Provider timestamp mapping and transport overfetch are proved by acquisition.
        $assign('MD-S041', [29, 56], 'MD-B07');
        // Calendar correction/publication binding and finalization are publication-state duties.
        $assign('MD-S041', [14, 30, 54, 65], 'MD-B10');
        // Short-session analytical treatment and warm-up behavior belong to the measure engine.
        $assign('MD-S041', [45, 46, 49, 53, 55], 'MD-B14');
        // Denominator interaction and the complete listing/date coverage explanation.
        $assign('MD-S041', [21, 37, 43, 79], 'MD-B15');
        $assign('MD-S041', [42], 'MD-B16');
        $assign('MD-S041', [48, 62], 'MD-B17');
        $assign('MD-S041', [32], 'MD-B18');
        $assign('MD-S041', [57, 59, 60, 61], 'MD-B19');
        // External completeness is explicitly owned by global gate 13/convergence.
        $assign('MD-S041', [67, 70, 71, 72, 74, 75, 76, 77, 78], 'MD-B21');

        // Temporal status fact, source authority/priority, import boundary, interval resolution,
        // expectation effects, conflict safety, and bounded claims.
        $assign('MD-S058', [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15], 'MD-B06');
        $assign('MD-S058', [18, 19, 20, 21, 22, 23, 24, 25], 'MD-B06');
        $assign('MD-S058', [26, 27, 28, 29, 31, 32, 33, 34, 35, 37], 'MD-B06');
        $assign('MD-S058', [39, 40, 41, 43, 49, 50, 53, 54, 56, 62], 'MD-B06');

        $assign('MD-S058', [48], 'MD-B07');
        $assign('MD-S058', [30, 52], 'MD-B08');
        $assign('MD-S058', [36, 51], 'MD-B10');
        $assign('MD-S058', [42, 44, 61], 'MD-B15');
        $assign('MD-S058', [45, 46, 47], 'MD-B16');
        $assign('MD-S058', [69], 'MD-B18');
        $assign('MD-S058', [55], 'MD-B19');
        $assign('MD-S058', [58, 59, 60, 63, 65, 66, 67, 68], 'MD-B21');

        foreach ($owners as &$documentOwners) {
            ksort($documentOwners, SORT_NUMERIC);
        }
        unset($documentOwners);

        return $owners;
    }

    /** @return array<string,string> rule id => governing parent rule id */
    public static function predicateParents(): array
    {
        $parents = [];
        $bind = function (string $document, array $numbers, int $parent) use (&$parents): void {
            foreach ($numbers as $number) {
                $parents[self::ruleId($document, $number)] = self::ruleId($document, $parent);
            }
        };

        $bind('MD-S041', range(6, 13), 5);
        $bind('MD-S041', [17, 18], 15);
        $bind('MD-S041', range(20, 24), 15);
        $bind('MD-S041', range(27, 30), 26);
        $bind('MD-S041', range(34, 36), 33);
        $bind('MD-S041', range(41, 43), 40);
        $bind('MD-S041', [45, 46], 44);
        $bind('MD-S041', range(48, 50), 47);
        $bind('MD-S041', range(59, 62), 58);
        $bind('MD-S041', range(70, 71), 69);
        $bind('MD-S041', range(74, 77), 73);

        $bind('MD-S058', range(4, 13), 3);
        $bind('MD-S058', range(18, 20), 16);
        $bind('MD-S058', range(27, 30), 26);
        $bind('MD-S058', range(39, 41), 38);
        $bind('MD-S058', range(58, 61), 57);
        $bind('MD-S058', range(65, 68), 64);

        return $parents;
    }
}
