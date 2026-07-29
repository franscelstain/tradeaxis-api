<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171FinalFailedNotReadyClosureService;
use Illuminate\Console\Command;

class SealBacktestC171FinalFailedNotReadyClosureCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-seal-final-failed-not-ready-closure
        {--anchor-artifact=storage/app/watchlist/backtest/c171-c01-v3-official-is-paramset-11.json : Exact immutable V3 anchor artifact}
        {--artifact-dir=storage/app/watchlist/backtest : Directory containing exact final official-IS artifacts for paramsets 12-14}
        {--summary-csv=storage/app/watchlist/backtest/c171-final-official-is-summary.csv : Exact final official-IS summary CSV}
        {--approval-reference= : Exact operator approval reference for C171 closure only}
        {--operator-approved : Confirm evidence-only C171 closure; no OOS/promotion/PLAN}
        {--output=storage/app/watchlist/backtest/c171-final-failed-not-ready-closure.json : Closure seal artifact path}
        {--overwrite : Replace a differing local closure file; never mutates database evidence}
        {--progress : Print closure result fields}';

    protected $description = 'Seal C171 as failed/not-ready after all three final bounded candidates fail canonical IS, with no further remediation allowed.';

    public function handle(): int
    {
        $service = $this->laravel->make(WeeklySwingC171FinalFailedNotReadyClosureService::class);
        $result = $service->execute(
            $this->absolutePath(trim((string) $this->option('anchor-artifact'))),
            $this->absolutePath(trim((string) $this->option('artifact-dir'))),
            $this->absolutePath(trim((string) $this->option('summary-csv'))),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'run_code','phase_label','status','reason_code','final_decision','c171_topic_closed',
            'strategy_quality_result','research_information_result','pipeline_version','pipeline_hash',
            'canonical_is_from','canonical_is_to','final_candidate_count','final_passing_candidate_count',
            'database_identity_verified','additional_c171_candidate_catalog_allowed','oos_allowed','c172_allowed',
            'promotion_allowed','plan_allowed','production_ready','official_is_runtime_invoked',
            'oos_runtime_invoked','oos_table_read','paramset_promoted','active_paramset_created',
            'plan_run_created','recommendation_persisted','confirm_mutated','production_activation_executed',
            'eval_count_before','eval_count_after','active_paramset_count_before','active_paramset_count_after',
            'plan_run_count_before','plan_run_count_after','database_mutated','next_recommendation','artifact_hash',
        ] as $key) {
            if (! array_key_exists($key, $result)) {
                continue;
            }
            $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
            $this->line($key.'='.$value);
        }
        foreach (($result['final_candidates'] ?? []) as $candidate) {
            $metrics = $candidate['metrics'] ?? [];
            $this->line('final_candidate='.(string) ($candidate['param_set_id'] ?? '')
                .'|eval_id='.(string) ($candidate['eval_id'] ?? '')
                .'|avg_ret_net='.(string) ($metrics['avg_ret_net_top'] ?? '')
                .'|median_ret_net='.(string) ($metrics['median_ret_net_top'] ?? '')
                .'|p25_ret_net='.(string) ($metrics['p25_ret_net_top'] ?? '')
                .'|period_fail_count='.(string) ($metrics['period_fail_count'] ?? '')
                .'|canonical_is_gates_pass=0');
        }
        if (array_key_exists('write', $result)) {
            $this->line('closure_file_sha1='.(string) ($result['write']['file_sha1'] ?? ''));
        }

        return ($result['status'] ?? '') === WeeklySwingC171FinalFailedNotReadyClosureService::SUCCESS_STATUS ? 0 : 1;
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
