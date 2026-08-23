<?php

/** Reviewed MD-B04-A001 predicate classification and proof-owner assignment. */
final class MarketDataConfigFoundationTraceabilitySpec
{
    public const ATTEMPT = 'MD-B04-A001';

    public const EXPECTED_B04_DENOMINATOR = 114;

    public const DOCUMENT_COUNTS = [
        'MD-S005' => 105,
        'MD-S019' => 106,
        'MD-S034' => 27,
        'MD-S065' => 5,
        'MD-S082' => 227,
        'MD-S085' => 464,
    ];

    /** @return array<string,array<int,string>> document => rule number => proof-owning stage */
    public static function requiredOwners(): array
    {
        $owners = [];
        $assign = function (string $document, array $numbers, string $stage) use (&$owners): void {
            foreach ($numbers as $number) {
                if (isset($owners[$document][$number])) {
                    throw new RuntimeException('Duplicate B04 traceability spec: '.$document.' R'.$number);
                }
                $owners[$document][$number] = $stage;
            }
        };

        // Audit hash: B04 owns the canonical serializer foundation; artifact/manifest/replay proof
        // stays with the stages that can create and compare those objects.
        $assign('MD-S005', array_merge(range(9, 18), [20]), 'MD-B04');
        $assign('MD-S005', array_merge([1], range(3, 8), range(21, 28), range(30, 40),
            range(42, 51), range(53, 62), range(64, 73), range(75, 78), range(80, 89),
            range(91, 94), [19, 97, 98, 105]), 'MD-B10');
        $assign('MD-S005', [95, 96], 'MD-B18');

        // Determinism is deliberately distributed by executable proof ownership.
        $assign('MD-S019', [76, 77, 78], 'MD-B04');
        $assign('MD-S019', [6, 7, 8, 14, 15, 23, 25, 57, 58, 59, 60, 62, 63, 64, 83, 84, 85], 'MD-B10');
        $assign('MD-S019', [9, 66, 67, 68, 69, 70, 71, 72, 73, 74], 'MD-B18');
        $assign('MD-S019', [24, 27, 28, 29, 30, 31, 32, 33, 35, 36, 37, 38], 'MD-B17');
        $assign('MD-S019', [42, 50, 51, 52, 54, 55], 'MD-B14');
        $assign('MD-S019', [43, 45, 46, 47, 48], 'MD-B12');
        $assign('MD-S019', [80, 81], 'MD-B16');
        $assign('MD-S019', range(94, 100), 'MD-B21');
        $assign('MD-S019', [101], 'MD-B22');

        // Exact hash-number formatting is a B04 primitive; consumers prove their alignment later.
        $assign('MD-S034', array_merge(range(3, 16), [21, 23]), 'MD-B04');
        $assign('MD-S034', [20], 'MD-B09');
        $assign('MD-S034', [24, 25, 26], 'MD-B14');
        $assign('MD-S034', [2, 27], 'MD-B21');

        $assign('MD-S065', [1, 2, 5], 'MD-B04');
        $assign('MD-S065', [3], 'MD-B18');
        $assign('MD-S065', [4], 'MD-B19');

        // Config identity, resolved families, registry metadata, environment controls, and the
        // serializer/change validation foundation are owned by B04.
        $assign('MD-S082', array_merge([1, 2], range(4, 11), range(20, 67), [89, 104, 105],
            range(212, 214), [220, 221, 223]), 'MD-B04');
        $assign('MD-S082', [12, 14, 16, 17, 222], 'MD-B10');
        $assign('MD-S082', [15, 216, 217, 218, 224, 225], 'MD-B18');
        $assign('MD-S082', [18], 'MD-B19');
        $assign('MD-S082', [19], 'MD-B22');
        $assign('MD-S082', [163], 'MD-B05');
        $assign('MD-S082', array_merge(range(203, 211), [226, 227]), 'MD-B21');

        // Registry identity/rules are B04. Runtime meaning of selected codes remains with its
        // behavioral owner rather than making every dictionary row a separate requirement.
        // R0001 is a list introducer. Its semantic context is bound into R0002..R0008 rather
        // than counted as an independent executable requirement.
        $assign('MD-S085', array_merge(range(2, 14), [455]), 'MD-B04');
        $assign('MD-S085', [446], 'MD-B16');
        $assign('MD-S085', [447], 'MD-B08');
        $assign('MD-S085', [448, 463], 'MD-B10');
        $assign('MD-S085', [449, 450], 'MD-B15');
        $assign('MD-S085', [451], 'MD-B20');
        $assign('MD-S085', [452], 'MD-B18');

        foreach ($owners as &$documentOwners) {
            ksort($documentOwners, SORT_NUMERIC);
        }
        unset($documentOwners);

        return $owners;
    }

    /** @return array<string,array<int,int>> document => child number => governing parent number */
    public static function parentBindings(): array
    {
        $parents = [];
        $bind = function (string $document, array $children, int $parent) use (&$parents): void {
            foreach ($children as $child) {
                $parents[$document][$child] = $parent;
            }
        };

        $bind('MD-S005', range(3, 7), 2);
        $bind('MD-S005', range(9, 19), 1);
        $bind('MD-S005', range(22, 27), 21);
        $bind('MD-S005', range(30, 39), 29);
        $bind('MD-S005', range(42, 50), 41);
        $bind('MD-S005', range(53, 59), 52);
        $bind('MD-S005', range(64, 72), 63);
        $bind('MD-S005', range(75, 78), 74);
        $bind('MD-S005', range(80, 84), 79);
        $bind('MD-S005', range(91, 98), 90);

        $bind('MD-S019', range(6, 8), 5);
        $bind('MD-S019', [23, 24, 25], 22);
        $bind('MD-S019', [27, 28, 29], 26);
        $bind('MD-S019', [35, 36, 37, 38], 34);
        $bind('MD-S019', [45, 46, 47, 48], 44);
        $bind('MD-S019', [50, 51, 52], 49);
        $bind('MD-S019', [54, 55], 53);
        $bind('MD-S019', [57, 58, 59, 60], 56);
        $bind('MD-S019', [62, 63, 64], 61);
        $bind('MD-S019', range(66, 73), 65);
        $bind('MD-S019', [76, 77, 78], 75);
        $bind('MD-S019', [80, 81], 79);
        $bind('MD-S019', [83, 84, 85], 82);
        $bind('MD-S019', range(94, 100), 93);

        $bind('MD-S034', range(3, 16), 1);
        $bind('MD-S034', range(23, 27), 22);
        $bind('MD-S065', [2, 3, 4, 5], 1);
        $bind('MD-S082', range(4, 10), 3);
        $bind('MD-S082', range(21, 65), 20);
        $bind('MD-S082', [89, 104, 105], 66);
        $bind('MD-S082', range(203, 210), 202);
        $bind('MD-S082', range(216, 217), 215);
        $bind('MD-S082', range(220, 226), 219);
        $bind('MD-S085', range(2, 8), 1);

        return $parents;
    }

    public static function ruleId(string $document, int $number): string
    {
        return sprintf('%s-R%04d', $document, $number);
    }
}
