<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicationSemanticIdentityMigrationOnMariaDbTest extends TestCase
{
    public function test_additive_upgrade_preserves_v1_rows_and_rolls_back_only_new_columns(): void
    {
        $base = config('database.connections.mysql');
        config()->set('database.connections.pub_semantic_migration_control', array_merge($base, ['database' => 'tradeaxis_testing']));
        $control = DB::connection('pub_semantic_migration_control');
        $database = 'tradeaxis_testing_pub_semantic_migration_'.bin2hex(random_bytes(5));
        $connection = 'pub_semantic_migration_case';
        $control->statement('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');
        try {
            config()->set('database.connections.'.$connection, array_merge($base, ['database' => $database]));
            config()->set('database.default', $connection);
            DB::purge($connection);
            foreach (['eod_runs', 'eod_publications', 'eod_dataset_corrections'] as $tableName) {
                Schema::connection($connection)->create($tableName, function (Blueprint $table) use ($tableName): void {
                    $table->bigIncrements($tableName === 'eod_runs' ? 'run_id' : ($tableName === 'eod_publications' ? 'publication_id' : 'correction_id'));
                    $table->string('legacy_marker')->nullable();
                });
                DB::connection($connection)->table($tableName)->insert(['legacy_marker' => 'V1_RETAINED']);
            }

            require_once base_path('database/migrations/2026_09_29_000003_add_publication_semantic_identity_profile.php');
            $migration = new AddPublicationSemanticIdentityProfile();
            $migration->up();
            foreach (['eod_runs', 'eod_publications'] as $tableName) {
                $this->assertTrue(Schema::connection($connection)->hasColumn($tableName, 'publication_semantic_profile'));
                $this->assertNull(DB::connection($connection)->table($tableName)->value('publication_semantic_profile'));
                $this->assertSame('V1_RETAINED', DB::connection($connection)->table($tableName)->value('legacy_marker'));
            }
            $this->assertTrue(Schema::connection($connection)->hasColumn('eod_publications', 'correction_semantic_hash'));
            $this->assertTrue(Schema::connection($connection)->hasColumn('eod_publications', 'seal_fingerprint'));
            $this->assertTrue(Schema::connection($connection)->hasColumn('eod_dataset_corrections', 'semantic_identity_hash'));

            $migration->down();
            $this->assertFalse(Schema::connection($connection)->hasColumn('eod_publications', 'publication_semantic_profile'));
            $this->assertFalse(Schema::connection($connection)->hasColumn('eod_dataset_corrections', 'semantic_identity_hash'));
            foreach (['eod_runs', 'eod_publications', 'eod_dataset_corrections'] as $tableName) {
                $this->assertSame('V1_RETAINED', DB::connection($connection)->table($tableName)->value('legacy_marker'));
            }
        } finally {
            DB::purge($connection);
            if (!preg_match('/^tradeaxis_testing_pub_semantic_migration_[a-f0-9]{10}$/D', $database)) throw new RuntimeException('UNSAFE_TEST_DATABASE_NAME');
            $control->statement('DROP DATABASE `'.$database.'`');
            DB::purge('pub_semantic_migration_control');
        }
    }
}
