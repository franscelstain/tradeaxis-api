<?php

namespace App\Infrastructure\Persistence\Watchlist;

use Illuminate\Support\Facades\DB;

class WatchlistBacktestOosEvaluationRepository
{
    private const KEY_FIELDS = [
        'policy_code', 'policy_version', 'eval_model', 'param_id_best_is', 'is_eval_id',
        'from_date_is', 'to_date_is', 'from_date_oos', 'to_date_oos',
    ];
    private const PAYLOAD_FIELDS = [
        'policy_code', 'policy_version', 'eval_model', 'param_id_best_is', 'is_eval_id',
        'from_date_is', 'to_date_is', 'from_date_oos', 'to_date_oos', 'days_covered_oos',
        'picks_count_oos', 'avg_ret_net_top_oos', 'win_rate_top_oos',
        'median_ret_net_top_oos', 'p25_ret_net_top_oos', 'month_win_rate_min_oos',
    ];

    public function persist(array $row): array
    {
        $payload = $this->canonicalRow($row);
        $existing = $this->findExisting($payload);
        if ($existing !== null) {
            $existingPayload = $this->canonicalRow($existing);
            if ($existingPayload !== $payload) {
                throw new \RuntimeException('WS_BT_OOS_PROOF_MISSING: Duplicate persistence conflict for watchlist_bt_oos_eval_ws; existing payload differs.');
            }

            return ['status' => 'IDEMPOTENT', 'oos_id' => (int) $existing['oos_id'], 'row' => $payload];
        }

        $oosId = $this->insertRow($payload);

        return ['status' => 'INSERTED', 'oos_id' => $oosId, 'row' => $payload];
    }

    protected function findExisting(array $payload): ?array
    {
        $query = DB::table('watchlist_bt_oos_eval_ws');
        foreach (self::KEY_FIELDS as $field) {
            $query->where($field, $payload[$field]);
        }
        $row = $query->first();

        return $row ? (array) $row : null;
    }

    protected function insertRow(array $payload): int
    {
        return (int) DB::table('watchlist_bt_oos_eval_ws')->insertGetId($payload);
    }

    private function canonicalRow(array $row): array
    {
        $canonical = [];
        foreach (self::PAYLOAD_FIELDS as $field) {
            $value = $row[$field] ?? null;
            if (in_array($field, ['param_id_best_is', 'is_eval_id', 'days_covered_oos', 'picks_count_oos'], true)) {
                $value = $value === null ? null : (int) $value;
            } elseif (in_array($field, [
                'avg_ret_net_top_oos', 'win_rate_top_oos', 'median_ret_net_top_oos',
                'p25_ret_net_top_oos', 'month_win_rate_min_oos',
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
