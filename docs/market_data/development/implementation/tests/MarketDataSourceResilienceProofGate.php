<?php

require_once __DIR__.'/MarketDataSourceResilienceTraceabilityGate.php';
require_once __DIR__.'/MarketDataSourceResilienceProofSpec.php';

/**
 * MD-B08 proof gate. Two modes, and they must not agree with each other.
 *
 *   (no flag)  readiness: the stage has not yet returned local runtime proof, so every predicate
 *              must still be NOT_ASSESSED with no evidence.
 *   --bound    closed: every predicate must be SATISFIED and bound to the exact governed evidence
 *              of the attempt that proved it.
 *
 * The entrypoint previously called readiness unconditionally and never read `$argv`, so `--bound`
 * was accepted and ignored and the gate reported `premature_satisfied: 139` against the legitimately
 * closed stage. `validateBound()` existed but nothing called it, and it took a single evidence id,
 * which cannot express the real closed state: `MD-B08-A002` re-proved one predicate, so the stage is
 * bound to two governed records. A gate that can only ever report FAIL detects no regression.
 */
final class MarketDataSourceResilienceProofGate
{
    /** Any governed MD-B08 evidence id. A bound row must name one of these and nothing else. */
    public const EVIDENCE_PATTERN = '/^E-MD-B08-A\\d{3}-\\d{3}$/';

    public const PRIMARY_EVIDENCE = 'E-MD-B08-A001-001';

    public const REMEDIATION_EVIDENCE = 'E-MD-B08-A002-001';

    /**
     * Which governed evidence each predicate must be bound to, derived from the traceability spec
     * rather than read back from the matrix. Reading the expectation out of the thing being checked
     * is how a gate agrees with whatever it finds.
     *
     * @return array<string,string>
     */
    public static function expectedBindings(array $map = null): array
    {
        $map = $map ?? MarketDataSourceResilienceProofSpec::proofMap();
        $remediated = MarketDataSourceResilienceTraceabilitySpec::REMEDIATED_RULES;

        $expected = [];
        foreach (array_keys($map) as $rule) {
            $expected[$rule] = isset($remediated[$rule])
                ? self::REMEDIATION_EVIDENCE
                : self::PRIMARY_EVIDENCE;
        }

        return $expected;
    }

    /**
     * Proof surfaces and methods named by the map must exist in both modes. A bound predicate whose
     * guard method no longer exists is a false claim, not a historical one.
     *
     * @param array<string,array{surfaces:array<int,string>,methods:array<int,array<int,string>>}> $map
     * @return array{surface_missing:int,method_missing:int,errors:array<int,string>}
     */
    private static function validateProofSurfaces(array $map, string $root): array
    {
        $errors = [];
        $surfaceMissing = 0;
        $methodMissing = 0;
        $seenSurfaces = [];
        $seenMethods = [];

        foreach ($map as $rule => $proof) {
            foreach ($proof['surfaces'] as $relative) {
                if (isset($seenSurfaces[$relative])) {
                    continue;
                }
                $seenSurfaces[$relative] = true;
                if (! is_file($root.'/'.$relative)) {
                    $surfaceMissing++;
                    $errors[] = $rule.': missing implementation surface '.$relative;
                }
            }
            foreach ($proof['methods'] as [$relative, $method]) {
                $key = $relative.'::'.$method;
                if (isset($seenMethods[$key])) {
                    continue;
                }
                $seenMethods[$key] = true;
                $path = $root.'/'.$relative;
                if (! is_file($path)) {
                    $methodMissing++;
                    $errors[] = $rule.': missing proof test file '.$relative;
                    continue;
                }
                if (strpos((string) file_get_contents($path), 'function '.$method.'(') === false) {
                    $methodMissing++;
                    $errors[] = $rule.': missing proof method '.$key;
                }
            }
        }

        return ['surface_missing' => $surfaceMissing, 'method_missing' => $methodMissing, 'errors' => $errors];
    }

