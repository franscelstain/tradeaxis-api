<?php

require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/** Exact, mutation-testable MD-B02-A001 semantic re-derivation and proof-binding gate. */
final class MarketDataProviderBootstrapTraceabilityGate
{
    public const EVIDENCE_ID = 'E-MD-B02-A001-001';

    public const REFERENCE = [
        1, 2, 3, 4, 8, 21, 22, 23, 24, 25, 26, 29, 30, 31, 32, 33, 34, 35, 39,
        51, 52, 73, 77, 78, 79, 85, 93, 104, 113, 116, 126, 129, 131, 143,
    ];

    public const OPTIONAL = [94, 95, 96, 97, 98, 99];

    public const CONDITIONAL_NOT_APPLICABLE = [81, 82, 105, 106, 107, 108, 109, 110];

    public const MOVES = [
        36 => 'MD-B22', 37 => 'MD-B22', 40 => 'MD-B07', 43 => 'MD-B21', 44 => 'MD-B08',
        45 => 'MD-B11', 46 => 'MD-B10', 47 => 'MD-B21', 48 => 'MD-B17', 50 => 'MD-B22',
        72 => 'MD-B19', 74 => 'MD-B19', 76 => 'MD-B08', 81 => 'MD-B22', 82 => 'MD-B22',
        135 => 'MD-B21', 138 => 'MD-B10', 140 => 'MD-B19', 141 => 'MD-B08', 142 => 'MD-B22',
    ];

    public static function numberOf(string $ruleId): int
    {
        return (int) substr($ruleId, strrpos($ruleId, 'R') + 1);
    }

