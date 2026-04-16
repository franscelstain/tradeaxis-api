<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRunTraceabilityAndLinkageFields extends Migration
{
    public function up()
    {
        $columns = [
            "ADD COLUMN source_name VARCHAR(64) NULL AFTER source",
            "ADD COLUMN source_provider VARCHAR(64) NULL AFTER source_name",
            "ADD COLUMN source_input_file VARCHAR(255) NULL AFTER source_provider",
            "ADD COLUMN source_timeout_seconds INT NULL AFTER source_input_file",
            "ADD COLUMN source_retry_max INT NULL AFTER source_timeout_seconds",
            "ADD COLUMN source_attempt_count INT NULL AFTER source_retry_max",
            "ADD COLUMN source_success_after_retry TINYINT(1) NULL AFTER source_attempt_count",
            "ADD COLUMN source_retry_exhausted TINYINT(1) NULL AFTER source_success_after_retry",
            "ADD COLUMN source_final_http_status INT NULL AFTER source_retry_exhausted",
            "ADD COLUMN source_final_reason_code VARCHAR(64) NULL AFTER source_final_http_status",
            "ADD COLUMN publication_id BIGINT UNSIGNED NULL AFTER supersedes_run_id",
            "ADD COLUMN correction_id BIGINT UNSIGNED NULL AFTER is_current_publication",
            "ADD COLUMN final_reason_code VARCHAR(64) NULL AFTER correction_id",
        ];

        foreach ($columns as $statement) {
            $column = preg_replace('/^ADD COLUMN\s+([a-z_]+).*$/i', '$1', $statement);
            if (! Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs '.$statement);
            }
        }

        foreach ([
            'idx_runs_publication_id' => 'CREATE INDEX idx_runs_publication_id ON eod_runs (publication_id)',
            'idx_runs_correction_id' => 'CREATE INDEX idx_runs_correction_id ON eod_runs (correction_id)',
            'idx_runs_final_reason_code' => 'CREATE INDEX idx_runs_final_reason_code ON eod_runs (final_reason_code)',
            'idx_runs_source_name' => 'CREATE INDEX idx_runs_source_name ON eod_runs (source_name)',
        ] as $index => $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // index already exists / sqlite compatibility
            }
        }
    }

    public function down()
    {
        foreach (['idx_runs_publication_id', 'idx_runs_correction_id', 'idx_runs_final_reason_code', 'idx_runs_source_name'] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON eod_runs');
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$index);
                } catch (\Throwable $ignored) {
                }
            }
        }

        foreach ([
            'final_reason_code',
            'correction_id',
            'publication_id',
            'source_final_reason_code',
            'source_final_http_status',
            'source_retry_exhausted',
            'source_success_after_retry',
            'source_attempt_count',
            'source_retry_max',
            'source_timeout_seconds',
            'source_input_file',
            'source_provider',
            'source_name',
        ] as $column) {
            if (Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs DROP COLUMN '.$column);
            }
        }
    }
}
