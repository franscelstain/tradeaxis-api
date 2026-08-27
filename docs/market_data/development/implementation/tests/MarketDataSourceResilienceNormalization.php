<?php

require_once __DIR__.'/MarketDataSourceResilienceTraceabilitySpec.php';
require_once __DIR__.'/MarketDataClassificationConsistencyGate.php';

/** Governed MD-B08-A001 matrix normalization. Usage: php this-file.php [--apply]. */
$root = dirname(__DIR__, 5);
$matrixPath = $root.'/docs/market_data/authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv';
$apply = in_array('--apply', $argv, true);

function b08ReadMatrix(string $path): array
{
    $handle = fopen($path, 'r');
    if ($handle === false) {
        throw new RuntimeException('Unable to open traceability matrix.');
    }
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

function b08RuleNumber(string $ruleId): int
{
    return (int) substr($ruleId, strrpos($ruleId, 'R') + 1);
}

function b08AddSupportingStage(string $raw, string $stage): string
{
    $stages = array_values(array_filter(array_map('trim', explode(';', $raw))));
    if (! in_array($stage, $stages, true)) {
        $stages[] = $stage;
    }

    return implode(';', array_values(array_unique($stages)));
}

function b08PredicateText(string $text): string
{
    return preg_replace('/\s+/', ' ', trim(preg_replace('/^\s*([-*]|\d+\.)\s+/', '', $text)));
}

function b08ReplaceAttemptNote(string $raw, ?string $replacement): string
{
    $parts = array_values(array_filter(array_map('trim', explode(' | ', $raw)), static function ($part) {
        if ($part === '') {
            return false;
        }
        foreach ([MarketDataSourceResilienceTraceabilitySpec::ATTEMPT,
            MarketDataSourceResilienceTraceabilitySpec::REMEDIATION_ATTEMPT] as $attempt) {
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

function b08InsertAdditiveRules(array $rows): array
{
    $byId = [];
    foreach ($rows as $row) {
        $byId[$row['rule_id']] = true;
    }

    foreach (MarketDataSourceResilienceTraceabilitySpec::ADDITIVE_RULES as $ruleId => $definition) {
        if (isset($byId[$ruleId])) {
            continue;
        }
        foreach ($rows as $existing) {
            if ($existing['strategy_document_id'] === $definition['strategy_document_id']
                && (int) $existing['source_line'] === (int) $definition['source_line']) {
                throw new RuntimeException('Source line already represented while additive rule is missing: '.$ruleId);
            }
        }

        $new = [
            'rule_id' => $ruleId,
            'strategy_document_id' => $definition['strategy_document_id'],
            'strategy_owner' => $definition['strategy_owner'],
            'source_line' => $definition['source_line'],
            'section' => $definition['section'],
            'rule_text' => $definition['rule_text'],
            'rule_fingerprint_sha1' => strtoupper(sha1($definition['rule_text'])),
            'coverage_requirement' => 'REFERENCE_ONLY',
            'applicability' => 'REFERENCE_ONLY',
            'primary_stage' => 'MD-B08',
            'supporting_stages' => '',
            'active' => 'YES',
            'coverage_status' => 'REFERENCE_ONLY',
            'current_evidence_ids' => '',
            'notes' => '',
        ];

        $insertAt = count($rows);
        foreach ($rows as $i => $existing) {
            if ($existing['strategy_document_id'] !== $definition['strategy_document_id']) {
                continue;
            }
            if ((int) $existing['source_line'] > (int) $definition['source_line']) {
                $insertAt = $i;
                break;
            }
            $insertAt = $i + 1;
        }
        array_splice($rows, $insertAt, 0, [$new]);
        $byId[$ruleId] = true;
    }

    return $rows;
}

[$headers, $rows, $bom] = b08ReadMatrix($matrixPath);
$rows = b08InsertAdditiveRules($rows);
$owners = MarketDataSourceResilienceTraceabilitySpec::requiredOwners();
$parents = MarketDataSourceResilienceTraceabilitySpec::predicateParents();
$overrides = MarketDataSourceResilienceTraceabilitySpec::normalizedPredicateOverrides();
$external = array_fill_keys(MarketDataSourceResilienceTraceabilitySpec::EXTERNAL_RULES, true);
$textByRule = [];
foreach ($rows as $row) {
    $textByRule[$row['rule_id']] = $row['rule_text'];
}

$seen = [];
$b08Owned = [];
$skipped = [];
$preserved = [];
$priorSatisfied = [];
foreach ($rows as $row) {
    if (strtoupper(trim($row['active'])) === 'YES'
        && $row['primary_stage'] === MarketDataSourceResilienceTraceabilitySpec::STAGE) {
        $b08Owned[$row['rule_id']] = true;
    }
    if ($row['coverage_status'] === 'SATISFIED'
        && $row['primary_stage'] !== MarketDataSourceResilienceTraceabilitySpec::STAGE) {
        $priorSatisfied[$row['rule_id']] = $row['primary_stage'];
    }
}
unset($row);
$counts = [
    'reviewed' => 0,
    'reference' => 0,
    'required' => 0,
    'mandatory_b08' => 0,
    'moved' => 0,
    'contextual' => 0,
    'additive_rules' => count(MarketDataSourceResilienceTraceabilitySpec::ADDITIVE_RULES),
];
foreach ($rows as &$row) {
    $document = $row['strategy_document_id'];
    $isSourceDocument = isset(MarketDataSourceResilienceTraceabilitySpec::SOURCE_DOCUMENT_COUNTS[$document]);
    if (! $isSourceDocument && ! isset($external[$row['rule_id']])) {
        $skipped[$row['rule_id']] = true;
        continue;
    }

    $counts['reviewed']++;
    $number = b08RuleNumber($row['rule_id']);
    if ($isSourceDocument) {
        $seen[$document][$number] = true;
    }
    $owner = $owners[$document][$number] ?? null;
    // A stage pass owns its own rows only. Clearing proof state on a row whose owner is another
    // stage silently unbinds predicates a different attempt already proved.
    $priorStatus = $row['coverage_status'];
    $priorEvidence = $row['current_evidence_ids'];
    $row['current_evidence_ids'] = '';

    if ($owner === null) {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        $row['applicability'] = 'REFERENCE_ONLY';
        $row['coverage_status'] = 'REFERENCE_ONLY';
        // Record the decision instead of erasing it. A reference row with empty notes is
        // indistinguishable from a row nobody examined, which is exactly how MD-S066-R0002 and
        // MD-S067-R0010 survived. The note asserts that this pass considered the row and
        // assigned it no proof obligation; it does not assert that the judgement is correct.
        $row['notes'] = b08ReplaceAttemptNote(
            $row['notes'],
            MarketDataSourceResilienceTraceabilitySpec::REMEDIATION_ATTEMPT
            .': applicability_normalized=REFERENCE_ONLY'
            .'; proof_owner_confirmed=MD-B08'
            .'; reference_basis='.(MarketDataClassificationConsistencyGate::structuralClass($row['rule_text']) ?? 'CONTEXT_ONLY')
            .'; stage_entry_review=re-derived against traceability standard sections 2 and 3; no executable proof obligation assigned'
        );
        $counts['reference']++;
        continue;
    }

    $row['coverage_requirement'] = 'REQUIRED';
    $row['applicability'] = 'MANDATORY';
    $row['coverage_status'] = 'NOT_ASSESSED';
    $row['primary_stage'] = $owner;
    if ($owner === MarketDataSourceResilienceTraceabilitySpec::STAGE) {
        $counts['mandatory_b08']++;
    } else {
        $row['supporting_stages'] = b08AddSupportingStage(
            $row['supporting_stages'],
            MarketDataSourceResilienceTraceabilitySpec::STAGE
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
        ? b08PredicateText($row['rule_text'])
        : b08PredicateText($textByRule[$parentRule]).' '.b08PredicateText($row['rule_text']));
    if ($parentRule !== null) {
        $counts['contextual']++;
    }
    $remediation = MarketDataSourceResilienceTraceabilitySpec::REMEDIATED_RULES[$row['rule_id']] ?? null;
    $attempt = $remediation === null
        ? MarketDataSourceResilienceTraceabilitySpec::ATTEMPT
        : MarketDataSourceResilienceTraceabilitySpec::REMEDIATION_ATTEMPT;
    $row['notes'] = b08ReplaceAttemptNote(
        $row['notes'],
        $attempt
        .': applicability_normalized=MANDATORY; predicate_context='.$context
        .'; normalized_predicate='.$normalized
        .'; applicability_basis=always_applicable_when_the_owning_branch_or_lifecycle_is_invoked; '
        .'proof_owner_confirmed='.$owner
        .'; proof_owner_basis=current build-sequence executable responsibility; '
        .'stage_entry_review=MD-B08 resolved transitional and mixed classification without denominator reduction for convenience'
        .($remediation === null ? '' : '; '.$remediation)
    );
    $counts['required']++;
}
unset($row);

foreach (MarketDataSourceResilienceTraceabilitySpec::SOURCE_DOCUMENT_COUNTS as $document => $expected) {
    $actual = count($seen[$document] ?? []);
    if ($actual !== $expected) {
        throw new RuntimeException($document.' corpus changed: '.$actual.' != '.$expected);
    }
}
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

// A normalization that selects its own scope can omit a row and still report success.
$unexamined = array_keys(array_intersect_key($b08Owned, $skipped));
if ($unexamined !== []) {
    throw new RuntimeException('B08-owned rows never examined by this normalization: '
        .implode(', ', $unexamined));
}
$counts['b08_owned_examined'] = count($b08Owned);

if ($counts['mandatory_b08'] !== MarketDataSourceResilienceTraceabilitySpec::EXPECTED_B08_DENOMINATOR) {
    throw new RuntimeException('B08 denominator mismatch: '.$counts['mandatory_b08']);
}
$counts['b08_denominator'] = $counts['mandatory_b08'];

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
