<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MD-B06-A001 — authority-bearing calendar/status metadata and safe legacy downgrade.
 */
class HardenCalendarAndTradingStatusExpectation extends Migration
{
    public function up()
    {
        $this->addStatusRevisionAuthorityContext();
        $this->createStatusSourceRegistry();
        $this->addManualTransportContext();
        $this->downgradeUnsupportedLegacyCalendarClaims();
    }

    public function down()
    {
        Schema::dropIfExists('md_trading_status_source_registry');
        $this->dropExisting('market_data_trading_status_events', [
            'origin_authority_class', 'source_payload_hash', 'operator_name',
            'governed_reason_code', 'authoritative_source_ref', 'transport_state',
        ]);
        $this->dropExisting('md_trading_status_revisions', [
            'status_event_uid', 'instrument_id', 'status_type_code', 'source_name',
            'source_payload_hash', 'announced_at', 'operator_name',
            'governed_reason_code', 'authoritative_source_ref',
        ]);
    }

    private function addStatusRevisionAuthorityContext()
    {
        if (! Schema::hasTable('md_trading_status_revisions')) {
            return;
        }
        Schema::table('md_trading_status_revisions', function (Blueprint $table) {
            if (! Schema::hasColumn('md_trading_status_revisions', 'status_event_uid')) $table->string('status_event_uid', 64)->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'instrument_id')) $table->unsignedBigInteger('instrument_id')->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'status_type_code')) $table->string('status_type_code', 64)->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'source_name')) $table->string('source_name', 64)->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'source_payload_hash')) $table->char('source_payload_hash', 64)->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'announced_at')) $table->dateTime('announced_at')->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'operator_name')) $table->string('operator_name', 128)->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'governed_reason_code')) $table->string('governed_reason_code', 64)->nullable();
            if (! Schema::hasColumn('md_trading_status_revisions', 'authoritative_source_ref')) $table->string('authoritative_source_ref', 255)->nullable();
        });
    }

    private function createStatusSourceRegistry()
    {
        if (! Schema::hasTable('md_trading_status_source_registry')) {
            Schema::create('md_trading_status_source_registry', function (Blueprint $table) {
                $table->string('source_name', 64);
                $table->string('status_type_code', 64)->default('*');
                $table->string('authority_class', 32);
                $table->unsignedInteger('priority');
                $table->boolean('active')->default(true);
                $table->string('source_ref_pattern', 255)->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
                $table->primary(['source_name', 'status_type_code'], 'pk_md_status_source_registry');
                $table->index(['status_type_code', 'authority_class', 'priority'], 'idx_md_status_source_priority');
            });
        }

        $now = date('Y-m-d H:i:s');
        foreach ([
            ['IDX_OFFICIAL', '*', 'EXCHANGE_AUTHORITATIVE', 10, 'idx.co.id'],
            ['IDX_LONG_SUSPENSION_SNAPSHOT', 'SUSPENSION_OBSERVED', 'EXCHANGE_AUTHORITATIVE', 10, 'block.idx.id'],
            ['GOVERNED_OPERATOR_ENTRY', '*', 'OPERATOR_ENTERED', 100, null],
        ] as $row) {
            DB::table('md_trading_status_source_registry')->updateOrInsert(
                ['source_name' => $row[0], 'status_type_code' => $row[1]],
                [
                    'authority_class' => $row[2], 'priority' => $row[3], 'active' => 1,
                    'source_ref_pattern' => $row[4], 'created_at' => $now, 'updated_at' => $now,
                ]
            );
        }
    }

    private function addManualTransportContext()
    {
        if (! Schema::hasTable('market_data_trading_status_events')) {
            return;
        }
        Schema::table('market_data_trading_status_events', function (Blueprint $table) {
            if (! Schema::hasColumn('market_data_trading_status_events', 'origin_authority_class')) $table->string('origin_authority_class', 32)->nullable();
            if (! Schema::hasColumn('market_data_trading_status_events', 'source_payload_hash')) $table->char('source_payload_hash', 64)->nullable();
            if (! Schema::hasColumn('market_data_trading_status_events', 'operator_name')) $table->string('operator_name', 128)->nullable();
            if (! Schema::hasColumn('market_data_trading_status_events', 'governed_reason_code')) $table->string('governed_reason_code', 64)->nullable();
            if (! Schema::hasColumn('market_data_trading_status_events', 'authoritative_source_ref')) $table->string('authoritative_source_ref', 255)->nullable();
            if (! Schema::hasColumn('market_data_trading_status_events', 'transport_state')) $table->string('transport_state', 32)->nullable();
        });
    }

    private function downgradeUnsupportedLegacyCalendarClaims()
    {
        if (Schema::hasTable('market_calendar')
            && Schema::hasColumn('market_calendar', 'provenance_tier')
            && Schema::hasColumn('market_calendar', 'reconciled_at')
            && Schema::hasColumn('market_calendar', 'reconciliation_source_ref')) {
            DB::table('market_calendar')
                ->where('provenance_tier', 'VERIFIED')
                ->where(function ($query) {
                    $query->whereNull('reconciled_at')->orWhereNull('reconciliation_source_ref');
                })
                ->update(['provenance_tier' => 'PROJECTED']);
        }

        if (! Schema::hasTable('md_market_calendar_revisions')) {
            return;
        }
        $rows = DB::table('md_market_calendar_revisions as revision')
            ->leftJoin('md_market_calendar_revisions as newer', 'newer.supersedes_revision_id', '=', 'revision.calendar_revision_id')
            ->whereNull('newer.calendar_revision_id')
            ->where('revision.provenance_tier', 'VERIFIED')
            ->where('revision.source_version', 'legacy_calendar_projection_v1')
            ->get(['revision.*']);
        $now = date('Y-m-d H:i:s');
        foreach ($rows as $row) {
            DB::table('md_market_calendar_revisions')->insert([
                'market_code' => $row->market_code,
                'market_segment' => $row->market_segment,
                'cal_date' => $row->cal_date,
                'revision_uid' => hash('sha256', $row->revision_uid.'|AUTHORITY_DOWNGRADE|PROJECTED'),
                'timezone' => $row->timezone,
                'is_trading_day' => $row->is_trading_day,
                'is_half_day' => $row->is_half_day,
                'session_state' => 'UNKNOWN',
                'session_open_at' => $row->session_open_at,
                'session_close_at' => $row->session_close_at,
                'completed_at' => null,
                'recorded_at' => $now,
                'source_observation_id' => $row->source_observation_id,
                'supersedes_revision_id' => $row->calendar_revision_id,
                'source_ref' => $row->source_ref,
                'source_version' => 'legacy_calendar_projection_v2',
                'provenance_tier' => 'PROJECTED',
                'reconciled_at' => null,
                'reconciliation_source_ref' => null,
            ]);
        }
    }

    private function dropExisting($tableName, array $columns)
    {
        if (! Schema::hasTable($tableName)) return;
        $existing = array_values(array_filter($columns, function ($column) use ($tableName) {
            return Schema::hasColumn($tableName, $column);
        }));
        if ($existing === []) return;
        Schema::table($tableName, function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
}

