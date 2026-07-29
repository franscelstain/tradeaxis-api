<?php

use App\Application\Watchlist\Services\WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddC171LowPriceExecutionQualityCatalogFields extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('watchlist_bt_param_grid')) {
            Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
                if (! Schema::hasColumn('watchlist_bt_param_grid', 'max_signal_tick_risk_expansion_pct')) {
                    $table->decimal('max_signal_tick_risk_expansion_pct', 10, 6)
                        ->nullable()
                        ->after('max_atr14_pct');
                }
            });
        }

        if (Schema::hasTable('watchlist_bt_universe_ws')) {
            Schema::table('watchlist_bt_universe_ws', function (Blueprint $table): void {
                if (! Schema::hasColumn('watchlist_bt_universe_ws', 'signal_close_price')) {
                    $table->decimal('signal_close_price', 20, 6)->nullable()->after('vol_ratio');
                }
                if (! Schema::hasColumn('watchlist_bt_universe_ws', 'signal_tick_risk_expansion_pct')) {
                    $table->decimal('signal_tick_risk_expansion_pct', 10, 6)
                        ->nullable()
                        ->after('signal_close_price');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('watchlist_bt_param_grid')) {
            $catalogRows = DB::table('watchlist_bt_param_grid')
                ->where('catalog_code', WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::CATALOG_CODE)
                ->count();
            if ($catalogRows > 0) {
                throw new RuntimeException(
                    'C171_LOW_PRICE_EXECUTION_QUALITY_CATALOG_ROLLBACK_BLOCKED: immutable catalog rows exist.'
                );
            }
        }

        if (Schema::hasTable('watchlist_bt_universe_ws')) {
            $versionedRows = Schema::hasColumn('watchlist_bt_universe_ws', 'eval_id')
                ? DB::table('watchlist_bt_universe_ws')->count()
                : 0;
            if ($versionedRows > 0) {
                throw new RuntimeException(
                    'C171_LOW_PRICE_EXECUTION_QUALITY_UNIVERSE_ROLLBACK_BLOCKED: official evidence rows exist.'
                );
            }
            Schema::table('watchlist_bt_universe_ws', function (Blueprint $table): void {
                foreach (['signal_tick_risk_expansion_pct', 'signal_close_price'] as $column) {
                    if (Schema::hasColumn('watchlist_bt_universe_ws', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('watchlist_bt_param_grid')) {
            Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
                if (Schema::hasColumn('watchlist_bt_param_grid', 'max_signal_tick_risk_expansion_pct')) {
                    $table->dropColumn('max_signal_tick_risk_expansion_pct');
                }
            });
        }
    }
}
