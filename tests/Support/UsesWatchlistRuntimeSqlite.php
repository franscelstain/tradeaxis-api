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
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        $schema->create('watchlist_bt_param_grid', function (Blueprint $table): void {
            $table->increments('param_id');
            $table->string('policy_code', 16);
            $table->string('catalog_code', 64);
            $table->string('catalog_version', 16);
            $table->string('catalog_hash', 40);
            $table->string('row_code', 64);
            $table->string('row_hash', 40);
            $table->unsignedBigInteger('min_dv20_idr');
            $table->decimal('max_atr14_pct', 10, 6);
            $table->decimal('min_vol_ratio', 10, 6);
            $table->decimal('w_momentum', 10, 6);
            $table->decimal('w_volume', 10, 6);
            $table->decimal('w_breakout', 10, 6);
            $table->decimal('w_risk', 10, 6);
            $table->decimal('stop_atr_mult', 10, 6);
            $table->decimal('min_rr', 10, 6);
            $table->unsignedInteger('top_picks_target');
            $table->unsignedInteger('secondary_target');
            $table->decimal('top_min_score_q', 10, 6);
            $table->decimal('secondary_min_score_q', 10, 6);
        });

        $schema->create('watchlist_bt_eval', function (Blueprint $table): void {
            $table->bigIncrements('eval_id');
            $table->string('policy_code', 16);
            $table->unsignedInteger('param_id');
            $table->string('eval_model', 96);
            $table->char('paramset_hash', 40);
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedSmallInteger('days_covered');
            $table->unsignedInteger('picks_count');
            $table->decimal('avg_ret_net_top', 10, 6);
            $table->decimal('median_ret_net_top', 10, 6);
            $table->decimal('p25_ret_net_top', 10, 6);
            $table->decimal('month_win_rate_min', 10, 6);
            $table->decimal('month_avg_ret_net_min', 10, 6);
        });

        $schema->create('watchlist_bt_oos_eval_ws', function (Blueprint $table): void {
            $table->bigIncrements('oos_id');
            $table->string('policy_code', 16);
            $table->string('policy_version', 64);
            $table->string('eval_model', 96);
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
            $table->decimal('ret_net', 10, 6);
        });

        $schema->create('watchlist_bt_universe_ws', function (Blueprint $table): void {
            $table->unsignedBigInteger('eval_id');
            $table->date('asof_eod_date');
            $table->unsignedBigInteger('ticker_id');
            $table->boolean('eligible_ok');
        });

        $schema->create('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
            $table->unsignedBigInteger('eval_id');
            $table->string('policy_code', 16);
            $table->unsignedInteger('param_id');
            $table->date('asof_eod_date');
            $table->decimal('top_cutoff_score', 10, 6);
            $table->decimal('secondary_cutoff_score', 10, 6);
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
