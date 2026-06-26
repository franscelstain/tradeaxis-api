<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review
        {--c80-artifact=storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json}
        {--expected-c80-hash=76270e9ebce21b101629de62aa48262d1d1a6492}
        {--expected-c80-file-sha1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C81 controlled limited runtime opt-in pilot / shadow rollout GO decision finalization review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C81 controlled limited runtime opt-in pilot / shadow rollout GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c80-artifact'),
            (string) $this->option('expected-c80-hash'),
            (string) $this->option('expected-c80-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'go_decision_finalization_review_executed', 'go_decision_finalization_review_allowed', 'go_decision_finalization_review_pass',
            'go_decision_finalized', 'finalized_go_decision', 'operator_go_decision',
            'primary_candidate_go_finalized', 'backup_candidate_go_finalized',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'go_decision_finalization_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c80_hash', 'actual_c80_hash', 'c80_hash_match',
            'expected_c80_file_sha1', 'actual_c80_file_sha1', 'c80_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C81 controlled limited runtime opt-in pilot / shadow rollout GO decision finalization review completed');
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
