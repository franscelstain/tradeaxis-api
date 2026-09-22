<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-MD-B18-A002-015 G04, MD-S040-R0071: `CoverageGateEvaluator` emits a `coverage_reason_code`
 * (RUN_COVERAGE_NOT_EVALUABLE / COVERAGE_THRESHOLD_MET / RUN_COVERAGE_LOW) that neither `eod_runs`
 * nor `md_replay_daily_metrics` had a column for, so every reader reconstructed it from
 * `coverage_gate_state` instead -- a reconstruction that returns COVERAGE_BELOW_THRESHOLD for FAIL,
 * a value the producer never emits for this field (it emits RUN_COVERAGE_LOW). This adds the
 * missing columns so the exact producer value can be persisted and read back losslessly. Existing
 * rows get NULL, not a backfilled reconstruction -- the historical value was never durably recorded
 * as a first-class field (only inside `eod_run_events.event_payload_json`), and this migration does
 * not invent one.
 */
class AddCoverageReasonCodePersistence extends Migration
{
    public function up()
    {
        if (Schema::hasTable('eod_runs')) {
            Schema::table('eod_runs', function (Blueprint $table) {
                if (! Schema::hasColumn('eod_runs', 'coverage_reason_code')) {
                    $table->string('coverage_reason_code', 64)->nullable()->after('coverage_gate_state');
                }
            });
        }

        if (Schema::hasTable('md_replay_daily_metrics')) {
            Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
                if (! Schema::hasColumn('md_replay_daily_metrics', 'coverage_reason_code')) {
                    $table->string('coverage_reason_code', 64)->nullable()->after('coverage_gate_state');
                }
                if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_reason_code')) {
                    $table->string('expected_coverage_reason_code', 64)->nullable()->after('expected_coverage_gate_state');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('eod_runs') && Schema::hasColumn('eod_runs', 'coverage_reason_code')) {
            Schema::table('eod_runs', function (Blueprint $table) {
                $table->dropColumn('coverage_reason_code');
            });
        }

        if (Schema::hasTable('md_replay_daily_metrics')) {
            Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
                foreach (['coverage_reason_code', 'expected_coverage_reason_code'] as $column) {
                    if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
