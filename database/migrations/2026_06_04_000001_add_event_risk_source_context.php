<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventRiskSourceContext extends Migration
{
    public function up()
    {
        $this->createCorporateActions();
        $this->createTradingStatusEvents();
        $this->addIndicatorColumns('eod_indicators');
        $this->addIndicatorColumns('eod_indicators_history');
    }

    public function down()
    {
        $this->dropIndicatorColumns('eod_indicators_history');
        $this->dropIndicatorColumns('eod_indicators');
        Schema::dropIfExists('market_data_trading_status_events');
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
            $table->string('status_code', 64);
            $table->boolean('is_suspended')->nullable();
            $table->boolean('is_uma')->nullable();
            $table->string('source_name', 64)->default('manual_trading_status_csv');
            $table->string('source_ref', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['ticker_id', 'trade_date', 'status_code', 'source_name'], 'uq_md_trading_status_ticker_date_code_source');
            $table->index(['trade_date', 'ticker_id'], 'idx_md_trading_status_date_ticker');
            $table->index(['status_code', 'trade_date'], 'idx_md_trading_status_code_date');
        });
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
