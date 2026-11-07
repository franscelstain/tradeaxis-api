<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodCorrectionRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

/** Read-only operator disclosure, re-evaluated before acquisition's first write. */
class CorrectionCommandPreflightService
{
    private $corrections;
    private $publications;
    private $snapshots;
    private $manualInput;

    public function __construct(EodCorrectionRepository $corrections, EodPublicationRepository $publications,
        MarketDataConfigSnapshotRepository $snapshots, ManualSourceInputContext $manualInput)
    {
        $this->corrections = $corrections;
        $this->publications = $publications;
        $this->snapshots = $snapshots;
        $this->manualInput = $manualInput;
    }

    public function inspect(int $correctionId, string $date, string $sourceMode, bool $lockBaseline = false): array
    {
        $correction = DB::transactionLevel() > 0
            ? $this->corrections->findByIdForUpdate($correctionId)
            : $this->corrections->findById($correctionId);
        if (! $correction || (string) $correction->trade_date !== $date
            || ! in_array((string) $correction->status, ['APPROVED', 'EXECUTING', 'RESEALED'], true)) {
            throw new \RuntimeException('COMMAND_CORRECTION_PREFLIGHT_NOT_AUTHORIZED');
        }
        if (! in_array($sourceMode, ['api', 'manual_file'], true)) {
            throw new \RuntimeException('COMMAND_CORRECTION_SOURCE_UNVERIFIABLE');
        }
        $baseline = $this->publications->findCorrectionBaselinePublicationForTradeDate($date, $lockBaseline);
        if (! $baseline || empty($correction->baseline_publication_id)
            || (int) $baseline->publication_id !== (int) $correction->baseline_publication_id) {
            throw new \RuntimeException('COMMAND_CORRECTION_BASELINE_CHANGED');
        }
        $candidateHash = hash('sha256', $this->snapshots->currentContent());
        $active = EodRun::where('trade_date_requested', $date)->where('source', $sourceMode)
            ->where('request_mode', 'correction')->whereIn('lifecycle_state', ['PENDING', 'RUNNING', 'FINALIZING'])
            ->orderByDesc('run_id')->first();
        if ($active && (string) $active->config_hash !== $candidateHash) {
            throw new \RuntimeException('COMMAND_CORRECTION_CONFIG_CHANGED');
        }
        $sourceIdentity = $sourceMode === 'api'
            ? 'api:'.(string) config('market_data.source.api.provider')
            : app(\App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter::class)
                ->inspectManualFileSourceIdentity($date);
        if ($sourceIdentity === 'api:' || $sourceIdentity === 'manual_file:') {
            throw new \RuntimeException('COMMAND_CORRECTION_SOURCE_UNVERIFIABLE');
        }

        return [
            'correction_id' => $correctionId,
            'requested_date' => $date,
            'source_mode' => $sourceMode,
            'source_identity' => $sourceIdentity,
            'correction_status' => (string) $correction->status,
            'reason_code' => (string) $correction->correction_reason_code,
            'reason_note' => (string) $correction->correction_reason_note,
            'baseline_publication_id' => (int) $baseline->publication_id,
            'baseline_run_id' => (int) $baseline->run_id,
            'candidate_config_content_hash' => $candidateHash,
            'new_run_id' => $active ? (int) $active->run_id : 'PENDING_ALLOCATION',
            'new_config_snapshot_id' => $active ? (int) $active->config_snapshot_id : 'PENDING_ALLOCATION',
        ];
    }

    public function assertMatches(array $disclosed, bool $lockBaseline = false): void
    {
        if ($this->inspect((int) $disclosed['correction_id'], (string) $disclosed['requested_date'],
            (string) $disclosed['source_mode'], $lockBaseline) !== $disclosed) {
            throw new \RuntimeException('COMMAND_CORRECTION_PREFLIGHT_CHANGED');
        }
    }
}
