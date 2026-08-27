<?php

/** Reviewed MD-B08-A001 resilience/manual-recovery predicate classification and proof-owner assignment. */
final class MarketDataSourceResilienceTraceabilitySpec
{
    public const ATTEMPT = 'MD-B08-A001';
    public const STAGE = 'MD-B08';

    /**
     * MD-B08-A002 remediation. MD-S067-R0010 is a standalone paragraph closing the 'Independent
     * dimensions' section, so it carries no list marker and the classification gate MIXED_RUN
     * invariant can never group it with its siblings. A001 left it REFERENCE_ONLY with empty notes
     * while it states two executable obligations, and nothing in the suite exercised either:
     * collapsing the retained reason map to its most frequent entry passed all 1946 tests.
     */
    public const REMEDIATION_ATTEMPT = 'MD-B08-A002';

    /** Rules whose classification MD-B08-A002 corrected, and the basis for each correction. */
    public const REMEDIATED_RULES = [
        'MD-S067-R0010' => 'a002_correction=promoted from REFERENCE_ONLY; a standalone paragraph '
            .'stating that every reason code is retained and that a primary reason carries routing '
            .'compatibility only, both objectively testable and neither previously exercised',
    ];
    public const EXPECTED_B08_DENOMINATOR = 139;

    public const SOURCE_DOCUMENT_COUNTS = [
        'MD-S029' => 208,
        'MD-S040' => 85,
        'MD-S067' => 20,
    ];

    /** Existing predicates already moved into B08 by predecessor-stage normalization. */
    public const EXTERNAL_RULES = [
        'MD-S053-R0028',
        'MD-S053-R0031',
        'MD-S053-R0044',
        'MD-S053-R0045',
        'MD-S053-R0052',
        'MD-S053-R0060',
        'MD-S053-R0100',
        'MD-S058-R0030',
        'MD-S058-R0052',
        'MD-S059-R0044',
        'MD-S059-R0076',
        'MD-S059-R0141',
        'MD-S085-R0447',
    ];

