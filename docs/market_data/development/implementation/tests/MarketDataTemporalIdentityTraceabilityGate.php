<?php

require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';
require_once __DIR__.'/MarketDataTemporalIdentityTraceabilitySpec.php';

/** Exact, mutation-testable MD-B05 stage-entry normalization gate. */
final class MarketDataTemporalIdentityTraceabilityGate
{
    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows): array
    {
        $errors = [];
        $owners = MarketDataTemporalIdentityTraceabilitySpec::requiredOwners();
        $parents = MarketDataTemporalIdentityTraceabilitySpec::predicateParents();
        $stage = MarketDataTemporalIdentityTraceabilitySpec::STAGE;
        $seen = [];
        $counts = ['mandatory_b05' => 0, 'moved' => 0, 'reference' => 0, 'imported' => 0, 'contextual' => 0];

        foreach ($rows as $row) {
            $document = $row['strategy_document_id'];

            if (in_array($row['rule_id'], MarketDataTemporalIdentityTraceabilitySpec::IMPORTED_B05_RULES, true)) {
                $counts['imported']++;
                if ($row['primary_stage'] !== $stage
                    || $row['coverage_requirement'] !== 'REQUIRED'
                    || $row['applicability'] !== 'MANDATORY') {
                    $errors[] = $row['rule_id'].': imported B05 predicate lost its assignment';
                }
                continue;
            }

            if (! isset(MarketDataTemporalIdentityTraceabilitySpec::DOCUMENT_COUNTS[$document])) {
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
                    $errors[] = $row['rule_id'].': structural row is not REFERENCE_ONLY';
                }
                // A structural exclusion has to stay structural. If the strategy text is later
                // rewritten into a predicate, the exclusion is no longer justified by section 2.
                if (MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) === null) {
                    $errors[] = $row['rule_id'].': excluded row is no longer structurally a fragment';
                }

                continue;
            }

            if ($row['coverage_requirement'] !== 'REQUIRED' || $row['applicability'] !== 'MANDATORY') {
                $errors[] = $row['rule_id'].': required predicate lifecycle is not REQUIRED/MANDATORY';
            }
            if ($row['primary_stage'] !== $expectedOwner) {
                $errors[] = $row['rule_id'].': proof owner '.$row['primary_stage'].' != '.$expectedOwner;
            }

            $expectedContext = $parents[$row['rule_id']] ?? 'SELF_CONTAINED';
            if ($expectedContext !== 'SELF_CONTAINED') {
                $counts['contextual']++;
            }
            if (strpos($row['notes'], MarketDataTemporalIdentityTraceabilitySpec::ATTEMPT
                .': applicability_normalized=MANDATORY') === false
                || strpos($row['notes'], 'predicate_context='.$expectedContext) === false
                || strpos($row['notes'], 'normalized_predicate=') === false
                || strpos($row['notes'], 'proof_owner_confirmed='.$expectedOwner) === false) {
                $errors[] = $row['rule_id'].': structured normalization note missing, stale, or bound to the wrong parent';
            }

            if ($expectedOwner === $stage) {
                $counts['mandatory_b05']++;
            } else {
                $counts['moved']++;
                if (! in_array($stage, explode(';', $row['supporting_stages']), true)) {
                    $errors[] = $row['rule_id'].': moved predicate lost MD-B05 supporting linkage';
                }
            }
        }

        foreach (MarketDataTemporalIdentityTraceabilitySpec::DOCUMENT_COUNTS as $document => $expected) {
            if (count($seen[$document] ?? []) !== $expected) {
                $errors[] = $document.': corpus count '.count($seen[$document] ?? []).' != '.$expected;
            }
        }
        if ($counts['imported'] !== count(MarketDataTemporalIdentityTraceabilitySpec::IMPORTED_B05_RULES)) {
            $errors[] = 'IMPORTED: '.$counts['imported'].' of '
                .count(MarketDataTemporalIdentityTraceabilitySpec::IMPORTED_B05_RULES).' imported B05 rules found';
        }

        $denominator = $counts['mandatory_b05'] + $counts['imported'];
        if ($denominator !== MarketDataTemporalIdentityTraceabilitySpec::EXPECTED_B05_DENOMINATOR) {
            $errors[] = 'DENOMINATOR: '.$denominator.' != '
                .MarketDataTemporalIdentityTraceabilitySpec::EXPECTED_B05_DENOMINATOR.' mandatory B05 predicates';
        }
        $counts['b05_denominator'] = $denominator;

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $matrix = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataTemporalIdentityTraceabilityGate::validate($rows);
    $result['gate'] = 'MarketDataTemporalIdentityTraceabilityGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
