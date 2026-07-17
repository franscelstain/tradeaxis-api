<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c151-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-review
        {--c150-artifact=storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json}
        {--expected-c150-hash=0b3b5e57011d8d98fcd38c004fb8d94fb33ca9ad}
        {--expected-c150-file-sha1=E25A4E0DF40F9E01E6B3270F2AE2C5FF1CF0A500}
        {--runtime-state=storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json}
        {--expected-runtime-state-hash=00cb935a8252efe340d5f6ec6ea6966d9645cff7}
        {--expected-runtime-state-file-sha1=17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c151-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C151 weekly swing watchlist production/live runtime activation post-execution observation review.';

    private WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService $service;

    public function __construct(?WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC151WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C151 weekly swing watchlist production/live runtime activation post-execution observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c150-artifact'),
            (string) $this->option('expected-c150-hash'),
            (string) $this->option('expected-c150-file-sha1'),
            (string) $this->option('runtime-state'),
            (string) $this->option('expected-runtime-state-hash'),
            (string) $this->option('expected-runtime-state-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'runtime_state_path',
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_review_pass',
            'production_live_runtime_activation_post_execution_observation_review_pass',
            'ready_for_production_live_runtime_activation_post_execution_observation_result_review',
            'production_live_runtime_activation_post_execution_observation_result_review_allowed_next',
            'production_live_runtime_activation_executed',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'c150_lock_valid', 'c150_final_execution_valid', 'c150_convert_from_json_pass',
            'runtime_state_lock_valid', 'runtime_state_observation_valid', 'runtime_state_convert_from_json_pass',
            'c149_operator_go_no_go_valid', 'c148_activation_observation_result_review_valid',
            'activation_authorized', 'primary_candidate_activation_authorized',
            'backup_candidate_activation_authorized', 'comparator_candidate_activation_authorized',
            'primary_candidate_live_runtime_active',
            'backup_candidate_live_runtime_standby_active',
            'comparator_candidate_live_runtime_active',
            'a01_remains_comparator_only',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c150_hash', 'actual_c150_hash', 'c150_hash_match',
            'expected_c150_file_sha1', 'actual_c150_file_sha1', 'c150_file_sha1_match',
            'expected_runtime_state_hash', 'actual_runtime_state_hash', 'runtime_state_hash_match',
            'expected_runtime_state_file_sha1', 'actual_runtime_state_file_sha1', 'runtime_state_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C151 weekly swing watchlist production/live runtime activation post-execution observation review completed');
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
