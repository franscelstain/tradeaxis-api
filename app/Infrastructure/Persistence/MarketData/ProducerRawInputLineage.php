<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

/** Materialize actual RAW inputs before the repository discards columns for a consumer. */
final class ProducerRawInputLineage
{
    public static function consume(array $selection, callable $read): array
    {
        if (! ProducerInputScope::active()) return $read();
        $readSelection = $selection;
        if (ProducerInputScope::historical() && ($selection['read_kind'] !== 'date' || empty($selection['publication_id']))) {
            throw new \RuntimeException('INPUT_CAPTURE_RAW_HISTORICAL_BINDING_REQUIRED');
        }
        $materialized = $read();
        ProducerInputScope::rawRead($readSelection, $materialized);
        $read = static function () use ($materialized) { return $materialized; };
        $selection = ['operation' => 'raw-input-lineage/v1'] + $selection;
        $values = ProducerInputScope::inputRead('raw_history', $selection, static function () use ($selection, $read) {
            // A live projection is not an immutable historical input manifest.
            if (ProducerInputScope::historical() && ($selection['read_kind'] !== 'date' || empty($selection['publication_id']))) {
                throw new \RuntimeException('INPUT_CAPTURE_RAW_HISTORICAL_BINDING_REQUIRED');
            }
            $rows = $read(); $publications = []; $ids = []; $sealedHistory = [];
            foreach ($rows as $row) {
                $id = (int) ($row['publication_id'] ?? 0);
                if ($id <= 0) throw new \RuntimeException('INPUT_CAPTURE_RAW_PUBLICATION_REQUIRED');
                if (! isset($publications[$id])) {
                    $p = DB::table('eod_publications')->where('publication_id', $id)->first();
                    if (! $p) throw new \RuntimeException('INPUT_CAPTURE_RAW_PUBLICATION_MISSING');
                    // Candidate output hashes can change later in this producer; they are not inputs.
                    $publications[$id] = array_intersect_key((array) $p, array_flip([
                        'publication_id', 'trade_date', 'run_id', 'publication_version',
                        'supersedes_publication_id', 'previous_publication_id', 'replaced_publication_id',
                    ]));
                    if ($p->seal_state === 'SEALED') {
                        if (! preg_match('/^[a-f0-9]{64}$/i', (string) $p->bars_batch_hash)) throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HASH_REQUIRED');
                        $publications[$id]['sealed_input_bars_hash'] = $p->bars_batch_hash;
                    }
                }
                if (isset($publications[$id]['sealed_input_bars_hash'])) {
                    $history = DB::table('eod_bars_history')->where('publication_id', $id)
                        ->where('trade_date', $row['trade_date'])->where('ticker_id', $row['ticker_id'])->first();
                    if (! $history) throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HISTORY_MISSING');
                    $sealedHistory[] = (array) $history;
                }
                $ids[] = (int) ($row['source_observation_id'] ?? 0);
            }
            $source = ProducerSourceObservationPopulation::immutablePopulation($ids);
            $target = null;
            if (in_array($selection['read_kind'], ['history-copy', 'history-copy-current'], true)) {
                $target = DB::table('eod_publications')->where('publication_id', $selection['target_publication_id'])->first();
                if (! $target || (int) $target->run_id !== $selection['target_run_id'] || $target->trade_date !== $selection['trade_date']) {
                    throw new \RuntimeException('INPUT_CAPTURE_RAW_COPY_TARGET_IDENTITY');
                }
                $target = array_intersect_key((array) $target, array_flip(['publication_id','run_id','trade_date','publication_version']));
            }
            $payload = ['schema' => 'producer_raw_lineage_v1', 'raw_rows' => $rows,
                'row_count' => count($rows), 'raw_hash' => self::hash($rows),
                'publications' => array_values($publications), 'source_population' => $source, 'copy_target' => $target,
                'sealed_history_rows' => $sealedHistory, 'sealed_history_hash' => self::hash($sealedHistory),
                'empty_basis' => $rows === [] ? 'NO_RAW_ROWS_IN_EXPLICIT_READ_BOUNDARY' : null,
                'links' => self::links($rows, $source, $selection)];
            self::assertValid($payload, $selection);
            return [$payload];
        });
        ProducerInputScope::rawRetained($readSelection, $values[0]['raw_rows']);
        return $values[0]['raw_rows'];
    }

