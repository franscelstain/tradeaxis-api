<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive data-model foundation for the strategy corrected by AUDIT_FINAL_STATE orders 4-20.
 *
 * Columns are nullable during rollout so old writers cannot create false provenance. The
 * application must backfill/validate them, then a later enforcement migration may make the
 * publication bindings non-null. A nullable binding never satisfies seal/readability.
 */
class AddMarketDataStrategyV2Foundation extends Migration
{
    public function up()
    {
        $this->createConfigSnapshots();
        $this->createSourceObservations();
        $this->createTemporalIdentity();
        $this->createCalendarAndStatusRevisions();
        $this->createCorporateActionAndFactorRevisions();
        $this->createPublicationBindings();

        $this->addBarBindings('eod_bars');
        $this->addBarBindings('eod_bars_history');
        $this->addIndicatorBindings('eod_indicators');
        $this->addIndicatorBindings('eod_indicators_history');
        $this->addEligibilityFacts('eod_eligibility');
        $this->addEligibilityFacts('eod_eligibility_history');
        $this->addRunBindings();
        $this->addPublicationMetadata();
    }

    public function down()
    {
        $this->dropColumns('eod_publications', [
            'config_snapshot_id', 'factor_set_id', 'observation_manifest_hash',
            'publication_manifest_hash', 'price_product_code', 'read_model_version',
            'readiness_state',
        ]);

        $this->dropColumns('eod_runs', [
            'config_snapshot_id', 'observation_manifest_hash', 'coverage_expected_count',
            'coverage_expectation_unknown_count', 'coverage_delivered_count',
            'coverage_delivered_valid_count', 'operational_start_date', 'freshness_state',
            'latest_expected_trade_date', 'latest_acquired_trade_date',
            'latest_canonicalized_trade_date', 'latest_readable_trade_date',
        ]);

        foreach (['eod_eligibility', 'eod_eligibility_history'] as $table) {
            $this->dropColumns($table, [
                'listing_id', 'universe_membership_state', 'bar_expectation_state',
                'delivery_state', 'canonical_quality_state', 'liquidity_state',
                'temporal_status_state', 'event_risk_state', 'eligibility_reasons_json',
                'config_snapshot_id',
            ]);
        }

        foreach (['eod_indicators', 'eod_indicators_history'] as $table) {
            $this->dropColumns($table, [
                'listing_id', 'formula_version', 'config_snapshot_id', 'factor_set_id',
                'price_product_code', 'adv20_traded_value_idr_actual',
                'adv20_close_volume_proxy_idr', 'atr14', 'atr_state_ref',
                'null_reasons_json',
            ]);
        }

        foreach (['eod_bars', 'eod_bars_history'] as $table) {
            $this->dropColumns($table, [
                'listing_id', 'source_observation_id', 'previous_close',
                'traded_value_idr_actual', 'trade_count_actual', 'board_code',
                'session_code', 'source_timestamp', 'acquired_at',
                'canonicalization_version', 'price_product_code', 'quality_state',
                'config_snapshot_id',
            ]);
        }

        foreach ([
            'md_publication_lineage_bindings',
            'md_adjustment_factors',
            'md_adjustment_factor_sets',
            'md_corporate_action_revisions',
            'md_trading_status_revisions',
            'md_market_calendar_revisions',
            'md_provider_symbol_mappings',
            'md_listing_symbols',
            'md_listings',
            'md_instruments',
            'md_issuers',
            'md_source_observations',
            'md_config_snapshots',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createConfigSnapshots()
    {
        if (Schema::hasTable('md_config_snapshots')) {
            return;
        }

        Schema::create('md_config_snapshots', function (Blueprint $table) {
            $table->bigIncrements('config_snapshot_id');
            $table->string('snapshot_uid', 64)->unique();
            $table->string('snapshot_schema_version', 32);
            $table->string('serialization_version', 32);
            $table->longText('resolved_config_json');
            $table->char('config_hash', 64);
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
    }

    private function createSourceObservations()
    {
        if (Schema::hasTable('md_source_observations')) {
            return;
        }

        Schema::create('md_source_observations', function (Blueprint $table) {
            $table->bigIncrements('source_observation_id');
            $table->string('observation_uid', 64)->unique();
            $table->unsignedBigInteger('run_id')->nullable();
            $table->string('attempt_uid', 64);
            $table->date('requested_trade_date');
            $table->string('source_name', 64);
            $table->string('provider', 64)->nullable();
            $table->string('provider_symbol', 128)->nullable();
            $table->unsignedBigInteger('provider_mapping_id')->nullable();
            $table->string('sanitized_request_identity', 255);
            $table->integer('response_status')->nullable();
            $table->string('content_type', 128)->nullable();
            $table->dateTime('source_timestamp')->nullable();
            $table->dateTime('acquired_at');
            $table->char('schema_fingerprint', 64)->nullable();
            $table->string('adapter_version', 64);
            $table->char('payload_hash', 64)->nullable();
            $table->string('payload_ref', 512)->nullable();
            $table->longText('bounded_payload_body')->nullable();
            $table->string('outcome_state', 32);
            $table->string('reason_code', 64)->nullable();
            $table->unsignedBigInteger('supersedes_observation_id')->nullable();
            $table->dateTime('created_at');
            $table->index(['run_id', 'requested_trade_date'], 'idx_md_obs_run_date');
            $table->index(['provider', 'provider_symbol', 'requested_trade_date'], 'idx_md_obs_provider_symbol_date');
            $table->index(['payload_hash', 'adapter_version'], 'idx_md_obs_payload_adapter');
            $table->index(['outcome_state', 'requested_trade_date'], 'idx_md_obs_outcome_date');
        });
    }

    private function createTemporalIdentity()
    {
        if (! Schema::hasTable('md_issuers')) {
            Schema::create('md_issuers', function (Blueprint $table) {
                $table->bigIncrements('issuer_id');
                $table->string('issuer_uid', 64)->unique();
                $table->string('legal_name', 255);
                $table->dateTime('recorded_at');
                $table->dateTime('created_at');
            });
        }

        if (! Schema::hasTable('md_instruments')) {
            Schema::create('md_instruments', function (Blueprint $table) {
                $table->bigIncrements('instrument_id');
                $table->string('instrument_uid', 64)->unique();
                $table->unsignedBigInteger('issuer_id');
                $table->string('instrument_type', 32);
                $table->string('currency_code', 3)->default('IDR');
                $table->dateTime('recorded_at');
                $table->dateTime('created_at');
                $table->index('issuer_id', 'idx_md_instrument_issuer');
            });
        }

        if (! Schema::hasTable('md_listings')) {
            Schema::create('md_listings', function (Blueprint $table) {
                $table->bigIncrements('listing_id');
                $table->string('listing_uid', 64)->unique();
                $table->unsignedBigInteger('instrument_id');
                $table->string('exchange_code', 16);
                $table->string('board_code', 16)->nullable();
                $table->date('listed_date');
                $table->date('delisted_date')->nullable();
                $table->dateTime('recorded_at');
                $table->dateTime('created_at');
                $table->index(['exchange_code', 'listed_date', 'delisted_date'], 'idx_md_listing_exchange_dates');
                $table->index('instrument_id', 'idx_md_listing_instrument');
            });
        }

        if (! Schema::hasTable('md_listing_symbols')) {
            Schema::create('md_listing_symbols', function (Blueprint $table) {
                $table->bigIncrements('listing_symbol_id');
                $table->unsignedBigInteger('listing_id');
                $table->string('symbol', 64);
                $table->string('symbol_type', 32)->default('EXCHANGE');
                $table->dateTime('effective_from');
                $table->dateTime('effective_to')->nullable();
                $table->dateTime('recorded_at');
                $table->dateTime('retracted_at')->nullable();
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->unique(['listing_id', 'symbol_type', 'effective_from', 'recorded_at'], 'uq_md_listing_symbol_revision');
                $table->index(['symbol', 'effective_from', 'effective_to'], 'idx_md_symbol_effective');
            });
        }

        if (! Schema::hasTable('md_provider_symbol_mappings')) {
            Schema::create('md_provider_symbol_mappings', function (Blueprint $table) {
                $table->bigIncrements('provider_mapping_id');
                $table->unsignedBigInteger('listing_id');
                $table->string('provider', 64);
                $table->string('provider_symbol', 128);
                $table->dateTime('effective_from');
                $table->dateTime('effective_to')->nullable();
                $table->dateTime('recorded_at');
                $table->dateTime('retracted_at')->nullable();
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->string('mapping_revision', 64);
                $table->unique(['listing_id', 'provider', 'effective_from', 'recorded_at'], 'uq_md_provider_mapping_revision');
                $table->index(['provider', 'provider_symbol', 'effective_from', 'effective_to'], 'idx_md_provider_symbol_effective');
            });
        }
    }

    private function createCalendarAndStatusRevisions()
    {
        if (! Schema::hasTable('md_market_calendar_revisions')) {
            Schema::create('md_market_calendar_revisions', function (Blueprint $table) {
                $table->bigIncrements('calendar_revision_id');
                $table->string('market_code', 16)->default('IDX');
                $table->date('cal_date');
                $table->string('revision_uid', 64);
                $table->string('timezone', 64)->default('Asia/Jakarta');
                $table->string('session_state', 32);
                $table->dateTime('session_open_at')->nullable();
                $table->dateTime('session_close_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('recorded_at');
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->unsignedBigInteger('supersedes_revision_id')->nullable();
                $table->unique(['market_code', 'cal_date', 'revision_uid'], 'uq_md_calendar_revision');
                $table->index(['cal_date', 'recorded_at'], 'idx_md_calendar_date_known');
            });
        }

        if (! Schema::hasTable('md_trading_status_revisions')) {
            Schema::create('md_trading_status_revisions', function (Blueprint $table) {
                $table->bigIncrements('status_revision_id');
                $table->unsignedBigInteger('listing_id');
                $table->string('status_code', 64);
                $table->string('bar_expectation_state', 32);
                $table->boolean('full_session_verified')->default(false);
                $table->dateTime('effective_from');
                $table->dateTime('effective_to')->nullable();
                $table->dateTime('recorded_at');
                $table->dateTime('retracted_at')->nullable();
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->unsignedBigInteger('supersedes_revision_id')->nullable();
                $table->index(['listing_id', 'effective_from', 'effective_to'], 'idx_md_status_listing_effective');
                $table->index(['recorded_at', 'bar_expectation_state'], 'idx_md_status_known_expectation');
            });
        }
    }

    private function createCorporateActionAndFactorRevisions()
    {
        if (! Schema::hasTable('md_corporate_action_revisions')) {
            Schema::create('md_corporate_action_revisions', function (Blueprint $table) {
                $table->bigIncrements('corporate_action_revision_id');
                $table->string('event_uid', 64);
                $table->unsignedInteger('revision_number');
                $table->unsignedBigInteger('listing_id');
                $table->string('action_type_code', 64);
                $table->string('lifecycle_state', 32);
                $table->string('verification_state', 32);
                $table->date('ex_date')->nullable();
                $table->date('cum_date')->nullable();
                $table->date('record_date')->nullable();
                $table->date('payment_date')->nullable();
                $table->longText('terms_json')->nullable();
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->dateTime('effective_at')->nullable();
                $table->dateTime('recorded_at');
                $table->unsignedBigInteger('supersedes_revision_id')->nullable();
                $table->unique(['event_uid', 'revision_number'], 'uq_md_action_event_revision');
                $table->index(['listing_id', 'ex_date', 'recorded_at'], 'idx_md_action_listing_ex_known');
                $table->index(['verification_state', 'lifecycle_state'], 'idx_md_action_verification_lifecycle');
            });
        }

        if (! Schema::hasTable('md_adjustment_factor_sets')) {
            Schema::create('md_adjustment_factor_sets', function (Blueprint $table) {
                $table->bigIncrements('factor_set_id');
                $table->string('factor_set_uid', 64)->unique();
                $table->string('price_product_code', 32);
                $table->string('factor_formula_version', 64);
                $table->unsignedBigInteger('config_snapshot_id');
                $table->string('state', 32);
                $table->char('content_hash', 64);
                $table->dateTime('recorded_at');
                $table->dateTime('created_at');
                $table->index(['price_product_code', 'state'], 'idx_md_factor_set_product_state');
            });
        }

        if (! Schema::hasTable('md_adjustment_factors')) {
            Schema::create('md_adjustment_factors', function (Blueprint $table) {
                $table->bigIncrements('adjustment_factor_id');
                $table->unsignedBigInteger('factor_set_id');
                $table->unsignedBigInteger('listing_id');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('price_factor', 24, 12);
                $table->decimal('volume_factor', 24, 12)->nullable();
                $table->unsignedBigInteger('corporate_action_revision_id');
                $table->dateTime('created_at');
                $table->unique(['factor_set_id', 'listing_id', 'effective_from', 'corporate_action_revision_id'], 'uq_md_factor_revision_scope');
                $table->index(['listing_id', 'effective_from', 'effective_to'], 'idx_md_factor_listing_effective');
            });
        }
    }

    private function createPublicationBindings()
    {
        if (Schema::hasTable('md_publication_lineage_bindings')) {
            return;
        }

        Schema::create('md_publication_lineage_bindings', function (Blueprint $table) {
            $table->bigIncrements('publication_lineage_id');
            $table->unsignedBigInteger('publication_id')->unique();
            $table->unsignedBigInteger('config_snapshot_id');
            $table->unsignedBigInteger('factor_set_id')->nullable();
            $table->char('observation_manifest_hash', 64);
            $table->char('identity_revision_set_hash', 64);
            $table->char('calendar_revision_set_hash', 64);
            $table->char('status_revision_set_hash', 64);
            $table->char('event_revision_set_hash', 64);
            $table->string('formula_version', 64);
            $table->string('build_id', 128);
            $table->string('read_model_version', 64);
            $table->dateTime('created_at');
        });
    }

    private function addBarBindings($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'listing_id')) $table->unsignedBigInteger('listing_id')->nullable();
            if (! Schema::hasColumn($tableName, 'source_observation_id')) $table->unsignedBigInteger('source_observation_id')->nullable();
            if (! Schema::hasColumn($tableName, 'previous_close')) $table->decimal('previous_close', 20, 4)->nullable();
            if (! Schema::hasColumn($tableName, 'traded_value_idr_actual')) $table->decimal('traded_value_idr_actual', 24, 2)->nullable();
            if (! Schema::hasColumn($tableName, 'trade_count_actual')) $table->unsignedBigInteger('trade_count_actual')->nullable();
            if (! Schema::hasColumn($tableName, 'board_code')) $table->string('board_code', 16)->nullable();
            if (! Schema::hasColumn($tableName, 'session_code')) $table->string('session_code', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'source_timestamp')) $table->dateTime('source_timestamp')->nullable();
            if (! Schema::hasColumn($tableName, 'acquired_at')) $table->dateTime('acquired_at')->nullable();
            if (! Schema::hasColumn($tableName, 'canonicalization_version')) $table->string('canonicalization_version', 64)->nullable();
            if (! Schema::hasColumn($tableName, 'price_product_code')) $table->string('price_product_code', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'quality_state')) $table->string('quality_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
        });
    }

    private function addIndicatorBindings($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'listing_id')) $table->unsignedBigInteger('listing_id')->nullable();
            if (! Schema::hasColumn($tableName, 'formula_version')) $table->string('formula_version', 64)->nullable();
            if (! Schema::hasColumn($tableName, 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
            if (! Schema::hasColumn($tableName, 'factor_set_id')) $table->unsignedBigInteger('factor_set_id')->nullable();
            if (! Schema::hasColumn($tableName, 'price_product_code')) $table->string('price_product_code', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'adv20_traded_value_idr_actual')) $table->decimal('adv20_traded_value_idr_actual', 24, 2)->nullable();
            if (! Schema::hasColumn($tableName, 'adv20_close_volume_proxy_idr')) $table->decimal('adv20_close_volume_proxy_idr', 24, 2)->nullable();
            if (! Schema::hasColumn($tableName, 'atr14')) $table->decimal('atr14', 20, 10)->nullable();
            if (! Schema::hasColumn($tableName, 'atr_state_ref')) $table->string('atr_state_ref', 128)->nullable();
            if (! Schema::hasColumn($tableName, 'null_reasons_json')) $table->text('null_reasons_json')->nullable();
        });
    }

    private function addEligibilityFacts($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'listing_id')) $table->unsignedBigInteger('listing_id')->nullable();
            if (! Schema::hasColumn($tableName, 'universe_membership_state')) $table->string('universe_membership_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'bar_expectation_state')) $table->string('bar_expectation_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'delivery_state')) $table->string('delivery_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'canonical_quality_state')) $table->string('canonical_quality_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'liquidity_state')) $table->string('liquidity_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'temporal_status_state')) $table->string('temporal_status_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'event_risk_state')) $table->string('event_risk_state', 32)->nullable();
            if (! Schema::hasColumn($tableName, 'eligibility_reasons_json')) $table->text('eligibility_reasons_json')->nullable();
            if (! Schema::hasColumn($tableName, 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
        });
    }

    private function addRunBindings()
    {
        if (! Schema::hasTable('eod_runs')) {
            return;
        }

        Schema::table('eod_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('eod_runs', 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
            if (! Schema::hasColumn('eod_runs', 'observation_manifest_hash')) $table->char('observation_manifest_hash', 64)->nullable();
            if (! Schema::hasColumn('eod_runs', 'coverage_expected_count')) $table->integer('coverage_expected_count')->nullable();
            if (! Schema::hasColumn('eod_runs', 'coverage_expectation_unknown_count')) $table->integer('coverage_expectation_unknown_count')->nullable();
            if (! Schema::hasColumn('eod_runs', 'coverage_delivered_count')) $table->integer('coverage_delivered_count')->nullable();
            if (! Schema::hasColumn('eod_runs', 'coverage_delivered_valid_count')) $table->integer('coverage_delivered_valid_count')->nullable();
            if (! Schema::hasColumn('eod_runs', 'operational_start_date')) $table->date('operational_start_date')->nullable();
            if (! Schema::hasColumn('eod_runs', 'freshness_state')) $table->string('freshness_state', 32)->nullable();
            if (! Schema::hasColumn('eod_runs', 'latest_expected_trade_date')) $table->date('latest_expected_trade_date')->nullable();
            if (! Schema::hasColumn('eod_runs', 'latest_acquired_trade_date')) $table->date('latest_acquired_trade_date')->nullable();
            if (! Schema::hasColumn('eod_runs', 'latest_canonicalized_trade_date')) $table->date('latest_canonicalized_trade_date')->nullable();
            if (! Schema::hasColumn('eod_runs', 'latest_readable_trade_date')) $table->date('latest_readable_trade_date')->nullable();
        });
    }

    private function addPublicationMetadata()
    {
        if (! Schema::hasTable('eod_publications')) {
            return;
        }

        Schema::table('eod_publications', function (Blueprint $table) {
            if (! Schema::hasColumn('eod_publications', 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
            if (! Schema::hasColumn('eod_publications', 'factor_set_id')) $table->unsignedBigInteger('factor_set_id')->nullable();
            if (! Schema::hasColumn('eod_publications', 'observation_manifest_hash')) $table->char('observation_manifest_hash', 64)->nullable();
            if (! Schema::hasColumn('eod_publications', 'publication_manifest_hash')) $table->char('publication_manifest_hash', 64)->nullable();
            if (! Schema::hasColumn('eod_publications', 'price_product_code')) $table->string('price_product_code', 32)->nullable();
            if (! Schema::hasColumn('eod_publications', 'read_model_version')) $table->string('read_model_version', 64)->nullable();
            if (! Schema::hasColumn('eod_publications', 'readiness_state')) $table->string('readiness_state', 32)->nullable();
        });
    }

    private function dropColumns($tableName, array $columns)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = array_values(array_filter($columns, function ($column) use ($tableName) {
            return Schema::hasColumn($tableName, $column);
        }));

        if (! $existing) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
}
