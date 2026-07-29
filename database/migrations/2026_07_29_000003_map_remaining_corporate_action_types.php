<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Complete the corporate action taxonomy against the action types actually present in
 * market_data_corporate_actions, and correct three rows seeded on a wrong premise.
 *
 * Owner contract: docs/market_data/registry/Corporate_Action_Type_Registry_LOCKED.md
 *
 * The initial seed conflated "outstanding share count increases" with "share unit is
 * redefined". They are different events:
 *
 *   - A split, reverse split, bonus share, or stock dividend redefines the unit. Every
 *     historical bar is expressed in a different unit than every later bar, so both price
 *     and volume history are arithmetically incomparable across the action date.
 *   - A private placement, warrant exercise, ESOP exercise, or bond conversion issues new
 *     shares at the existing unit. That is dilution. Historical volume stays directly
 *     comparable, and no ex-price adjustment is applied to the series.
 *
 * Only the first kind justifies quarantining indicators. Dilution is a fundamental matter
 * for the consumer to weigh, and it still reaches the consumer through the exact-date
 * corporate_action_flag context; it is not an arithmetic defect in the series.
 *
 * A rights issue (HMETD) sits between the two: the ex-rights price adjustment does rescale
 * the price series, but no holder's share count is multiplied automatically, so volume is
 * not rescaled.
 */
class MapRemainingCorporateActionTypes extends Migration
{
    /**
     * [action_type_code, price_continuity_impact, volume_continuity_impact, share_count_changes, description]
     */
    private static $types = [
        // Unit redefinition: both price and volume history change scale.
        ['STOCK_SPLIT', 'SCALED', 'SCALED', 1, 'Pemecahan saham. Share unit redefined; price divided and share count multiplied.'],
        ['REVERSE_STOCK_SPLIT', 'SCALED', 'SCALED', 1, 'Penggabungan saham. Share unit redefined; price multiplied and share count divided.'],
        ['BONUS_SHARE', 'SCALED', 'SCALED', 1, 'Saham bonus. Every holder receives proportional extra shares, so the unit is redefined.'],
        ['STOCK_DIVIDEND', 'SCALED', 'SCALED', 1, 'Dividen saham. Same mechanics as bonus share.'],
        ['MERGER', 'SCALED', 'SCALED', 1, 'Share exchange ratio applies to every holder, redefining both price basis and unit.'],

        // Price series rescaled, share unit unchanged.
        ['RIGHTS_ISSUE', 'SCALED', 'NONE', 1, 'PMHMETD. Ex-rights price adjustment rescales price history, but no holding is multiplied automatically, so volume stays comparable.'],

        // Additive price gap of unknown size, share unit unchanged.
        ['CASH_DIVIDEND', 'GAP_UNKNOWN_MAGNITUDE', 'NONE', 0, 'Ex-date price gap approximates the dividend; magnitude unknown without amount data.'],

        // Dilution only: new shares issued at the existing unit, no ex-price adjustment.
        ['PRIVATE_PLACEMENT', 'NONE', 'NONE', 1, 'PMTHMETD. Dilution without redenomination; historical price and volume stay comparable.'],
        ['NON_PREEMPTIVE_RIGHTS_ISSUE', 'NONE', 'NONE', 1, 'PMTHMETD variant recorded by the source feed. Dilution without redenomination.'],
        ['WARRANT', 'NONE', 'NONE', 1, 'Waran is a separate listed security granting the right to buy shares at an exercise price. Issuing or exercising it never rescales the underlying series.'],
        ['WARRANT_EXERCISE', 'NONE', 'NONE', 1, 'Exercise issues new shares at the existing unit. Dilution, not redenomination.'],
        ['MANDATORY_CONVERTIBLE_BOND', 'NONE', 'NONE', 1, 'Conversion issues new shares at the existing unit. Dilution, not redenomination.'],
        ['ESOP_MSOP', 'NONE', 'NONE', 1, 'Employee option exercise issues new shares at the existing unit. Dilution, not redenomination.'],

        // Lifecycle and identity events: no continuity to break.
        ['IPO', 'NONE', 'NONE', 0, 'Listing date. There is no prior series that could be discontinuous.'],
        ['DELISTING', 'NONE', 'NONE', 0, 'Ticker stops trading. No forward continuity remains to protect.'],
        ['PARTIAL_DELISTING', 'NONE', 'NONE', 0, 'Listing status change; does not rescale the price or volume series.'],
        ['PARTIAL_RELISTING', 'NONE', 'NONE', 0, 'Listing status change; does not rescale the price or volume series.'],
        ['CAPITAL_DEFICIENCY', 'NONE', 'NONE', 0, 'Financial condition status flag, not an action that rescales the series.'],
        ['TICKER_CODE_CHANGE', 'NONE', 'NONE', 0, 'Identity change only.'],
        ['COMPANY_NAME_CHANGE', 'NONE', 'NONE', 0, 'Identity change only.'],
    ];

    public function up()
    {
        if (! Schema::hasTable('market_data_corporate_action_types')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach (self::$types as $type) {
            $attributes = [
                'price_continuity_impact' => $type[1],
                'volume_continuity_impact' => $type[2],
                'share_count_changes' => $type[3],
                'description' => $type[4],
                'updated_at' => $now,
            ];

            $existing = DB::table('market_data_corporate_action_types')
                ->where('action_type_code', $type[0])
                ->exists();

            if ($existing) {
                DB::table('market_data_corporate_action_types')
                    ->where('action_type_code', $type[0])
                    ->update($attributes);

                continue;
            }

            DB::table('market_data_corporate_action_types')->insert(
                $attributes + ['action_type_code' => $type[0], 'created_at' => $now]
            );
        }
    }

    public function down()
    {
        if (! Schema::hasTable('market_data_corporate_action_types')) {
            return;
        }

        // Only the types introduced by this migration are removed. The rows seeded by the
        // initial taxonomy migration stay, so rolling back returns to that state rather
        // than emptying the dictionary and silently disabling every quarantine.
        DB::table('market_data_corporate_action_types')
            ->whereIn('action_type_code', [
                'WARRANT',
                'NON_PREEMPTIVE_RIGHTS_ISSUE',
                'MANDATORY_CONVERTIBLE_BOND',
                'ESOP_MSOP',
                'IPO',
                'DELISTING',
                'PARTIAL_DELISTING',
                'PARTIAL_RELISTING',
                'CAPITAL_DEFICIENCY',
            ])
            ->delete();
    }
}
