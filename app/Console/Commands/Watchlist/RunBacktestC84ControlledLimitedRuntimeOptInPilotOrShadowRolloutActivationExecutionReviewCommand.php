<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review
        {--c83-artifact=storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json}
        {--expected-c83-hash=2927dea9624be20ea493c9e449b57879e0ea5da7}
        {--expected-c83-file-sha1=E90EA61673FB7820988507670F547CD6F02D6A5F}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C84 controlled limited runtime opt-in pilot / shadow rollout activation execution review without live runtime wiring or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService $service;

    public function __construct(?WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C84 controlled limited runtime opt-in pilot / shadow rollout activation execution review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c83-artifact'),
            (string) $this->option('expected-c83-hash'),
            (string) $this->option('expected-c83-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'activation_execution_review_executed', 'activation_execution_review_allowed', 'activation_execution_review_pass',
            'activation_authorized', 'activation_executed', 'controlled_activation_record_created',
            'primary_candidate_activation_executed', 'backup_candidate_activation_executed',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'activation_execution_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c83_hash', 'actual_c83_hash', 'c83_hash_match',
            'expected_c83_file_sha1', 'actual_c83_file_sha1', 'c83_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C84 controlled limited runtime opt-in pilot / shadow rollout activation execution review completed');
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
