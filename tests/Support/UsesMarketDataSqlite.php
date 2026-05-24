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
        config()->set('market_data.source.api.timeout_seconds', 20);
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
            $table->increments('ticker_id');
            $table->string('ticker_code', 10);
            $table->string('company_name', 255)->default('');
            $table->string('company_logo', 255)->nullable();
            $table->date('listed_date')->nullable();
            $table->date('delisted_date')->nullable();
            $table->string('board_code', 10)->nullable();
            $table->string('exchange_code', 10)->nullable();
            $table->integer('is_active')->default(1);
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->unique('ticker_code', 'ticker_code');
        });

        $schema->create('market_calendar', function (Blueprint $table) {
            $table->date('cal_date')->primary();
            $table->boolean('is_trading_day')->default(true);
            $table->string('holiday_name', 120)->nullable();
            $table->string('session_open_time', 5)->nullable();
            $table->string('session_close_time', 5)->nullable();
            $table->text('breaks_json')->nullable();
            $table->string('source', 120)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->index(['is_trading_day', 'cal_date'], 'market_calendar_trading_idx');
        });

        $schema->create('market_benchmarks', function (Blueprint $table) {
            $table->bigIncrements('benchmark_id');
            $table->string('benchmark_code', 32);
            $table->string('benchmark_name', 120);
            $table->string('provider', 64);
            $table->string('provider_symbol', 64);
            $table->string('instrument_type', 32);
            $table->integer('is_active')->default(1);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->unique('benchmark_code', 'uq_market_benchmarks_code');
            $table->index(['provider', 'provider_symbol'], 'idx_market_benchmarks_provider_symbol');
            $table->index(['is_active', 'benchmark_code'], 'idx_market_benchmarks_active_code');
        });

        $schema->create('market_benchmark_bars', function (Blueprint $table) {
            $table->bigIncrements('benchmark_bar_id');
            $table->string('benchmark_code', 32);
            $table->date('trade_date');
            $table->decimal('open_price', 20, 4);
            $table->decimal('high_price', 20, 4);
            $table->decimal('low_price', 20, 4);
            $table->decimal('close_price', 20, 4);
            $table->decimal('adjusted_close', 20, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->string('provider', 64);
            $table->string('provider_symbol', 64);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->unique(['benchmark_code', 'trade_date'], 'uq_market_benchmark_bars_code_date');
            $table->index(['benchmark_code', 'trade_date'], 'idx_market_benchmark_bars_code_date');
            $table->index(['provider', 'provider_symbol'], 'idx_market_benchmark_bars_provider_symbol');
        });

        $schema->create('market_benchmark_indicators', function (Blueprint $table) {
            $table->bigIncrements('benchmark_indicator_id');
            $table->string('benchmark_code', 32);
            $table->date('trade_date');
            $table->decimal('roc_20', 20, 10)->nullable();
            $table->decimal('ma20', 20, 4)->nullable();
            $table->decimal('ma50', 20, 4)->nullable();
            $table->integer('is_valid')->default(0);
            $table->string('invalid_reason_code')->nullable();
            $table->string('indicator_set_version', 64);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->unique(['benchmark_code', 'trade_date', 'indicator_set_version'], 'uq_market_benchmark_indicators_code_date_version');
            $table->index(['benchmark_code', 'trade_date'], 'idx_market_benchmark_indicators_code_date');
        });


        $schema->create('eod_reason_codes', function (Blueprint $table) {
            $table->string('code', 64)->primary();
            $table->string('category', 32);
            $table->string('description', 255);
            $table->string('severity', 16)->default('INFO');
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->index(['category', 'is_active'], 'idx_reason_codes_category_active');
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
            $table->string('source', 32);
            $table->string('request_mode', 32)->nullable();
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
            $table->string('source_file_hash')->nullable();
            $table->string('source_file_hash_algorithm')->nullable();
            $table->bigInteger('source_file_size_bytes')->nullable();
            $table->integer('source_file_row_count')->nullable();
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
            $table->string('promote_mode')->nullable();
            $table->string('publish_target')->nullable();
            $table->string('final_reason_code')->nullable();
            $table->dateTime('sealed_at')->nullable();
            $table->string('sealed_by')->nullable();
            $table->text('seal_note')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->index(['trade_date_requested', 'lifecycle_state'], 'idx_runs_requested_lifecycle');
            $table->index(['trade_date_requested', 'terminal_status'], 'idx_runs_requested_terminal');
            $table->index(['trade_date_effective', 'terminal_status'], 'idx_runs_effective_terminal');
            $table->index(['trade_date_effective', 'publishability_state'], 'idx_runs_effective_publishability');
            $table->index(['quality_gate_state'], 'idx_runs_gate_state');
            $table->index(['coverage_gate_state'], 'idx_runs_coverage_gate_state');
            $table->index(['stage'], 'idx_runs_stage');
            $table->index(['request_mode'], 'idx_runs_request_mode');
            $table->index(['trade_date_effective', 'is_current_publication'], 'idx_runs_trade_date_current_pub');
            $table->index(['trade_date_effective', 'terminal_status', 'publishability_state', 'coverage_gate_state', 'is_current_publication'], 'idx_runs_effective_readable_contract');
            $table->index(['supersedes_run_id'], 'idx_runs_supersedes');
            $table->index(['publication_id'], 'idx_runs_publication_id');
            $table->index(['correction_id'], 'idx_runs_correction_id');
            $table->index(['promote_mode'], 'idx_runs_promote_mode');
            $table->index(['publish_target'], 'idx_runs_publish_target');
            $table->index(['final_reason_code'], 'idx_runs_final_reason_code');
            $table->index(['source_name'], 'idx_runs_source_name');
            $table->index(['source_file_hash'], 'idx_runs_source_file_hash');
            $table->index(['source', 'source_name', 'source_provider', 'source_file_hash'], 'idx_runs_source_identity');
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
            $table->dateTime('created_at');

            $table->index(['run_id', 'event_time'], 'idx_run_events_run_time');
            $table->index(['trade_date_requested', 'event_time'], 'idx_run_events_trade_date_time');
            $table->index(['stage', 'event_time'], 'idx_run_events_stage_time');
            $table->index(['reason_code'], 'idx_run_events_reason_code');
            $table->index(['severity', 'event_time'], 'idx_run_events_severity_time');
        });

        $schema->create('eod_dataset_corrections', function (Blueprint $table) {
            $table->increments('correction_id');
            $table->date('trade_date');
            $table->integer('prior_run_id')->nullable();
            $table->integer('new_run_id')->nullable();
            $table->integer('baseline_publication_id')->nullable();
            $table->integer('replacement_publication_id')->nullable();
            $table->string('correction_reason_code');
            $table->text('correction_reason_note')->nullable();
            $table->string('status');
            $table->string('requested_by')->nullable();
            $table->dateTime('requested_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->unsignedInteger('execution_count')->default(0);
            $table->dateTime('last_executed_at')->nullable();
            $table->dateTime('current_consumed_at')->nullable();
            $table->text('final_outcome_note')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->index(['trade_date', 'status'], 'idx_corr_trade_date_status');
            $table->index(['trade_date', 'status', 'execution_count'], 'idx_corr_trade_date_status_execution');
            $table->index(['prior_run_id'], 'idx_corr_prior_run');
            $table->index(['new_run_id'], 'idx_corr_new_run');
            $table->index(['prior_run_id', 'new_run_id'], 'idx_corr_prior_new_run');
            $table->index(['baseline_publication_id'], 'idx_corr_baseline_publication');
            $table->index(['replacement_publication_id'], 'idx_corr_replacement_publication');
            $table->index(['baseline_publication_id', 'replacement_publication_id'], 'idx_corr_baseline_replacement_publication');
        });

        $schema->create('eod_publications', function (Blueprint $table) {
            $table->increments('publication_id');
            $table->date('trade_date');
            $table->integer('run_id');
            $table->integer('publication_version');
            $table->integer('is_current')->default(0);
            $table->integer('supersedes_publication_id')->nullable();
            $table->integer('previous_publication_id')->nullable();
            $table->integer('replaced_publication_id')->nullable();
            $table->string('seal_state');
            $table->string('bars_batch_hash')->nullable();
            $table->string('indicators_batch_hash')->nullable();
            $table->string('eligibility_batch_hash')->nullable();
            $table->string('source_file_hash')->nullable();
            $table->string('source_file_hash_algorithm')->nullable();
            $table->bigInteger('source_file_size_bytes')->nullable();
            $table->integer('source_file_row_count')->nullable();
            $table->dateTime('sealed_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->unique(['trade_date', 'publication_version'], 'uq_publication_trade_date_version');
            $table->index(['trade_date', 'is_current'], 'idx_publication_trade_date_current');
            $table->index(['trade_date', 'is_current', 'seal_state', 'publication_version', 'run_id'], 'idx_publication_readable_lookup');
            $table->index(['run_id'], 'idx_publication_run');
            $table->index(['run_id', 'trade_date', 'publication_id'], 'idx_publication_run_trade_date');
            $table->index(['supersedes_publication_id'], 'idx_publication_supersedes');
            $table->index(['previous_publication_id'], 'idx_publication_previous');
            $table->index(['replaced_publication_id'], 'idx_publication_replaced');
            $table->index(['source_file_hash'], 'idx_publication_source_file_hash');
            $table->index(['trade_date', 'seal_state', 'sealed_at'], 'idx_publication_trade_date_sealed');
        });

        $schema->create('eod_current_publication_pointer', function (Blueprint $table) {
            $table->date('trade_date')->primary();
            $table->integer('publication_id');
            $table->integer('run_id');
            $table->integer('publication_version');
            $table->dateTime('sealed_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['publication_id'], 'uq_current_publication_pointer_publication');
            $table->index(['run_id'], 'idx_current_publication_pointer_run');
            $table->index(['run_id', 'publication_version'], 'idx_current_publication_pointer_run_version');
        });

        $schema->create('eod_bars', function (Blueprint $table) {
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->decimal('open', 20, 4);
            $table->decimal('high', 20, 4);
            $table->decimal('low', 20, 4);
            $table->decimal('close', 20, 4);
            $table->bigInteger('volume');
            $table->decimal('adj_close', 20, 4)->nullable();
            $table->string('source', 32);
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at');

            $table->primary(['trade_date', 'ticker_id']);
            $table->index(['ticker_id', 'trade_date'], 'idx_eod_bars_ticker_date');
            $table->index(['run_id'], 'idx_eod_bars_run');
            $table->index(['publication_id'], 'idx_eod_bars_publication');
            $table->index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_bars_publication_date_ticker');
        });

        $schema->create('eod_invalid_bars', function (Blueprint $table) {
            $table->increments('invalid_bar_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('run_id');
            $table->string('source', 32);
            $table->string('source_row_ref')->nullable();
            $table->decimal('open', 18, 4)->nullable();
            $table->decimal('high', 18, 4)->nullable();
            $table->decimal('low', 18, 4)->nullable();
            $table->decimal('close', 18, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('adj_close', 20, 4)->nullable();
            $table->string('invalid_reason_code');
            $table->text('invalid_note')->nullable();
            $table->date('loser_of_trade_date')->nullable();
            $table->integer('loser_of_ticker_id')->nullable();
            $table->dateTime('created_at');

            $table->index(['trade_date', 'ticker_id'], 'idx_invalid_bars_trade_date_ticker');
            $table->index(['run_id'], 'idx_invalid_bars_run');
            $table->index(['invalid_reason_code'], 'idx_invalid_bars_reason_code');
            $table->index(['source_row_ref'], 'idx_invalid_bars_source_row_ref');
            $table->index(['loser_of_trade_date', 'loser_of_ticker_id'], 'idx_invalid_bars_duplicate_loser');
        });

        $schema->create('eod_indicators', function (Blueprint $table) {
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->integer('is_valid');
            $table->string('invalid_reason_code')->nullable();
            $table->string('indicator_set_version');
            $table->decimal('dv20_idr', 24, 2)->nullable();
            $table->decimal('atr14_pct', 20, 10)->nullable();
            $table->decimal('vol_ratio', 20, 10)->nullable();
            $table->decimal('roc20', 20, 10)->nullable();
            $table->decimal('hh20', 20, 4)->nullable();
            $table->decimal('ma20', 20, 4)->nullable();
            $table->decimal('ma50', 20, 4)->nullable();
            $table->decimal('close_to_hh20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma50_pct', 20, 10)->nullable();
            $table->decimal('ma20_slope_pct', 20, 10)->nullable();
            $table->decimal('rs_20_vs_ihsg', 20, 10)->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at');

            $table->primary(['trade_date', 'ticker_id']);
            $table->index(['ticker_id', 'trade_date'], 'idx_eod_indicators_ticker_date');
            $table->index(['run_id'], 'idx_eod_indicators_run');
            $table->index(['invalid_reason_code'], 'idx_eod_indicators_invalid_reason');
            $table->index(['publication_id'], 'idx_eod_indicators_publication');
            $table->index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_indicators_publication_date_ticker');
        });

        $schema->create('eod_eligibility', function (Blueprint $table) {
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->integer('eligible');
            $table->string('reason_code')->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at');

            $table->primary(['trade_date', 'ticker_id']);
            $table->index(['ticker_id', 'trade_date'], 'idx_eod_eligibility_ticker_date');
            $table->index(['run_id'], 'idx_eod_eligibility_run');
            $table->index(['reason_code'], 'idx_eod_eligibility_reason');
            $table->index(['publication_id'], 'idx_eod_eligibility_publication');
            $table->index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_eligibility_publication_date_ticker');
        });

        $schema->create('md_replay_daily_metrics', function (Blueprint $table) {
            $table->integer('replay_id');
            $table->date('trade_date');
            $table->date('trade_date_effective')->nullable();
            $table->string('source');
            $table->string('source_mode')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_provider')->nullable();
            $table->integer('source_timeout_seconds')->nullable();
            $table->integer('source_retry_max')->nullable();
            $table->integer('source_attempt_count')->nullable();
            $table->boolean('source_success_after_retry')->nullable();
            $table->boolean('source_retry_exhausted')->nullable();
            $table->integer('source_final_http_status')->nullable();
            $table->string('source_final_reason_code')->nullable();
            $table->string('source_input_file')->nullable();
            $table->string('status');
            $table->string('publishability_state')->nullable();
            $table->integer('publication_id')->nullable();
            $table->integer('publication_run_id')->nullable();
            $table->string('comparison_result');
            $table->string('replay_status')->nullable();
            $table->text('comparison_note')->nullable();
            $table->string('artifact_changed_scope')->nullable();
            $table->string('config_identity')->nullable();
            $table->integer('publication_version')->nullable();
            $table->boolean('is_current_publication')->nullable();
            $table->integer('correction_id')->nullable();
            $table->string('correction_status')->nullable();
            $table->string('correction_outcome')->nullable();
            $table->string('correction_reseal_status')->nullable();
            $table->boolean('correction_publication_switch')->nullable();
            $table->integer('baseline_publication_id')->nullable();
            $table->integer('candidate_publication_id')->nullable();
            $table->integer('expected_correction_id')->nullable();
            $table->string('expected_correction_status')->nullable();
            $table->string('expected_correction_outcome')->nullable();
            $table->string('expected_correction_reseal_status')->nullable();
            $table->boolean('expected_correction_publication_switch')->nullable();
            $table->integer('expected_baseline_publication_id')->nullable();
            $table->integer('expected_candidate_publication_id')->nullable();
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
            $table->string('expected_terminal_status')->nullable();
            $table->string('expected_publishability_state')->nullable();
            $table->date('expected_trade_date_effective')->nullable();
            $table->string('expected_seal_state')->nullable();
            $table->string('expected_source_mode')->nullable();
            $table->string('expected_source_name')->nullable();
            $table->string('expected_source_provider')->nullable();
            $table->integer('expected_source_timeout_seconds')->nullable();
            $table->integer('expected_source_retry_max')->nullable();
            $table->integer('expected_source_attempt_count')->nullable();
            $table->boolean('expected_source_success_after_retry')->nullable();
            $table->boolean('expected_source_retry_exhausted')->nullable();
            $table->integer('expected_source_final_http_status')->nullable();
            $table->string('expected_source_final_reason_code')->nullable();
            $table->string('expected_source_input_file')->nullable();
            $table->string('expected_source_file_hash')->nullable();
            $table->string('expected_source_file_hash_algorithm')->nullable();
            $table->integer('expected_source_file_size_bytes')->nullable();
            $table->integer('expected_source_file_row_count')->nullable();
            $table->string('expected_config_identity')->nullable();
            $table->integer('expected_publication_id')->nullable();
            $table->integer('expected_publication_run_id')->nullable();
            $table->integer('expected_publication_version')->nullable();
            $table->boolean('expected_is_current_publication')->nullable();
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
            $table->string('replay_suite')->nullable();
            $table->string('replay_case')->nullable();
            $table->string('fixture_id')->nullable();
            $table->string('fixture_version')->nullable();
            $table->string('fixture_schema_version')->nullable();
            $table->string('fixture_source')->nullable();
            $table->string('fixture_created_at')->nullable();
            $table->integer('mismatch_count')->nullable();
            $table->text('mismatch_reason_codes_json')->nullable();
            $table->text('mismatches_json')->nullable();
            $table->text('expected_context_json')->nullable();
            $table->text('actual_context_json')->nullable();
            $table->text('ignored_volatile_fields_json')->nullable();
            $table->text('deterministic_fields_checked_json')->nullable();
            $table->string('final_reason_code')->nullable();
            $table->dateTime('created_at');

            $table->primary(['replay_id', 'trade_date']);
            $table->index(['replay_id', 'status'], 'idx_replay_daily_status');
            $table->index(['replay_id', 'publishability_state'], 'idx_replay_daily_publishability');
            $table->index(['replay_id', 'publication_id', 'publication_version'], 'idx_replay_daily_publication_identity');
            $table->index(['replay_id', 'trade_date_effective'], 'idx_replay_daily_effective');
            $table->index(['replay_id', 'comparison_result'], 'idx_replay_daily_comparison');
            $table->index(['replay_id', 'replay_status'], 'idx_replay_daily_replay_status');
            $table->index(['replay_id', 'coverage_gate_state'], 'idx_replay_daily_coverage_gate');
            $table->index(['replay_id', 'artifact_changed_scope'], 'idx_replay_daily_artifact_scope');
            $table->index(['replay_id', 'publication_version'], 'idx_replay_daily_publication_version');
            $table->index(['replay_id', 'config_identity'], 'idx_replay_daily_config_identity');
        });

        $schema->create('md_replay_reason_code_counts', function (Blueprint $table) {
            $table->integer('replay_id');
            $table->date('trade_date');
            $table->string('reason_code');
            $table->integer('reason_count');

            $table->primary(['replay_id', 'trade_date', 'reason_code']);
            $table->index(['replay_id', 'reason_code'], 'idx_replay_reason_code');
        });


        $schema->create('md_session_snapshots', function (Blueprint $table) {
            $table->bigIncrements('snapshot_id');
            $table->date('trade_date');
            $table->string('snapshot_slot', 32);
            $table->unsignedBigInteger('ticker_id');
            $table->dateTime('captured_at');
            $table->decimal('last_price', 18, 4)->nullable();
            $table->decimal('prev_close', 18, 4)->nullable();
            $table->decimal('chg_pct', 18, 10)->nullable();
            $table->unsignedBigInteger('volume')->nullable();
            $table->decimal('day_high', 18, 4)->nullable();
            $table->decimal('day_low', 18, 4)->nullable();
            $table->string('source', 32);
            $table->unsignedBigInteger('run_id')->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->string('error_note', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->index(['trade_date', 'snapshot_slot'], 'md_session_snapshots_trade_date_snapshot_slot_index');
            $table->index(['captured_at'], 'md_session_snapshots_captured_at_index');
            $table->unique(['trade_date', 'snapshot_slot', 'ticker_id'], 'md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique');
        });

        $schema->create('eod_bars_history', function (Blueprint $table) {
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->decimal('open', 18, 4)->nullable();
            $table->decimal('high', 18, 4)->nullable();
            $table->decimal('low', 18, 4)->nullable();
            $table->decimal('close', 18, 4)->nullable();
            $table->bigInteger('volume')->nullable();
            $table->decimal('adj_close', 20, 4)->nullable();
            $table->string('source', 32);
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at');

            $table->primary(['publication_id', 'trade_date', 'ticker_id']);
            $table->index(['trade_date'], 'idx_bars_history_trade_date');
            $table->index(['ticker_id', 'trade_date'], 'idx_bars_history_ticker_date');
            $table->index(['run_id'], 'idx_bars_history_run');
        });

        $schema->create('eod_indicators_history', function (Blueprint $table) {
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('is_valid')->nullable();
            $table->string('invalid_reason_code')->nullable();
            $table->string('indicator_set_version')->nullable();
            $table->decimal('dv20_idr', 24, 2)->nullable();
            $table->decimal('atr14_pct', 20, 10)->nullable();
            $table->decimal('vol_ratio', 20, 10)->nullable();
            $table->decimal('roc20', 20, 10)->nullable();
            $table->decimal('hh20', 20, 4)->nullable();
            $table->decimal('ma20', 20, 4)->nullable();
            $table->decimal('ma50', 20, 4)->nullable();
            $table->decimal('close_to_hh20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma50_pct', 20, 10)->nullable();
            $table->decimal('ma20_slope_pct', 20, 10)->nullable();
            $table->decimal('rs_20_vs_ihsg', 20, 10)->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at');

            $table->primary(['publication_id', 'trade_date', 'ticker_id']);
            $table->index(['trade_date'], 'idx_indicators_history_trade_date');
            $table->index(['ticker_id', 'trade_date'], 'idx_indicators_history_ticker_date');
            $table->index(['run_id'], 'idx_indicators_history_run');
        });

        $schema->create('eod_eligibility_history', function (Blueprint $table) {
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('eligible')->nullable();
            $table->string('reason_code')->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at');

            $table->primary(['publication_id', 'trade_date', 'ticker_id']);
            $table->index(['trade_date'], 'idx_eligibility_history_trade_date');
            $table->index(['ticker_id', 'trade_date'], 'idx_eligibility_history_ticker_date');
            $table->index(['run_id'], 'idx_eligibility_history_run');
        });
    }
}
