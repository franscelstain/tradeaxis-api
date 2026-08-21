<?php

/**
 * PHP 7.3+; enforces section 2 of `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` against the
 * canonical traceability matrix, in both directions.
 *
 * `F-MD-B01-A001-001` states the defect runs both ways: the required set "contains rules that cannot
 * be proved, and excludes rules that both can and should be". `MD-B01-A004` remediated the first
 * direction and measured it to zero. The second direction was never measured, and `MD-B01-A014`
 * found it live: 17 enumerated lists in `MD-B01` alone carried mixed classification, where the only
 * difference between a REQUIRED member and a REFERENCE_ONLY sibling was a deontic modal.
 *
 * Neither existing gate could see it. `MarketDataTraceabilityApplicabilityGate` validates rows that
 * are already REQUIRED; `MarketDataScopeBoundaryCompletionGate` asserted the denominator had not
 * drifted from a number this attempt proved wrong. A gate that locks a figure without checking how
 * the figure was derived reports PASS for exactly as long as the derivation stays broken.
 *
 * Two invariants:
 *
 *   MIXED_RUN         no enumerated run inside one document/section may hold both REQUIRED and
 *                     REFERENCE_ONLY members. The matrix marking some members required is its own
 *                     admission that the list carries obligations.
 *   REQUIRED_STRUCTURE a heading, colon-terminated introducer, or owner pointer may never carry a
 *                     proof obligation — the obligation belongs to what it introduces. A table row,
 *                     bare label, bare document reference, or bare identifier may, but only with a
 *                     recorded non-SELF predicate_context and normalized_predicate, because
 *                     section 3 forbids treating such a row as proof-complete merely because the
 *                     referenced target exists.
 *
 * Stages that have completed entry normalization fail on a violation. Unopened stages are reported
 * as an outstanding `MD-DEP-0004` entry obligation rather than silently excused, so the backlog is
 * visible instead of implied.
 */
final class MarketDataClassificationConsistencyGate
{
    /**
     * Stages that have performed the applicability/ownership entry obligation and are therefore held
     * to the invariants now. A stage joins this list when it opens, not when it becomes convenient.
     */
    public const NORMALIZED_STAGES = ['MD-B00', 'MD-B01'];

    /**
     * Floors that make a vacuous scan impossible. A scan that matches nothing must not be
     * indistinguishable from a corpus that is clean; this codebase has produced that shape three
     * times already.
     */
    public const MIN_ROWS = 6000;

    public const MIN_RUNS = 200;

    /**
     * Structural classes whose obligation always lives in the rows they introduce. No amount of
     * recorded context can make one of these provable in its own right.
     */
    public const NEVER_REQUIRABLE = ['HEADING', 'LIST_INTRODUCER', 'OWNER_POINTER', 'EMPTY'];

    /**
     * Section 3: a required row that is not a self-contained predicate must deterministically name
     * its governing parent and the composed predicate. `SELF` does not qualify — a bare fragment
     * cannot be its own semantic context.
     *
     * @param  array<string,string>  $row
     */
    public static function hasBoundPredicateContext(array $row): bool
    {
        $notes = (string) $row['notes'];
        if (! preg_match('/predicate_context=([^;]+);\s*normalized_predicate=(.+)/', $notes, $match)) {
            return false;
        }
        $context = trim($match[1]);

        return $context !== '' && strtoupper($context) !== 'SELF' && strlen(trim($match[2])) >= 20;
    }

    /** @return array{headers:array<int,string>,rows:array<int,array<string,string>>} */
    public static function readMatrix(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Cannot read matrix: '.$path);
        }
        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
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

    /**
     * Structural class per section 2, or null when the row states a predicate.
     *
     * Grammatical mood is deliberately absent. Encoding "has a modal" here would rebuild the defect
     * this gate exists to detect.
     */
    public static function structuralClass(string $raw): ?string
    {
        $text = trim($raw);
        if ($text === '') {
            return 'EMPTY';
        }
        if ($text[0] === '#') {
            return 'HEADING';
        }
        if ($text[0] === '|') {
            return 'TABLE_ROW';
        }
        if (preg_match('/^>\s*\*\*Owner\b/i', $text)) {
            return 'OWNER_POINTER';
        }
        if (substr($text, -1) === ':') {
            return 'LIST_INTRODUCER';
        }
        if (preg_match('/^\*\*[^*]+\*\*[.:;]?$/', $text)) {
            return 'BARE_LABEL';
        }
        $body = preg_replace('/^([-*]|\d+\.)\s+/', '', $text);
        if (preg_match('/^`?[A-Za-z0-9_\/\.\- ]+\.(md|json|csv|sql|php)`?$/i', $body)) {
            return 'BARE_DOC_REF';
        }
        if (preg_match('/^`[^`]+`[.;]?$/', $body)) {
            return 'BARE_IDENTIFIER';
        }

        return null;
    }

    public static function listMarker(string $raw): ?string
    {
        $text = ltrim($raw);
        if (preg_match('/^-\s+\S/', $text)) {
            return 'dash';
        }
        if (preg_match('/^\d+\.\s+\S/', $text)) {
            return 'num';
        }

        return null;
    }

