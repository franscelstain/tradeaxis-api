<?php
/**
 * Authors the frozen registry inputs of candidate-v4 (unchanged from candidate-v3 and candidate-v2; D-MD-B18-A002-014, Q4 = A).
 *
 *   inputs/frozen_reason_registry.json     the governed reason registry entries, frozen
 *   inputs/frozen_registry_literals.json   the two registry literals that are compiled into the implementation
 *
 * AUTHORING TOOL, NOT THE ORACLE. This script is run once by the package author to freeze the reason registry as a declaration. It reads the
 * governed reason registry table (the registry as the platform persists it) and the authority document, and writes the frozen input together
 * with a cross-check against the authority document. The reference oracle never runs this script and never opens a database: it reads only the
 * frozen file written here.
 *
 * Usage: php author_registry_inputs.php <database>     (the database whose `eod_reason_codes` table is the registry to freeze)
 */
$inputs = dirname(__DIR__).'/inputs';
$database = $argv[1] ?? 'tradeaxis_testing';
$env = parse_ini_file(dirname(__DIR__, 5).'/.env', false, INI_SCANNER_RAW);
$pdo = new PDO('mysql:host='.($env['DB_HOST'] ?? '127.0.0.1').';port='.($env['DB_PORT'] ?? '3306').';dbname='.$database.';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD']);
$rows = $pdo->query('select code, category, description, severity, is_active from eod_reason_codes order by code')->fetchAll(PDO::FETCH_ASSOC);
$entries = [];
foreach ($rows as $row) {
    $entries[] = ['code' => (string) $row['code'], 'category' => (string) $row['category'], 'description' => (string) $row['description'],
        'severity' => (string) $row['severity'], 'is_active' => (bool) $row['is_active']];
}
usort($entries, static function ($a, $b) { return strcmp($a['code'], $b['code']); });

// cross-check against the authority document (Reason_Codes_Registry.md): which codes, categories, severities and descriptions agree
$docPath = dirname(__DIR__, 5).'/docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md';
$doc = [];
foreach (file($docPath, FILE_IGNORE_NEW_LINES) as $line) {
    if (preg_match('/^\| `([A-Z0-9_]+)` \| ([A-Z0-9_]+) \| ([^|]+?) \| (.*) \|$/', $line, $m)) {
        $doc[$m[1]] = ['category' => $m[2], 'severity' => trim($m[3]), 'description' => $m[4]];
    }
}
$frozen = [];
foreach ($entries as $e) {
    $frozen[$e['code']] = $e;
}
$onlyFrozen = array_values(array_diff(array_keys($frozen), array_keys($doc)));
$onlyDoc = array_values(array_diff(array_keys($doc), array_keys($frozen)));
$categoryDiffs = [];
$descriptionDiffs = [];
$severityMap = [];
foreach ($frozen as $code => $e) {
    if (! isset($doc[$code])) {
        continue;
    }
    if ($doc[$code]['category'] !== $e['category']) {
        $categoryDiffs[] = $code;
    }
    if ($doc[$code]['description'] !== $e['description']) {
        $descriptionDiffs[] = $code;
    }
    $key = $doc[$code]['severity'].' => '.$e['severity'].' / is_active='.(int) $e['is_active'];
    $severityMap[$key] = ($severityMap[$key] ?? 0) + 1;
}
ksort($severityMap);
$document = [
    'label' => 'FROZEN_REASON_REGISTRY_FOR_SYNTHETIC_V2_WORLD',
    'frozen_from' => 'the governed reason registry table `eod_reason_codes` of the platform database, read once by the package author at authoring time',
    'entry_count' => count($entries),
    'entry_member_set' => ['code', 'category', 'description', 'severity', 'is_active'],
    'authority_document' => ['path' => 'docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md', 'sha256' => hash_file('sha256', $docPath), 'rows_parsed' => count($doc)],
    'authority_crosscheck' => [
        'codes_only_in_frozen_registry' => $onlyFrozen,
        'codes_only_in_authority_document' => $onlyDoc,
        'category_differences' => $categoryDiffs,
        'severity_and_activity_mapping' => $severityMap,
        'description_text_differences' => ['count' => count($descriptionDiffs), 'codes' => $descriptionDiffs,
            'note' => 'The persisted descriptions are seed text; the authority document words them differently for these codes. The registry identity binds the persisted text. Registry rule 3 lets description text be clarified, which is why this is disclosed to the reviewer rather than hidden.'],
    ],
    'entries' => $entries,
];
file_put_contents($inputs.'/frozen_reason_registry.json', json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");

$literals = [
    'label' => 'FROZEN_REGISTRY_LITERALS_COMPILED_INTO_THE_IMPLEMENTATION',
    'registry_contract' => ['value' => 'producer_registry_content_v1', 'source' => 'literal of the producer registry capture (its exact bytes are the identity); no configuration key exists for it'],
    'read_model_version' => ['value' => 'market_data_read_product_v1', 'source' => 'the one read-model identity the read product and the publication manifest already set; no configuration key exists for it'],
];
file_put_contents($inputs.'/frozen_registry_literals.json', json_encode($literals, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo 'frozen '.count($entries)." reason entries from {$database}; description differences from the authority document: ".count($descriptionDiffs)."\n";
echo 'codes only in registry: '.count($onlyFrozen).', only in document: '.count($onlyDoc).', category differences: '.count($categoryDiffs)."\n";
