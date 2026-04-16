<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait UsesMarketDataSqlite
{
    protected string $marketDataSqliteConnection = 'sqlite';

    protected function bootMarketDataSqlite(): void
    {
        config()->set('database.default', $this->marketDataSqliteConnection);
        config()->set("database.connections.{$this->marketDataSqliteConnection}", [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge($this->marketDataSqliteConnection);
        DB::reconnect($this->marketDataSqliteConnection);

        $schema = $this->schema();

        // Untuk test bootstrap, cukup clear schema tanpa setConnectionResolver().
        if (method_exists($schema, 'dropAllTables')) {
            $schema->dropAllTables();
        }

        $this->createMarketDataSqliteSchema();
    }

    protected function tearDownMarketDataSqlite(): void
    {
        DB::disconnect($this->marketDataSqliteConnection);
    }

    protected function schema()
    {
        return Schema::connection($this->marketDataSqliteConnection);
    }

    protected function db()
    {
        return DB::connection($this->marketDataSqliteConnection);
    }

    protected function createMarketDataSqliteSchema(): void
    {
        $schema = $this->schema();

        $schema->create('tickers', function (Blueprint $table) {
            $table->integer('ticker_id')->primary();
            $table->string('ticker_code');
            $table->string('is_active')->nullable();
            $table->date('listed_date')->nullable();
            $table->date('delisted_date')->nullable();
        });

        $schema->create('market_calendar', function (Blueprint $table) {
            $table->date('cal_date')->primary();
            $table->boolean('is_trading_day')->default(true);
            $table->string('market_code', 16)->default('IDX');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $schema->create('eod_runs', function (Blueprint $table) {
            $table->increments('run_id');
            $table->date('trade_date_requested');
            $table->date('trade_date_effective')->nullable();
            $table->string('lifecycle_state')->nullable();
            $table->string('terminal_status')->nullable();
            $table->string('quality_gate_state')->nullable();
            $table->string('publishability_state')->nullable();
            $table->string('stage')->nullable();
            $table->string('source')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_provider')->nullable();
            $table->string('source_input_file')->nullable();
            $table->integer('source_timeout_seconds')->nullable();
            $table->integer('source_retry_max')->nullable();
            $table->integer('source_attempt_count')->nullable();
            $table->integer('source_success_after_retry')->nullable();
            $table->integer('source_retry_exhausted')->nullable();
            $table->integer('source_final_http_status')->nullable();
            $table->string('source_final_reason_code')->nullable();
            $table->integer('coverage_universe_count')->nullable();
            $table->integer('coverage_available_count')->nullable();
            $table->integer('coverage_missing_count')->nullable();
            $table->decimal('coverage_ratio', 12, 6)->nullable();
            $table->decimal('coverage_min_threshold', 12, 6)->nullable();
            $table->string('coverage_gate_state')->nullable();
            $table->string('coverage_threshold_mode')->nullable();
            $table->string('coverage_universe_basis')->nullable();
            $table->string('coverage_contract_version')->nullable();
            $table->text('coverage_missing_sample_json')->nullable();
            $table->integer('bars_rows_written')->nullable();
            $table->integer('indicators_rows_written')->nullable();
            $table->integer('eligibility_rows_written')->nullable();
            $table->integer('invalid_bar_count')->nullable();
            $table->integer('invalid_indicator_count')->nullable();
            $table->integer('hard_reject_count')->nullable();
            $table->integer('warning_count')->nullable();
            $table->text('notes')->nullable();
            $table->string('bars_batch_hash')->nullable();
            $table->string('indicators_batch_hash')->nullable();
            $table->string('eligibility_batch_hash')->nullable();
            $table->string('config_version')->nullable();
            $table->string('config_hash')->nullable();
            $table->string('config_snapshot_ref')->nullable();
            $table->integer('supersedes_run_id')->nullable();
            $table->integer('publication_id')->nullable();
            $table->integer('publication_version')->nullable();
            $table->integer('is_current_publication')->default(0);
            $table->integer('correction_id')->nullable();
            $table->string('final_reason_code')->nullable();
            $table->dateTime('sealed_at')->nullable();
            $table->string('sealed_by')->nullable();
            $table->text('seal_note')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $schema->create('eod_run_events', function (Blueprint $table) {
            $table->increments('event_id');
            $table->integer('run_id');
            $table->date('trade_date_requested');
            $table->dateTime('event_time')->nullable();
            $table->string('stage');
            $table->string('event_type');
            $table->string('severity');
            $table->string('reason_code')->nullable();
            $table->string('message')->nullable();
            $table->text('event_payload_json')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('eod_dataset_corrections', function (Blueprint $table) {
            $table->increments('correction_id');
            $table->date('trade_date');
            $table->integer('prior_run_id')->nullable();
            $table->integer('new_run_id')->nullable();
            $table->string('correction_reason_code');
            $table->text('correction_reason_note')->nullable();
            $table->string('status');
            $table->string('requested_by')->nullable();
            $table->dateTime('requested_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->text('final_outcome_note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $schema->create('eod_publications', function (Blueprint $table) {
            $table->increments('publication_id');
            $table->date('trade_date');
            $table->integer('run_id');
            $table->integer('publication_version');
            $table->integer('is_current')->default(0);
            $table->integer('supersedes_publication_id')->nullable();
            $table->string('seal_state');
            $table->string('bars_batch_hash')->nullable();
            $table->string('indicators_batch_hash')->nullable();
            $table->string('eligibility_batch_hash')->nullable();
            $table->dateTime('sealed_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $schema->create('eod_current_publication_pointer', function (Blueprint $table) {
            $table->date('trade_date')->primary();
            $table->integer('publication_id');
            $table->integer('run_id');
            $table->integer('publication_version');
            $table->dateTime('sealed_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $schema->create('eod_bars', function (Blueprint $table) {
            $table->increments('bar_id');
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->decimal('open', 18, 4);
            $table->decimal('high', 18, 4);
            $table->decimal('low', 18, 4);
            $table->decimal('close', 18, 4);
            $table->bigInteger('volume');
            $table->decimal('adj_close', 18, 4)->nullable();
            $table->string('source')->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('eod_invalid_bars', function (Blueprint $table) {
            $table->increments('invalid_bar_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('run_id');
            $table->string('source')->nullable();
            $table->string('source_row_ref')->nullable();
            $table->decimal('open', 18, 4)->nullable();
            $table->decimal('high', 18, 4)->nullable();
            $table->decimal('low', 18, 4)->nullable();
            $table->decimal('close', 18, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('adj_close', 18, 4)->nullable();
            $table->string('invalid_reason_code');
            $table->text('invalid_note')->nullable();
            $table->date('loser_of_trade_date')->nullable();
            $table->integer('loser_of_ticker_id')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('eod_indicators', function (Blueprint $table) {
            $table->increments('indicator_id');
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->integer('is_valid');
            $table->string('invalid_reason_code')->nullable();
            $table->string('indicator_set_version');
            $table->decimal('dv20_idr', 24, 2)->nullable();
            $table->decimal('atr14_pct', 18, 10)->nullable();
            $table->decimal('vol_ratio', 18, 10)->nullable();
            $table->decimal('roc20', 18, 10)->nullable();
            $table->decimal('hh20', 18, 4)->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('eod_eligibility', function (Blueprint $table) {
            $table->increments('eligibility_id');
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->integer('eligible');
            $table->string('reason_code')->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('md_replay_daily_metrics', function (Blueprint $table) {
            $table->integer('replay_id');
            $table->date('trade_date');
            $table->date('trade_date_effective')->nullable();
            $table->string('source');
            $table->string('status');
            $table->string('comparison_result');
            $table->text('comparison_note')->nullable();
            $table->string('artifact_changed_scope')->nullable();
            $table->string('config_identity')->nullable();
            $table->integer('publication_version')->nullable();
            $table->integer('coverage_universe_count')->nullable();
            $table->integer('coverage_available_count')->nullable();
            $table->integer('coverage_missing_count')->nullable();
            $table->decimal('coverage_ratio', 12, 6)->nullable();
            $table->decimal('coverage_min_threshold', 12, 6)->nullable();
            $table->string('coverage_gate_state')->nullable();
            $table->string('coverage_threshold_mode')->nullable();
            $table->string('coverage_universe_basis')->nullable();
            $table->string('coverage_contract_version')->nullable();
            $table->text('coverage_missing_sample_json')->nullable();
            $table->integer('bars_rows_written')->nullable();
            $table->integer('indicators_rows_written')->nullable();
            $table->integer('eligibility_rows_written')->nullable();
            $table->integer('eligible_count')->nullable();
            $table->integer('invalid_bar_count')->nullable();
            $table->integer('invalid_indicator_count')->nullable();
            $table->integer('warning_count')->nullable();
            $table->integer('hard_reject_count')->nullable();
            $table->string('bars_batch_hash')->nullable();
            $table->string('indicators_batch_hash')->nullable();
            $table->string('eligibility_batch_hash')->nullable();
            $table->string('seal_state');
            $table->dateTime('sealed_at')->nullable();
            $table->string('expected_status')->nullable();
            $table->date('expected_trade_date_effective')->nullable();
            $table->string('expected_seal_state')->nullable();
            $table->string('expected_config_identity')->nullable();
            $table->integer('expected_publication_version')->nullable();
            $table->integer('expected_coverage_universe_count')->nullable();
            $table->integer('expected_coverage_available_count')->nullable();
            $table->integer('expected_coverage_missing_count')->nullable();
            $table->decimal('expected_coverage_ratio', 12, 6)->nullable();
            $table->decimal('expected_coverage_min_threshold', 12, 6)->nullable();
            $table->string('expected_coverage_gate_state')->nullable();
            $table->string('expected_coverage_threshold_mode')->nullable();
            $table->string('expected_coverage_universe_basis')->nullable();
            $table->string('expected_coverage_contract_version')->nullable();
            $table->text('expected_coverage_missing_sample_json')->nullable();
            $table->string('expected_bars_batch_hash')->nullable();
            $table->string('expected_indicators_batch_hash')->nullable();
            $table->string('expected_eligibility_batch_hash')->nullable();
            $table->text('expected_reason_code_counts_json')->nullable();
            $table->text('mismatch_summary')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->primary(['replay_id', 'trade_date']);
        });

        $schema->create('md_replay_reason_code_counts', function (Blueprint $table) {
            $table->integer('replay_id');
            $table->date('trade_date');
            $table->string('reason_code');
            $table->integer('reason_count');
        });

        $schema->create('eod_bars_history', function (Blueprint $table) {
            $table->increments('history_id');
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->decimal('open', 18, 4)->nullable();
            $table->decimal('high', 18, 4)->nullable();
            $table->decimal('low', 18, 4)->nullable();
            $table->decimal('close', 18, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('adj_close', 18, 4)->nullable();
            $table->string('source')->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('eod_indicators_history', function (Blueprint $table) {
            $table->increments('history_id');
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('is_valid')->nullable();
            $table->string('invalid_reason_code')->nullable();
            $table->string('indicator_set_version')->nullable();
            $table->decimal('dv20_idr', 24, 2)->nullable();
            $table->decimal('atr14_pct', 18, 10)->nullable();
            $table->decimal('vol_ratio', 18, 10)->nullable();
            $table->decimal('roc20', 18, 10)->nullable();
            $table->decimal('hh20', 18, 4)->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        $schema->create('eod_eligibility_history', function (Blueprint $table) {
            $table->increments('history_id');
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('eligible')->nullable();
            $table->string('reason_code')->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }
}