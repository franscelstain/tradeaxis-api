<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A sealed publication's identity is immutable while pointer and audit metadata remain updateable. */
class ProtectSealedPublicationIdentity extends Migration
{
    private const UPDATE_TRIGGER = 'trg_eod_publication_sealed_identity_bu';
    private const DELETE_TRIGGER = 'trg_eod_publication_sealed_identity_bd';

    private const SEALED_FIELDS = [
        'trade_date', 'run_id', 'publication_version', 'seal_state',
        'bars_batch_hash', 'indicators_batch_hash', 'eligibility_batch_hash',
        'source_file_hash', 'source_file_hash_algorithm', 'source_file_size_bytes', 'source_file_row_count',
        'config_snapshot_id', 'factor_set_id', 'factor_set_hash',
        'observation_manifest_hash', 'semantic_observation_manifest_hash',
        'seal_provenance_scope', 'publication_manifest_hash',
        'price_product_code', 'price_product_version', 'read_model_version', 'readiness_state',
        'source_scale_assessment_set_hash', 'market_structure_revision_set_hash', 'factor_decision_set_hash',
        'sealed_at', 'artifact_hash_profile', 'publication_semantic_profile',
        'correction_semantic_hash', 'seal_fingerprint',
    ];

    public function up()
    {
        if (! Schema::hasTable('eod_publications')) {
            throw new RuntimeException('SEALED_PUBLICATION_IDENTITY_PARENT_SCHEMA_REQUIRED');
        }
        foreach (self::SEALED_FIELDS as $field) {
            if (! Schema::hasColumn('eod_publications', $field)) {
                throw new RuntimeException('SEALED_PUBLICATION_IDENTITY_COLUMN_REQUIRED: '.$field);
            }
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            $schema = DB::connection()->getDatabaseName();
            $changed = implode("\n                OR ", array_map(static function ($field) {
                return 'NOT (NEW.`'.$field.'` <=> OLD.`'.$field.'`)';
            }, self::SEALED_FIELDS));
            $this->createMysqlTriggerIfMissing($schema, self::UPDATE_TRIGGER,
                'CREATE TRIGGER `'.self::UPDATE_TRIGGER.'` BEFORE UPDATE ON `eod_publications` FOR EACH ROW '
                .'BEGIN IF OLD.`seal_state` = \'SEALED\' AND ('.$changed.') THEN '
                .'SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'SEALED_PUBLICATION_IDENTITY_IMMUTABLE\'; '
                .'END IF; END');
            $this->createMysqlTriggerIfMissing($schema, self::DELETE_TRIGGER,
                'CREATE TRIGGER `'.self::DELETE_TRIGGER.'` BEFORE DELETE ON `eod_publications` FOR EACH ROW '
                .'BEGIN IF OLD.`seal_state` = \'SEALED\' THEN '
                .'SIGNAL SQLSTATE \'45000\' SET MESSAGE_TEXT = \'SEALED_PUBLICATION_IDENTITY_IMMUTABLE\'; '
                .'END IF; END');
        } elseif ($driver === 'sqlite') {
            $changed = implode(" OR ", array_map(static function ($field) {
                return 'NEW."'.$field.'" IS NOT OLD."'.$field.'"';
            }, self::SEALED_FIELDS));
            DB::unprepared('CREATE TRIGGER IF NOT EXISTS '.self::UPDATE_TRIGGER.' BEFORE UPDATE ON eod_publications '
                .'WHEN OLD.seal_state = \'SEALED\' AND ('.$changed.') BEGIN '
                .'SELECT RAISE(ABORT, \'SEALED_PUBLICATION_IDENTITY_IMMUTABLE\'); END');
            DB::unprepared('CREATE TRIGGER IF NOT EXISTS '.self::DELETE_TRIGGER.' BEFORE DELETE ON eod_publications '
                .'WHEN OLD.seal_state = \'SEALED\' BEGIN '
                .'SELECT RAISE(ABORT, \'SEALED_PUBLICATION_IDENTITY_IMMUTABLE\'); END');
        } else {
            throw new RuntimeException('SEALED_PUBLICATION_IDENTITY_UNSUPPORTED_DATABASE');
        }
    }

    public function down()
    {
        if (DB::table('eod_publications')->where('seal_state', 'SEALED')->exists()) {
            throw new RuntimeException('SEALED_PUBLICATION_IDENTITY_ROLLBACK_WOULD_REMOVE_PROTECTION');
        }
        DB::unprepared('DROP TRIGGER IF EXISTS `'.self::UPDATE_TRIGGER.'`');
        DB::unprepared('DROP TRIGGER IF EXISTS `'.self::DELETE_TRIGGER.'`');
    }

    private function createMysqlTriggerIfMissing(string $schema, string $name, string $sql): void
    {
        $exists = DB::table('information_schema.TRIGGERS')
            ->where('TRIGGER_SCHEMA', $schema)->where('TRIGGER_NAME', $name)->exists();
        if (! $exists) {
            DB::unprepared($sql);
        }
    }
}
