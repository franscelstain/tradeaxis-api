<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefineTradingStatusExpectedBarPolicy extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        $this->ensureExpectedBarPolicyColumn();
        $this->seedCanonicalEventTypes();
        $this->normalizeLegacyLongSuspensionEvents();
        $this->dropLegacyCoveragePolicyColumn();
    }

    public function down()
    {
        if (! Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        if (! $this->hasColumn('market_data_trading_status_event_types', 'coverage_policy')) {
            Schema::table('market_data_trading_status_event_types', function (Blueprint $table) {
                $table->string('coverage_policy', 32)->nullable()->after('transition_type');
            });
        }

        DB::table('market_data_trading_status_event_types')->update([
            'coverage_policy' => DB::raw("CASE expected_bar_policy WHEN 'BAR_NOT_REQUIRED' THEN 'EXCLUDE' WHEN 'BAR_REQUIRED_WITH_RISK' THEN 'INCLUDE_WITH_RISK' ELSE 'INCLUDE' END"),
        ]);
    }

    private function ensureExpectedBarPolicyColumn(): void
    {
        if (! $this->hasColumn('market_data_trading_status_event_types', 'expected_bar_policy')) {
            Schema::table('market_data_trading_status_event_types', function (Blueprint $table) {
                $table->string('expected_bar_policy', 32)->nullable()->after('transition_type');
            });
        }

        if ($this->hasColumn('market_data_trading_status_event_types', 'coverage_policy')) {
            DB::table('market_data_trading_status_event_types')->update([
                'expected_bar_policy' => DB::raw("CASE coverage_policy WHEN 'EXCLUDE' THEN 'BAR_NOT_REQUIRED' WHEN 'INCLUDE_WITH_RISK' THEN 'BAR_REQUIRED_WITH_RISK' WHEN 'INCLUDE' THEN 'BAR_REQUIRED' ELSE COALESCE(expected_bar_policy, 'BAR_REQUIRED') END"),
            ]);
        }

        DB::table('market_data_trading_status_event_types')
            ->where(function ($query) {
                $query->whereNull('expected_bar_policy')->orWhere('expected_bar_policy', '');
            })
            ->update(['expected_bar_policy' => 'BAR_REQUIRED']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `market_data_trading_status_event_types` MODIFY `expected_bar_policy` VARCHAR(32) NOT NULL');
        }

        if (! $this->hasIndex('market_data_trading_status_event_types', 'idx_md_status_types_expected_bar_policy')) {
            Schema::table('market_data_trading_status_event_types', function (Blueprint $table) {
                $table->index(['expected_bar_policy'], 'idx_md_status_types_expected_bar_policy');
            });
        }
    }

    private function seedCanonicalEventTypes(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ([
            ['event_type_code' => 'SUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'IDX announces a suspension start; EOD bar is not required until UNSUSPENDED.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SUSPENSION_OBSERVED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'OBSERVED', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Source snapshot shows ticker is still suspended, including IDX long-suspension lists; this is not a suspension start date.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'UNSUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SUSPENSION', 'description' => 'IDX reopens ticker trading; EOD bar becomes required again from the effective trade date.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SPECIAL_MONITORING_START', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Ticker enters IDX special monitoring board; EOD bar remains required with risk context.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SPECIAL_MONITORING_END', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SPECIAL_MONITORING', 'description' => 'Ticker exits IDX special monitoring board; clears special-monitoring state only.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'UMA', 'risk_family' => 'UMA', 'transition_type' => 'POINT_IN_TIME', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 0, 'clears_risk_family' => null, 'description' => 'Unusual Market Activity notice; exact-date risk context with no carry-forward end pair.', 'created_at' => $now, 'updated_at' => $now],
        ] as $row) {
            DB::table('market_data_trading_status_event_types')->updateOrInsert(
                ['event_type_code' => $row['event_type_code']],
                $row
            );
        }
    }

    private function normalizeLegacyLongSuspensionEvents(): void
    {
        if (! Schema::hasTable('market_data_trading_status_events') || ! $this->hasColumn('market_data_trading_status_events', 'event_type_code')) {
            return;
        }

        DB::table('market_data_trading_status_events')
            ->whereIn(DB::raw("UPPER(REPLACE(REPLACE(REPLACE(event_type_code, ' ', '_'), '-', '_'), '/', '_'))"), [
                'LONG_SUSPENSION',
                'LONG_SUSPENSION_GT_6M',
                'SUSPENSION_GT_6M',
                'SUSPENDED_GT_6M',
                'SUSPENSI_LEBIH_DARI_6_BULAN',
            ])
            ->update(['event_type_code' => 'SUSPENSION_OBSERVED', 'updated_at' => date('Y-m-d H:i:s')]);

        if ($this->hasColumn('market_data_trading_status_events', 'status_code')) {
            DB::table('market_data_trading_status_events')
                ->whereIn(DB::raw("UPPER(REPLACE(REPLACE(REPLACE(status_code, ' ', '_'), '-', '_'), '/', '_'))"), [
                    'LONG_SUSPENSION',
                    'LONG_SUSPENSION_GT_6M',
                    'SUSPENSION_GT_6M',
                    'SUSPENDED_GT_6M',
                    'SUSPENSI_LEBIH_DARI_6_BULAN',
                ])
                ->update(['event_type_code' => 'SUSPENSION_OBSERVED', 'updated_at' => date('Y-m-d H:i:s')]);
        }

        DB::table('market_data_trading_status_events')
            ->where('event_type_code', 'SUSPENDED')
            ->where(function ($query) {
                $query->whereRaw('LOWER(source_name) LIKE ?', ['%long%suspension%'])
                    ->orWhereRaw('LOWER(source_name) LIKE ?', ['%suspensi%6%'])
                    ->orWhereRaw('LOWER(source_name) LIKE ?', ['%suspensi%lebih%'])
                    ->orWhereRaw('LOWER(source_ref) LIKE ?', ['%suspensi-lebih-dari-6-bulan%'])
                    ->orWhereRaw('LOWER(source_ref) LIKE ?', ['%long%suspension%'])
                    ->orWhereRaw('LOWER(notes) LIKE ?', ['%lebih dari 6 bulan%'])
                    ->orWhereRaw('LOWER(notes) LIKE ?', ['%lebih dari enam bulan%'])
                    ->orWhereRaw('LOWER(notes) LIKE ?', ['%suspensi lebih%'])
                    ->orWhereRaw('LOWER(notes) LIKE ?', ['%long suspension%']);
            })
            ->update(['event_type_code' => 'SUSPENSION_OBSERVED', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    private function dropLegacyCoveragePolicyColumn(): void
    {
        $this->dropIndexIfExists('market_data_trading_status_event_types', 'idx_md_status_types_coverage_policy');

        if (! $this->hasColumn('market_data_trading_status_event_types', 'coverage_policy')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `market_data_trading_status_event_types` DROP COLUMN `coverage_policy`');
            return;
        }

        Schema::table('market_data_trading_status_event_types', function (Blueprint $table) {
            $table->dropColumn('coverage_policy');
        });
    }

    private function hasColumn(string $tableName, string $columnName): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $rows = DB::select(
                'SELECT COUNT(*) as aggregate FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$tableName, $columnName]
            );

            return isset($rows[0]) && (int) $rows[0]->aggregate > 0;
        }

        return Schema::hasColumn($tableName, $columnName);
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $rows = DB::select(
                'SELECT COUNT(*) as aggregate FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$tableName, $indexName]
            );

            return isset($rows[0]) && (int) $rows[0]->aggregate > 0;
        }

        try {
            $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($tableName);

            return array_key_exists($indexName, $indexes);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->hasIndex($tableName, $indexName)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `'.$tableName.'` DROP INDEX `'.$indexName.'`');
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }
}
