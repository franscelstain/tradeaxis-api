<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingNewStrategyR02RemediationDraftService;
use Illuminate\Console\Command;

class PersistWeeklySwingNewStrategyR02RemediationDraftCommand extends Command
{
    protected $signature = 'watchlist:weekly-swing-new-strategy-r02-persist-remediation-draft
        {--source-is=storage/app/watchlist/backtest/ws-new-strategy-r02-official-is-paramset-16.json : Exact failed H2 Official IS artifact}
        {--approval-reference= : Exact single-remediation DRAFT approval reference}
        {--operator-approved : Confirm exactly one remediation DRAFT; no IS, OOS, promotion, PLAN, or production}
        {--canonical-output=storage/app/watchlist/backtest/ws-new-strategy-r02-remediation-draft.json : Canonical remediation paramset JSON}
        {--output=storage/app/watchlist/backtest/ws-new-strategy-r02-remediation-catalog.json : Remediation lock artifact}
        {--overwrite : Replace differing local output artifacts}
        {--progress : Print result fields}';

    protected $description = 'Persist the single allowed R02 remediation from failed H2 IS with a fixed non-lookahead sequential exit rule; OOS is not run.';

    public function handle(): int
    {
        $service = $this->laravel->make(WeeklySwingNewStrategyR02RemediationDraftService::class);
        $result = $service->execute(
            $this->absolutePath(trim((string) $this->option('source-is'))),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('canonical-output'))),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code',
            'source_param_set_id', 'source_eval_id', 'source_params_hash',
            'source_official_is_artifact_hash', 'catalog_code', 'catalog_version',
            'catalog_hash', 'catalog_row_count', 'remediation_count',
            'max_remediation_count', 'selection_unchanged_from_h2',
            'one_primary_exit_idea', 'fixed_execution_before_entry',
            'future_derived_route_used', 'canonical_gates_changed',
            'param_set_id', 'bt_param_id', 'params_hash', 'paramset_status',
            'persistence_status', 'official_is_runtime_invoked', 'oos_runtime_invoked',
            'oos_table_read', 'paramset_promoted', 'plan_run_created',
            'production_ready', 'next_recommendation', 'artifact_hash',
        ] as $key) {
            if (! array_key_exists($key, $result)) {
                continue;
            }
            $value = is_bool($result[$key])
                ? ($result[$key] ? '1' : '0')
                : (string) $result[$key];
            $this->line($key.'='.$value);
        }
        if (array_key_exists('error', $result)) {
            $this->line('error='.(string) $result['error']);
        }

        return ($result['status'] ?? '')
            === 'WS_NEW_STRATEGY_R02_SINGLE_REMEDIATION_DRAFT_PERSISTED'
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
