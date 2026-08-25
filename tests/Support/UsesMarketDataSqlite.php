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
            /*
             * Not CURRENT_TIMESTAMP, unlike the other mirrored tables.
             *
             * `TemporalIdentityRepository::projectTicker` uses this column as the knowledge
             * coordinate of every listing it projects. CURRENT_TIMESTAMP is SQLite's real clock,
             * while the suite freezes Carbon months earlier, so a projected listing was recorded
             * after the run that read it — a run reading data that did not yet exist. Harmless while
             * nothing consulted the coordinate; once `F-006` made the coverage denominator read it,
             * every projected listing fell outside the cutoff and the universe emptied.
             *
             * Production carries no such rows: all 977 tickers were created between 2025-12-15 and
             * 2026-07-14, none ahead of the clock. The fixture is what modelled the impossible.
             */
            $table->dateTime('created_at')->default('2020-01-01 00:00:00');
            $table->dateTime('updated_at')->default('2020-01-01 00:00:00');

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
            $table->string('provenance_tier', 16)->nullable();
            $table->date('reconciled_at')->nullable();
            $table->string('reconciliation_source_ref', 255)->nullable();
        });

        $schema->create('market_data_sectors', function (Blueprint $table) {
            $table->string('sector_code', 8)->primary();
            $table->string('sector_name', 120);
            $table->string('sector_index_code', 32)->nullable();
            $table->string('classification_system', 32)->default('IDX-IC');
            $table->date('effective_from')->default('2021-01-25');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('source_name', 64)->default('idx');
            $table->string('source_ref', 255)->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['classification_system', 'is_active', 'sector_code'], 'idx_market_data_sectors_system_active_code');
            $table->index(['sector_index_code'], 'idx_market_data_sectors_index_code');
        });

        $schema->create('ticker_sector_memberships', function (Blueprint $table) {
            $table->bigIncrements('membership_id');
            $table->unsignedBigInteger('ticker_id');
            // NOT NULL here mirrors 2026_08_10_000001. These four columns carry the authority claim
            // and the as-known coordinate, and two of them compose
            // uq_sector_membership_listing_effective_known — a unique index MySQL cannot enforce over
            // NULLs. Keeping the mirror permissive would let tests pass on rows production rejects.
            $table->unsignedBigInteger('listing_id');
            $table->string('sector_code', 8);
            $table->string('classification_system', 32)->default('IDX-IC');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('source_name', 64);
            $table->string('source_ref', 255)->nullable();
            $table->string('source_authority_class', 32);
            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('supersedes_membership_id')->nullable();
            $table->string('operator_name', 128)->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->unique(['listing_id', 'classification_system', 'effective_from', 'recorded_at'], 'uq_sector_membership_listing_effective_known');
            $table->index(['ticker_id', 'classification_system', 'effective_from', 'effective_to'], 'idx_ticker_sector_membership_ticker_date');
            $table->index(['sector_code', 'classification_system', 'effective_from'], 'idx_ticker_sector_membership_sector_date');
            $table->index(['listing_id', 'classification_system', 'effective_from', 'effective_to'], 'idx_sector_membership_listing_effective');
            $table->index(['recorded_at', 'source_authority_class'], 'idx_sector_membership_known_authority');
            $table->index('supersedes_membership_id', 'idx_sector_membership_supersedes');
        });

        $schema->create('market_data_corporate_actions', function (Blueprint $table) {
            $table->bigIncrements('corporate_action_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->date('action_date');
            $table->string('action_type', 64);
            $table->string('source_name', 64)->default('manual_corporate_action_csv');
            $table->string('source_ref', 255)->nullable();
            // The as-known coordinate from 2026_08_11_000002. Without it in the mirror the
            // knowledge cutoff has nothing to filter on and its guard would pass vacuously.
            $table->dateTime('recorded_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->decimal('price_adjustment_factor', 20, 10)->nullable();
            $table->decimal('volume_adjustment_factor', 20, 10)->nullable();
            $table->date('ex_date')->nullable();
            $table->date('cum_date')->nullable();
            $table->decimal('ratio_from', 20, 6)->nullable();
            $table->decimal('ratio_to', 20, 6)->nullable();
            $table->decimal('dividend_per_share', 20, 4)->nullable();
            $table->string('adjustment_source', 32)->nullable();
            $table->string('adjustment_note', 255)->nullable();
            $table->string('continuity_check_status', 32)->nullable();
            $table->decimal('observed_gap_pct', 12, 6)->nullable();
            $table->dateTime('continuity_checked_at')->nullable();

            $table->unique(['ticker_id', 'action_date', 'action_type', 'source_name'], 'uq_md_corp_action_ticker_date_type_source');
            $table->index(['action_date', 'ticker_id'], 'idx_md_corp_action_date_ticker');
            $table->index(['action_type', 'action_date'], 'idx_md_corp_action_type_date');
            $table->index(['ticker_id', 'ex_date'], 'idx_md_corp_action_ex_date');
        });

        $schema->create('market_data_corporate_action_types', function (Blueprint $table) {
            $table->string('action_type_code', 64)->primary();
            $table->string('price_continuity_impact', 32);
            $table->string('volume_continuity_impact', 32);
            $table->boolean('share_count_changes')->default(false);
            $table->string('description', 255)->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        $now = date('Y-m-d H:i:s');

        DB::table('market_data_corporate_action_types')->insert(array_map(function ($row) use ($now) {
            return [
                'action_type_code' => $row[0],
                'price_continuity_impact' => $row[1],
                'volume_continuity_impact' => $row[2],
                'share_count_changes' => $row[3],
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, [
            ['STOCK_SPLIT', 'SCALED', 'SCALED', 1],
            ['REVERSE_STOCK_SPLIT', 'SCALED', 'SCALED', 1],
            ['BONUS_SHARE', 'SCALED', 'SCALED', 1],
            ['STOCK_DIVIDEND', 'SCALED', 'SCALED', 1],
            ['MERGER', 'SCALED', 'SCALED', 1],
            ['RIGHTS_ISSUE', 'SCALED', 'NONE', 1],
            ['CASH_DIVIDEND', 'GAP_UNKNOWN_MAGNITUDE', 'NONE', 0],
            ['PRIVATE_PLACEMENT', 'NONE', 'NONE', 1],
            ['NON_PREEMPTIVE_RIGHTS_ISSUE', 'NONE', 'NONE', 1],
            ['WARRANT', 'NONE', 'NONE', 1],
            ['WARRANT_EXERCISE', 'NONE', 'NONE', 1],
            ['MANDATORY_CONVERTIBLE_BOND', 'NONE', 'NONE', 1],
            ['ESOP_MSOP', 'NONE', 'NONE', 1],
            ['IPO', 'NONE', 'NONE', 0],
            ['DELISTING', 'NONE', 'NONE', 0],
            ['PARTIAL_DELISTING', 'NONE', 'NONE', 0],
            ['PARTIAL_RELISTING', 'NONE', 'NONE', 0],
            ['CAPITAL_DEFICIENCY', 'NONE', 'NONE', 0],
            ['TICKER_CODE_CHANGE', 'NONE', 'NONE', 0],
            ['COMPANY_NAME_CHANGE', 'NONE', 'NONE', 0],
        ]));

        $schema->create('market_data_price_scale_breaks', function (Blueprint $table) {
            $table->bigIncrements('price_scale_break_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->date('trade_date');
            $table->decimal('previous_close', 20, 4);
            $table->decimal('open_price', 20, 4);
            $table->decimal('implied_ratio', 20, 10);
            $table->string('ratio_direction', 16);
            $table->decimal('inferred_ratio', 12, 4)->nullable();
            $table->decimal('inferred_ratio_error_pct', 12, 6)->nullable();
            $table->string('break_type', 32);
            $table->string('match_status', 16);
            $table->unsignedBigInteger('matched_corporate_action_id')->nullable();
            $table->string('matched_action_type', 64)->nullable();
            $table->string('review_status', 16)->default('DETECTED');
            $table->string('review_note', 255)->nullable();
            $table->string('reviewed_by', 64)->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('detection_contract_version', 64);
            $table->dateTime('detected_at');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->unique(['ticker_id', 'trade_date'], 'uq_md_price_scale_break_ticker_date');
            $table->index(['trade_date', 'ticker_id'], 'idx_md_price_scale_break_date_ticker');
            $table->index(['match_status', 'review_status'], 'idx_md_price_scale_break_status');
        });

        $schema->create('market_data_trading_status_event_types', function (Blueprint $table) {
            $table->string('event_type_code', 64)->primary();
            $table->string('risk_family', 64);
            $table->string('transition_type', 32);
            $table->string('expected_bar_policy', 32);
            $table->boolean('carries_forward')->default(false);
            $table->string('clears_risk_family', 64)->nullable();
            $table->string('description', 255)->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['risk_family', 'transition_type'], 'idx_md_status_types_family_transition');
            $table->index(['expected_bar_policy'], 'idx_md_status_types_expected_bar_policy');
        });

        DB::table('market_data_trading_status_event_types')->insert([
            ['event_type_code' => 'SUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'IDX suspends ticker trading.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['event_type_code' => 'SUSPENSION_OBSERVED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'OBSERVED', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Source snapshot shows ticker is still suspended, including IDX long-suspension lists; this is not a suspension start date.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['event_type_code' => 'UNSUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SUSPENSION', 'description' => 'IDX reopens ticker trading.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['event_type_code' => 'SPECIAL_MONITORING_START', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Ticker enters special monitoring.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['event_type_code' => 'SPECIAL_MONITORING_END', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SPECIAL_MONITORING', 'description' => 'Ticker exits special monitoring.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['event_type_code' => 'UMA', 'risk_family' => 'UMA', 'transition_type' => 'POINT_IN_TIME', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 0, 'clears_risk_family' => null, 'description' => 'Unusual Market Activity notice.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);

        $schema->create('market_data_trading_status_events', function (Blueprint $table) {
            $table->bigIncrements('trading_status_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->date('trade_date');
            $table->string('event_type_code', 64);
            $table->string('source_name', 64)->default('manual_trading_status_csv');
            $table->string('source_ref', 255)->nullable();
            $table->string('origin_authority_class', 32)->nullable();
            $table->string('source_payload_hash', 64)->nullable();
            $table->string('operator_name', 128)->nullable();
            $table->string('governed_reason_code', 64)->nullable();
            $table->string('authoritative_source_ref', 255)->nullable();
            $table->string('transport_state', 32)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->unique(['ticker_id', 'trade_date', 'event_type_code', 'source_name'], 'uq_md_trading_status_ticker_date_type_source');
            $table->index(['trade_date', 'ticker_id'], 'idx_md_trading_status_date_ticker');
            $table->index(['event_type_code', 'trade_date'], 'idx_md_trading_status_event_type_date');
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
            $table->decimal('ma20_slope_pct', 20, 10)->nullable();
            $table->decimal('close_to_ma20_pct', 20, 10)->nullable();
            $table->decimal('close_to_ma50_pct', 20, 10)->nullable();
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
            // Nullable to match production: MARKET_DATA_DICTIONARY.md records both timestamp
            // columns as DATETIME NULL, and the reason code seed does not supply them.
            $table->dateTime('created_at')->nullable();
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
            // F-043/F-044 — the two Coverage_Universe_Definition_LOCKED.md:52 evidence items the
            // corpus never recorded: which universe produced the denominator, and which listings
            // left it.
            $table->string('coverage_universe_hash', 64)->nullable();
            $table->text('coverage_excluded_sample_json')->nullable();
            // F-006 — the run's own knowledge coordinate, so its denominator is reproducible.
            $table->dateTime('knowledge_cutoff_at')->nullable();
            $table->integer('coverage_available_count')->nullable();
            $table->integer('coverage_missing_count')->nullable();
            $table->integer('coverage_bar_not_expected_count')->nullable();
            $table->integer('coverage_expected_count')->nullable();
            $table->integer('coverage_expectation_unknown_count')->nullable();
            $table->integer('coverage_delivered_count')->nullable();
            $table->integer('coverage_delivered_valid_count')->nullable();
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
            $table->integer('config_snapshot_id')->nullable();
            $table->integer('corpus_admission_decision_id')->nullable();
            $table->string('observation_manifest_hash', 64)->nullable();
            $table->string('price_product_code', 32)->nullable();
            $table->string('price_product_version', 64)->nullable();
            $table->string('factor_set_hash', 64)->nullable();
            $table->date('operational_start_date')->nullable();
            $table->string('freshness_state', 32)->nullable();
            $table->date('latest_expected_trade_date')->nullable();
            $table->date('latest_acquired_trade_date')->nullable();
            $table->date('latest_canonicalized_trade_date')->nullable();
            $table->date('latest_readable_trade_date')->nullable();
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
            $table->integer('config_snapshot_id')->nullable();
            $table->integer('factor_set_id')->nullable();
            $table->string('factor_set_hash', 64)->nullable();
            $table->string('observation_manifest_hash', 64)->nullable();
            // What the seal covers — FULL, or ANALYTICAL_ONLY when the run recomputed analytics
            // over existing bars and had no acquisition provenance to carry forward.
            $table->string('seal_provenance_scope', 32)->nullable();
            $table->string('publication_manifest_hash', 64)->nullable();
            $table->string('price_product_code', 32)->nullable();
            $table->string('price_product_version', 64)->nullable();
            $table->string('read_model_version', 64)->nullable();
            $table->string('readiness_state', 32)->nullable();
            $table->string('source_scale_assessment_set_hash', 64)->nullable();
            $table->string('market_structure_revision_set_hash', 64)->nullable();
            $table->string('factor_decision_set_hash', 64)->nullable();
            $table->integer('corpus_admission_decision_id')->nullable();
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
            $table->integer('listing_id')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->decimal('previous_close', 20, 4)->nullable();
            $table->decimal('traded_value_idr_actual', 24, 2)->nullable();
            $table->bigInteger('trade_count_actual')->nullable();
            $table->string('board_code', 16)->nullable();
            $table->string('session_code', 32)->nullable();
            $table->dateTime('source_timestamp')->nullable();
            $table->dateTime('acquired_at')->nullable();
            $table->string('canonicalization_version', 64)->nullable();
            $table->string('price_product_code', 32)->nullable();
            $table->string('quality_state', 32)->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->string('source_scale_state', 32)->nullable();
            $table->integer('source_scale_assessment_id')->nullable();
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
            $table->integer('listing_id')->nullable();
            $table->integer('source_observation_id')->nullable();
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
            $table->string('sector_code', 8)->nullable();
            $table->decimal('dv20_idr', 24, 2)->nullable();
            $table->decimal('atr14_pct', 20, 10)->nullable();
            $table->decimal('vol_ratio', 20, 10)->nullable();
            $table->decimal('roc5', 20, 10)->nullable();
            $table->decimal('roc10', 20, 10)->nullable();
            $table->decimal('roc20', 20, 10)->nullable();
            $table->decimal('hh20', 20, 4)->nullable();
            $table->decimal('ll20', 20, 4)->nullable();
            $table->decimal('ma20', 20, 4)->nullable();
            $table->decimal('ma50', 20, 4)->nullable();
            $table->decimal('close_to_hh20_pct', 20, 10)->nullable();
            $table->decimal('close_to_ll20_pct', 20, 10)->nullable();
            $table->decimal('range_20_pct', 20, 10)->nullable();
            $table->decimal('range_position_20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma50_pct', 20, 10)->nullable();
            $table->decimal('ma20_slope_pct', 20, 10)->nullable();
            $table->decimal('rs_20_vs_ihsg', 20, 10)->nullable();
            $table->decimal('sector_roc20', 20, 10)->nullable();
            $table->decimal('rs_20_vs_sector', 20, 10)->nullable();
            $table->decimal('sector_rs_20_vs_ihsg', 20, 10)->nullable();
            $table->integer('corporate_action_flag')->nullable();
            $table->string('corporate_action_types', 255)->nullable();
            $table->string('trading_status_code', 64)->nullable();
            $table->integer('is_suspended')->nullable();
            $table->integer('is_uma')->nullable();
            $table->integer('event_risk_flag')->nullable();
            $table->string('event_risk_reasons', 255)->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at');
            $table->string('corporate_action_window_reasons', 255)->nullable();
            $table->integer('listing_id')->nullable();
            $table->string('formula_version', 64)->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->integer('factor_set_id')->nullable();
            $table->string('factor_set_hash', 64)->nullable();
            $table->string('price_product_code', 32)->nullable();
            $table->string('price_product_version', 64)->nullable();
            $table->integer('sector_membership_id')->nullable();
            $table->decimal('adv20_traded_value_idr_actual', 24, 2)->nullable();
            $table->decimal('adv20_close_volume_proxy_idr', 24, 2)->nullable();
            $table->decimal('atr14', 20, 10)->nullable();
            $table->string('atr_state_ref', 128)->nullable();
            $table->text('null_reasons_json')->nullable();

            $table->primary(['trade_date', 'ticker_id']);
            $table->index(['ticker_id', 'trade_date'], 'idx_eod_indicators_ticker_date');
            $table->index(['run_id'], 'idx_eod_indicators_run');
            $table->index(['invalid_reason_code'], 'idx_eod_indicators_invalid_reason');
            $table->index(['publication_id'], 'idx_eod_indicators_publication');
            $table->index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_indicators_publication_date_ticker');
            $table->index(['sector_code', 'trade_date'], 'idx_eod_indicators_sector_date');
            $table->index(['event_risk_flag', 'trade_date'], 'idx_eod_indicators_event_risk_date');
        });

        $schema->create('eod_eligibility', function (Blueprint $table) {
            $table->date('trade_date');
            $table->integer('ticker_id');
            $table->integer('eligible');
            $table->string('reason_code')->nullable();
            $table->integer('run_id');
            $table->integer('publication_id');
            $table->dateTime('created_at');
            $table->integer('listing_id')->nullable();
            $table->string('universe_membership_state', 32)->nullable();
            $table->string('bar_expectation_state', 32)->nullable();
            $table->string('delivery_state', 32)->nullable();
            $table->string('canonical_quality_state', 32)->nullable();
            $table->string('liquidity_state', 32)->nullable();
            $table->string('temporal_status_state', 32)->nullable();
            $table->integer('trading_status_revision_id')->nullable();
            $table->integer('trading_status_source_observation_id')->nullable();
            $table->string('event_risk_state', 32)->nullable();
            $table->text('eligibility_reasons_json')->nullable();
            $table->string('market_structure_resolution_state', 48)->nullable();
            $table->integer('price_band_revision_id')->nullable();
            $table->integer('minimum_price_revision_id')->nullable();
            $table->integer('tick_size_revision_id')->nullable();
            $table->integer('config_snapshot_id')->nullable();

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
            $table->integer('listing_id')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->decimal('previous_close', 20, 4)->nullable();
            $table->decimal('traded_value_idr_actual', 24, 2)->nullable();
            $table->bigInteger('trade_count_actual')->nullable();
            $table->string('board_code', 16)->nullable();
            $table->string('session_code', 32)->nullable();
            $table->dateTime('source_timestamp')->nullable();
            $table->dateTime('acquired_at')->nullable();
            $table->string('canonicalization_version', 64)->nullable();
            $table->string('price_product_code', 32)->nullable();
            $table->string('quality_state', 32)->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->string('source_scale_state', 32)->nullable();
            $table->integer('source_scale_assessment_id')->nullable();
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
            $table->string('sector_code', 8)->nullable();
            $table->decimal('dv20_idr', 24, 2)->nullable();
            $table->decimal('atr14_pct', 20, 10)->nullable();
            $table->decimal('vol_ratio', 20, 10)->nullable();
            $table->decimal('roc5', 20, 10)->nullable();
            $table->decimal('roc10', 20, 10)->nullable();
            $table->decimal('roc20', 20, 10)->nullable();
            $table->decimal('hh20', 20, 4)->nullable();
            $table->decimal('ll20', 20, 4)->nullable();
            $table->decimal('ma20', 20, 4)->nullable();
            $table->decimal('ma50', 20, 4)->nullable();
            $table->decimal('close_to_hh20_pct', 20, 10)->nullable();
            $table->decimal('close_to_ll20_pct', 20, 10)->nullable();
            $table->decimal('range_20_pct', 20, 10)->nullable();
            $table->decimal('range_position_20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma20_pct', 20, 10)->nullable();
            $table->decimal('close_vs_ma50_pct', 20, 10)->nullable();
            $table->decimal('ma20_slope_pct', 20, 10)->nullable();
            $table->decimal('rs_20_vs_ihsg', 20, 10)->nullable();
            $table->decimal('sector_roc20', 20, 10)->nullable();
            $table->decimal('rs_20_vs_sector', 20, 10)->nullable();
            $table->decimal('sector_rs_20_vs_ihsg', 20, 10)->nullable();
            $table->integer('corporate_action_flag')->nullable();
            $table->string('corporate_action_types', 255)->nullable();
            $table->string('trading_status_code', 64)->nullable();
            $table->integer('is_suspended')->nullable();
            $table->integer('is_uma')->nullable();
            $table->integer('event_risk_flag')->nullable();
            $table->string('event_risk_reasons', 255)->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at');
            $table->string('corporate_action_window_reasons', 255)->nullable();
            $table->integer('listing_id')->nullable();
            $table->string('formula_version', 64)->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->integer('factor_set_id')->nullable();
            $table->string('factor_set_hash', 64)->nullable();
            $table->string('price_product_code', 32)->nullable();
            $table->string('price_product_version', 64)->nullable();
            $table->integer('sector_membership_id')->nullable();
            $table->decimal('adv20_traded_value_idr_actual', 24, 2)->nullable();
            $table->decimal('adv20_close_volume_proxy_idr', 24, 2)->nullable();
            $table->decimal('atr14', 20, 10)->nullable();
            $table->string('atr_state_ref', 128)->nullable();
            $table->text('null_reasons_json')->nullable();

            $table->primary(['publication_id', 'trade_date', 'ticker_id']);
            $table->index(['trade_date'], 'idx_indicators_history_trade_date');
            $table->index(['ticker_id', 'trade_date'], 'idx_indicators_history_ticker_date');
            $table->index(['run_id'], 'idx_indicators_history_run');
            $table->index(['sector_code', 'trade_date'], 'idx_eod_indicators_history_sector_date');
            $table->index(['event_risk_flag', 'trade_date'], 'idx_eod_indicators_history_event_risk_date');
        });

        $schema->create('eod_eligibility_history', function (Blueprint $table) {
            $table->integer('publication_id');
            $table->date('trade_date')->nullable();
            $table->integer('ticker_id')->nullable();
            $table->integer('eligible')->nullable();
            $table->string('reason_code')->nullable();
            $table->integer('run_id')->nullable();
            $table->dateTime('created_at');
            $table->integer('listing_id')->nullable();
            $table->string('universe_membership_state', 32)->nullable();
            $table->string('bar_expectation_state', 32)->nullable();
            $table->string('delivery_state', 32)->nullable();
            $table->string('canonical_quality_state', 32)->nullable();
            $table->string('liquidity_state', 32)->nullable();
            $table->string('temporal_status_state', 32)->nullable();
            $table->integer('trading_status_revision_id')->nullable();
            $table->integer('trading_status_source_observation_id')->nullable();
            $table->string('event_risk_state', 32)->nullable();
            $table->text('eligibility_reasons_json')->nullable();
            $table->string('market_structure_resolution_state', 48)->nullable();
            $table->integer('price_band_revision_id')->nullable();
            $table->integer('minimum_price_revision_id')->nullable();
            $table->integer('tick_size_revision_id')->nullable();
            $table->integer('config_snapshot_id')->nullable();

            $table->primary(['publication_id', 'trade_date', 'ticker_id']);
            $table->index(['trade_date'], 'idx_eligibility_history_trade_date');
            $table->index(['ticker_id', 'trade_date'], 'idx_eligibility_history_ticker_date');
            $table->index(['run_id'], 'idx_eligibility_history_run');
        });

        $schema->create('md_publication_projection_reconciliations', function (Blueprint $table) {
            $table->bigIncrements('reconciliation_id');
            $table->char('reconciliation_uid', 64)->unique();
            $table->date('trade_date');
            $table->unsignedBigInteger('publication_id')->nullable();
            $table->unsignedBigInteger('run_id')->nullable();
            $table->unsignedInteger('publication_version')->nullable();
            $table->string('pointer_state', 32);
            $table->string('reconciliation_state', 32);

            foreach (['bars', 'indicators', 'eligibility'] as $artifact) {
                $table->unsignedInteger($artifact.'_projection_count')->default(0);
                $table->unsignedInteger($artifact.'_history_count')->default(0);
                $table->unsignedInteger($artifact.'_missing_history_count')->default(0);
                $table->unsignedInteger($artifact.'_missing_projection_count')->default(0);
                $table->unsignedInteger($artifact.'_value_mismatch_count')->default(0);
            }

            $table->unsignedInteger('orphan_projection_row_count')->default(0);
            $table->unsignedInteger('mismatch_count')->default(0);
            $table->text('mismatch_sample_json')->nullable();
            $table->char('reconciliation_hash', 64);
            $table->dateTime('checked_at');
            $table->dateTime('created_at');

            $table->index(['trade_date', 'reconciliation_state'], 'idx_md_pub_proj_recon_date_state');
            $table->index(['publication_id', 'checked_at'], 'idx_md_pub_proj_recon_pub_checked');
            $table->index(['checked_at'], 'idx_md_pub_proj_recon_checked');
        });

        $this->createMarketDataV2SqliteSchema($schema);
        $this->seedMarketDataSectorTaxonomy();
    }

    protected function createMarketDataV2SqliteSchema($schema): void
    {
        $schema->create('md_config_snapshots', function (Blueprint $table) {
            $table->bigIncrements('config_snapshot_id');
            $table->string('snapshot_uid', 64)->unique();
            $table->string('snapshot_schema_version', 32);
            $table->string('serialization_version', 32);
            $table->text('resolved_config_json');
            $table->string('config_hash', 64);
            $table->string('registry_revision', 64);
            $table->dateTime('effective_at');
            $table->dateTime('recorded_at');
            $table->string('build_id', 128)->nullable();
            $table->string('environment_profile', 64);
            $table->string('resolver_version', 64);
            $table->dateTime('created_at');
            $table->index(['config_hash', 'snapshot_schema_version'], 'idx_md_cfg_hash_schema');
            $table->index(['effective_at', 'recorded_at'], 'idx_md_cfg_effective_known');
        });

        $schema->create('md_source_observations', function (Blueprint $table) {
            $table->bigIncrements('source_observation_id');
            $table->string('observation_uid', 64)->unique();
            $table->integer('parent_observation_id')->nullable();
            $table->integer('run_id')->nullable();
            $table->string('attempt_uid', 64);
            $table->string('acquisition_batch_id', 128)->nullable();
            $table->string('acquisition_checkpoint_id', 128)->nullable();
            $table->date('requested_trade_date');
            $table->date('requested_start_date')->nullable();
            $table->date('requested_end_date')->nullable();
            $table->string('source_mode', 32)->nullable();
            $table->string('source_name', 64);
            $table->string('provider', 64)->nullable();
            $table->string('provider_symbol', 128)->nullable();
            $table->integer('provider_mapping_id')->nullable();
            $table->string('mapping_revision', 64)->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->string('sanitized_request_identity', 255);
            $table->integer('response_status')->nullable();
            $table->string('content_type', 128)->nullable();
            $table->dateTime('source_timestamp')->nullable();
            $table->dateTime('acquired_at');
            $table->string('provider_schema_version', 64)->nullable();
            $table->string('schema_fingerprint', 64)->nullable();
            $table->string('adapter_version', 64);
            $table->string('payload_hash', 64)->nullable();
            $table->string('payload_ref', 512)->nullable();
            $table->integer('payload_byte_length')->nullable();
            $table->text('bounded_payload_body')->nullable();
            $table->string('outcome_state', 32);
            $table->string('validation_state', 32)->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->integer('supersedes_observation_id')->nullable();
            $table->dateTime('created_at');
            $table->index(['run_id', 'requested_trade_date'], 'idx_md_obs_run_date');
            $table->index(['provider', 'provider_symbol', 'requested_trade_date'], 'idx_md_obs_provider_symbol_date');
            $table->index(['payload_hash', 'adapter_version'], 'idx_md_obs_payload_adapter');
            $table->index(['outcome_state', 'requested_trade_date'], 'idx_md_obs_outcome_date');
            $table->index(['parent_observation_id', 'outcome_state'], 'idx_md_obs_parent_outcome');
            $table->index(['source_mode', 'requested_start_date', 'requested_end_date'], 'idx_md_obs_mode_range');
            $table->index(['config_snapshot_id', 'mapping_revision'], 'idx_md_obs_config_mapping');
            $table->index(['acquisition_batch_id', 'acquisition_checkpoint_id'], 'idx_md_obs_acquisition_identity');
        });

        $schema->create('md_source_observation_rows', function (Blueprint $table) {
            $table->bigIncrements('source_observation_row_id');
            $table->integer('source_observation_id');
            $table->integer('capture_observation_id');
            $table->string('source_row_ref', 255);
            $table->integer('listing_id')->nullable();
            $table->string('provider', 64)->nullable();
            $table->string('provider_symbol', 128)->nullable();
            $table->integer('provider_mapping_id')->nullable();
            $table->string('mapping_revision', 64)->nullable();
            $table->string('ticker_code', 32);
            $table->date('trade_date');
            $table->dateTime('source_timestamp')->nullable();
            $table->string('open_value', 64);
            $table->string('high_value', 64);
            $table->string('low_value', 64);
            $table->string('close_value', 64);
            $table->string('volume_value', 64);
            $table->string('adj_close_value', 64)->nullable();
            $table->string('row_fingerprint', 64);
            $table->dateTime('created_at');
            $table->unique(['source_observation_id', 'source_row_ref'], 'uq_md_obs_row_observation_ref');
            $table->index(['listing_id', 'trade_date', 'source_observation_row_id'], 'idx_md_obs_row_listing_date');
            $table->index(['provider', 'provider_symbol', 'trade_date', 'source_observation_row_id'], 'idx_md_obs_row_provider_date');
        });

        $schema->create('md_source_observation_revision_comparisons', function (Blueprint $table) {
            $table->bigIncrements('source_observation_comparison_id');
            $table->string('comparison_uid', 64);
            $table->integer('prior_source_observation_row_id');
            $table->integer('current_source_observation_row_id');
            $table->integer('prior_source_observation_id');
            $table->integer('current_source_observation_id');
            $table->integer('listing_id')->nullable();
            $table->string('provider', 64)->nullable();
            $table->string('provider_symbol', 128)->nullable();
            $table->string('ticker_code', 32);
            $table->date('trade_date');
            $table->string('comparison_state', 32);
            $table->string('divergence_finding_uid', 64)->nullable();
            $table->string('finding_state', 32);
            $table->text('differing_fields_json')->nullable();
            $table->text('prior_values_json');
            $table->text('current_values_json');
            $table->text('value_deltas_json');
            $table->dateTime('created_at');
            $table->unique(['prior_source_observation_row_id', 'current_source_observation_row_id'], 'uq_md_obs_comparison_pair');
            $table->unique(['comparison_uid'], 'uq_md_obs_comparison_uid');
            $table->unique(['divergence_finding_uid'], 'uq_md_obs_divergence_finding');
            $table->index(['listing_id', 'trade_date', 'finding_state'], 'idx_md_obs_comparison_listing_state');
            $table->index(['comparison_state', 'finding_state'], 'idx_md_obs_comparison_state');
        });

        $schema->create('md_source_observation_identity_bindings', function (Blueprint $table) {
            $table->bigIncrements('source_observation_identity_binding_id');
            $table->integer('source_observation_row_id');
            $table->integer('source_observation_id');
            $table->integer('listing_id');
            $table->integer('provider_mapping_id')->nullable();
            $table->string('mapping_revision', 64);
            $table->date('effective_trade_date');
            $table->dateTime('recorded_at');
            $table->unique(['source_observation_row_id'], 'uq_md_obs_identity_row');
            $table->index(['listing_id', 'effective_trade_date'], 'idx_md_obs_identity_listing_date');
        });

        $schema->create('md_source_observation_rejected_rows', function (Blueprint $table) {
            $table->bigIncrements('source_observation_rejected_row_id');
            $table->integer('source_observation_id');
            $table->integer('capture_observation_id');
            $table->string('source_row_ref', 255);
            $table->string('instrument_code', 32);
            $table->string('provider_symbol', 128)->nullable();
            $table->date('trade_date');
            $table->string('open_value', 64)->nullable();
            $table->string('high_value', 64)->nullable();
            $table->string('low_value', 64)->nullable();
            $table->string('close_value', 64)->nullable();
            $table->string('volume_value', 64)->nullable();
            $table->string('adj_close_value', 64)->nullable();
            $table->string('reason_code', 64);
            $table->string('reason_note', 255);
            $table->dateTime('created_at');
            $table->unique(['source_observation_id', 'source_row_ref'], 'uq_md_obs_rejected_row_ref');
            $table->index(['instrument_code', 'trade_date', 'reason_code'], 'idx_md_obs_rejected_identity');
        });

        $schema->create('md_issuers', function (Blueprint $table) {
            $table->bigIncrements('issuer_id');
            $table->string('issuer_uid', 64)->unique();
            $table->string('legal_name', 255);
            $table->string('source_ref', 255)->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('created_at');
        });

        $schema->create('md_instruments', function (Blueprint $table) {
            $table->bigIncrements('instrument_id');
            $table->string('instrument_uid', 64)->unique();
            $table->integer('issuer_id');
            $table->string('instrument_type', 32);
            $table->string('currency_code', 3)->default('IDR');
            $table->string('source_ref', 255)->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('created_at');
            $table->index(['issuer_id'], 'idx_md_instrument_issuer');
        });

        $schema->create('md_listings', function (Blueprint $table) {
            $table->bigIncrements('listing_id');
            $table->string('listing_uid', 64)->unique();
            $table->integer('legacy_ticker_id')->nullable()->unique();
            $table->integer('instrument_id');
            $table->string('exchange_code', 16);
            $table->string('market_segment', 32)->nullable();
            $table->string('board_code', 16)->nullable();
            $table->date('listed_date');
            $table->date('delisted_date')->nullable();
            $table->string('source_ref', 255)->nullable();
            $table->string('listing_state', 32)->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('created_at');
            $table->index(['exchange_code', 'listed_date', 'delisted_date'], 'idx_md_listing_exchange_dates');
            $table->index(['instrument_id'], 'idx_md_listing_instrument');
            $table->index(['exchange_code', 'market_segment', 'listed_date', 'delisted_date'], 'idx_md_listing_market_interval');
        });

        $schema->create('md_listing_symbols', function (Blueprint $table) {
            $table->bigIncrements('listing_symbol_id');
            $table->integer('listing_id');
            $table->string('symbol', 64);
            $table->string('symbol_type', 32)->default('EXCHANGE');
            $table->string('symbol_namespace', 64)->nullable();
            $table->dateTime('effective_from');
            $table->dateTime('effective_to')->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('retracted_at')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->string('source_ref', 255)->nullable();
            $table->string('change_reason', 64)->nullable();
            $table->unique(['listing_id', 'symbol_type', 'effective_from', 'recorded_at'], 'uq_md_listing_symbol_revision');
            $table->index(['symbol', 'effective_from', 'effective_to'], 'idx_md_symbol_effective');
        });

        // Mirrors 2026_08_22_000001. Board and market segment are effective-dated here and cached
        // on md_listings; historical resolution reads only this table.
        $schema->create('md_listing_boards', function (Blueprint $table) {
            $table->bigIncrements('listing_board_id');
            $table->integer('listing_id');
            $table->string('market_segment', 32);
            $table->string('board_code', 16)->nullable();
            $table->dateTime('effective_from');
            $table->dateTime('effective_to')->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('retracted_at')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->string('source_ref', 255)->nullable();
            $table->string('change_reason', 64)->nullable();
            $table->unique(['listing_id', 'effective_from', 'recorded_at'], 'uq_md_listing_board_revision');
            $table->index(['listing_id', 'effective_from', 'effective_to'], 'idx_md_listing_board_effective');
            $table->index(['market_segment', 'effective_from', 'effective_to'], 'idx_md_listing_board_segment');
        });

        $schema->create('md_provider_symbol_mappings', function (Blueprint $table) {
            $table->bigIncrements('provider_mapping_id');
            $table->integer('listing_id');
            $table->string('provider', 64);
            $table->string('provider_symbol', 128);
            $table->dateTime('effective_from');
            $table->dateTime('effective_to')->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('retracted_at')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->string('mapping_revision', 64);
            $table->string('source_ref', 255)->nullable();
            $table->string('change_reason', 64)->nullable();
            $table->unique(['listing_id', 'provider', 'effective_from', 'recorded_at'], 'uq_md_provider_mapping_revision');
            $table->index(['provider', 'provider_symbol', 'effective_from', 'effective_to'], 'idx_md_provider_symbol_effective');
        });

        $schema->create('md_market_calendar_revisions', function (Blueprint $table) {
            $table->bigIncrements('calendar_revision_id');
            $table->string('market_code', 16)->default('IDX');
            $table->string('market_segment', 32)->nullable();
            $table->date('cal_date');
            $table->string('revision_uid', 64);
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->boolean('is_trading_day')->nullable();
            $table->boolean('is_half_day')->nullable();
            $table->string('session_state', 32);
            $table->dateTime('session_open_at')->nullable();
            $table->dateTime('session_close_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('recorded_at');
            $table->integer('source_observation_id')->nullable();
            $table->integer('supersedes_revision_id')->nullable();
            $table->string('source_ref', 255)->nullable();
            $table->string('source_version', 64)->nullable();
            $table->string('provenance_tier', 16)->nullable();
            $table->date('reconciled_at')->nullable();
            $table->string('reconciliation_source_ref', 255)->nullable();
            $table->unique(['market_code', 'cal_date', 'revision_uid'], 'uq_md_calendar_revision');
            $table->index(['cal_date', 'recorded_at'], 'idx_md_calendar_date_known');
        });

        $schema->create('md_trading_status_revisions', function (Blueprint $table) {
            $table->bigIncrements('status_revision_id');
            $table->integer('listing_id');
            $table->string('status_code', 64);
            $table->string('bar_expectation_state', 32);
            $table->string('board_code', 16)->nullable();
            $table->string('authority_class', 32)->nullable();
            $table->string('status_event_uid', 64)->nullable();
            $table->integer('instrument_id')->nullable();
            $table->string('status_type_code', 64)->nullable();
            $table->string('source_name', 64)->nullable();
            $table->string('source_payload_hash', 64)->nullable();
            $table->dateTime('announced_at')->nullable();
            $table->string('operator_name', 128)->nullable();
            $table->string('governed_reason_code', 64)->nullable();
            $table->string('authoritative_source_ref', 255)->nullable();
            $table->boolean('full_session_verified')->default(false);
            $table->dateTime('effective_from');
            $table->dateTime('effective_to')->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('retracted_at')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->integer('supersedes_revision_id')->nullable();
            $table->string('source_ref', 255)->nullable();
            $table->string('verification_state', 32)->nullable();
            $table->dateTime('observed_at')->nullable();
            $table->index(['listing_id', 'effective_from', 'effective_to'], 'idx_md_status_listing_effective');
            $table->index(['recorded_at', 'bar_expectation_state'], 'idx_md_status_known_expectation');
        });

        $schema->create('md_trading_status_source_registry', function (Blueprint $table) {
            $table->string('source_name', 64);
            $table->string('status_type_code', 64)->default('*');
            $table->string('authority_class', 32);
            $table->integer('priority');
            $table->boolean('active')->default(true);
            $table->string('source_ref_pattern', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->primary(['source_name', 'status_type_code'], 'pk_md_status_source_registry');
            $table->index(['status_type_code', 'authority_class', 'priority'], 'idx_md_status_source_priority');
        });

        $statusRegistryNow = date('Y-m-d H:i:s');
        DB::table('md_trading_status_source_registry')->insert([
            ['source_name' => 'IDX_OFFICIAL', 'status_type_code' => '*', 'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'priority' => 10, 'active' => 1, 'source_ref_pattern' => 'idx.co.id', 'created_at' => $statusRegistryNow, 'updated_at' => $statusRegistryNow],
            ['source_name' => 'IDX_LONG_SUSPENSION_SNAPSHOT', 'status_type_code' => 'SUSPENSION_OBSERVED', 'authority_class' => 'EXCHANGE_AUTHORITATIVE', 'priority' => 10, 'active' => 1, 'source_ref_pattern' => 'block.idx.id', 'created_at' => $statusRegistryNow, 'updated_at' => $statusRegistryNow],
            ['source_name' => 'GOVERNED_OPERATOR_ENTRY', 'status_type_code' => '*', 'authority_class' => 'OPERATOR_ENTERED', 'priority' => 100, 'active' => 1, 'source_ref_pattern' => null, 'created_at' => $statusRegistryNow, 'updated_at' => $statusRegistryNow],
        ]);

        $schema->create('md_corporate_action_revisions', function (Blueprint $table) {
            $table->bigIncrements('corporate_action_revision_id');
            $table->string('event_uid', 64);
            $table->integer('revision_number');
            $table->integer('listing_id');
            $table->string('action_type_code', 64);
            $table->string('lifecycle_state', 32);
            $table->string('verification_state', 32);
            $table->date('ex_date')->nullable();
            $table->date('cum_date')->nullable();
            $table->date('record_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->text('terms_json')->nullable();
            $table->integer('source_observation_id')->nullable();
            $table->dateTime('effective_at')->nullable();
            $table->dateTime('recorded_at');
            $table->integer('supersedes_revision_id')->nullable();
            $table->unique(['event_uid', 'revision_number'], 'uq_md_action_event_revision');
            $table->index(['listing_id', 'ex_date', 'recorded_at'], 'idx_md_action_listing_ex_known');
            $table->index(['verification_state', 'lifecycle_state'], 'idx_md_action_verification_lifecycle');
        });

        $schema->create('md_exchange_market_structure_revisions', function (Blueprint $table) {
            $table->bigIncrements('market_structure_revision_id');
            $table->string('rule_uid', 64);
            $table->integer('revision_number');
            $table->string('rule_type', 32);
            $table->string('exchange_code', 16);
            $table->string('market_segment', 32);
            $table->string('instrument_scope_code', 64);
            $table->text('coverage_scope_json');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('minimum_price_idr', 20, 4)->nullable();
            $table->string('verification_state', 32);
            $table->string('source_uid', 64);
            $table->integer('source_observation_id');
            $table->string('source_reference', 128);
            $table->string('content_hash', 64);
            $table->dateTime('recorded_at');
            $table->integer('supersedes_revision_id')->nullable();
            $table->unique(['rule_uid', 'revision_number'], 'uq_md_market_structure_rule_revision');
            $table->index(
                ['exchange_code', 'market_segment', 'rule_type', 'effective_from', 'effective_to'],
                'idx_md_market_structure_effective'
            );
            $table->index(
                ['instrument_scope_code', 'verification_state'],
                'idx_md_market_structure_scope_verification'
            );
            $table->index(['source_observation_id'], 'idx_md_market_structure_source');
            $table->index(['source_uid'], 'idx_md_market_structure_source_uid');
        });

        $schema->create('md_exchange_price_band_tiers', function (Blueprint $table) {
            $table->bigIncrements('price_band_tier_id');
            $table->integer('market_structure_revision_id');
            $table->integer('tier_sequence');
            $table->decimal('reference_price_min_idr', 20, 4)->nullable();
            $table->boolean('reference_price_min_inclusive')->default(false);
            $table->decimal('reference_price_max_idr', 20, 4)->nullable();
            $table->boolean('reference_price_max_inclusive')->default(false);
            $table->decimal('upper_limit_percent', 9, 6);
            $table->decimal('lower_limit_percent', 9, 6);
            $table->unique(['market_structure_revision_id', 'tier_sequence'], 'uq_md_price_band_revision_tier');
            $table->index(['reference_price_min_idr', 'reference_price_max_idr'], 'idx_md_price_band_range');
        });

        $schema->create('md_exchange_tick_size_tiers', function (Blueprint $table) {
            $table->bigIncrements('tick_size_tier_id');
            $table->integer('market_structure_revision_id');
            $table->integer('tier_sequence');
            $table->decimal('price_min_idr', 20, 4)->nullable();
            $table->boolean('price_min_inclusive')->default(false);
            $table->decimal('price_max_idr', 20, 4)->nullable();
            $table->boolean('price_max_inclusive')->default(false);
            $table->decimal('tick_size_idr', 20, 4);
            $table->decimal('maximum_price_step_idr', 20, 4);
            $table->unique(['market_structure_revision_id', 'tier_sequence'], 'uq_md_tick_size_revision_tier');
            $table->index(['price_min_idr', 'price_max_idr'], 'idx_md_tick_size_range');
        });

        $schema->create('md_adjustment_factor_sets', function (Blueprint $table) {
            $table->bigIncrements('factor_set_id');
            $table->string('factor_set_uid', 64)->unique();
            $table->string('price_product_code', 32);
            $table->string('factor_formula_version', 64);
            $table->integer('config_snapshot_id');
            $table->string('state', 32);
            $table->string('content_hash', 64);
            $table->dateTime('recorded_at');
            $table->dateTime('created_at');
            $table->index(['price_product_code', 'state'], 'idx_md_factor_set_product_state');
        });

        $schema->create('md_adjustment_factors', function (Blueprint $table) {
            $table->bigIncrements('adjustment_factor_id');
            $table->integer('factor_set_id');
            $table->integer('listing_id');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('price_factor', 24, 12);
            $table->decimal('volume_factor', 24, 12)->nullable();
            $table->integer('corporate_action_revision_id');
            $table->dateTime('created_at');
            $table->unique(['factor_set_id', 'listing_id', 'effective_from', 'corporate_action_revision_id'], 'uq_md_factor_revision_scope');
            $table->index(['listing_id', 'effective_from', 'effective_to'], 'idx_md_factor_listing_effective');
        });

        $schema->create('md_publication_lineage_bindings', function (Blueprint $table) {
            $table->bigIncrements('publication_lineage_id');
            $table->integer('publication_id')->unique();
            $table->integer('corpus_admission_decision_id')->nullable();
            $table->integer('config_snapshot_id');
            $table->integer('factor_set_id')->nullable();
            $table->string('observation_manifest_hash', 64);
            $table->string('identity_revision_set_hash', 64);
            $table->string('calendar_revision_set_hash', 64);
            $table->string('status_revision_set_hash', 64);
            $table->string('event_revision_set_hash', 64);
            $table->string('source_scale_assessment_set_hash', 64)->nullable();
            $table->string('market_structure_revision_set_hash', 64)->nullable();
            $table->string('factor_decision_set_hash', 64)->nullable();
            $table->string('formula_version', 64);
            $table->string('build_id', 128);
            $table->string('read_model_version', 64);
            $table->dateTime('created_at');
        });

        $schema->create('md_source_scale_assessments', function (Blueprint $table) {
            $table->bigIncrements('source_scale_assessment_id');
            $table->string('assessment_uid', 64)->unique();
            $table->integer('revision_number');
            $table->string('provider', 64);
            $table->integer('listing_id');
            $table->integer('corporate_action_revision_id');
            $table->string('source_scale_state', 32);
            $table->date('scale_effective_from')->nullable();
            $table->string('assessment_version', 64);
            $table->string('evidence_observation_set_hash', 64);
            $table->text('evidence_json');
            $table->dateTime('recorded_at');
            $table->integer('supersedes_assessment_id')->nullable();
            $table->dateTime('created_at');
        });

        $schema->create('md_adjustment_factor_decisions', function (Blueprint $table) {
            $table->bigIncrements('factor_decision_id');
            $table->integer('factor_set_id');
            $table->integer('listing_id');
            $table->integer('corporate_action_revision_id');
            $table->integer('source_scale_assessment_id')->nullable();
            $table->string('decision_state', 48);
            $table->decimal('candidate_price_factor', 24, 12)->nullable();
            $table->decimal('candidate_volume_factor', 24, 12)->nullable();
            $table->string('reason_code', 64);
            $table->dateTime('created_at');
            $table->unique(['factor_set_id', 'corporate_action_revision_id'], 'uq_md_factor_set_event_decision');
        });

        $schema->create('md_publication_market_structure_bindings', function (Blueprint $table) {
            $table->bigIncrements('market_structure_binding_id');
            $table->integer('publication_id');
            $table->integer('listing_id');
            $table->string('resolution_state', 48);
            $table->string('normalized_board_code', 32)->nullable();
            $table->dateTime('board_identity_recorded_at')->nullable();
            $table->integer('price_band_revision_id')->nullable();
            $table->integer('minimum_price_revision_id')->nullable();
            $table->integer('tick_size_revision_id')->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->dateTime('created_at');
            $table->unique(['publication_id', 'listing_id'], 'uq_md_pub_market_structure_listing');
        });

        $schema->create('md_stage8_reconstruction_campaigns', function (Blueprint $table) {
            $table->bigIncrements('campaign_id');
            $table->string('campaign_uid', 64)->unique();
            $table->date('scope_start');
            $table->date('scope_end');
            $table->integer('target_date_count');
            $table->integer('baseline_max_publication_id');
            $table->string('state', 32);
            $table->integer('admission_decision_id')->nullable();
            $table->integer('supersedes_campaign_id')->nullable();
            $table->dateTime('superseded_at')->nullable();
            $table->string('baseline_target_set_hash', 64);
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->text('result_json')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        $schema->create('md_corpus_admission_decisions', function (Blueprint $table) {
            $table->bigIncrements('admission_decision_id');
            $table->string('decision_uid', 64)->unique();
            $table->string('market_code', 16);
            $table->string('market_segment', 32);
            $table->string('canonical_price_product', 32);
            $table->date('intentional_dataset_start');
            $table->date('admitted_from');
            $table->date('measured_through');
            $table->decimal('coverage_threshold', 8, 6);
            $table->string('source_mode', 32);
            $table->integer('status_snapshot_observation_id');
            $table->integer('transition_search_observation_id');
            $table->integer('measurement_campaign_id');
            $table->string('measurement_input_hash', 64);
            $table->string('status_revision_set_hash', 64);
            $table->string('algorithm_version', 64);
            $table->text('measurement_json');
            $table->string('state', 32);
            $table->string('reason_code', 64);
            $table->integer('supersedes_decision_id')->nullable();
            $table->dateTime('recorded_at');
            $table->dateTime('created_at');
        });

        $schema->create('md_stage8_reconstruction_targets', function (Blueprint $table) {
            $table->bigIncrements('campaign_target_id');
            $table->integer('campaign_id');
            $table->date('trade_date');
            $table->integer('baseline_publication_id');
            $table->integer('baseline_run_id');
            $table->integer('baseline_publication_version');
            $table->string('baseline_bars_batch_hash', 64);
            $table->string('baseline_indicators_batch_hash', 64);
            $table->string('baseline_eligibility_batch_hash', 64);
            $table->string('baseline_bars_snapshot_hash', 64);
            $table->string('baseline_indicators_snapshot_hash', 64);
            $table->string('baseline_eligibility_snapshot_hash', 64);
            $table->integer('correction_id')->nullable();
            $table->integer('replacement_publication_id')->nullable();
            $table->integer('replacement_run_id')->nullable();
            $table->string('state', 32)->default('PENDING');
            $table->string('reason_code', 64)->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['campaign_id', 'trade_date'], 'uq_md_stage8_campaign_date');
        });
    }

    protected function seedMarketDataSectorTaxonomy(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ([
            ['sector_code' => 'A', 'sector_name' => 'Energy', 'sector_index_code' => 'IDXENERGY'],
            ['sector_code' => 'B', 'sector_name' => 'Basic Materials', 'sector_index_code' => 'IDXBASIC'],
            ['sector_code' => 'C', 'sector_name' => 'Industrials', 'sector_index_code' => 'IDXINDUST'],
            ['sector_code' => 'D', 'sector_name' => 'Consumer Non-Cyclicals', 'sector_index_code' => 'IDXNONCYC'],
            ['sector_code' => 'E', 'sector_name' => 'Consumer Cyclicals', 'sector_index_code' => 'IDXCYCLIC'],
            ['sector_code' => 'F', 'sector_name' => 'Healthcare', 'sector_index_code' => 'IDXHEALTH'],
            ['sector_code' => 'G', 'sector_name' => 'Financials', 'sector_index_code' => 'IDXFINANCE'],
            ['sector_code' => 'H', 'sector_name' => 'Properties & Real Estate', 'sector_index_code' => 'IDXPROPERT'],
            ['sector_code' => 'I', 'sector_name' => 'Technology', 'sector_index_code' => 'IDXTECHNO'],
            ['sector_code' => 'J', 'sector_name' => 'Infrastructures', 'sector_index_code' => 'IDXINFRA'],
            ['sector_code' => 'K', 'sector_name' => 'Transportation & Logistic', 'sector_index_code' => 'IDXTRANS'],
            ['sector_code' => 'Z', 'sector_name' => 'Listed Investment Product', 'sector_index_code' => null],
        ] as $sector) {
            DB::table('market_data_sectors')->insert($sector + [
                'classification_system' => 'IDX-IC',
                'effective_from' => '2021-01-25',
                'effective_to' => null,
                'is_active' => 1,
                'source_name' => 'idx',
                'source_ref' => 'https://www.idx.id/en/products/stocks/',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($sector['sector_index_code'] !== null) {
                DB::table('market_benchmarks')->insert([
                    'benchmark_code' => $sector['sector_index_code'],
                    'benchmark_name' => 'IDX Sector '.$sector['sector_name'],
                    'provider' => 'manual_sector_index_csv',
                    'provider_symbol' => $sector['sector_index_code'],
                    'instrument_type' => 'SECTOR_INDEX',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /** Seed a governed calendar revision; legacy market_calendar is intentionally not populated. */
    protected function seedVerifiedMarketCalendarDate(string $date, bool $isTradingDay = true, array $overrides = []): void
    {
        $base = [
            'market_code' => 'IDX',
            'market_segment' => 'REGULAR',
            'cal_date' => $date,
            'revision_uid' => hash('sha256', 'test-calendar|'.$date.'|'.($isTradingDay ? 'TRADING' : 'CLOSED')),
            'timezone' => 'Asia/Jakarta',
            'is_trading_day' => $isTradingDay ? 1 : 0,
            'is_half_day' => 0,
            'session_state' => $isTradingDay ? 'COMPLETED' : 'CLOSED',
            'session_open_at' => $isTradingDay ? $date.' 09:00:00' : null,
            'session_close_at' => $isTradingDay ? $date.' 16:00:00' : null,
            'completed_at' => $isTradingDay ? $date.' 16:00:00' : null,
            'recorded_at' => $date.' 17:00:00',
            'source_observation_id' => null,
            'supersedes_revision_id' => null,
            'source_ref' => 'https://www.idx.co.id/test-calendar/'.$date,
            'source_version' => 'idx-test-calendar-v1',
            'provenance_tier' => 'VERIFIED',
            'reconciled_at' => $date.' 17:00:00',
            'reconciliation_source_ref' => 'https://www.idx.co.id/test-calendar/'.$date,
        ];
        DB::table('md_market_calendar_revisions')->updateOrInsert(
            ['market_code' => 'IDX', 'market_segment' => 'REGULAR', 'cal_date' => $date],
            array_merge($base, $overrides)
        );
    }

    protected function seedVerifiedMarketCalendarRange(string $startDate, string $endDate): void
    {
        $date = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);
        while ($date <= $end) {
            $this->seedVerifiedMarketCalendarDate($date->format('Y-m-d'), (int) $date->format('N') <= 5);
            $date = $date->modify('+1 day');
        }
    }
}
