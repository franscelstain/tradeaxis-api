<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;

/**
 * Controlled repair of rebuildable current projections from immutable history selected by the
 * governed current-publication pointer.
 *
 * Historical run, publication rows and pointer are read-only. Repair acquires a distinct recovery
 * owner and observational audit, rebuilds live projections, and commits only after reconciliation.
 */
class PublicationProjectionRepairService
{
    private $publications;
    private $artifacts;
    private $reconciliation;

    public function __construct(
        EodPublicationRepository $publications,
        EodArtifactRepository $artifacts,
        PublicationProjectionReconciliationService $reconciliation
    ) {
        $this->publications = $publications;
        $this->artifacts = $artifacts;
        $this->reconciliation = $reconciliation;
    }

    public function inspectTradeDate(string $tradeDate): array
    {
        $context = $this->validatedRepairContext($tradeDate);
        $before = $this->reconciliation->inspectTradeDate($tradeDate);
        $runConfigId = (int) ($context['run']->config_snapshot_id ?? 0);
        $publicationConfigId = (int) ($context['publication']->config_snapshot_id ?? 0);
        $configHash = $runConfigId > 0 && $runConfigId === $publicationConfigId
            ? DB::table('md_config_snapshots')->where('config_snapshot_id', $runConfigId)->value('config_hash')
            : null;
        $repairability = ($before['reconciliation_state'] ?? '') === 'PASS'
            ? 'NO_REPAIR_REQUIRED'
            : 'REPAIRABLE_FROM_IMMUTABLE_HISTORY';

        return [
            'repairability_state' => $repairability,
            'trade_date' => $tradeDate,
            'publication_id' => (int) $context['publication']->publication_id,
            'run_id' => (int) $context['publication']->run_id,
            'config_snapshot_id' => $runConfigId > 0 && $runConfigId === $publicationConfigId ? $runConfigId : null,
            'config_hash' => $configHash,
            'snapshot_counts' => $context['snapshot_counts'],
            'reconciliation' => $before,
        ];
    }

    public function repairTradeDate(string $tradeDate, string $reason = '', ?array $expectedPreflight = null): array
    {
        $this->assertDate($tradeDate);
        if (trim($reason) === '') throw new \RuntimeException('PROJECTION_REPAIR_REASON_REQUIRED');
        return DB::transaction(function () use ($tradeDate, $reason, $expectedPreflight) {
            // Same date-first order as acquisition; a scan is not authority.
            DB::table('eod_run_owner_fences')->where('trade_date_requested', $tradeDate)->lockForUpdate()->first();
            $context = $this->validatedRepairContext($tradeDate);
            $publication = $context['publication'];
            if ($expectedPreflight !== null) {
                $runConfigId = (int) ($context['run']->config_snapshot_id ?? 0);
                $publicationConfigId = (int) ($publication->config_snapshot_id ?? 0);
                $configHash = $runConfigId > 0 && $runConfigId === $publicationConfigId
                    ? DB::table('md_config_snapshots')->where('config_snapshot_id', $runConfigId)->value('config_hash')
                    : null;
                if ((string) ($expectedPreflight['trade_date'] ?? '') !== $tradeDate
                    || (int) ($expectedPreflight['publication_id'] ?? 0) !== (int) $publication->publication_id
                    || (int) ($expectedPreflight['run_id'] ?? 0) !== (int) $publication->run_id
                    || (int) ($expectedPreflight['config_snapshot_id'] ?? 0) !== $runConfigId
                    || (string) ($expectedPreflight['config_hash'] ?? '') !== (string) $configHash
                    || ($expectedPreflight['snapshot_counts'] ?? null) !== $context['snapshot_counts']) {
                    throw new \RuntimeException('PROJECTION_REPAIR_PREFLIGHT_CHANGED');
                }
            }
            $before = $this->reconciliation->reconcileTradeDate($tradeDate);
            $result = ['trade_date' => $tradeDate, 'publication_id' => (int) $publication->publication_id,
                'run_id' => (int) $publication->run_id, 'before' => $before];
            if (($before['reconciliation_state'] ?? '') === 'PASS') {
                return $result + ['repair_state' => 'NO_CHANGE', 'after' => $before];
            }
            $historical = \App\Models\EodRun::findOrFail($publication->run_id);
            $runs = new \App\Infrastructure\Persistence\MarketData\EodRunRepository();
            $owner = $runs->acquireLinkedRecoveryOwner($tradeDate, (string) $historical->source, (int) $historical->run_id);
            $current = $this->validatedRepairContext($tradeDate);
            if ((int) $current['publication']->publication_id !== (int) $publication->publication_id
                || $current['snapshot_counts'] !== $context['snapshot_counts']) {
                throw new \RuntimeException('PROJECTION_REPAIR_SOURCE_CHANGED');
            }
            $audit = ['requested_date' => $tradeDate, 'historical_run_id' => (int) $historical->run_id,
                'source_publication_id' => (int) $publication->publication_id,
                'recovery_run_id' => (int) $owner->run_id, 'operator_reason' => $reason];
            $runs->appendEvent($owner, 'FINALIZE', 'PROJECTION_REPAIR_STARTED', 'WARN',
                'Linked recovery acquired for pointer-selected projection rebuild.', null, $audit);
            $this->artifacts->repairCurrentProjectionFromHistory($tradeDate, (int) $publication->publication_id, $owner);
            $after = $this->reconciliation->reconcileTradeDate($tradeDate);
            if (($after['reconciliation_state'] ?? '') !== 'PASS') {
                throw new \RuntimeException($this->repairFailureMessage($after));
            }
            $runs->appendEvent($owner, 'FINALIZE', 'PROJECTION_REPAIR_COMPLETED', 'INFO',
                'Projection verified against unchanged pointer-selected history.', null, $audit);
            $runs->finalize($owner, ['lifecycle_state' => 'COMPLETED', 'terminal_status' => 'HELD',
                'quality_gate_state' => 'BLOCKED', 'publishability_state' => 'NOT_READABLE',
                'final_reason_code' => 'RUN_PROJECTION_REPAIRED']);
            return $result + ['repair_state' => 'REBUILT_AND_VERIFIED', 'recovery_run_id' => (int) $owner->run_id, 'after' => $after];
        });
    }

