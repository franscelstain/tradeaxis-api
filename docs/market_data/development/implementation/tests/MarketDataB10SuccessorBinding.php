<?php

/**
 * PHP 7.3+; governed successor-attempt binding for `MD-B10` (F-MD-B10-A002-006).
 *
 * `MD-B10-A001` bound the whole B10 denominator once, to one evidence record, through
 * `MarketDataPublicationLifecycleProofBinder`. `MD-B10-A002` reopened a subset of that denominator and
 * re-proved it; the rest keeps its A001 binding. This library is the mechanism that promotes exactly the
 * reopened subset to the successor evidence, and the shared definition the B10 gates use to recognise a
 * state in which two attempts own different rows.
 *
 * Authority it implements (it adds none):
 *  - `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` s6: `SATISFIED` requires current correlation-first
 *    evidence tied to a valid Attempt/Baseline/Epoch;
 *  - s9: a prior `SATISFIED` is carried forward only when the existing evidence still proves the exact
 *    predicate, otherwise the row is `NOT_ASSESSED` until re-proven. The governed record that decides which
 *    rows carry forward and which are reopened is the scope evidence (`E-MD-B10-A002-001`), per predicate;
 *  - `STAGE_CLOSURE_MANIFEST_STANDARD.md`: closure needs every denominator row `SATISFIED`.
 *
 * Invariants it enforces:
 *  - the denominator stays exactly 1072;
 *  - only the rules the scope evidence names as affected change, and only `coverage_status`,
 *    `current_evidence_ids` and `notes`; every other line of the matrix is emitted byte-for-byte;
 *  - every affected rule must currently be `NOT_ASSESSED` with no evidence, every unaffected rule
 *    `SATISFIED` on the evidence the scope evidence records for it;
 *  - the successor evidence must prove every affected rule `PROVEN_CURRENT` and nothing else, belong to the
 *    successor attempt/baseline/change-impact, be immutable, and its raw package must still match its manifest;
 *  - the write is all or nothing: a temporary file is verified before it replaces the matrix, the result is
 *    read back and validated, and any failure restores the original bytes.
 */
final class MarketDataB10SuccessorBinding
{
    public const STAGE = 'MD-B10';

    public const EXPECTED_DENOMINATOR = 1072;

    public const PREDECESSOR_ATTEMPT = 'MD-B10-A001';

    public const PREDECESSOR_PATTERN = '/^E-MD-B10-A001-\d{3}$/';

    /** The only fields a successor binding may change on an affected row. */
    public const CHANGED_FIELDS = ['coverage_status', 'current_evidence_ids', 'notes'];

    /**
     * Registered successor attempts, oldest first. A rule owned by more than one scope belongs to the last one.
     *
     * @return array<string,array<string,string>>
     */
    public static function profiles(): array
    {
        return [
            'MD-B10-A002' => [
                'attempt' => 'MD-B10-A002',
                'baseline' => 'MD-B10-A002-BL001',
                'change_impact' => 'CI-MD-B10-A002-001',
                'scope_evidence' => 'E-MD-B10-A002-001',
                'evidence_pattern' => '/^E-MD-B10-A002-\d{3}$/',
                'epoch' => 'MD-REBASELINE-20260820-001',
                'strategy_freeze' => 'MD-STRATEGY-FREEZE-20260925-001',
            ],
        ];
    }

    public static function profile(string $attempt): ?array
    {
        $profiles = self::profiles();

        return $profiles[$attempt] ?? null;
    }

    /** @return array<string,string> */
    public static function paths(string $root, array $o = []): array
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');

