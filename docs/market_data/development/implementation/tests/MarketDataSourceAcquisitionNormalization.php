<?php

require_once __DIR__.'/MarketDataSourceAcquisitionTraceabilitySpec.php';

/** Governed MD-B07-A001 matrix normalization. Usage: php this-file.php [--apply]. */

$matrixPath = dirname(__DIR__, 3).'/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);

function b07ReadMatrix(string $path): array
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

function b07RuleNumber(string $ruleId): int
{
    return (int) substr($ruleId, strrpos($ruleId, 'R') + 1);
}

function b07AddSupportingStage(string $raw, string $stage): string
{
    $stages = array_values(array_filter(array_map('trim', explode(';', $raw))));
    if (! in_array($stage, $stages, true)) {
        $stages[] = $stage;
    }

    return implode(';', array_values(array_unique($stages)));
}

function b07PredicateText(string $text): string
{
    return preg_replace('/\s+/', ' ', trim(preg_replace('/^\s*([-*]|\d+\.)\s+/', '', $text)));
}

function b07ReplaceAttemptNote(string $raw, ?string $replacement): string
{
    $parts = array_values(array_filter(array_map('trim', explode(' | ', $raw)), static function ($part) {
        if ($part === '') {
            return false;
        }
        foreach ([MarketDataSourceAcquisitionTraceabilitySpec::ATTEMPT,
            MarketDataSourceAcquisitionTraceabilitySpec::REMEDIATION_ATTEMPT] as $attempt) {
            if (strpos($part, $attempt.': applicability_normalized=') === 0) {
                return false;
            }
        }

        return true;
    }));
    if ($replacement !== null) {
        $parts[] = $replacement;
    }

    return implode(' | ', $parts);
}

[$headers, $rows, $bom] = b07ReadMatrix($matrixPath);
$owners = MarketDataSourceAcquisitionTraceabilitySpec::requiredOwners();
$parents = MarketDataSourceAcquisitionTraceabilitySpec::predicateParents();
$overrides = MarketDataSourceAcquisitionTraceabilitySpec::normalizedPredicateOverrides();
$external = array_fill_keys(MarketDataSourceAcquisitionTraceabilitySpec::EXTERNAL_RULES, true);
$textByRule = [];
foreach ($rows as $row) {
    $textByRule[$row['rule_id']] = $row['rule_text'];
}

