<?php
/**
 * Authors inputs/frozen_config_content.txt of candidate-v5: the frozen configuration snapshot content of the synthetic world, WITH the derived
 * `reason_registry` member that MD-B04-A003 added to every new configuration snapshot (E-MD-B04-A003-002; D-MD-B18-A002-018, Q9 = A1).
 *
 * Candidate-v4 froze `{resolved_config, semantic_bindings}`. The snapshot content of the final build is the canonical JSON of the THREE members
 * `{reason_registry, resolved_config, semantic_bindings}` and config_content_hash is the SHA-256 of that content, so the member changes the config hash and
 * everything that binds it (bars, indicators and eligibility rows, the publication manifest, the replay bound input config_snapshot_hash).
 *
 * AUTHORING TOOL, NOT THE ORACLE. Standalone: no application code, no database, no run, no replay output, no target output.
 *
 *   inputs the tool reads    inputs/frozen_config_content.txt           the base members `resolved_config` and `semantic_bindings` (any `reason_registry`
 *                                                                        member already present is DISCARDED and derived again)
 *                            inputs/frozen_reason_registry.json         the frozen eod_reason_codes content (437 entries)
 *   derivation of the member the schema `market-data-reason-registry/v2` document over the frozen entries ordered by code (D-MD-B18-A002-014 Q4 = A),
 *                            its SHA-256 is `semantic_identity`; `identity_contract` is the schema string the governed record E-MD-B04-A003-002 names
 *   output                   inputs/frozen_config_content.txt           the three members, canonical JSON (keys ordered at every level, unescaped slashes
 *                                                                        and unicode, preserved zero fractions)
 *
 * The reference oracle re-derives the member and the bytes itself and refuses a frozen file that differs; it never trusts this tool.
 *
 * Usage: php author_config_content.php
 */
$inputs = dirname(__DIR__).'/inputs';
$base = json_decode((string) file_get_contents($inputs.'/frozen_config_content.txt'), true);
$registry = json_decode((string) file_get_contents($inputs.'/frozen_reason_registry.json'), true);
if (! is_array($base) || ! isset($base['resolved_config'], $base['semantic_bindings']) || ! is_array($registry) || ! isset($registry['entries'])) {
    fwrite(STDERR, "frozen inputs missing or unreadable\n");
    exit(2);
}

function order($value)
{
    if (! is_array($value)) {
        return $value;
    }
    if ($value !== [] && array_keys($value) !== range(0, count($value) - 1)) {
        ksort($value, SORT_STRING);
    }
    foreach ($value as $k => $child) {
        $value[$k] = order($child);
    }

    return $value;
}

function canonical(array $document): string
{
    return json_encode(order($document), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
}

$entries = [];
foreach ($registry['entries'] as $e) {
    $entries[$e['code']] = ['code' => $e['code'], 'category' => $e['category'], 'description' => $e['description'], 'severity' => $e['severity'], 'is_active' => (bool) $e['is_active']];
}
if (count($entries) !== count($registry['entries']) || count($entries) !== $registry['entry_count']) {
    fwrite(STDERR, "duplicate or miscounted registry entries\n");
    exit(2);
}
ksort($entries, SORT_STRING);
$identity = hash('sha256', canonical(['schema_version' => 'market-data-reason-registry/v2', 'entries' => array_values($entries)]));

$content = canonical([
    'reason_registry' => ['identity_contract' => 'market-data-reason-registry/v2', 'semantic_identity' => $identity],
    'resolved_config' => $base['resolved_config'],
    'semantic_bindings' => $base['semantic_bindings'],
]);
file_put_contents($inputs.'/frozen_config_content.txt', $content);
echo 'reason_registry semantic_identity ', $identity, "\nframed config content bytes ", strlen($content), ' sha256 ', hash('sha256', $content), "\n";
