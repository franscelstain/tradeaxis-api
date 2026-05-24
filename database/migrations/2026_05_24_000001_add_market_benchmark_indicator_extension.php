<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMarketBenchmarkIndicatorExtension extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('market_benchmarks')) {
            Schema::create('market_benchmarks', function (Blueprint $table) {
                $table->bigIncrements('benchmark_id');
                $table->string('benchmark_code', 32);
                $table->string('benchmark_name', 120);
                $table->string('provider', 64);
                $table->string('provider_symbol', 64);
                $table->string('instrument_type', 32);
                $table->boolean('is_active')->default(true);
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->unique('benchmark_code', 'uq_market_benchmarks_code');
                $table->index(['provider', 'provider_symbol'], 'idx_market_benchmarks_provider_symbol');
                $table->index(['is_active', 'benchmark_code'], 'idx_market_benchmarks_active_code');
            });
        }

        if (! Schema::hasTable('market_benchmark_bars')) {
            Schema::create('market_benchmark_bars', function (Blueprint $table) {
                $table->bigIncrements('benchmark_bar_id');
                $table->string('benchmark_code', 32);
                $table->date('trade_date');
                $table->decimal('open_price', 20, 4);
                $table->decimal('high_price', 20, 4);
                $table->decimal('low_price', 20, 4);
                $table->decimal('close_price', 20, 4);
                $table->decimal('adjusted_close', 20, 4)->nullable();
                $table->bigInteger('volume')->nullable();
                $table->string('provider', 64);
                $table->string('provider_symbol', 64);
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->unique(['benchmark_code', 'trade_date'], 'uq_market_benchmark_bars_code_date');
                $table->index(['benchmark_code', 'trade_date'], 'idx_market_benchmark_bars_code_date');
                $table->index(['provider', 'provider_symbol'], 'idx_market_benchmark_bars_provider_symbol');
            });
        }

        if (! Schema::hasTable('market_benchmark_indicators')) {
            Schema::create('market_benchmark_indicators', function (Blueprint $table) {
                $table->bigIncrements('benchmark_indicator_id');
                $table->string('benchmark_code', 32);
                $table->date('trade_date');
                $table->decimal('roc_20', 20, 10)->nullable();
                $table->decimal('ma20', 20, 4)->nullable();
                $table->decimal('ma50', 20, 4)->nullable();
                $table->integer('is_valid')->default(0);
                $table->string('invalid_reason_code')->nullable();
                $table->string('indicator_set_version', 64);
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->unique(['benchmark_code', 'trade_date', 'indicator_set_version'], 'uq_market_benchmark_indicators_code_date_version');
                $table->index(['benchmark_code', 'trade_date'], 'idx_market_benchmark_indicators_code_date');
            });
        }

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        DB::table('market_benchmarks')->updateOrInsert(
            ['benchmark_code' => 'IHSG'],
            [
                'benchmark_name' => 'Jakarta Composite Index',
                'provider' => 'yahoo_finance',
                'provider_symbol' => '^JKSE',
                'instrument_type' => 'INDEX',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->addIndicatorColumns('eod_indicators');
        $this->addIndicatorColumns('eod_indicators_history');
    }

    public function down()
    {
        $this->dropIndicatorColumns('eod_indicators_history');
        $this->dropIndicatorColumns('eod_indicators');

        Schema::dropIfExists('market_benchmark_indicators');
        Schema::dropIfExists('market_benchmark_bars');
        Schema::dropIfExists('market_benchmarks');
    }

    private function addIndicatorColumns($tableName)
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'ma20')) {
                $table->decimal('ma20', 20, 4)->nullable()->after('hh20');
            }
            if (! Schema::hasColumn($tableName, 'ma50')) {
                $table->decimal('ma50', 20, 4)->nullable()->after('ma20');
            }
            if (! Schema::hasColumn($tableName, 'close_to_hh20_pct')) {
                $table->decimal('close_to_hh20_pct', 20, 10)->nullable()->after('ma50');
            }
            if (! Schema::hasColumn($tableName, 'close_vs_ma20_pct')) {
                $table->decimal('close_vs_ma20_pct', 20, 10)->nullable()->after('close_to_hh20_pct');
            }
            if (! Schema::hasColumn($tableName, 'close_vs_ma50_pct')) {
                $table->decimal('close_vs_ma50_pct', 20, 10)->nullable()->after('close_vs_ma20_pct');
            }
            if (! Schema::hasColumn($tableName, 'ma20_slope_pct')) {
                $table->decimal('ma20_slope_pct', 20, 10)->nullable()->after('close_vs_ma50_pct');
            }
            if (! Schema::hasColumn($tableName, 'rs_20_vs_ihsg')) {
                $table->decimal('rs_20_vs_ihsg', 20, 10)->nullable()->after('ma20_slope_pct');
            }
        });
    }

    private function dropIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach ([
                'rs_20_vs_ihsg',
                'ma20_slope_pct',
                'close_vs_ma50_pct',
                'close_vs_ma20_pct',
                'close_to_hh20_pct',
                'ma50',
                'ma20',
            ] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
