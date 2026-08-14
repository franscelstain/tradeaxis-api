<?php

namespace App\Infrastructure\Persistence\MarketData;

use Illuminate\Support\Facades\DB;

class TemporalTradingStatusRepository
{
    public function resolveForListing($listingId, $tradeDate, $knownAt = null)
    {
        $query = DB::table('md_trading_status_revisions')
            ->where('listing_id', $listingId)
            ->where('effective_from', '<=', $tradeDate.' 23:59:59')
            ->where(function ($q) use ($tradeDate) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>', $tradeDate.' 00:00:00');
            })
            ->whereNull('retracted_at')
            ->where('verification_state', 'VERIFIED');

        if ($knownAt !== null) {
            $query->where('recorded_at', '<=', $knownAt);
        }

        $rows = $query->orderByDesc('recorded_at')->get();
        if ($rows->isEmpty()) {
            return [
                'status_code' => 'UNKNOWN',
                'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
                'authority_class' => null,
                'status_revision_id' => null,
                'reason_code' => 'TRADING_STATUS_NO_EVIDENCE',
            ];
        }

        $states = $rows->map(function ($row) {
            return $row->status_code.'|'.$row->bar_expectation_state;
        })->unique()->values();

        if ($states->count() > 1) {
            return [
                'status_code' => 'CONFLICTING',
                'bar_expectation_state' => 'BAR_EXPECTATION_UNKNOWN',
                'authority_class' => null,
                'status_revision_id' => null,
                'reason_code' => 'TRADING_STATUS_CONFLICT',
            ];
        }

        $row = $rows->first();

        return [
            'status_code' => (string) $row->status_code,
            'bar_expectation_state' => (string) $row->bar_expectation_state,
            'authority_class' => (string) $row->authority_class,
            'status_revision_id' => (int) $row->status_revision_id,
            'reason_code' => null,
        ];
    }
}
