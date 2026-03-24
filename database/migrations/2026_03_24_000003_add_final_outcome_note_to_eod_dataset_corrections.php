<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFinalOutcomeNoteToEodDatasetCorrections extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('eod_dataset_corrections', 'final_outcome_note')) {
            DB::statement("ALTER TABLE eod_dataset_corrections ADD COLUMN final_outcome_note TEXT NULL AFTER published_at");
        }
    }

    public function down()
    {
        if (Schema::hasColumn('eod_dataset_corrections', 'final_outcome_note')) {
            DB::statement("ALTER TABLE eod_dataset_corrections DROP COLUMN final_outcome_note");
        }
    }
}
