<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedStageEightAdmissionReasonCodes extends Migration
{
    private function rows(): array
    {
        return [
            ['AUTHORITATIVE_TRADING_STATUS_VALIDATED', 'STATUS', 'An official IDX long-suspension snapshot was captured and validated against its declared as-of scope.', 'INFO'],
            ['AUTHORITATIVE_TRADING_STATUS_TRANSITIONS_VALIDATED', 'STATUS', 'The official IDX suspension-transition search was captured and validated through the measured frontier.', 'INFO'],
            ['STAGE8_CONFORMANT_SUFFIX_ADMITTED', 'GOVERNANCE', 'A measured continuous suffix met the locked coverage threshold and quality requirements under verified status evidence.', 'INFO'],
            ['STAGE8_PRE_ADMISSION_READ_BLOCKED', 'READINESS', 'A requested date precedes the active conformant-corpus admission boundary and is not consumer-readable.', 'HARD'],
            ['STAGE8_ADMISSION_EVIDENCE_INVALID', 'GOVERNANCE', 'Stage 8 admission evidence was absent, inconsistent, or not hash-verifiable.', 'HARD'],
            ['STAGE8_ADMISSION_SUFFIX_NOT_FOUND', 'COVERAGE', 'No continuous suffix met the locked coverage and quality requirements.', 'HARD'],
            ['STAGE8_BLOCKED_CAMPAIGN_SUPERSEDED', 'GOVERNANCE', 'A blocked full-range Stage 8 campaign was superseded by an explicit measured admission decision without changing its immutable attempts.', 'INFO'],
            ['TRADING_STATUS_REVISION_BINDING_MISSING', 'STATUS', 'A BAR_NOT_EXPECTED eligibility fact lacks its verified trading-status revision and source-observation binding.', 'HARD'],
        ];
    }

    public function up()
    {
        if (! Schema::hasTable('eod_reason_codes')) {
            throw new RuntimeException('STAGE_8_REASON_REGISTRY_MISSING: eod_reason_codes must exist before Stage 8 admission.');
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
