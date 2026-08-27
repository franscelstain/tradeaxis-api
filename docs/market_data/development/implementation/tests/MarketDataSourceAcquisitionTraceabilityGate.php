<?php

require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';
require_once __DIR__.'/MarketDataSourceAcquisitionTraceabilitySpec.php';

/** Exact, mutation-testable MD-B07 stage-entry normalization gate. */
final class MarketDataSourceAcquisitionTraceabilityGate
{
    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows): array
    {
        $errors = [];
        $owners = MarketDataSourceAcquisitionTraceabilitySpec::requiredOwners();
        $parents = MarketDataSourceAcquisitionTraceabilitySpec::predicateParents();
        $external = array_fill_keys(MarketDataSourceAcquisitionTraceabilitySpec::EXTERNAL_RULES, true);
        $stage = MarketDataSourceAcquisitionTraceabilitySpec::STAGE;
        $seen = [];
        $counts = ['mandatory_b07' => 0, 'moved' => 0, 'reference' => 0, 'contextual' => 0, 'reviewed' => 0, 'b07_denominator' => 0];

        foreach ($rows as $row) {
            $document = $row['strategy_document_id'];
            $isSourceDocument = isset(MarketDataSourceAcquisitionTraceabilitySpec::SOURCE_DOCUMENT_COUNTS[$document]);
            if (! $isSourceDocument && ! isset($external[$row['rule_id']])) {
                continue;
            }

            $counts['reviewed']++;
            $number = (int) substr($row['rule_id'], strrpos($row['rule_id'], 'R') + 1);
            if ($isSourceDocument) {
                $seen[$document][$number] = true;
            }
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

            if ($row['coverage_requirement'] !== 'REQUIRED' || $row['applicability'] !== 'MANDATORY') {
                $errors[] = $row['rule_id'].': required predicate lifecycle is not REQUIRED/MANDATORY';
            }
            if ($row['primary_stage'] !== $expectedOwner) {
                $errors[] = $row['rule_id'].': proof owner '.$row['primary_stage'].' != '.$expectedOwner;
            }
            if ($expectedOwner !== $stage) {
                $counts['moved']++;
                $support = array_values(array_filter(array_map('trim', explode(';', $row['supporting_stages']))));
                if (! in_array($stage, $support, true)) {
                    $errors[] = $row['rule_id'].': moved predicate lost MD-B07 supporting-stage linkage';
                }
            } else {
                $counts['mandatory_b07']++;
            }

            $expectedContext = $parents[$row['rule_id']] ?? 'SELF_CONTAINED';
            if ($expectedContext !== 'SELF_CONTAINED') {
                $counts['contextual']++;
            }
            $expectedAttempt = isset(MarketDataSourceAcquisitionTraceabilitySpec::REMEDIATED_RULES[$row['rule_id']])
                ? MarketDataSourceAcquisitionTraceabilitySpec::REMEDIATION_ATTEMPT
                : MarketDataSourceAcquisitionTraceabilitySpec::ATTEMPT;
            if (strpos($row['notes'], $expectedAttempt
                .': applicability_normalized=MANDATORY') === false
                || strpos($row['notes'], 'predicate_context='.$expectedContext) === false
                || strpos($row['notes'], 'normalized_predicate=') === false
                || strpos($row['notes'], 'proof_owner_confirmed='.$expectedOwner) === false) {
                $errors[] = $row['rule_id'].': deterministic applicability/context/owner note is missing';
            }
        }

        foreach (MarketDataSourceAcquisitionTraceabilitySpec::SOURCE_DOCUMENT_COUNTS as $document => $expected) {
            if (count($seen[$document] ?? []) !== $expected) {
                $errors[] = $document.': active corpus count changed';
            }
            if (count($owners[$document] ?? []) > $expected) {
                $errors[] = $document.': reviewed assignment exceeds source corpus';
            }
        }

        $counts['b07_denominator'] = $counts['mandatory_b07'];
        if ($counts['mandatory_b07'] !== MarketDataSourceAcquisitionTraceabilitySpec::EXPECTED_B07_DENOMINATOR) {
            $errors[] = 'DENOMINATOR: expected '.MarketDataSourceAcquisitionTraceabilitySpec::EXPECTED_B07_DENOMINATOR;
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataSourceAcquisitionTraceabilityGate::validate($rows);
    $result['gate'] = 'MarketDataSourceAcquisitionTraceabilityGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
