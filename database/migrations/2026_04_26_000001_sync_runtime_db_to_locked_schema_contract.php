<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncRuntimeDbToLockedSchemaContract extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_universe_count')) {
                $table->integer('coverage_universe_count')->nullable()->after('publication_version');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_available_count')) {
                $table->integer('coverage_available_count')->nullable()->after('coverage_universe_count');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_missing_count')) {
                $table->integer('coverage_missing_count')->nullable()->after('coverage_available_count');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_min_threshold')) {
                $table->decimal('coverage_min_threshold', 12, 6)->nullable()->after('coverage_ratio');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_gate_state')) {
                $table->string('coverage_gate_state', 16)->nullable()->after('coverage_min_threshold');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_threshold_mode')) {
                $table->string('coverage_threshold_mode', 32)->nullable()->after('coverage_gate_state');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_universe_basis')) {
                $table->string('coverage_universe_basis', 64)->nullable()->after('coverage_threshold_mode');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_contract_version')) {
                $table->string('coverage_contract_version', 64)->nullable()->after('coverage_universe_basis');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_missing_sample_json')) {
                $table->text('coverage_missing_sample_json')->nullable()->after('coverage_contract_version');
            }
        });

        if (Schema::hasColumn('md_replay_daily_metrics', 'coverage_ratio')) {
            try {
                DB::statement('ALTER TABLE md_replay_daily_metrics MODIFY coverage_ratio DECIMAL(12,6) NULL');
            } catch (\Throwable $e) {
            }
        }

        foreach ([
            'idx_replay_daily_status' => 'CREATE INDEX idx_replay_daily_status ON md_replay_daily_metrics (replay_id, status)',
            'idx_replay_daily_effective' => 'CREATE INDEX idx_replay_daily_effective ON md_replay_daily_metrics (replay_id, trade_date_effective)',
            'idx_replay_daily_comparison' => 'CREATE INDEX idx_replay_daily_comparison ON md_replay_daily_metrics (replay_id, comparison_result)',
            'idx_replay_daily_coverage_gate' => 'CREATE INDEX idx_replay_daily_coverage_gate ON md_replay_daily_metrics (replay_id, coverage_gate_state)',
            'idx_replay_daily_artifact_scope' => 'CREATE INDEX idx_replay_daily_artifact_scope ON md_replay_daily_metrics (replay_id, artifact_changed_scope)',
            'idx_replay_reason_code' => 'CREATE INDEX idx_replay_reason_code ON md_replay_reason_code_counts (replay_id, reason_code)',
            'md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique' => 'CREATE UNIQUE INDEX md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique ON md_session_snapshots (trade_date, snapshot_slot, ticker_id)',
            'md_session_snapshots_trade_date_snapshot_slot_index' => 'CREATE INDEX md_session_snapshots_trade_date_snapshot_slot_index ON md_session_snapshots (trade_date, snapshot_slot)',
            'md_session_snapshots_captured_at_index' => 'CREATE INDEX md_session_snapshots_captured_at_index ON md_session_snapshots (captured_at)',
        ] as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
            }
        }

        foreach (['idx_publication_promote_mode', 'idx_publication_publish_target'] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON eod_publications');
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$index);
                } catch (\Throwable $ignored) {
                }
            }
        }

        foreach (['publish_target', 'promote_mode'] as $column) {
            if (Schema::hasColumn('eod_publications', $column)) {
                try {
                    DB::statement('ALTER TABLE eod_publications DROP COLUMN '.$column);
                } catch (\Throwable $e) {
                }
            }
        }
    }

    public function down()
    {
        foreach ([
            'idx_replay_daily_status',
            'idx_replay_daily_effective',
            'idx_replay_daily_comparison',
            'idx_replay_daily_coverage_gate',
            'idx_replay_daily_artifact_scope',
        ] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON md_replay_daily_metrics');
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$index);
                } catch (\Throwable $ignored) {
                }
            }
        }

        foreach (['idx_replay_reason_code'] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON md_replay_reason_code_counts');
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$index);
                } catch (\Throwable $ignored) {
                }
            }
        }

        foreach ([
            'md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique',
            'md_session_snapshots_trade_date_snapshot_slot_index',
            'md_session_snapshots_captured_at_index',
        ] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON md_session_snapshots');
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$index);
                } catch (\Throwable $ignored) {
                }
            }
        }

        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            foreach ([
                'coverage_missing_sample_json',
                'coverage_contract_version',
                'coverage_universe_basis',
                'coverage_threshold_mode',
                'coverage_gate_state',
                'coverage_min_threshold',
                'coverage_missing_count',
                'coverage_available_count',
                'coverage_universe_count',
            ] as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
