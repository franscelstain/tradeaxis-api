<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWatchlistBacktestCatalogIdentityAndR2EntryQuality extends Migration
{
    private const R1_CATALOG_CODE = 'WS_BT_GRID_BOOTSTRAP_2026_06';
    private const R1_CATALOG_HASH = '9da8b0983c57bde1ce0a1fbf1c119756f8af431c';

    public function up(): void
    {
        if (Schema::hasTable('watchlist_bt_param_grid')) {
            Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'catalog_code')) {
                    $table->string('catalog_code', 64)->default(self::R1_CATALOG_CODE)->after('policy_code');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'catalog_version')) {
                    $table->string('catalog_version', 16)->default('R1')->after('catalog_code');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'catalog_hash')) {
                    $table->char('catalog_hash', 40)->default(self::R1_CATALOG_HASH)->after('catalog_version');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'row_code')) {
                    $table->string('row_code', 64)->nullable()->after('catalog_hash');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'row_hash')) {
                    $table->char('row_hash', 40)->nullable()->after('row_code');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'rationale')) {
                    $table->text('rationale')->nullable()->after('row_hash');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'dv20_strong_idr')) {
                    $table->unsignedBigInteger('dv20_strong_idr')->default(5000000000)->after('min_dv20_idr');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'strong_vol_ratio')) {
                    $table->decimal('strong_vol_ratio', 10, 6)->default(2.500000)->after('min_vol_ratio');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'min_atr14_pct')) {
                    $table->decimal('min_atr14_pct', 10, 6)->default(0.020000)->after('strong_vol_ratio');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'atr_ideal_low')) {
                    $table->decimal('atr_ideal_low', 10, 6)->default(0.035000)->after('max_atr14_pct');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'atr_ideal_high')) {
                    $table->decimal('atr_ideal_high', 10, 6)->default(0.075000)->after('atr_ideal_low');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'roc_lo')) {
                    $table->decimal('roc_lo', 10, 6)->default(0.020000)->after('atr_ideal_high');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'roc_hi')) {
                    $table->decimal('roc_hi', 10, 6)->default(0.150000)->after('roc_lo');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'mom_roc20_soft_min')) {
                    $table->decimal('mom_roc20_soft_min', 10, 6)->default(0.000000)->after('roc_hi');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'bo_near_below_pct')) {
                    $table->decimal('bo_near_below_pct', 10, 6)->default(0.020000)->after('mom_roc20_soft_min');
                }
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'bo_max_ext_pct')) {
                    $table->decimal('bo_max_ext_pct', 10, 6)->default(0.050000)->after('bo_near_below_pct');
                }
            });

            $this->backfillR1GridIdentity();
            $this->enforceNotNullCatalogColumns();
            $this->dropIndexIfExists('watchlist_bt_param_grid', 'UQ_bt_grid_policy_payload');
            $this->createIndexIfMissing(
                'watchlist_bt_param_grid',
                'UQ_bt_grid_catalog_row',
                'CREATE UNIQUE INDEX UQ_bt_grid_catalog_row ON watchlist_bt_param_grid (policy_code, catalog_code, row_code)'
            );
            $this->createIndexIfMissing(
                'watchlist_bt_param_grid',
                'IDX_bt_grid_catalog',
                'CREATE INDEX IDX_bt_grid_catalog ON watchlist_bt_param_grid (policy_code, catalog_code, param_id)'
            );
        }

        if (Schema::hasTable('watchlist_bt_eval')) {
            Schema::table('watchlist_bt_eval', function (Blueprint $table): void {
                if (! Schema::hasColumn('watchlist_bt_eval', 'catalog_code')) {
                    $table->string('catalog_code', 64)->default(self::R1_CATALOG_CODE)->after('policy_code');
                }
                if (! Schema::hasColumn('watchlist_bt_eval', 'catalog_version')) {
                    $table->string('catalog_version', 16)->default('R1')->after('catalog_code');
                }
                if (! Schema::hasColumn('watchlist_bt_eval', 'catalog_hash')) {
                    $table->char('catalog_hash', 40)->default(self::R1_CATALOG_HASH)->after('catalog_version');
                }
            });

            DB::table('watchlist_bt_eval')
                ->where(function ($query): void {
                    $query->whereNull('catalog_code')->orWhere('catalog_code', '');
                })
                ->update([
                    'catalog_code' => self::R1_CATALOG_CODE,
                    'catalog_version' => 'R1',
                    'catalog_hash' => self::R1_CATALOG_HASH,
                ]);
            $this->dropIndexIfExists('watchlist_bt_eval', 'UQ_bt_eval_policy_param_window');
            $this->createIndexIfMissing(
                'watchlist_bt_eval',
                'UQ_bt_eval_catalog_param_window',
                'CREATE UNIQUE INDEX UQ_bt_eval_catalog_param_window ON watchlist_bt_eval ' .
                '(policy_code, catalog_code, catalog_version, param_id, eval_model, paramset_hash, from_date, to_date)'
            );
            $this->createIndexIfMissing(
                'watchlist_bt_eval',
                'IDX_bt_eval_catalog_rank',
                'CREATE INDEX IDX_bt_eval_catalog_rank ON watchlist_bt_eval ' .
                '(policy_code, catalog_code, avg_ret_net_top, median_ret_net_top, month_win_rate_min, p25_ret_net_top, win_rate_top)'
            );
        }
    }

    public function down(): void
    {
        $this->assertRollbackHasOnlyR1Data();

        if (Schema::hasTable('watchlist_bt_eval')) {
            $this->dropIndexIfExists('watchlist_bt_eval', 'IDX_bt_eval_catalog_rank');
            $this->dropIndexIfExists('watchlist_bt_eval', 'UQ_bt_eval_catalog_param_window');
            Schema::table('watchlist_bt_eval', function (Blueprint $table): void {
                foreach (['catalog_hash', 'catalog_version', 'catalog_code'] as $column) {
                    if (Schema::hasColumn('watchlist_bt_eval', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
            $this->createIndexIfMissing(
                'watchlist_bt_eval',
                'UQ_bt_eval_policy_param_window',
                'CREATE UNIQUE INDEX UQ_bt_eval_policy_param_window ON watchlist_bt_eval ' .
                '(policy_code, param_id, eval_model, paramset_hash, from_date, to_date)'
            );
        }

        if (Schema::hasTable('watchlist_bt_param_grid')) {
            $this->dropIndexIfExists('watchlist_bt_param_grid', 'IDX_bt_grid_catalog');
            $this->dropIndexIfExists('watchlist_bt_param_grid', 'UQ_bt_grid_catalog_row');
            Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
                foreach ([
                    'bo_max_ext_pct', 'bo_near_below_pct', 'mom_roc20_soft_min', 'roc_hi', 'roc_lo',
                    'atr_ideal_high', 'atr_ideal_low', 'min_atr14_pct', 'strong_vol_ratio', 'dv20_strong_idr',
                    'rationale', 'row_hash', 'row_code', 'catalog_hash', 'catalog_version', 'catalog_code',
                ] as $column) {
                    if (Schema::hasColumn('watchlist_bt_param_grid', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
            $this->createIndexIfMissing(
                'watchlist_bt_param_grid',
                'UQ_bt_grid_policy_payload',
                'CREATE UNIQUE INDEX UQ_bt_grid_policy_payload ON watchlist_bt_param_grid ' .
                '(policy_code, min_dv20_idr, max_atr14_pct, min_vol_ratio, w_momentum, w_volume, ' .
                'w_breakout, w_risk, stop_atr_mult, min_rr, top_picks_target, secondary_target, ' .
                'top_min_score_q, secondary_min_score_q)'
            );
        }
    }

    private function backfillR1GridIdentity(): void
    {
        $rows = DB::table('watchlist_bt_param_grid')
            ->where('catalog_code', self::R1_CATALOG_CODE)
            ->orderBy('param_id')
            ->get();
        foreach ($rows as $row) {
            $value = (array) $row;
            $notes = (string) ($value['notes'] ?? '');
            $prefix = self::R1_CATALOG_CODE.'_';
            $rowCode = strpos($notes, $prefix) === 0
                ? substr($notes, strlen($prefix))
                : sprintf('R1_ROW_%02d', (int) $value['param_id']);
            $maxAtr = (float) ($value['max_atr14_pct'] ?? 0.12);
            $idealHigh = min(0.075, $maxAtr);
            $idealLow = min(0.035, $idealHigh);

            DB::table('watchlist_bt_param_grid')
                ->where('param_id', (int) $value['param_id'])
                ->update([
                    'catalog_code' => self::R1_CATALOG_CODE,
                    'catalog_version' => 'R1',
                    'catalog_hash' => self::R1_CATALOG_HASH,
                    'row_code' => $rowCode,
                    'row_hash' => sha1(self::R1_CATALOG_CODE.'|'.$rowCode),
                    'rationale' => 'Immutable R1 bootstrap row preserved from the original canonical catalog.',
                    'dv20_strong_idr' => 5000000000,
                    'strong_vol_ratio' => 2.500000,
                    'min_atr14_pct' => 0.020000,
                    'atr_ideal_low' => $idealLow,
                    'atr_ideal_high' => $idealHigh,
                    'roc_lo' => 0.020000,
                    'roc_hi' => 0.150000,
                    'mom_roc20_soft_min' => 0.000000,
                    'bo_near_below_pct' => 0.020000,
                    'bo_max_ext_pct' => 0.050000,
                ]);
        }
    }

    private function enforceNotNullCatalogColumns(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            'ALTER TABLE watchlist_bt_param_grid '
            .'MODIFY catalog_code VARCHAR(64) NOT NULL, '
            .'MODIFY catalog_version VARCHAR(16) NOT NULL, '
            .'MODIFY catalog_hash CHAR(40) NOT NULL, '
            .'MODIFY row_code VARCHAR(64) NOT NULL, '
            .'MODIFY row_hash CHAR(40) NOT NULL, '
            .'MODIFY rationale TEXT NOT NULL'
        );
    }

    private function assertRollbackHasOnlyR1Data(): void
    {
        foreach (['watchlist_bt_param_grid', 'watchlist_bt_eval'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'catalog_code')) {
                continue;
            }
            if (DB::table($table)->where('catalog_code', '<>', self::R1_CATALOG_CODE)->exists()) {
                throw new RuntimeException(
                    'WS_BT_R1_MUTATION_REJECTED: rollback would erase non-R1 catalog identity/evidence.'
                );
            }
        }
    }

    private function createIndexIfMissing(string $table, string $index, string $sql): void
    {
        if (! $this->indexExists($table, $index)) {
            DB::statement($sql);
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX '.$index);
        } else {
            DB::statement('ALTER TABLE '.$table.' DROP INDEX '.$index);
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('".$table."')");
            foreach ($rows as $row) {
                if ((string) ((array) $row)['name'] === $index) {
                    return true;
                }
            }

            return false;
        }

        $database = DB::connection()->getDatabaseName();
        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
}
