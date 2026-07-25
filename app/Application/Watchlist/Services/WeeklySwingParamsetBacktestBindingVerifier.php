<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingParamsetBacktestBindingVerifier
{
    private const FIELD_MAP = [
        'min_dv20_idr' => 'liquidity.min_dv20_idr',
        'max_atr14_pct' => 'risk.max_atr14_pct',
        'min_vol_ratio' => 'volume.min_vol_ratio',
        'w_momentum' => 'scoring.weights.momentum',
        'w_volume' => 'scoring.weights.volume',
        'w_breakout' => 'scoring.weights.breakout',
        'w_risk' => 'scoring.weights.risk',
        'stop_atr_mult' => 'risk.stop_atr_mult',
        'min_rr' => 'risk.min_rr',
        'top_picks_target' => 'grouping.top_picks_target',
        'secondary_target' => 'grouping.secondary_target',
        'top_min_score_q' => 'grouping.top_min_score_q',
        'secondary_min_score_q' => 'grouping.secondary_min_score_q',
    ];

    public function verify(array $payload, int $btParamId, string $catalogCode): array
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            return $this->failed('WS_PARAMSET_BT_GRID_MISSING', []);
        }

        $row = DB::table('watchlist_bt_param_grid')
            ->where('param_id', $btParamId)
            ->where('policy_code', 'WS')
            ->where('catalog_code', $catalogCode)
            ->first();
        if (! $row) {
            return $this->failed('WS_PARAMSET_BT_BINDING_NOT_FOUND', []);
        }

        $row = (array) $row;
        $mismatches = [];
        foreach (self::FIELD_MAP as $column => $path) {
            $actual = $this->payloadValue($payload, $path);
            $expected = $row[$column] ?? null;
            if (! $this->equivalent($actual, $expected)) {
                $mismatches[] = [
                    'column' => $column,
                    'paramset_path' => $path,
                    'expected' => $expected,
                    'actual' => $actual,
                ];
            }
        }

        if ($mismatches !== []) {
            return $this->failed('WS_PARAMSET_BT_BINDING_MISMATCH', $mismatches);
        }

        return [
            'valid' => true,
            'reason_code' => 'WS_PARAMSET_BT_BINDING_VALID',
            'bt_param_id' => (int) $row['param_id'],
            'catalog_code' => (string) $row['catalog_code'],
            'catalog_version' => (string) ($row['catalog_version'] ?? ''),
            'catalog_hash' => (string) ($row['catalog_hash'] ?? ''),
            'row_code' => (string) ($row['row_code'] ?? ''),
            'row_hash' => (string) ($row['row_hash'] ?? ''),
            'mismatches' => [],
        ];
    }

    private function payloadValue(array $payload, string $path)
    {
        $parts = explode('.', $path);
        if (count($parts) === 2) {
            return $payload[$parts[0]][$parts[1]]['value'] ?? null;
        }
        if (count($parts) === 3 && $parts[0] === 'scoring' && $parts[1] === 'weights') {
            return $payload['scoring']['weights']['value'][$parts[2]] ?? null;
        }

        return null;
    }

    private function equivalent($actual, $expected): bool
    {
        if (is_int($actual) || is_float($actual)) {
            return is_numeric($expected) && abs((float) $actual - (float) $expected) <= 0.000001;
        }

        return $actual === $expected;
    }

    private function failed(string $reasonCode, array $mismatches): array
    {
        return [
            'valid' => false,
            'reason_code' => $reasonCode,
            'mismatches' => $mismatches,
        ];
    }
}
