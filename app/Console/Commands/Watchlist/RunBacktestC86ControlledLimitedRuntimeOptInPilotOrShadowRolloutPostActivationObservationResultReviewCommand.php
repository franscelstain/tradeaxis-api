<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review
        {--c85-artifact=storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json}
        {--expected-c85-hash=80aa0fc1a0ea662870c373706e8fc15b7bb03396}
        {--expected-c85-file-sha1=80C9596AC8AD714DE161BDA17AECE4734667E645}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C86 controlled limited runtime opt-in pilot / shadow rollout post-activation observation result review without live runtime wiring or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService $service;

    public function __construct(?WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C86 controlled limited runtime opt-in pilot / shadow rollout post-activation observation result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c85-artifact'),
            (string) $this->option('expected-c85-hash'),
            (string) $this->option('expected-c85-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_observation_result_review_executed', 'post_activation_observation_result_review_allowed', 'post_activation_observation_result_review_pass',
            'activation_authorized', 'activation_executed', 'controlled_activation_record_observed', 'post_activation_observation_result_reviewed',
            'primary_candidate_post_activation_result_reviewed', 'backup_candidate_post_activation_result_reviewed',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_observation_result_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c85_hash', 'actual_c85_hash', 'c85_hash_match',
            'expected_c85_file_sha1', 'actual_c85_file_sha1', 'c85_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C86 controlled limited runtime opt-in pilot / shadow rollout post-activation observation result review completed');
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
