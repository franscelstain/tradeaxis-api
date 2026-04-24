<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSourceIdentityImmutabilityLineageFields extends Migration
{
    public function up()
    {
        $runColumns = [
            "ADD COLUMN source_file_hash VARCHAR(64) NULL AFTER source_final_reason_code",
            "ADD COLUMN source_file_hash_algorithm VARCHAR(32) NULL AFTER source_file_hash",
            "ADD COLUMN source_file_size_bytes BIGINT UNSIGNED NULL AFTER source_file_hash_algorithm",
            "ADD COLUMN source_file_row_count INT UNSIGNED NULL AFTER source_file_size_bytes",
        ];

        foreach ($runColumns as $statement) {
            $column = preg_replace('/^ADD COLUMN\s+([a-z_]+).*$/i', '$1', $statement);
            if (! Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs '.$statement);
            }
        }

        $publicationColumns = [
            "ADD COLUMN previous_publication_id BIGINT UNSIGNED NULL AFTER supersedes_publication_id",
            "ADD COLUMN replaced_publication_id BIGINT UNSIGNED NULL AFTER previous_publication_id",
            "ADD COLUMN source_file_hash VARCHAR(64) NULL AFTER eligibility_batch_hash",
            "ADD COLUMN source_file_hash_algorithm VARCHAR(32) NULL AFTER source_file_hash",
            "ADD COLUMN source_file_size_bytes BIGINT UNSIGNED NULL AFTER source_file_hash_algorithm",
            "ADD COLUMN source_file_row_count INT UNSIGNED NULL AFTER source_file_size_bytes",
        ];

        foreach ($publicationColumns as $statement) {
            $column = preg_replace('/^ADD COLUMN\s+([a-z_]+).*$/i', '$1', $statement);
            if (! Schema::hasColumn('eod_publications', $column)) {
                DB::statement('ALTER TABLE eod_publications '.$statement);
            }
        }

        foreach ([
            'idx_runs_source_file_hash' => 'CREATE INDEX idx_runs_source_file_hash ON eod_runs (source_file_hash)',
            'idx_publication_previous' => 'CREATE INDEX idx_publication_previous ON eod_publications (previous_publication_id)',
            'idx_publication_replaced' => 'CREATE INDEX idx_publication_replaced ON eod_publications (replaced_publication_id)',
            'idx_publication_source_file_hash' => 'CREATE INDEX idx_publication_source_file_hash ON eod_publications (source_file_hash)',
        ] as $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
                // index already exists / sqlite compatibility
            }
        }
    }

    public function down()
    {
        foreach (['idx_runs_source_file_hash'] as $index) {
            try { DB::statement('DROP INDEX '.$index.' ON eod_runs'); } catch (\Throwable $e) { try { DB::statement('DROP INDEX '.$index); } catch (\Throwable $ignored) {} }
        }

        foreach (['idx_publication_previous', 'idx_publication_replaced', 'idx_publication_source_file_hash'] as $index) {
            try { DB::statement('DROP INDEX '.$index.' ON eod_publications'); } catch (\Throwable $e) { try { DB::statement('DROP INDEX '.$index); } catch (\Throwable $ignored) {} }
        }

        foreach (['source_file_row_count', 'source_file_size_bytes', 'source_file_hash_algorithm', 'source_file_hash'] as $column) {
            if (Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs DROP COLUMN '.$column);
            }
        }

        foreach (['source_file_row_count', 'source_file_size_bytes', 'source_file_hash_algorithm', 'source_file_hash', 'replaced_publication_id', 'previous_publication_id'] as $column) {
            if (Schema::hasColumn('eod_publications', $column)) {
                DB::statement('ALTER TABLE eod_publications DROP COLUMN '.$column);
            }
        }
    }
}
