<?php

namespace App\Application\MarketData\Services;

class EligibilityDecisionService
{
    public function decide($bar, $indicator)
    {
        if (! $bar) {
            return [
                'eligible' => 0,
                'reason_code' => 'ELIG_MISSING_BAR',
            ];
        }

        if (! $indicator) {
            return [
                'eligible' => 0,
                'reason_code' => 'ELIG_MISSING_INDICATORS',
            ];
        }

        if ((int) $indicator['is_valid'] === 0) {
            return [
                'eligible' => 0,
                'reason_code' => $indicator['invalid_reason_code'] === 'IND_INSUFFICIENT_HISTORY'
                    ? 'ELIG_INSUFFICIENT_HISTORY'
                    : 'ELIG_INVALID_INDICATORS',
            ];
        }

        return [
            'eligible' => 1,
            'reason_code' => null,
        ];
    }
}
