<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HardenMarketDataOrdersOneToFour extends Migration
{
    public function up()
    {
        $this->addSourceObservationContext();
        $this->addTemporalIdentityContext();
        $this->addCalendarAndStatusContext();
    }

    public function down()
    {
        $this->dropExisting('md_trading_status_revisions', [
            'board_code', 'authority_class', 'source_ref', 'verification_state', 'observed_at',
        ]);
        $this->dropExisting('md_market_calendar_revisions', [
            'market_segment', 'is_trading_day', 'is_half_day', 'source_ref', 'source_version',
        ]);
        $this->dropExisting('md_provider_symbol_mappings', ['source_ref', 'change_reason']);
        $this->dropExisting('md_listing_symbols', ['symbol_namespace', 'source_ref', 'change_reason']);
        $this->dropExisting('md_listings', ['legacy_ticker_id', 'market_segment', 'source_ref', 'listing_state']);
        $this->dropExisting('md_instruments', ['source_ref']);
        $this->dropExisting('md_issuers', ['source_ref']);
        $this->dropExisting('md_source_observations', [
            'parent_observation_id', 'source_mode', 'requested_start_date', 'requested_end_date',
            'mapping_revision', 'config_snapshot_id', 'provider_schema_version',
            'payload_byte_length', 'validation_state',
        ]);
    }

    private function addSourceObservationContext()
    {
        if (! Schema::hasTable('md_source_observations')) {
            return;
        }

        Schema::table('md_source_observations', function (Blueprint $table) {
            if (! Schema::hasColumn('md_source_observations', 'parent_observation_id')) $table->unsignedBigInteger('parent_observation_id')->nullable();
            if (! Schema::hasColumn('md_source_observations', 'source_mode')) $table->string('source_mode', 32)->nullable();
            if (! Schema::hasColumn('md_source_observations', 'requested_start_date')) $table->date('requested_start_date')->nullable();
            if (! Schema::hasColumn('md_source_observations', 'requested_end_date')) $table->date('requested_end_date')->nullable();
            if (! Schema::hasColumn('md_source_observations', 'mapping_revision')) $table->string('mapping_revision', 64)->nullable();
            if (! Schema::hasColumn('md_source_observations', 'config_snapshot_id')) $table->unsignedBigInteger('config_snapshot_id')->nullable();
            if (! Schema::hasColumn('md_source_observations', 'provider_schema_version')) $table->string('provider_schema_version', 64)->nullable();
            if (! Schema::hasColumn('md_source_observations', 'payload_byte_length')) $table->unsignedBigInteger('payload_byte_length')->nullable();
            if (! Schema::hasColumn('md_source_observations', 'validation_state')) $table->string('validation_state', 32)->nullable();
        });

        Schema::table('md_source_observations', function (Blueprint $table) {
            $table->index(['parent_observation_id', 'outcome_state'], 'idx_md_obs_parent_outcome');
            $table->index(['source_mode', 'requested_start_date', 'requested_end_date'], 'idx_md_obs_mode_range');
            $table->index(['config_snapshot_id', 'mapping_revision'], 'idx_md_obs_config_mapping');
        });
    }

    private function addTemporalIdentityContext()
    {
        if (Schema::hasTable('md_issuers')) {
            Schema::table('md_issuers', function (Blueprint $table) {
                if (! Schema::hasColumn('md_issuers', 'source_ref')) $table->string('source_ref', 255)->nullable();
            });
        }

        if (Schema::hasTable('md_instruments')) {
            Schema::table('md_instruments', function (Blueprint $table) {
                if (! Schema::hasColumn('md_instruments', 'source_ref')) $table->string('source_ref', 255)->nullable();
            });
        }

        if (Schema::hasTable('md_listings')) {
            Schema::table('md_listings', function (Blueprint $table) {
                if (! Schema::hasColumn('md_listings', 'legacy_ticker_id')) $table->unsignedBigInteger('legacy_ticker_id')->nullable();
                if (! Schema::hasColumn('md_listings', 'market_segment')) $table->string('market_segment', 32)->nullable();
                if (! Schema::hasColumn('md_listings', 'source_ref')) $table->string('source_ref', 255)->nullable();
                if (! Schema::hasColumn('md_listings', 'listing_state')) $table->string('listing_state', 32)->nullable();
            });
            Schema::table('md_listings', function (Blueprint $table) {
                $table->unique('legacy_ticker_id', 'uq_md_listing_legacy_ticker');
                $table->index(['exchange_code', 'market_segment', 'listed_date', 'delisted_date'], 'idx_md_listing_market_interval');
            });
        }

        if (Schema::hasTable('md_listing_symbols')) {
            Schema::table('md_listing_symbols', function (Blueprint $table) {
                if (! Schema::hasColumn('md_listing_symbols', 'symbol_namespace')) $table->string('symbol_namespace', 64)->nullable();
                if (! Schema::hasColumn('md_listing_symbols', 'source_ref')) $table->string('source_ref', 255)->nullable();
                if (! Schema::hasColumn('md_listing_symbols', 'change_reason')) $table->string('change_reason', 64)->nullable();
            });
        }

        if (Schema::hasTable('md_provider_symbol_mappings')) {
            Schema::table('md_provider_symbol_mappings', function (Blueprint $table) {
                if (! Schema::hasColumn('md_provider_symbol_mappings', 'source_ref')) $table->string('source_ref', 255)->nullable();
                if (! Schema::hasColumn('md_provider_symbol_mappings', 'change_reason')) $table->string('change_reason', 64)->nullable();
            });
        }
    }

    private function addCalendarAndStatusContext()
    {
        if (Schema::hasTable('md_market_calendar_revisions')) {
            Schema::table('md_market_calendar_revisions', function (Blueprint $table) {
                if (! Schema::hasColumn('md_market_calendar_revisions', 'market_segment')) $table->string('market_segment', 32)->nullable();
                if (! Schema::hasColumn('md_market_calendar_revisions', 'is_trading_day')) $table->boolean('is_trading_day')->nullable();
                if (! Schema::hasColumn('md_market_calendar_revisions', 'is_half_day')) $table->boolean('is_half_day')->nullable();
                if (! Schema::hasColumn('md_market_calendar_revisions', 'source_ref')) $table->string('source_ref', 255)->nullable();
                if (! Schema::hasColumn('md_market_calendar_revisions', 'source_version')) $table->string('source_version', 64)->nullable();
            });
        }

        if (Schema::hasTable('md_trading_status_revisions')) {
            Schema::table('md_trading_status_revisions', function (Blueprint $table) {
                if (! Schema::hasColumn('md_trading_status_revisions', 'board_code')) $table->string('board_code', 16)->nullable();
                if (! Schema::hasColumn('md_trading_status_revisions', 'authority_class')) $table->string('authority_class', 32)->nullable();
                if (! Schema::hasColumn('md_trading_status_revisions', 'source_ref')) $table->string('source_ref', 255)->nullable();
                if (! Schema::hasColumn('md_trading_status_revisions', 'verification_state')) $table->string('verification_state', 32)->nullable();
                if (! Schema::hasColumn('md_trading_status_revisions', 'observed_at')) $table->dateTime('observed_at')->nullable();
            });
        }
    }

    private function dropExisting($tableName, array $columns)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = array_values(array_filter($columns, function ($column) use ($tableName) {
            return Schema::hasColumn($tableName, $column);
        }));

        if ($existing === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
}
