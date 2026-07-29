<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingNewStrategyR02DraftCatalogService;
use Illuminate\Console\Command;

class PersistWeeklySwingNewStrategyR02DraftCatalogCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-new-strategy-r02-persist-draft-catalog
        {--r01-artifact=storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic.json : Exact completed R01 artifact}
        {--r01-hypothesis-lock=storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic-hypothesis-lock.json : Exact R01 hypothesis lock}
        {--c171-closure=storage/app/watchlist/backtest/c171-final-failed-not-ready-closure.json : Exact sealed C171 closure}
        {--approval-reference= : Exact R02 DRAFT-catalog approval reference}
        {--operator-approved : Confirm three DRAFTs only; no IS, OOS, promotion, PLAN, or production}
        {--output-dir=storage/app/watchlist/backtest/ws-new-strategy-r02-draft-catalog : Canonical candidate JSON directory}
        {--output=storage/app/watchlist/backtest/ws-new-strategy-r02-draft-catalog.json : R02 catalog artifact}
        {--overwrite : Replace differing local output artifacts; never mutates an existing paramset identity}
        {--progress : Print result fields}';

    protected $description = 'Persist exactly three immutable new-strategy R02 one-idea DRAFT candidates from the locked R01 evidence; official IS and OOS are not run.';

    public function handle(): int
    {
        $service = $this->laravel->make(WeeklySwingNewStrategyR02DraftCatalogService::class);
        $result = $service->execute(
            $this->absolutePath(trim((string) $this->option('r01-artifact'))),
            $this->absolutePath(trim((string) $this->option('r01-hypothesis-lock'))),
            $this->absolutePath(trim((string) $this->option('c171-closure'))),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output-dir'))),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach ([
            'run_code','phase_label','status','reason_code','source_eval_id','source_param_set_id',
            'source_params_hash','r01_artifact_hash','catalog_code','catalog_version','catalog_hash',
            'catalog_row_count','max_candidate_count','one_primary_idea_per_candidate',
            'canonical_gates_changed','draft_paramset_created_count','draft_paramset_idempotent_count',
            'official_is_runtime_invoked','oos_runtime_invoked','oos_table_read','paramset_promoted',
            'plan_run_created','production_ready','next_recommendation','artifact_hash',
        ] as $key) {
            if (! array_key_exists($key, $result)) {
                continue;
            }
            $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
            $this->line($key.'='.$value);
        }
        if (array_key_exists('error', $result)) {
            $this->line('error='.(string) $result['error']);
        }
        foreach (($result['drafts'] ?? []) as $draft) {
            $this->line('draft='.(string) ($draft['row_code'] ?? '')
                .'|hypothesis='.(string) ($draft['hypothesis_code'] ?? '')
                .'|param_set_id='.(string) ($draft['param_set_id'] ?? '')
                .'|bt_param_id='.(string) ($draft['bt_param_id'] ?? '')
                .'|params_hash='.(string) ($draft['params_hash'] ?? '')
                .'|persistence_status='.(string) ($draft['persistence_status'] ?? ''));
        }

        return ($result['status'] ?? '') === 'WS_NEW_STRATEGY_R02_THREE_MINIMAL_DRAFTS_PERSISTED'
            ? 0
            : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/'
            || substr($path, 0, 2) === '\\\\'
            || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':'))) {
            return $path;
        }

        return base_path($path);
    }
}
