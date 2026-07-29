<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171FinalBoundedRemediationDraftCatalogService;
use Illuminate\Console\Command;

class PersistBacktestC171FinalBoundedRemediationDraftCatalogCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-persist-final-bounded-remediation-draft-catalog
        {--source-eval-id=204 : Exact immutable V3 anchor eval_id}
        {--source-param-set-id=11 : Exact immutable V3 anchor DRAFT param_set_id}
        {--artifact-dir=storage/app/watchlist/backtest : Directory containing six exact C01 V3 official IS JSON artifacts}
        {--summary-csv=storage/app/watchlist/backtest/c171-c01-v3-official-is-summary.csv : Exact C01 V3 summary CSV}
        {--approval-reference= : Exact operator approval reference for final DRAFT-only persistence}
        {--operator-approved : Confirm final catalog persistence only; no IS/OOS/promotion}
        {--output-dir=storage/app/watchlist/backtest/c171-final-bounded-remediation-draft-catalog : Directory for three canonical DRAFT JSON files}
        {--output=storage/app/watchlist/backtest/c171-final-bounded-remediation-draft-catalog.json : Summary artifact path}
        {--overwrite : Replace differing local output artifacts; never mutates an existing paramset identity}
        {--progress : Print result fields}';

    protected $description = 'Persist the one-and-only final bounded C171 remediation catalog and three immutable DRAFTs while locking the no-pass closure rule.';

    public function handle(): int
    {
        $sourceEvalId = filter_var($this->option('source-eval-id'), FILTER_VALIDATE_INT);
        $sourceParamSetId = filter_var($this->option('source-param-set-id'), FILTER_VALIDATE_INT);
        if ($sourceEvalId === false || $sourceParamSetId === false) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=C171_FINAL_BOUNDED_REMEDIATION_SOURCE_IDENTITY_INVALID');

            return 1;
        }

        $service = $this->laravel->make(WeeklySwingC171FinalBoundedRemediationDraftCatalogService::class);
        $result = $service->execute(
            (int) $sourceEvalId,
            (int) $sourceParamSetId,
            $this->absolutePath(trim((string) $this->option('artifact-dir'))),
            $this->absolutePath(trim((string) $this->option('summary-csv'))),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output-dir'))),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'run_code','phase_label','status','reason_code','source_eval_id','source_param_set_id',
            'source_params_hash','source_pipeline_version','source_pipeline_hash','source_evidence_manifest_hash',
            'final_decision','closure_rule_if_no_pass','closure_rule_if_pass','additional_c171_candidate_catalog_allowed',
            'catalog_code','catalog_version','catalog_hash','catalog_row_count','candidate_hash_contract',
            'candidate_hash_manifest_hash','draft_paramset_created_count','draft_paramset_idempotent_count',
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

        return ($result['status'] ?? '') === 'C171_FINAL_BOUNDED_REMEDIATION_CATALOG_PERSISTED_CLOSURE_RULE_LOCKED'
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
