<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReplayDeterminismContextToReplayMetrics extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            $this->stringColumn($table, 'replay_suite', 128);
            $this->stringColumn($table, 'replay_case', 128);
            $this->stringColumn($table, 'fixture_id', 128);
            $this->stringColumn($table, 'fixture_version', 64);
            $this->stringColumn($table, 'fixture_schema_version', 64);
            $this->stringColumn($table, 'fixture_source', 128);
            $this->stringColumn($table, 'fixture_created_at', 64);
            $this->integerColumn($table, 'mismatch_count');
            $this->textColumn($table, 'mismatch_reason_codes_json');
            $this->longTextColumn($table, 'mismatches_json');
            $this->longTextColumn($table, 'expected_context_json');
            $this->longTextColumn($table, 'actual_context_json');
            $this->textColumn($table, 'ignored_volatile_fields_json');
            $this->textColumn($table, 'deterministic_fields_checked_json');
            $this->stringColumn($table, 'final_reason_code', 64);
        });
    }

    public function down()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            foreach ([
                'replay_suite',
                'replay_case',
                'fixture_id',
                'fixture_version',
                'fixture_schema_version',
                'fixture_source',
                'fixture_created_at',
                'mismatch_count',
                'mismatch_reason_codes_json',
                'mismatches_json',
                'expected_context_json',
                'actual_context_json',
                'ignored_volatile_fields_json',
                'deterministic_fields_checked_json',
                'final_reason_code',
            ] as $column) {
                if (Schema::hasColumn('md_replay_daily_metrics', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function stringColumn(Blueprint $table, $name, $length = 255)
    {
        if (! Schema::hasColumn('md_replay_daily_metrics', $name)) {
            $table->string($name, $length)->nullable();
        }
    }

    private function integerColumn(Blueprint $table, $name)
    {
        if (! Schema::hasColumn('md_replay_daily_metrics', $name)) {
            $table->integer($name)->nullable();
        }
    }

    private function textColumn(Blueprint $table, $name)
    {
        if (! Schema::hasColumn('md_replay_daily_metrics', $name)) {
            $table->text($name)->nullable();
        }
    }

    private function longTextColumn(Blueprint $table, $name)
    {
        if (! Schema::hasColumn('md_replay_daily_metrics', $name)) {
            $table->longText($name)->nullable();
        }
    }
}
