<?php

namespace App\Application\MarketData\Services;

/**
 * V2-profile replay identities of the formula registry, the reason registry and the contamination decisions
 * (D-MD-B18-A002-014: Q4 = A, Q7 = A). Pure and database-free: each function takes already-verified captured content and returns a
 * SHA-256 or NULL, where NULL means "the identity is unavailable" and the replay is BLOCKED. Nothing here substitutes a V1 value, a
 * live value or a local identifier, and V1-profile publications never reach it.
 *
 * The identities are semantic: they contain the members stated below and nothing else. Executable-build content (file hashes, archive
 * hash, PHP version) is the business of `executable_build_identity` and never enters a registry identity.
 *
 *   formula_registry_hash  = SHA-256 of canonical{schema_version, registry_contract, semantic_versions, indicator_set_version,
 *                            coverage_contract_version, eligibility_contract_version, config_registry_revision, config_resolver_version,
 *                            serialization_version, read_model_version}
 *   reason_registry_hash   = SHA-256 of canonical{schema_version, entries: [{code, category, description, severity, is_active}] ordered by code}
 *   contamination identity = SHA-256 of canonical{schema_version, contamination, price_scale_breaks}: decisions grouped by retained listing root,
 *                            semantic fields only (no ticker id, listing id, revision id, candidate uid), each group and entry list ordered
 *
 * "canonical" is the replay verifier's composite serialization: associative arrays ordered by key at every level, lists in the order given
 * here, JSON without escaped slashes or unicode, zero fractions preserved.
 */
final class ReplayV2IdentityProjection
{
    public const FORMULA_SCHEMA = 'market-data-formula-registry/v2';
    public const REASON_SCHEMA = 'market-data-reason-registry/v2';
    public const CONTAMINATION_SCHEMA = 'market-data-contamination-decision-set/v2';

    private const FORMULA_STRING_MEMBERS = [
        'registry_contract', 'indicator_set_version', 'coverage_contract_version', 'eligibility_contract_version',
        'config_registry_revision', 'config_resolver_version', 'serialization_version', 'read_model_version',
    ];
    private const REASON_FIELDS = ['code', 'category', 'description', 'severity'];

    /** Fields of one contamination entry that are semantic facts; `corporate_action_revision_id` is a local row id and is dropped by name. */
    private const CONTAMINATION_FIELDS = [
        'action_type_code', 'verification_state', 'ex_date', 'action_date', 'anchor_state', 'depth',
        'breaks_price_continuity', 'breaks_volume_continuity', 'is_unmapped_type', 'factor_hold_reason_code',
    ];
    private const CONTAMINATION_LOCAL_FIELDS = ['corporate_action_revision_id'];

    /** Fields of one price-scale-break entry that are semantic facts; `candidate_uid` is a platform row identity and is dropped by name. */
    private const PRICE_SCALE_BREAK_FIELDS = [
        'break_type', 'trade_date', 'depth', 'implied_ratio', 'inferred_ratio', 'match_status', 'matched_action_type', 'continuity_verdict',
    ];
    private const PRICE_SCALE_BREAK_LOCAL_FIELDS = ['candidate_uid'];

    /** @param array<string,mixed>|null $registry the decoded `registry_versions` capture row */
    public static function formulaRegistryIdentity($registry): ?string
    {
        if (! is_array($registry)) {
            return null;
        }
        $document = ['schema_version' => self::FORMULA_SCHEMA];
        foreach (self::FORMULA_STRING_MEMBERS as $member) {
            if (! isset($registry[$member]) || ! is_string($registry[$member]) || trim($registry[$member]) === '') {
                return null;
            }
            $document[$member] = $registry[$member];
        }
        $versions = $registry['semantic_versions'] ?? null;
        if (! is_array($versions) || $versions === []) {
            return null;
        }
        foreach ($versions as $name => $value) {
            if (! is_string($name) || ! is_string($value) || $value === '') {
                return null;
            }
        }
        $document['semantic_versions'] = $versions;

        return self::hash($document);
    }

