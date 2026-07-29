<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenWatchlistBacktestUniverseVolRatioPrecision extends Migration
{
    private const TABLE = 'watchlist_bt_universe_ws';
    private const COLUMN = 'vol_ratio';
    private const TARGET_COLUMN_TYPE = 'decimal(20,6)';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE) || ! Schema::hasColumn(self::TABLE, self::COLUMN)) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            // SQLite uses numeric affinity and does not enforce DECIMAL precision.
            return;
        }

        $actual = $this->mysqlColumnType();
        if ($actual === self::TARGET_COLUMN_TYPE) {
            return;
        }

        DB::statement(
            'ALTER TABLE `'.self::TABLE.'` MODIFY `'.self::COLUMN.'` DECIMAL(20,6) NULL'
        );

        $after = $this->mysqlColumnType();
        if ($after !== self::TARGET_COLUMN_TYPE) {
            throw new RuntimeException(
                'WS_C171_VOL_RATIO_PRECISION_REMEDIATION_FAILED: expected '.
                self::TARGET_COLUMN_TYPE.', actual '.($after ?: 'UNKNOWN').'.'
            );
        }
    }

    public function down(): void
    {
        // Irreversible by design. Narrowing to DECIMAL(10,6) can reject or truncate
        // immutable historical evidence rows whose vol_ratio exceeds 9999.999999.
    }

    private function mysqlColumnType(): ?string
    {
        $row = DB::table('information_schema.COLUMNS')
            ->select('COLUMN_TYPE')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', self::TABLE)
            ->where('COLUMN_NAME', self::COLUMN)
            ->first();

        if (! $row) {
            return null;
        }

        return strtolower((string) $row->COLUMN_TYPE);
    }
}
