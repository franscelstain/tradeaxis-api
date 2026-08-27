<?php

require_once __DIR__.'/MarketDataLiquidityMetricProofSpec.php';

/**
 * MD-B13 proof-readiness and binding gate.
 *
 * Unbound it asserts that nothing is bound yet; bound it asserts that every mandatory predicate is
 * SATISFIED against B13 evidence. Both directions matter: a gate that only checks the bound state
 * cannot tell a stage that proved its predicates from a stage that was born marked as proven.
 */
final class MarketDataLiquidityMetricProofGate
{
    public static function validate(string $root, bool $bound = false, array $overrides = []): array
    {
        $denominator = $overrides['mandatory'] ?? MarketDataLiquidityMetricTraceabilitySpec::mandatory($root);
        $entries = $overrides['entries'] ?? MarketDataLiquidityMetricProofSpec::entries($root);
        $families = $overrides['families'] ?? MarketDataLiquidityMetricProofSpec::families();

        $errors = [];
        $byRule = [];
        $familiesUsed = [];

        foreach ($denominator as $row) {
            $byRule[$row['rule_id']] = $row;
            $evidence = trim((string) ($row['current_evidence_ids'] ?? ''));
            if ($bound) {
                if (($row['coverage_status'] ?? '') !== 'SATISFIED' || ! preg_match('/^E-MD-B13-A001-\d{3}$/', $evidence)) {
                    $errors[] = 'BOUND_STATE_INVALID:'.$row['rule_id'];
                }
            } elseif (($row['coverage_status'] ?? '') !== 'NOT_ASSESSED' || $evidence !== '') {
                $errors[] = 'PREMATURE_BINDING:'.$row['rule_id'];
            }
        }

        if (count($denominator) !== MarketDataLiquidityMetricProofSpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'DENOMINATOR_MISMATCH';
        }
        if (count($entries) !== MarketDataLiquidityMetricProofSpec::EXPECTED_DENOMINATOR) {
            $errors[] = 'PROOF_MAP_COUNT_MISMATCH';
        }

        $seen = [];
        foreach ($entries as $entry) {
            $ruleId = $entry['rule_id'] ?? '';
            if (isset($seen[$ruleId])) {
                $errors[] = 'DUPLICATE_ENTRY:'.$ruleId;

                continue;
            }
            $seen[$ruleId] = 1;

            if (! isset($byRule[$ruleId])) {
                $errors[] = 'ORPHAN_ENTRY:'.$ruleId;

                continue;
            }

            try {
                $expected = MarketDataLiquidityMetricProofSpec::familyFor($byRule[$ruleId]);
            } catch (Throwable $exception) {
                $errors[] = 'UNMAPPED_FAMILY:'.$ruleId;

                continue;
            }

            if (($entry['family'] ?? '') !== $expected) {
                $errors[] = 'WRONG_FAMILY:'.$ruleId;
            }

            $family = $families[$expected] ?? null;
            if ($family === null) {
                $errors[] = 'MISSING_FAMILY:'.$expected;

                continue;
            }
            $familiesUsed[$expected] = 1;

            if (strpos((string) ($family['owner'] ?? ''), 'MD-B13:') !== 0) {
                $errors[] = 'WRONG_OWNER:'.$expected;
            }

            foreach ($family['implementation'] ?? [] as $path) {
                if (! is_file($root.'/'.$path)) {
                    $errors[] = 'MISSING_IMPL:'.$path;
                }
            }

            foreach (['positive', 'negative'] as $kind) {
                $reference = $family[$kind] ?? [];
                $file = $reference[0] ?? '';
                $method = $reference[1] ?? '';
                $source = is_file($root.'/'.$file) ? (string) file_get_contents($root.'/'.$file) : '';
                if ($source === '' || strpos($source, 'function '.$method.'(') === false) {
                    $errors[] = 'MISSING_'.strtoupper($kind).'_PROOF:'.$expected;
                }
            }
        }

        foreach ($byRule as $ruleId => $row) {
            if (! isset($seen[$ruleId])) {
                $errors[] = 'UNMAPPED:'.$ruleId;
            }
        }

        // A family declared but never reached proves nothing and hides a mapping mistake.
        foreach (array_keys($families) as $family) {
            if (! isset($familiesUsed[$family])) {
                $errors[] = 'UNREACHED_FAMILY:'.$family;
            }
        }

        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'denominator' => count($denominator),
            'proof_map_count' => count($entries),
            'proof_families_used' => count($familiesUsed),
            'bound' => $bound,
            'runtime_pending' => $bound ? 0 : count($denominator),
            'errors' => array_values(array_unique($errors)),
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $result = MarketDataLiquidityMetricProofGate::validate(dirname(__DIR__, 5), in_array('--bound', $argv, true));
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
