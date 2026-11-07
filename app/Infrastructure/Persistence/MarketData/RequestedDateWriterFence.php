<?php

namespace App\Infrastructure\Persistence\MarketData;

use App\Models\EodRun;
use Illuminate\Support\Facades\DB;

/** Validation only: acquisition and renewal remain explicit run-owner operations. */
final class RequestedDateWriterFence
{
    public function lock($tradeDate, ?EodRun $actor, bool $isolated = false): void
    {
        if (DB::connection()->transactionLevel() < 1) {
            throw new \LogicException('RUN_OWNER_CONFLICT: writer fence requires the mutation transaction.');
        }
        if (! $actor || ! $actor->run_id || (string) $actor->trade_date_requested !== (string) $tradeDate) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: explicit same-date acting owner token required.');
        }

        // Acquisition uses this same order. Keep the date lock until the protected write commits.
        $fence = DB::table('eod_run_owner_fences')
            ->where('trade_date_requested', $tradeDate)->lockForUpdate()->first();
        if (! $fence || $fence->owner_run_id === null) {
            throw new \RuntimeException('RUN_OWNER_FENCE_MISSING: RUN_OWNER_CONFLICT: explicitly acquired requested-date fence required.');
        }
        RunWriterContext::authenticate($actor, $fence);
        $persisted = DB::table('eod_runs')->where('run_id', $actor->run_id)->lockForUpdate()->first();
        if (! $fence || ! $persisted
            || (int) $fence->owner_run_id !== (int) $actor->run_id
            || $fence->released_at !== null
            || (string) $persisted->trade_date_requested !== (string) $tradeDate
            || ! in_array((string) $persisted->lifecycle_state, ['PENDING', 'RUNNING', 'FINALIZING'], true)
            || ! in_array($persisted->request_mode, ($isolated ? [null, 'full_publish', 'promote', 'correction', 'corpus_reconstruction', 'repair_candidate', 'import_only', 'replay_verify'] : [null, 'full_publish', 'promote', 'correction', 'corpus_reconstruction', 'repair_candidate']), true)
            || (string) $persisted->request_mode !== (string) $actor->request_mode
            || (string) $persisted->source !== (string) $actor->source) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: acting run lost its active requested-date fencing token.');
        }
    }

    public function lockRun($tradeDate, ?EodRun $actor): void
    {
        $this->lock($tradeDate, $actor, true);
    }

    /** Shared staging boundary: the caller keeps this transaction through every compound write. */
    public function lockCandidate($tradeDate, ?EodRun $actor, $publicationId, bool $isolated = false)
    {
        $this->lock($tradeDate, $actor, $isolated);
        $publication = DB::table('eod_publications')->where('publication_id', $publicationId)->lockForUpdate()->first();
        if (! $publication) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: candidate publication not found.');
        }
        if ((string) ($publication->seal_state ?? '') === 'SEALED') {
            throw new \RuntimeException('SEALED_PUBLICATION_IMMUTABLE');
        }
        if ((int) $publication->run_id !== (int) $actor->run_id
            || (string) $publication->trade_date !== (string) $tradeDate) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: candidate/run/date identity mismatch.');
        }
        $pointer = DB::table('eod_current_publication_pointer')->where('publication_id', $publicationId)->lockForUpdate()->first();
        if (!empty($publication->is_current) || $pointer) {
            throw new \RuntimeException('RUN_OWNER_CONFLICT: current publication is not mutable staging.');
        }
        return $publication;
    }
}
