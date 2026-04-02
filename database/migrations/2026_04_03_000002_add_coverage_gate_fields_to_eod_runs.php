<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoverageGateFieldsToEodRuns extends Migration
{
    public function up()
    {
        Schema::table('eod_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('eod_runs', 'coverage_universe_count')) {
                $table->integer('coverage_universe_count')->nullable()->after('source');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_available_count')) {
                $table->integer('coverage_available_count')->nullable()->after('coverage_universe_count');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_missing_count')) {
                $table->integer('coverage_missing_count')->nullable()->after('coverage_available_count');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_ratio')) {
                $table->decimal('coverage_ratio', 12, 6)->nullable()->after('coverage_missing_count');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_min_threshold')) {
                $table->decimal('coverage_min_threshold', 12, 6)->nullable()->after('coverage_ratio');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_gate_state')) {
                $table->string('coverage_gate_state', 16)->nullable()->after('coverage_min_threshold');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_threshold_mode')) {
                $table->string('coverage_threshold_mode', 32)->nullable()->after('coverage_gate_state');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_universe_basis')) {
                $table->string('coverage_universe_basis', 64)->nullable()->after('coverage_threshold_mode');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_contract_version')) {
                $table->string('coverage_contract_version', 64)->nullable()->after('coverage_universe_basis');
            }
            if (! Schema::hasColumn('eod_runs', 'coverage_missing_sample_json')) {
                $table->json('coverage_missing_sample_json')->nullable()->after('coverage_contract_version');
            }
        });
    }

    public function down()
    {
        Schema::table('eod_runs', function (Blueprint $table) {
            $columns = [
                'coverage_universe_count',
                'coverage_available_count',
                'coverage_missing_count',
                'coverage_ratio',
                'coverage_min_threshold',
                'coverage_gate_state',
                'coverage_threshold_mode',
                'coverage_universe_basis',
                'coverage_contract_version',
                'coverage_missing_sample_json',
            ];

            $existing = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('eod_runs', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
