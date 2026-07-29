<?php

namespace App\Infrastructure\Persistence\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC16ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC171RemediationParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class WatchlistBacktestParamGridRepository
{
    private const META_COLUMNS = [
        'policy_code', 'catalog_code', 'catalog_version', 'catalog_hash',
        'row_code', 'row_hash', 'rationale',
    ];

    private const PARAMETER_COLUMNS = [
        'min_dv20_idr', 'max_dv20_idr', 'dv20_strong_idr',
        'min_vol_ratio', 'max_vol_ratio', 'strong_vol_ratio',
        'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
        'max_signal_tick_risk_expansion_pct',
        'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
        'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
        'stop_atr_mult', 'min_rr',
        'top_picks_target', 'secondary_target',
        'top_min_score_q', 'top_max_score_total', 'secondary_min_score_q',
    ];

    private const PERSISTED_COLUMNS = [
        'policy_code', 'catalog_code', 'catalog_version', 'catalog_hash',
        'row_code', 'row_hash', 'rationale',
        'min_dv20_idr', 'max_dv20_idr', 'dv20_strong_idr',
        'min_vol_ratio', 'max_vol_ratio', 'strong_vol_ratio',
        'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
        'max_signal_tick_risk_expansion_pct',
        'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
        'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
        'stop_atr_mult', 'min_rr',
        'top_picks_target', 'secondary_target',
        'top_min_score_q', 'top_max_score_total', 'secondary_min_score_q',
        'notes',
    ];

    public function allForPolicy(string $policyCode = 'WS'): array
    {
        return $this->allForCatalog(WatchlistBacktestParamGridCatalog::CATALOG_CODE, $policyCode);
    }

    public function allForCatalog(string $catalogCode, string $policyCode = 'WS'): array
    {
        $this->assertSchema();
        $catalogCode = trim($catalogCode);
        if ($catalogCode === '') {
            throw new RuntimeException('WS_BT_R2_CATALOG_MISSING: explicit catalog_code is required.');
        }

        $rows = DB::table('watchlist_bt_param_grid')
            ->where('policy_code', $policyCode)
            ->where('catalog_code', $catalogCode)
            ->orderBy('row_code', 'asc')
            ->orderBy('param_id', 'asc')
            ->select(array_merge(['param_id'], self::PERSISTED_COLUMNS))
            ->get();

        return array_values(array_map(function ($row): array {
            return $this->castRow((array) $row);
        }, $rows->all()));
    }

    public function seedCanonical(array $rows): array
    {
        if ($rows !== [] && ! array_key_exists('catalog_code', $rows[0])) {
            $rows = WatchlistBacktestParamGridCatalog::persistenceRows();
        }

        return $this->seedCatalog($rows);
    }

