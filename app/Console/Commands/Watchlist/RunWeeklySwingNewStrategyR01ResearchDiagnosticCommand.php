<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingNewStrategyR01ResearchDiagnosticService;
use Illuminate\Console\Command;

class RunWeeklySwingNewStrategyR01ResearchDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-new-strategy-r01-diagnostic
        {--approval-reference= : Exact approval reference for separate read-only strategy research}
        {--operator-approved : Confirm read-only R01 diagnostic execution}
        {--output=storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic.json : Main JSON artifact path}
        {--overwrite : Replace existing R01 output artifacts}
        {--progress : Print result fields}';

    protected $description = 'Register and evaluate three new Weekly Swing research hypotheses from immutable C171 anchor evidence; no paramset, IS, OOS, PLAN, or production mutation.';

    public function handle(): int
    {
        $service = $this->laravel->make(WeeklySwingNewStrategyR01ResearchDiagnosticService::class);
        $result = $service->execute(
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'run_code',
            'phase_label',
            'status',
            'reason_code',
            'source_eval_id',
            'source_param_set_id',
            'candidate_design_allowed_count',
            'draft_paramset_created',
            'official_is_runtime_invoked',
            'oos_runtime_invoked',
            'paramset_promoted',
            'plan_run_created',
            'production_ready',
            'next_recommendation',
            'artifact_hash',
        ] as $key) {
            if (! array_key_exists($key, $result)) {
                continue;
            }
            $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
            $this->line($key.'='.$value);
        }

        return ($result['status'] ?? '') === WeeklySwingNewStrategyR01ResearchDiagnosticService::SUCCESS_STATUS
            ? 0
            : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== ''
            && (substr($path, 0, 1) === '/'
                || substr($path, 0, 2) === '\\\\'
                || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':'))) {
            return $path;
        }

        return base_path($path);
    }
}