    private function validatedRepairContext(string $tradeDate): array
    {
        $this->assertDate($tradeDate);

        $publication = $this->publications->resolveCurrentReadablePublicationForTradeDate($tradeDate);
        if (! $publication) {
            throw new \RuntimeException('PROJECTION_REPAIR_CURRENT_PUBLICATION_UNRESOLVED');
        }

        $run = DB::table('eod_runs')
            ->where('run_id', (int) $publication->run_id)
            ->first([
                'run_id', 'trade_date_requested', 'config_snapshot_id', 'bars_rows_written',
                'indicators_rows_written', 'eligibility_rows_written',
            ]);

        if (! $run || (string) ($run->trade_date_requested ?? '') !== $tradeDate) {
            throw new \RuntimeException('PROJECTION_REPAIR_RUN_IDENTITY_INVALID');
        }

        $expectedCounts = [
            'bars' => (int) ($run->bars_rows_written ?? 0),
            'indicators' => (int) ($run->indicators_rows_written ?? 0),
            'eligibility' => (int) ($run->eligibility_rows_written ?? 0),
        ];

        $snapshotCounts = $this->artifacts->assertPublicationSnapshotComplete(
            $tradeDate,
            (int) $publication->publication_id,
            $expectedCounts
        );

        $this->assertImmutableHistoryRunBinding(
            $tradeDate,
            (int) $publication->publication_id,
            (int) $publication->run_id
        );

        return [
            'publication' => $publication,
            'run' => $run,
            'snapshot_counts' => $snapshotCounts,
        ];
    }

    private function assertImmutableHistoryRunBinding(string $tradeDate, int $publicationId, int $runId): void
    {
        foreach (['eod_bars_history', 'eod_indicators_history', 'eod_eligibility_history'] as $table) {
            $invalid = (int) DB::table($table)
                ->where('trade_date', $tradeDate)
                ->where('publication_id', $publicationId)
                ->where(function ($query) use ($runId) {
                    $query->whereNull('run_id')->orWhere('run_id', '<>', $runId);
                })
                ->count();

            if ($invalid > 0) {
                throw new \RuntimeException(
                    'PROJECTION_REPAIR_HISTORY_IDENTITY_INVALID: table='.$table
                    .' publication_id='.$publicationId
                    .' run_id='.$runId
                    .' invalid_rows='.$invalid
                );
            }
        }
    }

    private function repairFailureMessage(array $after): string
    {
        $fields = [
            'mismatch_count',
            'bars_missing_history_count', 'bars_missing_projection_count', 'bars_value_mismatch_count',
            'indicators_missing_history_count', 'indicators_missing_projection_count', 'indicators_value_mismatch_count',
            'eligibility_missing_history_count', 'eligibility_missing_projection_count', 'eligibility_value_mismatch_count',
        ];
        $parts = [];
        foreach ($fields as $field) {
            $parts[] = $field.'='.(string) ($after[$field] ?? '');
        }
        $sample = trim((string) ($after['mismatch_sample_json'] ?? ''));
        if ($sample !== '') {
            $parts[] = 'mismatch_sample='.$sample;
        }

        return 'PROJECTION_REPAIR_RECONCILIATION_FAILED: '.implode(' ', $parts);
    }

    private function assertDate(string $tradeDate): void
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tradeDate)) {
            throw new \InvalidArgumentException('PROJECTION_REPAIR_TRADE_DATE_INVALID');
        }
    }
}
