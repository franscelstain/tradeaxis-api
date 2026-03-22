<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodRun;
use App\Models\EodRunEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EodRunRepository
{
    public function getOrCreateOwningRun($requestedDate, $sourceMode, $stage, $supersedesRunId = null)
    {
        return DB::transaction(function () use ($requestedDate, $sourceMode, $stage, $supersedesRunId) {
            $activeRun = EodRun::query()
                ->where('trade_date_requested', $requestedDate)
                ->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
                ->orderByDesc('run_id')
                ->lockForUpdate()
                ->first();

            if ($activeRun) {
                return $activeRun;
            }

            $now = Carbon::now(config('market_data.platform.timezone'));

            return EodRun::query()->create([
                'trade_date_requested' => $requestedDate,
                'trade_date_effective' => null,
                'lifecycle_state' => 'PENDING',
                'terminal_status' => null,
                'quality_gate_state' => 'PENDING',
                'publishability_state' => 'NOT_READABLE',
                'stage' => $stage,
                'source' => $sourceMode,
                'coverage_ratio' => null,
                'bars_rows_written' => null,
                'indicators_rows_written' => null,
                'eligibility_rows_written' => null,
                'invalid_bar_count' => null,
                'invalid_indicator_count' => null,
                'hard_reject_count' => null,
                'warning_count' => null,
                'notes' => null,
                'bars_batch_hash' => null,
                'indicators_batch_hash' => null,
                'eligibility_batch_hash' => null,
                'config_version' => config('market_data.indicators.set_version'),
                'config_hash' => null,
                'config_snapshot_ref' => null,
                'supersedes_run_id' => $supersedesRunId,
                'publication_version' => null,
                'is_current_publication' => 0,
                'sealed_at' => null,
                'sealed_by' => null,
                'seal_note' => null,
                'started_at' => $now,
                'finished_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function findByRunId($runId)
    {
        return EodRun::query()->where('run_id', $runId)->first();
    }

    public function touchStage(EodRun $run, $stage, array $attributes = [])
    {
        $run->stage = $stage;

        if ($run->lifecycle_state === 'PENDING') {
            $run->lifecycle_state = 'RUNNING';
        }

        foreach ($attributes as $key => $value) {
            $run->{$key} = $value;
        }

        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function appendEvent(EodRun $run, $stage, $eventType, $severity, $message, $reasonCode = null, array $payload = [])
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $severity = $this->normalizeSeverity($severity);
        $message = $this->truncateMessage($message);

        return EodRunEvent::query()->create([
            'run_id' => $run->run_id,
            'trade_date_requested' => $run->trade_date_requested,
            'event_time' => $now,
            'stage' => $stage,
            'event_type' => $eventType,
            'severity' => $severity,
            'reason_code' => $reasonCode,
            'message' => $message,
            'event_payload_json' => empty($payload) ? null : json_encode($payload),
            'created_at' => $now,
        ]);
    }

    public function failStage(EodRun $run, $stage, $reasonCode, $message, array $payload = [])
    {
        $safePayload = array_merge([
            'run_id' => (int) $run->run_id,
            'stage' => $stage,
            'reason_code' => $reasonCode,
        ], $payload);

        $this->appendEvent(
            $run,
            $stage,
            'STAGE_FAILED',
            'ERROR',
            $message,
            $reasonCode,
            $safePayload
        );

        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->lifecycle_state = 'FAILED';
        $run->terminal_status = 'FAILED';
        $run->quality_gate_state = 'FAIL';
        $run->publishability_state = 'NOT_READABLE';
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function updateTelemetry(EodRun $run, array $telemetry)
    {
        foreach ($telemetry as $key => $value) {
            $run->{$key} = $value;
        }

        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function storeHashes(EodRun $run, array $hashes)
    {
        $run->bars_batch_hash = $hashes['bars_batch_hash'];
        $run->indicators_batch_hash = $hashes['indicators_batch_hash'];
        $run->eligibility_batch_hash = $hashes['eligibility_batch_hash'];
        $run->updated_at = Carbon::now(config('market_data.platform.timezone'));
        $run->save();

        return $run->fresh();
    }

    public function markSealed(EodRun $run, $sealedBy, $sealNote)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));
        $run->sealed_at = $now;
        $run->sealed_by = $sealedBy;
        $run->seal_note = $sealNote;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }

    public function syncCurrentPublicationMirror($tradeDate, $currentRunId)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        EodRun::query()
            ->where('trade_date_requested', $tradeDate)
            ->update([
                'is_current_publication' => 0,
                'updated_at' => $now,
            ]);

        EodRun::query()
            ->where('run_id', $currentRunId)
            ->update([
                'is_current_publication' => 1,
                'updated_at' => $now,
            ]);
    }


    private function normalizeSeverity($severity)
    {
        if ($severity === 'WARNING') {
            return 'WARN';
        }

        return in_array($severity, ['INFO', 'WARN', 'ERROR'], true) ? $severity : 'ERROR';
    }

    private function truncateMessage($message)
    {
        $message = trim((string) $message);
        if ($message === '') {
            return null;
        }

        return mb_strlen($message) <= 255 ? $message : mb_substr($message, 0, 252).'...';
    }

    public function finalize(EodRun $run, array $finalState)
    {
        $now = Carbon::now(config('market_data.platform.timezone'));

        foreach ($finalState as $key => $value) {
            $run->{$key} = $value;
        }

        $run->lifecycle_state = $finalState['lifecycle_state'];
        $run->finished_at = $now;
        $run->updated_at = $now;
        $run->save();

        return $run->fresh();
    }
}
