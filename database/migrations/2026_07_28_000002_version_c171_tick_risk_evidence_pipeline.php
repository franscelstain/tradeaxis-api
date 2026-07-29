<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VersionC171TickRiskEvidencePipeline extends Migration
{
    private const TABLE = 'watchlist_bt_eval';
    private const INDEX = 'UQ_bt_eval_catalog_param_window';
    private const MYSQL_UPDATE_GUARD = 'trg_wbe_eval_no_update';
    private const LEGACY_PIPELINE_VERSION = 'WS_C171_OFFICIAL_EVIDENCE_PIPELINE_V1';
    private const LEGACY_PIPELINE_HASH = '331906bb7cd0cdb3586ff3493f14217d58abacfe';

    private const INDEX_COLUMNS = [
        'policy_code',
        'catalog_code',
        'catalog_version',
        'param_id',
        'eval_model',
        'eval_model_hash',
        'implementation_version',
        'implementation_hash',
        'evidence_pipeline_version',
        'evidence_pipeline_hash',
        'paramset_hash',
        'from_date',
        'to_date',
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        // A failed first attempt may already have dropped the old identity index
        // and added the nullable columns. Every operation below is therefore
        // intentionally idempotent.
        $this->dropEvaluationIdentityIndex();

        Schema::table(self::TABLE, function (Blueprint $table): void {
            if (! Schema::hasColumn(self::TABLE, 'evidence_pipeline_version')) {
                $table->string('evidence_pipeline_version', 64)
                    ->nullable()
                    ->after('implementation_hash');
            }
            if (! Schema::hasColumn(self::TABLE, 'evidence_pipeline_hash')) {
                $table->char('evidence_pipeline_hash', 40)
                    ->nullable()
                    ->after('evidence_pipeline_version');
            }
        });

        $immutablePayloadBefore = $this->immutablePayloadFingerprint();
        $this->backfillLegacyPipelineIdentity();
        $immutablePayloadAfter = $this->immutablePayloadFingerprint();

        if (! hash_equals($immutablePayloadBefore, $immutablePayloadAfter)) {
            throw new RuntimeException('WS_C171_EVIDENCE_PIPELINE_IMMUTABLE_PAYLOAD_CHANGED');
        }

        $missing = DB::table(self::TABLE)
            ->whereNull('evidence_pipeline_version')
            ->orWhereNull('evidence_pipeline_hash')
            ->orWhere('evidence_pipeline_version', '')
            ->orWhere('evidence_pipeline_hash', '')
            ->count();
        if ($missing > 0) {
            throw new RuntimeException('WS_C171_EVIDENCE_PIPELINE_BACKFILL_FAILED');
        }

        $this->createEvaluationIdentityIndex();
    }

    public function down(): void
    {
        // Official evaluation history is immutable. Evidence-pipeline identity is
        // intentionally not removed or collapsed during rollback.
    }

    private function backfillLegacyPipelineIdentity(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->performLegacyPipelineBackfill();

            return;
        }

        // MySQL already protects watchlist_bt_eval with a BEFORE UPDATE trigger.
        // Release only that update guard for the shortest possible metadata-only
        // backfill window, then restore it unconditionally. DELETE protection is
        // never removed, and an immutable-payload fingerprint proves that no
        // pre-existing evidence field changed.
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::MYSQL_UPDATE_GUARD);

        try {
            $this->performLegacyPipelineBackfill();
        } finally {
            $this->restoreMysqlEvaluationUpdateGuard();
        }
    }

    private function performLegacyPipelineBackfill(): void
    {
        DB::table(self::TABLE)
            ->where(function ($query): void {
                $query->whereNull('evidence_pipeline_version')
                    ->orWhere('evidence_pipeline_version', '')
                    ->orWhereNull('evidence_pipeline_hash')
                    ->orWhere('evidence_pipeline_hash', '');
            })
            ->update([
                'evidence_pipeline_version' => DB::raw(
                    "CASE WHEN evidence_pipeline_version IS NULL OR evidence_pipeline_version = '' "
                    ."THEN '".self::LEGACY_PIPELINE_VERSION."' ELSE evidence_pipeline_version END"
                ),
                'evidence_pipeline_hash' => DB::raw(
                    "CASE WHEN evidence_pipeline_hash IS NULL OR evidence_pipeline_hash = '' "
                    ."THEN '".self::LEGACY_PIPELINE_HASH."' ELSE evidence_pipeline_hash END"
                ),
            ]);
    }

    private function restoreMysqlEvaluationUpdateGuard(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::MYSQL_UPDATE_GUARD);
        DB::unprepared(
            'CREATE TRIGGER '.self::MYSQL_UPDATE_GUARD.' BEFORE UPDATE ON '.self::TABLE.' FOR EACH ROW '
            ."BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'watchlist_bt_eval is immutable official evidence (UPDATE blocked)'; END"
        );
    }

    private function immutablePayloadFingerprint(): string
    {
        $columns = array_values(array_filter(
            Schema::getColumnListing(self::TABLE),
            static function (string $column): bool {
                return ! in_array($column, [
                    'evidence_pipeline_version',
                    'evidence_pipeline_hash',
                ], true);
            }
        ));
        sort($columns, SORT_STRING);

        $rows = DB::table(self::TABLE)
            ->select($columns)
            ->orderBy('eval_id')
            ->get()
            ->map(static function ($row): array {
                return (array) $row;
            })
            ->all();

        $json = json_encode(
            $rows,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        );
        if ($json === false) {
            throw new RuntimeException('WS_C171_EVIDENCE_PIPELINE_IMMUTABLE_FINGERPRINT_ENCODING_FAILED');
        }

        return sha1($json);
    }

    private function dropEvaluationIdentityIndex(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            if ($this->mysqlIndexExists(self::INDEX)) {
                DB::statement('ALTER TABLE '.self::TABLE.' DROP INDEX '.self::INDEX);
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS '.self::INDEX);
        }
    }

    private function createEvaluationIdentityIndex(): void
    {
        $columns = implode(', ', self::INDEX_COLUMNS);
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            if (! $this->mysqlIndexExists(self::INDEX)) {
                DB::statement(
                    'ALTER TABLE '.self::TABLE.' ADD UNIQUE KEY '.self::INDEX.' ('.$columns.')'
                );
            }

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement(
                'CREATE UNIQUE INDEX IF NOT EXISTS '.self::INDEX.' ON '.self::TABLE.' ('.$columns.')'
            );
        }
    }

    private function mysqlIndexExists(string $indexName): bool
    {
        $database = DB::connection()->getDatabaseName();
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
            .'WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=?',
            [$database, self::TABLE, $indexName]
        );

        return (int) ($exists->aggregate ?? 0) > 0;
    }
}
