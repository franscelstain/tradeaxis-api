<?php

require_once __DIR__.'/MarketDataSourceResilienceTraceabilitySpec.php';

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
        return $part !== '' && strpos($part, MarketDataSourceResilienceTraceabilitySpec::ATTEMPT
            .': applicability_normalized=') !== 0;
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
        continue;
    }

    $counts['reviewed']++;
    $number = b08RuleNumber($row['rule_id']);
    if ($isSourceDocument) {
        $seen[$document][$number] = true;
    }
    $owner = $owners[$document][$number] ?? null;
    $row['current_evidence_ids'] = '';

    if ($owner === null) {
        $row['coverage_requirement'] = 'REFERENCE_ONLY';
        $row['applicability'] = 'REFERENCE_ONLY';
        $row['coverage_status'] = 'REFERENCE_ONLY';
        $row['notes'] = b08ReplaceAttemptNote($row['notes'], null);
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
    $row['notes'] = b08ReplaceAttemptNote(
        $row['notes'],
        MarketDataSourceResilienceTraceabilitySpec::ATTEMPT
        .': applicability_normalized=MANDATORY; predicate_context='.$context
        .'; normalized_predicate='.$normalized
        .'; applicability_basis=always_applicable_when_the_owning_branch_or_lifecycle_is_invoked; '
        .'proof_owner_confirmed='.$owner
        .'; proof_owner_basis=current build-sequence executable responsibility; '
        .'stage_entry_review=MD-B08 resolved transitional and mixed classification without denominator reduction for convenience'
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
