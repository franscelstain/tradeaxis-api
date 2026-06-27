<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewService;
use Illuminate\Console\Command;

class RunBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review
        {--c92-artifact=storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json}
        {--expected-c92-hash=21ea44188d303fb3208d1d1bff864ee86aa247e5}
        {--expected-c92-file-sha1=81B5F1502258E1419BAA7E302BCB6CBABE49A822}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C93 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff closure seal review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewService $service;

    public function __construct(?WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC93ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffClosureSealReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C93 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff closure seal review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c92-artifact'),
            (string) $this->option('expected-c92-hash'),
            (string) $this->option('expected-c92-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_handoff_closure_seal_review_executed', 'post_activation_handoff_closure_seal_review_allowed', 'post_activation_handoff_closure_seal_review_pass',
            'post_activation_handoff_closure_sealed', 'closure_sealed', 'post_activation_handoff_completion_boundary_cleared', 'boundary_cleared',
            'primary_candidate_closure_sealed', 'backup_candidate_closure_sealed', 'comparator_candidate_closure_sealed', 'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_handoff_closure_seal_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'expected_c92_hash', 'actual_c92_hash', 'c92_hash_match', 'expected_c92_file_sha1', 'actual_c92_file_sha1', 'c92_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C93 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff closure seal review completed');
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
