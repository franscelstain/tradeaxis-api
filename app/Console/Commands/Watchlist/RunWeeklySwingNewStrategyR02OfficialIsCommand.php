<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingNewStrategyR02OfficialIsEvidenceService;
use Illuminate\Console\Command;

class RunWeeklySwingNewStrategyR02OfficialIsCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-new-strategy-r02-official-is
        {--param-set-id= : Exact R02 DRAFT param_set_id}
        {--from=2023-01-02 : Canonical IS start date}
        {--to=2025-05-21 : Canonical IS end date}
        {--approval-reference= : Exact R02 official-IS approval reference}
        {--operator-approved : Confirm official IS only; never permits OOS, promotion, PLAN, or production}
        {--output=storage/app/watchlist/backtest/ws-new-strategy-r02-official-is.json : Output artifact path}
        {--overwrite : Replace existing local R02 IS artifact}
        {--progress : Print result fields}';

    protected $description = 'Run versioned official IS for one exact new-strategy R02 DRAFT; OOS remains locked unless every canonical IS gate passes.';

    public function handle(): int
    {
        $paramSetId = filter_var($this->option('param-set-id'), FILTER_VALIDATE_INT);
        if ($paramSetId === false || $paramSetId < 1) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=WS_NEW_STRATEGY_R02_PARAM_SET_ID_INVALID');

            return 1;
        }
        $service = $this->laravel->make(WeeklySwingNewStrategyR02OfficialIsEvidenceService::class);
        $result = $service->execute(
            (int) $paramSetId,
            trim((string) $this->option('from')),
            trim((string) $this->option('to')),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach ([
            'run_code','phase_label','status','reason_code','param_set_id','params_hash',
            'hypothesis_code','research_rule_code','research_selection_hash',
            'evidence_pipeline_version','evidence_pipeline_hash','canonical_is_gates_pass',
            'oos_runtime_invoked','oos_rows_before','oos_rows_after','paramset_promoted',
            'plan_run_created','production_ready','next_recommendation','artifact_hash',
        ] as $key) {
            if (! array_key_exists($key, $result)) {
                continue;
            }
            $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
            $this->line($key.'='.$value);
        }

        $evaluation = $result['is_calibration']['evaluations'][0] ?? [];
        $metrics = is_array($evaluation['metrics'] ?? null) ? $evaluation['metrics'] : [];
        foreach ([
            'picks_count','days_covered','avg_ret_net_top','median_ret_net_top',
            'p25_ret_net_top','win_rate_top','month_win_rate_min',
            'month_avg_ret_net_min','period_fail_count',
        ] as $key) {
            if (array_key_exists($key, $metrics)) {
                $this->line('metric_'.$key.'='.(string) $metrics[$key]);
            }
        }

        return ($result['canonical_is_gates_pass'] ?? false) === true ? 0 : 1;
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
