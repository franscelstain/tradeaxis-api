<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Canonical corporate action type dictionary.
 *
 * Owner contract: docs/market_data/registry/Corporate_Action_Type_Registry_LOCKED.md
 *
 * Mirrors the existing market_data_trading_status_event_types pattern: daily imports
 * store event identity only, while continuity semantics live in a seeded dictionary.
 */
class CreateMarketDataCorporateActionTypesTable extends Migration
{
    private static $seedRows = [
        ['STOCK_SPLIT', 'SCALED', 'SCALED', 1, 'Pemecahan saham. Price divided, share count multiplied.'],
        ['REVERSE_STOCK_SPLIT', 'SCALED', 'SCALED', 1, 'Penggabungan saham. Price multiplied, share count divided.'],
        ['BONUS_SHARE', 'SCALED', 'SCALED', 1, 'Saham bonus. Ex-date price adjusts down by the bonus ratio.'],
        ['STOCK_DIVIDEND', 'SCALED', 'SCALED', 1, 'Dividen saham. Same mechanics as bonus share.'],
        ['RIGHTS_ISSUE', 'SCALED', 'SCALED', 1, 'PMHMETD. Ex-rights price adjusts by the theoretical ex-rights formula.'],
        ['PRIVATE_PLACEMENT', 'NONE', 'SCALED', 1, 'PMTHMETD. No ex-price adjustment, but share count increases.'],
        ['WARRANT_EXERCISE', 'NONE', 'SCALED', 1, 'Share count increases on exercise; no ex-price adjustment.'],
        ['CASH_DIVIDEND', 'GAP_UNKNOWN_MAGNITUDE', 'NONE', 0, 'Ex-date price gap approximates the dividend; magnitude unknown without amount data.'],
        ['MERGER', 'SCALED', 'SCALED', 1, 'Share exchange changes both price basis and share count.'],
        ['TICKER_CODE_CHANGE', 'NONE', 'NONE', 0, 'Identity change only.'],
        ['COMPANY_NAME_CHANGE', 'NONE', 'NONE', 0, 'Identity change only.'],
    ];

    public function up()
    {
        if (! Schema::hasTable('market_data_corporate_action_types')) {
            Schema::create('market_data_corporate_action_types', function (Blueprint $table) {
                $table->string('action_type_code', 64);
                $table->string('price_continuity_impact', 32);
                $table->string('volume_continuity_impact', 32);
                $table->tinyInteger('share_count_changes')->default(0);
                $table->string('description', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent();

                $table->primary('action_type_code');
            });
        }

        $now = date('Y-m-d H:i:s');

        foreach (self::$seedRows as $row) {
            $exists = DB::table('market_data_corporate_action_types')
                ->where('action_type_code', $row[0])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('market_data_corporate_action_types')->insert([
                'action_type_code' => $row[0],
                'price_continuity_impact' => $row[1],
                'volume_continuity_impact' => $row[2],
                'share_count_changes' => $row[3],
                'description' => $row[4],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('market_data_corporate_action_types');
    }
}
