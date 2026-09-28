<?php

namespace App\Domain\SecurityIdentity;

use App\Application\SecurityIdentity\Contracts\FoundationRegistry;
use App\Application\SecurityIdentity\Contracts\IdentityResolution;

/** Pure resolver. Effective/knowledge instants are explicit UTC, intervals half-open. */
final class TemporalIdentityResolution
{
    public function resolve(FoundationRegistry $registry, string $namespace, string $symbol, string $effectiveAt, string $knowledgeCutoff): IdentityResolution
    {
        foreach ([$effectiveAt, $knowledgeCutoff] as $instant) {
            $t = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $instant, new \DateTimeZone('UTC'));
            if (!$t || $t->format('Y-m-d H:i:s') !== $instant) { throw new \InvalidArgumentException('UTC_TIME_INVALID'); }
        }
        $d = $registry->document();
        $visible = [];
        foreach ($d['revisions'] as $row) {
            if ($row['known_at'] !== null && $row['known_at'] <= $knowledgeCutoff && $row['recorded_at'] <= $knowledgeCutoff) { $visible[$row['revision_id']] = $row; }
        }
        foreach ($visible as $row) { if ($row['supersedes_revision_id'] !== null) { unset($visible[$row['supersedes_revision_id']]); } }
        $active = array_values(array_filter($visible, static fn (array $row): bool => $row['valid_from'] !== null && $row['valid_from'] <= $effectiveAt && ($row['valid_to'] === null || $effectiveAt < $row['valid_to'])));
        $mappings = array_values(array_filter($active, static fn (array $row): bool => $row['revision_type'] === 'PROVIDER_MAPPING' && ($row['data']['namespace'] ?? null) === $namespace && ($row['data']['symbol'] ?? null) === $symbol));
        if (count($mappings) > 1) { return new IdentityResolution('AMBIGUOUS', 'OVERLAPPING_PROVIDER_MAPPING'); }
        if (count($mappings) !== 1 || $mappings[0]['state'] !== 'ADMITTED') { return new IdentityResolution('HELD', 'IDENTITY_OR_MAPPING_UNAVAILABLE'); }
        $mapping = $mappings[0];
        $selected = ['PROVIDER_MAPPING' => $mapping];
        foreach (['LISTING', 'SYMBOL', 'BOARD'] as $type) {
            $rows = array_values(array_filter($active, static fn (array $row): bool => $row['identity_id'] === $mapping['identity_id'] && $row['revision_type'] === $type));
            if (count($rows) > 1) { return new IdentityResolution('AMBIGUOUS', 'OVERLAPPING_'.$type.'_REVISION'); }
            if (count($rows) !== 1 || $rows[0]['state'] !== 'ADMITTED') { return new IdentityResolution('HELD', $type.'_EVIDENCE_UNAVAILABLE'); }
            $selected[$type] = $rows[0];
        }
        $listing = $selected['LISTING'];
        if (($listing['data']['listing_state'] ?? null) !== 'LISTED') { return new IdentityResolution('HELD', 'LISTING_NOT_VALID'); }
        if (!in_array($listing['data']['continuity'] ?? null, ['NEW_LISTING', 'ESTABLISHED'], true)) { return new IdentityResolution('HELD', 'CONTINUITY_UNRESOLVED'); }
        if (($listing['data']['history_verified_through'] ?? '') < $effectiveAt) { return new IdentityResolution('HELD', 'LIFECYCLE_OUTSIDE_VERIFIED_SCOPE'); }
        $entities = array_column($d['entities'], null, 'identity_id');
        $l = $entities[$mapping['identity_id']] ?? null;
        $i = $l ? ($entities[$l['parent_identity_id']] ?? null) : null;
        $issuer = $i ? ($entities[$i['parent_identity_id']] ?? null) : null;
        if (!$l || !$i || !$issuer || $l['entity_type'] !== 'LISTING' || $i['entity_type'] !== 'INSTRUMENT' || $issuer['entity_type'] !== 'ISSUER') { return new IdentityResolution('HELD', 'LISTING_LINKAGE_UNAVAILABLE'); }
        return new IdentityResolution('RESOLVED', 'EVIDENCED_TEMPORAL_IDENTITY', $issuer['identity_id'], $i['identity_id'], $l['identity_id'], [
            'exchange_symbol' => $selected['SYMBOL']['data']['symbol'],
            'exchange_namespace' => $selected['SYMBOL']['data']['namespace'],
            'provider_namespace' => $namespace, 'provider_symbol' => $symbol,
            'venue' => $listing['data']['venue'],
            'market_segment' => $selected['BOARD']['data']['market_segment'],
            'board' => $selected['BOARD']['data']['board'],
            'effective_at' => $effectiveAt, 'knowledge_cutoff' => $knowledgeCutoff,
            'revisions' => $selected,
        ]);
    }
}
