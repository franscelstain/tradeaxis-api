<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddReplayPublishabilityStateContext extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'publishability_state')) {
                $table->string('publishability_state', 16)->nullable()->after('status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'publication_id')) {
                $table->unsignedBigInteger('publication_id')->nullable()->after('publishability_state');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'publication_run_id')) {
                $table->unsignedBigInteger('publication_run_id')->nullable()->after('publication_id');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'is_current_publication')) {
                $table->boolean('is_current_publication')->nullable()->after('publication_version');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_terminal_status')) {
                $table->string('expected_terminal_status', 16)->nullable()->after('expected_status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_publishability_state')) {
                $table->string('expected_publishability_state', 16)->nullable()->after('expected_terminal_status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_publication_id')) {
                $table->unsignedBigInteger('expected_publication_id')->nullable()->after('expected_config_identity');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_publication_run_id')) {
                $table->unsignedBigInteger('expected_publication_run_id')->nullable()->after('expected_publication_id');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_is_current_publication')) {
                $table->boolean('expected_is_current_publication')->nullable()->after('expected_publication_version');
            }
        });

        foreach ([
            'idx_replay_daily_publishability' => 'CREATE INDEX idx_replay_daily_publishability ON md_replay_daily_metrics (replay_id, publishability_state)',
            'idx_replay_daily_publication_identity' => 'CREATE INDEX idx_replay_daily_publication_identity ON md_replay_daily_metrics (replay_id, publication_id, publication_version)',
        ] as $index => $statement) {
            try {
                DB::statement($statement);
            } catch (\Throwable $e) {
                // Index may already exist on environments created from the locked SQL schema.
            }
        }

    }

    public function down()
    {
        foreach ([
            'idx_replay_daily_publishability',
            'idx_replay_daily_publication_identity',
        ] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON md_replay_daily_metrics');
            } catch (\Throwable $e) {
                // Ignore missing index or SQLite test environments.
            }
        }

        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $columns = [
                'publishability_state',
                'publication_id',
                'publication_run_id',
                'is_current_publication',
                'expected_terminal_status',
                'expected_publishability_state',
                'expected_publication_id',
                'expected_publication_run_id',
                'expected_is_current_publication',
            ];

            $existing = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
