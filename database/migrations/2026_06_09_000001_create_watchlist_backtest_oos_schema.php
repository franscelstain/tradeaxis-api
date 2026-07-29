<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWatchlistBacktestOosSchema extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            Schema::create('watchlist_bt_param_grid', function (Blueprint $table): void {
                $table->increments('param_id');
                $table->string('policy_code', 16);
                $table->unsignedBigInteger('min_dv20_idr');
                $table->unsignedBigInteger('max_dv20_idr')->nullable();
                $table->decimal('max_atr14_pct', 10, 6);
                $table->decimal('max_signal_tick_risk_expansion_pct', 10, 6)->nullable();
                $table->decimal('min_vol_ratio', 10, 6);
                $table->decimal('max_vol_ratio', 20, 6)->nullable();
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
                $table->string('notes', 255)->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->index(['policy_code', 'param_id'], 'IDX_bt_grid_policy');
                $table->unique([
                    'policy_code',
                    'min_dv20_idr',
                    'max_atr14_pct',
                    'min_vol_ratio',
                    'w_momentum',
                    'w_volume',
                    'w_breakout',
                    'w_risk',
                    'stop_atr_mult',
                    'min_rr',
                    'top_picks_target',
                    'secondary_target',
                    'top_min_score_q',
                    'secondary_min_score_q',
                ], 'UQ_bt_grid_policy_payload');
            });
        }

        if (! Schema::hasTable('watchlist_bt_eval')) {
            Schema::create('watchlist_bt_eval', function (Blueprint $table): void {
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
                $table->decimal('win_rate_top', 10, 6);
                $table->decimal('median_ret_net_top', 10, 6);
                $table->decimal('p25_ret_net_top', 10, 6);
                $table->decimal('p75_ret_net_top', 10, 6);
                $table->decimal('min_ret_net_top', 10, 6);
                $table->decimal('max_ret_net_top', 10, 6);
                $table->unsignedTinyInteger('periods_count');
                $table->unsignedTinyInteger('period_fail_count');
                $table->decimal('month_win_rate_min', 10, 6);
                $table->decimal('month_avg_ret_net_min', 10, 6);
                $table->decimal('avg_ret_net_all', 10, 6)->nullable();
                $table->decimal('win_rate_all', 10, 6)->nullable();
                $table->decimal('median_ret_net_all', 10, 6)->nullable();
                $table->decimal('p25_ret_net_all', 10, 6)->nullable();
                $table->decimal('p75_ret_net_all', 10, 6)->nullable();
                $table->decimal('min_ret_net_all', 10, 6)->nullable();
                $table->decimal('max_ret_net_all', 10, 6)->nullable();
                $table->dateTime('created_at')->useCurrent();
                $table->unique([
                    'policy_code',
                    'param_id',
                    'eval_model',
                    'paramset_hash',
                    'from_date',
                    'to_date',
                ], 'UQ_bt_eval_policy_param_window');
                $table->index([
                    'policy_code',
                    'avg_ret_net_top',
                    'median_ret_net_top',
                    'month_win_rate_min',
                    'p25_ret_net_top',
                    'win_rate_top',
                ], 'IDX_bt_eval_rank');
                $table->foreign('param_id', 'FK_bt_eval_param')
                    ->references('param_id')->on('watchlist_bt_param_grid');
            });
        }

        if (! Schema::hasTable('watchlist_bt_oos_eval_ws')) {
            Schema::create('watchlist_bt_oos_eval_ws', function (Blueprint $table): void {
                $table->bigIncrements('oos_id');
                $table->string('policy_code', 16);
                $table->string('policy_version', 32);
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
                $table->decimal('win_rate_top_oos', 10, 6);
                $table->decimal('median_ret_net_top_oos', 10, 6);
                $table->decimal('p25_ret_net_top_oos', 10, 6);
                $table->decimal('month_win_rate_min_oos', 10, 6);
                $table->dateTime('created_at')->useCurrent();
                $table->unique([
                    'policy_code',
                    'policy_version',
                    'eval_model',
                    'param_id_best_is',
                    'is_eval_id',
                    'from_date_is',
                    'to_date_is',
                    'from_date_oos',
                    'to_date_oos',
                ], 'UQ_bt_oos_policy_param_windows');
                $table->index([
                    'policy_code',
                    'avg_ret_net_top_oos',
                    'win_rate_top_oos',
                    'median_ret_net_top_oos',
                ], 'IDX_bt_oos_rank');
                $table->foreign('param_id_best_is', 'FK_bt_oos_param_best_is')
                    ->references('param_id')->on('watchlist_bt_param_grid');
                $table->foreign('is_eval_id', 'FK_bt_oos_is_eval')
                    ->references('eval_id')->on('watchlist_bt_eval');
            });
        }

        if (! Schema::hasTable('watchlist_bt_picks_ws')) {
            Schema::create('watchlist_bt_picks_ws', function (Blueprint $table): void {
                $table->bigIncrements('pick_id');
                $table->string('policy_code', 16);
                $table->unsignedInteger('param_id');
                $table->date('asof_eod_date');
                $table->unsignedBigInteger('ticker_id');
                $table->string('bucket_code', 16);
                $table->decimal('ret_net', 10, 6);
                $table->tinyInteger('pass_guard');
                $table->decimal('score_total', 10, 6);
                $table->dateTime('created_at')->useCurrent();
                $table->index(['policy_code', 'asof_eod_date', 'param_id'], 'IDX_bt_picks_date');
                $table->index(['policy_code', 'param_id', 'ticker_id'], 'IDX_bt_picks_param_ticker');
                $table->foreign('param_id', 'FK_bt_picks_param')
                    ->references('param_id')->on('watchlist_bt_param_grid');
            });
        }

        if (! Schema::hasTable('watchlist_bt_universe_ws')) {
            Schema::create('watchlist_bt_universe_ws', function (Blueprint $table): void {
                $table->date('asof_eod_date');
                $table->unsignedBigInteger('ticker_id');
                $table->boolean('required_ok');
                $table->string('missing_fields', 255)->nullable();
                $table->boolean('guard_ok');
                $table->boolean('eligible_ok');
                $table->unsignedBigInteger('dv20_idr')->nullable();
                $table->decimal('atr14_pct', 10, 6)->nullable();
                $table->decimal('vol_ratio', 20, 6)->nullable();
                $table->decimal('signal_close_price', 20, 6)->nullable();
                $table->decimal('signal_tick_risk_expansion_pct', 10, 6)->nullable();
                $table->string('reason_code', 32)->nullable();
                $table->primary(['asof_eod_date', 'ticker_id'], 'PK_bt_universe_ws');
                $table->index(['asof_eod_date', 'required_ok'], 'idx_bt_univ_ws_req');
                $table->index(['asof_eod_date', 'eligible_ok'], 'idx_bt_univ_ws_elig');
                $table->index(['asof_eod_date', 'reason_code'], 'idx_bt_univ_ws_reason');
            });
        }

        if (! Schema::hasTable('watchlist_bt_cutoffs_ws')) {
            Schema::create('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
                $table->string('policy_code', 16);
                $table->unsignedInteger('param_id');
                $table->date('asof_eod_date');
                $table->decimal('top_cutoff_score', 10, 6);
                $table->decimal('secondary_cutoff_score', 10, 6);
                $table->dateTime('created_at')->useCurrent();
            });
        }

        // MySQL executes CREATE TABLE before the follow-up ALTER statements emitted
        // for keys. A failed long auto-generated key name can therefore leave this
        // table present but unconstrained. Reconcile the physical schema on every run.
        $this->ensureCutoffsConstraints();
    }

    private function ensureCutoffsConstraints(): void
    {
        if (! Schema::hasTable('watchlist_bt_cutoffs_ws')) {
            return;
        }

        $this->ensureInnoDb('watchlist_bt_param_grid');
        $this->ensureInnoDb('watchlist_bt_cutoffs_ws');

        if (! $this->hasPrimaryKey('watchlist_bt_cutoffs_ws')) {
            Schema::table('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
                $table->primary(
                    ['policy_code', 'param_id', 'asof_eod_date'],
                    'PK_bt_cutoffs_ws'
                );
            });
        }

        if (! $this->hasIndex('watchlist_bt_cutoffs_ws', 'IDX_bt_cutoffs_date')) {
            Schema::table('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
                $table->index(
                    ['policy_code', 'asof_eod_date', 'param_id'],
                    'IDX_bt_cutoffs_date'
                );
            });
        }

        if (! $this->hasForeignKey('watchlist_bt_cutoffs_ws', 'FK_bt_cutoffs_param')) {
            Schema::table('watchlist_bt_cutoffs_ws', function (Blueprint $table): void {
                $table->foreign('param_id', 'FK_bt_cutoffs_param')
                    ->references('param_id')
                    ->on('watchlist_bt_param_grid');
            });
        }
    }

    private function ensureInnoDb(string $table): void
    {
        if (DB::connection()->getDriverName() !== 'mysql' || ! Schema::hasTable($table)) {
            return;
        }

        DB::statement(sprintf('ALTER TABLE `%s` ENGINE=InnoDB', str_replace('`', '``', $table)));
    }

    private function hasPrimaryKey(string $table): bool
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $row = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
                . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, 'PRIMARY']
            );

            return (int) ($row->aggregate ?? 0) > 0;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            foreach (DB::select(sprintf('PRAGMA table_info("%s")', str_replace('"', '""', $table))) as $column) {
                if ((int) ($column->pk ?? 0) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $row = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS '
                . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $index]
            );

            return (int) ($row->aggregate ?? 0) > 0;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            foreach (DB::select(sprintf('PRAGMA index_list("%s")', str_replace('"', '""', $table))) as $item) {
                if ((string) ($item->name ?? '') === $index) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $row = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.TABLE_CONSTRAINTS '
                . 'WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? '
                . 'AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
                [$table, $constraint, 'FOREIGN KEY']
            );

            return (int) ($row->aggregate ?? 0) > 0;
        }

        // SQLite does not preserve named foreign-key constraints. The table is only
        // used by tests through an explicit fixture schema, so avoid duplicate ALTERs.
        return DB::connection()->getDriverName() === 'sqlite';
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_bt_cutoffs_ws');
        Schema::dropIfExists('watchlist_bt_universe_ws');
        Schema::dropIfExists('watchlist_bt_picks_ws');
        Schema::dropIfExists('watchlist_bt_oos_eval_ws');
        Schema::dropIfExists('watchlist_bt_eval');
        Schema::dropIfExists('watchlist_bt_param_grid');
    }
}
