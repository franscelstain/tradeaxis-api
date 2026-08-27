<?php

use App\Domain\MarketData\LiquidityMetricLabelRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * B13 additive labelling and unit-identity state for actual/proxy liquidity metrics.
 *
 * `Volume_and_Turnover_Normalization_LOCKED.md` requires the proxy to carry formula version, RAW
 * basis, window and proxy label, and then says plainly where those must live: "Those are properties
 * of a stored artifact, not of a sentence in this contract." The platform had them in neither. It
 * had `formula_version` and `price_product_code` at row level, which identify the row, not the
 * individual metric — two liquidity metrics on one row were indistinguishable by anything a query
 * could reach. Column naming was the only signal, and the contract rules that out explicitly:
 * "without parsing a column name".
 *
 * `md_liquidity_metric_labels` is that stored artifact. A metric resolves its label from the same
 * publication context it already carries, through its own metric field and formula version.
 *
 * The volume-unit columns close the other half. Raw volume is defined as source-reported share
 * units "after verified unit normalization", with provider unit identity and normalization evidence
 * mandatory. Neither was recorded anywhere, so every stored volume asserted a unit it had never
 * evidenced.
 */
class AddLiquidityMetricLabellingAndVolumeUnitIdentity extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_liquidity_metric_labels')) {
            Schema::create('md_liquidity_metric_labels', function (Blueprint $table) {
                $table->bigIncrements('liquidity_metric_label_id');

                // Which stored metric this label describes, and where that metric lives.
                $table->string('metric_field', 64);
                $table->string('metric_scope', 32);
                $table->string('formula_version', 64);

                // The four properties the contract requires to be queryable.
                $table->string('metric_kind', 16);
                $table->string('price_basis', 32);
                $table->unsignedInteger('window_sessions')->nullable();

                // The acceptance criterion additionally requires units, market basis and quality state.
                $table->string('unit_code', 16);
                $table->string('market_scope', 32);
                $table->string('quality_state_field', 64)->nullable();

                /*
                 * Alias governance is queryable for the same reason the label is: the contract
                 * requires every compatibility alias to carry an explicit retirement condition, and
                 * a condition recorded only in prose is a condition no consumer can evaluate.
                 */
                $table->boolean('is_compatibility_alias')->default(false);
                $table->string('aliases_metric_field', 64)->nullable();
                $table->text('retirement_condition')->nullable();

                $table->unsignedBigInteger('config_snapshot_id')->nullable();
                $table->dateTime('created_at');

                $table->unique(['metric_field', 'formula_version'], 'uq_md_lml_field_version');
                $table->index(['metric_kind', 'metric_scope'], 'idx_md_lml_kind_scope');
                $table->index('aliases_metric_field', 'idx_md_lml_alias_target');
            });
        }

        /*
         * The metric states which liquidity formula produced it. The indicator row already carries
         * `formula_version`, but that is the operator-configured indicator set version: keying the
         * label to it would make the actual-versus-proxy marker resolvable only when an operator
         * happened to choose a matching string.
         */
        foreach (['eod_indicators', 'eod_indicators_history'] as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'liquidity_formula_version')) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('liquidity_formula_version', 64)->nullable();
            });
        }

        $this->seedDeclaredLabels();

        if (Schema::hasTable('md_source_observations')) {
            Schema::table('md_source_observations', function (Blueprint $table) {
                if (! Schema::hasColumn('md_source_observations', 'source_volume_unit_code')) {
                    $table->string('source_volume_unit_code', 32)->nullable();
                }
                if (! Schema::hasColumn('md_source_observations', 'volume_unit_normalization_factor')) {
                    $table->decimal('volume_unit_normalization_factor', 18, 8)->nullable();
                }
                if (! Schema::hasColumn('md_source_observations', 'volume_unit_normalization_state')) {
                    $table->string('volume_unit_normalization_state', 32)->nullable();
                }
                if (! Schema::hasColumn('md_source_observations', 'volume_unit_evidence_ref')) {
                    $table->string('volume_unit_evidence_ref', 255)->nullable();
                }
            });
        }
    }

    /**
     * A clean install must arrive labelled. Creating the table empty would leave every liquidity
     * metric unlabelled and therefore unpublishable, which is correct behaviour for a missing label
     * but the wrong starting state for a platform whose metrics are all declared.
     */
    private function seedDeclaredLabels()
    {
        $now = date('Y-m-d H:i:s');

        foreach (LiquidityMetricLabelRegistry::declared() as $label) {
            $key = [
                'metric_field' => $label['metric_field'],
                'formula_version' => $label['formula_version'],
            ];

            if (DB::table('md_liquidity_metric_labels')->where($key)->exists()) {
                continue;
            }

            DB::table('md_liquidity_metric_labels')->insert($key + [
                'metric_scope' => $label['metric_scope'],
                'metric_kind' => $label['metric_kind'],
                'price_basis' => $label['price_basis'],
                'window_sessions' => $label['window_sessions'],
                'unit_code' => $label['unit_code'],
                'market_scope' => $label['market_scope'],
                'quality_state_field' => $label['quality_state_field'],
                'is_compatibility_alias' => $label['is_compatibility_alias'] ? 1 : 0,
                'aliases_metric_field' => $label['aliases_metric_field'],
                'retirement_condition' => $label['retirement_condition'],
                'created_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('md_liquidity_metric_labels');

        foreach (['eod_indicators', 'eod_indicators_history'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'liquidity_formula_version')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('liquidity_formula_version');
                });
            }
        }

        if (Schema::hasTable('md_source_observations')) {
            Schema::table('md_source_observations', function (Blueprint $table) {
                foreach ([
                    'source_volume_unit_code',
                    'volume_unit_normalization_factor',
                    'volume_unit_normalization_state',
                    'volume_unit_evidence_ref',
                ] as $column) {
                    if (Schema::hasColumn('md_source_observations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
