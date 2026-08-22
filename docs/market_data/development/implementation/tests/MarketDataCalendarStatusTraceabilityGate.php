<?php

require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';
require_once __DIR__.'/MarketDataCalendarStatusTraceabilitySpec.php';

/** Exact, mutation-testable MD-B06 stage-entry normalization gate. */
final class MarketDataCalendarStatusTraceabilityGate
{
    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows): array
    {
        $errors = [];
        $owners = MarketDataCalendarStatusTraceabilitySpec::requiredOwners();
        $parents = MarketDataCalendarStatusTraceabilitySpec::predicateParents();
        $stage = MarketDataCalendarStatusTraceabilitySpec::STAGE;
        $seen = [];
        $counts = ['mandatory_b06' => 0, 'moved' => 0, 'reference' => 0, 'contextual' => 0, 'b06_denominator' => 0];

        foreach ($rows as $row) {
            $document = $row['strategy_document_id'];
            if (! isset(MarketDataCalendarStatusTraceabilitySpec::DOCUMENT_COUNTS[$document])) {
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
                    $errors[] = $row['rule_id'].': reviewed non-predicate row is not REFERENCE_ONLY';
                }
                continue;
            }

            if ($row['coverage_requirement'] !== 'REQUIRED'
                || $row['applicability'] !== 'MANDATORY') {
                $errors[] = $row['rule_id'].': required predicate lifecycle is not REQUIRED/MANDATORY';
            }
            if ($row['primary_stage'] !== $expectedOwner) {
                $errors[] = $row['rule_id'].': proof owner '.$row['primary_stage'].' != '.$expectedOwner;
            }
            if ($expectedOwner !== $stage) {
                $counts['moved']++;
                $support = array_values(array_filter(array_map('trim', explode(';', $row['supporting_stages']))));
                if (! in_array($stage, $support, true)) {
                    $errors[] = $row['rule_id'].': moved predicate lost MD-B06 supporting-stage linkage';
                }
            } else {
                $counts['mandatory_b06']++;
            }

            $expectedContext = $parents[$row['rule_id']] ?? 'SELF_CONTAINED';
            if ($expectedContext !== 'SELF_CONTAINED') {
                $counts['contextual']++;
            }
            if (strpos($row['notes'], MarketDataCalendarStatusTraceabilitySpec::ATTEMPT
                .': applicability_normalized=MANDATORY') === false
                || strpos($row['notes'], 'predicate_context='.$expectedContext) === false
                || strpos($row['notes'], 'normalized_predicate=') === false
                || strpos($row['notes'], 'proof_owner_confirmed='.$expectedOwner) === false) {
                $errors[] = $row['rule_id'].': deterministic applicability/context/owner note is missing';
            }
        }

        foreach (MarketDataCalendarStatusTraceabilitySpec::DOCUMENT_COUNTS as $document => $expected) {
            if (count($seen[$document] ?? []) !== $expected) {
                $errors[] = $document.': active corpus count changed';
            }
            $partition = count($owners[$document] ?? [])
                + count(MarketDataCalendarStatusTraceabilitySpec::NON_PREDICATE_REFERENCE[$document]);
            if ($partition !== $expected) {
                $errors[] = $document.': reviewed predicate/reference sets do not partition the document';
            }
        }

        $counts['b06_denominator'] = $counts['mandatory_b06'];
        if ($counts['mandatory_b06'] !== MarketDataCalendarStatusTraceabilitySpec::EXPECTED_B06_DENOMINATOR) {
            $errors[] = 'DENOMINATOR: expected '.MarketDataCalendarStatusTraceabilitySpec::EXPECTED_B06_DENOMINATOR;
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataCalendarStatusTraceabilityGate::validate($rows);
    $result['gate'] = 'MarketDataCalendarStatusTraceabilityGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
