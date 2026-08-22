<?php

/**
 * Governed MD-B02-A001 normalization for MD-S059.
 *
 * The strategy source is immutable. This tool changes only the MUTABLE_TRACEABLE matrix after the
 * attempt baseline and Change Impact Declaration have been issued. It is deliberately explicit:
 * every MD-S059 row is assigned one semantic lifecycle, so an all-REFERENCE_ONLY list cannot evade
 * the mixed-run detector and transitional applicability cannot survive by omission.
 *
 * Usage: php MarketDataProviderBootstrapNormalization.php [--apply]
 */

$matrixPath = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);
$bindProof = in_array('--bind-proof', $argv, true);
$attempt = 'MD-B02-A001';

// Explanatory/structural rows, objectives owned by the platform as a whole, and historical context.
$referenceNumbers = [
    1, 2, 3, 4, 8, 21, 22, 23, 24, 25, 26, 29, 30, 31, 32, 33, 34, 35, 39,
    51, 52, 73, 77, 78, 79, 85, 93, 104, 113, 116, 126, 129, 131, 143,
];

// A later paid-source evaluation/transition is not requested. These are preserved as executable
// semantics, but excluded from the current denominator by their explicit lifecycle.
$optionalNumbers = [94, 95, 96, 97, 98, 99];
$conditionalNotApplicableNumbers = [81, 82, 105, 106, 107, 108, 109, 110];

// Proof responsibility, not document location, determines the primary stage. MD-B02 remains a
// supporting stage on every moved row because this strategy is the source-decision owner.
$ownershipMoves = [
    36 => ['MD-B22', 'independent value-evidence separation and final acceptance'],
    37 => ['MD-B22', 'production/decision-grade non-claim and final relock'],
    40 => ['MD-B07', 'immutable acquisition observation identity and timestamp'],
    43 => ['MD-B21', 'cross-contract row validation, dedup, and coverage convergence'],
    44 => ['MD-B08', 'source failure taxonomy, evidence, and fail-safe outcome'],
    45 => ['MD-B11', 'anomaly-only detection versus verified corporate action'],
    46 => ['MD-B10', 'immutable publication and no in-place history rewrite'],
    47 => ['MD-B21', 'invalid-data exclusion across indicator and eligibility surfaces'],
    48 => ['MD-B17', 'consumer readability gateway'],
    50 => ['MD-B22', 'independent terms/usage/redistribution compliance review'],
    72 => ['MD-B19', 'backfill acquisition and immutable-observation operations'],
    74 => ['MD-B19', 'intentional-start backfill as irreversible-risk mitigation'],
    76 => ['MD-B08', 'measured sustained-availability failure threshold'],
    81 => ['MD-B22', 'dated terms evidence is an independent reviewed work item'],
    82 => ['MD-B22', 'known-use limits depend on the reviewed terms evidence'],
    135 => ['MD-B21', 'canonical publication remains the governed domain product'],
    138 => ['MD-B10', 'versioned publication lineage and no historical rewrite'],
    140 => ['MD-B19', 'intentional-start backfill risk mitigation'],
    141 => ['MD-B08', 'measured availability trigger'],
    142 => ['MD-B22', 'independent licensing-basis review at final acceptance'],
];

$parentBindings = [
    9 => 8, 10 => 8, 11 => 8, 12 => 8, 13 => 8,
    40 => 39, 41 => 39, 42 => 39, 43 => 39, 44 => 39, 45 => 39, 46 => 39,
    47 => 39, 48 => 39, 49 => 39, 50 => 39,
    53 => 52, 54 => 52, 55 => 52, 56 => 52, 57 => 52, 58 => 52, 59 => 52, 60 => 52,
    80 => 79, 81 => 79, 82 => 79, 83 => 79,
    86 => 85, 87 => 85, 88 => 85, 89 => 85, 90 => 85,
    94 => 93, 95 => 93, 96 => 93, 97 => 93, 98 => 93, 99 => 93,
    105 => 104, 106 => 104, 107 => 104, 108 => 104, 109 => 104, 110 => 104,
    117 => 116, 118 => 116, 119 => 116, 120 => 116, 121 => 116, 122 => 116, 123 => 116, 124 => 116,
    127 => 126, 128 => 126,
    132 => 131, 133 => 131, 134 => 131, 135 => 131, 136 => 131, 137 => 131,
    138 => 131, 139 => 131, 140 => 131, 141 => 131, 142 => 131,
    144 => 143, 145 => 143, 146 => 143, 147 => 143, 148 => 143, 149 => 143,
    150 => 143, 151 => 143,
];