    public function seedCatalog(array $rows): array
    {
        $this->assertSchema();
        $this->validateCatalog($rows);

        $catalogCode = (string) $rows[0]['catalog_code'];
        $policyCode = (string) $rows[0]['policy_code'];
        $beforeOtherCatalogs = $this->otherCatalogSnapshots($catalogCode, $policyCode);
        $inserted = 0;
        $existing = 0;
        $persisted = [];
        $snapshot = [];

        DB::transaction(function () use (
            $rows,
            $catalogCode,
            $policyCode,
            $beforeOtherCatalogs,
            &$inserted,
            &$existing,
            &$persisted,
            &$snapshot
        ): void {
            foreach ($rows as $row) {
                $matches = DB::table('watchlist_bt_param_grid')
                    ->where('policy_code', $row['policy_code'])
                    ->where('catalog_code', $row['catalog_code'])
                    ->where('row_code', $row['row_code'])
                    ->orderBy('param_id', 'asc')
                    ->get();

                if ($matches->count() > 1) {
                    throw new RuntimeException(
                        'WS_BT_R2_CATALOG_IDENTITY_CONFLICT: duplicate catalog_code + row_code identity exists.'
                    );
                }

                if ($matches->count() === 1) {
                    $existingRow = $this->castRow((array) $matches->first());
                    if ($this->canonicalPersistedPayload($existingRow) !== $this->canonicalPersistedPayload($row)) {
                        throw new RuntimeException(
                            'WS_BT_R2_CATALOG_IDENTITY_CONFLICT: immutable catalog row payload differs from the persisted row.'
                        );
                    }
                    $existing++;
                    continue;
                }

                DB::table('watchlist_bt_param_grid')->insert($this->canonicalPersistedPayload($row));
                $inserted++;
            }

            $persisted = $this->allForCatalog($catalogCode, $policyCode);
            $this->assertPersistedCatalogMatches($persisted, $rows);
            if ($beforeOtherCatalogs !== $this->otherCatalogSnapshots($catalogCode, $policyCode)) {
                throw new RuntimeException('WS_BT_R1_MUTATION_REJECTED: seeding one catalog mutated another catalog.');
            }
            $snapshot = $this->catalogSnapshot($catalogCode, $policyCode);
        });

        return [
            'status' => 'SEEDED',
            'catalog_code' => $catalogCode,
            'catalog_version' => (string) $rows[0]['catalog_version'],
            'catalog_hash' => (string) $rows[0]['catalog_hash'],
            'inserted_count' => $inserted,
            'updated_count' => 0,
            'existing_count' => $existing,
            'param_grid_count' => count($persisted),
            'param_grid_hash' => $snapshot['persisted_set_hash'],
            'ordered_row_hashes' => $snapshot['ordered_row_hashes'],
        ];
    }

    public function catalogSnapshot(string $catalogCode, string $policyCode = 'WS'): array
    {
        $rows = $this->allForCatalog($catalogCode, $policyCode);
        $catalogHashes = array_values(array_unique(array_map(function (array $row): string {
            return (string) $row['catalog_hash'];
        }, $rows)));

        return [
            'policy_code' => $policyCode,
            'catalog_code' => $catalogCode,
            'catalog_count' => count($rows),
            'catalog_hash' => count($catalogHashes) === 1 ? $catalogHashes[0] : null,
            'ordered_param_ids' => array_values(array_map(function (array $row): int {
                return (int) $row['param_id'];
            }, $rows)),
            'ordered_row_hashes' => array_values(array_map(function (array $row): string {
                return (string) $row['row_hash'];
            }, $rows)),
            'persisted_set_hash' => $this->stableHash(array_map(function (array $row): array {
                unset($row['param_id']);

                return $row;
            }, $rows)),
        ];
    }

    private function assertSchema(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            throw new RuntimeException('WS_BT_PARAM_GRID_SCHEMA_MISMATCH: watchlist_bt_param_grid table is missing.');
        }
        foreach (array_merge(['param_id'], self::PERSISTED_COLUMNS) as $column) {
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
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: catalog must not be empty.');
        }

