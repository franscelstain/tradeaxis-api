<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedStageEightStreamingCacheReasonCodes extends Migration
{
    private function rows(): array
    {
        return [
            ['STAGE8_ACQUISITION_CACHE_CLEANUP_FAILED', 'SOURCE', 'The temporary Stage 8 date-row cache could not be removed after a successful oracle.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_CLEANUP_REFUSED', 'SOURCE', 'Stage 8 refused cache cleanup because the bounded rows directory contained an unexpected entry.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_DATE_INVALID', 'SOURCE', 'A Stage 8 per-date acquisition cache contained an invalid batch record.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_DATE_MISSING', 'SOURCE', 'A Stage 8 target had no per-date acquisition cache after acquisition completed.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_DATE_UNREADABLE', 'SOURCE', 'A Stage 8 per-date acquisition cache could not be opened.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_IDENTITY_MISMATCH', 'SOURCE', 'A resumable Stage 8 acquisition cache did not match the frozen campaign/date/ticker identity.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_MANIFEST_INVALID', 'SOURCE', 'The resumable Stage 8 acquisition manifest was not valid structured JSON.', 'HARD'],
            ['COMMAND_RESUME_REQUIRES_APPLY', 'COMMAND', 'A reconstruction resume was requested without the explicit apply flag, so no campaign work was executed.', 'HARD'],
        ];
    }

    public function up()
    {
        if (! Schema::hasTable('eod_reason_codes')) {
            throw new RuntimeException('STAGE_8_REASON_REGISTRY_MISSING: eod_reason_codes must exist before Stage 8.');
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->rows() as [$code, $category, $description, $severity]) {
            $existing = DB::table('eod_reason_codes')->where('code', $code)->first();
            if ($existing) {
                if ((string) $existing->category !== $category
                    || (string) $existing->description !== $description
                    || (string) $existing->severity !== $severity
                    || (int) $existing->is_active !== 1) {
                    throw new RuntimeException('STAGE_8_REASON_REGISTRY_CONFLICT: '.$code.' semantics differ.');
                }
                continue;
            }

            DB::table('eod_reason_codes')->insert([
                'code' => $code,
                'category' => $category,
                'description' => $description,
                'severity' => $severity,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        if (! Schema::hasTable('eod_reason_codes')) {
            return;
        }

        foreach ($this->rows() as [$code, $category, $description, $severity]) {
            DB::table('eod_reason_codes')
                ->where('code', $code)
                ->where('category', $category)
                ->where('description', $description)
                ->where('severity', $severity)
                ->delete();
        }
    }
}
