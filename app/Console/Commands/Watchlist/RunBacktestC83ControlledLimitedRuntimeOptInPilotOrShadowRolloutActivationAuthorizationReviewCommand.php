<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review
        {--c82-artifact=storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json}
        {--expected-c82-hash=1c78f08cc78abe4800cde96b892932ad6b8df725}
        {--expected-c82-file-sha1=24D91E58F7F9FAADE95F6DABF985F430C48C05E2}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C83 controlled limited runtime opt-in pilot / shadow rollout activation authorization review without activation execution or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService $service;

    public function __construct(?WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C83 controlled limited runtime opt-in pilot / shadow rollout activation authorization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c82-artifact'),
            (string) $this->option('expected-c82-hash'),
            (string) $this->option('expected-c82-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'activation_authorization_review_executed', 'activation_authorization_review_allowed', 'activation_authorization_review_pass',
            'activation_authorized', 'activation_executed', 'primary_candidate_activation_authorized', 'backup_candidate_activation_authorized',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'activation_authorization_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c82_hash', 'actual_c82_hash', 'c82_hash_match',
            'expected_c82_file_sha1', 'actual_c82_file_sha1', 'c82_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C83 controlled limited runtime opt-in pilot / shadow rollout activation authorization review completed');
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
