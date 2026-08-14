<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detected discontinuities in the canonical price series.
 *
 * Owner contract: docs/market_data/registry/Price_Scale_Break_Detection_LOCKED.md
 *
 * Corporate action quarantine only covers actions the source feed recorded, and that feed
 * is incomplete: BNBR, SCCO, PYFA and RMKE each show an unambiguous split in the price
 * series with no corporate action row at all. This table stores breaks derived from the
 * series itself, so an unrecorded split still produces a quarantine.
 */
class CreateMarketDataPriceScaleBreaksTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('market_data_price_scale_breaks')) {
            return;
        }

        Schema::create('market_data_price_scale_breaks', function (Blueprint $table) {
            $table->bigIncrements('price_scale_break_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);

            // The bar whose open sits on the new scale.
            $table->date('trade_date');
            $table->decimal('previous_close', 20, 4);
            $table->decimal('open_price', 20, 4);

            // prev_close / open, always expressed >= 1 with the direction held separately,
            // so a 1:5 split and a 5:1 reverse split are comparable.
            $table->decimal('implied_ratio', 20, 10);
            $table->string('ratio_direction', 16);
            $table->decimal('inferred_ratio', 12, 4)->nullable();
            $table->decimal('inferred_ratio_error_pct', 12, 6)->nullable();

            // SCALE_SHIFT or ISOLATED_ANOMALY
            $table->string('break_type', 32);
            // EXPLAINED or UNEXPLAINED
            $table->string('match_status', 16);
            $table->unsignedBigInteger('matched_corporate_action_id')->nullable();
            $table->string('matched_action_type', 64)->nullable();

            // DETECTED, CONFIRMED or DISMISSED. Only DISMISSED stops the quarantine.
            $table->string('review_status', 16)->default('DETECTED');
            $table->string('review_note', 255)->nullable();
            $table->string('reviewed_by', 64)->nullable();
            $table->dateTime('reviewed_at')->nullable();

            $table->string('detection_contract_version', 64);
            $table->dateTime('detected_at');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['ticker_id', 'trade_date'], 'uq_md_price_scale_break_ticker_date');
            $table->index(['trade_date', 'ticker_id'], 'idx_md_price_scale_break_date_ticker');
            $table->index(['match_status', 'review_status'], 'idx_md_price_scale_break_status');
            $table->index(['break_type'], 'idx_md_price_scale_break_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('market_data_price_scale_breaks');
    }
}