        return [
            'root' => $root,
            'matrix' => $o['matrix'] ?? $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv',
            'evidence_dir' => $o['evidence_dir'] ?? $root.'/docs/market_data/records/evidence',
            'relationships' => $o['relationships_path'] ?? (isset($o['relationships']) && is_string($o['relationships'])
                ? $o['relationships'] : $root.'/docs/market_data/records/WORK_RELATIONSHIP_REGISTRY.csv'),
            'package_base' => $o['package_base'] ?? $root,
        ];
    }

    public static function canonicalMatrix(string $root): string
    {
        return rtrim(str_replace('\\', '/', $root), '/').'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    }

    /**
     * The matrix as raw lines, so untouched lines can be written back byte-for-byte.
     *
     * @return array{raw:string,bom:bool,header_line:string,headers:array<int,string>,lines:array<int,string>,rows:array<int,array<string,string>>,errors:array<int,string>}
     */
    public static function readMatrix(string $path): array
    {
        $out = ['raw' => '', 'bom' => false, 'header_line' => '', 'headers' => [], 'lines' => [], 'rows' => [], 'errors' => []];
        $raw = @file_get_contents($path);
        if ($raw === false) {
            $out['errors'][] = 'MATRIX_UNREADABLE';

            return $out;
        }
        $out['raw'] = $raw;
        $out['bom'] = substr($raw, 0, 3) === "\xEF\xBB\xBF";
        $body = $out['bom'] ? substr($raw, 3) : $raw;
        if (strpos($body, "\r") !== false) {
            $out['errors'][] = 'MATRIX_NOT_LF_ONLY: this writer will not guess line endings';

            return $out;
        }
        if (substr($body, -1) !== "\n") {
            $out['errors'][] = 'MATRIX_MISSING_FINAL_NEWLINE';

            return $out;
        }
        $lines = explode("\n", substr($body, 0, -1));
        $out['header_line'] = array_shift($lines);
        $out['headers'] = str_getcsv($out['header_line']);
        foreach ($lines as $index => $line) {
            $values = str_getcsv($line);
            if (count($values) !== count($out['headers'])) {
                $out['errors'][] = 'MATRIX_LINE_UNPARSEABLE: '.($index + 2);

                return $out;
            }
            $out['lines'][] = $line;
            $out['rows'][] = array_combine($out['headers'], $values);
        }

        return $out;
    }

    /**
     * The matrix was written by more than one tool: some lines quote a field only when it contains a comma, a quote
     * or a line break (minimal), others also quote any field with a space (fputcsv). A line is rewritten in the
     * style it already has; a line neither style reproduces is refused rather than silently re-quoted.
     */
    public static function encodeLine(array $fields, string $style = 'fputcsv'): string
    {
        if ($style === 'minimal') {
            return implode(',', array_map(static function ($value) {
                $value = (string) $value;

                return strpbrk($value, ",\"\r\n") !== false ? '"'.str_replace('"', '""', $value).'"' : $value;
            }, array_values($fields)));
        }
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_values($fields));
        rewind($handle);
        $line = stream_get_contents($handle);
        fclose($handle);

        return rtrim($line, "\n");
    }

    /** The style that reproduces $line exactly from its own fields, or null. */
    public static function detectStyle(string $line): ?string
    {
        $fields = str_getcsv($line);
        foreach (['minimal', 'fputcsv'] as $style) {
            if (self::encodeLine($fields, $style) === $line) {
                return $style;
            }
        }

        return null;
    }

    /** @return array{payload:?array,path:?string,sha256:?string} */
    public static function loadEvidence(string $dir, string $evidenceId, array &$errors): array
    {
        $matches = glob(rtrim($dir, '/').'/'.$evidenceId.'_*');
        if (! is_array($matches) || count($matches) !== 1) {
            $errors[] = 'GOVERNED_EVIDENCE_CARDINALITY_INVALID: '.$evidenceId;

            return ['payload' => null, 'path' => null, 'sha256' => null];
        }
        $payload = json_decode((string) file_get_contents($matches[0]), true);
        if (! is_array($payload)) {
            $errors[] = 'GOVERNED_EVIDENCE_JSON_INVALID: '.$evidenceId;

            return ['payload' => null, 'path' => $matches[0], 'sha256' => null];
        }

        return ['payload' => $payload, 'path' => $matches[0], 'sha256' => hash_file('sha256', $matches[0])];
    }

    /**
     * The scope a successor attempt reopened, from its governed scope evidence.
     *
     * @return array{affected:array<int,string>,review:array<string,array>}
     */
    public static function loadScope(array $paths, array $profile, array &$errors, ?array $payloadOverride = null): array
    {
        $payload = $payloadOverride;
        if ($payload === null) {
            $loaded = self::loadEvidence($paths['evidence_dir'], $profile['scope_evidence'], $errors);
            $payload = $loaded['payload'];
        }
        if (! is_array($payload)) {
            return ['affected' => [], 'review' => []];
        }
        foreach (['evidence_id' => $profile['scope_evidence'], 'attempt_id' => $profile['attempt'], 'baseline_id' => $profile['baseline'],
            'change_impact_declaration' => $profile['change_impact'], 'stage_id' => self::STAGE] as $field => $value) {
            if (($payload[$field] ?? null) !== $value) {
                $errors[] = 'SCOPE_EVIDENCE_WRONG_'.strtoupper($field);
            }
        }
        if (($payload['mutability'] ?? '') !== 'IMMUTABLE_AFTER_ISSUE') {
            $errors[] = 'SCOPE_EVIDENCE_NOT_IMMUTABLE';
        }
        $affected = $payload['affected_current_predicates'] ?? null;
        if (! is_array($affected) || $affected === []) {
            $errors[] = 'SCOPE_AFFECTED_SET_MISSING';

            return ['affected' => [], 'review' => []];
        }
        if (count($affected) !== count(array_unique($affected))) {
            $errors[] = 'SCOPE_AFFECTED_SET_HAS_DUPLICATES';
        }
        $review = [];
        foreach ((array) ($payload['predicate_review'] ?? []) as $entry) {
            if (isset($entry['rule_id'])) {
                $review[(string) $entry['rule_id']] = $entry;
            }
        }
        if ((int) ($payload['review_method']['mandatory_denominator'] ?? 0) !== self::EXPECTED_DENOMINATOR
            || (int) ($payload['review_method']['affected'] ?? 0) !== count($affected)) {
            $errors[] = 'SCOPE_EVIDENCE_COUNTS_DISAGREE';
        }

        return ['affected' => array_values($affected), 'review' => $review];
    }

    /**
     * rule id => owning successor attempt, for gates that need to know which evidence a bound row must carry.
     *
     * @return array<string,string>
     */
    public static function scopeMap(string $root, array $o, array &$errors): array
    {
        $successors = array_key_exists('successors', $o) ? $o['successors'] : self::profiles();
        $map = [];
        foreach ($successors as $attempt => $profile) {
            if (isset($o['scopes'][$attempt])) {
                $affected = $o['scopes'][$attempt];
            } else {
                $affected = self::loadScope(self::paths($root, $o), $profile, $errors)['affected'];
            }
            foreach ($affected as $rule) {
                $map[(string) $rule] = $attempt;
            }
        }

        return $map;
    }

    /** The evidence-id pattern a bound mandatory row must match, given who owns it. */
    public static function patternFor(string $rule, array $scopeMap, array $successors): string
    {
        if (isset($scopeMap[$rule]) && isset($successors[$scopeMap[$rule]])) {
            return $successors[$scopeMap[$rule]]['evidence_pattern'];
        }

        return self::PREDECESSOR_PATTERN;
    }

    /** @return array<int,string> */
    public static function validateSuccessorEvidence(array $payload, string $evidenceId, array $profile, array $affected): array
    {
        $errors = [];
        foreach (['evidence_id' => $evidenceId, 'record_type' => 'EVIDENCE', 'stage_id' => self::STAGE, 'attempt_id' => $profile['attempt'],
            'baseline_id' => $profile['baseline'], 'change_impact_declaration' => $profile['change_impact'],
            'verification_epoch' => $profile['epoch'], 'strategy_freeze_id' => $profile['strategy_freeze']] as $field => $value) {
            if (($payload[$field] ?? null) !== $value) {
                $errors[] = 'SUCCESSOR_EVIDENCE_WRONG_'.strtoupper($field);
            }
        }
        if (($payload['mutability'] ?? '') !== 'IMMUTABLE_AFTER_ISSUE') {
            $errors[] = 'SUCCESSOR_EVIDENCE_NOT_IMMUTABLE';
        }
        $counts = $payload['result_counts'] ?? [];
        if ((int) ($counts['PROVEN_CURRENT'] ?? -1) !== count($affected)) {
            $errors[] = 'SUCCESSOR_EVIDENCE_PROVEN_COUNT_MISMATCH';
        }
        foreach (['INCOMPLETE', 'BLOCKED', 'AUTHORITY_GAP'] as $bad) {
            if ((int) ($counts[$bad] ?? -1) !== 0) {
                $errors[] = 'SUCCESSOR_EVIDENCE_HAS_'.$bad;
            }
        }
        $matrixRules = [];
        foreach ((array) ($payload['matrix'] ?? []) as $row) {
            $rule = (string) ($row['rule_id'] ?? '');
            if (isset($matrixRules[$rule])) {
                $errors[] = 'SUCCESSOR_EVIDENCE_DUPLICATE_RULE: '.$rule;
            }
            $matrixRules[$rule] = true;
            if (($row['result'] ?? '') !== 'PROVEN_CURRENT') {
                $errors[] = 'SUCCESSOR_EVIDENCE_RULE_NOT_PROVEN: '.$rule.'='.($row['result'] ?? '');
            }
        }
        $missing = array_diff($affected, array_keys($matrixRules));
        $extra = array_diff(array_keys($matrixRules), $affected);
        if ($missing !== []) {
            $errors[] = 'SUCCESSOR_EVIDENCE_MISSING_RULES: '.implode(',', array_slice($missing, 0, 5));
        }
        if ($extra !== []) {
            $errors[] = 'SUCCESSOR_EVIDENCE_EXTRA_RULES: '.implode(',', array_slice($extra, 0, 5));
        }
        if ((int) ($payload['denominator']['count'] ?? 0) !== count($affected)) {
            $errors[] = 'SUCCESSOR_EVIDENCE_DENOMINATOR_MISMATCH';
        }
        if (! in_array($profile['scope_evidence'], (array) ($payload['related_evidence'] ?? []), true)) {
            $errors[] = 'SUCCESSOR_EVIDENCE_NOT_CORRELATED_TO_SCOPE';
        }
        if (! preg_match('/^[a-f0-9]{64}$/', (string) ($payload['raw_proof']['manifest_sha256'] ?? ''))
            || trim((string) ($payload['raw_proof']['package'] ?? '')) === '') {
            $errors[] = 'SUCCESSOR_EVIDENCE_RAW_LINKAGE_MISSING';
        }

        return $errors;
    }

    /**
     * The raw proof package named by the evidence must still be the package it was issued with.
     * $full re-hashes every member; without it only the manifest is verified.
     *
     * @return array<int,string>
     */
    public static function verifyPackage(array $payload, array $paths, bool $full): array
    {
        $errors = [];
        $relative = (string) ($payload['raw_proof']['package'] ?? '');
        $dir = rtrim($paths['package_base'], '/').'/'.$relative;
        $manifestPath = $dir.'/manifest.json';
        if ($relative === '' || ! is_file($manifestPath)) {
            return ['PACKAGE_MANIFEST_MISSING'];
        }
        if (! hash_equals((string) ($payload['raw_proof']['manifest_sha256'] ?? ''), (string) hash_file('sha256', $manifestPath))) {
            return ['PACKAGE_MANIFEST_HASH_MISMATCH'];
        }
        if (! $full) {
            return [];
        }
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest) || ! is_array($manifest['files'] ?? null) || $manifest['files'] === []) {
            return ['PACKAGE_MANIFEST_INVALID'];
        }
        $bad = 0;
        foreach ($manifest['files'] as $file => $meta) {
            $path = $dir.'/'.$file;
            if (! is_file($path) || ! hash_equals((string) $meta['sha256'], (string) hash_file('sha256', $path))) {
                $bad++;
            }
        }
        if ($bad > 0) {
            $errors[] = 'PACKAGE_MEMBER_HASH_MISMATCH: '.$bad;
        }
        $onDisk = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $entry) {
            if ($entry->isFile() && str_replace('\\', '/', substr($entry->getPathname(), strlen($dir) + 1)) !== 'manifest.json') {
                $onDisk++;
            }
        }
        if ($onDisk !== count($manifest['files'])) {
            $errors[] = 'PACKAGE_MEMBER_SET_CHANGED: manifest='.count($manifest['files']).' disk='.$onDisk;
        }

        return $errors;
    }

    /** @return array<int,array<string,string>> */
    public static function loadRelationships(string $path): array
    {
        $handle = @fopen($path, 'r');
        if (! $handle) {
            return [];
        }
        $headers = fgetcsv($handle);
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);

        return $rows;
    }

    /** @return array<int,string> */
    public static function verifyRelationships(array $relationships, string $evidenceId, array $profile): array
    {
        $errors = [];
        $required = [$profile['baseline'] => false, $profile['change_impact'] => false, $profile['scope_evidence'] => false];
        foreach ($relationships as $r) {
            if (($r['source_record_id'] ?? '') === $evidenceId && array_key_exists($r['target_record_id'] ?? '', $required)) {
                $required[$r['target_record_id']] = true;
            }
        }
        foreach ($required as $target => $found) {
            if (! $found) {
                $errors[] = 'SUCCESSOR_EVIDENCE_RELATIONSHIP_MISSING: '.$target;
            }
        }

        return $errors;
    }

    /**
     * Full admissibility check and the exact rewrite plan. Nothing is written.
     *
     * Options: matrix, evidence_dir, relationships, package_base (path overrides for temporary copies);
     * full_package (bool, default true); post_gates (callable(array $rows): array<int,string>) run on the
     * planned post-state rows.
     *
     * @return array<string,mixed>
     */
    public static function plan(string $root, string $attempt, string $evidenceId, array $o = []): array
    {
        $errors = [];
        $paths = self::paths($root, $o);
        $profile = self::profile($attempt);
        $result = ['status' => 'FAIL', 'attempt' => $attempt, 'evidence_id' => $evidenceId, 'errors' => [], 'diagnostics' => [],
            'changed_rules' => [], 'new_raw' => null, 'before_sha256' => null, 'after_sha256' => null, 'already_bound' => false];

        if ($profile === null) {
            $result['errors'] = ['ATTEMPT_NOT_REGISTERED: '.$attempt];

            return $result;
        }
        if (! preg_match($profile['evidence_pattern'], $evidenceId)) {
            $errors[] = 'EVIDENCE_ID_NOT_A_'.$attempt.'_EVIDENCE: '.$evidenceId;
        }

        $matrix = self::readMatrix($paths['matrix']);
        foreach ($matrix['errors'] as $e) {
            $errors[] = $e;
        }
        if ($matrix['errors'] !== []) {
            $result['errors'] = $errors;

            return $result;
        }
        $result['before_sha256'] = hash('sha256', $matrix['raw']);

        $scope = self::loadScope($paths, $profile, $errors);
        $affected = array_flip($scope['affected']);
        $evidence = self::loadEvidence($paths['evidence_dir'], $evidenceId, $errors);
        if (is_array($evidence['payload'])) {
            foreach (self::validateSuccessorEvidence($evidence['payload'], $evidenceId, $profile, $scope['affected']) as $e) {
                $errors[] = $e;
            }
            foreach (self::verifyPackage($evidence['payload'], $paths, $o['full_package'] ?? true) as $e) {
                $errors[] = $e;
            }
        }
        foreach (self::verifyRelationships(self::loadRelationships($paths['relationships']), $evidenceId, $profile) as $e) {
            $errors[] = $e;
        }

        // Matrix state, row by row.
        $mandatory = [];
        $seen = [];
        $affectedState = ['NOT_ASSESSED' => 0, 'SATISFIED_ON_SUCCESSOR' => 0, 'other' => 0];
        $unaffectedBad = 0;
        foreach ($matrix['rows'] as $index => $row) {
            $rule = (string) $row['rule_id'];
            if (isset($seen[$rule])) {
                $errors[] = 'MATRIX_DUPLICATE_RULE: '.$rule;
            }
            $seen[$rule] = true;
            $isMandatory = $row['active'] === 'YES' && $row['primary_stage'] === self::STAGE
                && $row['coverage_requirement'] === 'REQUIRED' && $row['applicability'] === 'MANDATORY';
            if (! $isMandatory) {
                if (isset($affected[$rule])) {
                    $errors[] = 'AFFECTED_RULE_NOT_AN_ACTIVE_B10_MANDATORY_ROW: '.$rule;
                }
                continue;
            }
            $mandatory[$rule] = $index;
            $status = $row['coverage_status'];
            $bound = trim($row['current_evidence_ids']);
            if (isset($affected[$rule])) {
                if ($status === 'NOT_ASSESSED' && $bound === '') {
                    $affectedState['NOT_ASSESSED']++;
                } elseif ($status === 'SATISFIED' && $bound === $evidenceId) {
                    $affectedState['SATISFIED_ON_SUCCESSOR']++;
                } else {
                    $affectedState['other']++;
                    $errors[] = 'AFFECTED_ROW_UNEXPECTED_STATE: '.$rule.' '.$status.' ['.$bound.']';
                }
                $required = $scope['review'][$rule]['required_current_status'] ?? null;
                if ($required !== 'NOT_ASSESSED') {
                    $errors[] = 'SCOPE_RECORD_DOES_NOT_REOPEN_RULE: '.$rule.'='.var_export($required, true);
                }
            } else {
                $expected = (string) ($scope['review'][$rule]['current_evidence_binding'] ?? '');
                $required = $scope['review'][$rule]['required_current_status'] ?? null;
                if ($status !== 'SATISFIED' || $required !== 'SATISFIED' || $bound !== $expected || ! preg_match(self::PREDECESSOR_PATTERN, $bound)) {
                    $unaffectedBad++;
                    if ($unaffectedBad <= 5) {
                        $errors[] = 'UNAFFECTED_ROW_UNEXPECTED_STATE: '.$rule.' '.$status.' ['.$bound.'] scope='.var_export($required, true).'/'.$expected;
                    }
                }
            }
        }
        if ($unaffectedBad > 5) {
            $errors[] = 'UNAFFECTED_ROWS_UNEXPECTED_STATE_TOTAL: '.$unaffectedBad;
        }
        if (count($mandatory) !== self::EXPECTED_DENOMINATOR) {
            $errors[] = 'DENOMINATOR_MISMATCH: '.count($mandatory);
        }
        $missingFromMatrix = array_diff(array_keys($affected), array_keys($mandatory));
        if ($missingFromMatrix !== []) {
            $errors[] = 'AFFECTED_RULES_NOT_IN_MATRIX: '.implode(',', array_slice($missingFromMatrix, 0, 5));
        }
        $extraReview = array_diff(array_keys($mandatory), array_keys($scope['review']));
        if ($extraReview !== []) {
            $errors[] = 'SCOPE_RECORD_MISSING_RULES: '.count($extraReview);
        }
        $already = $affectedState['SATISFIED_ON_SUCCESSOR'];
        if ($already > 0 && $already !== count($affected)) {
            $errors[] = 'PARTIAL_PROMOTION_STATE: '.$already.' of '.count($affected).' already bound';
        }

        $result['diagnostics'] = [
            'denominator' => count($mandatory),
            'affected' => count($affected),
            'affected_not_assessed' => $affectedState['NOT_ASSESSED'],
            'affected_already_on_successor' => $affectedState['SATISFIED_ON_SUCCESSOR'],
            'unaffected_satisfied_on_predecessor' => count($mandatory) - count($affected) - $unaffectedBad,
            'unaffected_unexpected' => $unaffectedBad,
            'matrix_lines' => count($matrix['lines']),
        ];

        if ($errors !== []) {
            $result['errors'] = array_values(array_unique($errors));

            return $result;
        }
        if ($already === count($affected)) {
            $result['status'] = 'PASS';
            $result['already_bound'] = true;
            $result['after_sha256'] = $result['before_sha256'];

            return $result;
        }

        // Build the successor state. Untouched lines stay byte-for-byte.
        $newLines = $matrix['lines'];
        $changed = [];
        $notePrefix = ' | '.$attempt.': proof_binding='.$evidenceId;
        foreach ($matrix['rows'] as $index => $row) {
            $rule = (string) $row['rule_id'];
            if (! isset($affected[$rule])) {
                continue;
            }
            $style = self::detectStyle($matrix['lines'][$index]);
            if ($style === null) {
                $errors[] = 'AFFECTED_LINE_DOES_NOT_ROUND_TRIP: '.$rule;
                continue;
            }
            $family = (string) ($scope['review'][$rule]['proof_family'] ?? '');
            $new = $row;
            $new['coverage_status'] = 'SATISFIED';
            $new['current_evidence_ids'] = $evidenceId;
            $new['notes'] = trim((string) $row['notes']).$notePrefix
                .'; proof_family='.$family
                .'; supersedes_prior_binding='.trim((string) ($scope['review'][$rule]['prior_evidence'] ?? ''))
                .'; scope_evidence='.$profile['scope_evidence']
                .'; proof_chain=current authority -> successor implementation -> mutation-proven member and behaviour proof -> successor full suite -> governed evidence';
            $newLines[$index] = self::encodeLine(array_values($new), $style);
            $changed[$rule] = true;
        }
        if ($errors !== []) {
            $result['errors'] = array_values(array_unique($errors));

            return $result;
        }

        $newRaw = ($matrix['bom'] ? "\xEF\xBB\xBF" : '').$matrix['header_line']."\n".implode("\n", $newLines)."\n";
        $newRows = [];
        foreach ($newLines as $line) {
            $newRows[] = array_combine($matrix['headers'], str_getcsv($line));
        }

        // The rewrite may differ from the original only in the affected lines and only in the three fields.
        $changedLines = 0;
        foreach ($matrix['rows'] as $index => $row) {
            if ($newLines[$index] === $matrix['lines'][$index]) {
                continue;
            }
            $changedLines++;
            $rule = (string) $row['rule_id'];
            if (! isset($changed[$rule])) {
                $errors[] = 'UNEXPECTED_LINE_CHANGE: '.$rule;
            }
            foreach ($matrix['headers'] as $field) {
                if (! in_array($field, self::CHANGED_FIELDS, true) && $newRows[$index][$field] !== $row[$field]) {
                    $errors[] = 'UNEXPECTED_FIELD_CHANGE: '.$rule.'.'.$field;
                }
            }
        }
        if ($changedLines !== count($affected) || count($changed) !== count($affected)) {
            $errors[] = 'CHANGED_LINE_COUNT_MISMATCH: '.$changedLines.' lines, '.count($changed).' rules, expected '.count($affected);
        }
        if (count($newLines) !== count($matrix['lines'])) {
            $errors[] = 'LINE_COUNT_CHANGED';
        }
        $afterBound = 0;
        foreach ($newRows as $row) {
            if ($row['active'] === 'YES' && $row['primary_stage'] === self::STAGE && $row['coverage_requirement'] === 'REQUIRED'
                && $row['applicability'] === 'MANDATORY' && $row['coverage_status'] === 'SATISFIED') {
                $afterBound++;
            }
        }
        if ($afterBound !== self::EXPECTED_DENOMINATOR) {
            $errors[] = 'POST_STATE_NOT_FULLY_SATISFIED: '.$afterBound;
        }
        if (isset($o['post_gates']) && is_callable($o['post_gates'])) {
            foreach (call_user_func($o['post_gates'], $newRows) as $e) {
                $errors[] = 'POST_STATE_GATE: '.$e;
            }
        }

        $result['diagnostics']['planned_changed_lines'] = $changedLines;
        $result['diagnostics']['post_state_satisfied'] = $afterBound;
        if ($errors !== []) {
            $result['errors'] = array_values(array_unique($errors));

            return $result;
        }
        $result['status'] = 'PASS';
        $result['changed_rules'] = array_keys($changed);
        $result['new_raw'] = $newRaw;
        $result['after_sha256'] = hash('sha256', $newRaw);

        return $result;
    }

    /**
     * All-or-nothing write of a PASSing plan. Options: matrix path, canonical (bool: forbids failure injection),
     * inject (test only: after_temp | after_rename | after_readback), verify (callable(): array<int,string>)
     * run against the written file.
     *
     * @return array<string,mixed>
     */
    public static function apply(array $plan, array $paths, array $o = []): array
    {
        $out = ['applied' => false, 'rolled_back' => false, 'errors' => []];
        if (($plan['status'] ?? '') !== 'PASS') {
            $out['errors'][] = 'PLAN_NOT_ADMISSIBLE';

            return $out;
        }
        if (($plan['already_bound'] ?? false) === true || $plan['new_raw'] === null) {
            return $out + ['note' => 'already bound; nothing to write'];
        }
        $inject = $o['inject'] ?? null;
        if ($inject !== null && (($o['canonical'] ?? true) === true || getenv('MD_B10_BINDER_TEST') !== '1')) {
            $out['errors'][] = 'FAILURE_INJECTION_REFUSED_ON_CANONICAL_MATRIX';

            return $out;
        }
        $target = $paths['matrix'];
        $original = (string) file_get_contents($target);
        if (! hash_equals((string) $plan['before_sha256'], hash('sha256', $original))) {
            $out['errors'][] = 'MATRIX_CHANGED_SINCE_PLAN';

            return $out;
        }
        $temp = $target.'.tmp.'.getmypid();
        $restore = static function () use ($target, $original, &$out) {
            $back = $target.'.restore.'.getmypid();
            file_put_contents($back, $original);
            rename($back, $target);
            $out['rolled_back'] = hash('sha256', (string) file_get_contents($target)) === hash('sha256', $original);
        };
        try {
            if (file_put_contents($temp, $plan['new_raw']) === false || ! hash_equals($plan['after_sha256'], (string) hash_file('sha256', $temp))) {
                throw new RuntimeException('TEMP_FILE_NOT_VERIFIED');
            }
            if ($inject === 'after_temp') {
                throw new RuntimeException('INJECTED_FAILURE_AFTER_TEMP');
            }
            if (! rename($temp, $target)) {
                throw new RuntimeException('REPLACE_FAILED');
            }
            if ($inject === 'after_rename') {
                throw new RuntimeException('INJECTED_FAILURE_AFTER_RENAME');
            }
            if (! hash_equals($plan['after_sha256'], (string) hash_file('sha256', $target))) {
                throw new RuntimeException('READBACK_HASH_MISMATCH');
            }
            if ($inject === 'after_readback') {
                throw new RuntimeException('INJECTED_FAILURE_AFTER_READBACK');
            }
            if (isset($o['verify']) && is_callable($o['verify'])) {
                $errors = call_user_func($o['verify']);
                if ($errors !== []) {
                    throw new RuntimeException('POST_WRITE_VERIFICATION_FAILED: '.implode('; ', array_slice($errors, 0, 5)));
                }
            }
            $out['applied'] = true;
        } catch (Throwable $e) {
            $out['errors'][] = $e->getMessage();
            if (is_file($temp)) {
                @unlink($temp);
            }
            if (hash('sha256', (string) @file_get_contents($target)) !== hash('sha256', $original)) {
                $restore();
            }
        }

        return $out;
    }
}
