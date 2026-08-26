<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B11 additive evidence/control state for verified corporate-action lifecycle.
 *
 * Price discontinuities remain append-only candidates. They never become corporate actions or
 * active factors by themselves. External reconciliation records qualification of the platform's
 * recorded event corpus against an exchange/CSD manifest without mutating event history.
 */
class AddVerifiedCorporateActionCandidateAndReconciliationState extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_price_scale_break_candidates')) {
            Schema::create('md_price_scale_break_candidates', function (Blueprint $table) {
                $table->bigIncrements('candidate_id');
                $table->char('candidate_uid', 64)->unique();
                $table->unsignedBigInteger('listing_id');
                $table->date('prior_trade_date');
                $table->date('current_trade_date');
                $table->unsignedBigInteger('prior_publication_id')->nullable();
                $table->unsignedBigInteger('current_publication_id')->nullable();
                $table->unsignedBigInteger('prior_source_observation_id')->nullable();
                $table->unsignedBigInteger('current_source_observation_id')->nullable();
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
                $table->unsignedBigInteger('config_snapshot_id')->nullable();
                $table->string('linkage_state', 64)->default('NO_LINKAGE_CANDIDATE');
                $table->unsignedBigInteger('possible_corporate_action_revision_id')->nullable();
                $table->string('review_state', 32)->default('DETECTED');
                $table->dateTime('detected_at');
                $table->unsignedBigInteger('supersedes_candidate_id')->nullable();
                $table->dateTime('created_at');
                $table->index(['listing_id', 'current_trade_date', 'detected_at'], 'idx_md_psbc_listing_date');
                $table->index(['review_state', 'continuity_verdict'], 'idx_md_psbc_review_continuity');
                $table->index('possible_corporate_action_revision_id', 'idx_md_psbc_action_revision');
            });
        }

        if (! Schema::hasTable('md_price_scale_break_candidate_reviews')) {
            Schema::create('md_price_scale_break_candidate_reviews', function (Blueprint $table) {
                $table->bigIncrements('candidate_review_id');
                $table->unsignedBigInteger('candidate_id');
                $table->unsignedInteger('revision_number');
                $table->string('review_state', 32);
                $table->unsignedBigInteger('evidence_source_observation_id')->nullable();
                $table->unsignedBigInteger('corporate_action_revision_id')->nullable();
                $table->string('reviewer', 128);
                $table->text('review_note');
                $table->dateTime('recorded_at');
                $table->unsignedBigInteger('supersedes_review_id')->nullable();
                $table->dateTime('created_at');
                $table->unique(['candidate_id', 'revision_number'], 'uq_md_psbc_review_revision');
                $table->index(['candidate_id', 'recorded_at'], 'idx_md_psbc_review_known');
                $table->index('corporate_action_revision_id', 'idx_md_psbc_review_action_revision');
            });
        }

        if (! Schema::hasTable('md_corporate_action_reconciliations')) {
            Schema::create('md_corporate_action_reconciliations', function (Blueprint $table) {
                $table->bigIncrements('reconciliation_id');
                $table->char('reconciliation_uid', 64)->unique();
                $table->date('scope_start');
                $table->date('scope_end');
                $table->string('authority_name', 128);
                $table->string('authority_class', 32);
                $table->boolean('scope_complete')->default(false);
                $table->char('manifest_sha256', 64);
                $table->unsignedInteger('manifest_event_count')->default(0);
                $table->unsignedInteger('platform_event_count')->default(0);
                $table->unsignedInteger('missing_platform_count')->default(0);
                $table->unsignedInteger('unexpected_platform_count')->default(0);
                $table->unsignedInteger('mismatch_count')->default(0);
                $table->string('reconciliation_state', 48);
                $table->longText('details_json');
                $table->dateTime('recorded_at');
                $table->dateTime('created_at');
                $table->index(['scope_start', 'scope_end', 'authority_class'], 'idx_md_action_recon_scope');
                $table->index(['reconciliation_state', 'recorded_at'], 'idx_md_action_recon_state');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('md_corporate_action_reconciliations');
        Schema::dropIfExists('md_price_scale_break_candidate_reviews');
        Schema::dropIfExists('md_price_scale_break_candidates');
    }
}
