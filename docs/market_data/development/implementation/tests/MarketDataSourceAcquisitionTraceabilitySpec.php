<?php

/** Reviewed MD-B07-A001 predicate classification and proof-owner assignment. */
final class MarketDataSourceAcquisitionTraceabilitySpec
{
    public const ATTEMPT = 'MD-B07-A001';

    public const STAGE = 'MD-B07';

    public const EXPECTED_B07_DENOMINATOR = 115;

    public const SOURCE_DOCUMENT_COUNTS = [
        'MD-S053' => 226,
        'MD-S054' => 20,
    ];

    /** Supporting predicates already moved into B07 by earlier stage normalization. */
    public const EXTERNAL_RULES = [
        'MD-S020-R0010',
        'MD-S041-R0029',
        'MD-S041-R0056',
        'MD-S052-R0026',
        'MD-S055-R0024',
        'MD-S058-R0048',
        'MD-S059-R0040',
        'MD-S066-R0001',
    ];

    public static function ruleId(string $document, int $number): string
    {
        return $document.'-R'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /** @return array<string,array<int,string>> document => rule number => proof-owning stage */
    public static function requiredOwners(): array
    {
        $owners = [];
        $assign = static function (string $document, array $numbers, string $stage) use (&$owners): void {
            foreach ($numbers as $number) {
                if (isset($owners[$document][$number])) {
                    throw new RuntimeException('Duplicate B07 traceability assignment: '.$document.' R'.$number);
                }
                $owners[$document][$number] = $stage;
            }
        };

        // Immutable observation, provider-neutral adapter, target-date/schema/provenance and
        // acquisition-boundary behavior owned and closed by B07.
        $assign('MD-S053', [2, 3, 4, 5, 7, 8, 9, 10, 15, 18, 19, 20, 21, 22, 23, 25, 26], 'MD-B07');
        $assign('MD-S053', [32, 34, 35, 36, 37, 38, 40, 41, 42, 43, 47, 48, 49, 51], 'MD-B07');
        $assign('MD-S053', [56, 57, 58, 59, 61], 'MD-B07');
        $assign('MD-S053', range(63, 80), 'MD-B07');
        $assign('MD-S053', [82, 83, 84], 'MD-B07');
        $assign('MD-S053', range(86, 95), 'MD-B07');
        $assign('MD-S053', range(97, 99), 'MD-B07');
        $assign('MD-S053', range(105, 108), 'MD-B07');
        $assign('MD-S053', [111, 112, 120, 129, 132, 133, 134, 141, 142], 'MD-B07');
        $assign('MD-S053', [144, 145, 146, 147, 150, 151, 153, 161], 'MD-B07');
        $assign('MD-S053', [165, 166, 167, 168, 169, 201], 'MD-B07');

        // Resilience/manual recovery, canonical candidate persistence, correction/publication,
        // coverage, readiness, operational backfill, and global alignment are closed by their
        // executable owners; B07 remains explicit supporting proof.
        $assign('MD-S053', [28, 31, 44, 45, 52, 60, 100], 'MD-B08');
        $assign('MD-S053', [11, 12, 13, 14, 122, 123, 124, 125, 126, 130, 131, 148, 155, 156, 157, 162, 163, 164], 'MD-B09');
        $assign('MD-S053', [81, 110, 158, 159], 'MD-B10');
        $assign('MD-S053', [16, 127, 136, 137, 138, 139, 143, 149], 'MD-B15');
        $assign('MD-S053', [101], 'MD-B17');
        $assign('MD-S053', [29, 30], 'MD-B19');
        $assign('MD-S053', range(179, 183), 'MD-B19');
        $assign('MD-S053', range(185, 195), 'MD-B19');
        $assign('MD-S053', range(197, 200), 'MD-B19');
        $assign('MD-S053', [202], 'MD-B19');
        $assign('MD-S053', range(204, 206), 'MD-B19');
        $assign('MD-S053', range(208, 210), 'MD-B19');
        $assign('MD-S053', range(212, 225), 'MD-B19');
        $assign('MD-S053', range(171, 176), 'MD-B21');

        $assign('MD-S054', [1, 11, 12, 13, 14, 15, 16, 17, 18, 19], 'MD-B07');
        $assign('MD-S054', [20], 'MD-B10');

        foreach (self::EXTERNAL_RULES as $rule) {
            [$document, $number] = self::splitRule($rule);
            $assign($document, [$number], 'MD-B07');
        }

        foreach ($owners as &$documentOwners) {
            ksort($documentOwners, SORT_NUMERIC);
        }
        unset($documentOwners);

        return $owners;
    }

    /** @return array{0:string,1:int} */
    public static function splitRule(string $rule): array
    {
        if (! preg_match('/^(MD-S\d{3})-R(\d{4})$/', $rule, $match)) {
            throw new RuntimeException('Invalid rule id '.$rule);
        }

        return [$match[1], (int) $match[2]];
    }

    /** @return array<string,string> rule id => governing parent rule id */
    public static function predicateParents(): array
    {
        $parents = [];
        $bind = static function (string $document, array $numbers, int $parent) use (&$parents): void {
            foreach ($numbers as $number) {
                $parents[self::ruleId($document, $number)] = self::ruleId($document, $parent);
            }
        };

        $bind('MD-S053', range(7, 16), 6);
        $bind('MD-S053', range(18, 23), 17);
        $bind('MD-S053', [25, 26], 24);
        $bind('MD-S053', range(28, 30), 27);
        $bind('MD-S053', range(34, 36), 33);
        $bind('MD-S053', range(40, 45), 39);
        $bind('MD-S053', range(47, 49), 46);
        $bind('MD-S053', [51, 52], 50);
        $bind('MD-S053', [59, 60], 56);
        $bind('MD-S053', range(63, 75), 62);
        $bind('MD-S053', range(86, 94), 85);
        $bind('MD-S053', range(97, 101), 96);
        $bind('MD-S053', range(105, 108), 104);
        $bind('MD-S053', range(122, 126), 121);
        $bind('MD-S053', range(129, 134), 128);
        $bind('MD-S053', range(136, 139), 135);
        $bind('MD-S053', range(141, 151), 140);
        $bind('MD-S053', range(155, 158), 154);
        $bind('MD-S053', range(161, 169), 160);
        $bind('MD-S053', range(171, 176), 170);
        $bind('MD-S053', range(179, 183), 178);
        $bind('MD-S053', range(185, 195), 184);
        $bind('MD-S053', range(197, 200), 196);
        $bind('MD-S053', range(204, 206), 203);
        $bind('MD-S053', range(208, 210), 207);
        $bind('MD-S053', range(212, 216), 211);
        $bind('MD-S053', range(219, 225), 218);
        $bind('MD-S054', range(12, 18), 1);

        $parents['MD-S020-R0010'] = 'MD-S020-R0008';
        $parents['MD-S041-R0029'] = 'MD-S041-R0026';
        $parents['MD-S052-R0026'] = 'MD-S052-R0025';
        $parents['MD-S059-R0040'] = 'MD-S059-R0039';

        return $parents;
    }

    /** @return array<string,string> */
    public static function normalizedPredicateOverrides(): array
    {
        return [
            'MD-S053-R0219' => 'Lifecycle API backfill must not use requested_start minus N calendar days as the warmup source of truth.',
            'MD-S053-R0220' => 'Lifecycle API backfill must not use a fixed holiday buffer as the warmup source of truth.',
            'MD-S053-R0221' => 'Lifecycle API backfill must not publish requested dates when the market-calendar warmup dependency cannot be proven.',
            'MD-S053-R0222' => 'Lifecycle API backfill must derive warmup_start through the governed tradingDateWindowStart contract, capped only by the first available dataset trading date.',
            'MD-S053-R0223' => 'Lifecycle API backfill must fail fast for requested dates that the governed calendar proves are non-trading dates.',
            'MD-S053-R0225' => 'Lifecycle API backfill telemetry must record warmup_start, requested_start, requested_end, and source_acquisition_mode=range_window.',
        ];
    }
}