    /** @param array<int,array<string,string>> $rows */
    public static function validateReadiness(array $rows, string $root, array $map = null): array
    {
        $map = $map ?? MarketDataSourceResilienceProofSpec::proofMap();
        $required = [];
        foreach ($rows as $row) {
            if ($row['primary_stage'] === MarketDataSourceResilienceTraceabilitySpec::STAGE
                && $row['coverage_requirement'] === 'REQUIRED') {
                $required[$row['rule_id']] = $row;
            }
        }
        ksort($required, SORT_STRING);

        $errors = [];
        $counts = [
            'denominator' => count($required),
            'proof_map_count' => count($map),
            'runtime_pending' => 0,
            'premature_satisfied' => 0,
            'surface_missing' => 0,
            'method_missing' => 0,
            'reason_code_scope_errors' => 0,
            'implementation_invariant_errors' => 0,
        ];

        if (count($required) !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
            $errors[] = 'DENOMINATOR: expected '.MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR.' got '.count($required);
        }
        if (array_keys($required) !== array_keys($map)) {
            $errors[] = 'PROOF_MAP: map must exactly cover the current MD-B08 denominator';
        }

        $reasonScope = self::validateB08ReasonCodeScope($root);
        if ($reasonScope['status'] !== 'PASS') {
            $counts['reason_code_scope_errors'] = count($reasonScope['errors']);
            foreach ($reasonScope['errors'] as $error) {
                $errors[] = 'B08_REASON_SCOPE: '.$error;
            }
        }

        $implementation = self::validateImplementationInvariants($root);
        if ($implementation['status'] !== 'PASS') {
            $counts['implementation_invariant_errors'] = count($implementation['errors']);
            foreach ($implementation['errors'] as $error) {
                $errors[] = 'B08_IMPLEMENTATION: '.$error;
            }
        }

        foreach ($required as $rule => $row) {
            if ($row['coverage_status'] !== 'NOT_ASSESSED' || trim((string) $row['current_evidence_ids']) !== '') {
                $counts['premature_satisfied']++;
                $errors[] = $rule.': runtime-dependent B08 predicate must remain NOT_ASSESSED with no evidence before returned local proof';
            } else {
                $counts['runtime_pending']++;
            }
        }

        $surfaces = self::validateProofSurfaces($map, $root);
        $counts['surface_missing'] = $surfaces['surface_missing'];
        $counts['method_missing'] = $surfaces['method_missing'];
        foreach ($surfaces['errors'] as $error) {
            $errors[] = $error;
        }

        return ['status' => $errors === [] ? 'PASS' : 'FAIL', 'mode' => 'READINESS',
            'counts' => $counts, 'errors' => $errors];
    }

