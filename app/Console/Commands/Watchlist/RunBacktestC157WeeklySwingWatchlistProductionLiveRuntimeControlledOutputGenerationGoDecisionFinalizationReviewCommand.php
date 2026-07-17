<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review
        {--c156-artifact=storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json}
        {--expected-c156-hash=f36edcf84b291dd58119caf4e003c00ced404311}
        {--expected-c156-file-sha1=A7165F0FB30111B313783A1FD3DE77992BD39E99}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--no-publication-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C157 weekly swing watchlist production/live runtime controlled output-generation GO decision finalization review.';

    private WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC157WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C157 weekly swing watchlist production/live runtime controlled output-generation GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c156-artifact'),
            (string) $this->option('expected-c156-hash'),
            (string) $this->option('expected-c156-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'no_publication_confirmed' => (bool) $this->option('no-publication-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass',
            'production_live_runtime_controlled_output_generation_go_decision_finalization_review_pass',
            'operator_decision', 'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'go_decision_finalization_confirmed',
            'no_publication_confirmed', 'plan_confirm_unchanged_confirmed',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_boundary_review',
            'production_live_runtime_controlled_output_publication_boundary_review_allowed_next',
            'controlled_output_generation_go_decision_finalization_manifest_created',
            'weekly_swing_watchlist_controlled_output_generation_executed',
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c156_lock_valid', 'c156_operator_go_no_go_review_valid', 'c156_convert_from_json_pass',
            'c155_lock_valid', 'c155_controlled_output_generation_result_review_valid',
            'controlled_output_lock_valid', 'controlled_output_integrity_valid',
            'primary_candidate_ready_for_controlled_output_publication_boundary_review',
            'backup_candidate_ready_for_controlled_output_publication_boundary_review',
            'comparator_candidate_ready_for_controlled_output_publication_boundary_review',
            'a01_remains_comparator_only',
            'c157_go_decision_finalization_review_only',
            'c157_not_publication', 'c157_not_unrestricted_publication', 'c157_not_plan_confirm_mutation',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c156_hash', 'actual_c156_hash', 'c156_hash_match',
            'expected_c156_file_sha1', 'actual_c156_file_sha1', 'c156_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_controlled_output_publication_boundary_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C157 weekly swing watchlist production/live runtime controlled output-generation GO decision finalization review completed');
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
