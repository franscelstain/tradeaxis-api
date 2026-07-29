<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171TradeEvidenceDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC171TradeEvidenceDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-trade-evidence-diagnostic
        {--eval-id=188 : Exact immutable failed official IS eval_id}
        {--param-set-id=1 : Exact immutable DRAFT param_set_id}
        {--approval-reference= : Operator approval reference for read-only C171 diagnostic}
        {--operator-approved : Confirm read-only diagnostic replay; never permits OOS or promotion}
        {--output=storage/app/watchlist/backtest/c171-trade-evidence-diagnostic.json : Main JSON artifact path}
        {--overwrite : Replace existing diagnostic artifacts}
        {--progress : Print result fields}';

    protected $description = 'Reproduce and segment immutable failed C171 official IS evidence; read-only, no OOS, no new DRAFT, no PLAN or production mutation.';

    public function handle(): int
    {
        $evalId = filter_var($this->option('eval-id'), FILTER_VALIDATE_INT);
        $paramSetId = filter_var($this->option('param-set-id'), FILTER_VALIDATE_INT);
        if ($evalId === false || $evalId < 1 || $paramSetId === false || $paramSetId < 1) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=C171_TRADE_DIAGNOSTIC_IDENTITY_INVALID');
            return 1;
        }
        $service = $this->laravel->make(WeeklySwingC171TradeEvidenceDiagnosticService::class);
        $result = $service->execute(
            (int) $evalId,
            (int) $paramSetId,
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['run_code','phase_label','status','reason_code','eval_id','param_set_id','params_hash','remediation_classification','draft_paramset_created','oos_runtime_invoked','paramset_promoted','plan_run_created','production_ready','next_recommendation','artifact_hash'] as $key) {
            if (! array_key_exists($key, $result)) continue;
            $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
            $this->line($key.'='.$value);
        }
        return ($result['status'] ?? '') === 'C171_TRADE_EVIDENCE_DIAGNOSTIC_COMPLETED' ? 0 : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/' || substr($path, 0, 2) === '\\\\' || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':'))) return $path;
        return base_path($path);
    }
}
