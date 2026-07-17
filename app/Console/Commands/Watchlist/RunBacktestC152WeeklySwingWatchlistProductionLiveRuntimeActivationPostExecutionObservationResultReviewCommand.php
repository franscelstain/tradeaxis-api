<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c152-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-result-review
        {--c151-artifact=storage/app/watchlist/backtest/c151-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-review.json}
        {--expected-c151-hash=55f06c57436ead483bea22626552b7e500d53120}
        {--expected-c151-file-sha1=198B10144A6ADC5447478E36347CD8DAD6136E16}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c152-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-result-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C152 weekly swing watchlist production/live runtime activation post-execution observation result review.';

    private WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService $service;

    public function __construct(?WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC152WeeklySwingWatchlistProductionLiveRuntimeActivationPostExecutionObservationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C152 weekly swing watchlist production/live runtime activation post-execution observation result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c151-artifact'),
            (string) $this->option('expected-c151-hash'),
            (string) $this->option('expected-c151-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_post_execution_observation_result_review_pass',
            'production_live_runtime_activation_post_execution_observation_result_review_pass',
            'ready_for_weekly_swing_watchlist_controlled_output_generation_boundary_review',
            'production_live_runtime_controlled_output_generation_boundary_review_allowed_next',
            'weekly_swing_watchlist_controlled_output_generation_allowed_next',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'weekly_swing_watchlist_publication_allowed',
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
            'c151_lock_valid', 'c151_post_execution_observation_review_valid', 'c151_convert_from_json_pass',
            'c150_lock_valid', 'c150_final_execution_valid',
            'runtime_state_lock_valid', 'runtime_state_observation_valid',
            'c149_operator_go_no_go_valid', 'c148_activation_observation_result_review_valid',
            'activation_authorized', 'primary_candidate_activation_authorized',
            'backup_candidate_activation_authorized', 'comparator_candidate_activation_authorized',
            'primary_candidate_live_runtime_active',
            'backup_candidate_live_runtime_standby_active',
            'comparator_candidate_live_runtime_active',
            'a01_remains_comparator_only',
            'c152_post_execution_observation_result_review_only',
            'c152_not_runtime_activation', 'c152_not_output_generation',
            'c152_not_publication', 'c152_not_plan_confirm_mutation',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c151_hash', 'actual_c151_hash', 'c151_hash_match',
            'expected_c151_file_sha1', 'actual_c151_file_sha1', 'c151_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C152_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C152 weekly swing watchlist production/live runtime activation post-execution observation result review completed');
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
