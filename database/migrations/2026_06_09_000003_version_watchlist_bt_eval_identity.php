<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VersionWatchlistBtEvalIdentity extends Migration
{
    private const INDEX = 'UQ_bt_eval_policy_param_window';

    private const LEGACY_MODEL = 'LEGACY_UNVERSIONED';

    private const LEGACY_HASH = '0000000000000000000000000000000000000000';

    private const INDEX_COLUMNS = [
        'policy_code',
        'param_id',
        'eval_model',
        'paramset_hash',
        'from_date',
        'to_date',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('watchlist_bt_eval')) {
            return;
        }

        Schema::table('watchlist_bt_eval', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_eval', 'eval_model')) {
                $table->string('eval_model', 96)
                    ->default(self::LEGACY_MODEL)
                    ->after('param_id');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'paramset_hash')) {
                $table->char('paramset_hash', 40)
                    ->default(self::LEGACY_HASH)
                    ->after('eval_model');
            }
        });

        $this->synchronizeUniqueIndex();
    }

    public function down(): void
    {
        if (! Schema::hasTable('watchlist_bt_eval')) {
            return;
        }

        $this->dropUniqueIndexIfPresent();

        Schema::table('watchlist_bt_eval', function (Blueprint $table): void {
            $table->unique(
                ['policy_code', 'param_id', 'from_date', 'to_date'],
                self::INDEX
            );
        });

        Schema::table('watchlist_bt_eval', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('watchlist_bt_eval', 'paramset_hash')) {
                $columns[] = 'paramset_hash';
            }
            if (Schema::hasColumn('watchlist_bt_eval', 'eval_model')) {
                $columns[] = 'eval_model';
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
                [$database, 'watchlist_bt_eval', self::INDEX]
            );
            $existing = array_map(function ($row): string {
                return (string) $row->column_name;
            }, $rows);

            if ($existing !== [] && $existing !== self::INDEX_COLUMNS) {
                DB::statement('ALTER TABLE watchlist_bt_eval DROP INDEX '.self::INDEX);
                $existing = [];
            }
            if ($existing === []) {
                DB::statement(
                    'ALTER TABLE watchlist_bt_eval ADD UNIQUE KEY '.self::INDEX.
                    ' ('.implode(', ', self::INDEX_COLUMNS).')'
                );
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);
            DB::statement(
                'CREATE UNIQUE INDEX '.self::INDEX.
                ' ON watchlist_bt_eval ('.implode(', ', self::INDEX_COLUMNS).')'
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
                [$database, 'watchlist_bt_eval', self::INDEX]
            );
            if ((int) ($exists->aggregate ?? 0) > 0) {
                DB::statement('ALTER TABLE watchlist_bt_eval DROP INDEX '.self::INDEX);
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);
        }
    }
}
