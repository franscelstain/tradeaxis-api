<?php

/**
 * Stage-scoped applicability and semantic-predicate gate.
 *
 * Default mode validates the canonical matrix and exits non-zero on any transitional applicability,
 * invalid lifecycle, missing context binding, missing normalized predicate, missing parent row, or
 * source-fingerprint drift in the active MD-B01 scope.
 *
 * `--apply-normalization` performs the governed MD-B01-A012 matrix mutation. It is deliberately
 * explicit and idempotent; normal gate execution is read-only.
 */
final class MarketDataTraceabilityApplicabilityGate
{
    private const ATTEMPT_MARKER = 'MD-B01-A012';

    /**
     * `MD-B01-A014` promoted 72 rows that section 2 does not permit to be reference context. They
     * carry the same structured note, produced by `MarketDataClassificationRederivation.php`, under
     * their own attempt marker. Their predicate context is not read from a table here: it is
     * recomputed from the matrix so a hand-edited note cannot declare a parent it does not have.
     */
    private const SUCCESSOR_MARKER = 'MD-B01-A014';

    /**
     * Required-row drift lock. 143 was recorded as FINAL at A012/A013; A014 proved that figure
     * understated the denominator because mixed-classification enumerated runs had demoted 72
     * predicate rows by grammatical mood.
     */
    private const EXPECTED_REQUIRED_ROWS = 207;

    /** @return array<string,array{applicability:string,basis:string}> */
    public static function successorConditionalSpec(): array
    {
        return [
            'MD-S020-R0068' => [
                'applicability' => 'CONDITIONAL_APPLICABLE',
                'basis' => 'condition_true: the eligible compatibility alias is still present on current schema and read surfaces, so the canonical/alias split it states is live',
            ],
            'MD-S020-R0071' => [
                'applicability' => 'CONDITIONAL_APPLICABLE',
                'basis' => 'condition_true: retirement has not occurred, so the until-retirement propagation prohibition binds now; matches the basis recorded for MD-S020-R0173',
            ],
        ];
    }

    /**
     * Governing parent of an enumerated-list row, derived from the matrix itself: the nearest
     * preceding active non-list row inside the same strategy document and section. Returns `SELF`
     * for a row that carries no list marker or begins its own section.
     *
     * @param array<int,array<string,string>> $rows
     */
    public static function derivedPredicateContext(array $rows, int $index): string
    {
        $row = $rows[$index];
        if (!preg_match('/^\s*([-*]|\d+\.)\s+\S/', $row['rule_text'])) {
            return 'SELF';
        }
        for ($k = $index - 1; $k >= 0 && $k > $index - 40; $k--) {
            $candidate = $rows[$k];
            if ($candidate['strategy_owner'] !== $row['strategy_owner']) {
                break;
            }
            if (strtoupper(trim($candidate['active'])) !== 'YES') {
                continue;
            }
            if (preg_match('/^\s*([-*]|\d+\.)\s+\S/', $candidate['rule_text'])) {
                continue;
            }
            if ($candidate['section'] !== $row['section']) {
                break;
            }

            return $candidate['rule_id'];
        }

        return 'SELF';
    }