function readCsvMatrix($path)
{
    $handle = fopen($path, 'r');
    if ($handle === false) {
        throw new RuntimeException('Cannot read matrix: '.$path);
    }
    $headers = fgetcsv($handle);
    $bom = strpos($headers[0], "\xEF\xBB\xBF") === 0;
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) !== count($headers)) {
            throw new RuntimeException('Malformed matrix row.');
        }
        $rows[] = array_combine($headers, $values);
    }
    fclose($handle);

    return [$headers, $rows, $bom];
}

function numberOf($ruleId)
{
    return (int) substr($ruleId, strrpos($ruleId, 'R') + 1);
}

function addSupportingStage($raw, $stage)
{
    $stages = array_values(array_filter(array_map('trim', explode(';', (string) $raw))));
    if (! in_array($stage, $stages, true)) {
        $stages[] = $stage;
    }

    return implode(';', array_values(array_unique($stages)));
}

function stripListMarker($text)
{
    return trim(preg_replace('/^\s*([-*]|\d+\.)\s+/', '', trim($text)));
}

[$headers, $rows, $bom] = readCsvMatrix($matrixPath);
$byNumber = [];
foreach ($rows as $index => $row) {
    if ($row['strategy_document_id'] === 'MD-S059') {
        $byNumber[numberOf($row['rule_id'])] = $index;
    }
}
if (count($byNumber) !== 151) {
    throw new RuntimeException('Expected all 151 MD-S059 rows; found '.count($byNumber).'.');
}

$counts = ['reference' => 0, 'optional' => 0, 'conditional_not_applicable' => 0, 'mandatory' => 0, 'moved' => 0];
foreach ($byNumber as $number => $index) {
    $row =& $rows[$index];
    $row['current_evidence_ids'] = '';

    if (in_array($number, $referenceNumbers, true)) {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        $row['applicability'] = 'REFERENCE_ONLY';
        $row['coverage_status'] = 'REFERENCE_ONLY';
        $counts['reference']++;
        unset($row);
        continue;
    }

    $row['coverage_requirement'] = 'REQUIRED';
    if (in_array($number, $optionalNumbers, true)) {
        $row['applicability'] = 'OPTIONAL_CAPABILITY';
        $row['coverage_status'] = 'OPTIONAL_NOT_REQUESTED';
        $basis = 'optional_not_requested: no later paid-source evaluation has been opened';
        $counts['optional']++;
    } elseif (in_array($number, $conditionalNotApplicableNumbers, true)) {
        $row['applicability'] = 'CONDITIONAL_NOT_APPLICABLE';
        $row['coverage_status'] = 'NOT_APPLICABLE';
        $basis = in_array($number, [81, 82], true)
            ? 'condition_false: terms remain explicitly unread and undated, no compliance claim is made, and MD-S059-R0091 makes the dated review separate non-blocking work'
            : 'condition_false: no paid-provider decision or source transition exists in the current phase';
        $counts['conditional_not_applicable']++;
    } else {
        $row['applicability'] = 'MANDATORY';
        $row['coverage_status'] = 'NOT_ASSESSED';
        $basis = 'always_applicable: the current bootstrap-source boundary or its current non-goal binds now';
        $counts['mandatory']++;
    }

    if (isset($ownershipMoves[$number])) {
        $row['supporting_stages'] = addSupportingStage($row['supporting_stages'], 'MD-B02');
        $row['primary_stage'] = $ownershipMoves[$number][0];
        $ownerBasis = $ownershipMoves[$number][1];
        $counts['moved']++;
    } else {
        $row['primary_stage'] = 'MD-B02';
        $ownerBasis = 'Yahoo bootstrap decision, provider boundary, or current non-goal';
    }

    $context = isset($parentBindings[$number])
        ? sprintf('MD-S059-R%04d', $parentBindings[$number])
        : 'SELF_CONTAINED';
    $normalized = isset($parentBindings[$number])
        ? trim($rows[$byNumber[$parentBindings[$number]]]['rule_text']).' '.stripListMarker($row['rule_text'])
        : trim($row['rule_text']);
    $note = $attempt.': applicability_normalized='.$row['applicability']
        .'; predicate_context='.$context
        .'; normalized_predicate='.preg_replace('/\s+/', ' ', $normalized)
        .'; applicability_basis='.$basis
        .'; proof_owner_confirmed='.$row['primary_stage']
        .'; proof_owner_basis='.$ownerBasis;
    if (strpos($row['notes'], $attempt.': applicability_normalized=') === false) {
        $row['notes'] = trim($row['notes']) === '' ? $note : trim($row['notes']).' | '.$note;
    }
    unset($row);
}

