<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingPriceQualityP01IdentityRepairDraftService;
use Illuminate\Console\Command;

class PersistWeeklySwingPriceQualityP01IdentityRepairDraftCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-price-quality-p01-persist-identity-repair-draft
        {--source=storage/app/watchlist/backtest/ws-price-quality-p01-remediation-official-is.json : Exact invalid-identity eval 218 artifact}
        {--approval-reference= : Exact identity-repair approval reference}
        {--operator-approved : Confirm identity-only DRAFT repair; no strategy change or OOS}
        {--canonical-output=storage/app/watchlist/backtest/ws-price-quality-p01-identity-repair-draft.json : Corrected canonical DRAFT JSON}
        {--output=storage/app/watchlist/backtest/ws-price-quality-p01-identity-repair-draft-artifact.json : Identity-repair artifact}
        {--overwrite : Replace differing local artifacts; persisted identities remain immutable}';

    protected $description = 'Create a new immutable P01 DRAFT with corrected execution identity and unchanged remediation semantics.';

    public function handle(): int
    {
        $result = $this->laravel
            ->make(WeeklySwingPriceQualityP01IdentityRepairDraftService::class)
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
            'run_code', 'status', 'reason_code', 'invalidated_eval_id',
            'bt_param_id', 'param_set_id', 'params_hash', 'eval_model',
            'eval_model_hash', 'implementation_hash', 'persistence_status',
            'strategy_semantics_changed', 'official_is_runtime_invoked',
            'oos_runtime_invoked', 'oos_table_read', 'production_ready',
            'next_recommendation', 'artifact_hash',
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
            === 'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_DRAFT_PERSISTED'
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