    /**
     * Standalone static fail-closed checks for B08 implementation invariants that must remain
     * true before local runtime proof can be accepted. Source overrides exist only for mutation
     * self-tests; production execution reads the repository files directly.
     *
     * @param array<string,string> $sourceOverrides repository-relative path => mutated source
     * @return array{status:string,errors:array<int,string>}
     */
    public static function validateImplementationInvariants(string $root, array $sourceOverrides = []): array
    {
        $errors = [];
        $read = static function (string $relative) use ($root, $sourceOverrides, &$errors): string {
            if (array_key_exists($relative, $sourceOverrides)) {
                return (string) $sourceOverrides[$relative];
            }
            $path = $root.'/'.$relative;
            if (! is_file($path)) {
                $errors[] = 'missing implementation surface '.$relative;
                return '';
            }
            return (string) file_get_contents($path);
        };

        $adapterPath = 'app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php';
        $adapter = $read($adapterPath);
        $pipeline = $read('app/Application/MarketData/Services/MarketDataPipelineService.php');
        $evidenceExport = $read('app/Application/MarketData/Services/MarketDataEvidenceExportService.php');

        if (strpos($adapter, "config('market_data.provider.circuit_breaker_error_rate'") === false
            || strpos($adapter, '($failureCount / $universeCount) > $threshold') === false) {
            $errors[] = 'breaker must use the registered configured threshold with strict crossing';
        }
        foreach (['minimumAttempts', 'minimum_sample', 'minimumSample'] as $forbidden) {
            if (strpos($adapter, $forbidden) !== false) {
                $errors[] = 'hidden breaker sample floor detected: '.$forbidden;
            }
        }
        if (strpos($adapter, 'if ($universeCount <= 0)') === false) {
            $errors[] = 'breaker must guard only an empty planned acquisition universe before applying the configured ratio';
        }
        if (strpos($adapter, "config('market_data.provider.api_retry_max')") === false
            || strpos($adapter, 'min(3') !== false
            || strpos($pipeline, 'min(3') !== false) {
            $errors[] = 'effective retry budget must come from registered api_retry_max without hidden min(3) clamp';
        }
        if (strpos($adapter, 'RUN_SOURCE_CIRCUIT_BREAKER_OPEN') !== false) {
            $errors[] = 'breaker state may not become an unregistered terminal reason code';
        }
        if (strpos($adapter, "in_array(\$sourceMode, ['api', 'api_free'], true)") === false
            || strpos($adapter, "\$isPrimaryApiSource ? 'PRIMARY' : 'SECONDARY_CONTROLLED_RECOVERY'") === false
            || strpos($adapter, "\$isPrimaryApiSource ? 'api_free' : \$sourceMode") === false) {
            $errors[] = 'api/api_free must remain the PRIMARY acquisition source with canonical active decision api_free';
        }
        if (strpos($pipeline, "\$payload['active_source_decision'] = 'api_free';") === false) {
            $errors[] = 'pipeline API source telemetry must expose canonical active-source decision api_free';
        }

        $sourceSummary = self::extractMethodSource($evidenceExport, 'buildSourceSummaryString');
        foreach (['source_priority', 'active_source_decision', 'retry_attempt_count', 'failure_class_summary'] as $field) {
            if ($sourceSummary === null || strpos($sourceSummary, "'".$field."'") === false) {
                $errors[] = 'run evidence source summary missing required audit-visible field '.$field;
            }
        }

        foreach (['fetchYahooFinanceBars', 'fetchYahooFinanceBarsRange', 'fetchOrLoadBenchmarkBars'] as $method) {
            $body = self::extractMethodSource($adapter, $method);
            if ($body === null || preg_match('/\bcircuitBreakerTelemetry\s*\(/', $body) !== 1) {
                $errors[] = $method.' missing circuit-breaker protection';
            }
            if ($body === null || strpos($body, 'requestWithRetry') === false) {
                $errors[] = $method.' missing shared retry/throttle transport';
            }
        }

        $auditFields = ['source_priority', 'active_source_decision', 'retry_attempt_count', 'failure_class_summary'];
        $protectionFields = ['source_protection_state', 'attempted_acquisition_unit_count', 'unattempted_acquisition_unit_count', 'circuit_breaker_trigger_reason_code'];
        foreach ([
            $adapterPath,
            'app/Infrastructure/MarketData/Source/LocalFileEodBarsAdapter.php',
            'app/Application/MarketData/Services/MarketDataPipelineService.php',
            'app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php',
            'app/Application/MarketData/Services/MarketDataEvidenceExportService.php',
        ] as $relative) {
            $source = $relative === $adapterPath ? $adapter : $read($relative);
            foreach ($auditFields as $field) {
                if (strpos($source, "'".$field."'") === false) {
                    $errors[] = $relative.' missing required audit field '.$field;
                }
            }
        }
        foreach ([
            'app/Application/MarketData/Services/MarketDataBackfillService.php',
            'app/Console/Commands/MarketData/AbstractMarketDataCommand.php',
            'app/Application/MarketData/Services/SectorIndexApiIngestService.php',
            'app/Console/Commands/MarketData/IngestSectorIndexBarsApiCommand.php',
        ] as $relative) {
            $source = $read($relative);
            foreach (array_merge($auditFields, $protectionFields) as $field) {
                if (strpos($source, "'".$field."'") === false) {
                    $errors[] = $relative.' missing operational resilience projection '.$field;
                }
            }
        }

        $evidenceRepository = $read('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        foreach (['payload_hash', 'schema_fingerprint', 'validation_state', 'md_source_observation_rejected_rows', 'source_observation_rejection_reason_summary'] as $needle) {
            if (strpos($evidenceRepository, $needle) === false) {
                $errors[] = 'existing immutable observation truth missing audit projection '.$needle;
            }
        }

        return ['status' => $errors === [] ? 'PASS' : 'FAIL', 'errors' => $errors];
    }

