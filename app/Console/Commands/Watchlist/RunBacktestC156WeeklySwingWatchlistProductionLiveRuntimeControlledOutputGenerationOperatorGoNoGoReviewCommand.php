<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review
        {--c155-artifact=storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json}
        {--expected-c155-hash=6fa40eafa588299db84b465202ea060a310d0d12}
        {--expected-c155-file-sha1=637A4D7EAE383CDCD8804040384367439847B16D}
        {--approval-reference=}
        {--operator-decision=}
        {--decision-reason=}
        {--output=storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-decision-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C156 weekly swing watchlist production/live runtime controlled output-generation operator GO/NO-GO/HOLD review.';

    private WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC156WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C156 weekly swing watchlist production/live runtime controlled output-generation operator GO/NO-GO/HOLD review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c155-artifact'),
            (string) $this->option('expected-c155-hash'),
            (string) $this->option('expected-c155-file-sha1'),
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
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass',
            'production_live_runtime_controlled_output_generation_operator_go_no_go_review_pass',
            'operator_decision_recorded', 'operator_decision', 'operator_go_decision',
            'operator_no_go_decision', 'operator_hold_decision', 'operator_decision_confirmed',
            'operator_decision_reason',
            'ready_for_weekly_swing_watchlist_controlled_output_generation_go_decision_finalization_review',
            'production_live_runtime_controlled_output_generation_go_decision_finalization_review_allowed_next',
            'controlled_output_generation_operator_go_no_go_manifest_created',
            'controlled_output_generation_stopped_no_go',
            'controlled_output_generation_deferred_hold',
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
            'c155_lock_valid', 'c155_controlled_output_generation_result_review_valid', 'c155_convert_from_json_pass',
            'controlled_output_lock_valid', 'controlled_output_integrity_valid',
            'primary_candidate_ready_for_controlled_output_generation_go_decision_finalization_review',
            'backup_candidate_ready_for_controlled_output_generation_go_decision_finalization_review',
            'comparator_candidate_ready_for_controlled_output_generation_go_decision_finalization_review',
            'a01_remains_comparator_only',
            'c156_operator_go_no_go_review_only',
            'c156_not_publication', 'c156_not_unrestricted_publication', 'c156_not_plan_confirm_mutation',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c155_hash', 'actual_c155_hash', 'c155_hash_match',
            'expected_c155_file_sha1', 'actual_c155_file_sha1', 'c155_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_concrete_controlled_output_step_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if ($this->isCompletedDecisionStatus((string) ($result['status'] ?? ''))) {
            if ((bool) $this->option('progress')) {
                $this->line('C156 weekly swing watchlist production/live runtime controlled output-generation operator GO/NO-GO/HOLD review completed');
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
            'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO',
            'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO',
            'C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD',
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
