<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandReplayExpectedCoverageContext extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_universe_count')) {
                $table->integer('expected_coverage_universe_count')->nullable()->after('expected_publication_version');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_available_count')) {
                $table->integer('expected_coverage_available_count')->nullable()->after('expected_coverage_universe_count');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_missing_count')) {
                $table->integer('expected_coverage_missing_count')->nullable()->after('expected_coverage_available_count');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_ratio')) {
                $table->decimal('expected_coverage_ratio', 12, 6)->nullable()->after('expected_coverage_missing_count');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_min_threshold')) {
                $table->decimal('expected_coverage_min_threshold', 12, 6)->nullable()->after('expected_coverage_ratio');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_gate_state')) {
                $table->string('expected_coverage_gate_state', 16)->nullable()->after('expected_coverage_min_threshold');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_threshold_mode')) {
                $table->string('expected_coverage_threshold_mode', 32)->nullable()->after('expected_coverage_gate_state');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_universe_basis')) {
                $table->string('expected_coverage_universe_basis', 64)->nullable()->after('expected_coverage_threshold_mode');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_contract_version')) {
                $table->string('expected_coverage_contract_version', 64)->nullable()->after('expected_coverage_universe_basis');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_coverage_missing_sample_json')) {
                $table->text('expected_coverage_missing_sample_json')->nullable()->after('expected_coverage_contract_version');
            }
        });
    }

    public function down()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $columns = [
                'expected_coverage_universe_count',
                'expected_coverage_available_count',
                'expected_coverage_missing_count',
                'expected_coverage_ratio',
                'expected_coverage_min_threshold',
                'expected_coverage_gate_state',
                'expected_coverage_threshold_mode',
                'expected_coverage_universe_basis',
                'expected_coverage_contract_version',
                'expected_coverage_missing_sample_json',
            ];

            $existing = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
}
