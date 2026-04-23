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
            'final_outcome_note' => null,
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

        if ($correction->current_consumed_at !== null || in_array($correction->status, ['PUBLISHED', 'CONSUMED_CURRENT', 'CLOSED'], true)) {
            throw new \RuntimeException('Correction request is already consumed for correction_current execution and cannot be approved again.');
        }

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
        return $this->canExecuteCorrection($correctionId, $tradeDate, 'correction_current');
    }

    public function canExecuteCorrection($correctionId, $tradeDate, $mode = 'correction_current')
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

        if (! in_array($mode, ['correction_current', 'repair_candidate'], true)) {
            throw new \InvalidArgumentException('Unsupported correction execution mode: '.$mode);
        }

        if ($correction->current_consumed_at !== null || in_array($correction->status, ['PUBLISHED', 'CONSUMED_CURRENT', 'CLOSED'], true)) {
            throw new \RuntimeException('Correction request is already consumed for correction_current execution and cannot be executed again.');
        }

        $allowedStatuses = $mode === 'repair_candidate'
            ? ['APPROVED', 'EXECUTING', 'RESEALED', 'REPAIR_ACTIVE', 'REPAIR_EXECUTED', 'REPAIR_CANDIDATE']
            : ['APPROVED', 'EXECUTING', 'RESEALED'];

        if (! in_array($correction->status, $allowedStatuses, true)) {
            if ($mode === 'repair_candidate') {
                throw new \RuntimeException('Correction request is not eligible for repair_candidate execution. Current status='.$correction->status);
            }

            throw new \RuntimeException('Correction request must be APPROVED before execution.');
        }

        return $correction;
    }

    public function markExecuting($correctionId, $priorRunId, $newRunId, $mode = 'correction_current')
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $status = $mode === 'repair_candidate' ? 'REPAIR_ACTIVE' : 'EXECUTING';

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update([
                'status' => $status,
                'prior_run_id' => $priorRunId,
                'new_run_id' => $newRunId,
                'execution_count' => DB::raw('COALESCE(execution_count, 0) + 1'),
                'last_executed_at' => $now,
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

    public function markPublished($correctionId, $newRunId, $priorRunId = null, $finalOutcomeNote = null)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $payload = [
            'status' => 'PUBLISHED',
            'new_run_id' => $newRunId,
            'published_at' => $now,
            'current_consumed_at' => $now,
            'final_outcome_note' => $finalOutcomeNote,
            'updated_at' => $now,
        ];

        if ($priorRunId !== null) {
            $payload['prior_run_id'] = $priorRunId;
        }

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update($payload);

        return $this->findById($correctionId);
    }


    public function markRepairExecuted($correctionId, $newRunId = null, $priorRunId = null, $finalOutcomeNote = null)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $payload = [
            'status' => 'REPAIR_EXECUTED',
            'new_run_id' => $newRunId,
            'final_outcome_note' => $finalOutcomeNote,
            'last_executed_at' => $now,
            'updated_at' => $now,
        ];

        if ($priorRunId !== null) {
            $payload['prior_run_id'] = $priorRunId;
        }

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update($payload);

        return $this->findById($correctionId);
    }

    public function markRepairCandidate($correctionId, $newRunId = null, $priorRunId = null, $finalOutcomeNote = null)
    {
        return $this->markRepairExecuted($correctionId, $newRunId, $priorRunId, $finalOutcomeNote);
    }

    public function markConsumedForCurrent($correctionId, $newRunId = null, $priorRunId = null, $finalOutcomeNote = null)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $payload = [
            'status' => 'CONSUMED_CURRENT',
            'new_run_id' => $newRunId,
            'final_outcome_note' => $finalOutcomeNote,
            'current_consumed_at' => $now,
            'updated_at' => $now,
        ];

        if ($priorRunId !== null) {
            $payload['prior_run_id'] = $priorRunId;
        }

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update($payload);

        return $this->findById($correctionId);
    }

    public function markRejected($correctionId, $finalOutcomeNote = null)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update([
                'status' => 'REJECTED',
                'final_outcome_note' => $finalOutcomeNote,
                'updated_at' => $now,
            ]);

        return $this->findById($correctionId);
    }

    public function markCancelled($correctionId, $newRunId = null, $priorRunId = null, $finalOutcomeNote = null, $consumeCurrent = true)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        $payload = [
            'status' => $consumeCurrent ? 'CONSUMED_CURRENT' : 'CANCELLED',
            'new_run_id' => $newRunId,
            'final_outcome_note' => $finalOutcomeNote,
            'updated_at' => $now,
        ];

        if ($consumeCurrent) {
            $payload['current_consumed_at'] = $now;
        }

        if ($priorRunId !== null) {
            $payload['prior_run_id'] = $priorRunId;
        }

        EodDatasetCorrection::query()
            ->where('correction_id', $correctionId)
            ->update($payload);

        return $this->findById($correctionId);
    }
}
