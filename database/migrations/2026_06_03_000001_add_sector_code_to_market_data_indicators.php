<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSectorCodeToMarketDataIndicators extends Migration
{
    public function up()
    {
        $this->createSectorMaster();
        $this->createTickerSectorMemberships();
        $this->addSectorCodeColumn('eod_indicators');
        $this->addSectorCodeColumn('eod_indicators_history');
        $this->seedIdxIcSectors();
    }

    public function down()
    {
        $this->dropSectorCodeColumn('eod_indicators_history');
        $this->dropSectorCodeColumn('eod_indicators');
        Schema::dropIfExists('ticker_sector_memberships');
        Schema::dropIfExists('market_data_sectors');
    }

    private function createSectorMaster()
    {
        if (Schema::hasTable('market_data_sectors')) {
            return;
        }

        Schema::create('market_data_sectors', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->string('sector_code', 8)->primary();
            $table->string('sector_name', 120);
            $table->string('sector_index_code', 32)->nullable();
            $table->string('classification_system', 32)->default('IDX-IC');
            $table->date('effective_from')->default('2021-01-25');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('source_name', 64)->default('idx');
            $table->string('source_ref', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['classification_system', 'is_active', 'sector_code'], 'idx_market_data_sectors_system_active_code');
            $table->index(['sector_index_code'], 'idx_market_data_sectors_index_code');
        });
    }

    private function createTickerSectorMemberships()
    {
        if (Schema::hasTable('ticker_sector_memberships')) {
            return;
        }

        Schema::create('ticker_sector_memberships', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('membership_id');
            $table->unsignedBigInteger('ticker_id');
            $table->string('sector_code', 8);
            $table->string('classification_system', 32)->default('IDX-IC');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('source_name', 64)->nullable();
            $table->string('source_ref', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['ticker_id', 'classification_system', 'effective_from'], 'uq_ticker_sector_membership_effective_from');
            $table->index(['ticker_id', 'classification_system', 'effective_from', 'effective_to'], 'idx_ticker_sector_membership_ticker_date');
            $table->index(['sector_code', 'classification_system', 'effective_from'], 'idx_ticker_sector_membership_sector_date');
        });
    }

    private function addSectorCodeColumn($tableName)
    {
        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'sector_code')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->string('sector_code', 8)->nullable()->after('indicator_set_version');
            $table->index(['sector_code', 'trade_date'], 'idx_'.$tableName.'_sector_date');
        });
    }

    private function dropSectorCodeColumn($tableName)
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'sector_code')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->dropIndex('idx_'.$tableName.'_sector_date');
            $table->dropColumn('sector_code');
        });
    }

    private function seedIdxIcSectors()
    {
        if (! Schema::hasTable('market_data_sectors')) {
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($this->idxIcSectors() as $sector) {
            DB::table('market_data_sectors')->updateOrInsert(
                ['sector_code' => $sector['sector_code']],
                $sector + [
                    'classification_system' => 'IDX-IC',
                    'effective_from' => '2021-01-25',
                    'effective_to' => null,
                    'is_active' => 1,
                    'source_name' => 'idx',
                    'source_ref' => 'https://www.idx.id/en/products/stocks/',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function idxIcSectors()
    {
        return [
            ['sector_code' => 'A', 'sector_name' => 'Energy', 'sector_index_code' => 'IDXENERGY'],
            ['sector_code' => 'B', 'sector_name' => 'Basic Materials', 'sector_index_code' => 'IDXBASIC'],
            ['sector_code' => 'C', 'sector_name' => 'Industrials', 'sector_index_code' => 'IDXINDUST'],
            ['sector_code' => 'D', 'sector_name' => 'Consumer Non-Cyclicals', 'sector_index_code' => 'IDXNONCYC'],
            ['sector_code' => 'E', 'sector_name' => 'Consumer Cyclicals', 'sector_index_code' => 'IDXCYCLIC'],
            ['sector_code' => 'F', 'sector_name' => 'Healthcare', 'sector_index_code' => 'IDXHEALTH'],
            ['sector_code' => 'G', 'sector_name' => 'Financials', 'sector_index_code' => 'IDXFINANCE'],
            ['sector_code' => 'H', 'sector_name' => 'Properties & Real Estate', 'sector_index_code' => 'IDXPROPERT'],
            ['sector_code' => 'I', 'sector_name' => 'Technology', 'sector_index_code' => 'IDXTECHNO'],
            ['sector_code' => 'J', 'sector_name' => 'Infrastructures', 'sector_index_code' => 'IDXINFRA'],
            ['sector_code' => 'K', 'sector_name' => 'Transportation & Logistic', 'sector_index_code' => 'IDXTRANS'],
            ['sector_code' => 'Z', 'sector_name' => 'Listed Investment Product', 'sector_index_code' => null],
        ];
    }
}
