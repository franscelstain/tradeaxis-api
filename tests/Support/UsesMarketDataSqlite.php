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

        // Canonical reason-code registry content, copied verbatim (code/category/description/
        // severity/is_active) from the real, migration-seeded tradeaxis_testing MariaDB corpus.
        // Whole-C1 completeness auditing (MD-B18-A002 E031-E033) established that production
        // always has this population; leaving the mirror's own copy of the same lookup table
        // empty was a fixture gap relative to that reality, not a legitimate empty state, so it
        // is closed here the same way its two sibling lookup tables above already are.
        // Inserted in chunks: SQLite's default SQLITE_MAX_VARIABLE_NUMBER (999 bound parameters
        // per statement) is far smaller than one INSERT covering all 437 rows x 7 columns at once.
        foreach (array_chunk(array_map(function ($row) {
            return [
                'code' => $row[0], 'category' => $row[1], 'description' => $row[2],
                'severity' => $row[3], 'is_active' => $row[4], 'created_at' => null, 'updated_at' => null,
            ];
        }, [
            ['AFFECTED_DATE_RUN_NOT_FOUND', 'PUBLICATION_REPROCESS', 'Affected non-readable date could not be promoted because no persisted run exists.', 'HARD', 1],
            ['AFFECTED_PUBLICATION_REQUIRES_CORRECTION', 'CORRECTION', 'A changed historical EOD bar can affect at least one already readable downstream publication; silent mutation is blocked and correction/reseal/republication is required.', 'HARD', 1],
            ['API_IMPORT_ACCEPTED', 'SOURCE', 'API import was accepted as import-only context.', 'INFO', 1],
            ['API_IMPORT_COMPLETED', 'SOURCE', 'API import completed.', 'INFO', 1],
            ['API_IMPORT_FAILED', 'SOURCE', 'API import failed.', 'HARD', 1],
            ['API_IMPORT_HELD', 'SOURCE', 'API import entered HELD state.', 'WARN', 1],
            ['API_IMPORT_PARTIAL_DATA', 'SOURCE', 'API import returned partial data and must not promote automatically.', 'WARN', 1],
            ['API_IMPORT_RATE_LIMITED', 'SOURCE', 'API import was rate limited.', 'WARN', 1],
            ['API_IMPORT_STARTED', 'SOURCE', 'API import started.', 'INFO', 1],
            ['API_IMPORT_TIMEOUT', 'SOURCE', 'API import timed out.', 'WARN', 1],
            ['API_PROMOTE_COMPLETED', 'IMPORT_PROMOTE', 'API promote completed.', 'INFO', 1],
            ['API_PROMOTE_COVERAGE_FAILED', 'IMPORT_PROMOTE', 'API promote coverage failed.', 'HARD', 1],
            ['API_PROMOTE_COVERAGE_REQUIRED', 'IMPORT_PROMOTE', 'API promote requires coverage gate.', 'HARD', 1],
            ['ARTIFACT_EMPTY', 'ARTIFACT', 'Canonical fail-safe alias for an artifact with zero valid rows.', 'HARD', 1],
            ['AUTHORITATIVE_MARKET_STRUCTURE_VALIDATED', 'MARKET_STRUCTURE', 'An effective-dated IDX price-band, minimum-price, or tick-ladder revision was validated against immutable authoritative evidence without applying it to a series.', 'INFO', 1],
            ['AUTHORITATIVE_TERMS_VALIDATED', 'CORPORATE_ACTION', 'A corporate-action revision and its complete terms were validated against an immutable authoritative exchange/CSD document observation.', 'INFO', 1],
            ['AUTHORITATIVE_TRADING_STATUS_TRANSITIONS_VALIDATED', 'STATUS', 'The official IDX suspension-transition search was captured and validated through the measured frontier.', 'INFO', 1],
            ['AUTHORITATIVE_TRADING_STATUS_VALIDATED', 'STATUS', 'An official IDX long-suspension snapshot was captured and validated against its declared as-of scope.', 'INFO', 1],
            ['BAR_DUPLICATE_SOURCE_ROW', 'BAR', 'More than one source row mapped to the same (trade_date, ticker_id), requiring deterministic winner selection.', 'WARN', 1],
            ['BAR_INVALID_OHLC_ORDER', 'BAR', 'Received OHLC values violated canonical ordering rules.', 'HARD', 1],
            ['BAR_MISSING_REQUIRED_FIELD', 'BAR', 'One or more mandatory source fields were missing.', 'HARD', 1],
            ['BAR_NEGATIVE_VOLUME', 'BAR', 'Received volume value was negative.', 'HARD', 1],
            ['BAR_NON_POSITIVE_PRICE', 'BAR', 'Received price value was zero or negative in a field that must be positive.', 'HARD', 1],
            ['BAR_PRICE_SCALE_BREAK_DETECTED', 'BAR', 'A canonical bar opens on a different price scale than the previous bar closes on, beyond the locked ratio and minimum-price guards.', 'WARN', 1],
            ['BAR_SOURCE_OBSERVATION_MISSING', 'BAR', 'Row carries no persisted source observation, so its origin cannot be traced.', 'HARD', 1],
            ['BAR_SOURCE_OBSERVATION_NOT_ACCEPTED', 'BAR', 'Referenced source observation exists but is not in an accepted immutable state.', 'HARD', 1],
            ['BAR_TEMPORAL_LISTING_MAPPING_MISSING', 'BAR', 'No point-in-time listing resolves for the instrument on the requested date, so the row cannot bind to a canonical logical identity.', 'HARD', 1],
            ['BAR_TICKER_MAPPING_MISSING', 'BAR', 'Source row ticker_code could not be resolved deterministically to ticker_id via the ticker master.', 'WARN', 1],
            ['BAR_ZERO_VOLUME_PRICE_MOVEMENT', 'BAR', 'A source-backed EOD row reports volume 0 while open/high/low/close are not all identical; the row is invalid/rejected evidence and never canonical.', 'HARD', 1],
            ['BARS_ARTIFACT_EMPTY', 'ARTIFACT', 'Bars artifact contained zero valid rows.', 'HARD', 1],
            ['CANONICAL_BAR_PRICE_PRODUCT_INVALID', 'READ_SIDE', 'A canonical bar in the resolved publication declares a price product other than the configured canonical RAW identity. The publication is withheld because canonical bars cannot expose an analytical or unknown scale as RAW.', 'HARD', 1],
            ['COMMAND_APPLY_CONFIRMED', 'COMMAND', 'Operator command mutation was executed only after explicit apply confirmation.', 'INFO', 1],
            ['COMMAND_CONFLICTING_OPTIONS', 'COMMAND', 'Operator command options are mutually exclusive or ambiguous.', 'HARD', 1],
            ['COMMAND_CORRECTION_NOT_FOUND', 'COMMAND', 'Operator command referenced a correction id that does not exist.', 'HARD', 1],
            ['COMMAND_CORRECTION_STATUS_NOT_APPROVABLE', 'COMMAND', 'Correction approve command was blocked because only REQUESTED corrections are approvable.', 'HARD', 1],
            ['COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE', 'COMMAND', 'Operator command attempted to execute a correction whose lifecycle status is not executable.', 'HARD', 1],
            ['COMMAND_DESTRUCTIVE_GUARD_REQUIRED', 'COMMAND', 'Operator command requested a destructive or force action without the required explicit guard/reason.', 'HARD', 1],
            ['COMMAND_DRY_RUN_ONLY', 'COMMAND', 'Operator command completed a dry-run preview and intentionally did not mutate final state.', 'INFO', 1],
            ['COMMAND_EXECUTION_FAILED', 'COMMAND', 'Operator command execution failed and surfaced a reason-coded blocking outcome.', 'HARD', 1],
            ['COMMAND_INVALID_DATE_FORMAT', 'COMMAND', 'Operator command date input does not use the locked YYYY-MM-DD format.', 'HARD', 1],
            ['COMMAND_INVALID_DATE_RANGE', 'COMMAND', 'Operator command date range is invalid because start_date is after end_date.', 'HARD', 1],
            ['COMMAND_INVALID_PROMOTE_MODE', 'COMMAND', 'Operator command promote mode is unsupported by the locked promote contract.', 'HARD', 1],
            ['COMMAND_INVALID_REQUEST_MODE', 'COMMAND', 'Operator command request mode is outside the locked set of run request modes.', 'HARD', 1],
            ['COMMAND_INVALID_SOURCE_MODE', 'COMMAND', 'Operator command source mode is outside the locked API/manual-file source modes.', 'HARD', 1],
            ['COMMAND_MISSING_REQUIRED_INPUT', 'COMMAND', 'Operator command input is missing or empty for a required argument or option.', 'HARD', 1],
            ['COMMAND_RESUME_REQUIRES_APPLY', 'COMMAND', 'A reconstruction resume was requested without the explicit apply flag, so no campaign work was executed.', 'HARD', 1],
            ['CONFIG_SNAPSHOT_REQUIRED', 'CONFIG', 'A run or canonical artifact lacks its immutable resolved configuration snapshot.', 'HARD', 1],
            ['CORPORATE_ACTION_AUTHORITATIVE_EVIDENCE_REQUIRED', 'CORPORATE_ACTION', 'A detected price break cannot synthesize corporate-action identity, terms, date, or factors.', 'HARD', 1],
            ['CORRECTION_ARTIFACT_BASELINE_OR_CANDIDATE_MISSING', 'CORRECTION', 'Correction artifact comparison cannot run because baseline or candidate publication is missing.', 'HARD', 1],
            ['CORRECTION_ARTIFACT_CHANGED', 'CORRECTION', 'Correction artifact comparison found deterministic content change and reseal/publish may proceed through normal guards.', 'INFO', 1],
            ['CORRECTION_ARTIFACT_HASH_INCOMPLETE', 'CORRECTION', 'Correction artifact comparison found missing hash context and cannot prove deterministic change.', 'HARD', 1],
            ['CORRECTION_ARTIFACT_UNCHANGED', 'CORRECTION', 'Correction artifact comparison found no content change; current publication must be preserved.', 'INFO', 1],
            ['CORRECTION_BASELINE_LINK_INVALID', 'CORRECTION', 'Correction baseline linkage is not a valid current readable publication.', 'HARD', 1],
            ['CORRECTION_BASELINE_LINK_MISSING', 'CORRECTION', 'Correction baseline publication/run linkage is missing.', 'HARD', 1],
            ['CORRECTION_BASELINE_LINK_VERIFIED', 'CORRECTION', 'Correction baseline publication/run linkage was verified.', 'INFO', 1],
            ['CORRECTION_BASELINE_POINTER_PRESERVED', 'CORRECTION', 'Correction preserved the baseline current pointer on unchanged or failed replacement.', 'INFO', 1],
            ['CORRECTION_BASELINE_PRESERVED_FAIL_SAFE', 'POINTER', 'Correction baseline was preserved because candidate failed fail-safe proof.', 'INFO', 1],
            ['CORRECTION_CANCELLED', 'CORRECTION', 'Correction lifecycle was consumed without publication because current content was unchanged or cancelled safely.', 'INFO', 1],
            ['CORRECTION_FAILED', 'CORRECTION', 'Correction lifecycle failed or was blocked before safe publication.', 'HARD', 1],
            ['CORRECTION_IMPORT_ACCEPTED', 'CORRECTION', 'Correction import accepted without publication.', 'INFO', 1],
            ['CORRECTION_IMPORT_NOT_PROMOTED', 'CORRECTION', 'Correction import was not promoted.', 'INFO', 1],
            ['CORRECTION_LINEAGE_INCOMPLETE', 'CORRECTION', 'Correction lineage is incomplete across baseline, replacement, run, publication, or pointer switch.', 'HARD', 1],
            ['CORRECTION_POINTER_SWITCH_BLOCKED', 'CORRECTION', 'Correction pointer switch was blocked because replacement or baseline linkage was unsafe.', 'HARD', 1],
            ['CORRECTION_POINTER_SWITCH_CREATED', 'CORRECTION', 'Correction pointer switch was created for a valid replacement publication.', 'INFO', 1],
            ['CORRECTION_PROMOTE_BLOCKED', 'CORRECTION', 'Correction promote blocked.', 'HARD', 1],
            ['CORRECTION_PROMOTE_COMPLETED', 'CORRECTION', 'Correction promote completed.', 'INFO', 1],
            ['CORRECTION_PROMOTE_REQUIRED', 'CORRECTION', 'Correction publication requires explicit promote/finalize path.', 'HARD', 1],
            ['CORRECTION_PUBLISHED', 'CORRECTION', 'Correction lifecycle published a changed, resealed publication safely.', 'INFO', 1],
            ['CORRECTION_REPLACEMENT_LINK_CREATED', 'CORRECTION', 'Correction replacement publication/run linkage was created.', 'INFO', 1],
            ['CORRECTION_REPLACEMENT_LINK_INVALID', 'CORRECTION', 'Correction replacement publication/run linkage is invalid.', 'HARD', 1],
            ['CORRECTION_REPLACEMENT_LINK_VERIFIED', 'CORRECTION', 'Correction replacement publication/run linkage was verified before publication.', 'INFO', 1],
            ['COVERAGE_BELOW_THRESHOLD', 'COVERAGE', 'Coverage evaluation failed because available canonical EOD bars stayed below the locked minimum threshold.', 'HARD', 1],
            ['COVERAGE_CANONICAL_BAR_EVIDENCE_DISABLED', 'COVERAGE', 'Canonical bar evidence requirement was disabled; coverage must remain not evaluable because readable coverage requires canonical bar proof.', 'HARD', 1],
            ['COVERAGE_DORMANT_TICKERS_EXCLUDED', 'COVERAGE', 'DEPRECATED legacy reason: dormancy must never exclude a temporal-universe listing from the coverage denominator; any emission blocks V2 relock.', 'HARD', 0],
            ['COVERAGE_GATE_DISABLED', 'COVERAGE', 'Coverage gate runtime switch was disabled; coverage must remain not evaluable and cannot create readable publication.', 'HARD', 1],
            ['COVERAGE_THRESHOLD_MET', 'COVERAGE', 'Coverage evaluation passed because available canonical EOD bars met or exceeded the locked minimum threshold.', 'INFO', 1],
            ['COVERAGE_UNIVERSE_EMPTY', 'COVERAGE', 'Coverage could not be evaluated because the resolved coverage universe for the requested date was empty.', 'HARD', 1],
            ['CURRENT_PUBLICATION_DEMOTED', 'PUBLICATION', 'Previous current publication was demoted during an allowed pointer switch.', 'INFO', 1],
            ['CURRENT_PUBLICATION_FORCE_REPLACED', 'PUBLICATION', 'Operator-controlled force replace switched current publication with audit reason.', 'WARN', 1],
            ['CURRENT_PUBLICATION_PRESERVED', 'POINTER', 'Current publication pointer was preserved after failed candidate.', 'INFO', 1],
            ['CURRENT_PUBLICATION_PROMOTED', 'PUBLICATION', 'Candidate publication was promoted to current after all validation passed.', 'INFO', 1],
            ['CURRENT_PUBLICATION_REPLACE_BLOCKED', 'PUBLICATION', 'Replacement of an existing current publication was blocked because force/audit controls were missing or invalid.', 'HARD', 1],
            ['DATASET_HASH_CREATED', 'DATASET', 'Dataset hash was created from canonical serialized artifact rows.', 'INFO', 1],
            ['DATASET_HASH_MISMATCH', 'DATASET', 'Recomputed or mirrored dataset hash does not match stored artifact hash context.', 'HARD', 1],
            ['DATASET_HASH_MISSING', 'DATASET', 'Dataset seal or finalize was blocked because mandatory artifact hash context is missing.', 'HARD', 1],
            ['DATASET_HASH_VERIFIED', 'DATASET', 'Dataset hash/seal context was verified against stored canonical manifest context.', 'INFO', 1],
            ['DATASET_MANIFEST_INVALID', 'DATASET', 'Dataset manifest is missing required run/date/source/hash/coverage context.', 'HARD', 1],
            ['DATASET_SEAL_INVALID', 'DATASET', 'Dataset seal state is invalid or cannot be verified from manifest/hash context.', 'HARD', 1],
            ['ELIG_CORPORATE_ACTION_DISCONTINUITY', 'ELIGIBILITY', 'Eligibility is blocked because required indicators were quarantined by a corporate action that breaks price or volume continuity inside their dependency window.', 'WARN', 1],
            ['ELIG_FETCH_FAILURE', 'ELIGIBILITY', 'Eligibility is blocked because ticker-level source acquisition failed and required upstream artifacts could not be formed safely.', 'WARN', 1],
            ['ELIG_INSUFFICIENT_HISTORY', 'ELIGIBILITY', 'Eligibility is blocked because required indicator history is still insufficient.', 'WARN', 1],
            ['ELIG_INVALID_INDICATORS', 'ELIGIBILITY', 'An indicator row exists but required indicators are marked invalid.', 'WARN', 1],
            ['ELIG_MISSING_BAR', 'ELIGIBILITY', 'A ticker in the coverage universe does not have a canonical valid bar for the requested date.', 'WARN', 1],
            ['ELIG_MISSING_INDICATORS', 'ELIGIBILITY', 'Eligibility cannot be determined because required indicators are unavailable.', 'HARD', 1],
            ['ELIG_PRICE_SCALE_DISCONTINUITY', 'ELIGIBILITY', 'Eligibility is blocked because required indicators were quarantined by an unexplained price-scale break inside their dependency window.', 'WARN', 1],
            ['ELIG_TRADING_SUSPENDED', 'ELIGIBILITY', 'Listing was suspended on the requested date, so its data is not usable and no bar was expected.', 'HARD', 1],
            ['ELIG_UNIVERSE_DEPENDENCY_MISSING', 'ELIGIBILITY', 'An upstream dependency required to determine universe membership is unavailable.', 'HARD', 1],
            ['ELIGIBILITY_ARTIFACT_EMPTY', 'ARTIFACT', 'Eligibility artifact contained zero rows and coverage cannot pass.', 'HARD', 1],
            ['EMPTY_ARTIFACT_NOT_READABLE', 'ARTIFACT', 'Empty artifact cannot be sealed, finalized, promoted, or exposed as readable.', 'HARD', 1],
            ['EVENT_RISK_CA_TYPE_UNMAPPED', 'EVENT_RISK', 'A corporate action row carries an action_type that has no row in the corporate action type dictionary; it was treated fail-safe as breaking both price and volume continuity until an operator maps it.', 'WARN', 1],
            ['EVIDENCE_COMPLETE', 'EVIDENCE', 'Evidence export includes all required operator-grade context sections.', 'INFO', 1],
            ['EVIDENCE_CORRECTION_LINEAGE_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included correction baseline/replacement lineage context.', 'INFO', 1],
            ['EVIDENCE_EMPTY_DATASET_INCLUDED', 'EVIDENCE', 'Evidence included empty dataset context.', 'INFO', 1],
            ['EVIDENCE_FAIL_SAFE_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included no-data/source-failure/pointer-preservation fail-safe context.', 'INFO', 1],
            ['EVIDENCE_FAIL_SAFE_CONTEXT_MISSING', 'EVIDENCE', 'Evidence is missing required fail-safe context.', 'HARD', 1],
            ['EVIDENCE_IMPORT_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included import context.', 'INFO', 1],
            ['EVIDENCE_IMPORT_PROMOTE_BOUNDARY_INCLUDED', 'EVIDENCE', 'Evidence export included import/promote boundary context.', 'INFO', 1],
            ['EVIDENCE_IMPORT_PROMOTE_CONTEXT_MISSING', 'EVIDENCE', 'Evidence export is missing import/promote boundary context.', 'WARN', 1],
            ['EVIDENCE_INCOMPLETE', 'EVIDENCE', 'Evidence export completed with one or more missing context sections that must be visible to the operator.', 'WARN', 1],
            ['EVIDENCE_LINEAGE_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included full run-publication-pointer-correction lineage context.', 'INFO', 1],
            ['EVIDENCE_LINEAGE_CONTEXT_MISSING', 'EVIDENCE', 'Evidence export found missing lineage context and marked evidence incomplete.', 'WARN', 1],
            ['EVIDENCE_POINTER_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included current pointer target context.', 'INFO', 1],
            ['EVIDENCE_POINTER_PRESERVATION_INCLUDED', 'EVIDENCE', 'Evidence included pointer preservation context.', 'INFO', 1],
            ['EVIDENCE_PROMOTE_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included promote context.', 'INFO', 1],
            ['EVIDENCE_PROOF_INCOMPLETE', 'EVIDENCE', 'Evidence proof is incomplete and must not be treated as replayable proof.', 'HARD', 1],
            ['EVIDENCE_RUN_PUBLICATION_CONTEXT_INCLUDED', 'EVIDENCE', 'Evidence export included run-publication linkage context.', 'INFO', 1],
            ['EVIDENCE_SOURCE_FAILURE_INCLUDED', 'EVIDENCE', 'Evidence included source failure context.', 'INFO', 1],
            ['FACTOR_APPLIED_SOURCE_AS_TRADED', 'CORPORATE_ACTION', 'An authoritative structural factor was applied because the bound Yahoo source-scale assessment classified the source series as as-traded.', 'INFO', 1],
            ['FACTOR_HELD_PROVIDER_BACK_ADJUSTED', 'CORPORATE_ACTION', 'An authoritative structural factor was held because the bound Yahoo source-scale assessment classified the source series as provider-back-adjusted.', 'WARN', 1],
            ['FACTOR_HELD_SOURCE_SCALE_UNKNOWN', 'CORPORATE_ACTION', 'An authoritative structural factor was held because the bound Yahoo source-scale assessment remained unknown.', 'WARN', 1],
            ['FINALIZE_BLOCKED_CANDIDATE_MISSING', 'RUN', 'Finalize was blocked because candidate publication was missing.', 'HARD', 1],
            ['FINALIZE_BLOCKED_COVERAGE_FAILED', 'RUN', 'Finalize was blocked because coverage failed.', 'HARD', 1],
            ['FINALIZE_BLOCKED_COVERAGE_NOT_EVALUABLE', 'RUN', 'Finalize was blocked because coverage could not be evaluated.', 'HARD', 1],
            ['FINALIZE_BLOCKED_EMPTY_ARTIFACT', 'RUN', 'Finalize was blocked because required artifact proof was empty.', 'HARD', 1],
            ['FINALIZE_BLOCKED_HASH_MISSING', 'RUN', 'Finalize was blocked because hash proof was missing.', 'HARD', 1],
            ['FINALIZE_BLOCKED_NO_VALID_DATA', 'RUN', 'Finalize was blocked because there was no valid data proof.', 'HARD', 1],
            ['FINALIZE_BLOCKED_POINTER_INVALID', 'RUN', 'Finalize was blocked because pointer target validation failed.', 'HARD', 1],
            ['FINALIZE_BLOCKED_SEAL_MISSING', 'RUN', 'Finalize was blocked because seal proof was missing.', 'HARD', 1],
            ['FINALIZE_BLOCKED_SOURCE_FAILED', 'RUN', 'Finalize was blocked because source acquisition failed.', 'HARD', 1],
            ['FINALIZE_HASH_MISMATCH', 'RUN', 'Finalize was blocked because run hash context differs from candidate publication hash context.', 'HARD', 1],
            ['FINALIZE_HASH_MISSING', 'RUN', 'Finalize was blocked because the candidate publication or run is missing mandatory hash context.', 'HARD', 1],
            ['FINALIZE_HELD_SOURCE_FAILURE', 'RUN', 'Finalize held the run because source failure prevented a readable publication.', 'WARN', 1],
            ['FINALIZE_NOT_READABLE_NO_VALID_DATA', 'RUN', 'Finalize produced not-readable state because no valid data existed.', 'HARD', 1],
            ['FINALIZE_SEAL_INVALID', 'RUN', 'Finalize was blocked because candidate publication seal timestamp or verification context is invalid.', 'HARD', 1],
            ['FINALIZE_SEAL_MISSING', 'RUN', 'Finalize was blocked because candidate publication seal state is missing.', 'HARD', 1],
            ['HASH_INPUT_EMPTY', 'ARTIFACT', 'Hash input was empty and cannot produce publication proof.', 'HARD', 1],
            ['IMMUTABLE_HISTORY_CORRECTION_REQUIRED', 'CORRECTION', 'An anomaly requires authoritative evidence and a new correction publication; in-place history mutation is prohibited.', 'HARD', 1],
            ['IMPORT_CORRECTION_PUBLISH_BLOCKED', 'IMPORT_PROMOTE', 'Import-only attempted to publish a correction.', 'HARD', 1],
            ['IMPORT_ONLY_ACCEPTED', 'IMPORT_PROMOTE', 'Import-only request accepted; data may be ingested but not promoted.', 'INFO', 1],
            ['IMPORT_ONLY_COMPLETED', 'IMPORT_PROMOTE', 'Import-only ingest completed with traceable candidate/import context.', 'INFO', 1],
            ['IMPORT_ONLY_COMPLETED_NOT_PROMOTED', 'IMPORT_PROMOTE', 'Import-only run was closed as completed non-readable candidate and still requires explicit promote.', 'INFO', 1],
            ['IMPORT_ONLY_NOT_PROMOTED', 'IMPORT_PROMOTE', 'Import-only run completed without readable publication or pointer switch.', 'INFO', 1],
            ['IMPORT_POINTER_WRITE_BLOCKED', 'IMPORT_PROMOTE', 'Import-only attempted to update current pointer.', 'HARD', 1],
            ['IMPORT_PROMOTE_BOUNDARY_VERIFIED', 'IMPORT_PROMOTE', 'Import/promote boundary was verified.', 'INFO', 1],
            ['IMPORT_PROMOTE_BOUNDARY_VIOLATION', 'IMPORT_PROMOTE', 'Import/promote boundary violation detected.', 'HARD', 1],
            ['IMPORT_PUBLICATION_CURRENT_BLOCKED', 'IMPORT_PROMOTE', 'Import-only attempted to mark a publication or run current.', 'HARD', 1],
            ['IMPORT_READABLE_STATE_BLOCKED', 'IMPORT_PROMOTE', 'Import-only attempted to mark a run readable.', 'HARD', 1],
            ['IMPORT_SIDE_EFFECT_BLOCKED', 'IMPORT_PROMOTE', 'Import-only side effect was blocked.', 'HARD', 1],
            ['IND_COMPUTE_ERROR', 'INDICATOR', 'Indicator computation failed because of logic or runtime error.', 'HARD', 1],
            ['IND_CORPORATE_ACTION_DISCONTINUITY', 'INDICATOR', 'At least one mandatory indicator window spans a corporate action that breaks price or volume continuity, so the affected fields were quarantined as NULL instead of published as arithmetically meaningless values.', 'WARN', 1],
            ['IND_INSUFFICIENT_HISTORY', 'INDICATOR', 'Required trading-day history is not yet sufficient for deterministic indicator computation.', 'WARN', 1],
            ['IND_INVALID_BAR_INPUT', 'INDICATOR', 'A canonical bar input required for indicator computation is invalid.', 'HARD', 1],
            ['IND_MISSING_DEPENDENCY_BAR', 'INDICATOR', 'A required canonical bar in the trading-day dependency chain is missing.', 'HARD', 1],
            ['IND_PRICE_SCALE_DISCONTINUITY', 'INDICATOR', 'At least one mandatory indicator window spans a detected price-scale break that no recorded corporate action explains, so the affected fields were quarantined as NULL.', 'WARN', 1],
            ['INDICATORS_ARTIFACT_EMPTY', 'ARTIFACT', 'Indicators artifact contained zero rows required for publication proof.', 'HARD', 1],
            ['MANUAL_FILE_ALL_ROWS_INVALID', 'SOURCE', 'Manual file rows were all rejected as invalid.', 'HARD', 1],
            ['MANUAL_FILE_EMPTY', 'SOURCE', 'Manual file contained no data rows.', 'HARD', 1],
            ['MANUAL_FILE_FORMAT_INVALID', 'SOURCE', 'Manual file format is invalid.', 'HARD', 1],
            ['MANUAL_FILE_HEADER_INVALID', 'SOURCE', 'Manual file header is missing or invalid.', 'HARD', 1],
            ['MANUAL_FILE_IMPORT_ACCEPTED', 'SOURCE', 'Manual file import was accepted as import-only context.', 'INFO', 1],
            ['MANUAL_FILE_IMPORT_BLOCKED', 'SOURCE', 'Manual file import was blocked by fail-safe policy.', 'HARD', 1],
            ['MANUAL_FILE_IMPORT_FAILED', 'SOURCE', 'Manual file import failed.', 'HARD', 1],
            ['MANUAL_FILE_IMPORT_ONLY_ACCEPTED', 'IMPORT_PROMOTE', 'Manual file import-only run accepted.', 'INFO', 1],
            ['MANUAL_FILE_IMPORT_ONLY_NOT_PROMOTED', 'IMPORT_PROMOTE', 'Manual file import-only run did not promote.', 'INFO', 1],
            ['MANUAL_FILE_MISSING', 'SOURCE', 'Manual file path was missing or unresolved.', 'HARD', 1],
            ['MANUAL_FILE_NO_VALID_ROWS', 'SOURCE', 'Manual file produced zero valid canonical rows.', 'HARD', 1],
            ['MANUAL_FILE_NOT_READABLE', 'SOURCE', 'Manual file output must remain not readable.', 'HARD', 1],
            ['MANUAL_FILE_PROMOTE_COMPLETED', 'IMPORT_PROMOTE', 'Manual file promote completed.', 'INFO', 1],
            ['MANUAL_FILE_PROMOTE_COVERAGE_FAILED', 'IMPORT_PROMOTE', 'Manual file promote coverage failed.', 'HARD', 1],
            ['MANUAL_FILE_PROMOTE_COVERAGE_REQUIRED', 'IMPORT_PROMOTE', 'Manual file promote requires coverage gate.', 'HARD', 1],
            ['MANUAL_FILE_PROMOTE_STARTED', 'IMPORT_PROMOTE', 'Manual file promote started.', 'INFO', 1],
            ['MANUAL_FILE_ROW_COUNT_MISMATCH', 'SOURCE', 'Manual file reported row count does not match accepted canonical row count.', 'HARD', 1],
            ['MANUAL_FILE_SOURCE_HASH_MISSING', 'SOURCE', 'Manual file source hash is missing and source identity cannot be proven.', 'HARD', 1],
            ['MANUAL_FILE_SOURCE_HASH_RECORDED', 'SOURCE', 'Manual file source hash was recorded.', 'INFO', 1],
            ['MANUAL_FILE_UNREADABLE', 'SOURCE', 'Manual file could not be opened or read.', 'HARD', 1],
            ['MARKET_CALENDAR_EVIDENCE_MISSING', 'CALENDAR', 'No authoritative or explicitly sourced calendar revision exists for the requested date.', 'HARD', 1],
            ['MARKET_CALENDAR_REQUIRES_REQUESTED_TRADING_DATE', 'COMMAND', 'The requested date is not an active trading day in market_calendar, so no trading-day window can be resolved for it.', 'HARD', 1],
            ['MARKET_SESSION_NOT_COMPLETED', 'CALENDAR', 'EOD acquisition was blocked because the regular-market session is not completed.', 'HARD', 1],
            ['MARKET_STRUCTURE_BOARD_NOT_POINT_IN_TIME', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because the listing-board value was recorded after the evaluated trade date.', 'HARD', 1],
            ['MARKET_STRUCTURE_BOARD_UNKNOWN', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because no listing-board value was available.', 'HARD', 1],
            ['MARKET_STRUCTURE_BOARD_UNRECOGNIZED', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because the listing-board value was not recognized by the locked scope.', 'HARD', 1],
            ['MARKET_STRUCTURE_REVISION_MISSING', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because one or more authoritative effective-dated rule revisions were unavailable.', 'HARD', 1],
            ['MARKET_STRUCTURE_SCOPE_EXCLUDED', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because the listing board is outside the standard-equity scope.', 'HARD', 1],
            ['NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT', 'RUN', 'Resume with only-failed found no failed source acquisition checkpoint to retry.', 'WARN', 1],
            ['NO_READABLE_PUBLICATION', 'READ_SIDE', 'A read-side consumer could not resolve a current readable publication through the authoritative pointer and must return no data.', 'HARD', 1],
            ['POINTER_ORPHAN_DETECTED', 'POINTER', 'Current pointer is orphaned from a valid publication/run lineage.', 'HARD', 1],
            ['POINTER_POST_SWITCH_MISMATCH', 'POINTER', 'Pointer resolver did not return the expected promoted publication after switch.', 'HARD', 1],
            ['POINTER_POST_SWITCH_VERIFIED', 'POINTER', 'Pointer resolver returned the promoted publication after switch.', 'INFO', 1],
            ['POINTER_PRESERVED_FAIL_SAFE', 'POINTER', 'Current pointer was preserved because candidate proof was unsafe or non-readable.', 'INFO', 1],
            ['POINTER_PUBLICATION_HASH_INVALID', 'POINTER', 'Current pointer target publication hash context is missing or mismatched.', 'HARD', 1],
            ['POINTER_PUBLICATION_ID_MISMATCH', 'POINTER', 'Current pointer validation found publication id mismatch between expected and resolved pointer target.', 'HARD', 1],
            ['POINTER_PUBLICATION_LINK_CREATED', 'POINTER', 'Current pointer linkage to a publication was created.', 'INFO', 1],
            ['POINTER_PUBLICATION_LINK_INVALID', 'POINTER', 'Current pointer linkage points to an invalid publication target.', 'HARD', 1],
            ['POINTER_PUBLICATION_LINK_MISSING', 'POINTER', 'Current pointer linkage is missing the target publication relationship.', 'HARD', 1],
            ['POINTER_PUBLICATION_LINK_VERIFIED', 'POINTER', 'Current pointer linkage to its target publication was verified.', 'INFO', 1],
            ['POINTER_PUBLICATION_NOT_FOUND', 'POINTER', 'Current pointer target publication row could not be found.', 'HARD', 1],
            ['POINTER_PUBLICATION_SEAL_INVALID', 'POINTER', 'Current pointer target publication is not sealed with valid seal metadata.', 'HARD', 1],
            ['POINTER_PUBLICATION_STATE_INVALID', 'POINTER', 'Current pointer target publication or run state is not readable/current-safe.', 'HARD', 1],
            ['POINTER_PUBLICATION_TRADE_DATE_MISMATCH', 'POINTER', 'Current pointer target publication trade date does not match pointer trade date.', 'HARD', 1],
            ['POINTER_PUBLICATION_VERSION_MISMATCH', 'POINTER', 'Current pointer validation found publication version mismatch.', 'HARD', 1],
            ['POINTER_RUN_ID_MISMATCH', 'POINTER', 'Current pointer validation found run id mismatch between pointer and publication/run context.', 'HARD', 1],
            ['POINTER_SEALED_AT_MISSING', 'POINTER', 'Current pointer validation found pointer sealed timestamp is missing.', 'HARD', 1],
            ['POINTER_SWITCH_BLOCKED_EMPTY_CANDIDATE', 'POINTER', 'Pointer switch was blocked because candidate publication proof was empty.', 'HARD', 1],
            ['POINTER_SWITCH_BLOCKED_INVALID_TARGET', 'POINTER', 'Pointer switch was blocked because target validation failed.', 'HARD', 1],
            ['POINTER_SWITCH_BLOCKED_NO_VALID_DATA', 'POINTER', 'Pointer switch was blocked because candidate had no valid data.', 'HARD', 1],
            ['POINTER_SWITCH_BLOCKED_NOT_READABLE', 'POINTER', 'Pointer switch was blocked because candidate was not readable.', 'HARD', 1],
            ['POINTER_SWITCH_BLOCKED_SOURCE_FAILURE', 'POINTER', 'Pointer switch was blocked because source acquisition failed.', 'HARD', 1],
            ['POINTER_SWITCH_COMPLETED', 'POINTER', 'Atomic pointer switch completed after validation.', 'INFO', 1],
            ['POINTER_SWITCH_FAILED', 'POINTER', 'Atomic pointer switch failed before a valid current publication was established.', 'HARD', 1],
            ['POINTER_SWITCH_ROLLED_BACK', 'POINTER', 'Pointer switch was rolled back or previous current publication was restored.', 'WARN', 1],
            ['POINTER_SWITCH_STARTED', 'POINTER', 'Atomic pointer switch validation started.', 'INFO', 1],
            ['PRICE_PRODUCT_UNRECORDED', 'READ_SIDE', 'A canonical bar in the resolved publication carries no price_product_code. The publication is withheld because a missing scale identity cannot be inferred as RAW; legacy rows remain unchanged until reconstructed through governed lifecycle.', 'HARD', 1],
            ['PROMOTE_BLOCKED', 'IMPORT_PROMOTE', 'Promote request was blocked before publication.', 'HARD', 1],
            ['PROMOTE_COMPLETED', 'IMPORT_PROMOTE', 'Promote request completed after all gates passed.', 'INFO', 1],
            ['PROMOTE_COVERAGE_FAILED', 'IMPORT_PROMOTE', 'Promote blocked because coverage gate failed.', 'HARD', 1],
            ['PROMOTE_COVERAGE_REQUIRED', 'IMPORT_PROMOTE', 'Promote requires coverage gate evaluation.', 'HARD', 1],
            ['PROMOTE_FINALIZE_REQUIRED', 'IMPORT_PROMOTE', 'Promote requires finalize decision.', 'HARD', 1],
            ['PROMOTE_HASH_REQUIRED', 'IMPORT_PROMOTE', 'Promote requires deterministic hash proof.', 'HARD', 1],
            ['PROMOTE_POINTER_SWITCH_BLOCKED', 'IMPORT_PROMOTE', 'Promote pointer switch was blocked.', 'HARD', 1],
            ['PROMOTE_POINTER_SWITCH_COMPLETED', 'IMPORT_PROMOTE', 'Promote completed current pointer switch.', 'INFO', 1],
            ['PROMOTE_POINTER_VALIDATION_REQUIRED', 'IMPORT_PROMOTE', 'Promote requires pointer target validation.', 'HARD', 1],
            ['PROMOTE_SEAL_REQUIRED', 'IMPORT_PROMOTE', 'Promote requires sealed dataset proof.', 'HARD', 1],
            ['PROMOTE_STARTED', 'IMPORT_PROMOTE', 'Promote request started.', 'INFO', 1],
            ['PROVIDER_EMPTY_OR_INVALID_RESPONSE', 'PROVIDER_SMOKE', 'Safe provider smoke returned no usable rows or an invalid provider payload.', 'HARD', 1],
            ['PROVIDER_NETWORK_ERROR', 'PROVIDER_SMOKE', 'Safe provider smoke hit a network or upstream transport failure before valid data was proven.', 'WARN', 1],
            ['PROVIDER_RATE_LIMITED', 'PROVIDER_SMOKE', 'Safe provider smoke was rate limited by the upstream provider and must not be counted as PASS.', 'WARN', 1],
            ['PROVIDER_REQUEST_HEADER_CONTEXT_MISMATCH', 'PROVIDER_SMOKE', 'Safe provider smoke proved the provider endpoint works with browser-like headers while a minimal PHP request context is blocked.', 'WARN', 1],
            ['PROVIDER_RESPONSE_PARSE_FAILED', 'PROVIDER_SMOKE', 'Safe provider smoke received an HTTP-success provider response but could not parse the payload safely.', 'HARD', 1],
            ['PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED', 'PROVIDER_SMOKE', 'Safe provider smoke blocked multi-ticker or full-universe execution.', 'HARD', 1],
            ['PROVIDER_SMOKE_INVALID_TICKER', 'PROVIDER_SMOKE', 'Safe provider smoke was blocked because the ticker format was invalid.', 'HARD', 1],
            ['PROVIDER_SMOKE_OK', 'PROVIDER_SMOKE', 'Safe single-ticker provider smoke returned valid data without publication, seal, finalize, full-universe fetch, or pointer switch.', 'INFO', 1],
            ['PROVIDER_SMOKE_TICKER_REQUIRED', 'PROVIDER_SMOKE', 'Safe provider smoke was blocked because no ticker was provided.', 'HARD', 1],
            ['PROVIDER_SYMBOL_MAPPING_AMBIGUOUS', 'IDENTITY', 'More than one provider symbol mapping is effective for the requested listing and date.', 'HARD', 1],
            ['PROVIDER_SYMBOL_MAPPING_MISSING', 'IDENTITY', 'No effective provider symbol mapping exists for the requested listing and date.', 'HARD', 1],
            ['PROVIDER_TIMEOUT', 'PROVIDER_SMOKE', 'Safe provider smoke timed out before a valid single-ticker response was proven.', 'WARN', 1],
            ['PROVIDER_TRADE_DATE_NOT_FOUND_IN_RESPONSE', 'PROVIDER_SMOKE', 'Safe provider smoke received provider data but the selected trade date was not present in returned timestamps.', 'HARD', 1],
            ['PUBLICATION_CANDIDATE_EMPTY', 'ARTIFACT', 'Publication candidate had no valid rows or proof context.', 'HARD', 1],
            ['PUBLICATION_NOT_MARKED_CURRENT', 'PUBLICATION', 'Current pointer validation found the publication row is not marked current.', 'HARD', 1],
            ['PUBLICATION_NOT_SEALED', 'PUBLICATION', 'Current pointer validation found the publication is not SEALED.', 'HARD', 1],
            ['PUBLICATION_REPROCESS_FAILED', 'PUBLICATION_REPROCESS', 'Publication reprocess failed before completing promote/hash/seal/finalize.', 'HARD', 1],
            ['PUBLICATION_REPROCESS_NOT_READABLE', 'PUBLICATION_REPROCESS', 'Affected-date promote flow completed without producing a readable publication.', 'HARD', 1],
            ['PUBLICATION_REPROCESS_REPLAY_FAILED', 'PUBLICATION_REPROCESS', 'Publication reprocess produced a readable run but requested replay verification failed.', 'HARD', 1],
            ['PUBLICATION_ROW_MISSING', 'PUBLICATION', 'Current pointer validation could not find the linked publication row.', 'HARD', 1],
            ['PUBLICATION_RUN_NOT_FOUND', 'PUBLICATION', 'Publication lineage points to a run id that cannot be found.', 'HARD', 1],
            ['PUBLICATION_RUN_STATE_INVALID', 'PUBLICATION', 'Publication lineage points to a run whose state cannot produce a readable/current publication.', 'HARD', 1],
            ['PUBLICATION_SEALED_AT_MISSING', 'PUBLICATION', 'Current pointer validation found a sealed publication without sealed timestamp.', 'HARD', 1],
            ['PUBLICATION_TRADE_DATE_MISMATCH', 'PUBLICATION', 'Current pointer validation found publication trade date mismatch.', 'HARD', 1],
            ['PUBLISHABILITY_BLOCKED_COVERAGE_NOT_EVALUABLE', 'PUBLISHABILITY', 'Publishability was blocked because coverage was not evaluable.', 'HARD', 1],
            ['PUBLISHABILITY_BLOCKED_EMPTY_ARTIFACT', 'PUBLISHABILITY', 'Publishability was blocked because artifact proof was empty.', 'HARD', 1],
            ['PUBLISHABILITY_BLOCKED_MISSING_SEAL', 'PUBLISHABILITY', 'Publishability was blocked because seal proof was missing.', 'HARD', 1],
            ['PUBLISHABILITY_BLOCKED_NO_VALID_DATA', 'PUBLISHABILITY', 'Publishability was blocked because no valid data existed.', 'HARD', 1],
            ['PUBLISHABILITY_BLOCKED_POINTER_INVALID', 'PUBLISHABILITY', 'Publishability was blocked because pointer target was invalid.', 'HARD', 1],
            ['PUBLISHABILITY_BLOCKED_SOURCE_FAILURE', 'PUBLISHABILITY', 'Publishability was blocked because source acquisition failed.', 'HARD', 1],
            ['PUBLISHABILITY_NOT_READABLE_FAIL_SAFE', 'PUBLISHABILITY', 'Publishability was forced to not readable by fail-safe policy.', 'HARD', 1],
            ['READABLE_PUBLICATION_RESOLVED', 'READ_SIDE', 'A read-side consumer resolved a current sealed readable publication through the authoritative pointer.', 'INFO', 1],
            ['REPLAY_ACTUAL_PROOF_INCOMPLETE', 'REPLAY', 'Replay actual proof package is missing required lifecycle evidence.', 'HARD', 1],
            ['REPLAY_ARTIFACT_HASH_MISMATCH', 'REPLAY', 'Replay artifact hash or artifact row-count context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_CONFIG_IDENTITY_MISMATCH', 'REPLAY', 'Replay ran under a different configuration identity than the recorded run. The dataset may be reproducible; the configuration it was produced under was not the same one, so compare config before treating the difference as non-determinism.', 'HARD', 1],
            ['REPLAY_CORRECTION_BASELINE_MISMATCH', 'REPLAY', 'Replay correction baseline or candidate publication context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_CORRECTION_LINEAGE_MISMATCH', 'REPLAY', 'Replay detected correction baseline/replacement lineage mismatch.', 'HARD', 1],
            ['REPLAY_COVERAGE_RATIO_MISMATCH', 'REPLAY', 'Replay coverage ratio or threshold differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_COVERAGE_REASON_MISMATCH', 'REPLAY', 'Replay coverage reason code differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_COVERAGE_STATE_MISMATCH', 'REPLAY', 'Replay coverage gate state or coverage count context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_CURRENT_POINTER_MOVED_HISTORICAL_VALID', 'REPLAY', 'Replay verified a historical sealed publication while the current pointer has moved to another publication.', 'INFO', 1],
            ['REPLAY_CURRENT_PUBLICATION_RESOLVED', 'REPLAY', 'Replay resolved the current readable publication for current-context actual-state proof.', 'INFO', 1],
            ['REPLAY_EFFECTIVE_DATE_MISMATCH', 'REPLAY', 'Replay effective trade date differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_EMPTY_ARTIFACT_MISMATCH', 'REPLAY', 'Replay detected empty-artifact context mismatch.', 'HARD', 1],
            ['REPLAY_EXPECTED_HISTORICAL_ACTUAL_CURRENT_MISMATCH', 'REPLAY', 'Replay expected a historical publication context but actual state resolved a current publication context, or the reverse.', 'HARD', 1],
            ['REPLAY_EXPECTED_PROOF_INCOMPLETE', 'REPLAY', 'Replay expected proof package is missing required deterministic lifecycle context.', 'HARD', 1],
            ['REPLAY_FAIL_SAFE_CONTEXT_MISSING', 'REPLAY', 'Replay proof is missing required fail-safe context.', 'HARD', 1],
            ['REPLAY_FAIL_SAFE_REASON_MISMATCH', 'REPLAY', 'Replay detected a mismatch in expected vs actual fail-safe reason context.', 'HARD', 1],
            ['REPLAY_FALLBACK_CONTEXT_MISMATCH', 'REPLAY', 'Replay fallback context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_FINAL_REASON_CODE_MISMATCH', 'REPLAY', 'Replay final reason code or reason-code counts differ between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_FINAL_STATUS_MISMATCH', 'REPLAY', 'Replay final terminal or publishability state differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_FIXTURE_SCHEMA_MISMATCH', 'REPLAY', 'Replay fixture manifest or schema version does not match the locked replay fixture contract.', 'HARD', 1],
            ['REPLAY_HISTORICAL_ARTIFACT_SCOPE_MISMATCH', 'REPLAY', 'Replay historical actual-state artifact scope is not publication-scoped to the selected publication.', 'HARD', 1],
            ['REPLAY_HISTORICAL_PUBLICATION_MISSING', 'REPLAY', 'Replay historical actual-state selector did not resolve a publication.', 'HARD', 1],
            ['REPLAY_HISTORICAL_PUBLICATION_RESOLVED', 'REPLAY', 'Replay resolved a selector-scoped historical sealed publication for actual-state proof without current pointer fallback.', 'INFO', 1],
            ['REPLAY_HISTORICAL_PUBLICATION_UNSEALED', 'REPLAY', 'Replay historical actual-state publication is not sealed.', 'HARD', 1],
            ['REPLAY_IMPORT_PROMOTE_MATCHED', 'REPLAY', 'Replay import/promote context matched expected proof.', 'INFO', 1],
            ['REPLAY_IMPORT_PROMOTE_MISMATCH', 'REPLAY', 'Replay import/promote context mismatch.', 'HARD', 1],
            ['REPLAY_IMPORT_STATUS_MISMATCH', 'REPLAY', 'Replay import status mismatch.', 'HARD', 1],
            ['REPLAY_LINEAGE_MATCHED', 'REPLAY', 'Replay lineage matched expected run-publication-pointer-correction proof.', 'INFO', 1],
            ['REPLAY_LINEAGE_MISMATCH', 'REPLAY', 'Replay lineage chain differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_MATCH', 'REPLAY', 'Replay expected proof matched observed proof across deterministic fields.', 'INFO', 1],
            ['REPLAY_MISMATCH', 'REPLAY', 'Replay found a difference that carries no more specific reason code. Seeing this means a comparison produced a mismatch without classifying it, so the specific code is the thing to add rather than this one being the answer.', 'HARD', 1],
            ['REPLAY_NO_PUBLICATION_ACTUAL_STATE', 'REPLAY', 'Replay built actual state for a run that has no readable publication proof.', 'INFO', 1],
            ['REPLAY_NO_VALID_DATA_MISMATCH', 'REPLAY', 'Replay detected no-valid-data context mismatch.', 'HARD', 1],
            ['REPLAY_NON_DETERMINISTIC_OUTPUT', 'REPLAY', 'Replay output contains a deterministic-field mismatch not covered by a more specific replay reason code.', 'HARD', 1],
            ['REPLAY_POINTER_PUBLICATION_MISMATCH', 'REPLAY', 'Replay detected a pointer-publication lineage mismatch.', 'HARD', 1],
            ['REPLAY_POINTER_RESOLUTION_MISMATCH', 'REPLAY', 'Replay pointer resolution state differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_POINTER_TARGET_MISMATCH', 'REPLAY', 'Replay pointer target differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_PROMOTE_STATUS_MISMATCH', 'REPLAY', 'Replay promote status mismatch.', 'HARD', 1],
            ['REPLAY_PROVIDER_CONTEXT_MISMATCH', 'REPLAY', 'Replay provider/API retry, timeout, or HTTP context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_PUBLICATION_RUN_MISMATCH', 'REPLAY', 'Replay historical actual-state publication does not belong to the selected run or mirror context.', 'HARD', 1],
            ['REPLAY_PUBLICATION_STATE_MISMATCH', 'REPLAY', 'Replay publication state/readability context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_PUBLICATION_VERSION_MISMATCH', 'REPLAY', 'Replay publication version differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_REQUEST_MODE_MISMATCH', 'REPLAY', 'Replay request/promote/publish target context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_REQUESTED_DATE_MISMATCH', 'REPLAY', 'Replay requested trade date differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_RUN_PUBLICATION_MISMATCH', 'REPLAY', 'Replay detected a run-publication lineage mismatch.', 'HARD', 1],
            ['REPLAY_SEAL_STATE_MISMATCH', 'REPLAY', 'Replay seal state differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_SOURCE_FAILURE_MISMATCH', 'REPLAY', 'Replay detected source-failure context mismatch.', 'HARD', 1],
            ['REPLAY_SOURCE_FILE_HASH_MISMATCH', 'REPLAY', 'Replay manual source file hash differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_SOURCE_IDENTITY_MISMATCH', 'REPLAY', 'Replay source identity or source row-count context differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_SOURCE_MODE_MISMATCH', 'REPLAY', 'Replay source mode differs between expected proof and actual proof.', 'HARD', 1],
            ['REPLAY_UNEXPECTED_FAILURE', 'REPLAY', 'Replay produced failure when the expected proof required a successful deterministic match.', 'HARD', 1],
            ['REPLAY_UNEXPECTED_POINTER_SWITCH', 'REPLAY', 'Replay detected unexpected pointer switch.', 'HARD', 1],
            ['REPLAY_UNEXPECTED_PUBLICATION_PROMOTION', 'REPLAY', 'Replay detected unexpected publication promotion or pointer switch.', 'HARD', 1],
            ['REPLAY_UNEXPECTED_READABLE_OUTPUT', 'REPLAY', 'Replay detected unexpected readable output.', 'HARD', 1],
            ['REPLAY_UNEXPECTED_SUCCESS', 'REPLAY', 'Replay produced a success-looking result when the expected proof required failure or degrade.', 'HARD', 1],
            ['REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE', 'IMPORT_PROMOTE', 'Import-only request attempted to enter a promote/publish stage.', 'HARD', 1],
            ['REQUEST_MODE_INVALID', 'IMPORT_PROMOTE', 'Request mode is not one of the allowed market-data intents.', 'HARD', 1],
            ['REQUEST_MODE_MISSING', 'IMPORT_PROMOTE', 'Request mode is missing from a run context that requires explicit intent.', 'HARD', 1],
            ['REQUEST_MODE_PROMOTE_GATE_REQUIRED', 'IMPORT_PROMOTE', 'Promote request requires publishability gates before publication.', 'HARD', 1],
            ['REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE', 'PUBLICATION_REPROCESS', 'Requested date was already handled by primary promote/hash/seal/finalize.', 'INFO', 1],
            ['RUN_COMPUTE_FAILED', 'RUN', 'Indicator computation stage failed and the run cannot continue silently.', 'HARD', 1],
            ['RUN_COVERAGE_EVALUATION_FAILED', 'RUN', 'Coverage evaluation failed before a deterministic gate result could be persisted.', 'HARD', 1],
            ['RUN_COVERAGE_GATE_NOT_PASS', 'RUN', 'Publication pointer validation found a run whose coverage gate state is not PASS.', 'HARD', 1],
            ['RUN_COVERAGE_LOW', 'RUN', 'Coverage ratio for the requested date is below the locked minimum threshold.', 'HARD', 1],
            ['RUN_COVERAGE_NOT_EVALUABLE', 'RUN', 'Coverage could not be evaluated meaningfully for the requested date, so requested-date publication must remain not readable.', 'HARD', 1],
            ['RUN_COVERAGE_TELEMETRY_INVALID', 'RUN', 'Readable pointer validation found invalid coverage telemetry for a candidate run.', 'HARD', 1],
            ['RUN_CURRENT_MIRROR_NOT_SET', 'RUN', 'Readable pointer validation found that the run current-publication mirror is not set.', 'HARD', 1],
            ['RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED', 'RUN', 'Current publication mirror or pointer integrity was repaired or fail-safed to preserve readable-state contract.', 'WARN', 1],
            ['RUN_DATA_DELAYED', 'RUN', 'Coverage failed while requested-date data was still inside the controlled delayed-data window.', 'WARN', 1],
            ['RUN_ELIGIBILITY_FAILED', 'RUN', 'Eligibility build stage failed and the run cannot continue silently.', 'HARD', 1],
            ['RUN_ELIGIBILITY_MISSING', 'RUN', 'Eligibility snapshot for the requested date is not available.', 'HARD', 1],
            ['RUN_FINALIZE_BEFORE_CUTOFF', 'RUN', 'Final success was attempted before the cutoff policy allowed it.', 'HARD', 1],
            ['RUN_FINALIZE_FAILED', 'RUN', 'Finalize stage failed before a safe terminal state could be completed.', 'HARD', 1],
            ['RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID', 'RUN', 'A previously completed readable finalize run no longer matches the current publication pointer and must be fail-safed before idempotent short-circuit.', 'HARD', 1],
            ['RUN_HASH_FAILED', 'RUN', 'Hash computation failed or produced unusable output.', 'HARD', 1],
            ['RUN_HASH_MISSING', 'RUN', 'One or more mandatory content hashes are missing at finalization time.', 'HARD', 1],
            ['RUN_INDICATORS_MISSING', 'RUN', 'Required indicator artifact or required indicator row set for the requested date is not available.', 'HARD', 1],
            ['RUN_KNOWLEDGE_CUTOFF_MISSING', 'RUN', 'Execution was blocked because the selected run predates the mandatory creation-time knowledge cutoff and cannot be resumed deterministically.', 'HARD', 1],
            ['RUN_LOCK_CONFLICT', 'RUN', 'Run-ownership conflict or duplicate writer activity occurred during hash, seal, or finalize stages.', 'HARD', 1],
            ['RUN_NON_CURRENT_PROMOTION', 'RUN', 'Promotion was requested for a target that must not become the current readable publication.', 'HARD', 1],
            ['RUN_PARTIAL_DATA', 'RUN', 'Coverage failed because only part of the requested-date universe had canonical valid EOD data.', 'HARD', 1],
            ['RUN_PUBLICATION_ID_MISMATCH', 'RUN', 'Readable pointer validation found a mismatch between run publication id and pointer publication id.', 'HARD', 1],
            ['RUN_PUBLICATION_LINK_CREATED', 'RUN', 'Publication lineage link was created from a valid originating run.', 'INFO', 1],
            ['RUN_PUBLICATION_LINK_INVALID', 'RUN', 'Publication lineage points to an invalid originating run or invalid publication context.', 'HARD', 1],
            ['RUN_PUBLICATION_LINK_MISSING', 'RUN', 'Publication lineage is missing either the publication row or originating run.', 'HARD', 1],
            ['RUN_PUBLICATION_LINK_VERIFIED', 'RUN', 'Publication lineage link to its originating run was verified.', 'INFO', 1],
            ['RUN_PUBLICATION_MIRROR_MISMATCH', 'RUN', 'Run-publication mirror fields disagree across run, publication, pointer, or trade-date context.', 'HARD', 1],
            ['RUN_PUBLICATION_VERSION_MISMATCH', 'RUN', 'Readable pointer validation found a mismatch between run publication version and pointer publication version.', 'HARD', 1],
            ['RUN_PUBLISHABILITY_NOT_READABLE', 'RUN', 'Publication pointer validation found a run whose publishability state is not READABLE.', 'HARD', 1],
            ['RUN_REPAIR_CANDIDATE_PARTIAL', 'RUN', 'Repair candidate is intentionally partial and must not be promoted as normal current readable data.', 'WARN', 1],
            ['RUN_ROW_MISSING', 'RUN', 'Readable pointer validation could not find the linked run row.', 'HARD', 1],
            ['RUN_SEAL_PRECONDITION_FAILED', 'RUN', 'Seal execution was attempted before all locked preconditions were satisfied.', 'HARD', 1],
            ['RUN_SEAL_WRITE_FAILED', 'RUN', 'Seal metadata could not be written successfully.', 'HARD', 1],
            ['RUN_SEALED_AT_MISSING', 'RUN', 'Readable pointer validation found the linked run has no sealed timestamp.', 'HARD', 1],
            ['RUN_SOURCE_AUTH_ERROR', 'RUN', 'Source authentication failure or credential/config error blocked data acquisition.', 'HARD', 1],
            ['RUN_SOURCE_BAD_REQUEST', 'RUN', 'Source provider returned HTTP 400 or equivalent bad request during acquisition; diagnostic context must identify ticker/window/systemic scope.', 'HARD', 1],
            ['RUN_SOURCE_INVALID_SYMBOL', 'RUN', 'Source provider rejected an individual ticker/symbol; partial acquisition may continue and coverage gate decides publishability.', 'WARN', 1],
            ['RUN_SOURCE_MALFORMED_PAYLOAD', 'RUN', 'The source payload could not be normalized safely.', 'HARD', 1],
            ['RUN_SOURCE_MANUAL_FILE_EMPTY', 'RUN', 'Manual file existed but contained no data rows; empty manual file import/promote is blocked.', 'HARD', 1],
            ['RUN_SOURCE_MANUAL_FILE_MALFORMED', 'RUN', 'The configured manual-file source could not be parsed or normalized safely.', 'HARD', 1],
            ['RUN_SOURCE_MANUAL_FILE_MISSING_ROW', 'RUN', 'Manual file was readable but contained no row for an expected ticker/date pair; the affected ticker is reported as failed while other tickers may still succeed.', 'WARN', 1],
            ['RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS', 'RUN', 'Manual file rows were parsed but no row produced a valid canonical bar; run must remain non-readable.', 'HARD', 1],
            ['RUN_SOURCE_MANUAL_FILE_NOT_FOUND', 'RUN', 'The configured manual-file source was not found.', 'HARD', 1],
            ['RUN_SOURCE_MANUAL_FILE_NOT_READABLE', 'RUN', 'The configured manual-file source could not be opened or read.', 'HARD', 1],
            ['RUN_SOURCE_MODE_UNSUPPORTED', 'RUN', 'The requested source mode is not supported by the selected source adapter.', 'HARD', 1],
            ['RUN_SOURCE_NO_VALID_DATA', 'RUN', 'Source acquisition produced zero valid canonical EOD bars; empty source output must remain non-readable and must not publish.', 'HARD', 1],
            ['RUN_SOURCE_PARTIAL_COVERAGE', 'RUN', 'The source returned incomplete symbol coverage for the requested date.', 'WARN', 1],
            ['RUN_SOURCE_PARTIAL_RESPONSE', 'RUN', 'The source adapter returned only part of the requested provider response and coverage gate must decide publishability.', 'WARN', 1],
            ['RUN_SOURCE_PROVIDER_REJECTED_RANGE', 'RUN', 'Source provider rejected the requested acquisition range/window or global request parameters.', 'HARD', 1],
            ['RUN_SOURCE_RATE_LIMIT', 'RUN', 'The source hit rate limiting and affected data acquisition.', 'WARN', 1],
            ['RUN_SOURCE_RESPONSE_CHANGED', 'RUN', 'A source schema or response-contract change was detected.', 'HARD', 1],
            ['RUN_SOURCE_TIMEOUT', 'RUN', 'The source timed out and retry policy was already applied or exhausted.', 'WARN', 1],
            ['RUN_STALE_DATA', 'RUN', 'Source rows were outside the requested trade date and must not count as available coverage.', 'HARD', 1],
            ['RUN_TERMINAL_STATUS_NOT_SUCCESS', 'RUN', 'Publication pointer validation found a run whose terminal status is not SUCCESS.', 'HARD', 1],
            ['SEAL_TARGET_EMPTY', 'ARTIFACT', 'Seal target was empty and cannot be sealed as readable.', 'HARD', 1],
            ['SEALED_DATASET_MUTATION_BLOCKED', 'DATASET', 'Runtime attempted to mutate a sealed/finalized/readable dataset through a normal artifact path and was blocked.', 'HARD', 1],
            ['SECTOR_INDEX_API_PARTIAL_RESPONSE', 'SOURCE', 'Sector index API returned fewer index codes than requested and partial acceptance was not enabled, so the ingest is blocked rather than storing an incomplete index set.', 'HARD', 1],
            ['SECTOR_MEMBERSHIP_EFFECTIVE_FROM_CORRECTED_TO_LISTING', 'SECTOR_CLASSIFICATION', 'Legacy membership effective date was corrected from the IDX-IC launch date to the instrument listing date, because the instrument was not yet listed on the launch date and therefore could not carry a classification from it.', 'INFO', 1],
            ['SECTOR_MEMBERSHIP_IMPORT', 'SECTOR_CLASSIFICATION', 'Operator-entered sector membership revision was imported with explicit provenance and governance.', 'INFO', 1],
            ['SECTOR_MEMBERSHIP_IMPORT_LOCK_UNAVAILABLE', 'SECTOR_CLASSIFICATION', 'A sector membership import could not take the serialising import lock because another import holds it; the command stopped before writing anything rather than validating one world and applying to another.', 'HARD', 1],
            ['SECTOR_MEMBERSHIP_LEGACY_RECLASSED_DERIVED', 'SECTOR_CLASSIFICATION', 'Pre-import legacy membership row was declared DERIVED_REFERENCE because its source is a profile or new-listing page rather than a dated IDX-IC classification announcement; it may corroborate but never establish membership.', 'INFO', 1],
            ['SESSION_SNAPSHOT_FEATURE_DISABLED', 'COMMAND', 'Optional session-snapshot capture was requested while the feature is disabled; EOD readiness is unaffected.', 'INFO', 1],
            ['SESSION_SNAPSHOT_SCOPE_UNRECOGNISED', 'COMMAND', 'Configured session-snapshot scope is not an upstream-safe value, so scope could not be resolved safely.', 'HARD', 1],
            ['SNAP_PARTIAL_SCOPE', 'INTRADAY', 'The session snapshot captured only part of the planned scope.', 'WARN', 1],
            ['SNAP_SOURCE_ERROR', 'INTRADAY', 'The session-snapshot source failed for an operational reason that does not block EOD.', 'WARN', 1],
            ['SNAP_SOURCE_RATE_LIMIT', 'INTRADAY', 'The session-snapshot source hit rate limiting.', 'WARN', 1],
            ['SNAP_SOURCE_TIMEOUT', 'INTRADAY', 'The session-snapshot source timed out.', 'WARN', 1],
            ['SOURCE_ALL_SYMBOLS_FAILED', 'SOURCE', 'All requested symbols failed source acquisition.', 'HARD', 1],
            ['SOURCE_FAILURE_HELD', 'SOURCE', 'Source failure caused the run to be held safely without readable publication.', 'WARN', 1],
            ['SOURCE_FAILURE_NOT_READABLE', 'SOURCE', 'Source failure caused the output to remain not readable.', 'HARD', 1],
            ['SOURCE_IMPORT_NOT_PROMOTED', 'SOURCE', 'Source import completed without promotion.', 'INFO', 1],
            ['SOURCE_MODE_IMMUTABLE', 'SOURCE', 'Source mode changed within a run and was blocked.', 'HARD', 1],
            ['SOURCE_MODE_INVALID', 'SOURCE', 'Source mode is invalid.', 'HARD', 1],
            ['SOURCE_MODE_MISSING', 'SOURCE', 'Source mode is missing.', 'HARD', 1],
            ['SOURCE_MODE_VERIFIED', 'SOURCE', 'Source mode and source identity were verified.', 'INFO', 1],
            ['SOURCE_NO_VALID_DATA', 'SOURCE', 'Canonical fail-safe alias for source acquisition that produced no valid data.', 'HARD', 1],
            ['SOURCE_OBSERVATION_CAPTURE_REQUIRED', 'SOURCE', 'Canonicalization was blocked because raw source evidence was not durably captured first.', 'HARD', 1],
            ['SOURCE_OBSERVATION_NOT_ACCEPTED', 'SOURCE', 'Canonicalization was blocked because the bound source observation is not accepted.', 'HARD', 1],
            ['SOURCE_OBSERVATION_PERSISTENCE_FAILED', 'SOURCE', 'Acquisition was blocked because immutable observation capture or outcome persistence failed.', 'HARD', 1],
            ['SOURCE_PROVIDER_EMPTY_RESPONSE', 'SOURCE', 'Canonical fail-safe alias for provider response that contained no usable rows.', 'HARD', 1],
            ['SOURCE_PROVIDER_HTTP_ERROR', 'SOURCE', 'Source provider returned a non-transient HTTP error that must not be treated as successful data.', 'HARD', 1],
            ['SOURCE_PROVIDER_MALFORMED_RESPONSE', 'SOURCE', 'Source provider response could not be parsed into canonical market-data payload.', 'HARD', 1],
            ['SOURCE_PROVIDER_PARTIAL_RESPONSE', 'SOURCE', 'Source provider returned only partial usable response context.', 'WARN', 1],
            ['SOURCE_PROVIDER_RATE_LIMITED', 'SOURCE', 'Source provider rate limited the request.', 'WARN', 1],
            ['SOURCE_PROVIDER_RETRY_EXHAUSTED', 'SOURCE', 'Source provider retry policy was exhausted without usable data.', 'HARD', 1],
            ['SOURCE_PROVIDER_TIMEOUT', 'SOURCE', 'Source provider timed out.', 'WARN', 1],
            ['SOURCE_PROVIDER_UNAVAILABLE', 'SOURCE', 'Source provider unavailable.', 'WARN', 1],
            ['SOURCE_SCALE_MARKET_STRUCTURE_UNRESOLVED', 'CORPORATE_ACTION', 'Yahoo source scale remained unknown because admissible point-in-time market-structure context was insufficient; factor activation was prohibited.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_CLEANUP_FAILED', 'SOURCE', 'The temporary Stage 8 date-row cache could not be removed after a successful oracle.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_CLEANUP_REFUSED', 'SOURCE', 'Stage 8 refused cache cleanup because the bounded rows directory contained an unexpected entry.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_DATE_INVALID', 'SOURCE', 'A Stage 8 per-date acquisition cache contained an invalid batch record.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_DATE_MISSING', 'SOURCE', 'A Stage 8 target had no per-date acquisition cache after acquisition completed.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_DATE_UNREADABLE', 'SOURCE', 'A Stage 8 per-date acquisition cache could not be opened.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_IDENTITY_MISMATCH', 'SOURCE', 'A resumable Stage 8 acquisition cache did not match the frozen campaign/date/ticker identity.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_MANIFEST_INVALID', 'SOURCE', 'The resumable Stage 8 acquisition manifest was not valid structured JSON.', 'HARD', 1],
            ['STAGE8_ACQUISITION_CACHE_WRITE_FAILED', 'SOURCE', 'The resumable Stage 8 Yahoo acquisition cache could not be written durably.', 'HARD', 1],
            ['STAGE8_ADMISSION_EVIDENCE_INVALID', 'GOVERNANCE', 'Stage 8 admission evidence was absent, inconsistent, or not hash-verifiable.', 'HARD', 1],
            ['STAGE8_ADMISSION_SUFFIX_NOT_FOUND', 'COVERAGE', 'No continuous suffix met the locked coverage and quality requirements.', 'HARD', 1],
            ['STAGE8_BASELINE_HASH_MISSING', 'CORRECTION', 'A frozen current baseline lacked a mandatory artifact hash and was ineligible for reconstruction.', 'HARD', 1],
            ['STAGE8_BASELINE_NOT_SEALED', 'CORRECTION', 'A current baseline was not a sealed successful publication and was ineligible for reconstruction.', 'HARD', 1],
            ['STAGE8_BASELINE_POINTER_DRIFT', 'CORRECTION', 'A current publication pointer no longer matches the baseline frozen by the Stage 8 campaign, so reconstruction stopped.', 'HARD', 1],
            ['STAGE8_BLOCKED_CAMPAIGN_SUPERSEDED', 'GOVERNANCE', 'A blocked full-range Stage 8 campaign was superseded by an explicit measured admission decision without changing its immutable attempts.', 'INFO', 1],
            ['STAGE8_CAMPAIGN_NOT_FOUND', 'CORRECTION', 'The requested Stage 8 reconstruction campaign does not exist.', 'HARD', 1],
            ['STAGE8_CAMPAIGN_RESUME_REQUIRED', 'CORRECTION', 'An unfinished Stage 8 campaign already exists and must be explicitly resumed.', 'HARD', 1],
            ['STAGE8_COMPLETED_CAMPAIGN_ORACLE_DRIFT', 'CORRECTION', 'A completed Stage 8 campaign no longer satisfies its frozen completion oracle.', 'HARD', 1],
            ['STAGE8_CONFORMANT_SUFFIX_ADMITTED', 'GOVERNANCE', 'A measured continuous suffix met the locked coverage threshold and quality requirements under verified status evidence.', 'INFO', 1],
            ['STAGE8_CURRENT_CORPUS_ORACLE_FAILED', 'CORRECTION', 'The final Stage 8 current-corpus oracle found one or more violations.', 'HARD', 1],
            ['STAGE8_CURRENT_CORPUS_RECONSTRUCTION', 'CORRECTION', 'A frozen current EOD publication is being replaced through the complete correction lifecycle using a fresh Yahoo observation set while retaining the immutable baseline.', 'INFO', 1],
            ['STAGE8_DATE_NOT_READABLE', 'CORRECTION', 'A reconstructed trade date did not end in a successful readable publication and its current pointer was not accepted.', 'HARD', 1],
            ['STAGE8_FROZEN_SCOPE_EMPTY', 'CORRECTION', 'Stage 8 could not freeze a reconstruction scope because no current publication pointers exist.', 'HARD', 1],
            ['STAGE8_FROZEN_SCOPE_POINTER_CALENDAR_MISMATCH', 'CORRECTION', 'The frozen current-pointer dates did not exactly match the authoritative trading-calendar dates.', 'HARD', 1],
            ['STAGE8_OUTPUT_DIRECTORY_CREATE_FAILED', 'CORRECTION', 'The Stage 8 resumable campaign output directory could not be created.', 'HARD', 1],
            ['STAGE8_POINTER_SWITCH_NOT_PROVEN', 'CORRECTION', 'A reconstructed trade date could not prove that its current pointer switched to the new sealed publication.', 'HARD', 1],
            ['STAGE8_PRE_ADMISSION_READ_BLOCKED', 'READINESS', 'A requested date precedes the active conformant-corpus admission boundary and is not consumer-readable.', 'HARD', 1],
            ['STAGE8_RECONSTRUCTION_FAILED', 'CORRECTION', 'A Stage 8 reconstruction target failed without a more specific registered reason and remained incomplete.', 'HARD', 1],
            ['STAGE8_SOURCE_ACQUISITION_FAILED', 'SOURCE', 'The bounded Stage 8 Yahoo range acquisition failed systemically.', 'HARD', 1],
            ['STALE_ACTIVE_RUN_CANCELLED', 'RUN', 'Stale active run was cancelled because it no longer represented an active process.', 'WARN', 1],
            ['TEMPORAL_IDENTITY_PROJECTION_INCOMPLETE', 'IDENTITY', 'Stage 8 found legacy ticker identities not projected into the temporal identity model and stopped without mutating them.', 'HARD', 1],
            ['TEMPORAL_LISTING_MAPPING_MISSING', 'IDENTITY', 'No unambiguous point-in-time listing identity could be bound for the requested date.', 'HARD', 1],
            ['TRADING_STATUS_CONFLICT', 'TRADING_STATUS', 'Conflicting verified trading-status revisions prevent a deterministic status decision.', 'HARD', 1],
            ['TRADING_STATUS_NO_EVIDENCE', 'TRADING_STATUS', 'No verified point-in-time trading-status evidence exists; status remains unknown.', 'WARN', 1],
            ['TRADING_STATUS_REVISION_BINDING_MISSING', 'STATUS', 'A BAR_NOT_EXPECTED eligibility fact lacks its verified trading-status revision and source-observation binding.', 'HARD', 1],
        ]), 100) as $chunk) {
            DB::table('eod_reason_codes')->insert($chunk);
        }

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
            $table->string('coverage_reason_code', 64)->nullable();
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
            $table->string('liquidity_formula_version', 64)->nullable();
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
            $table->string('source_provenance_state', 32)->nullable();
            $table->string('price_basis_state', 32)->nullable();
            $table->string('contamination_state', 32)->nullable();
            $table->string('indicator_state', 32)->nullable();
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
            $table->string('replay_mode', 32)->nullable();
            $table->dateTime('knowledge_cutoff_at')->nullable();
            $table->string('fixture_manifest_hash', 64)->nullable();
            $table->string('source_observation_manifest_hash', 64)->nullable();
            $table->string('canonical_raw_input_hash', 64)->nullable();
            $table->string('temporal_identity_hash', 64)->nullable();
            $table->string('calendar_status_hash', 64)->nullable();
            $table->string('event_factor_hash', 64)->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->string('config_snapshot_hash', 64)->nullable();
            $table->string('formula_registry_hash', 64)->nullable();
            $table->string('reason_registry_hash', 64)->nullable();
            $table->string('read_model_version', 64)->nullable();
            $table->string('serialization_version', 64)->nullable();
            $table->string('executable_build_identity', 128)->nullable();
            $table->string('admission_state', 32)->nullable();
            $table->text('bound_input_context_json')->nullable();
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
            $table->string('coverage_reason_code', 64)->nullable();
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
            $table->string('expected_coverage_reason_code', 64)->nullable();
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
            $table->string('liquidity_formula_version', 64)->nullable();
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
            $table->string('source_provenance_state', 32)->nullable();
            $table->string('price_basis_state', 32)->nullable();
            $table->string('contamination_state', 32)->nullable();
            $table->string('indicator_state', 32)->nullable();
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
        require_once dirname(__DIR__, 2).'/database/migrations/2026_09_16_000001_add_producer_bound_input_captures.php';
        (new \AddProducerBoundInputCaptures())->up();
        require_once dirname(__DIR__, 2).'/database/migrations/2026_09_21_000001_add_publication_lineage_binding_seal_immutability.php';
        (new \AddPublicationLineageBindingSealImmutability())->up();
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
            $table->string('source_volume_unit_code', 32)->nullable();
            $table->decimal('volume_unit_normalization_factor', 18, 8)->nullable();
            $table->string('volume_unit_normalization_state', 32)->nullable();
            $table->string('volume_unit_evidence_ref', 255)->nullable();
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
            // Knowledge time of the delisting itself; see F-MD-B18-A002-002.
            $table->dateTime('delisted_recorded_at')->nullable();
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


        $schema->create('md_price_scale_break_candidates', function (Blueprint $table) {
            $table->bigIncrements('candidate_id');
            $table->string('candidate_uid', 64)->unique();
            $table->integer('listing_id');
            $table->date('prior_trade_date');
            $table->date('current_trade_date');
            $table->integer('prior_publication_id')->nullable();
            $table->integer('current_publication_id')->nullable();
            $table->integer('prior_source_observation_id')->nullable();
            $table->integer('current_source_observation_id')->nullable();
            $table->decimal('prior_close', 24, 8);
            $table->decimal('current_open', 24, 8);
            $table->decimal('diagnostic_ratio', 24, 12);
            $table->string('ratio_direction', 32);
            $table->decimal('inferred_ratio', 24, 12)->nullable();
            $table->decimal('inferred_ratio_error_pct', 18, 8)->nullable();
            $table->string('candidate_classification', 64);
            $table->string('continuity_verdict', 64);
            $table->boolean('market_calendar_adjacent')->default(false);
            $table->string('detector_version', 64);
            $table->integer('config_snapshot_id')->nullable();
            $table->string('linkage_state', 64)->default('NO_LINKAGE_CANDIDATE');
            $table->integer('possible_corporate_action_revision_id')->nullable();
            $table->string('review_state', 32)->default('DETECTED');
            $table->dateTime('detected_at');
            $table->integer('supersedes_candidate_id')->nullable();
            $table->dateTime('created_at');
            $table->index(['listing_id', 'current_trade_date', 'detected_at'], 'idx_md_psbc_listing_date');
        });

        $schema->create('md_price_scale_break_candidate_reviews', function (Blueprint $table) {
            $table->bigIncrements('candidate_review_id');
            $table->integer('candidate_id');
            $table->integer('revision_number');
            $table->string('review_state', 32);
            $table->integer('evidence_source_observation_id')->nullable();
            $table->integer('corporate_action_revision_id')->nullable();
            $table->string('reviewer', 128);
            $table->text('review_note');
            $table->dateTime('recorded_at');
            $table->integer('supersedes_review_id')->nullable();
            $table->dateTime('created_at');
            $table->unique(['candidate_id', 'revision_number'], 'uq_md_psbc_review_revision');
        });

        $schema->create('md_corporate_action_reconciliations', function (Blueprint $table) {
            $table->bigIncrements('reconciliation_id');
            $table->string('reconciliation_uid', 64)->unique();
            $table->date('scope_start');
            $table->date('scope_end');
            $table->string('authority_name', 128);
            $table->string('authority_class', 32);
            $table->boolean('scope_complete')->default(false);
            $table->string('manifest_sha256', 64);
            $table->integer('manifest_event_count')->default(0);
            $table->integer('platform_event_count')->default(0);
            $table->integer('missing_platform_count')->default(0);
            $table->integer('unexpected_platform_count')->default(0);
            $table->integer('mismatch_count')->default(0);
            $table->string('reconciliation_state', 48);
            $table->text('details_json');
            $table->dateTime('recorded_at');
            $table->dateTime('created_at');
            $table->index(['scope_start', 'scope_end', 'authority_class'], 'idx_md_action_recon_scope');
        });

        $schema->create('md_liquidity_metric_labels', function (Blueprint $table) {
            $table->bigIncrements('liquidity_metric_label_id');
            $table->string('metric_field', 64);
            $table->string('metric_scope', 32);
            $table->string('formula_version', 64);
            $table->string('metric_kind', 16);
            $table->string('price_basis', 32);
            $table->integer('window_sessions')->nullable();
            $table->string('unit_code', 16);
            $table->string('market_scope', 32);
            $table->string('quality_state_field', 64)->nullable();
            $table->boolean('is_compatibility_alias')->default(false);
            $table->string('aliases_metric_field', 64)->nullable();
            $table->text('retirement_condition')->nullable();
            $table->integer('config_snapshot_id')->nullable();
            $table->dateTime('created_at');
            $table->unique(['metric_field', 'formula_version'], 'uq_md_lml_field_version');
            $table->index(['metric_kind', 'metric_scope'], 'idx_md_lml_kind_scope');
            $table->index('aliases_metric_field', 'idx_md_lml_alias_target');
        });

        // The migration seeds the declared labels on clean install, so the mirror does too. A
        // mirrored database that starts unlabelled would make every publication test fail closed
        // for a reason the deployed schema does not have.
        $this->seedDeclaredLiquidityMetricLabels();

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

    protected function seedDeclaredLiquidityMetricLabels(): void
    {
        $now = '2026-01-01 00:00:00';
        foreach (\App\Domain\MarketData\LiquidityMetricLabelRegistry::declared() as $label) {
            DB::table('md_liquidity_metric_labels')->insert([
                'metric_field' => $label['metric_field'],
                'metric_scope' => $label['metric_scope'],
                'formula_version' => $label['formula_version'],
                'metric_kind' => $label['metric_kind'],
                'price_basis' => $label['price_basis'],
                'window_sessions' => $label['window_sessions'],
                'unit_code' => $label['unit_code'],
                'market_scope' => $label['market_scope'],
                'quality_state_field' => $label['quality_state_field'],
                'is_compatibility_alias' => $label['is_compatibility_alias'] ? 1 : 0,
                'aliases_metric_field' => $label['aliases_metric_field'],
                'retirement_condition' => $label['retirement_condition'],
                'created_at' => $now,
            ]);
        }
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