    /** @return array<string,array{context:string,prefix:string}> */
    public static function contextSpec(): array
    {
        $spec = [];
        $add = static function (array $ids, string $context, string $prefix) use (&$spec): void {
            foreach ($ids as $id) {
                $spec[$id] = ['context' => $context, 'prefix' => $prefix];
            }
        };

        $add(['MD-S001-R0008'], 'MD-S001-R0005', 'The canonical scope requires ');
        $add(['MD-S001-R0033', 'MD-S001-R0034', 'MD-S001-R0035', 'MD-S001-R0036'], 'MD-S001-R0031;MD-S001-R0032', 'The date-driven system must ');
        $add(['MD-S001-R0046'], 'MD-S001-R0041', 'The four time boundaries must remain distinct; ');
        $add(['MD-S001-R0051', 'MD-S001-R0053'], 'MD-S001-R0049', 'The price-product terminology must keep distinct: ');
        $add(['MD-S001-R0061'], 'MD-S001-R0058', 'The locked cross-contract platform decision requires ');
        $add(['MD-S001-R0077'], 'MD-S001-R0074', 'For the active yahoo_finance path, ');
        $add(['MD-S001-R0080', 'MD-S001-R0081', 'MD-S001-R0083'], 'MD-S001-R0074;MD-S001-R0079', 'As a consequence of the active provider path, ');
        $add(['MD-S001-R0098'], 'MD-S001-R0093', 'A downstream consumer is prohibited from ');
        $add(['MD-S001-R0100'], 'MD-S001-R0099', 'Market Data owns ');
        $add([
            'MD-S001-R0127', 'MD-S001-R0128', 'MD-S001-R0129', 'MD-S001-R0130', 'MD-S001-R0131',
            'MD-S001-R0132', 'MD-S001-R0133', 'MD-S001-R0134', 'MD-S001-R0135', 'MD-S001-R0136',
            'MD-S001-R0137', 'MD-S001-R0138', 'MD-S001-R0139', 'MD-S001-R0140', 'MD-S001-R0141',
        ], 'MD-S001-R0126', 'Active domain documents must not state or imply that ');
        $add(['MD-S001-R0154', 'MD-S001-R0155', 'MD-S001-R0156', 'MD-S001-R0157', 'MD-S001-R0158'], 'MD-S001-R0153', 'The current documentation state must be read as follows: ');

        $add(['MD-S020-R0014', 'MD-S020-R0015'], 'MD-S020-R0008', 'Market-data readiness may be admitted only from market-data evidence establishing ');
        $add(['MD-S020-R0018', 'MD-S020-R0023'], 'MD-S020-R0017', 'Market Data may own and publish ');
        $add(['MD-S020-R0069', 'MD-S020-R0070'], 'MD-S020-R0067', 'The eligible compatibility-alias lifecycle requires that ');
        $add(['MD-S020-R0075', 'MD-S020-R0076', 'MD-S020-R0077', 'MD-S020-R0078', 'MD-S020-R0079'], 'MD-S020-R0074', 'Indicators must not become ');
        $add(['MD-S020-R0083', 'MD-S020-R0084', 'MD-S020-R0085', 'MD-S020-R0086'], 'MD-S020-R0082', 'Market Data must not ');
        $add(['MD-S020-R0096'], 'MD-S020-R0094', 'For known dual-use facts, the owner split is ');
        $add(['MD-S020-R0110', 'MD-S020-R0111', 'MD-S020-R0112', 'MD-S020-R0113'], 'MD-S020-R0109', 'A session snapshot must never become ');
        $add([
            'MD-S020-R0115', 'MD-S020-R0116', 'MD-S020-R0117', 'MD-S020-R0118', 'MD-S020-R0119',
            'MD-S020-R0120', 'MD-S020-R0121', 'MD-S020-R0122', 'MD-S020-R0123',
        ], 'MD-S020-R0114', 'Market Data must not silently embed consumer policy inside ');
        $add(['MD-S020-R0152'], 'MD-S020-R0141', 'The positive-feature vocabulary prohibition permits only ');
        $add(['MD-S020-R0159'], 'MD-S020-R0153', 'Overloaded vocabulary must be governed so that ');
        $add([
            'MD-S020-R0182', 'MD-S020-R0183', 'MD-S020-R0184', 'MD-S020-R0185',
            'MD-S020-R0186', 'MD-S020-R0187', 'MD-S020-R0188',
        ], 'MD-S020-R0181', 'The Domain Boundary owner contract must remain semantically aligned with ');

        $add(['MD-S056-R0008'], 'MD-S056-R0005', 'For a field to be decision-grade for T, it must be ');
        $add(['MD-S056-R0019', 'MD-S056-R0020', 'MD-S056-R0021'], 'MD-S056-R0018', 'Every dependency window must declare its horizon role; ');
        $add(['MD-S056-R0033'], 'MD-S056-R0030', 'Given the intentional dataset start, ');
        $add(['MD-S056-R0042'], 'MD-S056-R0037;MD-S056-R0038', 'Before operational activation, development-frontier reporting must ensure ');
        $add(['MD-S056-R0045', 'MD-S056-R0046'], 'MD-S056-R0043;MD-S056-R0044', 'Operational activation requires that ');
        $add(['MD-S056-R0054'], 'MD-S056-R0053', 'The platform target minimum outputs include ');
        $add(['MD-S056-R0141', 'MD-S056-R0142'], 'MD-S056-R0132;MD-S056-R0140', 'For terminology owned by this register, ');

        return $spec;
    }

