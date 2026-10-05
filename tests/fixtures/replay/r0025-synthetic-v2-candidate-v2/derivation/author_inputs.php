<?php
/**
 * Authors the frozen inputs of the synthetic V2 world for the second independent MD-S003-R0025 golden fixture candidate (candidate-v2).
 *
 * Standalone: it reads no application code, no database, no run, no publication and no replay output. Every value below is a
 * declaration of the synthetic world. The retained roots are fixed literals; they are not generated from any allocation.
 *
 * Usage: php author_inputs.php   (writes ../inputs/*)
 */
$inputs = dirname(__DIR__).'/inputs';

$world = [
    'label' => 'SYNTHETIC_TEST_WORLD_NOT_A_REAL_INSTRUMENT',
    'world_version' => 'r0025-synthetic-v2/2',
    'purpose' => 'Second independent MD-S003-R0025 golden fixture candidate, successor of the rejected candidate-v1 (D-MD-B18-A002-011, D-MD-B18-A002-013, D-MD-B18-A002-014). Test-only; never master-data truth.',
    'trade_date' => '2026-03-23',
    'run_clock' => '2026-03-25 10:30:00',
    'platform_timezone' => 'Asia/Jakarta',
    'artifact_hash_profile' => 'market-data-semantic-hash/v2',
    'listing' => [
        'ticker_code' => 'SYNV2',
        'company_name' => 'SYNTHETIC V2 TEST LISTING',
        'exchange_namespace' => 'IDX',
        'exchange_symbol' => 'SYNV2',
        'provider_namespace' => 'yahoo_finance',
        'provider_symbol' => 'SYNV2.JK',
        'venue' => 'SYNTHETIC_VENUE',
        'market_segment' => 'SYNTHETIC_SEGMENT',
        'board' => 'SYNTHETIC_BOARD',
        'listed_date' => '2020-01-01',
    ],
    'retained_roots' => [
        'issuer' => '5a000000-0000-4000-8000-000000000001',
        'instrument' => '5a000000-0000-4000-8000-000000000002',
        'listing' => '5a000000-0000-4000-8000-000000000003',
    ],
    'foundation_validity' => ['valid_from' => '2020-01-01 00:00:00', 'valid_to' => null, 'known_at' => '2020-01-01 00:00:00', 'history_verified_through' => '2030-01-01 00:00:00'],
    'bar' => ['trade_date' => '2026-03-23', 'open' => 100, 'high' => 110, 'low' => 95, 'close' => 105, 'volume' => 1000, 'provider_timestamp_unix' => 1774231200],
    'calendar' => [
        'market_code' => 'IDX', 'market_segment' => 'REGULAR', 'timezone' => 'Asia/Jakarta', 'first_date' => '2026-02-20', 'last_date' => '2026-03-23',
        'trading_days' => 'Monday to Friday', 'session_open' => '09:00:00', 'session_close' => '16:00:00', 'recorded_at_time' => '17:00:00',
        'source_version' => 'idx-test-calendar-v1', 'provenance_tier' => 'VERIFIED',
        'source_ref_template' => 'https://www.idx.co.id/test-calendar/{date}',
    ],
    'history' => 'none: exactly one bar is frozen, so every indicator that needs history is insufficient',
];

// The provider response bytes: one daily bar. A single line, no trailing newline, so the payload hash is the hash of exactly these bytes.
$response = '{"chart":{"result":[{"meta":{"symbol":"SYNV2.JK","exchangeTimezoneName":"Asia/Jakarta"},"timestamp":['.$world['bar']['provider_timestamp_unix'].'],"indicators":{"quote":[{"open":['.$world['bar']['open'].'],"high":['.$world['bar']['high'].'],"low":['.$world['bar']['low'].'],"close":['.$world['bar']['close'].'],"volume":['.$world['bar']['volume'].']}]}}],"error":null}}';

