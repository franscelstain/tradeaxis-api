<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodDatasetCorrection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodCorrectionRepository
{
    public function createRequest($tradeDate, $reasonCode, $reasonNote, $requestedBy)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        return EodDatasetCorrection::query()->create([
            'trade_date' => $tradeDate,
            'prior_run_id' => null,
            'new_run_id' => null,
            'correction_reason_code' => $reasonCode,
            'correction_reason_note' => $reasonNote,
            'status' => 'REQUESTED',
            'requested_by' => $requestedBy,
            'requested_at' => $now,
            'approved_by' => null,
            'approved_at' => null,
            'published_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function findById($correctionId)
    {
        return EodDatasetCorrection::query()->where('correction_id', $correctionId)->first();
    }

    public function approve($correctionId, $approvedBy)
    {
        $correction = EodDatasetCorrection::query()->where('correction_id', $correctionId)->firstOrFail();
        $now = Carbon::now(config('market_data.platform.timezone'));
        $correction->status = 'APPROVED';
        $correction->approved_by = $approvedBy;
        $correction->approved_at = $now;
        $correction->updated_at = $now;
        $correction->save();

        return $correction->fresh();
    }

    public function requireApprovedForTradeDate($correctionId, $tradeDate)
    {
        $correction = EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->lockForUpdate()
            ->first();

        if (! $correction) {
            throw new \RuntimeException('Correction request not found: '.$correctionId);
        }

        if ((string) $correction->trade_date !== (string) $tradeDate) {
            throw new \RuntimeException('Correction request trade_date mismatch against requested_date.');
        }

        if ($correction->status !== 'APPROVED' && $correction->status !== 'EXECUTING' && $correction->status !== 'RESEALED') {
            throw new \RuntimeException('Correction request must be APPROVED before execution.');
        }

        return $correction;
    }

    public function markExecuting($correctionId, $priorRunId, $newRunId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update([
                'status' => 'EXECUTING',
                'prior_run_id' => $priorRunId,
                'new_run_id' => $newRunId,
                'updated_at' => $now,
            ]);

        return $this->findById($correctionId);
    }

    public function markResealed($correctionId, $newRunId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update([
                'status' => 'RESEALED',
                'new_run_id' => $newRunId,
                'updated_at' => $now,
            ]);

        return $this->findById($correctionId);
    }

    public function markPublished($correctionId, $newRunId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update([
                'status' => 'PUBLISHED',
                'new_run_id' => $newRunId,
                'published_at' => $now,
                'updated_at' => $now,
            ]);

        return $this->findById($correctionId);
    }

    public function markCancelled($correctionId, $newRunId = null)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update([
                'status' => 'CANCELLED',
                'new_run_id' => $newRunId,
                'updated_at' => $now,
            ]);

        return $this->findById($correctionId);
    }
}
