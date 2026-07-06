<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefineMarketDataTradingStatusSemantics extends Migration
{
    public function up()
    {
        $this->seedTradingStatusEventTypes();
    }

    public function down()
    {
        // Canonical trading-status semantics now live in market_data_trading_status_event_types.
        // This migration intentionally does not reintroduce legacy denormalized event columns.
    }

    private function seedTradingStatusEventTypes(): void
    {
        if (! Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach ([
            ['event_type_code' => 'SUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'IDX suspends ticker trading; excludes ticker from expected EOD coverage until UNSUSPENDED.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SUSPENSION_OBSERVED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'OBSERVED', 'expected_bar_policy' => 'BAR_NOT_REQUIRED', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Source snapshot shows ticker is still suspended, including IDX long-suspension lists; this is not a suspension start date.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'UNSUSPENDED', 'risk_family' => 'SUSPENSION', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SUSPENSION', 'description' => 'IDX reopens ticker trading; clears active SUSPENDED state.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SPECIAL_MONITORING_START', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'START', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 1, 'clears_risk_family' => null, 'description' => 'Ticker enters IDX special monitoring board; included in coverage with risk context.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'SPECIAL_MONITORING_END', 'risk_family' => 'SPECIAL_MONITORING', 'transition_type' => 'END', 'expected_bar_policy' => 'BAR_REQUIRED', 'carries_forward' => 0, 'clears_risk_family' => 'SPECIAL_MONITORING', 'description' => 'Ticker exits IDX special monitoring board; clears active special-monitoring state only.', 'created_at' => $now, 'updated_at' => $now],
            ['event_type_code' => 'UMA', 'risk_family' => 'UMA', 'transition_type' => 'POINT_IN_TIME', 'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK', 'carries_forward' => 0, 'clears_risk_family' => null, 'description' => 'Unusual Market Activity notice; exact-date risk context, no carry-forward end pair.', 'created_at' => $now, 'updated_at' => $now],
        ] as $row) {
            DB::table('market_data_trading_status_event_types')->updateOrInsert(
                ['event_type_code' => $row['event_type_code']],
                $row
            );
        }
    }
}
