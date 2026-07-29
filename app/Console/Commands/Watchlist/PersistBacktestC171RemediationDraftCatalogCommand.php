<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171RemediationDraftCatalogService;
use Illuminate\Console\Command;

class PersistBacktestC171RemediationDraftCatalogCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-persist-remediation-draft-catalog
        {--source-eval-id=188 : Exact immutable failed official IS eval_id}
        {--source-param-set-id=1 : Exact immutable baseline DRAFT param_set_id}
        {--diagnostic-artifact=storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json : Exact completed C171 diagnostic JSON}
        {--approval-reference= : Exact operator approval reference for DRAFT-only persistence}
        {--operator-approved : Confirm catalog and five DRAFT persistence only; no IS/OOS/promotion}
        {--output-dir=storage/app/watchlist/backtest/c171-remediation-draft-catalog : Directory for five canonical DRAFT JSON files}
        {--output=storage/app/watchlist/backtest/c171-remediation-draft-catalog.json : Summary artifact path}
        {--overwrite : Replace differing local output artifacts; never mutates an existing paramset identity}
        {--progress : Print result fields}';

    protected $description = 'Persist the immutable five-row C171 real-IS remediation catalog and five new DRAFT paramsets; never runs IS/OOS, promotion, PLAN, or production activation.';

    public function handle(): int
    {
        $sourceEvalId = filter_var($this->option('source-eval-id'), FILTER_VALIDATE_INT);
        $sourceParamSetId = filter_var($this->option('source-param-set-id'), FILTER_VALIDATE_INT);
        if ($sourceEvalId === false || $sourceParamSetId === false) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=C171_REMEDIATION_DRAFT_CATALOG_SOURCE_IDENTITY_INVALID');

            return 1;
        }

        $service = $this->laravel->make(WeeklySwingC171RemediationDraftCatalogService::class);
        $result = $service->execute(
            (int) $sourceEvalId,
            (int) $sourceParamSetId,
            $this->absolutePath(trim((string) $this->option('diagnostic-artifact'))),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output-dir'))),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'run_code','phase_label','status','reason_code','source_eval_id','source_param_set_id',
            'source_params_hash','catalog_code','catalog_version','catalog_hash','catalog_row_count',
            'candidate_hash_contract','candidate_hash_manifest_hash','draft_paramset_created_count',
            'draft_paramset_idempotent_count','official_is_runtime_invoked',
            'oos_runtime_invoked','paramset_promoted','plan_run_created','production_ready',
            'next_recommendation','artifact_hash',
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
        foreach (($result['candidate_hash_manifest'] ?? []) as $candidateHash) {
            $this->line('candidate_hash='.(string) ($candidateHash['row_code'] ?? '')
                .'|params_hash='.(string) ($candidateHash['params_hash'] ?? ''));
        }
        foreach (($result['drafts'] ?? []) as $draft) {
            $this->line('draft='.(string) ($draft['row_code'] ?? '')
                .'|param_set_id='.(string) ($draft['param_set_id'] ?? '')
                .'|bt_param_id='.(string) ($draft['bt_param_id'] ?? '')
                .'|params_hash='.(string) ($draft['params_hash'] ?? '')
                .'|persistence_status='.(string) ($draft['persistence_status'] ?? ''));
        }

        return ($result['status'] ?? '') === 'C171_IMMUTABLE_REMEDIATION_DRAFT_CATALOG_PERSISTED' ? 0 : 1;
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
