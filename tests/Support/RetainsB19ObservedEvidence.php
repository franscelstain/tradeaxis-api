<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

/** Optional raw state retention; never changes an expected value or database row. */
trait RetainsB19ObservedEvidence
{
    private function retainObservedWorld(string $label, array $runIds, array $report = []): void
    {
        $directory = getenv('B19_E049_STATE_DIR');
        if (! $directory) return;
        if (! is_dir($directory)) mkdir($directory, 0775, true);
        $runs = DB::table('eod_runs')->whereIn('run_id', $runIds)->orderBy('run_id')->get();
        $publications = DB::table('eod_publications')->whereIn('run_id', $runIds)->orderBy('publication_id')->get();
        $dates = $runs->pluck('trade_date_requested')->unique()->all();
        $payload = [
            'database' => DB::connection()->getDatabaseName(), 'engine' => DB::selectOne('SELECT VERSION() AS version')->version,
            'report' => $report, 'runs' => $runs, 'publications' => $publications,
            'date_fences' => DB::table('eod_run_owner_fences')->whereIn('trade_date_requested', $dates)->get(),
            'observer_fences' => DB::table('eod_observer_fences')->whereIn('target_run_id', $runIds)->get(),
            'events' => DB::table('eod_run_events')->whereIn('run_id', $runIds)->orderBy('event_id')->get(),
            'producer_source_inputs' => DB::table('md_run_input_captures')->whereIn('run_id', $runIds)
                ->where('component_key', 'source_observations')->orderBy('input_capture_id')->get(),
            'observations' => DB::table('md_source_observations')->whereIn('run_id', $runIds)
                ->orderBy('source_observation_id')->get(['source_observation_id', 'run_id', 'parent_observation_id',
                    'payload_hash', 'acquired_at', 'outcome_state', 'validation_state', 'reason_code']),
            'canonical_bars' => DB::table('eod_bars')->whereIn('trade_date', $dates)->orderBy('trade_date')->orderBy('ticker_id')->get(),
            'historical_bars' => DB::table('eod_bars_history')->whereIn('publication_id', $publications->pluck('publication_id')->all())
                ->orderBy('publication_id')->orderBy('ticker_id')->get(),
            'pointers' => DB::table('eod_current_publication_pointer')->whereIn('trade_date', $dates)->orderBy('trade_date')->get(),
        ];
        $filename = preg_replace('/[^A-Za-z0-9_.-]/', '_', $label);
        if (strlen($filename) > 90) $filename = substr($filename, 0, 65).'_'.substr(hash('sha256', $label), 0, 24);
        if ($filename !== $label) $payload['retention_label'] = $label;
        file_put_contents($directory.'/'.$filename.'.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
