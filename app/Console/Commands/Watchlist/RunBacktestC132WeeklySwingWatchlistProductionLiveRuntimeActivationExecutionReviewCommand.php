<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC132WeeklySwingWatchlistProductionLiveRuntimeActivationExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC132WeeklySwingWatchlistProductionLiveRuntimeActivationExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c132-weekly-swing-watchlist-production-live-runtime-activation-execution-review
        {--c131-artifact=storage/app/watchlist/backtest/c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review.json}
        {--expected-c131-hash=b585d9df32751e811f2b11038e71acb730d694b5}
        {--expected-c131-file-sha1=C493DA15314B5AD070FC6D236AD90BB73B046AD8}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c132-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json}
        {--operator-approved}
        {--production-live-runtime-activation-execution-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C132 weekly swing watchlist production/live runtime activation execution review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC132WeeklySwingWatchlistProductionLiveRuntimeActivationExecutionReviewService $service;

    public function __construct(?WatchlistBacktestC132WeeklySwingWatchlistProductionLiveRuntimeActivationExecutionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC132WeeklySwingWatchlistProductionLiveRuntimeActivationExecutionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C132 weekly swing watchlist production/live runtime activation execution review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c131-artifact'),
            (string) $this->option('expected-c131-hash'),
            (string) $this->option('expected-c131-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'production_live_runtime_activation_execution_confirmed' => (bool) $this->option('production-live-runtime-activation-execution-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_execution_review_pass',
            'production_live_runtime_activation_execution_review_pass',
            'ready_for_production_live_runtime_activation_observation_review',
            'production_live_runtime_activation_observation_review_allowed_next',
            'production_live_runtime_activation_execution_review_manifest_created',
            'production_live_runtime_activation_executed',
            'c131_lock_valid', 'c131_activation_approval_valid', 'c131_convert_from_json_pass',
            'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c131_activation_approval_review_only', 'c131_not_live_runtime_activation_execution',
            'c132_execution_review_only', 'c132_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_observation_review',
            'backup_candidate_ready_for_production_live_runtime_activation_observation_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_observation_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c131_hash', 'actual_c131_hash', 'c131_hash_match',
            'expected_c131_file_sha1', 'actual_c131_file_sha1', 'c131_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_observation_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C132 weekly swing watchlist production/live runtime activation execution review completed');
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
