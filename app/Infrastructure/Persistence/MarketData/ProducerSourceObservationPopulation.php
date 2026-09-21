<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** Explicit immutable source lineage at the read, never a latest-publication lookup. */
final class ProducerSourceObservationPopulation
{
    public const TABLE_KEYS = [
        'md_source_observations' => 'source_observation_id',
        'md_source_observation_rows' => 'source_observation_row_id',
        'md_source_observation_rejected_rows' => 'source_observation_rejected_row_id',
        'md_source_observation_identity_bindings' => 'source_observation_identity_binding_id',
        'md_source_observation_revision_comparisons' => 'source_observation_comparison_id',
    ];

    public static function consume(string $kind, array $selection, array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids))); sort($ids, SORT_NUMERIC);
        $selection = ['operation' => 'source-observation-outcomes/v1', 'read_kind' => $kind, 'observation_ids' => $ids] + $selection;
        $values = ProducerInputScope::observationJournal($selection, static function () use ($selection, $ids) {
            $population = self::population($ids);
            $evaluated = self::evaluate($population, $selection);
            $payload = ['population' => $population, 'evaluation' => $evaluated];
            self::assertValid($payload, $selection);
            return [$payload];
        });
        return $values[0]['evaluation']['result'];
    }

    /** Content-only reuse for RAW lineage; does not perform another producer selection. */
    public static function immutablePopulation(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids))); sort($ids, SORT_NUMERIC);
        $population = self::population($ids);
        self::assertImmutablePopulation($population);
        return $population;
    }

    public static function assertImmutablePopulation(array $population): void
    {
        $selection = ['read_kind' => 'manifestIds', 'observation_ids' => $population['root_ids']];
        self::assertValid(['population' => $population, 'evaluation' => self::evaluate($population, $selection)], $selection);
    }

    private static function population(array $ids): array
    {
        $roots = $ids; $tables = []; $seen = [];
        do {
            $previous = $ids;
            $observations = DB::table('md_source_observations')->whereIn('source_observation_id', $ids)->orderBy('source_observation_id')->get()->all();
            $comparisons = DB::table('md_source_observation_revision_comparisons')->whereIn('current_source_observation_id', $ids)->orderBy('source_observation_comparison_id')->get()->all();
            foreach ($observations as $r) foreach (['parent_observation_id', 'supersedes_observation_id'] as $field) if ($r->$field !== null) $ids[] = (int) $r->$field;
            foreach ($comparisons as $r) $ids[] = (int) $r->prior_source_observation_id;
            $ids = array_values(array_unique($ids)); sort($ids, SORT_NUMERIC);
        } while ($ids !== $previous);
        $tables['md_source_observations'] = array_map(static function ($r) { return (array) $r; }, $observations);
        foreach (self::TABLE_KEYS as $table => $key) {
            if ($table === 'md_source_observations') continue;
            $column = $table === 'md_source_observation_revision_comparisons' ? 'current_source_observation_id' : 'source_observation_id';
            $tables[$table] = array_map(static function ($r) { return (array) $r; }, DB::table($table)->whereIn($column, $ids)->orderBy($key)->get()->all());
        }
        foreach ($tables['md_source_observations'] as $r) $seen[] = (int) $r['source_observation_id'];
        $missing = array_values(array_diff($ids, $seen));
        // An explicitly requested missing observation is a negative read; a missing ancestor is corruption.
        if (array_diff($missing, $roots)) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_ANCESTRY_MISSING');
        return ['schema' => 'source_observation_population_v1', 'root_ids' => $roots, 'missing_root_ids' => $missing,
            'tables' => $tables, 'population_counts' => array_map('count', $tables)];
    }

    public static function evaluate(array $population, array $selection): array
    {
        $tables = $population['tables']; $ids = $selection['observation_ids'];
        $obs = array_column($tables['md_source_observations'], null, 'source_observation_id');
        $rows = array_values(array_filter($tables['md_source_observation_rows'], static function ($r) use ($ids) { return in_array((int) $r['source_observation_id'], $ids, true); }));
        $rejected = array_values(array_filter($tables['md_source_observation_rejected_rows'], static function ($r) use ($ids) { return in_array((int) $r['source_observation_id'], $ids, true); }));
        $selected = []; $omitted = []; $kind = $selection['read_kind'];
        if ($kind === 'existsAccepted') {
            $id = $ids[0]; $outcome = $obs[$id] ?? null; $capture = $outcome ? ($obs[$outcome['parent_observation_id']] ?? null) : null;
            $ref = $selection['source_row_ref'];
            foreach ($rows as $r) {
                if ($ref === null || (string) $r['source_row_ref'] === $ref) $selected[] = (int) $r['source_observation_row_id'];
                else $omitted[] = ['row_id' => (int) $r['source_observation_row_id'], 'basis' => 'SOURCE_ROW_REF_NOT_REQUESTED'];
            }
            $bindings = array_values(array_filter($tables['md_source_observation_identity_bindings'], static function ($r) use ($id, $ref, $selected) {
                return (int) $r['source_observation_id'] === $id && ($ref === null || in_array((int) $r['source_observation_row_id'], $selected, true));
            }));
            $hash = strtolower((string) ($capture['payload_hash'] ?? '')); $schema = strtolower((string) ($capture['schema_fingerprint'] ?? ''));
            $result = $outcome && in_array($outcome['outcome_state'], ['ACCEPTED', 'NORMALIZED'], true)
                && $capture && $capture['outcome_state'] === 'CAPTURED'
                && (! empty($capture['run_id']) || ! empty($capture['acquisition_batch_id']))
                && count($selected) > 0 && count($bindings) === count($selected)
                && ! empty($capture['attempt_uid']) && ! empty($capture['requested_trade_date'])
                && ! empty($capture['source_mode']) && ! empty($capture['source_name'])
                && ! empty($capture['sanitized_request_identity']) && ! empty($capture['acquired_at']) && ! empty($capture['adapter_version'])
                && preg_match('/^[a-f0-9]{64}$/', $hash) === 1 && preg_match('/^[a-f0-9]{64}$/', $schema) === 1
                && (int) $capture['payload_byte_length'] > 0 && (string) $capture['payload_ref'] === 'sha256:'.$hash
                && strtolower((string) $outcome['payload_hash']) === $hash && strtolower((string) $outcome['schema_fingerprint']) === $schema;
        } elseif (in_array($kind, ['manifestIds', 'manifestRun'], true)) {
            if ($population['missing_root_ids']) throw new \RuntimeException('INPUT_CAPTURE_SOURCE_MANIFEST_INCOMPLETE');
            $members = array_values(array_filter($tables['md_source_observations'], static function ($r) use ($ids) { return in_array((int) $r['source_observation_id'], $ids, true); }));
            usort($members, static function ($a, $b) { return strcmp($a['observation_uid'], $b['observation_uid']); });
            $lines = [];
            foreach ($members as $r) {
                $selected[] = (int) $r['source_observation_id'];
                $line = [];
                foreach (['observation_uid','parent_observation_id','source_mode','provider','provider_symbol','provider_mapping_id','requested_trade_date','payload_hash','schema_fingerprint','adapter_version','outcome_state','reason_code'] as $f) $line[] = $r[$f] ?? '';
                $lines[] = implode('|', $line);
            }
            $result = hash('sha256', implode("\n", $lines));
        } elseif ($kind === 'priorRow') {
            $candidates = array_values(array_filter($rows, static function ($r) use ($selection) {
                return in_array((int) $r['source_observation_row_id'], $selection['candidate_row_ids'], true);
            }));
            if (count($candidates) !== count($selection['candidate_row_ids'])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_PRIOR_POPULATION');
            usort($candidates, static function ($a, $b) { return (int) $b['source_observation_row_id'] <=> (int) $a['source_observation_row_id']; });
            $result = $candidates[0] ?? null;
            if ($result) $selected[] = (int) $result['source_observation_row_id'];
            foreach (array_slice($candidates, 1) as $r) $omitted[] = ['row_id' => (int) $r['source_observation_row_id'], 'basis' => 'LOWER_PERSISTED_ROW_ID_THAN_SELECTED_PRIOR'];
        } elseif ($kind === 'incomingRows') {
            $result = $selection['input_rows'];
            foreach ($result as $index => $r) {
                $id = (int) ($r['source_observation_id'] ?? 0);
                if (isset($obs[$id])) $selected[] = ['input_index' => $index, 'observation_id' => $id, 'source_row_ref' => $r['source_row_ref'] ?? null];
                else $omitted[] = ['input_index' => $index, 'basis' => 'NO_PERSISTED_OBSERVATION_FOR_INPUT'];
            }
        } elseif ($kind === 'ingestSelection') {
            $key = static function ($r) { return (string) ($r['source_observation_id'] ?? '').'|'.(string) ($r['source_row_ref'] ?? ''); };
            $expected = array_map($key, $selection['input_rows']);
            $observed = array_merge(array_map($key, $selection['selected_rows']), array_map($key, $selection['omitted_rows']));
            sort($expected, SORT_STRING); sort($observed, SORT_STRING);
            if ($expected !== $observed) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_INGEST_PARTITION');
            foreach ($selection['omitted_rows'] as $r) if (empty($r['invalid_reason_code'])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_OMISSION_BASIS');
            $result = ['selected_rows' => $selection['selected_rows'], 'omitted_rows' => $selection['omitted_rows']];
            $selected = array_map($key, $selection['selected_rows']); $omitted = $selection['omitted_rows'];
        } elseif ($kind === 'normalizedAsKnown') {
            $result = [];
            foreach ($rows as $r) {
                $o = $obs[$r['source_observation_id']]; $matches = [];
                foreach ($tables['md_source_observation_identity_bindings'] as $b) if ((int) $b['source_observation_row_id'] === (int) $r['source_observation_row_id'] && $b['recorded_at'] <= $selection['known_at']) $matches[] = $b;
                if ($r['trade_date'] !== $selection['trade_date'] || $o['acquired_at'] > $selection['known_at'] || $matches === []) {
                    $omitted[] = ['row_id' => (int) $r['source_observation_row_id'], 'basis' => $r['trade_date'] !== $selection['trade_date'] ? 'OTHER_TRADE_DATE' : ($o['acquired_at'] > $selection['known_at'] ? 'OBSERVATION_AFTER_KNOWLEDGE_CUTOFF' : 'NO_IDENTITY_BINDING_KNOWN_AT_CUTOFF')];
                    continue;
                }
                foreach ($matches as $b) {
                    $value = [];
                    foreach (['source_observation_row_id','source_observation_id','source_row_ref','provider','provider_symbol','ticker_code','trade_date','source_timestamp','open_value','high_value','low_value','close_value','volume_value','adj_close_value','row_fingerprint'] as $f) $value[$f] = $r[$f];
                    foreach (['listing_id','provider_mapping_id','mapping_revision','effective_trade_date'] as $f) $value[$f] = $b[$f];
                    $value['identity_binding_recorded_at'] = $b['recorded_at'];
                    foreach (['observation_uid','source_mode','source_name','adapter_version','provider_schema_version','payload_hash','schema_fingerprint','acquired_at','outcome_state','reason_code'] as $f) $value[$f] = $o[$f];
                    $ordered = [];
                    foreach (['source_observation_row_id','source_observation_id','source_row_ref','listing_id','provider_mapping_id','mapping_revision','effective_trade_date','identity_binding_recorded_at','provider','provider_symbol','ticker_code','trade_date','source_timestamp','open_value','high_value','low_value','close_value','volume_value','adj_close_value','row_fingerprint','observation_uid','source_mode','source_name','adapter_version','provider_schema_version','payload_hash','schema_fingerprint','acquired_at','outcome_state','reason_code'] as $f) $ordered[$f] = $value[$f];
                    $result[] = $ordered;
                }
            }
            usort($result, static function ($a, $b) { return ((int) $a['listing_id'] <=> (int) $b['listing_id']) ?: ((int) $a['source_observation_row_id'] <=> (int) $b['source_observation_row_id']); });
            $selected = array_map('intval', array_column($result, 'source_observation_row_id'));
        } elseif ($kind === 'manifestAsKnown') {
            $canonical = [];
            foreach ($tables['md_source_observations'] as $r) {
                if ($r['requested_trade_date'] !== $selection['trade_date'] || $r['acquired_at'] > $selection['known_at']) {
                    $omitted[] = ['observation_id' => (int) $r['source_observation_id'], 'basis' => $r['requested_trade_date'] !== $selection['trade_date'] ? 'OTHER_REQUESTED_DATE' : 'OBSERVATION_AFTER_KNOWLEDGE_CUTOFF']; continue;
                }
                $value = [];
                foreach (['source_observation_id','observation_uid','parent_observation_id','run_id','acquisition_batch_id','attempt_uid','requested_trade_date','source_mode','source_name','provider','provider_symbol','provider_mapping_id','sanitized_request_identity','payload_hash','payload_ref','payload_byte_length','schema_fingerprint','adapter_version','provider_schema_version','acquired_at','outcome_state','reason_code'] as $f) $value[$f] = $r[$f];
                ksort($value, SORT_STRING); $canonical[] = $value;
            }
            usort($canonical, static function ($a, $b) { return strcmp($a['observation_uid'], $b['observation_uid']); });
            $selected = array_map('intval', array_column($canonical, 'source_observation_id'));
            $result = ['trade_date' => $selection['trade_date'], 'knowledge_cutoff' => $selection['known_at'], 'observation_count' => count($canonical), 'observations' => $canonical,
                'manifest_hash' => hash('sha256', json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION))];
        } elseif ($kind === 'outcomeRows') {
            $selected = array_map('intval', array_column($rows, 'source_observation_row_id'));
            if (count($rows) !== $selection['normalized_count'] || count($rejected) !== $selection['rejected_count']) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_ROW_MEMBERSHIP');
            $result = ['normalized_row_ids' => $selected, 'rejected_row_ids' => array_map('intval', array_column($rejected, 'source_observation_rejected_row_id'))];
        } else {
            throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_READ_KIND');
        }
        foreach ($rejected as $r) $omitted[] = ['rejected_row_id' => (int) $r['source_observation_rejected_row_id'], 'basis' => $r['reason_code']];
        return ['result' => $result, 'selected_ids' => $selected, 'omitted_rows' => $omitted,
            'empty_basis' => $selected === [] ? 'NO_MATCHING_MEMBERS_FOR_EXPLICIT_SELECTION' : null];
    }

    public static function assertValid(array $payload, array $selection): void
    {
        $p = $payload['population'] ?? [];
        if (($p['schema'] ?? null) !== 'source_observation_population_v1' || ($p['root_ids'] ?? null) !== $selection['observation_ids']) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_POPULATION_IDENTITY');
        foreach (self::TABLE_KEYS as $table => $key) {
            if (! isset($p['tables'][$table]) || ($p['population_counts'][$table] ?? null) !== count($p['tables'][$table])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_POPULATION_COUNT');
            $seen = [];
            foreach ($p['tables'][$table] as $r) {
                if (empty($r[$key]) || isset($seen[$r[$key]])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_MEMBER_IDENTITY');
                $seen[$r[$key]] = true;
            }
        }
        $obs = array_column($p['tables']['md_source_observations'], null, 'source_observation_id');
        $rows = array_column($p['tables']['md_source_observation_rows'], null, 'source_observation_row_id');
        $missingRoots = array_values(array_diff($p['root_ids'], array_map('intval', array_keys($obs))));
        if ($missingRoots !== ($p['missing_root_ids'] ?? null)) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_ROOT_MEMBERSHIP');
        foreach ($obs as $id => $r) {
            $refs = [];
            foreach (['parent_observation_id','supersedes_observation_id'] as $f) $refs[$f] = $r[$f] === null ? null : ($obs[$r[$f]] ?? null);
            ProducerSourceObservationJournal::assertValid(['observation' => $r, 'references' => $refs,
                'payload_basis' => 'PERSISTED_HASH_REFERENCE_AND_BOUNDED_REDACTED_DIAGNOSTIC',
                'scope' => 'PERSISTED_OBSERVATION_ONLY_NOT_CONSUMPTION_COMPLETENESS'], (int) $id);
        }
        foreach (['md_source_observation_rows','md_source_observation_rejected_rows','md_source_observation_identity_bindings'] as $table) foreach ($p['tables'][$table] as $r) {
            if (! isset($obs[$r['source_observation_id']])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_MEMBER_REFERENCE');
            if (isset($r['capture_observation_id']) && ! isset($obs[$r['capture_observation_id']])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_CAPTURE_REFERENCE');
            if ($table === 'md_source_observation_identity_bindings' && ! isset($rows[$r['source_observation_row_id']])) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_BINDING_REFERENCE');
            $fields = $table === 'md_source_observation_identity_bindings'
                ? ['source_observation_row_id','listing_id','provider_mapping_id','mapping_revision','effective_trade_date','recorded_at']
                : ($table === 'md_source_observation_rows'
                    ? ['capture_observation_id','source_row_ref','trade_date','source_timestamp','open_value','high_value','low_value','close_value','volume_value','adj_close_value','row_fingerprint','created_at']
                    : ['capture_observation_id','source_row_ref','trade_date','reason_code','reason_note','open_value','high_value','low_value','close_value','volume_value','adj_close_value','created_at']);
            foreach ($fields as $f) if (! array_key_exists($f, $r)) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_MEMBER_FIELD: '.$f);
        }
        foreach ($p['tables']['md_source_observation_revision_comparisons'] as $r) foreach (['prior','current'] as $side) {
            $row = $rows[$r[$side.'_source_observation_row_id']] ?? null;
            if (! $row || (int) $row['source_observation_id'] !== (int) $r[$side.'_source_observation_id']) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_COMPARISON_REFERENCE');
            $values = [];
            foreach (['open','high','low','close','volume','adj_close'] as $f) $values[$f] = $row[$f.'_value'] === null ? null : (string) $row[$f.'_value'];
            if (RunInputCaptureRepository::canonicalJson($values) !== RunInputCaptureRepository::canonicalJson(json_decode($r[$side.'_values_json'], true))) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_COMPARISON_VALUES');
        }
        if (RunInputCaptureRepository::canonicalJson(self::evaluate($p, $selection)) !== RunInputCaptureRepository::canonicalJson($payload['evaluation'] ?? null)) throw new \RuntimeException('INPUT_CAPTURE_OBSERVATION_SELECTION_CONTENT');
    }
}
