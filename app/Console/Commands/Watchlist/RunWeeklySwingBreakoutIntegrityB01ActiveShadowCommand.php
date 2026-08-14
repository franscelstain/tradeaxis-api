<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingBreakoutIntegrityB01ActiveShadowService;
use Illuminate\Console\Command;

class RunWeeklySwingBreakoutIntegrityB01ActiveShadowCommand extends Command
{
    protected $signature =
        'watchlist:weekly-swing-breakout-integrity-b01-active-shadow
        {--trade-date=2026-07-28 : Exact preregistered shadow trade date}
        {--promotion-review=storage/app/watchlist/backtest/ws-breakout-integrity-b01-promotion-readiness-review.json : Exact passing promotion-readiness artifact}
        {--promotion-log=storage/app/watchlist/backtest/ws-breakout-integrity-b01-promotion.stdout.log : Exact successful canonical promotion log}
        {--approval-reference= : Exact active-shadow approval reference}
        {--operator-approved : Confirm artifact-only ACTIVE shadow execution}
        {--runtime-output=storage/app/watchlist/runtime/ws-breakout-integrity-b01-active-shadow-2026-07-28.json : Non-official runtime output}
        {--output=storage/app/watchlist/backtest/ws-breakout-integrity-b01-active-shadow-review.json : Shadow review artifact}
        {--engineering-retry-reference= : Audit-only reference for an identity-preserving technical retry}
        {--overwrite-runtime : Replace an existing local shadow runtime artifact}
        {--overwrite : Replace an existing local shadow review artifact}';

    protected $description =
        'Execute B01 ACTIVE as a non-publishing shadow using one locked readable date and prove no PLAN/CONFIRM mutation.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingBreakoutIntegrityB01ActiveShadowService::class)
            ->execute(
                trim((string) $this->option('trade-date')),
                $this->absolutePath(
                    trim((string) $this->option('promotion-review'))
                ),
                $this->absolutePath(
                    trim((string) $this->option('promotion-log'))
                ),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(
                    trim((string) $this->option('runtime-output'))
                ),
                $this->absolutePath(trim((string) $this->option('output'))),
                [
                    'overwrite_runtime' =>
                        (bool) $this->option('overwrite-runtime'),
                    'overwrite' => (bool) $this->option('overwrite'),
                    'engineering_retry_reference' => trim(
                        (string) $this->option(
                            'engineering-retry-reference'
                        )
                    ),
                ]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'param_set_id',
            'paramset_status', 'bt_param_id', 'is_eval_id', 'oos_id',
            'canonical_params_hash', 'executable_runtime_paramset_hash',
            'active_shadow_pass', 'active_paramset_consumed',
            'plan_run_created', 'plan_item_created', 'confirm_mutated',
            'official_output_published', 'production_ready',
            'next_recommendation', 'artifact_hash',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key])
                    ? ($result[$key] ? '1' : '0')
                    : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }
        if (is_array($result['runtime_output'] ?? null)) {
            foreach ([
                'status', 'file_sha1', 'output_hash', 'watchlist_tickers',
                'summary', 'source_lineage',
            ] as $key) {
                if (array_key_exists($key, $result['runtime_output'])) {
                    $value = is_array($result['runtime_output'][$key])
                        ? json_encode(
                            $result['runtime_output'][$key],
                            JSON_UNESCAPED_SLASHES
                                | JSON_UNESCAPED_UNICODE
                        )
                        : (string) $result['runtime_output'][$key];
                    $this->line('runtime_'.$key.'='.$value);
                }
            }
        }

        return ($result['active_shadow_pass'] ?? false) === true ? 0 : 1;
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
