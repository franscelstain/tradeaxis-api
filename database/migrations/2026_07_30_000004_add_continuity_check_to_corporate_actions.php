<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verdict of checking a recorded corporate action against the actual price series.
 *
 * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
 *
 * The action type dictionary states an expected continuity impact. Price-series behavior is
 * diagnostic evidence only: it may contaminate or quarantine, but it cannot verify an event,
 * activate a factor, dismiss an authoritative event, or authorize history mutation.
 */
class AddContinuityCheckToCorporateActions extends Migration
{
    public function up()
    {
        $table = config('market_data.event_risk.corporate_actions_table', 'market_data_corporate_actions');

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            // Diagnostic only: GAP_BEYOND_EXCHANGE_BAND, GAP_AMBIGUOUS,
            // NO_MATERIAL_GAP, or NOT_CHECKED.
            if (! Schema::hasColumn($table, 'continuity_check_status')) {
                $blueprint->string('continuity_check_status', 32)->nullable();
            }
            if (! Schema::hasColumn($table, 'observed_gap_pct')) {
                $blueprint->decimal('observed_gap_pct', 12, 6)->nullable();
            }
            if (! Schema::hasColumn($table, 'continuity_checked_at')) {
                $blueprint->dateTime('continuity_checked_at')->nullable();
            }
        });
    }

    public function down()
    {
        $table = config('market_data.event_risk.corporate_actions_table', 'market_data_corporate_actions');

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            foreach (['continuity_checked_at', 'observed_gap_pct', 'continuity_check_status'] as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $blueprint->dropColumn($column);
                }
            }
        });
    }
}
