<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VersionWatchlistOfficialBacktestEvidenceAndParamsetIdentity extends Migration
{
    private const PARAMSET_UPDATE_TRIGGER = 'trg_wps_guard_update';
    private const PARAMSET_DELETE_TRIGGER = 'trg_wps_no_delete';
    private const OFFICIAL_EVIDENCE_TABLES = [
        'watchlist_bt_eval' => 'wbe_eval',
        'watchlist_bt_picks_ws' => 'wbe_picks',
        'watchlist_bt_universe_ws' => 'wbe_univ',
        'watchlist_bt_cutoffs_ws' => 'wbe_cutoffs',
    ];

    public function up(): void
    {
        $this->assertSupportTablesEmptyBeforeVersioning();
        $this->versionEvaluationIdentity();
        $this->synchronizeEvaluationUniqueIdentity();
        $this->versionOosIdentity();
        $this->versionPicksEvidence();
        $this->versionUniverseEvidence();
        $this->versionCutoffEvidence();
        $this->versionParamsetIdentity();
        $this->backfillParamsetIdentity();
        $this->createMysqlParamsetGuards();
        $this->createMysqlOfficialEvidenceGuards();
    }

    public function down(): void
    {
        $this->dropMysqlOfficialEvidenceGuards();
        $this->dropMysqlParamsetGuards();
        // Evidence identity is intentionally not destructively downgraded. The owner
        // contract treats official backtest evidence and paramset history as immutable.
    }

    private function assertSupportTablesEmptyBeforeVersioning(): void
    {
        foreach (['watchlist_bt_picks_ws', 'watchlist_bt_universe_ws', 'watchlist_bt_cutoffs_ws'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'eval_id') && DB::table($table)->count() > 0) {
                throw new RuntimeException(
                    'WS_C171_EVIDENCE_SCHEMA_BACKFILL_REQUIRED: '.$table.' contains unversioned rows; automatic identity guessing is forbidden.'
                );
            }
        }
    }

    private function versionEvaluationIdentity(): void
    {
        if (! Schema::hasTable('watchlist_bt_eval')) {
            return;
        }
        Schema::table('watchlist_bt_eval', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_eval', 'eval_model_hash')) {
                $table->char('eval_model_hash', 40)->nullable()->after('eval_model');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'implementation_version')) {
                $table->string('implementation_version', 64)->nullable()->after('eval_model_hash');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'implementation_hash')) {
                $table->char('implementation_hash', 40)->nullable()->after('implementation_version');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'picks_hash')) {
                $table->char('picks_hash', 40)->nullable()->after('picks_count');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'universe_count')) {
                $table->unsignedInteger('universe_count')->nullable()->after('picks_hash');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'universe_hash')) {
                $table->char('universe_hash', 40)->nullable()->after('universe_count');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'cutoff_count')) {
                $table->unsignedInteger('cutoff_count')->nullable()->after('universe_hash');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'cutoffs_hash')) {
                $table->char('cutoffs_hash', 40)->nullable()->after('cutoff_count');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'evidence_manifest_hash')) {
                $table->char('evidence_manifest_hash', 40)->nullable()->after('cutoffs_hash');
            }
            if (! Schema::hasColumn('watchlist_bt_eval', 'market_data_lineage_hash')) {
                $table->char('market_data_lineage_hash', 40)->nullable()->after('evidence_manifest_hash');
            }
        });
    }

    private function synchronizeEvaluationUniqueIdentity(): void
    {
        if (! Schema::hasTable('watchlist_bt_eval')) {
            return;
        }
        $columns = ['policy_code','catalog_code','catalog_version','param_id','eval_model','eval_model_hash','implementation_version','implementation_hash','paramset_hash','from_date','to_date'];
        if (DB::connection()->getDriverName() === 'mysql') {
            $database = DB::connection()->getDatabaseName();
            foreach (['UQ_bt_eval_policy_param_window', 'UQ_bt_eval_catalog_param_window'] as $indexName) {
                $exists = DB::selectOne(
                    'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=?',
                    [$database, 'watchlist_bt_eval', $indexName]
                );
                if ((int) ($exists->aggregate ?? 0) > 0) {
                    DB::statement('ALTER TABLE watchlist_bt_eval DROP INDEX '.$indexName);
                }
            }
            DB::statement('ALTER TABLE watchlist_bt_eval ADD UNIQUE KEY UQ_bt_eval_catalog_param_window ('.implode(', ', $columns).')');
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS UQ_bt_eval_policy_param_window');
            DB::statement('DROP INDEX IF EXISTS UQ_bt_eval_catalog_param_window');
            DB::statement('CREATE UNIQUE INDEX UQ_bt_eval_catalog_param_window ON watchlist_bt_eval ('.implode(', ', $columns).')');
        }
    }

    private function versionOosIdentity(): void
    {
        if (! Schema::hasTable('watchlist_bt_oos_eval_ws')) {
            return;
        }
        Schema::table('watchlist_bt_oos_eval_ws', function (Blueprint $table): void {
            foreach ([
                'paramset_hash' => ['char', 40],
                'eval_model_hash' => ['char', 40],
                'implementation_hash' => ['char', 40],
                'is_evidence_manifest_hash' => ['char', 40],
            ] as $column => $definition) {
                if (! Schema::hasColumn('watchlist_bt_oos_eval_ws', $column)) {
                    $table->char($column, $definition[1])->nullable();
                }
            }
            if (! Schema::hasColumn('watchlist_bt_oos_eval_ws', 'implementation_version')) {
                $table->string('implementation_version', 64)->nullable();
            }
        });
    }

    private function versionPicksEvidence(): void
    {
        if (! Schema::hasTable('watchlist_bt_picks_ws')) {
            return;
        }
        Schema::table('watchlist_bt_picks_ws', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_picks_ws', 'eval_id')) {
                $table->unsignedBigInteger('eval_id')->nullable()->after('pick_id');
            }
            if (! Schema::hasColumn('watchlist_bt_picks_ws', 'ticker_code')) {
                $table->string('ticker_code', 16)->nullable()->after('ticker_id');
            }
            if (! Schema::hasColumn('watchlist_bt_picks_ws', 'source_publication_id')) {
                $table->unsignedBigInteger('source_publication_id')->nullable();
                $table->unsignedInteger('source_publication_version')->nullable();
                $table->unsignedBigInteger('source_run_id')->nullable();
            }
            if (! Schema::hasColumn('watchlist_bt_picks_ws', 'row_hash')) {
                $table->char('row_hash', 40)->nullable();
            }
        });
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE watchlist_bt_picks_ws MODIFY ticker_code VARCHAR(16) NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_picks_ws MODIFY source_publication_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_picks_ws MODIFY source_publication_version INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_picks_ws MODIFY source_run_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_picks_ws MODIFY row_hash CHAR(40) NOT NULL');
            $this->prepareMysqlForeignKey(
                'watchlist_bt_picks_ws',
                'eval_id',
                'watchlist_bt_eval',
                'eval_id',
                'FK_bt_picks_eval'
            );
            $this->createMysqlIndexIfMissing(
                'watchlist_bt_picks_ws',
                'UQ_bt_picks_eval_date_ticker',
                'CREATE UNIQUE INDEX UQ_bt_picks_eval_date_ticker ON watchlist_bt_picks_ws (eval_id, asof_eod_date, ticker_id)'
            );
        }
    }

    private function versionUniverseEvidence(): void
    {
        if (! Schema::hasTable('watchlist_bt_universe_ws')) {
            return;
        }
        Schema::table('watchlist_bt_universe_ws', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_universe_ws', 'eval_id')) {
                $table->unsignedBigInteger('eval_id')->nullable()->first();
            }
            if (! Schema::hasColumn('watchlist_bt_universe_ws', 'policy_code')) {
                $table->string('policy_code', 16)->nullable()->after('eval_id');
                $table->unsignedInteger('param_id')->nullable()->after('policy_code');
            }
            if (! Schema::hasColumn('watchlist_bt_universe_ws', 'ticker_code')) {
                $table->string('ticker_code', 16)->nullable()->after('ticker_id');
            }
            if (! Schema::hasColumn('watchlist_bt_universe_ws', 'source_publication_id')) {
                $table->unsignedBigInteger('source_publication_id')->nullable();
                $table->unsignedInteger('source_publication_version')->nullable();
                $table->unsignedBigInteger('source_run_id')->nullable();
            }
            if (! Schema::hasColumn('watchlist_bt_universe_ws', 'row_hash')) {
                $table->char('row_hash', 40)->nullable();
            }
        });
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY reason_code VARCHAR(64) NULL');
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY policy_code VARCHAR(16) NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY ticker_code VARCHAR(16) NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY source_publication_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY source_publication_version INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY source_run_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_universe_ws MODIFY row_hash CHAR(40) NOT NULL');
            $this->prepareMysqlForeignKey(
                'watchlist_bt_universe_ws',
                'eval_id',
                'watchlist_bt_eval',
                'eval_id',
                'FK_bt_universe_eval'
            );
            $this->prepareMysqlForeignKey(
                'watchlist_bt_universe_ws',
                'param_id',
                'watchlist_bt_param_grid',
                'param_id',
                'FK_bt_universe_param'
            );
            $this->replaceMysqlPrimaryKey(
                'watchlist_bt_universe_ws',
                'ALTER TABLE watchlist_bt_universe_ws ADD PRIMARY KEY (eval_id, asof_eod_date, ticker_id)'
            );
        }
    }

    private function versionCutoffEvidence(): void
    {
        if (! Schema::hasTable('watchlist_bt_cutoffs_ws')) {
            return;
        }
        Schema::table('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_cutoffs_ws', 'eval_id')) {
                $table->unsignedBigInteger('eval_id')->nullable()->first();
            }
            if (! Schema::hasColumn('watchlist_bt_cutoffs_ws', 'source_publication_id')) {
                $table->unsignedBigInteger('source_publication_id')->nullable();
                $table->unsignedInteger('source_publication_version')->nullable();
                $table->unsignedBigInteger('source_run_id')->nullable();
            }
            if (! Schema::hasColumn('watchlist_bt_cutoffs_ws', 'row_hash')) {
                $table->char('row_hash', 40)->nullable();
            }
        });
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE watchlist_bt_cutoffs_ws MODIFY source_publication_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_cutoffs_ws MODIFY source_publication_version INT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_cutoffs_ws MODIFY source_run_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE watchlist_bt_cutoffs_ws MODIFY row_hash CHAR(40) NOT NULL');
            $this->prepareMysqlForeignKey(
                'watchlist_bt_cutoffs_ws',
                'eval_id',
                'watchlist_bt_eval',
                'eval_id',
                'FK_bt_cutoffs_eval'
            );
            $this->replaceMysqlPrimaryKey(
                'watchlist_bt_cutoffs_ws',
                'ALTER TABLE watchlist_bt_cutoffs_ws ADD PRIMARY KEY (eval_id, policy_code, param_id, asof_eod_date)'
            );
        }
    }

    private function versionParamsetIdentity(): void
    {
        if (! Schema::hasTable('watchlist_param_sets')) {
            return;
        }
        Schema::table('watchlist_param_sets', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_param_sets', 'params_hash')) {
                $table->char('params_hash', 40)->nullable()->after('params_json');
            }
            if (! Schema::hasColumn('watchlist_param_sets', 'eval_model')) {
                $table->string('eval_model', 96)->nullable()->after('params_hash');
            }
            if (! Schema::hasColumn('watchlist_param_sets', 'eval_model_hash')) {
                $table->char('eval_model_hash', 40)->nullable()->after('eval_model');
            }
            if (! Schema::hasColumn('watchlist_param_sets', 'implementation_version')) {
                $table->string('implementation_version', 64)->nullable()->after('eval_model_hash');
            }
            if (! Schema::hasColumn('watchlist_param_sets', 'implementation_hash')) {
                $table->char('implementation_hash', 40)->nullable()->after('implementation_version');
            }
        });
        if (DB::connection()->getDriverName() === 'mysql') {
            $this->createMysqlIndexIfMissing(
                'watchlist_param_sets',
                'UQ_param_policy_version_schema_hash',
                'CREATE UNIQUE INDEX UQ_param_policy_version_schema_hash ON watchlist_param_sets (policy_code, policy_version, schema_version, params_hash)'
            );
        }
    }

    private function backfillParamsetIdentity(): void
    {
        if (! Schema::hasTable('watchlist_param_sets') || ! Schema::hasColumn('watchlist_param_sets', 'params_hash')) {
            return;
        }
        foreach (DB::table('watchlist_param_sets')->orderBy('param_set_id')->get() as $row) {
            $paramsJson = (string) $row->params_json;
            $payload = json_decode($paramsJson, true);
            if (! is_array($payload)) {
                throw new RuntimeException('WS_C171_PARAMSET_IDENTITY_BACKFILL_INVALID_JSON: param_set_id='.(int) $row->param_set_id);
            }
            $canonical = $this->canonicalJson($payload);
            $evalModel = $this->evalModelFromCanonicalPayload($payload);
            DB::table('watchlist_param_sets')->where('param_set_id', (int) $row->param_set_id)->update([
                'params_json' => $canonical,
                'params_hash' => sha1($canonical),
                'eval_model' => $evalModel,
                'eval_model_hash' => sha1($evalModel),
                'implementation_version' => 'WS_CANONICAL_IS_C171_V1',
                'implementation_hash' => sha1('WS_CANONICAL_IS_C171_V1|PLAN_RECOMMENDATION_CONFIRM_REPLAY|PUBLISHED_EOD|NO_FUTURE_ROUTING'),
            ]);
        }
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE watchlist_param_sets MODIFY params_hash CHAR(40) NOT NULL');
            DB::statement('ALTER TABLE watchlist_param_sets MODIFY eval_model VARCHAR(96) NOT NULL');
            DB::statement('ALTER TABLE watchlist_param_sets MODIFY eval_model_hash CHAR(40) NOT NULL');
            DB::statement('ALTER TABLE watchlist_param_sets MODIFY implementation_version VARCHAR(64) NOT NULL');
            DB::statement('ALTER TABLE watchlist_param_sets MODIFY implementation_hash CHAR(40) NOT NULL');
        }
    }


    private function evalModelFromCanonicalPayload(array $payload): string
    {
        $runtime = $this->unwrapAuditValue($payload);
        $backtest = is_array($runtime['backtest'] ?? null) ? $runtime['backtest'] : [];
        $slip = rtrim(rtrim(number_format((float) ($backtest['slippage_entry_pct'] ?? 0.0), 6, '.', ''), '0'), '.');

        return sprintf(
            'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=%d;FEE=%s;SLIP=%s;GAP=OPEN;PX=IDX_BANDS',
            (int) ($backtest['holding_days'] ?? 5),
            (string) ($backtest['fee_model'] ?? 'IDR_FIXED'),
            $slip === '' ? '0' : $slip
        );
    }

    private function unwrapAuditValue($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_key_exists('value', $value)) {
            $allowed = ['value', 'origin', 'status', 'bt_target', 'rationale', 'change_triggers'];
            if (count(array_diff(array_keys($value), $allowed)) === 0) {
                return $this->unwrapAuditValue($value['value']);
            }
        }
        $result = [];
        foreach ($value as $key => $item) {
            $result[$key] = $this->unwrapAuditValue($item);
        }

        return $result;
    }


    /**
     * Align a child FK column to the exact physical type of the parent column.
     *
     * Historical owner DDL defines some identifiers as signed BIGINT/INT,
     * while older Laravel migrations use unsigned variants. MySQL requires
     * identical numeric signedness for foreign keys. Read the actual schema
     * instead of assuming either historical definition. This also makes the
     * migration safe to retry after MySQL auto-committed a partial DDL run.
     */
    private function prepareMysqlForeignKey(
        string $childTable,
        string $childColumn,
        string $parentTable,
        string $parentColumn,
        string $constraintName
    ): void {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->ensureMysqlInnoDb($parentTable);
        $this->ensureMysqlInnoDb($childTable);

        $parentType = $this->mysqlColumnType($parentTable, $parentColumn);
        $childType = $this->mysqlColumnType($childTable, $childColumn);
        if ($parentType === null || $childType === null) {
            throw new RuntimeException(
                'WS_C171_FOREIGN_KEY_COLUMN_MISSING: '.$childTable.'.'.$childColumn
                .' -> '.$parentTable.'.'.$parentColumn
            );
        }

        $constraintExists = $this->mysqlForeignKeyExists($childTable, $constraintName);
        if (strtolower($childType) !== strtolower($parentType)) {
            if ($constraintExists) {
                DB::statement(
                    'ALTER TABLE `'.$childTable.'` DROP FOREIGN KEY `'.$constraintName.'`'
                );
                $constraintExists = false;
            }
            DB::statement(
                'ALTER TABLE `'.$childTable.'` MODIFY `'.$childColumn.'` '.$parentType.' NOT NULL'
            );
        } else {
            DB::statement(
                'ALTER TABLE `'.$childTable.'` MODIFY `'.$childColumn.'` '.$childType.' NOT NULL'
            );
        }

        if (! $this->mysqlReferencedColumnIsIndexed($parentTable, $parentColumn)) {
            $indexName = 'IDX_C171_'.$parentTable.'_'.$parentColumn;
            $this->createMysqlIndexIfMissing(
                $parentTable,
                $indexName,
                'CREATE INDEX `'.$indexName.'` ON `'.$parentTable.'` (`'.$parentColumn.'`)'
            );
        }

        if (! $constraintExists) {
            try {
                DB::statement(
                    'ALTER TABLE `'.$childTable.'` ADD CONSTRAINT `'.$constraintName.'` '
                    .'FOREIGN KEY (`'.$childColumn.'`) REFERENCES `'.$parentTable.'` (`'.$parentColumn.'`)'
                );
            } catch (\Throwable $exception) {
                throw new RuntimeException(
                    'WS_C171_FOREIGN_KEY_CREATE_FAILED: '.$constraintName
                    .' child_type='.$this->mysqlColumnType($childTable, $childColumn)
                    .' parent_type='.$this->mysqlColumnType($parentTable, $parentColumn)
                    .' child_engine='.$this->mysqlTableEngine($childTable)
                    .' parent_engine='.$this->mysqlTableEngine($parentTable)
                    .' original='.$exception->getMessage(),
                    0,
                    $exception
                );
            }
        }
    }

    private function ensureMysqlInnoDb(string $table): void
    {
        $engine = $this->mysqlTableEngine($table);
        if ($engine !== null && strtolower($engine) !== 'innodb') {
            DB::statement('ALTER TABLE `'.$table.'` ENGINE=InnoDB');
        }
    }

    private function mysqlTableEngine(string $table): ?string
    {
        $row = DB::selectOne(
            'SELECT ENGINE AS engine FROM information_schema.TABLES '
            .'WHERE TABLE_SCHEMA=? AND TABLE_NAME=?',
            [DB::connection()->getDatabaseName(), $table]
        );

        return $row === null ? null : (string) $row->engine;
    }

    private function mysqlColumnType(string $table, string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS column_type FROM information_schema.COLUMNS '
            .'WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?',
            [DB::connection()->getDatabaseName(), $table, $column]
        );

        return $row === null ? null : (string) $row->column_type;
    }

    private function mysqlForeignKeyExists(string $table, string $constraintName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.REFERENTIAL_CONSTRAINTS '
            .'WHERE CONSTRAINT_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME=?',
            [DB::connection()->getDatabaseName(), $table, $constraintName]
        );

        return (int) ($row->aggregate ?? 0) > 0;
    }

    private function mysqlReferencedColumnIsIndexed(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
            .'WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=? AND SEQ_IN_INDEX=1',
            [DB::connection()->getDatabaseName(), $table, $column]
        );

        return (int) ($row->aggregate ?? 0) > 0;
    }

    private function mysqlIndexExists(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
            .'WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=?',
            [DB::connection()->getDatabaseName(), $table, $indexName]
        );

        return (int) ($row->aggregate ?? 0) > 0;
    }

    private function createMysqlIndexIfMissing(string $table, string $indexName, string $statement): void
    {
        if (! $this->mysqlIndexExists($table, $indexName)) {
            DB::statement($statement);
        }
    }

    private function mysqlPrimaryKeyExists(string $table): bool
    {
        return $this->mysqlIndexExists($table, 'PRIMARY');
    }

    private function replaceMysqlPrimaryKey(string $table, string $addStatement): void
    {
        if ($this->mysqlPrimaryKeyExists($table)) {
            DB::statement('ALTER TABLE `'.$table.'` DROP PRIMARY KEY');
        }
        DB::statement($addStatement);
    }

    private function createMysqlOfficialEvidenceGuards(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        $this->dropMysqlOfficialEvidenceGuards();
        foreach (self::OFFICIAL_EVIDENCE_TABLES as $table => $token) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            DB::unprepared(
                'CREATE TRIGGER trg_'.$token.'_no_update BEFORE UPDATE ON '.$table.' FOR EACH ROW '
                ."BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '".$table." is immutable official evidence (UPDATE blocked)'; END"
            );
            DB::unprepared(
                'CREATE TRIGGER trg_'.$token.'_no_delete BEFORE DELETE ON '.$table.' FOR EACH ROW '
                ."BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '".$table." is immutable official evidence (DELETE blocked)'; END"
            );
        }
    }

    private function dropMysqlOfficialEvidenceGuards(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        foreach (self::OFFICIAL_EVIDENCE_TABLES as $token) {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_'.$token.'_no_update');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_'.$token.'_no_delete');
        }
    }

    private function createMysqlParamsetGuards(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! Schema::hasTable('watchlist_param_sets')) {
            return;
        }
        $this->dropMysqlParamsetGuards();
        DB::unprepared(
            "CREATE TRIGGER ".self::PARAMSET_UPDATE_TRIGGER." BEFORE UPDATE ON watchlist_param_sets FOR EACH ROW
             BEGIN
               IF OLD.status = 'DRAFT' AND NEW.status = 'ACTIVE' AND
                  (SELECT COUNT(*) FROM watchlist_param_sets WHERE policy_code = NEW.policy_code AND status = 'ACTIVE' AND param_set_id <> OLD.param_set_id) > 0 THEN
                 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'watchlist_param_sets allows only one ACTIVE row per policy';
               END IF;
               IF NOT (
                 ((OLD.status = 'DRAFT' AND NEW.status = 'ACTIVE') OR (OLD.status = 'ACTIVE' AND NEW.status = 'DEPRECATED')) AND
                 OLD.policy_code = NEW.policy_code AND OLD.policy_version = NEW.policy_version AND
                 OLD.schema_version = NEW.schema_version AND OLD.hash_contract = NEW.hash_contract AND
                 OLD.provenance_json = NEW.provenance_json AND OLD.params_json = NEW.params_json AND
                 OLD.params_hash = NEW.params_hash AND OLD.eval_model = NEW.eval_model AND
                 OLD.eval_model_hash = NEW.eval_model_hash AND OLD.implementation_version = NEW.implementation_version AND
                 OLD.implementation_hash = NEW.implementation_hash AND OLD.created_at = NEW.created_at
               ) THEN
                 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'watchlist_param_sets payload is immutable; only DRAFT->ACTIVE or ACTIVE->DEPRECATED is allowed';
               END IF;
             END"
        );
        DB::unprepared(
            "CREATE TRIGGER ".self::PARAMSET_DELETE_TRIGGER." BEFORE DELETE ON watchlist_param_sets FOR EACH ROW
             BEGIN
               SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'watchlist_param_sets is immutable history (DELETE blocked)';
             END"
        );
    }

    private function dropMysqlParamsetGuards(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::PARAMSET_UPDATE_TRIGGER);
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::PARAMSET_DELETE_TRIGGER);
    }

    private function canonicalJson(array $payload): string
    {
        return json_encode($this->normalize($payload), JSON_UNESCAPED_SLASHES);
    }

    private function normalize($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) { return $this->normalize($item); }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalize($item);
        }
        return $value;
    }
}
