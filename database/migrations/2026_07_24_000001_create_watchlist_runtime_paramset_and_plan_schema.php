<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWatchlistRuntimeParamsetAndPlanSchema extends Migration
{
    private const TRIGGERS = [
        'trg_wpr_guard_update',
        'trg_wpr_no_delete',
        'trg_wpi_no_update',
        'trg_wpi_no_delete',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('watchlist_fail_codes')) {
            Schema::create('watchlist_fail_codes', function (Blueprint $table): void {
                $table->string('fail_code', 64)->primary();
                $table->enum('scope', ['PLAN', 'RECOMMENDATION', 'CONFIRM', 'SHARED']);
                $table->enum('severity', ['INFO', 'WARN', 'ERROR']);
                $table->text('description_id');
                $table->dateTime('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('watchlist_reason_codes')) {
            Schema::create('watchlist_reason_codes', function (Blueprint $table): void {
                $table->string('policy_code', 16);
                $table->string('reason_code', 64);
                $table->enum('scope', ['PLAN', 'RECOMMENDATION', 'CONFIRM', 'BT']);
                $table->enum('severity', ['INFO', 'WARN', 'BLOCK']);
                $table->string('short_id', 32);
                $table->text('description_id');
                $table->text('description_en');
                $table->dateTime('created_at')->useCurrent();
                $table->primary(['policy_code', 'reason_code']);
            });
        }

        if (! Schema::hasTable('watchlist_param_sets')) {
            Schema::create('watchlist_param_sets', function (Blueprint $table): void {
                $table->bigInteger('param_set_id', true, false);
                $table->string('policy_code', 16);
                $table->string('policy_version', 64);
                $table->string('schema_version', 64);
                $table->longText('hash_contract');
                $table->longText('provenance_json');
                $table->enum('status', ['DRAFT', 'ACTIVE', 'DEPRECATED'])->default('DRAFT');
                $table->longText('params_json');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
                $table->index(['policy_code', 'status', 'updated_at'], 'IDX_param_policy_status');
                $table->index(['policy_code', 'policy_version', 'schema_version'], 'IDX_param_policy_version');
            });
        }

        if (! Schema::hasTable('watchlist_plan_runs')) {
            Schema::create('watchlist_plan_runs', function (Blueprint $table): void {
                $table->bigInteger('plan_run_id', true, false);
                $table->string('policy_code', 16);
                $table->string('policy_version', 64);
                $table->date('asof_eod_date');
                $table->date('plan_trade_date');
                $table->bigInteger('param_set_id');
                $table->enum('run_status', ['OK', 'NO_TRADE', 'FAILED']);
                $table->char('data_batch_hash', 64);
                $table->integer('hash_count')->default(0);
                $table->integer('missing_required_count')->default(0);
                $table->integer('processed_count')->default(0);
                $table->integer('eligible_count')->default(0);
                $table->bigInteger('supersedes_plan_run_id')->nullable();
                $table->enum('is_active', ['Yes', 'No'])->default('Yes');
                $table->string('fail_code', 64)->nullable();
                $table->longText('run_metrics_json');
                $table->dateTime('created_at')->useCurrent();
                $table->index(['policy_code', 'plan_trade_date', 'is_active'], 'IDX_plan_active');
                $table->index(['policy_code', 'asof_eod_date'], 'IDX_plan_asof');
                $table->foreign('param_set_id', 'FK_plan_paramset')
                    ->references('param_set_id')->on('watchlist_param_sets');
                $table->foreign('fail_code', 'FK_plan_fail_code')
                    ->references('fail_code')->on('watchlist_fail_codes');
            });
        }

        if (! Schema::hasTable('watchlist_plan_items')) {
            Schema::create('watchlist_plan_items', function (Blueprint $table): void {
                $table->bigInteger('plan_item_id', true, false);
                $table->bigInteger('plan_run_id');
                $table->string('policy_code', 16);
                $table->date('trade_date');
                $table->bigInteger('ticker_id');
                $table->string('ticker_code', 16)->nullable();
                $table->enum('group_semantic', ['TOP_PICKS', 'SECONDARY', 'WATCH_ONLY', 'AVOID']);
                $table->enum('display_bucket', ['SHOW', 'HIDE']);
                $table->string('selection_reason_code', 64);
                $table->decimal('score_total', 10, 6)->default(0);
                $table->longText('scores_json');
                $table->longText('inputs_json');
                $table->longText('plan_levels_json');
                $table->longText('reason_codes_json');
                $table->dateTime('created_at')->useCurrent();
                $table->index(['plan_run_id', 'ticker_id'], 'IDX_items_run_ticker');
                $table->index(['policy_code', 'trade_date', 'display_bucket'], 'IDX_items_policy_date_bucket');
                $table->foreign('plan_run_id', 'FK_items_planrun')
                    ->references('plan_run_id')->on('watchlist_plan_runs');
            });
        }

        $this->createMysqlTriggers();
    }

    public function down(): void
    {
        $this->dropMysqlTriggers();
        Schema::dropIfExists('watchlist_plan_items');
        Schema::dropIfExists('watchlist_plan_runs');
        Schema::dropIfExists('watchlist_param_sets');
        Schema::dropIfExists('watchlist_reason_codes');
        Schema::dropIfExists('watchlist_fail_codes');
    }

    private function createMysqlTriggers(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->dropMysqlTriggers();

        DB::unprepared(
            "CREATE TRIGGER trg_wpr_guard_update
             BEFORE UPDATE ON watchlist_plan_runs
             FOR EACH ROW
             BEGIN
               IF NOT (
                 OLD.is_active = 'Yes' AND NEW.is_active = 'No' AND
                 OLD.policy_code = NEW.policy_code AND
                 OLD.policy_version = NEW.policy_version AND
                 OLD.asof_eod_date = NEW.asof_eod_date AND
                 OLD.plan_trade_date = NEW.plan_trade_date AND
                 OLD.param_set_id = NEW.param_set_id AND
                 OLD.run_status = NEW.run_status AND
                 OLD.data_batch_hash = NEW.data_batch_hash AND
                 OLD.hash_count = NEW.hash_count AND
                 OLD.missing_required_count = NEW.missing_required_count AND
                 OLD.processed_count = NEW.processed_count AND
                 OLD.eligible_count = NEW.eligible_count AND
                 (OLD.supersedes_plan_run_id <=> NEW.supersedes_plan_run_id) AND
                 (OLD.fail_code <=> NEW.fail_code) AND
                 OLD.run_metrics_json = NEW.run_metrics_json AND
                 OLD.created_at = NEW.created_at
               ) THEN
                 SIGNAL SQLSTATE '45000'
                   SET MESSAGE_TEXT = 'watchlist_plan_runs only allows controlled is_active Yes->No supersede update';
               END IF;
             END"
        );
        DB::unprepared(
            "CREATE TRIGGER trg_wpr_no_delete
             BEFORE DELETE ON watchlist_plan_runs
             FOR EACH ROW
             BEGIN
               SIGNAL SQLSTATE '45000'
                 SET MESSAGE_TEXT = 'watchlist_plan_runs is immutable-history (DELETE blocked)';
             END"
        );
        DB::unprepared(
            "CREATE TRIGGER trg_wpi_no_update
             BEFORE UPDATE ON watchlist_plan_items
             FOR EACH ROW
             BEGIN
               SIGNAL SQLSTATE '45000'
                 SET MESSAGE_TEXT = 'watchlist_plan_items is append-only (UPDATE blocked)';
             END"
        );
        DB::unprepared(
            "CREATE TRIGGER trg_wpi_no_delete
             BEFORE DELETE ON watchlist_plan_items
             FOR EACH ROW
             BEGIN
               SIGNAL SQLSTATE '45000'
                 SET MESSAGE_TEXT = 'watchlist_plan_items is append-only (DELETE blocked)';
             END"
        );
    }

    private function dropMysqlTriggers(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TRIGGERS as $trigger) {
            DB::unprepared('DROP TRIGGER IF EXISTS '.$trigger);
        }
    }
}