        $identity = null;
        $this->assertKnownCatalogIdentity($rows);
        $rowCodes = [];
        $parameterPayloads = [];
        foreach ($rows as $index => $row) {
            $row = array_replace([
                'max_dv20_idr' => null,
                'max_vol_ratio' => null,
                'top_max_score_total' => null,
                'max_signal_tick_risk_expansion_pct' => null,
            ], $row);
            foreach (self::PERSISTED_COLUMNS as $column) {
                if (! array_key_exists($column, $row)) {
                    throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: missing '.$column.' at catalog index '.$index.'.');
                }
            }

            $currentIdentity = [
                (string) $row['policy_code'],
                (string) $row['catalog_code'],
                (string) $row['catalog_version'],
                (string) $row['catalog_hash'],
            ];
            $identity = $identity ?? $currentIdentity;
            if ($identity !== $currentIdentity || $currentIdentity[0] !== 'WS' || in_array('', $currentIdentity, true)) {
                throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: catalog identity must be non-empty, stable, and policy WS.');
            }

            $rowCode = trim((string) $row['row_code']);
            if ($rowCode === '' || isset($rowCodes[$rowCode])) {
                throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: row_code must be non-empty and unique inside a catalog.');
            }
            $rowCodes[$rowCode] = true;
            if ((string) $row['row_hash'] !== sha1((string) $row['catalog_code'].'|'.$rowCode)) {
                throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: row_hash does not match catalog_code + row_code.');
            }

            $this->assertParameterInvariants($row);
            $parameterHash = $this->stableHash(array_intersect_key($row, array_flip(self::PARAMETER_COLUMNS)));
            if (isset($parameterPayloads[$parameterHash])) {
                throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: duplicate canonical parameter combination inside catalog.');
            }
            $parameterPayloads[$parameterHash] = true;
        }
    }

    private function assertKnownCatalogIdentity(array $rows): void
    {
        $code = (string) ($rows[0]['catalog_code'] ?? '');
        $version = (string) ($rows[0]['catalog_version'] ?? '');
        $hash = (string) ($rows[0]['catalog_hash'] ?? '');
        $known = [
            WatchlistBacktestParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestParamGridCatalog::hash(),
                WatchlistBacktestParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestR2ParamGridCatalog::hash(),
                WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC01ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC01ParamGridCatalog::hash(),
                WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC02ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC02ParamGridCatalog::hash(),
                WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC03ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC03ParamGridCatalog::hash(),
                WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC04ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC04ParamGridCatalog::hash(),
                WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC05ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC05ParamGridCatalog::hash(),
                WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC06ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC06ParamGridCatalog::hash(),
                WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC07ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC07ParamGridCatalog::hash(),
                WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC14ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC14ParamGridCatalog::hash(),
                WatchlistBacktestC14ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC15ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC15ParamGridCatalog::hash(),
                WatchlistBacktestC15ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC16ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC16ParamGridCatalog::hash(),
                WatchlistBacktestC16ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC17ParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC17ParamGridCatalog::hash(),
                WatchlistBacktestC17ParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC171RemediationParamGridCatalog::hash(),
                WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::hash(),
                WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_COUNT,
            ],
            WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE => [
                WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_VERSION,
                WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::hash(),
                WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_COUNT,
            ],
        ];
        if (! isset($known[$code])) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: catalog_code is not an approved immutable catalog.');
        }
        [$expectedVersion, $expectedHash, $expectedCount] = $known[$code];
        if ($version !== $expectedVersion || $hash !== $expectedHash || count($rows) !== $expectedCount) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: catalog version/count/hash differs from the approved immutable definition.');
        }
    }

    private function assertParameterInvariants(array $row): void
    {
        if ((int) $row['min_dv20_idr'] < 0 || (int) $row['dv20_strong_idr'] < (int) $row['min_dv20_idr']) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: liquidity thresholds are invalid.');
        }
        if ($row['max_dv20_idr'] !== null
            && ((int) $row['max_dv20_idr'] < (int) $row['min_dv20_idr']
                || (int) $row['max_dv20_idr'] < (int) $row['dv20_strong_idr'])) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: max_dv20_idr must be >= min_dv20_idr and dv20_strong_idr.');
        }
        if ((float) $row['min_vol_ratio'] < 0 || (float) $row['strong_vol_ratio'] < (float) $row['min_vol_ratio']) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: volume thresholds are invalid.');
        }
        if ($row['max_vol_ratio'] !== null
            && ((float) $row['max_vol_ratio'] < (float) $row['min_vol_ratio']
                || (float) $row['max_vol_ratio'] < (float) $row['strong_vol_ratio'])) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: max_vol_ratio must be >= min_vol_ratio and strong_vol_ratio.');
        }
        if (! ((float) $row['min_atr14_pct'] <= (float) $row['atr_ideal_low']
            && (float) $row['atr_ideal_low'] <= (float) $row['atr_ideal_high']
            && (float) $row['atr_ideal_high'] <= (float) $row['max_atr14_pct'])) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: ATR band invariant is invalid.');
        }
        if ((float) $row['roc_lo'] >= (float) $row['roc_hi']) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: setup.roc_lo must be lower than setup.roc_hi.');
        }
        foreach (['bo_near_below_pct', 'bo_max_ext_pct', 'top_min_score_q', 'secondary_min_score_q'] as $field) {
            if ((float) $row[$field] < 0 || (float) $row[$field] > 1) {
                throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: '.$field.' must be within 0..1.');
            }
        }
        if ((float) $row['secondary_min_score_q'] > (float) $row['top_min_score_q']) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: secondary quantile must not exceed top quantile.');
        }
        if ($row['top_max_score_total'] !== null
            && ((float) $row['top_max_score_total'] < 0 || (float) $row['top_max_score_total'] > 1)) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: top_max_score_total must be within 0..1.');
        }
        if ($row['max_signal_tick_risk_expansion_pct'] !== null
            && ((float) $row['max_signal_tick_risk_expansion_pct'] < 0
                || (float) $row['max_signal_tick_risk_expansion_pct'] > 1)) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: max_signal_tick_risk_expansion_pct must be within 0..1.');
        }

        $weightTotal = (float) $row['w_momentum'] + (float) $row['w_volume']
            + (float) $row['w_breakout'] + (float) $row['w_risk'];
        if (abs($weightTotal - 1.0) > 0.000001) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: scoring weights must sum to 1.0.');
        }
        foreach (['w_momentum', 'w_volume', 'w_breakout', 'w_risk'] as $field) {
            if ((float) $row[$field] < 0 || (float) $row[$field] > 1) {
                throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: scoring weights must be within 0..1.');
            }
        }
        if ((float) $row['stop_atr_mult'] <= 0 || (float) $row['min_rr'] <= 0
            || (int) $row['top_picks_target'] <= 0 || (int) $row['secondary_target'] <= 0) {
            throw new RuntimeException('WS_BT_R2_CATALOG_INVALID: fixed execution/grouping values must be positive.');
        }
    }

    private function assertPersistedCatalogMatches(array $persisted, array $catalog): void
    {
        $persistedSet = $this->catalogSet($persisted);
        $catalogSet = $this->catalogSet($catalog);
        if ($persistedSet !== $catalogSet) {
            $catalogCode = (string) ($catalog[0]['catalog_code'] ?? '');
            $reasonCode = $catalogCode === WatchlistBacktestParamGridCatalog::CATALOG_CODE
                ? 'WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH'
                : 'WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH';
            throw new RuntimeException($reasonCode.': persisted catalog differs from the immutable catalog definition.');
        }
    }

    private function catalogSet(array $rows): array
    {
        $set = [];
        foreach ($rows as $row) {
            $set[] = $this->stableHash($this->canonicalPersistedPayload($row));
        }
        sort($set, SORT_STRING);

        return $set;
    }

    private function otherCatalogSnapshots(string $excludedCatalog, string $policyCode): array
    {
        $this->assertSchema();
        $codes = DB::table('watchlist_bt_param_grid')
            ->where('policy_code', $policyCode)
            ->where('catalog_code', '<>', $excludedCatalog)
            ->distinct()
            ->orderBy('catalog_code', 'asc')
            ->pluck('catalog_code')
            ->all();
        $snapshots = [];
        foreach ($codes as $code) {
            $snapshots[(string) $code] = $this->catalogSnapshot((string) $code, $policyCode);
        }

        return $snapshots;
    }

    private function canonicalPersistedPayload(array $row): array
    {
        $payload = [];
        foreach (self::PERSISTED_COLUMNS as $column) {
            $payload[$column] = $row[$column] ?? null;
        }

        return $this->castRow($payload, false);
    }

    private function castRow(array $value, bool $includeParamId = true): array
    {
        $row = [];
        if ($includeParamId && array_key_exists('param_id', $value)) {
            $row['param_id'] = (int) $value['param_id'];
        }
        foreach (self::PERSISTED_COLUMNS as $column) {
            $item = $value[$column] ?? null;
            if (in_array($column, ['min_dv20_idr', 'max_dv20_idr', 'dv20_strong_idr', 'top_picks_target', 'secondary_target'], true)) {
                $item = $item === null ? null : (int) $item;
            } elseif (in_array($column, self::PARAMETER_COLUMNS, true)) {
                $item = $item === null ? null : (float) $item;
            } elseif ($item !== null) {
                $item = (string) $item;
            }
            $row[$column] = $item;
        }

        return $row;
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalizeForHash($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalizeForHash($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalizeForHash($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeForHash($item);
        }

        return $value;
    }
}
