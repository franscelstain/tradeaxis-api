<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `F-006`: the run had no knowledge coordinate of its own, so its coverage denominator answered
 * "as of whenever this query ran" and moved between runs of the same trade date — recorded as
 * 950 → 949 → 950 across three runs on one execution day, and measured across 202 date/day pairs.
 *
 * The cutoff cannot be borrowed from `started_at`. Identity projection runs as a side effect of the
 * first universe read, so every row it writes carries a `recorded_at` later than the run's start;
 * cutting at `started_at` excluded the entire universe. That was attempted on 2026-08-11, measured
 * as 31 errors and 5 failures, and reverted. The coordinate therefore needs its own column, stamped
 * once after projection has been driven to completion.
 *
 * Nullable on purpose. A null coordinate preserves the historical claim "unconstrained", which is
 * exactly what every run recorded before this column existed actually did; writing a value into
 * those rows would claim a cutoff was honoured when none was. Runtime execution must reject the
 * same legacy run identity rather than resume it without a boundary.
 */
class AddKnowledgeCutoffAtToEodRuns extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('eod_runs') || Schema::hasColumn('eod_runs', 'knowledge_cutoff_at')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            $table->dateTime('knowledge_cutoff_at')->nullable()->after('coverage_universe_hash');
        });
    }

    public function down()
    {
        if (! Schema::hasTable('eod_runs') || ! Schema::hasColumn('eod_runs', 'knowledge_cutoff_at')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            $table->dropColumn('knowledge_cutoff_at');
        });
    }
}