// The retained foundation registry document (security-identity-registry/v1) restored through FoundationService::restore.
$l = $world['listing'];
$v = $world['foundation_validity'];
$hash = hash('sha256', 'R0025_SYNTHETIC_V2_WORLD_REGISTRY_V1');
$base = 'test-only://r0025-synthetic-v2-world/v1/';
$revisionSpecs = [
    ['5b000000-0000-4000-8000-000000000001', $world['retained_roots']['issuer'], 'PROFILE', ['name' => 'Synthetic issuer'], 'profile'],
    ['5b000000-0000-4000-8000-000000000002', $world['retained_roots']['listing'], 'LISTING', ['venue' => $l['venue'], 'continuity' => 'NEW_LISTING', 'history_verified_through' => $v['history_verified_through'], 'listing_state' => 'LISTED', 'change_reason' => 'SYNTHETIC_INITIAL_ADMISSION'], 'listing'],
    ['5b000000-0000-4000-8000-000000000003', $world['retained_roots']['listing'], 'SYMBOL', ['namespace' => $l['exchange_namespace'], 'symbol' => $l['exchange_symbol']], 'symbol'],
    ['5b000000-0000-4000-8000-000000000004', $world['retained_roots']['listing'], 'BOARD', ['market_segment' => $l['market_segment'], 'board' => $l['board']], 'board'],
    ['5b000000-0000-4000-8000-000000000005', $world['retained_roots']['listing'], 'PROVIDER_MAPPING', ['namespace' => $l['provider_namespace'], 'symbol' => $l['provider_symbol']], 'provider_mapping'],
];
$sources = [];
$revisions = [];
foreach ($revisionSpecs as [$id, $entity, $type, $data, $slug]) {
    $locator = $base.$slug;
    $sources[$locator] = [
        'facts' => $data + ['valid_from' => $v['valid_from'], 'valid_to' => $v['valid_to']], 'admitted_fields' => array_keys($data),
        'evidence_class' => 'PRIMARY_MASTER_EVIDENCE', 'source_revision' => 'synthetic-v1', 'source_known_at' => $v['known_at'], 'captured_at' => $v['known_at'],
        'review_reference' => 'TEST_ONLY_INDEPENDENT_DECLARATION',
    ];
    $revisions[] = [
        'revision_id' => $id, 'identity_id' => $entity, 'revision_type' => $type, 'state' => 'ADMITTED', 'valid_from' => $v['valid_from'], 'valid_to' => $v['valid_to'],
        'known_at' => $v['known_at'], 'recorded_at' => $v['known_at'], 'package_hash' => $hash, 'source_locator' => $locator, 'supersedes_revision_id' => null, 'data' => $data,
    ];
}
$entities = [];
foreach ([['issuer', 'ISSUER', null], ['instrument', 'INSTRUMENT', 'issuer'], ['listing', 'LISTING', 'instrument']] as [$key, $type, $parent]) {
    $entities[] = ['identity_id' => $world['retained_roots'][$key], 'entity_type' => $type, 'parent_identity_id' => $parent === null ? null : $world['retained_roots'][$parent], 'package_hash' => $hash, 'source_locator' => $base.'listing'];
}
$registry = [
    'registry_version' => 'security-identity-registry/v1',
    'packages' => [['package_hash' => $hash, 'package_version' => 'test-only-r0025-synthetic-v2/1', 'recorded_at' => $v['known_at'], 'admission_evidence' => 'TEST_ONLY_NOT_PRODUCTION_ADMISSION', 'sources' => $sources]],
    'entities' => $entities,
    'revisions' => $revisions,
    'holds' => [],
];

file_put_contents($inputs.'/synthetic_world.json', json_encode($world, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents($inputs.'/provider_response.json', $response);
file_put_contents($inputs.'/foundation_registry.json', json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo "authored: synthetic_world.json, provider_response.json (".strlen($response)." bytes), foundation_registry.json\n";
