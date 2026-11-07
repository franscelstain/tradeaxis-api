<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use App\Infrastructure\Persistence\MarketData\EodRunRepository;
use App\Infrastructure\Persistence\MarketData\MarketDataConfigSnapshotRepository;
use App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository;
use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

/** Explicit linked recovery. An operator reason never substitutes for acquisition. */
final class CurrentPublicationRepairService
{
    private $publications;
    private $runs;
    private $configSnapshots;

    public function __construct(EodPublicationRepository $publications, EodRunRepository $runs, MarketDataConfigSnapshotRepository $configSnapshots)
    {
        $this->publications = $publications;
        $this->runs = $runs;
        $this->configSnapshots = $configSnapshots;
    }

    /** Read-only disclosure. The historical hash is never replaced by the recovery hash. */
    public function preflight(string $date, array $scanned): array
    {
        if (($scanned['pointer_trade_date'] ?? null) !== $date) {
            throw new \RuntimeException('COMMAND_REPAIR_SCOPE_INVALID');
        }
        // A missing publication row may still leave an authentic pointer-to-run identity.
        // Without either verified run, choosing an API source would invent recovery provenance.
        $historicalRunId = $scanned['run_id'] ?: ($scanned['pointer_run_id'] ?? null);
        $historical = empty($historicalRunId) ? null : EodRun::where('run_id', $historicalRunId)
            ->where('trade_date_requested', $date)->first();
        if (! $historical || ! in_array((string) $historical->source, ['api', 'manual_file'], true)) {
            throw new \RuntimeException('COMMAND_REPAIR_SOURCE_UNVERIFIABLE');
        }
        $snapshot = $historical && $historical->config_snapshot_id
            ? $this->configSnapshots->find($historical->config_snapshot_id) : null;
        if ($snapshot && (string) ($snapshot['config_hash'] ?? '') !== (string) $historical->config_hash) {
            throw new \RuntimeException('COMMAND_REPAIR_HISTORICAL_CONFIG_MISMATCH');
        }
        $historicalHash = $snapshot ? (string) $snapshot['config_hash'] : null;
        $unavailableReason = $historicalHash === null ? 'HISTORICAL_CONFIG_SNAPSHOT_UNVERIFIABLE' : null;

        return [
            'trade_date' => $date,
            'pointer_publication_id' => $scanned['pointer_publication_id'] ?? null,
            'pointer_run_id' => $scanned['pointer_run_id'] ?? null,
            'historical_run_id' => $historical ? (int) $historical->run_id : null,
            'historical_source_mode' => $historical ? (string) $historical->source : null,
            'historical_config_snapshot_id' => $snapshot ? (int) $historical->config_snapshot_id : null,
            'historical_config_hash' => $historicalHash,
            'historical_config_unavailable_reason' => $unavailableReason,
            'recovery_source_mode' => (string) $historical->source,
            'prospective_recovery_config_hash' => hash('sha256', $this->configSnapshots->currentContent()),
        ];
    }

    public function apply(string $date, array $scanned, string $reason, array $expectedPreflight): array
    {
        if ($reason === '' || ($scanned['pointer_trade_date'] ?? null) !== $date) {
            throw new \RuntimeException('COMMAND_REPAIR_SCOPE_INVALID: exact date, scan and audit reason required.');
        }
        return DB::transaction(function () use ($date, $scanned, $reason, $expectedPreflight) {
            // Before acquisition writes even its owner row, recheck the disclosed source/config/target
            // inside the transaction. The later date-fenced scan comparison closes the read-to-lock race.
            if ($this->preflight($date, $scanned) !== $expectedPreflight) {
                throw new \RuntimeException('COMMAND_REPAIR_PREFLIGHT_CHANGED');
            }
            $currentBeforeAcquisition = $this->publications->findInvalidCurrentPublicationStates($date)->first();
            if (! $currentBeforeAcquisition || RunInputCaptureRepository::canonicalJson((array) $currentBeforeAcquisition)
                !== RunInputCaptureRepository::canonicalJson($scanned)) {
                throw new \RuntimeException('COMMAND_REPAIR_SCAN_CHANGED: re-scan before requesting recovery.');
            }
            $historical = $expectedPreflight['historical_run_id'] === null ? null
                : EodRun::where('run_id', $expectedPreflight['historical_run_id'])
                    ->where('trade_date_requested', $date)->first();
            $owner = $this->runs->acquireLinkedRecoveryOwner($date,
                $expectedPreflight['recovery_source_mode'],
                $historical ? (int) $historical->run_id : null);
            if ($this->preflight($date, $scanned) !== $expectedPreflight
                || (string) $owner->config_hash !== (string) $expectedPreflight['prospective_recovery_config_hash']
                || (string) $owner->source !== (string) $expectedPreflight['recovery_source_mode']) {
                throw new \RuntimeException('COMMAND_REPAIR_PREFLIGHT_CHANGED');
            }

            DB::table('eod_current_publication_pointer')->where('trade_date', $date)->lockForUpdate()->first();
            $current = $this->publications->findInvalidCurrentPublicationStates($date)->first();
            if (! $current || RunInputCaptureRepository::canonicalJson((array) $current)
                !== RunInputCaptureRepository::canonicalJson($scanned)) {
                throw new \RuntimeException('COMMAND_REPAIR_SCAN_CHANGED: re-scan before requesting recovery.');
            }
            $reasons = $this->publications->determineCurrentIntegrityViolationReasons($current);
            if ($reasons === []) throw new \RuntimeException('COMMAND_REPAIR_SCAN_CHANGED: current state is no longer invalid.');
            $payload = ['requested_date' => $date, 'recovery_run_id' => (int) $owner->run_id,
                'historical_run_id' => $historical ? (int) $historical->run_id : null,
                'scanned_pointer' => $scanned, 'integrity_reasons' => $reasons, 'operator_reason' => $reason,
                'pre_apply_disclosure' => $expectedPreflight,
                'actual_recovery_config_snapshot_id' => (int) $owner->config_snapshot_id];
            $this->runs->appendEvent($owner, 'FINALIZE', 'OPERATOR_POINTER_REPAIR_STARTED', 'WARN',
                'Explicit linked recovery acquired; invalid current state revalidated.', null, $payload);
            $this->publications->clearCurrentPublicationState($date, $owner);
            if (DB::table('eod_current_publication_pointer')->where('trade_date', $date)->exists()
                || DB::table('eod_publications')->where('trade_date', $date)->where('is_current', 1)->exists()
                || DB::table('eod_runs')->where('trade_date_requested', $date)->where('is_current_publication', 1)->exists()) {
                throw new \RuntimeException('COMMAND_REPAIR_CLEANUP_INCOMPLETE');
            }
            $this->runs->appendEvent($owner, 'FINALIZE', 'OPERATOR_POINTER_REPAIR_COMPLETED', 'WARN',
                'Invalid current state cleared; sealed history preserved and no readability granted.', null, $payload);
            $this->runs->finalize($owner, ['lifecycle_state' => 'COMPLETED', 'terminal_status' => 'HELD',
                'quality_gate_state' => 'BLOCKED', 'publishability_state' => 'NOT_READABLE',
                'final_reason_code' => 'RUN_CURRENT_PUBLICATION_INTEGRITY_REPAIRED']);
            return ['recovery_run_id' => (int) $owner->run_id,
                'recovery_config_snapshot_id' => (int) $owner->config_snapshot_id,
                'pointer_cleared' => true];
        });
    }
}
