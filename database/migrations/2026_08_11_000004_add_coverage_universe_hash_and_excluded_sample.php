<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-043 and F-044: the two coverage evidence items `Coverage_Universe_Definition_LOCKED.md:52`
 * requires and the corpus never recorded.
 *
 * `coverage_universe_hash` — the universe was stored as a count and a basis, with no version or
 * hash, so two runs for one trade date could resolve different universes and nothing said which one
 * each used. This is adjacent to `F-006` but not the same thing: a hash makes a shifted denominator
 * detectable, it does not make it stop shifting.
 *
 * `coverage_excluded_sample_json` — the missing sample names the listings with no bar, but the
 * listings excluded from the denominator as verified `NOT_EXPECTED` were only counted. A reader
 * could see how many left and never which ones, so an exclusion could not be checked back against
 * its source evidence.
 */
class AddCoverageUniverseHashAndExcludedSample extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('eod_runs')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('eod_runs', 'coverage_universe_hash')) {
                $table->char('coverage_universe_hash', 64)->nullable()->after('coverage_universe_basis');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_excluded_sample_json')) {
                $table->text('coverage_excluded_sample_json')->nullable()->after('coverage_missing_sample_json');
            }
        });
    }

    public function down()
    {
        if (! Schema::hasTable('eod_runs')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            foreach (['coverage_universe_hash', 'coverage_excluded_sample_json'] as $column) {
                if (Schema::hasColumn('eod_runs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
