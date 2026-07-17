<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-review
        {--c158-finalization-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json}
        {--expected-c158-finalization-hash=d8e4bfc3f906f3bc613f9aae1e03a27a67f9241b}
        {--expected-c158-finalization-file-sha1=D732BDF92A76DC25434C2DECC539CD26181C8F21}
        {--controlled-publication-artifact=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json}
        {--expected-controlled-publication-hash=df064c7290ff4c3bfd0c7a8412d39299049c01d5}
        {--expected-controlled-publication-file-sha1=D87AB8CD1564BE8B266B8A68011470272D49EE60}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-review.json}
        {--operator-approved}
        {--post-publication-observation-confirmed}
        {--controlled-publication-observation-confirmed}
        {--free-publication-locked-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation review.';

    private WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService $service;

    public function __construct(?WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c158-finalization-artifact'),
            (string) $this->option('expected-c158-finalization-hash'),
            (string) $this->option('expected-c158-finalization-file-sha1'),
            (string) $this->option('controlled-publication-artifact'),
            (string) $this->option('expected-controlled-publication-hash'),
            (string) $this->option('expected-controlled-publication-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'post_publication_observation_confirmed' => (bool) $this->option('post-publication-observation-confirmed'),
                'controlled_publication_observation_confirmed' => (bool) $this->option('controlled-publication-observation-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_review_pass',
            'production_live_runtime_controlled_output_publication_post_publication_observation_review_pass',
            'post_publication_observation_confirmed',
            'controlled_publication_observation_confirmed',
            'free_publication_locked_confirmed',
            'plan_confirm_unchanged_confirmed',
            'weekly_swing_watchlist_controlled_output_publication_observed',
            'weekly_swing_watchlist_controlled_output_publication_observation_stable',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_review',
            'production_live_runtime_controlled_output_publication_post_publication_observation_result_review_allowed_next',
            'controlled_output_publication_post_publication_observation_manifest_created',
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed',
            'weekly_swing_watchlist_controlled_output_publication_executed',
            'weekly_swing_watchlist_controlled_output_published',
            'weekly_swing_watchlist_controlled_publication_artifact_created',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_controlled_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c158_finalization_lock_valid', 'c158_go_decision_finalization_valid', 'c158_finalization_convert_from_json_pass',
            'controlled_publication_lock_valid', 'controlled_publication_convert_from_json_pass', 'controlled_publication_integrity_valid',
            'primary_candidate_observed_in_controlled_publication',
            'backup_candidate_observed_in_controlled_publication',
            'comparator_candidate_observed_in_controlled_publication',
            'a01_remains_comparator_only',
            'c159_post_publication_observation_review_only',
            'c159_controlled_publication_observation_only',
            'c159_not_free_publication', 'c159_not_unrestricted_publication', 'c159_not_plan_confirm_mutation',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c158_finalization_hash', 'actual_c158_finalization_hash', 'c158_finalization_hash_match',
            'expected_c158_finalization_file_sha1', 'actual_c158_finalization_file_sha1', 'c158_finalization_file_sha1_match',
            'expected_controlled_publication_hash', 'actual_controlled_publication_hash', 'controlled_publication_hash_match',
            'expected_controlled_publication_file_sha1', 'actual_controlled_publication_file_sha1', 'controlled_publication_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation review completed');
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
