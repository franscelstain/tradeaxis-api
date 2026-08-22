<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MD-B07-A001 — retain acquisition identity and make provider revision visibility append-only.
 */
class HardenSourceObservationAcquisition extends Migration
{
    public function up()
    {
        $this->addAcquisitionIdentity();
        $this->createNormalizedObservationRows();
        $this->createIdentityBindings();
        $this->createRevisionComparisons();
    }

    public function down()
    {
        Schema::dropIfExists('md_source_observation_revision_comparisons');
        Schema::dropIfExists('md_source_observation_identity_bindings');
        Schema::dropIfExists('md_source_observation_rows');

        if (Schema::hasTable('md_source_observations')) {
            Schema::table('md_source_observations', function (Blueprint $table) {
                foreach (['acquisition_checkpoint_id', 'acquisition_batch_id'] as $column) {
                    if (Schema::hasColumn('md_source_observations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function addAcquisitionIdentity()
    {
        if (! Schema::hasTable('md_source_observations')) {
            return;
        }

        $needsIndex = ! Schema::hasColumn('md_source_observations', 'acquisition_batch_id')
            || ! Schema::hasColumn('md_source_observations', 'acquisition_checkpoint_id');

        Schema::table('md_source_observations', function (Blueprint $table) {
            if (! Schema::hasColumn('md_source_observations', 'acquisition_batch_id')) {
                $table->string('acquisition_batch_id', 128)->nullable();
            }
            if (! Schema::hasColumn('md_source_observations', 'acquisition_checkpoint_id')) {
                $table->string('acquisition_checkpoint_id', 128)->nullable();
            }
        });

        if ($needsIndex) {
            Schema::table('md_source_observations', function (Blueprint $table) {
                $table->index(['acquisition_batch_id', 'acquisition_checkpoint_id'], 'idx_md_obs_acquisition_identity');
            });
        }
    }

    private function createNormalizedObservationRows()
    {
        if (Schema::hasTable('md_source_observation_rows')) {
            return;
        }

        Schema::create('md_source_observation_rows', function (Blueprint $table) {
            $table->bigIncrements('source_observation_row_id');
            $table->unsignedBigInteger('source_observation_id');
            $table->unsignedBigInteger('capture_observation_id');
            $table->string('source_row_ref', 255);
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->string('provider', 64)->nullable();
            $table->string('provider_symbol', 128)->nullable();
            $table->unsignedBigInteger('provider_mapping_id')->nullable();
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
            $table->char('row_fingerprint', 64);
            $table->dateTime('created_at');
            $table->unique(['source_observation_id', 'source_row_ref'], 'uq_md_obs_row_observation_ref');
            $table->index(['listing_id', 'trade_date', 'source_observation_row_id'], 'idx_md_obs_row_listing_date');
            $table->index(['provider', 'provider_symbol', 'trade_date', 'source_observation_row_id'], 'idx_md_obs_row_provider_date');
        });
    }

    private function createRevisionComparisons()
    {
        if (Schema::hasTable('md_source_observation_revision_comparisons')) {
            return;
        }

        Schema::create('md_source_observation_revision_comparisons', function (Blueprint $table) {
            $table->bigIncrements('source_observation_comparison_id');
            $table->string('comparison_uid', 64);
            $table->unsignedBigInteger('prior_source_observation_row_id');
            $table->unsignedBigInteger('current_source_observation_row_id');
            $table->unsignedBigInteger('prior_source_observation_id');
            $table->unsignedBigInteger('current_source_observation_id');
            $table->unsignedBigInteger('listing_id')->nullable();
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
    }

    private function createIdentityBindings()
    {
        if (Schema::hasTable('md_source_observation_identity_bindings')) {
            return;
        }

        Schema::create('md_source_observation_identity_bindings', function (Blueprint $table) {
            $table->bigIncrements('source_observation_identity_binding_id');
            $table->unsignedBigInteger('source_observation_row_id');
            $table->unsignedBigInteger('source_observation_id');
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('provider_mapping_id')->nullable();
            $table->string('mapping_revision', 64);
            $table->date('effective_trade_date');
            $table->dateTime('recorded_at');
            $table->unique(['source_observation_row_id'], 'uq_md_obs_identity_row');
            $table->index(['listing_id', 'effective_trade_date'], 'idx_md_obs_identity_listing_date');
        });
    }
}
