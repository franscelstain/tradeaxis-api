<?php

namespace App\Application\MarketData\Services;

class EligibilityDecisionService
{
    /**
     * Indicator reason codes that carry a more specific eligibility meaning than the generic
     * ELIG_INVALID_INDICATORS fallback.
     *
     * Owner contract: docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md
     */
    private const INDICATOR_REASON_TO_ELIGIBILITY_REASON = [
        'IND_INSUFFICIENT_HISTORY' => 'ELIG_INSUFFICIENT_HISTORY',
        'IND_CORPORATE_ACTION_DISCONTINUITY' => 'ELIG_CORPORATE_ACTION_DISCONTINUITY',
        'IND_PRICE_SCALE_DISCONTINUITY' => 'ELIG_PRICE_SCALE_DISCONTINUITY',
    ];

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
            $indicatorReason = $indicator['invalid_reason_code'];

            return [
                'eligible' => 0,
                'reason_code' => isset(self::INDICATOR_REASON_TO_ELIGIBILITY_REASON[$indicatorReason])
                    ? self::INDICATOR_REASON_TO_ELIGIBILITY_REASON[$indicatorReason]
                    : 'ELIG_INVALID_INDICATORS',
            ];
        }

        return [
            'eligible' => 1,
            'reason_code' => null,
        ];
    }
}
