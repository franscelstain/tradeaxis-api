<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingParamsetPromotionService;
use Illuminate\Console\Command;

class PromoteWeeklySwingParamsetCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-paramset-promote
        {--param-set-id= : Exact DRAFT watchlist_param_sets.param_set_id}
        {--bt-param-id= : Exact bound watchlist_bt_param_grid.param_id}
        {--oos-id= : Exact passing watchlist_bt_oos_eval_ws.oos_id}';

    protected $description = 'Promote one validated Weekly Swing DRAFT only when its exact persisted IS/OOS proof passes every canonical gate.';

    public function handle(): int
    {
        $paramSetId = filter_var($this->option('param-set-id'), FILTER_VALIDATE_INT);
        $btParamId = filter_var($this->option('bt-param-id'), FILTER_VALIDATE_INT);
        $oosId = filter_var($this->option('oos-id'), FILTER_VALIDATE_INT);
        if ($paramSetId === false || $paramSetId < 1 || $btParamId === false || $btParamId < 1 || $oosId === false || $oosId < 1) {
            return $this->blocked('WS_PARAMSET_PROMOTION_ARGUMENT_INVALID');
        }

        $service = $this->laravel->make(WeeklySwingParamsetPromotionService::class);
        $result = $service->execute((int) $paramSetId, (int) $btParamId, (int) $oosId);
        foreach ([
            'status', 'reason_code', 'param_set_id', 'bt_param_id', 'oos_id',
            'deprecated_active_count', 'production_ready',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }

        return ($result['status'] ?? null) === 'PROMOTED' ? 0 : 1;
    }

    private function blocked(string $reasonCode): int
    {
        $this->error('status=BLOCKED');
        $this->line('reason_code='.$reasonCode);
        $this->line('production_ready=0');
        return 1;
    }
}
