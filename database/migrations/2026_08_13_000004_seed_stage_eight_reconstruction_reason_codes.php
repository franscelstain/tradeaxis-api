<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedStageEightReconstructionReasonCodes extends Migration
{
    private function rows(): array
    {
        return [
            ['STAGE8_CURRENT_CORPUS_RECONSTRUCTION', 'CORRECTION', 'A frozen current EOD publication is being replaced through the complete correction lifecycle using a fresh Yahoo observation set while retaining the immutable baseline.', 'INFO'],
            ['STAGE8_RECONSTRUCTION_FAILED', 'CORRECTION', 'A Stage 8 reconstruction target failed without a more specific registered reason and remained incomplete.', 'HARD'],
            ['STAGE8_BASELINE_POINTER_DRIFT', 'CORRECTION', 'A current publication pointer no longer matches the baseline frozen by the Stage 8 campaign, so reconstruction stopped.', 'HARD'],
            ['STAGE8_DATE_NOT_READABLE', 'CORRECTION', 'A reconstructed trade date did not end in a successful readable publication and its current pointer was not accepted.', 'HARD'],
            ['STAGE8_POINTER_SWITCH_NOT_PROVEN', 'CORRECTION', 'A reconstructed trade date could not prove that its current pointer switched to the new sealed publication.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_WRITE_FAILED', 'SOURCE', 'The resumable Stage 8 Yahoo acquisition cache could not be written durably.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_CLEANUP_FAILED', 'SOURCE', 'The temporary Stage 8 date-row cache could not be removed after a successful oracle.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_CLEANUP_REFUSED', 'SOURCE', 'Stage 8 refused cache cleanup because the bounded rows directory contained an unexpected entry.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_DATE_INVALID', 'SOURCE', 'A Stage 8 per-date acquisition cache contained an invalid batch record.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_DATE_MISSING', 'SOURCE', 'A Stage 8 target had no per-date acquisition cache after acquisition completed.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_DATE_UNREADABLE', 'SOURCE', 'A Stage 8 per-date acquisition cache could not be opened.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_IDENTITY_MISMATCH', 'SOURCE', 'A resumable Stage 8 acquisition cache did not match the frozen campaign/date/ticker identity.', 'HARD'],
            ['STAGE8_ACQUISITION_CACHE_MANIFEST_INVALID', 'SOURCE', 'The resumable Stage 8 acquisition manifest was not valid structured JSON.', 'HARD'],
            ['STAGE8_BASELINE_HASH_MISSING', 'CORRECTION', 'A frozen current baseline lacked a mandatory artifact hash and was ineligible for reconstruction.', 'HARD'],
            ['STAGE8_BASELINE_NOT_SEALED', 'CORRECTION', 'A current baseline was not a sealed successful publication and was ineligible for reconstruction.', 'HARD'],
            ['STAGE8_CAMPAIGN_NOT_FOUND', 'CORRECTION', 'The requested Stage 8 reconstruction campaign does not exist.', 'HARD'],
            ['STAGE8_CAMPAIGN_RESUME_REQUIRED', 'CORRECTION', 'An unfinished Stage 8 campaign already exists and must be explicitly resumed.', 'HARD'],
            ['STAGE8_COMPLETED_CAMPAIGN_ORACLE_DRIFT', 'CORRECTION', 'A completed Stage 8 campaign no longer satisfies its frozen completion oracle.', 'HARD'],
            ['STAGE8_CURRENT_CORPUS_ORACLE_FAILED', 'CORRECTION', 'The final Stage 8 current-corpus oracle found one or more violations.', 'HARD'],
            ['STAGE8_FROZEN_SCOPE_EMPTY', 'CORRECTION', 'Stage 8 could not freeze a reconstruction scope because no current publication pointers exist.', 'HARD'],
            ['STAGE8_FROZEN_SCOPE_POINTER_CALENDAR_MISMATCH', 'CORRECTION', 'The frozen current-pointer dates did not exactly match the authoritative trading-calendar dates.', 'HARD'],
            ['STAGE8_OUTPUT_DIRECTORY_CREATE_FAILED', 'CORRECTION', 'The Stage 8 resumable campaign output directory could not be created.', 'HARD'],
            ['STAGE8_SOURCE_ACQUISITION_FAILED', 'SOURCE', 'The bounded Stage 8 Yahoo range acquisition failed systemically.', 'HARD'],
            ['TEMPORAL_IDENTITY_PROJECTION_INCOMPLETE', 'IDENTITY', 'Stage 8 found legacy ticker identities not projected into the temporal identity model and stopped without mutating them.', 'HARD'],
            ['COMMAND_RESUME_REQUIRES_APPLY', 'COMMAND', 'A reconstruction resume was requested without the explicit apply flag, so no campaign work was executed.', 'HARD'],
            ['FACTOR_APPLIED_SOURCE_AS_TRADED', 'CORPORATE_ACTION', 'An authoritative structural factor was applied because the bound Yahoo source-scale assessment classified the source series as as-traded.', 'INFO'],
            ['FACTOR_HELD_PROVIDER_BACK_ADJUSTED', 'CORPORATE_ACTION', 'An authoritative structural factor was held because the bound Yahoo source-scale assessment classified the source series as provider-back-adjusted.', 'WARN'],
            ['FACTOR_HELD_SOURCE_SCALE_UNKNOWN', 'CORPORATE_ACTION', 'An authoritative structural factor was held because the bound Yahoo source-scale assessment remained unknown.', 'WARN'],
            ['SOURCE_SCALE_MARKET_STRUCTURE_UNRESOLVED', 'CORPORATE_ACTION', 'Yahoo source scale remained unknown because admissible point-in-time market-structure context was insufficient; factor activation was prohibited.', 'HARD'],
            ['MARKET_STRUCTURE_BOARD_UNKNOWN', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because no listing-board value was available.', 'HARD'],
            ['MARKET_STRUCTURE_BOARD_NOT_POINT_IN_TIME', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because the listing-board value was recorded after the evaluated trade date.', 'HARD'],
            ['MARKET_STRUCTURE_SCOPE_EXCLUDED', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because the listing board is outside the standard-equity scope.', 'HARD'],
            ['MARKET_STRUCTURE_BOARD_UNRECOGNIZED', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because the listing-board value was not recognized by the locked scope.', 'HARD'],
            ['MARKET_STRUCTURE_REVISION_MISSING', 'MARKET_STRUCTURE', 'Market-structure resolution failed closed because one or more authoritative effective-dated rule revisions were unavailable.', 'HARD'],
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
