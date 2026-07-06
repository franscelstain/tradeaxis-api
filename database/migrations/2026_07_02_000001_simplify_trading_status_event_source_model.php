<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SimplifyTradingStatusEventSourceModel extends Migration
{
    public function up()
    {
        $this->createTradingStatusEventTypes();
        $this->seedTradingStatusEventTypes();

        if (! Schema::hasTable('market_data_trading_status_events')) {
            $this->createTradingStatusEvents();
            return;
        }

        $hasLegacyStatusCode = $this->hasColumn('market_data_trading_status_events', 'status_code');
        $hasCanonicalEventType = $this->hasColumn('market_data_trading_status_events', 'event_type_code');

        if ($hasLegacyStatusCode && ! $hasCanonicalEventType) {
            Schema::table('market_data_trading_status_events', function (Blueprint $table) {
                $table->string('event_type_code', 64)->nullable()->after('trade_date');
            });

            $this->backfillEventTypeCodeFromLegacyStatusCode();
        }

        if (! $hasLegacyStatusCode && ! $hasCanonicalEventType) {
            Schema::table('market_data_trading_status_events', function (Blueprint $table) {
                $table->string('event_type_code', 64)->nullable()->after('trade_date');
            });
        }

        $this->backfillEventTypeCodeFromLegacyStatusCodeIfPossible();
        $this->assertEventTypeCodeBackfilled();

        $this->dropLegacyTradingStatusIndexes();
        $this->dropLegacyTradingStatusColumns();
        $this->collapseDuplicateCanonicalEventRows();
        $this->normalizeEventTypeCodeNullability();

        Schema::table('market_data_trading_status_events', function (Blueprint $table) {
            if (! $this->hasIndex('market_data_trading_status_events', 'uq_md_trading_status_ticker_date_type_source')) {
                $table->unique(['ticker_id', 'trade_date', 'event_type_code', 'source_name'], 'uq_md_trading_status_ticker_date_type_source');
            }
            if (! $this->hasIndex('market_data_trading_status_events', 'idx_md_trading_status_event_type_date')) {
                $table->index(['event_type_code', 'trade_date'], 'idx_md_trading_status_event_type_date');
            }
        });
    }

    public function down()
    {
        if (Schema::hasTable('market_data_trading_status_events')) {
            Schema::table('market_data_trading_status_events', function (Blueprint $table) {
                if (! Schema::hasColumn('market_data_trading_status_events', 'status_code')) {
                    $table->string('status_code', 64)->nullable()->after('trade_date');
                }
                if (! Schema::hasColumn('market_data_trading_status_events', 'is_suspended')) {
                    $table->boolean('is_suspended')->nullable()->after('status_code');
                }
                if (! Schema::hasColumn('market_data_trading_status_events', 'is_uma')) {
                    $table->boolean('is_uma')->nullable()->after('is_suspended');
                }
            });

            DB::table('market_data_trading_status_events')
                ->whereNull('status_code')
                ->update(['status_code' => DB::raw('event_type_code')]);
        }
    }

    private function createTradingStatusEventTypes(): void
    {
        if (Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        Schema::create('market_data_trading_status_event_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->string('event_type_code', 64)->primary();
            $table->string('risk_family', 64);
            $table->string('transition_type', 32);
            $table->string('expected_bar_policy', 32);
            $table->boolean('carries_forward')->default(false);
            $table->string('clears_risk_family', 64)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['risk_family', 'transition_type'], 'idx_md_status_types_family_transition');
            $table->index(['expected_bar_policy'], 'idx_md_status_types_expected_bar_policy');
        });
    }

    private function createTradingStatusEvents(): void
    {
        Schema::create('market_data_trading_status_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('trading_status_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->date('trade_date');
            $table->string('event_type_code', 64);
            $table->string('source_name', 64)->default('manual_trading_status_csv');
            $table->string('source_ref', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['ticker_id', 'trade_date', 'event_type_code', 'source_name'], 'uq_md_trading_status_ticker_date_type_source');
            $table->index(['trade_date', 'ticker_id'], 'idx_md_trading_status_date_ticker');
            $table->index(['event_type_code', 'trade_date'], 'idx_md_trading_status_event_type_date');
        });
    }

    private function collapseDuplicateCanonicalEventRows(): void
    {
        if (! Schema::hasTable('market_data_trading_status_events') || ! Schema::hasColumn('market_data_trading_status_events', 'event_type_code')) {
            return;
        }

        $duplicates = DB::table('market_data_trading_status_events')
            ->select([
                'ticker_id',
                'trade_date',
                'event_type_code',
                'source_name',
                DB::raw('MIN(trading_status_id) as keep_id'),
                DB::raw('COUNT(*) as duplicate_count'),
            ])
            ->groupBy('ticker_id', 'trade_date', 'event_type_code', 'source_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('market_data_trading_status_events')
                ->where('ticker_id', (int) $duplicate->ticker_id)
                ->where('trade_date', $duplicate->trade_date)
                ->where('event_type_code', $duplicate->event_type_code)
                ->where('source_name', $duplicate->source_name)
                ->where('trading_status_id', '<>', (int) $duplicate->keep_id)
                ->delete();
        }
    }

    private function backfillEventTypeCodeFromLegacyStatusCode(): void
    {
        if (! $this->hasColumn('market_data_trading_status_events', 'status_code') || ! $this->hasColumn('market_data_trading_status_events', 'event_type_code')) {
            return;
        }

        $select = ['trading_status_id', 'status_code'];
        $select[] = $this->hasColumn('market_data_trading_status_events', 'is_suspended')
            ? 'is_suspended'
            : DB::raw('0 as is_suspended');
        $select[] = $this->hasColumn('market_data_trading_status_events', 'is_uma')
            ? 'is_uma'
            : DB::raw('0 as is_uma');

        DB::table('market_data_trading_status_events')
            ->select($select)
            ->where(function ($query) {
                $query->whereNull('event_type_code')
                    ->orWhere('event_type_code', '');
            })
            ->orderBy('trading_status_id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('market_data_trading_status_events')
                        ->where('trading_status_id', (int) $row->trading_status_id)
                        ->update([
                            'event_type_code' => $this->mapLegacyStatusCodeToEventTypeCode(
                                $row->status_code,
                                $row->is_suspended ?? 0,
                                $row->is_uma ?? 0
                            ),
                        ]);
                }
            }, 'trading_status_id');
    }

    private function mapLegacyStatusCodeToEventTypeCode($statusCode, $isSuspended, $isUma): string
    {
        $code = $this->normalizeCode($statusCode);

        if ($code === 'UMA' || (int) $isUma === 1) {
            return 'UMA';
        }

        if (in_array($code, ['SPECIAL_MONITORING_END', 'SPECIAL_MONITORING_EXIT', 'SPECIAL_MONITORING_REMOVED', 'REMOVED_FROM_SPECIAL_MONITORING'], true)) {
            return 'SPECIAL_MONITORING_END';
        }

        if (in_array($code, ['SPECIAL_MONITORING', 'SPECIAL_MONITORING_START', 'WATCHLIST', 'SPECIAL_NOTATION', 'NOTASI_KHUSUS'], true)) {
            return 'SPECIAL_MONITORING_START';
        }

        if (strpos($code, 'UNSUSPEND') !== false || strpos($code, 'RESUME') !== false || in_array($code, ['ACTIVE', 'NORMAL', 'OPEN', 'REGULAR'], true)) {
            return 'UNSUSPENDED';
        }

        if (in_array($code, ['LONG_SUSPENSION', 'LONG_SUSPENSION_GT_6M', 'SUSPENSION_GT_6M', 'SUSPENDED_GT_6M', 'SUSPENSI_LEBIH_DARI_6_BULAN'], true)) {
            return 'SUSPENSION_OBSERVED';
        }

        if ((int) $isSuspended === 1 || strpos($code, 'SUSPEND') !== false || strpos($code, 'HALT') !== false) {
            return 'SUSPENDED';
        }

        return $code;
    }

    private function seedTradingStatusEventTypes(): void
    {
        if (! Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->tradingStatusEventTypeRows($now) as $row) {
            DB::table('market_data_trading_status_event_types')->updateOrInsert(
                ['event_type_code' => $row['event_type_code']],
                $row
            );
        }
    }

    private function tradingStatusEventTypeRows(string $now): array
    {
        return [
            ['event_type_code' => 'SUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'IDX suspends ticker trading; excludes ticker from expected EOD coverage until UNSUSPENDED.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SUSPENSION_OBSERVED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'OBSERVED', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Source snapshot shows ticker is still suspended, including IDX long-suspension lists; this is not a suspension start date.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'UNSUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SUSPENSION', 'description' => 'IDX reopens ticker trading; clears active SUSPENDED state.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SPECIAL_MONITORING_START', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Ticker enters IDX special monitoring board; included in coverage with risk context.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SPECIAL_MONITORING_END', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SPECIAL_MONITORING', 'description' => 'Ticker exits IDX special monitoring board; clears active special-monitoring state only.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'UMA', 'risk_family' => 'UMA', 'transition_type' => 'POINT_IN_TIME', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 0, 'clears_risk_family' => null, 'description' => 'Unusual Market Activity notice; exact-date risk context, no carry-forward end pair.', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    private function backfillEventTypeCodeFromLegacyStatusCodeIfPossible(): void
    {
        if ($this->hasColumn('market_data_trading_status_events', 'status_code')
            && $this->hasColumn('market_data_trading_status_events', 'event_type_code')) {
            $this->backfillEventTypeCodeFromLegacyStatusCode();
        }
    }

    private function assertEventTypeCodeBackfilled(): void
    {
        if (! $this->hasColumn('market_data_trading_status_events', 'event_type_code')) {
            throw new RuntimeException('market_data_trading_status_events.event_type_code is required before simplifying trading status events.');
        }

        $missing = DB::table('market_data_trading_status_events')
            ->where(function ($query) {
                $query->whereNull('event_type_code')
                    ->orWhere('event_type_code', '');
            })
            ->count();

        if ($missing > 0) {
            throw new RuntimeException('Cannot simplify trading status events because event_type_code still has '.$missing.' empty rows. Restore legacy status_code data or backfill event_type_code before rerunning migration.');
        }
    }

    private function dropLegacyTradingStatusIndexes(): void
    {
        foreach ([
            'idx_md_trading_status_coverage_date',
            'idx_md_trading_status_effect_date',
            'idx_md_trading_status_code_date',
            'uq_md_trading_status_ticker_date_code_source',
        ] as $indexName) {
            $this->dropIndexIfExists('market_data_trading_status_events', $indexName);
        }
    }

    private function dropLegacyTradingStatusColumns(): void
    {
        foreach (['coverage_exclusion_flag', 'event_risk_scope', 'is_uma', 'is_suspended', 'status_effect', 'status_code'] as $column) {
            $this->dropColumnIfExists('market_data_trading_status_events', $column);
        }
    }

    private function dropColumnIfExists(string $tableName, string $columnName): void
    {
        if (! $this->hasColumn($tableName, $columnName)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `'.$tableName.'` DROP COLUMN `'.$columnName.'`');
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropColumn($columnName);
        });
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

    private function normalizeEventTypeCodeNullability(): void
    {
        if (! $this->hasColumn('market_data_trading_status_events', 'event_type_code')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `market_data_trading_status_events` MODIFY `event_type_code` VARCHAR(64) NOT NULL');
        }
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

    private function normalizeCode($value): string
    {
        $code = strtoupper(trim((string) $value));
        $code = preg_replace('/[^A-Z0-9]+/', '_', $code);

        return trim((string) $code, '_');
    }

    private function hasIndex($tableName, $indexName)
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
}
