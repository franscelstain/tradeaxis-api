<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-operator-go-no-go-review
        {--c159-result-review-artifact=storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-result-review.json}
        {--expected-c159-result-review-hash=bdd708cbe69713e100daa869388eca188eecc2c2}
        {--expected-c159-result-review-file-sha1=26546D7BBD9525582D61A90A383823F508CF3E54}
        {--approval-reference=}
        {--operator-decision=}
        {--decision-reason=}
        {--output=storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-decision-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation operator GO/NO-GO/HOLD review.';

    private WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC159WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationPostPublicationObservationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation operator GO/NO-GO/HOLD review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c159-result-review-artifact'),
            (string) $this->option('expected-c159-result-review-hash'),
            (string) $this->option('expected-c159-result-review-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_decision_confirmed' => (bool) $this->option('operator-decision-confirmed'),
                'operator_decision' => (string) $this->option('operator-decision'),
                'decision_reason' => (string) $this->option('decision-reason'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass',
            'production_live_runtime_controlled_output_publication_post_publication_observation_operator_go_no_go_review_pass',
            'operator_decision_recorded', 'operator_decision', 'operator_go_decision',
            'operator_no_go_decision', 'operator_hold_decision', 'operator_decision_confirmed',
            'operator_decision_reason',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_go_decision_finalization_review',
            'production_live_runtime_controlled_output_publication_post_publication_observation_go_decision_finalization_review_allowed_next',
            'controlled_output_publication_post_publication_observation_operator_go_no_go_manifest_created',
            'post_publication_observation_stopped_no_go',
            'post_publication_observation_deferred_hold',
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
            'c159_result_review_lock_valid', 'c159_post_publication_observation_result_review_valid', 'c159_result_review_convert_from_json_pass',
            'controlled_publication_lock_valid', 'controlled_publication_integrity_valid',
            'primary_candidate_ready_for_post_publication_observation_go_decision_finalization_review',
            'backup_candidate_ready_for_post_publication_observation_go_decision_finalization_review',
            'comparator_candidate_ready_for_post_publication_observation_go_decision_finalization_review',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code',
            'a01_remains_comparator_only',
            'c159_post_publication_observation_operator_go_no_go_review_only',
            'c159_controlled_publication_observation_only',
            'c159_not_free_publication', 'c159_not_unrestricted_publication', 'c159_not_plan_confirm_mutation',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c159_result_review_hash', 'actual_c159_result_review_hash', 'c159_result_review_hash_match',
            'expected_c159_result_review_file_sha1', 'actual_c159_result_review_file_sha1', 'c159_result_review_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_concrete_post_publication_observation_step_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if ($this->isCompletedDecisionStatus((string) ($result['status'] ?? ''))) {
            if ((bool) $this->option('progress')) {
                $this->line('C159 weekly swing watchlist production/live runtime controlled output publication post-publication observation operator GO/NO-GO/HOLD review completed');
            }

            return 0;
        }

        if (($result['message'] ?? null) !== null) {
            $this->error((string) $result['message']);
        }

        return 1;
    }

    private function isCompletedDecisionStatus(string $status): bool
    {
        foreach ([
            'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO',
            'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO',
            'C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD',
        ] as $prefix) {
            if (strpos($status, $prefix) === 0) {
                return true;
            }
        }

        return false;
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