    public static function hash($value): string
    {
        return hash('sha256', RunInputCaptureRepository::canonicalJson($value));
    }

    private static function links(array $rows, array $source, array $selection): array
    {
        $links = [];
        foreach ($rows as $row) {
            $matches = []; $omitted = [];
            foreach ($source['tables']['md_source_observation_rows'] as $normalized) {
                if ((int) $normalized['source_observation_id'] !== (int) $row['source_observation_id']) continue;
                $same = (string) $normalized['trade_date'] === (string) $row['trade_date'];
                foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
                    $same = $same && is_numeric($row[$field] ?? null) && is_numeric($normalized[$field.'_value'])
                        && bccomp((string) $row[$field], (string) $normalized[$field.'_value'], 8) === 0;
                }
                $bound = false;
                foreach ($source['tables']['md_source_observation_identity_bindings'] as $binding) {
                    if ((int) $binding['source_observation_row_id'] === (int) $normalized['source_observation_row_id']
                        && (int) $binding['listing_id'] === (int) $row['listing_id']
                        && (string) $binding['effective_trade_date'] === (string) $row['trade_date']) $bound = true;
                }
                if ($same && $bound) $matches[] = (int) $normalized['source_observation_row_id'];
                else $omitted[] = ['row_id' => (int) $normalized['source_observation_row_id'], 'basis' => 'DIFFERENT_VALUES_DATE_OR_BOUND_LISTING'];
            }
            $history = ! empty($selection['publication_id']) && (string) $row['trade_date'] === $selection['trade_date'];
            $links[] = ['table' => $selection['source_table'] ?? ($history ? 'eod_bars_history' : 'eod_bars'),
                'key' => [$row['publication_id'], $row['trade_date'], $row['ticker_id'], $row['listing_id']],
                'raw_hash' => self::hash($row), 'observation_id' => (int) $row['source_observation_id'],
                // Preserve all compatible members; never invent a winning source_row_ref absent from RAW.
                'compatible_normalized_row_ids' => $matches, 'omitted_normalized_rows' => $omitted,
                'relationship_basis' => 'PERSISTED_OBSERVATION_AND_BOUND_LISTING_DATE_OHLCV'];
        }
        return $links;
    }

    public static function assertValid(array $payload, array $selection): void
    {
        $rows = $payload['raw_rows'] ?? [];
        if (($payload['schema'] ?? null) !== 'producer_raw_lineage_v1'
            || ($payload['row_count'] ?? null) !== count($rows)
            || ($payload['raw_hash'] ?? null) !== self::hash($rows)
            || ($payload['empty_basis'] ?? null) !== ($rows === [] ? 'NO_RAW_ROWS_IN_EXPLICIT_READ_BOUNDARY' : null)) {
            throw new \RuntimeException('INPUT_CAPTURE_RAW_POPULATION');
        }
        $source = $payload['source_population'];
        if (in_array($selection['read_kind'], ['history-copy', 'history-copy-current'], true)) {
            $target = $payload['copy_target'] ?? [];
            if ((int) ($target['publication_id'] ?? 0) !== $selection['target_publication_id']
                || (int) ($target['run_id'] ?? 0) !== $selection['target_run_id']
                || ($target['trade_date'] ?? null) !== $selection['trade_date'] || empty($target['publication_version'])) {
                throw new \RuntimeException('INPUT_CAPTURE_RAW_COPY_TARGET_IDENTITY');
            }
        }
        ProducerSourceObservationPopulation::assertImmutablePopulation($source);
        $publications = array_column($payload['publications'], null, 'publication_id');
        $history = $payload['sealed_history_rows'] ?? [];
        if (($payload['sealed_history_hash'] ?? null) !== self::hash($history)) throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HISTORY_HASH');
        $historyKeys = []; $expectedHistory = [];
        foreach ($history as $h) {
            $key = $h['publication_id'].'|'.$h['trade_date'].'|'.$h['ticker_id'];
            if (isset($historyKeys[$key])) throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HISTORY_DUPLICATE');
            $historyKeys[$key] = $h;
        }
        $obs = array_column($source['tables']['md_source_observations'], null, 'source_observation_id');
        $seen = []; $expectedRoots = [];
        foreach ($rows as $row) {
            foreach (['publication_id', 'run_id', 'listing_id', 'source_observation_id', 'canonicalization_version', 'price_product_code', 'quality_state'] as $field) {
                if (empty($row[$field])) throw new \RuntimeException('INPUT_CAPTURE_RAW_REQUIRED_FIELD: '.$field);
            }
            $p = $publications[$row['publication_id']] ?? null;
            if (! $p || (string) $p['trade_date'] !== (string) $row['trade_date'] || empty($p['run_id']) || empty($p['publication_version'])
                || (int) $p['run_id'] !== (int) $row['run_id']) {
                throw new \RuntimeException('INPUT_CAPTURE_RAW_PUBLICATION_IDENTITY');
            }
            if (isset($p['sealed_input_bars_hash'])) {
                if (! preg_match('/^[a-f0-9]{64}$/i', (string) $p['sealed_input_bars_hash'])) throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HASH_REQUIRED');
                $historyKey = $row['publication_id'].'|'.$row['trade_date'].'|'.$row['ticker_id']; $expectedHistory[] = $historyKey;
                // Projection creation time is execution metadata; every persisted domain field must agree.
                $h = $historyKeys[$historyKey] ?? [];
                if (self::hash(array_diff_key($row, ['created_at' => true])) !== self::hash(array_diff_key($h, ['created_at' => true]))) {
                    throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HISTORY_CONTENT');
                }
            }
            $id = (int) $row['source_observation_id']; $expectedRoots[] = $id;
            $selectionSource = ['read_kind' => 'existsAccepted', 'observation_ids' => [$id], 'source_row_ref' => null];
            if (! isset($obs[$id]) || ! ProducerSourceObservationPopulation::evaluate($source, $selectionSource)['result']) {
                throw new \RuntimeException('INPUT_CAPTURE_RAW_OBSERVATION_REFERENCE');
            }
            $key = $row['trade_date'].'|'.$row['ticker_id'];
            if (isset($seen[$key])) throw new \RuntimeException('INPUT_CAPTURE_RAW_DUPLICATE_MEMBER');
            $seen[$key] = true;
            if ($row['trade_date'] < $selection['start_date'] || $row['trade_date'] > $selection['trade_date']
                || (isset($selection['ticker_id']) && (int) $row['ticker_id'] !== $selection['ticker_id'])
                || (! empty($selection['publication_id']) && $row['trade_date'] === $selection['trade_date'] && (int) $row['publication_id'] !== $selection['publication_id'])) {
                throw new \RuntimeException('INPUT_CAPTURE_RAW_READ_BOUNDARY');
            }
        }
        $expectedRoots = array_values(array_unique($expectedRoots)); sort($expectedRoots, SORT_NUMERIC);
        $observedHistory = array_keys($historyKeys); sort($observedHistory, SORT_STRING); sort($expectedHistory, SORT_STRING);
        if ($observedHistory !== $expectedHistory) throw new \RuntimeException('INPUT_CAPTURE_RAW_SEALED_HISTORY_POPULATION');
        if ($source['root_ids'] !== $expectedRoots || $source['missing_root_ids'] !== []) throw new \RuntimeException('INPUT_CAPTURE_RAW_SOURCE_POPULATION');
        $links = self::links($rows, $source, $selection);
        foreach ($links as $link) if ($link['compatible_normalized_row_ids'] === []) throw new \RuntimeException('INPUT_CAPTURE_RAW_NORMALIZED_RELATIONSHIP');
        if (self::hash($links) !== self::hash($payload['links'] ?? null)) throw new \RuntimeException('INPUT_CAPTURE_RAW_LINK_CONTENT');
    }
}