    /** @return array<string,array{applicability:string,basis:string}> */
    public static function conditionalSpec(): array
    {
        return [
            'MD-S001-R0077' => [
                'applicability' => 'CONDITIONAL_APPLICABLE',
                'basis' => 'condition_true: current strategy and config select yahoo_finance as the bootstrap/default API path',
            ],
            'MD-S020-R0067' => [
                'applicability' => 'CONDITIONAL_APPLICABLE',
                'basis' => 'condition_true: the eligible compatibility alias remains present in current schema/read surfaces and active contracts',
            ],
            'MD-S020-R0069' => [
                'applicability' => 'CONDITIONAL_PENDING',
                'basis' => 'condition_unresolved: retirement requires demonstrated absence of every reader outside the package; repository absence alone is not an external-consumer inventory',
            ],
            'MD-S020-R0173' => [
                'applicability' => 'CONDITIONAL_APPLICABLE',
                'basis' => 'condition_true: the eligible compatibility alias is currently preserved and therefore its non-propagation obligation applies',
            ],
            'MD-S020-R0189' => [
                'applicability' => 'CONDITIONAL_APPLICABLE',
                'basis' => 'condition_true: F-MD-B01-A003-001 identifies dependent contracts with older eligible wording',
            ],
        ];
    }

    /** @return array<string,array{stage:string,supporting:string,invalidate:bool}> */
    public static function ownershipMoves(): array
    {
        $moves = [
            'MD-S020-R0014' => ['stage' => 'MD-B18', 'supporting' => 'MD-B01;MD-B10', 'invalidate' => true],
            'MD-S020-R0015' => ['stage' => 'MD-B17', 'supporting' => 'MD-B01;MD-B19', 'invalidate' => true],
            'MD-S020-R0069' => ['stage' => 'MD-B17', 'supporting' => 'MD-B01', 'invalidate' => false],
            'MD-S020-R0070' => ['stage' => 'MD-B17', 'supporting' => 'MD-B01', 'invalidate' => false],
            'MD-S056-R0042' => ['stage' => 'MD-B19', 'supporting' => 'MD-B01', 'invalidate' => false],
            'MD-S056-R0045' => ['stage' => 'MD-B19', 'supporting' => 'MD-B01', 'invalidate' => false],
        ];
        foreach (['MD-S056-R0019', 'MD-S056-R0020', 'MD-S056-R0021', 'MD-S056-R0022', 'MD-S056-R0024', 'MD-S056-R0129'] as $id) {
            $moves[$id] = ['stage' => 'MD-B14', 'supporting' => 'MD-B01', 'invalidate' => false];
        }

        return $moves;
    }

