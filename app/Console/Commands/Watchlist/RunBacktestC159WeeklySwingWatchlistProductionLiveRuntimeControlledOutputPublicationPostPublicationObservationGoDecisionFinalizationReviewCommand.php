<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-go-decision-finalization-review
        {--c159-operator-artifact=storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-operator-go-no-go-review.json}
        {--expected-c159-operator-hash=e6c1daae25cfd45950c9c7849b1277cc2099e557}
        {--expected-c159-operator-file-sha1=DEA4167C95413F45DA8E7F6F16816BD178987F78}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--post-publication-observation-finalization-confirmed}
        {--free-publication-locked-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation GO decision finalization review.';

    private WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c159-operator-artifact'),
            (string) $this->option('expected-c159-operator-hash'),
            (string) $this->option('expected-c159-operator-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'post_publication_observation_finalization_confirmed' => (bool) $this->option('post-publication-observation-finalization-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass',
            'production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_pass',
            'operator_decision', 'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'go_decision_finalization_confirmed',
            'post_publication_observation_finalization_confirmed', 'post_publication_observation_closed',
            'free_publication_locked_confirmed', 'plan_confirm_unchanged_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_boundary_review',
            'production_live_runtime_plan_confirm_boundary_review_allowed_next',
            'controlled_output_publication_post_publication_observation_go_decision_finalization_manifest_created',
            'weekly_swing_watchlist_controlled_output_publication_post_publication_observation_result_reviewed',
            'weekly_swing_watchlist_controlled_output_publication_observed',
            'weekly_swing_watchlist_controlled_output_publication_observation_stable',
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
            'c159_operator_go_no_go_lock_valid', 'c159_operator_go_no_go_review_valid', 'c159_operator_go_no_go_convert_from_json_pass',
            'c159_result_review_lock_valid', 'c159_post_publication_observation_result_review_valid',
            'controlled_publication_lock_valid', 'controlled_publication_integrity_valid',
            'primary_candidate_ready_for_plan_confirm_boundary_review',
            'backup_candidate_ready_for_plan_confirm_boundary_review',
            'comparator_candidate_ready_for_plan_confirm_boundary_review',
            'a01_remains_comparator_only',
            'c159_post_publication_observation_go_decision_finalization_review_only',
            'c159_controlled_publication_observation_only',
            'c159_not_free_publication', 'c159_not_unrestricted_publication', 'c159_not_plan_confirm_mutation',
            'c159_topic_complete_after_finalization',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c159_operator_go_no_go_hash', 'actual_c159_operator_go_no_go_hash', 'c159_operator_go_no_go_hash_match',
            'expected_c159_operator_go_no_go_file_sha1', 'actual_c159_operator_go_no_go_file_sha1', 'c159_operator_go_no_go_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_boundary_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation GO decision finalization review completed');
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
