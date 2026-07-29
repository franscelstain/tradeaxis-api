<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingTailRiskS01RemediationDraftService;
use Illuminate\Console\Command;

class PersistWeeklySwingTailRiskS01RemediationDraftCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-tail-risk-s01-persist-remediation-draft
        {--source-artifact=storage/app/watchlist/backtest/ws-tail-risk-s01-official-is-paramset-20.json : Exact failed H1 artifact}
        {--approval-reference= : Exact single-remediation approval reference}
        {--operator-approved : Confirm one final DRAFT only; no OOS or production action}
        {--canonical-output=storage/app/watchlist/backtest/ws-tail-risk-s01-remediation-draft.json : Canonical paramset}
        {--output=storage/app/watchlist/backtest/ws-tail-risk-s01-remediation-draft-artifact.json : Remediation artifact}
        {--overwrite : Replace differing local artifacts}';

    protected $description = 'Persist the single allowed S01 remediation DRAFT: H1 selection plus fixed -1% close-loss next-open containment.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingTailRiskS01RemediationDraftService::class)
            ->execute(
                $this->absolutePath(trim((string) $this->option('source-artifact'))),
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('canonical-output'))),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        foreach ([
            'run_code', 'status', 'reason_code', 'catalog_code', 'catalog_hash',
            'row_code', 'bt_param_id', 'param_set_id', 'params_hash',
            'remediation_rounds_used', 'remediation_rounds_remaining',
            'official_is_runtime_invoked', 'oos_runtime_invoked',
            'production_ready', 'next_recommendation', 'artifact_hash',
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
            === 'WS_TAIL_RISK_S01_SINGLE_REMEDIATION_DRAFT_PERSISTED'
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
