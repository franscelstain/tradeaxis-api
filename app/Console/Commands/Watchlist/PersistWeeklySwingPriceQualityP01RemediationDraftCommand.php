<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01RemediationDraftService;
use Illuminate\Console\Command;

class PersistWeeklySwingPriceQualityP01RemediationDraftCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-price-quality-p01-persist-remediation-draft
        {--source=storage/app/watchlist/backtest/ws-price-quality-p01-c1-official-is.json : Exact failed C1 Official-IS artifact}
        {--approval-reference= : Exact single-remediation approval reference}
        {--operator-approved : Confirm final P01 remediation DRAFT only; no OOS, promotion, PLAN, or production}
        {--canonical-output=storage/app/watchlist/backtest/ws-price-quality-p01-remediation-draft.json : Canonical DRAFT JSON}
        {--output=storage/app/watchlist/backtest/ws-price-quality-p01-remediation-draft-artifact.json : Remediation artifact}
        {--overwrite : Replace differing local artifacts; persisted identity remains immutable}';

    protected $description = 'Persist the one allowed P01 remediation: exact C1 price floor plus fixed -1% close-loss next-open exit.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingPriceQualityP01RemediationDraftService::class)
            ->execute(
                $this->absolutePath(trim((string) $this->option('source'))),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(
                    trim((string) $this->option('canonical-output'))
                ),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'row_code', 'bt_param_id',
            'param_set_id', 'params_hash', 'persistence_status',
            'remediation_rounds_used', 'remediation_rounds_remaining',
            'official_is_runtime_invoked', 'oos_runtime_invoked',
            'oos_table_read', 'production_ready', 'next_recommendation',
            'artifact_hash',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key])
                    ? ($result[$key] ? '1' : '0')
                    : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }
        if (isset($result['error'])) {
            $this->line('error='.(string) $result['error']);
        }

        return ($result['status'] ?? '')
            === 'WS_PRICE_QUALITY_P01_SINGLE_REMEDIATION_DRAFT_PERSISTED'
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
