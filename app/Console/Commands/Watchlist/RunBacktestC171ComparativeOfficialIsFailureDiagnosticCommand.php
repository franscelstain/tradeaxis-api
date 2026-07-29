<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC171ComparativeOfficialIsFailureDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-comparative-official-is-failure-diagnostic
        {--artifact-dir=storage/app/watchlist/backtest : Directory containing immutable official IS JSON artifacts for eval_id 188-193}
        {--approval-reference= : Operator approval reference for read-only comparative diagnostic}
        {--operator-approved : Confirm read-only diagnostic replay; never permits new DRAFT, OOS, promotion, PLAN, or production mutation}
        {--output=storage/app/watchlist/backtest/c171-comparative-official-is-failure-diagnostic.json : Main diagnostic JSON artifact path}
        {--overwrite : Replace existing comparative diagnostic artifacts}
        {--progress : Print result fields}';

    protected $description = 'Compare immutable failed C171 official IS evidence for eval_id 188-193 and lock decision-time R2 hypotheses; read-only and fail-closed.';

    public function handle(): int
    {
        $service = $this->laravel->make(WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService::class);
        try {
            $result = $service->execute(
                trim((string) $this->option('approval-reference')),
                (bool) $this->option('operator-approved'),
                $this->absolutePath(trim((string) $this->option('artifact-dir'))),
                $this->absolutePath(trim((string) $this->option('output'))),
                ['overwrite' => (bool) $this->option('overwrite')]
            );
        } catch (\Throwable $exception) {
            $result = [
                'run_code' => WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService::RUN_CODE,
                'phase_label' => WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService::RUN_CODE,
                'status' => 'BLOCKED',
                'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_EXECUTION_FAILED',
                'error' => $exception->getMessage(),
                'r2_hypothesis_locked' => false,
                'draft_paramset_created' => false,
                'official_is_runtime_invoked' => false,
                'oos_runtime_invoked' => false,
                'oos_table_read' => false,
                'paramset_promoted' => false,
                'plan_run_created' => false,
                'production_ready' => false,
            ];
        }

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'anchor_eval_id', 'anchor_param_set_id',
            'hypothesis_lock_status', 'primary_focus', 'next_semantic_catalog_code', 'r2_hypothesis_locked',
            'draft_paramset_created', 'official_is_runtime_invoked', 'diagnostic_trade_replay_invoked',
            'oos_runtime_invoked', 'oos_table_read', 'paramset_promoted', 'plan_run_created', 'production_ready',
            'next_recommendation', 'artifact_hash', 'error',
        ] as $key) {
            if (! array_key_exists($key, $result)) continue;
            $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
            $this->line($key.'='.$value);
        }
        foreach (($result['locked_hypotheses'] ?? []) as $hypothesis) {
            $this->line('locked_hypothesis='.(string) ($hypothesis['rank'] ?? '').'|'.(string) ($hypothesis['hypothesis_code'] ?? '').'|focus='.(string) ($hypothesis['focus'] ?? ''));
        }

        return ($result['status'] ?? '') === 'C171_COMPARATIVE_OFFICIAL_IS_FAILURE_DIAGNOSTIC_COMPLETED' ? 0 : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/' || substr($path, 0, 2) === '\\\\' || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':'))) return $path;
        return base_path($path);
    }
}
