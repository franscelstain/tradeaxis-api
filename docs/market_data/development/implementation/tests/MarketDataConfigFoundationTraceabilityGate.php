<?php

require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';
require_once __DIR__.'/MarketDataConfigFoundationTraceabilitySpec.php';

/** Exact, mutation-testable MD-B04 stage-entry normalization gate. */
final class MarketDataConfigFoundationTraceabilityGate
{
    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows): array
    {
        $errors = [];
        $owners = MarketDataConfigFoundationTraceabilitySpec::requiredOwners();
        $seen = [];
        $counts = ['mandatory_b04' => 0, 'moved' => 0, 'reference' => 0];

        foreach ($rows as $row) {
            $document = $row['strategy_document_id'];
            if (! isset(MarketDataConfigFoundationTraceabilitySpec::DOCUMENT_COUNTS[$document])) {
                continue;
            }
            $number = (int) substr($row['rule_id'], strrpos($row['rule_id'], 'R') + 1);
            $seen[$document][$number] = true;
            $expectedOwner = $owners[$document][$number] ?? null;

            if ($expectedOwner === null) {
                $counts['reference']++;
                if ($row['coverage_requirement'] !== 'REFERENCE_ONLY'
                    || $row['applicability'] !== 'REFERENCE_ONLY'
                    || $row['coverage_status'] !== 'REFERENCE_ONLY') {
                    $errors[] = $row['rule_id'].': explanatory/definition row is not REFERENCE_ONLY';
                }
                continue;
            }

            if ($row['coverage_requirement'] !== 'REQUIRED' || $row['applicability'] !== 'MANDATORY') {
                $errors[] = $row['rule_id'].': required predicate lifecycle is not REQUIRED/MANDATORY';
            }
            if ($row['primary_stage'] !== $expectedOwner) {
                $errors[] = $row['rule_id'].': proof owner '.$row['primary_stage'].' != '.$expectedOwner;
            }
            if (strpos($row['notes'], MarketDataConfigFoundationTraceabilitySpec::ATTEMPT
                .': applicability_normalized=MANDATORY') === false
                || strpos($row['notes'], 'predicate_context=') === false
                || strpos($row['notes'], 'normalized_predicate=') === false
                || strpos($row['notes'], 'proof_owner_confirmed='.$expectedOwner) === false) {
                $errors[] = $row['rule_id'].': structured normalization note missing or stale';
            }
            if ($expectedOwner === 'MD-B04') {
                $counts['mandatory_b04']++;
            } else {
                $counts['moved']++;
                if (! in_array('MD-B04', explode(';', $row['supporting_stages']), true)) {
                    $errors[] = $row['rule_id'].': moved predicate lost MD-B04 supporting linkage';
                }
            }
        }

        foreach (MarketDataConfigFoundationTraceabilitySpec::DOCUMENT_COUNTS as $document => $expected) {
            if (count($seen[$document] ?? []) !== $expected) {
                $errors[] = $document.': corpus count '.count($seen[$document] ?? []).' != '.$expected;
            }
        }
        if ($counts['mandatory_b04'] !== MarketDataConfigFoundationTraceabilitySpec::EXPECTED_B04_DENOMINATOR) {
            $errors[] = 'DENOMINATOR: '.$counts['mandatory_b04'].' != '
                .MarketDataConfigFoundationTraceabilitySpec::EXPECTED_B04_DENOMINATOR
                .' mandatory B04 predicates';
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $matrix = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataConfigFoundationTraceabilityGate::validate($rows);
    $result['gate'] = 'MarketDataConfigFoundationTraceabilityGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
