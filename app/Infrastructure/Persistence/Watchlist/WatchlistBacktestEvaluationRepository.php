<?php

namespace App\Infrastructure\Persistence\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use Illuminate\Support\Facades\DB;

class WatchlistBacktestEvaluationRepository
{
    private const KEY_FIELDS = [
        'policy_code', 'catalog_code', 'catalog_version', 'param_id',
        'eval_model', 'eval_model_hash', 'implementation_version', 'implementation_hash',
        'evidence_pipeline_version', 'evidence_pipeline_hash',
        'paramset_hash', 'from_date', 'to_date',
    ];

    private const STRING_FIELDS = [
        'policy_code', 'catalog_code', 'catalog_version', 'catalog_hash',
        'eval_model', 'eval_model_hash', 'implementation_version', 'implementation_hash',
        'evidence_pipeline_version', 'evidence_pipeline_hash',
        'paramset_hash', 'picks_hash', 'universe_hash', 'cutoffs_hash',
        'evidence_manifest_hash', 'market_data_lineage_hash', 'from_date', 'to_date',
    ];

    private const PAYLOAD_FIELDS = [
        'policy_code', 'catalog_code', 'catalog_version', 'catalog_hash',
        'param_id', 'eval_model', 'eval_model_hash', 'implementation_version', 'implementation_hash',
        'evidence_pipeline_version', 'evidence_pipeline_hash',
        'paramset_hash', 'from_date', 'to_date', 'days_covered', 'picks_count',
        'picks_hash', 'universe_count', 'universe_hash', 'cutoff_count', 'cutoffs_hash',
        'evidence_manifest_hash', 'market_data_lineage_hash',
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
            return $this->existingResult($existing, $payload, 'IDEMPOTENT');
        }

        try {
            $evalId = $this->insertRow($payload);
            return ['status' => 'INSERTED', 'eval_id' => $evalId, 'row' => $payload];
        } catch (\Throwable $e) {
            $concurrent = $this->findExisting($payload);
            if ($concurrent === null) {
                throw $e;
            }

            return $this->existingResult($concurrent, $payload, 'IDEMPOTENT');
        }
    }

    private function existingResult(array $existing, array $payload, string $status): array
    {
        $existingPayload = $this->canonicalRow($existing);
        if ($existingPayload !== $payload) {
            throw new \RuntimeException(
                'WS_BT_EVAL_IDENTITY_CONFLICT: duplicate watchlist_bt_eval identity has a different immutable payload.'
            );
        }

        return ['status' => $status, 'eval_id' => (int) $existing['eval_id'], 'row' => $payload];
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
            'evidence_pipeline_version' => \App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService::EVIDENCE_PIPELINE_VERSION,
            'evidence_pipeline_hash' => \App\Application\Watchlist\Services\WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash(),
        ];

        $canonical = [];
        foreach (self::PAYLOAD_FIELDS as $field) {
            $value = $row[$field] ?? null;
            if (in_array($field, ['param_id', 'days_covered', 'picks_count', 'universe_count', 'cutoff_count', 'periods_count', 'period_fail_count'], true)) {
                $value = $value === null ? null : (int) $value;
            } elseif (in_array($field, self::STRING_FIELDS, true)) {
                $value = (string) $value;
            } else {
                $value = $value === null ? null : round((float) $value, 6);
            }
            $canonical[$field] = $value;
        }

        return $canonical;
    }
}
