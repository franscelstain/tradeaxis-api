<?php

namespace App\Application\MarketData\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Read-only census of all sealed publications, including superseded history. */
final class MarketDataConfigurationCorpusService
{
    public function report(): array
    {
        $sealed = DB::table('eod_publications as publication')
            ->leftJoin('eod_runs as run', 'run.run_id', '=', 'publication.run_id')
            ->where('publication.seal_state', 'SEALED');
        $unbound = (clone $sealed)->where(function ($query) {
            $query->whereNull('run.config_snapshot_id')->orWhere('run.config_snapshot_id', '<=', 0)
                ->orWhereNull('run.config_hash')->orWhereRaw("TRIM(run.config_hash) = ''");
        });
        return [
            'assessed_at' => Carbon::now('Asia/Jakarta')->toIso8601String(),
            'population' => 'ALL_SEALED_PUBLICATIONS_INCLUDING_SUPERSEDED_HISTORY',
            'sealed_publication_count' => (int) $sealed->count(),
            'config_unbound_publication_count' => (int) $unbound->count(),
            'config_unbound_state' => 'SEALED_PUBLICATION_WITHOUT_RECORDED_RUN_CONFIG_SNAPSHOT_ID_AND_HASH',
            'authority' => 'Platform_Config_Registry_LOCKED.md: Status of artifacts sealed before this gate was enforced',
            'claim_limit' => 'A count is not conformance or replay PASS. No record is deleted, rebound, resealed or retired by this report.',
        ];
    }
}
