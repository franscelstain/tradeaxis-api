<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\ProducerSourceObservationPopulation;
use Illuminate\Support\Facades\DB;

/**
 * V2 content identity of source observations (Audit_Hash_and_Reproducibility_Contract_LOCKED.md:99-101).
 *
 * An entry carries the members the contract names: payload hash and reference, provider and
 * mapping revision, requested dates, source and acquisition timestamps, adapter and schema
 * versions, outcome and reason. Local keys and the random `observation_uid` never enter it. The
 * parent capture and a superseded observation are referenced by their own entry identities, so a
 * refetch (a new acquisition time) is a new identity while another allocation of the same facts is
 * not.
 */
class SemanticObservationIdentityService
{
    public const MANIFEST_SCHEMA = 'market-data-observation-manifest/v2';
    public const ENTRY_SCHEMA = 'market-data-observation-entry/v2';

    private DeterministicHashService $hashes;

    public function __construct(?DeterministicHashService $hashes = null)
    {
        $this->hashes = $hashes ?: new DeterministicHashService();
    }

    /**
     * The V2 manifest over exactly the root observations the V1 manifest lists. The population is
     * read through the sanctioned content-only path, so it is validated the same way the V1 journal
     * validates it and no producer selection is repeated.
     */
    public function manifestHashForObservationIds(array $observationIds): string
    {
        $ids = $this->normalizeIds($observationIds);
        $population = ProducerSourceObservationPopulation::immutablePopulation($ids);
        $rows = [];
        foreach ($population['tables']['md_source_observations'] as $row) {
            $rows[(int) $row['source_observation_id']] = (array) $row;
        }

        $entries = [];
        $memo = [];
        foreach ($ids as $id) {
            if (! isset($rows[$id])) {
                throw new \RuntimeException('SEMANTIC_OBSERVATION_MANIFEST_INCOMPLETE: '.$id);
            }
            $entries[] = $this->entryDocument($id, $rows, $memo, [$id => true]);
        }

        return $this->hashes->hashCanonicalDocument([
            'schema_version' => self::MANIFEST_SCHEMA,
            'entries' => $entries,
        ]);
    }

    /**
     * Entry identities of individually referenced observations, keyed by the local id the caller
     * used to find them. Callers may navigate with the key; only the value is semantic.
     *
     * @return array<int,string>
     */
    public function entryHashes(array $observationIds): array
    {
        $ids = $this->normalizeIds($observationIds);
        $rows = $this->loadWithAncestors($ids);
        $memo = [];
        $hashes = [];
        foreach ($ids as $id) {
            if (! isset($rows[$id])) {
                throw new \RuntimeException('SEMANTIC_OBSERVATION_REFERENCE_UNRESOLVED: '.$id);
            }
            $hashes[$id] = $this->entryHash($id, $rows, $memo, []);
        }

        return $hashes;
    }

    private function entryHash(int $id, array $rows, array &$memo, array $path): string
    {
        if (isset($memo[$id])) {
            return $memo[$id];
        }
        if (isset($path[$id])) {
            throw new \RuntimeException('SEMANTIC_OBSERVATION_LINEAGE_CYCLE: '.$id);
        }
        $memo[$id] = $this->hashes->hashCanonicalDocument([
            'schema_version' => self::ENTRY_SCHEMA,
            'entry' => $this->entryDocument($id, $rows, $memo, $path + [$id => true]),
        ]);

        return $memo[$id];
    }

    private function entryDocument(int $id, array $rows, array &$memo, array $path): array
    {
        $row = $rows[$id];
        $reference = function (string $field) use ($row, $rows, &$memo, $path): ?string {
            $referenced = $row[$field] ?? null;
            if ($referenced === null || $referenced === '') {
                return null;
            }
            $referenced = (int) $referenced;
            if (! isset($rows[$referenced])) {
                throw new \RuntimeException('SEMANTIC_OBSERVATION_ANCESTRY_MISSING: '.$referenced);
            }

            return $this->entryHash($referenced, $rows, $memo, $path);
        };

        return [
            'payload_hash' => $this->lowerOrNull($row['payload_hash'] ?? null),
            'payload_ref' => $this->textOrNull($row['payload_ref'] ?? null),
            'provider' => $this->textOrNull($row['provider'] ?? null),
            'provider_symbol' => $this->textOrNull($row['provider_symbol'] ?? null),
            'mapping_revision' => $this->textOrNull($row['mapping_revision'] ?? null),
            'requested_trade_date' => $this->dateOrNull($row['requested_trade_date'] ?? null),
            'requested_start_date' => $this->dateOrNull($row['requested_start_date'] ?? null),
            'requested_end_date' => $this->dateOrNull($row['requested_end_date'] ?? null),
            'source_timestamp' => $this->timestampOrNull($row['source_timestamp'] ?? null),
            'acquired_at' => $this->timestampOrNull($row['acquired_at'] ?? null),
            'adapter_version' => $this->textOrNull($row['adapter_version'] ?? null),
            'schema_fingerprint' => $this->lowerOrNull($row['schema_fingerprint'] ?? null),
            'provider_schema_version' => $this->textOrNull($row['provider_schema_version'] ?? null),
            'outcome_state' => $this->textOrNull($row['outcome_state'] ?? null),
            'validation_state' => $this->textOrNull($row['validation_state'] ?? null),
            'reason_code' => $this->textOrNull($row['reason_code'] ?? null),
            'parent_entry_hash' => $reference('parent_observation_id'),
            'supersedes_entry_hash' => $reference('supersedes_observation_id'),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function loadWithAncestors(array $ids): array
    {
        $rows = [];
        $pending = $ids;
        for ($depth = 0; $pending !== [] && $depth < 64; $depth++) {
            $found = DB::table('md_source_observations')->whereIn('source_observation_id', $pending)->get()->all();
            $next = [];
            foreach ($found as $row) {
                $row = (array) $row;
                $id = (int) $row['source_observation_id'];
                $rows[$id] = $row;
                foreach (['parent_observation_id', 'supersedes_observation_id'] as $field) {
                    $ancestor = $row[$field] ?? null;
                    if ($ancestor !== null && $ancestor !== '' && ! isset($rows[(int) $ancestor])) {
                        $next[] = (int) $ancestor;
                    }
                }
            }
            $pending = array_values(array_unique($next));
        }
        if ($pending !== []) {
            throw new \RuntimeException('SEMANTIC_OBSERVATION_LINEAGE_TOO_DEEP');
        }

        return $rows;
    }

    private function normalizeIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    private function textOrNull($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function lowerOrNull($value): ?string
    {
        $value = $this->textOrNull($value);

        return $value === null ? null : strtolower($value);
    }

    private function dateOrNull($value): ?string
    {
        $value = $this->textOrNull($value);

        return $value === null ? null : substr($value, 0, 10);
    }

    private function timestampOrNull($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        $value = $this->textOrNull($value);

        return $value === null ? null : substr($value, 0, 19);
    }
}
