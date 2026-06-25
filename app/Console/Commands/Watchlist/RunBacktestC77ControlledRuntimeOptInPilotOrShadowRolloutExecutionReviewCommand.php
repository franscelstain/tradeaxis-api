<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review
        {--c76-artifact=storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json}
        {--expected-c76-hash=40f1bc516ddbb127ab6f62433059cb99ff2ae2de}
        {--expected-c76-file-sha1=115929AD40A739E9BE1D5A1A58DAA4FECB394ACD}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C77 controlled runtime opt-in pilot / shadow rollout execution review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService $service;

    public function __construct(?WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C77 controlled runtime opt-in pilot / shadow rollout execution review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c76-artifact'),
            (string) $this->option('expected-c76-hash'),
            (string) $this->option('expected-c76-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_runtime_opt_in_pilot_execution_review_executed',
            'controlled_runtime_opt_in_pilot_execution_review_allowed',
            'controlled_runtime_opt_in_pilot_execution_review_pass',
            'controlled_shadow_rollout_execution_review_executed',
            'controlled_shadow_rollout_execution_review_allowed',
            'controlled_shadow_rollout_execution_review_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'controlled_pilot_execution_context_persisted_to_live_runtime',
            'controlled_shadow_execution_context_persisted_to_live_runtime', 'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c76_hash', 'actual_c76_hash', 'c76_hash_match',
            'expected_c76_file_sha1', 'actual_c76_file_sha1', 'c76_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C77 controlled runtime opt-in pilot / shadow rollout execution review completed');
            }
            return 0;
        }

        if (($result['message'] ?? null) !== null) {
            $this->error((string) $result['message']);
        }
        return 1;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return $value === null ? '' : (string) $value;
    }
}
