<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixPublicationCandidateAndEventLogging extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE eod_publications MODIFY sealed_at DATETIME NULL");
        DB::statement("ALTER TABLE eod_publications MODIFY seal_state ENUM('SEALED','UNSEALED') NOT NULL DEFAULT 'UNSEALED'");
    }

    public function down()
    {
        DB::statement("UPDATE eod_publications SET sealed_at = COALESCE(sealed_at, created_at) WHERE sealed_at IS NULL");
        DB::statement("UPDATE eod_publications SET seal_state = 'SEALED' WHERE seal_state IS NULL OR seal_state = ''");
        DB::statement("ALTER TABLE eod_publications MODIFY seal_state ENUM('SEALED','UNSEALED') NOT NULL DEFAULT 'SEALED'");
        DB::statement("ALTER TABLE eod_publications MODIFY sealed_at DATETIME NOT NULL");
    }
}
