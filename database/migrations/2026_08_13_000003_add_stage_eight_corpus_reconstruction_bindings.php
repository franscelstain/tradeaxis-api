<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persist the governance decisions consumed by the Stage 8 lifecycle reconstruction.
 *
 * All artifact additions remain nullable for immutable legacy rows. A new publication is
 * admissible only when its own rows and publication-lineage binding are complete; old sealed
 * publications are not relabelled in place.
 */
class AddStageEightCorpusReconstructionBindings extends Migration
{
    public function up()
    {
        $this->createSourceScaleAssessments();
        $this->createFactorDecisions();
        $this->createMarketStructureBindings();
        $this->createReconstructionCampaigns();

        foreach (['eod_bars', 'eod_bars_history'] as $table) {
            if (! Schema::hasColumn($table, 'source_scale_state')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('source_scale_state', 32)->nullable();
                    $blueprint->unsignedBigInteger('source_scale_assessment_id')->nullable();
                    $blueprint->index('source_scale_assessment_id', 'idx_eod_bar_source_scale_assessment');
                });
            }
        }

        if (! Schema::hasColumn('eod_invalid_bars', 'listing_id')) {
            Schema::table('eod_invalid_bars', function (Blueprint $table) {
                $table->unsignedBigInteger('listing_id')->nullable();
                $table->unsignedBigInteger('source_observation_id')->nullable();
                $table->index(['run_id', 'source_observation_id'], 'idx_eod_invalid_run_observation');
            });
        }

        foreach (['eod_eligibility', 'eod_eligibility_history'] as $table) {
            if (! Schema::hasColumn($table, 'market_structure_resolution_state')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->string('market_structure_resolution_state', 48)->nullable();
                    $blueprint->unsignedBigInteger('price_band_revision_id')->nullable();
                    $blueprint->unsignedBigInteger('minimum_price_revision_id')->nullable();
                    $blueprint->unsignedBigInteger('tick_size_revision_id')->nullable();
                });
            }
        }

        if (! Schema::hasColumn('md_publication_lineage_bindings', 'source_scale_assessment_set_hash')) {
            Schema::table('md_publication_lineage_bindings', function (Blueprint $table) {
                $table->char('source_scale_assessment_set_hash', 64)->nullable();
                $table->char('market_structure_revision_set_hash', 64)->nullable();
                $table->char('factor_decision_set_hash', 64)->nullable();
            });
        }

        if (! Schema::hasColumn('eod_publications', 'source_scale_assessment_set_hash')) {
            Schema::table('eod_publications', function (Blueprint $table) {
                $table->char('source_scale_assessment_set_hash', 64)->nullable();
                $table->char('market_structure_revision_set_hash', 64)->nullable();
                $table->char('factor_decision_set_hash', 64)->nullable();
            });
        }
    }

    public function down()
    {
        foreach (['eod_publications', 'md_publication_lineage_bindings'] as $table) {
            $this->dropColumns($table, [
                'source_scale_assessment_set_hash',
                'market_structure_revision_set_hash',
                'factor_decision_set_hash',
            ]);
        }

        foreach (['eod_eligibility', 'eod_eligibility_history'] as $table) {
            $this->dropColumns($table, [
                'market_structure_resolution_state',
                'price_band_revision_id',
                'minimum_price_revision_id',
                'tick_size_revision_id',
            ]);
        }

        $this->dropColumns('eod_invalid_bars', ['listing_id', 'source_observation_id']);
        foreach (['eod_bars', 'eod_bars_history'] as $table) {
            $this->dropColumns($table, ['source_scale_state', 'source_scale_assessment_id']);
        }

        Schema::dropIfExists('md_stage8_reconstruction_targets');
        Schema::dropIfExists('md_stage8_reconstruction_campaigns');
        Schema::dropIfExists('md_publication_market_structure_bindings');
        Schema::dropIfExists('md_adjustment_factor_decisions');
        Schema::dropIfExists('md_source_scale_assessments');
    }

    private function createSourceScaleAssessments()
    {
        if (Schema::hasTable('md_source_scale_assessments')) {
            return;
        }

        Schema::create('md_source_scale_assessments', function (Blueprint $table) {
            $table->bigIncrements('source_scale_assessment_id');
            $table->char('assessment_uid', 64)->unique();
            $table->unsignedInteger('revision_number');
            $table->string('provider', 64);
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('corporate_action_revision_id');
            $table->string('source_scale_state', 32);
            $table->date('scale_effective_from')->nullable();
            $table->string('assessment_version', 64);
            $table->char('evidence_observation_set_hash', 64);
            $table->longText('evidence_json');
            $table->dateTime('recorded_at');
            $table->unsignedBigInteger('supersedes_assessment_id')->nullable();
            $table->dateTime('created_at');
            $table->index(['listing_id', 'corporate_action_revision_id', 'recorded_at'], 'idx_md_scale_listing_event_known');
            $table->index(['provider', 'source_scale_state'], 'idx_md_scale_provider_state');
        });
    }

    private function createFactorDecisions()
    {
        if (Schema::hasTable('md_adjustment_factor_decisions')) {
            return;
        }

        Schema::create('md_adjustment_factor_decisions', function (Blueprint $table) {
            $table->bigIncrements('factor_decision_id');
            $table->unsignedBigInteger('factor_set_id');
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('corporate_action_revision_id');
            $table->unsignedBigInteger('source_scale_assessment_id')->nullable();
            $table->string('decision_state', 48);
            $table->decimal('candidate_price_factor', 24, 12)->nullable();
            $table->decimal('candidate_volume_factor', 24, 12)->nullable();
            $table->string('reason_code', 64);
            $table->dateTime('created_at');
            $table->unique(['factor_set_id', 'corporate_action_revision_id'], 'uq_md_factor_set_event_decision');
            $table->index(['corporate_action_revision_id', 'decision_state'], 'idx_md_factor_decision_event_state');
        });
    }

    private function createMarketStructureBindings()
    {
        if (Schema::hasTable('md_publication_market_structure_bindings')) {
            return;
        }

        Schema::create('md_publication_market_structure_bindings', function (Blueprint $table) {
            $table->bigIncrements('market_structure_binding_id');
            $table->unsignedBigInteger('publication_id');
            $table->unsignedBigInteger('listing_id');
            $table->string('resolution_state', 48);
            $table->string('normalized_board_code', 32)->nullable();
            $table->dateTime('board_identity_recorded_at')->nullable();
            $table->unsignedBigInteger('price_band_revision_id')->nullable();
            $table->unsignedBigInteger('minimum_price_revision_id')->nullable();
            $table->unsignedBigInteger('tick_size_revision_id')->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->dateTime('created_at');
            $table->unique(['publication_id', 'listing_id'], 'uq_md_pub_market_structure_listing');
            $table->index(['publication_id', 'resolution_state'], 'idx_md_pub_market_structure_state');
        });
    }

    private function createReconstructionCampaigns()
    {
        if (! Schema::hasTable('md_stage8_reconstruction_campaigns')) {
            Schema::create('md_stage8_reconstruction_campaigns', function (Blueprint $table) {
                $table->bigIncrements('campaign_id');
                $table->char('campaign_uid', 64)->unique();
                $table->date('scope_start');
                $table->date('scope_end');
                $table->unsignedInteger('target_date_count');
                $table->unsignedBigInteger('baseline_max_publication_id');
                $table->string('state', 32);
                $table->char('baseline_target_set_hash', 64);
                $table->dateTime('started_at');
                $table->dateTime('completed_at')->nullable();
                $table->longText('result_json')->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
            });
        }

        if (! Schema::hasTable('md_stage8_reconstruction_targets')) {
            Schema::create('md_stage8_reconstruction_targets', function (Blueprint $table) {
                $table->bigIncrements('campaign_target_id');
                $table->unsignedBigInteger('campaign_id');
                $table->date('trade_date');
                $table->unsignedBigInteger('baseline_publication_id');
                $table->unsignedBigInteger('baseline_run_id');
                $table->unsignedInteger('baseline_publication_version');
                $table->char('baseline_bars_batch_hash', 64);
                $table->char('baseline_indicators_batch_hash', 64);
                $table->char('baseline_eligibility_batch_hash', 64);
                $table->char('baseline_bars_snapshot_hash', 64);
                $table->char('baseline_indicators_snapshot_hash', 64);
                $table->char('baseline_eligibility_snapshot_hash', 64);
                $table->unsignedBigInteger('correction_id')->nullable();
                $table->unsignedBigInteger('replacement_publication_id')->nullable();
                $table->unsignedBigInteger('replacement_run_id')->nullable();
                $table->string('state', 32)->default('PENDING');
                $table->string('reason_code', 64)->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->unique(['campaign_id', 'trade_date'], 'uq_md_stage8_campaign_date');
                $table->index(['campaign_id', 'state'], 'idx_md_stage8_campaign_state');
            });
        }
    }

    private function dropColumns($table, array $columns)
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $existing = array_values(array_filter($columns, function ($column) use ($table) {
            return Schema::hasColumn($table, $column);
        }));

        if ($existing !== []) {
            Schema::table($table, function (Blueprint $blueprint) use ($existing) {
                $blueprint->dropColumn($existing);
            });
        }
    }
}
