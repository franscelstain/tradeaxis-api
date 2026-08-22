<?php

require_once __DIR__.'/MarketDataTemporalIdentityTraceabilitySpec.php';

/** Governed MD-B05-A001 matrix normalization. Usage: php this-file.php [--apply]. */

$matrixPath = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);

function b05ReadMatrix(string $path): array
{
    $handle = fopen($path, 'r');
    $headers = fgetcsv($handle);
    $bom = strpos($headers[0], "\xEF\xBB\xBF") === 0;
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    $rows = [];
    while (($values = fgetcsv($handle)) !== false) {
        if (count($values) !== count($headers)) {
            throw new RuntimeException('Malformed traceability row.');
        }
        $rows[] = array_combine($headers, $values);
    }
    fclose($handle);

    return [$headers, $rows, $bom];
}

function b05RuleNumber(string $ruleId): int
{
    return (int) substr($ruleId, strrpos($ruleId, 'R') + 1);
}

function b05AddSupportingStage(string $raw, string $stage): string
{
    $stages = array_values(array_filter(array_map('trim', explode(';', $raw))));
    if (! in_array($stage, $stages, true)) {
        $stages[] = $stage;
    }

    return implode(';', array_values(array_unique($stages)));
}

function b05PredicateText(string $text): string
{
    return preg_replace('/\s+/', ' ', trim(preg_replace('/^\s*([-*]|\d+\.)\s+/', '', $text)));
}

function b05ReplaceAttemptNote(string $raw, ?string $replacement): string
{
    $parts = array_values(array_filter(array_map('trim', explode(' | ', $raw)), static function ($part) {
        return $part !== ''
            && strpos($part, MarketDataTemporalIdentityTraceabilitySpec::ATTEMPT
                .': applicability_normalized=') !== 0;
    }));
    if ($replacement !== null) {
        $parts[] = $replacement;
    }

    return implode(' | ', $parts);
}

[$headers, $rows, $bom] = b05ReadMatrix($matrixPath);
$owners = MarketDataTemporalIdentityTraceabilitySpec::requiredOwners();
$parents = MarketDataTemporalIdentityTraceabilitySpec::predicateParents();
$textByRule = [];
foreach ($rows as $row) {
    $textByRule[$row['rule_id']] = $row['rule_text'];
}

$seen = [];
$counts = ['reference' => 0, 'required' => 0, 'mandatory_b05' => 0, 'moved' => 0, 'contextual' => 0];

foreach ($rows as &$row) {
    $document = $row['strategy_document_id'];
    if (! isset(MarketDataTemporalIdentityTraceabilitySpec::DOCUMENT_COUNTS[$document])) {
        continue;
    }

    $number = b05RuleNumber($row['rule_id']);
    $seen[$document][$number] = true;
    $owner = $owners[$document][$number] ?? null;
    $row['current_evidence_ids'] = '';

    if ($owner === null) {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        $row['applicability'] = 'REFERENCE_ONLY';
        $row['coverage_status'] = 'REFERENCE_ONLY';
        $row['notes'] = b05ReplaceAttemptNote($row['notes'], null);
        $counts['reference']++;
        continue;
    }

    $row['coverage_requirement'] = 'REQUIRED';
    $row['applicability'] = 'MANDATORY';
    $row['coverage_status'] = 'NOT_ASSESSED';
    if ($owner !== MarketDataTemporalIdentityTraceabilitySpec::STAGE) {
        $row['supporting_stages'] = b05AddSupportingStage(
            $row['supporting_stages'],
            MarketDataTemporalIdentityTraceabilitySpec::STAGE
        );
        $row['primary_stage'] = $owner;
        $counts['moved']++;
    } else {
        $row['primary_stage'] = MarketDataTemporalIdentityTraceabilitySpec::STAGE;
        $counts['mandatory_b05']++;
    }

    $parentRule = $parents[$row['rule_id']] ?? null;
    if ($parentRule !== null && ! isset($textByRule[$parentRule])) {
        throw new RuntimeException('Predicate parent does not exist: '.$parentRule);
    }
    $context = $parentRule === null ? 'SELF_CONTAINED' : $parentRule;
    $normalized = $parentRule === null
        ? b05PredicateText($row['rule_text'])
        : b05PredicateText($textByRule[$parentRule]).' '.b05PredicateText($row['rule_text']);
    if ($parentRule !== null) {
        $counts['contextual']++;
    }

    $note = MarketDataTemporalIdentityTraceabilitySpec::ATTEMPT
        .': applicability_normalized=MANDATORY; predicate_context='.$context
        .'; normalized_predicate='.$normalized
        .'; applicability_basis=always_applicable: the obligation exists now and does not depend on an external condition; '
        .'proof_owner_confirmed='.$owner
        .'; proof_owner_basis=executable responsibility under the current build sequence';
    $row['notes'] = b05ReplaceAttemptNote($row['notes'], $note);
    $counts['required']++;
}
unset($row);

foreach (MarketDataTemporalIdentityTraceabilitySpec::DOCUMENT_COUNTS as $document => $expected) {
    $actual = count($seen[$document] ?? []);
    if ($actual !== $expected) {
        throw new RuntimeException($document.' corpus changed: '.$actual.' != '.$expected);
    }
}

$importedOwned = 0;
foreach ($rows as $row) {
    if (in_array($row['rule_id'], MarketDataTemporalIdentityTraceabilitySpec::IMPORTED_B05_RULES, true)) {
        if ($row['primary_stage'] !== MarketDataTemporalIdentityTraceabilitySpec::STAGE
            || $row['coverage_requirement'] !== 'REQUIRED') {
            throw new RuntimeException('Imported B05 rule lost its assignment: '.$row['rule_id']);
        }
        $importedOwned++;
    }
}
if ($importedOwned !== count(MarketDataTemporalIdentityTraceabilitySpec::IMPORTED_B05_RULES)) {
    throw new RuntimeException('Imported B05 rules missing from the matrix.');
}

$denominator = $counts['mandatory_b05'] + $importedOwned;
if ($denominator !== MarketDataTemporalIdentityTraceabilitySpec::EXPECTED_B05_DENOMINATOR) {
    throw new RuntimeException('B05 denominator must be '
        .MarketDataTemporalIdentityTraceabilitySpec::EXPECTED_B05_DENOMINATOR.', got '.$denominator);
}
$counts['imported_b05'] = $importedOwned;
$counts['b05_denominator'] = $denominator;

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
    fputcsv($handle, array_map(function ($header) use ($row) {
        return $row[$header];
    }, $headers));
}
fclose($handle);
echo 'WROTE '.$matrixPath.PHP_EOL;
