<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddReplayV2BoundInputContext extends Migration
{
    private const COLUMNS = [
        'replay_mode', 'knowledge_cutoff_at', 'fixture_manifest_hash',
        'source_observation_manifest_hash', 'canonical_raw_input_hash',
        'temporal_identity_hash', 'calendar_status_hash', 'event_factor_hash',
        'config_snapshot_id', 'config_snapshot_hash', 'formula_registry_hash',
        'reason_registry_hash', 'read_model_version', 'serialization_version',
        'executable_build_identity', 'admission_state', 'bound_input_context_json',
    ];

    public function up()
    {
        if (! Schema::hasTable('md_replay_daily_metrics')) {
            return;
        }

        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'replay_mode')) $table->string('replay_mode', 32)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'knowledge_cutoff_at')) $table->dateTime('knowledge_cutoff_at')->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'fixture_manifest_hash')) $table->char('fixture_manifest_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'source_observation_manifest_hash')) $table->char('source_observation_manifest_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'canonical_raw_input_hash')) $table->char('canonical_raw_input_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'temporal_identity_hash')) $table->char('temporal_identity_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'calendar_status_hash')) $table->char('calendar_status_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'event_factor_hash')) $table->char('event_factor_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'config_snapshot_hash')) $table->char('config_snapshot_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'formula_registry_hash')) $table->char('formula_registry_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'reason_registry_hash')) $table->char('reason_registry_hash', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'read_model_version')) $table->string('read_model_version', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'serialization_version')) $table->string('serialization_version', 64)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'executable_build_identity')) $table->string('executable_build_identity', 128)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'admission_state')) $table->string('admission_state', 32)->nullable();
            if (! Schema::hasColumn('md_replay_daily_metrics', 'bound_input_context_json')) $table->longText('bound_input_context_json')->nullable();
        });

        // Historical unmoded rows are intentionally not rewritten. They remain unclassified and
        // therefore inadmissible as B18 replay proof. Only new executions are required to bind mode.
        $this->createIndexIfMissing('idx_replay_daily_mode', 'CREATE INDEX idx_replay_daily_mode ON md_replay_daily_metrics (replay_id, replay_mode)');
        $this->createIndexIfMissing('idx_replay_daily_cutoff', 'CREATE INDEX idx_replay_daily_cutoff ON md_replay_daily_metrics (replay_mode, knowledge_cutoff_at)');
    }

    public function down()
    {
        if (! Schema::hasTable('md_replay_daily_metrics')) return;
        if (DB::getDriverName() === 'mysql') {
            foreach (['idx_replay_daily_mode', 'idx_replay_daily_cutoff'] as $index) {
                try { DB::statement('DROP INDEX '.$index.' ON md_replay_daily_metrics'); } catch (\Throwable $e) {}
            }
        }
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) $table->dropColumn($column);
            }
        });
    }

    private function createIndexIfMissing(string $name, string $sql): void
    {
        if (DB::getDriverName() !== 'mysql') return;
        $exists = DB::select("SHOW INDEX FROM md_replay_daily_metrics WHERE Key_name = ?", [$name]);
        if ($exists === []) DB::statement($sql);
    }
}
