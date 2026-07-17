<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review
        {--c152-artifact=storage/app/watchlist/backtest/c152-weekly-swing-watchlist-production-live-runtime-activation-post-execution-observation-result-review.json}
        {--expected-c152-hash=85545acd1ea21a0efae6439ccb037b5c4ed34273}
        {--expected-c152-file-sha1=FB866FEC13B1BE9D00E9D9CA50D494EC835EED14}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C153 weekly swing watchlist production/live runtime controlled output-generation boundary review.';

    private WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC153WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C153 weekly swing watchlist production/live runtime controlled output-generation boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c152-artifact'),
            (string) $this->option('expected-c152-hash'),
            (string) $this->option('expected-c152-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_boundary_review_pass',
            'production_live_runtime_controlled_output_generation_boundary_review_pass',
            'ready_for_weekly_swing_watchlist_controlled_output_generation_execution',
            'production_live_runtime_controlled_output_generation_execution_allowed_next',
            'weekly_swing_watchlist_controlled_output_generation_allowed_next',
            'weekly_swing_watchlist_controlled_output_generation_executed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'production_live_runtime_activation_executed',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c152_lock_valid', 'c152_controlled_output_generation_boundary_ready', 'c152_convert_from_json_pass',
            'c151_lock_valid', 'c151_post_execution_observation_review_valid',
            'runtime_state_lock_valid', 'runtime_state_observation_valid',
            'primary_candidate_live_runtime_active',
            'backup_candidate_live_runtime_standby_active',
            'comparator_candidate_live_runtime_active',
            'a01_remains_comparator_only',
            'c153_boundary_review_only', 'c153_not_output_generation',
            'c153_not_publication', 'c153_not_plan_confirm_mutation',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c152_hash', 'actual_c152_hash', 'c152_hash_match',
            'expected_c152_file_sha1', 'actual_c152_file_sha1', 'c152_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C153_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C153 weekly swing watchlist production/live runtime controlled output-generation boundary review completed');
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
