<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MD-B10-A001 — deploy database-level sealed-history immutability and persist
 * independent current-projection/publication reconciliation evidence.
 *
 * The up() path is deliberately repair-safe after a failed MariaDB DDL attempt:
 * MariaDB may retain CREATE TABLE effects even when a later index statement fails
 * before Laravel records the migration. Existing compatible table state is retained,
 * missing short-named indexes are added, and all nine triggers are recreated idempotently.
 */
class EnforceSealedHistoryAndProjectionReconciliation extends Migration
{
    private const RECON_TABLE = 'md_publication_projection_reconciliations';

    private const TRIGGERS = [
        'trg_eod_bars_history_bi_sealed_immutable' => ['eod_bars_history', 'INSERT'],
        'trg_eod_bars_history_bu_sealed_immutable' => ['eod_bars_history', 'UPDATE'],
        'trg_eod_bars_history_bd_sealed_immutable' => ['eod_bars_history', 'DELETE'],
        'trg_eod_indicators_history_bi_sealed_immutable' => ['eod_indicators_history', 'INSERT'],
        'trg_eod_indicators_history_bu_sealed_immutable' => ['eod_indicators_history', 'UPDATE'],
        'trg_eod_indicators_history_bd_sealed_immutable' => ['eod_indicators_history', 'DELETE'],
        'trg_eod_eligibility_history_bi_sealed_immutable' => ['eod_eligibility_history', 'INSERT'],
        'trg_eod_eligibility_history_bu_sealed_immutable' => ['eod_eligibility_history', 'UPDATE'],
        'trg_eod_eligibility_history_bd_sealed_immutable' => ['eod_eligibility_history', 'DELETE'],
    ];

    public function up()
    {
        if (! Schema::hasTable(self::RECON_TABLE)) {
            Schema::create(self::RECON_TABLE, function (Blueprint $table) {
                $table->bigIncrements('reconciliation_id');
                $table->char('reconciliation_uid', 64);
                $table->date('trade_date');
                $table->unsignedBigInteger('publication_id')->nullable();
                $table->unsignedBigInteger('run_id')->nullable();
                $table->unsignedInteger('publication_version')->nullable();
                $table->string('pointer_state', 32);
                $table->string('reconciliation_state', 32);
                $table->unsignedInteger('bars_projection_count')->default(0);
                $table->unsignedInteger('bars_history_count')->default(0);
                $table->unsignedInteger('bars_missing_history_count')->default(0);
                $table->unsignedInteger('bars_missing_projection_count')->default(0);
                $table->unsignedInteger('bars_value_mismatch_count')->default(0);
                $table->unsignedInteger('indicators_projection_count')->default(0);
                $table->unsignedInteger('indicators_history_count')->default(0);
                $table->unsignedInteger('indicators_missing_history_count')->default(0);
                $table->unsignedInteger('indicators_missing_projection_count')->default(0);
                $table->unsignedInteger('indicators_value_mismatch_count')->default(0);
                $table->unsignedInteger('eligibility_projection_count')->default(0);
                $table->unsignedInteger('eligibility_history_count')->default(0);
                $table->unsignedInteger('eligibility_missing_history_count')->default(0);
                $table->unsignedInteger('eligibility_missing_projection_count')->default(0);
                $table->unsignedInteger('eligibility_value_mismatch_count')->default(0);
                $table->unsignedInteger('orphan_projection_row_count')->default(0);
                $table->unsignedInteger('mismatch_count')->default(0);
                $table->longText('mismatch_sample_json')->nullable();
                $table->char('reconciliation_hash', 64);
                $table->dateTime('checked_at');
                $table->dateTime('created_at');

                // Explicit names keep MariaDB identifiers below the 64-byte limit.
                $table->unique('reconciliation_uid', 'uq_md_pub_proj_recon_uid');
                $table->index(['trade_date', 'reconciliation_state'], 'idx_md_pub_proj_recon_date_state');
                $table->index(['publication_id', 'checked_at'], 'idx_md_pub_proj_recon_pub_checked');
                $table->index('checked_at', 'idx_md_pub_proj_recon_checked');
            });
        } else {
            $this->assertCompatibleExistingReconciliationTable();
            $this->ensureReconciliationIndexes();
        }

        foreach (self::TRIGGERS as $name => $definition) {
            [$table, $event] = $definition;
            DB::unprepared('DROP TRIGGER IF EXISTS `'.$name.'`');
            DB::unprepared($this->createTriggerSql($name, $table, $event));
        }
    }