    /** @return array{headers:array<int,string>,rows:array<int,array<string,string>>} */
    public static function readMatrix(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Cannot read matrix: '.$path);
        }
        $headers = fgetcsv($handle);
        if (!is_array($headers)) {
            fclose($handle);
            throw new RuntimeException('Matrix header is missing.');
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw new RuntimeException('Malformed matrix row with '.count($values).' fields.');
            }
            $rows[] = array_combine($headers, $values);
        }
        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** @param array<int,string> $headers @param array<int,array<string,string>> $rows */
    public static function writeMatrix(string $path, array $headers, array $rows): void
    {
        $temp = $path.'.tmp-'.getmypid();
        $handle = fopen($temp, 'w');
        if ($handle === false) {
            throw new RuntimeException('Cannot create temporary matrix.');
        }
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            $values = [];
            foreach ($headers as $header) {
                $values[] = $row[$header];
            }
            fputcsv($handle, $values);
        }
        fclose($handle);
        if (!rename($temp, $path)) {
            @unlink($temp);
            throw new RuntimeException('Cannot replace canonical matrix.');
        }
    }

    private static function cleanFragment(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^[-*]\s+/', '', $text);
        if (substr($text, 0, 1) === '|') {
            $cells = array_values(array_filter(array_map('trim', explode('|', trim($text, "| \t"))), static function ($cell): bool {
                return $cell !== '';
            }));
            $text = implode(' — ', $cells);
        }

        return trim($text);
    }

    /** @param array<int,array<string,string>> $rows @return array<int,array<string,string>> */
    public static function normalize(array $rows): array
    {
        $context = self::contextSpec();
        $conditional = self::conditionalSpec();
        $moves = self::ownershipMoves();

        foreach ($rows as &$row) {
            if ($row['primary_stage'] !== 'MD-B01' || $row['coverage_requirement'] !== 'REQUIRED' || $row['active'] !== 'YES') {
                continue;
            }

            $id = $row['rule_id'];
            $spec = $context[$id] ?? ['context' => 'SELF', 'prefix' => ''];
            $normalized = trim($spec['prefix'].self::cleanFragment($row['rule_text']));
            $app = $conditional[$id]['applicability'] ?? 'MANDATORY';
            $basis = $conditional[$id]['basis'] ?? 'always_applicable: the obligation is part of the current stage contract; conditional behavior inside it remains mandatory';

            $prior = trim((string) $row['notes']);
            $prior = preg_replace('/\s*\|\s*MD-B01-A012: applicability_normalized=.*$/', '', $prior);
            $marker = self::ATTEMPT_MARKER.': applicability_normalized='.$app
                .'; predicate_context='.$spec['context']
                .'; normalized_predicate='.$normalized
                .'; applicability_basis='.$basis
                .'; proof_owner_confirmed='.($moves[$id]['stage'] ?? 'MD-B01');
            $row['notes'] = $prior === '' ? $marker : $prior.' | '.$marker;
            $row['applicability'] = $app;

            if ($app === 'CONDITIONAL_PENDING') {
                $row['coverage_status'] = 'APPLICABILITY_PENDING';
                $row['current_evidence_ids'] = '';
            }

            if (isset($moves[$id])) {
                $row['primary_stage'] = $moves[$id]['stage'];
                $row['supporting_stages'] = $moves[$id]['supporting'];
                if ($moves[$id]['invalidate']) {
                    $oldEvidence = $row['current_evidence_ids'];
                    $row['coverage_status'] = 'NOT_ASSESSED';
                    $row['current_evidence_ids'] = '';
                    $row['notes'] .= '; prior_satisfied_invalidated=DOC-CHG-20260821-004 existence-only proof did not establish the normalized parent predicate'
                        .($oldEvidence === '' ? '' : '; prior_evidence='.$oldEvidence);
                }
            }
        }
        unset($row);

        return $rows;
    }

    /** @param array<int,array<string,string>> $rows @return array<int,array<string,string>> */
    public static function bindA012Evidence(array $rows): array
    {
        $newlySatisfied = [
            'MD-S001-R0155' => 'TraceabilityApplicabilityGateTest::test_current_system_summary_carries_the_three_previously_missing_state_predicates',
            'MD-S001-R0158' => 'TraceabilityApplicabilityGateTest::test_current_system_summary_carries_the_three_previously_missing_state_predicates',
            'MD-S056-R0142' => 'TraceabilityApplicabilityGateTest::test_current_system_summary_carries_the_three_previously_missing_state_predicates',
        ];
        foreach ($rows as &$row) {
            if ($row['primary_stage'] !== 'MD-B01' || $row['coverage_requirement'] !== 'REQUIRED' || $row['active'] !== 'YES') {
                continue;
            }
            if (isset($newlySatisfied[$row['rule_id']])) {
                $row['coverage_status'] = 'SATISFIED';
                if (strpos($row['notes'], 'proof='.$newlySatisfied[$row['rule_id']]) === false) {
                    $row['notes'] .= '; proof='.$newlySatisfied[$row['rule_id']]
                        .'; implementation_surface=development/implementation/guides/system/SYSTEM_DATA_PRODUCT_MAP.md';
                }
            }
            if ($row['coverage_status'] === 'SATISFIED') {
                $row['current_evidence_ids'] = 'E-MD-B01-A012-001';
                if (strpos($row['notes'], 'semantic_revalidation=E-MD-B01-A012-001') === false) {
                    $row['notes'] .= '; semantic_revalidation=E-MD-B01-A012-001';
                }
            }
        }
        unset($row);

        return $rows;
    }

    /** @param array<int,array<string,string>> $rows @return array<int,array<string,string>> */
    public static function validate(array $rows, string $stage = 'MD-B01'): array
    {
        $errors = [];
        $byId = [];
        $indexById = [];
        foreach ($rows as $index => $row) {
            $byId[$row['rule_id']] = $row;
            $indexById[$row['rule_id']] = $index;
        }

        $contextSpec = self::contextSpec();
        $conditional = self::conditionalSpec();
        $counts = [
            'required_rows' => 0,
            'mandatory' => 0,
            'conditional_applicable' => 0,
            'conditional_pending' => 0,
            'conditional_not_applicable' => 0,
            'satisfied' => 0,
            'not_assessed' => 0,
        ];

        foreach ($rows as $row) {
            if ($row['primary_stage'] !== $stage || $row['coverage_requirement'] !== 'REQUIRED' || $row['active'] !== 'YES') {
                continue;
            }
            $counts['required_rows']++;
            $id = $row['rule_id'];
            $app = $row['applicability'];
            $status = $row['coverage_status'];

            $allowed = ['MANDATORY', 'CONDITIONAL_PENDING', 'CONDITIONAL_APPLICABLE', 'CONDITIONAL_NOT_APPLICABLE'];
            if (!in_array($app, $allowed, true)) {
                $errors[] = $id.': transitional or invalid applicability '.$app;
                continue;
            }
            if ($app === 'MANDATORY') {
                $counts['mandatory']++;
                if (!in_array($status, ['NOT_ASSESSED', 'SATISFIED'], true)) {
                    $errors[] = $id.': MANDATORY row has invalid lifecycle '.$status;
                }
            } elseif ($app === 'CONDITIONAL_APPLICABLE') {
                $counts['conditional_applicable']++;
                if (!in_array($status, ['NOT_ASSESSED', 'SATISFIED'], true)) {
                    $errors[] = $id.': CONDITIONAL_APPLICABLE row has invalid lifecycle '.$status;
                }
            } elseif ($app === 'CONDITIONAL_PENDING') {
                $counts['conditional_pending']++;
                if ($status !== 'APPLICABILITY_PENDING') {
                    $errors[] = $id.': CONDITIONAL_PENDING row must be APPLICABILITY_PENDING';
                }
            } else {
                $counts['conditional_not_applicable']++;
                if ($status !== 'NOT_APPLICABLE') {
                    $errors[] = $id.': CONDITIONAL_NOT_APPLICABLE row must be NOT_APPLICABLE';
                }
            }

            if ($status === 'SATISFIED') {
                $counts['satisfied']++;
                if (trim($row['current_evidence_ids']) === '') {
                    $errors[] = $id.': SATISFIED row has no current evidence';
                }
            } elseif ($status === 'NOT_ASSESSED') {
                $counts['not_assessed']++;
            }

            $notes = (string) $row['notes'];
            $isSuccessor = strpos($notes, self::SUCCESSOR_MARKER.': reclassified REFERENCE_ONLY -> REQUIRED') !== false;

            $expectedApp = $isSuccessor
                ? (self::successorConditionalSpec()[$id]['applicability'] ?? 'MANDATORY')
                : ($conditional[$id]['applicability'] ?? 'MANDATORY');
            if ($app !== $expectedApp) {
                $errors[] = $id.': applicability is '.$app.', expected '.$expectedApp.' from the governed semantic review';
            }

            $hasA012 = strpos($notes, self::ATTEMPT_MARKER.': applicability_normalized=') !== false;
            if ((!$hasA012 && !$isSuccessor)
                || !preg_match('/predicate_context=(.+?); normalized_predicate=(.+); applicability_basis=.+; proof_owner_confirmed=([A-Z0-9-]+)/', $notes, $match)) {
                $errors[] = $id.': missing structured predicate/applicability note';
                continue;
            }
            $actualContext = $match[1];
            $normalized = trim($match[2]);
            if ($isSuccessor) {
                // Recomputed from the matrix, never read from the note it is checking.
                $expectedContext = self::derivedPredicateContext($rows, $indexById[$id]);
                $fragment = trim(preg_replace('/^\s*([-*]|\d+\.)\s+/', '', trim($row['rule_text'])));
                // Case-insensitive: composing a parent lead with a child clause legitimately
                // lowercases the child's first letter. Everything else must match verbatim.
                if ($fragment !== '' && stripos($normalized, rtrim($fragment, '.')) === false) {
                    $errors[] = $id.': normalized predicate does not contain the row fragment it claims to normalize';
                }
            } else {
                $expectedContext = $contextSpec[$id]['context'] ?? 'SELF';
            }
            if ($actualContext !== $expectedContext) {
                $errors[] = $id.': predicate context '.$actualContext.' does not match '.$expectedContext;
            }
            if (strlen($normalized) < 20) {
                $errors[] = $id.': normalized predicate is missing or too short';
            }
            if ($actualContext !== 'SELF') {
                foreach (explode(';', $actualContext) as $parentId) {
                    if (!isset($byId[$parentId])) {
                        $errors[] = $id.': predicate parent '.$parentId.' does not exist';
                    }
                }
            }
            if (strtoupper(sha1($row['rule_text'])) !== strtoupper($row['rule_fingerprint_sha1'])) {
                $errors[] = $id.': source rule fingerprint drift';
            }
        }

        if ($stage === 'MD-B01' && $counts['required_rows'] !== self::EXPECTED_REQUIRED_ROWS) {
            $errors[] = 'MD-B01: required row count is '.$counts['required_rows']
                .', expected '.self::EXPECTED_REQUIRED_ROWS.' after the A014 classification re-derivation';
        }

        return ['errors' => $errors, 'counts' => $counts];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $md = realpath(dirname(__DIR__, 3));
    $matrix = $md.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $data = MarketDataTraceabilityApplicabilityGate::readMatrix($matrix);
    if (in_array('--apply-normalization', $argv, true)) {
        $data['rows'] = MarketDataTraceabilityApplicabilityGate::normalize($data['rows']);
        MarketDataTraceabilityApplicabilityGate::writeMatrix($matrix, $data['headers'], $data['rows']);
    }
    if (in_array('--bind-a012-evidence', $argv, true)) {
        $data['rows'] = MarketDataTraceabilityApplicabilityGate::bindA012Evidence($data['rows']);
        MarketDataTraceabilityApplicabilityGate::writeMatrix($matrix, $data['headers'], $data['rows']);
    }
    $result = MarketDataTraceabilityApplicabilityGate::validate($data['rows']);
    $result['stage_id'] = 'MD-B01';
    $result['attempt_id'] = 'MD-B01-A012';
    $result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['errors'] === [] ? 0 : 1);
}
