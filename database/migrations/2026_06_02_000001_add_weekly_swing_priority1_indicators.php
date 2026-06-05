<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeeklySwingPriority1Indicators extends Migration
{
    public function up()
    {
        $this->addEquityIndicatorColumns('eod_indicators');
        $this->addEquityIndicatorColumns('eod_indicators_history');
        $this->addBenchmarkIndicatorColumns();
    }

    public function down()
    {
        $this->dropBenchmarkIndicatorColumns();
        $this->dropEquityIndicatorColumns('eod_indicators_history');
        $this->dropEquityIndicatorColumns('eod_indicators');
    }

    private function addEquityIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'roc5')) {
                $table->decimal('roc5', 20, 10)->nullable()->after('vol_ratio');
            }
            if (! Schema::hasColumn($tableName, 'roc10')) {
                $table->decimal('roc10', 20, 10)->nullable()->after('roc5');
            }
            if (! Schema::hasColumn($tableName, 'll20')) {
                $table->decimal('ll20', 20, 4)->nullable()->after('hh20');
            }
            if (! Schema::hasColumn($tableName, 'close_to_ll20_pct')) {
                $table->decimal('close_to_ll20_pct', 20, 10)->nullable()->after('close_to_hh20_pct');
            }
            if (! Schema::hasColumn($tableName, 'range_20_pct')) {
                $table->decimal('range_20_pct', 20, 10)->nullable()->after('close_to_ll20_pct');
            }
            if (! Schema::hasColumn($tableName, 'range_position_20_pct')) {
                $table->decimal('range_position_20_pct', 20, 10)->nullable()->after('range_20_pct');
            }
        });
    }

    private function dropEquityIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach ([
                'range_position_20_pct',
                'range_20_pct',
                'close_to_ll20_pct',
                'll20',
                'roc10',
                'roc5',
            ] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addBenchmarkIndicatorColumns()
    {
        if (! Schema::hasTable('market_benchmark_indicators')) {
            return;
        }

        Schema::table('market_benchmark_indicators', function (Blueprint $table) {
            if (! Schema::hasColumn('market_benchmark_indicators', 'ma20_slope_pct')) {
                $table->decimal('ma20_slope_pct', 20, 10)->nullable()->after('ma50');
            }
            if (! Schema::hasColumn('market_benchmark_indicators', 'close_to_ma20_pct')) {
                $table->decimal('close_to_ma20_pct', 20, 10)->nullable()->after('ma20_slope_pct');
            }
            if (! Schema::hasColumn('market_benchmark_indicators', 'close_to_ma50_pct')) {
                $table->decimal('close_to_ma50_pct', 20, 10)->nullable()->after('close_to_ma20_pct');
            }
        });
    }

    private function dropBenchmarkIndicatorColumns()
    {
        if (! Schema::hasTable('market_benchmark_indicators')) {
            return;
        }

        Schema::table('market_benchmark_indicators', function (Blueprint $table) {
            foreach ([
                'close_to_ma50_pct',
                'close_to_ma20_pct',
                'ma20_slope_pct',
            ] as $column) {
                if (Schema::hasColumn('market_benchmark_indicators', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
