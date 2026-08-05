<?php

namespace App\Application\MarketData\Services;

use Illuminate\Support\Facades\DB;

/**
 * Compatibility surface for the prohibited in-place bar-repair workflow.
 * Corrections must create new immutable observations/publications from authoritative
 * evidence; anomaly geometry is never permission to rewrite canonical history.
 */
class PriceScaleStretchRepairService
{
    public function repair($tickerCode = null, $apply = false): array
    {
        $query = DB::table('market_data_price_scale_breaks')
            ->whereIn('review_status', ['DETECTED', 'CONFIRMED'])
            ->orderBy('ticker_code')
            ->orderBy('trade_date');

        if ($tickerCode !== null && trim((string) $tickerCode) !== '') {
            $query->where('ticker_code', strtoupper(trim((string) $tickerCode)));
        }

        $skipped = $query->get()->map(function ($break) use ($apply) {
            return [
                'ticker_code' => (string) $break->ticker_code,
                'trade_date' => (string) $break->trade_date,
                'break_id' => (int) $break->price_scale_break_id,
                'reason_code' => 'IMMUTABLE_HISTORY_CORRECTION_REQUIRED',
                'reason' => 'price anomaly cannot authorize in-place canonical history mutation',
                'apply_requested' => (bool) $apply,
                'mutation_performed' => false,
            ];
        })->all();

        return [
            'stretches' => [],
            'skipped' => $skipped,
            'capability_state' => 'DETECTION_ONLY',
            'required_path' => 'authoritative evidence -> new source observation -> correction publication',
            'mutation_performed' => false,
        ];
    }
}
