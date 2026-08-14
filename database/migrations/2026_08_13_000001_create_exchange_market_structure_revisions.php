<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeMarketStructureRevisions extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_exchange_market_structure_revisions')) {
            Schema::create('md_exchange_market_structure_revisions', function (Blueprint $table) {
                $table->bigIncrements('market_structure_revision_id');
                $table->char('rule_uid', 64);
                $table->unsignedInteger('revision_number');
                $table->string('rule_type', 32);
                $table->string('exchange_code', 16);
                $table->string('market_segment', 32);
                $table->string('instrument_scope_code', 64);
                $table->longText('coverage_scope_json');
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->decimal('minimum_price_idr', 20, 4)->nullable();
                $table->string('verification_state', 32);
                $table->char('source_uid', 64);
                $table->unsignedBigInteger('source_observation_id');
                $table->string('source_reference', 128);
                $table->char('content_hash', 64);
                $table->dateTime('recorded_at');
                $table->unsignedBigInteger('supersedes_revision_id')->nullable();
                $table->unique(['rule_uid', 'revision_number'], 'uq_md_market_structure_rule_revision');
                $table->index(
                    ['exchange_code', 'market_segment', 'rule_type', 'effective_from', 'effective_to'],
                    'idx_md_market_structure_effective'
                );
                $table->index(
                    ['instrument_scope_code', 'verification_state'],
                    'idx_md_market_structure_scope_verification'
                );
                $table->index('source_observation_id', 'idx_md_market_structure_source');
                $table->index('source_uid', 'idx_md_market_structure_source_uid');
            });
        }

        if (! Schema::hasTable('md_exchange_price_band_tiers')) {
            Schema::create('md_exchange_price_band_tiers', function (Blueprint $table) {
                $table->bigIncrements('price_band_tier_id');
                $table->unsignedBigInteger('market_structure_revision_id');
                $table->unsignedInteger('tier_sequence');
                $table->decimal('reference_price_min_idr', 20, 4)->nullable();
                $table->boolean('reference_price_min_inclusive')->default(false);
                $table->decimal('reference_price_max_idr', 20, 4)->nullable();
                $table->boolean('reference_price_max_inclusive')->default(false);
                $table->decimal('upper_limit_percent', 9, 6);
                $table->decimal('lower_limit_percent', 9, 6);
                $table->unique(
                    ['market_structure_revision_id', 'tier_sequence'],
                    'uq_md_price_band_revision_tier'
                );
                $table->index(
                    ['reference_price_min_idr', 'reference_price_max_idr'],
                    'idx_md_price_band_range'
                );
            });
        }

        if (! Schema::hasTable('md_exchange_tick_size_tiers')) {
            Schema::create('md_exchange_tick_size_tiers', function (Blueprint $table) {
                $table->bigIncrements('tick_size_tier_id');
                $table->unsignedBigInteger('market_structure_revision_id');
                $table->unsignedInteger('tier_sequence');
                $table->decimal('price_min_idr', 20, 4)->nullable();
                $table->boolean('price_min_inclusive')->default(false);
                $table->decimal('price_max_idr', 20, 4)->nullable();
                $table->boolean('price_max_inclusive')->default(false);
                $table->decimal('tick_size_idr', 20, 4);
                $table->decimal('maximum_price_step_idr', 20, 4);
                $table->unique(
                    ['market_structure_revision_id', 'tier_sequence'],
                    'uq_md_tick_size_revision_tier'
                );
                $table->index(
                    ['price_min_idr', 'price_max_idr'],
                    'idx_md_tick_size_range'
                );
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('md_exchange_tick_size_tiers');
        Schema::dropIfExists('md_exchange_price_band_tiers');
        Schema::dropIfExists('md_exchange_market_structure_revisions');
    }
}