// The frozen platform decision is already self-contained but still belongs in the normalized set.
foreach ($rows as &$row) {
    if ($row['rule_id'] !== 'MD-S001-R0063') {
        continue;
    }
    $row['coverage_requirement'] = 'REQUIRED';
    $row['applicability'] = 'MANDATORY';
    $row['coverage_status'] = 'NOT_ASSESSED';
    $row['current_evidence_ids'] = '';
    $note = $attempt.': applicability_normalized=MANDATORY; predicate_context=SELF_CONTAINED; '
        .'normalized_predicate='.preg_replace('/\s+/', ' ', trim($row['rule_text']))
        .'; applicability_basis=always_applicable: frozen source-order decision binds now; '
        .'proof_owner_confirmed=MD-B02; proof_owner_basis=bootstrap source order and manual one-date rescue';
    if (strpos($row['notes'], $attempt.': applicability_normalized=') === false) {
        $row['notes'] = trim($row['notes']) === '' ? $note : trim($row['notes']).' | '.$note;
    }
    break;
}
unset($row);

if ($bindProof) {
    $bound = 0;
    foreach ($rows as &$row) {
        if ($row['primary_stage'] !== 'MD-B02' || $row['coverage_requirement'] !== 'REQUIRED'
            || $row['applicability'] !== 'MANDATORY') {
            continue;
        }
        $row['coverage_status'] = 'SATISFIED';
        $row['current_evidence_ids'] = 'E-MD-B02-A001-001';
        if (strpos($row['notes'], 'semantic_revalidation=E-MD-B02-A001-001') === false) {
            $row['notes'] = trim($row['notes']).'; semantic_revalidation=E-MD-B02-A001-001; '
                .'proof=provider_bootstrap_conformance_cluster; '
                .'implementation_surface=app/Application/MarketData/Ports,app/Infrastructure/MarketData/Source,config/market_data.php,tests/Unit/MarketData';
        }
        $bound++;
    }
    unset($row);
    if ($bound !== 86) {
        throw new RuntimeException('Proof binding must be atomic: expected 86 mandatory MD-B02 rows, got '.$bound.'.');
    }
    $counts['proof_bound'] = $bound;
}

echo json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
if (! $apply) {
    echo "DRY RUN — pass --apply to write.\n";
    exit(0);
}

$handle = fopen($matrixPath, 'w');
$outHeaders = $headers;
if ($bom) {
    $outHeaders[0] = "\xEF\xBB\xBF".$outHeaders[0];
}
fputcsv($handle, $outHeaders);
foreach ($rows as $row) {
    $values = [];
    foreach ($headers as $header) {
        $values[] = $row[$header];
    }
    fputcsv($handle, $values);
}
fclose($handle);
echo 'WROTE '.$matrixPath.PHP_EOL;
