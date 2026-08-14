<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persist the explicitly measured Stage 8 corpus-admission boundary.
 *
 * The intentional dataset start remains 2023-01-02. This decision only states which suffix has
 * enough source/status evidence to be reconstructed as the current conformant corpus. Legacy
 * publications remain immutable and cannot become admitted by receiving nullable columns later.
 */
class AddStageEightConformantCorpusAdmission extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_corpus_admission_decisions')) {
            Schema::create('md_corpus_admission_decisions', function (Blueprint $table) {
                $table->bigIncrements('admission_decision_id');
                $table->char('decision_uid', 64)->unique();
                $table->string('market_code', 16);
                $table->string('market_segment', 32);
                $table->string('canonical_price_product', 32);
                $table->date('intentional_dataset_start');
                $table->date('admitted_from');
                $table->date('measured_through');
                $table->decimal('coverage_threshold', 8, 6);
                $table->string('source_mode', 32);
                $table->unsignedBigInteger('status_snapshot_observation_id');
                $table->unsignedBigInteger('transition_search_observation_id');
                $table->unsignedBigInteger('measurement_campaign_id');
                $table->char('measurement_input_hash', 64);
                $table->char('status_revision_set_hash', 64);
                $table->string('algorithm_version', 64);
                $table->longText('measurement_json');
                $table->string('state', 32);
                $table->string('reason_code', 64);
                $table->unsignedBigInteger('supersedes_decision_id')->nullable();
                $table->dateTime('recorded_at');
                $table->dateTime('created_at');
                $table->index(['state', 'admitted_from'], 'idx_md_corpus_admission_active');
                $table->index('measurement_campaign_id', 'idx_md_corpus_admission_campaign');
            });
        }

        foreach (['eod_runs', 'eod_publications', 'md_publication_lineage_bindings'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'corpus_admission_decision_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->unsignedBigInteger('corpus_admission_decision_id')->nullable();
                    $table->index(
                        'corpus_admission_decision_id',
                        $tableName === 'md_publication_lineage_bindings'
                            ? 'idx_md_lineage_corpus_admission'
                            : ($tableName === 'eod_publications'
                                ? 'idx_eod_publication_corpus_admission'
                                : 'idx_eod_run_corpus_admission')
                    );
                });
            }
        }

        foreach (['eod_eligibility', 'eod_eligibility_history'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'trading_status_revision_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('trading_status_revision_id')->nullable();
                    $table->unsignedBigInteger('trading_status_source_observation_id')->nullable();
                });
            }
        }

        if (! Schema::hasColumn('md_stage8_reconstruction_campaigns', 'admission_decision_id')) {
            Schema::table('md_stage8_reconstruction_campaigns', function (Blueprint $table) {
                $table->unsignedBigInteger('admission_decision_id')->nullable();
                $table->unsignedBigInteger('supersedes_campaign_id')->nullable();
                $table->dateTime('superseded_at')->nullable();
                $table->index('admission_decision_id', 'idx_md_stage8_campaign_admission');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('md_stage8_reconstruction_campaigns')) {
            $this->dropColumns('md_stage8_reconstruction_campaigns', [
                'admission_decision_id', 'supersedes_campaign_id', 'superseded_at',
            ]);
        }

        foreach (['eod_eligibility', 'eod_eligibility_history'] as $tableName) {
            $this->dropColumns($tableName, [
                'trading_status_revision_id', 'trading_status_source_observation_id',
            ]);
        }

        foreach (['eod_runs', 'eod_publications', 'md_publication_lineage_bindings'] as $tableName) {
            $this->dropColumns($tableName, ['corpus_admission_decision_id']);
        }

        Schema::dropIfExists('md_corpus_admission_decisions');
    }

    private function dropColumns($tableName, array $columns)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existing = array_values(array_filter($columns, function ($column) use ($tableName) {
            return Schema::hasColumn($tableName, $column);
        }));

        if ($existing !== []) {
            Schema::table($tableName, function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }
}