    /** Missing semantic fragments found by line-by-line stage-entry review. */
    public const ADDITIVE_RULES = [
        'MD-S029-R0207' => [
            'strategy_document_id' => 'MD-S029',
            'strategy_owner' => 'authority/strategy/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md',
            'source_line' => '28',
            'section' => 'Import phase',
            'rule_text' => '- dedup',
        ],
        'MD-S029-R0208' => [
            'strategy_document_id' => 'MD-S029',
            'strategy_owner' => 'authority/strategy/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md',
            'source_line' => '284',
            'section' => 'Selected source and traceability minimum',
            'rule_text' => '- `run_id`',
        ],
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
                    throw new RuntimeException('Duplicate B08 traceability assignment: '.$document.' R'.$number);
                }
                $owners[$document][$number] = $stage;
            }
        };

        // MD-S029 — resilience/import behavior owned by B08.
        $assign('MD-S029', [8, 9, 10, 11, 16, 18, 19, 20, 21, 22], 'MD-B08');
        $assign('MD-S029', [30, 31, 32, 33, 34], 'MD-B08');
        $assign('MD-S029', [38, 39, 40, 41, 42, 44, 45, 46, 47], 'MD-B08');
        $assign('MD-S029', [49, 50, 51, 56, 58, 59, 60, 61, 62, 64], 'MD-B08');
        $assign('MD-S029', [66, 67, 68, 69, 70, 71, 77], 'MD-B08');
        $assign('MD-S029', range(90, 97), 'MD-B08');
        $assign('MD-S029', [99, 100, 101, 103, 106, 109, 110, 111, 112, 114, 115, 116], 'MD-B08');
        $assign('MD-S029', [118, 119, 120, 122, 123, 125, 126, 127, 128, 130], 'MD-B08');
        $assign('MD-S029', [134, 135, 137, 138, 139, 140, 141], 'MD-B08');
        $assign('MD-S029', [143, 144, 145, 146, 147, 148], 'MD-B08');
        $assign('MD-S029', [151, 152, 153, 154, 155, 157, 158, 159], 'MD-B08');
        $assign('MD-S029', [161, 162, 163, 164, 167, 168, 169, 171], 'MD-B08');
        $assign('MD-S029', [173, 174, 175, 176, 177, 178, 181, 182, 183, 185], 'MD-B08');
        $assign('MD-S029', [188, 190, 191, 192, 195, 196, 197, 198, 199, 208], 'MD-B08');

        // MD-S029 — explicit proof owners outside B08. B08 remains supporting stage.
        $assign('MD-S029', [12, 13, 14, 207], 'MD-B09');
        $assign('MD-S029', [129, 132, 133, 149, 156], 'MD-B10');
        $assign('MD-S029', [15, 105, 113, 121, 131, 166, 179, 180, 189], 'MD-B15');
        $assign('MD-S029', [107, 170, 184, 186, 194], 'MD-B17');
        $assign('MD-S029', [73, 74, 75, 76, 102, 193], 'MD-B19');
        $assign('MD-S029', [2, 82, 83, 84, 85, 86, 87, 88, 201, 202, 203, 204, 205, 206], 'MD-B22');

        // MD-S040 — manual-file acquisition boundary is B08; publication/coverage/replay outcomes
        // stay with their executable downstream owners.
        $assign('MD-S040', [4, 7, 8, 44], 'MD-B08');
        $assign('MD-S040', [6, 22, 23, 24, 25, 26, 33, 34, 35, 36, 37, 39, 40, 41, 42, 43], 'MD-B10');
        $assign('MD-S040', [59, 60, 61, 62, 63, 64, 66, 67, 68, 82, 83, 84, 85], 'MD-B10');
                $assign('MD-S040', range(70, 80), 'MD-B18');

        // MD-S067 — B08 owns retry classification only. Publication/read/run lifecycle rows
        // are moved to the stage that can execute and prove them.
        $assign('MD-S067', [10, 20], 'MD-B08');
        $assign('MD-S067', [13, 14, 15], 'MD-B10');
        $assign('MD-S067', [12, 18], 'MD-B17');
        $assign('MD-S067', [16, 17], 'MD-B19');

        foreach (self::EXTERNAL_RULES as $rule) {
            [$document, $number] = self::splitRule($rule);
            $assign($document, [$number], 'MD-B08');
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

        $bind('MD-S029', range(8, 16), 7);
        $bind('MD-S029', [207], 7);
        $bind('MD-S029', range(18, 22), 17);
        $bind('MD-S029', range(31, 34), 30);
        $bind('MD-S029', range(38, 42), 37);
        $bind('MD-S029', [44, 45], 43);
        $bind('MD-S029', [49, 50, 51], 48);
        $bind('MD-S029', [58, 59, 60, 61], 57);
        $bind('MD-S029', range(66, 69), 65);
        $bind('MD-S029', range(73, 77), 72);
        $bind('MD-S029', range(82, 87), 81);
        $bind('MD-S029', range(90, 94), 89);
        $bind('MD-S029', range(99, 103), 98);
        $bind('MD-S029', range(105, 107), 104);
        $bind('MD-S029', range(109, 114), 108);
        $bind('MD-S029', range(118, 121), 117);
        $bind('MD-S029', range(125, 131), 124);
        $bind('MD-S029', range(137, 140), 136);
        $bind('MD-S029', range(143, 149), 142);
        $bind('MD-S029', range(151, 156), 150);
        $bind('MD-S029', [208], 150);
        $bind('MD-S029', range(161, 164), 160);
        $bind('MD-S029', range(166, 171), 165);
        $bind('MD-S029', range(173, 186), 172);
        $bind('MD-S029', range(188, 199), 187);
        $bind('MD-S029', range(201, 206), 200);

        $bind('MD-S040', range(23, 26), 22);
        $bind('MD-S040', range(33, 34), 32);
        $bind('MD-S040', range(39, 42), 38);
        $bind('MD-S040', range(59, 63), 58);
        $bind('MD-S040', range(66, 68), 65);
        $bind('MD-S040', range(70, 79), 69);
        $bind('MD-S040', range(82, 85), 81);

        $bind('MD-S067', range(12, 17), 11);

        return $parents;
    }

    /** @return array<string,string> */
    public static function normalizedPredicateOverrides(): array
    {
        return [
            'MD-S067-R0012' => 'When all required gates pass and the active sealed DTO verifies, the run status is SUCCEEDED and the publication/read state is READABLE.',
            'MD-S067-R0013' => 'When a deterministic data/product/gate condition prevents publication, the run status is HELD and the read state remains the previous explicit result or NOT_AVAILABLE.',
            'MD-S067-R0014' => 'When execution cannot complete, the run status is FAILED and the read state remains the previous explicit result or NOT_AVAILABLE.',
            'MD-S067-R0015' => 'While work remains in progress, the run status is RUNNING and the candidate is never readable.',
            'MD-S067-R0016' => 'When a duplicate target already has a verified active publication, the run status is SKIPPED and the existing publication is unchanged.',
            'MD-S067-R0017' => 'When a lock is owned by another fenced worker, the run is BLOCKED or SKIPPED_LOCKED and publication state is unchanged.',
            'MD-S029-R0207' => 'The import phase includes deterministic deduplication before canonical acceptance.',
            'MD-S029-R0208' => 'Every canonical row or publication context must remain traceable to run_id.',
        ];
    }
}
