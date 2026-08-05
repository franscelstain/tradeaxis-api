<?php

namespace App\Application\MarketData\Services;

use Illuminate\Support\Facades\DB;

/**
 * Compatibility surface for a retired unsafe workflow.
 *
 * A price discontinuity can detect contamination, but cannot authoritatively establish
 * corporate-action type, terms, ex-date, or factor. Therefore both dry-run and apply are
 * non-mutating and return explicit capability-boundary evidence.
 */
class CorporateActionDerivationService
{
    public function checkRecordedActions($apply = false): array
    {
        return [
            'checked' => 0,
            'updated' => 0,
            'capability_state' => 'AUTHORITATIVE_EVENT_TERMS_REQUIRED',
            'mutation_performed' => false,
            'apply_requested' => (bool) $apply,
        ];
    }

    public function derive($apply = false): array
    {
        $skipped = DB::table('market_data_price_scale_breaks')
            ->whereIn('review_status', ['DETECTED', 'CONFIRMED'])
            ->orderBy('ticker_code')
            ->orderBy('trade_date')
            ->get()
            ->map(function ($break) use ($apply) {
                return [
                    'ticker_code' => (string) $break->ticker_code,
                    'trade_date' => (string) $break->trade_date,
                    'break_id' => (int) $break->price_scale_break_id,
                    'reason_code' => 'CORPORATE_ACTION_AUTHORITATIVE_EVIDENCE_REQUIRED',
                    'reason' => 'price break is anomaly evidence only; no event, date, type, or factor may be synthesized',
                    'apply_requested' => (bool) $apply,
                    'mutation_performed' => false,
                ];
            })
            ->all();

        return [
            'derived' => [],
            'skipped' => $skipped,
            'capability_state' => 'DETECTION_ONLY',
            'capability_limit' => 'Corporate-action terms and factors require independent authoritative evidence.',
            'mutation_performed' => false,
        ];
    }
}
