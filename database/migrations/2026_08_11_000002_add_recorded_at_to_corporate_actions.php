<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * F-028: give the corporate-action root an as-known coordinate.
 *
 * The stage-18 exit gate says an as-known replay "cannot see later identity/status/event/config/
 * factor revisions". The event root could not honour that, and not because a filter was forgotten:
 * market_data_corporate_actions had no knowledge-time column at all, so there was nothing to filter
 * on. Every action was visible to every cutoff.
 *
 * The exposure was total rather than theoretical. All 530 rows were created between 2026-06-07 and
 * 2026-07-30 while the dataset starts 2023-01-02, so a replay set to any cutoff before June 2026 —
 * which is every cutoff inside the dataset — still saw all of them.
 *
 * Backfilling recorded_at from created_at is not an invention. created_at is when the row entered
 * the platform, which is exactly what recorded_at means; the value is the same fact under the name
 * the temporal contract uses. This mirrors 2026_08_08_000001, which seeded sector membership
 * recorded_at from COALESCE(created_at, updated_at) for the same reason.
 *
 * What it deliberately does NOT claim: that these timestamps are when the exchange announced the
 * action. They are when this platform learned of it. An as-known replay answers "what did the
 * platform know", so that is the right coordinate — but nobody should read these rows as evidence
 * of announcement timing.
 */
class AddRecordedAtToCorporateActions extends Migration
{
    /**
     * Both tables EventRiskSourceRepository resolves from, not only corporate actions.
     *
     * market_data_trading_status_events has the same hole. TemporalTradingStatusRepository reads
     * md_trading_status_revisions and does honour a cutoff, but the event-risk path reads this
     * legacy table directly and did not — so status leaked through the same gate by a second route.
     * Fixing only the corporate-action half would leave the exit gate violated.
     */
    private $tables = [
        'market_data_corporate_actions' => 'idx_corporate_action_known_at',
        'market_data_trading_status_events' => 'idx_trading_status_event_known_at',
    ];

    public function up()
    {
        foreach ($this->tables as $tableName => $indexName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'recorded_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dateTime('recorded_at')->nullable()->after('source_ref');
                });
            }

            DB::table($tableName)
                ->whereNull('recorded_at')
                ->update(['recorded_at' => DB::raw('created_at')]);

            $this->createIndexIfMissing(
                $tableName,
                $indexName,
                'CREATE INDEX '.$indexName.' ON '.$tableName.' (recorded_at, ticker_id)'
            );
        }
    }

    public function down()
    {
        foreach ($this->tables as $tableName => $indexName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            try {
                DB::statement('DROP INDEX '.$indexName.' ON '.$tableName);
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$indexName);
                } catch (\Throwable $ignored) {
                }
            }

            if (Schema::hasColumn($tableName, 'recorded_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('recorded_at');
                });
            }
        }
    }

    private function createIndexIfMissing($table, $indexName, $statement)
    {
        try {
            $rows = DB::select(
                'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
                .'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $indexName]
            );

            if (isset($rows[0]) && (int) $rows[0]->aggregate > 0) {
                return;
            }
        } catch (\Throwable $e) {
            // information_schema is unavailable on SQLite; fall through and let CREATE INDEX decide.
        }

        try {
            DB::statement($statement);
        } catch (\Throwable $e) {
            // Already present on environments created from the locked SQL schema.
        }
    }
}
