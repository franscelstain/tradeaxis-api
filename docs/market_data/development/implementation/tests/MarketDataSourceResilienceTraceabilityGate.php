<?php

require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';
require_once __DIR__.'/MarketDataSourceResilienceTraceabilitySpec.php';

/** Exact, mutation-testable MD-B08 stage-entry normalization gate. */
final class MarketDataSourceResilienceTraceabilityGate
{
    /** @param array<int,array<string,string>> $rows */
    public static function validate(array $rows): array
    {
        $errors = [];
        $owners = MarketDataSourceResilienceTraceabilitySpec::requiredOwners();
        $parents = MarketDataSourceResilienceTraceabilitySpec::predicateParents();
        $external = array_fill_keys(MarketDataSourceResilienceTraceabilitySpec::EXTERNAL_RULES, true);
        $stage = MarketDataSourceResilienceTraceabilitySpec::STAGE;
        $seen = [];
        $seenIds = [];
        $counts = [
            'mandatory_b08' => 0,
            'moved' => 0,
            'reference' => 0,
            'contextual' => 0,
            'reviewed' => 0,
            'b08_denominator' => 0,
            'additive_rules' => 0,
        ];

        foreach ($rows as $row) {
            $seenIds[$row['rule_id']] = true;
            $document = $row['strategy_document_id'];
            $isSourceDocument = isset(MarketDataSourceResilienceTraceabilitySpec::SOURCE_DOCUMENT_COUNTS[$document]);
            if (! $isSourceDocument && ! isset($external[$row['rule_id']])) {
                continue;
            }

            $counts['reviewed']++;
            $number = (int) substr($row['rule_id'], strrpos($row['rule_id'], 'R') + 1);
            if ($isSourceDocument) {
                $seen[$document][$number] = true;
            }
            if (isset(MarketDataSourceResilienceTraceabilitySpec::ADDITIVE_RULES[$row['rule_id']])) {
                $definition = MarketDataSourceResilienceTraceabilitySpec::ADDITIVE_RULES[$row['rule_id']];
                $counts['additive_rules']++;
                if ((int) $row['source_line'] !== (int) $definition['source_line']
                    || $row['rule_text'] !== $definition['rule_text']
                    || strtoupper($row['rule_fingerprint_sha1']) !== strtoupper(sha1($definition['rule_text']))) {
                    $errors[] = $row['rule_id'].': additive semantic fragment identity drifted';
                }
            }
            $expectedOwner = $owners[$document][$number] ?? null;

            if ($expectedOwner === null) {
                $counts['reference']++;
                if ($row['coverage_requirement'] !== 'REFERENCE_ONLY'
                    || $row['applicability'] !== 'REFERENCE_ONLY'
                    || $row['coverage_status'] !== 'REFERENCE_ONLY') {
                    $errors[] = $row['rule_id'].': reviewed non-predicate/context row is not REFERENCE_ONLY';
                }
                continue;
            }

            if ($row['coverage_requirement'] !== 'REQUIRED' || $row['applicability'] !== 'MANDATORY') {
                $errors[] = $row['rule_id'].': required predicate lifecycle is not REQUIRED/MANDATORY';
            }
            if ($row['coverage_status'] !== 'NOT_ASSESSED' && $row['coverage_status'] !== 'SATISFIED') {
                $errors[] = $row['rule_id'].': required predicate has invalid current lifecycle '.$row['coverage_status'];
            }
            if ($row['primary_stage'] !== $expectedOwner) {
                $errors[] = $row['rule_id'].': proof owner '.$row['primary_stage'].' != '.$expectedOwner;
            }
            if ($expectedOwner !== $stage) {
                $counts['moved']++;
                $support = array_values(array_filter(array_map('trim', explode(';', $row['supporting_stages']))));
                if (! in_array($stage, $support, true)) {
                    $errors[] = $row['rule_id'].': moved predicate lost MD-B08 supporting-stage linkage';
                }
            } else {
                $counts['mandatory_b08']++;
            }

            $expectedContext = $parents[$row['rule_id']] ?? 'SELF_CONTAINED';
            if ($expectedContext !== 'SELF_CONTAINED') {
                $counts['contextual']++;
            }
            if (strpos($row['notes'], (isset(MarketDataSourceResilienceTraceabilitySpec::REMEDIATED_RULES[$row['rule_id']]) ? MarketDataSourceResilienceTraceabilitySpec::REMEDIATION_ATTEMPT : MarketDataSourceResilienceTraceabilitySpec::ATTEMPT)
                .': applicability_normalized=MANDATORY') === false
                || strpos($row['notes'], 'predicate_context='.$expectedContext) === false
                || strpos($row['notes'], 'normalized_predicate=') === false
                || strpos($row['notes'], 'proof_owner_confirmed='.$expectedOwner) === false) {
                $errors[] = $row['rule_id'].': deterministic applicability/context/owner note is missing';
            }
        }

        foreach (MarketDataSourceResilienceTraceabilitySpec::ADDITIVE_RULES as $ruleId => $definition) {
            if (! isset($seenIds[$ruleId])) {
                $errors[] = $ruleId.': missing semantic source fragment';
            }
        }
        foreach (MarketDataSourceResilienceTraceabilitySpec::SOURCE_DOCUMENT_COUNTS as $document => $expected) {
            if (count($seen[$document] ?? []) !== $expected) {
                $errors[] = $document.': active corpus count changed';
            }
        }

        $counts['b08_denominator'] = $counts['mandatory_b08'];
        if ($counts['mandatory_b08'] !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
            $errors[] = 'DENOMINATOR: expected '.MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR;
        }
        if ($counts['additive_rules'] !== count(MarketDataSourceResilienceTraceabilitySpec::ADDITIVE_RULES)) {
            $errors[] = 'ADDITIVE_RULES: exact missing semantic fragments not present';
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__, 5);
    $matrix = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $result = MarketDataSourceResilienceTraceabilityGate::validate($rows);
    $result['gate'] = 'MarketDataSourceResilienceTraceabilityGate';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