    /**
     * Contiguous runs of same-marker enumerated rows inside one strategy document and section.
     *
     * @param  array<int,array<string,string>>  $rows
     * @return array<int,array{key:string,items:array<int,int>}>
     */
    public static function detectRuns(array $rows): array
    {
        $runs = [];
        $current = null;
        $lastLine = 0;
        foreach ($rows as $index => $row) {
            $isListRow = strtoupper(trim($row['active'])) === 'YES' && self::listMarker($row['rule_text']) !== null;
            if (! $isListRow) {
                if ($current !== null) {
                    $runs[] = $current;
                    $current = null;
                }

                continue;
            }
            $key = $row['strategy_owner'].'|'.$row['section'].'|'.self::listMarker($row['rule_text']);
            if ($current === null || $current['key'] !== $key || (int) $row['source_line'] > $lastLine + 3) {
                if ($current !== null) {
                    $runs[] = $current;
                }
                $current = ['key' => $key, 'items' => []];
            }
            $current['items'][] = $index;
            $lastLine = (int) $row['source_line'];
        }
        if ($current !== null) {
            $runs[] = $current;
        }

        return $runs;
    }

    /**
     * @param  array<int,array<string,string>>  $rows
     * @return array{errors:array<int,string>,pending:array<string,int>,counts:array<string,int>,runs:int}
     */
    public static function validate(array $rows, array $normalizedStages = self::NORMALIZED_STAGES): array
    {
        $errors = [];
        $pending = [];
        $counts = ['active_rows' => 0, 'required' => 0, 'reference_only' => 0, 'mixed_runs' => 0, 'mixed_members' => 0];

        foreach ($rows as $row) {
            if (strtoupper(trim($row['active'])) !== 'YES') {
                continue;
            }
            $counts['active_rows']++;
            if ($row['coverage_requirement'] === 'REQUIRED') {
                $counts['required']++;
                $structural = self::structuralClass($row['rule_text']);
                if ($structural !== null && in_array($row['primary_stage'], $normalizedStages, true)) {
                    if (in_array($structural, self::NEVER_REQUIRABLE, true)) {
                        $errors[] = 'REQUIRED_STRUCTURE '.$row['rule_id'].' ['.$row['primary_stage'].']: a '
                            .$structural.' cannot carry an executable proof obligation; it belongs to what it introduces';
                    } elseif (! self::hasBoundPredicateContext($row)) {
                        $errors[] = 'REQUIRED_STRUCTURE '.$row['rule_id'].' ['.$row['primary_stage'].']: a '
                            .$structural.' is required without a governing predicate_context, so its proof '
                            .'can only establish that the referenced target exists';
                    }
                }
            } elseif ($row['coverage_requirement'] === 'REFERENCE_ONLY') {
                $counts['reference_only']++;
            }
        }

        $runs = self::detectRuns($rows);
        foreach ($runs as $run) {
            if (count($run['items']) < 2) {
                continue;
            }
            $classes = [];
            foreach ($run['items'] as $index) {
                $classes[$rows[$index]['coverage_requirement']] = true;
            }
            if (! isset($classes['REQUIRED']) || ! isset($classes['REFERENCE_ONLY'])) {
                continue;
            }
            $counts['mixed_runs']++;
            foreach ($run['items'] as $index) {
                $row = $rows[$index];
                if ($row['coverage_requirement'] !== 'REFERENCE_ONLY') {
                    continue;
                }
                if (self::structuralClass($row['rule_text']) !== null) {
                    continue; // a genuine fragment inside a mixed list stays reference context
                }
                $counts['mixed_members']++;
                $stage = $row['primary_stage'];
                if (in_array($stage, $normalizedStages, true)) {
                    $errors[] = 'MIXED_RUN '.$row['rule_id'].' ['.$stage.']: REFERENCE_ONLY while siblings in '
                        .$row['section'].' are REQUIRED';
                } else {
                    $pending[$stage] = ($pending[$stage] ?? 0) + 1;
                }
            }
        }

        if ($counts['active_rows'] < self::MIN_ROWS) {
            $errors[] = 'VACUOUS_SCAN: only '.$counts['active_rows'].' active rows were read; the matrix cannot be that small';
        }
        if (count($runs) < self::MIN_RUNS) {
            $errors[] = 'VACUOUS_SCAN: only '.count($runs).' enumerated runs were detected; run detection is not working';
        }

        ksort($pending);

        return ['errors' => $errors, 'pending' => $pending, 'counts' => $counts, 'runs' => count($runs)];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $marketData = realpath(dirname(__DIR__, 3));
    $matrix = $marketData.'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $data = MarketDataClassificationConsistencyGate::readMatrix($matrix);
    $result = MarketDataClassificationConsistencyGate::validate($data['rows']);
    $result['gate'] = 'MarketDataClassificationConsistencyGate';
    $result['normalized_stages'] = MarketDataClassificationConsistencyGate::NORMALIZED_STAGES;
    $result['pending_total'] = array_sum($result['pending']);
    $result['status'] = $result['errors'] === [] ? 'PASS' : 'FAIL';
    $result['note'] = 'Unopened stages are reported as pending, not excused: each carries the MD-DEP-0004 '
        .'entry obligation to resolve its own classification before claiming coverage.';
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
