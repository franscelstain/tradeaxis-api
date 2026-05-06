<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropActualSourceFileColumnsFromReplayMetrics extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $existing = [];
            foreach (['source_file_hash', 'source_file_hash_algorithm', 'source_file_size_bytes', 'source_file_row_count'] as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }

    public function down()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $columns = [
                'source_file_hash' => function (Blueprint $table) { $table->string('source_file_hash', 128)->nullable()->after('source_input_file'); },
                'source_file_hash_algorithm' => function (Blueprint $table) { $table->string('source_file_hash_algorithm', 32)->nullable()->after('source_file_hash'); },
                'source_file_size_bytes' => function (Blueprint $table) { $table->unsignedBigInteger('source_file_size_bytes')->nullable()->after('source_file_hash_algorithm'); },
                'source_file_row_count' => function (Blueprint $table) { $table->unsignedInteger('source_file_row_count')->nullable()->after('source_file_size_bytes'); },
            ];

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $definition($table);
                }
            }
        });
    }
}
