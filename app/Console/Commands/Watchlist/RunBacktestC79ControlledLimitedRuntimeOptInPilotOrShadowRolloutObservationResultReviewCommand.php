<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review
        {--c78-artifact=storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json}
        {--expected-c78-hash=989826f1620bea4592e3543d4908670192fab7f0}
        {--expected-c78-file-sha1=6C6EE121EB7B5F86E19532D24115139F5915CBF3}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C79 controlled limited runtime opt-in pilot / shadow rollout observation result review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewService $service;

    public function __construct(?WatchlistBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C79 controlled limited runtime opt-in pilot / shadow rollout observation result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c78-artifact'),
            (string) $this->option('expected-c78-hash'),
            (string) $this->option('expected-c78-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_limited_runtime_opt_in_pilot_observation_result_review_executed',
            'controlled_limited_runtime_opt_in_pilot_observation_result_review_allowed',
            'controlled_limited_runtime_opt_in_pilot_observation_result_review_pass',
            'controlled_limited_shadow_rollout_observation_result_review_executed',
            'controlled_limited_shadow_rollout_observation_result_review_allowed',
            'controlled_limited_shadow_rollout_observation_result_review_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'controlled_limited_pilot_observation_result_context_persisted_to_live_runtime',
            'controlled_limited_shadow_observation_result_context_persisted_to_live_runtime', 'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c78_hash', 'actual_c78_hash', 'c78_hash_match',
            'expected_c78_file_sha1', 'actual_c78_file_sha1', 'c78_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C79 controlled limited runtime opt-in pilot / shadow rollout observation result review completed');
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
