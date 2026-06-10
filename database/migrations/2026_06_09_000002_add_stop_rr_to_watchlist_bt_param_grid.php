<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStopRrToWatchlistBtParamGrid extends Migration
{
    private const INDEX = 'UQ_bt_grid_policy_payload';

    private const INDEX_COLUMNS = [
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

    public function up(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            return;
        }

        Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_param_grid', 'stop_atr_mult')) {
                $table->decimal('stop_atr_mult', 10, 6)
                    ->default(1.500000)
                    ->after('w_risk');
            }
            if (! Schema::hasColumn('watchlist_bt_param_grid', 'min_rr')) {
                $table->decimal('min_rr', 10, 6)
                    ->default(1.500000)
                    ->after('stop_atr_mult');
            }
        });

        $this->synchronizeUniqueIndex();
    }

    public function down(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            return;
        }

        $this->dropUniqueIndexIfPresent();

        Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('watchlist_bt_param_grid', 'min_rr')) {
                $columns[] = 'min_rr';
            }
            if (Schema::hasColumn('watchlist_bt_param_grid', 'stop_atr_mult')) {
                $columns[] = 'stop_atr_mult';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    private function synchronizeUniqueIndex(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $database = DB::connection()->getDatabaseName();
            $rows = DB::select(
                'SELECT COLUMN_NAME AS column_name
                 FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND INDEX_NAME = ?
                 ORDER BY SEQ_IN_INDEX ASC',
                [$database, 'watchlist_bt_param_grid', self::INDEX]
            );
            $existing = array_map(function ($row): string {
                return (string) $row->column_name;
            }, $rows);

            if ($existing !== [] && $existing !== self::INDEX_COLUMNS) {
                DB::statement('ALTER TABLE watchlist_bt_param_grid DROP INDEX '.self::INDEX);
                $existing = [];
            }
            if ($existing === []) {
                DB::statement(
                    'ALTER TABLE watchlist_bt_param_grid ADD UNIQUE KEY '.self::INDEX.
                    ' ('.implode(', ', self::INDEX_COLUMNS).')'
                );
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement(
                'CREATE UNIQUE INDEX IF NOT EXISTS '.self::INDEX.
                ' ON watchlist_bt_param_grid ('.implode(', ', self::INDEX_COLUMNS).')'
            );
        }
    }

    private function dropUniqueIndexIfPresent(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $database = DB::connection()->getDatabaseName();
            $exists = DB::selectOne(
                'SELECT COUNT(*) AS aggregate
                 FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = ?
                   AND INDEX_NAME = ?',
                [$database, 'watchlist_bt_param_grid', self::INDEX]
            );
            if ((int) ($exists->aggregate ?? 0) > 0) {
                DB::statement('ALTER TABLE watchlist_bt_param_grid DROP INDEX '.self::INDEX);
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);
        }
    }
}