    /** @param array<string,mixed>|null $registry the decoded `registry_versions` capture row */
    public static function reasonRegistryIdentity($registry): ?string
    {
        $entries = is_array($registry) ? ($registry['reason_entries'] ?? null) : null;
        if (! is_array($entries) || $entries === [] || array_keys($entries) !== range(0, count($entries) - 1)) {
            return null;
        }
        $byCode = [];
        foreach ($entries as $entry) {
            if (! is_array($entry) || ! array_key_exists('is_active', $entry) || ! is_bool($entry['is_active'])) {
                return null;
            }
            $normalized = ['is_active' => $entry['is_active']];
            foreach (self::REASON_FIELDS as $field) {
                if (! isset($entry[$field]) || ! is_string($entry[$field]) || ($field !== 'description' && $entry[$field] === '')) {
                    return null;
                }
                $normalized[$field] = $entry[$field];
            }
            if (isset($byCode[$normalized['code']])) {
                return null;
            }
            $byCode[$normalized['code']] = $normalized;
        }
        ksort($byCode, SORT_STRING);

        return self::hash(['schema_version' => self::REASON_SCHEMA, 'entries' => array_values($byCode)]);
    }

    /**
     * @param array<int|string,mixed> $contamination    ticker-keyed contamination decisions of the indicator dependency capture
     * @param array<int|string,mixed> $priceScaleBreaks ticker-keyed price-scale-break decisions of the same capture
     * @param array<int,string>       $rootsByTicker    retained listing root of each ticker the decisions mention; only values are semantic
     */
    public static function contaminationDecisionIdentity(array $contamination, array $priceScaleBreaks, array $rootsByTicker): ?string
    {
        $contaminationGroups = self::decisionGroups($contamination, $rootsByTicker, self::CONTAMINATION_FIELDS, self::CONTAMINATION_LOCAL_FIELDS);
        $breakGroups = self::decisionGroups($priceScaleBreaks, $rootsByTicker, self::PRICE_SCALE_BREAK_FIELDS, self::PRICE_SCALE_BREAK_LOCAL_FIELDS);
        if ($contaminationGroups === null || $breakGroups === null) {
            return null;
        }

        return self::hash([
            'schema_version' => self::CONTAMINATION_SCHEMA,
            'contamination' => $contaminationGroups,
            'price_scale_breaks' => $breakGroups,
        ]);
    }

    /** @return array<int,array{listing_root:string,decisions:array<int,array<string,mixed>>}>|null NULL when any decision cannot be projected */
    private static function decisionGroups(array $byTicker, array $rootsByTicker, array $semantic, array $local): ?array
    {
        $groups = [];
        foreach ($byTicker as $tickerId => $entries) {
            if (! is_array($entries)) {
                return null;
            }
            if ($entries === []) {
                continue;
            }
            $root = $rootsByTicker[(int) $tickerId] ?? null;
            if (! is_string($root) || preg_match('/^[a-f0-9-]{36}$/', $root) !== 1 || isset($groups[$root])) {
                return null;
            }
            $decisions = [];
            foreach ($entries as $entry) {
                if (! is_array($entry) || $entry === []) {
                    return null;
                }
                $projected = [];
                foreach ($entry as $field => $value) {
                    if (in_array($field, $local, true)) {
                        continue;
                    }
                    if (! in_array($field, $semantic, true) || (! is_scalar($value) && $value !== null)) {
                        return null;
                    }
                    $projected[$field] = $value;
                }
                ksort($projected, SORT_STRING);
                $decisions[] = $projected;
            }
            usort($decisions, static function ($a, $b) { return strcmp(json_encode($a), json_encode($b)); });
            $groups[$root] = ['listing_root' => $root, 'decisions' => $decisions];
        }
        ksort($groups, SORT_STRING);

        return array_values($groups);
    }

    /** The replay verifier's composite digest: keys ordered at every level, lists as given. */
    public static function hash(array $document): string
    {
        $json = json_encode(self::order($document), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        if ($json === false) {
            throw new \RuntimeException('REPLAY_BOUND_INPUT_SERIALIZATION_FAILED: replay identity cannot be serialized.');
        }

        return hash('sha256', $json);
    }

    private static function order($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        if (! $isList) {
            ksort($value, SORT_STRING);
        }
        foreach ($value as $key => $child) {
            $value[$key] = self::order($child);
        }

        return $value;
    }
}
