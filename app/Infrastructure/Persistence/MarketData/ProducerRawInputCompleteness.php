<?php

namespace App\Infrastructure\Persistence\MarketData;

/** Independent projection oracle: consumes the original materialization, never the lineage builder's result. */
final class ProducerRawInputCompleteness
{
    public static function projection(array $rows, array $selection): array
    {
        $kind = $selection['read_kind'];
        if ($kind === 'window') {
            $groups = [];
            foreach ($rows as $row) $groups[$row['ticker_id']][] = $row;
            foreach ($groups as &$group) usort($group, static function ($a, $b) { return strcmp($a['trade_date'], $b['trade_date']); });
            unset($group);
            return $groups;
        }
        if ($kind === 'atr') {
            usort($rows, static function ($a, $b) { return strcmp($a['trade_date'], $b['trade_date']); });
            return array_map(static function ($row) {
                return ['trade_date' => (string) $row['trade_date'], 'high' => $row['high'], 'low' => $row['low'], 'close' => $row['close']];
            }, $rows);
        }
        if ($kind === 'date') {
            $result = [];
            foreach ($rows as $row) {
                if (isset($result[$row['ticker_id']])) throw new \RuntimeException('INPUT_CAPTURE_RAW_DUPLICATE_PROJECTION_KEY');
                $result[$row['ticker_id']] = $row;
            }
            return $result;
        }
        if (! in_array($kind, ['history-copy', 'history-copy-current'], true)) throw new \RuntimeException('INPUT_CAPTURE_RAW_UNKNOWN_PROJECTION');
        foreach ($rows as &$row) {
            $row['publication_id'] = (int) $selection['target_publication_id'];
            $row['run_id'] = (int) $selection['target_run_id'];
            // C1/D005: execution timestamp only; every other RAW field remains in the comparison.
            unset($row['created_at']);
        }
        unset($row);
        usort($rows, static function ($a, $b) { return $a['ticker_id'] <=> $b['ticker_id']; });
        return $rows;
    }

    public static function observed(array $rows, array $selection): array
    {
        if (in_array($selection['read_kind'], ['history-copy', 'history-copy-current'], true)) {
            foreach ($rows as &$row) {
                unset($row['created_at']);
                // Database drivers may return integer columns as decimal strings.
                foreach (['publication_id', 'run_id'] as $field) {
                    if (! preg_match('/^[0-9]+$/D', (string) ($row[$field] ?? ''))) throw new \RuntimeException('INPUT_CAPTURE_RAW_COPY_IDENTITY_TYPE');
                    $row[$field] = (int) $row[$field];
                }
            }
            unset($row);
            usort($rows, static function ($a, $b) { return $a['ticker_id'] <=> $b['ticker_id']; });
        }
        return $rows;
    }

    public static function assertSamePopulation(array $expected, array $actual, string $surface): void
    {
        if (RunInputCaptureRepository::canonicalJson($expected) !== RunInputCaptureRepository::canonicalJson($actual)) {
            throw new \RuntimeException('INPUT_CAPTURE_RAW_'.$surface.'_MISMATCH');
        }
    }

    public function missing(array $captures, RunInputCaptureRepository $repository): array
    {
        $groups = []; $missing = [];
        foreach ($captures as $capture) {
            $p = $repository->verify($capture); $s = $p['selection_context'];
            $op = $s['operation'];
            if (! in_array($op, ['raw-input-read-population/v1', 'raw-input-lineage/v1', 'raw-input-projection-audit/v1'], true)) continue;
            unset($s['operation']);
            $key = $capture['stage_code'].'|'.RunInputCaptureRepository::canonicalJson($s);
            $requiredComponent = $op === 'raw-input-projection-audit/v1' ? 'completion' : 'raw_history';
            if (isset($groups[$key][$op]) || $capture['component_key'] !== $requiredComponent) $missing[] = 'raw_history.duplicate_or_wrong_component.'.$key;
            $groups[$key][$op] = [$capture, $p];
        }
        if ($groups === []) $missing[] = 'raw_history.no_consumption_population';
        foreach ($groups as $key => $group) {
            try {
                if (count($group) !== 3) throw new \RuntimeException('INPUT_CAPTURE_RAW_COMPLETENESS_MEMBER_MISSING');
                [$read, $rp] = $group['raw-input-read-population/v1'];
                [$lineage, $lp] = $group['raw-input-lineage/v1'];
                [$audit, $ap] = $group['raw-input-projection-audit/v1'];
                $selection = $rp['selection_context']; $receipt = $ap['rows'][0] ?? [];
                if (count($rp['rows']) !== 1 || ! isset($rp['rows'][0]['raw_rows']) || ! is_array($rp['rows'][0]['raw_rows'])
                    || ($rp['rows'][0]['empty_basis'] ?? null) !== ($rp['rows'][0]['raw_rows'] === [] ? 'NO_RAW_ROWS_IN_EXPLICIT_READ_BOUNDARY' : null)
                    || count($lp['rows']) !== 1 || count($ap['rows']) !== 1) throw new \RuntimeException('INPUT_CAPTURE_RAW_READ_POPULATION');
                self::assertSamePopulation($rp['rows'][0]['raw_rows'], $lp['rows'][0]['raw_rows'] ?? [], 'RETAINED');
                foreach (['read' => $read, 'lineage' => $lineage] as $name => $row) {
                    $ref = $receipt[$name.'_ref'] ?? [];
                    foreach (['run_id', 'stage_code', 'component_key', 'slot_hash', 'payload_hash'] as $field) {
                        if (! array_key_exists($field, $ref) || ($field === 'run_id' ? (string) $ref[$field] !== (string) $row[$field] : $ref[$field] !== $row[$field])) throw new \RuntimeException('INPUT_CAPTURE_RAW_COMPLETENESS_REFERENCE');
                    }
                }
                if (($receipt['projection_hash'] ?? null) !== ProducerRawInputLineage::hash(self::projection($rp['rows'][0]['raw_rows'], $selection))) {
                    throw new \RuntimeException('INPUT_CAPTURE_RAW_PROJECTION_MISMATCH');
                }
            } catch (\Throwable $e) { $missing[] = 'raw_history.'.$key.'.'.$e->getMessage(); }
        }
        return $missing;
    }
}
