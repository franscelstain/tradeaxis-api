<?php

namespace App\Infrastructure\Persistence\MarketData;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionSnapshotRepository
{
    public function replaceSlotRows($tradeDate, $snapshotSlot, array $rows)
    {
        return DB::transaction(function () use ($tradeDate, $snapshotSlot, $rows) {
            DB::table('md_session_snapshots')
                ->where('trade_date', $tradeDate)
                ->where('snapshot_slot', $snapshotSlot)
                ->delete();

            if (! empty($rows)) {
                DB::table('md_session_snapshots')->insert($rows);
            }
        });
    }

    public function purgeBefore($cutoffTimestamp)
    {
        return DB::table('md_session_snapshots')
            ->where('captured_at', '<', $cutoffTimestamp)
            ->delete();
    }
}
