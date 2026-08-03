<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visible count of listings whose temporal calendar/status evidence proves a bar was not expected.
 *
 * Owner contract: docs/market_data/book/Coverage_Universe_Definition_LOCKED.md
 *
 * This field must never represent dormancy, provider absence, zero volume, or current
 * inactivity. Those conditions cannot remove a temporal universe member from the denominator.
 */
class AddDormantExclusionCountToEodRuns extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('eod_runs') || Schema::hasColumn('eod_runs', 'coverage_bar_not_expected_count')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            $table->integer('coverage_bar_not_expected_count')->nullable();
        });
    }

    public function down()
    {
        if (! Schema::hasTable('eod_runs') || ! Schema::hasColumn('eod_runs', 'coverage_bar_not_expected_count')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            $table->dropColumn('coverage_bar_not_expected_count');
        });
    }
}
