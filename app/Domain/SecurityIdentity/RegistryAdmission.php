<?php

namespace App\Domain\SecurityIdentity;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;

/** Pure admission rules. A retained registry never infers identity from names/symbols. */
final class RegistryAdmission
{
    public function validate(FoundationRegistry $registry): void
    {
        $d = $registry->document();
        $packages = $this->index($d['packages'], 'package_hash');
        $entities = $this->index($d['entities'], 'identity_id');
        $revisions = $this->index($d['revisions'], 'revision_id');
        $this->index($d['holds'], 'hold_hash');
        foreach ($packages as $p) {
            $this->keys($p, ['package_hash', 'package_version', 'admission_evidence', 'sources', 'recorded_at']);
            $this->require(preg_match('/^[a-f0-9]{64}$/D', $p['package_hash']) === 1, 'PACKAGE_HASH_INVALID');
            $this->require(!empty($p['package_version']) && !empty($p['admission_evidence']) && !empty($p['sources']), 'PACKAGE_PROVENANCE_REQUIRED');
            $this->require(is_array($p['sources']), 'SOURCE_FACT_SHAPE_INVALID');
            $this->time($p['recorded_at']);
            foreach ($p['sources'] as $locator => $source) {
                $this->require(is_array($source), 'SOURCE_FACT_SHAPE_INVALID');
                $this->keys($source, ['facts', 'admitted_fields', 'evidence_class', 'source_revision', 'source_known_at', 'captured_at', 'review_reference']);
                $this->require(is_array($source['facts']) && is_array($source['admitted_fields']), 'SOURCE_FACT_SHAPE_INVALID');
                $this->require($locator !== '' && !empty($source['review_reference']) && isset($source['facts'], $source['admitted_fields'], $source['evidence_class']), 'SOURCE_PROVENANCE_REQUIRED');
                $this->time($source['captured_at']);
                if ($source['source_known_at'] !== null) { $this->time($source['source_known_at']); }
                $this->require($source['source_known_at'] === null || $source['source_known_at'] <= $source['captured_at'], 'SOURCE_KNOWLEDGE_AFTER_CAPTURE');
            }
        }
        foreach ($entities as $e) {
            $this->keys($e, ['identity_id', 'entity_type', 'parent_identity_id', 'package_hash', 'source_locator']);
            $this->uuid($e['identity_id']);
            $this->require(in_array($e['entity_type'], ['ISSUER', 'INSTRUMENT', 'LISTING'], true), 'ENTITY_TYPE_INVALID');
            $this->source($e, $packages);
            $parent = $e['parent_identity_id'];
            if ($e['entity_type'] === 'ISSUER') {
                $this->require($parent === null, 'ISSUER_PARENT_FORBIDDEN');
            } else {
                $expected = $e['entity_type'] === 'INSTRUMENT' ? 'ISSUER' : 'INSTRUMENT';
                $this->require(isset($entities[$parent]) && $entities[$parent]['entity_type'] === $expected, 'ENTITY_PARENT_INVALID');
            }
        }
        foreach ($revisions as $rev) {
            $this->keys($rev, ['revision_id', 'identity_id', 'revision_type', 'state', 'valid_from', 'valid_to', 'known_at', 'recorded_at', 'package_hash', 'source_locator', 'supersedes_revision_id', 'data']);
            $this->require(is_array($rev['data']) && $rev['data'] !== [], 'REVISION_FACTS_REQUIRED');
            $this->uuid($rev['revision_id']);
            $this->require(isset($entities[$rev['identity_id']]), 'REVISION_ENTITY_MISSING');
            $this->require(in_array($rev['revision_type'], ['PROFILE', 'LISTING', 'SYMBOL', 'BOARD', 'PROVIDER_MAPPING', 'CONTINUITY'], true), 'REVISION_TYPE_INVALID');
            $this->require(in_array($rev['state'], ['ADMITTED', 'HELD', 'AMBIGUOUS', 'RETRACTED'], true), 'REVISION_STATE_INVALID');
            $source = $this->source($rev, $packages);
            $this->time($rev['recorded_at']);
            $this->require($rev['recorded_at'] === $source['captured_at'], 'CAPTURE_TIME_MISMATCH');
            $this->require($rev['known_at'] === $source['source_known_at'], 'HISTORICAL_KNOWLEDGE_NOT_EVIDENCED');
            if ($rev['valid_from'] !== null) { $this->time($rev['valid_from']); }
            if ($rev['valid_to'] !== null) {
                $this->time($rev['valid_to']);
                $this->require($rev['valid_from'] !== null && $rev['valid_from'] < $rev['valid_to'], 'INTERVAL_INVALID');
            }
            foreach ($rev['data'] as $field => $value) {
                $this->require(in_array($field, $source['admitted_fields'], true) && array_key_exists($field, $source['facts']) && $source['facts'][$field] === $value, 'FACT_NOT_ADMITTED:'.$field);
            }
            if ($rev['state'] === 'ADMITTED') {
                $this->require(in_array($source['evidence_class'], ['BOUNDED_FACT', 'PRIMARY_MASTER_EVIDENCE'], true), 'CORROBORATING_FACT_NOT_AUTHORITATIVE');
                if ($rev['revision_type'] !== 'PROFILE') {
                    $this->require($entities[$rev['identity_id']]['entity_type'] === 'LISTING', 'LIFECYCLE_TARGET_INVALID');
                    $this->require($source['evidence_class'] === 'PRIMARY_MASTER_EVIDENCE', 'LIFECYCLE_PRIMARY_EVIDENCE_REQUIRED');
                    $this->require($rev['valid_from'] !== null && ($source['facts']['valid_from'] ?? null) === $rev['valid_from'] && array_key_exists('valid_to', $source['facts']) && $source['facts']['valid_to'] === $rev['valid_to'], 'LIFECYCLE_INTERVAL_NOT_EVIDENCED');
                }
                if ($rev['revision_type'] === 'LISTING') {
                    foreach (['venue', 'continuity', 'history_verified_through', 'listing_state', 'change_reason'] as $field) { $this->require(!empty($rev['data'][$field]), 'LISTING_CONTEXT_REQUIRED:'.$field); }
                    $this->require(in_array($rev['data']['continuity'], ['NEW_LISTING', 'ESTABLISHED'], true), 'CONTINUITY_UNRESOLVED');
                    $this->require(in_array($rev['data']['listing_state'], ['LISTED', 'DELISTED'], true), 'LISTING_STATE_INVALID');
                    $this->time($rev['data']['history_verified_through']);
                }
                $required = ['SYMBOL' => ['namespace', 'symbol'], 'BOARD' => ['market_segment', 'board'], 'PROVIDER_MAPPING' => ['namespace', 'symbol']];
                foreach ($required[$rev['revision_type']] ?? [] as $field) { $this->require(!empty($rev['data'][$field]), 'MAPPING_CONTEXT_REQUIRED:'.$field); }
            }
            $previous = $rev['supersedes_revision_id'];
            if ($previous !== null) {
                $this->require(isset($revisions[$previous]) && $previous !== $rev['revision_id'] && $revisions[$previous]['identity_id'] === $rev['identity_id'] && $revisions[$previous]['revision_type'] === $rev['revision_type'], 'SUPERSESSION_TARGET_INVALID');
                $seen = [$rev['revision_id'] => true];
                while ($previous !== null) {
                    $this->require(isset($revisions[$previous]), 'SUPERSESSION_TARGET_INVALID');
                    $this->require(!isset($seen[$previous]), 'SUPERSESSION_CYCLE');
                    $seen[$previous] = true;
                    $previous = $revisions[$previous]['supersedes_revision_id'];
                }
            }
        }
        foreach ($entities as $e) {
            if ($e['entity_type'] === 'LISTING') {
                $proof = array_filter($revisions, static fn (array $v): bool => $v['identity_id'] === $e['identity_id'] && $v['revision_type'] === 'LISTING' && $v['state'] === 'ADMITTED');
                $this->require(count($proof) > 0, 'LISTING_ADMISSION_MISSING');
            }
        }
        foreach ($d['holds'] as $hold) {
            $this->require(isset($packages[$hold['package_hash']]) && !empty($hold['scope']) && !empty($hold['held_facts']) && is_array($hold['held_facts']), 'HOLD_PROVENANCE_REQUIRED');
            $this->require($hold['hold_hash'] === hash('sha256', $hold['package_hash'].'|'.$hold['scope']), 'HOLD_KEY_INVALID');
        }
    }

