<?php
/**
 * R0025 synthetic V2 golden fixture candidate -- standalone reference oracle.
 *
 *   method:   r0025-synthetic-v2-reference-oracle
 *   version:  1.0.0
 *
 * WHAT THIS IS. A small separate reference implementation that takes ONLY the frozen inputs of this package and writes the literal
 * expected semantics and the literal expected hashes. It is meant to be read and re-run by an independent reviewer:
 *
 *     php reference_oracle.php            (writes ../expected/* and oracle_output.json; prints the sha256 of each output)
 *
 * WHAT IT DOES NOT DO. It includes no application code, autoloader or framework; it opens no database; it reads no run, publication,
 * lineage binding, artifact row, observation row or replay output; it calls none of ArtifactSemanticHashService,
 * SemanticNestedIdentityService, SemanticObservationIdentityService, DeterministicHashService, the publication serializer, the manifest
 * builder or the replay verifier. Its inputs are exactly:
 *
 *     ../inputs/synthetic_world.json        the synthetic world declaration (retained roots, listing, bar, calendar rule, clock)
 *     ../inputs/provider_response.json      the exact provider response bytes
 *     ../inputs/frozen_config_content.txt   the frozen configuration content (an input snapshot of the configuration source)
 *     PHP's own sha256/json functions
 *
 * SPECIFICATION TRANSCRIPTION POINTS (for the reviewer). The V2 profile is specified by governed records, not by this file:
 * E-MD-B10-A002-010 (`semantic_hash_contract`: framing "profile|domain" then LF then rows, identity = retained roots, ordering
 * trade_date then listing root, NULL = empty token), Audit_Hash_and_Reproducibility_Contract_LOCKED.md (SHA-256, `|` delimiter, LF row
 * separator, locked schema order, sorted canonical JSON) and Hash_Number_Formatting_LOCKED.md (4/2/10/12 decimals, integers, 0/1 flags,
 * dates, timestamps). The member ORDER of each V2 artifact and the member list of each nested set document are transcribed below from
 * those records and from the V2 member lists they enumerate; the reviewer compares them with the profile's declared members.
 * Vocabulary labels that no authority text defines (replay resolution labels, state names) are marked `vocabulary` in the output.
 */

$pkg = dirname(__DIR__);
$world = json_decode((string) file_get_contents($pkg.'/inputs/synthetic_world.json'), true);
$response = (string) file_get_contents($pkg.'/inputs/provider_response.json');
$configBytes = (string) file_get_contents($pkg.'/inputs/frozen_config_content.txt');
$config = json_decode($configBytes, true);
if (! is_array($world) || $response === '' || ! is_array($config)) {
    fwrite(STDERR, "frozen inputs missing or unreadable\n");
    exit(2);
}

const PROFILE = 'market-data-semantic-hash/v2';

// ---------------------------------------------------------------------------------------------------------------------
// serialization primitives (Audit_Hash contract + Hash_Number_Formatting)
// ---------------------------------------------------------------------------------------------------------------------

/** Fixed-point rendering without floating point: round half up at the given scale. */
function fixed($value, int $scale): string
{
    $raw = trim((string) $value);
    if (! preg_match('/^(-?)(\d+)(?:\.(\d+))?$/', $raw, $m)) {
        throw new RuntimeException('not a plain decimal: '.$raw);
    }
    $fraction = str_pad($m[3] ?? '', $scale + 1, '0');
    $digits = ltrim($m[2].substr($fraction, 0, $scale), '0');
    if ((int) $fraction[$scale] >= 5) {
        // increment the decimal digit string by one unit in the last place (no floating point, no extension)
        $digits = $digits === '' ? '0' : $digits;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            if ($digits[$i] !== '9') {
                $digits[$i] = (string) ((int) $digits[$i] + 1);
                break;
            }
            $digits[$i] = '0';
            if ($i === 0) {
                $digits = '1'.$digits;
            }
        }
    }
    $digits = str_pad($digits === '' ? '0' : $digits, $scale + 1, '0', STR_PAD_LEFT);

    return $m[1].substr($digits, 0, strlen($digits) - $scale).($scale > 0 ? '.'.substr($digits, -$scale) : '');
}

