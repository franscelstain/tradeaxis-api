<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService;
use Illuminate\Console\Command;

class RunWeeklySwingBreakoutIntegrityB01OfficialOosCommand extends Command
{
    protected $signature =
        'watchlist:weekly-swing-breakout-integrity-b01-official-oos
        {--identity-review-artifact=storage/app/watchlist/backtest/ws-breakout-integrity-b01-is-identity-review.json : Exact passing B01 IS identity review}
        {--from=2025-05-22 : Locked reserved OOS start}
        {--to=2026-05-29 : Locked reserved OOS end}
        {--approval-reference= : Exact single Official-OOS approval reference}
        {--operator-approved : Confirm one locked OOS run; no retuning, promotion, PLAN, or production}
        {--output=storage/app/watchlist/backtest/ws-breakout-integrity-b01-official-oos.json : Output artifact}
        {--overwrite : Replace an existing local OOS artifact}';

    protected $description =
        'Run one locked B01 Official OOS from the verified IS winner without retuning or promotion.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(
                WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService::class
            )
            ->execute(
                $this->absolutePath(trim((string) $this->option(
                    'identity-review-artifact'
                ))),
                trim((string) $this->option('from')),
                trim((string) $this->option('to')),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'param_set_id', 'bt_param_id',
            'is_eval_id', 'oos_id', 'params_hash',
            'is_evidence_manifest_hash', 'official_oos_gates_pass',
            'oos_runtime_invoked', 'oos_table_read', 'oos_mutated',
            'paramset_promoted', 'plan_run_created', 'production_ready',
            'next_recommendation', 'artifact_hash',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key])
                    ? ($result[$key] ? '1' : '0')
                    : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }
        foreach ([
            'picks_count', 'days_covered', 'avg_ret_net_top',
            'median_ret_net_top', 'p25_ret_net_top', 'win_rate_top',
            'month_win_rate_min', 'month_avg_ret_net_min',
        ] as $key) {
            if (array_key_exists($key, $result['oos_metrics'] ?? [])) {
                $this->line(
                    'metric_'.$key.'='.(string) $result['oos_metrics'][$key]
                );
            }
        }

        return ($result['official_oos_gates_pass'] ?? false) === true ? 0 : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/'
            || substr($path, 0, 2) === '\\\\'
            || (strlen($path) >= 3
                && ctype_alpha($path[0])
                && $path[1] === ':'))) {
            return $path;
        }

        return base_path($path);
    }
}
