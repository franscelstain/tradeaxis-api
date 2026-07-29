<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WeeklySwingC171VersionedOfficialIsEvidenceService;
use Illuminate\Console\Command;

class RunBacktestC171VersionedOfficialIsEvidenceCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-versioned-official-is-evidence
        {--param-set-id= : Exact DRAFT watchlist_param_sets.param_set_id}
        {--from=2023-01-02 : Canonical IS start date}
        {--to=2025-05-21 : Canonical IS end date}
        {--approval-reference= : Operator approval reference for official IS evidence persistence only}
        {--operator-approved : Confirm official IS evidence persistence; never permits OOS or promotion}
        {--output=storage/app/watchlist/backtest/c171-versioned-official-is-evidence.json : Output artifact path}
        {--overwrite : Replace existing C171 artifact}
        {--progress : Print progress fields}';

    protected $description = 'Persist versioned official IS evidence for one exact DRAFT paramset; OOS, promotion, PLAN, CONFIRM and rollout remain forbidden.';

    public function handle(): int
    {
        $paramSetId = filter_var($this->option('param-set-id'), FILTER_VALIDATE_INT);
        if ($paramSetId === false || $paramSetId < 1) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=C171_PARAM_SET_ID_INVALID');
            return 1;
        }
        $service = $this->laravel->make(WeeklySwingC171VersionedOfficialIsEvidenceService::class);
        $result = $service->execute(
            (int) $paramSetId,
            trim((string) $this->option('from')),
            trim((string) $this->option('to')),
            trim((string) $this->option('approval-reference')),
            (bool) $this->option('operator-approved'),
            $this->absolutePath(trim((string) $this->option('output'))),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['run_code','phase_label','status','reason_code','param_set_id','params_hash','evidence_pipeline_version','evidence_pipeline_hash','canonical_is_gates_pass','oos_runtime_invoked','paramset_promoted','plan_run_created','production_ready','next_recommendation','artifact_hash'] as $key) {
            if (array_key_exists($key, $result)) {
                $value = is_bool($result[$key]) ? ($result[$key] ? '1' : '0') : (string) $result[$key];
                $this->line($key.'='.$value);
            }
        }
        $tickRiskAudit = is_array($result['tick_risk_guard_audit'] ?? null)
            ? $result['tick_risk_guard_audit']
            : [];
        foreach ([
            'status', 'pass', 'threshold',
            'scored_candidate_count', 'metric_propagated_to_scored_candidates_count',
            'metric_missing_on_scored_candidates_count',
            'official_pick_count', 'metric_propagated_to_official_picks_count',
            'metric_missing_on_official_picks_count',
            'above_threshold_before_guard_count', 'tick_only_rejected_count',
            'tick_multi_reason_rejected_count', 'above_threshold_without_tick_reason_count',
            'eligible_above_threshold_after_guard_count',
            'tick_risk_metric_propagated_to_scored_candidates',
            'tick_risk_metric_propagated_to_official_picks',
            'threshold_enforced_for_all_evidence_rows',
        ] as $key) {
            if (! array_key_exists($key, $tickRiskAudit)) {
                continue;
            }
            $value = is_bool($tickRiskAudit[$key]) ? ($tickRiskAudit[$key] ? '1' : '0') : (string) $tickRiskAudit[$key];
            $this->line('tick_risk_audit_'.$key.'='.$value);
        }

        return (($result['canonical_is_gates_pass'] ?? false) === true) ? 0 : 1;
    }

    private function absolutePath(string $path): string
    {
        if ($path !== '' && (substr($path, 0, 1) === '/' || substr($path, 0, 2) === '\\\\' || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':'))) {
            return $path;
        }
        return base_path($path);
    }
}
