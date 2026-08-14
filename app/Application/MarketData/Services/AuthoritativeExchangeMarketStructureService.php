<?php

namespace App\Application\MarketData\Services;

use App\Application\MarketData\Ports\ExchangeMarketStructureEvidenceVerifier;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthoritativeExchangeMarketStructureService
{
    const MANIFEST_SCHEMA = 'market-data-authoritative-exchange-market-structure/v1';
    const SOURCE_ADAPTER_VERSION = 'authoritative-exchange-market-structure-v1';
    const INSTRUMENT_SCOPE = 'IDX_REGULAR_STANDARD_EQUITY';

    private $sourceObservations;
    private $evidenceVerifier;

    public function __construct(
        SourceObservationRepository $sourceObservations,
        ExchangeMarketStructureEvidenceVerifier $evidenceVerifier
    ) {
        $this->sourceObservations = $sourceObservations;
        $this->evidenceVerifier = $evidenceVerifier;
    }

    public function process($manifestPath, $apply = false)
    {
        $manifest = $this->readManifest($manifestPath);
        $prepared = $this->validateManifest($manifest);

        if (! $apply) {
            return $this->summarize($prepared, false);
        }

        $sourcesToVerify = [];
        foreach ($prepared['revisions'] as $revision) {
            if ($revision['operation'] === 'INSERT' && $revision['source_observation_id'] === null) {
                $sourcesToVerify[$revision['source']['source_uid']] = $revision['source'];
            }
        }
        $verifiedSources = [];
        foreach ($sourcesToVerify as $sourceUid => $source) {
            $verifiedSources[$sourceUid] = $this->validateVerificationResult(
                $source,
                $this->evidenceVerifier->verify($source)
            );
        }

        return DB::transaction(function () use ($prepared, $verifiedSources) {
            $inserted = 0;
            $unchanged = 0;
            $evidenceCorrections = 0;
            $bandTierCount = 0;
            $tickTierCount = 0;
            $sourceObservationsInserted = 0;
            $sourceObservationIds = [];
            $recordedAt = Carbon::now(config('market_data.platform.timezone', 'Asia/Jakarta'))->toDateTimeString();

            foreach ($prepared['revisions'] as $revision) {
                $existing = $this->findRevision($revision, true);
                if ($existing) {
                    $this->assertExistingRevisionMatches($existing, $revision);
                    $unchanged++;
                    continue;
                }

                $sourceUid = $revision['source']['source_uid'];
                if (! isset($sourceObservationIds[$sourceUid])) {
                    $sourceObservationIds[$sourceUid] = $this->findAcceptedSourceObservationId(
                        $revision['source'],
                        true
                    );
                }
                if ($sourceObservationIds[$sourceUid] === null) {
                    if (! isset($verifiedSources[$sourceUid])) {
                        throw new \RuntimeException(
                            'STAGE_7_VERIFIED_RESPONSE_REQUIRED: a new authority observation cannot be persisted without the verified response identity.'
                        );
                    }
                    $verified = $verifiedSources[$sourceUid];
                    $capture = $this->sourceObservations->capture([
                        'requested_trade_date' => $revision['source']['document_date'],
                        'source_mode' => 'authority_document',
                        'source_name' => $revision['source']['authority_name'],
                        'provider' => $revision['source']['authority_name'],
                        'sanitized_request_identity' => $revision['source']['document_url'],
                        'response_status' => $verified['http_status'],
                        'content_type' => $verified['content_type'],
                        'source_timestamp' => null,
                        'acquired_at' => $recordedAt,
                        'provider_schema_version' => self::MANIFEST_SCHEMA,
                        'schema_fingerprint' => $verified['schema_fingerprint'],
                        'adapter_version' => self::SOURCE_ADAPTER_VERSION,
                        'payload_hash' => $verified['document_sha256'],
                        'payload_ref' => $verified['payload_ref'],
                        'payload_byte_length' => $verified['document_byte_length'],
                        'bounded_payload_body' => $verified['bounded_payload_body'],
                        'supersedes_observation_id' => $revision['superseded_source_observation_id'],
                    ]);
                    $accepted = $this->sourceObservations->recordOutcome(
                        $capture,
                        'ACCEPTED',
                        'AUTHORITATIVE_MARKET_STRUCTURE_VALIDATED',
                        ['acquired_at' => $recordedAt]
                    );
                    $sourceObservationIds[$sourceUid] = (int) $accepted['source_observation_id'];
                    $sourceObservationsInserted += 2;
                }

                $row = $revision['row'];
                $row['source_observation_id'] = $sourceObservationIds[$sourceUid];
                $row['recorded_at'] = $recordedAt;
                $revisionId = (int) DB::table('md_exchange_market_structure_revisions')->insertGetId($row);

                foreach ($revision['band_tiers'] as $tier) {
                    DB::table('md_exchange_price_band_tiers')->insert(array_merge($tier, [
                        'market_structure_revision_id' => $revisionId,
                    ]));
                    $bandTierCount++;
                }
                foreach ($revision['tick_tiers'] as $tier) {
                    DB::table('md_exchange_tick_size_tiers')->insert(array_merge($tier, [
                        'market_structure_revision_id' => $revisionId,
                    ]));
                    $tickTierCount++;
                }

                $inserted++;
                if ($revision['is_evidence_correction']) {
                    $evidenceCorrections++;
                }
            }

            return [
                'scope_id' => $prepared['scope_id'],
                'scope_entry_count' => count($prepared['revisions']),
                'inserted_revision_count' => $inserted,
                'unchanged_revision_count' => $unchanged,
                'evidence_correction_revision_count' => $evidenceCorrections,
                'inserted_price_band_tier_count' => $bandTierCount,
                'inserted_tick_size_tier_count' => $tickTierCount,
                'source_observation_insert_count' => $sourceObservationsInserted,
                'applied' => true,
            ];
        });
    }

    private function readManifest($manifestPath)
    {
        if (! is_string($manifestPath) || $manifestPath === '' || ! is_file($manifestPath)) {
            throw new \RuntimeException('STAGE_7_MANIFEST_NOT_FOUND: manifest must point to an existing JSON file.');
        }
        $contents = file_get_contents($manifestPath);
        if ($contents === false) {
            throw new \RuntimeException('STAGE_7_MANIFEST_UNREADABLE: manifest could not be read.');
        }
        $decoded = json_decode($contents, true);
        if (! is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('STAGE_7_MANIFEST_INVALID_JSON: '.json_last_error_msg());
        }

        return $decoded;
    }

    private function validateManifest(array $manifest)
    {
        $this->assertExactKeys($manifest, [
            'schema_version', 'scope_id', 'scope_statement', 'record_only',
            'dataset_coverage_start', 'source_count', 'revision_count', 'sources', 'revisions',
        ], 'manifest');

        if ($manifest['schema_version'] !== self::MANIFEST_SCHEMA) {
            throw new \RuntimeException('STAGE_7_MANIFEST_SCHEMA_UNSUPPORTED: schema_version must be '.self::MANIFEST_SCHEMA.'.');
        }
        if (! is_string($manifest['scope_id']) || ! preg_match('/^[a-z0-9][a-z0-9._-]{2,63}$/', $manifest['scope_id'])) {
            throw new \RuntimeException('STAGE_7_SCOPE_ID_INVALID: scope_id must be a stable lowercase identifier.');
        }
        if (! is_string($manifest['scope_statement']) || trim($manifest['scope_statement']) === '') {
            throw new \RuntimeException('STAGE_7_SCOPE_UNDECLARED: scope_statement is required.');
        }
        if ($manifest['record_only'] !== true) {
            throw new \RuntimeException('STAGE_7_SCOPE_ESCAPE_FORBIDDEN: record_only must be true; Stage 7 cannot apply tiers to any series.');
        }
        if ($manifest['dataset_coverage_start'] !== '2023-01-02') {
            throw new \RuntimeException('STAGE_7_DATASET_SCOPE_INVALID: dataset_coverage_start must match the locked 2023-01-02 boundary.');
        }
        if (! is_array($manifest['sources']) || ! is_int($manifest['source_count'])
            || $manifest['source_count'] !== count($manifest['sources']) || count($manifest['sources']) === 0) {
            throw new \RuntimeException('STAGE_7_SOURCE_COUNT_MISMATCH: source_count must equal a non-empty sources array.');
        }
        if (! is_array($manifest['revisions']) || ! is_int($manifest['revision_count'])
            || $manifest['revision_count'] !== count($manifest['revisions']) || count($manifest['revisions']) === 0) {
            throw new \RuntimeException('STAGE_7_REVISION_COUNT_MISMATCH: revision_count must equal a non-empty revisions array.');
        }

        $sources = [];
        foreach ($manifest['sources'] as $index => $source) {
            $validated = $this->validateSource($source, 'sources['.$index.']');
            if (isset($sources[$validated['source_uid']])) {
                throw new \RuntimeException('STAGE_7_DUPLICATE_SOURCE: source_uid must be unique.');
            }
            $sources[$validated['source_uid']] = $validated;
        }

        $revisions = [];
        $seen = [];
        foreach ($manifest['revisions'] as $index => $entry) {
            $revision = $this->validateRevision($entry, $sources, $manifest['dataset_coverage_start'], $index);
            $identity = $revision['row']['rule_uid'].'|'.$revision['row']['revision_number'];
            if (isset($seen[$identity])) {
                throw new \RuntimeException('STAGE_7_DUPLICATE_REVISION: rule_uid/revision_number must be unique.');
            }
            $seen[$identity] = true;

            $revision['is_evidence_correction'] = false;
            $revision['superseded_source_observation_id'] = null;
            $latest = $this->findLatestRevision($revision['row']['rule_uid'], false);
            if ($latest) {
                $storedRevision = $this->withRevisionVersion(
                    $revision,
                    (int) $latest->revision_number,
                    $latest->supersedes_revision_id === null ? null : (int) $latest->supersedes_revision_id
                );
                $this->assertStoredRevisionContentMatches($latest, $storedRevision);

                if ($this->evidenceObservationMatches(
                    (int) $latest->source_observation_id,
                    $revision['source']
                )) {
                    $revision = $storedRevision;
                    $revision['operation'] = 'UNCHANGED';
                    $revision['source_observation_id'] = (int) $latest->source_observation_id;
                } else {
                    $revision = $this->withRevisionVersion(
                        $revision,
                        (int) $latest->revision_number + 1,
                        (int) $latest->market_structure_revision_id
                    );
                    $revision['operation'] = 'INSERT';
                    $revision['is_evidence_correction'] = true;
                    $revision['superseded_source_observation_id'] = (int) $latest->source_observation_id;
                    $revision['source_observation_id'] = $this->findAcceptedSourceObservationId(
                        $revision['source'],
                        false
                    );
                }
            } else {
                $revision['operation'] = 'INSERT';
                $revision['source_observation_id'] = $this->findAcceptedSourceObservationId(
                    $revision['source'],
                    false
                );
            }
            $revisions[] = $revision;
        }

        $this->assertCompleteCoverage($revisions, $manifest['dataset_coverage_start']);

        return ['scope_id' => $manifest['scope_id'], 'revisions' => $revisions];
    }

    private function validateSource($source, $path)
    {
        if (! is_array($source)) {
            throw new \RuntimeException('STAGE_7_SOURCE_INVALID: '.$path.' must be an object.');
        }
        $this->assertExactKeys($source, [
            'source_uid', 'authority_name', 'authority_class', 'document_number', 'document_date',
            'document_url', 'document_sha256', 'document_byte_length', 'content_type', 'transport_role',
        ], $path);

        if (! in_array($source['authority_name'], ['IDX', 'OJK'], true)
            || ! in_array($source['authority_class'], ['EXCHANGE', 'REGULATOR'], true)) {
            throw new \RuntimeException('STAGE_7_SOURCE_NOT_AUTHORITATIVE: authority must be IDX/EXCHANGE or OJK/REGULATOR.');
        }
        if (($source['authority_name'] === 'IDX') !== ($source['authority_class'] === 'EXCHANGE')) {
            throw new \RuntimeException('STAGE_7_SOURCE_AUTHORITY_CONFLICT: authority name and class disagree.');
        }
        foreach (['document_number', 'document_url'] as $field) {
            if (! is_string($source[$field]) || trim($source[$field]) === '') {
                throw new \RuntimeException('STAGE_7_SOURCE_VALUE_MISSING: '.$path.'.'.$field.' is required.');
            }
        }
        if (! $this->isIsoDate($source['document_date'])) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_DATE_INVALID: document_date must use YYYY-MM-DD.');
        }
        if (! preg_match('/^[a-f0-9]{64}$/', (string) $source['document_sha256'])) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_HASH_INVALID: document_sha256 must be lowercase SHA-256.');
        }
        if (! is_int($source['document_byte_length']) || $source['document_byte_length'] <= 0) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_LENGTH_INVALID: document_byte_length must be a positive integer.');
        }
        if (! in_array($source['content_type'], ['application/pdf', 'application/json', 'text/html'], true)) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_TYPE_INVALID: unsupported evidence content type.');
        }
        if (! in_array($source['transport_role'], [
            'AUTHORITY_ORIGIN', 'AUTHORITY_DOCUMENT_MIRROR', 'AUTHORITY_DOCUMENT_MIRROR_REDIRECT',
        ], true)) {
            throw new \RuntimeException('STAGE_7_TRANSPORT_ROLE_INVALID: evidence transport role is not governed.');
        }

        $url = parse_url($source['document_url']);
        $host = strtolower((string) ($url['host'] ?? ''));
        if (! is_array($url) || strtolower((string) ($url['scheme'] ?? '')) !== 'https') {
            throw new \RuntimeException('STAGE_7_DOCUMENT_URL_INVALID: evidence URL must use HTTPS.');
        }
        $allowed = [
            'AUTHORITY_ORIGIN' => ['www.idx.id', 'www.ojk.go.id'],
            'AUTHORITY_DOCUMENT_MIRROR' => ['utrade.co.id'],
            'AUTHORITY_DOCUMENT_MIRROR_REDIRECT' => ['www.dropbox.com'],
        ];
        if (! in_array($host, $allowed[$source['transport_role']], true)) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_HOST_FORBIDDEN: host does not match the declared transport role.');
        }
        if ($source['transport_role'] === 'AUTHORITY_ORIGIN'
            && (($source['authority_name'] === 'IDX' && $host !== 'www.idx.id')
                || ($source['authority_name'] === 'OJK' && $host !== 'www.ojk.go.id'))) {
            throw new \RuntimeException('STAGE_7_SOURCE_AUTHORITY_CONFLICT: origin host does not match authority.');
        }

        $expectedSourceUid = hash('sha256', implode('|', [
            'market-structure-source', $source['authority_name'], $source['document_number'],
            $source['document_date'], $source['document_sha256'],
        ]));
        if (! is_string($source['source_uid']) || ! hash_equals($expectedSourceUid, $source['source_uid'])) {
            throw new \RuntimeException('STAGE_7_SOURCE_UID_INVALID: source_uid must be content-addressed from authority and document identity.');
        }

        return $source;
    }

    private function validateRevision(array $entry, array $sources, $datasetStart, $index)
    {
        $path = 'revisions['.$index.']';
        $this->assertExactKeys($entry, [
            'rule_uid', 'revision_number', 'supersedes_revision_number', 'rule_type', 'exchange_code',
            'market_segment', 'instrument_scope_code', 'effective_from', 'effective_to',
            'minimum_price_idr', 'verification_state', 'source_uid', 'source_reference',
            'coverage_scope', 'tiers',
        ], $path);

        if (! in_array($entry['rule_type'], ['PRICE_BAND', 'MINIMUM_PRICE', 'TICK_SIZE'], true)) {
            throw new \RuntimeException('STAGE_7_RULE_TYPE_INVALID: unsupported market-structure rule type.');
        }
        if ($entry['exchange_code'] !== 'IDX' || $entry['market_segment'] !== 'REGULAR'
            || $entry['instrument_scope_code'] !== self::INSTRUMENT_SCOPE) {
            throw new \RuntimeException('STAGE_7_SCOPE_ESCAPE_FORBIDDEN: rules are limited to standard IDX Regular-Market equity.');
        }
        if (! $this->isIsoDate($entry['effective_from'])
            || ($entry['effective_to'] !== null && ! $this->isIsoDate($entry['effective_to']))) {
            throw new \RuntimeException('STAGE_7_EFFECTIVE_DATE_INVALID: effective dates must use YYYY-MM-DD or NULL.');
        }
        if ($entry['effective_to'] !== null && $entry['effective_to'] < $entry['effective_from']) {
            throw new \RuntimeException('STAGE_7_EFFECTIVE_RANGE_INVALID: effective_to precedes effective_from.');
        }
        if ($entry['effective_to'] !== null && $entry['effective_to'] < $datasetStart) {
            throw new \RuntimeException('STAGE_7_OUTSIDE_DATASET_SCOPE: a rule ends before the locked dataset boundary.');
        }
        if (! is_int($entry['revision_number']) || $entry['revision_number'] !== 1
            || $entry['supersedes_revision_number'] !== null) {
            throw new \RuntimeException('STAGE_7_REVISION_LINEAGE_INVALID: initial Stage 7 authority records must be revision 1.');
        }
        if ($entry['verification_state'] !== 'AUTHORITATIVE_VERIFIED') {
            throw new \RuntimeException('STAGE_7_VERIFICATION_INVALID: authority scope requires AUTHORITATIVE_VERIFIED.');
        }
        if (! isset($sources[$entry['source_uid']])) {
            throw new \RuntimeException('STAGE_7_SOURCE_UNRESOLVED: revision source_uid is absent from sources.');
        }
        if (! is_string($entry['source_reference']) || $entry['source_reference'] !== $sources[$entry['source_uid']]['document_number']) {
            throw new \RuntimeException('STAGE_7_SOURCE_REFERENCE_CONFLICT: source_reference must equal the immutable document number.');
        }

        $coverage = $this->validateCoverage($entry['coverage_scope'], $datasetStart, $path.'.coverage_scope');
        $bandTiers = [];
        $tickTiers = [];
        if (! is_array($entry['tiers'])) {
            throw new \RuntimeException('STAGE_7_TIERS_INVALID: tiers must be an array.');
        }
        if ($entry['rule_type'] === 'PRICE_BAND') {
            if ($entry['minimum_price_idr'] !== null || count($entry['tiers']) !== 3) {
                throw new \RuntimeException('STAGE_7_PRICE_BAND_SHAPE_INVALID: each band revision requires exactly three tiers and no scalar floor.');
            }
            $bandTiers = $this->validateBandTiers($entry['tiers'], $path.'.tiers');
        } elseif ($entry['rule_type'] === 'TICK_SIZE') {
            if ($entry['minimum_price_idr'] !== null || count($entry['tiers']) !== 5) {
                throw new \RuntimeException('STAGE_7_TICK_SIZE_SHAPE_INVALID: tick ladder requires exactly five tiers and no scalar floor.');
            }
            $tickTiers = $this->validateTickTiers($entry['tiers'], $path.'.tiers');
        } else {
            if ($entry['minimum_price_idr'] !== 50 || count($entry['tiers']) !== 0) {
                throw new \RuntimeException('STAGE_7_MINIMUM_PRICE_SHAPE_INVALID: Regular-Market floor must be sourced as IDR 50 with no tiers.');
            }
        }

        $expectedRuleUid = hash('sha256', implode('|', [
            'market-structure', $entry['exchange_code'], $entry['market_segment'],
            $entry['instrument_scope_code'], $entry['rule_type'], $entry['effective_from'],
        ]));
        if (! is_string($entry['rule_uid']) || ! hash_equals($expectedRuleUid, $entry['rule_uid'])) {
            throw new \RuntimeException('STAGE_7_RULE_UID_INVALID: rule_uid must be content-addressed from scope, type, and effective date.');
        }

        $content = [
            'rule_uid' => $entry['rule_uid'],
            'revision_number' => $entry['revision_number'],
            'rule_type' => $entry['rule_type'],
            'exchange_code' => $entry['exchange_code'],
            'market_segment' => $entry['market_segment'],
            'instrument_scope_code' => $entry['instrument_scope_code'],
            'effective_from' => $entry['effective_from'],
            'effective_to' => $entry['effective_to'],
            'minimum_price_idr' => $entry['minimum_price_idr'],
            'verification_state' => $entry['verification_state'],
            'source' => $sources[$entry['source_uid']],
            'coverage_scope' => $coverage,
            'tiers' => $entry['tiers'],
        ];

        return [
            'source' => $sources[$entry['source_uid']],
            'content' => $content,
            'row' => [
                'rule_uid' => $entry['rule_uid'],
                'revision_number' => $entry['revision_number'],
                'rule_type' => $entry['rule_type'],
                'exchange_code' => $entry['exchange_code'],
                'market_segment' => $entry['market_segment'],
                'instrument_scope_code' => $entry['instrument_scope_code'],
                'coverage_scope_json' => $this->canonicalJson($coverage),
                'effective_from' => $entry['effective_from'],
                'effective_to' => $entry['effective_to'],
                'minimum_price_idr' => $entry['minimum_price_idr'],
                'verification_state' => $entry['verification_state'],
                'source_uid' => $entry['source_uid'],
                'source_observation_id' => null,
                'source_reference' => $entry['source_reference'],
                'content_hash' => hash('sha256', $this->canonicalJson($content)),
                'supersedes_revision_id' => null,
            ],
            'band_tiers' => $bandTiers,
            'tick_tiers' => $tickTiers,
        ];
    }

    private function validateCoverage($coverage, $datasetStart, $path)
    {
        if (! is_array($coverage)) {
            throw new \RuntimeException('STAGE_7_COVERAGE_SCOPE_INVALID: '.$path.' must be an object.');
        }
        $this->assertExactKeys($coverage, [
            'dataset_coverage_start', 'included_boards', 'excluded_boards',
            'unresolved_board_policy', 'use_boundary',
        ], $path);
        if ($coverage['dataset_coverage_start'] !== $datasetStart
            || $coverage['included_boards'] !== ['MAIN', 'DEVELOPMENT', 'NEW_ECONOMY']
            || $coverage['excluded_boards'] !== ['ACCELERATION', 'SPECIAL_MONITORING']) {
            throw new \RuntimeException('STAGE_7_COVERAGE_SCOPE_CONFLICT: board coverage must match the declared standard-equity scope.');
        }
        if ($coverage['unresolved_board_policy'] !== 'FAIL_CLOSED'
            || $coverage['use_boundary'] !== 'INTERPRETATION_ONLY_NO_SERIES_APPLICATION') {
            throw new \RuntimeException('STAGE_7_COVERAGE_POLICY_INVALID: unresolved boards and use boundary must remain fail-closed/record-only.');
        }

        return $coverage;
    }

    private function validateBandTiers(array $tiers, $path)
    {
        $expected = [
            [1, 50, true, 200, true, 35],
            [2, 200, false, 5000, true, 25],
            [3, 5000, false, null, false, 20],
        ];
        $rows = [];
        foreach ($tiers as $index => $tier) {
            $this->assertExactKeys($tier, [
                'tier_sequence', 'reference_price_min_idr', 'reference_price_min_inclusive',
                'reference_price_max_idr', 'reference_price_max_inclusive',
                'upper_limit_percent', 'lower_limit_percent',
            ], $path.'['.$index.']');
            if ([$tier['tier_sequence'], $tier['reference_price_min_idr'], $tier['reference_price_min_inclusive'],
                $tier['reference_price_max_idr'], $tier['reference_price_max_inclusive'], $tier['upper_limit_percent']]
                !== $expected[$index]) {
                throw new \RuntimeException('STAGE_7_PRICE_BAND_TIER_INVALID: price ranges and upper limits must match the sourced IDX standard tiers.');
            }
            if (! is_int($tier['lower_limit_percent'])
                || ! in_array($tier['lower_limit_percent'], [7, 15, 20, 25, 35], true)) {
                throw new \RuntimeException('STAGE_7_PRICE_BAND_LOWER_INVALID: lower limit must be an explicit sourced percentage.');
            }
            $rows[] = $tier;
        }

        return $rows;
    }

    private function validateTickTiers(array $tiers, $path)
    {
        $expected = [
            [1, null, false, 200, false, 1, 10],
            [2, 200, true, 500, false, 2, 20],
            [3, 500, true, 2000, false, 5, 50],
            [4, 2000, true, 5000, false, 10, 100],
            [5, 5000, true, null, false, 25, 250],
        ];
        $rows = [];
        foreach ($tiers as $index => $tier) {
            $this->assertExactKeys($tier, [
                'tier_sequence', 'price_min_idr', 'price_min_inclusive', 'price_max_idr',
                'price_max_inclusive', 'tick_size_idr', 'maximum_price_step_idr',
            ], $path.'['.$index.']');
            $actual = [
                $tier['tier_sequence'], $tier['price_min_idr'], $tier['price_min_inclusive'],
                $tier['price_max_idr'], $tier['price_max_inclusive'], $tier['tick_size_idr'],
                $tier['maximum_price_step_idr'],
            ];
            if ($actual !== $expected[$index]) {
                throw new \RuntimeException('STAGE_7_TICK_SIZE_TIER_INVALID: tick ladder must exactly match the sourced IDX tiers.');
            }
            $rows[] = $tier;
        }

        return $rows;
    }

    private function assertCompleteCoverage(array $revisions, $datasetStart)
    {
        $byType = ['PRICE_BAND' => [], 'MINIMUM_PRICE' => [], 'TICK_SIZE' => []];
        foreach ($revisions as $revision) {
            $byType[$revision['row']['rule_type']][] = $revision['row'];
        }
        if (count($byType['PRICE_BAND']) !== 4 || count($byType['MINIMUM_PRICE']) !== 1 || count($byType['TICK_SIZE']) !== 1) {
            throw new \RuntimeException('STAGE_7_COVERAGE_INCOMPLETE: manifest requires four band regimes, one floor, and one tick ladder.');
        }

        usort($byType['PRICE_BAND'], function ($a, $b) {
            return strcmp($a['effective_from'], $b['effective_from']);
        });
        $expectedBandRanges = [
            ['2021-12-01', '2023-06-04', 7],
            ['2023-06-05', '2023-09-03', 15],
            ['2023-09-04', '2025-04-07', null],
            ['2025-04-08', null, 15],
        ];
        foreach ($byType['PRICE_BAND'] as $index => $row) {
            if ($row['effective_from'] !== $expectedBandRanges[$index][0]
                || $row['effective_to'] !== $expectedBandRanges[$index][1]) {
                throw new \RuntimeException('STAGE_7_BAND_COVERAGE_GAP: band regimes must be contiguous across the dataset.');
            }
            $revision = $this->revisionByRuleUid($revisions, $row['rule_uid']);
            $lowerValues = array_values(array_unique(array_column($revision['band_tiers'], 'lower_limit_percent')));
            if ($index === 2) {
                if ($lowerValues !== [35, 25, 20]) {
                    throw new \RuntimeException('STAGE_7_BAND_REGIME_CONFLICT: symmetric regime must mirror tiered upper limits.');
                }
            } elseif ($lowerValues !== [$expectedBandRanges[$index][2]]) {
                throw new \RuntimeException('STAGE_7_BAND_REGIME_CONFLICT: asymmetric lower limit differs from the sourced regime.');
            }
        }
        foreach (['MINIMUM_PRICE', 'TICK_SIZE'] as $type) {
            $row = $byType[$type][0];
            if ($row['effective_from'] > $datasetStart || $row['effective_to'] !== null) {
                throw new \RuntimeException('STAGE_7_COVERAGE_INCOMPLETE: '.$type.' must cover the full dataset and remain open-ended.');
            }
        }
    }

    private function revisionByRuleUid(array $revisions, $ruleUid)
    {
        foreach ($revisions as $revision) {
            if ($revision['row']['rule_uid'] === $ruleUid) {
                return $revision;
            }
        }

        throw new \RuntimeException('STAGE_7_INTERNAL_REVISION_LOOKUP_FAILED');
    }

    private function findRevision(array $revision, $lock)
    {
        $query = DB::table('md_exchange_market_structure_revisions')
            ->where('rule_uid', $revision['row']['rule_uid'])
            ->where('revision_number', $revision['row']['revision_number']);
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function findLatestRevision($ruleUid, $lock)
    {
        $query = DB::table('md_exchange_market_structure_revisions')
            ->where('rule_uid', $ruleUid)
            ->orderByDesc('revision_number')
            ->orderByDesc('market_structure_revision_id');
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    private function findAcceptedSourceObservationId(array $source, $lock)
    {
        $query = DB::table('md_exchange_market_structure_revisions as r')
            ->join('md_source_observations as o', 'o.source_observation_id', '=', 'r.source_observation_id')
            ->where('r.source_uid', $source['source_uid'])
            ->where('o.outcome_state', 'ACCEPTED')
            ->where('o.validation_state', 'PASSED')
            ->orderByDesc('r.revision_number')
            ->orderByDesc('r.market_structure_revision_id')
            ->select('r.source_observation_id');
        if ($lock) {
            $query->lockForUpdate();
        }
        foreach ($query->get() as $row) {
            if ($this->evidenceObservationMatches((int) $row->source_observation_id, $source)) {
                return (int) $row->source_observation_id;
            }
        }

        return null;
    }

    private function assertExistingRevisionMatches($existing, array $revision)
    {
        $this->assertStoredRevisionContentMatches($existing, $revision);
        if (! $this->evidenceObservationMatches(
            (int) $existing->source_observation_id,
            $revision['source']
        )) {
            throw new \RuntimeException(
                'STAGE_7_SOURCE_OBSERVATION_INVALID: revision is not bound to the verified response identity.'
            );
        }
    }

    private function assertStoredRevisionContentMatches($existing, array $revision)
    {
        foreach ($revision['row'] as $field => $expected) {
            if (in_array($field, ['source_observation_id', 'recorded_at'], true)) {
                continue;
            }
            $actual = $existing->{$field};
            if (! $this->storedValueMatches($actual, $expected)) {
                throw new \RuntimeException('STAGE_7_REVISION_CONFLICT: immutable revision differs at '.$field.'.');
            }
        }

        $revisionId = (int) $existing->market_structure_revision_id;
        $actualBand = DB::table('md_exchange_price_band_tiers')
            ->where('market_structure_revision_id', $revisionId)->orderBy('tier_sequence')->get();
        $actualTick = DB::table('md_exchange_tick_size_tiers')
            ->where('market_structure_revision_id', $revisionId)->orderBy('tier_sequence')->get();
        if (! $this->tierRowsMatch($actualBand, $revision['band_tiers'])
            || ! $this->tierRowsMatch($actualTick, $revision['tick_tiers'])) {
            throw new \RuntimeException('STAGE_7_REVISION_CONFLICT: immutable tier rows differ from the manifest.');
        }
    }

    private function evidenceObservationMatches($observationId, array $source)
    {
        $accepted = DB::table('md_source_observations')
            ->where('source_observation_id', $observationId)
            ->first();
        if (! $accepted || $accepted->parent_observation_id === null) {
            return false;
        }
        $capture = DB::table('md_source_observations')
            ->where('source_observation_id', $accepted->parent_observation_id)
            ->first();
        if (! $capture) {
            return false;
        }

        foreach ([$capture, $accepted] as $row) {
            if ((int) $row->response_status !== 200
                || ! hash_equals($source['document_sha256'], (string) $row->payload_hash)
                || (string) $row->payload_ref !== 'sha256:'.$source['document_sha256']
                || (int) $row->payload_byte_length !== (int) $source['document_byte_length']
                || ! preg_match('/^[a-f0-9]{64}$/', (string) $row->schema_fingerprint)
                || (string) $row->adapter_version !== self::SOURCE_ADAPTER_VERSION
                || (string) $row->source_name !== $source['authority_name']
                || (string) $row->provider !== $source['authority_name']
                || (string) $row->sanitized_request_identity !== $source['document_url']
                || ! $this->contentTypeMatchesExpected($source['content_type'], (string) $row->content_type)) {
                return false;
            }
        }

        return $capture->outcome_state === 'CAPTURED'
            && $capture->validation_state === 'PENDING'
            && $capture->reason_code === null
            && trim((string) $capture->bounded_payload_body) !== ''
            && $accepted->outcome_state === 'ACCEPTED'
            && $accepted->validation_state === 'PASSED'
            && $accepted->reason_code === 'AUTHORITATIVE_MARKET_STRUCTURE_VALIDATED'
            && hash_equals((string) $capture->payload_hash, (string) $accepted->payload_hash)
            && $accepted->bounded_payload_body === null;
    }

    private function withRevisionVersion(array $revision, $revisionNumber, $supersedesRevisionId)
    {
        $revision['content']['revision_number'] = $revisionNumber;
        $revision['row']['revision_number'] = $revisionNumber;
        $revision['row']['supersedes_revision_id'] = $supersedesRevisionId;
        $revision['row']['content_hash'] = hash('sha256', $this->canonicalJson($revision['content']));

        return $revision;
    }

    private function validateVerificationResult(array $source, $result)
    {
        if (! is_array($result)
            || (int) ($result['http_status'] ?? 0) !== 200
            || ! hash_equals($source['document_sha256'], (string) ($result['document_sha256'] ?? ''))
            || (int) ($result['document_byte_length'] ?? 0) !== (int) $source['document_byte_length']
            || (string) ($result['payload_ref'] ?? '') !== 'sha256:'.$source['document_sha256']
            || ! preg_match('/^[a-f0-9]{64}$/', (string) ($result['schema_fingerprint'] ?? ''))
            || ! $this->contentTypeMatchesExpected($source['content_type'], (string) ($result['content_type'] ?? ''))) {
            throw new \RuntimeException(
                'STAGE_7_VERIFICATION_RESULT_INVALID: verifier result does not carry the exact observed document identity.'
            );
        }

        $sample = json_decode((string) ($result['bounded_payload_body'] ?? ''), true);
        $sampleBytes = is_array($sample) && ($sample['encoding'] ?? null) === 'base64'
            ? base64_decode((string) ($sample['sample_base64'] ?? ''), true)
            : false;
        if ($sampleBytes === false || $sampleBytes === ''
            || (int) ($sample['sample_byte_length'] ?? -1) !== strlen($sampleBytes)
            || ! hash_equals((string) ($sample['sample_sha256'] ?? ''), hash('sha256', $sampleBytes))) {
            throw new \RuntimeException(
                'STAGE_7_VERIFICATION_SAMPLE_INVALID: verifier result lacks a self-consistent bounded response sample.'
            );
        }

        return $result;
    }

    private function contentTypeMatchesExpected($expected, $actual)
    {
        $actual = strtolower(trim((string) $actual));
        if ($expected === 'application/pdf') {
            return strpos($actual, 'application/pdf') === 0
                || strpos($actual, 'application/binary') === 0
                || strpos($actual, 'application/octet-stream') === 0;
        }

        return strpos($actual, strtolower((string) $expected)) === 0;
    }

    private function tierRowsMatch($actualRows, array $expectedRows)
    {
        if ($actualRows->count() !== count($expectedRows)) {
            return false;
        }
        foreach ($actualRows as $index => $actual) {
            foreach ($expectedRows[$index] as $field => $expected) {
                $value = $actual->{$field};
                if (! $this->storedValueMatches($value, $expected)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function storedValueMatches($actual, $expected)
    {
        if ($expected === null) {
            return $actual === null;
        }
        if (is_bool($expected)) {
            return (bool) $actual === $expected;
        }
        if (is_int($expected) || is_float($expected)) {
            return is_numeric($actual) && (float) $actual === (float) $expected;
        }

        return (string) $actual === (string) $expected;
    }

    private function summarize(array $prepared, $applied)
    {
        $inserted = 0;
        $unchanged = 0;
        $evidenceCorrections = 0;
        $bandTiers = 0;
        $tickTiers = 0;
        foreach ($prepared['revisions'] as $revision) {
            if ($revision['operation'] === 'INSERT') {
                $inserted++;
                if ($revision['is_evidence_correction']) {
                    $evidenceCorrections++;
                }
                $bandTiers += count($revision['band_tiers']);
                $tickTiers += count($revision['tick_tiers']);
            } else {
                $unchanged++;
            }
        }

        return [
            'scope_id' => $prepared['scope_id'],
            'scope_entry_count' => count($prepared['revisions']),
            'inserted_revision_count' => $inserted,
            'unchanged_revision_count' => $unchanged,
            'evidence_correction_revision_count' => $evidenceCorrections,
            'inserted_price_band_tier_count' => $bandTiers,
            'inserted_tick_size_tier_count' => $tickTiers,
            'source_observation_insert_count' => 0,
            'applied' => $applied,
        ];
    }

    private function assertExactKeys(array $value, array $expected, $path)
    {
        $actual = array_keys($value);
        sort($actual);
        sort($expected);
        if ($actual !== $expected) {
            throw new \RuntimeException('STAGE_7_SCHEMA_DRIFT: '.$path.' keys differ from the locked manifest schema.');
        }
    }

    private function canonicalJson($value)
    {
        $normalized = $this->normalize($value);

        return json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($this->isList($value)) {
            return array_map(function ($item) {
                return $this->normalize($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }

        return $value;
    }

    private function isList(array $value)
    {
        return $value === [] || array_keys($value) === range(0, count($value) - 1);
    }

    private function isIsoDate($value)
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = \DateTime::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value;
    }
}
