<?php

namespace App\Application\MarketData\Services;

/**
 * Read-only evaluation of the five declared release conditions. A passing
 * evaluation records only that these declared conditions had evidence; it
 * never asserts that their set is sufficient or that the release is safe.
 */
final class B19ReleaseAdmissionEvaluator
{
    public const CHECKS = [
        'contract_tests', 'golden_fixtures', 'replay_data_quality',
        'config_registry', 'downstream_safety',
    ];

    public function evaluate(array $bundle, string $currentBuild, string $evidenceRoot): array
    {
        $issues = [];
        if (($bundle['stage'] ?? null) !== 'MD-B19-A001') $issues[] = 'STAGE_IDENTITY_MISMATCH';
        if (($bundle['attempt'] ?? null) !== 'MD-B19-A001') $issues[] = 'ATTEMPT_IDENTITY_MISMATCH';
        if (! preg_match('/^[a-f0-9]{40}$/i', (string) ($bundle['build_sha'] ?? ''))
            || ! hash_equals(strtolower($currentBuild), strtolower((string) ($bundle['build_sha'] ?? '')))) {
            $issues[] = 'BUILD_IDENTITY_MISMATCH';
        }
        if (! empty($bundle['unresolved_dependencies'])) $issues[] = 'UNRESOLVED_DEPENDENCIES';
        if (! empty($bundle['failed_verifications'])) $issues[] = 'FAILED_VERIFICATIONS';
        $checks = is_array($bundle['checks'] ?? null) ? $bundle['checks'] : [];
        $root = realpath($evidenceRoot);
        if ($root === false || ! is_dir($root)) $issues[] = 'EVIDENCE_ROOT_UNAVAILABLE';
        $results = [];
        $seenRawPaths = [];
        foreach (self::CHECKS as $name) {
            $record = $checks[$name] ?? null;
            $reasons = [];
            if (! is_array($record)) {
                $reasons[] = 'CHECK_MISSING';
            } else {
                if (($record['stage'] ?? null) !== ($bundle['stage'] ?? null)
                    || ($record['attempt'] ?? null) !== ($bundle['attempt'] ?? null)
                    || ($record['build_sha'] ?? null) !== ($bundle['build_sha'] ?? null)) {
                    $reasons[] = 'CHECK_IDENTITY_MISMATCH';
                }
                if (($record['result'] ?? null) !== 'PASS' || ($record['exit_code'] ?? null) !== 0) {
                    $reasons[] = 'CHECK_NOT_PASSED';
                }
                if (trim((string) ($record['command'] ?? '')) === ''
                    || trim((string) ($record['executed_at'] ?? '')) === '') {
                    $reasons[] = 'EXECUTION_IDENTITY_MISSING';
                }
                $sourceRoot = realpath(base_path());
                $source = (string) ($record['source_file'] ?? '');
                $sourcePath = $sourceRoot !== false && $source !== ''
                    ? realpath($sourceRoot.DIRECTORY_SEPARATOR.$source) : false;
                if ($sourcePath === false || $sourceRoot === false
                    || strpos($sourcePath, $sourceRoot.DIRECTORY_SEPARATOR) !== 0 || ! is_file($sourcePath)) {
                    $reasons[] = 'SOURCE_IDENTITY_UNAVAILABLE';
                } elseif (! preg_match('/^[a-f0-9]{64}$/i', (string) ($record['source_sha256'] ?? ''))
                    || ! hash_equals(strtolower((string) $record['source_sha256']), hash_file('sha256', $sourcePath))) {
                    $reasons[] = 'SOURCE_HASH_MISMATCH';
                }
                $raw = (string) ($record['raw_file'] ?? '');
                $path = $root !== false && $raw !== '' ? realpath($root.DIRECTORY_SEPARATOR.$raw) : false;
                if ($path === false || $root === false
                    || strpos($path, $root.DIRECTORY_SEPARATOR) !== 0
                    || ! is_file($path) || filesize($path) === 0) {
                    $reasons[] = 'RAW_EXECUTION_UNAVAILABLE';
                } else {
                    if (isset($seenRawPaths[$path])) $reasons[] = 'RAW_EXECUTION_REUSED';
                    $seenRawPaths[$path] = true;
                    if (! preg_match('/^[a-f0-9]{64}$/i', (string) ($record['raw_sha256'] ?? ''))
                        || ! hash_equals(strtolower((string) $record['raw_sha256']), hash_file('sha256', $path))) {
                        $reasons[] = 'RAW_EXECUTION_HASH_MISMATCH';
                    }
                }
                if ($name === 'golden_fixtures'
                    && ! in_array(($record['fixture_decision'] ?? null), ['MATCH', 'INTENTIONAL_VERSION_BUMP'], true)) {
                    $reasons[] = 'GOLDEN_FIXTURE_DECISION_MISSING';
                }
                if ($name === 'golden_fixtures' && ($record['fixture_decision'] ?? null) === 'INTENTIONAL_VERSION_BUMP'
                    && trim((string) ($record['version_bump_record'] ?? '')) === '') {
                    $reasons[] = 'VERSION_BUMP_AUTHORITY_MISSING';
                }
                if ($name === 'replay_data_quality' && trim((string) ($record['review_record'] ?? '')) === '') {
                    $reasons[] = 'REPLAY_QUALITY_REVIEW_MISSING';
                }
                if ($name === 'config_registry' && trim((string) ($record['registry_record'] ?? '')) === '') {
                    $reasons[] = 'CONFIG_REGISTRY_RECORD_MISSING';
                }
                if ($name === 'downstream_safety') {
                    foreach (['effective_date', 'seal', 'fallback'] as $invariant) {
                        if (($record['invariants'][$invariant] ?? null) !== 'PASS') {
                            $reasons[] = 'DOWNSTREAM_'.strtoupper($invariant).'_UNPROVEN';
                        }
                    }
                }
            }
            $results[$name] = ['state' => $reasons === [] ? 'EVIDENCED' : 'BLOCKED', 'reasons' => $reasons];
            foreach ($reasons as $reason) $issues[] = $name.':'.$reason;
        }
        return [
            'stage' => 'MD-B19-A001', 'attempt' => 'MD-B19-A001',
            'build_sha' => $currentBuild,
            'state' => $issues === [] ? 'DECLARED_CONDITIONS_MET' : 'BLOCKED',
            'checks' => $results, 'issues' => array_values(array_unique($issues)),
            'unresolved_dependencies' => array_values((array) ($bundle['unresolved_dependencies'] ?? [])),
            'failed_verifications' => array_values((array) ($bundle['failed_verifications'] ?? [])),
            'claim_limit' => 'Declared release conditions only. This does not establish gate-set sufficiency, meaningful exercise, validity after superseded source/build identity, release safety, deployment, formal SATISFIED, or B19 closure.',
        ];
    }
}
