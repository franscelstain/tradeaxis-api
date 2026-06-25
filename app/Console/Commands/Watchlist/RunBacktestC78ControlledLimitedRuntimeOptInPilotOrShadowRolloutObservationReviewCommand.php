<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review
        {--c77-artifact=storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json}
        {--expected-c77-hash=d827547d6d40a73785d4c2409b2913f60db42115}
        {--expected-c77-file-sha1=8C296276DD4D278206366953F975AFD5F7E328DE}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C78 controlled limited runtime opt-in pilot / shadow rollout observation review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService $service;

    public function __construct(?WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C78 controlled limited runtime opt-in pilot / shadow rollout observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c77-artifact'),
            (string) $this->option('expected-c77-hash'),
            (string) $this->option('expected-c77-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_limited_runtime_opt_in_pilot_observation_review_executed',
            'controlled_limited_runtime_opt_in_pilot_observation_review_allowed',
            'controlled_limited_runtime_opt_in_pilot_observation_review_pass',
            'controlled_limited_shadow_rollout_observation_review_executed',
            'controlled_limited_shadow_rollout_observation_review_allowed',
            'controlled_limited_shadow_rollout_observation_review_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'controlled_limited_pilot_observation_context_persisted_to_live_runtime',
            'controlled_limited_shadow_observation_context_persisted_to_live_runtime', 'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c77_hash', 'actual_c77_hash', 'c77_hash_match',
            'expected_c77_file_sha1', 'actual_c77_file_sha1', 'c77_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C78 controlled limited runtime opt-in pilot / shadow rollout observation review completed');
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
