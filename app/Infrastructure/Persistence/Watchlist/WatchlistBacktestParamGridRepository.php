<?php

namespace App\Infrastructure\Persistence\Watchlist;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class WatchlistBacktestParamGridRepository
{
    private const PAYLOAD_COLUMNS = [
        'policy_code',
        'min_dv20_idr',
        'max_atr14_pct',
        'min_vol_ratio',
        'w_momentum',
        'w_volume',
        'w_breakout',
        'w_risk',
        'stop_atr_mult',
        'min_rr',
        'top_picks_target',
        'secondary_target',
        'top_min_score_q',
        'secondary_min_score_q',
    ];

    public function allForPolicy(string $policyCode = 'WS'): array
    {
        $this->assertSchema();
        $rows = DB::table('watchlist_bt_param_grid')
            ->where('policy_code', $policyCode)
            ->orderBy('param_id', 'asc')
            ->select(array_merge(['param_id'], self::PAYLOAD_COLUMNS, ['notes']))
            ->get();

        return array_values(array_map(function ($row): array {
            $value = (array) $row;

            return [
                'param_id' => (int) $value['param_id'],
                'policy_code' => (string) $value['policy_code'],
                'min_dv20_idr' => (int) $value['min_dv20_idr'],
                'max_atr14_pct' => (float) $value['max_atr14_pct'],
                'min_vol_ratio' => (float) $value['min_vol_ratio'],
                'w_momentum' => (float) $value['w_momentum'],
                'w_volume' => (float) $value['w_volume'],
                'w_breakout' => (float) $value['w_breakout'],
                'w_risk' => (float) $value['w_risk'],
                'stop_atr_mult' => (float) $value['stop_atr_mult'],
                'min_rr' => (float) $value['min_rr'],
                'top_picks_target' => (int) $value['top_picks_target'],
                'secondary_target' => (int) $value['secondary_target'],
                'top_min_score_q' => (float) $value['top_min_score_q'],
                'secondary_min_score_q' => (float) $value['secondary_min_score_q'],
                'notes' => $value['notes'] !== null ? (string) $value['notes'] : null,
            ];
        }, $rows->all()));
    }

    public function seedCanonical(array $rows): array
    {
        $this->assertSchema();
        $this->validateCatalog($rows);
        $inserted = 0;
        $updated = 0;
        $existing = 0;

        DB::transaction(function () use ($rows, &$inserted, &$updated, &$existing): void {
            foreach ($rows as $row) {
                $query = DB::table('watchlist_bt_param_grid');
                foreach (self::PAYLOAD_COLUMNS as $column) {
                    $query->where($column, $row[$column]);
                }

                $matches = $query->orderBy('param_id', 'asc')->get();
                if ($matches->count() > 1) {
                    throw new RuntimeException(
                        'WS_BT_PARAM_GRID_DUPLICATE_CONFLICT: existing database contains duplicate canonical payload rows.'
                    );
                }

                if ($matches->count() === 1) {
                    $match = (array) $matches->first();
                    if ((string) ($match['notes'] ?? '') !== (string) ($row['notes'] ?? '')) {
                        DB::table('watchlist_bt_param_grid')
                            ->where('param_id', (int) $match['param_id'])
                            ->update(['notes' => $row['notes']]);
                        $updated++;
                    } else {
                        $existing++;
                    }
                    continue;
                }

                DB::table('watchlist_bt_param_grid')->insert($row);
                $inserted++;
            }
        });

        $persisted = $this->allForPolicy('WS');
        $this->assertPersistedCatalogMatches($persisted, $rows);

        return [
            'status' => 'SEEDED',
            'inserted_count' => $inserted,
            'updated_count' => $updated,
            'existing_count' => $existing,
            'param_grid_count' => count($persisted),
            'param_grid_hash' => $this->stableHash($persisted),
        ];
    }

    private function assertSchema(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            throw new RuntimeException('WS_BT_PARAM_GRID_SCHEMA_MISMATCH: watchlist_bt_param_grid table is missing.');
        }
        foreach (array_merge(['param_id'], self::PAYLOAD_COLUMNS, ['notes']) as $column) {
            if (! Schema::hasColumn('watchlist_bt_param_grid', $column)) {
                throw new RuntimeException(
                    'WS_BT_PARAM_GRID_SCHEMA_MISMATCH: watchlist_bt_param_grid is missing column '.$column.'.'
                );
            }
        }
    }

    private function validateCatalog(array $rows): void
    {
        if ($rows === []) {
            throw new RuntimeException('WS_BT_PARAM_GRID_CATALOG_EMPTY: canonical parameter grid must not be empty.');
        }

        $keys = [];
        foreach ($rows as $index => $row) {
            foreach (array_merge(self::PAYLOAD_COLUMNS, ['notes']) as $column) {
                if (! array_key_exists($column, $row)) {
                    throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: missing '.$column.' at catalog index '.$index.'.');
                }
            }
            if ((string) $row['policy_code'] !== 'WS') {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: policy_code must be WS.');
            }
            if ((int) $row['min_dv20_idr'] < 0
                || (float) $row['max_atr14_pct'] <= 0
                || (float) $row['max_atr14_pct'] > 1
                || (float) $row['min_vol_ratio'] < 0) {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: guard thresholds are outside canonical units.');
            }

            $weightTotal = (float) $row['w_momentum']
                + (float) $row['w_volume']
                + (float) $row['w_breakout']
                + (float) $row['w_risk'];
            if (abs($weightTotal - 1.0) > 0.000001) {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: scoring weights must sum to 1.0.');
            }
            foreach (['w_momentum', 'w_volume', 'w_breakout', 'w_risk'] as $weight) {
                if ((float) $row[$weight] < 0 || (float) $row[$weight] > 1) {
                    throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: scoring weights must be within 0..1.');
                }
            }

            $topQ = (float) $row['top_min_score_q'];
            $secondaryQ = (float) $row['secondary_min_score_q'];
            if ($topQ < 0 || $topQ > 1 || $secondaryQ < 0 || $secondaryQ > 1 || $topQ < $secondaryQ) {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: grouping quantiles are invalid.');
            }
            if ((float) $row['stop_atr_mult'] <= 0 || (float) $row['min_rr'] <= 0) {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: stop_atr_mult and min_rr must be positive.');
            }
            if ((int) $row['top_picks_target'] <= 0 || (int) $row['secondary_target'] <= 0) {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: grouping targets must be positive integers.');
            }

            $key = $this->stableHash(array_intersect_key($row, array_flip(self::PAYLOAD_COLUMNS)));
            if (isset($keys[$key])) {
                throw new RuntimeException('WS_BT_PARAM_GRID_INVALID: canonical catalog contains duplicate payload rows.');
            }
            $keys[$key] = true;
        }
    }

    private function assertPersistedCatalogMatches(array $persisted, array $catalog): void
    {
        $persistedSet = $this->catalogSet($persisted);
        $catalogSet = $this->catalogSet($catalog);

        if ($persistedSet !== $catalogSet) {
            throw new RuntimeException(
                'WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH: database WS grid must match the canonical catalog exactly.'
            );
        }
    }

    private function catalogSet(array $rows): array
    {
        $set = [];
        foreach ($rows as $row) {
            $payload = [];
            foreach (array_merge(self::PAYLOAD_COLUMNS, ['notes']) as $column) {
                $payload[$column] = $row[$column] ?? null;
            }
            $set[] = $this->stableHash($payload);
        }

        sort($set, SORT_STRING);

        return $set;
    }

    private function stableHash(array $payload): string
    {
        ksort($payload, SORT_STRING);

        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
