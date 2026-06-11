<?php

namespace App\Infrastructure\Persistence\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use Illuminate\Support\Facades\DB;

class WatchlistBacktestEvaluationRepository
{
    private const KEY_FIELDS = [
        'policy_code', 'catalog_code', 'catalog_version', 'param_id',
        'eval_model', 'paramset_hash', 'from_date', 'to_date',
    ];

    private const PAYLOAD_FIELDS = [
        'policy_code', 'catalog_code', 'catalog_version', 'catalog_hash',
        'param_id', 'eval_model', 'paramset_hash', 'from_date', 'to_date', 'days_covered', 'picks_count',
        'avg_ret_net_top', 'win_rate_top', 'median_ret_net_top', 'p25_ret_net_top',
        'p75_ret_net_top', 'min_ret_net_top', 'max_ret_net_top', 'periods_count',
        'period_fail_count', 'month_win_rate_min', 'month_avg_ret_net_min',
        'avg_ret_net_all', 'win_rate_all', 'median_ret_net_all', 'p25_ret_net_all',
        'p75_ret_net_all', 'min_ret_net_all', 'max_ret_net_all',
    ];

    public function persist(array $row): array
    {
        $payload = $this->canonicalRow($row);
        $existing = $this->findExisting($payload);
        if ($existing !== null) {
            $existingPayload = $this->canonicalRow($existing);
            if ($existingPayload !== $payload) {
                throw new \RuntimeException(
                    'WS_BT_EVAL_IDENTITY_CONFLICT: duplicate watchlist_bt_eval identity has a different immutable payload.'
                );
            }

            return ['status' => 'IDEMPOTENT', 'eval_id' => (int) $existing['eval_id'], 'row' => $payload];
        }

        $evalId = $this->insertRow($payload);

        return ['status' => 'INSERTED', 'eval_id' => $evalId, 'row' => $payload];
    }

    protected function findExisting(array $payload): ?array
    {
        $query = DB::table('watchlist_bt_eval');
        foreach (self::KEY_FIELDS as $field) {
            $query->where($field, $payload[$field]);
        }
        $row = $query->first();

        return $row ? (array) $row : null;
    }

    protected function insertRow(array $payload): int
    {
        return (int) DB::table('watchlist_bt_eval')->insertGetId($payload);
    }

    private function canonicalRow(array $row): array
    {
        $row += [
            'catalog_code' => WatchlistBacktestParamGridCatalog::CATALOG_CODE,
            'catalog_version' => WatchlistBacktestParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestParamGridCatalog::hash(),
        ];

        $canonical = [];
        foreach (self::PAYLOAD_FIELDS as $field) {
            $value = $row[$field] ?? null;
            if (in_array($field, ['param_id', 'days_covered', 'picks_count', 'periods_count', 'period_fail_count'], true)) {
                $value = $value === null ? null : (int) $value;
            } elseif (! in_array($field, [
                'policy_code', 'catalog_code', 'catalog_version', 'catalog_hash',
                'eval_model', 'paramset_hash', 'from_date', 'to_date',
            ], true)) {
                $value = $value === null ? null : round((float) $value, 6);
            } else {
                $value = (string) $value;
            }
            $canonical[$field] = $value;
        }

        return $canonical;
    }
}
