<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for corporate action window contamination.
 *
 * Owner contract: docs/market_data/registry/Indicator_Registry_Baseline_LOCKED.md
 * (Amendment 2026-07-29 - Corporate action window contamination)
 *
 * A NULL indicator alone is ambiguous: it may be NULL because history is still short,
 * or because a past corporate action poisoned its dependency window. invalid_reason_code
 * cannot carry both because it reports one cause per row while contamination is evaluated
 * per field. This column records the causing actions as sorted comma-joined
 * ACTION_TYPE_CODE@YYYY-MM-DD tokens.
 *
 * Deliberately excluded from indicators_batch_hash: the contamination decision itself is
 * already hash-protected through invalid_reason_code and the NULL pattern of the indicator
 * fields. Adding this annotation to the hash column list would invalidate every existing
 * sealed publication hash without adding integrity.
 *
 * Appended as the last column rather than placed next to the event-risk fields. Physical
 * column order carries no meaning here because every read is by name, while eod_indicators_history
 * holds tens of millions of rows and a mid-table ADD COLUMN can force a full table rebuild
 * instead of an instant metadata change.
 */
class AddCorporateActionWindowReasonsToIndicators extends Migration
{
    private static $tables = ['eod_indicators', 'eod_indicators_history'];

    public function up()
    {
        foreach (self::$tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'corporate_action_window_reasons')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('corporate_action_window_reasons', 255)->nullable();
            });
        }
    }

    public function down()
    {
        foreach (self::$tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'corporate_action_window_reasons')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('corporate_action_window_reasons');
            });
        }
    }
}
