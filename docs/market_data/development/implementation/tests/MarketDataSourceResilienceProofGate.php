<?php

require_once __DIR__.'/MarketDataSourceResilienceTraceabilityGate.php';
require_once __DIR__.'/MarketDataSourceResilienceProofSpec.php';

/** MD-B08-A001 proof-readiness gate. Runtime proof binding is intentionally forbidden pre-evidence. */
final class MarketDataSourceResilienceProofGate
{
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

        $seenSurfaces = [];
        $seenMethods = [];
        foreach ($map as $rule => $proof) {
            foreach ($proof['surfaces'] as $relative) {
                if (isset($seenSurfaces[$relative])) {
                    continue;
                }
                $seenSurfaces[$relative] = true;
                if (! is_file($root.'/'.$relative)) {
                    $counts['surface_missing']++;
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
                    $counts['method_missing']++;
                    $errors[] = $rule.': missing proof test file '.$relative;
                    continue;
                }
                $source = (string) file_get_contents($path);
                if (strpos($source, 'function '.$method.'(') === false) {
                    $counts['method_missing']++;
                    $errors[] = $rule.': missing proof method '.$key;
                }
            }
        }

        return ['status' => $errors === [] ? 'PASS' : 'FAIL', 'counts' => $counts, 'errors' => $errors];
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

    /** @param array<int,array<string,string>> $rows */
    public static function validateBound(array $rows, string $root, string $evidenceId, array $map = null): array
    {
        $map = $map ?? MarketDataSourceResilienceProofSpec::proofMap();
        $errors = [];
        $counts = ['denominator' => 0, 'satisfied' => 0, 'unbound' => 0];
        foreach ($rows as $row) {
            if (! isset($map[$row['rule_id']])) {
                continue;
            }
            $counts['denominator']++;
            if ($row['primary_stage'] !== MarketDataSourceResilienceTraceabilitySpec::STAGE
                || $row['coverage_requirement'] !== 'REQUIRED'
                || $row['coverage_status'] !== 'SATISFIED'
                || trim((string) $row['current_evidence_ids']) !== $evidenceId) {
                $counts['unbound']++;
                $errors[] = $row['rule_id'].': exact current B08 evidence binding missing';
            } else {
                $counts['satisfied']++;
            }
        }
        if ($counts['denominator'] !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
            $errors[] = 'BOUND_DENOMINATOR: expected '.MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR.' got '.$counts['denominator'];
        }

        $evidenceMatches = glob($root.'/docs/market_data/records/evidence/'.$evidenceId.'_*');
        if (! is_array($evidenceMatches) || count($evidenceMatches) !== 1) {
            $errors[] = 'EVIDENCE: expected exactly one governed evidence file for '.$evidenceId;
        }

        return ['status' => $errors === [] ? 'PASS' : 'FAIL', 'counts' => $counts, 'errors' => $errors];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = MarketDataClassificationConsistencyGate::readMatrix($root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv');
    $result = MarketDataSourceResilienceProofGate::validateReadiness($matrix['rows'], $root);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
