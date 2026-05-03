<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCorrectionLifecycleContextToReplayMetrics extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'correction_id')) {
                $table->unsignedBigInteger('correction_id')->nullable()->after('is_current_publication');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'correction_status')) {
                $table->string('correction_status', 32)->nullable()->after('correction_id');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'correction_outcome')) {
                $table->string('correction_outcome', 32)->nullable()->after('correction_status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'correction_reseal_status')) {
                $table->string('correction_reseal_status', 64)->nullable()->after('correction_outcome');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'correction_publication_switch')) {
                $table->boolean('correction_publication_switch')->nullable()->after('correction_reseal_status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'baseline_publication_id')) {
                $table->unsignedBigInteger('baseline_publication_id')->nullable()->after('correction_publication_switch');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'candidate_publication_id')) {
                $table->unsignedBigInteger('candidate_publication_id')->nullable()->after('baseline_publication_id');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_correction_id')) {
                $table->unsignedBigInteger('expected_correction_id')->nullable()->after('candidate_publication_id');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_correction_status')) {
                $table->string('expected_correction_status', 32)->nullable()->after('expected_correction_id');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_correction_outcome')) {
                $table->string('expected_correction_outcome', 32)->nullable()->after('expected_correction_status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_correction_reseal_status')) {
                $table->string('expected_correction_reseal_status', 64)->nullable()->after('expected_correction_outcome');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_correction_publication_switch')) {
                $table->boolean('expected_correction_publication_switch')->nullable()->after('expected_correction_reseal_status');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_baseline_publication_id')) {
                $table->unsignedBigInteger('expected_baseline_publication_id')->nullable()->after('expected_correction_publication_switch');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_candidate_publication_id')) {
                $table->unsignedBigInteger('expected_candidate_publication_id')->nullable()->after('expected_baseline_publication_id');
            }
        });
    }

    public function down()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $columns = [
                'correction_id',
                'correction_status',
                'correction_outcome',
                'correction_reseal_status',
                'correction_publication_switch',
                'baseline_publication_id',
                'candidate_publication_id',
                'expected_correction_id',
                'expected_correction_status',
                'expected_correction_outcome',
                'expected_correction_reseal_status',
                'expected_correction_publication_switch',
                'expected_baseline_publication_id',
                'expected_candidate_publication_id',
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
