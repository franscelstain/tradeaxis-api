<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandReplayExpectedContext extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_config_identity')) {
                $table->string('expected_config_identity', 128)->nullable()->after('expected_seal_state');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_publication_version')) {
                $table->unsignedInteger('expected_publication_version')->nullable()->after('expected_config_identity');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_bars_batch_hash')) {
                $table->string('expected_bars_batch_hash', 64)->nullable()->after('expected_publication_version');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_indicators_batch_hash')) {
                $table->string('expected_indicators_batch_hash', 64)->nullable()->after('expected_bars_batch_hash');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_eligibility_batch_hash')) {
                $table->string('expected_eligibility_batch_hash', 64)->nullable()->after('expected_indicators_batch_hash');
            }
            if (! Schema::hasColumn('md_replay_daily_metrics', 'expected_reason_code_counts_json')) {
                $table->text('expected_reason_code_counts_json')->nullable()->after('expected_eligibility_batch_hash');
            }
        });
    }

    public function down()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $columns = [
                'expected_config_identity',
                'expected_publication_version',
                'expected_bars_batch_hash',
                'expected_indicators_batch_hash',
                'expected_eligibility_batch_hash',
                'expected_reason_code_counts_json',
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