    private function source(array $row, array $packages): array
    {
        $this->require(isset($packages[$row['package_hash']]['sources'][$row['source_locator']]), 'SOURCE_PROVENANCE_REQUIRED');
        return $packages[$row['package_hash']]['sources'][$row['source_locator']];
    }

    private function index(array $rows, string $key): array
    {
        $this->require(count($rows) <= 10000, 'BOUNDED_REGISTRY_LIMIT');
        $out = [];
        foreach ($rows as $row) {
            $this->require(is_array($row) && !empty($row[$key]) && !isset($out[$row[$key]]), 'DUPLICATE_OR_MISSING_REGISTRY_KEY:'.$key);
            $out[$row[$key]] = $row;
        }
        return $out;
    }

    private function uuid(string $value): void { $this->require(preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $value) === 1, 'RETAINED_ID_INVALID'); }
    private function keys(array $row, array $keys): void
    {
        foreach ($keys as $key) { $this->require(array_key_exists($key, $row), 'PROVENANCE_OR_REGISTRY_FIELD_REQUIRED:'.$key); }
    }
    private function time(string $value): void
    {
        $t = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value, new \DateTimeZone('UTC'));
        $this->require($t !== false && $t->format('Y-m-d H:i:s') === $value, 'UTC_TIME_INVALID');
    }
    private function require(bool $condition, string $reason): void { if (!$condition) { throw new \DomainException($reason); } }
}
