<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceProviderContextToReplayMetrics extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            foreach ($this->columns() as $column => $definition) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    continue;
                }

                $definition($table);
            }
        });
    }

    public function down()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $existing = [];
            foreach (array_keys($this->columns()) as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }

    private function columns()
    {
        return [
            'source_mode' => function (Blueprint $table) { $table->string('source_mode', 32)->nullable()->after('source'); },
            'source_name' => function (Blueprint $table) { $table->string('source_name', 64)->nullable()->after('source_mode'); },
            'source_provider' => function (Blueprint $table) { $table->string('source_provider', 64)->nullable()->after('source_name'); },
            'source_timeout_seconds' => function (Blueprint $table) { $table->integer('source_timeout_seconds')->nullable()->after('source_provider'); },
            'source_retry_max' => function (Blueprint $table) { $table->integer('source_retry_max')->nullable()->after('source_timeout_seconds'); },
            'source_attempt_count' => function (Blueprint $table) { $table->integer('source_attempt_count')->nullable()->after('source_retry_max'); },
            'source_success_after_retry' => function (Blueprint $table) { $table->boolean('source_success_after_retry')->nullable()->after('source_attempt_count'); },
            'source_retry_exhausted' => function (Blueprint $table) { $table->boolean('source_retry_exhausted')->nullable()->after('source_success_after_retry'); },
            'source_final_http_status' => function (Blueprint $table) { $table->integer('source_final_http_status')->nullable()->after('source_retry_exhausted'); },
            'source_final_reason_code' => function (Blueprint $table) { $table->string('source_final_reason_code', 64)->nullable()->after('source_final_http_status'); },
            'source_input_file' => function (Blueprint $table) { $table->string('source_input_file', 255)->nullable()->after('source_final_reason_code'); },
            'expected_source_mode' => function (Blueprint $table) { $table->string('expected_source_mode', 32)->nullable()->after('expected_seal_state'); },
            'expected_source_name' => function (Blueprint $table) { $table->string('expected_source_name', 64)->nullable()->after('expected_source_mode'); },
            'expected_source_provider' => function (Blueprint $table) { $table->string('expected_source_provider', 64)->nullable()->after('expected_source_name'); },
            'expected_source_timeout_seconds' => function (Blueprint $table) { $table->integer('expected_source_timeout_seconds')->nullable()->after('expected_source_provider'); },
            'expected_source_retry_max' => function (Blueprint $table) { $table->integer('expected_source_retry_max')->nullable()->after('expected_source_timeout_seconds'); },
            'expected_source_attempt_count' => function (Blueprint $table) { $table->integer('expected_source_attempt_count')->nullable()->after('expected_source_retry_max'); },
            'expected_source_success_after_retry' => function (Blueprint $table) { $table->boolean('expected_source_success_after_retry')->nullable()->after('expected_source_attempt_count'); },
            'expected_source_retry_exhausted' => function (Blueprint $table) { $table->boolean('expected_source_retry_exhausted')->nullable()->after('expected_source_success_after_retry'); },
            'expected_source_final_http_status' => function (Blueprint $table) { $table->integer('expected_source_final_http_status')->nullable()->after('expected_source_retry_exhausted'); },
            'expected_source_final_reason_code' => function (Blueprint $table) { $table->string('expected_source_final_reason_code', 64)->nullable()->after('expected_source_final_http_status'); },
            'expected_source_input_file' => function (Blueprint $table) { $table->string('expected_source_input_file', 255)->nullable()->after('expected_source_final_reason_code'); },
            'expected_source_file_hash' => function (Blueprint $table) { $table->string('expected_source_file_hash', 128)->nullable()->after('expected_source_input_file'); },
            'expected_source_file_hash_algorithm' => function (Blueprint $table) { $table->string('expected_source_file_hash_algorithm', 32)->nullable()->after('expected_source_file_hash'); },
            'expected_source_file_size_bytes' => function (Blueprint $table) { $table->unsignedBigInteger('expected_source_file_size_bytes')->nullable()->after('expected_source_file_hash_algorithm'); },
            'expected_source_file_row_count' => function (Blueprint $table) { $table->unsignedInteger('expected_source_file_row_count')->nullable()->after('expected_source_file_size_bytes'); },
        ];
    }
}
