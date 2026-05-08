<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCorrectionPublicationLineageFields extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('eod_dataset_corrections', 'baseline_publication_id')) {
            DB::statement('ALTER TABLE eod_dataset_corrections ADD COLUMN baseline_publication_id BIGINT UNSIGNED NULL AFTER new_run_id');
        }

        if (! Schema::hasColumn('eod_dataset_corrections', 'replacement_publication_id')) {
            DB::statement('ALTER TABLE eod_dataset_corrections ADD COLUMN replacement_publication_id BIGINT UNSIGNED NULL AFTER baseline_publication_id');
        }

        $this->addIndexIfMissing('eod_dataset_corrections', 'idx_corr_baseline_publication', 'CREATE INDEX idx_corr_baseline_publication ON eod_dataset_corrections (baseline_publication_id)');
        $this->addIndexIfMissing('eod_dataset_corrections', 'idx_corr_replacement_publication', 'CREATE INDEX idx_corr_replacement_publication ON eod_dataset_corrections (replacement_publication_id)');
        $this->addIndexIfMissing('eod_dataset_corrections', 'idx_corr_baseline_replacement_publication', 'CREATE INDEX idx_corr_baseline_replacement_publication ON eod_dataset_corrections (baseline_publication_id, replacement_publication_id)');
    }

    public function down()
    {
        foreach (['idx_corr_baseline_replacement_publication', 'idx_corr_replacement_publication', 'idx_corr_baseline_publication'] as $index) {
            $this->dropIndexIfExists('eod_dataset_corrections', $index);
        }

        foreach (['replacement_publication_id', 'baseline_publication_id'] as $column) {
            if (Schema::hasColumn('eod_dataset_corrections', $column)) {
                DB::statement('ALTER TABLE eod_dataset_corrections DROP COLUMN '.$column);
            }
        }
    }

    private function addIndexIfMissing($table, $indexName, $statement)
    {
        if (! $this->indexExists($table, $indexName)) {
            DB::statement($statement);
        }
    }

    private function dropIndexIfExists($table, $indexName)
    {
        if ($this->indexExists($table, $indexName)) {
            DB::statement('DROP INDEX '.$indexName.' ON '.$table);
        }
    }

    private function indexExists($table, $indexName)
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(1) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return $row && (int) $row->aggregate > 0;
    }
}
