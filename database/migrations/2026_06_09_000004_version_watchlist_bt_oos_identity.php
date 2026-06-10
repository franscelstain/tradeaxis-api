<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VersionWatchlistBtOosIdentity extends Migration
{
    private const INDEX = 'UQ_bt_oos_policy_param_windows';

    private const INDEX_COLUMNS = [
        'policy_code',
        'policy_version',
        'eval_model',
        'param_id_best_is',
        'is_eval_id',
        'from_date_is',
        'to_date_is',
        'from_date_oos',
        'to_date_oos',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('watchlist_bt_oos_eval_ws')) {
            return;
        }

        $this->synchronizeUniqueIndex(self::INDEX_COLUMNS);
    }

    public function down(): void
    {
        if (! Schema::hasTable('watchlist_bt_oos_eval_ws')) {
            return;
        }

        $legacy = array_values(array_filter(self::INDEX_COLUMNS, function (string $column): bool {
            return $column !== 'is_eval_id';
        }));
        $this->synchronizeUniqueIndex($legacy);
    }

    private function synchronizeUniqueIndex(array $columns): void
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
                [$database, 'watchlist_bt_oos_eval_ws', self::INDEX]
            );
            $existing = array_map(function ($row): string {
                return (string) $row->column_name;
            }, $rows);

            if ($existing !== [] && $existing !== $columns) {
                DB::statement('ALTER TABLE watchlist_bt_oos_eval_ws DROP INDEX '.self::INDEX);
                $existing = [];
            }
            if ($existing === []) {
                DB::statement(
                    'ALTER TABLE watchlist_bt_oos_eval_ws ADD UNIQUE KEY '.self::INDEX.
                    ' ('.implode(', ', $columns).')'
                );
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);
            DB::statement(
                'CREATE UNIQUE INDEX '.self::INDEX.
                ' ON watchlist_bt_oos_eval_ws ('.implode(', ', $columns).')'
            );
        }
    }
}
