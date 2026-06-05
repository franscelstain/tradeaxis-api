<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSectorRotationIndicators extends Migration
{
    public function up()
    {
        $this->seedSectorBenchmarks();
        $this->addIndicatorColumns('eod_indicators');
        $this->addIndicatorColumns('eod_indicators_history');
    }

    public function down()
    {
        $this->dropIndicatorColumns('eod_indicators_history');
        $this->dropIndicatorColumns('eod_indicators');

        if (Schema::hasTable('market_benchmarks')) {
            DB::table('market_benchmarks')
                ->whereIn('benchmark_code', array_column($this->sectorBenchmarks(), 'benchmark_code'))
                ->delete();
        }
    }

    private function addIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'sector_roc20')) {
                $table->decimal('sector_roc20', 20, 10)->nullable()->after('rs_20_vs_ihsg');
            }
            if (! Schema::hasColumn($tableName, 'rs_20_vs_sector')) {
                $table->decimal('rs_20_vs_sector', 20, 10)->nullable()->after('sector_roc20');
            }
            if (! Schema::hasColumn($tableName, 'sector_rs_20_vs_ihsg')) {
                $table->decimal('sector_rs_20_vs_ihsg', 20, 10)->nullable()->after('rs_20_vs_sector');
            }
        });
    }

    private function dropIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach (['sector_rs_20_vs_ihsg', 'rs_20_vs_sector', 'sector_roc20'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function seedSectorBenchmarks()
    {
        if (! Schema::hasTable('market_benchmarks')) {
            return;
        }

        $now = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();

        foreach ($this->sectorBenchmarks() as $benchmark) {
            DB::table('market_benchmarks')->updateOrInsert(
                ['benchmark_code' => $benchmark['benchmark_code']],
                [
                    'benchmark_name' => $benchmark['benchmark_name'],
                    'provider' => config('market_data.sectors.index_provider', 'manual_sector_index_csv'),
                    'provider_symbol' => $benchmark['provider_symbol'],
                    'instrument_type' => 'SECTOR_INDEX',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function sectorBenchmarks()
    {
        return [
            ['benchmark_code' => 'IDXENERGY', 'benchmark_name' => 'IDX Sector Energy', 'provider_symbol' => 'IDXENERGY'],
            ['benchmark_code' => 'IDXBASIC', 'benchmark_name' => 'IDX Sector Basic Materials', 'provider_symbol' => 'IDXBASIC'],
            ['benchmark_code' => 'IDXINDUST', 'benchmark_name' => 'IDX Sector Industrials', 'provider_symbol' => 'IDXINDUST'],
            ['benchmark_code' => 'IDXNONCYC', 'benchmark_name' => 'IDX Sector Consumer Non-Cyclicals', 'provider_symbol' => 'IDXNONCYC'],
            ['benchmark_code' => 'IDXCYCLIC', 'benchmark_name' => 'IDX Sector Consumer Cyclicals', 'provider_symbol' => 'IDXCYCLIC'],
            ['benchmark_code' => 'IDXHEALTH', 'benchmark_name' => 'IDX Sector Healthcare', 'provider_symbol' => 'IDXHEALTH'],
            ['benchmark_code' => 'IDXFINANCE', 'benchmark_name' => 'IDX Sector Financials', 'provider_symbol' => 'IDXFINANCE'],
            ['benchmark_code' => 'IDXPROPERT', 'benchmark_name' => 'IDX Sector Properties & Real Estate', 'provider_symbol' => 'IDXPROPERT'],
            ['benchmark_code' => 'IDXTECHNO', 'benchmark_name' => 'IDX Sector Technology', 'provider_symbol' => 'IDXTECHNO'],
            ['benchmark_code' => 'IDXINFRA', 'benchmark_name' => 'IDX Sector Infrastructures', 'provider_symbol' => 'IDXINFRA'],
            ['benchmark_code' => 'IDXTRANS', 'benchmark_name' => 'IDX Sector Transportation & Logistic', 'provider_symbol' => 'IDXTRANS'],
        ];
    }
}