    private static function extractMethodSource(string $source, string $method): ?string
    {
        if (! preg_match('/(?:public|protected|private) function '.preg_quote($method, '/').'\s*\([^)]*\)\s*(?::\s*[^\s{]+)?\s*\{/m', $source, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = $match[0][1];
        $brace = strpos($source, '{', $start);
        if ($brace === false) {
            return null;
        }
        $depth = 0;
        $length = strlen($source);
        for ($i = $brace; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }
        return null;
    }

    /**
     * B08 adds a deliberately narrow scanner for RUN_* values returned by the source adapter.
     * It must catch an unregistered breaker/root-cause return without sweeping downstream
     * publication/backfill state vocabulary into B08.
     *
     * @return array{status:string,returned_codes:array<int,string>,errors:array<int,string>}
     */
    public static function validateB08ReasonCodeScope(string $root, string $adapterSource = null): array
    {
        $adapterPath = $root.'/app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php';
        $registryPath = $root.'/docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md';
        $seedPath = $root.'/docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql';
        $errors = [];

        foreach ([$adapterPath, $registryPath, $seedPath] as $path) {
            if (! is_file($path)) {
                $errors[] = 'missing required reason-code proof surface '.$path;
            }
        }
        if ($errors !== []) {
            return ['status' => 'FAIL', 'returned_codes' => [], 'errors' => $errors];
        }

        $adapterSource = $adapterSource ?? (string) file_get_contents($adapterPath);
        $returned = [];
        foreach ([
            "/return '(RUN_[A-Z0-9_]{3,})'/",
            "/\\?\\s*'(RUN_[A-Z0-9_]{3,})'/",
        ] as $pattern) {
            if (preg_match_all($pattern, $adapterSource, $matches)) {
                foreach ($matches[1] as $code) {
                    $returned[$code] = true;
                }
            }
        }
        ksort($returned, SORT_STRING);

        $registry = (string) file_get_contents($registryPath);
        $seed = (string) file_get_contents($seedPath);
        foreach (array_keys($returned) as $code) {
            if (strpos($registry, $code) === false) {
                $errors[] = $code.' missing from canonical reason-code registry';
            }
            if (strpos($seed, "'".$code."'") === false && strpos($seed, '"'.$code.'"') === false) {
                $errors[] = $code.' missing from reason-code seed';
            }
        }

        if (strpos($adapterSource, 'RUN_SOURCE_CIRCUIT_BREAKER_OPEN') !== false) {
            $errors[] = 'breaker state must be telemetry and may not reintroduce RUN_SOURCE_CIRCUIT_BREAKER_OPEN';
        }

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'returned_codes' => array_keys($returned),
            'errors' => $errors,
        ];
    }

    /**
     * Closed-stage mode. Every predicate must be SATISFIED and bound to the exact governed evidence
     * of the attempt that proved it, and the static invariants the proof rests on must still hold.
     *
     * @param  array<int,array<string,string>>  $rows
     * @param  array<string,string>|null  $expected  rule id => required evidence id
     */
    public static function validateBound(array $rows, string $root, array $expected = null, array $map = null): array
    {
        $map = $map ?? MarketDataSourceResilienceProofSpec::proofMap();
        $expected = $expected ?? self::expectedBindings($map);
        $errors = [];
        $counts = [
            'denominator' => 0,
            'proof_map_count' => count($map),
            'satisfied' => 0,
            'unbound' => 0,
            'wrong_evidence' => 0,
            'surface_missing' => 0,
            'method_missing' => 0,
            'reason_code_scope_errors' => 0,
            'implementation_invariant_errors' => 0,
        ];

        $boundIds = [];
        foreach ($rows as $row) {
            if (! isset($map[$row['rule_id']])) {
                continue;
            }
            $counts['denominator']++;
            $rule = $row['rule_id'];
            $evidence = trim((string) $row['current_evidence_ids']);

            if ($row['primary_stage'] !== MarketDataSourceResilienceTraceabilitySpec::STAGE
                || $row['coverage_requirement'] !== 'REQUIRED') {
                $counts['unbound']++;
                $errors[] = $rule.': mapped predicate is not an active MD-B08 required rule';
                continue;
            }
            if ($row['coverage_status'] !== 'SATISFIED') {
                $counts['unbound']++;
                $errors[] = $rule.': closed-stage predicate is '.$row['coverage_status'].', expected SATISFIED';
                continue;
            }
            if ($evidence !== $expected[$rule]) {
                $counts['wrong_evidence']++;
                $errors[] = $rule.': bound to '.($evidence === '' ? '(none)' : $evidence)
                    .', expected '.$expected[$rule];
                continue;
            }
            $boundIds[$evidence] = true;
            $counts['satisfied']++;
        }

        if ($counts['denominator'] !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
            $errors[] = 'BOUND_DENOMINATOR: expected '
                .MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR
                .' got '.$counts['denominator'];
        }
        if (count($map) !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
            $errors[] = 'PROOF_MAP: map must exactly cover the current MD-B08 denominator';
        }

        // Every distinct evidence id must be a governed MD-B08 record that actually exists. A
        // binding to an id nobody issued is worse than an unbound row: it reads as proven.
        foreach (array_keys($boundIds) as $evidenceId) {
            if (preg_match(self::EVIDENCE_PATTERN, $evidenceId) !== 1) {
                $errors[] = 'EVIDENCE: '.$evidenceId.' is not a governed MD-B08 evidence id';
                continue;
            }
            $matches = glob($root.'/docs/market_data/records/evidence/'.$evidenceId.'_*');
            if (! is_array($matches) || count($matches) !== 1) {
                $errors[] = 'EVIDENCE: expected exactly one governed evidence file for '.$evidenceId;
            }
        }
        $counts['evidence_records'] = count($boundIds);

        $surfaces = self::validateProofSurfaces($map, $root);
        $counts['surface_missing'] = $surfaces['surface_missing'];
        $counts['method_missing'] = $surfaces['method_missing'];
        foreach ($surfaces['errors'] as $error) {
            $errors[] = $error;
        }

        // The static invariants are not historical facts. They must still hold, or the closed
        // proof is describing an implementation that no longer exists.
        $reasonScope = self::validateB08ReasonCodeScope($root);
        if ($reasonScope['status'] !== 'PASS') {
            $counts['reason_code_scope_errors'] = count($reasonScope['errors']);
            foreach ($reasonScope['errors'] as $error) {
                $errors[] = 'B08_REASON_SCOPE: '.$error;
            }
        }
        $implementation = self::validateImplementationInvariants($root);
        if ($implementation['status'] !== 'PASS') {
            $counts['implementation_invariant_errors'] = count($implementation['errors']);
            foreach ($implementation['errors'] as $error) {
                $errors[] = 'B08_IMPLEMENTATION: '.$error;
            }
        }

        return ['status' => $errors === [] ? 'PASS' : 'FAIL', 'mode' => 'BOUND',
            'counts' => $counts, 'errors' => $errors];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = MarketDataClassificationConsistencyGate::readMatrix($root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv');
    $bound = in_array('--bound', $argv, true);
    $result = $bound
        ? MarketDataSourceResilienceProofGate::validateBound($matrix['rows'], $root)
        : MarketDataSourceResilienceProofGate::validateReadiness($matrix['rows'], $root);
    $result['gate'] = 'MarketDataSourceResilienceProofGate';
    $result['stage_id'] = MarketDataSourceResilienceTraceabilitySpec::STAGE;
    $result['generated_at'] = date(DATE_ATOM);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
