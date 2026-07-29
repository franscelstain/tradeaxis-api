<?php

use App\Application\Watchlist\Services\WatchlistBacktestC171RemediationParamGridCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddC171RealIsRemediationCatalogBounds extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            return;
        }

        Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
            if (! Schema::hasColumn('watchlist_bt_param_grid', 'max_dv20_idr')) {
                $table->unsignedBigInteger('max_dv20_idr')->nullable()->after('min_dv20_idr');
            }
            if (! Schema::hasColumn('watchlist_bt_param_grid', 'max_vol_ratio')) {
                $table->decimal('max_vol_ratio', 20, 6)->nullable()->after('min_vol_ratio');
            }
            if (! Schema::hasColumn('watchlist_bt_param_grid', 'top_max_score_total')) {
                $table->decimal('top_max_score_total', 10, 6)->nullable()->after('top_min_score_q');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('watchlist_bt_param_grid')) {
            return;
        }

        $catalogRows = DB::table('watchlist_bt_param_grid')
            ->where('catalog_code', WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE)
            ->count();
        if ($catalogRows > 0) {
            throw new RuntimeException(
                'C171_REMEDIATION_CATALOG_ROLLBACK_BLOCKED: immutable catalog rows exist; do not drop bound columns.'
            );
        }

        Schema::table('watchlist_bt_param_grid', function (Blueprint $table): void {
            foreach (['top_max_score_total', 'max_vol_ratio', 'max_dv20_idr'] as $column) {
                if (Schema::hasColumn('watchlist_bt_param_grid', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
