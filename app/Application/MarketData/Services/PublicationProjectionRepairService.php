<?php

namespace App\Application\MarketData\Services;

use App\Infrastructure\Persistence\MarketData\EodArtifactRepository;
use App\Infrastructure\Persistence\MarketData\EodPublicationRepository;
use Illuminate\Support\Facades\DB;

/**
 * Controlled repair of rebuildable current projections from immutable history selected by the
 * governed current-publication pointer.
 *
 * History, publication rows, pointer state, and run identity are read-only. Repair mutates only
 * eod_bars/eod_indicators/eod_eligibility and commits only after independent reconciliation PASS.
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
        $before = $this->reconciliation->reconcileTradeDate($tradeDate);
        $repairability = ($before['reconciliation_state'] ?? '') === 'PASS'
            ? 'NO_REPAIR_REQUIRED'
            : 'REPAIRABLE_FROM_IMMUTABLE_HISTORY';

        return [
            'repairability_state' => $repairability,
            'trade_date' => $tradeDate,
            'publication_id' => (int) $context['publication']->publication_id,
            'run_id' => (int) $context['publication']->run_id,
            'snapshot_counts' => $context['snapshot_counts'],
            'reconciliation' => $before,
        ];
    }

    public function repairTradeDate(string $tradeDate): array
    {
        $context = $this->validatedRepairContext($tradeDate);
        $publication = $context['publication'];
        $before = $this->reconciliation->reconcileTradeDate($tradeDate);

        if (($before['reconciliation_state'] ?? '') === 'PASS') {
            return [
                'repair_state' => 'NO_CHANGE',
                'trade_date' => $tradeDate,
                'publication_id' => (int) $publication->publication_id,
                'run_id' => (int) $publication->run_id,
                'before' => $before,
                'after' => $before,
            ];
        }

        return DB::transaction(function () use ($tradeDate, $publication, $before) {
            $this->artifacts->promotePublicationHistoryToCurrent(
                $tradeDate,
                (int) $publication->publication_id,
                (int) $publication->run_id
            );

            $after = $this->reconciliation->reconcileTradeDate($tradeDate);
            if (($after['reconciliation_state'] ?? '') !== 'PASS') {
                throw new \RuntimeException($this->repairFailureMessage($after));
            }

            return [
                'repair_state' => 'REBUILT_AND_VERIFIED',
                'trade_date' => $tradeDate,
                'publication_id' => (int) $publication->publication_id,
                'run_id' => (int) $publication->run_id,
                'before' => $before,
                'after' => $after,
            ];
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
                'run_id', 'trade_date_requested', 'bars_rows_written',
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
