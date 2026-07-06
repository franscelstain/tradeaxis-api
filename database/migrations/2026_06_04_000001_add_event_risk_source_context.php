<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEventRiskSourceContext extends Migration
{
    public function up()
    {
        $this->createCorporateActions();
        $this->createTradingStatusEventTypes();
        $this->seedTradingStatusEventTypes();
        $this->createTradingStatusEvents();
        $this->addIndicatorColumns('eod_indicators');
        $this->addIndicatorColumns('eod_indicators_history');
    }

    public function down()
    {
        $this->dropIndicatorColumns('eod_indicators_history');
        $this->dropIndicatorColumns('eod_indicators');
        Schema::dropIfExists('market_data_trading_status_events');
        Schema::dropIfExists('market_data_trading_status_event_types');
        Schema::dropIfExists('market_data_corporate_actions');
    }

    private function createCorporateActions()
    {
        if (Schema::hasTable('market_data_corporate_actions')) {
            return;
        }

        Schema::create('market_data_corporate_actions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('corporate_action_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->date('action_date');
            $table->string('action_type', 64);
            $table->string('source_name', 64)->default('manual_corporate_action_csv');
            $table->string('source_ref', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['ticker_id', 'action_date', 'action_type', 'source_name'], 'uq_md_corp_action_ticker_date_type_source');
            $table->index(['action_date', 'ticker_id'], 'idx_md_corp_action_date_ticker');
            $table->index(['action_type', 'action_date'], 'idx_md_corp_action_type_date');
        });
    }

    private function createTradingStatusEventTypes()
    {
        if (Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        Schema::create('market_data_trading_status_event_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->string('event_type_code', 64)->primary();
            $table->string('risk_family', 64);
            $table->string('transition_type', 32);
            $table->string('expected_bar_policy', 32);
            $table->boolean('carries_forward')->default(false);
            $table->string('clears_risk_family', 64)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['risk_family', 'transition_type'], 'idx_md_status_types_family_transition');
            $table->index(['expected_bar_policy'], 'idx_md_status_types_expected_bar_policy');
        });
    }

    private function createTradingStatusEvents()
    {
        if (Schema::hasTable('market_data_trading_status_events')) {
            return;
        }

        Schema::create('market_data_trading_status_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('trading_status_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('ticker_code', 16);
            $table->date('trade_date');
            $table->string('event_type_code', 64);
            $table->string('source_name', 64)->default('manual_trading_status_csv');
            $table->string('source_ref', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['ticker_id', 'trade_date', 'event_type_code', 'source_name'], 'uq_md_trading_status_ticker_date_type_source');
            $table->index(['trade_date', 'ticker_id'], 'idx_md_trading_status_date_ticker');
            $table->index(['event_type_code', 'trade_date'], 'idx_md_trading_status_event_type_date');
        });
    }

    private function seedTradingStatusEventTypes(): void
    {
        if (! Schema::hasTable('market_data_trading_status_event_types')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->tradingStatusEventTypeRows($now) as $row) {
            DB::table('market_data_trading_status_event_types')->updateOrInsert(
                ['event_type_code' => $row['event_type_code']],
                $row
            );
        }
    }

    private function tradingStatusEventTypeRows(string $now): array
    {
        return [
            [
                'event_type_code' => 'SUSPENDED',
                'risk_family' => 'SUSPENSION',
                'transition_type' => 'START',
                'expected_bar_policy' => 'BAR_NOT_REQUIRED',
                'carries_forward' => 1,
                'clears_risk_family' => null,
                'description' => 'IDX suspends ticker trading; excludes ticker from expected EOD coverage until UNSUSPENDED.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_type_code' => 'SUSPENSION_OBSERVED',
                'risk_family' => 'SUSPENSION',
                'transition_type' => 'OBSERVED',
                'expected_bar_policy' => 'BAR_NOT_REQUIRED',
                'carries_forward' => 1,
                'clears_risk_family' => null,
                'description' => 'Source snapshot shows ticker is still suspended, including IDX long-suspension lists; this is not a suspension start date.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_type_code' => 'UNSUSPENDED',
                'risk_family' => 'SUSPENSION',
                'transition_type' => 'END',
                'expected_bar_policy' => 'BAR_REQUIRED',
                'carries_forward' => 0,
                'clears_risk_family' => 'SUSPENSION',
                'description' => 'IDX reopens ticker trading; clears active SUSPENDED state.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_type_code' => 'SPECIAL_MONITORING_START',
                'risk_family' => 'SPECIAL_MONITORING',
                'transition_type' => 'START',
                'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK',
                'carries_forward' => 1,
                'clears_risk_family' => null,
                'description' => 'Ticker enters IDX special monitoring board; included in coverage with risk context.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_type_code' => 'SPECIAL_MONITORING_END',
                'risk_family' => 'SPECIAL_MONITORING',
                'transition_type' => 'END',
                'expected_bar_policy' => 'BAR_REQUIRED',
                'carries_forward' => 0,
                'clears_risk_family' => 'SPECIAL_MONITORING',
                'description' => 'Ticker exits IDX special monitoring board; clears active special-monitoring state only.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event_type_code' => 'UMA',
                'risk_family' => 'UMA',
                'transition_type' => 'POINT_IN_TIME',
                'expected_bar_policy' => 'BAR_REQUIRED_WITH_RISK',
                'carries_forward' => 0,
                'clears_risk_family' => null,
                'description' => 'Unusual Market Activity notice; exact-date risk context, no carry-forward end pair.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }

    private function addIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'corporate_action_flag')) {
                $table->boolean('corporate_action_flag')->nullable()->after('sector_rs_20_vs_ihsg');
            }
            if (! Schema::hasColumn($tableName, 'corporate_action_types')) {
                $table->string('corporate_action_types', 255)->nullable()->after('corporate_action_flag');
            }
            if (! Schema::hasColumn($tableName, 'trading_status_code')) {
                $table->string('trading_status_code', 64)->nullable()->after('corporate_action_types');
            }
            if (! Schema::hasColumn($tableName, 'is_suspended')) {
                $table->boolean('is_suspended')->nullable()->after('trading_status_code');
            }
            if (! Schema::hasColumn($tableName, 'is_uma')) {
                $table->boolean('is_uma')->nullable()->after('is_suspended');
            }
            if (! Schema::hasColumn($tableName, 'event_risk_flag')) {
                $table->boolean('event_risk_flag')->nullable()->after('is_uma');
            }
            if (! Schema::hasColumn($tableName, 'event_risk_reasons')) {
                $table->string('event_risk_reasons', 255)->nullable()->after('event_risk_flag');
            }

            if (! $this->hasIndex($tableName, 'idx_'.$tableName.'_event_risk_date')) {
                $table->index(['event_risk_flag', 'trade_date'], 'idx_'.$tableName.'_event_risk_date');
            }
        });
    }

    private function dropIndicatorColumns($tableName)
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if ($this->hasIndex($tableName, 'idx_'.$tableName.'_event_risk_date')) {
                $table->dropIndex('idx_'.$tableName.'_event_risk_date');
            }

            foreach ([
                'event_risk_reasons',
                'event_risk_flag',
                'is_uma',
                'is_suspended',
                'trading_status_code',
                'corporate_action_types',
                'corporate_action_flag',
            ] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function hasIndex($tableName, $indexName)
    {
        try {
            $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($tableName);

            return array_key_exists($indexName, $indexes);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
