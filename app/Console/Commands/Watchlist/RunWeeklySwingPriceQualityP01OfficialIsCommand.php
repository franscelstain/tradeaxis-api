<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01OfficialIsEvidenceService;
use Illuminate\Console\Command;

class RunWeeklySwingPriceQualityP01OfficialIsCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-price-quality-p01-official-is
        {--param-set-id= : Exact P01 DRAFT param_set_id}
        {--from=2023-01-02 : Canonical IS start date}
        {--to=2025-05-21 : Canonical IS end date}
        {--approval-reference= : Exact P01 Official-IS approval reference}
        {--operator-approved : Confirm Official IS only; no OOS, promotion, PLAN, or production}
        {--output=storage/app/watchlist/backtest/ws-price-quality-p01-official-is.json : Output artifact}
        {--overwrite : Replace an existing local P01 IS artifact}';

    protected $description = 'Run strict versioned Official IS for one exact P01 DRAFT; OOS is neither read nor run.';

    public function handle(): int
    {
        $paramSetId = filter_var(
            $this->option('param-set-id'),
            FILTER_VALIDATE_INT
        );
        if ($paramSetId === false || $paramSetId < 1) {
            $this->line('status=BLOCKED');
            $this->line('reason_code=WS_PRICE_QUALITY_P01_PARAM_SET_ID_INVALID');

            return 1;
        }
        $result = $this->laravel
            ->make(WeeklySwingPriceQualityP01OfficialIsEvidenceService::class)
            ->execute(
                (int) $paramSetId,
                trim((string) $this->option('from')),
                trim((string) $this->option('to')),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'param_set_id', 'row_code',
            'minimum_signal_close_price', 'params_hash',
            'canonical_is_gates_pass', 'oos_runtime_invoked', 'oos_table_read',
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
        $metrics = $result['is_calibration']['evaluations'][0]['metrics'] ?? [];
        foreach ([
            'picks_count', 'days_covered', 'avg_ret_net_top',
            'median_ret_net_top', 'p25_ret_net_top', 'win_rate_top',
            'month_win_rate_min', 'month_avg_ret_net_min', 'period_fail_count',
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