/** Canonical set/document JSON: objects by key, lists by their own JSON text, NULL as the empty token, integers kept. */
function canon($value)
{
    if (is_array($value)) {
        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        $out = [];
        foreach ($value as $k => $child) {
            $out[$k] = canon($child);
        }
        if ($isList) {
            usort($out, static function ($a, $b) { return strcmp(json_encode($a), json_encode($b)); });
        } else {
            ksort($out, SORT_STRING);
        }

        return $out;
    }
    if ($value === null) {
        return '';
    }
    if (is_int($value)) {
        return $value;
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    return (string) $value;
}

function canonJson($value): string
{
    return json_encode(canon($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function document(string $schema, array $content): array
{
    $json = canonJson(['schema_version' => $schema, 'content' => $content]);

    return ['preimage' => $json, 'hash' => hash('sha256', $json)];
}

function setDocument(string $schema, array $rows): array
{
    return document($schema, ['rows' => array_values($rows)]);
}

/** Replay composite identity digest: sorted keys, JSON with unescaped slashes/unicode and preserved zero fractions. */
function composite(array $members): string
{
    ksort($members, SORT_STRING);

    return hash('sha256', json_encode($members, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
}

/** Column types of the V2 artifacts: D date, T timestamp, H content hash, P4/P2/P10 fixed decimals, I integer, B 0/1 flag, J canonical JSON, S text. */
function token($type, $value): string
{
    if ($value === null) {
        return '';
    }
    switch ($type) {
        case 'P4': return fixed($value, 4);
        case 'P2': return fixed($value, 2);
        case 'P10': return fixed($value, 10);
        case 'I': return (string) (int) $value;
        case 'B': return in_array($value, [1, '1', true], true) ? '1' : '0';
        case 'J': return canonJson($value);
        default:
            $text = (string) $value;
            if (strpos($text, '|') !== false || preg_match('/[\r\n]/', $text)) {
                throw new RuntimeException('delimiter in text');
            }
            if ($type === 'D' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
                throw new RuntimeException('date '.$text);
            }
            if ($type === 'T' && ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $text)) {
                throw new RuntimeException('timestamp '.$text);
            }
            if ($type === 'H' && ! preg_match('/^[a-f0-9]{64}$/', $text)) {
                throw new RuntimeException('hash '.$text);
            }

            return $text;
    }
}

/** V2 framing: "profile|domain" then LF then the rows, ordered by trade_date, listing root, instrument root; no trailing LF. */
function domainHash(string $domain, array $columns, array $rows): array
{
    $lines = [];
    foreach ($rows as $row) {
        $tokens = [];
        foreach ($columns as $name => $type) {
            $tokens[$name] = token($type, $row[$name] ?? null);
        }
        $lines[] = ['key' => $tokens['trade_date'].'|'.$tokens['listing_root'].'|'.$tokens['instrument_root'], 'line' => implode('|', $tokens), 'tokens' => $tokens];
    }
    usort($lines, static function ($a, $b) { return strcmp($a['key'], $b['key']) ?: strcmp($a['line'], $b['line']); });
    $body = implode("\n", array_column($lines, 'line'));
    $preimage = PROFILE.'|'.$domain.($body === '' ? '' : "\n".$body);

    return ['preimage' => $preimage, 'hash' => hash('sha256', $preimage), 'row_tokens' => array_column($lines, 'tokens')];
}

// ---------------------------------------------------------------------------------------------------------------------
// frozen facts
// ---------------------------------------------------------------------------------------------------------------------

$date = $world['trade_date'];
$clock = $world['run_clock'];
$listing = $world['listing'];
$roots = ['issuer_root' => $world['retained_roots']['issuer'], 'instrument_root' => $world['retained_roots']['instrument'], 'listing_root' => $world['retained_roots']['listing']];
$resolved = $config['resolved_config'];
$configHash = hash('sha256', $configBytes);
$responseHash = hash('sha256', $response);
$bar = $world['bar'];

// ---------------------------------------------------------------------------------------------------------------------
// 1. source observation manifest (nested semantic member: observation_manifest_hash)
// ---------------------------------------------------------------------------------------------------------------------

/** Structural fingerprint of a provider response: every JSON path with its PHP type, sorted, one per line. */
function shape($value, string $path, array &$out): void
{
    if (! is_array($value)) {
        $out[] = $path.':'.gettype($value);

        return;
    }
    $out[] = $path.':array';
    foreach ($value as $key => $child) {
        shape($child, $path.(is_int($key) ? '[]' : '.'.$key), $out);
    }
}
$shape = [];
shape(json_decode($response, true), '$', $shape);
sort($shape, SORT_STRING);
$schemaFingerprint = hash('sha256', implode("\n", $shape));

$entry = static function (string $outcome, string $validation, ?string $parentEntryHash) use ($responseHash, $listing, $date, $clock, $resolved) {
    return [
        'payload_hash' => $responseHash, 'payload_ref' => 'sha256:'.$responseHash, 'provider' => $listing['provider_namespace'], 'provider_symbol' => $listing['provider_symbol'],
        'mapping_revision' => 'temporal_provider_mapping_v1', // vocabulary: the provider-mapping revision label of the legacy projection
        'requested_trade_date' => $date, 'requested_start_date' => null, 'requested_end_date' => null, 'source_timestamp' => null, 'acquired_at' => $clock,
        'adapter_version' => $resolved['source']['api']['adapter_version'], 'schema_fingerprint' => $GLOBALS['schemaFingerprint'],
        'provider_schema_version' => $resolved['source']['api']['schema_version'], 'outcome_state' => $outcome, 'validation_state' => $validation, 'reason_code' => null,
        'parent_entry_hash' => $parentEntryHash, 'supersedes_entry_hash' => null,
    ];
};
$captureEntry = $entry('CAPTURED', 'PENDING', null);
$captureEntryHash = hash('sha256', canonJson(['schema_version' => 'market-data-observation-entry/v2', 'entry' => $captureEntry]));
$acceptedEntry = $entry('ACCEPTED', 'PASSED', $captureEntryHash);
$observationManifest = ['preimage' => canonJson(['schema_version' => 'market-data-observation-manifest/v2', 'entries' => [$acceptedEntry]])];
$observationManifest['hash'] = hash('sha256', $observationManifest['preimage']);

// ---------------------------------------------------------------------------------------------------------------------
// 2. nested semantic set documents (one listing, no events, no factors, no sector, no status revision)
// ---------------------------------------------------------------------------------------------------------------------

$boardRecordedAt = $listing['listed_date'].' 00:00:00'; // the listing master record time of the frozen world (ticker created_at)
$identitySet = setDocument('identity-board-resolution-set/v2', [[
    'listing' => $roots, 'normalized_board_code' => null, 'board_identity_recorded_at' => $boardRecordedAt, 'resolution_state' => 'FAIL_CLOSED_BOARD_UNKNOWN',
]]);
$marketStructureSet = setDocument('market-structure-resolution-set/v2', [[
    'listing' => $roots, 'resolution_state' => 'FAIL_CLOSED_BOARD_UNKNOWN', 'normalized_board_code' => null, 'board_identity_recorded_at' => $boardRecordedAt,
    'reason_code' => 'MARKET_STRUCTURE_BOARD_UNKNOWN', 'price_band' => null, 'minimum_price' => null, 'tick_size' => null,
]]);
$statusSet = setDocument('status-resolution-set/v2', [[
    'listing' => $roots, 'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN', 'temporal_status_state' => 'UNKNOWN', 'status_revision_hash' => null, 'status_source_payload_hash' => null,
]]);
$cal = $world['calendar'];
$calendarSet = setDocument('calendar-revision-set/v2', [[
    'revision_uid' => hash('sha256', 'r25-calendar|'.$date), 'market_code' => $cal['market_code'], 'market_segment' => $cal['market_segment'], 'cal_date' => $date,
    'timezone' => $cal['timezone'], 'session_state' => 'COMPLETED', 'is_trading_day' => 1, 'is_half_day' => 0,
    'session_open_at' => $date.' '.$cal['session_open'], 'session_close_at' => $date.' '.$cal['session_close'], 'completed_at' => $date.' '.$cal['session_close'],
    'source_ref' => str_replace('{date}', $date, $cal['source_ref_template']), 'source_version' => $cal['source_version'], 'provenance_tier' => $cal['provenance_tier'],
    'source_payload_hash' => null, 'recorded_at' => $date.' '.$cal['recorded_at_time'], 'supersedes_revision_uid' => null,
]]);
$eventSet = setDocument('event-revision-set/v2', []);
$sourceScaleSet = setDocument('source-scale-assessment-set/v2', []);
$factorDecisionSet = setDocument('factor-decision-set/v2', []);
$factorSet = document('market-data-adjustment-factor-set/v2', [
    'price_product_code' => 'STRUCTURAL_ADJUSTED', 'factor_formula_version' => 'structural_factor_product_v2', 'state' => 'BOUND', 'config_content_hash' => $configHash,
    'window_start' => $resolved['scope']['dataset_start'], 'window_end' => $date, 'decisions' => [], 'factors' => [],
]);
$members = [
    'observation_manifest_hash' => $observationManifest['hash'], 'identity_revision_set_hash' => $identitySet['hash'], 'calendar_revision_set_hash' => $calendarSet['hash'],
    'status_revision_set_hash' => $statusSet['hash'], 'event_revision_set_hash' => $eventSet['hash'], 'source_scale_assessment_set_hash' => $sourceScaleSet['hash'],
    'market_structure_revision_set_hash' => $marketStructureSet['hash'], 'factor_decision_set_hash' => $factorDecisionSet['hash'], 'factor_set_hash' => $factorSet['hash'],
];

// ---------------------------------------------------------------------------------------------------------------------
// 3. the three V2 artifact hashes
// ---------------------------------------------------------------------------------------------------------------------

$barsColumns = [
    'trade_date' => 'D', 'issuer_root' => 'S', 'instrument_root' => 'S', 'listing_root' => 'S', 'provider_namespace' => 'S', 'provider_symbol' => 'S', 'observation_manifest_hash' => 'H',
    'source_timestamp' => 'T', 'acquired_at' => 'T', 'open' => 'P4', 'high' => 'P4', 'low' => 'P4', 'close' => 'P4', 'volume' => 'I', 'previous_close' => 'P4',
    'traded_value_idr_actual' => 'P2', 'trade_count' => 'I', 'board_code' => 'S', 'session_code' => 'S', 'canonicalization_version' => 'S', 'price_product_code' => 'S',
    'quality_state' => 'S', 'quality_reasons_json' => 'J', 'config_content_hash' => 'H', 'source_scale_state' => 'S', 'source_scale_assessment_set_hash' => 'H',
];
$barRow = $roots + [
    'trade_date' => $date, 'provider_namespace' => $listing['provider_namespace'], 'provider_symbol' => $listing['provider_symbol'], 'observation_manifest_hash' => $members['observation_manifest_hash'],
    'source_timestamp' => null, 'acquired_at' => $clock, 'open' => $bar['open'], 'high' => $bar['high'], 'low' => $bar['low'], 'close' => $bar['close'], 'volume' => $bar['volume'],
    'previous_close' => null, 'traded_value_idr_actual' => null, 'trade_count' => null, 'board_code' => null, 'session_code' => 'REGULAR', 'canonicalization_version' => 'idx_regular_raw_v2',
    'price_product_code' => 'RAW', 'quality_state' => 'VALIDATED', 'quality_reasons_json' => [], 'config_content_hash' => $configHash, 'source_scale_state' => 'UNKNOWN',
    'source_scale_assessment_set_hash' => $members['source_scale_assessment_set_hash'],
];
$bars = domainHash('bars', $barsColumns, [$barRow]);

// Insufficient history: one frozen bar cannot satisfy any window of 5, 10, 20 or 50 sessions (EOD_Indicators_Formula_Spec.md windows).
$insufficient = ['adv20_close_volume_proxy_idr', 'atr14', 'atr14_pct', 'close_to_hh20_pct', 'close_to_ll20_pct', 'close_vs_ma20_pct', 'close_vs_ma50_pct', 'dv20_idr', 'hh20', 'll20', 'ma20',
    'ma20_slope_pct', 'ma50', 'range_20_pct', 'range_position_20_pct', 'roc10', 'roc20', 'roc5', 'rs_20_vs_ihsg', 'rs_20_vs_sector', 'vol_ratio'];
$nullReasons = [];
foreach ($insufficient as $field) {
    $nullReasons[$field] = ['IND_INSUFFICIENT_HISTORY'];
}
$indColumns = [
    'trade_date' => 'D', 'issuer_root' => 'S', 'instrument_root' => 'S', 'listing_root' => 'S', 'observation_manifest_hash' => 'H', 'identity_revision_set_hash' => 'H',
    'status_revision_set_hash' => 'H', 'event_revision_set_hash' => 'H', 'factor_decision_set_hash' => 'H', 'is_valid' => 'B', 'invalid_reason_code' => 'S', 'indicator_set_version' => 'S',
    'sector_code' => 'S', 'sector_membership_revision_hash' => 'H', 'dv20_idr' => 'P2', 'atr14_pct' => 'P10', 'vol_ratio' => 'P10', 'roc5' => 'P10', 'roc10' => 'P10', 'roc20' => 'P10',
    'hh20' => 'P4', 'll20' => 'P4', 'ma20' => 'P4', 'ma50' => 'P4', 'close_to_hh20_pct' => 'P10', 'close_to_ll20_pct' => 'P10', 'range_20_pct' => 'P10', 'range_position_20_pct' => 'P10',
    'close_vs_ma20_pct' => 'P10', 'close_vs_ma50_pct' => 'P10', 'ma20_slope_pct' => 'P10', 'rs_20_vs_ihsg' => 'P10', 'sector_roc20' => 'P10', 'rs_20_vs_sector' => 'P10',
    'sector_rs_20_vs_ihsg' => 'P10', 'corporate_action_flag' => 'B', 'corporate_action_types' => 'S', 'trading_status_code' => 'S', 'is_suspended' => 'B', 'is_uma' => 'B',
    'event_risk_flag' => 'B', 'event_risk_reasons' => 'J', 'corporate_action_window_reasons' => 'J', 'formula_version' => 'S', 'config_content_hash' => 'H', 'factor_set_hash' => 'H',
    'price_product_code' => 'S', 'price_product_version' => 'S', 'liquidity_formula_version' => 'S', 'adv20_traded_value_idr_actual' => 'P2', 'adv20_close_volume_proxy_idr' => 'P2',
    'atr14' => 'P10', 'atr_state_ref' => 'S', 'null_reasons_json' => 'J',
];
$indRow = $roots + [
    'trade_date' => $date, 'observation_manifest_hash' => $members['observation_manifest_hash'], 'identity_revision_set_hash' => $members['identity_revision_set_hash'],
    'status_revision_set_hash' => $members['status_revision_set_hash'], 'event_revision_set_hash' => $members['event_revision_set_hash'], 'factor_decision_set_hash' => $members['factor_decision_set_hash'],
    'is_valid' => 0, 'invalid_reason_code' => 'IND_INSUFFICIENT_HISTORY', 'indicator_set_version' => 'v1', 'formula_version' => 'v1', 'config_content_hash' => $configHash,
    'factor_set_hash' => $members['factor_set_hash'], 'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'structural_adjusted_v2',
    'liquidity_formula_version' => 'liquidity_metric_v1', 'null_reasons_json' => $nullReasons,
];
$indicators = domainHash('indicators', $indColumns, [$indRow]);

$eliColumns = [
    'trade_date' => 'D', 'issuer_root' => 'S', 'instrument_root' => 'S', 'listing_root' => 'S', 'identity_revision_set_hash' => 'H', 'status_revision_set_hash' => 'H',
    'event_revision_set_hash' => 'H', 'market_structure_revision_set_hash' => 'H', 'eligible' => 'B', 'reason_code' => 'S', 'universe_membership_state' => 'S', 'bar_expectation_state' => 'S',
    'delivery_state' => 'S', 'canonical_quality_state' => 'S', 'liquidity_state' => 'S', 'temporal_status_state' => 'S', 'event_risk_state' => 'S', 'source_provenance_state' => 'S',
    'price_basis_state' => 'S', 'contamination_state' => 'S', 'indicator_state' => 'S', 'freshness_state' => 'S', 'eligibility_reasons_json' => 'J', 'config_content_hash' => 'H',
    'read_model_version' => 'S', 'market_structure_resolution_state' => 'S',
];
$eliRow = $roots + [
    'trade_date' => $date, 'identity_revision_set_hash' => $members['identity_revision_set_hash'], 'status_revision_set_hash' => $members['status_revision_set_hash'],
    'event_revision_set_hash' => $members['event_revision_set_hash'], 'market_structure_revision_set_hash' => $members['market_structure_revision_set_hash'], 'eligible' => 0,
    'reason_code' => 'ELIG_INSUFFICIENT_HISTORY', 'universe_membership_state' => 'MEMBER', 'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN', 'delivery_state' => 'DELIVERED',
    'canonical_quality_state' => 'VALIDATED', 'liquidity_state' => 'ACTIVE', 'temporal_status_state' => 'UNKNOWN', 'event_risk_state' => 'UNKNOWN', 'source_provenance_state' => 'SOURCE_TRACEABLE',
    'price_basis_state' => 'STRUCTURAL_ADJUSTED', 'contamination_state' => 'NO_CONTAMINATION_DETECTED', 'indicator_state' => 'INVALID', 'freshness_state' => 'NOT_AVAILABLE',
    'eligibility_reasons_json' => ['ELIG_INSUFFICIENT_HISTORY'], 'config_content_hash' => $configHash, 'read_model_version' => 'market_data_read_product_v1',
    'market_structure_resolution_state' => 'FAIL_CLOSED_BOARD_UNKNOWN',
];
$eligibility = domainHash('eligibility', $eliColumns, [$eliRow]);

// ---------------------------------------------------------------------------------------------------------------------
// 4. replay frozen identities (V2 composites defined by the replay verifier's V2 rule, E-MD-B18-A002-092)
// ---------------------------------------------------------------------------------------------------------------------

$temporalIdentity = composite(['component_key' => 'universe_identity', 'profile' => PROFILE, 'identity_revision_set_hash' => $members['identity_revision_set_hash']]);
$calendarStatus = composite(['profile' => PROFILE, 'calendar_revision_set_hash' => $members['calendar_revision_set_hash'], 'status_revision_set_hash' => $members['status_revision_set_hash']]);

// ---------------------------------------------------------------------------------------------------------------------
// 5. the literal expected replay result
// ---------------------------------------------------------------------------------------------------------------------

$T = static function (string $kind) { return '@TARGET:'.$kind; };
$sourceName = 'API_FREE'; // vocabulary: the run's source label for the free API source
$expected = [
    'comparison_result' => 'MATCH',
    'comparison_note' => 'Independently authored expectation of the synthetic V2 world (candidate; not a runtime-generated fixture).',
    'expected_config_identity' => $configHash,
    'expected_replay_resolution_context' => [
        'replay_actual_resolution_mode' => 'CURRENT_READABLE_PUBLICATION_AUDIT', 'replay_publication_scope' => 'CURRENT_POINTER_PUBLICATION',
        'replay_selector_type' => 'current_readable_replay_actual_state', 'replay_selector_id' => $T('publication_id'), 'historical_publication_allowed' => false,
        'current_pointer_required' => true, 'current_pointer_status' => 'RESOLVED_READABLE_CURRENT', 'publication_id' => $T('publication_id'), 'publication_version' => 1,
        'publication_run_id' => $T('run_id'), 'run_id' => $T('run_id'), 'run_publication_mirror_status' => 'MIRROR_VALID', 'seal_state' => 'SEALED', 'is_current_publication' => true,
        'artifact_scope' => $T('artifact_scope'), 'coverage_basis_publication_id' => $T('publication_id'), 'coverage_basis_run_id' => $T('run_id'),
        'lineage_verification_status' => 'LINEAGE_VERIFIED', 'replay_reason_code' => 'REPLAY_CURRENT_PUBLICATION_RESOLVED',
    ],
    'expected_run_context' => [
        'trade_date_requested' => $date, 'trade_date_effective' => $date, 'request_mode' => 'full_publish', 'import_status' => 'COMPLETED', 'promote_status' => 'PROMOTED',
        'promoted' => true, 'pointer_switched' => true, 'promote_mode' => null, 'publish_target' => null, 'terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE',
        'final_reason_code' => 'COVERAGE_THRESHOLD_MET',
    ],
    'expected_source_context' => [
        'source_mode' => 'api', 'source_name' => $sourceName, 'source_identity' => 'mode=api|name='.$sourceName.'|provider='.$listing['provider_namespace'],
        'source_provider' => $listing['provider_namespace'], 'provider' => $listing['provider_namespace'], 'source_timeout_seconds' => $resolved['source']['api']['timeout_seconds'],
        'source_retry_max' => $resolved['provider']['api_retry_max'], 'source_attempt_count' => 1, 'source_success_after_retry' => false, 'source_retry_exhausted' => false,
        'source_final_http_status' => 200, 'source_final_reason_code' => null, 'source_input_file' => null, 'source_file_hash' => null, 'source_file_hash_algorithm' => null,
        'source_file_size_bytes' => null, 'source_file_row_count' => null, 'accepted_row_count' => 1, 'rejected_row_count' => 0, 'invalid_row_count' => 0,
    ],
    'expected_coverage_context' => [
        'coverage_universe_count' => 1, 'coverage_expected_count' => 1, 'coverage_available_count' => 1, 'coverage_missing_count' => 0, 'expected_bar_count' => 1,
        'available_bar_count' => 1, 'missing_bar_count' => 0, 'coverage_ratio' => 1, 'coverage_min_threshold' => $resolved['coverage_gate']['min_ratio'], 'coverage_gate_state' => 'PASS',
        'legacy_coverage_gate_state_raw' => null, 'coverage_reason_code' => 'COVERAGE_THRESHOLD_MET', 'coverage_threshold_mode' => $resolved['coverage_gate']['threshold_mode'],
        'coverage_universe_basis' => $resolved['coverage_gate']['universe_basis'], 'coverage_contract_version' => $resolved['coverage_gate']['contract_version'],
        'coverage_missing_sample' => [], 'coverage_basis' => 'CandidatePublication', 'coverage_basis_publication_id' => $T('publication_id'),
        'coverage_basis_artifact_scope' => 'candidate_publication_artifact', 'candidate_publication_id' => $T('publication_id'), 'baseline_publication_id' => null,
    ],
    'expected_artifact_context' => [
        'bars_rows_written' => 1, 'indicators_rows_written' => 1, 'eligibility_rows_written' => 1, 'eligible_count' => 0, 'invalid_bar_count' => 0, 'invalid_indicator_count' => 1,
        'warning_count' => null, 'hard_reject_count' => 1, 'bars_batch_hash' => $bars['hash'], 'indicators_batch_hash' => $indicators['hash'], 'eligibility_batch_hash' => $eligibility['hash'],
        'artifact_scope' => $T('artifact_scope'),
    ],
    'expected_seal_context' => ['seal_state' => 'SEALED'],
    'expected_publication_context' => [
        'publication_id' => $T('publication_id'), 'publication_run_id' => $T('run_id'), 'publication_version' => 1, 'publication_terminal_status' => 'SUCCESS',
        'publication_publishability_state' => 'READABLE', 'publication_is_current' => true, 'publication_seal_state' => 'SEALED', 'price_product_code' => 'STRUCTURAL_ADJUSTED',
        'price_product_version' => 'structural_adjusted_v2', 'factor_set_id' => $T('factor_set_id'), 'factor_set_hash' => $members['factor_set_hash'],
    ],
    'expected_pointer_context' => [
        'pointer_publication_id' => $T('publication_id'), 'pointer_run_id' => $T('run_id'), 'pointer_publication_version' => 1, 'pointer_resolve_status' => 'RESOLVED_READABLE_CURRENT',
        'pointer_switched' => true, 'current_pointer_required' => true, 'historical_publication_allowed' => false,
    ],
    'expected_fallback_context' => ['fallback_used' => false, 'fallback_publication_id' => null, 'fallback_run_id' => null],
    'expected_correction_context' => [
        'correction_id' => null, 'correction_status' => null, 'correction_outcome' => null, 'correction_reseal_status' => null, 'correction_publication_switch' => null,
        'baseline_publication_id' => null, 'candidate_publication_id' => null,
    ],
    'expected_final_state' => ['terminal_status' => 'SUCCESS', 'publishability_state' => 'READABLE', 'final_reason_code' => 'COVERAGE_THRESHOLD_MET'],
    'expected_reason_code' => 'COVERAGE_THRESHOLD_MET',
    'expected_lineage' => [
        'run_id' => $T('run_id'), 'publication_id' => $T('publication_id'), 'current_publication_id' => $T('publication_id'), 'publication_run_id' => $T('run_id'), 'correction_id' => null,
        'source_file_hash' => null, 'bars_batch_hash' => $bars['hash'], 'indicators_batch_hash' => $indicators['hash'], 'eligibility_batch_hash' => $eligibility['hash'],
        'final_reason_code' => 'COVERAGE_THRESHOLD_MET', 'price_product_code' => 'STRUCTURAL_ADJUSTED', 'price_product_version' => 'structural_adjusted_v2',
        'factor_set_id' => $T('factor_set_id'), 'factor_set_hash' => $members['factor_set_hash'],
    ],
    // The frozen inputs of the publication. event_factor_hash, formula_registry_hash, reason_registry_hash and executable_build_identity are
    // deliberately NOT asserted (see manifest.not_asserted_bound_inputs): the first still binds ancillary captures that carry local ids
    // (F-MD-B10-A002-004 residual), the other three bind the executable build, which is not a semantic fact of this world.
    'expected_bound_input_context' => [
        'source_observation_manifest_hash' => $members['observation_manifest_hash'], 'canonical_raw_input_hash' => $bars['hash'], 'temporal_identity_hash' => $temporalIdentity,
        'calendar_status_hash' => $calendarStatus, 'config_snapshot_hash' => $configHash, 'read_model_version' => 'market_data_read_product_v1',
        'serialization_version' => $resolved['governance']['config_serialization_version'],
    ],
];
$reasonCounts = [['reason_code' => 'DATASET_HASH_CREATED', 'reason_count' => 1], ['reason_code' => 'ELIG_INSUFFICIENT_HISTORY', 'reason_count' => 1]];

file_put_contents($pkg.'/expected/expected_replay_result.json', json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents($pkg.'/expected/expected_reason_code_counts.json', json_encode($reasonCounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

$output = [
    'oracle' => ['id' => 'r0025-synthetic-v2-reference-oracle', 'version' => '1.0.0', 'inputs' => [
        'synthetic_world.json' => hash_file('sha256', $pkg.'/inputs/synthetic_world.json'),
        'provider_response.json' => $responseHash,
        'frozen_config_content.txt' => $configHash,
    ]],
    'frozen_input_facts' => ['provider_response_sha256' => $responseHash, 'provider_response_schema_fingerprint' => $schemaFingerprint, 'config_content_sha256' => $configHash, 'retained_roots' => $roots],
    'observation_entries' => ['capture' => $captureEntry, 'capture_entry_hash' => $captureEntryHash, 'accepted' => $acceptedEntry],
    'documents' => [
        'observation_manifest' => $observationManifest, 'identity_revision_set' => $identitySet, 'calendar_revision_set' => $calendarSet, 'status_revision_set' => $statusSet,
        'event_revision_set' => $eventSet, 'source_scale_assessment_set' => $sourceScaleSet, 'market_structure_revision_set' => $marketStructureSet,
        'factor_decision_set' => $factorDecisionSet, 'factor_set' => $factorSet,
    ],
    'nested_members' => $members,
    'artifacts' => [
        'bars' => ['columns' => $barsColumns, 'row_tokens' => $bars['row_tokens'], 'preimage' => $bars['preimage'], 'sha256' => $bars['hash']],
        'indicators' => ['columns' => $indColumns, 'row_tokens' => $indicators['row_tokens'], 'preimage' => $indicators['preimage'], 'sha256' => $indicators['hash']],
        'eligibility' => ['columns' => $eliColumns, 'row_tokens' => $eligibility['row_tokens'], 'preimage' => $eligibility['preimage'], 'sha256' => $eligibility['hash']],
    ],
    'replay_composites' => ['temporal_identity_hash' => $temporalIdentity, 'calendar_status_hash' => $calendarStatus],
];
file_put_contents(__DIR__.'/oracle_output.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo "bars_batch_hash        ", $bars['hash'], "\nindicators_batch_hash  ", $indicators['hash'], "\neligibility_batch_hash ", $eligibility['hash'], "\n";
foreach ($members as $k => $v) {
    echo str_pad($k, 36), $v, "\n";
}
echo 'config_content_sha256  ', $configHash, "\n";
