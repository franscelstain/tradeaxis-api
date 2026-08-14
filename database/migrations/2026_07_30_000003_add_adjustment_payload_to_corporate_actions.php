<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Quantitative fields for source-backed corporate-action observations.
 *
 * These nullable fields do not by themselves authorize adjustment. Only a separately
 * verified immutable event revision and factor-set revision may affect an analytical product.
 * Price-series detectors must never populate an adjustment-authorizing verification source.
 */
class AddAdjustmentPayloadToCorporateActions extends Migration
{
    public function up()
    {
        $table = config('market_data.event_risk.corporate_actions_table', 'market_data_corporate_actions');

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (! Schema::hasColumn($table, 'price_adjustment_factor')) {
                $blueprint->decimal('price_adjustment_factor', 20, 10)->nullable();
            }
            if (! Schema::hasColumn($table, 'volume_adjustment_factor')) {
                $blueprint->decimal('volume_adjustment_factor', 20, 10)->nullable();
            }
            if (! Schema::hasColumn($table, 'ex_date')) {
                $blueprint->date('ex_date')->nullable();
            }
            if (! Schema::hasColumn($table, 'cum_date')) {
                $blueprint->date('cum_date')->nullable();
            }
            if (! Schema::hasColumn($table, 'ratio_from')) {
                $blueprint->decimal('ratio_from', 20, 6)->nullable();
            }
            if (! Schema::hasColumn($table, 'ratio_to')) {
                $blueprint->decimal('ratio_to', 20, 6)->nullable();
            }
            if (! Schema::hasColumn($table, 'dividend_per_share')) {
                $blueprint->decimal('dividend_per_share', 20, 4)->nullable();
            }
            if (! Schema::hasColumn($table, 'adjustment_source')) {
                $blueprint->string('adjustment_source', 32)->nullable();
            }
            if (! Schema::hasColumn($table, 'adjustment_note')) {
                $blueprint->string('adjustment_note', 255)->nullable();
            }
        });

        if (! $this->indexExists($table, 'idx_md_corp_action_ex_date')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->index(['ticker_id', 'ex_date'], 'idx_md_corp_action_ex_date');
            });
        }

        // Intentionally seed no price-derived action type. Authoritative or explicitly
        // operator-verified events enter through the revision lifecycle.
    }

    public function down()
    {
        $table = config('market_data.event_risk.corporate_actions_table', 'market_data_corporate_actions');

        if (! Schema::hasTable($table)) {
            return;
        }

        if ($this->indexExists($table, 'idx_md_corp_action_ex_date')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex('idx_md_corp_action_ex_date');
            });
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            foreach ([
                'adjustment_note',
                'adjustment_source',
                'dividend_per_share',
                'ratio_to',
                'ratio_from',
                'cum_date',
                'ex_date',
                'volume_adjustment_factor',
                'price_adjustment_factor',
            ] as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $blueprint->dropColumn($column);
                }
            }
        });
    }

    private function indexExists($table, $indexName)
    {
        try {
            $rows = \DB::select(
                'SELECT COUNT(*) AS aggregate FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $indexName]
            );

            return isset($rows[0]) && (int) $rows[0]->aggregate > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