    public function down()
    {
        foreach (array_keys(self::TRIGGERS) as $name) {
            DB::unprepared('DROP TRIGGER IF EXISTS `'.$name.'`');
        }

        Schema::dropIfExists(self::RECON_TABLE);
    }

    private function assertCompatibleExistingReconciliationTable()
    {
        $required = [
            'reconciliation_id', 'reconciliation_uid', 'trade_date', 'publication_id', 'run_id',
            'publication_version', 'pointer_state', 'reconciliation_state',
            'bars_projection_count', 'bars_history_count', 'bars_missing_history_count',
            'bars_missing_projection_count', 'bars_value_mismatch_count',
            'indicators_projection_count', 'indicators_history_count', 'indicators_missing_history_count',
            'indicators_missing_projection_count', 'indicators_value_mismatch_count',
            'eligibility_projection_count', 'eligibility_history_count', 'eligibility_missing_history_count',
            'eligibility_missing_projection_count', 'eligibility_value_mismatch_count',
            'orphan_projection_row_count', 'mismatch_count', 'mismatch_sample_json',
            'reconciliation_hash', 'checked_at', 'created_at',
        ];

        foreach ($required as $column) {
            if (! Schema::hasColumn(self::RECON_TABLE, $column)) {
                throw new \RuntimeException('MD_B10_RECONCILIATION_SCHEMA_DRIFT_MISSING_COLUMN: '.$column);
            }
        }
    }

    private function ensureReconciliationIndexes()
    {
        $indexes = [
            'uq_md_pub_proj_recon_uid' => 'ALTER TABLE `'.self::RECON_TABLE.'` ADD UNIQUE `uq_md_pub_proj_recon_uid` (`reconciliation_uid`)',
            'idx_md_pub_proj_recon_date_state' => 'ALTER TABLE `'.self::RECON_TABLE.'` ADD INDEX `idx_md_pub_proj_recon_date_state` (`trade_date`, `reconciliation_state`)',
            'idx_md_pub_proj_recon_pub_checked' => 'ALTER TABLE `'.self::RECON_TABLE.'` ADD INDEX `idx_md_pub_proj_recon_pub_checked` (`publication_id`, `checked_at`)',
            'idx_md_pub_proj_recon_checked' => 'ALTER TABLE `'.self::RECON_TABLE.'` ADD INDEX `idx_md_pub_proj_recon_checked` (`checked_at`)',
        ];

        foreach ($indexes as $name => $sql) {
            if (! $this->indexExists(self::RECON_TABLE, $name)) {
                DB::statement($sql);
            }
        }
    }

    private function indexExists($table, $index)
    {
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $index]
        );

        return count($rows) > 0;
    }

    private function createTriggerSql($name, $table, $event)
    {
        if ($event === 'INSERT') {
            $condition = "EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED')";
        } elseif ($event === 'DELETE') {
            $condition = "EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED')";
        } else {
            $condition = "EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = OLD.publication_id AND seal_state = 'SEALED')\n"
                ."       OR EXISTS (SELECT 1 FROM eod_publications WHERE publication_id = NEW.publication_id AND seal_state = 'SEALED')";
        }

        return "CREATE TRIGGER `{$name}` BEFORE {$event} ON `{$table}` FOR EACH ROW\n"
            ."BEGIN\n"
            ."    IF {$condition} THEN\n"
            ."        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE';\n"
            ."    END IF;\n"
            ."END";
    }
}
