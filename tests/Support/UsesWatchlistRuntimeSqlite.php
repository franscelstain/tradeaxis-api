<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait UsesWatchlistRuntimeSqlite
{
    protected string $watchlistRuntimeSqliteConnection = 'sqlite';

    protected function bootWatchlistRuntimeSqlite(): void
    {
        config()->set('database.default', $this->watchlistRuntimeSqliteConnection);
        config()->set("database.connections.{$this->watchlistRuntimeSqliteConnection}", [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge($this->watchlistRuntimeSqliteConnection);
        DB::reconnect($this->watchlistRuntimeSqliteConnection);

        $schema = Schema::connection($this->watchlistRuntimeSqliteConnection);
        if (method_exists($schema, 'dropAllTables')) {
            $schema->dropAllTables();
        }

        $schema->create('watchlist_param_sets', function (Blueprint $table): void {
            $table->bigIncrements('param_set_id');
            $table->string('policy_code', 16);
            $table->string('policy_version', 64);
            $table->string('schema_version', 64);
            $table->text('hash_contract');
            $table->text('provenance_json');
            $table->string('status', 16);
            $table->text('params_json');
            $table->char('params_hash', 40);
            $table->string('eval_model', 96);
            $table->char('eval_model_hash', 40);
            $table->string('implementation_version', 64);
            $table->char('implementation_hash', 40);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->unique(['policy_code', 'policy_version', 'schema_version', 'params_hash'], 'UQ_param_policy_version_schema_hash');
        });

        $schema->create('watchlist_plan_runs', function (Blueprint $table): void {
            $table->bigIncrements('plan_run_id');
            $table->string('policy_code', 16);
            $table->string('policy_version', 64);
            $table->date('asof_eod_date');
            $table->date('plan_trade_date');
            $table->unsignedBigInteger('param_set_id');
            $table->string('run_status', 16);
            $table->char('data_batch_hash', 64);
            $table->integer('hash_count')->default(0);
            $table->integer('missing_required_count')->default(0);
            $table->integer('processed_count')->default(0);
            $table->integer('eligible_count')->default(0);
            $table->unsignedBigInteger('supersedes_plan_run_id')->nullable();
            $table->string('is_active', 3)->default('Yes');
            $table->string('fail_code', 64)->nullable();
            $table->text('run_metrics_json');
            $table->dateTime('created_at');
            $table->index(['policy_code', 'plan_trade_date', 'is_active'], 'IDX_plan_active');
            $table->index(['policy_code', 'asof_eod_date'], 'IDX_plan_asof');
        });

        $schema->create('watchlist_bt_param_grid', function (Blueprint $table): void {
            $table->increments('param_id');
            $table->string('policy_code', 16);
            $table->string('catalog_code', 64);
            $table->string('catalog_version', 16);
            $table->string('catalog_hash', 40);
            $table->string('row_code', 64);
            $table->string('row_hash', 40);
            $table->text('rationale')->nullable();
            $table->unsignedBigInteger('min_dv20_idr');
            $table->unsignedBigInteger('max_dv20_idr')->nullable();
            $table->unsignedBigInteger('dv20_strong_idr')->default(5000000000);
            $table->decimal('min_vol_ratio', 20, 6);
            $table->decimal('max_vol_ratio', 20, 6)->nullable();
            $table->decimal('strong_vol_ratio', 20, 6)->default(2.500000);
            $table->decimal('min_atr14_pct', 10, 6)->default(0.020000);
            $table->decimal('max_atr14_pct', 10, 6);
            $table->decimal('max_signal_tick_risk_expansion_pct', 10, 6)->nullable();
            $table->decimal('atr_ideal_low', 10, 6)->default(0.035000);
            $table->decimal('atr_ideal_high', 10, 6)->default(0.075000);
            $table->decimal('roc_lo', 10, 6)->default(0.020000);
            $table->decimal('roc_hi', 10, 6)->default(0.150000);
            $table->decimal('mom_roc20_soft_min', 10, 6)->default(0.000000);
            $table->decimal('bo_near_below_pct', 10, 6)->default(0.020000);
            $table->decimal('bo_max_ext_pct', 10, 6)->default(0.050000);
            $table->decimal('w_momentum', 10, 6);
            $table->decimal('w_volume', 10, 6);
            $table->decimal('w_breakout', 10, 6);
            $table->decimal('w_risk', 10, 6);
            $table->decimal('stop_atr_mult', 10, 6);
            $table->decimal('min_rr', 10, 6);
            $table->unsignedInteger('top_picks_target');
            $table->unsignedInteger('secondary_target');
            $table->decimal('top_min_score_q', 10, 6);
            $table->decimal('top_max_score_total', 10, 6)->nullable();
            $table->decimal('secondary_min_score_q', 10, 6);
            $table->text('notes')->nullable();
            $table->unique(['policy_code', 'catalog_code', 'row_code'], 'UQ_bt_grid_catalog_row');
        });

        $schema->create('watchlist_bt_eval', function (Blueprint $table): void {
            $table->bigIncrements('eval_id');
            $table->string('policy_code', 16);
            $table->string('catalog_code', 64)->default('WS_BT_GRID_BOOTSTRAP_2026_06');
            $table->string('catalog_version', 16)->default('R1');
            $table->char('catalog_hash', 40)->default('9da8b0983c57bde1ce0a1fbf1c119756f8af431c');
            $table->unsignedInteger('param_id');
            $table->string('eval_model', 96);
            $table->char('eval_model_hash', 40);
            $table->string('implementation_version', 64);
            $table->char('implementation_hash', 40);
            $table->string('evidence_pipeline_version', 64)->nullable();
            $table->char('evidence_pipeline_hash', 40)->nullable();
            $table->char('paramset_hash', 40);
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedSmallInteger('days_covered');
            $table->unsignedInteger('picks_count');
            $table->char('picks_hash', 40);
            $table->unsignedInteger('universe_count');
            $table->char('universe_hash', 40);
            $table->unsignedInteger('cutoff_count');
            $table->char('cutoffs_hash', 40);
            $table->char('evidence_manifest_hash', 40);
            $table->char('market_data_lineage_hash', 40);
            $table->decimal('avg_ret_net_top', 10, 6);
            $table->decimal('win_rate_top', 10, 6)->nullable();
            $table->decimal('median_ret_net_top', 10, 6);
            $table->decimal('p25_ret_net_top', 10, 6);
            $table->decimal('p75_ret_net_top', 10, 6)->nullable();
            $table->decimal('min_ret_net_top', 10, 6)->nullable();
            $table->decimal('max_ret_net_top', 10, 6)->nullable();
            $table->unsignedTinyInteger('periods_count')->nullable();
            $table->unsignedTinyInteger('period_fail_count')->nullable();
            $table->decimal('month_win_rate_min', 10, 6);
            $table->decimal('month_avg_ret_net_min', 10, 6);
            $table->decimal('avg_ret_net_all', 10, 6)->nullable();
            $table->decimal('win_rate_all', 10, 6)->nullable();
            $table->decimal('median_ret_net_all', 10, 6)->nullable();
            $table->decimal('p25_ret_net_all', 10, 6)->nullable();
            $table->decimal('p75_ret_net_all', 10, 6)->nullable();
            $table->decimal('min_ret_net_all', 10, 6)->nullable();
            $table->decimal('max_ret_net_all', 10, 6)->nullable();
            $table->unique([
                'policy_code', 'catalog_code', 'catalog_version', 'param_id', 'eval_model',
                'eval_model_hash', 'implementation_version', 'implementation_hash',
                'evidence_pipeline_version', 'evidence_pipeline_hash',
                'paramset_hash', 'from_date', 'to_date',
            ], 'UQ_bt_eval_catalog_param_window');
        });

        $schema->create('watchlist_bt_oos_eval_ws', function (Blueprint $table): void {
            $table->bigIncrements('oos_id');
            $table->string('policy_code', 16);
            $table->string('policy_version', 64);
            $table->string('eval_model', 96);
            $table->char('paramset_hash', 40)->nullable();
            $table->char('eval_model_hash', 40)->nullable();
            $table->string('implementation_version', 64)->nullable();
            $table->char('implementation_hash', 40)->nullable();
            $table->char('is_evidence_manifest_hash', 40)->nullable();
            $table->unsignedInteger('param_id_best_is');
            $table->unsignedBigInteger('is_eval_id');
            $table->date('from_date_is');
            $table->date('to_date_is');
            $table->date('from_date_oos');
            $table->date('to_date_oos');
            $table->unsignedSmallInteger('days_covered_oos');
            $table->unsignedInteger('picks_count_oos');
            $table->decimal('avg_ret_net_top_oos', 10, 6);
            $table->decimal('median_ret_net_top_oos', 10, 6);
            $table->decimal('p25_ret_net_top_oos', 10, 6);
            $table->decimal('month_win_rate_min_oos', 10, 6);
        });

        $schema->create('watchlist_bt_picks_ws', function (Blueprint $table): void {
            $table->bigIncrements('pick_id');
            $table->unsignedBigInteger('eval_id');
            $table->string('policy_code', 16);
            $table->unsignedInteger('param_id');
            $table->date('asof_eod_date');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->string('bucket_code', 16)->nullable();
            $table->decimal('ret_net', 10, 6);
            $table->tinyInteger('pass_guard')->default(1);
            $table->decimal('score_total', 10, 6)->default(0);
            $table->unsignedBigInteger('source_publication_id');
            $table->unsignedInteger('source_publication_version');
            $table->unsignedBigInteger('source_run_id');
            $table->char('row_hash', 40);
            $table->unique(['eval_id', 'asof_eod_date', 'ticker_id'], 'UQ_bt_picks_eval_date_ticker');
        });

        $schema->create('watchlist_bt_universe_ws', function (Blueprint $table): void {
            $table->unsignedBigInteger('eval_id');
            $table->string('policy_code', 16);
            $table->unsignedInteger('param_id');
            $table->date('asof_eod_date');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->boolean('required_ok')->default(true);
            $table->string('missing_fields', 255)->nullable();
            $table->boolean('guard_ok')->default(true);
            $table->boolean('eligible_ok');
            $table->unsignedBigInteger('dv20_idr')->nullable();
            $table->decimal('atr14_pct', 10, 6)->nullable();
            $table->decimal('vol_ratio', 20, 6)->nullable();
            $table->decimal('signal_close_price', 20, 6)->nullable();
            $table->decimal('signal_tick_risk_expansion_pct', 10, 6)->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->unsignedBigInteger('source_publication_id');
            $table->unsignedInteger('source_publication_version');
            $table->unsignedBigInteger('source_run_id');
            $table->char('row_hash', 40);
            $table->primary(['eval_id', 'asof_eod_date', 'ticker_id']);
        });

        $schema->create('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
            $table->unsignedBigInteger('eval_id');
            $table->string('policy_code', 16);
            $table->unsignedInteger('param_id');
            $table->date('asof_eod_date');
            $table->decimal('top_cutoff_score', 10, 6);
            $table->decimal('secondary_cutoff_score', 10, 6);
            $table->unsignedBigInteger('source_publication_id');
            $table->unsignedInteger('source_publication_version');
            $table->unsignedBigInteger('source_run_id');
            $table->char('row_hash', 40);
            $table->primary(['eval_id', 'policy_code', 'param_id', 'asof_eod_date']);
        });

        $schema->create('market_calendar', function (Blueprint $table): void {
            $table->date('cal_date')->primary();
            $table->boolean('is_trading_day');
            $table->string('source', 64)->nullable();
        });

        $calendarRows = [];
        $date = new \DateTimeImmutable('2025-01-01');
        for ($day = 0; $day < 100; $day++) {
            $calendarRows[] = [
                'cal_date' => $date->modify('+'.$day.' days')->format('Y-m-d'),
                'is_trading_day' => 1,
                'source' => 'c169-test',
            ];
        }
        DB::table('market_calendar')->insert($calendarRows);
    }

    protected function seedR1BaselineParamGrid(): void
    {
        DB::table('watchlist_bt_param_grid')->insert([
            'param_id' => 1,
            'policy_code' => 'WS',
            'catalog_code' => 'WS_BT_GRID_BOOTSTRAP_2026_06',
            'catalog_version' => 'R1',
            'catalog_hash' => '9da8b0983c57bde1ce0a1fbf1c119756f8af431c',
            'row_code' => '01_BASELINE',
            'row_hash' => sha1('WS_BT_GRID_BOOTSTRAP_2026_06|01_BASELINE'),
            'min_dv20_idr' => 1000000000,
            'max_atr14_pct' => 0.12,
            'min_vol_ratio' => 1.2,
            'w_momentum' => 0.3,
            'w_volume' => 0.2,
            'w_breakout' => 0.3,
            'w_risk' => 0.2,
            'stop_atr_mult' => 1.5,
            'min_rr' => 1.5,
            'top_picks_target' => 5,
            'secondary_target' => 10,
            'top_min_score_q' => 0.8,
            'secondary_min_score_q' => 0.65,
        ]);
    }

    protected function tearDownWatchlistRuntimeSqlite(): void
    {
        DB::disconnect($this->watchlistRuntimeSqliteConnection);
    }
}
