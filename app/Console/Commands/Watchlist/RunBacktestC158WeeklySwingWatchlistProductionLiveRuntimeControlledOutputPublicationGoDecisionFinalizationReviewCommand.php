<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review
        {--c158-operator-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review.json}
        {--expected-c158-operator-hash=14fc284651d7d5f07d1941300b382c2d7071fea3}
        {--expected-c158-operator-file-sha1=66EDD8CC51F5C5F9C29889354A94A01FC0501B21}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--controlled-publication-finalization-confirmed}
        {--free-publication-locked-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C158 weekly swing watchlist production/live runtime controlled output publication GO decision finalization review.';

    private WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C158 weekly swing watchlist production/live runtime controlled output publication GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c158-operator-artifact'),
            (string) $this->option('expected-c158-operator-hash'),
            (string) $this->option('expected-c158-operator-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'controlled_publication_finalization_confirmed' => (bool) $this->option('controlled-publication-finalization-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass',
            'production_live_runtime_controlled_output_publication_go_decision_finalization_review_pass',
            'operator_decision', 'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'go_decision_finalization_confirmed',
            'controlled_publication_finalization_confirmed', 'free_publication_locked_confirmed',
            'plan_confirm_unchanged_confirmed',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_post_publication_observation_review',
            'production_live_runtime_controlled_output_publication_post_publication_observation_review_allowed_next',
            'controlled_output_publication_go_decision_finalization_manifest_created',
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
            'c158_operator_go_no_go_lock_valid', 'c158_operator_go_no_go_review_valid', 'c158_operator_go_no_go_convert_from_json_pass',
            'c158_result_review_lock_valid', 'c158_controlled_output_publication_result_review_valid',
            'controlled_publication_lock_valid', 'controlled_publication_integrity_valid',
            'primary_candidate_ready_for_controlled_output_publication_post_publication_observation_review',
            'backup_candidate_ready_for_controlled_output_publication_post_publication_observation_review',
            'comparator_candidate_ready_for_controlled_output_publication_post_publication_observation_review',
            'a01_remains_comparator_only',
            'c158_controlled_output_publication_go_decision_finalization_review_only',
            'c158_controlled_publication_only',
            'c158_not_free_publication', 'c158_not_unrestricted_publication', 'c158_not_plan_confirm_mutation',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c158_operator_go_no_go_hash', 'actual_c158_operator_go_no_go_hash', 'c158_operator_go_no_go_hash_match',
            'expected_c158_operator_go_no_go_file_sha1', 'actual_c158_operator_go_no_go_file_sha1', 'c158_operator_go_no_go_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_controlled_output_publication_post_publication_observation_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C158 weekly swing watchlist production/live runtime controlled output publication GO decision finalization review completed');
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