$seen = [];
$b07Owned = [];
$skipped = [];
$preserved = [];
$priorSatisfied = [];
foreach ($rows as $row) {
    if ($row['coverage_status'] === 'SATISFIED' && $row['primary_stage'] !== MarketDataSourceAcquisitionTraceabilitySpec::STAGE) {
        $priorSatisfied[$row['rule_id']] = $row['primary_stage'];
    }
}
unset($row);
foreach ($rows as $row) {
    if (strtoupper(trim($row['active'])) === 'YES'
        && $row['primary_stage'] === MarketDataSourceAcquisitionTraceabilitySpec::STAGE) {
        $b07Owned[$row['rule_id']] = true;
    }
}
unset($row);
$counts = ['reviewed' => 0, 'reference' => 0, 'required' => 0, 'mandatory_b07' => 0, 'moved' => 0, 'contextual' => 0];
foreach ($rows as &$row) {
    $document = $row['strategy_document_id'];
    $isSourceDocument = isset(MarketDataSourceAcquisitionTraceabilitySpec::SOURCE_DOCUMENT_COUNTS[$document]);
    if (! $isSourceDocument && ! isset($external[$row['rule_id']])) {
        $skipped[$row['rule_id']] = true;
        continue;
    }

    $counts['reviewed']++;
    $number = b07RuleNumber($row['rule_id']);
    if ($isSourceDocument) {
        $seen[$document][$number] = true;
    }
    $owner = $owners[$document][$number] ?? null;
    // A stage normalization owns its own rows. For a row whose proof owner is another stage the
    // pass may set ownership, but it must not touch that stage's closed proof state: clearing it
    // silently unbinds predicates a different attempt already proved. Recorded as the reason the
    // A002 re-run stripped 30 bindings from MD-B08, MD-B09 and MD-B10.
    $priorStatus = $row['coverage_status'];
    $priorEvidence = $row['current_evidence_ids'];
    $row['current_evidence_ids'] = '';

    if ($owner === null) {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        $row['applicability'] = 'REFERENCE_ONLY';
        $row['coverage_status'] = 'REFERENCE_ONLY';
        $row['notes'] = b07ReplaceAttemptNote($row['notes'], null);
        $counts['reference']++;
        continue;
    }

    $row['coverage_requirement'] = 'REQUIRED';
    $row['applicability'] = 'MANDATORY';
    $row['coverage_status'] = 'NOT_ASSESSED';
    $row['primary_stage'] = $owner;
    if ($owner === MarketDataSourceAcquisitionTraceabilitySpec::STAGE) {
        $counts['mandatory_b07']++;
    } else {
        $row['supporting_stages'] = b07AddSupportingStage(
            $row['supporting_stages'],
            MarketDataSourceAcquisitionTraceabilitySpec::STAGE
        );
        $row['coverage_status'] = $priorStatus;
        $row['current_evidence_ids'] = $priorEvidence;
        if ($priorStatus === 'SATISFIED') {
            $preserved[$row['rule_id']] = $priorEvidence;
        }
        $counts['moved']++;
    }

    $parentRule = $parents[$row['rule_id']] ?? null;
    if ($parentRule !== null && ! isset($textByRule[$parentRule])) {
        throw new RuntimeException('Predicate parent does not exist: '.$parentRule);
    }
    $context = $parentRule === null ? 'SELF_CONTAINED' : $parentRule;
    $normalized = $overrides[$row['rule_id']] ?? ($parentRule === null
        ? b07PredicateText($row['rule_text'])
        : b07PredicateText($textByRule[$parentRule]).' '.b07PredicateText($row['rule_text']));
    if ($parentRule !== null) {
        $counts['contextual']++;
    }
    $remediation = MarketDataSourceAcquisitionTraceabilitySpec::REMEDIATED_RULES[$row['rule_id']] ?? null;
    $attempt = $remediation === null
        ? MarketDataSourceAcquisitionTraceabilitySpec::ATTEMPT
        : MarketDataSourceAcquisitionTraceabilitySpec::REMEDIATION_ATTEMPT;
    $row['notes'] = b07ReplaceAttemptNote(
        $row['notes'],
        $attempt
        .': applicability_normalized=MANDATORY; predicate_context='.$context
        .'; normalized_predicate='.$normalized
        .'; applicability_basis=always_applicable: the acquisition/import/backfill branch must implement the obligation whenever invoked and is not an optional capability; '
        .'proof_owner_confirmed='.$owner
        .'; proof_owner_basis=executable responsibility under the current build sequence'
        .($remediation === null ? '' : '; '.$remediation)
    );
    $counts['required']++;
}
unset($row);

foreach (MarketDataSourceAcquisitionTraceabilitySpec::SOURCE_DOCUMENT_COUNTS as $document => $expected) {
    $actual = count($seen[$document] ?? []);
    if ($actual !== $expected) {
        throw new RuntimeException($document.' corpus changed: '.$actual.' != '.$expected);
    }
}
// A normalization that selects its own scope can silently omit a row and still report success.
// MD-B07-A001 did exactly that. Every row the matrix assigns to this stage must be examined by
// this pass, or the pass is not an entry review of the stage — it is a review of a subset.
// A stage pass must not unbind another stage's closed proof. Every row that arrived SATISFIED
// under a different proof owner must still be SATISFIED when this pass finishes.
$statusNow = [];
foreach ($rows as $row) {
    $statusNow[$row['rule_id']] = $row['coverage_status'];
}
unset($row);
$unbound = [];
foreach ($priorSatisfied as $rule => $stage) {
    if (($statusNow[$rule] ?? null) !== 'SATISFIED') {
        $unbound[] = $rule.' ('.$stage.')';
    }
}
if ($unbound !== []) {
    throw new RuntimeException('this pass unbound closed proof owned by another stage: '
        .implode(', ', $unbound));
}
$counts['foreign_bindings_preserved'] = count($preserved);

$unexamined = array_keys(array_intersect_key($b07Owned, $skipped));
if ($unexamined !== []) {
    throw new RuntimeException('B07-owned rows never examined by this normalization: '
        .implode(', ', $unexamined));
}
$counts['b07_owned_examined'] = count($b07Owned);

if ($counts['mandatory_b07'] !== MarketDataSourceAcquisitionTraceabilitySpec::EXPECTED_B07_DENOMINATOR) {
    throw new RuntimeException('B07 denominator mismatch: '.$counts['mandatory_b07']);
}
$counts['b07_denominator'] = $counts['mandatory_b07'];

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
    fputcsv($handle, array_map(static function ($header) use ($row) {
        return $row[$header];
    }, $headers));
}
fclose($handle);
echo 'WROTE '.$matrixPath.PHP_EOL;