    /** @param array<int,array<string,string>> $rows */
    public static function validateNormalization(array $rows): array
    {
        $errors = [];
        $sourceRows = [];
        $platform = null;
        foreach ($rows as $row) {
            if ($row['strategy_document_id'] === 'MD-S059') {
                $sourceRows[self::numberOf($row['rule_id'])] = $row;
            } elseif ($row['rule_id'] === 'MD-S001-R0063') {
                $platform = $row;
            }
        }
        if (count($sourceRows) !== 151) {
            $errors[] = 'CORPUS: expected 151 MD-S059 rows, got '.count($sourceRows);
        }

        $counts = ['mandatory_b02' => 0, 'optional_b02' => 0, 'not_applicable_b02' => 0];
        foreach ($sourceRows as $number => $row) {
            $isReference = in_array($number, self::REFERENCE, true);
            $isOptional = in_array($number, self::OPTIONAL, true);
            $isNotApplicable = in_array($number, self::CONDITIONAL_NOT_APPLICABLE, true);
            $expectedCoverage = $isReference ? 'REFERENCE_ONLY' : 'REQUIRED';
            $expectedApplicability = $isReference ? 'REFERENCE_ONLY'
                : ($isOptional ? 'OPTIONAL_CAPABILITY'
                    : ($isNotApplicable ? 'CONDITIONAL_NOT_APPLICABLE' : 'MANDATORY'));
            $expectedStatus = $isReference ? 'REFERENCE_ONLY'
                : ($isOptional ? 'OPTIONAL_NOT_REQUESTED'
                    : ($isNotApplicable ? 'NOT_APPLICABLE' : null));
            $expectedStage = self::MOVES[$number] ?? 'MD-B02';

            if ($row['coverage_requirement'] !== $expectedCoverage) {
                $errors[] = $row['rule_id'].': coverage '.$row['coverage_requirement'].' != '.$expectedCoverage;
            }
            if ($row['applicability'] !== $expectedApplicability) {
                $errors[] = $row['rule_id'].': applicability '.$row['applicability'].' != '.$expectedApplicability;
            }
            if ($expectedStatus !== null && $row['coverage_status'] !== $expectedStatus) {
                $errors[] = $row['rule_id'].': lifecycle '.$row['coverage_status'].' != '.$expectedStatus;
            }
            if ($row['primary_stage'] !== $expectedStage) {
                $errors[] = $row['rule_id'].': proof owner '.$row['primary_stage'].' != '.$expectedStage;
            }
            if (isset(self::MOVES[$number]) && ! in_array('MD-B02', explode(';', $row['supporting_stages']), true)) {
                $errors[] = $row['rule_id'].': moved predicate lost MD-B02 supporting-stage linkage';
            }
            if (! $isReference) {
                if (strpos($row['notes'], 'MD-B02-A001: applicability_normalized='.$expectedApplicability) === false
                    || strpos($row['notes'], 'predicate_context=') === false
                    || strpos($row['notes'], 'normalized_predicate=') === false
                    || strpos($row['notes'], 'proof_owner_confirmed='.$expectedStage) === false) {
                    $errors[] = $row['rule_id'].': structured normalization record is missing or stale';
                }
            }
            if ($expectedStage === 'MD-B02') {
                if ($expectedApplicability === 'MANDATORY') {
                    $counts['mandatory_b02']++;
                } elseif ($expectedApplicability === 'OPTIONAL_CAPABILITY') {
                    $counts['optional_b02']++;
                } elseif ($expectedApplicability === 'CONDITIONAL_NOT_APPLICABLE') {
                    $counts['not_applicable_b02']++;
                }
            }
        }

        if (! is_array($platform)) {
            $errors[] = 'MD-S001-R0063: frozen source-order predicate missing';
        } else {
            if ($platform['coverage_requirement'] !== 'REQUIRED' || $platform['applicability'] !== 'MANDATORY'
                || $platform['primary_stage'] !== 'MD-B02') {
                $errors[] = 'MD-S001-R0063: frozen source-order predicate lifecycle/owner is wrong';
            }
            if (strpos($platform['notes'], 'MD-B02-A001: applicability_normalized=MANDATORY') === false) {
                $errors[] = 'MD-S001-R0063: normalization record missing';
            }
            $counts['mandatory_b02']++;
        }

        if ($counts !== ['mandatory_b02' => 86, 'optional_b02' => 6, 'not_applicable_b02' => 6]) {
            $errors[] = 'DENOMINATOR: '.json_encode($counts).' != governed 86 mandatory + 6 optional + 6 conditional N/A';
        }

        return ['errors' => $errors, 'counts' => $counts, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }

    /** @param array<int,array<string,string>> $rows */
    public static function validateProofBinding(array $rows): array
    {
        $result = self::validateNormalization($rows);
        $errors = $result['errors'];
        $satisfied = 0;
        foreach ($rows as $row) {
            if ($row['primary_stage'] !== 'MD-B02' || $row['coverage_requirement'] !== 'REQUIRED'
                || $row['applicability'] !== 'MANDATORY') {
                continue;
            }
            if ($row['coverage_status'] !== 'SATISFIED') {
                $errors[] = $row['rule_id'].': current mandatory predicate is not SATISFIED';
                continue;
            }
            if (! in_array(self::EVIDENCE_ID, explode(';', $row['current_evidence_ids']), true)) {
                $errors[] = $row['rule_id'].': current evidence binding missing';
                continue;
            }
            if (strpos($row['notes'], 'semantic_revalidation='.self::EVIDENCE_ID) === false) {
                $errors[] = $row['rule_id'].': semantic revalidation marker missing';
                continue;
            }
            $satisfied++;
        }
        if ($satisfied !== 86) {
            $errors[] = 'PROOF_DENOMINATOR: '.$satisfied.' != 86 satisfied current mandatory predicates';
        }

        return ['errors' => $errors, 'satisfied' => $satisfied, 'status' => $errors === [] ? 'PASS' : 'FAIL'];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $matrix = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
    $rows = MarketDataClassificationConsistencyGate::readMatrix($matrix)['rows'];
    $mode = in_array('--proof', $argv, true) ? 'proof' : 'normalization';
    $result = $mode === 'proof'
        ? MarketDataProviderBootstrapTraceabilityGate::validateProofBinding($rows)
        : MarketDataProviderBootstrapTraceabilityGate::validateNormalization($rows);
    $result['gate'] = 'MarketDataProviderBootstrapTraceabilityGate';
    $result['mode'] = $mode;
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
