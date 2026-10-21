<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A sealed publication can mirror current state only after the date pointer names it. */
class RequirePointerBeforeCurrentPublication extends Migration
{
    private const TRIGGER = 'trg_eod_pub_current_requires_pointer_bu';

    public function up()
    {
        foreach (['eod_publications', 'eod_current_publication_pointer'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new RuntimeException('CURRENT_PUBLICATION_POINTER_PARENT_REQUIRED: '.$table);
            }
        }

        if (DB::getDriverName() === 'mysql') {
            $schema = DB::connection()->getDatabaseName();
            $exists = DB::table('information_schema.TRIGGERS')->where('TRIGGER_SCHEMA', $schema)
                ->where('TRIGGER_NAME', self::TRIGGER)->exists();
            if (! $exists) {
                DB::unprepared("CREATE TRIGGER `".self::TRIGGER."` BEFORE UPDATE ON `eod_publications` FOR EACH ROW
                    BEGIN
                      IF NEW.`seal_state` = 'SEALED' AND NEW.`is_current` = 1 AND NOT EXISTS (
                        SELECT 1 FROM `eod_current_publication_pointer` p
                        WHERE p.`trade_date` = NEW.`trade_date` AND p.`publication_id` = NEW.`publication_id`
                      ) THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CURRENT_PUBLICATION_POINTER_REQUIRED';
                      END IF;
                    END");
            }
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared("CREATE TRIGGER IF NOT EXISTS ".self::TRIGGER." BEFORE UPDATE ON eod_publications
                WHEN NEW.seal_state = 'SEALED' AND NEW.is_current = 1 AND NOT EXISTS (
                    SELECT 1 FROM eod_current_publication_pointer p
                    WHERE p.trade_date = NEW.trade_date AND p.publication_id = NEW.publication_id
                ) BEGIN SELECT RAISE(ABORT, 'CURRENT_PUBLICATION_POINTER_REQUIRED'); END");
        } else {
            throw new RuntimeException('CURRENT_PUBLICATION_POINTER_UNSUPPORTED_DATABASE');
        }
    }

    public function down()
    {
        if (DB::table('eod_publications')->where('seal_state', 'SEALED')->exists()) {
            throw new RuntimeException('CURRENT_PUBLICATION_POINTER_ROLLBACK_WOULD_REMOVE_PROTECTION');
        }
        DB::unprepared('DROP TRIGGER IF EXISTS `'.self::TRIGGER.'`');
    }
}
