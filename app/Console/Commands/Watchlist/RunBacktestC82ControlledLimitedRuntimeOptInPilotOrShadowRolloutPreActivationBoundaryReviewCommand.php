<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review
        {--c81-artifact=storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json}
        {--expected-c81-hash=45e1abfb6ba0ddc6ddf2b0494527cf8706172f18}
        {--expected-c81-file-sha1=588753D1F62EBCDB318A5969ACE4165CD83D98BD}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C82 controlled limited runtime opt-in pilot / shadow rollout pre-activation boundary review without live activation or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C82 controlled limited runtime opt-in pilot / shadow rollout pre-activation boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c81-artifact'),
            (string) $this->option('expected-c81-hash'),
            (string) $this->option('expected-c81-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'pre_activation_boundary_review_executed', 'pre_activation_boundary_review_allowed', 'pre_activation_boundary_review_pass',
            'pre_activation_boundary_cleared', 'primary_candidate_boundary_cleared', 'backup_candidate_boundary_cleared',
            'production_ready', 'activation_authorized', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'pre_activation_boundary_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c81_hash', 'actual_c81_hash', 'c81_hash_match',
            'expected_c81_file_sha1', 'actual_c81_file_sha1', 'c81_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C82 controlled limited runtime opt-in pilot / shadow rollout pre-activation boundary review completed');
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
