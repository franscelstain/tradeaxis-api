<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedAuthoritativeMarketStructureReasonCode extends Migration
{
    const CODE = 'AUTHORITATIVE_MARKET_STRUCTURE_VALIDATED';
    const CATEGORY = 'MARKET_STRUCTURE';
    const DESCRIPTION = 'An effective-dated IDX price-band, minimum-price, or tick-ladder revision was validated against immutable authoritative evidence without applying it to a series.';

    public function up()
    {
        if (! Schema::hasTable('eod_reason_codes')) {
            throw new RuntimeException('STAGE_7_REASON_REGISTRY_MISSING: eod_reason_codes must exist before Stage 7.');
        }

        $existing = DB::table('eod_reason_codes')->where('code', self::CODE)->first();
        if ($existing) {
            if ((string) $existing->category !== self::CATEGORY
                || (string) $existing->description !== self::DESCRIPTION
                || (string) $existing->severity !== 'INFO'
                || (int) $existing->is_active !== 1) {
                throw new RuntimeException('STAGE_7_REASON_REGISTRY_CONFLICT: existing reason-code semantics differ.');
            }

            return;
        }

        $now = date('Y-m-d H:i:s');
        DB::table('eod_reason_codes')->insert([
            'code' => self::CODE,
            'category' => self::CATEGORY,
            'description' => self::DESCRIPTION,
            'severity' => 'INFO',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down()
    {
        if (! Schema::hasTable('eod_reason_codes')) {
            return;
        }

        DB::table('eod_reason_codes')
            ->where('code', self::CODE)
            ->where('category', self::CATEGORY)
            ->where('description', self::DESCRIPTION)
            ->where('severity', 'INFO')
            ->delete();
    }
}
